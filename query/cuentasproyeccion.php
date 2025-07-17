<?php
ini_set('display_errors',1);
include_once('../funciones.php');

$idproy = $_POST['idproy'];
$id = $_POST['id'];

$existenenproy = busca($id,'cuentas_proyecciones','cp_proyeccion = "'.$idproy.'" AND cp_cuenta','cp_id');
if($existenenproy){
  $sql = 'DELETE FROM cuentas_proyecciones WHERE cp_id = "'.$existenenproy.'" ';
  setq($sql);
  echo '0';
}else{
  $sql = 'INSERT INTO cuentas_proyecciones SET
          cp_cuenta = "'.$id.'",
          cp_proyeccion = "'.$idproy.'"';
  setq($sql);
  echo '1';
}


?>