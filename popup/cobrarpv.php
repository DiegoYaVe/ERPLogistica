<?php
//ini_set('display_errors',1);
include('../funciones.php');
session_start();
$idcaja = $_GET['idcaja'];
$remision = $_GET['remision'];

$sql = 'SELECT * FROM remisiones WHERE r_id = "'.$remision.'"';
$result = setq($sql);
$row = $result->fetch_array();

$sqlcxc = 'SELECT cx_id, cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$remision.'"';
$resultcxc = setq($sqlcxc);
list($idcxc, $total, $abonado, $estatus) = $resultcxc -> fetch_array();
$saldo = $total - $abonado;

 

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

<div class="col-md-12 table-inverse p-1 mt-5" style="">
<h4>Cobrar remisión <?php echo $row['r_folio']; ?></h4>
</div>
 
<?php
$sqlb = 'SELECT SUM(i_monto) FROM ingresos
        WHERE i_id IN (SELECT ic_ingreso FROM ingreso_cxcobrar
                      WHERE ic_cxcobrar ="'.$idcxc.'") AND i_estatus="P"';
$resultb = setq($sqlb);
list($pend) = $resultb -> fetch_array();
echo '
<input type="hidden" value="'.$saldo.'" id="saldo" name="saldo">
<div class="container" hidden>
<form action="../query/cobrarfin.php" method="POST" id="formcobro">
  <div class="row">
    <div class="col-xs-12 mt-2">
      <h5>Información del cobro: </h5>
    </div>
    <div class="col-xs-12 p-1 form-inline">
      <div class="mb-5 col-xs-6">
        <label for="">Folio:</label>
        <input type="text" name="remision" value="'.$remision.'">
        <input type="text" name="caja" value="'.$idcaja.'">
        <input type="text" name="folio" value="'.$row['r_folio'].'" class="form-control" readonly>
      </div>
      <div class="mb-5 col-xs-6">
        <label for="">Vendedor:</label>
        <input type="text" value="'.busca($_SESSION['uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'" class="form-control" readonly>
      </div>
      <div class="mb-5 col-xs-6">
        <label for="">Total:</label>
        <input  name="total" id="total" type="text" value="'.$total.'" class="form-control" readonly>
      </div>
      <div class="mb-5 col-xs-6">
        <label for="">Abonado:</label>
        <input type="text" name="abonado" id="abonado" value="'.$abonado.'" class="form-control" readonly>
      </div>';
      echo '<div class="mb-5 col-xs-6">
        <label for="">Abonos por confirmar:</label>
        <input type="text" name="pendiente" id="pendiente" value="'.$pend.'" class="form-control" readonly>
      </div>';
      if($saldo-$pend < 0){
        $saldotot = 0;
      } else {
        $saldotot = $saldo-$pend;
      }
      echo '<div class="mb-5 col-xs-6">
        <label for=""></label>
        <input type="text" value="'.$saldo-$pend.'" class="form-control" readonly>
      </div>
    </div>
  </div>
</div>

<div class="card">
<center>
<div class="table-responsive p-1 col-11">
<table class="table table-bordered table-striped" id="myTableG">
  <thead>
    <tr>
      <th class="text-xs-center text-white" colspan="2">
        Información de la remisión:
      </th>
    </tr>
  </thead>
  <tr>
    <td>Folio: </td>
    <td class="number-align">'.$row['r_folio'].'</td>
  </tr>
  <tr>
    <td>Vendedor: </td>
    <td class="number-align">'.busca($_SESSION['uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
  </tr>
  <tr>
    <td>Total: </td>
    <td class="number-align">$'.number_format($total,2).'</td>
  </tr>
  <tr>
    <td>Abonado: </td>
    <td class="number-align">$'.number_format($abonado,2).'</td>
  </tr>';
  if(floatval($pend) > 0) echo '<tr>
    <td>Ingresos por confirmar: </td>
    <td class="number-align">$'.number_format($pend,2).'</td>
  </tr>';
  echo '<tr>
    <td class="number-align"><h2>Saldo: </h2></td>
    <td> <h2>$'.number_format($saldo-$pend,2).' </h2> </td>
  </tr>
</table>
</div>
</center>

</div>';




echo '
<div class="card">
<center>
<div class="table-responsive p-1 col-8">
<table class="table table-bordered table-striped" id="myTableG">
  <thead>
    <tr>
      <th align="center" class="text-xs-center text-white" colspan="3">
        Forma de Pago:
      </th>
    </tr>
  </thead>
  <tbody>
  <tr>
    <td>Efectivo por cobrar: </td>
      <td class="number-align"><input name="efectivo" id="efectivo" type="number" step="0.01" min="0" value="0.00" class="form-control number-align" autofocus onfocus="this.select();" required></td>
    <td></td>
  </tr>
  <tr>
      <td>Efectivo recibido: </td>
      <td class="number-align"><input name="efectivorec" id="efectivorec" type="number" step="0.01" min="0" value="0.00" class="form-control number-align" autofocus onfocus="this.select();" required></td>
    <td></td>
  </tr>
  <tr>
    <td>Tarjeta: </td>
    <td class="number-align"><input name="tarjeta" id="tarjeta" type="number" step="0.01" min="0" value="0.00" class="form-control number-align" onfocus="this.select();" onchange="calculacomision();" required></td>
    <td>
    <select id="comisiones" name="comisiones" class="form-control" onchange="calculacomision(2);" disabled>';
    $sqlcom = 'SELECT * FROM comisiones_tarjeta WHERE cta_estatus = "A" ORDER BY cta_exhibiciones';
    $resultcom = setq($sqlcom);
      echo '<option value="">Seleccionar Meses</option>';
      while($row = $resultcom->fetch_array()){
        echo '<option value="'.$row['cta_id'].'">'.$row['cta_exhibiciones'].' MSI</option>';
      }
    echo '</select>
  </td>
  </tr>
  <tr style="display:none;" id="pasarportdc">
    <td colspan="3"><center><h3>Monto a pasar por terminal: </h3><h1><span id="pagarcontdc"></span></b></h1><center></td>
  </tr>
  <tr>
    <td align="center" colspan="3" class="text-xs-center p-2"><button id="fin" onclick="finalizar();" type="button" class="btn btn-success"><i class="fa fa-save"></i> Cobrar</button></td>
  </tr>

  </tbody>
</table>
</form>
</div>
</center>
</div>
';

//Tabludador de meses para script de calculo
  $sqlcom = 'SELECT * FROM comisiones_tarjeta WHERE cta_estatus = "A" ORDER BY cta_exhibiciones';
  $resultcom = setq($sqlcom);
  $min = 0;
  while($row = $resultcom->fetch_array()){
    if($min == 0) echo '<input type="hidden" id="mincomismes" value="'.$row['cta_id'].'">';
    echo '<input type="hidden" id="comismes'.$row['cta_id'].'" value="'.$row['cta_porcentaje'].'">';
    $min++;
  }


?>
<script>
  function calculacomision(evento){
    var montotdc = document.getElementById("tarjeta").value;
    montotdc = parseFloat(montotdc);

    if(montotdc > 0){
      document.getElementById("comisiones").disabled = false;
      document.getElementById("comisiones").required = true;

      var mesessel = document.getElementById("comisiones").value;
      if(mesessel == ""){
        if(evento == "2"){
          alert("Error: es necesario elegir un elemento de la lista");
        }

        document.getElementById("comisiones").value = document.getElementById("mincomismes").value;
        mesessel = document.getElementById("mincomismes").value;
      }
      var porccomis = document.getElementById("comismes"+mesessel).value;
      porccomis = parseFloat(porccomis);

      var importetdc = montotdc*(1+(porccomis/100));
      document.getElementById("pasarportdc").style.display = "";
      document.getElementById("pagarcontdc").innerHTML = importetdc.toFixed(2);
    }
    else{
      document.getElementById("pasarportdc").style.display = "none";
      document.getElementById("comisiones").value = "";
      document.getElementById("comisiones").disabled = true;
      document.getElementById("comisiones").required = false;
    }


  }
</script>

</body>

<!-- BEGIN VENDOR JS-->
<script src="../assets/js/jquery.min.js" type="text/javascript"></script>
<script src="../assets/js/ui/tether.min.js" type="text/javascript"></script>
<script src="../assets/js/bootstrap.min.js" type="text/javascript"></script>

<script>
$('#efectivo').select();

function finalizar(){
  var efectivo = document.getElementById('efectivo');
  var efectivorec = document.getElementById('efectivorec');
  var tarjeta = document.getElementById('tarjeta');
  var saldo = document.getElementById('saldo');
  
  if(parseFloat(efectivorec.value) >= parseFloat(efectivo.value)){
    document.getElementById('formcobro').submit();
  } else{
    alert('El efectivo recibido es menor al que se va a cobrar.');
  }
}

$('#efectivo').on("keydown", function(e) { 
  if(event.keyCode == 13){
    var btn = document.getElementById('fin');
    btn.click();
  }else{

  }
});

$('#tarjeta').on("keydown", function(e) { 
  if(event.keyCode == 13){
    var btn = document.getElementById('fin');
    btn.click();
  }else{

  }
});

/* $('#vales').on("keydown", function(e) { 
  if(event.keyCode == 13){
    var btn = document.getElementById('fin');
    btn.click();
  }else{

  }
});

$('#transferencia').on("keydown", function(e) { 
  if(event.keyCode == 13){
    var btn = document.getElementById('fin');
    btn.click();
  }else{

  }
});

$('#cheque').on("keydown", function(e) { 
  if(event.keyCode == 13){
    var btn = document.getElementById('fin');
    btn.click();
  }else{

  }
}); */

var selcomision = document.getElementById("comisiones");
var selformapago = document.getElementById("formapago");

function seltarjeta(){
  if(selformapago.value == 1){
    selcomision.removeAttribute("hidden");
    selcomision.setAttribute("required", true);
  } else{
    selcomision.setAttribute("hidden", true);
    selcomision.removeAttribute("required");
  }
}

</script>

<style>
  #myTableG {
    width: 100%;
    border-collapse: collapse;
  }

  #myTableG th, #myTableG td {
    padding: 8px;
    border: 1px solid #ddd;
    background-color: #5490c6; /* Fondo gris claro para todas las celdas */
  }

  #myTableG tbody tr:nth-child(odd) td {
    background-color: #ffffff; /* Fondo blanco para las filas impares */
  }

  #myTableG tbody tr:nth-child(even) td {
    background-color: #f9f9f9; /* Fondo gris claro para las filas pares */
  }
  
  #myTableG {
    font-size: 12px; /* Cambia el tamaño de letra deseado */
  }
</style>



</html>