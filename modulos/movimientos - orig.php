<?php
//ini_set('display_errors',1);
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

  $this->model->resultmov($_REQUEST['tipom'],$_REQUEST['motivo'],$_REQUEST['almacen']);
  $this->view = new viewmovimientos($this->model);
  $this->view->movimiento($_REQUEST['tipom'],$_REQUEST['motivo'],$_REQUEST['almacen']);
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

  if (($_POST['almacenes'] == $_POST['almacendestino']) && $_POST['tipo'] == "T") {
      echo alert_back('No se puede hacer un traspaso al mismo almancen', true);
  }
  else {
    $this->model->setData($_POST['tipo'], NULL, $_POST['almacen'], $_POST['estatus'], $_POST['destino'],$_POST['motivo']);
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

    $articulo = getIDprod($_POST['producto']);
    if(!$articulo){
      echo '<script>
              alert("Error: El producto buscado no esta registrado en tu base de datos, verifica la información");
              window.location.href="?modulo=movimientos&accion=show&id='.$_GET['id'].'&tipo='.$_GET['tipo'].'";
            </script>';
      die();
    }
    $this->model->setDatad($_GET['tipo'], $_GET['id'], $_POST['cantidad'], $articulo);

    if($this->model->tipo=="S" OR $this->model->tipo=="T"){
    $anteriores = 0;
    $almaceno = busca($this->model->mov,'movimientos','m_tipo = "'.$this->model->tipo.'" AND m_id','m_almacen');
    $existencia = existencia($articulo,$almaceno);

    $sqlan = 'SELECT SUM(md_cantidad) FROM movimientosd
              WHERE md_tipo = "'.$this->model->tipo.'" AND md_movimiento = "'.$this->model->mov.'"
              AND md_articulo = "'.$this->model->codigo.'"';
    $resultan = setq($sqlan) or die($sqlan);
    list($anteriores) = $resultan->fetch_array();
    $total = $existencia - $anteriores - $this->model->cantidad;
    if ($total < 0) {
      die(alert_back('No tienes existencias Suficientes en ' . busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb'), true));
    }
  }
  $this->model->insertdetalle();

  redirect('?modulo=movimientos&accion=show&tipo=' . $this->model->tipo . '&id=' . $_GET['id'].'&last='.$this->model->codigo);
}

function updatedetalle(){
  $articulo = busca($_GET['tipo'],'movimientosd','md_id = "'.$_GET['idprod'].'" AND md_movimiento = "'.$_GET['id'].'" AND md_tipo','md_articulo');
  $this->model->setDatad($_GET['tipo'], $_GET['id'], $_POST['cantidad'], $articulo);

  if($this->model->tipo=="S" OR $this->model->tipo=="T"){
    $anteriores = 0;
    $almaceno = busca($this->model->mov,'movimientos','m_tipo = "'.$this->model->tipo.'" AND m_id','m_almacen');
    $existencia = existencia($articulo,$almaceno);

    $sqlan = 'SELECT SUM(md_cantidad) FROM movimientosd
              WHERE md_tipo = "'.$this->model->tipo.'" AND md_movimiento = "'.$this->model->mov.'"
              AND md_articulo = "'.$this->model->codigo.'"';
    $resultan = setq($sqlan) or die($sqlan);
    list($anteriores) = $resultan->fetch_array();
    $total = $existencia - $anteriores - $this->model->cantidad;
    if ($total < 0) {
      die(alert_back('No tienes existencias Suficientes en ' . busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb'), true));
    }
  }

  $this->model->updatedetalle();

  redirect('?modulo=movimientos&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->mov.'&last='.$this->model->codigo);
}

function delprod(){
  $this->model->setDatad($_GET['tipo'], $_GET['id'], NULL, NULL);
  $this->model->delprod($_GET['idprod']);

  redirect('?modulo=movimientos&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->mov.'&last='.$this->model->codigo);
}

function aplicarmov() {
  $this->model->aplicarmov($_GET['movimiento'],$_GET['tipo']);
 // $this->model->imprimir($_GET['movimiento'],$_GET['tipo']);
  redirect('?modulo=movimientos&accion=show&tipo=' . $_GET['tipo'] . '&id=' . $_GET['movimiento']);
}

function caducidades(){

  $id = $_GET['id'];
  $tipo = $_GET['tipo'];
  $this->model->ResultArticulosMov($id,$tipo);
  $caducidad = $this->model->caducacompleta($id,$tipo);

  if($caducidad == 1)
    redirect("?modulo=movimientos&accion=aplicarmov&movimiento=".$id."&tipo=".$tipo);
  else
    die(alert_back("Existen Articulos sin Fecha de Caducidad Registrada en el Movimiento!",true));
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

function setData($tipo, $id, $almacenes, $estatus, $destino, $motivo) {
  $this->tipo = strtoupper($tipo);
  $this->id = strtoupper($id);
  $this->almacenes = strtoupper($almacenes);
  $this->estatus = strtoupper($estatus);
  $this->destino = strtoupper($destino);
  $this->motivo = strtoupper($motivo);
}

function setDatad($tipo, $mov, $cantidad, $codigo) {
  $this->tipo = strtoupper($tipo);
  $this->mov = strtoupper($mov);
  $this->cantidad = strtoupper($cantidad);
  $this->codigo = strtoupper($codigo);
  $this->id = strtoupper($codigo);
}

function resultmov($tipom,$motivo,$almacen) {
    $sql = 'SELECT * FROM movimientos
            WHERE m_empresa = "'.$_SESSION['emp'].'" ';

    if($tipom) $sql.= ' AND m_tipo = "'.$tipom.'" ';
    if($motivo) $sql.= ' AND m_motivo LIKE "%'.$motivo.'%" ';
    if($almacen) $sql.= ' AND m_almacen = "'.$almacen.'" OR m_almacen = "'.$almacendest.'" ';
    $sql.='order by m_id DESC ';
    $sql.=' LIMIT 0,50';

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
  $sqlart = 'SELECT * FROM movimientosd INNER JOIN articulos ON md_articulo = a_sku WHERE
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
          m_usuario = "' . $_SESSION['uid'] . '"';
  setq($sql);
}

function comprueba($id, $tipo) {
  $sql = 'SELECT count(*) FROM movimientosd WHERE md_movimiento = "' . $id . '" AND md_tipo = "' . $tipo . '"';
  $result = setq($sql);
  list($cuenta) = $result->fetch_array();
  if ($cuenta >= 1) $ok = 1;
  else $ok = 0;

  return($ok);
}

function insertdetalle() {
  $cb = $this->codigo;

  if(busca($cb,'movimientosd','md_movimiento="' . $this->mov . '" AND md_tipo="' . $this->tipo . '" AND md_articulo','md_id')){
    $mid=busca($cb,'movimientosd','md_movimiento="' . $this->mov . '" AND md_tipo="' . $this->tipo . '" AND md_articulo','md_id');
    $sql = 'UPDATE movimientosd SET
            md_cantidad = md_cantidad + '.$this->cantidad.'
            WHERE md_tipo = "' . $this->tipo . '"
            AND md_id = "' . $mid . '" AND md_movimiento = "' . $this->mov . '" AND md_articulo = "' . $cb . '"';
    setq($sql) or die($sql);
  }
  else{
    $this->id = getmax('md_id','movimientosd','md_tipo="'.$this->tipo.'" AND md_movimiento="'.$this->mov.'"');

    $sql = 'INSERT INTO movimientosd SET
            md_tipo = "' . $this->tipo . '",
            md_movimiento = "' . $this->mov . '",
            md_id = "' . $this->id . '",
            md_cantidad = "' . $this->cantidad . '",
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
          md_articulo = "' . $this->id . '"';
  setq($sql);
}

function delprod($idprod){
  $sql = 'DELETE FROM movimientosd WHERE md_tipo = "' . $this->tipo . '" AND
          md_movimiento = "' . $this->mov . '" AND
          md_id = "' . $idprod . '"';
  setq($sql);
}

function aplicarmov($id,$tipo) {
  $sqlcero = 'DELETE FROM existencias WHERE e_cantidad = 0';
  setq($sqlcero);

  $sql = 'SELECT * FROM movimientosd INNER JOIN movimientos ON (md_movimiento=m_id AND m_tipo = md_tipo)
          WHERE m_tipo="' . $tipo . '" AND m_id="'. $id.'" AND m_estatus = "P"';
  $result1 = setq($sql) or die($sql);
  while($rowe = $result1->fetch_array()){
    $existeal = existencia($rowe['md_articulo'],$rowe['m_almacen']);
    $sqld = 'UPDATE movimientosd SET md_existenciaant = "'.$existeal.'" WHERE
             md_tipo = "' . $rowe['md_tipo'] . '" AND md_movimiento = "' .  $rowe['md_movimiento'] . '" AND
             md_articulo = "' . $rowe['md_articulo'] . '" ';
    setq($sqld);
  }

  $result1 = setq($sql) or die($sql);

  if ($tipo == "E") {
    while ($row = $result1->fetch_array()) {
      $articulo = $row['md_articulo'];

      if(busca($articulo,'articulos','a_id','a_caduca')==0){
        ajustaexistencia($articulo,$row['md_cantidad'],$row['m_almacen'],"E");
      }
      else{
        $sqlc = 'SELECT * FROM caducidadesm WHERE cm_movimiento="' . $id . '"
                 AND cm_tipo="'.$tipo.'" AND cm_articulo="'.$articulo.'"';
        $resultc = setq($sqlc);
        while ($rowc = $resultc->fetch_array()) {
          ajustaexistencia($articulo,$rowc['cm_cantidad'],$row['m_almacen'],"E",$rowc['cm_fcaducidad']);
        }
      }
    }
    $sql = 'DELETE FROM caducidadesm WHERE cm_movimiento="' . $id . '" AND cm_tipo="'.$tipo.'"';
    setq($sql) or die($sql);
  }
  elseif ($tipo == "S") {
    $sqlmds = 'SELECT * FROM movimientosd
               INNER JOIN movimientos ON movimientosd.md_movimiento=movimientos.m_id
               WHERE m_tipo="' . $tipo . '" AND m_id=' . $id . ' AND md_tipo="' . $tipo . '" AND md_movimiento=' . $id;
    $result2 = setq($sqlmds);

    while ($rows = $result2->fetch_array()) {
      if(busca($rows['md_articulo'],'articulos','a_id','a_caduca') == 0)
        ajustaexistencia($rows['md_articulo'],$row['md_cantidad'],$row['m_almacen'],"S");
      else{
        $sqlex = 'SELECT cm_articulo, cm_cantidad, cm_fcaducidad
                  FROM caducidadesm WHERE cm_articulo="' .$rows['md_articulo'] . '"
                  AND cm_tipo="' . $tipo . '" AND cm_movimiento = "'.$id.'"
                  ORDER BY cm_fcaducidad ASC';
        $resultex = setq($sqlex);
        $eximov = $rows['md_cantidad'];

        ajustaexistencia($rows['md_articulo'],$row['md_cantidad'],$row['m_almacen'],"S",$rows['cm_fcaducidad']);
      }
    }
  }
  elseif ($tipo == "T") {
    while ($rowt = $result1->fetch_array()) {
      if ($rowt['m_almacen'] != $rowt['m_almacendest']) {
        $articulos = $rowt['md_articulo'];
        $origen = $rowt['m_almacen'];
        $destino = $rowt['m_almacendest'];
        $cantidad = $rowt['md_cantidad'];

        $exist = 'SELECT * FROM existencias WHERE e_articulo="' . $rowt['md_articulo'] . '"
                  AND e_almacen = "' . $rowt['m_almacen'] . '" ORDER BY e_cantidad DESC';
        $resexist = setq($exist);

        while ($rowexist = $resexist->fetch_array()) {
          if ($rowt['m_estatus'] != "A") {
            if ($cantidad >= $rowexist['e_cantidad']) {
              $sqlif = 'UPDATE existencias SET e_almacen="' . $destino . '"
                        WHERE e_articulo="' . $rowt['md_articulo'] . '"
                        AND e_cantidad="' . $rowexist['e_cantidad'] . '"
                        AND e_secuencia = "' . $rowexist['e_secuencia'] . '"';
              setq($sqlif);
              $cantidad = $cantidad - $rowexist['e_cantidad'];
            }
            else {
              $sqlelse = 'UPDATE existencias SET e_cantidad="' . ($rowexist['e_cantidad'] - $cantidad) . '"
                          WHERE e_articulo="' . $rowt['md_articulo'] . '"
                          AND e_cantidad="' . $rowexist['e_cantidad'] . '"
                          AND e_secuencia = "' . $rowexist['e_secuencia'] . '"';
              setq($sqlelse);

              $rowmax = getmax('e_secuencia','existencias','e_articulo="'.$articulo.'"');

              $fcompra=busca($rowt['md_articulo'],'existencias','e_secuencia="'.$rowexist['e_secuencia'].'" AND e_articulo','e_compra');
              $fcaducidad=busca($rowt['md_articulo'],'existencias','e_secuencia="'.$rowexist['e_secuencia'].'" AND e_articulo','e_fcaducidad');
              $costo=busca($rowt['md_articulo'],'existencias','e_secuencia="'.$rowexist['e_secuencia'].'" AND e_articulo','e_costo');
              $empresa=busca($rowt['m_almacendest'],'almacenes','a_id','a_empresa');

              $sql2 = 'INSERT IGNORE INTO existencias SET
                      e_secuencia="' . $rowmax . '",
                      e_articulo="' . $rowt['md_articulo'] . '",
                      e_cantidad="' . $cantidad . '",
                      e_almacen="' . $rowt['m_almacendest'] . '",
                      e_costo="' . $costo . '",
                      e_compra="' . $fcompra . '",
                      e_empresa="' . $empresa . '",
                      e_fcaducidad="' . $fcaducidad . '"';
              if($cantidad>0){ setq($sql2) ; }
              $cantidad=0;

              $sql3 = 'UPDATE movimientos SET m_estatus="A" WHERE m_tipo="' . $_GET['tipo'] . '" AND m_id=' . $rowt['m_id'];
              setq($sql3);

            }
          }
        }
      }
      else {
        die(alert_back('El trasaso se esta realizando al mismo almacen', true));
      }
    }

    $sql = 'DELETE FROM caducidadesm WHERE cm_movimiento="' . $id . '" AND cm_tipo="'.$tipo.'"';
    setq($sql);
  }

  $sql3 = 'UPDATE movimientos SET m_estatus = "A", m_fechaap = "'.date('Y-m-d').'"
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

function movimiento($tipom,$almacen,$motivo) {
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
</script>
<?php
  $this->model->tipo = array("E" => "Entrada","S" => "Salida","T" => "Traspaso");
  $this->model->aestatus = array("A" => "Finalizado","P" => "En Captura");
  $this->model->nestatus = array("A" => "btn-success","P" => "bg-amber bg-darken-2");

  $selte = "";
  $selts = "";
  $seltt = "";

  if($tipom =="E") $selte = "selected";
  elseif($tipom =="S") $selts = "selected";
  else $seltt = "selected";

  $accion = 'insert';
  if (isset($this->model->id)) {
    $accion = 'update';
  }
   //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions">';
    //Sección: E2 Encabezado - Filtros

  $sql = 'SELECT * FROM almacenes WHERE a_empresa = "'.$_SESSION['emp'].'" AND a_estatus = "A"';
  $result = setq($sql);
  ?>

    <div class="ml-2 col-md-1">
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>
    <div id="filter-panel" class="col-md-9 collapse filter-panel mb-2">
      <div class="panel panel-default">
        <div class="panel-body">
          <form class="form-inline" role="form" method="post" >
            <div class="mb-5 mr-2">
              <label for="tipom">Tipo Movimiento</label>
              <select class="form-control" name="tipomov" id="tipomov" onchange="valida()">
                <option value="E" <?php echo $selte; ?> >Entrada</option>
                <option value="S" <?php echo $selts; ?> >Salida</option>
                <option value="T" <?php echo $seltt; ?> >Traspaso</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5 mr-2">
              <label for="tipom">Mótivo</label>
              <input type="text" class="form-control" name="motivo" value="<?php echo $motivo ?>" />
            </div>
            <div class="mb-5 mr-2">
              <label for="tipom">Almacen</label>
              <select name="almacen" id="origen" required class="form-control" >
              <?php
              while ($row = $result->fetch_array()){
              ?>
                <option value="'.$row['a_id'].'"><?php echo $row['a_nmb'] ?></option>
              <?php
              }
              ?>
              </select>
            </div>
            <div class="mb-5">
            <button type="submit" class="btn btn-info">
              <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
            </button>
            <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
            </button></a>
            </div>
          </form>
        </div>
      </div>
    </div></div></div>
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

        <div class="main-card mb-3 card">
          <div class="card-body row">

  <?php
  echo '<form method="post" action="?modulo=movimientos&accion=insert" name="regmov">
        <div class="card">
          <div class="card-header p-1"><h5>Registrar un movimiento de almacen</h5></div>
            <div class="p-1 card-body collapse in row">
              <div class="col-xs-12 col-md-4">
                <div class="mb-5">
                  <label for="tipomov">Tipo de movimiento</label>
                  <select class="form-control" name="tipo" id="tipo" onchange="valida()">
                    <option value="E">Entrada</option>
                    <option value="S">Salida</option>
                    <option value="T">Traspaso</option>
                  </select>
                </div>
              </div> ';

  $result = setq($sql);
  echo '<div class="col-xs-12 col-md-3">
          <div class="mb-5">
            <label for="almacen">Almacen movimiento</label>';
      echo '<select name="almacen" id="origen" required class="form-control" >';
        while ($row = $result->fetch_array()){
          echo '<option value="'.$row['a_id'].'">'.$row['a_nmb'].':::'.busca($row['a_empresa'],'empresas','e_id','e_nmb').'</option>';
        }
      echo '</select>
          </div>
        </div>

        <div class="col-xs-12 col-md-3" id="almdestino" style="display:none">
          <div class="mb-5">
            <label for="almacen">Almacen Destino</label>';
      echo '<select name="destino" id="destino" required class="form-control" >';
        $result = setq($sql);
        while ($row = $result->fetch_array()){
          echo '<option value="'.$row['a_id'].'">'.$row['a_nmb'].':::'.busca($row['a_empresa'],'empresas','e_id','e_nmb').'</option>';
        }
      echo '</select>
          </div>
        </div>
            <div class="col-xs-12 col-md-3">
              <div class="mb-5">
                <label for="almacen">Motivo del movimiento</label>
                  <input type="text" class="form-control" name="motivo" id="motivo" placeholder="Describe el motivo del movimiento" required="true"  />
              </div>
            </div>
            <div class="col-xs-12 col-md-2 text-xs-center" >
              <div class="mb-5">
                <label for="almacen">Guardar</label><br>
                  <button class="btn btn-primary"><i class="fa fa-save"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div></form>';

  echo '<div class="table table-responsive">
        <table class="table table-hover table-striped">
        <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
          <tr>
            <th>Folio Movimiento</th>
            <th>Mótivo</th>
            <th>Fecha de Alta</th>
            <th>Fecha de aplicación</th>
            <th>Usuario</th>
            <th>Almacen Origen</th>
            <th>Almacen Destino</th>
            <th>Estatus</th>
            <th></th>
          </tr>
        </thead>';
  while($row = $this->model->resultmd->fetch_array()){
    echo '<tr>
            <th>'.$this->tipomov[$row['m_tipo']].' - '.$row['m_id'].'</th>
            <td>'.$row['m_motivo'].'</td>
            <td>'.date('d-m-Y',strtotime($row['m_fecha'])).'</td>
            <td>'.date('d-m-Y',strtotime($row['m_fechaap'])).'</td>
            <td>'.$row['m_usuario'].'</td>
            <td>'.busca($row['m_almacen'],'almacenes','a_id','a_nmb').'</td>
            <td>'.busca($row['m_almacendest'],'almacenes','a_id','a_nmb').'</td>
            <td class="'.$this->model->nestatus[$row['m_estatus']].'">'.$this->model->aestatus[$row['m_estatus']].'</td>
            <td>
              <a href="?modulo=movimientos&accion=show&tipo='.$row['m_tipo'].'&id='.$row['m_id'].'">
                <button type="button" class="btn-sm btn-info"><i class="fa fa-eye"></i> Detalle</button>
              </a>';
      if($row['m_estatus'] == "A")
        echo '<a target="_BLANK" href="/formats/pdfmovimiento?tipo='.$row['m_tipo'].'&id='.$row['m_id'].'">
                <button type="button" class="btn-sm btn-secondary"><i class="icon-print"></i> Imprimir</button>
              </a>';

    echo '</td>
          </tr>';
  }
  echo '</table></div>';
}

function showmovimiento(){
?>
   <script>
    $(document).ready(function() {
        $('#producto').on('keyup', function() {
          var key = $(this).val();
//          var empresa = $('#empresa').val();
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
              setname();
//              alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
              return false;
            });
          }
        });
      });
    });
    </script>
<?php
  if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
  if ($_GET['alerta'] == 1) {
    echo alert("El articulo no Existe", true);
  }

  echo '<script>
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

  echo '<div class="row page-title-actions"><div class="col-4 col-md-3">';
  echo '<a href="?modulo=movimientos&accion=index" accesskey="">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
        </a>';

  if ($this->model->comprueba($this->model->id, $this->model->tipo) == 1 AND $this->model->estatus != "A") {
    echo '<button class="btn btn-primary ml-1" onclick="aplicamov()"><i class="fa fa-check"></i> Aplicar</button>';
  }
  if($this->model->estatus == "A")
    echo '<a target="_BLANK" href="/formats/pdfmovimiento?tipo='.$this->model->tipo.'&id='.$this->model->id.'">
            <button type="button" class="btn btn-secondary ml-1"><i class="icon-print"></i> Imprimir</button>
          </a>';

  echo '</div>';

  if($this->model->estatus == "A"){
    //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
  }

  if($this->model->tipo == "T") $dest = ' <i class="icon-arrow-right3"></i> '.busca($this->model->almacendest,'almacenes','a_id','a_nmb'); else $dest = "";

  if($this->model->estatus == "P") $leyenda = 'Añadir productos a '.$this->tipomov[$this->model->tipo];
  else  $leyenda = 'Lista de productos en '.$this->tipomov[$this->model->tipo];

  echo '</div></div>';

  $readon = "";
  $exisantm = "";
  if($this->model->estatus == "P")
    echo '<div class="row">
            <form method="post" autocomplete="off" action="?modulo=movimientos&accion=insertdetalle&id='.$this->model->id.'&tipo='.$this->model->tipo.'">
              <div class="mt-1 col-8 col-md-8 alert alert-'.$this->estilomov[$this->model->tipo].'">
                '.$leyenda.'
                 -
                 '.$this->model->motivo.'
              </div>
              <div class="mt-1 col-4 col-md-4 alert alert-info">Almacen: '.busca($this->model->almacen,'almacenes','a_id','a_nmb').' '.$dest.'</div>
              <div class="col-8 col-md-6">
                <div class="mb-5">
                  <label for="agregar" class">Producto</label><br>
                  <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus >
                 <div id="suggestions"></div>
                </div>
              </div>
              <div class="col-4 col-md-3">
                <div class="mb-5">
                  <label for="cant">Cantidad</label><br>
                  <input type="number" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control" required="required" />
                </div>
              </div>
              <div class="col-12 col-md-2">
                <div class="mb-5">
                <label>.</label><br>
                  <button type="submit" class="btn btn-success"><i class="icon-android-send"></i> Agregar producto</button>
                </div>
              </div>
            </form>
          </div>';
  else{
    echo '<div class="mt-1 col-8 col-md-8 alert alert-'.$this->estilomov[$this->model->tipo].'">
            '.$leyenda.' - '.$this->model->motivo.'
          </div>';
    $readon = 'readonly="readonly"';
    $exisantm = "Existencia antes del movimiento";
  }

  echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
        <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
          <tr>
            <th>Artículo</th>
            <th>Piezas del movimiento</th>
            <th>Existencia Actual</th>
            <th>'.$exisantm.'</th>
          </tr>
        </thead>';
  while($row = $this->model->resultart->fetch_array()){
    echo '<tr><form method="post" action="?modulo=movimientos&accion=updatedetalle&id='.$this->model->id.'&tipo='.$this->model->tipo.'&idprod='.$row['md_id'].'">
              <td>'.$row['a_nmb'].'</td>
              <th>
                <input type="number" value="'.number_format($row['md_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control" required="required" onfocus="this.select();" '.$readon.' />
              </th>
              <th>'.existencia($row['md_articulo'],$this->model->almacen).'</th>
              <th>';

    if($this->model->estatus == "P")
          echo '<button type="submit" class="btn-sm btn-primary"><i class="fa fa-redo"></i> Actualizar</button>
                <button type="button" class="btn-sm btn-danger" onclick="delprod('.$row['md_id'].')"><i class="fa fa-trash"></i> Borrar</button>';
    else
      echo $row['md_existenciaant'];

        echo '</th>
            </form></tr>';
  }
  echo '</table></div>';

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
    <th><center>Cantidad</th>
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