<?php
  include('../funciones.php');

  $cp = $_POST['cp'];
  $html = "";

  $sql = 'SELECT cpc_colonia,cpc_nmb FROM carta_porte_colonias WHERE cpc_cp = "'.$cp.'"';
  $result = setq($sql) or die($sql);
  while($row = $result->fetch_array()){
    $html.='<option value="'.$row['cpc_colonia'].'">'.$row['cpc_colonia'].' - '.$row['cpc_nmb'].'</option>';
  }

  echo $html;
?>