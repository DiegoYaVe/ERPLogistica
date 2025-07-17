<?php

/* ini_set('display_errors',1); */
include('../funciones.php');

// Crear un array para almacenar los resultados
$data = array();
$idPaqueteria = $_POST['idPaqueteria'];
$cp = $_POST['cp'];
$cob = busca($cp, 'paqueterias_cobertura', 'pc_paqueteria = "'.$idPaqueteria.'" AND pc_cp', 'pc_tipo');

if($cob == "0"){
// Consulta SQL para obtener los datos que deseas
$sql = 'SELECT ps_id AS id, CONCAT(ps_sucursal, " - ", ps_nmb) AS nombre FROM paqueterias_sucursales INNER JOIN paqueterias ON p_id = ps_paqueteria WHERE ps_plaza IN (SELECT DISTINCT(ps_plaza) FROM paqueterias_sucursales WHERE ps_sucursal IN (SELECT pc_sucursal FROM paqueterias_cobertura WHERE pc_cp = "'.$cp.'")) AND ps_ocurre="1" AND p_estatus = "A" AND p_ocurre = 1 AND p_id = "'.$idPaqueteria.'";';
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
} else{
  // No se encontraron resultados
  echo 1;
}

?>