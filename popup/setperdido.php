<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$id = $_GET['id'];

$telefono = busca($id, 'crm_leads', 'cl_id', 'CONCAT("+",cl_code," ", cl_telefono)');

$sql = 'SELECT * FROM catalogo_perdidos WHERE cp_estatus = "A"';
$result = setq($sql);

echo '
    <div class="container">
    <div class="col-12 alert alert-primary">Marcar lead como perdido</div>
      <form class="row" method="post" action="index?modulo=leadscapturados&accion=leadperdido&id='.$id.'">
        <input type="hidden" id="id" name="id" value="'.$id.'">
        <div class="mb-5 col-md-4">
        <label>Teléfono:</label>
          <input type="text" class="form-control focus mayus" value="'.$telefono.'" readonly>
        </div>
        <div class="mb-5 col-md-4">
        <label>Motivo:</label>
          <select class="form-control" name="motivo" id="motivo" required>
          <option value="">SELECCIONE UNA OPCIÓN</option>';
          while($row = $result->fetch_array()){
            echo '<option value="'.$row['cp_id'].'">'.$row['cp_nmb'].'</option>';
          }
            echo '
            <option value="0">OTRO</option>
          </select>
        </div>
        <div class="mb-5 col-md-4">
        <label>Especifique:</label>
          <textarea class="form-control" rows="5" name="otro" id="otro"></textarea>
        </div>
        <div class="mb-5 col-md-12">
          <center><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar</center>
        </div>
      </form>
    </div>';
?>