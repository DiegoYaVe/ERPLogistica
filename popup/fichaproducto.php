<?php
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
  include_once('../funciones.php');
  ini_set('display_errors', 1);

  //foreachdie();
  $art = $_REQUEST['articulo'];
  $modelo = $_REQUEST['modelo'];
  $nmb = busca($art, 'articulos', 'a_id', 'a_nmb');
  $tipoprod= busca($art, 'articulos', 'a_id', 'a_tipoprod');
  if($tipoprod != "M"){
    if($modelo != ""){
      $nmb .= ' '.busca($art, 'articulos_variantes', 'av_modelo = "'.$modelo.'" AND av_articulo', 'av_nmb');
      $imagensql = busca($art, 'articulos_descargas', 'ad_modelo = "'.$modelo.'" ANd ad_articulo', 'ad_ruta');
      if(!$imagensql) $imagen = "img/noimage.png";
      else $imagen = "img/".$imagensql;
    } else {
      $imagensql = busca(busca($art, 'articulos', 'a_id', 'a_cb'), 'imagenes', 'i_idproducto', 'CONCAT(i_nmb,".",i_ext)'); 
      if(!$imagensql) $imagen = "img/noimage.png";
      else $imagen = "img/productos/".$imagensql;
    }
    $sql = 'SELECT a_peso, a_alto, a_ancho, a_largo FROM articulos WHERE a_id = "'.$art.'"';
    $result = setq($sql);
    list($peso, $alto, $ancho, $largo) = $result -> fetch_array();
    echo '<div class="col-3 col-xs-4 col-sm-4">
    <center>
      <div class="card" style=" border: 2px solid #000;">
        <img class="card-img-top" src="'.$imagen.'" alt="Imagen de referencia">
        <div class="card-body">
          <h2 class="card-title">'.$nmb.'</h2>
          <p class="card-text">
          <b>Ancho: </b>'.$ancho.' metros<br>
          <b>Largo: </b> '.$largo.' metros<br>
          <b>Alto: </b> '.$alto.' metros<br>
          <b>Peso: </b> '.$peso.' kilos<br>
          </p>
        </div>
      </div>
    <center>
    </div>
    ';
  } else {
    echo '<div class="col-11">
    <div class="row">
    <center><h1>Artículos del paquete '.$nmb.'</h1></center>';
    $sql = 'SELECT * FROM articulos_combos WHERE ac_articulo = "'.$art.'"';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $nmb = busca($row['ac_ahijo'], 'articulos', 'a_id', 'a_nmb');
      $imagensql = busca(busca($row['ac_ahijo'], 'articulos', 'a_id', 'a_cb'), 'imagenes', 'i_idproducto', 'CONCAT(i_nmb,".",i_ext)'); 
      if(!$imagensql) $imagen = "img/noimage.png";
      else $imagen = "img/productos/".$imagensql;
      $sql2 = 'SELECT a_peso, a_alto, a_ancho, a_largo FROM articulos WHERE a_id = "'.$row['ac_ahijo'].'"';
      $result2 = setq($sql2);
      list($peso, $alto, $ancho, $largo) = $result2 -> fetch_array();
      echo '<div class="col-lg-3 col-md-4  col-sm-4 col-xs-4 mt-1">
      <center>
        <div class="card" style="height:70vh; border: 2px solid #000;">
          <img class="card-img-top" src="'.$imagen.'" alt="Imagen de referencia">
          <div class="card-body">
            <h2 class="card-title">'.$nmb.'</h2>
            <p class="card-text">
            <b>Ancho: </b>'.$ancho.' metros<br>
            <b>Largo: </b> '.$largo.' metros<br>
            <b>Alto: </b> '.$alto.' metros<br>
            <b>Peso: </b> '.$peso.' kilos<br>
            </p>
          </div>
        </div>
      <center>
      </div>
      ';
    }
    echo '</div>
    </div>';
  }

?>