<?php
  session_start();
  ini_set();
  include('../funciones.php');
 

  $mp = $_POST['mp'];

  $sql = 'SELECT * FROM pr_materiaprima_proveedor WHERE mpp_materiaprima = "'.$mp.'" AND mpp_estatus = "1"';
  
  $result = setq($sql);
  $row = $result->fetch_array();

  echo $row['mpp_costo'];
  
  //echo $_POST['cb'].' '.$_POST['esquema'];
?>