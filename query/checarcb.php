<?php
//ini_set('display_errors', 1);
include('../funciones.php');

$code = $_POST['cb'];

$art = busca($code, 'articulos', 'a_cb', 'COUNT(*)');
$var = busca($code, 'articulos_variantes', 'av_cb', 'COUNT(*)');

if($art > 0 || $var > 0){
  $acronimo = busca(1,'empresas','e_id','e_siglas');
  $sqln = 'SELECT max(cb) cb FROM (SELECT max(av_cb) AS cb FROM articulos_variantes UNION select MAX(a_cb) FROM articulos) as maxcode';
  $resultn = setq($sqln);
  list($cb) = $resultn -> fetch_array();

  if($resultn -> num_rows > 0){
    $max = intval(str_replace('FDI', '', $cb));
    $max++;
    $cb = $acronimo.str_pad($max,6,"0",STR_PAD_LEFT);
  }
  else {
    $cb = $acronimo.str_pad(1,6,"0",STR_PAD_LEFT);
  }
} else {
  $cb = 0;
}

echo $cb;
?>