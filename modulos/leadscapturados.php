<?php
  //ini_set('display_errors', 1);
  class leadscapturados{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelleadscapturados(isset($obj));
    }
    function index(){      
      if(!isset($_REQUEST['uvendedor'])) $_REQUEST['uvendedor'] = NULL;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-20 days'));
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
      if(!isset($_REQUEST['hini'])) $_REQUEST['hini'] = NULL;
      if(!isset($_REQUEST['hfin'])) $_REQUEST['hfin'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
      if(!isset($_REQUEST['telefono'])) $_REQUEST['telefono'] = NULL;

      /* die(isset($_REQUEST['nmb'])); */
     
      
      $this->model->result(trim($_REQUEST['uvendedor']),$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['hini'],$_REQUEST['hfin'],$_REQUEST['page'], $_REQUEST['telefono']);
      $this->view = new viewleadscapturados($this->model);
      $this->view->browse($_REQUEST['uvendedor'],$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['page'], $_REQUEST['telefono']);
    }
    function insert(){

      $fini = explode("T",$_POST['fini']);
      $this->model->setdata("NULL",$_POST['ugen'],$fini[0],$fini[1],"NULL","A",$_POST['observacion']);
      $this->model->insert();

      redirect('?modulo=leadscapturados&accion=show&id='.$this->model->idt);
    }
    function show(){
      $this->model->resultlead($_GET['id']);
      $this->view = new viewleadscapturados($this->model);
      $this->view->show();
    }
    function insertlead(){
      $ncaptura = getmax("cl_ncaptura", "crm_leads WHERE cl_fasigna = '".date("Y-m-d")."'", false, true); //Buscamos el número consecutivo diario de registros
      $pvendedor = busca($_POST['telefono'], "crm_leads", "cl_telefono", "cl_vendedor"); //Buscamos si hay un registro previo del prospecto
      $nvendedores = getmax("uo_orden", "usuarios_orden", false, false); //Consultamos el total de usuarios asignados para ventas


      /* $leadmax = getmax("cl_id", "crm_leads WHERE cl_fasigna = '".date("Y-m-d")."'", false, true); //Traemos el último registro de la tabla de leadscapturados
      $uidvendedor = busca($leadmax, "crm_leads", "cl_estatus = 'P' AND cl_id", "cl_vendedor"); //Traemos el uid del ultimo registro de asignación vendedor
      $ultimovendedor = busca($uidvendedor, "usuarios_orden", "uo_uid", "uo_orden"); //Consultamos el orden del ultimo registro de asignación
 */
      $usuariosventas = array();
      $ocupados = array();
      $sqlu = "SELECT * FROM usuarios_orden ORDER BY uo_orden";
      $resultu = setq($sqlu);
      while ($rowu = $resultu->fetch_array()){
        array_push($usuariosventas, $rowu['uo_orden']);
      }

      $sqluo = "SELECT uo_orden FROM usuarios_orden INNER JOIN crm_leads  ON cl_vendedor = uo_uid WHERE cl_estatus = 'P' AND cl_ucaptura = '".$_SESSION['uid']."'";
      $resultuo = setq($sqluo);
      $totalu = $resultuo->num_rows;
      while ($rowuo = $resultuo->fetch_array()){
        array_push($ocupados, $rowuo['uo_orden']);
      }

      $disponibles = array_merge(array_diff($usuariosventas, $ocupados), array_diff($usuariosventas, $ocupados));
      $ultimovendedor = busca($disponibles[0], "usuarios_orden", "uo_orden", "uo_uid"); //Consultamos el orden del ultimo registro de asignación

      //Comrobamos que el número de vendedores no sea mayor al orden
      if($totalu == $nvendedores){
        //Si el número de asignación llegó al límite de vendedores asignados volvemos a comenzar desde el 1
        $vendedor = busca("1", "usuarios_orden", "uo_orden", "uo_uid");
      } else{
        //Si el número de asignación no ha llegado al límite de vendedores asignados
        $vendedor = $ultimovendedor;
      }    

      //Si el prospecto ya tuvo un registro previo se le asigna el asesor que lo atendió en esa ocasión
      if (!empty($pvendedor)) {
        $vendedor = $pvendedor;
      }
    
                                                                                                                                                    
      $this->model->setdatalead("",$_POST['lead'],$_POST['nmb'],$_POST['correo'],$_POST['telefono'],"A",$_POST['observacion'],$vendedor,$ncaptura,$_SESSION['uid'],"NULL",date("Y-m-d"));
      $this->model->insertlead();
      
      $link = '?modulo=leadscapturados&accion=show&id='.$_POST['lead'];
      redirect($link);
    }
    function finalizar(){
      $this->model->finalizarhoja($_GET['id']);

      redirect('?modulo=leadscapturados&accion=index');
    }

    function fcaptura(){
      $this->model->finalizarcaptura($_GET['id']);
      redirect('?modulo=leadscapturados&accion=show&id='.$_GET['id']);
    }

    function leadperdido(){
      $id = $_POST['id'];
      $motivo = $_POST['motivo'];
      $descripcion = $_POST['otro'];
      $descripcion = strtoupper($descripcion);
      $estatus = busca($id, 'crm_leads', 'cl_id', 'cl_estatus');      
      $this->model->leadperdido($id);
      $this->model->historialperdido($id, $motivo, $descripcion);

      $grupos = '"ADMIN","GERENCIA"';
      echo "
      <script>
      $.ajax({
        url: 'task/notificacionpushgrupos.php',
        method: 'POST',
        data: {
                'grupos': '".$grupos."',
                'estatus': 'X',
                'titulo': 'LEAD SIN NEGOCIACIÓN',
                'idlead': '".$id."'},
      }).done(function(data){
        window.location.href = '?modulo=leadscapturados&accion=index';
      });  
      </script>";
    }
  }

  class modelleadscapturados{
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
    function result($ugen,$estatus,$fini,$ffin,$hini,$hfin,$page, $telefono){ //filtro 
      $bloque = 50;
      $sql = 'SELECT * FROM crm_leads WHERE 1'; 
      if(!empty($estatus)){
        $sql .= ' AND cl_estatus = "'.$estatus.'"';
      } else{
        $sql .= ' AND cl_estatus IN ("R", "A")';
      }
      $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
      if($grupo == 'ADMIN' || $grupo == 'GERENCIA' ){ //|| $grupo == 'MERCA'
        if(!empty($ugen)){
          $sql .= ' AND cl_vendedor = "'.$ugen.'"';
        } else{
          $sql .= '';
        }
      } else {
        if(!empty($ugen) && $ugen != $_SESSION['uid']){
          $sql .= '';
          $caso = 1;
        } else{
          $sql .= ' AND cl_vendedor = "'.$_SESSION['uid'].'"';
        }
      }
      if($telefono){
        $sql.=' AND cl_telefono LIKE "%'.$telefono.'%"';
      }
      if($_SESSION['uid'] != "ADMIN"){
        $sql .= ' AND cl_vendedor != "VENDEDOR8" ';
      }
      $sql.='  ORDER BY 
          CASE WHEN cl_acercamiento = 1 THEN 1 ELSE 0 END, 
        cl_id DESC';

      $sql.=' LIMIT '.($bloque*$page).','.$bloque;
      //$result = setq($sql);
      //if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
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
    function insertlead(){
      $sqlf = '';
      if(!empty($this->id)){
        $sqlf = ' cl_id = "'.$this->id.'", ';
      }
      $sql = 'INSERT INTO crm_leads SET'.$sqlf.'
              cl_lead = "'.$this->lead.'",
              cl_nmb = "'.$this->nmb.'",
              cl_cp = "'.$this->cp.'",
              cl_correo = "'.$this->correo.'",
              cl_telefono = "'.$this->telefono.'" ,
              cl_estatus = "'.$this->estatus.'",
              cl_observacion = "'.$this->observacion.'",
              cl_vendedor = "'.$this->vendedor.'",
              cl_ncaptura = "'.$this->ncaptura.'",
              cl_ucaptura = "'.$this->ucaptura.'",
              cl_hasigna = NULL,
              cl_fasigna = "'.$this->fasigna.'"';
      $result = setq($sql);
      if ($result !== false) {
        $respuesta = 1; //La consulta fue exitosa
      } else{
        $respuesta = 0; //La consulta NO fue exitosa
      }
      return($respuesta);
    }
    
    function updatelead(){
      $sql = 'UPDATE crm_leads SET
              cl_lead = "'.$this->lead.'",         
              cl_nmb = "'.$this->nmb.'",
              cl_telefono = "'.$this->telefono.'" ,
              cl_correo = "'.$this->correo.'",
              cl_estatus = "'.$this->estatus.'",
              cl_observacion = "'.$this->observacion.'",
              cl_vendedor = "'.$this->vendedor.'",
              cl_ncaptura = "'.$this->ncaptura.'",
              cl_ucaptura = "'.$this->ucaptura.'",
              cl_hasigna = "'.$this->hasigna.'",
              cl_fasigna = "'.$this->fasigna.'"
              WHERE cl_id = "'.$this->id.'"';
      setq($sql);
    }

    function resultlead($id){
      $sql = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_estatus = "P" ORDER BY cl_id DESC';
      $this->result = setq($sql);
    }

    function finalizarhoja($id){
      $sql = 'UPDATE crm_capturaleads SET cc_estatus= "F", cc_hfin = "'.date("H:i:s").'", cc_ucancela = "'.$_SESSION['uid'].'" WHERE cc_id = "'.$id.'"';
      setq($sql);

      $sql = 'UPDATE crm_capturaleads SET cc_estatus= "F", cc_hfin = "'.date("H:i:s").'", cc_ucancela = "'.$_SESSION['uid'].'" WHERE cc_id = "'.$id.'"';
      $sql2 = 'DELETE FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_estatus = "P"';
      setq($sql2);
      setq($sql);
    }

  function finalizarcaptura($id){
    $sql = 'UPDATE crm_leads SET cl_estatus= "A", cl_hasigna = "'.date("H:i:s").'" WHERE cl_lead = "'.$id.'"';
    setq($sql);
  }

  function leadperdido($id){
    $sql = 'UPDATE crm_leads SET cl_estatus= "X" WHERE cl_id = "'.$id.'"';
    setq($sql);
  }
  function historialperdido($id, $motivo, $descripcion){
    $fanterior = busca($id, 'crm_leads', 'cl_id', 'cl_fasigna');

    $sql = 'INSERT INTO historial_perdidos SET
            hp_perdido = "'.$id.'",
            hp_uasigna = "'.$_SESSION['uid'].'",
            hp_motivo = "'.$motivo.'",
            hp_fanterior = "'.$fanterior.'",
            hp_descripcion = "'.$descripcion.'"';
    setq($sql);
  }


  }

  class viewleadscapturados{
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
function browse($vendedor,$estatus,$hini,$hfin,$page, $telefono){

        $filtro = '
        <form autocomplete="off" action="?modulo=leadscapturados&accion=index" method="post" id="filtro" class="">
          <div class="mb-5" hidden>
            <label class="mr-sm-2">Page:</label>
            <div class="position-relative has-icon-left">
              <input type="text" id="page" name="page" class="form-control" value="'.$page.'" required>
            </div>
          </div>
          <div class="mb-2">
            <label class="mr-sm-2">Telefono:</label>
            <div class="position-relative has-icon-left">
              <input type="number" id="telefono" name="telefono" placeholder="Buscar por número de telefono" class="form-control" value="'.$telefono.'" required>
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-5" style="max-width: 250px">
            <label for="">Buscar por vendedor:</label>
            <select id="uvendedor" class="form-control" name="uvendedor">
              <option value="">Todos</option>';
              $sqlvend = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_grupo = "VENTAS"';
              $resultvend = setq($sqlvend);
              while($row = $resultvend->fetch_array()){
                if($vendedor == $row['u_id']) $sel = 'selected';
                else $sel = '';
                $filtro .= '<option value="'.$row['u_id'].'">'.$row['u_nmb'].' '.$row['u_apellidos'].'</option> '.$sel.'';
              }
              $filtro .= 
            '</select>
          </div>

          <div class="mb-5" style="max-width: 250px">
            <label for="">Buscar por estatus:</label>
            <select id="estatusfiltro" class="form-control" name="estatus">
              <option value="">Todos</option>
              <option value="X" '.$estatus.'>Sin negociación</option>
              <option value="A" '.$estatus.'>Asignados</option>
              <option value="R" '.$estatus.'>Reasignados</option>
            </select>
          </div>

          <div class="mb-2">
            <label class="mr-sm-2">Fecha:</label>
            <div class="position-relative has-icon-left">
              <input type="date" id="fecha" name="fecha" class="form-control" value="" required>
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-5">
            <label>Acciones:</label><br>
            <button type="button" onclick="mandar(0)" class="btn btn-success"><i class="fas fa-search"></i> Filtrar </button>
            <a href="?modulo=leadscapturados&accion=index"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="fas fa-broom"></i> Limpiar
            </a>
          </div>
        </form>
        ';
        toolbar($_GET['modulo'],'',$filtro, '');
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
          window.location.href = "?modulo=leadscapturados&accion=finalizar&id=" + id;
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
                  <h4 class="modal-title" id="myModalLabel33">Nueva hoja de leadscapturados</h4>
                </div>
                <form method="post" action="?modulo=leadscapturados&accion=insert" autocomplete="off" >
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
        <!-- <input type="text" id="aliasp2" name="aliasp" hidden> -->
        <input type="text" id="idlead" name="idlead" hidden>
      </form>
      ';
      $nombres = array();
      $sqlnmb = 'SELECT u_nmb, u_apellidos, u_id FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A" ';
      $resultnmb = setq($sqlnmb);
      while($rownmb = $resultnmb -> fetch_array()){
        $nombres[$rownmb['u_id']] = $rownmb['u_nmb'].' '.$rownmb['u_apellidos'];
      }


      echo '
      <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table width="90%" class="mb-0 table-hover table-striped" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">
                    <tr>
                      <th><b>Nombre</b></th>
                      <th><b>Teléfono</b></th>
                      <th><b>Correo</b></th>';
                      $sqlsaludo = 'SELECT u_saludo, u_grupo FROM usuarios WHERE u_id = "'.$_SESSION['uid'].'"';
                      $resultsaludo = setqemojis($sqlsaludo);
                      list($texto, $grupo) = $resultsaludo->fetch_array();
                      if($grupo == "ADMIN" || $grupo == "GERENCIA"){
                        echo '<th><b>Vendedor</b></th>';
                      }
                      echo '<th><b>Estatus</b></th>
                      <th><b>Asignado</b></th>
                      <th><b>Acercamiento</b></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>';
                    
                    while($row = $this->model->result->fetch_array()){
                      // if($_SESSION['uid'] == "ADMIN") echo $row['cl_vendedor'].'<br>';
                      $nmbcompleto = $nombres[$row['cl_vendedor']];
                      // Decodificar entidades HTML
                      $html = html_entity_decode($texto);
                      // Reemplazar saltos de línea con saltos de línea reales
                      $texto = str_replace('<br>', "\n", $html);
                      // Eliminar etiquetas HTML restantes
                      $texto = strip_tags($texto); 
                      $texto_codificado = urlencode($texto);
                      if(empty($texto)){
                        /* $phone = $row['cl_telefono']; */
                        $texton = "¡Hola, un gusto saludarte!";
                        $texto_codificado = urlencode($texton);
                      }
                      $numero_de_telefono = "+".$row['cl_code'].$row['cl_telefono']; // Reemplaza con tu número de teléfono en formato internacional
                      $link_whatsapp = "https://api.whatsapp.com/send?phone=$numero_de_telefono&text=$texto_codificado";
                      
                      $phone = '<a href="'.$link_whatsapp.'" target="_blank">'.'+'.$row['cl_code'].$row['cl_telefono'].'&nbsp;
                      <button style="border: 0px; background: white;" title="Contactar por WhatsApp">
                          <i class="fab fa-whatsapp" style="font-size: 20px;"></i>
                      </button>
                      </a>';

                      if($row['cl_estatus'] == "A"){
                        $estatusdesc = "Asignado";
                      } else if($row['cl_estatus'] == "R" && $grupo != "ADMIN" && $grupo != "GERENCIA"){
                        $estatusdesc = "Asignado";
                      } else if($row['cl_estatus'] == "R"){
                        $estatusdesc = "Reasignado";
                      } else {
                        $estatusdesc = "Sin negociación";
                        /* $acciones = '<center><span><em>Lead "Sin negociación"</em></span></center>'; */
                        $acciones = '';
                      }
                      if($row['cl_acercamiento'] == 0){
                        $check = "";
                      } else{
                        $check = " checked disabled";
                        /* $acciones = '<center><span><em>Se hizo acercamiento a este lead</em></span></center>'; */
                      }
echo '
<style>
  .estatus-select option[value="No iniciado"] {
    background-color: #00bcd4; /* Azul claro */
    color: white;
  }
  .estatus-select option[value="Correo Enviado"] {
    background-color: #4caf50; /* Verde */
    color: white;
  }
  .estatus-select option[value="No localizado"] {
    background-color: #f57c00; /* Naranja */
    color: white;
  }
  .estatus-select option[value="Reunion concretada"] {
    background-color: #1565c0; /* Azul fuerte */
    color: white;
  }
  .estatus-select option[value="Grupo Creado"] {
    background-color: #00897b; /* Verde azulado */
    color: white;
  }
  .estatus-select option[value="Buzon"] {
    background-color: #ffeb3b; /* Amarillo */
    color: black;
  }
  .estatus-select option[value="Negado"] {
    background-color: #424242; /* Gris oscuro */
    color: red;
  }
  .estatus-select option[value="Contactar Nuevamente"] {
    background-color: #b71c1c; /* Rojo fuerte */
    color: white;
  }
</style>

<script>
function aplicarColorSelect(select) {
    let color = "#00bcd4"; // default "No iniciado"
    let textColor = "white";

    switch (select.value) {
        case "": // si no tiene valor
        case "No iniciado": 
            color = "#00bcd4"; 
            break;
        case "Correo Enviado": 
            color = "#4caf50"; 
            break;
        case "No localizado": 
            color = "#f57c00"; 
            break;
        case "Reunion concretada": 
            color = "#1565c0"; 
            break;
        case "Grupo Creado": 
            color = "#00897b"; 
            break;
        case "Buzon": 
            color = "#ffeb3b"; 
            textColor = "black"; 
            break;
        case "Negado": 
            color = "#424242"; 
            textColor = "red"; 
            break;
        case "Contactar Nuevamente": 
            color = "#b71c1c"; 
            break;
    }

    select.style.backgroundColor = color;
    select.style.color = textColor;
}

// Aplicar al cargar la página
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".estatus-select").forEach(select => {
        aplicarColorSelect(select);
        select.addEventListener("change", function() {
            aplicarColorSelect(this);
        });
    });
});
</script>
';

$acciones = '
<div class="row">
  <div class="col-5">
    <button type="button" onClick="iniciarTablero('.$row['cl_id'].');" class="btn btn-sm btn-success" data-toggle="tooltip" title="Iniciar un tablero">
      <i class="fas fa-book-open"></i> Iniciar
    </button>
  </div>
  <div class="col-7">
    <select class="form-control form-control-sm estatus-select" onchange="cambiarEstatusLead(this, '.$row['cl_id'].')">
      <option value="No iniciado" '.($row['cl_observacion'] == "No iniciado" ? 'selected' : '').'>No iniciado</option>
      <option value="Correo Enviado" '.($row['cl_observacion'] == "Correo Enviado" ? 'selected' : '').'>Correo Enviado</option>
      <option value="No localizado" '.($row['cl_observacion'] == "No localizado" ? 'selected' : '').'>No localizado</option>
      <option value="Reunion concretada" '.($row['cl_observacion'] == "Reunion concretada" ? 'selected' : '').'>Reunión concretada</option>
      <option value="Grupo Creado" '.($row['cl_observacion'] == "Grupo Creado" ? 'selected' : '').'>Grupo Creado</option>
      <option value="Buzon" '.($row['cl_observacion'] == "Buzon" ? 'selected' : '').'>Buzón</option>
      <option value="Negado" '.($row['cl_observacion'] == "Negado" ? 'selected' : '').'>Negado</option>
      <option value="Contactar Nuevamente" '.($row['cl_observacion'] == "Contactar Nuevamente" ? 'selected' : '').'>Contactar Nuevamente</option>
    </select>
  </div>
</div>';


                      $checkb = '<center><input class="form-check-input consult-check" onclick="marcarAcercamiento('.$row['cl_id'].')" type="checkbox" name="select'.$row['cl_id'].'" id="select'.$row['cl_id'].'" '.$check.'></center>';
                      $hfasignacion = date('d-m-Y',strtotime($row['cl_fasigna'])).' '.$row['cl_hasigna'];

                      echo '<tr>
                        <td>'.$row['cl_nmb'].'</td>
                        <td>'.$phone.'</td>
                        <td>'.$row['cl_correo'].'</td>';
                        if($grupo == "ADMIN" || $grupo == "GERENCIA"){
                          echo '<td>'.$nmbcompleto.'</td>';
                        }
                        echo '<td>'.$estatusdesc.'</td>';
                        echo '<td>'.$hfasignacion.'</td>';
                        echo '<td>'.$checkb.'</td>
                        <td>'.$acciones .'</td>
                      </tr>';
                    }
                  echo '</tbody>';
                echo '</table>
              </center>
            </div>
          </div>
        </div>
      </div>
      
      ';

    
    if($_REQUEST['page'] == 0){
      $hidden = 'style="pointer-events: none;
      background: #70707026;
      color: black;"';
    }else $hidden = "";

    echo '<div class="col-md-12 mt-5 text-xs-center">
      <div class="mb-3">
        <nav aria-label="Page navigation">
          <ul class="pagination">
            <li class="page-item">
              <a class="page-link" onclick="mandar('.($_REQUEST['page']-1).')" aria-label="Previous" '.$hidden.'>
                <span aria-hidden="true">&laquo; Ant</span>
                <span class="sr-only">Anterior</span>
              </a>
            </li>';

            echo '
            <script>
              function mandar(id){
                document.getElementById("page").value = id;
                document.getElementById("filtro").submit();
              }
            </script>';

            //$_REQUEST['categoria'],$_REQUEST['marca'],$_REQUEST['page'],$_REQUEST['unidad'],$_REQUEST['linean'],$_REQUEST['esquema']
    $sqlf = '';
    if(!empty($estatus)){
      $sqlf .= ' cl_estatus = "'.$estatus.'"';
    } else{
      $sqlf .= ' cl_estatus IN ("R", "A")';
    }
    $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
    if($grupo == 'ADMIN' || $grupo == 'GERENCIA'){
      if(!empty($vendedor)){
        $sqlf .= ' AND cl_vendedor = "'.$vendedor.'"';
      } else{
        $sqlf .= '';
      }
    } else {
      if(!empty($vendedor) && $vendedor != $_SESSION['uid']){
        $sqlf .= '';
        $caso = 1;
      } else{
        $sqlf .= ' AND cl_vendedor = "'.$_SESSION['uid'].'"';
      }
    }

    $bloque = 50;
    if(trim($sqlf) == ""){
      $sql = 'SELECT COUNT(*) FROM crm_leads';
    } else {
      $sql = 'SELECT COUNT(*) FROM crm_leads WHERE '.$sqlf.'';
    }
    
    $resultpg = setq($sql);
    list($numg) =  $resultpg->fetch_array();
    $pageres = ceil($numg/$bloque);

      if($pageres>=10){
        if($_REQUEST['page'] == 0) { $min = 1; $nombre = "Inicio";}
        else {$min = $_REQUEST['page']-1; $nombre = "Inicio...";}
        if($min <= 1) $min = 1;

        if($_REQUEST['page'] == ($pageres-1)) $max = ($pageres-1);
        else $max = $_REQUEST['page']+3;
        if($max >= ($pageres-1)) $max = ($pageres-1);

        if($_REQUEST['page'] == 0) $active = "active";
        else $active = "";
        echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar(0)">'.$nombre.'</a></li>';
      }elseif($pageres<=9){
        $max=$pageres;
        $min=0;
      }
      //for($i=0;$i<$pageres;$i++){
      for($i=$min;$i<$max;$i++){
        if($_REQUEST['page'] == $i) $active = "active";
        else $active = "";
        if($i == 0) $nombre = "Inicio";
        else $nombre = $i;
        echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.$i.')">'.$nombre.'</a></li>';
      }
      if($pageres<=9){
      }else{
        if($_REQUEST['page'] == ($pageres-5)) $nombre = ($pageres-2);
        else $nombre = '... '.($pageres-2);
        if($_REQUEST['page'] == $i) $active = "active";
        else $active = "";
        if($_REQUEST['page'] >= ($pageres-4)) echo '';
        else echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.($pageres-2).')">'.$nombre.'</a></li>';
        echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.($pageres-1).')">'.($pageres-1).'</a></li>';
      }
      if($_REQUEST['page'] == ($pageres-1) || $pageres <= 1) $hidden2 = 'style="pointer-events: none;
      background: #70707026;
      color: black;"';
      else $hidden2 = '';
      echo '<li class="page-item">
        <a class="page-link" onclick="mandar('.($_REQUEST['page']+1).')" aria-label="Next" '.$hidden2.'>
          <span aria-hidden="true">Sig &raquo;</span>
          <span class="sr-only">Siguiente</span>
        </a>
      </li>
    </ul>
  </nav>
  </div>
  </div>'; 

      echo '<script>

      /* function mandar(id){
        var telefono = document.getElementById("telefono");
        var estatusfiltro = document.getElementById("estatusfiltro");
        var fecha = document.getElementById("fecha");
        var vendedor = document.getElementById("uvendedor");

        if(id == 0){          
          estatusfiltro.value = "";
          fecha.value = "";
          vendedor.value = "";
          telefono.value = "";
          page.value = "0";
        } 

        //$("#myTable").DataTable().clear().draw();
        $("#myTable").DataTable().destroy();

        $("#myTable").DataTable({
          paging: true,
          scrollY: 400,
          processing: true,
          ordering: false,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatableleadscapturados.php",
            type: "POST",
            datatype: "json",
            data:{ 
              "estatusfiltro": estatusfiltro.value,
              "fecha": fecha.value,
              "vendedor": vendedor.value,
              "page": page.value,
              "telefono": telefono.value
            }
          },
          pageLength: "50",
          responsivePriority: 1,
        });
      } */

      function mandar(id){
        document.getElementById("page").value = id;
        document.getElementById("filtro").submit();
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
        document.getElementById("idlead").value = id;
        formulario.submit();
      }
      
 
      function marcarAcercamiento(id){
        var checkb = document.getElementById("select"+id);
        var estatus = 0;

        // Verifica si está marcado
        if (checkb.checked) {
          estatus = 1;
        } else {
          estatus = 0;
        }

        $.ajax({
          url: "query/marcaracercamiento.php", 
          type: "POST",  
          data: {
            "id": id,
            "estatus": estatus
          },
          success: function(data) {
            //mandar(1);
          },
          error: function(jqXHR, textStatus, errorThrown) {
              // Manejo de errores
              console.log("Error: " + textStatus);
          }
      });
      }
      //mandar(0);

      function cambiarEstatusLead(select, idLead) {
        const nuevoEstatus = select.value;

        $.ajax({
          url: "query/actualiza_estatus_observacion.php",
          type: "POST",
          data: {
            id: idLead,
            estatus: nuevoEstatus
          },
          success: function(response) {
            //Swal.fire({
              //icon: "success",
              //title: "Actualizado",
              //text: "Estatus actualizado correctamente."
            //});
          },
          error: function() {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "No se pudo actualizar el estatus."
            });
          }
        });
      }

    </script>';


     /*  echo '
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
            url: "query/datatableleadscapturados.php",
            type: "POST",
            datatype: "json",
            data:{ 
              "estatusfiltro": "",
              "fecha": ""
            }
            },
            pageLength: "50",
            responsivePriority: 1,
        });
      </script>
      '; */

}
    
function show(){
    $estatus = busca($_GET['id'], "crm_capturaleads", "cc_id", "cc_estatus");
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
    <a href="?modulo=leadscapturados&accion=index">
      <button class="btn btn-sm btn-warning">
        <i class="fa fa-arrow-left"></i> Atrás
      </button>
    </a>';   
    toolbar($_GET['modulo'],$atras,'','');
    echo '
    <style>
    .container { 
      display: flex;
      width: 100%;
      height: 100%;
      padding: 20px;
      justify-content: center;

    .listWrap {
      height: 800px;
      width: 1000px;
    }

    .list {
      list-style: none;
      margin: 0;
      padding: 0;
      display: table;
      white-space: nowrap;
      width: 100%;
    }

    .list tr {
      background-color: #f0f0f0;
      display: table-row;
      color: #5c5c5c;
    }

    .list tr:nth-child(odd) {
      background-color: #f2f2f2;
      display: table-row;
      font-size: 9pt;
      color: #5c5c5c;
    }

    .list td:nth-child(even) {
      background-color: #e8e8e8;
      display: table-row;
      font-size: 9pt;
      color: #5c5c5c;
    }

    .list tr:nth-child(even):hover {
      background-color: #dadada;
    }

    .list tr:nth-child(1) th:first-child {
      border-top-left-radius: 6px;
    }

    .list tr:nth-child(1) th:last-child {
      border-top-right-radius: 6px;
    }

    /*Inicio Estas 2*/

    .list thead tr:nth-child(1) {
      background-color: #201c2b;
      text-transform: uppercase;
      font-size: 8pt;
      font-weight: bold;
      color: #b8b5c0;
    }

    .list thead tr:nth-child(1) th {
      border-bottom: 2px solid #7d5bbe;
      padding: 14px;
    } 
    
    .list thead tr:nth-child(2) th {
      border-bottom: 2px solid #7d5bbe;
      padding: 14px;
    }  

    /*Fin Estas 2*/

    .list th {
      text-align: left;
      display: table-cell;
      padding: 6px;
      vertical-align: middle;
    }

    </style>
    ';

    echo '
    <script>
    $( document ).ready(function() {
      initEvents();
    });
    
    function initEvents() {
        $(".list").hover(function(){
        $(".list li:first span").stop().animate({borderWidth: "5", backgroundColor: "#3f3659", color: "#e5e3e8"},{duration: 170, complete: function() {}} );     
      }, function () {        
        $(".list li:first span").stop().animate({borderWidth: "2", backgroundColor: "#201c2b", color: "#b8b5c0"},{duration: 170, complete: function() {}} ); 
      });
    }
    </script>
      ';

      echo '
      <div class="container">
        <div class="listWrap">
            <table id="mi-tabla" class="list">
                <thead class="">
                  <tr>
                    <th>Nombre</th>
                    <th>Código postal</th>
                    <th>Telefono</th>
                    <th>Correo</th>
                    <th>Observaciones</th>
                    <th></th>
                  </tr>
                ';
                $nvendedores = getmax("uo_orden", "usuarios_orden", false, false); //Consultamos el total de usuarios asignados para ventas
                $read = "readonly";
                $onfocus = "";
                if($this->model->result->num_rows < $nvendedores){
                  $read = "";
                  $onfocus = "
                  //Hacemos focus en el elemento nombre
                  function setFocus() {
                    const inputElement = document.getElementById('nmb');
                    inputElement.focus();
                  }

                  // Ejecutamos el focus cuando se cargue la pagina
                  window.onload = setFocus;

                  const inputs = document.querySelectorAll('.focusable');
                  let currentFocusIndex = 0;

                  document.addEventListener('keydown', function (event) {
                    // Verificar si la tecla presionada es Enter (keyCode 13 o key 'Enter')
                    if (event.key === 'Enter' || event.keyCode === 13) {
                    // Evitar el comportamiento predeterminado de la tecla Enter (evitar el envío de formularios)
                    event.preventDefault();

                    // Obtener el siguiente índice del elemento de entrada de texto
                    let nextFocusIndex = (currentFocusIndex + 1) % inputs.length;

                    // Omitir los inputs con la clase 'form-control' y atributo 'hidden'
                    while (inputs[nextFocusIndex].classList.contains('form-control') && inputs[nextFocusIndex].getAttribute('hidden')) {
                        nextFocusIndex = (nextFocusIndex + 1) % inputs.length;
                    }

                    // Hacer focus en el siguiente elemento de entrada de texto
                    inputs[nextFocusIndex].focus();

                    // Actualizar el índice del elemento de entrada de texto actual
                    currentFocusIndex = nextFocusIndex;
                    }
                  });
                  ";
                }
                echo '
                <tr class="">
                        <input hidden class="form-control" type="text" id="lead" name="lead" value="'.$_GET['id'].'">
                        <input hidden class="form-control" type="text" id="id" name="id" value="">
                    <th><input class="focusable form-control" type="text" id="nmb" name="nmb" value="" placeholder="Nombre completo" '.$read.' required></th>
                    <th><input class="focusable form-control" type="number" id="cp" name="cp" value="" placeholder="Código postal" '.$read.'></th>
                    <th><input class="focusable form-control" type="number" id="telefono" name="telefono" maxlength="10" value="" placeholder="Número de telefono" '.$read.' required></th>
                    <th><input class="focusable form-control" type="email" id="correo" name="correo" value="" placeholder="Correo (Opcional)" '.$read.'></th>
                    <th><input class="focusable form-control" type="text" id="obs" name="obs" placeholder="Observación (Opcional)" value="" '.$read.'></th>
                    <th>';
                    if($estatus == "A"){
                    echo '
                      <button disabled type="button" onClick="addProspecto()" id="btnguardar" class="btn btn-sm btn-success" /><i class="fas fa-save"></i></button>';
                    }
                      echo '
                    </th>
                </tr>
                </thead>';
                
                echo '<tbody id="mi-contenido">';
                $i = 1;
                $registros = $this->model->result->num_rows;
                $telefonos = "";
                while($row = $this->model->result->fetch_array()){
                echo'
                  <tr class="">
                    <th><span>'.$row['cl_nmb'].'</span></th>
                    <th><span>'.$row['cl_telefono'].'</span></th>
                    <th><span>'.$row['cl_correo'].'</span></th>
                    <th><span>'.$row['cl_observacion'].'</span></th>
                    <th>';
                    if($estatus == "A"){
                      echo '<button type="button" onClick="editarLead('.$row['cl_id'].');" class="btn btn-sm btn-primary" /><i class="fas fa-user-edit"></i></button>  
                      <button type="button" onClick="borrarLead('.$row['cl_id'].');" class="btn btn-sm btn-danger" /><i class="fa fa-window-close"></i></button>';
                    }
                    echo '</th>
                  </tr>';
                  if($registros == $i){
                    $telefonos .= $row['cl_telefono'];
                  } else{
                    $telefonos .= $row['cl_telefono'].",";
                  }
                  $i++;
                }
                echo '
                </tbody>';
                echo '<input hidden type="text" id="tel" value="'.$telefonos.'">';
            echo '</table>';

            if($estatus == "A"){
              $disb = "disabled";
              if($registros >= 2){
                $disb = "";
              }
            echo '
            <div style="justify-content: center; display: flex;">
            <!-- Botón para guardar el orden -->
              <button type="button" id="guardarOrden" class="btn btn-primary mt-3" '.$disb.'>Finalizar captura</button>
            </div>';
            }
        echo '</div>
    </div>
      ';
  
    
  
          
    ?>
    <script>
    // Inicializar la tabla arrastrable
    const guardarOrdenBtn = document.getElementById("guardarOrden");

    guardarOrdenBtn.addEventListener("click", function() {
      guardarOrdenBtn.setAttribute("disabled", true);
      Swal.fire({
        icon: "question",
        title: "Atención",
        text: "¿Está seguro de finalizar la captura?",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Estoy seguro'
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.close();
          fcaptura();
        }
      })
    });

    function fcaptura(){
      window.location.href = "?modulo=leadscapturados&accion=fcaptura&id=" + <?php echo $_GET['id'];?>;
    }

    function setFocusInput(input) {
      const inputElement = document.getElementById(input);
      inputElement.focus();
    }

    <?php echo $onfocus; ?>
  
    // Obtener el elemento del telefono
    var phone = document.getElementById("telefono");
    // Obtener el elemento del correo y el botón por sus IDs
    var correoInput = document.getElementById('correo');
    var miBoton = document.getElementById('btnguardar');

    // Expresión regular para validar el correo electrónico
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Agregar un evento de escucha para detectar cambios en el campo de teléfono
    phone.addEventListener('input', function() {
      // Obtener el valor actual del teléfono
      const phoneValue = phone.value;

      // Si el valor tiene más de 10 caracteres, truncarlo a 10 caracteres
      if (phoneValue.length > 10) {
        phone.value = phoneValue.slice(0, 10);
      }

      // Deshabilitar el botón si el valor del teléfono es menor a 10 caracteres o si el correo no es válido
      const correo = correoInput.value;
      const esValido = emailRegex.test(correo);
      miBoton.disabled = phoneValue.length < 10 || !esValido;
    });

    // Agregar un evento de escucha para detectar cambios en el campo de correo
    correoInput.addEventListener('input', function() {
      // Obtener el valor actual del correo
      const correo = correoInput.value;

      // Validar el correo con la expresión regular
      const esValido = emailRegex.test(correo);

      // Deshabilitar el botón si el correo no es válido
      miBoton.disabled = phone.value.length < 10 || !esValido;
    });



    function addProspecto(){
    var guardarOrden = document.getElementById("guardarOrden");
    var lead = document.getElementById("lead").value;
    var nmb = document.getElementById("nmb").value;
    var cp = document.getElementById("cp").value;
    var correo = document.getElementById("correo").value;
    var telefono = document.getElementById("telefono").value;
    var observacion = document.getElementById("obs").value;
    var id = document.getElementById("id").value;
      if(nmb == ""){
        alertSweet("error", "Atención", 'El campo "Nombre" no puede quedar vacío. Verifique');
      }else{
      if(!compararTelefono()){
      $.ajax({
        url: "query/addprospecto.php",
        method: "POST",
        dataType: 'json',
        data: {'nmb': nmb, "cp" : cp, "correo" : correo, "telefono" : telefono, "observacion" : observacion, "lead" : lead, "id" : id},
      })
      .done(function(data){
        document.getElementById("mi-contenido").innerHTML = data.html;
        document.getElementById("id").value = "";
        document.getElementById("nmb").value = "";
        document.getElementById("cp").value = "";
        document.getElementById("correo").value = "";
        document.getElementById("telefono").value = "";
        document.getElementById("obs").value = "";
        document.getElementById("tel").value = data.tel;
        $("#btnguardar").prop("disabled", true);
        if(data.respuesta == 0){
          alertSweet("Error", "Atención", "No se ha podido registrar el prospecto correctamente");
        } else if (data.respuesta == 2){
          if(data.enviar == 1){
            fcaptura();
          }else{
            alertSweet("", "Aviso", "La página está llena");
            document.getElementById("mi-contenido").innerHTML = data.html;
            document.getElementById("nmb").setAttribute("readonly", true);
            document.getElementById("correo").setAttribute("readonly", true);
            document.getElementById("cp").setAttribute("readonly", true);
            document.getElementById("telefono").setAttribute("readonly", true);
            document.getElementById("obs").setAttribute("readonly", true);
          }
        } else if (data.respuesta == 3){
          if(data.enviar == 1){
            fcaptura();
          } else{
            alertSweet("success", "Correcto", "Registro actualizado correctamente");
          }
        }

        if(data.registros >= 2){
          guardarOrden.removeAttribute("disabled");
        } else{
          guardarOrden.setAttribute("disabled", true);
        }
      })
      } else{
        alertSweet("error", "Atención", "Ya existe un registro con el mismo número de telefono en la tabla. Verifique.");
      }
      
    }
    }

    function editarLead(id) {
    var leadId = document.getElementById("id"); // Cambiar el nombre de la variable para evitar conflicto con el parámetro "id"
    var lead = document.getElementById("lead");
    var nmb = document.getElementById("nmb");
    var cp = document.getElementById("cp");
    var correo = document.getElementById("correo");
    var telefono = document.getElementById("telefono");
    var obs = document.getElementById("obs"); // Cambiar el nombre de la variable a "obs"
    var btnguardar = document.getElementById("btnguardar");

    $.ajax({
        url: "query/traerprospecto.php",
        method: "POST",
        dataType: 'json',
        data: {'id': id},
    })
    .done(function(data) {
        leadId.value = data.id; // Aquí debes asignar el valor al campo correcto, que parece ser "leadId" y no "id"
        lead.value = data.lead;
        nmb.value = data.nmb;
        cp.value = data.cp;
        correo.value = data.correo;
        telefono.value = data.telefono;
        obs.value = data.obs; // Aquí debes utilizar el nombre de la variable corregido a "obs"

        // Remover el atributo "disabled" de los campos y el botón
        nmb.removeAttribute("readonly");
        cp.removeAttribute("readonly");
        correo.removeAttribute("readonly");
        telefono.removeAttribute("readonly");
        obs.removeAttribute("readonly");
        btnguardar.removeAttribute("disabled");
    });
    }

    function compararTelefono(){
      var id = document.getElementById("id").value;
      //Se obtiene el valor del campo "telefono"
      var telefono0 = document.getElementById("telefono").value;
      //Se obtiene el valor de la cadena separada por comas del campo "telefonos"
      var telefonosCadena = document.getElementById("tel").value;
      // Separamos los números de teléfono individuales en un arreglo
      var telefonosArray = telefonosCadena.split(',');
      
      // Recorremos y comparamos cada número de teléfono
      var coincidencias = false;
      for (var i = 0; i < telefonosArray.length; i++) {
        var numeroTelefono = telefonosArray[i].trim(); // Eliminamos espacios en blanco
        if (telefono0 === numeroTelefono) {
          coincidencias = true;
          break;
        }
      }

      if(id != ""){
        coincidencias = false;
      }

      return coincidencias;
    }

    function borrarLead(id){
      Swal.fire({
      title: 'Atención',
      html: "¿Estás seguro de borrar este registro?<br>Esta opción es irreversible.",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Estoy seguro'
    }).then((result) => {
      if (result.isConfirmed) {
    var guardarOrden = document.getElementById("guardarOrden");
    var lead = document.getElementById("lead").value;
    var leadId = document.getElementById("id"); // Cambiar el nombre de la variable para evitar conflicto con el parámetro "id"
    var nmb = document.getElementById("nmb");
    var correo = document.getElementById("correo");
    var telefono = document.getElementById("telefono");
    var obs = document.getElementById("obs"); // Cambiar el nombre de la variable a "obs"
    var btnguardar = document.getElementById("btnguardar");
      $.ajax({
        url: "query/eliminarprospecto.php",
        method: "POST",
        dataType: 'json',
        data: {'id': id, "lead" : lead},
    })
    .done(function(data) {
      document.getElementById("mi-contenido").innerHTML = data.html;
      nmb.removeAttribute("readonly");
      correo.removeAttribute("readonly");
      telefono.removeAttribute("readonly");
      obs.removeAttribute("readonly");
      btnguardar.setAttribute("disabled", true);
      document.getElementById("id").value = "";
      document.getElementById("nmb").value = "";
      document.getElementById("cp").value = "";
      document.getElementById("correo").value = "";
      document.getElementById("telefono").value = "";
      document.getElementById("obs").value = "";
      document.getElementById("tel").value = data.tel;
      if(data.registros >= 2){
        guardarOrden.removeAttribute("disabled");
      } else{
        guardarOrden.setAttribute("disabled", true);
      }
    });
    }
    })
    }

    </script>
          <?php

          echo '
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
          window.location.href = "?modulo=leadscapturados&accion=finalizar&id=" + id;
        }
        Swal.close();
      });
    }
    </script>
          ';
}
  }
?>