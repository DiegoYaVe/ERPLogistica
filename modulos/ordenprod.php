<?php
ini_set('display_errors', 0);
class ordenprod{

function __construct(){
    $this->model = new modelordenprod($obj);
}

function setmermas(){
  $id = $_GET['id'];
  $this->model->setmermas($id);

  redirect('?modulo=ordenprod&accion=show&id='.$id);
}
function index(){
    //foreachdie();
    if(!isset($_REQUEST['folio'])) $_REQUEST['folio'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "";
    if(!isset($_REQUEST['forecast'])) $_REQUEST['forecast'] = NULL;

    if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-30 days'));
    if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d', strtotime('+30 days'));

    $this->model->result($_REQUEST['folio'],$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'], $_REQUEST['forecast']);
    $this->view = new viewlordenprod($this->model);
    $this->view->browse($_REQUEST['folio'],$_REQUEST['estatus'],$_REQUEST['fini'], $_REQUEST['ffin'], $_REQUEST['forecast']);
}
function procesoemergente(){
  // foreachdie();

  if($_REQUEST['tipo']) $tipo = "M";
  else $tipo = "C";

  if($_REQUEST['bloqueo']) $bloqueo = '1';
  else $bloqueo = '0';

  $sqlup = 'UPDATE pr_procesosop SET
            pp_orden = (pp_orden+1)
            WHERE pp_orden > '.$_REQUEST['ant'].' AND pp_ordenp = "'.$_REQUEST['ordenp'].'"';
  setq($sqlup);

  $sqlop = 'SELECT * FROM pr_ordenprod WHERE po_id = "'.$_REQUEST['ordenp'].'" ';
  $resultop = setq($sqlop);
  $rowop = $resultop->fetch_array();

  $sql = 'INSERT INTO pr_procesosop SET
          pp_ordenp = "'.$_REQUEST['ordenp'].'",
          pp_solicitud = "'.$rowop['po_solicitud'].'",
          pp_tiposolicitud = "'.$rowop['po_tiposoli'].'",
          pp_estatus = "N",
          pp_proceso = "'.$_REQUEST['proceso'].'",
          pp_fase = "'.$_REQUEST['fase'].'",
          pp_supervisor = "'.$rowop['po_encargado'].'",
          pp_orden = "'.($_REQUEST['ant']+1).'",
          pp_tipoproceso = "'.$tipo.'",
          pp_planeado = "0",
          pp_bloqueo = "'.$bloqueo.'",
          pp_obs = "'.clearvmayus($_REQUEST['obs']).'"
           ';
  setq($sql);

  redirect('?modulo=ordenprod&accion=show&id='.$_REQUEST['ordenp']);

}

function insertmerma(){
  $id = $_GET['id'];
  /* $mp = $_POST['mp'];
  $cantidad = $_POST['cantidad'];
  $largo = $_POST['largo'];
  $alto = $_POST['alto'];
  $ancho = $_POST['ancho'];
  $this->model->setdatamerma($id,$mp, $cantidad, $largo, $alto, $ancho); */
  $this->model->insertmerma($id);
  redirect('?modulo=ordenprod&accion=mermas&id='.$id);
}

function updatemerma(){

  $id = $_GET['id'];
  $amid = $_GET['amid'];
  $cantidad = $_GET['cantidad'];
  $this->model->updatemerma($amid, $id, $cantidad);
  redirect('?modulo=ordenprod&accion=mermas&id='.$id);
}

function deletemerma(){
  $id = $_GET['id'];
  $amid = $_GET['amid'];
  $this->model->deletemerma($amid, $id);
  redirect('?modulo=ordenprod&accion=mermas&id='.$id);
}

function show(){
  $id = $_GET['id'];
  $this->model->select($id);
  $this->model->resultprocesos($id);
  $this->view = new viewlordenprod($this->model);
  $this->view->show();
}

function mermas(){
  $id = $_GET['id'];
  $this->model->select($id);
  $this->model->resultprocesosmp($id);
  $this->view = new viewlordenprod($this->model);
  $this->view->mermas();
}

function insert(){
  $folioop = getmax('po_folio','pr_ordenprod');
  $acrof = busca("1",'empresas','e_id','e_siglas').'-ORDP';
  if($folioop == "1"){
    $folioop = $acrof.str_pad($folioop,6,"0",STR_PAD_LEFT);
  }  
  $idprod = getIDprod($_POST['nmb']);
  if(!$idprod){ 
    list($idprod, $modelo) = getIDprodVar($_POST['nmb']);
    if(!$idprod){
      $idprod = "0";
      $modelo = NULL;
    }
  } else {
    $modelo = NULL;
  }

  if($_POST['tipo'] != "O") $_POST['texto'] = "";
  $tiposoli = busca($_POST['solicitud'], 'pr_solicitudes', 'ps_id','ps_tiposoli');
  if(!isset($_POST['forecast'])) $_POST['forecast'] = "";

  //Inicia elsubir el adjunto correspondiente a la orden de producción
  $tipoimagen = "";
if(!isset($_POST['timagen'])){
  $carpetaDestino = 'ordenesproduccion/'; // Reemplaza con la ruta donde deseas guardar el archivo
  $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato predeterminado
  $fechaHoraSinGuionesNiPuntos = str_replace(array("-", ":", " "), "", $fechaHora);

  if (isset($_FILES['adj']['name'])) {
    $nombre_archivo = $_FILES['adj']['name'];
    $tamano_archivo = $_FILES['adj']['size'];
    if($tamano_archivo > 3000000) $error = "6XMUE2";
    else{
      $adj = $carpetaDestino . $fechaHoraSinGuionesNiPuntos . $nombre_archivo;
      if (move_uploaded_file($_FILES['adj']['tmp_name'], 'img/'.$adj)){
        $error= 0;
        $tipoimagen = "S";
      } else {
        $adj = "NULL";
        $error = "6XMUE4";
      }
    }
  }else{
    $adj = "NULL";
    $error = 1;
  }
} else{
  $adj = $_POST['rutaimg'];
  $tipoimagen = "E";
}
  //Finaliza el subir el adjunto correspondiente a la orden de producción

  $this->model->setdata('', $folioop, $idprod, $_POST['nmb'], $modelo, $_POST['cantidad'], $_POST['tipo'],
           $_POST['texto'], date('Y-m-d H:i:s'), $_SESSION['uid'], "N", '', $_POST['encargado'], $_POST['cortador'],
            $_POST['fentrega'], $_POST['notasalida'], $_POST['almacen'], $_POST['forecast'], '', '', $tiposoli, $_POST['obstiposoli'],$_POST['solicitud'], $adj, $_POST['almacendestino'], $tipoimagen);
  $this->model->insert();

  $id = getmax('po_id', 'pr_ordenprod', false, false);

  if($_POST['forecastd']){
    $this->model->ligar($id, $_POST['forecastd'], $_POST['cantidad']);
  }

  if($idprod){
    //Omitimos los procesos de impresión si el tipo es liso
    if($_POST['tipo'] != "I"){
      $this->model->insertarprocesosliso($id,$idprod,$_POST['solicitud'],$tiposoli,$_POST['encargado']);
    } else{
      $this->model->insertarprocesos($id,$idprod,$_POST['solicitud'],$tiposoli,$_POST['encargado']);
    }
  }

  redirect('?modulo=ordenprod&accion=show&id='.$id);
}



function update(){
  $tipoimagen = "";
  if(!isset($_POST['timagen'])){
    $carpetaDestino = 'ordenesproduccion/'; // Reemplaza con la ruta donde deseas guardar el archivo
    $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato predeterminado
    $fechaHoraSinGuionesNiPuntos = str_replace(array("-", ":", " "), "", $fechaHora);
  
    if (isset($_FILES['adj']['name'])) {
      $nombre_archivo = $_FILES['adj']['name'];
      $tamano_archivo = $_FILES['adj']['size'];
      if($tamano_archivo > 3000000) $error = "6XMUE2";
      else{
        $adj = $carpetaDestino . $fechaHoraSinGuionesNiPuntos . $nombre_archivo;
        if (move_uploaded_file($_FILES['adj']['tmp_name'], 'img/'.$adj)){
          $error= 0;
          $tipoimagen = "S";
        } else {
          $adj = "NULL";
          $error = "6XMUE4";
        }
      }
    }else{
      $adj = "NULL";
      $error = 1;
    }
  } else{
    $adj = $_POST['rutaimg'];
    $tipoimagen = "E";
  }
  $id = $_REQUEST['id'];

  $this->model->setdata($id, $_POST['folio'], $_POST['articulo'], $_POST['nmb'], $_POST['modelo'], 
                        $_POST['cantidad'], $_POST['tipo'], $_POST['texto'], date('Y-m-d H:i:s'), $_SESSION['uid'], 
                        "N", '', $_POST['encargado'], $_POST['cortador'], $_POST['fentrega'], 
                        $_POST['notasalida'], $_POST['almacen'], $_POST['forecast'], '', '', 
                        '', $_POST['obstiposoli'],$_POST['solicitud'], $adj, $_POST['almacendestino'], $tipoimagen);

  $this->model->update();

  $sql = 'UPDATE pr_forecastdorden SET pfo_cantidad = "'.$_POST['cantidad'].'" WHERE pfo_ordenprod = "'.$id.'"';
  setq($sql);

  redirect('?modulo=ordenprod&accion=show&id='.$id);
}


function activarop(){
  $id= $_GET['id'];
  $almacen = busca($id,'pr_ordenprod','po_id','po_almacen');
  $forecast = busca($id,'pr_ordenprod','po_id','po_forecast');
  $suma = array();
  $sumaoriginal = array();
  $materiap = array();
  $sql = 'UPDATE pr_ordenprod SET po_estatus = "P", po_obsinicio = "'.$_POST['obsinicio'].'" WHERE po_id ="'.$id.'"';
  setq($sql);
  $sqlsel = 'SELECT po_articulo, po_modelo, po_cantidad, po_almacen FROM pr_ordenprod WHERE po_id = "'.$id.'"';
  $resultsel = setq($sqlsel);
  list($articulo, $modelo, $cantidad, $almacen) = $resultsel -> fetch_array();
  if($articulo != "0"){
    $sqlmp = 'SELECT ac_mp AS materiaprima, ac_cantidad AS cantidad, ac_largo AS largo, ac_ancho AS ancho, ac_alto AS alto FROM articulos_composicion WHERE ac_articulo = "'.$articulo.'" AND ac_modelo = "'.$modelo.'"';
    $sqlmp2 = 'SELECT ac_mp AS materiaprima FROM articulos_composicion WHERE ac_articulo = "'.$articulo.'" AND ac_modelo = "'.$modelo.'" GROUP BY ac_mp';
  } else {
    $sqlmp = 'SELECT pom_materiaprima AS materiaprima, pom_cantidad AS cantidad, pom_largo AS largo, pom_ancho AS ancho, pom_alto AS alto FROM pr_ordenmateriaprima WHERE pom_ordenprod = "'.$id.'"';
    $sqlmp2 = 'SELECT pom_materiaprima AS materiaprima FROM pr_ordenmateriaprima WHERE pom_ordenprod = "'.$id.'" GROUP BY pom_materiaprima';
  }
  $resultmp = setq($sqlmp);
  $i = 0;
  while($rowmp = $resultmp -> fetch_array()){
    $tipo = busca($rowmp['materiaprima'], 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
    /* if(!$suma[$rowmp['materiaprima']]) $suma[$rowmp['ac_mp']] = 0; */
    $cantidadrow = $rowmp['cantidad'];
    if($tipo == "A"){
      $cantmp = $cantidad * $cantidadrow*$rowmp['largo']*$rowmp['ancho'];
      $sumaoriginal[$rowmp['materiaprima']] += $cantmp;
    } else if($tipo == "V"){
      $cantmp = $cantidad * $cantidadrow*$rowmp['largo']*$rowmp['ancho']*$rowmp['alto'];
      $sumaoriginal[$rowmp['materiaprima']] += $cantmp;
    } else if($tipo == "P"){
      $cantmp = $cantidad * $cantidadrow;
      $sumaoriginal[$rowmp['materiaprima']] += $cantmp;
    }
  }

  foreach ($sumaoriginal as $materiaprima => $valor) {
  //Buscamos a ver si encontramos merma seleccionada para usar en esta orden de producción
  $sqlamu = 'SELECT amu_cantidadgeneral, amu_cantidad, amu_usados, amu_id, amu_ammp, amu_largo, amu_alto, amu_ancho, amu_ordenp FROM articulos_mermas_usados WHERE amu_ammp = "'.$materiaprima.'" AND amu_estatus = "N" AND amu_ordenp = "'.$id.'"';
  /* $sqlamu = 'SELECT amu_cantidad, amu_usados, amu_id, amu_ammp, amu_largo, amu_alto, amu_ancho, amu_ordenp FROM articulos_mermas_usados WHERE amu_alto = "'.$rowmp['alto'].'" AND amu_largo = "'.$rowmp['largo'].'" AND amu_ancho = "'.$rowmp['ancho'].'" AND amu_ammp = "'.$rowmp['materiaprima'].'" AND amu_estatus = "N"'; */
  $resultamu = setq($sqlamu);

  if($resultamu->num_rows == 0){
    $suma[$materiaprima] += $valor;
    ajustaexistenciamp($materiaprima, $valor, $almacen, "S");
  } else{
  while($rowamu = $resultamu->fetch_array()){
  $banderae = false;
  $generalcantidad = $rowamu['amu_cantidadgeneral'];
  $cantidadamu = $rowamu['amu_cantidad'];
  $usadosamu = $rowamu['amu_usados'];
  $amuid = $rowamu['amu_id'];
  $amump = $rowamu['amu_ammp'];

  $largo = $rowamu['amu_largo'];
  $ancho = $rowamu['amu_ancho'];
  $alto = $rowamu['amu_alto'];
  $ordenp = $rowamu['amu_ordenp'];

  $cantidaddos = $generalcantidad; //Cantidad de las mermas seleccionadas
  $cantidadg = $valor; //Cantidad requerida de la materia prima
  
  if($cantidadg > 0){
    if($cantidadg > $cantidaddos){
      //Cuando la cantidad requerida es mayor a la de las mermas seleccionadas
      $usados = $cantidadamu;  
      $sqlf = '';
    } else if($cantidaddos == $cantidadg){
      //Cuando la cantidad requerida es igual a la de las mermas seleccionadas
      $usados = $cantidadamu;
      $sqlf = '';
    } else {
      //Cuando la cantidad requerida es menor a la de las mermas seleccionadas
      $usados = $cantidadg;
      //bandera de eliminación
      $banderae = true;
/*       if($amuid == 22){
        die("Cantidados: ".$cantidaddos." < cantidadg: ".$cantidadg);        
      } */
    }

    $tipomp = busca($amump, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
    if(!$banderae){
      if($tipomp == "P"){ //Pieza
        $cant = $usados;
      } else if($tipomp == "A"){ //Área
        $cant = (floatval($largo) * floatval($ancho)) * floatval($usados);
      } else{
        $cant = (floatval($largo) * floatval($ancho) * floatval($alto)) * floatval($usados);
      }
    } else{
      if($tipomp == "P"){ //Pieza
        $cant = $usados;
      } else if($tipomp == "A"){ //Área
        $usados = (int)(floatval($usados) / (floatval($largo) * floatval($ancho)));
        if($usados == 0){
          $usados = 1;
        }
        $cant = $usados * (floatval($largo) * floatval($ancho));
      } else{                
        $usados = (int)(floatval($usados) / (floatval($largo) * floatval($ancho) * floatval($alto)));
        if($usados == 0){
          $usados = 1;
        }
        $cant = $usados * (floatval($largo) * floatval($ancho) * floatval($alto));
      }
      $sqlf = ' amu_cantidad = "'.$usados.'", ';
    }
      $suma[$materiaprima] += $cant;
      $valordos = $valor;
      $valor = $valordos - $cant;

      $sqlupd = 'UPDATE articulos_mermas_usados SET '.$sqlf.'amu_usados = "'.$usados.'", amu_estatus = "F", amu_cantidadgeneral = "'.$cant.'" WHERE amu_id = "'.$amuid.'"';
      setq($sqlupd);

      $materiap[$i][0] = $amump;
      $materiap[$i][1] = $ordenp;
      $materiap[$i][2] = $amuid;
      $i++;

    }
    }
  }
}

/* die(var_dump($suma)); */
  $resultmp2 = setq($sqlmp2);
  while($rowmp2 = $resultmp2 -> fetch_array()){
    $cantidadrestar = (floatval($sumaoriginal[$rowmp2['materiaprima']]) - floatval($suma[$rowmp2['materiaprima']]));
    if($cantidadrestar < 0){
      $cantidadrestar = floatval($sumaoriginal[$rowmp2['materiaprima']]);
      /* $cantidadrestar = floatval($suma[$rowmp2['materiaprima']]); */
    }

    if(floatval($sumaoriginal[$rowmp2['materiaprima']]) < 0){
      $cantidadoficial = 0;
    } else{
      $cantidadoficial = $sumaoriginal[$rowmp2['materiaprima']];
    }
    ajustaexistenciamp($rowmp2['materiaprima'], $cantidadrestar, $almacen, "S");
    $this->model->existenciaapartado($rowmp2['materiaprima'],$cantidadoficial, $sumaoriginal[$rowmp2['materiaprima']], $almacen, $forecast, $id); 
  }

  for($j = 0; $j < count($materiap); $j++){
    $amump = $materiap[$j][0];
    $ordenp = $materiap[$j][1];
    $amuid = $materiap[$j][2];

    $this->model->updexistenciaapartadousados($amump, $ordenp, $amuid);
  }

  $sql = 'SELECT pp_id FROM pr_procesosop WHERE pp_ordenp = "'.$id.'" ORDER BY pp_orden ASC LIMIT 0,1';
  $result = setq($sql);
  list($idproceso) = $result->fetch_array();
  $sqlup = 'UPDATE pr_procesosop SET pp_estatus = "P" WHERE pp_ordenp = "'.$id.'" AND pp_id = "'.$idproceso.'" ';  //POSIBLE UPDATE AL PROCESO
  setq($sqlup);
  //redirect('?modulo=ordenprod&accion=show&id='.$id);
  //redirect('?modulo=temporizadorpps&accion=index&op='.$id.'&proceso='.$idproceso.'');
  $nmbart = busca($id,'pr_ordenprod','po_id','po_nmbarticulo');
  $orden = busca($idproceso,'pr_procesosop','pp_id','pp_orden');
  $sql = 'SELECT pp_id FROM pr_procesosop WHERE pp_ordenp = "'.$id.'" AND pp_orden > '.$orden.' ORDER BY pp_orden ASC LIMIT 0,1 ';
  $result = setq($sql);
  list($siguiente) = $result->fetch_array();
  for ($i=0; $i < $cantidad; $i++) { 
    $cb = cbproduccion($forecast,$id);
    $sql = 'INSERT INTO pr_articulosop SET
            pra_cb = "'.$cb.'",
            pra_op = "'.$id.'",
            pra_nmb = "'.$nmbart.'",
            pra_articulo = "'.$articulo.'",
            pra_modelo = "'.$modelo.'",
            pra_pa = "'.$idproceso.'",
            pra_estpa = "N",
            pra_sp = "'.$siguiente.'",
            pra_estsp = "N" ';
    setq($sql);
  }

  redirect('?modulo=ordenprod&accion=show&id='.$id.'');
}

function insertproceso(){
  $ordenprod = $_REQUEST['id'];
  $tiposoli = busca($ordenprod, 'pr_ordenprod', 'po_id', 'po_tiposoli');
  $solicitud = busca($ordenprod, 'pr_ordenprod', 'po_id', 'po_solicitud');
  $supervisor = busca($ordenprod, 'pr_ordenprod', 'po_id', 'po_encargado');
  $sql = 'DELETE FROM pr_procesosop WHERE pp_ordenp = "'.$ordenprod.'" ';
  setq($sql);
  // AGREGAR AQUÍ EL LOG
  foreach ($_REQUEST['activo'] as $key => $value) {  
    $veri = busca($ordenprod, 'pr_procesosop', 'pp_proceso = "'.$key.'" AND pp_ordenp', 'COUNT(*)');
    $orden = $_REQUEST['orden'.$key];

    $sql = 'SELECT ppr_planta, ppr_fase FROM pr_procesos WHERE ppr_id = "'.$key.'" ';
    $result = setq($sql);
    list($planta,$fase) = $result->fetch_array();

    if($_REQUEST['obligatorio'.$key]) $obligatorio = '1';
    else $obligatorio = '0';

    if($_REQUEST['tipo'.$key]) $tipo = 'M';
    else $tipo = 'C';

    if(intval($veri) <= 0){
      $this->model->insertproceso($ordenprod, $fase, $key, $orden, $tipo, $obligatorio, $solicitud, $tiposoli, $supervisor);
    }else{
      $this->model->updateproceso($ordenprod, $fase, $key, $orden, $tipo, $obligatorio, $solicitud, $tiposoli, $supervisor);
    }
  }
  
  redirect('?modulo=ordenprod&accion=show&id='.$ordenprod);
}

function verarticulos(){  
  $id = $_GET['idp']; //Id del la orden de producción
  $proceso = $_GET['proceso']; //Id del la orden de producción
  $this->model->select($id);
  $this->model->resultartop($id, $proceso);
  $this->view = new viewlordenprod($this->model);
  $this->view->articulosop();
}

function verarticulosdinamico(){  
  $id = $_GET['idp']; //Id del la orden de producción
  $proceso = $_GET['proceso']; //Id del la orden de producción
  $this->model->select($id);
  $this->model->resultartop($id, $proceso);
  $this->view = new viewlordenprod($this->model);
  $response = $this->view->articulosopdinamico();
  return $response;
}

function cancelarorden(){
  $ordenp = $_GET['id'];
  $estatus = busca($ordenp, 'pr_ordenprod' ,'po_id', 'po_estatus'); 
  if($estatus == "N"){
    $sql = 'DELETE FROM articulos_mermas_usados WHERE amu_ordenp ="'.$ordenp.'"';
    setq($sql);
  }
  $this->model->cancelarorden($ordenp);

  redirect('?modulo=ordenprod&accion=show&id='. $ordenp);
}

function updatedproceso(){

  if(isset($_GET['tipo'])){
    $tipo = "&tipo=".$_GET['tipo'];
  } else{
    $tipo = '';
  }

  $this->model->updatedproceso();
  redirect('popup/procesomanual?op='.$_POST['ordenp'].'&proceso='.$_POST['proceso'].$tipo);
}

}

class modelordenprod{
function insertarprocesos($ordenp,$articulo,$solicitud,$tiposoli,$supervisor){
  $sql = 'SELECT * FROM articulos_produccion WHERE apr_articulo = "'.$articulo.'" ';
  $result = setq($sql);
  while($row = $result->fetch_array()){
    $sqlin = 'INSERT INTO pr_procesosop SET
              pp_ordenp = "'.$ordenp.'",
              pp_solicitud = "'.$solicitud.'",
              pp_tiposolicitud = "'.$tiposoli.'",
              pp_estatus = "N",
              pp_proceso = "'.$row['apr_proceso'].'",
              pp_fase = "'.$row['apr_fase'].'",
              pp_supervisor = "'.$supervisor.'",
              pp_orden = "'.$row['apr_orden'].'",
              pp_tipoproceso = "'.$row['apr_tipo'].'",
              pp_planeado = "1",
              pp_bloqueo = "'.$row['apr_obligatorio'].'",
              pp_obs = ""';
    setq($sqlin);
    
    /* pp_fini = "'.$fini.'",
    pp_ffin = "'.$ffin.'",
    pp_operador = "'.$operador.'",
    pp_cantini = "'.$cantini.'",
    pp_cantfini = "'.$cantfin.'", */

  }
}

function insertarprocesosliso($ordenp,$articulo,$solicitud,$tiposoli,$supervisor){
  $sql = 'SELECT * FROM articulos_produccion WHERE apr_articulo = "'.$articulo.'" ORDER BY apr_orden ASC ';
  $result = setq($sql);
  $i = 1;
  while($row = $result->fetch_array()){
    //Consultamos si el proceso es de impresión
    $pimpresion = busca($row['apr_proceso'], 'pr_procesos', 'ppr_id', 'ppr_tipo');
    if($pimpresion != "I"){
      $sqlin = 'INSERT INTO pr_procesosop SET
              pp_ordenp = "'.$ordenp.'",
              pp_solicitud = "'.$solicitud.'",
              pp_tiposolicitud = "'.$tiposoli.'",
              pp_estatus = "N",
              pp_proceso = "'.$row['apr_proceso'].'",
              pp_fase = "'.$row['apr_fase'].'",
              pp_supervisor = "'.$supervisor.'",
              pp_orden = "'.$i.'",
              pp_tipoproceso = "'.$row['apr_tipo'].'",
              pp_planeado = "1",
              pp_bloqueo = "'.$row['apr_obligatorio'].'",
              pp_obs = ""';
    setq($sqlin);
    $i++;
    }
  }
}
function resultprocesos($ordenp){
  $sql = 'SELECT * FROM pr_procesosop INNER JOIN pr_fases ON pp_fase = pf_id WHERE pp_ordenp = "'.$ordenp.'" GROUP BY pp_fase ORDER BY pp_orden'; 
  $this->result = setq($sql);
}

function resultprocesosmp(){
  $sql = 'SELECT pmp_id, pmp_nmb, pmp_material, pmp_tipo, ac_largo, ac_ancho, ac_alto FROM articulos_composicion INNER JOIN pr_materiaprima ON pmp_id = ac_mp WHERE ac_articulo = "'.$this->articulo.'" AND ac_modelo = "'.$this->modelo.'" GROUP BY ac_mp, ac_largo, ac_ancho, ac_alto ORDER BY ac_id;';
  $this->result = setq($sql);
}

function result($nmb,$estatus,$fini, $ffin, $forecast){ //filtro
  $bloque = 50;
  $sql = 'SELECT * FROM pr_ordenprod  WHERE po_fentrega >= "'.$fini.'" AND po_fentrega <= "'.$ffin.'"';
  if($nmb) $sql.= ' AND po_folio LIKE "%'.trim($nmb).'%"';
  if($estatus) $sql.= ' AND po_estatus = "'.$estatus.'"';
  
  if($forecast) {
    $sql.= ' AND po_id IN (SELECT pfo_ordenprod FROM pr_forecastdorden WHERE pfo_forecastd IN (SELECT prfd_id FROM pr_forecastd INNER JOIN pr_forecast ON prf_id = prfd_forecast WHERE prf_nmb LIKE "%'.trim($forecast).'%"))';
  }
  $sql.=' ORDER BY po_folio ASC ';
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}

function setmermas($ordenp){
  $ammp = $_POST['ammp'];
  if(isset($_SESSION['uid'])){
    $tuser = 'U';
    $operador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else{
    $tuser = 'O';
    $operador = $_SESSION['eid'];
  }
  // Recibe los datos del formulario
  foreach ($_POST as $key => $value) {
    if (preg_match('/^back(\d+)$/', $key, $matchesb)) {      
        $cantidad = $_POST['cantidadd'.$matchesb[1]];
        $ammp = $_POST['ammp'.$matchesb[1]];
        $largo = $_POST['largo'.$matchesb[1]];
        $ancho = $_POST['ancho'.$matchesb[1]];
        $alto = $_POST['alto'.$matchesb[1]];
      if (isset($_POST['check'.$matchesb[1]])) {        
        $tipomp = busca($ammp, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
        if($tipomp == "P"){ //Pieza
          $cant = $cantidad;
        } else if($tipomp == "A"){ //Área
          $cant = (floatval($largo) * floatval($ancho)) * floatval($cantidad);
        } else{
          $cant = (floatval($largo) * floatval($ancho) * floatval($alto)) * floatval($cantidad);
        }

        $sql = 'INSERT INTO articulos_mermas_usados
                SET amu_ammp = "'.$ammp.'",
                    amu_largo = "'.$largo.'",
                    amu_ancho = "'.$ancho.'",
                    amu_alto = "'.$alto.'",
                    amu_cantidad = "'.$cantidad.'",
                    amu_cantidadgeneral = "'.$cant.'",
                    amu_ordenp = "'.$ordenp.'",
                    amu_fecha = "'.date("Y-m-d H:i:s").'",
                    amu_operador = "'.$operador.'",
                    amu_tuser = "'.$tuser.'",
                    amu_estatus = "N"
                ON DUPLICATE KEY UPDATE
                    amu_cantidad = "'.$cantidad.'",
                    amu_cantidadgeneral = "'.$cant.'"
                    ';
        setq($sql);
      } else{
        $sqlupd = 'DELETE FROM articulos_mermas_usados WHERE 
        amu_ammp = "'.$ammp.'" AND
        amu_largo = "'.$largo.'" AND
        amu_ancho = "'.$ancho.'" AND
        amu_alto = "'.$alto.'" AND
        amu_ordenp = "'.$ordenp.'"';
        setq($sqlupd); 
      }
    }
  }
}

function insertmerma($ordenp){
  /* foreachdie(); */

  foreach ($_POST as $key => $value) {
    if (preg_match('/^back(\d+)$/', $key, $matches)) {
      $cadena = $_POST['back'.$matches[1]];
      $arreglo = explode(",", $cadena);
      $largo = $arreglo[0];
      $ancho = $arreglo[1];
      $alto = $arreglo[2];
      $mp = $arreglo[3];
      if (isset($_POST['check'.$matches[1]])) {
          $cantidad = $_POST['cantidadd'.$matches[1]];
  
          $tipomp = busca($mp, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
          if($tipomp == "P"){ //Pieza
            $cant = $cantidad;
          } else if($tipomp == "A"){ //Área
            $cant = (floatval($largo) * floatval($ancho)) * floatval($cantidad);
          } else{
            $cant = (floatval($largo) * floatval($ancho) * floatval($alto)) * floatval($cantidad);
          }
  
          $sql = 'INSERT INTO articulos_mermas 
          SET am_ordenp = "'.$ordenp.'",
              am_mp = "'.$mp.'",
              am_largo = "'.$largo.'",
              am_ancho = "'.$ancho.'",
              am_alto = "'.$alto.'",
              am_cantidad = "'.$cantidad.'",
              am_cantidadgeneral = "'.$cant.'",
              am_estatus = "N"
          ON DUPLICATE KEY UPDATE
              am_cantidad = "'.$cantidad.'"';
          setq($sql);
  
          /* $this->updexistenciaapartado($mp, $ordenp); */
      }else{
        $cantidad = busca($mp, 'articulos_mermas', 'am_mp', 'am_cantidad');
        $cant = $_POST['cantidadoriginal'.$matches[1]];
        $cantidadg = $cantidad - $cant;
        if($cantidadg > 0){
          $sqlupd = 'UPDATE articulos_mermas SET 
          WHERE am_mp = "'.$mp.'" AND
                am_largo = "'.$largo.'" AND
                am_ancho = "'.$ancho.'" AND
                am_alto = "'.$alto.'" AND
                am_ordenp = "'.$ordenp.'"';
        } else{
          $sqlupd = 'DELETE FROM articulos_mermas WHERE 
                        am_mp = "'.$mp.'" AND
                        am_largo = "'.$largo.'" AND
                        am_ancho = "'.$ancho.'" AND
                        am_alto = "'.$alto.'" AND
                        am_ordenp = "'.$ordenp.'"';
        }
        setq($sqlupd); 
        /* $this->updexistenciaapartado($mp, $ordenp); */
      }
      $this->updexistenciaapartado($mp, $ordenp);
  
  }
  }
}

function updatemerma($amid, $ordenp, $cantidad){

  $sqlm = 'SELECT * FROM articulos_mermas WHERE am_id = "'.$amid.'"';
  $result = setq($sqlm);
  $row = $result->fetch_array();
  $mp = $row['am_mp'];
  $largo = $row['am_largo'];
  $ancho = $row['am_ancho'];
  $alto = $row['am_alto'];

  $tipomp = busca($mp, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
  if($tipomp == "P"){ //Pieza
    $cant = $cantidad;
  } else if($tipomp == "A"){ //Área
    $cant = (floatval($largo) * floatval($ancho)) * floatval($cantidad);
  } else{
    $cant = (floatval($largo) * floatval($ancho) * floatval($alto)) * floatval($cantidad);
  }

  $sql = 'UPDATE articulos_mermas 
  SET am_cantidad = "'.$cantidad.'", am_cantidadgeneral = "'.$cant.'" WHERE am_id = "'.$amid.'" AND am_ordenp = "'.$ordenp.'"';
  setq($sql);

  $this->updexistenciaapartado($mp, $ordenp);
}

function deletemerma($amid, $ordenp){

  $sqlm = 'SELECT * FROM articulos_mermas WHERE am_id = "'.$amid.'"';
  $result = setq($sqlm);
  $row = $result->fetch_array();
  $mp = $row['am_mp'];

  $this->updexistenciaapartado($mp, $ordenp);

  $sql = 'DELETE FROM articulos_mermas WHERE am_id = "'.$amid.'" AND am_ordenp = "'.$ordenp.'"';
  setq($sql);
}

function select($id){
  $sql = 'SELECT * FROM pr_ordenprod WHERE po_id = "'.$id.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $this->id = $row['po_id'];
  $this->folio = $row['po_folio'];
  $this->articulo = $row['po_articulo'];
  $this->modelo = $row['po_modelo'];
  $this->nmbarticulo = $row['po_nmbarticulo'];
  $this->cantidad = $row['po_cantidad'];
  $this->tipo = $row['po_tipo'];
  $this->obstipo = $row['po_obstipo'];
  $this->fgen = $row['po_fgen'];
  $this->ugen = $row['po_ugen'];
  $this->estatus = $row['po_estatus'];
  $this->finventario = $row['po_finventario'];
  $this->encargado = $row['po_encargado'];
  $this->cortador = $row['po_cortador'];
  $this->fentrega = $row['po_fentrega'];
  $this->notasalida = $row['po_notasalida'];
  $this->almacen = $row['po_almacen'];
  $this->forecast = $row['po_forecast'];
  $this->tipoorden = $row['po_tipoorden'];
  $this->obsinicio = $row['po_obsinicio'];
  $this->tiposoli = $row['po_tiposoli'];
  $this->obstiposoli = $row['po_obstiposoli'];
  $this->solicitud = $row['po_solicitud'];
}

function setdata($id,$folio, $articulo, $nmbarticulo, $modelo, $cantidad, $tipo, $obstipo, $fgen, $ugen, $estatus, $finventario, $encargado, $cortador, $fentrega, $notasalida, $almacen, $forecast, $tipoorden, $obsinicio, $tiposoli, $obstiposoli,$solicitud, $adj, $almacendestino, $tipoimagen){
  mb_internal_encoding("UTF-8");
  $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
  $cambio = "";
  $this->id = $id;
  $this->folio = $folio;
  $this->articulo = $articulo;
  $this->modelo = $modelo;
  $this->nmbarticulo = $nmbarticulo;
  $this->cantidad = $cantidad;
  $this->tipo = $tipo;
  $this->obstipo = $obstipo;
  $this->fgen = $fgen;
  $this->ugen = $ugen;
  $this->estatus = $estatus;
  $this->finventario = $finventario;
  $this->encargado = $encargado;
  $this->cortador = $cortador;
  $this->fentrega = $fentrega;
  $this->notasalida = $notasalida;
  $this->almacen = $almacen;
  $this->forecast = $forecast;
  $this->tipoorden = $tipoorden;
  $this->obsinicio = $obsinicio;
  $this->tiposoli = $tiposoli;
  $this->obstiposoli = $obstiposoli;
  $this->solicitud = $solicitud;
  $this->adj = $adj;
  $this->tipoimagen = $tipoimagen;
  $this->almacendestino = $almacendestino;    
}

function setdatamerma($ordenp,$mp, $cantidad, $largo, $alto, $ancho)
{  
  $this->ordenp = $ordenp;
  $this->mp = $mp;
  $this->cantidad = $cantidad;
  $this->largo = $largo;
  $this->alto = $alto;
  $this->ancho = $ancho;
}

function insert(){
  if($this->adj == "NULL"){
    $sqlf = 'po_adj = NULL';
  } else{
    $sqlf = 'po_adj = "'.$this->adj.'"';
  }
  $sql = 'INSERT INTO pr_ordenprod SET
          po_folio = "'.$this->folio.'",
          po_articulo = "'.$this->articulo.'",
          po_nmbarticulo = "'.$this->nmbarticulo.'",
          po_modelo = "'.$this->modelo.'",
          po_cantidad = "'.$this->cantidad.'",
          po_tipo = "'.$this->tipo.'",
          po_obstipo = "'.$this->obstipo.'",
          po_fgen = "'.$this->fgen.'",
          po_ugen = "'.$this->ugen.'",
          po_estatus = "'.$this->estatus.'",
          po_finventario = "'.$this->finventario.'",
          po_encargado = "'.$this->encargado.'",
          po_cortador = "'.$this->cortador.'",
          po_fentrega = "'.$this->fentrega.'",
          po_almacen = "'.$this->almacen.'",
          po_notasalida = "'.$this->notasalida.'",
          po_forecast = "'.$this->forecast.'",
          po_tipoorden = "'.$this->tipoorden.'",
          po_obsinicio = "'.$this->obsinicio.'",
          po_tiposoli = "'.$this->tiposoli.'",
          po_obstiposoli = "'.$this->obstiposoli.'",
          po_almacendestino = "'.$this->almacendestino.'",
          po_tipoimagen = "'.$this->tipoimagen.'",
          po_solicitud = "'.$this->solicitud.'",'.$sqlf;
  setq($sql);

  $this->model->id = getmax('po_id', 'pr_ordenprod', false, false);
}
function update(){
  $sql = 'UPDATE pr_ordenprod SET
        po_cantidad = "'.$this->cantidad.'",
        po_tipo = "'.$this->tipo.'",
        po_obstipo = "'.$this->obstipo.'",
        po_estatus = "'.$this->estatus.'",
        po_finventario = "'.$this->finventario.'",
        po_encargado = "'.$this->encargado.'",
        po_cortador = "'.$this->cortador.'",
        po_fentrega = "'.$this->fentrega.'",
        po_almacen = "'.$this->almacen.'",
        po_notasalida = "'.$this->notasalida.'",
        po_adj = "'.$this->adj.'",
        po_tipoimagen = "'.$this->tipoimagen.'",
        po_almacendestino = "'.$this->almacendestino.'"
        WHERE po_id = "'.$this->id.'"';
  setq($sql);
}

function ligar($orden, $forecastd, $cantidad){
  $sql = 'INSERT INTO pr_forecastdorden SET pfo_forecastd = "'.$forecastd.'", pfo_ordenprod = "'.$orden.'", pfo_cantidad = "'.$cantidad.'"';
  setq($sql);
}

function comprueba($idop){
  $datos = array();
  $suma = array();
  $datos['error'][0] = 'OK';
  $sqlop = 'SELECT po_articulo, po_modelo, po_cantidad, po_almacen FROM pr_ordenprod WHERE po_id = "'.$idop.'"';
  $resultop = setq($sqlop);
  list($articulo, $modelo, $cantidad, $almacen) = $resultop -> fetch_array();
  if($articulo != "0"){
    $sqlmp = 'SELECT * FROM articulos_composicion WHERE ac_articulo = "'.$articulo.'" AND ac_modelo = "'.$modelo.'"';
    $resultmp = setq($sqlmp);
    if($resultmp -> num_rows > 0){
      while($rowmp = $resultmp -> fetch_array()){
        $tipo = busca($rowmp['ac_mp'], 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
        if(!$suma[$rowmp['ac_mp']]) $suma[$rowmp['ac_mp']] = 0;
        if($tipo == "A"){
          $suma[$rowmp['ac_mp']] += ($cantidad*$rowmp['ac_cantidad']*$rowmp['ac_largo']*$rowmp['ac_ancho']);
        } else if($tipo == "V"){
          $suma[$rowmp['ac_mp']] += ($cantidad*$rowmp['ac_cantidad']*$rowmp['ac_largo']*$rowmp['ac_ancho']*$rowmp['ac_alto']);
        }if($tipo == "P"){
          $suma[$rowmp['ac_mp']] += ($cantidad*$rowmp['ac_cantidad']);
        }
      }
      $i=0;
      $sqlmp = 'SELECT * FROM articulos_composicion WHERE ac_articulo = "'.$articulo.'" AND ac_modelo = "'.$modelo.'" GROUP BY ac_mp';
      $resultmp = setq($sqlmp);
      while($rowmp = $resultmp -> fetch_array()){
        if(!$suma[$rowmp['ac_mp']]) $suma[$rowmp['ac_mp']] = 0;
        $existencia = existenciamp($rowmp['ac_mp'], $almacen);  
        if($existencia < $suma[$rowmp['ac_mp']]){
          $nmb = busca($rowmp['ac_mp'], 'pr_materiaprima', 'pmp_id', 'pmp_nmb');
          $tipo = busca($rowmp['ac_mp'], 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
          $tipou = array('A'=>'mts cuadrados','V'=>'mts cúbicos','P'=>'unidades');
          $datos['error'][0] = '2';
          $datos['msj'][0] = 'Los siguientes artículos se encuentran sin existencias en almacén';
          $datos['mp'][$i] = $nmb;
          $datos['exi'][$i] = number_format($existencia, 2).' '.$tipou[$tipo];    
          $datos['esp'][$i] = number_format($suma[$rowmp['ac_mp']], 2).' '.$tipou[$tipo];
          $i++;
        }
      }
    } else {
      $datos['error'][0] = '1';
      $datos['msj'][0] = 'No se ha configurado la composición de este artículo';
    }
  } else {
    $procesos = busca($idop, 'pr_procesosop', 'pp_ordenp', 'COUNT(*)');
    if($procesos <= 0){
      $datos['error'][0] = '1';
      $datos['msj'][0] = 'No se han configurado los procesos de producción.';
    }
  }
  return $datos;
}

function existenciaapartado($mp, $cantidad,$cantidadoriginal, $almacen, $forecast, $idordenprod){
  $sqlins = 'INSERT INTO pr_existencias_apartado SET pea_materiaprima = "'.$mp.'", 
                                                    pea_cantidadoriginal = "'.$cantidadoriginal.'", 
                                                    pea_cantidad = "'.$cantidad.'",
                                                    pea_almacen = "'.$almacen.'",
                                                    pea_fingreso = "'.date('Y-m-d H:i:s').'",
                                                    pea_forecast = "'.$forecast.'",
                                                    pea_ordenprod = "'.$idordenprod.'"
                                                    ';
                                                          
  setq($sqlins);                                                                         
    
}

function updexistenciaapartado($mp, $idordenprod){
  $cantidad = floatval(busca($mp, 'articulos_mermas', 'am_ordenp = "'.$idordenprod.'" AND am_mp', 'SUM(am_cantidadgeneral)'));
  $cantidadoriginal = floatval(busca($mp, 'pr_existencias_apartado', 'pea_ordenprod = "'.$idordenprod.'" AND pea_materiaprima', 'pea_cantidadoriginal'));
  $cant = $cantidadoriginal - $cantidad;
  $sqlupd = 'UPDATE pr_existencias_apartado SET pea_cantidad = "'.$cant.'"
                                                WHERE
                                                pea_materiaprima = "'.$mp.'" AND pea_ordenprod = "'.$idordenprod.'"
                                                ';                                            
  setq($sqlupd);  
}

function updexistenciaapartadousados($mp, $idordenprod, $amuid){
  $cantidad = floatval(busca($mp, 'articulos_mermas_usados', 'amu_id = "'.$amuid.'" AND amu_ordenp = "'.$idordenprod.'" AND amu_ammp', 'amu_cantidadgeneral'));
  /* $cantidadoriginal = floatval(busca($mp, 'pr_existencias_apartado', 'pea_ordenprod = "'.$idordenprod.'" AND pea_materiaprima', 'pea_cantidadoriginal')); */
  $cantidadoriginal = floatval(busca($mp, 'pr_existencias_apartado', 'pea_ordenprod = "'.$idordenprod.'" AND pea_materiaprima', 'pea_cantidad'));
  $cant = $cantidadoriginal - $cantidad;
  if($cant < 0){
    $cant = 0;
  }
  /* die("cantidadoriginal: ".$cantidadoriginal." - cantidad: ".$cantidad); */
  $sqlupd = 'UPDATE pr_existencias_apartado SET pea_cantidad = "'.$cant.'"
                                                WHERE
                                                pea_materiaprima = "'.$mp.'" AND pea_ordenprod = "'.$idordenprod.'"
                                                ';                                            
  setq($sqlupd);

  return $cant;
}

function insertproceso($ordenp, $fase, $proceso, $orden, $tipo, $obligatorio, $solicitud, $tiposoli, $supervisor){
  $sql = 'INSERT INTO pr_procesosop SET pp_ordenp = "'.$ordenp.'",
                                        pp_solicitud = "'.$solicitud.'",
                                        pp_tiposolicitud = "'.$tiposoli.'",
                                        pp_estatus = "N",
                                        pp_proceso = "'.$proceso.'",
                                        pp_fase = "'.$fase.'",
                                        pp_supervisor = "'.$supervisor.'",
                                        pp_orden = "'.$orden.'",
                                        pp_tipoproceso = "'.$tipo.'",
                                        pp_planeado = "1",
                                        pp_bloqueo = "'.$obligatorio.'",
                                        pp_obs = ""';
  $result = setq($sql);  
}
function updateproceso($ordenp, $fase, $proceso, $orden, $tipo, $obligatorio, $solicitud, $tiposoli, $supervisor){
  $sql = 'UPDATE pr_procesosop SET pp_solicitud = "'.$solicitud.'",
                                  pp_tiposolicitud = "'.$tiposoli.'",
                                  pp_estatus = "N",
                                  pp_proceso = "'.$proceso.'",
                                  pp_fase = "'.$fase.'",
                                  pp_supervisor = "'.$supervisor.'",
                                  pp_orden = "'.$orden.'",
                                  pp_tipoproceso = "'.$tipo.'",
                                  pp_planeado = "1",
                                  pp_bloqueo = "'.$obligatorio.'",
                                  pp_obs = ""
          WHERE pp_ordenp = "'.$ordenp.'" AND pp_proceso = "'.$proceso.'" ';
  setq($sql);
}

function resultartop($ordenp, $proceso){
  if(isset($_GET['tipo'])){
    if($_GET['tipo'] != "Z"){
      if($_GET['tipo'] == "I"){
        $estatus = '"N","A"';
      } else if($_GET['tipo'] == "P"){
        $estatus = '"A"';
      } else{
        $estatus = '"A","F"';
      }
    } else{
      $estatus = '"N"';
    }
    $sqlf = ' AND pra_estpa IN ('.$estatus.')';
  } else{
    $sqlf = '';
  }
  $sql = 'SELECT * FROM pr_articulosop WHERE pra_op = "'.$ordenp.'" AND pra_pa = "'.$proceso.'"'.$sqlf;
  $this->result = setq($sql);

}

function cancelarorden($id){
  $sql = 'UPDATE pr_ordenprod SEt po_estatus = "C" WHERE po_id = "'.$id.'"'; 
  setq($sql);


  $sqlupd = 'UPDATE articulos_mermas SET am_estatus = "A" WHERE am_ordenp = "'.$id.'"';
  setq($sqlupd);
}

function updatedproceso(){
  $proceso = $_POST['proceso'];
  $ordenp = $_POST['ordenp'];
  $obs = $_POST['obs'];

  $carpetaDestino = 'img/procesos/'; // Reemplaza con la ruta donde deseas guardar el archivo
  $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato predeterminado
  $fechaHoraSinGuionesNiPuntos = str_replace(array("-", ":", " "), "", $fechaHora);

  if (isset($_FILES['adj']['name'])) {
    $nombre_archivo = $_FILES['adj']['name'];
    $tamano_archivo = $_FILES['adj']['size'];
    if($tamano_archivo > 3000000) $error = "6XMUE2";
    else{
        $adj = $carpetaDestino . $fechaHoraSinGuionesNiPuntos . $nombre_archivo;
        if (move_uploaded_file($_FILES['adj']['tmp_name'], $adj)){
          $sqlf = 'pp_adj = "'."../".$adj.'"';
          $error= 0;
        } else {
          $sqlf = 'pp_adj = NULL';
          $error = "6XMUE4";
        }
    }
  }else{
    $error = 1;
  }

  if($error == 0){
    $sql = 'UPDATE pr_procesosop SEt '.$sqlf.', pp_obs = "'.$obs.'" WHERE pp_id = "'.$proceso.'" AND pp_ordenp = "'.$ordenp.'"'; 
    setq($sql);
  } else{
    die($error);
  }
}
}

class viewlordenprod {
    var $model;

function __construct($model) {

    $this->model = $model;
    $this->aest = array("N" => "Nueva","P" => "En ejecución", "F" => "Finalizado", "C" => "Cancelada");
    $this->fest = array("N" => "bg-warning","P" => "bg-primary", "F" => "bg-success", "C" => "bg-danger");
?>
<script language="JavaScript">
function checkSubmit() {
    console.log("entro");
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
function checksubmit() {
    console.log("entro");
    //document.getElementById("xg").value = "JD";
    document.getElementById("btnSave").setAttribute("disabled", "disabled");
    return true;
}
</script>
<?php
}
function browse($folio,$estatus,$fini, $ffin, $forecast) {
   //Sección: E1 Encabezado - Botones de acción
    if(!isset($estatus) || $estatus == "") $estt = "selected";
    elseif($estatus == "N") $estn = "selected";
    elseif($estatus == "P") $estp = "selected";
    elseif($estatus == "P") $estf = "selected";
    else $estc = "selected";

    //Sección: E2 Encabezado - Filtros
    ?>
    <script>
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 78) {
          var btn = document.getElementById("nuevo");
          btn.click();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 76) {
          var btn = document.getElementById("filtrar");
          btn.click();
          $("#pref-search").focus();
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
    
      $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setordenprod.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>';
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      /* $boton = '<button class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Las categorias o familias de produtos, permiten realizar una división en el tratamiento y estadístico de los productos" data-trigger="click" >
        <i class="fas fa-question-circle"></i>
      </button>'; */
      
    $filtrar = '
      <form class="form-inline" role="form" method="post" action="?modulo=ordenprod&accion=index" id="filtro">
        <div class="mb-5">
          <label for="">Filtrar por folio:</label>
          <input type="text" class="form-control" id="pref-search" name="folio" value="'.$folio.'" placeholder="Buscar por folio">
        </div>
        <div class="mb-5">
          <label for="">Filtrar por Forecast:</label>
          <input type="text" class="form-control" id="pref-search" name="forecast" value="'.$forecast.'" placeholder="Buscar por nombre del forecast">
        </div>
        <div class="mb-5">
          <label for="">Desde:</label>
          <input type="date" class="form-control" id="pref-search" name="fini" value="'.$fini.'" placeholder="Desde">
        </div>
        <div class="mb-5">
          <label for="">Hasta:</label>
          <input type="date" class="form-control" id="pref-search" name="ffin" value="'.$ffin.'" placeholder="Hasta">
        </div>
        <div class="mb-5">
          <label for="tipom">Estatus</label>
          <select class="form-control" name="estatus" id="estatus" >
            <option value="" '.$estt.'>Todos</option>
            <option value="N" '.$estn.'>Nuevas</option>
            <option value="A" '.$estp.'>En proceso</option>
            <option value="P" '.$estf.'>Finalizadas</option>
            <option value="C" '.$estc.'>Cancelados</option>
          </select>
        </div>
        <div class="mb-5">
          <label for="">Acciones:</label><br>
        <button type="submit" class="btn btn-info">
          <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
        </button>
        <a href="?modulo=ordenprod&accion=index"><button type="button" class="btn btn-warning">
          <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
        </button></a>
        </div>
      </form>';

      toolbar($_GET['modulo'],"", $filtrar, $nuevo);

  echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th width="13%">Folio</th>
                    <th width="22%">Articulo</th>
                    <th width="13%">Cantidad</th>
                    <th width="13%">Fecha de entrega</th>
                    <th width="13%">Encargado</th>
                    <th width="13%">Estatus</th>
                    <th width="13%"></th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
      $nmb = $row['po_nmbarticulo'];
      
        echo '<tr>
        <td>'.$row['po_folio'].'</td>
        <td>'.$nmb.'</td>
        <td>'.$row['po_cantidad'].'</td>
        <td>'.fecha_formato($row['po_fentrega'], false, false).'</td>
        <td>'.busca($row['po_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
        <td class="alert '.$this->fest[$row['po_estatus']].'">'.$this->aest[$row['po_estatus']].'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '
          <a href="?modulo=ordenprod&accion=show&id='.$row['po_id'].'">
            <button type="button" class="btn btn-info">
              <i class="fas fa-eye" style="color: #ffffff;"></i>
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
        });
      });
      </script>';
  
}

function show(){
    ?>
      <script>
        function confirmacionarranque(){
          Swal.fire({
            title: "<strong>Una vez iniciado no hay retorno de esta acción.<br> Al continuar iniciaras el primer proceso del articulo. <br> ¿Deseas continuar?</strong>",
            icon: "info",
            html: `
            <form action="?modulo=ordenprod&accion=activarop&id=<?php echo $this->model->id; ?>" method="POST" onsubmit="checkSubmit();">
              <div class="">
                <label>Observaciones</label>
                <textarea type="text" name="obsinicio" class="form-control" required></textarea>
              </div>
              <div>
                <button type="submit" class="btn btn-success" href="" id="guardar"><i class="fa fa-check"></i> Iniciar</button>
              </div>
            </form>
            `,
            showCloseButton: true,
            showCancelButton: false,
            showConfirmButton: false,
            focusConfirm: false,
            confirmButtonText: `
              <i class="fa fa-check"></i> Iniciar
            `,
            confirmButtonAriaLabel: "Thumbs up, great!",
            cancelButtonText: `
              <i class="fa fa-close"></i> Cancelar
            `,
            cancelButtonAriaLabel: ""
          });
        }
        function sincomposicion(){
          Swal.fire({
          icon: 'warning',
          title: '¿Deseas continuar?',
          showCancelButton: true,
          showConfirmButton: true,
          focusConfirm: false,
          confirmButtonText: `<i class="fas fa-check" style="color:#fff"></i> Iniciar`,
          cancelButtonText: `<i class="fas fa-times-circle" style="color:#fff"></i> Cancelar`,
          cancelButtonColor: '#f1416c',
          text: 'No se ha seleccionado la materia prima a utilizar en este artículo.'
          }).then((result) => {
            if (result.isConfirmed) {
              Swal.close();
              confirmacionarranque();
            } else {
              Swal.close();
            }
          });
        }
        
        $(document).ready(function() {
          $("#fbmateriap").fancybox({
            afterClose: function() {
              if(document.getElementById("banderacierre").value == "0"){
                location.reload(); // recarga la página después de cerrar el fancybox
              }              
            }
          });

        function recargarPagina(){
            location.reload(); // recarga la página después de cerrar el fancybox
        }

          $("#fbprocesos").fancybox({
            afterClose: function() {
              location.reload(); // recarga la página después de cerrar el fancybox
            }
          });
        });
        function cancelarord(estatus){
          var btnCerrar = document.getElementById("btnCerrar");
          if(estatus == "N"){
            Swal.fire({
          icon: 'warning',
          title: '¿Deseas cancelar esta orden de produccion?',
          showCancelButton: true,
          showConfirmButton: true,
          focusConfirm: false,
          confirmButtonText: `<i class="fas fa-check" style="color:#fff"></i> Confirmar`,
          cancelButtonText: `<i class="fas fa-times-circle" style="color:#fff"></i> Cerrar`,
          cancelButtonColor: '#6c757d',
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = '?modulo=ordenprod&accion=cancelarorden&id=<?php echo $this->model->id; ?>';
            } else {
              Swal.close();
            }
          });
          } else{
            btnCerrar.click();
          }
        }
      </script>
    <?php
    $leyenda = 'Mapa de procesos de la orden de producción '.$this->model->folio." ";
    $botones = '';
    $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setordenprod.php?ordenprod='.$this->model->id.'&rand='.rand().'" href="javascript:;">
      <button type="button" class="btn btn-secondary btn-sm"><i class="fa fa-pen"></i> Editar orden</button>
    </a>';


    $botones .= '
    <a hidden href="?modulo=ordenprod&accion=mermas&id='.$this->model->id.'">
      <button id="btnCerrar" type="button"></button>
    </a>
    ';

    if($this->model->estatus == "N"){
      //<a href="?modulo=ordenprod&accion=activarop&id='.$this->model->id.'"></a>
      $datos = $this->model->comprueba($this->model->id);
      $countmp = busca($this->model->id,'pr_ordenmateriaprima', 'pom_ordenprod','COUNT(*)');
      //$datos['error'][0] = "OK";
      if($datos['error'][0] == "OK"){
        if($this->model->articulo == "0" && $countmp <= 0){
          $botones .= '
            <button onclick="sincomposicion();" type="button" class="btn btn-success btn-sm"><i class="fas fa-play-circle"></i> Activar</button>
          ';
        }else{
          $botones .= '
            <button onclick="confirmacionarranque();" type="button" class="btn btn-success btn-sm"><i class="fas fa-play-circle"></i> Activar</button>
          ';
        }
        
      } else if(($datos['error'][0]) == "1") {
        
        $botones .= '<button onclick="checarmp();" type="button" class="btn btn-danger btn-sm"><i class="fas fa-play-circle"></i> Activar</button>
        <script language="javascript">
          function checarmp(){
            Swal.fire({
                icon: "error",
                title: "Error",
                html: "'.$datos['msj'][0].'",
            }).then((result) => {
                if (result.isConfirmed || result.isDenied) {
                    Swal.close();
            }
            });
          }
        </script>';
      } else if($datos['error'][0] == "2"){
        $botones.='<button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#tablaerror"><i class="fas fa-play-circle"></i> Activar</button>';
      }
    } else {
      $botones .= '
      <a href="formats/hojaviajera.php?ordenp='.$_GET['id'].'" target="_blank">
        <button type="button" class="btn btn-info btn-sm"><i class="fas fa-leaf"></i> Hoja viajera</button>
      </a>
      ';
    }

    $contador = 0;
    $ordenesprod = '';

    //Filtramos por composición
    if($this->model->articulo != 0){
      $sql = 'SELECT ac_mp AS materiaprima FROM articulos_composicion WHERE ac_articulo = "'.$this->model->articulo.'" AND ac_modelo = "'.$this->model->modelo.'" GROUP BY ac_mp ORDER BY ac_mp ASC';
      /* $sql = 'SELECT po_id FROM pr_ordenprod WHERE po_articulo = "'.$this->model->articulo.'" AND po_modelo = "'.$this->model->modelo.'"';  */     
    } else{    
      $sql = 'SELECT pom_materiaprima AS materiaprima FROM pr_ordenmateriaprima WHERE pom_ordenprod = "'.$this->model->id.'" GROUP BY pom_materiaprima ORDER BY pom_materiaprima ASC';
      /* $sql = 'SELECT po_id FROM pr_ordenprod WHERE po_nmbarticulo = "'.$this->model->nmbarticulo.'"'; */
    }
    $result = setq($sql);
    if($result->num_rows){
      while($row = $result->fetch_array()){
        //Contabilizamos la cantidad de usados de la materia prima
        if($contador == 0){
            $ordenesprod .= '"'.$row['materiaprima'].'"';
        } else{
            $ordenesprod .= ',"'.$row['materiaprima'].'"';
        }
        $contador++;
      }
    }

    $sqlmermas = 'SELECT am_mp FROM articulos_mermas WHERE am_mp IN ('.$ordenesprod.') AND 
                  am_estatus = "A" GROUP BY am_mp, am_largo, am_ancho, am_alto ORDER BY am_id';

    /* $sqlmermas = 'SELECT am_cantidad, am_mp FROM articulos_mermas WHERE am_ordenp IN ('.$ordenesprod.') AND 
                  am_estatus = "A" GROUP BY am_mp, am_largo, am_ancho, am_alto ORDER BY am_id'; */



    $resultmermas = setq($sqlmermas);
    $nmermas = $resultmermas->num_rows;
    if($nmermas > 0 && $this->model->estatus == "N"){
      $botones .= '<a id="abtnmermas" data-src="popup/setmermasordenp.php?articulo='.$this->model->articulo.'&modelo='.$this->model->modelo.'&ordenp='.$this->model->id.'" data-fancybox data-type="ajax" href="javascript:;">
                <button type="button" id="btnmermas" class="btn btn-sm btn-info"><i class="fas fa-eye"></i>Mermas existentes</button>
                </a>';
    }


    if($this->model->articulo == "0" && $this->model->estatus == "N"){
      $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setcompordenp.php?ordenprod='.$this->model->id.'&rand='.rand().'" href="javascript:;" id="fbmateriap">
        <button type="button" class="btn btn-sm text-white" style="background: teal;"><i class="fas fa-puzzle-piece" style="color: #fff"></i> Materia prima</button>
      </a>';
      $botones .= '<a  data-fancybox data-type="ajax" data-src="popup/listaprocesos.php?ordenprod='.$this->model->id.'&rand='.rand(1,999).'" href="javascript:;" id="fbprocesos">
        <button class="btn btn-sm btn-info">
          <i class="fa fa-plus"></i> Agregar procesos 
        </button>
      </a>';
    }
    $estatusp = "'".$this->model->estatus."'";
    if($this->model->estatus != "C"){
      $botones.= '<button class="btn btn-sm btn-danger" onclick="cancelarord('.$estatusp.');">
          <i class="fas fa-times"></i> Cancelar 
        </button>';
    } else{
      $botones.= '<button class="btn btn-sm btn-secondary" onclick="cancelarord('.$estatusp.');">
          <i class="fas fa-times"></i> Mermas 
        </button>';
    }

    
    $atras = '<a href="?modulo=ordenprod&accion=index">
      <button type="button" class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" ></i> Atrás</button>
    </a>';

    $artname = $this->model->nmbarticulo;

    if($this->model->tipo == "L"){
      $artipo = "LISO";
    } else if ($this->model->tipo == "I"){
      $artipo = "IMPRESO";
    } else{
      $artipo = $this->model->obstipo;
    }

    $rolpe = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_rol');
    $encargado = busca($this->model->encargado, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');

    $cortador = busca($this->model->cortador, 'pr_empleados', 'pe_id', 'pe_nmb');

    if($this->model->estatus == "N"){
      $estatus = "Nuevo";
    } else if($this->model->estatus == "P"){
      $estatus = "En proceso";
    } else if($this->model->estatus == "C"){
      $estatus = "Cancelada";
    } else{
      $estatus = "Finalizado";
    }
    
    toolbar('ORDEN DE PRODUCCIÓN ',$atras,'',$botones);
    echo '
    <style>
    /* Agrega estilos personalizados aquí si es necesario */
    #miDiv {
    display: none;
    }

    /* ESTILOS DEL LISTADO DE PROCESOS DE LA FASE */
    .c-timeline__item {
    position: relative;
    display: flex;
    gap: 1.5rem;
    //outline: solid 1px;

    &:last-child {
      .c-timeline__content {
      &:before {
          display: none;
      }
      }
    }
    }

    ol, ul {
      padding-left: unset !important;
    }
    .c-timeline__content {
    flex: 1;
    position: relative;
    order: 1;
    padding-left: 1.5rem;
    padding-bottom: 3rem;

    &:before {
      content: "";
      position: absolute;
      right: 100%;
      top: 0;
      height: 100%;
      width: 2px;
      background-color: lightgrey;
    }

    &:after {
      content: "";
      position: absolute;
      left: calc(0px - 11px);
      top: 0;
      width: 20px;
      height: 20px;
      background-color: #fff;
      z-index: 1;
      border: 2px solid lightgrey;
      border-radius: 50%;
    }
    }

    .c-timeline__content2 {
      flex: 1;
      position: relative;
      order: 1;
      padding-left: 1.5rem;
      padding-bottom: 3rem;
  
      &:before {
        content: "+";
        position: absolute;
        right: 100%;
        top: 0;
        height: 100%;
        width: 2px;
        background-color: lightgrey;
      }
  
      &:after {
        content: "+";
        position: absolute;
        left: calc(0px - 11px);
        top: 0;
        width: 20px;
        height: 20px;
        background-color: #fff;
        z-index: 1;
        border: 2px solid lightgrey;
        border-radius: 50%;
        font-weight: bold;
        text-align: center;
      }
      }

    .c-timeline__title {
    font-weight: bold;
    margin-bottom: 0.5rem;
    }

    .c-timeline__desc {
    color: grey;
    }

    time {
    text-align: end;
    flex: 0 0 100px;
    min-width: 0;
    overflow-wrap: break-word;
    padding-bottom: 1rem;
    }

    /*** Non-demo CSS ***/

    *,
    *:before,
    *:after {
    box-sizing: border-box;
    }

    table, th, td {
      border: 1px solid #ddd !important; /* Borde gris tenue */
    }
    /* ESTILOS DEL LISTADO DE PROCESOS DE LA FASE */
    
    .c-timeline__content.custom-color-c::after {
      background-color: #fcff02; /* Color de fondo del pseudo-elemento */
      animation: pulse-animation 2s infinite;
      /* height: 20px;
      width: 20px;
      position: absolute;
      animation: animation-pulse 3.5s ease-out;
      animation-iteration-count: infinite;
      opacity: 0;
      border-width: 3px;
      border-style: solid;
      border-color: #a1a5b7; */
    }

    .c-timeline__content.custom-color-a::after {
      background-color: #2ccd2b; /* Color de fondo del pseudo-elemento */
    }
  
    .c-timeline__content.custom-color-b::before {
      background-color: #2ccd2b; /* Color de fondo del pseudo-elemento */
    }
    .c-timeline__content.custom-color-p::after {
      background-color: orange; /* Color de fondo del pseudo-elemento */
    }
    .c-timeline__content.custom-color-x::after {
      background-color: red; /* Color de fondo del pseudo-elemento */
    }


    @keyframes pulse-animation {
      0% {
        box-shadow: 0 0 0 0px rgba(0, 0, 0, 0.2);
      }
      100% {
        box-shadow: 0 0 0 10px rgba(0, 0, 0, 0);
      }
    }

    </style>';
    echo '
    <div class="modal fade text-xs-left" id="tablaerror" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel33"><i class="fas fa-times-circle" style="color: red; font-size: x-large"></i> No se puede iniciar la orden de producción</h4>
            <button type="button" class="btn close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="col-12 p-4">
            <h4>
            '.$datos['msj'][0].'
            </h4>
          </div>
          <center>
              <div class="col-10">
              <table class="table table-responsive p-4">
                <thead>
                  <tr>
                    <th> <b>Materia prima</b></th>
                    <th> <b>Cantidad necesaria</b></th>
                    <th> <b>Cantidad en existencia</b></th>
                  </tr>
                </thead>
                <tbody>';
                  for($i=0;$i<sizeof($datos['mp']);$i++){
                    echo '<tr>
                      <td>'.$datos['mp'][$i].'</td>
                      <td>'.$datos['esp'][$i].'</td>
                      <td>'.$datos['exi'][$i].'</td>
                    </tr>';
                  }
                echo '</tbody>
              </table>
            </div>
          </center>
          <div class="modal-body row">
            <div class="modal-footer p-2">
              <button type="button" class="btn btn-info" data-bs-dismiss="modal" aria-label="Close">
                <i class="fas fa-check"></i>
                Cerrar
              </button>
            </div>
          </div>  
        </div>
      </div>
    </div>';
echo '<script>
function banderacambios(){
  //console.log("Entra");
  var banderacierre = document.getElementById("banderacierre");
  if(banderacierre.value == 1 ){
    setTimeout(function () {
      banderacierre.value = 0;
    }, 200);
  }
}
      </script>';
    echo '<div class="row mt-8">    
    <div class="col-md-4 col-12">
    <input type="hidden" id="banderacierre" value="0">
    <table id="myTableG" class="table table-hover">
        <thead class="thead-active text-black">
            <tr>
                <th colspan="2" class="thead-active bg-primary text-white">
                    <center>Detalles del proceso</center>
                </th>
            </tr>
        </thead>
        <tbody>
            <!-- <tr class="thead-active bg-primary text-white">
                <td>Concepto</td>
                <td>Descripción</td>
            </tr> -->

            <tr>
                <td>Orden de producción:</td>
                <td>'.$this->model->folio.'</td>
            </tr>
            <tr>
                <td>Generó:</td>
                <td>'.$this->model->ugen.'</td>
            </tr>
            <tr>
                <td>Encargado:</td>
                <td>'.$encargado.'</td>
            </tr>
            <tr>
                <td>Cortador:</td>
                <td>'.$cortador.'</td>
            </tr>
            <tr>
                <td>Artículo:</td>
                <td>'.$artname.'</td>
            </tr>
            <tr>
                <td>Tipo de artículo:</td>
                <td>'.$artipo.'</td>
            </tr>
            <tr>
                <td>Cantidad:</td>
                <td>'.$this->model->cantidad.'</td>
            </tr>
            <tr>
                <td>Estatus:</td>
                <td>'.$estatus.'</td>
            </tr>
            <tr>
                <td>Fecha del la orden:</td>
                <td>'.fecha_formato($this->model->fgen, true, false).'</td>
            </tr>
            <tr>
                <td>Fecha estimada de entrega:</td>
                <td>'.fecha_formato($this->model->fentrega, false, false).'</td>
            </tr>
            <tr>
                <td>Retroalimentación:</td>
                <td>'.$this->model->notasalida.'</td>
            </tr>

        </tbody>
    </table>
    </div>

    <div class="col-md-8 col-12">';

    // INICIA EL CICLO DE FASES QUE CONTIENE LA ORDEN DE PRODUCCIÓN
    $pactivo = '';
    if($this->model->result -> num_rows > 0){
      while($row = $this->model->result->fetch_array()){
        $faseactiva = busca($row['pp_fase'],'pr_procesosop','pp_estatus IN ("A","F") AND pp_ordenp = "'.$row['pp_ordenp'].'" AND pp_fase','pp_estatus');
        
        if($faseactiva == "A" || $faseactiva == "F"){
          $disnone = 'block';
        }else{
          $disnone = 'none';
        }
  
        echo '
        <div style="padding-bottom: 1px;">
          <button id="miBoton'.$row['pp_proceso'].'" onclick="selectfase('.$row['pp_proceso'].')" '.$back.' class="btn btn-primary btn-block col-12 col-md-12 "><h3 class="text-white">FASE: '.$row['pf_nmb'].' </h3></button>
        </div>
        <div id="miDiv'.$row['pp_proceso'].'" style="display: '.$disnone.';">
          <div class="mt-6">
  
            <!-- 
            <div class="">
              <table class="table table-striped">
                <thead class="">
                  <tr class="">
                    <th class="">Estatus</th>
                    <th class="">Fecha Inicio</th>
                    <th class=""></th>
                  </tr>
                </thead>
                <tbody class="">
                </tbody>
              </table>
            </div> -->
            ';
  
  
            // AQUI SE HACE EL WHILE DE LOS PUNTOS
            echo '
              <ol class="c-timeline">';
  
                $sqlx = 'SELECT pp_id,pp_orden,pp_proceso, pp_estatus, pp_planeado , pp_fini, pp_ffin, pp_operador, pp_obs  FROM pr_procesosop WHERE pp_ordenp = "'.$row['pp_ordenp'].'" AND pp_fase = "'.$row['pp_fase'].'" ORDER BY pp_orden ASC '; //AND pp_id NOT IN ('.implode( "," , $arrayant).') 
                $resultx = setq($sqlx);
                //echo $sqlx; 
                $count = $resultx->num_rows;
                $tot = 0;
                $contadorfinalizados = 0;
                while($rowx = $resultx->fetch_array()){
  
                  $agregar = '
                    <li class="c-timeline__item">
                    <a style="display: contents;" data-fancybox data-type="ajax" data-src="popup/setprocesoemergente.php?ordenp='.$row['pp_ordenp'].'&fase='.$row['pp_fase'].'&antid='.$rowx['pp_id'].'&ordenant='.$rowx['pp_orden'].'&rand='.rand().'" href="javascript:;">
                      <div class="c-timeline__content2">
                          <p class="c-timeline__desc">Agregar Proceso</p>
                      </div>
                    </a>
                    <time></time>
                  ';
  
                  $detalles = '';
                  if($rowx['pp_estatus'] == "A"){
                    $espendiente = 'En proceso';
                    $color = 'custom-color-c';
                  }elseif($rowx['pp_estatus'] == "F"){
                    $espendiente = 'Finalizado';
                    $color = 'custom-color-b custom-color-a';
                    $agregar = '';
                    $detalles = '
                    <p class="c-timeline__desc">
                      Inicio: '.$rowx['pp_fini'].' <br>
                      Fin: '.$rowx['pp_ffin'].' <br>
                      Operador: '.busca($rowx['pp_operador'],'pr_empleados','pe_id','pe_nmb').' <br>
                      Observaciones: <br>
                      '.$rowx['pp_obs'].' <br>
                    </p>
                    ';
                    $contadorfinalizados++;
                  }elseif($rowx['pp_estatus'] == "N"){
                    $espendiente = 'Pendiente';
                    $color = '';
                  }elseif($rowx['pp_estatus'] == "P"){
                    $espendiente = 'Pendiente de Operador';
                    $color = 'custom-color-p';
                  }elseif($rowx['pp_estatus'] == "C"){
                    $espendiente = 'Cancelado';
                    $color = 'custom-color-x';
                    $agregar = '';
                  }
                  

                  if($rowx['pp_planeado'] == "0") $espendiente .= ' - Proceso Emergente (No planeado)';
  
                  $tot++;
                  $siguientevar = busca($rowx['pp_proceso'],'pr_procesos','ppr_id','ppr_nmb');
                    //onclick="?modulo=temporizadorpps&accion=index&op='.$row['pp_ordenp'].'&proceso='.$rowx['pp_id'].'" 
                    echo '
                      <li class="c-timeline__item">
                      <a onclick="window.open(\'popup/temporizadorpps.php?op='.$row['pp_ordenp'].'&proceso='.$rowx['pp_id'].'&tipo=uid\',\'operador\',\'width=\'+(window.screen.width)+\' height=\'+(window.screen.height)+\'\')" class="" style="display: contents;">
                      <div class="c-timeline__content '.$color.'">
                          <h3 class="c-timeline__title ">'.$siguientevar.'</h3>
                          <p class="c-timeline__desc">'.$espendiente.'</p>
                          '.$detalles.'
                      </div>
                      <time></time>
                      </a>
                      </li>
                    
  
                    
                    '.$agregar;
  
                    if($count == $tot){
                      if($contadorfinalizados == $tot) $colorxx = 'custom-color-a';
                      else $colorxx = '';
                      echo '
                      <li class="c-timeline__item">
                      <div class="c-timeline__content '.$colorxx.'">
                          <h3 class="c-timeline__title">FIN DE FASE</h3>
                          <!-- <p class="c-timeline__desc">Pendiente</p> -->
                      </div>
                      <time></time>
                      </li>
                      ';
                    }
                }
            
  
        echo '
            </ol>
            <!-- <div class="mb-2 text-center">
              <a href="" class="btn btn-info"><i class="fa fa-eye"></i> Visualizar</a>
            </div> -->
  
          </div>
        </div>';
        //}
      }
      // FINALIZA EL CICLO DE FASES QUE CONTIENE LA ORDEN DE PRODUCCIÓN
    } else {
      echo '<div class="alert alert-danger">No se han configurado los procesos de producción </div>';
    }
   

    echo '</div>
    </div>';
  

    echo '
    <script>
    $(document).ready(function() {
    $("#miBoton").click(function() {
    $("#miDiv"+"'.$pactivo.'").slideToggle(); //Desplegaremos la fase del proceso que se encuentra activo
    });
    });

    function selectfase(k){
      var miDiv = document.getElementById("miDiv"+k);
      //var miBoton = document.getElementById("miBoton"+k);
      
      $("#miDiv"+k).slideToggle();

    }
    </script>
    ';
    }

    function articulosop(){
      $nregistros = $this->model->result->num_rows;
      $artname = busca($this->model->articulo, "articulos", "a_id", "a_nmb");
      $var = busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "'.$this->model->modelo.'" AND av_articulo', 'COUNT(*)');
      if($var > 0) {
        $artname .= " ".busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "'.$this->model->modelo.'" AND av_articulo', 'av_nmb');
      }
  
      if($this->model->tipo == "L"){
        $artipo = "LISO";
      } else if ($this->model->tipo == "I"){
        $artipo = "IMPRESO";
      } else{
        $artipo = $this->model->obstipo;
      }
  
      $rolpe = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_rol');
      $rol = busca($rolpe, 'rolesprod', 'rp_id', 'rp_nmb');
      $encargado = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_nmb')." - ".$rol;
  
      $cortador = busca($this->model->cortador, 'pr_empleados', 'pe_id', 'pe_nmb');
  
      if($this->model->estatus == "N"){
        $estatus = "Nuevo";
      } else if($this->model->estatus == "P"){
        $estatus = "En proceso";
      } else{
        $estatus = "Finalizado";
      }

      echo '
      <style>
      table, th, td {
        border: 1px solid #ddd; /* Borde gris tenue */
      }

      .text-end{
        text-align:right!important
      }

      .text-bold-400 {
        font-weight : 400;
      }

      .align-self-center{
        -ms-flex-item-align:center!important;
        align-self:center!important
      }

      .justify-content-between{
        -webkit-box-pack:justify!important;
        -ms-flex-pack:justify!important;
        justify-content:space-between!important
      }

      .px-md-1{
        padding-right:.25rem!important;
        padding-left:.25rem!important
      }

      .castle-success{
        color: #50cd89;
      }

      .castle-danger{
        color: #f1416c;
      }

      .castle-warning{
        color: #ffc700;
      }

      .castle-primary{
        color: #41b1f1;
      }
      
      </style>
      ';

      echo '
      <div class="row mt-8">
        <div class="col-12 col-md-2">
        <span style="font-weight: bold;">Seleccionar todos: <input class="form-check-input" type="checkbox" onchange="selectAll(1);" id="checkAll" name="selall"></span>
        </div>
      </div>
      <div class="row">
      <div class="col-12 col-md-4">
        <span style="font-style: italic; font-size: 11px;">Número de artículos: '.$nregistros.'</span>
      </div>
      <div class="col-12 col-md-8" style="text-align: right;">
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Sin iniciar: <input class="form-check-input" type="checkbox" style="border: none; background: #888;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Iniciado: <input class="form-check-input" type="checkbox" style="border: none; background: #ffc700;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Entre fases (Finalizado): <input class="form-check-input" type="checkbox" style="border: none; background: #f1416c;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Finalizado: <input class="form-check-input" type="checkbox" style="border: none; background: #50cd89;" checked disabled></span>
        &nbsp;&nbsp;
        &nbsp;&nbsp;
      </div>
      ';

      //INICIA TABLA DE TODOS LOS ARTÍCULOS QUE HAY DE LA ORDEN DE PRODUCCIÓN
      $anchocol = 10;
      echo'
      <div class="row mt-2">
        <div class="col-12 col-md-12">
          <center>
          <table class="col-12 col-md-12 table-responsive" style="background: white;">
            <thead>
              <th class="thead-active bg-primary text-white" colspan="'.$anchocol.'"><center>Listado de artículos</center></th>
            </thead>
            <tbody>
            
            <tr>';

            
            if($nregistros == 0){
              echo '
              <td colspan="'.$anchocol.'"><center>No hay artículos para mostrar</center></td>
              ';
            } else{
              $i = 0;
              while($row = $this->model->result->fetch_array()){
                if($i%$anchocol == 0){
                  echo '
                  </tr>
                  <tr>';
                }
                /* echo '
              <td><center><button class="btn btn-sm btn-secondary col-12 col-md-12">RED DE PROTECCIÓN TRAMPOLIN NARANJA '.($i +1).'</button></center></td>
              '; */
              if(!empty($row['pra_estpa']) && !empty($row['pra_estsp'])){
                if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "N"){
                  $clase = 'castle-danger'; //Artículo entre fases
                  $txticono = 'Artículo entre fases';
                } else if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "F"){
                  $clase = 'castle-success'; //Artículo finalizado
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A"){
                  $clase = 'castle-warning'; //En ejecución
                  $txticono = 'Artículo en ejecución';
                } else if($row['pra_estpa'] == "N"){
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              } else{
                if($row['pra_estpa'] == "F" && empty($row['pra_estsp'])){
                  $clase = 'castle-success';
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A" && empty($row['pra_estsp'])){
                  $clase = 'castle-warning';
                  $txticono = 'Artículo en ejecución';
                } else{
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              }

              if(isset($_GET['tipo']) && $_GET['tipo'] == "I" && $row['pra_estpa'] == "F"){
                $disa = "disabled checked";
              } else{
                if(!empty($row['pra_estpa']) && !empty($row['pra_estsp'])){
                if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "N"){
                  $clase = 'castle-danger'; //Artículo entre fases
                  $txticono = 'Artículo entre fases';
                } else if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "F"){
                  $clase = 'castle-success'; //Artículo finalizado
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A"){
                  $clase = 'castle-warning'; //En ejecución
                  $txticono = 'Artículo en ejecución';
                } else if($row['pra_estpa'] == "N"){
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              } else{
                if($row['pra_estpa'] == "F" && empty($row['pra_estsp'])){
                  $clase = 'castle-success';
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A" && empty($row['pra_estsp'])){
                  $clase = 'castle-warning';
                  $txticono = 'Artículo en ejecución';
                } else{
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              }
                $disa = "";
              }

              if(isset($_GET['tipo']) && $_GET['tipo'] == "F" && $row['pra_estpa'] == "F"){
                $disa = "disabled checked";
              }

              echo '
              <td>
              <center>
              <div onclick="pressIcono('.$row['pra_id'].');">
                <i class="fab fa-fort-awesome fs-1 fa-2x '.$clase.'" id="icono'.$row['pra_id'].'"></i>
                <input hidden class="form-check-input check-all" type="checkbox" id="check'.$row['pra_id'].'" name="boletos'.$row['pra_id'].'" value="'.$row['pra_id'].'" '.$disa.'>
                <input type="hidden" id="class'.$row['pra_id'].'" value="'.$clase.'">
              </div>
              <div onclick="pressIcono('.$row['pra_id'].');">
                <span style="font-style: italic; font-size: 11px; font-weight: bold;">'.$row['pra_cb'].'<span>
              </div>
              </center>
              </td>';
              $i++;
              }
            }
            echo '
            </tr>
            </tbody>
          </table>
    </center>
        <div>
      </div>';
      //FINALIZA TABLA DE TODOS LOS ARTÍCULOS QUE HAY DE LA ORDEN DE PRODUCCIÓN

      echo '
      <script>

      function selectAll(k){
        var response = false;
        var cb1 = document.getElementById("checkAll");
        var icono = "";
        var classorigen = "";
        
        if(!cb1.checked){
          response = false;
          $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/estatusproceso.php",
            data: {
                "estatus": 0,
                "all": 0,
                "proceso": "'.$_GET['proceso'].'",
                "ordenp": "'.$_GET['idp'].'"
            },
            success: function (data) {
              if(data == 0){
                alert("Otro usuario está interactuando con este proceso.");
                window.close();
                window.location.href = "operador.php"; 
              }
            }
          });
        } else{
          response = true;
          $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/estatusproceso.php",
            data: {
                "estatus": 1,
                "all": 0,
                "proceso": "'.$_GET['proceso'].'",
                "ordenp": "'.$_GET['idp'].'"
            },
            success: function (data) {
              if(data == 0){
                alert("Otro usuario está interactuando con este proceso.");
                window.close();
                window.location.href = "operador.php"; 
              }
            }
          });
        }
        var elementosCheckAll = document.querySelectorAll(".check-all");

        // Recorre los elementos y marca cada uno como "checked"
        elementosCheckAll.forEach(function(elemento) {
          if(!elemento.hasAttribute("disabled")){
            elemento.checked = response;
            icono = document.getElementById("icono"+elemento.value);
            classorigen = document.getElementById("class"+elemento.value).value;
            //console.log("Elemto origen: "+elemento.value);
            if(elemento.checked){
              icono.classList.remove("castle-origin");
              icono.classList.remove("castle-danger");
              icono.classList.remove("castle-warning");
              icono.classList.remove("castle-success");
              icono.classList.add("castle-primary");
            }else{
              icono.classList.remove("castle-primary");
              icono.classList.add(classorigen); 
            }
          }
        });
      }

        function pressIcono(k){
          // Obtén el icono y el checkbox por su ID
          var checkbox = document.getElementById("check"+k);
          var icono = document.getElementById("icono"+k);
          var classorigen = document.getElementById("class"+k).value;

            // Cambia el estado "checked" del checkbox
            if(!checkbox.hasAttribute("disabled")){
              checkbox.checked = !checkbox.checked;
              if(checkbox.checked){
                icono.classList.remove("castle-origin");
                icono.classList.remove("castle-danger");
                icono.classList.remove("castle-warning");
                icono.classList.remove("castle-success");
                icono.classList.add("castle-primary");

                $.ajax({
                  dataType: "json",
                  type: "POST",
                  url: "../query/estatusproceso.php",
                  data: {
                      "estatus": 1,
                      "all": k,
                      "proceso": "'.$_GET['proceso'].'",
                      "ordenp": "'.$_GET['idp'].'"
                  },
                  success: function (data) {
                    if(data == 0){
                      alert("Otro usuario está interactuando con este proceso.");
                      window.close();
                      window.location.href = "operador.php"; 
                    }
                  }
                });
              }else{
                icono.classList.remove("castle-primary");
                icono.classList.add(classorigen); 

                $.ajax({
                  dataType: "json",
                  type: "POST",
                  url: "../query/estatusproceso.php",
                  data: {
                      "estatus": 0,
                      "all": k,
                      "proceso": "'.$_GET['proceso'].'",
                      "ordenp": "'.$_GET['idp'].'"
                  },
                  success: function (data) {
                    if(data == 0){
                      alert("Otro usuario está interactuando con este proceso.");
                      window.close();
                      window.location.href = "operador.php"; 
                    }
                  }
                });
              }
            }
        }

      </script>
      ';
      
    }

    function articulosopdinamico(){
      $nregistros = $this->model->result->num_rows;
      $artname = busca($this->model->articulo, "articulos", "a_id", "a_nmb");
      $var = busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "'.$this->model->modelo.'" AND av_articulo', 'COUNT(*)');
      if($var > 0) {
        $artname .= " ".busca($this->model->articulo, 'articulos_variantes', 'av_modelo = "'.$this->model->modelo.'" AND av_articulo', 'av_nmb');
      }
  
      if($this->model->tipo == "L"){
        $artipo = "LISO";
      } else if ($this->model->tipo == "I"){
        $artipo = "IMPRESO";
      } else{
        $artipo = $this->model->obstipo;
      }
  
      $rolpe = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_rol');
      $rol = busca($rolpe, 'rolesprod', 'rp_id', 'rp_nmb');
      $encargado = busca($this->model->encargado, 'pr_empleados', 'pe_id', 'pe_nmb')." - ".$rol;
  
      $cortador = busca($this->model->cortador, 'pr_empleados', 'pe_id', 'pe_nmb');
  
      if($this->model->estatus == "N"){
        $estatus = "Nuevo";
      } else if($this->model->estatus == "P"){
        $estatus = "En proceso";
      } else{
        $estatus = "Finalizado";
      }

      $html = '
      <style>
      table, th, td {
        border: 1px solid #ddd; /* Borde gris tenue */
      }

      .text-end{
        text-align:right!important
      }

      .text-bold-400 {
        font-weight : 400;
      }

      .align-self-center{
        -ms-flex-item-align:center!important;
        align-self:center!important
      }

      .justify-content-between{
        -webkit-box-pack:justify!important;
        -ms-flex-pack:justify!important;
        justify-content:space-between!important
      }

      .px-md-1{
        padding-right:.25rem!important;
        padding-left:.25rem!important
      }

      .castle-success{
        color: #50cd89;
      }

      .castle-danger{
        color: #f1416c;
      }

      .castle-warning{
        color: #ffc700;
      }

      .castle-primary{
        color: #41b1f1;
      }
      
      </style>
      ';

      $html .= '
      <div class="row mt-8">
        <div class="col-12 col-md-2">
        <span style="font-weight: bold;">Seleccionar todos: <input class="form-check-input" type="checkbox" onchange="selectAll(1);" id="checkAll" name="selall"></span>
        </div>
      </div>
      <div class="row">
      <div class="col-12 col-md-4">
        <span style="font-style: italic; font-size: 11px;">Número de artículos: '.$nregistros.'</span>
      </div>
      <div class="col-12 col-md-8" style="text-align: right;">
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Sin iniciar: <input class="form-check-input" type="checkbox" style="border: none; background: #888;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Iniciado: <input class="form-check-input" type="checkbox" style="border: none; background: #ffc700;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Entre fases (Finalizado): <input class="form-check-input" type="checkbox" style="border: none; background: #f1416c;" checked disabled></span>
        &nbsp;&nbsp;
        <span style="font-style: italic; font-size: 11px; font-weight: bold;">Finalizado: <input class="form-check-input" type="checkbox" style="border: none; background: #50cd89;" checked disabled></span>
        &nbsp;&nbsp;
        &nbsp;&nbsp;
      </div>
      ';

      //INICIA TABLA DE TODOS LOS ARTÍCULOS QUE HAY DE LA ORDEN DE PRODUCCIÓN
      $anchocol = 10;
      $html .= '
      <div class="row mt-2">
        <div class="col-12 col-md-12">
          <center>
          <table class="col-12 col-md-12 table-responsive" style="background: white;">
            <thead>
              <th class="thead-active bg-primary text-white" colspan="'.$anchocol.'"><center>Listado de artículos</center></th>
            </thead>
            <tbody>
            
            <tr>';

            
            if($nregistros == 0){
              $html .= '
              <td colspan="'.$anchocol.'"><center>No hay artículos para mostrar</center></td>
              ';
            } else{
              $i = 0;
              while($row = $this->model->result->fetch_array()){
                if($i%$anchocol == 0){
                  $html .=  '
                  </tr>
                  <tr>';
                }
                /* echo '
              <td><center><button class="btn btn-sm btn-secondary col-12 col-md-12">RED DE PROTECCIÓN TRAMPOLIN NARANJA '.($i +1).'</button></center></td>
              '; */
              if(!empty($row['pra_estpa']) && !empty($row['pra_estsp'])){
                if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "N"){
                  $clase = 'castle-danger'; //Artículo entre fases
                  $txticono = 'Artículo entre fases';
                } else if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "F"){
                  $clase = 'castle-success'; //Artículo finalizado
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A"){
                  $clase = 'castle-warning'; //En ejecución
                  $txticono = 'Artículo en ejecución';
                } else if($row['pra_estpa'] == "N"){
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              } else{
                if($row['pra_estpa'] == "F" && empty($row['pra_estsp'])){
                  $clase = 'castle-success';
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A" && empty($row['pra_estsp'])){
                  $clase = 'castle-warning';
                  $txticono = 'Artículo en ejecución';
                } else{
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              }

              /* if(isset($_GET['tipo']) && $_GET['tipo'] == "I" && ($row['pra_estpa'] == "A" || $row['pra_estpa'] == "F")){ */
              if(isset($_GET['tipo']) && $_GET['tipo'] == "I" && $row['pra_estpa'] == "F"){
                $disa = "disabled checked";
              } else{
                if(!empty($row['pra_estpa']) && !empty($row['pra_estsp'])){
                if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "N"){
                  $clase = 'castle-danger'; //Artículo entre fases
                  $txticono = 'Artículo entre fases';
                } else if($row['pra_estpa'] == "F" && $row['pra_estsp'] == "F"){
                  $clase = 'castle-success'; //Artículo finalizado
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A"){
                  $clase = 'castle-warning'; //En ejecución
                  $txticono = 'Artículo en ejecución';
                } else if($row['pra_estpa'] == "N"){
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              } else{
                if($row['pra_estpa'] == "F" && empty($row['pra_estsp'])){
                  $clase = 'castle-success';
                  $txticono = 'Artículo finalizado';
                } else if($row['pra_estpa'] == "A" && empty($row['pra_estsp'])){
                  $clase = 'castle-warning';
                  $txticono = 'Artículo en ejecución';
                } else{
                  $clase = 'castle-origin';
                  $txticono = 'Artículo listo para iniciar el primer proceso';
                }
              }
                $disa = "";
              }

              if(isset($_GET['tipo']) && $_GET['tipo'] == "F" && $row['pra_estpa'] == "F"){
                $disa = "disabled checked";
              }

              $html .= '
              <td>
              <center>
              <div onclick="pressIcono('.$row['pra_id'].');">
                <i class="fab fa-fort-awesome fs-1 fa-2x '.$clase.'" id="icono'.$row['pra_id'].'"></i>
                <input hidden class="form-check-input check-all" type="checkbox" id="check'.$row['pra_id'].'" name="boletos'.$row['pra_id'].'" value="'.$row['pra_id'].'" '.$disa.'>
                <input type="hidden" id="class'.$row['pra_id'].'" value="'.$clase.'">
              </div>
              <div onclick="pressIcono('.$row['pra_id'].');">
                <span style="font-style: italic; font-size: 11px; font-weight: bold;">'.$row['pra_cb'].'<span>
              </div>
              </center>
              </td>';
              $i++;
              }
            }
            $html .= '
            </tr>
            </tbody>
          </table>
    </center>
        <div>
      </div>';
      //FINALIZA TABLA DE TODOS LOS ARTÍCULOS QUE HAY DE LA ORDEN DE PRODUCCIÓN

      $html .= '
      <script>

      function selectAll(k){
        var response = false;
        var cb1 = document.getElementById("checkAll");
        var icono = "";
        var classorigen = "";
        
        if(!cb1.checked){
          response = false;
          $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/estatusproceso.php",
            data: {
                "estatus": 0,
                "all": 0,
                "proceso": "'.$_GET['proceso'].'",
                "ordenp": "'.$_GET['idp'].'"
            },
            success: function (data) {
              if(data == 0){
                alert("Otro usuario está interactuando con este proceso.");
                window.close();
                window.location.href = "operador.php"; 
              }
            }
          });
        } else{
          response = true;
          $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/estatusproceso.php",
            data: {
                "estatus": 1,
                "all": 0,
                "proceso": "'.$_GET['proceso'].'",
                "ordenp": "'.$_GET['idp'].'"
            },
            success: function (data) {
              if(data == 0){
                alert("Otro usuario está interactuando con este proceso.");
                window.close();
                window.location.href = "operador.php"; 
              }
            }
          });
        }
        var elementosCheckAll = document.querySelectorAll(".check-all");

        // Recorre los elementos y marca cada uno como "checked"
        elementosCheckAll.forEach(function(elemento) {
          if(!elemento.hasAttribute("disabled")){
            elemento.checked = response;
            icono = document.getElementById("icono"+elemento.value);
            classorigen = document.getElementById("class"+elemento.value).value;
            //console.log("Elemto origen: "+elemento.value);
            if(elemento.checked){
              icono.classList.remove("castle-origin");
              icono.classList.remove("castle-danger");
              icono.classList.remove("castle-warning");
              icono.classList.remove("castle-success");
              icono.classList.add("castle-primary");
            }else{
              icono.classList.remove("castle-primary");
              icono.classList.add(classorigen); 
            }
          }
        });
      }

        function pressIcono(k){
          // Obtén el icono y el checkbox por su ID
          var checkbox = document.getElementById("check"+k);
          var icono = document.getElementById("icono"+k);
          var classorigen = document.getElementById("class"+k).value;

            // Cambia el estado "checked" del checkbox
            if(!checkbox.hasAttribute("disabled")){
              checkbox.checked = !checkbox.checked;
              if(checkbox.checked){
                icono.classList.remove("castle-origin");
                icono.classList.remove("castle-danger");
                icono.classList.remove("castle-warning");
                icono.classList.remove("castle-success");
                icono.classList.add("castle-primary");

                $.ajax({
                  dataType: "json",
                  type: "POST",
                  url: "../query/estatusproceso.php",
                  data: {
                      "estatus": 1,
                      "all": k,
                      "proceso": "'.$_GET['proceso'].'",
                      "ordenp": "'.$_GET['idp'].'"
                  },
                  success: function (data) {
                    if(data == 0){
                      alert("Otro usuario está interactuando con este proceso.");
                      window.close();
                      window.location.href = "operador.php"; 
                    }
                  }
                });
              }else{
                icono.classList.remove("castle-primary");
                icono.classList.add(classorigen); 

                $.ajax({
                  dataType: "json",
                  type: "POST",
                  url: "../query/estatusproceso.php",
                  data: {
                      "estatus": 0,
                      "all": k,
                      "proceso": "'.$_GET['proceso'].'",
                      "ordenp": "'.$_GET['idp'].'"
                  },
                  success: function (data) {
                    if(data == 0){
                      alert("Otro usuario está interactuando con este proceso.");
                      window.close();
                      window.location.href = "operador.php"; 
                    }
                  }
                });
              }
            }
        }
      </script>
      ';

      return $html;
      
    }

    function mermas(){

      ?>  
      <style>
        #myTableG {
          width: 100%;
          border-collapse: collapse;
        }
  
        #myTableG th, #myTableG td {
          padding: 8px;
          text-align: left;
          border: 1px solid #ddd;
          /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
        }
  
        #myTableG tbody tr:nth-child(odd) td {
          /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
        }
  
        #myTableG tbody tr:nth-child(even) td {
          /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
        }
        
        #myTableG {
          font-size: 12px; /* Cambia el tamaño de letra deseado */
        }
      </style>
      <?php 
      $titulo = "".$this->model->nmbarticulo;
      $atras = '<a href="?modulo=ordenprod&accion=show&id='.$this->model->id.'">
              <button class="mb-2 mr-2 btn btn-sm btn-warning">
                <i class="fa fa-arrow-left"></i> Atrás
              </button>
            </a>';
      if($this->model->estatus != "C"){
        $cancelar = '
            <button class="mb-2 mr-2 btn btn-sm btn-success" onclick="cancelarord();">
            <i class="fas fa-check-circle"></i> Aplicar 
            </button>';
        $agregar = '<a data-fancybox data-type="ajax" data-src="popup/setmermas.php?articulo='.$this->model->articulo.'&modelo='.$this->model->modelo.'&ordenp='.$this->model->id.'" href="javascript:;">
            <button class="mb-2 mr-2 btn btn-sm btn-primary" type="button" id="agregar"><i class="fas fa-plus"></i>Agregar</button>
          </a>';
      } else{
        $cancelar = '';
        $agregar = '';
      }
        
      toolbar("MERMAS ".$this->model->nmbarticulo,$atras, '', $agregar.$cancelar);
  
      if($this->model->estatus != "C"){
      echo '
      <div class="alert alert-warning col-md-12" role="alert">
      <center>
        Las medidas de volumen y área están representadas en metros.
      </center>
      </div>';
      } else{
      echo '
      <div class="alert alert-danger col-md-12" role="alert">
      <center>
        Esta orden de producción se encuentra cancelada
      </center>
      </div>';
      }
      
      echo '
      <div class="row mt-5">
      <div class="col-md-12">
        <div class="card">';
      $rtip = array('A' => 'Área', 'V' => 'Volumen', 'P' => 'Pieza');

      if($this->model->estatus != "F"){
      echo '<form action="?modulo=ordenprod&accion=insertmerma&id=' . $this->model->id . '" method="post" onsubmit="checksubmit();">';
          echo '<div class="row">';
          echo '<div class="col-12 col-md-7 mt-2 ms-5" >
          <!-- 
            <div class="form-group">
              <label for="agregar" class">Materia prima</label><br>
              <div class="input-group">
                <select id="mp" name="mp" class="form-control" onchange="getmp();" required> ';
                  while($rowmp = $this->model->result->fetch_array()){
                  $tipo = $rowmp['pmp_tipo'];
                  $medidas = ' - Medidas: ';
                  if($tipo == "A") $medidas .= decimal_format($rowmp['ac_largo']).' X '.decimal_format($rowmp['ac_ancho']);
                  else if($tipo == "V") $medidas .= decimal_format($rowmp['ac_ancho']).' X '.decimal_format($rowmp['ac_largo']).' X '.decimal_format($rowmp['ac_alto']);
                  else $medidas = '';
                    
                  echo '<option value="'.$rowmp['pmp_id'].'" data-largo="'.decimal_format($rowmp['ac_largo']).'" data-ancho="'.decimal_format($rowmp['ac_ancho']).'" data-alto="'.decimal_format($rowmp['ac_alto']).'">'.$rowmp['pmp_nmb'].' - Material: '. $rowmp['pmp_material'].' - Unidad: '.$rtip[$rowmp['pmp_tipo']]. $medidas . '</option>';
                  }
                echo '</select>
              </div>
            </div>

            <div hidden>  
              <input type="hidden" id="largo" name="largo" value="0">
              <input type="hidden" id="ancho" name="ancho" value="0">
              <input type="hidden"id="alto" value="0">
            </div>
          </div>';
          echo '
          <div class="mb-5 col-12 col-md-2 mt-2">  
            <label>Color:</label>
            <input type="color" id="color" value="#000000" class="form-control" data-toggle="tooltip" data-placement="top" name="color" style="height: 90%;" disabled>
          </div>';
          echo '<div class="col-12 col-md-2 mt-2 row">
          <div class="form-group">
            <label><b>Cantidad</b></label>
            <input type="number" min="1" step="1" value="1" name="cantidad" class="form-control" placeholder="Cantidad de artículos ligados" required></input >
          </div>
        </div> -->
        </div>
        ';

        echo '</div>';
  
      echo '</form>';
      }
        
        echo '<div class="row mt-4">';

        echo '
        <center>
          <div class="col-12 col-md-12 mb-2 card card-body">
            <table width="100%" class="table table-stripped table-bordered table-hover" id="myTableG"> 
              <thead class="bg-primary text-white">
                <tr>
                  <th width="40%" class="p-2">Composición</th>
                  <th width="25%" class="p-2">Cantidad</th>
                  <th width="10%" class="p-2">Acciones</th>
                </tr>
              </thead>
              <tbody>';
              $cantidadp = intval(busca($this->model->id, 'pr_ordenprod', 'po_id', 'po_cantidad'));
              $rtip = array('A' => 'Área', 'V' => 'Volumen', 'P' => 'Pieza');
              $sqlc = 'SELECT pmp_id, pmp_nmb, pmp_material, pmp_tipo, am_mp, am_id, am_cantidad, am_largo, am_ancho, am_alto FROM articulos_mermas INNER JOIN pr_materiaprima ON pmp_id = am_mp WHERE am_ordenp = "'.$this->model->id.'"';
              $resultc = setq($sqlc);
              if($resultc->num_rows == 0){
                echo '<tr> <td colspan="4" style="border: 1px solid #c5c5c5 !important;"><center>No hay elementos para mostrar</center></td></tr>';                
              } else {
                while($row = $resultc->fetch_array()){
                  $cantidadg = intval(busca($row['am_alto'], 'articulos_composicion', 'ac_modelo = "'.$this->model->modelo.'" AND ac_articulo = "'.$this->model->articulo.'" AND ac_mp = "'.$row['am_mp'].'" AND ac_ancho = "'.$row['am_ancho'].'" AND ac_largo = "'.$row['am_largo'].'" AND ac_alto', 'SUM(ac_cantidad)'));
                  $compo = "";
                  $cant = "";
                  $acciones = "";
                  $tipo = $row['pmp_tipo'];
                  $medidas = ' - Medidas: ';
                  if($tipo == "A") $medidas .= decimal_format($row['am_largo']).' X '.decimal_format($row['am_ancho']);
                  else if($tipo == "V") $medidas .= decimal_format($row['am_ancho']).' X '.decimal_format($row['am_largo']).' X '.decimal_format($row['am_alto']);
                  else $medidas = '';
                  $compo = $row['pmp_nmb'].' - Material: '. $row['pmp_material'].' - Unidad: '.$rtip[$row['pmp_tipo']] . "" . $medidas . '<br>';
                  
                  if($this->model->estatus != "C"){
                    $acciones = '<button class="btn btn-sm btn-danger" onclick="delcomp('.$row['am_id'].');"><i class="fas fa-times-circle" style="color:#fff"></i>Borrar</button>';
                    $reado = '';
                  } else{
                    $acciones = '';
                    $reado = 'readonly';
                  }

                  $max = ($cantidadg * $cantidadp);

                  $cant = '<input type="number" '.$reado.' onchange="updatecant('.$row['am_id'].', '.$max.');" min="1" step="1" max="'.$max.'" value="'.$row['am_cantidad'].'" id="cantidad'.$row['am_id'].'" name="cantidad'.$row['am_id'].'" class="form-control" placeholder="Cantidad de artículos ligados"></input >'."<br>"; 

                  echo '<tr>';
                  echo '<td>'.$compo.'</td>';
                  echo '<td>'.$cant.'</td>';
                  echo '<td>'.$acciones.'</td>';
                  echo '</tr>';                  
                }
              }
              echo '</tbody>
            </table>
         </div>
        </center>  
        ';
        ?>
          <script>
            function delcomp(idcomp){
              var c = confirm("¿Desea eliminar esta composición?");
              if(c){
                window.location.href="?modulo=ordenprod&accion=deletemerma&id=<?php echo $this->model->id; ?>&amid="+idcomp;
              }
            }
  
            function updatecant(id, max){
              var cant = document.getElementById('cantidad'+id).value;
              if(cant > max || cant < 1){
                Swal.fire({
                  title: "Atención",
                  text: "La cantidad ingresada tiene que ser mayor o igual que 1 y menor o igual que "+max,
                  icon: "warning"
                });
              } else{
                /* console.log("Cantidad: "+cant+" y máximo: "+max); */
                window.location.href="?modulo=ordenprod&accion=updatemerma&id=<?php echo $this->model->id; ?>&amid="+id+"&cantidad="+cant;
              }
            }
            function getmp(){
              var mp = document.getElementById('mp');
              var selectedOption = mp.options[mp.selectedIndex];

              // Acceder a los datos usando dataset
              var largomp = selectedOption.dataset.largo;
              var anchomp = selectedOption.dataset.ancho;
              var altomp = selectedOption.dataset.alto;

              var largo = document.getElementById('largo');
              var ancho = document.getElementById('ancho');
              var alto = document.getElementById('alto');

              var color = document.getElementById('color');
              $.ajax({
                type: "POST",
                url: "query/checkmp.php",
                data: {'mp': mp.value},
                success: function(dataRTN) {
                  data = JSON.parse(dataRTN);
                  color.value= data['color'];
                  largo.value = largomp;
                  ancho.value = anchomp;
                  alto.value = altomp;
                }
              });
  
            } 
            getmp();
          </script>
  
        <?php
  
      
  
      echo '</div></div>';
  
      ?>
      <script>
        function seltodo(){
          var elementos = document.getElementsByClassName("varcheck");
          var todos = document.getElementById("todos");
          
          elementos.forEach(function(elemento) {
            if(todos.checked){
              elemento.checked = true;
            } else {
              elemento.checked = false;
            }
          });
        }

        function cancelarord(){
          Swal.fire({
          icon: 'warning',
          title: '¿Deseas cancelar esta orden de produccion?',
          showCancelButton: true,
          showConfirmButton: true,
          focusConfirm: false,
          confirmButtonText: `<i class="fas fa-check" style="color:#fff"></i> Confirmar`,
          cancelButtonText: `<i class="fas fa-times-circle" style="color:#fff"></i> Cerrar`,
          cancelButtonColor: '#6c757d',
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = '?modulo=ordenprod&accion=cancelarorden&id=<?php echo $this->model->id; ?>';
            } else {
              Swal.close();
            }
          });
        }
      </script>
    <?php
    }

}
?>