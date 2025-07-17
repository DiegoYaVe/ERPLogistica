<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$banderarol = true;
if(isset($_SESSION['uid'])){
    $tuser = 'U';
    $operador2 = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
    $banderarol = true;
} else{
    $tuser = 'O';
    $operador2 = $_SESSION['eid'];

    //Verificamos si el empleado tiene los permisos para acceder a este proceso
    $rol = busca($operador2, 'pr_empleados', 'pe_id', 'pe_rol');
    $proceso = busca($_GET['proceso'],'pr_procesosop','pp_id','pp_proceso');
    $permiso = busca($rol, 'roles_permisos', 'rp_proceso = "'.$proceso.'" AND rp_idrol', 'COUNT(*)');
    
    if($permiso > 0){
        //El empleado tiene permiso para acceder a este proceso
        $banderarol = true;
    } else{
        //El empleado NO tiene permiso para acceder a este proceso
        $banderarol = false;
    }
}

if(!$banderarol){
    echo  '
    <script>
        alert("No tienes los permisos necesarios para acceder a este proceso.");        
        window.close();
        window.location.href = "operador.php";
    </script>';
}



$estatusart = busca($_GET['op'], 'pr_articulosop', 'pra_pa = "'.$_GET['proceso'].'" AND pra_seleccionado = "1" AND pra_op', 'COUNT(*)');
if($estatusart > 0){
    $estatusart2 = busca($_GET['op'], 'pr_articulosop', 'pra_pa = "'.$_GET['proceso'].'" AND pra_operador = "'.$operador2.'" AND pra_toperador = "'.$tuser.'" AND pra_seleccionado = "1" AND pra_op', 'COUNT(*)');
    if($estatusart2 == 0){
        if(!isset($_SESSION['uid'])){
            $bandera = 0;
            if(!isset($_SESSION['eid'])){
                $bandera = 0;
            } else{
                $bandera = 1;
            }
        } else{
            $bandera = 1;
        }

        if($bandera == 0){
            echo  '
            <script>
                alert("Otro usuario está interactuando con este proceso.");
                window.close();
                window.location.href = "operador.php";
            </script>';
        } else{
            echo  '
        <script>
            alert("Inicie sesión para continuar.");
            opener.window.reload();
            window.close();
            window.location.href = "operador.php";
        </script>';
        }
    } else{
        echo '
        <script>
        $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/estatusproceso.php",
            data: {
                "estatus": 0,
                "all": 0,
                "proceso": "'.$_GET['proceso'].'",
                "ordenp": "'.$_GET['op'].'"
            },
            success: function (data) {
            }
          });
        </script>
        ';
    }
}
//Inicia funcion de asignación de proceso
if(isset($_GET['accion']) && $_GET['accion'] != "updatedproceso"){
/* if(isset($_GET['accion'])){ */
    $destino = $_POST['procesodestino'];
    $origen = $_POST['procesoorigen'];
    $ordenp = $_POST['ordenp'];    
    $arrayArt = explode(",", $_POST["valores"]);

    for($i = 0; $i < count($arrayArt); $i++){
        //Inicia el proceso del cambio de estatus y procesos de los artículos
        $sqlorden = 'SELECT pp_orden FROM pr_procesosop WHERE pp_id = "'.$destino.'" ';
        $resultorden = setq($sqlorden);
        list($ordenactual) = $resultorden->fetch_array();

        $sqlsig = 'SELECT pp_id, pp_orden FROM pr_procesosop WHERE pp_orden > '.$ordenactual.' AND pp_ordenp = "'.$ordenp.'" ORDER BY pp_orden ASC LIMIT 0,1 ';
        $resultsig = setq($sqlsig);
        list($idsp,$ordens) = $resultsig->fetch_array();

        if($ordens != "" || isset($ordens)){

            /* $sqlsig2 = 'SELECT pp_id FROM pr_procesosop WHERE pp_orden > '.$ordens.' AND pp_ordenp = "'.$ordenp.'" ORDER BY pp_orden ASC LIMIT 0,1 ';
            $resultsig2 = setq($sqlsig2, true);
            list($idsp2) = $resultsig2->fetch_array(); */

            $sqlup = 'UPDATE pr_articulosop SET
                    pra_pa = "'.$destino.'",
                    pra_estpa = "N",
                    pra_sp = "'.$idsp.'",
                    pra_estsp = "N"
                    WHERE pra_id = "'.$arrayArt[$i].'" AND pra_op = "'.$ordenp.'" ';
            setq($sqlup);

        }else{
            $idsp2  =  '';
            $sqlup = 'UPDATE pr_articulosop SET
                        pra_pa = "'.$destino.'",
                        pra_estpa = "N",
                        pra_sp = NULL,
                        pra_estsp = NULL
                        WHERE pra_id = "'.$arrayArt[$i].'" AND pra_op = "'.$ordenp.'" ';
            setq($sqlup);
        }
        $procesoact = busca($destino, 'pr_procesosop', 'pp_estatus = "N" AND pp_id', 'COUNT(*)');
        if($procesoact > 0){
            $query = "UPDATE pr_procesosop SET pp_estatus = 'P' WHERE pp_id = '".$idsp."' AND pp_ordenp = '".$ordenp."'";
            setq($query);
        }

        //Finaliza el proceso del cambio de estatus y procesos de los artículos
    }
    $tip= "";
    if(isset($_GET['tipo'])){
        $tipo = "&tipo=".$_GET['tipo'];
    }
    redirect("temporizadorpps.php?op=".$ordenp."&proceso=".$origen.$tipo);
}
//Finaliza funcion de asignación de proceso

$contenedor = false;
$mostrarbtn = false;
$bloqueo = '';

if(isset($_SESSION['uid'])){
    $tuser = 'U';
    $ppoperador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else{
    $tuser = 'O';
    $ppoperador = $_SESSION['eid'];
}


$estatuspp = busca($_GET['op'], 'pr_procesosop', 'pp_id = "'.$_GET['proceso'].'" AND pp_ordenp', 'pp_estatus');
if($estatuspp == "N"){
//Verificamos que no exista un bloqueo del proceso actual
$ordenp = busca($_GET['op'], 'pr_procesosop', 'pp_id = "'.$_GET['proceso'].'" AND pp_ordenp', 'pp_orden');
if($ordenp > 1){ //Si el proceso actual es diferente al primero
    $orden = $ordenp - 1; //Reducimos 1 al orden de este proceso para determinar el proceso anterior
    if($orden == 1){ //Si el orden del proceso resultante de la resta es mayor igual a 1 (O al primer proceso)
        $bloqueo = busca($orden, 'pr_procesosop', 'pp_ordenp = "'.$_GET['op'].'" AND pp_orden', 'pp_bloqueo');//Verificamos si hay bloqueo en el proceso anterior
        $estatus = busca($orden, 'pr_procesosop', 'pp_ordenp = "'.$_GET['op'].'" AND pp_orden', 'pp_estatus'); //Verificamos el estatus del proceso anterior
        if($estatus == "F"){
            $bloqueo = 0; //Si el proceso está finalizado y tenía bloqueo no tomamos en cuenta el bloqueo
        }
        if($bloqueo == 0){
            $contenedor = true; // Bandera boleana para no mostrar las alertas de procesos
        } else{
            $contenedor = false; // Bandera boleana para mostrar las alertas de procesos
        }
    } else{ // Si el orden del proceso resultante de la resta es mayor a 1 (O diferente al primer proceso)

        $maximo = busca($_GET['op'], 'pr_procesosop', 'pp_orden < (SELECT pp_orden FROM pr_procesosop WHERE pp_orden = "'.$ordenp.'" AND pp_ordenp = "'.$_GET['op'].'") AND pp_bloqueo = 1 AND pp_ordenp', 'MAX(pp_orden)');

        $minimo = busca($_GET['op'], 'pr_procesosop', 'pp_orden < (SELECT pp_orden FROM pr_procesosop WHERE pp_orden = "'.$ordenp.'" AND pp_ordenp = "'.$_GET['op'].'") AND pp_bloqueo = 0 AND pp_ordenp', 'MIN(pp_orden)');

        //Establecemos el rango determinando el limite superior e inferior respectivamente
        if($maximo > $minimo){
            $lmin = $maximo;
            $lmax = $ordenp;
            $sqlf = 'pp_orden > "'.$lmin.'"';
        } else{
            $lmin = ($minimo - 1);
            if($lmin == 0){
                $lmin = 1;
            }
            $lmax = $ordenp;
            $sqlf = 'pp_orden >= "'.$lmin.'"';
        }
 
        $status = busca($_GET['op'], 'pr_procesosop', 'pp_orden = "'.$lmin.'" AND pp_bloqueo = 1 AND pp_ordenp', 'pp_estatus');
        $nrows = busca($_GET['op'], 'pr_procesosop', $sqlf.' AND pp_orden < "'.$lmax.'" AND pp_bloqueo = 0 AND pp_ordenp', 'COUNT(*)');

        if($nrows == 0){
            $mostrarbtn = false; // No existen procesos no obligatorios en el rango establecido
            //$contenedor = true; // Bandera boleana para mostrar las alertas de procesos
        } else{
            $mostrarbtn = true; //Mostramos el botón para iniciar un proceso despues de no obligatorios
            //$contenedor = false; // Bandera boleana para mostrar las alertas de procesos
        }

        if($status == "F"){
            $contenedor = true; // No existen procesos no obligatorios
        } else{
            $contenedor = false; // Bandera boleana para mostrar las alertas de procesos
        }
    }
} else{ // Si el orden del proceso es igual al primer proceso
    $contenedor = true;
}
} else{
    $contenedor = true;
}

$mostrarbtn2 = false;

$ordenproceso = $_GET['op'];
$proceso = $_GET['proceso'];
$ordn = busca($ordenproceso, 'pr_procesosop', 'pp_id = "'.$proceso.'" AND pp_ordenp', 'pp_orden');
$mmin = busca($ordenproceso, 'pr_procesosop', 'pp_id = "' . $proceso . '" AND pp_ordenp', 'pp_orden');

$mmax = busca($ordenproceso, 'pr_procesosop', 'pp_orden > (SELECT pp_orden FROM pr_procesosop WHERE pp_orden = "' . $ordn . '" AND pp_ordenp = "' . $ordenproceso . '") AND pp_bloqueo = 1 AND pp_ordenp', 'MIN(pp_orden)');
if(empty($mmax)){
    $mmax = busca($ordenproceso, 'pr_procesosop', 'pp_orden > (SELECT pp_orden FROM pr_procesosop WHERE pp_orden = "' . $ordn . '" AND pp_ordenp = "' . $ordenproceso . '") AND pp_bloqueo = 0 AND pp_ordenp', 'MAX(pp_orden)');
}

$resultados = busca($ordenproceso, 'pr_procesosop', 'pp_orden > "' . $mmin . '" AND pp_orden <= "' . $mmax . '" AND pp_ordenp', 'COUNT(*)');


if(!$contenedor){
	if(isset($_SESSION['uid'])){
    echo  '
    <script>
        alert("Existe un proceso previo no finalizado necesario para acceder a este proceso.");
        window.close();
        window.location.href = "operador.php";
    </script>';
	} else{
        echo '
        <script>
            alert("Existe un proceso previo no finalizado necesario para acceder a este proceso.");
            window.close();
            window.location.href = "operador.php";
        </script>';
	}

}

$estop = busca($_GET['proceso'], 'pr_procesosop', 'pp_id', 'pp_estatus');
$deva = '';
if ($estop == "F") {
	$deva = '<div class="col-12 alert alert-danger" style="text-align: center;">Este proceso ya se encuentra finalizado</div>';
}

if(isset($_GET['accion']) && $_GET['accion'] == "updatedproceso"){
	$redirigir = updatedproceso();
	redirect($redirigir);
}

$cantidade = busca($_GET['op'], 'pr_articulosop', 'pra_op', 'COUNT(*)');
if ($cantidade == 0) {
    if(isset($_GET['tipo'])){
        echo '<script>
        alert("No hay artículos registrados para esta orden de producción. Verifique");
        opener.location.reload();
        window.close();
        </script>';
    } else{
        echo '<script>
        alert("No hay artículos registrados para esta orden de producción. Verifique");
            window.location.href = "../";
			// window.close();
          </script>';
    }
}

$tipo = '';
if(isset($_GET['tipo']) && $_GET['tipo'] = 'uid'){
    $tipo = "&tipo=2";
} else if(isset($_GET['tipo']) && $_GET['tipo'] = 1){
    $tipo = "&tipo=2";
} 

if (!isset($_SESSION['eid']) && !isset($_SESSION['uid'])) {

    if(isset($_GET['tipo'])){
        echo '<script>
        alert("No hay una sesión iniciada. Inicie sesión para continuar.");
        window.close();
        opener.location.reload();        
        </script>';
    } else{
        echo '<script>
            alert("No hay una sesión iniciada. Inicie sesión para continuar.");
            window.location.href = "../";
			/* window.close(); */
          </script>';
    }
}
$proceso = $_GET['proceso'];
$esmanual = busca($proceso,'pr_procesosop','pp_id','pp_tipoproceso');
if($esmanual == "M"){
    redirect('procesomanual?op='.$_GET['op'].'&proceso='.$_GET['proceso'].$tipo.'');
}

$operadores = '';
$operadorestipo = '';
$sqlppx = 'SELECT ppx_operador, ppx_tuser FROM pr_procesoexe WHERE ppx_proceso = "' . $_GET['proceso'] . '" AND ppx_ordenp = "' . $_GET['op'] . '" AND ppx_operador != "" GROUP BY ppx_operador';
$resultppx = setq($sqlppx);
while ($rowppx = $resultppx->fetch_array()) {
    if($rowppx['ppx_tuser'] == "U"){
        $operadorestipo .= "U";
        $operadores .= busca($rowppx['ppx_operador'], 'usuarios', 'u_nuser', 'u_nmb') . " " . busca($rowppx['ppx_operador'], 'usuarios', 'u_nuser', 'u_apellidos') . '<br>';
    } else {
        $operadorestipo .= "O";
        $operadores .= busca($rowppx['ppx_operador'], 'pr_empleados', 'pe_id', 'pe_nmb') . '<br>';
    }
}

?>
<!DOCTYPE html>
<!--
Author: Keenthemes
Product Name: Metronic - Bootstrap 5 HTML, VueJS, React, Angular & Laravel Admin Dashboard Theme
Purchase: https://1.envato.market/EA4JP
Website: http://www.keenthemes.com
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
License: For each use you must have a valid license purchased only from above link in order to legally use the theme for your project.
-->
<html lang="en">
<!--begin::Head-->

<head>
	<base href="">
	<title>FABRICA DE INFLABLES - PROCESO CRONOMETRADO</title>
	<meta name="description"
		content="SOMOS LA FÁBRICA #1 DE IFLABLES EN MÉXICO" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="../assets/media/logos/favicon.png" />
	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->
	<!--begin::Page Vendor Stylesheets(used by this page)-->
	<link href="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
	<!--end::Page Vendor Stylesheets-->
	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link href="../assets/css/style.css" rel="stylesheet" type="text/css" />
	<link href="../assets/css/jquery.fancybox.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" class="">
	<!--end::Global Stylesheets Bundle-->
	<script src="../assets/plugins/global/plugins.bundle.js"></script>
	<!-- <script src="../assets/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script> -->
	<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.3.js">
	</script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js">
	</script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.2.1/js/dataTables.buttons.min.js">
	</script>
	<script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js">
	</script>
	<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js">
	</script>
	<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js">
	</script>
	<script type="text/javascript" language="javascript" src="//cdn.datatables.net/buttons/1.2.1/js/buttons.html5.min.js">
	</script>
</head>
<style>
    .table-ext {
        border: 0px solid !important;
    }

    .full-height-container {
        /* border: 1px #gray; */
    }

    .timer-container {
        text-align: center;
        margin: 20px;
    }

    .timer {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .timer-segment {
        display: flex;
        align-items: center;
        flex-direction: column;
        padding: 5px;
    }

    .timer-number {
        font-size: 10rem;
    }

    .timer-label {
        font-size: 1.2rem;
    }

    .timer h2 {
        font-size: 1.5rem;
        font-weight: bold;
    }

    /* Estilos para botones (si los agregas) */
    .button {
        background-color: #007BFF;
        color: #fff;
        padding: 10px 20px;
        margin: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .button:hover {
        background-color: #0056b3;
    }

    /* Estilo base para los botones */
    .custom-button {
        /* background-color: #007BFF; */
        color: #fff;
        border: none;
        border-radius: 20px;
        cursor: pointer;
    }

    /* Estilo para el botón "Iniciar" */
    .custom-button.btn-success {
        font-size: 24px;
        padding: 10px 20px;
        margin-right: 10px;
    }

    /* Estilo para el botón "Pausa" */
    .custom-button.btn-secondary {
        font-size: 24px;
        padding: 10px 20px;
        margin-right: 10px;
    }

    /* Estilo para el botón "Detener" */
    .custom-button.btn-danger {
        font-size: 24px;
        padding: 10px 20px;
    }

    /* CSS para aumentar el tamaño de la fuente en SweetAlert */
    .my-title {
        font-size: 32px;
        /* Tamaño de fuente para el título */
    }

    .my-text {
        font-size: 27px;
        /* Tamaño de fuente para el texto */
    }

    /* Estilos table G*/
    #myTableG {
        width: 100%;
        border-collapse: collapse;
    }

    #myTableG th,
    #myTableG td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
        /* background-color: #5490c6; */
        /* Fondo gris claro para todas las celdas */
    }

    #myTableG tbody tr:nth-child(odd) td {
        /* background-color: #ffffff; */
        /* Fondo blanco para las filas impares */
    }

    #myTableG tbody tr:nth-child(even) td {
        /* background-color: #f9f9f9; */
        /* Fondo gris claro para las filas pares */
    }

    #myTableG {
        font-size: 12px;
        /* Cambia el tamaño de letra deseado */
    }
</style>
<!-- BUSCAMOS SI HAY UN REGISTRO DEL PROCESO Y ORDEN DE PRODUCCION PREVIO Y DIFERENTE A FINALIZADO -->
<?php

/* $grupou = busca($_SESSION['eid'], 'pr_empleados', 'pe_id', 'pe_rol'); */

if (isset($_SESSION['uid'])) {
    $btnreset = '
            <!-- <div class="col-12 d-flex justify-content-end"> -->
                <button id="resetButton" class="custom-button btn btn-secondary" onclick="resetContador();">
                    <i class="fas fa-history"></i>&nbsp;Reiniciar
                </button>
            <!-- </div> -->
            ';
} else {
    $btnreset = '';
}

 //Preguntamos sí el proceso está activo y está iniciado por otro usuario
 $sqlpu = 'SELECT pp_operador, pp_toperador FROM pr_procesosop WHERE pp_id = "'.$_GET['proceso'].'" AND pp_estatus IN ("A","F")';
 $resultpu = setq($sqlpu);
 list($oper, $toper) = $resultpu->fetch_array();

    $cantidadd = busca($_GET['op'], 'pr_articulosop', 'pra_estpa IN ("A", "N") AND pra_op', 'COUNT(*)');
    
    $sqle = 'SELECT ppx_id, ppx_accion, ppx_operador, ppx_horas, ppx_minutos, ppx_segundos FROM pr_procesoexe WHERE ppx_proceso = "' . $_GET['proceso'] . '" AND ppx_ordenp = "' . $_GET['op'] . '" ORDER BY ppx_id DESC LIMIT 1;';
    $resulte = setq($sqle);
    list($ppxid, $accion, $operador, $horas, $minutos, $segundos) = $resulte->fetch_array();
    if (empty($ppxid)) {

        if($cantidadd == 0){
            $disI = " disabled";
            $disP = " disabled";
            $disF = " disabled";
            $clase = "danger";

            $horas = (strlen($horas) >= 2) ? $horas : ( (!empty($horas) && strlen($horas) < 2) ? "0$horas" : "00" );
            $minutos = (strlen($minutos) >= 2) ? $minutos : ( (!empty($minutos) && strlen($minutos) < 2) ? "0$minutos" : "00" );
            $segundos = (strlen($segundos) >= 2) ? $segundos : ( (!empty($segundos) && strlen($segundos) < 2) ? "0$segundos" : "00" );

            
            $txtAlert = "Este proceso de esta orden de producción ya se encuentra finalizado.";
        } else{
            $disI = "";
            $disP = "disabled";
            $disF = "disabled";

            $horas = "00";
            $minutos = "00";
            $segundos = "0";

            $clase = "success";
            $txtAlert = "Este proceso de esta orden de producción se encuentra listo para iniciarse.";
        }
    } else {

        /* if (strlen($horas) < 2) {
            $horas = "0" . $horas;
        }

        if (strlen($minutos) < 2) {
            $minutos = "0" . $minutos;
        }

        if (strlen($segundos) < 2) {
            $segundos = "0" . $segundos;
        } */

        $horas = (strlen($horas) >= 2) ? $horas : ( (!empty($horas) && strlen($horas) < 2) ? "0$horas" : "00" );
        $minutos = (strlen($minutos) >= 2) ? $minutos : ( (!empty($minutos) && strlen($minutos) < 2) ? "0$minutos" : "00" );
        $segundos = (strlen($segundos) >= 2) ? $segundos : ( (!empty($segundos) && strlen($segundos) < 2) ? "0$segundos" : "00" );

        if ($oper != $operador2 && $tuser == $toper && !empty($oper)) {
            $disI = " disabled";
            $disP = " disabled";
            $disF = " disabled";
            $clase = "danger";
            $txtAlert = "Este proceso de esta orden de producción ya se encuentra iniciado por otro usuario";
        } else {
            if ($accion == "1" ) {
                if($oper == $operador){
                    echo '
                    <script>
                        function actualizarReg(){
                            var id = "'.$ppxid.'";
                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: "../query/tiemposprod.php",
                                data: {
                                    "pausaCarga": 1,
                                    "tpid": id
                                },
                                success: function (data) {
                                    location.reload();
                                    //console.log("Salió 1");
                                }
                            });
                        }

                        actualizarReg();
                    </script>
                    ';
                }
                $disI = " disabled";
                $disP = "";
                $disF = "";
                $clase = "primary";
                $txtAlert = "Este proceso de esta orden de producción se encuentra en ejecución.";
            } else if ($accion == "3") {
                if($oper == $operador){
                echo '
                <script>
                    function actualizarReg(){
                        var id = "'.$ppxid.'";
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: "../query/tiemposprod.php",
                            data: {
                                "pausaCarga": 1,
                                "tpid": id
                            },
                            success: function (data) {
                                location.reload();
                                //console.log("Salió 3");
                            }
                        });
                    }

                    actualizarReg();
                </script>
                ';
                }
                $disI = " disabled";
                $disP = "";
                $disF = "";
                $clase = "primary";
                $txtAlert = "Este proceso de esta orden de producción se encuentra en ejecución.";
            } else if ($accion == "2") {
                $disI = "";
                $disP = " disabled";
                $disF = "";
                $clase = "dark";
                $txtAlert = "Este proceso de esta orden de producción se encuentra en pausa.";
            } else {
                if ($cantidadd > 0) {
                    $disI = "";
                    $disP = "disabled";
                    $disF = "disabled";

                    $clase = "success";
                    $txtAlert = "Este proceso de esta orden de producción se encuentra listo para reaunudarse.";
                } else {
                    $disI = " disabled";
                    $disP = " disabled";
                    $disF = " disabled";
                    $clase = "danger";
                    $txtAlert = "Este proceso de esta orden de producción ya se encuentra finalizado";
                }
            }
        }
    }


//Buscamos los detalles del proceso y de la orden de producción
$sqlprod = 'SELECT * FROM pr_procesosop WHERE pp_id = "' . $_GET['proceso'] . '" AND pp_ordenp = "' . $_GET['op'] . '"';
$resultp = setq($sqlprod);
$rowp = $resultp->fetch_array();

    ?>
<div class="container">
<div class="row mt-5">
    <div class="col-12 col-md-6">
    <?php
	if(isset($_GET['tipo']) && $_GET['tipo'] == 1){
        ?>
        <a href="operador.php">
        <button class="mb-2 mr-2 btn btn-warning">
                  <i class="fa fa-arrow-left"></i> Atrás
            </button> 
        </a>
        <?php
    
    } else{
        if(isset($_SESSION['uid'])){
            ?>
            <button class="mb-2 mr-2 btn btn-danger" onclick="cerrarVentana();">
              <i class="fas fa-times-circle"></i> Cerrar
            </button>  
            <?php
                $txtRedireccion = 'window.close();';
            } else{
                $txtRedireccion = 'window.location.href = "operador.php";';
                ?>
                <a href="operador.php">
                <button class="mb-2 mr-2 btn btn-warning">
                  <i class="fa fa-arrow-left"></i> Atrás
                </button>
            </a>
                <?php
            }
    }
	?>  
    </div>
    <?php
        /* if(($estatuspp == "N" || $estatuspp == "A" || $estop == "P") && $resultados > 0){
            echo '
            <div class="col-12 col-md-6 d-flex justify-content-end">
                <a data-fancybox data-type="ajax" data-src="setotroproceso.php?op='.$_GET['op'].'&proceso='.$_GET['proceso'].'" href="javascript:;">
                    <button type="button" class="btn btn-info mb-1 mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i> Envíar artículos a otro proceso</button>
                </a>
            </div>';
        } */
        ?>
</div>
<?php
echo $deva;
if ($estop != "F") {
?>

<div class="row" style="background: white;">
    <div class="col-12 col-md-4 mt-14 full-height-container">
        <?php
        include('../query/detallesproceso.php');
		$detalleproceso = new detallep();
		$detalleproceso->detallesproceso();
        ?>
    </div>
    <div class="col-12 col-md-8 full-height-container">
        <table class="table table-ext table-responsive">
            <thead>
                <tr>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="timer-container">
                            <!-- <h2>Temporizador</h2> -->
                            <div id="msj-alert" class="alert alert-<?php echo $clase; ?> mt-3" role="alert">
                                <span id="mensaje">
                                    <?php echo $txtAlert; ?>
                                </span>
                            </div>
                            <?php 
                            if($mostrarbtn){//Mostramos el botón para iniciar un proceso despues de los no obligados
                                echo '
                                <div class="row">
                                <div class="col-6 col-md-6 d-flex justify-content-center">
                                    <button type="button" class="btn btn-primary" onclick="initProcess('.$_GET['op'].', '.$_GET['proceso'].');"><i class="fas fa-check"></i>Marca proceso como activo</button>
                                </div>
                                ';

                                echo '
                                <div class="col-6 col-md-6 d-flex justify-content-center">
                                    <button type="button" class="btn btn-danger" onclick="finProcess('.$_GET['op'].', '.$_GET['proceso'].');"><i class="fas fa-window-close"></i>Marca proceso como finalizado</button>
                                </div>
                                </div>
                                ';


                                $disI = " disabled";
                                $disP = " disabled";
                                $disF = " disabled";
                            }
                            ?>
                            <div class="timer">
                                <div class="timer-segment">
                                    <div class="timer-number" id="hours">
                                        <?php echo $horas; ?>
                                    </div>
                                    <div class="timer-label">Horas</div>
                                </div>
                                <div class="timer-segment">
                                    <div class="timer-number">:</div>
                                    <div class="timer-label"></div>
                                </div>
                                <div class="timer-segment">
                                    <div class="timer-number" id="minutes">
                                        <?php echo $minutos; ?>
                                    </div>
                                    <div class="timer-label">Minutos</div>
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td class="text-center">
                        <input type="hidden" id="adj1" value="">
                        <input type="hidden" id="retro1" value="">
                        <input type="hidden" id="cantidad1" value="">
                        <input type="hidden" id="articuloSel" value="">
                        <input type="hidden" id="hidden-seconds" value="<?php echo $segundos; ?>">
                        <input type="hidden" id="tpid" value="<?php echo $ppxid; ?>">
                        <input type="hidden" id="ordenprod" value="<?php echo $_GET['op']; ?>">
                        <input type="hidden" id="proceso" value="<?php echo $_GET['proceso']; ?>">
                        <button id="startButton" class="custom-button btn btn-success" <?php echo $disI; ?>><i
                                class="fas fa-play-circle"></i>&nbsp;Iniciar</button>
                        <button id="pauseButton" class="custom-button bnt btn-secondary" <?php echo $disP; ?>><i
                                class="fas fa-pause-circle"></i>&nbsp;Pausa</button>
                        <button id="stopButton" class="custom-button btn btn-danger" <?php echo $disF; ?>><i
                                class="fas fa-stop-circle"></i>&nbsp;Detener</button>
                        <?php echo $btnreset; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
</div>
<?php
} else{

    $sql = 'SELECT * FROM pr_procesosop WHERE pp_id = "' . $_GET['proceso'] . '"';
			$result = setq($sql);
			$row = $result->fetch_array();

            $nmbp = busca($row['pp_proceso'], 'pr_procesos', 'ppr_id', 'ppr_nmb');
			$foliordenp = busca($_GET['op'], 'pr_ordenprod', 'po_id', 'po_folio');
			$articulordenp = busca($_GET['op'], 'pr_ordenprod', 'po_id', 'po_nmbarticulo');

			$foliosolicitud = busca($row['pp_solicitud'], 'pr_solicitudes', 'ps_id', 'ps_folio');
			if ($row['pp_tiposolicitud'] == "P") {
				$tsolicitud = 'PRODUCCIÓN';
			} else if ($row['pp_tiposolicitud'] == "M") {
				$tsolicitud = 'MARKETING';
			} else if ($row['pp_tiposolicitud'] == "G") {
				$tsolicitud = 'GARANTÍAS';
			} else {
				$tsolicitud = 'OTRO';
			}

			$fini = fecha_formato($row['pp_fini'], true, false);
			$ffin = fecha_formato($row['pp_ffin'], true, false);

			$fase = busca($row['pp_fase'], 'pr_fases', 'pf_id', 'pf_nmb');

			$supervisor = $row['pp_supervisor'];
			if ($row['pp_tipoproceso'] == "M") {
				$tproceso = 'MANUAL';
			} else {
				$tproceso = 'CRONOMETRADO';
			}

			$observaciones = $row['pp_obs'];

			if (empty($observaciones)) {
				$html1 = '
			<textarea class="form-control" rows="4" id="obs" name="obs" required></textarea>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("obs").focus();
                });
            </script>
			';
				$html2 = '
			<input class="form-control" type="file" id="adj" name="adj">
			';

				$botonG = '
			<div class="col-12 col-md-12">
				<center>
					<button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i>Guardar</button>
				</center>
			</div>
			<div class="col-12 col-md-12 mt-14">
			</div>
			';
			} else {
				$html1 = $observaciones;
                if(empty($row['pp_adj'])){
                    $html2 = '<em>No se adjunto una imagen al finalizar el proceso</em>';
                } else{
                    $html2 = '
                    <a href="" onclick="abrirVentana(); return false;">Ver Imagen</a>

                    <script>
                        function abrirVentana() {
                            // URL de la imagen que deseas mostrar
                            var urlImagen = "' . $row['pp_adj'] . '";
                            
                            // Abre una nueva ventana con la imagen
                            window.open(urlImagen, "Imagen", "width=600,height=400");
                        }
                    </script>
                    ';
                }
                $botonG = '';
			}


			if (isset($_GET['tipo'])) {
				$tipo = "&tipo=" . $_GET['tipo'];
			} else {
				$tipo = '';
			}

			echo '
		<form class="row" method="post" enctype="multipart/form-data" action="temporizadorpps.php?accion=updatedproceso&origen=1' . $tipo . '">
		<input type="hidden" id="ordenp" name="ordenp" value="' . $_GET['op'] . '">
		<input type="hidden" id="proceso" name="proceso" value="' . $_GET['proceso'] . '">

		<div class="row" style="background: white;">
    	<div class="col-12 col-md-12 mt-2 full-height-container">
		<center>
        <table id="myTableG" class="table">
            <thead style=" background-color: #009ef7; color: white;">
			<th colspan="2"><center>Detalles del proceso</center></th>
		</thead>
		<tbody>
            <tr>
				<td style="width: 30%;">Proceso: </td>
				<td style="width: 70%;">' . $nmbp . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Fase: </td>
				<td style="width: 70%;">' . $fase . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Orden de producción: </td>
				<td style="width: 70%;">' . $foliordenp . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Artículo en producción: </td>
				<td style="width: 70%;">' . $articulordenp . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Solicitud de producción: </td>
				<td style="width: 70%;">' . $foliosolicitud . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Tipo de solicitud: </td>
				<td style="width: 70%;">' . $tsolicitud . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Fecha de inicio: </td>
				<td style="width: 70%;">' . $fini . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Fecha final: </td>
				<td style="width: 70%;">' . $ffin . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Operador(es): </td>
				<td style="width: 70%;">' . $operadores . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Supervisor: </td>
				<td style="width: 70%;">' . $supervisor . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Observaciones: </td>
				<td style="width: 70%;">' . $html1 . '</td>
			</tr>
			<tr>
				<td style="width: 30%;">Adjunto: </td>
				<td style="width: 70%;">' . $html2 . '</td>
			</tr>
		</tbody>
		</table>
		</center>
		</div>';
			echo $botonG;
			echo '
		</div>
		</form>
		';
}
?>
<script>
		var hostUrl = "../assets/";
	</script>
	<!--begin::Javascript-->
	<!--begin::Global Javascript Bundle(used by all pages)-->
	<script src="../assets/js/scripts.bundle.js"></script>
	<!--end::Global Javascript Bundle-->
	<!--begin::Page Vendors Javascript(used by this page)-->
	<script src="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
	<!--end::Page Vendors Javascript-->
	<!--begin::Page Custom Javascript(used by this page)-->
	<script src="../assets/js/custom/widgets.js"></script>
	<script src="../assets/js/custom/apps/chat/chat.js"></script>
	<script src="../assets/js/custom/modals/create-app.js"></script>
	<script src="../assets/js/custom/modals/upgrade-plan.js"></script>
	<script src="../assets/js/jquery.fancybox.min.js"></script>

<script>
    // Crear un nuevo Web Worker desde una cadena de URL
    var running = false;

    // main.js
    const worker = new Worker('js/timeWorker.js');

    const hoursElement = document.getElementById("hours");
    const minutesElement = document.getElementById("minutes");
    const hiddenSecondsElement = document.getElementById("hidden-seconds");
    const startButton = document.getElementById("startButton");
    const pauseButton = document.getElementById("pauseButton");
    const stopButton = document.getElementById("stopButton");

    let totalSeconds = 0;

    if (parseInt(hiddenSecondsElement.value) > 0) {
        totalSeconds = hiddenSecondsElement.value;
    }

    startButton.addEventListener("click", function () {
        abrirPopup("I");
    });

    pauseButton.addEventListener("click", function () {
        abrirPopup("P");
    });

    stopButton.addEventListener("click", function () {
        abrirPopup("F");
    });
    /* pauseButton.addEventListener("click", pauseTimer);
    stopButton.addEventListener("click", stopTimer); */


    var txtComplemento = '';

    // Escuchar mensajes del Web Worker
    worker.onmessage = function (event) {
    const { hours, minutes, seconds, run} = event.data;

        if(totalSeconds % 30 === 0 && totalSeconds != 0){
            /* console.log("Checkpoint: "+totalSeconds); */
            checkPointTimer();
        }

        /* console.log("totalSeconds: "+totalSeconds); */
        totalSeconds++;

        if(hours < 10){
            txtComplemento = "0"+hours;
        } else{
            txtComplemento = hours;
        }
        hoursElement.textContent = txtComplemento;

        if(minutes < 10){
            txtComplemento = "0"+minutes;
        } else{
            txtComplemento = minutes;
        }

        minutesElement.textContent = txtComplemento;
        hiddenSecondsElement.value = seconds;
        running = run;
    };


    function setValues() {
        var seconds = parseInt(document.getElementById("hidden-seconds").value, 10);
        var minutes = parseInt(document.getElementById("minutes").textContent, 10);
        var hours = parseInt(document.getElementById("hours").textContent, 10);

        /* console.log("Hora: " + hours);
        console.log("Minutos: " + minutes);
        console.log("segundos: " + seconds); */

        var horas = hours * 3600;
        var minutos = minutes * 60;

        totalSeconds = minutos + horas + seconds;

        /* console.log("totalSeconds SetValues: "+totalSeconds); */

        // Envía el objeto directamente al worker
        worker.postMessage({
            action: 'setValues',
            seconds: seconds,
            minutes: minutes,
            hours: hours
        });
    }

    // Función para mostrar la alerta con el contador
    function showCountdownAlert() {
        updateCountdown(5);
        Swal.fire({
            title: 'Cuenta regresiva',
            text: 'Comenzando en ' + 5 + ' segundos',
            timer: 5 * 1000,
            timerProgressBar: true,
            showConfirmButton: false, // Oculta el botón "Ok"
            showCancelButton: true, // Muestra el botón de "Cancelar"
            cancelButtonColor: '#d33', // Color rojo
            cancelButtonText: 'Cancelar', // Texto del botón de "Cancelar"
            onOpen: () => {
                Swal.showLoading();
            }
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                // La cuenta regresiva llegó a 0, ejecuta la función
                startTimer();
            }
        });
    }


    // Función para actualizar y mostrar la cuenta regresiva en SweetAlert
    function updateCountdown(seconds) {
        if (seconds >= 0) {
            Swal.update({
                title: 'Cuenta regresiva',
                text: 'Comenzando en ' + seconds + ' segundos',
            });

            setTimeout(() => {
                updateCountdown(seconds - 1);
            }, 1000); // Actualiza cada segundo
        }
    }

    function abrirPopup(tipo) {
        var tpid = document.getElementById("tpid").value;
        if (!tpid) {
            url = 'settemporizador?tipo=' + tipo + '&idp=' + '<?php echo $_GET['op']; ?>' + '&proceso=' + '<?php echo $_GET['proceso']; ?>';
        } else {
            url = 'settemporizador?id=' + tpid + '&tipo=' + tipo + '&idp=' + '<?php echo $_GET['op']; ?>' + '&proceso=' + '<?php echo $_GET['proceso']; ?>';
        }
        // Abre Fancybox con los atributos especificados
        $.fancybox.open({
            src: url,
            type: 'ajax'
        });
    }

    window.addEventListener('datosEnviados', function (event) {
        var datosRecibidos = event.detail;
        var adj4 = datosRecibidos.adj3;
        var retro4 = datosRecibidos.retro3;
        var cantidad4 = datosRecibidos.cantidad3;
        var tipo = datosRecibidos.tipo;
        var articuloSel = datosRecibidos.articuloSel 

        /* console.log("Tipo xd: "+tipo);] */
        // Ahora puedes utilizar estos valores en la pantalla principal
        document.getElementById("cantidad1").value = cantidad4;
        document.getElementById("adj1").value = adj4;
        document.getElementById("retro1").value = retro4;
        document.getElementById("articuloSel").value = articuloSel;
        if (tipo == "I") {
            showCountdownAlert();
        } else if (tipo == "P") {
            pauseTimer();
        } else {
            stopTimer();
        }
    });


    function startTimer() {
        if (!running) {
        setValues();
        /* console.log("StartTime"); */
        worker.postMessage({ action: 'start' });

        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = document.getElementById("hidden-seconds").value;
        var ordenprod = document.getElementById("ordenprod").value;
        var proceso = document.getElementById("proceso").value;
        var tpid = document.getElementById("tpid");
        var msja = document.getElementById("msj-alert");
        var mensaje = document.getElementById("mensaje");

        var fechainicio = document.getElementById("fechainicio");
        var operador = document.getElementById("operador");        

        var cantidad = document.getElementById("cantidad1").value;
        var retro = document.getElementById("retro1").value;
        var adj = document.getElementById("adj1").value;
        var articuloSel = document.getElementById("articuloSel").value;
            startButton.disabled = true;
            pauseButton.disabled = false;
            stopButton.disabled = false;

            $.ajax({
                dataType: "json",
                type: "POST",
                url: "../query/tiemposprod.php",
                data: {
                    "iniciar": 1,
                    "proceso": proceso,
                    "ordenprod": ordenprod,
                    "horas": hours,
                    "minutos": minutes,
                    "segundos": seconds,
                    "tpid": tpid.value,
                    "retro": retro,
                    "adj": adj,
                    "cantidad": cantidad,
                    "articuloSel": articuloSel
                },
                success: function (data) {
                    //console.log("Se pone 1");
                    tpid.value = data.respuesta;
                    msja.classList.remove("alert-success");
                    msja.classList.remove("alert-dark");
                    msja.classList.add("alert-primary");
                    mensaje.innerHTML = "Este proceso de esta orden de producción se encuentra en ejecución.";
                    operador.innerHTML = data.operador;
                    fechainicio.innerHTML = data.fechainicio;
                }
            });
        }
    }


    function pauseTimer() {
        setValues();
        /* console.log("PauseTime"); */
        worker.postMessage({ action: 'pause' });

        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = document.getElementById("hidden-seconds").value;
        var ordenprod = document.getElementById("ordenprod").value;
        var proceso = document.getElementById("proceso").value;
        var tpid = document.getElementById("tpid");
        var msja = document.getElementById("msj-alert");
        var mensaje = document.getElementById("mensaje");

        var cantidad = document.getElementById("cantidad1").value;
        var retro = document.getElementById("retro1").value;
        var adj = document.getElementById("adj1").value;

        /* clearInterval(timer);
        running = false; */
        startButton.disabled = false;
        pauseButton.disabled = true;
        stopButton.disabled = false;

        $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/tiemposprod.php",
            data: {
                "pausar": 1,
                "proceso": proceso,
                "ordenprod": ordenprod,
                "horas": hours,
                "minutos": minutes,
                "segundos": seconds,
                "tpid": tpid.value,
                "retro": retro,
                "adj": adj,
                "cantidad": cantidad
            },
            success: function (data) {
                //console.log("Se pone 2");
                tpid.value = data.respuesta;
                msja.classList.remove("alert-primary");
                msja.classList.add("alert-dark");
                mensaje.innerHTML = "Este proceso de esta orden de producción se encuentra en pausa.";
            }
        });
        
    }

    function stopTimer() {
        setValues();
        worker.postMessage({ action: 'stop' });

        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = document.getElementById("hidden-seconds").value;
        var ordenprod = document.getElementById("ordenprod").value;
        var proceso = document.getElementById("proceso").value;
        var tpid = document.getElementById("tpid");
        var msja = document.getElementById("msj-alert");
        var mensaje = document.getElementById("mensaje");

        var fechafin = document.getElementById("fechafin");

        var cantidad = document.getElementById("cantidad1").value;
        var retro = document.getElementById("retro1").value;
        var adj = document.getElementById("adj1").value;
        var articuloSel = document.getElementById("articuloSel").value;

        /* clearInterval(timer);
        running = false;
        updateDisplay(); */
        startButton.disabled = false;
        pauseButton.disabled = true;
        stopButton.disabled = true;

        $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/tiemposprod.php",
            data: {
                "finalizar": 1,
                "proceso": proceso,
                "ordenprod": ordenprod,
                "horas": hours,
                "minutos": minutes,
                "segundos": seconds,
                "tpid": tpid.value,
                "retro": retro,
                "adj": adj,
                "cantidad": cantidad,
                "articuloSel": articuloSel
            },
            success: function (data) {
                //console.log("Se pone 3");
                if (data.respuesta == "e1") {
                    /* tpid.value = data; */
                    msja.classList.remove("alert-primary");
                    msja.classList.remove("alert-dark");
                    msja.classList.remove("alert-danger");
                    msja.classList.add("alert-success");
                    mensaje.innerHTML = "Este proceso de esta orden de producción se encuentra listo para reaunudarse.";
                } else {
                    tpid.value = "";
                    msja.classList.remove("alert-dark");
                    msja.classList.remove("alert-primary");
                    msja.classList.add("alert-danger");
                    mensaje.innerHTML = "Este proceso de esta orden de producción ya se encuentra finalizado.";
                    startButton.setAttribute("disabled", true);
                    pauseButton.setAttribute("disabled", true);
                    stopButton.setAttribute("disabled", true);

                    fechafin.innerHTML = data.fechafin;       
                    location.reload();             
                    /* totalSeconds = 0;
                    hiddenSecondsElement.value = 0; */
                }
            }
        });
    }

    function checkPointTimer() {
        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = document.getElementById("hidden-seconds").value;
        var ordenprod = document.getElementById("ordenprod").value;
        var proceso = document.getElementById("proceso").value;
        var tpid = document.getElementById("tpid");
        var msja = document.getElementById("msj-alert");
        var mensaje = document.getElementById("mensaje");

        /* console.log("hours checkpoint: "+hours);
        console.log("minutes checkpoint: "+minutes);
        console.log("Segundos checkpoint: "+seconds); */

        $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/tiemposprod.php",
            data: {
                "checkpoint": 1,
                "proceso": proceso,
                "ordenprod": ordenprod,
                "horas": hours,
                "minutos": minutes,
                "segundos": seconds,
                "tpid": tpid.value
            },
            success: function (data) {
                //console.log("Se pone 4");
                /* console.log("Registro ID "+tpid.value+" actualizado"); */
            }
        });
        
    }

    function resetContador() {
        var ordenprod = document.getElementById("ordenprod").value;
        var proceso = document.getElementById("proceso").value;

        $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/tiemposprod.php",
            data: {
                "reset": 1,
                "proceso": proceso,
                "ordenprod": ordenprod
            },
            success: function (data) {
                //console.log("Se pone 5");
                var popup = window.open("temporizadorpps.php?op=" + "<?php echo $_GET['op']; ?>" + "&proceso=" + "<?php echo $_GET['proceso']; ?>", "MiPopup");
            }
        });

    }

    function actualizarOperador(){
        //var operador = "<?php echo $operador2; ?>";
        var operador = document.getElementById("operadores").value;
        var tuser = "<?php echo $tuser; ?>";
        var op = "<?php echo $_GET['op']; ?>";
        var proceso = "<?php echo $_GET['proceso']; ?>";

        $.ajax({
            type: "POST",
            url: "../query/actualizaroperador.php",
            data: {
                "operador": operador,
                "tuser": tuser,
                "proceso": proceso,
                "ordenprod": op
            },
            success: function (data) {
                location.reload();
            }
        });

    }

    /* function cerrarVentana(){
        console.log("cerrar");
        window.close();
    } */

    function initProcess(op, proceso){
        var operador = "<?php echo $ppoperador; ?>";
        var tuser = "<?php echo $tuser; ?>";
        var lmin = "<?php echo $lmin; ?>";

        Swal.fire({
        title: "Atención",
        text: "¿Estas seguro de marcar este proceso como activo?. Esta acción es irreversible.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Estoy seguro"
        }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "../query/initproceso.php",
                data: {
                    "tipo": "iniciar",
                    "operador": operador,
                    "proceso": proceso,
                    "ordenp": op,
                    "tuser": tuser,
                    "lmin": lmin //Representa el id del proceso donde se ubican los artículos que queremos mandar al iguiente proceso
                },
                success: function (data) {
                    location.reload();
                }
            });
        }
        });
    }

    function finProcess(op, proceso){
        var operador = "<?php echo $ppoperador; ?>";
        var tuser = "<?php echo $tuser; ?>";
        var lmin = "<?php echo $lmin; ?>";

        Swal.fire({
        title: "Atención",
        text: "¿Estas seguro de marcar este proceso como finalizado?. Esta acción es irreversible.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Estoy seguro"
        }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "../query/initproceso.php",
                data: {
                    "tipo": "finalizar",
                    "operador": operador,
                    "proceso": proceso,
                    "ordenp": op,
                    "tuser": tuser,
                    "lmin": lmin //Representa el id del proceso donde se ubican los artículos que queremos mandar al iguiente proceso
                },
                success: function (data) {
                    location.reload();
                }
            });
        }
        });
    }

    function cerrarVentana(){
        console.log("cerrar");
        $.ajax({
            dataType: "json",
            type: "POST",
            url: "../query/estatusproceso.php",
            data: {
                "estatus": 0,
                "all": 0,
                "proceso": "<?php echo $_GET['proceso'] ?>",
                "ordenp": "<?php echo $_GET['op']; ?>"
            },
            success: function (data) {
              if(data == 0){
                alert("Otro usuario está interactuando con este proceso.");
                window.close();
                window.location.href = "operador.php"; 
              } else {
                window.close();
              }
            }
          });
          /* alert("Se va a cerrar"); */
    }

    // Registrar la función para el evento 'beforeunload'
    /* window.addEventListener('beforeunload', cerrarVentana()); */
    window.addEventListener('beforeunload', function (event) {
        cerrarVentana()

        // Por ejemplo, puedes mostrar un mensaje de despedida
        event.returnValue = '¿Estás seguro de cerrar la ventana?';
    });



</script>