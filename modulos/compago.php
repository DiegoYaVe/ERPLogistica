<?php
//ini_set('display_errors','on');
class Compago{

var $model;
var $view;

function Compago(){ // constructor
    $this->model= new ModelCompago; // crea modelo
}

function index(){
    if(!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
    if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-').'01';
    if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d');
    if(!isset($_REQUEST['folio'])) $_REQUEST['folio'] = NULL;
    if(!isset($_REQUEST['empresa'])) $_REQUEST['empresa'] = $_SESSION['emp'];
    if(!isset($_REQUEST['facrec'])) $_REQUEST['facrec'] = NULL;

    $this->model->resultcpago($_REQUEST['cliente'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['estatus'],$_REQUEST['folio'],$_REQUEST['empresa'],$_REQUEST['facrec']);
    $this->view = new  ViewCompago($this->model);
    $this->view->browsecpago($_REQUEST['cliente'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['estatus'],$_REQUEST['folio'],$_REQUEST['empresa']);
}

function showcpago(){
  if(!isset($_GET['cpago'])) $_GET['cpago'] = NULL;

  $this->model->select($_GET['cpago']);
  $this->view = new ViewCompago($this->model);
  $this->view->showcpago($_GET['cpago']);
}

function insertcon(){

  if(!isset($_GET['idd']))
    $idf = $this->model->insertcon($_GET['cpago'],$_POST['foliof'],$_POST['moneda'],$_POST['metodo'],$_POST['pacialidad'],$_POST['saldoant'],$_POST['importe'],$_POST['uuid'],$_POST['tipo']);

redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['cpago']);
}

function updatecon(){
  if(isset($_POST['reten'])) $reten = 1;
  else $reten = 0;

  if(isset($_POST['iva'])) $iva = 1;
  else $iva = 0;

  if(isset($_POST['ivain'])) $ivain = 1;
  else $ivain = 0;
  $this->model->updatecon($_GET['idd'],$_GET['cpago'],$_POST['foliof'],$_POST['moneda'],$_POST['metodo'],$_POST['pacialidad'],$_POST['saldoant'],$_POST['importe'],$_POST['uuid'],$_POST['tipo']);

  redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['cpago']);
}

function borrarart() {
        $this->model->borrarart($_GET['cpago'], $_GET['id']);
//        $this->model->insertadenda($_GET['cpago'],NULL,"R");

      redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['cpago']);
}

function timbrar(){
     $ticket = $_GET['id'];
     $cliente = busca($ticket,'cpago','c_id','c_cliente');
     /* $restantes = busca("A",'folios','f_estatus','SUM(f_contratados)') - busca("A",'folios','f_estatus','SUM(f_usados)');
    if($restantes <= 0){
      die(alert_back("Error los tus folios se han agotado ",true));
    }
    elseif($restantes <= 20){
      echo alert_back("Advertencia: Te restan ".$restantes.' Folios, No dejes de contactar a tu ejecutivo de T&E Technological Solutions');
    } */

        $sql = 'SELECT * FROM folios WHERE f_estatus = "1" ORDER BY f_fcontrato ASC LIMIT 0,1';
        $result = setq($sql) or die(mysql_error() . $sql);
        $row = $result->fetch_array();

        $sql2 = 'SELECT MAX(fd_id) FROM foliosd WHERE fd_contrato = "' . $row['f_fcontrato'] . '"';
        $result2 = setq($sql2) or die(mysql_error());
        list($id) = $result2->fetch_array();

        $id = $id + 1;

        if(busca($ticket,'foliosd','fd_factura','COUNT(*)') == 0){
            $sql3 = 'INSERT INTO foliosd(fd_id, fd_contrato, fd_cliente, fd_factura, fd_fechatimbrado,fd_timbro)VALUES
                     ("' . $id . '",  "' . $row['c_contrato'] . '", "' . busca($_GET['id'], 'cpago', 'c_id', 'c_cliente') . '" , "' . $_GET['id'] . '", "' . date('Y-m-d H:i:s') . '","'.$_SESSION['uid'].'")
                    ON DUPLICATE KEY UPDATE fd_factura = "'.$ticket.'",
                    fd_cliente = "'.$cliente.'",
                    fd_fechatimbrado = "'.date('Y-m-d H:i:s').'",
                    fd_timbro = "'.$_SESSION['uid'].'"';
            setq($sql3) or die(mysql_error() . $sql3);
        }

       include_once('modulos/xmlcpago.php');
//         include_once('modulos/xml.php');
        $xml = new Modelxml();
        if($xml->resultxml($_GET['id']) == true){
            $this->model->timbrar($_GET['id']);
        }

redirect("?modulo=compago&accion=showcpago&cpago=".$ticket);
}

function setfiscales(){
  $this->model->setfiscales($_POST['fiscales'],$_GET['id']);

  redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['id']);
}

function registracuentac(){

if(isset($_POST['cuenta']) && !empty($_POST['cuenta']))
  $idc = $this->model->registracuentac($_REQUEST['cliente'],$_POST['banco'],$_POST['cuenta']);

  $sqlu ='UPDATE cpago SET c_cuentac = "'.$idc.'" WHERE c_id = "'.$_GET['cpago'].'"';
  setq($sqlu) or die($sqlu);

  redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['cpago']);
}

function nuevafac(){
  if(empty($_POST['idcuenta'])){
    if(isset($_POST['cuenta']) && !empty($_POST['cuenta']))
      $_REQUEST['idcuenta'] = $this->model->registracuentac($_REQUEST['cliente'],$_POST['banco'],$_POST['numcta']);
  }

  $_REQUEST['uso'] = "CP01";
  $_POST['formapago'] = "03";
  $_POST['fecha'] = date('Y-m-d').' 00:00:00';
  $_REQUEST['empresa'] = $_SESSION['emp'];

  $idfac = $this->model->insertfacturablanco($_REQUEST['cliente'],$_REQUEST['empresa'],$_REQUEST['confirmacion'],$_REQUEST['uso'],$_REQUEST['fecha'],$_REQUEST['idcuenta'],"1",$_POST['formapago']);

  redirect("?modulo=compago&accion=showcpago&cpago=".$idfac."&new=1");
}

function cancelarfac() {
  $this->model->cancelarfac($_GET['id']);

  redirect('?modulo=compago&accion=index');
}

function reenviar(){
  $cliente = busca($_GET['id'],'cpago','c_id','c_cliente');

  echo '<script>
        function enable(){
            if(document.getElementById("enaex").checked){
                document.getElementById("correoex").disabled = false;
                document.getElementById("correoex").focus();
            }
            else{
                document.getElementById("correoex").disabled = true;
            }
        }
        </script>';
  echo '<form action="mailercpago.php" method="get" target="_BLANK">
  <table class="lista"><thead><tr><th colspan="2">Elija el correo para enviar el comprobante</th></tr></thead>
        <input type="hidden" name="id" value="'.$_GET['id'].'" />
        <tr><td>'.busca($cliente,'clientes','c_id','c_email').'</td>
        <td>
          <input type="hidden" name="correo1" value="'.busca($cliente,'clientes','c_id','c_email').'" />
          <input type="checkbox" name="mail1" checked />
        </td></tr>';
      echo '<td><input type="email" name="correo2" placeholder="Correo Extra" id="correoex"  disabled /></td>
            <td><input type="checkbox" name="mail2" id="enaex" onchange="enable();" /></td>';
      echo '<tr><td colspan="2"><input type="submit" class="botont" id="enviar" value="" /></td></tr>';
      echo '</form>';
    }

function changeempresa(){
  $sql = 'UPDATE cpago SET c_empresa = "'.$_POST['empresa'].'" WHERE c_id = "'.$_GET['id'].'"';
  setq($sql) or die($sql);

  redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['id']);
}

function abrir(){
  $sql = 'UPDATE cpago SET
          c_estatus = "N"
          WHERE c_id = "'.$_GET['id'].'"';
  setq($sql) or die($sql);

  redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['id']);
}

function setuso(){

  $sql = 'UPDATE cpago SET
          c_cuentac = "'.$_POST['cuentac'].'",
          c_cuentae = "'.clearvmayus($_POST['cuentae']).'",
          c_bancoe = "'.clearvmayus($_POST['bancoe']).'",
          c_fpago = "'.clearvmayus($_POST['formapago']).'",
          c_fecha = "'.str_replace("T"," ",$_POST['fecha']).'",
          c_confirmacion = "'.clearvmayus($_POST['confirmacion']).'"
          WHERE c_id = "'.$_GET['id'].'"';
  setq($sql) or die($sql);

  redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['id']);
}
function adenda2(){
  $this->model->select($_GET['id']);

  $sql1 = 'SELECT c_id,c_folio,c_uuid FROM cpago
           WHERE c_id != '.$this->model->id.' AND c_estatus IN ("T","C","Q")
           AND c_empresa="'.$this->model->empresa.'" AND c_cliente="'.$this->model->cliente.'"
           AND c_id NOT IN (SELECT cr_cpago FROM cpago_relacion WHERE cr_origen = "'.$this->model->id.'")
           ORDER BY c_ftimbro DESC';
  $resultf = setq($sql1) or die($sql1);

  echo '<form action="?modulo=compago&accion=insertadenda2&id='.$this->model->id.'" method="post">
        <table class="lista" width="100%"><thead><th colspan="4">Relaciona CFDI</th></thead>
        <tbody><tr>
        <th>Borrar</th>
        <th>Tipo relacion</th>
        <th>Folio</th>
        <th>UUID</th>
        <th></th></tr>';

        echo'<tr><td>&nbsp;</td><td>'.menu_select_db('cfdi_relaciones', 'cr_id', 'cr_nmb', '', 'cfdi_relaciones', 'cr_estatus="A"', false,  false, false, true).'</td>';
        echo'<td><select name="cpago" required>';

        while($row = $resultf->fetch_array()){
            echo'<option value="'.$row['c_id'].'">'.$row['c_folio'].'</option>';
        }
        echo '</select></td>';
        echo'<td><center><input type="submit" id="guardar" value=" " class="botont" /></center></td></tr>';

        $sql2 = 'SELECT * FROM cpago_relacion WHERE cr_origen = '.$this->model->id.'';
        $result2 = setq($sql2) or die($sql2);
        while($row2 = $result2->fetch_array()){
          echo '<tr><td><a href="?modulo=compago&accion=deladenda2&origen='.$this->model->id.'&tiporel='.$row2['cr_tiporelacion'].'&cpago='.$row2['cr_cpago'].'">
                      <input type="button" class="botont" id="borrar" />
                    </a></td>
                    <th>'.busca($row2['cr_tiporelacion'],'cfdi_relaciones','cr_id','cr_nmb').'</th>
                    <td>
                        <a target="_BLANK" href="?modulo=compago&accion=showcpago&factura='.$row2['cr_cpago'].'">
                            '.busca($row2['cr_cpago'],'cpago','c_id','c_folio').'
                        </a></td>
                    <td>'.busca($row2['cr_cpago'],'cpago','c_id','c_uuid').'</td>
                </tr>';
        }

        echo'
        </table>
    </form>';
}

function insertadenda2(){

  $sqls='SELECT * FROM cpago_relacion WHERE cr_origen = "'.$_GET['id'].'" AND cr_tiporelacion = "'.$_POST['cfdi_relaciones'].'" AND cr_cpago = "'.$_POST['cpago'].'" ';
  $results=setq($sqls) or die($sqls);

  if(mysql_num_rows($results)==0){
    $sql = 'INSERT INTO cpago_relacion SET
        cr_origen = "'.$_GET['id'].'",
        cr_tiporelacion = "'.$_POST['cfdi_relaciones'].'",
        cr_cpago = "'.$_POST['cpago'].'"';
    setq($sql) or die($sql);
  redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['id']."");
  }
  else{
      die(alert_back("La relacion ya esta dada de alta",true));
      redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['id']."");
  }
}

function deladenda2(){
  $sql = 'DELETE FROM cpago_relacion
          WHERE cr_origen = "'.$_GET['origen'].'" AND
          cr_tiporelacion = "'.$_GET['tiporel'].'" AND cr_cpago = "'.$_GET['cpago'].'"';
  setq($sql) or die($sql);

  redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['origen']."");
}

function finalcaptura() {
        $this->model->finalcaptura($_GET['id']);
      redirect("?modulo=compago&accion=showcpago&cpago=".$_GET['id']);
}

function setpagosm(){

  $facts = explode(',',$_POST['facturas']);
  for ($i=0; $i<count($facts);  $i++) {
    if($_POST['combo'.$facts[$i]]){

      if($_POST['total'.$facts[$i]] == $_POST['pagado'.$facts[$i]]) $metodo = "PUE";
      else $metodo = "PPD";

      $idcon = $this->model->insertcon($_GET['cpago'],$_POST['folio'.$facts[$i]],"MXN",$metodo,$_POST['parical'.$facts[$i]],$_POST['saldo'.$facts[$i]],$_POST['pagado'.$facts[$i]],$_POST['uuid'.$facts[$i]],"F");
      if($_POST['iva'.$facts[$i]] > 0){
        $triva = ($_POST['pagado'.$facts[$i]]/$_POST['total'.$facts[$i]])*$_POST['iva'.$facts[$i]];
        $baseiva = ($_POST['pagado'.$facts[$i]]/$_POST['total'.$facts[$i]])*$_POST['base'.$facts[$i]];

        $baseiva0 = 0;
        $triva0 = 0;
      }
      else{
        $triva = 0;
        $baseiva = 0;

        $baseiva0 = $_POST['base'.$facts[$i]];
        $triva0 = 0;
      }

      if($_POST['retiva'.$facts[$i]] > 0){
        $retiva = ($_POST['pagado'.$facts[$i]]/$_POST['total'.$facts[$i]])*$_POST['retiva'.$facts[$i]];
        $baseretiva = ($_POST['pagado'.$facts[$i]]/$_POST['total'.$facts[$i]])*$_POST['base'.$facts[$i]];
      }
      else { $retiva = 0; $baseretiva = 0; }

      if($_POST['isr'.$facts[$i]] > 0){
        $risr = ($_POST['pagado'.$facts[$i]]/$_POST['total'.$facts[$i]])*$_POST['isr'.$facts[$i]];
        $baseir = ($_POST['pagado'.$facts[$i]]/$_POST['total'.$facts[$i]])*$_POST['base'.$facts[$i]];
      }
      else{ $risr  = 0; $baseir = 0; }

      if($_POST['ieps'.$facts[$i]] > 0){
        $tieps = ($_POST['base'.$facts[$i]]/$_POST['total'.$facts[$i]])*$_POST['ieps'.$facts[$i]];
        $baseieps = 0;
      }
      else{
        $tieps = 0;
        $baseieps = 0;
      }

      $this->model->setimpuestos($_GET['cpago'],$idcon,$retiva,$risr,$tieps,$baseiva,$triva,$baseiva0,$triva0);
    }
  }
  ?>
    <script>
      window.opener.location.reload();
      window.close();
    </script>
  <?php
}

}

class ModelCompago{

function select($id){
    $sql = 'SELECT * FROM cpago WHERE c_id = "'.$id.'"';
    $result = setq($sql) or die($sql);
    $row = $result->fetch_array();
    $this->id = $row['c_id'];
    $this->empresa = $row['c_empresa'];
    $this->cliente = $row['c_cliente'];
    $this->confirmacion = $row['c_confirmacion'];
    $this->fecha = $row['c_fecha'];
    $this->fcreo = $row['c_fcreo'];
    $this->estatus = $row['c_estatus'];
    $this->folio = $row['c_folio'];
    $this->fpago = $row['c_fpago'];
    $this->serie = $row['c_serie'];
    $this->nfolio = $row['c_nfolio'];
    $this->cuentac = $row['c_cuentac'];
    $this->bancoc = $row['c_bancoc'];
    $this->cuentae = $row['c_cuentae'];
    $this->bancoe = $row['c_bancoe'];
    $this->uuid = $row['c_uuid'];
    $this->uso = $row['c_uso'];
    $this->fiscales = $row['c_fiscales'];
}

function selectd($factura,$id){
  $sql = 'SELECT * FROM cpagod WHERE d_factura = "'.$factura.'" AND d_id = "'.$id.'"';
  $result = setq($sql) or die($sql);
  $row = $result->fetch_array();
  $this->id = $row['cd_cpago'];
  $this->idd = $row['cd_id'];
  $this->factura = $row['cd_factura'];
  $this->moneda = $row['cd_moneda'];
  $this->tipocambio = $row['cd_tipocambio'];
  $this->metodo = $row['cd_metodo'];
  $this->nparc = $row['cd_nparc'];
  $this->impsaldoant = $row['cd_impsaldoant'];
  $this->imppagado = $row['cd_imppagado'];
  $this->impsaldoinsoluto = $row['cd_impsaldoinsoluto'];
}

function resultcpago($cliente,$fini,$ffin,$estatuss,$folio,$empresa,$factura){
    $pages = $page*50;
    $sql = 'SELECT * FROM cpago WHERE DATE(c_fcreo) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
    if($cliente) $sql.=' AND c_cliente IN (SELECT c_id FROM crm_clientes WHERE c_empresa = "'.$_SESSION['emp'].'" AND (c_nmb LIKE "%'.$cliente.'%" OR c_alias LIKE "%'.$cliente.'%"))';
    if($estatuss) $sql.=' AND c_estatus = "'.$estatuss.'"';
    if($folio) $sql.=' AND c_folio = "'.$folio.'"';
    if($factura) $sql.=' AND c_id IN (SELECT cd_cpago FROM cpagod WHERE cd_factura = "'.$factura.'") ';

    $grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
    if($grupo != "ADMIN"){
      $almacen = busca($_SESSION['uid'],'usuarios','u_id','u_almacen');
      $sql.= ' AND c_cliente IN (SELECT c_id FROM crm_clientes WHERE c_almacen = "'.$almacen.'" ) ';
    }


    $sql.=' ORDER BY c_serie,c_nfolio DESC';
    $this->result = setq($sql) or die($sql);
}


function insertfacturablanco($cliente,$empresa,$confirmacion=NULL,$uso,$fecha,$idcuenta,$cuentaemp=1,$fpago){

  $sqlm = 'SELECT MAX(c_id) FROM cpago';
  $resultm = setq($sqlm) ;
  list($mid) = $resultm->fetch_row();
  $mid++;

  $sql = 'INSERT INTO cpago SET
          c_id = "'.$mid.'",
          c_cliente = "'.$cliente.'",
          c_empresa = "'.$empresa.'",
          c_confirmacion = "'.$confirmacion.'",
          c_uso = "'.$uso.'",
          c_cuentac = "'.$idcuenta.'",
          c_fpago = "'.$fpago.'",
          c_cuentae = "'.$cuentaemp.'",
          c_fecha = "'.str_replace("T"," ",$fecha).'",
          c_fcreo = "'.date('Y-m-d H:i:s').'",
          c_estatus = "N"';
   setq($sql) or die($sql);

  return($mid);
}
function resultd($cpago){
    $sql = 'SELECT * FROM cpagod
            WHERE cd_cpago = "'.$cpago.'" ORDER BY cd_id ASC';
    $this->resulttd = setq($sql) or die($sql);
}

function insertcon($cpago,$idfac,$moneda,$metodo,$nparc,$impsaldoant,$imppagado,$uuid,$tipo){
  $id = busca($cpago,'cpagod','cd_cpago','MAX(cd_id)');
  $id++;

  $fac = busca($idfac,'facturas','f_estatus IN ("T","C") AND f_folio','f_id');
  $impsaldoinsoluto = $impsaldoant-$imppagado;
  if($impsaldoinsoluto < 0) die(alert_back("Error: El saldo insoluto no puede ser mayor que el saldo anterior",true));

  $sql = 'INSERT INTO cpagod SET
          cd_id = "'.$id.'",
          cd_cpago = "'.$cpago.'",
          cd_factura = "'.$fac.'",
          cd_uuid = "'.$uuid.'",
          cd_moneda = "'.$moneda.'",
          cd_metodo = "'.$metodo.'",
          cd_tipo = "'.$tipo.'",
          cd_nparc = "'.$nparc.'",
          cd_impsaldoant = "'.$impsaldoant.'",
          cd_impsaldoinsoluto = "'.$impsaldoinsoluto.'",
          cd_imppagado = "'.$imppagado.'"';
  setq($sql) or die($sql);
  return($id);
}

function updatecon($idd,$cpago,$idfac,$moneda,$metodo,$nparc,$impsaldoant,$imppagado,$uuid,$tipo){
  $fac = busca($idfac,'facturas','f_folio','f_id');
  $impsaldoinsoluto = $impsaldoant-$imppagado;
  if($impsaldoinsoluto < 0) die(alert_back("Error: El saldo insoluto no puede ser mayor que el saldo anterior",true));

  $sql = 'UPDATE cpagod SET
          cd_factura = "'.$fac.'",
          cd_moneda = "'.$moneda.'",
          cd_metodo = "'.$metodo.'",
          cd_uuid = "'.$uuid.'",
          cd_nparc = "'.$nparc.'",
          cd_tipo = "'.$tipo.'",
          cd_impsaldoant = "'.$impsaldoant.'",
          cd_impsaldoinsoluto = "'.$impsaldoinsoluto.'",
          cd_imppagado = "'.$imppagado.'"
          WHERE cd_id = "'.$idd.'" AND cd_cpago = "'.$cpago.'"';
  setq($sql) or die($sql);
}

function setimpuestos($cpago,$idcon,$retiva,$risr,$tieps,$baseiva,$triva,$baseiva0,$triva0){
  $sql = 'UPDATE cpagod SET
          cd_retiva = "'.$retiva.'",
          cd_retirs = "'.$risr.'",
          cd_retieps = "'.$tieps.'",
          cd_baseiva16 = "'.$baseiva.'",
          cd_triva16 = "'.$triva.'",
          cd_baseiva0 = "'.$baseiva0.'",
          cd_triva0 = "'.$triva0.'"
          WHERE cd_id = "'.$idcon.'" AND cd_cpago = "'.$cpago.'"';
  setq($sql);
}

function borrarart($factura, $id) {
  $sql2 = 'DELETE FROM cpagod WHERE cd_cpago="' . $factura . '" AND cd_id="' . $id . '"';
  setq($sql2) or die(mysql_error());
}

function finalcaptura($id) {
     $estatus = "P";
    $sqlupt = 'UPDATE cpago SET c_estatus = "'.$estatus.'" WHERE c_id = "' . $id . '"';
    setq($sqlupt) or die(mysql_error().'<br>'.$sqlupt);
}

function registracuentac($cliente,$banco,$numcta){
  if(busca($numcta,'cliente_cuenta','cc_cuenta','COUNT(*)') > 0){
    die(alert_back("Error: El número de cuenta ya esta previamente asignado",true));
 }

  $sql = 'INSERT INTO cliente_cuenta SET
          cc_cliente = "'.$cliente.'",
          cc_banco = "'.mb_strtoupper($banco).'",
          cc_cuenta = "'.mb_strtoupper($numcta).'"';
  setq($sql) or die($sql);

  $sqlm = 'SELECT MAX(cc_idc) FROM cliente_cuenta';
  $resultm = setq($sqlm) or die($sqlm);
  list($idc) = $resultm->fetch_row;
  return($idc);
}

function cancelarfac($ticket) {
  if(busca($ticket,'cpago','c_id','c_estatus')=="T" || busca($ticket,'cpago','c_id','c_estatus')=="Q"){

    $empresa = busca($ticket,'cpago','c_id','c_empresa');
    $foliosat =  sacarcadena('UUID="','"',file_get_contents('cfd/'.$empresa.'/cpago/xml/'.busca($ticket,'cpago','c_id','c_folio').'.xml'));
    require_once 'lib/nusoap.php';
    $parametros = array();

    $parametros['cancelacionrequest']['Usuario'] = "facturacion@tye-solutions.com";
    $parametros['cancelacionrequest']['Contrasena'] = "vfgyat38";

    $parametros['cancelacionrequest']['UUID'] = $foliosat;
    $serverURL = 'https://cfd.sicofi.com.mx/SicofiWS33';
    $serverScript = 'Digifact.asmx';

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
        die("Error: En la ".$resultado." del Folio ".$foliosat." <br> Codigo: ".$codigo."\n".$error);
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
            if (!is_dir('cfd/cpago/xml')) {
                @mkdir('cfd/cpago/xml', 0777);
            }
             if (!is_dir('cfd/cpago/xml/cancel')) {
                @mkdir('cfd/cpago/xml/cancel', 0777);
            }

        if($result[$resultado][$metodores] == "true"){
          file_put_contents('cfd/cpago/xml/cancel/'.busca($ticket,'cpago','c_id','c_folio').'.xml', $result[$resultado]['AcuseCancelacion']);
          file_put_contents("Respuiestas.txt", $result[$resultado]['AcuseCancelacion']);
          //Factura Cancelada en SAT y en Sistema
           $sql = 'UPDATE cpago SET
                  c_estatus = "C",
                  c_cancelo = "' . $_SESSION['uid'] . '",
                  c_fcancelo = "' . date('Y-m-d H:i:s') . '"
                  WHERE c_id = "' . $ticket . '"';
          setq($sql) or die($sql);
          $sintimbre = 0;
        }
        else{
          $codigo = $result[$resultado]['CodigoError'];
          $error = $result[$resultado][$cancelacor];
          $sql = 'UPDATE cpago SET
                  c_estatus = "Q",
                  c_cancelo = "' . $_SESSION['uid'] . '",
                  c_fcancelo = "' . date('Y-m-d H:i:s') . '"
                  WHERE c_id = "' . $ticket . '"';
          setq($sql) or die($sql);

        }
      }
    }
  }
  else{
    if(busca($ticket,'cpago','c_id','c_estatus')!="T"){
      ///
      $sql = 'DELETE FROM cpago WHERE c_id = "'.$ticket.'"';
      setq($sql) or die($sql);
      $sql2 = 'DELETE FROM cpagod WHERE cd_factura = "'.$ticket.'"';
      setq($sql2) or die($sql2);

        $sintimbre = 1;
    }
    return($sintimbre);
  }
}

function timbrar($id) {
    require_once 'lib/nusoap.php';
    /********************************************Inicio Timbrado T&E*************************************************************** */
    $parametros = array();
      $emp = busca($id,'cpago','c_id','c_empresa');

    $parametros['timbraRequest']['Usuario'] = "facturacion@tye-solutions.com";
    $parametros['timbraRequest']['Password'] = "vfgyat38";
    $serverURL = 'https://cfd.sicofi.com.mx/SicofiWS33';


    $mixml = file_get_contents('cfd/'.$emp.'/cpago/xml/'.busca($_GET['id'],'cpago','c_id','c_folio').'.xml');
    $parametros['timbraRequest']['XML'] = $mixml;

    $serverScript = 'Digifact.asmx';
    $metodoALlamar = 'TimbraCFDIV40';
    $cliente = new nusoap_client("$serverURL/$serverScript?WSDL", 'wsdl');
    $error = $cliente->getError();
    if ($error) {
      $sqlup = 'UPDATE folios SET c_folio = "" WHERE c_id = "'.$id.'"';
      setq($sqlup) or die($sqlup);

      die(alert_back("Error: El servidor No esta disponible para timbrar\n intente de nuevo por favor",true));
    }

  // Llamar a la función GeneraTFD del servidor
    $result = $cliente->call(
      $metodoALlamar, $parametros, "uri:$serverURL/$serverScript", "uri:$serverURL/$serverScript/$metodoALlamar"
    );

  // Analizando resultados
    if ($cliente->fault) {
      $sqlup = 'UPDATE cpago SET c_folio = "" WHERE c_id = "'.$id.'"';
      setq($sqlup) or die($sqlup);

      print_r($result);

      die(alert_back("Error de Servidor Reporte: \n".$cliente->fault,true));
    }
    else {
      $error = $cliente->getError();
      if ($error) {
        $sqlup = 'UPDATE cpago SET c_folio = "" WHERE c_id = "'.$id.'"';
        setq($sqlup) or die($sqlup);

        die(alert_back('Error #003: Lo sentimos el sertvidor sat no se encuentra disponible, favor de intentar mas tarde \n'.$error));
      }
      else {

        // Creando el archivo XML
        if($result['TimbraCFDIV40Result']['TimbreCorrecto'] == "true")
            file_put_contents('cfd/'.$emp.'/cpago/xml/'.busca($id,'cpago','c_id','c_folio').'.xml', $result['TimbraCFDIV40Result']['XML']);
        else{
             $codigo = $result['TimbraCFDIV40Result']['CodigoError'];
             $error = $result['TimbraCFDIV40Result']['Error'];
             die("Error: En el timbrado Codigo: ".$codigo."<br>Atributo ".busca($codigo,'cfdi_errores','ce_codigo','ce_atributo')."<br>".$error);
        }
        $folioinf = "P";
        $emp = busca($id,'cpago','c_id','c_empresa');

        $folion = busca($folioinf, 'foliosinf', 'f_empresa = "'.$emp.'" AND f_tipofac', 'f_folio');
        $folios = busca($folioinf, 'foliosinf', 'f_empresa = "'.$emp.'" AND f_tipofac', 'f_serie');
        $nfolion = $folion;

        $sql5 = 'UPDATE cpago SET
                    c_estatus="T",
                    c_timbro = "'.$_SESSION['uid'].'",
                    c_ftimbro = "'.date('Y-m-d H:i').'",
                    c_serie = "'.$folios.'",
                    c_nfolio = "'.$nfolion.'",
                    c_folio = "'.$folios.$folion.'"
                 WHERE c_id="' . $id  . '"';
        setq($sql5) or die(mysql_error());

        $sql = 'select * from folios where f_estatus = 1';
        $result = setq($sql);
        $row = $result->fetch_array();

        $usados = $row['f_usados'] + 1;
        $slq4 = 'UPDATE folios SET  f_usados = "' . $usados . '"
                 WHERE  f_fcontrato = "' . $row['f_fcontrato'] . '" AND f_estatus = 1';
        setq($slq4) or die(mysql_error());


      /*********************** Finaliza Facturación T&E  *******************************************/
      $empresa = busca($id,'cpago','c_id','c_empresa');
      /*********************** Inicia actualización de estatus  ***********************************/
      $folion = busca($folioinf, 'foliosinf', 'f_empresa = "'.$empresa.'" AND f_tipofac', 'f_folio');
      $nfolion = $folion;
      $folion++;

      $sqlemp = 'UPDATE foliosinf SET f_folio = "'.$folion.'"
                 WHERE f_tipofac = "'.$folioinf.'" AND f_empresa = "'.$empresa.'"';
      setq($sqlemp) or die($sqlemp);
      /*********************** Finaliza actualización de estatus  ************************************/
          // Genero PDF y envio correos
      include_once('formats/crearpdfcpago.php');
      }
    }
  }

function setfiscales($fiscales,$id){
  $sql = 'UPDATE cpago SET c_fiscales = "'.$fiscales.'" WHERE c_id = "'.$id.'"';
  setq($sql);
}

}
class ViewCompago {

function ViewCompago($model) {

  $this->model = $model;
  $this->estatus = array("N" => "Proceso de Captura", "P" => "Lista para ser Timbrada", "T" => "Timbrada", "C" => "Cancelada", "Q" => "Requiere cancelación ante SAT");
  $this->tipocambio = array("MXN" => "Pesos mexicanos", "USD" => "Dolares americanos", "EUR" => "Euros");
  $this->metodopago = array("PUE" => "Pago en una sola exhibición", "PPD" => "Pago en parcialidades");
  $this->meses = array("1"=>"Ene","2"=>"Feb","3"=>"Mar","4"=>"Abr","5"=>"May","6"=>"Jun","7"=>"Jul","8"=>"Ago","9"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dic");
  $this->tipof = array("D"=>"Factura por Tiro Directo","R"=>"Factura por Rutas de Reparto","P"=>"Factura Manual");
  $this->arraytipo = array("F"=>"Facturas","N"=>"Notas de crédito ");
}

function showcpago($factura) {
  echo '<script>
        function confirmaCancela(){
            var confirma = confirm("¿Estas seguro que deseas Cancelar la Factura?");
            if(confirma == true){
                window.location.href="?modulo=compago&accion=cancelarfac&id=' . $this->model->id . '&estatus=' . $this->model->estatus.'";
            }
        }
        function confirmaCancela5000(){
            var confirma = confirm("Por disposición del SAT no se nos permite cancelar Complemento de pago mayores a $5,000 \nSi das Clic en continuar, la Factura será cancelada en JD Invoice, pero deberás cancelarla en tu portal del SAT");
            if(confirma == true){
                window.location.href="?modulo=compago&accion=cancelarfac&id=' . $this->model->id . '&estatus=' . $this->model->estatus.'";
            }
        }
        function confirmaCancelaSAT(){
            var confirma = confirm("Confirmas que has cancelado el folio ante el SAT \nJD Invoice la dará por cancelada");
            if(confirma == true){
                document.location.href="?modulo=compago&accion=cancelarfacs&id=' . $this->model->id . '";
                document.getElementById("cancelar").disable = true;
            }
        }
        function confirmaTimbra(){
            var confirma = confirm("¿Estas seguro que deseas Timbrar el Documento?");
            if(confirma == true){
                window.location.href="?modulo=compago&accion=timbrar&id=' . $this->model->id . '";
                document.getElementById("timbrar").disabled = true;
            }
        }
      </script>';
  $botones = "";
  $atras = '<a href="?modulo=compago&accion=index">
          <button class="btn btn-warning"><i class="fa fa-arrow-left"></i>Atras</button>
        </a>';

  if ($this->model->estatus == "N") {
    if(busca($factura,'cpagod','cd_cpago','COUNT(*)') >= 1)
      $botones .= '<a href="?modulo=compago&accion=finalcaptura&id=' . $factura . '">
              <button type="button" id="aplicar" class="btn btn-success ml-1" />
            <i class="fa fa-check"></i> Finalizar captura</button></a>';

    $numrels = busca($this->model->id,'factura_relacion','fr_origen','COUNT(*)');
    if($numrels > 0){
      $badge = '<span class="badge badge-pill badge-danger">'.$numrels.'</span>';
    }
    else $badge= "";

    $botones .= '<a href="?modulo=compago&accion=adenda2&id='.$this->model->id.'" id="popup">
            <button type="button" id="relacionarfactura" class="btn bg-cyan bg-darken-3 text-white ml-1" style="background: #00CCBC;"/>
            <i class="fas fa-link" style="color: #ffffff"></i> Relacionar '.$badge.'
            </button>
          </a>';
  }
  elseif ($this->model->estatus == "P") {
    $botones .= '<a href="?modulo=compago&accion=abrir&id='.$this->model->id.'">
            <button type="button" id="editar" class="btn btn-primary ml-1" /><i class="fa fa-pen-square"></i> Editar conceptos </button>
          </a>';

    $botones .= '<button type="button" id="timbrar" onclick="javascript:confirmaTimbra()" class="btn btn-success ml-1" />
    <i class="fas fa-feather-alt"></i> Timbrar </button>';

    $botones .= '<a href="formats/previewcpago.php?id='.$this->model->id.'" target="_BLANK"><button  id="previsual" title="previsualizar" class="btn btn-danger ml-1" /><i class="fa fa-file-pdf"></i> Vista previa de PDF</button></a>';
  }
  elseif ($this->model->estatus == "T") {
    $botones .= '<a href="descargar.php?file=cfd/'.$this->model->empresa.'/cpago/xml/'.$this->model->folio.'.xml" target="_BLANK">
            <button id="xml" class="btn btn-primary ml-1" /><i class="fas fa-file-code"></i> XML</button>
          </a>';
    $botones .= '<a href="cfd/'.$this->model->empresa.'/cpago/pdf/'.$this->model->folio.'.pdf" target="_BLANK">
      <button id="previsual" title="previsualizar" class="btn btn-danger ml-1" /><i class="fa fa-file-pdf"></i> Ver PDF</button>
    </a>';
    $botones .= '<a data-fancybox data-type="ajax" data-src="popup/correocompago.php?cpago='.$this->model->id.'&act=2&rand='.rand(1,100).'" href="javascript:;">
      <button type="button" class="btn btn-secondary ml-1" data-toggle="tooltip" data-placement="top" title="Reenviar correo"><i class="fas fa-envelope-open-text"></i> Reenviar</button>
    </a>';
  }

  $botones .= '<button type="button" id="cancelar" class="btn btn-danger ml-1" onclick="javascript:confirmaCancela()"/>
        <i class="fa fa-trash"></i> Cancelar
      </button>';

  toolbar('COMPLEMENTOS', $atras, "", "", $botones);

  include_once('clientes.php');
  $modelcliente = new ModelClientes();
  $modelcliente->selectfisc($this->model->cliente,$this->model->fiscales);

  echo '<div class="mt-1 row mx-auto container-fluid sinpadding">
         <div class="col-md-2 col-sm-4 mb-5">
          <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Usar datos </div>
          <div class="bodytb text-medium">
            <form method="post" action="?modulo=compago&accion=setfiscales&id='.$this->model->id.'">';
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
      <div class="col-md-1 col-sm-4 mb-5">
        <div class=" bg-primary bg-darken-3 text-white headtb text-medium">RFC del Cliente </div>
        <div class="bodytb text-medium">';
        echo $modelcliente->rfc;
        echo '</div>
      </div>

      <div class="col-md-4 col-sm-8 mb-5">
        <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Razón Social </div>
        <div class="bodytb text-medium"><a href="?modulo=clientes&accion=fiscales&cliente='.$this->model->cliente.'" target="_BLANK">'.$modelcliente->razonsocial.'</a></div>
      </div>

      <div class="col-md-2 col-sm-6 mb-5">
        <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Correo electronico principal </div>
        <div class="bodytb text-medium">'.$modelcliente->correo1.'</div>
      </div>';

    echo '<div class="col-md-3 col-sm-6 mb-5">
            <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Regimen fiscal</div>
            <div class="bodytb text-medium"><span style="font-size: 0.6rem;">'.$modelcliente->regimen.' '.busca($modelcliente->regimen,'cfdi_regimenfiscal','cr_id','cr_nmb').'</span></div>
          </div>';
  echo '</div>';

    //Formulario oculto para agregar una cuenta bancaria
  $fechap = str_replace('T',' ',$this->model->fecha);
  echo '<form name="newcc" action="?modulo=compago&accion=registracuentac&cpago='.$this->model->id.'&cliente='.$this->model->cliente.'" method="post" >
        <input type="hidden" id="numct" name="cuenta" />
        <input type="hidden" id="bank" name="banco" />
        </form> ';

  echo '
  <form class="sinpadding row" name="setuso" action="?modulo=compago&accion=setuso&id='.$this->model->id.'" method="post">
        <div class="col-md-2 col-sm-4 mb-5">
          <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Fecha de pago </div>
          <div class="bodytb text-medium">
            <input type="datetime-local" step="1" name="fecha" value="'.$fechap.'" class="form-control" required />
          </div>
        </div>';
  echo '<div class="col-md-2 col-sm-4 mb-5">
          <div class=" bg-primary bg-darken-3 text-white headtb text-medium">No. Autorización </div>
          <div class="bodytb text-medium">
            <input type="text" size="10" class="form-control" name="confirmacion" value="'.$this->model->confirmacion.'" required />
          </div>
        </div>';
  echo '<div class="col-md-2 col-sm-4 mb-5">
          <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Forma de pago </div>
          <div class="bodytb text-medium">';
    $sql = 'SELECT cf_id,CONCAT(cf_nmb,"-",cf_descripcion) cad FROM cfdi_fpago
            WHERE cf_estatus = "A"
            ORDER BY cf_descripcion';
    $result = setq($sql) or die($sql);
    echo '<select name="formapago" onchange="submit();" class="form-control">';
    while($row = $result->fetch_array()){
      if($row['cf_id'] == $this->model->fpago) $sel = 'selected';
      else $sel = '';
      $cadena = substr($row['cad'],0,28);
      echo '<option value="'.$row['cf_id'].'" '.$sel.'>'.$cadena.'</option>';
    }
    echo '</select>';
  echo '</div>
        </div>';
  echo '<div class="col-md-2 col-sm-4 mb-5 ">
          <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Cuenta Ordenante</div>
          <div class="bodytb text-medium"><input type="text" class="form-control" name="cuentae" value="'.$this->model->cuentae.'" />
          </div>
        </div>';
  echo '<div class="col-md-2 col-sm-4 mb-5">
          <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Banco Ordenante</div>
          <div class="bodytb text-medium"><input type="text" class="form-control" name="bancoe" value="'.$this->model->bancoe.'" />
          </div>
        </div>';
/*
  echo '<div class="col-md-2 col-sm-4 mb-5">
          <div class=" bg-primary bg-darken-3 text-white headtb text-medium">Cuenta Beneficiario</div>
          <div class="bodytb text-medium"><input type="text" class="form-control" name="cuentac" value="'.$this->model->cuentac.'" />
          </div>
        </div>';
*/
  echo '<div class="col-md-2 col-sm-4 mb-5 ">
          <button type="submit" class="btn btn-primary"><i class="fas fa-redo-alt"></i> Actualizar</button>
          </div>';
  echo '</form>';

  $this->model->resultd($this->model->id);
  $total = 0;

  if ($this->model->estatus == "N") $colspan = "4";
  else $colspan = "3";

  echo '<center>';
  echo '<table width="100%" cellpadding="0" cellspacing="0">
        <tr><br>';
  echo '<td width="25%" valign="top"  class="align-top">';

  if($this->model->estatus == "N"){
    if(!isset($_GET['idd']))
      echo '<form name="setinfo" action="?modulo=compago&accion=insertcon&cpago='.$this->model->id.'" method="post">';
    else
      echo '<form name="setinfo" action="?modulo=compago&accion=updatecon&cpago='.$this->model->id.'&idd='.$_GET['idd'].'" method="post">';

  echo '<div class="table-responsive">
        <table class="table table-striped">
          <thead class="thead bg-primary text-white">
          <tr><th colspan="2">Agregar conceptos</th></tr></thead>';

      if(isset($_GET['idd'])){
        $factura = busca($this->model->id,'cpagod','cd_id = "'.$_GET['idd'].'" AND cd_cpago','cd_factura');
        $moneda = busca($this->model->id,'cpagod','cd_id = "'.$_GET['idd'].'" AND cd_cpago','cd_moneda');
        $metodo = busca($this->model->id,'cpagod','cd_id = "'.$_GET['idd'].'" AND cd_cpago','cd_metodo');
        $parcialidad = busca($this->model->id,'cpagod','cd_id = "'.$_GET['idd'].'" AND cd_cpago','cd_nparc');
        $saldoant = busca($this->model->id,'cpagod','cd_id = "'.$_GET['idd'].'" AND cd_cpago','cd_impsaldoant');
        $imppagado = busca($this->model->id,'cpagod','cd_id = "'.$_GET['idd'].'" AND cd_cpago','cd_imppagado');
        $uuid = busca($this->model->id,'cpagod','cd_id = "'.$_GET['idd'].'" AND cd_cpago','cd_uuid');
        $folio = busca($factura,'facturas','f_id','f_folio');
        $tipo = busca($this->model->id,'cpagod','cd_id = "'.$_GET['idd'].'" AND cd_cpago','cd_tipo');
      }
      else{
        $factura = "";
        $moneda = "MXN";
        $metodo = "PPD";
        $saldoant = "";
        $imppagado = "";
        $saldoinsoluto = "";
        $parcialidad = "";
        $folio = "";
        $uuid = "";
        $tipo = "";
      }
    echo '<tr>
            <th>Factura</th>
              <td>
                <input type="text" class="form-control" size="8" name="foliof" id="foliof" value="'.$folio.'" />
                <a href="eligefac.php?cpago='.$this->model->id.'&cliente='.$this->model->cliente.'&form=setinfo&input=foliof" onclick="window.open(this.href,\'window\',\'width=980\');return false">
                  <button type="button" class="btn btn-sm bg-darken-3 text-white" style="background: #626262"/><i class="fa fa-search" style="color: #ffffff"></i></button>
                </a>
              </td>
          </tr>
          <tr>
            <th>No. de Parcialidad</th>
            <td>
              <input type="number" class="form-control" id="noparc" name="pacialidad" value="'.$parcialidad.'" min="1" max="20" step="1" />
            </td>
          </tr>
          <tr>
            <th>Importe pagado</th>
            <td><input type="number" class="form-control" id="imppagado" name="importe" min="0" max="999999999" step="any" value="'.$imppagado.'" /></td>
          </tr>
          <tr>
            <th>Saldo Anterior</th>
            <td><input type="number" class="form-control" id="impsaldoant" name="saldoant" min="0" max="999999999" step="any" value="'.$saldoant.'" /></td>
          </tr>
          <tr>
            <th>Metodo de pago</th>
            <td>
              '.menu_select_array($this->metodopago,$metodo,'metodo',true).'
            </td>
          </tr>
          <tr>
            <th>Moneda</th>
            <td style="text-align:center;"><input type="text" class="form-control" size="5" name="moneda" value="'.$moneda.'" /></td>
          </tr>';
    echo '<tr><td colspan="2"><input type="text" class="form-control" size="35" id="iduuid" name="uuid" value="'.$uuid.'" value="" placeholder="UUID" required /></td></tr>';
    if(!$tipo || $tipo == "F"){  $cheff = "checked"; $chefn = ""; } else{ $cheff = ""; $chefn = "checked"; }
    echo '<tr>
          <center>
            <td colspan="2">
              <label class="display-inline-block custom-control custom-radio ml-1">
                <input type="radio" class="custom-control-input" name="tipo" value="F" '.$cheff.' />
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description ml-0">Factura</span>
              </label>

              <label class="display-inline-block custom-control custom-radio ml-1">
                <input type="radio" class="custom-control-input" name="tipo" value="n" '.$chefn.' />
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description ml-0">Nota de crédito</span>
              </label>
            </td>
            </center>
          </tr>';

    echo '<tr><td colspan="2">
            <center><button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i></button></center>
          </td></tr>';
    echo '</table></form>';
      }
      elseif($this->model->estatus == "T" || $this->model->estatus == "C"){
        $version =  sacarcadena('" version="','"',file_get_contents('cfd/'.$this->model->empresa.'/cpago/xml/'.$this->model->folio.'.xml'));
        $fechatimbrado = sacarcadena('FechaTimbrado="','"',file_get_contents('cfd/'.$this->model->empresa.'/cpago/xml/'.$this->model->folio.'.xml'));
        $fechapago = sacarcadena('FechaPago="','"',file_get_contents('cfd/'.$this->model->empresa.'/cpago/xml/'.$this->model->folio.'.xml'));
        $uuids = sacarcadena('Version="1.1" UUID="','"',file_get_contents('cfd/'.$this->model->empresa.'/cpago/xml/'.$this->model->folio.'.xml'));

        echo '
        <table class="'.$lista.'" width="90%">
              <thead><tr><th colspan="2">Información de Factura</th></tr></thead>
              <tr>
                <td>'.$this->model->folio.'</td>
              </tr><tr>
                <td><span style="font-size: 9pt">'.$uuids.'</span></td>
              </tr>
              <tr>
                <td><span style="font-size: 9pt"><b>Fecha de pago</b><br>'.$fechapago.'</span></td>
              </tr>
              ';

        echo '<tr>
                  <td><b>Fecha de Timbrado</b><br>'.date('d-m-Y h:i:s',strtotime($fechatimbrado)).'</td>
                </tr>

              </table>';

      }

        echo '</td><td width="75%" valign="top" class="align-top">';

        echo '<div class="table-responsive">
        <table width="' . $width . '%"  cellpadding="0" cellspacing="0" class="table table-stripped">
        <thead class="thead-primary bg-primary text-white">
        <tr>';

        if ($this->model->estatus == "N"){
            echo '<th style="padding-left: 10px ">Borrar</th>';
        }
        echo '<th>UUID</th>';
        echo '<th width="15%">Metodo</th>
              <th width="10%">Moneda</th>
              <th width="10%">No.<br>Parc</th>
              <th width="15%">Importe<br>Pagado</th>';
        echo '<th width="15%">Saldo<br>Anterior</th>
              <th width="15%">Saldo<br>Insoluto</th>
              <th width="10%">Documento</th>';
        echo '</tr></thead><tbody>';
        $subtotal = 0;

$total = 0;

    while ($row = $this->model->resulttd->fetch_array()) {
      if($row['cd_tipo'] == "F")
        $total+=$row['cd_imppagado'];
      else
        $total-=$row['cd_imppagado'];

      echo '<tr>';
      if ($this->model->estatus == "N"){
        echo '<td>
                <a href="?modulo=compago&accion=borrarart&cpago='.$this->model->id.'&id='.$row['cd_id'].'">
                  <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                <a/>
                <br>
                <a href="?modulo=compago&accion=showcpago&cpago='.$this->model->id.'&idd='.$row['cd_id'].'">
                    <button class="btn btn-info"><i class="fa fa-pen"></i></button>
                <a/>
              </td>';
      }
      echo '<td>'.$row['cd_uuid'].'</td>';
      echo '<td>'.$this->metodopago[$row['cd_metodo']].'</td>';
      echo '<td>'.$row['cd_moneda'].'</td>';
      echo '<td>'.$row['cd_nparc'].'</td>';
      echo '<td>'.number_format($row['cd_imppagado'],2).'</td>';
      echo '<td>'.number_format($row['cd_impsaldoant'],2).'</td>';
      echo '<td>'.number_format($row['cd_impsaldoinsoluto'],2).'</td>';
      if($row['cd_tipo'] == "F") $doc = "Factura ".busca($row['cd_factura'],'facturas','f_id','f_folio'); else $doc = "Nota de crédito";
      echo '<td>'.$doc.'</td>';
      echo '</tr>';
    }
    echo '<tr><td colspan="' . $colspan . '">&nbsp;</td>';
    echo '<tr><td colspan="' . $colspan . '">&nbsp;</td><td style="text-align:right;"><b>Total</td><td style="text-align:right;">$ ' . number_format($total,2) . '</td></tr>';
    echo '</table></div></td></tr></table>';
    echo '</center>';
  }

    function browsecpago($cliente,$fini,$ffin,$estatus,$folio,$empresa){
 //Sección: E1 Encabezado - Botones de acción
    //Sección: E2 Encabezado - Filtros
    $nuevo= '<a accesskey="N" href="popup/setcompago" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
        <button type="button" class="btn btn-primary bg-darken-1 text-white" ><i class="fa fa-plus"></i> Nuevo</button>
      </a>';

    echo '<form name="setfacliente" method="post" action="?modulo=compago&accion=nuevafac">
            <input type="hidden" id="cliente" name="cliente" />
            <input type="hidden" id="fiscales" name="fiscales" />
            <input type="hidden" id="empresa" name="'.$empresa.'" />
            <input type="hidden" id="version" name="version" value="2.0" />
          </form>';
  
    $filtro ='
          <form class="form-inline" role="form" method="post" action="?modulo=compago&accion=index">
            <div class="mb-5 mr-1">
              <label for="tipom">Cliente</label>
              <input type="text" class="form-control" name="cliente" placeholder="Nombre del cliente que buscas" onfocus="this.select();" value="'.$cliente.'" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Folio</label>
              <input type="text" class="form-control" name="folio" placeholder="Folio del complemento" onfocus="this.select();" value="'.$folio.'" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Desde</label>
              <input type="date" class="form-control" name="fini" value="'.$fini.'" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Hasta</label>
              <input type="date" class="form-control" name="ffin" value="'.$ffin.'" />
            </div>

            <div class="mb-5 mr-1">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value="" '.$sela.' >Todos</option>
                <option value="N" '.$seln.' >Proceso de Captura</option>
                <option value="P" '.$selp.' >Lista para timbrar</option>
                <option value="T" '.$selt.' >Timbrada</option>
                <option value="Q" '.$selq.' >Solicitud de cancelación</option>
                <option value="C" '.$selc.' >Cancelados</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <button type="submit" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';

    toolbar("COMPLEMENTOS", "", $filtro, $nuevo);

  echo '<div class="table-responsive mt-5">
          <table class="table table-striped" id="myTable">
            <thead>';
  echo '<tr>
          <th width="5%">Folio</th>
            <th width="25%">Cliente</th>
            <th width="30%">Razón Social</th>
            <th width="15%">Fecha</th>
            <th width="8%">Estatus</th>
            <th>Reenviar</th>
            <th>Descargar</th>
            <th width="7%">Total</th>
        </tr>
        </thead>
        <tbody>';
    while($row = $this->model->result->fetch_array()){

       if($row['c_estatus'] == "N") { $estatus = "Proceso de captura"; $colorf = "#FFDC52"; $divid = '<button type="button" class="btn btn-primary"><i class="fa fa-pen"></i></button>'; }
       if($row['c_estatus'] == "P") { $estatus = "Por timbrar"; $colorf = "#FF9900"; $divid = '<button type="button" class="btn btn-primary"><i class="fa fa-pen"></i></button>'; }
       elseif($row['c_estatus'] == "T") { $estatus = "Timbrada"; $colorf = "#84E184";  $divid = '<button type="button" id="editar" class="btn btn-primary">'.$row['c_folio'].'</button>';}
       elseif($row['c_estatus'] == "C") { $estatus = "Cancelada"; $colorf = "#FF7A7A";  $divid = '<button type="button" id="editar" class="btn btn-primary">'.$row['c_folio'].'</button>';}
       elseif($row['c_estatus'] == "Q") { $estatus = "Requiere cancelación"; $colorf = "#CC9900";  $divid = '<button type="button" id="editar" class="btn btn-primary">'.$row['c_folio'].'</button>';}

       if($row['c_estatus'] == "T" || $row['c_estatus'] == "C") $fecha = $row['c_ftimbro'];
       else $fecha = $row['c_fcreo'] ;

       echo '<tr>
            <td><a href="?modulo=compago&accion=showcpago&cpago='.$row['c_id'].'">

              '.$divid.'

            </a></td>';
       echo '
            <td>'.busca($row['c_cliente'],'crm_clientes','c_id','c_nmb').'</td>
            <td>'.busca($row['c_fiscales'],'crm_fiscales','cf_id','cf_razonsocial').'</td>
            <td>'.cambiar_fecha(date('Y-m-d',strtotime($fecha))).'<br>a las '.date('H:i:s',strtotime($fecha)).'</td>
            <td style="background:'.$colorf.';">'.$estatus.'</td>';

      if($row['c_estatus'] != "N" && $row['c_estatus'] != "P"){
        if($row['c_estatus'] == "Q"){
          if($row['c_uuid'] == "NULL") getuuid($row['f_id']);
            echo '<td>'.$row['c_uuid'].'</td>';
          }
        else
          echo '<td>
                  <a data-fancybox data-type="ajax" data-src="popup/correocompago.php?cpago='.$row['c_id'].'&act=2&rand='.rand(1,100).'" href="javascript:;">
                    <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Nueva actividad"><i class="fas fa-envelope-open-text"></i> Reenviar</button>
                  </a>
                </td>';
        //Descargar XML y PDF
        if($row['c_estatus'] != "C"){
          if(file_exists('cfd/'.$this->model->empresa.'/cpago/pdf/'.$row['c_folio'].'.pdf'))
            echo '<td><a href="cfd/'.$this->model->empresa.'/cpago/pdf/'.$row['c_folio'].'.pdf" target="_BLANK">
                      <button class="btn btn-danger"><i class="fa fa-file-pdf"></i> PDF</button></a></td>';
          else
            echo '<td><a href="formats/recreapdfcpago.php?id='.$row['c_id'].'" target="_BLANK">
                    <button class="btn btn-danger"><i class="fa fa-file-pdf"></i> PDF</button></a>
                  </td>';
        }
        else{
          echo '<td><a href="formats/recreapdfcpago.php?id='.$row['c_id'].'" target="_BLANK">
                  <button class="btn btn-danger"><i class="fa fa-file-pdf"></i> PDF</button></a>
                </td>';
        }
        echo '<td><a href="descargar.php?file=cfd/cpago/xml/'.$row['c_folio'].'.xml" target="_BLANK">
          <button class="btn btn-info text-white"><i class="fas fa-file-code"></i> XML</button></a></td>';
        }
      else{
        echo '<td>&nbsp;</td>';
        echo '<td>&nbsp;</td>';
      }
     //Descargar XML y PDF
     $total = busca($row['c_id'],'cpagod','cd_cpago','SUM(cd_imppagado)');
     echo '<td>'.number_format($total,2).'</td>';
     echo '</tr>';
   }

      echo '</tbody></table></center></div>';
      
      echo '<script>
      $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          responsivePriority: 1,
          pageLength: 50,  
      });
      </script>';

      if(isset($_GET['aviso'])) echo alert_back("Presione F5 para conocer el estatus de su factura");
    }

    function emitir($clientepost,$nota,$empresa) {
       echo '<div class="div2" id="bboton01">
                <center><input type="button" id="atras" class="botonb" onclick="javascript:history.back();"></center>
        </div></div>';

        if(!$nota) $leyendafac = "Seleccione al cliente para Comprobante de pago";
        else $leyendafac = "Seleccione al cliente para la nota de credito";

      if($empresa == "1") $lista = 'lista';
      elseif($empresa == "2")  $lista = 'lista3';
      else  $lista = 'lista4';
    $con4 = 'SELECT c_alias,c_nmb
             FROM clientes WHERE c_estatus="A" ORDER BY c_alias';  //consulta para seleccionar las palabras a buscar, esto va a depender de su base de datos
    $query1 = setq($con4) or die($con4);
    ?>
      <script>
        $(function() {
          <?php
            while ($row9 = $query1->fetch_array()) {//se reciben los valores y se almacenan en un arreglo
                $elementos1[] = '"' . $row9['c_nmb'] . '"';
            }
            $arreglo1 = implode(", ", $elementos1); //junta los valores del array en una sola cadena de texto
          ?>

          var availableTags = new Array(<?php echo $arreglo1; ?>);//imprime el arreglo dentro de un array de javascript
          $("#tags1").autocomplete({
            source: availableTags
          });
        });
      </script>
    <?php
      $nmbcliente = busca($clientepost,'clientes','c_id','c_alias');
        echo '<center><form method="post">';
        echo '<table width="50%" class="lista"><thead><tr><td colspan="12"><br></td></tr><tr><th id="mtotal" colspan="2">Seleccionar un cliente para emisión de factura</th>';
        echo '<tr>
                    <td><label for="tags1"><input id="tags1" type="text" size="30" value="'.$nmbcliente.'" name="cliente" placeholder="Nombre" required onkeydown ="var key = window.event.keyCode;if(key==13){  submit() }" autofocus onfocus="this.select();"></td>
              </tr><tr>
                <td colspan="2"><input type="submit" id="actualizar" value=" " class="botont" /></td>
              </tr>
              </thead>
            </table></form></div></center>';
        if ($this->model->cliente) {
            include 'clientes.php';
            $modelcliente = new modelclientes();
            $modelcliente->select($this->model->cliente);
            $viewcliente = new viewclientesd($modelcliente);
            $viewcliente->show($clientepost);

              if(isset($_GET['remision'])){
                echo '<input type="hidden" name="remision" value="'.$_GET['remision'].'">';
              }

              echo '<center>
              <form autocomplete="off" action="?modulo=compago&accion=nuevafac&cliente=' . $this->model->cliente . '" method="post">
              <table class="lista" width="90%"><thead><tr><th colspan="4">Modificar información de Comprobante</th></tr>
                  <tr>
                      <th>Elegir Empresa</th>
                      <th>Fecha y hora de autorización</th>
                      <th>Folio de autorización</th>
                      <th>Uso del CFDI</th>
                  </tr>
              </thead>';
              echo '<tr>
                      <td>Empresa'.menu_select_db('configuracion','c_consecutivo','c_nmb',$empresa,'empresa','XXX', false, false,false, false, false,false,false,1).'</td>
                      <td><input type="datetime-local" step="1" name="fecha" value="'.date('Y-m-d').'T'.date('H:i:s').'" required autofocus /></td>
                      <td data-tooltip="Se puede registrar el número de cheque, número de autorización, número de referencia, clave de rastreo en caso de ser SPEI, línea de captura o algún número de referencia" class="tooltip-top">
                        <input type="text" name="confirmacion" placeholder="Número de confirmación bancaría" size="30" required autofocus /></td>
                      <td>'.menu_select_db('cfdi_uso','cu_id','cu_nmb',"P01",'uso','cu_id = "P01"', false, false, "S", true).'</td>
              ';

    echo '<tr>
            <th>Cuenta Bancaría del cliente</th>
            <th>Forma de pago</th>
            <th colspan="2">¿Es correcta la información del cliente?</th></tr>
          <tr>
            <td rowspan="2">';
    $sql = 'SELECT * FROM cliente_cuenta WHERE cc_cliente = "'.$this->model->cliente.'" ORDER BY cc_idc';
    $result = setq($sql) or die($sql);
    $resultn = setq($sql) or die($sql);
    $numc = mysql_num_rows($resultn);

    echo '<script>
            function setcuenta(){
              var cta = document.getElementById("cuentac").value;
              document.getElementById("idcuenta").value = cta;
            }
          </script>';
    echo '<input type="hidden" id="idcuenta" name="idcuenta" value=""  />';
    if($numc == 0){
      echo 'Es necesario registrar una cuenta del cliente<br>';
      echo 'Banco Emisor <input type="text" name="banco" /><br>';
      echo 'Número de cuenta <input type="text" name="numcta" placeholder="Entre 10 y 50 caracteres" />';
    }
    else{
      echo '<select name="cuenta" id="cuentac" onchange="setcuenta();">';
      echo '<option value="">Omitir cuenta o Registrar una cuenta nueva</option>';
     while($row = $result->fetch_array()){
          echo '<option value="'.$row['cc_idc'].'" '.$sele.'>'.$row['cc_banco'].' - '.$row['cc_cuenta'].'</option>';
      }
      echo '</select>';
    }
    echo '</td>
          <td rowspan="2">';

    $sql = 'SELECT fd_id,CONCAT(cf_nmb,"-",cf_descripcion) cad
            FROM fpagod INER JOIN cfdi_fpago ON cf_id = fd_id
            ORDER BY cf_nmb';
    $result = setq($sql) or die($sql);

    echo '<select name="formapago" required >';
    while($row = $result->fetch_array()){
      if($row['fd_id'] == "03") $sel = 'selected';
      else $sel = '';

      $cadena = substr($row['cad'],0,28);

      echo '<option value="'.$row['fd_id'].'" '.$sel.'>'.$cadena.'</option>';
    }
    echo '</select>';
    echo '</td>
            <th>Si</th>
            <th>No</th>
          </tr>';
    echo '<td><a><input type="submit" id="aplicar" class="botont" value="" autofocus/></a></td>
          <td><a href="?modulo=clientes&accion=index&id=' . $this->model->cliente . '"><input type="button" id="editar" value=" " class="botont" /></a></td></tr>
         </table></center></form>';
    }
  }
}

?>