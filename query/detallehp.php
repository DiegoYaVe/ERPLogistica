
<?php
session_start();
ini_set('display_errors',0);
include_once('../funciones.php');

if(isset($_SESSION['uid'])){
    $tuser = 'U';
} else{
    $tuser = 'O';
}

$operador = $_POST['operador'];
$ordenp = $_POST['ordenp'];

$html .= '
<div class="container">
<div class="row">
    <div class="col-12 col-md-12">
        <button type="button" class="btn btn-warning" onclick="iratras()"><i class="fa fa-arrow-left"></i>Atrás</button>
    </div>
    <div class="table table-responsive col-12 col-md-12 p-2">
        <table width="100%" class="table table-stripped table-bordered table-hover table-bordered border-primary">
            <thead class="bg-primary text-white">
            <tr>
                <td>Proceso</td>
                <td>Fase</td>
                <td>Fecha inicio</td>
                <td>Fecha final</td>
                <td>Estatus</td>
                <td>Supervisor</td>
            </tr>
            </thead>
            <tbody>';
            $sql = 'SELECT * FROM pr_procesoexe WHERE ppx_operador = "'.$operador.'" AND ppx_ordenp = "'.$ordenp.'" AND ppx_tuser = "'.$tuser.'" GROUP BY ppx_proceso ORDER BY ppx_fecha DESC';
            $result = setq($sql);
            while($rowc = $result->fetch_array()){
                $idp = busca($rowc['ppx_proceso'], 'pr_procesosop', 'pp_id', 'pp_proceso');
                $idfase = busca($idp, 'pr_procesos', 'ppr_id', 'ppr_fase');
                $nmbproceso = busca($idp, 'pr_procesos', 'ppr_id', 'ppr_nmb');
                $nmbfase= busca($idfase, 'pr_fases', 'pf_id', 'pf_nmb');
                $estatusproceso = busca($rowc['ppx_proceso'], 'pr_procesosop', 'pp_id', 'pp_estatus');
                $fini = busca($rowc['ppx_proceso'], 'pr_procesosop', 'pp_id', 'pp_fini');
                $ffin = busca($rowc['ppx_proceso'], 'pr_procesosop', 'pp_id', 'pp_ffin');
                $supervisor = busca($rowc['ppx_proceso'], 'pr_procesosop', 'pp_id', 'pp_supervisor');
                if(empty($fini)){
                    $fefini = 'No disponible';
                  } else{
                    $fefini = fecha_formato($fini, true, false);
                  }

                  if(empty($ffin)){
                    $fefin = 'No disponible';
                  } else{
                    $fefin = fecha_formato($ffin, true, false);
                  }
        
                  if($estatusproceso == "N"){
                    $estatus = "Nueva";
                  } else if($estatusproceso == "A"){
                    $estatus = "En ejecución";
                  } else if($estatusproceso == "P"){
                    $estatus = "Pendiente de operador";
                  } else if($estatusproceso == "F"){
                    $estatus = "Finalizado";
                  } else{
                    //Si se cancela
                    $estatus = "Cancelado";
                  }
            $html .= '<tr>
                <td>'.$nmbproceso.'</td>
                <td>'.$nmbfase.'</td>
                <td>'.$fefini.'</td>
                <td>'.$fefin.'</td>
                <td>'.$estatus.'</td>
                <td>'.$supervisor.'</td>
            </tr>';
            }
            $html .= '</tbody>
        </table>
        </div>
        <center>
        <div class="col-12">
            <button class="btn btn-danger" onclick="cerrarModal();"><i class="fas fa-times-circle"> </i> Cerrar </button>
        </div>
        </center>
    </div>
</div>
</div>';

echo $html;
?>