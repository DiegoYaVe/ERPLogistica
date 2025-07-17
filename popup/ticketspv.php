<?php
//ini_set('display_errors',1);
include('../funciones.php');
session_start();

$idcaja = $_GET['idcaja'];

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- //// -->
  <html lang="en" data-textdirection="ltr" class="loading">
  <head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no"/>
  <meta name="author" content="JD Suite">
  <title>JD CEO</title>
  <link rel="icon" type="image/png" href="../images/ico/favicon-32.png">
  <link rel="apple-touch-icon" sizes="60x60" href="../images/ico/apple-icon-60.png">
  <link rel="apple-touch-icon" sizes="76x76" href="../images/ico/apple-icon-76.png">
  <link rel="apple-touch-icon" sizes="120x120" href="../images/ico/apple-icon-120.png">
  <link rel="apple-touch-icon" sizes="152x152" href="../images/ico/apple-icon-152.png">
  <link rel="shortcut icon" type="image/x-icon" href="../images/ico/favicon-32.png">
  <link rel="shortcut icon" type="image/png" href="../images/ico/favicon-32.png">
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
  <link rel="stylesheet" type="text/css" href="../css/dropzone.css" />
  <link rel="stylesheet" type="text/css" href="../css/jquery.fancybox.min.css" />
  <!-- TINYMCE-->
  <script src="../js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>

<!-- //// -->
</head>

<body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="menu-collapsed vertical-layout vertical-menu 2-columns bg-white" style="mt-2">


<!-- style="width:40%;" bg: #e3ebf3-->
<?php

echo '
<div class="col-md-12 table-inverse p-1" style="">
<h4>Punto de Venta JDCEO - Tickets</h4>
</div>

<div class="p-1 mt-1">
  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
      <thead>
        <tr>
          <th>Folio</th>
          <th>Fecha de Cobro</th>
          <th>Cajero</th>
          <th>Total</th>
          <th>Estatus</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>';
      $sql = 'SELECT * FROM pv_tickets WHERE pt_caja = "'.$idcaja.'" AND pt_empresa = "'.$_SESSION['emp'].'" AND pt_estatus IN ("A","C") AND pt_cajero = "'.$_SESSION['uid'].'"';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        echo '
        <tr>
          <td>'.$row['pt_folio'].'</td>
          <td>'.fecha_formato($row['pt_fechacobro'],true,false).'</td>
          <td>'.$row['pt_cajero'].'</td>
          <td>$'.number_format($row['pt_total'],2).'</td>';
          if($row['pt_estatus'] == "A"){
            $estatus = "APLICADO";
            $bg = "bg-light-green bg-accent-4 text-white";
            $botones = ' 
            <div class="mb-5">
              <button type="button" class="btn btn-sm btn-info" onclick=\'window.open("detalletck.php?idticket='.$row['pt_id'].'&idcaja='.$idcaja.'","ticketscaja","width=700 height=700");return false\'>
              <i class="icon-eye4"></i> Ver detalles</button>
            </div>
            <div class="mb-5">
              <button type="button" class="btn btn-sm btn-danger" onclick="cancelar('.$row['pt_id'].','.$idcaja.');"><i class="fa fa-times"></i> Cancelar</button>
            </div>';
          }elseif ($row['pt_estatus'] == "C") {
            $estatus = "CANCELADO";
            $bg = "bg-red text-white";
            $botones = '
            <div class="mb-5">
              <button type="button" class="btn btn-sm btn-info" onclick=\'window.open("detalletck.php?idticket='.$row['pt_id'].'&idcaja='.$idcaja.'","ticketscaja","width=700 height=700");return false\'>
              <i class="icon-eye4"></i> Ver detalles</button>
            </div>';
          }
          echo '
          <td class="'.$bg.'">'.$estatus.'</td>
          <td>'.$botones.'</td>
        </tr>
        ';

      }
       echo ' 
      </tbody>

    </table>
  </div>
</div>';

?>


</body>

<!-- BEGIN VENDOR JS-->
<script src="../js/jquery.min.js"></script>
<script type="text/javascript" src="js/dynamic_search.js"></script>
<script src="../js/ui/tether.min.js" type="text/javascript"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/ui/perfect-scrollbar.jquery.min.js" type="text/javascript"></script>
<script src="../js/ui/unison.min.js" type="text/javascript"></script>
<script src="../js/ui/blockUI.min.js" type="text/javascript"></script>
<script src="../js/ui/jquery.matchHeight-min.js" type="text/javascript"></script>
<script src="../js/ui/screenfull.min.js" type="text/javascript"></script>
<script src="../js/pace.min.js" type="text/javascript"></script>

<!-- BEGIN VENDOR JS-->
<!-- BEGIN PAGE VENDOR JS-->
<script src="../js/charts/chart.min.js" type="text/javascript"></script>
<script src="../js/popover.min.js" type="text/javascript"></script>
<!-- END PAGE VENDOR JS-->
<!-- BEGIN ROBUST JS-->
<script src="../js/jquery.validate.js" type="text/javascript"></script>
<script src="../js/validation.js" type="text/javascript"></script>
<script src="../js/script.js"></script>
<!--
-->
<!-- END ROBUST JS-->
<!-- BEGIN PAGE LEVEL JS
<script src="js/scripts/pages/dashboard-lite.js" type="text/javascript"></script>  -->
<!-- END PAGE LEVEL JS-->
<script src="../js/select2.min.js"></script>


<script>
  function cancelar(idtck,idcaja){
    var a = confirm("¿Estás seguro de cancelar este ticket? ");
    if(a) window.location.href = "../query/cancelticket.php?idtck="+idtck+"&idcaja="+idcaja;

  }

  /*$('body').on("keydown", function(e) { 
    if (e.which === 13) {
      window.close();
      e.preventDefault();
    }
  }); */

</script>



</html>