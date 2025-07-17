<?php
include_once('../funciones.php');
ini_set('display_errors',1);
session_start();

$response = array();

if(isset($_SESSION['uid'])){
    $tuser = "U";
    $operador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else {
    $tuser = "O";
    $operador = $_SESSION['eid'];
}


// Verifica si se recibieron los datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera los datos del formulario
    $ordenp = $_POST["ordenp"];
    $proceso = $_POST["proceso"];

    $id = $_POST["id"];
    $retro = $_POST["retro"];
    $cantidad = $_POST["cantidad"];
    $tipo = intval($_POST["tipo"]);
    $fechaActual = date("Y-m-d");
    $horaActual = date("H:i:s");
    $rutaFinal = '';
    $adj = '';
    $cantidadd = busca($ordenp, 'pr_articulosop', 'pra_estpa IN ("A", "N") AND pra_op', 'COUNT(*)');
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = $_FILES['archivo']['name'];
        $rutaTemporal = $_FILES['archivo']['tmp_name'];
        $carpetaDestino = '../img/evidenciastemporizador/'; // Reemplaza con la ruta donde deseas guardar el archivo
        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato predeterminado
        $fechaHoraSinGuionesNiPuntos = str_replace(array("-", ":", " "), "", $fechaHora);

        $rutaFinal = $carpetaDestino . $fechaHoraSinGuionesNiPuntos . $nombreArchivo;

        if (move_uploaded_file($rutaTemporal, $rutaFinal)) {
            //El archivo se ha movido exitosamente a la carpeta de destino
            // Ahora puedes guardar la ruta de la carpeta en la base de datos o realizar otras acciones necesarias
            $response['respuesta'] = 0;
            $adj = $rutaFinal;
        } else {
            //Error al mover el archivo
            $response['respuesta'] = 2;
            $adj = "";
        }
    }

    $sql = 'INSERT INTO pr_procesoexe SET ppx_proceso = "'.$proceso.'", 
            ppx_operador="'.$operador.'", ppx_fecha="'.$fechaActual.'", ppx_ordenp = "'.$ordenp.'",
            ppx_hora = "'.$horaActual.'", ppx_horas="0", ppx_minutos="0", 
            ppx_segundos="0",  ppx_accion = "'.$tipo.'",
            ppx_cantidad = "'.$cantidad.'", ppx_obs = "'.$retro.'", ppx_tuser = "'.$tuser.'"';
    setq($sql);
    $respuesta = getmax('ppx_id', 'pr_procesoexe', false, false);

    if(isset($_POST["articuloSel"])){
        $arrayArt = explode(",", $_POST["articuloSel"]);


        if($tipo == 1){
            $estatuspra = "A";
            $estatuspra2 = "N";
        } else{
            $estatuspra = "F";
            $estatuspra2 = "A";
        }
    
        for($i = 0; $i < count($arrayArt); $i++){
            $estatusactual = busca($arrayArt[$i], 'pr_articulosop', 'pra_estpa = "'.$estatuspra2.'" AND pra_id', 'COUNT(*)');
            if($estatusactual > 0){
                $sqlupd = 'UPDATE pr_articulosop SET pra_estpa = "'.$estatuspra.'" WHERE pra_id = "'.$arrayArt[$i].'"';
                setq($sqlupd);
    
                if($tipo == 4){
                $procesoactual = intval(busca($arrayArt[$i], 'pr_articulosop', 'pra_id', 'pra_pa'));
                $nordenpa = busca($procesoactual, 'pr_procesosop', 'pp_id', 'pp_orden');
                $siguienteproceso = busca($ordenp, 'pr_procesosop', 'pp_orden = "'.($nordenpa + 1).'" AND pp_ordenp', 'pp_id');
                /* $siguienteproceso1 = busca2($ordenp, 'pr_procesosop', 'pp_orden = "'.($nordenpa + 1).'" AND pp_ordenp', 'pp_id'); */
                /* die($siguienteproceso1); */
                if(!empty($siguienteproceso)){
                    $sqlf = 'pra_sp = "'.$siguienteproceso.'", pra_estsp = "N"';
                } else{
                    $sqlf = 'pra_sp = NULL, pra_estsp = NULL';
                }
    
                $sqlupd2 = 'UPDATE pr_articulosop SET '.$sqlf.' WHERE pra_id = "'.$arrayArt[$i].'"';
                setq($sqlupd2);

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

    // Prepara la consulta para insertar los datos
    if($tipo == 1){
        $procesoestatus = busca($proceso, 'pr_procesosop', 'pp_ordenp ="'.$ordenp.'" AND pp_id', 'pp_estatus');
        if($procesoestatus == "A"){

        } else{
            $query = "UPDATE pr_procesosop SET pp_fini = '".$fechaActual.' '.$horaActual."', pp_operador = '".$operador."', pp_toperador = '".$tuser."', pp_estatus = 'A' WHERE pp_id = '".$proceso."' AND pp_ordenp = '".$ordenp."'";
            setq($query);
        }
    } else if($tipo == 4){
        /* $query = "UPDATE pr_procesosop SET pp_fini = '".$fechaActual.' '.$horaActual."', pp_operador = '".$operador."', pp_estatus = 'A' WHERE pp_id = '".$proceso."' AND pp_ordenp = '".$ordenp."'";
        setq($query); */

        $cantidadd = busca($ordenp, 'pr_articulosop', 'pra_estpa IN ("A", "N") AND pra_pa = "'.$proceso.'" AND pra_op', 'COUNT(*)');
        /* $cantidadd0 = busca2($ordenp, 'pr_articulosop', 'pra_estpa IN ("A", "N") AND pra_pa = "'.$proceso.'" AND pra_op', 'COUNT(*)');
        die($cantidadd0); */
        /* $cantidadd0 = busca2($ordenp, 'pr_articulosop', 'pra_estpa IN ("A", "N") AND pra_pa = "'.$proceso.'" AND pra_op', 'COUNT(*)');
        die($cantidadd0); */

        if($cantidadd > 0){
            $respuesta = "e1";
        } else{
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
    /* $response['retro'] = $retro;
    $response['cantidad'] = $cantidad;
    $response['adj'] = $rutaFinal; */
    $response['respuesta'] = 0; //Todo correcto
} else {
    //Acceso no permitido
    $response['respuesta'] = 1;
}
echo json_encode($response);
?>
