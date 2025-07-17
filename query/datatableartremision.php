<?php
include_once('../funciones.php');
ini_set('display_errors',0);
/* session_start(); */

$articulo = $_POST['articulo'];
$modelo = $_POST['model'];
$arreglo = array();

$sql = 'SELECT rc_remision, r_folio, r_cliente, r_encargado, r_faplica, r_estatus FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision WHERE rc_articulo = "' . $articulo . '" AND rc_modelo = "' . $modelo . '" AND 
r_estatus IN ("A","F") AND rc_estatus IN ("N","A","P") GROUP BY rc_remision ORDER BY r_faplica DESC';
$result = setq($sql);

while ($registros = $result->fetch_array()) {

    /*  $sqlr = 'SELECT * FROM remisiones WHERE r_id = "'.$registros['rc_remision'].'"';
     $resultr = setq($sqlr);
     $remision = $resultr->fetch_array(); */
    $folio = $registros['r_folio'];
    $nmb = busca($registros['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
    $apellidos = busca($registros['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
    $cliente = $nmb.' '.$apellidos;
    $vendedor = busca($registros['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
    $faplica = $registros['r_faplica'];

    if ($registros['r_estatus'] == "A") {
        $estatus = "ABONADA";
    } else {
        $estatus = "LIQUIDADA";
    }
    $tenvio = '';

    $sqlr = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id  WHERE r_estatus IN ("A","F") AND
    rc_estatus != "F" AND rc_articulo = "' . $articulo . '" AND rc_modelo = "' . $modelo . '" AND cx_estatus IN ("A", "F")
    AND rc_remision = "' . $registros['rc_remision'] . '"';
    $resultr = setq($sqlr);
    list($nregistros) = $resultr->fetch_array();

    if ($nregistros == 1) {
        $nreg = $nregistros . " artículo";
    } else {
        $nreg = $nregistros . " artículos";
    }

    //Inica todas las paqueterias ocurre
    $sqlpaq0 = 'SELECT p_id, p_nmb FROM paqueterias';
    $resultpaq0 = setq($sqlpaq0);
    while ($rowpaq0 = $resultpaq0->fetch_array()) {
        $sqlr0 = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id  WHERE r_estatus IN ("A","F") AND
        rc_estatus != "F" AND rc_articulo = "' . $articulo . '" AND rc_modelo = "' . $modelo . '" AND cx_estatus IN ("A", "F")
        AND rc_remision = "' . $registros['rc_remision'] . '" AND rc_tipoenvio = "O" AND rc_paqueteria = "' . $rowpaq0['p_id'] . '"';
        $resultr0 = setq($sqlr0);
        list($nregistros0) = $resultr0->fetch_array();

        if ($nregistros0 > 0) {
            $tenvio .= $nregistros0 . " " . $rowpaq0['p_nmb'] . " (OCURRE)<br>";
        }
    }
    //Finaliza todas las paqueterias ocurre

    //Inica todas las paqueterias a domicilio
    $sqlpaq1 = 'SELECT p_id, p_nmb FROM paqueterias';
    $resultpaq1 = setq($sqlpaq1);
    while ($rowpaq1 = $resultpaq1->fetch_array()) {
        $sqlr1 = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id  WHERE r_estatus IN ("A","F") AND
        rc_estatus != "F" AND rc_articulo = "' . $articulo . '" AND rc_modelo = "' . $modelo . '" AND cx_estatus IN ("A", "F")
        AND rc_remision = "' . $registros['rc_remision'] . '" AND rc_tipoenvio = "D" AND rc_paqueteria = "' . $rowpaq1['p_id'] . '"';
        $resultr1 = setq($sqlr1);
        list($nregistros1) = $resultr1->fetch_array();

        if ($nregistros1 > 0) {
            $tenvio .= $nregistros1 . " " . $rowpaq1['p_nmb'] . "<br>";
        }
    }
    //Finaliza todas las paqueterias domicilio

    //Inica todos los artículos por definir
    $sqlr2 = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id  WHERE r_estatus IN ("A","F") AND
    rc_estatus != "F" AND rc_articulo = "' . $articulo . '" AND rc_modelo = "' . $modelo . '" AND cx_estatus IN ("A", "F")
    AND rc_remision = "' . $registros['rc_remision'] . '" AND rc_tipoenvio = "P"';
    $resultr2 = setq($sqlr2);
    list($nregistros2) = $resultr2->fetch_array();

    if ($nregistros2 > 0) {
        $tenvio .= $nregistros2 . " POR DEFINIR<br>";
    }
    //Finaliza todos los artículos por definir

    //Inica todos los artículos por definir
    $sqlr3 = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id  WHERE r_estatus IN ("A","F") AND
    rc_estatus != "F" AND rc_articulo = "' . $articulo . '" AND rc_modelo = "' . $modelo . '" AND cx_estatus IN ("A", "F")
    AND rc_remision = "' . $registros['rc_remision'] . '" AND rc_tipoenvio = "C"';
    $resultr3 = setq($sqlr3);
    list($nregistros3) = $resultr3->fetch_array();

    if ($nregistros3 > 0) {
        $tenvio .= $nregistros3 . " RECOGE CLIENTE<br>";
    }
    //Finaliza todos los artículos por definir

    $arreglo[] = array($folio, $cliente, $vendedor, $nreg, $estatus, $tenvio, $faplica);
}


$new_array = array("data" => $arreglo);
echo json_encode($new_array);
?>