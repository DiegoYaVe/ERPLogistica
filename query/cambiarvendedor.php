<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$id = $_POST['id'];
$vendedor = $_POST['vendedor'];

$sqlcv = 'SELECT * FROM crm_leads WHERE cl_id = "' . $id . '"';
$result = setq($sqlcv);
$rowcv = $result->fetch_array();

$bandera = 1;
$existe = busca($rowcv['cl_telefono'], "crm_clientes", "c_telefono1 = '" . $rowcv['cl_telefono'] . "' OR c_telefono2", "c_id");
if (!empty($existe)) {
    $tablerof = intval(busca($existe, "crm_tableros", "ct_estatus != 'F' AND ct_cliente", "COUNT(*)"));
    if ($tablerof > 0) {
        $bandera = 0;
    } else {
        $bandera = 1;
    }
}

if ($bandera == 1) {
    $sql = 'UPDATE crm_leads SET cl_vendedor = "' . $vendedor . '" WHERE cl_id = "' . $id . '"';
    $result = setq($sql);
    if ($result) {
        $respuesta = 0;
    } else {
        $respuesta = 1;
    }
} else {
    $respuesta = 2;
}

echo $respuesta;
?>