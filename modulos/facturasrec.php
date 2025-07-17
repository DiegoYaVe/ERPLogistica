<?php
  //ini_set('display_errors', 1);
  $GLOBALS['menu'] = "Facturación";
  class Facturasrec{
    function __construct(){ // constructor
      $this->model= new ModelFacturasrec; // crea modelo
    }
    function index(){
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-').'01';
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if(!isset($_REQUEST['proveedor'])) $_REQUEST['proveedor'] = NULL;
      if(!isset($_REQUEST['fpago'])) $_REQUEST['fpago'] = NULL;

      $this->model->resultfac(trim($_REQUEST['proveedor']),$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['page'],$_REQUEST['estatus'],$_REQUEST['fpago']);
      $this->view = new  ViewFacturasrec($this->model);
      $this->view->browsefac($_REQUEST['proveedor'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['page'],$_REQUEST['estatus'],$_REQUEST['fpago']);
    }
    function cargar(){
      $this->view = new ViewFacturasrec($this->model);
      if(busca("N",'facturasrec_carga','fc_estatus','COUNT(*)') > 0){
        $carga = busca("N",'facturasrec_carga','fc_estatus','fc_id');
        $this->model->resultfaccarga($carga);
        $this->view->showcarga($carga);
        //echo alert("Tienes una carga de facturas pendiente");
      }else
        $this->view->cargar();
    }
    function setcarga(){
      /* if (!is_dir('cfd')) {
        @mkdir('cfd', 0777);
      }
      if (!is_dir('cfd/'.$_POST['empresa'])) {
        @mkdir('cfd/'.$_POST['empresa'], 0777);
      }
      if (!is_dir('cfd/'.$_POST['empresa'].'/tmprec')) {
        @mkdir('cfd/'.$_POST['empresa'].'/tmprec', 0777);
      } */

      //$directorio = 'cfd/'.$_POST['empresa'].'/tmprec';
      /* $directorio = 'sat-connect/SAT';
      $existosos = 0;
      $sqlfc = 'SELECT MAX(fc_id) FROM facturasrec_carga';
      $resultfc = setq($sqlfc) or die($sqlfc);
      list($idfc) = $resultfc->fetch_array();
      $idfc++; */
      /* //Como el elemento es un arreglos utilizamos foreach para extraer todos los valores
      foreach($_FILES["archivo"]['tmp_name'] as $key => $tmp_name){
        //Validamos que el archivo exista
        if($_FILES["archivo"]["name"][$key]) {
  	      $filename = $_FILES["archivo"]["name"][$key]; //Obtenemos el nombre original del archivo
  	      $source = $_FILES["archivo"]["tmp_name"][$key]; //Obtenemos un nombre temporal del archivo
  	      //Validamos si la ruta de destino existe, en caso de no existir la creamos
  	      if(!file_exists($directorio)){
  		      mkdir($directorio, 0777) or die("No se puede crear el directorio de extracci&oacute;n");
  	      }
          $sql = 'SELECT MAX(fr_id) FROM facturasrec';
          $result = setq($sql) or die($sql);
          list($idfac) = $result->fetch_array();
          $idfac++;

  	      $dir=opendir($directorio); //Abrimos el directorio de destino
  	      $target_path = $directorio.'/'.$idfac.'.xml'; //Indicamos la ruta de destino, así como el nombre del archivo
  	      //Movemos y validamos que el archivo se haya cargado correctamente
  	      //El primer campo es el origen y el segundo el destino
  	      if(move_uploaded_file($source, $target_path)) {
            $cargaxml = $this->model->leerxml($idfac,$_POST['empresa'],$idfc);
            $existosos++;
  	      }else{
            $handle=fopen('cfd/'.$_POST['empresa'].'/tmprec/errores.txt','a');
            fwrite($handle, $filename.'\n');
            fclose($handle);
          }
  	      closedir($dir); //Cerramos el directorio de destino
        }
      } */

      $directorio = 'sat-connect/SAT';
      $existosos = 0;
      $sqlfc = 'SELECT MAX(fc_id) FROM facturasrec_carga';
      $resultfc = setq($sqlfc) or die($sqlfc);
      list($idfc) = $resultfc->fetch_array();
      $idfc++;
      $directorio2 = 'cfd/1/tmprec';
      $arrFiles = scandir($directorio);
      //var_dump($arrFiles);
      for($i = 0; $i <= count($arrFiles); $i++){
        $sql = 'SELECT MAX(fr_id) FROM facturasrec';
        $result = setq($sql) or die($sql);
        list($idfac) = $result->fetch_array();
        $idfac++;
        $info = new SplFileInfo($arrFiles[$i]);
        if(!is_dir($arrFiles[$i]) && $info->getExtension() == "xml"){
          $target_path = $directorio2.'/'.$idfac.'.xml';
          $original = $directorio.'/'.$arrFiles[$i];
          if(copy($original, $target_path)) {
            $cargaxml = $this->model->leerxml($idfac,'1',$idfc);
            $existosos++;
            unlink($original);
  	      }else{
            $handle=fopen('cfd/1/tmprec/errores.txt','a');
            fwrite($handle, $arrFiles[$i].'\n');
            fclose($handle);
          }
        }
      }
      
      if($existosos > 0){
        $sql = 'INSERT INTO facturasrec_carga SET
                fc_id = "'.$idfc.'",
                fc_fecha = "'.date('Y-m-d H:i:s').'",
                fc_usuario = "'.$_SESSION['uid'].'",
                fc_estatus = "N"';
        setq($sql) or die($sql);
      }

      redirect("?modulo=facturasrec&accion=showcarga&id=".$idfc.'&exitosos='.$existosos);
    }
    function showcarga(){
      $this->model->resultfaccarga($_GET['id']);
      $this->view = new ViewFacturasrec($this->model);
      $this->view->showcarga($_GET['id']);
    }
    function fincarga(){
      $this->model->setproveedores($_GET['id']);
      $this->model->fincarga($_GET['id']);
    
      redirect("?modulo=facturasrec&accion=index&id=".$_GET['id']);
    }
    function delcarga(){
      $this->model->unfiles($_GET['id']);
      $this->model->delcarga($_GET['id']);

      redirect("?modulo=facturasrec&accion=index&id=".$_GET['id']);
    }
    function showfactura(){
      $this->model->selectfac($_GET['id']);
      $this->view = new ViewFacturasrec($this->model);
      $this->view->showfactura();
    }
    function acreditar(){
      $this->model->selectfac($_GET['id']);
      $this->view = new ViewFacturasrec($this->model);
      $this->view->acreditar();
    }
    function insertfaceg(){
      $fechaf = busca($_GET['fac'],'facturasrec','fr_id','fr_fecha');

      $sqlme = 'SELECT fe_id FROM facturarec_egreso WHERE fe_factura = "'.$_GET['fac'].'"';
      $resultme = setq($sqlme) or die($sqlme);
      list($me) = $resultme->fetch_array();
      $me++;

      $sql = 'INSERT INTO facturarec_egreso SET
              fe_id = "'.$me.'",
              fe_factura = "'.$_GET['fac'].'",
              fe_fechafac = "'.$fechaf.'",
              fe_monto = "'.$_POST['importe'].'",
              fe_ivac = "'.$_POST['importeiva'].'",
              fe_observaciones = "'.$_POST['obs'].'",
              fe_categoria = "'.$_POST['categoria'].'"';
      setq($sql) or die($sql);

      redirect("?modulo=facturasrec&accion=acreditar&id=".$_GET['fac']);
    }
    function finalizareg(){
      $sql = 'SELECT DISTINCT(fe_egreso) FROM facturarec_egreso WHERE fe_factura = "'.$_GET['id'].'"';
      $result = setq($sql) or die($sql);
      while($row = $result->fetch_array()){
        $importe = busca($row['fe_egreso'],'egresos','e_id','e_monto');
        $importefac = busca($row['fe_egreso'],'facturarec_egreso','fe_egreso','SUM(fe_monto)');
        if($importe == $importefac){
          $sqles = 'UPDATE egresos SET e_estatus = "X" WHERe e_id = "'.$row['fe_egreso'].'"';
          setq($sqles) or die($sqles);
        }
      }

      $sql = 'UPDATE facturasrec SET fr_estatus = "F" WHERE fr_id = "'.$_GET['id'].'"';
      setq($sql) or die($sql);

      redirect("?modulo=facturasrec&accion=index");
    }
    function setegresos(){
      $sql = 'SELECT * FROM egresos WHERE e_estatus IN ("A","F") AND e_proveedor = "'.$_POST['proveedor'].'"
              ORDER BY e_fgen DESC';
      $result = setq($sql) or die($sql);
      while($row = $result->fetch_array()){
        if(isset($_POST['combo'.$row['e_id']])){
          $fechaf = busca($_GET['id'],'facturasrec','fr_id','fr_fecha');
          $sqlme = 'SELECT fe_id FROM facturarec_egreso WHERE fe_factura = "'.$_GET['id'].'"';
          $resultme = setq($sqlme) or die($sqlme);
          list($me) = $resultme->fetch_array();
          $me++;

          $sql = 'INSERT INTO facturarec_egreso SET
                  fe_id = "'.$me.'",
                  fe_egreso = "'.$row['e_id'].'",
                  fe_factura = "'.$_GET['id'].'",
                  fe_fechafac = "'.$fechaf.'",
                  fe_monto = "'.$_POST['monto'.$row['e_id']].'",
                  fe_ivac = "'.$_POST['importeiva'.$row['e_id']].'",
                  fe_categoria = "'.$_POST['categoria'].'"';
          setq($sql) or die($sql);
        }
      }
      echo '<script>
        window.opener.location.reload();
        window.close();
      </script>';
    }
    function setconcepto(){
      $sql = 'UPDATE facturarec_egreso SET fe_categoria = "'.$_POST['categoria'].'"
              WHERE fe_factura = "'.$_GET['factura'].'" AND fe_id = "'.$_GET['id'].'"';
      setq($sql) or die($sql);

      redirect("?modulo=facturasrec&accion=acreditar&id=".$_GET['factura']);
    }
    function setobservaciones(){
      $simbol  = array('"',"'");
      $simboldi  = array('');

      $observaciones = str_replace($simbol,$simboldi,mb_strtoupper(trim($_POST['Observaciones'])));
      $sql = 'UPDATE facturarec_egreso SET fe_observaciones = "'.$observaciones.'"
              WHERE fe_factura = "'.$_GET['factura'].'" AND fe_id = "'.$_GET['id'].'"';
      setq($sql) or die($sql);

      redirect("?modulo=facturasrec&accion=acreditar&id=".$_GET['factura']);
    }
    function delfaceg(){
      $sql = 'DELETE FROM facturarec_egreso
              WHERE fe_factura = "'.$_GET['fac'].'" AND fe_id = "'.$_GET['id'].'"';
      setq($sql) or die($sql);

      redirect("?modulo=facturasrec&accion=acreditar&id=".$_GET['fac']);
    }
  }

  class ModelFacturasrec{
    function resultcarga($proveedor,$fini,$ffin,$page){
      $pageres = $page*50;
      $sql = 'SELECT * FROM facturasrec_carga WHERE fc_fecha BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      if($proveedor) $sql.= ' AND fc_id IN (SELECT DISTINCT(fr_carga) WHERE fr_nmbproveedor LIKE "%'.$proveedor.'%") ';
      $sql.=' ORDER BY fc_id DESC LIMIT '.$pageres.',50';
      $this->result = setq($sql) or die($sql);
    }
    function resultfac($proveedor,$fini,$ffin,$page,$estatus,$fpago){
      $pageres = $page*1000;
      $sql = 'SELECT * FROM facturasrec WHERE DATE(fr_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND fr_empresa = "'.$_SESSION['emp'].'" ';
      //$sql = 'SELECT * FROM facturasrec WHERE DATE(fr_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"  ';
      if($proveedor) $sql.= ' AND fr_nmbproveedor LIKE "%'.$proveedor.'%"'; //$sql.= 'AND fr_proveedor IN (SELECT p_id FROM proveedores WHERE p_nmb LIKE "%'.mb_strtoupper($proveedor).'%")';
      if($estatus) $sql.= 'AND fr_estatus = "'.$estatus.'"';
      if($fpago) $sql .= ' AND fr_fpago = "'.$fpago.'" ';
      $sql.=' ORDER BY fr_fecha DESC LIMIT '.$pageres.',1000';
      $this->result = setq($sql) or die($sql.' '.mysql_error());
    }
    function resultfacrd($id){
      $sql = 'SELECT * FROM facturasrecd  WHERE rd_factura = "'.$id.'" ORDER BY rd_id';
      $this->resultcd = setq($sql) or die($sql);
    }
    function resultfaccarga($carga){
      $sql = 'SELECT * FROM facturasrec WHERE fr_carga = "'.$carga.'"
              ORDER BY fr_id ASC';
      $this->resultc = setq($sql) or die($sql);
    }
    function setproveedores($carga){
      $this->resultfaccarga($carga);
      while($row = $this->resultc->fetch_array()){
        /*
          $sql = 'SELECT pf_proveedor FROM proveedor_fiscales WHERE pf_rfc = "'.$row['fr_rfcproveedor'].'"';
          $resultpr = setq($sql) or die($sql);
          list($idprov) = mysql_fetch_array($resultpr);

          $sqlu = 'UPDATE facturasrec SET fr_proveedor = "'.$idprov.'" WHERE fr_id = "'.$row['fr_id'].'"';
          setq($sqlu) or die($sqlu);
        */
        if(copy('cfd/1/tmprec/'.$row['fr_id'].'.xml', 'cfd/1/recibidas/'.$row['fr_id'].'.xml')){
          unlink('cfd/1/tmprec/'.$row['fr_id'].'.xml');
        }
      }
    }
    function unfiles($carga){
      $this->resultfaccarga($carga);
      while($row = $this->resultc->fetch_array()){
        unlink('cfd/1/tmprec/'.$row['fr_id'].'.xml');
      }
    }
    function fincarga($id){
      $sql = 'UPDATE facturasrec_carga SET fc_estatus = "A" WHERE fc_id = "'.$id.'"';
      setq($sql) or die($sql);

      $sqlc = 'UPDATE facturasrec SET fr_estatus = "A" WHERE fr_carga = "'.$id.'"';
      setq($sqlc) or die($sqlc);
    }
    function delcarga($id){
      $this->resultfaccarga($id);
      while($row = $this->resultc->fetch_array()){
        $sqlrd = 'DELETE FROM facturasrecd WHERE rd_factura = "'.$id.'"';
        setq($sqlrd) or die($sqlrd);

        $sqlrim = 'DELETE FROM facturasrec_impuestos WHERE fi_facturas = "'.$id.'"';
        setq($sqlrim) or die($sqlrim);

        $sqlrr = 'DELETE FROM facturasrec_relacion WHERE frr_factura = "'.$id.'"';
        setq($sqlrr) or die($sqlrr);

        $sqlrti = 'DELETE FROM facturasrec_totimp WHERE fti_factura = "'.$id.'"';
        setq($sqlrti) or die($sqlrti);
      }

      $sql = 'DELETE FROM facturasrec WHERE fr_carga = "'.$id.'"';
      setq($sql) or die($sql);

      $sqlc = 'UPDATE facturasrec_carga SET fc_estatus = "C" WHERE fc_id = "'.$id.'"';
      setq($sqlc) or die($sqlc);
    }
    function selectfac($id){
      $sql = 'SELECT * FROM facturasrec WHERE fr_id = "'.$id.'"';
      $result = setq($sql) or die($sql);
      $row = $result->fetch_array();
      $this->id = $row['fr_id'];
      $this->proveedor = $row['fr_proveedor'];
      $this->rfcproveedor = $row['fr_rfcproveedor'];
      $this->nmbproveedor = $row['fr_nmbproveedor'];
      $this->rfcreceptor = $row['fr_rfcreceptor'];
      $this->usocfdi = $row['fr_usocfdi'];
      $this->fecha = $row['fr_fecha'];
      $this->serie = $row['fr_serie'];
      $this->folio = $row['fr_folio'];
      $this->fpago = $row['fr_fpago'];
      $this->certificado = $row['fr_certificado'];
      $this->condiciones = $row['fr_condiciones'];
      $this->subtotal = $row['fr_subtotal'];
      $this->descuento = $row['fr_descuento'];
      $this->moneda = $row['fr_moneda'];
      $this->tipocambio = $row['fr_tipocambio'];
      $this->total = $row['fr_total'];
      $this->tipoc = $row['fr_tipoc'];
      $this->metodop = $row['fr_metodop'];
      $this->lugarexp = $row['fr_lugarexp'];
      $this->estatus = $row['fr_estatus'];
      $this->uuid = $row['fr_uuid'];
      $this->fechatim = $row['fr_fechatim'];
      $this->provcerv = $row['fr_provcerv'];
      $this->nocertisat = $row['fr_nocertisat'];
      $this->carga = $row['fr_carga'];
      $this->falta = $row['fr_falta'];
      $this->ualta = $row['fr_ualta'];
    }
    ///insert into facturasrec
    function leerxml($idfac,$empresa,$idfc){
      /*include_once('leerxml.php');
      $cfdi = new LeerCFDI();*/

      $directorio = 'cfd/'.$empresa.'/tmprec/';
      $archivoXML = $directorio.$idfac.'.xml';

      if(file_exists($archivoXML)){
        xmlFactura("R",$archivoXML,$idfac,$empresa,$idfc);
        /*$cfdi->cargaXml($archivoXML);
        $existe = $cfdi->exists($archivoXML);
        if($existe == 0){
          $cfdi->recorrer($idfac,$empresa,$idfc);
          if($idfac)
            $folio = $cfdi->uuid($idfac);
        }*/
        /*
          else{
            die("factura previamente cargada");
          }
        */
      }
    }
  }

  class ViewFacturasrec {
    function __construct($model) {
      $this->model = $model;
      $this->estatus = array("N" => "Pendiente de autorización", "A" => "Autorizada", "C" => "Cancelada");
      $this->tipocambio = array("MXN" => "Pesos mexicanos", "USD" => "Dolares americanos");
      $this->metodopago = array("PUE" => "Pago en una sola exhibición", "PPD" => "Pago en parcialidades", "NO IDENTIFICADO" => "No Identificado", "" => "No Aplica");
      $this->tipometodopago = array("PUE","PPD");
      $this->meses = array("1"=>"Ene","2"=>"Feb","3"=>"Mar","4"=>"Abr","5"=>"May","6"=>"Jun","7"=>"Jul","8"=>"Ago","9"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dic");
    }
    function browsefac($proveedor,$fini,$ffin,$page,$estatus,$fpago){
        //Sección: E2 Encabezado - Filtros
        $sela = '';
        $seln = '';
        $selp = '';
        $selc = '';
        if($estatus == "N") $seln = 'selected';
        elseif($estatus == "A") $selp = 'selected';
        elseif($estatus == "C") $selc = 'selected';
        else $sela = 'selected';
        ?>
        <script>
          $('body').on("keydown", function(e) { 
            if (e.altKey && e.which === 78) {
              var btn = document.getElementById('nuevo');
              btn.click();
              e.preventDefault();
            }
          });
          $('body').on("keydown", function(e) { 
            if (e.altKey && e.which === 76) {
              var btn = document.getElementById('filtrar');
              btn.click();
              $("#proveedor").focus();
              e.preventDefault();
            }
          });
          $('body').on("keydown", function(e) { 
            if (e.altKey && e.which === 82) {
              window.location.reload();
            }
          });
        </script>
        <?php
        $nuevo = '<a id="nuevo" href="?modulo=facturasrec&accion=cargar" >
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>';
        $filtro = '
              <form class="form-inline" role="form" method="post" action="?modulo=facturasrec&accion=index">
                <div class="mb-5 mr-1">
                  <label for="tipom">Proveedor:</label>
                  <input type="text" class="form-control" id="proveedor" name="proveedor" placeholder="Nombre del proveedor que buscas" onfocus="this.select();" value="'.$proveedor .'" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Fecha Inicial:</label>
                  <input type="date" class="form-control" name="fini" placeholder="Fecha inicial" onfocus="this.select();" value="'.$fini .'" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Fecha Final:</label>
                  <input type="date" class="form-control" name="ffin" placeholder="Fecha final" onfocus="this.select();" value="'.$ffin .'" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Estatus:</label>
                  <select class="form-control" name="estatus" id="estatus" >
                    <option value="" '.$sela .' >Todos</option>
                    <option value="N" '.$seln .' >Proceso de Captura</option>
                    <option value="A" '.$selp .' >Aplicado</option>
                    <option value="C" '.$selc .' >Cancelados</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Forma de Pago</label>
                  <select name="fpago" class="form-control" id="">
                    <option value="">TODAS</option>';
                    $sqlfpago = "SELECT * FROM cfdi_fpago WHERE cf_estatus = 'A'";
                    $resultfpago = setq($sqlfpago);
                    while($row = $resultfpago->fetch_array()){
                      if($fpago == $row['cf_nmb']) $selected = 'selected';
                      else $selected = '';
                      $filtro .= '<option value="'.$row['cf_nmb'].'" '.$selected.'>'.$row['cf_descripcion'].'</option>';
                    };
                    $filtro .= '</select>
                </div>
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
                  </button>
                  <a href="?modulo=facturasrec&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times"></i> Limpiar
                  </button></a>
                </div>
              </form>';
                    
          toolbar('FACTURAS RECIBIDAS', '', $filtro, $nuevo);
          
          if(busca("N",'facturasrec_carga','fc_estatus','COUNT(*)') > 0){
            $idc = busca("N",'facturasrec_carga','fc_estatus','fc_id');
              echo '<span style="font-size:14px;font-weight:bolder;">Tienes una carga de facturas en proceso, da
              <span style="color: #CC0000;"><a href="?modulo=facturasrec&accion=showcarga&id='.$idc.'">Click aquí</a></span>
              para finalizarla</span>';
          }
          echo '<div class="table-responsive">
            <table class="table table-striped" id="myTable">
              <thead class="thead pt-1 pb-1" style="border-collapse:collapse;" >';
                echo '<tr>
                  <th width="8%">Folio</th>
                  <th width="12%">RFC Proveedor</th>
                  <th width="25%">Razón Social</th>
                  <th width="12%">Fecha</th>
                  <th width="10%">Estatus</th>
                  <th width="10%">UUID</th>
                  <th width="7%">Descargar</th>
                  <th width="10%">Total</th>
                  <td></td>
                  <th></th>
                </tr>
              </thead><tbody>';
              $costot = 0;
              while($row = $this->model->result->fetch_array()){
                /*
                  $acreditado = busca($row['fr_id'],'facturarec_egreso','fe_factura','SUM(fe_monto)');
                  if(!$acreditado) $acreditado = 0;
                */
                if($row['fr_estatus'] == "N") {
                  $estatus = "Pendiente de autorización";
                  $colorf = "#FFDC52";
                  if($row['fr_folio']) $divid = $row['fr_folio'];
                  else $divid = "SIN FOLIO";
                }elseif($row['fr_estatus'] == "A") { $estatus = "Autorizada"; $colorf = "#84E184";  $divid = $row['fr_folio'];}
                elseif($row['fr_estatus'] == "C") { $estatus = "Cancelada"; $colorf = "#FF4D4D";  $divid = $row['fr_folio'];}
                //<a target="_BLANK" href="?modulo=facturasrec&accion=showfactura&factura='.$row['fr_id'].'">
                echo '<tr>
                  <th class="text-medium">
                    '.$divid.'
                  </th>';
                  echo '<td class="text-medium">'.$row['fr_rfcproveedor'].'</td>
                  <td class="text-medium">'.$row['fr_nmbproveedor'].'</td>
                  <td class="text-medium">'.fecha_formato($row['fr_fecha'],true,true).'</td>
                  <td class="text-medium" style="background:'.$colorf.';">'.$estatus.'</td>
                  <td class="text-medium">'.$row['fr_uuid'].'</td>';
                  if($row['fr_estatus'] == "A" || $row['fr_estatus'] == "F"){
                    echo '<td>
                      <a target="_BLANK" href="cfd/1/tmprec/'.$row['fr_id'].'.xml">
                        <button class="btn btn-secondary"><i class="icon-code2"></i></button> 
                      </a>
                    </td>';
                  }else{
                    echo '<td>&nbsp;</td>';
                  }
                  echo '<td class="text-medium number-align">'.number_format(($row['fr_total']),2).'</td>';
                  /*
                    echo '<td>'.number_format($acreditado,2).'</td>';
                    if($row['fr_estatus'] == "A")
                      echo '<td>
                        <a href="?modulo=facturasrec&accion=acreditar&id='.$row['fr_id'].'">
                          <input type="button" class="botont" id="recibir" />
                        </a>
                      </td>';
                    if($row['fr_estatus'] == "F")
                      echo '<td>
                        <a href="?modulo=facturasrec&accion=acreditar&id='.$row['fr_id'].'">
                          <input type="button" class="botont" id="mostrar" />
                        </a>
                      </td>';
                  */
                  echo '<td>
                    <a target="_BLANK" href="?modulo=facturasrec&accion=showfactura&id='.$row['fr_id'].'">
                      <button class="btn btn-primary"><i class="icon-angle-right"></i></button> 
                    </a>
                  </td>
                </tr>';
                $costot+=$row['fr_total'];
              }
              echo '</tbody><tfoot><tr>
                <td colspan="6"></td>
                <td>Total</td>
                <td>'.number_format($costot,2).'</td>
              </tr>';
            echo '</tfoot><table>
          </div>
        </div>
      </div>
      
      <script>
      $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          responsivePriority: 1,
          pageLength:50,
      });
    </script>';
    }
    function cargar(){
      $empresa = $_SESSION['emp'];
      /* echo '<center>
        <form action="?modulo=facturasrec&accion=setcarga" method="post" enctype="multipart/form-data">
          <input type="hidden" name="empresa" value="'.$empresa.'" />
          <table class="table table-hover table-striped" width="50%">
            <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
              <tr>
                <th colspan="3">Elegir archivos XML para cargar</th>
              </tr>
            </thead>';
            echo '<tr>
              <td>Seleccionar Archivos</td>
              <td>
                <input type="file" class="form-control" id="archivo[]" name="archivo[]" accept=".xml" required multiple >
              </td>
              <td>
                <button type="submit" class="btn btn-success" id="adjuntar" value="" />
                  <i class="fa fa-paper-plane-o"></i> Subir facturas
                </button>
              </td>
            </tr>
          </table>';
        echo '</form>
      </center>'; */
      $atras = ' <a href="?modulo=facturasrec&accion=index" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras</a>';
      
      toolbar('FACTURAS RECIBIDAS', $atras);
      echo ' <div class="card mt-5">
        <div class="card-header">
          <h3 class="card-title">Descargar Facturas Recibidas</h3>
        </div>
        <form action="sat-connect/recibidas.php" method="POST" onsubmit="$(\'#envio\').prop(\'disabled\',true)" class="">
        <div class="p-2 row">
          <div class="col-md-4">
            <label for="" class="">Desde:</label>
            <input type="date"  name="fini" value="'.date('Y-m-01').'" max="'.date('Y-m-d').'" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="" class="">Hasta:</label>
            <input type="date" name="ffin" value="'.date('Y-m-d').'" max="'.date('Y-m-d').'" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="" class="">Descargar:</label><br>
            <button type="submit" class="btn btn-success" id="envio"><i class="fas fa-file-download"></i> Descargar Recibidas</button>
          </div>
        </div>
        </form>
      </div>
      ';
    }
    function showcarga($id){
      //Sección: E1 Encabezado - Botones de acción
      echo '<div class="row page-title-actions"><div class="col-md-12">';
        echo '<a href="?modulo=facturasrec&accion=index">
          <button class="btn btn-warning"><i class="fa fa-arrow-left"></i>Atrás</button>
        </a>';
        $estatuscarga = busca($id,'facturasrec_carga','fc_id','fc_estatus');
        echo '<script>
          function fincarga(){
            var conf = confirm("¿Deseas finalizar la captura de facturas?,\nEste proceso es irreversible");
            if(conf == true){
              document.getElementById("finalizar").disabled = true;
              document.location.href="?modulo=facturasrec&accion=fincarga&id='.$id.'";
            }
          }
          function delcarga(){
            var conf = confirm("¿Deseas Cancelar la carga actual?,\nNingún cambio se conservará");
            if(conf == true){
              document.getElementById("cancelar").disabled = true;
              document.location.href="?modulo=facturasrec&accion=delcarga&id='.$id.'";
            }
          }
        </script>';
        echo '<button type="button" style="display:none;" class="btn bg-grey bg-darken-3 text-white" id="finalizar" onclick="fincarga();" />
          <i class="icon-flag-checkered"></i> Finalizar Carga
        </button>';
        if($estatuscarga == "N"){
          echo '<button type="button" style="display:none;" class="btn btn-danger" id="delete" onclick="delcarga();">
            <i class="icon-remove"></i> Finalizar Carga
          </button>';
        }
        echo '<div class="main-card mb-3 card">
          <div class="card-body row">
            <div class="table-responsive">
              <table class="table table-hover table-striped" >
                <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
                  <tr>
                    <th colspan="13">Resumen de carga</th>
                  </tr>
                  <tr>
                    <th></th>
                    <th>Serie</th>
                    <th>Proveedor</th>
                    <th>Fecha </th>
                    <th>Forma de pago</th>
                    <th>Método de pago</th>
                    <th>Subtotal</th>
                    <th>Desc.</th>
                    <th>Tras.</th>
                    <th>Ret.</th>
                    <th>Total</th>
                    <th>UUID</th>
                    <th>Documento</th>
                  </tr>
                </thead>';
                $ok = 1;
                $i = 0;
                $disp = "";
                $subtotal = 0;
                $tottras = 0;
                $totdesc = 0;
                $totret = 0;
                $totalf = 0;
                while($row = $this->model->resultc->fetch_array()){
                  $i++;
                  $okind = "SI";
                  $fechatim = explode(" ",str_replace("T"," ",$row['fr_fecha']));
                  $traslados = busca($row['fr_id'],'facturasrec_totimp','fti_tipo = "T" AND fti_factura','SUM(fti_importe)');
                  if(!$traslados) $traslados = 0;
                  $colpr = "";
                  $retenciones = busca($row['fr_id'],'facturasrec_totimp','fti_tipo = "R" AND fti_factura','SUM(fti_importe)');
                  if(!$retenciones) $retenciones = 0;
                  //*****************INicio proceso de validación de facturas*************************************/
                  //PRoveedor
                  /*
                    if(busca($row['fr_rfcproveedor'],'proveedor_fiscales','pf_rfc','COUNT(*)') == 0){
                      $colpr = "#CC0000";
                      $toltpr = 'data-tooltip="No existe un proveedor con el RFC seleccionado" class="tooltip-top"';
                      $ok = 0;
                      $okind = "NO";
                      $colpr = "#CC0000";
                    }else{
                  */
                  $toltpr = 'data-tooltip="Correcto '.$row['fr_nmbproveedor'].'" class="tooltip-top"';
                  //Forma de pago
                  if(busca($row['fr_fpago'],'cfdi_fpago','cf_nmb','COUNT(*)') == 0 && $row['fr_fpago'] == "I"){
                    $colpr = "#CC0000";
                    $okind = "NO";
                    $toltfp = 'data-tooltip="La forma de pago no se encuentra en el catálogo del SAT" class="tooltip-top"';
                  }else{
                    $fpago = busca($row['fr_fpago'],'cfdi_fpago','cf_nmb','cf_descripcion');
                    $toltfp = 'data-tooltip="Correcto" class="tooltip-top"';
                  }
                  //Método de pago
                  if(in_array($row['fr_metodop'],$this->tipometodopago)){
                    $toltmp = 'data-tooltip="Correcto" class="tooltip-top"';
                  }else{
                    $toltmp = 'data-tooltip="El método de pago no se encuentra en el catálogo del SAT" class="tooltip-top"';
                  }
                  //Suma de conceptos
                  $totalc = $row['fr_subtotal']-$row['fr_descuento']+$traslados-$retenciones;
                  $totalcmas=$totalc+.01;
                  $totalcmenos=$totalc-.01;
                  if($totalc >= $totalcmenos && $totalc <= $totalcmas){
                    $tolttt = 'data-tooltip="Correcto" class="tooltip-top"';
                  }else{
                    $tolttt = 'data-tooltip="La suma de subtotal + Traslados - Retenciones
                      NO es igual al total expresado" class="tooltip-top"';
                    $okind = "NO";
                    $colpr = "#CC0000";
                  }
                  //Validar UUID  validauuidSAT($row['fr_rfcproveedor'],$rfcrec,$row['fr_total'],$row['fr_uuid'],3)
                  if("Vigente" == "Vigente"){
                    $toltui = 'data-tooltip="Estatus la de factura Vigente" class="tooltip-top"';
                    //$cancela = validauuidSAT($row['fr_rfcproveedor'],$rfcrec,$row['fr_total'],$row['fr_uuid'],2);
                  }else{
                    $toltui = 'data-tooltip="La factura no se encuentra en las listas del SAT" class="tooltip-top"';
                    $cancela = "-";
                  }
                  $subtotal+=$row['fr_subtotal'];
                  $tottras+=$traslados;
                  $totdesc+=$row['fr_descuento'];
                  $totret+=$retenciones;
                  $totalf+=$row['fr_total'];
                  if($row['fr_tipoc'] == "I") $docto = "FACTURA";
                  elseif($row['fr_tipoc'] == "P") $docto = "COMPLEMENTO DE PAGO";
                  echo '<tr>
                    <td>
                      <a target="_BLANK" href="?modulo=facturasrec&accion=showfactura&id='.$row['fr_id'].'">
                        <button class="btn btn-secondary"><i class="icon-book2"></i></button>
                      </a>
                    </td>
                    <td class="text-medium">'.$row['fr_serie'].''.$row['fr_folio'].'</td>
                    <td class="text-medium" '.$toltpr.'><b>'.$row['fr_rfcproveedor'].'</b><br>'.$row['fr_nmbproveedor'].'</td>
                    <td class="text-medium">'.$fechatim[0].' '.$fechatim[1].'</td>
                    <td class="text-medium" '.$toltfp.'>'.$fpago.'</td>
                    <td class="text-medium" '.$toltmp.'>'.$this->metodopago[$row['fr_metodop']].'</td>
                    <td class="text-medium">'.number_format($row['fr_subtotal'],2).'</td>
                    <td class="text-medium">'.number_format($row['fr_descuento'],2).'</td>
                    <td class="text-medium">'.number_format($traslados,2).'</td>
                    <td class="text-medium">'.number_format($retenciones,2).'</td>
                    <td class="text-medium" '.$tolttt.'>'.number_format($row['fr_total'],2).'</td>
                    <td class="text-medium" '.$toltui.'>'.$row['fr_uuid'].'</td>
                    <td class="text-medium">'.$docto.'</td>
                  </tr>';
                  if($okind == "NO") $disp = "none";
                }
                echo '<tr>
                  <td colspan="5">&nbsp;</td>
                  <td>Totales</td>
                  <td>'.number_format($subtotal,2).'</td>
                  <td>'.number_format($totdesc,2).'</td>
                  <td>'.number_format($tottras,2).'</td>
                  <td>'.number_format($totret,2).'</td>
                  <td>'.number_format($totalf,2).'</td>
                </tr>';
              echo '</table>
            </div>
          </div>
        </div>';
        if($estatuscarga == "N")
          echo '<script>
            document.getElementById("finalizar").style.display = "'.$disp.'";
          </script>';
      echo'</div';
    }
    function showfactura(){
        echo '<div class="div2 mb-1" id="bboton01">
          <a href="?modulo=facturasrec&accion=index" class="btn btn-warning botont">
            <i class="fa fa-arrow-left"></i> Atrás
          </a>';
        echo '</div>
      </div>';
      echo '<center>
        <div class="table-responsive bg-white">
          <table width="100%" border="0" cellpadding="0" cellspacing="0" class="lista table table-bordered">
            <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
              <tr>
                <th align="center" COLSPAN="8"><strong>INFORMACIÓN DEL CLIENTE </strong></th>
              </tr>
              <tr>
                <th colspan="2" ><strong>Razón Social del Proveedor </strong></th>
                <th align="center"><strong>* RFC del Proveedor </strong></th>
                <th align="center"><strong>* Uso del CFDI </strong></th>';
              echo '</tr>
            </thead>
            <tbody>';
              echo '<td colspan="2">'.$this->model->nmbproveedor.'</td>';
              echo '<td>'.$this->model->rfcproveedor.'</td>';
              echo '<td>'.busca($this->model->usocfdi,'cfdi_uso','cu_id','cu_nmb').'</td>
            </tbody>
          </table>';
          echo '<table width="80%"  cellpadding="0" cellspacing="0" class="lista table table-bordered table-stripeds">
            <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
              <tr>';
                echo '<th width="10%">Cantidad</th>
                <th width="10%">Unidad</th>
                <th width="10%">Clave</th>
                <th width="40%">Concepto</th>
                <th width="15%">Precio Unitario</th>
                <th width="15%">Total</th>';
              echo'</tr>
            </thead>
            <tbody>';
              $this->model->resultfacrd($this->model->id);
              while ($row = $this->model->resultcd->fetch_array()) {
                echo '<tr>';
                  echo '<td style="text-align:center;">' . $row['rd_cantidad'] .'</td>';
                  echo '<td style="text-align:center;">' .$row['rd_unidad'].'<br>'.$row['rd_claveuni'].'</td>
                  <td>'.$row['rd_producto'].'</td>
                  <td style="text-align:center;">'.$row['rd_concepto'].'</td>
                  <td style="text-align:right;">'.number_format($row['rd_costo'],6).'</td>';
                  echo '<td style="text-align:right;">$ ' . number_format(($row['rd_costo'] * $row['rd_cantidad']), 2) . '</td>';
                echo '</tr>';
              }
              echo '<tr>
                <td colspan="4">&nbsp;</td>';
                echo '<td style="text-align:right;"><b>Subtotal</td>
                <td style="text-align:right;">$ ' . number_format($this->model->subtotal, 2) . '</td>
              </tr>';
              /*si el IVA es aplicable se suma a la factura*/
              $iva = busca($this->model->id,'facturasrec_impuestos','fi_tipo = "T" AND fi_impuesto = "002" AND fi_facturas','SUM(fi_importe)');
              $isr = busca($this->model->id,'facturasrec_impuestos','fi_tipo = "R" AND fi_impuesto = "001" AND fi_facturas','SUM(fi_importe)');
              $retiva = busca($this->model->id,'facturasrec_impuestos','fi_tipo = "R" AND fi_impuesto = "002" AND fi_facturas','SUM(fi_importe)');
              if($iva > 0)
                echo '<tr>
                  <td colspan="4">&nbsp;</td>
                  <td style="text-align:right;"><b>I.V.A.</td>
                  <td style="text-align:right;">$ ' . number_format($iva, 2) . '</td>
                </tr>';
              if($isr > 0)
                echo '<tr>
                  <td colspan="4">&nbsp;</td>
                  <td style="text-align:right;"><b>I.S.R. RET</td>
                  <td style="text-align:right;">$ ' . number_format($isr, 2) . '</td>
                </tr>';

              if($retiva > 0)
                echo '<tr><td colspan="4">&nbsp;</td>
                  <td style="text-align:right;"><b>I.V.A. RET</td>
                  <td style="text-align:right;">$ ' . number_format($retiva, 2) . '</td>
                </tr>';
              echo '<tr>
                <td colspan="4">&nbsp;</td>
                <td style="text-align:right;"><b>Total</td>
                <td style="text-align:right;">$ ' . number_format($this->model->total, 2) . '</td>
              </tr>';
            echo'</tbody>
          </table>
        </div>';
                //</td>
              //</tr>
            //</tbody>
          //</table>
        //</div>
      echo '</center>';
    }
    function acreditar(){
        $acreditado = busca($this->model->id,'facturarec_egreso','fe_factura','SUM(fe_monto)');
        echo '<div class="div2" id="bboton01">
          <a href="?modulo=facturasrec&accion=index"><input type="button" class="botont" id="atras" /></a>';
          // <a target="_BLANK" href="?modulo=facturasrec&accion=showfactura&factura='.$this->model->id.'"><input type="button" class="botont" id="detalles" /></a>
          if($acreditado == $this->model->total && $this->model->estatus == "A")
            echo '<input type="button" class="botont" id="finalizar" onclick="finalizar();" />';
        echo '</div>
      </div>';
      echo '<script>
        function finalizar(){
          conf = confirm("¿Deseas finalizar esta poliza?");
          if(conf == true){
            document.getElementById("finalizar").disabled = true;
            document.location.href="?modulo=facturasrec&accion=finalizareg&id='.$this->model->id.'";
          }
        }
      </script>';
      echo '<center>
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="lista"><tbody>
          <tr>
            <th align="center" COLSPAN="8"><strong>INFORMACIÓN DE LA FACTURA</strong></th>
          </tr>
          <tr>
            <th><strong>Razón Social del Proveedor </strong></th>
            <th align="center"><strong>* RFC del Proveedor </strong></th>
            <th align="center"><strong>* Uso del CFDI </strong></th>
            <th align="center"><strong>* Fecha de la factura</strong></th>';
          echo '</tr>
          <tr>';
            echo '<td>'.$this->model->nmbproveedor.'</td>';
            echo '<td>'.$this->model->rfcproveedor.'</td>';
            echo '<td>'.busca($this->model->usocfdi,'cfdi_uso','cu_id','cu_nmb').'</td>
            <td>'.fecha_formato($this->model->fecha,true,false).'</td>
          </tr>
          <tr>
            <th align="center"><strong>* Forma de pago </strong></th>
            <th align="center"><strong>* Método de pago </strong></th>
            <th align="center"><strong>* Monto de la factura </strong></th>
            <th align="center"><strong>* Iva Acreditable </strong></th>
          </tr>
          <tr>';
            ?>
              <script>
                function calculaiva(){
                  var importe = document.getElementById("import").value;
                  var ivac = 0;

                  importe = parseFloat(importe);
                  ivac = importe-(importe/1.16);

                  document.getElementById("importiv").value = ivac.toFixed(2);
                  document.getElementById("importiv").focus();
                  document.getElementById("importiv").select();
                }
              </script>
            <?php
            $ivacre = busca($this->model->id,'facturasrec_totimp','fti_tipo = "T" AND fti_factura','fti_importe');
            echo '<td>'.$this->model->fpago.'<br>'.busca($this->model->fpago,'cfdi_fpago','cf_nmb','cf_descripcion').'</td>';
            echo '<td>'.$this->model->metodop.'<br>'.$this->metodopago[$this->model->metodop].'</td>';
            echo '<td>$ '.number_format($this->model->total,2).'</td>';
            echo '<td>$ '.number_format($ivacre,2).'</td>';
          echo '</tr>
        </table>';
        echo '<table width="80%"  cellpadding="0" cellspacing="0" class="lista">
          <thead>
            <tr>';
              echo '<th width="10%">Cantidad</th>
              <th width="10%">Unidad</th>
              <th width="10%">Clave</th>
              <th width="40%">Concepto</th>
              <th width="15%">Precio Unitario</th>
              <th width="15%">Total</th>';
            echo '</tr>
          </thead>
          <tbody>';
            $this->model->resultfacrd($this->model->id);
            while ($row = $this->model->resultcd->fetch_array()) {
              echo '<tr>';
                echo '<td style="text-align:center;">' . $row['rd_cantidad'] .'</td>';
                echo '<td style="text-align:center;">' .$row['rd_unidad'].'<br>'.$row['rd_claveuni'].'</td>
                <td>'.$row['rd_producto'].'</td>
                <td style="text-align:center;">'.$row['rd_concepto'].'</td>
                <td style="text-align:right;">'.number_format($row['rd_costo'],6).'</td>';
                echo '<td style="text-align:right;">$ ' . number_format(($row['rd_costo'] * $row['rd_cantidad']), 2) . '</td>';
              echo '</tr>';
            }
            echo '<tr>
              <td colspan="4">&nbsp;</td>';
              echo '<td style="text-align:right;"><b>Subtotal</td><td style="text-align:right;">$ ' . number_format($this->model->subtotal, 2) . '</td>
            </tr>';
            /*si el IVA es aplicable se suma a la factura*/
            $iva = busca($this->model->id,'facturasrec_impuestos','fi_tipo = "T" AND fi_impuesto = "002" AND fi_facturas','SUM(fi_importe)');
            $isr = busca($this->model->id,'facturasrec_impuestos','fi_tipo = "R" AND fi_impuesto = "001" AND fi_facturas','SUM(fi_importe)');
            $retiva = busca($this->model->id,'facturasrec_impuestos','fi_tipo = "R" AND fi_impuesto = "002" AND fi_facturas','SUM(fi_importe)');
            if($iva > 0)
              echo '<tr>
                <td colspan="4">&nbsp;</td>
                <td style="text-align:right;"><b>I.V.A.</td>
                <td style="text-align:right;">$ ' . number_format($iva, 2) . '</td>
              </tr>';
            if($isr > 0)
              echo '<tr>
                <td colspan="4">&nbsp;</td>
                <td style="text-align:right;"><b>I.S.R. RET</td>
                <td style="text-align:right;">$ ' . number_format($isr, 2) . '</td>
              </tr>';
            if($retiva > 0)
              echo '<tr>
                <td colspan="4">&nbsp;</td>
                <td style="text-align:right;"><b>I.V.A. RET</td>
                <td style="text-align:right;">$ ' . number_format($retiva, 2) . '</td>
              </tr>';
            echo '<tr>
              <td colspan="4">&nbsp;</td>
              <td style="text-align:right;"><b>Total</td>
              <td style="text-align:right;">$ ' . number_format($this->model->total, 2) . '</td>
            </tr>';
          echo '</tbdoy>
        </table>
      </center>';//</div></td></tr></table>
      /*
        echo '<center>';
          if($acreditado < $this->model->total)
          echo '<a href="popup/agregaegreso.php?id='.$this->model->id.'&modulo='.$_GET['modulo'].'&accion='.$_GET['accion'].'&form=factur&input=importe" onclick="window.open(this.href,\'window\',\'width=1080, height=650\');return false">
            <input type="button" id="agregar" class="botont"/>
          </a>';
          echo '<table class="lista" width="90%">
            <thead>
              <tr>
                <th colspan="7">Agregar desglose al egreso (Poliza de gastos)</th>
              </tr>
              <tr>
                <th width="15%">Concepto</th>
                <th width="15%">Importe</th>
                <th width="10%">IVA Acréditadble</th>
                <th width="15%">Importe IVA</th>
                <th width="25%">Observaciones</th>
                <th width="10%"></th>
              </tr>
            </thead>';
            $importe = $this->model->total-$acreditado;
            if($this->model->fpago == "01" && $acreditado < $this->model->total){
              echo '<form method="post" action="?modulo=facturasrec&accion=insertfaceg&fac='.$this->model->id.'">
                <tr>
                  <td>'.menu_select_db('categoriasg','cg_id','cg_nmb',$concepto,'categoria','cg_estatus = "A"',false,false,false,true).'</td>
                  <td><input type="number" id="import" name="importe" value="'.$importe.'" step="0.01" min="0" max="'.$importe.'" onchange="calculaiva();" required /></td>
                  <td><input type="checkbox" id="ivacr" onchange="calculaiva();" name="ivac" '.$checkivac.' /></td>
                  <td><input type="number" id="importiv" name="importeiva" value="'.$importeiva.'" step="0.01" min="0" max="'.number_format($ivacre,2,'.','').'" required /></td>
                  <td><input type="text" id="obst" name="obs" size="40" value="'.$obervaciones.'" placeholder="Observaciones sobre la poliza" /></td>
                  <td><input type="submit" class="botont" id="agregar" value=""  /></td>
                </tr>
              </form>';
            }
            $sql = 'SELECT * FROM facturarec_egreso WHERE fe_factura = "'.$this->model->id.'" ORDER BY fe_id';
            $result = setq($sql) or die($sql);
            while($row = mysql_fetch_array($result)){
              if($row['fe_ivac'] > 0) $ivac = "SI"; else $ivac = "NO";
              echo '<tr>
                <td>
                  <form method="post" action="?modulo=facturasrec&accion=setconcepto&factura='.$this->model->id.'&id='.$row['fe_id'].'">
                    '.menu_select_db('categoriasg','cg_id','cg_nmb',$row['fe_categoria'],'categoria','cg_estatus = "A"',false,false,'S').'
                  </form>
                </td>
                <td>'.number_format($row['fe_monto'],2).'</td>
                <td>'.$ivac.'</td>
                <td>'.number_format($row['fe_ivac'],2).'</td>
                <td>
                  <form method="post" action="?modulo=facturasrec&accion=setobservaciones&factura='.$this->model->id.'&id='.$row['fe_id'].'">
                    <input type="text" name="Observaciones" size="40" value="'.$row['fe_observaciones'].'" onchange="submit();" />
                  </form>
                </td>';
                if($this->model->estatus == "A")
                  echo '<td>
                    <a href="?modulo=facturasrec&accion=delfaceg&fac='.$this->model->id.'&id='.$row['fe_id'].'">
                      <input type="button" class="botont" id="borrar"  />
                    </a>
                  </td>';
                else
                  echo '<td>&nbsp;</td>';
              echo '</tr>';
            }
          echo '</table>
        </center>';
      */
    }
  }
?>