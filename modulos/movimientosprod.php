<?php
//ini_set('display_errors',1);
class movimientosprod {
  var $model;
  var $view;

function __construct() {
  $this->model = new modelmovimientosprod(isset($obj));
}

function index() {
  if (!isset($_REQUEST['tipom']) || empty($_REQUEST['tipom'])) $_REQUEST['tipom'] = NULL;
  if (!isset($_REQUEST['motivo']) || empty($_REQUEST['motivo'])) $_REQUEST['motivo'] = NULL;
  if (!isset($_REQUEST['almacen']) || empty($_REQUEST['almacen'])) $_REQUEST['almacen'] = NULL;
  if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
  
  //die($_REQUEST['almacen']);

  $this->model->resultmov($_REQUEST['tipom'],$_REQUEST['motivo'],$_REQUEST['almacen'],$_REQUEST['page']);
  $this->view = new viewmovimientosprod($this->model);
  $this->view->movimiento($_REQUEST['tipom'],$_REQUEST['motivo'],$_REQUEST['almacen'],$_REQUEST['page']);
}

function insert() {
  if (!isset($_POST['estatus'])) $_POST['estatus'] = NULL;
  if (!isset($_POST['almacenes'])) $_POST['almacenes'] = NULL;
  if (!isset($_POST['almacendestino'])) $_POST['almacendestino'] = NULL;

  if(busca($_POST['tipo'],'pr_movimientos','pm_estatus = "N" AND pm_tipo','COUNT(*)')){
    $idm = busca($_POST['tipo'],'pr_movimientos','pm_estatus = "N" AND pm_tipo','pm_id');

    echo '<script>
            var conf = confirm("Existe un movimiento de este tipo pendiente de aplicación\nNo podrás generar uno nuevo hasta aplicarlo\n¿Deseas ver el momiento?");
            if(conf == true){
              window.location.href="?modulo=movimientosprod&accion=show&id='.$idm.'&tipo='.$_POST['tipo'].'";
            }
            else{
              window.location.href="?modulo=movimientosprod&accion=index";
            }
          </script>';
    die();
  }

  if (($_POST['almacen'] == $_POST['destino']) && $_POST['tipo'] == "T") {
      echo alert_back('No se puede hacer un traspaso al mismo almancen', true);
  }
  else {

    $acronimo = busca(1,'empresas','e_id','e_siglas');
    $sqlm = 'SELECT COUNT(*) FROM pr_movimientos WHERE pm_folio LIKE "'.$_POST['tipo'].$acronimo.'%"';
    $resultm = setq($sqlm);
    list($maxcot) = $resultm->fetch_array();
    $maxcot++;
    
    $foliocot = ''.$_POST['tipo'].'PR'.$acronimo.str_pad($maxcot,6,"0",STR_PAD_LEFT);

    $this->model->setData($_POST['tipo'], NULL, $_POST['almacen'], $_POST['estatus'], $_POST['destino'],$_POST['motivo'],$foliocot);
    $this->model->insert();

    redirect('?modulo=movimientosprod&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->id);
  }
}

function show() {
  $this->model->select($_GET['id'],$_GET['tipo']);
  $this->model->ResultArticulosMov($_GET['id'],$_GET['tipo']);
  $this->view = new viewmovimientosprod($this->model);
  $this->view->showmovimiento();
}

function insertdetalle(){
  if (!isset($id)) $id = NULL;
  $articulo = getIDmp($_POST['producto']);
  if($articulo){ 

    $this->model->setDatad($_GET['tipo'], $_GET['id'], $_POST['cantidad'], $articulo, $_POST['largo'], $_POST['ancho'], $_POST['alto'], '');

    if($this->model->tipo=="S" OR $this->model->tipo=="T"){
      $anteriores = 0;
      $almaceno = busca($this->model->mov,'pr_movimientos','pm_tipo = "'.$this->model->tipo.'" AND pm_id','pm_almacen');
      $total = 0;
      $existencia = existenciamp($articulo,$almaceno);
      $tipomp = busca($articulo, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');

      $sqlan = 'SELECT * FROM pr_movimientosd
                WHERE pmd_tipo = "'.$this->model->tipo.'" AND pmd_movimiento = "'.$this->model->mov.'"
                AND pmd_materiaprima = "'.$this->model->codigo.'"';
      $resultan = setq($sqlan) or die($sqlan);
      while($rowtan = $resultan->fetch_array()) {
        if($tipomp == "A"){
          $total += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']);
        } else if($tipomp == "V"){
          $total += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']*$rowtan['pmd_aalto']);
        } else if($tipomp == "P"){
          $total += $rowtan['pmd_cantidad'];
        }
      }
      if($tipomp == "A"){
        $total += ($_POST['cantidad']*$_POST['largo']*$_POST['ancho']);
      } else if($tipomp == "V"){
        $total += ($_POST['cantidad']*$_POST['largo']*$_POST['ancho']*$_POST['largo']);
      } else if($tipomp == "P"){
        $total += $_POST['cantidad'];
      }
      //die($total);
      if ($existencia < $total) {
        die(alert_back('No tienes existencias Suficientes en ' . $_POST['producto'], true));
        //echo '<script>alert("No tienes existencias Suficientes en '.busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb').' "); window.location.reload();</script>';
        //die();
      } 
    }
  } else {
    echo '<script>
            alert("Error: El producto buscado no esta registrado en tu base de datos, verifica la información");
            window.location.href="?modulo=movimientosprod&accion=show&id='.$_GET['id'].'&tipo='.$_GET['tipo'].'";
          </script>';
  }
  $this->model->insertdetalle();

  redirect('?modulo=movimientosprod&accion=show&tipo=' . $this->model->tipo . '&id=' . $_GET['id'].'&last='.$this->model->codigo);
}

function updatedetalle(){  
  $articulo = busca($_GET['tipo'],' pr_movimientosd','pmd_id = "'.$_GET['idprod'].'" AND pmd_movimiento = "'.$_GET['id'].'" AND pmd_tipo','pmd_materiaprima');

  $this->model->setDatad($_GET['tipo'], $_GET['id'], $_POST['cantidad'], $articulo, '', '', '', $_GET['idprod']);
  if($this->model->cantidad == 0){
    $sql = 'DELETE FROM pr_movimientosd WHERE pmd_tipo = "'.$this->model->tipo.'" AND pmd_movimiento = "'.$_GET['id'].'" AND pmd_materiaprima = "'.$articulo.'" AND pmd_id = "'.$_GET['idprod'].'"';
    setq($sql);
  }
  else
  if($this->model->tipo=="S" OR $this->model->tipo=="T"){
    $total = 0;
    
    $almaceno = busca($this->model->mov,'pr_movimientos','pm_tipo = "'.$this->model->tipo.'" AND pm_id','pm_almacen');
    $existencia = existenciamp($articulo,$almaceno);
    $tipomp = busca($articulo, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');

    $sqlan = 'SELECT * FROM pr_movimientosd
                WHERE pmd_tipo = "'.$this->model->tipo.'" AND pmd_movimiento = "'.$this->model->mov.'"
                AND pmd_materiaprima = "'.$this->model->codigo.'" AND pmd_id != "'.$_GET['idprod'].'"';
    $resultan = setq($sqlan) or die($sqlan);
    while($rowtan = $resultan->fetch_array()) {
      if($tipomp == "A"){
        $total += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']);
      } else if($tipomp == "V"){
        $total += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']*$rowtan['pmd_alto']);
      } else if($tipomp == "P"){
        $total += $rowtan['pmd_cantidad'];
      }
    }
    $sqlsel = 'SELECT pmd_largo, pmd_ancho, pmd_alto FROM pr_movimientosd WHERE pmd_tipo = "'.$this->model->tipo.'" AND pmd_movimiento = "'.$this->model->mov.'" AND pmd_id = "'.$_GET['idprod'].'"'; 
    $resultsel = setq($sqlsel); 
    list($largo, $ancho, $alto) = $resultsel -> fetch_array();
    if($tipomp == "A"){
      $total += ($_POST['cantidad']*$largo*$ancho);
    } else if($tipomp == "V"){
      $total += ($_POST['cantidad']*$largo*$ancho*$alto);

    } else if($tipomp == "P"){
      $total += $_POST['cantidad'];
    }
    //die($total);
    
    if ($existencia < $total) {
      die(alert_back('No tienes existencias Suficientes en ' . busca($this->model->codigo,'pr_materiaprima', 'pmp_id' ,'pmp_nmb'), true));
      //echo '<script>alert("No tienes existencias Suficientes en '.busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb').' "); window.location.reload();</script>';
      //die();
    }
  }

  $this->model->updatedetalle();

  redirect('?modulo=movimientosprod&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->mov.'&last='.$this->model->codigo);
}

function delprod(){
  $this->model->setDatad($_GET['tipo'], $_GET['id'], NULL, '', '', '', '', $_GET['idprod']);  
  $this->model->delprod($_GET['idprod']);

  redirect('?modulo=movimientosprod&accion=show&tipo=' . $this->model->tipo . '&id=' . $this->model->mov.'&last='.$this->model->codigo);
}

function aplicarmov() {
  //$this->model->select($_GET['movimiento'],$_GET['tipo']);
  $this->model->aplicarmov($_GET['movimiento'],$_GET['tipo']);
  // $this->model->imprimir($_GET['movimiento'],$_GET['tipo']);
  redirect('?modulo=movimientosprod&accion=show&tipo=' . $_GET['tipo'] . '&id=' . $_GET['movimiento']);
}

function caducidades(){
  $id = $_GET['id'];
  $tipo = $_GET['tipo'];
/*   
  $this->model->ResultArticulosMov($id,$tipo);
  $caducidad = $this->model->caducacompleta($id,$tipo);

  if($caducidad == 1) */
    redirect("?modulo=movimientosprod&accion=aplicarmov&movimiento=".$id."&tipo=".$tipo);
 /*  else
    die(alert_back("Existen Articulos sin Fecha de Caducidad Registrada en el Movimiento!",true)); */
  //header("Location: ?modulo=movimientosprod&accion=insertfechacad&id=".$id."&tipo=".$tipo);

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
        /* $sqldm='DELETE FROM caducidadesm WHERE cm_movimiento = "'.$_GET['mov'].'" AND cm_tipo = "'.$_GET['tipo'].'" AND cm_articulo = "'.busca($_GET['idd'],'movimientosd','pmd_tipo = "'.$_GET['tipo'].'" AND pmd_movimiento = "'.$_GET['mov'].'" AND pmd_id','pmd_materiaprima').'"';
        setq($sqldm) or die($sqldm); */

        $sql = 'DELETE FROM pr_movimientosd WHERE pmd_tipo="' . $_GET['tipo'] . '" AND pmd_movimiento="' . $_GET['mov'] . '" AND pmd_id="' . $_GET['idd'] . '"';
        setq($sql) or die(mysql_error() . $sql);
        header('Location:?modulo=movimientosprod&accion=detalle&tipo=' . $_GET['tipo'] . '&id=' . $_GET['mov']);
    }

    function fecha() {
        $sql = 'UPDATE pr_movimientosd SET pmd_fcaducidad="' . $_POST['fini'] . '" WHERE pmd_tipo="' . $_GET['tipo'] . '" AND pmd_movimiento="' . $_GET['mov'] . '" AND pmd_id="' . $_GET['id'] . '"';
        setq($sql) or die(mysql_error() . $sql);
        header('Location:?modulo=movimientosprod&accion=detalle&tipo=' . $_GET['tipo'] . '&id=' . $_GET['mov']);
    }

    function showmov() {
        $this->model->resultmov($_GET['id']);
        $this->view = new viewmovimientos($this->model);
        $this->view->showmov();
    }

}

/* -----------------------------------------------MODEL---------------------------------------------------------------------- */

class modelmovimientosprod {

function setData($tipo, $id, $almacenes, $estatus, $destino, $motivo, $folio) {
  $this->tipo = strtoupper($tipo);
  $this->id = strtoupper($id);
  $this->almacenes = strtoupper($almacenes);
  $this->estatus = strtoupper($estatus);
  $this->destino = strtoupper($destino);
  $this->motivo = strtoupper($motivo);
  $this->folio = strtoupper($folio);
}

function setDatad($tipo, $mov, $cantidad, $codigo, $largo, $ancho, $alto, $idd) {
  $this->tipo = strtoupper($tipo);
  $this->mov = strtoupper($mov);
  $this->cantidad = strtoupper($cantidad);
  $this->largo = strtoupper($largo);
  $this->ancho = strtoupper($ancho);
  $this->alto = strtoupper($alto);
  $this->codigo = strtoupper($codigo);
  $this->idd = strtoupper($idd);
}

function resultmov($tipom,$motivo,$almacen,$page) { //filtro;
    $bloque =  50;
    $sql = 'SELECT * FROM pr_movimientos ';

    if($tipom) $sql.= ' AND pm_tipo = "'.$tipom.'" ';
    if($almacen) $sql.= ' AND (pm_almacen = "'.$almacen.'" OR pm_almacendest = "'.$almacen.'")';
    $sql.='order by pm_fecha DESC ';
    $sql.=' LIMIT '.($page*$bloque).','.$bloque.'';
    $this->resultmd = setq($sql);
}

function select($id,$tipo) {
  $sql = 'SELECT * FROM pr_movimientos WHERE pm_tipo = "'.$tipo.'" AND pm_id = "'.$id.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $this->tipo = $row['pm_tipo'];
  $this->id = $row['pm_id'];
  $this->motivo = $row['pm_motivo'];
  $this->almacen = $row['pm_almacen'];
  $this->almacendest = $row['pm_almacendest'];
  $this->estatus = $row['pm_estatus'];
  $this->fecha = $row['pm_fecha'];
  $this->fechaap = $row['pm_fechaap'];
  $this->documento = $row['pm_documento'];
  $this->usuario= $row['pm_usuario'];
}

function ResultArticulosMov($id,$tipo){
  $sqlart = 'SELECT * FROM pr_movimientosd INNER JOIN pr_materiaprima ON pmd_materiaprima = pmp_id WHERE
             pmd_movimiento = "'.$id.'" AND pmd_tipo = "'.$tipo.'"
             ORDER BY pmd_id DESC';
  $this->resultart = setq($sqlart);
}

function insert() {
  $this->id = getmax('pm_id','pr_movimientos','pm_tipo = "'.$this->tipo.'"');

  if($this->destino==NULL || $this->tipo != "T") $this->destino="0";

  $sql = 'INSERT INTO pr_movimientos SET
          pm_tipo = "' . $this->tipo . '",
          pm_id = "' . $this->id . '",
          pm_fecha = "' . date('Y-m-d') . '",
          pm_almacen = "' . $this->almacenes . '",
          pm_motivo = "' . $this->motivo . '",
          pm_almacendest = "'.$this->destino.'",
          pm_documento = "NULL",
          pm_estatus = "P",
          pm_usuario = "' . $_SESSION['uid'] . '",
          pm_folio = "'.$this->folio.'"';
  setq($sql);
}

function comprueba($id, $tipo) {
  $sql = 'SELECT count(*) FROM pr_movimientosd WHERE pmd_movimiento = "' . $id . '" AND pmd_tipo = "' . $tipo . '"';
  $result = setq($sql);
  list($cuenta) = $result->fetch_array();
  //if ($cuenta >= 1) $ok = 1;
  if ($cuenta >= 1) $ok = "OK";
  else $ok = 0;

  $sql = 'SELECT SUM(pmd_cantidad) FROM pr_movimientosd WHERE pmd_movimiento = "' . $id . '" AND pmd_tipo = "' . $tipo . '"';
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


  $sql = 'SELECT pmp_id, pmp_estatus, pmp_nmb FROM pr_materiaprima INNER JOIN pr_movimientosd ON pmd_materiaprima = pmp_id WHERE pmd_movimiento = "'.$id.'" AND pmd_tipo = "'.$tipo.'" ';
  $result = setq($sql);
  $nombres = '';
  while($row = $result->fetch_array()){
    if($row['pmp_id'] == 0){
      $nombres .= $row['pmp_nmb'].' - ';
      $ok = $row['pmp_id'];
      //$ok = 0;
    }
  }
  return($ok);
}

function insertdetalle() {
  $cb = $this->codigo;
  /* if(busca($cb,'pr_movimientosd','pmd_movimiento="' . $this->mov . '" AND pmd_tipo="' . $this->tipo . '" AND pmd_materiaprima','pmd_id')){    
    $mid=busca($cb,'pr_movimientosd','pmd_movimiento="' . $this->mov . '" AND pmd_tipo="' . $this->tipo . '" AND pmd_materiaprima','pmd_id');
    $sql = 'UPDATE pr_movimientosd SET
            pmd_cantidad = pmd_cantidad + '.$this->cantidad.'
            WHERE pmd_tipo = "' . $this->tipo . '"
            AND pmd_id = "' . $mid . '" AND pmd_movimiento = "' . $this->mov . '" AND pmd_materiaprima = "' . $cb . '"';
    setq($sql) or die($sql);
  }
  else{ */
    $this->id = getmax('pmd_id','pr_movimientosd','pmd_tipo="'.$this->tipo.'" AND pmd_movimiento="'.$this->mov.'"');

    $sql = 'INSERT INTO pr_movimientosd SET
            pmd_tipo = "' . $this->tipo . '",
            pmd_movimiento = "' . $this->mov . '",
            pmd_id = "' . $this->id . '",
            pmd_cantidad = "' . $this->cantidad . '",
            pmd_materiaprima = "' . $cb . '",
            pmd_largo = "' . $this->largo . '",
            pmd_ancho = "' . $this->ancho . '",
            pmd_alto = "' . $this->alto . '"';
    setq($sql) or die(mysql_error() . $sql);
  //}
}

function updatedetalle(){
  $sql = 'UPDATE pr_movimientosd SET
          pmd_cantidad = "' . $this->cantidad . '"
          WHERE
          pmd_tipo = "' . $this->tipo . '" AND
          pmd_movimiento = "' . $this->mov . '" AND
          pmd_id = "' . $this->idd . '"';          
  setq($sql);
}

function delprod($idprod){
  $sql = 'DELETE FROM pr_movimientosd WHERE pmd_tipo = "' . $this->tipo . '" AND
          pmd_movimiento = "' . $this->mov . '" AND
          pmd_id = "' . $idprod . '"';
  setq($sql);
}

function aplicarmov($id,$tipo) {  
  if($tipo == "S" || $tipo == "T"){
    $anteriores = 0;
    $almaceno = busca($id,'pr_movimientos','pm_tipo = "'.$tipo.'" AND pm_id','pm_almacen');
    $sqlxx = 'SELECT * FROM pr_movimientosd WHERE pmd_movimiento = "'.$id.'" AND pmd_tipo = "'.$tipo.'" GROUP BY pmd_materiaprima'; 
    $resultxx = setq($sqlxx);
    while($rowxx = $resultxx->fetch_array()){
      $articulo = $rowxx['pmd_materiaprima']; 
      /* $existencia = existencia($articulo,$almaceno); */
      $total = 0;
      $existencia = existenciamp($articulo,$almaceno);
      $tipomp = busca($articulo, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');

      $sqlan = 'SELECT * FROM pr_movimientosd
                WHERE pmd_tipo = "'.$tipo.'" AND pmd_movimiento = "'.$id.'"
                AND pmd_materiaprima = "'.$articulo.'"';
      $resultan = setq($sqlan) or die($sqlan);
      while($rowtan = $resultan->fetch_array()) {
        if($tipomp == "A"){
          $total += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']);
        } else if($tipomp == "V"){
          $total += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']*$rowtan['pmd_alto']);
        } else if($tipomp == "P"){
          $total += $rowtan['pmd_cantidad'];
        }
      }
      //die($total);
      if ($existencia < $total) {
        die(alert_back('No tienes existencias Suficientes en ' . busca($articulo, 'pr_materiaprima', 'pmp_id', 'pmp_nmb'), true));
        //echo '<script>alert("No tienes existencias Suficientes en '.busca($this->model->codigo, 'articulos', 'a_id', 'a_nmb').' "); window.location.reload();</script>';
        //die();
      } 
    }
  }
  
  $sqlcero = 'DELETE FROM pr_existencias WHERE pe_cantidad = 0';
  setq($sqlcero);

  $sql = 'SELECT * FROM pr_movimientosd INNER JOIN pr_movimientos ON (pmd_movimiento=pm_id AND pm_tipo = pmd_tipo)
          WHERE pm_tipo="' . $tipo . '" AND pm_id="'. $id.'" AND pm_estatus = "P" GROUP BY pmd_materiaprima';
  $result1 = setq($sql) or die($sql);
  while($rowe = $result1->fetch_array()){
    $existeal = existenciamp($rowe['pmd_materiaprima'],$rowe['pm_almacen']);
    $sqld = 'UPDATE pr_movimientosd SET pmd_existenciaant = "'.$existeal.'" WHERE
             pmd_tipo = "' . $rowe['pmd_tipo'] . '" AND pmd_movimiento = "' .  $rowe['pmd_movimiento'] . '" AND
             pmd_materiaprima = "' . $rowe['pmd_materiaprima'] . '"';
    setq($sqld);
  }  

  $result1 = setq($sql) or die($sql);

  if ($tipo == "E") {
    while ($row = $result1->fetch_array()) {
      $articulo = $row['pmd_materiaprima'];
      $tipomp = busca($articulo, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
      $cantidad = 0;      
      $sqlan = 'SELECT * FROM pr_movimientosd
                WHERE pmd_tipo = "'.$tipo.'" AND pmd_movimiento = "'.$id.'"
                AND pmd_materiaprima = "'.$articulo.'"';
      $resultan = setq($sqlan) or die($sqlan);
      while($rowtan = $resultan->fetch_array()) {
        if($tipomp == "A"){
          $cantidad += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']);
        } else if($tipomp == "V"){
          $cantidad += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']*$rowtan['pmd_alto']);
        } else if($tipomp == "P"){
          $cantidad += $rowtan['pmd_cantidad'];
        }
      }
      /* echo 'mp: '.$articulo.' cantidad: '.$cantidad.' almacen: '.$row['pm_almacen'].'<br>'; */
      ajustaexistenciamp($articulo,$cantidad,$row['pm_almacen'],"E");
    }
  }
  elseif ($tipo == "S") {
    while ($row = $result1->fetch_array()) {
      $articulo = $row['pmd_materiaprima'];
      $tipomp = busca($articulo, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
      $cantidad = 0;      
      $sqlan = 'SELECT * FROM pr_movimientosd
                WHERE pmd_tipo = "'.$tipo.'" AND pmd_movimiento = "'.$id.'"
                AND pmd_materiaprima = "'.$articulo.'"';
      $resultan = setq($sqlan) or die($sqlan);
      while($rowtan = $resultan->fetch_array()) {
        if($tipomp == "A"){
          $cantidad += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']);
        } else if($tipomp == "V"){
          $cantidad += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']*$rowtan['pmd_alto']);
        } else if($tipomp == "P"){
          $cantidad += $rowtan['pmd_cantidad'];
        }
      }
      ajustaexistenciamp($articulo,$cantidad,$row['pm_almacen'],"S");
    }
  }
  elseif ($tipo == "T") {
    while ($rowt = $result1->fetch_array()) {
      if ($rowt['pm_almacen'] != $rowt['pm_almacendest'] && $rowt['pm_estatus'] != "A") {
        $articulo = $rowt['pmd_materiaprima'];
        $tipomp = busca($articulo, 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
        $cantidad = 0;      
        $sqlan = 'SELECT * FROM pr_movimientosd
                  WHERE pmd_tipo = "'.$tipo.'" AND pmd_movimiento = "'.$id.'"
                  AND pmd_materiaprima = "'.$articulo.'"';
        $resultan = setq($sqlan) or die($sqlan);
        while($rowtan = $resultan->fetch_array()) {
          if($tipomp == "A"){
            $cantidad += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']);
          } else if($tipomp == "V"){
            $cantidad += ($rowtan['pmd_cantidad']*$rowtan['pmd_largo']*$rowtan['pmd_ancho']*$rowtan['pmd_alto']);
          } else if($tipomp == "P"){
            $cantidad += $rowtan['pmd_cantidad'];
          }
        }

        ajustaexistenciamp($rowt['pmd_materiaprima'],$cantidad,$rowt['pm_almacen'],'S');
        ajustaexistenciamp($rowt['pmd_materiaprima'],$cantidad,$rowt['pm_almacendest'],"E");

      }
      else {
        die(alert_back('El traspaso se esta realizando al mismo almacen', true));
      }
    }
  }

  $sql3 = 'UPDATE pr_movimientos SET pm_estatus = "A",pm_fechaap = "'.date('Y-m-d').'"
           WHERE pm_tipo="' . $tipo . '" AND pm_id="' . $id . '"';
  setq($sql3);
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
Genero:    '.busca(busca($movimiento,'pr_movimientos','pm_tipo="'.$tipo.'" AND pm_id','pm_usuario'),'usuarios','u_id','u_nmb').'
Tipo:      '.$tip.'
Motivo:    '.busca($movimiento,'pr_movimientos','pm_tipo="'.$tipo.'" AND pm_id','pm_motivo').'
Origen:    '.busca(busca($movimiento,'pr_movimientos','pm_tipo="'.$tipo.'" AND pm_id','pm_almacen'),'pr_almacenes','pa_id','pa_nmb');

if($tipo=="T"){
$ped.='
Destino:   '.busca(busca($movimiento,'pr_movimientos','pm_tipo="'.$tipo.'" AND pm_id','pm_almacendest'),'pr_almacenes','pa_id','pa_nmb');
}

$ped.='
FECHA: '.date("d-m-Y H:i:s").'
------------------------------------------
ARTICULO         CANT   COSTO    TOTAL
------------------------------------------';
    $sql = 'SELECT * FROM pr_movimientosd WHERE pmd_movimiento="'.$movimiento.'" AND pmd_tipo="'.$tipo.'"  AND pmd_cantidad != "0" order by a_nmb ASC';
    $result=setq($sql) or die($sql);

            if ($result){
            while ($row=mysql_fetch_array($result))
         	{
$producto = $row['a_nmb'];
$costo=$row['a_costos'];
$ped.='
'.sprintf("%-15s",substr($producto,0,12)).''.sprintf("%6s", number_format($row['pmd_cantidad'],2)).' '.sprintf("%8s", number_format($costo,2)).' '.sprintf("%8s", number_format($costo*$row['pmd_cantidad'],2)).'';

$sub+=$costo*$row['pmd_cantidad'];
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

class viewmovimientosprod {
  var $model;

function __construct($model) {
  $this->model = $model;
  $this->tipomov = array("E"=>"Entrada","S"=>"Salida","T"=>"Trasapaso");
  $this->estilomov = array("E"=>"primary","S"=>"danger","T"=>"info");
}

function movimiento($tipom,$motivo,$almacenfil,$page) {

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

  $sql = 'SELECT * FROM pr_almacenes WHERE pa_estatus = "A"';
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
  <a id="nuevo" class="btn btn-primary btn-sm" data-fancybox data-type="ajax" data-src="popup/setmovimientoprod.php" href="javascript:;">
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
    if ($row['pa_id'] == $almacenfil) {
        $selected = "selected";
    } else {
        $selected = "";
    }
    
    $options[] = array(
        'value' => $row['pa_id'],
        'selected' => $selected,
        'label' => $row['pa_nmb']
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
          window.location.href="?modulo=almacenesprod&accion=borrar&id=" + iduni;
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
  toolbar('Movimientos producción',$botones,$filtros);
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
            <th>Usuario</th>
            <th>Almacen Origen</th>
            <th>Almacen Destino</th>
            <th>Estatus</th>
            <th></th>
          </tr>
        </thead>';
  while($row = $this->model->resultmd->fetch_array()){
    echo '<tr>
            <th>'.$row['pm_folio'].'</th>
            <th>'.$this->tipomov[$row['pm_tipo']].'</th>
            <td>'.$row['pm_motivo'].'</td>
            <td>'.date('d-m-Y',strtotime($row['pm_fecha'])).'</td>';
            if($row['pm_estatus'] == "A") $fechaapli = date('d-m-Y',strtotime($row['pm_fechaap']));
            else $fechaapli = "";
            echo '
            <td>'.$fechaapli.'</td>
            <td>'.$row['pm_usuario'].'</td>
            <td>'.busca($row['pm_almacen'],'pr_almacenes','pa_id','pa_nmb').'</td>
            <td>'.busca($row['pm_almacendest'],'pr_almacenes','pa_id','pa_nmb').'</td>
            <td class="'.$this->model->nestatus[$row['pm_estatus']].'">'.$this->model->aestatus[$row['pm_estatus']].'</td>
            <td>
              <a href="?modulo=movimientosprod&accion=show&tipo='.$row['pm_tipo'].'&id='.$row['pm_id'].'">
                <button type="button" class="btn-sm btn-info btn"><i class="fa fa-eye"></i> Detalle</button>
              </a>';
      if($row['pm_estatus'] == "A")
        echo '<a target="_BLANK" href="formats/pdfmovimientoprod.php?tipo='.$row['pm_tipo'].'&id='.$row['pm_id'].'">
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
              url: "query/suggestmp.php",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  var valor = $(this).attr('value');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  $.ajax({
                    type: "POST",
                    url: "query/checkmp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var largo = document.getElementById('largodiv');
                      var ancho = document.getElementById('anchodiv');
                      var alto = document.getElementById('altodiv');
                      var largoin = document.getElementById('largo');
                      var anchoin = document.getElementById('ancho');
                      var altoin = document.getElementById('alto');
                      data = JSON.parse(dataRTN);
                      if(data['tipo'] == "A"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = true;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.removeAttribute('min');
                      } else if(data['tipo'] == "V"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = false;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.setAttribute('min', '0.01');
                      } else if(data['tipo'] == "P"){
                        largo.hidden = true;
                        ancho.hidden = true;
                        alto.hidden = true;
                        largoin.removeAttribute('min');
                        anchoin.removeAttribute('min');
                        altoin.removeAttribute('min');
                      }
                    }
                  });
                  $("#cantidad").focus();
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
          window.location.href="?modulo=movimientosprod&accion=delprod&id='.$this->model->id.'&tipo='.$this->model->tipo.'&idprod=" + idprod;
        }
      }
      function aplicamov(){
        var conf = confirm("Al aplicar este movimiento, se verá afectado el inventario\n¿Deseas continuar?");
        if(conf == true){
          window.location.href="?modulo=movimientosprod&accion=caducidades&id='.$this->model->id.'&tipo='.$this->model->tipo.'&almacen='.$this->model->almacen.'";
        }
      }
    </script>';

  $botones = '';
  $botones .= '
  <a href="?modulo=movimientosprod&accion=index" accesskey="">
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
  $botones .= '<a target="_BLANK" href="formats/pdfmovimientoprod.php?tipo='.$this->model->tipo.'&id='.$this->model->id.'">
            <button type="button" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Imprimir</button>
          </a>';

  if($this->model->estatus == "A"){
    //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
  }

  if($this->model->tipo == "T") $dest = ' <i class="icon-arrow-right3"></i> '.busca($this->model->almacendest,'pr_almacenes','pa_id','pa_nmb'); else $dest = "";

  if($this->model->estatus == "P") $leyenda = 'Añadir productos a '.$this->tipomov[$this->model->tipo];
  else  $leyenda = 'Lista de productos en '.$this->tipomov[$this->model->tipo];


  $readon = "";
  $exisantm = "";
  $repuestos = "";
  $apartantm = "";

  if($this->model->tipo == "T") $tipodemov = "Traspaso";
  elseif($this->model->tipo == "S") $tipodemov = "Salida";
  elseif($this->model->tipo == "E") $tipodemov = "Entrada";

  toolbar('Movimientos producción',$botones);


  echo '<div class="card mt-3">';
  if($this->model->estatus == "P")
    echo '
          <div class="card-body">
            <form method="post" class="row" autocomplete="off" action="?modulo=movimientosprod&accion=insertdetalle&id='.$this->model->id.'&tipo='.$this->model->tipo.'">
              <div class="row"> 
                <div class="col-md-6">
                  <h4 class="card-header">'.$leyenda.'
                  -
                  '.$this->model->motivo.' </h4>
                </div>
                <div class="col-md-3 alert-'.$this->estilomov[$this->model->tipo].'" style="display: flex; align-items: center;">'.$tipodemov.'</div>
                <div class="col-md-3 alert-info" style="display: flex; align-items: center;" >Almacen: '.busca($this->model->almacen,'pr_almacenes','pa_id','pa_nmb').' '.$dest.'</div>
              </div>
              <div class="col-8 col-md-4">
                <div class="mb-5">
                  <label for="agregar">Producto</label><br>
                  <div class="input-group">
                    <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control " required="required"  autofocus >
                    <!-- <a class="input-group-addon" href="popup/productos-buscar.php?id='.$this->model->id.'&from=ordenesc" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                      <button accesskey="B" type="button" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </a> -->
                  </div>
                 <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: scroll;"></div>
                </div>
              </div>
              <div class="col-4 col-md-3">
                <div class="mb-5">
                  <label for="cant">Cantidad</label><br>
                  <input type="number" min="0" max="9999999" value="1" step="0.01" name="cantidad" id="cantidad" placeholder="Cantidad" class="form-control" required="required" />
                </div>
              </div>
              <div class="mb-5 col-12 col-md-2 ms-5" id="largodiv" hidden>  
                <label>Largo:</label>
                <input type="number" min="0.01" step="0.01" id="largo" class="form-control" data-toggle="tooltip" data-placement="top" name="largo" value="0" onfocus="this.select();">
              </div>
              <div class="mb-5 col-12 col-md-2" id="anchodiv" hidden>  
                <label>Ancho:</label>
                <input type="number" min="0.01" step="0.01" id="ancho" class="form-control" data-toggle="tooltip" data-placement="top" name="ancho" value="0" onfocus="this.select();">
              </div>
              <div class="mb-5 col-12 col-md-2" id="altodiv" hidden>  
                <label>Alto:</label>
                <input type="number" min="0.01" step="0.01" id="alto" class="form-control" data-toggle="tooltip" data-placement="top" name="alto" value="0" onfocus="this.select();">
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
  }

  echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
        <thead class="thead bg-primary text-white">
          <tr>
            <th>Artículo</th>
            <th>Piezas del movimiento</th>
            <th>Existencia Actual</th>
            <th>'.$exisantm.'</th>
          </tr>
        </thead>';
  while($row = $this->model->resultart->fetch_array()){
    $nombre = $row['pmp_nmb'];
    $tipomp = $row['pmp_tipo'];
    $tipos = array('A'=>'mts cuadrados', 'P'=>'unidades', 'V'=>'mts cúbicos');

    if($row['pmp_tipo'] == "A") $nombre.=' Medidas: '.$row['pmd_largo'].' X '.$row['pmd_ancho'];
    else if($row['pmp_tipo'] == "V") $nombre.=' Medidas: '.$row['pmd_largo'].' X '.$row['pmd_ancho'].' X '.$row['pmd_alto'];

    
      $existencia = number_format(existenciamp($row['pmd_materiaprima'],$this->model->almacen), 2). ' '.$tipos[$tipomp];
    
    
    echo '<tr><form method="post" action="?modulo=movimientosprod&accion=updatedetalle&id='.$this->model->id.'&tipo='.$this->model->tipo.'&idprod='.$row['pmd_id'].'">
     <td>'.$nombre;

    if(busca($row['a_id'],'activo_producto','ap_producto','COUNT(*)') > 0){
      $numact = busca($this->model->id,'movimiento_activo','ma_idm = "'.$row['pmd_id'].'" AND ma_tipo = "'.$this->model->tipo.'" AND ma_movimiento','COUNT(*)');
      echo '<br><a href="popup/elijeactivos-movimientos.php?modulo='.$_GET['modulo'].'&accion=seleccionar&movimiento='.$this->model->id.'&idmov='.$row['pmd_id'].'&tipom='.$row['pmd_tipo'].'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
              <button type="button" class="btn btn-sm bg-teal bg-darken-2 text-white"><i class="icon-cup"></i> Seleccionar activos
                 <div class="tag tag-pill tag-danger">'.$numact.'</div>
              </button>
            </a>';
    }
    echo '</td>
              <th>
                <input type="number" value="'.number_format($row['pmd_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control" required="required" onfocus="this.select();" '.$readon.' />
              </th>
              <th>'.$existencia.'</th>
              <th>';

    if($this->model->estatus == "P")
          echo '<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-redo"></i> Actualizar</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="delprod('.$row['pmd_id'].')"><i class="fa fa-trash"></i> Borrar</button>';
    else
      echo number_format($row['pmd_existenciaant'], 2).' '.$tipos[$tipomp];

    echo '</th>';
      echo '
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
        $estatus = busca($_GET['id'], 'pr_movimientos', 'pm_tipo="' . $_GET['tipo'] . '" AND pm_id', 'pm_estatus');
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
                    <form autocomplete="off" method=post action=?modulo=movimientosprod&accion=insertdetalle&tipo=<?php echo $_GET['tipo'] ?>&mov=<?php echo $_GET['id'] ?> onsubmit="return checkSubmitmovimientosd();">
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
  $estatus = busca($_GET['id'], 'pr_movimientos', 'pm_tipo = "' . $_GET['tipo'] . '" AND pm_id', 'pm_estatus');
  $almaceno = busca($_GET['id'], 'pr_movimientos', 'pm_tipo = "' . $_GET['tipo'] . '" AND pm_id', 'pm_almacen');
  if($_GET['tipo'] == "T")
    $almacend = busca($_GET['id'], 'pr_movimientos', 'pm_tipo = "' . $_GET['tipo'] . '" AND pm_id', 'pm_almacendest');
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
    $cantidading = busca($_GET['last'],'pr_movimientosd','pmd_movimiento = "'.$_GET['id'].'" AND pmd_tipo = "'.$_GET['tipo'].'" AND pmd_materiaprima','SUM(pmd_cantidad)');
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
    $articulo = busca($row['pmd_materiaprima'],'articulos','a_sku','a_id');
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

//die(($estatus != "A" OR $_GET['tipo'] != "E") AND (busca($row['pmd_materiaprima'],'articulos','a_','a_caduca') == "1" OR busca($row['pmd_materiaprima'],'articulos','a_id','a_lote') == "1"));
    if(($estatus != "A" OR $_GET['tipo'] != "E") AND (busca($row['pmd_materiaprima'],'articulos','a_sku','a_caduca') == "1" OR busca($row['pmd_materiaprima'],'articulos','a_sku','a_lote') == "1"))
      echo '<tr><td style="background:'.$color.';"><a id="popup" href="popup/ingresalote.php?mov=M&id='.$row['pmd_movimiento'].'&idd='.$row['pmd_materiaprima'].'&tipo='.$row['pmd_tipo'].'&almacen='.$almacen.'" style="color:#FFFFFF;">'.strtoupper(busca($row['pmd_materiaprima'], 'articulos', 'a_sku', 'a_sku')).'</a></td>';
    else
      echo '<tr><td style="background:'.$color.';color:#FFFFFF;">'.strtoupper(busca($row['pmd_materiaprima'], 'articulos', 'a_sku', 'a_sku')).'</td>';
    echo '<td><center>' .busca($row['pmd_materiaprima'], 'articulos', 'a_sku', 'a_id'). '</center></td>
    <td style="word-break: break-all;"><center><a href=?modulo=productos&accion=show&id=' . $row['pmd_materiaprima'] . ' TARGET = "_blank" onclick="this.onclick = function(){return false;}">' . busca($row['pmd_materiaprima'], 'articulos', 'a_sku', 'a_nmb') . '</a></center></td>
    <td><center>'.$existencia.'
    <td><center>' . $row['pmd_cantidad'] . '</center></td>';    //die( '>>>>>>>>>>>>>>>>'.busca($row['pmd_materiaprima'], 'articulos1', 'a_id', 'a_nmb'));
    if ($estatus == "A" AND $_GET['tipo'] != "T"){
      echo'<th><center>'.$existencia.'</th>';
    }
    elseif ( $estatus =="A"){
      echo'<th><center>'.$existenciad.'</th>';
    }
    $estatus = busca($_GET['id'], 'pr_movimientos', 'pm_tipo="' . $_GET['tipo'] . '" AND pm_id', 'pm_estatus');
    if (busca($_GET['id'], 'pr_movimientos', 'pm_tipo = "' . $_GET['tipo'] . '" AND pm_id', 'pm_estatus') != "A") {
      echo '<td><a href=?modulo=movimientosprod&accion=delete&idd=' . $row['pmd_id'] . '&tipo=' . $_GET['tipo'] . '&mov=' . $_GET['id'] . '  onclick="alert(Seguro que quiere Eliminar");"
      value="Confirmation Alert"><center><input type="button" value="" id="borrar" class="botont"></center></a></td></tr>';
    }
  }
  echo '</table>';
}


function fechaCad(){
  $con = 'SELECT a_nmb FROM articulos
  INNER JOIN pr_movimientosd ON articulos.a_id=pr_movimientosd.pmd_materiaprima
  WHERE a_estatus=A AND pmd_movimiento="'.$_GET['id'].'"
  AND pmd_tipo="'.$_GET['tipo'].'" ORDER BY a_nmb'; //consulta para seleccionar las palabras a buscar, esto va a depender de su base de datos
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
    echo '<a href="?modulo=movimientosprod&accion=detalle&id='.$_GET['id'].'&tipo='.$_GET['tipo'].'" accesskey="r"><input type="button" value=" " id="atras" class="botont"></a>';
  if($this->model->caducacompleta($_GET['id'],$_GET['tipo']) == 1){
    echo '<a href="?modulo=movimientosprod&accion=aplicarmov&movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" title="Aplicar recepcion"  acceskey="A" onclick="this.onclick = function(){return false;}"><input type="button" value="" id="aplicar" class="botont"></a>';
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

          echo '<form autocomplete="off" name="insertacaducidad" method="post" action="?modulo=movimientosprod&accion=insertacaducidad&id='.$_GET['id'].'&tipo='.$_GET['tipo'].'" onsubmit="return checkSubmitguardar();">';
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
          echo '<td><a href="?modulo=movimientosprod&accion=deletecaducidad&idc='.$rowd['cm_id'].'&movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'"  onclick="this.onclick = function(){return false;}"><input type="button" value="" id="borrar" class="botont"></a></td></tr>';
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
            $pendientes = $row['pmd_cantidad'];
          else
            $pendientes = $row['suma'];
        if($pendientes != 0){

            echo '<tr><td>'.busca($row['pmd_materiaprima'],'articulos','a_id','a_nmb').'</b></td>';
            echo '<td>'.number_format($row['pmd_cantidad'],2).'</td>';
            echo '<td>'.number_format($pendientes,2).'</td>';
        }
    }
echo '</tbody></table>';

}


}
?>