<?php
include_once('../funciones.php');
//ini_set('display_errors', 1);
//foreachdie();
$datos = array();
$idcl = substr($_POST['idcl'], 6);
$tel1 = busca($idcl, 'crm_clientes', 'c_id', 'c_telefono1');
$nmb = busca($idcl, 'crm_clientes', 'c_id', 'c_nmb');
$apellidos = busca($idcl, 'crm_clientes', 'c_id', 'c_apellidos');
if(!$tel1) {
  $tel2 = busca($idcl, 'crm_clientes', 'c_id', 'c_telefono2');
  $nmbtabl = $tel2.' - '.date('Y-m-d');
  $tel = $tel2;
}
else {
  $nmbtabl = $tel1.' - '.date('Y-m-d');
  $tel = $tel1;
}

$datos['id'] = $idcl;
$datos['nmb'] = $nmb.' '.$apellidos; 
$datos['nmbtablero'] = $nmbtabl;
$datos['telefono'] = $tel; 

echo json_encode($datos);
?>