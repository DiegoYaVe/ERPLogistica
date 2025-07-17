<?php 
//ini_set('display_errors', 1);
include_once('../funciones.php');
session_start();

$idcd = $_POST['idcd'];
$sqlcd = 'SELECT cd_remision, cd_fgen, cd_ugen, cd_total, cd_efectivo, cd_tarjeta, cd_cambio FROM cajasd WHERE cd_id = "'.$idcd.'"';
$resultcd = setq($sqlcd);
list($remision, $fgen, $ugen, $total, $efectivo, $tarjeta, $cambio) = $resultcd -> fetch_array();

$sqlre = 'SELECT r_folio, r_subtotal, r_mdescuento, r_total, r_descuento FROM remisiones WHERE r_id = "'.$remision.'"';
$resultre = setq($sqlre);
list($folio, $subtotal, $descuento, $totalr, $descuentop) = $resultre -> fetch_array();
$asesor = busca($ugen, 'usuarios', 'u_id', 'CONCAT(u_nmb ," ", u_apellidos)');
$sqlcxc = 'SELECT cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$remision.'"';
$resultcxc = setq($sqlcxc);
list($importe, $abonado, $estatus) = $resultcxc -> fetch_array();
$logo = "../img/UNICO-LOGO-OFICIAL.png";

$sqldir = 'SELECT e_calle, e_colonia, e_ciudad, e_estado, e_tel, e_cp FROM empresas WHERE e_id = "1"';
$resultdir = setq($sqldir);
list($calle, $colonia, $ciudad, $estado, $telefono, $cp) = $resultdir -> fetch_array();

$texto = busca('1', 'configuracionesp', 'c_id', 'c_textoticket');

$datos = array();

$saldo = $importe-$abonado;
if($saldo < 0) $saldo = 0;
$efectivorec = $efectivo + $cambio;

$datos['fecha']= $fgen;
$datos['folio']= $folio;
$datos['genera']= $asesor;
$datos['subtotal']= number_format($subtotal, 2);
$datos['descuento']= $descuento;
$datos['totalcd']= number_format($total, 2);
$datos['totalrem']= number_format($totalr, 2);
$datos['logo']= $logo;
$datos['descuentop'] = number_format($descuentop, 2);
$datos['efectivo'] = number_format($efectivo, 2);
$datos['efectivorec'] = number_format($efectivorec, 2);
$datos['cambio'] = number_format($cambio, 2);
$datos['tarjeta'] = number_format($tarjeta, 2);
$datos['abonado'] = number_format($abonado, 2);
$datos['saldo'] = number_format($saldo, 2);
$datos['direccion'] = clearvmayus($calle.' COL. '.$colonia.' '.$ciudad.', '.$estado.' C.P. '.$cp);
$datos['telefono'] = $telefono;
$datos['texto'] = $texto;

$sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$remision.'"';
$result = setq($sql);
while($row = $result -> fetch_array()){
  if(!$datos['articulos']) {
    $datos['articulos'] = $row['rd_nmbarticulo'];
    $datos['precio']= number_format( $row['rd_precio'], 2);
    $datos['cantidad'] = number_format($row['rd_cantidad'], 0);
  } else {
    $datos['articulos'] .= '/'.$row['rd_nmbarticulo'];
    $datos['precio'] .= '/'.number_format($row['rd_precio'], 2);
    $datos['cantidad'] .= '/'.number_format($row['rd_cantidad'], 0);
  }
}

echo json_encode($datos);
?>



