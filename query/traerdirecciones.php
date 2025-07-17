<?php
//ini_set('display_errors', 1);
include_once('../funciones.php');
session_start();

$cliente = $_POST['cliente'];
$html = '
    <div class="mb-5">
        <label for"cp">Seleccionar dirección</label>
        <select id="direcciones" name="direcciones" onchange="cambiardir();" class="form-control">
        <option value="YYY" disabled> Seleccionar una dirección </option>';
        $i = 0;
        $sqldir = 'SELECT * FROM crm_direcciones WHERE cd_cliente = "'.$cliente.'"';
        $result = setq($sqldir);
        while($row = $result -> fetch_array()){
            if($direnvio == $row['cd_id'] || $row['cd_predeterminada'] == "1") {
            $seld = 'selected';
            }else{
            $seld = '';
            }
            $html .= '<option value="'.$row['cd_id'].'" '.$seld.' >'.$row['cd_calle'].' '.$row['cd_nume'].' '.$row['cd_numi'].' '.$row['cd_colonia'].' '.$row['cd_municipio'].' '.busca($row['cd_estado'], 'estado', 'e_id', 'e_nmb').' '.$row['cd_cp'].'</option>';
        }
        $html .= '
        <option value="XXX" '.$seldir.'> Nueva dirección</option>
        </select> 
    </div>';

    echo $html;
?>