<?php
ini_set('display_errors',0);
include_once('../funciones.php');
$respuesta = 0;
$idguia = $_POST['idguia'];


$remision = busca($idguia, 'guias_articulos', 'ga_id', 'ga_cotizacion');

$nimagenes = intval(busca($idguia, 'guias_imagenes', 'gi_tipo = "P" AND gi_guia', 'COUNT(*)'));

if ($nimagenes > 0) {
  $sql = 'UPDATE guias_articulos SET ga_estatus = "P", ga_ffin = "' . date("Y-m-d H:i:s") . '" WHERE ga_id = "' . $idguia . '"';
  $result = setq($sql);

  if ($result === false) {
    // La consulta no se ejecutó correctamente
    $respuesta = 1;
  } else {
    $sqlguia = 'UPDATE remisionesc SET rc_estatus = "P" WHERE rc_guia = "' . $idguia . '"';
    $resultguia = setq($sqlguia);
    if ($resultguia === false) {
      $respuesta = 1;
    } else {
      //Consultamos si todos los articulos y todas las guias ya se encuentran asignadas
      //Todos los articulos de la remision ya tienen una guía asignada
      $totalarticulos = intval(busca($remision, 'remisionesc', 'rc_guia IS NULL AND rc_tipoenvio != "C" AND rc_remision', 'COUNT(*)'));


      if($totalarticulos == 0){
        //Todas las guías han pasado a un estatus de preparación y todas ya contienen artículos
        $sqlg = 'SELECT ga_id FROM guias_articulos WHERE ga_estatus = "A" AND ga_cotizacion = "'.$remision.'"';
        $resultg = setq($sqlg);
        while($row = $resultg->fetch_array()){
          $articulosc = intval(busca($row['gaid'], 'remisionesc', 'rc_guia', 'COUNT(*)'));
          if($articulosc == 0){
            //Eliminamos todas las guías de la cotización que esten listas para asignar y vacías
            $sqldel = 'DELETE FROM guias_articulos WHERE ga_id = "'.$row['ga_id'].'"';
            setq($sqldel);
          }
        }
      }
      $respuesta = 0;

      $remisionguias = intval(busca($remision, 'guias_articulos', 'ga_nmb IS NOT NULL AND ga_cotizacion', 'COUNT(*)'));
      $remisionguiasf = intval(busca($remision, 'guias_articulos', 'ga_estatus IN ("F", "P") AND ga_cotizacion', 'COUNT(*)'));
      /* $respuesta = $remisionguias.' == '.$remisionguiasf; */
      if($remisionguias == $remisionguiasf){
        $respuesta = 3;
      }
    }
  }
} else {
  $respuesta = 2;
}


echo $respuesta;
?>