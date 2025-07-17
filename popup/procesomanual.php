<?php
include_once('../funciones.php');
include_once('../modulos/ordenprod.php');
session_start();
$estop = busca($_GET['proceso'], 'pr_procesosop', 'pp_id', 'pp_estatus');
$contenedor = false;
$mostrarbtn = false;
$deva = '';

if(isset($_SESSION['uid'])){
    $tuser = 'U';
    $ppoperador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else{
    $tuser = 'O';
    $ppoperador = $_SESSION['eid'];
}

if ($estop == "F") {
	$deva = '<div class="col-12 alert alert-danger" style="text-align: center;">Este proceso ya se encuentra finalizado</div>';
} else {
	$deva = '<div class="col-12 alert alert-primary" style="text-align: center;" id="tiempoRestante">Tiempo restante: 05:00</div>';
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


if(isset($_GET['accion'])){
	$redirigir = updatedproceso();
	redirect($redirigir);
}

$operadores = '';
$sqlppx = 'SELECT ppx_operador FROM pr_procesoexe WHERE ppx_proceso = "' . $_GET['proceso'] . '" AND ppx_ordenp = "' . $_GET['op'] . '" AND ppx_operador != "" GROUP BY ppx_operador';
$resultppx = setq($sqlppx);
while ($rowppx = $resultppx->fetch_array()) {
    $operadores .= busca($rowppx['ppx_operador'], 'pr_empleados', 'pe_id', 'pe_nmb') . '<br>';
}

$ordenproceso = $_GET['op'];
$proceso = $_GET['proceso'];
$ordn = busca($ordenproceso, 'pr_procesosop', 'pp_id = "'.$proceso.'" AND pp_ordenp', 'pp_orden');
$mmin = busca($ordenproceso, 'pr_procesosop', 'pp_id = "' . $proceso . '" AND pp_ordenp', 'pp_orden');

$mmax = busca($ordenproceso, 'pr_procesosop', 'pp_orden > (SELECT pp_orden FROM pr_procesosop WHERE pp_orden = "' . $ordn . '" AND pp_ordenp = "' . $ordenproceso . '") AND pp_bloqueo = 1 AND pp_ordenp', 'MIN(pp_orden)');
if(empty($mmax)){
    $mmax = busca($ordenproceso, 'pr_procesosop', 'pp_orden > (SELECT pp_orden FROM pr_procesosop WHERE pp_orden = "' . $ordn . '" AND pp_ordenp = "' . $ordenproceso . '") AND pp_bloqueo = 0 AND pp_ordenp', 'MAX(pp_orden)');
}

$resultados = busca($ordenproceso, 'pr_procesosop', 'pp_orden > "' . $mmin . '" AND pp_orden <= "' . $mmax . '" AND pp_ordenp', 'COUNT(*)');

// Resto de tu código aquí
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
	<title>FABRICA DE INFLABLES - PROCESO MANUAL</title>
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

<body style="background: white;">
	<style>
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

		#myTableG {
			font-size: 12px;
			/* Cambia el tamaño de letra deseado */
		}
	</style>
	<div class="container">
		<div class="row mt-5">
			<div class="col-12 col-md-6">
				<?php

				if (isset($_GET['tipo']) && $_GET['tipo'] == 1) {
					?>
					<a href="operador.php">
						<button class="mb-2 mr-2 btn btn-warning">
							<i class="fa fa-arrow-left"></i> Atrás
						</button>
					</a>
					<?php

				} else {
					if (isset($_SESSION['uid'])) {
						?>
						<button class="mb-2 mr-2 btn btn-danger" onclick="cerrarVentana();">
							<i class="fas fa-times-circle"></i> Cerrar
						</button>
						<?php
						$txtRedireccion = 'window.close();';
					} else {
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
			if(($estop == "N" || $estop == "A" || $estop == "P") && $resultados > 0){
				echo '
				<div class="col-12 col-md-6 d-flex justify-content-end">
					<a data-fancybox data-type="ajax" data-src="setotroproceso.php?op='.$_GET['op'].'&proceso='.$_GET['proceso'].'" href="javascript:;">
						<button type="button" class="btn btn-info mb-1 mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i> Envíar artículos a otro proceso</button>
					</a>
				</div>';
			}
			?>
		</div>
		<?php
		echo $deva;
		if($mostrarbtn){//Mostramos el botón para iniciar un proceso despues de los no obligados
			echo '
			<div class="row">
			<div class="col-6 col-md-9 d-flex justify-content-end">
				<button type="button" class="btn btn-primary" onclick="initProcess('.$_GET['op'].', '.$_GET['proceso'].');"><i class="fas fa-check"></i>Marca proceso como activo</button>
			</div>
			';
		
			echo '
			<div class="col-6 col-md-3 d-flex justify-content-center">
				<button type="button" class="btn btn-danger" onclick="finProcess('.$_GET['op'].', '.$_GET['proceso'].');"><i class="fas fa-window-close"></i>Marca proceso como finalizado</button>
			</div>
			</div>
			<br><br>
			';

			$ocultar = "readonly disabled";
		} else{
			$ocultar = "";
		}
		if ($estop != "F") {
			echo '
		<div class="">
		<div class="row">

		<div class="col-12 col-md-4 full-height-container">';
		include('../query/detallesproceso.php');
		$detalleproceso = new detallep();
		$detalleproceso->detallesproceso();
		echo '
		</div>

		<div class="col-12 col-md-8">
		<form class="row" method="post">
		<div class="mb-5 col-md-4">
            <label>Fecha:</label>
            <input class="form-control" value="' . fecha_formato(date("Y-m-d H:i:s"), true, false) . '" readonly>
        </div>
        <div class="mb-5 col-md-4">
            <label>Cantidad:</label>
            <input class="form-control" placeholder="Ingrese una cantidad" type="number" step="1" min="1"
                name="cantidad2" id="cantidad2" '.$ocultar.'>
        </div>
        <div class="mb-5 col-md-4">
            <label>Adjunto:</label>
            <input type="file" name="archivo" id="archivo" class="form-control" '.$ocultar.'>
        </div>
		<div class="mb-5 col-md-4">
            <label>Tipo:</label>
            <select class="form-control" id="tipo" name="tipo" onchange="seleccionarTipo();" '.$ocultar.'>
				<option value="">Selecciona una opción</option>
				<option value="1">Iniciar proceso</option>
				<option value="4">Finalizar proceso</option>
			</select>
        </div>
		<div class="mb-5 col-md-8">
            <label>Retroalimentación:</label>
            <textarea id="retro2" name="retro2" class="form-control focus"
                placeholder="Escribe una breve retroalimentación" '.$ocultar.'></textarea>
        </div>
		<div class="col-12 col-md-12" id="tablaart">
		</div>

        <div class="mb-5 col-md-12 mt-5">
            <center><button type="button" disabled id="btnG" class="btn btn-primary" onclick="guardarCambios();"><i class="fa fa-save" '.$ocultar.'></i>
                    Guardar</button></center>
        </div>
    </form>
	</div>
	</div>
	</div>';
		} else {
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
			';
				$html2 = '
			<input class="form-control" type="file" id="adj" name="adj" required>
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
				$botonG = '';
			}

			if (isset($_GET['tipo'])) {
				$tipo = "&tipo=" . $_GET['tipo'];
			} else {
				$tipo = '';
			}

			echo '
		<form class="row" method="post" enctype="multipart/form-data" action="procesomanual.php?accion=updatedproceso&origen=1' . $tipo . '">
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
	</div>
	</div>


</body>

<!--end::Scrolltop-->
<!--end::Main-->
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
<!--end::Page Custom Javascript-->
<script>

	const worker = new Worker('js/timeWorkerTemp.js');
	var tiempoRestante = document.getElementById("tiempoRestante");

	var txtComplemento = '';
	var txtTiempo = '';

	worker.onmessage = function (event) {
		const { minutes, seconds, run } = event.data;

		if (minutes < 10) {
			txtComplemento = "0" + minutes;
		} else {
			txtComplemento = minutes;
		}

		txtTiempo = txtComplemento;

		if (seconds < 10) {
			txtComplemento = "0" + seconds;
		} else {
			txtComplemento = seconds;
		}

		txtTiempo = txtTiempo + ":" + txtComplemento;

		tiempoRestante.textContent = "Tiempo restante: " + txtTiempo;
		running = run;
		if (minutes == 0 && seconds == 0) {
			// Obtén una referencia al botón por su clase
			<?php echo $txtRedireccion; ?>
		}
	};

	var btnG = document.getElementById("btnG");

	function seleccionarTipo() {
		var tipo = document.getElementById("tipo").value;
		var ordenp = "<?php echo $_GET['op'] ?>";
		var proceso = "<?php echo $_GET['proceso'] ?>";
		var estatus = '';
		if (tipo == 1) {
			//iniciar
			estatus = "I";
		} else if (tipo == 4) {
			//Finalizar
			estatus = "F";
		} else {
			//No hacer nada y
			estatus = '';
		}

		if (tipo != '') {
			$.ajax({
				type: "POST",
				url: "../query/extraerdatos.php",
				data: {
					"estatus": estatus,
					"ordenp": ordenp,
					"proceso": proceso
				},
				success: function (response) {
					$("#tablaart").html(response);
					btnG.removeAttribute("disabled");

				}
			});
		} else {
			$("#tablaart").html('');
			btnG.setAttribute("disabled", true);
		}
	}

	// Función para contar checkboxes chequeados y no deshabilitados
	function contarCheckboxes() {
		// Obtener todos los checkboxes con la clase "check-all"
		var checkboxes = document.querySelectorAll('.check-all');

		// Filtrar los checkboxes para obtener solo los chequeados y no deshabilitados
		var checkboxesChequeados = Array.from(checkboxes).filter(function (checkbox) {
			return checkbox.checked && !checkbox.disabled;
		});

		// Obtener la cantidad de checkboxes chequeados y no deshabilitados
		var cantidadCheckboxesChequeados = checkboxesChequeados.length;

		// Mostrar la cantidad en la consola (puedes hacer lo que desees con esta información)
		return cantidadCheckboxesChequeados;
	}

	function validarCampos() {
		var retroalimentacion = document.getElementById("retro2").value;
		var cantidad = document.getElementById("cantidad2").value;
		var tipo = document.getElementById("tipo").value;
		var texto = '';
		var checkboxes = contarCheckboxes();

		/* console.log("Checkboxes: "+checkboxes+" y cantidad: "+cantidad); */

		if (cantidad != checkboxes) {
			Swal.fire({
				text: "La cantidad ingresada no coincide con los artículos seleccionados. Vefifique.",
				icon: "warning"
			});
			return false; // Los campos están vacíos, la validación falla
		}

		if (cantidad == 0) {
			Swal.fire({
				text: "La cantidad no puede ser 0",
				icon: "warning"
			});
			return false; // Los campos están vacíos, la validación falla
		}

		if (retroalimentacion.trim() === "" || cantidad.trim() === "" || tipo.trim() === "") {
			if (cantidad.trim() === "") {
				texto = 'El campo "Cantidad" no puede estar vacío';
			} else if (tipo.trim() === "") {
				texto = 'El campo "Tipo" no puede estar vacío';
			} else {
				texto = 'El campo "Retroalimentación" no puede estar vacío';
			}
			Swal.fire({
				text: texto,
				icon: "warning"
			});
			return false; // Los campos están vacíos, la validación falla
		}

		// Si llegamos aquí, los campos no están vacíos
		return true; // La validación es exitosa
	}

	function guardarCambios() {
		if (validarCampos()) {
			var retro4 = document.getElementById("retro2").value;
			var archivo = $("#archivo")[0].files[0]; // Ten en cuenta que si estás enviando un archivo, debes manejarlo de manera diferente
			var cantidad4 = document.getElementById("cantidad2").value;
			var ordenp = "<?php echo $_GET['op'] ?>";
			var proceso = "<?php echo $_GET['proceso'] ?>";
			var tipo = document.getElementById("tipo").value;
			var articuloSel = obtenerValoresComoCadena();

			// Crea un objeto FormData para enviar los datos
			var formData = new FormData();
			formData.append("retro", retro4);
			formData.append("archivo", archivo);
			formData.append("cantidad", cantidad4);
			formData.append("tipo", tipo);
			formData.append("ordenp", ordenp);
			formData.append("proceso", proceso);
			formData.append("tipo", tipo);
			formData.append("articuloSel", articuloSel);

			// Realiza la solicitud AJAX
			$.ajax({
				type: "POST",
				url: "../query/datosprocesomanual.php",
				dataType: 'json',
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					window.location.reload();
				}
			});
		}
	}

	function obtenerValoresComoCadena() {
		// Selecciona todos los elementos checkbox con la clase "check-all" que estén marcados
		var checkboxes = document.querySelectorAll(".check-all:checked");

		// Crea una cadena para almacenar los valores separados por comas
		var valoresCadena = "";

		// Recorre los elementos checkbox y agrega sus valores a la cadena
		checkboxes.forEach(function (checkbox, index) {
			// Agrega una coma si no es el primer elemento
			if (index > 0) {
				valoresCadena += ",";
			}
			valoresCadena += checkbox.value;
		});

		// Devuelve la cadena de valores separados por comas
		return valoresCadena;
	}

	function cerrarVentana() {
		window.close();
	}

	<?php
	if ($estop != "F") {
		echo 'worker.postMessage({ action: "start" });';
	}
	?>

	function cerrarVentanaF(){
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
			}
			}
		});
		/* alert("Se va a cerrar"); */
	}

    // Registrar la función para el evento 'beforeunload'
    window.addEventListener('beforeunload', cerrarVentanaF());

	function initProcess(op, proceso){
        var operador = "<?php echo $ppoperador; ?>";
        var tuser = "<?php echo $tuser; ?>";

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
                    "tuser": tuser
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
                    "tuser": tuser
                },
                success: function (data) {
                    location.reload();
                }
            });
        }
        });
    }

</script>
<!--end::Javascript-->
</body>
<!--end::Body-->

</html>