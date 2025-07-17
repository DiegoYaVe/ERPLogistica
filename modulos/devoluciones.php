<?php
  ini_set('display_errors', 1);
  class devoluciones {
    var $model;
    var $view;

    function __construct() {
      $this->model = new modeldevoluciones(isset($obj));
    }
    function index() {
      $iniciomes = strtotime('first day of this month', time());
      $finmes = strtotime('last day of this month', time());
      if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d', $iniciomes);
      if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d', $finmes);
      if (!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->result($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['estatus'],$_REQUEST['page']);
      $this->view = new viewdevoluciones($this->model);
      $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['estatus'],$_REQUEST['page']);
    }
    function show(){
      $this->model->select($_GET['id']);
      $this->model->resultd($_GET['id']);
      $this->view = new viewdevoluciones($this->model);
      $this->view->show();
    }
    function insert(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_GET['idt'])) $_GET['idt'] = NULL;
      $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-DV';
      $folioint = getmax('rd_folio','remisiones_devoluciones','rd_empresa = "'.$_SESSION['emp'].'"');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }
      $this->model->setdata(NULL,$folioint,$_GET['id'],$_GET['idt'],NULL,NULL,"N");
      $id = $this->model->insert();

      $query = 'SELECT * FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision
              WHERE r_id = "'.$_GET['id'].'" ORDER BY rd_nmbarticulo';
      $resultado = setq($query);
      $viva = busca("1",'configuracionesp','c_id','c_iva');
      $i = 0;
      while($row = $resultado->fetch_array()){
        if($_POST['prod'.$row['rd_id']]){
          $cantidad = $_POST['cantidad'.$row['rd_id']];
          $costo = $_POST['costo'.$row['rd_id']];
          
          $articulo = busca($row['rd_id'],'remisionesd','rd_id','rd_articulo');
          $nmbprod = busca($row['rd_id'],'remisionesd','rd_id','rd_nmbarticulo');

          $this->model->setdatad(NULL,$id,$articulo,$nmbprod,$cantidad,$costo,$row['rd_id'],$row['rd_iva'],$row['r_descuento']);
          $this->model->insertd();
        }
      }
      echo '<script>
        window.opener.location.reload();
        window.opener.location.href="?modulo=devoluciones&accion=show&id='.$id.'";
        window.close();
      </script>';
    }
    function delprod(){
      $this->model->delprod($_GET['id'],$_GET['idprod']);
      redirect('?modulo=devoluciones&accion=show&id='.$_GET['id'].'');
    }
    function cancelar(){
      $this->model->cancelar($_GET['remision'],$_GET['id']);
      redirect('?modulo=devoluciones&accion=index');
    }
    function updated(){
      //foreach($_POST as $campo => $valor){	echo "POST->". $campo ."= ". $valor.'<br>'; }
      //foreach($_GET as $campo => $valor) {	echo "GET->". $campo ."= ". $valor.'<br>'; }
      //die();
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_GET['remision'])) $_GET['remision'] = NULL;
      
      $query = 'SELECT * FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision
              WHERE r_id = "'.$_GET['remision'].'" ORDER BY rd_nmbarticulo';
      $resultado = setq($query);
      $viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
      $i = 0;
      while($row = $resultado->fetch_array()){
        if($_POST['prod'.$row['rd_id']]){
          $cg = busca($row['rd_id'],'remisiones_devolucionesd','rdd_devolucion = "'.$_GET['id'].'" AND rdd_remisiond','rdd_cantidad');
          if($cg > 0) $cantidad = $cg + $_POST['cantidad'.$row['rd_id']];
          else $cantidad = $_POST['cantidad'.$row['rd_id']];
          $costo = $_POST['costo'.$row['rd_id']];

          $articulo = busca($row['rd_id'],'remisionesd','rd_id','rd_articulo');
          $nmbprod = busca($row['rd_id'],'remisionesd','rd_id','rd_nmbarticulo');
          $linean = busca($row['rd_id'],'remisionesd','rd_id','rd_linean');
          $idd = busca($row['rd_id'],'remisiones_devolucionesd','rdd_devolucion="'.$_GET['id'].'" AND rdd_remisiond','rdd_id');
          
          $this->model->setdatad($idd,$_GET['id'],$articulo,$nmbprod,$linean,$cantidad,$costo,$row['rd_id'],$row['rd_iva'],$row['r_descuento']);
          if($idd) {
            $this->model->updated();
          }else {
            $this->model->insertd();
          }
        }
      }
      echo '<script>
        window.opener.location.reload();
        window.close();
      </script>';
    }
    function update(){
      if(!isset($_POST['motivo'])) $_POST['motivo'] = $_POST['motivorel'];
      $this->model->setdata($_GET['id'],NULL,$_GET['remision'],NULL,$_POST['motivo'],date('Y-m-d H:i:s'),"A");
      $this->model->update();
      
      $this->model->select($_GET['id']);
      
      $nmbrem = busca($this->model->remision,'remisiones','r_id','r_folio');
      include_once('cxcobrar.php');
      $cuentaxc = new modelcxcobrar();
      $idcx = busca($this->model->remision,'cxcobrar','cx_tipo = "R" AND cx_referencia','cx_id');
      $cuentaxc->select($idcx);

      $totalr = busca($this->model->remision,'remisiones','r_id','r_total');
      $impdevolucion = $_POST['impd'];
      $abonado = $cuentaxc->abonado;
      $restante = $totalr - $abonado;

      $debes = $restante - $impdevolucion;
      if($cuentaxc->estatus == "N"){
        $montofinal0 = $totalr - $impdevolucion;
        $cuentaxc->updatecxc("C","CANCELADA POR DEVOLUCIÓN DE PRODUCTOS REMISIÓN - ".$nmbrem,$idcx);
        if($montofinal0 > 0){
          $id = $cuentaxc->insertcxcobrar($_SESSION['emp'],$cuentaxc->cliente,$this->model->remision,"R",$montofinal0,$cuentaxc->fini,"");
          $cuentaxc->addproyeccion($id);
        }
      }elseif($cuentaxc->estatus == "A"){
        $cuentaxc->updatecxc("C","CANCELADA POR DEVOLUCIÓN DE PRODUCTOS REMISIÓN - ".$nmbrem,$idcx);
        if($debes > 0){
          $id = $cuentaxc->insertcxcobrar($_SESSION['emp'],$cuentaxc->cliente,$this->model->remision,"R",$debes,$cuentaxc->fini,"");
          $cuentaxc->addproyeccion($id);
        }else{
          include_once('cxpagar.php');
          $cxpagar = new modelcxpagar();
          $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_id');
          $id = $cxpagar->insertcxpagar($_SESSION['emp'],$proveedor,$_GET['id'],"D",abs($debes),$this->model->fecha,"DEVOLUCIÓN DE REMISIÓN - ".$nmbrem);
          $cxpagar->addproyeccion($id);
        }
      }elseif($cuentaxc->estatus == "F"){
        $montofinal0 = $totalr - $impdevolucion;
        include_once('cxpagar.php');
        $cxpagar = new modelcxpagar();
        $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_id');
        if($montofinal0 <= 0){
          $id = $cxpagar->insertcxpagar($_SESSION['emp'],$proveedor,$_GET['id'],"D",$totalr,$this->model->fecha,"DEVOLUCIÓN DE REMISIÓN - ".$nmbrem); 
          $cxpagar->addproyeccion($id);
        }else{
          $id = $cxpagar->insertcxpagar($_SESSION['emp'],$proveedor,$_GET['id'],"D",$montofinal0,$this->model->fecha,"DEVOLUCIÓN DE REMISIÓN - ".$nmbrem); 
          $cxpagar->addproyeccion($id);
        }
      }
      
      $this->model->ajustaexistencia($_GET['id']);

      if(isset($_POST['ncredito'])){
        include('remisiones.php');
        $remisionM = new modelremisiones();
        $remisionM->select($_GET['remision']);
        $fiscales = busca($remisionM->cliente,'crm_fiscales','cf_predeterminada = "1" AND cf_cliente','cf_id');
        include('ncredito.php');
        $ncredito = new Modelncredito();
        $idncr = $ncredito->insertncreditoblanco($remisionM->cliente,$fiscales,$_SESSION['emp'],"4.0");
        $ncredito->insertncrrem($idncr,$_GET['remision']);

        $link= "?modulo=ncredito&accion=showncredito&ncredito=".$idncr."&new=1";
      }else $link = '?modulo=devoluciones&accion=show&id='.$_GET['id'].'';
      
      redirect($link);
    }
    function updatemotivo(){
      $this->model->setdata($_GET['id'],NULL,NULL,NULL,$_POST['motivo'],NULL,NULL);
      $this->model->updatemotivo();
      redirect('?modulo=devoluciones&accion=show&id='.$_GET['id'].'');
    }
  }

  class modeldevoluciones {
    function select($id){
      $sql = 'SELECT * FROM remisiones_devoluciones WHERE rd_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['rd_id'];
      $this->folio = $row['rd_folio'];
      $this->remision = $row['rd_remision'];
      $this->tablero = $row['rd_tablero'];
      $this->motivo = $row['rd_motivo'];
      $this->fecha = $row['rd_fecha'];
      $this->usuario = $row['rd_usuario'];
      $this->estatus = $row['rd_estatus'];
      $this->ncredito = $row['rd_ncredito'];
    }
    function result($fini,$ffin,$estatus,$page){
      $bloque = 50;
      $sql = 'SELECT * FROM remisiones_devoluciones WHERE rd_empresa = "'.$_SESSION['emp'].'" 
              AND DATE(rd_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
      if($estatus) $sql .= ' AND rd_estatus = "'.$estatus.'"';
      $sql .= ' ORDER BY rd_id DESC';
      $result = setq($sql);
      if($result->num_rows > $bloque) $sql .= ' LIMIT '.($bloque*$page).','.$bloque;
      $this->resultrc = setq($sql);
      $this->resultt = setq($sql);
    }
    function setdata($id,$folio,$remision,$tablero,$motivo,$fecha,$estatus){
      $this->id = $id;
      $this->folio = $folio;
      $this->remision = $remision;
      $this->tablero = $tablero;
      $this->motivo = clearvmayus($motivo);
      $this->fecha = $fecha;
      $this->estatus = $estatus;
    }
    function insert(){
      $sql = 'INSERT INTO remisiones_devoluciones SET
              rd_folio = "'.$this->folio.'",
              rd_remision = "'.$this->remision.'",
              rd_empresa = "'.$_SESSION['emp'].'",
              rd_tablero = "'.$this->tablero.'",
              rd_motivo = "'.$this->motivo.'",
              rd_fecha = "'.date('Y-m-d H-i-s').'",
              rd_usuario = "'.$_SESSION['uid'].'",
              rd_estatus = "'.$this->estatus.'"';
      setq($sql);
      $idd = getmax('rd_id','remisiones_devoluciones','rd_empresa',false);
      return($idd);
    }
    function update(){
      $sql = 'UPDATE remisiones_devoluciones SET
              rd_motivo = "'.$this->motivo.'",
              rd_estatus = "'.$this->estatus.'",
              rd_fecha = "'.$this->fecha.'"
              WHERE rd_id = "'.$this->id.'"';
      setq($sql);
    }
    function cancelar($remision,$id){
      $sql = 'UPDATE remisiones_devoluciones SET rd_estatus = "C"
              WHERE rd_id = "'.$id.'" AND rd_remision = "'.$remision.'"';
      setq($sql);
    }
    function selectd($id,$idd){
      $sql = 'SELECT * FROM remisiones_devolucionesd WHERE rdd_devolucion = "'.$id.'" AND rdd_id = "'.$idd.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->idd = $row['rdd_id'];
      $this->devoluciond = $row['rdd_devolucion'];
      $this->articulod = $row['rdd_articulo'];
      $this->nmbarticulod = $row['rdd_nmbarticulo'];
      $this->lineand = $row['rdd_linean'];
      $this->cantidadd = $row['rdd_cantidad'];
      $this->preciod = $row['rdd_precio'];
    }
    function resultd($id){
      $sql = 'SELECT * FROM remisiones_devolucionesd WHERE rdd_devolucion = "'.$id.'" ORDER BY rdd_id';
      $this->resultdd = setq($sql);
    }
    function setdatad($id,$devolucion,$articulo,$nmbarticulo,$cantidad,$precio,$remisiond,$iva,$desceunto){
      $this->id = $id;
      $this->devolucion = $devolucion;
      $this->articulo = $articulo;
      $this->nmbarticulo = clearvmayus($nmbarticulo);
      $this->cantidad = $cantidad;
      $this->precio = $precio;
      $this->remisiond = $remisiond;
      $this->iva = $iva;
      $this->descuento = $desceunto;
    }
    function insertd(){
      $sql = 'INSERT INTO remisiones_devolucionesd SET 
              rdd_devolucion = "'.$this->devolucion.'",
              rdd_articulo = "'.$this->articulo.'",
              rdd_nmbarticulo = "'.$this->nmbarticulo.'",
              rdd_cantidad = "'.$this->cantidad.'",
              rdd_precio = "'.$this->precio.'",
              rdd_iva = "'.$this->iva.'",
              rdd_descuento = "'.$this->descuento.'",
              rdd_remisiond = "'.$this->remisiond.'"';
      setq($sql);
    }
    function updated(){
      $sql = 'UPDATE remisiones_devolucionesd SET
              rdd_cantidad = "'.$this->cantidad.'",
              rdd_iva = "'.$this->iva.'"
              WHERE rdd_id = "'.$this->id.'"';
      setq($sql);
    }
    function delprod($id,$idprod){
      $sql = 'DELETE FROM remisiones_devolucionesd WHERE rdd_devolucion = "'.$id.'" AND rdd_id = "'.$idprod.'"';
      setq($sql);
    }
    function updatemotivo(){
      $sql = 'UPDATE remisiones_devoluciones SET rd_motivo = "'.$this->motivo.'" WHERE rd_id = "'.$this->id.'"';
      setq($sql);
    }
    function ajustaexistencia($id){
      $sql = 'SELECT rdd_articulo,rdd_cantidad,r_almacen
                FROM remisiones_devolucionesd INNER JOIN remisiones_devoluciones ON rdd_devolucion = rd_id
                INNER JOIN remisiones ON rd_remision = r_id
                INNER JOIN articulos ON a_id = rdd_articulo
                WHERE rd_estatus = "A" AND rd_id = "'.$id.'" AND rdd_articulo != "0"';
      $result1 = setq($sql);

      while ($row = $result1->fetch_array()) {
        $cmb = busca($row['rdd_articulo'],'articulos','a_id','a_tipoprod');
        if($cmb == "M"){
          $sqlcmb = 'SELECT ac_ahijo,ac_cantidad FROM articulos_combos WHERE ac_articulo = "'.$row['rdd_articulo'].'"';
          $resultcmb = setq($sqlcmb);
          while($rowcmb = $resultcmb->fetch_array()){
            $inv = busca($rowcmb['ac_ahijo'],'articulos','a_id','a_inventariado');
            if($inv == "1"){
              ajustaexistencia($rowcmb['ac_ahijo'],$rowcmb['ac_cantidad'],$row['r_almacen'],"E");
            }
          }
        }else{
          $inv = busca($row['rdd_articulo'],'articulos','a_id','a_inventariado');
          if($inv == "1"){
            ajustaexistencia($row['rdd_articulo'],$row['rdd_cantidad'],$row['r_almacen'],"E");
          }
        }
      }
    }
  }

  class viewdevoluciones {
    var $model;

    function __construct($model) {
      $this->model = $model;
    }
    function browse($fini,$ffin,$estatus,$page) {
      $this->model->aestatus = array("A" => "Finalizada","N" => "En Captura","C" => "Devolución Cancelada");
      $this->model->nestatus = array("A" => "btn-success","N"=>"bg-yellow bg-darken-3","C" => "btn-danger");
      echo '
      <div class="row page-title-actions">
        <button accesskey="L" id="filtrar" type="button" class="btn btn-info mb-1"  data-toggle="collapse" data-target="#filter-panel">
          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
        </button>
      </div>';
        $selt = '';
        $sela = '';
        $seln = '';
        $selc = '';
        if($estatus == "A") $sela = "selected";
        if($estatus == "N") $seln = "selected";
        if($estatus == "C") $selc = "selected";
        else $selt = 'selected';
        ?>
        <div id="filter-panel" class="collapse filter-panel col-md-12 mb-2">
          <div class="">
            <div class="">
              <form class="form-inline" role="form" method="post" action="?modulo=devoluciones&accion=index">
                <div class="mb-5">
                  <label for="tipom">Desde:</label>
                  <input type="date" name="fini" id="fini" class="form-control" value="<?php echo $fini ?>" onchange="submit();"/>
                </div>
                <div class="mb-5">
                  <label for="tipom">Hasta:</label>
                  <input type="date" name="ffin" id="ffin" class="form-control" value="<?php echo $ffin?>" onchange="submit();"/>
                </div>
                <div class="mb-5">
                  <label for="tipom">Estatus:</label>
                  <select class="form-control" name="estatus" id="estatus" onchange="submit();">
                    <option value="" <?php echo $selt; ?> >Todos</option>
                    <option value="N" <?php echo $seln; ?> >En captura</option>
                    <option value="A" <?php echo $sela; ?> >Aplicados</option>
                    <option value="C" <?php echo $selc; ?> >Cancelados</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                  <a href="?modulo=devoluciones&accion=index">
                    <button type="button" class="btn btn-warning">
                      <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                    </button>
                  </a>
                </div>
                <!--<div class="mb-5 mt-2">
                  <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="bottom" data-original-title="." data-trigger="click" >
                    <i class="icon-question-circle"></i>
                  </button>
                </div>-->
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="">
        <div class="card-body row">
        <?php
          echo '<div class="table table-responsive">
            <table class="table table-hover table-striped">
              <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
                <tr>
                  <th>Folio</th>
                  <th>Remisión</th>
                  <th>Fecha</th>
                  <th>Estatus</th>
                  <th width="15%"></th>
                </tr>
              </thead>';
              while($row = $this->model->resultrc->fetch_array()){
                if($row['rd_estatus'] == "A"){
                  $lastm = "Aplicación: ".$row['rd_usuario'].'<br>'.date('d-m-Y',strtotime($row['rd_fecha']));
                }
                else{
                  $lastm = "Registro: ".$row['rd_usuario'].'<br>'.date('d-m-Y',strtotime($row['rd_fecha']));
                }
                $remision = busca($row['rd_remision'],'remisiones','r_id','r_folio');
                echo '<tr>
                  <th>'.$row['rd_folio'].'</th>
                  <th>'.$remision.'</th>
                  <td>'.$lastm.'</td>
                  <td class="'.$this->model->nestatus[$row['rd_estatus']].'">'.$this->model->aestatus[$row['rd_estatus']].'</td>
                  <td>
                    <a href="?modulo=devoluciones&accion=show&id='.$row['rd_id'].'">
                      <button type="button" class="btn btn-info"><i class="fa fa-eye"></i></button>
                    </a>';
                    if($row['rd_estatus'] == "A"){
                      echo '<a target="_BLANK" href="formats/pdfdevolucion.php?id='.$row['rd_id'].'" >
                        <button type="button" class="btn btn-danger" /><i class="fa fa-file-pdf"></i></button>
                      </a>';
                    }
                  echo'</td>
                </tr>';
              }
            echo'</table>
          </div>';
          echo '<div class="col-md-12 text-xs-center">
            <div class="mb-3">
              <nav aria-label="Page navigation">
                <ul class="pagination">
                  <li class="page-item">';
                    if($page == 0) $npag = 0;
                    else $npag = $page -1;
                    echo'<a class="page-link" href="?modulo=devoluciones&accion=index&page='.$npag.'" aria-label="Previous">
                      <span aria-hidden="true">&laquo; Ant</span>
                      <span class="sr-only">Anterior</span>
                    </a>
                  </li>';      
                  $numres = 50;
                  $nr = $this->model->resultt->num_rows;
                  $np = $nr/$numres;
                  $paginaa = $page-1;
                  $pagina = $page+1;
                  $sqlpg = 'SELECT COUNT(*) FROM remisiones_devoluciones INNER JOIN remisiones ON rd_remision = r_id 
                            WHERE r_empresa = "'.$_SESSION['emp'].'" AND DATE(rd_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
                  $resultpg = setq($sqlpg);
                  list($numg) =  $resultpg->fetch_array();
                  $pageres = ceil($numg/$numres);
                  if($pageres>10){
                    if($_GET['page'] == 0) { $min = 1; $nombre = "Inicio";}
                    else {$min = $_GET['page']-1; $nombre = "Inicio...";}
                    if($min <= 1) $min = 1;
                    if($_GET['page'] == ($pageres-1)) $max = ($pageres-1);
                    else $max = $_GET['page']+3;
                    if($max >= ($pageres-1)) $max = ($pageres-1);
                    if($_GET['page'] == 0) $active = "active";
                    else $active = "";
                    echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=devoluciones &accion=index&page=0">'.$nombre.'</a></li>';
                  }elseif($pageres<6){
                    $max=$pageres;
                    $min=0;
                  }
                  for($i=$min;$i<$max;$i++){
                      if($page == $i) $active = "active";
                      else $active = "";
                      if($i == 0) $nombre = "Inicio";
                      else $nombre = $i;
                    echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=devoluciones&accion=index&page='.$i.'">'.$nombre.'</a></li>';
                  }
                  if($pageres>9){
                    if($_GET['page'] == ($pageres-5)) $nombre = ($pageres-2);
                    else $nombre = '... '.($pageres-2);
                    if($_GET['page'] == $i) $active = "active";
                    else $active = "";
                    if($_GET['page'] >= ($pageres-4)) echo '';
                    else echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=devoluciones&accion=index&page='.($pageres-2).'">'.$nombre.'</a></li>';
                    echo '
                    <li class="page-item '.$active.'"><a class="page-link" href="?modulo=devoluciones&accion=index&page='.($pageres-1).'">'.($pageres-1).'</a></li>';
                  }
                  echo '<li class="page-item">
                    <a class="page-link" href="?modulo=devoluciones&accion=index&page='.($page+1).'" aria-label="Next">
                      <span aria-hidden="true">Sig &raquo;</span>
                      <span class="sr-only">Siguiente</span>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        '; 
    }
    function show(){
      echo '
      <script>
        function delprod(idprod){
          var conf = confirm("¿Deseas quitar el producto de la devolución?");
          if(conf == true){
            window.location.href="?modulo=devoluciones&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
          }
        }
        function cancelar(){
          var conf = confirm("¿Estas seguro que deseas cancelar la devolución actual?");
          if(conf == true){
            window.location.href="?modulo=devoluciones&accion=cancelar&id='.$this->model->id.'&remision='.$this->model->remision.'";
          }
        }
        function updatemotivo(){
          console.log("prueba act");
          var motivo = document.getElementById("motivo").value;
          if(motivo.length > 0){
            document.formmotivo.submit();
          }else $("#motivo").css("border","solid 3px red");
        }
        function quitar(){
          var motivo = document.getElementById("motivo").value;
          $("#motivo").css("border","");
          $("#motivorel").val(motivo);
          console.log("rel: "+motivo);
        }
      </script>';

      $readmot = '';
      if($this->model->estatus != "N") $readmot = 'readonly';
      echo '
      <div class="row page-title-actions">
        <div class="col-12 col-lg-8 col-md-12">
          <a href="?modulo=devoluciones&accion=index" accesskey="">
            <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras </button>
          </a>';
          if($this->model->estatus == "N"){
            echo '<!--<a accesskey="B" href="popup/importarprodsdev.php?remision='.$this->model->remision.'&id='.$this->model->id.'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
              <button type="button" class="btn btn-primary">
                <span class="glyphicon glyphicon-cog"></span><i class="fa fa-pen"></i> Modificar
              </button>
            </a>-->
            <a accesskey="B" href="popup/importarprodsdev.php?remision='.$this->model->remision.'&id='.$this->model->id.'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
              <button type="button" class="btn btn-secondary">
                <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus4"></i>Agregar producto
              </button>
            </a>
            <button class="btn btn-danger" onclick="cancelar();">
              <i class="fa fa-times"></i> Cancelar
            </button>';
          }
          echo '
        </div>';
        $sqlcliente = 'SELECT CONCAT(c_nmb," ",c_apellidos) FROM crm_clientes INNER JOIN crm_tableros ON ct_cliente = c_id
                        INNER JOIN remisiones_devoluciones ON rd_tablero = ct_id WHERE rd_tablero = "'.$this->model->tablero.'"';
        $resultcl = setq($sqlcliente);
        list($clientedev) = $resultcl->fetch_array();
        echo '
        <div class="col-6 card p-1 col-lg-4 col-md-12 bg-grey bg-lighten-2">
          '.$this->model->folio.'
        </div>
        <div class="col-md-6 card p-1 bg-grey bg-lighten-2">
        Remisión: '.busca($this->model->remision,'remisiones','r_id','CONCAT(r_folio," - ",r_nmb)').'
      </div>
      <div class="col-md-6 card p-1 bg-blue-grey bg-lighten-4">Cliente: '.$clientedev.'</div>
      </div>';
      if($this->model->estatus == "F") $title = 'Motivo de devolución';
      else $title = 'Captura de productos';
      echo '<div class="row bg-grey bg-lighten-2">
        <div class="alert alert-primary h6 col-12 col-md-12"><center><b>'.$title.'</b></center></div>';
            if($this->model->estatus == "A"){
              echo'<div class="col-12 col-md-12">
                <div class="mb-5">
                  <textarea rows="3" placeholder="Describe el motivo de la devolución de los productos" class="form-control" '.$readmot.'>'.$this->model->motivo.'</textarea>
                </div>
              </div>';
            }
          echo '<form method="post" action="?modulo=devoluciones&accion=update&id='.$this->model->id.'&remision='.$this->model->remision.'" id="devapl">
            <input type="hidden" name="motivorel" id="motivorel" value="'.$this->model->motivo.'"/>
            <div class="table table-responsive table-hover">
              <table class="table table-hover table-striped">
                <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
                  <tr>
                    <th>Modelo</th>
                    <th>Artículo</th>
                    <th>Piezas a devolver</th>
                    <th>Precio unitario</th>
                    <th>Importe total</th>
                    <th></th>
                  </tr>
                </thead>';
                $totalf = 0;
                $subtotal = 0;
                $iva = 0;
                $descuento = 0;
                $descuentot = 0;
                $subtotalfin = 0;
                $totiva = 0;
                if($this->model->estatus != "N") $readon = 'readonly';
                else $readon = 'readonly';
                $viva = busca(1,'configuracionesp','c_id','c_iva');
                while($row = $this->model->resultdd->fetch_array()){
                  //$importe = $row['rdd_precio']*$row['rdd_cantidad'];
                  //$subtotal += $importe;

                  if($row['rdd_iva'] == "1"){
                    $precio = $row['rdd_precio'];
                    $importe = $precio*$row['rdd_cantidad'];
                    $descuni = $importe*($row['rdd_descuento']/100);
                    $impmd = $importe-$descuni;
                    $descuento=$descuni;
                    $iva=$impmd*($viva/100);
                    $subtotal+=$impmd;
                  }elseif($row['rdd_iva'] == "0"){
                    $precio = $row['rdd_precio'];
                    $importe = $precio*$row['rdd_cantidad'];
                    $iva= 0;
                    $descuento= $importe*($row['rdd_descuento']/100);
                    $subtotal+=($importe-$descuento);
                  }

                  $descuentot+=$descuento;
                  $subtotalfin+= $importe;
                  $totiva+=$iva;

                  if($row['rdd_articulo'] != 0) $modelo = busca($row['rdd_articulo'],'articulos','a_id','a_modelo'); else $modelo = "-";
                  $maxc = busca($row['rdd_articulo'],'remisionesd','rd_remision = "'.$this->model->remision.'" AND rd_articulo','rd_cantidad');
                  echo '
                  <tr>
                    <td>'.$modelo.'</td>
                    <td>'.$row['rdd_nmbarticulo'].'</td>
                    <td>
                      <input type="number" value="'.number_format($row['rdd_cantidad'],2,'.','').'" min="1" max="'.$maxc.'" step="0.01" name="cantidad'.$row['rdd_id'].'" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                    </td>
                    <td>
                      <input type="number" value="'.number_format($row['rdd_precio'],2,'.','').'" min="0" max="9999999" step="0.01" name="importe'.$row['rdd_id'].'" placeholder="Monto unitario" class="form-control number-align" required="required" readonly onfocus="this.select();" />
                    </td>
                    <td class="number-align ">
                      $ '.number_format($importe,2,'.',',').'
                    </td>
                    <td>
                      <center>';
                        if($this->model->estatus == "N")
                        echo '<button type="button" class="btn btn-danger" onclick="delprod('.$row['rdd_id'].')"><i class="fa fa-trash"></i> </button>
                      </center>
                    </td>
                  </tr>';
                }
                //$totalf = $subtotal + $iva - $descuento;
                $totalf = $subtotal + $totiva;
                echo '
                <tfoot>';
                  $hdd = 0; $hdi = 0;
                  if($iva > 0 && $descuento > 0){
                    $bgd = 'bg-lighten-3';
                    $bgi = 'bg-lighten-2';
                  }else if($iva > 0){
                    $bgi = 'bg-lighten-3';
                    $hdd = 1;
                  }else if($descuento > 0){
                    $bgd = 'bg-lighten-3';
                    $hdi = 1;
                  }
                  if($iva > 0 || $descuento > 0){  
                    echo';<tr class="p-1 bg-grey bg-lighten-2">
                      <td colspan="3">&nbsp;</td>
                      <td colspan="2"  class="number-align h4">Subtotal</td>
                      <td  class="number-align h4">$'.number_format($subtotalfin,2).'</td>
                    </tr>';
                    if($hdd == 0)
                      echo'<tr class="p-1 bg-grey '.$bgd.'">
                        <td colspan="3">&nbsp;</td>
                        <td colspan="2"  class="number-align h4">Descuento</td>
                        <td  class="number-align h4">$'.number_format($descuentot,2).'</td>
                      </tr>';
                    if($hdi == 0)
                      echo'<tr class="p-1 bg-grey '.$bgi.'">
                        <td colspan="3">&nbsp;</td>
                        <td colspan="2"  class="number-align h4">IVA</td>
                        <td  class="number-align h4">$'.number_format($totiva,2).'</td>
                      </tr>';
                  }
                  echo'
                  <tr class="p-1 bg-grey bg-lighten-3">
                    <td colspan="3">&nbsp;</td>
                    <td colspan="2"  class="number-align h4">Total</td>
                    <td  class="number-align h4">$'.number_format($totalf,2).'</td>
                  </tr>
                </tfoot>
              </table>
            </div>
            <center>';
            if($this->model->estatus == "N"){
              echo'<a data-fancybox data-type="ajax" data-src="popup/setdevoluciones.php?id='.$this->model->id.'&impd='.$totalf.'&rand='.rand(1,1000).'" href="javascript:;" >
                <button class="btn btn-success ml-1" ><i class="fa fa-check"></i> Aplicar</button>
              </a>';
            }
            echo'
            </center>
          </form>
        </div>
      </div>';
    }
  }
?>