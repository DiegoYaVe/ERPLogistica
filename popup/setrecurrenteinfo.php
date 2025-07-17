<?php
  session_start();
  ini_set('display_errors',1);
  include_once('../funciones.php');
  include_once('../modulos/cxcobrar.php');
  
  if(isset($_GET['tablero'])) $tablero = $_GET['tablero'];
  if(isset($_GET['remision'])) $remision = $_GET['remision'];

  if($remision){
    $esttablero = busca($tablero,'crm_tableros','ct_id','ct_estatus');
    if($esttablero != "A"){
      $btn = "1";
      $accion = '';
    }else{
      $btn = "0";
      $accion = '?modulo=tableros&accion=endtablero&rem=1&estatus=F&id='.$tablero;
    }
  }

  $estatusp = array("N"=>"NUEVA","F"=>"FINALIZADA","C"=>"CANCELADA","A"=>"ABONADA","T"=>"TODOS");
  $nmbrecurrencia = array("1"=>"SEMANAL","2"=>"QUINCENAL","3"=>"MENSUAL","4"=>"TRIMESTRAL","5"=>"SEMESTRAL","6"=>"ANUAL");

  echo'<script>
    function checkend(){
      document.getElementById("guardar").value = "Finalizando";
      document.getElementById("guardar").disabled = true;
      document.endtab.submit();
    }
  </script>';

  echo '<div class="container">  
    <div class="col-12 alert alert-primary">Información de remisión recurrente</div>
    <form method="post" action="'.$accion.'" name="endtab">
      <input type="hidden" name="remision" value="'.$remision.'" />
      <div class="mb-5 col-md-6">
        <label>Remisión recurrente:</label>
        <input type="text" class="form-control focus mayus" placeholder="Nombre de la categoria" value="'.busca($tablero,'crm_tablerosrecurrentes','ctr_remision = "'.$remision.'" AND ctr_tablero','ctr_nmb').'" disabled readonly>
      </div>
      <div class="mb-5 col-md-4">  
        <label>Recurrencia</label>
        <input type="text" class="form-control focus mayus" placeholder="Recurrencia de aparición" value="'.$nmbrecurrencia[busca($tablero,'crm_tablerosrecurrentes','ctr_remision = "'.$remision.'" AND ctr_tablero','ctr_recurrencia')].'" disabled readonly>
      </div>';
    echo'</form>';
    echo'<div class="col-md-12 alert alert-primary">Cuentas por cobrar de remisión recurrente</div>';
    echo'<div class="row col-md-12">
      <div class="table">
        <center>
          <table class="mb-0 table table-hover table-striped">
            <thead class="bg-light-blue bg-darken-2 text-white">
              <tr>
                <th>No</th>
                <th>Fecha</th>
                <th>Importe</th>
                <th>Abonado</th>
                <th>Estatus</th>
                <th></th>';
              echo'</tr>
            </thead>
            <tbody>';
              $count = busca($tablero,'crm_tablerosrecurrentes','ctr_tablero','COUNT(*)');
              $sqlrecurrente = 'SELECT * FROM crm_tablerosrecurrentes WHERE ctr_tablero = "'.$tablero.'"';
              if($count > 1) $sqlrecurrente .= ' AND ctr_estatus = "F"';
              $resultrecurrente = setq($sqlrecurrente);
              $ncxc = 1;
              while($rowrecu = $resultrecurrente->fetch_array()){
                $sqlcxc = 'SELECT cx_estatus,cx_importe,cx_fini,cx_abonado,cx_id 
                          FROM cxcobrar WHERE cx_referencia = "'.$rowrecu['ctr_remision'].'" AND cx_tipo = "R"';
                $resultcxc = setq($sqlcxc);
                list($estatuscxc,$impcxc,$finicxc,$abonadocxc,$idcxc) = $resultcxc->fetch_array();
                
                if($estatuscxc == "N") $color="style='background-color:#FF5C5C;color:white;'";
                else if($estatuscxc == "A") $color="style='background-color:#FFE447'";
                else if($estatuscxc == "F") $color="style='background-color:#47B6FF'";
                else if($estatuscxc == "C") $color="style='background-color:#000000; color:white;'";
                echo '<tr>
                  <td width="15%">'.$ncxc.'</td>
                  <td>'.date("d-m-Y", strtotime($finicxc)).'</td>
                  <td class="number-align">$'.number_format($impcxc,2).'</td>
                  <td class="number-align">$'.number_format($abonadocxc,2).'</td>
                  <td '.$color.'>'.$estatusp[$estatuscxc].'</td>
                  <td width="10%">
                    <a href="?modulo=cxcobrar&accion=show&idcxc='.$idcxc.'" target="_BLANK">
                      <button class="btn btn-info"><i class="icon-eye4"></i></button>
                    </a>
                  </td>
                </tr>';
                $ncxc++;
              }
            echo'</tbody>
          </table>
        </center>';
        if($btn == "0"){
          echo'<center>
            <button class="btn btn-danger mt-2" type="button" id="guardar" onclick="checkend();">
              <i class="fa fa-times"></i> Finalizar recurrencia
            </button>
          </center>';
        }
      echo'</div>
    </div>
  </div>';
?>