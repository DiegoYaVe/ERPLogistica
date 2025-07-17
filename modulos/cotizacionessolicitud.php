<?php
/* ini_set('display_errors',1); */

class cotizacionessolicitud{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelcotizacionessolicitud(isset($obj));
  }
  function index(){
    $this->view = new viewcotizacionessolicitud($this->model);
    $this->view->browse();
  }
  function setcotiza(){
    $cotizacion = $_POST['id'];
    $query = 'SELECT ccc_id, ccc_articulo FROM crm_cotizacionesc WHERE ccc_cotizacion = "'.$cotizacion.'"';
    $result = setq($query);
    while($rowupd = $result->fetch_array()){
      $fenvio = $_POST['fechaEnvio'.$rowupd['ccc_id']];
      $estatus = '';
      if($_POST['tipo'.$rowupd['ccc_id']] == "D"){
        $pqt = $_POST['paqueteriaDom'.$rowupd['ccc_id']];   
        $sucursal = 0; 
        $estatus = 'N';
      } else if($_POST['tipo'.$rowupd['ccc_id']] == "O"){
        $pqt = $_POST['paqueteriaOcurre'.$rowupd['ccc_id']];
        $sucursal = $_POST['sucursalOcurre'.$rowupd['ccc_id']];
        $estatus = 'N';
      } else if($_POST['tipo'.$rowupd['ccc_id']] == "C"){
        $pqt = 0;
        $sucursal = 0;
        $estatus = 'P';
      } else{
        $pqt = 0;
        $sucursal = 0;
        $estatus = 'N';
      }

      $sql = 'UPDATE crm_cotizacionesc SET ccc_tipoenvio = "'.$_POST['tipo'.$rowupd['ccc_id']].'", ccc_paqueteria = "'.$pqt.'", ccc_sucursal = "'.$sucursal.'", ccc_fenvio = "'.$fenvio.'", ccc_estatus = "'.$estatus.'" WHERE ccc_id = "'.$rowupd['ccc_id'].'"';
      $resultupd = setq($sql);
      if(!$resultupd){
        die("Error 708");
      }
      $articulosLigados = $this->model->articulosLigados("tipo".$rowupd['ccc_id']."a");

      if(count($articulosLigados) > 0){
        for($j = 0; $j < count($articulosLigados); $j++){
          $artpadre = $rowupd['ccc_id'];
          $estructura = $artpadre."a".$articulosLigados[$j][0]."n".$articulosLigados[$j][1];
          $tipoenvio = $_POST['tipo'.$estructura];
          $estatus = '';
          $fenvio = $_POST['fechaEnvio'.$estructura];
          if($tipoenvio == "D"){
            $pqt = $_POST['paqueteriaDom'.$estructura];   
            $sucursal = 0;    
            $estatus = "N";
          } else if($tipoenvio == "O"){
            $pqt = $_POST['paqueteriaOcurre'.$estructura];
            $sucursal = $_POST['sucursalOcurre'.$estructura];
            $estatus = "N";
          } else if($tipoenvio == "C"){
            $pqt = 0;
            $sucursal = 0;
            $estatus = "P";
          }else{
            //Tipo envio "P"
            $pqt = 0;
            $sucursal = 0;
            $estatus = "N";
          }

          $combo = busca($artpadre, 'crm_cotizacionesc', 'ccc_id', 'ccc_combo');
          $cotizaciond = busca($artpadre, 'crm_cotizacionesc', 'ccc_id', 'ccc_cotizaciond');
          $modelo = $_POST['modelo'.$estructura];
          //$this->model->insertLigados($cotizacion, $articulo, $modelo, $numero, $tipoenvio, $paqueteria, $sucursal, $combo, $estatus, $fenvio, $ligado);
          $this->model->insertLigados($cotizacion,$cotizaciond, $articulosLigados[$j][0], $modelo, $articulosLigados[$j][1], $tipoenvio, $pqt, $sucursal, $combo, $estatus, $fenvio, $artpadre);
        }
      }
      

    }

    $this->model->setcotiza($_POST['id'], $_POST['precio'], $_POST['comentario'], $_POST['tcambio']);


    echo '
    <script>
   
    $.ajax({
      url: "query/correocotizaciones.php",
      method: "POST",
      data: { "correoop": "2",
              "cotizacion": "'.$_POST['id'].'"},
    }).done(function(data){
    });  

    $.ajax({
      url: "task/notificacionpush.php",
      method: "POST",
      data: {"grupo": "VENTAS",
              "estatus": "P",
              "cotizacion": "'.$_POST['id'].'"},
    }).done(function(data){
      window.location.href = "?modulo=cotizacionessolicitud&accion=index";
    });  
    </script>';
    /* redirect('?modulo=cotizacionessolicitud&accion=index'); */
  }

  function regresoaventas(){
    $cotizacion = $_POST['idcotiza'];
    $motivo = $_POST['ccmotivo'];
    $this->model->regresoaventas($cotizacion, $motivo);
    $grupos = '"VENTAS","GERENCIA","ADMIN"';
    echo "
    <script>
    $.ajax({
      url: 'task/notificacionpushgrupos.php',
      method: 'POST',
      data: {
              'grupos': '".$grupos."',
              'tipo': 'REGRESOVENTAS',
              'cuerpo': 'Cotización de vuelta de logística',
              'cotizacion': '".$cotizacion."'
            },
    }).done(function(data){
      window.location.href = '?modulo=cotizacionessolicitud&accion=index';
    });  
    </script>";
    /* redirect('?modulo=cotizacionessolicitud&accion=index'); */
  }
 
}

class modelcotizacionessolicitud{
  function setcotiza($id, $precio, $comentario, $tcambio){
  
    if ($_FILES['adjunto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $extensionArchivo = pathinfo($_FILES['adjunto']['name'], PATHINFO_EXTENSION);
    $longitud = 9;
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $cadenaAleatoria = '';

    for ($i = 0; $i < $longitud; $i++) {
      $posicion = mt_rand(0, strlen($caracteres) - 1);
      $cadenaAleatoria .= $caracteres[$posicion];
    }

    $targetDir = "adjuntos/solcotizaciones/";  // Carpeta donde se guardarán los archivos subidos
    $archivoNombre = $id.$cadenaAleatoria.".".$extensionArchivo;
    $archivoRuta = $targetDir . $archivoNombre;
    
    // Subir el archivo
    /* if (!move_uploaded_file($_FILES['adjunto']["tmp_name"], $archivoRuta)) {
      echo '
      <script>
        alert("Hubo un error al subir el archivo.");
      </script>';
    } */
  } else{
    $archivoRuta = '';
  }

    /* $sqlcc = 'SELECT cc_subtotal, cc_iva, cc_descuento FROM crm_cotizaciones WHERE cc_id = "'.$id.'"';
    $result = setq($sqlcc);
    list($subtotal, $iva, $descuento) = $result->fetch_array(); */

    /* $nuevomonto = floatval($subtotal)+floatval($iva)-floatval($descuento)+floatval($precio);     */
    if($tcambio > 0) $precio = $precio * $tcambio;
    $sql = 'UPDATE crm_cotizaciones SET 
            cc_observaenvio = "'.$comentario.'", 
            cc_adjunto = "'.$archivoRuta.'", 
            cc_estatus = "P", 
            cc_precioenvio = "'.$precio.'" ,
            cc_preciodolares = "'.$tcambio.'"
            WHERE cc_id = "'.$id.'"';
    setq($sql);

    $sql2 = 'INSERT INTO crm_cotizaciones_historial SET
            cch_cotizacion = "'.$id.'",
            cch_fecha = "'.date("Y-m-d H:i:s").'",
            cch_user = "'.$_SESSION['uid'].'",
            cch_descripcion = "VALORACIÓN DEL PRECIO DE ENVÍO EN LOGÍSTICA"';
    setq($sql2);
    $nguias = intval($_POST['nguias']);

    if($nguias > 0){
      for($i = 0; $i < $nguias; $i++){
        $sqlguia = 'INSERT INTO guias_articulos SET
                  ga_ugenera = "' . $_SESSION['uid'] . '",
                  ga_estatus = "A",
                  ga_cotizacion = "' . $id . '",
                  ga_fini = "' . date("Y-m-d H:i:s") . '"
                  ';
      setq($sqlguia);
      }
    }
  }

  function regresoaventas($cotizacion, $motivo){
    $sql = 'UPDATE crm_cotizaciones SET 
            cc_motivo = "'.$motivo.'", 
            cc_estatus = "D" 
            WHERE cc_id = "'.$cotizacion.'"';
    setq($sql);
  }

  function articulosLigados($indicio){
  $i = 0;
  $variablesConIndicio = array();
  $articulos = array();

  foreach ($_POST as $key => $value) {
      if (strpos($key, $indicio) !== false) {
          $matches = array();
          // Usamos una expresión regular para buscar números después de "a" y "n"
          if (preg_match('/' . $indicio . '(\d+)n(\d+)/', $key, $matches)) {
              $numeroDespuesDeA = $matches[1];
              $numeroDespuesDeN = $matches[2];
              $variablesConIndicio[$key] = array(
                  'NumeroDespuesDeA' => $numeroDespuesDeA,
                  'NumeroDespuesDeN' => $numeroDespuesDeN
              );
          }
      }
  }

  if (!empty($variablesConIndicio)) {
      foreach ($variablesConIndicio as $variable => $numeros) {
        $articulos[$i][0] = $numeros['NumeroDespuesDeA'];
        $articulos[$i][1] = $numeros['NumeroDespuesDeN'];
        $i++;
      }
  }  
  return $articulos;
}

function insertLigados($cotizacion, $cotizaciond, $articulo, $modelo, $numero, $tipoenvio, $paqueteria, $sucursal, $combo, $estatus, $fenvio, $ligado){
  $sql = 'INSERT INTO crm_cotizacionesc SET
        ccc_cotizacion = "'.$cotizacion.'",
        ccc_cotizaciond = "'.$cotizaciond.'",
        ccc_articulo = "'.$articulo.'",
        ccc_modelo = "'.$modelo.'",
        ccc_numero = "'.$numero.'",
        ccc_tipoenvio = "'.$tipoenvio.'",
        ccc_paqueteria = "'.$paqueteria.'",
        ccc_sucursal = "'.$sucursal.'",
        ccc_combo = "'.$combo.'",
        ccc_estatus = "'.$estatus.'",
        ccc_fenvio = "'.$fenvio.'",
        ccc_ligado = "'.$ligado.'"';
    setq($sql);
}

}

class viewcotizacionessolicitud{
  function __construct($model){
    ?>
    <script>
    function checkguardar(){
      document.getElementById("sendform").innerHTML = "Guardando";
      document.getElementById("sendform").disabled = true;
      return true;
    }
    </script>
    <?php
  }

  function browse(){

  $filtro = '
  <form class="form-inline" role="form" method="post" action="?modulo=articulos&accion=index" id="filtro">
  <div class="mb-5">
  <label class="mr-sm-2">Registra:</label>
  <select name="nmb" id="nmb" class="form-control">
    <option value="">TODOS</option>';
    $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $filtro.= '<option value="'.$row['u_id'].'">'.$row['u_id'].'</option>';
    }
    $filtro.= '
  </select>
  </div>
  <div class="mb-5" hidden>
    <label class="mr-sm-2">Page:</label>
    <div class="position-relative has-icon-left">
      <input type="text" id="page" name="page" class="form-control" value="" required>
    </div>
  </div>
  <div class="mb-5">
    <label class="mr-sm-2">Fecha desde:</label>
    <div class="position-relative has-icon-left">
      <input type="date" id="fini" name="fini" class="form-control" value="" required>
      <input hidden type="date" id="fini2" class="form-control" value="'.date('Y-m-d',strtotime('-20 days')).'">
      <div class="form-control-position">
        <i class="icon-calendar5"></i>
      </div>
    </div>
  </div>
  <div class="mb-5">
    <label class="mr-sm-2">Fecha hasta:</label>
    <div class="position-relative has-icon-left">
      <input type="date" id="ffin" name="ffin" class="form-control" value="" required>
      <input hidden type="date" id="ffin2" class="form-control" value="'.date('Y-m-d',strtotime('+10 days')).'">
      <div class="form-control-position">
        <i class="icon-calendar5"></i>
      </div>
    </div>
  </div>
    <div class="mb-5">
      <label for="">Acciones:</label> <br>
    <button type="button" onclick="mandar(0)" class="btn btn-info">
      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-redo"></i> Actualizar
    </button>
    <button type="button" onclick="mandar(1)" class="btn btn-warning">
      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
    </button>
    </div>
  </form>';

  toolbar($_GET['modulo'],"",$filtro,"");

    ?>
    <div class="card mt-3">
    <div class="card-body">
    <?php
    echo '
    <div class=" col-12 table-responsive">
      <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
      <thead class="bg-light-blue bg-darken-2 ">
        <tr>
          <th><b>Folio</b></th>
          <th><b>Nombre</b></th>
          <th><b>Destino</b></th>
          <th><b>Agente</b></th>
          <th><b>Inicio</b></th>
          <th><b>Final</b></th>
          <th><b>Importe</b></th>
          <th><b></b></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      </table>
      </div>
    </div>';

    echo '
    <script>
    function mandar(id) {
      var fini = document.getElementById("fini");
      var ffin = document.getElementById("ffin");
      var hini = document.getElementById("hini");
      var ugen = document.getElementById("nmb");
    
      if (id == 1) {
        fini.value = document.getElementById("fini2").value;
        ffin.value = document.getElementById("ffin2").value;
        ugen.value = "";
      }
    
      $("#myTable").DataTable().clear().draw();
      $("#myTable").DataTable().destroy();
    
      var windowHeight = $(window).height();
      
      $("#myTable").DataTable({
        paging: true,
        scrollY: windowHeight * 0.5,
        processing: true,
        serverside: true,
        ordering: false,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatablecotizacionessol.php",
          type: "POST",
          datatype: "json",
          data: {
            "fini": fini.value,
            "ffin": ffin.value,
            "ugen": ugen.value,
          },
        },
        pageLength: "50",
        responsivePriority: 1,
        order: [[4, "desc"]], // Ordena por la columna de fechas con hora de más reciente a más antigua
      });
    }
    
    
    mandar(1);
  </script>
  ';
  }
}
?>