<?php
session_start();
include_once('../funciones.php');

$idmarca = $_GET['id'];
if($idmarca){
  $sql = 'SELECT * FROM marcas WHERE m_id = "'.$idmarca.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
}
if(!$idmarca) $titulo = 'Agregar'; else $titulo = 'Actualizar';


if($row['m_id']) $accion = 'update'; else $accion = 'insert';


echo '
      <div class="container">
        <div class="alert alert-primary">'.$titulo.' marca</div>
        <form method="post" action="?modulo=marcas&accion='.$accion.'">
          <input type="hidden" value="'.$row['m_id'].'" name="id" />
          <div class="col-md-10"> 
            <label>Nomre de la marca:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la marca" name="nmb" value="'.$row['m_nmb'].'" required="required" >
          </div>
          <div class="col-md-2">
            <label>Guardar: </label><br>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
          </div>
        </form>
      </div>';


?>