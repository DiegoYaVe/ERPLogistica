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
include('../funciones.php');
$remision = $_GET['remision'];
$title = 'Selecciona solo los productos a devolver y la cantidad exacta de cada uno';
if(isset($_GET['tablero'])){
  $tablero = $_GET['tablero'];
  $link = '../?modulo=devoluciones&accion=insert&id='.$remision.'&idt='.$_GET['tablero'].'';
  $bandera = 1;
}else {
  $tablero = 0;
  $bandera = 2;
  $link = '../?modulo=devoluciones&accion=updated&remision='.$remision.'&id='.$_GET['id'].'';
}
  echo '<div class="table table-responsive table-hover">
          <form method="post" action="'.$link.'" onsubmit="checksubmit();">
            <table class="table table-striped table-bordered table-hover" width="100%">
              <thead class="thead bg-primary text-white h4">
              <tr>
                <th width="5%" class="pb-1">Sel</th>
                <th width="15%" class="pb-1">Cantidad</th>
                <th width="15%" class="pb-1">Módelo</th>
                <th width="50%" class="pb-1">Producto</th>
                <th width="15%" class="pb-1">Costo Unitario</th>
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
          function setprec(prec,i){
            var cant = document.getElementById("num"+i).value;
            var precF = prec * cant;

            document.getElementById("costo"+i).value = precF;
          }
        </script>';
  $query = 'SELECT * FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision
            WHERE r_id = "'.$remision.'" ORDER BY rd_nmbarticulo';
  $resultado = setq($query);
  $i = 0;
  while($row = $resultado->fetch_assoc()){
    $i++;
    $cantidad = $row['rd_cantidad'];
    $costo = $row['rd_precio'];

    if($row['rd_articulo']){
      $modelo = busca($row['rd_articulo'],'articulos','a_id','a_modelo');
      if($bandera == 2){
        $sqlc = 'SELECT rdd_cantidad FROM remisiones_devolucionesd INNER JOIN remisiones_devoluciones 
                ON rdd_devolucion = rd_id WHERE rd_remision = "'.$_GET['remision'].'" AND rdd_articulo = "'.$row['rd_articulo'].'"';
        $resultc = setq($sqlc);
        list($c) = $resultc->fetch_array();
        $cantidad = $row['rd_cantidad'] - $c;
      }
    }
    else{
      $modelo = "-";
      if($bandera == 2){
        $idrd = busca($row['rd_id'],'remisiones_devolucionesd','rdd_remisiond','rdd_cantidad');
        $cantidad = $row['rd_cantidad'] - $idrd;
      }
    }

    if($cantidad > 0){
      echo '<tr>
            <td class="align-middle">
              <input type="checkbox" name="prod'.$row['rd_id'].'" id="ch'.$i.'" onchange="setcant('.$i.')" />
            </td>
            <td><input type="number" min="1" max="'.$cantidad.'" value="'.$cantidad.'" disabled id="num'.$i.'" class="form-control" step="0.01" name="cantidad'.$row['rd_id'].'" onfocus="this.select();"/></td>
            <td class="align-middle">'.$modelo.'</td>
            <td class="align-middle">'.$row['rd_nmbarticulo'].'</td>
            <td><input type="number" min="0" max="999999" step="0.01" value="'.number_format($costo,2,'.','').'" disabled id="costo'.$i.'" class="form-control" name="costo'.$row['rd_id'].'" onfocus="this.select();" readonly/></td>
          </tr>';
    }
  }
  echo '</tbody>
        <tfoot>
          <tr>
            <td colspan="5">
              <button type="submit" class="btn btn-success" id="guardar">
                <i class="fas fa-download" style="color: #ffffff;"></i> Importar Productos
              </button>
            </td>
          </tr>
        </tfoot>
      </table>
    </form>
    <center><h4>'.$title.'</h4></center>
  </div>';
  ?>