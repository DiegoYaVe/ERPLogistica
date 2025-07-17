<?php
  /* ini_set('display_error', 1); */
  include_once('funciones.php');
  session_start();
  $meses = array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
  $dias = array('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado');
  $sql = 'SELECT * FROM crm_clientes LIMIT 0,50';
  $result = setq($sql);

  $usuario = $_SESSION['uid'];
  $metamensual = busca($usuario, 'usuarios_orden', 'uo_uid', 'uo_metamensual');
  $grupo = busca($usuario, 'usuarios', 'u_id', 'u_grupo');

  $result = setq($sql);
  $lunes = date('Y-m-d', strtotime("this week"));
  $fecha_i = date('Y-m-d', strtotime($lunes));
  $fecha_f = date('Y-m-d', strtotime($lunes.'+ 6 days'));
  $begin = new DateTime($fecha_i);
  $end = new DateTime($fecha_f);
  $interval = new DateInterval('P1D');
  $daterange = new DatePeriod($begin, $interval ,$end);
  foreach ($daterange as $date) {
    $dia =$date->format('Y-m-d');
    $sql = 'SELECT * FROM remisiones WHERE DATE(r_faplica) >= "'.date('Y-m-d', strtotime($dia)).'"
            AND DATE(r_faplica) <= "'.date('Y-m-d', strtotime($dia)).'" AND r_estatus IN ("A", "F", "FE")';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      if(!$datos[$date->format('w')][$row['r_encargado']]) $datos[$date->format('w')][$row['r_encargado']] = 0;
      $datos[$date->format('w')][$row['r_encargado']] += ($row['r_total']-$row['r_precioenvio']);
    }
  }
  $fecha_actual = new DateTime();
  $ultimo_dia_mes = new DateTime($fecha_actual->format('Y-m-t'));
  $dias_faltantes = $fecha_actual->diff($ultimo_dia_mes)->days;

  echo '<div id="toast" class="hidden text-black">Faltan <b>'.$dias_faltantes.'</b> dia(s) para que se termine el mes.</div>
  <input id="diasfaltantes" type="hidden" value="'.$dias_faltantes.'">
  ';
?>
  <div class="card col-12">
    <div class="card-body">
      <section id="minimal-statistics">
      <div class="row">
          <div class="col-8 col-md-10 mt-1 mb-3">
              <h4>Estadisticas de arranque</h4>
              <p>Ve a perseguir tus metas, así vamos en el mes de <?php echo  $meses[date('n')] ?>.</p>
              <hr>
          </div>
          <?php
          if($grupo == "ADMIN") $hidden = ''; 
          else $hidden = 'hidden'; 
          
            ?>
            <div class="col-1 mt-1 mb-3" <?php echo $hidden; ?>>  
              <div class="switch-button">
                  <!-- Checkbox -->
                  <input type="checkbox" name="switch-button" id="switch-label" class="switch-button__checkbox" onchange="llenarcontenido()">
                  <!-- Botón -->
                  <label for="switch-label" class="switch-button__label"></label>
              </div>
            </div>
      </div> 
      <div id="contenido">  
  </section>
  </div>
</div>

<script>
  var diasfaltantes = document.getElementById('diasfaltantes');
  function mostrarToast() {
    var toast = document.getElementById("toast");
    if(toast){
      toast.style.display = "block";

      // Agrega una pequeña demora para permitir que la transición funcione
      setTimeout(function() {
        toast.style.opacity = "1";
      }, 500);
    }
  }  
  function mostrarAlert() {
    Swal.fire({
      icon: 'error',
      title: 'Faltan '+diasfaltantes.value+' dia(s) para que finalice el mes, revisa tus metas.',
      //showDenyButton: true,
      confirmButtonText: '<i class="fas fa-check" style="color:#ffffff"></i> Entendido',
      }).then((result) => {
          Swal.close();
      });
  }  
  if(parseInt(diasfaltantes.value) > 7){
    mostrarToast();
  } else {
    mostrarAlert();
  }
  
  function llenarcontenido(){
    console.log('entro aqui');
    var switchcon = document.getElementById("switch-label");
    var divcon = document.getElementById("contenido");
    if(switchcon.checked){
      $.ajax({
        type: "POST",
        url: "query/contenidopsadmin.php",
        data: {},
        success: function(data) {
          divcon.innerHTML = data;
          var tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
          tooltips.forEach(function(tooltip) {
              new bootstrap.Tooltip(tooltip);
          });
        }
      });
    } else {
      $.ajax({
        type: "POST",
        url: "query/contenidopssimple.php",
        data: {},
        success: function(data) {
          divcon.innerHTML = data;
          var tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
          tooltips.forEach(function(tooltip) {
              new bootstrap.Tooltip(tooltip);
          });
        }
      });
    }
  }

  llenarcontenido();
</script>

<style>
  :root {
    --color-green: #00a878;
    --color-red: #fe5e41;
    --color-button: #fdffff;
    --color-black: #000;
  }

  .switch-button {
      display: inline-block;
  }

  .switch-button .switch-button__checkbox {
      display: none;
  }

  .switch-button .switch-button__label {
      background-color: var(--color-red);
      width: 8rem;
      height: 3rem;
      border-radius: 3rem;
      display: inline-block;
      position: relative;
      text-align: center; /* Centra el texto horizontalmente */
      line-height: 3rem; /* Centra el texto verticalmente */
      color: var(--color-black); /* Color del texto */
  }

  .switch-button .switch-button__label::after {
    transition: .2s;
      content: 'TABLAS'; /* Texto a mostrar */
      position: absolute;
      left: 2rem;
      top: 0;
      bottom: 0;
      right: 0;
      z-index: 1; /* Asegura que el texto esté encima del botón */
  }

  .switch-button .switch-button__label:before {
      transition: .2s;
      display: block;
      position: absolute;
      width: 3rem;
      height: 3rem;
      background-color: var(--color-button);
      content: '';
      border-radius: 50%;
      box-shadow: inset 0px 0px 0px 1px var(--color-black);
  }

  .switch-button .switch-button__checkbox:checked + .switch-button__label {
      background-color: var(--color-green);
  }

  .switch-button .switch-button__checkbox:checked + .switch-button__label:before {
      transform: translateX(5rem);
  }

  .switch-button .switch-button__checkbox:checked + .switch-button__label::after {
      content: 'RESUMEN'; /* Texto a mostrar cuando el switch está activado */
      transition: .2s;
      transform: translateX(-40%); /* Desplaza el texto hacia la izquierda */
  }
  #toast {
    display: none;
    position: fixed;
    top: 10%;
    left: 85%;
    transform: translateX(-50%);
    background-color: #FFC300;
    color: #fff;
    padding: 10px 20px;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 99999;
    width: 30%;
    opacity: 0; 
    transition: opacity 0.5s ease; 
  }
  .form-group {
      position: relative;
  }
  
  .form-group input:focus + .custom-label {
      top: -18px;
      font-size: 12px;
      background-color: white;
      padding: 0 5px;
  }
  
  .custom-label {
      position: absolute;
      top: 10px;
      left: 10px;
      transition: all 0.2s;
      pointer-events: none;
  }
</style>