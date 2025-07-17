<?php
ini_set('display_errors',1);
include_once('../funciones.php');
$dirid = $_GET['dir'];
$accion = 'insertdir';
$titulo = 'Agregar Dirección';
echo '
<form autocomplete="off" method=post name="pro" action="?modulo=clientesw&accion='.$accion.'" onsubmit="return checkSubmitguardar();" class="container">
<div class="row nowrap">
<div class="col-md-12 alert alert-primary text-white">
  '.$titulo.'
</div>';
echo '<input type="hidden" name="id" value="'.$row['d_id'].'">
      <input type="hidden" name="cliente" value="'.$_GET['cliente'].'">';
echo '
<div class="mb-5 col-md-3">
  <label>Calle</label>
  <input type="text" name="calle" size="25" maxlength="80" class="form-control" value="' . $row['d_calle'] . '" required autofocus>
</div>
<div class="mb-5 col-md-2" >
  <label>Número Exterior</label> 
  <input type="text" name="nume" size="15" maxlength="80" class="form-control" value="' . $row['d_nume'] . '" required>
</div>
<div class="mb-5 col-md-2">
<label>Número Interior</label>
<input type="text" name="numi" size="15" maxlength="80" class="form-control" value="' . $row['d_numi'] . '">
</div>
<div class="mb-5 col-md-3">
  <label>Colonia</label> 
  <input type="text" name="colonia" size="15" maxlength="80" class="form-control" value="' . $row['d_colonia'] . '" required>
</div>
<div class="mb-5 col-md-3">
  <label>Codigo postal</label>
  <input type="text" name="cp" size="5" maxlength="5" class="form-control" value="' . $row['d_cp'] . '" required>
</div>
<div class="mb-5 col-md-3">
  <label>Municipio</label> 
  <input type="text" name="municipio" size="25" maxlength="100" class="form-control" value="' . $row['d_municipio']. '" required>
</div>
<div class="mb-5 col-md-3">
<label>Estado</label>';
$sql='SELECT * FROM estado WHERE e_estatus="A"';
$rs=setq($sql) or die($sql);
echo '<select name="estado" id="estado" class="form-control" required>
      <option >SELECCIONAR ESTADO</option>';
  while($rw1=$rs->fetch_array()){
    if($row['d_calle']==$rw1['e_nmb']) $sele='selected';else $sele=NULL;
    echo '<option value="'.$rw1['e_nmb'].'" '.$sele.'>'.$rw1['e_nmb'].'</option>';
  }
echo '
  </select>
</div>
<div>
  <label>Telefono</label>
  <input class="form-control" value="" name="telefono">
</div>
<div class="mb-5 col-md-3">
  <label>Pais</label> 
  <input type="text" name="pais" size="25" maxlength="100" class="form-control" value="' . $row['d_pais']. '" required>
</div>
<div class="mb-5 col-md-3">
  <label>Teléfono</label> 
  <input type="text" name="telefono" size="15" maxlength="15" class="form-control" value="' . $row['d_telefono'] . '">
</div>
<div class="mb-5 col-md-3">
  <label>Referencia</label> 
  <textarea name="referencia" cols="50" rows="2" maxlength="250" class="form-control">' . $row['d_referencia'].'</textarea>
</div>
<div class="mb-5 col-md-3">
  <label>Dirección Predeterminada</label> <br>
  <input type="checkbox" name="predeterminado" class="flipswitch">
</div>';
echo'
<div class="mb-5 col-md-12 text-xs-center">
  <button type="submit" id="guardar" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
</div>
</form>
</div>';

?>
