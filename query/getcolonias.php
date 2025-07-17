<?php 
//ini_set('display_errors', 1);
include_once("../funciones.php");
//foreachdie();
$cp = $_POST['cp'];
$colonias = array();
$i = 0;
$sql = 'SELECT ccp_colonia FROM cfdi_codigopostal WHERE ccp_codigo = "'.$cp.'" ORDER BY ccp_colonia ASC';
$result = setq($sql);
while($row = $result -> fetch_array()){
  $colonias['colonias'][$i] = $row['ccp_colonia'];
  $i++;
} 

$i = 0;
$sql2 = 'SELECT ccp_clestado, ccp_estado FROM cfdi_codigopostal WHERE ccp_codigo = "'.$cp.'" GROUP BY ccp_estado';
$result = setq($sql2);
while($row = $result -> fetch_array()){
  $colonias['estados'][$i] = $row['ccp_estado'];
  $colonias['estadosid'][$i] = $row['ccp_clestado'];
  $i++;
} 

$i = 0;
$sql3 = 'SELECT ccp_municipio FROM cfdi_codigopostal WHERE ccp_codigo = "'.$cp.'" GROUP BY ccp_municipio';
$result = setq($sql3);
while($row = $result -> fetch_array()){
  $colonias['municipios'][$i] = $row['ccp_municipio'];
  $i++;
} 

$cober = busca($cp, 'paqueterias_cobertura', 'pc_cp', 'pc_tipo');
$colonias['cobertura'][$i] = $cober;


echo json_encode($colonias);
?>