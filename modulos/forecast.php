<?php
ini_set('display_errors',1);
class forecast {
  var $model;
  var $view;

function __construct() {
  $this->model = new modelforecast(isset($obj));
}

function index() {
  if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-30 days') );
  if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('+30 days'));
  
  //die($_REQUEST['almacen']);

  $this->model->result($_REQUEST['fini'],$_REQUEST['ffin']);
  $this->view = new viewforecast($this->model);
  $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin']);
}


function showarticulos() {
  if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-30 days') );
  if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('+30 days'));

  $this->model->resultsa($_REQUEST['fini'],$_REQUEST['ffin']);
  $this->view = new viewforecast($this->model);
  $this->view->showarticulos($_REQUEST['fini'],$_REQUEST['ffin']);
}

function show() {
  $this->model->select($_GET['id']);
  $this->model->resultd($_GET['id']);
  $this->view = new viewforecast($this->model);
  $this->view->show();
}

function insert(){
  $tiposoli = busca($_POST['solicitud'], 'pr_solicitudes', 'ps_id','ps_tiposoli');
  $this->model->setData('',$_POST['nmb'], date('Y-m-d H:i:s'), $_SESSION['uid'], $_POST['finicio'], $_POST['ffinal'], "N", $_POST['solicitud'], $tiposoli);
  $this->model->insert();
  $this->model->insertcp("1", $this->model->id, $_POST['finicio']);

  redirect('?modulo=forecast&accion=show&id='.$this->model->id.'');
}

function update(){
  $id=$_GET['id'];
  $tiposoli = busca($_POST['solicitud'], 'pr_solicitudes', 'ps_id','ps_tiposoli');
  $this->model->setData($id,$_POST['nmb'], date('Y-m-d H:i:s'), $_SESSION['uid'], $_POST['finicio'], $_POST['ffinal'], "N", $_POST['solicitud'], $tiposoli);
  $this->model->update();
  redirect('?modulo=forecast&accion=show&id='.$id.'');
}

function aplicar(){
  $id = $_GET['id'];
  $cp = busca($id, 'pr_checkpoints', 'pc_estatus = "N" AND pc_forecast', 'pc_id');
  
  $veri = busca($id, 'pr_forecastd', 'prfd_forecast', 'COUNT(*)');
  if($veri > 0){
    $check = busca($cp, 'pr_forecastd', 'prfd_forecast ="'.$id.'" AND prfd_checkpoint','COUNT(*)');
    if($check > 0){
      $maxcp = getmax('pc_id', 'pr_checkpoints','pc_forecast = "'.$id.'"');
      $fini = busca($maxcp, 'pr_checkpoints', 'pc_forecast = "'.$id.'" AND pc_id', 'pc_ffin');
      $ffin = busca($id, 'pr_forecast', 'prf_id', 'prf_ffin');
      $this->model->finalizarcp($cp, $id, $fini, $ffin);
    } else {
      $this->model->eliminarcp($cp, $id);
    }
    $this->model->aplicar($id);
  } else {
    echo '<script> alert("Se tiene que seleccionar al menos un articulo para poder finalizar el forecast."); </script>';  
  }
  
  redirect('?modulo=forecast&accion=show&id='.$id.'');
}
function finalizarcp(){
  $id = $_GET['id'];
  $cp = busca($id, 'pr_checkpoints', 'pc_estatus = "N" AND pc_forecast', 'pc_id');
  $veri = busca($cp, 'pr_forecastd', 'prfd_forecast = "'.$id.'" AND prfd_checkpoint', 'COUNT(*)');
  if($veri > 0 ){
    $this->model->finalizarcp($cp, $id, $_POST['finicio'], $_POST['ffinal']);
    $max= getmax('pc_id', 'pr_checkpoints', 'pc_forecast = "'.$id.'"');
    $this->model->insertcp($max, $id, $_POST['ffinal']);
  } else {
    echo '<script> alert("Se tiene que seleccionar al menos un articulo para poder finalizar el checkpoint."); </script>';
  }
  
  redirect('?modulo=forecast&accion=show&id='.$id.'');
}

}

/* -----------------------------------------------MODEL---------------------------------------------------------------------- */

class modelforecast {

function setData($id, $nmb, $fgen, $ugen, $fini, $ffin, $estatus, $solicitud, $tiposoli) {
  $this->id = strtoupper($id);
  $this->nmb = strtoupper($nmb);
  $this->fgen = strtoupper($fgen);
  $this->ugen = strtoupper($ugen);
  $this->fini = strtoupper($fini);
  $this->ffin = strtoupper($ffin);
  $this->estatus = strtoupper($estatus);
  $this->solicitud = strtoupper($solicitud);
  $this->tiposoli = strtoupper($tiposoli);
}

function setDatad($id,$forecast, $articulo, $modelo, $cantidad) {
  $this->idd = strtoupper($id);
  $this->cantidad = strtoupper($cantidad);
  $this->modelo = strtoupper($modelo);
  $this->articulo = strtoupper($articulo);
  $this->forecast = strtoupper($forecast);
}

function result($fini, $ffin) { //filtro;
    $sql = 'SELECT * FROM pr_forecast
            WHERE DATE(prf_fini) >= "'.$fini.'" AND DATE(prf_fini) <= "'.$ffin.'"';

    $sql.='ORDER BY prf_id DESC';
    $this->result = setq($sql);
}

function resultsa($fini, $ffin) { //filtro;
  $forecast = $_GET['id'];
  $sql = 'SELECT pfo_ordenprod FROM pr_forecastd INNER JOIN pr_forecastdorden ON pfo_forecastd = prfd_id WHERE prfd_forecast = "'.$forecast.'"';

  $sql.='ORDER BY pfo_ordenprod ASC';
  $this->result = setq($sql);
}

function select($id) {
  $sql = 'SELECT * FROM pr_forecast WHERE prf_id = "'.$id.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $this->id = $row['prf_id'];
  $this->nmb = $row['prf_nmb'];
  $this->fgen = $row['prf_fgen'];
  $this->ugen = $row['prf_ugen'];
  $this->fini = $row['prf_fini'];
  $this->ffin = $row['prf_ffin'];
  $this->solicitud = $row['prf_solicitud'];
  $this->tiposoli = $row['prf_tiposoli'];
  $this->estatus = $row['prf_estatus'];
}

function resultd($id){
  $sqlart = 'SELECT * FROM pr_forecastd INNER JOIN articulos ON prfd_articulo = a_id WHERE
             prfd_forecast = "'.$id.'" ORDER BY prfd_id DESC';
  $this->resultart = setq($sqlart);
}

function insert() {
  
  $sql = 'INSERT INTO pr_forecast SET
         prf_nmb = "'.$this->nmb.'",
         prf_fgen = "'.$this->fgen.'",
         prf_ugen = "'.$this->ugen.'",
         prf_fini = "'.$this->fini.'",
         prf_ffin = "'.$this->ffin.'",
         prf_solicitud = "'.$this->solicitud.'",
         prf_tiposoli = "'.$this->tiposoli.'",
         prf_estatus = "'.$this->estatus.'"
  ';
          
  setq($sql);
  $this->id = getmax('prf_id','pr_forecast', false, false);
}

function insertcp($id, $forecast, $fini){
  $sql = 'INSERT INTO pr_checkpoints SET pc_forecast = "'.$forecast.'",
                      pc_id = "'.$id.'", pc_estatus="N", pc_fini = "'.$fini.'"';                      
  setq($sql);                    
}
function finalizarcp($id,$forecast, $fini, $ffin){
  $sql = 'UPDATE pr_checkpoints SET pc_fini = "'.$fini.'",
                      pc_estatus="F", pc_ffin="'.$ffin.'" WHERE pc_id = "'.$id.'" AND pc_forecast = "'.$forecast.'"';
  setq($sql);                    
}
function update(){
  $sql = 'UPDATE pr_forecast SET
         prf_nmb = "'.$this->nmb.'",
         prf_fini = "'.$this->fini.'",
         prf_ffin = "'.$this->ffin.'",
         prf_solicitud = "'.$this->solicitud.'",
         prf_tiposoli = "'.$this->tiposoli.'"
         WHERE prf_id ="'.$this->id.'"
  ';
  setq($sql); 
}

function aplicar($id) {
  $sql3 = 'UPDATE pr_forecast SET prf_estatus  = "A"
           WHERE prf_id="' . $id . '"';
  setq($sql3);
}

function eliminarcp($id, $forecast){
  $sql = 'DELETE FROM pr_checkpoints WHERE pc_id = "'.$id.'" AND pc_forecast = "'.$forecast.'"';
  setq($sql);
}

}

/* -----------------------------------------------VIEW---------------------------------------------------------------------- */

class viewforecast {
  var $model;

function __construct($model) {
  $this->model = $model;
}

function browse($fini,$ffin) {

?>
<script language="JavaScript">
function checkSubmitmovimiento() {
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
$('body').on("keydown", function(e) { 
  if (e.altKey && e.which === 78) {
    var btn = document.getElementById('nuevo');
    btn.click();
    e.preventDefault();
  }
});

$('body').on("keydown", function(e) { 
  if (e.altKey && e.which === 76) {
    var btn = document.getElementById('filtrar');
    btn.click();
    $("#origen").focus();
    e.preventDefault();
  }
});

$('body').on("keydown", function(e) { 
  if (e.altKey && e.which === 82) {
    window.location.reload();
  }
});
</script>
<?php
  $this->model->aestatus = array("F" => "Finalizado", "A" => "En proceso de producción","N" => "En Captura");
  $this->model->nestatus = array("F" => "alert bg-success", "A" => "alert bg-primary text-white","N" => "alert bg-warning");

  
  $filtros = '';
  $botones = '';
  $botones .= '
  <a id="nuevo" class="btn btn-primary btn-sm" data-fancybox data-type="ajax" data-src="popup/setforecast.php" href="javascript:;">
    <i class="fa fa-plus"></i> Nuevo
  </a>
  ';
  
  $filtros .= '
  <form class="form-inline" role="form" method="post" action="?modulo=forecast&accion=index" id="filtro">
    <input type="text" id="tipom" name="tipom" value="<?php echo $tipom; ?>" hidden />
    <div class="mb-5">
      <label for="tipomov">Desde:</label>
      <input type="date" id="fini" name="fini" value="'.$fini.'" class="form-control">
    </div>
    <div class="mb-5">
      <label for="tipomov">Hasta:</label>
      <input type="date" id="ffin" name="ffin" value="'.$ffin.'" class="form-control">
    </div>
    <div class="mb-5">
      <label for="">Acciones: </label> <br>
      <button  class="btn btn-info">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
      </button>
      <a href="?modulo=forecast&accion=index">
        <button class="btn btn-warning">
          <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
        </button>
      </a>
    </div>
  </form>';

  toolbar($_GET['modulo'],$botones,$filtros);
  echo '
    <div class="card mt-3">
      <div class="card-body">
      <div class="table table-responsive text-medium">
        <center>
        <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 ">
          <tr>
            <th>Descripción</th>
            <th>Fecha de inicio</th>
            <th>Fecha de cierre</th>
            <th>Usuario</th>
            <th>Estatus</th>
            <th></th>
          </tr>
        </thead>';
  while($row = $this->model->result->fetch_array()){
    echo '<tr>
            <th>'.$row['prf_nmb'].'</th>
            <td>'.date('d-m-Y',strtotime($row['prf_fini'])).'</td>
            <td>'.date('d-m-Y',strtotime($row['prf_ffin'])).'</td>
            <td>'.busca($row['prf_ugen'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
            <td class="'.$this->model->nestatus[$row['prf_estatus']].'">'.$this->model->aestatus[$row['prf_estatus']].'</td>
            <td>
              <a href="?modulo=forecast&accion=show&id='.$row['prf_id'].'">
                <button type="button" class="btn-sm btn-info btn"><i class="fa fa-eye"></i> Ver</button>
              </a>';
    echo '</td>
    </tr>';
  }
  echo '</table>
  </center></div>
  <script>
    $(document).ready(function () {
      var windowHeight = $(window).height();
      $("#myTable").DataTable( {
          paging: true,
          scrollY: windowHeight * 0.5,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          responsivePriority: 1,
          pageLength: 100,
          ordering: false,
      });
    });
  </script>
  
  ';

}

function show(){

  ?>
  <style>
      #myTableG {
        width: 100%;
        border-collapse: collapse;
      }

      #myTableG th, #myTableG td {
        padding: 8px;
        text-align: left;
        border: 1px solid #2ECC71;
        /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
      }
    </style>

  <?php
  $botones = '';
  $otros = '';
  $botones .= '
  <a href="?modulo=forecast&accion=index" accesskey="">
    <button type="button" class="btn btn-warning btn-sm"><i class="fa fa-arrow-left"></i> Atras </button>
  </a>';
  $sqlcount = 'SELECT COUNT(*) FROM pr_forecastdorden WHERE pfo_forecastd IN (SELECT prfd_id FROM pr_forecastd WHERE prfd_forecast = "'.$this->model->id.'" ) ';
  $result = setq($sqlcount);
  list($count) = $result->fetch_array();
  if($count > 0){
    $otros .= '
    <a target="_BLANK" href="?modulo=ordenprod&accion=index&forecast='.$this->model->nmb.'" accesskey="">
      <button type="button" class="btn btn-success btn-sm"><i class="fa fa-arrow-left"></i> Ver ordenes de producción </button>
    </a>';
  }
  $botones .= '
    <a id="nuevo" class="btn btn-secondary btn-sm" data-fancybox data-type="ajax" data-src="popup/setforecast.php?id='.$this->model->id.'" href="javascript:;">
      <i class="fas fa-info"></i>Información
    </a>
  ';
  $botones .= '
    <a id="nuevo" class="btn btn-info btn-sm" data-fancybox data-type="ajax" data-src="popup/calculopm.php?id='.$this->model->id.'&rand='.rand().'" href="javascript:;">
    <i class="fas fa-calculator"></i>Materia prima estimada
    </a>
  ';
  $cp = busca($this->model->id, 'pr_checkpoints', 'pc_estatus = "N" AND pc_forecast', 'pc_id');
  $sqladd = '';
  if($this->model->estatus != "A"){
    $otros='<button class="btn btn-sm btn-primary" onclick="aplicar();"><i class="fas fa-check"></i> Aplicar</button>';
    $otros.='<a id="nuevo" class="btn btn-success btn-sm" data-fancybox data-type="ajax" data-src="popup/setcheckpoint.php?id='.$this->model->id.'&rand='.rand().'" href="javascript:;">
    <i class="fas fa-flag"></i> Finalizar checkpoint '.$cp.'<a/>';
    $sqladd = ' AND prfd_checkpoint="'.$cp.'"';
  } else {
    if(date('Y-m-d') < $this->model->id){
      $otros.='<a id="add" class="btn btn-primary btn-sm" data-fancybox data-type="ajax" data-src="popup/addproducto.php?id='.$this->model->id.'&rand='.rand().'" href="javascript:;">
      <i class="fas fa-flag"></i>Añadir artículos<a/>';
    }
  }

  toolbar($_GET['modulo'],$botones,'', '', $otros);
  $categoria = busca('1', 'categorias', 'cat_inflable', 'cat_id'); 
  $check = busca($this->model->id, 'pr_checkpoints', 'pc_estatus = "N" AND pc_forecast', 'pc_id');

  echo '<div class="row mt-5">';
          echo '<div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title" id="basic-layout-form">Forecast '.$this->model->nmb.'</h5>
                <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>-->
              </div>';
  echo '<div class="col-12 p-5">
  <div class="col-12 col-md-3" style="text-align: right;">';
  /* echo '<select id="checkpoint" class="form-control mx-auto" style="margin-left: auto; margin-right: 0;">';
  $sql = 'SELECT * FROM pr_checkpoints WHERE pc_forecast = "'.$this->model->id.'"';
  $result = setq($sql);
  while($row = $result -> fetch_array()){
    if($row['pc_id'] == $check) $sel = 'selected';
    else $sel = '';
    echo '<option value="'.$row['pc_id'].'" '.$sel.'>Checkpoint '.$row['pc_id'].'</option>';
  }
  echo '</select>'; */
  echo '</div>
  </div>';
  echo '<center>';
  echo '<div class="card col-10">';
  echo '';
  if($this->model->estatus != "A"){
    echo '<div class="table table-responsive table-hover">
        <table class="table table-hover" id="myTable2">
        <thead class="thead bg-primary text-white">';
          if($this->model->estatus == "A") echo '<tr > <th colspan="4"><center> Artículos totales del forecast </center></th> </tr>';
          echo '<tr>
            <th width="40%">Artículo</th>
            <th width="20%">Modelo</th>
            <th width="20%">Categoría</th>
            <th width="20%">Cantidad</th>
          </tr>
        </thead>';
  /* if($this->model->estatus == "F"){
      $read = 'readonly';
  }else {
    $read = '';
  } */
  if($this->model->estatus == "N"){
    $sql = 'SELECT * FROM articulos INNER JOIN categorias ON cat_id = a_categoria WHERE a_tipoprod != "M" AND a_estatus = "A" AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") ORDER BY a_nmb ASC';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $var = busca($row['a_id'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
      if($var){
        $sqlvar = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$row['a_id'].'"';
        $resultvar = setq($sqlvar);
        while($rowvar = $resultvar -> fetch_array()){
          $articulo = $row['a_nmb'].' '.$rowvar['av_nmb'];
          $modelo = $rowvar['av_modelo']; 
          $cant = busca($row['a_id'], 'pr_forecastd', 'prfd_forecast = "'.$this->model->id.'" AND prfd_modelo = "'.$rowvar['av_modelo'].'" '.$sqladd.' AND prfd_articulo', 'prfd_cantidad');
          if(!$cant) {
            $bg = '';
            $cant = 0;
          } else {
            $bg = 'style="background:#97FFCA"';
          }

          ?>
          <tr <?php echo$bg;?> id="tr<?php echo $rowvar['av_cb'].','.$cp;?>">
          <td><?php echo$articulo;?> </td>
          <td><?php echo$modelo;?> </td>
          <td><?php echo$row['cat_nmb'];?> </td>
          <td><input onfocus="this.select();" type="number" id="cant<?php echo $rowvar['av_cb'].','.$cp;?>" class="form-control" min="0" value="<?php echo$cant;?>" onchange="changecant('<?php echo $rowvar['av_cb'];?>', <?php echo $row['a_id'];?>, '<?php echo $rowvar['av_modelo'];?>', '<?php echo $cp;?>')">  </td>
          </tr>
          <?php
        }
      } else {
        $cant = busca($row['a_id'], 'pr_forecastd', 'prfd_forecast = "'.$this->model->id.'" '.$sqladd.' AND prfd_modelo = "" AND prfd_articulo', 'prfd_cantidad');
        if(!$cant) {
          $cant = 0;
          $bg ="";
        } else {
          $bg = 'style="background:#97FFCA"';
        }
        ?>
        <tr <?php echo $bg; ?> id="tr<?php echo $row['a_cb'].','.$cp;?>">
        <td><?php echo $row['a_nmb'];?> </td>
        <td></td>
        <td><?php echo $row['cat_nmb'];?> </td>
        <td><input onfocus="this.select();" type="number" id="cant<?php echo $row['a_cb'].','.$cp;?>" class="form-control" min="0" value="<?php echo $cant;?>" onchange="changecant('<?php echo $row['a_cb'];?>', <?php echo $row['a_id']; ?>, '', <?php echo $cp; ?>)"> </td>
        </tr>
        <?php
      }
    }
  } else{
    $sql = 'SELECT a_nmb, SUM(prfd_cantidad) AS prfd_cantidad, prfd_modelo, prfd_articulo, cat_nmb  FROM pr_forecastd INNER JOIN articulos ON a_id = prfd_articulo INNER JOIN categorias ON cat_id = a_categoria WHERE prfd_forecast = "'.$this->model->id.'"  GROUP BY prfd_articulo, prfd_modelo ORDER BY a_nmb ASC';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $articulo = $row['a_nmb'];
      if($row['prfd_modelo'] != "")$articulo .= ' '.busca($row['prfd_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['prfd_modelo'].'" AND av_articulo', 'av_nmb');
      echo '<tr style="background:#97FFCA"><td>'.$articulo.'</td>
      <td>'.$row['prfd_modelo'].'</td>
      <td>'.$row['cat_nmb'].'</td>
      <td><input onfocus="this.select();" type="number" class="form-control" min="0" value="'.$row['prfd_cantidad'].'"></td></tr>';
    }
  }
    echo '</table></div>
    </div>';
  }
    $cp = busca($this->model->id, 'pr_checkpoints', 'pc_estatus = "N" AND pc_forecast', 'pc_id');
    $sqlcp = 'SELECT * FROM pr_checkpoints WHERE pc_forecast = "'.$this->model->id.'" AND pc_id != "'.$cp.'"';
    $resultcp = setq($sqlcp);
    if($resultcp -> num_rows > 0){
      $totcp = busca($this->model->id, 'pr_checkpoints','pc_estatus != "N" AND pc_forecast', 'COUNT(*)');
      echo '<center><div class="col-9">
      <!--begin::List Widget 5-->
      <div class="card card-xxl-stretch">
        <!--begin::Header-->
        <div class="card-header align-items-center border-0 mt-4">
          <h3 class="card-title align-items-start flex-column">
            <span class="fw-bolder mb-2 text-dark">Checkpoints</span>
            <span class="text-muted fw-bold fs-7"> '.$totcp.' checkpoints registrados</span>
          </h3>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body pt-5">
          <!--begin::Timeline-->
          <div class="timeline-label">';
          
          while($rowcp = $resultcp -> fetch_array()){
            echo '<!--begin::Item-->
              <div class="timeline-item">
                <!--begin::Label-->
                <div class="timeline-label fw-bolder text-gray-800 fs-6">'.$rowcp['pc_id'].' </div>
                <!--end::Label-->
                <!--begin::Badge-->
                <div class="timeline-badge">
                  <i class="fa fa-genderless text-primary fs-1"></i>
                </div>
                <!--end::Badge-->
                <!--begin::Text-->
                <div class="flex-grow-1">
                <div class="timeline-content fw-bolder ps-3 d-flex">Inicio: '.fecha_formato($rowcp['pc_fini'], false, true).'</div>
                <div class="timeline-content fw-bolder ps-3 d-flex">Fin: '.fecha_formato($rowcp['pc_ffin'], false, true).'</div>
                <div class="symbol symbol-50px me-5 d-flex alert bg-light-success rounded">
                <table class="table mt-3" id="myTableG">
                  <thead class="thead">
                    <tr>
                      <th width="40%">Artículo</th>
                      <th width="15%">Modelo</th>
                      <th width="15%">Categoría</th>
                      <th width="15%">Cantidad</th>
                      <th width="15%">Cantidad en ordenes de producción</th>
                      <th width="15%">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>';
                    
                    $sqlart = 'SELECT * FROM pr_forecastd INNER JOIN articulos ON a_id = prfd_articulo INNER JOIN categorias ON cat_id = a_categoria WHERE prfd_forecast = "'.$this->model->id.'" AND prfd_checkpoint = "'.$rowcp['pc_id'].'" ORDER BY a_nmb ASC';
                    $resultart = setq($sqlart);
                    while($row = $resultart -> fetch_array()){
                      echo '<tr>';
                        $articulo = $row['a_nmb'];
                        if($row['prfd_modelo'] != "") {
                          $articulo .= ' '.busca($row['prfd_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['prfd_modelo'].'" AND av_articulo', 'av_nmb');
                          $codb = busca($row['prfd_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['prfd_modelo'].'" AND av_articulo', 'av_cb');
                        } else {
                          $codb = $row['a_cb'];
                        }

                        $artinop = busca($row['prfd_id'], 'pr_forecastdorden', 'pfo_forecastd', 'SUM(pfo_cantidad)');
                        if($artinop <= 0) $artinop = 0;
            
                        ?>
                        <tr id="tr<?php echo $codb.','.$rowcp['pc_id'];?>">
                        <td><?php echo$articulo;?> </td>
                        <td><?php echo$row['prfd_modelo'];?> </td>
                        <td><?php echo$row['cat_nmb'];?> </td>
                        <td><input onfocus="this.select();" type="number" id="cant<?php echo $codb.','.$rowcp['pc_id'];?>" class="form-control" min="<?php echo number_format($artinop, 2);?>" value="<?php echo$row['prfd_cantidad'];?>" onchange="changecant('<?php echo $codb;?>', <?php echo $row['a_id'];?>, '<?php echo $row['prfd_modelo'];?>', '<?php echo $rowcp['pc_id'];?>')" <?php echo $read; ?>> </td>
                        <td><input readonly onfocus="this.select();" type="number" class="form-control" min="0" value="<?php echo number_format($artinop, 2);?>"> </td>
                        <td><?php 
                          $cantart = busca($row['prfd_id'], 'pr_forecastdorden', 'pfo_forecastd', 'SUM(pfo_cantidad)');
                          $cantproc = busca($row['prfd_articulo'], 'articulos_produccion', 'apr_articulo', 'COUNT(*)');
                          if($cantart < $row['prfd_cantidad']){
                            if($cantproc >0){
                        ?>
                        <a id="nuevo" class="btn btn-primary btn-sm" data-fancybox data-type="ajax" data-src="popup/setordenprod.php?forecast=<?php echo $this->model->id; ?>&forecastd=<?php echo $row['prfd_id'];?>&rand=<?php echo rand();?>" href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Crear orden de producción">
                            <i class="fas fa-file-invoice"></i> Crear
                        </a>
                        <?php 
                            }else{
                              ?>
                                <button class="btn btn-sm btn-primary" onclick="denegar(<?php echo $row['prfd_articulo'];?>);"><i class="fas fa-file-invoice"></i> Crear</button>        
                              <?php
                            }
                          }
                        ?>
                        </tr>
                        <?php
                      echo '</tr>';
                    }
                    echo'<tr> </tr>';
                    echo '</tbody>';
                  echo '</table>
                  </div>
                  </div>
                <!--end::Text-->
              </div>
            <!--end::Item-->';
          }
          echo '</div>
          <!--end::Timeline-->
        </div>
        <!--end: Card Body-->
      </div>
      <!--end: List Widget 5-->
    </div></center>
    <!--end::Col-->';
  }
    
        


  ?>
    <script>
      $(document).ready(function () {
        var windowHeight = $(window).height();
        $("#myTable2").DataTable({
          paging: true,
          scrollY: windowHeight * 0.5,
          language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          responsivePriority: 1,
          lengthMenu: [[10, 25, 50, 100, -1], ["10", "25", "50", "100", "Todos"]],
          pageLength: -1,
        });
      });
      function changecant(cb, id, modelo, cp){
        var cant = document.getElementById('cant'+cb+','+cp);
        var td = document.getElementById('tr'+cb+','+cp);
        var checkpoint = document.getElementById('checkpoint');
        if(cant.checkValidity()){
          $.ajax({
            url: "query/cantidadforecast.php",
            method: "POST",
            data: { "articulo": id,
                    "cp": cp,  
                    "modelo": modelo,
                    "cantidad": cant.value,
                    "forecast": <?php echo $this->model->id; ?>
                  },
          }).done(function(data){
            console.log(data);
            if(parseFloat(cant.value) > 0){
              td.style.backgroundColor = '#97FFCA';
            } else {
              td.style.backgroundColor = '';
            }
          });
        } else {
          td.style.backgroundColor = '';
          Swal.fire({
          icon: 'error',
          title: 'La cantidad ingresada es invalida.',
          }).then((result) => {
            Swal.close();
          });
        }
      }

      function aplicar(){
        var confirmar = confirm('¿Deseas terminar la captura de articulo para este forecast?')
        if(confirmar){
          window.location.href="?modulo=forecast&accion=aplicar&id=<?php echo $this->model->id; ?>";
        } 
      }

      function denegar(articulo){
        Swal.fire({
          icon: 'error',
          title: 'No se puede generar la orden de producción',
          html: 'El artículo no tiene configurado los procesos para su producción.',
          showCancelButton: true,
          showConfirmButton: true,
          focusConfirm: false,
          confirmButtonText: `<i class="fas fa-reply" style="color:#fff"></i> Ir a la configuración`,
          cancelButtonText: `<i class="fas fa-times-circle" style="color:#fff"></i> Cancelar`,
          cancelButtonColor: '#f1416c',
          }).then((result) => {
            if (result.isConfirmed) {
              window.open('?modulo=articulos&accion=produccion&articulo='+articulo, '_blank');
            } else {
              Swal.close();
            }
          });
      }
    </script>
  <?php

}

function showarticulos($fini,$ffin) {

  ?>
  <script language="JavaScript">
  function checkSubmitmovimiento() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
  }
  $('body').on("keydown", function(e) { 
    if (e.altKey && e.which === 78) {
      var btn = document.getElementById('nuevo');
      btn.click();
      e.preventDefault();
    }
  });
  
  $('body').on("keydown", function(e) { 
    if (e.altKey && e.which === 76) {
      var btn = document.getElementById('filtrar');
      btn.click();
      $("#origen").focus();
      e.preventDefault();
    }
  });
  
  $('body').on("keydown", function(e) { 
    if (e.altKey && e.which === 82) {
      window.location.reload();
    }
  });
  </script>
  <?php
    $this->model->aestatus = array("F" => "Finalizado", "A" => "En proceso de producción","N" => "En Captura");
    $this->model->nestatus = array("F" => "alert bg-success", "A" => "alert bg-primary","N" => "alert bg-warning");
  
  
    
    $filtros = '';
    $botones = '';
    $botones .= '
    <a id="nuevo" class="btn btn-primary btn-sm" data-fancybox data-type="ajax" data-src="popup/setforecast.php" href="javascript:;">
      <i class="fa fa-plus"></i> Nuevo
    </a>
    ';
    
    $filtros .= '
    <form class="form-inline" role="form" method="post" action="?modulo=forecast&accion=index" id="filtro">
      <input type="text" id="tipom" name="tipom" value="<?php echo $tipom; ?>" hidden />
      <div class="mb-5">
        <label for="tipomov">Desde:</label>
        <input type="date" id="fini" name="fini" value="'.$fini.'" class="form-control">
      </div>
      <div class="mb-5">
        <label for="tipomov">Hasta:</label>
        <input type="date" id="ffin" name="ffin" value="'.$ffin.'" class="form-control">
      </div>
      <div class="mb-5">
        <label for="">Acciones: </label> <br>
        <button  class="btn btn-info">
          <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
        </button>
        <a href="?modulo=forecast&accion=index">
          <button class="btn btn-warning">
            <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
          </button>
        </a>
      </div>
    </form>';

  
    toolbar($_GET['modulo'],$botones,$filtros);
    echo '
      <div class="card mt-3">
        <div class="card-body">
        <div class="table table-responsive text-medium">
          <center>
          <table class="table table-hover" id="myTable">
          <thead class="thead bg-blue bg-darken-3 ">
            <tr>
              <th>Descripción</th>
              <th>Fecha de inicio</th>
              <th>Fecha de cierre</th>
              <th>Usuario</th>
              <th>Estatus</th>
              <th></th>
            </tr>
          </thead>';
    while($row = $this->model->result->fetch_array()){
      echo '<tr>
              <th>'.$row['prf_nmb'].'</th>
              <td>'.date('d-m-Y',strtotime($row['prf_fini'])).'</td>
              <td>'.date('d-m-Y',strtotime($row['prf_ffin'])).'</td>
              <td>'.busca($row['prf_ugen'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
              <td class="'.$this->model->nestatus[$row['prf_estatus']].'">'.$this->model->aestatus[$row['prf_estatus']].'</td>
              <td>
                <a href="?modulo=forecast&accion=show&id='.$row['prf_id'].'">
                  <button type="button" class="btn-sm btn-info btn"><i class="fa fa-eye"></i> Ver</button>
                </a>';
      echo '</td>
            </tr>';
    }
    echo '</table>
    </center></div>
    <script>
      $(document).ready(function () {
        var windowHeight = $(window).height();
        $("#myTable").DataTable( {
            paging: true,
            scrollY: windowHeight * 0.5,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength: 100,
            ordering: false,
        });
      });
    </script>
    
    ';
  
  }



}
?>