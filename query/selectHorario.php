<?php
  //ini_set('display_errors',1);
  date_default_timezone_set("America/Mexico_City");
  include('../funciones.php');
  session_start();

  $html = "";
  $cliente = $_POST['fecha'];
  if(isset($_POST['origen'])){
    $hora = date("H:i");
    $asesor = $_POST['asesor'];
    $sql = 'SELECT ca_hora,ca_id FROM crm_actividades WHERE ca_estatus = "N" AND ca_user = "'.$asesor.'" AND ca_accion = "8" 
            AND ca_fecha = "'.$_POST['fecha'].'"';
    if(date("Y-m-d",strtotime($_POST['fecha'])) == date("Y-m-d")) $sql .= ' AND ca_hora >= "'.date("H:i",strtotime($hora.'+1 hours')).'"';
    $result = setqTYE($sql);
    $numrow = $result->num_rows;
    if($numrow > 0){
      $html .= '<label>Hora</label>
        <select name="fecha" id="hora" class="form-control" required="required" onchange="seltipo();">';
          $html .= '<option value="" disabled selected>Selecciona una hora</option>';
          while($row = $result->fetch_array()){
            $html .= '<option value="'.$row['ca_id'].'">'.date("H:i",strtotime($row['ca_hora'])).'</option>';
          }
        $html .= '</selected>';
    }else{
      $html .= '<label>Hora</label>
        <select name="fecha" id="hora" class="form-control" required="required" onchange="seltipo();">';
          $html .= '<option value="" disabled selected>Selecciona una hora</option>';
        $html .= '</selected>';
    }
  }else{
    $sql = 'SELECT rh_hora, rh_id FROM registros_horarios WHERE rh_web = "1" AND rh_estatus = "N" AND rh_fecha = "'.$_POST['fecha'].'" ORDER BY rh_hora';
    $result = setq($sql);
    $numrow = $result->num_rows;
    if($result->num_rows > 0){
      $html .= '<label>Hora</label>
        <select name="fecha" id="hora" class="form-control" required="required">';
      $html .= '<option value="" disabled selected>Selecciona una hora</option>';
      while($row = $result->fetch_array()){
        $html .= '<option value="'.$row['rh_id'].'">'.date("H:i",strtotime($row['rh_hora'])).'</option>';
      }
      $html .= '</selected>';
    }
  }
  echo $html;
?>