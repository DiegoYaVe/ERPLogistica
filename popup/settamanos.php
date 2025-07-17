<?php
  session_start();
  ini_set('display_errors', 1);
  date_default_timezone_set("America/Mexico_City");
  $title = 'JD CEO';
  $numnot = 0;
  if($numnot > 0) $numtag = "danger"; else $numtag = "success";
  if(!isset($_SESSION['emp'])) $_SESSION['emp'] = 0;
?>
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
function setcolor(){
  document.getElementById("seleccionados").style.display = "none";
  document.getElementById("showcolor").style.display = "none";

  document.getElementById("nuevo").style.display = "";
  document.getElementById("nmbc").focus();
}

function closewindow(){
   window.opener.location.reload();
   window.close();
}
</script>
<?php
include_once('../funciones.php');
if(!isset($_POST['busca'])){
  $_POST['busca']="";
  $autof1 = 'autofocus';
  $autof2 = '';
}
else{
  $autof1 = "";
  $autof2 = 'autofocus onfocus="this.select();"';
}

//<form id="formnew" method="post" action="buscaprod?modulo='.$_GET['modulo'].'&accion='.$_GET['accion'].'&id='.$_GET['id'].'">

echo '<script>
        function cierrarpop(){
          window.close();
          window.opener.location.reload();
        }
      </script>';

echo'
<input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />
<input type="hidden" id="articulo" value="'.$_GET['articulo'].'" />

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12"><center><h2>Elegir o agregar tamaños al producto</h2></center></div>
    <div class="col-md-8">
      <div class="row mb-5">
        <div class="col-10 col-md-8">
          <input type="text" name="nmb" class="form-control" id="nmb" autocomplete="off" placeholder="Busca el Tamaño por nombre" autofocus />
        </div>
        <div class="col-2 col-md-4">
          <button class="btn btn-primary" id="showcolor" onclick="setcolor();"><i class="icon-plus3"></i>Nuevo Tamaño</button>
        </div>
      </div>
      <div id="datos">
      </div>
    </div>
    <div class="col-md-4 border rounded" id="seleccionados">
      <center>
        <button class="btn bg-grey bg-darken-2 text-white mb-2" onclick="closewindow();">
          <i class="fa fa-times"></i> Cerrar Ventana
        </button>
      <center>
      <center><h4>Tamaños Seleccionados</h4></center>';
    $sqlc = 'SELECT * FROM articulos_tamanos WHERE at_id IN
             (SELECT av_tamano FROM articulos_variantes WHERE av_articulo = "'.$_GET['articulo'].'")';
    $resultc = setq($sqlc);
    echo '<ul class="list-group">';
    while($row = $resultc->fetch_array()){
      echo '
          <li class="list-group-item">
            '.$row['at_nmbtamano'].' - '.$row['at_alias'].'
            <button class="btn btn-secondary float-xs-right"><i class="fa fa-trash"></i></button>
          </li>';
    }

    echo '</div>
    <div class="col-md-4 border rounded" id="nuevo" style="display:none;">
      <form method="post" id="sendnewcolor" action="registratamano.php?articulo='.$_GET['articulo'].'&empresa='.$_SESSION['emp'].'" onsubmit="checksubmit();">
        <h4 class="mb-2"><center>Agregar un nuevo Tamaño</center></h4>
        <div class="mb-5">
          <label>Nombre del Tamaño</label>
          <input type="text" name="nmbc" class="form-control" id="nmbc" placeholder="Nombre completo" required />
        </div>
        <div class="mb-5">
          <label>Nombre corto</label>
          <input type="text" name="siglasc" class="form-control" id="siglasc" maxlength="3" minlength="1" placeholder="Asigna 3 siglas para el color" required />
        </div>
        <div class="mb-5">
          <center><button type="submit" id="guardar" class="btn btn-info"><i class="fa fa-save"></i> Guardar</button></center>
        </div>
      </form>
    </div>
  </div>
</div>';
?>
    <!-- BEGIN VENDOR JS-->
    <script src="../js/jquery.min.js" type="text/javascript"></script>
    <script src="../js/ui/tether.min.js" type="text/javascript"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/articulos_tamanos.js"></script>
  </body>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
</html>