<script language="javascript">
function checksubmit(){
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function cargar(){
  opener.location.reload();
  window.close();
}
function closewindow(){
   window.opener.location.reload();
   window.close();
}
function calculaimp(){
  var modal = document.getElementById("modalidad").value;
  var punit = document.getElementById("mod" + modal).value;
  var cant = document.getElementById("cantidad").value;

  var preciou = parseFloat(punit);
  var cant = parseFloat(cant);

  var importe = preciou*cant;
  document.getElementById("importe").value = importe.toFixed(2);
  calculapago();
}
function calculapago(){
  var fini = document.getElementById("fini").value;
  var diasgracia = document.getElementById("diasgracia").value;
  var ffin = document.getElementById("fpago");
  $.ajax({
    url: "query/sumafechalicencia.php",
    type: "POST",
    dataType: "html",
    data: { 'inicio' : fini,
            'diasg' : diasgracia},
  })
  .done(function(data){
    ffin.value = data;
  });

}
/*
function calculapago2() {
  var modal = document.getElementById("modalidad").value;
  var fin = document.getElementById("fpago");
  var inicio = document.getElementById("fini").value;
  $.ajax({
    url: "query/sumafechalicencia.php",
    type: "POST",
    dataType: "html",
    data: {'modalidad' : modal,
            'inicio' : inicio},
  })
  .done(function(data){
    fin.value = data;
  });
} */

</script>
<?php

session_start();
  include_once('../funciones.php');

  include_once('../modulos/licencias.php');
  $licen = new modellicencias();
  $licen->select($_GET['idlic']);

  include_once('../modulos/clientes.php');
  $mcliente = new modelclientes();

  if($licen->id){
    $cliente = $licen->cliente;
    $mcliente->select($licen->cliente);
    $nmbact = $licen->nmb;
    $fini = $licen->fini;
    $accion = 'agregarlicencia&id='.$licen->id;
    $title = "Agregar una ";
    $diasgracia = $licen->diasgracia;
  }
  $today = date('Y-m-d');
  $flic = max($fini,$today);
  $fpago = date('Y-m-d',strtotime($flic.'+'.$diasgracia.' days'));
  $unitario = busca(1,'licencias_modalidades','lm_id','lm_precio');
  $importe = $unitario;


echo'
<div class="container mt-1 ">
  <form action="?modulo=licencias&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <input type="hidden" name="cliente" value="'.$cliente.'" />
    <input type="hidden" id="diasgracia" value="'.$diasgracia.'" />

    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary"><label class="modaltext">'.$title.' Licencia</label></div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

  echo '
    <div class="row">
      <div class="col-12 col-md-3 ">
        <div class="mb-5">
          <label for"nmbac">Modalidad de licencia</label>';
  $sqlm = 'SELECT * FROM licencias_modalidades WHERE lm_estatus = "A"';
  $resultm = setq($sqlm);
  while($row = $resultm->fetch_array()){
    echo '<input type="hidden" name="precio'.$row['lm_id'].'" id="mod'.$row['lm_id'].'" value="'.$row['lm_precio'].'">';
    echo '<input type="hidden" name="articulo'.$row['lm_id'].'" id="mod'.$row['lm_id'].'" value="'.$row['lm_producto'].'">';
  }

  echo '<select name="modalidad" id="modalidad" onchange="calculaimp();" class="form-control">';
  $resultm = setq($sqlm);
  $idlm = 0;
  while($row = $resultm->fetch_array()){
    $idlm++;
    if($idlm == 1) $sel = 'selected'; else $sel = '';

    echo '<option value="'.$row['lm_id'].'" '.$sel.'>'.$row['lm_nmb'].'</option>';

  }
    echo '</select></div>
      </div>
      <div class="col-6 col-md-2" hidden >
        <div class="mb-5">
          <label for"correo">Numero de licencias</label>
          <input type="number" min="1" class="form-control" max="99999" step="1" value="1" name="cantidad" id="cantidad" required="required" placeholder="Cantidad de licencias" onchange="calculaimp();"/>
        </div>
      </div>
      <div class="col-12 col-md-3" >
        <div class="mb-5">
          <label for"fini">Fecha inicio de la licencia</label>
          <input type="date" name="fini" id="fini" min="'.date('Y-m-d',strtotime($flic)).'" value="'.$flic.'" class="form-control" placeholder="Arranque del contrato de licencia" required="required" onchange="calculapago();" />
        </div>
      </div>

      <div class="col-12 col-md-2" >
        <div class="mb-5">
          <label for"fpago">Fecha limite de pago</label>
          <input type="date" name="fpago" id="fpago" readonly="readonly" value="'.$fpago.'" class="form-control" placeholder="Arranque del contrato de licencia" required="required" />
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="mb-5">
          <label for"correo">Importe</label>
          <input type="number" min="0" class="form-control" max="99999" step="0.01" onfocus="this.select();" value="'.$importe.'" name="importe" id="importe" required="required" placeholder="Importe de las licencias" />
        </div>
      </div>

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Crear</button></center>
      </div>
    </div>
  </form>
</div>';
?>