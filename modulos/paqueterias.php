<?php
//ini_set('display_errors', 1);
ini_set('memory_limit',"2048M");
class paqueterias{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelpaqueterias(isset($obj));
  }
  function index(){
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";
    if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

    $this->model->result(trim($_REQUEST['nmb']),$_REQUEST['estatus'], $_REQUEST['page']);
    $this->view = new viewpaqueterias($this->model);
    $this->view->browse(trim($_REQUEST['nmb']),$_REQUEST['estatus'], $_REQUEST['page']);
  }
  function insert(){
    $ocurre = isset($_POST['ocurre']) ? 1 : 0;
    $adomicilio = isset($_POST['adomicilio']) ? 1 : 0;
    $estatus = "A";
    $this->model->setdata($_REQUEST['id'],$_POST['nmb'],$_POST['urlrastreo'],$adomicilio,$ocurre,$estatus);
    $this->model->insert();

    redirect('?modulo=paqueterias&accion=index');
  }
  function update(){
    $ocurre = isset($_POST['ocurre']) ? 1 : 0;
    $adomicilio = isset($_POST['adomicilio']) ? 1 : 0;
    if(isset($_POST['estatus'])) $estatus = "A"; else $estatus = "I";
    $this->model->setdata($_REQUEST['id'],$_POST['nmb'],$_POST['urlrastreo'],$adomicilio,$ocurre,$estatus);
    $this->model->update();

    redirect('?modulo=paqueterias&accion=index');
  }
  function show(){
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;

    $this->model->select($_GET['id']);
    $this->view = new viewpaqueterias($this->model);
    $this->view->show($_REQUEST['nmb'],$_REQUEST['estatus']);
  }
  function setlogo(){
    $this->model->setlogo($_REQUEST['id']);

    redirect('?modulo=paqueterias&accion=index');
  }
  function dellogo(){
    $this->model->select($_GET['id']);
    $this->model->dellogo($_REQUEST['id']);

    redirect('?modulo=paqueterias&accion=index');
  }

  function tarifas(){
    if(!isset($_REQUEST['id'])) $_REQUEST['id'] = NULL;

    $this->model->resultarifas($_REQUEST['id']);
    $this->view = new viewpaqueterias($this->model);
    $this->view->tarifas($_REQUEST['id']);
  }
  function inserttarifa(){
    $this->model->inserttarifa($_POST['paqueteria'],$_POST['nmb'],$_POST['kmmin'],$_POST['kmmax'],$_POST['pesomin'],$_POST['pesomax'],$_POST['precio']);

    redirect('?modulo=paqueterias&accion=tarifas&id='.$_POST['paqueteria']);
  }

  function updatetarifa(){
    $this->model->updatetarifa($_GET['idtarifa'],$_POST['paqueteria'],$_POST['nmb'],$_POST['kmmin'],$_POST['kmmax'],$_POST['pesomin'],$_POST['pesomax'],$_POST['precio']);

    redirect('?modulo=paqueterias&accion=tarifas&id='.$_POST['paqueteria']);

  }

  function layoucobertura(){
    $this->view = new viewpaqueterias($this->model);
    $this->view->layoucobertura($_GET['id']);
  }

  function setcoberturas(){
    $this->model->setcoberturas($_GET['paqueteria']);

    redirect('?modulo=paqueterias&accion=layoucobertura&id='.$_GET['paqueteria']);
  }

  function setsucursales(){
    $this->model->setsucursales($_GET['paqueteria']);

    redirect('?modulo=paqueterias&accion=layoucobertura&id='.$_GET['paqueteria']);
  }
  function updatesucursal(){
    $paqueteria = $_GET['paqueteria']; 
    $sucursal = $_POST['sucursal'];

    $estatus = isset($_POST['estatus']) ? 1 : 0;

    $this->model->updatesucursal($sucursal, strtoupper($_POST['nmb']), strtoupper($_POST['direccion']), $estatus, strtoupper($_POST['plaza']), date('Y-m-d H:i:s'), $_SESSION['uid']);

    redirect('?modulo=paqueterias&accion=layoucobertura&id='.$paqueteria);

  }
}

class modelpaqueterias{
  function select($id){
    $sql = 'SELECT * FROM paqueterias WHERE p_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->idp = $row['p_id'];
    $this->nmb = $row['p_nmb'];
    $this->urlrastreo = $row['p_urlrastreo'];
    $this->logo = $row['p_logo'];
    $this->ext = $row['p_ext'];
    $this->adomicilio = $row['p_adomicilio'];
    $this->ocurre = $row['p_ocurre'];
    $this->estatus = $row['p_estatus'];
  }
  function result($nmb,$estatus,$page){ //filtro
    $bloque = 50;
    $sql = 'SELECT * FROM paqueterias WHERE 1';

    if($estatus ) $sql.=' AND p_estatus = "'.$estatus.'" ';
    if($nmb) $sql.= ' AND p_nmb LIKE "%'.$nmb.'%" ';
    $sql.=' ORDER BY p_estatus,p_id DESC LIMIT '.($bloque*$page).','.$bloque;
    $this->result = setq($sql);
    $this->resultt = setq($sql);
  }
  function setdata($id,$nmb,$urlrastreo,$adomicilio,$ocurre,$estatus){
    mb_internal_encoding("UTF-8");
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->urlrastreo = clearvminus($urlrastreo);
    $this->adomicilio = $adomicilio;
    $this->ocurre = $ocurre;
    $this->estatus = clearvmayus($estatus);
  }
  function insert(){
    $sql = 'INSERT INTO paqueterias SET
            p_nmb = "'.$this->nmb.'",
            p_urlrastreo = "'.$this->urlrastreo.'",
            p_adomicilio = '.$this->adomicilio.',
            p_ocurre = '.$this->ocurre.',
            p_estatus = "'.$this->estatus.'"';
    setq($sql);

    $this->idp = getmax('p_id','paqueterias',false,false);
  }
  function update(){
    $sql = 'UPDATE paqueterias SET
            p_nmb = "'.$this->nmb.'",
            p_urlrastreo = "'.$this->urlrastreo.'",
            p_adomicilio = '.$this->adomicilio.',
            p_ocurre = '.$this->ocurre.',
            p_estatus = "'.$this->estatus.'"
            WHERE p_id = "'.$this->id.'"';
    setq($sql);
  }
  function setlogo($id){

    $dirimg = 'img/paqueterias/';
    if (!is_dir($dirimg)) {
      @mkdir($dirimg, 0777);
    }
    $directorio = $dirimg;

    $dir_subida = $directorio;
    $info = new SplFileInfo(basename($_FILES['logo']['name']));
    $viewext = array("jpg","png",'gif','jpeg','webp','WEBP');
    $ext = $info->getExtension();
    $paqueteria = $info->getBasename('.'.$ext);
    if(in_array($ext,$viewext)){
    	$dir=opendir($dir_subida); //Abrimos el directorio de destino
    	$target_path = $dir_subida.'/'.$paqueteria.'.'.$ext; //Indicamos la ruta de destino, así como el nombre del archivo

    	//Movemos y validamos que el archivo se haya cargado correctamente
    	//El primer campo es el origen y el segundo el destino
      if(move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)){
        $sqln = 'UPDATE paqueterias SET p_logo = "'.$paqueteria.'", p_ext = "'.$ext.'"
                 WHERE p_id = "'.$id.'"';
        setq($sqln);
  	    closedir($dir); //Cerramos el directorio de destino
      }
      else{
        echo alert_back("La imagen seleccionada no tiene un formato permitido, por favor intenta con otra imagen",true);
        die();
      }
    }
  }

  function dellogo($id){
    if(file_exists('img/paqueterias/'.$this->logo.'.'.$this->ext)){
      $sqld = 'UPDATE paqueterias SET p_logo = NULL, p_ext = NULL
               WHERE p_id = "'.$id.'"';
      setq($sqld);

      unlink('img/paqueterias/'.$this->logo.'.'.$this->ext);
    }
  }

  function resultarifas($id){
    $sql = 'SELECT * FROM paqueterias_tarifas WHERE pt_paqueteria = "'.$id.'" ORDER BY pt_kmmin';
    $this->resultt = setq($sql);
  }
  function selecttarifa($idtarifa){
    $sql = 'SELECT * FROM paqueterias_tarifas WHERE pt_id = "'.$idtarifa.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['pt_id'];
    $this->nmb = $row['pt_nmb'];
    $this->paqueteria = $row['pt_paqueteria'];
    $this->pesomin = $row['pt_pesomin'];
    $this->pesomax = $row['pt_pesomax'];
    $this->kmmin = $row['pt_kmmin'];
    $this->kmmax = $row['pt_kmmax'];
    $this->precio = $row['pt_precio'];
    $this->uactualiza = $row['pt_uactualiza'];
    $this->factualiza = $row['pt_factualiza'];
  }
  function inserttarifa($paqueteria,$nmb,$kmmin,$kmmax,$kgmin,$kgmax,$precio){
    $sql = 'INSERT INTO paqueterias_tarifas SET
            pt_nmb = "'.clearvmayus($nmb).'",
            pt_paqueteria = "'.$paqueteria.'",
            pt_pesomin = "'.$kgmin.'",
            pt_pesomax = "'.$kgmax.'",
            pt_kmmin = "'.$kmmin.'",
            pt_kmmax = "'.$kmmax.'",
            pt_precio = "'.$precio.'",
            pt_uactualiza = "'.$_SESSION['uid'].'",
            pt_factualiza= "'.date('Y-m-d H:i:s').'"';
    setq($sql);
  }
  function updatetarifa($id,$paqueteria,$nmb,$kmmin,$kmmax,$kgmin,$kgmax,$precio){
    $sql = 'UPDATE paqueterias_tarifas SET
            pt_nmb = "'.clearvmayus($nmb).'",
            pt_paqueteria = "'.$paqueteria.'",
            pt_pesomin = "'.$kgmin.'",
            pt_pesomax = "'.$kgmax.'",
            pt_kmmin = "'.$kmmin.'",
            pt_kmmax = "'.$kmmax.'",
            pt_precio = "'.$precio.'",
            pt_uactualiza = "'.$_SESSION['uid'].'",
            pt_factualiza= "'.date('Y-m-d H:i:s').'"
            WHERE pt_id = "'.$id.'"';
    setq($sql);
  }

  function setcoberturas($paqueteria){
    if (!is_dir('adjuntos')) {
      @mkdir('adjuntos', 0777);
    }
    if (!is_dir('adjuntos/')) {
      @mkdir('adjuntos/', 0777);
    }
    if (!is_dir('adjuntos/layout')) {
      @mkdir('adjuntos/layout', 0777);
    }

    $directorio = 'adjuntos/layout';
    if($_FILES["archivo"]["name"]){
  	  $filename = $_FILES["archivo"]["name"]; //Obtenemos el nombre original del archivo
  	  $source = $_FILES["archivo"]["tmp_name"]; //Obtenemos un nombre temporal del archivo
    }
     if(!file_exists($directorio)){
      mkdir($directorio, 0777) or die("No se puede crear el directorio de extracci&oacute;n");
     }

     $dir=opendir($directorio); //Abrimos el directorio de destino
     $archivo = $directorio.'/tarifario-'.$paqueteria.'.xlsx'; //Indicamos la ruta de destino, así como el nombre del archivo
     if(move_uploaded_file($source, $archivo)){
      require_once 'lib/excel/Classes/PHPExcel.php';
      $inputFileType = PHPExcel_IOFactory::identify($archivo);
      $objReader = PHPExcel_IOFactory::createReader($inputFileType);
      $objPHPExcel = $objReader->load($archivo);
      $sheet = $objPHPExcel->getSheet(0);
      $highestRow = $sheet->getHighestRow();
      $highestColumn = $sheet->getHighestColumn();

      $num=0;
      ?>

      <?php
      for ($row = 2; $row <= $highestRow; $row++){
        /* if($paqueteria == "1"){ */
          if($sheet->getCell("H".$row)->getValue() == "PAQUETEXPRESS") $cobertura = "0";
          elseif($sheet->getCell("H".$row)->getValue() == "ZONA PLUS") $cobertura = "1";
          else $cobertura = "2";

          $num++;
          $sql = 'INSERT INTO paqueterias_cobertura SET
                  pc_paqueteria = "'.$paqueteria.'",
                  pc_cp = "'.$sheet->getCell("A".$row)->getValue().'",
                  pc_tipo = "'.$cobertura.'",
                  pc_sucursal = "'.$sheet->getCell("B".$row)->getValue().'",
                  pc_plaza = "'.$sheet->getCell("C".$row)->getValue().'"
                  ON DUPLICATE KEY UPDATE
                  pc_tipo = "'.$cobertura.'",
                  pc_sucursal = "'.$sheet->getCell("B".$row)->getValue().'",
                  pc_plaza = "'.$sheet->getCell("C".$row)->getValue().'"';
          setq($sql);
        /* }
        else die("--"); */
      }
    }
  }

  function setsucursales($paqueteria){
    if (!is_dir('adjuntos')) {
      @mkdir('adjuntos', 0777);
    }
    if (!is_dir('adjuntos/')) {
      @mkdir('adjuntos/', 0777);
    }
    if (!is_dir('adjuntos/layout')) {
      @mkdir('adjuntos/layout', 0777);
    }

    $directorio = 'adjuntos/layout';
    if($_FILES["archivo"]["name"]){
  	  $filename = $_FILES["archivo"]["name"]; //Obtenemos el nombre original del archivo
  	  $source = $_FILES["archivo"]["tmp_name"]; //Obtenemos un nombre temporal del archivo
    }
     if(!file_exists($directorio)){
      mkdir($directorio, 0777) or die("No se puede crear el directorio de extracci&oacute;n");
     }

     $dir=opendir($directorio); //Abrimos el directorio de destino
     $archivo = $directorio.'/sucursales-'.$paqueteria.'.xlsx'; //Indicamos la ruta de destino, así como el nombre del archivo
     if(move_uploaded_file($source, $archivo)){
      require_once 'lib/excel/Classes/PHPExcel.php';
      $inputFileType = PHPExcel_IOFactory::identify($archivo);
      $objReader = PHPExcel_IOFactory::createReader($inputFileType);
      $objPHPExcel = $objReader->load($archivo);
      $sheet = $objPHPExcel->getSheet(0);
      $highestRow = $sheet->getHighestRow();
      $highestColumn = $sheet->getHighestColumn();

      $num=0;
      ?>

      <?php
      for ($row = 2; $row <= $highestRow; $row++){
        
          if($sheet->getCell("K".$row)->getValue() == "Y") $ocurre = "1";
          else $ocurre = "0";

          $num++;
          $sql = 'INSERT INTO paqueterias_sucursales SET
                  ps_paqueteria = "'.$paqueteria.'",
                  ps_sucursal = "'.$sheet->getCell("A".$row)->getValue().'",
                  ps_nmb = "'.$sheet->getCell("B".$row)->getValue().'",
                  ps_plaza = "'.$sheet->getCell("G".$row)->getValue().'",
                  ps_direccion = "'.$sheet->getCell("C".$row)->getValue().'",
                  ps_cp = "'.$sheet->getCell("D".$row)->getValue().'",
                  ps_ocurre = "'.$ocurre.'",
                  ps_estatus = "'.$ocurre.'",
                  ps_fmodifica = "'.date('Y-m-d').'",
                  ps_umodifica = "'.$_SESSION['uid'].'"
                  ON DUPLICATE KEY UPDATE
                  ps_nmb = "'.$sheet->getCell("B".$row)->getValue().'",
                  ps_direccion = "'.$sheet->getCell("C".$row)->getValue().'",
                  ps_cp = "'.$sheet->getCell("D".$row)->getValue().'",
                  ps_plaza = "'.$sheet->getCell("G".$row)->getValue().'",
                  ps_estatus = "'.$ocurre.'",
                  ps_ocurre = "'.$ocurre.'",
                  ps_fmodifica = "'.date('Y-m-d').'",
                  ps_umodifica = "'.$_SESSION['uid'].'"';
          setq($sql);
      }
    }
  }

  function updatesucursal($sucursal, $nmb, $direccion, $estatus, $plaza, $fecha, $usuario){
    $sql = 'UPDATE paqueterias_sucursales SET ps_nmb ="'.$nmb.'",
                                              ps_direccion ="'.$direccion.'",
                                              ps_estatus ="'.$estatus.'",
                                              ps_ocurre ="'.$estatus.'",
                                              ps_plaza ="'.$plaza.'",
                                              ps_fmodifica = "'.$fecha.'",
                                              ps_umodifica = "'.$usuario.'"
        WHERE ps_id = "'.$sucursal.'"';
    setq($sql);
  } 
}

  class viewpaqueterias{
    var $model;
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
        $this->model = $model;
    }

    function browse($nmb,$estatus,$page){
      $sela = "";
      $selt = "";
      $seli = "";
      if($estatus == "A") $sela = "selected";
      elseif($estatus == "T") $selt = 'selected';
      else $seli = "";

        $nuevo = '<a data-fancybox data-type="ajax" data-src="popup/setpaqueteria.php" href="javascript:;">
                    <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Crear una nueva cotización"><i class="fa fa-plus"></i> Nueva</button>
                  </a>';
        $filtro = '
        <form autocomplete="off" action="?modulo=paqueterias&accion=index" method="post" id="filtro" onsubmit="return checkSubmitenviar();" class="">
          <div class="mb-5">
            <label class="mr-sm-2">Nombre:</label>
            <input type="text" value="'.$nmb.'" onfocus="this.select();" />
          </div>
          <div class="mb-5">
            <label class="mr-sm-2">Estatus:</label>
            <select name="estatus" id="estatus" class="form-control" >
              <option value="A" >Activos</option>
              <option value="I" >Inactivos</option>
              <option value="T" '.$selt.'>Todos</option>
            </select>
          </div>
          <div class="mb-5">
            <label>Acciones:</label><br>
            <button type="button" onclick="mandar(0)" class="btn btn-success"><i class="icon-funnel"></i> Filtrar </button>
            <a href="?modulo=paqueterias&accion=index"><button type="button" class="btn btn-warning">
            <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
            </button></a>
          </div>
        </form>
        ';
        toolbar($_GET['modulo'],'',$filtro,$nuevo);


    echo '
      <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table width="90%" class="mb-0 table table-hover table-striped" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">
                    <tr>
                      <th><b>Nombre</b></th>
                      <th><b>URL</b></th>
                      <th><b>Logotipo</b></th>
                      <th><b>Estatus</b></th>
                      <th width="10%"><b>Acción</b></th>
                    </tr>
                  </thead>';
                  $i = 0;
                  while($row = $this->model->result->fetch_array()){
                    if($row['p_estatus'] == "A"){
                      $colest = "bg-success";
                      $estatus = "Activo";
                    }
                    else{
                      $colest = "bg-danger";
                      $estatus = "Inactivo";
                    }

                    if(!empty($row['p_logo']) && file_exists('img/paqueterias/'.$row['p_logo'].'.'.$row['p_ext'])){
                      $logotipo = '<img src="img/paqueterias/'.$row['p_logo'].'.'.$row['p_ext'].'" class="img-thumbnail" style="width:150px;">
                      <a href="?modulo=paqueterias&accion=dellogo&id='.$row['p_id'].'">
                      <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                      </a>';
                    }
                    else {
                      $logotipo = '<form method="post" enctype="multipart/form-data" action="?modulo=paqueterias&accion=setlogo&id='.$row['p_id'].'">
                                    <input type="file" name="logo" required onchange="submit();" />
                                   </form>';
                    }
                    echo '<tr>
                      <th>'.$row['p_nmb'].'</th>
                      <td>'.$row['p_urlrastreo'].'</td>
                      <td>'.$logotipo.'</td>
                      <td class="'.$colest.'" >'.$estatus.'</td>
                      <td>
                        <a data-fancybox data-type="ajax" data-src="popup/setpaqueteria.php?id='.$row['p_id'].'" href="javascript:;">
                          <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Cambiar información"><i class="fas fa-pencil-alt"></i> Editar</button>
                        </a>
                        <a href="?modulo=paqueterias&accion=layoucobertura&id='.$row['p_id'].'">
                          <button type="button" class="btn btn-sm mb-1 mr-1 text-white" data-toggle="tooltip" data-placement="top" title="Ver las sucursales" style="background: teal;"><i class="fas fa-map-marked-alt" style="color: #fff"></i> Sucursales</button>
                        </a>
                        <!-- <a href="?modulo=paqueterias&accion=tarifas&id='.$row['p_id'].'">
                          <button type="button" class="btn btn-sm btn-dark mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Crear una nueva cotización"><i class="fas fa-search-dollar"></i> Tarifas</button>
                        </a>
                        <a href="?modulo=paqueterias&accion=show&id='.$row['p_id'].'">
                          <button type="button" class="btn btn-sm btn-info mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Ver envios"><i class="fas fa-atlas"></i> Envios</button>
                        </a>  
                        <a href="?modulo=paqueterias&accion=layoucobertura&id='.$row['p_id'].'">
                          <button type="button" class="btn btn-sm btn-dark mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Crear una nueva cotización"><i class="fas fa-search-dollar"></i> Tarifas</button>
                        </a> -->
                      </td>
                    </tr>';
                  }
                echo '</table>
              </center>
            </div>
          </div>
        </div>
      </div>';

      echo '
      <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength: 50,
        });
      </script>

      ';


    }
    function tarifas($id){
      $atras = '<a href="?modulo=paqueterias&accion=index">
                  <button type="button" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Regresar a listado"><i class="fas fa-arrow-left"></i> Atras</button>
                </a>';
      $nuevo = '<a data-fancybox data-type="ajax" data-src="popup/settarifapaqueteria.php?id='.$id.'" href="javascript:;">
                  <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Cambiar informacion"><i class="fas fa-plus"></i> Añadir tarifa</button>
                </a>';

      toolbar("Tarifas ".busca($id,'paqueterias','p_id','p_nmb'),$atras,'',$nuevo);

    echo '
      <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table width="90%" class="mb-0 table table-hover table-striped" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">
                    <tr>
                      <th><b>Nombre tarifa</b></th>
                      <th><b>Rango de KM</b></th>
                      <th><b>Peso Max. KG</b></th>
                      <th><b>Precio</b></th>
                      <th><b>Ultima actualización</b></th>
                      <th width="15%"><b>Acción</b></th>
                    </tr>
                  </thead>';
                  $i = 0;
                  while($row = $this->model->resultt->fetch_array()){

                    echo '<tr>
                      <th>'.$row['pt_nmb'].'</th>
                      <td>'.$row['pt_kmmin'].' - '.$row['pt_kmmax'].'</td>
                      <td>'.$row['pt_pesomin'].' - '.$row['pt_pesomax'].'</td>
                      <td class="number-format">$'.$row['pt_precio'].'</td>
                      <td>'.$row['pt_factualiza'].'<br>'.$row['pt_uactualiza'].'</td>
                      <td>
                        <a data-fancybox data-type="ajax" data-src="popup/settarifapaqueteria.php?id='.$id.'&idtarifa='.$row['pt_id'].'" href="javascript:;">
                          <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Cambiar informacion"><i class="fas fa-exchange-alt"></i> Cambiar tarifa</button>
                        </a>
                      </td>
                    </tr>';
                  }
                echo '</table>
              </center>
            </div>
          </div>
        </div>
      </div>';

      echo '
      <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength: 50, 
        });
      </script>

      ';

    }

function layoucobertura($paqueteria){
  /* foreachdie(); */
  //if(!isset($_POST['ok'])){
      $botones = '';
      $atras = '<a href="?modulo=paqueterias&accion=index">
        <button class="btn btn-sm btn-warning">
          <i class="fas fa-arrow-left"></i> Atras
        </button>
      </a>';
      $botones .= '<button id="cobertura" type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#coberturaxls" >
        <i class="fas fa-file-excel"></i> Actualizar las coberturas 
        </button>';
      $botones .= '<button id="sucursales" type="button" class="btn btn-sm btn-success ms-1" data-bs-toggle="modal" data-bs-target="#sucursalesxls" >
      <i class="fas fa-file-excel"></i> Actualizar sucursales
      </button>';
      toolbar('Sucursales', $atras, '', '', $botones);

      echo '
      <div class="modal fade text-xs-left" id="coberturaxls" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel33">Actualizar la cobertura de las sucursales</h4>
            </div>
            <center>
              <div class="col-11">
                <form action="?modulo=paqueterias&accion=setcoberturas&paqueteria='.$paqueteria.'" method="post" enctype="multipart/form-data" onsubmit="guardando();">
                  <center>
                    <br>
                    <h4>Elegir archivo XLSX para cargar</h4>
                    <input type="file" class="form-control" id="archivo" name="archivo" accept=".xlsx" required ><br>
                    <button type="submit" class="btn btn-success" id="adjuntar" value="" />
                      <i class="fas fa-share"></i> Subir Layout
                    </button>
                  <center>
                  <br>
                </form>
              </div>
            </center>
          </div>
        </div>
      </div>
      ';
      echo '
      <div class="modal fade text-xs-left" id="sucursalesxls" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel33">Actualizar las sucursales</h4>
            </div>
            <center>
              <div class="col-11">
                <form action="?modulo=paqueterias&accion=setsucursales&paqueteria='.$paqueteria.'" method="post" enctype="multipart/form-data" onsubmit="guardando();">
                  <center>
                    <br>
                    <h4>Elegir archivo XLSX para cargar</h4>
                    <input type="file" class="form-control" id="archivo" name="archivo" accept=".xlsx" required ><br>
                    <button type="submit" class="btn btn-success" id="adjuntar" value="" />
                      <i class="fas fa-share"></i> Subir Layout
                    </button>
                  <center>
                  <br>
                </form>
              </div>
            </center>
          </div>
        </div>
      </div>
      ';

      echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th width="10%">Sucursal</th>
                    <th width="20%">Nombre</th>
                    <th width="20%">Plaza</th>
                    <th width="30%">Dirección</th>
                    <th width="10%">Estatus</th>
                    <th width="10%"></th>
                  </tr>
                </thead>'; 
    $sql = 'SELECT * FROM paqueterias_sucursales WHERE ps_paqueteria = "'.$paqueteria.'"';
    $result = setq($sql);
    while ($row = $result->fetch_array()) {
        if($row['ps_estatus'] == "1"){
          $color = 'class="alert bg-success"';
          $texto = 'Activa';
        } else {
          $color = 'class="alert bg-danger"';
          $texto = 'Inactiva';
        }
        echo '<tr>
        <td>'.$row['ps_sucursal'].'</td>
        <td>'.$row['ps_nmb'].'</td>
        <td>'.$row['ps_plaza'].'</td>
        <td>'.$row['ps_direccion'].'</td>
        <td '.$color.'>'.$texto.'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar sucursal" data-fancybox data-type="ajax" data-src="popup/setsucursales.php?id='.$row['ps_id'].'&paqueteria='.$paqueteria.'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';
        echo '</td>
        ';
    }
    echo '</table>';
  /* }
  else{
    echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
    echo '<a href="?modulo=articulos&accion=index">
            <button class="mb-2 mr-2 btn btn-warning">
              <i class="icon-android-arrow-back"></i> Atras
            </button>
          </a>
          <button class="mb-2 mr-2 btn btn-success" onclick="confirmasubida()">
            <i class="icon-upload4"></i> Subir precios
          </button>
          </div>
          </div>';

    if (!is_dir('adjuntos')) {
      @mkdir('adjuntos', 0777);
    }
    if (!is_dir('adjuntos/')) {
      @mkdir('adjuntos/', 0777);
    }
    if (!is_dir('adjuntos/layout')) {
      @mkdir('adjuntos/layout', 0777);
    }

    $directorio = 'adjuntos/layout';
    if($_FILES["archivo"]["name"]){
  	  $filename = $_FILES["archivo"]["name"]; //Obtenemos el nombre original del archivo
  	  $source = $_FILES["archivo"]["tmp_name"]; //Obtenemos un nombre temporal del archivo
    }
     if(!file_exists($directorio)){
      mkdir($directorio, 0777) or die("No se puede crear el directorio de extracci&oacute;n");
     }

     $dir=opendir($directorio); //Abrimos el directorio de destino
     $archivo = $directorio.'/tarifario-'.$paqueteria.'.xlsx'; //Indicamos la ruta de destino, así como el nombre del archivo
     if(move_uploaded_file($source, $archivo)){
      require_once 'lib/excel/Classes/PHPExcel.php';
      $inputFileType = PHPExcel_IOFactory::identify($archivo);
      $objReader = PHPExcel_IOFactory::createReader($inputFileType);
      $objPHPExcel = $objReader->load($archivo);
      $sheet = $objPHPExcel->getSheet(0);
      $highestRow = $sheet->getHighestRow();
      $highestColumn = $sheet->getHighestColumn();

      $num=0;
      ?>
      <div class="table-responsive">
      <table class="table table-stipped">
        <thead class="bg-primary text-white">
          <tr>
            <th>Codigo Postal</th>
            <th>Sucursal</th>
            <th>Plaza</th>
            <th>Municipio</th>
            <th>Ciudad</th>
            <th>Colonia</th>
            <th>Estado</th>
            <th>Cobertura</th>
          </tr>
        </thead>
      <?php
      for ($row = 2; $row <= $highestRow; $row++){

        $num++;
        ?>
          <tr>
            <td><?php echo $sheet->getCell("A".$row)->getValue();?></td>
            <td><?php echo $sheet->getCell("B".$row)->getValue();?></td>
            <td><?php echo $sheet->getCell("C".$row)->getValue();?></td>
            <td><?php echo $sheet->getCell("D".$row)->getValue();?></td>
            <td><?php echo $sheet->getCell("E".$row)->getValue();?></td>
            <td><?php echo $sheet->getCell("F".$row)->getValue();?></td>
            <td><?php echo $sheet->getCell("G".$row)->getValue();?></td>
          </tr>
        <?php

      }
      ?>
      </table> </div>
      <?php
     }
     else{
       die("Error al cargar el archivo");
     }

    
  } */
  ?>
  
  <script>
      $(document).ready(function () {
        var windowHeight = $(window).height();      
        $("#myTable").DataTable( {
            paging: true,
            scrollY: windowHeight * 0.5,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength: 50, 
        });
      });
      function confirmasubida(){
        var conf = confirm("¿Deseas subir el layout de precios cargado a JD CEO?");
        if(conf == true){
          window.location.href="?modulo=articulos&accion=setlayoutprecios";
        }
      }
      function guardando(){
        Swal.fire({
          icon: 'warning',
          title: "Guardando información!",
          html: "La información se esta guardando, no cierre esta ventana hasta que se finalice el proceso.",
          showConfirmButton: false,
          timerProgressBar: true,
          didOpen: () => {
            Swal.showLoading();
          },
          allowOutsideClick: false, // Evita que se cierre al hacer clic fuera de la alerta
        }).then((result) => {
          

          //Read more about handling dismissals below
          //if (result.dismiss === Swal.DismissReason.timer){
          //  console.log("I was closed by the timer");
          //}
        });
      } 
    </script>
    <?php

}
}
?>