<?php
session_start();
ini_set('display_errors',0);
include_once('../funciones.php');
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolutions_jdceo');
//include('../funciones.php');

$salida = "";
$query = 'SELECT * FROM condicionesc WHERE c_estatus = "A" ORDER BY c_nmb ASC';
$cotizacion = $_POST['cotizacion'];

if(isset($_POST['consulta']) && !empty($_POST['consulta'])){
  $query = 'SELECT   * FROM condicionesc
            WHERE c_estatus = "A" AND c_nmb LIKE "%'.$q.'%" ORDER BY c_nmb ASC';
}
  $resultado = setq($query) or die($query);
  if($resultado->num_rows > 0){
    $salida = '<table class="table table-responsive table-striped table-bordered">
                <thead class="thead-active bg-primary text-white">
                <tr>
                  <th width="10%" class="p-1">Seleccionar</th>
                  <th width="90%" class="p-1">Condición  <a type="button" class="btn btn-warning text-white btn-sm ml-3" id="finalizar" onclick="seleccionarcondiciones();" 
                  style="padding: 0.15rem 0.5rem;"><i class="fas fa-save"></i> Guardar Selección</a></th>
                </tr>
               </thead>
               <tbody>';
    while($row = $resultado->fetch_array()){
      $i++;

      $sqlc = 'SELECT COUNT(*) FROM crm_cotizaciones_condiciones
              WHERE cf_condicion = "'.$row['c_id'].'" AND cf_cotizacion="'.$cotizacion.'"';
      $result = setq($sqlc) or die($sqlc);
//      echo $sqlc.'<<';
      list($numcon) = $result->fetch_array();

      if($numcon > 0)
        $checkb = 'checked';
      else
        $checkb = "";
      

      if($row['c_obligatorio'] == "1"){
        $comp = 'checked=" checked" onclick="return false"';
      } else {
        $comp = "";
      }

      $salida.='<tr>
      <td>
        <input type="checkbox" '.$checkb.' name="con'.$row['c_id'].'" id="ch'.$row['c_id'].'" onchange="setcondicion('.$row['c_id'].');" '.$comp.$comp2.'/>
        <input type="hidden" name="desc'.$row['c_id'].'"  id="desc'.$row['c_id'].'" value="'.$row['c_nmb'].'" />
      </td>
      <td>'.$row['c_nmb'].'</td>
      </tr>';
    }
    $salida.='</tbody></table>';
  }
  else{
    $salida = 'Por favor escriba un indicio de palabra';
  }
  echo $salida;
  //Finaliza tabla dinámica de registros
?>