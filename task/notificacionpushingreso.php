<?php
/* ini_set('display_errors', 1); */
include('../funciones.php');

$grupo = $_POST['grupo'];
$estatus = $_POST['estatus'];
$ingreso = $_POST['ingreso'];
$idcxc = $_POST['cxc'];
$cuerpo = $_POST['cuerpo'];

// Buscamos el ID de la remisión por su cxc

$remision = busca($idcxc, 'cxcobrar', 'cx_id', 'cx_referencia');
$folioremision = busca($remision, 'remisiones', 'r_id', 'r_folio');
$encargado = busca($remision, 'remisiones', 'r_id', 'r_encargado');
$existeingreso = intval(busca($ingreso, 'ingresos', 'i_estatus = "' . $estatus . '" AND i_id', 'COUNT(*)'));
if ($existeingreso > 0) {
  $sql = 'SELECT u_token, r_nmb FROM usuarios WHERE u_grupo = "' . $grupo . '" AND u_notificaciones = 1 AND u_estatus = "A" AND u_token != "" AND u_id = "'.$encargado.'"';
  $titulo = 'r_nmb';
  $notificationLink = '?modulo=remisiones&accion=show&id=' . $_POST['remision'];
  /* echo $sql."<br>"; */

  $result = setq($sql);
  $data = array();
  while ($row = $result->fetch_array()) {
    $data[] = $row['u_token'];
    $title[] = $row[$titulo];
    /* $body[] = $row[$cuerpo]; */
    $body[] = $cuerpo;
  }
  for ($i = 0; $i < count($data); $i++) {
    /* echo 'Titulo '.$i.': '.$title[$i]; */
    $serverkey = 'AAAA5f6Cr0I:APA91bG5YMlk5WkkRbzguEJByGsR9cnCAKwamh9PWo2VXKWVTaI5JAsC39wmWPtP9k7eA5dwTsRyiCBoO5WySvYTel-cvFX_CojnkDXm1RK81_70ai9qfTsyQQdMN9UoJ48loHlCoCeA';
    $url = 'https://fcm.googleapis.com/fcm/send';
    $field = array(
      'to' => $data[$i],
      'notification' => array(
        'title' => $title[$i],
        'body' => $body[$i],
        'click_action' => $notificationLink
      ),
    );

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