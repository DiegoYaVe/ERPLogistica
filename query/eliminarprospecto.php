<?php
/* ini_set('display_errors', 1); */
session_start();
include_once('../funciones.php');
include_once('../modulos/prospectos.php');

$prospectos = new modelprospectos();

$html = "";
$telefonos = "";
$response = array();
$usuariosventas = array();
$ocupados = array();
$id = $_POST['id'];
$lead = $_POST['lead'];
$sqldel = "DELETE FROM crm_leads WHERE cl_id = '" . $id . "'";
setq($sqldel);
$respuesta3 = 3;

$sqlcl2 = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$lead.'" AND cl_estatus = "P" AND cl_fasigna = "'.date("Y-m-d").'" ORDER BY cl_id ASC';
$resultcl2 = setq($sqlcl2);


$sqlcl = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$lead.'" AND cl_fasigna = "'.date("Y-m-d").'" ORDER BY cl_estatus = "P" DESC, cl_id DESC;';
$resultcl = setq($sqlcl);
$read = "readonly";

$registros = $resultcl2->num_rows;
$nvendedores = getmax("uo_orden", "usuarios_orden", false, false); //Consultamos el total de usuarios asignados para ventas

if ($registros < $nvendedores) {
  $read = "";
} else if ($registros == $nvendedores) {
  $respuesta2 = 2;
}

$norden = 1;
$i = 1;
while ($row = $resultcl->fetch_array()) {
  //Actualizamos el nuevo orden de captura
  $sqlupd = 'UPDATE crm_leads SET cl_ncaptura = "'.$norden.'" WHERE cl_id = "'.$row['cl_id'].'"';
  $resultupd = setq($sqlupd);
  if($row['cl_estatus'] == "A" || $row['cl_estatus'] == "F"){
    $backg = 'background: #deffde;';
  } else if($row['cl_estatus'] == "X"){
    $backg = 'background: #f5000026;';
  } else{
    $backg = '';
  }
  $html .= '
  <tr class="">
    <th style="height: 44.84px;'.$backg.'">+'.$row['cl_code']." ".$row['cl_telefono'].'</span></th>
    <th style="height: 44.84px;'.$backg.'"><span>'.$row['cl_nmb'].'</span></th>
    <th style="height: 44.84px;'.$backg.'">'.$row['cl_cp'].'</span></th>
    <th style="height: 44.84px;'.$backg.'">'.$row['cl_correo'].'</span></th>
    <th style="height: 44.84px;'.$backg.'">'.$row['cl_observacion'].'</span></th>
    <th style="height: 44.84px;'.$backg.'">';
    if($row['cl_estatus'] == "P"){
      $html .= '<button type="button" onClick="editarLead('.$row['cl_id'].');" class="btn btn-sm btn-primary" /><i class="fas fa-user-edit"></i></button>  
      <button type="button" onClick="borrarLead('.$row['cl_id'].');" class="btn btn-sm btn-danger" /><i class="fa fa-trash"></i></button>';
    } else{
      $vend = '';
      $html .= '
      <div class="row">
      <div class="col-md-6">
          <select class="form-control" id="idvendedor' . $row['cl_id'] . '" name="idvendedor' . $row['cl_id'] . '" onchange="cambiarvendedor(' . $row['cl_id'] . ');">';
          $sqlv = 'SELECT * FROM usuarios_orden WHERE uo_estatus = "A"';
          $resultv = setq($sqlv);
          while ($rowv = $resultv->fetch_array()) {
              $nmb = busca($rowv['uo_uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
              if ($row['cl_vendedor'] == $rowv['uo_uid']) {
                  $sel = 'selected';
                  $vend = $row['cl_vendedor'];
              } else {
                  $sel = '';
              }
              $html .= '<option value="' . $rowv['uo_uid'] . '" ' . $sel . '>' . $nmb . '</option>';
          }
          $html .= '</select>
          </div>
          <div class="col-md-2">
              <input type="hidden" value="'.$vend.'" id="venoriginal'.$row['cl_id'].'">
              <button type="button" onClick="editarLead(' . $row['cl_id'] . ');" class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i></button>
          </div>
      </div>';

    }
    $html .= '</th>
  </tr>
  ';
  if ($i == 1) {
    $telefonos .= $row['cl_telefono'];
    $codes .= $row['cl_code'];
  } else {
    $telefonos .= ",".$row['cl_telefono'];
    $codes .= ",".$row['cl_code'];
  }
  $i++;
  $norden++;
}

  //BUSCAMOS TODOS LOS LEADS QUE SE CREARON HOY PERO QUE ESTÁN REASIGNADOS A OTROS DIAS
  $sqlr = 'SELECT DISTINCT(hl_lead) AS prospecto FROM historial_leadsasignacion INNER JOIN crm_leads ON cl_id = hl_lead WHERE cl_estatus = "R";';
  $resultr = setq($sqlr);
  while($rowp = $resultr->fetch_array()){
    $prospecto = $rowp['prospecto'];
    $respon = intval(busca($prospecto, 'historial_leadsasignacion', 'hl_fasigna = "'.date("Y-m-d").'" AND hl_lead', 'COUNT(*)'));
    if($respon > 0){
      $sqlcl = 'SELECT * FROM crm_leads WHERE cl_id = "'.$prospecto.'"';
      $resultcl = setq($sqlcl);
      $rowcl = $resultcl->fetch_array();
      $backg = 'background: #005ef526;';

    $html .= '
      <tr class="">
        <th style="height: 44.84px;'.$backg.'">+'.$rowcl['cl_code']." ".$rowcl['cl_telefono'].'</span></th>
        <th style="height: 44.84px;'.$backg.'"><span>'.$rowcl['cl_nmb'].'</span></th>
        <th style="height: 44.84px;'.$backg.'">'.$rowcl['cl_cp'].'</span></th>
        <th style="height: 44.84px;'.$backg.'">'.$rowcl['cl_correo'].'</span></th>
        <th style="height: 44.84px;'.$backg.'">'.$rowcl['cl_observacion'].'</span></th>
        <th style="height: 44.84px;'.$backg.'">';
        if($rowcl['cl_estatus'] == "P"){
    $html .= '
          <button type="button" onClick="editarLead('.$rowcl['cl_id'].');" class="btn btn-sm btn-primary" /><i class="fas fa-user-edit"></i></button>  
          <button type="button" onClick="borrarLead('.$rowcl['cl_id'].');" class="btn btn-sm btn-danger" /><i class="fa fa-trash"></i></button>';
        } else{
          $vend = '';
          $html .= '
          <div class="row">
          <div class="col-md-6">
              <select class="form-control" id="idvendedor' . $rowcl['cl_id'] . '" name="idvendedor' . $rowcl['cl_id'] . '" onchange="cambiarvendedor(' . $rowcl['cl_id'] . ');">';
              $sqlv = 'SELECT * FROM usuarios_orden WHERE uo_estatus = "A"';
              $resultv = setq($sqlv);
              while ($rowv = $resultv->fetch_array()) {
                  $nmb = busca($rowv['uo_uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
                  if ($rowcl['cl_vendedor'] == $rowv['uo_uid']) {
                      $sel = 'selected';
                      $vend = $rowcl['cl_vendedor'];
                  } else {
                      $sel = '';
                  }
                  $html .= '<option value="' . $rowv['uo_uid'] . '" ' . $sel . '>' . $nmb . '</option>';
              }
              $html .= '</select>
              </div>
              <div class="col-md-2">
                  <input type="hidden" value="'.$vend.'" id="venoriginal'.$rowcl['cl_id'].'">
                  <button type="button" onClick="editarLead(' . $rowcl['cl_id'] . ');" class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i></button>
              </div>
          </div>';
        }
    $html .=  '</th>
      </tr>';
      if($i == 1){
        $telefonos .= $rowcl['cl_telefono'];
        $codes .= $rowcl['cl_code'];
      } else{
        $telefonos .= ",".$rowcl['cl_telefono'];
        $codes .= ",".$rowcl['cl_code'];
      }
      $i++;
    }
  }
$response['tel'] = $telefonos;
$response['codes'] = $codes;



if (isset($respuesta2)) {
  if (isset($respuesta3)) {
    $respuesta = $respuesta3;
  } else {
    $respuesta = $respuesta2;
  }

}
$response['html'] = $html;
$response['registros'] = $registros;

echo json_encode($response);
?>