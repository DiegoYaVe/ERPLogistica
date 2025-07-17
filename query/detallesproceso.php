<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
class detallep{
function detallesproceso(){
//Buscamos los detalles del proceso y de la orden de producción
$sqlprod = 'SELECT * FROM pr_procesosop WHERE pp_id = "' . $_GET['proceso'] . '" AND pp_ordenp = "' . $_GET['op'] . '"';
$resultp = setq($sqlprod);
$rowp = $resultp->fetch_array();
if (empty($rowp['pp_fini'])) {
    $fini = "No disponible";
} else {
    $fini = fecha_formato($rowp['pp_fini'], true, false);
}

if (empty($rowp['pp_ffin'])) {
    $ffin = "No disponible";
} else {
    $ffin = fecha_formato($rowp['pp_ffin'], true, false);
}
$obs = $rowp['pp_obs'];

$ordenp = busca($_GET['op'], 'pr_ordenprod', 'po_id', 'po_folio');
$proceso = busca($rowp['pp_proceso'], 'pr_procesos', 'ppr_id', 'ppr_nmb');
$artp = busca($_GET['op'], 'pr_ordenprod', 'po_id', 'po_nmbarticulo');
$adj = busca($_GET['op'], 'pr_ordenprod', 'po_id', 'po_adj');

$operadores = '';
$sqlppx = 'SELECT ppx_operador FROM pr_procesoexe WHERE ppx_proceso = "' . $_GET['proceso'] . '" AND ppx_ordenp = "' . $_GET['op'] . '" AND ppx_operador != "" GROUP BY ppx_operador';
$resultppx = setq($sqlppx);
while ($rowppx = $resultppx->fetch_array()) {
    if($rowppx['ppx_tuser'] == "O"){
        $operadores .= busca($rowppx['ppx_operador'], 'pr_empleados', 'pe_id', 'pe_nmb') . " - OPERADOR" . '<br>';
    } else{
        $operadores .= busca($rowppx['ppx_operador'], 'usuarios', 'u_nuser', 'u_nmb') . " " . busca($rowppx['ppx_operador'], 'usuarios', 'u_nuser', 'u_apellidos') . " - ADMIN" . '<br>';
    }
}

$urlimg = "'../img/".$adj."'";

echo '
<table id="myTableG" class="table">
    <thead class="thead-active text-black">
        <tr>
            <th colspan="2">
                <center>Detalles del proceso</center>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr class="thead-active bg-primary text-white">
            <td>Concepto</td>
            <td>Descripción</td>
        </tr>
        <tr style="color: #3e7cb4; font-size: initial;">
            <td><b>Artículo en producción:</b></td>
            <td>
                <b>'.$artp.'</b>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <center>
                <div class="view overlay" style="height: 200px; width: 300px; background: url('.$urlimg.'); background-size: cover; background-position: center;" alt="Imágen descriptiva">
                </center>            
            </td>
        </tr>
        <tr>
            <td>Orden de producción:</td>
            <td>
                '.$ordenp.'
            </td>
        </tr>
        <tr>
            <td>Proceso:</td>
            <td>
                '.$proceso.'
            </td>
        </tr>
        <tr>
            <td>Operador:</td>
            <td id="operador">';                        
                if (isset($_SESSION['uid'])) {
                    /* $sqlemp = 'SELECT * FROM pr_empleados WHERE pe_rol = "1" AND pe_estatus = "A"'; */
                    $sqlemp = 'SELECT * FROM pr_empleados WHERE pe_estatus = "A"';
                    $resultemp = setq($sqlemp);
                    echo '
                    <select class="form-control" id="operadores" name="operadores" onchange="actualizarOperador()">';
                    if($resultemp->num_rows == 0){
                        echo '
                        <option value="">Sin resultados</option>
                        ';
                    } else{
                    while($rowemp = $resultemp->fetch_array()) {
                        if($rowp['pp_operador'] == $rowemp['pe_id']){
                            $sel = 'selected';
                        } else{
                            $sel = '';
                        }
                        echo '
                        <option value="'.$rowemp['pe_id'].'" '.$sel.'>'.$rowemp['pe_nmb'].'</option>
                        ';
                    }
                    }
                    echo '
                    </select>
                    ';

                    echo "<strong>Otros operadoradores:</strong> <br>".$operadores; 
                } else{
                    if(!empty($operadores)){
                        echo $operadores; 
                    } else{
                        echo "Sin operador asignado"; 
                    }
                } 
                
            echo '</td>
        </tr>
        <tr>
            <td>Fecha inicio:</td>
            <td id="fechainicio">
                '.$fini.'
            </td>
        </tr>
        <tr>
            <td>Fecha final:</td>
            <td id="fechafin">'.
                $ffin.'
            </td>
        </tr>
        <tr>
            <td>Retroalimentación:</td>
            <td>
                '.$obs.'
            </td>
        </tr>

    </tbody>
</table>';
}
}
?>