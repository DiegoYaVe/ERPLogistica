<?php
  session_start();
//  ini_set('display_errors', 1);
  date_default_timezone_set("America/Mexico_City");
  $title = 'JD CEO';
  include('funciones.php');
  $numnot = 0;
  if($numnot > 0) $numtag = "danger"; else $numtag = "success";
  if(!isset($_SESSION['emp'])) $_SESSION['emp'] = 0;
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
    <link rel="shortcut icon" href="assets/media/logos/favicon.png" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link href="assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/jquery.fancybox.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" class="">
    <!--end::Global Stylesheets Bundle-->
    <script src="assets/plugins/global/plugins.bundle.js"></script>
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
/*
function valida(){
  if (document.forma.combo.value=='') {
    alert("\nSelecciona un CFDI.");
    document.forma.combo.focus();
  }
  else{
    var folio = document.forma.combo.value;
    var importe = document.getElementById("total"+folio).value;
    var parcial = document.getElementById("parcial"+folio).value;
    var uuid = document.getElementById("uuid"+folio).value;
    var imported = parseFloat(importe).toFixed(2);

    window.opener.document.<?php echo $_GET['form']; ?>.<?php echo $_GET['input']; ?>.value = document.forma.combo.value;
    window.opener.document.<?php echo $_GET['form']; ?>.impsaldoant.value = imported;
    window.opener.document.<?php echo $_GET['form']; ?>.noparc.value = parcial;
    window.opener.document.<?php echo $_GET['form']; ?>.iduuid.value = uuid;
    window.opener.document.<?php echo $_GET['form']; ?>.iduuid.readOnly = true
    window.opener.document.<?php echo $_GET['form']; ?>.imppagado.focus();
    window.close();
  }
}
*/
function checksubmit(){
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function confirmasub(){
  var conf = confirm("Las facturas seleccionadas y los montos mayores a 0 formarán parte de tu complemento de pago\n ¿Deseas continuar?");
  if(conf == true){
    document.forma.submit();
    document.getElementById("agregar").disabled = true;
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

if(!isset($_POST['tipo'])) $_POST['tipo'] = "F";

echo '<form method="post">
      <div style="font-size:28px;">MONTO PAGADO $
        <span style="font-size:35px;" id="montosinasig">0.00</span>
        <button type="button" class="btn btn-primary" onclick="confirmasub();"><i class="fas fa-calculator"></i> Agregar pagos al complemento</button>
      </div></form>';

echo '<form autocomplete="off" method="post" name="forma" action="index.php?modulo=compago&accion=setpagosm&cpago='.$_GET['cpago'].'&cliente='.$_GET['cliente'].'">
        <input type="hidden" name="montoas" id="montoas" value="0" />
        <input type="hidden" name="facturas" id="facturas" value="" />
<div class="table-responsive">
<table class="table table-striped">
<thead class="thead-primary bg-primary text-white">
<tr>
<th width="3%">Elegir</th>
<th width="7%">Folio</th>
<th width="6%">Parcial</th>
<th width="7%">Fecha</th>
<th width="6%">Subtotal</th>
<th width="6%">IVA</th>
<th width="6%">Ret. Iva</th>
<th width="6%">ISR</th>
<th width="6%">IEPS</th>
<th width="8%">Total</th>
<th width="8%">Abonado</th>
<th width="12%">Pagado</th>
<th width="10%">Saldo</th>
<th width="15%">UUID</th>
</tr></thead>';

  $sql = 'SELECT * FROM facturas
          WHERE f_estatus = "T" AND f_uuid IS NOT NULL
          AND f_cliente = "'.$_GET['cliente'].'" OR f_cliente IN (SELECT DISTINCT(c_id)
          FROM clientes WHERE c_rfc = "'.busca($_GET['cliente'],'clientes','c_id','c_rfc').'")
          AND f_id NOT IN (SELECT DISTINCT(cd_factura) FROM cpagod WHERE cd_cpago = "'.$_GET['cpago'].'")
          ORDER BY f_ftimbro DESC';
  $result = setq($sql) or die($sql);
  $i=0;
  $r=0;
  echo'<tbody>';
  ?>
  <script>
    function permitep(fac){
      if(document.getElementById("combo"+fac).checked){
        document.getElementById("parical"+fac).disabled = false;
        document.getElementById("total"+fac).disabled = false;
        document.getElementById("pagado"+fac).disabled = false;

        var parcial = document.getElementById("parical"+fac).value;
        parcial++;
        var saldo = document.getElementById("saldo"+fac).value;
        var newsaldo = parseFloat(saldo).toFixed(2);

        document.getElementById("parical"+fac).value = parcial;
//        document.getElementById("pagado"+fac).value = newsaldo;
        document.getElementById("pagado"+fac).focus();
        document.getElementById("pagado"+fac).select();

        facts = document.getElementById("facturas").value;
        facts = facts+","+fac;
        document.getElementById("facturas").value = facts;
      }
      else{
        document.getElementById("parical"+fac).disabled = true;
        document.getElementById("total"+fac).disabled = true;
        document.getElementById("pagado"+fac).disabled = true;
      }
    }
    function vsaldo(fac){
      var pagado = document.getElementById("pagado"+fac).value;
      var saldo = document.getElementById("saldo"+fac).value;
      saldo = parseFloat(saldo);
      pagado = parseFloat(pagado);
      if(pagado > saldo){
        var newsaldo = parseFloat(saldo);
        alert("Error: el monto ingresado es mayor al saldo pendiente ");
        document.getElementById("pagado"+fac).value = newsaldo;
        document.getElementById("pagado"+fac).focus();
      }
      var sumfa = [];
      facts = document.getElementById("facturas").value;
      var sumar = facts.split(',');
      var facturs = removeDuplicates(sumar);
      var i;
      var montosum = 0;
      montosum = parseFloat(montosum);
      for(i = 0; i < facturs.length; i++){
        if(facturs[i] != ""){
          var facsum = document.getElementById("pagado"+facturs[i]).value;
          facsum = parseFloat(facsum);
          montosum = montosum+facsum;
        }
      }
      document.getElementById("montoas").value = montosum.toFixed(2);
      document.getElementById("montosinasig").innerHTML = addCommas(montosum.toFixed(2));

    }
    function addCommas(nStr)
    {
      nStr += '';
      x = nStr.split('.');
      x1 = x[0];
      x2 = x.length > 1 ? '.' + x[1] : '';
      var rgx = /(\d+)(\d{3})/;
      while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
      }
      return x1 + x2;
    }
    function removeDuplicates(arr){
      let unique_array = [];
      for(let i = 0;i < arr.length; i++){
          if(unique_array.indexOf(arr[i]) == -1){
              unique_array.push(arr[i])
          }
      }
      return unique_array
    }
  </script>
  <?php
  $r=0;
  while($row = $result->fetch_array()){
    $i++;

    if($row['f_fieps'] == "D") $ieps = $row['f_ieps']; else $ieps = 0;

    $total = $row['f_subtotal']+$row['f_iva']+$ieps-$row['f_isr']-$row['f_retiva'];
    $parcial = busca($row['f_id'],'cpagod INNER JOIN cpago ON c_id = cd_cpago','c_estatus = "T" AND cd_factura','COUNT(*)');
    $abono = busca($row['f_id'],'cpagod INNER JOIN cpago ON c_id = cd_cpago','c_estatus = "T" AND cd_factura','SUM(cd_imppagado)');
    if(!$abono) $abono = 0;
    $saldo = $total-$abono;

    if($saldo > 0.01){
      $r++;
      if(!$row['f_uuid'] || empty($row['f_uuid'])) getuuid($row['f_id']);
      $uuid = $row['f_uuid'];

      if($saldo > 0){

        echo '<tr >
        <td>
            <input type="checkbox" id="combo'.$row['f_id'].'" name="combo'.$row['f_id'].'" value="'.$row['f_id'].'" onChange="permitep('.$row['f_id'].');" />
            <input type="hidden" id="base'.$row['f_id'].'" name="base'.$row['f_id'].'" value="'.round($row['f_subtotal'],2).'" />
            <input type="hidden" id="uuid'.$row['f_id'].'" name="uuid'.$row['f_id'].'" value="'.$uuid.'" />
            <input type="hidden" id="saldo'.$row['f_id'].'" name="saldo'.$row['f_id'].'" value="'.number_format($saldo,2,'.','').'" />
            <input type="hidden" id="importe'.$row['f_id'].'" name="importe'.$row['f_id'].'" value="'.number_format($total,2,'.','').'" />
            <input type="hidden" id="folio'.$row['f_id'].'" name="folio'.$row['f_id'].'" value="'.$row['f_folio'].'" />
            <input type="hidden" id="iva'.$row['f_id'].'" name="iva'.$row['f_id'].'" value="'.round($row['f_iva'],2).'" />
            <input type="hidden" id="ieps'.$row['f_id'].'" name="ieps'.$row['f_id'].'" value="'.round($ieps,2).'" />
            <input type="hidden" id="retiva'.$row['f_id'].'" name="retiva'.$row['f_id'].'" value="'.round($row['f_retiva'],2).'" />
            <input type="hidden" id="isr'.$row['f_id'].'" name="isr'.$row['f_id'].'" value="'.round($row['f_isr'],2).'" />
        </td>
        <td class="text-medium">'.$row['f_folio'].'</td>
        <td class="text-medium"><input type="number" id="parical'.$row['f_id'].'" style="width:40px;" min="1" max="99" step="1" name="parical'.$row['f_id'].'" value="'.$parcial.'" disabled="disabled" /></td>
        <td class="text-medium">'.cambiar_fecha(date('Y-m-d',strtotime($row['f_ftimbro']))).'</td>
        <td class="text-medium">'.number_format($row['f_subtotal'],2).'</td>
        <td class="text-medium">'.number_format($row['f_iva'],2).'</td>
        <td class="text-medium">'.number_format($row['f_retiva'],2).'</td>
        <td class="text-medium">'.number_format($row['f_isr'],2).'</td>
        <td class="text-medium">'.number_format($ieps,2).'</td>
        <td class="text-medium"><input type="number" id="total'.$row['f_id'].'" style="width:100px;"  min="1" max="999999999" step="0.01" name="total'.$row['f_id'].'" value="'.number_format($total,2,'.','').'" disabled="disabled" /></td>
        <td class="text-medium">'.number_format($abono,2).'</td>
        <td class="text-medium"><input type="number" id="pagado'.$row['f_id'].'" style="width:100px;" min="1" max="999999999" step="0.01" name="pagado'.$row['f_id'].'" value="0" disabled="disabled" onchange="vsaldo('.$row['f_id'].');" /></td>
        <td class="text-medium">'.number_format($saldo,2).'</td>
        <td class="text-medium">'.$uuid.'</td>
        </tr>';
      }
    }
  }
  if($i == 0){
    echo '<tr>
            <td colspan="7">No Existen Registros de Facturas timbradas del cliente</td>
          </tr>';
  }
  echo'</tbody></table></form>';

?>