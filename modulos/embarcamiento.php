<?php
ini_set('display_errors',0);

class embarcamiento
{
  var $model;
  var $view;
  function __construct()
  {
    $this->model = new modelembarcamiento(isset($obj));
  }
  function index()
  {
    $this->view = new viewembarcamiento($this->model);
    $this->view->browse();
  }

  function show()
  {
    $this->view = new viewembarcamiento($this->model);
    $this->view->show();
  }

  function insert()
  {
    $this->model->setdata('', $_POST['nmb'], $_POST['paqueteria'], $_SESSION['uid'], date('Y-m-d H:i:s'), $_POST['fechaenvio'], '', "A");
    $this->model->insert();
    $id = getmax('e_id', 'embarques', false, false);
    redirect('?modulo=embarcamiento&accion=show&id=' . $id);
  }
  function seleccionarenvio()
  {
    //foreachdie();
    $idguias = $_POST['idguias'];
    $idgarantias = $_POST['idgarantias'];
    $tipoe = $_POST['tipoe'];
    if ($tipoe == "C") {
      $resultado = $this->model->seleccionarenviocliente($idguias);

      $nguias = $resultado[0];
      $ccid = $resultado[1];

      if ($nguias == 1) {
        $titulo = 'PRODUCTO EMBARCADO';
        $cuerpo = $nguias . ' producto ha sido embarcado';
      } else {
        $titulo = 'PRODUCTOS EMBARCADOS';
        $cuerpo = $nguias . ' productos han sido embarcados';
      }
    } else {
      $resultado = $this->model->seleccionarenvio($idguias);

      $nguias = $resultado[0];
      $cotizaciones = $resultado[1];

      if ($nguias == 1) {
        $titulo = 'GUÍA EMBARCADA';
        $cuerpo = $nguias . ' guía ha sido embarcada';
      } else {
        $titulo = 'GUÍAS EMBARCADAS';
        $cuerpo = $nguias . ' guías han sido embarcadas';
      }
    }
    $resultadon = $this->model->embarcargarantias($idgarantias);
    //NOTIFICACIÓN PARA LOGÍSTICA
    $grupos = '"LOGISTIC"';
    echo "
        <script>
          $.ajax({
            url: 'task/notificacionpushgrupos.php',
            method: 'POST',
            data: {
                    'tipo': 'GUIAEMBARCADA',
                    'grupos': '" . $grupos . "',
                    'titulo': '" . $titulo . "',
                    'cuerpo': '" . $cuerpo . "',
                    'tipoe': '" . $tipoe . "'
                  },
          }).done(function(data){
          });          
          </script>
        ";

    //NOTIFICACIÓN PARA EL VENDEDOR
    $grupos = '"VENTAS","ADMIN","GERENCIA"';
    echo "
        <script>
          $.ajax({
            url: 'task/notificacionpushembarques.php',
            method: 'POST',
            data: {
                    'grupos': '" . $grupos . "',
                    'cotizaciones': '" . $cotizaciones . "',
                    'cccid': '" . $ccid . "'
                  },
          }).done(function(data){
            window.location.href = '?modulo=embarcamiento&accion=show&id=" . $_GET['id'] . "';
          });          
          </script>
        ";

    /* redirect('?modulo=embarcamiento&accion=show&id='.$_GET['id']); */
  }

  function finalizarembarque()
  {
    $idguias = $_POST['idguias'];
    $idgarantias = $_POST['idgarantias'];
    $tipoe = $_POST['tipoe'];
    if ($tipoe == "C") {
      $resultado = $this->model->seleccionarenviocliente($idguias);

      $nguias = $resultado[0];
      $ccid = $resultado[1];

      if ($nguias == 1) {
        $cuerpo = $nguias . ' producto embarcado';
      } else {
        $cuerpo = $nguias . ' productos embarcados';
      }
    } else {
      $resultado = $this->model->seleccionarenvio($idguias);

      $nguias = $resultado[0];
      $cotizaciones = $resultado[1];

      if ($nguias == 1) {
        $cuerpo = $nguias . ' guía embarcada';
      } else {
        $cuerpo = $nguias . ' guías embarcadas';
      }
    }

    $resultadon = $this->model->embarcargarantias($idgarantias);

    $embarque = $_POST['embarque'];
    $this->model->finalizarembarque($embarque);

    //NOTIFICACIÓN PARA LOGÍSTICA
    $grupos = '"LOGISTIC"';
    echo "
        <script>
          $.ajax({
            url: 'task/notificacionpushgrupos.php',
            method: 'POST',
            data: {
                    'tipo': 'GUIAEMBARCADA',
                    'grupos': '" . $grupos . "',
                    'cuerpo': '" . $cuerpo . "',
                    'tipoe': '" . $tipoe . "'
                  },
          }).done(function(data){
          });          
          </script>
        ";

    //NOTIFICACIÓN PARA EL VENDEDOR
    $grupos = '"VENTAS","ADMIN","GERENCIA"';
    echo "
        <script>
          $.ajax({
            url: 'task/notificacionpushembarques.php',
            method: 'POST',
            data: {
                    'grupos': '" . $grupos . "',
                    'cotizaciones': '" . $cotizaciones . "',
                    'cccid': '" . $ccid . "'
                  },
          }).done(function(data){
            window.location.href = '?modulo=embarcamiento&accion=index';
          });          
          </script>
        ";
    /* redirect('?modulo=embarcamiento&accion=index'); */
  }

}

class modelembarcamiento
{
  function seleccionarenvio($guias)
  {
    $array_guias = explode(",", $guias);
    $total_guias = count($array_guias);
    $cotizaciones = '';
    $contador = 0;


    for ($i = 0; $i < $total_guias; $i++) {
      if (isset($_POST['select' . $array_guias[$i]])) {
        // Verificamos que la guía no este finalizada previamente
        $gaestatus = busca($array_guias[$i], 'guias_articulos', 'ga_id', 'ga_estatus');
        if ($gaestatus == "P") {
          $sql = 'UPDATE guias_articulos SET ga_estatus = "F", ga_embarque = "' . $_POST['embarque'] . '", ga_ffin="' . date("Y-m-d H:i:s") . '" WHERE ga_id = "' . $array_guias[$i] . '"';
          setq($sql);

          $sqlccc = 'UPDATE remisionesc SET rc_estatus = "F", rc_embarque = "' . $_POST['embarque'] . '", rc_fenvio = "'.date("Y-m-d").'" WHERE rc_guia = "' . $array_guias[$i] . '"';
          setq($sqlccc);
          //Procedemos a ajustar existencias

          $remision = busca($array_guias[$i], 'guias_articulos', 'ga_id', 'ga_cotizacion');
          $nguias = busca($remision, 'guias_articulos', 'ga_estatus IN ("P", "N", "A") AND ga_nmb IS NOT NULL AND ga_cotizacion', 'COUNT(*)');
          //BUSCAMOS SI EXISTE ALGUN ARTÍCULO QUE AUN NO SE HAYA ASIGNADO A GUÍA
          $artfaltantes = intval(busca($remision, 'remisionesc', 'rc_estatus IN ("N", "P") AND rc_tipoenvio IN ("O", "D", "C", "P") AND rc_remision', 'COUNT(*)'));

          if ($artfaltantes == 0) {
            $sqlr = 'UPDATE remisiones SET r_estatus = "FE" WHERE r_id = "' . $remision . '"';
            setq($sqlr);
          }
        }
        $ccid = $array_guias[$i] . ",";
        $contador++;
      }
    }

    // Elimina la coma del final si existe
    if (substr($ccid, -1) === ',') {
      $cadena = rtrim($ccid, ',');
    }

    return array($contador, $cadena);

  }

  function seleccionarenviocliente($guias)
  {
    $array_guias = explode(",", $guias);
    $total_guias = count($array_guias);
    $ccid = '';
    $contador = 0;

    for ($i = 0; $i < $total_guias; $i++) {
      if (isset($_POST['select' . $array_guias[$i]])) {
        // Estatus A = Articulos de las remisiones embarcados
        /* $sql = 'UPDATE guias_articulos SET ga_estatus = "F", ga_embarque = "'.$_POST['embarque'].'", ga_ffin="'.date("Y-m-d H:i:s").'" WHERE ga_id = "'.$array_guias[$i].'"'; */
        /* setq($sql); */

        $sqlccc = 'UPDATE remisionesc SET rc_estatus = "F", rc_embarque = "' . $_POST['embarque'] . '", rc_fenvio = "'.date("Y-m-d").'" WHERE rc_id = "' . $array_guias[$i] . '"';
        setq($sqlccc);
        //Procedemos a ajustar existencias

        $remision = busca($array_guias[$i], 'remisionesc', 'rc_id', 'rc_remision');
        $narticulos = intval(busca($remision, 'remisionesc', 'rc_estatus IN ("P", "N") AND rc_tipoenvio IN ("O", "D", "C", "P") AND rc_remision', 'COUNT(*)'));
        if ($narticulos == 0) {
          $sqlr = 'UPDATE remisiones SET r_estatus = "FE" WHERE r_id = "' . $remision . '"';
          setq($sqlr);
        }
        $ccid = $array_guias[$i] . ",";
        $contador++;
      }
    }

    // Elimina la coma del final si existe
    if (substr($ccid, -1) === ',') {
      $cadena = rtrim($ccid, ',');
    }

    return array($contador, $cadena);

  }

  function seleccionarenviocliente2($guias)
  {
    $array_g = explode(",", $guias);

    $array_guias = array_unique($array_g);

    $total_guias = count($array_guias);
    $ccid = '';
    $contador = 0;

    for ($i = 0; $i < $total_guias; $i++) {
      if (isset($_POST['select' . $array_guias[$i]])) {
        // Estatus A = Articulos de las remisiones embarcados
        /* $sql = 'UPDATE guias_articulos SET ga_estatus = "F", ga_embarque = "'.$_POST['embarque'].'", ga_ffin="'.date("Y-m-d H:i:s").'" WHERE ga_id = "'.$array_guias[$i].'"'; */
        /* setq($sql); */

        $sqlccc = 'UPDATE remisionesc SET rc_estatus = "F", rc_embarque = "' . $_POST['embarque'] . '", rc_fenvio = "'.date("Y-m-d").'" WHERE rc_id = "' . $array_guias[$i] . '"';
        setq($sqlccc);

        $sqlccc2 = 'UPDATE remisionesc SET rc_estatus = "F", rc_embarque = "' . $_POST['embarque'] . '", rc_fenvio = "'.date("Y-m-d").'" WHERE rc_ligado = "' . $array_guias[$i] . '"';
        setq($sqlccc2);
        //Procedemos a ajustar existencias

        $cotizacion = busca($array_guias[$i], 'remisionesc', 'rc_id', 'rc_remision');
        /* $sqlcot = 'SELECT rd_cotizacion FROM remisionesd WHERE rd_id = "' . $array_guias[$i] . '"';
        $resultcot = setq($sqlcot);
        list($cotizacion) = $resultcot->fetch_array(); */
        $narticulos = busca($cotizacion, 'remisionesc', 'rc_estatus IN ("P", "N") AND rc_remision', 'COUNT(*)');
        $remision = busca($cotizacion, 'remisiones', 'r_id', 'r_remision');
        if ($narticulos == 0) {
          $tablero = busca($remision, 'remisiones', 'r_id', 'r_tablero');
          $sqlr = 'UPDATE remisiones SET r_estatus = "FE" WHERE r_id = "' . $remision . '"';
          setq($sqlr);

          $sqlt = 'UPDATE crm_tableros SET ct_estatus = "X" WHERE ct_id = "' . $tablero . '"';
          setq($sqlt);
        }
        $ccid = $array_guias[$i] . ",";
        $contador++;
      }
    }

    // Elimina la coma del final si existe
    if (substr($ccid, -1) === ',') {
      $cadena = rtrim($ccid, ',');
    }

    return array($contador, $cadena);

  }

  function embarcargarantias($idgarantias){
    $k = 0;
    $array_g = explode(",", $idgarantias);

    $array_guias = array_unique($array_g);

    $total_guias = count($array_guias);

    for ($i = 0; $i < $total_guias; $i++) {
      if(isset($_POST['garantia'.$array_guias[$i]])){
        $sqlccc = 'UPDATE garantias_articulos SET ga_estatus = "F", ga_embarcamiento = "' . $_POST['embarque'] . '", ga_fenvio = "'.date("Y-m-d").'", ga_ffin = "'.date("Y-m-d H:i:s").'" WHERE ga_id = "' . $array_guias[$i] . '"';
        setq($sqlccc);
        $k++;
      }
    }
    return $k;
  }

  function setdata($id, $nmb, $paqueteria, $ugenera, $fini, $ffin, $ufinaliza, $estatus)
  {
    $this->nmb = clearvmayus($nmb);
    $this->paqueteria = $paqueteria;
    $this->ugenera = clearvmayus($ugenera);
    $this->fini = $fini;
    $this->ffin = $ffin;
    $this->ufinaliza = clearvmayus($ufinaliza);
    $this->estatus = clearvmayus($estatus);
  }

  function insert()
  {
    $sql = 'INSERT INTO embarques SET
          e_nmb = "' . $this->nmb . '",
          e_paqueteria = "' . $this->paqueteria . '",
          e_ugenera = "' . $this->ugenera . '",
          e_fini = "' . $this->fini . '",
          e_ffin = "' . $this->ffin . '",
          e_estatus = "' . $this->estatus . '"';
    setq($sql);
  }

  function finalizarembarque($embarque)
  {
    $sql = 'UPDATE embarques SET e_estatus = "F", e_ufinaliza = "' . $_SESSION['uid'] . '", e_hfin = "' . date("H:i:s") . '", e_ffin = "'.date('Y-m-d').'" WHERE e_id = "' . $embarque . '"';
    setq($sql);
  }
}

class viewembarcamiento
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
    $nembarques = 0;
    //Buscamos los artículos que recoge el cliente
/*     $sql = 'SELECT DISTINCT(rc_remision), COUNT(*) AS cantidad 
        FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision WHERE rc_estatus = "P" AND rc_tipoenvio = "C" AND r_estatus NOT IN ("N", "C")';
    $result = setq($sql);
    while ($row = $result->fetch_array()) {
      $remision = busca($row['rc_remision'], 'remisiones', 'r_id IS NOT NULL AND r_estatus = "V" AND r_id', 'r_id');
      if (!empty($remision)) {
        $remisioncount = intval(busca($remision, 'remisiones', 'r_estatus = "F" AND r_id', 'COUNT(*)'));
        if ($remisioncount > 0) {
          $registros = intval($row['cantidad']);
          $nembarques += $registros;
        }
      }
    } */

  $sqlpaq = 'SELECT p_id, p_nmb FROM paqueterias WHERE p_estatus = "A"';
	$resultpaq = setq($sqlpaq);
	while($rowpaq = $resultpaq->fetch_array()){

		$registros = intval(busca($rowpaq['p_id'], 'guias_articulos', 'ga_estatus = "P" AND ga_paqueteria', 'COUNT(*)'));
		if($registros > 0){
			$nembarques += $registros;
		}
	}

	$sql = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision
      WHERE r_estatus IN ("F", "A") AND rc_estatus IN ("P") AND rc_tipoenvio = "C"';
	$result = setq($sql);
	list($registrosc) = $result->fetch_array();

	if($registrosc > 0){
		$nembarques += $registrosc;
	}

    $nuevo = '
  <a data-fancybox data-type="ajax" data-src="popup/setembarque.php" href="javascript:;">
    <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Nuevo embarque"><i class="fas fa-plus"></i> Nuevo embarque</button>
  </a>';

    $información = '
  <a data-fancybox data-type="ajax" data-src="popup/articulosembarque.php" href="javascript:;">
    <button type="button" class="btn btn-sm btn-secondary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Mostrar por paquetería la cantidad de artículos disponibles para embarcar"><i class="fas fa-info-circle"></i> Información
    ';
    if ($nembarques > 0) {
      $información .= '<span class="badge bg-danger">' . $nembarques . '</span>';
    }
    $información .= '</button>
  </a>';

    $filtro = '
  <form class="form-inline" role="form" method="post" action="?modulo=articulos&accion=index" id="filtro">
  <div class="mb-5">
  <label class="mr-sm-2">Genera:</label>
  <select name="nmb" id="nmb" class="form-control">
    <option value="">TODOS</option>';
    $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_grupo IN ("LOGISTIC", "ADMIN", "GERENCIA") AND u_empresa = "' . $_SESSION['emp'] . '"';
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

    toolbar($_GET['modulo'], "", $filtro, $nuevo, $información);

    ?>
    <div class="card mt-3">
      <div class="card-body">
        <?php
        echo '
    <div class=" col-12 table-responsive">
      <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
      <thead class="bg-light-blue bg-darken-2 ">
        <tr>
          <th><b>Nombre</b></th>
          <th><b>Paquetería</b></th>
          <th><b>Genera</b></th>
          <th><b>Fecha creación</b></th>
          <th><b>Fecha de envío programada</b></th>
          <th><b>Embarcó</b></th>
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
      
      var windowHeight = $(window).height();

      $("#myTable").DataTable( {
        paging: true,
        scrollY: windowHeight * 0.5,
        processing: true,
        serverside: true,
        ordering: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatableembarcamiento.php",
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
      });
    }
    
    mandar(1);
  </script>
  ';
  }

  function show()
  {
    $embarque = $_GET['id'];
    /* $estatusembarque = busca($embarque, 'embarques', 'e_id', 'e_estatus');
    $nmbembarque = busca($embarque, 'embarques', 'e_id', 'e_nmb');
    $idpaqueteria = busca($embarque, 'embarques', 'e_id', 'e_paqueteria'); */
    $sqlemb = 'SELECT e_estatus, e_nmb, e_paqueteria FROM embarques WHERE e_id = "'.$embarque.'"';
    $resultemb = setq($sqlemb);
    list($estatusembarque, $nmbembarque, $idpaqueteria) = $resultemb -> fetch_array();
    $nmbpaqueteria = busca($idpaqueteria, 'paqueterias', 'p_id', 'p_nmb');
    $paqt = $idpaqueteria;
    
    if ($idpaqueteria == -1) {
      $idpaqueteria = 0;
      $sqlf = 'AND rc_paqueteria = "' . $idpaqueteria . '" AND rc_tipoenvio = "C" ';
    } else {
      $sqlf = 'AND rc_paqueteria = "' . $idpaqueteria . '" AND rc_tipoenvio != "C" ';
    }
    $nuevo = '
    <a data-fancybox data-type="ajax" data-src="popup/setembarque.php" href="javascript:;">
      <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Nuevo embarque"><i class="fas fa-plus"></i> Nuevo embarque</button>
    </a>';


    $atras = '
    <a href="?modulo=embarcamiento&accion=index">
      <button type="button" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>Atrás</button>    
    </a>
    ';

    if ($estatusembarque != "F") {
      $finalizar = '
        <button type="button" onclick="finalizarembarque(' . $embarque . ')"; class="btn btn-sm btn-info"><i class="fas fa-check"></i>Finalizar embarque</button>    
      ';

      $guardar = '
      <button type="button" class="btn btn-sm btn-primary" onclick="clickGuardar();"><i class="fas fa-save"></i>Guardar</button>
      ';
    } else {
      $finalizar = '';
      $guardar = '';
    }

    $titulo = "Nombre del embarque: " . $nmbembarque;
    $titulo2 = "Paquetería: " . $nmbpaqueteria;
    toolbar($_GET['modulo'], $atras, '', $guardar, $finalizar);

    if ($estatusembarque == "F") {
      echo '
      <div class="alert alert-danger" role="alert">
        <center>Este embarque está finalizado</center>
      </div>
      ';
    }



    echo '
    <style>
    #myTableO {
      width: 100%;
      border-collapse: collapse;
    }

    #myTableO th, #myTableO td {
      padding: 8px;
      text-align: left;
      border: 1px solid #ddd;
      /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
    }

    #myTableO tbody tr:nth-child(odd) td {
      /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
    }

    #myTableO tbody tr:nth-child(even) td {
      /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
    }
    </style>
    ';
    //INICIA LA TABLA DE PRODUCTOS DE TODAS LAS REMISIONES
    if ($paqt == -1) {
      $tamanio = 6;
      $titulo2 = "Tipo de envío: Recoge cliente";
    } else {
      $tamanio = 4;
    }
    echo '
  <div class="container mt-1 ">
  <form id="finalizaForm" action="?modulo=embarcamiento&accion=seleccionarenvio&id=' . $_GET['id'] . '" method="post" onsubmit="checksubmit();" enctype="multipart/form-data">
  <div class="row">
  <div class="row mt-5" style="background: white;">
  <div class="card-body">
  <center>
    <div class="table-responsive col-12">
    <div class="row">
    <div class="col-' . $tamanio . '" style="display: flex; justify-content: center;">
    <b><i><span style="justify-content: left; display: flex; color: black;">' . $titulo . '</span></i></b><br>
    </div>      
    <div class="col-' . $tamanio . '" style="display: flex; justify-content: center;">
      <b><i><span style="justify-content: left; display: flex; color: black;">' . $titulo2 . '</span></i></b><br>
    </div>';
    if ($paqt != -1) {
      echo '
      <div class="col-4" style="display: flex; justify-content: center;">
        <b><i><span id="totalreg" style="justify-content: left; display: flex; color: black;"></span></i></b><br>
      </div>';

      echo '</div>
      <table class="table table-hover" id="myTableO">
      <thead class="thead-active bg-primary text-white">
        <tr>
          <th width="45%">&nbsp;&nbsp;Detalles</th>
          <th width="25%">Productos</th>
          <th width="15%">Fecha envío</th>
          <th width="15%">&nbsp;&nbsp;Embarcar</th>
        </tr>
      </thead>
      <tbody>';
     //COMIENZA EL LLENADO DE TABLA CON LOS ARTICULOS DE LAS GARANTIAS
     $z1 = 0;
     $idgarantias = '';
     $sqlga = 'SELECT * FROM garantias_articulos WHERE ga_estatus IN ("M","F") AND ga_tipoenvio IN ("O","D") AND ga_guia IS NOT NULL AND (ga_embarcamiento IN ("' . $_GET['id'] . '") OR ga_embarcamiento IS NULL) ORDER BY ga_id ASC;';
     $resultga = setq($sqlga);
     while($rowga = $resultga->fetch_array()){
 
 
       if(!empty($rowga['ga_embarcamiento'])){
         $checkb = "checked disabled";
       } else{
         $checkb = "";
       }
 
       if ($estatusembarque == "F") {
         $checkb = '';
         $check = ' checked disabled';
         $back = ' style="background-color: rgb(204, 255, 204);"';
       } else {
         if(!empty($rowga['ga_embarcamiento'])){
           $checkb = '';
           $check = ' checked disabled';
           $back = ' style="background-color: rgb(204, 255, 204);"';
         } else {
           $checkb = ' disabled';
           $check = '';
           $back = '';
         }
       }
 
       $boton = '
       <button ' . $checkb . ' id="evidga' . $rowga['ga_id'] . '" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenesgarantia.php?id='.$rowga['ga_id'].'&embarque='.$_GET['id'].'&tipo=E" href="javascript:;" >
         <i class="fa fa-plus"></i> Evidencia
        </button>';
 
       $articulosg = '
       <tr style="background: #b5b5c3 !important;">
         <td class="sin-hover" colspan="3">&nbsp;&nbsp;</td>
         <td class="sin-hover"> ' . $boton . '</td>
       </tr>';
 
       
             $articulosg .= '
       <tr ' . $back . '>
         <td></td>
         <td rowspan="6">';
             $k = 0;
             $fenvio = '';
               if ($k == 0) {
                 $fenvio = $rowga['ga_fenvio'];
               }
 
               if(empty($rowga['ga_rcid'])){
                 $remision = $rowga['ga_remision'];
                 $folioremision = $remision;
                 $anmb = $rowga['ga_articulo']." ".$rowga['ga_modelo'];
               } else{
                 $remision = busca($rowga['ga_remision'], 'remisiones', 'r_id', 'r_folio');
                 $folioremision = busca($remision, 'remisiones', 'r_id', 'r_folio');
                 $anmb = busca($rowga['ga_articulo'], "articulos", "a_id", "a_nmb");
                 $var = busca($rowga['ga_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowga['ga_modelo'] . '" AND av_articulo', 'COUNT(*)');
                 if ($var > 0) {
                   $anmb .= " " . busca($rowga['ga_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowga['ga_modelo'] . '" AND av_articulo', 'av_nmb');
                 }
               }
 
             $articulosg .= ' ' . $anmb . '<br>';
             $numarticulos++;           
 
             $articulosg .= '
         </td>
         <td rowspan="6">
         <center>
           <input class="form-control" type="text" value="' . $fenvio . '" readonly>
         </center>
         </td>
         <td rowspan="6">
         <center>
           <input class="form-check-input consult-check-ga" type="checkbox" onclick="selectGarantia(' . $rowga['ga_id'] . ')" name="garantia' . $rowga['ga_id'] . '" id="garantia' . $rowga['ga_id'] . '" value="' . $rowga['ga_id'] . '" ' . $check . '>
         </center>
         </td>
       </tr>';
       $remision = $rowga['ga_remision'];
 
       $dire = $rowga['ga_direccion'];
       $country = intval(busca($dire, 'crm_direcciones', 'cd_id', 'cd_pais'));
       $dirsend = '';
       if ($country != 146 && $country != 0) {
       $dirsend = busca($dire, 'crm_direcciones', 'cd_id', 'cd_calle');
       } else {
       $dirsend = busca($dire, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
       }
       $pais = busca($country, 'paises', 'p_id', 'p_nmb');
       $direccion = $dirsend . ', ' . $pais;
 
       $client = $rowga['ga_cliente'];
       $cliente = busca($client, 'crm_clientes', 'c_id', 'c_nmb');
       $cliente .= " ".busca($client, 'crm_clientes', 'c_id', 'c_apellidos');
 
             $articulosg .= '
       <tr ' . $back . '>
         <td>Número de guía: ' . $rowga['ga_guia'] . '</td>
       </tr>
       <tr ' . $back . '>
         <td>Remisión: ' . $folioremision . '</td>
       </tr>
       <tr ' . $back . '>
         <td>Artículo ingresado a garantía</td>
       </tr>
       <tr ' . $back . '>
         <td>Cliente: ' . $cliente . '</td>
       </tr>
       <tr ' . $back . '>
         <td>Dirección: ' . $direccion . '</td>
       </tr>
       ';
 
       $embarqueguia = intval(busca($rowga['ga_id'], 'garantias_articulos', 'ga_id', 'ga_embarcamiento'));
       if ($estatusembarque == "F") {
         if (intval($_GET['id']) == $embarqueguia) {
           if ($z1 == 0) {
             $idgarantias .= $rowga['ga_id'];
           } else {
             $idgarantias .= "," . $rowga['ga_id'];
           }
           echo $articulosg;
           $z1++;
         }
       } else {
         if (intval($_GET['id']) == $embarqueguia || $embarqueguia == 0) {
           if ($z1 == 0) {
             $idgarantias .= $rowga['ga_id'];
           } else {
             $idgarantias .= "," . $rowga['ga_id'];
           }
           echo $articulosg;
           $z1++;
         }
       }
 
     }
     //FINALIZA EL LLENADO DE TABLA CON LOS ARTICULOS DE LAS GARANTIAS
    } else {
      echo '</div>
      <table class="table table-hover" id="myTableO">
      <thead class="thead-active bg-primary text-white">
        <tr>
          <th width="60%">&nbsp;&nbsp;Detalles</th>
          <th width="25%">Embarcar</th>
          <th width="15%">&nbsp;&nbsp;Fecha envío</th>
        </tr>
      </thead>
      <tbody>';
          //COMIENZA EL LLENADO DE TABLA CON LOS ARTICULOS DE LAS GARANTIAS
    $z1 = 0;
    $idgarantias = '';
    $sqlga = 'SELECT * FROM garantias_articulos WHERE ga_estatus IN ("M","F") AND ga_tipoenvio IN ("C") AND (ga_embarcamiento IN ("' . $_GET['id'] . '") OR ga_embarcamiento IS NULL) ORDER BY ga_id ASC;';
    $resultga = setq($sqlga);
    while($rowga = $resultga->fetch_array()){
      $estatuscxc = intval(busca($rowga['ga_id'], 'cxcobrar', 'cx_tipo = "B" AND cx_estatus = "F" AND cx_referencia', 'COUNT(*)'));
      if($estatuscxc > 0){


      if(!empty($rowga['ga_embarcamiento'])){
        $checkb = "checked disabled";
      } else{
        $checkb = "";
      }

      if ($estatusembarque == "F") {
        $checkb = '';
        $check = ' checked disabled';
        $back = ' style="background-color: rgb(204, 255, 204);"';
      } else {
        if(!empty($rowga['ga_embarcamiento'])){
          $checkb = '';
          $check = ' checked disabled';
          $back = ' style="background-color: rgb(204, 255, 204);"';
        } else {
          $checkb = ' disabled';
          $check = '';
          $back = '';
        }
      }

      if(empty($rowga['ga_rcid'])){
        $remision = $rowga['ga_remision'];
        $folioremision = $remision;
        $anmb = $rowga['ga_articulo']." ".$rowga['ga_modelo'];
      } else{
        $remision = busca($rowga['ga_remision'], 'remisiones', 'r_id', 'r_folio');
        $folioremision = busca($remision, 'remisiones', 'r_id', 'r_folio');
        $anmb = busca($rowga['ga_articulo'], "articulos", "a_id", "a_nmb");
        $var = busca($rowga['ga_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowga['ga_modelo'] . '" AND av_articulo', 'COUNT(*)');
        if ($var > 0) {
          $anmb .= " " . busca($rowga['ga_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowga['ga_modelo'] . '" AND av_articulo', 'av_nmb');
        }
      }

      $dire = $rowga['ga_direccion'];
      $country = intval(busca($dire, 'crm_direcciones', 'cd_id', 'cd_pais'));
      $dirsend = '';
      if ($country != 146 && $country != 0) {
      $dirsend = busca($dire, 'crm_direcciones', 'cd_id', 'cd_calle');
      } else {
      $dirsend = busca($dire, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
      }
      $pais = busca($country, 'paises', 'p_id', 'p_nmb');
      $direccion = $dirsend . ', ' . $pais;

      $client = $rowga['ga_cliente'];
      $cliente = busca($client, 'crm_clientes', 'c_id', 'c_nmb');
      $cliente .= " ".busca($client, 'crm_clientes', 'c_id', 'c_apellidos');
      $boton = '';
      
      $boton .= '
      <button ' . $checkb . ' id="evidga' . $rowga['ga_id'] . '" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenesgarantia.php?id='.$rowga['ga_id'].'&embarque='.$_GET['id'].'&tipo=E" href="javascript:;" >
        <i class="fa fa-plus"></i> Evidencia
      </button>';

      $articulosg = '
      <tr style="background: #b5b5c3 !important;">
      <td class="sin-hover" colspan="4">Remisión: '.$folioremision.'&nbsp;&nbsp;&nbsp;&nbsp;Cliente: '.$cliente.'&nbsp;&nbsp;&nbsp;&nbsp;Artículo ingresado a garantía</td>
      </tr>';

      
            $articulosg .= '
      <tr ' . $back . '>
        <td>';
            $k = 0;
            $fenvio = '';
              if ($k == 0) {
                $fenvio = $rowga['ga_fenvio'];
              }


            $articulosg .= ' ' . $anmb . '<br>';
            $numarticulos++;           

            $articulosg .= '
      </td>
      <td>
      <center>
        <input class="form-check-input consult-check" onclick="selectGarantia('.$rowga['ga_id'].')" type="checkbox" name="garantia'.$rowga['ga_id'].'" id="garantia'.$rowga['ga_id'].'" value="'.$rowga['ga_id'].'" '.$check.'>
      </center>
      </td>
      <td>
        <input type="text" class="form-control" value="'.$fenvio.'" readonly>
      </td>
      <!-- <td>
        '.$boton.'
      </td> -->
      </tr>';

        $embarqueguia = intval(busca($rowga['ga_id'], 'garantias_articulos', 'ga_id', 'ga_embarcamiento'));
        if ($estatusembarque == "F") {
          if (intval($_GET['id']) == $embarqueguia) {
            if ($z1 == 0) {
              $idgarantias .= $rowga['ga_id'];
            } else {
              $idgarantias .= "," . $rowga['ga_id'];
            }
            echo $articulosg;
            $z1++;
          }
        } else {
          if (intval($_GET['id']) == $embarqueguia || $embarqueguia == 0) {
            if ($z1 == 0) {
              $idgarantias .= $rowga['ga_id'];
            } else {
              $idgarantias .= "," . $rowga['ga_id'];
            }
            echo $articulosg;
            $z1++;
          }
        }

      }
    }
    //FINALIZA EL LLENADO DE TABLA CON LOS ARTICULOS DE LAS GARANTIAS
    }

    //COMIENZA EL LLENADO DE TABLA CON LOS ARTICULOS
    
    if($estatusembarque != "F"){
        
        /* $totalremisiones = intval(busca($rowcot['r_tablero'], 'remisiones', 'r_estatus NOT IN ("C") AND r_tablero', 'COUNT(*)'));
        $remisionesf = intval(busca($rowcot['r_tablero'], 'remisiones', 'r_estatus IN ("F", "FE") AND r_tablero', 'COUNT(*)')); */
        //if ($totalremisiones == $remisionesf){
          

          //CASO DE ENTREGA A DOMICILIO Y OCURRE
          
          if ($paqt != -1) {
            $tipoe = "A";
            
            $querycot = 'SELECT r_id, r_tablero, r_encargado, r_cliente, c_nmb, c_apellidos, r_folio FROM remisiones INNER JOIN crm_clientes ON c_id = r_cliente
                WHERE r_estatus IN ("F", "A") ORDER BY r_id ASC';
      
              $numarticulos = 0;
              $resultcot = setq($querycot);
              $n = 0;
              $z = 0;
              while ($rowcot = $resultcot->fetch_array()) { 
                $idremision = $rowcot['r_id'];
                  $encargado = $rowcot['r_encargado'];
                  $vendedor = busca($encargado, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');

                  $client0 = $rowcot['r_cliente'];
                  $cliente = $rowcot['c_nmb']. ' '.$rowcot['c_apellidos'];

                  $folioremision = $rowcot['r_folio'];

                  $direnvio = busca($idremision, 'crm_cotizaciones', 'cc_remision', 'cc_direnvio');
                  $direccion = busca($direnvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi,", ",cd_colonia,", ",cd_municipio,", ",e_nmb,", C.P.:",cd_cp)');
                
                $queryguiac = 'SELECT DISTINCT rc_guia AS rc_guia, ga_nmb, ga_estatus FROM remisionesc INNER JOIN guias_articulos ON rc_guia = ga_id
                    WHERE rc_remision = "' . $idremision . '" AND ga_estatus = "P" AND ga_paqueteria = "' . $idpaqueteria . '"
                    AND (rc_embarque IN ("' . $_GET['id'] . '", "0") OR rc_embarque IS NULL)
                    ORDER BY rc_guia ASC;';
                $resultguiac = setq($queryguiac);
                //if($_SESSION['uid'] == "ADMIN")echo $queryguiac.'<br>';
                while ($rowguiac = $resultguiac->fetch_array()) {
                  //echo 'Entro aqui'.'<br>';

                  $nmbguia = $rowguiac['ga_nmb'];
                  $guiaestatus = $rowguiac['ga_estatus'];
                  if ($estatusembarque == "F") {
                    $checkb = '';
                    $check = ' checked disabled';
                    $back = ' style="background-color: rgb(204, 255, 204);"';
                  } else {
                    if ($guiaestatus == "F") {
                      $checkb = '';
                      $check = ' checked disabled';
                      $back = ' style="background-color: rgb(204, 255, 204);"';
                    } else {
                      $checkb = ' disabled';
                      $check = '';
                      $back = '';
                    }
                  }
                  $boton = '';
                  /* $remdocs = busca($remision, 'remisiones_adjuntos', 'ra_id', 'COUNT(*)');
                  if($remdocs > 0){
                      $boton .= '
                      <a data-fancybox data-type="ajax" data-src="popup/adjuntardocremision?id='.$remision.'" href="javascript:;">
                        <button  type="button" class="btn btn-sm text-white" style="background: brown" data-toggle="tooltip" data-placement="top" title="Ver documentos adjuntos"><i class="fa fa-paperclip" style="color: #fff"></i>Archivos adjuntados</button>
                      </a>';  
                  } */
                  
                  $boton .= '
                      <button ' . $checkb . ' id="evid' . $rowguiac['rc_guia'] . '" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=' . $rowguiac['rc_guia'] . '&tipo=E&embarque=' . $_GET['id'] . '" href="javascript:;" >
                        <i class="fa fa-plus"></i> Evidencia
                      </button>';


                  /* $boton .= '
                  <a target="_BLANK" href="formats/pdfcotizacion.php?idcotiza='.$idcotiza.'" >
                  <button type="button" class="btn btn-sm btn-danger" /><i class="fa fa-file-pdf"></i></button>
                  </a>'; */
                  $contador = 0;

                  $articulos = '
                    <tr style="background: #008eff47 !important;">
                      <td class="sin-hover" colspan="2">&nbsp;&nbsp;</td>
                      <td class="sin-hover" colspan="2" style="text-align: end;"> ' . $boton . '</td>
                    </tr>';

                    $query0 = 'SELECT * FROM remisionesc 
                        WHERE rc_remision = "' . $idremision . '" AND rc_guia IN ("' . $rowguiac['rc_guia'] . '") 
                        AND rc_estatus IN ("P", "F")
                        AND (rc_embarque IN ("' . $_GET['id'] . '", "0") OR rc_embarque IS NULL)
                        ORDER BY rc_id ASC;';
                    $result0 = setq($query0);
                    //if($_SESSION['uid'] == "ADMIN") echo $query0.'<br>';
                          $articulos .= '
                    <tr ' . $back . '>
                      <td></td>
                      <td rowspan="6">';
                          $k = 0;
                          $fenvio = '';
                          while ($rowcb = $result0->fetch_array()) {
                            //echo 'Entro aqui'.'<br>';
                            if ($k == 0) {
                              $fenvio = $rowcb['rc_fenvio'];
                            }

                            $nmba = busca($rowcb['rc_articulo'], "articulos", "a_id", "a_nmb");
                            //$var = busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['rc_modelo'] . '" AND av_articulo', 'COUNT(*)');
                            //if ($var > 0) {
                              $nmba .= " " . busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['rc_modelo'] . '" AND av_articulo', 'av_nmb');
                            //}

                            $anmb = $nmba . " " . $rowcb['rc_numero'];

                            if(!empty($rowcb['rc_ligado'])){
                              $artpadre = $rowcb['rc_ligado'];
                              $sqligados = 'SELECT rc_id, rc_tipoenvio, rc_articulo, rc_modelo, rc_numero, rc_estatus, rc_paqueteria, rc_embarque FROM remisionesc WHERE rc_id = "' . $artpadre . '" ORDER BY rc_id';
                              $resultligado = setq($sqligados);
                              $rowligado = $resultligado->fetch_array();
                              /* echo $sqligados;
                              echo '<br>'; */
                                //Inicia listado de artículos ligados de este artículo que no es un paquete
                                $varcontenedora = '';
                                $artnameligado = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
                                //$var2 = busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'COUNT(*)');
                                //if ($var2 > 0) {
                                  $artnameligado .= " " . busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'av_nmb');
                                //}
                                $artnameg = $anmb . " " . " DEL " . $artnameligado." ".$rowligado['rc_numero'];
                                $varcontenedora .= "* " . $artnameg . "<br>";
                                $articulos .= $varcontenedora;
                                $numarticulos++;
                            } else{
                                $articulos .= ' ' . $anmb . '<br>';
                                $numarticulos++;
                            }
                              $k++;
                          }

                          $articulos .= '
                      </td>
                      <td rowspan="6">
                      <center>
                        <input class="form-control" type="text" value="' . $fenvio . '" readonly>
                      </center>
                      </td>
                      <td rowspan="6">
                      <center>
                        <input class="form-check-input consult-check" onclick="selectOne(' . $rowguiac['rc_guia'] . ')" type="checkbox" name="select' . $rowguiac['rc_guia'] . '" id="select' . $rowguiac['rc_guia'] . '" value="' . $rowguiac['rc_guia'] . '" ' . $check . '>
                      </center>
                      </td>
                    </tr>';

                          $articulos .= '
                    <tr ' . $back . '>
                      <td>Número de guía: ' . $nmbguia . '</td>
                    </tr>
                    <tr ' . $back . '>
                      <td>Remisión: ' . $folioremision . '</td>
                    </tr>
                    <tr ' . $back . '>
                      <td>Vendedor: ' . $vendedor . '</td>
                    </tr>
                    <tr ' . $back . '>
                      <td>Cliente: ' . $cliente . '</td>
                    </tr>
                    <tr ' . $back . '>
                      <td>Dirección: ' . $direccion . '</td>
                    </tr>
                    ';


                          $embarqueguia = intval($rowguiac['ga_embarque']);
                          if ($estatusembarque == "F") {
                            if (intval($_GET['id']) == $embarqueguia) {
                              if ($z == 0) {
                                $idguias .= $rowguiac['rc_guia'];
                              } else {
                                $idguias .= "," . $rowguiac['rc_guia'];
                              }
                              echo $articulos;
                              $z++;
                            }
                          } else {
                            if (intval($_GET['id']) == $embarqueguia || $embarqueguia == 0) {
                              if ($z == 0) {
                                $idguias .= $rowguiac['rc_guia'];
                              } else {
                                $idguias .= "," . $rowguiac['rc_guia'];
                              }
                              echo $articulos;
                              $z++;
                            }
                          }

                }
              }

          } else {
            $tipoe = "C";
            //echo 'entro aqui 1.';
            
              $querycotiza = 'SELECT DISTINCT(rc_remision) AS rc_remision FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision
                            WHERE r_estatus IN ("A","F", "FE") AND rc_estatus IN ("P", "F") AND rc_paqueteria = "0" AND rc_tipoenvio = "C" 
                            AND (rc_embarque IN ("'.$_GET['id'].'", "0") OR rc_embarque IS NULL)
                            ORDER BY rc_id ASC;';         
            
            
            $resultcotiza = setq($querycotiza);
            
            while($rowcotiza = $resultcotiza->fetch_array()){
              $remision = $rowcotiza['rc_remision'];
              $rencargado = busca($remision, 'remisiones', 'r_id', 'r_encargado');
              $nmbvendedor = busca($rencargado, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
              $estatusr = busca($remision, 'remisiones', 'r_id', 'r_estatus');
              $client0 = busca($rowcotiza['rc_remision'], 'remisiones', 'r_id', 'r_cliente');

              $client = busca($client0, 'crm_clientes', 'c_id', 'c_nmb');
              $client .= " ".busca($client0, 'crm_clientes', 'c_id', 'c_apellidos');


              /* $client = busca($client0, 'crm_clientes', 'c_id', 'CONCAT(c_nmb, " ", c_apellidos)'); */
              $folremision = busca($remision, 'remisiones', 'r_id', 'r_folio');
              if($estatusr == "F" || $estatusr == "A"){
                $boton = '';
                $remdocs = busca($remision, 'remisiones_adjuntos', 'ra_id', 'COUNT(*)');
                if($remdocs > 0){
                    $boton .= '
                  <div style="text-align: end">
                    <a data-fancybox data-type="ajax" data-src="popup/adjuntardocremision?id='.$remision.'" href="javascript:;">
                      <button  type="button" class="btn btn-sm text-white" style="background: brown" data-toggle="tooltip" data-placement="top" title="Ver documentos adjuntos"><i class="fa fa-paperclip" style="color: #fff"></i>Archivos adjuntados</button>
                    </a>
                  <div>';
                }

              $articulos .= '
              <tr style="background: #fcef66 !important;">
              <td class="sin-hover" colspan="5">Remisión: '.$folremision.'&nbsp;&nbsp;&nbsp;&nbsp;Cliente: '.$client.'&nbsp;&nbsp;&nbsp;&nbsp;Vendedor: '.$nmbvendedor.' - '.$rencargado.' '.$boton.'</td>
              </tr>';


              if($estatusembarque == "F"){
                $queryguiac = 'SELECT * FROM remisionesc 
                              WHERE rc_estatus IN ("P", "F") AND rc_paqueteria = "0" 
                              AND rc_tipoenvio = "C" AND rc_remision = "'.$rowcotiza['rc_remision'].'"
                              AND rc_embarque IN ("'.$_GET['id'].'")
                              ORDER BY rc_id ASC;'; 
                } else{
                  $queryguiac = 'SELECT * FROM remisionesc 
                                WHERE rc_estatus IN ("P", "F") AND rc_paqueteria = "0" 
                                AND rc_tipoenvio = "C" AND rc_remision = "'.$rowcotiza['rc_remision'].'"
                                AND (rc_embarque IN ("'.$_GET['id'].'", "0") OR rc_embarque IS NULL)
                                ORDER BY rc_id ASC;'; 
                }

                $resultguiac = setq($queryguiac);
                $back = ' style="background-color: rgb(204, 255, 204);"';
                $contadorf = 0;
                while($rowguiac = $resultguiac->fetch_array()){

                  if($estatusembarque == "F" || $rowguiac['rc_estatus'] == "F"){
                    $checkb = '';
                    $check = ' checked disabled';
                    $back = ' style="background-color: rgb(204, 255, 204);"';
                  } else{
                    $checkb = ' disabled';
                    $check = '';
                    $back = '';
                  }
                  
                  $boton = '
                  <button '.$checkb.' id="evid'.$rowguiac['rc_id'].'" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?art='.$rowguiac['rc_id'].'&tipo=E&embarque='.$_GET['id'].'" href="javascript:;" >
                  <i class="fa fa-plus"></i> Evidencia
                        </button>';
                  $contador = 0;

                  /*     $articulos .= '
                  <tr style="background: #008eff47 !important;">
                  <td class="sin-hover" colspan="2">&nbsp;&nbsp;</td>
                  <td class="sin-hover"> '.$boton.'</td>
                  </tr>'; */

                  $articulos .= '
                  <tr '.$back.'>
                  <td colspan="">';
                  $nmba = busca($rowguiac['rc_articulo'], "articulos", "a_id", "a_nmb");
                  //$var = busca($rowguiac['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowguiac['rc_modelo'].'" AND av_articulo', 'COUNT(*)');
                  //if($var > 0) {
                    $nmba .= " ".busca($rowguiac['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowguiac['rc_modelo'].'" AND av_articulo', 'av_nmb');
                  //}

                  $anmb = $nmba." ".$rowguiac['rc_numero'];



                  $artpadre = $rowguiac['rc_ligado'];
                  if(!empty($artpadre)){
                  $sqligados = 'SELECT rc_id, rc_tipoenvio, rc_articulo, rc_modelo, rc_numero, rc_estatus, rc_paqueteria, rc_embarque FROM remisionesc WHERE rc_id = "'.$artpadre.'" ORDER BY rc_id';
                  $resultligado = setq($sqligados);
                  /* echo $sqligados;
                  echo '<br>';
                  echo '<br>'; */
                  $artnameg = $anmb;
                  
                    //Inicia listado de artículos ligados de este artículo que no es un paquete
                  $rowligado = $resultligado->fetch_array();
                  $varcontenedora = '';
                  $artnameligado = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
                  //$var = intval(busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'COUNT(*)'));
                  //if ($var > 0) {
                    $artnameligado .= " ".busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'av_nmb');
                  //}
                  $artnameg = $anmb." "." DEL ".$artnameligado." ".$rowligado['rc_numero'];    
                  $varcontenedora .= "* ".$artnameg."<br>";
                  $articulos .= $varcontenedora;
                  $numarticulos++;  
                  } else{
                    $articulos .= ' '.$anmb.'<br>';
                    $numarticulos++;
                  }

                  $articulos .= '
                  </td>
                  <td>
                  <center>
                    <input class="form-check-input consult-check" onclick="selectOne('.$rowguiac['rc_id'].')" type="checkbox" name="select'.$rowguiac['rc_id'].'" id="select'.$rowguiac['rc_id'].'" value="'.$rowguiac['rc_id'].'" '.$check.'>
                  </center>
                  </td>
                  <td>
                    <input type="text" class="form-control" value="'.$rowguiac['rc_fenvio'].'" readonly>
                  </td>
                  <!-- <td>
                    '.$boton.'
                  </td> -->
                  </tr>';

                  $embarqueguia = intval(busca($rowguiac['rc_id'], 'remisionesc', 'rc_id', 'rc_embarque'));      
                  if($estatusembarque == "F"){
                  if(intval($_GET['id']) == $embarqueguia){
                    if($z == 0){
                      $idguias .= $rowguiac['rc_id'];  
                    } else{
                      $idguias .= ",".$rowguiac['rc_id'];
                    }
                    /* echo $articulos; */
                    $z++;
                  }
                  } else{
                  if(intval($_GET['id']) == $embarqueguia || $embarqueguia == 0){
                    if($z == 0){
                      $idguias .= $rowguiac['rc_id'];  
                    } else{
                      $idguias .= ",".$rowguiac['rc_id'];        
                    }
                    /* echo $articulos; */
                    $z++;
                  }        
                  }
                  $contadorf++;
                } 

              $articulos .= '
                <tr style="background: #fcef66 !important;">
                <td class="sin-hover" colspan="4">&nbsp;&nbsp;</td>
                </tr>';
              }
            }
            echo $articulos;
            if(empty($articulos) && empty($articulosg)){
              echo '
              <tr>
                <td colspan="4"><center>No hay artículos para mostrar</center></td>
              </tr>';
            }
          }
        //}
      //}
    } else {
      if($paqt == -1) {
        
        $querycotiza = 'SELECT DISTINCT(rc_remision) AS rc_remision FROM remisionesc 
                        WHERE rc_estatus IN ("P", "F") AND rc_paqueteria = "0" AND rc_tipoenvio = "C" 
                        AND rc_embarque IN ("'.$_GET['id'].'")
                        ORDER BY rc_id ASC;'; 
        

        $resultcotiza = setq($querycotiza);
        while($rowcotiza = $resultcotiza->fetch_array()){

        $remision = $rowcotiza['rc_remision'];
        $sqlrem = 'SELECT r_encargado, r_estatus, r_cliente, r_folio FROM remisiones WHERE r_id = "'.$remision.'"'; 
        $resultrem = setq($sqlrem);
        list($rencargado, $estatusr, $client0, $folremision) = $resultrem -> fetch_array();
        //$rencargado = busca($remision, 'remisiones', 'r_id', 'r_encargado');
        //$nmbvendedor = busca($rencargado, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
        //$estatusr = busca($remision, 'remisiones', 'r_id', 'r_estatus');
        //$client0 = busca($rowcotiza['rc_remision'], 'remisiones', 'r_id', 'r_cliente');

        $sqlcli = 'SELECT c_nmb, c_apellidos FROM crm_clientes WHERE c_id = "'.$client0.'"';
        $resultcli = setq($sqlcli);
        list($clinmb, $cliape) = $resultcli -> fetch_array();

        $client = $clinmb.' '.$cliape; 
        //$client = busca($client0, 'crm_clientes', 'c_id', 'c_nmb');
        //$client .= " ".busca($client0, 'crm_clientes', 'c_id', 'c_apellidos');


        /* $client = busca($client0, 'crm_clientes', 'c_id', 'CONCAT(c_nmb, " ", c_apellidos)'); */
        //$folremision = busca($remision, 'remisiones', 'r_id', 'r_folio');
        
          $boton = '';
          /* $remdocs = busca($remision, 'remisiones_adjuntos', 'ra_id', 'COUNT(*)');
          if($remdocs > 0){
              $boton .= '
            <div style="text-align: end">
              <a data-fancybox data-type="ajax" data-src="popup/adjuntardocremision?id='.$remision.'" href="javascript:;">
                <button  type="button" class="btn btn-sm text-white" style="background: brown" data-toggle="tooltip" data-placement="top" title="Ver documentos adjuntos"><i class="fa fa-paperclip" style="color: #fff"></i>Archivos adjuntados</button>
              </a>
            <div>';
          } */

        $articulos .= '
        <tr style="background: #fcef66 !important;">
        <td class="sin-hover" colspan="4">Remisión: '.$folremision.'&nbsp;&nbsp;&nbsp;&nbsp;Cliente: '.$client.'&nbsp;&nbsp;&nbsp;&nbsp;Vendedor: '.$nmbvendedor.' - '.$rencargado.' '.$boton.'</td>
        </tr>';

      $queryguiac = 'SELECT * FROM remisionesc 
                    WHERE rc_estatus IN ("P", "F") AND rc_paqueteria = "0" 
                    AND rc_tipoenvio = "C" AND rc_remision = "'.$rowcotiza['rc_remision'].'"
                    AND rc_embarque IN ("'.$_GET['id'].'")
                    ORDER BY rc_id ASC;'; 
      

      $resultguiac = setq($queryguiac);
      $back = ' style="background-color: rgb(204, 255, 204);"';
      $contadorf = 0;
      while($rowguiac = $resultguiac->fetch_array()){

      
        $checkb = '';
        $check = ' checked disabled';
        $back = ' style="background-color: rgb(204, 255, 204);"';
      
      
      $boton = '
      <button '.$checkb.' id="evid'.$rowguiac['rc_id'].'" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?art='.$rowguiac['rc_id'].'&tipo=E&embarque='.$_GET['id'].'" href="javascript:;" >
      <i class="fa fa-plus"></i> Evidencia
            </button>';
      $contador = 0;

      /*     $articulos .= '
      <tr style="background: #008eff47 !important;">
      <td class="sin-hover" colspan="2">&nbsp;&nbsp;</td>
      <td class="sin-hover"> '.$boton.'</td>
      </tr>'; */

      $articulos .= '
      <tr '.$back.'>
      <td>';
      $nmba = busca($rowguiac['rc_articulo'], "articulos", "a_id", "a_nmb");
      /* $var = busca($rowguiac['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowguiac['rc_modelo'].'" AND av_articulo', 'COUNT(*)');
      if($var > 0) { */
        $nmba .= " ".busca($rowguiac['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowguiac['rc_modelo'].'" AND av_articulo', 'av_nmb');
      //}

      $anmb = $nmba." ".$rowguiac['rc_numero'];



      $artpadre = $rowguiac['rc_ligado'];
      if(!empty($artpadre)){
      $sqligados = 'SELECT rc_id, rc_tipoenvio, rc_articulo, rc_modelo, rc_numero, rc_estatus, rc_paqueteria, rc_embarque FROM remisionesc WHERE rc_id = "'.$artpadre.'" ORDER BY rc_id';
      $resultligado = setq($sqligados);
      /* echo $sqligados;
      echo '<br>';
      echo '<br>'; */
      $artnameg = $anmb;
      
        //Inicia listado de artículos ligados de este artículo que no es un paquete
      $rowligado = $resultligado->fetch_array();
      $varcontenedora = '';
      $artnameligado = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
      /* $var = intval(busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'COUNT(*)'));
      if ($var > 0) { */
        $artnameligado .= " ".busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'av_nmb');
      //}
      $artnameg = $anmb." "." DEL ".$artnameligado." ".$rowligado['rc_numero'];    
      $varcontenedora .= "* ".$artnameg."<br>";
      $articulos .= $varcontenedora;
      $numarticulos++;  
      } else{
        $articulos .= ' '.$anmb.'<br>';
        $numarticulos++;
      }

      $articulos .= '
      </td>
      <td>
      <center>
        <input class="form-check-input consult-check" onclick="selectOne('.$rowguiac['rc_id'].')" type="checkbox" name="select'.$rowguiac['rc_id'].'" id="select'.$rowguiac['rc_id'].'" value="'.$rowguiac['rc_id'].'" '.$check.'>
      </center>
      </td>
      <td>
        <input type="text" class="form-control" value="'.$rowguiac['rc_fenvio'].'" readonly>
      </td>
      <!-- <td>
        '.$boton.'
      </td> -->
      </tr>';

      $embarqueguia = intval(busca($rowguiac['rc_id'], 'remisionesc', 'rc_id', 'rc_embarque'));      
      if($estatusembarque == "F"){
      if(intval($_GET['id']) == $embarqueguia){
        if($z == 0){
          $idguias .= $rowguiac['rc_id'];  
        } else{
          $idguias .= ",".$rowguiac['rc_id'];
        }
        /* echo $articulos; */
        $z++;
      }
      } else{
      if(intval($_GET['id']) == $embarqueguia || $embarqueguia == 0){
        if($z == 0){
          $idguias .= $rowguiac['rc_id'];  
        } else{
          $idguias .= ",".$rowguiac['rc_id'];        
        }
        /* echo $articulos; */
        $z++;
      }        
      }
      $contadorf++;
      } 

      $articulos .= '
        <tr style="background: #fcef66 !important;">
        <td class="sin-hover" colspan="4">&nbsp;&nbsp;</td>
        </tr>';
      
      }
      echo $articulos;
      if(empty($articulos) && empty($articulosg)){
        echo '
        <tr>
          <td colspan="4"><center>No hay artículos para mostrar</center></td>
        </tr>';
      }
      } else {

      $querycotiza = 'SELECT DISTINCT(rc_remision) AS rc_remision, rc_guia FROM remisionesc 
                      WHERE rc_estatus IN ("P", "F") AND rc_paqueteria != "0" AND rc_tipoenvio != "C" 
                      AND rc_embarque IN ("'.$_GET['id'].'")
                      ORDER BY rc_id ASC;'; 
      
      
      $resultcotiza = setq($querycotiza);
      while($rowcotiza = $resultcotiza->fetch_array()){

      $remision = $rowcotiza['rc_remision'];
      $rencargado = busca($remision, 'remisiones', 'r_id', 'r_encargado');
      $nmbvendedor = busca($rencargado, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
      $estatusr = busca($remision, 'remisiones', 'r_id', 'r_estatus');
      $client0 = busca($rowcotiza['rc_remision'], 'remisiones', 'r_id', 'r_cliente');
      $guia = busca($rowcotiza['rc_guia'], 'guias_articulos', 'ga_id', 'ga_nmb');

      $client = busca($client0, 'crm_clientes', 'c_id', 'c_nmb');
      $client .= " ".busca($client0, 'crm_clientes', 'c_id', 'c_apellidos');


      /* $client = busca($client0, 'crm_clientes', 'c_id', 'CONCAT(c_nmb, " ", c_apellidos)'); */
      $folremision = busca($remision, 'remisiones', 'r_id', 'r_folio');
      
        $boton = '';
        $remdocs = busca($remision, 'remisiones_adjuntos', 'ra_id', 'COUNT(*)');
        if($remdocs > 0){
            $boton .= '
          <div style="text-align: end">
            <a data-fancybox data-type="ajax" data-src="popup/adjuntardocremision?id='.$remision.'" href="javascript:;">
              <button  type="button" class="btn btn-sm text-white" style="background: brown" data-toggle="tooltip" data-placement="top" title="Ver documentos adjuntos"><i class="fa fa-paperclip" style="color: #fff"></i>Archivos adjuntados</button>
            </a>
          <div>';
        }

      $articulos .= '
      <tr style="background: #fcef66 !important;">
      <td class="sin-hover" colspan="4">Remisión: '.$folremision.'&nbsp;&nbsp;&nbsp;&nbsp;Cliente: '.$client.'&nbsp;&nbsp;&nbsp;&nbsp;Vendedor: '.$nmbvendedor.' - '.$rencargado.' '.$boton.'&nbsp;&nbsp;&nbsp;&nbsp;Guía: '.$guia.'</td>
      </tr>';


    if($estatusembarque == "F"){
      $queryguiac = 'SELECT * FROM remisionesc 
                    WHERE rc_estatus IN ("P", "F") AND rc_paqueteria != "0" 
                    AND rc_tipoenvio != "C" AND rc_remision = "'.$rowcotiza['rc_remision'].'"
                    AND rc_embarque IN ("'.$_GET['id'].'")
                    ORDER BY rc_id ASC;'; 
      } else{
        $queryguiac = 'SELECT * FROM remisionesc 
                      WHERE rc_estatus IN ("P", "F") AND rc_paqueteria != "0" 
                      AND rc_tipoenvio != "C" AND rc_remision = "'.$rowcotiza['rc_remision'].'"
                      AND (rc_embarque IN ("'.$_GET['id'].'", "0") OR rc_embarque IS NULL)
                      ORDER BY rc_id ASC;'; 
      }

      $resultguiac = setq($queryguiac);
      $back = ' style="background-color: rgb(204, 255, 204);"';
      $contadorf = 0;
      while($rowguiac = $resultguiac->fetch_array()){

      if($estatusembarque == "F" || $rowguiac['rc_estatus'] == "F"){
        $checkb = '';
        $check = ' checked disabled';
        $back = ' style="background-color: rgb(204, 255, 204);"';
      } else{
        $checkb = ' disabled';
        $check = '';
        $back = '';
      }
      
      $boton = '
      <button '.$checkb.' id="evid'.$rowguiac['rc_id'].'" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?art='.$rowguiac['rc_id'].'&tipo=E&embarque='.$_GET['id'].'" href="javascript:;" >
      <i class="fa fa-plus"></i> Evidencia
            </button>';
      $contador = 0;

      /*     $articulos .= '
      <tr style="background: #008eff47 !important;">
      <td class="sin-hover" colspan="2">&nbsp;&nbsp;</td>
      <td class="sin-hover"> '.$boton.'</td>
      </tr>'; */

      $articulos .= '
      <tr '.$back.'>
      <td colspan="2">';
      $nmba = busca($rowguiac['rc_articulo'], "articulos", "a_id", "a_nmb");
      //$var = busca($rowguiac['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowguiac['rc_modelo'].'" AND av_articulo', 'COUNT(*)');
      //if($var > 0) {
        $nmba .= " ".busca($rowguiac['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowguiac['rc_modelo'].'" AND av_articulo', 'av_nmb');
      //}

      $anmb = $nmba." ".$rowguiac['rc_numero'];



      $artpadre = $rowguiac['rc_ligado'];
      if(!empty($artpadre)){
      $sqligados = 'SELECT rc_id, rc_tipoenvio, rc_articulo, rc_modelo, rc_numero, rc_estatus, rc_paqueteria, rc_embarque FROM remisionesc WHERE rc_id = "'.$artpadre.'" ORDER BY rc_id';
      $resultligado = setq($sqligados);
      /* echo $sqligados;
      echo '<br>';
      echo '<br>'; */
      $artnameg = $anmb;
      
        //Inicia listado de artículos ligados de este artículo que no es un paquete
      $rowligado = $resultligado->fetch_array();
      $varcontenedora = '';
      $artnameligado = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
      //$var = intval(busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'COUNT(*)'));
      //if ($var > 0) {
        $artnameligado .= " ".busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'av_nmb');
      //}
      $artnameg = $anmb." "." DEL ".$artnameligado." ".$rowligado['rc_numero'];    
      $varcontenedora .= "* ".$artnameg."<br>";
      $articulos .= $varcontenedora;
      $numarticulos++;  
      } else{
        $articulos .= ' '.$anmb.'<br>';
        $numarticulos++;
      }

      $articulos .= '
      </td>
      <td>
      <center>
        <input class="form-check-input consult-check" onclick="selectOne('.$rowguiac['rc_id'].')" type="checkbox" name="select'.$rowguiac['rc_id'].'" id="select'.$rowguiac['rc_id'].'" value="'.$rowguiac['rc_id'].'" '.$check.'>
      </center>
      </td>
      <td>
        <input type="text" class="form-control" value="'.$rowguiac['rc_fenvio'].'" readonly>
      </td>
      <!-- <td>
        '.$boton.'
      </td> -->
      </tr>';

      $embarqueguia = intval(busca($rowguiac['rc_id'], 'remisionesc', 'rc_id', 'rc_embarque'));      
      if($estatusembarque == "F"){
      if(intval($_GET['id']) == $embarqueguia){
        if($z == 0){
          $idguias .= $rowguiac['rc_id'];  
        } else{
          $idguias .= ",".$rowguiac['rc_id'];
        }
        /* echo $articulos; */
        $z++;
      }
      } else{
      if(intval($_GET['id']) == $embarqueguia || $embarqueguia == 0){
        if($z == 0){
          $idguias .= $rowguiac['rc_id'];  
        } else{
          $idguias .= ",".$rowguiac['rc_id'];        
        }
        /* echo $articulos; */
        $z++;
      }        
      }
      $contadorf++;
      } 

      $articulos .= '
        <tr style="background: #fcef66 !important;">
        <td class="sin-hover" colspan="4">&nbsp;&nbsp;</td>
        </tr>';
      
      }
      echo $articulos;
      if(empty($articulos) && empty($articulosg)){
        echo '
        <tr>
          <td colspan="4"><center>No hay artículos para mostrar</center></td>
        </tr>';
      }
      }
    }

    
    echo '
    <script>
    
      var totalreg = document.getElementById("totalreg");
      totalreg.textContent = "Número de guías: ' . (intval($z)+intval($z1)) . '";
    
    </script>
    ';

    echo '</tbody>
    </table>
    <input type="hidden" name="idgarantias" value="' . $idgarantias . '">
    <input type="hidden" name="idguias" value="' . $idguias . '">
    <input type="hidden" name="embarque" value="' . $_GET['id'] . '">
    <input type="hidden" name="tipoe" value="' . $tipoe . '">
    <input type="hidden" value="' . $numarticulos . '" name="numarticulos">
    </form>
    </div>
  </center>
  </div>';
    //FIN DE LA TABLA DE PRODUCTOS DE TODAS LAS REMISIONES
    ?>
        <script>

        function selectGarantia(k) {
            // Obtener el checkbox por su ID
            var checkbox = document.getElementById("garantia" + k);
            var boton = document.getElementById("evidga" + k);

            // Verificar si el checkbox está marcado
            if (checkbox.checked) {
              boton.disabled = false;
          } else {
            // El checkbox no está marcado
            boton.disabled = true;
          }
        }

          function selectOne(k) {
            // Obtener el checkbox por su ID
            var checkbox = document.getElementById("select" + k);
            var boton = document.getElementById("evid" + k);

            // Verificar si el checkbox está marcado
            if (checkbox.checked) {
              boton.disabled = false;
            // El checkbox está marcado
    /*         $.ajax({
            url: 'query/consultasalida.php',
            method: 'POST', // Puedes usar 'GET' o 'POST' según tus necesidades
            data: {
              embarque: "<?php /* echo $_GET['id']; */?>",
              id: k
            }, // Convierte el objeto de datos en una cadena JSON
            success: function(response) {
              if (response == 1) {
                Swal.fire({
                  icon: 'error',
                  title: 'Atención',
                  text: 'Es necesario que el gerente confirme la salida de estos productos'
                }).then((result) => {
                  if (result.isConfirmed) {
                    boton.disabled = false;
                  }
                  Swal.close();
                });
              } else {
                boton.disabled = false;
              }
            },
            error: function(xhr, status, error) {
              // Maneja errores de la solicitud aquí
              console.error('Error:', error);
            }
          }); */
          } else {
            // El checkbox no está marcado
            boton.disabled = true;
          }
        }

          function selectOneTwo(k, j = '') {
            var checkbox = document.getElementById("select" + k);
            var input = document.getElementById("in" + k);
            /* var btn = document.getElementById("btn"+k); */

            /////////////////////////////////////////////
            if (j != '') {
              var input2 = document.getElementById("in" + k + "2");
              /* var btn2 = document.getElementById("btn"+k+"2"); */
            }
            if (checkbox.checked) {
              input.removeAttribute("readonly");
              input.setAttribute("required", true);
              input.focus();
              /* btn.removeAttribute("disabled"); */
              if (j != '') {
                input2.removeAttribute("readonly");
                /* btn2.removeAttribute("disabled"); */
              }

            } else {
              input.setAttribute("readonly", true);
              input.removeAttribute("required");
              /* btn.setAttribute("disabled", true); */
              if (j != '') {
                input2.setAttribute("readonly", true);
                /* btn2.setAttribute("disabled", true); */
              }
            }
            /////////////////////////////////////////////    
          }

          function checkpaqueteria(k) {
            var checkbox = document.getElementById("select" + k);
            if (checkbox.checked) {
              document.getElementById("tr" + k).style.backgroundColor = '#ccffcc';
            } else {
              document.getElementById("tr" + k).style.backgroundColor = '#ffffff';
            }
          }

          function entregacliente(k) {
            // Obtenemos el botón por su ID
            var button = document.getElementById("btn" + k);
            if (button.classList.contains("btn-success")) {
              // Remueve una clase del botón
              button.classList.remove("btn-success");
              // Agrega una clase al botón
              button.classList.add("btn-primary");
              // Cambia el color de fondo
              document.getElementById("tr" + k).style.backgroundColor = '#ffffff';
              // Cambia el contenido del botón al icono
              button.innerHTML = '<i class="fas fa-check-circle"></i>&nbsp;Entregar a cliente';
            } else {
              // Remueve una clase del botón
              button.classList.remove("btn-primary");
              // Agrega una clase al botón
              button.classList.add("btn-success");
              // Cambia el color de fondo
              document.getElementById("tr" + k).style.backgroundColor = '#ccffcc';
              // Cambia el contenido del botón al icono
              button.innerHTML = '<i class="fas fa-check-circle"></i>';
            }

          }

          <?php
          echo "
  function clickGuardar(){
    var guardar = document.getElementById('guardar');
    ";
    if ($paqt != -1) {
    echo "
        var valores = obtenerChecked();
        var valoresga = obtenerCheckedga();
        var data = {
          checkboxValues: valores,
          checkboxValuesga: valoresga";
          if ($paqt == -1) {
            echo ', cccid: "1"';
          }
          echo "};
        $.ajax({
        url: 'query/finalizarcargaembarque.php',
        method: 'POST', // Puedes usar 'GET' o 'POST' según tus necesidades
        contentType: 'application/json', // Especifica el tipo de contenido JSON
        data: JSON.stringify(data), // Convierte el objeto de datos en una cadena JSON
        success: function(response) {
          if(response == 2){
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: 'Una o más guías faltan de subir evidencia.'
            }).then((result) => {
                if (result.isConfirmed || result.isDenied) {
                    Swal.close();
                }
            });
          } else{
            guardar.click();
          }
        },
        error: function(xhr, status, error) {
          // Maneja errores de la solicitud aquí
          console.error('Error:', error);
        }
      });";
    } else{
      echo "guardar.click();";
    }
      echo "
  }
  ";
  echo "
  function finalizarembarque(embarque){
    var formulario = document.getElementById('finalizaForm');
    Swal.fire({
      title: 'Atención',
      text: '¿Estás seguro de finalizar la carga de artículos para este embarque?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Estoy seguro'
    }).then((result) => {
      if (result.isConfirmed) {";

        if ($paqt != -1) {
        echo "
        var valores = obtenerChecked();
        var valoresga = obtenerCheckedga();
        var data = {
          checkboxValues: valores,
          checkboxValuesga: valoresga
        }
        $.ajax({
        url: 'query/finalizarcargaembarque.php',
        method: 'POST', // Puedes usar 'GET' o 'POST' según tus necesidades
        contentType: 'application/json', // Especifica el tipo de contenido JSON
        data: JSON.stringify(data), // Convierte el objeto de datos en una cadena JSON
        success: function(response) {
          if(response == 2){
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: 'Una o más guías faltan de subir evidencia.'
            }).then((result) => {
                if (result.isConfirmed || result.isDenied) {
                    Swal.close();
                }
            });
          } else{
            formulario.action = '?modulo=embarcamiento&accion=finalizarembarque&id='+embarque;
            formulario.submit();
            /* console.log('Se envía el formulario'); */
          }
        },
        error: function(xhr, status, error) {
          // Maneja errores de la solicitud aquí
          console.error('Error:', error);
        }
      });";
    } else{
      echo "
      formulario.action = '?modulo=embarcamiento&accion=finalizarembarque&id='+embarque;
      formulario.submit();";
    }

      echo "}
    })
  }";
          ?>
          function obtenerChecked() {
            // Obtener todos los elementos con la clase "consult-check"
            var checkboxes = document.querySelectorAll('.consult-check');

            // Crear un array para almacenar los valores de los checkboxes
            var valores = [];

            // Recorrer los checkboxes y obtener sus valores
            for (var i = 0; i < checkboxes.length; i++) {
              if (checkboxes[i].checked) { // Verificar si el checkbox está marcado
                valores.push(checkboxes[i].value); // Agregar el valor del checkbox al array
              }
            }

            // Crear un objeto de datos para enviar por AJAX
            return valores;
          }

          function obtenerCheckedga() {
            // Obtener todos los elementos con la clase "consult-check"
            var checkboxes = document.querySelectorAll('.consult-check-ga');

            // Crear un array para almacenar los valores de los checkboxes
            var valores = [];

            // Recorrer los checkboxes y obtener sus valores
            for (var i = 0; i < checkboxes.length; i++) {
              if (checkboxes[i].checked) { // Verificar si el checkbox está marcado
                valores.push(checkboxes[i].value); // Agregar el valor del checkbox al array
              }
            }

            // Crear un objeto de datos para enviar por AJAX
            return valores;
          }
        </script>

        <?php

        //TERMINA EL LISTADO DE PRODUCTOS
        echo '
      <div class="btn-group" data-toggle="buttons">';
        echo '</div>
    </div>';
        echo '
      <div class="row">
      <!-- 
      <div class="col-12 col-md-3">
        <div class="mb-5">
          <label for"adjunto">Archivo</label>
          <input type="file" name="adjunto" id="adjunto" class="form-control"/>
        </div>
      </div>  
      <div class="col-12 col-md-9">
        <div class="mb-5">
          <label for"comentario">Comentario</label>
          <textarea name="comentario" maxlength="100" id="comentario" class="form-control" placeholder="Agrega un comentario" onfocus="this.select();"></textarea>
        </div>
      </div>  
      -->

      <div class="col-12 col-md-12">
        <center><button type="submit" class="btn btn-primary" id="guardar" hidden><i class="fas fa-save"></i>Guardar</button></center>
      </div>
    </div>
  </form>
</div>';




  }
}
?>