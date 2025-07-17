<?php 
session_start();
include_once('../funciones.php');
ini_set('display_errors',1);

$siglas = trim($_POST['siglas']);

$existensiglas = buscajdsuite($siglas,'empresas','e_siglas','e_id');
if($existensiglas){
  $r = "1";
}else{
  $r = "0";
}

echo $r;

?>