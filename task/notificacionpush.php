<?php
  ini_set('display_errors', 1);
  include('../funciones.php');

  /* $estatus = $_POST['estatus']; */
/*   $_POST['cotizacion'] = "10";
  $_POST['estatus'] = "V"; */
  $estatus = $_POST['estatus'];
  $sqlf = '';
  $cuerpo = '';
  $nmb = '';
  $tipo = '';
  
  if(isset($_POST['remision'])){  
    $tipo = "REMISIÓN: ";  
    /* $sql = 'SELECT u_token, r_nmb, r_observaciones, r_motivocan, r_encargado FROM usuarios INNER JOIN remisiones ON r_estatus = "'.$estatus.'" WHERE u_grupo = "'.$grupo.'" AND u_notificaciones = 1 AND u_estatus = "A" AND u_token != ""'.$sqlf; */
    $sqlr = 'SELECT r_folio, r_cliente, r_observaciones, r_motivocan, r_encargado FROM remisiones 
            WHERE r_estatus = "'.$estatus.'" AND r_id = "'.$_POST['remision'].'"';
    $resultr = setq($sqlr);
    list($nmb, $rcliente, $robs, $motivocan, $rencargado) = $resultr->fetch_array();
    $cliente = busca($rcliente, 'crm_clientes', 'c_id', 'CONCAT(c_nmb, " ", c_apellidos)');
   
    $titulo = "r_nmb";;
    $cuerpo = 'Remisión';
    $notificationLink = '?modulo=remisiones&accion=show&id='.$_POST['remision'];
    $sqlf = ' u_id = "'.$rencargado.'" ';
    if($estatus == "C"){
      /* $cuerpo = 'r_motivocan'; */
      $cuerpo .= ' cancelada';
    } else if($estatus == "A"){
      /* $cuerpo .= 'r_observaciones'; */
      $cuerpo = ' vendida';  
      $sqlf = ' u_grupo IN ("FINANZAS") ';    
    }else if($estatus == "P"){
      /* $cuerpo .= 'r_observaciones'; */
      $cuerpo = 'Ingreso a la remisión '.$nmb;
      $sqlf = ' u_grupo IN ("FINANZAS") ';
    } else{ //Estatus N
      $cuerpo = 'Nueva remisión creada';
    }
    if(isset($_POST['cuerpo'])){
      $cuerpo = $_POST['cuerpo'];
    }

  } else{
    $cotizacion = $_POST['cotizacion'];
    $tipo = "COTIZACION: ";  
    $sqlc = 'SELECT cc_folio, cc_destino, cc_observaenvio, cc_agente FROM crm_cotizaciones 
              WHERE cc_estatus = "'.$estatus.'" AND cc_id = "'.$_POST['cotizacion'].'"';
      $resultc = setq($sqlc);
      list($nmb, $cliente, $cobs, $cagente) = $resultc->fetch_array();

    if($estatus != "L"){
      $notificationLink = '?modulo=cotizaciones&accion=index&id='.$cotizacion;
    } else{
      $notificationLink = '?modulo=cotizacionessolicitud&accion=index';
    }
    $titulo = "cc_nmb";
    $cuerpo = 'Cotización';
    $sqlf = ' u_id = "'.$cagente.'" ';
    if($estatus == "C"){
      $cuerpo = ' cancelada';
    } else if($estatus == "A"){
      $cuerpo .= ' aplicada';
    } else if($estatus == "L"){
      $cuerpo .= ' enviada a logística';
      $sqlf = ' u_grupo IN ("LOGISTIC") ';
    } else{ //Estatus P
      $cuerpo = 'Solicitud de cotización a logística aprobada';
    }
    if(isset($_POST['cuerpo'])){
      $cuerpo = $_POST['cuerpo'];
    }
  }

  $sql = 'SELECT u_id, u_token FROM usuarios 
    WHERE '.$sqlf.'
    AND u_notificaciones = 1 AND u_estatus = "A" AND u_token != ""';

  $result = setq($sql);
  if($result->num_rows > 0){
  $data = array();
if($estatus == "L"){
  while($row = $result->fetch_array()){
    $data[] = $row['u_token'];
    $title[] = $tipo.$nmb."  CLIENTE: ".$cliente;
    /* $body[] = $row[$cuerpo]; */
    $body[] = $cuerpo;
  }
}else{
  while($row = $result->fetch_array()){
    $data[] = $row['u_token'];
    $title[] = $tipo.$nmb."  CLIENTE: ".$cliente;
    /* $body[] = $row[$cuerpo]; */
    $body[] = $cuerpo;
  }
}
  for($i = 0; $i < count($data); $i++){
    /* echo 'Titulo '.$i.': '.$title[$i];
    echo "<br>";
    echo 'body '.$i.': '.$body[$i]; */
    $serverkey ='AAAA5f6Cr0I:APA91bG5YMlk5WkkRbzguEJByGsR9cnCAKwamh9PWo2VXKWVTaI5JAsC39wmWPtP9k7eA5dwTsRyiCBoO5WySvYTel-cvFX_CojnkDXm1RK81_70ai9qfTsyQQdMN9UoJ48loHlCoCeA';
    $url = 'https://fcm.googleapis.com/fcm/send';
    $field = [
      'to'=> $data[$i],
      'notification'=>array(
        'title'=>$title[$i],
        'body'=>$body[$i],
        'click_action' => $notificationLink
      ),
    ];

    $fields = json_encode($field);
    $header = array(
      'Authorization: key='.$serverkey,
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