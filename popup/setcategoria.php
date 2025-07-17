<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$idcat = $_GET['id'];
if($idcat){
  $sql = 'SELECT * FROM categorias WHERE cat_id = "'.$idcat.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = 'update';
  $titulo = "Actualizar";
  if($row['cat_inflable'] == "1") $sel = "checked";
  else $sel = "";
} else {
  $sel = "";
  $accion = 'insert';
  $titulo = "Agregar";
}


echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' Categoría</div>
      <form method="post" action="index?modulo=categorias&accion='.$accion.'">
        <input type="hidden" value="'.$idcat.'" name="id" />
        <div class="row">
          <div class="mb-5 col-md-5">
          <label>Nombre de la categoría:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la categoria" name="nmb" value="'.$row['cat_nmb'].'" required="required" >
          </div>
          <div class="mb-5 col-md-4">  
          <label>Siglas de la categoría:</label>
            <input type="text" id="siglas" class="form-control mayus" data-toggle="tooltip" data-placement="top" data-original-title="Determina un nombre corto de tu Linea de negocio, 3 Carácteres" placeholder="Acronimo para numero de parte interno, maximo 3 caracteres" name="siglas" minlength="3" maxlength="3" value="'.$row['cat_siglas'].'" required="required"  >
          </div>
          <div class="mb-5 col-md-2">  
          <label>Categoria para inflable:</label>
            <input type="checkbox" id="inflable" class="form-control flipswitch2" data-toggle="tooltip" data-placement="top" data-original-title="Al activar esta opción se marca que los artículos dentro de esta categoría son inflables" name="inflable" '.$sel.'>
          </div>
          <div class="mb-5 col-md-1">
          <label>Guardar:</label>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
          </div>
        </div>
      </form>
    </div>';

?>