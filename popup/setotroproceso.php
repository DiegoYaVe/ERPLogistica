<?php
session_start();
/* ini_set('display_errors',1); */
include_once('../funciones.php');
include('../modulos/ordenprod.php');

$ordenproceso = $_GET['op'];
$_GET['idp'] = $_GET['op'];
$_GET['tipo'] = "Z";
$proceso = $_GET['proceso'];

$estatuspp = busca($ordenproceso, 'pr_procesosop', 'pp_id = "' . $proceso . '" AND pp_ordenp', 'pp_estatus');
$ordenp = busca($ordenproceso, 'pr_procesosop', 'pp_id = "'.$proceso.'" AND pp_ordenp', 'pp_orden');
//Verificamos que no exista un bloqueo del proceso actual
$lmin = busca($ordenproceso, 'pr_procesosop', 'pp_id = "' . $proceso . '" AND pp_ordenp', 'pp_orden');

$lmax = busca($ordenproceso, 'pr_procesosop', 'pp_orden > (SELECT pp_orden FROM pr_procesosop WHERE pp_orden = "' . $ordenp . '" AND pp_ordenp = "' . $ordenproceso . '") AND pp_bloqueo = 1 AND pp_ordenp', 'MIN(pp_orden)');
if(empty($lmax)){
    $lmax = busca($ordenproceso, 'pr_procesosop', 'pp_orden > (SELECT pp_orden FROM pr_procesosop WHERE pp_orden = "' . $ordenp . '" AND pp_ordenp = "' . $ordenproceso . '") AND pp_bloqueo = 0 AND pp_ordenp', 'MAX(pp_orden)');
}

$sqln = 'SELECT * FROM pr_procesosop WHERE pp_orden > "' . $lmin . '" AND pp_orden <= "' . $lmax . '" AND pp_ordenp = "' . $ordenproceso . '" ORDER BY pp_orden';
$resultn = setq($sqln);

if(isset($_SESSION['uid'])){
    $tuser = 'U';
    $operador = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nmb');
    $nuser = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_nuser');
} else{
    $tuser = 'O';
    $operador = busca($_SESSION['eid'], 'pr_empleados', 'pe_id', 'pe_nmb');
    $nuser = $_SESSION['eid'];
}
echo '
    <div class="container">
    <div class="col-12 alert alert-primary">' . $title . ' Paqueteria</div>
      <form class="row" method="post" id="formulario" action="temporizadorpps.php?accion=setproceso">

        <input type="hidden" name="user" value="' . $nuser . '">
        <input type="hidden" name="tuser" value="' . $tuser . '">
        <input type="hidden" name="procesoorigen" value="' . $proceso . '">
        <input type="hidden" name="ordenp" value="' . $ordenproceso . '">
        <input type="hidden" id="valores" name="valores" value="">

        <div class="mb-5 col-md-4">
        <label>Usuario:</label>
          <input type="text" class="form-control" value="' . $operador . '" readonly>
        </div>
        <div class="mb-5 col-md-4">
        <label>Cantidad:</label>
          <input type="number" min="1" step="1" id="cant" name="cant" class="form-control" placeholder="Ingresa la cantidad" required>
        </div>
        <div class="mb-5 col-md-4">
        <label>Proceso destino:</label>
          <select class="form-control" id="procesodestino" name="procesodestino" required>';
          while ($row = $resultn->fetch_array()) {
            $proceso = busca($row['pp_proceso'], 'pr_procesos', 'ppr_id', 'ppr_nmb');
            echo '
            <option value="'.$row['pp_id'].'">'.$proceso.'</option>
            ';
          }
          echo '
          </select>
        </div>
        <div class="mb-5 col-md-12 col-12" hidden>
        <label>Retroalimentación:</label>
          <textarea class="form-control" id="retro2" name="retro2" rows="3" placeholder="Ingresa una retroalimentación"></textarea>
        </div>
        ';
            $ordenprod = new ordenprod();
            $ordenprod->verarticulos();
        echo '<div class="mb-5 mt-5 col-md-12">
          <center><button type="button" onclick="guardarCambios();" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar</center>
        </div>
      </form>
    </div>';

?>

<script>
  // Función para contar checkboxes chequeados y no deshabilitados
function contarCheckboxes() {
    // Obtener todos los checkboxes con la clase "check-all"
    var checkboxes = document.querySelectorAll('.check-all');

    // Filtrar los checkboxes para obtener solo los chequeados y no deshabilitados
    var checkboxesChequeados = Array.from(checkboxes).filter(function(checkbox) {
        return checkbox.checked && !checkbox.disabled;
    });

    // Obtener la cantidad de checkboxes chequeados y no deshabilitados
    var cantidadCheckboxesChequeados = checkboxesChequeados.length;

    // Mostrar la cantidad en la consola (puedes hacer lo que desees con esta información)
    return cantidadCheckboxesChequeados;
}

function validarCampos2() {
  var cantidad = document.getElementById("cant").value;
  var texto = '';
  var checkboxes = contarCheckboxes();

  /* alert("Checkboxes: "+checkboxes+" y cantidad: "+cantidad); */

  if(cantidad != checkboxes){
  Swal.fire({
      text: "La cantidad ingresada no coincide con los artículos seleccionados. Vefifique 2.",
      icon: "warning"
  });
  return false; // Los campos están vacíos, la validación falla
  }
 /*  if (retroalimentacion.trim() === "" || cantidad.trim() === "") {
    if(retroalimentacion.trim() === "" && cantidad.trim() === ""){
        texto = 'El campo "Cantidad" y "Retroalimentación" no pueden estar vacíos';
    } else{
        if(cantidad.trim() === ""){
            texto = 'El campo "Cantidad" no puede estar vacío';
        } else{
            texto = 'El campo "Retroalimentación" no puede estar vacío';
        }
    }
    Swal.fire({
        text: texto,
        icon: "warning"
    });
    return false; // Los campos están vacíos, la validación falla
  } */

  if(cantidad.trim() === ""){
      texto = 'El campo "Cantidad" no puede estar vacío';
      Swal.fire({
        text: texto,
        icon: "warning"
      });
    return false; // Los campos están vacíos, la validación falla
  }

  // Si llegamos aquí, los campos no están vacíos
  return true; // La validación es exitosa
}

function obtenerValoresComoCadena() {
  // Selecciona todos los elementos checkbox con la clase "check-all" que estén marcados
  var checkboxes = document.querySelectorAll(".check-all:checked");
  
  // Crea una cadena para almacenar los valores separados por comas
  var valoresCadena = "";
  
  // Recorre los elementos checkbox y agrega sus valores a la cadena
  checkboxes.forEach(function(checkbox, index) {
    // Agrega una coma si no es el primer elemento
    if (index > 0) {
      valoresCadena += ",";
    }
    valoresCadena += checkbox.value;
  });
  
  // Devuelve la cadena de valores separados por comas
  return valoresCadena;
}

function guardarCambios() {
  var formulario = document.getElementById("formulario");
    if(validarCampos2()){
      let seleccionados = obtenerValoresComoCadena();
      document.getElementById("valores").value = seleccionados;
      formulario.submit();
    }
}
</script>