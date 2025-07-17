<?php
  ini_set('display_errors',0);
  include('../funciones.php');
  session_start();

  $html = "";
  $proveedor = $_POST['prv'];
  $i = 0;
  $lastdt = date('Y-m-d',strtotime(busca('A','cxpagar','cp_estatus = "N" OR cp_estatus','MIN(cp_fini)')));
  $fini = date('Y-m-d',strtotime(min(date('Y-m-d'),$lastdt)));
  $ffin = date('Y-m-d',strtotime('next sunday'));
  $sql = 'SELECT cp_id,cp_importe,cp_abonado,cp_observaciones,cp_fini FROM cxpagar WHERE cp_empresa = "'.$_SESSION['emp'].'" 
  AND cp_proveedor = "'.$proveedor.'" AND cp_estatus IN ("N","A") AND DATE(cp_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'"  ORDER BY cp_fini ASC,cp_id';
  $result = setq($sql) or die($sql);
  $numrow = $result->num_rows;
  if($result->num_rows > 0) $html .= '<div class="col-md-12 mb-5"><label>Selecciona las cuentas por pagar</label>
                              <div class="col-md-12 mb-5">
                                <button type="button" class="btn btn-secondary" id="fondear" value="'.$numrow.'" onclick="fondear();">
                                  <i class="fas fa-calculator"></i> FONDEAR 
                                </button>
                              </div>';
  else $html .= '<div class="col-md-12 mb-5"><label>No hay cuentas por pagar para el proveedor seleccionado</label></div>';
  while($row = $result->fetch_array()){
    $monto = $row['cp_importe'] - $row['cp_abonado'];
    $i++;
    $html .= '
    <input type="hidden" id="monto'.$i.'" value="'.$monto.'" readonly/>
    <div class="row">
      <div class="col-md-1">
        <div class="mb-5">
          <input type="checkbox" id="'.$i.'" name="chcxp" value="'.$row['cp_id'].'" onchange="sum('.$i.');"/>
        </div>
      </div>
      <div class="col-6 col-md-5">
        <div class="mb-5">
          <input type="text" id="obs'.$i.'" value="'.$row['cp_observaciones'].'" class="form-control" readonly/>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="mb-5">
          <input type="text" id="fecha'.$i.'" value="'.date('Y-m-d', strtotime($row['cp_fini'])).'" class="form-control" readonly/>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="mb-5">
          <input type="number" id="nsaldo'.$i.'" min="0" max="'.$monto.'" step="0.01" value="'.$monto.'" class="form-control" onchange="sum('.$i.');" disabled="true"/>
        </div>
      </div>
    </div>
    ';
  }

  echo $html;
?>