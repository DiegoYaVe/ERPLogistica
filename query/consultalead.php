<?php
session_start();
ini_set('display_errors',0);
include('../funciones.php');
$id = $_POST['id'];
$response = array();
$query = 'SELECT * FROM crm_leads WHERE cl_id = "'.$id.'"';
$result = setq($query);
while($row = $result->fetch_array()){
  $response['nmb'] = $row['cl_nmb'];
  $response['telefono'] = $row['cl_telefono'];
  $response['correo'] = $row['cl_correo'];
  $response['cp'] = $row['cl_cp'];
  $response['observaciones'] = $row['cl_observacion'];
}

echo json_encode($response);
?>