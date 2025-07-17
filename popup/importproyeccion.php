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
include('../funciones.php');
$proyeccion = $_GET['proyeccion'];
$title = 'Selecciona las cuentas por cobrar que deseas importar';
$link = '../?modulo=cuentas&accion=setproyecciond&proyeccion='.$proyeccion.'&from='.$_GET['from'].'';

  echo '<div class="table table-responsive table-hover">
          <form method="post" action="'.$link.'" onsubmit="checksubmit();">
            <table class="table table-striped table-bordered table-hover" width="100%">
              <thead class="thead bg-primary text-white">
              <tr>
                <th width="5%" class="pb-1">Sel</th>
                <th width="15%" class="pb-1">Referencia</th>
                <th width="10%" class="pb-1">Fecha</th>
                <th width="10%" class="pb-1">Tipo</th>
                <th width="30%" class="pb-1">Descripción</th>
                <th width="10%" class="pb-1">Total</th>
                <th width="20%" class="pb-1">Importe</th>
              </tr>
             </thead>
             <tbody>';

  echo '<script>
          function setcant(i){
            if(document.getElementById("ch" + i).checked){
              var importe = document.getElementById("cta" + i).value;

              document.getElementById("monto" + i).disabled = false;
              document.getElementById("monto" + i).value = importe;
              document.getElementById("monto" + i).focus();
              document.getElementById("monto" + i).select();
            }
            else{
              document.getElementById("monto" + i).disabled = true;
              document.getElementById("monto" + i).value = 0;
            }
          }
          function setprec(prec,i){
            var cant = document.getElementById("num"+i).value;
            var precF = prec * cant;

            document.getElementById("costo"+i).value = precF;
          }
        </script>';
  if($_GET['from'] == "cxcobrar"){
    $sql = 'SELECT cx_id id,cx_cliente cliente,cx_referencia ref,cx_tipo tipo,(cx_importe-cx_abonado) importe,cx_observaciones observaciones,cx_fini fini,cx_importe
            FROM cxcobrar
            WHERE cx_estatus IN ("A","N") AND cx_tipo != "C" ORDER BY cx_fini ASC';
  }
  elseif($_GET['from'] == "cxpagar"){
    $sql = 'SELECT cp_id id,cp_proveedor cliente,cp_idref ref,cp_tipo tipo,(cp_importe-cp_abonado) importe,cp_observaciones observaciones,cp_fini fini,cp_importe
            FROM cxpagar
            WHERE cp_estatus IN ("A","N") ORDER BY cp_fini ASC';
  }

  $resultado = setq($sql);
  $i = 0;
  $importe = 0;
  while($row = $resultado->fetch_array()){
    $i++;
    $importe+= $row['importe'];
    if($_GET['from'] == "cxcobrar"){
      $importeoriginal = $row['cx_importe'];
      $tipop = array("T"=>"COBRO TAE","Y"=>"PROYECTO","S"=>"SOPORTE","Z"=>"POLIZA","P"=>"PRESTAMO","A"=>"COTIZACION","I"=>"COMISION");
      $refer=busca($row['cliente'],'crm_clientes','c_id','c_alias');

      if(in_array($tipop[$row['tipo']],$tipop)) $tp = $tipop[$row['tipo']];
      else $tp = busca($row['tipo'],'cortes_tipom','ct_tipo = "C" AND ct_clave','ct_descripcion');

      if($row['tipo'] == "R") $desc = busca($row['ref'],'remisiones','r_id','r_nmb').' - '.$refer;
      else $desc = $row['observaciones'];

      if($refer) $refer=busca($row['cliente'],'crm_clientes','c_id','c_alias');
      else $refer=$row['cliente'];

      if(busca($row['id'],'proyeccionesd','pd_tipo = "C" AND pd_proyeccion = "'.$proyeccion.'" AND pd_referencia','COUNT(*)') > 0){
        $selop = "checked";
        $montop = busca($row['id'],'proyeccionesd','pd_tipo = "C" AND pd_proyeccion = "'.$proyeccion.'" AND pd_referencia','pd_importe');
      }
      else{
        $selop= "";
        $montop = 0;
      }
    }
    else{
      $tp = busca($row['tipo'],'cortes_tipom','ct_tipo = "P" AND ct_clave','ct_descripcion');
      $importeoriginal = $row['cp_importe'];
      $desc = $row['observaciones'];
      if($row['tipo'] == "C"){
        $refer = busca($row['cliente'],'proveedores','p_id','p_nmb');
        $desc.=' '.busca(busca($row['ref'],'ordenesc','o_id','o_tablero'),'crm_tableros','ct_id','ct_nmb').' - '.$refer;
      }
      elseif($row['tipo'] == "T"){
        $refer = busca($row['cliente'],'proveedores','p_id','p_nmb');
        $desc.=' '.busca($row['ref'],'tae','t_id','t_fecha').' - '.$refer;
      }
      elseif($row['tipo'] == "M"){
        $tp = 'Orden de Compra Menor';
        $refer = busca($row['cliente'],'empresas','e_id','e_nmb');
        $desc.=' '.busca(busca($row['ref'],'ordenesp','op_id','op_proyecto'),'crm_tableros','ct_id','ct_nmb').' - '.$refer;
      }
      elseif($row['tipo'] == "O"){
        $refer = busca($row['cliente'],'empresas','e_id','e_nmb');
        $desc.=' - '.busca(busca($row['ref'],'ordenesp','op_id','op_proyecto'),'crm_tableros','ct_id','ct_nmb').' - '.$refer;
      }
      else{
        $refer = busca($row['cliente'],'empresas','e_id','e_nmb');
        if($row['tipo'] == "F") $desc = busca($row['ref'],'gastosf','gf_id','gf_nmb');
      }

      if(busca($row['id'],'proyeccionesd','pd_tipo = "P" AND pd_proyeccion = "'.$proyeccion.'" AND pd_referencia','COUNT(*)') > 0){
        $selop = "checked";
        $montop = busca($row['id'],'proyeccionesd','pd_tipo = "P" AND pd_proyeccion = "'.$proyeccion.'" AND pd_referencia','pd_importe');
        $disab = "";
      }
      else{
        $selop= "";
        $montop = 0;
        $disab = "disabled";
      }

    }
    echo '<input type="hidden" name="original'.$row['id'].'" id="x'.$row['id'].'" value="'.$importeoriginal.'">';
    echo '<input type="hidden" name="cta'.$row['id'].'" id="cta'.$row['id'].'" value="'.$row['importe'].'">';
    echo '<input type="hidden" name="nmb'.$row['id'].'" id="cta'.$row['id'].'" value="'.$desc.'">';

    echo '<tr>
            <td><input type="checkbox" name="cuenta'.$row['id'].'" id="ch'.$row['id'].'" onchange="setcant('.$row['id'].')" '.$selop.' /></td>
            <td class="pb-1 text-medium">'.$refer.'</td>
            <td class="pb-1 text-medium">'.date('d-m-Y',strtotime($row['fini'])).'</td>
            <td class="pb-1 text-medium">'.strtoupper($tp).'</td>
            <td class="pb-1 text-medium">'.$desc.'</td>
            <td class="pb-1 number-align text-medium">'.number_format($row['importe'],2).'</td>
            <td class="pb-1 number-align text-medium"><input type="number" id="monto'.$row['id'].'" name="monto'.$row['id'].'" step="0.01" '.$disab.' value="'.$montop.'" class="form-control" /></td>
          </tr>';
  }
    echo '<tr>
            <td colspan="3"></td>
            <td class="pb-1 number-align text-medium bg-primary text-white">Total</td>
            <td class="pb-1 number-align text-medium bg-primary text-white">'.number_format($importe,2).'</td>

          </tr>';

  echo '</tbody>
        <tfoot>
          <tr>
            <td colspan="5">
              <button type="submit" class="btn btn-success" id="guardar">
                <i class="icon-download4"></i> Importar conceptos
              </button>
            </td>
          </tr>
        </tfoot>
      </table>
    </form>
    <center><h4>'.$title.'</h4></center>
  </div>';
?>