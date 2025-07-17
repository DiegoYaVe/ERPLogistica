<?php
  session_start();
  /* ini_set('display_errors',1); */
  include_once('../funciones.php');
  include_once('../modulos/correosop.php');

  $idcat = $_GET['id'];
  $accion = 'insertcategoria';
  
  if($idcat){
    $categoria = new modelcorreosop();
    $categoria->selectcategoria($idcat);

    $accion = 'updatecategoria';
    $titulo = "Actualizar";

    if($categoria->estatus == "D") $selD = 'selected';
    else $selA = 'selected';
  }else{
    $selA = 'selected';
    $titulo = "Agregar";
  }
  

  echo '<div class="container">  
    <div class="col-12 alert alert-primary">'.$titulo.' Categoría de Correos</div>
    <input type="hidden" name="id" value="'.$categoria->id.'">
    <form method="post" action="?modulo=correosop&accion='.$accion.'" onsubmit="return checkSubmitedit();">
    <div class="row">
      <input type="hidden" name="id" value="'.$categoria->id.'">
      <div class="mb-5 col-md-6">
        <label>Nombre de la categoría:</label>
        <input type="text" name="nmb"  class="form-control" value="'.$categoria->nmb.'" maxlength="100" required/>
      </div>
      <div class="mb-5 col-md-3">  
        <label>Estatus:</label>
        <select name="estatus" class="form-control" required>
          <option value="" readonly disabled>Seleccioan un estatus</option>
          <option value="A" '.$selA.'>Activo</option>
          <option value="D" '.$selD.'>Inactivo</option>
        </select>
      </div>
      </div>
      <div class="mb-5 col-md-12 row">
        <label style="color:transparent">Guardar:</label><br>
        <center>
          <button type="submit" name="save" value=" " id="guardar" class="btn btn-success text-white">
            <i class="fas fa-save"></i> Guardar
          </button>
        </center>
      </div>
    </form>
  </div>';
?>