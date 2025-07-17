<?php
/* if($_SESSION['uid'] == "ADMIN")
ini_set('display_errors','1'); */
$GLOBALS['menu'] = "Reportes";
/* ini_set('memory_limit', '2048M'); */

class Reportes{
  var $model;
  var $view;

function __construct(){ // constructor
  $this->model = new modelreportes($obj); // crea modelo
}
function index() {
  $this->view = new viewreportes($this->model);
  $this->view->showreportes();
}

function facturacion(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-01');
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d');
  if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;
  if(!isset($_POST['estatus'])) $_POST['estatus'] = "T";

  $this->model->resultfacturas($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus']);
  $this->view = new viewreportes($this->model);
  $this->view->facturacion($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus']);
  //include('reportes/comprasproveedor.php');
}
function existencias(){
  //die($_REQUEST['almacen']);
  $this->view = new viewreportes($this->model);
  $this->view->existencias();
  //include('reportes/comprasproveedor.php');
}

function existenciasprod(){
  //die($_REQUEST['almacen']);
  $this->view = new viewreportes($this->model);
  $this->view->existenciasprod();
  //include('reportes/comprasproveedor.php');
}

function facturasemirec(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-').'01';
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d');
  if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;
  if(!isset($_POST['estatus'])) $_POST['estatus'] = "T";

  $this->model->resultfacturas($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus']);
  $this->model->resultfacturasrec($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus']);
  $this->view = new viewreportes($this->model);
  $this->view->facturasemirec($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus']);
}

function ventasd(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_POST['estatus'])) $_POST['estatus'] = "V";
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;
  if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;

  $this->model->resultremisiones($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus'],$_POST['asesor']);
  $this->view = new viewreportes($this->model);
  $this->view->ventasd($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus'],$_POST['asesor']);

}

function ventasxprod(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_POST['estatus'])) $_POST['estatus'] = "V";
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;
  if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;
  if(!isset($_POST['categoria'])) $_POST['categoria'] = NULL;

  $this->model->resultremisionesd($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus'],$_POST['asesor'], $_POST['categoria']);
  $this->view = new viewreportes($this->model);
  $this->view->ventasxprod($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus'],$_POST['asesor'], $_POST['categoria']);
}

function envios(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;
  if(!isset($_POST['paqueteria'])) $_POST['paqueteria'] = NULL;

  $this->view = new viewreportes($this->model);
  $this->view->envios($_POST['fini'],$_POST['ffin'],$_POST['paqueteria'],$_POST['asesor']);
}


function comprasd(){  //not working
  if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";
  if(!isset($_REQUEST['almacen'])) $_REQUEST['almacen'] = NULL;
  if(!isset($_REQUEST['cliente'])) $_REQUEST['proveedor'] = NULL;

  $this->model->resultrecepciones($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['estatus'],$_REQUEST['almacen']);
  $this->view = new viewreportes($this->model);
  $this->view->compras($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['estatus'],$_REQUEST['almacen']);
  //include('reportes/comprasproveedor.php');
}

function financieros(){  //not working
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m',strtotime('january this year'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m',strtotime('december this year'));
  if(!isset($_POST['linean'])) $_POST['linean'] = NULL;

  $this->view = new viewreportes($this->model);
  $this->view->financieros($_POST['fini'],$_POST['ffin'],$_POST['linean']);
  //include('reportes/comprasproveedor.php');
}

function tablerosxvendedor(){
  if(!isset($_REQUEST['vendedor'])) $_REQUEST['vendedor'] = NULL;
  if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m',strtotime('january this year'));
  if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m',strtotime('december this year'));
  if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;

  $this->model->resulttabs($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['vendedor']);
  $this->view = new viewreportes($this->model);
  $this->view->tablerosxvendedor($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['vendedor']);
}

function conversiones(){
  $this->view = new viewreportes($this->model);
  $this->view->conversiones();
}

function dashclientes(){
  $this->view = new viewreportes($this->model);
  $this->view->showdashclientes();
}

function dashproductos(){
  $this->view = new viewreportes($this->model);
  $this->view->showdashproductos();
}

function dashcompras(){
  $this->view = new viewreportes($this->model);
  $this->view->showdashcompras();
}

//Joss
function dashtableros(){
  if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_REQUEST['vendedor'])) $_REQUEST['vendedor'] = NULL;
  if(!isset($_REQUEST['linean'])) $_REQUEST['linean'] = NULL;

  $this->view = new viewreportes($this->model);
  $this->view->dashtableros($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['vendedor'],$_REQUEST['linean']);
}

function dashfacturas(){
  if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_REQUEST['clientes'])) $_REQUEST['clientes'] = NULL;
  if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "T";

  $this->view = new viewreportes($this->model);
  $this->view->dashfacturas($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['clientes'],$_REQUEST['estatus']);
}
function estadisticos(){
  $fini = date('Y-m-01', strtotime('-1 month'));
  $ffin = date('Y-m-d');
  $this->view = new viewreportes($this->model);
  $this->view->showestadisticos($fini,$ffin);
}

function escuelavirtual(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m', strtotime('january this year'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m');

  $this->view = new viewreportes($this->model);
  $this->view->escuelavirtual($_POST['fini'],$_POST['ffin']);
}

function cambios(){
  if(!isset($_REQUEST['tablero'])) $_REQUEST['tablero'] = NULL;
  if(!isset($_REQUEST['cotizacion'])) $_REQUEST['cotizacion'] = NULL;
  if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-20 days'));
  if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_REQUEST['usuario'])) $_REQUEST['usuario'] = NULL;
  

  /* $this->model->result(trim($_REQUEST['nmb']),$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['page'],$_REQUEST['agente']); */
  $this->model->resultcambios(trim($_REQUEST['tablero']),$_REQUEST['cotizacion'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['usuario']);
  $this->view = new viewreportes($this->model);
  $this->view->cambios(trim($_REQUEST['tablero']),trim($_REQUEST['cotizacion']), $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['usuario']);
}

function formapago(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_POST['fpago'])) $_POST['fpago'] = "";
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;
  if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;

  $this->view = new viewreportes($this->model);
  $this->view->formapago($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['fpago'],$_POST['asesor']);
}

function excedentes(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d',strtotime('last day of this month'));

  $this->model->resultexcedentes($_POST['fini'],$_POST['ffin']);
  $this->view = new viewreportes($this->model);
  $this->view->excedentes($_POST['fini'],$_POST['ffin']); 
}

function ventascaducadas(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;

  $this->model->resultventascaducadas($_POST['fini'],$_POST['ffin'], $_POST['asesor']);
  $this->view = new viewreportes($this->model);
  $this->view->ventascaducadas($_POST['fini'],$_POST['ffin'], $_POST['asesor']);
}

function ventaspagos(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_POST['estatus'])) $_POST['estatus'] = "V";
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;
  if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;

  $this->model->resultremisiones($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus'],$_POST['asesor']);
  $this->view = new viewreportes($this->model);
  $this->view->ventaspagos($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus'],$_POST['asesor']);
}

function cobroenvio(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;

  $this->model->resultenvios($_POST['fini'],$_POST['ffin'],$_POST['asesor']);
  $this->view = new viewreportes($this->model);
  $this->view->cobroenvio($_POST['fini'],$_POST['ffin'],$_POST['asesor']);
} 

function historialmov(){
  $this->view = new viewreportes($this->model);
  $this->view->historialmov();
}
function embarquesxprod(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d');
  if(!isset($_POST['estatus'])) $_POST['estatus'] = "V";
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;
  if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;
  if(!isset($_POST['categoria'])) $_POST['categoria'] = NULL;
  $this->view = new viewreportes($this->model);
  $this->view->embarquesxprod($_POST['fini'],$_POST['ffin'],$_POST['cliente'],$_POST['estatus'],$_POST['asesor'], $_POST['categoria']);
}
function movimientosxprod(){
  if(!isset($_POST['fini'])) $_POST['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_POST['ffin'])) $_POST['ffin'] = date('Y-m-d');
  if(!isset($_POST['estatus'])) $_POST['estatus'] = "V";
  if(!isset($_POST['asesor'])) $_POST['asesor'] = NULL;
  if(!isset($_POST['tipo'])) $_POST['tipo'] = 'E';
  if(!isset($_POST['categoria'])) $_POST['categoria'] = NULL;
  $this->view = new viewreportes($this->model);
  $this->view->movimientosxprod($_POST['fini'],$_POST['ffin'],$_POST['tipo'],$_POST['estatus'],$_POST['asesor'], $_POST['categoria']);
}

}
class modelreportes{

function resultfacturas($fini,$ffin,$cliente,$estatus){
  $sql = 'SELECT * FROM facturas
          WHERE f_ftimbro BETWEEN DATE("'.$fini.'") AND DATE("'.date('Y-m-d',strtotime($ffin." + 1 day")).'")
          AND f_estatus = "'.$estatus.'"';
          if($cliente) $sql.=' AND f_cliente = "'.$cliente.'" ';
          $sql.='ORDER BY f_id DESC,f_folio DESC';
  $this->resultmonto = setq($sql) or die($sql);
}

function resultfacturasrec($fini,$ffin,$cliente,$estatus,$tipodoc="I"){
  $sql = 'SELECT * FROM facturasrec
          WHERE fr_fecha BETWEEN DATE("'.$fini.'") AND DATE("'.date('Y-m-d',strtotime($ffin." + 1 day")).'")';
          if($cliente) $sql.=' AND fr_proveedor = "'.$cliente.'" ';
          if($tipodoc) $sql.=' AND fr_tipoc = "'.$tipodoc.'" ';
          $sql.='ORDER BY fr_fecha DESC';
  $this->resultrec = setq($sql) or die($sql);
}
function resultremisiones($fini,$ffin,$cliente,$estatus,$asesor){
  //$sql = 'SELECT * FROM remisiones WHERE DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus != "N" ';
  $sql = 'SELECT * FROM remisiones INNER JOIN remisionesd ON rd_remision = r_id WHERE rd_articulo != "0" AND DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus != "N"';
  if($cliente) $sql.=' AND r_cliente IN (SELECT c_id FROM crm_clientes WHERE 
    (c_nmb LIKE "%'.$cliente.'%" OR c_alias LIKE "%'.$cliente.'%" OR c_apellidos LIKE "%'.$cliente.'%") )';
  if($asesor) $sql.=' AND r_encargado = "'.$asesor.'" ';
  if($estatus){
    if($estatus == "V") $sql.=' AND r_estatus IN ("A","F","FE")';
    else if ($estatus == "F") $sql.=' AND r_estatus IN ("F","FE")';
    else if ($estatus == "P") $sql.=' AND r_estatus IN ("P","D")';
    else $sql.=' AND r_estatus = "'.$estatus.'" ';
  }
  $sql.='GROUP BY r_id ORDER BY r_faplica DESC';
  $this->resultrem = setq($sql);
  
}

function resultremisionesd($fini,$ffin,$cliente,$estatus,$asesor, $categoria){
  //$sql = 'SELECT * FROM remisiones WHERE DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus != "N" ';
  $sql = 'SELECT r_folio,rd_articulo,rd_modelo,rd_cantidad,remisiones.r_descuento,rd_precio, a_categoria, r_encargado
          FROM remisiones INNER JOIN remisionesd ON r_id = rd_remision INNER JOIN articulos ON rd_articulo = a_id 
          WHERE DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus IN ("A","F","FE")';

  if($cliente) $sql.=' AND r_cliente IN (SELECT c_id FROM crm_clientes WHERE
    (c_nmb LIKE "%'.$cliente.'%" OR c_alias LIKE "%'.$cliente.'%" OR c_apellidos LIKE "%'.$cliente.'%") )';
  if($asesor) $sql.=' AND r_encargado = "'.$asesor.'" ';
  if($categoria) {
    if($categoria == "T") $sql.= ' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
    else $sql.=' AND a_categoria = "'.$categoria.'"';
  }
  $sql.=' ORDER BY r_faplica DESC';

  $this->resultremd = setq($sql);


  $sqls = 'SELECT SUM(r_total-r_iva-r_precioenvio) FROM remisiones
           WHERE DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus IN ("A","F","FE")';
  if($asesor) $sqls.=' AND r_encargado = "'.$asesor.'" ';
  $results = setq($sqls);
  list($sumaprod) = $results->fetch_array();
  $this->totalventa = $sumaprod;
}


function resultrecepciones($fini,$ffin,$proveedor,$estatus,$almacen){
  $sql = 'SELECT * FROM recepciones WHERE DATE(r_fechaapli) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus != "N"';
  if($proveedor) $sql.=' AND r_proveedor IN (SELECT p_id FROM proveedores WHERE 
    (p_nmb LIKE "%'.$proveedor.'%" OR p_alias LIKE "%'.$proveedor.'%") )';
  if($almacen) $sql.=' AND r_almacen = "'.$almacen.'" ';
  if($estatus) $sql.=' AND r_estatus = "'.$estatus.'" ';
  $sql.=' ORDER BY r_fechaapli DESC';
  $this->resultrec = setq($sql);
}

function resultcambios($tablero,$cotizacion,$fini,$ffin,$usuario){ //filtro
  $bloque = 50;

  $sql = 'SELECT * FROM crm_cotizaciones_cambios INNER JOIN crm_cotizaciones ON cc_id = cca_cotizacion INNER JOIN crm_tableros  ON cc_tablero = ct_id WHERE cca_estatus = "C" AND cca_fgen > '.$fini.' AND cca_fgen > '.$ffin.'';
  if($tablero) $sql.= ' AND ct_nmb LIKE "%'.$tablero.'%"';
  if($cotizacion) $sql.= ' AND cc_folio LIKE "%'.$cotizacion.'%"';
  if($usuario) $sql.= ' AND cca_ugen LIKE "%'.$usuario.'%"';
  $sql .= ' ORDER BY cca_fgen DESC';

  
  //$result = setq($sql);
  //if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}

function resulttabs($fini,$ffin,$vendedor){ //,$estatus
  $grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
  $sql = 'SELECT * FROM crm_tableros WHERE ct_fini BETWEEN "'.$fini.'" AND "'.$ffin.'"';
  if($grupo != "ADMIN" && $grupo != "GERENCIA")
    if($vendedor) $sql.=' AND ct_agente = "'.$vendedor.'"';
  else $sql.=' AND ct_agente = "'.$vendedor.'"';
  $this->resultt = setq($sql);
}

function resultexcedentes($fini, $ffin){
  $sql = 'SELECT * FROM cxcobrar INNER JOIN remisiones ON cx_referencia = r_id WHERE DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND cx_abonado > cx_importe AND r_estatus IN("F", "FE", "A")';
  $this->result = setq($sql);
}

function resultventascaducadas($fini, $ffin, $asesor){
  $sql = 'SELECT * FROM remisiones INNER JOIN cxcobrar ON cx_referencia = r_id WHERE DATE(r_fliquidacion) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_vencida = "1" AND cx_tipo = "R" AND cx_abonado != 0';
  if($asesor) $sql .= ' AND r_uaplica = "'.$asesor.'"';
  $this->result = setq($sql);
}

function resultenvios($fini, $ffin, $asesor){
  $sql = 'SELECT * FROM remisiones INNER JOIN remisionesc ON rc_remision = r_id INNER JOIN crm_cotizaciones ON cc_remision = r_id WHERE DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus IN("F", "FE", "A") AND rc_tipoenvio IN ("D", "O") GROUP BY r_id';
  if($asesor) $sql .= ' AND r_uaplica = "'.$asesor.'"';
  $this->result = setq($sql);
}

}

class viewreportes{
  var $model;

function __construct($model) {
  $this->model = $model;
}

function showreportes(){
/*
  echo '<div class="row">
    <div class="col-md-12 bg-blue bg-darken-3 text-white">
      <h2>Dashboard</h2>
    </div>
  ';

  echo '';
  echo '
<div class="table-responsive" bis_skin_checked="1">
  <table class="table">
    <tbody>
      <tr>
        <td>
          <div class="pb-1 pt-1"><center> <a href="?modulo=reportes&accion=dashclientes">
          <button class="btn btn-secondary"><i class="fas fa-users"></i> Dashboard Clientes</button>
          </a></center></div>
        </td>

        <td>
          <div class="pb-1 pt-1"><center> <a href="?modulo=reportes&accion=dashproductos">
          <button class="btn btn-secondary"><i class="fab fa-dropbox"></i> Dashboard Productos</button>
          </a></center></div>
        </td>

        <td>
          <div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=dashcompras">
          <button class="btn btn-secondary"><i class="fas fa-money-bill-wave"></i> Dashboard Compras</button>
          </a></center></div>
        </td>

        <td>
          <div class="pb-1 pt-1"><center> <a href="?modulo=reportes&accion=dashtableros">
            <button class="btn btn-secondary"><i class="fas fa-cart-plus"></i> Dashboard Tableros</button>
          </a></center></div>
        </td>
        <td>
          <div class="pb-1 pt-1"><center> <a href="?modulo=reportes&accion=dashfacturas">
            <button class="btn btn-secondary"><i class="far fa-file-word"></i> Dashboard Facturación</button>
          </a></center></div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="pb-1 pt-1"><center> <a href="?modulo=reportes&accion=financieros">
            <button class="btn btn-secondary"><i class="fas fa-dollar-sign"></i> Análisis Financiero</button>
          </a></center></div>
        </td>

      </tr>
    </tbody>
  </table>
</div>
  ';
*/




  echo '<div class="row">
    <div class="col-md-12 bg-blue bg-darken-3 text-white">
      <h2>Reportes</h2>
    </div>
  ';

  echo '</tbody></table>';
  echo '
<div class="table-responsive" bis_skin_checked="1">
  <table class="table">
      <thead class="">
          <tr>
              <th><div class="bg-blue bg-darken-2 text-white"><center> <h5>Ventas</h5> </center></div></th>
              <th><div class="bg-blue bg-darken-2 text-white"><center> <h5>Inventarios / Compras</h5></center></div></th>
              <th><div class="bg-blue bg-darken-2 text-white"><center> <h5>Facturación / Finanzas</h5> </center></div></th>
              <th><div class="bg-blue bg-darken-2 text-white"><center> <h5>Otros</h5> </center></div></th>
          </tr>
      </thead>
      <tbody>
          <tr>
              <td><div class="pb-1 pt-1"><center> <a href="?modulo=reportes&accion=ventasd">
              <button class="btn btn-secondary"> Detalle de ventas </button>
              </a></center></div></td>

              <td><div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=existencias">
              <button class="btn btn-secondary"> Existencias </button>
              </a></center></div></td>

              <th><div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=facturacion">
              <button class="btn btn-secondary"> Facturación / Finanzas </button>
              </a></center></div></th>
              
              <td><center><a href="?modulo=reportes&accion=cambios";
                <button id="historial" type="button" class="btn btn-secondary">
                  Cambios en paquetes
                </button>
              </a></center></td>';
              /* if($_SESSION['emp'] == "1")
              echo '
              <td><div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=conversiones">
              <button class="btn btn-secondary">Mailing - Conversiones</button>
              </a></center></div></td>'; */
              
            echo '

          </tr>
          <tr>
              <td><div class="pb-1 pt-1"><center> <a href="?modulo=reportes&accion=ventasxprod">
              <button class="btn btn-secondary"> Ventas por producto </button>
              </a></center></div></td>';
              
                echo '<td>
                  <div class="pb-1 pt-1"><center> <a href="?modulo=reportes&accion=historialmov">
                    <button class="btn btn-secondary"> Historial de movimientos </button>
                  </a></center></div>
                </td>';

              echo '<!-- <td><div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=comprasd">
              <button class="btn btn-secondary"> Detalle de compras </button>
              </a></center></div></td> -->

              <td><div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=facturasemirec">
              <button class="btn btn-secondary">Balance Emitidas y recibidas </button>
              </a></center></div></td>

              <td><center><a href="?modulo=reportes&accion=envios";
                <button id="historial" type="button" class="btn btn-secondary">
                  Destinos de envios
                </button>
              </a></center></td>
              
          </tr>';
          
          echo '<tr>';
            echo '<td>
              <center><a href="?modulo=reportes&accion=ventascaducadas";
                <button id="excede" type="button" class="btn btn-secondary">
                  Ventas vencidas
                </button>
              </a></center>
            </td>';
            
              echo '<td>
              <center><a href="?modulo=reportes&accion=embarquesxprod";
                <button id="excede" type="button" class="btn btn-secondary">
                  Embarques por producto
                </button>
              </a></center> 
              </td>';
            
            echo '<td>
              <center><a href="?modulo=reportes&accion=formapago";
                <button id="fpago" type="button" class="btn btn-secondary">
                  Formas de pago
                </button>
              </a></center>
            </td>
            <td>
              <center><a href="?modulo=reportes&accion=cobroenvio";
                <button id="costoenvio" type="button" class="btn btn-secondary">
                  Costos de envio
                </button>
              </a></center>
            </td>
          </tr>';
            echo '<tr>
            <td></td>';
            echo '<td>
              <center><a href="?modulo=reportes&accion=movimientosxprod";
                <button id="excede" type="button" class="btn btn-secondary">
                  Movimientos por producto
                </button>
              </a></center> 
              </td>';
            echo '<td>
              <center><a href="?modulo=reportes&accion=excedentes";
                <button id="excede" type="button" class="btn btn-secondary">
                  Excedentes
                </button>
              </a></center>
            </td>
            <td></td>
          </tr>';
          echo '<tr>
            <td></td>
            <td></td>
            <td>
              <center><a href="?modulo=reportes&accion=ventaspagos";
                <button id="excede" type="button" class="btn btn-secondary">
                  Estado de cuenta Ventas
                </button>
              </a></center>
            </td>
            <td></td>
          </tr>';

          
      echo '</tbody>
  </table>
</div>
  ';

  /* if($_SESSION['emp'] == "1"){
    echo '
    <div class="col-md-12 bg-blue bg-darken-3 text-white">
      <h2>Dashboard TYE</h2>
    </div>
      <div class="table-responsive" bis_skin_checked="1">
        <table class="table">
            <thead class="">
                <tr>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=conversiones">
                    <button class="btn btn-secondary">Mailing - Conversiones</button>
                    </a></center></div></td>

                    <td><div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=estadisticos">
                    <button class="btn btn-secondary">Estadisticos JD CEO</button>
                    </a></center></div></td>

                    <td><div class="pb-1 pt-1"><center><a href="?modulo=reportes&accion=escuelavirtual">
                    <button class="btn btn-secondary">Estadisticos Escuela virtual JD Shop</button>
                    </a></center></div></td>
                </tr>
            </tbody>
        </table>
      </div>
    ';

  }
   */


}

function existencias(){
  if (!isset($_POST['categoria'])) $_POST['categoria'] = NULL;
  if (!isset($_POST['articulo'])) $_POST['articulo'] = NULL;
  if (!isset($_REQUEST['almacen'])) $_REQUEST['almacen'] = NULL;
  $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
  if($grupo != "VENTAS"){
    $atras = '<a href="?modulo=reportes&accion=index">
              <button type="button" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
            </a>';
  } else {
    $atras = "";
  }
  
  $nuevo = ' <a target="_blank" href="formats/pdfexistencias.php">
              <button type="button" class="btn btn-sm btn-danger mb-1 mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </a>
            <a href="formats/xlsxexistencias.php">
              <button type="button" class="btn btn-sm btn-success mb-1 mr-1"><i class="fas fa-file-excel"></i> Excel</button>
            </a>';

    $filtro = '
    <form autocomplete="off" action="?modulo=reportes&accion=existencias" method="post" id="filtro" onsubmit="return checkSubmitenviar();" class="">
      <div class="mb-5">
        <label class="mr-sm-2">Producto:</label>
        <input type="text" name="articulo" class="form-control" value="'.$_POST['articulo'].'" onfocus="this.select();" placeholder="Escribe el nombre del artículo"/>
      </div>
      <div class="mb-5" style="max-width: 250px">
        <label for="">Categoria:</label>
        <select id="pref-orderby" class="form-control" name="categoria" placeholder="Categoria">
          <option value="">TODAS LAS CATEGORIAS</option>';

          $sql = 'SELECT * FROM categorias WHERE cat_estatus = "A"  ORDER BY cat_nmb';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if($_POST['categoria'] == $row['cat_id']) $sel = "selected"; else $sel = "";
            $filtro.='<option value="'.$row['cat_id'].'" '.$sel.'>'.$row['cat_nmb'].'</option>';
          }
          $filtro.='
        </select>
      </div>
      <div class="mb-5" style="max-width: 250px">
        <label for="">Almacén:</label>
        <select id="pref-orderby" class="form-control" name="almacen" placeholder="almacen">
          <option value="">TODOS LOS ALMACENES</option>';

          $sql = 'SELECT * FROM almacenes WHERE a_estatus = "A"  ORDER BY a_nmb';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if($_POST['almacen'] == $row['a_id']) $sel = "selected"; else $sel = "";
            $filtro.='<option value="'.$row['a_id'].'" '.$sel.'>'.$row['a_nmb'].'</option>';
          }
          $filtro.='
        </select>
      </div>
      <div class="mb-5">
      <label for="">Acciones:</label><br>
      <button type="submit"  class="btn btn-info">
      <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
    </button>
    <!-- <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-sm btn-warning"> -->
    <button type="submit" class="btn btn-warning">
      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Limpiar
    </button><!-- </a> -->
    </div>
    </form>';
  toolbar("EXISTENCIAS",$atras,$filtro,$nuevo);

    $sql2 = '';
    if ($_POST['categoria']) {
      $sql2.='AND a_categoria = "' . $_POST['categoria'] . '" ';
    }
    if ($_POST['articulo']) {
      $sql2.='AND a_nmb LIKE "%' . $_POST['articulo'] . '%" OR a_modelo LIKE "%' . $_POST['articulo'] . '%" OR a_cb LIKE "%' . $_POST['articulo'] . '%" ';
    }

    $admin="";
    $sqlal = 'SELECT * FROM almacenes where a_estatus="A" ';
    if($_REQUEST['almacen']) $sqlal.=' AND a_id = "'.$_REQUEST['almacen'].'" ';
    $resultal = setq($sqlal) or die($sqlal);
    while ($rowal = $resultal->fetch_array()) {
      $datosla = existenciaxestatus($rowal['a_id'], "LA");
      //$datosla = json_decode($la); 
      $datosva = existenciaxestatus($rowal['a_id'], "VA");
      //$datosva = json_decode($va); 
      $datosvp = existenciaxestatus($rowal['a_id'], "VP");
      //$datosvp = json_decode($vp); 
      if ($rowal['a_vendible'] == 1) $ven = 'vendible'; 
      else $ven = 'No vendible';
      $sqlex = 'SELECT a_id id, a_nmb nmb, cat_nmb categoria, cat_id catid FROM articulos INNER JOIN categorias ON a_categoria = cat_id WHERE a_tipoprod != "M" AND a_inventariado = "1" ';
      if($_REQUEST['categoria']) $sqlex.=' AND cat_id = "'.$_REQUEST['categoria'].'"';
      if($_REQUEST['articulo']) $sqlex.=' AND (a_nmb LIKE "%'.$_REQUEST['articulo'].'%" OR a_cb LIKE "%'.$_REQUEST['articulo'].'%")';
      $sqlex .= 'ORDER BY a_nmb ASC';
      $resultex = setq($sqlex) or die($sqlex);
      /* $sqlcomp = 'SELECT SUM(e_cantidad) FROM existencias INNER JOIN articulos ON a_id = e_articulo
                  WHERE a_estatus = "A" '.$admin.' AND  e_almacen = "' . $rowal['a_id'] . '"
                  ' . $sql2 . '
                  GROUP BY a_id ORDER BY a_nmb';
      $resultcomp = setq($sqlcomp) or die($sqlcomp);
      $exi = $resultcomp->fetch_array(); */
      echo '<div class="table-responsive">
        <table width="90%" class="mb-0 table table-hover" id="myTable">
          <thead class="tfill-blue">
            <tr>
              <th colspan="3">Productos en almacen ' . $rowal['a_nmb'] . ' "' . $ven . '"</th>
              <th colspan="6">Existencias</th>
            </tr>
            <tr>
              <th width="35%">Artículo</th>
              <th width="10%">Modelo</th>
              <th width="15%">Categoria</th>
              <th width="8%">Libre</th>
              <!-- <th width="8%">Libre-Apartado</th> -->
              <th width="8%">Apartado</th>
              <!-- <th width="8%">Vendido-Pagado</th> -->
              <th width="8%">Físico</th>
              <th width="8%">Por reponer</th>
              ';
            echo '</tr>
          </thead> 
          <tbody>'; 
          $libres = array();
          $apartados = array();
          $fisic = array();
          $xreponer = array();
              while ($row = $resultex->fetch_array()) {
                $dif = 0;
                $var = busca($row['id'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
                if($var){ 
                  $sqlvar = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$row['id'].'"';
                  $resultvar = setq($sqlvar);
                  while($rowvar = $resultvar -> fetch_array()){
                    $articulo = $row['nmb'].' '.$rowvar['av_nmb'];
                    $modelo = $rowvar['av_modelo'];
                    $existencia = existenciaModelo($row['id'], $rowal['a_id'], $rowvar['av_modelo']);

                    $libre = $existencia; // - $libre_apartado
                    if(floatval($libre) < 1){ 
                      $dif = abs($libre);
                      $libre = 0;
                    }
                    $vendido_abonado = $datosva[$row['id']][$modelo];
                    $vendido_pagado = $datosvp[$row['id']][$modelo];
                    $reponer = busca($row['id'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_modelo = "'.$rowvar['av_modelo'].'" AND xp_almacen = "'.$rowal['a_id'].'" AND xp_articulo', 'xp_cantidad');
                    $disponibles = $libre  + $vendido_pagado + $vendido_abonado - $reponer; //+ $libre_apartado  - $dif
                    // echo $libre  + $vendido_pagado + $vendido_abonado - $reponer.' <br>';
                    if($_SESSION['uid'] == "ADMIN" && $row['id'] == 127) {
                      // echo "$libre  + $vendido_pagado + $vendido_abonado - $reponer";
                    }
                    if($disponibles < 1) $disponibles = 0;

                    //$reponer += $dif;
                    echo '<tr>
                          <th>' . $articulo . '</th>
                          <th>' . $modelo . '</th>
                          <td>' . $row['categoria']. '</td>
                          <td class="text-center bg-success">' . number_format($libre, 0) . '</td>';
                    //echo '<td class="text-center"> '.number_format($libre_apartado, 0).' </td>';
                    echo '<td class="text-center"> '.number_format($vendido_abonado+$vendido_pagado, 0).' </td>';
                    echo '<td class="text-center"> '.number_format($disponibles, 0).' </td> </td>';
                    echo '<td class="text-center"> '.number_format($reponer, 0).' </td> </td>';
                    echo'</tr>';

                    if(!isset($libres[$row['catid']])) $libres[$row['catid']] = 0;
                    $libres[$row['catid']]+=$libre;
                    if(!isset($apartados[$row['catid']])) $apartados[$row['catid']] = 0;
                    $apartados[$row['catid']]+=$vendido_abonado+$vendido_pagado;
                    if(!isset($fisic[$row['catid']])) $fisic[$row['catid']] = 0;
                    $fisic[$row['catid']]+=$disponibles;
                    if(!isset($xreponer[$row['catid']])) $xreponer[$row['catid']] = 0;
                    $xreponer[$row['catid']]+=$reponer;
                  }
                } else {
                  $articulo = $row['nmb'];
                  $modelo = "";
                  //echo 'datos LA: '.$datosla[$row['id']][$row['modelo']];
                  //echo 'articulo: '.$row['id'].' modelo: '.$row['modelo'];
                  
                  $existencia = existencia($row['id'], $rowal['a_id']);
                  /* if(floatval($existencia) == 0) $libre_apartado = 0;
                  else $libre_apartado = $datosla[$row['id']][$modelo]; */
                  $libre = $existencia; // - $libre_apartado
                  if(floatval($libre) < 1){ 
                    $dif = abs($libre);
                    $libre = 0;
                  }
                  $vendido_abonado = $datosva[$row['id']][$modelo];
                  $vendido_pagado = $datosvp[$row['id']][$modelo];
                  $reponer = busca($row['id'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_almacen = "'.$rowal['a_id'].'" AND xp_articulo', 'xp_cantidad');
                  $disponibles = $libre + $vendido_abonado + $vendido_pagado - $reponer; //+ $libre_apartado - $dif
                  if($disponibles < 0) $disponibles = 0;
                  
                  echo '<tr>
                        <th>' . $articulo . '</th>
                        <th>' . $modelo . '</th>
                        <td>' . $row['categoria'] . '</td>
                        <td class="text-center bg-success">' . number_format($libre, 0) . '</td>';
                  //echo '<td class="text-center"> '.number_format($libre_apartado, 0).' </td>';
                  echo '<td class="text-center"> '.number_format($vendido_abonado+$vendido_pagado, 0).' </td>';
                  echo '<td class="text-center"> '.number_format($disponibles, 0).' </td> </td>';
                  echo '<td class="text-center"> '.number_format($reponer, 0).' </td> </td>';
                  echo'</tr>';

                  if(!isset($libres[$row['catid']])) $libres[$row['catid']] = 0;
                    $libres[$row['catid']]+=$libre;
                    if(!isset($apartados[$row['catid']])) $apartados[$row['catid']] = 0;
                    $apartados[$row['catid']]+=$vendido_abonado+$vendido_pagado;
                    if(!isset($fisic[$row['catid']])) $fisic[$row['catid']] = 0;
                    $fisic[$row['catid']]+=$disponibles;
                    if(!isset($xreponer[$row['catid']])) $xreponer[$row['catid']] = 0;
                    $xreponer[$row['catid']]+=$reponer;
                }
              }
          }
          echo '<tbody>
        </table>
      </div><br><br>';
      //echo'libres'.var_dump($libres); 
      $sqlcategoria = 'SELECT * FROM categorias WHERE cat_estatus = "A"  ORDER BY cat_nmb';
      $resultcategoria = setq($sqlcategoria);
      echo'
      <div class="table-responsive col-md-6">
        <table width="100%" class="table table-hover" id="myTableresum">
          <thead class="tfill-blue">
            <tr>
              <th width="40%" colspan="1">RESUMEN</th>
              <th width="50%" colspan="4">Total Existencias</th>
            </tr>
            <tr>
              <th width="40%">Categoría</th>
              <th width="15%">Libre</th>
              <th width="15%">Apartado</th>
              <th width="15%">Físico</th>
              <th width="15%">Por reponer</th>
            </tr>
          </thead>
          <tbody>';
          $tlibres = 0;
          $tapartados = 0;
          $tfisic = 0;
          $txreponer = 0;
          while($rowc = $resultcategoria->fetch_array()){
            echo '<tr>
                    <th>'.$rowc['cat_nmb'].'</th>
                    <th class="text-center bg-success">'.number_format($libres[$rowc['cat_id']],0).'</th>
                    <th class="text-center">'.number_format($apartados[$rowc['cat_id']],0).'</th>
                    <th class="text-center">'.number_format($fisic[$rowc['cat_id']],0).'</th>
                    <th class="text-center">'.number_format($xreponer[$rowc['cat_id']],0).'</th>
                  </tr>';
            $tlibres+= $libres[$rowc['cat_id']];
            $tapartados+= $apartados[$rowc['cat_id']];
            $tfisic+= $fisic[$rowc['cat_id']];
            $txreponer+= $xreponer[$rowc['cat_id']];
          }
          echo'
          </tbody>
          <tfoot class="bg-secondary">
            <tr>
              <th class="text-center">TOTALES</th>
              <th class="text-center bg-success">'.number_format($tlibres,0).'</th>
              <th class="text-center">'.number_format($tapartados,0).'</th>
              <th class="text-center">'.number_format($tfisic,0).'</th>
              <th class="text-center">'.number_format($txreponer,0).'</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>';
}
function existenciasprod(){
  if (!isset($_POST['articulo'])) $_POST['articulo'] = NULL;
  if (!isset($_REQUEST['almacen'])) $_REQUEST['almacen'] = NULL;
  $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
  if($grupo != "VENTAS"){
    $atras = '<a href="?modulo=reportes&accion=index">
              <button type="button" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
            </a>';
  } else {
    $atras = "";
  }
  
  $nuevo = ' <a target="_blank" href="formats/pdfexistenciasprod.php">
              <button type="button" class="btn btn-sm btn-danger mb-1 mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </a>
            <a href="formats/xlsxexistenciasprod.php">
              <button type="button" class="btn btn-sm btn-success mb-1 mr-1"><i class="fas fa-file-excel"></i> Excel</button>
            </a>';

    $filtro = '
    <form autocomplete="off" action="?modulo=reportes&accion=existenciasprod" method="post" id="filtro" onsubmit="return checkSubmitenviar();" class="">
      <div class="mb-5">
        <label class="mr-sm-2">Producto:</label>
        <input type="text" name="articulo" class="form-control" value="'.$_POST['articulo'].'" onfocus="this.select();" placeholder="Escribe el nombre del artículo"/>
      </div>
      <div class="mb-5" style="max-width: 250px">
        <label for="">Almacén:</label>
        <select id="pref-orderby" class="form-control" name="almacen" placeholder="almacen">
          <option value="">TODOS LOS ALMACENES</option>';
          $sql = 'SELECT * FROM pr_almacenes WHERE pa_estatus = "A"  ORDER BY pa_nmb';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if($_POST['almacen'] == $row['pa_id']) $sel = "selected"; else $sel = "";
            $filtro.='<option value="'.$row['pa_id'].'" '.$sel.'>'.$row['pa_nmb'].'</option>';
          }
          $filtro.='
        </select>
      </div>
      <div class="mb-5">
      <label for="">Acciones:</label><br>
      <button type="submit"  class="btn btn-info">
      <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
    </button>
    <!-- <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-sm btn-warning"> -->
    <button type="submit" class="btn btn-warning">
      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Limpiar
    </button><!-- </a> -->
    </div>
    </form>';
  toolbar("EXISTENCIAS PRODUCCIÓN",$atras,$filtro,$nuevo);

    $sql2 = '';
    if ($_POST['articulo']) {
      $sql2.='AND a_nmb LIKE "%' . $_POST['articulo'] . '%" OR a_modelo LIKE "%' . $_POST['articulo'] . '%" OR a_cb LIKE "%' . $_POST['articulo'] . '%" ';
    }

    $admin="";
    $sqlal = 'SELECT * FROM pr_almacenes where pa_estatus="A"';
    if($_REQUEST['almacen']) $sqlal.=' AND pa_id = "'.$_REQUEST['almacen'].'" ';
    $resultal = setq($sqlal) or die($sqlal);
    while ($rowal = $resultal->fetch_array()) {
      if($rowal['pa_tipoa'] == "M"){
        $tiposm = array('A'=>'Área', 'P'=>'Pieza', 'V'=>'Volumen');
        $tiposu = array('A'=>'mts cuadrados', 'P'=>'unidades', 'V'=>'mts cúbicos');

        $sqlex = 'SELECT pmp_id id, pmp_nmb nmb, pmp_tipo tipo FROM pr_materiaprima WHERE pmp_estatus = "A"';
        if($_REQUEST['articulo']) $sqlex.=' AND (pmp_nmb LIKE "%'.$_REQUEST['articulo'].'%")';
        $sqlex .= 'ORDER BY pmp_nmb ASC';
      } else{
      $k = 0;
      $sqlcat = 'SELECT cat_id FROM categorias WHERE cat_estatus = "A" AND cat_inflable = "1"';
      $resultcat = setq($sqlcat);
      while($rowcat = $resultcat->fetch_array()){
        if($k == 0){
          $categorias .= $rowcat['cat_id'];
        } else{
          $categorias .= ",".$rowcat['cat_id'];
        }
        $k++;
      }
      //$datosvp = json_decode($vp); 
      if ($rowal['pa_vendible'] == 1) $ven = 'vendible'; 
      else $ven = 'No vendible';
      $sqlex = 'SELECT a_id id, a_nmb nmb, cat_nmb categoria FROM articulos INNER JOIN categorias ON a_categoria = cat_id WHERE a_tipoprod != "M" AND a_inventariado = "1" ';
      $sqlex.=' AND cat_id IN ('.$categorias.')';
      
      if($_REQUEST['articulo']) $sqlex.=' AND (a_nmb LIKE "%'.$_REQUEST['articulo'].'%" OR a_cb LIKE "%'.$_REQUEST['articulo'].'%")';
      $sqlex .= 'ORDER BY a_nmb ASC';
      }
      $resultex = setq($sqlex) or die($sqlex);

      echo '<div class="table-responsive">
        <table width="90%" class="mb-0 table table-hover" id="myTable">
          <thead class="tfill-blue">';
            if($rowal['pa_tipoa'] == "M"){
              echo '
              <tr>
                <th colspan="3">Productos en almacen ' . $rowal['pa_nmb'] . '</th>
              </tr>
              <tr>';
              echo '<th width="50%">Artículo</th>
              <th width="25%">Tipo</th>
              <th width="25%">Existencia</th>';
              // if($rowal['pa_id'] == "1") echo '<th class="">Merma</th>';
            } else{
              echo '
              <tr>
                <th colspan="4">Productos en almacen ' . $rowal['pa_nmb'] . '</th>
              </tr>
              <tr>';
              echo '<th width="30%">Artículo</th>
              <th width="20%">Modelo</th>
              <th width="25%">Tipo</th>
              <th width="25%">Existencia</th>';
            }
            echo '</tr>
          </thead> 
          <tbody>'; 
            if($rowal['pa_tipoa'] == "M"){
              while ($row = $resultex->fetch_array()) {
                $articulo = $row['nmb'];
                
                $existencia = existenciamp($row['id'], $rowal['pa_id']);
                
                echo '<tr>
                      <th>' . $articulo . '</th>
                      <td>' . $tiposm[$row['tipo']] . '</td>
                      <td class="text-center bg-success">' . number_format($existencia, 0) . ' '.$tiposu[$row['tipo']].'</td>';
                /* $usados = intval(busca($row['am_mp'], 'articulos_mermas_usados', 'amu_largo = "'.$row['am_largo'].'" AND amu_ancho = "'.$row['am_ancho'].'" AND amu_alto = "'.$row['am_alto'].'" AND amu_ammp', 'SUM(amu_cantidad)'));
                $disponibles = (intval($existencia) - intval($usados)); */
                /* if($rowal['pa_id'] == "1"){
                  $sqlmer  = 'SELECT * FROM articulos_mermas_usados WHERE   '
                } */
                echo'</tr>';
              }
            } else{
              while ($row = $resultex->fetch_array()) {
                $dif = 0;
                $var = busca($row['id'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
                if($var){ 
                  $sqlvar = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$row['id'].'"';
                  $resultvar = setq($sqlvar);
                  while($rowvar = $resultvar -> fetch_array()){
                    $articulo = $row['nmb'].' '.$rowvar['av_nmb'];
                    $modelo = $rowvar['av_modelo'];                    
                    $existencia = existenciaModeloProduccion($row['id'], $rowal['pa_id'], $rowvar['av_modelo']);

                    echo '<tr>
                          <th>' . $articulo . '</th>
                          <th>' . $modelo . '</th>
                          <td>' . $row['categoria']. '</td>
                          <td class="text-center bg-success"> '.number_format($existencia, 0).' </td>';
                    echo'</tr>';
                  }
                } else {
                  $articulo = $row['nmb'];
                  $modelo = "";                  
                  $existencia = existenciaProduccion($row['id'], $rowal['pa_id']);
                  
                  echo '<tr>
                        <th>' . $articulo . '</th>
                        <th>' . $modelo . '</th>
                        <td>' . $row['categoria'] . '</td>
                        <td class="text-center bg-success">' . number_format($existencia, 0) . '</td>';
                  echo'</tr>';
                }
              }
            }
          }
          echo '<tbody>
        </table>
      </div>
    </div>';
}
function facturacion($fini,$ffin,$cliente,$estatus){
  $selt = "";
  $selc = "";
  $sel = "";
  if($estatus == "T") $selt = "selected";
  elseif($estatus == "C") $selc = "selected";
  else $sel = "selected";

    //Sección: E2 Encabezado - Filtros
   $filtro =  '<form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=facturacion">
            <div class="mb-5 mr-2">
              <span>Cliente</span>
              <input type="text" class="form-control" id="pref-search" name="cliente" value=" $cliente ?>" placeholder="Buscar por cliente">
            </div><!-- form group [search] -->
            <div class="mb-5 mr-2">
              <span>Fecha inicial</span>
              <input type="date" name="fini" class="form-control" value=" $fini ?>" />
            </div><!-- form group [search] -->
            <div class="mb-5 mr-2">
              <span>Fecha Final</span>
              <input type="date" name="ffin" class="form-control" value=" $ffin?>" />
            </div><!-- form group [search] -->
            <div class="mb-5 mr-1">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value=""  $sel; ?> >Todos</option>
                <option value="T"  $selt; ?> >Timbradas</option>
                <option value="C"  $selc; ?> >Canceladas</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
            <button type="submit" class="btn btn-info">
              <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
            </button>
            <a href="?modulo=reportes&accion=facturacion"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
            </button></a>
            </div>
          </form>';

  toolbar('FACTURACIÓN', "", $filtro, "", "");
    
  $this->nmbmetodopago = array("PUE" => "PAGO EN UNA SOLA EXHIBICION", "PPD" => "PAGO EN PARCIALIDADES O DIFERIDO", "NO IDENTIFICADO" => "No Identificado");

  echo '<div class="table">
          <table  class="mb-0 table table-hover table-striped" id="myTable">
            <thead class="bg-light-blue bg-darken-2">
            <tr>
            <td></td>
            <th width="8%">RFC</th>
            <th width="15%">Cliente</th>
            <th width="13%">Fecha</th>
            <th width="10%">Estatus</th>
            <th width="10%">Método</th>
            <th width="10%">Forma</th>
            <th>Subtotal</th>
            <th>IVA</th>
            <th>Total</th>
          </tr></thead>';

  $totsubtotal = 0;
  $totiva = 0;
  $totretiva = 0;
  $totisr = 0;
  $sumtot = 0;
  while($row = $this->model->resultmonto->fetch_array()){
    echo '<tr>
            <td>'.$row['f_folio'].'</td>
            <td>'.busca($row['f_fiscales'],'crm_fiscales','cf_id','cf_rfc').'</td>
            <td>'.busca($row['f_fiscales'],'crm_fiscales','cf_id','cf_razonsocial').'</td>';

      $total = $row['f_subtotal']+$row['f_iva']-$row['f_isr']-$row['f_retiva'];

      if($row['f_estatus'] == "T"){ $estatus = "VIGENTE"; $stl = 'bg-success'; }
      elseif($row['f_estatus'] == "C"){ $estatus = "CANCELADA"; $stl = 'bg-danger'; }

      echo '<td>'.date('d-m-y',strtotime($row['f_ftimbro'])).'</td>';
      echo '<td class="'.$stl.'">'.$estatus.'</td>
            <td class="text-medium">'.$row['f_metodopago'].'</td>
            <td class="text-medium">'.busca($row['f_fpago'],'cfdi_fpago','cf_id','cf_descripcion').'</td>';
      echo '<td class="number-align">'.number_format($row['f_subtotal'],2).'</td>';
      echo '<td class="number-align">'.number_format($row['f_iva'],2).'</td>';
      echo '<td class="number-align">'.number_format($total,2).'</td>';

      $totsubtotal+=$row['f_subtotal'];
      $totiva+=$row['f_iva'];
      $totretiva+=$row['f_retiva'];
      $totisr+=$row['f_isr'];
      $sumtot+=$total;

    echo '</tr>';
     }
  echo '<tfoot><tr><td colspan="7" style="text-align:right;font-size: 15pt;">Total </td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($totsubtotal,2).'</td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($totiva,2).'</td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($sumtot,2).'</td>
        </tr>';
     echo '</tfoot>
     
     
     <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength: "50",
            
        });
      </script>';
}

function facturasemirec($fini,$ffin,$cliente,$estatus){
  $selt = "";
  $selc = "";
  $sel = "";
  if($estatus == "T") $selt = "selected";
  elseif($estatus == "C") $selc = "selected";
  else $sel = "selected";

    $filtro ='
    <form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=facturasemirec">
      <div class="mb-5 mr-2">
        <span>Cliente</span>
        <input type="text" class="form-control" id="pref-search" name="cliente" value="'.$cliente .'" placeholder="Buscar por cliente">
      </div><!-- form group [search] -->
      <div class="mb-5 mr-2">
        <span>Fecha inicial</span>
        <input type="date" name="fini" class="form-control" value="'.$fini .'" />
      </div><!-- form group [search] -->
      <div class="mb-5 mr-2">
        <span>Fecha Final</span>
        <input type="date" name="ffin" class="form-control" value="'.$ffin.'" />
      </div><!-- form group [search] -->
      <div class="mb-5 mr-1">
        <label for="tipom">Estatus</label>
        <select class="form-control" name="estatus" id="estatus" >
          <option value="" '.$sel .' >Todos</option>
          <option value="T" '.$selt .' >Timbradas</option>
          <option value="C" '.$selc .' >Canceladas</option>
        </select>
      </div><!-- form group [search] -->
      <div class="mb-5">
      <button type="submit" class="btn btn-info">
        <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
      </button>
      <a href="?modulo=reportes&accion=facturacion"><button type="button" class="btn btn-warning">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
      </button></a>
      </div>
    </form>';

  toolbar('BALANCE FACTURACIÓN', "", $filtro);
  $this->nmbmetodopago = array("PUE" => "PAGO EN UNA SOLA EXHIBICION", "PPD" => "PAGO EN PARCIALIDADES O DIFERIDO", "NO IDENTIFICADO" => "No Identificado");

  echo '<div class="table">
          <table class="mb-0 table table-hover table-striped">
            <thead class="bg-primary text-white">
            <tr>
            <th width="8%">Folio</th>
            <th width="8%">RFC</th>
            <th width="15%">Cliente</th>
            <th width="13%">Fecha</th>
            <th width="10%">Estatus</th>
            <th width="10%">Método</th>
            <th width="10%">Forma</th>
            <th>Subtotal</th>
            <th>IVA</th>
            <th>Total</th>
          </tr></thead>';

  $totsubtotal = 0;
  $totiva = 0;
  $totretiva = 0;
  $totisr = 0;
  $sumtot = 0;
  while($row = $this->model->resultmonto->fetch_array()){
    echo '<tr>
            <td class="text-medium">'.$row['f_folio'].'</td>
            <td class="text-medium">'.busca($row['f_fiscales'],'crm_fiscales','cf_id','cf_rfc').'</td>
            <td class="text-medium">'.busca($row['f_fiscales'],'crm_fiscales','cf_id','cf_razonsocial').'</td>';

      $total = $row['f_subtotal']+$row['f_iva']-$row['f_isr']-$row['f_retiva'];

      if($row['f_estatus'] == "T"){ $estatus = "VIGENTE"; $stl = 'bg-success'; }
      elseif($row['f_estatus'] == "C"){ $estatus = "CANCELADA"; $stl = 'bg-danger'; }

      echo '<td class="text-medium">'.date('d-m-y',strtotime($row['f_ftimbro'])).'</td>';
      echo '<td class="'.$stl.'">'.$estatus.'</td>
            <td class="text-medium">'.$row['f_metodopago'].'</td>
            <td class="text-medium">'.busca($row['f_fpago'],'cfdi_fpago','cf_id','cf_descripcion').'</td>';
      echo '<td class="number-align">'.number_format($row['f_subtotal'],2).'</td>';
      echo '<td class="number-align">'.number_format($row['f_iva'],2).'</td>';
      echo '<td class="number-align">'.number_format($total,2).'</td>';

      $totsubtotal+=$row['f_subtotal'];
      $totiva+=$row['f_iva'];
      $totretiva+=$row['f_retiva'];
      $totisr+=$row['f_isr'];
      $sumtot+=$total;

    echo '</tr>';
     }
  echo '<tfoot><tr><td colspan="7" style="text-align:right;font-size: 15pt;">Total </td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($totsubtotal,2).'</td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($totiva,2).'</td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($sumtot,2).'</td>
        </tr>';
     echo '</tfoot></table></div>';

    $total = 0;
    echo '<div class="table">
        <table class="mb-0 table table-hover table-striped">
        <thead class="bg-primary text-white">
        <tr><th colspan="5" width="75%">Factura</th><th colspan="1" width="15%">Impuestos</th>
        <th rowspan="2" width="10%">Total</th>
        </tr><tr width="5%"><th>Factura</th>
        <th width="8%">Fecha</th>
        <th width="25%">Cliente</th>
        <th>Forma de pago</th>
        <th>Subtotal</th>
        <th>IVA</th>
        </tr></thead>';

        $totsubtotal = 0;
        $totiva = 0;
        $totretiva = 0;
        $totisr = 0;
        $sumtot = 0;
        while($row = $this->model->resultrec->fetch_array()){
         echo '<tr>
                <th class="text-medium">'.$row['fr_serie'].''.$row['fr_folio'].'</th>';
          echo '<td class="text-medium">'.date('d-m-y',strtotime($row['fr_fecha'])).'</td>';
          echo '<td class="text-medium">'.$row['fr_nmbproveedor'].'</td>';
          echo '<td class="text-medium">'.busca($row['fr_fpago'],'cfdi_fpago','cf_nmb','cf_descripcion').'</td>';

          $iva = busca($row['fr_id'],'facturasrec_impuestos','fi_tipo = "T" AND fi_impuesto = "002" AND fi_facturas','SUM(fi_importe)');
          if(!$iva) $iva = 0;

          $retiva = busca($row['fr_id'],'facturasrec_impuestos','fi_tipo = "R" AND fi_impuesto = "002" AND fi_facturas','SUM(fi_importe)');
          if(!$retiva) $retiva = 0;

          $isr = busca($row['fr_id'],'facturasrec_impuestos','fi_tipo = "R" AND fi_impuesto = "001" AND fi_facturas','SUM(fi_importe)');
          if(!$isr) $isr = 0;

          $total = $row['fr_total'];

          echo '<td class="text-medium number-align">'.number_format($row['fr_subtotal'],2).'</td>';
          echo '<td class="text-medium number-align">'.number_format($iva,2).'</td>';
          echo '<td class="text-medium number-align">'.number_format($row['fr_total'],2).'</td>';

          $totsubtotal+=$row['fr_subtotal'];
          $totiva+=$iva;
          $totretiva+=$retiva;
          $totisr+=$isr;
          $sumtot+=$total;

          echo '</tr>';
     }
    echo '<tfoot><tr><td colspan="4" style="text-align:right;font-size: 15pt;">Total </td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($totsubtotal,2).'</td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($totiva,2).'</td>
        <td style="font-size: 12pt;text-align:center;">$'.number_format($sumtot,2).'</td>
        </tr>';
    echo '</tfoot></table>';

}


////////////////////////Not working

function compras($fini,$ffin,$proveedor,$estatus,$almacen){
  $sel = "";
  $selt = "";
  $selc = "";
  if($estatus == "A") $selt = "selected";
  elseif($estatus == "C") $selc = "selected";
  else $sel = "selected";


  $sqlal = 'SELECT * FROM almacenes';
  if(busca($_SESSION['uid'],'usuarios','u_id','u_grupo') == "USER"){
    $sqlal.= ' AND a_id = "'.busca($_SESSION['uid'],'usuarios','u_id','u_almacen').'"';
  }
  $resultal = setq($sqlal);
  //Sección: E1 Encabezado - Botones de acción
  echo '<div class="page-title-actions row">';
    //Sección: E2 Encabezado - Filtros
    ?>
    <div class="col-4 col-md-3">
      <a href="formats/xlsxcomprasd.php?<?php echo 'fini='.$fini.'&ffin='.$ffin.'&proveedor='.$proveedor.'&estatus='.$estatus.'&almacen='.$almacen ?>">
      <!-- <a href="formats/xlsxcomprasd.php?<?php echo 'fini='.$fini.'&ffin='.$ffin ?>"> -->
        <button type="button" class="btn btn-success" >
          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-file-excel"></i> Descargar
        </button></a>
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fas fa-search"></i> Filtrar
      </button>
    </div>
    <div id="filter-panel" class="col-md-12 collapse filter-panel">
      <div class="panel panel-default">
        <div class="panel-body">
          <form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=comprasd">
            <div class="mb-5 mr-2">
              <span>Proveedor</span>
              <input type="text" class="form-control" id="pref-search" name="cliente" value="<?php echo $cliente ?>" placeholder="Buscar por cliente">
            </div><!-- form group [search] -->
            <div class="mb-5 mr-2">
              <span>Almacenes</span>
              <select name="almacen" class="form-control" >
                <option value="">Todos los almacenes</option>
                <?php
                  while($row = $resultal->fetch_array()){
                    if($almacen == $row['a_id']) $sele = 'selected'; else $sele = "";
                      echo '<option value="'.$row['a_id'].'" '.$sele.'>'.$row['a_nmb'].'</option>';
                }
                ?>
              </select>
            </div><!-- form group [search] -->

            <div class="mb-5 mr-2">
              <span>Fecha inicial</span>
              <input type="date" name="fini" class="form-control" value="<?php echo $fini ?>" />
            </div><!-- form group [search] -->
            <div class="mb-5 mr-2">
              <span>Fecha Final</span>
              <input type="date" name="ffin" class="form-control" value="<?php echo $ffin?>" />
            </div><!-- form group [search] -->
            <div class="mb-5 mr-1">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value="" <?php echo $sel; ?> >Todos</option>
                <option value="A" <?php echo $selt; ?> >Aplicadas</option>
                <option value="C" <?php echo $selc; ?> >Canceladas</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
            <button type="submit" class="btn btn-info">
              <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
            </button>
            <a href="?modulo=reportes&accion=comprasd"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
            </button></a>
            </div>
          </form>
        </div>
      </div>
    </div>

    </div>
  </div>
  <div class="main-card mb-3 card">
    <div class="card-body row">
    <?php
  echo '
    <div class="table-responsive">
      <table width="100%" class="table table-striped">
        <thead class="bg-light-blue bg-darken-2 text-white">
          <th>Folio</th>
          <th>Fecha</th>
          <th>Almacen</th>
          <th>Comprador</th>
          <th>Proveedor</th>
          <th>Subtotal</th>
          <th>Descuento</th>
          <th>Total Compra</th>
          <th>Estatus</th>
  </tr></thead><tbody>';

  $totalsubtotal = 0;
  $totaldescuento = 0;
  $totalfinal = 0;

  $date1 = new DateTime($fini);
  $date2 = new DateTime($ffin);
  $diff = $date1->diff($date2);
  for($a = 0;$a<=$diff->days;$a++){
    $fecha[$a] = date('Y-m-d', strtotime($fini.' + '.$a.' days'));
    $totalf[$fecha[$a]] = 0;
  }


  $numrems = 0;
  while($rowal = $this->model->resultrec->fetch_array()){
  $numrems++;
  if($rowal['r_estatus'] == "A"){ $color = "bg-success"; $nmb = "Finalizada"; }
  elseif($rowal['r_estatus'] == "C"){ $color = "bg-danger"; $nmb = "Cancelada"; }

  $subtotal = ($rowal['r_subtotal']+$rowal['r_iva']-$rowal['r_descuento']);

  echo '<tr>
          <th class="text-medium">'.$rowal['r_folio'].'</th>
          <td class="text-medium">'.fecha_formato($rowal['r_fechaapli'],true,true).'</td>
          <td class="text-medium">'.busca($rowal['r_almacen'],'almacenes','a_id','a_siglas').'</td>
          <td class="text-medium">'.$rowal['r_comprador'].'</td>
          <td class="text-medium">'.busca($rowal['r_proveedor'],'proveedores','p_id','p_alias').'</td>
          <td class="text-medium number-align">$ '.number_format($subtotal,2).'</td>
          <td class="text-medium number-align">$ '.number_format($rowal['r_descuento'],2).'</td>
          <td class="text-medium number-align">$ '.number_format($rowal['r_total'],2).'</td>
          <td class="'.$color.'">'.$nmb.'</td>
  </tr>';
  $totalsubtotal+=$subtotal;
  $totaldescuento+=$rowal['r_descuento'];
  $totalfinal+=$rowal['r_total'];

  $totalf[date('Y-m-d',strtotime($rowal['r_fechaapli']))]+=$totalfinal;
  }

  echo '<tfoot>
        <tr>
          <td colspan="5" style="text-align:right;">Totales individuales</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalsubtotal,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totaldescuento,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalfinal,2).'</td>
          <td>No. Compras '.$numrems.'</td>
        </tr></tfood></table>';

  $maximo = 0;
  $etiquetas = array();
  $datosVentas = array();
  for($a = 0;$a<=$diff->days;$a++){
    $fecha[$a] = date('Y-m-d', strtotime($fini.' + '.$a.' days'));
    if($totalf[$fecha[$a]] > $maximo) $maximo = $totalf[$fecha[$a]];
    echo '<input type="hidden" id="valuedate'.$a.'" value="'.$totalf[$fecha[$a]].'">';
    array_push($etiquetas,$fecha[$a]);
    array_push($datosVentas,$totalf[$fecha[$a]]);

  }

  echo '<input type="hidden" id="maximo" value="'.$maximo.'">';
  // Ahora las imprimimos como JSON para pasarlas a AJAX, pero las agrupamos
  $values = '<?php
  ';
  $values.= '$etiquetas = [';
  foreach($etiquetas as $etiq){
    $values.='"'.$etiq.'",';
  }
  $values = substr($values,0,-1);
  $values.='];
  ';
  $values.= '$datosVentas = [';
  foreach($datosVentas as $dv){
    $values.='"'.$dv.'",';
  }
  $values = substr($values,0,-1);

  $values.='];
  ';

  $values.='$respuesta = [
    "etiquetas" => $etiquetas,
    "datos" => $datosVentas,
  ];
  ';
  $values.='echo json_encode($respuesta);';
  $fh = fopen("detallev.php", 'w') or die("Se produjo un error al crear el archivo");
  $texto =$values;
  fwrite($fh, $texto) or die("No se pudo escribir en el archivo");
  fclose($fh);

   ?>
      <canvas id="DetalleVentas"></canvas>
      <script type="text/javascript" src="js/charts/gral-ventas.js"></script>


  <?php
}



function ventasd($fini,$ffin,$cliente,$estatus,$asesor){
  $selt = '';
  $sela = '';
  $seln = '';
  $selp = '';
  $selc = '';
  if($estatus == "A") $sela = "selected";
  if($estatus == "N") $seln = "selected";
  if($estatus == "P") $selp = "selected";
  if($estatus == "C") $selc = "selected";
  if($estatus == "V") $selv = "selected";
  if($estatus == "F") $self = "selected";
  if($estatus == "FE") $selfe = "selected";
  else $selt = 'selected';
  $botones = '';

  $atras = '<a href="?modulo=reportes&accion=index" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>
              Atrás
            </a>';
  
        $filtro = '
          <form class="" role="form" method="post" action="?modulo=reportes&accion=ventasd" id="filtro">
            <div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Vendedor</label>
              <select class="form-control" name="asesor" id="asesor" >';
          if(!$asesor) $selt = "selected"; else $selt = "";
          $filtro.='<option value="" '.$selt.'>Todos</option>';
          $sqlv= 'SELECT DISTINCT(u_id),u_nmb,u_apellidos FROM usuarios INNER JOIN remisiones ON r_encargado = u_id
                  WHERE r_estatus != "N" ORDER BY u_nmb';
          $resultv = setq($sqlv);
          while($rowv = $resultv->fetch_array()){
            if($asesor == $rowv['u_id']) $sqlt = 'selected'; else $selt ="";
            $filtro.='<option value="'.$rowv['u_id'].'" '.$selt.'>'.$rowv['u_nmb'].' '.$rowv['u_apellidos'].'</option>';
          }
          $filtro.='</select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value="" '.$selt.'>Todos</option>
                <option value="V" '.$selv.'>Vendidos</option>
                <option value="A" '.$sela.'>Credito</option>
                <option value="F" '.$self.'>Liquidada</option>
                <option value="FE" '.$selfe.'>Enviada</option>
                <option value="P" '.$selp.'>Por aprobar en ingreso</option>
                <option value="C" '.$selc.'>Cancelados</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=reportes&accion=ventasd"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';
  
    $botones = ' <a target="_blank" href="formats/pdfventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
              <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </a> 
            <a target="_blank" href="formats/pdfventasddet.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
            <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF Detallado</button>
          </a> 
            <a href="formats/xlsxventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
              <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
            </a>
            <a href="formats/xlsventasddet.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
              <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel Detallado</button>
            </a>';

           
  
  echo '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("DETALLE DE VENTAS",$atras ,$filtro, '', $botones);

     ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '
    <div class="table table-responsive text-medium">
      <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
          <th>Folio</th>
          <th>Fecha</th>
          <th>Vendedor</th>
          <th>Cliente</th>
          <th>Telefono</th>
          <th>Subtotal</th>
          <th>Descuento</th>
          <th>Total Remision</th>
          <th>Envio</th>
          <th>Estatus</th>
  </tr></thead><tbody>';
 /*
    echo '

    <script>
      $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          processing: true,
          serverside: true,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatablerepventasd.php",
            type: "POST",
            datatype: "json",
          },
          pageLength: "1000",
          responsivePriority: 1,

      });
    </script>

    ';
*/

$totalsubtotal = 0;
$totaldescuento = 0;
$totalfinal = 0;
$totalenvios = 0;

$numrems = 0;
while($rowal = $this->model->resultrem->fetch_array()){
$numrems++;
if($rowal['r_estatus'] == "A"){ $color = "bg-primary"; $nmb = "Credito"; }
elseif($rowal['r_estatus'] == "F"){ $color = "bg-success"; $nmb = "Liquidada"; }
elseif($rowal['r_estatus'] == "FE"){ $color = "bg-success"; $nmb = "Enviada"; }
elseif($rowal['r_estatus'] == "P"){ $color = "bg-warning"; $nmb = "Proceso"; }
elseif($rowal['r_estatus'] == "C"){ $color = "bg-danger"; $nmb = "Cancelada"; }

$subtotal = ($rowal['r_total']-$rowal['r_iva']+$rowal['r_mdescuento']-$rowal['r_precioenvio']);
$totalrem = ($rowal['r_total']-$rowal['r_iva']-$rowal['r_precioenvio']);
$nmbcliente = busca($rowal['r_cliente'],'crm_clientes','c_id','c_nmb').' '.busca($rowal['r_cliente'],'crm_clientes','c_id','c_apellidos');

echo '<tr>
        <th class="text-medium">'.$rowal['r_folio'].'</th>
        <td class="text-medium">'.fecha_formato($rowal['r_faplica'],true,true).'</td>
        <td class="text-medium">'.busca($rowal['r_encargado'],'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)').'</td>
        <td class="text-medium">'.$nmbcliente.'</td>
        <td class="text-medium">'.busca($rowal['r_cliente'],'crm_clientes','c_id','c_telefono2').'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($subtotal,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($rowal['r_mdescuento'],2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalrem,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($rowal['r_precioenvio'],2).'</td>
        <td class="'.$color.'">'.$nmb.'</td>
</tr>';
$totalsubtotal+=$subtotal;
$totaldescuento+=$rowal['r_mdescuento'];
$totalenvios+=$rowal['r_precioenvio'];
$totalfinal+=$totalrem;

}
if($totalsubtotal > 0) {
  $pordesc = ($totaldescuento*100)/$totalsubtotal;
  $porfinal = ($totalfinal*100)/$totalsubtotal;
} else {
  $pordesc = 0;
  $porfinal = 0;
}
$sqlnif = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON rc_remision = r_id INNER JOIN articulos ON rc_articulo = a_id WHERE r_estatus IN ("A","F","FE")
AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") AND DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
if($asesor) $sqlnif.=' AND r_encargado = "'.$asesor.'"';
$sqlnif.= ' GROUP BY r_id';
$resultnif = setq($sqlnif);
$infvf = $resultnif -> num_rows;  

$sqlni = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON rc_remision = r_id INNER JOIN articulos ON rc_articulo = a_id WHERE r_estatus IN ("A","F","FE")
AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") AND DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
if($asesor) $sqlni.=' AND r_encargado = "'.$asesor.'" ';
$resultni = setq($sqlni);
list($infv) = $resultni -> fetch_array();  

echo '<tfoot>
      <tr>
        <td colspan="5" style="text-align:right;">Totales monto</td>
        <td style="text-align:right; font-size:13px;">$'.number_format($totalsubtotal,2).'</td>
        <td style="text-align:right; font-size:13px;">$'.number_format($totaldescuento,2).'</td>
        <td style="text-align:right; font-size:13px;">$'.number_format($totalfinal,2).'</td>
        <td style="text-align:right; font-size:13px;">$'.number_format($totalenvios,2).'</td>
        <td>No. Ventas '.$numrems.'</td>
      </tr>
      <tr>
        <td colspan="5" style="text-align:right;">Totales porcentajes</td>
        <td style="text-align:right; font-size:13px;">100%</td>
        <td style="text-align:right; font-size:13px;">'.number_format($pordesc, 2).'%</td>
        <td style="text-align:right; font-size:13px;">'.number_format($porfinal, 2).'%</td>
        <td></td>
        <td>No. Ventas con inflables '.$infvf.'</td>
      </tr>
      <tr>
        <td colspan="9" style="text-align:right;"></td>
        <td>Inflables vendidos '.$infv.'</td>
      </tr>
      </tfood></table>';
}

function ventasxprod($fini,$ffin,$cliente,$estatus,$asesor, $categoria){
  $selt = '';
  $sela = '';
  $seln = '';
  $selp = '';
  $selc = '';
  if($estatus == "A") $sela = "selected";
  if($estatus == "N") $seln = "selected";
  if($estatus == "P") $selp = "selected";
  if($estatus == "C") $selc = "selected";
  if($estatus == "V") $selv = "selected";
  else $selt = 'selected';

        $atras = '<a href="?modulo=reportes&accion=index">
        <button type="button" class="btn btn-sm btn-warning mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
      </a>';

        $filtro = '
          <form class="" role="form" method="post" action="?modulo=reportes&accion=ventasxprod" id="filtro">
            <div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Vendedor</label>
              <select class="form-control" name="asesor" id="asesor" >';
          if(!$asesor) $selt = "selected"; else $selt = "";
          $filtro.='<option value="" '.$selt.'>Todos</option>';
          $sqlv= 'SELECT DISTINCT(u_id),u_nmb,u_apellidos FROM usuarios INNER JOIN remisiones ON r_encargado = u_id
                  WHERE r_estatus != "N" ORDER BY u_nmb';
          $resultv = setq($sqlv);
          while($rowv = $resultv->fetch_array()){
            if($asesor == $rowv['u_id']) $selt = 'selected'; else $selt ="";
            $filtro.='<option value="'.$rowv['u_id'].'" '.$selt.'>'.$rowv['u_nmb'].' '.$rowv['u_apellidos'].'</option>';
          }
          $filtro.='</select>
            </div>
            <div class="mb-5">
              <label for="tipom">Categoría</label>
              <select class="form-control" name="categoria" id="categoria">';
              if(!$categoria) $selc = "selected"; else if($categoria == "T") $selt = "selected"; else {$selc = ''; $selt = '';}
              $filtro.='<option value="" '.$selc.'>Todas</option>';
    
              $numcat = busca('1', 'categorias', 'cat_inflable', 'COUNT(*)');
              if($numcat > 1)
              $filtro.='<option value="T" '.$selt.'>TODOS LOS INFLABLES</option>';
              
              $sqlv= 'SELECT * FROM categorias';
              $resultv = setq($sqlv);
    
              while($rowv = $resultv->fetch_array()){
                if($categoria == $rowv['cat_id']) $selc = 'selected'; else $selc ="";
                $filtro.='<option value="'.$rowv['cat_id'].'" '.$selc.'>'.$rowv['cat_nmb'].'</option>';
              }
              $filtro.='</select>
            </div>
            <!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=reportes&accion=ventasxprod"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';


          
            $botones = '<a target="_blank" href="formats/pdfventasxprod.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'&categoria='.$categoria.'">
              <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </a>
            <a href="formats/xlsxventasxprod.php?fini='.$fini.'&ffin='.$ffin.'&asesor='.$asesor.'&estatus='.$estatus.'&categoria='.$categoria.'">
              <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
            </a>';
          
            
          
  echo '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("VENTAS POR PRODUCTO","" ,$filtro, $atras, $botones);

     ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '
    <div class="table table-responsive">
      <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
          <tr>
            <th>Modelo</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Importe</th>
            <th>% del total</th>
          </tr></thead><tbody>';
    $sql = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, r_encargado, a_nmb FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision 
              INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'"  AND r_estatus IN ("A","F","FE") AND rc_ligado IS NULL';
    if($categoria) {
      if($categoria == "T") $sql .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
      else $sql.=' AND a_categoria = "'.$categoria.'"';
    }            
    if($asesor) $sql.=' AND r_encargado = "'.$asesor.'"';
    $sql.=' GROUP BY rc_articulo, rc_modelo, r_encargado ORDER BY a_nmb ASC';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $cant[$row['articulo']][$row['rc_modelo']] = 0;
      $import[$row['articulo']][$row['rc_modelo']] = 0;
      $vcat[$row['r_encargado']][$row['a_categoria']] = 0;
    }
  //echo $this->model->totalventa.'<<<<br><br><br>';

$numrems = 0;

while($row = $this->model->resultremd->fetch_array()){
  $aplicadesc = busca($row['rd_articulo'], 'articulos', 'a_id', 'a_descuento');
  if($row['r_descuento'] == 0)
    $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*(($row['rd_precio']));
  else{
    if($aplicadesc == "1") $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*($row['rd_precio']-($row['rd_precio']*($row['r_descuento']/100)));
    else $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*(($row['rd_precio']));
  }
}
$numrems++;
$result = setq($sql);
while($row = $result -> fetch_array()){
  $cant[$row['articulo']][$row['rc_modelo']]+= $row['cantidad'];
  $vcat[$row['r_encargado']][$row['a_categoria']]+= $row['cantidad'];
}


$sql = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, r_encargado, a_nmb FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision 
          INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'"  AND r_estatus IN ("A","F","FE") AND rc_ligado IS NULL';
if($categoria) {
  if($categoria == "T") $sql .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
  else $sql.=' AND a_categoria = "'.$categoria.'"';
}            
if($asesor) $sql.=' AND r_encargado = "'.$asesor.'"';
$sql.=' GROUP BY rc_articulo, rc_modelo ORDER BY a_nmb ASC';
$result = setq($sql);
$sumacant = 0;
$sumatot = 0;
$sumaporc = 0;

while($row = $result->fetch_array()){
  
  $porc = ($import[$row['articulo']][$row['rc_modelo']]/$this->model->totalventa)*100;
  $nmb = $row['a_nmb'];
  if($row['rc_modelo']){
    $nmb.= ' '.busca($row['rc_modelo'], 'articulos_variantes', 'av_articulo = "'.$row['articulo'].'" AND av_modelo' ,'av_nmb');
  }
  echo '<tr>
            <th>'.$row['rc_modelo'].'</th>
            <th>'.$nmb.'</th>
            <th style="text-align:right;">'.number_format($cant[$row['articulo']][$row['rc_modelo']],0).'</th>
            <th style="text-align:right;">$ '.number_format($import[$row['articulo']][$row['rc_modelo']],2).'</th>
            <th style="text-align:right;">'.number_format($porc,2).'%</th>
          </tr>';
  $sumacant+=$cant[$row['articulo']][$row['rc_modelo']];
  $sumatot+=$import[$row['articulo']][$row['rc_modelo']];
  $sumaporc+=$porc;
}
echo '</tbody><tfoot><tr>
            <th colspan="2"></th>
            <th style="text-align:right;">'.number_format($sumacant,0).'</th>
            <th style="text-align:right;">$'.number_format($sumatot,2).'</th>
            <th style="text-align:right;">'.number_format($sumaporc,2).'%</th>
          </tr></tfoot></table>';

  echo '<div class="row"><div class="col-md-5 table-responsive">';

  echo '<table class="table table-striped">
        <thead>
          <tr><th colspan="2"><b>Paquetes vendidos</b></th></tr>
          <tr>
            <th>Paquete</th>
            <th>Cantidad</th>
            <th>Importe</th>
            <th>% del total</th>
          </tr>
        </thead>';

  $result = setq($sql);
  $sumpack = 0;
  $summon = 0;
  $sumport = 0;
  $sumcat = array();
  $sql = 'SELECT rd_nmbarticulo, SUM(rd_cantidad) cantidad, SUM(rd_cantidad*rd_precio) precio FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision INNER JOIN articulos ON a_id = rd_articulo
   WHERE a_tipoprod = "M" AND DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'"  AND r_estatus IN ("A","F","FE")';
  if($asesor) $sql.=' AND r_encargado = "'.$asesor.'"';
  $sql .= ' GROUP BY rd_articulo, rd_modelo';
  $result = setq($sql);
  while($row = $result->fetch_array()){
      $porc = ($row['precio']/$this->model->totalventa)*100;
      echo '<tr>
        <th>'.$row['rd_nmbarticulo'].'</th>
        <th style="text-align:right;">'.number_format($row['cantidad'], 0).'</th>
        <th style="text-align:right;">$ '.number_format($row['precio'], 2).'</th>
        <th style="text-align:right;">'.number_format($porc,2).'%</th>
      </tr>';
      $sumpack += $row['cantidad'];
      $summon += $row['precio'];
      $sumport += $porc;
  }
  echo '<tr>
    <td>Totales</td>
    <td style="text-align:right;">'.number_format($sumpack,0).'</td>
    <td style="text-align:right;">$'.number_format($summon,2).'</td>
    <td style="text-align:right;">'.number_format($sumport,2).'%</td>
  </tr>';
  echo '</table></div>';

  echo '<div class="col-md-5 offset-md-1">';

  echo '<table class="table table-striped">
        <thead>
          <tr><th colspan="2"><b>Ventas por categoria</b></th></tr>
          <tr>
            <th>Asesor</th>';
          $sqlcat = 'SELECT * FROM categorias';
          if($categoria) {
            if($categoria == "T") $sqlcat .= ' WHERE cat_inflable = "1"';
            else $sqlcat .= ' WHERE cat_id = "'.$categoria.'"';
          }
          $resultcat = setq($sqlcat);
          while($rowcat = $resultcat -> fetch_array()){
            echo '<th>'.$rowcat['cat_nmb'].'</th>';
          }
          echo '</tr>
        </thead>';
        echo '<tbody>';
        $sqlusu = 'SELECT DISTINCT(r_encargado), u_id, u_nmb, u_apellidos FROM remisiones INNER JOIN usuarios ON r_encargado = u_id WHERE DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'"  AND r_estatus IN ("A","F","FE")';
        if($asesor) $sqlusu.=' AND r_encargado = "'.$asesor.'"';
        $resultusu = setq($sqlusu);
        while($rowusu = $resultusu -> fetch_array()){
          echo '<tr>
            <td>'.$rowusu['u_nmb'].' '.$rowusu['u_apellidos'].'</td>';
            $resultcat = setq($sqlcat);
            while($rowcat = $resultcat -> fetch_array()){
              if(!$vcat[$rowusu['r_encargado']][$rowcat['cat_id']]) $vcat[$rowusu['r_encargado']][$rowcat['cat_id']] = 0;
              echo '<td style="text-align:right;">'.$vcat[$rowusu['r_encargado']][$rowcat['cat_id']].'</td>';
              if(!$sumcat[$rowcat['cat_id']]) $sumcat[$rowcat['cat_id']] = 0;
              $sumcat[$rowcat['cat_id']]+= $vcat[$rowusu['r_encargado']][$rowcat['cat_id']];
            }
          echo '</tr>';
        }
  
  echo '<tr><td>Totales</td>';
  $resultcat = setq($sqlcat);
  while($rowcat = $resultcat -> fetch_array()){
    echo '<td style="text-align:right;">'.number_format($sumcat[$rowcat['cat_id']],0).'</td>';
  }
  
  echo '</tbody></tr>';
  echo '</table></div>';


}

function envios($fini,$ffin,$paqueteria,$asesor){
  $atras = '<a href="?modulo=reportes&accion=index">
              <button type="button" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
            </a>';
  $filtro = '
    <form class="" role="form" method="post" action="?modulo=reportes&accion=envios" id="filtro">
      <div class="mb-5">
        <label for="tipom">Desde</label>
        <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
      </div>
      <div class="mb-5">
        <label for="tipom">Hasta</label>
        <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
      </div>
      <div class="mb-5">
        <label for="tipom">Vendedor</label>
        <select class="form-control" name="asesor" id="asesor" >';
    if(!$asesor) $selt = "selected"; else $selt = "";
    $filtro.='<option value="" '.$selt.'>Todos</option>';
    $sqlv= 'SELECT DISTINCT(u_id),u_nmb,u_apellidos FROM usuarios INNER JOIN remisiones ON r_encargado = u_id
            WHERE r_estatus != "N" ORDER BY u_nmb';
    $resultv = setq($sqlv);
    while($rowv = $resultv->fetch_array()){
      if($asesor == $rowv['u_id']) $selu = 'selected'; else $selu ="";
      $filtro.='<option value="'.$rowv['u_id'].'" '.$selu.'>'.$rowv['u_nmb'].' '.$rowv['u_apellidos'].'</option>';
    }
    $selt = '';
    $selo = '';
    $seld = '';
    $selp = '';
    $selc = '';
    if($paqueteria == "O") $selo = 'selected';
    elseif($paqueteria == "D") $seld = 'selected';
    elseif($paqueteria == "C") $selc = 'selected';
    elseif($paqueteria == "P") $selp = 'selected';
    else $selt = 'selected';

    $filtro.='</select>
      </div><!-- form group [search] -->

      <div class="mb-5">
        <label for="tipom">Tipo de envio</label>
        <select class="form-control" name="paqueteria" id="paqueteria" >';

    $filtro.='<option value="" '.$selt.'>Todos</option>';
    $filtro.='<option value="D" '.$seld.'>Domicilio</option>';
    $filtro.='<option value="C" '.$selc.'>Recoge en tienda</option>';
    $filtro.='<option value="P" '.$selp.'>Por definir</option>';
    $filtro.='<option value="O" '.$selo.'>Ocurre</option>';

    $filtro.='</select>
      </div><!-- form group [search] -->

      <div class="mb-5">
        <label for="">Acciones</label> <br>
        <button type="button" onclick="mandar(0);" class="btn btn-info">
          <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
        </button>
        <a href="?modulo=reportes&accion=envios"><button type="button" class="btn btn-warning">
          <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
        </button></a>
      </div>
    </form>';
  echo '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("ENVIOS","" ,$filtro, $atras);

     ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '
    <div class="table table-responsive">
      <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
          <tr>
            <th>Nota</th>
            <th>Producto</th>
            <th>Vendedor</th>
            <th>Tipo de envio</th>
            <th>Paquetería</th>
            <th>Guia</th>
            <th>CP</th>
            <th>Estado</th>
          </tr></thead><tbody>';
  $tipoenvio = array("D"=>"Domicilio","C"=>"Recoge en tienda","P"=>"Por definir","O"=>"Ocurre");
  $tipoenviod = array("D","C","P","O");
  foreach($tipoenviod as $tipoen){
    $tipo[$tipoen] == 0;
  }
  $sqle = 'SELECT e_id FROM estado';
  $resulte = setq($sqle);
  while($rowe = $resulte->fetch_array()){
    $numes[$rowe['e_id']] = 0;
  }
  $extra = 0;
  $sqlp = 'SELECT * FROM remisiones INNER JOIN remisionesc ON r_id = rc_remision
           WHERE DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND rc_ligado IS NULL AND r_estatus IN ("A","F","FE")';
  if(!empty($paqueteria)) $sqlp .= ' AND rc_tipoenvio = "'.$paqueteria.'"';
  if(!empty($asesor)) $sqlp .= ' AND r_encargado = "'.$asesor.'"';
  $resultp = setq($sqlp);
  while($row = $resultp->fetch_array()){
    $tipo[$row['rc_tipoenvio']]++;
    if($row['rc_tipoenvio'] == "O" || $row['rc_tipoenvio'] == "D"){
      $direccion = busca($row['r_id'], 'crm_cotizaciones', 'cc_remision', 'cc_direnvio');
      $estado = busca($direccion,'crm_direcciones','cd_id','cd_estado');
      $paq = busca($row['rc_paqueteria'],'paqueterias','p_id','p_nmb');
      $guia = busca($row['rc_guia'],'guias_articulos','ga_id','ga_nmb');
      $cp = busca($direccion,'crm_direcciones','cd_id','cd_cp');
      $pais = busca($direccion,'crm_direcciones','cd_id','cd_pais');
      if($pais != "146"){ 
        $nmbestado = busca($pais,'paises','p_id','p_nmb');
        $extra++;
      } else {
        $nmbestado = busca($estado,'estado','e_id','e_nmb');
        $numes[$estado]++;
      }
      
      
    }
    else{
      $paq = "-";
      $guia = "-";
      $cp = "NA";
      $estado = "NA";
      $nmbestado = "-";
    }
    $nmb = busca($row['rc_articulo'],'articulos','a_id','a_nmb');
    if(!empty($row['rc_articulo'])) $nmb.= ' '.busca($row['rc_articulo'],'articulos_variantes','av_modelo = "'.$row['rc_modelo'].'" AND av_articulo ','av_nmb');
    $nota = $row['r_folio'];
    echo ' <tr>
            <th>'.$nota.'</th>
            <th>'.$nmb.'</th>
            <th>'.busca($row['r_encargado'],'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)').'</th>
            <th>'.$tipoenvio[$row['rc_tipoenvio']].'</th>
            <th>'.$paq.'</th>
            <th>'.$guia.'</th>
            <th>'.$cp.'</th>
            <th>'.$nmbestado.'</th>
          </tr>';
  }
  echo '</tbody></table>';

  echo '<div class="row"><div class="col-md-4 offset-md-1 table-responsive">';

  echo '<table class="table table-striped">
        <thead>
          <tr><th colspan="2"><b>Envios por estado</b></th></tr>
          <tr>
            <th>Estado</th>
            <th>Envios</th>
          </tr>
        </thead>';
  $sumaestado = 0;
  $sqle = 'SELECT e_id,e_nmb FROM estado';
  $resulte = setq($sqle);
  while($rowe = $resulte->fetch_array()){
    echo '<tr>
            <td>'.$rowe['e_nmb'].'</td>
            <td style="text-align:right;">'.number_format($numes[$rowe['e_id']],0).'</td>
          </tr>';
    $sumaestado+=$numes[$rowe['e_id']];
  }
  echo '<tr>
  <td>EXTRANJERO</td>
  <td style="text-align:right;">'.$extra.'</td>
  </tr>';
  $sumaestado+=$extra;
  echo '<tr><td>Totales</td><td style="text-align:right;">'.number_format($sumaestado,0).'</td></tr>';
  echo '</table></div>';

  echo '<div class="col-md-4 offset-md-2 table-responsive">';

  echo '<table class="table table-striped">
        <thead>
          <tr><th colspan="2"><b>Resumen de tipos de envio</b></th></tr>
          <tr>
            <th>Tipo de envio</th>
            <th>Envios</th>
          </tr>
        </thead>';
  $sumaenvio = 0;
  foreach($tipoenviod as $tipoen){
    echo '<tr>
            <td>'.$tipoenvio[$tipoen].'</td>
            <td style="text-align:right;">'.number_format($tipo[$tipoen],0).'</td>
          </tr>';
    $sumaenvio+=$tipo[$tipoen];
  }
  echo '<tr><td>Totales</td><td style="text-align:right;">'.number_format($sumaenvio,0).'</td></tr>';
  echo '</table></div>';

  echo '</div>';
}

function financieros($fini,$ffin,$linean){
  $arrmes = array("01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio","07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre");
  $arrmesc = array("01"=>"Ene","02"=>"Feb","03"=>"Mar","04"=>"Abr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Ago","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dic");
   //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions">';
    if($estatus == "A") $chest = "checked"; else $chest = "";

    //Sección: E2 Encabezado - Filtros
    ?>

    <div class="col-md-12">
      <a href="?modulo=reportes&accion=index" class="btn btn-warning"><i class="fa fa-arrow-left"></i>
        Atrás
      </a>
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fas fa-search"></i> Filtrar
      </button>
    </div>
    <div id="filter-panel" class="col-md-12 collapse filter-panel p-1">
          <div class="panel panel-default">
            <div class="panel-body">
              <form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=financieros">
                 <div class="mb-5 mr-1">
                  <label for="tipom">Desde</label>
                  <input type="month" name="fini" id="fini" class="form-control" value="<?php echo $fini ?>" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Hasta</label>
                  <input type="month" name="ffin" id="ffin" class="form-control" value="<?php echo $ffin?>" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Linea de negocio</label>
                  <select class="form-control" name="linean" id="linean" >
                    <option value="" <?php echo $selt; ?> >Todos</option>
                  <?php
                    $sqlu = 'SELECT ln_id,ln_nmb FROM lineas_negocio WHERE ln_estatus = "A"';
                    $resultu = setq($sqlu);
                    while($rowu = $resultu->fetch_array()){
                      if($rowu['ln_id'] == $linean) $seln = 'selected'; else $seln = "";
                  ?>
                    <option value="<?php echo $rowu['ln_id'] ?>" <?php echo $seln; ?> ><?php echo $rowu['ln_nmb'] ?></option>
                  <?php }  ?>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                    <button type="submit" id="filtrar" class="btn btn-primary">
                      <i class="fas fa-filter"></i>Enviar
                    </button>
                <a href="?modulo=reportes&accion=financieros"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>
            </div>
          </div>
        </div>

      </div></div>
      <h2 class=""> Estado de resultados <?php echo $arrmes[date('m',strtotime($fini))].' '.date('Y',strtotime($fini)) ?> a  <?php echo $arrmes[date('m',strtotime($ffin))].' '.date('Y',strtotime($ffin)) ?></h2>
<?php
  $anios = date('Y',strtotime($ffin))-date('Y',strtotime($fini));
  $cols = 0;
  for($a=0;$a<=$anios;$a++){
    $mini = date('n',strtotime($fini));
    $mfin = date('n',strtotime($ffin));
    $anio = date('Y',strtotime($fini.'+ '.$a.' years'));

    for($m=$mini;$m<=12;$m++){
      $cols++;
      $mes = str_pad($m,2,"0",STR_PAD_LEFT);
      $title[$cols] = $arrmesc[$mes].' '.$anio;

      $fecha = $anio.' '.$mes;
      $an[$cols] = $anio;
      $ms[$cols] = $mes;
      $sumames[$cols] = 0;

      if($fecha == $ffin){
        break;
      }
    }
  }
  $multiva = 1+(busca(1,'configuracionesp','c_id','c_iva')/100);

  //Tabla de totales de Venta
  echo '<div class="tabler-responsive">
        <table class="table table-striped text-medium">
          <thead class="bg-light-blue bg-darken-2 text-white">
            <tr>
              <th colspan="'.($cols+2).'"><h3>Ventas</h3></th>
            </tr><tr>
            <th>Ventas</th>';
  for($i=1;$i<=$cols;$i++){
    echo '<th>'.$title[$i].'</th>';
  }
  echo '<th>Sumatorias</th></tr></thead><tbody>';

  $sqlln = 'SELECT ln_id,ln_nmb FROM lineas_negocio WHERE ln_estatus = "A"';
  $resultln = setq($sqlln);
  while($rowl = $resultln->fetch_array()){
    $sumalinea = 0;
    echo '<tr><td class="bg-light-blue bg-darken-2 text-white" width="13%" style="word-break: break-all;">'.$rowl['ln_nmb'].'</td>';
    for($i=1;$i<=$cols;$i++){
      $sqlv = 'SELECT SUM((rd_precio*('.$multiva.'*rd_iva))*rd_cantidad) FROM remisionesd WHERE rd_remision IN
              (SELECT r_id FROM remisiones WHERE r_estatus = "A" AND YEAR(r_faplica) = "'.$an[$i].'"
              AND MONTH(r_faplica) = "'.$ms[$i].'") AND rd_linean = "'.$rowl['ln_id'].'"';
      $resultv = setq($sqlv);
      list($ventas[$i]) = $resultv->fetch_array();
      if(!$ventas[$i]) $ventas[$i] = 0;
      $sumalinea+=$ventas[$i];
      $sumames[$i]+=$ventas[$i];;

      echo '<td class="number-align">'.number_format($ventas[$i],2).'</td>';
    }
    echo '<td class="number-align">'.number_format($sumalinea,2).'</td>';
    echo '</tr>';
  }
  echo '</tbody><tfoot><tr class="bg-teal bg-lighten-1 text-bold-900"><th>Totales</th>';
  $sumatotal = 0;
  for($i=1;$i<=$cols;$i++){
    echo '<td class="number-align">'.number_format($sumames[$i],2).'</td>';
    $sumatotal+=$sumames[$i];
  }
  echo '<td class="number-align">'.number_format($sumatotal,2).'</td>';
  echo '</tr>';

  echo '</table></div>';
  //Fin de ventas1

  //Tabla de totales de Costos operacionales
  echo '<div class="tabler-responsive">
        <table class="table table-striped text-medium">
          <thead class="bg-light-blue bg-darken-2 text-white">
            <tr>
              <th colspan="'.($cols+2).'"><h3>Costos operacionales</h3></th>
            </tr><tr>
            <th>Concepto</th>';
  for($i=1;$i<=$cols;$i++){
    echo '<th>'.$title[$i].'</th>';
  }
  echo '<th>Sumatorias</th></tr></thead><tbody>';

  $sqlln = 'SELECT DISTINCT(ln_id),ln_nmb FROM lineas_negocio
            WHERE ln_estatus = "A"';
  $resultln = setq($sqlln);
  while($rowl = $resultln->fetch_array()){
    $sumacolinea = 0;
    echo '<tr><td class="bg-light-blue bg-darken-2 text-white" width="13%" style="word-break: break-all;">COMPRAS '.$rowl['ln_nmb'].'</td>';
    for($i=1;$i<=$cols;$i++){
      $sqlc = 'SELECT SUM((od_precio*('.$multiva.'*od_iva))*od_cantidad) FROM ordenescd WHERE od_ordenc IN
              (SELECT o_id FROM ordenesc WHERE o_estatus = "A" YEAR(o_fapli) = "'.$an[$i].'"
              AND MONTH(o_fapli) = "'.$ms[$i].'") AND od_linean = "'.$rowl['ln_id'].'"';
      $resultc = setq($sqlc);
      list($compras[$i]) = $resultc->fetch_array();
      if(!$compras[$i]) $compras[$i] = 0;
      $sumacolinea+=$compras[$i];
      $sumacomes[$i]+=$compras[$i];

      $sqlc = 'SELECT SUM((rd_costo*('.$multiva.'*rd_iva))*rd_cantidad) FROM recepcionesd WHERE rd_recepcion IN
              (SELECT r_id FROM recepciones WHERE r_estatus = "A" YEAR(r_fechaapli) = "'.$an[$i].'"
              AND MONTH(r_fechaapli) = "'.$ms[$i].'" AND r_ordenc = "0") AND rd_ln = "'.$rowl['ln_id'].'"';
      $resultc = setq($sqlc);
      list($compras[$i]) = $resultc->fetch_array();
      if(!$compras[$i]) $compras[$i] = 0;

      $sumacolinea+=$compras[$i];
      $sumacomes[$i]+=$compras[$i];

      echo '<td class="number-align">'.number_format($compras[$i],2).'</td>';
    }
    echo '<td class="number-align">'.number_format($sumacolinea,2).'</td>';
    echo '</tr>';
  }


  //Gastos operacionales
  $sqlgo = 'SELECT * FROM gastos_categoria WHERE gc_estatus = "A" AND gc_financiero = "O"';
  $resultgo = setq($sqlgo);
  while($rowgo = $resultgo->fetch_array()){
    $sumacolinea = 0;
    echo '<tr><td class="bg-light-blue bg-darken-2 text-white" width="13%" style="word-break: break-all;">'.$rowgo['gc_nmb'].'</td>';
    if($rowgo['gc_tipo'] == "G" || $rowgo['gc_tipo'] == "V"){
      $tipocp = "G";
      $innerjoin = ' INNER JOIN gastos ON g_id = cp_idref ';
      $wher = ' AND g_categoria = "'.$rowgo['gc_id'].'" ';
    }
    else{
      $tipocp = "F";
      $innerjoin = ' INNER JOIN gastosf ON gf_id = cp_idref ';
      $wher = '  AND gf_categoria = "'.$rowgo['gc_id'].'" ';
    }
    for($i=1;$i<=$cols;$i++){
      $sqlgcco = 'SELECT SUM(cp_importe) FROM cxpagar '.$innerjoin.'
                  WHERE cp_estatus != "C" AND cp_tipo = "'.$tipocp.'" AND YEAR(cp_fini) = "'.$an[$i].'"
                  AND MONTH(cp_fini) = "'.$ms[$i].'" '.$wher;
      $resultcgo = setq($sqlgcco);
      list($gastoso[$i]) = $resultcgo->fetch_array();
      if(!$gastoso[$i]) $gastoso[$i] = 0;
      $sumacolinea+=$gastoso[$i];
      $sumacomes[$i]+=$gastoso[$i];;
      echo '<td class="number-align">'.number_format($gastoso[$i],2).'</td>';
    }
    echo '<td class="number-align">'.number_format($sumacolinea,2).'</td>';
    echo '</tr>';

  }

  echo '</tbody><tfoot><tr class="bg-teal bg-lighten-1 text-bold-900"><th>Suma Gastos operacionales</th>';
  $sumatotalgo = 0;
  for($i=1;$i<=$cols;$i++){
    echo '<td class="number-align">'.number_format($sumacomes[$i],2).'</td>';
    $sumatotalgo+=$sumacomes[$i];
  }
  echo '<td class="number-align">'.number_format($sumatotalgo,2).'</td>';
  echo '</tr>';

  echo '</table></div>';
  //Fin de Gastos operacionales



  //Gastos Administración
  echo '<div class="tabler-responsive">
        <table class="table table-striped text-medium">
          <thead class="bg-light-blue bg-darken-2 text-white">
            <tr>
              <th colspan="'.($cols+2).'"><h3>Gastos de Administración</h3></th>
            </tr><tr>
            <th>Concepto</th>';
  for($i=1;$i<=$cols;$i++){
    echo '<th>'.$title[$i].'</th>';
  }
  echo '<th>Sumatorias</th></tr></thead><tbody>';

  $sqlga = 'SELECT * FROM gastos_categoria WHERE gc_estatus = "A" AND gc_financiero = "A"';
  $resultga = setq($sqlga);
  $sumagalinea = 0;
  while($rowga = $resultga->fetch_array()){
    $sumagalinea = 0;
    echo '<tr><td class="bg-light-blue bg-darken-2 text-white" width="13%" style="word-break: break-all;">'.$rowga['gc_nmb'].'</td>';
    if($rowga['gc_tipo'] == "G" || $rowga['gc_tipo'] == "V"){
      $tipocp = "G";
      $innerjoin = ' INNER JOIN gastos ON g_id = cp_idref ';
      $wher = ' AND g_categoria = "'.$rowga['gc_id'].'" ';
    }
    else{
      $tipocp = "F";
      $innerjoin = ' INNER JOIN gastosf ON gf_id = cp_idref ';
      $wher = '  AND gf_categoria = "'.$rowga['gc_id'].'" ';
    }
    for($i=1;$i<=$cols;$i++){
      $sqlgcca = 'SELECT SUM(cp_importe) FROM cxpagar '.$innerjoin.'
                  WHERE cp_estatus != "C" AND cp_tipo = "'.$tipocp.'" AND YEAR(cp_fini) = "'.$an[$i].'"
                  AND MONTH(cp_fini) = "'.$ms[$i].'" '.$wher;
      $resultcga = setq($sqlgcca);
      list($gastosa[$i]) = $resultcga->fetch_array();
      if(!$gastosa[$i]) $gastosa[$i] = 0;
      $sumagalinea+=$gastosa[$i];
      $sumagames[$i]+=$gastosa[$i];;
      echo '<td class="number-align">'.number_format($gastosa[$i],2).'</td>';
    }
    echo '<td class="number-align">'.number_format($sumagalinea,2).'</td>';
    echo '</tr>';

  }

  echo '</tbody><tfoot><tr class="bg-teal bg-lighten-1 text-bold-900"><th>Suma Gastos Administrativos</th>';
  $sumatotalga = 0;
  for($i=1;$i<=$cols;$i++){
    echo '<td class="number-align">'.number_format($sumagames[$i],2).'</td>';
    $sumatotalga+=$sumagames[$i];
  }
  echo '<td class="number-align">'.number_format($sumatotalga,2).'</td>';
  echo '</tr>';

  echo '</table></div>';
  //Fin de Gastos Administración

  //Gastos Ventas
  echo '<div class="tabler-responsive">
        <table class="table table-striped text-medium">
          <thead class="bg-light-blue bg-darken-2 text-white">
            <tr>
              <th colspan="'.($cols+2).'"><h3>Gastos de Ventas</h3></th>
            </tr><tr>
            <th>Concepto</th>';
  for($i=1;$i<=$cols;$i++){
    echo '<th>'.$title[$i].'</th>';
  }
  echo '<th>Sumatorias</th></tr></thead><tbody>';

  $sqlgv = 'SELECT * FROM gastos_categoria WHERE gc_estatus = "A" AND gc_financiero = "V"';
  $resultgv = setq($sqlgv);
  $sumagvlinea = 0;
  while($rowgv = $resultgv->fetch_array()){
    $sumagvlinea = 0;
    echo '<tr><td class="bg-light-blue bg-darken-2 text-white" width="13%" style="word-break: break-all;">'.$rowgv['gc_nmb'].'</td>';
    if($rowgv['gc_tipo'] == "G" || $rowgv['gc_tipo'] == "V"){
      $tipocp = "G";
      $innerjoin = ' INNER JOIN gastos ON g_id = cp_idref ';
      $wher = ' AND g_categoria = "'.$rowgv['gc_id'].'" ';
    }
    else{
      $tipocp = "F";
      $innerjoin = ' INNER JOIN gastosf ON gf_id = cp_idref ';
      $wher = '  AND gf_categoria = "'.$rowgv['gc_id'].'" ';
    }
    for($i=1;$i<=$cols;$i++){
      $sqlgccv = 'SELECT SUM(cp_importe) FROM cxpagar '.$innerjoin.'
                  WHERE cp_estatus != "C" AND  cp_tipo = "'.$tipocp.'" AND YEAR(cp_fini) = "'.$an[$i].'"
                  AND MONTH(cp_fini) = "'.$ms[$i].'" '.$wher;
      $resultcgv = setq($sqlgccv);
      list($gastosv[$i]) = $resultcgv->fetch_array();
      if(!$gastosv[$i]) $gastosv[$i] = 0;
      $sumagvlinea+=$gastosv[$i];
      $sumagvmes[$i]+=$gastosv[$i];
      echo '<td class="number-align">'.number_format($gastosv[$i],2).'</td>';
    }
    echo '<td class="number-align">'.number_format($sumagvlinea,2).'</td>';
    echo '</tr>';
  }
  echo '<tr>
          <td class="bg-light-blue bg-darken-2 text-white" width="13%" style="word-break: break-all;">COMISIONES</td>';
  for($i=1;$i<=$cols;$i++){
    $sumaoplinea = 0;
    $sqlop = 'SELECT SUM(op_importe) FROM ordenesp
              WHERE YEAR(op_fechac) = "'.$an[$i].'" AND MONTH(op_fechac) = "'.$ms[$i].'"';
    $resultop = setq($sqlop);
    list($comisiones[$i]) = $resultop->fetch_array();
    if(!$comisiones[$i]) $comisiones[$i] = 0;
    $sumaoplinea+=$comisiones[$i];
    $sumagvmes[$i]+=$comisiones[$i];
    echo '<td class="number-align">'.number_format($comisiones[$i],2).'</td>';
  }
  echo '<td class="number-align">'.number_format($sumaoplinea[$i],2).'</td>';
  echo '</tr>';

  echo '</tbody><tfoot><tr class="bg-teal bg-lighten-1 text-bold-900"><th>Suma Gastos de ventas</th>';
  $sumatotalgv = 0;
  for($i=1;$i<=$cols;$i++){
    echo '<td class="number-align">'.number_format($sumagvmes[$i],2).'</td>';
    $sumatotalgv+=$sumagvmes[$i];
  }
  echo '<td class="number-align">'.number_format($sumatotalgv,2).'</td>';
  echo '</tr>';

  echo '</table></div>';
  //Fin de Gastos Administración

  //Gastos Ventas
  echo '<div class="tabler-responsive">
        <table class="table table-striped text-medium">
          <thead class="bg-light-blue bg-darken-2 text-white">
            <tr>
              <th colspan="'.($cols+2).'"><h3>Resultado del ejercicio</h3></th>
            </tr><tr>
            <th width="13%">Totales</th>';
  for($i=1;$i<=$cols;$i++){
    echo '<th>'.$title[$i].'</th>';
  }
  echo '<th>Sumatorias</th>';

  echo '</tr></thead><tr>
        <th class="bg-light-blue bg-darken-2 text-white">Utilidad bruta </th>';
  $sumaub = 0;
  for($i=1;$i<=$cols;$i++){
    $utilbruta[$i] = $sumames[$i]-$sumacomes[$i];
    $sumaub+=$utilbruta[$i];
    echo '<td class="number-align">'.number_format($utilbruta[$i],2).'</th>';
  }

  echo '<td class="number-align">'.number_format($sumaub,2).'</td></tr><tr>
        <th class="bg-light-blue bg-darken-2 text-white">Total de gastos de operación</th>';
  $sumaga = 0;
  for($i=1;$i<=$cols;$i++){
    $totgastov[$i] = $sumagvmes[$i]+$sumagames[$i];
    $sumaga+=$totgastov[$i];
    echo '<td class="number-align">'.number_format($totgastov[$i],2).'</th>';
  }

  echo '<td class="number-align">'.number_format($sumaga,2).'</td></tr><tr class="bg-teal bg-lighten-1 text-bold-900">
        <th class="bg-light-blue bg-darken-2 text-white">Utilidad por operación </th>';
  $sumauo = 0;
  for($i=1;$i<=$cols;$i++){
    $utilopera[$i] = $utilbruta[$i]-$totgastov[$i];
    $sumauo+=$utilopera[$i];
    echo '<td class="number-align">'.number_format($utilopera[$i],2).'</th>';
  }
  echo '<td class="number-align">'.number_format($sumauo,2).'</td></tr>';

  //Agrego gastos financieros
  $sqlgn = 'SELECT * FROM gastos_categoria WHERE gc_estatus = "A" AND gc_financiero = "N"';
  $resultgn = setq($sqlgn);
  $sumagnlinea = 0;
  while($rowgn = $resultgn->fetch_array()){
    $sumagnlinea = 0;
    echo '<tr><td class="bg-light-blue bg-darken-2 text-white" width="13%" style="word-break: break-all;">'.$rowgn['gc_nmb'].'</td>';
    if($rowgn['gc_tipo'] == "G" || $rowgn['gc_tipo'] == "V"){
      $tipocp = "G";
      $innerjoin = ' INNER JOIN gastos ON g_id = cp_idref ';
      $wher = ' AND g_categoria = "'.$rowgn['gc_id'].'" ';
    }
    else{
      $tipocp = "F";
      $innerjoin = ' INNER JOIN gastosf ON gf_id = cp_idref ';
      $wher = '  AND gf_categoria = "'.$rowgn['gc_id'].'" ';
    }

    for($i=1;$i<=$cols;$i++){
      $sqlgcgn = 'SELECT SUM(cp_importe) FROM cxpagar '.$innerjoin.'
                  WHERE cp_estatus != "C" AND cp_tipo = "'.$tipocp.'" AND YEAR(cp_fini) = "'.$an[$i].'"
                  AND MONTH(cp_fini) = "'.$ms[$i].'" '.$wher;
      $resultcgn = setq($sqlgcgn);
      list($gastosn[$i]) = $resultcgn->fetch_array();
      if(!$gastosn[$i]) $gastosn[$i] = 0;
      $sumagnlinea+=$gastosn[$i];
      $sumagnmes[$i]+=$gastosn[$i];
      echo '<td class="number-align">'.number_format($gastosn[$i],2).'</td>';
    }
    echo '<td class="number-align">'.number_format($sumagnlinea,2).'</td>';
    echo '</tr>';
  }

  echo '<tr class="bg-teal bg-lighten-1 text-bold-900">
        <th>Utilidad O pérdida antes de impuestos </th>';
  $sumaup = 0;
  for($i=1;$i<=$cols;$i++){
    $utilperdida[$i] = $utilopera[$i]-$sumagnmes[$i];
    $sumaup+=$utilperdida[$i];
    echo '<td class="number-align">'.number_format($utilperdida[$i],2).'</th>';
  }
  echo '<td class="number-align">'.number_format($sumaup,2).'</td></tr>';

  //Agrego gastos financieros
  $sqlgi = 'SELECT * FROM gastos_categoria WHERE gc_estatus = "A" AND gc_financiero = "I"';
  $resultgi = setq($sqlgi);
  $sumagilinea = 0;
  while($rowgi = $resultgi->fetch_array()){
    $sumagilinea = 0;
    echo '<tr><td class="bg-light-blue bg-darken-2 text-white" width="13%" style="word-break: break-all;">'.$rowgi['gc_nmb'].'</td>';
    if($rowgi['gc_tipo'] == "G" || $rowgi['gc_tipo'] == "V"){
      $tipocp = "G";
      $innerjoin = ' INNER JOIN gastos ON g_id = cp_idref ';
      $wher = ' AND g_categoria = "'.$rowgi['gc_id'].'" ';
    }
    else{
      $tipocp = "F";
      $innerjoin = ' INNER JOIN gastosf ON gf_id = cp_idref ';
      $wher = '  AND gf_categoria = "'.$rowgi['gc_id'].'" ';
    }

    for($i=1;$i<=$cols;$i++){
      $sqlgcgi = 'SELECT SUM(cp_importe) FROM cxpagar '.$innerjoin.'
                  WHERE cp_estatus != "C" AND  cp_tipo = "'.$tipocp.'" AND YEAR(cp_fini) = "'.$an[$i].'"
                  AND MONTH(cp_fini) = "'.$ms[$i].'" '.$wher;
      $resultcgi = setq($sqlgcgi);
      list($gastosi[$i]) = $resultcgi->fetch_array();
      if(!$gastosi[$i]) $gastosi[$i] = 0;
      $sumagilinea+=$gastosi[$i];
      $sumagimes[$i]+=$gastosi[$i];
      echo '<td class="number-align">'.number_format($gastosi[$i],2).'</td>';
    }
    echo '<td class="number-align">'.number_format($sumagilinea,2).'</td>';
    echo '</tr>';
  }

  echo '<tr class="bg-teal bg-lighten-1 text-bold-900">
        <th>Utilidad o pérdida neta del ejercicio</th>';
  $sumaupe = 0;
  for($i=1;$i<=$cols;$i++){
    $utilperdidaej[$i] = $utilperdida[$i]-$sumagimes[$i];
    $sumaupe+=$utilperdidaej[$i];
    echo '<td class="number-align">'.number_format($utilperdidaej[$i],2).'</th>';
  }
  echo '<td class="number-align">'.number_format($sumaupe,2).'</td></tr>';
  echo '</table></div>';
}

function tablerosxvendedor($fini,$ffin,$vendedor){

}

function dashtableros($fini,$ffin,$vendedor,$linean){

   //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions">';
    if($estatus == "A") $chest = "checked"; else $chest = "";

    //Sección: E2 Encabezado - Filtros
    ?>

    <section id="conversiontab" class="card p-1">
      <div class="">
        <a href="?modulo=reportes&accion=index" class="btn btn-warning"><i class="fas fa-arrow-left"></i>
          Atrás
        </a>
        <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-search"></i> Filtrar
        </button>
      </div>
      <div id="filter-panel" class="col-md-12 p-2 collapse filter-panel">
        <div class="panel panel-default">
          <div class="panel-body">
            <form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=dashtableros">
                <div class="mb-5">
                <label for="tipom">Desde</label>
                <input type="date" name="fini" id="fini" class="form-control" value="<?php echo $fini ?>" />
              </div>
              <div class="mb-5">
                <label for="tipom">Hasta</label>
                <input type="date" name="ffin" id="ffin" class="form-control" value="<?php echo $ffin?>" />
              </div>
              <div class="mb-5">
                <label for="tipom">Vendedor</label>
                <select class="form-control" name="vendedor" id="vendedor" >
                  <option value="" <?php echo $selt; ?> >Todos</option>
                <?php
                  $sqlu = 'SELECT u_id,u_nmb FROM usuarios WHERE u_estatus = "A"';
                  $resultu = setq($sqlu);
                  while($rowu = $resultu->fetch_array()){
                    if($rowu['u_id'] == $vendedor) $seln = 'selected'; else $seln = "";
                ?>
                  <option value="<?php echo $rowu['u_id'] ?>" <?php echo $seln; ?> ><?php echo $rowu['u_nmb'] ?></option>
                <?php }  ?>
                </select>
              </div><!-- form group [search] -->
              <div class="mb-5">
                <label for="">Acciones</label><br>
                <!-- <button type="submit" class="btn btn-info"> -->
                  <button type="button" id="filtrar" onclick="filtrartableros()" class="btn btn-primary">Filtrar</button>
                <!-- </button>-->
              <a href="?modulo=reportes&accion=dashtableros"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
              </div>
            </form>
          </div>
        </div>
      </div>
      <h2 class="card-title p-1"> Análisis de tableros  </h2>
        <div class="row">
          <div class="col-md-12">
            <h4 class="card-title p-1"> % Conversiones por tablero</h4>
          </div>
          <div class="col-md-8 offset-md-2">
            <div class="card-block chartjs" id="containconversiontablero">
              <canvas id="canvatblero" style=""></canvas>
            </div>
            <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
            </div>
          </div>
          <div id="divtablaconversiontablero" class="col-md-12"> <!-- -->
          </div>
          <!-- Fin de convrsión de tableros-->

          <!--<hr class="mt-3">-->
          <!-- Inicio de tableros por asesor-->
          <div class="col-md-12">
            <h4 class="card-title p-1"> Tableros por asesor</h4>
          </div>
          <div class="col-md-7">
            <div class="card-block chartjs" id="containtableroasesor">
              <canvas id="canvatabasesor" style=""></canvas>
            </div>
            <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
            </div>
          </div>
          <div id="divtabasesor2" class="col-md-5"> </div>

          <div id="divtablatableroasesor" class="col-md-12"> <!-- -->
          </div>
          <!-- fin de tableros por asesor-->

          <!-- <hr class="mt-3"> -->
          <div class="col-md-12">
            <h4 class="card-title p-1"> Ticket promedio </h4>
          </div>
            <div class="col-md-7">
              <div class="card-block chartjs" id="contenedorpadreticket">
                <canvas id="ticket" style="max-width: 100px"></canvas>
              </div>
              <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <strong>Presiona sobre cualquier sección de la grafica para ver más detalles.
              </div>
            </div>
            <div id="divgraf2ticket" class="col-md-5">

            </div>
            <div id="divtablaticket" class="col-md-12"> <!-- -->
            </div> <!-- -->

          <!-- <hr class="mt-3"> -->
          <div class="col-md-12">
            <h4 class="card-title p-1"> Utilidad por tablero </h4>
          </div>

            <div class="col-md-12">
              <div class="card-block chartjs" id="contenedorutiltab">
                <canvas id="utiltab"></canvas>
              </div>
              <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <strong>Presiona sobre cualquier sección de la grafica para ver más detalles.
              </div>
            </div>
            <div id="divutilidadtab" class="col-md-12">
            </div>
        </div>
    </section>


  <script>
    window.onload =  filtrartableros;
  </script>
<?php


}

function dashfacturas($fini,$ffin,$cliente,$estatus){

   //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions">';
    $selt = "";
    $selc = "";
    if($estatus == "T") $selt = "selected"; else $selc = "selected";

    //Sección: E2 Encabezado - Filtros
    ?>

    <div class="main-card mb-3 card p-2">
      <div class="card-body row">
        <div class="p-1 mb-1">
          <a href="?modulo=reportes&accion=index" class="btn btn-warning"><i class="fa fa-arrow-left"></i>
            Atrás
          </a>
          <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span><i class="fas fa-search"></i> Filtrar
          </button>
        </div>
        <div id="filter-panel" class="col-md-12 collapse filter-panel mb-2 p-2">
          <div class="panel panel-default">
            <div class="panel-body">
              <form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=dashfacturas">
                <div class="mb-5">
                  <label for="tipom">Cliente</label>
                  <input type="text" class="form-control" name="cliente" id="cliente" placeholder="Nombre del cliente que buscas" onfocus="this.select();" value="<?php echo $cliente ?>" />
                </div>
                <div class="mb-5">
                  <span>Fecha inicial</span>
                  <input type="date" name="fini" id="fini" class="form-control" value="<?php echo $fini ?>" />
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <span>Fecha Final</span>
                  <input type="date" name="ffin" id="ffin" class="form-control" value="<?php echo $ffin?>" />
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="tipom">Estatus</label>
                  <select class="form-control" name="estatus" id="estatus" >
                    <option value="T" <?php echo $selt; ?> >Timbrada</option>
                    <option value="C" <?php echo $selc; ?> >Cancelados</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones</label> <br>
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i>
                  </button>
                  <a href="?modulo=reportes&accion=dashfacturas"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i>
                  </button></a>
                </div>
              </form>
            </div>
          </div>
        </div>
        <section id="facturacion">
          <h2 class="card-title"> Análisis de Facturación  </h2>
            <div class="row mt-2">
              <div class="col-md-12">
                <h4 class="card-title"> Facturas emitidas por fecha</h4>
              </div>
              <div class="col-md-8 offset-md-2">
                <div class="card-block chartjs" id="containfacturas">
                  <canvas id="canvafacturas" style=""></canvas>
                </div>
                <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                  <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
                </div>
              </div>
              <div id="divtablafacturas" class="col-md-12"> <!-- -->
              </div>
              <!-- Fin de convrsión de tableros-->

              <!-- <hr class="mt-3">-->
              <!-- Inicio de tableros por asesor-->
              <div class="col-md-12">
                <h4 class="card-title p-1"> Top 10 clientes por facturación </h4>
              </div>
              <div class="col-md-12">
                <div class="card-block chartjs" id="containtop10facs">
                  <canvas id="canvatop10facs" style=""></canvas>
                </div>
                <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                  <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
                </div>
              </div>
              <div id="divtablatop10fac" class="col-md-12"> </div>
              </div>
              <!-- fin de tableros por asesor-->

              <div class="col-md-12">
                <h4 class="card-title p-1"> Balance Emitidas VS Recibidas </h4>
              </div>

              <div class="col-md-6">
                  <div class="card-block chartjs" id="contenedoremires">
                    <canvas id="canvaemires"></canvas>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card-block chartjs" id="contenedorivaemires">
                    <canvas id="canvaivaemires"></canvas>
                  </div>
                </div>

              <!-- <hr class="mt-3"> -->
              <div class="col-md-12">
                <h4 class="card-title p-1"> Desglose de impuestos </h4>
              </div>
                <div class="col-md-12">
                  <div class="card-block chartjs" id="containimpuestos">
                    <canvas id="canvaimpuestos" style="max-width: 100px"></canvas>
                  </div>
                  <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                    <strong>Presiona sobre cualquier sección de la grafica para ver más detalles.
                  </div>
                </div>
                <div id="divtavlaimpuestos" class="col-md-12"></div>
                </div>
        </section>
    <!-- <hr class="mt-3"> -->
      </div>
    </div>


  <script>
    window.onload =  filtrarfacturas;
  </script>
  
<?php
}

function creditos($fini,$ffin,$cliente){
  echo '<form name="fechas" method="post"><center><table width="100%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <td>&nbsp;</td>
  <th><b>Cliente</b> </th>
  <td>'.menu_select_db('clientes','c_id','c_nmb',$cliente,'cliente','c_estatus = "1"').'</td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td>
    <input name="enviar" style="width:80px;" type="submit" value="" id="actualizar" class="botont"/>
    <a href="xlscreditos.php?tipo='.$tipo.'&fini='.$fini.'&ffin='.$ffin.'&cliente='.$cliente.'">
      <input name="xls" style="width:80px;" type="button" value="" id="descargaexcel" class="botont"/>
    </a>
    </td>

  </tr></thead>
  </table></form>';

  echo '<table width="100%" class="listam" border="0">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Vendedor</th>
            <th>Monto</th>
            <th>Abonos</th>
            <th>Estatus</th>
          </tr>
        </thead>';


  $sql = 'SELECT t_id,t_cliente,t_fcreo,t_ucreo,t_estatus FROM tickets WHERE DATE(t_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND t_estatus IN ("R","P")
          UNION
          SELECT t_id,t_cliente,t_fcreo,t_ucreo,t_estatus FROM tickets as tick WHERE  DATE(t_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'"
          AND t_id IN (SELECT DISTINCT(pt_ticket) FROM pagos_ticket INNER JOIN pagos ON p_id = pt_pago
          WHERE p_estatus = "A" AND pt_ticket = t_id)
          ORDER BY t_fcreo DESC';
  $result = mysql_query($sql) or die($sql);
  $totalvc = 0;
  $totalab = 0;
  while($row = mysql_fetch_array($result)){
    $montoc = busca($row['t_id'],'ticketsd','td_ticket','SUM(td_precio*td_cantidad)');
    $totalvc+=$montoc;
    if($row['t_estatus'] == "P"){ $cole = '#FFCC33'; $nmbe = "Pendiente"; }
    else{ $cole = '#00CC33'; $nmbe = "Pagado"; }
    echo '<tr>
            <th>'.$row['t_id'].'</th>
            <td>'.busca($row['t_cliente'],'clientes','c_id','c_nmb').'</th>
            <td>'.date('d-m-Y',strtotime($row['t_fcreo'])).'</th>
            <td>'.$row['t_ucreo'].'</th>
            <td>'.number_format($montoc,2).'</th>
            <td>&nbsp;</td>
            <td style="background:'.$cole.';">'.$nmbe.'</td>
          </tr>';
    $sqlp = 'SELECT * FROM pagos_ticket INNER JOIN pagos ON p_id = pt_pago
            WHERE pt_ticket = "'.$row['t_id'].'" AND p_estatus = "A"
            ORDER BY p_fecha DESC';
    $resultp = mysql_query($sqlp) or die($sqlp);
  //    $resultpn = mysql_query($sqlp) or die($sqlp);
    $sumabo = 0;
    $numabos = 0;
    while($rowp = mysql_fetch_array($resultp)){
      $sumabo+=$rowp['pt_monto'];
      $saldo=$montoc-$sumabo;
      $numabos++;

      echo '<tr>
              <td colspan="3">&nbsp;</td>
              <th>Abono</th>
              <td>'.date('d-m-Y',strtotime($rowp['p_fecha'])).'</td>
              <td>'.number_format($rowp['pt_monto'],2).'</td>
              <td>'.number_format($saldo,2).'</td>
            </tr>';
    }
    $totalab+=$sumabo;
    if($numabos >0)
    echo '<tr><th colspan="7">&nbsp;</th></tr>';

  }
  $saldof = $totalvc-$totalab;
  echo '<tr>
          <td colspan="3">&nbsp;</td>
          <td>Totales</td>
          <td>'.number_format($totalvc,2).'</td>
          <td>'.number_format($totalab,2).'</td>
          <td>'.number_format($saldof,2).'</td>
        </tr>';

}

function ventaspt($fini,$ffin,$estatus){
  if($estatus == "A"){ $esta = "selected"; $estc = "";}
  elseif($estatus == "C"){ $esta = ""; $estc = "selected";}

  echo '<form name="fechas" method="post"><center><table width="100%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <th><b>Estatus</b> </th>
  <td><select name="estatus">
        <option value="T" '.$esta.'>Aplicados y créditos</option>
        <option value="A" '.$esta.'>Solo Aplicados</option>
        <option value="P" '.$esta.'>Solo Créditos</option>
        <option value="C" '.$estc.'>Cancelados</option>
      </select></td>
  <td>&nbsp;</td>
  <td>
    <input name="enviar" style="width:80px;" type="submit" value="" id="actualizar" class="botont"/>
    <a href="reportes/xlsventaspt.php?tipo='.$tipo.'&fini='.$fini.'&ffin='.$ffin.'&estatus='.$estatus.'">
      <input name="xls" style="width:80px;" type="button" value="" id="descargaexcel" class="botont"/>
    </a>
    </td>

  </tr></thead>
  </table></form>';

  echo '<table width="100%" class="listam" border="0">
        <thead>
          <tr>
            <th align="center">Ticket</th>
            <th align="center">Código de barras </th>
            <th align="center">Articulo </th>
            <th align="center">Cantidad</th>
            <th align="center">Precio</th>
            <th align="center">Total</th>
            <th align="center">Descuento</th>
          </tr>
        </thead><tbody>';


  $sql = 'SELECT t_id FROM tickets WHERE DATE(t_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
  if($estatus){
    if($estatus == "T") $sql.= ' AND t_estatus IN ("A","P")' ;
    else $sql.= ' AND t_estatus = "'.$estatus.'"' ;
  }
  $result=mysql_query($sql) or die($sql);
  $totc = 0;
  $totp = 0;
  while($rowt = mysql_fetch_array($result)){
    $sqltd = 'SELECT * FROM ticketsd WHERE td_ticket = "'.$rowt['t_id'].'" ORDER BY td_id DESC';
    $resultd = mysql_query($sqltd);

    while($row = mysql_fetch_array($resultd)){
      $precio = ($row['td_cantidad']*$row['td_precio']);
      $totc+=$row['td_cantidad'];
      $totp+=$precio;

      if($row['td_descuento']>0){$desc="Si";}else{$desc="No";}
      echo '<tr><th align=center><a target="_BLANK" href="?modulo=tickets&accion=showtickets&id='.$rowt['t_id'].'">'.$rowt['t_id'].'</a></th>';
      echo '<td align=center>'.$row['td_articulo'].'</a></td>';
      echo '<td align=center>'.busca($row['td_articulo'],'articulos','a_id','a_nmb').'</td>';
      echo '<td style="text-align:right;">'.number_format($row['td_cantidad'],0).'</td>';
      echo '<td style="text-align:right;">$'.number_format($row['td_precio'],2).'</td>';
      echo '<td style="text-align:right;">$'.number_format($precio,2).'</td>';
      echo '<td style="text-align:right;">'.$desc.' - $ '.$row['td_descuento'].'</td></tr>';
    }
  }
  echo '</tbody><tfoot><tr>
          <td colspan="2">&nbsp;</td>
          <td style="text-align:right;">Total</td>
          <td>'.number_format($totc,2).'</td>
          <td>&nbsp;</td>
          <td>'.number_format($totp,2).'</td>
        </tr></tfoot></table>';

}

function rgastos(){
  if(!isset($_POST['desde'])) $_POST['desde'] = date('Y-m-01');
  if(!isset($_POST['hasta'])) $_POST['hasta'] = date('Y-m-d');

  echo '<center><table class="lista" width="100%" border="0">
  <form method="post">
  <thead><tr>
  <th>Desde</th>
  <td><input type="date" name="desde" value='.$_POST['desde'].' ></td>
  <td style="padding-left:10px;">&nbsp;</td>
  <th>Hasta</th>
  <td><input type="date" name="hasta" value="'.$_POST['hasta'].'" ></td>
  <th>Concepto</th>
  <td>'.menu_select_db('conceptosg','c_id','c_nmb',$_POST['concepto'],'concepto','c_estatus = 1').'</td>
  <td style="padding-left:10px;"><input name="enviar" type="submit" value="" id="actualizar" class="botont"/></td>';
  echo '<td ><a href="xlsrgastos.php?fi='.$_POST['desde'].'&ff='.$_POST['hasta'].'&c='.$_POST['concepto'].'" target=_SELF ><input type="button" value="" id="descargaexcel" class="botont"></a></td>';

  echo '</tr></thead></form></table>';

  if($_POST['concepto']) $sqlg = ' AND g_concepto = "'.$_POST['concepto'].'" ';
  $sql = 'SELECT * FROM gastos
          WHERE DATE(g_fechagen) BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'" '.$sqlg;
  $result = mysql_query($sql) or die($sql);
  $sum = 'SELECT SUM(g_monto) FROM gastos
          WHERE DATE(g_fechagen) BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'" '.$sqlg;
  $sumresult = mysql_query($sum) or die($sum);
  list($gastos) = mysql_fetch_array($sumresult);


  echo '<center><table border="0" width="90%" class="lista"><thead>
  <tr><th colspan="6">Gastos</th>
  <tr><th>Genero</th><th>Caja</th><th>Fecha que se Genero</th><th>Monto</th><th>Descripcion</th><th>Concepto</th></tr></thead><tbody>';

  while($row = mysql_fetch_array($result)){

   $fecha = explode(' ', $row['g_fechagen']);

  echo '<tr><th>'.$row['g_ugen'].'</th><td>'.$row['g_caja'].'</td><td>'.cambiar_fecha($fecha[0],'').$fecha[1].'</td><td><b>$ '.number_format($row['g_monto'],2).'</b></td><td>'.$row['g_descripcion'].'</td><td>'.busca($row['g_concepto'],'conceptosg','c_id','c_nmb').'</td></tr>';

  }

    echo '<tr><td></td><td></td><th>Total</th><td><b>$ '.number_format($gastos,2).'</b></td></tr>';
  echo '</table>';
}

function saldoscliente(){
  echo '<a href="xlsrsaldos.php" target=_SELF ><input type="button" value="" id="descargaexcel" class="botont"></a>';

  $sqlc = 'SELECT * FROM clientes WHERE c_estatus = "1" ORDER BY c_nmb';
  $resultc = mysql_query($sqlc) or die($sqlc);
  echo '<center><table class="lista" width="75%">';
  echo '<tr>
          <th>No. Cliente</th>
          <th>Nombre</th>
          <th>Saldo</th>
        </tr>';

  while($row = mysql_fetch_array($resultc)){
    $sqltc = 'SELECT t_id,t_cliente,t_fcreo,t_ucreo,t_estatus FROM tickets
              WHERE t_cliente = "'.$row['c_id'].'" AND t_estatus IN ("R","P")';
    $resultcrtc = mysql_query($sqltc) or die($sqltc);
    $saldo = 0;
      while($rowtdc = mysql_fetch_array($resultcrtc)){
      $abonosc = busca($rowtdc['t_id'],'pagos_ticket','pt_ticket','SUM(pt_monto)');
      $cargoc = busca($rowtdc['t_id'],'ticketsd','td_ticket','SUM(td_precio*td_cantidad)');
      $saldo+=$cargoc;
      $saldo-=$abonosc;
    }
    if($saldo > 0) $cols = "#CC0000"; else $cols = "#00CC33";

    echo '<tr>
            <th>'.$row['c_id'].'</th>
            <td>'.$row['c_nmb'].'</td>
            <td style="text-align:right;background:'.$cols.';color:#FFFFFF">$'.number_Format($saldo,2).'</td>
          </tr>';
  }
  echo '</table></center>';

}

function showestadisticos($fini,$ffin){

  echo '
  <div class="card p-2">

    <div class="row">
      <div class="col-md-12">
        <a href="?modulo=reportes&accion=index" class="btn btn-warning"><i class="fas fa-arrow-left"></i>
          Atrás
        </a>
        <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-search"></i> Filtrar
        </button>
      </div>
      <div id="filter-panel" class="col-md-12 p-2 collapse filter-panel">
        <div class="panel panel-default">
          <div class="panel-body">
            <form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=dashtableros">
              <div class="mb-5">
                <label for="tipom">Desde</label>
                <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
              </div>
              <div class="mb-5">
                <label for="tipom">Hasta</label>
                <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
              </div>
              <div class="mb-5">
                <label for="">Acciones</label><br>
                <button type="button" id="filtrar" onclick="dashceo()" class="btn btn-primary">Filtrar</button>
                <a href="?modulo=reportes&accion=dashtableros"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-12 ">
        <a style="display: inline-block;" class="btn btn-success mb-5" href="formats/xlsestatusreg.php?fini='.$fini.'&ffin='.$ffin.'"><i class="fas fa-file-excel"></i> Excel</a>
        <h3 class="mb-5" style="display: inline-block;">% de Estatus</h3> 
        <hr>
        <div id="contenedorpadre1" class="col-md-8 offset-md-2 mb-2">
          <canvas id="myChart1" style=""></canvas>
        </div>
      </div>
      <div class="mt-2" id="divtablaestatus"></div>

      <div class="col-md-12 ">
        <a style="display: inline-block;" class="btn btn-success mb-5" href="formats/xlsventasreg.php?fini='.$fini.'&ffin='.$ffin.'"><i class="fas fa-file-excel"></i> Excel</a>
        <h3 class="mb-5" style="display: inline-block;">% de Vendidos</h3> 
        <hr>
        <div id="contenedorpadre2" class="col-md-8 offset-md-2 mb-2">
          <canvas id="myChart2" style=""></canvas>
        </div>
      </div>
      <div class="mt-2" id="divtablaventas"></div>          

      <div class="col-md-12 ">
        <a style="display: inline-block;"  class="btn btn-success mb-5" href="formats/xlsventasxplan.php?fini='.$fini.'&ffin='.$ffin.'"><i class="fas fa-file-excel"></i> Excel</a>
        <h3 class="mb-5" style="display: inline-block;">Ventas X Plan </h3> 
        <hr>
        <div id="contenedorpadre3" class="col-md-8 offset-md-2 mb-2">
          <canvas id="myChart3" style=""></canvas>
        </div>
      </div>
      <div class="mt-2" id="divtablaventasxplan"></div>         
      
      <div class="col-md-12 ">
        <a style="display: inline-block;" class="btn btn-success mb-5" href="formats/xlsventasextra.php.php?fini='.$fini.'&ffin='.$ffin.'"><i class="fas fa-file-excel"></i> Excel</a>
        <h3 class="mb-5" style="display: inline-block;">Ventas Extra</h3>
        <hr>
        <div id="contenedorpadre4" class="col-md-8 offset-md-2 mb-2">
          <canvas id="myChart4" style=""></canvas>
        </div>
      </div>
      <div class="mt-2" id="divtablaventasextra"></div>         

    </div>
  </div>
  ';

  ?>

  <script>
  dashceo();
  function dashceo(){

    document.getElementById("contenedorpadre1").innerHTML = "";
    document.getElementById("contenedorpadre1").innerHTML = '<canvas id="myChart1" style=""></canvas>';
    document.getElementById("contenedorpadre2").innerHTML = "";
    document.getElementById("contenedorpadre2").innerHTML = '<canvas id="myChart2" style=""></canvas>';
    document.getElementById("contenedorpadre3").innerHTML = "";
    document.getElementById("contenedorpadre3").innerHTML = '<canvas id="myChart3" style=""></canvas>';
    document.getElementById("contenedorpadre4").innerHTML = "";
    document.getElementById("contenedorpadre4").innerHTML = '<canvas id="myChart4" style=""></canvas>';
      
    var fechai = document.getElementById("fini").value;
    var fechaf = document.getElementById("ffin").value;
    // % de estatus
    $.ajax({
      url: "query/dashregistros/estatusdash.php",
      type: "POST",
      dataType: 'json',
      data:{'fini':fechai,
            'ffin': fechaf},
      success: function(rtnData) {
        $.each(rtnData, function(dataType, data) {
          var myChart = new Chart(document.getElementById("myChart1"), {
            type: data.type,
            data: {
                datasets: data.datasets,
                labels: data.labels
            },
            options:  {
              plugins: {
                tooltip: {
                  callbacks: {
                    
                  }
                } 
              },
              scales: 
                {
                  x : {
                    display: true,
                    title: {
                      display: true,
                      text: 'Total',
                    }

                  },
                  y: {
                    display: true,
                    title: {
                      display: true,
                      text: 'Fecha',
                    }
                  },
                },
                responsive: true,
                title: {
                    display: true,
                    text: data.title
                }
            }
          });
          
          document.getElementById("myChart1").onclick = function(evt) {
          var activePoints = myChart.getElementAtEvent(evt)[0];
          var firstPoint = activePoints;
          var indexpoint = activePoints._datasetIndex;
          var label = myChart.data.labels[firstPoint._index];
          var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
          var estatuschar = myChart.data.datasets[firstPoint._datasetIndex].index[firstPoint._index]
          console.log(myChart.data.datasets[firstPoint._datasetIndex].index[firstPoint._index]);
          if (firstPoint !== undefined) {

            $.ajax({
              url: "query/dashregistros/estatustabla.php",
              type: "POST",
              data: {'estatus' : estatuschar,
                    'fini':fechai,
                    'ffin': fechaf},
            })
            .done(function(data){
              var div = document.getElementById('divtablaestatus');
              div.innerHTML = data;

            }); // FIN DONE FUNCTION

          }else{


          } // FIN IF SI NO ES INDEFINIDO

        };// FIN FUNCION ON CLICK


        });// FIN DEL EACH
      }, // FIN DEL SUCCESS PRINCIPAL
      error: function(rtnData) {
          //alert('error' + rtnData);
      }
    });
    // % de estatus

    // % de vendidos
    $.ajax({
      url: "query/dashregistros/ventasdash.php",
      type: "POST",
      dataType: 'json',
      data:{'fini':fechai,
            'ffin': fechaf},
      success: function(rtnData) {
        $.each(rtnData, function(dataType, data) {
          var myChart = new Chart(document.getElementById("myChart2"), {
            type: data.type,
            data: {
                datasets: data.datasets,
                labels: data.labels
            },
            options:  {
              plugins: {
                tooltip: {
                  callbacks: {
                    
                  }
                } 
              },
              scales: 
                {
                  x : {
                    display: true,
                    title: {
                      display: true,
                      text: 'Total',
                    }

                  },
                  y: {
                    display: true,
                    title: {
                      display: true,
                      text: 'Fecha',
                    }
                  },
                },
                responsive: true,
                title: {
                    display: true,
                    text: data.title
                }
            }
          });

          document.getElementById("myChart2").onclick = function(evt) {
            var activePoints = myChart.getElementAtEvent(evt)[0];
            var firstPoint = activePoints;
            var indexpoint = activePoints._datasetIndex;
            var label = myChart.data.labels[firstPoint._index];
            var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
            var estatuschar = myChart.data.datasets[firstPoint._datasetIndex].index[firstPoint._index]
            console.log(myChart.data.datasets[firstPoint._datasetIndex].index[firstPoint._index]);
            if (firstPoint !== undefined) {

              $.ajax({
                url: "query/dashregistros/ventastabla.php",
                type: "POST",
                data: {'estatus' : estatuschar,
                      'fini':fechai,
                      'ffin': fechaf},
              })
              .done(function(data){
                var div = document.getElementById('divtablaventas');
                div.innerHTML = data;

              }); // FIN DONE FUNCTION

            }else{


            } // FIN IF SI NO ES INDEFINIDO

          };// FIN FUNCION ON CLICK



        });// FIN DEL EACH
      }, // FIN DEL SUCCESS PRINCIPAL
      error: function(rtnData) {
          //alert('error' + rtnData);
      }
    });
    // % de vendidos

    // ventas x plan
    $.ajax({
      url: "query/dashregistros/ventasxplandash.php",
      type: "POST",
      dataType: 'json',
      data:{'fini':fechai,
            'ffin': fechaf},
      success: function(rtnData) {
        $.each(rtnData, function(dataType, data) {
          var myChart = new Chart(document.getElementById("myChart3"), {
            type: data.type,
            data: {
                datasets: data.datasets,
                labels: data.labels
            },
            options:  { 
              responsive: true,
              title: {
                  display: true,
                  text: data.title
              },
              scales: {
                  xAxes: [{
                      stacked: true,
                  }],
                  yAxes: [{
                      stacked: true,
                      ticks: {                                    
                          stepSize: 1  
                      }
                  }],
              }, 
              //barPercentage: 1.0,
            }
          });

          document.getElementById("myChart3").onclick = function(evt) {
            var activePoints = myChart.getElementAtEvent(evt)[0];
            var firstPoint = activePoints;
            var indexpoint = activePoints._datasetIndex;
            var label = myChart.data.labels[firstPoint._index];
            var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
            var estatuschar = myChart.data.datasets[firstPoint._datasetIndex].index[firstPoint._index]
            console.log(myChart.data.datasets[firstPoint._datasetIndex].index[firstPoint._index]);
            if (firstPoint !== undefined) {

              $.ajax({
                url: "query/dashregistros/ventastablaxplan.php",
                type: "POST",
                data: {'estatus' : estatuschar,
                      'fini':fechai,
                      'ffin': fechaf},
              })
              .done(function(data){
                var div = document.getElementById('divtablaventasxplan');
                div.innerHTML = data;

              }); // FIN DONE FUNCTION

            }else{


            } // FIN IF SI NO ES INDEFINIDO

          };// FIN FUNCION ON CLICK



        });// FIN DEL EACH
      }, // FIN DEL SUCCESS PRINCIPAL
      error: function(rtnData) {
          //alert('error' + rtnData);
      }
    });
    // ventas x plan

    // ventas extra
    $.ajax({
      url: "query/dashregistros/ventasextradash.php",
      type: "POST",
      dataType: 'json',
      data:{'fini':fechai,
            'ffin': fechaf},
      success: function(rtnData) {
        $.each(rtnData, function(dataType, data) {
          var myChart = new Chart(document.getElementById("myChart4"), {
            type: data.type,
            data: {
                datasets: data.datasets,
                labels: data.labels
            },
            options:  { 
              responsive: true,
              title: {
                  display: true,
                  text: data.title
              },
              scales: {
                  xAxes: [{
                      stacked: true,
                  }],
                  yAxes: [{
                      stacked: true,
                      ticks: {                                    
                          stepSize: 1  
                      }
                  }],
              }, 
              //barPercentage: 1.0,
            }
          });

          document.getElementById("myChart4").onclick = function(evt) {
            var activePoints = myChart.getElementAtEvent(evt)[0];
            var firstPoint = activePoints;
            var indexpoint = activePoints._datasetIndex;
            var label = myChart.data.labels[firstPoint._index];
            var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
            var estatuschar = myChart.data.datasets[firstPoint._datasetIndex].index[firstPoint._index]
            console.log(myChart.data.datasets[firstPoint._datasetIndex].index[firstPoint._index]);
            if (firstPoint !== undefined) {

              $.ajax({
                url: "query/dashregistros/ventasextratabla.php",
                type: "POST",
                data: {'estatus' : estatuschar,
                      'fini':fechai,
                      'ffin': fechaf},
              })
              .done(function(data){
                var div = document.getElementById('divtablaventasextra');
                div.innerHTML = data;

              }); // FIN DONE FUNCTION

            }else{


            } // FIN IF SI NO ES INDEFINIDO

          };// FIN FUNCION ON CLICK



        });// FIN DEL EACH
      }, // FIN DEL SUCCESS PRINCIPAL
      error: function(rtnData) {
          //alert('error' + rtnData);
      }
    });
    // ventas extra

  }

  </script>
  <?php
}

function ventastotales(){
  if(!isset($_POST['desde'])) $_POST['desde'] = date('Y-m-01');
  if(!isset($_POST['hasta'])) $_POST['hasta'] = date('Y-m-d');
  if(!isset($_POST['order'])) $_POST['order'] = "V";

  if(!isset($_POST['ventas0'])) $ventas0 = ""; else $ventas0 = "checked";

  if(!isset($_POST['existencia']) OR $_POST['existencia'] == 0){
    $_POST['existencia'] = "0";
    $e0='Selected';
    $sqle = '';
    $sqle2 = '';
  }
  elseif($_POST['existencia'] == "1"){
    $e1='Selected';
    $sqle = 'AND td_se = "0"';
    $sqle2 = 'AND cd_se = "0"';
  }
  else{
    $e2='Selected';
    $sqle = 'AND td_se = "1"';
    $sqle2 = 'AND cd_se = "1"';
  }
  if(!isset($totaldes)) $totaldes = NULL;

  if($_POST['order'] == "N"){ $odn = "checked"; $odv = ""; $order = "a_nmb ASC"; }
  elseif($_POST['order'] == "V"){ $odn = ""; $odv = "checked"; $order = "cantidad DESC, ptotal DESC"; }
    echo '<center><table width="80%" border="0">
       <thead>
     <form method="post">
  <th>Artículo</th><td><input type="text" name="articulo" value="'.$_POST['articulo'].'" placeholder="Nombre o fragmento del producto" size="30" onfocus="this.select();" ></td>
  <th>Desde</th><td><input type="date" name="desde" value="'.$_POST['desde'].'" ></td>
  <th>Hasta</th>
  <td><input type="date" name="hasta" value="'.$_POST['hasta'].'">
  </tr><tr>
  <td>Ordenar por</td>

  <td><b>Por Nombre</b>  <input type="radio" name="order" value="N" '.$odn.' />
      <b>Más vendidos</b>  <input type="radio" name="order" value="V" '.$odv.' /></td>
  <td></td>

  </td><td style="padding-left:0px;"><input name="enviar" type="submit" value="" id="actualizar" class="botont"/></td>
  <td ><a href="reportes/xlsventastotales.php?desde='.$_POST['desde'].'&hasta='.$_POST['hasta'].'&departamento='.$_POST['departamento'].'&existencia='.$_POST['existencia'].'&concepto='.$_POST['concepto'].'&order='.$_POST['order'].'" target=_SELF ><input type="button" value="" id="descargaexcel" class="botont"></a></td>
  </form></thead></table>';

    $sqlc2 = '';
    $concept = '';

  if($_POST['articulo']){
    $sqlc2.=' AND a_nmb LIKE "%'.$_POST['articulo'].'%"';
  }

  //sumo vendido en pv
  $sqlsum = 'SELECT SUM(td_cantidad) as cantidad,SUM(td_precio*td_Cantidad) as ptotal , td_articulo, td_precio, a_id,a_nmb,a_sku,a_proveedor,a_inventariado,a_marca
             FROM articulos INNER JOIN ticketsd ON a_id = td_articulo
             INNER JOIN tickets ON ticketsd.td_ticket = tickets.t_id
             WHERE tickets.t_estatus = "A"
             AND DATE(t_fcreo) BETWEEN "'.$_POST['desde'].'" AND "'.$_POST['hasta'].'" '.$sqlc2.'
             GROUP BY a_id
             ORDER BY '.$order;
  $resultsum = mysql_query($sqlsum) or die(mysql_error());

  echo '<center><table width="100%" border="0" class="listam">';
  echo '<thead>
  <tr><th colspan="7">Detalles de ventas por articulos '.$concept.'</th></tr>
  <tr>
    <th>Codigo de Barras</th>
    <th>SKU</th>
    <th>Articulo</th>
    <th>Proveedor</th>
    <th>Inventariado</th>
    <th>Cantidad</th>
    <th>Total</th>
  </tr></thead><tbody> ';

  $costototal=0;
  $preciototal=0;
  $cantidadtotal=0;
  $numa = 0;
  while($rowc = mysql_fetch_array($resultsum)){
    $numa++;
    $articulo=$rowc['td_articulo'];
    echo '<tr><th>'.$rowc['td_articulo'].'</th>
              <td>'. $rowc['a_sku'].'</td><b>
              <td>'.$rowc['a_nmb'].'</td></b>
              <td><center><b>'.busca($rowc['a_proveedor'],'proveedores','p_id','p_nmb').'</b></center></td>
              ';
    if(busca($articulo,'articulos','a_id','a_inventariado')==1){
      echo'<td><center>SI</center></td>';
    }
    else{
      echo'<td><center>NO</center></td>';
    }

   echo '<td style="text-align:right;">'.number_format($rowc['cantidad'],2).'</td>
         <td style="text-align:right;">$'.number_format($rowc['ptotal'],2).'</td>
         </tr>';

    $preciototal += $rowc['ptotal'];//+$rowsumcot['ctotal'];
    $cantidadtotal += $rowc['cantidad'];//+$rowsumcot['cantidadcot'];
  }

  echo '<tfoot><tr><td colspan="5" style="text-align:right;">Totales</td>
  <td style="text-align:right;">'.$cantidadtotal.'</td>
  <td style="text-align:right;">$'.number_format($preciototal,2).'</td></tr></tfoot>';

  echo '</tbody></table>';
}

function combos($fini,$ffin){
  echo '<form name="fechas" method="post"><center><table width="100%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <td>&nbsp;</td>
  <td>
    <input name="enviar" style="width:80px;" type="submit" value="" id="actualizar" class="botont"/>

    </td>

  </tr></thead>
  </table></form>';


  $sql = 'SELECT * FROM tickets WHERE DATE(t_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND t_estatus = "A"
          AND t_id IN (SELECT DISTINCT(tc_ticket) FROM ticketsc)';
  $resultt=mysql_query($sql) or die($sql);
  $totc = 0;
  $totp = 0;
  while($rowt = mysql_fetch_array($resultt)){
    $precio =  $rowt['t_efectivo']+$rowt['t_tdc']+$rowt['t_vales']+$rowt['t_deposito'];
    echo '<table width="100%" class="listam" border="0">
          <thead>
            <tr>
              <th align="center">Ticket</th>
              <td>
                <a target="_BLANK" href="?modulo=tickets&accion=showtickets&id='.$rowt['t_id'].'">
                  '.$rowt['t_id'].'
                </a>
              </td>
              <th align="center">Fecha</th>
              <td>'.fecha_formato($rowt['t_fcreo'],true,true).'</td>
              <th align="center">Precio</th>
              <td>'.number_format($precio,2).'</td>
            </tr>
            <tr>
              <th colspan="2">Producto vendido</th>
              <th colspan="2">Insumo</th>
              <th colspan="2">Cantidad</th>
            </tr>
          </thead><tbody>';

    $sqlc = 'SELECT td_articulo,tc_articulo,tc_cantidad
             FROM ticketsd INNER JOIN ticketsc ON (td_ticket = tc_ticket AND td_id = tc_idt)
             WHERE tc_ticket = "'.$rowt['t_id'].'"';
    $resultc = mysql_query($sqlc) or die($sqlc);
    while($rowc = mysql_fetch_array($resultc)){
      echo '<tr><th align=center colspan="2">'.busca($rowc['td_articulo'],'articulos','a_id','a_nmb').'</th>';
      echo '<td align=center colspan="2">'.busca($rowc['tc_articulo'],'articulos','a_id','a_nmb').'</td>';
      echo '<td style="text-align:right;" colspan="2">'.$rowc['tc_cantidad'].'</td></tr>';
    }
  }

}

function ventasprodsat($fini,$ffin){
  if($estatus == "A"){ $esta = "selected"; $estc = "";}
  elseif($estatus == "C"){ $esta = ""; $estc = "selected";}

  echo '<form name="fechas" method="post"><center><table width="100%" border="0">
  <thead><tr>
  <th><b>Fecha inicial</b> </th>
  <td><input type="date" name="fini" value="'.$fini.'" /></td>
  <td>&nbsp;</td>
  <th><b>Fecha final</b> </th>
  <td><input type="date" name="ffin" value="'.$ffin.'" /></td>
  <td><input name="enviar" style="width:80px;" type="submit" value="" id="actualizar" class="botont"/></td>
  </tr></thead>
  </table></form>';

  $sql = 'SELECT DISTINCT(d_producto) FROM facturasd WHERE d_factura
          IN (SELECT f_id FROM facturas WHERE f_estatus = "T" AND DATE(f_ftimbro) BETWEEN "'.$fini.'" AND "'.$ffin.'")';
  $resultf = mysql_query($sql);
  while($rowf = mysql_fetch_array($resultf)){
    $sqlf = 'SELECT * FROM facturasd INNER JOIN facturas ON f_id = d_factura
              WHERE d_producto = "'.$rowf['d_producto'].'" AND f_estatus = "T" AND DATE(f_ftimbro)
              BETWEEN "'.$fini.'" AND "'.$ffin.'"';
    $result = mysql_query($sqlf);
    echo '<table width="100%" class="listam" border="0">
        <thead>
          <tr><th colspan="7">'.$rowf['d_producto'].' - '.busca($rowf['d_producto'],'productos','p_claveps','p_nmb').'</th></tr>
          <tr>
            <th align="center">Factura</th>
            <th align="center">Cantidad</th>
            <th align="center">Concepto</th>
            <th align="center">Unitario</th>
            <th align="center">Importe</th>
            <th align="center">IVA</th>
            <th align="center">Total</th>
          </tr>
        </thead><tbody>';
    $importeprod = 0;
    $ivaprod = 0;
    $totalprod = 0;
    while($row = mysql_fetch_array($result)){
      $importe = $row['d_cantidad']*$row['d_costo'];
      $iva = $importe*0.16;
      $total = $importe+$iva;

      echo '<tr><th align=center>'.$row['f_folio'].'</th>';
      echo '<td align=center>'.number_format($row['d_cantidad'],0).'</a></td>';
      echo '<td align=center>'.$row['d_concepto'].'</td>';
      echo '<td style="text-align:right;">'.number_format($row['d_costo'],2).'</td>';
      echo '<td style="text-align:right;">$'.number_format($importe,2).'</td>';
      echo '<td style="text-align:right;">$'.number_format($iva,2).'</td>';
      echo '<td style="text-align:right;">$'.number_format($total,2).'</td>';
      $importeprod+=$importe;
      $totalprod+=$total;
      $ivaprod+=$iva;
    }
    echo '<tr><td colspan="3">&nbsp;</td><td>Totales</td>
            <td>$'.number_format($importeprod,2).'</td>
            <td>$'.number_format($ivaprod,2).'</td>
            <td>$'.number_format($totalprod,2).'</td>
          </tr>';
    echo '</table><br>';
  }
}

function showdashclientes(){

  $atras = '<a class="btn btn-warning" href="?modulo=reportes&accion=index"><i class="fas fa-arrow-circle-left"></i> Atrás</a>';
      
  $botones ='<a class="btn btn-success" href="#top10">Top 10 Clientes </a>
      <a class="btn btn-success" href="#ticket">Ticket Promedio</a>
      <a class="btn btn-success" href="#conver">% Conversión de Clientes</a>
      <a class="btn btn-success" href="#nuevos">Clientes Nuevos</a>';
    
    $filtro = '
          <div class="col-12">
            <label>Fecha inicio: </label> 
            <input type="date" name="finicio" id="finicio" class="form-control" value="'.date('Y-m-01').'" max="'.date('Y-m-d').'">
          </div>
          <div class="col-12">
            <label>Fecha final: </label>
            <input type="date" name="ffinal" id="ffinal" class="form-control" value="'.date('Y-m-d').'" max="'.date('Y-m-d').'">
          </div>  
          <div class="col-12">
            <div class="mb-5" bis_skin_checked="1">
              <label for="projectinput5">Encargado</label>
              <select id="encargado" name="encargado" class="form-control">
                <option value="">Todos los encargados</option>';
                $sql = 'SELECT u_id FROM usuarios WHERE u_estatus = "A"';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  $filtro.= '
                  <option value="'.$row['u_id'].'" >'.$row['u_id'].'</option>
                  ';
                }
                $filtro.= '
              </select>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-5" bis_skin_checked="1">
              <label for="projectinput5">Linea de Negocio</label>
              <select id="linean" name="linean" class="form-control">
                <option value="">Todas las lineas de negocio</option>';
                $sql = 'SELECT * FROM lineas_negocio WHERE ln_estatus = "A"';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  $filtro.= '
                  <option value="'.$row['ln_id'].'" >'.$row['ln_nmb'].'</option>
                  ';
                }
                $filtro.= '
              </select>
            </div>
          </div>
          <div class="col-12">
            <label> Filtrar: </label><br>
            <button type="button" id="filtrar" onclick="filtrarclientes()" class="btn btn-primary"><i class="fa fa-redo"></i> Filtrar</button>
            <a type="button" href="?modulo=reportes&accion=dashclientes" class="btn btn-warning"><i class="fas fa-times"></i> Limpiar</a> 
          </div>
        </div>
      </div>
    </div>';

    toolbar('DASHBOARD CLIENTES', $atras, $filtro, '', $botones);

    echo '<a href="#inicio" class="btn btn-success round" style="
    position: fixed;
    bottom: 40px;
    right: 40px;"><i class="fas fa-long-arrow-alt-up"></i></a>
    
    <!-- 
    <div class="input-form form-inline p-2 col-md-12">
      <div class="mb-5">
        <div class="col-md-3">
          <label>Fecha inicio: </label> 
          <input type="date" name="finicio" id="finicio" class="form-control" value="'.date('Y-m-01').'" max="'.date('Y-m-d').'">
        </div>
        <div class="col-md-3">
          <label>Fecha final: </label>
          <input type="date" name="ffinal" id="ffinal" class="form-control" value="'.date('Y-m-d').'" max="'.date('Y-m-d').'">
        </div>          
        <div class="col-md-3">
          <div class="mb-5" bis_skin_checked="1">
            <label for="projectinput5">Encargado</label>
            <select id="encargado" name="encargado" class="form-control">
              <option value="">Todos los encargados</option>';
              $sql = 'SELECT u_id FROM usuarios';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                echo '
                <option value="'.$row['u_id'].'" >'.$row['u_id'].'</option>
                ';
              }
            echo '
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-5" bis_skin_checked="1">
            <label for="projectinput5">Linea de Negocio</label>
            <select id="linean" name="linean" class="form-control">
              <option value="">Todas las lineas de negocio</option>';
              $sql = 'SELECT * FROM lineas_negocio';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                echo '
                <option value="'.$row['ln_id'].'" >'.$row['ln_nmb'].'</option>
                ';
              }
            echo '
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <label> Filtrar: </label><br>
          <button type="button" id="filtrar" onclick="filtrarcompras()" class="btn btn-primary">Filtrar</button>
        </div>
      </div>
    </div> -->
    <hr class="mt-3 mb-3">

    <section id="top10">
      <h4 class="card-title p-1"> Top 10 clientes </h4>
        <div class="row">
          <div class="col-md-12">
          </div>
          <div class="col-md-7">
            <div class="card-block chartjs" id="contenedorpadretop10">
              <canvas id="top10cl" style=""></canvas>
            </div>
            <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
            </div>
          </div>
          <div id="divgraf2" class="col-md-5">

          </div>
          <div id="divtabla" class="col-md-12"> <!-- -->
          </div> <!-- -->
        </div>
    </section>

    <hr class="mt-3 mb-3">

    <section  class="mt-3">
      <h4 class="card-title p-1"> Ticket Promedio  </h4>
          <div class="row">
            <div class="col-md-12">
            </div>
            <div class="col-md-7">
              <div class="card-block chartjs" id="contenedorpadreticket">
                <canvas id="ticket" style="max-width: 100px"></canvas>
              </div>
              <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <strong>Presiona sobre cualquier sección de la grafica para ver más detalles.
              </div>
            </div>
            <div id="divgraf2ticket" class="col-md-5">

            </div>
            <div id="divtablaticket" class="col-md-12"> <!-- -->
            </div> <!-- -->
          </div>
    </section>

    <hr class="mt-3">

    <section id="conver" class="mt-3">
      <h4 class="card-title p-1"> % Conversion de Clientes </h4>
          <div class="row">
            <div class="col-md-12">
            </div>

            <div class="col-md-8 offset-md-2">
              <div class="card-block chartjs" id="contenedorpadreconversion">
              <center> 
                <canvas id="conversion" style="max-width: 980px;"></canvas>
              </center>
              </div>
              <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <strong>Presiona sobre cualquier barra de la grafica para ver más detalles.
              </div>
            </div>
            <div id="divtablaconversion" class="col-md-12"> <!-- -->
            </div> <!-- -->
          </div>
    </section>

    <hr class="mt-3">

    <section class="mt-3">
      <h4 class="card-title p-1"> Clientes Nuevos  </h4>
          <div class="row">
            <div class="col-md-12">
            </div>
            <div class="col-md-8 offset-md-2">
              <div class="card-block chartjs" id="contenedorpadrenuevos">
              <center> 
                <canvas id="nuevos" style=""></canvas>
              </center>
              </div>
              <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <strong>Presiona sobre cualquier barra de la grafica para ver más detalles.
              </div>
            </div>
            <div id="divtablanuevos" class="col-md-12"> <!-- -->
            </div> <!-- -->
          </div>
    </section>

    <script>
      window.onload =  filtrarclientes;
    </script>

  </div>';


}

function showdashproductos(){

  
      $atras = '<a class="btn btn-warning" href="?modulo=reportes&accion=index"><i class="fas fa-arrow-circle-left"></i> Atrás</a>';
      $botones = '
      <a class="btn btn-success" href="#prov">  Productos X Proveedor</a>
      <!-- <a class="btn btn-success" href="#lineas">Productos X Linea de Negocio</a> -->
      <a class="btn btn-success" href="#utilidad">TOP 20 Productos X Utilidad</a>
      <a class="btn btn-success" href="#categoria">Productos X Categoria</a>
      <a class="btn btn-success" href="#vendedor">Productos X Vendedor</a>';
      $filtro  = '
          <div class="col-12">
            <label>Fecha inicio: </label> 
            <input type="date" name="finicio" id="finicio" class="form-control" value="'.date('Y-m-01').'" max="'.date('Y-m-d').'">
          </div>
          <div class="col-12">
            <label>Fecha final: </label>
            <input type="date" name="ffinal" id="ffinal" class="form-control" value="'.date('Y-m-d').'" max="'.date('Y-m-d').'">
          </div>  
          <div class="col-12">
            <div class="mb-5" bis_skin_checked="1">
              <label for="projectinput5">Encargado</label>
              <select id="encargado" name="encargado" class="form-control">
                <option value="">Todos los encargados</option>';
                $sql = 'SELECT u_id FROM usuarios WHERE u_estatus = "A"';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  $filtro .= '
                  <option value="'.$row['u_id'].'" >'.$row['u_id'].'</option>
                  ';
                }
                $filtro .= '
              </select>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-5" bis_skin_checked="1">
              <label for="projectinput5">Linea de Negocio</label>
              <select id="linean" name="linean" class="form-control">
                <option value="">Todas las lineas de negocio</option>';
                $sql = 'SELECT * FROM lineas_negocio WHERE ln_estatus = "A"';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  $filtro .= '
                  <option value="'.$row['ln_id'].'" >'.$row['ln_nmb'].'</option>
                  ';
                }
                $filtro .= '
              </select>
            </div>
          </div>
          <div class="col-12">
            <label> Filtrar: </label><br>
            <button type="button" id="filtrar" onclick="filtrarproductos()" class="btn btn-primary"><i class="fa fa-redo"></i> Filtrar</button>
            <a type="button" href="?modulo=reportes&accion=dashproductos" class="btn btn-warning"><i class="fas fa-times"></i> Limpiar</a> 
          </div>';

    toolbar('DASHBOARD PRODUCTOS', $atras, $filtro, '', $botones);

    echo '<a href="#inicio" class="btn btn-success round" style="
    position: fixed;
    bottom: 40px;
    right: 40px;"><i class="fas fa-long-arrow-alt-up"></i></a>
    
    <!-- 
    <div class="input-form form-inline p-2 col-md-12">
      <div class="mb-5">
        <div class="col-md-3">
          <label>Fecha inicio: </label> 
          <input type="date" name="finicio" id="finicio" class="form-control" value="'.date('Y-m-01').'" max="'.date('Y-m-d').'">
        </div>
        <div class="col-md-3">
          <label>Fecha final: </label>
          <input type="date" name="ffinal" id="ffinal" class="form-control" value="'.date('Y-m-d').'" max="'.date('Y-m-d').'">
        </div>          
        <div class="col-md-3">
          <div class="mb-5" bis_skin_checked="1">
            <label for="projectinput5">Encargado</label>
            <select id="encargado" name="encargado" class="form-control">
              <option value="">Todos los encargados</option>';
              $sql = 'SELECT u_id FROM usuarios';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                echo '
                <option value="'.$row['u_id'].'" >'.$row['u_id'].'</option>
                ';
              }
            echo '
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-5" bis_skin_checked="1">
            <label for="projectinput5">Linea de Negocio</label>
            <select id="linean" name="linean" class="form-control">
              <option value="">Todas las lineas de negocio</option>';
              $sql = 'SELECT * FROM lineas_negocio';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                echo '
                <option value="'.$row['ln_id'].'" >'.$row['ln_nmb'].'</option>
                ';
              }
            echo '
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <label> Filtrar: </label><br>
          <button type="button" id="filtrar" onclick="filtrarcompras()" class="btn btn-primary">Filtrar</button>
        </div>
      </div>
    </div> -->
    <hr class="mt-3 mb-3">

    <section id="prov">
      <h4 class="card-title p-1"> Productos X Proveedor </h4>
        <div class="row">
          <div class="col-md-12">
          </div>
          <div class="col-md-8 offset-md-2">
            <div class="card-block chartjs" id="contenedorpadreprodxprov">
              <canvas id="prodxprov" style=""></canvas>
            </div>
            <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
            </div>
          </div>
          <div id="divtablaprodxprov" class="col-md-12"> <!-- -->
          </div> <!-- -->
        </div>
    </section>

    <hr class="mt-3">

    <section >
      <h4 class="card-title p-1"> Productos X Lineas de Negocio </h4>
        <div class="row">
          <div class="col-md-12">
          </div>
          <div class="col-md-8 offset-md-2">
            <div class="card-block chartjs" id="contenedorpadrelineas">
              <canvas id="lineas" style=""></canvas>
            </div>
            <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
            </div>
          </div>
          <div id="divtablalineas" class="col-md-12"> <!-- -->
          </div> <!-- -->
        </div>
    </section>

    <hr class="mt-3">

    <section id="utilidad" class="mt-3">
      <h4 class="card-title p-1"> TOP 20 Productos X Utilidad </h4>
          <div class="row">
            <div class="col-md-12">
            </div>
            <div class="col-md-7">
              <div class="card-block chartjs" id="contenedorpadretop20">
              <center> 
                <canvas id="top20" style=""></canvas>
              </center>
              </div>
              <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <strong>Presiona sobre cualquier barra de la grafica para ver más detalles.
              </div>
            </div>
            <div id="divgraf2top20" class="col-md-5">

            </div>
            <div id="divtablatop20" class="col-md-12"> <!-- -->
            </div> <!-- -->
          </div>
    </section>
  
    <hr class="mt-3">

    <section id="" class="mt-3">
      <h4 class="card-title p-1"> Productos X Categoria </h4>
          <div class="row">
            <div class="col-md-12">
            </div>
            <div class="col-md-8 offset-md-2">
              <div class="card-block chartjs" id="contenedorpadrecat">
              <center> 
                <canvas id="categoria" style=""></canvas>
              </center>
              </div>
              <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <strong>Presiona sobre cualquier barra de la grafica para ver más detalles.
              </div>
            </div>
            <div id="divtablacategoria" class="col-md-12"> <!-- -->
            </div> <!-- -->
          </div>
    </section>

    <hr class="mt-3">

    <section id="vededor" class="mt-3">
      <h4 class="card-title p-1"> Productos X Vendedor </h4>
          <div class="row">
            <div class="col-md-12">
            </div>
            <div class="col-md-5">
              <div class="card-block chartjs" id="contenedorpadrevendedor">
              <center> 
                <canvas id="vendedor" style=""></canvas>
              </center>
              </div>
              <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <strong>Presiona sobre cualquier barra de la grafica para ver más detalles.
              </div>
            </div>
            <div id="divgraf2vendedor" class="col-md-7">
                
            </div>
            <div id="divtablavendedor" class="col-md-12"> <!-- -->
            </div> <!-- -->
          </div>
    </section>
    

  </div>


  <script>
    window.onload =  filtrarproductos;
  </script>
  ';
}

function showdashcompras(){
   
  echo '
  <div class="bg-white" id="inicio">
    

    <div  class="mt-3 col-md-12 input-form form-inline">
      <a class="btn btn-warning" href="?modulo=reportes&accion=index"><i class="fas fa-arrow-circle-left"></i> Atrás</a>
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fas fa-search"></i> Filtrar
      </button>
      <a class="btn btn-success" href="#prov">Compras X Proveedor</a>
      <a class="btn btn-success" href="#asesor">Compras X Asesor</a>
      <a class="btn btn-success" href="#lineas">Compras X Linea de Negocios</a>
    </div>
    <div id="filter-panel" class="input-form form-inline p-2 col-md-12 collapse filter-panel">
      <div class="panel panel-default">
        <div class="panel-body">
            <div class="col-md-2">
              <label>Fecha inicio: </label> 
              <input type="date" name="finicio" id="finicio" class="form-control" value="'.date('Y-m-01').'" max="'.date('Y-m-d').'">
            </div>
            <div class="col-md-2">
              <label>Fecha final: </label>
              <input type="date" name="ffinal" id="ffinal" class="form-control" value="'.date('Y-m-d').'" max="'.date('Y-m-d').'">
            </div>  
            <div class="col-md-2">
            <div class="mb-5" bis_skin_checked="1">
              <label for="projectinput5">Encargado</label>
              <select id="encargado" name="encargado" class="form-control">
                <option value="">Todos los encargados</option>';
                $sql = 'SELECT u_id FROM usuarios WHERE u_estatus = "A"';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  echo '
                  <option value="'.$row['u_id'].'" >'.$row['u_id'].'</option>
                  ';
                }
              echo '
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="mb-5" bis_skin_checked="1">
              <label for="projectinput5">Linea de Negocio</label>
              <select id="linean" name="linean" class="form-control">
                <option value="">Todas las lineas de negocio</option>';
                $sql = 'SELECT * FROM lineas_negocio WHERE ln_estatus = "A"';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  echo '
                  <option value="'.$row['ln_id'].'" >'.$row['ln_nmb'].'</option>
                  ';
                }
              echo '
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <label> Filtrar: </label><br>
            <button type="button" id="filtrar" onclick="filtrarcompras()" class="btn btn-primary"><i class="fa fa-redo"></i> Filtrar</button>
            <a type="button" href="?modulo=reportes&accion=dashcompras" class="btn btn-warning"><i class="fas fa-times"></i> Limpiar</a> 
          </div>
        </div>
      </div>
    </div>

    <a href="#inicio" class="btn btn-success round" style="
    position: fixed;
    bottom: 40px;
    right: 40px;"><i class="fas fa-long-arrow-alt-up"></i></a>
    
    <!-- 
    <div class="input-form form-inline p-2 col-md-12">
      <div class="mb-5">
        <div class="col-md-3">
          <label>Fecha inicio: </label> 
          <input type="date" name="finicio" id="finicio" class="form-control" value="'.date('Y-m-01').'" max="'.date('Y-m-d').'">
        </div>
        <div class="col-md-3">
          <label>Fecha final: </label>
          <input type="date" name="ffinal" id="ffinal" class="form-control" value="'.date('Y-m-d').'" max="'.date('Y-m-d').'">
        </div>          
        <div class="col-md-3">
          <div class="mb-5" bis_skin_checked="1">
            <label for="projectinput5">Encargado</label>
            <select id="encargado" name="encargado" class="form-control">
              <option value="">Todos los encargados</option>';
              $sql = 'SELECT u_id FROM usuarios';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                echo '
                <option value="'.$row['u_id'].'" >'.$row['u_id'].'</option>
                ';
              }
            echo '
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-5" bis_skin_checked="1">
            <label for="projectinput5">Linea de Negocio</label>
            <select id="linean" name="linean" class="form-control">
              <option value="">Todas las lineas de negocio</option>';
              $sql = 'SELECT * FROM lineas_negocio';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                echo '
                <option value="'.$row['ln_id'].'" >'.$row['ln_nmb'].'</option>
                ';
              }
            echo '
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <label> Filtrar: </label><br>
          <button type="button" id="filtrar" onclick="filtrarcompras()" class="btn btn-primary">Filtrar</button>
        </div>
      </div>
    </div> -->

    <hr class="mt-3 mb-3">

    <section id="prov">
        <div class="row">
          <div class="col-md-12">
          <h4 class="card-title p-1"> Compras X Proveedor </h4>
          </div>
          <div class="col-md-8 offset-md-2">
            <div class="card-block chartjs" id="contenedorpadrecomprasxprov">
              <canvas id="comprasxprov" style=""></canvas>
            </div>
            <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <strong>Presiona sobre cualquier sección de la grafica para ver más detalles.
            </div>
          </div>
          <div id="divtablacomprasxprov" class="col-md-12"> <!-- -->
          </div> <!-- -->
        </div>
    </section>

    <hr class="mt-3 mb-3">

    <section id="asesor">
      <h4 class="card-title p-1"> Compras X Asesor </h4>
        <div class="row">
          <div class="col-md-12">
          </div>
          <div class="col-md-8 offset-md-2">
            <div class="card-block chartjs" id="contenedorpadrecomprasxasesor">
              <canvas id="comprasxasesor" style=""></canvas>
            </div>
            <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <strong>Presiona sobre cualquier sección de la grafica para ver más detalles.
            </div>
          </div>
          <div id="divtablacomprasxasesor" class="col-md-12"> <!-- -->
          </div> <!-- -->
        </div>
    </section>

    <hr class="mt-3 mb-3">

    <section id="lineas">
      <h4 class="card-title p-1"> Historial de Costos X Linea de Negocios </h4>
        <div class="row">
          <div class="col-md-12">
          </div>
          <div class="col-md-8 offset-md-2">
            <div class="card-block chartjs" id="contenedorpadrecostosxln">
              <canvas id="costosxln" style=""></canvas>
            </div>
            <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
              <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
            </div>
          </div>
          <div id="divtablacostosxln" class="col-md-12"> <!-- -->
          </div> <!-- -->
        </div>
    </section>


  </div>


  <script>
    window.onload =  filtrarcompras;
  </script>



  ';

}

function conversiones(){

  echo '
  <div class="bg-white">
  <h4 class="card-title p-1">Mailing-Conversiones</h4>
    <div class="row">
      <div class="col-md-12">
      </div>
      <div class="col-md-7">
        <div class="card-block chartjs">
          <canvas id="myChart" style=""></canvas>
        </div>
        <div class="alert alert-success alert-dismissible fade in" role="alert" bis_skin_checked="1">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
          <strong>Presiona sobre cualquier punto de la grafica para ver más detalles.
        </div>
      </div>
      <div id="divcampana" class="col-md-5">

      </div>
      <div id="divtabla" class="col-md-12"> <!-- -->
      </div> <!-- -->
    </div>
  </div>';


  echo '




  ';

}

function escuelavirtual($fini,$ffin){

  echo '
  <div class="card p-2">
    <div class="row">
      <div class="col-md-12">
        <a href="?modulo=reportes&accion=index" class="btn btn-warning"><i class="fas fa-arrow-left"></i>
          Atrás
        </a>
        <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-search"></i> Filtrar
        </button>
      </div>
      <div id="filter-panel" class="col-md-12 p-2 collapse filter-panel">
        <div class="panel panel-default">
          <div class="panel-body">
            <form class="form-inline" role="form" method="post" action="?modulo=reportes&accion=dashtableros">
              <div class="mb-5">
                <label for="tipom">Desde</label>
                <input type="month" name="fini" id="fini" class="form-control" value="'.$fini.'" />
              </div>
              <div class="mb-5">
                <label for="tipom">Hasta</label>
                <input type="month" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
              </div>
              <div class="mb-5">
                <label for="">Acciones</label><br>
                <button type="button" id="filtrar" onclick="dashceo()" class="btn btn-primary">Filtrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-12 ">
        <a style="display: inline-block;" class="btn btn-success mb-5" href="formats/xlsestatusreg.php?fini='.$fini.'&ffin='.$ffin.'"><i class="fas fa-file-excel"></i> Excel</a>
        <h3 class="mb-5" style="display: inline-block;"># de Cursos vendidos</h3>
        <hr>
        <div id="contenedorpadre1" class="col-md-8 offset-md-2 mb-2">
          <canvas id="myChart1" style=""></canvas>
        </div>
      </div>
      <div class="mt-2" id="divtablanconversiones"></div>

      <div class="col-md-12 ">
        <a style="display: inline-block;" class="btn btn-success mb-5" href="formats/xlsestatusreg.php?fini='.$fini.'&ffin='.$ffin.'"><i class="fas fa-file-excel"></i> Excel</a>
        <h3 class="mb-5" style="display: inline-block;">Ventas por curso</h3>
        <hr>
        <div id="contenedorpadre2" class="col-md-8 offset-md-2 mb-2">
          <canvas id="myChart2" style=""></canvas>
        </div>
      </div>
      <div class="mt-2" id="divtablanventasxc"></div>

    </div>
  </div>
  ';
?>

  <script>
  dashescuelav();
  function dashescuelav(){

    document.getElementById("contenedorpadre1").innerHTML = "";
    document.getElementById("contenedorpadre1").innerHTML = '<canvas id="myChart1" style=""></canvas>';
    document.getElementById("contenedorpadre2").innerHTML = "";
    document.getElementById("contenedorpadre2").innerHTML = '<canvas id="myChart1" style=""></canvas>';
    var fechai = document.getElementById("fini").value;
    var fechaf = document.getElementById("ffin").value;
    // # de ventas
    $.ajax({
      url: "query/dashescuelavirtual/ventasxcursodash.php",
      type: "POST",
      dataType: 'json',
      data:{'fini':fechai,
            'ffin': fechaf},
      success: function(rtnData) {
        $.each(rtnData, function(dataType, data) {
          var myChart = new Chart(document.getElementById("myChart2"), {
            type: data.type,
            data: {
                datasets: data.datasets,
                labels: data.labels
            },
            options:  {
              plugins: {
                tooltip: {
                  callbacks: {

                  }
                }
              },
              scales:
                {
                  x : {
                    display: true,
                    title: {
                      display: true,
                      text: 'Total',
                    }

                  },
                  y: {
                    display: true,
                    title: {
                      display: true,
                      text: 'Fecha',
                    }
                  },
                },
                responsive: true,
                title: {
                    display: true,
                    text: data.title
                }
            }
          });

          document.getElementById("myChart2").onclick = function(evt) {
          var activePoints = myChart.getElementAtEvent(evt)[0];
          var firstPoint = activePoints;
          var indexpoint = activePoints._datasetIndex;
          var label = myChart.data.labels[firstPoint._index];
          var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
          var mes = myChart.data.datasets[firstPoint._datasetIndex].mes[firstPoint._index]
          var anio = myChart.data.datasets[firstPoint._datasetIndex].anio[firstPoint._index]
          var curso= myChart.data.datasets[firstPoint._datasetIndex].curso[firstPoint._index]
          console.log(anio);
          if (firstPoint !== undefined) {

            $.ajax({
              url: "query/dashescuelavirtual/ventasxcursotabla.php",
              type: "POST",
              data: {
                    'mes':mes,
                    'anio':anio,
                    'curso':curso},
            })
            .done(function(data){
              var div = document.getElementById('divtablanconversiones');
              div.innerHTML = data;

            }); // FIN DONE FUNCTION

          }else{


          } // FIN IF SI NO ES INDEFINIDO

        };// FIN FUNCION ON CLICK


        });// FIN DEL EACH
      }, // FIN DEL SUCCESS PRINCIPAL
      error: function(rtnData) {
          //alert('error' + rtnData);
      }
    });
    // # de ventas

    // Ventas X Curso
    $.ajax({
      url: "query/dashescuelavirtual/ncoversionesdash.php",
      type: "POST",
      dataType: 'json',
      data:{'fini':fechai,
            'ffin': fechaf},
      success: function(rtnData) {
        $.each(rtnData, function(dataType, data) {
          var myChart = new Chart(document.getElementById("myChart1"), {
            type: data.type,
            data: {
                datasets: data.datasets,
                labels: data.labels
            },
            options:  {
              plugins: {
                tooltip: {
                  callbacks: {

                  }
                }
              },
              scales:
                {
                  x : {
                    display: true,
                    title: {
                      display: true,
                      text: 'Total',
                    }

                  },
                  y: {
                    display: true,
                    title: {
                      display: true,
                      text: 'Fecha',
                    }
                  },
                },
                responsive: true,
                title: {
                    display: true,
                    text: data.title
                }
            }
          });

          document.getElementById("myChart1").onclick = function(evt) {
          var activePoints = myChart.getElementAtEvent(evt)[0];
          var firstPoint = activePoints;
          var indexpoint = activePoints._datasetIndex;
          var label = myChart.data.labels[firstPoint._index];
          var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
          var mes = myChart.data.datasets[firstPoint._datasetIndex].mes[firstPoint._index]
          var anio = myChart.data.datasets[firstPoint._datasetIndex].anio[firstPoint._index]
          console.log(anio);
          if (firstPoint !== undefined) {

            $.ajax({
              url: "query/dashescuelavirtual/ncoversionestabla.php",
              type: "POST",
              data: {
                    'mes':mes,
                    'anio':anio},
            })
            .done(function(data){
              var div = document.getElementById('divtablanconversiones');
              div.innerHTML = data;

            }); // FIN DONE FUNCTION

          }else{


          } // FIN IF SI NO ES INDEFINIDO

        };// FIN FUNCION ON CLICK


        });// FIN DEL EACH
      }, // FIN DEL SUCCESS PRINCIPAL
      error: function(rtnData) {
          //alert('error' + rtnData);
      }
    });
    // Ventas X Curso
  }

  </script>
  <?php
}

function cambios($tablero, $cotizacion, $fini, $ffin, $usuario){
  $filtro = '
    <form autocomplete="off" action="?modulo=tableros&accion=cambios" method="post" id="filtro" class="">
      <div class="mb-5">
        <label class="mr-sm-2">Tablero:</label>
        <div class="position-relative has-icon-left">
          <input type="text" id="tablero" name="tablero" class="form-control" placeholder="Nombre del tablero" value="'.$tablero.'">
        </div>
      </div>
      <div class="mb-5">
        <label class="mr-sm-2">Cotización:</label>
        <div class="position-relative has-icon-left">
          <input type="text" id="cotizacion" name="cotizacion" class="form-control" placeholder="Folio de la cotización cliente" value="'.$cotizacion.'" >
        </div>
      </div>
      <div class="mb-5">
        <label class="mr-sm-2">Usuario:</label>
        <select name="usuario" id="usuariofiltro" class="form-control">
          <option value="">TODOS</option>';
          //menu_select_db('usuarios','u_id','u_id',$agente,'agente','u_empresa = "'.$_SESSION['emp'].'" AND u_estatus = "A"',false,false,true,false,"Todos")
          $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A"';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if($row['u_id'] == $usuario) $agenteselect = 'selected';
            else $agenteselect = '';
            $filtro.= '<option value="'.$row['u_id'].'" '.$agenteselect.'>'.$row['u_id'].'</option>';
          }

          $filtro.= '
        </select>
      </div>
      <div class="mb-5">
        <label class="mr-sm-2">Desde:</label>
        <div class="position-relative has-icon-left">
          <input type="date" id="fini" name="fini" class="form-control" value="'.$fini.'" required>
          <input type="hidden" id="finilim" name="finilim" class="form-control" value="'.$fini.'">
          <div class="form-control-position">
            <i class="fas fa-calendar-alt"></i>
          </div>
        </div>
      </div>
      <div class="mb-5">
        <label class="mr-sm-2">Hasta:</label>
        <div class="position-relative has-icon-left">
          <input type="date" id="ffin" name="ffin" class="form-control" value="'.$ffin.'" >
          <input type="hidden" id="ffinlim" name="ffinlim" class="form-control" value="'.$ffin.'" >
          <div class="form-control-position">
            <i class="fas fa-calendar-alt"></i>
          </div>
        </div>
      </div>
      <div class="mb-5">
        <label>Acciones:</label><br>
        <button class="btn btn-success"><i class="fas fa-filter" style="color: #ffffff;"></i> Filtrar </button>
        <button class="btn btn-warning">
          <span class="glyphicon glyphicon-record"></span><i class="fas fa-times-circle" style="color: #ffffff;"></i> Limpiar
        </button>
      </div>
    </form>
  ';
  $atras = '<a href="?modulo=reportes&accion=index">
              <button type="button" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
            </a>';
    toolbar("Historial de cambio en paquetes",$atras,$filtro);

    echo '
    <div class="card mt-3">
      <div class="card-body row" >
        <div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table" id="myTable">
                <thead class="">
                  <th>Tablero</th>
                  <th>Cotización</th>
                  <th>Artículo original</th>
                  <th>Cambió a</th>
                  <th>Usuario</th>
                  <th>Fecha</th>
                  <th>Ir a la cotización</th>
                </thead>
                <tbody>
                ';
                
                while($row = $this->model->result->fetch_array()){
                  echo '<tr>
                    <td>'.$row['ct_nmb'].' </td>
                    <td>'.$row['cc_folio'].' </td>
                    <td>'.busca($row['cca_articulo'], 'articulos', 'a_id', 'a_nmb').' </td>
                    <td>'.busca($row['cca_cambio'], 'articulos', 'a_id', 'a_nmb').' </td>
                    <td>'.$row['cca_ugen'].' </td>
                    <td>'.$row['cca_fgen'].' </td>
                    <td> <a href="?modulo=cotizaciones&accion=index&id='.$row['cc_id'].'"><button class="btn btn-info"><i class="fas fa-eye"></i></button></a></td>
                  </tr>';
                }
                echo '
                </tbody>
              </table>
            </center>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <script>
    $("#myTable").DataTable( {
        paging: true,
        scrollY: 400,
        "order": [[5, "desc"]],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        responsivePriority: 1,
        pageLength: 50,  
    });
  </script>';
}

function formapago($fini,$ffin,$cliente,$fpago,$asesor){
  $selt = '';
  $selc = '';
  if(!$fpago) $selt = 'selected';
  else if($fpago == "EC") $selec = "selected";
  else if($fpago == "TD") $seltd = "selected";
  else if($fpago == "TC") $seltc = "selected";
  $botones = '';

  $atras = '<a href="?modulo=reportes&accion=index" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>
              Atrás
            </a>';
  
        $filtro = '
          <form class="" role="form" method="post" action="?modulo=reportes&accion=formapago" id="filtro">
            <div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Vendedor</label>
              <select class="form-control" name="asesor" id="asesor" >';
          if(!$asesor) $selt = "selected"; else $selt = "";
          $filtro.='<option value="" '.$selt.'>Todos</option>';
          $sqlv= 'SELECT DISTINCT(u_id),u_nmb,u_apellidos FROM usuarios INNER JOIN remisiones ON r_encargado = u_id
                  WHERE r_estatus != "N" ORDER BY u_nmb';
          $resultv = setq($sqlv);
          while($rowv = $resultv->fetch_array()){
            if($asesor == $rowv['u_id']) $selt = 'selected'; else $selt ="";
            $filtro.='<option value="'.$rowv['u_id'].'" '.$selt.'>'.$rowv['u_nmb'].' '.$rowv['u_apellidos'].'</option>';
          }
          $filtro.='</select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="tipom">Forma de pago</label>
              <select class="form-control" name="fpago" id="fpago" >
                <option value="" '.$selt.'>Todos</option>';
                $sql = 'SELECT * FROM cfdi_fpago WHERE cf_referencia ="1" AND cf_estatus="A"';
                $result = setq($sql);
                while($row = $result ->fetch_array() ){
                  if($fpago == $row['cf_id']) $selfp = 'selected';
                  else $selfp = '';
                  $filtro.= '<option value="'.$row['cf_id'].'" '.$selfp.'>'.$row['cf_descripcion'].'</option>';
                }
                $filtro .= '<option value="EC" '.$selec.'>EFECTIVO EN CAJA</option>';
                $filtro .= '<option value="TD" '.$seltd.'>TARJETA DE DÉBITO EN CAJA</option>';
                $filtro .= '<option value="TC" '.$seltc.'>TARJETA DE CRÉDITO EN CAJA</option>';
              $filtro.= '</select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=reportes&accion=formapago"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';
  
    $botones = ' <a target="_blank" href="formats/pdfformapago.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&fpago='.$fpago.'">
              <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </a>
             <a href="formats/xlsxfpago.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&fpago='.$fpago.'">
            <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
          </a>
            '; 
  
  echo '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("FORMAS DE PAGO POR VENTA",$atras ,$filtro, '', $botones);
     ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '
    <div class="table table-responsive text-medium">
      <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
          <th>Folio</th>
          <th>Fecha</th>
          <th>Vendedor</th>
          <th>Cliente</th>
          <th>Total Remision</th>
          <th>Monto</th>
          <th>Comisión</th>
          <th>Total ingreso</th>
          <th>Forma de pago</th>
          <th>Estatus</th>
          <th></th>
    </tr></thead><tbody>';
  /*
    echo '

    <script>
      $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          processing: true,
          serverside: true,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatablerepventasd.php",
            type: "POST",
            datatype: "json",
          },
          pageLength: "1000",
          responsivePriority: 1,

      });
    </script>

    ';
  */

  $totalsubtotal = 0;
  $totalcomision = 0;
  $totalingresos = 0;
  $totaldescuento = 0;
  $totalfinal = 0;
  $totalenvios = 0;
  $numrems = 0;
  $total = 0;
  $sumfpago = array();
  $fpagos = array();

  //$fpagos = array('1'=>'EFECTIVO', '3'=>'TRANSFERENCIA ELECTRÓNICA DE FONDOS', '4'=>'TARJETA DE CRÉDITO', '18'=>'TARJETA DE DÉBITO', '21'=>'INTERMEDIARIO PAGOS');
  if($fpago != '' && $fpago != 'EC' && $fpago != 'TC' && $fpago != 'TD'){
    $sql4 = ' AND i_fpago = "'.$fpago.'"';
  }
  if($asesor) {
    $sql2 =' AND r_encargado = "'.$asesor.'"';
    $sql3 =' AND cd_ugen = "'.$asesor.'"';
  }
  $sql = 'SELECT cx_referencia AS remision, i_fecha AS fecha, i_monto AS monto, cf_descripcion as fpago, "I" AS tipo, i_comision AS comision, cf_id AS fpagoid FROM ingresos
            INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso INNER JOIN cxcobrar ON cx_id = ic_cxcobrar INNER JOIN cfdi_fpago ON i_fpago = cf_id 
            WHERE ic_cxcobrar IN (SELECT cx_id FROM cxcobrar INNER JOIN remisiones ON r_id = cx_referencia WHERE cx_tipo = "R" AND r_estatus NOT IN ("N", "C")'.$sql2.')
            AND i_estatus NOT IN ("C", "N", "P") AND DATE(i_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"'.$sql4;
  $sql.=' UNION SELECT cd_remision AS remision, cd_fgen AS fecha, cd_total AS monto, cd_id AS fpago, "C" AS tipo, cd_comision AS comision, "1" AS fpagoid
            FROM cajasd INNER JOIN remisiones ON r_id = cd_remision WHERE DATE(cd_fgen) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus NOT IN ("N", "C")'.$sql3.'';
  $sql.='ORDER BY fecha DESC';
  $result = setq($sql);

  while($rowal = $result->fetch_array()){
  $numrems++;

  $sqlrem = 'SELECT r_folio, r_total, r_cliente, r_encargado, r_estatus FROM remisiones WHERE r_id = "'.$rowal['remision'].'"';
  $resultrem = setq($sqlrem);
  list($folio, $totalrem, $cliente, $encargado, $estatus) = $resultrem -> fetch_array();
  if($estatus == "A"){ $color = "bg-primary"; $nmb = "Credito"; }
  elseif($estatus == "F" || $estatus == "FE"){ $color = "bg-success"; $nmb = "Liquidada"; }
  $nmbc = busca($cliente, 'crm_clientes', 'c_id', 'c_nmb');
  $apellidos = busca($cliente, 'crm_clientes', 'c_id', 'c_apellidos');
  $nmbcl = $nmbc.' '.$apellidos;
  $nmbencargado = busca($encargado,'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)');
  if($fpago != "EC" && $fpago != "TD" && $fpago != "TC" && $rowal['tipo'] == "I"){
    $totaling = $rowal['monto']+$rowal['comision'];
    $totalcomision += $rowal['comision'];
    $totalingresos += $totaling;
    echo '<tr>
      <th class="text-medium">'.$folio.'</th>
      <td class="text-medium">'.fecha_formato($rowal['fecha'],true,true).'</td>
      <td class="text-medium">'.$nmbencargado.'</td>
      <td class="text-medium">'.$nmbcl.'</td>
      <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalrem,2).'</td>
      <td class="text-medium number-align" style="text-align:right;">$ '.number_format($rowal['monto'],2).'</td>
      <td class="text-medium number-align" style="text-align:right;">$ '.number_format($rowal['comision'],2).'</td>
      <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totaling,2).'</td>
      <td class="text-medium number-align">'.$rowal['fpago'].'</td>
      <td class="'.$color.'">'.$nmb.'</td>
      <td>
        <a target="_BLANK" href="formats/notadeventa?id='.$rowal['remision'].'">
          <button type="button" class="btn-sm btn-info btn" title=""><i class="fa fa-print"></i></button>
        </a>
      </td>
    </tr>';
    $total+=$rowal['monto'];
    $fpagos[$rowal['fpagoid']] = $rowal['fpago'];
    if(!$sumfpago[$rowal['fpagoid']]) $sumfpago[$rowal['fpagoid']] = 0;
    $sumfpago[$rowal['fpagoid']] += $rowal['monto'];
  }
  if(($fpago == "" || $fpago == "EC" || $fpago == "TD" || $fpago == "TC") && $rowal['tipo'] == "C"){
    $sqlc = 'SELECT cd_efectivo, cd_tarjeta, cd_comision FROM cajasd WHERE cd_id = "'.$rowal['fpago'].'"';
    $resultc = setq($sqlc);
    list($efectivo, $tarjeta, $comision) = $resultc -> fetch_array();
    if(($fpago == "" || $fpago == "EC") && $efectivo > 0){
      $totalingresos += $efectivo;
      echo '<tr>
        <th class="text-medium">'.$folio.'</th>
        <td class="text-medium">'.fecha_formato($rowal['fecha'],true,true).'</td>
        <td class="text-medium">'.$nmbencargado.'</td>
        <td class="text-medium">'.$nmbcl.'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalrem,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($efectivo,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format('0',2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($efectivo,2).'</td>
        <td class="text-medium number-align">EFECTIVO EN CAJA</td>
        <td class="'.$color.'">'.$nmb.'</td>
        <td>
          <a target="_BLANK" href="formats/notadeventa?id='.$rowal['remision'].'">
            <button type="button" class="btn-sm btn-info btn" title=""><i class="fa fa-print"></i></button>
          </a>
        </td>
      </tr>';
      $total+=$efectivo;
      $fpagos['EF'] = "EFECTIVO EN CAJA";
      if(!$sumfpago['EF']) $sumfpago['EF'] = 0;
      $sumfpago['EF'] += $efectivo;

    }
    if(($fpago == "" || $fpago == "TC" && $comision > 0) && $tarjeta > 0){
      $totaling = $tarjeta + $comision;
      $totalcomision += $comision; 
      $totalingresos += $totaling;
      echo '<tr>
        <th class="text-medium">'.$folio.'</th>
        <td class="text-medium">'.fecha_formato($rowal['fecha'],true,true).'</td>
        <td class="text-medium">'.$nmbencargado.'</td>
        <td class="text-medium">'.$nmbcl.'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalrem,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($tarjeta,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($comision,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totaling,2).'</td>
        <td class="text-medium number-align">TARJETA DE CRÉDITO EN CAJA</td>
        <td class="'.$color.'">'.$nmb.'</td>
        <td>
          <a target="_BLANK" href="formats/notadeventa?id='.$rowal['remision'].'">
            <button type="button" class="btn-sm btn-info btn" title=""><i class="fa fa-print"></i></button>
          </a>
        </td>
      </tr>';
      $total+=$tarjeta;
      $fpagos['TC'] = 'TARJETA DE CRÉDITO EN CAJA';
      if(!$sumfpago['TC']) $sumfpago['TC'] = 0;
      $sumfpago['TC'] += $tarjeta;
    } else if(($fpago == "" || $fpago == "TD") && $tarjeta > 0){
      $totalingresos += $tarjeta;
      echo '<tr>
        <th class="text-medium">'.$folio.'</th>
        <td class="text-medium">'.fecha_formato($rowal['fecha'],true,true).'</td>
        <td class="text-medium">'.$nmbencargado.'</td>
        <td class="text-medium">'.$nmbcl.'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalrem,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($tarjeta,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format('0',2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($tarjeta,2).'</td>
        <td class="text-medium number-align">TARJETA DE DÉBITO EN CAJA</td>
        <td class="'.$color.'">'.$nmb.'</td>
        <td>
          <a target="_BLANK" href="formats/notadeventa?id='.$rowal['remision'].'">
            <button type="button" class="btn-sm btn-info btn" title=""><i class="fa fa-print"></i></button>
          </a>
        </td>
      </tr>';
      $total+=$tarjeta;
      $fpagos['TD'] = 'TARJETA DE CRÉDITO EN CAJA';
      if(!$sumfpago['TD']) $sumfpago['TD'] = 0;
      $sumfpago['TD'] += $tarjeta;
    }
  }

  
  }
  
  echo '<tfoot>
      <tr>
        <td colspan="5" style="text-align:right;">Totales monto</td>
        <td style="text-align:right; font-size:13px;">$'.number_format($total,2).'</td>
        <td style="text-align:right; font-size:13px;">$'.number_format($totalcomision,2).'</td>
        <td style="text-align:right; font-size:13px;">$'.number_format($totalingresos,2).'</td>
        <td></td>
      </tr>
    </tfood></table>';

    echo '
    <center>
    <div class="col-md-6 offset-md-1 table-responsive">
      <h1>Resumen </h1>
      <table class="table" id="myTable">
        <thead>
          <tr class="">
            <th>FORMA DE PAGO</th>
            <th>MONTO</th>
          </tr>
        </thead>
        <tbody>';
        foreach ($fpagos as $id => $nmb) {
            echo '<tr>
              <td> '.$nmb.' </td>
              <td>'.number_format($sumfpago[$id], 2).' </td>
            </tr>';
        }
        echo '</tbody>
      </table>
    </div>
    </center>';
  
}
  function ventascaducadas($fini,$ffin,$asesor){
    $botones = '';

    $atras = '<a href="?modulo=reportes&accion=index" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>
                Atrás
              </a>';
    
          $filtro = '
            <form class="" role="form" method="post" action="?modulo=reportes&accion=ventascaducadas" id="filtro">
              <div class="mb-5">
                <label for="tipom">Desde</label>
                <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
              </div>
              <div class="mb-5">
                <label for="tipom">Hasta</label>
                <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
              </div>
              <div class="mb-5">
                <label for="tipom">Vendedor</label>
                <select class="form-control" name="asesor" id="asesor" >';
            if(!$asesor) $selt = "selected"; else $selt = "";
            $filtro.='<option value="" '.$selt.'>Todos</option>';
            $sqlv= 'SELECT DISTINCT(u_id),u_nmb,u_apellidos FROM usuarios INNER JOIN remisiones ON r_encargado = u_id
                    WHERE r_estatus != "N" ORDER BY u_nmb';
            $resultv = setq($sqlv);
            while($rowv = $resultv->fetch_array()){
              if($asesor == $rowv['u_id']) $sqlt = 'selected'; else $selt ="";
              $filtro.='<option value="'.$rowv['u_id'].'" '.$selt.'>'.$rowv['u_nmb'].' '.$rowv['u_apellidos'].'</option>';
            }
            $filtro.='</select>
              </div>
              <div class="mb-5">
                <label for="">Acciones</label> <br>
                <button type="button" onclick="mandar(0);" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="?modulo=reportes&accion=ventasd"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
              </div>
            </form>';
    
      /* $botones = ' <a target="_blank" href="formats/pdfventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
              </a> 
              <a href="formats/xlsxventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
              </a>'; */

            
    
    echo '
      <script>
        function mandar(id){
          document.getElementById("filtro").submit();
        }
      </script>';

      //Sección: E1 Encabezado - Botones de acción
    toolbar("VENTAS VENCIDAS",$atras ,$filtro, '', $botones);

      ?>
        <div class="card mt-3">
          <div class="card-body row">
          <div class="col-md-12 col-12 col-sm-12">
            <?php
            echo '
      <div class="table table-responsive text-medium">
        <table class="table" id="myTable">
          <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
            <th>Folio</th>
            <th>Fecha</th>
            <th>Vendedor</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Abonado</th>
            <th>Restante</th>
    </tr></thead><tbody>';
  /*
      echo '

      <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            processing: true,
            serverside: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            ajax: {
              url: "query/datatablerepventasd.php",
              type: "POST",
              datatype: "json",
            },
            pageLength: "1000",
            responsivePriority: 1,

        });
      </script>

      ';
  */

  $totalremis = 0;
  $totalabonados = 0;
  $totalrestantes = 0;

  $numrems = 0;
  while($rowal = $this->model->result->fetch_array()){
  $numrems++;

  $totalrem = $rowal['r_total'];
  $abonado = $rowal['cx_abonado'];
  $restante = $totalrem - $abonado;
  $nmbcliente = busca($rowal['r_cliente'],'crm_clientes','c_id','c_nmb').' '.busca($rowal['r_cliente'],'crm_clientes','c_id','c_apellidos');

  echo '<tr>
          <th class="text-medium">'.$rowal['r_folio'].'</th>
          <td class="text-medium">'.fecha_formato($rowal['r_fliquidacion'],true,true).'</td>
          <td class="text-medium">'.busca($rowal['r_encargado'],'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)').'</td>
          <td class="text-medium">'.$nmbcliente.'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalrem,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($abonado,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($restante,2).'</td>
  </tr>';
  $totalremis+=$totalrem;
  $totalabonados+=$abonado;
  $totalrestantes+=$restante;

  }


  echo '<tfoot>
        <tr>
          <td>No. Compras vencidas '.$numrems.'</td>
          <td colspan="3" style="text-align:right;">Totales monto</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalremis,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalabonados,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalrestantes,2).'</td>
          
        </tr>
        </tfoot></table>';
  }

  function excedentes($fini,$ffin){
    $botones = '';
  
    $atras = '<a href="?modulo=reportes&accion=index" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>
                Atrás
              </a>';
    
          $filtro = '
            <form class="" role="form" method="post" action="?modulo=reportes&accion=excedentes" id="filtro">
              <div class="mb-5">
                <label for="tipom">Desde</label>
                <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
              </div>
              <div class="mb-5">
                <label for="tipom">Hasta</label>
                <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
              </div>
              <div class="mb-5">
                <label for="">Acciones</label> <br>
                <button type="button" onclick="mandar(0);" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="?modulo=reportes&accion=ventasd"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
              </div>
            </form>';
    
      /* $botones = ' <a target="_blank" href="formats/pdfventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
              </a> 
              <a href="formats/xlsxventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
              </a>'; */
  
             
    
    echo '
      <script>
        function mandar(id){
          document.getElementById("filtro").submit();
        }
      </script>';
  
      //Sección: E1 Encabezado - Botones de acción
    toolbar("EXCEDENTES",$atras ,$filtro, '', $botones);
  
       ?>
        <div class="card mt-3">
          <div class="card-body row">
          <div class="col-md-12 col-12 col-sm-12">
            <?php
            echo '
      <div class="table table-responsive text-medium">
        <table class="table" id="myTable">
          <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
            <th>Folio</th>
            <th>Fecha</th>
            <th>Vendedor</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Abonado</th>
            <th>Excedente</th>
    </tr></thead><tbody>';
   /*
      echo '
  
      <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            processing: true,
            serverside: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            ajax: {
              url: "query/datatablerepventasd.php",
              type: "POST",
              datatype: "json",
            },
            pageLength: "1000",
            responsivePriority: 1,
  
        });
      </script>
  
      ';
  */
  
  $totalremis = 0;
  $totalabonados = 0;
  $totalrestantes = 0;
  
  $numrems = 0;
  while($rowal = $this->model->result->fetch_array()){
  $numrems++;
  
  $totalrem = $rowal['cx_importe'];
  $abonado = $rowal['cx_abonado'];
  $restante = abs($totalrem - $abonado);
  $nmbcliente = busca($rowal['r_cliente'],'crm_clientes','c_id','c_nmb').' '.busca($rowal['r_cliente'],'crm_clientes','c_id','c_apellidos');
  
  echo '<tr>
          <th class="text-medium">'.$rowal['r_folio'].'</th>
          <td class="text-medium">'.fecha_formato($rowal['r_fliquidacion'],true,true).'</td>
          <td class="text-medium">'.busca($rowal['r_encargado'],'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)').'</td>
          <td class="text-medium">'.$nmbcliente.'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalrem,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($abonado,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($restante,2).'</td>
  </tr>';
  $totalremis+=$totalrem;
  $totalabonados+=$abonado;
  $totalrestantes+=$restante;
  
  }
  
  
  echo '<tfoot>
        <tr>
          <td>No. Compras vencidas '.$numrems.'</td>
          <td colspan="3" style="text-align:right;">Totales monto</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalremis,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalabonados,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalrestantes,2).'</td>
          
        </tr>
        </tfoot></table>';
  }

function ventaspagos($fini,$ffin,$cliente,$estatus,$asesor){
  $selt = '';
  $sela = '';
  $seln = '';
  $selp = '';
  $selc = '';
  if($estatus == "A") $sela = "selected";
  if($estatus == "N") $seln = "selected";
  if($estatus == "P") $selp = "selected";
  if($estatus == "C") $selc = "selected";
  if($estatus == "V") $selv = "selected";
  else $selt = 'selected';
  $botones = '';

  $atras = '<a href="?modulo=reportes&accion=index" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>
              Atrás
            </a>';

        $filtro = '
          <form class="" role="form" method="post" action="?modulo=reportes&accion=ventaspagos" id="filtro">
            <div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Vendedor</label>
              <select class="form-control" name="asesor" id="asesor" >';
          if(!$asesor) $selt = "selected"; else $selt = "";
          $filtro.='<option value="" '.$selt.'>Todos</option>';
          $sqlv= 'SELECT DISTINCT(u_id),u_nmb,u_apellidos FROM usuarios INNER JOIN remisiones ON r_encargado = u_id
                  WHERE r_estatus != "N" ORDER BY u_nmb';
          $resultv = setq($sqlv);
          while($rowv = $resultv->fetch_array()){
            if($asesor == $rowv['u_id']) $sqlt = 'selected'; else $selt ="";
            $filtro.='<option value="'.$rowv['u_id'].'" '.$selt.'>'.$rowv['u_nmb'].' '.$rowv['u_apellidos'].'</option>';
          }
          $filtro.='</select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value="" '.$selt.'>Todos</option>
                <option value="V" '.$selv.'>Vendidos</option>
                <option value="A" '.$sela.'>Aplicados</option>
                <option value="F" '.$sela.'>Liquidada</option>
                <option value="FE" '.$sela.'>Enviada</option>
                <option value="P" '.$selp.'>Por aprobar en caja</option>
                <option value="C" '.$selc.'>Cancelados</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=reportes&accion=ventasd"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';

  /*
      $botones = ' <a target="_blank" href="formats/pdfventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
              </a>
              <a href="formats/xlsxventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
              </a>';
  */


  echo '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("ESTADO DE CUENTA - VENTAS",$atras ,$filtro, '', $botones);

     ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '
    <div class="table table-responsive text-medium">
      <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
          <th>Folio</th>
          <th>Fecha</th>
          <th>Vendedor</th>
          <th>Cliente</th>
          <th>Importe</th>
          <th>Abonado</th>
          <th>Saldo</th>
          <th>Estatus</th>
    </tr></thead><tbody>';

  $total= 0;
  $totalabonado = 0;
  $totalsaldo = 0;

  $numrems = 0;
  while($rowal = $this->model->resultrem->fetch_array()){
  $numrems++;
  if($rowal['r_estatus'] == "A"){ $color = "bg-primary"; $nmb = "Credito"; }
  elseif($rowal['r_estatus'] == "F"){ $color = "bg-success"; $nmb = "Liquidada"; }
  elseif($rowal['r_estatus'] == "FE"){ $color = "bg-success"; $nmb = "Enviada"; }
  elseif($rowal['r_estatus'] == "P"){ $color = "bg-warning"; $nmb = "Proceso"; }
  elseif($rowal['r_estatus'] == "C"){ $color = "bg-danger"; $nmb = "Cancelada"; }

  $totalrem = ($rowal['r_total']-$rowal['r_iva']);
  $abonado = busca($rowal['r_id'],'cxcobrar','cx_tipo = "R" AND cx_referencia','cx_abonado');
  $saldo = $totalrem-$abonado;
  $nmbcliente = busca($rowal['r_cliente'],'crm_clientes','c_id','c_nmb').' '.busca($rowal['r_cliente'],'crm_clientes','c_id','c_apellidos');

  echo '<tr>
          <th class="text-medium">'.$rowal['r_folio'].'</th>
          <td class="text-medium">'.fecha_formato($rowal['r_faplica'],true,true).'</td>
          <td class="text-medium">'.busca($rowal['r_encargado'],'usuarios','u_id','CONCAT(u_nmb)').'</td>
          <td class="text-medium">'.$nmbcliente.'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalrem,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($abonado,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($saldo,2).'</td>
          <td class="'.$color.'">'.$nmb.'</td>
  </tr>';
  $total+=$totalrem;
  $totalabonado+=$abonado;
  $totalsaldo+=$saldo;
  }

  echo '<tr>
          <td colspan="3">&nbsp;</td>
          <td class="text-medium">Totales</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($total,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalabonado,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalsaldo,2).'</td>
          <td class="'.$color.'">'.$nmb.'</td>
  </tr>';

  /*
  if($totalsubtotal > 0) {
    $pordesc = ($totaldescuento*100)/$totalsubtotal;
    $porfinal = ($totalfinal*100)/$totalsubtotal;
  } else {
    $pordesc = 0;
    $porfinal = 0;
  }


  echo '<tfoot>
        <tr>
          <td colspan="5" style="text-align:right;">Totales monto</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalsubtotal,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totaldescuento,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalfinal,2).'</td>
          <td style="text-align:right; font-size:13px;">$'.number_format($totalenvios,2).'</td>
          <td>No. Compras '.$numrems.'</td>
        </tr>
        <tr>
          <td colspan="5" style="text-align:right;">Totales porcentajes</td>
          <td style="text-align:right; font-size:13px;">100%</td>
          <td style="text-align:right; font-size:13px;">'.number_format($pordesc, 2).'%</td>
          <td style="text-align:right; font-size:13px;">'.number_format($porfinal, 2).'%</td>
          <td></td>
        </tr>
        </tfood></table>';
  */
}

function cobroenvio($fini,$ffin,$asesor){
  
  $botones = '';

  $atras = '<a href="?modulo=reportes&accion=index" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>
              Atrás
            </a>';

        $filtro = '
          <form class="" role="form" method="post" action="?modulo=reportes&accion=cobroenvio" id="filtro">
            <div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Vendedor</label>
              <select class="form-control" name="asesor" id="asesor" >';
          if(!$asesor) $selt = "selected"; else $selt = "";
          $filtro.='<option value="" '.$selt.'>Todos</option>';
          $sqlv= 'SELECT DISTINCT(u_id),u_nmb,u_apellidos FROM usuarios INNER JOIN remisiones ON r_encargado = u_id
                  WHERE r_estatus != "N" ORDER BY u_nmb';
          $resultv = setq($sqlv);
          while($rowv = $resultv->fetch_array()){
            if($asesor == $rowv['u_id']) $sqlt = 'selected'; else $selt ="";
            $filtro.='<option value="'.$rowv['u_id'].'" '.$selt.'>'.$rowv['u_nmb'].' '.$rowv['u_apellidos'].'</option>';
          }
          $filtro.='</select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=reportes&accion=cobroenvio"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';

  /*
      $botones = ' <a target="_blank" href="formats/pdfventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
              </a>
              <a href="formats/xlsxventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
              </a>';
  */


  echo '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("COSTOS DE ENVÍO",$atras ,$filtro, '', $botones);

     ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '
    <div class="table table-responsive text-medium">
      <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
          <th>Folio</th>
          <th>Fecha</th>
          <th>Vendedor</th>
          <th>Cliente</th>
          <th>Costo de envio</th>
          <th>Empresa</th>
          <th>Cliente</th>
          <th>Diferencia</th>
    </tr></thead><tbody>';

  $total= 0;
  $totalabonado = 0;
  $totalsaldo = 0;
  $totaldiferencia = 0;

  $numrems = 0;
  while($rowal = $this->model->result->fetch_array()){
    $numrems++;

    $envio = busca($rowal['r_id'].' GROUP BY ga_cotizacion', 'guias_articulos', 'ga_cotizacion', 'SUM(ga_costo)'); // costo de envio real
    if($envio == 0) $envio = $rowal['cc_precioenvio']; // costo de envio aproximado (quitar cuando ya se capturen los costos dentro de las guias)
    if($envio != 0){
      $empresa = $envio - $rowal['r_precioenvio'];
      $cliente = $envio - $empresa;
      $diferencia = $cliente- $envio;
      if($diferencia < 0) $diferencia = 0;
      $nmbcliente = busca($rowal['r_cliente'],'crm_clientes','c_id','c_nmb').' '.busca($rowal['r_cliente'],'crm_clientes','c_id','c_apellidos');
      $asesor = busca($rowal['r_encargado'],'usuarios','u_id','CONCAT(u_nmb, " ",u_apellidos)');
      if($empresa < 0)$empresa = 0;

      echo '<tr>
        <th class="text-medium">'.$rowal['r_folio'].'</th>
        <td class="text-medium">'.fecha_formato($rowal['r_faplica'],true,true).'</td>
        <td class="text-medium">'.$asesor.'</td>
        <td class="text-medium">'.$nmbcliente.'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($envio,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($empresa,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($cliente,2).'</td>
        <td class="text-medium number-align" style="text-align:right;">$ '.number_format($diferencia,2).'</td>
      </tr>';
      $total+=$envio;
      $totalabonado+=$empresa;
      $totalsaldo+=$cliente;
      $totaldiferencia += $diferencia;
    }
  }

  echo '<tr>
          <td colspan="3">&nbsp;</td>
          <td class="text-medium">Totales</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($total,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalabonado,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totalsaldo,2).'</td>
          <td class="text-medium number-align" style="text-align:right;">$ '.number_format($totaldiferencia,2).'</td>
  </tr>';
}

function historialmov(){

  if(!$_REQUEST['desde']) $_REQUEST['desde'] = date('Y-m-d', strtotime('first day of this month'));
  if(!$_REQUEST['hasta']) $_REQUEST['hasta'] = date('Y-m-d');

  $desde = $_REQUEST['desde'];
  $hasta = $_REQUEST['hasta'];
 
  $index = $_REQUEST['index'];

  if($_REQUEST['almacen']){
    $almacen = $_REQUEST['almacen'];
  }else{
    //$almacen = NULL;
    $almacen = 1;
  }
  
  if($_REQUEST['articulo']){
    $articulo = $_REQUEST['articulo'];
    list($idart, $modelo) = getIDprodVar($articulo);
    if(!$idart) {
      $idart = getIDprod($articulo);
      $modelo = '';
    }
  }else{

  }
  //$almacen = '1';

  //$invrapido = getmax('i_id','invrapido','i_estatus = "A"',false);
  if($_REQUEST['invinicial']) {
    $invrapido = $_REQUEST['invinicial'];
    $fecha = busca($invrapido,'invrapido','i_id','i_fechaapli');
  }else{
    // else $invrapido = getmax('i_id','invrapido','i_estatus = "A"',false);
    $fecha = date('Y-m-d', strtotime('-1 year'));
  }
  // else 

  $sql = 'SELECT * FROM articulos INNER JOIN invrapidod ON id_articulo = a_id 
          WHERE id_inventario = "'.$invrapido.'" AND id_almacen = "'.$almacen.'" 
          AND id_articulo = "'.$idart.'" ORDER BY a_id';
  $result = setq($sql);

  if($index == "1" || !$index){
    $classs1 = 'btn-primary ';
  }elseif($index == "2"){
    $classs2 = 'btn-primary ';
  }elseif($index == "3"){
    $classs3 = 'btn-primary ';
  }elseif($index == "4"){
    $classs4 = 'btn-primary ';
  }elseif($index == "5"){
    $classs5 = 'btn-primary ';
  }elseif($index == "6"){
    $classs6 = 'btn-primary ';
  }elseif($index == "7"){
    $classs7 = 'btn-primary ';
  }

  $botones = '';

  $atras = '<a href="?modulo=reportes&accion=index" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>
              Atrás
            </a>';

        $filtro = '
          <form class="" role="form" method="post" action="?modulo=reportes&accion=historialmov" id="filtro">
            <div class="form-group">
              <label for="" class="">Artículo</label>
              <input type="text" name="articulo" id="articulo" class="form-control" value="'.$_REQUEST['articulo'].'">
              <div class="" id="suggestions"></div>
            </div>
            <div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="desde" id="desde" class="form-control" value="'.$_REQUEST['desde'].'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="hasta" id="hasta" class="form-control" value="'.$_REQUEST['hasta'].'" />
            </div>
            <div class="form-group">
              <label for="" class="">Almacén</label>
              <select name="almacen" id="" class="form-control">'; 
                $sql = 'SELECT * FROM almacenes';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  if($almacen == $row['a_id']) $almselect = 'selected';
                  else $almselect = '';
                  $filtro.= '
                  <option value="'.$row['a_id'].'" class="" '.$almselect.'>'.$row['a_nmb'].'</option>
                  ';
                }
                $filtro.= '
              </select>
            </div>
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=reportes&accion=historialmov"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';

  /*
      $botones = ' <a target="_blank" href="formats/pdfventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
              </a>
              <a href="formats/xlsxventasd.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'">
                <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
              </a>';
  */


  echo  '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("HISTORIAL DE MOVIMIENTOS",$atras ,$filtro, '', $botones);

  
  // <h5 class="">CANTIDAD DE INVENTARIO INICIAL: '.number_format(busca($idart,'invrapidod','id_almacen = "'.$almacen.'" AND id_inventario = "'.$invrapido.'" AND id_articulo','id_cantidad'),2).'</h5>
  echo '<div class="">
  <div class="card" bis_skin_checked="1">
    <!-- <div class="card-header" bis_skin_checked="1">
      <h4 class="card-title">Basic Justified Tab</h4>
    </div> -->
    <div class="card-body" bis_skin_checked="1">
      <div class="card-block" bis_skin_checked="1">
        
        <ul class="nav nav-tabs nav-justified p-3 " style="font-size: large; color: blue !important;">
          <li class="nav-item">
            <a class="nav-link btn '.$classs1.'" href="?modulo=reportes&accion=historialmov&articulo='.$articulo.'&almacen='.$almacen.'&desde='.$desde.'&hasta='.$hasta.'" >Embarques</a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn '.$classs2.'" href="?modulo=reportes&accion=historialmov&index=2&articulo='.$articulo.'&almacen='.$almacen.'&desde='.$desde.'&hasta='.$hasta.'" >Entradas</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link btn '.$classs3.'" href="?modulo=reportes&accion=historialmov&index=3&articulo='.$articulo.'&almacen='.$almacen.'&desde='.$desde.'&hasta='.$hasta.'" >Entrada Traspaso</a>
          </li>
          <!-- <li class="nav-item">
            <a class="nav-link btn '.$classs4.'" href="?modulo=reportes&accion=historialmov&index=4&articulo='.$articulo.'&almacen='.$almacen.'&desde='.$desde.'&hasta='.$hasta.'" >Compra</a>
          </li> -->
          <li class="nav-item">
            <a class="nav-link btn '.$classs5.'" href="?modulo=reportes&accion=historialmov&index=5&articulo='.$articulo.'&almacen='.$almacen.'&desde='.$desde.'&hasta='.$hasta.'" >Ventas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn '.$classs6.'" href="?modulo=reportes&accion=historialmov&index=6&articulo='.$articulo.'&almacen='.$almacen.'&desde='.$desde.'&hasta='.$hasta.'" >Salidas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn '.$classs7.'" href="?modulo=reportes&accion=historialmov&index=7&articulo='.$articulo.'&almacen='.$almacen.'&desde='.$desde.'&hasta='.$hasta.'" >Salida Traspaso</a>
          </li>
        </ul>
        <div class="tab-content px-1 pt-1" bis_skin_checked="1">
          <div role="tabpanel" class="tab-pane active in" id="active" aria-labelledby="active-tab" aria-expanded="true" bis_skin_checked="1">';
          if($index == "1" || !$index){
            $sql = 'SELECT COUNT(*) cantidad, r_folio, e_id, e_nmb, u_nmb, u_apellidos, e_ffin, e_hfin, CONCAT(e_ffin, " ", e_hfin) fecha FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision
             INNER JOIN embarques ON rc_embarque = e_id INNER JOIN usuarios ON e_ufinaliza = u_id WHERE DATE(e_ffin) BETWEEN "'.$desde.'" AND "'.$hasta.'" 
             AND rc_articulo = "'.$idart.'" AND rc_modelo = "'.$modelo.'" AND r_estatus IN ("F", "A", "FE") GROUP BY r_id ORDER BY fecha ASC';
            $result = setq($sql);
            echo '
            <div class="table-responsive">
              <table class="table ">
                <thead class="">
                  <tr class="">
                    <th class="">Embarque</th>
                    <th class="">Remisión</th>
                    <th class="">Usuario</th>
                    <th class="">Aplicación</th>
                    <th class="">Cantidad</th>
                  </tr>
                </thead>
                <tbody class="">';
                $total = 0;
                while($row = $result->fetch_array()){
                  echo '
                  <tr class="">
                    <td class=""><a target="_blank" href="?modulo=embarcamiento&accion=show&id='.$row['e_id'].'" class="">'.$row['e_id'].' - '.$row['e_nmb'].'</a></td>
                    <td class="">'.$row['r_folio'].'</td>
                    <td class="">'.$row['u_nmb'].' '.$row['u_apellidos'].'</td>
                    <td class="">'.$row['e_ffin'].' '.$row['e_hfin'].'</td>
                    <td class="number-align">'.number_format($row['cantidad'],2).'</td>
                  </tr> 
                  ';
                  $total += $row['cantidad'];
                }
            echo '      
                  <tr class="">
                    <td class="number-align" colspan="4"><b>Total</b></td>
                    <td class="number-align"><b>'.$total.'</b></td>
                  </tr>          
                </tbody>
              </table>
            </div>
            ';
          }elseif($index == "2"){ 
            $sql = 'SELECT * FROM `movimientos` INNER JOIN movimientosd ON md_movimiento = m_id AND md_tipo = m_tipo
                      INNER JOIN usuarios ON m_usuario = u_id WHERE md_articulo = "'.$idart.'" AND md_modelo = "'.$modelo.'"
                      AND (m_almacen = "'.$almacen.'" AND md_tipo = "E") AND  m_estatus = "A" AND m_fechaap BETWEEN "'.$desde.'" AND "'.$hasta.'" 
                      ';
                      // AND m_fechaap >= "'.$fecha.'" AND m_motivo != "INVETARIO RAPIDO NO. '.$invrapido.'" 
            $result = setq($sql);
            echo '
            <div class="table-responsive">
              <table class="table ">
                <thead class="">
                  <tr class="">
                    <th class="">Movimiento</th>
                    <th class="">Aplicación</th>
                    <th class="">Usuario</th>
                    <th class="">Descripción</th>
                    <th class="">Cantidad</th>
                  </tr>
                </thead>
                <tbody class="">';
                $total = 0;
                while($row = $result->fetch_array()){
                  echo '
                  <tr class="">
                    <td class=""><a target="_blank" href="?modulo=movimientos&accion=show&tipo=E&id='.$row['m_id'].'" class="">'.$row['m_id'].' - '.$row['m_folio'].'</a></td>
                    <td class="">'.$row['m_fechaap'].'</td>
                    <td class="">'.$row['u_nmb'].' '.$row['u_apellidos'].'</td>
                    <td class="">'.$row['m_motivo'].'</td>
                    <td class="number-align">'.number_format($row['md_cantidad'],2).'</td>
                  </tr> 
                  ';
                  $total += $row['md_cantidad'];
                }
            echo '      
                  <tr class="">
                    <td class="number-align" colspan="4"><b>Total</b></td>
                    <td class="number-align"><b>'.$total.'</b></td>
                  </tr>          
                </tbody>
              </table>
            </div>
            ';
          }elseif($index == "3"){
            $sql = 'SELECT * FROM `movimientos` INNER JOIN movimientosd ON md_movimiento = m_id AND md_tipo = m_tipo INNER JOIN usuarios ON m_usuario = u_id WHERE md_articulo = "'.$idart.'" AND md_modelo = "'.$modelo.'"
                      AND (m_almacendest = "'.$almacen.'" AND md_tipo = "T") AND m_estatus = "A" AND m_fechaap BETWEEN "'.$desde.'" AND "'.$hasta.'"';
                      // AND m_fechaap >= "'.$fecha.'" AND m_motivo != "INVETARIO RAPIDO NO. '.$invrapido.'" 
            $result = setq($sql);
            echo '
            <div class="table-responsive">
              <table class="table ">
                <thead class="">
                  <tr class="">
                    <th class="">Movimiento</th>
                    <th class="">Fecha</th>
                    <th class="">Usuario</th>
                    <th class="">Descripción</th>
                    <th class="">Cantidad</th>
                  </tr>
                </thead>
                <tbody class="">';
                $total = 0;
                while($row = $result->fetch_array()){
                  echo '
                  <tr class="">
                    <td class=""><a target="_blank" href="?modulo=movimientos&accion=show&tipo=E&id='.$row['m_id'].'" class="">'.$row['m_id'].' - '.$row['m_folio'].'</a></td>
                    <td class="">'.$row['m_fechaap'].'</td>
                    <td class="">'.$row['u_nmb'].' '.$row['u_apellidos'].'</td>
                    <td class="">'.$row['m_motivo'].'</td>
                    <td class="number-align">'.number_format($row['md_cantidad'],2).'</td>
                  </tr> 
                  ';
                  $total += $row['md_cantidad'];
                }
            echo '      
                  <tr class="">
                    <td class="number-align" colspan="4"><b>Total</b></td>
                    <td class="number-align"><b>'.$total.'</b></td>
                  </tr>          
                </tbody>
              </table>
            </div>
            ';
          }elseif($index == "4"){
            $sql = 'SELECT * FROM recepciones INNER JOIN recepcionesd ON r_id = rd_recepcion 
                    WHERE r_almacen = "'.$almacen.'" AND rd_articulo = "'.$idart.'" AND r_estatus = "A" AND r_fechaapli >= "'.$fecha.'" ';
                    // AND DATE(r_fechaapli) >= "'.$fecha.'"
            $result = setq($sql);
            echo '
            <div class="table-responsive">
              <table class="table ">
                <thead class="">
                  <tr class="">
                    <th class="">Compra</th>
                    <th class="">Fecha</th>
                    <th class="">Descripción</th>
                    <th class="">Cantidad</th>
                  </tr>
                </thead>
                <tbody class="">';
                $total = 0;
                while($row = $result->fetch_array()){
                  echo '
                  <tr class="">
                    <td class=""><a target="_blank" href="?modulo=recepciones&accion=show&id='.$row['r_id'].'" class="">'.$row['r_id'].' - '.$row['r_folio'].'</a></td>
                    <td class="">'.$row['r_fechaapli'].'</td>
                    <td class="">'.$row['r_observaciones'].'</td>
                    <td class="number-align">'.number_format($row['rd_cantidad'],2).'</td>
                  </tr> 
                  ';
                  $total += $row['rd_cantidad'];
                }
            echo '      
                  <tr class="">
                    <td class="number-align" colspan="3"><b>Total</b></td>
                    <td class="number-align"><b>'.$total.'</b></td>
                  </tr>          
                </tbody>
              </table>
            </div>
            ';
          }elseif($index == "5"){
            $sql = 'SELECT r_id, r_folio, r_faplica, u_nmb, u_apellidos, COUNT(*) cantidad, c_nmb, c_apellidos FROM remisionesc
                    INNER JOIN remisiones ON r_id = rc_remision INNER JOIN usuarios ON u_id = r_uaplica INNER JOIN crm_clientes ON c_id = r_cliente
                    WHERE rc_articulo = "'.$idart.'" AND rc_modelo = "'.$modelo.'"  AND DATE(r_faplica) BETWEEN "'.$desde.'" AND "'.$hasta.'"
                    AND r_estatus IN ("F", "FE", "A") GROUP BY r_id';
                    // AND DATE(pt_fechacobro) >= "'.$fecha.'"
            $result = setq($sql);
            // AND DATE(pt_fechacobro) >= "'.$fecha.'"
            echo '
            <div class="table-responsive">
              <table class="table ">
                <thead class="">
                  <tr class="">
                    <th class="">Remision</th>
                    <th class="">Fecha</th>
                    <th class="">Vendedor</th>
                    <th class="">Cliente</th>
                    <th class="">Cantidad</th>
                  </tr>
                </thead>
                <tbody class="">';
                $total = 0;
                while($row = $result->fetch_array()){
                  echo '
                  <tr class="">
                    <td class="">'.$row['r_folio'].'</td>
                    <td class="">'.Date('Y-m-d', strtotime($row['r_faplica'])).'</td>
                    <td class="">'.$row['u_nmb'].' '.$row['u_apellidos'].'</td>
                    <td class="">'.$row['c_nmb'].'</td>
                    <td class="number-align">'.number_format($row['cantidad'],2).'</td>
                  </tr> 
                  ';
                  $total += $row['cantidad'];
                }
            echo '      
                  <tr class="">
                    <td class="number-align" colspan="4"><b>Total</b></td>
                    <td class="number-align"><b>'.$total.'</b></td>
                  </tr>          
                </tbody>
              </table>
            </div>
            ';
          }elseif($index == "6"){
            $sql = 'SELECT * FROM `movimientos` INNER JOIN movimientosd 
                      ON md_movimiento = m_id AND md_tipo = m_tipo INNER JOIN usuarios ON u_id = m_usuario WHERE md_articulo = "'.$idart.'" AND md_modelo = "'.$modelo.'"
                      AND (m_almacen = "'.$almacen.'" AND md_tipo = "S") AND m_estatus = "A" AND DATE(m_fechaap) BETWEEN "'.$desde.'" AND "'.$hasta.'"
                      ';
                      // AND m_fechaap >= "'.$fecha.'" AND m_motivo != "INVETARIO RAPIDO NO. '.$invrapido.'" 
            $result = setq($sql);
            echo '
            <div class="table-responsive">
              <table class="table ">
                <thead class="">
                  <tr class="">
                    <th class="">Movimiento</th>
                    <th class="">Fecha</th>
                    <th class="">Usuario</th>
                    <th class="">Descripción</th>
                    <th class="">Cantidad</th>
                  </tr>
                </thead>
                <tbody class="">';
                $total = 0;
                while($row = $result->fetch_array()){
                  echo '
                  <tr class="">
                    <td class=""><a target="_blank" href="?modulo=movimientos&accion=show&tipo=S&id='.$row['m_id'].'" class="">'.$row['m_id'].' - '.$row['m_folio'].'</a></td>
                    <td class="">'.$row['m_fechaap'].'</td>
                    <td class="">'.$row['u_nmb'].' '.$row['u_apellidos']. '</td>
                    <td class="">'.$row['m_motivo'].'</td>
                    <td class="number-align">'.number_format($row['md_cantidad'],2).'</td>
                  </tr> 
                  ';
                  $total += $row['md_cantidad'];
                }
            echo '      
                  <tr class="">
                    <td class="number-align" colspan="4"><b>Total</b></td>
                    <td class="number-align"><b>'.$total.'</b></td>
                  </tr>          
                </tbody>
              </table>
            </div>
            ';
          }elseif($index == "7"){
            $sql = 'SELECT * FROM `movimientos` INNER JOIN movimientosd 
                      ON md_movimiento = m_id AND md_tipo = m_tipo INNER JOIN usuarios ON u_id = m_usuario WHERE md_articulo = "'.$idart.'"
                      AND (m_almacen = "'.$almacen.'" AND md_tipo = "T") AND m_estatus = "A" AND m_fechaap >= "'.$fecha.'" ';
                      // AND m_fechaap >= "'.$fecha.'" AND m_motivo != "INVETARIO RAPIDO NO. '.$invrapido.'" 
            $result = setq($sql);
            echo '
            <div class="table-responsive">
              <table class="table ">
                <thead class="">
                  <tr class="">
                    <th class="">Movimiento</th>
                    <th class="">Fecha</th>
                    <th class="">Descripción</th>
                    <th class="">Cantidad</th>
                  </tr>
                </thead>
                <tbody class="">';
                $total = 0;
                while($row = $result->fetch_array()){
                  echo '
                  <tr class="">
                    <td class=""><a target="_blank" href="?modulo=movimientos&accion=show&tipo=S&id='.$row['m_id'].'" class="">'.$row['m_id'].' - '.$row['m_folio'].'</a></td>
                    <td class="">'.$row['m_fechaap'].'</td>
                    <td class="">'.$row['m_motivo'].'</td>
                    <td class="number-align">'.number_format($row['md_cantidad'],2).'</td>
                  </tr> 
                  ';
                  $total += $row['md_cantidad'];
                }
            echo '      
                  <tr class="">
                    <td class="number-align" colspan="3"><b>Total</b></td>
                    <td class="number-align"><b>'.$total.'</b></td>
                  </tr>          
                </tbody>
              </table>
            </div>
            ';
          }
          echo '
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  ';
  ?>
  <script>
  $(document).ready(function() {
    $('#articulo').on('keyup', function() {
      if (event.keyCode == 38 || event.keyCode == 40){
        //console.log('chi');
      }else{
      var key = $(this).val();
      //var empresa = $('#empresa').val();
      var dataString = 'producto='+key;
      $.ajax({
        type: "POST",
        url: "query/suggestproducts2.php",
        data: dataString,
        success: function(data) {
          //Escribimos las sugerencias que nos manda la consulta
          $('#suggestions').fadeIn(300).html(data);
          //Al hacer click en alguna de las sugerencias
          $('.suggest-element').on('click', function(){
            //Obtenemos la id unica de la sugerencia pulsada
            var id = $(this).attr('id');
            var cb = $(this).attr('value');
            //Editamos el valor del input con data de la sugerencia pulsada
            $('#articulo').val($('#'+id).attr('data'));
            //Hacemos desaparecer el resto de sugerencias
            $('#suggestions').fadeOut(300);
            return false;
          });
        }
      });
    }
    });
  });
  </script>
  <?php
}

function embarquesxprod($fini,$ffin,$cliente,$estatus,$asesor, $categoria){
  $selt = '';
  $sela = '';
  $seln = '';
  $selp = '';
  $selc = '';
  if($estatus == "A") $sela = "selected";
  if($estatus == "N") $seln = "selected";
  if($estatus == "P") $selp = "selected";
  if($estatus == "C") $selc = "selected";
  if($estatus == "V") $selv = "selected";
  else $selt = 'selected';

        $atras = '<a href="?modulo=reportes&accion=index">
        <button type="button" class="btn btn-sm btn-warning mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
      </a>';

        $filtro = '
          <form class="" role="form" method="post" action="?modulo=reportes&accion=embarquesxprod" id="filtro">
            <div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Categoría</label>
              <select class="form-control" name="categoria" id="categoria">';
              if(!$categoria) $selc = "selected"; else if($categoria == "T") $selt = "selected"; else {$selc = ''; $selt = '';}
              $filtro.='<option value="" '.$selc.'>Todas</option>';
    
              $numcat = busca('1', 'categorias', 'cat_inflable', 'COUNT(*)');
              if($numcat > 1)
              $filtro.='<option value="T" '.$selt.'>TODOS LOS INFLABLES</option>';
              
              $sqlv= 'SELECT * FROM categorias';
              $resultv = setq($sqlv);
    
              while($rowv = $resultv->fetch_array()){
                if($categoria == $rowv['cat_id']) $selc = 'selected'; else $selc ="";
                $filtro.='<option value="'.$rowv['cat_id'].'" '.$selc.'>'.$rowv['cat_nmb'].'</option>';
              }
              $filtro.='</select>
            </div>
            <!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=reportes&accion=embarquesxprod"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';


          
            /* $botones = '<a target="_blank" href="formats/pdfventasxprod.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'&categoria='.$categoria.'">
              <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </a>
            <a href="formats/xlsxventasxprod.php?fini='.$fini.'&ffin='.$ffin.'&asesor='.$asesor.'&estatus='.$estatus.'&categoria='.$categoria.'">
              <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
            </a>'; */
            $botones = '';
          
            
          
  echo '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("EMBARQUES POR PRODUCTO","" ,$filtro, $atras, $botones);

     ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '
    <div class="table table-responsive">
      <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
          <tr>
            <th>Modelo</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Categoría</th>
          </tr></thead><tbody>';
    $sql = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, a_nmb FROM embarques INNER JOIN remisionesc ON e_id = rc_embarque 
              INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(e_ffin) BETWEEN "'.$fini.'" AND "'.$ffin.'"  AND e_estatus = "F"';
    if($categoria) {
      if($categoria == "T") $sql .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
      else $sql.=' AND a_categoria = "'.$categoria.'"';
    }            
    $sql.=' GROUP BY rc_articulo, rc_modelo ORDER BY a_nmb ASC';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $cant[$row['articulo']][$row['rc_modelo']] = 0; 
    }
  //echo $this->model->totalventa.'<<<<br><br><br>';


  $result = setq($sql);
  while($row = $result -> fetch_array()){
    $cant[$row['articulo']][$row['rc_modelo']]+= $row['cantidad'];
  }


  /* $sql = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, r_encargado, a_nmb FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision 
            INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'"  AND r_estatus IN ("A","F","FE") AND rc_ligado IS NULL';
  if($categoria) {
    if($categoria == "T") $sql .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
    else $sql.=' AND a_categoria = "'.$categoria.'"';
  }            
  if($asesor) $sql.=' AND r_encargado = "'.$asesor.'"';
  $sql.=' GROUP BY rc_articulo, rc_modelo ORDER BY a_nmb ASC'; */
  $result = setq($sql);
  $sumacant = 0;
  $sumatot = 0;
  $sumaporc = 0;

  while($row = $result->fetch_array()){
    $categoria = busca($row['a_categoria'], 'categorias', 'cat_id', 'cat_nmb');
    $nmb = $row['a_nmb'];
    if($row['rc_modelo']){
      $nmb.= ' '.busca($row['rc_modelo'], 'articulos_variantes', 'av_articulo = "'.$row['articulo'].'" AND av_modelo' ,'av_nmb');
    }
    echo '<tr>
              <th>'.$row['rc_modelo'].'</th>
              <th>'.$nmb.'</th>
              <th style="text-align:right;">'.number_format($cant[$row['articulo']][$row['rc_modelo']],0).'</th>
              <th>'.$categoria.'</th>
            </tr>';
    $sumacant+=$cant[$row['articulo']][$row['rc_modelo']];
  }
  echo '</tbody><tfoot><tr>
            <th colspan="2"></th>
            <th style="text-align:right;">'.number_format($sumacant,0).'</th>
          </tr></tfoot></table></div>';

  


}

function movimientosxprod($fini,$ffin,$tipo,$estatus,$asesor, $categoria){
  $selt = '';
  $sela = '';
  $seln = '';
  $selp = '';
  $selc = '';
  if($estatus == "A") $sela = "selected";
  if($estatus == "N") $seln = "selected";
  if($estatus == "P") $selp = "selected";
  if($estatus == "C") $selc = "selected";
  if($estatus == "V") $selv = "selected";
  else $selt = 'selected';

  if($tipo == "E") $seltipoe = 'selected';
  else $seltipos = 'selected';
  

        $atras = '<a href="?modulo=reportes&accion=index">
        <button type="button" class="btn btn-sm btn-warning mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
      </a>';

        $filtro = '
          <form class="" role="form" method="post" action="?modulo=reportes&accion=movimientosxprod" id="filtro">
            <div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Tipo</label>
              <select class="form-control" name="tipo" id="tipo">
                <option value="E" '.$seltipoe.'> Entradas </option>
                <option value="S" '.$seltipos.'> Salidas </option>
              </select >
            </div>
            <div class="mb-5">
              <label for="tipom">Categoría</label>
              <select class="form-control" name="categoria" id="categoria">';
              if(!$categoria) $selc = "selected"; else if($categoria == "T") $selt = "selected"; else {$selc = ''; $selt = '';}
              $filtro.='<option value="" '.$selc.'>Todas</option>';
    
              $numcat = busca('1', 'categorias', 'cat_inflable', 'COUNT(*)');
              if($numcat > 1)
              $filtro.='<option value="T" '.$selt.'>TODOS LOS INFLABLES</option>';
              
              $sqlv= 'SELECT * FROM categorias';
              $resultv = setq($sqlv);
    
              while($rowv = $resultv->fetch_array()){
                if($categoria == $rowv['cat_id']) $selc = 'selected'; else $selc ="";
                $filtro.='<option value="'.$rowv['cat_id'].'" '.$selc.'>'.$rowv['cat_nmb'].'</option>';
              }
              $filtro.='</select>
            </div>
            <!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=reportes&accion=embarquesxprod"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';


          
            /* $botones = '<a target="_blank" href="formats/pdfventasxprod.php?fini='.$fini.'&ffin='.$ffin.'&vendedor='.$asesor.'&estatus='.$estatus.'&categoria='.$categoria.'">
              <button type="button" class="btn btn-sm btn-danger mr-1"><i class="fas fa-file-pdf"></i> PDF</button>
            </a>
            <a href="formats/xlsxventasxprod.php?fini='.$fini.'&ffin='.$ffin.'&asesor='.$asesor.'&estatus='.$estatus.'&categoria='.$categoria.'">
              <button type="button" class="btn btn-sm btn-success mr-1"><i class="fas fa-file-excel"></i> Excel</button>
            </a>'; */
            $botones = '';
          
            
          
  echo '
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>';

    //Sección: E1 Encabezado - Botones de acción
  toolbar("MOVIMIENTOS POR PRODUCTO","" ,$filtro, $atras, $botones);

     ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '
    <div class="table table-responsive">
      <table class="table" id="myTable">
        <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
          <tr>
            <th>Modelo</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Categoría</th>
          </tr></thead><tbody>';
    $sql = 'SELECT  SUM(md_cantidad) cantidad, md_articulo articulo, md_modelo modelo, a_tipoprod, a_categoria, a_nmb FROM movimientos INNER JOIN movimientosd ON (md_movimiento = m_id AND m_tipo = md_tipo)
              INNER JOIN articulos ON a_id = md_articulo WHERE  DATE(m_fechaap) BETWEEN "'.$fini.'" AND "'.$ffin.'"  AND m_estatus = "A" AND m_tipo = "'.$tipo.'"';
    if($categoria) {
      if($categoria == "T") $sql .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
      else $sql.=' AND a_categoria = "'.$categoria.'"';
    }            
    $sql.=' GROUP BY md_articulo, md_modelo ORDER BY a_nmb ASC';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $cant[$row['articulo']][$row['modelo']] = 0; 
    }
  //echo $this->model->totalventa.'<<<<br><br><br>';


  $result = setq($sql);
  while($row = $result -> fetch_array()){
    $cant[$row['articulo']][$row['modelo']]+= $row['cantidad'];
  }


  /* $sql = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, r_encargado, a_nmb FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision 
            INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(r_faplica) BETWEEN "'.$fini.'" AND "'.$ffin.'"  AND r_estatus IN ("A","F","FE") AND rc_ligado IS NULL';
  if($categoria) {
    if($categoria == "T") $sql .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
    else $sql.=' AND a_categoria = "'.$categoria.'"';
  }            
  if($asesor) $sql.=' AND r_encargado = "'.$asesor.'"';
  $sql.=' GROUP BY rc_articulo, rc_modelo ORDER BY a_nmb ASC'; */
  $result = setq($sql);
  $sumacant = 0;
  $sumatot = 0;
  $sumaporc = 0;

  while($row = $result->fetch_array()){
    $categoria = busca($row['a_categoria'], 'categorias', 'cat_id', 'cat_nmb');
    $nmb = $row['a_nmb'];
    if($row['modelo']){
      $nmb.= ' '.busca($row['modelo'], 'articulos_variantes', 'av_articulo = "'.$row['articulo'].'" AND av_modelo' ,'av_nmb');
    }
    echo '<tr>
              <th>'.$row['modelo'].'</th>
              <th>'.$nmb.'</th>
              <th style="text-align:right;">'.number_format($cant[$row['articulo']][$row['modelo']],0).'</th>
              <th>'.$categoria.'</th>
            </tr>';
    $sumacant+=$cant[$row['articulo']][$row['modelo']];
  }
  echo '</tbody><tfoot><tr>
            <th colspan="2"></th>
            <th style="text-align:right;">'.number_format($sumacant,0).'</th>
          </tr></tfoot></table></div>';

  


}
}
?>