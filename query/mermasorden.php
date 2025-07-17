<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;

// Verifica si se han enviado datos a través de POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amid = $_POST['amid'];
    $ordenp = $_POST['ordenp'];
    // Recibe los datos del formulario
    foreach ($_POST as $key => $value) {
        if (preg_match('/^check(\d+)$/', $key, $matches)) {
            $cadena = $_POST['check'.$matches[1]];
            $arreglo = explode(",", $cadena);
            $cantidad = $_POST['cantidadd'.$matches[1]];
    
            $sql = 'INSERT INTO articulos_mermas_usados
                    SET amu_amid = "'.$amid.'",
                        amu_cantidad = "'.$cantidad.'",
                        amu_ordenp = "'.$ordenp.'",
                        amu_fecha = "'.date("Y-m-d H:i:s").'"';
            setq($sql);
        }
    }

} else {
    // Si no es una solicitud POST, puedes redirigir o manejar el error según sea necesario
    echo "Error: Este archivo solo acepta solicitudes POST.";
}
?>
