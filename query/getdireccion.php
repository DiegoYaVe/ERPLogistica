<?php 
//ini_set('display_errors', 1);
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
include_once("../funciones.php");

$dir = $_POST['direccion'];
$datos = array();
$sqlget = 'SELECT cd_cp, cd_colonia, cd_municipio, cd_estado, cd_calle, cd_nume, cd_numi, cd_pais FROM crm_direcciones WHERE cd_id ="'.$dir.'"';
$resultget = setq($sqlget);
list($cp, $colonia, $municipio, $estadoh, $calle, $nume, $numi, $pais) = $resultget->fetch_array();
$estado =busca($estadoh, 'estado', 'e_id', 'e_nmb');

$datos['cp']= $cp;
$datos['colonia']=$colonia ;
$datos['municipio']= $municipio;
$datos['estado']= $estado;
$datos['estadoh']= $estadoh;
$datos['calle']= $calle;
$datos['nume']= $nume;
$datos['numi']= $numi;
$datos['pais']= $pais;

echo json_encode($datos);
?>