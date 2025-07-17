<?php
/*
$modalidad = $_POST['modalidad'];

$fechai =  $_POST['inicio'];

if($modalidad == "1"){

  $fecha = date('Y-m-d', strtotime($fechai.' +1 month'));

}elseif($modalidad == "2"){
  
  $fecha = date('Y-m-d', strtotime($fechai.' +1 year'));


}elseif($modalidad == "3"){

  $fecha = date('Y-m-d', strtotime($fechai.' +1 month'));

}elseif($modalidad == "4"){

  $fecha = date('Y-m-d', strtotime($fechai.' +1 month'));

}elseif($modalidad == "5"){

  $fecha = date('Y-m-d', strtotime($fechai.' +3 month'));

}elseif($modalidad == "6"){

  $fecha = date('Y-m-d', strtotime($fechai.' +3 month'));

}elseif($modalidad == "7"){

  $fecha = date('Y-m-d', strtotime($fechai.' +3 month'));

}elseif($modalidad == "8"){

  $fecha = date('Y-m-d', strtotime($fechai.' +1 year'));

}elseif($modalidad == "9"){

  $fecha = date('Y-m-d', strtotime($fechai.' +1 year'));

}elseif($modalidad == "10"){

  $fecha = date('Y-m-d', strtotime($fechai.' +1 '));

}*/

$diasg = $_POST['diasg'];
$inicio = $_POST['inicio'];

$fecha = date('Y-m-d', strtotime($inicio.' +'.$diasg.' days'));


echo $fecha;