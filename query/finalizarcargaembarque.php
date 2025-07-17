<?php
/* ini_set('display_errors',1); */
include_once('../funciones.php');
$respuesta = 0;
// Obtener el contenido JSON enviado desde la solicitud
$json = file_get_contents('php://input');

// Decodificar el JSON en un objeto PHP
$data = json_decode($json);
$cantidada = 0;
// Verificar si el objeto contiene el arreglo de valores de los checkboxes
if (isset($data->checkboxValues)) {
  // Acceder al arreglo de valores de los checkboxes
  $checkboxValues = $data->checkboxValues;
  
  // Realizar cualquier operación necesaria con los datos
  foreach ($checkboxValues as $value) {
    if (isset($data->cccid)) {
      $cantidad = intval(busca($value, 'guias_imagenes', 'gi_tipo = "E" AND gi_cccid', 'COUNT(*)'));
    } else{
      $cantidad = intval(busca($value, 'guias_imagenes', 'gi_tipo = "E" AND gi_guia', 'COUNT(*)'));
    }
    if($cantidad > 0){
      $respuesta = 0; //Todo correcto
    } else{
      $respuesta = 2; //Una de las guías falta de imágenes
      $cantidada = 1;
      break;
    }
  }
} else {
  $respuesta = 0; //No se encontraron valores de checkbox en los datos.
}

if (isset($data->checkboxValuesga)) {
  // Acceder al arreglo de valores de los checkboxes
  $checkboxValuesga = $data->checkboxValuesga;
  
  // Realizar cualquier operación necesaria con los datos
  foreach ($checkboxValuesga as $value) {

    $cantidad = intval(busca($value, 'garantias_imagenes', 'gi_tipo = "E" AND gi_garantia', 'COUNT(*)'));
    if($cantidad > 0){
      if($cantidada == 1){
        $respuesta = 2; //Una de las guías falta de imágenes
      } else{
        $respuesta = 0; //Todo correcto
      }
    } else{
      $respuesta = 2; //Una de las guías falta de imágenes
      break;
    }
  }
} else{
  if($cantidada == 1){
    $respuesta = 2; //Una de las guías falta de imágenes
  } else {
    $respuesta = 0; //No se encontraron valores de checkbox en los datos.
  }
  
}


echo $respuesta;
?>