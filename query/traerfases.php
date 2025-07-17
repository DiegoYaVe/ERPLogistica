<?php
include_once('../funciones.php');

$planta = $_REQUEST['planta'];
$html = '<option value="" class="">Selecciona fase</option>';
$sql = 'SELECT * FROM pr_fases WHERE pf_planta = "'.$planta.'" AND pf_estatus = "A" ';
$result = setq($sql);
while($row = $result->fetch_array()){
  if($_REQUEST['fase'] == $row['pf_id']) $selectedfase = 'selected';
  else $selectedfase = '';
  $html .= '
    <option value="'.$row['pf_id'].'" class="" '.$selectedfase.'>'.$row['pf_nmb'].'</option>
    ';
}

echo $html;

?>