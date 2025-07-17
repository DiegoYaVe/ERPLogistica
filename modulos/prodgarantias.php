<?php
  ini_set('display_errors', 0);
  class prodgarantias {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelprodgarantias(isset($obj));
    }

    function index(){
      $this->model->results($_GET['id']);
      $this->view = new viewprodgarantias($this->model);
      $this->view->artengarantia($_GET['id']);
    }

    function cancelarprod (){
      $id = $_GET['id'];
      $motivocancela = $_POST['motivocancela'];
      $this->model->accionprodc($id, $motivocancela, "C"); //C: Producción cancelo el ingreso a la garantía
        redirect("?modulo=prodgarantias&accion=index");
    }

    function aprobarprod (){
      $id = $_GET['id'];

      $this->model->accionprod($id, "E"); //E: Garantía en producción
      redirect("?modulo=prodgarantias&accion=index");
    }

    function sendtofabrica(){
      $id = $_GET['id'];

      $this->model->accionprod($id, "R"); //R: Garantía recibida de producción
      redirect("?modulo=prodgarantias&accion=index");
    }

    function insertgarantia(){
      if(isset($_GET['id'])){
        $rcid = $_GET['id'];
      } else {
        $rcid = '';
      }

      $link = $_GET['link'];

      $descripcion = $_POST['descripcion'];
      $remision = '';
      if(isset($_GET['tipo'])){
        $sqlart = 'SELECT rc_articulo, rc_modelo, rc_remision FROM remisionesc WHERE rc_id = "'.$rcid.'"';
        $resultart = setq($sqlart);
        list($articulo, $modelo, $remision) = $resultart->fetch_array();
        $linkred = "?modulo=remisiones&accion=seegarantias&id=".$_POST['remision'];
      } else{
        $articulo = $_POST['articulo'];
        $modelo = $_POST['modelo'];
        $linkred = "?modulo=remisiones&accion=garantia&id=".$_POST['remision'];
      }
      if($link == 1){
        $linkred = "?modulo=prodgarantias&accion=index";
      }
      $this->model->insertgarantia($articulo, $modelo, $descripcion, $rcid, $remision);
      redirect($linkred);
    }

}

  class modelprodgarantias {

    function results($remision){ //filtro
      $sql = 'SELECT * FROM garantias_articulos WHERE ga_estatus IN ("M","P","E","C","R","F") ORDER BY ga_fini DESC';
      $this->results = setq($sql);
    } 

    function accionprod ($id, $estatus){
      $sql = 'UPDATE garantias_articulos SET
              ga_estatus = "'.$estatus.'"
              WHERE ga_id = "'.$id.'"';
      setq($sql);
    }

    function accionprodc ($id, $motivocancela, $estatus){
      $sql = 'UPDATE garantias_articulos SET
              ga_estatus = "'.$estatus.'", ga_motivocancela = "'.$motivocancela.'"
              WHERE ga_id = "'.$id.'"';
      setq($sql);
    }

    function insertgarantia($articulo, $modelo, $descripcion, $rcid, $remision){
      $respuesta = 0;
      $ruta = 'img/garantias/';
  
      if(!isset($_GET['save'])){
        if(!empty($rcid)){
          $query = 'INSERT INTO garantias_articulos SET
                  ga_articulo = "'.$articulo.'",
                  ga_modelo = "' . $modelo . '",
                  ga_fini = "' . date("Y-m-d H:i:s") . '",
                  ga_descripcion = "'.$descripcion.'",
                  ga_estatus = "A",
                  ga_rcid = "'.$rcid.'",
                  ga_remision = "'.$remision.'"
                  ';
        } else{
          $query = 'INSERT INTO garantias_articulos SET
                  ga_articulo = "'.$articulo.'",
                  ga_modelo = "' . $modelo . '",
                  ga_fini = "' . date("Y-m-d H:i:s") . '",
                  ga_descripcion = "'.$descripcion.'",
                  ga_estatus = "A",
                  ga_rcid = NULL,
                  ga_remision = NULL';
        }
        setq($query);
        $garantia = getmax('ga_id', 'garantias_articulos', false, false);   
      } else{
        $garantia = $rcid;
      }
  
  
      if ($_FILES['archivos']['name']) {  
        /* die("Entra 1 G:".$garantia." y ".$descripcion); */
        for ($i = 0; $i <= count($_FILES['archivos']['name']); $i++) {
          //Recogemos el archivos enviado por el formulario
          $archivos = $_FILES['archivos']['name'][$i];
          //echo $archivos."\n";
          /* $max = getmax('gi_orden', 'garantias_imagenes', 'gi_garantia = "' . $garantia . '"'); */
          $extension = pathinfo($archivos, PATHINFO_EXTENSION);
          $nombre = pathinfo($archivos, PATHINFO_FILENAME);
          $nuevo_nombre = $nombre;
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
              $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato predeterminado
              $fechaHoraSinGuionesNiPuntos = str_replace(array("-", ":", " "), "", $fechaHora);
  
              //Si la archivos es correcta en tamaño y tipo
              //Se intenta subir al servidor
              $destination_path = getcwd() . DIRECTORY_SEPARATOR;
              $target_path = $destination_path . $ruta . $nuevo_nombre . $fechaHoraSinGuionesNiPuntos . '.' . $extension;
              //echo $temp." , path: ".$target_path;
              if (move_uploaded_file($temp, $target_path)) {
                //Cambiamos los permisos del archivos a 777 para poder modificarlo posteriormente
                chmod($target_path, 0777);
                //Mostramos el mensaje de que se ha subido co éxito
                //die ('<div><b>Se ha subido correctamente la imagen.</b></div>');
                //Mostramos la imagen subida
                //die ('<p><img src="img/imagens/'.$imagen.'"></p>');
                //$ruta = $rutatipo.$nuevo_nombre;
  
                
                  $sql = 'INSERT INTO garantias_imagenes SET
                          gi_garantia = "'.$garantia.'",
                          gi_nmb = "' . $nuevo_nombre  . $fechaHoraSinGuionesNiPuntos . '",
                          gi_ext = "' . $extension . '",
                          gi_tipo = "G"';
                  setq($sql);
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
      }

  }

  class viewprodgarantias {
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->tipodoc = array("F"=>"Factura","R"=>"Remisión");
    }    

    function artengarantia($id) {
      ?>
      <script language="JavaScript">
        function checkSubmitmovimiento() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
        $("body").on("keydown", function(e) { 
          if (e.altKey && e.which === 78) {
            var btn = document.getElementById("nuevo");
            btn.click();
            e.preventDefault();
          }
        });
    
        $("body").on("keydown", function(e) { 
          if (e.altKey && e.which === 76) {
            var btn = document.getElementById("filtrar");
            btn.click();
            $("#cliente").focus();
            e.preventDefault();
          }
        });
    
        $("body").on("keydown", function(e) { 
          if (e.altKey && e.which === 82) {
            window.location.reload();
          }
        });
      </script>
      <?php

      /* $atras = '
      <a href="?modulo=remisiones&accion=garantia&id='.$id.'">
        <button type="button" class="btn btn-sm btn-warning " data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
      </a>'; */



      $folio = busca($id, "remisiones", "r_id", "r_folio");
      toolbar("GARANTÍAS PRODUCCIÓN".$folio);
      //Inicio de la condición de la fecha de garantía
      ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '<div class="table table-responsive text-medium">
          <center>
            <table class="table" id="myTable">
              <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
                <tr>
                  <th>Nombre artículo</th>
                  <th>Fecha entrada</th>
                  <th>Fecha salida</th>
                  <th>Descripción</th>
                  <th>estatus</th>
                  <th>Remisión</th>
                  <th></th>
                    ';
                echo '</tr>
              </thead>';
              while($rowga = $this->model->results->fetch_array()){
                echo '
                <tr>';
                if(empty($rowga['ga_rcid'])){
                  $remision = $rowga['ga_remision'];
                  $nmba = $rowga['ga_articulo']." ".$rowga['ga_modelo'];
                } else{
                  $nmba = busca($rowga['ga_articulo'], "articulos", "a_id", "a_nmb");
                  $var = busca($rowga['ga_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowga['ga_modelo'] . '" AND av_articulo', 'COUNT(*)');
                  if ($var > 0) {
                    $nmba .= " " . busca($rowga['ga_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowga['ga_modelo'] . '" AND av_articulo', 'av_nmb');
                  }
                  $remision = busca($rowga['ga_remision'], 'remisiones', 'r_id', 'r_folio');
                }
    
                  $fentrada = fecha_formato($rowga['ga_fini'], true, false);
                  if(empty($rowga['ga_ffin'])){
                    $ffin = "No disponible";
                  } else{
                    $ffin = fecha_formato($rowga['ga_ffin'], true, false);
                  }
                  $descripcion = $rowga['ga_descripcion'];
                  if($rowga['ga_estatus'] == "A"){
                    $estatus = "PENDIENTE DE ENVÍO A PRODUCCIÓN";
                  } else if($rowga['ga_estatus'] == "E"){
                    $estatus = "REPARACIÓN EN PRODUCCIÓN";
                  } else if($rowga['ga_estatus'] == "P"){
                    $estatus = "PENDIENTE DE APROBACIÓN EN PRODUCCIÓN";
                  } else if($rowga['ga_estatus'] == "R"){
                    $estatus = "ENVÍADO A LA FABRICA";
                  } else if($rowga['ga_estatus'] == "F"){
                    $estatus = "FINALIZADO";
                  } else if($rowga['ga_estatus'] == "M"){
                    $estatus = "ENTREGADO A LA FÁBRICA";
                  } else {
                    $estatus = "NO APROBADA";
                  }

                    echo '<td>'.$nmba.'</td>
                    <td>'.$fentrada.'</td>
                    <td>'.$ffin.'</td>
                    <td>'.$descripcion.'</td>
                    <td>'.$estatus.'</td>
                    <td>'.$remision.'</td>';
                      echo '
                      <td>
                      <a data-fancybox="" data-type="ajax" data-src="popup/setaprobargarantia.php?id='.$rowga['ga_id'].'&tipo=1&origen=P&link=1" href="javascript:;">
                        <button type="button" class="btn btn-sm btn-primary">
                          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
                        </button>
                      </a>
                      </td>
                      ';
                echo '</tr>';
              }
            echo '</table>
            <center>
            </div>
          </div>';
        echo'</div>
      </div>';
      //Fin de la condición de la fecha de garantía

      echo '
          <script> 
          $(document).ready(function () {
            var windowHeight = $(window).height();
            $("#myTable").DataTable( {
                paging: true,
                ordering: false,
                scrollY: windowHeight * 0.5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                },
                responsivePriority: 1,
                pageLength:50,
            });
          });
          </script>';
    }
  }
?>