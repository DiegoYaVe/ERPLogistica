<?php
/* ini_set('display_errors', 1); */
session_start();
include_once('../funciones.php');

$vendedorc = $_POST['vendedor'];

$response = array();
$usuariosventas = array();
$usuariosventas2 = array();
$respuesta = 0;
$txtrespuesta = "Leads reasignados con éxito";
if (isset($_SESSION['uid'])) {
    $respuesta = 0;

    $sqlvr = 'UPDATE usuarios SET u_estatus = "I" WHERE u_id = "' . $vendedorc . '"';
    $resultvr = setq($sqlvr);

    $orden = busca($vendedorc, "usuarios_orden", "uo_uid", "uo_orden");
    $registro = busca($vendedorc, "usuarios_orden", "uo_uid", "COUNT(*)");
    if ($registro > 0) {
        $sql = "DELETE FROM usuarios_orden WHERE uo_uid = '" . $vendedorc . "'";
        setq($sql);
        $sql1 = "SELECT uo_uid FROM usuarios_orden WHERE uo_orden > " . $orden;
        $result1 = setq($sql1);
        while ($row = $result1->fetch_array()) {
            $sqlo = "UPDATE usuarios_orden SET uo_orden= '" . $orden . "' WHERE uo_uid = '" . $row['uo_uid'] . "'";
            setq($sqlo);
            $orden++;
        }
    }

    $sqlu = "SELECT u_id FROM usuarios INNER JOIN usuarios_orden ON uo_uid = u_id WHERE u_estatus = 'A' AND u_grupo = 'VENTAS' ORDER BY uo_orden ASC";
    $resultu = setq($sqlu);
    $nvendedoresv = $resultu->num_rows;
    if ($nvendedoresv > 0) { //Si hay vendedores activos para asignar prospectos

        $lead = busca('A', 'crm_capturaleads', 'cc_fini = "'.date("Y-m-d").'" AND cc_estatus', 'cc_id');
        if (!empty($lead)) {

            //Si el prospecto ya tuvo un registro previo se le asigna el asesor que lo atendió en esa ocasión
            while ($rowu = $resultu->fetch_array()) {
                array_push($usuariosventas, $rowu['u_id']);
            }

            $sqlvd = 'SELECT * FROM crm_leads WHERE cl_vendedor = "' . $vendedorc . '"';
            /*
            echo $sqlvd;
            echo '<br>';
            echo '<br>'; 
            */
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
    
} else {
    $respuesta = 247; //La sesión caducó
    $txtrespuesta = "La sesión caducó";
}

$response['respuesta'] = $respuesta;
$response['txtrespuesta'] = $txtrespuesta;
echo json_encode($response);
?>