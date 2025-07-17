<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$folioarticulo = $_POST['articulo'];
$nmermas = 0;
$amid = 0;
$respuesta = 3; //No hay ordenes de producción del artículo
$response = array();

// Verificamos si el artículo es o no una variante
$art = busca($folioarticulo, "articulos", "a_cb", "COUNT(*)");
if($art > 0){
    $articulo = $folioarticulo;
    $modelo = '';
    $respuesta = 1;// No es una variante
} else{
    $artvar = busca($folioarticulo, 'articulos_variantes', 'av_cb', 'COUNT(*)');
    if($artvar > 0){
        $articulo = busca($folioarticulo, 'articulos_variantes', 'av_cb', 'av_articulo');
        $modelo = busca($folioarticulo, 'articulos_variantes', 'av_cb', 'av_modelo');
        $respuesta = 2; // Es es una variante
    } else{
        //No existe el artículo
        $respuesta = 0;
    }
}

$ordenesprod = '';
if($respuesta != 0){
    // Buscamos todas las ordenes de produccion de este artículo
    $sql = 'SELECT po_id FROM pr_ordenprod WHERE po_articulo = "'.$articulo.'" AND po_modelo = "'.$modelo.'"';
    $result = setq($sql);
    if($result->num_rows == 0){
        $respuesta = 3; //No hay ordenes de producción del artículo
    } else{
        $response['articulo'] = $articulo;
        $response['modelo'] = $modelo;
        $contador = 0;
        while($row = $result->fetch_array()){
            if($contador == 0){
                $ordenesprod .= '"'.$row['po_id'].'"';
            } else{
                $ordenesprod .= ',"'.$row['po_id'].'"';
            }
            $contador++;
        }

        $sqlmermas = 'SELECT am_cantidad, am_mp FROM articulos_mermas WHERE am_ordenp IN ('.$ordenesprod.') AND am_estatus = "A" GROUP BY am_mp, am_largo, am_ancho, am_alto ORDER BY am_id';
        $resultmermas = setq($sqlmermas);
        $nmermas = $resultmermas->num_rows;
        list($cantidad, $ammp) = $resultmermas->fetch_array();
        $respuesta = 4;
    }
}

$response['respuesta'] = $respuesta;
$response['nmermas'] = $nmermas;
$response['ammp'] = $ammp;


echo json_encode($response);
?>