<?php
class recepciones {
  var $model;
  var $view;

function recepciones() {
  $this->model = new modelrecepciones(isset($obj));
}

function index() {
  if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-60 days'));
  if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d');
  if (!isset($_REQUEST['proveedor'])) $_REQUEST['proveedor'] = NULL;
  if (!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
  if (!isset($_REQUEST['documento'])) $_REQUEST['documento'] = NULL;
  if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

  $this->model->result($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['estatus'],$_REQUEST['documento'],$_REQUEST['page']);
  $this->view = new viewrecepciones($this->model);
  $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['estatus'],$_REQUEST['documento'],$_REQUEST['page']);

}

function show(){
  $this->model->select($_GET['id']);
  $this->model->resultd($_GET['id']);
  $this->view = new viewrecepciones($this->model);
  $this->view->show();
}

function insert(){
  if($_POST['tiporec'] == "C"){
    $sqlna = 'SELECT COUNT(*) FROM activo_producto INNER JOIN articulos ON a_id = ap_producto
              INNER JOIN articulo_proveedor ON ap_articulo = a_id
              WHERE a_empresa = "'.$_SESSION['emp'].'" AND ap_proveedor = "'.$_POST['proveedor'].'"';
    $resulta = setq($sqlna);
    list($numa) = $resulta->fetch_array();
    if($numa == 0){
      ?>
        <script>
          alert("ERROR: El proveedor seleccionado, no tiene productos ligados a activos registrados\nElige un distinto");
          window.location.href="?modulo=recepciones&accion=index";
        </script>
      <?php
      die();
    }
  }

  $folioint = getmax('r_folio','recepciones','r_empresa = "'.$_SESSION['emp'].'"');
  $this->model->setdata(NULL,$folioint,$_POST['almacen'],$_POST['ffin'],$_POST['responsable'],$_POST['proveedor'],$_POST['foliodoc'],$_POST['tipodesc'],$_POST['descripcion'],$_POST['tiporec'],$_POST['ordenc']);
  $this->model->insert();

  if($this->model->ordenc){
    $sql = 'SELECT * FROM ordenescd WHERE od_ordenc = "'.$this->model->ordenc.'"';
    $result = setq($sql);
    while($rowo = $result->fetch_array()){
      $idrec = getmax('rd_id','recepcionesd','rd_recepcion','rd_recepcion = "'.$this->model->id.'"');
      $this->model->setDatad($this->model->id, $idrec, $rowo['od_cantidad'], $rowo['od_articulo'],$rowo['od_precio'],$rowo['od_iva']);
      $this->model->insertdetalle();
    }
  }

  redirect("?modulo=recepciones&accion=show&id=".$this->model->id);
}

function update(){

  $this->model->setdata($_POST['id'],$_POST['folioint'],$_POST['almacen'],$_POST['ffin'],$_POST['responsable'],$_POST['proveedor'],$_POST['foliodoc'],$_POST['tipodesc'],$_POST['descripcion'],$_POST['tiporec'],$_POST['ordenc']);
  $this->model->update();

  redirect("?modulo=recepciones&accion=show&id=".$this->model->id);
}

function insertdetalle(){
  $this->model->id = $_GET['id'];
  $articulo = getIDprod($_POST['producto']);

  if(!$articulo){
    echo '<script>
            alert("Error: El producto buscado no esta registrado en tu base de datos, verifica la información");
            window.location.href="?modulo=recepciones&accion=show&id='.$_GET['id'].'";
          </script>';
    die();
  }

  if(busca($articulo,'activo_producto','ap_producto','COUNT(*)') == 0 && busca($_GET['id'],'recepciones','r_id','r_tiporec') == "C"){
    echo '<script>
            alert("Error: El producto no tiene ningun activo ligado\nVerifica el producto");
            window.location.href="?modulo=recepciones&accion=show&id='.$_GET['id'].'";
          </script>';
    die();
  }

  $tiporec = busca($_GET['id'],'recepciones','r_id','r_tiporec');
  if($tiporec == "C"){
    $almacen = busca($_GET['id'],'recepciones','r_id','r_almacen');
    $sqlal = 'SELECT COUNT(*) FROM activo_almacen WHERE aa_activo IN
              (SELECT DISTINCT(ap_activo) FROM activo_producto WHERE ap_producto = "'.$articulo.'")';
    $resultal = setq($sqlal);
    list($numacs) = $resultal->fetch_array();
    if(!$numacs) $numacs = 0;

    $cantiprod = busca($_GET['id'],'recepcionesd','rd_articulo = "'.$articulo.'" AND rd_recepcion','SUM(rd_cantidad)')+$_POST['cantidad'];
    if($numacs < $cantiprod){
      echo '<script>
              alert("Error: NO cuentas con suficientes activos para contener el producto");
              window.location.href="?modulo=recepciones&accion=show&id='.$_GET['id'].'";
            </script>';
      die();
    }
 }

  if(isset($_POST['iva'])) $iva = 1; else $iva = 0;

  $idrec = getmax('rd_id','recepcionesd','rd_recepcion');
  $this->model->setDatad($_GET['id'], $idrec, $_POST['cantidad'], $articulo,$_POST['importe'],$iva);
  $this->model->insertdetalle();

  if($tiporec == "C") $this->model->checkactivos($_GET['id'],$idrec,$almacen);
  $this->model->setprecio($_GET['id']);

  redirect("?modulo=recepciones&accion=show&id=".$this->model->id);
}

function updatedetalle(){
  if(isset($_POST['iva'])) $iva = 1; else $iva = 0;

  $this->model->setDatad($_GET['id'], $_GET['idprod'], $_POST['cantidad'], NULL,$_POST['importe'],$iva);
  $this->model->updatedetalle();
  $this->model->setprecio($_GET['id']);

  redirect("?modulo=recepciones&accion=show&id=".$this->model->recepcion);
}

function delprod(){
  $this->model->delprod($_GET['id'],$_GET['idprod']);

  redirect("?modulo=recepciones&accion=show&id=".$_GET['id']);
}

function aplicar(){
  $this->model->select($_GET['id']);
  $this->model->aplicar($_GET['id']);
 // $this->model->imprimir($_GET['movimiento'],$_GET['tipo']);
  redirect('?modulo=recepciones&accion=show&id=' . $_GET['id']);
}

function updateactivos(){
  $sql = 'DELETE FROM recepcion_activo WHERE ra_recepcion = "'.$_GET['recepciones'].'" AND ra_idr = "'.$_GET['id'].'"';
  setq($sql);

  $almacen = busca($_GET['recepciones'],'recepciones','r_id','r_almacen');
  $sql = 'SELECT activos.*,aa_noserie,aa_id FROM activo_almacen INNER JOIN activos ON aa_activo = a_id
          WHERE aa_almacen = "'.$almacen.'" AND aa_estatus = "N" ORDER BY aa_id ASC';
  $result = setq($sql);
  while($rowr = $result->fetch_array()){
    if(isset($_POST['activo'.$rowr['aa_id']]))
      $this->model->setactivorec($_GET['recepciones'],$_GET['id'],$rowr['aa_id']);
  }

  if(isset($_GET['from'])){
    echo '<script>
            opener.location.reload();
            window.close();
          </script>';
  }
}

}

class modelrecepciones {
  function result($fini,$ffin,$proveedor,$estatus,$documento,$page){
    $prov = clearvmayus($proveedor);
    $doc = clearvmayus($documento);

    $sql = 'SELECT * FROM recepciones WHERE r_empresa = "'.$_SESSION['emp'].'"
            AND DATE(r_fechagen) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
    if($proveedor) $sql.=' AND r_proveedor IN (SELECT p_id FROM proveedores WHERE p_nmb LIKE "%'.$prov.'%" OR p_alias LIKE "%'.$prov.'%")';
    if($documento) $sql.=' AND r_foliodoc LIKE "%'.$doc.'%" ';
    if($estatus) $sql.=' AND r_estatus = "'.$estatus.'"';
    $sql.='ORDER BY r_id DESC';
    $result = setq($sql);
    if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
    $this->resultrc = setq($sql);
    $this->resultt = setq($sql);
  }

  function select($id){
    $sql = 'SELECT * FROM recepciones WHERE r_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['r_id'];
    $this->empresa = $row['r_empresa'];
    $this->folio = $row['r_folio'];
    $this->almacen = $row['r_almacen'];
    $this->cliente = $row['r_cliente'];
    $this->fechacompra= $row['r_fechacompra'];
    $this->comprador= $row['r_comprador'];
    $this->fechagen = $row['r_fechagen'];
    $this->ugen = $row['r_ugen'];
    $this->fechaapli = $row['r_fechaapli'];
    $this->uapli = $row['r_uapli'];
    $this->ordenc = $row['r_ordenc'];
    $this->proveedor = $row['r_proveedor'];
    $this->fechacan = $row['r_fechacan'];
    $this->ucan = $row['r_ucan'];
    $this->mcan = $row['r_mcan'];
    $this->estatus = $row['r_estatus'];
    $this->precio = $row['r_precio'];
    $this->descuento = $row['r_descuento'];
    $this->tipodesc = $row['r_tipodesc'];
    $this->observaciones = $row['r_observaciones'];
    $this->foliodoc = $row['r_foliodoc'];
    $this->tiporec = $row['r_tiporec'];
    $this->subtotal = $row['r_subtotal'];
    $this->iva = $row['r_iva'];
    $this->total = $row['r_total'];
  }

  function setdata($id,$folioint,$almacen,$fechacompra,$responsable,$proveedor,$foliodoc,$tipodesc,$descripcion,$tiporec,$ordenc){
    $this->id = $id;
    $this->folioint = $folioint;
    $this->almacen = $almacen;
    $this->fechacompra = $fechacompra;
    $this->responsable = $responsable;
    $this->proveedor = $proveedor;
    $this->foliodoc = clearvmayus($foliodoc);
    $this->tipodesc = $tipodesc;
    $this->descripcion = clearvmayus($descripcion);
    $this->tiporec = clearvmayus($tiporec);
    $this->ordenc = clearvmayus($ordenc);
  }

  function insert(){
    $sql = 'INSERT INTO recepciones SET
            r_empresa = "'.$_SESSION['emp'].'",
            r_folio = "'.$this->folioint.'",
            r_almacen = "'.$this->almacen.'",
            r_fechacompra = "'.$this->fechacompra.'",
            r_comprador = "'.$this->responsable.'",
            r_proveedor = "'.$this->proveedor.'",
            r_tipodesc = "'.$this->tipodesc.'",
            r_foliodoc = "'.$this->foliodoc.'",
            r_ordenc = "'.$this->ordenc.'",
            r_observaciones = "'.$this->descripcion.'",
            r_tiporec = "'.$this->tiporec.'",
            r_fechagen = "'.date('Y-m-d H:i:s').'",
            r_ugen = "'.$_SESSION['uid'].'",
            r_estatus	 = "N"';
    setq($sql);

    $this->id = getmax('r_id','recepciones','r_empresa = "'.$_SESSION['emp'].'"',false);
  }

  function update(){
    $sql = 'UPDATE recepciones SET
            r_folio = "'.$this->folioint.'",
            r_almacen = "'.$this->almacen.'",
            r_fechacompra = "'.$this->fechacompra.'",
            r_comprador = "'.$this->responsable.'",
            r_proveedor = "'.$this->proveedor.'",
            r_tipodesc = "'.$this->tipodesc.'",
            r_foliodoc = "'.$this->foliodoc.'",
            r_tiporec = "'.$this->tiporec.'",
            r_ordenc = "'.$this->ordenc.'",
            r_observaciones = "'.$this->descripcion.'",
            r_fechagen = "'.date('Y-m-d H:i:s').'",
            r_ugen = "'.$_SESSION['uid'].'",
            r_estatus	 = "N"
            WHERE r_id = "'.$this->id.'"';
    setq($sql);
  }
  function comprueba($id) {
    $sql = 'SELECT count(*) FROM recepcionesd WHERE rd_recepcion = "' . $id . '"';
    $result = setq($sql);
    list($cuenta) = $result->fetch_array();
    if ($cuenta >= 1) $ok = 1;
    else $ok = 0;

    if($ok == 1){
      $tipoc = busca($id,'recepciones','r_id','r_tiporec');
      if($tipoc == "C"){
        $sql = 'SELECT SUM(rd_cantidad) FROM recepcionesd WHERE rd_recepcion = "'.$id.'"
                AND rd_articulo IN (SELECT ap_producto FROM activo_producto)';
        $result = setq($sql);
        list($numprods) = $result->fetch_array();
        if(!$numprods) $numprods = 0;

        $sqlr = 'SELECT COUNT(*) FROM recepcion_activo WHERE ra_recepcion = "'.$id.'"';
        $resultr = setq($sqlr);
        list($numracs) = $resultr->fetch_array();
        if(!$numracs) $numracs = 0;

        if($numracs != $numprods) $ok = 0;
      }
    }

    return($ok);
  }

  function resultd($id){
    $sql = 'SELECT * FROM recepcionesd INNER JOIN articulos ON a_id = rd_articulo
            WHERE rd_recepcion = "'.$id.'" ORDER BY rd_id';
    $this->resultr = setq($sql);
  }

  function setDatad($recepcion, $idrec, $cantidad, $articulo,$importe,$iva){
    $this->recepcion = $recepcion;
    $this->idrec = $idrec;
    $this->cantidad = $cantidad;
    $this->articulo = $articulo;
    $this->importe = $importe;
    $this->iva = $iva;
  }

  function insertdetalle(){
    $sql = 'INSERT INTO recepcionesd SET
            rd_recepcion = "'.$this->id.'",
            rd_id = "'.$this->idrec.'",
            rd_articulo = "'.$this->articulo.'",
            rd_cantidad = "'.$this->cantidad.'",
            rd_costo = "'.$this->importe.'",
            rd_iva = "'.$this->iva.'"';
    setq($sql);
  }

  function updatedetalle(){
    $sql = 'UPDATE recepcionesd SET
            rd_cantidad = "'.$this->cantidad.'",
            rd_costo = "'.$this->importe.'",
            rd_iva = "'.$this->iva.'"
            WHERE rd_recepcion = "'.$this->recepcion.'" AND rd_id = "'.$this->idrec.'"';
    setq($sql);
  }

  function aplicar($id){
    $tiporec = busca($id,'recepciones','r_id','r_tiporec');
    if($tiporec == "A"){
      $sql = 'SELECT r_id,rd_articulo,r_almacen,rd_id,rd_costo,rd_iva,rd_cantidad
              FROM recepcionesd INNER JOIN recepciones ON r_id = rd_recepcion
              WHERE r_estatus = "N" AND r_id = "'.$id.'"';
      $result1 = setq($sql) or die($sql);
      while($rowe = $result1->fetch_array()){
        $existeal = existencia($rowe['rd_articulo'],$rowe['r_almacen']);
        $sqld = 'UPDATE recepcionesd SET rd_existenciaant = "'.$existeal.'" WHERE
                 rd_recepcion = "' .  $rowe['r_id'] . '" AND rd_id = "' . $rowe['rd_id'] . '" ';
        setq($sqld);
      }
      $result1 = setq($sql) or die($sql);
      $poriva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva')/100;
      while ($row = $result1->fetch_array()) {
        $costo = $row['rd_costo']/$row['rd_cantidad'];
        if($row['rd_iva'] == "1") $costo*(1+$poriva);
        sumaexistencia($row['rd_articulo'],$row['rd_cantidad'],$row['r_almacen'],$costo,"R",$id,date('Y-m-d'));
      }
    }
    else{
      $sql = 'SELECT r_id,rd_articulo,r_almacen,rd_id,rd_costo,rd_iva,rd_cantidad
              FROM recepcionesd INNER JOIN recepciones ON r_id = rd_recepcion
              WHERE r_estatus = "N" AND r_id = "'.$id.'"';
      $result1 = setq($sql) or die($sql);
      $ok = 1;
      while($rowv = $result1->fetch_array()){
        $sqlrd = 'SELECT * FROM recepcion_activo WHERE ra_recepcion = "' .  $rowv['r_id'] . '"
                  AND ra_idr = "' . $rowv['rd_id'] . '"';
        $resultrd = setq($sqlrd);
        while($rowrd = $resultrd->fetch_array()){
          if(busca($rowrd['ra_activo'],'activo_almacen','aa_id','aa_estatus') != "N") $ok = 0;
        }
      }

      if($ok == 1){
        $result1 = setq($sql) or die($sql);
        while($rowe = $result1->fetch_array()){
          $existeal = existencia($rowe['rd_articulo'],$rowe['r_almacen']);
          $sqld = 'UPDATE recepcionesd SET rd_existenciaant = "'.$existeal.'" WHERE
                   rd_recepcion = "' .  $rowe['r_id'] . '" AND rd_id = "' . $rowe['rd_id'] . '" ';
          setq($sqld);
        }
        $result1 = setq($sql) or die($sql);
        $poriva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva')/100;
        while ($row = $result1->fetch_array()) {
          $costo = $row['rd_costo']/$row['rd_cantidad'];
          if($row['rd_iva'] == "1") $costo*(1+$poriva);

          $sqlrd = 'SELECT * FROM recepcion_activo WHERE ra_recepcion = "' .  $row['r_id'] . '"
                    AND ra_idr = "' . $row['rd_id'] . '"';
          $resultrd = setq($sqlrd);
          while($rowrd = $resultrd->fetch_array()){
            sumaexistencia($row['rd_articulo'],1,$row['r_almacen'],$costo,"R",$id,date('Y-m-d'),$rowrd['ra_activo']);

            $sqlua = 'UPDATE activo_almacen SET aa_estatus = "L" WHERE aa_id = "'.$rowrd['ra_activo'].'"';
            setq($sqlua);
          }
        }
      }
      else{
        die(alert_back("Error: Revisa los activos, existen activos que no estan disponibles",true));
      }
    }

    /*
        include_once('cxpagar.php');
        $cxp = new modelcxpagar();
        $cxp->insertcxpagar($this->empresa,$this->proveedor,$this->id,"C",$this->total,date('Y-m-d H:i:s'),"ORDEN DE COMPRA ".$this->foliodoc);
    */

    $sql3 = 'UPDATE recepciones SET r_estatus = "A", r_fechaapli = "'.date('Y-m-d H:i:s').'", r_uapli = "'.$_SESSION['uid'].'"
             WHERE r_id="' . $id . '"';
    setq($sql3);

    $ordenc = busca($id,'recepciones','r_id','r_ordenc');
    if($ordenc){
      $sql4 = 'UPDATE ordenesc SET o_estatus = "F" WHERE o_id = "'.$ordenc.'"';
      setq($sql4);
    }
  }

  function delprod($id,$idprod){
    $sql = 'DELETE FROM recepcionesd WHERE rd_recepcion = "'.$id.'" AND rd_id = "'.$idprod.'"';
    setq($sql);

    $sql = 'DELETE FROM recepcion_activo WHERE ra_recepcion = "'.$id.'" AND ra_idr = "'.$idprod.'"';
    setq($sql);
  }

  function checkactivos($id,$idrec,$almacen){
    $sqlp = 'SELECT rd_articulo,rd_cantidad FROM recepcionesd
              WHERE rd_recepcion = "'.$id.'" AND rd_id = "'.$idrec.'"';
    $resultp = setq($sqlp);
    list($prod,$cantidad) = $resultp->fetch_array();

    $sqlac = 'SELECT ap_activo FROM activo_producto WHERE ap_producto = "'.$prod.'"';
    $resultac = setq($sqlac);
    $numacs = 0;
    while($rowac = $resultac->fetch_array()){
      $sqlna = 'SELECT COUNT(*) FROM activo_almacen WHERE aa_almacen = "'.$almacen.'"
                AND aa_activo = "'.$rowac['ap_activo'].'" AND aa_estatus = "N"';
      $resultna = setq($sqlna);
      list($actives) = $resultna->fetch_array();
      if(!$actives) $actives = 0;
      $numacs+=$actives;
    }
    if($numacs >= $cantidad){
      $sqlaac = 'SELECT aa_id FROM activo_almacen WHERE aa_almacen = "'.$almacen.'" AND aa_estatus = "N"
                 AND aa_activo IN (SELECT ap_activo FROM activo_producto WHERE ap_producto = "'.$prod.'")
                 ORDER BY aa_noserie';
      $resultaac = setq($sqlaac);
      $vlt = 0;
      while($rowaa = $resultaac->fetch_array()){
        $vlt++;

        if($vlt <= $cantidad)
          $this->setactivorec($id,$idrec,$rowaa['aa_id']);
      }
    }
  }

  function setactivorec($id,$idrec,$idactivo){
    $sql = 'SELECT aa_estatus FROM activo_almacen WHERE aa_id = "'.$idactivo.'"';
    $result = setq($sql);
    list($estatus) = $result->fetch_array();
    if($estatus == "N"){
      $sqli = 'INSERT INTO recepcion_activo SET
               ra_recepcion = "'.$id.'",
               ra_idr = "'.$idrec.'",
               ra_activo = "'.$idactivo.'"';
      setq($sqli);
    }
  }

  function setprecio($id){
    $sql = 'SELECT * FROM recepcionesd WHERE rd_recepcion = "'.$id.'"';
    $result = setq($sql);
    $total = 0;
    $iva = 0;
    $subtotal = 0;
    while($row = $result->fetch_array()){
      $subtotal+=$row['rd_costo'];
      if($row['rd_iva'] == "1") $iva+=($row['rd_costo']*0.16);
    }
    $total = $subtotal+$iva;

    $sql = 'UPDATE recepciones SET r_subtotal = "'.$subtotal.'", r_iva = "'.$iva.'", r_total = "'.$total.'"
            WHERE r_id = "'.$id.'" ';
    setq($sql);
  }
}

class viewrecepciones {
  var $model;

function viewrecepciones($model) {
  $this->model = $model;
  $this->tipodoc = array("F"=>"Factura","R"=>"Remisión");
}

function browse($fini,$ffin,$proveedor,$estatus,$documento,$page) {
?>
<script language="JavaScript">
function checkSubmitmovimiento() {
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
</script>
<?php
  $this->model->aestatus = array("A" => "Finalizado","P" => "En espera de autorización","N" => "En Captura","C" => "Cancelado");
  $this->model->nestatus = array("A" => "btn-success","P" => "bg-amber bg-darken-2","N"=>"bg-yellow bg-darken-3","C" => "btn-danger");

  $accion = 'insert';
  if (isset($this->model->id)) {
    $accion = 'update';
  }
   //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions">';
    //Sección: E2 Encabezado - Filtros

    $selt = '';
    $sela = '';
    $seln = '';
    $selp = '';
    $selc = '';
    if($estatus == "A") $sela = "selected";
    if($estatus == "N") $seln = "selected";
    if($estatus == "P") $selp = "selected";
    if($estatus == "C") $selc = "selected";
    else $selt = 'selected';
  ?>

    <div class="ml-2 col-md-3">
      <a data-fancybox data-type="ajax" data-src="popup/setrecepcion.php" href="javascript:;">
        <button type="button" class="btn btn-primary">
          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
        </button>
      </a>
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>
    <div id="filter-panel" class="col-md-12 collapse filter-panel mb-2">
      <div class="panel panel-default">
        <div class="panel-body">
          <form class="form-inline" role="form" method="post" >
            <div class="mb-5 mr-1">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="<?php echo $fini ?>" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="<?php echo $ffin?>" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Proveedor</label>
              <input type="text" class="form-control" name="proveedor" placeholder="Nombre del proveedor que buscas" onfocus="this.select();" value="<?php echo $proveedor ?>" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value="" <?php echo $selt; ?> >Todos</option>
                <option value="N" <?php echo $seln; ?> >En captura</option>
                <option value="A" <?php echo $sela; ?> >Aplicados</option>
                <option value="P" <?php echo $selp; ?> >Espera de aplicación</option>
                <option value="C" <?php echo $selc; ?> >Cancelados</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <button type="submit" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
              </button>
                    <a href="?modulo=recepciones&accion=index"><button type="button" class="btn btn-warning">
              <!-- <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-warning"> -->
                <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
              </button></a>
            </div>
          </form>
        </div>
      </div>
    </div>
    </div></div>
    <div class="main-card mb-3 card">
    <div class="card-body row">

  <?php
  echo '<div class="table table-responsive">
        <table class="table table-hover table-striped">
        <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
          <tr>
            <th>No. Compra</th>
            <th>Proveedor</th>
            <th>Tipo</th>
            <th>Último Movimiento</th>
            <th>Almacen</th>
            <th>Folio del documento</th>
            <th>Estatus</th>
            <th></th>
          </tr>
        </thead>';
  while($row = $this->model->resultrc->fetch_array()){
    if($row['r_estatus'] == "A"){
      $lastm = "Aplicación: <b>".$row['r_uapli'].'</b><br>'.date('d-m-Y',strtotime($row['r_fechaapli']));
    }
    elseif($row['r_estatus'] == "C"){
      $lastm = "Cancelación: <b>".$row['r_ucan'].'</b><br>'.date('d-m-Y',strtotime($row['r_fechacan']));
    }
    else{
      $lastm = "Registro: <b>".$row['r_ugen'].'</b><br>'.date('d-m-Y',strtotime($row['r_fechagen']));
    }
    if($row['r_tiporec'] == "A") $tipor = "Recepción de artículos";
    else $tipor = "Compra de activos";

    echo '<tr>
            <th>'.$row['r_id'].'</th>
            <td>'.busca($row['r_proveedor'],'proveedores','p_id','p_alias').'</td>
            <td>'.$tipor.'</td>
            <td>'.$lastm.'</td>
            <td>'.busca($row['r_almacen'],'almacenes','a_id','a_nmb').'</td>
            <td>'.$row['r_foliodoc'].'</td>
            <td class="'.$this->model->nestatus[$row['r_estatus']].'">'.$this->model->aestatus[$row['r_estatus']].'</td>
            <td>
              <a href="?modulo=recepciones&accion=show&id='.$row['r_id'].'">
                <button type="button" class="btn-sm btn-info"><i class="fa fa-eye"></i> Detalles</button>
              </a>';
      if($row['r_estatus'] == "A")
        echo '<a target="_BLANK" href="formats/printrecepcion&id='.$row['r_id'].'">
                <button type="button" class="btn-sm btn-secondary"><i class="icon-print"></i> Imprimir</button>
              </a>';

    echo '</td>
          </tr>';
  }
  echo '</table></div>';
  if($_REQUEST['proveedor']){


  }elseif($_REQUEST['fini'] == date('Y-m-d',strtotime('-60 days')) && $_REQUEST['ffin'] == date('Y-m-d')) {

  echo '
  <div class="col-md-12 text-xs-center">
  <div class="mb-3">
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <li class="page-item">
          <a class="page-link" href="?modulo=recepciones&accion=index&page='.($_GET['page']-1).'" aria-label="Previous">
            <span aria-hidden="true">&laquo; Ant</span>
            <span class="sr-only">Anterior</span>
          </a>
        </li>';
        
  $numres = 50;
  $nr = $this->model->resultt->num_rows;
  $np = $nr/$numres;
  $paginaa = $page-1;
  $pagina = $page+1;
  $sqlpg = 'SELECT COUNT(*) FROM recepciones WHERE r_empresa = "'.$_SESSION['emp'].'"
  AND DATE(r_fechagen) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
  $resultpg = setq($sqlpg);
  list($numg) =  $resultpg->fetch_array();
  $pageres = ceil($numg/$numres);
  //die($numg);

  if($pageres>10){
    if($_GET['page'] == 0) { $min = 1; $nombre = "Inicio";}
    else {$min = $_GET['page']-1; $nombre = "Inicio...";}
          if($min <= 1) $min = 1;

    if($_GET['page'] == ($pageres-1)) $max = ($pageres-1);
    else $max = $_GET['page']+3;
          if($max >= ($pageres-1)) $max = ($pageres-1);

    if($_GET['page'] == 0) $active = "active";
    else $active = "";
    echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=recepciones&accion=index&page=0">'.$nombre.'</a></li>';
  }elseif($pageres<6){
    $max=$pageres;
    $min=0;
  }

  //for($i=0;$i<$pageres;$i++){
  for($i=$min;$i<$max;$i++){
      if($_GET['page'] == $i) $active = "active";
      else $active = "";
      if($i == 0) $nombre = "Inicio";
      else $nombre = $i;
    echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=recepciones&accion=index&page='.$i.'">'.$nombre.'</a></li>';
  }
  
  if($pageres<9){

  }else{
  if($_GET['page'] == ($pageres-5)) $nombre = ($pageres-2);
  else $nombre = '... '.($pageres-2);
  if($_GET['page'] == $i) $active = "active";
  else $active = "";
  if($_GET['page'] >= ($pageres-4)) echo '';
  else echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=recepciones&accion=index&page='.($pageres-2).'">'.$nombre.'</a></li>';
    echo '
    <li class="page-item '.$active.'"><a class="page-link" href="?modulo=recepciones&accion=index&page='.($pageres-1).'">'.($pageres-1).'</a></li>';

  }
    echo '<li class="page-item">
                    <a class="page-link" href="?modulo=recepciones&accion=index&page='.($_GET['page']+1).'" aria-label="Next">
                      <span aria-hidden="true">Sig &raquo;</span>
                      <span class="sr-only">Siguiente</span>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>'; 
  }else {
    
  }
}

function show(){
//  if()
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
            var conf = confirm("¿Deseas borrar el producto de la recepción?");
            if(conf == true){
              window.location.href="?modulo=recepciones&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
            }
          }
          function aplicamov(){
            var conf = confirm("Al aplicar esta recepción, se verá afectado el inventario y se recalcularán costos y precios\n¿Deseas continuar?");
            if(conf == true){
              window.location.href="?modulo=recepciones&accion=aplicar&id='.$this->model->id.'";
            }
          }
        </script>';

  echo '<div class="row page-title-actions"><div class="col-4 col-md-3">';
  echo '<a href="?modulo=recepciones&accion=index" accesskey="">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
        </a>';

  if ($this->model->comprueba($this->model->id) == 1 AND $this->model->estatus != "A") {
    echo '<button class="btn btn-primary ml-1" onclick="aplicamov()"><i class="fa fa-check"></i> Aplicar</button>';
  }
  if($this->model->estatus == "A")
    echo '<a target="_BLANK" href="formats/printrecepcion.php?id='.$this->model->id.'">
            <button type="button" class="btn btn-secondary ml-1"><i class="icon-print"></i> Imprimir</button>
          </a>';

  echo '</div>';

  if($this->model->estatus == "A"){
    //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
  }

  if($this->model->estatus == "N") $leyenda = 'Añadir productos a la recepción ';
  else  $leyenda = 'Lista de productos en la recepción ';

  echo '</div></div>';

  $readon = "";
  $exisantm = "";
  $miva = "";

  if($this->model->estatus == "N")
    echo '<div class="row">
            <form method="post" autocomplete="off" action="?modulo=recepciones&accion=insertdetalle&id='.$this->model->id.'">
              <div class="mt-1 col-8 col-md-8 alert alert-primary">
                '.$leyenda.'
                 -
                 '.$this->model->foliodoc.'
              </div>
              <div class="mt-1 col-4 col-md-4 alert alert-info">Almacen: '.busca($this->model->almacen,'almacenes','a_id','a_nmb').'</div>
              <div class="col-8 col-md-4">
                <div class="mb-5">
                  <label for="agregar" class">Producto</label><br>
                  <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus >
                 <div id="suggestions"></div>
                </div>
              </div>
              <div class="col-4 col-md-2">
                <div class="mb-5">
                  <label for="cant">Cantidad</label><br>
                  <input type="number" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control" required="required" />
                </div>
              </div>
              <div class="col-4 col-md-3">
                <div class="mb-5">
                  <label for="cant">Total (Antes de IVA)</label><br>
                  <input type="number" min="0" max="9999999" step="0.01" name="importe" placeholder="Importe del concepto" class="form-control" required="required" />
                </div>
              </div>
              <div class="col-4 col-md-1">
                <div class="mb-5">
                  <label for="cant">IVA</label><br>
                  <input type="checkbox" name="iva" '.$miva.' />
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
    echo '<div class="mt-1 col-8 col-md-8 alert alert-primary">
            '.$leyenda.' - '.$this->model->foliodoc.'
          </div>';
    $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
    $exisantm = "Existencia antes del movimiento";
  }

  echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
        <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
          <tr>
            <th width="30%">Artículo</th>';
      if($this->model->tiporec == "C")
        echo '<th>Activos ligados</th>';

      echo '<th>Piezas Compradas</th>
            <th>Precio unitario</th>
            <th>IVA</th>
            <th>Importe total</th>
            <th>'.$exisantm.'</th>
          </tr>
        </thead>';
  $subtotal = 0;
  $totiva = 0;
  $poriva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva')/100;
  if($this->model->estatus == "A") $readonly = "disabled";

  while($row = $this->model->resultr->fetch_array()){
    $importe = $row['rd_costo']/$row['rd_cantidad'];
    $subtotal+=$row['rd_costo'];

    if($row['rd_iva'] == "1"){
      $chiva = 'checked';
      $iva = $row['rd_costo']*$poriva;
    }
    else{
      $chiva = "";
      $iva = 0;
    }
    $totiva+=$iva;

    echo '<tr><form method="post" action="?modulo=recepciones&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['rd_id'].'">
            <td>'.$row['a_nmb'].'</td>';

    if($this->model->tiporec == "C"){
      echo '<td>';
      if(busca($row['rd_articulo'],'activo_producto','ap_producto','COUNT(*)') > 0){
        $numact = busca($this->model->id,'recepcion_activo','ra_idr = "'.$row['rd_id'].'" AND ra_recepcion','COUNT(*)');
        echo '<a href="popup/elijeactivos?modulo='.$_GET['modulo'].'&accion=seleccionar&recepcion='.$this->model->id.'&idrec='.$row['rd_id'].'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                <button type="button" class="btn-sm bg-teal bg-darken-2 text-white"><i class="icon-cup"></i> Seleccionar activos
                   <div class="tag tag-pill tag-danger">'.$numact.'</div>
                </button>
              </a>';
      }
      else echo 'Sin activos ligados';
      echo '</td>';
    }

    echo '<td>
              <input type="number" value="'.number_format($row['rd_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
            </td>
            <td class="number-align">
              <input type="number" value="'.number_format($importe,2,'.','').'" min="0" max="9999999" step="0.01" name="importe" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" readonly="readonly" />
            </td>
            <td><input type="checkbox" name="iva" '.$chiva.' '.$readon.' /></td>
            <td>
              <input type="number" value="'.number_format($row['rd_costo'],2,'.','').'" min="0" max="9999999" step="0.01" name="importe" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
            </td>
            <td>';

  if($this->model->estatus == "N")
        echo '<button type="submit" class="btn-sm btn-primary"><i class="fa fa-redo"></i> Actualizar</button>
              <button type="button" class="btn-sm btn-danger" onclick="delprod('.$row['rd_id'].')"><i class="fa fa-trash"></i> Borrar</button>';
  else
    echo $row['rd_existenciaant'];

      echo '</td>
          </form></tr>';
  }
  echo '<tfoot>
          <tr>
            <td colspan="2">&nbsp;</td>
            <td colspan="2" class="number-align alert alert-primary h4">Subtotal</td>
            <td class="number-align alert alert-primary h4">'.number_format($subtotal,2).'</td>
          </tr>';
  $totalf = $subtotal+$totiva;
  if($totiva > 0){
    echo '<tr>
            <td colspan="2" >&nbsp;</td>
            <td colspan="2"  class="number-align alert alert-info h4">IVA</td>
            <td  class="number-align alert alert-info h4">'.number_format($totiva,2).'</td>

          </tr>';
    echo '<tr >
            <td colspan="2">&nbsp;</td>
            <td colspan="2"  class="number-align alert alert-primary h4">Total</td>
            <td  class="number-align alert alert-primary h4">'.number_format($totalf,2).'</td>
          </tr>';
  }
  echo '
        </tfoot>';
  echo '</table></div>';

}

}
?>