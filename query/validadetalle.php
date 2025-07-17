<?php 
  include_once('../funciones.php');
  //ini_set('display_error', 1);

  $sql = 'UPDATE crm_cotizacionesd SET cdm_completo = "0" WHERE cdm_cotizacion = "'.$_POST['cotizacion'].'" AND cdm_id = "'.$_POST['cotizaciond'].'"';
  setq($sql);

?>