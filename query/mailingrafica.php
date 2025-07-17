<?php
ini_set('display_errors',1);
include_once('../funciones.php');

/*
$sql = 'SELECT md_asunto, md_id FROM mailing_campanasd';
$result = setq($sql);
$data = array();
$data2 = array();
while($row = $result->fetch_array()){
  $data[] = $row['md_asunto'];
  //die(var_dump($data));
  $sql2 = 'SELECT COUNT(*) FROM campanas_conversiones WHERE cc_estatus = "N" AND cc_campanad = "'.$row['md_id'].'"';
  $result2 = setq($sql2);
  while($row2 = $result2->fetch_array()){
    $data2[] = $row2['COUNT(*)'];
    }
    

}

$respuesta = [
  "etiquetas" => $data,
  "datos" => $data2,
]; */


$ageneral = array();
$counts = array();
/*
$sql = 'SELECT DISTINCT(me_fecha) FROM mailing_envios WHERE me_fecha BETWEEN "'.date('Y-m-01').'" AND "'.date('Y-m-d').'"';
$result = setq($sql);
while($row = $result->fetch_array()){
  $fechas[] = $row['me_fecha'];
  $sql2 = 'SELECT COUNT(*) FROM mailing_envios WHERE me_fecha = "'.$row['me_fecha'].'"';
  $result2 = setq($sql2);
  while($row2 = $result2->fetch_array()){
    $counts[] = $row2['COUNT(*)'];
    }
  $sqlre = 'SELECT COUNT(*) FROM campanas_conversiones';
}
 

$respuesta = [
  "etiquetas" => $fechas,
  "datos" => $counts,
]; */

/*
$fechasr = array();
$sqlre = 'SELECT DISTINCT(me_fecha) FROM campanas_conversiones' */



$fechas = array();  
$fechainicio = date('Y-m-01');
$fechafinal = date('Y-m-d');

for($i = $fechainicio; $i <= $fechafinal; $i = date('Y-m-d', strtotime($i.' +1 day'))){
  $fechas[] = $i;
  $sql = 'SELECT COUNT(*) FROM mailing_envios WHERE me_fecha = "'.$i.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $data1[] = $row['COUNT(*)'];


  $sql2 = 'SELECT COUNT(*) FROM campanas_conversiones WHERE cc_estatus = "N" AND cc_fecha = "'.$i.'" AND cc_fuente = "M"';
  $result2 = setq($sql2);
  $row2 = $result2->fetch_array();
  $data2[] = $row2['COUNT(*)'];

  $sql3 = 'SELECT COUNT(*) FROM campanas_conversiones WHERE cc_estatus = "A" AND cc_fconversion = "'.$i.'" AND cc_fuente = "M"';
  $result3 = setq($sql3);
  $row3 = $result3->fetch_array();
  $data3[] = $row3['COUNT(*)'];
  
}


$returnData['line'] = array(
  'type' => 'line',
  'title' => 'Mailing entre: '.$fechainicio.' - '.$fechafinal.'',
  'labels' => $fechas,
  'datasets' => array(
    array(
      'data' => $data1,
      'borderColor' => "#f7464a",

      'label' => "Enviados"
    ),
    array(
      'data' => $data2,
      'borderColor' => "#8e5ea2",

      'label' => "Respuesta"
    ),
    array(
      'data' => $data3,

      'borderColor' => "#28badb",
      'label' => "Conversiones"
    )
  )
);




echo json_encode($returnData); 


?>
