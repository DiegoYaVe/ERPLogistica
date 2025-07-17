<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$remision = $_POST['id'];
$paq = $_POST['paqueteria'];
$tenvio = $_POST['tenvio'];
$response= array();

$paqueteria = str_replace($tenvio, "", $paq);
$cotizacion = busca($remision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
$direnvio = intval(busca($cotizacion, 'crm_cotizaciones', 'cc_id', 'cc_direnvio'));

if($direnvio != 0){//Si tiene dirección de envío
$sqld = 'SELECT * FROM crm_direcciones WHERE cd_id = "'.$direnvio.'"';
$resultd = setq($sqld);
$row = $resultd->fetch_array();
if($tenvio == "O"){
  //Si tiene dirección de envío
  //Validamos que tenga código postal la direccion
  $cp = $row['cd_cp'];
  if(empty($cp)){
    $respuesta = 4;//El código postal está vacío
  } else{
    //Si hay codigo postal. Checamos cobertura
    $cob = busca(busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_cp'), 'paqueterias_cobertura', 'pc_paqueteria = "'.$paqueteria.'" AND pc_cp', 'pc_tipo');
    if($cob != "0"){
      $respuesta = 3; //Código postal sin zona de cobertura para OCURRE
    } else{
      $respuesta = 0; //Si hay cobertura
    }
  }
} else{
  //El tipo de envío es entrega a domicilio
  //Número exterior obligado
if (
    empty($row['cd_calle']) ||
    empty($row['cd_nume']) ||
    empty($row['cd_colonia']) ||
    empty($row['cd_municipio']) ||
    empty($row['cd_cp']) ||
    empty($row['cd_estado']) ||
    empty($row['cd_pais'])
) {
    $response['direnvio'] = $direnvio;
    $respuesta = 1; //Por lo menos un elemento está vacío
} else {
    $respuesta = 0; // Todos los elementos tienen valores, no hay ninguno vacío
}
}
} else{
  $respuesta = 2; //No tiene dirección de envío
}

$response['respuesta'] = $respuesta;
echo json_encode($response);
?>