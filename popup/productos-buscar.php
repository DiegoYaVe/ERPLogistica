<?php
  session_start();
//  ini_set('display_errors', 1);
  date_default_timezone_set("America/Denver");
  $title = 'JD CEO';
  $numnot = 0;
  if($numnot > 0) $numtag = "danger"; else $numtag = "success";
  if(!isset($_SESSION['emp'])) $_SESSION['emp'] = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" data-textdirection="ltr" class="loading">
<head>
    <base href="">
    <title>Inflalandia</title>
    <meta name="description"
      content="SOMOS LA FÁBRICA #1 DE IFLABLES EN MÉXICO" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../assets/media/logos/favicon.png" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link href="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/jquery.fancybox.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" class="">
    <!--end::Global Stylesheets Bundle-->
    <script src="../assets/plugins/global/plugins.bundle.js"></script>
    <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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

echo'
<div id="queryin">
</div>
<div class="container-fluid mt-1 border-top rounded">
  <div class="row">
    <input type="hidden" id="iddoc" value="'.$_GET['id'].'" />
    <input type="hidden" id="tipodoc" value="'.$_GET['tipo'].'" />
    <input type="hidden" id="from" value="'.$_GET['from'].'" />
    <div class="col-md-12">
      <div class="form-grop">
        <input type="text" name="nmb" class="form-control" id="nmb" autocomplete="off" placeholder="Escribe nombre o código del producto buscado" autofocus />
    </div>
  </div>
</div>

<div id="datos">

</div>

</tr></thead>';

?>
    <!-- BEGIN VENDOR JS-->
    <script src="../assets/js/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/busqueda_productos.js"></script>
  </div>
  </body>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
</html>