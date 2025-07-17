<?php
//ini_set('display_errors', 1);
include('../funciones.php');

$categoria = mb_strtolower(trim($_REQUEST['categoria']));
$sql = 'SELECT MAX(a_noparte) FROM articulos
        WHERE a_noparte LIKE "'.$_REQUEST['acronimo'].mb_strtoupper($categoria).'%"
        AND a_empresa = "'.$_REQUEST['empresa'].'"';
$result = setq($sql);
list($idqc) = $result->fetch_array();
$idqc++;
if($idqc == "1"){
  $return = $_REQUEST['acronimo'].mb_strtoupper($categoria).str_pad($idqc,4,"0",STR_PAD_LEFT);
}else $return = str_pad($idqc,4,"0",STR_PAD_LEFT);

echo $return;
?>