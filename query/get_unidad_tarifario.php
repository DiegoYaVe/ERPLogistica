<?php
//ini_set('display_errors', 1);
include('../funciones.php');
header('Content-Type: application/json; charset=utf-8');

$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

if ($id <= 0) {
  echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
  exit;
}

$sql = "
  SELECT 
    tc_id,
    tc_unidad,
    tc_km,
    tc_rendimiento,
    tc_capacidad_tanque,
    tc_casetas,
    tc_var_desgaste,
    tc_tipocombustible,
    tc_preciocombustible
  FROM tarifario_costos
  WHERE tc_id = $id AND tc_estatus = 'A'
  LIMIT 1
";

$result = setq($sql);

if ($row = $result->fetch_assoc()) {
  $data = [
    'ok' => true,
    'tc_id' => (int)$row['tc_id'],
    'tc_unidad' => $row['tc_unidad'],
    'tc_km' => (float)$row['tc_km'],
    'tc_rendimiento' => (float)$row['tc_rendimiento'],
    'tc_capacidad_tanque' => (float)$row['tc_capacidad_tanque'],
    'tc_casetas' => (float)$row['tc_casetas'],
    'tc_var_desgaste' => (float)$row['tc_var_desgaste'],
    'tc_tipocombustible' => $row['tc_tipocombustible'],
    'tc_preciocombustible' => (float)$row['tc_preciocombustible']
  ];
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
  echo json_encode(['ok' => false, 'msg' => 'No se encontró la unidad']);
}
?>
