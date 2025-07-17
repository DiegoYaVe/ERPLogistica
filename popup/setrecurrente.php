<script language="javascript">
function checksubmit(){
  var repetir = document.getElementById("recurrencia");
  var sem = document.getElementById('sem');
  var mes = document.getElementById('mes');
  var anual = document.getElementById('anual');
  var sem1 = document.getElementById('semanal');
  var mes1 = document.getElementById('mensual');
  var anual1 = document.getElementById('prox');
  var error = 1;
  if(repetir.value !== ""){
    if(sem.style.display === "inline"){
      if(sem1.value === "") $("#semanal").css("border", "solid 2px red");
      else error = 0;
    }
    
    if(mes.style.display === "inline"){
      if(mes1.value === "") $("#mensual").css("border", "solid 2px red");
      else error = 0;
    }
    if(anual.style.display === "inline"){
      if(anual1.value === "") $("#prox").css("border", "solid 2px red");
      else console.log("anual vacio");//error = 0;
    }

    if(error === 0){
      document.getElementById("guardar").value = "JD";
      document.getElementById("guardar").disabled = true;
      document.formrecurrente.submit();
    }
  }else{
    $("#recurrencia").focus();
    $("#recurrencia").css("border", "solid 2px red");
  }
}
function cargar(){
  opener.location.reload();
  window.close();
}
function closewindow(){
   window.opener.location.reload();
   window.close();
}
function checkRecurrecia(sel){
  $("#recurrencia").css("border", "");
  if(sel.value == 1){
    document.getElementById('sem').style.display = "inline";
    document.getElementById('mes').style.display = "none";
    document.getElementById('anual').style.display = "none";
    $("#semanal").prop('required','true');
    $("#anual").prop('required','false');
    $("#mes").prop('required','false');
  }else if(sel.value == 2){
    document.getElementById('sem').style.display = "none";
    document.getElementById('mes').style.display = "none";
    document.getElementById('anual').style.display = "none";
    $("#sem").prop('required','false');
    $("#anual").prop('required','false');
    $("#mes").prop('required','false');
  }else if(sel.value == 3 || sel.value == 4 || sel.value == 5){
    document.getElementById('sem').style.display = "none";
    document.getElementById('mes').style.display = "inline";
    document.getElementById('anual').style.display = "none";
    $("#sem").prop('required','false');
    $("#anual").prop('required','false');
    $("#mes").prop('required','true');
  }else if(sel.value == 6){
    document.getElementById('sem').style.display = "none";
    document.getElementById('mes').style.display = "none";
    document.getElementById('anual').style.display = "inline";
    $("#sem").prop('required','false');
    $("#anual").prop('required','true');
    $("#mes").prop('required','false');
  }
}
</script>
<?php
session_start();
  include_once('../funciones.php');
  $accion = 'recurrente';
  $title = "Registrar";
  if(isset($_GET['tablero'])) $tablero = $_GET['tablero'];
  if(isset($_GET['remision'])) $remision = $_GET['remision'];

echo'
<div class="container mt-1 ">
  <form action="?modulo=remisiones&accion='.$accion.'&id='.$remision.'" method="post" name="formrecurrente">
    <input type="hidden" name="idtablero" id="idtablero" value="'.$tablero.'" readonly />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">Remisión Recurrente</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
  $tab = busca($tablero,'crm_tableros','ct_id','CONCAT(ct_folio," - ",ct_nmb)');
  $rem = busca($remision,'remisiones','r_id','r_nmb');
  echo '
    <div class="row">
      <div class="col-12 col-md-6">
        <div class="mb-5">
          <label for"tablero">Tablero</label>
          <input type="text" id="tablero" name="tablero" value="'.$tab.'" class="form-control" placeholder="Nombre del tablero" readonly>
        </div>
      </div>';
      echo '
      <div class="col-12 col-md-6" >
        <div class="mb-5">
          <label>Remisión</label>
          <input type="text" id="rem" name="rem" value="'.$rem.'" class="form-control" placeholder="Nombre de la remisión" readonly>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-5">
          <label>Nombre de la remisión recurrente</label>
          <input type="text" id="nmbrem" name="nmbrem" value="'.$rem.'" class="form-control" placeholder="Nombre de la remisión recurrente">
        </div>
      </div>';
      echo '
      <div class="col-12 col-md-12 alert alert-primary" '.$style.'>Información de la próxima remisión</div>
      <div class="col-12 col-md-4" '.$style.'>
        <div class="mb-5">
          <label for"destino">Repetir</label>
          <select onchange="checkRecurrecia(this);" name="recurrencia" id="recurrencia" class="form-control" >
            <option value="" disabled selected>Selecciona una opción...</option>
            <option value="1">Semanal</option>
            <option value="2">Quincenal</option>
            <option value="3">Mensual</option>
            <option value="4">Trimestral</option>
            <option value="5">Semestral</option>
            <option value="6">Anual</option>
          </select>
        </div>
      </div>';
      echo'
      <div class="col-6 col-md-4" id="sem" style="display:none">
        <div class="mb-5">
          <label for"semanal">Día de la semana</label>
          <select name="semanal" id="semanal" class="form-control" >
            <option value="" disabled selected>Selecciona un día de la semana...</option>
            <option value="1">Lunes</option>
            <option value="2">Martes</option>
            <option value="3">Miercoles</option>
            <option value="4">Jueves</option>
            <option value="5">Viernes</option>
            <option value="6">Sábado</option>
            <option value="7">Domingo</option>
          </select>
        </div>
      </div>';
      echo'
      <div class="col-12 col-md-4" id="mes" style="display:none">
        <div class="mb-5">
          <label for"destino">Día del mes</label>
          <select name="mensual" id="mensual" class="form-control">
            <option value="" disabled selected>Selecciona un día del mes...</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
          </select>
        </div>
      </div>';
      $año = date('Y');
      echo'
      <div class="col-12 col-md-4" id="anual" style="display:none">
        <div class="mb-5">
          <label for"destino">Fecha de siguiente aparición</label>
          <input type="date" name="prox" id="prox" min="'.date(($año+1).'-01-01').'" max="'.date(($año+1).'-12-31').'" class="form-control" />
        </div>
      </div>
      <div class="col-12 col-md-12" '.$style.'>
        <center><button type="button" class="btn btn-primary" id="guardar" onclick="checksubmit();"><i class="fa fa-save"></i> Guardar</button></center>
      </div>
    </div>
  </form>
</div>';
?>