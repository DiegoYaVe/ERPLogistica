<?php
//ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
include_once('../funciones.php');
session_start();

// Parámetros DataTables
$draw   = isset($_POST['draw'])   ? intval($_POST['draw'])   : 1;
$start  = isset($_POST['start'])  ? intval($_POST['start'])  : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

$orderCol = 0;
$orderDir = 'asc';
if (isset($_POST['order'][0]['column'])) $orderCol = intval($_POST['order'][0]['column']);
if (isset($_POST['order'][0]['dir']))    $orderDir = ($_POST['order'][0]['dir'] === 'desc') ? 'DESC' : 'ASC';

// Mapeo columnas
$sortableMap = [
  0 => 'od_origen',
  1 => 'od_destino',
  2 => 'od_km'
];

$orderBy = isset($sortableMap[$orderCol]) ? $sortableMap[$orderCol] . ' ' . $orderDir : 'od_origen ASC';

// Totales
list($recordsTotal) = setq('SELECT COUNT(*) FROM od_kilometros WHERE od_estatus="A"')->fetch_array();

// WHERE (filtro búsqueda)
$where = 'WHERE od_estatus="A"';
if ($search !== '') {
  $s = addslashes($search);
  $where .= ' AND (od_origen LIKE "%'.$s.'%" OR od_destino LIKE "%'.$s.'%")';
}

// Filtrados
list($recordsFiltered) = setq('SELECT COUNT(*) FROM od_kilometros '.$where)->fetch_array();

// Datos
$sql = 'SELECT od_id, od_origen, od_destino, od_km
        FROM od_kilometros
        '.$where.'
        ORDER BY '.$orderBy.'
        LIMIT '.$start.', '.$length;
$res = setq($sql);

$data = [];
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $acciones = '<div class="text-end">
      <a href="?modulo=odkm&accion=show&id='.$row['od_id'].'">
        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
      </a>
      <a href="?modulo=odkm&accion=delete&id='.$row['od_id'].'"
         onclick="return confirm(\'¿Eliminar?\')">
        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
      </a>
    </div>';

    $data[] = [
      'origen'   => $row['od_origen'],
      'destino'  => $row['od_destino'],
      'km'       => (float)$row['od_km'],
      'acciones' => $acciones
    ];
  }
}

echo json_encode([
  'draw'            => $draw,
  'recordsTotal'    => intval($recordsTotal),
  'recordsFiltered' => intval($recordsFiltered),
  'data'            => $data
]);
