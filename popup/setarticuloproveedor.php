<?php
session_start();
?>
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
</script>
<?php
  include_once('../funciones.php');

  if(!isset($_GET['articulo'])) $_GET['articulo'] = NULL;
  include_once('../modulos/articulos.php');
  $marticulo = new modelarticulos();
  $marticulo->select($_GET['articulo']);
  $accion = 'setproroveedor&id='.$marticulo->id;
  $title = "Agregar un proveedor al producto ".$marticulo->nmb;
  $valorusd = busca($_SESSION['emp'],'empresas','e_id','e_valorusd');

  if($marticulo->ausd == "1") $checkusd = " active "; else $checkusd = "";
  if($marticulo->aiva == "1") $checkiva = " active "; else $checkiva = "";
  $hidd = 'hidden';
  if($valorusd == 0){
    $disabusd = ' readonly="readonly"  ';
    $hidd = '';
    $style = 'style="background:red"';
    $leyusd = ' data-toggle="tooltip" data-placement="top" title="Valor del dolar no definido, vaya al menu configuracion para establecer un precio" ';
  }

echo'
<div class="container mt-1 ">
  <form action="?modulo=articulos&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' </div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

    echo '<div class="row">
          <form action="?modulo=articulos&accion=setproroveedor&id='.$marticulo->id.'" method="post">
              <div class="mb-5 col-md-4">
                <label for="proveedor"><b>Proveedor</b></label>';
    $sql = 'SELECT * FROM proveedores WHERE p_estatus = "A"
            AND p_id NOT IN (SELECT ap_proveedor FROM articulo_proveedor WHERE ap_articulo = "'.$marticulo->id.'" AND ap_activo = "1")
            AND p_empresa = "'.$_SESSION['emp'].'" ORDER BY p_nmb';
    $result = setq($sql);
    echo '<select name="proveedor" id="proveedor" class="form-control" required="required">
          <option value="">¿Con quien compras el producto?</option>';
    while($row = $result->fetch_array()){
      if($marticulo->aproveedor == $row['p_id']) $selp = 'selected'; else $selp = "";
      echo '<option value="'.$row['p_id'].'" '.$selp.'>'.$row['p_alias'].'</option>';
    }
          echo '</select>
              </div>';
    echo '<div class="mb-5 col-md-6 col-lg-2">
            <div class="mb-5">
              <label for="noparte"><b>No. Parte</b></label>
              <input type="text" class="form-control" name="noparteprov" placeholder="No. parte del proveedor" value="'.$marticulo->anoparteprov.'" required="required" />
            </div>
          </div>';

    echo '<div class="mb-5 col-md-6 col-lg-2">
            <div class="mb-5">
              <label for="noparte"><b>Costo</b></label>
              <input type="number" min="0" max="999999" step="0.0001" class="form-control" name="costo" placeholder="Costo del producto" value="'.$marticulo->acosto.'" required="required" />
            </div>
          </div>';
    echo '<div class="mb-5 col-md-1 col-lg-1">
            <div class="mb-5 btn-group btn-group-toggle" data-toggle="buttons">
              <label for="usd">.<i '.$leyusd.' class="icon-question-circle" '.$hidd.'></i></label><br>
              <label class="btn btn-outline-secondary '.$checkusd.'" '.$leyusd.' '.$style.' >
                <input type="checkbox" name="usd" value="0" autocomplete="off" '.$disabusd.'> USD
              </label>
            </div>
          </div>';
    echo '<div class="mb-5 col-md-1 col-lg-1">
            <div class="mb-5 btn-group btn-group-toggle" data-toggle="buttons">
              <label for="iva">.</label><br>
              <label class="btn btn-outline-success '.$checkiva.'">
                <input type="checkbox" name="iva" value="1" autocomplete="off"> +IVA
              </label>
            </div>
          </div>';
    echo '<div class="mb-5 col-md-2 col-lg-2">
            <div class="mb-5" >
              <label for="save">.</label><br>
              <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Guardar</button>
            </div>
          </div>';
    echo '</form>';

?>