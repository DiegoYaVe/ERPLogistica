<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');


$id = $_GET['id'];
if($id){
  $sql = 'SELECT * FROM condicionesc WHERE c_id = "'.$id.'"';
  $result = setq($sql);
  $row = $result->fetch_array();

}


if($id) $accion = 'update'; else $accion = 'insert';

if(!$id) $titulo = "Agregar"; else $titulo = "Actualizar";

echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' condición comercial</div>
      <form method="post" action="index?modulo=condiciones&accion='.$accion.'">
        <input type="hidden" value="'.$id.'" name="id" />
        <div class="row">
          <div class="mb-5 col-md-4">
          <label>Nombre de la condición:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la condición" name="nmb" value="'.$row['c_nmb'].'" required="required" >
          </div>
          <div class="mb-5 col-md-3">  
            <label>Estatus</label>';
            if($row['c_estatus'] == "A") $checked = "checked";
            else $checked = "";
            echo '
            <input class= "flipswitch form-control" type="checkbox" name="estatus" id="estatus" '.$checked.'>
          </div>
          <div class="mb-5 col-md-3">  
            <label>Obligatorio</label>';
            if($row['c_obligatorio'] == "1") $checked2 = "checked";
            else $checked2 = "";
            echo '
            <input class= "flipswitch2 form-control" type="checkbox" name="obliga" id="obliga" '.$checked2.'>
          </div>
          <div class="mb-5 col-md-1">
          <label>Guardar:</label>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
          </div>
        </div>
      </form>
    </div>';

?>