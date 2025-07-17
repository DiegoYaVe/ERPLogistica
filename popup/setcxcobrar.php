<?php
session_start();
  include_once('../funciones.php');

  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  include_once('../modulos/cxcobrar.php');
  $cxc = new modelcxcobrar();
  $cxc->select($_GET['id']);

    $accion = 'insertcxcobrar';
    $title = "Insertar una ";
    $seldis = "selected";
    $cxc->fecha = date('Y-m-d').'T'.date('H:i:s');
    $cxc->monto = $cxc->importe - $cxc->abonado;

echo'

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

';
echo'
<div class="container mt-1 ">
  <form action="?modulo=cxcobrar&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.' Cuenta por Cobrar</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
  echo '
    <div class="row">
      <div class="col-12 col-md-5">
        <div class="mb-5">
          <label for"nmbac">Selecciona un cliente</label>';

  if(busca($_SESSION['uid'],'usuarios','u_id','u_grupo') == "USER"){
    $sqlac = ' AND c_almacen = "'.busca($_SESSION['uid'],'usuarios','u_id','u_almacen').'" ';
  }
  else $sqlac = "";
  $sql = 'SELECT c_id,c_alias,a_nmb FROM crm_clientes INNER JOIN almacenes ON a_id = c_almacen
          WHERE c_empresa = "'.$_SESSION['emp'].'" '.$sqlac.' ORDER BY c_almacen,c_nmb';
  $result = setq($sql) or die($sql);
  echo '<select class="form-control" searchable="Buscar cliente" name="cliente" required >';
  echo '<option value="" disabled '.$seldis.'>Elegir Cliente</option>';
  while($row = $result->fetch_array()){
    if($row['c_id'] == $cxc->cliente) $sel = 'selected'; else $sel = "";

    echo '<option value="'.$row['c_id'].'" '.$sel.'>'.$row['c_alias'].' - '.$row['a_nmb'].'</option>';
  }
  echo '</select>';

  echo '</div>
      </div>
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"nmbac">Importe de la Cuenta por Cobrar</label>
          <input type="number" min="0" max="99999999" step="0.01" name="importe" value="'.$cxc->monto.'" id="nmb" class="form-control" placeholder="Importe del ingreso" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-12 col-md-4" >
        <div class="mb-5">
          <label for"fini">Tipo de Cobro</label>';
  $sql = 'SELECT ct_clave,ct_descripcion FROM cortes_tipom
          WHERE ct_tipo = "X" ORDER BY ct_descripcion';
  $result = setq($sql);
  echo '<select name="tipo" id="tipo" class="form-control">';
  while($row = $result->fetch_array()){
    if($row['ct_clave'] == $cxc->tipo) $selcu = 'selected'; else $selcu = "";

    echo '<option value="'.$row['ct_clave'].'" '.$selcu.'>'.$row['ct_descripcion'].'</option>';
  }
  echo '</select>';

  echo '</div>
      </div>
      <div class="col-6 col-md-4">
        <div class="mb-5">
          <label for"correo">Observaciones</label>
          <input type="text" name="observaciones" value="'.$cxc->observaciones.'" id="observaciones" class="form-control" placeholder="Observaciones" onfocus="this.select();" required />
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="mb-5">
          <label for"correo">Fecha de la cuenta por cobrar</label>
          <input type="datetime-local" name="fecha" value="'.$cxc->fecha.'" id="referencia" class="form-control" onfocus="this.select();" required />
        </div>
      </div>
      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="icon-bank"></i> GUARDAR </button></center>
      </div>
    </div>
  </form>
</div>';
?>