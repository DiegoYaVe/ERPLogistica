<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$idguia = $_POST['idguia'];
$nmb = $_POST['nmb'];
$paqueteria = $_POST['paqueteria'];
$costo = $_POST['costo'];


$registros = intval(busca($idguia, 'crm_cotizacionesc', 'ccc_guia', 'COUNT(*)'));
$paqueteriag = busca($idguia, 'guias_articulos', 'ga_id', 'ga_paqueteria');
if($paqueteriag == $paqueteria){
    $registros = 0; //Cambia solo el nombre y no la paqueteria
}


if($registros == 0){
    $result = busca($nmb, 'guias_articulos', 'ga_nmb', 'ga_id');
    if(!empty($result)){
        if($result == $idguia){
            $registros = 0;
            $respuesta = 1; //E la misma guía
        } else{
            $registros = 1;
            $respuesta = 4; //Ya hay un registro perteneciente a esa guía
        }
      } else{
        $respuesta = 1;
        $registros = 0; //No hay ninguna guía registrada con ese nombre
      }
}




if($registros == 0){
    $sql = 'UPDATE guias_articulos SET ga_paqueteria = "'.$paqueteria.'", ga_nmb = "'.$nmb.'", ga_costo ="'.$costo.'" WHERE ga_id = "'.$idguia.'"';
    $result = setq($sql);
    if($result){
        $respuesta = 1;
    } else{
        $respuesta = 0;
    }
} else{
    if($respuesta != 4){
        $respuesta = 3; //Para poder editar el "Tipo de entrega" es necesario quitar los artículos que se encuentran en la guía debido a que la paquetería es diferente
    }
}

echo $respuesta;
?>