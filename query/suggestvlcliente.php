<?php
session_start();
//ini_set('display_errors', 1);
include('../funciones.php');

$html = '';
$key = strtoupper($_POST['codigo']);
$estatusventalinea = ['A'=>'PENDIENTE DE LIBERACION','F'=>'COMPRA LIBERADA','C'=>'CANCELADA'];
$bgestatuslinea = ['A'=>'bg-info','F'=>'bg-success','C'=>'bg-danger'];


if(!empty($key) && strlen($key) >= 3){
  $sql = 'SELECT * FROM comprasweb WHERE c_nmb LIKE "%'.strip_tags($key).'%" LIMIT 0,20';
  $result = setq($sql, true);
  if($result->num_rows > 0){
    $html .= '<table class="table bg-white">
    <thead class="bg-teal text-white">
      <th>Cliente</th>
      <th>Fecha de acceso</th>
      <th>Estatus</th>
      <th>Ver</th>
    </thead>
    <tbody>';
    while ($row = $result->fetch_array()) {
			
      $url = '?modulo=comprasweb&accion=edit&id='.$row['ca_id'];

      $html .= '<tr>
      <td>'.$row['c_cb'].'</td>
      <td>'.$row['ca_nmb'].'</td>
      <td class="text-white '.$this->bgestatuslinea[$row['c_estatus']].'">'.$this->estatusventalinea[$row['c_estatus']].'</td>
      <td><a href="'.$url.'" class="btn btn-primary"><i class="icon-eye"></i></td>
      </tr>';
    }
    $html .='
    </body>
    </table>';

  } else {
    $html.= '<div class="alert alert-danger">No existe compra con el nombre de cliente ingresado </div>';
  } 
  
}

echo $html;
?>