<?php
//ini_set('display_errors',1);
include_once('../funciones.php');

$checkboxes = $_POST['checkboxes'];
$tipo = $_POST['tipo'];
$guia = $_POST['guia'];

$cadena = str_replace("select", "", $checkboxes);
$ids = explode(",", $cadena);
$guia = '"'.$guia.'"';
if(!isset($_POST['paqueteria'])){
  if($tipo != "In"){
    $guia = "NULL";
  }
  
  for($i = 0; $i < count($ids); $i++){
    $sql = 'UPDATE remisionesc SET 
            rc_guia = '.$guia.' WHERE 
            rc_id = "' . $ids[$i] . '"';
    setq($sql);
  }
  $respuesta = 0;
} else{
  $respuesta = 0;
  $paqueteria = $_POST['paqueteria'];
  for ($i = 0; $i < count($ids); $i++) {
    $sqlguia = 'SELECT rc_paqueteria FROM remisionesc WHERE rc_id = "' . $ids[$i].'"';
    $resultg = setq($sqlguia);
    list($paqueteriag) = $resultg->fetch_array();
    if ($paqueteriag == $paqueteria) {
      $sql = 'UPDATE remisionesc SET 
              rc_guia = ' . $guia . ' WHERE 
              rc_id = "' . $ids[$i] . '"';
      setq($sql);
    } else {
      $respuesta = 1; //El aticulo no coincide con la paqueteria configurada
      break;
    }
    
  }
  
}
echo $respuesta;
?>