<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$id = $_POST['id'];
$lead = $_POST['lead'];
$pais = $_POST['pais'];
$code = $_POST['code'];
$phone = $_POST['telefono'];

$telefono = preg_replace("/[^0-9]/", "", $phone); //Limpiamos el número de teléfono

$telCompleto = $code.$telefono;
$existe = intval(busca($code, "crm_leads", "cl_lead = '".$lead."' AND cl_id != '".$id."' AND cl_telefono = '".$telefono."' AND cl_code", "COUNT(*)"));

if($existe > 0){
  $respuesta = 1;
}

echo $respuesta;
?>