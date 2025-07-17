<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */


$sqlu = 'SELECT * FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A"';
$sqlu.=' ORDER BY u_id DESC';    
$resultu = setq($sqlu);

/* echo $sql; */
$result = setq($sqlu);
$arreglo = array();
$i = 0;
while($row = $resultu->fetch_array()){ 
  if(isset($_POST['tipo'])){
    $estatus = ' checked';
  } else{
    $existe = busca($row['u_id'], 'usuarios_orden', 'uo_estatus = "A" AND uo_uid', 'uo_estatus');
    if(!empty($existe)){
      $estatus = ' checked';
    } else{
      $estatus = '';
    }
  }

    $acciones = '
    <input class="form-check-input" type="checkbox" id="check'.$row['u_nuser'].'" name="check'.$row['u_nuser'].'" value="'.$row['u_id'].'" '.$estatus.'>
      ';
  /* $arreglo[] = array($row['u_id'],$acciones); */
  $arreglo[] = array($row['u_nmb']." - ".$row['u_id'],$acciones);
  $i++;
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>