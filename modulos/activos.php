<?php
  class activos{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelactivos($obj);
    }
    function index(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";
      if(!isset($_REQUEST['almacen'])) $_REQUEST['almacen'] = "T";
  
      $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus'],$_REQUEST['almacen']);
      $this->view = new viewlactivos($this->model);
      $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus'],$_REQUEST['almacen']);
    }
    function insert(){
      $this->model->setdata(getmax('a_id','activos'),$_SESSION['emp'], $_POST['nmbac'],$_POST['uso'],$_POST['accesorio'],$_POST['tipoactivo'],$_POST['capacidad'],$_POST['presion'],$_POST['observaciones']);
      $unieue = unique($_POST['nmb'],'a_nmb','activos');

      if($unieue == 1) {
        $this->model->insert();
        $error = "";
      }
      else $error = "&error=2XMM01";

      redirect('?modulo=activos&accion=index'.$error);
    }
    function update(){
      $this->model->setdata($_GET['id'],$_SESSION['emp'], $_POST['nmbac'],$_POST['uso'],$_POST['accesorio'],$_POST['tipoactivo'],$_POST['capacidad'],$_POST['presion'],$_POST['observaciones']);
      if(unique($_POST['nmbac'],'a_nmb','activos','a_empresa != "'.$_SESSION['emp'].'" AND a_id = "'.$this->model->id.'"') == 1) {
        $this->model->update();
        $error = "";
      }
      else $error = "&error=2XMM01";

      redirect('?modulo=activos&accion=index'.$error);
    }
    function borrar(){
      $sql = 'DELETE FROM activos
              WHERE a_id = "'.$_GET['id'].'"
              AND a_empresa = "'.$_SESSION['emp'].'"';
      setq($sql);

      redirect("?modulo=activos&accion=index");
    }
    function asignar(){
      $this->model->select($_GET['id']);
      $this->model->resultacprod($_GET['id']);
      $this->view = new viewlactivos($this->model);
      $this->view->asignar();
    }
    function asignarproducto(){
      $prod = getIDprod($_POST['producto']);
      $obs = clearvmayus($_POST['observaciones']);
      $this->model->asignarproducto($_POST['id'],$prod,$obs);
      
      redirect('?modulo=activos&accion=asignar&id='.$_POST['id']);
    }
    function borrarprod(){
      $this->model->borrarprod($_GET['activo'],$_GET['id']);

      redirect('?modulo=activos&accion=asignar&id='.$_GET['activo']);
    }
    function almacenes(){
      if(!isset($_POST['almacen'])) $_POST['almacen'] = NULL;

      $this->model->select($_GET['id']);
      $this->model->resultacalmaen($_GET['id'],$_POST['almacen']);
      $this->view = new viewlactivos($this->model);
      $this->view->almacenes($_POST['almacen']);
    }
    function insertalmacenes(){
      $this->model->insertalmacenes($_POST['activo'],$_POST['almacen'],$_POST['cantidad']);

      redirect('?modulo=activos&accion=almacenes&id='.$_POST['activo']);
    }
    function desasigna(){
      $this->model->desasigna($_GET['activo'],$_GET['producto'],$_GET['id']);

      redirect('?modulo=activos&accion=almacenes&id='.$_GET['activo']);
    }
  }

  class modelactivos{
    function result($nmb,$estatus,$almacen){
      $sql = 'SELECT * FROM activos WHERE a_empresa = "'.$_SESSION['emp'].'" ';
      if($nmb) $sql.= ' AND (a_nmb LIKE "%'.trim($nmb).'%" OR a_uso LIKE "%'.trim($nmb).'%" OR a_accesorio LIKE "%'.trim($nmb).'%" OR a_tipoactivo LIKE "%'.trim($nmb).'%")';
      if($almacen != "T") $sql.=' AND a_id IN (SELECT aa_activo FROM activo_almacen WHERE aa_almacen = "'.$almacen.'")';
      $sql.=' ORDER BY a_nmb ASC';
      $this->result = setq($sql);
    }
    function select($id){
      $sql = 'SELECT * FROM activos WHERE a_id="'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['a_id'];
      $this->nmb = $row['a_nmb'];
      $this->uso = $row['a_uso'];
      $this->accesorio = $row['a_accesorio'];
      $this->tipoactivo = $row['a_tipoactivo'];
      $this->capacidad = $row['a_capacidad'];
      $this->presion = $row['a_presion'];
      $this->largo = $row['a_largo'];
      $this->ancho = $row['a_ancho'];
      $this->alto = $row['a_alto'];
      $this->observaciones = $row['a_observaciones'];
      $this->estatus = $row['a_estatus'];
    }
    function setdata($id,$empresa,$nmbac,$uso,$accesorio,$tipoactivo,$capacidad,$presion,$observaciones,$estatus = "A"){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
      $cambio = "";
      $this->id = $id;
      $this->empresa = clearvmayus($empresa);
      $this->nmb = clearvmayus($nmbac);
      $this->uso = clearvmayus($uso);
      $this->accesorio = clearvmayus($accesorio);
      $this->tipoactivo = clearvmayus($tipoactivo);
      $this->capacidad = clearvmayus($capacidad);
      $this->presion = clearvmayus($presion);
      $this->observaciones = clearvmayus($observaciones);
      $this->estatus = clearvmayus($estatus);
    }
    function insert(){
      $sql = 'INSERT INTO activos SET
                a_id = "'.$this->id.'",
                a_nmb = "'.$this->nmb.'",
                a_uso = "'.$this->uso.'",
                a_accesorio = "'.$this->accesorio.'",
                a_tipoactivo = "'.$this->tipoactivo.'",
                a_capacidad = "'.$this->capacidad.'",
                a_presion = "'.$this->presion.'",
                a_observaciones = "'.$this->observaciones.'",
                a_estatus = "'.$this->estatus.'",
                a_empresa = "'.$this->empresa.'"';
      setq($sql);
    }
    function update(){
      $sql = 'UPDATE activos SET
              a_nmb = "'.$this->nmb.'",
              a_uso = "'.$this->uso.'",
              a_accesorio = "'.$this->accesorio.'",
              a_tipoactivo = "'.$this->tipoactivo.'",
              a_capacidad = "'.$this->capacidad.'",
              a_presion = "'.$this->presion.'",
              a_observaciones = "'.$this->observaciones.'",
              a_estatus = "'.$this->estatus.'",
              a_empresa = "'.$this->empresa.'"
              WHERE a_id = "'.$this->id.'"';
      setq($sql);
    }
    function resultacprod($activo){
      $sql = 'SELECT * FROM activo_producto INNER JOIN articulos ON a_id = ap_producto
              WHERE ap_activo = "'.$activo.'"';
      $this->result = setq($sql);
    }
    function asignarproducto($id,$prod,$obs){
      $sql = 'INSERT IGNORE INTO activo_producto SET
              ap_activo = "'.$id.'",
              ap_producto = "'.$prod.'",
              ap_observaciones = "'.$obs.'"';
      setq($sql);
    }
    function borrarprod($activo,$producto){
      $sql = 'DELETE FROM activo_producto WHERE ap_activo = "'.$activo.'" AND ap_producto = "'.$producto.'"';
      setq($sql);
    }
    function resultacalmaen($activo,$almacen){
      $sql = 'SELECT * FROM activo_almacen INNER JOIN almacenes ON a_id = aa_almacen
              WHERE aa_activo = "'.$activo.'" ';
      if($almacen) $sql.=' AND aa_almacen = "'.$almacen.'" ';
      $this->result = setq($sql);
    }
    function insertalmacenes($activo,$almacen,$cantidad){
      for($i = 0;$i<$cantidad;$i++){
        $acronimo = busca($_SESSION['emp'],'empresas','e_id','e_siglas');
        $folio = substr(busca($almacen,'almacenes','a_id','a_nmb'),0,3);
        $numactivos = busca($activo,'activo_almacen','aa_almacen = "'.$almacen.'" AND aa_activo','COUNT(*)');
        $numactivos++;
        
        $serie = str_pad($numactivos,4,"0",STR_PAD_LEFT);
        $noserie = $acronimo.$folio.$serie;

        $sql = 'INSERT INTO activo_almacen SET
                aa_activo = "'.$activo.'",
                aa_almacen = "'.$almacen.'",
                aa_noserie = "'.$noserie.'"';
        setq($sql);
      }
    }
    function desasigna($activo,$almacen,$id){
      $sql = 'DELETE FROM activo_almacen WHERE aa_id = "'.$id.'" ';
      setq($sql);
    }
  }

  class viewlactivos {
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->model->aest = array("A" => "Activo","I" => "Inactivo");
      ?>
      <script language="JavaScript">
        function checkSubmitedit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
      </script>
      <?php
    }
    function browse($nmb,$estatus,$almacen) {
      //Sección: E1 Encabezado - Botones de acción
      echo '<div class="row page-title-actions">';
        if($estatus == "A") $chest = "checked"; else $chest = "";
        $esta = "";
        $esti = "";
        $estt = "";
        if(!isset($estatus) || $estatus == "A") $esta = "selected";
        elseif($estatus == "I") $esti = "selected";
        else $estt = "selected";
        //Sección: E2 Encabezado - Filtros
        ?>
        <div class="ml-2 col-md-2">
          <a data-fancybox data-type="ajax" data-src="popup/setactivo.php" href="javascript:;">
            <button type="button" class="btn btn-primary">
              <i class="fa fa-search"></i> Nuevo
            </button>
          </a>
          <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search"></i> Filtrar
          </button>
        </div>
        <div class="col-md-1">
          <button class="btn btn-secondary" data-toggle="tooltip" data-placement="bottom" data-original-title="Un activo en la sección de productos, es un medio mediante el cual se permite la venta de un producto más" data-trigger="click" >
            <i class="icon-question-circle"></i>
          </button>
        </div>
        <div id="filter-panel" class="col-md-7 collapse filter-panel">
          <div class="panel panel-default">
            <div class="panel-body">
              <form class="form-inline" role="form" method="post" action="?modulo=activos&accion=index">
                <div class="mb-5 mr-2">
                  <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
                </div><!-- form group [search] -->
                <div class="mb-5 mr-2">
                  <?php
                  $sql = 'SELECT * FROM almacenes WHERE a_empresa = "'.$_SESSION['emp'].'"';
                  $result = setq($sql);
                  echo '<select name="almacen" class="form-control" onchange="submit();">';
                    echo '<option value="T">Todos los almacenes</option>';
                    while($row = $result->fetch_array()){
                      if($almacen == $row['a_id']) $sel = 'selected'; else $sel = "";
                      $numacts = busca($row['a_id'],'activo_almacen','aa_almacen','COUNT(*)');
                      if(!$numacts) $numacts = 0;
                      echo '<option value="'.$row['a_id'].'" '.$sel.'>'.$row['a_nmb'].' - ('.$numacts.')</option>';
                    }
                  echo '</select>';
                  ?>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                  </button>
                  <a href="?modulo=activos&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                  </button></a>
                  <a href="formats/pdfactivosalmacen.php?almacen=<?php echo $almacen ?>" target="_BLANK"><button type="button" class="btn btn-danger">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-file-pdf"></i> Imprimir
                  </button></a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div></div>
      <script>
        function checerase(activo){
          var conf = confirm("¿Deseas eliminar la marca seleccionada?\nPresiona Aceptar para borrarla");
          if(conf == true){
            window.location.href="?modulo=activos&accion=borrar&activo=" + activo ;
          }
        }
        function verlineasneg(iduni){
          window.open("?modulo=articulos&accion=index&marca=" + iduni);
        }
      </script>
      <div class="main-card mb-3 card">
        <div class="card-body row">
          <?php
          echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
          echo '</div>
        </div>';
        echo '<div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead class="bg-light-blue bg-darken-2 text-white">
                  <tr>
                    <th>Nombre</th>
                    <th>Uso</th>
                    <th>Válvula</th>
                    <th>Tipo</th>
                    <th>Capacidad</th>
                    <th>Presión</th>
                    <th width="35%"></th>
                  </tr>
                </thead>';
                while ($row = $this->model->result->fetch_array()) {
                  $ligados = busca($row['a_id'],'activo_producto','ap_activo','COUNT(*)');
                  if($almacen == "T")
                    $invalmacen = busca($row['a_id'],'activo_almacen','aa_activo','COUNT(*)');
                  else
                    $invalmacen = busca($row['a_id'],'activo_almacen','aa_almacen = "'.$almacen.'" AND aa_activo','COUNT(*)');
                  
                  echo '<tr>
                    <td><h5>'.$row['a_nmb'].'</h5></td>
                    <td><h5>'.$row['a_uso'].'</h5></td>
                    <td><h5>'.$row['a_accesorio'].'</h5></td>
                    <td><h5>'.$row['a_tipoactivo'].'</h5></td>
                    <td><h5>'.$row['a_capacidad'].'</h5></td>
                    <td><h5>'.$row['a_presion'].'</h5></td>
                    <td>
                      <a data-fancybox data-type="ajax" data-src="popup/setactivo.php?id='.$row['a_id'].'" href="javascript:;">
                        <button type="button" class="btn btn-primary"> <i class="fa fa-pen"></i> Editar </button>
                      </a>
                      <a href="?modulo=activos&accion=asignar&id='.$row['a_id'].'">
                        <button type="button" class="btn btn-info"> <i class="icon-indent"></i> Productos
                          <div class="tag tag-pill tag-danger">'.$ligados.'</div>
                        </button>
                      </a>
                      <a href="?modulo=activos&accion=almacenes&id='.$row['a_id'].'">
                        <button type="button" class="btn bg-blue-grey bg-darken-2 text-white"> <i class="icon-th"></i> Inventario
                          <div class="tag tag-pill tag-danger">'.$invalmacen.'</div>
                        </button>
                      </a>
                    </td>
                  </tr>';
                }
              echo '</table>
            </div>
          </div>
        </div>
      </div>';
    }
    function asignar(){
      ?>
      <script>
        $(document).ready(function() {
          $('#producto').on('keyup', function() {
            var key = $(this).val();
            var dataString = 'producto='+key;
            $.ajax({
              type: "POST",
              url: "query/suggestproducts.php",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  setname();
                  //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
                  return false;
                });
              }
            });
          });
        });
      </script>
      <script>
        function checerase(activo,id){
          var conf = confirm("¿Deseas eliminar el producto del activo?\nPresiona Aceptar para borrarlo");
          if(conf == true){
            window.location.href="?modulo=activos&accion=borrarprod&activo=" + activo + "&id=" + id;
          }
        }
        function verlineasneg(iduni){
          window.open("?modulo=articulos&accion=index&marca=" + iduni);
        }
      </script>
      <?php
      echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
        echo '<a href="?modulo=activos&accion=index" accesskey="">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
        </a>';
      echo '</div></div>';
      echo '<div class="row mb-1">
        <div class="col-12 bg-primary p-1 text-white">
          <h3>
            Productos ligados '.$this->model->nmb.' - '.$this->model->uso.' - '.$this->model->accesorio.' - '.$this->model->tipoactivo.' - '.$this->model->capacidad.' - '.$this->model->presion.'
          </h3>
        </div>';
      echo '</div>';
      echo '<div class="row">
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead class="bg-light-blue bg-darken-2 text-white">
                <tr>
                  <th width="45%">Producto</th>
                  <th width="45%">Observaciones</th>
                  <th width="10%"></th>
                </tr>
              </thead>';
              $accion = 'asignarproducto';
              echo '<form method="post" action="?modulo=activos&accion='.$accion.'" autocomplete="off">
                <input type="hidden" value="'.$this->model->id.'" name="id" />
                <tr>
                  <td>
                    <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required autofocus >
                    <div id="suggestions"></div>
                  </td>
                  <td><textarea name="observaciones" class="form-control" placeholder="Espacio para anotar alguna observación en la relación producto-activo"></textarea></td>
                  <td><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i></td>
                </tr>
              </form>';
              while ($row = $this->model->result->fetch_array()) {
                echo '<tr>
                  <td>'.$row['a_nmb'].'</td>
                  <td>'.$row['ap_observaciones'].'</td>
                  <td>';
                    //if(busca($row['a_id'],'articulos','a_estatus = "A" AND a_categoria','COUNT(*)') == 0)
                    echo '<button type="button" class="btn btn-danger text-white" onclick="checerase('.$row['ap_activo'].','.$row['a_id'].')">
                      <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar la liga del producto" class="icon-eraser"></i>
                    </button>';
                    /*
                      else
                        echo '<button type="button" class="btn bg-teal text-white" onclick="verlineasneg('.$row['a_id'].')">
                          <i data-toggle="tooltip" data-placement="top" title data-original-title="La linea de negocio se encuentra en uso, Presiona aquí para ver los productos asignados a '.$row['a_nmb'].'" class="fa fa-eye"></i>
                        </button> ';
                    */
                  echo '</td>';
              }
            echo '</table>
          </div>
        </div>
      </div>';
    }
    function almacenes($almacen){
      ?>
      <script>
        function checerase(activo,producto){
          var conf = confirm("¿Deseas eliminar la marca seleccionada?\nPresiona Aceptar para borrarla");
          if(conf == true){
            window.location.href="?modulo=activos&accion=borrarprod&activo=" + activo + "&producto=" + producto;
          }
        }
        function verlineasneg(iduni){
          window.open("?modulo=articulos&accion=index&marca=" + iduni);
        }
      </script>
      <?php
      echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
        echo '<a href="?modulo=activos&accion=index" accesskey="">
          <button type="button" class="btn btn-warning mr-2 ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
        </a>';
      echo '</div></div>';
      echo '<div class="row mb-1">
        <div class="col-12 col-md-6 bg-info p-1 text-white">
          <h3>
            Inventario '.$this->model->nmb.' - '.$this->model->uso.' - '.$this->model->accesorio.' - '.$this->model->tipoactivo.' - '.$this->model->capacidad.' - '.$this->model->presion.'
          </h3>
        </div>
        <div class="col-4 col-md-2">
          <a data-fancybox data-type="ajax" data-src="popup/setactivoalmacen.php?id='.$this->model->id.'" href="javascript:;">
            <button type="button" class="btn btn-primary mt-1">
              <i class="fa fa-search"></i> Asignar activos
            </button>
          </a>
        </div>
        <div class="col-8 col-md-4">
          <label for="almacen">Filtrar por almacen</label>
          <form method="post">
            '.menu_select_db('almacenes','a_id','a_nmb',$almacen,'almacen','a_empresa = "'.$_SESSION['emp'].'" AND a_estatus = "A"',false,false,"S",true).'
          </form>
        </div>';
      echo '</div>';
      echo '<div class="row">
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead class="bg-light-blue bg-darken-2 text-white">
                <tr>
                  <th width="30%">Almacen</th>
                  <th width="20%">Serie</th>
                  <th width="20%">Estatus</th>
                  <th width="20%"></th>
                </tr>
              </thead>';
              $accion = 'asignarproducto';
              while ($row = $this->model->result->fetch_array()) {
                if($row['aa_estatus'] == "N"){ $progress = "100"; $colorp = "red"; $estatus = "Vacio"; }
                elseif($row['aa_estatus'] == "A"){ $progress = "100"; $colorp = "orange"; $estatus = "Asignado"; }
                elseif($row['aa_estatus'] == "L"){ $progress = "100"; $colorp = "green"; $estatus = "Lleno"; }
                else{ $progress = "100"; $colorp = "white"; $estatus = "-"; }
                echo '<tr>
                  <td>'.$row['a_nmb'].'</td>
                  <td>'.$row['aa_noserie'].'</td>
                  <td> '.$estatus.'
                    <progress class="progress progress progress-'.$colorp.' mt-1 mb-0" value="'.$progress.'" max="'.$progress.'"></progress>
                  </td>
                  <td>';
                    //if(busca($row['a_id'],'articulos','a_estatus = "A" AND a_categoria','COUNT(*)') == 0)
                    echo '<button type="button" class="btn btn-danger text-white" onclick="checerase('.$row['aa_id'].')">
                      <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar la liga del producto" class="icon-eraser"></i> Borrar
                    </button>';
                    echo '<a action="?modulo=activos&accion=historial&id='.$row['aa_activo'].'">
                      <button type="button" class="btn btn-primary text-white">
                        <i data-toggle="tooltip" data-placement="top" title data-original-title="Historial del activo" class="icon-history2"></i> Historial
                      </button>
                    </a>';
                    /*
                      else
                        echo '<button type="button" class="btn bg-teal text-white" onclick="verlineasneg('.$row['a_id'].')">
                          <i data-toggle="tooltip" data-placement="top" title data-original-title="La linea de negocio se encuentra en uso, Presiona aquí para ver los productos asignados a '.$row['a_nmb'].'" class="fa fa-eye"></i>
                        </button> ';
                    */
                  echo '</td>
                </tr>';
              }
            echo '</table>
          </div>
        </div>
      </div>';
    }
  }
?>