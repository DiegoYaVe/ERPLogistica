<?php
session_start();
//ini_set('display_errors',1);
include('../funciones.php');
$id = $_POST['id'];
$telefono = busca($id, 'crm_leads', 'cl_id', 'cl_telefono');
$query = 'SELECT c_alias FROM crm_clientes WHERE c_telefono1 = "'.$telefono.'" OR c_telefono2 = "'.$telefono.'"';
$result = setq($query);
list($alias) = $result->fetch_array();

echo $alias;
?>