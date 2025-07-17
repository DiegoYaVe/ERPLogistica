<?php
  session_start();
  include("funciones.php");
  if(!isset($_GET['id'])) $user = $_SESSION['uid'];
  elseif($_GET['id'] != "0") $user = $_GET['id'];
  else $user = 0;
  $sql = 'SELECT ca_id,ca_nmb,ca_fecha fecha,ca_hora,ca_descripcion,cad_user FROM crm_actividades INNER JOIN crm_actividadesd 
          ON ca_id = cad_actividad WHERE ca_estatus IN ("N","P","F")';
  if($user == "0") $sql .= ' AND cad_user IN (SELECT u_id FROM usuarios)';
  else $sql .= ' AND cad_user = "'.$user.'"';
  $sql .= ' ORDER BY ca_fecha ASC, ca_hora DESC';
  $result = setq($sql);
  $datos = array();
  $i = 0;
  while($row = $result->fetch_array()){
      $color = busca($row['cad_user'],'usuarios','u_id','u_color');
      $datos[$i]['id'] = $row['ca_id'];
      $datos[$i]['title'] = date("H:i",strtotime($row['ca_hora'])).' '.$row['ca_nmb'];
      $datos[$i]['start'] = $row['fecha'];
      $datos[$i]['end'] = $row['fecha'];
      $datos[$i]['descripcion'] = $row['ca_descripcion'];
      $datos[$i]['backgroundColor'] = $color;
      $datos[$i]['textColor'] = '#ffffff';
      $datos[$i]['className'] = 'evento-calendario';
    $i++;;
  }

  echo json_encode($datos);
?>