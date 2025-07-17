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
  if($mactivo->id){
    $accion = 'update&id='.$_GET['id'];
    $title = "Registrar un ";
  }
  else{
    $accion = 'insert';
    $title = "Editar información de ";
  }
  $usoact = array("MED"=>"Medicinal","IND"=>"Industrial","ESP"=>"Especial");
  $valvula = array("CGA 580"=>"CGA 580","CGA 540"=>"CGA 540","CGA 510"=>"CGA 510","CGA 320"=>"CGA 320","ESTANDAR"=>"ESTANDAR");
  $tipocil = array("K"=>"K","T"=>"T","PGS"=>"PGS o Dewar","E"=>"E","M"=>"M","FC"=>"FC","A3"=>"A3");

echo'
<div class="container mt-1 ">
  <form action="?modulo=activos&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' Activo</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

  echo '
    <div class="row">
      <div class="col-12 col-md-2 ">
        <div class="mb-5">
          <label for"nmbac">Nombre del activo</label>
          <input type="text" name="nmbac" maxlength="50" value="'.$mactivo->nmb.'" id="nmbac" class="form-control" placeholder="Nombre de tu actividad" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-12 col-md-2" >
        <div class="mb-5">
          <label for"fini">Uso del activo</label>
          '.menu_select_array($usoact,$mactivo->uso,'uso',true).'
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="mb-5">
          <label for"correo">Tipo Válvula</label>
          '.menu_select_array($valvula,$mactivo->accesorio,'accesorio',true).'
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="mb-5">
          <label for"correo">Tipo de cílindro</label>
          '.menu_select_array($tipocil,$mactivo->tipoactivo,'tipoactivo',true).'
        </div>
      </div>
      <div class="col-6 col-md-2" >
        <div class="mb-5">
          <label for"fini">Capacidad</label>
          <input type="number" name="capacidad" min="0" max="999" step="0.01" value="'.$mactivo->capacidad.'" id="descuento" class="form-control" placeholder="Capacidad del cilindro" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-6 col-md-2" >
        <div class="mb-5">
          <label for"fini">Presión</label>
          <input type="number" name="presion" min="0" max="99999" step="0.01" value="'.$mactivo->presion.'" id="presion" class="form-control" placeholder="Presión del cilidnro" required="required" onfocus="this.select();" />
        </div>
      </div>
     <div class="col-12 col-md-12" >
        <div class="mb-5">
          <label for"fini">Observaciones</label>
          <textarea class="form-control" name="observaciones" rows="2" >'.$mactivo->observaciones.'</textarea>
        </div>
      </div>

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Crear</button></center>
      </div>
    </div>
  </form>
</div>';
?>