<?php
/* ini_set('display_errors',1); */
class movimientos {
  var $model;
  var $view;

function __construct() {
  $this->model = new modelmovimientos(isset($obj));
}

function index() {
  if (!isset($_REQUEST['tipom']) || empty($_REQUEST['tipom'])) $_REQUEST['tipom'] = NULL;
  if (!isset($_REQUEST['motivo']) || empty($_REQUEST['motivo'])) $_REQUEST['motivo'] = NULL;
  if (!isset($_REQUEST['almacen']) || empty($_REQUEST['almacen'])) $_REQUEST['almacen'] = NULL;
  if (!isset($_REQUEST['fini']) || empty($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d', strtotime('-30 days'));
  if (!isset($_REQUEST['ffin']) || empty($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d');
  if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
  
  //die($_REQUEST['almacen']);

  $this->model->resultmov($_REQUEST['tipom'],$_REQUEST['motivo'],$_REQUEST['almacen'],$_REQUEST['page'],$_REQUEST['fini'],$_REQUEST['ffin']);
  $this->view = new viewmovimientos($this->model);
  $this->view->movimiento($_REQUEST['tipom'],$_REQUEST['motivo'],$_REQUEST['almacen'],$_REQUEST['page'],$_REQUEST['fini'],$_REQUEST['ffin']);
}

function insert() {
  if (!isset($_POST['estatus'])) $_POST['estatus'] = NULL;
  if (!isset($_POST['almacenes'])) $_POST['almacenes'] = NULL;
  if (!isset($_POST['almacendestino'])) $_POST['almacendestino'] = NULL;

  if(busca($_POST['tipo'],'movimientos','m_estatus = "N" AND m_tipo','COUNT(*)')){
    $idm = busca($_POST['tipo'],'movimientos','m_estatus = "N" AND m_tipo','m_id');

    echo '<script>
            var conf = confirm("Existe un movimiento de este tipo pendiente de aplicación\nNo podrás generar uno nuevo hasta aplicarlo\n¿Deseas ver el momiento?");
            if(conf == true){
              window.location.href="?modulo=movimientos&accion=show&id='.$idm.'&tipo='.$_POST['tipo'].'";
            }
            else{
              window.location.href="?modulo=movimientos&accion=index";
            }
          </script>';
    die();
  }

  if (($_POST['almacen'] == $_POST['destino']) && $_POST['tipo'] == "T") {
      echo alert_back('No se puede hacer un traspaso al mismo almancen', true);
  }
  else {

    $acronimo = busca($_SESSION['emp'],'empresas','e_id','e_siglas');
    $sqlm = 'SELECT COUNT(*) FROM movimientos WHERE m_folio LIKE "'.$_POST['tipo'].$acronimo.'%" AND m_empresa = "'.$_SESSION['emp'].'"';
    $resultm = setq($sqlm);
    list($maxcot) = $resultm->fetch_array();
    $maxcot++;
    
    $foliocot = ''.$_POST['tipo'].$acronimo.str_pad($maxcot,6,"0",STR_PAD_LEFT);

    $this->model->setData($_POST['tipo'], NULL, $_POST['almacen'], $_POST['estatus'], $_POST['destino'],$_POST['motivo'],$foliocot);
    $this->model->insert();

    redirect('?modulo=movimientos&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->id);
  }
}

function show() {
  $this->model->select($_GET['id'],$_GET['tipo']);
  $this->model->ResultArticulosMov($_GET['id'],$_GET['tipo']);
  $this->view = new viewmovimientos($this->model);
  $this->view->showmovimiento();
}

function insertdetalle(){
  if (!isset($id)) $id = NULL;
    $modelo = "";
    $articulo = getIDprod($_POST['producto']);
    if(!$articulo){ 
      list($articulo, $modelo) = getIDprodVar($_POST['producto']);
      /* die("Modelo: ".$modelo.", Producto: ".$_POST['producto']); */
      if(!$articulo){
      echo '<script>
              alert("Error: El producto buscado no esta registrado en tu base de datos, verifica la información");
              window.location.href="?modulo=movimientos&accion=show&id='.$_GET['id'].'&tipo='.$_GET['tipo'].'";
            </script>';
      die();
      }
    }

    $this->model->setDatad($_GET['tipo'], $_GET['id'], $_POST['cantidad'], $articulo, $modelo);
    $inventariado = busca($articulo,'articulos','a_id','a_inventariado');
    if($inventariado != 1){
      echo '<script>
            alert("El producto seleccionado: '.busca($comprueba,'articulos','a_id','a_nmb').' esta marcado como NO inventariado, no es posible agregarlo a movimientos.");
            window.location.href="?modulo=movimientos&accion=show&id='.$_GET['id'].'&tipo='.$_GET['tipo'].'";
          </script>';
      die();
    }

    if($this->model->tipo=="S" OR $this->model->tipo=="T"){
    $anteriores = 0;
    $almaceno = busca($this->model->mov,'movimientos','m_tipo = "'.$this->model->tipo.'" AND m_id','m_almacen');

    $existencia = existenciaModelo($articulo,$almaceno, $modelo);

    $sqlf = "";
    if($modelo != ""){
      $sqlf = ' AND md_modelo = "'.$modelo.'"';
    }
    $sqlan = 'SELECT SUM(md_cantidad) FROM movimientosd
              WHERE md_tipo = "'.$this->model->tipo.'" AND md_movimiento = "'.$this->model->mov.'"
              AND md_articulo = "'.$this->model->codigo.'"'.$sqlf;
    $resultan = setq($sqlan) or die($sqlan);
    list($anteriores) = $resultan->fetch_array();
    //$total = $existencia - $anteriores - $this->model->cantidad;
    $total = $existencia - ($anteriores-($anteriores - $this->model->cantidad));
    //die($total);
    if ($total < 0) {
      die(alert_back('No tienes existencias Suficientes en ' . busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb'), true));
      //echo '<script>alert("No tienes existencias Suficientes en '.busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb').' "); window.location.reload();</script>';
      //die();
    }
  }
  $this->model->insertdetalle();

  redirect('?modulo=movimientos&accion=show&tipo=' . $this->model->tipo . '&id=' . $_GET['id'].'&last='.$this->model->codigo);
}

function updatedetalle(){  
  $modelo = "";
  $articulo = busca($_GET['tipo'],'movimientosd','md_id = "'.$_GET['idprod'].'" AND md_movimiento = "'.$_GET['id'].'" AND md_tipo','md_articulo');
  $modelo = busca($_GET['tipo'],'movimientosd','md_id = "'.$_GET['idprod'].'" AND md_movimiento = "'.$_GET['id'].'" AND md_tipo','md_modelo');

  $this->model->setDatad($_GET['tipo'], $_GET['id'], $_POST['cantidad'], $articulo, $modelo);
  if($this->model->cantidad == 0){
    $sql = 'DELETE FROM movimientosd WHERE md_tipo = "'.$this->model->tipo.'" AND md_movimiento = "'.$_GET['id'].'" AND md_articulo = "'.$articulo.'" AND md_id = "'.$_GET['idprod'].'"';
    setq($sql);
  }
  else

  if($this->model->tipo=="S" OR $this->model->tipo=="T"){
    $anteriores = 0;
    
    $almaceno = busca($this->model->mov,'movimientos','m_tipo = "'.$this->model->tipo.'" AND m_id','m_almacen');
    $existencia = existencia($articulo,$almaceno);

    $sqlan = 'SELECT SUM(md_cantidad) FROM movimientosd
              WHERE md_tipo = "'.$this->model->tipo.'" AND md_movimiento = "'.$this->model->mov.'"
              AND md_articulo = "'.$this->model->codigo.'"';
    $resultan = setq($sqlan) or die($sqlan);
    list($anteriores) = $resultan->fetch_array();
    //$total = $existencia - $anteriores - $this->model->cantidad;
    $total = $existencia - ($anteriores-($anteriores - $this->model->cantidad));
    //die($total.' = '.$anteriores.' '.$existencia.' '.$this->model->cantidad.' '.($anteriores - $this->model->cantidad));
    if ($total < 0) {
      //die(alert_back('No tienes existencias Suficientes en ' . busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb'), true));
      echo '<script>alert("No tienes existencias Suficientes en '.busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb').' "); window.location.href="?modulo=movimientos&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->mov.'";</script>';
      die();
    }
  }

  $this->model->updatedetalle();

  redirect('?modulo=movimientos&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->mov.'&last='.$this->model->codigo);
}

function delprod(){
  $modelo = "";
  list($articulo, $modelo) = getIDprodVar($_GET['idprod']);
  $this->model->setDatad($_GET['tipo'], $_GET['id'], NULL, $articulo, $modelo);  
  $this->model->delprod($_GET['idprod']);

  redirect('?modulo=movimientos&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->mov.'&last='.$this->model->codigo);
}

function aplicarmov() {
  //$this->model->select($_GET['movimiento'],$_GET['tipo']);
  $this->model->aplicarmov($_GET['movimiento'],$_GET['tipo']);
 // $this->model->imprimir($_GET['movimiento'],$_GET['tipo']);
  redirect('?modulo=movimientos&accion=show&tipo=' . $_GET['tipo'] . '&id=' . $_GET['movimiento']);
}

function caducidades(){
  $id = $_GET['id'];
  $tipo = $_GET['tipo'];
/*   
  $this->model->ResultArticulosMov($id,$tipo);
  $caducidad = $this->model->caducacompleta($id,$tipo);

  if($caducidad == 1) */
    redirect("?modulo=movimientos&accion=aplicarmov&movimiento=".$id."&tipo=".$tipo);
 /*  else
    die(alert_back("Existen Articulos sin Fecha de Caducidad Registrada en el Movimiento!",true)); */
  //header("Location: ?modulo=movimientos&accion=insertfechacad&id=".$id."&tipo=".$tipo);

}

//Aqui se finalizan los metodos

    function browsedetallemov() {
        if (!isset($_GET['id']))
            $_GET['id'] = NULL;
        $this->model->resultdetallemov($_GET['id']);
        $this->view = new viewmovimientos($this->model);
        $this->view->browsedetallemov();
    }

    function delete() {
        $sqldm='DELETE FROM caducidadesm WHERE cm_movimiento = "'.$_GET['mov'].'" AND cm_tipo = "'.$_GET['tipo'].'" AND cm_articulo = "'.busca($_GET['idd'],'movimientosd','md_tipo = "'.$_GET['tipo'].'" AND md_movimiento = "'.$_GET['mov'].'" AND md_id','md_articulo').'"';
        setq($sqldm) or die($sqldm);

        $sql = 'DELETE FROM movimientosd WHERE md_tipo="' . $_GET['tipo'] . '" AND md_movimiento="' . $_GET['mov'] . '" AND md_id="' . $_GET['idd'] . '"';
        setq($sql) or die(mysql_error() . $sql);
        header('Location:?modulo=movimientos&accion=detalle&tipo=' . $_GET['tipo'] . '&id=' . $_GET['mov']);
    }

    function fecha() {
        $sql = 'UPDATE movimientosd SET md_fcaducidad="' . $_POST['fini'] . '" WHERE md_tipo="' . $_GET['tipo'] . '" AND md_movimiento="' . $_GET['mov'] . '" AND md_id="' . $_GET['id'] . '"';
        setq($sql) or die(mysql_error() . $sql);
        header('Location:?modulo=movimientos&accion=detalle&tipo=' . $_GET['tipo'] . '&id=' . $_GET['mov']);
    }

    function showmov() {
        $this->model->resultmov($_GET['id']);
        $this->view = new viewmovimientos($this->model);
        $this->view->showmov();
    }

function insertfechacad(){
  $id = $_GET['id'];
  $tipo = $_GET['tipo'];
  $this->model->resultCaducidad($id,$tipo);
  $this->view = new viewmovimientos($this->model);
  $this->view->fechaCad();
}

function insertacaducidad(){
    if($_POST)
  {
  foreach ($_POST as $clave=>$valor)
   		{
   		echo "POST El valor de $clave es: $valor<br>";
   		}
  }
     if($_GET)
  {
  foreach ($_GET as $clave=>$valor)
   		{
   		echo "GET El valor de $clave es: $valor<br>";
   		}
  }
  $id = $_GET['id'];
  $tipo = $_GET['tipo'];
  $lote = $_POST['lote'];
  $almacen=$_GET['almacen'];
  $cb=codigobarrascb($_POST['articulo']);
if(
   (busca($cb,'articulos','a_sku','a_lote')== 1 AND $_POST['lote']!="0") OR  busca($cb,'articulos','a_sku','a_lote')== 0
){

if($tipo=="S" || $tipo=="T"){
$exi=existenciaalmacencad($cb,$almacen,$_POST['fechacad']);
echo 'movimientos del producto >>>>>>>>>>>>>>>>>>>>>'.$exi;
   $sql = 'SELECT sum(cm_cantidad) as cantidad
   		    FROM caducidadesm
        WHERE cm_movimiento="'.$id.'" AND cm_tipo="'.$tipo.'"
            AND cm_articulo="'.$cb.'"
            AND cm_lote="'.$lote.'"
            AND cm_fcaducidad="'.$_POST['fechacad'].'"  ';
   $result=setq($sql) or die($sql);
   list($exil)=mysql_fetch_array($result);
   if(!$exil){$exil=0;}
  echo 'exi '.$exi.' exil'.$exil;
 $disponible=$exi-$exil;
 //die('disponible '.$disponible.' cantidad postr'.$_POST['cantidad']);
if($disponible < $_POST['cantidad']) {
      die(alert_back("movimientos Insuficientes para hacer el movimiento.     Verificar el fecha de caducidad",true));
}
if(busca($cb,"articulos","a_sku",'a_lote')==1){
// Reviso si el lote tiene las existncias suficientes
   $sql ='SELECT count(*) as exilo FROM lotes
          WHERE l_articulo="'.$cb.'" AND l_lote="'.$_POST['lote'].'"
          AND l_estatus="A"';
   $result=setq($sql) or die($sql);
   list($exilo) = mysql_fetch_array($result);

   $sql = 'SELECT sum(cm_cantidad) as cantidad
   		FROM caducidadesm
    WHERE cm_movimiento="'.$id.'"
        AND cm_lote="'.$_POST['lote'].'"
        AND cm_tipo="'.$tipo.'"
        AND cm_articulo="'.$cb.'"
        AND cm_fcaducidad="'.$_POST['fechacad'].'"  ';
   $result=setq($sql) or die($sql);
   list($exil2)=mysql_fetch_array($result);
   if(!$exil2){$exil2=0;}

 $disponiblel=$exilo-$exil2;
 echo 'disponible'.$disponiblel.' '.$_POST['cantidad'];
    if($disponiblel < $_POST['cantidad']) {
      die(alert_back("Lote erroneo para este artículo",true));
    }
}

}     /*fin de salidas*/

$sqlcaduc = 'SELECT (SUM(cm_cantidad)) FROM caducidadesm
             WHERE cm_articulo = "'.$cb.'" AND cm_movimiento = "'.$id.'"
             AND cm_tipo = "'.$tipo.'"';
$resultcaduc = setq($sqlcaduc) or die($sqlcaduc);
list($caduc) = mysql_fetch_array($resultcaduc);

$sqlrecibido = 'SELECT SUM(md_cantidad) FROM movimientosd
                WHERE md_articulo = "'.$cb.'" AND md_movimiento = "'.$id.'"
                AND md_tipo = "'.$tipo.'"';
$resultrecib = setq($sqlrecibido) or die($sqlrecibido);
list($recibido) = mysql_fetch_array($resultrecib);


if($caduc == NULL) $caduc = 0;
if($recibido == NULL) $recibido = 0;

$diferencia = $recibido - $caduc;
$pendiente = $diferencia - $_POST['cantidad'];

     if($pendiente >= 0)
           $this->model->InsertCaducidad($cb,$_POST['cantidad'],$id,$_POST['fechacad'],$_POST['lote'],$tipo);
      else
             die(alert_back("El número solicitado es mayor al pendiente",true));

    header("Location: ?modulo=movimientos&accion=detalle&id=".$id."&tipo=".$tipo.'&last='.$cb);
}
 }

 function deletecaducidad(){
   $caducidad = $_GET['idc'];
   $id = $_GET['movimiento'];
   $tipo = $_GET['tipo'];
   $articulo = busca($caducidad,'caducidadesm','cm_id','cm_articulo');

    $sqldel = 'DELETE FROM caducidadesm WHERE cm_id = "'.$caducidad.'"';
    setq($sqldel) or die($sqldel);

    header("Location: ?modulo=movimientos&accion=detalle&id=".$id."&tipo=".$tipo.'&last='.$articulo);

 }

}

/* -----------------------------------------------MODEL---------------------------------------------------------------------- */

class modelmovimientos {

function setData($tipo, $id, $almacenes, $estatus, $destino, $motivo, $folio) {
  $this->tipo = strtoupper($tipo);
  $this->id = strtoupper($id);
  $this->almacenes = strtoupper($almacenes);
  $this->estatus = strtoupper($estatus);
  $this->destino = strtoupper($destino);
  $this->motivo = strtoupper($motivo);
  $this->folio = strtoupper($folio);
}

function setDatad($tipo, $mov, $cantidad, $codigo, $modelo) {
  $this->tipo = strtoupper($tipo);
  $this->mov = strtoupper($mov);
  $this->cantidad = strtoupper($cantidad);
  $this->modelo = strtoupper($modelo);
  $this->codigo = strtoupper($codigo);
  $this->id = strtoupper($codigo);
}

function resultmov($tipom,$motivo,$almacen,$page, $fini, $ffin) { //filtro;
    $bloque =  50;
    $sql = 'SELECT * FROM movimientos
            WHERE DATE(m_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"';

    if($tipom) $sql.= ' AND m_tipo = "'.$tipom.'" ';
    if($almacen) $sql.= ' AND (m_almacen = "'.$almacen.'" OR m_almacendest = "'.$almacen.'")';
    $sql.='order by  `m_estatus` DESC, m_fecha DESC';
    $sql.=' LIMIT '.($page*$bloque).','.$bloque.'';
    $this->resultmd = setq($sql);
}

function select($id,$tipo) {
  $sql = 'SELECT * FROM movimientos WHERE m_tipo = "'.$tipo.'" AND m_id = "'.$id.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $this->tipo = $row['m_tipo'];
  $this->id = $row['m_id'];
  $this->motivo = $row['m_motivo'];
  $this->almacen = $row['m_almacen'];
  $this->almacendest = $row['m_almacendest'];
  $this->estatus = $row['m_estatus'];
  $this->fecha = $row['m_fecha'];
  $this->fechaap = $row['m_fechaap'];
  $this->documento = $row['m_documento'];
  $this->usuario= $row['m_usuario'];
}

function ResultArticulosMov($id,$tipo){
  $sqlart = 'SELECT * FROM movimientosd INNER JOIN articulos ON md_articulo = a_id WHERE
             md_movimiento = "'.$id.'" AND md_tipo = "'.$tipo.'"
             ORDER BY md_id DESC';
  $this->resultart = setq($sqlart);
}

function insert() {
  
  $this->id = getmax('m_id','movimientos','m_tipo = "'.$this->tipo.'"');

  if($this->destino==NULL || $this->tipo != "T") $this->destino="0";

  $sql = 'INSERT INTO movimientos SET
          m_tipo = "' . $this->tipo . '",
          m_id = "' . $this->id . '",
          m_empresa = "' . $_SESSION['emp'] . '",
          m_fecha = "' . date('Y-m-d') . '",
          m_almacen = "' . $this->almacenes . '",
          m_motivo = "' . $this->motivo . '",
          m_almacendest = "'.$this->destino.'",
          m_documento = "NULL",
          m_estatus = "P",
          m_usuario = "' . $_SESSION['uid'] . '",
          m_folio = "'.$this->folio.'"';
  setq($sql);
}

function comprueba($id, $tipo) {
  $sql = 'SELECT count(*) FROM movimientosd WHERE md_movimiento = "' . $id . '" AND md_tipo = "' . $tipo . '"';
  $result = setq($sql);
  list($cuenta) = $result->fetch_array();
  //if ($cuenta >= 1) $ok = 1;
  if ($cuenta >= 1) $ok = "OK";
  else $ok = 0;

  $sql = 'SELECT SUM(md_cantidad) FROM movimientosd INNER JOIN articulos ON md_articulo = a_id
          WHERE md_movimiento = "' . $id . '" AND md_tipo = "' . $tipo . '" AND a_id IN
          (SELECT DISTINCT(ap_producto) FROM activo_producto)';
  $result = setq($sql);
  list($activos) = $result->fetch_array();
  if(!$activos) $activos = 0;
  if ($activos >= 1){
    $activoscap = busca($id,'movimiento_activo','ma_tipo = "'.$tipo.'" AND ma_movimiento','COUNT(*)');
    //if($activoscap == $activos) $ok = 1; else $ok = 0;
    if($activoscap == $activos) $ok = "OK"; else $ok = 0;
  }
  //else $ok = 1;
  else $ok = "OK";


  $sql = 'SELECT a_id, a_inventariado, a_nmb FROM articulos INNER JOIN movimientosd ON md_articulo = a_id WHERE md_movimiento = "'.$id.'" AND md_tipo = "'.$tipo.'" ';
  $result = setq($sql);
  $nombres = '';
  while($row = $result->fetch_array()){
    if($row['a_inventariado'] == 0){
      $nombres .= $row['a_nmb'].' - ';
      $ok = $row['a_id'];
      //$ok = 0;
    }
  }

  return($ok);
}

function insertdetalle() {
  $cb = $this->codigo;
  if(busca($cb,'movimientosd','md_movimiento="' . $this->mov . '" AND md_tipo="' . $this->tipo . '" AND md_modelo = "'.$this->modelo.'" AND md_articulo','md_id')){    
    $mid=busca($cb,'movimientosd','md_movimiento="' . $this->mov . '" AND md_tipo="' . $this->tipo . '" AND md_modelo = "'.$this->modelo.'" AND md_articulo','md_id');
    $sql = 'UPDATE movimientosd SET
            md_cantidad = md_cantidad + '.$this->cantidad.'
            WHERE md_tipo = "' . $this->tipo . '"
            AND md_id = "' . $mid . '" AND md_movimiento = "' . $this->mov . '" AND md_articulo = "' . $cb . '" AND md_modelo = "'.$this->modelo.'"';
    setq($sql) or die($sql);
  }
  else{
    $this->id = getmax('md_id','movimientosd','md_tipo="'.$this->tipo.'" AND md_movimiento="'.$this->mov.'"');

    $sql = 'INSERT INTO movimientosd SET
            md_tipo = "' . $this->tipo . '",
            md_movimiento = "' . $this->mov . '",
            md_id = "' . $this->id . '",
            md_cantidad = "' . $this->cantidad . '",
            md_modelo = "' . $this->modelo . '",
            md_articulo = "' . $cb . '"';
    setq($sql) or die(mysql_error() . $sql);
  }
}

function updatedetalle(){
  $sql = 'UPDATE movimientosd SET
          md_cantidad = "' . $this->cantidad . '"
          WHERE
          md_tipo = "' . $this->tipo . '" AND
          md_movimiento = "' . $this->mov . '" AND
          md_articulo = "' . $this->id . '" AND
          md_modelo = "' . $this->modelo . '"';          
  setq($sql);
}

function delprod($idprod){
  $sql = 'DELETE FROM movimientosd WHERE md_tipo = "' . $this->tipo . '" AND
          md_movimiento = "' . $this->mov . '" AND
          md_id = "' . $idprod . '"';
  setq($sql);
}

function aplicarmov($id,$tipo) {  
  if($tipo == "S" || $tipo == "T"){
    $anteriores = 0;
    $almaceno = busca($id,'movimientos','m_tipo = "'.$tipo.'" AND m_id','m_almacen');
    $sqlxx = 'SELECT * FROM movimientosd WHERE md_movimiento = "'.$id.'" AND md_tipo = "'.$tipo.'"'; 
    $resultxx = setq($sqlxx);
    while($rowxx = $resultxx->fetch_array()){
      $articulo = $rowxx['md_articulo']; 
      $modelo = $rowxx['md_modelo']; 
      /* $existencia = existencia($articulo,$almaceno); */
      $existencia = existenciaModelo($articulo,$almaceno, $modelo);
      $sqlan = 'SELECT SUM(md_cantidad) FROM movimientosd
                WHERE md_tipo = "'.$tipo.'" AND md_movimiento = "'.$id.'"
                AND md_articulo = "'.$articulo.'" AND md_modelo = "'.$modelo.'"';
      $resultan = setq($sqlan) or die($sqlan);
      list($anteriores) = $resultan->fetch_array();
      //$total = $existencia - $anteriores - $this->model->cantidad;
      $total = $existencia - ($anteriores-($anteriores - $rowxx['md_cantidad']));
      //die($existencia.' - ('.$anteriores.' - ('.$anteriores.'-  '.$rowxx['md_cantidad'].')))';
      if ($total < 0) {
        $nmb = busca($articulo, 'articulos', 'a_id', 'a_nmb');
        if($modelo) $nmb.= ' '. busca($articulo, 'articulos_variantes', 'av_modelo = "'.$modelo.'" AND av_articulo', 'av_nmb');
        die(alert_back('No tienes existencias Suficientes en ' . $nmb, true));
        //echo '<script>alert("No tienes existencias Suficientes en '.busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb').' "); window.location.reload();</script>';
        //die();
      }
    }
  }
  
  $sqlcero = 'DELETE FROM existencias WHERE e_cantidad = 0';
  setq($sqlcero);
  $sql = 'SELECT * FROM movimientosd INNER JOIN movimientos ON (md_movimiento=m_id AND m_tipo = md_tipo)
          WHERE m_tipo="' . $tipo . '" AND m_id="'. $id.'" AND m_estatus = "P"';
  $result1 = setq($sql) or die($sql);
  while($rowe = $result1->fetch_array()){
    $existeal = existenciaModelo($rowe['md_articulo'],$rowe['m_almacen'], $rowe['md_modelo']);
    $apartado = existenciaxestatus($rowe['m_almacen'], 'VA');
    $vendido = existenciaxestatus($rowe['m_almacen'], 'VP');
    $apartadoant = $apartado[$rowe['md_articulo']][$rowe['md_modelo']] + $vendido[$rowe['md_articulo']][$rowe['md_modelo']];
    $reponerant = busca($rowe['md_modelo'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_articulo = "'.$rowe['md_articulo'].'" AND xp_modelo', 'xp_cantidad');
    $sqld = 'UPDATE movimientosd SET md_existenciaant = "'.$existeal.'", 	md_apartadosant = "'.$apartadoant.'", md_reponerant = "'.$reponerant.'" WHERE
             md_tipo = "' . $rowe['md_tipo'] . '" AND md_movimiento = "' .  $rowe['md_movimiento'] . '" AND
             md_articulo = "' . $rowe['md_articulo'] . '" AND md_modelo = "' . $rowe['md_modelo'] . '"';
    setq($sqld);
  }  

  $result1 = setq($sql) or die($sql);

  if ($tipo == "E") {
    while ($row = $result1->fetch_array()) {
      $articulo = $row['md_articulo'];      
      ajustaexistencia($articulo,$row['md_cantidad'],$row['m_almacen'],"E",null,$row['md_modelo'], $id);
    }
  }
  elseif ($tipo == "S") {
    $sqlmds = 'SELECT * FROM movimientosd
               INNER JOIN movimientos ON movimientosd.md_movimiento=movimientos.m_id
               WHERE m_tipo="' . $tipo . '" AND m_id=' . $id . ' AND md_tipo="' . $tipo . '" AND md_movimiento=' . $id;
    $result2 = setq($sqlmds);

    while ($rows = $result2->fetch_array()) {
      ajustaexistencia($rows['md_articulo'],$rows['md_cantidad'],$rows['m_almacen'],"S",null,$rows['md_modelo'], $id);
    }
  }
  elseif ($tipo == "T") {
    while ($rowt = $result1->fetch_array()) {
      if ($rowt['m_almacen'] != $rowt['m_almacendest'] && $rowt['m_estatus'] != "A") {
        $articulos = $rowt['md_articulo'];
        $origen = $rowt['m_almacen'];
        $destino = $rowt['m_almacendest'];
        $cantidad = $rowt['md_cantidad'];

        ajustaexistencia($rowt['md_articulo'],$rowt['md_cantidad'],$rowt['m_almacen'],"S",null,$rowt['md_modelo'], $id);
        ajustaexistencia($rowt['md_articulo'],$rowt['md_cantidad'],$rowt['m_almacendest'],"E",null,$rowt['md_modelo'], $id);

      }
      else {
        die(alert_back('El traslaso se esta realizando al mismo almacen', true));
      }
    }
  }

  $sql3 = 'UPDATE movimientos SET m_estatus = "A", m_fechaap = "'.date('Y-m-d H:i:s').'", m_usuarioap = "'.$_SESSION['uid'].'"
           WHERE m_tipo="' . $tipo . '" AND m_id="' . $id . '"';
  setq($sql3);
}


function ResultCaducidad($id,$tipo){
  $sqlcad = 'SELECT movimientosd.*,( md_cantidad - SUM(cm_cantidad) ) suma
             FROM movimientosd
             LEFT OUTER JOIN caducidadesm ON (md_movimiento = cm_movimiento AND md_articulo = cm_articulo AND md_tipo = cm_tipo)
             INNER JOIN articulos ON a_id = md_articulo
             WHERE md_movimiento = "'.$id.'" AND md_tipo = "'.$tipo.'" AND (a_caduca = "1" OR a_lote = "1")
             GROUP BY md_articulo ';
  $this->resultCad = setq($sqlcad) or die($sqlcad);
}

function caducacompleta($id,$tipo){
  $ok = 1;
  $sqlrec = 'SELECT md_tipo, md_articulo, md_cantidad
            FROM movimientosd
            LEFT OUTER JOIN articulos ON ( md_articulo = a_sku )
            WHERE md_movimiento = "'.$id.'" AND md_tipo = "'.$tipo.'"
            AND a_caduca = 1';
  $result = setq($sqlrec) or die($sqlrec);

  while($row = $result->fetch_array()){
    $caduco = busca($id,'caducidadesm','cm_tipo="'.$row['md_tipo'].'" AND cm_articulo = "'.$articulo.'" AND cm_movimiento','SUM(cm_cantidad)');
    if($caduco < $row['md_cantidad'])
        $ok = 0;
  }
  return($ok);
}

function InsertCaducidad($articulo,$cantidad,$movimiento,$caducidad,$lote,$tipo){

//die('articulo '.$articulo.' cantidad'.$cantidad.' movimientio'.$movimiento.' caducidad'.$caducidad.' lote'.$lote.' tipo'.$tipo);

$idd = busca($caducidad,'caducidadesm','cm_tipo="'.$tipo.'" AND cm_articulo = "'.$articulo.'" AND cm_movimiento = "'.$movimiento.'" AND cm_lote = "'.$lote.'" AND cm_fcaducidad','cm_id');

    if($idd == NULL){
      $sqlid = 'SELECT MAX(cm_id) FROM caducidadesm';
      $resultid = setq($sqlid) or die($sqlid);
      list($this->idc) = mysql_fetch_array($resultid);
      $this->idc++;
//die('busca '.busca($articulo,'articulos','a_sku','a_lote'));
if(busca($articulo,'articulos','a_sku','a_lote')==0){
$lote=0;
}

    $sqlexis = 'INSERT INTO caducidadesm SET
                cm_id = "'.$this->idc.'",
                cm_articulo = "'.$articulo.'",
                cm_cantidad = "'.$cantidad.'",
                cm_fcaducidad = "'.$caducidad.'",
                cm_lote = "'.$lote.'",
                cm_tipo = "'.$tipo.'",
                cm_movimiento = "'.$movimiento.'"';
  }
  else {
      $sqlexis = 'UPDATE caducidadesm SET cm_cantidad = cm_cantidad + "'.$cantidad.'"
                WHERE cm_id = "'.$idd.'" AND
                cm_articulo = "'.$articulo.'" AND
                cm_lote = "'.$lote.'" AND
                cm_tipo = "'.$tipo.'" AND
                cm_fcaducidad = "'.$caducidad.'" AND
                cm_movimiento = "'.$movimiento.'"';
  }
  setq($sqlexis) or die($sqlexis);

}

function clcompletaart($id,$articulo,$tipo){
  $ok = 1;

  //Obtengo el total de lotes que debo ingresar en esta recepcion
  $sqllote =   'SELECT sum(movimientosd.md_cantidad) as cantidad FROM movimientosd
                INNER JOIN movimientos ON (m_id = md_movimiento AND m_tipo = md_tipo)
                INNER JOIN articulos ON a_sku = md_articulo
                WHERE a_lote = "1" AND m_tipo = "'.$tipo.'" AND m_id= "'.$id.'" AND md_articulo = "'.$articulo.'"';
  $resultlote=setq($sqllote) or die($sqllote);
  list($cantidadl)=mysql_fetch_array($resultlote);
  if(!$cantidadl) $cantidadl = 0;

  //Obtengo el total de caducidades que debo ingresar en esta recepcion
  $sqllote =   'SELECT sum(movimientosd.md_cantidad) as cantidad FROM movimientosd
                INNER JOIN movimientos ON (m_id = md_movimiento AND m_tipo = md_tipo)
                INNER JOIN articulos ON a_sku = md_articulo
                WHERE a_caduca = "1" AND m_tipo = "'.$tipo.'" AND m_id='.$id.' AND md_articulo = "'.$articulo.'"';
  $resultlote=setq($sqllote) or die($sqllote);
  list($cantidadc)=mysql_fetch_array($resultlote);
  if(!$cantidadc) $cantidadc = 0;

  //Obtengo el no de lotes ya capturados de esta recepcion
  $sqltotc =   'SELECT SUM(cm_cantidad) FROM caducidadesm
                 WHERE cm_movimiento='.$id.' AND cm_tipo = "'.$tipo.'"
                 AND cm_articulo = "'.$articulo.'" AND cm_lote != "0"';
  $resultotl=setq($sqltotc) or die($sqltotc);
  list($totlote) = mysql_fetch_row($resultotl);
  if(!$totlote) $totlote = 0;

  //Obtengo el no de caducidades ya capturados de esta recepcion
  $sqltotc =   'SELECT SUM(cm_cantidad) FROM caducidadesm
                 WHERE cm_movimiento='.$id.' AND cm_tipo = "'.$tipo.'" AND cm_articulo = "'.$articulo.'" AND cm_fcaducidad != "0000-00-00"';
  $resultotc=setq($sqltotc) or die($sqltotc);
  list($totcad) = mysql_fetch_row($resultotc);
  if(!$totcad) $totcad = 0;

  if($totlote == $cantidadl && $cantidadc == $totcad) {$ok = "1";}
  else $ok = "0";

//  echo $articulo.'----- Nl '.$totlote.' Cl '.$cantidadl.' Nc '.$cantidadc.' CC '.$totcad.' ok->'.$ok.'<br>';
  return($ok);
}

function imprimir($movimiento, $tipo){
$tip=""; $ped=NULL; $sub=NULL;

if($tipo=="S"){$tip="SALIDA";}
if($tipo=="T"){$tip="TRASPASO";}
if($tipo=="E"){$tip="ENTRADA";}
$ped.='
             MOVIMIENTO
';
include_once('modulos/config.php');

$config = new modelconfig();
$config->select();

$ped.=''.sprintf("%".(20+round((strlen($config->id))/2,0))."s", $config->id).'
Movimiento:'.$movimiento .'
Genero:    '.busca(busca($movimiento,'movimientos','m_tipo="'.$tipo.'" AND m_id','m_usuario'),'usuarios','u_id','u_nmb').'
Tipo:      '.$tip.'
Motivo:    '.busca($movimiento,'movimientos','m_tipo="'.$tipo.'" AND m_id','m_motivo').'
Origen:    '.busca(busca($movimiento,'movimientos','m_tipo="'.$tipo.'" AND m_id','m_almacen'),'almacenes','a_id','a_nmb');

if($tipo=="T"){
$ped.='
Destino:   '.busca(busca($movimiento,'movimientos','m_tipo="'.$tipo.'" AND m_id','m_almacendest'),'almacenes','a_id','a_nmb');
}

$ped.='
FECHA: '.date("d-m-Y H:i:s").'
------------------------------------------
ARTICULO         CANT   COSTO    TOTAL
------------------------------------------';
    $sql = 'SELECT * FROM movimientosd INNER JOIN articulos ON a_id = md_articulo WHERE md_movimiento="'.$movimiento.'" AND md_tipo="'.$tipo.'"  AND md_cantidad != "0" order by a_nmb ASC';
    $result=setq($sql) or die($sql);

            if ($result){
            while ($row=mysql_fetch_array($result))
         	{
$producto = $row['a_nmb'];
$costo=$row['a_costos'];
$ped.='
'.sprintf("%-15s",substr($producto,0,12)).''.sprintf("%6s", number_format($row['md_cantidad'],2)).' '.sprintf("%8s", number_format($costo,2)).' '.sprintf("%8s", number_format($costo*$row['md_cantidad'],2)).'';

$sub+=$costo*$row['md_cantidad'];
            }
            }
 $ped.='
----------------------------------------';
$ped.='
                          Total: '.number_format($sub,2);

$ped.='
';

// imprimir ticket
$handle=fopen('movimiento.txt','w');
fwrite($handle,$ped);
fwrite($handle, "





");
fclose($handle);
system('copy movimiento.txt '.busca($_SERVER['REMOTE_ADDR'],'equipos','e_ip','e_impresora'));
system('copy '.busca($_SERVER['REMOTE_ADDR'],'equipos','e_ip','e_papel').' '.busca($_SERVER['REMOTE_ADDR'],'equipos','e_ip','e_impresora'));
system('copy movimiento.txt '.$impresora);
}

}

/* -----------------------------------------------VIEW---------------------------------------------------------------------- */

class viewmovimientos {
  var $model;

function __construct($model) {
  $this->model = $model;
  $this->tipomov = array("E"=>"Entrada","S"=>"Salida","T"=>"Trasapaso");
  $this->estilomov = array("E"=>"primary","S"=>"danger","T"=>"info");
}

function movimiento($tipom,$motivo,$almacenfil,$page, $fini, $ffin) {

?>
<script language="JavaScript">
function checkSubmitmovimiento() {
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function valida() {
  var strUser = document.getElementById("tipo").value;

  var fila = document.getElementById("almdestino");
 if (strUser == "T") {
    fila.style.display = "";
    document.getElementById("destino").setAttribute("required","required");
  }
  else {
    fila.style.display = "none";
    document.getElementById("destino").value = "";
    document.getElementById("destino").removeAttribute("required","");
  }
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
  $this->model->tipo = array("E" => "Entrada","S" => "Salida","T" => "Traspaso");
  $this->model->aestatus = array("A" => "Finalizado","P" => "En Captura");
  $this->model->nestatus = array("A" => "btn-success","P" => "bg-amber bg-darken-2");

  $entradas = "";
  $salidas = "";
  $traspasos = "";
  $todos = "";
  
  if($tipom =="E") $entradas = "active";
  elseif($tipom =="S") $salidas = "active";
  elseif($tipom =="T") $traspasos = "active";
  else $todos = "active";

  $accion = 'insert';
  if (isset($this->model->id)) {
    $accion = 'update';
  }

  $sql = 'SELECT * FROM almacenes WHERE a_empresa = "'.$_SESSION['emp'].'" AND a_estatus = "A"';
  $resultd = setq($sql);
  $grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
/*   if($grupo != "ADMIN"){
    $almacen = busca($_SESSION['uid'],'usuarios','u_id','u_almacen');
    $sql.= ' AND a_id = "'.$almacen.'" ';
  } */
  $result = setq($sql);
  $filtros = '';
  $botones = '';
  $botones .= '
  <a id="nuevo" class="btn btn-primary btn-sm" data-fancybox data-type="ajax" data-src="popup/setmovimiento.php" href="javascript:;">
    <i class="fa fa-plus"></i> Nuevo
  </a>
  <div class="btn-group">
    <button id="todos" onclick="estatussend(\'todos\');" type="button" class="btn btn-secondary btn-sm '.$todos.'">Todos</button>
    <button id="entradas" onclick="estatussend(\'entradas\');" type="button" class="btn btn-secondary btn-sm  '.$entradas.'">Entradas</button>
    <button id="salidas" onclick="estatussend(\'salidas\');" type="button" class="btn btn-secondary btn-sm  '.$salidas.' ?>">Salidas</button>
    <button id="traspasos" onclick="estatussend(\'traspasos\');" type="button" class="btn btn-secondary btn-sm  '.$traspasos.'">Traspasos</button>
  </div>
  ';

  $options = array(); // Aquí se almacenarán las opciones
  while ($row = $result->fetch_array()) {
    if ($row['a_id'] == $almacenfil) {
        $selected = "selected";
    } else {
        $selected = "";
    }
    
    $options[] = array(
        'value' => $row['a_id'],
        'selected' => $selected,
        'label' => $row['a_nmb']
    );
}

  
  $filtros .= '
  <form class="form-inline" role="form" method="post" id="filtro">
    <input type="text" id="tipom" name="tipom" value="<?php echo $tipom; ?>" hidden />
    <div class="mb-5">
      <label for="tipomov">Tipo de movimiento:</label>
      <select id="tipomov" required class="form-control" >
        <option value="">Todos</option>
          <option value="E">Entrada</option>
          <option value="S">Salida</option>
          <option value="T">Traspaso</option>
      </select>
    </div>
    <div class="mb-5">
      <label for="origen">Almacen origen:</label>
      <select id="origen" required class="form-control" >
        <option value="">Todos</option>';
        foreach ($options as $option) {
          $filtros .= '
          <option value="' . $option['value'] . '" ' . $option['selected'] . ' > ' . $option['label'] . '</option>';
      }
        $filtros .= '
      </select>
    </div>
    <div class="mb-5">
      <label for="destino">Almacen destino:</label>
      <select id="destino" required class="form-control" >
        <option value="">Todos</option>';
        foreach ($options as $option) {
          $filtros .= '
          <option value="' . $option['value'] . '" ' . $option['selected'] . ' > ' . $option['label'] . '</option>';
        }
        $filtros .= '
      </select>
    </div>
    <div class="mb-5">
      <label for="destino">Fecha de inicio:</label>
      <input type="date" value="'.$fini.'" id="fini" name="fini" class="form-control">
    </div>
    <div class="mb-5">
      <label for="destino">Fecha de fin:</label>
      <input type="date" value="'.$ffin.'" id="ffin" name="ffin" class="form-control">
    </div>
    <div class="mb-5">
      <label for="">Acciones: </label> <br>
      <button type="button" onclick="datamandar(0)" class="btn btn-info">
        <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
      </button>
      <button type="button"  onclick="datamandar(1)"  class="btn btn-warning">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-broom"></i> Limpiar
      </button>
    </div>
  </form>';

  ?>

    <script>
      function checerase(iduni){
        var conf = confirm("¿Deseas eliminar la marca seleccionada?\nPresiona Aceptar para borrarla");
        if(conf == true){
          window.location.href="?modulo=almacenes&accion=borrar&id=" + iduni;
        }
      }
      function verlineasneg(iduni){
        window.open("?modulo=articulos&accion=index&marca=" + iduni);
      }
    </script>
    <script>

          function datamandar(id){
            var origen = document.getElementById("origen");
            var destino = document.getElementById("destino");
            var tipomov = document.getElementById("tipomov");
            
            if(id == "1"){
              origen.value = "";
              destino.value = "";
              tipomov.value = "";
            }  

            //var table = $("#myTable").DataTable();
            $("#myTable").DataTable().clear().draw();
            $("#myTable").DataTable().destroy();

            
            var windowHeight = $(window).height();

            $("#myTable").DataTable( {
              paging: true,
              scrollY: windowHeight * 0.5,
              processing: true,
              serverside: true,
              language: {
                  url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
              },
              ajax: {
                url: "query/datatablemovimientos.php",
                type: "POST",
                datatype: "json",
                data:{ 
                  "origen": origen.value,
                  "destino": destino.value,
                  "tipomov": tipomov.value
                },
              },
              pageLength: "50",
              responsivePriority: 1
            });
          }
      function estatussend(tipo){
        var mov = document.getElementById("tipom");
        var form = document.getElementById("filtro");
        if(tipo == "todos"){
          mov.value = "";
          //form.submit();
          mandar(0);
        }else if(tipo == "entradas"){
          mov.value="E";
          //form.submit();
          mandar(0);
        }else if(tipo == "salidas"){
          mov.value="S";
          //form.submit();
          mandar(0);
        }else if(tipo == "traspasos"){
          mov.value="T";
          //form.submit();
          mandar(0);
        }
      }
    </script>
    <script>
      function mandar(id){
        document.getElementById("filtro").submit();
      }
    </script>

  <?php
  toolbar($_GET['modulo'],$botones,$filtros);
  echo '
    <div class="card mt-3">
      <div class="card-body">
      <div class="table table-responsive text-medium">
        <center>
        <table class="table table-hover table-striped" id="myTable">
        <thead class="thead bg-blue bg-darken-3 ">
          <tr>
            <th>Folio Movimiento</th>
            <th>Tipo</th>
            <th>Mótivo</th>
            <th>Fecha de Alta</th>
            <th>Fecha de aplicación</th>
            <th>Generó </th>
            <th>Aplicó </th>
            <th>Almacen Origen</th>
            <th>Almacen Destino</th>
            <th>Estatus</th>
            <th></th>
          </tr>
        </thead>';
  while($row = $this->model->resultmd->fetch_array()){
    echo '<tr>
            <th>'.$row['m_folio'].'</th>
            <th>'.$this->tipomov[$row['m_tipo']].'</th>
            <td>'.$row['m_motivo'].'</td>
            <td>'.date('d-m-Y',strtotime($row['m_fecha'])).'</td>';
            if($row['m_estatus'] == "A") $fechaapli = date('d-m-Y H:i:s',strtotime($row['m_fechaap']));
            else $fechaapli = "";
            echo '
            <td>'.$fechaapli.'</td>
            <td>'.$row['m_usuario'].'</td>
            <td>'.$row['m_usuario'].'</td>
            <td>'.busca($row['m_almacen'],'almacenes','a_id','a_nmb').'</td>
            <td>'.busca($row['m_almacendest'],'almacenes','a_id','a_nmb').'</td>
            <td class="'.$this->model->nestatus[$row['m_estatus']].'">'.$this->model->aestatus[$row['m_estatus']].'</td>
            <td>
              <a href="?modulo=movimientos&accion=show&tipo='.$row['m_tipo'].'&id='.$row['m_id'].'">
                <button type="button" class="btn-sm btn-info btn"><i class="fa fa-eye"></i> Detalle</button>
              </a>';
      if($row['m_estatus'] == "A")
        echo '<a target="_BLANK" href="formats/pdfmovimiento.php?tipo='.$row['m_tipo'].'&id='.$row['m_id'].'">
                <button type="button" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Imprimir</button>
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
          ordering: false,
      });
    });
  </script>
  
  ';

}

function showmovimiento(){
  ?>
    <script>
      $(document).ready(function() {
          $('#producto').on('keyup', function() {
        if (event.keyCode == 38 || event.keyCode == 40){
              //console.log('chi');
        }else{
            var key = $(this).val();
            var dataString = 'producto='+key;
          $.ajax({
            type: "POST",
            url: "query/suggestproducts.php",
            data: dataString,
            success: function(data) {
              //Escribimos las sugerencias que nos manda la consulta
              $('#suggestions').fadeIn(1000).html(data);
              //Al hacer click en alguna de las sugerencias
              $('.suggest-element').on('click', function(){
                //Obtenemos la id unica de la sugerencia pulsada
                var id = $(this).attr('id');
                //Editamos el valor del input con data de la sugerencia pulsada
                $('#producto').val($('#'+id).attr('data'));
                //Hacemos desaparecer el resto de sugerencias
                $('#suggestions').fadeOut(1000);
                $('#cantidad').focus();
                $('#cantidad').select();
                return false;
              });
            }
          });
        }
        });
      });
    </script>
  <?php

  if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
  if ($_GET['alerta'] == 1) {
    echo alert("El articulo no Existe", true);
  }


  echo '
    <script>
      function delprod(idprod){
        var conf = confirm("¿Deseas borrar el producto del movimiento de almacen?");
        if(conf == true){
          window.location.href="?modulo=movimientos&accion=delprod&id='.$this->model->id.'&tipo='.$this->model->tipo.'&idprod=" + idprod;
        }
      }
      function aplicamov(){
        var conf = confirm("Al aplicar este movimiento, se verá afectado el inventario\n¿Deseas continuar?");
        if(conf == true){
          window.location.href="?modulo=movimientos&accion=caducidades&id='.$this->model->id.'&tipo='.$this->model->tipo.'&almacen='.$this->model->almacen.'";
        }
      }
    </script>';

  $botones = '';
  $botones .= '
  <a href="?modulo=movimientos&accion=index" accesskey="">
    <button type="button" class="btn btn-warning btn-sm"><i class="fa fa-arrow-left"></i> Atras </button>
  </a>';

  $comprueba = $this->model->comprueba($this->model->id, $this->model->tipo);
  
  if ($comprueba == "OK" && $this->model->estatus != "A") {
    $botones .= '<button class="btn btn-primary btn-sm" onclick="aplicamov()"><i class="fa fa-check"></i> Aplicar</button>';
  }elseif($comprueba !== 0  && $this->model->estatus != "A"){
    
    echo '<script>
          alert("El producto seleccionado: '.busca($comprueba,'articulos','a_id','a_nmb').' esta marcado como NO inventariado, no es posible agregarlo a movimientos.");
        </script>';
    /*
    echo '<script>
          alert("El producto seleccionado dentro de el movimiento, no está catalogado como inventariado, por favor verifica antes de poder aplicar el movimiento.");
        </script>'; */
  }
  if($this->model->estatus == "A")
  $botones .= '<a target="_BLANK" href="formats/pdfmovimiento.php?tipo='.$this->model->tipo.'&id='.$this->model->id.'">
            <button type="button" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Imprimir</button>
          </a>';

  if($this->model->estatus == "A"){
    //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
  }

  if($this->model->tipo == "T") $dest = ' <i class="icon-arrow-right3"></i> '.busca($this->model->almacendest,'almacenes','a_id','a_nmb'); else $dest = "";

  if($this->model->estatus == "P") $leyenda = 'Añadir productos a '.$this->tipomov[$this->model->tipo];
  else  $leyenda = 'Lista de productos en '.$this->tipomov[$this->model->tipo];


  $readon = "";
  $exisantm = "";
  $repuestos = "";
  $apartantm = "";

  if($this->model->tipo == "T") $tipodemov = "Traspaso";
  elseif($this->model->tipo == "S") $tipodemov = "Salida";
  elseif($this->model->tipo == "E") $tipodemov = "Entrada";

  toolbar($_GET['modulo'],$botones);


  echo '<div class="card mt-3">';
  if($this->model->estatus == "P")
    echo '
          <div class="card-body">
            <form method="post" class="row" autocomplete="off" action="?modulo=movimientos&accion=insertdetalle&id='.$this->model->id.'&tipo='.$this->model->tipo.'">
              <div class="row"> 
                <div class="col-md-6">
                  <h4 class="card-header">'.$leyenda.'
                  -
                  '.$this->model->motivo.' </h4>
                </div>
                <div class="col-md-3 alert-'.$this->estilomov[$this->model->tipo].'" style="display: flex; align-items: center;">'.$tipodemov.'</div>
                <div class="col-md-3 alert-info" style="display: flex; align-items: center;" >Almacen: '.busca($this->model->almacen,'almacenes','a_id','a_nmb').' '.$dest.'</div>
              </div>
              <div class="col-8 col-md-6">
                <div class="mb-5">
                  <label for="agregar">Producto</label><br>
                  <div class="input-group">
                    <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control " required="required"  autofocus >
                    <a class="input-group-addon" href="popup/productos-buscar.php?id='.$this->model->id.'&from=ordenesc" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                      <button accesskey="B" type="button" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </a>
                  </div>
                 <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: scroll;"></div>
                </div>
              </div>
              <div class="col-4 col-md-3">
                <div class="mb-5">
                  <label for="cant">Cantidad</label><br>
                  <input type="number" min="0" max="9999999" value="1" step="0.01" name="cantidad" id="cantidad" placeholder="Cantidad" class="form-control" required="required" />
                  <input id="importe" hidden>
                  <input id="costo" hidden>
                  <input id="linean" hidden>
                </div>
              </div>
              <div class="col-12 col-md-2">
                <div class="mb-5">
                <label>.</label><br>
                  <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Agregar producto</button>
                </div>
              </div>
            </form>';
  else{
    echo '<div class="mt-1 col-8 col-md-8 alert-'.$this->estilomov[$this->model->tipo].'" style="display: flex; align-items: center;">
            '.$leyenda.' - '.$this->model->motivo.'
          </div>';
    $readon = 'readonly="readonly"';
    $exisantm = "Existencia antes del movimiento";
    $repuestos = "Repuestos en almacen";
  }

  echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
        <thead class="thead bg-primary text-white">
          <tr>
            <th>Artículo</th>
            <th>Piezas del movimiento</th>
            <th>Existencia Actual</th>
            <th>'.$exisantm.'</th>
            <th>'.$repuestos.'</th>
          </tr>
        </thead>';
  while($row = $this->model->resultart->fetch_array()){
    if($row['md_modelo'] != NULL){
      $mdmodelo = $row['md_modelo'];
      $nombre = $row['a_nmb']. " " . busca($row['md_modelo'], "articulos_variantes", "av_modelo", "av_nmb");
    } else{
      $mdmodelo = "";
      $nombre = $row['a_nmb'];
    }

    $apartado = existenciaxestatus($this->model->almacen,'VA');
    $vendido = existenciaxestatus($this->model->almacen,'VP');
    $apar = $apartado[$row['md_articulo']][$row['md_modelo']]+$vendido[$row['md_articulo']][$row['md_modelo']];
    $reponer = busca($row['md_modelo'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_articulo = "'.$row['md_articulo'].'" AND xp_modelo', 'xp_cantidad');
    $existencia = number_format(existenciaModelo($row['md_articulo'],$this->model->almacen, $row['md_modelo']) + $apar - $reponer, 2);
    if($existencia < 0) $existencia = 0;
    
    echo '<tr><form method="post" action="?modulo=movimientos&accion=updatedetalle&id='.$this->model->id.'&tipo='.$this->model->tipo.'&idprod='.$row['md_id'].'">
              <td>'.$nombre;

    if(busca($row['a_id'],'activo_producto','ap_producto','COUNT(*)') > 0){
      $numact = busca($this->model->id,'movimiento_activo','ma_idm = "'.$row['md_id'].'" AND ma_tipo = "'.$this->model->tipo.'" AND ma_movimiento','COUNT(*)');

      echo '<br><a href="popup/elijeactivos-movimientos.php?modulo='.$_GET['modulo'].'&accion=seleccionar&movimiento='.$this->model->id.'&idmov='.$row['md_id'].'&tipom='.$row['md_tipo'].'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
              <button type="button" class="btn btn-sm bg-teal bg-darken-2 text-white"><i class="icon-cup"></i> Seleccionar activos
                 <div class="tag tag-pill tag-danger">'.$numact.'</div>
              </button>
            </a>';
    }

    echo '</td>
              <th>
                <input type="number" value="'.number_format($row['md_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control" required="required" onfocus="this.select();" '.$readon.' />
              </th>
              <th>'.$existencia.'</th>
              <th>';

    if($this->model->estatus == "P")
          echo '<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-redo"></i> Actualizar</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="delprod('.$row['md_id'].')"><i class="fa fa-trash"></i> Borrar</button>';
    else{
      $existencia = $row['md_existenciaant'] + $row['md_apartadosant']; // - $row['md_reponerant']
      if($existencia < 0) $existencia = 0;
      echo number_format($existencia, 2);
    }
      

    echo '</th>
    <th>';

    if($this->model->estatus == "P")
      echo '';
    else
      echo $row['md_repuestos'];

      echo '</th>
      <th>';

      echo '</th>';
      echo '</form></tr>';
  }
  echo '</table></div>
  </div>';
  ?>
  <script>
    
//var x = -1;// Obtener la lista, para recorrer cada elemento
let listGroup = document.getElementById('suggestions');
// Asignar evento al campo de texto
document.querySelector('#producto').addEventListener('keydown', e => {
  if(!listGroup) {
      return; // No existe la lista
  }

  // Obtener todos los elementos
  let items = listGroup.querySelectorAll('a');
  var a = document.getElementById("suggestions");
  // Saber si alguno está activo
  let actual = Array.from(items).findIndex(item => item.classList.contains('active'));
  //let actual = 0;
  // Analizar tecla pulsada
  //console.log(actual);
  //console.log(items[actual]);
  if(e.keyCode == 13) {
      // Tecla Enter, evitar que se procese el formulario
      e.preventDefault();
      // ¿Hay un elemento activo?
      if(items[actual]) {
          // Hacer clic
          items[actual].click();
      }
  } if(e.keyCode == 38 || e.keyCode == 40) {
      // Flecha arriba (restar) o abajo (sumar)
      if(items[actual]) {
          // Solo si hay un elemento activo, eliminar clase
          items[actual].classList.remove('active');
          //items[actual].className += " active";
      }
      // Calcular posición del siguiente
      if((e.keyCode == 38)){
        a.scrollTop -= ((items[actual].clientHeight - 1));
        actual += -1;
      }else{
        if(actual > 1) a.scrollTop += ((items[actual-2].offsetHeight + 1));
        
        actual += 1;
      }
      //actual += (e.keyCode == 38) ? -1 : 1;
      // Asegurar que está dentro de los límites
      if(actual < 0) {
          actual = 0;
      } else if(actual >= items.length) {
          actual = items.length - 1;
      } 
      // Asignar clase activa
      items[actual].classList.add('active');
      items[actual].setAttribute('autofocus','');
      //items[actual].className += " active";
  }
  
});
// En la función donde generas la lista debes activar evento clic para cada elemento
// Para este ejemplo se hace manual
listGroup.querySelectorAll('a').forEach(a => {
  a.addEventListener('click', e => {
      // Asignar valor al campo
      document.querySelector('#producto').value = e.currentTarget.textContent;
      // Aquí deberías cerrar la lista y/o eliminar el contenido
  });
});
  </script>
  <?php

}

function movimientosd() {
        $estatus = busca($_GET['id'], 'movimientos', 'm_tipo="' . $_GET['tipo'] . '" AND m_id', 'm_estatus');
        if ($estatus == "A") {
            if ($_GET['tipo'] == "E") {
                echo '<center><span style="font-size: 16px"><b>Entrada aplicada<br><br></b></span></center>';
            }
            if ($_GET['tipo'] == "S") {
                echo '<center><span style="font-size: 16px"><b>Salida aplicada<br><br></b></span></center>';
            }
            if ($_GET['tipo'] == "T") {
                echo '<center><span style="font-size: 16px"><b>Traspaso aplicada<br><br></b></span></center>';
            }
        } else {
            ?>

<script language="JavaScript">
function checkSubmitmovimientosd() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
</script>
            <br><br><br>
            <center>
                <table width="15%" >
                    <form autocomplete="off" method=post action=?modulo=movimientos&accion=insertdetalle&tipo=<?php echo $_GET['tipo'] ?>&mov=<?php echo $_GET['id'] ?> onsubmit="return checkSubmitmovimientosd();">
                        <tr><td>Cantidad</td><td><input type=text name=cantidad size="4" maxlength="4" value="<?php if (!isset($this->model->cantidad)) $this->model->cantidad = 1; echo $this->model->cantidad; ?>" required></td></tr>
                        <tr><td>Codigo de Barras</td><td><input type=text name=codigo size="20" maxlength="20" value="<?php if (!isset($this->model->codigo)) $this->model->codigo = NULL; echo $this->model->codigo; ?>" required autofocus></td></tr>
                        <tr><td colspan="2"><center><input type=submit name=save id="guardar" class="botont" value="" ></td></center></tr>
                    </form>
                </table></center>
            <?php
        }
    }

function browsedetallemov($almacen) {
  $almacend=NULL;
  $estatus = busca($_GET['id'], 'movimientos', 'm_tipo = "' . $_GET['tipo'] . '" AND m_id', 'm_estatus');
  $almaceno = busca($_GET['id'], 'movimientos', 'm_tipo = "' . $_GET['tipo'] . '" AND m_id', 'm_almacen');
  if($_GET['tipo'] == "T")
    $almacend = busca($_GET['id'], 'movimientos', 'm_tipo = "' . $_GET['tipo'] . '" AND m_id', 'm_almacendest');
  echo '<table width="100%" border="0" class="lista"><thead>
  <tr>
  <th>SKU</th>
  <th>Codigo de Barras</th>
  <th>Articulo</th>
  <th>Existencia</th>';
  echo '<th>Cantidad</th>';
  if ($estatus == "A" AND $_GET['tipo'] != "T"){
    echo'<th>Existencia Actual</th>';
  }
  elseif($estatus == "A"){
    echo '<th>Existencia Actual Destino</th>';
  }
  if ($estatus != "A") {
    echo '<th>Borrar</th>';
  }
?>
<script type="text/javascript">
$(document).ready(function() {
  $("div#divlote a").fancybox({
    scrolling: 'no',
    'onComplete': function() {
      $('#fechacad').focus();
    },
  });
  $("#dos").click();
});
</script>
<?php
  if(isset($_GET['last'])){
    $cantidading = busca($_GET['last'],'movimientosd','md_movimiento = "'.$_GET['id'].'" AND md_tipo = "'.$_GET['tipo'].'" AND md_articulo','SUM(md_cantidad)');
    $ingresados = busca($_GET['last'],'caducidadesm','cm_movimiento = "'.$_GET['id'].'" AND cm_tipo = "'.$_GET['tipo'].'" AND cm_articulo','SUM(cm_cantidad)');

    if(!$ingresados) $ingresados = 0;
    if(!$cantidading) $cantidading = 0;

    if((busca($_GET['last'],'articulos','a_sku','a_caduca') == 1 || busca($_GET['last'],'articulos','a_sku','a_lote') == 1) && $cantidading > $ingresados){
      echo '<div id="divlote" class="divlote">
      <a title="Ingresar caducidades y lotes" href="popup/ingresalote.php?mov=M&id='.$_GET['id'].'&idd='.$_GET['last'].'&tipo='.$_GET['tipo'].'&almacen='.$almacen.'" id="dos">
      <span style="z-index:-1;">&nbsp;</span></a>
      </div>';
    }
  }
  echo '</tr></thead>';

  while ($row = mysql_fetch_array($this->model->resultdet)) {
    $articulo = busca($row['md_articulo'],'articulos','a_sku','a_id');
    if (!isset($fini))
      $fini = NULL;
    $existencia = totalexistencia($articulo);
    $existencia = existenciaalmacen($articulo,$almaceno);
    $existenciad = existenciaalmacen($articulo,$almacend);
    //die('existencia'.$existencia.' existenciad '.$existenciad.' almacend '.$almacend);
    if($existencia<1){$existencia=0;}

    if($this->model->clcompletaart($_GET['id'],$articulo,$_GET['tipo']) == 1  OR $estatus == "A")
      $color = "#33CC33";
    else
      $color = "#CC0000";

//die(($estatus != "A" OR $_GET['tipo'] != "E") AND (busca($row['md_articulo'],'articulos','a_','a_caduca') == "1" OR busca($row['md_articulo'],'articulos','a_id','a_lote') == "1"));
    if(($estatus != "A" OR $_GET['tipo'] != "E") AND (busca($row['md_articulo'],'articulos','a_sku','a_caduca') == "1" OR busca($row['md_articulo'],'articulos','a_sku','a_lote') == "1"))
      echo '<tr><td style="background:'.$color.';"><a id="popup" href="popup/ingresalote.php?mov=M&id='.$row['md_movimiento'].'&idd='.$row['md_articulo'].'&tipo='.$row['md_tipo'].'&almacen='.$almacen.'" style="color:#FFFFFF;">'.strtoupper(busca($row['md_articulo'], 'articulos', 'a_sku', 'a_sku')).'</a></td>';
    else
      echo '<tr><td style="background:'.$color.';color:#FFFFFF;">'.strtoupper(busca($row['md_articulo'], 'articulos', 'a_sku', 'a_sku')).'</td>';
    echo '<td><center>' .busca($row['md_articulo'], 'articulos', 'a_sku', 'a_id'). '</center></td>
    <td style="word-break: break-all;"><center><a href=?modulo=productos&accion=show&id=' . $row['md_articulo'] . ' TARGET = "_blank" onclick="this.onclick = function(){return false;}">' . busca($row['md_articulo'], 'articulos', 'a_sku', 'a_nmb') . '</a></center></td>
    <td><center>'.$existencia.'
    <td><center>' . $row['md_cantidad'] . '</center></td>';    //die( '>>>>>>>>>>>>>>>>'.busca($row['md_articulo'], 'articulos1', 'a_id', 'a_nmb'));
    if ($estatus == "A" AND $_GET['tipo'] != "T"){
      echo'<th><center>'.$existencia.'</th>';
    }
    elseif ( $estatus =="A"){
      echo'<th><center>'.$existenciad.'</th>';
    }
    $estatus = busca($_GET['id'], 'movimientos', 'm_tipo="' . $_GET['tipo'] . '" AND m_id', 'm_estatus');
    if (busca($_GET['id'], 'movimientos', 'm_tipo = "' . $_GET['tipo'] . '" AND m_id', 'm_estatus') != "A") {
      echo '<td><a href=?modulo=movimientos&accion=delete&idd=' . $row['md_id'] . '&tipo=' . $_GET['tipo'] . '&mov=' . $_GET['id'] . '  onclick="alert(Seguro que quiere Eliminar");"
      value="Confirmation Alert"><center><input type="button" value="" id="borrar" class="botont"></center></a></td></tr>';
    }
  }
  echo '</table>';
}


function fechaCad(){
  $con = 'SELECT a_nmb FROM articulos
  INNER JOIN movimientosd ON articulos.a_id=movimientosd.md_articulo
  WHERE a_estatus=A AND md_movimiento="'.$_GET['id'].'"
  AND md_tipo="'.$_GET['tipo'].'" ORDER BY a_nmb'; //consulta para seleccionar las palabras a buscar, esto va a depender de su base de datos
  $query = setq($con) OR die($con);
?>
<script>
            $(function() {
        <?php
        while ($row = mysql_fetch_array($query)) {//se reciben los valores y se almacenan en un arreglo
            $elementos[] = '"' . $row['a_nmb'] . '"';
        }
        $arreglo = implode(", ", $elementos); //junta los valores del array en una sola cadena de texto
        ?>
                var availableTags = new Array(<?php echo $arreglo; ?>);//imprime el arreglo dentro de un array de javascript
                $("#tags").autocomplete({
                    source: availableTags
                });
            });
</script>
<?php
  echo '<div class="div2" id="bboton01">';
    echo '<a href="?modulo=movimientos&accion=detalle&id='.$_GET['id'].'&tipo='.$_GET['tipo'].'" accesskey="r"><input type="button" value=" " id="atras" class="botont"></a>';
  if($this->model->caducacompleta($_GET['id'],$_GET['tipo']) == 1){
    echo '<a href="?modulo=movimientos&accion=aplicarmov&movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" title="Aplicar recepcion"  acceskey="A" onclick="this.onclick = function(){return false;}"><input type="button" value="" id="aplicar" class="botont"></a>';
     echo '</div>
    </div>';
  }
  else{
    echo '</div>
    </div>';
?>
<script language="JavaScript">
function checkSubmitguardar() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
</script>
<?php

          echo '<form autocomplete="off" name="insertacaducidad" method="post" action="?modulo=movimientos&accion=insertacaducidad&id='.$_GET['id'].'&tipo='.$_GET['tipo'].'" onsubmit="return checkSubmitguardar();">';
          echo '<table class="lista" width="85%" style="padding-left:15%"><thead>
<tr>
          <th colspan=4>SOLICITUD DE FECHA DE CADUCIDAD</th>
</tr>
          <tr>
          <th width="25%">Articulos</th>
          <th>Cantidad</th>
          <th>Fecha de Caducidad</th>';
    if($_GET['activalote']==1){
          echo'<th>Lote</th>';
     }
          echo'</tr></thead><tbody>';
          echo '<tr>';
          $manana = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
           if($_GET['activalote']==1){
            echo '<td><input type="text" size="20" id="tags" name="articulo" value="'.$_GET['articulo'].'" required></td>';
            echo '<td><input type="text" size="7" id="cantidad" name="cantidad" value="'.$_GET['cantidad'].'" required ></td>';
            echo '<td><input type="date" size="7" id="fechacad" name="fechacad" value="'.$_GET['fechacad'].'" required ></td>';
            echo '<td><input type="text" size="10" id="lote" name="lote" value="" autofocus></td>';
           }
           else{
            echo '<td><input type="text" size="20" id="tags" name="articulo" value="'.$articulo.'" required autofocus></td>';
            echo '<td><input type="text" size="7" id="cantidad" name="cantidad" value="" required ></td>';
            echo '<td><input type="date" size="7" id="fechacad" name="fechacad" value="" required ></td>';
            echo '<input type="hidden" size="10" id="lote" name="lote" value="0">';
           }
            echo '<tr><td colspan="6"><center><input type="submit" value="" id="guardar" class="botont"></td></center></tr>';
            echo '</tbody></table></form>';

}

echo '<table width="100%" >
<tr><td valign="top">';

    $sql = 'SELECT * FROM caducidadesm WHERE cm_movimiento = "'.$_GET['id'].'" AND cm_tipo = "'.$_GET['tipo'].'"';
    $caducicomp = setq($sql) or die($sql);

    echo '
    <table class="lista" width="100%" >';
    echo '<thead>
   <tr><th colspan="6">Capturados</th></tr>
    <tr>
     <th>ID</th>
    <th><center>Articulo</th>
    <th><center>Cantidad</th><
    <th><center>Fecha de Caducidad</th>';
    if($_GET['activalote'] == 1)
      echo '<th><center>Lote</th>';

    echo '<th></th>
    </tr></thead><tbody>';
    $capturados=0;
    while($rowd = mysql_fetch_array($caducicomp)){
          echo '<td>'.$rowd['cm_articulo'].'</b></td>';
          echo '<td>'.busca($rowd['cm_articulo'],'articulos','a_id','a_nmb').'</b></td>';
          echo '<td>'.number_format($rowd['cm_cantidad'],2).'</td>';
          echo '<td>'.cambiar_fechaa($rowd['cm_fcaducidad']).'</td>';
          echo '<td>'.$rowd['cm_lote'].'</td>';
          echo '<td><a href="?modulo=movimientos&accion=deletecaducidad&idc='.$rowd['cm_id'].'&movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'"  onclick="this.onclick = function(){return false;}"><input type="button" value="" id="borrar" class="botont"></a></td></tr>';
      $capturados+=$rowd['cm_cantidad'];
      }
echo '</tbody></table>';


echo'</td><td valign="TOP">';
//Mostrar los que FALTAN
          echo '<table class="lista" width="100%" ><thead>
          <tr><th colspan="4">Pendientes</th> </tr>
          <tr>
          <th><center>Articulo</th>
          <th><center>Recibido</th>
          <th><center>Pendiente</th>
          </tr></thead><tbody>';

    while($row = mysql_fetch_array($this->model->resultCad)){
$capturados = 0;
$completos = '';

          if($row['suma'] == NULL )
            $pendientes = $row['md_cantidad'];
          else
            $pendientes = $row['suma'];
        if($pendientes != 0){

            echo '<tr><td>'.busca($row['md_articulo'],'articulos','a_id','a_nmb').'</b></td>';
            echo '<td>'.number_format($row['md_cantidad'],2).'</td>';
            echo '<td>'.number_format($pendientes,2).'</td>';
        }
    }
echo '</tbody></table>';

}


}
?>