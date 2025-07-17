<?php

session_start();
//$_GET['solicitud'] = 1;
include_once('../funciones.php');
include_once('../modulos/registrosw.php');
$regw = new modelregistrosw();
$regw->select($_GET['solicitud']);

echo '
<script language="javascript">
function checksubmit(){
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
</script>
';

echo'
<input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />

<div class="container mt-1 ">
  <form action="?modulo=registrosw&accion=autorizar&id='.$regw->id.'" method="post" onsubmit="checksubmit();">
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">Dictaminar solicitud '.$regw->id.'</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
  echo '
    <div class="row">
      <div class="col-12 col-md-4">
        <div class="mb-5">
          <label for"correo">Autorizar/Denegar</label>
          <select name="estatus" id="estatus" required class="form-control">
            <option value="" disabled selected>Elegir una opción</option>
            <option value="A" >Autorizada</option>
            <option value="C">Denegada</option>
          </select>
        </div>
      </div>
      <div class="col-12 col-md-8 form-section text-medium">
        '.$regw->nombre.' '.$regw->telefono.' '.$regw->correo.' '.$regw->comentarios.'
      </div>';

  echo '<div class="col-12 col-md-12">
        <div class="mb-5">
          <label for"correo">Observaciones</label>
          <textarea class="form-control" name="comevaluacion" rows="2" placeholder="Si tienes alguna anotación sobre la compra, anotala " >'.$regw->comevaluacion.'</textarea>
        </div>
      </div>

      <div class="col-12 col-md-12">
        <center><button role="submit" class="btn btn-success" id="guardar"><i class="fa fa-check"></i> Finalizar</button></center>
      </div>
    </div>
  </form>
</div>';
?>