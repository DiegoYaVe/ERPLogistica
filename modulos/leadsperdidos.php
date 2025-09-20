<?php
  //ini_set('display_errors', 1);
  class leadsperdidos{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelleadsperdidos(isset($obj));
    }
    function index(){      
      if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-20 days'));
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
      if(!isset($_REQUEST['hini'])) $_REQUEST['hini'] = NULL;
      if(!isset($_REQUEST['hfin'])) $_REQUEST['hfin'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      /* die(isset($_REQUEST['nmb'])); */
     
      $this->model->result(trim($_REQUEST['nmb']), $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['page']);
      $this->view = new viewleadsperdidos($this->model);
      $this->view->browse($_REQUEST['nmb'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['page']);
    }
    function insert(){

      $fini = explode("T",$_POST['fini']);
      $this->model->setdata("NULL",$_POST['ugen'],$fini[0],$fini[1],"NULL","A",$_POST['observacion']);
      $this->model->insert();
      redirect('?modulo=leadsperdidos&accion=show&id='.$this->model->idt);
    }

    function catalogo(){
      $this->view = new viewleadsperdidos(isset($this->model));
      $this->view->catalogo();
    }

    function reasignarlead(){
      $id = $_POST['id'];
      $fasigna = $_POST['fasigna'];
      $resultado = $this->model->reasignarlead($id);
      // Ahora accedemos a los elementos del arreglo utilizando índices
      $usuario = $resultado[0];
      $bandera = $resultado[1];

      if($bandera == 1){
        $this->model->historialperdido($id, $usuario, $fasigna);
      }
      $this->model->nuevovendedor($id, $usuario, $fasigna);
      
      redirect('?modulo=leadsperdidos&accion=index');
    }

  }

  class modelleadsperdidos{
    function select($id){
      $sql = 'SELECT * FROM crm_capturaleads WHERE cc_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['cc_id'];
      $this->ugen = $row['cc_ugen'];
      $this->fini = $row['cc_fini'];
      $this->hini = $row['cc_hini'];
      $this->hfin = $row['cc_hfin'];      
      $this->estatus = $row['cc_estatus'];
      $this->observacion = $row['cc_observacion'];
    }
    function result($nmb, $fini, $ffin, $page) {
      $bloque = 50;
      $sql = 'SELECT * FROM crm_leads WHERE cl_vendedor = "'.$_SESSION['uid'].'" AND cl_estatus = "X"';      
      
      if ($fini) {
        $sql .= ' AND cl_fasigna >= "'.$fini.'"';
      }
    
      if ($ffin) {
        $sql .= ' AND cl_fasigna <= "'.$ffin.'"';
      }
    
      if ($nmb) {
        $sql .= ' AND cl_nmb LIKE "%'.$nmb.'%"';
      }
    
      $sql .= ' ORDER BY cl_id DESC LIMIT '.($bloque * $page).','.$bloque;
      //die($sql);
      $this->result = setq($sql);
      $this->resultt = setq($sql);
    }    
    function setdata($id,$ugen,$fini,$hini,$hfin,$estatus,$observacion){
      mb_internal_encoding("UTF-8");
      $this->id = $id;
      $this->ugen = clearvmayus($ugen);
      $this->fini = $fini;
      $this->hini = $hini;
      $this->hfin = $hfin;
      $this->estatus = clearvmayus($estatus);
      $this->observacion = clearvmayus($observacion);
    }
    function insert(){
      $sql = 'INSERT INTO crm_capturaleads SET
              cc_fini = "'.$this->fini.'",
              cc_ugen = "'.$this->ugen.'",
              cc_hini = "'.$this->hini.'",
              cc_hfin = '.$this->hfin.',
              cc_estatus = "'.$this->estatus.'",
              cc_observacion = "'.$this->observacion.'"';
      setq($sql);

      $this->idt = getmax('cc_id','crm_capturaleads',false,false);
    }

    function setdatalead($id,$lead,$nmb,$cp,$correo,$telefono,$estatus,$observacion,$vendedor,$ncaptura,$ucaptura,$hasigna,$fechaasigna){
      $this->id = $id;
      $this->lead = $lead;
      $this->nmb = clearvmayus($nmb);
      $this->cp = $cp;
      $this->correo = $correo;
      $this->telefono = $telefono;
      $this->estatus = clearvmayus($estatus);
      $this->observacion = clearvmayus($observacion);
      $this->vendedor = $vendedor;
      $this->ncaptura = $ncaptura;
      $this->ucaptura = clearvmayus($ucaptura);
      $this->hasigna = $hasigna;
      $this->fasigna = date("Y-m-d");
    }

    function historialperdido($id, $usuario, $fasigna){
      $sql = 'UPDATE historial_perdidos SET 
              hp_fasigna = "'.$fasigna.'", 
              hp_vendedor = "'.$usuario.'" 
              WHERE hp_perdido = "'.$id.'"';
      setq($sql);
    }

    function reasignarlead($id){
      $nasignaciones = busca($id, 'historial_perdidos', 'hp_perdido', 'COUNT(*)');
      if($nasignaciones >= 0){
        $nasinacion = busca($id, 'historial_perdidos', 'hp_fasigna is NULL AND hp_perdido', 'COUNT(*)');
        //Si es la primera vez que se le va a asignar un vendedor
        if($nasinacion == 1){
          //Buscamos el uid del vendedor
          $vendedor = busca($id, 'crm_leads', 'cl_id', 'cl_vendedor');
          $bandera = 1;
        } else{
          //Si se le ha asignado una vez un vendedor previamente tenemos que asignar un nuevo vendedor
          $resultado = $this->buscavendedor($id);
          // Ahora accedemos a los elementos del arreglo utilizando índices
          $vendedor = $resultado[0];
          $bandera = $resultado[1];
        }
      } else{
        //Si hay más de un registro en la tabla del historial
        $resultado = $this->buscavendedor($id);
        // Ahora accedemos a los elementos del arreglo utilizando índices
        $vendedor = $resultado[0];
        $bandera = $resultado[1];
      }

      return array($vendedor, $bandera);
    }

    function buscavendedor($idlead){
    //Consultamos el número de usuarios activos que son de ventas
    $sqlu = 'SELECT u_id FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A"';
    $resultu = setq($sqlu);
    $arrayU = mysqli_fetch_all($resultu, MYSQLI_ASSOC);
    $u_id_array = array_column($arrayU, 'u_id');

    //Consultamos el número de vendedores que ya han sido asignados a este lead con anterioridad
    $sqlh = 'SELECT hp_vendedor FROM historial_perdidos WHERE hp_perdido = "' . $idlead . '"';
    $resulth = setq($sqlh);
    $arrayH = mysqli_fetch_all($resulth, MYSQLI_ASSOC);
    $hl_vendedor_array = array_column($arrayH, 'hp_vendedor');

    // Encontrar elementos diferentes
    $diferencia = array_diff($u_id_array, $hl_vendedor_array);

    /* echo '<br>' . var_dump($diferencia) . '<br>';
    echo '<br> Asignaciones antes de dar una vuelta: ' . count($diferencia) . '<br>'; */

    if (count($diferencia) > 0) {
      // Obtén un índice aleatorio del arreglo $vendedores
      $vendedorAleatorio = array_rand($diferencia);

      // Usa el índice aleatorio para obtener el valor correspondiente
      $vendedor = $diferencia[$vendedorAleatorio];
      $bandera = 1;

    } else {
      /*
      Si no hay diferencia alguna significa que todos los vendedores posibles ya han sido 
      asignados a este lead con anteriordad.
      Procedemos a asignar el lead de forma aleatoria a cualquier otro vendedor diferente al último asignado
      contabilizando el número de veces que se le ha asignado el lead a ese vendedor.
      */

      //Obtenemos el uid del ultimo vendedor asignado
      $sqlt = 'SELECT hp_vendedor FROM historial_perdidos WHERE hp_perdido = "' . $idlead . '" ORDER BY hp_fasigna DESC LIMIT 1';
      $resultt = setq($sqlt);
      list($ultimovendedor) = $resultt->fetch_array();

      //Consulto los vendedores con la excepcion del ultimo asignado
      $sqlr = 'SELECT hp_vendedor, COUNT(*) AS cantidad
              FROM historial_perdidos WHERE hp_vendedor != "' . $ultimovendedor . '"
              GROUP BY hp_vendedor
              HAVING COUNT(*) = (
                SELECT MIN(cnt)
                FROM (
                  SELECT COUNT(*) AS cnt
                  FROM historial_perdidos
                  GROUP BY hp_vendedor
                ) AS subquery
              )
      ';
      $resultr = setq($sqlr);
      $filas = $resultr->num_rows;
      //Si el resultado incluye más de un vendedor
      if ($filas > 1) {
        $arrayR = mysqli_fetch_all($resultr, MYSQLI_ASSOC);
        // Obtén los valores de 'u_id' de $arrayU y 'hl_vendedor' de $arrayH en dos arreglos separados
        $vendedores = array_column($arrayR, 'hp_vendedor');
        // Obtén un índice aleatorio del arreglo $vendedores
        $vendedorAleatorio = array_rand($vendedores);

        // Usa el índice aleatorio para obtener el valor correspondiente
        $vendedor = $vendedores[$vendedorAleatorio];

        //Si el resultado es solo un vendedor
      } else if($filas == 1){
        list($vendedor) = $resultr->fetch_array();
      } else{
        $sqlt = 'SELECT hp_vendedor FROM historial_perdidos WHERE hp_perdido = "' . $idlead . '" ORDER BY hp_fasigna ASC LIMIT 1';
        $resultt = setq($sqlt);
        list($vendedor) = $resultt->fetch_array();
      }
      $bandera = 2;
      }
      return array($vendedor, $bandera);
    }

    function nuevovendedor($id, $usuario, $fasigna){
      $sql = 'UPDATE crm_leads SET cl_estatus= "R", cl_vendedor = "'.$usuario.'", cl_fasigna = "'.$fasigna.'" WHERE cl_id = "'.$id.'"';
      setq($sql);

      inserthistorial($id, date("H:i:s"), date("Y-m-d"), $usuario);
    }

  }

  class viewleadsperdidos{
    var $model;
    function __construct($model){
      ?>
      <script>
      function checkguardar(){
        document.getElementById("sendform").innerHTML = "Guardando";
        document.getElementById("sendform").disabled = true;
        return true;
      }
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 78) {
          var btn = document.getElementById("nuevo");
          btn.click();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 76) {
          $("#nmb").focus();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 82) {
          window.location.reload();
        }
      });
      </script>
      <?php
        $this->model = $model;
        $this->tipoc = array("C"=>"Cliente","P"=>"Prospécto");
        $this->estatust = array("N"=>"Abierto","P"=>"Construcción","G"=>"Negociación","L"=>"Aplazado","A"=>"Vendido","F"=>"Finalizado","C"=>"Cancelado","O"=>"En Linea","X"=>"Perdido","W"=>"Ganados");
        $this->colestatust = array("N"=>"bg-yellow bg-accent-1","P"=>"bg-orange bg-accent-2","G"=>"bg-indigo bg-accent-3","L"=>"bg-amber bg-darken-4","A"=>"bg-success bg-accent-3","C"=>"bg-red bg-accent-4","F"=>"bg-blue bg-darken-2","X"=>"bg-red bg-darken-2");
    }
    function browse($ugen,$fini,$ffin,$page){
        $botones = '';
        $count = busca('X', 'crm_leads', 'cl_estatus', 'COUNT(*)');
        if($count > 0){
          $botones = '<button class="btn btn-primary" onclick="reasignar()"><i class="fas fa-exchange-alt"></i> Reasignar todos</button>';
        }
        $filtro = '
        <form autocomplete="off" action="?modulo=leadsperdidos&accion=index" method="post" id="filtro" class="">
          <div class="mb-5" hidden>
            <label class="mr-sm-2">Page:</label>
            <div class="position-relative has-icon-left">
              <input type="text" id="page" name="page" class="form-control" value="" required>
            </div>
          </div>
          <div class="mb-2">
            <label class="mr-sm-2">Motivo:</label>
            <select name="motivo" id="motivo" class="form-control">
              <option value="">TODOS</option>';
              //menu_select_db('usuarios','u_id','u_id',$agente,'agente','u_empresa = "'.$_SESSION['emp'].'" AND u_estatus = "A"',false,false,true,false,"Todos")
              $sql = 'SELECT * FROM catalogo_perdidos';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                $filtro.= '<option value="'.$row['cp_id'].'">'.$row['cp_nmb'].'</option>';
              }
              $filtro.= '
              <option value="O">OTROS</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="mr-sm-2">Fecha desde:</label>
            <div class="position-relative has-icon-left">
              <input type="date" id="fini" name="fini" class="form-control" value="'.$fini.'" required>
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="mr-sm-2">Fecha hasta:</label>
            <div class="position-relative has-icon-left">
              <input type="date" id="ffin" name="ffin" class="form-control" value="'.$ffin.'" required>
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-5">
            <label>Acciones:</label><br>
            <button type="button" onclick="mandar(0)" class="btn btn-success"><i class="fas fa-search"></i> Filtrar </button>
            <!-- <a href="?modulo=leadsperdidos&accion=index"><button type="button" class="btn btn-warning"> -->
            <button type="button" onclick="mandar(1)" class="btn btn-warning">
            <span class="glyphicon glyphicon-record"></span> <i class="fas fa-broom"></i> Limpiar
            </button><!-- </a> -->
          </div>
        </form>
        ';

        $atras = '
        <a href="?modulo=leadsperdidos&accion=index">
          <button class="btn btn-sm btn-warning">
            <i class="fa fa-arrow-left"></i> Atrás
          </button>
        </a>';  
        toolbar("LEADS SIN NEGOCIACIÓN",'',$filtro, '', $botones);
          ?>
    <script>

    function alertSweet(icono, titulo, mensaje) {
        Swal.fire({
            icon: icono,
            title: titulo,
            text: mensaje
        }).then((result) => {
            if (result.isConfirmed || result.isDenied) {
                Swal.close();
            }
        });
    }

    function reasignar(){

      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      })

      Swal.fire({
        icon: "question",
        title: "Confirmar",
        text: '¿Deseas reasignar todos los leads sin negociación?',
        showCancelButton: true, // Muestra el botón de cancelar
        focusConfirm: false, // Evita que el botón "Confirmar" obtenga el foco
        confirmButtonText: "Confirmar", // Texto para el botón "Confirmar"
        confirmButtonColor: "#3085d6", // Color del botón "Confirmar"
        cancelButtonText: "Cancelar", // Texto para el botón "Cancelar"
        cancelButtonColor: "#d33", // Color del botón "Cancelar"
      }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              type: "POST",
              url: "query/reasignarleadsperdidos.php",
              success: function(datajson) {
                var data = JSON.parse(datajson);
                console.log('data: ', data.respuesta);
                if(data.respuesta == 0){
                  Toast.fire({
                    icon: 'success',
                    title: data.txtrespuesta
                  })
                } else{
                  Toast.fire({
                    icon: 'error',
                    title: data.txtrespuesta
                  })
                }
              }
             });
          } else {
            Swal.close();
          }
      });
    }

    /* function mandar(id){
      document.getElementById("page").value = id;
      document.getElementById("filtro").submit();
    } */

    function finalizar(id) {
      Swal.fire({
        icon: "question",
        title: "Atención",
        html: "¿Está seguro de finalizar la hoja del día?<br>Esta opción es irreversible.",
        showCloseButton: true, // Muestra el botón de cerrar (x) en el SweetAlert
        showCancelButton: true, // Muestra el botón de cancelar
        focusConfirm: false, // Evita que el botón "Confirmar" obtenga el foco
        confirmButtonText: "Confirmar", // Texto para el botón "Confirmar"
        confirmButtonColor: "#3085d6", // Color del botón "Confirmar"
        cancelButtonText: "Cancelar", // Texto para el botón "Cancelar"
        cancelButtonColor: "#d33", // Color del botón "Cancelar"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "?modulo=leadsperdidos&accion=finalizar&id=" + id;
        }
        Swal.close();
      });
    }


    </script>
          <?php
          //Modal - Nuevo deal - TABLERO
          echo '
          <div class="modal fade text-xs-left" id="newtablero" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title" id="myModalLabel33">Nueva hoja de leadsperdidos</h4>
                </div>
                <form method="post" action="?modulo=leadsperdidos&accion=insert" autocomplete="off" >
                  <div class="modal-body row">
                    <div class="col-md-6 col-xs-12">
                      <label>Fecha inicio: </label>
                      <div class="mb-5">
                        <div class="position-relative has-icon-left">
                          <input type="datetime-local" id="fini" name="fini" class="form-control" value="'.date('Y-m-d').'T'.date('H:i:s').'" step="any" required readonly>
                          <div class="form-control-position">
                            <i class="icon-calendar5"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                      <label>Usuario: </label>
                      <div class="mb-5">
                      <input type="text" id="ugen" name="ugen" class="form-control" value="'.$_SESSION['uid'].'" required readonly>
                      </div>
                    </div>
                    <div class="col-md-12 col-xs-12">
                      <label>Observaciones: </label>
                      <div class="mb-5">
                        <textarea id="observacion" class="form-control" rows="4" name="observacion" placeholder="Escribe una obervación"></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer p-2">
                    <center><button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Registrar hoja nueva</button></center>
                  </div>
                </form>
              </div>
            </div>
          </div>';
          //filtro
      echo '
      <form hidden id="formreg" method="post" action="?modulo=clientes&accion=edit" autocomplete="off">
        <input type="number" name="flag" value="1" hidden>
        <input type="text" id="idp" name="idp" hidden>
        <input type="text" id="nmbp" name="nmbp" hidden>
        <input type="text" id="telefonop" name="telefonop" hidden>
        <input type="text" id="correop" name="correop" hidden>
        <input type="text" id="cpp" name="cpp" hidden>
        <input type="text" id="observacionesp" name="observacionesp" hidden>
      </form>
      ';

      echo '
      <form hidden id="formtablero" method="post" action="?modulo=tableros&accion=index" autocomplete="off">
        <input type="number" name="flag" value="1" hidden>
        <input type="text" id="aliasp2" name="aliasp" hidden>
      </form>
      ';


      echo '
      <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table width="90%" class="mb-0 table-hover table-striped" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">
                    <tr>
                      <th style="width: 15%;"><b>Nombre</b></th>
                      <th style="width: 15%;"><b>Teléfono</b></th>
                      <th style="width: 20%;"><b>Correo</b></th>
                      <th><b>Estatus</b></th>
                      <th style="width: 5%;"><b>Fecha de asignación</b></th>
                      <th style="width: 20%;"><b>Motivo</b></th>
                      <th style="width: 10%;"><b>Reasignado</b></th>
                      <th style="width: 15%;"></th>
                    </tr>
                  </thead>';
                echo '</table>
              </center>
            </div>
          </div>
        </div>
      </div>';

    echo '
    <script>
    function mandar(id){
      var fini = document.getElementById("fini");
      var ffin = document.getElementById("ffin");
      var motivo = document.getElementById("motivo");

      if(id == 1){          
        fini.value = "";
        ffin.value = "";
        motivo.value = "";
      }

      $("#myTable").DataTable().clear().draw();
      $("#myTable").DataTable().destroy();

      $("#myTable").DataTable( {
      paging: true,
      scrollY: 400,
      processing: true,
      serverside: true,
      ordering: false,
      language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
      },
      ajax: {
        url: "query/datatableleadsperdidos.php",
        type: "POST",
        datatype: "json",
        data:{ 
          "fini": fini.value,
          "ffin": ffin.value,
          "motivo": motivo.value
        }
      },
      pageLength: "50",
      responsivePriority: 1,
      columnDefs: [
        { type: "date", targets: 4 } // Define el tipo de columna de ordenamiento de tiempo para la columna 5 (hora)
      ],
      order: [[4, "asc"]],
      });
    }

    function registrarCliente(id){
      $.ajax({
      url: "query/consultalead.php", 
      type: "POST", 
      dataType: "json", 
      data: {
        "id": id
      },
      success: function(data) {
        document.getElementById("idp").value = id;
        document.getElementById("nmbp").value = data["nmb"];
        document.getElementById("telefonop").value = data["telefono"];
        document.getElementById("correop").value = data["correo"];
        document.getElementById("cpp").value = data["cp"];
        document.getElementById("observacionesp").value = data["observaciones"];
        document.getElementById("formreg").submit();
      },
      error: function(jqXHR, textStatus, errorThrown) {
          // Manejo de errores
          console.log("Error: " + textStatus);
      }
    });
    }

    function iniciarTablero(id){
      var formulario = document.getElementById("formtablero");
      $.ajax({
      url: "query/consultacliente.php", 
      type: "POST", 
      data: {
        "id": id
      },
      success: function(data) {
        document.getElementById("aliasp2").value = data;
        formulario.submit();
      },
      error: function(jqXHR, textStatus, errorThrown) {
        // Manejo de errores
        console.log("Error: " + textStatus);
      }
    });
    }
    
  </script>';


    echo '
    <script>
    $("#myTable").DataTable({
      paging: true,
      scrollY: 400,
      processing: true,
      ordering: false,
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
      },
      ajax: {
        url: "query/datatableleadsperdidos.php",
        type: "POST",
        datatype: "json",
        data: {
          "motivo": "",
          "hini": "",
          "hfin": ""
        }
      },
      pageLength: "50",
      responsivePriority: 1,
      columnDefs: [
        { type: "date", targets: 4 } // Define el tipo de columna de ordenamiento de fecha para la columna 5 (fecha)
      ],
      order: [[4, "desc"]], // Ordenar la columna 5 de forma descendente (de más reciente a más vieja)
    });    
    </script>
    ';

    }
    
    function catalogo(){
    echo '
      <!-- Incluye la biblioteca Sortable -->
      <script src="assets/js/Sortable.min.js"></script>
      <!-- Incluye los scripts de Materialize -->
      <script src="assets/js/materialize.min.js"></script>
      ';
    echo '

    <style>
      /* Añade estilos personalizados para la tabla arrastrable */
      .sortable-table {
        width: 100%;
      }
      .sortable-item {
        background-color: #f5f5f5;
        border: 1px solid #e0e0e0;
        padding: 10px;
        cursor: grab;
      }
    </style>';

    $atras = '
    <a href="?modulo=leadsperdidos&accion=index">
      <button class="btn btn-sm btn-warning">
        <i class="fa fa-arrow-left"></i> Atrás
      </button>
    </a>';   

    toolbar("Catálogo",$atras,'','');

    echo '
    <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table width="90%" class="mb-0 table-hover table-striped" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">';
      /* echo '
      <div class="container">
        <div class="listWrap">
            <table id="myTable" class="list">
            <thead class="">'; */
                echo '
                  <tr>
                    <th>Nombre</th>
                    <th>Estatus</th>
                  </tr>
                </thead>
                <tbody>';
                echo '
                </tbody>
            </table>
          </div>
    </div>
      ';
          

    echo '
    <script>

    $("#myTable").DataTable( {
    paging: true,
    scrollY: 400,
    processing: true,
    ordering: false,
    language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
      ajax: {
    url: "query/datatablecatalogo.php",
    type: "POST",
    data:{}
    },
    pageLength: "50",
    responsivePriority: 1,
    });

    $("#myTable").on("draw.dt", function () {
      aplicarEstiloColumna(1);
    });

    function aplicarEstiloColumna(col) {
      var columnaNumero = col;
      $("#myTable tbody tr").each(function() {
        var celdas = $(this).find("td");
        if (celdas.length > columnaNumero) {
          celdas.eq(columnaNumero).css("width", "130px");
          celdas.eq(columnaNumero).css("height", "33px");
          celdas.eq(columnaNumero).css("display", "table-cell");
          celdas.eq(columnaNumero).addClass("btn");
          celdas.eq(columnaNumero).addClass("no-border");
          celdas.eq(columnaNumero).addClass("text-middle");
          celdas.eq(columnaNumero).addClass("btn-sm");
          if(celdas.eq(columnaNumero).html() == "Activo"){
            celdas.eq(columnaNumero).addClass("btn-success");
          } else{
            celdas.eq(columnaNumero).addClass("btn-warning");
          }
        }
      });
    }


    function alertSweet(icono, titulo, mensaje) {
      Swal.fire({
          icon: icono,
          title: titulo,
          text: mensaje
      }).then((result) => {
          if (result.isConfirmed || result.isDenied) {
              Swal.close();
          }
      });
    }

    function finalizar(id) {
      Swal.fire({
        icon: "question",
        title: "Atención",
        html: "¿Está seguro de finalizar la hoja del día?<br>Esta opción es irreversible.",
        showCloseButton: true, // Muestra el botón de cerrar (x) en el SweetAlert
        showCancelButton: true, // Muestra el botón de cancelar
        focusConfirm: false, // Evita que el botón "Confirmar" obtenga el foco
        confirmButtonText: "Confirmar", // Texto para el botón "Confirmar"
        confirmButtonColor: "#3085d6", // Color del botón "Confirmar"
        cancelButtonText: "Cancelar", // Texto para el botón "Cancelar"
        cancelButtonColor: "#d33", // Color del botón "Cancelar"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "?modulo=leadsperdidos&accion=finalizar&id=" + id;
        }
        Swal.close();
      });
    }
    </script>
          ';
    }
  }
?>