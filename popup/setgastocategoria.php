<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');


$idcategoria = $_GET['id'];
if($idcategoria){
  $sql = 'SELECT * FROM gastos_categoria WHERE gc_id = "'.$idcategoria.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  
}

$seltg = "";
$seltv = "";
$seltf = "";

$selfco = "";
$selfca = "";
$selfcv = "";
$selfcn = "";
$selfci = "";

if($idcategoria){
  $accion = 'updatecat&id='.$row['gc_id'].'"';
  if($row['gc_tipo'] == "G") $seltg = "";
  elseif($row['gc_tipo'] == "F") $seltf = "selected";
  else $seltv = "selected";

  if($row['gc_financiero'] == "O") $selfco = "selected";
  elseif($row['gc_financiero'] == "A") $selfca = "selected";
  elseif($row['gc_financiero'] == "V") $selfcv = "selected";
  elseif($row['gc_financiero'] == "N") $selfcn = "selected";
  elseif($row['gc_financiero'] == "I") $selfci = "selected";

}
else {
  if($_GET['o']) $accion = "insertcat&o=".$_GET['o'];
  else $accion = "insertcat";
}

if(!$idcategoria) $titulo = "Agregar"; else $titulo = "Actualizar";


echo '<center><form method="post" action="?modulo=gastos&accion='.$accion.'" onsubmit="return checkSubmitedit();">
  <div class="row">
  <div class="col-md-12 col-12 alert alert-primary">'.$titulo.' nueva categoría</div>
  <div class="col-md-3 col-sm-8 ">
    <div class="mb-5">
      <label for="nmb">Nombre de la categoria</label>
      <input type="text" name="nmb" value="'.$row['gc_nmb'].'" required class="form-control" autofocus />
    </div>
  </div>

  <div class="col-md-2 col-sm-4">
    <div class="mb-5">
      <label for="tipo">Tipo de categoria</label>
      <select name="tipo" id="tipo" class="form-control">
        <option value="G" '.$seltg.'>Gasto</option>
        <option value="V" '.$seltv.'>Vale</option>
        <option value="F" '.$seltf.'>Gasto Fijo</option>
      </select>
    </div>
  </div>

  <div class="col-md-2 col-sm-6">
    <div class="mb-5">
      <label for="tipo">Tipo de categoria</label>
      <select class="form-control" name="financiero" id="financiero" >
        <option value="O" '.$selfco.' >Operacion</option>
        <option value="A" '.$selfca.' >Administración</option>
        <option value="V" '.$selfcv.' >Ventas</option>
        <option value="N" '.$selfcn.' >No Operacional</option>
        <option value="I" '.$selfci.' >Impuestos</option>
      </select>
    </div>
  </div>

  <div class="col-md-3 col-sm-6">
    <div class="mb-5">
      <label for="tipo">Color de categoria <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Color que aparecerá en los reportes"></i></label>
      <input type="color" name="color" class="form-control" id="color" value="'.$row['gc_color'].'" required>
    </div>
  </div>';
  echo '
    <div class="col-md-2 col-sm-12">
      <label for="tipo"></label>.<br>
      <button type="submit" name="save" id="guardar" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
    </div>
  </form>';



?>