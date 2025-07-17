<?php

session_start();
  include_once('../funciones.php');

  include_once('../modulos/licencias.php');
  $licen = new modellicencias();
  $licen->select($_GET['idlic']);

  include_once('../modulos/clientes.php');
  $mcliente = new modelclientes();

  if($licen->id){
    $cliente = $licen->cliente;
    $mcliente->select($licen->cliente);
    $nmbact = $licen->nmb;
    $ffin = $licen->fini;
    $diasgracia = $licen->diasgracia;
    $accion = 'update&id='.$licen->id;
    $title = "Editar";
    $responsable = $licen->ualta;
  }
  else{
    $mcliente->select($_GET['cliente']);

    if(!isset($nmbact)) $nmbact = "JD CEO ".$mcliente->nmb;
    $ffin = date('Y-m-d',strtotime('+ 1 day'));
    $responsable = $_SESSION['uid'];
    $accion = 'insert';
    $title = "Registrar";
    $diasgracia = 5;
    $cliente = $_GET['cliente'];
  }

  echo '
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
  
  
  ';

echo'
<div class="container mt-1 ">
  <form action="?modulo=licencias&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <input type="hidden" name="cliente" value="'.$cliente.'" />

    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' Licencia</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

  echo '
    <div class="row">
      <div class="col-12 col-md-5 ">
        <div class="mb-5">
          <label for"nmbac">Alias de tu Licencia </label>
          <input type="text" name="nmbac" maxlength="50" value="'.$nmbact.'" id="nmbac" class="form-control" placeholder="Nombre de tu actividad" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-12 col-md-2" >
        <div class="mb-5">
          <label for"fini">Fecha inicio del contrato</label>
          <input type="date" name="ffin" id="ffin" min="'.date('Y-m-d').'" value="'.$ffin.'" class="form-control" placeholder="Cuando programas tu actividad" required="required" />
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="mb-5">
          <label for"correo">Responsable</label>
          '.menu_select_db('usuarios','u_id','u_nmb',$responsable,'responsable','u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"',false,false,false,true,"Responsable").'
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="mb-5">
          <label for"correo">Días de gracia</label>
          <input type="number" name="diasgracia" min="0" max="100" step="1" value="'.$diasgracia.'" id="diasgracia" class="form-control" placeholder="Días de gracia" required="required" onfocus="this.select();" data-toggle="tooltip" data-placement="right" title="Expresar descuento en porcentaje" />
        </div>
      </div>

      <div class="col-12 col-md-12">
        <div class="mb-5">
          <label for"correo">Observaciones</label>';
      echo '<textarea name="observaciones" class="form-control" rows="2" placeholder="Observaciones de la licencia"></textarea>
        </div>
      </div>

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Crear</button></center>
      </div>
    </div>
  </form>
</div>';
?>