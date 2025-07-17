<?php
  include('../funciones.php');

  $cp = $_POST['cp'];

  $sql = 'SELECT cc_localidad,cc_municipio,cc_estado FROM cfdi_cp WHERE cc_cp = "'.$cp.'"';
  $result = setq($sql) or die($sql);
  if($result->num_rows > 0){
    $result = setq($sql);
    list($loc,$mun,$est) = $result->fetch_array();

    $return_arr[] = array("localidad" => $loc,
                          "municipio" => $mun,
                          "estado" => $est);

    echo json_encode($return_arr);
    //echo $est;
  }else echo "";
?>