<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php'); 

$idmp = $_GET['id'];
$selp = '';
$sela = '';
$selv = '';
if($idmp){
  $sql = 'SELECT * FROM pr_materiaprima WHERE pmp_id = "'.$idmp.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = 'update';
  $titulo = "Actualizar";
  if($row['pmp_estatus'] == "A") $sel = "checked";
  else $sel = "";
  if($row['pmp_tipo'] == "P") $selp = "checked";
  else if($row['pmp_tipo'] == "A") $sela = "checked";
  else $selv = "checked";
} else {
  $sel = "checked";
  $accion = 'insert';
  $titulo = "Agregar";
  $selp = 'checked';
}


echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' materia prima</div>
      <form method="post" action="index?modulo=materiaprima&accion='.$accion.'">
        <input type="hidden" value="'.$idmp.'" name="id" />
        <div class="row">
          <div class="mb-5 col-12 col-md-4">
            <label>Nombre de la materia prima:</label>
            <input type="text" id="nmb" class="form-control focus" placeholder="Nombre de la materia prima" name="nmb" value="'.$row['pmp_nmb'].'" required="required" >
          </div>
          <div class="mb-5 col-12 col-md-3">  
            <label>Material:</label>
            <input type="text" id="material" class="form-control" data-toggle="tooltip" data-placement="top" name="material" value="'.$row['pmp_material'].'" required="required"  >
          </div>
          <div class="mb-5 col-12 col-md-2">  
            <label>Color:</label>
            <input type="color" id="color" value="'.$row['pmp_color'].'" class="form-control" data-toggle="tooltip" data-placement="top" name="color" style="height: 70%;">
          </div>
          <div class="mb-5 col-md-3 col-12">  
            <label>Tipo:</label>
            <br> <br>
            <input type="radio" class="form-check-input" id="pieza" name="tipo" value="P" '.$selp.' '.$read.'> Pieza
            <input type="radio" class="form-check-input" id="area" name="tipo" value="A" '.$sela.' '.$read.'> Área
            <input type="radio" class="form-check-input" id="volumen" name="tipo" value="V" '.$selv.' '.$read.'> Volumen
          </div>
          <div class="mb-5 col-md-2">  
          <label>Estatus:</label>
            <input type="checkbox" id="estatus" class="form-control flipswitch2" data-toggle="tooltip" name="estatus" '.$sel.'>
          </div>
          <div class="mb-5 col-md-1">
            <label>Guardar:</label>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
          </div>
        </div>
      </form>
    </div>';
?>