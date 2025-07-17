<?php
  // En el caso probable de trabajar
  //con una base de datos se haria una consulta
  //para averiguar si el nombre esta libre o no
  include("../funciones.php");
  $alias = $_GET["q"];
  $idCl = $_GET['c'];

  $total = 0;
  $sql = 'SELECT c_alias FROM crm_clientes WHERE c_alias="'.mb_strtoupper($alias).'" AND c_id != "'.$idCl.'"';
  $res = setq($sql);
  $total = $res->num_rows;
      
  if ($total > 0)
  {echo "NO"; }
  else
  {echo "OK";}
?>