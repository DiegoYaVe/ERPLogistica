<?php 
include_once('../funciones.php');

$fecha  = $_POST['fecha'];
$duracion = ($_POST['duracion'] * 60);
$hora = $_POST['hora'];

$respuesta = '';
//echo "$hora - $duracion - $fecha";

$sqlhora = 'SELECT COUNT(*) FROM registros_horarios WHERE rh_fcita BETWEEN "'.date('Y-m-d H:i',strtotime($fecha.' '.$hora.' -'.($duracion-60).' seconds')).'" AND 
"'.date('Y-m-d H:i',strtotime($fecha.' '.$hora.' +'.($duracion-60).' seconds')).'" AND rh_web IN ("2","3")';
$resulthora = setq($sqlhora);
list($existeintervalo) = $resulthora->fetch_array(); 
if($existeintervalo){
  $respuesta = '0';
  echo $respuesta;
}else{
  $sql = 'INSERT INTO registros_horarios SET
          rh_fecha = "'.$fecha.'",
          rh_hora = "'.$hora.'",
          rh_usuario = "ETHAN",
          rh_duracion = "'.$_POST['duracion'].'",
          rh_fcita = "'.$fecha." ".$hora.'",
          rh_estatus = "N",
          rh_web = "2"';
  setq($sql);

  $sql  = 'SELECT * FROM registros_horarios WHERE rh_fecha = "'.$fecha.'" AND rh_web = "2" ORDER BY rh_hora ASC';
  $result = setq($sql);
  while($row = $result->fetch_array()){
    $disablehorario = 'disabled';
    $totreg = busca($row['rh_id'],'registros_jdceo','rj_cita','COUNT(*)');
    if($totreg > 0) $hiddenbtn = 'hidden';
    else $hiddenbtn = '';
    $hiddenbtn = 'hidden';
    echo'
    <div class="mb-5 col-md-4 col-lg-2 col-xs-6"> 
      &nbsp;&nbsp;<button '.$hiddenbtn.' id="delh'.$i.'" type="button" class="btn btn-sm btn-danger text-white" onclick="delhora('.$i.');" title="Eliminar hora"><i class="fa fa-trash"></i></button>
      <input type="time" id="hora'.$i.'" min="'.$minhora.'" max="'.$maxhora.'" name="hora[]" class="form-control hora" step="'.$step.'" value="'.$row['rh_hora'].'" required="required"  '.$disablehorario.'>
    </div>';
  }
}

?>