<?php
ini_set('display_errors', 0);
session_start();
include_once('../funciones.php');

$vendedorc = $_POST['vendedor'];

$response = array();
$usuariosventas = array();
$usuariosventas2 = array();
$txtrespuesta = "Leads reasignados con éxito";
$respuesta = 0;

$sqlu = "SELECT u_id FROM usuarios INNER JOIN usuarios_orden ON uo_uid = u_id WHERE u_estatus = 'A' AND u_grupo = 'VENTAS' ORDER BY uo_orden ASC";
$resultu = setq($sqlu);
$nvendedoresv = $resultu->num_rows;
if ($nvendedoresv > 0) { //Si hay vendedores activos para asignar prospectos
    $lead = busca('A', 'crm_capturaleads', 'cc_estatus', 'cc_id');
    if (!empty($lead)) {
        //Si el prospecto ya tuvo un registro previo se le asigna el asesor que lo atendió en esa ocasión
        while ($rowu = $resultu->fetch_array()) {
            array_push($usuariosventas, $rowu['u_id']);
        }
        $sqlvd = 'SELECT * FROM crm_leads WHERE cl_estatus = "X"';
        $resultvd = setq($sqlvd);

        while ($rowvd = $resultvd->fetch_array()) {
            $id = $rowvd['cl_id'];
            if (count($usuariosventas2) == 0) {
                $usuariosventas2 = $usuariosventas;
            }
            //Extraemos el primer elemento del arreglo
            $vendedor = $usuariosventas2[0];
            // Quitar el primer elemento
            array_shift($usuariosventas2);

            $sqlupd = 'UPDATE crm_leads SET cl_vendedor = "' . $vendedor . '", cl_fasigna = "' . date('Y-m-d') . '", cl_hasigna = "' . date('H:i:s') . '", cl_acercamiento = "0", cl_estatus = "R", cl_lead = "'.$lead.'"
                        WHERE cl_id = "' . $id . '"';
            setq($sqlupd);
        }
    } else {
        $respuesta = 704;
        $txtrespuesta = "No hay una hoja abierta para reasignar los leads";
    }
} else{
    $respuesta = 1;
    $txtrespuesta = "No hay vendedores activos para asignar prospectos";
} 

$response['respuesta'] = $respuesta;
$response['txtrespuesta'] = $txtrespuesta;
echo json_encode($response);
?>