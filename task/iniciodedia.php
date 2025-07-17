<?php
include_once('funciones.php');

reasignarLeads();
validaestatus("corte");
validaestatus("articulos");
validaestatus("cotizaciones");
validaestatus("remisiones");


echo 'iniciodedia.php -> ok';

?>