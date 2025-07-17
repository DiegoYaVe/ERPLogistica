<?php
session_start();
//ini_set('display_errors', 1);
include('../funciones.php');

$html = '';
$key = strtoupper($_POST['producto']);
if(!empty($key) && strlen($key) >= 3){    
  $sqla = 'SELECT * FROM pr_materiaprima
    WHERE (pmp_nmb LIKE "%'.strip_tags($key).'%" OR pmp_material LIKE "%'.strip_tags($key).'%") AND pmp_estatus = "A"';
  $resulta = setq($sqla);
  while ($rowa = $resulta->fetch_array()) {
    $html .= '<div class="suggest-element1"><a class="suggest-element" data="'.$rowa['pmp_nmb'].'" id="product'.$rowa['pmp_id'].'" value="'.$rowa['pmp_id'].'">'.$rowa['pmp_nmb'].'</a></div>';
  }
} 

echo $html;
?>