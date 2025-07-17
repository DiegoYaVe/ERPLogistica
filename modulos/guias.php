<?php
/* ini_set('display_errors',1); */

class guias
{
  var $model;
  var $view;
  function __construct()
  {
    $this->model = new modelguias(isset($obj));
  }
  function index()
  {
    $this->view = new viewguias($this->model);
    $this->view->browse();
  }

  function setenvios()
  {
    /* foreachdie(); */
    $idcotiza = $_POST['idcotiza'];
    $this->model->setenvios($idcotiza);
    redirect('?modulo=guias&accion=index');
  }

  function setenviosgeneral()
  {
    $idcotiza = $_POST['idcotiza'];
    $this->model->setenviosgeneral($idcotiza);
    redirect('?modulo=guias&accion=index');
  }

  function prodpordefinir()
  {
    $this->view = new viewguias($this->model);
    $this->view->prodpordefinir();
  }

  function setprodpordefinir()
  {    
    $idcotiza = $_POST['idcotiza'];
    $resultado = $this->model->setprodpordefinir($idcotiza);
    $narticulosdo = intval($resultado[0]);
    $narticulosc = intval($resultado[1]);



    if($narticulosdo > 0){
      if($narticulosdo == 1){
        $cuerpo = $narticulosdo.' nuevo producto listo para asignarle guía';
      } else{
        $cuerpo = $narticulosdo.' nuevos productos listos para asignarles guía';
      }
      $grupos = '"LOGISTIC"';
          echo "
          <script>
            $.ajax({
              url: 'task/notificacionpushgrupos.php',
              method: 'POST',
              data: {
                      'tipo': 'PORDEFINIR',
                      'grupos': '".$grupos."',
                      'cotizacion': '".$idcotiza."',
                      'cuerpo': '".$cuerpo."'
                    },
            }).done(function(data){
            });          
            </script>
          ";
    } 

    if($narticulosc > 0){
      if($narticulosc == 1){
        $cuerpo = $narticulosc.' nuevo producto listo para ser embarcado';
      } else{
        $cuerpo = $narticulosc.' nuevos productos listos para ser embarcados';
      }
      $grupos = '"LOGISTIC"';
          echo "
          <script>
            $.ajax({
              url: 'task/notificacionpushgrupos.php',
              method: 'POST',
              data: {
                      'tipo': 'PORDEFINIR',
                      'grupos': '".$grupos."',
                      'cotizacion': '".$idcotiza."',
                      'cuerpo': '".$cuerpo."'
                    },
            }).done(function(data){
            });          
            </script>
          ";
    }
    
      echo '
      <script>
      window.location.href = "?modulo=guias&accion=prodpordefinir&tipo=1";
      </script>
      ';
      /* redirect('?modulo=guias&accion=prodpordefinir'); */
  }

  function insertguiaga()
  {
    $id = $_POST['id'];
    $guia = $_POST['guia'];

    $this->model->insertguiaga($id, $guia);

    redirect("?modulo=guias&accion=index");
  }

  function insertdireccion(){
    if($_POST['direcciones'] == "XXX"){
      $cl = $_POST['cliente'];
      $sql = 'INSERT INTO crm_direcciones SET
          cd_nmbdir = "Cotizacion '.$_POST['nmbac'].'",
          cd_cliente = "'.$cl.'",
          cd_tipodir = "2",
          cd_calle = "'.$_POST['calle'].'",
          cd_nume = "'.$_POST['exterior'].'",
          cd_numi = "'.$_POST['interior'].'",
          cd_colonia = "'.$_POST['col'].'",
          cd_municipio = "'.$_POST['municipio'].'",
          cd_cp = "'.$_POST['cp'].'",
          cd_estado = "'.$_POST['estadoh'].'",
          cd_predeterminada = "0",
          cd_pais = "MEXICO"';
      setq($sql);
      $direccion = getmax('cd_id', 'crm_direcciones', false, false);
    } else {
      $direccion = $_POST['direcciones'];
      $sql = 'UPDATE crm_direcciones SET cd_calle = "'.$_POST['calle'].'",
                                        cd_nume = "'.$_POST['exterior'].'",
                                        cd_numi = "'.$_POST['interior'].'",
                                        cd_colonia = "'.$_POST['col'].'",
                                        cd_municipio = "'.$_POST['municipio'].'",
                                        cd_cp = "'.$_POST['cp'].'",
                                        cd_estado = "'.$_POST['estadoh'].'"
              WHERE cd_id = "'.$direccion.'"';
      setq($sql);        
    }

    $sqlc = 'UPDATE crm_cotizaciones SET cc_direnvio = "'.$direccion.'"
              WHERE cc_remision = "'.$_POST['remision'].'"';
    setq($sqlc);


    redirect("?modulo=guias&accion=prodpordefinir&tipo=1");
     /* else {
      $direccion = "0";
    } */
  }
}

class modelguias
{

  function sonTodosIgualesA($arreglo, $valor) {
    foreach ($arreglo as $elemento) {
        if ($elemento !== $valor) {
            return false; // Si un elemento es diferente, devuelve false
        }
    }

    return true; // Si todos los elementos son iguales al valor, devuelve true
  }
  function setenvios($idcotiza)
  {
    $query = 'SELECT ccc_id FROM crm_cotizacionesc WHERE ccc_cotizacion = "' . $idcotiza . '" AND ccc_estatus = "N"';
    $result = setq($query);
    while ($row = $result->fetch_array()) {
      if (isset($_POST['select' . $row['ccc_id']])) {
        // Estatus B = Articulo de remisión pendiente de asignación de guia (Borrador de salidas)
        $sql = 'UPDATE crm_cotizacionesc SET ccc_estatus = "P", ccc_observacion = "'.$_POST['obs' . $row['ccc_id']].'" WHERE ccc_id = "' . $row['ccc_id'] . '"';
        setq($sql);

          $sqlguia = 'INSERT INTO guias_articulos SET
                      ga_cccid = "' . $row['ccc_id'] . '",
                      ga_guia = "' . $_POST['in' . $row['ccc_id']] . '"';
          setq($sqlguia);
          if(isset($_POST['in' . $row['ccc_id'].'2'])){
            $sqlguia2 = 'INSERT INTO guias_articulos SET
                        ga_cccid = "' . $row['ccc_id'] . '",
                        ga_guia = "' . $_POST['in' . $row['ccc_id'].'2'] . '"';
            setq($sqlguia2);
          }
      }
    }

    /* $totalreg = busca($idcotiza, 'crm_cotizacionesc', 'ccc_tipoenvio != "C" AND ccc_cotizacion', 'COUNT(*)');
    $estatusB = busca($idcotiza, 'crm_cotizacionesc', 'ccc_estatus = "B" AND ccc_cotizacion', 'COUNT(*)');
    if ($totalreg == $estatusB) {
      // Estatus G = Todos los productos están listos para enviarse y cuando se embarquen se finalizará la remisión
      $sql = 'UPDATE remisiones SET r_estatus = "G" WHERE r_id = "' . $_POST['idremision'] . '"';
      setq($sql);
    } */

  }

  function insertguiaga($garantia, $guia){
    $sql = 'UPDATE garantias_articulos SET ga_guia = "'.$guia.'" WHERE ga_id = "'.$garantia.'"';
    setq($sql);
    $ruta = 'img/garantias/preparacion/';

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
                        gi_tipo = "P"';
                setq($sql);
            } else {
              //Si no se ha podido subir la imagen, mostramos un mensaje de error
              
              $respuesta = 1;  //Ocurrió algún error al subir los archivos. No pudo guardarse.
            }
          }
        }
      }
    }
  }
  function setprodpordefinir($idcotiza)
  {
    $contador = 0;
    $contador2 = 0;
    $contador3 = 0;
    $estatusG = array();
    $articulosmodelos = array();
    
    $query = 'SELECT ccc_id, ccc_modelo, ccc_articulo FROM crm_cotizacionesc WHERE ccc_cotizacion = "' . $idcotiza . '"';
    $result = setq($query);
    while ($row = $result->fetch_array()) {
      if (isset($_POST['select' . $row['ccc_id']])) {
        if($_POST['tipoenvio' . $row['ccc_id']] == "C"){
          $paqueteria = 0;
          $sucursal = 0;
          $estatus = "P";
          $tenvio = "C";  
          $contador3++;        
          array_push($estatusG, $tenvio);
        } else if($_POST['tipoenvio' . $row['ccc_id']] == "O"){
          $paqueteria = $_POST['paqueteria' . $row['ccc_id']];
          $sucursal = $_POST['sucursal' . $row['ccc_id']];
          $estatus = "N";
          $tenvio = "O";
          $contador2++;
          array_push($estatusG, $tenvio);
        } else{
          $paqueteria = $_POST['tipoenvio' . $row['ccc_id']];
          $sucursal = 0;
          $estatus = "N";
          $tenvio = "D";
          array_push($estatusG, $tenvio);
          $contador2++;
        }

        /* die("Paqueteria: ".$paqueteria.", sucursal: ".$sucursal." y estatus: ".$tenvio); */


        $sql = 'UPDATE crm_cotizacionesc SET 
                ccc_estatus = "'.$estatus.'", 
                ccc_tipoenvio = "'.$tenvio.'", 
                ccc_paqueteria = "'.$paqueteria.'", 
                ccc_sucursal = "'.$sucursal.'",
                ccc_observacion = "'.$_POST['obs' . $row['ccc_id']].'"
                WHERE ccc_id = "' . $row['ccc_id'] . '"';

        setq($sql);
        $contador++;
      }
    }

    $response = $this->sonTodosIgualesA($estatusG, "C");
    if(!$response){
    
    //Buscamos la remision perteneciente a esta
    $remision = busca($idcotiza, 'crm_cotizaciones', 'cc_id', 'cc_remision');
    //Creamos la remision de esta ccotización
    $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-RM';
    $folioint = getmax('r_folio','remisiones','r_empresa = "'.$_SESSION['emp'].'"');
    if($folioint == "1"){
      $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
    }

    $sqlr = 'SELECT * FROM remisiones WHERE r_id = "'.$remision.'"';
    $resultr = setq($sqlr);
    $rowr = $resultr->fetch_array();
    $tablero = $rowr['r_tablero'];
    //Abrimos el tablero si está finalizado
    $estatustab = busca($tablero, 'crm_tableros', 'ct_id', "ct_estatus");

    if($estatustab == "F"){
      $sqltab = 'UPDATE crm_tableros SET ct_estatus = "A"  WHERE ct_id = "'.$tablero.'"';
      setq($sqltab);
    }

    $cliente = $rowr['r_cliente'];
    $almacen = $rowr['r_almacen'];
    $nmbac = 'COSTO DE ENVÍO EXTRA DE REMISIÓN '.$rowr['r_folio'];
    $responsable = $rowr['r_encargado'];

    $correo = $rowr['r_email'];
    $precioenvio = $_POST['precio'];
    $observaciones = $_POST['comentario'];
    
    $idnuevaremision = $this->crearRemision($tablero, $cliente, $folioint, $almacen, $nmbac, $responsable, $correo, $precioenvio, $observaciones);
    $this->crearCXC($cliente, $idnuevaremision, $precioenvio, $folioint);
    $this->insertRemisionD($idnuevaremision, $nmbac, "1", $precioenvio);
  }

  $formaenvios = array();  
  $formaenvios[0] = $contador2;
  $formaenvios[1] = $contador3;
  return $formaenvios;
}

  function seleccionarenvio($cotizacion)
  {
    $array_cotizaciones = explode(",", $cotizacion);
    $total_cotizaciones = count($array_cotizaciones);

    for ($i = 0; $i < $total_cotizaciones; $i++) {
      $query = 'SELECT ccc_id FROM crm_cotizacionesc WHERE ccc_estatus = "N" AND ccc_cotizacion = "' . $array_cotizaciones[$i] . '"';
      $result = setq($query);
      while ($row = $result->fetch_array()) {
        if (isset($_POST['select' . $row['ccc_id']])) {
          // Estatus P = Articulo de remisión pendiente de embarque (Borrador de salidas)
          $sql = 'UPDATE crm_cotizacionesc SET ccc_estatus = "P", ccc_nguia = "' . $_POST['in' . $row['ccc_id']] . '" WHERE ccc_id = "' . $row['ccc_id'] . '"';
          setq($sql);

          $sqlguia = 'INSERT INTO guias_articulos SET
                    ga_cccid = "' . $row['ccc_id'] . '",
                    ga_guia = "' . $_POST['in' . $row['ccc_id']] . '"';
          setq($sqlguia, true);
        }
      }
    }
  }

  function setenviosgeneral($cotizacion)
  {
    $array_cotizaciones = explode(",", $cotizacion);
    $total_cotizaciones = count($array_cotizaciones);

    for ($i = 0; $i < $total_cotizaciones; $i++) {
      $query = 'SELECT ccc_id FROM crm_cotizacionesc WHERE ccc_cotizacion = "' . $array_cotizaciones[$i] . '" AND ccc_estatus = "N"';
      $result = setq($query);
      while ($row = $result->fetch_array()) {
        if (isset($_POST['select' . $row['ccc_id']])) {
          // Estatus P = Articulo de remisión en borrador de envios
          $sql = 'UPDATE crm_cotizacionesc SET ccc_estatus = "P" WHERE ccc_id = "' . $row['ccc_id'] . '"';
          setq($sql);

          $sqlguia = 'INSERT INTO articulos_guias SET
                      ag_cccid = "' . $row['ccc_id'] . '",
                      ag_guia = "' . $_POST['in' . $row['ccc_id']] . '"';
          setq($sqlguia);
          if(isset($_POST['in' . $row['ccc_id'].'2'])){
            $sqlguia2 = 'INSERT INTO articulos_guias SET
                        ag_cccid = "' . $row['ccc_id'] . '",
                        ag_guia = "' . $_POST['in' . $row['ccc_id'].'2'] . '"';
            setq($sqlguia2);
          }
        }
      }
      /*   $totalreg = busca($idcotiza, 'crm_cotizacionesc', 'ccc_cotizacion', 'COUNT(*)');
        $estatusB = busca($idcotiza, 'crm_cotizacionesc', 'ccc_estatus = "B" AND ccc_cotizacion', 'COUNT(*)');
        if($totalreg == $estatusB){
          // Estatus G = Todos los productos están listos para enviarse y cuando se embarquen se finalizará la remisión
          $sql = 'UPDATE remisiones SET r_estatus = "G" WHERE r_id = "'.$_POST['idremision'].'"';
          setq($sql);
        } */
    }
  }

  function crearRemision($tablero, $cliente, $folioint, $almacen, $nmbac, $responsable, $correo, $precioenvio, $observaciones){
    $sql = 'INSERT INTO remisiones SET
              r_tablero = "'.$tablero.'",
              r_cliente = "'.$cliente.'",
              r_folio = "'.$folioint.'",
              r_almacen = "'.$almacen.'",
              r_descuento = "0",
              r_diva = "0",
              r_subtotal = "'.$precioenvio.'",
              r_total = "'.$precioenvio.'",
              r_iva = "0",
              r_mdescuento = "0",
              r_nmb = "'.$nmbac.'",
              r_encargado = "'.$responsable.'",
              r_email = "'.$correo.'",
              r_fliquidacion = "0000-00-00",
              r_falta = "'.date('Y-m-d H:i:s').'",
              r_faplica = "'.date('Y-m-d H:i:s').'",
              r_ualta = "'.$_SESSION['uid'].'",
              r_uaplica = "'.$_SESSION['uid'].'",
              r_observaciones = "'.$observaciones.'",
              r_precioenvio = "0",
              r_estatus	 = "A"';
      setq($sql);

      $remision = getmax('r_id', 'remisiones', false, false);
      return $remision;
  }

  function crearCXC($cliente, $referencia, $importe, $folio){
    $sql = 'INSERT INTO cxcobrar SET
            cx_cliente = "'.$cliente.'",
            cx_referencia = "'.$referencia.'",
            cx_tipo = "R",
            cx_importe = "'.$importe.'",
            cx_observaciones = "COSTO DE ENVÍO EXTRA DE REMISIÓN '.$folio.'",
            cx_abonado = "0",
            cx_estatus = "N",
            cx_fini = "'.date('Y-m-d H:i:s').'"';
    setq($sql);
  }

  function insertRemisionD($idremision, $nmbarticulo, $cantidad, $precio){
    $sql = 'INSERT INTO remisionesd SET
            rd_remision = "'.$idremision.'",
            rd_nmbarticulo ="'.$nmbarticulo.'",
            rd_cantidad = "'.$cantidad.'",
            rd_precio = "'.$precio.'"';
    setq($sql);
  }
}


class viewguias
{
  function __construct($model)
  {
    ?>
    <script>
      function checkguardar() {
        document.getElementById("sendform").innerHTML = "Guardando";
        document.getElementById("sendform").disabled = true;
        return true;
      }
    </script>
    <?php
  }

  function browse()
  {
 
  /* $pordefinir = '
  <a href="?modulo=guias&accion=prodpordefinir&tipo=1">
    <button type="button" class="btn btn-sm btn-secondary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Productos por definir forma de envío"><i class="fas fa-file-signature"></i> Productos por definir</button>
  </a>'; */
  if($_SESSION['uid'] == "ADMIN")
  $pordefinir = '
        <a data-fancybox data-type="ajax" data-src="popup/mostrarguias.php?rand='.rand().'" href="javascript:;">
          <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Mostrar guías"><i class="fas fa-clipboard-list"></i> Mostrar guías</button>
        </a>';
  else 
  $pordefinir = '';


  

    $filtro = '
  <form class="form-inline" role="form" method="post" action="?modulo=articulos&accion=index" id="filtro">
  <div class="mb-5">
  <label class="mr-sm-2">Registra:</label>
  <select name="nmb" id="nmb" class="form-control">
    <option value="">TODOS</option>';
    $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_grupo IN ("VENTAS", "ADMIN", "GERENCIA")';
    $result = setq($sql);
    while ($row = $result->fetch_array()) {
      if($row['u_id'] != "ADMIN")
      $filtro .= '<option value="' . $row['u_id'] . '">' . $row['u_id'] . '</option>';
    }
    $filtro .= '
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
      <input hidden type="date" id="fini2" class="form-control" value="' . date('Y-m-d', strtotime('-20 days')) . '">
      <div class="form-control-position">
        <i class="icon-calendar5"></i>
      </div>
    </div>
  </div>
  <div class="mb-5">
    <label class="mr-sm-2">Fecha hasta:</label>
    <div class="position-relative has-icon-left">
      <input type="date" id="ffin" name="ffin" class="form-control" value="" required>
      <input hidden type="date" id="ffin2" class="form-control" value="' . date('Y-m-d', strtotime('+10 days')) . '">
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

    toolbar($_GET['modulo'], '', $filtro, $pordefinir);

    echo '
    <div class="card mt-3">
      <div class="card-body">
    <div class=" col-12 table-responsive">
      <center><table width="100%" class="mb-0 table" id="myTable">
      <thead class="bg-light-blue bg-darken-2 ">
        <tr>
          <th><b>Folio</b></th>
          <th><b>Nombre</b></th>
          <th><b>Encargado</b></th>
          <th><b>Almacen</b></th>
          <th><b>Cliente</b></th>
          <th><b>Fecha de liquidación</b></th>
          <th><b>Estatus</b></th>
          <th style="width: 15%;"><b></b></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      </table>
      </div>
    </div>';

        echo '
    <script>
    function mandar(id){
      var ugen = document.getElementById("nmb");
      var fini2 = document.getElementById("fini2");
      var ffin2 = document.getElementById("ffin2");
      var fini = document.getElementById("fini");
      var ffin = document.getElementById("ffin");

      if(id == 1){
        ugen.value = "";
        fini.value = fini2.value;
        ffin.value = ffin2.value;
      }

      //var table = $("#myTable").DataTable();
      $("#myTable").DataTable().clear().draw();
      $("#myTable").DataTable().destroy();

      $("#myTable").DataTable( {
        paging: true,
        scrollY: 400,
        processing: true,
        serverside: true,
        order: [[5, "desc"]],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatableguias.php",
          type: "POST",
          datatype: "json",
          data:{ 
            "ugen" : ugen.value,
            "fini" : fini.value,
            "ffin" : ffin.value
          }
        },
        pageLength: "50",
        responsivePriority: 1,
        createdRow: function(row, data, dataIndex) {
        // Cambia el índice (3) y el valor ("valor_especial") según tu caso
        
        if (parseInt(data[8]) > 0) { 
            console.log("valor", data[7]);
            $(row).css("background-color", "#d1b9ff");
        }
    }
      });
    }
    
    mandar(1);
  </script>
  <style>
    .highlight-row {
        background-color: #f2dede; /* Cambia este color según tus necesidades */
    }
  </style>
  ';
  }

  function prodpordefinir()
  {
    $atras = '
  <a href="?modulo=guias&accion=index">
    <button type="button" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
  </a>';

    $filtro = '
  <form class="form-inline" role="form" method="post" action="?modulo=articulos&accion=index" id="filtro">
  <div class="mb-5">
  <label class="mr-sm-2">Registra:</label>
  <select name="nmb" id="nmb" class="form-control">
    <option value="">TODOS</option>';
    $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_empresa = "' . $_SESSION['emp'] . '" AND (u_grupo = "VENTAS" OR u_grupo = "ADMIN" OR u_grupo = "GERENCIA")';
    $result = setq($sql);
    while ($row = $result->fetch_array()) {
      $filtro .= '<option value="' . $row['u_id'] . '">' . $row['u_id'] . '</option>';
    }
    $filtro .= '
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
      <input hidden type="date" id="fini2" class="form-control" value="' . date('Y-m-d', strtotime('-20 days')) . '">
      <div class="form-control-position">
        <i class="icon-calendar5"></i>
      </div>
    </div>
  </div>
  <div class="mb-5">
    <label class="mr-sm-2">Fecha hasta:</label>
    <div class="position-relative has-icon-left">
      <input type="date" id="ffin" name="ffin" class="form-control" value="" required>
      <input hidden type="date" id="ffin2" class="form-control" value="' . date('Y-m-d', strtotime('last day of this month')) . '">
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

    toolbar('ENVIOS POR DEFINIR', $atras, $filtro);

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
          <th><b>Encargado</b></th>
          <th><b>Almacen</b></th>
          <th><b>Cliente</b></th>
          <th><b>Estatus</b></th>
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
    function mandar(id){
      var ugen = document.getElementById("nmb");
      var fini2 = document.getElementById("fini2");
      var ffin2 = document.getElementById("ffin2");
      var fini = document.getElementById("fini");
      var ffin = document.getElementById("ffin");

      if(id == 1){
        ugen.value = "";
        fini.value = fini2.value;
        ffin.value = ffin2.value;
      }

      //var table = $("#myTable").DataTable();
      $("#myTable").DataTable().clear().draw();
      $("#myTable").DataTable().destroy();

      $("#myTable").DataTable( {
        paging: true,
        scrollY: 400,
        processing: true,
        serverside: true,
        ordering: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatableprodpordefinir.php",
          type: "POST",
          datatype: "json",
          data:{ 
            "tipo" : "1",
            "ugen" : ugen.value,
            "fini" : fini.value,
            "ffin" : ffin.value
          }
        },
        pageLength: "50",
        responsivePriority: 1,
      });
    }
    
    mandar(1);
  </script>
  ';
  }
}
?>