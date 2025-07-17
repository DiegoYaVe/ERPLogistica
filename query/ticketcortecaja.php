<?php 
//ini_set('display_errors', 1);
include_once('../funciones.php');
session_start();

$datos = array();
$idcaja = $_POST['idcaja'];
$sqlcd = 'SELECT SUM(cd_efectivo) efectivo, SUM(cd_tarjeta) tarjeta, SUM(cd_total) total FROM cajasd WHERE cd_caja = "'.$idcaja.'"';
$resultcd = setq($sqlcd);
list($efesis, $tarsis, $totalsis) = $resultcd -> fetch_array();

$sqlc = 'SELECT c_ffin, c_hfin, c_ufin,c_fondo, c_cajero, c_efectivo, c_tarjeta, c_total FROM cajas WHERE c_id = "'.$idcaja.'"';
$resultc = setq($sqlc);
list($ffin, $hfin, $ufin, $fondo, $cajero, $efecap, $tarcap, $totalcap) = $resultc -> fetch_array();
$nmbcajero = busca($cajero, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
$nmbufin = busca($ufin, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');

$sqltab = 'SELECT ct_1000, ct_500, ct_200, ct_100, ct_50, ct_20, ct_10, ct_5, ct_2, ct_1, ct_50c FROM caja_tabulador WHERE ct_caja = "'.$idcaja.'"';
$resulttab = setq($sqltab);
list($v1000, $v500, $v200, $v100, $v50, $v20, $v10, $v5, $v2, $v1, $v50c) = $resulttab -> fetch_array();
$logo = "../img/UNICO-LOGO-OFICIAL.png";

$suming = busca($idcaja, 'cajas_movimientos', 'cm_tipo = "I" AND cm_caja', 'SUM(cm_monto)');
$sumgasto = busca($idcaja, 'cajas_movimientos', 'cm_tipo = "G" AND cm_caja', 'SUM(cm_monto)');

$tardif = $tarcap - $tarsis;
$efectivo = $suming + $efesis + $fondo;
$efedif = $efecap + $sumgasto - $efectivo;
$totaldif = $tardif + $efedif;
$sumtab = ($v1000 * 1000)+ ($v500*500)+ ($v200*200)+ ($v100*100)+ ($v50*50)+ ($v20*20)+ ($v10*10)+ ($v5*5)+ ($v2*2)+ $v1+($v50c*0.5);

$totalsis = $tarsis + $efectivo;
$totalcap = $tarcap + $efecap;
$totaldif = $totalcap + $sumgasto - $totalsis;


$datos['fecha']= fecha_formato($ffin.' '.$hfin, true, false);
$datos['fondo']= number_format($fondo, 2);
$datos['efesis']= number_format($efectivo, 2);
$datos['efedif']= number_format($efedif, 2);
$datos['efecap']= number_format($efecap, 2);
$datos['eferetiro']= number_format($sumgasto, 2);
$datos['tarsis']= number_format($tarsis, 2);
$datos['tarcap']= number_format($tarcap, 2);
$datos['tardif']= number_format($tardif, 2);
$datos['totalsis']= number_format($totalsis, 2);
$datos['totalcap']= number_format($totalcap, 2);
$datos['totaldif']= number_format($totaldif, 2);
$datos['uapertura']= $nmbcajero;
$datos['ucierre']= $nmbufin;
$datos['1000']= $v1000;
$datos['500']= $v500;
$datos['200']= $v200;
$datos['100']= $v100;
$datos['50']= $v50;
$datos['20']= $v20;
$datos['10']= $v10;
$datos['5']= $v5;
$datos['2']= $v2;
$datos['1']= $v1;
$datos['50c']= $v50c;
$datos['sumtab']= number_format($sumtab, 2);
$datos['logo']= $logo;

echo json_encode($datos);
?>



