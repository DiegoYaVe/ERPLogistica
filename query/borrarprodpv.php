<?php
session_start();
include('../funciones.php');
$idarticulo = $_POST['id'];
$idticket = $_POST['ticket'];
$sql = 'DELETE FROM pv_ticketsd WHERE ptd_articulo = "'.$idarticulo.'" AND ptd_ticket = "'.$idticket.'"';
setq($sql);


$sql = 'SELECT * FROM pv_ticketsd WHERE ptd_ticket = "'.$idticket.'"';
$result = setq($sql);
$total = 0;
$viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');

$descuento = 0;
$totiva = 0;
$subtotal = 0;
while($row = $result->fetch_array()){
  $importe = $row['ptd_precio']*$row['ptd_cantidad'];

  if($row['ptd_iva'] == 1){
    $iva=$impmd*($viva/100);
    $subtotal+=$impmd;
  }
  else{
    $iva = $importe-(($importe/(100+$viva))*100);
    $subtotal+=($importe/($viva+100))*100;
  }

  $totiva+=$iva;
}
$total = $subtotal-$descuento+$totiva;

$sql = 'UPDATE pv_tickets SET
        pt_subtotal = "'.$subtotal.'",
        pt_iva = "'.$totiva.'",
        pt_total = "'.$total.'"
        WHERE pt_id = "'.$idticket.'"';
setq($sql);

?>