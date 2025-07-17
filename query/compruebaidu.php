<?php

// En el caso probable de trabajar
//con una base de datos se haria una consulta
//para averiguar si el nombre esta libre o no
include("../funciones.php");
$user=$_GET["q"];

$total=0;
$sql='SELECT u_id FROM usuarios WHERE u_id="'.mb_strtoupper($user).'"';
$res=setq($sql);
$total=$res->num_rows;
     
if ($total > 0)
{echo "NO"; }
else
{echo "OK";}
?>