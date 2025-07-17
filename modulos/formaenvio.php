<?php
ini_set('display_errors', 1);
class formaenvio{

function __construct(){
    $this->model = new modelformaenvio($obj);
} 
function index(){
    $this->model->result();
    $this->view = new viewlformaenvio($this->model);
    $this->view->browse();
}

function insertdireccion(){
  //foreachdie();
  $remision = $_GET['id'];
  $cotizacion = busca($remision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
  $cl = busca($remision, 'remisiones', 'r_id', 'r_cliente');
  $nmb = busca($remision, 'remisiones', 'r_id', 'r_nmb');
  if($_POST['direcciones'] == "XXX"){
    $pais = '146';//$_POST['pais'] 
    if($pais == "146"){
      $sql = 'INSERT INTO crm_direcciones SET
      cd_nmbdir = "Remisión '.$nmb.'",
      cd_cliente = "'.$cl.'",
      cd_tipodir = "2",
      cd_calle = "'.$_POST['calle'].'",
      cd_nume = "'.$_POST['exterior'].'",
      cd_numi = "'.$_POST['interior'].'",
      cd_colonia = "'.$_POST['col'].'",
      cd_municipio = "'.$_POST['municipio'].'",
      cd_cp = "'.$_POST['cp'].'",
      cd_estado = "'.$_POST['estadoh'].'",
      cd_predeterminada = "0",
      cd_pais = "146"';
      setq($sql);
      $direccion = getmax('cd_id', 'crm_direcciones', false, false);
    } else {
      $sql = 'INSERT INTO crm_direcciones SET
      cd_nmbdir = "Cotizacion '.$nmb.'",
      cd_cliente = "'.$cl.'",
      cd_tipodir = "2",
      cd_calle = "'.$_POST['direccionint'].'",
      cd_pais = "'.$pais.'"';
      setq($sql);
      $direccion = getmax('cd_id', 'crm_direcciones', false, false);
    }
   
  } else {
    $direccion = $_POST['direcciones'];
    $pais = $_POST['pais'];
    if($pais == "146"){
      $sql = 'UPDATE crm_direcciones SET cd_calle = "'.$_POST['calle'].'",
                                      cd_nume = "'.$_POST['exterior'].'",
                                      cd_numi = "'.$_POST['interior'].'",
                                      cd_colonia = "'.$_POST['col'].'",
                                      cd_municipio = "'.$_POST['municipio'].'",
                                      cd_cp = "'.$_POST['cp'].'",
                                      cd_estado = "'.$_POST['estadoh'].'"
              WHERE cd_id = "'.$direccion.'"';
      setq($sql);                
    } else {
      $sql = 'UPDATE crm_direcciones SET cd_calle = "'.$_POST['direccionint'].'",
                                        cd_pais = "'.$pais.'"
              WHERE cd_id = "'.$direccion.'"';
      setq($sql);   
    }
  }

  $sqlupd = 'UPDATE crm_cotizaciones SET cc_direnvio = "'.$direccion.'" WHERE cc_id = "'.$cotizacion.'"';
  setq($sqlupd);

  redirect('?modulo=formaenvio&accion=index');
}

function cambiarenvio(){
  //foreachdie();
  $remision = $_GET['id'];
  $sql = 'SELECT * FROM remisionesc WHERE rc_remision = "'.$remision.'"';
  $result = setq($sql);
  while($rowupd = $result -> fetch_array()){
    $fenvio = $_POST['fechaEnvio'.$rowupd['rc_id']];
    $estatus = '';
    if($_POST['tipo'.$rowupd['rc_id']] == "D"){
      $pqt = $_POST['paqueteriaDom'.$rowupd['rc_id']];   
      $sucursal = 0; 
      $estatus = 'N';
    } else if($_POST['tipo'.$rowupd['rc_id']] == "O"){
      $pqt = $_POST['paqueteriaOcurre'.$rowupd['rc_id']];
      $sucursal = $_POST['sucursalOcurre'.$rowupd['rc_id']];
      $estatus = 'N';
    } else if($_POST['tipo'.$rowupd['rc_id']] == "C"){
      $pqt = 0;
      $sucursal = 0;
      $estatus = 'P';
    } else{
      $pqt = 0;
      $sucursal = 0;
      $estatus = 'N';
    }

    $sqlupd = 'UPDATE remisionesc SET rc_tipoenvio = "'.$_POST['tipo'.$rowupd['rc_id']].'", rc_paqueteria = "'.$pqt.'", rc_sucursal = "'.$sucursal.'", rc_fenvio = "'.$fenvio.'", rc_estatus = "'.$estatus.'" WHERE rc_id = "'.$rowupd['rc_id'].'"';
    setq($sqlupd);
  }

  $sqlcxc = 'SELECT cx_id, cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$remision.'" AND cx_tipo = "R"';
  $resultcxc = setq($sqlcxc);
  list($cxid, $importe, $abonado, $estatuscxc) = $resultcxc -> fetch_array();
  $sqlr = 'SELECT r_total, r_precioenvio, r_estatus FROM remisiones WHERE r_id = "'.$remision.'"';
  $resultr = setq($sqlr);
  list($total, $envio, $estatusr) = $resultr -> fetch_array();
  if($_POST['precio'] > 0){
    $newtotal = $total + $_POST['precio'];
    $newenvio = $envio + $_POST['precio'];
    $newestatus = "A";
    $sqlupdr = 'UPDATE remisiones SET r_total = "'.$newtotal.'", r_precioenvio = "'.$newenvio.'", r_estatus = "'.$newestatus.'" WHERE r_id = "'.$remision.'"';
    setq($sqlupdr);
    $newimporte= $importe + $_POST['precio'];
    if($abonado == $newimporte){
      $newestcxc = "F"; 
    } else {
      $newestcxc = "A";
    }
    $sqlupdcxc = 'UPDATE cxcobrar SET cx_importe = "'.$newimporte.'", cx_estatus = "'.$newestcxc.'" WHERE cx_id = "'.$cxid.'"';
    setq($sqlupdcxc);
  } 
  redirect('?modulo=formaenvio&accion=index');
}
}

class modelformaenvio{
function result(){ //filtro
  
  $sql = 'SELECT * FROM remisiones INNER JOIN crm_clientes ON r_cliente = c_id INNER JOIN usuarios ON r_encargado = u_id WHERE (r_estatus = "A" OR r_estatus = "F") AND r_id
  IN (SELECT rc_remision FROM remisionesc WHERE rc_estatus != "F" GROUP BY rc_remision) ORDER BY r_id DESC';
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}

}

class viewlformaenvio {
    var $model;

function __construct($model) {

    $this->model = $model;
    $this->model->aest = array("A" => "Activo","I" => "Inactivo");
?>
<script language="JavaScript">
function checkSubmitedit() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
</script>
<?php
}
function browse() {
    
      /* $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setcategoria.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>'; */
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      /* $boton = '<button class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Las categorias o familias de produtos, permiten realizar una división en el tratamiento y estadístico de los productos" data-trigger="click" >
        <i class="fas fa-question-circle"></i>
      </button>'; */
      
    $filtrar = '
              <form class="form-inline" role="form" method="post" action="?modulo=categorias&accion=index" id="filtro">
                <div class="mb-5">
                  <label for="">Filtrar por nombre:</label>
                  <input onkeyup="ceropage();" type="text" class="form-control" id="pref-search" name="nmb" value="" placeholder="Buscar por nombre">
                  <script>
                    function ceropage(){
                      document.getElementById("page").value = 0;
                      var codigo = event.which || event.keyCode;
                      if(codigo === 13) {
                        mandar(0);
                      }
                    }
                  </script>
                </div><!-- form group [search] -->

                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                <button type="submit" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=categorias&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

        toolbar("Formas de envío","");

  echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table table-striped" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th width="20%">Folio</th>
                    <th width="20%">Nombre</th>
                    <th width="20%">Asesor</th>
                    <th width="30%">Cliente</th>
                    <th width="10%"></th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
        echo '<tr>
        <td>'.$row['r_folio'].'</td>
        <td>'.$row['r_nmb'].'</td>
        <td>'.$row['u_nmb'].' '.$row['u_apellidos'].'</td>
        <td>'.$row['c_nmb'].' '.$row['c_apellidos'].'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" data-fancybox data-type="ajax" data-src="popup/cambiarenvio.php?id='.$row['r_id'].'&rand='.rand().'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';
        echo '</td>
        ';
    }
    echo '</table>
    
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
            pageLength: 50,
            ordering: false,
        });
      });
      </script>';


  
}

}
?>