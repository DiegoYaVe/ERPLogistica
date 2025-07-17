<?php
session_start();
include_once('../funciones.php');
if($_SESSION['uid']){
  $idc = $_SESSION['uid'];
  $token = $_POST['token'];
  $sql = "UPDATE usuarios SET
          u_token = '".$token."'
          WHERE u_id = '".$idc."'";
  setq($sql);
  $r = 1;
}else $r = 2;
echo $r;
?>