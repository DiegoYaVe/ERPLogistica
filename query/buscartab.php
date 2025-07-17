<?php
session_start();
include_once('../funciones.php');


$idc = getIDcliente($_POST['cliente']);
//die($idc);
$html = '<option value="">Nuevo Tablero</option>';
if($idc){
  $sql = 'SELECT * FROM crm_tableros WHERE ct_cliente = "'.$idc.'" AND ct_estatus = "N" AND ct_empresa = "'.$_SESSION['emp'].'" ';
  $result = setq($sql);
  while($row = $result->fetch_array()){
    $html .= '
    <option value="'.$row['ct_id'].'">'.$row['ct_folio'].' - '.$row['ct_nmb'].'</option>
    ';
  }
}

echo $html;



?>