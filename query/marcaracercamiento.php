<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$id = $_POST['id'];
$estatus = $_POST['estatus'];

$sql = 'UPDATE crm_leads SET
                cl_acercamiento = "' . $estatus . '"
                WHERE cl_id = "' . $id . '"';
setq($sql);
echo $respuesta;
?>