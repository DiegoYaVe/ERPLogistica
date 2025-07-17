<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$response = array();

// Verifica si se recibieron los datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera los datos del formulario
    $id = $_POST["id"];
    $retro = $_POST["retro"];
    $cantidad = $_POST["cantidad"];
    $rutaFinal = '';

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = $_FILES['archivo']['name'];
        $rutaTemporal = $_FILES['archivo']['tmp_name'];
        $carpetaDestino = '../img/evidenciastemporizador/'; // Reemplaza con la ruta donde deseas guardar el archivo
        $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato predeterminado
        $fechaHoraSinGuionesNiPuntos = str_replace(array("-", ":", " "), "", $fechaHora);

        $rutaFinal = $carpetaDestino . $fechaHoraSinGuionesNiPuntos . $nombreArchivo;

        if (move_uploaded_file($rutaTemporal, $rutaFinal)) {
            //El archivo se ha movido exitosamente a la carpeta de destino
            // Ahora puedes guardar la ruta de la carpeta en la base de datos o realizar otras acciones necesarias
            $response['respuesta'] = 0;
        } else {
            //Error al mover el archivo
            $response['respuesta'] = 2;
        }
    }

    // Prepara la consulta para insertar los datos
    /* $query = "UPDATE pr_procesoexe SET ppx_obs = '".$retro."', ppx_cantidad = '".$cantidad."', ppx_adj = '".$rutafinal."' WHERE ppx_id = '".$id."'";
    setq($query); */
    $response['retro'] = $retro;
    $response['cantidad'] = $cantidad;
    $response['adj'] = $rutaFinal;
} else {
    //Acceso no permitido
    $response['respuesta'] = 1;
}
echo json_encode($response);
?>
