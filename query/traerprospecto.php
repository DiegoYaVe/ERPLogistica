<?php
/* ini_set('display_errors', 1); */
session_start();
include_once('../funciones.php');
include_once('../modulos/prospectos.php');

$id = $_POST['id'];
$bandera = 1;
$sqlcl = "SELECT * FROM crm_leads WHERE cl_id = '" . $id . "' AND cl_estatus IN ('P','A')";
$resultcl = setq($sqlcl);
$rowcl = $resultcl->fetch_array();
$existe = busca($rowcl['cl_telefono'], "crm_clientes", "c_telefono1 = '" . $rowcl['cl_telefono'] . "' OR c_telefono2", "c_id");
if (!empty($existe)) {
    $tablerof = intval(busca($existe, "crm_tableros", "ct_estatus != 'F' AND ct_cliente", "COUNT(*)"));
    if ($tablerof > 0) {
        $bandera = 0;
    } else {
        $bandera = 1;
    }
}

if ($bandera == 1) {
    $response['id'] = $rowcl['cl_id'];
    $response['lead'] = $rowcl['cl_lead'];
    $response['nmb'] = $rowcl['cl_nmb'];
    $response['cp'] = $rowcl['cl_cp'];
    $response['correo'] = $rowcl['cl_correo'];
    $response['telefono'] = $rowcl['cl_telefono'];
    $response['code'] = $rowcl['cl_code'];
    $response['pais'] = $rowcl['cl_pais'];
    $response['empresa'] = $rowcl['cl_empresa'];
    $response['comentarios'] = $rowcl['cl_comentarios'];
}
$response['respuesta'] = $bandera;


echo json_encode($response);
?>