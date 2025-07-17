<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');

$remision = $_GET['remision'];
include_once('../modulos/remisiones.php');
$remm = new modelremisiones();

include_once('../modulos/cxcobrar.php');
$cxc = new modelcxcobrar();

$remm -> select($remision);
$estatusp = array("N"=>"NUEVA","F"=>"LIQUIDADA","C"=>"CANCELADA","A"=>"ABONADA");
$estatusa = array("P"=>"POR APROBAR","F"=>"APROBADO", "C"=> "CANCELADO","A"=>"APROBADO");
$sql = 'SELECT cx_id, cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$remision.'" AND cx_tipo = "R"';
$result = setq($sql);
list($idcxc, $importe, $abonado, $estcxc) = $result -> fetch_array();
$sqlb = 'SELECT SUM(i_monto) FROM ingresos WHERE i_id IN (SELECT ic_ingreso FROM ingreso_cxcobrar WHERE ic_cxcobrar ="'.$idcxc.'") AND i_estatus="P"';
$resultb = setq($sqlb);
list($pend) = $resultb -> fetch_array();
$sqlb = 'SELECT SUM(cd_total) FROM cajasd WHERE cd_remision = "'.$remision.'" AND cd_estatus="P"';
$resultb = setq($sqlb);
list($pendcd) = $resultb -> fetch_array();
$rest = $importe - $abonado - $pend - $pendcd;
if($rest < 0) $rest = 0;

if($estcxc == "F" || $rest == 0){
  $form = 'hidden';
  $hist = "";
} else {
  $form = "";
  $hist = 'hidden';
}

?>

<script>
  function cambiar(num){
    var tohist = document.getElementById('formhis');
    var toform = document.getElementById('hisform');
    var hist = document.getElementById('hist');
    var form = document.getElementById('form');

    if(num == 0){
      form.hidden = true;
      hist.hidden = false;
      toform.hidden = false;
      tohist.hidden = true;
    } else {
      form.hidden = false;
      hist.hidden = true;
      toform.hidden = true;
      tohist.hidden = false;
    }
  }
  
  function ingresoacaja(){
    var fpago =document.getElementById("formapago").value;
    var adj = document.getElementById("adj");
    var adjunto = document.getElementById("adjunto");
    var ingreso = document.getElementById("ingresocaja");
    var cuentas = document.getElementById("cta");
    //console.log('fpago: ', fpago);
    if(ingreso.checked){
      cuentas.hidden = true;
    }else{
      cuentas.hidden = false;
    }
    if(ingreso.checked){
      adj.hidden = true;
      adjunto.removeAttribute("required");
    }
    else{
      adj.hidden = false;
      adjunto.setAttribute("required", "required");
    }
  } 

  function checksubmit() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
  }
  function saldomaximo(){
    var saldos = document.getElementById("cxsaldos");
    var importe = document.getElementById("importe");

    var indice = saldos.selectedIndex;
    var saldosel = saldos.options[indice].dataset.info;
    importe.setAttribute('max', saldosel);

    importe.value = saldosel;
  }

  function aplicarsaldo(){
    var fpago =document.getElementById("formapagoing").value;
    var adj = document.getElementById("adj");
    var adjunto = document.getElementById("adjunto");
    var ingreso = document.getElementById("ingresocaja");
    var cuentas = document.getElementById("cta");
    var importediv = document.getElementById("importediv");
    var fpagodiv = document.getElementById("fpagodiv");
    var obscta = document.getElementById("obscta");
    var fechadiv = document.getElementById("fechadiv");
    var obsdiv = document.getElementById("obsdiv");
    var saldos = document.getElementById("saldos");
    var comdiv = document.getElementById("comdiv");
    var maximp = document.getElementById("maximp");
    if(fpago == "23"){
      cuentas.hidden = true;
      saldos.hidden = false;
      comdiv.hidden = true;
      adj.hidden = true;
      obscta.hidden = true;
      adjunto.removeAttribute("required");
      adjunto.removeAttribute("required");
      saldomaximo();
    } else {
      cuentas.hidden = false;
      saldos.hidden = true;
      comdiv.hidden = false;
      adj.hidden = false;
      obscta.hidden = false;
      importe.removeAttribute('max');
      adjunto.setAttribute("required", "required");
      adjunto.setAttribute("required", "required");
      importe.value= maximp.value;
    }
  } 

</script>

<?php

echo '
  <div class="container col-10">  
    <div class="col-12">
      <div class="row">
        <div class="text-xs-center col-md-3" >
          <div class="mb-5">
            <center>
              <div class="alert alert-info"> <b>Estatus de la cuenta: </b> '.$estatusp[$estcxc].'</div>
            </center>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-5">
            <center>';
              $cliente = busca($remm->cliente,'crm_clientes','c_id','c_alias');
              echo '<div class="col-md-12 alert alert-success text-xs-center">Cliente: <b>'.$cliente.'</b></div>';
              echo'
            </center>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-5">
            <center>
              <input type="hidden" name="maximp" value="'.$rest.'" id="maximp" class="form-control"/>
              <div class="col-md-12 alert alert-danger text-xs-center">Importe faltante: <b>$'.number_format($rest,2).'</b></div>
            </center>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-5">
            <center>
              <div class="col-md-12 alert alert-warning text-xs-center">Importe total: <b>$'.number_format($importe,2).'</b></div>
            </center>
          </div>
        </div>        
      </div>
    </div>
    <div class=" container">
      <div class="d-flex justify-content-end">
          <button id="formhis" class="btn btn-secondary" onclick="cambiar(0);" '.$form.'> <i class="fas fa-history"></i> Historial de Pagos</button>
          <button id="hisform" class="btn btn-primary" onclick="cambiar(1);" '.$form.$hist.'><i class="fas fa-comment-dollar"></i> Insertar cobro</button>
      </div>
    </div>    
    <div class="row">
      <div class="col-md-12 col-sm-12 mt-4" id="form" '.$form.'>
        <div class="alert alert-primary"> Insertar nuevo ingreso en la cuenta
        </div>
        <form action="?modulo=remisiones&accion=abonaremision" method="post" enctype="multipart/form-data" onsubmit="checksubmit();">
          <input id="cxc" name="idcxc" type="hidden" value="'.$idcxc.'">
          <input id="remision" name="remision" type="hidden" value="'.$remision.'">  
          <div class="row">
            <div class="col-12 col-md-2">
              <div class="mb-5">
                <label for"nmbac">Importe del ingreso</label>
                <input type="number" min="1" step="0.01" name="importe" value="'.round($rest, 2).'" id="importe" class="form-control" placeholder="Importe del ingreso" required="required" onfocus="this.select();" />
              </div>
            </div>
            <div class="col-12 col-md-2" id="comdiv">
              <div class="mb-5">
                <label for"nmbac">Comisión del ingreso</label>
                <input type="number" min="0" step="0.01" name="comision" value="0" id="comision" class="form-control" placeholder="Comisión del ingreso" required="required" onfocus="this.select();" />
              </div>
            </div>
            <div class="col-6 col-md-4" id="fpagodiv">
              <div class="mb-5">
                <label for"correo">Forma de pago</label>';
                $sqlsaldo = 'SELECT * FROM cxcobrar WHERE cx_cliente = "'.$remm->cliente.'" AND cx_tipo = "S"'; 
                $resultsal = setq($sqlsaldo);
                $sql = 'SELECT * FROM cfdi_fpago WHERE cf_referencia = "1" ORDER BY cf_nmb ASC';
                $result = setq($sql);
                echo '<select name="formapagoing" id="formapagoing" class="form-control" onchange="aplicarsaldo();">';
                  while($row = $result->fetch_array()){
                    if($row['cf_nmb'] != "98"){
                      echo '<option value="'.$row['cf_id'].'">'.$row['cf_descripcion'].'</option>';
                    } else if ($row['cf_nmb'] == "98" && $resultsal ->num_rows > 0){
                      echo '<option value="'.$row['cf_id'].'">'.$row['cf_descripcion'].'</option>';
                    }                    
                  }
                echo '</select>'; 
              echo '</div>
            </div>
            <div class="col-12 col-md-4" id="cta">
              <div class="mb-5">
                <label for"fini">Cuenta</label>';
                $sql = 'SELECT cu_id,cu_nmb FROM cuentas INNER JOIN cortes ON cu_id = c_cuenta
                        WHERE c_estatus = "A" ORDER BY cu_orden';
                $result = setq($sql);
                echo '<select name="cuenta" id="cuenta" class="form-control" required >';
                  while($row = $result->fetch_array()){
                    
                    if($row['cu_id'] != "1")
                    echo '<option value="'.$row['cu_id'].'" '.$selcu.'>'.$row['cu_nmb'].'</option>';
                  }
                echo '</select>';
              echo '</div>
            </div>
            <div class="col-12 col-md-4" id="saldos" hidden>
              <div class="mb-5">
                <label for"fini">Saldos</label>';
                $sqlsaldo = 'SELECT * FROM cxcobrar WHERE cx_cliente = "'.$remm->cliente.'" AND cx_tipo = "S" AND cx_estatus != "C" AND cx_estatus != "F"'; 
                $resultsal = setq($sqlsaldo);
                echo '<select name="cxsaldos" id="cxsaldos" class="form-control" onchange="saldomaximo();">';
                while($rowsal = $resultsal -> fetch_array() ){
                  $saldo = $rowsal['cx_importe']-$rowsal['cx_abonado'];
                  $vence = busca($rowsal['cx_referencia'], 'penalizaciones', 'p_remision', 'p_fcaduca');
                  echo '<option value="'.$rowsal['cx_id'].'" data-info="'.$saldo.'">$'.number_format($saldo, 2).' Vence: '.$vence.'</option>';
                }
                echo '</select>';
              echo '</div>
            </div>
            <div class="col-6 col-md-4" id="obscta">
              <div class="mb-5">
                <label for"correo">Referencia</label>
                <input type="text" name="referencia" value="" id="referencia" class="form-control" placeholder="REFERENCIA DEL DEPOSITO" onfocus="this.select();" />
              </div>
            </div>
            <div class="col-3 col-md-4" id="fechadiv">
              <div class="mb-5">
                <label for"correo">Fecha del ingreso</label>
                <input type="datetime-local" name="fecha" value="'.date('Y-m-d H:i:s').'" id="referencia" class="form-control" onfocus="this.select();" required />
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="mb-5">
                <label for"correo">Aplicar a Cuenta por cobrar</label>
                <input type="text" value="'.$remm->folio.'" class="form-control" disabled />
              </div>
            </div>
            <div class="col-12 col-md-6" id="obsdiv">
              <div class="mb-5">
                <label for"correo">Observaciones</label>
                <input type="text" name="observaciones" id="observaciones" value="INGRESO A REMISIÓN '.$remm->folio.'" class="form-control" placeholder="Observaciones" onfocus="this.select();"/>
              </div>
            </div>
            ';
            echo '<div class="col-12 col-md-6" id="adj">
              <div class="mb-5">
                <label for"correo">Adjuntar comprobante</label>
                <input type="file" name="adjunto" id="adjunto" class="form-control" placeholder="ADJUNTAR COMPROBANTE" required/>
              </div>
            </div>';
            echo '<div class="col-12 col-md-12">
              <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fas fa-university" style="color: #ffffff;"></i> GUARDAR </button></center>
            </div>
          </div>
        </form>  
      </div>
      <div class="col-md-12 col-sm-12 mt-4" id="hist" '.$hist.'>
        <div class="alert alert-primary"> Historial de abonos a la cuenta
        </div>
        <center>
          <table class="table table-striped table-hover table-bordered" style="width:100%">
            <thead>
              <tr style="background-color:#1c3249;color:white;">
                <th>Fecha</th>
                <th>Cuenta</th>
                <th>Monto</th>
                <th>Estatus</th>
                <th>Registró</th>
              </tr>
            </thead>
            <tbody>';
              $adjuntos = 0;
              $totabonos = 0;
              $sql = 'SELECT * FROM ingreso_cxcobrar INNER JOIN ingresos ON ic_ingreso = i_id
                      WHERE ic_cxcobrar = "'.$idcxc.'" ORDER BY ic_fapli DESC';
              $resultab = setq($sql);
              while($row = $resultab->fetch_array()){
                if(!empty($row['i_adjunto']) && $row['i_adjunto']) $adjuntos++;
                $cuenta = busca($row['ic_ingreso'],"ingresos INNER JOIN cuentas ON i_cuenta = cu_id","i_id","cu_nmb");
                if($row['i_estatus']!= "C")
                $totabonos += $row['ic_monto'];
                echo'<tr style="background-color:#c0c8d1">
                  <td>'.$row['ic_fapli'].'</td>
                  <td>'.$cuenta.'</td>
                  <td class="number-align">$'.number_format($row['ic_monto'],2 ).'</td>
                  <td class="">'.$estatusa[$row['i_estatus']].'</td>
                  <td class="">'.$row['ic_uapli'].'</td>
                </tr>';
              }
              $sql = 'SELECT * FROM cajasd WHERE cd_remision = "'.$remm->id.'" AND cd_estatus != "C"';
              $resultab2 = setq($sql);
              while($row = $resultab2->fetch_array()){
                /* if(!empty($row['i_adjunto']) && $row['i_adjunto']) $adjuntos++;
                $cuenta = busca($row['ic_ingreso'],"ingresos INNER JOIN cuentas ON i_cuenta = cu_id","i_id","cu_nmb"); */
                $totabonos += $row['cd_total'];
                echo'<tr style="background-color:#c0c8d1">
                  <td>'.$row['cd_fgen'].'</td>
                  <td> Ingreso a caja</td>
                  <td class="number-align">$'.number_format($row['cd_total'],2 ).'</td>
                  <td class="">'.$estatusa[$row['cd_estatus']].'</td>
                  <td class="">'.$row['cd_ugen'].'</td>
                </tr>';
              }
              if($resultab->num_rows == 0 && $resultab2->num_rows == 0 ){
                echo '<tr style="background-color:#c0c8d1">
                  <th colspan="5" style="text-align:center">Sin registro de cobros</th>
                </tr>';
              }else{
                echo '<tr class="p-1 bg-grey bg-lighten-3">
                  <td >&nbsp;</td>
                  <td >Total</td>
                  <td class="number-align">$ '.number_format($totabonos,2).'</td>
                </tr>';
              }
            echo'</tbody>';
          echo'</table>
        </center>
      </div>
    </div>
  </div>';

