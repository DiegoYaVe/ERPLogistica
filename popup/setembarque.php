<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$sql = 'SELECT p_id, p_nmb FROM paqueterias WHERE p_estatus = "A"';
$result = setq($sql);
$fecha = fecha_formato(date("Y-m-d"),false,false);
$fechaenvio = date("Y-m-d");

echo '
    <div class="container">
    <div class="col-12 alert alert-primary">Nuevo embarque de artículos</div>
      <form class="row" method="post" action="index?modulo=embarcamiento&accion=insert">
      <div class="mb-5 col-md-4">
        <label>Usuario:</label>
          <input type="text" name="ugenera" id="ugenera" class="form-control" data-toggle="tooltip" value="'.$_SESSION['uid'].'" data-placement="top" data-original-title="Usuario que genera el embarque" readonly>
        </div>
        <div class="mb-5 col-md-4">
        <label>Fecha creación:</label>
          <input class="form-control" type="text" name="fecha" id="fecha" value="'.$fecha.'" readonly>
        </div> 
        <div class="mb-5 col-md-4">
        <label>Fecha de envío:</label>
          <input class="form-control" type="date" name="fechaenvio" id="fechaenvio" value="'.$fechaenvio.'" required>
        </div> 
      <div class="mb-5 col-md-6">
        <label>Nombre del embarque:</label>
          <input type="text" id="nmb" class="form-control" data-toggle="tooltip" data-placement="top" data-original-title="Nombre con el que se identificará el embarque" name="nmb" required>
        </div>
        <div class="mb-5 col-md-6">
        <label>Paqueteria:</label>
          <select class="form-control" name="paqueteria" id="paqueteria" required>';
            while($row = $result->fetch_array()){
              echo '<option value="'.$row['p_id'].'">'.$row['p_nmb'].'</option>';
            }
            echo '<option value="-1">RECOGE CLIENTE</option>';
          echo '</select>
        </div>
          
        <div class="mb-5 col-md-12">
          <center><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar</center>
        </div>
      </form>
    </div>';

?>