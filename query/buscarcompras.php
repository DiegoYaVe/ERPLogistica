<?php
include_once('../funciones.php');
session_start();


$idordenc = $_POST['id'];
if($idordenc != 0){
  $sql = 'SELECT * FROM recepciones INNER JOIN ordenesc ON r_ordenc = o_id WHERE r_empresa = "'.$_SESSION['emp'].'" AND r_ordenc = "'.$idordenc.'"';
  $result = setq($sql);
  $html = "";
  $html .= '<div class="table-responsive">
            <table class="table table-bordered table-hover">
            <thead class="bg-primary text-white">
              <tr>
                <th>Recepcion</th>
                <th>Orden de Compra</th>
                <th>Fecha de Aplicación</th>
              </tr>
            </thead>
            <tbody>';
  while($row = $result->fetch_array()){
    //$html .= '<option value="'.$row['r_id'].'">'.$row['r_folio'].'</option>';
    if($row['r_fechaapli'] == NULL) $fechaapli = "No aplicada"; else $fechaapli = $row['r_fechaapli'];

    $html .= '<tr>
                <td><a href="?modulo=recepciones&accion=show&id='.$row['r_id'].'">'.$row['r_folio'].'</a></td>
                <td><a href="?modulo=ordenesc&accion=show&id='.$row['r_ordenc'].'">'.$row['o_folio'].'</a></td>
                <td>'.$fechaapli.'</td>
              </tr>';
  }
  $html .= '</tbody>
            </table>
            </div>';
  if($result->num_rows == 0) $html = "";
}else{
  $html="";
}
echo $html;

?>