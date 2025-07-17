<?php
  ini_set('display_errors',0);
  include('../funciones.php');
  session_start();

  $html = "";
  $cliente = $_POST['clt'];
  $fini = date('Y-m-d',strtotime(busca('A','cxcobrar','cx_estatus = "N" OR cx_estatus','MIN(cx_fini)')));
  $ffin = date('Y-m-d',strtotime('next sunday'));
  $sql = 'SELECT cx_id,cx_importe,cx_abonado,cx_referencia,cx_tipo,cx_observaciones,cx_fini FROM cxcobrar WHERE cx_empresa = "'.$_SESSION['emp'].'" 
          AND cx_cliente = "'.$cliente.'" AND cx_estatus IN ("N","A") AND DATE(cx_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'" ORDER BY cx_fini ASC,cx_id';
  $result = setq($sql);
  $numrow = $result->num_rows;
  if($result->num_rows > 0)$html .= '<div class="col-md-12 mb-5"><label>Selecciona las cuentas por cobrar</label></div>
                                    <div class="col-md-12 mb-5">
                                      <button type="button" class="btn bg-grey bg-darken-2 text-white" id="fondear" value="'.$numrow.'" onclick="fondear();">
                                        <i class="icon-calculator2"></i> FONDEAR 
                                      </button>
                                    </div>';
  else $html .= '<div class="col-md-12 mb-5"><label>No hay cuentas por cobrar para el cliente seleccionado</label></div>';
  while($row = $result->fetch_array()){
    if($row['cx_observaciones'] != "") $desc = $row['cx_observaciones'];
    else $desc = busca($row['cx_referencia'],'remisiones','r_id','r_nmb');
    $monto = $row['cx_importe'] - $row['cx_abonado'];
    $j++;
    $html .= '
    <input type="hidden" id="monto'.$j.'" value="'.$monto.'" readonly/>
    <div class="col-6 col-md-1">
      <div class="mb-5">
        <input type="checkbox" id="'.$j.'" name="chcxc" value="'.$row['cx_id'].'" onchange="sum('.$j.');"/>
      </div>
    </div>
    <div class="col-6 col-md-5">
      <div class="mb-5">
        <input type="text" id="obs'.$j.'" value="'.$desc.'" class="form-control" readonly/>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="mb-5">
        <input type="text" id="fecha'.$j.'" value="'.date('Y-m-d', strtotime($row['cx_fini'])).'" class="form-control" readonly/>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="mb-5">
        <input type="number" id="nsaldo'.$j.'" min="0" max="'.$monto.'" step="0.01" value="'.$monto.'" class="form-control" onchange="sum('.$j.');" disabled="true"/>
      </div>
    </div>
    ';
  }

  echo $html;
?>