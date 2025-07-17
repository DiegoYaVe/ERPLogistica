<?php
  ini_set('display_errors',0);
  include('../funciones.php');
  session_start();

  $html = "";
  $sql = 'SELECT u_id FROM usuarios WHERE u_empresa = "'.$_SESSION['emp'].'"';
  if(isset($_REQUEST['act'])) $sql .= ' AND u_id NOT IN (SELECT cad_user FROM crm_actividadesd WHERE cad_actividad = "'.$_REQUEST['act'].'" )';
  else $sql .= ' AND u_id != "'.$_SESSION['uid'].'"';
  $result = setq($sql) or die($sql);
  $html = '<option value="0" disabled="">Selecciona un usuario </option>';
  while($row = $result->fetch_array()){
    $html .='<option value="'.$row['u_id'].'">'.$row['u_id'].'</option>';
  }

  echo $html;
?>