<?php
/* ini_set('display_errors',1); */

class embarcamiento{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelembarcamiento(isset($obj));
  }
  function index(){
    $this->view = new viewembarcamiento($this->model);
    $this->view->browse();
  }

  function show(){
    $this->view = new viewembarcamiento($this->model);
    $this->view->show();
  }

  function insert(){
    $this->model->setdata('', $_POST['nmb'], $_POST['paqueteria'], $_SESSION['uid'], date('Y-m-d H:i:s'), $_POST['fechaenvio'],'',"A");
    $this->model->insert();
    $id = getmax('e_id', 'embarques', false, false);
    redirect('?modulo=embarcamiento&accion=show&id='.$id);
  }
  function seleccionarenvio(){   
    $idcotiza = $_POST['idcotiza'];
    $this->model->seleccionarenvio($idcotiza);
    redirect('?modulo=embarcamiento&accion=show&id='.$_GET['id']);
  }

  function finalizarembarque(){
    $idcotiza = $_POST['idcotiza'];
    $this->model->seleccionarenvio($idcotiza);
    $embarque = $_GET['id'];
    $this->model->finalizarembarque($embarque);
    redirect('?modulo=embarcamiento&accion=index');
  }

}

class modelembarcamiento{
  function seleccionarenvio($cotizacion){
    $array_cotizaciones = explode(",", $cotizacion);
    $total_cotizaciones = count($array_cotizaciones);
    $numart = 0; //Numero de articulos totales a cambiar de estatus

    for ($i = 0; $i < $total_cotizaciones; $i++) {
        $query = 'SELECT ccc_id FROM crm_cotizacionesc WHERE ccc_estatus = "P" AND ccc_cotizacion = "'.$array_cotizaciones[$i].'"';
        $result = setq($query);
        while($row = $result->fetch_array()){
          if(isset($_POST['select'.$row['ccc_id']])){
            // Estatus A = Articulos de las remisiones embarcados
            $sql = 'UPDATE crm_cotizacionesc SET ccc_estatus = "A", ccc_embarque = "'.$_GET['id'].'" WHERE ccc_id = "'.$row['ccc_id'].'"';
            setq($sql);
            //Procedemos a ajustar existencias
            $numart++;
          }
        }
    }

    if($_POST["numarticulos"] == $numart && ($numart != 0 && $_POST["numarticulos"] != 0)){
      /* $sqle = 'UPDATE embarques SET e_estatus = "F" WHERE e_id = "'.$_GET['id'].'"';
      setq($sqle); */
    }

  }

  function setdata($id, $nmb, $paqueteria, $ugenera, $fini, $ffin, $ufinaliza, $estatus){
    $this->nmb = clearvmayus($nmb);
    $this->paqueteria = $paqueteria;
    $this->ugenera = clearvmayus($ugenera);
    $this->fini = $fini;
    $this->ffin = $ffin;
    $this->ufinaliza = clearvmayus($ufinaliza);
    $this->estatus = clearvmayus($estatus);
}

function insert(){
  $sql = 'INSERT INTO embarques SET
          e_nmb = "'.$this->nmb.'",
          e_paqueteria = "'.$this->paqueteria.'",
          e_ugenera = "'.$this->ugenera.'",
          e_fini = "'.$this->fini.'",
          e_ffin = "'.$this->ffin.'",
          e_estatus = "'.$this->estatus.'"';
  setq($sql);
}

function finalizarembarque($embarque){
  $sql = 'UPDATE embarques SET e_estatus = "F" WHERE e_id = "'.$embarque.'"';
  setq($sql);
}
}

class viewembarcamiento{
  function __construct($model){
    ?>
    <script>
    function checkguardar(){
      document.getElementById("sendform").innerHTML = "Guardando";
      document.getElementById("sendform").disabled = true;
      return true;
    }
    </script>
    <?php
  }

  function browse(){
  $nuevo = '
  <a data-fancybox data-type="ajax" data-src="popup/setembarque.php" href="javascript:;">
    <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Nuevo embarque"><i class="fas fa-plus"></i> Nuevo embarque</button>
  </a>';

  $filtro = '
  <form class="form-inline" role="form" method="post" action="?modulo=articulos&accion=index" id="filtro">
  <div class="mb-5">
  <label class="mr-sm-2">Registra:</label>
  <select name="nmb" id="nmb" class="form-control">
    <option value="">TODOS</option>';
    $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $filtro.= '<option value="'.$row['u_id'].'">'.$row['u_id'].'</option>';
    }
    $filtro.= '
  </select>
  </div>
  <div class="mb-5" hidden>
    <label class="mr-sm-2">Page:</label>
    <div class="position-relative has-icon-left">
      <input type="text" id="page" name="page" class="form-control" value="" required>
    </div>
  </div>
  <div class="mb-5">
    <label class="mr-sm-2">Fecha desde:</label>
    <div class="position-relative has-icon-left">
      <input type="date" id="fini" name="fini" class="form-control" value="" required>
      <input hidden type="date" id="fini2" class="form-control" value="'.date('Y-m-d',strtotime('-20 days')).'">
      <div class="form-control-position">
        <i class="icon-calendar5"></i>
      </div>
    </div>
  </div>
  <div class="mb-5">
    <label class="mr-sm-2">Fecha hasta:</label>
    <div class="position-relative has-icon-left">
      <input type="date" id="ffin" name="ffin" class="form-control" value="" required>
      <input hidden type="date" id="ffin2" class="form-control" value="'.date('Y-m-d',strtotime('last day of this month')).'">
      <div class="form-control-position">
        <i class="icon-calendar5"></i>
      </div>
    </div>
  </div>
    <div class="mb-5">
      <label for="">Acciones:</label> <br>
    <button type="button" onclick="mandar(0)" class="btn btn-info">
      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-redo"></i> Actualizar
    </button>
    <button type="button" onclick="mandar(1)" class="btn btn-warning">
      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
    </button>
    </div>
  </form>';

  toolbar($_GET['modulo'],"","",$nuevo);

    ?>
    <div class="card mt-3">
    <div class="card-body">
    <?php
    echo '
    <div class=" col-12 table-responsive">
      <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
      <thead class="bg-light-blue bg-darken-2 ">
        <tr>
          <th><b>Nombre</b></th>
          <th><b>Paquetería</b></th>
          <th><b>Genera</b></th>
          <th><b>Fecha creación</b></th>
          <th><b>Fecha de envío programada</b></th>
          <th><b>Embarcó</b></th>
          <th><b>Estatus</b></th>
          <th><b></b></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      </table>
      </div>
    </div>';

    echo '
    <script>
    function mandar(id){
      //var table = $("#myTable").DataTable();
      $("#myTable").DataTable().clear().draw();
      $("#myTable").DataTable().destroy();
      
      var windowHeight = $(window).height();

      $("#myTable").DataTable( {
        paging: true,
        scrollY: windowHeight * 0.5,
        processing: true,
        serverside: true,
        ordering: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatableembarcamiento.php",
          type: "POST",
          datatype: "json"
        },
        pageLength: "50",
        responsivePriority: 1,
      });
    }
    
    mandar(1);
  </script>
  ';
  }

  function show(){
    $embarque = $_GET['id'];
    $estatusembarque = busca($embarque, 'embarques', 'e_id','e_estatus');
    $idpaqueteria = busca($embarque, 'embarques', 'e_id', 'e_paqueteria');
    $paqt = $idpaqueteria;
    if($idpaqueteria == -1){
      $idpaqueteria = 0;
      $sqlf = 'AND ccc_paqueteria = "'.$idpaqueteria.'" AND ccc_tipoenvio = "C" ';
    } else{
      $sqlf = 'AND ccc_paqueteria = "'.$idpaqueteria.'" AND ccc_tipoenvio != "C" ';
    }
    $nuevo = '
    <a data-fancybox data-type="ajax" data-src="popup/setembarque.php" href="javascript:;">
      <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Nuevo embarque"><i class="fas fa-plus"></i> Nuevo embarque</button>
    </a>';
  
    $filtro = '
    <form class="form-inline" role="form" method="post" action="?modulo=articulos&accion=index" id="filtro">
    <div class="mb-5">
    <label class="mr-sm-2">Registra:</label>
    <select name="nmb" id="nmb" class="form-control">
      <option value="">TODOS</option>';
      $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $filtro.= '<option value="'.$row['u_id'].'">'.$row['u_id'].'</option>';
      }
      $filtro.= '
    </select>
    </div>
    <div class="mb-5" hidden>
      <label class="mr-sm-2">Page:</label>
      <div class="position-relative has-icon-left">
        <input type="text" id="page" name="page" class="form-control" value="" required>
      </div>
    </div>
    <div class="mb-5">
      <label class="mr-sm-2">Fecha desde:</label>
      <div class="position-relative has-icon-left">
        <input type="date" id="fini" name="fini" class="form-control" value="" required>
        <input hidden type="date" id="fini2" class="form-control" value="'.date('Y-m-d',strtotime('-20 days')).'">
        <div class="form-control-position">
          <i class="icon-calendar5"></i>
        </div>
      </div>
    </div>
    <div class="mb-5">
      <label class="mr-sm-2">Fecha hasta:</label>
      <div class="position-relative has-icon-left">
        <input type="date" id="ffin" name="ffin" class="form-control" value="" required>
        <input hidden type="date" id="ffin2" class="form-control" value="'.date('Y-m-d',strtotime('last day of this month')).'">
        <div class="form-control-position">
          <i class="icon-calendar5"></i>
        </div>
      </div>
    </div>
      <div class="mb-5">
        <label for="">Acciones:</label> <br>
      <button type="button" onclick="mandar(0)" class="btn btn-info">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-redo"></i> Actualizar
      </button>
      <button type="button" onclick="mandar(1)" class="btn btn-warning">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
      </button>
      </div>
    </form>';

    $guardar = '
    <button type="button" class="btn btn-sm btn-primary" onclick="clickGuardar();"><i class="fas fa-save"></i>Guardar</button>
    ';

    $atras = '
    <a href="?modulo=embarcamiento&accion=index">
      <button type="button" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>Atrás</button>    
    </a>
    ';

    if($estatusembarque != "F"){
      $finalizar = '
        <button type="button" onclick="finalizarembarque('.$embarque.')"; class="btn btn-sm btn-info"><i class="fas fa-check"></i>Finalizar embarque</button>    
      ';
    } else{
      $finalizar = '';
    }
  
    toolbar($_GET['modulo'], $atras,'',$guardar,$finalizar);

    if($estatusembarque == "F"){
      echo '
      <div class="alert alert-danger" role="alert">
        <center>Este embarque está finalizado</center>
      </div>
      ';
    }

    //INICIO DE LA FUNCIÓN PARA EL LLENADO DE LA TABLA POR CELDAS
  function imprimircheckboxes($k, $articulotipo, $background, $idremision, $producto, $peso, $guia) {
    /* $color = ' style="background: #c8c8c8;"'; */                         
    $pqt = busca($k, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
    $fecha = busca($idremision, 'remisiones', 'r_id', 'r_fliquidacion');
    $folio = busca($idremision, 'remisiones', 'r_id', 'r_folio');
    $cliente = busca($idremision, 'remisiones INNER JOIN crm_clientes ON c_id = r_cliente', 'r_id', 'c_nmb');
    if($pqt == 0){
    if($articulotipo == "C"){
      $paqueteria = 'RECOGE EN SUCURSAL';
    } else {
      $paqueteria = 'OCURRE';
    }

    } else{
    $paqueteria = busca($pqt, 'paqueterias', 'p_id', 'p_nmb');
    }

    $check = !empty($background) ? ' checked' : '';
    $disa = !empty($background) ? ' disabled' : '';

    $parametro = "''"."''";
    if(intval($peso) > 60){
    $parametros = $k.', '.$k.'2';
    } else{
    $parametros = $k.', '.$parametro.', '.$guia;
    }

    $rtn .= 
        '<td>
          <span>'.$folio.'</span>
        </td>
        <td>
          <span>'.$cliente.'</span>
        </td>
        ';
    $rtn .= $producto;
    $rtn .= '<td>
        <center>
            <span>'.$paqueteria.'</span>
        </center>
        </td>
        <td>
        <center>
            <span>'.$fecha.'</span>
        </center>
        </td>
        ';
    $rtn .= '
        <td rowspan="2">
        <center>
            <input class="form-check-input" type="checkbox" checked disabled>
        </center>
        </td>
        <td rowspan="2">
        <center>
            <input class="form-check-input" onclick="selectOne('.$parametros.')" type="checkbox" name="select'.$k.'" id="select'.$k.'" '.$check.' '.$disa.'>
        </center>
        </td>';
    return $rtn;
    } 
    //FIN DE LA FUNCIÓN PARA EL LLENADO DE LA TABLA POR CELDAS


    echo '
    <style>
    #myTableO {
      width: 100%;
      border-collapse: collapse;
    }

    #myTableO th, #myTableO td {
      padding: 8px;
      text-align: left;
      border: 1px solid #ddd;
      /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
    }

    #myTableO tbody tr:nth-child(odd) td {
      /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
    }

    #myTableO tbody tr:nth-child(even) td {
      /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
    }
    </style>
    ';
    //INICIA LA TABLA DE PRODUCTOS DE TODAS LAS REMISIONES
  echo'
  <div class="container mt-1 ">
  <form id="finalizaForm" action="?modulo=embarcamiento&accion=seleccionarenvio&id='.$_GET['id'].'" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
  <div class="row">
  <div class="row mt-5" style="background: white;">
  <div class="card-body">
  <center>
    <div class="table-responsive col-12">
    <b><i><span id="totalreg" style="justify-content: left; display: flex; color: black;"></span></i></b><br>
    <table class="table table-hover" id="myTableO">
    <thead class="thead-active bg-primary text-white">
      <tr>
        <th width="10%">&nbsp;&nbsp;Remisión</th>
        <th width="15%">Cliente</th>
        <th width="30%">&nbsp;&nbsp;Producto</th>
        <th width="15%">Paquetería</th>
        <th width="10%">Fecha libreacion</th>
        <th width="10%">Preparación</th>
        <th width="10%">Embarcar</th>
      </tr>
    </thead>
    <tbody>';
    
    //COMIENZA EL LLENADO DE TABLA CON LOS ARTICULOS
    $querycot = 'SELECT cc_id, cc_tablero FROM crm_cotizaciones WHERE cc_estatus = "V"';
    /* $querycot = 'SELECT cc_id FROM crm_cotizaciones INNER JOIN crm_tableros ON cc_tablero = ct_id WHERE cc_estatus = "V" AND ct_estatus = "F"'; */
    $numarticulos = 0;
    $resultcot = setq($querycot);
    $n = 0;
    $numarticulos = 0;
    $resultcot = setq($querycot);

    while($rowcot = $resultcot->fetch_array()){
    $totalremisiones = intval(busca($rowcot['cc_tablero'], 'remisiones', 'r_tablero', 'COUNT(*)'));
    $remisionesf = intval(busca($rowcot['cc_tablero'], 'remisiones', 'r_estatus = "F" AND r_tablero', 'COUNT(*)'));
    /* if($totalremisiones == $remisionesf){ */
    if($totalremisiones == $remisionesf){
    $idcotiza = $rowcot['cc_id'];
    $idremision = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_remision');
    

    //CASO DE RECOGE CLIENTE
    if($paqt != -1){
    $queryguiac = 'SELECT DISTINCT ccc_guia AS ccc_guia FROM crm_cotizacionesc INNER JOIN guias_articulos ON ccc_guia = ga_id
              WHERE ccc_cotizacion = "'.$idcotiza.'" AND ga_estatus != "A" AND ga_paqueteria = "'.$idpaqueteria.'"
              ORDER BY ccc_cotizaciond ASC;'; 
    $resultguiac = setq($queryguiac);

    while($rowguiac = $resultguiac->fetch_array()){
      $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia='.$rowguiac['ccc_guia'].'&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>';
      $nmbguia = busca($rowguiac['ccc_guia'], 'guias_articulos', 'ga_id', 'ga_nmb');
      $contador = 0;
      $listaArt = '
      <tr style="background: #008eff47 !important;">
        <td colspan="5">&nbsp;&nbsp;NÚMERO DE GUÍA - '.$nmbguia.' </td>
        <td colspan="2">'.$boton.'</td>
      </tr>';

    $query0 = 'SELECT DISTINCT ccc_cotizaciond AS ccc_cotizaciond FROM crm_cotizacionesc 
              WHERE ccc_cotizacion = "'.$idcotiza.'" AND ccc_guia = "'.$rowguiac['ccc_guia'].'"
              ORDER BY ccc_cotizaciond ASC;';                   
    $result0 = setq($query0);
    $k = 0;
    $resp = 0;
    if($n == 0){
      $tcotiza .= $idcotiza;  
      $tremision .= $idremision;
    } else{
      $tcotiza .= ",".$idcotiza;
      $tremision .= ",".$idremision;
    }
    $direnvio = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_direnvio');
    $direccion = busca($direnvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
    echo '<input type="hidden" value="'.$tcotiza.'" name="idcotiza">';
    echo '<input type="hidden" value="'.$tremision.'" name="idremision">';
    while($row0 = $result0->fetch_array()){
      //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
      $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, cdm_cantidad, cdm_id, cdm_articulo FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo WHERE cdm_id = "'.$row0['ccc_cotizaciond'].'"'; 
      $resultcmb = setq($sqlcmb);
      list($aid, $anmb, $tipoprod, $cantidad, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();
      if($tipoprod == "M") {
      for($j = 1; $j <= intval($cantidad); $j++){

      /* $queryd = 'SELECT * FROM crm_cotizacionesc WHERE ccc_cotizacion = "'.$idcotiza.'" AND ccc_cotizaciond = "'.$row0['ccc_cotizaciond'].'" AND ccc_combo = "'.$j.'" AND (ccc_estatus = "P" OR ccc_estatus = "A" OR ccc_estatus = "F") '.$sqlf.' AND (ccc_embarque is NULL OR ccc_embarque = "'.$_GET['id'].'") ORDER BY ccc_id'; */
      $queryd = 'SELECT * FROM crm_cotizacionesc WHERE ccc_cotizacion = "'.$idcotiza.'" AND ccc_cotizaciond = "'.$row0['ccc_cotizaciond'].'" AND ccc_combo = "'.$j.'" AND (ccc_estatus = "P" OR ccc_estatus = "A" OR ccc_estatus = "F") '.$sqlf.' AND (ccc_embarque is NULL OR ccc_embarque = "'.$_GET['id'].'") ORDER BY ccc_id';
      $resultd = setq($queryd);
      $listaArt .= '<tr style="background: #FFEE85 !important;"><td colspan="7">&nbsp;&nbsp;PAQUETE - '.$anmb.' '.$j.' </td></tr>';
      
      $res = 0;
      while($rowcb = $resultd->fetch_array()){
        if($res == 0){
        $artname = busca($rowcb['ccc_articulo'], "articulos", "a_id", "a_nmb");
        $var = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'COUNT(*)');
        if($var > 0) {
          $artname .= " ".busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'av_nmb');
        }
        $tantos = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "COUNT(*)");
        if($tantos > 1){
          for($h = 1; $h <= intval($tantos); $h++){
            $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_id");
            $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_tipoenvio");
            $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
            $pqteria = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
            $embarque = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_embarque');
            $muestra = false;
            if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && ($embarque == $_GET['id'] || empty($embarque))){
              $muestra = true;
            }
            
            if($estatusembarque == "F"){
              if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && $embarque == $_GET['id']){
                $muestra = true;
              } else{
                $muestra = false;
              }
            }
            
            if($muestra){
              $back = ($status == "A") ? ' style="background-color: rgb(204, 255, 204);"' : '';
              $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' '.$h.' </td>';
              $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
              $listaArt .= '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $rowguiac['ccc_guia']) . '</tr>';
              $listaArt .= '<tr id="tr'.$p1.'2"'.$back.' style="background-color: #d8d8d8;"><td colspan="5">Dirección: '.$direccion.'</td></tr>';
              $contador++;
              $numarticulos++;
            }

          }
          $res = intval($tantos);
        } else{
          $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_id");
          $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_tipoenvio");
          $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
          $pqteria = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
          $embarque = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_embarque');
          $muestra = false;
            if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && ($embarque == $_GET['id'] || empty($embarque))){
              $muestra = true;
            }
            
            if($estatusembarque == "F"){
              if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && $embarque == $_GET['id']){
                $muestra = true;
              } else{
                $muestra = false;
              }
            }
            
          if($muestra){
          $back = ($status == "A") ? ' style="background-color: rgb(204, 255, 204);"' : '';
          $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' </td>';
          $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
          $listaArt .= '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $rowguiac['ccc_guia']) . '</tr>';
          $listaArt .= '<tr id="tr'.$p1.'2"'.$back.' style="background-color: #d8d8d8;"><td colspan="5">Dirección: '.$direccion.'</td></tr>';
          $contador++;
          $res = 0;
          $numarticulos++;
          }
        }
      } else{
        $res--;
      } 
      }
      $listaArt .= '<tr style="background: #FFEE85" ><td colspan="7"></td></tr>';
      if($contador == 0){
        $listaArt = '';
      }
      }
      } else {
          for($h = 1; $h <= intval($cantidad); $h++){
            $p1 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_id");                
            $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
            $pqteria = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
            $embarque = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_embarque');
            $muestra = false;
            if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && ($embarque == $_GET['id'] || empty($embarque))){
              $muestra = true;
            }
            
            if($estatusembarque == "F"){
              if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && $embarque == $_GET['id']){
                $muestra = true;
              } else{
                $muestra = false;
              }
            }
            
            if($muestra){
            $p2 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_tipoenvio");
            $back = ($status == "A") ? ' style="background-color: rgb(204, 255, 204);"' : '';
            $producto = '<td> '.$anmb.' '.$h.'</td>';
            $peso = busca($cdmarticulo, 'articulos', 'a_id', 'a_peso');
            /* echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' </td>' . imprimircheckboxes($p1.$h, $p2, $aid.$j) . '</tr>'; */
            $listaArt .= '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $rowguiac['ccc_guia']) . '</tr>';
            $listaArt .=  '<tr id="tr'.$p1.'2"'.$back.' style="background-color: #d8d8d8;"><td colspan="5">Dirección: '.$direccion.'</td></tr>';
            $contador++;
            $numarticulos++;

            //Inicia listado de artículos ligados de este artículo que no es un paquete
            $sqligados = 'SELECT ccc_id, ccc_tipoenvio, ccc_articulo, ccc_modelo, ccc_numero, ccc_estatus, ccc_paqueteria, ccc_embarque FROM crm_cotizacionesc WHERE ccc_ligado = "'.$p1.'"';
            $resultligado = setq($sqligados);
            while($rowligado = $resultligado->fetch_array()){
              $p1 = $rowligado['ccc_id'];
              $status = $rowligado['ccc_estatus'];
              $pqteria = $rowligado['ccc_paqueteria'];
              $embarque = $rowligado['ccc_embarque'];

              $muestra = false;
              if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && ($embarque == $_GET['id'] || empty($embarque))){
                $muestra = true;
              }
              
              if($estatusembarque == "F"){
                if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && $embarque == $_GET['id']){
                  $muestra = true;
                } else{
                  $muestra = false;
                }
              }
              
              if($muestra){
                $p2 = $rowligado['ccc_tipoenvio'];
                $back = ($status == "A") ? ' style="background-color: rgb(204, 255, 204);"' : '';
                $artnameligado = busca($rowligado['ccc_articulo'], "articulos", "a_id", "a_nmb");
                $var = busca($rowligado['ccc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['ccc_modelo'] . '" AND av_articulo', 'COUNT(*)');
                if ($var > 0) {
                  $artnameligado .= " ".busca($rowligado['ccc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['ccc_modelo'] . '" AND av_articulo', 'av_nmb');
                }
                $artnameg = $artnameligado." ".$rowligado['ccc_numero']." DEL ".$anmb;
                $producto = '<td>' . $artnameg . ' ' . $h . ' </td>';
                $peso = busca($rowligado['ccc_articulo'], 'articulos', 'a_id', 'a_peso');

                $listaArt .= '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $rowguiac['ccc_guia']) . '</tr>';
                $listaArt .= '<tr id="tr'.$p1.'2"'.$back.' style="background-color: #d8d8d8;"><td colspan="5">Dirección: '.$direccion.'</td></tr>';
                $contador++;
                $numarticulos++;
              }
            }
            // Finaliza listado de artículos ligados de este artículo que no es un paquete
            }
          }
          $resp = $tantos;
      }     
      }
      $n++;
      $listaArt .= '<tr style="background: #008eff47" ><td colspan="7"></td></tr>';
      if($contador == 0){
        $listaArt = '<tr style="background: #008eff47" ><td colspan="7"><center>No hay artículos para mostrar<center></td></tr>';
      }
      echo $listaArt;
      }
    } else {
      //INICIA LISTADO DE PRODUCTOS DE RECOGE CLIENTE
      $query0 = 'SELECT DISTINCT ccc_cotizaciond AS ccc_cotizaciond FROM crm_cotizacionesc 
                WHERE ccc_cotizacion = "'.$idcotiza.'"
                ORDER BY ccc_cotizaciond ASC;';                   
      $result0 = setq($query0);
      $k = 0;
      $resp = 0;
      if($n == 0){
        $tcotiza .= $idcotiza;  
        $tremision .= $idremision;
      } else{
        $tcotiza .= ",".$idcotiza;
        $tremision .= ",".$idremision;
      }
      $direnvio = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_direnvio');
      $direccion = busca($direnvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
      echo '<input type="hidden" value="'.$tcotiza.'" name="idcotiza">';
      echo '<input type="hidden" value="'.$tremision.'" name="idremision">';
      while($row0 = $result0->fetch_array()){
        //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
        $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, cdm_cantidad, cdm_id, cdm_articulo FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo WHERE cdm_id = "'.$row0['ccc_cotizaciond'].'"'; 
        $resultcmb = setq($sqlcmb);
        list($aid, $anmb, $tipoprod, $cantidad, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();
        if($tipoprod == "M") {
        for($j = 1; $j <= intval($cantidad); $j++){
  
        /* $queryd = 'SELECT * FROM crm_cotizacionesc WHERE ccc_cotizacion = "'.$idcotiza.'" AND ccc_cotizaciond = "'.$row0['ccc_cotizaciond'].'" AND ccc_combo = "'.$j.'" AND (ccc_estatus = "P" OR ccc_estatus = "A" OR ccc_estatus = "F") '.$sqlf.' AND (ccc_embarque is NULL OR ccc_embarque = "'.$_GET['id'].'") ORDER BY ccc_id'; */
        $queryd = 'SELECT * FROM crm_cotizacionesc WHERE ccc_cotizacion = "'.$idcotiza.'" AND ccc_cotizaciond = "'.$row0['ccc_cotizaciond'].'" AND ccc_combo = "'.$j.'" AND (ccc_estatus = "P" OR ccc_estatus = "A" OR ccc_estatus = "F") '.$sqlf.' AND (ccc_embarque is NULL OR ccc_embarque = "'.$_GET['id'].'") ORDER BY ccc_id';
        $resultd = setq($queryd);
        $contador = 0;
        $listaArt = '<tr style="background: #FFEE85 !important;"><td colspan="7">&nbsp;&nbsp;PAQUETE - '.$anmb.' '.$j.' </td></tr>';
        
        
        $res = 0;
        while($rowcb = $resultd->fetch_array()){
          if($res == 0){
          $artname = busca($rowcb['ccc_articulo'], "articulos", "a_id", "a_nmb");
          $var = busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'COUNT(*)');
          if($var > 0) {
            $artname .= " ".busca($rowcb['ccc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowcb['ccc_modelo'].'" AND av_articulo', 'av_nmb');
          }
          $tantos = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "COUNT(*)");
          if($tantos > 1){
            for($h = 1; $h <= intval($tantos); $h++){
              $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_id");
              $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_tipoenvio");
              $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
              $pqteria = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
              $embarque = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_embarque');
              $muestra = false;
              if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && ($embarque == $_GET['id'] || empty($embarque))){
                $muestra = true;
              }
              
              if($estatusembarque == "F"){
                if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && $embarque == $_GET['id']){
                  $muestra = true;
                } else{
                  $muestra = false;
                }
              }
              
              if($muestra){
              $back = ($status == "A") ? ' style="background-color: rgb(204, 255, 204);"' : '';
              $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' '.$h.' </td>';
              $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
              $listaArt .= '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $rowguiac['ccc_guia']) . '</tr>';
              $listaArt .= '<tr id="tr'.$p1.'2"'.$back.' style="background-color: #d8d8d8;"><td colspan="5">Dirección: '.$direccion.'</td></tr>';
              $contador++;
              $numarticulos++;
              }
            }
            $res = intval($tantos);
          } else{
            $p1 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_id");
            $p2 = busca($rowcb['ccc_articulo'], "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '".$rowcb['ccc_modelo']."' AND ccc_combo = '".$j."' AND ccc_articulo", "ccc_tipoenvio");
            $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
            $pqteria = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
            $embarque = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_embarque');
            $muestra = false;
              if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && ($embarque == $_GET['id'] || empty($embarque))){
                $muestra = true;
              }
              
              if($estatusembarque == "F"){
                if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && $embarque == $_GET['id']){
                  $muestra = true;
                } else{
                  $muestra = false;
                }
              }
              
            if($muestra){
            $back = ($status == "A") ? ' style="background-color: rgb(204, 255, 204);"' : '';
            $producto = '<td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' </td>';
            $peso = busca($rowcb['ccc_articulo'], 'articulos', 'a_id', 'a_peso');
            $listaArt .= '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $rowguiac['ccc_guia']) . '</tr>';
            $listaArt .= '<tr id="tr'.$p1.'2"'.$back.' style="background-color: #d8d8d8;"><td colspan="5">Dirección: '.$direccion.'</td></tr>';
            $contador++;
            $res = 0;
            $numarticulos++;
            }
          }
        } else{
          $res--;
        } 
        }
        $listaArt .= '<tr style="background: #FFEE85" ><td colspan="7"></td></tr>';
          if($contador == 0){
            $listaArt = '';
          }
          echo $listaArt;
        }
        } else {
            for($h = 1; $h <= intval($cantidad); $h++){
              $p1 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_id");                
              $status = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_estatus');
              $pqteria = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_paqueteria');
              $embarque = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_embarque');
              $muestra = false;
              if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && ($embarque == $_GET['id'] || empty($embarque))){
                $muestra = true;
              }
              
              if($estatusembarque == "F"){
                if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && $embarque == $_GET['id']){
                  $muestra = true;
                } else{
                  $muestra = false;
                }
              }
              
              if($muestra){
              $p2 = busca($cdmarticulo, "crm_cotizacionesc", "ccc_cotizacion = '".$idcotiza."' AND ccc_cotizaciond = '".$cdmid."' AND ccc_modelo = '' AND ccc_numero = '".$h."' AND ccc_articulo", "ccc_tipoenvio");
              $back = ($status == "A") ? ' style="background-color: rgb(204, 255, 204);"' : '';
              $producto = '<td> Z1 '.$anmb.' '.$h.'</td>';
              $peso = busca($cdmarticulo, 'articulos', 'a_id', 'a_peso');
              /* echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp; * '.$artname.' </td>' . imprimircheckboxes($p1.$h, $p2, $aid.$j) . '</tr>'; */
              echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $rowguiac['ccc_guia']) . '</tr>';
              echo '<tr id="tr'.$p1.'2"'.$back.' style="background-color: #d8d8d8;"><td colspan="5">Dirección: '.$direccion.'</td></tr>';
              $numarticulos++;

              //Inicia listado de artículos ligados de este artículo que no es un paquete
              $sqligados = 'SELECT ccc_id, ccc_tipoenvio, ccc_articulo, ccc_modelo, ccc_numero, ccc_estatus, ccc_paqueteria, ccc_embarque FROM crm_cotizacionesc WHERE ccc_ligado = "'.$p1.'"';
              $resultligado = setq($sqligados);
              while($rowligado = $resultligado->fetch_array()){
                $p1 = $rowligado['ccc_id'];
                $p2 = $rowligado['ccc_tipoenvio'];
                
                if ($p2 == "C") {
                $status = $rowligado['ccc_estatus'];
                $pqteria = $rowligado['ccc_paqueteria'];
                $embarque = $rowligado['ccc_embarque'];
                //$embarque = busca($p1, 'crm_cotizacionesc', 'ccc_id', 'ccc_embarque');
                $muestra = false;
                  if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && ($embarque == $_GET['id'] || empty($embarque))){
                    $muestra = true;
                  }
                  
                  if($estatusembarque == "F"){
                    if(($status == "P" || $status == "A") && $idpaqueteria == $pqteria && $embarque == $_GET['id']){
                      $muestra = true;
                    } else{
                      $muestra = false;
                    }
                  }
                  
                  if($muestra){
                  $artnameligado = busca($rowligado['ccc_articulo'], "articulos", "a_id", "a_nmb");
                  $var = busca($rowligado['ccc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['ccc_modelo'] . '" AND av_articulo', 'COUNT(*)');
                  if ($var > 0) {
                    $artnameligado .= " ".busca($rowligado['ccc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['ccc_modelo'] . '" AND av_articulo', 'av_nmb');
                  }
                  $artnameg = $artnameligado." ".$rowligado['ccc_numero']." DEL ".$anmb;
                  $back = ($status == "A") ? ' style="background-color: rgb(204, 255, 204);"' : '';
                  $producto = '<td>** ' . $artnameg . ' ' . $h . ' </td>';
                  $peso = busca($rowligado['ccc_articulo'], 'articulos', 'a_id', 'a_peso');

                  echo '<tr id="tr'.$p1.'"'.$back.'>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $rowguiac['ccc_guia']) . '</tr>';
                  echo '<tr id="tr'.$p1.'2"'.$back.' style="background-color: #d8d8d8;"><td colspan="5">Dirección: '.$direccion.'</td></tr>';
                  $numarticulos++;
                }
                } 
              }
              // Finaliza listado de artículos ligados de este artículo que no es un paquete
              }
            }
            $resp = $tantos;
        }     
        }
        $n++;
        //FINALIZA LISTADO DE PRODUCTOS DE RECOGE CLIENTE
      }

      }
    }
    echo '
    <script>
    
      var totalreg = document.getElementById("totalreg");
      totalreg.textContent = "Número de artículos: '.$numarticulos.'";
    
    </script>
    ';

    if($numarticulos == 0){
      echo '<tr style="background: #008eff47" ><td colspan="7"><center>No hay artículos para mostrar<center></td></tr>';
    }
    echo '</tbody>
    </table>
    <input type="hidden" id="cant" name ="cant" value="'.$k--.'">
    <input type="hidden" value="'.$numarticulos.'" name="numarticulos">
    </form>
    </div>
  </center>
  </div>';
  //FIN DE LA TABLA DE PRODUCTOS DE TODAS LAS REMISIONES
  ?>
  <script>
    function selectOne(k, j = '', guia = ''){
    //Consulta AJAX
    var checkbox = document.getElementById("select"+k);
    $.ajax({
      url: "query/verificarsalidaarticulos.php",
      method: "POST",
      data: {
        guia: guia
      },
    }).done(function(data){
    selectOneTwo(k, '');
    if(data == 1){
      checkbox.checked = false;
      Swal.fire(
        'Atención',
        'Es necesario que el gerente confirme la salida de estos productos',
        'error'
      )
    }
    }); 
    }

    function selectOneTwo(k, j = ''){
    var checkbox = document.getElementById("select"+k);
    var input = document.getElementById("in"+k);
    /* var btn = document.getElementById("btn"+k); */

     /////////////////////////////////////////////
     if(j != ''){
    var input2 = document.getElementById("in"+k+"2");
    /* var btn2 = document.getElementById("btn"+k+"2"); */
    }
    if(checkbox.checked){
    input.removeAttribute("readonly");
    input.setAttribute("required", true);
    input.focus();
    /* btn.removeAttribute("disabled"); */
    if(j != ''){
      input2.removeAttribute("readonly");
      /* btn2.removeAttribute("disabled"); */
    }

    } else{
    input.setAttribute("readonly", true);
    input.removeAttribute("required");
    /* btn.setAttribute("disabled", true); */
    if(j != ''){
      input2.setAttribute("readonly", true);
      /* btn2.setAttribute("disabled", true); */
    }
    } 
    /////////////////////////////////////////////    
  }

  function checkpaqueteria(k){
    var checkbox = document.getElementById("select" + k);
    if(checkbox.checked){
      document.getElementById("tr" + k).style.backgroundColor = '#ccffcc';
    } else {
      document.getElementById("tr" + k).style.backgroundColor = '#ffffff';
    }
  }

  function entregacliente(k) {
  // Obtenemos el botón por su ID
  var button = document.getElementById("btn" + k);
  if (button.classList.contains("btn-success")) {
    // Remueve una clase del botón
    button.classList.remove("btn-success");
    // Agrega una clase al botón
    button.classList.add("btn-primary");
    // Cambia el color de fondo
    document.getElementById("tr" + k).style.backgroundColor = '#ffffff';
    // Cambia el contenido del botón al icono
    button.innerHTML = '<i class="fas fa-check-circle"></i>&nbsp;Entregar a cliente';
  } else {
    // Remueve una clase del botón
    button.classList.remove("btn-primary");
    // Agrega una clase al botón
    button.classList.add("btn-success");
    // Cambia el color de fondo
    document.getElementById("tr" + k).style.backgroundColor = '#ccffcc';
    // Cambia el contenido del botón al icono
    button.innerHTML = '<i class="fas fa-check-circle"></i>';
  }

  }

  function clickGuardar(){
    var guardar = document.getElementById("guardar");
    guardar.click();
  }

  function finalizarembarque(embarque){
    var formulario = document.getElementById("finalizaForm");
    Swal.fire({
      title: 'Atención',
      text: "¿Estás seguro de finalizar la carga de artículos para este embarque?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Estoy seguro'
    }).then((result) => {
      if (result.isConfirmed) {
        formulario.action = "?modulo=embarcamiento&accion=finalizarembarque&id="+embarque;
        formulario.submit();
      }
    })
  }
  </script>

  <?php

//TERMINA EL LISTADO DE PRODUCTOS
      echo '
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
  echo '
      <div class="row">
      <!-- 
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"adjunto">Archivo</label>
          <input type="file" name="adjunto" id="adjunto" class="form-control"/>
        </div>
      </div>  
      <div class="col-12 col-md-9">
        <div class="mb-5">
          <label for"comentario">Comentario</label>
          <textarea name="comentario" maxlength="100" id="comentario" class="form-control" placeholder="Agrega un comentario" onfocus="this.select();"></textarea>
        </div>
      </div>  
      -->

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar" hidden><i class="fas fa-save"></i>Guardar</button></center>
      </div>
    </div>
  </form>
</div>';




  }
}
?>