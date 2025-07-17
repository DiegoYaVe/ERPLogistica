<?php
session_start();
 include_once('../funciones.php');
 //ini_set('display_errors', 1);
 
 //foreachdie();
 $cambio = $_POST['cambio'];
 $ant = $_POST['ant'];
 $exc = $_POST['exc'];
 $cotizacion = $_POST['cotizacion'];
 $cotizaciond = $_POST['cotizaciond'];
 $cantidad = $_POST['cantidad'];
 $error = "0";
 
 $idprod = getIDprod($cambio);  
 if($idprod){
  $tipoprod = busca($idprod, 'articulos', 'a_id', 'a_tipoprod');
  if($tipoprod != "M"){
    if($idprod != $exc){
      $sqlupd = 'UPDATE crm_cotizaciones_cambios SET cca_estatus = "I" WHERE cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_articulo = "'.$ant.'"';
      setq($sqlupd);
      $idcambio = busca($idprod, 'crm_cotizaciones_cambios', 'cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_estatus = "N" AND cca_articulo', 'cca_id');
      if($idcambio){
        $cant = busca($idprod, 'crm_cotizaciones_cambios', 'cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$cotizaciond.'" AND cca_estatus = "N" AND cca_articulo', 'cca_cantidad');
        $cant += $cantidad;
        $sql = 'UPDATE crm_cotizaciones_cambios SET cca_cantidad = "'.$cant.'", cca_fgen = "'.date('Y-m-d H:i:s').'", cca_ugen = "'.$_SESSION['uid'].'" WHERE cca_id = "'.$idcambio.'"';
      } else {
        $sql = 'INSERT INTO crm_cotizaciones_cambios SET cca_cotizacion = "'.$cotizacion.'",
                                                      cca_cotizaciond = "'.$cotizaciond.'",
                                                      cca_articulo = "'.$idprod.'",
                                                      cca_cambio = "",
                                                      cca_cantidad = "'.$cantidad.'",
                                                      cca_fgen = "'.date('Y-m-d H:i:s').'", 
                                                      cca_ugen = "'.$_SESSION['uid'].'",
                                                      cca_estatus = "N"                                                   
                                                      ';
      }
      setq($sql);
      $sqlins = 'INSERT INTO crm_cotizaciones_cambios SET cca_cotizacion = "'.$cotizacion.'",
                                                      cca_cotizaciond = "'.$cotizaciond.'",
                                                      cca_articulo = "'.$ant.'",
                                                      cca_cambio = "'.$idprod.'",
                                                      cca_cantidad = "'.$cantidad.'",
                                                      cca_fgen = "'.date('Y-m-d H:i:s').'", 
                                                      cca_ugen = "'.$_SESSION['uid'].'",
                                                      cca_estatus = "C"
                                                      ';
      
      setq($sqlins);
      $sqldel = 'DELETE FROM crm_cotizacion_variantes WHERE ccv_cotizacion = "'.$cotizacion.'" AND ccv_cotizaciond = "'.$cotizaciond.'" AND ccv_articulo = "'.$ant.'"';
      setq($sqldel);
      $var = busca($idprod, 'articulos_variantes', 'av_articulo', 'COUNT(*)');
      if($var < 1){
        $exist = busca($cotizacion, 'crm_cotizacion_variantes', 'ccv_articulo = "'.$idprod.'" AND ccv_cotizaciond = "'.$cotizaciond.'" AND ccv_cotizacion', 'ccv_cantidad');
        if($exist){
          $canttot = $exist+$cantidad;
          $sqlupd = 'UPDATE crm_cotizacion_variantes SET ccv_cantidad = "'.$canttot.'" WHERE ccv_cotizacion = "'.$cotizacion.'" AND 
                                                                                          ccv_cotizaciond = "'.$cotizaciond.'" AND 
                                                                                          ccv_articulo = "'.$idprod.'" AND 
                                                                                          ccv_modelo = ""';
          setq($sqlupd);                                                                                          
        } else {
          $sqlins = 'INSERT INTO crm_cotizacion_variantes SET ccv_cotizacion = "'.$cotizacion.'",
                                                          ccv_cotizaciond = "'.$cotizaciond.'",
                                                          ccv_articulo = "'.$idprod.'",
                                                          ccv_modelo = "",
                                                          ccv_cantidad = "'.$cantidad.'",
                                                          ccv_existenciaant = "0"';
          setq($sqlins);    
        }                                                  
      }
      $precioant = busca($ant, 'articulos_precios', 'ap_activo = "1" AND ap_modelo IS NULL AND ap_articulo', 'ap_precio');
      $precionuevo = busca($idprod, 'articulos_precios', 'ap_activo = "1" AND ap_modelo IS NULL AND ap_articulo', 'ap_precio');
      //echo "precionuevo: ".$precionuevo." precioant: ".$precioant." idprod: ".$idprod;
      if($precionuevo > $precioant){
        $addtotal = $precionuevo - $precioant;
        $totalold = busca($cotizacion, 'crm_cotizacionesd', 'cdm_id = "'.$cotizaciond.'" AND cdm_cotizacion', 'cdm_precio');
        $totalfin = floatval($totalold)+floatval($addtotal);
        $sqlupd = 'UPDATE crm_cotizacionesd SET cdm_precio = "'.$totalfin.'" WHERE cdm_cotizacion = "'.$cotizacion.'" AND cdm_id = "'.$cotizaciond.'"';
        setq($sqlupd);  
      }
    } else {
      $error = "2"; // El artículo a cambiar es el mismo
    }
  } else {
    $error = "3"; // El artículo es un combo
  }
 } else {
  $error = "1"; // El artículo ingresado no existe
 }

 echo $error;

?>
