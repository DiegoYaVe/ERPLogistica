<script language="javascript">
function checksubmit(){
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function cargar(){
  opener.location.reload();
  window.close();
}
function closewindow(){
   window.opener.location.reload();
   window.close();
}
</script>
<?php
session_start();
  include_once('../funciones.php');

  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  include_once('../modulos/activos.php');
  $mactivo = new modelactivos();
  $mactivo->select($_GET['id']);

    $accion = 'insertalmacenes';
    $title = 'Añadir '.$mactivo->nmb.' - '.$mactivo->uso.' - '.$mactivo->accesorio.' - '.$mactivo->tipoactivo.' - '.$mactivo->capacidad.' - '.$mactivo->presion.' al almacen';

echo'
<div class="container mt-1 ">
  <form action="?modulo=activos&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <input type="hidden" name="activo" value="'.$_GET['id'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.'</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

  echo '
    <div class="row">
      <div class="col-12 col-md-8 ">
        <div class="mb-5">
          <label for"nmbac">Almacen</label>
          '.menu_select_db('almacenes','a_id','a_nmb','','almacen','a_empresa = "'.$_SESSION['emp'].'" AND a_estatus = "A"',false,false,false,true).'
        </div>
      </div>
      <div class="col-12 col-md-4 ">
        <div class="mb-5">
          <label for"nmbac">Cantidad</label>
          <input type="number" name="cantidad" min="0" max="999" step="1" value="'.$mactivo->cantidad.'" id="descuento" class="form-control" placeholder="Cantidad de piezas" required="required" onfocus="this.select();" />
        </div>
      </div>


      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Crear</button></center>
      </div>
    </div>
  </form>
</div>';
?>