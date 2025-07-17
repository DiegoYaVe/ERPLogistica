<?php
session_start();
ini_set('display_errors', 0);
include('../funciones.php');

$html = '';
$key = strtoupper($_POST['producto']);
if(!empty($key) && strlen($key) >= 3){    
  if($_POST['from'] != "articulos") $sqladd = ' AND (SELECT COUNT(*) FROM articulos_variantes WHERE av_articulo = a_id) < 1';
  else $sqladd = " AND a_tipoprod != 'M'";
  if($_POST['desde'] == "remision"){
    $sqladd = ' AND a_categoria NOT IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
    $_POST['from'] = 'articulos';
  }
  if(isset($_POST['excepcion'])) $sqlremove = 'AND a_id != "'.$_POST['excepcion'].'"';
  else $sqlremove = '';
  $sqla = 'SELECT * FROM articulos
    WHERE (a_nmb LIKE "%'.strip_tags($key).'%" OR a_cb LIKE "%'.strip_tags($key).'%") AND a_estatus = "A" AND a_inventariado = "1" '.$sqladd.' '.$sqlremove.'';
  $resulta = setq($sqla);
  while ($rowa = $resulta->fetch_array()) {
    $html .= '<div class="suggest-element1"><a class="suggest-element" data="'.$rowa['a_nmb'].'" id="product'.$rowa['a_id'].'" value="'.$rowa['a_cb'].'">'.$rowa['a_nmb'].'</a></div>';
  }

  if($_POST['from'] != "articulos"){
    $sqlav = 'SELECT * FROM articulos_variantes INNER JOIN articulos ON a_id = av_articulo
            WHERE (av_nmb LIKE "%'.strip_tags($key).'%" OR av_cb LIKE "%'.strip_tags($key).'%" OR av_descripcion LIKE "%'.strip_tags($key).'%" OR a_nmb LIKE "%'.strip_tags($key).'%" OR a_modelo LIKE "%'.strip_tags($key).'%" OR a_cb LIKE "%'.strip_tags($key).'%") AND a_estatus = "A"';
    $resultav = setq($sqlav);
    $i = 0;
      while ($rowav = $resultav->fetch_array()) {
        $html .= '<div class="suggest-element1"><a class="suggest-element" data="'.$rowav['a_nmb'].' '.$rowav['av_nmb'].'" id="product'.$rowav['av_cb'].'" value="'.$rowav['av_cb'].'">'.$rowav['a_nmb'].' '.$rowav['av_nmb'].' </a></div>';
          $i++;
    }
  }
}
         
echo $html;
?>