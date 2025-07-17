<?php
include_once('../funciones.php');
$empresa = $_GET['empresa'];
$articulo = $_GET['articulo'];
$nmb = clearvmayus($_POST['nmbc']);
$hexadec = clearvmayus($_POST['hexadec']);
$siglas = clearvmayus($_POST['siglasc']);
$unique = 0;

if(!unique($nmb,'ac_nmbcolor','articulos_colores','ac_empresa = "'.$empresa.'"'))
  $unique = "N";
elseif(!unique($hexadec,'ac_hexadecimal','articulos_colores','ac_empresa = "'.$empresa.'"'))
  $unique = "C";
elseif(!unique($siglas,'ac_siglas','articulos_colores','ac_empresa = "'.$empresa.'"'))
  $unique = "C";
else{
  $idm = getmax('ac_id','articulos_colores',' ac_empresa = "'.$empresa.'" ');

  $sql = 'INSERT INTO articulos_colores SET
          ac_id = "'.$idm.'",
          ac_nmbcolor = "'.$nmb.'",
          ac_hexadecimal = "'.$hexadec.'",
          ac_siglas = "'.$siglas.'",
          ac_empresa = "'.$empresa.'"';
  setq($sql);

  $costo = busca($articulo,'articulos_precios','ap_activo = "1" AND ap_articulo','ap_costo');
  $base = busca($articulo,'articulos','a_id','a_cb');

  $sqlta = 'SELECT DISTINCT(av_tamano) FROM articulos_variantes
            WHERE av_articulo = "'.$articulo.'"';
  $resulta = setq($sqlta);

  if($resulta->num_rows > 0){
    $resulta = setq($sqlta);
    while($rowta = $resulta->fetch_array()){
      $siglata = busca($rowta['av_tamano'],'articulos_tamanos','at_id','at_alias');
      $cbcol = $base.$siglas.$siglata;

      $sqlav = 'INSERT INTO articulos_variantes SET
                av_articulo = "'.$articulo.'",
                av_color = "'.$idm.'",
                av_tamano = "'.$rowta['av_tamano'].'",
                av_costo = "'.$costo.'",
                av_codigo = "'.$cbcol.'"';
      setq($sqlav);
    }
  }
  else{
      $cbcol = $base.$siglas;
      $sqlav = 'INSERT INTO articulos_variantes SET
                av_articulo = "'.$articulo.'",
                av_color = "'.$idm.'",
                av_costo = "'.$costo.'",
                av_codigo = "'.$cbcol.'"';
      setq($sqlav);
  }
}

redirect('setcolores?articulo='.$articulo.'&uni='.$unique)
?>