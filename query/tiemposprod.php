<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');

$respuesta = 0;
$response = array();
$tpid = $_POST['tpid'];
$fechaActual = date("Y-m-d");
$horaActual = date("H:i:s");
$uid = $_SESSION['eid'];
$proceso = $_POST['proceso'];
$ordenp = $_POST['ordenprod'];

$horas = $_POST['horas'];
$minutos = $_POST['minutos'];
$segundos = $_POST['segundos'];

$cantidad = $_POST['cantidad'];
$retro = $_POST['retro'];
$adj = $_POST['adj'];

if(isset($_SESSION['uid'])){
    $tuser = "U";
    $operador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else {
    $tuser = "O";
    $operador = $_SESSION['eid'];
}

$sql = 'UPDATE pr_articulosop SET pra_operador = NULL, pra_toperador = NULL, pra_seleccionado = "0" WHERE pra_op = "'.$ordenp.'" AND pra_pa = "'.$proceso.'"';
setq($sql);

if(!isset($_POST['reset'])){
if(isset($_POST['iniciar'])){

    $operadores = '';
    $sqlppx = 'SELECT ppx_operador, ppx_tuser FROM pr_procesoexe WHERE ppx_proceso = "' . $_GET['proceso'] . '" AND ppx_ordenp = "' . $_GET['op'] . '" AND ppx_operador != "" GROUP BY ppx_operador';
    $resultppx = setq($sqlppx);

    if(isset($_SESSION['uid'])){
        $tuser = 'U';
        $operador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
    } else{
        $tuser = 'O';
        $operador = $_SESSION['eid'];
    }

    if(empty($tpid)){
        $tipo = "1";
        $fini = date("Y-m-d H:i:s");
        $sqlupd = 'UPDATE pr_procesosop SET pp_fini = "'.$fini.'", pp_operador = "'.$operador.'", pp_toperador = "'.$tuser.'", pp_estatus = "A" WHERE pp_id = "'.$proceso.'" AND pp_ordenp = "'.$ordenp.'"';
        setq($sqlupd);

        $response['fechainicio'] = fecha_formato($fini, true, false);
    } else{
        $tipo = "3";

        $sqlupd = 'UPDATE pr_procesosop SET pp_operador = "'.$operador.'", pp_toperador = "'.$tuser.'", pp_estatus = "A" WHERE pp_id = "'.$proceso.'" AND pp_ordenp = "'.$ordenp.'"';
        setq($sqlupd);

        $finic = busca($proceso, 'pr_procesosop', 'pp_id', 'pp_fini');
        $response['fechainicio'] = fecha_formato($finic, true, false);
    }

    $operadores = '';
    $sqlppx = 'SELECT ppx_operador, ppx_tuser FROM pr_procesoexe WHERE ppx_proceso = "' . $proceso . '" AND ppx_ordenp = "' . $ordenp . '" AND ppx_operador != "" GROUP BY ppx_operador';
    $resultppx = setq($sqlppx);
    while ($rowppx = $resultppx->fetch_array()) {
        if($rowppx['ppx_tuser'] == "U"){
            $operadores .= busca($rowppx['ppx_operador'], 'usuarios', 'u_nuser', 'u_nmb') . " " . busca($rowppx['ppx_operador'], 'usuarios', 'u_nuser', 'u_apellidos') . '<br>';
        } else {
            $operadores .= busca($rowppx['ppx_operador'], 'pr_empleados', 'pe_id', 'pe_nmb') . '<br>';
        }
    }

    $response['operador'] = $operadores;

} else if(isset($_POST['pausar'])){
    $tipo = "2";
    $sqlupd = 'UPDATE pr_procesosop SET pp_estatus = "P" WHERE pp_id = "'.$proceso.'" AND pp_ordenp = "'.$ordenp.'"';
        setq($sqlupd);
} else if(isset($_POST['finalizar'])){
    $tipo = "4";
    //Actualizamos los datos respectivos del proceso actual
} 

if(isset($_POST['pausaCarga'])){
    $sqle = 'SELECT * FROM pr_procesoexe WHERE ppx_id = "' . $tpid . '"';
    $resulte = setq($sqle);
    $rowe = $resulte->fetch_array();

    $sql = 'INSERT INTO pr_procesoexe SET ppx_proceso = "'.$rowe['ppx_proceso'].'", 
                    ppx_operador="'.$operador.'", ppx_fecha="'.date("Y-m-d").'", ppx_ordenp = "'.$rowe['ppx_ordenp'].'",
                    ppx_hora = "'.date("H:i:s").'", ppx_horas="'.$rowe['ppx_horas'].'", ppx_minutos="'.$rowe['ppx_minutos'].'", 
                    ppx_segundos="'.$rowe['ppx_segundos'].'",  ppx_accion = "2",
                    ppx_cantidad = "'.$rowe['ppx_cantidad'].'", ppx_obs = "'.$rowe['ppx_obs'].'", ppx_adj = "'.$rowe['ppx_adj'].'", ppx_tuser = "'.$tuser.'"';
    setq($sql);
    $respuesta = getmax('ppx_id', 'pr_procesoexe', false, false);
} else if(isset($_POST['checkpoint'])){
    $sqlupd = 'UPDATE pr_procesoexe SET ppx_horas = "'.$horas.'", ppx_minutos = "'.$minutos.'", ppx_segundos = "'.$segundos.'" WHERE ppx_id = "'.$tpid.'"';
    setq($sqlupd);
} else {
    $sql = 'INSERT INTO pr_procesoexe SET ppx_proceso = "'.$proceso.'", 
                    ppx_operador="'.$operador.'", ppx_fecha="'.$fechaActual.'", ppx_ordenp = "'.$ordenp.'",
                    ppx_hora = "'.$horaActual.'", ppx_horas="'.$horas.'", ppx_minutos="'.$minutos.'", 
                    ppx_segundos="'.$segundos.'",  ppx_accion = "'.$tipo.'",
                    ppx_cantidad = "'.$cantidad.'", ppx_obs = "'.$retro.'", ppx_adj = "'.$adj.'", ppx_tuser = "'.$tuser.'"';
    setq($sql);
    $respuesta = getmax('ppx_id', 'pr_procesoexe', false, false);
}
} else if(isset($_POST['reset'])){
    $sql = 'DELETE FROM pr_procesoexe WHERE ppx_proceso = "'.$proceso.'" AND ppx_ordenp = "'.$ordenp.'"';
    setq($sql);
    $respuesta = "X";
}

if(isset($_POST["articuloSel"])){
    $arrayArt = explode(",", $_POST["articuloSel"]);
    if($tipo == "1" || $tipo == "3"){
        $estatuspra = "A";
        $estatuspra2 = "N";
    } else if($tipo == "4"){
        $estatuspra = "F";
        $estatuspra2 = "A";
    }

    for($i = 0; $i < count($arrayArt); $i++){
        $estatusactual = busca($arrayArt[$i], 'pr_articulosop', 'pra_estpa = "'.$estatuspra2.'" AND pra_id', 'COUNT(*)');
        if($estatusactual > 0){
            $sqlupd = 'UPDATE pr_articulosop SET pra_estpa = "'.$estatuspra.'" WHERE pra_id = "'.$arrayArt[$i].'"';
            setq($sqlupd);

            $procesoactual = intval(busca($arrayArt[$i], 'pr_articulosop', 'pra_id', 'pra_pa'));
            $nordenpa = busca($procesoactual, 'pr_procesosop', 'pp_id', 'pp_orden');
            $siguienteproceso = busca($ordenp, 'pr_procesosop', 'pp_orden = "'.($nordenpa + 1).'" AND pp_ordenp', 'pp_id');
            if(!empty($siguienteproceso)){
                $sqlf = 'pra_sp = "'.$siguienteproceso.'", pra_estsp = "N"';
            } else{
                $sqlf = 'pra_sp = NULL, pra_estsp = NULL';
            }

            $sqlupd2 = 'UPDATE pr_articulosop SET '.$sqlf.' WHERE pra_id = "'.$arrayArt[$i].'"';
            setq($sqlupd2);

            if($tipo == 4){
                //Inicia el proceso del cambio de estatus y procesos de los artículos
                $sqlorden = 'SELECT pp_orden FROM pr_procesosop WHERE pp_id = "'.$proceso.'" ';
                $resultorden = setq($sqlorden);
                list($ordenactual) = $resultorden->fetch_array();

                $sqlsig = 'SELECT pp_id, pp_orden FROM pr_procesosop WHERE pp_orden > '.$ordenactual.' AND pp_ordenp = "'.$ordenp.'" AND pp_estatus != "F" ORDER BY pp_orden ASC LIMIT 0,1 ';
                $resultsig = setq($sqlsig);
                list($idsp,$ordens) = $resultsig->fetch_array();

                if($ordens != "" || isset($ordens)){

                    $sqlsig2 = 'SELECT pp_id FROM pr_procesosop WHERE pp_orden > '.$ordens.' AND pp_ordenp = "'.$ordenp.'" AND pp_estatus != "F" ORDER BY pp_orden ASC LIMIT 0,1 ';
                    $resultsig2 = setq($sqlsig2);
                    list($idsp2) = $resultsig2->fetch_array();

                    $sqlup = 'UPDATE pr_articulosop SET
                            pra_pa = pra_sp,
                            pra_estpa = pra_estsp,
                            pra_sp = "'.$idsp2.'",
                            pra_estsp = "N"
                            WHERE pra_id = "'.$arrayArt[$i].'" AND pra_op = "'.$ordenp.'" ';
                    setq($sqlup);

                }else{
                    $idsp2  =  '';
                    $sqlup = 'UPDATE pr_articulosop SET
                                pra_estpa = "F",
                                pra_sp = NULL,
                                pra_estsp = NULL
                                WHERE pra_id = "'.$arrayArt[$i].'" AND pra_op = "'.$ordenp.'" ';
                    setq($sqlup);
                }

                $procesoact = busca($idsp, 'pr_procesosop', 'pp_estatus = "N" AND pp_id', 'COUNT(*)');
                if($procesoact > 0){
                    $query = "UPDATE pr_procesosop SET pp_estatus = 'P' WHERE pp_id = '".$idsp."' AND pp_ordenp = '".$ordenp."'";
                    setq($query);
                }

                //Finaliza el proceso del cambio de estatus y procesos de los artículos
            }
        }
    }
}

//Verificamos si aún hay artículos desponibles
if(isset($_POST['finalizar'])){
    $cantidadd = busca($ordenp, 'pr_articulosop', 'pra_estpa IN ("A", "N") AND pra_pa = "'.$proceso.'" AND pra_op', 'COUNT(*)');

    if($cantidadd > 0){
        $respuesta = "e1";
    } else{

        $operador = $_SESSION['eid'];
        $ffin = date("Y-m-d H:i:s");

        $sqlupd = 'UPDATE pr_procesosop SET pp_estatus = "F", pp_operador = "'.$operador.'", pp_toperador = "'.$tuser.'", pp_ffin = "'.$ffin.'" WHERE pp_id = "'.$proceso.'" AND pp_ordenp = "'.$ordenp.'"';
        setq($sqlupd);

        $res = busca($ordenp, 'pr_procesosop', 'pp_estatus != "F" AND pp_ordenp', 'COUNT(*)');
        if($res == 0){
            $sqlupd0 = 'UPDATE pr_ordenprod SET po_estatus = "F", po_ffin = "'.$ffin.'" WHERE po_id = "'.$ordenp.'"';
            setq($sqlupd0);

            $sqlopg = 'SELECT * FROM pr_ordenprod WHERE po_id = "'.$ordenp.'"';
            $resultopg = setq($sqlopg);
            $op = $resultopg->fetch_array();
            if(intval($op['po_articulo']) != 0){
                $articulo = $op['po_articulo'];
                $cantidad = $op['po_cantidad'];
                $almacen = $op['po_almacendestino'];
                $modo = "E";
                $modelo = $op['po_modelo'];
                ajustaexistenciaproduccion($articulo, $cantidad, $almacen, $modo, $modelo);
            }
        }

        $response['fechafin'] = fecha_formato($ffin, true, false);
    }
}

$response['respuesta'] = $respuesta;

echo json_encode($response);
?>