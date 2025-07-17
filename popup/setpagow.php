<?php
session_start();
  include_once('../funciones.php');

  $monto = busca($_GET['id'],'pedidoscld','pd_pedido','SUM(pd_cantidad*pd_precio)');
  $nocajas = busca($_GET['id'],'pedidoscl','p_id','p_nguias');
  $formaenvio = busca($_GET['id'],'pedidoscl','p_id','p_paqueteria');
  //$costoenvio = $nocajas*busca($formaenvio,'paqueterias','p_id','p_costoguia');
  $costoenvio = busca($_GET['id'],'pedidoscl','p_id','p_envio');
  $adicional = busca($_GET['id'],'pedidoscl','p_id','p_adicionalfp');
  $descuento = busca($_GET['id'],'bonificaciones','b_estatus = "A" AND b_pedido','SUM(b_monto)');
  $monto += $costoenvio;
  $monto += $adicional;
  $monto -= $descuento;
  $monto = busca($_GET['id'],'pedidoscl','p_id','p_total');

   $cliente = busca($_GET['id'],'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)');

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
</script>
   
   ';
echo'
<div class="container mt-1 ">
  <form action="?modulo=pedidosclw&accion=insertpago&id='.$_GET['id'].'" method="post" onsubmit="checksubmit();">
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary">Ingresar Datos del Pago</div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';

  echo '
    <div class="row">
      <div class="col-12 col-md-4">
        <label for"nmbac">Cuenta</label> ';
      $sql = 'SELECT cu_id,cu_nmb FROM cuentas INNER JOIN cortes ON cu_id = c_cuenta
              WHERE c_estatus = "A" AND cu_empresa = "'.$_SESSION['emp'].'" ORDER BY cu_orden';
      $result = setq($sql);
      echo '<select name="cuenta" id="idcuenta" class="form-control" required >';
      echo '<option value="" disabled selected>Elige una cuenta</option>';
      while($row = $result->fetch_array()){
          $selcu = "";
          echo '<option value="'.$row['cu_id'].'" '.$selcu.'>'.$row['cu_nmb'].'</option>';
      }
      echo '</select>';

      echo '</div>
        <div class="col-12 col-md-4">
          <label for"correo">Fecha del pago</label>
          <input type="datetime-local" name="fecha" value="'.date('Y-m-d').'T'.date('H:i:s').'" id="referencia" class="form-control" onfocus="this.select();" required />
        </div>
        <div class="col-12 col-md-4">
          <label for"nmbac">Importe</label>
          <input type="number" min="'.$monto.'" step="0.01" max="'.$monto.'" name="importe" value="" id="importe" class="form-control number-align" placeholder="Importe del gasto" required="required" onfocus="this.select();" title="El importe debe ser menor o igual al saldo disponible" autofocus />
        </div>

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="icon-bars"></i> Registrar pago</button></center>
      </div>
    </div>
  </form>
</div>';
?>