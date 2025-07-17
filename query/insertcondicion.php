<?php
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolutions_jdceo');
$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','inflalandia_fabricai');


if($_POST['marcado'] == "true"){
  $sqlmi = 'SELECT MAX(cf_id) FROM crm_cotizaciones_condiciones';
  $result = $mysqli->query($sqlmi) or die($sqlmi);
  list($idd) = $result->fetch_row();
  $idd++;

  $sqlo = 'SELECT MAX(cf_orden) FROM crm_cotizaciones_condiciones
            WHERE cf_cotizacion = "'.$_POST['cotizacion'].'"';
  $resulto = $mysqli->query($sqlo) or die($sqlo);
  list($orden) = $resulto->fetch_row();
  $orden++;

  $sqld = 'SELECT c_nmb FROM condicionesc WHERE c_id = "'.$_POST['condicion'].'"';
  $rest = $mysqli->query($sqld) or die($sqld);
  list($descripcion) = $rest->fetch_row();

  $sqli = 'INSERT IGNORE INTO crm_cotizaciones_condiciones SET
           cf_id = "'.$idd.'",
           cf_cotizacion = "'.$_POST['cotizacion'].'",
           cf_condicion = "'.$_POST['condicion'].'",
           cf_estatus = "A",
           cf_descripcion = "'.$descripcion.'",
           cf_orden = "'.$orden.'"';
  $mysqli->query($sqli) or die($sqli);
//  echo $sqli.'<';
}
else{
  $sqld = 'DELETE FROM crm_cotizaciones_condiciones
           WHERE cf_cotizacion = "'.$_POST['cotizacion'].'" AND cf_condicion = "'.$_POST['condicion'].'"';
  $mysqli->query($sqld) or die($sqld);
}


?>