<?php
/* ini_set('display_errors', 1); */
include('../funciones.php');

$vendedor = $_POST['vendedor'];
$nleads = $_POST['nleads'];
$titulo = $_POST['titulo'];
$cuerpo = $_POST['cuerpo'];
$grupos = $_POST['grupos'];
$nmb = '';

$notificationLink = '?modulo=leadsperdidos&accion=index';


//Buscamos a todos los usuarios que son ADMINISTRADORES, GERENTES Y VENDEDORES a los que se les puede notificar
$sql = "SELECT u_token FROM usuarios 
        WHERE u_grupo IN (".$grupos.") AND u_id = '".$vendedor."'
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





?>