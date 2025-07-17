<?php
include_once('../funciones.php');
ini_set('display_errors',1);
session_start();

$estatusfiltro = $_POST['estatusfiltro'];
$fecha = $_POST['fecha'];
$vendedor = $_POST['vendedor'];
$telefono = $_POST['telefono'];
$page = $_POST['page'];
$sqlf = '';
$caso = 0;
$arreglo = array();
$arreglonoperdidos = array();
$arregloexiste = array();
$arreglonoexiste = array();
$new_array = array();
$nombres = array();


// $nmbcompleto = busca($row['cl_vendedor'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
$sqlnmb = 'SELECT CONCAT(u_nmb, " ", u_apellidos) AS nombres, u_id FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A"';
$resultnmb = setq($sqlnmb);
while($rownmb = $resultnmb -> fetch_array()){
  $nombres[$rownmb['u_id']] = $rownmb['nombres'];
}
$sqlsaludo = 'SELECT u_saludo, u_grupo FROM usuarios WHERE u_id = "'.$_SESSION['uid'].'"';
$resultsaludo = setqemojis($sqlsaludo);
list($texto, $grupo) = $resultsaludo->fetch_array();

if(!empty($estatusfiltro)){
  $sqlf .= ' cl_estatus = "'.$estatusfiltro.'"';
} else{
  $sqlf .= ' cl_estatus IN ("R", "A")';
}
if($_SESSION['uid'] == "ALEXA") echo 'grupo: '.$grupo;
if($grupo == 'ADMIN' || $grupo == 'GERENCIA' || $grupo == "MERCA"){
  if(!empty($vendedor)){
    $sqlf .= ' AND cl_vendedor = "'.$vendedor.'"';
  } else{
    $sqlf .= '';
  }
} else{
  if(!empty($vendedor) && $vendedor != $_SESSION['uid']){
    $sqlf .= '';
    $caso = 1;
  } else{
    $sqlf .= ' AND cl_vendedor = "'.$_SESSION['uid'].'"';
  }
}

if($caso == 0){
/* $sql = 'SELECT * FROM crm_leads WHERE cl_fasigna = "'.date("Y-m-d").'" '.$sqlf.''; */
$bloque = 50;
$sql = 'SELECT * FROM crm_leads WHERE'.$sqlf.'';

if(!empty($fecha)) {
  $sql.=' AND cl_fasigna = "'.$fecha.'"';
} else{
  // Días que deseas sumar
  $dias_a_restar = 1;

  // Sumar los días a la fecha
  $nueva_fecha = date('Y-m-d', strtotime($fecha . ' - ' . $dias_a_restar . ' days'));
  $sql.=' AND cl_fasigna >= "'.$nueva_fecha.'"';
}
if(!empty($telefono)) {
  $sql.=' AND cl_telefono LIKE "%'.$telefono.'%"';
}

$sql.=' GROUP BY cl_telefono 
ORDER BY 
  CASE WHEN cl_acercamiento = 1 THEN 1 ELSE 0 END, 
cl_id DESC';
/* if($_SESSION['uid'] == "ADMIN"){
  $sql .=' LIMIT '.($bloque*$page).','.$bloque;
} */
$result = setq($sql);
$i = 0;
$bandera = 0;
$arropen = array("A","I");
/* echo $sql; */
while($row = $result->fetch_array()){
  $nmbcompleto = $nombres[$row['cl_vendedor']];
  $bandera = 1;
  $existe = busca($row['cl_telefono'], "crm_clientes", "c_telefono1 = '".$row['cl_telefono']."' OR c_telefono2", "c_id");
  if(!empty($existe)){
    $tablerof = intval(busca($existe, "crm_tableros", "ct_estatus != 'F' AND ct_cliente", "COUNT(*)"));
    if($tablerof > 0){
      $bandera = 0;
    } else{
      $bandera = 1;
    }
  }

  if($row['cl_acercamiento'] == 0){
    $check = "";
  } else{
    $check = " checked disabled";
    /* $acciones = '<center><span><em>Se hizo acercamiento a este lead</em></span></center>'; */
  }

    if($bandera == 1){
    $acciones = '
    <div class="row">
      <div class="col-5">
        <button type="button" onClick="iniciarTablero('.$row['cl_id'].');" class="btn btn-sm btn-success" data-toggle="tooltip" title="Iniciar un tablero"><i class="fas fa-book-open"></i> Iniciar</button>
      </div>
      <div class="col-7">
        <a data-fancybox data-type="ajax" data-src="popup/setperdido.php?id='.$row['cl_id'].'" href="javascript:;">
          <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Sin negociación"><i class="fas fa-user-slash"></i>Sin negociación</button>
        </a>
      </div>
    </div>';
    } else{
      /* $acciones = '<center><span><em>Lead con tablero abierto</em></span></center>'; */
      $acciones = '';
    }
    /* $texto = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_saludo'); */
    

    // Decodificar entidades HTML
    $html = html_entity_decode($texto);
    
    // Reemplazar saltos de línea con saltos de línea reales
    $texto = str_replace('<br>', "\n", $html);
    
    // Eliminar etiquetas HTML restantes
    $texto = strip_tags($texto); 

    $texto_codificado = urlencode($texto);

    $numero_de_telefono = "+".$row['cl_code'].$row['cl_telefono']; // Reemplaza con tu número de teléfono en formato internacional

    

    if(empty($texto)){
      /* $phone = $row['cl_telefono']; */
      $texton = "¡Hola, un gusto saludarte!";
      $texto_codificado = urlencode($texton);
    }
    $link_whatsapp = "https://api.whatsapp.com/send?phone=$numero_de_telefono&text=$texto_codificado";
    

    $phone = '<a href="'.$link_whatsapp.'" target="_blank">'.'+'.$row['cl_code'].$row['cl_telefono'].'&nbsp;
    <button style="border: 0px; background: white;" title="Contactar por WhatsApp">
        <i class="fab fa-whatsapp" style="font-size: 20px;"></i>
    </button>
    </a>';

  if($row['cl_estatus'] == "A"){
    $estatus = "Asignado";
  } else if($row['cl_estatus'] == "R"){
    $estatus = "Reasignado";
  } else {
    $estatus = "Sin negociación";
    /* $acciones = '<center><span><em>Lead "Sin negociación"</em></span></center>'; */
    $acciones = '';
  }

  $checkb = '<center><input class="form-check-input consult-check" onclick="marcarAcercamiento('.$row['cl_id'].')" type="checkbox" name="select'.$row['cl_id'].'" id="select'.$row['cl_id'].'" '.$check.'></center>';

  $hfasignacion = date('d-m-Y',strtotime($row['cl_fasigna'])).' '.$row['cl_hasigna'];

  
  if($grupo == "ADMIN" || $grupo == "GERENCIA" || $grupo == "MERCA"){
    $nmbcompleto = $nombres[$row['cl_vendedor']];
    if($estatusfiltro == 2 && !empty($existe)){
      $arregloexiste[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $nmbcompleto, $estatus, $hfasignacion, $checkb,  $acciones);
    } else if($estatusfiltro == 1 && $existe == 0){
      $arreglonoexiste[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $nmbcompleto, $estatus, $hfasignacion, $checkb,  $acciones);
    } else if($estatusfiltro == 3 && $existe == 0){
      $arreglonoperdidos[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $nmbcompleto ,$estatus, $hfasignacion, $checkb,  $acciones);
    } else{
      $arreglo[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $nmbcompleto, $estatus, $hfasignacion, $checkb,  $acciones);
    }
  } else{
    if($estatusfiltro == 2 && !empty($existe)){
      $arregloexiste[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $estatus, $hfasignacion, $checkb,  $acciones);
    } else if($estatusfiltro == 1 && $existe == 0){
      $arreglonoexiste[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $estatus, $hfasignacion, $checkb,  $acciones);
    } else if($estatusfiltro == 3 && $existe == 0){
      $arreglonoperdidos[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $estatus, $hfasignacion, $checkb,  $acciones);
    } else{
      $arreglo[] = array($row['cl_nmb'],$phone, $row['cl_correo'], $estatus, $hfasignacion, $checkb,  $acciones);
    }
  }
  
  
} //Fin WHILE
}
if($estatusfiltro == 1){
  $new_array  = array("data"=>$arreglonoexiste);
}else if($estatusfiltro == 2){
  $new_array  = array("data"=>$arregloexiste);
}else if($estatusfiltro == 3){
  $new_array  = array("data"=>$arreglonoperdidos);
} else{
  $new_array  = array("data"=>$arreglo);
}


echo json_encode($new_array);
?>