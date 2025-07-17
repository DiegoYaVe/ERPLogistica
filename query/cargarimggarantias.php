<?php
/* ini_set('display_errors', 1); */
include_once('../funciones.php');

$respuesta = 0;
if(isset($_POST['articulo'])){
 $articulo = $_POST['articulo'];
} else{
  $guia = $_POST['guia'];
}
$tipog = $_POST['tipo'];
if ($tipog == "P") {
  $modulo = "guias";
  $ruta = '../img/guias/preparacion/';
} else {
  $modulo = "embarcamiento";
  $ruta = '../img/guias/embarque/';
}

if ($_FILES['archivos']['name']) {  
  for ($i = 0; $i <= count($_FILES['archivos']['name']); $i++) {
    //Recogemos el archivos enviado por el formulario
    $archivos = $_FILES['archivos']['name'][$i];
    //echo $archivos."\n";
    if(isset($_POST['articulo'])){
      $max = getmax('gi_orden', 'garantias_imagenes', 'gi_cccid = "' . $articulo . '" AND gi_tipo = "' . $tipog . '"');
    } else{
      $max = getmax('gi_orden', 'garantias_imagenes', 'gi_guia = "' . $guia . '" AND gi_tipo = "' . $tipog . '"');
    }
    $extension = pathinfo($archivos, PATHINFO_EXTENSION);
    $nombre = pathinfo($archivos, PATHINFO_FILENAME);
    $nuevo_nombre = $nombre . '-' . $max;
    //Si el archivos contiene algo y es diferente de vacio
    if (isset($archivos) && $archivos != "") {
      //Obtenemos algunos datos necesarios sobre el archivos
      $tipo = $_FILES['archivos']['type'][$i];
      $tamano = $_FILES['archivos']['size'][$i];
      $temp = $_FILES['archivos']['tmp_name'][$i];

      //list($archivosw, $archivosh, $tipox, $atributos) = getimagesize($_FILES['archivos']['tmp_name']); 2000000
      //Se comprueba si el archivos a cargar es correcto observando su extensión y tamaño
      //die($tipo);
      if (!($tamano < (300000000))) {
            $respuesta = 2;  //Error. La extensión o el tamaño de los pdf no es correcta. Se permiten .gif, .jpg, .png. y de 200 kb como máximo
      } else {
        //Si la archivos es correcta en tamaño y tipo
        //Se intenta subir al servidor
        $destination_path = getcwd() . DIRECTORY_SEPARATOR;
        $target_path = $destination_path . $ruta . $nuevo_nombre . '.' . $extension;
        //echo $temp." , path: ".$target_path;
        if (move_uploaded_file($temp, $target_path)) {
          //Cambiamos los permisos del archivos a 777 para poder modificarlo posteriormente
          chmod($target_path, 0777);
          //Mostramos el mensaje de que se ha subido co éxito
          //die ('<div><b>Se ha subido correctamente la imagen.</b></div>');
          //Mostramos la imagen subida
          //die ('<p><img src="img/imagens/'.$imagen.'"></p>');
          //$ruta = $rutatipo.$nuevo_nombre;

          if(isset($_POST['articulo'])){
            $sql = 'INSERT INTO guias_imagenes SET
            gi_guia = NULL,
            gi_nmb = "' . $nuevo_nombre . '",
            gi_ext = "' . $extension . '",
            gi_orden = "' . $max . '",
            gi_tipo = "' . $tipog . '", 
            gi_cccid = "'.$articulo.'"';
          } else{
            $sql = 'INSERT INTO guias_imagenes SET
            gi_guia = "' . $guia . '",
            gi_nmb = "' . $nuevo_nombre . '",
            gi_ext = "' . $extension . '",
            gi_orden = "' . $max . '",
            gi_tipo = "' . $tipog . '"';
          }
          
          setq($sql);
          /* echo $sql."<br>"; */
          /* echo $sql; */
        } else {
          //Si no se ha podido subir la imagen, mostramos un mensaje de error
          
          $respuesta = 1;  //Ocurrió algún error al subir los archivos. No pudo guardarse.
        }
      }
    }
  }
} else{
  $respuesta = 3;
}

echo $respuesta
?>