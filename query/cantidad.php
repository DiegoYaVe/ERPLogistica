<?php
include('../funciones.php');

$cantidad = $_POST['cantidad'];
$idprod = $_POST['id'];
$idticket = $_GET['idticket'];

$sql = 'UPDATE pv_ticketsd SET
        ptd_cantidad = "'.$cantidad.'"
        WHERE ptd_ticket = "'.$idticket.'"
        AND ptd_id = "'.$idprod.'"';
setq($sql);


?>