<?php
  ini_set('display_errors',1);
  class recepciones {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelrecepciones(isset($obj));
    }
    function index() {
      if(isset($_GET['proveedor'])) $_REQUEST['proveedor'] = busca($_GET['proveedor'], 'proveedores', 'p_id' ,'p_nmb');
      if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-60 days'));
      if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d');
      if (!isset($_REQUEST['proveedor'])) $_REQUEST['proveedor'] = NULL;
      if (!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if (!isset($_REQUEST['documento'])) $_REQUEST['documento'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
      $this->model->result($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['estatus'],$_REQUEST['documento'],$_REQUEST['page']);
      $this->view = new viewrecepciones($this->model);
      $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['estatus'],$_REQUEST['documento'],$_REQUEST['page']);
    }
    function show(){
      $this->model->select($_GET['id']);
      $this->model->resultd($_GET['id']);
      $this->view = new viewrecepciones($this->model);
      $this->view->show();
    }
    function showalm(){
      $this->model->select($_GET['id']);
      $this->model->resultdalm($_GET['id']);
      $this->view = new viewrecepciones($this->model);
      $this->view->showalm();
    }
    function dividir(){
      //foreachdie();
      $cantidadn = $_POST['cantidado'] - $_POST['restar'];
      $sqlsel = 'SELECT rd_costo, rd_iva, rd_largo, rd_ancho, rd_alto FROM recepcionesd WHERE rd_id = "'.$_GET['idprod'].'" AND rd_recepcion = "'.$_GET['id'].'"';
      $resultsel = setq($sqlsel);
      list($costo, $iva, $largo, $ancho,$alto) = $resultsel -> fetch_array();
      
      $this->model->setDataAlm($_GET['id'],$_GET['idprod'],$cantidadn,$_POST['restar'],$_POST['articulo'],$costo,$iva,$_POST['almacenprod'],'', $largo, $ancho, $alto);
      $this->model->updatealm();
      $this->model->insertalm();
      redirect("?modulo=recepciones&accion=showalm&id=".$_GET['id']);
      //redirect('?modulo=recepciones&accion=index');
    }
    function importremd(){
      //$tablero = busca($_GET['ordenc'],'ordenesc','o_id','o_tablero');
      //$this->model->select()
      $this->model->id = $_GET['recepcion'];
      $query = 'SELECT * FROM ordenesc INNER JOIN ordenescd ON od_ordenc = o_id
                WHERE o_id = "'.$_GET['ordenc'].'" ORDER BY od_nmbarticulo';
      $resultado = setq($query);
      $i = 0;
      while($row = $resultado->fetch_array()){
        //$idrec = getmax('od_id','recepciones','od_ordenc');
        //$idrec = getmax('rd_id','recepcionesd','rd_recepcion','rd_recepcion = "'.$_GET['recepcion'].'"');    
        $idrec = getmax('rd_id','recepcionesd','rd_recepcion');    
        $cantidad = $_POST['cantidad'.$row['od_id']];
        $costo = $_POST['costo'.$row['od_id']];
        $articulo = busca($row['od_id'],'ordenescd','od_id','od_articulo');
        $nmbprod = busca($row['od_id'],'ordenescd','od_id','od_nmbarticulo');
        $iva = 0;
        $importe = $costo;
        if($articulo){
          $precio = 'SELECT mpp_iva,mpp_usd FROM pr_materiaprima_proveedor WHERE mpp_materiaprima = "'.$articulo.'" AND mpp_activo = "1"';
          $result = setq($precio);
          list($aiva,$usd) = $result->fetch_array();
          if($aiva == "1") $importe = $costo * 1.16;
          if($usd == "1") $importe = $costo * (busca(1,'empresas','e_id','e_valorusd'));
        }

    
        if($cantidad > 0){
          $cantiprod = busca($_GET['recepcion'],'recepcionesd','rd_articulo = "'.$articulo.'" AND rd_recepcion','SUM(rd_cantidad)')+$cantidad;
          /*$this->model->insertdetalle(); */
          $existe = busca($_GET['recepcion'],'recepcionesd','rd_articulo = "'.$articulo.'" AND rd_recepcion','rd_id');
          if($existe){
            $cantant = busca($_GET['recepcion'],'recepcionesd','rd_articulo = "'.$articulo.'" AND rd_id = "'.$existe.'" AND rd_recepcion','rd_cantidad');
            $cantfin = $cantidad+$cantant;
            $this->model->setDatad($_GET['recepcion'], $existe, $cantfin, $articulo,$importe,$aiva, $row['od_largo'], $row['od_ancho'], $row['od_alto']);
            $this->model->updatedetalle();
          }else{
            $idrec = getmax('rd_id','recepcionesd','rd_recepcion');
            $this->model->setDatad($_GET['recepcion'], $idrec, $cantiprod, $articulo,$importe,$aiva, $row['od_largo'], $row['od_ancho'], $row['od_alto']);
            $this->model->insertdetalle();
          }
        }
      }
      $this->model->setprecio($_GET['recepcion']);
      echo '<script>
        window.opener.location.reload();
        window.close();
      </script>';
    }
    function insert(){
      if(!isset($_POST['tiporec'])) $_POST['tiporec'] = "A";
      if($_POST['tiporec'] == "C"){
        $sqlna = 'SELECT COUNT(*) FROM activo_producto INNER JOIN articulos ON a_id = ap_producto
                  INNER JOIN articulo_proveedor ON ap_articulo = a_id
                  WHERE ap_proveedor = "'.$_POST['proveedor'].'"';
        $resulta = setq($sqlna);
        list($numa) = $resulta->fetch_array();
        if($numa == 0){
          ?>
          <script>
            alert("ERROR: El proveedor seleccionado, no tiene productos ligados a activos registrados\nElige un distinto");
            window.location.href="?modulo=recepciones&accion=index";
          </script>
          <?php
          die();
        }
      }
      
      if(isset($_POST['cargar'])) $cargar = "1"; else $cargar = "0";
      if(isset($_POST['diva'])) $diva = "1"; else $diva = "0";
      $folioint = getmax('r_folio','recepciones');
      if($folioint == "1") $folioint = "INF".str_pad($folioint,6,"0",STR_PAD_LEFT); 
      else $folioint = str_pad($folioint,6,"0",STR_PAD_LEFT);   
      $this->model->setdata(NULL,$folioint,$_POST['almacen'],$_POST['ffin'],$_POST['responsable'],$_POST['proveedor'],$_POST['foliodoc'],$_POST['tipodesc'],$_POST['descripcion'],$_POST['tiporec'],$_POST['ordenc'],$_POST['desc'],$cargar,$diva);
      $ok = 0;
      if($this->model->ordenc){
        $sql = 'SELECT * FROM ordenescd WHERE od_ordenc = "'.$this->model->ordenc.'"';
        $result = setq($sql);
        while($rowo = $result->fetch_array()){
          if($rowo['od_articulo'] == "0") $ok++;
        }
        if($ok == 0){//
          $this->model->insert();
          $sql = 'SELECT * FROM ordenescd WHERE od_ordenc = "'.$this->model->ordenc.'"';
          $result = setq($sql);
          while($rowo = $result->fetch_array()){
            if($rowo['od_articulo'] != "0"){
              //$sqldif = 'SELECT SUM(rd_cantidad) FROM recepcionesd INNER JOIN recepciones ON rd_recepcion = r_id WHERE r_ordenc = "'.$this->model->ordenc.'" AND rd_articulo = "'.$rowo['od_articulo'].'"'; 
              //$resultdif = setq($sqldif,true);
              //list($existeprod) = $resultdif->fetch_array();
              /* $existeprod = busca($rowo['od_articulo'],'recepcionesd INNER JOIN recepciones ON rd_recepcion = r_id','r_ordenc = "'.$this->model->ordenc.'" AND rd_articulo','SUM(rd_cantidad)');
              if($existeprod < $rowo['od_cantidad']){ */
                $dif = $rowo['od_cantidad']; //-$existeprod
                $idrec = getmax('rd_id','recepcionesd','rd_recepcion','rd_recepcion = "'.$this->model->id.'"');
                $this->model->setDatad($this->model->id, $idrec, $dif, $rowo['od_articulo'],$rowo['od_precio'],$rowo['od_iva'],$rowo['od_largo'],$rowo['od_ancho'],$rowo['od_alto']);
                $this->model->insertdetalle();
              //}
            }else{
            }
          }
          $this->model->setprecio($this->model->id);//
        }else{
          echo '<script>alert("La orden de compra contiene servicios o productos que no están en tu catalogo. Verifica para poder continuar"); window.location.href="?modulo=recepciones&accion=index";</script>';
          die();
        }
      }else{
        $this->model->insert();
      }
      //$this->model->insert();
      redirect("?modulo=recepciones&accion=show&id=".$this->model->id);
    }
    function update(){
      if($_POST['cargar'] == "") $cargar = "0"; else $cargar = "1";
      if($_POST['diva'] == "") $diva = "0"; else $diva = "1";
      $this->model->setdata($_GET['idrecepcion'],$_POST['folioint'],$_POST['almacen'],$_POST['ffin'],$_POST['responsable'],$_POST['proveedor'],$_POST['foliodoc'],$_POST['tipodesc'],$_POST['descripcion'],$_POST['tiporec'],$_POST['ordenc'],$_POST['desc'],$cargar,$diva);
      $ok = 0;
      if($this->model->ordenc){
        $sql = 'SELECT * FROM ordenescd WHERE od_ordenc = "'.$this->model->ordenc.'"';
        $result = setq($sql);
        while($rowo = $result->fetch_array()){
          if($rowo['od_articulo'] == "0") $ok++;
        }
        if($ok == 0){
        }else{
          echo '<script>alert("La orden de compra contiene servicios o productos que no están en tu catalogo. Verifica para poder continuar"); window.location.href="?modulo=recepciones&accion=index";</script>';
          die();
        }
      }

      $sql = 'SELECT * FROM recepcionesd WHERE rd_recepcion = "'.$_GET['idrecepcion'].'"';
      $result = setq($sql);

      if($result->num_rows > 0){
        $mivar = busca($_GET['idrecepcion'],'recepciones','r_id','r_diva');
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        while($row = $result->fetch_array()){
          if($mivar != $diva){
            if($row['rd_iva'] == "1" && $diva == "0"){
              $precio = $row['rd_costo'] * (1+($viva/100));
              $sqlup = 'UPDATE recepcionesd SET rd_costo = "'.$precio.'" WHERE rd_id = "'.$row['rd_id'].'"';
              setq($sqlup);
            }elseif($row['rd_iva'] == "1" && $diva == "1"){
              $precio = $row['rd_costo'] / (1+($viva/100));
              $sqlup = 'UPDATE recepcionesd SET rd_costo = "'.$precio.'" WHERE rd_id = "'.$row['rd_id'].'"';
              setq($sqlup);
            }
          }
        }
      }

      $this->model->update();  
      $this->model->setprecio($_GET['idrecepcion'],1);
      
      if($this->model->estatus == "P") $dir = "showalm"; else $dir="show";
      redirect('?modulo=recepciones&accion='.$dir.'&id='.$this->model->id.'');
    }
    function insertdetalle(){
      $this->model->id = $_GET['id'];
      $articulo = getIDmp($_POST['producto']);

      if($articulo){
        if(busca($articulo,'activo_producto','ap_producto','COUNT(*)') == 0 && busca($_GET['id'],'recepciones','r_id','r_tiporec') == "C"){
          echo '<script>
            alert("Error: El producto no tiene ningun activo ligado\nVerifica el producto");
            window.location.href="?modulo=recepciones&accion=show&id='.$_GET['id'].'";
          </script>';
          die();
        }

        $tiporec = busca($_GET['id'],'recepciones','r_id','r_tiporec');
        if($tiporec == "C"){
          $almacen = busca($_GET['id'],'recepciones','r_id','r_almacen');
          $sqlal = 'SELECT COUNT(*) FROM activo_almacen WHERE aa_activo IN
                    (SELECT DISTINCT(ap_activo) FROM activo_producto WHERE ap_producto = "'.$articulo.'")';
          $resultal = setq($sqlal);
          list($numacs) = $resultal->fetch_array();
          if(!$numacs) $numacs = 0;
          $cantiprod = busca($_GET['id'],'recepcionesd','rd_articulo = "'.$articulo.'" AND rd_recepcion','SUM(rd_cantidad)')+$_POST['cantidad'];
          if($numacs < $cantiprod){
            echo '<script>
              alert("Error: NO cuentas con suficientes activos para contener el producto");
              window.location.href="?modulo=recepciones&accion=show&id='.$_GET['id'].'";
            </script>';
            die();
          }
        }
        /* if($existe){
          $cantant = busca($_GET['id'],'recepcionesd','rd_articulo = "'.$articulo.'" AND rd_id = "'.$existe.'" AND rd_recepcion','rd_cantidad');
          $cantfin = $_POST['cantidad']+$cantant;
        }else{ */
          $idrec = getmax('rd_id','recepcionesd','rd_recepcion');
          $cantfin = $_POST['cantidad'];
        //}

        $diva = busca($_GET['id'],'recepciones','r_id','r_diva');
        //if(isset($_POST['iva'])) $iva = 1; else $iva = 0;
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        //if($diva == "1" && $iva == 1){
        if($diva == "1"){
          $imp = $_POST['importe']/(1+($viva/100));
          $iva = "1";
        /*}else if($iva == 1){
          $imp = $_POST['importe']/(1+($viva/100));*/
        }else{
          $imp = $_POST['importe'];
          $iva = "0";
        }

        /* if($existe){
          $this->model->setDatad($_GET['id'], $existe, $cantfin, $articulo,$imp,$iva,$_POST['linean'], $modelo, $_POST['largo'], $_POST['ancho'], $_POST['lato']);
          $this->model->updatedetalle();
        }else{ */
          $this->model->setDatad($_GET['id'], $idrec, $cantfin, $articulo,$imp,$iva, $_POST['largo'], $_POST['ancho'], $_POST['alto']);
          $this->model->insertdetalle();
        /* } */
        
        if($tiporec == "C") $this->model->checkactivos($_GET['id'],$idrec,$almacen);
        $this->model->setprecio($_GET['id']);
        if($this->model->estatus == "P") $dir = "showalm"; else $dir="show";
      } else {
        echo '<script>
          alert("Error: El producto buscado no esta registrado en tu base de datos, verifica la información");
          window.location.href="?modulo=recepciones&accion=show&id='.$_GET['id'].'";
          </script>';
      } 
      redirect('?modulo=recepciones&accion='.$dir.'&id='.$this->model->id.'');
    }
    function updatedetalle(){
      if(isset($_POST['iva']) && $_POST['iva'] == "on") $iva = 1; 
      elseif($_POST['iva'] == "1") $iva = 1;
      else $iva = 0;
      $imp = $_POST['importe'];
      
      $this->model->setDatad($_GET['id'], $_GET['idprod'], $_POST['cantidad'], NULL,$imp,$iva," ","", '');
      $this->model->updatedetalle();

      $this->model->setprecio($_GET['id']);
      $estatus = busca($this->model->recepcion,'recepciones','r_id','r_estatus'); 
      if($estatus == "P") $dir = "showalm"; else $dir="show";
      redirect("?modulo=recepciones&accion=show&id=".$this->model->recepcion);
    }
    function updatealmacen(){
      //die($_POST['almacen']);
      $sql = 'UPDATE recepcionesd SET
              rd_almacen = "'.$_POST['almacenprod' . $_GET['idprod']].'"
              WHERE rd_recepcion = "'.$_GET['id'].'" AND rd_id = "'.$_GET['idprod'].'"';
      setq($sql);
      redirect("?modulo=recepciones&accion=showalm&id=".$_GET['id']);
    }
    function delprod(){
      $this->model->delprod($_GET['id'],$_GET['idprod']);
      $this->model->setprecio($_GET['id']);
      redirect("?modulo=recepciones&accion=show&id=".$_GET['id']);
    }
    function aplicar(){
      $this->model->select($_GET['id']);
      if($this->model->cargar == "0") $this->model->aplicar($_GET['id']);
      else $this->model->aplicarcargar($_GET['id']);

      /* $this->model->imprimir($_GET['movimiento'],$_GET['tipo']); */
      //redirect('?modulo=recepciones&accion=show&id=' . $_GET['id']);
      redirect('?modulo=recepciones&accion=index');
    }
    function updateactivos(){
      $sql = 'DELETE FROM recepcion_activo WHERE ra_recepcion = "'.$_GET['recepciones'].'" AND ra_idr = "'.$_GET['id'].'"';
      setq($sql);

      $almacen = busca($_GET['recepciones'],'recepciones','r_id','r_almacen');
      $sql = 'SELECT activos.*,aa_noserie,aa_id FROM activo_almacen INNER JOIN activos ON aa_activo = a_id
              WHERE aa_almacen = "'.$almacen.'" AND aa_estatus = "N" ORDER BY aa_id ASC';
      $result = setq($sql);
      while($rowr = $result->fetch_array()){
        if(isset($_POST['activo'.$rowr['aa_id']]))
          $this->model->setactivorec($_GET['recepciones'],$_GET['id'],$rowr['aa_id']);
      }
      if(isset($_GET['from'])){
        echo '<script>
          opener.location.reload();
          window.close();
        </script>';
      }
    }
    function cambiarcantidad(){
      if(isset($_REQUEST['cantidad'])){
        $sql = 'UPDATE recepcionesd SET
                rd_cantidad = "'.$_REQUEST['cantidad'].'"
                WHERE rd_id = "'.$_GET['id'].'"';
        setq($sql);

        $this->model->setprecio($_GET['recepcion'],1);
      } 

      redirect("?modulo=recepciones&accion=show&id=".$_GET['recepcion']);
    }
  }

  class modelrecepciones {
    function result($fini,$ffin,$proveedor,$estatus,$documento,$page){ //filtro
      $prov = clearvmayus($proveedor);
      $doc = clearvmayus($documento);
      $bloque = 10;
      $sql = 'SELECT * FROM recepciones WHERE DATE(r_fechagen) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      if($proveedor) $sql.=' AND r_proveedor IN (SELECT p_id FROM proveedores WHERE p_nmb LIKE "%'.$prov.'%" OR p_alias LIKE "%'.$prov.'%" OR p_id = "'.$prov.'")';
      if($documento) $sql.=' AND r_foliodoc LIKE "%'.$doc.'%" ';
      if($estatus) $sql.=' AND r_estatus = "'.$estatus.'"';
      $sql.=' ORDER BY r_id DESC';
      $result = setq($sql);
      $this->resultrc = setq($sql);
      $this->resultt = setq($sql);
    }
    function select($id){
      $sql = 'SELECT * FROM recepciones WHERE r_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['r_id'];
      $this->empresa = $row['r_empresa'];
      $this->folio = $row['r_folio'];
      $this->almacen = $row['r_almacen'];
      $this->cliente = $row['r_cliente'];
      $this->fechacompra= $row['r_fechacompra'];
      $this->comprador= $row['r_comprador'];
      $this->fechagen = $row['r_fechagen'];
      $this->ugen = $row['r_ugen'];
      $this->fechaapli = $row['r_fechaapli'];
      $this->uapli = $row['r_uapli'];
      $this->ordenc = $row['r_ordenc'];
      $this->proveedor = $row['r_proveedor'];
      $this->fechacan = $row['r_fechacan'];
      $this->ucan = $row['r_ucan'];
      $this->mcan = $row['r_mcan'];
      $this->estatus = $row['r_estatus'];
      $this->precio = $row['r_precio'];
      $this->descuento = $row['r_descuento'];
      $this->tipodesc = $row['r_tipodesc'];
      $this->observaciones = $row['r_observaciones'];
      $this->foliodoc = $row['r_foliodoc'];
      $this->tiporec = $row['r_tiporec'];
      $this->subtotal = $row['r_subtotal'];
      $this->iva = $row['r_iva'];
      $this->total = $row['r_total'];
      $this->cargar = $row['r_cargaralm'];
      $this->diva = $row['r_diva'];
    }
    function setDataAlm($id,$idprod,$cantidadn,$resta,$articulo,$costo,$iva,$almacen,$modelo,$largo,$ancho,$alto){
      $this->id = clearvmayus($id);
      $this->idprod = clearvmayus($idprod);
      $this->cantidadn = clearvmayus($cantidadn);
      $this->resta = clearvmayus($resta);
      $this->articulo = clearvmayus($articulo);
      $this->modelo = $modelo;
      $this->costo = clearvmayus($costo);
      $this->iva = clearvmayus($iva);
      $this->almacen = clearvmayus($almacen);
      $this->largo = $largo;
      $this->ancho = $ancho;
      $this->alto = $alto;
    }
    function updatealm(){
      $sql = 'UPDATE recepcionesd SET
              rd_cantidad = "'.$this->cantidadn.'"
              WHERE rd_id = "'.$this->idprod.'" AND rd_recepcion = "'.$this->id.'"';
      setq($sql);
    }
    function insertalm(){
      //$maxid = getmax('rd_id','recepcionesd');
      $idrec = getmax('rd_id','recepcionesd','rd_recepcion');
      $sql = 'INSERT INTO recepcionesd SET
              rd_recepcion = "'.$this->id.'",
              rd_id = "'.$idrec.'",
              rd_articulo = "'.$this->articulo.'",
              rd_cantidad = "'.$this->resta.'",
              rd_costo = "'.$this->costo.'",
              rd_iva = "'.$this->iva.'",
              rd_almacen = "'.$this->almacen.'",
              rd_largo = "'.$this->largo.'",
              rd_ancho = "'.$this->ancho.'",
              rd_alto = "'.$this->alto.'"';
      //echo 'detalle: '.$sql.'<br>'; 
      setq($sql);
    }
    function setdata($id,$folioint,$almacen,$fechacompra,$responsable,$proveedor,$foliodoc,$tipodesc,$descripcion,$tiporec,$ordenc,$descuento,$cargar,$diva){
      $this->id = $id;
      $this->folioint = $folioint;
      $this->almacen = $almacen;
      $this->fechacompra = $fechacompra;
      $this->responsable = $responsable;
      $this->proveedor = $proveedor;
      $this->foliodoc = clearvmayus($foliodoc);
      $this->tipodesc = $tipodesc;
      $this->descripcion = clearvmayus($descripcion);
      $this->tiporec = clearvmayus($tiporec);
      $this->ordenc = clearvmayus($ordenc);
      $this->descuento = $descuento;
      $this->cargar = clearvmayus($cargar);
      $this->diva = clearvmayus($diva);
    }
    function insert(){
      $sql = 'INSERT INTO recepciones SET
              r_folio = "'.$this->folioint.'",
              r_almacen = "'.$this->almacen.'",
              r_fechacompra = "'.$this->fechacompra.'",
              r_comprador = "'.$this->responsable.'",
              r_proveedor = "'.$this->proveedor.'",
              r_tipodesc = "'.$this->tipodesc.'",
              r_foliodoc = "'.$this->foliodoc.'",
              r_ordenc = "'.$this->ordenc.'",
              r_observaciones = "'.$this->descripcion.'",
              r_tiporec = "'.$this->tiporec.'",
              r_fechagen = "'.date('Y-m-d H:i:s').'",
              r_ugen = "'.$_SESSION['uid'].'",
              r_estatus	 = "N",
              r_diva = "'.$this->diva.'",
              r_cargaralm = "'.$this->cargar.'",
              r_descuento = "'.$this->descuento.'"';
      setq($sql);
      $this->id = getmax('r_id','recepciones','',false);
    }
    /*
      function update(){
        $sql = 'UPDATE recepciones SET
                r_folio = "'.$this->folioint.'",
                r_almacen = "'.$this->almacen.'",
                r_fechacompra = "'.$this->fechacompra.'",
                r_comprador = "'.$this->responsable.'",
                r_proveedor = "'.$this->proveedor.'",
                r_tipodesc = "'.$this->tipodesc.'",
                r_foliodoc = "'.$this->foliodoc.'",
                r_tiporec = "'.$this->tiporec.'",
                r_ordenc = "'.$this->ordenc.'",
                r_observaciones = "'.$this->descripcion.'",
                r_fechagen = "'.date('Y-m-d H:i:s').'",
                r_ugen = "'.$_SESSION['uid'].'",
                r_estatus	 = "N"
                WHERE r_id = "'.$this->id.'"';
        setq($sql,TRUE);
      } 
    */
    function update(){
      $sql = 'UPDATE recepciones SET
              r_almacen = "'.$this->almacen.'",
              r_fechacompra = "'.$this->fechacompra.'",
              r_comprador = "'.$this->responsable.'",
              r_proveedor = "'.$this->proveedor.'",
              r_tipodesc = "'.$this->tipodesc.'",
              r_foliodoc = "'.$this->foliodoc.'",
              r_ordenc = "'.$this->ordenc.'",
              r_observaciones = "'.$this->descripcion.'",
              r_fechagen = "'.date('Y-m-d H:i:s').'",
              r_ugen = "'.$_SESSION['uid'].'",
              r_estatus	 = "N",
              r_diva = "'.$this->diva.'",
              r_cargaralm = "'.$this->cargar.'",
              r_descuento = "'.$this->descuento.'"
              WHERE r_id = "'.$this->id.'"';
      setq($sql);
    }
    function comprueba($id) {
      $sql = 'SELECT count(*) FROM recepcionesd WHERE rd_recepcion = "' . $id . '"';
      $result = setq($sql);
      list($cuenta) = $result->fetch_array();
      if ($cuenta >= 1) $ok = 1;
      else $ok = 0;

      if($ok == 1){
        $tipoc = busca($id,'recepciones','r_id','r_tiporec');
        if($tipoc == "C"){
          $sql = 'SELECT SUM(rd_cantidad) FROM recepcionesd WHERE rd_recepcion = "'.$id.'"
                  AND rd_articulo IN (SELECT ap_producto FROM activo_producto)';
          $result = setq($sql);
          list($numprods) = $result->fetch_array();
          if(!$numprods) $numprods = 0;

          $sqlr = 'SELECT COUNT(*) FROM recepcion_activo WHERE ra_recepcion = "'.$id.'"';
          $resultr = setq($sqlr);
          list($numracs) = $resultr->fetch_array();
          if(!$numracs) $numracs = 0;

          if($numracs != $numprods) $ok = 0;
        }
      }

      return($ok);
    }
    function resultd($id){
      $sql = 'SELECT * FROM recepcionesd INNER JOIN pr_materiaprima ON pmp_id = rd_articulo
                WHERE rd_recepcion = "'.$id.'" ORDER BY rd_id';
      $this->resultr = setq($sql);
    }
    function resultdalm($id){
      $sql = 'SELECT * FROM recepcionesd INNER JOIN pr_materiaprima ON pmp_id = rd_articulo
                WHERE rd_recepcion = "'.$id.'" ORDER BY rd_almacen';
      $this->resultr = setq($sql);
    }
    function setDatad($recepcion, $idrec, $cantidad, $articulo,$importe,$iva, $largo, $ancho, $alto){
      $this->recepcion = $recepcion;
      $this->idrec = $idrec;
      $this->cantidad = $cantidad;
      $this->articulo = $articulo;
      $this->importe = $importe;
      $this->iva = $iva;
      $this->largo = $largo;
      $this->ancho = $ancho;
      $this->alto = $alto;
    }
    function insertdetalle(){
      $almacen = busca($this->id,'recepciones','r_id','r_almacen');
      $sql = 'INSERT INTO recepcionesd SET
              rd_recepcion = "'.$this->id.'",
              rd_id = "'.$this->idrec.'",
              rd_articulo = "'.$this->articulo.'",
              rd_largo = "'.$this->largo.'",
              rd_ancho = "'.$this->ancho.'",
              rd_alto = "'.$this->alto.'",
              rd_cantidad = "'.$this->cantidad.'",
              rd_costo = "'.$this->importe.'",
              rd_iva = "'.$this->iva.'",
              rd_almacen = "'.$almacen.'"';
      setq($sql);
    }
    function updatedetalle(){
      $sql = 'UPDATE recepcionesd SET
              rd_cantidad = "'.$this->cantidad.'",
              rd_costo = "'.$this->importe.'",
              rd_iva = "'.$this->iva.'"
              WHERE rd_recepcion = "'.$this->recepcion.'" AND rd_id = "'.$this->idrec.'"';
      setq($sql);
    }
    function aplicar($id){
      $estatus = busca($id,'recepciones','r_id','r_estatus');
      if($estatus == "N"){
        $select = 'SELECT * FROM recepcionesd INNER JOIN recepciones ON rd_recepcion = r_id WHERE rd_recepcion = "'.$id.'"';
        $results = setq($select);
        $viva = busca(1,'configuracionesp','c_id','c_iva')/100;
        while($row = $results->fetch_array()){
          $iva = $row['rd_iva'];
          $articulo = $row['rd_articulo'];
          $existeart = busca($row['rd_articulo'],'pr_materiaprima_proveedor','mpp_proveedor = "'.$row['r_proveedor'].'" AND mpp_activo = "1" AND mpp_materiaprima','mpp_id');
          if($existeart){ //existeart
            $sqlup = 'UPDATE pr_materiaprima_proveedor SET
                      mpp_activo = "0",
                      mpp_estatus = "0"
                      WHERE mpp_id = "'.$existeart.'"';
            setq($sqlup) or die("1");
            $noparte = busca($existeart,'pr_materiaprima_proveedor','mpp_id','mpp_noparteprov');//funcion para no perder el numero de parte del proveedor
            $sqlins = 'INSERT INTO pr_materiaprima_proveedor SET
                      mpp_materiaprima = "'.$row['rd_articulo'].'",
                      mpp_proveedor = "'.$row['r_proveedor'].'",
                      mpp_activo = "1",
                      mpp_costo = "'.$row['rd_costo'].'",
                      mpp_iva = "'.$row['rd_iva'].'",
                      mpp_noparteprov = "'.$noparte.'",
                      mpp_estatus = "1"';
            setq($sqlins) or die("2");
          }else{
            $sqlins = 'INSERT INTO pr_materiaprima_proveedor SET
                      mpp_materiaprima = "'.$row['rd_articulo'].'",
                      mpp_proveedor = "'.$row['r_proveedor'].'",
                      mpp_activo = "0",
                      mpp_costo = "'.$row['rd_costo'].'",
                      mpp_iva = "'.$row['rd_iva'].'",
                      mpp_estatus = "1"';
            setq($sqlins) or die("3");
          } /// existeart
          /* $sql = 'SELECT * FROM esquema_precio WHERE ep_estatus = "A"';
          $result = setq($sql); 
          while($rowesq = $result->fetch_array()){
            //$sqlprecio = 'SELECT * FROM articulo_precio WHERE ap_articulo = "'.$row['rd_articulo'].'" AND ap_esquema = "'.$rowesq['ep_id'].'" AND ap_activo = "1"';
            if($row['rd_iva'] == "1") $costoo = $row['rd_costo']*1.16;

            if($rowesq['ep_base'] == "C") $base = $costoo;
            else $base = busca($rowesq['ep_precio'],'articulos_precios','ap_activo = "1" AND ap_articulo = "'.$row['rd_articulo'].'" AND ap_esquema','ap_precio');

            $utilidad = $base*($rowesq['ep_sumarp']/100);

            if($rowesq['ep_masmenos'] == "+") $precio = ($base+$utilidad)+$rowesq['ep_sumarf'];
            else $precio = ($base-$utilidad)+$rowesq['ep_sumarf'];

            if($rowesq['ep_miva'] == 1) $preciofinal = $precio * (1+$viva);

            $apactivo = busca($rowesq['ep_id'],'articulos_precios','ap_activo = "1" AND ap_articulo = "'.$row['rd_articulo'].'" AND ap_esquema','ap_id');
            $sqlup = 'UPDATE articulos_precios SET 
                      ap_activo = "0"
                      WHERE ap_id = "'.$apactivo.'"';
            setq($sqlup);

            $sqlin = 'INSERT INTO articulos_precios SET
                      ap_articulo = "'.$row['rd_articulo'].'",
                      ap_esquema = "'.$rowesq['ep_id'].'",
                      ap_precio = "'.$preciofinal.'",
                      ap_costo = "'.$costoo.'",
                      ap_activo = "1",
                      ap_fecha = "'.date('Y-m-d H:i:s').'",
                      ap_user = "'.$_SESSION['uid'].'",
                      ap_talla = "0"';
            setq($sqlin);
          } */
        }
        $sql3 = 'UPDATE recepciones SET r_estatus = "P", r_fechaapli = "'.date('Y-m-d H:i:s').'", r_uapli = "'.$_SESSION['uid'].'"
                  WHERE r_id="' . $id . '"';
        setq($sql3);
        if($this->ordenc == "0" || $this->ordenc == NULL){
          include_once('cxpagar.php');
          $cxp = new modelcxpagar();
          $idcp = $cxp->insertcxpagar('1',$this->proveedor,$this->id,"E",$this->total,date('Y-m-d H:i:s'),"RECEPCIÓN DE MERCANCÍA ".$this->folio);
          $cxp->addproyeccion($idcp);
        }
      } //end if estatus
      elseif($estatus == "P"){
        $tiporec = busca($id,'recepciones','r_id','r_tiporec');
        if($tiporec == "A"){
          $sql = 'SELECT r_id,rd_articulo,rd_almacen,rd_id,rd_costo,rd_iva,rd_cantidad,rd_largo,rd_ancho,rd_alto
                  FROM recepcionesd INNER JOIN recepciones ON r_id = rd_recepcion
                  WHERE r_estatus = "P" AND r_id = "'.$id.'"';
          $result1 = setq($sql) or die($sql);
          while($rowe = $result1->fetch_array()){
            $existeal = existenciamp($rowe['rd_articulo'],$rowe['rd_almacen']);
            $sqld = 'UPDATE recepcionesd SET rd_existenciaant = "'.$existeal.'" WHERE
                    rd_recepcion = "' .  $rowe['r_id'] . '" AND rd_id = "' . $rowe['rd_id'] . '" ';
            setq($sqld);
          }
          $result1 = setq($sql) or die($sql);
          $poriva = busca('1','configuracionesp','c_id','c_iva')/100;
          while ($row = $result1->fetch_array()) {
            $costo = $row['rd_costo']/$row['rd_cantidad'];
            if($row['rd_iva'] == "1") $costo*(1+$poriva);
            $canti =0;
            $tipomp = busca($row['rd_articulo'], 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
            if($tipomp == "A"){
              //echo 'articulo: '.$row['rd_articulo'].' largo: '.$row['rd_largo'].' ancho: '.$row['rd_ancho'].'<br>';
              $canti += ($row['rd_cantidad']*$row['rd_largo']*$row['rd_ancho']);
            } else if($tipomp == "V"){
              //echo 'articulo: '.$row['rd_articulo'].' largo: '.$row['rd_largo'].' ancho: '.$row['rd_ancho'].' alto: '.$row['rd_alto'].'<br>';
              $canti += ($row['rd_cantidad']*$row['rd_largo']*$row['rd_ancho']*$row['rd_alto']);
            } else if($tipomp == "P"){
              $canti += $row['rd_cantidad'];
            }
            //echo 'articulo: '.$row['rd_articulo'].' cantidad: '.$canti.' almacen: '.$row['rd_almacen'].' tipo: '.$tipomp.'<br>';
            sumaexistenciamp($row['rd_articulo'],$canti,$row['rd_almacen'],$costo,"R",$id,date('Y-m-d'));
          }
        }else{
          $sql = 'SELECT r_id,rd_articulo,rd_almacen,rd_id,rd_costo,rd_iva,rd_cantidad,rd_largo,rd_ancho,rd_alto
                  FROM recepcionesd INNER JOIN recepciones ON r_id = rd_recepcion
                  WHERE r_estatus = "P" AND r_id = "'.$id.'"';
          $result1 = setq($sql) or die($sql);
          $ok = 1;
          while($rowv = $result1->fetch_array()){
            $sqlrd = 'SELECT * FROM recepcion_activo WHERE ra_recepcion = "' .  $rowv['r_id'] . '"
                      AND ra_idr = "' . $rowv['rd_id'] . '"';
            $resultrd = setq($sqlrd);
            while($rowrd = $resultrd->fetch_array()){
              if(busca($rowrd['ra_activo'],'activo_almacen','aa_id','aa_estatus') != "N") $ok = 0;
            }
          }

          if($ok == 1){
            $result1 = setq($sql) or die($sql);
            while($rowe = $result1->fetch_array()){
              $existeal = existenciamp($rowe['rd_articulo'],$rowe['rd_almacen']);
              $sqld = 'UPDATE recepcionesd SET rd_existenciaant = "'.$existeal.'" WHERE
                      rd_recepcion = "' .  $rowe['r_id'] . '" AND rd_id = "' . $rowe['rd_id'] . '" ';
              setq($sqld);
            }
            $result1 = setq($sql) or die($sql);
            $poriva = busca('1','configuracionesp','c_id','c_iva')/100;
            while ($row = $result1->fetch_array()) {
              $costo = $row['rd_costo']/$row['rd_cantidad'];
              if($row['rd_iva'] == "1") $costo*(1+$poriva);

              $sqlrd = 'SELECT * FROM recepcion_activo WHERE ra_recepcion = "' .  $row['r_id'] . '"
                        AND ra_idr = "' . $row['rd_id'] . '"';
              $resultrd = setq($sqlrd);
              while($rowrd = $resultrd->fetch_array()){
                sumaexistenciamp($row['rd_articulo'],1,$row['rd_almacen'],$costo,"R",$id,date('Y-m-d'));
                $sqlua = 'UPDATE activo_almacen SET aa_estatus = "L" WHERE aa_id = "'.$rowrd['ra_activo'].'"';
                setq($sqlua);
              }
            }
          }
          else{
            die(alert_back("Error: Revisa los activos, existen activos que no estan disponibles",true));
          }
        }
        
          include_once('cxpagar.php');
          $cxp = new modelcxpagar();
          $cxp->insertcxpagar($this->empresa,$this->proveedor,$this->id,"C",$this->total,date('Y-m-d H:i:s'),"ORDEN DE COMPRA ".$this->foliodoc);
       
        $sql3 = 'UPDATE recepciones SET r_estatus = "A", r_fechaapli = "'.date('Y-m-d H:i:s').'", r_uapli = "'.$_SESSION['uid'].'"
                WHERE r_id="' . $id . '"';
        setq($sql3);

        $ordenc = busca($id,'recepciones','r_id','r_ordenc');
        if($ordenc){
          $cantidadoc = busca($ordenc,'ordenescd','od_ordenc','SUM(od_cantidad)');
          $cantidadrc = busca($ordenc,'recepcionesd  INNER JOIN recepciones ON rd_recepcion = r_id','r_estatus = "A" AND r_ordenc','SUM(rd_cantidad)');
          if($cantidadoc == $cantidadrc){ 
            $sql4 = 'UPDATE ordenesc SET o_estatus = "F" WHERE o_id = "'.$ordenc.'"';
            setq($sql4);
          }
        }
      }
    }
    function aplicarcargar($id){
      $estatus = busca($id,'recepciones','r_id','r_estatus');
      $select = 'SELECT * FROM recepcionesd INNER JOIN recepciones ON rd_recepcion = r_id WHERE rd_recepcion = "'.$id.'"';
      $results = setq($select);
      $viva = busca(1,'configuracionesp','c_id','c_iva')/100;
      while($row = $results->fetch_array()){
        $iva = $row['rd_iva'];
        $articulo = $row['rd_articulo'];
        $existeart = busca($row['rd_articulo'],'pr_materiaprima_proveedor','mpp_proveedor = "'.$row['r_proveedor'].'" AND mpp_activo = "1" AND mpp_materiaprima','mpp_id');
        if($existeart){ //existeart
          $sqlup = 'UPDATE pr_materiaprima_proveedor SET
                    mpp_activo = "0",
                    mpp_estatus = "0"
                    WHERE mpp_id = "'.$existeart.'"';
          setq($sqlup) or die("1");
          $noparte = busca($existeart,'pr_materiaprima_proveedor','mpp_id','mpp_noparteprov');//funcion para no perder el numero de parte del proveedor
          $sqlins = 'INSERT INTO pr_materiaprima_proveedor SET
                    mpp_materiaprima = "'.$row['rd_articulo'].'",
                    mpp_proveedor = "'.$row['r_proveedor'].'",
                    mpp_activo = "1",
                    mpp_costo = "'.$row['rd_costo'].'",
                    mpp_iva = "'.$row['rd_iva'].'",
                    mpp_noparteprov = "'.$noparte.'",
                    mpp_estatus = "1"';
          setq($sqlins) or die("2");
        }else{
          $sqlins = 'INSERT INTO pr_materiaprima_proveedor SET
                    mpp_materiaprima = "'.$row['rd_articulo'].'",
                    mpp_proveedor = "'.$row['r_proveedor'].'",
                    mpp_activo = "0",
                    mpp_costo = "'.$row['rd_costo'].'",
                    mpp_iva = "'.$row['rd_iva'].'",
                    mpp_estatus = "1"';
          setq($sqlins) or die("3");
        } /// existeart
        /* $sql = 'SELECT * FROM esquema_precio WHERE ep_estatus = "A"';
        $result = setq($sql); 
        while($rowesq = $result->fetch_array()){
          //$sqlprecio = 'SELECT * FROM articulo_precio WHERE ap_articulo = "'.$row['rd_articulo'].'" AND ap_esquema = "'.$rowesq['ep_id'].'" AND ap_activo = "1"';
          if($row['rd_iva'] == "1") $costo = $row['rd_costo']*1.16;

          if($rowesq['ep_base'] == "C") $base = $costo;
          else $base = busca($rowesq['ep_precio'],'articulos_precios','ap_activo = "1" AND ap_articulo = "'.$articulo.'" AND ap_esquema','ap_precio');

          $utilidad = $base*($rowesq['ep_sumarp']/100);

          if($rowesq['ep_masmenos'] == "+") $precio = ($base+$utilidad)+$rowesq['ep_sumarf'];
          else $precio = ($base-$utilidad)+$rowesq['ep_sumarf'];

          if($rowesq['ep_miva'] == 1) $preciofinal = $precio * (1+$viva);
          //else $iva = 0;

          //$preciofinal = $precio+$iva;

          $apactivo = busca($rowesq['ep_id'],'articulos_precios','ap_activo = "1" AND ap_articulo = "'.$row['rd_articulo'].'" AND ap_esquema','ap_id');
          $sqlup = 'UPDATE articulos_precios SET 
                    ap_activo = "0"
                    WHERE ap_id = "'.$apactivo.'"';
          setq($sqlup);

          $sqlin = 'INSERT INTO articulos_precios SET
                    ap_articulo = "'.$row['rd_articulo'].'",
                    ap_esquema = "'.$rowesq['ep_id'].'",
                    ap_precio = "'.$preciofinal.'",
                    ap_costo = "'.$row['rd_costo'].'",
                    ap_activo = "1",
                    ap_fecha = "'.date('Y-m-d H:i:s').'",
                    ap_user = "'.$_SESSION['uid'].'",
                    ap_talla = "0"';
          setq($sqlin);
        } */
      }
      $sql3 = 'UPDATE recepciones SET r_estatus = "P", r_fechaapli = "'.date('Y-m-d H:i:s').'", r_uapli = "'.$_SESSION['uid'].'"
      WHERE r_id="' . $id . '"';
      setq($sql3);
      if($this->ordenc == "0" || $this->ordenc == NULL){
        include_once('cxpagar.php');
        $cxp = new modelcxpagar();
        $idcp = $cxp->insertcxpagar('1',$this->proveedor,$this->id,"E",$this->total,date('Y-m-d H:i:s'),"RECEPCIÓN DE MERCANCÍA ".$this->folio);
        $cxp->addproyeccion($idcp);
      }
      $tiporec = busca($id,'recepciones','r_id','r_tiporec');
      if($tiporec == "A"){
        $sql = 'SELECT r_id,rd_articulo,r_almacen,rd_id,rd_costo,rd_iva,rd_cantidad,rd_largo,rd_ancho,rd_alto
                FROM recepcionesd INNER JOIN recepciones ON r_id = rd_recepcion
                WHERE r_estatus = "P" AND r_id = "'.$id.'"';
        $result1 = setq($sql) or die($sql);
        while($rowe = $result1->fetch_array()){
          $existeal = existenciamp($rowe['rd_articulo'],$rowe['r_almacen']);
          $sqld = 'UPDATE recepcionesd SET rd_existenciaant = "'.$existeal.'", rd_almacen = "'.$rowe['r_almacen'].'" 
                  WHERE rd_recepcion = "'.$rowe['r_id'].'" AND rd_id = "'.$rowe['rd_id'] .'"';
          setq($sqld);
        }
        $result1 = setq($sql) or die($sql);
        $poriva = busca('1','configuracionesp','c_id','c_iva')/100;
        while ($row = $result1->fetch_array()) {
          $costo = $row['rd_costo']/$row['rd_cantidad'];
          if($row['rd_iva'] == "1") $costo*(1+$poriva);
          $canti =0;
          $tipomp = busca($row['rd_articulo'], 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
          if($tipomp == "A"){
            //echo 'articulo: '.$row['rd_articulo'].' largo: '.$row['rd_largo'].' ancho: '.$row['rd_ancho'].'<br>';
            $canti += ($row['rd_cantidad']*$row['rd_largo']*$row['rd_ancho']);
          } else if($tipomp == "V"){
            //echo 'articulo: '.$row['rd_articulo'].' largo: '.$row['rd_largo'].' ancho: '.$row['rd_ancho'].' alto: '.$row['rd_alto'].'<br>';
            $canti += ($row['rd_cantidad']*$row['rd_largo']*$row['rd_ancho']*$row['rd_alto']);
          } else if($tipomp == "P"){
            $canti += $row['rd_cantidad'];
          }
          sumaexistenciamp($row['rd_articulo'],$canti,$row['r_almacen'],$costo,"R",$id,date('Y-m-d'));
        }
      }else{
        $sql = 'SELECT r_id,rd_articulo,r_almacen,rd_id,rd_costo,rd_iva,rd_cantidad, rd_largo, rd_ancho, rd_alto
                FROM recepcionesd INNER JOIN recepciones ON r_id = rd_recepcion
                WHERE r_estatus = "P" AND r_id = "'.$id.'"';
        $result1 = setq($sql) or die($sql);
        $ok = 1;
        while($rowv = $result1->fetch_array()){
          $sqlrd = 'SELECT * FROM recepcion_activo WHERE ra_recepcion = "' .  $rowv['r_id'] . '"
                    AND ra_idr = "' . $rowv['rd_id'] . '"';
          $resultrd = setq($sqlrd);
          while($rowrd = $resultrd->fetch_array()){
            if(busca($rowrd['ra_activo'],'activo_almacen','aa_id','aa_estatus') != "N") $ok = 0;
          }
        }

        if($ok == 1){
          $result1 = setq($sql) or die($sql);
          while($rowe = $result1->fetch_array()){
            $existeal = existenciamp($rowe['rd_articulo'],$rowe['r_almacen']);
            $sqld = 'UPDATE recepcionesd SET rd_existenciaant = "'.$existeal.'" WHERE
                    rd_recepcion = "' .  $rowe['r_id'] . '" AND rd_id = "' . $rowe['rd_id'] . '" ';
            setq($sqld);
          }
          $result1 = setq($sql) or die($sql);
          $poriva = busca('1','configuracionesp','c_id','c_iva')/100;
          while ($row = $result1->fetch_array()) {
            $costo = $row['rd_costo']/$row['rd_cantidad'];
            if($row['rd_iva'] == "1") $costo*(1+$poriva);

            $sqlrd = 'SELECT * FROM recepcion_activo WHERE ra_recepcion = "' .  $row['r_id'] . '"
                      AND ra_idr = "' . $row['rd_id'] . '"';
            $resultrd = setq($sqlrd);
            while($rowrd = $resultrd->fetch_array()){
              sumaexistenciamp($row['rd_articulo'],1,$row['r_almacen'],$costo,"R",$id,date('Y-m-d'));
              $sqlua = 'UPDATE activo_almacen SET aa_estatus = "L" WHERE aa_id = "'.$rowrd['ra_activo'].'"';
              setq($sqlua);
            }
          }
        }
        else{
          die(alert_back("Error: Revisa los activos, existen activos que no estan disponibles",true));
        }
      }
      /*
        include_once('cxpagar.php');
        $cxp = new modelcxpagar();
        $cxp->insertcxpagar($this->empresa,$this->proveedor,$this->id,"C",$this->total,date('Y-m-d H:i:s'),"ORDEN DE COMPRA ".$this->foliodoc);
      */
      $sql3 = 'UPDATE recepciones SET r_estatus = "A", r_fechaapli = "'.date('Y-m-d H:i:s').'", 
              r_uapli = "'.$_SESSION['uid'].'" WHERE r_id="' . $id . '"';
      setq($sql3);

      $ordenc = busca($id,'recepciones','r_id','r_ordenc');
      if($ordenc){
        $cantidadoc = busca($ordenc,'ordenescd','od_ordenc','SUM(od_cantidad)');
        $cantidadrc = busca($ordenc,'recepcionesd  INNER JOIN recepciones ON rd_recepcion = r_id','r_estatus = "A" AND r_ordenc','SUM(rd_cantidad)');
        if($cantidadoc == $cantidadrc){ 
          $sql4 = 'UPDATE ordenesc SET o_estatus = "F" WHERE o_id = "'.$ordenc.'"';
          setq($sql4);
        }
      }
    }
    function delprod($id,$idprod){
      $sql = 'DELETE FROM recepcionesd WHERE rd_recepcion = "'.$id.'" AND rd_id = "'.$idprod.'"';
      setq($sql);
    
      $sql = 'DELETE FROM recepcion_activo WHERE ra_recepcion = "'.$id.'" AND ra_idr = "'.$idprod.'"';
      setq($sql);
    }
    function checkactivos($id,$idrec,$almacen){
      $sqlp = 'SELECT rd_articulo,rd_cantidad FROM recepcionesd
                WHERE rd_recepcion = "'.$id.'" AND rd_id = "'.$idrec.'"';
      $resultp = setq($sqlp);
      list($prod,$cantidad) = $resultp->fetch_array();

      $sqlac = 'SELECT ap_activo FROM activo_producto WHERE ap_producto = "'.$prod.'"';
      $resultac = setq($sqlac);
      $numacs = 0;
      while($rowac = $resultac->fetch_array()){
        $sqlna = 'SELECT COUNT(*) FROM activo_almacen WHERE aa_almacen = "'.$almacen.'"
                  AND aa_activo = "'.$rowac['ap_activo'].'" AND aa_estatus = "N"';
        $resultna = setq($sqlna);
        list($actives) = $resultna->fetch_array();
        if(!$actives) $actives = 0;
        $numacs+=$actives;
      }
      if($numacs >= $cantidad){
        $sqlaac = 'SELECT aa_id FROM activo_almacen WHERE aa_almacen = "'.$almacen.'" AND aa_estatus = "N"
                    AND aa_activo IN (SELECT ap_activo FROM activo_producto WHERE ap_producto = "'.$prod.'")
                    ORDER BY aa_noserie';
        $resultaac = setq($sqlaac);
        $vlt = 0;
        while($rowaa = $resultaac->fetch_array()){
          $vlt++;
          if($vlt <= $cantidad)
            $this->setactivorec($id,$idrec,$rowaa['aa_id']);
        }
      }
    }
    function setactivorec($id,$idrec,$idactivo){
      $sql = 'SELECT aa_estatus FROM activo_almacen WHERE aa_id = "'.$idactivo.'"';
      $result = setq($sql);
      list($estatus) = $result->fetch_array();
      if($estatus == "N"){
        $sqli = 'INSERT INTO recepcion_activo SET
                  ra_recepcion = "'.$id.'",
                  ra_idr = "'.$idrec.'",
                  ra_activo = "'.$idactivo.'"';
        setq($sqli);
      }
    }
    function setprecio($id){
      $pordes = busca($id,'recepciones','r_id','r_descuento');
      $viva = busca(1,'configuracionesp','c_id','c_iva')/100;
      $diva = busca($id,'recepciones','r_id','r_diva');

      $sql = 'SELECT * FROM recepcionesd WHERE rd_recepcion = "'.$id.'"';
      $result = setq($sql);

      $total = 0;
      $totiva = 0;
      $subtotal = 0;
      $iva = 0;
      $descuentot = 0;
      $subtotalfin = 0;
      
      while($row = $result->fetch_array()){
        $precio = $row['rd_costo'];
        $importe = $precio*$row['rd_cantidad'];
        $descuni = $importe*($pordes/100);
        $impmd = $importe-$descuni;
        $subtotal+=$impmd;
        if($diva == "1"){
          if($row['rd_iva'] == "1"){
            $iva=$impmd*($viva);
          }else{
            $iva= 0;
          }
        }else{
          $iva = 0;
        }
        $descuentot+=$descuni;
        $subtotalfin+= $importe;
        $totiva+=$iva;

      }
      $total = $subtotal+$totiva;
      $sql = 'UPDATE recepciones SET r_subtotal = "'.$subtotalfin.'", r_iva = "'.$totiva.'", 
              r_total = "'.$total.'", r_mdescuento = "'.$descuentot.'"
              WHERE r_id = "'.$id.'" ';
      setq($sql);
    }
  }

  class viewrecepciones {
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->tipodoc = array("F"=>"Factura","R"=>"Remisión");
    }
    function browse($fini,$ffin,$proveedor,$estatus,$documento,$page) {
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
              $("#proveedor").focus();
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
        $this->model->aestatus = array("A" => "Finalizado","P" => "Asignación de Almacen","N" => "En Captura","C" => "Cancelado");
        $this->model->nestatus = array("A" => "btn-success","P" => "bg-blue bg-darken-2","N"=>"bg-yellow bg-darken-3","C" => "btn-danger");
        $accion = 'insert';
        if (isset($this->model->id)) {$accion = 'update';}
        //Sección: E1 Encabezado - Botones de acción
        echo '<div class="row page-title-actions">';
          //Sección: E2 Encabezado - Filtros
          $selt = ''; $sela = ''; $seln = ''; $selp = ''; $selc = '';
          if($estatus == "A") $sela = "selected";
          if($estatus == "N") $seln = "selected";
          if($estatus == "P") $selp = "selected";
          if($estatus == "C") $selc = "selected";
          else $selt = 'selected';
        $nuevo = '
            <a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setrecepcion.php" href="javascript:;">
              <button type="button" class="btn btn-sm btn-primary">
                <i class="fa fa-plus"></i> Nuevo
              </button>
            </a>';

          $filtro = '
                <form class="form-inline" role="form" method="post" id="filtro">
                <input name="page" id="page" value="'.$page.'" hidden> 
                  <div class="mb-5">
                    <label for="tipom">Desde:</label>
                    <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
                    <input hidden type="date" id="fini2" class="form-control" value="'.date('Y-m-d',strtotime('-60 days')).'" />
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Hasta:</label>
                    <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
                    <input hidden type="date" id="ffin2" class="form-control" value="'.date('Y-m-d').'" />
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Proveedor:</label>
                    <input type="text" class="form-control" id="proveedor" name="proveedor" value="'.$proveedor.'" placeholder="Nombre del proveedor que buscas" onfocus="this.select();"/>
                    <script>
                      function ceropage(){
                        document.getElementById("page").value = 0;
                        var codigo = event.which || event.keyCode;
                        if(codigo === 13) {
                          mandar(0);
                        }
                      }
                    </script>
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Estatus:</label>
                    <select class="form-control" name="estatus" id="estatus" >
                      <option value=""'.$selt.'>Todos</option>
                      <option value="N"'.$seln.'>En captura</option>
                      <option value="A"'.$sela.'>Aplicados</option>
                      <option value="P"'.$selp.'>Espera de aplicación</option>
                      <option value="C"'.$selc.'>Cancelados</option>
                    </select>
                  </div><!-- form group [search] -->
                  <div class="mb-5">
                    <label for="">Acciones:</label><br>
                    <button type="button" onclick="mandar(0)" class="btn btn-sm btn-info">
                      <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                    </button>
                    <!-- <a href="?modulo=recepciones&accion=index"> -->
                      <button type="button" id="limpiar" onclick="mandar(1)" class="btn btn-sm btn-warning">
                      <!-- <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-warning"> -->
                        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                      </button>
                    <!-- </a> -->
                  </div>
                </form>
              ';
          ?>
        </div>

      <?php
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
                      <th><b>Folio</b></th>
                      <th><b>Proveedor</b></th>
                      <th style="width: 20%;"><b>Tipo</b></th>
                      <th><b>Último Movimiento</b></th>
                      <th><b>Almacen</b></th>
                      <th><b>Folio del documento</b></th>
                      <th><b>Estatus</b></th>
                      <th></th>
                    </tr>
                  </thead>';
                echo '</table>
              </center>
            </div>
            </div>
        </div>
      ';

      echo '<script>
      function mandar(id){
        var fini = document.getElementById("fini");
        var ffin = document.getElementById("ffin");
        var proveedor = document.getElementById("proveedor");
        var estatus = document.getElementById("estatus");

        if(id == 1){
          fini.value = document.getElementById("fini2").value;
          ffin.value = document.getElementById("ffin2").value;
          proveedor.value = "";
          estatus.value = "";
        }

        $("#myTable").DataTable().clear().draw();
        $("#myTable").DataTable().destroy();

        $("#myTable").DataTable({
          paging: true,
          scrollY: 400,
          processing: true,
          serverside: true,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatablerecepciones.php",
            type: "POST",
            datatype: "json",
            data:{ 
              "fini": fini.value,
              "ffin": ffin.value,
              "proveedor": proveedor.value,
              "estatus" : estatus.value
            }
          },
          pageLength: "50",
          responsivePriority: 1,
          order: [[0, "desc"]], // Ordenar en orden descendente por la columna 0
        });
        $("#myTable").on("draw.dt", function () {
          aplicarEstiloColumna(6);
        });
      }

      function aplicarEstiloColumna(col) {
        var columnaNumero = col;
        $("#myTable tbody tr").each(function() {
          var celdas = $(this).find("td");
          if (celdas.length > columnaNumero) {
            celdas.eq(columnaNumero).css("width", "100px");
            celdas.eq(columnaNumero).css("height", "34px");
            celdas.eq(columnaNumero).css("display", "table-cell");
            celdas.eq(columnaNumero).addClass("btn");
            celdas.eq(columnaNumero).addClass("no-border");
            celdas.eq(columnaNumero).addClass("text-middle");
            celdas.eq(columnaNumero).addClass("btn-sm");

            var txtCelda = celdas.eq(columnaNumero).html();
            if(txtCelda == "Finalizado"){
              celdas.eq(columnaNumero).addClass("btn-success");
            } else if(txtCelda == "Asignación de Almacen"){
              celdas.eq(columnaNumero).addClass("btn-primary");
              celdas.eq(columnaNumero).addClass("bg-darken-2");
            } else if(txtCelda == "En Captura"){
              celdas.eq(columnaNumero).addClass("btn-warning");
              celdas.eq(columnaNumero).addClass("bg-darken-3");
            } else{
              celdas.eq(columnaNumero).addClass("btn-danger");
            }
          }
        });
      }
      document.addEventListener("DOMContentLoaded", function() {
        mandar(0);
      });

    </script>';
      echo '
      <script>
      function imprimirr(id){
        window.open("formats/printrecepcion?id="+id, "_blank");
      }
      </script>';
    }
    function show(){
      ?>
      <script>
        $(document).ready(function() {
          $('#producto').on('keyup', function() {
            if (event.keyCode == 38 || event.keyCode == 40){
            }else{
              var key = $(this).val();
              var dataString = 'producto='+key;
              $.ajax({
              type: "POST",
              url: "query/suggestmp.php",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  var valor = $(this).attr('value');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  $.ajax({
                    type: "POST",
                    url: "query/checkmp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var largo = document.getElementById('largodiv');
                      var ancho = document.getElementById('anchodiv');
                      var alto = document.getElementById('altodiv');
                      var largoin = document.getElementById('largo');
                      var anchoin = document.getElementById('ancho');
                      var altoin = document.getElementById('alto');
                      data = JSON.parse(dataRTN);
                      if(data['tipo'] == "A"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = true;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.removeAttribute('min');
                      } else if(data['tipo'] == "V"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = false;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.setAttribute('min', '0.01');
                      } else if(data['tipo'] == "P"){
                        largo.hidden = true;
                        ancho.hidden = true;
                        alto.hidden = true;
                        largoin.removeAttribute('min');
                        anchoin.removeAttribute('min');
                        altoin.removeAttribute('min');
                      }
                    }
                  });
                  $.ajax({
                    type: "POST",
                    url: "query/setpreciomp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var costo = document.getElementById('costo');
                      costo.value=dataRTN;
                    }
                  });
                  $("#cantidad").focus();
                  return false;
                });
              }
            });
            }
          });
        });
      </script>
      <?php
      if($this->model->ordenc == "0") {$disabledd = ""; $hiddenx = "";}
      else {$disabledd = "disabled"; $hiddenx = "hidden";}
      if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
      if ($_GET['alerta'] == 1) {
        echo alert("El articulo no Existe", true);
      }
      if($this->model->cargar == "1") $texto = "Al aplicar esta recepción, se verá afectado el inventario y se recalcularán costos"; else $texto = "Al aplicar esta recepción se recalcularán costos y precios";
      $aplicar = "aplicar";
      echo '<script>
        function delprod(idprod){
          Swal.fire({
            title: "Atención",
            html: "¿Deseas borrar el producto de la recepción?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Continuar"
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href="?modulo=recepciones&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
            }
          })
        }
        function aplicamov(){
          Swal.fire({
            title: "Atención",
            html: "'.$texto.'<br>¿Deseas continuar?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Continuar"
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href="?modulo=recepciones&accion='.$aplicar.'&id='.$this->model->id.'";
            }
          })
        }
      </script>';
      $aplicar = "";
      $importar = "";
      $imprimir = "";

      $atras = '<a href="?modulo=recepciones&accion=index" accesskey="">
        <button type="button" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i> Atrás </button>
      </a>';
      if($this->model->estatus == "P" || $this->model->estatus == "A") $btnedit = "";
      else $btnedit = '<a class="btn btn-sm btn-primary text-white" data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?id='.$_GET['id'].'" href="javascript:;"><i class="fas fa-edit"></i> Editar</a>&nbsp;';
      if ($this->model->comprueba($this->model->id) == 1 AND $this->model->estatus != "A") {
        $aplicar = '<button class="btn btn-sm btn-success text-white" onclick="aplicamov()"><i class="fa fa-check"></i> Aplicar</button>';
      }
      if($this->model->estatus == "A")
        $imprimir = '<a target="_BLANK" href="formats/printrecepcion.php?id='.$this->model->id.'">
          <button type="button" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Imprimir</button>
        </a>';
      if($this->model->ordenc != 0 AND $this->model->estatus == "N")
        $importar = '&nbsp;<a href="popup/importarprodcompras.php?ordenc='.$this->model->ordenc.'&recepcion='.$_GET['id'].'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
          <button type="button" class="btn btn-sm btn-secondary"><i class="fas fa-download"></i>Importar producto</button>
        </a>'; 

      toolbar($_GET['modulo'],$atras, '', $btnedit, $aplicar.$imprimir.$importar);



      if($this->model->estatus == "A"){
        //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
      }
      if($this->model->estatus == "N") $leyenda = 'Añadir productos a la recepción ';
      else  $leyenda = 'Lista de productos en la recepción ';
      $readon = "";
      $exisantm = "";
      $miva = "";

      if($this->model->estatus == "N"){
        echo '<div class="card mt-3">';
        echo '<div class="card-body">
          <form method="post" class="row" autocomplete="off" action="?modulo=recepciones&accion=insertdetalle&id='.$this->model->id.'">
            <div class="mt-1 col-8 col-md-8 alert alert-primary">
              '.$leyenda.' - '.$this->model->folio.'
            </div>
            <div class="mt-1 col-4 col-md-4 alert alert-info">Almacen: '.busca($this->model->almacen,'almacenes','a_id','a_nmb').'</div>
            <div class="col-8 col-md-3" '.$hiddenx.'>
              <div class="mb-5">
                <label for="agregar" class">Producto</label><br>
                <div class="input-group">
                  <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus '.$disabledd.'>
                  <!-- <a class="input-group-addon" href="popup/productos-buscar.php?id='.$this->model->id.'&from=ordenesc" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                    <button accesskey="B" type="button" class="btn btn-primary"><i class="fas fa-search"></i></button>
                  </a> -->
                </div>
                <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: scroll;"></div>
              </div>
            </div>
            <div class="col-4 col-md-2" '.$hiddenx.' >
              <div class="mb-5">
                <label for="cant">Cantidad</label><br>
                <input type="number" min="0" max="9999999" step="0.01" value="1" id="cantidad" name="cantidad" placeholder="Cantidad" class="form-control" required="required" '.$disabledd.' />
              </div>
            </div>
            <div class="mb-5 col-12 col-md-2 ms-5" id="largodiv" hidden>  
              <label>Largo:</label>
              <input type="number" min="0.01" step="0.01" id="largo" class="form-control" data-toggle="tooltip" data-placement="top" name="largo" value="0" onfocus="this.select();">
            </div>
            <div class="mb-5 col-12 col-md-2" id="anchodiv" hidden>  
              <label>Ancho:</label>
              <input type="number" min="0.01" step="0.01" id="ancho" class="form-control" data-toggle="tooltip" data-placement="top" name="ancho" value="0" onfocus="this.select();">
            </div>
            <div class="mb-5 col-12 col-md-2" id="altodiv" hidden>  
              <label>Alto:</label>
              <input type="number" min="0.01" step="0.01" id="alto" class="form-control" data-toggle="tooltip" data-placement="top" name="alto" value="0" onfocus="this.select();">
            </div>
            <div class="col-4 col-md-3" '.$hiddenx.' >
              <div class="mb-5">
                <label for="cant">Costo Unitario </label><br><!-- (Antes de IVA) -->
                <input type="number" min="0" max="9999999" step="0.01" id="costo" name="importe" placeholder="Importe del concepto" class="form-control" required="required" '.$disabledd.' />
                <!-- <input type="number" min="0" max="9999999" step="0.01" id="importe" name="importe" placeholder="Importe del concepto" class="form-control" required="required" '.$disabledd.' /> -->
                <input type="number" min="0" max="9999999" step="0.01" id="importe" placeholder="Importe del concepto" class="form-control" required="required" '.$disabledd.' hidden disabled/>
              </div>
            </div>
            <div class="col-4 col-md-1" '.$hiddenx.' hidden>
              <div class="mb-5">
                <label for="cant">+IVA</label><br>
                <input type="checkbox" name="iva" '.$miva.' '.$disabledd.' />
              </div>
            </div>
            <input type="text" name="linean" id="linean" hidden>
            <div class="col-12 col-md-2" '.$hiddenx.' >
              <div class="mb-5">
                <label></label><br>
                <button type="submit" class="btn btn-sm btn-success" '.$disabledd.'><i class="fas fa-paper-plane"></i> Agregar producto</button>
              </div>
            </div>
          </form>';
      }else{
        echo '<div class="mt-1 col-8 col-md-8 alert alert-primary">
          '.$leyenda.' - '.$this->model->foliodoc.'
        </div>';
        $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
        $exisantm = "Existencia antes del movimiento";
      }
      echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
        <thead class="thead bg-primary text-white">
            <tr>
              <th width="30%"><strong>Artículo</strong></th>';
              if($this->model->tiporec == "C")
                echo '<th><strong>Activos ligados</strong></th>';
              echo '<th><strong>Piezas Compradas</strong></th>
              <th><strong>Precio unitario</strong></th>
              <th><strong>+IVA</strong></th>
              <th><strong>Importe total</strong></th>
              <th>'.$exisantm.'</th>
            </tr>
          </thead>';
          $subtotal = 0;
          $totiva = 0;
          $iva = 0;
          $poriva = busca(1,'configuracionesp','c_id','c_iva')/100;
          $desc = 0;
          if($this->model->estatus == "A") $readonly = "disabled";
          $i = 0;
          echo '
          <script>
            function cambiarcantidad(id,recepcion){
              var cant = document.getElementById("rdcantidad").value;
              window.location.href="?modulo=recepciones&accion=cambiarcantidad&id="+id+"&cantidad="+cant+"&recepcion="+recepcion;
            }
          </script>';
          while($row = $this->model->resultr->fetch_array()){
            $anmb = $row['pmp_nmb'];
            if($row['pmp_tipo'] == "A") $anmb .=' Medidas: '.$row['rd_largo'].' X '.$row['rd_ancho'];
            else if($row['pmp_tipo'] == "V") $anmb .=' Medidas: '.$row['rd_largo'].' X '.$row['rd_ancho'].' X '.$row['rd_alto'];
            $costo = $row['rd_costo'];
            if($row['rd_iva'] == "1"){
              $chiva = "checked";
              $importe = $costo * $row['rd_cantidad'];
              $iva = ($importe*$poriva);
              $desc+= ($importe)*($this->model->descuento/100);
            }else{
              $chiva = "";
              $importe = $costo * $row['rd_cantidad'];
              $iva = 0;
              $desc+= ($importe)*($this->model->descuento/100);
            }
            echo'<script>
              function updatecant'.$row['rd_id'].$concat.'(){
                var cantidad = document.getElementById("rdcantidad'.$row['rd_id'].$concat.'").value;
                var newcant = document.getElementById("newcant'.$row['rd_id'].$concat.'").value = cantidad;
                document.updatecantidad'.$row['rd_id'].$concat.'.submit();
              }
            </script>';
            echo '<form name="updatecantidad'.$row['rd_id'].$concat.'" method="post" action="?modulo=recepciones&accion=cambiarcantidad&id='.$row['rd_id'].'&recepcion='.$_GET['id'].'">
              <input type="hidden" name="cantidad" id="newcant'.$row['rd_id'].$concat.'" />
            </form>';
            $totiva+=$iva; 
            $subtotal+=$importe;  
            echo '<tr>
            <!-- <form method="post" action="?modulo=recepciones&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['rd_id'].'"> -->
                <td>'.$anmb.'</td>';
                if($this->model->tiporec == "C"){
                  echo '<td>';
                    if(busca($row['rd_articulo'],'activo_producto','ap_producto','COUNT(*)') > 0){
                      $numact = busca($this->model->id,'recepcion_activo','ra_idr = "'.$row['rd_id'].'" AND ra_recepcion','COUNT(*)');
                      echo '<a href="popup/elijeactivos?modulo='.$_GET['modulo'].'&accion=seleccionar&recepcion='.$this->model->id.'&idrec='.$row['rd_id'].'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                        <button type="button" class="btn-sm bg-teal bg-darken-2 text-white"><i class="fas fa-mug-hot"></i> Seleccionar activos
                          <div class="tag tag-pill tag-danger">'.$numact.'</div>
                        </button>
                      </a>';
                    }
                    else echo 'Sin activos ligados';
                  echo '</td>';
                }
                echo '
                <form method="post" action="?modulo=recepciones&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['rd_id'].'">
                <td>
                  <input value="'.$row['rd_costo'].'" name="importebd" hidden>
                  <input id="rdcantidad'.$row['rd_id'].$concat.'" type="number" onchange="updatecant'.$row['rd_id'].$concat.'();" value="'.number_format($row['rd_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>
                <!-- <input type="number" value="'.number_format($row['rd_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' hidden/> -->
                <td class="number-align">
                  <input type="number" value="'.number_format($costo,2,'.','').'" min="0" max="9999999" step="0.01" name="importe" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>';
                if($this->model->diva == "1")
                  echo'<td><input type="checkbox" name="iva" '.$chiva.' '.$readon.' /></td>';
                else 
                  echo'<td><input type="hidden" name="iva" value="'.$row['rd_iva'].'"/></td>';
                echo'<td>
                  <input type="number" value="'.number_format($importe,2,'.','').'" min="0" max="9999999" step="0.01" name="importetotal" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" readonly="readonly" />
                </td> 
                <td>';
                  if($this->model->estatus == "N")
                    echo '
                    <div style="display: -webkit-box;">
                      <div>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-redo"></i></button>
                      </div>
                      <div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="delprod('.$row['rd_id'].')"><i class="fa fa-trash"></i></button>
                      </div>
                    </div>';
                  else
                    echo $row['rd_existenciaant'];
                echo '</td>
              </form>
            </tr>';
          }
          if($this->model->diva == "1"){
            echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
              <td colspan="3">&nbsp;</td><td colspan="1">Subtotal</td>
              <td class="number-align">$ '.number_format(($subtotal),2).'</td>
              <td>&nbsp;</td>
            </tr>';
            if($desc > 0){
              echo '<tr class="p-1 bg-grey bg-lighten-3 h4">
                <td colspan="3">&nbsp;</td><td colspan="1">Descuento</td>
                <td class="number-align">$ '.number_format($desc,2).'</td>
                <td>&nbsp;</td>
              </tr>';
            }
            echo '<tr class="p-1 bg-grey bg-lighten-4 h4">
              <td colspan="3">&nbsp;</td><td colspan="1">IVA</td>
              <td class="number-align">$ '.number_format($this->model->iva,2).'</td>
              <td>&nbsp;</td>
            </tr>';
            echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
              <td colspan="3">&nbsp;</td><td colspan="1">Total</td>
              <td class="number-align">$ '.number_format($this->model->total,2).'</td>
              <td>&nbsp;</td>
            </tr>';
          }else{
            if($desc > 0)
              echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
                <td colspan="3">&nbsp;</td><td colspan="1">Subtotal</td>
                <td class="number-align">$ '.number_format(($subtotal),2).'</td>
                <td>&nbsp;</td>
              </tr>
              <tr class="p-1 bg-grey bg-lighten-3 h4">
                <td colspan="3">&nbsp;</td><td colspan="1">Descuento</td>
                <td class="number-align">$ '.number_format($desc,2).'</td>
                <td>&nbsp;</td>
              </tr>';
            echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
              <td colspan="3">&nbsp;</td><td colspan="1">Total</td>
              <td class="number-align">$ '.number_format($this->model->total,2).'</td><td>&nbsp;</td>
            </tr>';
          }
        echo '</table>
      </div>
      </div>';
      ?>
      <script>  
        let listGroup = document.getElementById('suggestions');
        // Asignar evento al campo de texto
        document.querySelector('#producto').addEventListener('keydown', e => {
          if(!listGroup) {
            return; // No existe la lista
          }    
          // Obtener todos los elementos
          let items = listGroup.querySelectorAll('a');
          var a = document.getElementById("suggestions");
          // Saber si alguno está activo
          let actual = Array.from(items).findIndex(item => item.classList.contains('active'));
          //let actual = 0;
          // Analizar tecla pulsada
          if(e.keyCode == 13) {
            // Tecla Enter, evitar que se procese el formulario
            e.preventDefault();
            // ¿Hay un elemento activo?
            if(items[actual]) {
              // Hacer clic
              items[actual].click();
            }
          } 
          if(e.keyCode == 38 || e.keyCode == 40) {
            // Flecha arriba (restar) o abajo (sumar)
            if(items[actual]) {
              // Solo si hay un elemento activo, eliminar clase
              items[actual].classList.remove('active');
              //items[actual].className += " active";
            }
            // Calcular posición del siguiente
            if((e.keyCode == 38)){
              a.scrollTop -= ((items[actual].clientHeight - 1));
              actual += -1;
            }else{
              if(actual > 1) a.scrollTop += ((items[actual-2].offsetHeight + 1));            
              actual += 1;
            }
            //actual += (e.keyCode == 38) ? -1 : 1;
            // Asegurar que está dentro de los límites
            if(actual < 0) {
              actual = 0;
            } else if(actual >= items.length) {
              actual = items.length - 1;
            } 
            // Asignar clase activa
            items[actual].classList.add('active');
            items[actual].setAttribute('autofocus','');
            //items[actual].className += " active";
          }
        });
        // En la función donde generas la lista debes activar evento clic para cada elemento
        // Para este ejemplo se hace manual
        listGroup.querySelectorAll('a').forEach(a => {
          a.addEventListener('click', e => {
            // Asignar valor al campo
            document.querySelector('#producto').value = e.currentTarget.textContent;
            // Aquí deberías cerrar la lista y/o eliminar el contenido
          });
        });
      </script>
      <?php
    }
    function showalm(){
      //  if()
      if($this->model->estatus == "P") {$disabledd = "disabled"; $hiddenxx = "hidden";}
      else {$disabledd = ""; $hiddenxx = "";}

      if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
      if ($_GET['alerta'] == 1) {
        echo alert("El articulo no Existe", true);
      }

      echo '<script>
              function delprod(idprod){
                Swal.fire({
                  title: "Atención",
                  html: "¿Deseas borrar el producto de la recepción?",
                  icon: "warning",
                  showCancelButton: true,
                  confirmButtonColor: "#3085d6",
                  cancelButtonColor: "#d33",
                  cancelButtonText: "Cancelar",
                  confirmButtonText: "Continuar"
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href="?modulo=recepciones&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
                  }
                })
              }
              function aplicamov(){
                Swal.fire({
                  title: "Atención",
                  html: "Al aplicar esta recepción se verá afectado el inventario.<br>¿Deseas continuar?",
                  icon: "warning",
                  showCancelButton: true,
                  confirmButtonColor: "#3085d6",
                  cancelButtonColor: "#d33",
                  cancelButtonText: "Cancelar",
                  confirmButtonText: "Continuar"
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href="?modulo=recepciones&accion=aplicar&id='.$this->model->id.'";
                  }
                })
              }
            </script>';
      $aplicar = "";
      $imprimir = "";
      $importar = "";
      $atras = '<a href="?modulo=recepciones&accion=index" accesskey="">
              <button type="button" class="btn btn-sm btn-warning "><i class="fa fa-arrow-left"></i> Atrás </button>
            </a>';

      if ($this->model->comprueba($this->model->id) == 1 AND $this->model->estatus != "A") {
        $aplicar = '<button class="btn btn-sm btn-success" onclick="aplicamov()"><i class="fa fa-check"></i> Aplicar</button>';
      }
      if($this->model->estatus == "A")
        $imprimir = '<a target="_BLANK" href="formats/printrecepcion.php?id='.$this->model->id.'">
                <button type="button" class="btn btn-sm btn-secondary "><i class="fas fa-print"></i> Imprimir</button>
              </a>';

      if($this->model->ordenc != 0 AND $this->model->estatus == "N")
        $importar = '<a href="popup/importarprodcompras.php?ordenc='.$this->model->ordenc.'&recepcion='.$_GET['id'].'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                <button type="button" class="btn btn-sm btn-secondary"><i class="fas fa-download"></i>Importar producto</button>
              </a>'; 

      toolbar($_GET['modulo'],$atras, '', $aplicar, $imprimir.$importar);
      if($this->model->estatus == "N") $leyenda = 'Añadir productos a la recepción ';
      else  $leyenda = 'Lista de productos en la recepción ';

      

      $readon = "";
      $exisantm = "";
      $miva = "";
      $hiddenx = "";

      if($this->model->estatus == "N")
        echo '<div class="card mt-3">
        <div class="card-body">
          <form method="post" class="row" autocomplete="off" action="?modulo=recepciones&accion=insertdetalle&id='.$this->model->id.'">
                  <div class="mt-1 col-12 col-md-12 alert alert-primary">
                    '.$leyenda.'
                    -
                    '.$this->model->folio.'
                  </div>
                  <div class="mt-1 col-4 col-md-4 alert alert-info">Almacen: '.busca($this->model->almacen,'pr_almacenes','pa_id','pa_nmb').'</div>
                  <div class="col-8 col-md-4" '.$hiddenx.' '.$hiddenxx.'>
                    <div class="mb-5">
                      <label for="agregar" class">Producto</label><br>
                      <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus '.$disabledd.'> 
                    <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: scroll;"></div>
                    </div>
                  </div>
                  <div class="col-4 col-md-2" '.$hiddenx.' '.$hiddenxx.'>
                    <div class="mb-5">
                      <label for="cant">Cantidad</label><br>
                      <input type="number" min="0" max="9999999" step="0.01" id="cantidad" name="cantidad" placeholder="Cantidad" class="form-control" required="required" '.$disabledd.' />
                    </div>
                  </div>
                  <div class="mb-5 col-12 col-md-2 ms-5" id="largodiv" hidden>  
                    <label>Largo:</label>
                    <input type="number" min="0.01" step="0.01" id="largo" class="form-control" data-toggle="tooltip" data-placement="top" name="largo" value="0" onfocus="this.select();">
                  </div>
                  <div class="mb-5 col-12 col-md-2" id="anchodiv" hidden>  
                    <label>Ancho:</label>
                    <input type="number" min="0.01" step="0.01" id="ancho" class="form-control" data-toggle="tooltip" data-placement="top" name="ancho" value="0" onfocus="this.select();">
                  </div>
                  <div class="mb-5 col-12 col-md-2" id="altodiv" hidden>  
                    <label>Alto:</label>
                    <input type="number" min="0.01" step="0.01" id="alto" class="form-control" data-toggle="tooltip" data-placement="top" name="alto" value="0" onfocus="this.select();">
                  </div>
                  <div class="col-4 col-md-3" '.$hiddenx.' '.$hiddenxx.'>
                    <div class="mb-5">
                      <label for="cant">Costo Unitario (Antes de IVA)</label><br>
                      <input type="number" min="0" max="9999999" step="0.01" id="importe" name="importe" placeholder="Importe del concepto" class="form-control" required="required" '.$disabledd.' />
                    </div>
                  </div>
                  <div class="col-4 col-md-1" '.$hiddenx.' '.$hiddenxx.'>
                    <div class="mb-5">
                      <label for="cant">IVA</label><br>
                      <input type="checkbox" name="iva" '.$miva.' '.$disabledd.' />
                    </div>
                  </div>
                  <input type="text" name="linean" id="linean" hidden>
                  <div class="col-12 col-md-2" '.$hiddenx.' '.$hiddenxx.'>
                    <div class="mb-5">
                    <label></label><br>
                      <button type="submit" class="btn btn-success" '.$disabledd.'><i class="fas fa-paper-plane"></i> Agregar producto</button>
                    </div>
                  </div>
                </form>
              </div>';
      else{
        echo '<div class="mt-1 col-12 col-md-12 alert alert-primary">
                '.$leyenda.' - '.$this->model->foliodoc.'
              </div>';
        $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
        $exisantm = "Existencia antes del movimiento";
      }

      echo '<div class="table table-responsive table-hover">
            <table class="table table-hover table-striped">
            <thead class="thead bg-primary text-white pt-1 pb-1">
              <tr>
                <th width="30%">Artículo</th>';
          if($this->model->tiporec == "C")
            echo '<th>Activos ligados</th>';

          echo '<th>Piezas Compradas</th>
                <th '.$hiddenxx.' hidden>Precio unitario</th>
                <th '.$hiddenxx.' hidden>+IVA</th>
                <th '.$hiddenxx.' hidden>Importe total</th>
                <th '.$hiddenxx.' hidden>'.$exisantm.'</th>
                <th>Almacen</th>
                <th>Acciones</th>
              </tr>
            </thead>';
      $subtotal = 0;
      $totiva = 0;
      $poriva = busca(1,'configuracionesp','c_id','c_iva')/100;
      $desc = 0;
      if($this->model->estatus == "A") $readonly = "disabled";
      //var_dump(($this->model->resultr->num_rows));
      //  die();
      $i = 0;
      while($row = $this->model->resultr->fetch_array()){
        
        $anmb = $row['pmp_nmb'];
        if($row['pmp_tipo'] == "A") $anmb .=' Medidas: '.$row['rd_largo'].' X '.$row['rd_ancho'];
        else if($row['pmp_tipo'] == "V") $anmb .=' Medidas: '.$row['rd_largo'].' X '.$row['rd_ancho'].' X '.$row['rd_alto'];

        echo '<tr><form method="post" action="?modulo=recepciones&accion=updatealmacen&id='.$this->model->id.'&idprod='.$row['rd_id'].'">
                <td>'.$anmb.'</td>';

    echo '
    <td>
      <input type="number" value="'.number_format($row['rd_cantidad'],2,'.','').'" min="1" max="'.$row['rd_cantidad'].'" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();"  readonly/>
    </td>
    <td> 
      <select name="almacenprod'.$row['rd_id'].'" class="form-control" required>';
      $sqlalm = 'SELECT * FROM pr_almacenes WHERE pa_estatus = "A" ';
      $resultalm = setq($sqlalm);
      //echo $this->model->almacen.' '.$this->model->tiporec;
      while($rowal = $resultalm->fetch_array()){
        if($rowal['pa_id'] == $row['rd_almacen']) $selected = "selected"; 
        else $selected = "";
        
        echo '<option value="'.$rowal['pa_id'].'" '.$selected.' >'.$rowal['pa_nmb'].'</option>';
      }
      echo '
      </select>
    </td>
    <td>';
    
    /* Actualizar */
    echo '
    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-redo"></i></button>
    <!-- <button type="button" class="btn btn-sm btn-danger" onclick="delprod('.$row['rd_id'].')"><i class="fa fa-trash"></i> Borrar</button> -->'; 
    /* Dividir Por Almacen */
    echo '
    <span data-bs-toggle="modal" data-bs-target="#exampleModal'.$row['rd_id'].$concat.'">
    <button data-bs-toggle="tooltip" data-placement="top" title="Divide en producto y colocalo en diferentes almacenes." 
      type="button" class="btn btn-sm btn-warning" ><i class="fas fa-divide"></i></button>
    </span>';

    echo 
    '</td>
    </form></tr>';

    echo '
    <!-- Modal -->
    <div class="modal fade" id="exampleModal'.$row['rd_id'].$concat.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div style="background: white;" class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">'.$anmb.'</h5>
            <button type="button" class="btn close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
          <form action="?modulo=recepciones&accion=dividir&id='.$this->model->id.'&idprod='.$row['rd_id'].'" method="POST">
            <div class="row">
              <div class="mb-5 col-md-6">
                <label>Cantidad a restar: </label>
                <input type="number" class="form-control" name="restar" id="restar" value="1" min="1" max="'.($row['rd_cantidad']-1).'"> 
              </div>
              <div class="mb-5 col-md-6">
                <label>Almacen de Destino: </label>
                <select name="almacenprod" class="form-control" required>';
                  $sqlalm = 'SELECT * FROM pr_almacenes WHERE pa_estatus = "A" AND pa_id NOT IN (SELECT rd_almacen FROM recepcionesd WHERE rd_id = "'.$row['rd_id'].'")';
                  $resultalm = setq($sqlalm);
                  while($rowal = $resultalm->fetch_array()){
                    echo '<option value="'.$rowal['pa_id'].'">'.$rowal['pa_nmb'].'</option>';
                  }
                  echo '
                </select>
              </div>
              <input type="number" name="cantidado" value="'.$row['rd_cantidad'].'" hidden> 
              <input type="text" name="articulo" value="'.$row['rd_articulo'].'" hidden>
            </div>
          
          </div>
          <div class="modal-footer text-xs-center">
            <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> -->
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
          </div>
          </form> 
        </div>
      </div>
    </div>
    ';
    $i++;
      }

    echo '<tfoot>';

      
    ?>
    <script>  
    //var x = -1;// Obtener la lista, para recorrer cada elemento
    let listGroup = document.getElementById('suggestions');
    // Asignar evento al campo de texto
    if(document.getElementById("producto")){
    document.querySelector('#producto').addEventListener('keydown', e => {
    if(!listGroup) {
        return; // No existe la lista
    }
  
    //console.log(listGroup);
    // Obtener todos los elementos
    let items = listGroup.querySelectorAll('a');
    var a = document.getElementById("suggestions");
    // Saber si alguno está activo
    let actual = Array.from(items).findIndex(item => item.classList.contains('active'));
    //let actual = 0;
    // Analizar tecla pulsada
    //console.log(actual);
    //console.log(items[actual]);
    if(e.keyCode == 13) {
      // Tecla Enter, evitar que se procese el formulario
      e.preventDefault();
      // ¿Hay un elemento activo?
      if(items[actual]) {
          // Hacer clic
          items[actual].click();
      }
    } if(e.keyCode == 38 || e.keyCode == 40) {
      // Flecha arriba (restar) o abajo (sumar)
      if(items[actual]) {
          // Solo si hay un elemento activo, eliminar clase
          items[actual].classList.remove('active');
          //items[actual].className += " active";
      }
      // Calcular posición del siguiente
      if((e.keyCode == 38)){
        a.scrollTop -= ((items[actual].clientHeight - 1));
        actual += -1;
      }else{
        if(actual > 1) a.scrollTop += ((items[actual-2].offsetHeight + 1));
        
        actual += 1;
      }
      //actual += (e.keyCode == 38) ? -1 : 1;
      // Asegurar que está dentro de los límites
      if(actual < 0) {
          actual = 0;
      } else if(actual >= items.length) {
          actual = items.length - 1;
      } 
      // Asignar clase activa
      items[actual].classList.add('active');
      items[actual].setAttribute('autofocus','');
      //items[actual].className += " active";
    }
          
        });
        // En la función donde generas la lista debes activar evento clic para cada elemento
        // Para este ejemplo se hace manual
        listGroup.querySelectorAll('a').forEach(a => {
          a.addEventListener('click', e => {
              // Asignar valor al campo
              document.querySelector('#producto').value = e.currentTarget.textContent;
              // Aquí deberías cerrar la lista y/o eliminar el contenido
          });
        });
      }

        $(document).ready(function(){
            $('[rel="tooltip"]').tooltip({trigger: "hover"});
        });

        $(document).ready(function() {
            $('#producto').on('keyup', function() {
          if (event.keyCode == 38 || event.keyCode == 40){
                //console.log('chi');
          }else{
              
              var key = $(this).val();
      //          var empresa = $('#empresa').val();
              var dataString = 'producto='+key;
              $.ajax({
              type: "POST",
              url: "query/suggestmp.php",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  var valor = $(this).attr('value');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  $.ajax({
                    type: "POST",
                    url: "query/checkmp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var largo = document.getElementById('largodiv');
                      var ancho = document.getElementById('anchodiv');
                      var alto = document.getElementById('altodiv');
                      var largoin = document.getElementById('largo');
                      var anchoin = document.getElementById('ancho');
                      var altoin = document.getElementById('alto');
                      data = JSON.parse(dataRTN);
                      if(data['tipo'] == "A"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = true;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.removeAttribute('min');
                      } else if(data['tipo'] == "V"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = false;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.setAttribute('min', '0.01');
                      } else if(data['tipo'] == "P"){
                        largo.hidden = true;
                        ancho.hidden = true;
                        alto.hidden = true;
                        largoin.removeAttribute('min');
                        anchoin.removeAttribute('min');
                        altoin.removeAttribute('min');
                      }
                    }
                  });
                  $.ajax({
                    type: "POST",
                    url: "query/setpreciomp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var costo = document.getElementById('costo');
                      costo.value=dataRTN;
                    }
                  });
                  $("#cantidad").focus();
                  return false;
                });
              }
            });
          }
          });
        });

        function dividir(id){
          
        }
        </script>
      <?php


    }
  }
?>