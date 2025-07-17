<?php

ini_set('display_errors',1);
include('../funciones.php');

$idarticulo = $_POST['id'];
$artcategoria = $_POST['cat'];

$sql = 'SELECT * FROM articulosw WHERE aw_id = "'.$idarticulo.'"';
$result = setq($sql);
$row = $result->fetch_array();


$sqlconcep = 'SELECT cc_concepto FROM concepto_categoria WHERE cc_categoria = "'.$artcategoria.'"';
$resultconcep = setq($sqlconcep);
$rowconcep = $resultconcep->fetch_array();

$sqldep = 'SELECT dc_departamento FROM departamento_concepto WHERE dc_concepto = "'.$rowconcep['cc_concepto'].'"';
$resultdep = setq($sqldep);
$rowdep = $resultdep->fetch_array();

if($rowconcep['cc_concepto'] == NULL || $rowdep['dc_departamento'] == NULL){


  $r = 0;

}else{

 
  $sql = 'UPDATE articulosw SET
          aw_departamento = "'.$rowdep['dc_departamento'].'",
          aw_concepto = "'.$rowconcep['cc_concepto'].'"
          WHERE aw_id = "'.$idarticulo.'"';

  $sql2 = 'UPDATE articulos SET
          a_categoria = "'.$artcategoria.'"
          WHERE a_id = "'.$idarticulo.'"';

  setq($sql);
  setq($sql2);

  //echo $sql.' - '.$sql2;

  $r = 1;
}

//echo $row['aw_cb'].' - '.$rowconcep['cc_concepto'].' - '.$rowdep['dc_departamento']; 

echo $r;




?>