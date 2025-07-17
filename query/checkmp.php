<?php
  session_start();
  include('../funciones.php');

  $mp = $_POST['mp'];
  $datos = array();

  $sql = 'SELECT pmp_color, pmp_tipo FROM pr_materiaprima WHERE pmp_id = "'.$mp.'"';
  $result = setq($sql);
  list($color, $tipo) = $result -> fetch_array();

  $datos['color'] = $color;
  $datos['tipo'] = $tipo;

  echo json_encode($datos);

?>