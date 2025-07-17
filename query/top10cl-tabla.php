<?php
include('../funciones.php'); 
$idcl = $_POST['idcl'];

$fechainicio = date('Y-m-d', strtotime($_POST['finicio']));
$fechafinal = date('Y-m-d', strtotime($_POST['ffinal']));
//$fechainicio = date('Y-07-01');
//$fechafinal = date('Y-m-d');


$sql = 'SELECT c_id, c_alias, r_id, r_folio, r_faplica, r_total, r_estatus, r_encargado FROM crm_clientes INNER JOIN remisiones ON r_cliente = c_id WHERE DATE(r_faplica) 
BETWEEN "'.$fechainicio.'" AND "'.$fechafinal.'" AND r_estatus = "A" AND c_id = "'.$idcl.'" ORDER BY r_faplica';
$result = setq($sql);
$respuesta = 
  '
  <div class="table-responsive" bis_skin_checked="1">
    <table class="table table-hover  table-bordered table-striped mb-0">
      <thead class="bg-primary text-white">
        <tr>
            <th>Alias Cliente</th>
            <th>Folio Remisión</th>
            <th>Fecha A.</th>
            <th>Total</th>
            <th>Encargado</th>
        </tr>
      </thead>
      <tbody>';
while($row = $result->fetch_array()){
  $respuesta .= '
  <tr>
      <tH scope="row">'.$row['c_alias'].'</th>
      <td>'.$row['r_folio'].'</td>  
      <td>'.fecha_formato($row['r_faplica'],false,false).'</td>
      <td>$ '.number_format($row['r_total'],2).'</td>
      <td>'.$row['r_encargado'].'</td>
  </tr>';

}

$respuesta .= '
</tbody>
</table>
</div>

';

echo $respuesta;

?>