<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

/* $fini = $_POST['fini'];
$ffin = $_POST['ffin']; */
$ugen = $_POST['ugen'];
$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');

$sql = 'SELECT * FROM remisiones WHERE r_estatus IN ("F", "A", "FE") AND r_id IN (SELECT rc_remision FROM remisionesc WHERE rc_estatus = "A" GROUP BY rc_remision)'; //"A"  AND r_faplica BETWEEN "'.$fini.'" AND "'.$ffin.'" 
if(!empty($ugen)) $sql.= ' AND r_encargado LIKE "%'.$ugen.'%"';
$sql.=' ORDER BY r_id DESC';    
$result = setq($sql);
$arreglo = array();
/* echo $sql;
echo '<br>'; */
$i = 0;
$ac = '';
while($row = $result->fetch_array()){  
  $remision = $row['r_id'];
  $cotizacion = busca($remision, 'crm_cotizaciones', 'cc_remision', 'cc_id');
   $articulosc = intval(busca($remision, 'remisionesc', 'rc_estatus IN ("A") AND rc_tipoenvio IN ("O", "D") AND rc_remision', 'COUNT(*)'));
  /* $ac .= busca2($remision, 'remisionesc', 'rc_estatus IN ("A", "N") AND rc_tipoenvio IN ("O", "D") AND rc_remision', 'COUNT(*)')."<br><br>"; */
  if($articulosc > 0){
  /* $narticulos = intval(busca($cotizacion, 'crm_cotizacionesc', 'ccc_estatus = "N" AND ccc_guia IS NULL AND ccc_cotizacion', 'COUNT(*)')); */
  $narticulos = intval(busca($remision, 'remisionesc', 'rc_estatus = "N" AND rc_remision', 'COUNT(*)'));
  //echo busca2($cotizacion, 'remisionesc', 'rc_estatus = "N" AND rc_remision', 'COUNT(*)');
  $encargado = busca($row['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
  /* if($narticulos){ */
    $fini = $row['r_fliquidacion'];  
    $almacen = busca($row['r_almacen'], 'almacenes', 'a_id', 'a_nmb');
    $cliente = busca($row['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
    $cliente .= " ".busca($row['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
    if($row['r_estatus'] == "A"){
      $estatus = "Entrega parcial";
    } else{
      $estatus = "Por embarcar";
    }
    $ffin = busca($remision, 'cxcobrar', 'cx_tipo = "R" AND cx_referencia','cx_ffin');
    if($ffin == "0000-00-00 00:00:00") $ffin = 'Sin liquidar';
      $acciones = '
        <a data-fancybox data-type="ajax" data-src="popup/setguia.php?id='.$row['r_id'].'" href="javascript:;" >
            <button type="button" class="btn btn-sm btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
            </button>
          </a>
        ';
      if($grupo == "ADMIN" || $grupo == "GERENCIA") {
        $acciones .= '<a target="_BLANK" href="formats/pdfcotizacion.php?idcotiza='.$cotizacion.'&from=guias" >
          <button type="button" class="btn btn-sm btn-danger" /><i class="fa fa-file-pdf"></i></button>
        </a>';  
      } 
    $artenv = busca($remision, 'remisionesc','rc_estatus = "F" AND rc_remision', 'COUNT(*)');
    $arreglo[] = array($row['r_folio'],$row['r_nmb'],$encargado,$almacen,$cliente,$ffin,$estatus,$acciones, $artenv);
  /* } */
}
}
//echo $ac;



//Buscamos todas las garantías que van a requerir un embarcamiento
$sqlga = 'SELECT * FROM garantias_articulos WHERE ga_estatus = "M" AND ga_tipoenvio IN ("O","D") AND ga_guia IS NULL';
$sqlga.=' ORDER BY ga_fini DESC';    
$resultga = setq($sqlga);
/* echo $sql;
echo '<br>'; */
$i = 0;
$ac = '';
while($row = $resultga->fetch_array()){  

  $estatuscxc = intval(busca($row['ga_id'], 'cxcobrar', 'cx_tipo = "B" AND cx_estatus = "F" AND cx_referencia', 'COUNT(*)'));
  if($estatuscxc > 0){
  if(empty($row['ga_rcid'])){
    $folio = $row['ga_remision'];
    $name = $row['ga_articulo']." ".$rowga['ga_modelo'];
    $client = $row['ga_cliente'];
    $encargado = '';
    $fini = $row['ga_fini'];  
    $almacen = "SIN DEFINIR";
    $cliente = busca($client, 'crm_clientes', 'c_id', 'c_nmb');
    $cliente .= " ".busca($client, 'crm_clientes', 'c_id', 'c_apellidos');
    $tipo = "GARANTÍA";
    $estatus = "Por embarcar";
  
      $acciones = '
        <a data-fancybox data-type="ajax" data-src="popup/setguiagarantias.php?id='.$row['ga_id'].'" href="javascript:;" >
            <button type="button" class="btn btn-sm btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
            </button>
          </a>
        ';
  } else{
  $remision = $row['ga_remision'];

  $sqlr = 'SELECT r_folio, r_nmb, r_encargado FROM remisiones WHERE r_id = "'.$remision.'"';
  $resultr = setq($sqlr);
  list($folio, $name, $encargado) = $resultr->fetch_array();
  $client = $row['ga_cliente'];
  
  //echo busca2($cotizacion, 'remisionesc', 'rc_estatus = "N" AND rc_remision', 'COUNT(*)');
  /* $encargado = busca($encargado, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)'); */
  $encargado = '';
  $fini = $row['ga_fini'];  
  $almacen = "SIN DEFINIR";
  $cliente = busca($client, 'crm_clientes', 'c_id', 'c_nmb');
  $cliente .= " ".busca($client, 'crm_clientes', 'c_id', 'c_apellidos');
  $tipo = "GARANTÍA";
  $estatus = "Por embarcar";

    $acciones = '
      <a data-fancybox data-type="ajax" data-src="popup/setguiagarantias.php?id='.$row['ga_id'].'" href="javascript:;" >
          <button type="button" class="btn btn-sm btn-primary">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
          </button>
        </a>
      ';
    /* $acciones .= '<a target="_BLANK" href="formats/pdfcotizacion.php?idcotiza='.$cotizacion.'" >
    <button type="button" class="btn btn-sm btn-danger" /><i class="fa fa-file-pdf"></i></button>
  </a>'; */
  }
  $arreglo[] = array($folio." (".$tipo.")",$name,$encargado,$almacen,$cliente,$estatus,$acciones, '0');
}
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>