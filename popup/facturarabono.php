<?php
include_once('../funciones.php');
ini_set('display_errors', 1);

$remision = $_GET['remision'];
$folio = busca($remision,'remisiones','r_id','r_folio');
$sql = 'SELECT cx_id, cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$remision.'"';
$result = setq($sql);
list($idcxc, $importe, $abonado, $estcxc) = $result -> fetch_array();
$facturado = busca($remision, 'factura_abonos', 'fa_remision', 'SUM(fa_monto)');
if($facturado > 0 && $facturado < $importe) $nmb = "Facturar el restante de la remisión";
else if(floatval($facturado) == 0 && $facturado < $importe) $nmb = "Facturar la remisión completa";

?>
 <div class="container col-10">
  <div class="alert alert-primary">ABONOS APROBADOS DE LA REMISÍON <?php echo $folio ?> </div>
  <?php
    if($facturado < $importe){
  ?>
  <div class="d-flex justify-content-end">
    <button class="btn btn-success" onclick="validafac(<?php echo $remision; ?>, '','T', '');"><i class="fas fa-file-invoice"></i> <?php echo $nmb; ?></button>
  </div>
  <?php } ?>
  <center>
    <table class="table table-responsive col-10 mt-4">
      <thead class="bg-primary text-white">
        <td>Forma de pago</td>
        <td>Cuenta</td>
        <td>Fecha del ingreso</td>
        <td>Registró</td>
        <td>Monto</td>
        <td>Facturar</td>
      </thead>
      <tbody>
        <?php
        $sql = 'SELECT * FROM ingreso_cxcobrar INNER JOIN ingresos ON ic_ingreso = i_id
        WHERE ic_cxcobrar = "'.$idcxc.'" AND i_estatus = "F" ORDER BY ic_fapli DESC';
        $resultab = setq($sql);
        while($row = $resultab->fetch_array()){
          $cuenta = busca($row['ic_ingreso'],"ingresos INNER JOIN cuentas ON i_cuenta = cu_id","i_id","cu_nmb");
          $fpago = busca($row['i_fpago'], 'cfdi_fpago', 'cf_id', 'cf_descripcion');
          echo'<tr>
            <td>'.$fpago.'</td>
            <td>'.$cuenta.'</td>
            <td>'.$row['ic_fapli'].'</td>
            <td class="">'.$row['ic_uapli'].'</td>
            <td class="number-align">$'.number_format($row['ic_monto'],2 ).'</td>';
            $check = busca($row['i_id'], 'factura_abonos', 'fa_remision = "'.$remision.'" AND fa_tipo = "I" AND fa_identrada', 'fa_factura');
            /* echo $row['i_id'].'<br>'; */
            if($check){
              echo '<td>
                <a target="_blank" href="?modulo=facturas&accion=showfactura&factura='.$check.'">
                  <button class="btn btn-warning"> <i class="fas fa-sign-out-alt" style="color:#ffffff"></i> Ir a la factura</button>
                </a>
              </td>';  
            } else {
              echo '<td>
                
                  <button class="btn text-white" style="background:teal" onclick="validafac('.$remision.', '.$row['i_id'].' , \'A \', \'I \');"> <i class="fas fa-file-invoice" style="color:#fff"></i> Facturar</button>
                </a>
              </td>';
            }
          '</tr>';
        }
        $sql = 'SELECT * FROM cajasd WHERE cd_remision = "'.$remision.'" AND cd_estatus = "A" ORDER BY cd_fconfirma DESC';
        $resultab2 = setq($sql);
        while($row = $resultab2->fetch_array()){
          /* if(!empty($row['i_adjunto']) && $row['i_adjunto']) $adjuntos++;
          $cuenta = busca($row['ic_ingreso'],"ingresos INNER JOIN cuentas ON i_cuenta = cu_id","i_id","cu_nmb"); */
          $fpago = busca($row['cd_fpago'], 'cfdi_fpago', 'cf_id', 'cf_descripcion');
          echo'<tr>
            <td>'.$fpago.'</td>
            <td> Ingreso a caja</td>
            <td>'.$row['cd_fconfirma'].'</td>
            <td class="">'.$row['cd_ugen'].'</td>
            <td class="number-align">$'.number_format($row['cd_total'],2).'</td>';
            $check = busca($row['cd_id'], 'factura_abonos', 'fa_remision = "'.$remision.'" AND fa_tipo = "C" AND fa_identrada', 'fa_factura');
            if($check){
              echo '<td>
                <a target="_blank" href="?modulo=facturas&accion=showfactura&factura='.$check.'">
                  <button class="btn btn-warning"> <i class="fas fa-sign-out-alt" style="color:#ffffff"></i> Ir a la factura</button>
                </a>
              </td>';  
            } else {
              echo '<td>
                  <button class="btn text-white" style="background:teal" onclick="validafac('.$remision.','.$row['cd_id'].', \'A \', \'C \');"> <i class="fas fa-file-invoice" style="color:#fff"></i> Facturar</button>
                </a>
              </td>';
            }
          echo '</tr>';
        }
        if($resultab->num_rows == 0 && $resultab2->num_rows == 0 ){
          echo '<tr style="background-color:#c0c8d1">
            <th colspan="7" style="text-align:center">Sin pagos por facturar</th>
          </tr>';
        }
        ?>
      </tbody>
    </table>
  </center>
 </div>

<script>
  function validafac(id,ida, tipo, entrada){
    if(tipo == "T"){
      var conf = confirm("¿Deseas facturar la remisión?");
    }else {
      var conf = confirm("¿Deseas facturar este abono?");
    }    
    if(conf == true){
      window.location.href="?modulo=facturas&accion=facturarem&remision="+id+"&tipo="+tipo+'&entrada='+entrada+'&identrada='+ida;
    }
  }

</script>