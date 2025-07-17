<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$id = $_GET['id'];
$fechaActual = date("Y-m-d");
$telefono = busca($id, 'crm_leads', 'cl_id', 'CONCAT("+",cl_code," ", cl_telefono)');
$sqlhist = 'SELECT hp_motivo, hp_descripcion, hp_uasigna FROM historial_perdidos WHERE hp_perdido = "'.$id.'" ORDER BY hp_id DESC LIMIT 1';
$resulthis = setq($sqlhist);
list($hpmotivo, $descripcion, $uasigna) = $resulthis->fetch_array();

if($hpmotivo == 0){
  $motivo = "OTRO";
} else{
  $motivo = busca($hpmotivo, 'catalogo_perdidos', 'cp_id', 'cp_nmb');
}

echo '
    <div class="container">
    <div class="col-12 alert alert-primary">Detalles del lead sin negociación</div>
      <form class="row" method="post" action="index?modulo=leadsperdidos&accion=reasignarlead">
        <input type="hidden" id="id" name="id" value="'.$id.'">
        <div class="mb-5 col-md-4">
        <label>Teléfono:</label>
          <input type="text" class="form-control focus mayus" value="'.$telefono.'" readonly>
        </div>
        <div class="mb-5 col-md-4">
        <label>Vendedor:</label>
          <input type="text" class="form-control focus mayus" value="'.$uasigna.'" readonly>
        </div>
        <div class="mb-5 col-md-4">
        <label>Fecha de reasignación:</label>
          <input type="date" class="form-control" name="fasigna" id="fasigna" value="'.date("Y-m-d").'" min="'.$fechaActual.'" required>
        <div id="mensaje"></div>
        </div>
        <div class="mb-5 col-md-4">
        <label>Motivo:</label>
          <input type="text" class="form-control focus mayus" value="'.$motivo.'" readonly>
        </div>
        <div class="mb-5 col-md-4">
        <label>Descripción:</label>
          <textarea class="form-control" rows="5" readonly>'.$descripcion.'</textarea>
        </div>
        <div class="mb-5 col-md-12">
          <center><button type="submit" class="btn btn-primary" ><i class="fas fa-exchange-alt"></i> Reasignar</center>
        </div>
      </form>
    </div>';
?>
<script>
// Obtén el elemento input de fecha
const fechaInput = document.getElementById("fasigna");

// Agrega un controlador de eventos para el cambio de fecha
fechaInput.addEventListener("change", function() {
    const fechaSeleccionada = new Date(this.value);
    const diaDeLaSemana = fechaSeleccionada.getDay(); // 0 para domingo, 6 para sábado

    // Verifica si es sábado (5) o domingo (6)
    if (diaDeLaSemana === 5) {
        const mensajeDiv = document.getElementById("mensaje");
        mensajeDiv.textContent = "El día seleccionado es sábado";
        mensajeDiv.style.color = "red";
    } else if (diaDeLaSemana === 6) {
        const mensajeDiv = document.getElementById("mensaje");
        mensajeDiv.textContent = "El día seleccionado es domingo";
        mensajeDiv.style.color = "red";
    } else {
        // Si no es sábado ni domingo, borra el mensaje y el estilo
        const mensajeDiv = document.getElementById("mensaje");
        mensajeDiv.textContent = "";
        mensajeDiv.style.color = "";
    }
});
</script>
