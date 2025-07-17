<?php
  session_start();
  ini_set('display_errors',1);
  include_once('../funciones.php');
  $idr = $_GET['remision'];
  if($idr){
    include_once("../modulos/remisiones.php");
    $rem = new modelremisiones();
    $rem->select($idr);
    $fiscales = busca($rem->cliente,'crm_fiscales','cf_predeterminada = "1" AND cf_cliente','cf_id');
  }

  $listestatus = array("N" => "Proceso de Captura", "P" => "Lista para ser Timbrada", "T" => "Timbrada", "C" => "Cancelada", "Q" => "Solicitud de Cancelación");
  $colestatus = array("N" => "#FFCC33", "P" => "#FF9933", "T" => "#00CC66", "C" => "#990000", "Q" => "#3399FF");

  echo '<script>
    function rfcfis(){
      var fiscales = document.getElementById("fiscales").value;
      var rfc = document.getElementById("rfc"+fiscales).value;
      document.getElementById("rfc").value = rfc;
    }
  </script>';
  echo '<div class="container col-10">  
    <div class="col-12 alert alert-primary">Facturas anteriores</div>
    <div class="row col-md-12">
      <div class="table">
        <center>
          <table class="mb-0 table table-striped">
            <thead class="bg-primary text-white">
              <tr>
                <th>Folio</th>
                <th>Razón social</th>
                <th>Importe</th>
                <th>Estatus</th>
                <th></th>';
              echo'</tr>
            </thead>
            <tbody>';
              $sqlfact = 'SELECT DISTINCT(fr_factura) FROM factura_remision WHERE fr_remision = "'.$rem->id.'"';
              $resultfact = setq($sqlfact);
              $total = 0;
              while($row = $resultfact->fetch_array()){
                $sqldatos = 'SELECT f_fiscales,f_folio,f_subtotal,f_iva,f_isr,f_retiva,f_estatus,f_id FROM facturas WHERE f_id = "'.$row['fr_factura'].'"';
                $resultdatos = setq($sqldatos);
                list($ffiscales,$ffolio,$fsubtotal,$fiva,$fisr,$fretiva,$estatus,$idfactura) = $resultdatos->fetch_array();
                $importe = $fsubtotal+$fiva-$fisr-$fretiva;
                $total += $importe;
                echo '<tr>
                  <td width="20%">'.$ffolio.'</td>
                  <td>'.busca($ffiscales,'crm_fiscales','cf_id','cf_razonsocial').'</td>
                  <td width="15%" class="number-align">$ '.number_format($importe,2).'</td>
                  <td width="12%" style="background:'.$colestatus[$estatus].';">'.$listestatus[$estatus].'</td>
                  <td width="10%">
                    <a href="?modulo=facturas&accion=showfactura&factura='.$idfactura.'" target="_BLANK">
                      <button class="btn-sm btn-info"><i class="fas fa-eye" style="color: #ffffff;"></i></button>
                    </a>
                  </td>
                </tr>';
              }
              echo '<tr>
                <th style="text-align:right" colspan="2">Total</th>
                <td class="number-align">$ '.number_format($total,2).'</td>
              </tr>';
            echo'</tbody>
          </table>
        </center>
      </div>
    </div>';
    //if(!isset($_GET['or'])){
    echo'<div class="col-md-12 alert alert-primary">Insertar nueva factura de remisión</div>
    <form method="post" action="?modulo=facturas&accion=insertfacremcomplemento">
      <input type="hidden" name="cliente" value="'.$rem->cliente.'" />
      <input type="hidden" name="remision" value="'.$rem->id.'" />
      <div class="mb-5 col-md-4">
        <label>Cliente:</label>
        <input type="text" class="form-control focus mayus" placeholder="Nombre de la categoria" value="'.busca($rem->cliente,'crm_clientes','c_id','c_nmb').'" disabled readonly>
      </div>
      <div class="mb-5 col-md-4">  
        <label>Razón social</label>
        <select class="form-control" name="fiscales" id="fiscales" required onchange="rfcfis();">';
          $sql = 'SELECT * FROM crm_fiscales WHERE cf_cliente = "'.$rem->cliente.'"';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if($fiscales == $row['cf_id']) $sel = 'selected';
            echo '<option value="'.$row['cf_id'].'" '.$sel.'>'.$row['cf_razonsocial'].'</option>';
          }
        echo'</select>
      </div>';
      $sql = 'SELECT * FROM crm_fiscales WHERE cf_cliente = "'.$rem->cliente.'"';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        echo '<input type="hidden" id="rfc'.$row['cf_id'].'" value="'.$row['cf_rfc'].'"/>';
      }
      echo'<div class="mb-5 col-md-4">  
        <label>RFC</label>';
        $sql = 'SELECT * FROM crm_fiscales WHERE cf_cliente = "'.$rem->cliente.'"';
        $result = setq($sql);
        while($row = $result->fetch_array()){
          if($fiscales == $row['cf_id']){
            echo '<input type="text" disbaled readonly value="'.$row['cf_rfc'].'" class="form-control" id="rfc">';
          }
        }
      echo'</div>';
        echo'<div class="mb-5 col-md-12">
          <center>
            <button type="submit" class="btn btn-primary" >
              <i class="fa fa-save"></i> Guardar
            </button>
          </center>
        </div>';
      //}
    echo'</form>
  </div>';
?>