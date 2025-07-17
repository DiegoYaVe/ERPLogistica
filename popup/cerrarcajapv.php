<?php
//ini_set('display_errors',1);
include('../funciones.php');
session_start();
$idcaja = $_GET['idcaja'];



$sql = 'SELECT SUM(cd_efectivo), SUM(cd_tarjeta), SUM(cd_total) FROM cajasd WHERE cd_caja = "'.$idcaja.'" AND cd_estatus = "A"';
$result = setq($sql);
$row = $result->fetch_array();

$fondo = busca($idcaja, 'cajas', 'c_id', 'c_fondo');
 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" data-textdirection="ltr" class="loading">
<head>
    <base href="">
    <title>Fabrica de inflables</title>
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

<!-- style="width:40%;" bg: #e3ebf3-->

<div class="col-md-12 p-3 h-5 " style="background: #343EDB">

<h1 class="text-white">Cierre de Caja</h1>

</div>

<?php

echo '

<div class="container" hidden>
<form action="../query/cerrarcajapvfin.php" method="POST" id="formcerrarcaja">
  <div class="row">
    <div class="col-xs-12 mt-2">
      <h5>Información del Ticket: </h5>
    </div>
    <div class="col-xs-12 p-1 form-inline">
      <div class="mb-5 col-xs-6">
        <label for="">Folio:</label>
        <input type="text" name="idcaja" value="'.$idcaja.'">
        <input type="text" name="efectivotot" id="efectivotot" value="'.($row['SUM(pt_efectivo)']+$rowcaja['tc_fondo']-$row['SUM(pt_cambio)']).'" class="form-control" readonly>
        <input type="text" name="efectivosf" id="efectivosf" value="'.($row['SUM(pt_efectivo)']-$row['SUM(pt_cambio)']).'" class="form-control" readonly>
      </div>
      <div class="mb-5 col-xs-6">
        <label for="">Cajero:</label>
        <input type="text" name="tarjetatot" id="tarjetatot" value="'.$row['SUM(pt_tarjeta)'].'" class="form-control" readonly>
      </div>
      <div class="mb-5 col-xs-6">
        <label for="">Subtotal:</label>
        <input type="text" name="valestot" id="valestot" value="'.$row['SUM(pt_vales)'].'" class="form-control" readonly>
      </div>
      <div class="mb-5 col-xs-6">
        <label for="">Subtotal:</label>
        <input type="text" name="transferenciatot" id="transferenciatot" value="'.$row['SUM(pt_trans)'].'" class="form-control" readonly>
      </div>
      <div class="mb-5 col-xs-6">
        <label for="">IVA:</label>
        <input type="text" name="chequetot" id="chequetot" value="'.$row['SUM(pt_cheque)'].'" class="form-control" readonly>
      </div>
      <div class="mb-5 col-xs-6">
        <label for="">Total:</label>
        <input type="text" name="totaltot" id="totaltot" value="'.($row['SUM(pt_total)']+$rowcaja['tc_fondo']).'" class="form-control" readonly>
      </div>
      <input type="text" name="totaltotsf" id="totaltotsf" value="'.$row['SUM(pt_total)'].'" class="form-control" readonly>
      <input type="number" name="fondotot" id="fondotot" value="'.$rowcaja['tc_fondo'].'" readonly>
    </div>
  </div>
</div>

<!--  

<div class="table-responsive p-1">
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th class="text-xs-center" colspan="2">
        Información del Ticket: 
      </th>
    </tr>
  </thead>
  <tr>
    <td>Folio: </td>
    <td class="number-align"></td>
  </tr>
  <tr>
    <td>Cajero: </td>
    <td class="number-align"></td>
  </tr>
  <tr>
    <td>Subtotal: </td>
    <td class="number-align"></td>
  </tr>
  <tr>
    <td>IVA: </td>
    <td class="number-align"></td>
  </tr>
  <tr>
    <td>Total: </td>
    <td class="number-align"></td>
  </tr>
</table>  
</div>

-->

<!-- <button type="button" data-toggle="tooltip"
    data-placement="top" title="Ingresos de: $'.number_format($row['SUM(cd_efectivo)'],2).' + Fondo de: $'.number_format($fondo,2).'" 
    class="btn round btn-sm btn-warning" style="padding: 2px;"></button> -->
<div class="table-responsive p-2">
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th class="text-xs-center" colspan="3">
        Total en caja:
      </th>
    </tr>
  </thead>
  <tr>
    <td>Efectivo:</td>
    <td class="number-align"><input name="efectivo" onchange="alcambiarefe();" min="0" id="efectivo" type="number" step="0.01" value="0.00" class="form-control number-align" readonly></td>
  </tr>

  <tr>
  <td colspan="2">
  <div class="row">
      <div class="col-6">
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th>Billetes: </th>
                <th>Cantidad: </th>
              </tr>
            </thead>
            <tbody>
            <tr>
              <td>Billetes de $1000: </td>
              <td><input onchange="efectivocj();" type="number" name="1000" value="0" id="1000" min="0" max="999" step="1" class="form-control number-align" autofocus required></td>
            </tr>
            <tr>
              <td>Billetes de $500: </td>
              <td><input onchange="efectivocj();" type="number" name="500" value="0" id="500" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            <tr>
              <td>Billetes de $200: </td>
              <td><input onchange="efectivocj();" type="number" name="200" value="0" id="200" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            <tr>
              <td>Billetes de $100: </td>
              <td><input onchange="efectivocj();" type="number" name="100" value="0" id="100" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            <tr>
              <td>Billetes de $50: </td>
              <td><input onchange="efectivocj();" type="number" name="50" value="0" id="50" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            <tr>
              <td>Billetes de $20: </td>
              <td><input onchange="efectivocj();" type="number" name="20" value="0" id="20" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="col-6">
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th>Monedas: </th>
                <th>Cantidad: </th>
              </tr>
            </thead>
            <tbody>
            <tr>
              <td>Monedas de $10: </td>
              <td><input onchange="efectivocj();" type="number" name="10" value="0" id="10" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            <tr>
              <td>Monedas de $5: </td>
              <td><input onchange="efectivocj();" type="number" name="5" value="0" id="5" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            <tr>
              <td>Monedas de $2: </td>
              <td><input onchange="efectivocj();" type="number" name="2" value="0" id="2" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            <tr>
              <td>Monedas de $1: </td>
              <td><input onchange="efectivocj();" type="number" name="1" value="0" id="1" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            <tr>
              <td>Monedas de $.50/c: </td>
              <td><input onchange="efectivocj();" type="number" name="50c" value="0" id="50c" min="0" max="999" step="1" class="form-control number-align"></td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </td>
  
  </tr>
  
  <!-- <tr>
    <td>Fondo Inicial: </td>
    <td></td>
    <td  class="number-align">$'.number_format($rowcaja['tc_fondo'],2).'</td> 
  </tr> -->
  <tr>
    <td>Tarjeta: </td>
    <td class="number-align"><input name="tarjeta" onchange="alcambiartj();" min="0" id="tarjeta" type="number" step="0.01" value="0.00" class="form-control number-align" required></td>
    <!-- <td  class="number-align">$'.number_format($row['SUM(pt_tarjeta)'],2).'</td> -->
  </tr>
  <tr>
    <td>Total: </td>
    <td class="number-align"><input name="total" id="total" type="number" min="0" step="0.01" value="0.00" class="form-control number-align" required readonly></td>
    <!-- <td  class="number-align">$'.number_format($row['SUM(pt_total)']+$rowcaja['tc_fondo'],2).'</td> -->
  </tr>
  <tr>
    <td colspan="2" align="center" class="text-xs-center p-2"><button onclick="confirmacerrar();" type="button" class="btn btn-danger"><i class="fa fa-times"></i> Cerrar Caja</button></td>
  </tr>
</table>
</form>
</div>

';
?>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Denominaciones en Caja</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-1 row">
        <div class="col-xs-6">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Billetes: </th>
                  <th>Cantidad: </th>
                </tr>
              </thead>
              <tbody>
              <tr>
                <td>Billetes de $1000: </td>
                <td><input type="number" name="1000" value="0" id="1000" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Billetes de $500: </td>
                <td><input type="number" name="500" value="0" id="500" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Billetes de $200: </td>
                <td><input type="number" name="200" value="0" id="200" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Billetes de $100: </td>
                <td><input type="number" name="100" value="0" id="100" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Billetes de $50: </td>
                <td><input type="number" name="50" value="0" id="50" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Billetes de $20: </td>
                <td><input type="number" name="20" value="0" id="20" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="col-xs-6">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>Monedas: </th>
                  <th>Cantidad: </th>
                </tr>
              </thead>
              <tbody>
              <tr>
                <td>Monedas de $10: </td>
                <td><input type="number" name="10" value="0" id="10" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Monedas de $5: </td>
                <td><input type="number" name="5" value="0" id="5" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Monedas de $2: </td>
                <td><input type="number" name="2" value="0" id="2" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Monedas de $1: </td>
                <td><input type="number" name="1" value="0" id="1" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              <tr>
                <td>Monedas de $.50/c: </td>
                <td><input type="number" name="50c" value="0" id="50c" min="0" max="999" step="1" class="form-control number-align"></td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" onclick="efectivocj();" class="btn btn-primary"><i class="icon-bar-chart"></i> Guardar y Salir</button>
      </div>
    </div>
  </div>
</div>


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
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

$('#1000').select();
$('.collapse').collapse();




function alcambiarefe(){
  var efectivo = document.getElementById('efectivo');
  var efectivotot = document.getElementById('efectivotot');
  var total = document.getElementById('total');
  var tarjeta = document.getElementById('tarjeta');
  var vales = document.getElementById('vales');
  var transferencia = document.getElementById('transferencia');
  var cheque = document.getElementById('cheque');
  if(efectivo.value == "") efectivo.value = 0.00;
  if(tarjeta.value == "") tarjeta.value = 0.00;
  if(vales.value == "") vales.value = 0.00;
  if(transferencia.value == "") transferencia.value = 0.00;
  if(cheque.value == "") cheque.value = 0.00;
  if(parseFloat(efectivo.value) == parseFloat(efectivotot.value)) efectivo.style = "border-color: #00ff50;";
  else efectivo.style = "border-color: red;";
  total.value = (parseFloat(efectivo.value)+parseFloat(tarjeta.value)+parseFloat(vales.value)+parseFloat(transferencia.value)+parseFloat(cheque.value)); 
  var totaltot = document.getElementById('totaltot');
  /* if(parseFloat(total.value) == parseFloat(totaltot.value)) total.style = "border-color: #00ff50;";
  else total.style = "border-color: red;"; */
}

function alcambiartj(){
  var efectivo = document.getElementById('efectivo');
  var efectivotot = document.getElementById('efectivotot');
  var total = document.getElementById('total');
  var tarjeta = document.getElementById('tarjeta');
  /* var vales = document.getElementById('vales');
  var transferencia = document.getElementById('transferencia');
  var cheque = document.getElementById('cheque');
  var tarjetatot = document.getElementById('tarjetatot'); */
  if(efectivo.value == "") efectivo.value = 0.00;
  if(tarjeta.value == "") tarjeta.value = 0.00;
  /* if(vales.value == "") vales.value = 0.00;
  if(transferencia.value == "") transferencia.value = 0.00;
  if(cheque.value == "") cheque.value = 0.00;
  if(parseFloat(tarjeta.value) == parseFloat(tarjetatot.value)) tarjeta.style = "border-color: #00ff50;";
  else tarjeta.style = "border-color: red;"; */
  total.value = (parseFloat(efectivo.value)+parseFloat(tarjeta.value)); //+parseFloat(vales.value)+parseFloat(transferencia.value)+parseFloat(cheque.value)
  var totaltot = document.getElementById('totaltot');
  /* if(parseFloat(total.value) == parseFloat(totaltot.value)) total.style = "border-color: #00ff50;";
  else total.style = "border-color: red;"; */
}

/* function alcambiarval(){
  var efectivo = document.getElementById('efectivo');
  var efectivotot = document.getElementById('efectivotot');
  var total = document.getElementById('total');
  var tarjeta = document.getElementById('tarjeta');
  var vales = document.getElementById('vales');
  var transferencia = document.getElementById('transferencia');
  var cheque = document.getElementById('cheque');
  var valestot = document.getElementById('valestot');
  if(efectivo.value == "") efectivo.value = 0.00;
  if(tarjeta.value == "") tarjeta.value = 0.00;
  if(vales.value == "") vales.value = 0.00;
  if(transferencia.value == "") transferencia.value = 0.00;
  if(cheque.value == "") cheque.value = 0.00;
  if(parseFloat(vales.value) == parseFloat(valestot.value)) vales.style = "border-color: #00ff50;";
  else vales.style = "border-color: red;";
  total.value = (parseFloat(efectivo.value)+parseFloat(tarjeta.value)+parseFloat(vales.value)+parseFloat(transferencia.value)+parseFloat(cheque.value)); 
  var totaltot = document.getElementById('totaltot');
  if(parseFloat(total.value) == parseFloat(totaltot.value)) total.style = "border-color: #00ff50;";
  else total.style = "border-color: red;";
}

function alcambiartrans(){
  var efectivo = document.getElementById('efectivo');
  var efectivotot = document.getElementById('efectivotot');
  var total = document.getElementById('total');
  var tarjeta = document.getElementById('tarjeta');
  var vales = document.getElementById('vales');
  var transferencia = document.getElementById('transferencia');
  var cheque = document.getElementById('cheque');
  var transferenciatot = document.getElementById('transferenciatot');
  if(efectivo.value == "") efectivo.value = 0.00;
  if(tarjeta.value == "") tarjeta.value = 0.00;
  if(vales.value == "") vales.value = 0.00;
  if(transferencia.value == "") transferencia.value = 0.00;
  if(cheque.value == "") cheque.value = 0.00;
  if(parseFloat(transferencia.value) == parseFloat(transferenciatot.value)) transferencia.style = "border-color: #00ff50;";
  else transferencia.style = "border-color: red;";
  total.value = (parseFloat(efectivo.value)+parseFloat(tarjeta.value)+parseFloat(vales.value)+parseFloat(transferencia.value)+parseFloat(cheque.value)); 
  var totaltot = document.getElementById('totaltot');
  if(parseFloat(total.value) == parseFloat(totaltot.value)) total.style = "border-color: #00ff50;";
  else total.style = "border-color: red;";
}

function alcambiarche(){
  var efectivo = document.getElementById('efectivo');
  var efectivotot = document.getElementById('efectivotot');
  var total = document.getElementById('total');
  var tarjeta = document.getElementById('tarjeta');
  var vales = document.getElementById('vales');
  var transferencia = document.getElementById('transferencia');
  var cheque = document.getElementById('cheque');
  var chequetot = document.getElementById('chequetot');
  if(efectivo.value == "") efectivo.value = 0.00;
  if(tarjeta.value == "") tarjeta.value = 0.00;
  if(vales.value == "") vales.value = 0.00;
  if(transferencia.value == "") transferencia.value = 0.00;
  if(cheque.value == "") cheque.value = 0.00;
  if(parseFloat(cheque.value) == parseFloat(chequetot.value)) cheque.style = "border-color: #00ff50;"; 
  else heque.style = "border-color: red;";
  total.value = (parseFloat(efectivo.value)+parseFloat(tarjeta.value)+parseFloat(vales.value)+parseFloat(transferencia.value)+parseFloat(cheque.value)); 
  var totaltot = document.getElementById('totaltot');
  if(parseFloat(total.value) == parseFloat(totaltot.value)) total.style = "border-color: #00ff50;";
  else total.style = "border-color: red;";
} */

function efectivocj(){
  var $1000 = document.getElementById('1000').value;
  var $500 = document.getElementById('500').value;
  var $200 = document.getElementById('200').value;
  var $100 = document.getElementById('100').value;
  var $50 = document.getElementById('50').value;
  var $20 = document.getElementById('20').value;
  var $10 = document.getElementById('10').value;
  var $5 = document.getElementById('5').value;
  var $2 = document.getElementById('2').value;
  var $1 = document.getElementById('1').value;
  var $50c = document.getElementById('50c').value;
  var efectivo = document.getElementById('efectivo');
  var efectivotot = document.getElementById('efectivotot');
  var totaltot = document.getElementById('totaltot');
  var total = document.getElementById('total');
  var totalbilletesmon = parseFloat((1000*$1000)+(500*$500)+(200*$200)+(100*$100)+(50*$50)+(20*$20)+(10*$10)+(5*$5)+(2*$2)+(1*$1)+(.5*$50c));
  efectivo.value = totalbilletesmon;
  console.log(totalbilletesmon);
  /* if(parseFloat(efectivo.value) == parseFloat(efectivotot.value)) efectivo.style = "border-color: #00ff50;";
  else efectivo.style = "border-color: red;"; */
  total.value = (parseFloat(efectivo.value)+parseFloat(tarjeta.value)); //+parseFloat(vales.value)+parseFloat(transferencia.value)+parseFloat(cheque.value)
  /*if(parseFloat(total.value) == parseFloat(totaltot.value)) total.style = "border-color: #00ff50;";
  else total.style = "border-color: red;";
  var mod = document.getElementById('exampleModal');
   $('#exampleModal').modal('hide'); */
  

}

function confirmacerrar(){
  var totaltot = document.getElementById('totaltot');
  var total = document.getElementById('total');
  /* if(parseFloat(total.value) == parseFloat(totaltot.value))  */var a = confirm('¿Seguro que deseas cerrar esta caja? Posteriormente no pordras generar alguna acción sobre ella.');
  /* else var a = confirm('Los valores ingresados no coinciden con la cantidad total vendida. ¿Seguro que deseas cerrar esta caja? Posteriormente no pordras generar alguna acción sobre ella.'); */

  if(a){
    var form = document.getElementById('formcerrarcaja');
    form.submit();
  }


}




</script>

</html>