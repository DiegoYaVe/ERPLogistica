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

echo'<input type="hidden" id="empresa" value="'.$_SESSION['emp'].'" />

<div class="container mt-1 ">
  <form action="?modulo=cxcobrar&accion=inserttae" method="post" onsubmit="return checksubmitaplicar();">
    <div class="table">
      <table class="table table-striped">
        <thead class="thead bg-blue bg-accent-3 text-white pt-1 pb-1">
          <tr>
          <th></th>
          <th>Cliente</th>
          <th>Monto</th>
          <th>Fecha</th>
          </tr>
        </thead>';
  $sql = 'SELECT * FROM tae WHERE t_estatus = "N" ORDER BY t_fecha ASC ';
  $resultae = setq($sql);
  $y = 1;
  while($rwt = $resultae->fetch_array()){
    echo '<tr>
            <td><input type="checkbox" name="tae'.$rwt['t_id'].'" id="ch'.$x.$y.'"/></td>
            <td>'.busca($rwt['t_cliente'],'clientestaed','ct_ctae','ct_nmbtae').'</td>
            <td>$ '.number_format($rwt['t_importe'],2).'</td>
            <td>'.$rwt['t_fecha'].'</td>
          </tr>';
    $y++;
  }
echo '<tr><td colspan="4"><button class="btn btn-success" id="aplicar">Guardar</button></td></tr>
</table>
    </div>
  </form>
</div>';


?>