<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
ini_set('display_errors',1);
include_once('../funciones.php');

include_once('../modulos/solicitudes.php');

if($_GET['id']){
  $titulo = 'Editar';
  $solicitud =  new modelsolicitud();
  $solicitud->select($_GET['id']);
  $hidden = '';
  $hiddenbtn = 'hidden';
  $readonly = 'readonly';
  $accion = '';
}else{
  $hidden = 'hidden';
  $hiddenbtn = '';
  $titulo = 'Agregar';
  $solicitud = '';
  $obj = '';
  $readonly = ' ';
  $accion = '?modulo=solicitudes&accion=insert';
}
echo '

<div class="container">
  <form action="'.$accion.'" class="" method="POST" onsubmit="document.getElementById(\'guardar\').setAttribute(\'disabled\',\'disabled\');">
  <div class="row">
    <div class="col-md-12 alert alert-primary">
      '.$titulo.' Solicitud '.$obj->folio.'
    </div>
    <div class="col-md-4 mb-1" '.$hidden.'>
      <label for="" class="">Nombre de proyecto</label>
      <input type="text" class="form-control" name="folio" value="'.$solicitud->folio.'" readonly>
    </div>
    <div class="col-md-4 mb-1" '.$hidden.'>
      <label for="" class="">Nombre de proyecto</label>
      <input type="text" class="form-control" name="fechasoli" value="'.$solicitud->fechasoli.'" readonly>
    </div>
    <div class="col-md-6 mb-1">
      <label for="" class="">Nombre de proyecto</label>
      <input type="text" class="form-control" name="proyecto" value="'.$solicitud->nmb.'" '.$readonly.'> 
    </div>
    <div class="col-md-6 mb-1">
      <label for="" class="">Tipo de solicitud</label>
      <select name="tipo" id="" class="form-control" '.$readonly.'>';
      $sql = 'SELECT * FROM pr_tiposoli';
      $result = setq($sql); 
      while($row = $result -> fetch_array()){
        if($solicitud->tiposoli == $row['pts_siglas']) $sel = 'selected';
        else $sel = '';
        echo '<option value="'.$row['pts_siglas'].'" '.$sel.'>'.$row['pts_nmb'].' - '.$row['pts_descripcion'].'</option>';
      }
      echo '
      </select>
    </div>';

    echo '
    <div class="col-md-12 mb-4">
      <label for="" class="">Descripción</label>
      <textarea name="descripcion" cols="2" class="form-control" '.$readonly.'>'.$solicitud->descripcion.'</textarea>
    </div>
    <div class="col-md-12 text-center" '.$hiddenbtn.'>
      <button type="submit" id="guardar" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
    </div>
  </div>
  </form>
</div>



';

?>