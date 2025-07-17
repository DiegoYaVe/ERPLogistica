<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$saldo = $_GET['id'];
$date = date('Y-m-d',strtotime('+ 30 days'));
$read = '';
$sql = 'SELECT * FROM penalizaciones INNER JOIN remisiones ON r_id = p_remision INNER JOIN crm_clientes ON p_cliente = c_id WHERE p_id = "'.$saldo.'"';
$result = setq($sql);
$row = $result->fetch_array();
$asesor = busca($row['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
if($row['p_estatus'] != "N") $read = 'readonly';
$monto = $row['p_monto']+$row['p_saldoempresa'];
$saldoempresa = $row['p_saldoempresa'];

if($row['p_fcaduca'] != '0000-00-00 00:00:00') $date = date('Y-m-d',strtotime($row['p_fcaduca']));


echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">Saldo a favor del cliente</div>
      <form method="post" action="index?modulo=saldos&accion=setsaldo&id='.$saldo.'" onsubmit="checksubmit();">
        <input type="hidden" value="'.$saldo.'" name="id" />
        <div class="row">
          <div class="col-12 mb-5 col-md-4">
            <label>Folio de la remisión:</label>
            <input type="text" id="folio" class="form-control" placeholder="Folio de la remisión" name="remision" value="'.$row['r_folio'].'" readonly="readonly" required="required" >
          </div>
          <div class="col-12 mb-5 col-md-4">
            <label>Nombre del cliente:</label>
            <input type="text" id="cliente" class="form-control" placeholder="Nombre del cliente" name="cliente" value="'.$row['c_nmb'].' '.$row['c_apellidos'].'" readonly="readonly" required="required" >
          </div>
          <div class="col-12 mb-5 col-md-4">
            <label>Asesor:</label>
            <input type="text" id="asesor" class="form-control" placeholder="Nombre del asesor" name="asesor" value="'.$asesor.'" readonly="readonly" required="required" >
          </div> 
          <div class="col-12 mb-5 col-md-4">
            <label>Saldo a favor del cliente:</label>
            <input type="number" id="monto"  onblur="updsaldoemp()" class="form-control" placeholder="Saldo a favor del cliente" name="monto" value="'.$row['p_monto'].'" min="0" max="'.$monto.'" required="required" '.$read.'>
          </div>
          <div class="col-12 mb-5 col-md-4">
            <label>Saldo para la empresa:</label>
            <input type="hidden" value="'.$monto.'" id="importereal" name="importereal">
            <input type="number" name="saldoempresa" id="saldoempresa" class="form-control" placeholder="Saldo a favor del cliente" name="monto" value="0" min="0" max="'.$monto.'" required="required" readonly>
          </div>
          <div class="col-12 mb-5 col-md-4">
            <label>Fecha de vencimiento de saldo:</label>
            <input type="date" id="vencimiento" class="form-control" placeholder="Fecha de vencimiento del saldo" name="vencimiento" value="'.$date.'" min="'.date('Y-m-d').'" required="required" '.$read.'>
          </div>';
          if($row['p_estatus'] == "N")
          echo '<center>
            <div class="mb-5 col-md-12">
              <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i> Guardar
            </div>
          </center>
        </div>
      </form>
    </div>';

?>

<script>
  function checksubmit() {
    document.getElementById("guardar").value = "Guardando";
    document.getElementById("agregar").disabled = true;
    return true;
  }
  function updsaldoemp(){
    var monto = document.getElementById("monto");
    var importereal = document.getElementById("importereal");
    var saldoempresa = document.getElementById("saldoempresa");

    var total = parseFloat(importereal.value) - parseFloat(monto.value);
    saldoempresa.value= parseFloat(total);
  }
  updsaldoemp();
</script>