<?php
include_once('../funciones.php');
$art = $_REQUEST['articulo'];
$articulo = getIDprod($art);
if(!$articulo) list($articulo, $modelo) = getIDprodVar($art);

$count = busca($articulo, 'articulos_produccion', 'apr_articulo', 'COUNT(*)');

echo $count;

?>