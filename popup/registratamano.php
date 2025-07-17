<?php
//ini_set('display_errors', 1);
include_once('../funciones.php');
$empresa = $_GET['empresa'];
$articulo = $_GET['articulo'];
$nmb = clearvmayus($_POST['nmbc']);
$hexadec = clearvmayus($_POST['hexadec']);
$siglata = clearvmayus($_POST['siglasc']);
$unique = 0;

if(!unique($nmb,'at_nmbtamano','articulos_tamanos','at_empresa = "'.$empresa.'"'))
  $unique = "N";
elseif(!unique($hexadec,'at_alias','articulos_tamanos','at_empresa = "'.$empresa.'"'))
  $unique = "C";

else{
  $idm = getmax('at_id','articulos_tamanos',' at_empresa = "'.$empresa.'" ');

  $sql = 'INSERT INTO articulos_tamanos SET
          at_id = "'.$idm.'",
          at_nmbtamano = "'.$nmb.'",
          at_alias = "'.$siglata.'",
          at_empresa = "'.$empresa.'"';
  setq($sql);

  $costo = busca($articulo,'articulos_precios','ap_activo = "1" AND ap_articulo','ap_costo');
  $base = busca($articulo,'articulos','a_id','a_cb');

  $sqlta = 'SELECT DISTINCT(av_color) FROM articulos_variantes
            WHERE av_articulo = "'.$articulo.'"';
  $resulta = setq($sqlta);

  if($resulta->num_rows > 0){
    $sqld = 'DELETE FROM articulos_variantes WHERE av_articulo = "'.$articulo.'" AND av_color = "'.$rowta['av_color'].'"';
    setq($sqld);

    $resulta = setq($sqlta);
    while($rowta = $resulta->fetch_array()){
      $siglas = busca($rowta['av_color'],'articulos_colores','ac_id','ac_siglas');
      $cbcol = $base.$siglas.$siglata;

      $sqlav = 'INSERT INTO articulos_variantes SET
                av_articulo = "'.$articulo.'",
                av_color = "'.$rowta['av_color'].'",
                av_tamano = "'.$idm.'",
                av_costo = "'.$costo.'",
                av_codigo = "'.$cbcol.'"';
      setq($sqlav);
    }
  }
  else{
      $cbcol = $base.$siglas;
      $sqlav = 'INSERT INTO articulos_variantes SET
                av_articulo = "'.$articulo.'",
                av_tamano = "'.$idm.'",
                av_costo = "'.$costo.'",
                av_codigo = "'.$cbcol.'"';
      setq($sqlav);
  }
}

redirect('settamanos?articulo='.$articulo.'&uni='.$unique)
?>