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
  include_once('../nomina/nomina.php');
  $nomin = new modelnomina();
  $nomin->selectnomina($_GET['id']);
  if($nomin->id){
    $accion = 'updatef&id='.$_GET['id'];
    $title = "Editar información de ";
    $fini = $nomin->fini;
    $ffin = $nomin->ffin;
  }
  else{
    $accion = 'insertnom';
    $title = "Nueva ";

    $sqlf = 'SELECT MAX(n_ffin) FROM nomina WHERE n_empresa = "'.$_SESSION['emp'].'"';
    $resultf = setq($sqlf) or die($sqlf);
    list($fini) = $resultf->fetch_row();
    $fini = date('Y-m-d',strtotime($fini.'+ 1 days'));

    if(!$periodicidad) $periodicidad = "04";
    if($periodicidad == "04" && $fini != date('Y-m-d',strtotime($fini.' first day of this month'))) $ffin = date('Y-m-d',strtotime($fini.' last day of this month'));
    $dias = busca($periodicidad,'nomina_periodicidad','np_clave','np_dias');
    $dias--;

    if(!$ffin) $ffin = date('Y-m-d',strtotime($fini.' + '.$dias.' days'));
  }

echo'
<div class="container mt-1 ">
  <form action="?modulo=nomina&accion='.$accion.'" method="post" >
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary"><label class="modaltext">'.$title.' Nomina</label></div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
  echo '
    <div class="row">
      <div class="col-12 col-md-2 ">
        <div class="mb-5">
          <label for"nmbac">Fecha inicial</label>
          <input type="date" name="fini" class="form-control" required autofocus value="'.$fini.'" />
        </div>
      </div>
      <div class="col-12 col-md-2 ">
        <div class="mb-5">
          <label for"nmbac">Fecha inicial</label>
          <input type="date" name="ffin" class="form-control" required autofocus value="'.$ffin.'" />
        </div>
      </div>
      <div class="col-12 col-md-2 ">
        <div class="mb-5">
          <label for"nmbac">Fecha Pago</label>
          <input type="date" name="fpago" class="form-control" required autofocus value="'.$ffin.'" />
        </div>
      </div>
      <div class="col-12 col-md-6 ">
        <div class="mb-5">
          <label for"nmbac">Tipo de Nomina</label> <br>
          <label class="display-inline-block custom-control custom-radio ml-1">
            <input type="radio" class="custom-control-input" name="tiponom" value="O" checked />
            <span class="custom-control-indicator"></span>
            <span class="custom-control-description ml-0"><b>Ordinaria</b></span>
          </label>
          <label class="display-inline-block custom-control custom-radio ml-1">
            <input type="radio" class="custom-control-input" name="tiponom" value="E" />
            <span class="custom-control-indicator"></span>
            <span class="custom-control-description ml-0"><b>Especial</b></span>
          </label>
        </div>
      </div>
     <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Crear</button></center>
      </div>
    </div>
  </form>
</div>';
?>