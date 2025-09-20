<?php
//ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
include_once('../funciones.php');
session_start();

// Helpers de DataTables
$draw   = isset($_POST['draw'])   ? intval($_POST['draw'])   : 1;
$start  = isset($_POST['start'])  ? intval($_POST['start'])  : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

$orderCol = 0;
$orderDir = 'asc';
if (isset($_POST['order'][0]['column'])) $orderCol = intval($_POST['order'][0]['column']);
if (isset($_POST['order'][0]['dir']))    $orderDir = ($_POST['order'][0]['dir'] === 'desc') ? 'DESC' : 'ASC';

// Mapeo de columnas mostradas -> alias SQL
$cols = [
  'unidad',            // 0
  'km',                // 1
  'rendimiento',       // 2
  'precio',            // 3
  'capacidad_tanque',  // 4
  'tanques',           // 5
  'combustible',       // 6
  'casetas',           // 7
  'var_desgaste',      // 8
  'desgaste',          // 9
  'operador',          // 10
  'total',             // 11
  'venta',             // 12
  'acciones'           // 13 (no ordenable)
];

$sortableMap = [
  0 => 'unidad',
  1 => 'km',
  2 => 'rendimiento',
  3 => 'precio',
  4 => 'capacidad_tanque',
  5 => 'tanques',
  6 => 'combustible',
  7 => 'casetas',
  8 => 'var_desgaste',
  9 => 'desgaste',
 10 => 'operador',
 11 => 'total',
 12 => 'venta'
];

$orderBy = isset($sortableMap[$orderCol]) ? $sortableMap[$orderCol] . ' ' . $orderDir : 'tc_id DESC';

// Totales
list($recordsTotal) = setq('SELECT COUNT(*) FROM tarifario_costos WHERE tc_estatus="A"')->fetch_array();

// Filtro
$where = 'WHERE v.tc_estatus = "A"';
if ($search !== '') {
  $s = addslashes($search);
  // amplía aquí si quieres buscar en más columnas
  $where .= ' AND (v.UNIDAD LIKE "%'.$s.'%")';
}

// Filtrados
list($recordsFiltered) = setq('SELECT COUNT(*) 
  FROM v_tarifario_costos v
  '.$where)->fetch_array();

// Datos
$sql = '
SELECT
  v.tc_id AS id,
  v.UNIDAD AS unidad,
  v.KM AS km,
  v.RENDIMIENTO AS rendimiento,
  v.PRECIO AS precio,
  v.CAPACIDAD_TANQUE AS capacidad_tanque,
  v.TANQUES AS tanques,
  v.COMBUSTIBLE AS combustible,
  v.CASETAS AS casetas,
  v.VARIABLE_DE_DESGASTE AS var_desgaste,
  v.DESGASTE AS desgaste,
  v.OPERADOR AS operador,
  v.TOTAL_COSTO_DVL AS total,
  v.VENTA_DVL AS venta
FROM v_tarifario_costos v
'.$where.'
ORDER BY '.$orderBy.'
LIMIT '.$start.', '.$length;

$res = setq($sql);

$data = [];
if ($res) {
  while ($row = $res->fetch_assoc()) {
    // Devolvemos datos crudos (numéricos) para que DataTables pueda ordenar numéricamente
    $acciones = '<div class="text-end">
      <a href="?modulo=tarifario&accion=show&id='.$row['id'].'"><button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button></a>
      <a href="?modulo=tarifario&accion=delete&id='.$row['id'].'" onclick="return confirm(\'¿Eliminar?\')"><button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></a>
    </div>';

    $data[] = [
      'unidad'           => $row['unidad'],
      'km'               => (float)$row['km'],
      'rendimiento'      => (float)$row['rendimiento'],
      'precio'           => (float)$row['precio'],
      'capacidad_tanque' => (float)$row['capacidad_tanque'],
      'tanques'          => (float)$row['tanques'],
      'combustible'      => (float)$row['combustible'],
      'casetas'          => (float)$row['casetas'],
      'var_desgaste'     => (float)$row['var_desgaste'],
      'desgaste'         => (float)$row['desgaste'],
      'operador'         => (float)$row['operador'],
      'total'            => (float)$row['total'],
      'venta'            => (float)$row['venta'],
      'acciones'         => $acciones,
    ];
  }
}

echo json_encode([
  'draw'            => $draw,
  'recordsTotal'    => intval($recordsTotal),
  'recordsFiltered' => intval($recordsFiltered),
  'data'            => $data
]);
?>