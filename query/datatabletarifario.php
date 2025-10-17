<?php
header('Content-Type: application/json; charset=utf-8');
include_once('../funciones.php');
session_start();

// Helpers DataTables
$draw   = isset($_POST['draw'])   ? intval($_POST['draw'])   : 1;
$start  = isset($_POST['start'])  ? intval($_POST['start'])  : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

$orderCol = 0;
$orderDir = 'asc';
if (isset($_POST['order'][0]['column'])) $orderCol = intval($_POST['order'][0]['column']);
if (isset($_POST['order'][0]['dir']))    $orderDir = ($_POST['order'][0]['dir'] === 'desc') ? 'DESC' : 'ASC';

// Mapeo de columnas que SÍ mostramos
$cols = [
  'nombre',            // 0
  'rendimiento',       // 1
  'capacidad_tanque',  // 2
  'var_desgaste',      // 3
  'tipo_combustible',  // 4
  'costo_combustible', // 5
  'acciones'           // 6
];

$sortableMap = [
  0 => 'nombre',
  1 => 'rendimiento',
  2 => 'capacidad_tanque',
  3 => 'var_desgaste',
  4 => 'tipo_combustible',
  5 => 'costo_combustible'
];

$orderBy = isset($sortableMap[$orderCol]) ? $sortableMap[$orderCol] . ' ' . $orderDir : 'nombre ASC';

// Totales base
list($recordsTotal) = setq('SELECT COUNT(*) FROM tarifario_costos WHERE tc_estatus="A"')->fetch_array();

// Filtro
$where = 'WHERE v.tc_estatus = "A"';
if ($search !== '') {
  $s = addslashes($search);
  $where .= ' AND (
      v.UNIDAD LIKE "%'.$s.'%"
      OR v.TIPO_COMBUSTIBLE LIKE "%'.$s.'%"
    )';
}

// Filtrados
list($recordsFiltered) = setq('SELECT COUNT(*)
  FROM v_tarifario_costos v
  '.$where)->fetch_array();

// Datos (ajusta los nombres si tu vista usa otros alias)
$sql = '
SELECT
  v.tc_id                    AS id,
  v.tc_unidad                   AS nombre,
  v.tc_rendimiento             AS rendimiento,
  v.tc_capacidad_tanque        AS capacidad_tanque,
  v.tc_var_desgaste    AS var_desgaste,
  v.tc_tipocombustible        AS tipo_combustible,    -- <- este campo debe existir en la vista
  v.tc_preciocombustible                  AS costo_combustible    -- <- precio combustible unitario
FROM tarifario_costos v
ORDER BY '.$orderBy.'
LIMIT '.$start.', '.$length;

$res = setq($sql);

$data = [];
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $acciones = '<div class="text-end">
      <a href="?modulo=tarifario&accion=show&id='.$row['id'].'"><button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button></a>
      <a href="?modulo=tarifario&accion=delete&id='.$row['id'].'" onclick="return confirm(\'¿Eliminar?\')"><button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></a>
    </div>';

    $data[] = [
      'nombre'            => $row['nombre'],
      'rendimiento'       => (float)$row['rendimiento'],
      'capacidad_tanque'  => (float)$row['capacidad_tanque'],
      'var_desgaste'      => (float)$row['var_desgaste'],
      'tipo_combustible'  => $row['tipo_combustible'],
      'costo_combustible' => (float)$row['costo_combustible'],
      'acciones'          => $acciones,
    ];
  }
}

echo json_encode([
  'draw'            => $draw,
  'recordsTotal'    => intval($recordsTotal),
  'recordsFiltered' => intval($recordsFiltered),
  'data'            => $data
]);
