<?php

/* ini_set('display_errors',1); */
include('../funciones.php');

// Crear un array para almacenar los resultados
$data = array();
$origen = $_POST['origen'];

// Consulta SQL para obtener los datos que deseas
$sql = 'SELECT rd_id, rd_nombre AS nombre FROM ruta_destino INNER JOIN ruta_tarifario ON rt_destino = rd_id WHERE rt_origen = "'.$origen.'";';
$result = setq($sql);

if ($result->num_rows > 0) {

    // Obtener filas de resultados y agregarlas al array  
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Devolver los resultados como JSON
    echo json_encode($data);
} else {
    // No se encontraron resultados
    echo 1;
}


?>