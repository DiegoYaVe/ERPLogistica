<?php
  session_start();
  ini_set('display_errors', 1);
  date_default_timezone_set("America/Denver");
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
    <input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />
    <input type="hidden" id="iddoc" value="'.$_GET['id'].'" />
    <input type="hidden" id="from" value="'.$_GET['from'].'" />
    <div class="col-md-12">
      <div class="form-grop">
        <input type="text" name="nmb" class="form-control" id="nmb" autocomplete="off" placeholder="Escribe nombre, modelo o código del producto buscado" autofocus />
    </div>
  </div>
</div>

<div id="datos">

</div>

</tr></thead>';

?>
    <!-- BEGIN VENDOR JS-->
    <script src="../assets/js/jquery.min.js" type="text/javascript"></script>
    <script src="../assets/js/ui/tether.min.js" type="text/javascript"></script>
    <script src="../assets/sjs/bootstrap.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/busqueda_productos.js"></script>
  </div>
  </body>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
</html>