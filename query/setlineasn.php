<?php
session_start();
include_once('../funciones.php');

$cb = $_POST['cb'];

$sql = 'SELECT a_lineaneg, ln_nmb, ln_alias FROM articulos INNER JOIN lineas_negocio ON ln_id = a_lineaneg WHERE a_cb = "'.$cb.'" AND a_empresa = "'.$_SESSION['emp'].'" AND ln_estatus = "A"';
$result = setq($sql);
list($linean,$lnmb,$lnsiglas) = $result->fetch_array();

echo $linean;



?>