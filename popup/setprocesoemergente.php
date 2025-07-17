<?php
include_once('../funciones.php');


$ordenp = $_REQUEST['ordenp'];
$fase = $_REQUEST['fase'];
$orden = $_REQUEST['ordenant']; 

echo '
<div class="container">
  <div class="alert alert-primary ">
  Selecciona proceeso a agregar
  <p class="text-danger">*  El proceso emergente entrará listado en la fase actual</p>
  </div>
';

echo '<form action="?modulo=ordenprod&accion=procesoemergente&ordenp='.$ordenp.'&ant='.$orden.'&fase='.$fase.'" method="post" onsubmit="checksubmit();">';
echo '<div class="row">';
  echo '<div class="col-12 col-md-2 mt-2  mb-5" >
    <div class="form-group">
      <label for="agregar" class">Planta</label><br>
      <div class="input-group">
        <select id="planta" name="planta" class="form-control" onchange="traerfases();" required>
          <option value="" class="">Selecciona planta</option>
        ';
          $sqlmp = 'SELECT * FROM pr_plantas WHERE pp_estatus = "A"';
          $resultmp = setq($sqlmp);
          while($rowmp = $resultmp->fetch_array()){
            echo '<option value="'.$rowmp['pp_id'].'">'.$rowmp['pp_nmb'].'</option>';
          }
        echo '</select>
      </div>
    </div>
  </div>';
  echo '<div class="col-12 col-md-2 mt-2 mb-5" >
    <div class="form-group">
      <label for="agregar" class">Fase</label><br>
      <div class="input-group">
        <select id="fase" name="" class="form-control" onchange="traerproceso();" required>
          <option value="" class="">Selecciona fase</option>
          ';
          /* $sqlmp = 'SELECT * FROM pr_fases WHERE pf_estatus = "A"';
          $resultmp = setq($sqlmp);
          while($rowmp = $resultmp->fetch_array()){
            echo '<option value="'.$rowmp['pf_id'].'">'.$rowmp['pf_nmb'].'</option>';
          } */
        echo '</select>
      </div>
    </div>
  </div>';
  echo '<div class="col-12 col-md-3 mt-2 mb-5" >
    <div class="form-group">
      <label for="agregar" class">Proceso</label><br>
      <div class="input-group">
        <select id="proceso" name="proceso" class="form-control" required>
          <option value="" class="">Selecciona proceso</option>';
          /* $sqlmp = 'SELECT * FROM pr_procesos WHERE ppr_estatus = "A"';
          $resultmp = setq($sqlmp);
          while($rowmp = $resultmp->fetch_array()){
            echo '<option value="'.$rowmp['ppr_id'].'">'.$rowmp['ppr_nmb'].'</option>';
          } */
        echo '</select>
      </div>
    </div>
  </div>
  <script class="">

function traerfases(fase){
var planta = document.getElementById("planta").value;
$.ajax({
  url: "query/traerfases.php",
  method: "POST",
  data: {planta: planta,
        fase: fase},
}).success(function (data){
  document.getElementById("fase").innerHTML = data;
});
}

function traerproceso(){
var planta = document.getElementById("planta").value;
var fase = document.getElementById("fase").value;
$.ajax({
  url: "query/traerprocesos.php",
  method: "POST",
  data: {planta: planta,
        fase: fase},
}).success(function (data){
  document.getElementById("proceso").innerHTML = data;
});
}

</script>
  

  ';


  echo '
  
  <div class="col-md-3 mb-5">
    <label for="" class="">Tipo</label>
    <input type="checkbox" class="flipswitchtipocron" name="tipo">
  </div>

  <div class="col-md-2 mb-5">
    <label for="" class="">Bloqueo</label>
    <input type="checkbox" class="flipswitch2" name="bloqueo">
  </div>


  ';


  echo '
  <div class="mt-2">
    <div class="form-group">
      <label for="" class="">Observaciones</label>
      <textarea name="obs" id="" rows="2" class="form-control" required></textarea>
    </div>
  </div>
  <div class="mt-2">
    <div class="form-group text-center" >
      <label for="save"></label><br>
      <button class="btn btn-primary" type="submit" id="agregar"><i class="fas fa-plus"></i>Agregar</button>
    </div>
  </div>
</div>';
echo '
</div>
';


echo '</form>

</div>';



?>