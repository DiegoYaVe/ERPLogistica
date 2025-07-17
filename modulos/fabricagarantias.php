<?php
  ini_set('display_errors', 0);
  class fabricagarantias {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelfabricagarantias(isset($obj));
    }

    function index(){
      $this->model->results($_GET['id']);
      $this->view = new viewfabricagarantias($this->model);
      $this->view->artengarantia($_GET['id']);
    }

    function insertgarantia(){
      include('remisiones.php');
      $rem = new remisiones();
      if(!isset($_GET['link'])){
        $link = 0;
      } else{
        $link = 1;
      }
      if(isset($_GET['id'])){
        $rcid = $_GET['id'];
      } else {
        $rcid = '';
      }

      $descripcion = $_POST['descripcion'];
      $cliente = $_POST['cliente'];
      $remision = '';
      if(isset($_GET['tipo'])){
        $sqlart = 'SELECT ga_articulo, ga_modelo, ga_remision FROM garantias_articulos WHERE ga_id = "'.$rcid.'"';
        $resultart = setq($sqlart);
        list($articulo, $modelo, $remision) = $resultart->fetch_array();
        $articuloname = busca($articulo, "articulos", "a_id", "a_nmb");
        $var = busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'COUNT(*)');
        if ($var > 0) {
          $articuloname .= " " . busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'av_nmb');
        }

        $folio = busca($remision, 'remisiones', 'r_id', 'r_folio');
        $linkred = "?modulo=remisiones&accion=seegarantias&id=".$remision;
      } else{
        $remision = $_POST['folio'];
        $articulo = $_POST['articulo'];
        $folio = $_POST['folio'];
        $modelo = $_POST['modelo'];
        $articuloname = $articulo . " " . $modelo;
        $linkred = "?modulo=fabricagarantias&accion=index";
      }
      if($link == 1){
        $linkred = "?modulo=fabricagarantias&accion=index";
      }
      if(isset($_POST['estatusp'])){
        $_POST['exterior'] = '';
        $_POST['interior'] = '';
        $_POST['cp'] = '';
        $_POST['estadoh'] = '';
        $_POST['estado'] = '';
        $_POST['munidesc'] = '';
        $_POST['municipio'] = '';
        $_POST['col'] = '';
        $_POST['coldesc'] = '';
        $preciodolares = $_POST['precio'];
        $tcambio = $_POST['tcambio'];
        $precio = (floatval($tcambio) * floatval($preciodolares));
      } else{
        $precio = $_POST['precio'];
        $tcambio = "1";
        $preciodolares = 0;
      }
      $importe = $precio;

      $direccion = $rem->insertdireccion();
      $referencia = $rem->model->insertgarantia($articulo, $modelo, $descripcion, $rcid, $remision, $direccion, $cliente, $precio, $tcambio, $preciodolares);
      $cxcobrar = $rem->model->creargarantiaCXC($cliente, $referencia, $importe, $folio, $articuloname);
      redirect($linkred);
    }
 
    function abonargarantia(){
      include("remisiones.php");
      $rem = new remisiones();
      $rem->abonargarantia(); 
    }

    function sendproduccion (){
      $id = $_GET['id'];      
      $link = $_GET['link'];

      $this->model->accionprod($id, "P"); //P: Pendiente de aprobación en producción
      if($link == 1){
        $linkred = "?modulo=fabricagarantias&accion=index";
      } else {
        $remision = busca($id, 'garantias_articulos', 'ga_id', 'ga_remision');
        $linkred = "?modulo=remisiones&accion=seegarantias&id=".$remision;
      }
      redirect($linkred);
    }

    function cancelarprod (){
      $id = $_GET['id'];
      $motivocancela = $_POST['motivocancela'];

      $this->model->accionprodc($id, $motivocancela, "C"); //C: Producción cancelo el ingreso a la garantía
      redirect("?modulo=fabricagarantias&accion=index");
    }

    function aprobarprod (){
      $id = $_GET['id'];

      $this->model->accionprod($id, "E"); //E: Garantía en producción
      redirect("?modulo=fabricagarantias&accion=index");
    }

    function sendtofabrica(){
      $id = $_GET['id'];

      $this->model->accionprod($id, "R"); //R: Garantía recibida de producción
      redirect("?modulo=fabricagarantias&accion=index");
    }

    function confirmarecibido(){
      $id = $_GET['id'];
      $link = $_GET['link'];
      if($link == 1){
        $linkred = "?modulo=fabricagarantias&accion=index";
      } else {
        $remision = busca($id, 'garantias_articulos', 'ga_id', 'ga_remision');
        $linkred = "?modulo=remisiones&accion=seegarantias&id=".$remision;
      }
      $this->model->accionprod2($id, "M"); //R: Garantía recibida de producción
      redirect($linkred);
    }


}

  class modelfabricagarantias {


    function select($id){
      $sql = 'SELECT * FROM garantias_articulos WHERE ga_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['ga_id'];
      $this->articulo = $row['ga_articulo'];
      $this->modelo = $row['ga_modelo'];
      $this->fini = $row['ga_fini'];
      $this->ffin = $row['ga_ffin'];
      $this->descripcion = $row['ga_descripcion'];
      $this->motivocancela = $row['ga_motivocancela'];
      $this->estatus = $row['ga_estatus'];
      $this->tipoenvio = $row['ga_tipoenvio'];
      $this->sucursal= $row['ga_sucursal'];
      $this->paqueteria= $row['ga_paqueteria'];
      $this->guia = $row['ga_guia'];
      $this->embarcamiento = $row['ga_embarcamiento'];
      $this->fenvio = $row['ga_fenvio'];
      $this->rcid = $row['ga_rcid'];
      if(empty($row['ga_rcid'])){
        $this->folio = $row['ga_remision'];
      } else{
        $this->folio = busca($row['ga_remision'], 'remisiones', 'r_id', 'r_folio');
      }
      $this->remision = $row['ga_remision'];
      $this->cliente = $row['ga_cliente'];
      $this->direccion = $row['ga_direccion'];
      $this->precio = $row['ga_precio'];
      $this->tcambio = $row['ga_tcambio'];
      $this->preciodolares = $row['ga_preciodolares'];
    }
    function results($remision){ //filtro
      $sql = 'SELECT * FROM garantias_articulos WHERE ga_estatus IN ("A","M","P","E","C","R","F") ORDER BY ga_fini DESC';
      $this->results = setq($sql);
    } 

    function insertgarantia(){
      include('remisiones.php');
      $rem = new remisiones();
      if(!isset($_GET['link'])){
        $link = 0;
      } else{
        $link = 1;
      }
      if(isset($_GET['id'])){
        $rcid = $_GET['id'];
      } else {
        $rcid = '';
      }

      $descripcion = $_POST['descripcion'];
      $cliente = $_POST['cliente'];
      $remision = '';
      if(isset($_GET['tipo'])){
        $sqlart = 'SELECT ga_articulo, ga_modelo, ga_remision FROM garantias_articulos WHERE ga_id = "'.$rcid.'"';
        $resultart = setq($sqlart);
        list($articulo, $modelo, $remision) = $resultart->fetch_array();
        $articuloname = busca($articulo, "articulos", "a_id", "a_nmb");
        $var = busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'COUNT(*)');
        if ($var > 0) {
          $articuloname .= " " . busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'av_nmb');
        }

        $folio = busca($remision, 'remisiones', 'r_id', 'r_folio');
        $linkred = "?modulo=remisiones&accion=seegarantias&id=".$remision;
      } else{
        $remision = $_POST['folio'];
        $articulo = $_POST['articulo'];
        $folio = $_POST['folio'];
        $modelo = $_POST['modelo'];
        $articuloname = $articulo . " " . $modelo;
        $linkred = "?modulo=fabricagarantias&accion=index";
      }
      if($link == 1){
        $linkred = "?modulo=fabricagarantias&accion=index";
      }
      if(isset($_POST['estatusp'])){
        $_POST['exterior'] = '';
        $_POST['interior'] = '';
        $_POST['cp'] = '';
        $_POST['estadoh'] = '';
        $_POST['estado'] = '';
        $_POST['munidesc'] = '';
        $_POST['municipio'] = '';
        $_POST['col'] = '';
        $_POST['coldesc'] = '';
        $preciodolares = $_POST['precio'];
        $tcambio = $_POST['tcambio'];
        $precio = (floatval($tcambio) * floatval($preciodolares));
      } else{
        $precio = $_POST['precio'];
        $tcambio = "1";
        $preciodolares = 0;
      }
      $importe = $precio;

      $direccion = $rem->insertdireccion();
      $referencia = $rem->model->insertgarantia($articulo, $modelo, $descripcion, $rcid, $remision, $direccion, $cliente, $precio, $tcambio, $preciodolares);
      $cxcobrar = $rem->model->creargarantiaCXC($cliente, $referencia, $importe, $folio, $articuloname);
      redirect($linkred);
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
  
      function accionprod2 ($id, $estatus){
  
        if($_POST['tipo'] == "D"){
          $paqueteria = $_POST['paqueteriaDom'];
          $sucursal = 0;
        } else if($_POST['tipo'] == "O"){
          $paqueteria = $_POST['paqueteriaDom'];
          $sucursal = $_POST['sucursalOcurre'];
        } else{
          //Recoge cliente
          $paqueteria = 0;
          $sucursal = 0;
        }
        $fenvio = $_POST['fechaEnvio'];
  
        $sql = 'UPDATE garantias_articulos SET
                ga_estatus = "'.$estatus.'", ga_tipoenvio = "'.$_POST['tipo'].'", 
                ga_sucursal = "'.$sucursal.'", ga_paqueteria = "'.$paqueteria.'", ga_fenvio = "'.$fenvio.'"
                WHERE ga_id = "'.$id.'"';
        setq($sql);
      }

  }

  class viewfabricagarantias {
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

      $nuevo = '
      <a data-fancybox="" data-type="ajax" data-src="popup/setnewgarantia.php" href="javascript:;">
        <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Crear una nueva cotización"><i class="fa fa-plus"></i> Nueva</button>
      </a>';



      $folio = busca($id, "remisiones", "r_id", "r_folio");
      toolbar("LISTADO DE GARANTÍAS".$folio, '','',$nuevo);
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
                  <th>Estatus</th>
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
                    $estatus = "PENDIENTE DE CONFIRMACIÓN DE RECIBIDO EN LA FABRICA";
                  } else if($rowga['ga_estatus'] == "F"){
                    $estatus = "FINALIZADO";
                  } else if($rowga['ga_estatus'] == "M"){
                    $estatus = "EN LA FÁBRICA";
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
                      <a data-fancybox="" data-type="ajax" data-src="popup/setaprobargarantia.php?id='.$rowga['ga_id'].'&save=1&tipo=1&origen=R&fa=1&link=1" href="javascript:;">
                        <button type="button" class="btn btn-sm btn-primary">
                          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
                        </button>
                      </a>';
                      $cxc = busca($rowga['ga_id'], 'cxcobrar', 'cx_tipo = "B" AND cx_referencia', 'cx_id');
                      if(!empty($cxc)){
                        echo '<a data-fancybox="" data-type="ajax" data-src="popup/abonargarantia.php?garantia='.$rowga['ga_id'].'&fa=1" href="javascript:;">
                        <button type="button" class="btn btn-sm btn-success">
                          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-money-bill-wave"></i>
                        </button>
                      </a>';
                      }

                      echo '</td>
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