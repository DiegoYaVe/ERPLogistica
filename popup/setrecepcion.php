<?php
/* ini_set('display_errors', 1); */
session_start();
include_once('../funciones.php');

if(isset($_GET['id'])) $recepcion = $_GET['id'];
include_once('../modulos/recepciones.php');
$recepc = new modelrecepciones();
$recepc->select($recepcion);

  $diva = ' checked="checked" ';
  $dtotal = ' checked="checked" ';
  
  if($recepc->id){
    $accion = 'update&idrecepcion='.$recepc->id;
    $title = "Editar";
    $ordenc = $recepc->ordenc;
    $proveedor = $recepc->proveedor;
    $responsable = $recepc->comprado;
    $descuento = $recepc->descuento;
    $responsable = $recepc->comprador; 
    $foliodoc = $recepc->foliodoc;
    $ffin = $recepc->fechacompra;
  }
  else{
    $responsable = $_SESSION['uid'];
    $accion = 'insert';
    $title = "Registrar";
    $descuento = "0.00";
    $ffin = date('Y-m-d');
    $sdesc = "checked";
    $sindescuento = "checked";
    $sindescuento = "checked";

    if(isset($_GET['ordenc'])){
      $ordenc = $_GET['ordenc'];
      $proveedor = busca($_GET['ordenc'],'ordenesc','o_id','o_proveedor');
    }
  }

  if($recepc->cargar == "1") {$tipo = "checked";}else{$tipo = "";}
  if($recepc->diva == "1") {$diva = "checked";}else{$diva = "";}


  $sqlrecepd = 'SELECT * FROM recepcionesd INNER JOIN recepciones ON rd_recepcion = r_id WHERE rd_recepcion = "'.$recepc->id.'"';
  $resultrecepd = setq($sqlrecepd);
  if($resultrecepd->num_rows == 0){
    $readonlymayor = "";
    $hiddenmayor = "";
    $fgroup="mb-5";
  }else{
    $fgroup="mb-1"; 
    $readonlymayor = "readonly";
    $hiddenmayor = "hidden";
  }

echo '
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
</script>';

echo'
<input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />

<div class="container mt-1 ">
  <form action="?modulo=recepciones&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <div class="row">
      <div class="col-12 col-lg-12 alert alert-primary">'.$title.' Recepción</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

    
  echo '
    <div class="row">
      <div class="col-12 col-lg-4 " '.$hiddenmayor.'>
        <div class="mb-5">
          <label for"nmbac">Orden de compra</label>';
          $sql = 'SELECT * FROM ordenesc WHERE o_empresa = "'.$_SESSION['emp'].'" AND o_estatus = "A" ORDER BY o_id DESC';
          $result = setq($sql);
          echo '<select name="ordenc" id="ordenc" required="required" class="form-control" onchange="ordenchange()">';
          echo '<option value="0" >Sin orden de compra</option>';
          while($row = $result->fetch_array()){
            if($ordenc == $row['o_id']) $selo = 'selected'; else $selo = "";

            echo '<option value="'.$row['o_id'].'" '.$selo.'>'.$row['o_folio'].' - '.busca($row['o_proveedor'],'proveedores','p_id','p_alias').' - '.$row['o_fechacom'].'</option>';
          }
  echo '</select></div>
      </div>
      <div class="col-12 col-lg-2 " '.$hiddenmayor.'>
        <div class="mb-5">
          <label for"nmbac">Almacen de depósito</label>';
  $sql = 'SELECT * FROM pr_almacenes WHERE pa_estatus = "A"';
  $result = setq($sql);
  echo '<select name="almacen" id="almacen" required="required" class="form-control">';
  while($row = $result->fetch_array()){
    if($row['pa_id'] == $recepc->almacen) $select = "selected"; else $select = "";
    echo '<option value="'.$row['pa_id'].'" '.$select.'>'.$row['pa_nmb'].'</option>';
  }
  echo '</select></div>
      </div>
      <div class="col-12 col-lg-3 " '.$hiddenmayor.' >
        <div class="mb-5">
          <label for"nmbac">Proveedor</label>';
  $sql = 'SELECT * FROM proveedores WHERE p_estatus = "A"';
  $result = setq($sql);
  echo '<select name="proveedor" id="proveedor" required="required" class="form-control">';
  echo '<option value="" disabled>Elegir proveedor</option>';
  while($row = $result->fetch_array()){
    if($proveedor == $row['p_id']) $selp = 'selected'; else $selp = "";
    echo '<option value="'.$row['p_id'].'" '.$selp.'>'.$row['p_alias'].'</option>';
  }
  echo '</select></div>
      </div>
      <div class="col-6 col-lg-2">
        <div class="'.$fgroup.'">
          <label for"correo">Comprador</label>
          '.menu_select_db('usuarios','u_id','u_nmb',$responsable,'responsable','u_estatus = "A" AND u_id != "ADMIN"',false,false,false,true,"Responsable").'
        </div>
      </div>
      <div class="col-12 col-lg-3" >
        <div class="mb-5">
          <label for"folio">Folio documento</label>
          <input type="number" name="foliodoc" id="foliodoc" value="'.$foliodoc.'" class="form-control" placeholder="Documento del proveedor" required="required" />
        </div>
      </div>

      <div class="col-lg-2">
        <label>Cargar Almacen</label> 
        <input type="checkbox" name="cargar" '.$tipo.' class="flipswitch" />
      </div>
      <div class="col-lg-2">
        <label>Desplegar IVA</label> <br>
        <input type="checkbox" name="diva" '.$diva.' class="flipswitch" />
      </div>

      <div class="col-6 col-lg-2" hidden>
        <div class="mb-5">
          <label for="aplicad">Aplica Descuento</label><br>
          <select name="tipodesc" id="tipodesc" class="form-control">
            <option value="N">Sin Descuento</option>
            <option value="P">Descuento por producto</option>
            <option value="T" selected >Descuento sobre el total</option>
          </select>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <div class="mb-5">
          <label for="desc">% Descuento</label><br>
          <input value="'.$descuento.'" name="desc" type="number" class="form-control">
        </div>
      </div>';
  $sqlna = 'SELECT COUNT(*) FROM activo_producto INNER JOIN articulos ON a_id = ap_producto';
  $resulta = setq($sqlna);
  list($numa) = $resulta->fetch_array();
  if($numa > 0){
    echo '<div class="col-6 col-lg-2" >
        <div class="mb-5">
          <label for="aplicad">Tipo de compra</label><br>
          <select name="tiporec" id="tiporec" class="form-control">
            <option value="A">Articulos</option>
            <option value="C">Activos</option>
          </select>
        </div>
      </div>';
  }
if(!isset($descripcion)){
  $descripcion = "";
}
  echo '
         <div class="col-12 col-lg-2" >
        <div class="mb-5">
          <label for"fini">Fecha de la compra</label>
          <input type="date" name="ffin" id="ffin" value="'.$ffin.'" class="form-control" placeholder="Cuando programas tu actividad" required="required" />
        </div>
      </div>
  <div class="col-12 col-lg-5">
        <div class="mb-5">
          <label for"correo">Observaciones</label>
          <textarea class="form-control" name="descripcion" rows="2" placeholder="Si tienes alguna anotación sobre la compra, anotala " >'.$descripcion.'</textarea>
        </div>
      </div>

      <div id="historialoc" class="col-md-12">
      </div>
  
      <div class="col-12 col-md-12">
        <center><button role="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
      </div>
    </div>
  </form>
</div>';
?>

<script>

function ordenchange(){
  var idoc = document.getElementById("ordenc");
  $.ajax({
    url: "query/buscarcompras.php",
    type: "POST",
    dataType: "html",
    data: {'id' : idoc.value},
  })
  .done(function(data){
    if(idoc != 0){
      var historial = document.getElementById("historialoc");
      historial.innerHTML = data;
    }
  });
}

ordenchange();
</script>