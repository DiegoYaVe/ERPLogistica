<?php
//ini_set('display_errors',1);
include('../funciones.php');
session_start();

$idcaja = $_GET['idcaja'];
$idticket = $_GET['idticket'];

$sql = 'SELECT * FROM pv_tickets WHERE pt_id = "'.$idticket.'" AND pt_empresa = "'.$_SESSION['emp'].'" AND pt_cajero = "'.$_SESSION['uid'].'"';
$result = setq($sql);
$row = $result->fetch_array();

if($row['pt_estatus'] == "A"){
  $estatus = "APLICADO";
  $bg = "bg-light-green bg-accent-4 text-white";
}elseif ($row['pt_estatus'] == "C") {
  $estatus = "CANCELADO";
  $bg = "bg-red text-white";
}

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


<div class="col-md-12 table-inverse p-1" style="">
  <h4>Punto de Venta JDCEO - Detalle Ticket</h4>
</div>

<div class="col-12 p-1">
  <a class="btn btn-warning" href="ticketspv.php?idcaja=<?php echo $idcaja;?>"><i class="fa fa-arrow-left"></i> Atras</a>
</div>

<!-- style="width:40%;" bg: #e3ebf3-->
<?php

echo '


<div class="p-1">
  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
      <thead>
        <tr>
          <th class="text-xs-center" colspan="2">Resumen del Ticket</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>Folio: </th>
          <td>'.$row['pt_folio'].'</td>
        </tr>
        <tr>
          <th>Fecha de Cobro: </th>
          <td>'.fecha_formato($row['pt_fechacobro'],true,false).'</td>
        </tr>
        <tr>
          <th>Cajero: </th>
          <td>'.$row['pt_cajero'].'</td>
        </tr>
        <tr>
          <th>Total: </th>
          <td>$'.number_format($row['pt_total'],2).'</td>
        </tr>
        <tr>
          <th>Estatus: </th>
          <td class="'.$bg.'">'.$estatus.'</td>
        </tr>';
        if($row['pt_estatus'] == "C"){
        echo '
        <tr>
          <th>Usuario de Cancelación: </th>
          <td>'.$row['pt_ucancela'].'</td>
        </tr>
        <tr>
          <th>Fecha de Cancelación: </th>
          <td>'.$row['pt_fcancela'].'</td>
        </tr> ';
        }
        echo '
        <tr>
          <th class="text-xs-center" colspan="2">Formas de Pago: </th>
        </tr>
        <tr>
          <th>Efectivo: </th>
          <td>$'.number_format($row['pt_efectivo'],2).'</td>
        </tr>
        <tr>
          <th>Tarjeta: </th>
          <td>$'.number_format($row['pt_tarjeta'],2).'</td>
        </tr>
        <tr>
          <th>Vales: </th>
          <td>$'.number_format($row['pt_vales'],2).'</td>
        </tr>
        <tr>
          <th>Transferencia: </th>
          <td>$'.number_format($row['pt_trans'],2).'</td>
        </tr>
        <tr>
          <th>Cheque: </th>
          <td>$'.number_format($row['pt_cheque'],2).'</td>
        </tr>
        <tr>
          <th class="bg-light-green bg-accent-4 text-white">Total: </th>
          <td class="bg-light-green bg-accent-4 text-white">$'.number_format($row['pt_total'],2).'</td>
        </tr>
        <tr>
          <th class="bg-light-blue bg-accent-4 text-white">Cambio: </th>
          <td class="bg-light-blue bg-accent-4 text-white">$'.number_format($row['pt_cambio'],2).'</td>
        </tr>
        <tr>
          <th>Ver Productos:</th>
          <td><button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-target="#exampleModal"><i class="icon-eye4"></i></button></td>
        </tr>
      </tbody>

    </table>
  </div>
</div>

';

$sqlprod = 'SELECT * FROM pv_ticketsd WHERE ptd_ticket = "'.$idticket.'"';
$resultprod = setq($sqlprod);

echo '
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Productos del Ticket '.$row['pt_folio'].'</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th class="border p-1">Articulo</th>
                <th class="border p-1">Cantidad</th>
                <th class="border p-1">Precio</th>
              </tr>
            </thead>
            <tbody>';
              while($rowprod = $resultprod->fetch_array()){
                echo '
                <tr>
                  <td class="border p-1">'.$rowprod['ptd_nmbarticulo'].'</td>
                  <td class="border p-1">'.$rowprod['ptd_cantidad'].'</td>
                  <td class="border p-1">$'.number_format($rowprod['ptd_precio'],2).'</td>
                </tr>';
              }
              echo '
              <tr>
                <th class="bg-light-green bg-accent-4 text-white p-1">Total: </th>
                <td class="bg-light-green bg-accent-4 text-white p-1">
                <td class="bg-light-green bg-accent-4 text-white p-1">$'.number_format($row['pt_total'],2).'</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
      </div>
    </div>
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

/*
$('body').on("keydown", function(e) { 
  if (e.which === 13) {
    window.close();
    e.preventDefault();
  }
}); */

</script>
</html>