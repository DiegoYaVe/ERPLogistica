<?php
  session_start();
//  ini_set('display_errors', 1);
  include_once('../funciones.php');
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
    <!-- END Custom CSS-->
    <link rel="stylesheet" type="text/css" href="../css/select2.min.css" />
  </head>
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
function checkcantidad(){
  var requeridos = document.getElementById("requerido").value;
  var cantidad = 0;
  for (i=0;i<document.activos1.elements.length;i++){
    if(document.activos1.elements[i].type == "checkbox"){
      if(document.activos1.elements[i].checked){
        cantidad=cantidad+1;
      }
    }
  }
}
</script>
<?php
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
$almacen = busca($_GET['remision'],'remisiones','r_id','r_almacen');
$estatus = busca($_GET['remision'],'remisiones','r_id','r_estatus');

if($estatus == "A"){
  $sql = 'SELECT * FROM activo_almacen INNER JOIN activos ON aa_activo = a_id
          INNER JOIN remision_activo ON ra_activo = aa_id
          WHERE ra_remision = "'.$_GET['remision'].'" ';
  $display = 'style="display:none;"';
  $title = "Activos recargados";
}
else{
  $producto = busca($_GET['remision'],'remisionesd','rd_id = "'.$_GET['idrec'].'" AND rd_remision','rd_articulo');

  $sql = 'SELECT activos.*,aa_noserie,aa_id FROM activo_almacen INNER JOIN activos ON aa_activo = a_id
          INNER JOIN activo_producto ON ap_activo = a_id
          WHERE aa_almacen = "'.$almacen.'" AND aa_estatus = "L"
          AND ap_producto = "'.$producto.'"
          ORDER BY aa_id ASC';
  $display  = "";
  $title = "Elegir activos disponibles";
}

echo'
<div id="queryin">
</div>
<div class="container mt-1 border-top rounded">
  <div class="row">
    <input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />
    <div class="col-md-12">
      <div class="form-grop">
        <h2>'.$title.'</h2>
    </div>
  </div>
</div>
<div id="datos" class="table-responsive">
<form method="post" name="activos1" action="../?modulo=remisiones&accion=updateactivos&remision='.$_GET['remision'].'&id='.$_GET['idrec'].'&from=popup" onsubmit="checksubmit();">';

$result = setq($sql);
echo '<table class="table table-stripped table-hover">
      <thead>
        <tr>
          <th>Seleccionar</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Serie</th>
        </tr>
      </thead>';
$ire = 0;
while($row = $result->fetch_array()){
  $ire++;
  if($ire%2) $clastab = ' bg-blue bg-accent-1'; else $clastab = ' bg-info bg-lighten-2';

  if(busca($_GET['remision'],'remision_activo','ra_idr = "'.$_GET['idrec'].'" AND ra_activo = "'.$row['aa_id'].'" AND ra_remision','COUNT(*)') > 0)
    $active = 'checked';
  else
    $active = "";

  echo '<tr class="'.$clastab.'">
          <td class="p-1"><input type="checkbox" name="activo'.$row['aa_id'].'" onchange="checkcantidad();" '.$active.' '.$display .' /></td>
          <th>'.$row['a_nmb'].'</th>
          <td>'.$row['a_uso'].' '.$row['a_accesorio'].' '.$row['a_tipoactivo'].' '.$row['a_capacidad'].' '.$row['a_presion'].'</td>
          <th>'.$row['aa_noserie'].'</th>
        </tr>';
}
echo '<tr><td colspan="4"><center>
        <button type="submit" id="guardar" class="btn btn-primary" '.$display .'>
          <i class="fa fa-redo"></i> Actualizar
        </button></center>
      </td></tr>';
echo '</table></form></div>';


?>
    <!-- BEGIN VENDOR JS-->
    <script src="../js/jquery.min.js" type="text/javascript"></script>
    <script src="../js/ui/tether.min.js" type="text/javascript"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
  </div>
  </body>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
</html>