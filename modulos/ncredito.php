<?php
//ini_set('display_errors', 1);
class ncredito{

var $model;
var $view;

function __construct(){ // constructor
  $this->model= new Modelncredito; // crea modelo
}

function index(){
    if(!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
    if(!isset($_REQUEST['folio'])) $_REQUEST['folio'] = NULL;
    if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

    $this->model->resultncr(trim($_REQUEST['cliente']),$_REQUEST['page'],$_REQUEST['estatus'],trim($_REQUEST['folio']),$_SESSION['emp']);
    $this->view = new  Viewncredito($this->model);
    $this->view->browsencr($_REQUEST['page'],$_REQUEST['cliente'],$_REQUEST['estatus'],$_REQUEST['folio'],$_SESSION['emp']);
}

function nuevancr(){
  $idncr = $this->model->insertncreditoblanco($_REQUEST['cliente'],$_REQUEST['fiscales'],$_SESSION['emp'],$_POST['version']);
  if(isset($_REQUEST['remision']))
    $this->model->insertncrrem($idncr,$_REQUEST['remision']);

  redirect("?modulo=ncredito&accion=showncredito&ncredito=".$idncr."&new=1");
}

function cancelacion(){
    if(!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;
    if(!isset($_REQUEST['folio'])) $_REQUEST['folio'] = NULL;

    $this->model->resultcan($_REQUEST['cliente'],$_REQUEST['folio'],$_SESSION['emp'],$_REQUEST['tipo']);
    $this->view = new  Viewncredito($this->model);
    $this->view->browsecan($_REQUEST['cliente'],$_REQUEST['folio'],$_SESSION['emp'],$_REQUEST['tipo']);
}

function insertncredito(){
    $ncredito = $this->model->insertncredito($_GET['ncredito']);

redirect('?modulo=ncredito&accion=showncredito&ncredito='.$ncredito);
}

function showncredito(){
  if(!isset($_GET['ncredito'])) $_GET['ncredito'] = NULL;
  if(!isset($_GET['editc'])) $_GET['editc'] = NULL;

  $this->model->selectncr($_GET['ncredito']);
  $this->model->selectd($_GET['ncredito'],$_GET['editc']);
  $this->view = new Viewncredito($this->model);
  $this->view->showncredito($_GET['ncredito']);
}

function quitaret(){
      if(isset($_POST['retencion'])) $retencion = 1;
      else $retencion= 0;

      if(isset($_POST['iva'])) $iva = 1;
      else $iva= 0;

      $this->model->quitaret($_GET['id'],$_GET['ncredito'],$iva,$retencion);

      redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['ncredito']);
}

function insertcon(){
    if(isset($_POST['reten'])) $retenma = 1;
    else $retenma = 0;

    if(isset($_POST['iva'])) $ivama = 1;
    else $ivama = 0;

    if(isset($_POST['ivain']) && $ivama == "1") $ivain = 1;
    else $ivain = 0;

    $_POST['articulo'] = str_replace('"','',$_POST['articulo']);
    $_POST['articulo'] = str_replace("'","",$_POST['articulo']);

    if(!isset($_POST['idd'])) {
      $idf = $this->model->insertcon($_GET['ncredito'],$_POST['cantidad'],$_POST['costo'],$_POST['unidad'],$_POST['articulo'],$_POST['producto'],$retenma,$ivama,$ivain);
    }
    else{
      $this->model->updatecon($_POST['idd'],$_GET['ncredito'],$_POST['cantidad'],$_POST['costo'],$_POST['unidad'],$_POST['articulo'],$_POST['producto'],$retenma,$ivama,$ivain);
    }

  redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['ncredito']);
}


function borrarart() {
        $this->model->borrarart($_GET['ncredito'], $_GET['id']);
  //          $this->model->insertadenda($_GET['ncredito'],NULL,"R");

      redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['ncredito']);
}

function actualizapagos() {
    $this->model->actualizapago($_POST['formapago'], $_POST['tipocambio'], $_POST['metodopago'], $_POST['numcta'], $_GET['id'], $_POST['valorm'], $_POST['condicion']);
    redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id']);
}


function finalcaptura() {
        $this->model->finalcaptura($_GET['id']);
      redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id']);
}


function abrir(){
      $this->model->abrir($_GET['id']);
      redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id']);
}


function timbrar(){
   $ticket = $_GET['id'];
   $cliente = busca($ticket,'ncredito','n_id','n_cliente');

   $restantes = busca("1",'folios','f_cliente = "'.$_SESSION['emp'].'" AND f_estatus','SUM(f_contratados)') - busca("1",'folios','f_cliente = "'.$_SESSION['emp'].'" AND f_estatus','SUM(f_usados)');
   if($restantes <= 0){
      die(alert_back("Error los tus folios se han agotado ",true));
    }

  $sql = 'SELECT * FROM folios WHERE f_estatus = "1" ORDER BY f_id ASC LIMIT 0,1';
  $result = setq($sql) or die(mysql_error() . $sql);
  $row = $result->fetch_array();

  $sql2 = 'SELECT MAX(fd_id) FROM foliosd WHERE fd_contrato = "' . $row['n_id'] . '"';
  $result2 = setq($sql2) or die(mysql_error());
  list($id) = $result2->fetch_array();

  $id = $id + 1;

  if(busca($ticket,'foliosd','fd_factura','COUNT(*)') == 0){
      $sql3 = 'INSERT INTO foliosd(fd_id, fd_contrato, fd_cliente, fd_factura, fd_fechatimbrado,fd_timbro)VALUES
               ("' . $id . '",  "' . $row['n_id'] . '", "' . busca($_GET['id'], 'ncredito', 'n_id', 'n_cliente') . '" , "' . $_GET['id'] . '", "' . date('Y-m-d H:i:s') . '","'.$_SESSION['uid'].'")
              ON DUPLICATE KEY UPDATE fd_factura = "'.$ticket.'",
              fd_cliente = "'.$cliente.'",
              fd_fechatimbrado = "'.date('Y-m-d H:i:s').'",
              fd_timbro = "'.$_SESSION['uid'].'"';
      setq($sql3) or die(mysql_error() . $sql3);
  }

  include_once('modulos/xmlncredito.php');

  $xml = new Modelxml();
  if($xml->resultxml($_GET['id']) == true){
    $code = $this->model->timbrar($_GET['id'],$version);
  }

  redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id']);
}


function cancelar(){
  $this->model->cancelarncr($_GET['id']);

  redirect("?modulo=ncredito&accion=index");
}

function cancelarncr() {
  if($this->model->cancelarncr($_GET['id']) == 0)
      $location = '?modulo=ncredito&?modulo=ncredito&accion=showncredito&ncredito='.$_GET['id'];
  else{
    $estatus = busca($_GET['id'],'ncredito','n_id','n_estatus');
    if(!$estatus || $estatus == "C") $location = "?modulo=ncredito&accion=index";
    else $location = "?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id'].'&esta=Q';
  }

    redirect(''.$location);
}

function adenda(){
  echo '<div class="page-title-actions mb-1"><div class="d-inline-block dropdown">';
  echo '<a href="?modulo=ncredito&accion=showncredito&ncredito='.$_GET['id'].'" accesskey="">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atrás </button>
        </a>';
  echo '</div></div>';

  echo '<form action="?modulo=ncredito&accion=insertadenda&id='.$_GET['id'].'&tipo=A" method="post">
          <div class="row">
            <div class="col-12 col-md-12 bg-primary text-white">Relaciona CFDI</div>
          </div>
          <div class="col-12 col-md-12"><textarea class="form-control" name="addenda" cols="100" rows="5" /></div>
          <div class="col-12 col-md-12 bg-primary text-white">
            <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
          </div>
        </form>';
}

function adenda2(){
  echo '<div class="page-title-actions mb-1"><div class="d-inline-block dropdown">';
  echo '<a href="?modulo=ncredito&accion=showncredito&ncredito='.$_GET['id'].'" accesskey="">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atrás </button>
        </a>';
  echo '</div></div>';

    $this->model->selectncr($_GET['id']);

    $sql1 = 'SELECT n_id,n_folio,n_uuid,n_ftimbro FROM ncredito
             WHERE n_id != '.$this->model->id.' AND n_estatus IN ("T","C") AND n_uuid IS NOT NULL
             AND n_empresa="'.$this->model->empresa.'" AND n_cliente="'.$this->model->cliente.'"
             AND n_id NOT IN (SELECT nr_ncredito FROM ncredito_relacion WHERE nr_origen = "'.$this->model->id.'")
             ORDER BY n_id DESC';
    $resultf = setq($sql1) or die($sql1);

  echo '<script>
          function setother(){
            if(document.getElementById("ncredito").value == "OTRO"){
              document.getElementById("iduuid").value = "";
              document.getElementById("iduuid").readOnly = false;
              document.getElementById("iduuid").focus();
            }
            else{
              var idncr = document.getElementById("ncredito").value;
              var uuid = document.getElementById("uuid" + idncr).value;
              document.getElementById("iduuid").value = uuid;
              document.getElementById("iduuid").readOnly = true;
            }
          }
        </script>';
    echo '<form action="?modulo=ncredito&accion=insertadenda2&id='.$this->model->id.'" method="post" class="table-responsive">
          <table class="table-primary" width="100%">
          <thead class="thead-primary bg-primary text-white"><th colspan="3">Relaciona CFDI</th></thead>
          <tbody><tr><th>Tipo relacion</th>
          <th>Folio</th>
          <th></th></tr>';

    while($row = $resultf->fetch_array())
      echo '<input type="hidden" name="uuid" id="uuid'.$row['n_id'].'" value="'.$row['n_uuid'].'" >';

    echo'<tr><td>'.menu_select_db('cfdi_relaciones', 'cr_id', 'cr_nmb', '', 'cfdi_relaciones', 'cr_estatus="A"', false,  false, false, true).'</td>';
    echo'<td><select name="ncredito" id="ncredito" required class="form-control" onchange="setother();">';
    echo '<option value="">ELEGIR CFDI</option>';
    $resultf = setq($sql1) or die($sql1);
    while($row = $resultf->fetch_array()){
      echo'<option value="'.$row['n_id'].'">'.$row['n_folio'].' '.date('d-m-Y',strtotime($row['n_ftimbro'])).'</option>';
    }
    echo '<option value="OTRO">OTRO</option>';
    echo '</select></td>';
    echo '</tr><tr>';
    echo '<td><input type="text" name="uuid"  class="form-control" placeholder="UUID de la ncredito a relacionar" id="iduuid" readonly="readonly" required /></td>';
    echo'<td><center><input type="submit" id="guardar" value="Guardar" class="btn btn-success" /></center></td></tr>';

    $sql2 = 'SELECT * FROM ncredito_relacion WHERE nr_origen = '.$this->model->id.' ';
    $result2 = setq($sql2) or die($sql2);
    while($row2 = $result2->fetch_array()){
      $folioncr = busca($row2['nr_ncredito'],'ncredito','n_id','n_folio');
      echo '<tr><td class="bg-primary text-white"></td></tr>
      <tr>
        <th>'.busca($row2['nr_tiporelacion'],'cfdi_relaciones','cr_id','cr_nmb').'</th>
        <td>
          <center>
            <span class="h6">'.$row2['nr_uuid'].'</span>';
      if($folioncr)
        echo '<a target="_BLANK" href="?modulo=ncredito&accion=showncredito&ncredito='.$row2['nr_ncredito'].'">
                <button type="button" class="btn btn-primary">'.$folioncr.'</button></a>
              </a>';

      echo '</center>
        </td>
        <td><a href="?modulo=ncredito&accion=deladdenda2&ncredito='.$row2['nr_ncredito'].'&origen='.$row2['nr_origen'].'">
          <input type="button" class="btn btn-danger" value="Borrar" id="borrar" />
        </a></td>
      </tr>';
    }
    echo'</table>
        </form>';
}

function configimpuesto(){
  echo '<div class="page-title-actions mb-1"><div class="d-inline-block dropdown">';
  echo '<a href="?modulo=ncredito&accion=showncredito&ncredito='.$_GET['ncredito'].'" accesskey="">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atrás </button>
        </a>';
  echo '</div></div>';

  $tipoimp = array("T","R");
  $impuest = array("001","002","003");
  $nmbtipo = array("T"=>"Traslado","R"=>"Retención");
  $nmbimp = array("001"=>"ISR","002"=>"IVA","003"=>"IEPS");
  $valimp = array("001"=>"1","002"=>"2","003"=>"3");
  $valtipo = array("T"=>"1","R"=>"2");
  $subtotal = busca($_GET['ncredito'],'ncreditod','d_id = "'.$_GET['id'].'" AND d_ncredito','(d_cantidad*d_costo)');

  foreach($tipoimp as $tipoi){
    foreach($impuest as $mpuest){
      $base[$tipoi][$mpuest] = 0;
      $ncrtor[$tipoi][$mpuest] = "T";
      $tasa[$tipoi][$mpuest] = 0;
      $importe[$tipoi][$mpuest] = 0;
    }
  }

  $sql = 'SELECT * FROM ncreditod_imp WHERE ni_ncredito = "'.$_GET['ncredito'].'" AND ni_id = "'.$_GET['id'].'"';
  $result = setq($sql) or die($sql);
  while($row = $result->fetch_array()){
      $base[$row['ni_tr']][$row['ni_impuesto']] = $row['ni_base'];
      $ncrtor[$row['ni_tr']][$row['ni_impuesto']] = $row['ni_tipof'];
      $tasa[$row['ni_tr']][$row['ni_impuesto']] = $row['ni_tasac'];
      $importe[$row['ni_tr']][$row['ni_impuesto']] = $row['ni_importe'];
  }

  ?>
  <script>
    function habilitaimp(tipoi,imp){
      var tipoimp = "T";
      var base = document.getElementById("baseconc").value;
      if(tipoi == "2") tipoimp = "R";

      if(document.getElementById("hb-" + tipoimp + "-" + imp).checked){
        if(tipoi == "1"){
          document.getElementById("base-T-" + imp).disabled = false;
          document.getElementById("ncrtor-T-" + imp).disabled = false;
          document.getElementById("tasac-T-" + imp).disabled = false;

          document.getElementById("base-T-" + imp).value = base;
          document.getElementById("tasac-T-" + imp).focus();
          document.getElementById("tasac-T-" + imp).select();
        }
        else{
          document.getElementById("base-R-" + imp).disabled = false;
          document.getElementById("ncrtor-R-" + imp).disabled = false;
          document.getElementById("tasac-R-" + imp).disabled = false;

          document.getElementById("base-R-" + imp).value = base;
          document.getElementById("tasac-R-" + imp).focus();
          document.getElementById("tasac-R-" + imp).select();
        }
      }
      else{
        if(tipoi == "1"){
          document.getElementById("base-T-" + imp).disabled = true;
          document.getElementById("ncrtor-T-" + imp).disabled = true;
          document.getElementById("tasac-T-" + imp).disabled = true;

          document.getElementById("base-T-" + imp).value = 0;
          document.getElementById("tasac-T-" + imp).value = 0;
        }
        else{
          document.getElementById("base-R-" + imp).disabled = true;
          document.getElementById("ncrtor-R-" + imp).disabled = true;
          document.getElementById("tasac-R-" + imp).disabled = true;

          document.getElementById("base-R-" + imp).value = 0;
          document.getElementById("tasac-R-" + imp).value = 0;
        }
      }
    }
    function calcula(tipoi,imp){
      var tipoimp = "T";
      if(tipoi == "1"){
        var base = document.getElementById("base-T-" + imp).value;
        var tasac = document.getElementById("tasac-T-" + imp).value;
        var importe = base*tasac;
        var tasac = document.getElementById("importe-T-" + imp).value = importe;
      }
      else{
        var base = document.getElementById("base-R-" + imp).value;
        var tasac = document.getElementById("tasac-R-" + imp).value;
        var importe = base*tasac;
        var tasac = document.getElementById("importe-R-" + imp).value = importe;
      }
    }
  </script>
  <?php
  $baseimp = busca($_GET['ncredito'],'ncreditod','d_id = "'.$_GET['id'].'" AND d_ncredito','(d_cantidad*d_costo)');
  echo '<input type="hidden" id="baseconc" value="'.number_format($baseimp,6,'.','').'" >';
  echo '<div class="table-responsive"><form method="post" action="?modulo=ncredito&accion=setimpuestos&ncredito='.$_GET['ncredito'].'&id='.$_GET['id'].'">
        <table class="table table-striped">';
  echo '<thead class="bg-primary text-white">
          <tr>
            <td><button class="btn btn-info"><i class="fa fa-redo"></i>Actualizar</button></td>
            <th colspan="5">'.busca($_GET['ncredito'],'ncreditod','d_id = "'.$_GET['id'].'" AND d_ncredito','d_concepto').'</th>
          </tr>
          <tr>
            <th></th>
            <th>Impuesto</th>
            <th>Base</th>
            <th>ncrtor</th>
            <th>Tasa</th>
            <th>Importe</th>
          </tr>
        </thead>';

  $back = 0;
  foreach($impuest as $mpuest){
    foreach($tipoimp as $tipoi){
      $back++;
      if($tipoi == "T" && $mpuest == "001"){

      }
      else{
        $radioc = ""; $radiot = "TASA";

        if($ncrtor[$tipoi][$mpuest] == "C") $radioc = "checked";
        else $radiot = "checked";

        if($back%2 == 0) $bg = ' border-2'; else $bg = "";
        if($importe[$tipoi][$mpuest] > 0 || $tasa[$tipoi][$mpuest] > 0){
          $checkimp = "checked";
          $disab = ' ';
        }
        else{
          $disab = 'disabled ';
          $checkimp = "";
        }

        echo '<tr>
                <th><input type="checkbox" name="hb-'.$tipoi.'-'.$mpuest.'" id="hb-'.$tipoi.'-'.$valimp[$mpuest].'" '.$checkimp.' onchange="habilitaimp('.$valtipo[$tipoi].','.$valimp[$mpuest].')" /></th>
                <td>'.$nmbtipo[$tipoi].' '.$nmbimp[$mpuest].'</td>
                <td>
                  <input type="number" style="text-align:right" '.$disab.' name="base-'.$tipoi.'-'.$mpuest.'" id="base-'.$tipoi.'-'.$valimp[$mpuest].'"  class="form-control number-align" min="0" max="'.$subtotal.'" step="0.000001" value="'.number_format($base[$tipoi][$mpuest],6,'.','').'" onfocus="this.select();" onchange="calcula('.$valtipo[$tipoi].','.$valimp[$mpuest].')" />
                </td>
                <td>Tasa <input type="radio" name="ncrtor-'.$tipoi.'-'.$mpuest.'" '.$radiot.' '.$disab.' id="ncrtor-'.$tipoi.'-'.$valimp[$mpuest].'" /></td>
                <td>';
      if($tipoi == "T") $sqlt = ' ct_traslado = "SÍ" '; else  $sqlt = ' ct_retencion = "SÍ" ';

      if($mpuest == "001") $sqlim = 'ISR';
      elseif($mpuest == "002") $sqlim = 'IVA';
      else $sqlim = 'IEPS';

      if($tipoi == "R"){
        if($mpuest == "001")
          echo '<input type="number" class="form-control" '.$disab.' min="0" max="0.350000" step="0.000001" onchange="calcula('.$valtipo[$tipoi].','.$valimp[$mpuest].')" id="tasac-'.$tipoi.'-'.$valimp[$mpuest].'" name="tasac-'.$tipoi.'-'.$mpuest.'" value="'.$tasa[$tipoi][$mpuest].'" required >';
        elseif($mpuest == "002")
          echo '<input type="number" class="form-control" '.$disab.' min="0" max="0.160000" step="0.000001" onchange="calcula('.$valtipo[$tipoi].','.$valimp[$mpuest].')" id="tasac-'.$tipoi.'-'.$valimp[$mpuest].'" name="tasac-'.$tipoi.'-'.$mpuest.'" value="'.$tasa[$tipoi][$mpuest].'" required >';
        else{
          $sqlriep = 'SELECT ct_maximo FROM cfdi_tasaocuota WHERE ct_impuesto = "IEPS" AND ct_retencion = "SÍ"
                      AND ct_rangofijo = "Fijo" ORDER BY ct_maximo';
          $resultriep = setq($sqlriep);
          echo '<select  name="tasac-'.$tipoi.'-'.$mpuest.'" onchange="calcula('.$valtipo[$tipoi].','.$valimp[$mpuest].')" class="form-control" id="tasac-'.$tipoi.'-'.$valimp[$mpuest].'" required '.$disab.'>';
          echo '<option value="0">0.000000</option>';
          while($rowrie = $resultriep->fetch_array()){

            echo '<option value="'.$rowrie['ct_maximo'].'">'.$rowrie['ct_maximo'].'</option>';
          }
          echo '</select>';
        }
      }
      else{
        if($mpuest == "002"){
          if(busca("002",'ncreditod_imp','ni_ncredito = "'.$_GET['ncredito'].'" AND ni_tr = "T" AND ni_id = "'.$_GET['id'].'" AND ni_impuesto','ni_tasac') == '0.16000000') $seliva = "selected"; else $seliva = "";

          echo '<select name="tasac-'.$tipoi.'-'.$mpuest.'" onchange="calcula('.$valtipo[$tipoi].','.$valimp[$mpuest].')" id="tasac-'.$tipoi.'-'.$valimp[$mpuest].'" class="form-control" required '.$disab.'>';
          echo '<option value="0">0.000000</option>';
          echo '<option value="0.160000" '.$seliva.'>0.160000</option>';
          echo '</select>';
        }
        else{
          $sqlriep = 'SELECT ct_maximo FROM cfdi_tasaocuota WHERE ct_impuesto = "IEPS" AND ct_traslado = "SÍ"
                      AND ct_rangofijo = "Fijo" ORDER BY ct_maximo';
          $resultriep = setq($sqlriep);
          echo '<select name="tasac-'.$tipoi.'-'.$mpuest.'" onchange="calcula('.$valtipo[$tipoi].','.$valimp[$mpuest].')" id="tasac-'.$tipoi.'-'.$valimp[$mpuest].'" class="form-control" required '.$disab.'>';
          echo '<option value="0">0.000000</option>';
          while($rowrie = $resultriep->fetch_array()){
            echo '<option value="'.$rowrie['ct_maximo'].'">'.$rowrie['ct_maximo'].'</option>';
          }
          echo '</select>';
        }

      }
      echo '</td>
            <td>
              <input type="number" style="text-align:right" id="importe-'.$tipoi.'-'.$valimp[$mpuest].'" name="importe-'.$tipoi.'-'.$mpuest.'" class="form-control number-align" min="0" step="0.000001" value="'.number_format($importe[$tipoi][$mpuest],6,'.','').'" readonly="readonly" />
            </td>';
      }
    }
  }
      echo '</table></form></div>';
}

function setimpuestos(){
  $tipoimp = array("T","R");
  $impuest = array("001","002","003");
  $nmbtipo = array("T"=>"Traslado","R"=>"Retención");
  $nmbimp = array("001"=>"ISR","002"=>"IVA","003"=>"IEPS");
  $valimp = array("001"=>"1","002"=>"2","003"=>"3");
  $valtipo = array("T"=>"1","R"=>"2");


  $sqldi = 'DELETE FROM ncreditod_imp WHERE ni_ncredito = "'.$_GET['ncredito'].'" AND ni_id = "'.$_GET['id'].'"';
  $resultdi = setq($sqldi);
  foreach($impuest as $mpuest){
    foreach($tipoimp as $tipoi){
      if(isset($_POST['hb-'.$tipoi.'-'.$mpuest])){
        $this->model->insertimpuesto($_GET['ncredito'],$_GET['id'],$mpuest,$tipoi,$_POST['base-'.$tipoi.'-'.$mpuest],"Tasa",$_POST['tasac-'.$tipoi.'-'.$mpuest],$_POST['importe-'.$tipoi.'-'.$mpuest]);
      }
    }
  }

  $retiva = busca($_GET['ncredito'],'ncreditod_imp','ni_tr = "R" AND ni_impuesto = "002" AND ni_ncredito','SUM(ni_importe)');
  $iva = busca($_GET['ncredito'],'ncreditod_imp','ni_tr = "T" AND ni_impuesto = "002" AND ni_ncredito','SUM(ni_importe)');
  $isr = busca($_GET['ncredito'],'ncreditod_imp','ni_tr = "R" AND ni_impuesto = "001" AND ni_ncredito','SUM(ni_importe)');

  $sqlf = 'UPDATE ncredito SET
         n_retiva = "'.$retiva.'",
         n_iva = "'.$iva.'",
         n_isr = "'.$isr.'"
         WHERE n_id = "'.$_GET['ncredito'].'"';
  setq($sqlf) or die($sqlf);

  redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['ncredito']);
}

    function insertadenda(){
      $this->model->insertadenda($_GET['id'],$_POST['addenda'],$_GET['tipo']);
      redirect('?modulo=ncredito&accion=showncredito&ncredito=' . $_GET['id']);
    }

    function insertadenda2(){

      $sqls='SELECT * FROM ncredito_relacion
            WHERE nr_origen = "'.$_GET['id'].'" AND nr_tiporelacion = "'.$_POST['cfdi_relaciones'].'" AND nr_ncredito = "'.$_POST['ncredito'].'" ';
      $results=setq($sqls) or die($sqls);

      if(mysql_num_rows($results)==0){
        $sql = 'INSERT INTO ncredito_relacion SET
                nr_origen = "'.$_GET['id'].'",
                nr_tiporelacion = "'.$_POST['cfdi_relaciones'].'",
                nr_ncredito = "'.$_POST['ncredito'].'",
                nr_uuid = "'.$_POST['uuid'].'"';
        setq($sql) or die($sql);
      redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id']."");
      }
      else{
          die(alert_back("La relacion ya esta dada de alta",true));
          redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id']."");
      }
    }

    function deladdenda2(){
      $sql = 'DELETE FROM ncredito_relacion WHERE nr_origen = "'.$_GET['origen'].'" AND nr_ncredito = "'.$_GET['ncredito'].'"';
      $result = setq($sql) or die($sql);

      redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['origen']."");
    }

    function deleteadenda(){
      $this->model->deleteadenda($_GET['id'],$_GET['ncredito'],$_GET['tipo']);
      redirect('?modulo=ncredito&accion=showncredito&ncredito=' . $_GET['ncredito']);
    }


function updateprecio(){
    $sql = 'UPDATE ncreditod SET d_costo = "'.$_POST['costo'].'"
            WHERE d_ncredito = "'.$_GET['ncredito'].'" AND d_id = "'.$_GET['id'].'"';
    setq($sql) or die($sql);

    $ncredito = $_GET['ncredito'];
    $nsubtot = 0;
    $nieps = 0;
    $niva = 0;
    $nret = 0;
    $nisr = 0;

    $this->model->calculaimp($ncredito);

    redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['ncredito']);
}

function reenviar(){
      $cliente = busca($_GET['id'],'ncredito','n_id','n_cliente');

      echo '<script>
            function enable(){
                if(document.getElementById("enaex").checked){
                    document.getElementById("correoex").disabled = false;
                    document.getElementById("correoex").focus();
                    document.getElementById("enaex").setAttribute("name","exena");
                }
                else{
                    document.getElementById("correoex").disabled = true;
                    document.getElementById("enaex").setAttribute("name","correo");
                }
            }
            </script>';
                echo '<center><div class="container mx-auto">
                <form action="mailer.php" method="get" target="_BLANK" class="table-responsive">
                <table width="100%" class="table-primary"><thead class="bg-primary text-white">
                <tr><th colspan="2">Elija el correo</th></tr></thead>
                        <input type="hidden" name="id" value="'.$_GET['id'].'" />
                        <tr><td>'.busca($cliente,'crm_clientes','c_id','c_correo1').'</td>
                        <td><input type="radio" name="correo" value="'.busca($cliente,'clientes','c_id','c_correo').'" checked /></td></tr>';
                      $sql = 'SELECT DISTINCT(c_correo1) correo FROM crm_fiscales WHERE cn_cliente = "'.$cliente.'"';
                      $result = setq($sql) or die($sql);
                      while($row = $result->fetch_array()){
                        echo '<tr>
                                <td>'.$row['correo'].'</td>
                                <td><input type="radio" name="correo" value="'.$row['correo'].'" onchange="enable();" /></td>
                              </tr>';
                      }
                      echo '<td><input type="email" name="correo" placeholder="Correo Extra" id="correoex"  disabled /></td>
                      <td><input type="radio" name="correo" id="enaex" onchange="enable();" /></td>';
                      echo '<tr><td colspan="2"><input type="submit" class="btn btn-success" id="enviarcorreo" value="Enviar Correo" /></td></tr>';
                      echo '</form></div></center>';
}

function updatecant(){
    $sql = 'UPDATE ncreditod SET d_cantidad = "'.$_POST['cantidad'].'"
            WHERE d_ncredito = "'.$_GET['ncredito'].'" AND d_id = "'.$_GET['id'].'"';
    setq($sql) or die($sql);

    $ncredito = $_GET['ncredito'];
    $nsubtot = 0;
    $nieps = 0;
    $niva = 0;
    $nret = 0;
    $nisr = 0;

    $this->model->calculaimp($ncredito);

    redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['ncredito']);
}

function setuso(){
  $sql = 'UPDATE ncredito SET n_uso = "'.$_POST['uso'].'" WHERE n_id = "'.$_GET['id'].'"';
  setq($sql) or die($sql);

  redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id']);
}

function comentarios(){
  echo '<div class="page-title-actions mb-1"><div class="d-inline-block dropdown">';
  echo '<a href="?modulo=ncredito&accion=showncredito&ncredito='.$_GET['id'].'" accesskey="">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atrás </button>
        </a>';
  echo '</div></div>';

  echo '<form class="table-responsive" action="?modulo=ncredito&accion=insertcomentario&id='.$_GET['id'].'&tipo=C" method="post">
  <table class="table-primary">
  <thead<tr><th>Insertar Comentario</th><td><textarea name="comentario" class="form-control" cols="100" rows="5">
  '.busca($_GET['id'],'adendas','atipo = "N" AND a_factura','a_adenda').'</textarea></td>
  <td><input type="submit" id="guardar" value="Guardar" class="btn btn-success" /></td></tr></thead>
  </table>
  </form>';
}

function insertcomentario(){
  $this->model->insertcomentario($_GET['id'],$_POST['comentario'],$_GET['tipo']);
  redirect('?modulo=ncredito&accion=showncredito&ncredito='.$_GET['id']);
}

function changecl(){
  $sql = 'UPDATE ncredito SET n_cliente = "'.$_POST['clien'].'" WHERE n_id = "'.$_GET['ncredito'].'"';
  setq($sql) or die($sql);

  redirect('?modulo=ncredito&accion=showncredito&ncredito='.$_GET['ncredito']);
}

function clonar(){
  $this->model->selectncr($_REQUEST['origen']);
  $this->model->actualizapago($this->model->formapago, $this->model->tipocambio, $this->model->metodopago, $this->model->numcta, $_REQUEST['destino'], $this->model->valorm, $this->model->condicion);

  $this->model->resultd($_REQUEST['origen']);
  while($row = $this->model->resulttd->fetch_array()){
    $this->model->insertcon($_REQUEST['destino'],$row['d_cantidad'],$row['d_costo'],$row['d_claveuni'],$row['d_concepto'],$row['d_producto'],NULL,$row['d_retenciones'],$row['d_traslados']);
  }

  redirect('?modulo=ncredito&accion=showncredito&ncredito='.$_REQUEST['destino']);
}
/************************Complementos**********************************/

//Carta porte Joss 11-2021
function traslados(){
    if(!isset($_POST['tipodoc'])) $_POST['tipodoc'] = "T";
    if(!isset($_POST['cliente'])) $_POST['cliente'] = NULL;
    if(!isset($_POST['estatus'])) $_POST['estatus'] = NULL;
    if(!isset($_POST['folio'])) $_POST['folio'] = NULL;
    if(!isset($_GET['page'])) $_GET['page'] = 0;
    if(!isset($_POST['empresa'])) $_POST['empresa'] = NULL;

    $this->model->resultncr($_POST['cliente'],$_GET['page'],$_POST['estatus'],$_POST['folio'],$_POST['empresa'],$_POST['tipodoc']);
    $this->view = new  Viewncredito($this->model);
    $this->view->browsencr($_GET['page'],$_POST['cliente'],$_POST['estatus'],$_POST['folio'],$_POST['empresa'],$_POST['tipodoc']);
}

function cartap(){
  $this->model->selectncr($_GET['id']);
  $this->model->resultcp($_GET['id']);
  $this->view = new Viewncredito($this->model);
  $this->view->cartap();
}

function insertcartaporte(){

  if(isset($_POST['TranspInternac'])) $TranspInternac = "Si"; else $TranspInternac = "No";
  $this->model->setdatacartap($_GET['ncredito'],$TranspInternac,$_POST['EntradaSalidaMerc'],$_POST['PaisOrigenDestino'],$_POST['ViaEntradaSalida']);
  $this->model->insertcartaporte();

  $this->model->empresa = busca($_GET['ncredito'],'ncredito','n_id','n_empresa');
  $sqld = 'SELECT e_nmb,e_rfc,g_domicilio,g_nume,g_numi,g_cp,g_colonia,g_municipio,g_estado,g_pais
           FROM empresas INNER JOIN girosempresa ON e_id = g_empresa
           WHERE g_empresa = "'.$this->model->empresa.'" ';
  $resultd = setq($sqld) or die($sqld);
  list($nmbempresa,$rfcempresa,$calleu,$numeu,$numiu,$cpu,$coloniau,$ciudadu,$estadou,$paisu) = $resultd->fetch_array();

  $idubica = $this->model->insertcpubicacion($_GET['ncredito'],"Origen",NULL,$this->model->empresa,$rfcempresa,$nmbempresa,$_POST['FechaHoraSalidaLlegada'],0);
  $this->model->insertcpdom($_GET['ncredito'],$idubica,$calleu,$numeu,$numiu,$coloniau,$ciudadu,$estadou,$paisu,$cpu);

  redirect("?modulo=ncredito&accion=cartap&id=".$this->model->ncredito);
}

function insertubica(){
  if(!isset($this->model->empresa)) $this->model->empresa = busca($_GET['ncredito'],'ncredito','n_id','n_empresa');
  $idubica = $this->model->insertcpubicacion($_GET['ncredito'],$_POST['TipoUbicacion'],$_POST['IDUbicacion'],$this->model->empresa,$_POST['RFCRemitenteDestinatario'],$_POST['NombreRFC'],$_POST['FechaHoraSalidaLlegada'],$_POST['DistanciaRecorrida']);

  if(isset($_POST['domicilio']))
    $this->model->insertcpdom($_GET['ncredito'],$idubica,$_POST['calle'],$_POST['NumeroExterior'],$_POST['NumeroInterior'],$_POST['Colonia'],$_POST['Localidad'],$_POST['Municipio'],$_POST['Estado'],$_POST['Pais'],$_POST['CodigoPostal']);

  redirect("?modulo=ncredito&accion=cartap&id=".$_GET['ncredito']);
}

function insertcpdom(){
  $this->model->insertcpdom($_GET['ncredito'],$_GET['idubica'],$_POST['calle'],$_POST['NumeroExterior'],$_POST['NumeroInterior'],$_POST['Colonia'],$_POST['Localidad'],$_POST['Municipio'],$_POST['Estado'],$_POST['Pais'],$_POST['CodigoPostal']);

  redirect("?modulo=ncredito&accion=cartap&id=".$_GET['ncredito']);
}

function setubicacion(){
  $sql = 'SELECT cpu_Calle,cpu_NumeroExterior,cpu_NumeroInterior,cpu_Colonia,cpu_Localidad,cpu_Municipio,cpu_Estado,cpu_Pais,cpu_CodigoPostal
          FROM carta_porte_ubicaciones WHERE cpu_ncredito = "'.$_GET['ncredito'].'" AND cpu_id = "'.$_GET['idu'].'"';
  $result = setq($sql);
  list($Calle,$NumeroExterior,$NumeroInterior,$Colonia,$Localidad,$Municipio,$Estado,$Pais,$CodigoPostal) = $result->fetch_array();

  ?>
    <script>
    function setcp(){
      var cp = $("#cptcp").val();

      $.ajax({
        url: 'modulos/buscarcolonia.php',
        type: 'POST',
        dataType: 'html',
        data: {'cp': cp},
      })
      .done(function(respuesta){
        if(respuesta != ""){
          var direccion = JSON.parse(respuesta);
          var local = direccion[0].localidad;
          var muni = direccion[0].municipio;
          var estad = direccion[0].estado;

          $('#cptlocalidad').val(local);
          $('#cptmunicipio').val(muni);
          $('#cptestado').val(estad);
          $('#cptpais').val("MEX");
        }
      });

      $.ajax({
        url: 'coloniascodigo.php',
        type: 'POST',
        dataType: 'html',
        data: {'cp': cp},
      })
      .done(function(datacol){
          $("#cptcolonia").html(datacol);
      });

      $('#cptcolonia').focus();
    }

    </script>
  <?php

  $htmlcol = "";
  if($CodigoPostal){
    $sql = 'SELECT cpc_colonia,cpc_nmb FROM carta_porte_colonias WHERE cpc_cp = "'.$CodigoPostal.'"';
    $result = setq($sql) or die($sql);
    while($row = $result->fetch_array()){
      if($Colonia == $row['cpc_colonia']) $selcol = "selected"; else $selcol = "";
      $htmlcol.='<option value="'.$row['cpc_colonia'].'" '.$selcol.'>'.$row['cpc_colonia'].' - '.$row['cpc_nmb'].'</option>';
    }
  }

  echo '<form autocomplete="off" name="domicilio" action="?modulo=ncredito&accion=insertcpdom&ncredito='.$_GET['ncredito'].'&idubica='.$_GET['idu'].'" method="post">
        <table class="table table-bordered table-hover table-striped" width="100%">
        <thead class="thead-primary bg-primary text-white">
        <tr><th colspan="5">Dirección de la ubicación</th></tr></thead>';
  echo '<tr><th>Calle</th><th>Exterior</th><th>Interior</th></tr>
        <tr>
          <td><input type="text" name="calle" value="'.$Calle.'" class="form-control"  /></td>
          <td><input type="text" name="NumeroExterior" value="'.$NumeroExterior.'" class="form-control"  /></td>
          <td><input type="text" name="NumeroInterior" value="'.$NumeroInterior.'" class="form-control"  /></td>

      </tr>
        <tr><th>Codigo postal*</th><th>Colonia</th><th>Localidad</th></tr>
      <tr>
        <td><input type="text" name="CodigoPostal" maxlenght="12" id="cptcp" value="'.$CodigoPostal.'" required="required" class="form-control" onchange="setcp();" autofocus /></td>
        <td>
          <select name="Colonia" id="cptcolonia" placeholder="Seleccionar colonia" class="form-control">'.$htmlcol.'</select>
        </td>
        <td><input type="text" name="Localidad" value="'.$Localidad.'" id="cptlocalidad" class="form-control"  /></td>
      </tr>
      <tr><th>Municipio</th><th>Estado*</th><th>Pais*</th></tr>
      <tr>
          <td><input type="text" name="Municipio" value="'.$Municipio.'" id="cptmunicipio" class="form-control" required="required"  /></td>
          <th><input type="text" name="Estado" value="'.$Estado.'" id="cptestado" required="required" class="form-control" required="required"  /></th>
          <th><input type="text" name="Pais" maxlenght="3" value="'.$Pais.'" id="cptpais" required="required" class="form-control" required="required"  /></th>
        </tr>
        <tr>
          <td colspan="4"><button type="submit" class="btn btn-primary" />Actualizar</button></td>
        </tr>';
  echo '</table></form>';
}

function ubicaciondir(){
  if(busca($_GET['ncredito'],'carta_porte_ubicaciones','cpu_ncredito','COUNT(*)') == 1){

    $sqlcld = 'SELECT c_rfc,c_nmb,c_calle,c_nume,c_numi,c_colonia,c_municipio,c_estado,c_pais,c_cp
            FROM ncredito INNER JOIN clientes ON n_cliente = c_id
            WHERE n_id = "'.$_GET['ncredito'].'"';
    $resultcld = setq($sqlcld);
    list($rfc,$nmb,$calle,$nume,$numi,$colonia,$municipio,$estado,$pais,$cp) = $resultcld->fetch_array();
  }
  else{
    $rfc = NULL;
    $nmb = NULL;
    $calle = NULL;
    $nume = NULL;
    $numi = NULL;
    $colonia = NULL;
    $municipio = NULL;
    $estado = NULL;
    $pais = NULL;
    $cp = NULL;
  }

  $this->tipoubica = array("Destino"=>"Destino");
  $fechaor = busca($_GET['ncredito'],'carta_porte_ubicaciones','cpu_TipoUbicacion = "Origen" AND cpu_ncredito','cpu_FechaHoraSalidaLlegada');
  echo '<form action="?modulo=ncredito&accion=insertubica&ncredito='.$_GET['ncredito'].'" method="post">
        <table class="table table-bordered table-hover table-striped" width="100%">
        <thead class="thead-primary bg-primary text-white">
          <tr>
            <th>Tipo</th>
            <th>ID</th>
            <th>RFC Remitente</th>
            <th>Nombre Remitente</th>
          </tr>
        </thead>';

  echo '<tr>
          <th>'.menu_select_array($this->tipoubica,"Destino",'TipoUbicacion',true).'</th>
          <td><input type="number" name="IDUbicacion" step="1" max="3" min="1" value="" class="form-control" /></td>
          <td><input type="text" name="RFCRemitenteDestinatario" autofocus="autofocus" value="'.$rfc.'" required="required" class="form-control text-uppercase" /></td>
          <td><input type="text" name="NombreRFC" value="'.$nmb.'" class="form-control" /></td>
        </tr><tr>
          <th colspan="2">Fecha y hora Llegada</th>
          <th>Distancia</th>
        </tr><tr>
          <th colspan="2">
            <input type="datetime-local" name="FechaHoraSalidaLlegada" min="'.str_replace(' ',"T",$fechaor).'" value="'.str_replace(" ","T",date('Y-m-d H:00',strtotime($fechaor.'+ 1 hour'))).'" step="any"  required="required" class="form-control" />
          </th>
          <th>
            <div class="input-group">
              <input type="number" name="DistanciaRecorrida" step="0.01" min="0" max="99999" value="" required="required" class="form-control" />
                <div class="input-group-append">
                  <span class="input-group-text" id="basic-addon2">KM</span>
                </div>
            </div>
          </th>
        </tr>';
  echo '<tr><th colspan="5">Incluir dirección <input type="checkbox" name="domicilio" id="indireccion" onchange="includedir();" /></th></tr>';
  echo '</table>';
  ?>
    <script>
      function includedir(){
        if(document.getElementById("indireccion").checked == true){
          document.getElementById("direccion").style.display = "";
          document.getElementById("cptpais").value = "MEX";
          document.getElementById("cptcp").focus();
        }
        else{
          document.getElementById("direccion").style.display = "none";
        }
      }
</script>
  <?php
  ?>
    <script>
    function setcp(){
      var cp = $("#cptcp").val();

      $.ajax({
        url: 'modulos/buscarcolonia.php',
        type: 'POST',
        dataType: 'html',
        data: {'cp': cp},
      })
      .done(function(respuesta){
        if(respuesta != ""){
          var direccion = JSON.parse(respuesta);
          var local = direccion[0].localidad;
          var muni = direccion[0].municipio;
          var estad = direccion[0].estado;

          $('#cptlocalidad').val(local);
          $('#cptmunicipio').val(muni);
          $('#cptestado').val(estad);
        }
      });

      $.ajax({
        url: 'coloniascodigo.php',
        type: 'POST',
        dataType: 'html',
        data: {'cp': cp},
      })
      .done(function(datacol){
          $("#cptcolonia").html(datacol);
      });

      $('#cptcolonia').focus();
    }

    </script>
  <?php

  $htmlcol = "";
  if($CodigoPostal){
    $sql = 'SELECT cpc_colonia,cpc_nmb FROM carta_porte_colonias WHERE cpc_cp = "'.$CodigoPostal.'"';
    $result = setq($sql) or die($sql);
    while($row = $result->fetch_array()){
      if($Colonia == $row['cpc_colonia']) $selcol = "selected"; else $selcol = "";
      $htmlcol.='<option value="'.$row['cpc_colonia'].'" '.$selcol.'>'.$row['cpc_colonia'].' - '.$row['cpc_nmb'].'</option>';
    }
  }
  echo '<table class="table table-bordered table-hover table-striped" style="display:none" id="direccion" width="100%">
        <thead class="thead-primary bg-primary text-white">
        <tr><th colspan="5">Dirección de la ubicación</th></tr></thead>';
  echo '<tr><th>Calle</th><th>Exterior</th><th>Interior</th></tr>
        <tr>
          <td><input type="text" name="calle" value="'.$Calle.'" class="form-control"  /></td>
          <td><input type="text" name="NumeroExterior" value="'.$NumeroExterior.'" class="form-control"  /></td>
          <td><input type="text" name="NumeroInterior" value="'.$NumeroInterior.'" class="form-control"  /></td>

      </tr>
        <tr><th>Codigo postal*</th><th>Colonia</th><th>Localidad</th></tr>
      <tr>
        <td><input type="text" name="CodigoPostal" maxlenght="12" id="cptcp" value="'.$CodigoPostal.'" required="required" class="form-control" onchange="setcp();" autofocus /></td>
        <td>
          <select name="Colonia" id="cptcolonia" placeholder="Seleccionar colonia" class="form-control">'.$htmlcol.'</select>
        </td>
        <td><input type="text" name="Localidad" value="'.$Localidad.'" id="cptlocalidad" class="form-control"  /></td>
      </tr>
      <tr><th>Municipio</th><th>Estado*</th><th>Pais*</th></tr>
      <tr>
          <td><input type="text" name="Municipio" value="'.$Municipio.'" id="cptmunicipio" class="form-control" required="required"  /></td>
          <th><input type="text" name="Estado" value="'.$Estado.'" id="cptestado" required="required" class="form-control" required="required"  /></th>
          <th><input type="text" name="Pais" maxlenght="3" value="'.$Pais.'" id="cptpais" required="required" class="form-control" required="required"  /></th>
        </tr>
        <tr>
          <td colspan="4"><button type="submit" class="btn btn-primary" />Actualizar</button></td>
        </tr>';
  echo '
          <center><button role="submit" class="btn btn-success mt-2">Guardar ubicación</button></center>
        </table></form>';
}

function delubicacion(){
  $this->model->delubicacion($_GET['ncredito'],$_GET['idu']);

  redirect("?modulo=ncredito&accion=cartap&id=".$_GET['ncredito']);
}

function updateubica(){
  if(!isset($this->model->empresa)) $this->model->empresa = busca($_GET['ncredito'],'ncredito','n_id','n_empresa');

  $this->model->updatecpubicacion($_GET['ncredito'],$_GET['idu'],$_POST['TipoUbicacion'],$_POST['IDUbicacion'],$this->model->empresa,mb_strtoupper($_POST['RFCRemitenteDestinatario']),$_POST['NombreRFC'],$_POST['FechaHoraSalidaLlegada'],$_POST['DistanciaRecorrida']);

  redirect("?modulo=ncredito&accion=cartap&id=".$_GET['ncredito']);
}

function newmarcancia(){
  echo '<script>
          function confirmaimport(){
            var conf = confirm("¿Deseas importar las mercancias desde la ncredito?");
            if(conf == true){
              window.location.href="?modulo=ncredito&accion=importmerca&ncredito='.$_GET['ncredito'].'";
            }
          }
        </script>';
  echo '<form method="post" action="?modulo=ncredito&accion=insertmerca&ncredito='.$_GET['ncredito'].'">';

  echo '<table class="table table-bordered table-hover table-striped" >
        <thead class="thead-primary bg-primary text-white">';
  if(busca($_GET['ncredito'],'carta_porte_mercancias','cpm_ncredito','COUNT(*)') == 0){
    echo '<tr><td colspan="6">
            <button role="button" class="btn-sm btn-secondary" onclick="confirmaimport();"><i class="fas fa-file-import"></i> Importar desde la ncredito</button>
          </td></tr>';
  }

  echo '<tr>
            <th>Bienes Transportados*</th>
            <th>Descripcion*</th>
            <th>Cantidad*</th>
            <th>Unidad</th>
            <th>Peso KG*</th>
            <th>Valor Mercancia*</th>
          </tr>
        </thead>
        <tr>
          <th>'.menu_select_db('prodsat','p_claveps','p_alias',"",'BienesTransp','p_empresa = "'.$_SESSION['emp'].'"',false,false,false,true).'</th>
          <th><input type="text" name="Descripcion" size="40" value="" onfocus="this.select();" required="required" /></td>
          <th><input type="number" name="Cantidad" min="0.01" max="99999" step="0.01" value="" onfocus="this.select();" required="required" /></td>
          <th>'.menu_select_db('unidades','u_unidad','u_nmb',"",'ClaveUnidad','u_empresa = "'.$_SESSION['emp'].'"',false,false,"N",false).'</td></td>
          <th><input type="number" name="PesoEnKg" min="0.01" max="99999" step="0.001" value="" onfocus="this.select();" required="required" /></td>
          <th><input type="number" name="ValorMercancia" min="0.00" max="99999" step="0.01" value="" onfocus="this.select();" /></td>
        </tr><tr><td colspan="6"><button class="btn-sm btn-primary">Guardar</button></td>';
  echo '</tr></table></form>';
}

function importardoc(){
  echo '<form method="post" enctype="multipart/form-data" action="?modulo=ncredito&accion=serimportado&ncredito='.$_GET['ncredito'].'">
          <div class="row">
            <div class="col-12 col-md-12 bg-primary text-white">Importar prodsat desde formato</div>
            <div class="col-6 col-md-6">Elegir archivo</div>
            <div class="col-6 col-md-6"><input type="file" name="archivo" required accept="text/xml, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"  /></div>
            <div class="col-12 col-md-12 mt-1"><center>
              <button class="btn btn-success"><i class="fas fa-file-import"></i> Importar</button>
            </center></div>
          </div>
        </form>';
}

function serimportado(){
  $path = $_FILES['archivo']['name'];
  $extension = pathinfo($path, PATHINFO_EXTENSION);
  if($extension == "xls" || $extension == "xlsx" || $extension == "csv"){
    die("--Leer excel");
  }
  else{
    $directorio = 'adjuntos/';
    $subir_archivo = $directorio.'documentocp'.$_GET['ncredito'].'.'.$extension;
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $subir_archivo)) {
      include_once('leerxmlcp.php');

      $cfdi = new LeerCFDI();
      $cfdi -> cargaXml($subir_archivo);
      $cfdi -> recorrer($_GET['ncredito']);

    }
    else echo "Error de subida ".$_FILES['archivo']['name'].'<-<br>';
  }

  redirect("?modulo=ncredito&accion=cartap&id=".$_GET['ncredito']);
}

function setfiscales(){
  $this->model->setfiscales($_POST['fiscales'],$_GET['id']);

  redirect("?modulo=ncredito&accion=showncredito&ncredito=".$_GET['id']);
}

function cancelarncredito(){
    ?>
      <script>
        function validasustituto(){
          var motivo = document.getElementById("motivocan").value;
          if(motivo == "01"){
            document.getElementById("sustituto").style.display = "";
            document.getElementById("sustituto").required = true;
         }
          else{
            document.getElementById("sustituto").style.display = "none";
            document.getElementById("sustituto").required = false;
          }
        }
      </script>
    <?php
    echo '<form action="?modulo=ncredito&accion=cancelarncr&id='.$_GET['id'].'" method="post">
          <table class="table table-bordered table-hover table-striped" style="border-collapse:collapse;">
        <thead class="bg-primary text-white">
            <tr><th colspan="5">Motivo de cancelación</th></tr>
            <thead>
            <tr style="margin-top:10px"><td>
              <select name="motivocan" class="form-control" id="motivocan" onchange="validasustituto();"  style="font-size:15px;">';
    $sql = 'SELECT * FROM cfdi_motivocancelacion WHERE cm_estatus = "A"';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      echo '<option value="'.$row['cm_motivo'].'">'.$row['cm_motivo'].' - '.$row['cm_descripcion'].'</option>';
    }

    echo '</select> </td></tr>';
    echo '<tr><td><input type="text" class="form-control" name="sustituto" id="sustituto" placeholder="UUID sustituto" size="40" style="font-size:15px;" required="required" /></td></tr>';
    echo '<tr><td><button type="submit" class="btn btn-danger" id="cancelar" /><i class="fa fa-times"></i>Cancelar</button></td></tr>';

    echo '</table></form>';
  }

}

class Modelncredito{

function selectncr($id){
    $sql = 'SELECT * FROM ncredito WHERE n_id = "'.$id.'"';
    $result = setq($sql) or die($sql);
    $row = $result->fetch_array();
    $this->id = $row['n_id'];
    $this->empresa = $row['n_empresa'];
    $this->fiscales = $row['n_fiscales'];
    $this->cliente = $row['n_cliente'];
    $this->creo = $row['n_creo'];
    $this->estatus = $row['n_estatus'];
    $this->fcancelo = $row['n_fcancelo'];
    $this->fcreo = $row['n_fcreo'];
    $this->folio = $row['n_folio'];
    $this->fpago = $row['n_fpago'];
    $this->ftimbro = $row['n_ftimbro'];
    $this->metodopago = $row['n_metodopago'];
    $this->timbro = $row['n_timbro'];
    $this->condiciones = $row['n_condiciones'];
    $this->tipocambio = $row['n_tipocambio'];
    $this->valorm = $row['n_valorm'];
    $this->subtotal = $row['n_subtotal'];
    $this->iva = $row['n_iva'];
    $this->isr = $row['n_isr'];
    $this->retiva = $row['n_retiva'];
    $this->serie = $row['n_serie'];
    $this->nfolio = $row['n_nfolio'];
    $this->version = $row['n_version'];
    $this->uso = $row['n_uso'];
    $this->uuid = $row['n_uuid'];
}

function selectd($ncredito,$id){
  $sql = 'SELECT * FROM ncreditod WHERE d_ncredito = "'.$ncredito.'" AND d_id = "'.$id.'"';
  $result = setq($sql) or die($sql);
  $row = $result->fetch_array();
  $this->idd = $row['d_id'];
  $this->unidad = $row['d_unidad'];
  $this->claveuni = $row['d_claveuni'];
  $this->concepto = $row['d_concepto'];
  $this->producto = $row['d_producto'];
  $this->cantidad = $row['d_cantidad'];
  $this->descuento = $row['d_descuento'];
  $this->costo = $row['d_costo'];
  $this->retenciones = $row['d_retenciones'];
  $this->traslados = $row['d_traslados'];
  $this->noid = $row['d_noid'];
  $this->predial = $row['d_predial'];
}

function resultncr($cliente,$page,$estatus,$folio,$empresa){ //Filtro
    $bloque = 10;
    $sql = 'SELECT * FROM ncredito WHERE n_empresa = "'.$empresa.'"';

      if($cliente) $sql.=' AND n_cliente IN
                          (SELECT c_id FROM crm_clientes WHERE c_empresa = "'.$_SESSION['emp'].'" AND c_nmb LIKE "%'.$cliente.'%")';
      if($folio){
        $sql.=' AND n_folio LIKE "%'.$folio.'%"';
      }

      if($estatus) $sql.=' AND n_estatus = "'.$estatus.'"';

    $sql.=' ORDER BY FIELD(n_estatus,"N","P","T","Q","C"), n_folio DESC,n_fcreo DESC
            LIMIT '.($page*$bloque).','.$bloque.'';

    $this->result = setq($sql) or die($sql);
    $this->resultt = setq($sql);
}

function resultcan($cliente,$folio,$empresa){
    $sqlf = 'SELECT * FROM ncredito WHERE n_empresa = "'.$empresa.'" AND n_estatus = "C"';
    $sqlc = 'SELECT * FROM ncredito WHERE n_empresa = "'.$empresa.'" AND n_estatus = "Q"';

    $sql = "";
    if($cliente) $sql.=' AND n_cliente IN
                        (SELECT c_id FROM clientes WHERE c_empresa = "'.$_SESSION['emp'].'" AND c_nmb LIKE "%'.$cliente.'%")';
    if($folio)
      $sql.=' AND n_folio = "'.$folio.'"';

    $sql.=' ORDER BY FIELD(n_estatus,"Q","C"), n_ftimbro DESC,n_fcreo DESC LIMIT 0,50';

    $sqlf = $sqlf.$sql;
    $sqlc = $sqlc.$sql;
    $this->resultc = setq($sqlf) or die($sqlf);
    $this->resultq = setq($sqlc) or die($sqlc);
}

function insertncreditoblanco($cliente,$fiscales,$empresa,$version){
  $sql = 'INSERT INTO ncredito SET
          n_cliente = "'.$cliente.'",
          n_empresa = "'.$empresa.'",
          n_fiscales = "'.$fiscales.'",
          n_creo = "'.$_SESSION['uid'].'",
          n_fcreo = "'.date('Y-m-d').'",
          n_version = "'.$version.'",
          n_estatus = "N"';
  setq($sql) or die($sql);

  $mid = getmax('n_id','ncredito','n_empresa',false);
  return($mid);
}

function insertprogramacion($cliente,$tipo,$empresa){
  $sqlm = 'SELECT MAX(p_id) FROM programacion';
  $resultm = setq($sqlm) or die($sqlm);
  list($mid) = mysql_fetch_row($resultm);
  $mid++;

  if(busca($empresa,'girosempresa','g_empresa','COUNT(*)') == 1)
    $giro = busca($empresa,'girosempresa','g_empresa','g_id');

  $uso = busca($cliente,'clientes','c_id','c_usocfdi');

  $sql = 'INSERT INTO programacion SET
          p_id = "'.$mid.'",
          p_creo = "'.$_SESSION['uid'].'",
          p_giro = "'.$giro.'",
          p_cliente = "'.$cliente.'",
          p_empresa = "'.$empresa.'",
          p_tipo = "'.$tipo.'",
          p_uso = "'.$uso.'",
          p_fcreo = "'.date('Y-m-d').'",
          p_estatus = "N"';
  setq($sql) or die($sql);

  return($mid);
}

function insertncrrem($ncredito,$remision,$iva=1){
  $sqltd = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$remision.'"';
  $resultc = setq($sqltd) or die($sqltd);
  $porcd = busca($remision,'remisiones','r_id','r_descuento');

  while($row = $resultc->fetch_array()){

    $unidad = busca($row['rd_articulo'],'articulos','a_id','a_unidad');
    $clavesat = busca($row['rd_articulo'],'articulos','a_id','a_clavesat');
    $cveunidad = busca($unidad,'unidades','u_id','u_clavesat');
    $ivain = 1;
    $ivama = 1;
    $idf = $this->insertcon($ncredito,$row['rd_cantidad'],$row['rd_precio'],$cveunidad,$row['rd_nmbarticulo'],$clavesat,$retenma,$ivama,$ivain,$row['rd_articulo']);

    $sqlfr = 'INSERT INTO ncredito_remision SET
              nr_ncredito= "'.$ncredito.'",
              nr_remision = "'.$remision.'",
              nr_idr = "'.$row['rd_id'].'",
              nr_idf = "'.$idf.'",
              nr_cantidad = "'.$row['rd_cantidad'].'",
              nr_precio = "'.$row['rd_precio'].'"';
    //setq($sqlfr) or die($sqlfr);
  }

  $this->calculaimp($ncredito);

  $sqlu = 'UPDATE remisiones SET r_ncreditoda = "1" WHERE r_id = "'.$remision.'"';
  //setq($sqlu) or die($sqlu);

  return($ncredito);
}

function resultd($ncredito){
    $sql = 'SELECT * FROM ncreditod WHERE d_ncredito = "'.$ncredito.'" ORDER BY d_id ASC';
    $this->resulttd = setq($sql) or die($sql);
}

function quitaret($id,$ncredito,$iva,$retencion){

    $sql = 'UPDATE ncreditod SET d_retenciones = '.$retencion.',d_traslados = '.$iva.' WHERE d_ncredito = "'.$ncredito.'" AND d_id = "'.$id.'"';
    setq($sql) or die($sql);

    $ncredito = $_GET['ncredito'];
    $nsubtot = 0;
    $nieps = 0;
    $niva = 0;
    $nret = 0;
    $nisr = 0;

    $this->calculaimp($ncredito);
}

function insertcon($ncredito,$cantidad,$costo,$unidad,$concepto,$producto,$retenma,$ivama,$ivain,$noid=NULL){

$id = busca($ncredito,'ncreditod','d_ncredito','MAX(d_id)');
$id++;

$concepto = str_replace('"','',$concepto);
$empresa = busca($ncredito,'ncredito','n_id','n_empresa');

if($ivain == "1") $costo = ($costo/116)*100;
$prodd = NULL;
$prodd = busca($producto,'prodsat','p_id','p_claveps');
if(!$prodd && empty($prodd)) $prodd = $producto;

    $sql = 'INSERT INTO ncreditod SET
            d_id = "'.$id.'",
            d_ncredito = "'.$ncredito.'",
            d_costo = "'.$costo.'",
            d_cantidad = "'.$cantidad.'",
            d_concepto = "'.strtoupper(trim($concepto)).'",
            d_retenciones = "'.$retenma.'",
            d_traslados = "'.$ivama.'",
            d_claveuni = "'.$unidad.'",
            d_producto = "'.$prodd.'",
            d_noid = "'.$noid.'",
            d_unidad = "'.busca($unidad,'unidades','u_empresa = "'.$empresa.'" AND u_clavesat','u_nmb').'"';
    setq($sql) or die($sql);

 //   $ncredito = $_GET['ncredito'];
    $nsubtot = 0;
    $nieps = 0;
    $niva = 0;
    $nret = 0;
    $nisr = 0;

    $this->calculaimp($ncredito);


    return($id);
}

function updatecon($idd,$ncredito,$cantidad,$costo,$unidad,$concepto,$producto,$retenma,$ivama,$ivain){
if($ivain == "1") $costo = ($costo/116)*100;

    $sql = 'UPDATE ncreditod SET
            d_costo = "'.$costo.'",
            d_cantidad = "'.$cantidad.'",
            d_concepto = "'.strtoupper($concepto).'",
            d_retenciones = "'.$retenma.'",
            d_traslados = "'.$ivama.'",
            d_claveuni = "'.$unidad.'",
            d_producto = "'.$producto.'",
            d_unidad = "'.busca($unidad,'unidades','u_empresa = "'.$_SESSION['emp'].'" AND u_clavesat','u_nmb').'"
            WHERE d_id = "'.$idd.'" AND d_ncredito = "'.$ncredito.'"';
    setq($sql) or die($sql);

//    $ncredito = $_GET['ncredito'];
    $nsubtot = 0;
    $nieps = 0;
    $niva = 0;
    $nret = 0;
    $nisr = 0;

    $this->calculaimp($ncredito);

}

function borrarart($ncredito, $id) {
    $sql2 = 'DELETE FROM ncreditod WHERE d_ncredito="' . $ncredito . '" AND d_id="' . $id . '"';
    setq($sql2) or die(mysql_error());

    $sql2 = 'DELETE FROM ncreditod_imp WHERE ni_ncredito="' . $ncredito . '" AND ni_id="' . $id . '"';
    setq($sql2) or die(mysql_error());

    $this->calculaimp($ncredito);

}

function borrarartprog($ncredito, $id) {
    $sql2 = 'DELETE FROM programaciond WHERE d_programacion="' . $ncredito . '" AND d_id="' . $id . '"';
    setq($sql2) or die(mysql_error());

    $sql2 = 'DELETE FROM programaciond_imp WHERE pi_ncredito="' . $ncredito . '" AND pi_id="' . $id . '"';
    setq($sql2) or die(mysql_error());

    $this->calculaimpprog($ncredito);

}

function finalcaptura($id) {
     $estatus = "P";

        $sqlupt = 'UPDATE ncredito SET n_estatus = "'.$estatus.'" WHERE n_id = "' . $id . '"';
        setq($sqlupt) or die(mysql_error().'<br>'.$sqlupt);
}

function finalcapturaprog($id) {
     $estatus = "P";

        $sqlupt = 'UPDATE programacion SET p_estatus = "'.$estatus.'" WHERE p_id = "' . $id . '"';
        setq($sqlupt) or die(mysql_error().'<br>'.$sqlupt);
}


function abrir($id){
  $sql = 'UPDATE ncredito SET n_estatus = "N" WHERE n_id = "'.$id.'"';
  setq($sql) or die($sql);
}

function abrirprog($id){
  $sql = 'UPDATE programacion SET p_estatus = "N" WHERE p_id = "'.$id.'"';
  setq($sql) or die($sql);
}
function actualizapago($formapago, $tipocambio, $metodopago, $numcta, $ticket, $valorm, $condicion) {
        $sql = 'UPDATE ncredito SET
                n_fpago = "' . $formapago . '",
                n_tipocambio = "' . $tipocambio . '",
                n_metodopago = "' . $metodopago . '",
                n_valorm = "' . $valorm . '",
                n_condiciones = "' . strtoupper($condicion) . '"
                WHERE n_id = "' . $ticket . '"';
        setq($sql) or die($sql);
}

function actualizapagosprog($formapago, $tipocambio, $metodopago, $numcta, $ticket, $valorm, $condicion) {
        $sql = 'UPDATE programacion SET
                p_fpago = "' . $formapago . '",
                p_tipocambio = "' . $tipocambio . '",
                p_metodopago = "' . $metodopago . '",
                p_valorm = "' . $valorm . '",
                p_condiciones = "' . strtoupper($condicion) . '"
                WHERE p_id = "' . $ticket . '"';
        setq($sql) or die($sql);
}


function selectncrts($cliente){
    $sql = 'SELECT * FROM ncredito WHERE t_cliente = "'.$cliente.'" AND t_ncreditodo = "N"';
    $this->result = setq($sql) or die($sql);
}

function setfiscales($fiscales,$id){
  $sql = 'UPDATE ncredito SET n_fiscales = "'.$fiscales.'" WHERE n_id = "'.$id.'"';
  setq($sql);
}

function cancelarncr($ticket) {
  if(busca($ticket,'ncredito','n_id','n_estatus')=="T" || busca($ticket,'ncredito','n_id','n_estatus')=="Q"){
     $foliosat =  sacarcadena('UUID="','"',file_get_contents('cfd/'.$_SESSION['emp'].'/xml/'.busca($ticket,'ncredito','n_id','n_folio').'.xml'));

     require_once 'lib/nusoap.php';
      // Datos del documento
      $parametros = array();

  // Crear un cliente apuntando al script del servidor
/*
        $parametros['Usuario'] = "aocd850322pua909@mail.com";
        $parametros['Contrasena'] = "12345";
        $parametros['uuid'] = $foliosat;
        $serverURL = 'http://demo.sicofi.com.mx/SicofiWSv2';
*/

//Cancelacion de ncredito Digincrt
    $versionncr = array("32"=>"3.2","33"=>"3.3","40"=>"4.0");
    $parametros['cancelacionrequest']['Usuario'] = "facturacion@tye-solutions.com";
    $parametros['cancelacionrequest']['Contrasena'] = "vfgyat38";
    $parametros['cancelacionrequest']['UUID'] = $foliosat;
    $parametros['cancelacionrequest']['MotivoCancelacion'] = $_POST['motivocan'];
    $parametros['cancelacionrequest']['Version'] = "4.0";
    if($_POST['motivocan'] == "01"){
      $parametros['cancelacionrequest']['FolioSustitucion'] = $_POST['sustituto'];
    }

    $serverURL = 'https://cfd.sicofi.com.mx/SicofiWS33';

    $serverScript = 'Digincrt.asmx';
    $metodoALlamar = 'CancelaTFDV33';
    $resultado = "CancelaTFDV33Result";
    $metodores = "CancelacionCorrecta";
    $cancelacor = "ErrorCancelacion";

        $cliente = new nusoap_client("$serverURL/$serverScript?WSDL", 'wsdl');
        $error = $cliente->getError();
        if ($error) {
            echo '<pre style="color: red">' . $error . '</pre>';
            echo '<p style="color:red;'>htmlspecialchars($cliente->getDebug(), ENT_QUOTES).'</p>';
            die("1");
        }
        // Llamar a la funcion CancelaTFD del servidor
        $result = $cliente->call(
        $metodoALlamar,
        $parametros,
        "uri:$serverURL/$serverScript",
        "uri:$serverURL/$serverScript/$metodoALlamar"
        );
        // Analizando resultados
        if ($cliente->fault) {
          //Se cancela en sistema pero queda pendiente en SAT
          $codigo = $result[$resultado]['CodigoError'];
          $error = $result[$resultado][$cancelacor];
            $sql = 'UPDATE ncredito SET n_estatus = "Q", n_fcancelo = "'.date('Y-m-d H:i:s').'", n_cancelo = "'.$_SESSION['uid'].'"
                    WHERE n_id = "'.$ticket.'"';
            setq($sql) or die($sql);
            $sintimbre = 1;
        }
        else {
            $error = $cliente->getError();
            if ($error) {
                echo '<b style="color: red">Error encontrado: ' . $error . '</b>';
                echo "Error #005: Lo sentimos el sertvidor sat no se encuentra disponible, favor de intentar mas tarde";
                die("3");
        }
        else {
            if (!is_dir('cfd')) {
                @mkdir('cfd', 0777);
            }
            if (!is_dir('cfd/xml')) {
                @mkdir('cfd/xml', 0777);
            }
             if (!is_dir('cfd/xml/cancel')) {
                @mkdir('cfd/xml/cancel', 0777);
            }
          if($result[$resultado][$metodores] == "true"){
            file_put_contents('cfd/xml/cancel/'.busca($ticket,'ncredito','n_id','n_folio').'.xml', $result[$resultado]['AcuseCancelacion']);
            file_put_contents("Respuiestas.txt", $result[$resultado]['AcuseCancelacion']);

            $sql = 'UPDATE ncredito SET n_estatus = "C" WHERE n_id = "'.$ticket.'"';
            setq($sql) or die($sql);
            $sintimbre = 1;
          }
          else{
            $codigo = $result[$resultado]['CodigoError'];
            $error = $result[$resultado][$cancelacor];

            $sql = 'UPDATE ncredito SET n_estatus = "Q", n_fcancelo = "'.date('Y-m-d H:i:s').'", n_cancelo = "'.$_SESSION['uid'].'"
                    WHERE n_id = "'.$ticket.'"';
            setq($sql) or die($sql);
            $sintimbre = 1;
          }
        }
      }
  }
  else{
      $sql = 'DELETE FROM ncredito WHERE n_id = "'.$ticket.'"';
      setq($sql) or die($sql);
      $sql2 = 'DELETE FROM ncreditod WHERE d_ncredito = "'.$ticket.'"';
      setq($sql2) or die($sql2);
      $sql2 = 'DELETE FROM adendas WHERE a_factura = "'.$ticket.'"';
      setq($sql2) or die($sql2);
      $sqlt = 'DELETE FROM ncreditod_imp WHERE ni_ncredito = "'.$ticket.'"';
      setq($sqlt) or die($sqlt);
      $sqlt = 'DELETE FROM ncredito_relacion WHERE nr_origen = "'.$ticket.'"';
      setq($sqlt) or die($sqlt);


      $sintimbre = 1;
  }

  return($sintimbre);
}

    function insertadenda($id,$adenda,$tipo = NULL){

    if($tipo == "R"){
        $sqlda = 'DELETE FROM adendas WHERE a_factura = "'.$id.'" AND a_tipo = "R"';
        setq($sqlda) or die($sqlda);

        $sqlrem = 'SELECT DISTINCT(nr_remision)
                   FROM ncredito_remision WHERE nr_ncredito = "'.$id.'"';
        $result = setq($sqlrem) or die($sqlrem);
        $adenda="";
            while($rowr = $result->fetch_array()){
                $almaceno = busca($rowr['nr_remision'],'remisiones','r_id','r_almaceno');
                $adenda.=busca($almaceno,'almacenes','a_id','a_codigo').'-'.busca($rowr['nr_remision'],'remisiones','r_id','r_remisionc').' - ';
            }
        $adenda=substr($adenda,0,-3);
    }

    if(!empty($adenda)){
      $sql = 'SELECT MAX(a_id) FROM adendas WHERE a_factura = "'.$id.'"';
      $result = setq($sql) or die($sql);
      list($idad) = $result->fetch_array();
      $idad++;
      $sqli = 'INSERT INTO adendas SET
                a_factura = "'.$id.'",
                a_id = "'.$idad.'",
                a_tipo = "'.$tipo.'",
                a_adenda = "'.strtoupper($adenda).'"';
      setq($sqli) or die($sqli);
    }

/*
      if(busca($id,'ncredito','n_id','n_estatus') == "T" AND $tipo == "A"){
          $cadenda='';
          $sqla = 'SELECT * FROM adendas WHERE a_factura = "'.$id.'"';
          $resulta = setq($sqla) or die($sqla);
          while($rowa = $resulta->fetch_array()){
              $cadenda.=' - '.$rowa['a_adenda'];
          }

          $doc = new DOMDocument("utf-8");

          $doc->formatOutput = true;
          $doc->load('cfd/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml');
          $cargo = $doc->getElementsByTagName("Comprobante")->item(0);
          $adendad = $doc->createElement('mfn:Comentario');
          $adendad->setAttribute('xmlns:mfn','http://boveda.misncredito.net/schema');
          $adendad->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
          $adendad->setAttribute('xsi:schemaLocation','http://boveda.misncredito.net/schema https://boveda.misncredito.net/AddendaMfn.xsd');
          $cargo->appendChild($adendad);
          $tNode = $doc->createTextNode($cadenda);
          $adendad->appendChild($tNode);
          $doc->formatOutput = true;
          $string = $doc->saveXML();
          $doc->save('cfd/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml');
      }
*/
    }
    function deleteadenda($id,$ticket){
        $sql = 'DELETE FROM adendas WHERE a_id = "'.$id.'" AND a_factura ="'.$ticket.'"';
        setq($sql) or die($sql);
    }

function insertlocal($id,$tipo,$nmb,$tasa,$importe){
      $max = busca($id,'implocales','i_ncredito','MAX(i_id)');
      $max++;

      $sql = 'INSERT INTO implocales SET
              i_id = "'.$max.'",
              i_ncredito = "'.$id.'",
              i_tipo = "'.$tipo.'",
              i_nmb = "'.$nmb.'",
              i_tasa = "'.$tasa.'",
              i_importe = "'.$importe.'"
              ON DUPLICATE KEY UPDATE
              i_tasa = "'.$tasa.'",
              i_importe = "'.$importe.'"';
      setq($sql) or die($sql);
}

function insertcp($ticket,$id,$peso,$unidades,$cantidad){
    $sql = 'INSERT INTO carta_porte_old SET
            cp_ncredito = "'.$ticket.'",
            cp_idncr = "'.$id.'",
            cp_peso = "'.$peso.'",
            cp_unidad = "'.$unidades.'",
            cp_cantidad = "'.$cantidad.'"
            ON DUPLICATE KEY UPDATE
            cp_peso = "'.$peso.'",
            cp_unidad = "'.$unidades.'",
            cp_cantidad = "'.$cantidad.'"';
    setq($sql) or die($sql);
  }

  function delimplocal($id,$ncredito){
      $sql = 'DELETE FROM implocales WHERE i_id = "'.$id.'" AND i_ncredito = "'.$ncredito.'"';
      setq($sql) or die($sql);
  }


  function insertimpuesto($ncredito,$id,$impuesto,$tr,$base,$tipof,$tasac,$importe){
    $sqli = 'INSERT INTO ncreditod_imp SET
             ni_ncredito = "'.$ncredito.'",
             ni_id = "'.$id.'",
             ni_impuesto = "'.str_pad($impuesto,3,"0",STR_PAD_LEFT).'",
             ni_tr = "'.$tr.'",
             ni_base = "'.$base.'",
             ni_tipof = "'.$tipof.'",
             ni_tasac = "'.$tasac.'",
             ni_importe = "'.$importe.'"
             ON DUPLICATE KEY UPDATE
             ni_base = "'.$base.'",
             ni_tipof = "'.$tipof.'",
             ni_tasac = "'.$tasac.'",
             ni_importe = "'.$importe.'"';
    if($importe > 0)
    setq($sqli);
  }


  function calculaimp($ncredito) {
    $sqltotal = 'SELECT * FROM ncreditod WHERE d_ncredito = "'.$ncredito.'" AND d_costo > 0';
    $result = setq($sqltotal) or die($sqltotal);
    $empresa = busca($ncredito,'ncredito','n_id','n_empresa');

    $total=0;
    $niva=0;
    $nret=0;
    $nisr=0;
    $nsubtot=0;
    $descu=0;
    $amortizacion = 0;
//    $montoa = busca($ncredito,'implocales','i_tipo = "A" AND i_ncredito','i_importe');
//    if(!$montoa) $montoa = 0;
    $montoa = 0;

    while($row = $result->fetch_array()){
      $traslados = busca($row['d_id'],'ncreditod','d_ncredito = "'.$ncredito.'" AND d_id','d_traslados');
      $retenciones = busca($row['d_id'],'ncreditod','d_ncredito = "'.$ncredito.'" AND d_id','d_retenciones');

    if($montoa > 0)
        $subtotal = ($row['d_costo']*$row['d_cantidad'])-$montoa;
      else
        $subtotal = ($row['d_costo']*$row['d_cantidad']);

      $subtotal-=$row['d_descuento'];
      $descu+=$row['d_descuento'];

    $sqlip = 'SELECT e_iva,e_isr,e_retiva FROM empresas WHERE e_id = "'.$_SESSION['emp'].'"';
    $resultip = setq($sqlip);
    list($tiva,$tisr,$tretiva) = $resultip->fetch_array();

    $iva = $subtotal*$tiva*$traslados;
    $retiva = $subtotal*$tretiva*$retenciones;
    $isr = $subtotal*$tisr*$retenciones;
    $iepst = 0;
    $iepsr = 0;

          $nsubtot+=$subtotal+$montoa;
          $niva+=$iva;
//          $nish+=$iva;
          $nret+=$retiva;
          $nisr+=$isr;

          $sqldp = 'DELETE FROM ncreditod_imp WHERE ni_ncredito = "'.$ncredito.'" AND ni_id = "'.$row['d_id'].'"';
          setq($sqldp) or die($sqldp);

          if($iva >= 0) $this->insertimpuesto($ncredito,$row['d_id'],"002","T",$subtotal,"Tasa",number_format($tiva,6,'.',''),$iva);
          if($retiva > 0) $this->insertimpuesto($ncredito,$row['d_id'],"002","R",$subtotal,"Tasa",number_format($tretiva,6,'.',''),$retiva);
          if($iepst > 0) $this->insertimpuesto($ncredito,$row['d_id'],"003","T",$subtotal,"Tasa",number_format($tiepst,6,'.',''),$iepst);
          if($iepsr > 0) $this->insertimpuesto($ncredito,$row['d_id'],"003","R",$subtotal,"Tasa",number_format($tiepsr,6,'.',''),$iepsr);
          if($isr > 0) $this->insertimpuesto($ncredito,$row['d_id'],"001","R",$subtotal,"Tasa",number_format($tisr,6,'.',''),$isr);

    }

    $sqlf = 'UPDATE ncredito SET
             n_subtotal = "'.$nsubtot.'",
             n_retiva = "'.$nret.'",
             n_descuento = "'.$descu.'",
             n_iva = "'.$niva.'",
             n_isr = "'.$nisr.'"
             WHERE n_id = "'.$ncredito.'"';
    setq($sqlf) or die($sqlf);
  }

function insertcomentario($id,$comentario,$tipo){
  $idad = busca($id,'adendas','a_tipo = "N" AND a_factura','a_id');
  $comentario = trim($comentario);
  $okad = 1;

  if(!$idad){
    $sql = 'SELECT MAX(a_id) FROM adendas WHERE a_factura = "'.$id.'"';
    $result = setq($sql) or die($sql);
    list($idad) = $result->fetch_array();
    $idad++;
  }
  else{
    if(empty($comentario)){
      $sqli = ' DELETE FROM adendas WHERE a_factura = "'.$id.'"
                                      AND a_id = "'.$idad.'" AND a_tipo = "'.$tipo.'"';
      setq($sqli);
      $okad = 0;
    }
  }
  $sqli = 'INSERT INTO adendas SET
            a_factura = "'.$id.'",
            a_id = "'.$idad.'",
            a_tipo = "'.$tipo.'",
            a_adenda = "'.$comentario.'"
            ON DUPLICATE KEY UPDATE
            a_adenda = "'.$comentario.'"
            ';
  if(!empty($comentario) && $okad == 1) setq($sqli) or die($sqli);
}

function validancr($id){

  $confirma = "OK";
  $cliente = busca($id,'ncredito','n_id','n_fiscales');
  //Errores confirma: 1-> subtotal = 0; 2-> sin prodsat; 3-> costo o cantidad = 0; 4-> Falta CP; 5-> Falta regimen; 6-> Factura global
  if(busca($id,'ncredito','n_id','n_subtotal') == 0) $confirma = 1;
  elseif(busca($id,'ncreditod','d_ncredito','COUNT(*)') == 0) $confirma = 2;
  elseif(busca($id,'ncreditod','(d_costo = 0 OR d_cantidad = 0) AND d_ncredito','COUNT(*)') > 0) $confirma = 3;
  elseif(!busca($cliente,'crm_fiscales','cf_id','cf_regimen')) $confirma = 5;
  else{
    $sqlc = 'SELECT cd_cp,cf_regimen FROM crm_direcciones INNER JOIN crm_fiscales ON cf_direccion = cd_id
             WHERE cf_id = "'.$cliente.'"' ;
    $resultc = setq($sqlc);
    list($cpc,$regimen) = $resultc->fetch_array();
    if(empty($cpc)) $confirma = 4;
    if(empty($regimen)) $confirma = 5;
  }

  return($confirma);

}

function timbrar($id) {
    require_once 'lib/nusoap.php';
    /********************************************Inicio Timbrado T&E*************************************************************** */
    $folio = busca($id,'ncredito','n_id','n_folio');
    $TimbradoV33 = array();

    $TimbradoV33['timbraRequest']['Usuario'] = "facturacion@tye-solutions.com";
    $TimbradoV33['timbraRequest']['Password'] = "vfgyat38";
    $TimbradoV33['timbraRequest']['Version'] = "4.0";
    $serverURL = 'https://cfd.sicofi.com.mx/SicofiWS33';

/* Datos de prueba
    $parametros['Usuario'] = "aocd850322pua909@mail.com";
    $parametros['Contrasena'] = "ncrt@0";
    $serverURL = 'http://demo.sicofi.com.mx/SicofiWSv2';
*/

    $emp = busca($id,'ncredito','n_id','n_empresa');
    $mixml = file_get_contents('cfd/'.$emp.'/xml/'.$folio.'.xml');
    $TimbradoV33['timbraRequest']['XML'] = $mixml;

    $serverScript = 'Digincrt.asmx';
    $version = busca($id,'ncredito','n_id','n_version');
//            die($version.'<');
      $metodoALlamar = 'TimbraCFDIV40';
      $resultcfdi = "TimbraCFDIV40Result";

    $cliente = new nusoap_client("$serverURL/$serverScript?WSDL", 'wsdl');
    $error = $cliente->getError();
    if ($error) {
      $codigo = $result[$resultcfdi]['CodigoError'];
      $error = $result[$resultcfdi]['Error'];
//        die("Error: En el timbrado\n Código: ".$codigo."\n".$error);
    }
  // Llamar a la función GeneraTFD del servidor
    $result = $cliente->call(
      $metodoALlamar, $TimbradoV33, "uri:$serverURL/$serverScript", "uri:$serverURL/$serverScript/$metodoALlamar"
    );

  // Analizando resultados
    if ($cliente->fault) {
      $codigo = $result[$resultcfdi]['CodigoError'];
      $error = $result[$resultcfdi]['Error'];
//      die("Error: En el timbrado\n Código: ".$codigo."<br>".$error);
    }
    else {
      $error = $cliente->getError();
      if ($error) {
//        print_r($error).'<br>';
        $codigo = $result[$resultcfdi]['CodigoError'];
        $error = $result[$resultcfdi]['Error'];
//        die("Error: En el timbrado<br> Codigo: ".$codigo."<br>".$error);
      }
      else {
      // Creando el archivo XML
        if (!is_dir('cfd')) {
          @mkdir('cfd', 0777);
        }
        if($result[$resultcfdi]['TimbreCorrecto'] == "true"){
          file_put_contents('cfd/'.$emp.'/xml/'.$folio.'.xml', $result[$resultcfdi]['XML']);
          $codigo = "OK";

    ///////////*****************************Comprueba si hay addendas y las agrega********************************************************/

          $cadenda='';
          $sqla = 'SELECT * FROM adendas WHERE a_factura = "'.$id.'"';
          $resulta = setq($sqla) or die($sqla.' '.mysql_error());
          $resultan = setq($sqla) or die($sqla);
          while($rowa = $resulta->fetch_array()){
              $cadenda.=' - '.$rowa['a_adenda'];
          }
        }
        else{
           $codigo = $result[$resultcfdi]['CodigoError'];
           $error = $result[$resultcfdi]['Error'];
           die("Error En el timbrado<br> Codigo: ".$codigo."<br>--".$error);
        }

        if($codigo == "OK"){

          $folioinf = "F";
          $folion = busca($_SESSION['emp'], 'foliosinf', 'f_tipofac = "F" AND f_empresa', 'f_folio');
          $folios = busca($_SESSION['emp'], 'foliosinf', 'f_tipofac = "F" AND f_empresa', 'f_serie');
          $nfolion = $folion;

          $sql5 = 'UPDATE ncredito SET
                      n_estatus="T",
                      n_timbro = "'.$_SESSION['uid'].'",
                      n_ftimbro = "'.date('Y-m-d H:i').'",
                      n_serie = "'.$folios.'",
                      n_nfolio = "'.$nfolion.'",
                      n_folio = "'.$folios.$folion.'"
                   WHERE n_id="' . $id  . '"';
          setq($sql5) or die(mysql_error());

          $sql = 'SELECT * FROM folios WHERE f_estatus = "1"
                  AND f_cliente = "'.$_SESSION['emp'].'"
                  ORDER BY f_fcontrato ASC';
          $result = setq($sql) or die($sql);
          $row = $result->fetch_array();

          $usados = $row['n_usados'] + 1;
          $slq4 = 'UPDATE folios SET  f_usados = "' . $usados . '"
                   WHERE  f_id = "' . $row['n_id'] . '" AND f_estatus = 1
                   AND f_cliente = "'.$_SESSION['emp'].'"';
          setq($slq4) or die(mysql_error());

          $folion = busca($folioinf, 'foliosinf', 'f_empresa = "'.$emp.'" AND f_tipofac', 'f_folio');
          $nfolion = $folion;
          $folion++;

          $sqlemp = 'UPDATE foliosinf SET f_folio = "'.$folion.'"
                     WHERE f_tipofac = "'.$folioinf.'" AND f_empresa = "'.$emp.'"';
          setq($sqlemp) or die($sqlemp);

            // Genero PDF y envio correos
          include_once('formats/crearpdfncredito.php');
 //         sendmailer($id,busca(busca($id,'ncredito','n_id','n_fiscales'),'crm_fiscales','cn_id','cn_correo1'));
        }
      }
    }
   /*********************** Finaliza actualización de estatus  ************************************/
   return($codigo);
  }

}

class Viewncredito {

function __construct($model) {
 echo '
        <div class="app-page-title">
          <div class="page-title-wrapper">
            <div class="page-title-heading">
              '.strtoupper(busca($_GET['modulo'], 'grupos_accion', 'ga_accion = "'.$_GET['accion'].'" AND ga_modulo', 'ga_descripcion')).'
            </div>
          </div>
        </div>';

   $this->model = $model;
   $this->estatus = array("N" => "Proceso de Captura", "P" => "Lista para ser Timbrada", "T" => "Timbrada", "C" => "Cancelada", "Q" => "Solicitud de Cancelación");
   $this->colestatus = array("N" => "#FFCC33", "P" => "#FF9933", "T" => "#00CC66", "C" => "#990000", "Q" => "#3399FF");
   $this->tipocambio = array("MXN" => "MXN", "USD" => "USD", "EUR" => "EURO");
   $this->metodopago = array("PAGO EN UNA SOLA EXHIBICION" => "PUE", "PAGO EN PARCIALIDADES O DIFERIDO" => "PPD", "NO IDENTIFICADO" => "No Identificado");
   $this->nmbmetodopago = array("PUE" => "PAGO EN UNA SOLA EXHIBICION", "PPD" => "PAGO EN PARCIALIDADES O DIFERIDO", "NO IDENTIFICADO" => "No Identificado");
   $this->meses = array("1"=>"Ene","2"=>"Feb","3"=>"Mar","4"=>"Abr","5"=>"May","6"=>"Jun","7"=>"Jul","8"=>"Ago","9"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dic");
   $this->tituloncrt = array("1"=>"ncredito","3"=>"Recibo de Honorarios","4"=>"Recibo de Arrendamiento","5"=>"Nota de Crédito","6"=>"Nota de Débito","7"=>"Carta Porte (V 1.0)","8"=>"ncredito a Gobierno","9"=>"ncredito con partida y clave","11"=>"Recibo de Donativo","12"=>"CFDI de Traslado");
   $this->esttim = array('T','C');
   $this->frecuencia = array("S" => "Semanal", "Q" => "Quincenal", "M" => "Mensual","U"=>'Unica Ocasión');
  $this->viaes = array("01"=>"Autotransporte","02"=>"Transporte Marítimo","03"=>"Transporte Aéreo","04"=>"Transporte Ferroviario");
  $this->figuratr= array("01"=>"Operador","02"=>"Propietario","03"=>"Arrendador","04"=>"Notificado");
  $this->tipoubicaor = array("Origen"=>"Origen");
  $this->tipoubicade = array("Destino"=>"Destino");
  $this->tipoubica = array("Destino"=>"Destino","Origen"=>"Origen");
  $this->versionncr = array("32"=>"3.2","33"=>"3.3","40"=>"4.0");

}

function showncredito($ncredito) {
    //Sección: E2 Encabezado - Filtros

    $atras = '<a href="?modulo=ncredito&accion=index">
            <button class="btn btn-warning" type="button"><i class="fa fa-arrow-left"></i> Atrás</button>
          </a>';
    $botones = "";
  include_once('clientes.php');
  $modelcliente = new ModelClientes();
  $modelcliente->selectfisc($this->model->cliente,$this->model->fiscales);

  $numcoms = busca($this->model->id,'adendas','a_factura','COUNT(*)');
  if($numcoms == 0) $badgecom = 'btn-secondary';
  else $badgecom = "bg-success bg-accent-3";

  if ($this->model->estatus == "N") {
    $botones.= '<button type="button" id="cancelar" class="btn btn-danger" onclick="javascript:confirmaCancela()"/>
            <i class="fa fa-trash"></i> Cancelar
          </button>';

  $ervldf = array("1"=>"El subtotal de la ncredito no puede ser igual a 0","2"=>"No hay prodsat cargados en la ncredito","3"=>"prodsat con costo o cantidad = 0","4"=>"Se debe registrar el Codigo postal del cliente","5"=>"El cliente no tiene registrado el regimen fiscal","6"=>"El RFC es XAXX010101000 y no existe información de ncredito global");
  $validador = $this->model->validancr($this->model->id);

  if($validador == "OK")
  $botones.= '<a href="?modulo=ncredito&accion=finalcaptura&id=' . $ncredito . '"  data-toggle="tooltip" data-placement="top" title="Información completa" >
            <button type="button" id="aplicar" class="btn btn-success ml-1" />
            <i class="fa fa-check"></i> Finalizar captura</button></a>';
  else
  $botones.= '<button type="button" id="aplicar" class="btn btn-danger ml-1" data-toggle="tooltip" data-placement="top" title="'.$ervldf[$validador].'" />
  <i class="fas fa-exclamation-circle"></i> Finalizar captura
            </button>';

  if(busca($this->model->id,'ncreditod','d_ncredito','COUNT(*)') == 0)
  $botones.= '<a href="modulos/clonar.php?id='.$this->model->id.'&modulo='.$_GET['modulo'].'&accion='.$_GET['accion'].'&form=clonarncr&input=origen" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
            <button type="button" id="clonar" class="btn btn-info"/><i class="fas fa-clone"></i> Clonar</button>
          </a>';

  $numrels = busca($this->model->id,'ncredito_relacion','nr_origen','COUNT(*)');
  if($numrels > 0){
    $badge = '<span class="badge badge-pill badge-danger">'.$numrels.'</span>';
  }
  else $badge= "";

  $botones.= '<a href="?modulo=ncredito&accion=adenda2&id='.$this->model->id.'" id="popup">
          <button type="button" id="relacionarncredito" class="btn bg-cyan bg-darken-3 text-white" style="background: #00CEBE;"/>
          <i class="fas fa-link" style="color: #ffffff"></i> Relacionar '.$badge.'

          </button>
        </a>';

        $botones.= '<a id="popup" href="?modulo=ncredito&accion=comentarios&id='.$this->model->id.'">
          <button type="button" id="comentario" class="btn '.$badgecom.'"/>
            <i class="fas fa-comments"></i> Comentario
          </button>
        </a>';

  if($modelcliente->rfc == "XAXX010101000"){
    echo '<a id="popup" href="?modulo=ncredito&accion=ncrglobal&id='.$this->model->id.'">
            <button type="button" id="comentario" class="btn btn-info"/>
              <i class="fas fa-globe-americas"></i>> Información global
            </button>
          </a>';
  }
  }
  elseif ($this->model->estatus == "P") {
    $botones.= '<a href="?modulo=ncredito&accion=abrir&id='.$this->model->id.'">
            <button type="button" id="editar" class="btn btn-primary" /><i class="fa fa-pen-square"></i> Editar conceptos </button>
          </a>';

    $botones.= '<button type="button" id="timbrar" onclick="javascript:confirmaTimbra()" class="btn btn-success" />
    <i class="fas fa-feather-alt"></i> Timbrar </button>';

  $botones.= '<a id="popup" href="?modulo=ncredito&accion=comentarios&id='.$this->model->id.'">
      <button type="button" id="comentario" class="btn '.$badgecom.'"/>
      <i class="fas fa-feather-alt"></i> Comentario
      </button>
    </a>';
    $botones.= '<a href="formats/previewncredito.php?id='.$this->model->id.'" target="_BLANK"><button  id="previsual" title="previsualizar" class="btn btn-danger" /><i class="fa fa-file-pdf"></i> Vista previa de PDF</button></a>';


/*
  if(busca($this->model->id,'carta_porte','cp_ncredito','COUNT(*)') == 0){ $clasbtn = 'primary'; $textcp = "Añadir Carta Porte"; }
  else{ $clasbtn = 'success'; $textcp = "Ver Carta Porte"; }

  echo '<a href="?modulo=ncredito&accion=cartap&id='.$this->model->id.'">
          <button class="btn btn-'.$clasbtn.'"><i class="icon-truck2"></i> '.$textcp.'</button>
        </a>';
*/

$botones.= '<button type="button" id="cancelar" onclick="javascript:confirmaCancela()" class="btn bg-red bg-darken-2 text-white ml-1" />
        <i class="fas fa-times"></i> Cancelar </button>';

  }
  elseif ($this->model->estatus == "T") {
    $botones.= '<a href="descargar.php?file=cfd/'.$this->model->empresa.'/xml/'.$this->model->folio.'.xml" target="_BLANK">
            <button id="xml" class="btn btn-primary" /><i class="far fa-file-code"></i> XML</button>
          </a>';

    if(file_exists('cfd/'.$this->model->empresa.'/pdf/'.$this->model->folio.'.pdf'))
    $botones.= '<a href="cfd/'.$this->model->empresa.'/pdf/'.$this->model->folio.'.pdf" target="_BLANK"><button id="previsual" title="previsualizar" class="btn btn-danger" /><i class="fa fa-file-pdf"></i> Ver PDF</button></a>';
    else
    $botones.= '<a href="formats/recreapdfncredito.php?id='.$this->model->id.'" target="_BLANK"><button id="previsual" title="previsualizar" class="btn btn-danger" /><i class="fa fa-file-pdf"></i> Ver PDF</button></a>';

    $botones.= '<a href="?modulo=ncredito&accion=cancelarncredito&id='.$this->model->id.'">
            <button type="button" class="btn bg-red bg-darken-2 text-white"/>
            <i class="fa fa-times"></i> Cancelar</button>
          </a>';
          $botones.= '<a data-fancybox data-type="ajax" data-src="popup/correoncredito.php?ncredito='.$this->model->id.'&act=2" href="javascript:;">
            <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Reenviar correo"><i class="fas fa-envelope-open-text"></i> Reenviar</button>
          </a>';
  }
  if($this->model->estatus == "Q"){
        echo '<input type="button" id="cancelar" value=" " onclick="javascript:confirmaCancelaSAT()" class="btn bg-red bg-darken-2 text-white"/>';
  }

  if(busca($_SESSION['uid'],'usuarios','u_id','u_grupo') == "SUPER" && file_exists('cfd/'.$this->model->empresa.'/xml/'.$this->model->folio.'.xml' && $this->model->folio)){
    $botones.= '<a href="cfd/'.$this->model->empresa.'/xml/'.$this->model->folio.'.xml" target="_BLANK"><button id="Ver XML sin timbrar" class="btn btn-primary" /><i class="fas fa-file-code"></i> Preview XML</button></a>';
  }

  if((isset($_GET['esta']) && $_GET['esta'] == "Q") || $this->model->estatus == "Q"){
    echo '<span style="color: #CC0000;font-size:18px;font-family: kalinga;">La ncredito '.$this->model->folio.' con folios fiscal <b>'.$this->model->uuid.'</b>, emitida al cliente con RFC '.busca($this->model->cliente,'clientes','c_id','c_rfc').'<br> se encuentra en proceso de cancelación, JD Invoice te invita a cancelar la ncredito ante el SAT y posterior marcar esta como cancelada</span>';
  }
  if(isset($_GET['code'])){
    if($_GET['code'] == "OK"){
      echo '<span style="color: #00CC33;font-size:18px;font-family: kalinga;">Timbre correcto</span>';
    }
    elseif(!empty($_GET['code'])){
      echo '<span style="color: #CC0000;font-size:16px;font-family: kalinga;">Error en el timbrado de la ncredito <b>Código de Error '.$_GET['code'].'</b><br>Error en el atributo <b>'.busca($_GET['code'],'cfdi_errores','ce_codigo','ce_atributo').'</b> Regla <b>'.busca($_GET['code'],'cfdi_errores','ce_codigo','ce_regla').'</b><br> <b>Error</b>: '.busca($_GET['code'],'cfdi_errores','ce_codigo','ce_error').'</span>';
    }
  }


  /*Defino formulario para clonar ncredito*/
  echo '<form name="clonarncr" id="clonarncr" action="?modulo=ncredito&accion=clonar&destino='.$this->model->id.'" method="post">
        <input type="hidden" id="origen" name="origen" />
        </form>';

  echo '<script>
        function confirmaCancela(){
            var confirma = confirm("¿Estas seguro que deseas Cancelar la ncredito?");
            if(confirma == true){
                window.location.href="?modulo=ncredito&accion=cancelarncr&id=' . $this->model->id . '&estatus=' . $this->model->estatus.'";
            }
        }
        function confirmaCancela5000(){
            var confirma = confirm("Por disposición del SAT no se nos permite cancelar ncredito mayores a $5,000 \nSi das Clic en continuar, la ncredito será cancelada en JD Invoice, pero deberás cancelarla en tu portal del SAT");
            if(confirma == true){
                window.location.href="?modulo=ncredito&accion=cancelarncr&id=' . $this->model->id . '&estatus=' . $this->model->estatus.'";
            }
        }
        function confirmaCancelaSAT(){
            var confirma = confirm("Confirmas que has cancelado el folio ante el SAT \nJD Invoice la dará por cancelada");
            if(confirma == true){
                document.location.href="?modulo=ncredito&accion=cancelarncrs&id=' . $this->model->id . '";
                document.getElementById("cancelar").disable = true;
            }
        }
        function confirmaTimbra(){
            var confirma = confirm("¿Estas seguro que deseas Timbrar la ncredito?");
            if(confirma == true){
                window.location.href="?modulo=ncredito&accion=timbrar&id=' . $this->model->id . '";
                document.getElementById("timbrar").disabled = true;
            }
        }
      </script>';
  $lista = 'lista';

  toolbar('NOTAS DE CREDITO', $atras, "", "", $botones);

  echo '<div class="mt-1 row mx-auto container-fluid sinpadding">

         <div class="col-md-2 col-sm-3 mb-5">
          <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Usar datos </div>
          <div class="bodytb text-medium">
            <form method="post" action="?modulo=ncredito&accion=setfiscales&id='.$this->model->id.'">';
    $sqlf = 'SELECT cf_id,cf_nmfiscales FROM crm_fiscales WHERE cf_cliente = "'.$this->model->cliente.'" ORDER BY cf_nmfiscales';
    $resultf = setq($sqlf);

    echo '<select name="fiscales" class="form-control text-medium" onchange="submit();" required>';
    echo '<option value="" disabled selected>Elegir datos</option>';
    while($rowf = $resultf->fetch_array()){
      if($rowf['cf_id'] == $this->model->fiscales) $selfi = 'selected'; else $selfi = "";
      echo '<option value="'.$rowf['cf_id'].'" '.$selfi.'>'.$rowf['cf_nmfiscales'].'</option>';
    }
    echo '</select></form></div>
        </div>

      <div class="col-md-2 col-sm-3 mb-5">
        <div class=" bg-primary bg-darken-3 text-white headtb text-medium">RFC del Cliente </div>
        <div class="bodytb text-medium" style="min-height:35px">';
        echo $modelcliente->rfc;
        echo '</div>
      </div>

      <div class="col-lg-2 col-md-2 col-sm-6 mb-5">
        <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Razón Social </div>
        <div class="bodytb text-medium" style="min-height:35px"><a href="?modulo=clientes&accion=fiscales&cliente='.$this->model->cliente.'" target="_BLANK">'.$modelcliente->razonsocial.'</a></div>
      </div>

      <div class="col-lg-3 col-md-4 col-sm-6 mb-5">
        <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Correo electronico principal </div>
        <div class="bodytb text-medium" style="min-height:35px">'.$modelcliente->correo1.'</div>
      </div>';

    echo '<div class="col-lg-3 col-md-2 col-sm-6 mb-5">
            <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Regimen fiscal</div>
            <div class="bodytb text-medium" style="min-height:35px"><span style="font-size: 0.6rem;">'.$modelcliente->regimen.' '.busca($modelcliente->regimen,'cfdi_regimenfiscal','cr_id','cr_nmb').'</span></div>
          </div>';
  echo '</div>';

  if($this->model->estatus == "T" || $this->model->estatus == "C"){
    echo '<div class="row mx-auto">
            <div class="col-12 col-md-12 bg-primary text-white rounded padding-min h6">
              <center>Información de la ncredito</center></div>';
    echo '<div class="col-6 col-md-3 padding-min mb-3"><center>'.$this->model->uuid.'</center></div>';
    echo '<div class="col-6 col-md-3 padding-min mb-3"><center>'.busca($this->model->fpago,'cfdi_fpago','cf_id','CONCAT(cf_nmb," - ",cf_descripcion)').'</center></div>';
    echo '<div class="col-6 col-md-3 padding-min mb-3"><center>'.$this->model->metodopago.' - '.$this->nmbmetodopago[$this->model->metodopago].'</center></div>';
    echo '<div class="col-6 col-md-3 padding-min mb-3"><center>'.$this->model->uso.' '.busca($this->model->uso,'cfdi_uso','cu_id','cu_nmb').'</center></div>';
    echo '</div>';
  }


  $this->model->resultd($this->model->id);
  $total = 0;

  if ($this->model->estatus == "N")  $colspan = "3";
  else $width = "100";

  if($this->model->estatus == "N"){
    echo '<form action="?modulo=ncredito&accion=insertcon&ncredito='.$this->model->id.'" method="post">';
    if($this->model->idd) echo '<input type="hidden" value="'.$this->model->idd.'" name="idd" >';

  echo '<div class="row mx-auto container-fluid sinpadding w-100">';

  echo '<div class="col-md-12 text-center bg-info text-white padding-min">
          <span class="text-bold headtb text-white">Conceptos de la ncredito</span>
          <input type="submit" id="guardar" value="Guardar" class="btn btn-success" />
        </div>';

  echo '<div class="col-md-12 col-sm-12 row mx-auto">';

  echo '<div class="col-md-1 col-sm-6 mb-5">
          <div class="headtbsm bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Cantidad</div>
          <div class="bodytb">
            <input type="number" style="font-size:12px;" min="0.01" max="9999999" step="0.000001"  name="cantidad" class="form-control" value="'.$this->model->cantidad.'" required="required" autofocus="autofocus" />
          </div>
        </div>';

  echo '<div class="col-md-1 col-sm-6 mb-5">
          <div class=" bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Unidad</div>
          <div class="bodytb">
            ' . menu_select_db('unidades', 'u_clavesat','u_nmb', $this->model->claveuni, 'unidad', 'u_clavesat != "" AND u_empresa = "'.$_SESSION['emp'].'"','','','',true). '
          </div>
        </div>';
  echo '<div class="col-md-4 col-sm-6 mb-5">
          <div class=" bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Concepto</div>
            <div class="bodytb">
              <textarea class="form-control" rows="1" name="articulo" placeholder="Articulo" onfocus=this.select(); required>'.$this->model->concepto.'</textarea>
            </div>
          </div>';

  //$prods = busca($this->model->producto,'prodsat','p_claveps','p_id');
  echo '<div class="col-md-2 col-sm-4 mb-5">
          <div class=" bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Prod/Serv SAT</div>
          <div class="bodytb">
            '.menu_select_db('prodsat','p_claveps','p_alias',$this->model->producto,'producto','p_empresa = "'.$_SESSION['emp'].'"',false,'','',true).'
          </div>
        </div>';

  echo '<div class="col-md-2 col-sm-4 mb-5">
          <div class=" bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Precio U</div>
          <div class="bodytb">
            <input type="number" style="font-size:12px;" min="0.01" max="999999999" step="0.000001" class="form-control" name="costo" value="'.number_format($this->model->costo,6,'.','').'" onfocus="this.select();" />
          </div>
        </div>';

  echo '<div class="col-md-2 col-sm-4 row mx-auto">';
/*
    echo '<div class="col-md-4 col-sm-4 mb-5">
            <div class="headtbsm">Retención</div>
            <div class="bodytb">
              <input type="checkbox" name="reten" checked="checked" />
            </div>
          </div>';
    $cols = 4;
*/

  $cols = 6;
    if(isset($_GET['iva'])) $chiva = ' checked="checked" ' ;
    else $chiva = "";

  echo '<div class="col-md-'.$cols.' col-sm-4 mb-5">
          <div class=" bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Neto</div>
          <div class="bodytb">
            <input type="checkbox" name="ivain" '.$chiva.' />
          </div>
        </div>';

  echo '<div class="col-md-'.$cols.' col-sm-4 mb-5">
          <div class=" bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Aplca IVA</div>
          <div class="bodytb">
            <input type="checkbox" name="iva" checked="checked"  />
          </div>
        </div>';

  echo '</div>';  //Contenedor de IVAS
  echo '</form>';
  }

  if ($this->model->estatus == "P")
    echo '<div class="col-md-12 col-sm-6">
            <div class="padding-min bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Uso de CFDI para Cliente</div>
            <div class="bodytb">
              <form action="?modulo=ncredito&accion=setuso&id='.$this->model->id.'" method="post">
                '.menu_select_db('cfdi_uso','cu_id','cu_nmb',$this->model->uso,'uso','XXX', true, false, "S", true).'
              </form>
            </div>
          </div>';

  if ($this->model->estatus == "P") {
    $checkm = "";
    $checku = "";
    $checke= "";
    if ($this->model->tipocambio == "MXN")
        $checkm = "checked";
    elseif ($this->model->tipocambio == "USD")
        $checku = "checked";
    elseif ($this->model->tipocambio == "EUR")
        $checke = "checked";


    if ($this->model->metodopago == "PUE")
        $ch1 = "checked";
    else
        $ch1 = "";

    if ($this->model->metodopago == "PPD")
        $ch2 = "checked";
    else
        $ch2 = "";

    if($this->model->tipocambio != "MXN") $sinpatc = "sinpadding"; else $sinpatc = "";

    echo '<form action="?modulo=ncredito&accion=actualizapagos&id=' . $this->model->id . '" method="post">
          <div class="row mx-auto '.$sinpatc.'">';
    echo '<div class="col-md-12 text-center bg-info text-white padding-min headtb mt-1">
            <h6>Completar datos de pago</h6>
          </div>';

    $sql = 'SELECT cf_id,CONCAT(cf_nmb,"-",cf_descripcion) cad FROM cfdi_fpago
            WHERE cf_estatus = "A"
            ORDER BY cf_descripcion';
    $result = setq($sql) or die($sql);

    echo '<div class="col-12 col-md-3">
            <div class="input-group-prepend">
              <div class="padding-min bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Forma de pago</div>
            </div>';
    echo '<select name="formapago" onchange="submit();" class="form-control">';
    while($row = $result->fetch_array()){
      if($row['cf_id'] == $this->model->fpago) $sel = 'selected';
      else $sel = '';
      $cadena = substr($row['cad'],0,28);
      echo '<option value="'.$row['cn_id'].'" '.$sel.'>'.$cadena.'</option>';
    }
    echo '</select>';
    echo '</div>';

    echo '<div class="col-12 col-md-2">
            <div class="input-group-prepend">
              <div class="bg-indigo bg-accent-2 padding-min text-white headtb" id="inputGroup-sizing" style="background: indigo">Método de pago</div>
            </div>
            <label class="display-inline-block custom-control custom-radio ml-1">
              <input type="radio" class="custom-control-input" name="metodopago" value="PUE" onchange="submit();" ' . $ch1 . '>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description ml-0" data-toggle="tooltip" data-placement="top" title="PAGO EN UNA SOLA EXHIBICIÓN">' . $this->metodopago['PAGO EN UNA SOLA EXHIBICION'] . '</span>
            </label>
            <label class="display-inline-block custom-control custom-radio ml-1">
              <input type="radio" class="custom-control-input" name="metodopago" value="PPD" onchange="submit();" ' . $ch2 . '>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description ml-0" data-toggle="tooltip" data-placement="top" title="PAGO EN PARCIALIDADES O DIFERIDO">' . $this->metodopago['PAGO EN PARCIALIDADES O DIFERIDO'] . '</span>
            </label>
          </div>';

    echo '<div class="col-12 col-md-3">
            <div class="input-group-prepend">
              <div class="input-group-text  bg-indigo bg-accent-2 padding-min text-white headtb" id="inputGroup-sizing" style="background: indigo">Moneda</div>
            </div>
            <label class="display-inline-block custom-control custom-radio ml-1">
              <input type="radio" class="custom-control-input" name="tipocambio" value="MXN" onchange="submit();" ' . $checkm . '>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description ml-0" >' . $this->tipocambio['MXN'] . '</span>
            </label>
            <label class="display-inline-block custom-control custom-radio ml-1">
              <input type="radio" class="custom-control-input" name="tipocambio" value="USD" onchange="submit();" ' . $checku . '>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description ml-0" >' . $this->tipocambio['USD'] . '</span>
            </label>
            <label class="display-inline-block custom-control custom-radio ml-1">
              <input type="radio" class="custom-control-input" name="tipocambio" value="EUR" onchange="submit();" ' . $checke . '>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description ml-0" >' . $this->tipocambio['EUR'] . '</span>
            </label>
          </div>';

    if (busca($this->model->id,'ncredito','n_id','n_tipocambio') != "MXN"){
      echo '<div class="col-12 col-md-1">
              <div class="input-group-prepend">
                <div class="input-group-text  bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Tipo cambio</div>
              </div>
              <input type="text" name="valorm" value="' . $this->model->valorm . '" class="form-control" required="required" onchange="submit();">
            </div>';
    }
    else
        echo '<input type="hidden" name="valorm" value="1"">';

    echo '<div class="col-12 col-md-3">
            <div class="input-group-prepend">
              <div class="input-group-text  bg-indigo bg-accent-2 padding-min text-white headtb" style="background: indigo">Condición del pago</div>
            </div>
            <input type="text" name="condicion" value="'.$this->model->condiciones.'" required onchange="submit();" class="form-control" onfocus="this.select();" />
          </div>';

    echo '</div></form>';
  }

  echo '<div class="col-md-12 col-sm-12 table-responsive">';
  echo '<table class="table table-stripped table-bordered table-hover table-fixed" width="100%">
          <thead><tr class="bg-primary text-white">';

  echo '<th scope="col" width="8%">Cantidad</th>
        <th scope="col" width="10%">Unidad</th>
        <th scope="col" width="10%">Prod/Serv</th>
        <th scope="col" width="35%">Concepto</th>';
  echo '<th scope="col" width="12%">Precio Unitario</th>
        <th scope="col" width="15%">Total</th>';

  if ($this->model->estatus == "N"){
    echo '
      <th scope="col" colspan="3"></th>';
  }
  echo '</tr></thead><tbody>';
  $subtotal = 0;
  $totaliva = 0;
  $totalretiva = 0;

  while ($row = $this->model->resulttd->fetch_array()) {
    echo '<tr class="table-primary">';

    echo '<td scope="row" style="text-align:center;">' . number_format($row['d_cantidad'],2) .'</td>';
    echo '<td style="text-align:center;">' .$row['d_unidad'].'</td>';
    echo '<td style="text-align:center;">' .$row['d_producto'].'</td>';

    $concepto = $row['d_concepto'];
    echo '<td style="text-align:center;font-size:8pt;">'.$concepto.'</td>';

    echo '<td style="text-align:right;">';
    echo '$'.number_format($row['d_costo'],2,'.',',');

    echo '</td>
          <td style="text-align:right;">$ ' . number_format($row['d_costo'] * $row['d_cantidad'],2) . '</td>';

    if ($this->model->estatus == "N")
    echo '<td>
            <a href="?modulo=ncredito&accion=showncredito&ncredito='.$this->model->id.'&editc='.$row['d_id'].'">
              <button type="button" class="btn btn-primary"><i class="fa fa-pen-square"></i></button>
            </a>
          </td>
          <td>
            <a alt="Impuestos" data-toggle="tooltip" data-placement="top" title="Configuración de impuestos" id="popup" href="?modulo=ncredito&accion=configimpuesto&ncredito='.$this->model->id.'&id='.$row['d_id'].'">
              <button type="button" alt="Impuestos" class="btn btn-info"><i class="fab fa-shirtsinbulk"></i></button>
            </a>
          </td>
          <td>
            <a href="?modulo=ncredito&accion=borrarart&ncredito='.$this->model->id.'&id='.$row['d_id'].'">
              <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button>
            </a>
          </td>';
    echo '</tr>';
  }

  $colspan = 4;
  $ivas = busca($this->model->id,'ncreditod_imp','ni_impuesto = "002" AND ni_tr = "T" AND ni_ncredito ','SUM(ni_importe)');
  $tasaretiva = busca($this->model->id,'ncreditod_imp','ni_impuesto = "002" AND ni_tr = "R" AND ni_ncredito','SUM(ni_importe)');
  $tasaretisr = busca($this->model->id,'ncreditod_imp','ni_impuesto = "001" AND ni_tr = "R" AND ni_ncredito','SUM(ni_importe)');

  echo '<tr><td colspan="' . $colspan . '">&nbsp;</td>';
  echo '<td style="text-align:right;"><b>Subtotal</td><td style="text-align:right;">$ ' . number_format(round($this->model->subtotal,2), 2) . '</td></tr>';

  /*si el IVA es aplicable se suma a la ncredito*/
  if($ivas > 0){

    echo '<tr><td colspan="' . $colspan . '">&nbsp;</td><td style="text-align:right;"><b>I.V.A.</td><td style="text-align:right;">$ ' . number_format(round($this->model->iva,2),2) . '</td></tr>';
    $totiva=$subtotal+$totaliva;
    if($tasaretiva > 0){
      echo '<tr><td colspan="' . $colspan . '">&nbsp;</td><td style="text-align:right;"><b>I.V.A. RET</td><td style="text-align:right;">$ ' . number_format(round($this->model->retiva,2),2) . '</td></tr>';
    }
  }
  /*si el ISR es aplicable se suma a la ncredito*/
  if($tasaretisr > 0){
    echo '<tr><td colspan="' . $colspan . '">&nbsp;</td><td style="text-align:right;"><b>I.S.R. RET</td><td style="text-align:right;">$ ' . number_format(round($this->model->isr,2),2) . '</td></tr>';
  }
  $total = $this->model->subtotal+$this->model->iva-$this->model->isr-$this->model->retiva;
  echo '<tr><td colspan="' . $colspan . '">&nbsp;</td><td style="text-align:right;"><b>Total</td><td style="text-align:right;">$ ' . number_format($total,2) . '</td></tr>';
  echo '</table></div></td></tr></table>';

/*
  $sqla = 'SELECT * FROM adendas WHERE a_factura = "'.$this->model->id.'"';
  $resulta = setq($sqla) or die($sqla);
  $resultan = setq($sqla) or die($sqla);
  if(mysql_num_rows($resultan) > 0 ){
      echo '<br><table class="table-primary" width="95%">
      <thead class="bg-primary text-white"><tr><th colspan="3">Comentarios</th></tr></thead>';
    while($rowa = $resulta->fetch_array()){
      echo '<tr><th>Comentario</th><td>'.$rowa['a_adenda'].'</td>';

      if($this->model->estatus != "C" AND $this->model->estatus != "T")
      echo '<td width="10%"><a href="?modulo=ncredito&accion=deleteadenda&id='.$rowa['a_id'].'&ncredito='.$this->model->id.'"><input type="button" id="borrar" class="btn btn-danger" value="Borrar" /></a></td>';

      echo '</tr>';
    }
  }
*/

  echo '</center>';
}

  function browsencr($page,$cliente,$estatus,$folio,$empresa){
  $sela = "";
  $seln = "";
  $selp = "";
  $selt = "";
  $selq = "";
  $selc = "";

  if($estatus == "N") $seln = 'selected';
  elseif($estatus == "P") $selp = 'selected';
  elseif($estatus == "T") $selt = 'selected';
  elseif($estatus == "Q") $selq = 'selected';
  elseif($estatus == "C") $selc = 'selected';
  else $sela = 'selected';

    //Sección: E2 Encabezado - Filtros
    $nuevo = '
      <a id="nuevo" href="popup/setfactura" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
        <button type="button" class="btn btn-primary bg-darken-1 text-white" ><i class="fa fa-plus"></i> Nuevo</button>
      </a>';

    echo '<form name="setfacliente" method="post" action="?modulo=ncredito&accion=nuevancr">
            <input type="hidden" id="cliente" name="cliente" />
            <input type="hidden" id="fiscales" name="fiscales" />
            <input type="hidden" id="version" name="version" value="4.0" />
          </form>';
  ?>
  <script>
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
      $("#clienteje").focus();
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
    $filtro ='
    <form class="form-inline" role="form" method="post" action="?modulo=ncredito&accion=index" id="filtro">
      <div class="mb-5">
        <label for="tipom">Cliente:</label>
        <input onkeyup="ceropage();" type="text" class="form-control" id="clienteje" name="cliente" placeholder="Nombre del cliente que buscas" onfocus="this.select();" value="'. $cliente .'" />
      </div>
      <div class="mb-5">
        <label for="tipom">Folio:</label>
        <input onkeyup="ceropage();" type="text" class="form-control" name="folio" placeholder="Folio de la ncredito" onfocus="this.select();" value="'. $folio .'" />
        <script>
          function ceropage(){
            document.getElementById("page").value = 0;
            var codigo = event.which || event.keyCode;
            if(codigo === 13) {
              mandar(0);
            }
          }
        </script>
      </div>
      <div class="mb-5">
        <label for="tipom">Estatus:</label>
        <select class="form-control" name="estatus" id="estatus" >
          <option value="" '. $sela .' >Todos</option>
          <option value="N" '. $seln .' >Proceso de Captura</option>
          <option value="P" '. $selp .' >Lista para timbrar</option>
          <option value="T" '. $selt .' >Timbrada</option>
          <option value="Q" '. $selq .' >Solicitud de cancelación</option>
          <option value="C" '. $selc .' >Cancelados</option>
        </select>
      </div><!-- form group [search] -->
      <div class="mb-5">
        <label for="">Acciones:</label><br>
        <button type="button" onclick="mandar(0)" class="btn btn-info">
          <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar 
        </button>
        <a href="?modulo=ncredito&accion=index"><button type="button" class="btn btn-warning">
          <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
        </button></a>
      </div>
    </form>';
  
  toolbar('Notas de crédito', "", $filtro, $nuevo);

  echo '<div class="row">
          <div class="col-lg-12 table-responsive">
          <table class="table table-bordered table-hover table-striped" style="border-collapse:collapse;" id="myTable">
        <thead class="">';

  echo '<tr>
          <th width="8%">Folio</th>
          <th width="20%">Cliente</th>
          <th width="30%">Razón Social</th>
          <th width="10%">Fecha</th>
          <th width="8%">Estatus</th>';

  if($estatus != "Q")
    echo '<th>Reenviar</th>';
  else
    echo '<th>UUID</th>';

  echo '<th>Descargar</th>
        <th width="7%">Total</th>
        </tr></thead><tbody>';

  echo '<script>
          function gotoncr(idncr){
            document.location.href="?modulo=ncredito&accion=showncredito&ncredito=" + idncr;
          }
        </script>';
  while($row = $this->model->result->fetch_array()){
    if($row['n_estatus'] == "N") { $divid = '<input type="button" id="editar" value="Editar" class="btn btn-info">'; }
    elseif($row['n_estatus'] == "P") { $divid = '<input type="button" id="editar" value="Editar" class="btn btn-info">';}
    elseif($row['n_estatus'] == "T") { $divid = '<button type="button" id="editar" class="btn btn-primary">'.$row['n_folio'].'</button>';}
    elseif($row['n_estatus'] == "C") { $divid = '<button type="button" id="editar" class="btn btn-primary">'.$row['n_folio'].'</button>';}
    elseif($row['n_estatus'] == "E") { $divid = '<button type="button" id="editar" class="btn btn-primary">'.$row['n_folio'].'</button>';}
    elseif($row['n_estatus'] == "Q") { $divid = '<button type="button" id="editar" class="btn btn-primary">'.$row['n_folio'].'</button>';}

    echo '<tr>
            <th class="bg-primary" onclick="gotoncr('.$row['n_id'].');">
              <a class="text-white text-center" href="?modulo=ncredito&accion=showncredito&ncredito='.$row['n_id'].'">
                '.$divid.'
              </a>
            </th>';

    echo '<td>'.busca($row['n_cliente'],'crm_clientes','c_id','c_alias').'</td>
          <td>'.busca($row['n_fiscales'],'crm_fiscales','cf_id','cf_razonsocial').'</td>
          <td>'.cambiar_fecha($row['n_fcreo']).'</td>
          <td style="background:'.$this->colestatus[$row['n_estatus']].';">'.$this->estatus[$row['n_estatus']].'</td>';

    if($row['n_estatus'] != "N" && $row['n_estatus'] != "P"){
      if($row['n_estatus'] == "Q"){
        if($row['n_uuid'] == "NULL") getuuid($row['n_id']);
          echo '<td>'.$row['n_uuid'].'</td>';
        }
      else
        echo '<td>
                <a data-fancybox data-type="ajax" data-src="popup/correoncredito.php?ncredito='.$row['n_id'].'&act=2" href="javascript:;">
                  <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Nueva actividad"><i class="fas fa-envelope-open-text"></i> Reenviar</button>
                </a>
              </td>';

      //Descargar XML y PDF
      if($row['n_estatus'] != "C"){
        if(file_exists('cfd/'.$row['n_empresa'].'/pdf/'.$row['n_folio'].'.pdf'))
          echo '<td>
                  <a href="cfd/'.$row['n_empresa'].'/pdf/'.$row['n_folio'].'.pdf" target="_BLANK">
                    <button class="btn btn-danger"><i class="fa fa-file-pdf"></i> PDF</button></a></td>';
        else
          echo '<td><a href="formats/recreapdfncredito.php?id='.$row['n_id'].'" target="_BLANK">
                    <button class="btn btn-danger"><i class="fa fa-file-pdf"></i> PDF</button></a></td>';
      }
      else{
          echo '<td>
                  <a href="formats/recreapdfncredito.php?id='.$row['n_id'].'" target="_BLANK">
                    <button class="btn btn-danger"><i class="fa fa-file-pdf"></i> PDF</button></a></td>';
      }

      if($row['n_estatus'] != "C"){
          echo '<td>
                  <a href="descargar.php?file=cfd/'.$row['n_empresa'].'/xml/'.$row['n_folio'].'.xml" target="_BLANK">
                    <button class="btn btn-info text-white"><i class="fas fa-file-code"></i> XML</button></a></td>';
      }
      else{
          echo '<td>
                  <a href="descargarxmlc.php?file=cfd/'.$row['n_empresa'].'/xml/'.$row['n_folio'].'.xml" target="_BLANK">
                    <button class="btn btn-info text-white"><i class="fas fa-file-code"></i> XML</button></a></td>';
      }
    }
    else{
      echo '<td></td><td></td>';
    }

    //Descargar XML y PDF
      $total = $row['n_subtotal']-$row['n_descuento']-$row['n_retiva']+$row['n_iva']-$row['n_isr'];
      echo '<td>'.number_Format($total,2).'</td></tr>';
    }

    echo '</tbody></table></div></center>';
    if(isset($_GET['aviso'])) echo alert_back("Presione F5 para conocer el estatus de su ncredito");

    echo '<script>
    $("#myTable").DataTable( {
      paging: true,
      scrollY: 400,
      language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
      },
      responsivePriority: 1,
      pageLength:50,
  });
    </script>';
    
  }

  function browsecan($cliente,$folio,$empresa){
    echo '<form method="post">';
    echo 'Cliente <input type="text" size="50" name="cliente" value="'.$cliente.'"  autofocus onfocus="this.select();" />
            Folio <input type="text" size="10" value="'.$folio.'" name="folio" onchange="submit();" />';
    echo '<input type="submit" class="btn btn-success" id="actualizar" value="Actualizar">
          </form>';

    $lista = 'table-light table-hover';

    echo '<div class="table-responsive" id="tabla01"><center><table width="100%" class="'.$lista.'">
          <thead class="bg-primary text-white">';
    echo '<tr>
            <th scope="row" width="8%">Folio</th>
            <th scope="row" width="30%">Cliente</th>
            <th scope="row" width="15%">Fecha</th>
            <th scope="row" width="8%">Estatus</th>';
    echo '<th scope="row">UUID</th>';

    echo '<th scope="row" colspan="2">Descargar</th>
          <th scope="row" width="7%">Total</th>
          <th scope="row" width="7%">Marcar<br>Cancelada</th>
          </tr></thead>';

    echo '<script>
            function cancelncr(ncredito){
              var conff = confirm("Confirmas que has cancelado el folio ante el SAT \nJD Invoice la dará por cancelada");
              if(conff == true){
                document.location.href="?modulo=ncredito&accion=cancelarncrs&id=" + ncredito;
                document.getElementById("cancelar").disable = true;
              }
            }
          </script>';

    while($row = $this->model->resultq->fetch_array()){
      $divid = $row['n_folio'];

      if(busca($row['n_cliente'],'clientes','c_id','c_tipop') == "P"){
        $sql = '';
      }
      echo '<tr>
        <th scope="col" class="bg-primary text-white text-center">
          <a  href="?modulo=ncredito&accion=showncredito&ncredito='.$row['n_id'].'&from=canc">'.$divid.'</a>
        </th>';

      echo '<td>'.busca($row['n_cliente'],'clientes','c_id','c_nmb').'</td>
            <td>'.cambiar_fecha($row['n_fcreo']).'</td>
            <td style="background:'.$this->colestatus[$row['n_estatus']].';">'.$this->estatus[$row['n_estatus']].'</td>';

      if($row['n_uuid'] == "NULL"){
        $row['n_uuid'] = getuuid($row['n_id']);
      }

      echo '<td>'.$row['n_uuid'].'</td>';

      echo '<td><a href="formats/recreapdfncredito.php?id='.$row['n_id'].'" target="_BLANK"><input type="button" id="pdf" value=" " class="botont" /></a></td>';
      echo '<td><a href="descargarxmlc.php?id='.$row['n_id'].'" target="_BLANK"><input type="button" id="xml" value=" " class="botont" /></a></td>';

      $total = $row['n_subtotal']-busca($row['n_id'],'impuestos','i_tipo IN ("R") AND i_ncredito','SUM(i_importe)')+busca($row['n_id'],'impuestos','i_tipo = "T" AND i_ncredito','SUM(i_importe)')-busca($row['n_id'],'implocales','i_tipo IN ("A") AND i_ncredito','SUM(i_importe)');
      echo '<td>'.number_Format($total,2).'</td>
            <td><input type="button" class="botont" id="cancelar" onclick="cancelncr('.$row['n_id'].');" /></td>
            </tr>';
     }

    while($row = $this->model->resultc->fetch_array()){
      $divid = $row['n_folio'];

      if(busca($row['n_cliente'],'clientes','c_id','c_tipop') == "P"){
        $sql = '';
      }
      echo '<tr>
        <th scope="col" class="bg-primary text-white text-center" >
          <a class="text-white" href="?modulo=ncredito&accion=showncredito&ncredito='.$row['n_id'].'">'.$divid.'</a>
        </th>';

      echo '<td>'.busca($row['n_cliente'],'clientes','c_id','c_nmb').'</td>
            <td>'.cambiar_fecha($row['n_fcreo']).'</td>
            <td style="background:'.$this->colestatus[$row['n_estatus']].';">'.$this->estatus[$row['n_estatus']].'</td>';

      if($row['n_uuid'] == "NULL") getuuid($row['n_id']);
      echo '<td>'.$row['n_uuid'].'</td>';

      echo '<td><a href="formats/recreapdfncredito.php?id='.$row['n_id'].'" target="_BLANK"><input type="button" id="pdf" value="PDF" class="btn btn-danger" /></a></td>';
//      echo '<td><a href="descargarxmlc.php?id='.$row['n_id'].'" target="_BLANK"><input type="button" id="xml" value="XML" class="btn btn-primary" /></a></td>';

      $total = $row['n_subtotal']-busca($row['n_id'],'impuestos','i_tipo IN ("R") AND i_ncredito','SUM(i_importe)')+busca($row['n_id'],'impuestos','i_tipo = "T" AND i_ncredito','SUM(i_importe)')-busca($row['n_id'],'implocales','i_tipo IN ("A") AND i_ncredito','SUM(i_importe)');
      echo '<td>'.number_Format($total,2).'</td>
        <td>&nbsp;</td></tr>';
     }
      echo '<table></center>';

      if(isset($_GET['aviso'])) echo alert_back("Presione F5 para conocer el estatus de su ncredito");
  }

function cartap(){
  echo '<div class="div2" id="bboton02">';
  echo '<a href="?modulo=ncredito&accion=showncredito&ncredito='.$this->model->id.'">
            <buttonr role="button" type="button" class="btn btn-warning" id="atras" />Atrás</button>
        </a>';
  echo '</div>';

  //Cargo por trasación<br><input type="number" min="0.01" max="999999" step="0.01" name="CargoPorTasacion" value="'.$this->model->CargoPorTasacion.'" onfocus="this.select();" class="form-control" />
  if(busca($this->model->id,'carta_porte_ubicaciones','cpu_ncredito','COUNT(*)') >= 2){
    echo '<div class="table-responsive">
      <form action="?modulo=ncredito&accion=updatecpm&id='.$this->model->id.'" method="post">
      <table class="table table-bordered table-hover table-striped" style="border-collapse:collapse;" width="100%">
          <thead class="thead-primary bg-primary text-white">
            <tr><th colspan="6">Mercancias
              <a id="popup" href="?modulo=ncredito&accion=newmarcancia&ncredito='.$this->model->id.'">
                <button class="btn-sm btn-success"><i class="fas fa-barcode"></i>Añadir Mercancia</button>
              </a></th>
            </tr></thead>
            <tr>
              <td width="20%">Peso Bruto*<br><input type="number" class="form-control" min="0.001" max="999999" step="0.001" name="PesoBrutoTotal" value="'.$this->model->PesoBrutoTotal.'" onfocus="this.select();" /></td>
              <td width="40%">Unidad Peso*<br>
              '.menu_select_db('cfdi_unidadescp','cu_clave','cu_nmb',$this->model->UnidadPeso,'UnidadPeso',"XXX",false,false,"N",true).'</td>
              <td width="20%">Total Mercancias*<br><input type="number" min="1" max="999999" step="1" name="NumTotalMercancias" value="'.$this->model->NumTotalMercancias.'" onfocus="this.select();" class="form-control" /></td>
              <td width="20%"></td>
            </tr><tr>
              <td>Número permiso SCT*<br><input type="text" class="form-control" name="numpermiso" value="'.$this->model->numpermiso.'" onfocus="this.select();" /></td>
              <td>Tipo permiso SCT*<br>
              '.menu_select_db('catcp_tipopermiso','ccp_clave','ccp_nmb',$this->model->tipopermiso,'tipopermiso',"XXX",false,false,"N",true).'</td>
              <td>transporte* '.menu_select_array($this->figuratr,$this->model->figuratransporte,'figuratransporte',true).'</td>
              <td>Licencia*<br><input type="text" class="form-control" name="NumLicencia" value="'.$this->model->NumLicencia.'" onfocus="this.select();" required="required" /></td>
            </tr><tr>
              <td>RFC Figura transporte*<br><input type="text" class="form-control" name="RFCFigura" value="'.$this->model->RFCFigura.'" onfocus="this.select();" required="required" /></td>
              <td width="20%">Nombre transporta*<br><input type="text" class="form-control" name="NombreFigura" value="'.$this->model->NombreFigura.'" onfocus="this.select();" required="required" /></td>
              <td>Tipo Transporte*
              '.menu_select_db('catcp_configtransporte','ccc_clave','ccc_nmb',$this->model->configtransporte,'configtransporte',"XXX",false,false,"N",true).'</td>
              <td>Placa*<br><input type="text" class="form-control" name="PlacaVM" value="'.$this->model->PlacaVM.'" onfocus="this.select();" required="required" /></td>
            </tr><tr>
              <td>Año Trans.*<br><input type="text" class="form-control" name="AnioModeloVM" value="'.$this->model->AnioModeloVM.'" onfocus="this.select();" required="required" /></td>
              <td>Aseguradora*<br><input type="text" class="form-control" name="AseguraRespCivil" value="'.$this->model->AseguraRespCivil.'" onfocus="this.select();" required="required" /></td>
              <td>Poliza Seguro*<br><input type="text" class="form-control" name="PolizaRespCivil" value="'.$this->model->PolizaRespCivil.'" onfocus="this.select();" required="required" /></td>
              <td><button class="btn btn-success"><i class="fas fa-truck-loading"></i> Actualizar</button></td>
            </tr><tr>
              <td>Tipo Remolque 1<br>
              '.menu_select_db('catcp_remolques','ccr_clave','ccr_nmb',$this->model->TipoRem1,'TipoRem1',"XXX",false,false,"N",false).'</td></td>
              <td>Placa Remolque 1<br><input type="text" class="form-control" name="PlacaRem1" value="'.$this->model->PlacaRem1.'" onfocus="this.select();" /></td>
              <td>Tipo Remolque 2<br>
              '.menu_select_db('catcp_remolques','ccr_clave','ccr_nmb',$this->model->TipoRem2,'TipoRem2',"XXX",false,false,"N",false).'</td></td>
              <td>Placa Remolque 2<br><input type="text" class="form-control" name="PlacaRem2" value="'.$this->model->PlacaRem2.'" onfocus="this.select();" /></td>
            </tr>
          </table></form>
      </div>';

    echo '<script>
            function delmerca(idmerca){
              var conf = confirm("¿Estas seguro que deseas borrar esta mercancia?");
              if(conf == true){
                window.location.href="?modulo=ncredito&accion=delmerca&ncredito='.$this->model->id.'&idm=" + idmerca;
              }
            }
          </script>';

    echo '<div class="table-responsive">
          <table class="table table-bordered table-hover table-striped" style="border-collapse:collapse;" width="100%">
            <thead class="thead-primary bg-primary text-white">
            <tr>
              <th>Bienes Transportados*<a id="popup" href="?modulo=ncredito&accion=newmarcancia&ncredito='.$this->model->id.'">
                <button class="btn-xs btn-success">Añadir</button>
              </a></th>
              <th>Peligroso*</th>
              <th>Descripcion*</th>
              <th>Cantidad*</th>
              <th>Unidad</th>
              <th>Peso KG*</th>
              <th>Valor Mercancia*</th>
              <th><a id="popup" href="?modulo=ncredito&accion=importardoc&ncredito='.$this->model->id.'">
                <button class="btn-xs btn-info">Importar</button>
              </a></th>
            </tr></thead>';

    while($rowm = $this->model->resultmer->fetch_array()){
      echo '<tr><form method="post" action="?modulo=ncredito&accion=updatemerca&ncredito='.$this->model->id.'&idm='.$rowm['cpm_idm'].'">';

      if($rowm['cpm_peligroso'] == "Sí") $checkpeligro = "checked";

        echo '<th>'.menu_select_db('prodsat','p_claveps','p_alias',$rowm['cpm_BienesTransp'],'BienesTransp','p_empresa = "'.$_SESSION['emp'].'"',false,false,false,true).'</th>
              <th><input type="checkbox" name="peligroso" '.$checkpeligro.' /></th>
              <th><input type="text" class="form-control" name="Descripcion" value="'.$rowm['cpm_Descripcion'].'" onfocus="this.select();" required="required" /></td>
              <th><input type="number" class="form-control" name="Cantidad" min="0.01" max="99999" step="0.01" value="'.$rowm['cpm_Cantidad'].'" onfocus="this.select();" required="required" /></td>
              <td>'.menu_select_db('unidades','u_unidad','u_nmb',$rowm['cpm_ClaveUnidad'],'ClaveUnidad','u_empresa = "'.$_SESSION['emp'].'"',false,false,"N",true).'</td></td>
              <th><input type="number" class="form-control" name="PesoEnKg" min="0.01" max="99999" step="0.001" value="'.$rowm['cpm_PesoEnKg'].'" onfocus="this.select();" required="required" /></td>
              <th><input type="number" class="form-control" name="ValorMercancia" min="0.00" max="9999999" step="0.01" value="'.$rowm['cpm_ValorMercancia'].'" onfocus="this.select();" required="required" /></td>
              <td>
                <button class="btn-xs btn-primary"><i class="fas fa-sync-alt"></i></button>
                <button type="button" class="btn-xs btn-danger" onclick="delmerca('.$rowm['cpm_idm'].')"><i class="fa fa-trash"></i></button>
              </td>';
      echo '</form></tr>';
    }
    echo '</table></div>';
  }

  if($this->model->cpnum > 0){
    echo '<div class="table-responsive">
        <table class="table table-bordered table-hover table-striped" style="border-collapse:collapse;" width="100%">
          <thead class="thead-primary bg-primary text-white">
            <tr><th colspan="8">Ubicaciones <a id="popup" href="?modulo=ncredito&accion=ubicaciondir&ncredito='.$this->model->id.'">
                <button class="btn-sm btn-secondary"><i class="fas fa-truck"></i>Añadir dirreción</button></a>
            </th></tr>
            <tr>
              <th>Tipo</th>
              <th>RFC</th>
              <th>Nombre</th>
              <th>Fecha Salida/Llegada</th>
              <th>Distancia Recorrida</th>
              <th>Dirección</th>
            </tr>
          </thead>';

    echo '<script>
            function delubica(idubica){
              var tipoub = document.getElementById("tipoub" + idubica).value;
              if(tipoub == "Origen"){
                alert("Error: El Origen no puede ser borrado");
              }
              else{
                var conf = confirm("¿Estas seguro que deseas borrar esta ubicación?");
                if(conf == true){
                  window.location.href="?modulo=ncredito&accion=delubicacion&ncredito='.$this->model->id.'&idu=" + idubica;
                }

              }
            }
          </script>';
    $sqld = 'SELECT * FROM carta_porte_ubicaciones WHERE cpu_ncredito = "'.$this->model->id.'" ORDER BY cpu_id ASC';
    $resultd = setq($sqld);
    while($rowd = $resultd->fetch_array()){
      echo '<input type="hidden" value="'.$rowd['cpu_TipoUbicacion'].'" id="tipoub'.$rowd['cpu_id'].'">';

      echo '<tr><form method="post" action="?modulo=ncredito&accion=updateubica&ncredito='.$this->model->id.'&idu='.$rowd['cpu_id'].'">';

      $readon = "";
      if($rowd['cpu_TipoUbicacion'] == "Origen"){
        echo '<th>'.menu_select_array($this->tipoubicaor,$rowd['cpu_TipoUbicacion'],'TipoUbicacion',true).'</th>';
        $readon = 'disabled="disabled"';
      }
      else
        echo '<th>'.menu_select_array($this->tipoubicade,$rowd['cpu_TipoUbicacion'],'TipoUbicacion',true).'</th>';

      echo '
              <td><input type="text" class="form-control-sm" name="RFCRemitenteDestinatario" value="'.$rowd['cpu_RFCRemitenteDestinatario'].'" required="required" /></td>
              <td><input type="text" class="form-control-sm" name="NombreRFC" value="'.$rowd['cpu_NombreRFC'].'" /></td>
              <th><input type="datetime-local" class="form-control-sm" name="FechaHoraSalidaLlegada" value="'.str_replace(" ","T",date('Y-m-d H:i',strtotime($rowd['cpu_FechaHoraSalidaLlegada']))).'"  required="required" /></th>
              <th><input type="number" class="form-control-sm" '.$readon.' name="DistanciaRecorrida" step="0.01" max="9999" min="0" value="'.$rowd['cpu_DistanciaRecorrida'].'" required="required" /></th>
              <td>
                <a href="?modulo=ncredito&accion=updateubica&ncredito='.$this->model->id.'&idu='.$rowd['cpu_id'].'">
                  <button class="btn-xs btn-primary"><i class="fas fa-sync-alt"></i></button>
                </a>
                <a id="popup" href="?modulo=ncredito&accion=setubicacion&ncredito='.$this->model->id.'&idu='.$rowd['cpu_id'].'">
                  <button type="button" class="btn-xs btn-info"><i class="fas fa-home"></i></button>
                </a>
                <button type="button" class="btn-xs btn-danger" onclick="delubica('.$rowd['cpu_id'].');"><i class="fa fa-trash"></i></button>
              </td>
            </form></tr>';
    }
    echo '</table></div>';
  }

  echo '<center>
        <form method="post" action="?modulo=ncredito&accion=insertcartaporte&ncredito='.$this->model->id.'">';
  echo '<div class="table-responsive">
          <table class="table table-bordered table-hover table-striped" style="border-collapse:collapse;">
          <thead class="thead-primary bg-primary text-white">
            <tr><th colspan="6">Información del Complemento de carta porte</th></tr>
            <tr>
              <th>Internacional</th>
              <th>Salida/Entrada</th>
              <th>Pais Origen</th>
              <th>Via Entrada/Salida</th>
              <th>Total Distancia</th>';
  if($this->model->cpnum == 0)
    echo '<th>Fecha y hora de salida</th>';

  echo '</tr>
          </thead>';
  $checkint = "";
  if(!isset($this->model->TranspInternac)) $checint = "";
  else{
    if($this->model->TranspInternac == "Si") $checint = "checked";
    else $checint = "";
  }

  $radiosa = "";
  $radioes = "";
  if(!isset($this->model->EntradaSalidaMerc)) $radiosa = "checked";
  else{
    if($this->model->EntradaSalidaMerc == "Salida") $radiosa = "checked";
    elseif($this->model->EntradaSalidaMerc == "Entrada") $radioes = "checked";
  }

  if(!isset($this->model->PaisOrigenDestino)) $this->model->PaisOrigenDestino = "MEX";
  if(!isset($this->model->ViaEntradaSalida)) $this->model->ViaEntradaSalida = "01";
  if(!isset($this->model->TotalDistRec)) $this->model->TotalDistRec = 0;

  echo '<tr>
          <td><input type="checkbox" class="form-control" name="TranspInternac" '.$checint.'/></td>
          <td>Salida <input type="radio" name="EntradaSalidaMerc" value="Salida"  '.$radiosa.' />
          <br>
              Entrada <input type="radio"  name="EntradaSalidaMerc" value="Entrada"  '.$radioes.' /></td>
          <td><input type="text" class="form-control" name="PaisOrigenDestino" size="5" value="'.$this->model->PaisOrigenDestino.'" /></td>
          <td>'.menu_select_array($this->viaes,$this->model->ViaEntradaSalida,'ViaEntradaSalida',true).'</td>
          <td><input type="number" class="form-control" min="0" max="99999" step="0.01" value="'.$this->model->TotalDistRec.'" required="true" /></thd>';
  if($this->model->cpnum == 0)
    echo '<td><input type="datetime-local" class="form-control" name="FechaHoraSalidaLlegada" value="'.date('Y-m-d').'T'.date('H:i:s',strtotime('+1 hour')).'" /></td>';
  echo '</tr>
        <tr><td colspan="6"><button type="submit" class="btn btn-success" id="atras" />Guardar</button></td></tr>
      </table></div></form>';


  echo '</div></center>';
}

}
?>