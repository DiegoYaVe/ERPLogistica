<?php
ini_set('display_errors', 0);
session_start();
include_once('../funciones.php');
include_once('../modulos/prospectos.php');

$response = array();
if(isset($_SESSION['uid'])){
$prospectos = new modelprospectos();

$html = "";
$telefonos = "";
$usuariosventas = array();
$ocupados = array();
$ocupados2 = array();
$id = $_POST['id'];
$nmb = $_POST['nmb'];
$cp = $_POST['cp'];
$correo = $_POST['correo'];
$phone = $_POST['telefono'];
$code = $_POST['code'];
$status = '';
$telefono = preg_replace("/[^0-9]/", "", $phone); //Limpiamos el número de teléfono

$pais = $_POST['pais'];
$observacion = $_POST['observacion'];
$lead = $_POST['lead'];

$enviar = 0;
$read = "readonly";
$ncaptura = getmax("cl_ncaptura", "crm_leads WHERE cl_fasigna = '" . date("Y-m-d") . "'", false, true); //Buscamos el número consecutivo diario de registros
if(!empty($id)){
  $sqldat = 'SELECT cl_ncaptura, cl_estatus, cl_vendedor FROM crm_leads WHERE cl_id = "'.$id.'"'; 
  $resultdat = setq($sqldat);
  list($ncaptura, $status, $vendedori) = $resultdat -> fetch_array();
  /* $ncaptura = busca($id, "crm_leads", "cl_id", "cl_ncaptura");
  $status = busca($id, "crm_leads", "cl_id", "cl_estatus");
  if($status == "A"){
    $vendedori = busca($id, "crm_leads", "cl_id", "cl_vendedor");
  } */
  $sqldel = "DELETE FROM crm_leads WHERE cl_id = '".$id."'";
  setq($sqldel);
  $respuesta3 = 3;
} else{
  //Verificamos si el telefono existe previamente
  $existe = busca($telefono, 'crm_leads', ' cl_lead = "'.$lead.'" AND cl_id != "'.$id.'" AND cl_code = "'.$code.'" AND cl_telefono', 'cl_vendedor');
  if(!empty($existe)){
    $respuesta  = 41; //El telefono ya existe para un lead de la página
    $agente = busca($existe, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
    $response['agente'] = $existe." ".$agente;
  }
  $status = 'P';

  //Verificamos si el telefono esta registrado en clientes
  $existe = busca($telefono, 'crm_clientes', ' c_telefono1 = "'.$telefono.'" OR c_telefono2', 'c_uregistro');
  if(!empty($existe)){
    $respuesta  = 42; //El telefono ya existe para un lead de la página
    $agente = busca($existe, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
    $response['agente'] = $existe." ".$agente;
  }
  $status = 'P';
}


if($respuesta != 41){//El telefono NO existe para un lead de la página
$pvendedor = busca($telefono, "crm_leads", "cl_telefono", "cl_vendedor"); //Buscamos si hay un registro previo del prospecto
$nvendedores = intval(busca("A", "usuarios_orden", 'uo_estatus', 'COUNT(*)')); //Consultamos el total de usuarios asignados para ventas

$clid0 = busca($lead, "crm_leads", "cl_lead", "MAX(cl_id) AS cl_id");
$consecutivo = intval(busca($clid0, "crm_leads", "cl_id", "cl_consecutivo"));

if($consecutivo >= $nvendedores){
  $siguiente = 1; //Se reinicia el contador
} else{
  $siguiente = $consecutivo + 1; //Continuamos con el bloque
}


//Sacamos el registro de crm_leads inicial del bloque hasta el final
$sqlcon = "SELECT cl_id FROM crm_leads WHERE cl_consecutivo = 1 ORDER BY cl_id DESC, cl_consecutivo DESC LIMIT 1";
$resultcon = setq($sqlcon);
list($inicial) = $resultcon->fetch_array();

$sqlvend = "SELECT cl_vendedor FROM crm_leads WHERE cl_id >= '".$inicial."' AND cl_lead = '".$lead."' ORDER BY cl_id ASC, cl_consecutivo ASC LIMIT ".$consecutivo;
$resultvend = setq($sqlvend);
$k = 0;
while($rowvend = $resultvend->fetch_array()){
  $ocupados[$k] = $rowvend['cl_vendedor'];
  $k++;
}

$sqlu = "SELECT * FROM usuarios_orden WHERE uo_estatus = 'A' ORDER BY uo_orden";
$resultu = setq($sqlu);
while ($rowu = $resultu->fetch_array()) {
  array_push($usuariosventas, $rowu['uo_uid']);
}

// Encontrar usuarios en Ventas que no están en Usuarios ocupados
$disponibles = array_diff($usuariosventas, $ocupados);

//Comrobamos que el número de vendedores no sea mayor al orden
/* if (empty($vendedororden)) { */
  $vendedor = '';
if (count($disponibles) == 0) {
  //Si el número de asignación llegó al límite de vendedores asignados volvemos a comenzar desde el 1
  $vdor = busca('A', 'usuarios_orden', 'uo_estatus', 'MIN(uo_orden) AS uo_orden');
  if(empty($vdor)){
    $respuesta = 22; //No hay vendedor activos
  } else{
    /* $vendedor = busca($vdor, 'usuarios_orden', 'uo_orden', 'MIN(uo_uid) AS uo_uid'); */
    $vendedor = busca($vdor, 'usuarios_orden', 'uo_orden', 'uo_uid');
  }
} else {
  //Si el número de asignación no ha llegado al límite de vendedores asignados asignamos el vendedor consecutivo
  foreach ($disponibles as $value) {
    $vendedor = $value;
    break;
  }
  

}
if($respuesta != 22){//Si hay vendedores activos para asignar prospectos
//Si el prospecto ya tuvo un registro previo se le asigna el asesor que lo atendió en esa ocasión
if (!empty($pvendedor)) {
  $vendedor = $pvendedor;
}

if(isset($vendedori) && !empty($vendedori)){
  $vendedor = $vendedori;
}

$prospectos->setdatalead($id, $lead, $nmb, $cp, $correo, $telefono, $code, $pais, $status, $observacion, $vendedor, $ncaptura, $_SESSION['uid'], "", date("Y-m-d"), $siguiente);
$respuesta = $prospectos->insertlead();

$sqlcl = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$lead.'" AND cl_fasigna = "'.date("Y-m-d").'" ORDER BY cl_estatus = "P" DESC, cl_id DESC;';
$resultcl = setq($sqlcl);
$read = "readonly";

$clid1 = busca($lead, "crm_leads", "cl_lead", "MAX(cl_id) AS cl_id");
$consecutivo = intval(busca($clid1, "crm_leads", "cl_id", "cl_consecutivo"));

if($consecutivo >= $nvendedores){
  $respuesta2 = 2;
  $enviar = 1;
} else{
  $nvendedores2 = intval(busca("A", "usuarios_orden", 'uo_estatus', 'COUNT(*)')); //Consultamos el total de usuarios asignados para ventas
  $registros = intval(busca($lead, 'crm_leads', 'cl_estatus = "P" AND cl_lead', 'COUNT(*)'));

  if($registros < $nvendedores2){
    $read = "";
  } else {
    $read = "readonly";
  }
}

if($respuesta == 1){
  $i = 1;
  while($row = $resultcl->fetch_array()){
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
      /* $html .= '
      <select class="form-control" id="idvendedor'.$row['cl_id'].'" name="idvendedor'.$row['cl_id'].'" onchange="cambiarvendedor('.$row['cl_id'].');">';
      $sqlv = 'SELECT * FROM usuarios_orden WHERE uo_estatus = "A"';
      $resultv = setq($sqlv);
      while($rowv = $resultv->fetch_array()){
        $nmb = busca($rowv['uo_uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
        if($row['cl_vendedor'] == $rowv['uo_uid']){
          $sel = 'selected';
        } else{ 
          $sel = '';
        }
        $html .=  '<option value="'.$rowv['uo_uid'].'" '.$sel.'>'.$nmb.'</option>';
      }
      $html .= '
      </select>
      '; */
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
  if($i == 1){
    $telefonos .= $row['cl_telefono'];
    $codes .= $row['cl_code'];
  } else{
    $telefonos .= ",".$row['cl_telefono'];
    $codes .= ",".$row['cl_code'];
  }
  $i++;
  }

  //BUSCAMOS TODOS LOS LEADS QUE SE CREARON HOY PERO QUE ESTÁN REASIGNADOS A OTROS DIAS
  /* $sqlr = 'SELECT DISTINCT(hl_lead) AS prospecto FROM historial_leadsasignacion INNER JOIN crm_leads ON cl_id = hl_lead WHERE cl_estatus = "R";';
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
  $response['codes'] = $codes; */
}

if(isset($respuesta2)){
  $respuesta = $respuesta2;
}

if(isset($respuesta3)){
  $respuesta = $respuesta3;
}

$response['html'] = $html;
$response['registros'] = $registros;
$response['vendedores'] = $nvendedore2;
$response['enviar'] = $enviar;
} //Si no entra a este if significa que NO hay vendedores activos para asignar prospectos
}

$response['respuesta'] = $respuesta;
} else{
  $response['respuesta'] = 247; //La sesión caducó
}


echo json_encode($response);
?>