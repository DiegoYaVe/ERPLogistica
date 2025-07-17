<?php
session_start();
ini_set('display_errors',1);
include_once('../funciones.php');

echo '

<script language="JavaScript">
function checkSubmitedit() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
</script>

';

$idlinean = $_GET['id'];
if($idlinean){
  $sql = 'SELECT * FROM lineas_negocio WHERE ln_id = "'.$idlinean.'" AND ln_empresa = "'.$_SESSION['emp'].'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  
}

if($idlinean) $accion = 'update'; else $accion = 'insert';
if(!$idlinean) $titulo = 'Agregar'; else $titulo = 'Actualizar';


if($row['ln_estatus'] == "A" || $accion == "insert") $check = 'checked="checked"'; else $check = "";
echo '
      <div class="row">
      <div class="alert alert-primary col-md-12">'.$titulo.' linea de negocios </div>
      <form method="post" action="?modulo=lineasn&accion='.$accion.'">
        <input type="hidden" value="'.$idlinean.'" name="id" />
        <div class="col-md-4">
          <label>Nombre de la Linea: </label>
          <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la linea" name="nmb" value="'.$row['ln_nmb'].'" required="required" >
        </div>
        <div class="col-md-4">
          <label>% de Utilidad (Promedio):</label>
          <input type="number" name="utilidad" data-toggle="tooltip" data-placement="top" data-original-title="Determina el porcentaje aproximado de utilidad por cada venta de ese producto"  min="0" step="1" max="9999" class="form-control" placeholder="% Utilidad" required="required" value="'.$row['ln_utilidad'].'"  />
        </div>
        <div class="col-md-2">
          <label>Abreviatura:</label>
          <input type="text" id="abrevia" class="form-control mayus" data-toggle="tooltip" data-placement="top" data-original-title="Determina un nombre corto de tu Linea de negocio, 3 Carácteres" placeholder="Siglas" name="abrevia" minlength="3" maxlength="3" value="'.$row['ln_alias'].'" required="required"  >
        </div>
        <div class="col-md-1">
          <label>Color: </label> <br>
          <input type="color" id="color" name="color" required="required"  data-toggle="tooltip" data-placement="top" data-original-title="Elije un color para que se muestre en tus reportes gráficos" value="'.$row['ln_color'].'"  >
        </div>
        <div class="col-md-1">
          <label>Guardar: </label> <br>
          <button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i>
        </div>
      </form>
      </div>';




?>