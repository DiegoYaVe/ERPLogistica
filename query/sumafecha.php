<?php

$temporalidad = $_POST['temporalidad'];

$fechai =  $_POST['fechai'];

if($temporalidad == "A"){

  $fecha = date('Y-m-d', strtotime($fechai.' +7 days'));

}elseif($temporalidad == "U"){
  
  $fecha = $fechai;

}elseif($temporalidad == "M"){

  $fecha = date('Y-m-d', strtotime($fechai.' +1 month'));

}

echo $fecha;