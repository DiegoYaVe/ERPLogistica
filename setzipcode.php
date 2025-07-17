<?php
include('funciones.php');

/* Orden de campos aceptados
0d_codigo|1d_asenta|2d_tipo_asenta|3D_mnpio|4d_estado|5d_ciudad|d_CP|c_estado|c_oficina|c_CP|c_tipo_asenta|c_mnpio|id_asenta_cpcons|d_zona|c_cve_ciudad
*/
$fp = fopen("assets/catalogo-cp.txt", "r");
$i = 0;
while (!feof($fp)){
  $i++;
  $linea = fgets($fp);
  $codigo = explode('|',$linea);

  $sql = 'INSERT INTO cfdi_codigopostal SET
          ccp_codigo = "'.$codigo[6].'",
          ccp_clcolonia = "'.utf8_encode($codigo[10]).'",
          ccp_colonia = "'.utf8_encode($codigo[1]).'",
          ccp_clestado = "'.utf8_encode($codigo[7]).'",
          ccp_estado = "'.utf8_encode($codigo[4]).'",
          ccp_cllocalidad = "'.utf8_encode($codigo[14]).'",
          ccp_localidad = "'.utf8_encode($codigo[5]).'",
          ccp_clmunicipio = "'.utf8_encode($codigo[11]).'",
          ccp_municipio = "'.utf8_encode($codigo[3]).'"';
  if($i > 2)
    setq($sql);
}
fclose($fp);
?>