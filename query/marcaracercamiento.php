<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$id = $_POST['id'];
$estatus = $_POST['estatus'];

$sql = 'UPDATE crm_leads SET
              cl_acercamiento = "'.$estatus.'",
              cl_fechalimite  = DATE_ADD(COALESCE(cl_fechalimite, cl_fasigna, CURDATE()), INTERVAL 7 DAY)
            WHERE cl_id = "'.$id.'"';
setq($sql);
echo $respuesta;
?>