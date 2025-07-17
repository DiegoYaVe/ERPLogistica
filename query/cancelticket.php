<?php
include('../funciones.php');
session_start();

$idticket = $_GET['idtck'];
$idcaja = $_GET['idcaja'];

$sql = 'UPDATE pv_tickets SET
        pt_ucancela = "'.$_SESSION['uid'].'",
        pt_fcancela = "'.date('Y-m-d H:i:s').'",
        pt_estatus = "C"
        WHERE pt_id = "'.$idticket.'" AND pt_caja = "'.$idcaja.'" AND pt_empresa = "'.$_SESSION['emp'].'"';
setq($sql);

echo '
<script>
  window.location.href = "../popup/ticketspv.php?idcaja='.$idcaja.'";
</script>

';

?>