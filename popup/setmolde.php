<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$idmolde = $_GET['id'];
if($idmolde){
  $sql = 'SELECT * FROM pr_moldes WHERE pm_id = "'.$idmolde.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = 'update';
  $titulo = "Actualizar";
  if($row['pm_estatus'] == "A") $sel = "checked";
  else $sel = "";
} else {
  $sel = "checked";
  $accion = 'insert';
  $titulo = "Agregar";
}

echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' Molde</div>
      <form method="post" action="index?modulo=moldes&accion='.$accion.'">
        <input type="hidden" value="'.$idmolde.'" name="id" />
        <div class="row">
          <div class="mb-5 col-md-3 col-12">
            <label>Nombre del molde:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre para identificar el molde" name="nmb" value="'.$row['pm_nmb'].'" required="required" onfocus="this.select();">
          </div>
          <div class="mb-5 col-md-2 col-12">  
            <label>Medida X:</label>
            <input type="number" step="0.01" class="form-control" id="x" name="x" value="'.number_format($row['pm_x'],2).'" required="required" onfocus="this.select();">
          </div>
          <div class="mb-5 col-md-2 col-12">  
            <label>Medida Y:</label>
            <input type="number" step="0.01" id="y" name="y" class="form-control" value="'.number_format($row['pm_y'],2).'" required="required" onfocus="this.select();" >
          </div>
          <div class="mb-5 col-md-2 col-12">  
            <label>Medida Z:</label>
            <input type="number" step="0.01" id="z" name="z" class="form-control" value="'.number_format($row['pm_z'], 2).'" required="required" onfocus="this.select();" >
          </div>
          <div class="mb-5 col-md-2 col-12">  
            <label>Estatus:</label>
            <input type="checkbox" id="estatus" class="form-control flipswitch2" data-toggle="tooltip" name="estatus" '.$sel.'>
          </div>
          <div class="mb-5 col-md-1 col-12">
          <label>Guardar:</label>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
          </div>
        </div>
      </form>
    </div>';

?>