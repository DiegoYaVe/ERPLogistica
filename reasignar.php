<?php
include_once('funciones.php');

$sql = 'SELECT * FROM crm_leads WHERE cl_estatus IN ("R","P") AND cl_fasigna <= "2024-03-23" ';
$result = setq($sql);
while($row = $result->fetch_array()){
  $sqlup = 'UPDATE crm_leads SET cl_estatus = "F" WHERE cl_id = "'.$row['cl_id'].'" ';
  setq($sqlup);
  // echo $sqlup.'<br>';

} 

?>