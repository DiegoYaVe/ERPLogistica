<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
/* foreachdie(); */
$idfor = $_GET['id'];
if($idfor){
  $sql = 'SELECT * FROM pr_forecast WHERE prf_id = "'.$idfor.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = 'update';
  $titulo = "Actualizar";
  if($row['prf_estatus'] == "F") $read = "readonly";
  else $sel = "";
  $fini = $row['prf_fini'];
  $ffin = $row['prf_ffin'];
  $solicitud = $row['prf_solicitud'];
} else {
  $read = "";
  $accion = 'insert';
  $titulo = "Agregar";
  $fini = date('Y-m-d');
  $ffin = date('Y-m-d', strtotime('+30 days'));
  $solicitud = '';
}


echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' datos del Forecast</div>
      <form method="post" action="index?modulo=forecast&accion='.$accion.'&id='.$idfor.'" id="form">
        <div class="row">
          <div class="mb-5 col-md-6">
            <label>Descripción del forecast:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Descripción del forecast" name="nmb" value="'.$row['prf_nmb'].'" required="required">
          </div>
          <div class="mb-5 col-md-3">  
            <label>Fecha de inicio:</label>
            <input type="date" id="finicio" class="form-control" data-toggle="tooltip" data-placement="top" name="finicio" value="'.date('Y-m-d',strtotime($fini)).'" required="required">
          </div>
          <div class="mb-5 col-md-3">  
            <label>Fecha de fin:</label>
            <input type="date" id="ffinal" class="form-control" data-toggle="tooltip" data-placement="top" name="ffinal" value="'.date('Y-m-d',strtotime($ffin)).'" required="required">
          </div>';
          echo '<div class="mb-5 col-md-4">  
            <label>Solicitud:</label>
            <select id="solicitud" name="solicitud" class="form-control" required>';
            $sql = 'SELECT * FROM pr_solicitudes WHERE ps_estatus = "N"';
            $result = setq($sql);
              while($row = $result -> fetch_array()){
                if($row['ps_id'] == $solicitud) $sel = 'selected';
                else $sel = '';
                echo '<option value="'.$row['ps_id'].'" '.$sel.'>'.$row['ps_folio'].' - '.$row['ps_nmb'].'</option>';
              }
            echo '</select> 
          </div>
          </div>';
          echo '<div class="mb-5 col-md-12">
          <center>
            <button type="button" class="btn btn-primary" onclick="fechas()"><i class="fa fa-save"></i> Guardar </button>
            </center>
          </div>';
          
        echo '</div>
      </form>
    </div>';

?>
<script>
  function fechas(){
    var fini = document.getElementById("finicio");
    var ffin = document.getElementById("ffinal");
    var nmb = document.getElementById("nmb");
    var form = document.getElementById("form");

    var fechaini = new Date(fini.value);
    var fechafin = new Date(ffin.value);
    if(fechafin < fechaini){
      alert('La fecha de fin no puede ser menor que la de inicio.');
    } else {
      if(nmb.value != ""){
        form.submit();
      } else {
        alert('Agregue una descripción al forecast para continuar.');
      }
    }
  }

</script>