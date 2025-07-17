<?php
session_start();
ini_set('display_errors',0);
include_once('../funciones.php');
include('../modulos/ordenprod.php');
$respuesta = 1;
$estatus = $_POST['estatus'];
$ordenp = $_POST['ordenp'];
$proceso = $_POST['proceso'];

//Buscamos el estatus actual del proceso
$_GET['tipo'] = $estatus;

$_GET['idp'] = $ordenp;
$_GET['proceso'] = $proceso;
$accion = new ordenprod();

$metodo = $accion->verarticulosdinamico();

echo $metodo;
?>