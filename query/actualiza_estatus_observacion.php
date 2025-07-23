<?php
session_start();
// ini_set('display_errors', 1);
include('../funciones.php');

$response = array();

if (isset($_POST['id']) && isset($_POST['estatus'])) {
  $id = $_POST['id'];
  $estatus = $_POST['estatus'];

  // Limpieza básica de variables (si no usas funciones como escape)
  $id = trim($id);
  $estatus = trim($estatus);

  // Ejecutar la actualización
  $query = 'UPDATE crm_leads SET cl_observacion = "' . $estatus . '" WHERE cl_id = "' . $id . '"';
  $result = setq($query);

  if ($result) {
    $response['status'] = 'success';
    $response['message'] = 'Estatus actualizado correctamente.';
  } else {
    $response['status'] = 'error';
    $response['message'] = 'Error al ejecutar la consulta SQL.';
  }
} else {
  $response['status'] = 'error';
  $response['message'] = 'Parámetros faltantes.';
}

echo json_encode($response);
?>
