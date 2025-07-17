<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$idplanta = $_GET['id'];
if($idplanta){
  $sql = 'SELECT * FROM pr_plantas WHERE pp_id = "'.$idplanta.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = 'update';
  $titulo = "Actualizar";
  if($row['pp_estatus'] == "A") $sel = "checked";
  else $sel = "";
} else {
  $sel = "checked";
  $accion = 'insert';
  $titulo = "Agregar";
}

echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' planta</div>
      <form method="post" action="index?modulo=plantas&accion='.$accion.'">
        <input type="hidden" value="'.$idplanta.'" name="id" />
        <div class="row">
          <div class="mb-5 col-md-4 col-12">
            <label>Nombre del planta:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre para identificar la planta" name="nmb" value="'.$row['pp_nmb'].'" required="required" onfocus="this.select();">
          </div>
          <div class="mb-5 col-md-5 col-12">
            <label>Descripción:</label>
            <input type="text" id="descripcion" class="form-control focus mayus" placeholder="Descripcion de la planta" name="descripcion" value="'.$row['pp_descripcion'].'" required="required" onfocus="this.select();">
          </div>
          <div class="mb-5 col-md-2 col-12">  
            <label>Estatus:</label>
            <input type="checkbox" id="estatus" class="form-control flipswitch" data-toggle="tooltip" name="estatus" '.$sel.'>
          </div>
          <div class="mb-5 col-md-1 col-12">
          <label>Guardar:</label>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
          </div>
        </div>
      </form>
    </div>';

?>