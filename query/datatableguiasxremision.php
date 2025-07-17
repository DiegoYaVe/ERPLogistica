<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();


$cotizacion = $_POST['cotizacion'];

if(isset($_POST['cotizacion'])) $sqladd = 'WHERE ga_cotizacion = "'.$cotizacion.'"';
else $sqladd = '';

$sql = 'SELECT * FROM guias_articulos '.$sqladd.' ORDER BY ga_fini DESC';
$result = setq($sql);
$arreglo = array();
$i = 0;
while($row = $result->fetch_array()){  
  $guia = $row['ga_nmb'];
  if(!empty($guia)){
  $ugenera = $row['ga_ugenera'];
  if(empty($row['ga_sucursal'])){
    $sucursal = "No aplica";
    $tipoenvio = "A DOMICILIO";
  } else{
    $sucursal = busca($row['ga_sucursal'], 'paqueterias_sucursales', 'ps_id', 'CONCAT(ps_sucursal, " - ", ps_nmb)');
    $tipoenvio = "OCURRE";
  }

  $paqueteria = busca($row['ga_paqueteria'], 'paqueterias', 'p_id', 'p_nmb')." - ".$tipoenvio;
  
  $estatus = ($row['ga_estatus'] != "A") ? "Finalizado" : "En proceso de carga";
  $fini = $row['ga_fini'];
  if(empty($row['ga_ffin'])){
    $ffin = "No disponible";
  } else{
    $ffin = $row['ga_ffin'];
  }

  $nasignados = busca($row['ga_id'], 'remisionesc', 'rc_guia', 'COUNT(*)')." artículos";
  $acciones = "";
  $guiac = "'".$guia."'";
  if(!isset($_POST['cotizacion']) && $row['ga_embarque'] != "0")
  $acciones = '
  <div class="row">
    <div class="col-3">
      <a href="?modulo=embarcamiento&accion=show&id='.$row['ga_embarque'].'">
        <button type="button" class="btn btn-sm btn-success" data-toggle="tooltip" title="Ir al embarcamiento">
          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-ship"></i>
        </button>
      </a>
  </div>';
  else if(isset($_POST['cotizacion'])) $acciones = '
  <div class="row">
    <div class="col-3">
        <button type="button" onclick="cargarGuiaAlPanel('.$guiac.', 0);" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Cargar guía en el panel principal">
          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
        </button>
  </div>';
  $acciones .='<div class="col-6">
    <button type="button" id="evidencia'.$row['ga_id'].'" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia='.$row['ga_id'].'&tipo=P" href="javascript:;">
      <i class="fa fa-plus"></i>Evidencia</button>
  </div>';
  if(isset($_POST['cotizacion']))
  $acciones .= '<div class="col-3">
  <button type="button" onclick="deleteGuia('.$row['ga_id'].');" class="btn btn-sm btn-danger">
    <i class="fas fa-trash-alt"></i>
      </button>
</div>
  </div>
  ';
  $arreglo[] = array($guia,$ugenera,$paqueteria,$nasignados,$estatus,$fini,$ffin,$acciones);
  }
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>