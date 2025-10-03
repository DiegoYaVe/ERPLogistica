<?php

/* ini_set('display_errors',1); */
include('../funciones.php');

// Crear un array para almacenar los resultados
$data = array();
$origen = $_POST['origen'];
$destino = $_POST['destino'];

// Consulta SQL para obtener los datos que deseas
$sql = 'SELECT * FROM ruta_tarifario WHERE rt_origen = "'.$origen.'" AND rt_destino = "'.$destino.'";';
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