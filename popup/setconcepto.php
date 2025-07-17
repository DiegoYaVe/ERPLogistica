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
  include_once('../modulos/departamentosw.php');
  $depto = new modeldepartamentosw();
  $depto->selectc($_GET['id']);
  if($depto->id){
    $accion = 'updateconcepto&id='.$_GET['id'];
    $title = "Registrar un ";
  }
  else{
    $accion = 'insertconcepto';
    $title = "Editar información de ";
  }

echo'
<div class="container mt-1 ">
  <form action="?modulo=departamentosw&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' Concepto</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
  echo '
    <div class="row">
      <div class="col-12 col-md-12 ">
        <div class="mb-5">
          <label for"nmbac">Nombre del Concepto</label>
          <input type="text" name="nmbac" maxlength="50" value="'.$depto->nmb.'" id="nmbac" class="form-control" placeholder="Nombre de tu Departamento" required="required" onfocus="this.select();" />
        </div>
      </div>
     <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Crear</button></center>
      </div>
    </div>
  </form>
</div>';
?>