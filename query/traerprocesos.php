<?php
include_once('../funciones.php');

$planta  = $_REQUEST['planta'];
$fase = $_REQUEST['fase'];
$html = '<option value="" class="">Selecciona proceso</option>';
$sql = 'SELECT * FROM pr_procesos WHERE ppr_fase = "'.$fase.'" AND ppr_planta = "'.$planta.'" AND ppr_estatus = "A" ';
$result = setq($sql);
while($row = $result->fetch_array()){
  $html .= '
    <option value="'.$row['ppr_id'].'" class="" >'.$row['ppr_nmb'].'</option>
    ';
}

echo $html;

?>