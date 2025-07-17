<?php
include_once('../funciones.php');
//ini_set('display_errors', 1);
$cotizacion = $_POST['cotizacion'];
$cotizaciond = $_POST['cotizaciond'];


$sqldel = 'DELETE FROM crm_cotizacionesdd WHERE cdd_cotizacion = "'.$cotizacion.'" AND cdd_idd = "'.$cotizaciond.'" ';
setq($sqldel);
include_once('../modulos/cotizaciones.php');
$cotiza = new modelcotizaciones;
$cotiza->setdescripcion($cotizacion,$cotizaciond,'INCLUYE:');
$sqlvar = 'SELECT ccv_articulo, ccv_modelo, ccv_cantidad FROM crm_cotizacion_variantes WHERE ccv_cotizacion = "'.$cotizacion.'" AND ccv_cotizaciond = "'.$cotizaciond.'"'; 
$resultvar = setq($sqlvar);
while($rowvar = $resultvar -> fetch_array()){ 
  $nmb = mb_strtolower(busca($rowvar['ccv_articulo'], 'articulos', ' a_id', 'a_nmb'));
  if($rowvar['ccv_modelo'] == "")
    $cotiza->setdescripcion($cotizacion, $cotizaciond, number_format($rowvar['ccv_cantidad'], 0)." ".$nmb);
  else 
    $cotiza->setdescripcion($cotizacion, $cotizaciond, number_format($rowvar['ccv_cantidad'], 0)." ".$nmb.' '.mb_strtolower(busca($rowvar['ccv_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowvar['ccv_modelo'].'" AND av_articulo', 'av_nmb')));

}

$sqllig = 'SELECT ccv_articulo, ccv_modelo, SUM(ccv_cantidad) cantidad, al_aligado, al_amodelo, al_cantidad FROM crm_cotizacion_variantes INNER JOIN articulos_ligados ON al_articulo = ccv_articulo WHERE ccv_cotizacion = "'.$cotizacion.'" AND ccv_cotizaciond = "'.$cotizaciond.'" GROUP BY al_aligado;';
$resultlig = setq($sqllig);
if($resultlig -> num_rows > 0){
  $cotiza->setdescripcion($cotizacion,$cotizaciond,"COMPLEMENTOS:");
  while($rowlig = $resultlig -> fetch_array()){
    
    if($rowlig['al_amodelo']) $nmb = busca($rowlig['al_aligado'], 'articulos_variantes', 'av_modelo = "'.$rw['al_amodelo'].'" AND av_articulo', 'av_nmb');
    else $nmb = busca($rowlig['al_aligado'], 'articulos', 'a_id', 'a_nmb');
    $cotiza->setdescripcion($cotizacion,$cotizaciond,number_format(($rowlig['al_cantidad']* $rowlig['cantidad'])).' '.mb_strtolower($nmb));
     
  }
}
$sqlupd = 'UPDATE crm_cotizacionesd SET cdm_completo = "1" WHERE cdm_cotizacion = "'.$cotizacion.'" AND cdm_id = "'.$cotizaciond.'"';
setq($sqlupd)

?>