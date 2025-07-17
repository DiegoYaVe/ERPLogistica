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
session_start();
  include_once('../funciones.php');

  if(!isset($_GET['pedido'])) $_GET['pedido'] = NULL;

  include_once('../modulos/pedidosclw.php');
  $pediw = new modelpedidosclw();
  $pediw->select($_GET['pedido']);

  if($pediw->id){
    $accion = 'insertguiapedido&id='.$_GET['pedido'];
    $title = "Añadir una guía al pedido";
  }


echo'
<div class="container mt-1 ">
  <form action="?modulo=pedidosclw&accion='.$accion.'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">'.$title.'</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

  $cliente = busca($pediw->cliente,'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)');
  $sqlde = 'SELECT CONCAT(d_calle," ",d_nume," ",d_numi," ",d_colonia," ",d_cp," ",d_municipio),d_telefono,d_referencia
            FROM direnvio WHERE d_id = "'.$pediw->direccion.'"';
  $resultde = setq($sqlde);
  list($direnvio,$telefono,$referencia) = $resultde->fetch_array();
  echo '
    <div class="row">
      <div class="col-12 col-md-3 form-section text-medium">'.$cliente.'</div>
      <div class="col-12 col-md-8 form-section text-medium">'.$direnvio.' - Ref. '.$referencia.'</div>
      <div class="col-12 col-md-1 form-section">'.$telefono.'</div>
      <div class="col-8 col-md-6">
        <div class="mb-5">
          <label for"nmbac">Paqueteria</label>
          <input type="text" name="paqueteria" value="'.$paqueteria.'" id="paqueteria" class="form-control" placeholder="Nombre de la paqueteria" required="required" onfocus="this.select();" />
        </div>
      </div>
      <div class="col-4 col-md-4" >
        <div class="mb-5">
          <label for"fini">No. Guía</label>
          <input type="text" name="guia" value="'.$guia.'" id="guia" class="form-control" placeholder="Número de guía" required="required" onfocus="this.select();" />
        </div>
      </div>
     <div class="col-4 col-md-2" >
        <div class="mb-5">
          <label for"fini">No. Paquetes</label>
          <input type="text" name="nopaq" value="'.$nopaq.'" id="nopaq" class="form-control" placeholder="Número Paquetes" required="required" onfocus="this.select();" />
        </div>
      </div>

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="icon-bars"></i> Cargar Guía</button></center>
      </div>
    </div>
  </form>
</div>';
?>