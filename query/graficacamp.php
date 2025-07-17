<?php
ini_set('display_errors',1);
include('../funciones.php');


function rand_color() {
  return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

$fecha = $_POST['fecha'];
$index = $_POST['index'];

$nombrescamp = array();
$coloresbg = array();



if($index == "1"){
  //$sql = 'SELECT DISTINCT(cc_campanad) FROM campanas_conversiones WHERE cc_fecha = "'.$fecha.'" AND cc_estatus = "N" AND cc_fuente = "M"';
  $sql = 'SELECT DISTINCT(cc_campanad), md_asunto  FROM campanas_conversiones INNER JOIN mailing_campanasd ON md_id = cc_campanad WHERE cc_fecha = "'.$fecha.'" AND cc_estatus = "N" AND cc_fuente = "M"';
  $result = setq($sql);
  while($row = $result->fetch_array()){
    $sql2 = 'SELECT COUNT(*) FROM campanas_conversiones INNER JOIN mailing_campanasd ON md_id = cc_campanad WHERE cc_campanad = "'.$row['cc_campanad'].'" AND cc_fecha = "'.$fecha.'" AND cc_estatus = "N" AND cc_fuente = "M"';
    $result2 = setq($sql2);
    $row2 = $result2->fetch_array();
    $data1[] = $row2['COUNT(*)']; 
    $coloresbg[] = rand_color();
    if($row2['COUNT(*)'] != NULL) $nombrescamp[] = $row['md_asunto'];
    
    
  }

  $returnData['pie'] = array(
    'type' => 'pie',
    'title' => 'Campanas Por Fecha '.$fecha.'',
    'labels' => $nombrescamp,
    
    'datasets' => array(
      array(
        'data' => $data1,
        'borderColor' => $coloresbg,
        'backgroundColor' => $coloresbg,
        'label' => "Enviados"
      )
    )
  );

}

if($index == "2"){
  //$sql = 'SELECT DISTINCT(cc_campanad) FROM campanas_conversiones WHERE cc_fecha = "'.$fecha.'" AND cc_estatus = "N" AND cc_fuente = "M"';
  $sql = 'SELECT DISTINCT(cc_campanad), md_asunto  FROM campanas_conversiones INNER JOIN mailing_campanasd ON md_id = cc_campanad WHERE cc_fconversion = "'.$fecha.'" AND cc_estatus = "A" AND cc_fuente = "M"';
  $result = setq($sql);
  while($row = $result->fetch_array()){
    $sql2 = 'SELECT COUNT(*) FROM campanas_conversiones INNER JOIN mailing_campanasd ON md_id = cc_campanad WHERE cc_campanad = "'.$row['cc_campanad'].'" AND cc_fconversion = "'.$fecha.'" AND cc_estatus = "A" AND cc_fuente = "M"';
    $result2 = setq($sql2);
    $row2 = $result2->fetch_array();
    $data1[] = $row2['COUNT(*)']; 
    $coloresbg[] = rand_color();
    if($row2['COUNT(*)'] != NULL) $nombrescamp[] = $row['md_asunto'];
    
    
  }

  $returnData['pie'] = array(
    'type' => 'pie',
    'title' => 'Campanas Por Fecha '.$fecha.'',
    'labels' => $nombrescamp,
    
    'datasets' => array(
      array(
        'data' => $data1,
        'borderColor' => $coloresbg,
        'backgroundColor' => $coloresbg,
        'label' => "Enviados"
      )
    )
  );

}



echo json_encode($returnData);


?>