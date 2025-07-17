<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$ordenp = $_POST['ordenp'];
$proceso = $_POST['proceso'];
$operador = $_POST['operador'];
$tuser = $_POST['tuser'];
$tipo = $_POST['tipo'];
if($tipo == "iniciar"){
    $sql = 'UPDATE pr_procesosop SET pp_operador = "'.$operador.'", pp_toperador = "'.$tuser.'", pp_fini = "'.date("Y-m-d H:i:s").'", pp_estatus = "P" WHERE pp_id = "'.$proceso.'" AND pp_ordenp = "'.$ordenp.'"';
    setq($sql);
} else{
    $sql = 'UPDATE pr_procesosop SET pp_operador = "'.$operador.'", pp_toperador = "'.$tuser.'", pp_fini = "'.date("Y-m-d H:i:s").'", pp_ffin = "'.date("Y-m-d H:i:s").'", pp_estatus = "F" WHERE pp_id = "'.$proceso.'" AND pp_ordenp = "'.$ordenp.'"';
    setq($sql);
}

echo $respuesta;
?>