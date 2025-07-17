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
  $sql = 'SELECT gc_id,gc_nmb,gc_color FROM gastos_categoria WHERE gc_empresa = "'.$_SESSION['emp'].'"
          AND gc_id = "'.$_GET['id'].'"';
  $result = setq($sql);
  list($id,$nmb,$color) = $result->fetch_array();


  if($mgastos->id){
    $accion = 'updatetgasto&id='.$_GET['id'];
    $title = "Registrar una ";
  }
  else{
    $accion = 'inserttgasto';
    $title = "Editar información de la ";
  }

echo'
<div class="container mt-1 ">
  <form action="?modulo=gastos&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' Categoria de Gasto</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

  echo '
    <div class="row">
      <div class="col-8 col-md-8">
        <div class="mb-5">
          <label for"nmbac">Nombre de la categoría de gasto</label>
          <input type="text" name="nmb" maxlength="50" value="'.$nmb.'" id="nmb" class="form-control" placeholder="Nombre de la categoria" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-4 col-md-2" >
        <div class="mb-5">
          <label for"fini">Color para reportes</label>
          <input type="color" name="color" value="'.$color.'" id="nmb" class="form-control" placeholder="Color de la categoria" required="required" onfocus="this.select();" />
        </div>
      </div>

      <div class="col-12 col-md-2">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="icon-bars"></i> Crear categoria</button></center>
      </div>
    </div>
  </form>
</div>';
?>