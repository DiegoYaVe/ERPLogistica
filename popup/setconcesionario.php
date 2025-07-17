<?php
session_start();
include_once('../funciones.php');

$idcon = $_GET['id'];
$accion = "insert";
if($idcon){
  $sql = 'SELECT * FROM concesionarios WHERE c_empresa = "'.$_SESSION['emp'].'" AND c_id = "'.$idcon.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = "update";
}

if(!$idcon) $titulo = "Agregar"; else $titulo = "Actualizar";

echo '
<form method="POST" action="?modulo=concesionarios&accion='.$accion.'">
<div class="container">
  <div class="row">
    <div class="col-md-12 alert alert-primary">'.$titulo.' concesionario </div>
    <input type="text" name="id" value="'.$row['c_id'].'" class="form-control" hidden>
    <div class="mb-5 col-md-3">
      <label for="">Nombre Completo: </label>
      <input type="text" name="nombre" value="'.$row['c_nmb'].'" placeholder="Nombre completo" class="form-control" required />
    </div>
    <div class="col-md-3 mb-5 ">
      <label for="">Dirección: </label>
      <input type="text" name="direccion" value="'.$row['c_direccion'].'" placeholder="Dirección del concesionario" class="form-control" />
    </div>
    <div class="col-md-2 mb-5 ">
      <label for="">Telefono:</label>
      <input type="tel" maxlength="10" name="telefono" value="'.$row['c_telefono'].'" placeholder="Teléfono" class="form-control" required>
    </div>
    <div class="col-md-3 mb-5 ">
      <label for="">Correo:</label>
      <input type="email" style="text-transform:lowercase;" name="correo" value="'.$row['c_email'].'" placeholder="Correo" class="form-control" required>
    </div>
    <div class="col-md-1">
      <label>Activo: </label> <br>';
      if($row['c_estatus'] == "A") $checked = "checked"; else $checked = "";
      if(!$idcon) $checked = 'checked';
      echo '<input type="checkbox" class="flipswitchsn" name="estatus" id="estatus" '.$checked.'>
    </div>
    <div class="col-md-12 text-xs-center">
      <button class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button>
    </div>
  </div>
</div>
</form>';

?>