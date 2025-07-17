<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$search = $_POST['search'];
$sqlf = '';
$arreglo = array();
if(!empty($search)){
  $sqlf = ' WHERE (a_nmb LIKE "%'.$search.'%")';
} 
/* $listAlmacenes = $_POST['arregloJS']; */
$sql = 'SELECT * FROM almacenes'.$sqlf;
$result = setq($sql);
while ($row = $result->fetch_array()) {
  if($row['a_vendible'] == "1") $vendible = 'Almacen de venta';
  else $vendible = 'Almacen de resguardo';
  $img = '';
  if($row['a_img']){ 
    $img = '<img src="'.$row['a_img'].'" alt="" class=" img-chat img-thumbnail"></td>';
  }
  $direccion = $row['a_calle'].' '.$row['a_nume'].' '.$row['a_numi'].' '.$row['a_colonia'].' '.$row['a_ciudad'].' '.$row['a_estado'].' '.$row['a_pais'].' '.$row['a_cp'];
  $acciones = '
  <div style="display: flex;">
  <div>
  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar almacen" data-fancybox data-type="ajax" data-src="popup/setalmacen.php?id='.$row['a_id'].'" href="javascript:;">
    <button type="button" class="btn btn-sm btn-primary text-white">
      <i class="fas fa-edit"></i>
    </button> 
  </a>
  </div>
  <div>';

  if(busca($row['a_id'],'existencias','e_almacen','COUNT(*)') == 0)
    $acciones .= '
  <button type="button" class="btn btn-sm btn-danger text-white" onclick="checerase('.$row['a_id'].')">
    <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar el almacen" class="fas fa-trash"></i></button>
  ';
  else
    $acciones .= '
    <button type="button" class="btn btn-sm btn-info text-white" onclick="verlineasneg('.$row['a_id'].')">
      <i data-toggle="tooltip" data-placement="top" title data-original-title="El almacen se encuentra en uso, Presiona aquí para ver los productos asignados a '.$row['a_nmb'].'" class="fa fa-eye"></i>
    </button> ';
  $acciones .= '</div>';
  

  $arreglo[] = array($row['a_nmb'],$vendible,$direccion,$row['a_telefono'],$row['a_correo'],$img,$acciones);

}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);

?>