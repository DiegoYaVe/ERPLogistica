<?php
ini_set('display_errors',1);

include('../funciones.php');

function rand_color() {
  return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}


$idcl = $_POST['idcl'];

if(!empty($_POST['encargado'])){
  $encargado = $_POST['encargado'];
}

$fechainicio = date('Y-m-d', strtotime($_POST['finicio']));
$fechafinal = date('Y-m-d', strtotime($_POST['ffinal']));
//$fechainicio = date('Y-07-01');
//$fechafinal = date('Y-m-d');

$sql = 'SELECT c_id, c_alias, r_id, r_folio, r_faplica, r_total, r_estatus,  r_encargado FROM crm_clientes INNER JOIN remisiones ON r_cliente = c_id WHERE DATE(r_faplica) 
BETWEEN "'.$fechainicio.'" AND "'.$fechafinal.'" AND r_estatus = "A" AND c_id = "'.$idcl.'" ';
if(!empty($_POST['encargado'])) $sql .= ' AND r_encargado = "'.$encargado.'"';
$sql .= ' ORDER BY r_faplica ';
$result = setq($sql);
while($row = $result->fetch_array()){

  //$idcl[] = $row['c_id'];
  $fechas[] = $row['r_faplica'];
  $clientes[] = $row['c_alias'];
  $total[] = $row['r_total'];
  $coloresbg[] = rand_color();
  $labels[] = $clientes;
  $folio[] = $row['r_folio'];

}


$returnData['bar'] = array(
  'type' => 'bar',
  'title' => 'Folios de '.$clientes[0].' entre: '.$fechainicio.' - '.$fechafinal.'',
  'labels' => $folio,
  'datasets' => array(
    array(
      'data' => $total,
      'borderColor' => $coloresbg,
      'backgroundColor' => $coloresbg,
      'label' => "Total",
    )
  )
);

echo json_encode($returnData);



?>