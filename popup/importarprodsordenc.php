<?php
  session_start();
  ini_set('display_errors', 1);
  date_default_timezone_set("America/Mexico_City");
  $title = 'JD CEO';
  $numnot = 0;
  if($numnot > 0) $numtag = "danger"; else $numtag = "success";
  if(!isset($_SESSION['emp'])) $_SESSION['emp'] = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" data-textdirection="ltr" class="loading">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="author" content="JD Suite">
    <title><?php echo $title; ?></title>
    <link rel="icon" type="image/png" href="images/ico/favicon-32.png">
    <link rel="apple-touch-icon" sizes="60x60" href="images/ico/apple-icon-60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="images/ico/apple-icon-76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="images/ico/apple-icon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="images/ico/apple-icon-152.png">
    <link rel="shortcut icon" type="image/x-icon" href="images/ico/favicon-32.png">
    <link rel="shortcut icon" type="image/png" href="images/ico/favicon-32.png">
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
    <!-- END Custom CSS-->
    <link rel="stylesheet" type="text/css" href="../css/select2.min.css" />
  </head>
<script language="javascript">
function checksubmit(){
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function cargar(){
  opener.location.reload();
  window.close();
}
</script>
<?php
//ini_set('display_errors', 1);
//$mysqli = new mysqli("localhost",'tyesolut_root','Wptyeall.0','tyesolut_ceo');
include('../funciones.php');
$ordenc = $_REQUEST['ordenc'];
include_once('../modulos/ordenesc.php');
$ordn = new modelordenesc();
$ordn->select($ordenc);

//$query = 'SELECT * FROM cfdi_unidades ORDER BY cu_nmb ASC';
  echo '<div class="table table-responsive table-hover">
          <form method="post" action="../?modulo=ordenesc&accion=importremd&ordenc='.$ordenc.'" onsubmit="checksubmit();">
              <table class="table table-striped table-bordered table-hover" width="100%">
              <thead class="thead bg-primary text-white h4">
              <tr>
                <th width="5%" class="pb-1">Sel</th>
                <th width="15%" class="pb-1">Cantidad</th>
                <th width="15%" class="pb-1">Módelo</th>
                <th width="50%" class="pb-1">Producto</th>
                <th width="15%" class="pb-1">Costo</th>
              </tr>
             </thead>
             <tbody>';

  echo '<script>
          function setcant(i){
            if(document.getElementById("ch" + i).checked){
              document.getElementById("num" + i).disabled = false;
              document.getElementById("costo" + i).disabled = false;
              document.getElementById("num" + i).focus();
              document.getElementById("num" + i).select();
            }
            else{
              document.getElementById("num" + i).disabled = true;
            }
          }
        </script>';
  $query = 'SELECT * FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision
            WHERE r_tablero = "'.$ordn->tablero.'" ORDER BY rd_nmbarticulo';
  $resultado = setq($query);
  $i = 0;
  while($row = $resultado->fetch_assoc()){
    $bandera = 0;
    $i++;

    if($row['rd_articulo']){
      $combo = busca($row['rd_articulo'],'articulos','a_id','a_tipoprod');
      if($combo == "M"){
        $sql = 'SELECT * FROM articulos_combos WHERE ac_articulo = "'.$row['rd_articulo'].'"';
        $result = setq($sql);
        while($rowa = $result->fetch_array()){
          $modelo = busca($rowa['ac_ahijo'],'articulos','a_id','a_modelo');
          $costo = busca($rowa['ac_ahijo'],'articulo_proveedor','ap_activo = "1" AND ap_estatus = "1" AND ap_articulo','ap_costo');
          $nmb = busca($rowa['ac_ahijo'],'articulos','a_id','a_nmb');
          echo '<tr>
            <td class="align-middle">
              <input type="checkbox" name="prod'.$rowa['ac_ahijo'].'" id="ch'.$i.'" onchange="setcant('.$i.')" />
            </td>
            <td><input type="number" min="0" max="9999" value="'.$rowa['ac_cantidad'].'" disabled id="num'.$i.'" class="form-control" step="1" name="cantidad'.$row['rd_id'].'" onfocus="this.select();" /></td>
            <td class="align-middle">'.$modelo.'</td>
            <td class="align-middle">'.$nmb.'</td>
            <td><input type="number" min="0" max="999999" step="0.01" value="'.$costo.'" disabled id="costo'.$i.'" class="form-control" name="costo'.$row['rd_id'].'" onfocus="this.select();" /></td>
            ';
          echo '</tr>';
          $i++;
        }
        $bandera = 1;
      }else{
        $modelo = busca($row['rd_articulo'],'articulos','a_id','a_modelo');
        $costo = busca($row['rd_articulo'],'articulo_proveedor','ap_activo = "1" AND ap_estatus = "1" AND ap_articulo','ap_costo');

      }
    }
    else{
      $modelo = "-";
      $costo = 0;
    }
    if($bandera == 0){
      echo '<tr>
              <td class="align-middle">
                <input type="checkbox" name="prod'.$row['rd_id'].'" id="ch'.$i.'" onchange="setcant('.$i.')" />
              </td>
              <td><input type="number" min="0" max="9999" value="'.number_format($row['rd_cantidad']).'" disabled id="num'.$i.'" class="form-control" step="1" name="cantidad'.$row['rd_id'].'" onfocus="this.select();" /></td>
              <td class="align-middle">'.$modelo.'</td>
              <td class="align-middle">'.$row['rd_nmbarticulo'].'</td>
              <td><input type="number" min="0" max="999999" step="0.01" value="'.$costo.'" disabled id="costo'.$i.'" class="form-control" name="costo'.$row['rd_id'].'" onfocus="this.select();" /></td>
              ';
      echo '</tr>';
    }
  }
  echo '</tbody><tfoot><tr><td colspan="5"><button type="submit" class="btn btn-success" id="guardar"/>
          <i class="icon-download4"></i> Importar Productos
        </td></tr></tfoot></table></form></div>';
  ?>