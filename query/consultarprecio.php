<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$id = $_POST['id']; //Elemento del checkbox seleccionado
$nreg = $_POST['nreg']; //Numero de checkbox seleccionados
$nval = $_POST['nval']; //Valores de los checkbox seleccionados

$elementos = explode(",", $nval);

$remision = busca($id, 'remisionesc', 'rc_id', 'rc_remision');

$sql = 'SELECT cx_importe, cx_abonado, cx_estatus FROM remisiones INNER JOIN cxcobrar ON cx_referencia = r_id 
        WHERE r_id = "'.$remision.'" AND r_estatus = "A" AND cx_estatus = "A"';
$result = setq($sql);
if($result->num_rows > 0){
    //Buscamos el artículo, modelo, y si pertenece a un combo del registro de remisionesc
    $sqlrc = 'SELECT rc_remisiond, rc_combo FROM remisionesc WHERE rc_id = "'.$id.'" AND rc_estatus IN ("N")';
    $resultrc = setq($sqlrc);
    $rowrc = $resultrc->fetch_array();
    if($rowrc['rc_combo'] == 1){ 
        // Es un combo
        $respuesta  = 2; //No es posible enviar artículos de un paquete en entrega parcial
    } else{
    // No es un combo
        $row = $result->fetch_array();
        $importe = floatval($row['cx_importe']);
        $abonado = floatval($row['cx_abonado']);
        
        //Buscamos si existen otros artículos marcados como envío parcial previamente.
        $sqlrd = 'SELECT rc_remisiond FROM remisionesc WHERE rc_remision = "'.$remision.'" AND rc_estatus IN ("A", "P", "F")';
        $resultrd = setq($sqlrd);
        $costo0 = 0;
        while($rowrd = $resultrd->fetch_array()){
            $costord0 = floatval(busca($rowrd['rc_remisiond'], 'remisionesd', 'rd_id', 'rd_precio'));
            $costo0 += $costord0 + ($costord0 * (5/100));
        }
        //$abonado = $abonado - $costo0;
            
        //Todos los checkbox seleccionamos que no están deshabilitados los tomamos encuenta
        $costo1 = 0;
        for ($i = 0; $i < count($elementos); $i++) {
            $sqlrdf = 'SELECT rd_precio FROM remisionesd INNER JOIN remisionesc ON rc_remisiond = rd_id WHERE rc_id = "'.$elementos[$i].'"';
            $resultrdf = setq($sqlrdf);
            list($costordf) = $resultrdf->fetch_array();
            $ccosto = floatval($costordf);
            $costo1 += $ccosto + ($ccosto * (5/100));
        }

        //$abonado = $abonado - $costo1;

        $costo = floatval(busca($rowrc['rc_remisiond'], 'remisionesd', 'rd_id', 'rd_precio'));
        //$costo = $costord + ($costord * (5/100));
        //if($_SESSION['uid'] == "ADMIN") echo 'abonado: '.$abonado.' costo: '.$costo.' costo0:'.$costo0;
        if($abonado >= $costo){
            $repuesta = 0; // Se cumplen las condiciones para realizar el envío parcial
        } else{
            $respuesta = 3; // El importe abonado es menor que el requerido para hacer el envío parcial
        }
    }
} else{
    $respuesta = 1; //No se encontró la remisión correspondiente a la entrega parcial.
}


if($existe > 0){
  $respuesta = 1;
}

echo $respuesta;
?>