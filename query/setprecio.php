<?php
  session_start();
  include('../funciones.php');


  $cb = $_POST['cb'];
  if(isset($_POST['origen'])) $origen = $_POST['origen'];
  else $origen = 0;

  $viva = busca(1,'configuracionesp','c_id','c_iva')/100;

  if($origen == 1){
    $sql = 'SELECT ap_costo,ap_iva,ap_usd FROM articulo_proveedor INNER JOIN articulos ON a_id = ap_articulo WHERE a_cb = "'.$cb.'" AND ap_activo = "1" AND ap_estatus = "1"';
    $result = setq($sql);
    list($costo,$iva,$usd) = $result->fetch_array();
    if($usd == 1){
      $vusd = busca(1,'empresas','e_id','e_valorusd');
      $costo = $costo*$vusd; 
      if($iva == "1") $costo = number_format($costo * (1+$viva),2,'.','');
      else $costo = $costo;
    }else{
      if($iva == "1") $costo = number_format($costo * (1+$viva),2,'.',''); 
      else $costo = $costo;
    }
    echo number_format($costo,2,'.','');
  }else{
    $art = busca($cb, 'articulos', 'a_cb', 'a_id');
    if(!$art){
      $sql2 = 'SELECT av_modelo, av_articulo FROM articulos_variantes WHERE av_cb = "'.$cb.'"';
      $result2  = setq($sql2);
      list($modelo, $art) = $result2 ->fetch_array(); 
      $sql = 'SELECT * FROM articulos_precios WHERE ap_modelo = "'.$modelo.'" AND ap_articulo = "'.$art.'" AND ap_activo = "1"';
    } else {
      $sql = 'SELECT * FROM articulos_precios INNER JOIN articulos ON a_id = ap_articulo WHERE a_cb = "'.$cb.'" AND ap_activo = "1"';
    }
    
    $result = setq($sql);
    $row = $result->fetch_array();

    echo $row['ap_precio'];
  }
  //echo $_POST['cb'].' '.$_POST['esquema'];
?>