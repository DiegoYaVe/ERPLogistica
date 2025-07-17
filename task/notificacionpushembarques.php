<?php
/* ini_set('display_errors', 1); */
include('../funciones.php');

$tipoe = $_POST['tipoe'];
$grupos = $_POST['grupos'];
$arrayrem = array();
$arraycant= array();
$arrayremisiones = array();
if($tipoe == "C"){ //Recoge cliente
    $cccid = $_POST['ccid'];
    $array_cccid = explode(",", $cccid);
    $cantidadinicial = count($array_cccid);

    for($i = 0; $i < $cantidadinicial; $i++){
        $remision = busca($array_cccid[$i], 'remisionesd', 'rd_id', 'rd_remision');
        array_push($arrayremisiones, $remision);
    }

    // Cuenta la cantidad de veces que se repite cada elemento en el arreglo
    $conteo = array_count_values($arrayremisiones);

    foreach ($conteo as $elemento => $cantidad) {
        array_push($arrayrem, $elemento); //ID Cotización 
        array_push($arraycant, $cantidad); // Cantidad de veces que se repite
    }

} else{ // Por guía
    $remisiones = $_POST['cotizaciones'];
    $array_remisiones = explode(",", $remisiones);
    $cantidadinicial = count($array_remisiones);

    for($i = 0; $i < $cantidadinicial; $i++){
        $remision = busca($array_remisiones[$i], 'guias_articulos', 'ga_id', 'ga_cotizacion');
        array_push($arrayremisiones, $remision);
    }

    // Cuenta la cantidad de veces que se repite cada elemento en el arreglo
    $conteo = array_count_values($arrayremisiones);

    foreach ($conteo as $elemento => $cantidad) {
        //echo "El elemento '{$elemento}' se repite {$cantidad} veces.<br>";
        array_push($arrayrem, $elemento);
        array_push($arraycant, $cantidad);
    }
}
$cantidad = count($arrayrem);

for($k = 0; $k < $cantidad; $k++){

    if($tipoe == "C"){ //Recoge cliente
        $vendedor = busca($arrayrem[$k], 'remisiones', 'r_id', 'r_encargado');
        $folio = busca($arrayrem[$k], 'remisiones', 'r_id', 'r_folio');
        $sqlf = " AND u_id = '".$vendedor."' ";
        $titulo = $folio;
        $cuerpo = $arraycant[$k]." artículos embarcados de la remisión ".$folio;
    } else{ //Por guía
        $vendedor = busca($arrayrem[$k], 'remisiones', 'r_id', 'r_encargado');
        $folio = busca($arrayrem[$k], 'remisiones', 'r_id', 'r_folio');
        $sqlf = " AND u_id = '".$vendedor."' ";
        $titulo = $folio;
        if(intval($arraycant[$k]) > 0){
            $cuerpo = $arraycant[$k]." guía embarcada de la remisión ".$folio;
        } else{
            $cuerpo = $arraycant[$k][1]." guías embarcadas de la remisión ".$folio;
        }        
    }
//Buscamos a todos los usuarios que son ADMINISTRADORES o GERENTES a los que se les puede notificar
$sql = "SELECT u_token FROM usuarios 
        WHERE u_grupo IN (".$grupos.") ".$sqlf."
        AND u_notificaciones = 1 AND u_estatus = 'A' AND u_token != ''";
$result = setq($sql);
if ($result->num_rows > 0) {

    $data = array();
    while ($row = $result->fetch_array()) {
        $data[] = $row['u_token'];
    }

    for ($i = 0; $i < count($data); $i++) {
        $serverkey = 'AAAA5f6Cr0I:APA91bG5YMlk5WkkRbzguEJByGsR9cnCAKwamh9PWo2VXKWVTaI5JAsC39wmWPtP9k7eA5dwTsRyiCBoO5WySvYTel-cvFX_CojnkDXm1RK81_70ai9qfTsyQQdMN9UoJ48loHlCoCeA';
        $url = 'https://fcm.googleapis.com/fcm/send';
        $field = [
            'to' => $data[$i],
            'notification' => array(
                'title' => $titulo,
                'body' => $cuerpo,
                'click_action' => $notificationLink
            ),
        ];

        $fields = json_encode($field);
        $header = array(
            'Authorization: key=' . $serverkey,
            'Content-Type: application/json'
        );


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        echo $result;
        echo $data[$i];
        curl_close($ch);
    }
}
}




?>