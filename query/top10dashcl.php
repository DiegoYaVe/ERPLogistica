<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');


function rand_color() {
  return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}


//$fechainicio = date('Y-07-01');
//$fechafinal = date('Y-m-d');
$fechainicio = date('Y-m-d', strtotime($_POST['finicio']));
$fechafinal = date('Y-m-d', strtotime($_POST['ffinal']));

if(!empty($_POST['encargado'])){
  $encargado = $_POST['encargado'];
}

$contador = 0;
$concat = " ";
// AND c_empresa = "'.$_SESSION['emp'].'" 
$sql = 'SELECT c_id, c_alias, r_id, r_folio, r_faplica, SUM(r_total), r_estatus,  r_encargado FROM crm_clientes INNER JOIN remisiones ON r_cliente = c_id WHERE DATE(r_faplica) 
BETWEEN "'.$fechainicio.'" AND "'.$fechafinal.'" AND r_estatus = "A" '.$concat.' ';
if(!empty($_POST['encargado'])) $sql .= ' AND r_encargado = "'.$encargado.'"';
$sql .= ' GROUP BY c_id ORDER BY SUM(r_total) DESC LIMIT 0,'.(10-$contador).' ';
$result = setq($sql,true);
$j = 0;
while($row = $result->fetch_array()){

  $idcl[] = $row['c_id'];
  $fechas[] = $row['r_faplica'];
  $clientes[] = $row['c_alias'];
  $total[] = $row['SUM(r_total)'];
  $coloresbg[] = rand_color();
  $labels[] = $clientes;
  $folio[] = $row['r_folio'];

}


$returnData['bar'] = array(
  'type' => 'bar',
  'title' => 'TOP 10 Clientes entre: '.$fechainicio.' - '.$fechafinal.'',
  'labels' => $clientes,
  'datasets' => array(
    array(
      'data' => $total,
      'borderColor' => $coloresbg,
      'backgroundColor' => $coloresbg,
      'label' => "Total",
      'idcl' => $idcl 
    )
  )
);


echo json_encode($returnData); 


?>
