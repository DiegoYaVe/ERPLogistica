<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');

$idproceso = $_GET['id'];
if($idproceso){
  $sql = 'SELECT * FROM pr_procesos WHERE ppr_id = "'.$idproceso.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $accion = 'update';
  $titulo = "Actualizar";
  if($row['ppr_estatus'] == "A") $sel = "checked";
  else $sel = "";
  $selc = '';
  $seli = '';
  $selo = '';
  if($row['ppr_tipo'] == "C") $selc = "checked";
  else if($row['ppr_tipo'] == "I") $seli = "checked";
  else if($row['ppr_tipo'] == "O") $selo = "checked";
} else {
  $sel = "checked";
  $accion = 'insert';
  $titulo = "Agregar";
  $selc = 'checked';
  $seli = '';
  $selo = '';
}



echo '
    <div class="container col-10">  
    <div class="col-12 alert alert-primary">'.$titulo.' proceso</div>
      <form method="post" action="index?modulo=procesos&accion='.$accion.'">
        <input type="hidden" value="'.$idproceso.'" name="id" />
        <div class="row">
          <div class="mb-5 col-md-3 col-12">
            <label>Nombre del proceso:</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre para identificar la proceso" name="nmb" value="'.$row['ppr_nmb'].'" required="required" onfocus="this.select();">
          </div>
          <div class="mb-5 col-md-4 col-12">
            <label>Descripción:</label>
            <input type="text" id="descripcion" class="form-control focus mayus" placeholder="Descripcion de la proceso" name="descripcion" value="'.$row['ppr_descripcion'].'" required="required" onfocus="this.select();">
          </div>
          <div class="mb-5 col-md-2 col-12">
            <label>Planta</label>
            <select name="planta" id="planta" class="form-control" onchange="traerfases();" required>
              <option value="" class="">Selecciona planta</option>';
              $sqplanta = 'SELECT * FROM pr_plantas WHERE pp_estatus = "A" ';
              $resultplanta = setq($sqplanta);
              while($rowplanta = $resultplanta->fetch_array()){
                if($row['ppr_planta'] == $rowplanta['pp_id']) $selectplan = 'selected';
                else $selectplan = '';
                echo '
                <option value="'.$rowplanta['pp_id'].'" class="" '.$selectplan.'>'.$rowplanta['pp_nmb'].'</option>
                ';
              }
            echo '
            </select>
          </div>
          <div class="mb-5 col-md-2 col-12">
            <label>Fase</label>
            <select name="fase" id="fase" class="form-control" required>
              <option value="" class="">Selecciona fase</option>
            </select>
          </div>
          <div class="mb-5 col-md-2 col-12">  
            <label>Estatus:</label>
            <input type="checkbox" id="estatus" class="form-control flipswitch" data-toggle="tooltip" name="estatus" '.$sel.'>
          </div>
          <div class="mb-5 col-md-5 col-12">  
            <label>Tipo de proceso:</label>
            <br>
            <input type="radio" class="form-check-input" id="corte" name="tipo" onchange="checartipo();" value="C" '.$selc.'> Corte
            <input type="radio" class="form-check-input" id="impresion" name="tipo" onchange="checartipo()" value="I" '.$seli.'> Impresión
            <input type="radio" class="form-check-input" id="otro" name="tipo" value="O" onchange="checartipo()" '.$selo.'> Otro (especificar)
          </div>
          <div class="mb-5 col-md-4 col-12" id="obs">  
            <label>Especificar:</label>
            <input type="text" id="texto" name="texto" value="'.$row['ppr_obstipo'].'" class="form-control" placeholder="DESCRIBE EL TIPO DE PROCESO" data-toggle="tooltip"  '.$sel.'>
          </div>
          <div class="mb-5 col-md-1 col-12">
            <label>Guardar:</label>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
          </div>
        </div>
      </form>
    </div>';


/*     if($row['ppr_planta'] || $row['ppr_fase']){
      echo '
      <script class="">
        traerfases('.$row['ppr_fase'].');
      </script>
      ';
    } */

?>

<script>
  function checartipo(){
    var otro = document.getElementById('otro');
    var texto = document.getElementById('obs');
    var inptext = document.getElementById('texto');
    if(otro.checked){
      texto.hidden = false;
      inptext.setAttribute('required', 'required')
    }else {
      texto.hidden = true;
      inptext.removeAttribute('required')
    }
  }
  checartipo();

</script>
<script class="">
  function traerfases(fase){
    var planta = document.getElementById("planta").value;
    $.ajax({
      url: 'query/traerfases.php',
      method: 'POST',
      data: {planta: planta,
            fase: fase},
    }).success(function (data){
      console.log(data);
      document.getElementById("fase").innerHTML = data;
    });
  }

</script>

<?php 
if($row['ppr_planta'] || $row['ppr_fase']){
  echo '
  <script class="">
    traerfases('.$row['ppr_fase'].');
  </script>
  ';
}
?>