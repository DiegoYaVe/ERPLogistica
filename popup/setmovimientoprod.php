<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');


$selte = "";
$selts = "";
$seltt = "";

if($tipom =="E") $selte = "selected";
elseif($tipom =="S") $selts = "selected";
else $seltt = "selected";




$sql = 'SELECT * FROM pr_almacenes WHERE pa_estatus = "A"';
$resultd = setq($sql);
/*$grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
 if($grupo != "ADMIN"){
  $almacen = busca($_SESSION['uid'],'usuarios','u_id','u_almacen');
  $sql.= ' AND a_id = "'.$almacen.'" ';
} */
$result = setq($sql);

echo  '

<script language="JavaScript">
function checkSubmitmovimiento() {
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function valida() {
  var strUser = document.getElementById("tipo").value;

  var fila = document.getElementById("almdestino");
 if (strUser == "T") {
    fila.style.display = "";
    document.getElementById("destino").setAttribute("required","required");
  }
  else {
    fila.style.display = "none";
    document.getElementById("destino").value = "";
    document.getElementById("destino").removeAttribute("required","");
  }
}
</script>

';

echo '<div class="col-10">
<form method="post" action="?modulo=movimientosprod&accion=insert">
<div class="">
  <div class="row">
    <div class="alert alert-primary col-md-12">Registrar un movimiento de almacen</div>
  </div>
    <div class="row">
      <div class="col-xs-4 col-md-3">
        <div class="mb-5">
          <label for="tipomov">Tipo de movimiento:</label>
          <select class="form-control" name="tipo" id="tipo" onchange="valida()">
            <option value="E">Entrada</option>
            <option value="S">Salida</option>
            <option value="T">Traspaso</option>
          </select>
        </div>
      </div> ';

$result = setq($sql);
echo '<div class="col-xs-4 col-md-4">
  <div class="mb-5">
    <label for="almacen">Almacen movimiento:</label>';
echo '<select name="almacen" id="origen" required class="form-control" >';
while ($row = $result->fetch_array()){
  echo '<option value="'.$row['pa_id'].'">'.$row['pa_nmb'].'</option>';
}
echo '</select>
  </div>
</div>

<div class="col-xs-4 col-md-3" id="almdestino" style="display:none">
  <div class="mb-5">
    <label for="almacen">Almacen Destino:</label>';
echo '<select name="destino" id="destino" required class="form-control" >';
//        $result = setq($sql);
while ($row = $resultd->fetch_array()){
  echo '<option value="'.$row['pa_id'].'">'.$row['pa_nmb'].'</option>';
}
echo '</select>
  </div>
</div>
    <div class="col-xs-4 col-md-4">
      <div class="mb-5">
        <label for="almacen">Motivo del movimiento:</label>
          <input type="text" class="form-control" name="motivo" id="motivo" placeholder="Describe el motivo del movimiento" required="true"  />
      </div>
    </div>
    <div class="col-xs-4 col-md-1" >
      <div class="mb-5">
        <label for="almacen">Guardar:</label><br>
          <button class="btn btn-primary"><i class="fa fa-save"></i></button>
      </div>
    </div>
  </div>
</div>
</div></form>';




?>