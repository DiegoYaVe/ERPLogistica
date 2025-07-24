<?php
//ini_set('display_errors', 1);
date_default_timezone_set("America/Guatemala");

function compruebampop($idop){
  $datos = array();
  $suma = array();
  $datos['error'][0] = 'OK';
  $sqlop = 'SELECT po_articulo, po_modelo, po_cantidad, po_almacen FROM pr_ordenprod WHERE po_id = "'.$idop.'"';
  $resultop = setq($sqlop);
  list($articulo, $modelo, $cantidad, $almacen) = $resultop -> fetch_array();
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

  return $datos;
}
function traealmacenusuario($id){
  $almacen = busca($id,'usuarios','u_id','u_almacen');
  return $almacen;
}
function articulosdisponibles($almacen, $articulo, $modelo = NULL){
  $datosla = existenciaxestatus($almacen, "LA");
  $datosva = existenciaxestatus($almacen, "VA");
  //$datosvp = existenciaxestatus($almacen, "VP");
  if($modelo) $existencia = existenciaModelo($articulo, $almacen, $modelo);
  else $existencia = existencia($articulo, $almacen);
  if(!$datosla[$articulo][$modelo]) $datosla[$articulo][$modelo] = 0;
  $libre_apartado = $datosla[$articulo][$modelo];
  $libre = $existencia;
  if(floatval($libre) < 1) $libre = 0;
  if(!$datosva[$articulo][$modelo]) $datosva[$articulo][$modelo] = 0;
  $vendido_abonado = $datosva[$articulo][$modelo];
  $disponibles = $libre + $libre_apartado + $vendido_abonado;
  //echo 'Libre: '.$libre.' apartado: '.$libre_apartado.' abonado: '.$vendido_abonado.' existencia: '.$existencia.' disponibles: '.$disponibles;
  return $disponibles;
}
function existenciaxestatus($almacen, $estatus){
  $datos = array();
  
  if($estatus == "LA"){ //Libre-apartado
    $sqlla = 'SELECT ccc_articulo AS articulo, ccc_modelo AS modelo FROM crm_cotizaciones INNER JOIN crm_cotizacionesc ON ccc_cotizacion = cc_id INNER JOIN crm_tableros ON ct_id = cc_tablero 
                        WHERE ct_nnegociacion IN ("2", "3")  AND cc_estatus IN ("A", "V") AND cc_almacen = "'.$almacen.'"';

  } else if($estatus == "VA"){ //Vendido-abonado
    $sqlla = 'SELECT rc_articulo AS articulo, rc_modelo AS modelo FROM remisiones INNER JOIN remisionesc ON rc_remision = r_id INNER JOIN cxcobrar ON cx_referencia = r_id 
                      WHERE r_estatus = "A" AND cx_estatus IN ("A") AND rc_estatus != "F" AND r_almacen = "'.$almacen.'"';

  } else if($estatus == "VP"){ //Vendido-pagado
    $sqlla = 'SELECT rc_articulo AS articulo, rc_modelo AS modelo FROM remisiones INNER JOIN remisionesc ON rc_remision = r_id INNER JOIN cxcobrar ON cx_referencia = r_id
                WHERE r_estatus = "F" AND cx_estatus = "F" AND rc_estatus != "F"  AND r_almacen = "'.$almacen.'"';
  }

  // echo $sqlla.'<br>';
  $resultla = setq($sqlla);
  
  while($row = $resultla -> fetch_array()){
    if(!isset($datos[$row['articulo']][$row['modelo']])) $datos[$row['articulo']][$row['modelo']] = 0;
    $datos[$row['articulo']][$row['modelo']]++;
  }
  //echo $sqlla.'<br>';
  /* echo 'datos: '.$estatus.' - '.json_encode($datos).'<br>';
  echo 'datos: '.$estatus.' - '.$datos["11"][""].'<br>'; */
  //echo json_decode(json_encode($datos));
  return $datos;
}
function validaestatus($accion){
  if($accion == "corte"){
    $sql = 'SELECT * FROM cortes INNER JOIN cuentas ON cu_id = c_cuenta WHERE c_ffin < "' . date('Y-m-d') . '"';
    $result = setq($sql);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_array()) {
        $sql2 = 'UPDATE cortes SET c_estatus = "I" WHERE c_id = "' . $row['c_id'] . '"';
        setq($sql2);
      }
    }
    include('modulos/cuentas.php');
    $cuent = new modelcuentas();
    $sql = 'SELECT * FROM cuentas WHERE cu_estatus = "A"';
    $result = setq($sql);
    while ($row = $result->fetch_array()) {
      //echo 'cuenta: '.$row['cu_id'].'<br>';
      $okcta = $cuent->cuentaactiva($row['cu_id']);
      if ($okcta == "NOTOK") {
        $ant = busca($row['c_id'], 'cortes', 'c_cuenta', 'COUNT(*)');
        if ($ant != 0) {
          $idcorte = getmax('c_id', 'cortes');
          $sqlpas = 'SELECT c_saldo, c_id FROM cortes WHERE c_ffin = "' . date('Y-m-t', strtotime('last month')) . '" AND c_cuenta = "1"';
          $resultpas = setq($sqlpas);
          list($saldopas, $cortepas) = $resultpas->fetch_array();
          $sqlsaldo = 'SELECT cd_id, cd_tipo, cd_monto FROM cortesd INNER JOIN cortes ON cd_corte = c_id WHERE c_cuenta = "1" AND cd_corte = "' . $cortepas . '"';
          $result = setq($sqlsaldo);
          while ($row = $result->fetch_array()) {
            if ($row['cd_tipo'] == "C")
              $saldopas += $row['cd_monto'];
            else if ($row['cd_tipo'] == "P")
              $saldopas -= $row['cd_monto'];
          }
          $sql = 'INSERT INTO cortes SET c_id = "' . $idcorte . '",
                                        c_fini = "' . date('Y-m-d', strtotime('first day of this month')) . '",
                                        c_ffin = "' . date('Y-m-d', strtotime('last day of this month')) . '",
                                        c_cuenta = "'.$row['cu_id'].'",
                                        c_estatus = "A",
                                        c_saldo= "' . $saldopas . '"';
        } else {
          $idcorte = getmax('c_id', 'cortes');
          $sql = 'INSERT INTO cortes SET c_id = "' . $idcorte . '",
                                      c_fini = "' . date('Y-m-d', strtotime('first day of this month')) . '",
                                      c_ffin = "' . date('Y-m-d', strtotime('last day of this month')) . '",
                                      c_cuenta = "'.$row['cu_id'].'",
                                      c_estatus = "A",
                                      c_saldo= "0"';

        }
        setq($sql);
        $okcta = $idcorte;
      }
    }
  } else if($accion == "articulos"){
    $sql = 'SELECT * FROM articulos WHERE a_estatus = "A" AND a_expira < "'.date('Y-m-d').'" AND a_expira != "0000-00-00"';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $sqlupd = 'UPDATE articulos SET a_estatus = "I" WHERE a_id = "'.$row['a_id'].'"';
      setq($sqlupd);
    }
  } else if($accion == "cotizaciones"){
    $sql = 'SELECT * FROM crm_cotizaciones WHERE cc_estatus NOT IN ("V", "A", "C") AND cc_ffin < "'.date('Y-m-d').'"';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $sqlupd = 'UPDATE crm_cotizaciones SET cc_estatus = "C" WHERE cc_id = "'.$row['cc_id'].'"';
      setq($sqlupd);
    }
  } else if($accion == "remisiones"){
    $sql = 'SELECT * FROM remisiones WHERE DATE(r_fliquidacion) < "'.date('Y-m-d').'" AND r_estatus NOT IN ("F", "FE", "C")';
    $result = setq($sql); 
    while($row = $result -> fetch_array()){
      include_once('modulos/remisiones.php');
      $remm = new modelremisiones();
      $remm -> cancelar($row['r_id'], 'Se supero la fecha de liquidación', '1');
    }
  } else if($accion == "saldos"){
    $sql = 'SELECT * FROM penalizaciones INNER JOIN cxcobrar ON cx_referencia = p_remision WHERE DATE(p_fcaduca) < "'.date('Y-m-d').'" AND cx_tipo = "S"';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $sqlupd = 'UPDATE cxcobrar SET cx_estatus = "F" WHERE cx_id = "'.$row['cx_id'].'"';
      setq($sqlupd);
    }
  }
}

function toolbar($modulo, $atras = '', $filtro = '', $nuevo = '', $otro = '')
{
  if ($filtro != '') {
    $botonfiltro = '
    <a href="#" class="btn btn-sm btn-flex btn-info btn-active-primary fw-bolder" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
    <!--begin::Svg Icon | path: icons/duotune/general/gen031.svg-->
    <span class="svg-icon svg-icon-5 svg-icon-gray-500 me-1">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z" fill="black" />
      </svg>
    </span> Filtrar
    </a>
    <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_61484bf44d957">
      <div class="px-7 py-5">
        <div class="fs-5 text-dark fw-bolder">Filtrar Por</div>
      </div>
      <div class="separator border-gray-200"></div>
      <div class="px-7 py-5">
        ' . $filtro . '
      </div>
      </div>
    
    ';

  } else
    $botonfiltro = NULL;
  echo '
  <div class="toolbar" id="kt_toolbar">
    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
      <div class="d-flex align-items-center py-1 flex-stack">
        <h1 class="d-flex align-items-center text-dark fw-bolder fs-3 my-1">' . clearvmayus($modulo) . '
        <span class="h-20px border-gray-200 border-start ms-3 mx-2"></span></h1>
        <div>
          ' . $atras . '

          ' . $nuevo . '

          ' . $otro . '
        </div>
      </div>
      <div class="me-4">
      ' . $botonfiltro . '
      </div>
    </div>
  </div> 
  ';
}
function foreachdie()
{
  foreach ($_POST as $campo => $valor) {
    echo "POST->" . $campo . "= " . $valor . '<br>';
  }
  foreach ($_GET as $campo => $valor) {
    echo "GET->" . $campo . "= " . $valor . '<br>';
  }
  foreach ($_FILES as $key => $file) {
    $name = $file["name"];
    $type = $file["type"];
    $tmp_name = $file["tmp_name"];
    $error = $file["error"];
    $size = $file["size"];

    echo "Key: $key<br>";
    echo "Name: $name<br>";
    echo "Type: $type<br>";
    echo "Temporary Name: $tmp_name<br>";
    echo "Error: $error<br>";
    echo "Size: $size<br><br>";
  }
  die();
}
function setq($sql, $die = false)
{
  $dbuser = "root";
  $dbpass = "";
  $dbhost = "localhost";
  $db = "inflalandia_fabricai";
  $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
  $mysqli->query("SET CHARACTER SET utf8");
  $mysqli->query("SET NAMES utf8");

  if ($die)
    die($sql);

  $result = $mysqli->query($sql);
  $mysqli->close();

  return ($result);
}


function setqemojis($sql, $die = false)
{ //Realizar una consulta a BD en primer nivel
  $dbuser = "root"; // El usuario
  $dbpass = ""; // El Pass

  // $dbuser = "root"; // El usuario
  // $dbpass = ""; // El Pass

  $dbhost = "localhost"; // El host
  $db = "inflalandia_fabricai"; // Nombre de la base
  $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
  $mysqli->query("SET CHARACTER SET utf8mb4");
  $mysqli->query("SET NAMES utf8mb4");

  if ($die)
    die($sql);
  $result = $mysqli->query($sql);
  $mysqli->close();

  return ($result);

}

function setq1($sql, $die = false)
{ //Realizar una consulta a BD en segundo nivel (anidación)
  $dbuser = "root"; // El usuario
  $dbpass = ""; // El Pass

  // $dbuser = "root"; // El usuario
// $dbpass = ""; // El Pass

  $dbhost = "localhost"; // El host
  $db = "inflalandia_fabricai"; // Nombre de la base
  $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
  $mysqli->query("SET CHARACTER SET utf8");

  if ($die)
    die($sql);
  $resultu = $mysqli->query($sql);
  $mysqli->close();
  return ($resultu);
}

function setq2($sql, $die = false)
{ //Realizar una consulta a BD en tercer nivel (anidación)
  $dbuser = "root"; // El usuario
  $dbpass = ""; // El Pass

  // $dbuser = "root"; // El usuario
// $dbpass = ""; // El Pass

  $dbhost = "localhost"; // El host
  $db = "inflalandia_fabricai"; // Nombre de la base
  $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
  $mysqli->query("SET CHARACTER SET utf8");

  if ($die)
    die($sql);
  $resultr = $mysqli->query($sql);
  $mysqli->close();
  return ($resultr);
}

function validatae($fini, $ffin)
{
  $host = 'localhost';
  $user = 'tyesolut_taecel';
  $pass = '';
  $db = 'tyesolut_recargas';
  $mysqli = mysqli_init();
  $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 600);
  $mysqli->real_connect($host, $user, $pass, $db);
  if ($mysqli->ping()) {
    $sqlct = 'SELECT DISTINCT(cr_codigo) FROM creditos
      WHERE cr_admin = "0" AND (cr_estatus = "A" OR cr_estatus = "F") ORDER BY cr_id ASC';
    $resultct = $mysqli->query($sqlct) or die($sqlct);
    while ($rwc = $resultct->fetch_array()) {
      $sqld = 'SELECT *,MAX(cr_monto) FROM creditos
          WHERE cr_codigo = "' . $rwc['cr_codigo'] . '" GROUP BY cr_codigo';
      $resultd = $mysqli->query($sqld) or die($sqld);
      $rwd = $resultd->fetch_array();

      $sqlm = 'SELECT MAX(t_id) FROM tae';
      $resultm = setq($sqlm) or die($sqlm);
      list($idt) = $resultm->fetch_array();
      $idt++;

      $sqlia = 'INSERT INTO tae SET
          t_id = "' . $idt . '",
          t_cliente = "' . $rwd['cr_cliente'] . '",
          t_fecha = "' . $rwd['cr_fecha'] . '",
          t_hora = "' . $rwd['cr_hora'] . '",
          t_user = "' . $rwd['cr_user'] . '",
          t_idcr = "' . $rwd['cr_id'] . '",
          t_importe = "' . $rwd['cr_monto'] . '",
          t_estatus = "N"
          ';
      setq($sqlia) or die($sqlia);

      $sqlut = 'UPDATE creditos SET cr_admin = "1" WHERE cr_codigo = "' . $rwc['cr_codigo'] . '"';
      $mysqli->query($sqlut) or die($sqlut);
    }
  }

  $sql = 'SELECT COUNT(*) FROM tae WHERE t_estatus = "N"
          AND t_fecha BETWEEN "' . $fini . '" AND "' . $ffin . '" ORDER BY t_fecha ASC ';
  $resultae = setq($sql) or die($sql);
  list($numtae) = $resultae->fetch_array();
  if (!$numtae)
    $numtae = 0;

  return ($numtae);
}
function validatableros()
{
  $sql = 'UPDATE crm_tableros SET ct_estatus = "X" WHERE ct_fcierre < "' . date('Y-m-d') . '" AND ct_estatus  IN ("N","P","G","V")';
  setq($sql);
}
function RandomString($length = 10, $uc = TRUE, $n = TRUE, $sc = FALSE)
{

  $source = 'abcdefghijklmnopqrstuvwxyz';
  if ($uc == 1)
    $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  if ($n == 1)
    $source .= '1234567890';
  if ($sc == 1)
    $source .= '|@#~$%()=^+[]{}-_';
  if ($length > 0) {
    $rstr = "";
    $source = str_split($source, 1);
    for ($i = 1; $i <= $length; $i++) {
      mt_srand((double) microtime() * 1000000);
      $num = mt_rand(1, count($source));
      $rstr .= $source[$num - 1];
    }
  }
  return $rstr;
}
function cadena_aleatoria($ancho = 8, $numeros = true, $minusculas = true, $mayusculas = true, $signos = true)
{
  $permitted_chars = '';
  if ($numeros)
    $permitted_chars .= '1234567890';
  if ($minusculas)
    $permitted_chars .= 'abcdefghijklmnopqrstuvwxyz';
  if ($mayusculas)
    $permitted_chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  if ($signos)
    $permitted_chars .= '#$%&/()¿?¡!+{}-._';

  $input_length = strlen($permitted_chars);
  $random_string = '';
  for ($i = 0; $i < $ancho; $i++) {
    $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
    $random_string .= $random_character;
  }
  $random_string = utf8_decode($random_string);

  return $random_string;
}


function url_completa($forwarded_host = false)
{
  $ssl = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
  $proto = strtolower($_SERVER['SERVER_PROTOCOL']);
  $proto = substr($proto, 0, strpos($proto, '/')) . ($ssl ? 's' : '');
  if ($forwarded_host && isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
  } else {
    if (isset($_SERVER['HTTP_HOST'])) {
      $host = $_SERVER['HTTP_HOST'];
    } else {
      $port = $_SERVER['SERVER_PORT'];
      $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
      $host = $_SERVER['SERVER_NAME'] . $port;
    }
  }
  $request = $_SERVER['REQUEST_URI'];
  return $proto . '://' . $host . $request;
}
function redirect($url)
{
  ?>
  <script>
    document.location.href = "<?php echo $url; ?>";
  </script>
  <?php
}

function busca($target, $tabla, $id, $nmb)
{
  $sql = "SELECT $nmb FROM $tabla WHERE $id=\"$target\"";
  $result = setq($sql) or die($sql . ' <br>' . mysql_error());
  list($busca) = $result->fetch_row();
  return $busca;
}

function busca2($target, $tabla, $id, $nmb)
{
  $sql = "SELECT $nmb FROM $tabla WHERE $id=\"$target\"";
  die($sql);  
}

function real_scape($sql)
{
  $dbuser = "root"; // El usuario
  $dbpass = ""; // El Pass

  $dbhost = "localhost"; // El host
  $db = "inflalandia_fabricai"; // Nombre de la base
  $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $db);
  $mysqli->query("SET CHARACTER SET utf8");

  $scaped = $mysql->real_escape_string($sql);

  return ($scaped);
}

function clearvmayus($var, $mayus = true, $comp = false)
{
  mb_internal_encoding("UTF-8");
  if ($comp)
    $simbol = array('"', "'", "#", "$", "%", "&", "/", "(", ")", "=", "?", "¡", "*", "+", "~", "^", "[", "°", "|", "{", "}", "[", "]");
  else
    $simbol = array('"', "'");
  $cambio = "";

  $newvar = str_replace($simbol, $cambio, trim($var));
  if ($mayus)
    $newvar = mb_strtoupper($newvar);

  return ($newvar);
}

function formmayus($var, $mayus = true, $comp = false)
{
  mb_internal_encoding("UTF-8");
  $simbol = array('"', "'");
  $cambio = array('\"', "\'");

  $newvar = str_replace($simbol, $cambio, trim($var));
  if ($mayus)
    $newvar = mb_strtoupper($newvar);

  return ($newvar);
}
function saldo_actual($cuenta)
{
  $corte = busca($cuenta, 'cortes', 'c_estatus="A" AND c_cuenta', 'c_id');
  if (!isset($corte)) {
    $corte = busca($cuenta, 'cortes', 'c_cuenta', 'MAX(c_id)');
  }
  $fini = busca($corte, 'cortes', 'c_id', 'c_fini');
  $ffin = busca($corte, 'cortes', 'c_id', 'c_ffin');

  $sql1 = ' AND DATE(cd_fecha) BETWEEN "' . $fini . '" AND "' . $ffin . '" ';
  $tipo = busca($cuenta, 'cuentas', 'cu_id', 'cu_tipo');
  $saldoini = busca($corte, 'cortes', 'c_id', 'c_saldo');
  $pagos = busca($corte, 'cortesd', 'cd_condonar="0" AND cd_reverso IS NULL AND cd_tipo="P" AND cd_corte', 'SUM(cd_monto)');

  if (!isset($pagos))
    $pagos = 0;
  $traspasoso = busca($cuenta, 'traspasos', 't_origencorte="' . $corte . '" AND t_origen', 'SUM(t_importe)');
  if (!isset($traspasoso))
    $traspasoso = 0;
  $traspasosd = busca($cuenta, 'traspasos', 't_destinocorte="' . $corte . '" AND t_destino', 'SUM(t_importe)');
  if (!isset($traspasosd))
    $traspasosd = 0;
  $cobros = busca($corte, 'cortesd', 'cd_condonar="0" AND cd_reverso IS NULL AND cd_tipo="C" AND cd_corte', 'SUM(cd_monto)');
  
  if (!isset($cobros))
    $cobros = 0;

  if ($tipo == "C")
    $total = $saldoini - $cobros - $traspasosd + $traspasoso + $pagos;
  else
    $total = $saldoini + $cobros + $traspasosd - $traspasoso - $pagos;
      /* echo 'corte: '.$corte.'<br>';
      if($cuenta == 1)        die($saldoini.'__CB'.$cobros.'__TD'.$traspasosd.'__TR'.$traspasoso.'__PG'.$pagos); */
  if (!$total)
    $total = 0;
  return $total;
}

function disponible($cuenta, $saldo)
{
  $limite = busca($cuenta, 'cuentas', 'cu_id', 'cu_limite');
  $tipo = busca($cuenta, 'cuentas', 'cu_id', 'cu_tipo');
  if ($tipo == "A") {
    $total = 0;
  } else if ($tipo == "D" or $tipo == "E") {
    $total = $saldo;
  } else if ($tipo == "C") {
    $total = $limite - $saldo;
  }
  return $total;
}
function saldoanterior($fecha, $cuenta)
{
  $corte = busca($cuenta, 'cortes', 'c_fini <= "' . $fecha . '" AND c_ffin >= "' . $fecha . '" AND c_cuenta', 'MAX(c_id)');
  if (!$corte)
    $corte = busca($cuenta, 'cortes', 'c_fini=(SELECT MIN(c_fini) FROM cortes WHERE c_cuenta="' . $cuenta . '") AND c_cuenta', 'c_id');

  $tipo = busca($cuenta, 'cuentas', 'cu_id', 'cu_tipo');
  $saldo = busca($corte, 'cortes', 'c_id', 'c_saldo');

  $pagos = busca($corte, 'cortesd', 'cd_condonar="0" AND DATE(cd_fecha) < "' . $fecha . '" AND cd_tipo="P" AND cd_corte', 'SUM(cd_monto)');
  if (!isset($pagos))
    $pagos = 0;
  $traspasoso = busca($cuenta, 'traspasos', 'DATE(t_fgen) < "' . $fecha . '" AND t_origencorte="' . $corte . '" AND t_origen', 'SUM(t_importe)');
  if (!isset($traspasoso))
    $traspasoso = 0;
  $traspasosd = busca($cuenta, 'traspasos', 'DATE(t_fgen) < "' . $fecha . '" AND t_destinocorte="' . $corte . '" AND t_destino', 'SUM(t_importe)');
  if (!isset($traspasosd))
    $traspasosd = 0;
  $cobros = busca($corte, 'cortesd', 'cd_condonar="0" AND DATE(cd_fecha) < "' . $fecha . '" AND cd_tipo="C" AND cd_corte', 'SUM(cd_monto)');
  if (!isset($cobros))
    $cobros = 0;
  if ($tipo == "C")
    $total = $saldo - $cobros - $traspasosd + $traspasoso + $pagos;
  else
    $total = $saldo + $cobros + $traspasosd - $traspasoso - $pagos;

  return $total;
}
function clearvminus($var, $minus = true, $comp = false)
{
  mb_internal_encoding("UTF-8");
  if ($comp)
    $simbol = array('"', "'", "#", "$", "%", "&", "/", "(", ")", "=", "?", "¡", "*", "+", "~", "^", "[", "°", "|", "{", "}", "[", "]");
  else
    $simbol = array('"', "'");
  $cambio = "";

  $newvar = str_replace($simbol, $cambio, trim($var));
  if ($minus)
    $newvar = strtolower($newvar);

  return ($newvar);
}


function getmax($key, $table, $cond = false, $sumar = true)
{
  $sql = 'SELECT MAX(' . $key . ') FROM ' . $table . ' ';
  if ($cond)
    $sql .= ' WHERE ' . $cond;
  $result = setq($sql);
  list($id) = $result->fetch_array();
  if ($sumar)
    $id++;

  return ($id);
}

function unique($valor, $key, $table, $condicion = false)
{
  /*
    $valor = Valor que se quiere insertar en la BD
    key = Campo donde se insertará y se validará que no exista un valor igual
    $table = Tabla donse se comparárá

    $unique = true -> Campo no repetido __ False -> Campo repetido
  */
  $unique = 1;

  $sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $key . ' = "' . trim($valor) . '" ';
  if ($condicion)
    $sql .= ' AND ' . $condicion;
  $result = setq($sql);
  list($numr) = $result->fetch_array();
  if ($numr > 0)
    $unique = 0;

  return ($unique);
}

function getIDcliente($input)
{
  $input = strtoupper($input);
  $idc = NULL;

  $sqlpn = 'SELECT c_id FROM crm_clientes WHERE CONCAT(c_nmb," ",c_apellidos) = "' . $input . '"';
  $resultpn = setq($sqlpn);
  list($idpn) = $resultpn->fetch_array();

  if ($idpn) {
    $idc = $idpn;
  } else {
    $sqlpa = 'SELECT c_id FROM crm_clientes WHERE c_alias = "' . $input . '"';
    $resultpa = setq($sqlpa);
    list($idpa) = $resultpa->fetch_array();
    if ($idpa) {
      $idc = $idpa;
    }
  }

  // 👉 Aquí la diferencia:
  $idpa = $idpa ?? NULL; // Evita undefined
  if (!$idpa) {
    $sqlpa = 'SELECT COUNT(*) FROM crm_clientes WHERE c_id = "' . $input . '"';
    $resultpa = setq($sqlpa);
    list($idpa) = $resultpa->fetch_array();
    if ($idpa > 0) {
      $idc = $input;
    }
  }

  return ($idc);
}


function getIDprod($producto)
{
  $producto = clearvmayus($producto);

  $sql = 'SELECT a_id FROM articulos WHERE a_nmb LIKE "%' . $producto . '%" OR
           a_cb LIKE "%' . $producto . '%"';
  $result = setq($sql);
  $numresult = $result->num_rows;
  if ($numresult > 0 && !empty($producto)) {
    list($return) = $result->fetch_array();
  } else
    $return = false;

  return ($return);
}

function getIDmp($producto)
{
  $producto = clearvmayus($producto);

  $sql = 'SELECT pmp_id FROM pr_materiaprima WHERE pmp_nmb LIKE "%' . $producto . '%"';
  $result = setq($sql);
  $numresult = $result->num_rows;
  if ($numresult > 0 && !empty($producto)) {
    list($return) = $result->fetch_array();
  } else
    $return = false;

  return ($return);
}

function getIDprodVar($producto)
{
  $producto = clearvmayus($producto);

  $sql = 'SELECT av_articulo, av_modelo AS nmb_completo FROM articulos_variantes INNER JOIN articulos ON av_articulo = a_id WHERE CONCAT(a_nmb, " ", av_nmb) LIKE "%' . $producto . '%" OR av_cb LIKE "%' . $producto . '%" OR av_articulo LIKE "%' . $producto . '%"';
  $result = setq($sql);
  $numresult = $result->num_rows;

  if ($numresult > 0 && !empty($producto)) {
    list($return, $modelo) = $result->fetch_array();
    // Si deseas devolver ambos valores en un array asociativo, puedes hacerlo así:
    // return array("av_articulo" => $return, "av_modelo" => $modelo);
  } else {
    $return = false;
    $modelo = null; // Asignar null al modelo si no se encontraron resultados.
  }

  return array($return, $modelo); // Devolver ambos valores como un array.
}

function ajustaexistencia($articulo, $cantidad, $almacen, $modo = "E", $fcaduca = NULL, $modelo = NULL, $idmov = NULL, $tipomod= NULL)
{
  if ($modo == "S") {
    $contados = 0;
    $totcosto = 0;
    $sql = ' SELECT e_secuencia, e_cantidad FROM existencias
            WHERE e_articulo="' . $articulo . '" AND e_almacen = "' . $almacen . '" AND e_modelo = "' . $modelo . '"
            ORDER BY e_secuencia ASC';
    $result = setq($sql);
    while ($row2 = $result->fetch_array()) {
      if ($row2['e_cantidad'] > ($cantidad - $contados)) {
        $sql = 'UPDATE existencias SET e_cantidad=e_cantidad-' . ($cantidad - $contados) . '
                WHERE e_articulo="' . $articulo . '" AND e_secuencia="' . $row2['e_secuencia'] . '" AND e_almacen = "' . $almacen . '" AND e_modelo = "' . $modelo . '"';
        setq($sql) or die($sql);
        $contados += ($cantidad - $contados);
        break;
      } else {
        $contados += $row2['e_cantidad'];
        $sql = 'DELETE FROM existencias WHERE e_articulo="' . $articulo . '"
              AND e_secuencia="' . $row2['e_secuencia'] . '" AND e_almacen = "' . $almacen . '" AND e_modelo = "' . $modelo . '"';
        setq($sql) or die($sql);
      }
    }
    $restante = $cantidad - $contados;
    if($restante > 0){
      $sqlck = 'SELECT xp_id, xp_cantidad FROM existencia_pendiente WHERE xp_articulo = "'.$articulo.'" AND xp_modelo = "'.$modelo.'" AND xp_estatus = "N"';
      $resultck = setq($sqlck);
      list($xpid, $canpen) = $resultck -> fetch_array();
      if($xpid){
        $cantnew = $restante + $canpen;
        $sqlres = 'UPDATE existencia_pendiente SET xp_cantidad = "'.$cantnew.'", xp_fechaupd = "'.date('Y-m-d H:i:s').'" WHERE xp_id = "'.$xpid.'"';
      } else{
        $sqlres = 'INSERT INTO existencia_pendiente SET xp_articulo = "'.$articulo.'",
                                                      xp_modelo = "'.$modelo.'",
                                                      xp_cantidad = "'.$restante.'",
                                                      xp_almacen = "'.$almacen.'",
                                                      xp_fechaupd = "'.date('Y-m-d H:i:s').'",
                                                      xp_estatus = "N"';
      }
      setq($sqlres);                                                    
    }
  } else {
    $sqlrep = 'SELECT xp_id, xp_cantidad FROM existencia_pendiente WHERE xp_almacen = "'.$almacen.'" AND xp_articulo = "'.$articulo.'" AND xp_modelo = "'.$modelo.'" AND xp_estatus = "N"';
    $resultrep = setq($sqlrep);
    list($idrep, $cantrep) = $resultrep -> fetch_array();
    if($idrep){
      $rest = $cantidad - $cantrep;
      if($rest > 0){
        $sqlres = 'UPDATE existencia_pendiente SET xp_estatus = "F" WHERE xp_id = "'.$idrep.'"';
        if($idmov){
          $sqlupd = 'UPDATE movimientosd SET md_repuestos = "'.$cantrep.'" WHERE md_movimiento = "'.$idmov.'" AND md_articulo = "'.$articulo.'" AND md_modelo = "'.$modelo.'"';
          setq($sqlupd);
        }
        $cantidad = $rest;
      } else if($rest == 0) {
        $sqlres = 'UPDATE existencia_pendiente SET xp_estatus = "F" WHERE xp_id = "'.$idrep.'"';
        if($idmov){
          $sqlupd = 'UPDATE movimientosd SET md_repuestos = "'.$cantidad.'" WHERE md_movimiento = "'.$idmov.'" AND md_articulo = "'.$articulo.'" AND md_modelo = "'.$modelo.'"';
          setq($sqlupd);
        }
        $cantidad = 0;
        
      } else {
        $rest = abs($rest);
        $sqlres = 'UPDATE existencia_pendiente SET xp_cantidad = "'.$rest.'" WHERE xp_id = "'.$idrep.'"';
        if($idmov){
          $sqlupd = 'UPDATE movimientosd SET md_repuestos = "'.$cantidad.'" WHERE md_movimiento = "'.$idmov.'" AND md_articulo = "'.$articulo.'" AND md_modelo = "'.$modelo.'"';
          setq($sqlupd);
        }
        $cantidad = 0;
      }
      setq($sqlres);
    } 
    if($cantidad > 0){
      $sql = 'SELECT MAX(e_secuencia) FROM existencias WHERE e_articulo = "' . $articulo . '" AND e_modelo = "' . $modelo . '"';
      $result = setq($sql) or die($sql);
      list($maxs) = $result->fetch_row();
      $maxs++;

      $sql = 'INSERT INTO existencias SET
              e_articulo = "' . $articulo . '",
              e_modelo = "' . $modelo . '",
              e_cantidad = "' . $cantidad . '",
              e_almacen = "' . $almacen . '",
              e_secuencia = "' . $maxs . '",
              e_costo="' . busca($articulo, 'articulos_precios', 'ap_activo = "1" AND ap_articulo', 'ap_costo') . '",            
              e_fingreso = "' . $_SESSION['emp'] . '",
              e_empresa = "' . $almacen . '",
              e_tipomov = "C",
              e_ordenc = "' . $maxs . '"';
      setq($sql) or die($sql);
    }
  }
}


function ajustaexistenciaproduccion($articulo, $cantidad, $almacen, $modo = "E", $modelo = NULL)
{
  if ($modo == "S") {
    $contados = 0;
    $sql = ' SELECT pe_secuencia, pe_cantidad FROM pr_existencias
            WHERE pe_articulo="' . $articulo . '" AND pe_almacen = "' . $almacen . '" AND pe_modelo = "' . $modelo . '"
            ORDER BY pe_secuencia ASC';
    $result = setq($sql);
    while ($row2 = $result->fetch_array()) {
      if ($row2['pe_cantidad'] > ($cantidad - $contados)) {
        $sql = 'UPDATE pr_existencias SET pe_cantidad=pe_cantidad-' . ($cantidad - $contados) . '
                WHERE pe_articulo="' . $articulo . '" AND pe_secuencia="' . $row2['pe_secuencia'] . '" AND pe_almacen = "' . $almacen . '" AND pe_modelo = "' . $modelo . '"';
        setq($sql) or die($sql);
        $contados += $row2['pe_cantidad'] - ($cantidad - $contados);
        break;
      } else {
        $contados += $row2['pe_cantidad'];
        $sql = 'DELETE FROM pr_existencias WHERE pe_articulo="' . $articulo . '"
              AND pe_secuencia="' . $row2['pe_secuencia'] . '" AND pe_almacen = "' . $almacen . '" AND pe_modelo = "' . $modelo . '"';
        setq($sql) or die($sql);
      }
    }
  } else {
    if($cantidad > 0){
      $sql = 'SELECT MAX(pe_secuencia) FROM pr_existencias WHERE pe_articulo = "' . $articulo . '" AND pe_modelo = "' . $modelo . '"';
      $result = setq($sql) or die($sql);
      list($maxs) = $result->fetch_row();
      $maxs++;

      $sql = 'INSERT INTO pr_existencias SET
              pe_articulo = "' . $articulo . '",
              pe_modelo = "' . $modelo . '",
              pe_cantidad = "' . $cantidad . '",
              pe_almacen = "' . $almacen . '",
              pe_secuencia = "' . $maxs . '",
              pe_costo="' . busca($articulo, 'articulos_precios', 'ap_activo = "1" AND ap_articulo', 'ap_costo') . '",            
              pe_fingreso = "' . date("Y-m-d") . '",
              pe_tipoe = "I",
              pe_ordenc = "' . $maxs . '"';
      setq($sql) or die($sql);
    }
  }
}


function ajustaexistenciamp($mp, $cantidad, $almacen, $modo = "E"){
  if ($modo == "S") {
    $contados = 0;
    $sql = 'SELECT pe_secuencia, pe_cantidad FROM pr_existencias
            WHERE pe_materiaprima ="' . $mp . '" AND pe_almacen = "' . $almacen . '"
            ORDER BY pe_secuencia ASC';
    $result = setq($sql);
    while ($row2 = $result->fetch_array()) {
      if ($row2['pe_cantidad'] > ($cantidad - $contados)) {

        $sql = 'UPDATE pr_existencias SET pe_cantidad=pe_cantidad-' . ($cantidad - $contados) . '
                WHERE pe_materiaprima="' . $mp . '" AND pe_secuencia="' . $row2['pe_secuencia'] . '" AND pe_almacen = "' . $almacen . '"';
                setq($sql) or die($sql);
                
        $contados += $row2['pe_cantidad'] - ($cantidad - $contados);
        break;
      } else {
        $contados += $row2['pe_cantidad'];
        $sql = 'DELETE FROM pr_existencias WHERE pe_materiaprima="' . $mp . '"
              AND pe_secuencia="' . $row2['pe_secuencia'] . '" AND pe_almacen = "' . $almacen . '"';
        setq($sql) or die($sql);
      }
    }
  } else {
    if($cantidad > 0){
      $sql = 'SELECT MAX(pe_secuencia) FROM pr_existencias WHERE pe_materiaprima = "' . $mp . '" AND pe_almacen = "'.$almacen.'"';
      $result = setq($sql) or die($sql);
      list($maxs) = $result->fetch_row();
      $maxs++;

      $sql = 'INSERT INTO pr_existencias SET
              pe_materiaprima = "' . $mp . '",
              pe_cantidad = "' . $cantidad . '",
              pe_almacen = "' . $almacen . '",
              pe_secuencia = "' . $maxs . '",
              pe_fingreso = "' . date('Y-m-d') . '",
              pe_tipoe = "M"';
      setq($sql) or die($sql);
    }
  }
}
function existencia($producto, $almacen = NULL)
{
  $sql = 'SELECT SUM(e_cantidad) FROM existencias WHERE e_articulo = "' . $producto . '"';
  if ($almacen)
    $sql .= ' AND e_almacen = "' . $almacen . '" ';
  $result = setq($sql);
  list($existencia) = $result->fetch_array();

  return ($existencia);
}
function existenciaModelo($producto, $almacen = NULL, $modelo)
{
  $sql = 'SELECT SUM(e_cantidad) FROM existencias WHERE e_articulo = "' . $producto . '"';
  if ($almacen)
    $sql .= ' AND e_almacen = "' . $almacen . '" ';
  if ($modelo != "")
    $sql .= ' AND e_modelo = "' . $modelo . '" ';
  $result = setq($sql);
  list($existencia) = $result->fetch_array();
  if ($existencia == null) {
    $existencia = 0;
  }
  return ($existencia);
}


function existenciaProduccion($producto, $almacen = NULL)
{
  $sql = 'SELECT SUM(e_cantidad) FROM existencias WHERE e_articulo = "' . $producto . '"';
  if ($almacen)
    $sql .= ' AND e_almacen = "' . $almacen . '" ';
  $result = setq($sql);
  list($existencia) = $result->fetch_array();

  return ($existencia);
}

function existenciaModeloProduccion($producto, $almacen = NULL, $modelo)
{
  $sql = 'SELECT SUM(pe_cantidad) FROM pr_existencias WHERE pe_articulo = "' . $producto . '"';
  if ($almacen)
    $sql .= ' AND pe_almacen = "' . $almacen . '" ';
  if ($modelo != "")
    $sql .= ' AND pe_modelo = "' . $modelo . '" ';
  $result = setq($sql);
  list($existencia) = $result->fetch_array();
  if ($existencia == null) {
    $existencia = 0;
  }

  return ($existencia);
}

function existenciamp($mp, $almacen = NULL)
{
  $sql = 'SELECT SUM(pe_cantidad) FROM pr_existencias WHERE pe_materiaprima = "' . $mp . '"';
  if ($almacen)
    $sql .= ' AND pe_almacen = "' . $almacen . '" ';
  $result = setq($sql);
  list($existencia) = $result->fetch_array();

  return ($existencia);
}

function buscacuenta($cp, $tipom, $idref)
{
  $cuenta = "";
  if ($cp == "P") {
    if ($tipom == "G") {
      $sql = 'SELECT cd_cuenta,cu_nmb FROM cxpagar INNER JOIN egreso_cxpagar ON cp_id = ec_cxpagar
              INNER JOIN egresos ON e_id = ec_egreso
              INNER JOIN cortesd ON cd_idref = e_id
              INNER JOIN cuentas ON cu_id = cd_cuenta
              WHERE cp_tipo = "G" AND cp_idref = "' . $idref . '"';
      $result = setq($sql);
      while ($row = $result->fetch_array()) {
        $cuenta .= $row['cu_nmb'] . ', ';
      }
    }
  }
  $cuenta = substr($cuenta, 0, -2);

  return ($cuenta);
}

function bitacora($cliente, $modulo, $desc, $idref, $proy)
{
  /*
    $Cliente = Id del cliente al que se le inserta la
    $modulo = Módulo donde se inserto el registro
    $desc = Descripción de la función insetada
    $idref = Id del movimiento realiado
    $clave = Clave del movimiento que se realiza
    $proy = Proyecto al que pertenece la entrada
  */

  $sql = 'INSERT INTO crm_bitacora SET
          cb_cliente = "' . $cliente . '",
          cb_user = "' . $_SESSION['uid'] . '",
          cb_modulo = "' . $modulo . '",
          cb_fecha = "' . date('Y-m-d H:i:s') . '",
          cb_descripcion = "' . $desc . '",
          cb_idref = "' . $desc . '",
          cb_proyecto = "' . $proy . '"';
  $ok = setq($sql);
  return ($ok);
}

function sumaexistencia($articulo, $cantidad, $almacen, $costo, $tipoin, $idin, $fecha = NULL, $modelo)
{
  if (!$fecha)
    $fecha = date('Y-m-d');

  $rowmax = getmax('e_secuencia', 'existencias', 'e_articulo="' . $articulo . '"');

  $sql2 = 'INSERT IGNORE INTO existencias SET
            e_secuencia="' . $rowmax . '",
            e_articulo="' . $articulo . '",
            e_modelo="' . $modelo . '",
            e_cantidad="' . $cantidad . '",
            e_almacen="' . $almacen . '",
            e_costo="' . $costo . '",
            e_tipomov="' . $tipoin . '",
            e_ordenc="' . $idin . '",
            e_fingreso="' . date('Y-m-d') . '"';
  setq($sql2);
}

function sumaexistenciamp($mp, $cantidad, $almacen, $costo, $tipoin, $idin, $fecha = NULL)
{
  if($cantidad > 0){
    if (!$fecha)
    $fecha = date('Y-m-d');

    $rowmax = getmax('pe_secuencia', 'pr_existencias', 'pe_materiaprima="' . $mp . '"');

    $sql2 = 'INSERT IGNORE INTO pr_existencias SET
              pe_secuencia="' . $rowmax . '",
              pe_materiaprima="' . $mp . '",
              pe_cantidad="' . $cantidad . '",
              pe_almacen="' . $almacen . '",
              pe_costo="' . $costo . '",
              pe_tipomov="' . $tipoin . '",
              pe_ordenc="' . $idin . '",
              pe_fingreso="'.date('Y-m-d').'"';
    setq($sql2);
  }
}

function alert_back($mensaje, $regreso = false)
{
  $alert = '<script type="text/javascript">';
  if ($regreso == true)
    $alert .= 'history.back(alert("' . $mensaje . '"));';
  else
    $alert .= 'alert("' . $mensaje . '");';
  $alert .= '</script>';
  return $alert;
}
function fecha_formato($fecha, $hora, $corta)
{

  $meses = array("01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre");
  $mesesc = array("01" => "Ene", "02" => "Feb", "03" => "Mar", "04" => "Abr", "05" => "May", "06" => "Jun", "07" => "Jul", "08" => "Ago", "09" => "Sep", "10" => "Oct", "11" => "Nov", "12" => "Dic");
  $dia = array('', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
  if ($hora) {
    $temp = explode(' ', $fecha);
    $elementos = explode('-', $temp[0]);
    if ($corta) {
      $fechanew = $elementos[2] . '-' . $mesesc[$elementos[1]] . '-' . $elementos[0];
      $fechanew = $fechanew . '  ' . $temp[1];
    } else {
      $f = $dia[date('N', strtotime($fecha))];
      $fechanew = $f . ', ' . $elementos[2] . ' de ' . $meses[$elementos[1]] . ' ' . $elementos[0];
      $fechanew = $fechanew . ' a las ' . $temp[1];
    }
  } else {
    $elementos = explode('-', $fecha);
    if ($corta)
      $fechanew = $elementos[2] . '-' . $mesesc[$elementos[1]] . '-' . $elementos[0];
    else {
      $f = $dia[date('N', strtotime($fecha))];
      $fechanew = $f . ', ' . $elementos[2] . ' de ' . $meses[$elementos[1]] . ' ' . $elementos[0];
    }
  }
  return $fechanew;
}

function menu_select_db($tabla, $id, $nmb, $seleccion, $nombre, $filtro = 'XXX', $showid = false, $multiple = false, $cambio = false, $required = false, $place = false, $disabled = false)
{
  //genera un select de una base y se mantiene en la seleccion
//$tabla - nombre de tabla de una base abierta
//$id - campo llave que compara con $seleccion
//$nmb - descripcion o campo a mostrar en el select
//$seleccion - información
//$nombre - nombre del control
//$filtro - expresion para filtrar por campo (opc)
//$cambio - accion ke realizara el menu al dar click sobre una opcion R=refrecar la pagina, S=envia submint

  //------------------------------------------------
  $sqlmen = 'SELECT ' . $id . ', ' . $nmb . ' FROM ' . $tabla;
  if ($filtro != 'XXX')
    $sqlmen = $sqlmen . ' WHERE ' . $filtro;
  if ($showid) {
    $sqlmen .= ' ORDER BY ' . $id;
  } else {
    $sqlmen .= ' ORDER BY ' . $nmb;
  }
  $resultmen = setq($sqlmen);
  if ($multiple) {
    $multiple = 'multiple size="3"';

  }
  if ($cambio == 'S')
    $cambio = 'onChange="this.form.submit()"';
  if ($cambio == 'R')
    $cambio = 'onChange="location.reload()"';
  if ($required == true)
    $required = 'required title="Se requiere un valor del menú"';
  if ($disabled)
    $disabled = "disabled";
  else
    $disabled = "";
  $leytodos = "";
  if (!$place)
    $place = "Elije un elemento";
  else
    $leytodos = $place;

  $menu = '<select class="form-control" placeholder="' . $place . '" name="' . $nombre . '" ' . $multiple . ' ' . $cambio . ' ' . $required . '  ' . $disabled . '}>';
  if (!$multiple)
    $menu = $menu . '<option selected value="">' . $leytodos . ' </option>';
  while ($rowme = $resultmen->fetch_array()) {
    //      $row[$nmb]  = substr($row[$nmb],0,60);
    $rowme[$nmb] = $rowme[$nmb];

    $menu = $menu . '<option ';
    if ($seleccion === $rowme[$id])
      $menu = $menu . ' selected ';
    $menu = $menu . 'value="' . $rowme[$id] . '">';
    if ($showid) {
      $menu .= $rowme[$id] . ' - ';
    }
    $menu .= $rowme[$nmb] . '</option>';
  }
  $menu = $menu . '</select>';
  return ($menu);
}
function num2letras($num, $fem = true, $dec = true)
{
  $matuni[2] = "dos";
  $matuni[3] = "tres";
  $matuni[4] = "cuatro";
  $matuni[5] = "cinco";
  $matuni[6] = "seis";
  $matuni[7] = "siete";
  $matuni[8] = "ocho";
  $matuni[9] = "nueve";
  $matuni[10] = "diez";
  $matuni[11] = "once";
  $matuni[12] = "doce";
  $matuni[13] = "trece";
  $matuni[14] = "catorce";
  $matuni[15] = "quince";
  $matuni[16] = "dieciseis";
  $matuni[17] = "diecisiete";
  $matuni[18] = "dieciocho";
  $matuni[19] = "diecinueve";
  $matuni[20] = "veinte";
  $matunisub[2] = "dos";
  $matunisub[3] = "tres";
  $matunisub[4] = "cuatro";
  $matunisub[5] = "quin";
  $matunisub[6] = "seis";
  $matunisub[7] = "sete";
  $matunisub[8] = "ocho";
  $matunisub[9] = "nove";

  $matdec[2] = "veint";
  $matdec[3] = "treinta";
  $matdec[4] = "cuarenta";
  $matdec[5] = "cincuenta";
  $matdec[6] = "sesenta";
  $matdec[7] = "setenta";
  $matdec[8] = "ochenta";
  $matdec[9] = "noventa";
  $matsub[3] = 'mill';
  $matsub[5] = 'bill';
  $matsub[7] = 'mill';
  $matsub[9] = 'trill';
  $matsub[11] = 'mill';
  $matsub[13] = 'bill';
  $matsub[15] = 'mill';
  $matmil[4] = 'millones';
  $matmil[6] = 'billones';
  $matmil[7] = 'de billones';
  $matmil[8] = 'millones de billones';
  $matmil[10] = 'trillones';
  $matmil[11] = 'de trillones';
  $matmil[12] = 'millones de trillones';
  $matmil[13] = 'de trillones';
  $matmil[14] = 'billones de trillones';
  $matmil[15] = 'de billones de trillones';
  $matmil[16] = 'millones de billones de trillones';

  $num = trim((string) @$num);

  if ($num == 100)
    $tex = ' cien';
  elseif ($num == 100000)
    $tex = ' cien mil';
  else {
    if ($num[0] == '-') {
      $neg = 'menos ';
      $num = substr($num, 1);
    } else
      $neg = '';
    while ($num[0] == '0')
      $num = substr($num, 1);
    if ($num[0] < '1' or $num[0] > 9)
      $num = '0' . $num;
    $zeros = true;
    $punt = false;
    $ent = '';
    $fra = '';
    for ($c = 0; $c < strlen($num); $c++) {
      $n = $num[$c];
      if (!(strpos(".,'''", $n) === false)) {
        if ($punt)
          break;
        else {
          $punt = true;
          continue;
        }

      } elseif (!(strpos('0123456789', $n) === false)) {
        if ($punt) {
          if ($n != '0')
            $zeros = false;
          $fra .= $n;
        } else

          $ent .= $n;
      } else

        break;

    }
    $ent = '     ' . $ent;
    $fin = '';
    if ((int) $ent === 0)
      return 'Cero ' . $fin;
    $tex = '';
    $sub = 0;
    $mils = 0;
    $neutro = false;
    while (($num = substr($ent, -3)) != '   ') {
      $ent = substr($ent, 0, -3);
      if (++$sub < 3 and $fem) {
        $matuni[1] = 'un';
        $subcent = 'os';
      } else {
        $matuni[1] = $neutro ? 'un' : 'uno';
        $subcent = 'os';
      }
      $t = '';
      $n2 = substr($num, 1);
      if ($n2 == '00') {
      } elseif ($n2 < 21)
        $t = ' ' . $matuni[(int) $n2];
      elseif ($n2 < 30) {
        $n3 = $num[2];
        if ($n3 != 0)
          $t = 'i' . $matuni[$n3];
        $n2 = $num[1];
        $t = ' ' . $matdec[$n2] . $t;
      } else {
        $n3 = $num[2];
        if ($n3 != 0)
          $t = ' y ' . $matuni[$n3];
        $n2 = $num[1];
        $t = ' ' . $matdec[$n2] . $t;
      }
      $n = $num[0];
      if ($n == 1) {
        $t = ' ciento' . $t;
      } elseif ($n == 5) {
        $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
      } elseif ($n != 0) {
        $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
      }
      if ($sub == 1) {
      } elseif (!isset($matsub[$sub])) {

        if ($num == 1) {
          $t = ' mil';
        } elseif ($num > 1) {
          $t .= ' mil';
        }
      } elseif ($num == 1) {
        $t .= ' ' . $matsub[$sub] . 'on';
      } elseif ($num > 1) {
        $t .= ' ' . $matsub[$sub] . 'ones';
      }
      if ($num == '000')
        $mils++;
      elseif ($mils != 0) {
        if (isset($matmil[$sub]))
          $t .= ' ' . $matmil[$sub];
        $mils = 0;
      }
      $neutro = true;
      $tex = $t . $tex;
    }

  }
  $tex = $neg . substr($tex, 1) . $fin;
  return ucfirst($tex);
}

function cambiar_fecha($fecha, $hr = NULL)
{
  $formato = '';
  //echo $formato;
  $meses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

  $fechaA = explode('-', $fecha);
  //convierte formatos de fecha
  /* switch($fechaA[0])
    {
    case
    } */
  //$formato.=' ';
  $dia = array('', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
  $f = $dia[date('N', strtotime($fecha))];


  if (!isset($fechaA[2]))
    $fechaA[2] = NULL;
  if (!isset($fechaA[1]))
    $fechaA[1] = NULL;

  $formato .= $f . ' '; //muestra dia
  $formato .= $fechaA[2] . ' '; //muestra dia
  $formato .= $meses[number_format($fechaA[1], 0)] . ' '; //muestra mes
  $formato .= $fechaA[0] . ' '; //muestra año
//$formato.=$fecha;
  return $formato;
}
function limpia_espacios($codigo)
{
  $buscar = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
  $reemplazar = array('>', '<', '\\1');
  $codigo = preg_replace($buscar, '', $codigo);
  $codigo = str_replace("> <", "><", $codigo);
  $codigo = str_replace("br/>", "", $codigo);
  return $codigo;
}
function menu_select_array($array, $seleccion, $nombre, $required = false, $cambio = false, $todos = false)
{
  // genera un select de acuerdo a un array y se mantiene en la seleccion
//$array - nombre de tabla de una base abierta
//$seleccion - información
//$nombre - nombre del control
//------------------------------------------------
  if ($required == true)
    $required = "required";
  else
    $required = "";


  if ($cambio == true)
    $cambio = 'onChange="this.form.submit()"';
  if ($todos == false)
    $leytodos = '  ';
  else
    $leytodos = $todos;

  $menu = '<select class="form-control" name=' . $nombre . ' ' . $required . ' ' . $cambio . '>';
  /* if ($seleccion=='') */$menu = $menu . '<option selected value=""> ' . $leytodos . ' </option>';
  for (reset($array); $id = key($array); next($array)) {
    $menu = $menu . '<option ';
    if ($seleccion == $id)
      $menu = $menu . ' selected ';
    $menu = $menu . 'value="' . $id . '">' . $array[$id] . '</option>';
  }
  $menu = $menu . '</select>';
  return ($menu);
}

function sendmail($accion, $destino, $mensaje, $param = NULL)
{
  /* $accion
     1-> Recupera contraseña
  */
  if ($accion == 1) {
    $sql = 'SELECT * FROM mails_empresa WHERE me_funcion = "1"';
    $result = setq($sql);
    list($empresa, $idmail, $host, $port, $mailemp, $passmail) = $result->fetch_array();

    $nmbemp = busca($empresa, 'empresas', 'e_id', 'e_nmb');

    $from = $nmbemp;
    $nombredes = busca($param, 'usuarios', 'u_id', 'u_nmb');
    $subject = "Recuperación de contraseña";
  }

  require_once('lib/mailer/class.phpmailer.php');
  require_once("lib/mailer/class.smtp.php");

  session_start();
  //instancio un objeto de la clase PHPMailer
  $mail = new PHPMailer(); // defaults to using php "mail()"
  $correoc = $destino;
  $body = (utf8_decode($mensaje));

  //defino el email y nombre del remitente del mensaje
  $mail->setFrom($mailemp, utf8_decode($nmbemp));

  //Defino la dirección de correo a la que se envía el mensaje
  $nombredes;


  $mail->AddAddress($correoc, utf8_decode($nombredes));

  //Añado un asunto al mensaje
  $mail->Subject = utf8_decode($subject);

  //Puedo definir un cuerpo alternativo del mensaje, que contenga solo texto
  $mail->AltBody = "Cuerpo alternativo del mensaje";

  //inserto el texto del mensaje en formato HTML
  $mail->MsgHTML($body);

  $mail->Host = $host;
  //  $mail->SMTPDebug  = 2;
  $mail->Port = $port;
  $mail->IsSMTP();
  $mail->SMTPSecure = 'ssl';
  $mail->SMTPAuth = true; // turn on SMTP authentication
  $mail->Username = $mailemp;
  $mail->Password = $passmail;

  //envío el mensaje, comprobando si se envió correctamente
  if (!$mail->Send())
    $sended = 0;
  else
    $sended = 1;
}

function sendmailer($factura, $correo)
{
  //incluimos la clase PHPMailer
  require_once('lib/mailer/class.phpmailer.php');
  require_once("lib/mailer/class.smtp.php");
  include("funciones.php");

  session_start();
  //instancio un objeto de la clase PHPMailer
  $mail = new PHPMailer(); // defaults to using php "mail()"
  $id = $factura;
  $correoc = $factura;
  $empresa = busca($factura, 'facturas', 'f_id', 'f_empresa');

  $logoempresa = 'logos/l' . $empresa . '.jpg';

  $razonsocial = busca($empresa, 'empresas', 'e_id', 'e_razons');
  $mailemp = busca($empresa, 'empresas', 'e_id', 'e_correofac');

  $telefono = busca(1, 'empresas', 'e_id', 'e_tel');
  $factura = busca($id, 'facturas', 'f_id', 'f_folio');

  $nombrec = busca(busca($id, 'facturas', 'f_id', 'f_cliente'), 'crm_clientes', 'c_id', 'c_nmb');
  $folioemp = busca($id, 'facturas', 'f_id', 'f_folio');

  //defino el cuerpo del mensaje en una variable $body
//se trae el contenido de un archivo de texto
//también podríamos hacer $body="contenido...";
  $body = (utf8_decode('
      <table width="100%" border="0"><tr><td><img src="' . $logoempresa . '" width="100" height="100" /></td></tr></table>

      Estimado (a) ' . $nombrec . '<br>
      <br>'));
  if (busca($id, 'facturas', 'f_id', 'f_estatus') == "T") {
    $body .= (utf8_decode('
            A quien corresponda:<br>
            Por medio de la presente le informamos que ' . $razonsocial . ' le ha enviado un nuevo Comprobante Fiscal Digital.<br>
            <br>
            Mensaje:<br>
            Estimado(a) cliente, A continuación se le adjunta su Comprobante Fiscal Digital en electrónico y en formato para impresión.<br>
            Nosotros nos preocupamos por su éxito, empezando por nuestra promesa de dar siempre nuestro mejor esfuerzo,<br>
            llevando a cabo un proceso de mejora continua que garantice nuestra permanencia en el mercado siempre a la vanguardia,<br>
            conservando la lealtad y fidelidad de nuestros clientes.<br>
            <br>'));
  } else {
    $body .= (utf8_decode('
            Le informamos que se ha cancelado el documento previamente emitido con folio ' . $folioemp . ', si usted considera que la factura
            fue cancelada innecesariamente pongase en contacto a la brevedad.
            <br>'));

  }
  $body .= (utf8_decode('
            Atentamente:<br>
            ' . $razonsocial . '<br>
            Telefono: ' . $telefono . '<br>
            <br>
            Agradecemos su preferencia.<br>
            <br>
            Este es un mensaje generado automáticamente<br>
            Favor de no responder al mismo.<br>
            <br>'));
  //Esta línea la he tenido que comentar
//porque si la pongo me deja el $body vacío
// $body = preg_replace('/[]/i','',$body);
  if (busca($id, 'facturas', 'f_id', 'f_estatus') == "C")
    include_once('crearpdf.php');

  //defino el email y nombre del remitente del mensaje
  $mail->setFrom($mailemp, utf8_decode($razonsocial));

  //Defino la dirección de correo a la que se envía el mensaje
  $address = $nombrec;
  //la añado a la clase, indicando el nombre de la persona destinatario
  $nomempresa = $razonsocial;

  $mail->AddAddress($correoc, utf8_decode($nombrec));

  //Añado un asunto al mensaje
  if (busca($id, 'facturas', 'f_id', 'f_estatus') == "T")
    $mail->Subject = utf8_decode("Factura " . $razonsocial . ' ' . $folioemp);
  else
    $mail->Subject = utf8_decode("Cancelación de Factura " . $folioemp);

  //Puedo definir un cuerpo alternativo del mensaje, que contenga solo texto
  $mail->AltBody = "Cuerpo alternativo del mensaje";

  //inserto el texto del mensaje en formato HTML
  $mail->MsgHTML($body);

  //asigno un archivo adjunto al mensaje
  $mail->AddAttachment("cfd/" . $empresa . "/xml/" . $folioemp . ".xml");
  $mail->AddAttachment("cfd/" . $empresa . "/pdf/" . $folioemp . ".pdf");

  $mail->Host = 'tye-solutions.com';
  //$mail->SMTPDebug  = 3;
  $mail->Port = 587;
  $mail->IsSMTP();
  //$mail->SMTPSecure = 'ssl';
  $mail->SMTPAuth = true; // turn on SMTP authentication
  $mail->Username = $mailemp;
  $mail->Password = "WpJDTye2020#";

  //envío el mensaje, comprobando si se envió correctamente
  $mail->Send();
}

function preciojd($costo, $proveedor = NULL, $cb = NULL, $esquema = 1)
{
  $sqlep = 'SELECT ep_id,ep_base,ep_precio,ep_masmenos,ep_sumarp,ep_miva,ep_sumarf
            FROM esquema_precio WHERE ep_id = "' . $esquema . '" AND ep_estatus = "A"';
  $resultep = setq($sqlep);
  list($epid, $base, $precio, $masmenos, $sumarp, $miva, $sumarf) = $resultep->fetch_array();

  $precioact = busca($cb, 'articulos_precios', 'ap_activo = "1" AND ap_esquema = "' . $esquema . '" AND ap_articulo', 'ap_precio');

  if ($base == "C") {
    $pput = 1 + ($sumarp / 100);
    if ($masmenos == "+")
      $precio = $costo * $pput;
    if ($miva == "1")
      $precio = $precio * ((100 + busca(1, 'configuracionesp', 'c_id', 'c_iva')) / 100);
    if ($sumarf == "1")
      $precio += $sumarf;
  } else {
    $precio = $precioact;
  }

  $precio = ceil($precio);
  $precioacom = $precio;
  //Sumo comisión de paypal y ML 5/10/2021 JOSS
  $comision = ((($precio * (4 / 100)) + 4) * 1.16); // (4% del precio + $4.00) + IVA
//  echo $comision.'<-->'.$precioacom.'<br>';
  if ($esquema == 1)
    $precio = $precioacom + $comision;
  //  die($precio);

  //sumo 200 de envio si aplica
/*
  if($precio > 2500){
    $sql = 'UPDATE articulo_proveedor SET
            i_envio = "200"
            WHERE i_articulo = "'.$sku.'" AND i_proveedor = "'.$proveedor.'"
            AND i_usar = "1"';
    setq($sql) or die($sql);
  }
  else{
    $sql = 'UPDATE indexproveedores SET
            i_envio = "0"
            WHERE i_articulo = "'.$sku.'" AND i_proveedor = "'.$proveedor.'"
            AND i_usar = "1"';
    setq($sql) or die($sql);
  }

*/
  //calculo de msi
  $sqles = 'SELECT * FROM esquema_precio  WHERE ep_mesesinc = "1" AND ep_id= "' . $esquema . '"';
  $resultes = setq($sqles) or die($sqles);
  $resultesnp = setq($sqles);

  if ($resultesnp->num_rows > 0) {
    $precioac = $precio;

    while ($rowp = $resultes->fetch_array()) {
      $sqlfp = 'SELECT f_cuotafija,f_pcom,f_pcom6msi FROM formaspago WHERE f_predeterminado = "1"';
      $resultfp = $mysqli->query($sqlfp);
      list($cuotafija, $pcom, $pcom6msi) = $resultfp->fetch_array();
      $preciorv = ($precioac * ($pcom / 100)) + $cuotafija;
      $precio6ms = $precioac * ($pcom6msi / 100);
      $preciofin = $precioac + $preciorv + $precio6ms;
      $mysqli->close();
    }
  } else
    $preciofin = $precio;

  $preciofin = ceil($preciofin);

  return $preciofin;
}

function sendmailop($web, $correop, $cliente, $pedido = NULL, $adjunto = NULL, $accion = NULL, $idacc = NULL)
{
  require_once('lib/mailer/class.phpmailer.php');
  require_once("lib/mailer/class.smtp.php");
  $web = 1;

  $sql = 'SELECT * FROM correosop WHERE c_id = "' . $correop . '" AND c_web = "' . $web . '"';
  $result = setq($sql) or die($sql);
  $rwco = $result->fetch_array();

  $conmb = $rwco['c_nmb'];
  $codescripcion = nl2br($rwco['c_descripcion']);
  $coemail = $rwco['c_mail'];
  $cotitulo = $rwco['c_titulo'];
  $cocategoria = $rwco['c_categoria'];
  $cosms = $rwco['c_sms'];
  $cotsms = $rwco['c_textsms'];
  $coccp1 = $rwco['c_ccp1'];
  $coccp2 = $rwco['c_ccp2'];
  $coccp3 = $rwco['c_ccp3'];
  $coautomatico = $rwco['c_automatico'];
  $descripcion = $rwco['c_nmb'];
  $clave = $rwco['c_claveb'];

  $sqlc = 'SELECT * FROM clientes WHERE c_id = "' . $cliente . '"';
  $resultc = setq($sqlc) or die($sqlc);
  $rwc = $resultc->fetch_array();

  $idc = $rwc['c_id'];
  $nmbc = $rwc['c_nmb'];
  $mailc = $rwc['c_mail'];
  $telefonoc = $rwc['c_telefono'];
  $celularc = $rwc['c_celular'];

  $tablep = "";
  $tablepn = "";

  if ($pedido) {
    /*$sqlp = 'SELECT * FROM pedidoscld INNER JOIN productos ON p_id = pd_producto
    WHERE pd_pedido = "'.$pedido.'"';*/
    $sqlp = 'SELECT * FROM pedidoscld INNER JOIN articulos ON a_cb = pd_producto
    INNER JOIN articulosw ON a_id = aw_id
    WHERE pd_pedido = "' . $pedido . '"';
    $resultp = setq($sqlp) or die($sqlp);
    $total = 0;

    $tablep .= '
    <table class="table table-bordered" style="font-size:10px;font-family:andale mono, monospace;">
    <thead><tr>
    <th width="30%">Producto</th>
    <th width="12%">Cantidad</th>
    <th width="20%">Precio</th>
    <th width="20%">Total</th>
    </tr></thead>
    <tbody>';
    $subtotal = 0;
    while ($row = $resultp->fetch_array()) {
      if ($row['pd_cantidad'] > 0) {
        $sku = busca($row['pd_producto'], 'productos_tallasc', 'pt_talla = "' . $row['pd_talla'] . '" AND pt_color = "' . $row['pd_color'] . '" AND pt_producto', 'pt_sku');
        $imagen = busca($row['a_cb'], 'imagenes', 'i_idimg = "1" AND i_idproducto', 'i_nmb');

        $precio = $row['pd_precio'] - $row['pd_descuento'];
        $cb = $row['pd_producto'];
        $talla = $row['pd_talla'];
        $color = $row['pd_color'];
        $importe = $precio * $row['pd_cantidad'];
        $subtotal += $importe;
        $total += $importe;
        $totalpub = 0;

        $tablep .= '<tr class="content-tabla">';

        $tablep .= '<td style="padding:5pt;">' . descripcion1($row['a_id']);
        if ($row['pd_talla'] != 0)
          $tablep .= '<br><b>TALLA</b>: ' . busca($row['pd_talla'], 'tallas', 't_id', 't_nmb');
        if ($row['pd_color'] != 0)
          $tablep .= '<br><b>COLOR</b>: ' . busca($row['pd_color'], 'colores', 'c_id', 'c_nmb');
        $tablep .= '</td>';
        $tablep .= '<td>' . number_format($row['pd_cantidad'], 0) . '</td>';
        $tablep .= '<td><span style="font-size:12px;">$' . number_format($precio, 2) . '</span></td>';
        $tablep .= '<td><span id="td' . $row['pd_id'] . '" style="font-size:12px;">$' . number_format($importe, 2) . '</span></td>';
      }
    }
    $formaenvio = busca($pedido, 'pedidoscl', 'p_id', 'p_paqueteria');
    $paqueteria = busca($pedido, 'guias_pedidos', 'gp_pedido', 'gp_paqueteria');
    $costoenvio = busca($pedido, 'pedidoscl', 'p_id', 'p_envio');
    $descuento = busca($pedido, 'bonificaciones', 'b_estatus = "A" AND b_pedido', 'SUM(b_monto)');
    $adicional = busca($pedido, 'pedidoscl', 'p_id', 'p_adicionalfp');
    $total = busca($pedido, 'pedidoscl', 'p_id', 'p_total');
    $dirp = busca($pedido, 'pedidoscl', 'p_id', 'p_direccion');
    $direnvio = busca($dirp, 'direnvio', 'd_id', 'd_calle') . ' ' . busca($dirp, 'direnvio', 'd_id', 'd_nume') . ' ' . busca($dirp, 'direnvio', 'd_id', 'd_numi') . ' ' . busca($dirp, 'direnvio', 'd_id', 'd_colonia') . ' ' . busca($dirp, 'direnvio', 'd_id', 'd_municipio') . ' ' . busca(busca($dirp, 'direnvio', 'd_id', 'd_estado'), 'estado', 'e_id', 'e_nmb') . ' ' . busca($dirp, 'direnvio', 'd_id', 'd_cp') . ' ' . busca($dirp, 'direnvio', 'd_id', 'd_pais');
    $metodop = busca($pedido, 'pedidoscl', 'p_id', 'p_fpago');
    $nocajas = busca($pedido, 'guias_pedidos', 'gp_pedido', 'gp_paquete');

    $tablep .= '<tr><td colspan="2">&nbsp;</td><td>Subtotal</td><td>$' . number_format($subtotal, 2) . '</td></tr>';
    $tablep .= '<tr><td colspan="2">&nbsp;</td><td>Envio</td><td>$' . number_format($costoenvio, 2) . '</td></tr>';
    $tablep .= '<tr><td colspan="2">&nbsp;</td><td>Descuento</td><td>$' . number_format($descuento, 2) . '</td></tr>';
    $tablep .= '<tr><td colspan="2">&nbsp;</td><td>Adicional</td><td>$' . number_format($adicional, 2) . '</td></tr>';
    $tablep .= '<tr><td colspan="2">&nbsp;</td><td>Total a Pagar</td><td>$' . number_format($total, 2) . '</td></tr>';
    $tablep .= '</table>';

    $infopagopedido = '--';

    if (busca($metodop, 'formaspago', 'f_id', 'f_subforma') == 1) {
      $cuenta = busca($pedido, 'pedidoscl', 'p_id', 'p_cuentafp');
      $cadcta = busca($cuenta, 'cuentasfp', 'c_id', 'c_nmb') . ' ' . busca($cuenta, 'cuentasfp', 'c_id', 'c_descripcion');
      $infopagopedido .= $cadcta;
    } else {
      $infopagopedido .= busca($metodop, 'formaspago', 'f_id', 'f_descripcion');
    }

    $sql = 'SELECT * FROM guias_pedidos WHERE gp_pedido = "' . $pedido . '"';
    $result = setq($sql) or die($sql);
    $noguia = '';
    while ($row = $result->fetch_array()) {
      $noguia .= $row['gp_guia'] . ', ';
    }
    $noguia = substr($noguia, 0, -2);
    $fenvio = date('d-m-Y', strtotime(busca($pedido, 'pedidoscl', 'p_id', 'p_fautoriza')));

    $detalleped = $tablep;
  }

  if ($accion == 'rpassword') {
    do {
      $md5Hash = md5(microtime() * mktime());
    }
    while (strlen($md5Hash) < 8);
    $newpassword = substr($md5Hash, 0, 8);

    $sql = 'UPDATE clientes SET c_rstpass = PASSWORD("' . $newpassword . '") WHERE c_id = "' . $cliente . '"';
    setq($sql) or die($sql);
  } elseif ($accion == 'cpago') {
    $idpago = $idacc;
    $sqlrp = 'SELECT * FROM reportepago WHERE r_id = "' . $idpago . '" AND r_pedido = "' . $pedido . '"';
    $resultrp = setq($sqlrp) or die($sqlrp);
    $rwrp = $resultrp->fetch_array();
    $detpago = '';
    $detpago .= 'Cuenta: ' . $rwrp['r_cuenta'] . ' Fecha: ' . $rwrp['r_fecha'] . ' Hora: ' . $rwrp['r_hora'];
    $detpago .= '<br>Monto Reportado: $ ' . $rwrp['r_monto'] . ' Referencia: ' . $rwrp['r_referencia'];
    $detpago .= '<br>Observaciones: $ ' . $rwrp['r_observacion'];
  }
  $title = $cotitulo;
  $title = str_replace('--%numeropedido%--', $pedido, $title);

  $message = $codescripcion;
  $message = str_replace('--%nombrecliente%--', $nmbc, $message);
  $message = str_replace('--%emailcliente%--', $mailc, $message);
  $message = str_replace('--%contrasenacliente%--', $newpassword, $message);
  $message = str_replace('--%numeropedido%--', $pedido, $message);
  $message = str_replace('--%infopagopedido%--', $infopagopedido, $message);
  $message = str_replace('--%direnviopedido%--', $direnvio, $message);
  $message = str_replace('--%detallepedido%--', $detalleped, $message);
  $message = str_replace('--%totalpedido%--', $total, $message);
  $message = str_replace('--%cajaspedido%--', $nocajas, $message);
  $message = str_replace('--%socioenviopedido%--', $paqueteria, $message);
  $message = str_replace('--%nocajaspedido%--', $nocajas, $message);
  $message = str_replace('--%noguiapedido%--', $noguia, $message);
  $message = str_replace('--%fenviopedido%--', $fenvio, $message);
  $message = str_replace('--%nopiezas%--', $nopiezas, $message);
  $message = str_replace('--%numerocliente%--', $cliente, $message);
  $message = str_replace('--%recibopago%--', $idpago, $message);
  $message = str_replace('--%detallepagonoacreditado%--', $detpago, $message);
  $message = str_replace('--%nombreingasignado%--', '', $message);
  $message = str_replace('--%maildeingasignado%--', '', $message);

  if ($cosms == "1") {
    $sms = $cotsms;
    $sms = str_replace('--%nombrecliente%--', $nmbc, $sms);
    $sms = str_replace('--%emailcliente%--', $mailc, $sms);
    $sms = str_replace('--%contraseñacliente%--', $newpassword, $sms);
  }

  /*if($correop == 7 || $correop == 8){
    $clientd = busca($id,'devoluciones','d_id','d_cliente');
    $cliente = busca($cliented,'clientes','c_id','c_nmb');
    $message = $codescripcion;
    $message = str_replace('--%nombrecliente%--',$cliente,$codescripcion);
    $message = str_replace('--%iddevolucion%--',$id,$codescripcion);
  }*/
  echo $message . '<';
  $mail = new PHPMailer();
  $body = (utf8_decode('
  <br>' . $message . '<br>
  '));

  $mail->setFrom($coemail, "JD SHOP");
  $mail->AddAddress($mailc, $nmbc);

  if (!empty($coccp1))
    $mail->AddBCC($coccp1);
  if (!empty($coccp2))
    $mail->AddBCC($coccp2);
  if (!empty($coccp3))
    $mail->AddBCC($coccp3);

  $mail->Subject = utf8_decode($title);
  $mail->AltBody = "";
  $mail->MsgHTML($body);

  if ($adjunto)
    $mail->AddAttachment($adjunto);

  $mail->SMTPSecure = "ssl";
  $mail->Host = 'jdshop.mx';
  $mail->Port = "465";
  //  $mail->SMTPDebug = 2;
  $mail->IsSMTP();
  $mail->SMTPAuth = true;
  $mail->Username = $coemail;
  $mail->Password = "WpJDTye2020#";

  if ($coautomatico == 1) {
    //$mail->Send();
    if (!$mail->Send()) {
      //      die("Error al enviar el mensaje: " . $mail->ErrorInfo);
    }
    /*if($cosms == 1){
      if(strlen($sms) > 135){
        if($mulsms == 15){
          $nm = ceil(strlen($sms)/135);
          for($n=0;$n<$nm;$n++){
            $nsms = substr($sms, ($n*135), 135);
            sendsms('52',$celularc,$nsms);
          }
        }
        else{
          $nsms = substr($sms, 0, 135);
          sendsms('52',$celularc,$nsms);
        }
      }
      else{
        sendsms('52',$celularc,$sms);
      }
    }*/

  }
  //addbitacoraw($cliente,$clave,'JDSHOP',$descripcion,$pedido);
}
function descripcion1($id)
{
  //$sql = 'SELECT p_concepto,p_marca,p_nmb FROM productos WHERE p_id = "'.$id.'"';
  $sql = 'SELECT aw_concepto,a_marca,a_nmb FROM articulos INNER JOIN articulosw ON a_id = aw_id WHERE a_id = "' . $id . '"';
  $result = setq($sql) or die($sql);
  $row = $result->fetch_array();
  //  $concepto = busca($row['p_concepto'],'conceptos','c_id','c_nmb');
  $marca = busca($row['a_marca'], 'marcas', 'm_id', 'm_nmb');
  $descripcion = $row['a_nmb'];
  return $descripcion;
}
function sacarcadena($separador1, $separador2, $cadena)
{
  if (strpos($cadena, $separador1) !== false) {
    $pos = strpos($cadena, $separador1);
    $a = substr($cadena, $pos + strlen($separador1));
    if (strpos($a, $separador2) !== false) {
      $npos = strpos($a, $separador2);
      $b = substr($a, 0, $npos);
      return $b;
    } else
      return $a;
  } else
    return "NULL";
}

function crearToken($nombre, $descripcion, $fecha, $location, $responsable)
{
  //session_start();

  require_once 'vendor/autoload.php';
  $_SESSION['e_nombre'] = $nombre;
  $_SESSION['e_desc'] = $descripcion;
  $_SESSION['e_fecha'] = $fecha;
  $_SESSION['e_location'] = $location;
  $_SESSION['e_resp'] = $responsable;
  $client = new Google_Client();
  $client->setAuthConfig("vendor/credentials.json");
  $client->addScope('https://www.googleapis.com/auth/calendar');
  $client->addScope(Google_Service_Calendar::CALENDAR);
  $client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
  $authUrl = $client->createAuthUrl();
  redirect($authUrl);
}
function crearActividad($nombre, $descripcion, $fecha, $location, $responsable, $accion)
{
  $newDate = date("Y-m-d", strtotime($fecha));
  $newHora = date("h:m:s", strtotime($fecha));
  require_once 'vendor/autoload.php';
  $client = new Google_Client();
  $client->setAuthConfig("vendor/credentials.json");
  $client->addScope('https://www.googleapis.com/auth/calendar');
  $client->addScope(Google_Service_Calendar::CALENDAR);
  $client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
  $hora = $newDate . 'T' . $newHora;
  if (isset($_SESSION['access_token'])) {
    $client->setAccessToken($_SESSION['access_token']);
    $service = new Google_Service_Calendar($client);
    $event = new Google_Service_Calendar_Event(
      array(
        'summary' => $nombre,
        'location' => $location,
        'description' => $descripcion,
        'start' => array(
          'dateTime' => $hora,
          'timeZone' => 'America/Mexico_City'
        ),
        'end' => array(
          'dateTime' => $hora,
          'timeZone' => 'America/Mexico_City'
        ),
        'attendees' => array(
          array('email' => $responsable)
        ),
        'reminders' => array(
          'useDefault' => FALSE,
          'overrides' => array(
            array('method' => 'email', 'minutes' => 24 * 60),
            array('method' => 'popup', 'minutes' => 30),
          ),
        ),
      )
    );
    $calendarId = 'primary';
    $event = $service->events->insert($calendarId, $event);
    $_SESSION['e_nombre'] = "";
    $_SESSION['e_desc'] = "";
    $_SESSION['e_fecha'] = "";
    $_SESSION['e_location'] = "";
    $_SESSION['e_resp'] = "";
    //$_SESSION['access_token'] = "";
    $accion;
  }
}

function reasignarLeads2()
{
  $fechaActual = date("Y-m-d");

  // Resta 3 días a la fecha actual
  $fechaMenosTresDias = date("Y-m-d", strtotime("-3 days", strtotime($fechaActual)));
  $fechaMenosTresDias = date("Y-m-d");

  //Consultamos el número de usuarios activos que son de ventas
  $sqlu = 'SELECT u_id FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A"';
  /* echo '<br>Consulta de todos los Usuarios de ventas activos: <br>' . $sqlu . '<br>'; */
  $resultu = setq($sqlu);
  $arrayU = mysqli_fetch_all($resultu, MYSQLI_ASSOC);

  //Consultamos los leads que estan asignados o reasignados de tres dias atrás
  $sql = 'SELECT cl_id, cl_vendedor FROM crm_leads WHERE (cl_estatus = "A" OR cl_estatus = "R") AND cl_fasigna = "' . $fechaMenosTresDias . '"';
  $result = setq($sql);
  /* echo '<br>Consulta de todos los leads en estatus A y R: <br>' . $sql . '<br>'; */
  while ($row = $result->fetch_array()) {
    $idlead = $row['cl_id'];
    $vendedorcl = $row['cl_vendedor'];

    //Consultamos el número de vendedores que ya han sido asignados a este lead con anterioridad
    $sqlh = 'SELECT hl_vendedor FROM historial_leadsasignacion WHERE hl_lead = "' . $idlead . '"';
    /* echo 'Consulta de los registros del historial de asignacion del lead ' . $idlead . ': <br>' . $sqlead . '<br>'; */
    $resulth = setq($sqlh);

    $u_id_array = array_column($arrayU, 'u_id');
    /* echo '<br>Registros en el historial: ' . $resulth->num_rows . '<br>'; */
    if ($resulth->num_rows == 0) {

      // Arreglo para almacenar los valores que no coinciden con la cadena de búsqueda
      $valoresNoCoincidentes = [];

      // Recorremos el arreglo y agregamos los valores que no coinciden
      foreach ($u_id_array as $valor) {
        if ($valor !== $vendedorcl) {
          $valoresNoCoincidentes[] = $valor;
        }
      }
      $vendedorAleatorio = array_rand($valoresNoCoincidentes);

      // Usa el índice aleatorio para obtener el valor correspondiente
      $vendedor = $valoresNoCoincidentes[$vendedorAleatorio];
      /* die("Vendedor asignado: ".$vendedor); */
      reasignaVendedor($idlead, $vendedor);
    } else {

      $sqlead = 'SELECT u_id FROM historial_leadsasignacion INNER JOIN usuarios ON hl_vendedor = u_id WHERE hl_lead = "' . $idlead . '" AND hl_vendedor != "' . $vendedorcl . '" AND u_id != "' . $vendedorcl . '"';
      /* echo 'Consulta de los registros del historial de asignacion del lead ' . $idlead . ': <br>' . $sqlead . '<br>'; */
      $resultlead = setq($sqlead);

      /* die("Hay registros en historial"); */
      $arrayH = mysqli_fetch_all($resultlead, MYSQLI_ASSOC);
      $hl_vendedor_array = array_column($arrayH, 'hl_vendedor');

      // Encuentra los valores que están en $u_id_array pero no en $hl_vendedor_array
      $diferencia = array_intersect($u_id_array, $hl_vendedor_array);
      /* echo '<br>' . var_dump($diferencia) . '<br>';
      echo '<br> Asignaciones antes de dar una vuelta: ' . count($diferencia) . '<br>'; */

      if (count($diferencia) > 0) {
        // Obtén un índice aleatorio del arreglo $vendedores
        $vendedorAleatorio = array_rand($diferencia);

        // Usa el índice aleatorio para obtener el valor correspondiente
        $vendedor = $diferencia[$vendedorAleatorio];
        reasignaVendedor($idlead, $vendedor);
      } else {
        /* die("Llegó aqui"); */
        /*
        Si no hay diferencia alguna significa que todos los vendedores posibles ya han sido 
        asignados a este lead con anteriordad.
        Procedemos a asignar el lead de forma aleatoria a cualquier otro vendedor diferente al último asignado
        contabilizando el número de veces que se le ha asignado el lead a ese vendedor.
        */

        //Obtenemos el uid del ultimo vendedor asignado
        $sqlt = 'SELECT hl_vendedor FROM historial_leadsasignacion WHERE hl_lead = "' . $idlead . '" ORDER BY hl_fasigna DESC LIMIT 1';
        $resultt = setq($sqlt);
        list($ultimovendedor) = $resultt->fetch_array();

        //Consulto los vendedores con la excepcion del ultimo asignado
        $sqlr = 'SELECT hl_vendedor, COUNT(*) AS cantidad
              FROM historial_leadsasignacion WHERE hl_vendedor != "' . $ultimovendedor . '"
              GROUP BY hl_vendedor
              HAVING COUNT(*) = (
                SELECT MIN(cnt)
                FROM (
                  SELECT COUNT(*) AS cnt
                  FROM historial_leadsasignacion
                  GROUP BY hl_vendedor
                ) AS subquery
              )
      ';
        $resultr = setq($sqlr);
        $filas = $resultr->num_rows;
        //Si el resultado incluye más de un vendedor
        if ($filas > 1) {
          $arrayR = mysqli_fetch_all($resultr, MYSQLI_ASSOC);
          // Obtén los valores de 'u_id' de $arrayU y 'hl_vendedor' de $arrayH en dos arreglos separados
          $vendedores = array_column($arrayR, 'hl_vendedor');
          // Obtén un índice aleatorio del arreglo $vendedores
          $vendedorAleatorio = array_rand($vendedores);

          // Usa el índice aleatorio para obtener el valor correspondiente
          $vendedor = $vendedores[$vendedorAleatorio];

          //Si el resultado es solo un vendedor
        } else {
          list($vendedor) = $resultr->fetch_array();
        }
        //Asigno el lead a un nuevo vendedor
        reasignaVendedor($idlead, $vendedor);
      }
    }

  }
}


function reasignarLeads()
{
  $fechaActual = date("Y-m-d");

  // Resta 5 días a la fecha actual
  $fechaMenosTresDias = date("Y-m-d", strtotime("-5 days", strtotime($fechaActual)));
  //$fechaMenosTresDias = date("Y-m-d");

  //Consultamos el número de usuarios activos que son de ventas
  $sqlu = 'SELECT u_id FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A"';
  /* echo '<br>Consulta de todos los Usuarios de ventas activos: <br>' . $sqlu . '<br>'; */
  $resultu = setq($sqlu);
  $arrayU = mysqli_fetch_all($resultu, MYSQLI_ASSOC);
  $u_id_array = array_column($arrayU, 'u_id');

  //Consultamos los leads que estan asignados o reasignados de cinco dias atrás
  $sql = 'SELECT cl_id, cl_vendedor FROM crm_leads WHERE (cl_estatus = "A" OR cl_estatus = "R") AND cl_fasigna = "' . $fechaMenosTresDias . '"';
  $result = setq($sql);
  /* echo '<br>Consulta de todos los leads en estatus A y R: <br>' . $sql . '<br>'; */
  while ($row = $result->fetch_array()) {
    $idlead = $row['cl_id'];
    $vendedorcl = $row['cl_vendedor'];

    //Consultamos el número de vendedores que ya han sido asignados a este lead con anterioridad
    $sqlh = 'SELECT hl_vendedor FROM historial_leadsasignacion WHERE hl_lead = "' . $idlead . '"';
    /* echo '<br>Consulta de los registros del historial de asignacion del lead ' . $idlead . ': <br>' . $sqlh . '<br>'; */
    $resulth = setq($sqlh);
    $arrayH = mysqli_fetch_all($resulth, MYSQLI_ASSOC);
    $hl_vendedor_array = array_column($arrayH, 'hl_vendedor');

    /* echo '<br>Registros en el historial: ' . $resulth->num_rows . '<br>'; */

    // Encontrar elementos diferentes
    $diferencia = array_diff($u_id_array, $hl_vendedor_array);

    /* echo '<br>' . var_dump($diferencia) . '<br>';
    echo '<br> Asignaciones antes de dar una vuelta: ' . count($diferencia) . '<br>'; */

    if (count($diferencia) > 0) {
      // Obtén un índice aleatorio del arreglo $vendedores
      $vendedorAleatorio = array_rand($diferencia);

      // Usa el índice aleatorio para obtener el valor correspondiente
      $vendedor = $diferencia[$vendedorAleatorio];
      reasignaVendedor($idlead, $vendedor);
    } else {
      /*
      Si no hay diferencia alguna significa que todos los vendedores posibles ya han sido 
      asignados a este lead con anteriordad.
      Procedemos a asignar el lead de forma aleatoria a cualquier otro vendedor diferente al último asignado
      contabilizando el número de veces que se le ha asignado el lead a ese vendedor.
      */

      //Obtenemos el uid del ultimo vendedor asignado
      $sqlt = 'SELECT hl_vendedor FROM historial_leadsasignacion WHERE hl_lead = "' . $idlead . '" ORDER BY hl_fasigna DESC LIMIT 1';
      $resultt = setq($sqlt);
      list($ultimovendedor) = $resultt->fetch_array();
      /* echo '<br>Ultimo vendedor asignado: '.$ultimovendedor.'<br>'; */

      //Consulto los vendedores con la excepcion del ultimo asignado
      $sqlr = 'SELECT hl_vendedor, COUNT(*) AS cantidad
              FROM historial_leadsasignacion WHERE hl_vendedor != "' . $ultimovendedor . '"
              GROUP BY hl_vendedor
              HAVING COUNT(*) = (
                SELECT MIN(cnt)
                FROM (
                  SELECT COUNT(*) AS cnt
                  FROM historial_leadsasignacion
                  GROUP BY hl_vendedor
                ) AS subquery
              )
      ';
      /* echo '<br>Consulto los vendedores con la excepcion del ultimo asignado: <br>'.$sqlr.'<br>'; */
      $resultr = setq($sqlr);
      $filas = $resultr->num_rows;
      /* echo '<br>Filas: '.$filas.'<br>'; */
      //Si el resultado incluye más de un vendedor
      if ($filas > 1) {
        $arrayR = mysqli_fetch_all($resultr, MYSQLI_ASSOC);
        // Obtén los valores de 'u_id' de $arrayU y 'hl_vendedor' de $arrayH en dos arreglos separados
        $vendedores = array_column($arrayR, 'hl_vendedor');
        // Obtén un índice aleatorio del arreglo $vendedores
        $vendedorAleatorio = array_rand($vendedores);

        // Usa el índice aleatorio para obtener el valor correspondiente
        $vendedor = $vendedores[$vendedorAleatorio];

        //Si el resultado es solo un vendedor
      } else if($filas == 1){
        list($vendedor) = $resultr->fetch_array();
      } else{
        $sqlt = 'SELECT hl_vendedor FROM historial_leadsasignacion WHERE hl_lead = "' . $idlead . '" ORDER BY hl_fasigna ASC LIMIT 1';
        $resultt = setq($sqlt);
        list($vendedor) = $resultt->fetch_array();
      }
      //Asigno el lead a un nuevo vendedor
      reasignaVendedor($idlead, $vendedor);
    }

  }
}
function reasignaVendedor($idlead, $vendedor)
{
  $hora = date("H:i:s");
  $fecha = date("Y-m-d");
  $sql = 'UPDATE crm_leads SET
            cl_vendedor = "' . $vendedor . '",
            cl_hasigna = "' . $hora . '",
            cl_fasigna = "' . $fecha . '",
            cl_estatus = "R",
            cl_acercamiento = "0"
            WHERE cl_id = "' . $idlead . '"';
  setq($sql);
  inserthistorial($idlead, $hora, $fecha, $vendedor);
}

function inserthistorial($idlead, $hora, $fecha, $vendedor)
{
  $sql = 'INSERT INTO historial_leadsasignacion SET
            hl_lead = "' . $idlead . '",
            hl_vendedor = "' . $vendedor . '",
            hl_hasigna = "' . $hora . '",
            hl_fasigna = "' . $fecha . '"';
  setq($sql);
}

function screenpps(){

  ?>

  <style>
    .full-height-container {
      border: 1px solid;
    }
  </style>
  <div class="row">
  <div class="col-md-4 full-height-container table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th colspan="2">Detalles del proceso</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Concepto</td>
          <td>Descripción</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="col-md-8 full-height-container">
  <table class="table">
        <thead>
          <tr>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>12</td>
            <td>:</td>
            <td>51</td>
          </tr>
        </tbody>
      </table>
      </div>

  </div>


  <?php

}

function cbproduccion($forecast, $ordenp){
  $cb = '';
  
  $sqlmax = 'SELECT COUNT(*) AS consecutivo FROM pr_articulosop WHERE pra_op = "'.$ordenp.'"';
  $resultmax = setq($sqlmax);
  list($consec) = $resultmax->fetch_array();
  $consecutivo = intval($consec) + 1;

  $longitud = intval(strlen($consecutivo));

  if($longitud == 4){
      $txtConsecutivo = '';
  } else if($longitud == 3){
      $txtConsecutivo = '0';
  } else if($longitud == 2){
      $txtConsecutivo = '00';
  } else{
      $txtConsecutivo = '000';
  }

  $cb = $forecast.$ordenp.$txtConsecutivo.$consecutivo;
  return $cb;
}

function updatedproceso()
{
	if(isset($_GET['tipo'])){
		$tipo = "&tipo=".$_GET['tipo'];
	} else{
		$tipo = '';
	}

	$proceso = $_POST['proceso'];
	$ordenp = $_POST['ordenp'];
	$obs = $_POST['obs'];

	$carpetaDestino = '../img/procesos/'; // Reemplaza con la ruta donde deseas guardar el archivo
	$fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato predeterminado
	$fechaHoraSinGuionesNiPuntos = str_replace(array("-", ":", " "), "", $fechaHora);

	if (isset($_FILES['adj']['name'])) {
		$nombre_archivo = $_FILES['adj']['name'];
		$tamano_archivo = $_FILES['adj']['size'];
		if ($tamano_archivo > 3000000)
			$error = "6XMUE2";
		else {
			$adj = $carpetaDestino . $fechaHoraSinGuionesNiPuntos . $nombre_archivo;
			if (move_uploaded_file($_FILES['adj']['tmp_name'], $adj)) {
				$sqlf = 'pp_adj = "' . $adj . '"';
				$error = 0;
			} else {
				$sqlf = 'pp_adj = NULL';
				$error = "6XMUE4";
			}
		}
	} else {
		$error = 1;
	}

	
  $sql = 'UPDATE pr_procesosop SET ' . $sqlf . ', pp_obs = "' . $obs . '" WHERE pp_id = "' . $proceso . '" AND pp_ordenp = "' . $ordenp . '"';
  setq($sql);
	if(isset($_GET['origen']) && $_GET['origen'] == 1){
		$destino = 'procesomanual';
	} else{
		$destino = 'temporizadorpps';
	}
	return $destino.'.php?op=' . $_POST['ordenp'] . '&proceso=' . $_POST['proceso'] . $tipo;
}

function decimal_format($entrada, $decimales = 2, $decimal = ".", $separador = ",") {
  $entrada = number_format($entrada, $decimales, $decimal, $separador);
  $salida = rtrim(rtrim($entrada, "0"), $decimal);
  if (substr($salida, -1) === $decimal) {
      $salida .= '00';
  }
  return $salida;
}

function xmlFactura($tipo,$archivoXML,$idfac,$empresa = NULL,$idfc = NULL){
  if (file_exists($archivoXML)) {
    libxml_use_internal_errors(true);
    $xml = new SimpleXMLElement($archivoXML, 0, true);
    $namespaces = $xml->getNamespaces(true);

    $uuid = "";
    $ftimbrado = "";
    $certSat = "";
    $selloSAT = "";

    $xml->registerXPathNamespace('t', $namespaces['tfd']);
    foreach ($xml->xpath('//t:TimbreFiscalDigital') as $timbre) {
      $uuid = $timbre['UUID'];
      $fTimbrado = $timbre['FechaTimbrado'];
      $certSat = $timbre['NoCertificadoSAT'];
      $selloSat = $timbre['SelloSAT'];
    }

    $ftimbrado = str_replace('T',' ',$fTimbrado);

    if($tipo == "T"){
      foreach ($xml->xpath('//cfdi:Comprobante') as $comprobante) {
        $sello = $comprobante['Sello'];
        $nocertificado = $comprobante['NoCertificado'];
        $fechaEmision = $comprobante['Fecha'];
        $ft = explode('T',$fechaEmision);
        $fech = $ft[0].' '.$ft[1];
      }

      $sqlu = 'UPDATE facturas SET
              f_uuid = "'.mb_strtoupper($uuid).'",
              f_nocertisat = "'.$certSat.'",
              f_sellosat = "'.$selloSat.'",
              f_sello = "'.$sello.'",
              f_ftimbro = "'.$ftimbrado.'",
              f_nocerti = "'.$nocertificado.'",
              f_femision = "'.$fech.'"
              WHERE f_id = "'.$idfac.'"';
      //echo 'sql datos fiscales: '.$sqlu.' ftimbrado: '.$ftimbrado;
      setq($sqlu) or die($sqlu);
    }else{
      $sqlu = 'SELECT COUNT(*) FROM facturasrec WHERE fr_uuid = "'.$uuid.'"';
      $resultu = setq($sqlu) or die($sqlu);
      list($existe) = $resultu->fetch_array();

      if($existe == 0){
        $rfcrec = busca($empresa,'empresas','e_id','e_rfc');

        foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Receptor') as $receptor) {
          $rfcreceptor = $receptor['Rfc'];
        }

        if($rfcreceptor == $rfcrec){
          $sqlf = 'SELECT MAX(fr_id) FROM facturasrec';
          $resultf = setq($sqlf) or die($sqlf);
          list($maxf) = $resultf->fetch_array();
          $maxf++;

          $sqli = 'INSERT INTO facturasrec SET
                    fr_id = "'.$idfac.'", fr_falta = "'.date('Y-m-d H:i:s').'",
                    fr_carga = "'.$idfc.'", fr_ualta = "'.$_SESSION['uid'].'",
                    fr_empresa = "'.$_SESSION['emp'].'", fr_estatus = "N"';
          setq($sqli) or die($sqli);

          /*Se definen el encabezado para insertar la factura*/
          foreach ($xml->xpath('//cfdi:Comprobante') as $comprobante) {
            $serie = $comprobante['Serie'];
            $folio = $comprobante['Folio'];
            $fecha = $comprobante['Fecha'];
            $formapago = $comprobante['FormaPago'];
            $subtotal = $comprobante['SubTotal'];
            $moneda = $comprobante['Moneda'];
            $certificado = $comprobante['NoCertificado'];
            $condiciones = $comprobante['CondicionesDePago'];
            $descuento = $comprobante['Descuento'];
            $tipocambio = $comprobante['TipoCambio'];
            $total = $comprobante['Total'];
            $tipodecomprobante = $comprobante['TipoDeComprobante'];
            $metodopago = $comprobante['MetodoPago'];
            $lugarexp = $comprobante['LugarExpedicion'];
            $sello = $comprobante['Sello'];

            $sf = $serie.$folio;

            $ft = explode('T',$fecha);
            $fech = $ft[0].' '.$ft[1];

            $sqlu = 'UPDATE facturasrec SET
                    fr_serie = "'.$serie.'",
                    fr_folio = "'.$folio.'",
                    fr_fecha = "'.$fech.'",
                    fr_fpago = "'.$formapago.'",
                    fr_certificado = "'.$certificado.'",
                    fr_condiciones = "'.$condiciones.'",
                    fr_descuento = "'.$descuento.'",
                    fr_moneda = "'.$moneda.'",
                    fr_tipocambio = "'.$tipocambio.'",
                    fr_tipoc = "'.$tipodecomprobante.'",
                    fr_metodop = "'.$metodopago.'",
                    fr_lugarexp = "'.$lugarexp.'",
                    fr_subtotal = "'.$subtotal.'",
                    fr_total = "'.$total.'",
                    fr_sello = "'.$sello.'",
                    fr_fechatim = "'.$ftimbrado.'",
                    fr_nocertisat = "'.$certSat.'",
                    fr_uuid = "'.$uuid.'",
                    fr_sellosat = "'.$selloSAT.'"
                    WHERE fr_id = "'.$maxf.'"';
            setq($sqlu) or die($sqlu);
          }
          /*Se obtienen los datos del emisor de la factura*/
          foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Emisor') as $emisor) {
            $rfcr = $emisor['Rfc'];
            $usocfdi = $emisor['UsoCFDI'];
            $nmbprov = $emisor['Nombre'];

            $sqlu = 'UPDATE facturasrec SET
                    fr_rfcproveedor = "'.$rfcr.'",
                    fr_nmbproveedor = "'.$nmbprov.'"
                    WHERE fr_id = "'.$maxf.'"';
            setq($sqlu) or die($sqlu);
          }
          /*Se obtinen los datos del receptor de la factura */
          foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Receptor') as $receptor) {
            $rfcr = $receptor['Rfc'];
            $usocfdi = $receptor['UsoCFDI'];

            $sqlu = 'UPDATE facturasrec SET
                    fr_rfcreceptor = "'.$rfcr.'",
                    fr_usocfdi = "'.$usocfdi.'"
                    WHERE fr_id = "'.$maxf.'"';
            setq($sqlu) or die($sqlu);
          }
          /*Recorrido para recuprar los conceptos de la facrtura recibida*/
          $ArrayConceptos = array();
          foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Conceptos) {
            $ArrayConceptos[] = $Conceptos;
          }
          /*Insertar la información de los conceptos recuperados en el recorrido*/
          $Totales = count($ArrayConceptos);
          for($i=0;$i<$Totales;$i++){
            $ClaveProdServ = $ArrayConceptos[$i]['ClaveProdServ'];
            $Cantidad = $ArrayConceptos[$i]['Cantidad'];
            $ClaveUnidad = $ArrayConceptos[$i]['ClaveUnidad'];
            $Unidad = $ArrayConceptos[$i]['Unidad'];
            $concepto = $ArrayConceptos[$i]['Descripcion'];
            $ValorUnitario = $ArrayConceptos[$i]['ValorUnitario'];
            $descuento = $ArrayConceptos[$i]['Descuento'];

            $sqlmd = 'SELECT MAX(rd_id) FROM facturasrecd WHERE rd_factura = "'.$maxf.'"';
            $resultmd = setq($sqlmd) or die($sqlmd);
            list($mdf) = $resultmd->fetch_array();
            $mdf++;

            //$unidad = $ClaveUnidad;
            $simbol = array('"',"'");
            $concepto= str_replace($simbol,"",mb_strtoupper(trim($concepto)));
            /*Insertar detalles del concepto*/
            $sqld = 'INSERT INTO facturasrecd SET
                    rd_factura = "'.$maxf.'",
                    rd_id = "'.$mdf.'",
                    rd_claveuni = "'.$ClaveUnidad.'",
                    rd_unidad = "'.$Unidad.'",
                    rd_concepto = "'.$concepto.'",
                    rd_cantidad = "'.$Cantidad.'",
                    rd_descuento = "'.$descuento.'",
                    rd_costo = "'.$ValorUnitario.'",
                    rd_producto = "'.$ClaveProdServ.'"';
            setq($sqld) or die($sqld);

            /*Busqueda de la información de traslados del concepto a registrar*/
            $j=$i+1;
            $numtraslados = array();
            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto['.$j.']//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado') as $traslado) {
              $numtraslados[] = $traslado;
            }

            $Traslados = count($numtraslados);

            /*Insertar impuestos generados por concepto */
            for($t=0;$t<$Traslados;$t++){
              $Base = $numtraslados[$t]['Base'];
              $Impuesto = $numtraslados[$t]['Impuesto'];
              $TipoFactor = $numtraslados[$t]['TipoFactor'];
              $TasaOCuota = $numtraslados[$t]['TasaOCuota'];
              $Importe = $numtraslados[$t]['Importe'];

              $sqld = 'INSERT INTO facturasrec_impuestos SET
                      fi_facturas = "'.$maxf.'",
                      fi_idc = "'.$mdf.'",
                      fi_tipo = "T",
                      fi_base = "'.$Base.'",
                      fi_impuesto = "'.$Impuesto.'",
                      fi_tipofac = "'.$TipoFactor.'",
                      fi_tasaocuota = "'.$TasaOCuota.'",
                      fi_importe = "'.$Importe.'"';
              setq($sqld) ;
            }

            /*Busqueda de la información de retención del concepto a registrar*/
            $numreten = array();
            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto['.$j.']//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion ') as $retencion) {
              $numreten[] = $retencion;
            }

            $Retenciones = count($numreten);

            for($t=0;$t<$Retenciones;$t++){
              $Base = $numreten[$t]['Base'];
              $Impuesto = $numreten[$t]['Impuesto'];
              $TipoFactor = $numreten[$t]['TipoFactor'];
              $TasaOCuota = $numreten[$t]['TasaOCuota'];
              $Importe = $numreten[$t]['Importe'];

              $sqld = 'INSERT INTO facturasrec_impuestos SET
                      fi_facturas = "'.$maxf.'",
                      fi_idc = "'.$mdf.'",
                      fi_tipo = "R",
                      fi_base = "'.$Base.'",
                      fi_impuesto = "'.$TipoFactor.'",
                      fi_tipofac = "'.$Cantidad.'",
                      fi_tasaocuota = "'.$descuento.'",
                      fi_importe = "'.$Importe.'"';
              setq($sqld) ;
            }
          }//Fin del If para recorrer conceptos
          /*Recorrido para recuperar los impuestos de traslado de la factura recibida*/
          $numttr = array();
          foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado') as $totras) {
            $numttr[] = $totras;
          }
          /*Insertar los impuestos recuperados del recorrido*/
          $Tottras = count($numttr);
          for($tt=0;$tt<$Tottras;$tt++){
            $Importe = $numttr[$tt]['Importe'];

            $sqld = 'INSERT INTO facturasrec_totimp SET
                    fti_factura = "'.$maxf.'",
                    fti_tipo = "T",
                    fti_importe = "'.$Importe.'"
                    ON DUPLICATE KEY UPDATE
                    fti_importe = "'.$Importe.'"';
            if($tt == ($Tottras-1)){

            }
              setq($sqld) or die($sqld);
          }
          /*Recorrido para recuperar los impuestos de retenciones de la factura recibida*/
          $numtrt = array();
          foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion') as $totret) {
            $numtrt[] = $totret;
          }
          /*Insertar las retenciones recuperadas del recorrido*/
          $Totret = count($numtrt);
          for($tr=0;$tr<$Totret;$tr++){
            $Importe = $numtrt['Importe'];

            $sqld = 'INSERT INTO facturasrec_totimp SET
                    fti_factura = "'.$maxf.'",
                    fti_tipo = "R",
                    fti_importe = "'.$Importe.'"';
            if($tr == ($Totret-1)){

            }
              setq($sqld) or die($sqld);
          }

          if($tipodecomprobante == "P"){
            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Emisor') as $emisor) {
              $rfcr = $emisor['Rfc'];
              $usocfdi = $emisor['UsoCFDI'];
              $nmbprov = $emisor['Nombre'];

              $sqlu = 'UPDATE facturasrec SET
                      fr_rfcproveedor = "'.$rfcr.'",
                      fr_nmbproveedor = "'.$nmbprov.'"
                      WHERE fr_id = "'.$maxf.'"';
              setq($sqlu) or die($sqlu);
            }
          }
        }
      }
    }
  }
}
?>