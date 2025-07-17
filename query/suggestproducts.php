<?php
session_start();
/* ini_set('display_errors', 1); */
include('../funciones.php');

$html = '';
$key = strtoupper($_POST['producto']);
if(!empty($key) && strlen($key) >= 3){
    $sqla = 'SELECT * FROM articulos
    WHERE (a_nmb LIKE "%'.strip_tags($key).'%" OR a_cb LIKE "%'.strip_tags($key).'%") AND a_estatus = "A" AND (SELECT COUNT(*) FROM articulos_variantes WHERE av_articulo = a_id) < 1 AND a_tipoprod != "M"';
    $resulta = setq($sqla);
    while ($rowa = $resulta->fetch_array()) {
      $html .= '<div class="suggest-element1"><a class="suggest-element" data="'.$rowa['a_nmb'].'" id="product'.$rowa['a_id'].'" value="'.$rowa['a_cb'].'">'.$rowa['a_nmb'].'</a></div>';
    }

    $sqlav = 'SELECT * FROM articulos_variantes INNER JOIN articulos ON a_id = av_articulo
            WHERE (av_nmb LIKE "%'.strip_tags($key).'%" OR av_cb LIKE "%'.strip_tags($key).'%" OR av_descripcion LIKE "%'.strip_tags($key).'%" OR a_nmb LIKE "%'.strip_tags($key).'%" OR a_modelo LIKE "%'.strip_tags($key).'%" OR a_cb LIKE "%'.strip_tags($key).'%") AND a_inventariado = "1" AND a_estatus = "A"';
    $resultav = setq($sqlav);
    $i = 0;
    while ($rowav = $resultav->fetch_array()) {
      $html .= '<div class="suggest-element1"><a class="suggest-element" data="'.$rowav['a_nmb'].' '.$rowav['av_nmb'].'" id="product'.$rowav['av_cb'].'">'.$rowav['a_nmb'].' '.$rowav['av_nmb'].'</a></div>';
      $i++;
    }
}

echo $html;
?>