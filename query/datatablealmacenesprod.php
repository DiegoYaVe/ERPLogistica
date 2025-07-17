<?php
include_once('../funciones.php');
ini_set('display_errors',1);
session_start();

$search = $_POST['search'];
$sqlf = '';
$arreglo = array();
if(!empty($search)){
  $sqlf = ' WHERE (pa_nmb LIKE "%'.$search.'%")';
} 
/* $listAlmacenes = $_POST['arregloJS']; */
$sql = 'SELECT * FROM pr_almacenes'.$sqlf;
$result = setq($sql);
while ($row = $result->fetch_array()) {
  if($row['pa_vendible'] == "1") $vendible = 'Almacen de venta';
  else $vendible = 'Almacen de resguardo';
  $img = '';
  if($row['pa_img']){ 
    $img = '<img src="'.$row['pa_img'].'" alt="" class=" img-chat img-thumbnail"></td>';
  }
  $direccion = $row['pa_calle'].' '.$row['pa_nume'].' '.$row['pa_numi'].' '.$row['pa_colonia'].' '.$row['pa_ciudad'].' '.$row['pa_estado'].' '.$row['pa_pais'].' '.$row['pa_cp'];
  $acciones = '
  <div style="display: flex;">
  <div>
  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar almacen" data-fancybox data-type="ajax" data-src="popup/setalmacenprod.php?id='.$row['pa_id'].'" href="javascript:;">
    <button type="button" class="btn btn-sm btn-primary text-white">
      <i class="fas fa-edit"></i>
    </button> 
  </a>
  </div>
  <div>';

  if(busca($row['pa_id'],'pr_existencias','pe_almacen','COUNT(*)') == 0)
    $acciones .= '
  <button type="button" class="btn btn-sm btn-danger text-white" onclick="checerase('.$row['pa_id'].')">
    <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar el almacen" class="fas fa-trash"></i></button>
  ';
  $acciones .= '</div>';
  

  $arreglo[] = array($row['pa_nmb'],$vendible,$direccion,$row['pa_telefono'],$row['pa_correo'],$img,$acciones);

}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);

?>