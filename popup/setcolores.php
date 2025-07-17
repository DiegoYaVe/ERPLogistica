<?php
  session_start();
  ini_set('display_errors', 1);
  date_default_timezone_set("America/Mexico_City");
  $title = 'JD CEO';
  $numnot = 0;
  if($numnot > 0) $numtag = "danger"; else $numtag = "success";
  if(!isset($_SESSION['emp'])) $_SESSION['emp'] = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" data-textdirection="ltr" class="loading">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="author" content="JD Suite">
    <title><?php echo $title; ?></title>
    <link rel="icon" type="image/png" href="images/ico/favicon-32.png">
    <link rel="apple-touch-icon" sizes="60x60" href="images/ico/apple-icon-60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="images/ico/apple-icon-76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="images/ico/apple-icon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="images/ico/apple-icon-152.png">
    <link rel="shortcut icon" type="image/x-icon" href="images/ico/favicon-32.png">
    <link rel="shortcut icon" type="image/png" href="images/ico/favicon-32.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="../fonts/icomoon.css">
    <link rel="stylesheet" type="text/css" href="../fonts/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" type="text/css" href="../css/pace.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN ROBUST CSS-->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="../css/app.css">
    <link rel="stylesheet" type="text/css" href="../css/colors.css">
    <link rel="stylesheet" type="text/css" href="../css/screen.css">
    <!-- END ROBUST CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="../css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="../css/core/menu/menu-types/vertical-overlay-menu.css">
    <link rel="stylesheet" type="text/css" href="../css/core/colors/palette-gradient.css">
    <!-- END Page Level CSS-->
    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" type="text/css" href="../css/style.css">
  </head>
  <body>
<script language="javascript">
function checksubmit(){
  document.getElementById("guardarcolor").value = "JD";
  document.getElementById("guardarcolor").disabled = true;
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

<div class="container-fluid mt-1 ">
  <div class="row">
    <div class="col-md-8">
      <div class="row mb-5">
        <div class="col-10 col-md-9">
          <input type="text" name="nmb" class="form-control" id="nmb" autocomplete="off" placeholder="Busca el color por nombre" autofocus />
        </div>
        <div class="col-2 col-md-3">
          <button class="btn btn-primary" id="showcolor" onclick="setcolor();"><i class="icon-plus3"></i>Nuevo color</button>
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
      <center><h4>Colores Seleccionados</h4></center>';
    $sqlc = 'SELECT * FROM articulos_colores WHERE ac_id IN
             (SELECT av_color FROM articulos_variantes WHERE av_articulo = "'.$_GET['articulo'].'")';
    $resultc = setq($sqlc);
    echo '<ul class="list-group">';
    while($row = $resultc->fetch_array()){
      echo '
          <li class="list-group-item">
            <span class="tag tag-pill tag-default" style="background:'.$row['ac_hexadecimal'].';color:'.$row['ac_hexadecimal'].';border: 1px solid #666666" data-toggle="tooltip" data-placement="left" title="'.$row['ac_hexadecimal'].'">
            '.$row['ac_hexadecimal'].'
            </span>
            '.$row['ac_nmbcolor'].'
            <button class="btn btn-secondary float-xs-right"><i class="fa fa-trash"></i></button>
          </li>';
    }

    echo '</div>
    <div class="col-md-4 border rounded" id="nuevo" style="display:none;">
      <form method="post" id="sendnewcolor" action="registracolor.php?articulo='.$_GET['articulo'].'&empresa='.$_SESSION['emp'].'" onsubmit="checksubmit();">
        <h4 class="mb-2"><center>Agregar un nuevo color</center></h4>
        <div class="mb-5">
          <label>Nombre del color</label>
          <input type="text" name="nmbc" class="form-control" id="nmbc" placeholder="Nombra tu color" required />
        </div>
        <div class="mb-5">
          <label>Siglas para el color</label>
          <input type="text" name="siglasc" class="form-control" id="siglasc" maxlength="3" minlength="1" placeholder="Asigna 3 siglas para el color" required />
        </div>
        <div class="mb-5">
          <label>Tono del color</label>
          <input type="color" name="hexadec" class="form-control" id="hexadec" placeholder="Tono del color" required />
        </div>
        <div class="mb-5">
          <center><button type="submit" id="guardarcolor" class="btn btn-info"><i class="fa fa-save"></i> Guardar</button></center>
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
    <script type="text/javascript" src="js/articulos_colores.js"></script>
  </body>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
</html>