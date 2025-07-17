<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" data-textdirection="ltr" class="loading">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="author" content="JD Suite">
    <title><?php echo $title; ?></title>
    <link rel="icon" type="image/png" href="../images/ico/favicon-32.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../images/ico/apple-icon-60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../images/ico/apple-icon-76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../images/ico/apple-icon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../images/ico/apple-icon-152.png">
    <link rel="shortcut icon" type="image/x-icon" href="../images/ico/favicon-32.png">
    <link rel="shortcut icon" type="image/png" href="../images/ico/favicon-32.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="../fonts/icomoon.css">
    <link rel="stylesheet" type="text/css" href="../fonts/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" type="text/css" href="../css/pace.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN ROBUST CSS-->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="../css/app.css">
    <link rel="stylesheet" type="text/css" href="../css/colors.css">
    <link rel="stylesheet" type="text/css" href="../css/screen.css">
    <!-- END ROBUST CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="../css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="../css/core/menu/menu-types/vertical-overlay-menu.css">
    <link rel="stylesheet" type="text/css" href="../css/core/colors/palette-gradient.css">
    <!-- END Page Level CSS-->
    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" type="text/css" href="../css/style.css">
  </head>
  <body>
<?php
ini_set('display_errors', 0);
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
include_once('../funciones.php');
$empresa = $_POST['empresa'];
$articulo = $_POST['articulo'];

$salida = "";
//$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
    $salida = '<div class="table table-responsive">
                <table class="table table-striped table-bordered table-hover" width="100%">
                <thead class="thead-secondary">';

  if(isset($_POST['consulta']) && !empty($_POST['consulta'])){

    $q = mb_strtoupper(trim($_POST['consulta']));
    if(strlen($q) > 2)
      $query = 'SELECT * FROM articulos_tamanos WHERE at_nmbtamano LIKE "%'.$_POST['consulta'].'%" OR at_alias LIKE "%'.$_POST['consulta'].'%"
                AND at_empresa = "'.$empresa.'" ORDER BY at_nmbtamano ASC ';
  }
  echo '  <tr>
            <th width="10%">Seleccionar</th>
            <th width="30%">Grupo de tamanos</th>
            <th width="60%">Valores</th>
          </tr>
        </thead><tbody>';
  if($query){
    $resultado = setq($query);
    if($resultado->num_rows > 0){
      while($row = $resultado->fetch_assoc()){
        $i++;

        $sqlc = 'SELECT COUNT(*) FROM articulos_variantes
                 WHERE av_articulo = "'.$articulo.'" AND av_articulo IN
                 (SELECT a_id FROM articulos WHERE a_empresa = "'.$empresa.'" )';
        $result = setq($sqlc);
        list($numcon) = $result->fetch_array();

        if($numcon > 0)
          $checkb = 'checked';
        else
          $checkb = "";

        $salida.='<tr>
        <td>
          <input type="checkbox" class="check30" '.$checkb.' name="con'.$row['at_id'].'" id="ch'.$row['at_id'].'" onchange="setcondicion('.$row['at_id'].');" />
        </td>
        <td>'.$row['at_nmbtamano'].'</td>
        <td style="background:'.$row['at_siglas'].'">&nbsp;</td>
        </tr>';
      }

      $salida.='</tbody></table>';
    }
    else{
      $salida = '<tr><td colspan="3"><center>Por favor escriba un indicio de palabra</center></td></tr>';
    }
  }

  echo $salida;
  //Finaliza tabla dinámica de registros
?>