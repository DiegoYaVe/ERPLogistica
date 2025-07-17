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
    <base href="">
    <title>Fabrica de inflables</title>
    <meta name="description"
      content="SOMOS LA FÁBRICA #1 DE IFLABLES EN MÉXICO" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../assets/media/logos/favicon.png" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link href="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/jquery.fancybox.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" class="">
    <!--end::Global Stylesheets Bundle-->
    <script src="../assets/plugins/global/plugins.bundle.js"></script>
    <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
          <form method="post" action="../?modulo=recepciones&accion=importremd&ordenc='.$ordenc.'&recepcion='.$_GET['recepcion'].'" onsubmit="checksubmit();">
              <table class="table table-striped table-bordered table-hover" width="100%">
              <thead class="thead bg-primary text-white h4">
              <tr>
                <th width="5%" class="pb-1">Sel</th>
                <th width="15%" class="pb-1">Cantidad</th>
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
              document.getElementById("costo" + i).disabled = true;
            }
          }
        </script>';
  $query = 'SELECT * FROM ordenescd INNER JOIN ordenesc ON o_id = od_ordenc
            WHERE od_ordenc = "'.$ordn->id.'"';
  $resultado = setq($query);
  $i = 0;
  while($row = $resultado->fetch_assoc()){
    $bandera = 0;
    $i++;

    if($row['od_articulo']){
        $modelo = "-";
        $costo = 0;
    }
    if($bandera == 0){
      $sqlcant = 'SELECT od_cantidad,SUM(rd_cantidad), od_cantidad-SUM(rd_cantidad) resta, od_articulo FROM ordenesc INNER JOIN ordenescd ON od_ordenc = o_id INNER JOIN recepciones ON o_id = r_ordenc INNER JOIN recepcionesd ON rd_recepcion = r_id WHERE r_ordenc = "'.$ordn->id.'" AND rd_articulo = "'.$row['od_articulo'].'" AND od_id = "'.$row['od_id'].'"';
      $resultcant = setq($sqlcant);
      list($odcant,$rdcant,$resta,$articulo) = $resultcant->fetch_array();
      if($resta == NULL) {$row['od_cantidad'] = $row['od_cantidad'];}
      else {$row['od_cantidad'] = $resta;}

      if($row['od_cantidad'] <= 0){
        $disabledchk = "disabled";
        $row['od_cantidad'] = 0;
      }else{
        $disabledchk = "";
      }

      echo '<tr>
              <td class="align-middle">
                <input type="checkbox" name="prod'.$row['od_id'].'" id="ch'.$i.'" onchange="setcant('.$i.')"  '.$disabledchk.'/>
              </td>
              <td><input type="number" min="0" max="9999" value="'.number_format($row['od_cantidad']).'" disabled id="num'.$i.'" class="form-control" step="1" name="cantidad'.$row['od_id'].'" onfocus="this.select();" /></td>
              <td class="align-middle">'.$row['od_nmbarticulo'].'</td>
              <td><input type="number" min="0" max="999999" step="0.01" value="'.number_format($row['od_precio'], 2).'" disabled id="costo'.$i.'" class="form-control" name="costo'.$row['od_id'].'" onfocus="this.select();" /></td>
              ';
      echo '</tr>';
    }
  }
  echo '</tbody><tfoot><tr><td colspan="5"><button type="submit" class="btn btn-success" id="guardar"/>
        <i class="fas fa-download"></i> Importar Productos
        </td></tr></tfoot></table></form></div>';
  ?>