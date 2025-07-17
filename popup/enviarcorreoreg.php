<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');
$sql = 'SELECT * FROM registros_jdceo WHERE rj_id = "'.$_GET['id'].'"';
$result = setq($sql);
$row = $result->fetch_array();

echo '
<div class="container">
<div class="row">
  <form action="?modulo=registros&accion=mandarcorreo&id='.$_GET['id'].'" method="POST">
    <div class="col-md-12 alert alert-primary text-white mb-5 text-xs-center">
      Enviar correo a: '.$row['rj_nmb'].'
    </div>
    <div class="col-md-12 mt-1"> 
      <label>Correo de destino</label>
      <input name="correo" id="correo" value="'.$row['rj_correo'].'" class="form-control" required>
    </div>
    <div class="col-md-12 mt-1"> 
      <label>Asunto</label>
      <input name="asunto" id="asunto" value="" class="form-control" required>
    </div>
    <div class="col-md-12 mt-1"> 
      <label>Cuerpo</label>
      <textarea rows="8" name="cuerpo" class="form-control" placeholder="Cuerpo del correo" required></textarea>
    </div>
    <div class="col-md-12 text-xs-center mt-1">
      <button type="submit" class="btn btn-success"><i class="icon-envelope"></i> Enviar</button>
    </div>
  </form>
</div>
</div>
';

?>