<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$origen = $_POST['origen'];
$destino = $_POST['destino'];
$tipomov = $_POST['tipomov'];

$sqlf = '';
$arreglo = array();
if(!empty($origen)){
  $sqlf = ' WHERE m_almacen = "'.$origen.'"';
} 
if(!empty($destino)){
  if(!empty($origen)){
    $sqlf .= ' AND m_almacendest = "'.$destino.'"';
  } else{
    $sqlf = ' WHERE m_almacendest = "'.$destino.'"'; 
  }
} 

if(empty($origen) && empty($destino) && !empty($tipomov)){
    $sqlf = ' WHERE m_tipo = "'.$tipomov.'"'; 
}

/* $listAlmacenes = $_POST['arregloJS']; */
$sql = 'SELECT * FROM movimientos'.$sqlf;
$sql.=' ORDER BY m_fecha ASC m_estatus DESC';
$result = setq($sql);
while ($row = $result->fetch_array()) {

  if($row['m_tipo'] == "E"){
    $tipo = "Entrada";
  } else if($row['m_tipo'] == "S"){
    $tipo = "Salida";
  } else{
    $tipo = "Traspaso";
  }

  if($row['m_tipo'] == "A"){
    $estatus = "Finalizado";
  } else{
    $estatus = "Captura";
  }
  $acciones = '
  <a href="?modulo=movimientos&accion=show&tipo='.$row['m_tipo'].'&id='.$row['m_id'].'">
    <button type="button" class="btn-sm btn-info btn"><i class="fa fa-eye"></i> Detalle</button>
  </a>';
  if($row['m_estatus'] == "A"){
    $acciones = '<a target="_BLANK" href="formats/pdfmovimiento.php?tipo='.$row['m_tipo'].'&id='.$row['m_id'].'">
      <button type="button" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Imprimir</button>
    </a>';
  }

  

  $almorigen = busca($row['m_almacen'], "almacenes", "a_id", "a_nmb");
  $almdestino = busca($row['m_almacendest'], "almacenes", "a_id", "a_nmb");
  $arreglo[] = array($row['m_folio'], $tipo, $row['m_motivo'], $row['m_fecha'], $row['m_fechaap'], $row['m_usuario'], $almorigen, $almdestino, $estatus, $acciones);

}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);

?>