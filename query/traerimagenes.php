<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$folioarticulo = $_POST['articulo'];
$ordenp = $_POST['ordenp'];
$response = array();
$html = '';
if(isset($_POST['tipo'])){
    $rutas = '';
}

if(empty($ordenp)){
    $art = busca($folioarticulo, "articulos", "a_cb", "COUNT(*)");
    if($art > 0){
        $variante = 0; //No es variante
        $articulo = $folioarticulo;
    } else{
        $artvar = busca($folioarticulo, 'articulos_variantes', 'av_cb', 'COUNT(*)');
        if($artvar > 0){
            $articulo = busca($folioarticulo, 'articulos_variantes', 'av_cb', 'av_articulo');
            $modelo = busca($folioarticulo, 'articulos_variantes', 'av_cb', 'av_modelo');
            $variante = 1; //Es variante
        } else{
            $variante = 0; //No es variante
        }
    }

    if ($variante == 1) { //Es un artículo que es variante
        $sqldes = 'SELECT ad_ruta AS ruta FROM articulos_descargas WHERE ad_articulo = "' . $articulo . '" AND ad_modelo = "' . $modelo . '"';
    } else { //No es un artículo que es variante
        $sqldes = 'SELECT CONCAT("productos/",i_nmb, ".", i_ext) AS ruta FROM imagenes WHERE i_idproducto = "' . $articulo . '"';
    }
} else{
    $sqldes = 'SELECT po_adj AS ruta FROM pr_ordenprod WHERE po_id = "'.$ordenp.'"';
}

$resultdes = setq($sqldes);
$nrows = $resultdes->num_rows;
if ($nrows > 0) {
    $contador = 1;
    while ($rowdes = $resultdes->fetch_array()) {
        $ruta = "'img/" . $rowdes['ruta'] . "'";
        $ruta2 = $rowdes['ruta'];
        if(isset($_POST['tipo'])){
            if($contador == 1){
                $rutas .= $ruta;
            } else {
                $rutas .= ",".$ruta;
            }
        }
        $html .= '
        <div class="view overlay col-md-3 div-sel seleccionado-dos" id="div' . $contador . '" onclick="selDiv(' . $contador . ');" style="height: 220px; width: 220px;; background: url(' . $ruta . '); background-size: contain; background-position: center; background-repeat: no-repeat;" alt="Imágen descriptiva">
            <input type="hidden" id="in'.$contador.'" value="'.$ruta2.'">
        </div>';
        $contador++;
    }
    $respuesta = 1;
} else{
    $respuesta = 0;
}

$response['respuesta'] = $respuesta;
if(isset($_POST['tipo'])){
    $response['rutas'] = $rutas;
}
$response['html'] = $html;
echo json_encode($response);
?>