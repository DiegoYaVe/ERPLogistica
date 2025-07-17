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
  <form action="?modulo=registros&accion=mandarvideo&id='.$_GET['id'].'" method="POST">
    <div class="col-md-12 alert alert-primary text-white">
      Enviar video por correo
    </div>
    <div class="col-md-12"> 
      <label>Correo de destino</label>
      <input name="correo" id="correo" value="'.$row['rj_correo'].'" class="form-control" required>
    </div>
    <div class="col-md-12 text-xs-center mt-1">
      <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Enviar</button>
    </div>
  </form>
</div>
</div>
';

/* */
?>