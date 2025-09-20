<?php
  ini_set('display_errors',0);

class articulos{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelarticulos(isset($obj));
  }
  function index(){
    //if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";
    //if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
    //else $_REQUEST['estatus'] = $_REQUEST['estatus'];
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['categoria'])) $_REQUEST['categoria'] = NULL;
    if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
    //if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";
    

    $this->model->result($_REQUEST['estatus'],trim($_REQUEST['nmb']),$_REQUEST['categoria'],$_REQUEST['page']);
    $this->view = new viewarticulos($this->model);
    $this->view->browse($_REQUEST['estatus'],$_REQUEST['categoria']);
  }
  function edit(){
    if($_GET['nmba']){      
      $idprod = getIDprod($_GET['nmba']);
      if($idprod) redirect('?modulo=articulos&accion=edit&articulo='.$idprod.'');
      //else echo '<script>alert("Producto no encontrado. Verificar."); window.location.href="?modulo=articulos&accion=edit"</script>';
      else echo '<script>alert("Producto no encontrado. Verificar."); window.history.back();</script>';
    }

    if(!isset($_REQUEST['articulo'])) $_REQUEST['articulo'] = NULL;
    $this->model->select($_REQUEST['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->edit();
  }
  function insertprod(){
    //foreachdie();
    if(isset($_SESSION['uid'])){
      
        if($_POST['estatusp']) $estatusp = "A";
        else $estatusp = "I";

        if($_POST['inventariado']) $inventariado = "1";
        else $inventariado = "0";

        if($_POST['tipoprod']) {
          $tipoprod = "M";
          $_POST['largo'] = "0";
          $_POST['ancho'] = "0";
          $_POST['alto'] = "0";
          $mdetalle= "0";
        }
        else {
          if($_POST['mdetalle']) $mdetalle= "1";
          else $mdetalle= "0";
          $tipoprod = "I";
          $_POST['expira'] = '0000-00-00';
        }
        if($_POST['mdetalle']) $mdetalle= "1";
        else $mdetalle= "0";
        
        if($_POST['descuento']) $descuento= "1";
        else $descuento= "0";

        if($_POST['enviogratis']) $envio= "1";
        else $envio= "0";

        $this->model->setdata(getmax('a_id','articulos'),getmax('a_sku','articulos'),$_POST['cb'],$_POST['nmb'],$_POST['modelo'],$_POST['unidad'],$tipoprod,$_POST['categoria'],$_POST['clavesat'],$estatusp,$inventariado, $_POST['largo'], $_POST['ancho'], $_POST['alto'], $_POST['url'], $_POST['peso'], $_POST['expira'], $mdetalle,$_POST['tarifa'], $descuento, $envio);

        if(unique($this->model->cb,'a_cb','articulos')){
          $error = $this->model->insert();
          $sqlcosto = 'INSERT INTO articulo_proveedor SET ap_articulo = "'.$this->model->id.'",
                                                          ap_activo = "1",
                                                          ap_costo = "'.$_POST['costo'].'",
                                                          ap_estatus = "1"';
          setq($sqlcosto);
          $sqlprecio = 'INSERT INTO articulos_precios SET ap_articulo = "'.$this->model->id.'",
                                                          ap_costo = "'.$_POST['costo'].'",
                                                          ap_precio = "'.$_POST['precio'].'",
                                                          ap_preciol = "'.$_POST['preciol'].'",
                                                          ap_activo = "1",
                                                          ap_fecha = "'.date('Y-m-d H:i:s').'",
                                                          ap_user = "'.$_SESSION['uid'].'"'; 
          setq($sqlprecio);  

        }else{
          $error = '2XMA001';
          die("Error: Código de barras duplicado");
        }
    }

    redirect('?modulo=articulos&accion=edit&articulo='.$this->model->id.'&ok=1');
  }
  function updateprod(){
    //foreachdie();
    if($_POST['estatusp']) $estatusp = "A";
    else $estatusp = "I";

    if($_POST['inventariado']) $inventariado = "1";
    else $inventariado = "0";

    if($_POST['tipoprod']) {
      $tipoprod = "M";
      $_POST['largo'] = "0";
      $_POST['ancho'] = "0";
      $_POST['alto'] = "0";
      $mdetalle= "0";
    }else {
      if($_POST['mdetalle']) $mdetalle = "1";
      else $mdetalle= "0";
      $tipoprod = "I";
      $_POST['expira'] = '0000-00-00';
    }
    if($_POST['descuento']) $descuento= "1";
    else $descuento= "0";
    $costo = $_POST['costo'];
    $precio = $_POST['precio'];
    $preciol = $_POST['preciol'];

    if($_POST['enviogratis']) $envio= "1";
    else $envio= "0";

    
    $this->model->setdata($_POST['id'],$_POST['sku'],$_POST['cb'],$_POST['nmb'],$_POST['modelo'],$_POST['unidad'],$tipoprod,$_POST['categoria'],$_POST['clavesat'],$estatusp,$inventariado,$_POST['largo'], $_POST['ancho'], $_POST['alto'], $_POST['url'], $_POST['peso'], $_POST['expira'], $mdetalle, $_POST['tarifa'], $descuento, $envio);

    if(unique($this->model->cb,'a_cb','articulos','a_id != "'.$_POST['id'].'"')){
      $error = $this->model->update();
      $costobd = busca($this->model->id, 'articulo_proveedor', 'ap_activo = "1" AND ap_estatus ="1" AND ap_articulo', 'ap_costo');
      if($costobd != $costo){
        $sqlcosto = 'UPDATE articulo_proveedor SET ap_costo = "'.$costo.'" WHERE ap_activo = "1" AND ap_estatus = "1" AND ap_articulo = "'.$this->model->id.'"';
        setq($sqlcosto);
      }
      $preciobd = busca($this->model->id, 'articulos_precios', 'ap_activo = "1"  AND ap_modelo IS NULL AND ap_articulo', 'ap_precio');
      if($preciobd != $precio){
        $sql1 = 'UPDATE articulos_precios SET ap_activo = "0" WHERE ap_articulo = "'.$this->model->id.'"  AND ap_modelo IS NULL';
        setq($sql1);
        $sqlprecio = 'INSERT INTO articulos_precios SET ap_articulo = "'.$this->model->id.'",
                                                        ap_costo = "'.$_POST['costo'].'",
                                                        ap_precio = "'.$precio.'",
                                                        ap_preciol = "'.$preciol.'",
                                                        ap_activo = "1",
                                                        ap_fecha = "'.date('Y-m-d H:i:s').'",
                                                        ap_user = "'.$_SESSION['uid'].'"'; 
        setq($sqlprecio);  
      }
      $preciolbd = busca($this->model->id, 'articulos_precios', 'ap_activo = "1"  AND ap_modelo IS NULL AND ap_articulo', 'ap_preciol');
      if($preciolbd != $precio){
        $sqlprecio = 'UPDATE articulos_precios SET ap_preciol = "'.$preciol.'" WHERE ap_activo = "1" AND ap_articulo = "'.$this->model->id.'"';
        setq($sqlprecio);
      }
    }
    else
      $error = '2XMA001';

    redirect('?modulo=articulos&accion=edit&articulo='.$this->model->id);
  }
  function fichatecnica(){
    $this->model->select($_GET['articulo']);
    $this->model->selectft($this->model->id);
    $this->view = new viewarticulos($this->model);
    $this->view->fichatecnica();
  }
  function setftecnica(){
    if(isset($_POST['mnoporte'])) $mnoporte = 1;
    if(isset($_POST['mmodelo'])) $mmodelo = 1;
    if(isset($_POST['mmarca'])) $mmarca = 1;

    $this->model->SetDataF($_GET['id'],$_POST['nmb'],$mnoporte,$mmodelo,$mmarca,$_POST['descripcion'],$_POST['especificaciones'],$_POST['ocaracteristicas']);
    $this->model->setftecnica();

    redirect('?modulo=articulos&accion=fichatecnica&articulo='.$_GET['id']);
  }
  function images(){
    $this->model->select($_REQUEST['articulo']);
    $this->model->resultimages($_REQUEST['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->images();
  }
  function setimages(){

    $idpr = $_POST['articulo'];
    $prod = busca($idpr,'articulos','a_id','a_cb');
    $dirimg = 'img/productos/';

    if (!is_dir($dirimg)) {
      @mkdir($dirimg, 0777);
    }
    $directorio = $dirimg;

    $dir_subida = $directorio;
    $info = new SplFileInfo(basename($_FILES['imagenes']['name']));
    $viewext = array("jpg","png",'gif','jpeg','webp','WEBP');
    $ext = $info->getExtension();
    $img = $info->getBasename('.'.$ext);
    if(in_array($ext,$viewext)){
      $ordm1 = getmax('i_id','imagenes');
      $fichero_subido = $dir_subida.'/'.$prod.$ordm1; //basename($_FILES['imagenes']['name']);

    	$dir=opendir($directorio); //Abrimos el directorio de destino
    	$target_path = $directorio.'/'.$prod.$ordm1.'.'.$ext; //Indicamos la ruta de destino, así como el nombre del archivo

    	//Movemos y validamos que el archivo se haya cargado correctamente
    	//El primer campo es el origen y el segundo el destino
      if(file_exists($fichero_subido)){
        //echo 'file_exist';
        $sqln = 'SELECT COUNT(*) FROM imagenes WHERE i_nmb = "%'.$img.'"';
        $resultn = setq($sqln);
        list($maximgname) = $resultn->fetch_array();
        $maximgname++;
        $imgnv = $prod.$ordm1.'('.$maximgname.')';

    	  $target_path = $directorio.'/'.$imgnv.'.'.$ext; //Indicamos la ruta de destino, así como el nombre del archivo
        $img = $imgnv;
      }else{
        $img = $prod.$ordm1;
      }

      if(move_uploaded_file($_FILES['imagenes']['tmp_name'], $target_path)) {
        //echo 'move_file_upload';
        $file = fopen($directorio."/archivo.txt", "w+");
        fwrite($file, $target_path . PHP_EOL);
        fclose($file);

            $ordm = getmax('i_idimg','imagenes','i_idproducto = "'.$prod.'"');

            $sql = 'INSERT INTO imagenes SET
                    i_id = "'.$ordm1.'",
                    i_idproducto = "'.$prod.'",
                    i_idp = "'.$idpr.'",
                    i_nmb = "'.$img.'",
                    i_ext = "'.$ext.'",
                    i_idimg = "'.$ordm.'"';
            setq($sql);

            $existosos++;
      }
      else{
        echo '<script>alert("ERROR");</script>';
        die("no subio");
      } 

  	  closedir($dir); //Cerramos el directorio de destino
    }else {
      echo alert_back("La imagen seleccionada no tiene un formato permitido, por favor intenta con otra imagen",true);
      die();
    }

    redirect('?modulo=clientes&accion=index&articulo='.$idpr);
  }
  function delimg(){
    $this->model->delimg($_GET['articulo'],$_GET['idm']);
    
      redirect('?modulo=articulos&accion=images&articulo='.$_GET['articulo']);
  }
  function setup(){
    $this->model->setup($_GET['articulo'],$_GET['idm']);

      redirect('?modulo=articulos&accion=images&articulo='.$_GET['articulo']);
  }
  function setdown(){
    $this->model->setdown($_GET['articulo'],$_GET['idm']);

      redirect('?modulo=articulos&accion=images&articulo='.$_GET['articulo']);
  }
  function precios(){
    if(!isset($_REQUEST['proveedor'])) $_REQUEST['proveedor'] = NULL;

    $this->model->select($_REQUEST['articulo']);
    $this->model->selectap($_REQUEST['articulo'],$_REQUEST['proveedor']);
    $this->view = new viewarticulos($this->model);
    $this->view->precios();
  }
  function setproroveedor(){
    if(isset($_POST['iva'])) $iva = "1"; else $iva = 0;
    if(isset($_POST['usd'])) $usd = "1"; else $usd = 0;
    if(isset($_POST['activo'.$row['p_id']])) $activo = "1"; else $activo = 0;

      $this->model->SetDataProdProv($_GET['id'],$_POST['proveedor'],$_POST['noparteprov'],$_POST['costo'],$iva,$usd,$activo);
      $this->model->setproroveedor();

    redirect('?modulo=articulos&accion=precios&articulo='.$_GET['id']);
  }
  function proveedoractive(){
    $sql = 'UPDATE articulo_proveedor SET ap_activo = "0" WHERE ap_articulo = "'.$_GET['articulo'].'" ';
    setq($sql);

    $sqlpr = 'SELECT ap_id FROM articulo_proveedor WHERE ap_articulo = "'.$_GET['articulo'].'" AND ap_proveedor = "'.$_GET['proveedor'].'"
              ORDER BY ap_id DESC LIMIT 0,1';
    $resultpr = setq($sqlpr);
    list($idpr) = $resultpr->fetch_array();

    $sqlu = 'UPDATE articulo_proveedor SET ap_activo = "1" WHERE ap_id = "'.$idpr.'"';
    setq($sqlu);

    $this->model->setprecios($_GET['articulo'],1);
    redirect('?modulo=articulos&accion=precios&articulo='.$_GET['articulo']);
  }
  function updatecostos(){

    if(isset($_GET['prov'])){

      if(isset($_POST['iva'])) $iva = "1"; else $iva = 0;
      if(isset($_POST['usd'])) $usd = "1"; else $usd = 0;
      $activo = 1;

      $this->model->SetDataProdProv($_GET['articulo'],$_POST['proveedor'],$_POST['noparteprov'],$_POST['costo'],$iva,$usd,$activo);

      if($this->model->proveedor != 0)   die("murio");
        //$this->model->setproroveedor();
      else
        //$this->model->setprecios();
        $this->model->setprecios($this->articulo,0);
    }else{
      $sql = 'SELECT * FROM proveedores WHERE p_estatus = "A"
              AND p_id IN (SELECT ap_proveedor FROM articulo_proveedor WHERE ap_articulo = "'.$_GET['articulo'].'" AND ap_activo = "1" AND ap_estatus = "1")
              ';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        if(isset($_POST['iva'.$row['p_id']])) $iva = "1"; else $iva = 0;
        if(isset($_POST['usd'.$row['p_id']])) $usd = "1"; else $usd = 0;
        if(isset($_POST['activo'.$row['p_id']])) $activo = "1"; else $activo = 0;

        $this->model->SetDataProdProv($_GET['articulo'],$row['p_id'],$_POST['noparteprov'.$row['p_id']],$_POST['costo'.$row['p_id']],$iva,$usd,$activo);
        $this->model->setproroveedor();
        if(isset($_POST['idsyscom'])){
          $sql = 'UPDATE articulo_proveedor SET ap_idproducto = "'.$_POST['idsyscom'].'"
                  WHERE ap_articulo = "'.$_GET['articulo'].'" AND ap_proveedor = "'.$row['p_id'].'"';
          setq($sql);
        }
      }
    }

    redirect('?modulo=articulos&accion=precios&articulo='.$_GET['articulo']);
  }
  function updateprecios(){
    $sql = 'SELECT ep_id,ep_nmb,ap_costo,ap_precio
            FROM esquema_precio INNER JOIN articulos_precios ON ep_id = ap_esquema
            WHERE ep_estatus = "A" AND ap_activo = "1"
            AND ap_articulo = "'.$_GET['articulo'].'"';
    $result = setq($sql);
    $prices = 0;
    while($row = $result->fetch_array()){
      $prices++;
      $precio = busca("1",'articulos_precios','ap_articulo = "'.$_GET['articulo'].'" AND ap_esquema = "'.$row['ep_id'].'" AND ap_activo','ap_precio');
      $costo = busca("1",'articulos_precios','ap_articulo = "'.$_GET['articulo'].'" AND ap_esquema = "'.$row['ep_id'].'" AND ap_activo','ap_costo');
      if($precio != $_POST['precio'.$row['ep_id']]){
        $sqldpr = 'UPDATE articulos_precios SET ap_activo = "0"
                  WHERE ap_articulo = "'.$_GET['articulo'].'" AND ap_esquema = "'.$row['ep_id'].'"';
        setq($sqldpr);
        $sqlipr = 'INSERT INTO articulos_precios SET
                  ap_articulo = "'.$_GET['articulo'].'",
                  ap_esquema = "'.$row['ep_id'].'",
                  ap_precio = "'.$_POST['precio'.$row['ep_id']].'",
                  ap_activo = "1",
                  ap_costo = "'.$costo.'",
                  ap_fecha = "'.date('Y-m-d H:i:s').'",
                  ap_user = "'.$_SESSION['uid'].'"';
        setq($sqlipr);

        
      }
    }
    $countesquemas = busca($_SESSION['emp'],'esquema_precio','ep_estatus = "A" AND ep_empresa','COUNT(*)');
    if($prices == 0 || $prices < $countesquemas){
        $sql = 'SELECT ep_id FROM esquema_precio  
                WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_estatus = "A"';
        $result = setq($sql);
        while($row = $result->fetch_array()){
          $sqldpr = 'UPDATE articulos_precios SET ap_activo = "0"
                  WHERE ap_articulo = "'.$_GET['articulo'].'" AND ap_esquema = "'.$row['ep_id'].'"';
          setq($sqldpr);
          $sqlipr = 'INSERT INTO articulos_precios SET
                    ap_articulo = "'.$_GET['articulo'].'",
                    ap_esquema = "'.$row['ep_id'].'",
                    ap_precio = "'.$_POST['precio'.$row['ep_id']].'",
                    ap_activo = "1",
                    ap_fecha = "'.date('Y-m-d H:i:s').'",
                    ap_user = "'.$_SESSION['uid'].'"';
          setq($sqlipr);
        }
    }
    if($_POST['proveedor'] == "OK"){
      $existe = busca($_GET['articulo'],'articulo_proveedor','ap_articulo','ap_id');
      if(!$existe){
        $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_id');
        $sql = 'INSERT INTO articulo_proveedor SET
                ap_articulo = "'.$_GET['articulo'].'",
                ap_proveedor = "'.$proveedor.'",
                ap_activo = "1",
                ap_costo = "0",
                ap_iva = "1",
                ap_usd = "0",
                ap_estatus = "1"';
        setq($sql);
      }
    }

    preciosCombos(NULL,NULL,NULL,"4",NULL);
    
    redirect('?modulo=articulos&accion=precios&articulo='.$_GET['articulo']);
  } 
  function delprodprov(){
    $this->model->delprodprov($_GET['articulo'],$_GET['proveedor']);

    redirect('?modulo=articulos&accion=precios&articulo='.$_GET['articulo']);
  }
  function variantes(){
    $this->model->select($_REQUEST['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->variantes();
  }
  function composiciones(){
    $this->model->select($_REQUEST['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->composiciones();
  }
  function produccion(){
    $this->model->select($_REQUEST['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->produccion();
  }
  function grupostamano(){
    if(!isset($_POST['nmb'])) $_POST['nmb'] = NULL;

    $this->model->select($_GET['id']);
    $this->model->resultgtamano($_POST['nmb']);
    $this->view = new viewarticulos($this->model);
    $this->view->grupostamano($_POST['nmb']);
  }
  function articulosweb(){
    if(!isset($_REQUEST['articulo'])) $_REQUEST['articulo'] = NULL;

    $this->model->selectaw($_REQUEST['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->editaw();

  }
  function insertdet(){
    $prod = busca($_GET['prod'],'articulos','a_id','a_cb');
    $this->model->insertdet($prod,$_POST['detalle']);

    redirect('?modulo=articulos&accion=edit&articulo='.$_GET['prod']);
  }
  function updatedet(){
    $prod = busca($_GET['prod'],'articulos','a_id','a_cb');
    $this->model->updatedet($prod,$_POST['detalle'],$_POST['det']);

    redirect('?modulo=articulos&accion=edit&articulo='.$_GET['prod']);
  }
  function deldetalle(){
    $prod = busca($_GET['prod'],'articulos','a_id','a_cb');
    $this->model->deldetalle($prod,$_GET['det']);

    redirect('?modulo=articulos&accion=edit&articulo='.$_GET['prod']);
  }
  function combos(){
    if(!isset($_GET['articulo'])) $_GET['articulo'] = NULL;

    $this->model->select($_GET['articulo']);
    $this->model->selectac($_GET['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->combos();
  }
  function insertartcmb(){
    $articulo = getIDprod($_POST['producto']);
    if($articulo){
      $e2 = busca($articulo,'articulos_combos','ac_articulo = "'.$_GET['id'].'" AND ac_ahijo','ac_cantidad');
      $idCombo = getmax('ac_id','articulos_combos');
      $this->model->insertartcmb($idCombo,$_GET['id'],$articulo,$_POST['cantidad'],$e2);
      redirect('?modulo=articulos&accion=combos&articulo='.$_GET['id']);
    
    }else{
      echo '<script>
              alert("El articulo no se encuentra en tu inventario, verifica que escribiste correctamente el producto.");
              window.location.href="?modulo=articulos&accion=combos&articulo='.$_GET['id'].'";
            </script>';
    }
  }
  function updatecmb(){
    $this->model->updateartcmb($_GET['articulo'],$_GET['acid'], $_GET['cantidad'] );
    redirect('?modulo=articulos&accion=combos&articulo='.$_GET['articulo']);
  }
  function deleteartcmb(){
    $this->model->deleteartcmb($_GET['articulo'],$_GET['acid']);
    redirect('?modulo=articulos&accion=combos&articulo='.$_GET['articulo']);
  }
  function borrardoc(){
    $id = $_GET['id'];
    $ruta = busca($id,'articulosw','aw_id','aw_excel');
    $sql = 'UPDATE articulosw SET
            aw_excel = NULL
            WHERE aw_id = "'.$id.'"';
    setq($sql);
    unlink('../jdshop.mx/'.$ruta.'');
    redirect('?modulo=articulos&accion=articulosweb&articulo='.$id.'');
  }
  function borrardocpdf(){
    $id = $_GET['id'];
    $ruta = busca($id,'articulosw','aw_id','aw_pdf');
    $sql = 'UPDATE articulosw SET
            aw_pdf = NULL
            WHERE aw_id = "'.$id.'"';
    setq($sql);
    unlink('../jdshop.mx/'.$ruta.'');
    redirect('?modulo=articulos&accion=articulosweb&articulo='.$id.'');
  }
  function borrararchivo(){
    $id = $_GET['id'];
    $ruta = busca($id,'articulos_descargas','ad_id','ad_ruta');
    $articulo = busca($id,'articulos_descargas','ad_id','ad_articulo');
    $sql = 'DELETE FROM articulos_descargas WHERE ad_id = "'.$id.'"';
    setq($sql);
    unlink('../jdshop.mx/'.$ruta.'');
    redirect('?modulo=articulos&accion=variantes&articulo='.$articulo.'');
  }
  function insertvariante(){
    //foreachdie();
    $art = $_POST['articulo'];
    $cb = $_POST['cb'];
    $modelo = $_POST['modelo'];
    $desc = clearvmayus($_POST['descripcion']);
    $nmb = clearvmayus($_POST['nmb']);
    $precio = $_POST['precio'];
    $url = $_POST['url'];


    $veri = busca($art, 'articulos_variantes', 'av_modelo = "'.$modelo.'" AND av_articulo', 'COUNT(*)');
    if($veri > 0){
      echo '<script>
        alert("El modelo de la variante ya se encuentra registrado, intente con otro.")
      </script>';
      redirect('?modulo=articulos&accion=variantes&articulo='.$art);
      die();
    }

    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";

    $this->model->insertvariante($art, $cb, $modelo, $desc, $precio, $nmb, $url, $estatus);

    $sqlbusca = 'SELECT ap_costo, ap_preciol FROM articulos_precios WHERE ap_articulo = "'.$art.'" AND ap_modelo IS NULL AND ap_activo = "1"';
    $result = setq($sqlbusca);
    list($costo, $preciol) = $result -> fetch_array();
    $sql2 = 'INSERT INTO articulos_precios SET ap_articulo = "'.$art.'",
                                               ap_modelo = "'.$modelo.'",
                                               ap_costo = "'.$costo.'",
                                               ap_precio = "'.$precio.'",
                                               ap_preciol = "'.$preciol.'",
                                               ap_activo = "1",
                                               ap_fecha = "'.date('Y-m-d H:i:s').'",
                                               ap_user = "'.$_SESSION['uid'].'"
                                               ';
    setq($sql2);

    if($_FILES['archivos']['name']){
      //echo $_FILES['archivos'];
      for($i = 0; $i <= count($_FILES['archivos']['name']); $i++ ){
        //Recogemos el archivos enviado por el formulario
        $archivos = $_FILES['archivos']['name'][$i];
        //echo $archivos."\n";
        $extension = pathinfo($archivos, PATHINFO_EXTENSION);
        $nombre = pathinfo($archivos, PATHINFO_FILENAME);
        $nuevo_nombre = $nombre.'-'.$art.'.'.$extension;
        //Si el archivos contiene algo y es diferente de vacio
        if (isset($archivos) && $archivos != "") {
            //Obtenemos algunos datos necesarios sobre el archivos
            $tipo = $_FILES['archivos']['type'][$i];
            $tamano = $_FILES['archivos']['size'][$i];
            $temp = $_FILES['archivos']['tmp_name'][$i];
            
            //list($archivosw, $archivosh, $tipox, $atributos) = getimagesize($_FILES['archivos']['tmp_name']); 2000000
            //Se comprueba si el archivos a cargar es correcto observando su extensión y tamaño
            //die($tipo);
          if (!($tamano < (300000000) )) {
              die('<div><b>Error. La extensión o el tamaño de los pdf no es correcta.<br/>
              - Se permiten .gif, .jpg, .png. y de 200 kb como máximo.</b></div>');
          }
          else {
              //Si la archivos es correcta en tamaño y tipo
              //Se intenta subir al servidor
              $destination_path = getcwd().DIRECTORY_SEPARATOR;
              $target_path = $destination_path . 'img\variantes\ '.$nuevo_nombre;
              //echo $temp." , path: ".$target_path;
              if (move_uploaded_file($temp, $target_path)) {
                  //Cambiamos los permisos del archivos a 777 para poder modificarlo posteriormente
                  chmod($target_path, 0777);
                  //Mostramos el mensaje de que se ha subido co éxito
                  //die ('<div><b>Se ha subido correctamente la imagen.</b></div>');
                  //Mostramos la imagen subida
                  //die ('<p><img src="img/imagens/'.$imagen.'"></p>');
                  $ruta = 'variantes/'.$nuevo_nombre.'';
                  $sql = 'INSERT INTO articulos_descargas SET
                          ad_articulo = "'.$art.'",
                          ad_modelo = "'.$modelo.'",
                          ad_ruta = "'.$ruta.'",
                          ad_archivo = "'.$nuevo_nombre.'",
                          ad_ext = "'.$extension.'"';
                  setq($sql);
              }
              else {
                //Si no se ha podido subir la imagen, mostramos un mensaje de error
                die('<div><b>Ocurrió algún error al subir los archivos. No pudo guardarse.</b></div>');
              }
            }
        } 
      }
    }

    redirect('?modulo=articulos&accion=variantes&articulo='.$art);

  }
  function updatevariante(){
    //foreachdie();
    $art = $_POST['articulo'];
    $cb = $_POST['cb'];
    $modelo = $_POST['modelo'];
    $desc = clearvmayus($_POST['descripcion']);
    $nmb = clearvmayus($_POST['nmb']);
    $precio = $_POST['precio'];
    $url = $_POST['url'];

    $veri = busca($art, 'articulos_variantes', 'av_cb != "'.$cb.'" AND av_modelo = "'.$modelo.'" AND av_articulo', 'COUNT(*)');
    if($veri > 0){
      echo '<script>
        alert("El modelo de la variante ya se encuentra registrado, intente con otro.")
      </script>';
      redirect('?modulo=articulos&accion=variantes&articulo='.$art);
      die();
    }

    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";

    $this->model->updatevariante($art, $cb, $modelo, $desc, $precio, $nmb, $url, $estatus);

    $precioant = busca($art, 'articulos_precios', 'ap_modelo = "'.$modelo.'" AND ap_activo = "1" AND ap_articulo', 'ap_precio');
    if($precioant != $precio){
      $sqlbusca = 'SELECT ap_costo, ap_preciol FROM articulos_precios WHERE ap_articulo = "'.$art.'" AND ap_modelo IS NULL AND ap_activo = "1"';
      $result = setq($sqlbusca);
      list($costo, $preciol) = $result -> fetch_array();
      $sql3= 'UPDATE articulos_precios SET ap_activo = "0" WHERE ap_articulo="'.$art.'" AND ap_modelo = "'.$modelo.'"';
      setq($sql3);
      $sql2 = 'INSERT INTO articulos_precios SET ap_articulo = "'.$art.'",
                                                ap_modelo = "'.$modelo.'",
                                                ap_costo = "'.$costo.'",
                                                ap_precio = "'.$precio.'",
                                                ap_preciol = "'.$preciol.'",
                                                ap_activo = "1",
                                                ap_fecha = "'.date('Y-m-d H:i:s').'",
                                                ap_user = "'.$_SESSION['uid'].'"
                                                ';
      setq($sql2);
    }

    if($_FILES['archivos']['name']){
      for($i = 0; $i <= count($_FILES['archivos']['name']); $i++ ){
        //Recogemos el archivos enviado por el formulario
        $archivos = $_FILES['archivos']['name'][$i];
        $extension = pathinfo($archivos, PATHINFO_EXTENSION);
        $nombre = pathinfo($archivos, PATHINFO_FILENAME);
        $nuevo_nombre = $nombre.'-'.$art.'.'.$extension;
        //Si el archivos contiene algo y es diferente de vacio
        if (isset($archivos) && $archivos != "") {
            //Obtenemos algunos datos necesarios sobre el archivos
            $tipo = $_FILES['archivos']['type'][$i];
            $tamano = $_FILES['archivos']['size'][$i];
            $temp = $_FILES['archivos']['tmp_name'][$i];

            
            //list($archivosw, $archivosh, $tipox, $atributos) = getimagesize($_FILES['archivos']['tmp_name']); 2000000
            //Se comprueba si el archivos a cargar es correcto observando su extensión y tamaño
            //die($tipo);
          if (!($tamano < (300000000) )) {
              die('<div><b>Error. La extensión o el tamaño de los pdf no es correcta.<br/>
              - Se permiten .gif, .jpg, .png. y de 200 kb como máximo.</b></div>');
          }
          else {
              //Si la archivos es correcta en tamaño y tipo
              //Se intenta subir al servidor
              $destination_path = getcwd().DIRECTORY_SEPARATOR;
              $target_path = $destination_path . 'img/variantes/'.$nuevo_nombre;
              
              if (move_uploaded_file($temp, $target_path)) {
                  //Cambiamos los permisos del archivos a 777 para poder modificarlo posteriormente
                  chmod($destination_path.'img/variantes/'.$nuevo_nombre, 0777);
                  //Mostramos el mensaje de que se ha subido co éxito
                  //die ('<div><b>Se ha subido correctamente la imagen.</b></div>');
                  //Mostramos la imagen subida
                  //die ('<p><img src="img/imagens/'.$imagen.'"></p>');
                  $ruta = 'variantes/'.$nuevo_nombre.'';
                  $sql = 'INSERT INTO articulos_descargas SET
                          ad_articulo = "'.$art.'",
                          ad_modelo = "'.$modelo.'",
                          ad_ruta = "'.$ruta.'",
                          ad_archivo = "'.$nuevo_nombre.'",
                          ad_ext = "'.$extension.'"';
                  setq($sql);
              }
              else {
                //Si no se ha podido subir la imagen, mostramos un mensaje de error
                die('<div><b>Ocurrió algún error al subir el fichero. No pudo guardarse.</b></div>');
              }
            }
        } 
      }
    }

    redirect('?modulo=articulos&accion=variantes&articulo='.$art);

  }
  function borrarvariante(){
    $this->model->borrarvariante($_GET['articulo'], $_GET['modelo']);

    $art = $_GET['articulo'];
    $modelo = $_GET['modelo'];
    $sql = 'SELECT ad_id, ad_ruta, ad_articulo FROM articulos_descargas WHERE ad_articulo = "'.$art.'" AND ad_modelo = "'.$modelo.'"';
    $result = setq($sql);
    while($row= $result->fetch_array()){
      $sql2 = 'DELETE FROM articulos_descargas WHERE ad_id = "'.$row['ad_id'].'"';
      setq($sql2);
      unlink('../img/variantes/'.$row['ad_ruta'].'');
    }
    $sql2 = 'DELETE FROM articulos_precios WHERE ap_articulo = "'.$art.'" AND ap_modelo = "'.$modelo.'"';
    setq($sql2);
    
    

    redirect('?modulo=articulos&accion=variantes&articulo='.$_GET['articulo']);
  }

  function articulosligados() {
    if (!isset($_GET['articulo']))
      $_GET['articulo'] = NULL;

    $this->model->select($_GET['articulo']);
    $this->model->selectal($_GET['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->articulosligados();
  }
  function insertartligado(){
    //foreachdie();
    $articulo = getIDprod($_POST['producto']);
    if ($articulo != $_GET['id']) {
      if (!$articulo) {
        list($articulo, $modelo) = getIDprodVar($_POST['producto']);
        if(!$articulo){
        echo '<script>
                alert("Error: El producto buscado no esta registrado en tu base de datos, verifica la información");
                window.location.href="?modulo=articulos&accion=articulosligados&id='.$_GET['id'].'";
              </script>';
        die();
        }
        
      } else {
        $modelo = NULL;
      }

      $idlig = getmax('al_id', 'articulos_ligados');
      if($modelo != NULL) $verif = busca($_GET['id'], 'articulos_ligados', 'al_aligado = "' . $articulo . '" AND al_amodelo IS NULL AND al_articulo', 'al_cantidad');
      else $verif = busca($_GET['id'], 'articulos_ligados', 'al_aligado = "' . $articulo . '" AND al_articulo', 'al_cantidad');
      
      if ($verif) {
        $cantidad = $verif + $_POST['cantidad'];
        $sql = 'UPDATE articulos_ligados SET al_cantidad = "' . $cantidad . '" WHERE al_aligado = "' . $articulo . '" AND al_articulo = "' . $_GET['id'] . '" AND al_amodelo = "'.$modelo.'"';
        setq($sql);
      } else{
        $this->model->insertartligado($idlig, $_GET['id'], $articulo, $modelo, $_POST['cantidad']);
      }
      redirect('?modulo=articulos&accion=articulosligados&articulo=' . $_GET['id']);
        
    } else {
      echo '<script>
                alert("No se puede ligar el articulo a sí mismo, verifica el articulo.");
                window.location.href="?modulo=articulos&accion=articulosligados&articulo=' . $_GET['id'] . '";
              </script>';
    }
    
  }
  function deleteartligado(){
    $this->model->deleteartligado($_GET['articulo'], $_GET['alid']);
    redirect('?modulo=articulos&accion=articulosligados&articulo=' . $_GET['articulo']);
  }
  function updateligado(){
    $this->model->updateligado($_GET['articulo'], $_GET['alid'], $_GET['cantidad']);
    redirect('?modulo=articulos&accion=articulosligados&articulo=' . $_GET['articulo']);
  }

  function insertartcomposicion(){
    //foreachdie();
    $articulo = $_GET['id'];
    $sqlvar = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$articulo.'"';
    $resultvar = setq($sqlvar);
    /* if($_POST['molde'] == "X") $_POST['molde']= ""; */
    $tipo= busca($_POST['mp'], 'pr_materiaprima', 'pmp_id', 'pmp_tipo');
    if($tipo == "A"){
      $_POST['alto'] = '';
    } else if($tipo == "P"){
      $_POST['alto'] = '';
      $_POST['largo'] = '';
      $_POST['ancho'] = '';
    }
    
    if($resultvar->num_rows > 0){
      while($rowvar = $resultvar -> fetch_array()) {
        if(isset($_POST['var'.$rowvar['av_modelo']])){
          $this->model->insertcomposicion($articulo, $rowvar['av_modelo'], $_POST['mp'], $_POST['cantidad'], $_POST['largo'], $_POST['ancho'], $_POST['alto']);    
        }
      }
    } else {
      $this->model->insertcomposicion($articulo, '', $_POST['mp'], $_POST['cantidad'], $_POST['largo'], $_POST['ancho'], $_POST['alto']);    
    }
    redirect('?modulo=articulos&accion=composiciones&articulo=' . $articulo);
  }
  function deleteartcomposicion(){
    $articulo = $_GET['id'];
    $this->model->deletecomposicion($_GET['acid']);
    redirect('?modulo=articulos&accion=composiciones&articulo=' . $articulo);
  }
  function updateartcomposicion(){
    $articulo = $_GET['id'];
    $this->model->updatecomposicion($_GET['acid'], $_GET['cantidad']);
    redirect('?modulo=articulos&accion=composiciones&articulo=' . $articulo);
  }

  function insertproceso(){
    $articulo = $_REQUEST['articulo'];
    $sql = 'DELETE FROM articulos_produccion WHERE apr_articulo = "'.$articulo.'" ';
    setq($sql);
    // AGREGAR AQUÍ EL LOG
    foreach ($_REQUEST['activo'] as $key => $value) {  
      $veri = busca($articulo, 'articulos_produccion', 'apr_proceso = "'.$key.'" AND apr_articulo', 'COUNT(*)');
      $orden = $_REQUEST['orden'.$key];

      $sql = 'SELECT ppr_planta, ppr_fase FROM pr_procesos WHERE ppr_id = "'.$key.'" ';
      $result = setq($sql);
      list($planta,$fase) = $result->fetch_array();

      if($_REQUEST['obligatorio'.$key]) $obligatorio = '1';
      else $obligatorio = '0';

      if($_REQUEST['tipo'.$key]) $tipo = 'M';
      else $tipo = 'C';

      if(intval($veri) <= 0){
        $this->model->insertproceso($articulo,$planta, $fase, $key, $orden, $tipo, $obligatorio);
      }else{
        $this->model->updateproceso($articulo,$planta, $fase, $key, $orden, $tipo, $obligatorio);
      }
    }
    /*
      $veri = busca($articulo, 'articulos_produccion', 'apr_planta = "'.$_POST['planta'].'" AND apr_fase = "'.$_POST['fase'].'" AND apr_proceso = "'.$_POST['proceso'].'" AND apr_articulo', 'COUNT(*)');
      if(intval($veri) <= 0){
        $orden = getmax('apr_orden', 'articulos_produccion', 'apr_articulo="'.$articulo.'"');
        $this->model->insertproceso($articulo,$_POST['planta'], $_POST['fase'], $_POST['proceso'], $orden);
      }else {
        echo '<script>alert("Este paso ya esta dentro del proceso de producción actual.")</script>';
      }*/
    //foreachdie();
    
    
    redirect('?modulo=articulos&accion=produccion&articulo=' . $articulo);
  }
  function deleteproceso(){
    $articulo = $_GET['id'];
    $this->model->deleteproceso($_GET['aprid']);
    $this->model->updateorden($articulo, "", "");
    redirect('?modulo=articulos&accion=produccion&articulo=' . $articulo);
  }
  function updateorden(){
    //foreachdie();
    $articulo = $_GET['id'];
    $neworden = $_GET['orden'];
    $aprid = $_GET['aprid'];
    $sqlupd = 'UPDATE articulos_produccion SET apr_orden = "'.$neworden.'" WHERE apr_id = "'.$aprid.'"';
    setq($sqlupd);
    $this->model->updateorden($articulo, $aprid, $neworden);
    redirect('?modulo=articulos&accion=produccion&articulo=' . $articulo);
  }

  function copiarproduccion(){
    //foreachdie();
    $articulo = $_GET['id'];
    $copiart = $_GET['articulo'];

    $sqldel = 'DELETE FROM articulos_produccion WHERE apr_articulo="'.$articulo.'"';
    setq($sqldel);

    $sqlres = 'SELECT * FROM articulos_produccion WHERE apr_articulo = "'.$copiart.'"';
    $resultres = setq($sqlres);
    while($rowres = $resultres -> fetch_array()){
      //$orden = getmax('apr_orden', 'articulos_produccion', 'apr_articulo="'.$articulo.'"');
      $this->model->insertproceso($articulo,$rowres['apr_planta'], $rowres['apr_fase'], $rowres['apr_proceso'], $rowres['apr_orden'], $rowres['apr_tipo'], $rowres['apr_obligatorio']);
    }
    redirect('?modulo=articulos&accion=produccion&articulo=' . $articulo);
  } 

  function piezas(){
    $this->model->select($_GET['articulo']);
    $this->model->resultpiezas($_GET['articulo']);
    $this->view = new viewarticulos($this->model);
    $this->view->showpiezas();

  }

  function insertpieza(){

    $sqlv = 'SELECT * FROM articulos_variantes WHERE av_articulo =  "'.$_GET['articulo'].'" ';
    $resultv = setq($sqlv);
    if($resultv->num_rows > 0){

      foreach ($_POST['variantes'] as $key => $value) {

        $sql2 = 'INSERT INTO articulos_piezas SET
                az_color = "'.$_POST['color'].'",
                az_articulo = "'.$_GET['articulo'].'",
                az_pieza = "'.$_POST['pieza'].'",
                az_cantidad = "'.$_POST['cantidad'].'",
                az_variante = "'.$key.'" ';

        setq($sql2);
  
      }

    }else{

      $sql = 'INSERT INTO articulos_piezas SET
              az_color = "'.$_POST['color'].'",
              az_articulo = "'.$_GET['articulo'].'",
              az_pieza = "'.$_POST['pieza'].'",
              az_cantidad = "'.$_POST['cantidad'].'" ';
      setq($sql);

    }
    foreachdie();

  }
}


class modelarticulos{
  function resultpiezas($id){
    $sql = 'SELECT * FROM articulos_piezas WHERE az_articulo = "'.$id.'" ';
    $this->resultp = setq($sql);
  }
  function select($id){
    $sql = 'SELECT * FROM articulos WHERE a_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['a_id'];
    $this->sku = $row['a_sku'];
    $this->nmb = $row['a_nmb'];
    $this->cb = $row['a_cb'];
    $this->tipoprod = $row['a_tipoprod'];
    $this->modelo = $row['a_modelo'];
    $this->categoria = $row['a_categoria'];
    $this->unidad = $row['a_unidad'];
    $this->estatus = $row['a_estatus'];
    $this->clavesat = $row['a_clavesat'];
    $this->inventariado = $row['a_inventariado'];
    $this->largo = $row['a_largo'];
    $this->ancho = $row['a_ancho'];
    $this->alto = $row['a_alto'];
    $this->url = $row['a_url'];
    $this->peso = $row['a_peso'];
    $this->expira = $row['a_expira'];
    $this->mdetalle = $row['a_mdetalle'];
    $this->tarifa = $row['a_tarifa'];
    $this->descuento = $row['a_descuento'];
    $this->enviogratis = $row['a_enviogratis'];
  }
  function result($estatus,$nmb,$categoria,$page){ //filtro
    $bloque = 50;

    $sql = 'SELECT * FROM articulos INNER JOIN categorias ON a_categoria = cat_id INNER JOIN marcas ON a_marca = m_id ';
    
    if($estatus == "A" || $estatus == "I") $sql.= ' WHERE a_estatus = "'.$estatus.'"';
    else $sql.= ' WHERE 1';

    if($nmb) $sql.=' AND (a_nmb LIKE "%'.$nmb.'%" OR a_modelo LIKE "%'.$nmb.'%" OR a_noparte LIKE "%'.$nmb.'%" OR a_cb LIKE "%'.$nmb.'%" )';
    if($categoria) $sql.=' AND a_categoria = "'.$categoria.'" ';
    if($unidad) $sql.=' AND a_unidad = "'.$unidad.'" ';
    $sql.=' ORDER BY cat_nmb,a_nmb ASC ';
    $sql = 'SELECT * FROM articulos ';
    $result = setq($sql);
    $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
    $this->result = setq($sql);
    $this->resultt = setq($sql);
  }
  function setdata($id,$sku,$cb,$nmb,$modelo,$unidad,$tipoprod,$categoria,$clavesat,$estatusp,$inventariado, $largo, $ancho, $alto, $url, $peso, $expira, $mdetalle, $tarifa, $descuento, $enviogratis){
    $this->id = clearvmayus($id);
    $this->sku = clearvmayus($sku);
    $this->cb = clearvmayus($cb);
    $this->nmb = clearvmayus($nmb);
    $this->modelo = clearvmayus($modelo);
    $this->unidad = clearvmayus($unidad);
    $this->tipoprod = clearvmayus($tipoprod);
    $this->categoria = clearvmayus($categoria);
    $this->clavesat= clearvmayus($clavesat);
    $this->estatusp = clearvmayus($estatusp);
    $this->inventariado = clearvmayus($inventariado);
    $this->largo = $largo;
    $this->ancho = $ancho;
    $this->alto = $alto;
    $this->url = clearvmayus($url);
    $this->peso = $peso;
    $this->expira = clearvmayus($expira); 
    $this->mdetalle = $mdetalle; 
    $this->tarifa = $tarifa;
    $this->descuento = $descuento;
    $this->enviogratis = $enviogratis;
  }
  
  function insert(){
    $sql = 'INSERT INTO articulos SET
            a_id = "'.$this->id.'",
            a_sku = "'.$this->sku.'",
            a_cb = "'.$this->cb.'",
            a_nmb = "'.$this->nmb.'",
            a_modelo = "'.$this->modelo.'",
            a_tipoprod = "'.$this->tipoprod.'",
            a_categoria = "'.$this->categoria.'",
            a_unidad = "'.$this->unidad.'",
            a_clavesat = "'.$this->clavesat.'",
            a_estatus = "'.$this->estatusp.'",
            a_inventariado = "'.$this->inventariado.'",
            a_largo = "'.$this->largo.'",
            a_ancho = "'.$this->ancho.'",
            a_alto = "'.$this->alto.'",
            a_url = "'.$this->url.'",
            a_peso = "'.$this->peso.'",
            a_mdetalle = "'.$this->mdetalle.'",
            a_expira = "'.$this->expira.'",
            a_tarifa = "'.$this->tarifa.'",
            a_enviogratis = "'.$this->enviogratis.'",
            a_descuento = "'.$this->descuento.'"';
    setq($sql);
  }
  function update(){
    $sql = 'UPDATE articulos SET
            a_sku = "'.$this->sku.'",
            a_cb = "'.$this->cb.'",
            a_nmb = "'.$this->nmb.'",
            a_modelo = "'.$this->modelo.'",
            a_tipoprod = "'.$this->tipoprod.'",
            a_categoria = "'.$this->categoria.'",
            a_unidad = "'.$this->unidad.'",
            a_clavesat = "'.$this->clavesat.'",
            a_estatus = "'.$this->estatusp.'",
            a_inventariado = "'.$this->inventariado.'",
            a_largo = "'.$this->largo.'",
            a_ancho = "'.$this->ancho.'",
            a_alto = "'.$this->alto.'",
            a_url = "'.$this->url.'",
            a_peso = "'.$this->peso.'",
            a_expira = "'.$this->expira.'",
            a_mdetalle = "'.$this->mdetalle.'",
            a_tarifa = "'.$this->tarifa.'",
            a_enviogratis = "'.$this->enviogratis.'",
            a_descuento = "'.$this->descuento.'"
            WHERE a_id = "'.$this->id.'"';
    setq($sql);
  }
  function resultimages($articulos){
    $sql = 'SELECT * FROM articulos_imagenes WHERE a_articulo = "'.$articulos.'" ORDER BY a_id ASC';
    $this->result = setq($sql);
  }
  function delimg($producto,$idc){
    $sql = 'SELECT i_nmb,i_ext FROM  imagenes
            WHERE i_id = "'.$idc.'"';
            //i_idp = "'.$producto.'" AND 
    $result = setq($sql);
    list($img,$ext) = $result->fetch_array();
    unlink('img/productos/'.$img.'.'.$ext);
    $sqld = 'DELETE FROM imagenes WHERE i_id = "'.$idc.'"';
    setq($sqld);

    $this->orderelements($producto);
  }
  function setup($producto,$idc){
    $sql = 'SELECT i_idimg FROM imagenes WHERE i_idp = "'.$producto.'" AND i_id = "'.$idc.'"';
    $result = setq($sql) or die($sql);
    list($ant) = $result->fetch_array();
    $old = $ant++;

    $sqlan = 'UPDATE imagenes SET i_idimg = "'.$ant.'" WHERE i_idp = "'.$producto.'" AND i_idimg = "'.$old.'"';
    setq($sqlan);


    $sqlan = 'UPDATE imagenes SET i_idimg = i_idimg+1 WHERE i_idp = "'.$producto.'" AND i_id = "'.$idc.'"';
    setq($sqlan);

    //echo $sqlan.'<br>';
    $this->orderelements($producto);
    //die("--");
  }
  function setdown($producto,$idc){
    $sql = 'SELECT i_idimg FROM imagenes WHERE i_idp = "'.$producto.'" AND i_id = "'.$idc.'"';
    $result = setq($sql);
    list($ant) = $result->fetch_array();
    $old = $ant++;

    $sqlan = 'UPDATE imagenes SET i_idimg = "'.$ant.'" WHERE i_idp = "'.$producto.'" AND i_idimg = "'.$old.'"';
    setq($sqlan);


    $sqlan = 'UPDATE imagenes SET i_idimg = i_idimg-1 WHERE i_idp = "'.$producto.'" AND i_id = "'.$idc.'"';
    setq($sqlan);

    //echo $sqlan.'<br>';
    //die("-");
    $this->orderelements($producto);
    //die("--");

  }
  function orderelements($producto){
    $sql = 'SELECT * FROM imagenes WHERE i_idp = "'.$producto.'" ORDER BY i_idimg';
    $result = setq($sql);
    $resultn = setq($sql);
    $numel = $resultn->num_rows;

    $n = 0;
    while($row = $result->fetch_array()){
      $n++;
      $sql = 'UPDATE imagenes SET i_idimg = "'.$n.'" WHERE i_idp = "'.$producto.'" AND i_id = "'.$row['i_id'].'"';
      setq($sql) ;
    }
  }
  function selectap($articulo,$proveedor){
    $sql = 'SELECT * FROM articulo_proveedor WHERE ap_articulo = "'.$articulo.'" AND ap_proveedor = "'.$proveedor.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->aarticulo = $row['ap_articulo'];
    $this->aproveedor = $row['ap_proveedor'];
    $this->anoparteprov = $row['ap_noparteprov'];
    $this->aidproducto = $row['ap_idproducto'];
    $this->acosto = $row['ap_costo'];
    $this->aiva = $row['ap_iva'];
    $this->ausd = $row['ap_usd'];
    $this->afcompra = $row['ap_fcompra'];
  }
  function SetDataProdProv($articulo,$proveedor,$noparteprov,$costo,$iva,$usd,$activo){
    $this->articulo = $articulo;
    $this->proveedor = $proveedor;
    $this->noparteprov = $noparteprov;
    $this->costo = $costo;
    $this->iva = $iva;
    $this->usd = $usd;
    $this->activo = $activo;
  }
  function setproroveedor(){

    $sql = 'SELECT COUNT(*) FROM articulo_proveedor
            WHERE ap_articulo = "'.$this->articulo.'" AND ap_proveedor = "'.$this->proveedor.'"
            AND ap_costo = "'.$this->costo.'" AND ap_iva = "'.$this->iva.'" AND ap_usd = "'.$this->usd.'"
            AND ap_estatus = "1"';
    $result = setq($sql);
    list($nump) = $result->fetch_array();
    if($nump == 0){
      if($this->activo == 1){
        $sqlpp = 'UPDATE articulo_proveedor SET ap_activo = "0" WHERE  ap_articulo = "'.$this->articulo.'"
                AND ap_proveedor = "'.$this->proveedor.'"';
        setq($sqlpp);
      }
      $sqlua = 'UPDATE articulo_proveedor SET ap_estatus = "0" WHERE ap_articulo = "'.$this->articulo.'"
                AND ap_proveedor = "'.$this->proveedor.'"';
      setq($sqlua);

      $sqlap = 'INSERT INTO articulo_proveedor SET
                ap_articulo = "'.$this->articulo.'",
                ap_proveedor = "'.$this->proveedor.'",
                ap_noparteprov = "'.$this->noparteprov.'",
                ap_costo = "'.$this->costo.'",
                ap_iva = "'.$this->iva.'",
                ap_usd = "'.$this->usd.'",
                ap_activo = "'.$this->activo.'",
                ap_estatus = "1"';
      setq($sqlap);

      if(busca($this->articulo,'articulo_proveedor','ap_articulo','COUNT(*)') == 1){
        $sqlac = 'UPDATE articulo_proveedor SET ap_activo = "1" WHERE ap_articulo = "'.$this->articulo.'"';
        setq($sqlac);
      }

      $this->setprecios($this->articulo,1);
    }
  }
  function delprodprov($articulo,$proveedor){
    $sql = 'UPDATE articulo_proveedor SET ap_estatus = "0", ap_activo = "0" WHERE ap_articulo = "'.$articulo.'" AND ap_proveedor = "'.$proveedor.'"';
    setq($sql);
  }
  function setprecios($articulo,$prov = 0){
    if($prov == 1){
      $sql = 'SELECT ap_costo,ap_iva,ap_usd FROM articulo_proveedor
              WHERE ap_articulo = "'.$articulo.'" AND ap_activo = "1"';
      $result = setq($sql);
      list($costo1,$iva,$usd) = $result->fetch_array();
      $valorusd = busca(1,'empresas','e_id','e_valorusd');
    }else{
      $costo1 = $this->costo;
      $iva = $this->iva;
      $usd = $this->usd;
      $articulo = $this->articulo;
    }
    
    $valorusd = busca($_SESSION['emp'],'empresas','e_id','e_valorusd');
    $sql = 'SELECT * FROM esquema_precio WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_base != "F"
            ORDER BY ep_base ASC,ep_id ASC';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $costo = $costo1;
      if($usd == "1") $costo = $costo*$valorusd;
      if($iva == "1") $costo = $costo*1.16;
      
      if($row['ep_base'] == "C") $base = $costo;
      else $base = busca($row['ep_precio'],'articulos_precios','ap_activo = "1" AND ap_articulo = "'.$articulo.'" AND ap_esquema','ap_precio');

      $diferencia = $base*($row['ep_sumarp']/100);

      if($row['ep_masmenos'] == "+") $precio = ($base+$diferencia)+$row['ep_sumarf'];
      else $precio = ($base-$diferencia)+$row['ep_sumarf'];

      if($row['ep_miva'] == 1) $precio = $precio * 1.16;


      $sqldpr = 'UPDATE articulos_precios SET ap_activo = "0"
                WHERE ap_articulo = "'.$articulo.'" AND ap_esquema = "'.$row['ep_id'].'"';
      setq($sqldpr);
      $sqlipr = 'INSERT INTO articulos_precios SET
                 ap_articulo = "'.$articulo.'",
                 ap_esquema = "'.$row['ep_id'].'",
                 ap_precio = "'.$precio.'",
                 ap_costo = "'.$costo.'",
                 ap_activo = "1",
                 ap_fecha = "'.date('Y-m-d H:i:s').'",
                 ap_user = "'.$_SESSION['uid'].'"'; 
      setq($sqlipr);
      /*
      $sqlipr = 'INSERT INTO articulos_precios SET
        ap_articulo = "'.$articulo.'",
        ap_esquema = "'.$row['ep_id'].'",
        ap_precio = "'.$costo1.'",
        ap_costo = "'.$costo1.'",
        ap_activo = "1",
        ap_fecha = "'.date('Y-m-d H:i:s').'",
        ap_user = "'.$_SESSION['uid'].'"'; 
      setq($sqlipr); */
    }
    
    preciosCombos(NULL,NULL,NULL,"4",NULL);
  }
  function insertdet($prod,$detalle){
    $sqld = 'SELECT MAX(ae_id) FROM articulos_extra WHERE ae_articulo = "'.$prod.'"';
    $result = setq($sqld) or die($sqld);
    list($maxid) = $result->fetch_array();
    $maxid++;

    $this->detalle = clearvmayus($detalle);

    $sql = 'INSERT INTO articulos_extra SET
            ae_articulo = "'.$prod.'",
            ae_id = "'.$maxid.'",
            ae_detalle = "'.$this->detalle.'"';
    setq($sql) or die($sql);

  }
  function updatedet($prod,$detalle,$det){
    $this->detalle = clearvmayus($detalle);

    $sql = 'UPDATE articulos_extra SET
            ae_detalle = "'.$this->detalle.'"
            WHERE ae_articulo = "'.$prod.'" AND ae_id = "'.$det.'"';
    setq($sql) or die($sql);
  }
  function deldetalle($prod,$det){
    $sql = 'DELETE FROM articulos_extra WHERE ae_articulo = "'.$prod.'" AND ae_id = "'.$det.'"';
    setq($sql) or die($sql);
  }
  function selectac(){
    $sql = 'SELECT ac_id,ac_ahijo,ac_cantidad,a_nmb,a_categoria,a_modelo,a_estatus,cat_nmb FROM articulos_combos INNER JOIN articulos ON ac_ahijo = a_id INNER JOIN categorias ON cat_id = a_categoria
            WHERE ac_articulo = "'.$this->id.'" ORDER BY a_nmb ASC ';
    $result = setq($sql);
    $this->result = setq($sql);
  }
  function insertartcmb($idCombo,$articuloP,$articuloH,$cantidad,$e2){
    if($e2)
      $cantidadF = $e2 + $cantidad;
    else
      $cantidadF = $cantidad;
    
    $sql = 'INSERT INTO articulos_combos SET ac_id = "'.$idCombo.'", ac_articulo = "'.$articuloP.'",
            ac_ahijo = "'.$articuloH.'", ac_cantidad = "'.$cantidadF.'",
            ac_uregistra = "'.$_SESSION['uid'].'", ac_fregistro = "'.date('Y-m-d H:i:s').'"
            ON DUPLICATE KEY UPDATE ac_cantidad = "'.$cantidadF.'", ac_fregistro = "'.date('Y-m-d H:i:s').'"';
    $result = setq($sql);
  }
  function updateartcmb($articuloP,$idartcmb, $cantidad){
    $sql = 'UPDATE articulos_combos SET ac_cantidad = "'.$cantidad.'"  WHERE ac_id = "'.$idartcmb.'" AND ac_articulo = "'.$articuloP.'"';
    $result = setq($sql);
  }
  function deleteartcmb($articuloP,$idartcmb){
    $sql = 'DELETE FROM articulos_combos WHERE ac_id = "'.$idartcmb.'" AND ac_articulo = "'.$articuloP.'"';
    $result = setq($sql);
  }
  function insertvariante($art, $cb, $modelo, $desc, $precio, $nmb, $url, $estatus){
    $sql = 'INSERT INTO articulos_variantes SET av_articulo = "'.$art.'",
                                                av_cb = "'.$cb.'",
                                                av_nmb = "'.$nmb.'",
                                                av_modelo = "'.$modelo.'",
                                                av_descripcion = "'.$desc.'",
                                                av_url = "'.$url.'",
                                                av_estatus = "'.$estatus.'"';
    setq($sql);
  }
  function updatevariante($art, $cb, $modelo, $desc, $precio, $nmb, $url, $estatus){
    $sql = 'UPDATE articulos_variantes SET av_cb = "'.$cb.'",
                                           av_nmb = "'.$nmb.'",
                                           av_descripcion = "'.$desc.'",
                                           av_url = "'.$url.'",
                                           av_estatus = "'.$estatus.'"
            WHERE av_articulo = "'.$art.'" AND av_modelo = "'.$modelo.'"';
    setq($sql);
  }
  function borrarvariante($art, $modelo){
    $sql = 'DELETE FROM articulos_variantes WHERE av_articulo = "'.$art.'" AND av_modelo = "'.$modelo.'"';
    setq($sql);
  }

  function selectal()
  {
    $sql = 'SELECT al_id,al_aligado,al_cantidad,a_nmb,a_estatus, al_fregistro, al_uregistra, al_amodelo FROM articulos_ligados INNER JOIN articulos ON al_aligado = a_id 
            WHERE al_articulo = "' . $this->id . '" ORDER BY a_nmb ASC ';
    $this->result = setq($sql);
  }
  function insertartligado($idlig, $articuloP, $artligado, $modeloligado, $cantidad)
  {
    $sql = 'INSERT INTO articulos_ligados SET al_id = "' . $idlig . '", al_articulo = "' . $articuloP . '",
            al_aligado = "' . $artligado . '", al_amodelo = "'.$modeloligado.'", al_cantidad = "' . $cantidad . '",
            al_uregistra = "' . $_SESSION['uid'] . '", al_fregistro = "' . date('Y-m-d H:i:s') . '"
            ON DUPLICATE KEY UPDATE al_cantidad = "' . $cantidad . '", al_fregistro = "' . date('Y-m-d H:i:s') . '"';
    $result = setq($sql);
  }
  function deleteartligado($articuloP, $idlig)
  {
    $sql = 'DELETE FROM articulos_ligados WHERE al_id = "' . $idlig . '" AND al_articulo = "' . $articuloP . '"';
    setq($sql);
  }
  function updateligado($articuloP, $idlig, $cantidad){
    $sql = 'UPDATE articulos_ligados SET al_cantidad = "'.$cantidad.'" WHERE al_id = "' . $idlig . '" AND al_articulo = "' . $articuloP . '"';
    setq($sql);
  }
  function insertcomposicion($articulo, $modelo, $mp, $cantidad, $largo, $ancho, $alto){
    $sql = 'INSERT INTO articulos_composicion SET ac_articulo = "'.$articulo.'",
                                                  ac_modelo = "'.$modelo.'",
                                                  ac_mp = "'.$mp.'",
                                                  ac_largo = "'.$largo.'",
                                                  ac_ancho = "'.$ancho.'",
                                                  ac_alto = "'.$alto.'",
                                                  ac_cantidad = "'.$cantidad.'"';
    $result = setq($sql);
  }
  function deletecomposicion($idac){
    $sql = 'DELETE FROM articulos_composicion WHERE ac_id = "' . $idac . '"';
    setq($sql);
  }
  function updatecomposicion($acid, $cantidad){
    $sql = 'UPDATE articulos_composicion SET ac_cantidad = "'.$cantidad.'" WHERE ac_id = "'.$acid.'"';
    setq($sql);
  }
  function insertproceso($articulo, $planta, $fase, $proceso, $orden, $tipo, $obligatorio){
    $sql = 'INSERT INTO articulos_produccion SET apr_articulo = "'.$articulo.'",
                                                  apr_planta = "'.$planta.'",
                                                  apr_fase = "'.$fase.'",
                                                  apr_proceso = "'.$proceso.'",
                                                  apr_orden = "'.$orden.'",
                                                  apr_tipo = "'.$tipo.'",
                                                  apr_obligatorio = "'.$obligatorio.'" ';
    $result = setq($sql);
  }
  function updateproceso($articulo, $planta, $fase, $proceso, $orden, $tipo, $obligatorio){
    $sql = 'UPDATE articulos_produccion SET
            apr_planta = "'.$planta.'",
            apr_fase = "'.$fase.'",
            apr_proceso = "'.$proceso.'",
            apr_orden = "'.$orden.'",
            apr_tipo = "'.$tipo.'",
            apr_obligatorio = "'.$obligatorio.'"
            WHERE apr_articulo = "'.$articulo.'" AND apr_proceso = "'.$proceso.'" ';
    setq($sql);
  }
  function deleteproceso($aprid){
    $sql = 'DELETE FROM articulos_produccion WHERE apr_id = "'.$aprid.'"';
    setq($sql);
  }

  function updateorden($articulo, $aprid, $orden){
    $i = 1;
    $sql = 'SELECT * FROM articulos_produccion WHERE apr_articulo = "'.$articulo.'"';
    if($aprid != "") $sql.= ' AND apr_id != "'.$aprid.'"';
    $sql .= 'ORDER BY apr_orden ASC';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      if($orden == $i) $i++;
      $sqlupd = 'UPDATE articulos_produccion SET apr_orden = "'.$i.'" WHERE apr_id = "'.$row['apr_id'].'"';
      setq($sqlupd);
      $i++;
    }
  } 
}

class viewarticulos{
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
      $this->tipoprod = array("P"=>"Propio (Producción)","C"=>"Comprado a proveedor","S"=>"Servicio","T"=>"Producto de tercero (Reventa)","M"=>"Combo (Conjunto de productos)");
  }

  function browse($estatus,$categoria){
  
    $esta = "";
    $esti = "";
    $estt = "";
    if($estatus == "A") $esta = "selected";
    elseif($estatus == "I") $esti = "selected";
    else $estt = "selected";

    $nuevo = '<a href="?modulo=articulos&accion=edit" id="nuevo" >
                <button type="button" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nuevo </button>
              </a>';
    $filtrar = '
    
    <form class="form-inline" role="form" method="post" action="?modulo=articulos&accion=index" id="filtro">
      <div class="mb-5" style="max-width: 250px">
        <label for="">Buscar por categoría:</label>
        <select id="categoriafiltro" class="form-control" name="categoria" placeholder="Categoria">
          <option value="">TODAS LAS CATEGORIAS</option>
          ';
            $sql = 'SELECT * FROM categorias WHERE cat_estatus = "A" ORDER BY cat_nmb';
            $result = setq($sql);
            while($row = $result->fetch_array()){
              if($categoria == $row['cat_id']) $sel = "selected"; else $sel = "";
              $filtrar .= '<option value="'.$row['cat_id'].'"'.$sel.'>'.$row['cat_nmb'].'</option>';
            }
          $filtrar .= '
        </select>
      </div> <!-- form group [order by] -->
      <div class="mb-5" style="max-width: 250px">
        <label for="">Buscar por estatus:</label>
        <select id="estatusfiltro" class="form-control" name="estatus">
          <option value="A" '.$esta.'>Activos</option>
          <option value="I" '.$esti.'>Inactivos</option>
          <option value="T" '.$estt.'>Todos</option>
        </select>
      </div> <!-- form group [order by] -->
      <div class="mb-5">
        <label for="">Acciones:</label> <br>
      <button type="button" onclick="mandar(0)" class="btn btn-info">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-redo"></i> Actualizar
      </button>
      <button type="button" onclick="mandar(1)" class="btn btn-warning">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
      </button>
      </div>
    </form>

    <script>
      function mandar(id){
        //document.getElementById("page").value = id;
        //document.getElementById("filtro").submit();
          

        var sts = document.getElementById("estatusfiltro");
        var cat = document.getElementById("categoriafiltro");

        if(id == "1"){
          sts.value = "T";
          cat.value= "";  
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
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatablearticulos.php",
            type: "POST",
            datatype: "json",
            data:{ "estatus": sts.value,
              "categoria": cat.value,
            },
          },
          pageLength: "50",
          responsivePriority: 1,
        });
      }
    </script>
    
    ';

    toolbar($_GET['modulo'],'',$filtrar,$nuevo);


    

    echo '
    <script>
      function alertaprod(){
        alert("La cantidad de productos en tu licencia ha sido superado. No puedes agregar nuevos productos. Contacta a tu agente de JD CEO.");
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
          $("#pref-search").focus();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 82) {
          window.location.reload();
        }
      });
    </script>
    ';


    //Sección: E2 Encabezado - Filtros
    ?>
    <!--<div class="mb-5 col-md-2">-->
        <div class="card mt-3">
          <div class="card-body">
    <?php
    // style="overflow-x: clip;"
    echo '<div class=" col-12 table-responsive text-medium">
          <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
          <thead class="bg-light-blue bg-darken-2 ">
            <tr>
              <th><b>CB</b></th>
              <th><b>SKU</b></th>
              <th><b>Nombre</b></th>
              <th><b>Modelo</b></th>
              <th><b>Categoria</b></th>
              <th><b>Precio</b></th>
              <th><b>Acciones</b></th>
            </tr>
          </thead>
          <tbody>
          </tbody>';

    echo '</table>
      </div>
    </div>';
    if($estatus == "") $estatus = "0";
    if($categoria == "") $categoria = "0";
 
    echo '
    <script>
      $(document).ready(function () {
        var windowHeight = $(window).height();
        $("#myTable").DataTable( {
          paging: true,
          processing: true,
          serverside: true,
          scrollY: windowHeight * 0.5,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatablearticulos.php",
            type: "POST",
            datatype: "json",
            data:{ "estatus": "'.$estatus.'",
              "categoria": "'.$categoria.'",
            },
              
          },
          pageLength: "50",
          responsivePriority: 1,
        });
      });
    </script>
    ';
    
    
    
  }

  function edit(){
    if($this->model->id){
      $accion = 'update';
      $titulo = 'Actualizar Datos';
    }
    else{
      $accion = 'insert';
      $titulo = 'Registro básico de Producto';
    }

    ?>
    <script>
      function generacb(){
        var codigo= document.getElementById('checkauto');
        codigo.value = "1";
        $.ajax({
          url: 'query/getcb.php',
          type: 'POST',
          dataType: 'html',
          data: {},
        })
        .done(function(respuesta){
          $('#cb').val(respuesta);
        });
        $('#nmb').focus();
      }
      //Ocultamos el contenedor1 mientras pasan 3 segundos  
      //$('#contenido').hide(3000);
      //Mostramos el contenedor2 mientras pasan 3 segundos
      
      window.onload=function(){
        const valores = window.location.search;
        const urlParams = new URLSearchParams(valores);
        var actividad = urlParams.get('ok');
        if(actividad !== "undefined" && actividad !== null){
          //$('#nuevoarticulo').show(5000);
          //setInterval($('#nuevoarticulo').hide(3000),3000);}
          $('#nuevoarticulo').delay(3000).slideUp(400);
          //$('#nuevoarticulo').fadeOut(3000);
        }
      }

      function cbauto(){
        var codigo= document.getElementById('checkauto');
        codigo.value = "0";
      }
      
    </script>
    <?php
    $atras 
    = '
    <a href="?modulo=articulos&accion=index">
      <button class="btn btn-sm btn-warning">
        <i class="fa fa-arrow-left"></i> Atrás
      </button>
    </a>';    

    toolbar($_GET['modulo'],$atras,'','');

  echo '
  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ayuda para registro básico de producto: </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-xs-center">
        <video class="img-fluid" controls>
          <source src="vid/prueba.mp4" type="video/mp4">
        </video>
        </div>
        <div class="modal-footer">
        </div>
      </div>
    </div>
  </div>';

 echo '
  <div class="row mt-3" >
    <div class="card col-md-9">
      <div class="">
        <div class="card-header">
          <h4 class="card-title" id="basic-layout-form">'.$titulo.' '.$btnmod.'</h4>
          <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
          <div class="heading-elements">
            <ul class="list-inline mb-0">
              <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
              <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
              <li><a data-action="close"><i class="icon-cross2"></i></a></li>
            </ul>
          </div>-->
        </div>';

  if($this->model->id){
    $accion = 'updateprod';
    $redcb = 'readonly = "readonly" ';
  }
  else{
    $accion = 'insertprod';
    $redcb = ' ';
  }
  echo '<form autocomplete="off" name="artedit" id="cliedit" action="?modulo=articulos&accion='.$accion.'" method="post" onsubmit="checkguardar();">
        <input type="hidden" name="acronimo" value="'.busca(1,'empresas','e_id','e_siglas').'" id="acronimo" />
        <input type="hidden" name="id" value="'.$this->model->id.'" id="idproducto" />
        <input type="hidden" name="sku" value="'.$this->model->sku.'" id="skuproducto" />';
  if($this->model->id)
    echo '<input type="hidden" name="id" value="'.$this->model->id.'" />';
  echo '
        <div class="card-body">
          <div class="card-block">
            <div class="form-body">
              ';
            if($_GET['ok']){
              echo'<div class="col-md-12 alert alert-green h6" style="background-color: rgb(154 255 177);" id="nuevoarticulo">
                <center>
                  <b>Articulo agregado correctamente</b>
                </center>
              </div>';
            }
                echo' <div id="formprod" class="row">
                <div class="col-sm-2 col-md-1">
                  <div class="mb-5">';
                    if($accion == 'insertprod')
                      echo '<label class="font-weight-bold font-weight-bold">Generar</label>
                    <center>
                      <button type="button" role="button" onclick="generacb();" class="btn-sm bg-black" >
                        <i class="fas fa-barcode fa-lg" style="color: #ffffff;"></i>
                      </button>
                    </center>';
                  echo '</div>
                </div>
                <div class="col-sm-10 col-md-5" style="block-size:auto">
                  <div class="mb-5">
                    <label class="name font-weight-bold">Código de barras</label>
                    <input class="form-control" autocomplete="off" onchange="cbauto();" type="text" value="'.$this->model->cb.'" name="cb" id="cb" required="required" autofocus placeholder="Código de barras del producto" '.$redcb.'>
                    <input type="hidden" value="0" name="checkauto" id="checkauto">
                  </div>
                </div>

                <div class="col-sm-12 col-md-6" style="block-size:auto">
                  <div class="mb-5">
                    <label class="font-weight-bold font-weight-bold">Nombre del producto</label>
                    <input class="form-control" type="text" value="'.$this->model->nmb.'" name="nmb" id="nmb" placeholder="Nombre del producto" required="required" >
                  </div>
                </div>

                <div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Modelo único del producto</label>
                    <input data-toggle="tooltip" data-place="top" title="El modelo no puede tener espacios en blanco." class="form-control" type="text" value="'.$this->model->modelo.'" name="modelo" id="modelo" placeholder="Nombre del modelo" required="required" >
                  </div>
                </div>
                <script>
                $("#modelo").keyup(function(){              
                        var ta      =   $("#modelo");
                        letras      =   ta.val().replace(/ /g, "");
                        ta.val(letras)
                }); 
                </script>
              <!--  <div class="col-sm-12 col-md-5 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Marca</label>
                    <select name="marca" id="marca" class="form-control" required="required">
                    <option value="">Elije una marca</option>';
                $sqlm = 'SELECT * FROM marcas';
                $resultm = setq($sqlm);
                while($rowm = $resultm->fetch_array()){
                  if($rowm['m_id'] == $this->model->marca) $selem = 'selected'; else $selem = "";
                  echo '<option value="'.$rowm['m_id'].'" '.$selem.'>'.$rowm['m_nmb'].'</option>';
                }
                echo '</select>
                  </div>
                </div> -->

                <div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Unidad de Medida</label>
                    <select name="unidad" id="unidad" class="form-control" required="required">
                    <option value="">Elije una unidad de medida</option>';
                $sqlu = 'SELECT * FROM unidades';
                $resultu = setq($sqlu);
                while($rowu = $resultu->fetch_array()){
                  if($rowu['u_id'] == $this->model->unidad) $seleu = 'selected'; else $seleu = "";
                  echo '<option value="'.$rowu['u_id'].'" '.$seleu.'>'.$rowu['u_nmb'].'</option>';
                }
                echo '</select>
                  </div>
                </div>';

              /* echo '<div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Tipo de producto</label>
                    <select name="tipoprod" id="tipoprod" class="form-control" required="required" >
                    <option value="">¿Como obtienes tu producto?</option>';
                foreach($this->tipoprod as $key => $tipoprod){
                  if($key == $this->model->tipoprod) $seletp = 'selected'; else $seletp = "";
                  echo '<option value="'.$key.'" '.$seletp.'>'.$tipoprod.'</option>';
                }
                echo '</select>
                  </div>
                </div>'; */

              $sqlc = 'SELECT * FROM categorias ';
              $resultc = setq($sqlc);
              while($rowcat = $resultc->fetch_array()){
                echo '<input type="hidden" id="idcat'.$rowcat['cat_id'].'" value="'.$rowcat['cat_siglas'].'">';
              }
              $resultc = setq($sqlc);

              echo '<div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Categoría /  Familia</label>
                    <select name="categoria" id="categoria" class="form-control" required="required" onchange="setnumparte() ;">
                    <option value="">Elije una categoria</option>';
                while($rowc = $resultc->fetch_array()){
                  if($rowc['cat_id'] == $this->model->categoria) $selec = 'selected'; else $selec = "";
                  echo '<option value="'.$rowc['cat_id'].'" '.$selec.'>'.$rowc['cat_nmb'].'</option>';
                }
                echo '</select>
                  </div>
                </div>

                <!-- <div class="col-sm-12 col-md-3 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Número de parte</label>
                    <input class="form-control" type="text" value="'.$this->model->noparte.'" name="noparte" id="noparte" placeholder="Se genera automáticamente" required="required" readonly="readonly" >
                  </div>
                </div> -->

                <!-- <div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Linea de negocio</label>
                    <select name="lineaneg" id="lineaneg" class="form-control" required="required" >
                    <option value="" disabled selected>Elije una Linea de negocio</option>';
                $sqlln = 'SELECT * FROM lineas_negocio WHERE ln_empresa = "'.$_SESSION['emp'].'" ORDER BY ln_alias';
                $resultln = setq($sqlln);
                while($rowln = $resultln->fetch_array()){
                  if($rowln['ln_id'] == $this->model->lineaneg) $seleln = 'selected'; else $seleln = "";
                  echo '<option value="'.$rowln['ln_id'].'" '.$seleln.'>'.$rowln['ln_alias'].' - '.$rowln['ln_nmb'].'</option>';
                }
                echo '</select>
                  </div>
                </div> -->
                <div class="col-sm-12 col-md-6 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Producto/Servicio SAT
                      <a type="button" href="popup/setprodsat?modulo='.$_GET['modulo'].'&accion=insertd" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                        <button type="button" class="btn btn-sm btn-primary" >
                          <i class="fas fa-search"></i>
                        </button>
                      </a>
                    </label>
                      <select name="clavesat" id="clavesat" class="form-control" required>';
                        if($this->model->clavesat) $selsat = '0';
                        else $selsat = 'selected';

                        $sqlln = 'SELECT * FROM prodsat';
                        $resultln = setq($sqlln);
                        while($rowln = $resultln->fetch_array()){
                          if($rowln['p_claveps'] == $this->model->clavesat) $seleln = 'selected'; else $seleln = "";
                          echo '<option value="'.$rowln['p_claveps'].'" '.$seleln.'>'.$rowln['p_claveps'].' - '.$rowln['p_alias'].'</option>';
                        }
                        echo '<option value="0" '.$selsat.'>00000000 - REGISTRAR SIN PRODUCTO</option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="col-sm-6 col-xs-12 col-md-3 mt-6">
                    <div class="" title="Al marcar como inventariado, tus productos quedan condicionados a tener existencia física para venta">
                      <label class="font-weight-bold">Inventariado</label>';
                      if($this->model->inventariado == "1" || $this->model->inventariado == "") $checked2 = "checked";
                      else $checked2 = "";
                      echo '
                      <input class= "flipswitch form-control" type="checkbox" name="inventariado" id="inventariado" '.$checked2.'>
                    </div>
                  </div>
                  <div class="col-sm-6 col-xs-12 col-md-3 mt-6">
                    <div class="">
                      <label>Estatus del Producto</label>';
                      if($this->model->estatus == "A" || $this->model->estatus == "") $checked = "checked";
                      else $checked = "";
                      echo '
                      <input class= "flipswitch form-control" type="checkbox" name="estatusp" id="estatusp" '.$checked.'>
                    </div> 
                  </div>';
                

                $costo = busca($this->model->id, 'articulo_proveedor', 'ap_activo = "1" AND ap_estatus = "1" AND ap_articulo', 'ap_costo');
                if(!$costo) $costo = "0.00";

                echo '<div class="col-sm-12 col-md-3 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Costo</label>
                    <input class="form-control" type="number" value="'.$costo.'" name="costo" id="costo" placeholder="Costo del articulo" required="required" onfocus="this.select();" >
                  </div>
                </div>';
                
                $precio = busca($this->model->id, 'articulos_precios', 'ap_modelo IS NULL AND ap_activo = "1" AND ap_articulo', 'ap_precio');
                if(!$precio) $precio = "0.00";
                $preciol = busca($this->model->id, 'articulos_precios', 'ap_modelo IS NULL AND ap_activo = "1" AND ap_articulo', 'ap_preciol');
                if(!$preciol) $preciol = "0.00";

                echo '<div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Precio de venta</label>
                    <input class="form-control" type="number" value="'.$precio.'" name="precio" id="precio" placeholder="Precio de venta" required="required"  onfocus="this.select();">
                  </div>
                </div>
                <div class="col-sm-11 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Precio de lista</label>
                    <div class="input-group">
                      <button type="button" id="copiar" onclick="copyprecio();" class="btn btn-primary"><i class="fas fa-copy"></i></button>
                      <input class="form-control" type="number" value="'.$preciol.'" name="preciol" id="preciol" placeholder="Precio en el catálogo" required="required" onfocus="this.select();">
                    </div>
                    
                  </div>
                </div>
                
                
                <div class="col-sm-6 col-xs-12 col-md-5 mt-2">
                  <div class="" title="¿El artículo es un paquete que incluye otros artículos?">
                    <label>Paquete</label>';
                    if($this->model->tipoprod == "M") {
                      $checked3 = "checked";
                      $hiddenm = "hidden";
                    }
                    else {
                      $checked3 = "";
                      $hiddenm = "";
                    }
                    echo '
                    <input class= "flipswitch2 form-control" type="checkbox" name="tipoprod" id="tipoprod" onchange="espaquete();" '.$checked3.'>
                  </div> 
                </div>
                <div class="col-sm-12 col-md-5 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Link en página web</label>
                    <input class="form-control" type="text" value="'.$this->model->url.'" name="url" id="url" placeholder="Link a la página web" >
                  </div>
                </div>';
                if($this->model->descuento == "1"){
                  $checked4 = "checked";
                } else {
                  $checked4 = "";
                }
                echo '<div class="col-sm-6 col-md-2 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Aplica descuento</label>
                    <input class="form-control flipswitch2" type="checkbox"  name="descuento" id="descuento" '.$checked4.'>
                  </div>
                </div>';
                echo '<div class="col-sm-6 col-xs-12 col-md-4 mt-2" id="mdetallesdiv" '.$hiddenm.'>
                <div class="" title="¿Mostrar las especificaciones en el documento PDF de la cotización?">
                  <label>Mostrar detalles en PDF</label>';
                  if($this->model->mdetalle == "1")$checkedd = "checked";
                  else $checkedd = "";
                  echo '
                  <input class= "flipswitch2 form-control" type="checkbox" name="mdetalle" id="mdetalle" '.$checkedd.'>
                </div> 
              </div>';
              echo '<div class="col-6 col-md-4 mt-2" id="mdetallesdiv" '.$hiddenm.'>
                <div class="" title="¿EL artículo tiene envío gratis?">
                  <label>Envío gratis</label>';
                  if($this->model->enviogratis == "1")$checkede = "checked";
                  else $checkede = "";
                  echo '
                  <input class= "flipswitch2 form-control" type="checkbox" name="enviogratis" id="enviogratis" '.$checkede.'>
                </div> 
              </div>';
                if($this->model->tipoprod == "M")
                  $hiddenexp = '';
                else 
                  $hiddenexp = 'hidden';

                if($this->model->expira == "0000-00-00") $fecha = date('Y-m-d',strtotime('last day of this month'));
                else $fecha = $this->model->expira;
                echo '<div class="col-sm-12 col-md-4 mt-2" id="caduca" '.$hiddenexp.' >
                <div class="mb-5">
                  <label class="font-weight-bold">Fecha de vencimiento</label>
                  <input class="form-control" type="date" value="'.$fecha.'" name="expira" id="expira" placeholder="Fecha de vencimiento del producto" >
                </div>
                </div>
                ';
                ///
                echo '
                <div class="col-sm-12 col-md-4 mt-2" id="tarifa" '.$hiddenm.'>
                <div class="mb-5">
                  <label for="" class="">Tarifa</label>
                  <select name="tarifa" id="tarifa" class="form-control">
                  <option value="" class="">Ninguna</option>
                  ';
                  $sqltar = 'SELECT * FROM tarifas_envios WHERE te_estatus = "A" ';
                  $resulttar = setq($sqltar);
                  while($rowtar = $resulttar->fetch_array()){
                    if($rowtar['te_id'] == $this->model->tarifa) $seltar = 'selected';
                    else $seltar = '';
                    echo '
                    <option value="'.$rowtar['te_id'].'" class="" '.$seltar.'>'.$rowtar['te_nmb'].' - $'.number_format($rowtar['te_costo']).'</option>
                    ';
                  }
                echo '
                  </select>
                </div>
                </div>
                  ';
                  ///
                echo '
            </div>
            <div class="col-12 mt-8" id="medidas" '.$hiddenm.'>
              <h3>Medidas</h3>
              <div class="row">
                <div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Largo  (en metros)</label>
                    <input class="form-control" step="0.01" type="number" value="'.$this->model->largo.'" name="largo" id="largo" placeholder="Largo"  >
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Ancho  (en metros)</label>
                    <input class="form-control" step="0.01" type="number" value="'.$this->model->ancho.'" name="ancho" id="ancho" placeholder="Ancho"  >
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Alto (en metros)</label>
                    <input class="form-control" step="0.01" type="number" value="'.$this->model->alto.'" name="alto" id="alto" placeholder="Alto" >
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 mt-2">
                  <div class="mb-5">
                    <label class="font-weight-bold">Peso (en kilogramos)</label>
                    <input class="form-control" step="0.01" type="number" value="'.$this->model->peso.'" name="peso" id="peso" placeholder="Peso" >
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 mt-4">
              <center><button type="button" id="sendform" class="btn btn-success" onclick="checarcb('.$this->model->id.');">
                <i class="fas fa-save"></i> Guardar</button>
              </center>
            </div>
          </div>
        </div>
      </div>
    </form>';

 
  ?> 
  <script>
    function espaquete(){
      var prod = document.getElementById("tipoprod");
      var med = document.getElementById("medidas");
      var caduca = document.getElementById("caduca");
      var mdetalle = document.getElementById("mdetallesdiv");
      var trifa = document.getElementById("tarifa");
      if(prod.checked){
        med.hidden = true;
        caduca.hidden = false;
        mdetalle.hidden = true;
        trifa.hidden = true;
      } else {
        med.hidden = false;
        caduca.hidden = true;
        mdetalle.hidden = false;
        trifa.hidden = false;
      }
    }
    function copyprecio(){
      var precio = document.getElementById("precio");
      var preciol = document.getElementById("preciol");

      preciol.value = precio.value;
    }

    function checarcb(id){
      var cb = document.getElementById('cb');
      var checkauto = document.getElementById('checkauto');
      var form = document.getElementById('cliedit');
      event.preventDefault();
      console.log('valor', parseInt(id));
      if(parseInt(id) == 0 ){
        $.ajax({
          url: 'query/checarcb.php',
          type: 'POST',
          dataType: 'html',
          data: { 'cb': cb.value},
        })
        .done(function(respuesta){
          console.log(respuesta);
          if(respuesta != "0"){
            if(checkauto.value == "1"){
              cb.value = respuesta;
              if(!form.checkValidity()){
                alert('Faltan datos por llenar o son erroneos. Verifica tu información.');
              } else {
                form.submit();
              }
            } else {
              alertSweet('error', 'Error', 'El código de barras ingresado ya existe, verifiquelo para continuar.');
            }
          } else{
            if(!form.checkValidity()){
              alert('Faltan datos por llenar o son erroneos. Verifica tu información.');
            } else {
              form.submit();
            }
          }
        });
      } else{
        if(!form.checkValidity()){
          alert('Faltan datos por llenar o son erroneos. Verifica tu información.');
        } else {
          form.submit();
        }
      }
    }

    function alertSweet(icono, titulo, mensaje) {
      Swal.fire({
          icon: icono,
          title: titulo,
          text: mensaje
      }).then((result) => {
          if (result.isConfirmed || result.isDenied) {
              Swal.close();
          }
      });
    }
  </script>
  <?php

  echo '</div></div>';
  
    if($this->model->id){

      echo '<div class="col-md-3">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title" id="basic-layout-form">Actividades</h4>
                  <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i> </a>
                  <div class="heading-elements">
                    <ul class="list-inline mb-0">
                      <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                    </ul>
                  </div>-->
                </div>';
      
      echo $this->actividades($this->model->tipoprod);
    }
  }
  function fichatecnica(){
    $titulo = "FICHA TÉCNICA DE ".$this->model->nmb;
    echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
    echo '<a href="?modulo=articulos&accion=index">
            <button class="mb-2 mr-2 btn btn-warning">
              <i class="fa fa-arrow-left"></i> Atrás
            </button>
          </a>
          <button class="mb-2 mr-2 btn btn-primary" onclick="setftec();">
            <i class="fa fa-paper-plane-o"></i> Guardar ficha técnica
          </button>
          </div></div>';
    ?>
    <script>
      function setftec(){
        document.ftec.submit();
      }
    </script>
    <?php
    echo '<div class="row">
            <div class="col-md-9">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title" id="basic-layout-form">'.$titulo.'</h4>
                  <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                  <div class="heading-elements">
                    <ul class="list-inline mb-0">
                      <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                      <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                      <li><a data-action="close"><i class="icon-cross2"></i></a></li>
                    </ul>
                  </div>-->
                </div>';

    if($this->model->mnoporte == "1" || !$this->model->articulof) $chmnp = "checked"; else $chmnp = "";
    if($this->model->mmodelo == "1" || !$this->model->articulof) $chmmd = "checked"; else $chmmd = "";
    if($this->model->mmarca == "1" || !$this->model->articulof) $chmma = "checked"; else $chmma = "";

    echo '<div class="row">
          <form autocomplete="off" method="post" action="?modulo=articulos&accion=setftecnica&id='.$this->model->id.'" name="ftec" onsubmit="checksubmit(); return fale">';
    echo '<div class="col-sm-12 col-md-12 mt-2">
            <div class="mb-5">
              <label class="label-md">Nombre comercial</label>
              <input class="form-control"  type="text" value="'.$this->model->nmb.'" name="nmb" id="nmb" required="required" autofocus placeholder="Nombre del producto">
            </div>
          </div>';
    echo '<div class="col-sm-12 col-md-3 mt-2">
            <div class="mb-5">
              <label class="label-md">Mostrar No. Parte</label>
              <input type="checkbox" name="mnoporte" '.$chmnp.' class="flipswitchok" />
            </div>
          </div>';
    echo '<div class="col-sm-12 col-md-3 mt-2">
            <div class="mb-5">
              <label class="label-md">Mostrar Modelo</label>
              <input type="checkbox" name="mmodelo" '.$chmmd.' class="flipswitchok" />
            </div>
          </div>';
    echo '<div class="col-sm-12 col-md-3 mt-2">
            <div class="mb-5">
              <label class="label-md">Mostrar Marca</label>
              <input type="checkbox" name="mmarca" '.$chmma.' class="flipswitchok" />
            </div>
          </div>';

    echo '<div class="col-sm-12 col-md-12 mt-2">
            <div class="mb-5">
              <label class="label-md">Descripción del artículo</label>
              <textarea name="descripcion" required="required" id="artiulo-descripcion" class="" rows="3"  placeholder="Describe lo mejor posible tu producto" data-sample-short>'.$this->model->descripcion.'</textarea>
            </div>
          </div>';

    echo '<div class="col-sm-12 col-md-12 mt-2">
            <div class="mb-5">
              <label class="label-md">Especificaciones técnicas</label>
              <textarea name="especificaciones" required="required" id="artiulo-editortecnicas" class="editor" rows="3"  placeholder="Dime como es tu producto" data-sample-short>'.$this->model->especificaciones.'</textarea>
            </div>
          </div>';

    echo '<div class="col-sm-12 col-md-12 mt-2">
            <div class="mb-5">
              <label class="label-md">Otras caracteristicas</label>
              <textarea name="ocaracteristicas" required="required" id="artiulo-ocaracteristicas" class="editor" rows="3"  placeholder="Otras caracteristicas que quieres mostrar" data-sample-short>'.$this->model->ocaracteristicas.'</textarea>
            </div>
          </div>';

    echo '</form></div>';

    echo '</div></div>';
    ?>
      <script src="assets/js/tinymce/tinymce.min.js"></script>
      <script>
      tinymce.init({
        selector: 'textarea#artiulo-descripcion ',
        height: 150,
        license_key: 'gpl',
        base_url: 'assets/tinymce',
        menubar: false,
        plugins: ['advlist autolink lists link image charmap print preview anchor','searchreplace visualblocks code fullscreen','insertdatetime media table paste code help wordcount'],
        toolbar: 'undo redo | formatselect | ' + 'bold italic backcolor | alignleft aligncenter ' + 'alignright alignjustify | bullist numlist outdent indent | ' + 'removeformat ',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
      });
      tinymce.init({
        selector: 'textarea#artiulo-editortecnicas',
        height: 150,
        menubar: false,
        plugins: ['advlist autolink lists link image charmap print preview anchor','searchreplace visualblocks code fullscreen','insertdatetime media table paste code help wordcount'],
        toolbar: 'undo redo | formatselect | ' + 'bold italic backcolor | alignleft aligncenter ' + 'alignright alignjustify | bullist numlist outdent indent | ' + 'removeformat ',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
      });
      tinymce.init({
        selector: 'textarea#artiulo-ocaracteristicas',
        height: 150,
        menubar: false,
        plugins: ['advlist autolink lists link image charmap print preview anchor','searchreplace visualblocks code fullscreen','insertdatetime media table paste code help wordcount'],
        toolbar: 'undo redo | formatselect | ' + 'bold italic backcolor | alignleft aligncenter ' + 'alignright alignjustify | bullist numlist outdent indent | ' + 'removeformat ',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
      });
    </script>

  <?php

  echo '<div class="col-md-3">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title" id="basic-layout-form">Actividades</h4>
              <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i>Atrás </a>
              <div class="heading-elements">
                <ul class="list-inline mb-0">
                  <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                </ul>
              </div>-->
            </div>';

    echo $this->actividades($this->model->tipoprod);
  }

  function images(){
    $atras = '
    <a href="?modulo=articulos&accion=index">
      <button class="btn btn-sm btn-warning">
        <i class="fa fa-arrow-left"></i> Atrás
      </button>
    </a>
    ';
    toolbar($_GET['modulo'],$atras);

    $titulo = "IMAGENES DE ".$this->model->nmb;
    echo '<input type="hidden" value="'.$this->model->id.'" id="articulo">';
    echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
    echo '</div></div>';

    echo '<div class="row">
            <div class="card col-md-9">
              <div class="card-header">
                <h1 class="card-title" id="basic-layout-form">'.$titulo.'</h1>
                <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                <div class="heading-elements">
                  <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                    <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                    <li><a data-action="close"><i class="icon-cross2"></i></a></li>
                  </ul>
                </div>-->
              </div>
              <div class="card-body">';
      echo '    <div class="">
                  <div class="card-block">
                    <div class="form-body">
                      <div class="row">
                      <div class="col-md-12 col-12">
                        <form id="imagesdropzone" class="dropzone" action="?modulo=articulos&accion=setimages&articulo='.$this->model->id.'">
                          <input type="hidden" name="articulo" value="'.$this->model->id.'" />
                        </form>
                      </div>';

   ?>
      <script>
        function setup(idm){
          document.getElementById("up").disabled = true;
          var articulo = document.getElementById("articulo").value;
          document.location.href="?modulo=articulos&accion=setup&idm=" + idm + "&articulo=" + articulo;
          return false;
        }
        function setdown(idm){
          document.getElementById("down").disabled = true;
          var articulo = document.getElementById("articulo").value;
          document.location.href="?modulo=articulos&accion=setdown&idm=" + idm + "&articulo=" + articulo;
          return false;
        }

      </script>
      <div class="mt-4">
        <div class="mt-1 alert alert-primary text-secondary text-center font-weight-bolder col-md-12"><h5>Imagenes de <?php echo $this->model->nmb ?></h5></div>
        <?php
          $sql = 'SELECT * FROM imagenes WHERE i_idproducto = "'.$this->model->cb.'" ORDER BY i_idimg ';
          $result = setq($sql) ;
          $resultn = setq($sql) ;
          $maxn = $resultn->num_rows;
          $ic = 0;
          while($row = $result->fetch_array()){
            $ic++;
            ?>
              <div class="col-md-2 mb-2">
                  <img class="img-thumbnail" src="<?php echo 'img/productos/'.$row['i_nmb'].'.'.$row['i_ext']; ?>" alt="<?php echo $row['i_descripcion'] ?>">
                <br>
                <?php
                  //echo $row['i_id'].' '.$this->model->id;
                ?>
                <a method="post" href="?modulo=articulos&accion=delimg&articulo=<?php echo $this->model->id ?>&idm=<?php echo $row['i_id'] ?>">
                  <button class="btn btn-danger border-secondary col-md-12" role="button">
                    <i class="icon-trash2"></i> Borrar imagen
                  </button>
                </a>
                <div class="row">
                  <div class="col-md-12"><label class="text-center">Orden <?php echo $row['i_idimg']; ?></label></div>
                  <?php
                  /*
    if($ic > 1){
                  ?>
                    <button class="btn btn-light border-secondary col-md-6" role="button" onclick="setdown(<?php echo $row['i_id']; ?>);return false;" id="down" >
                      <i class="fa fa-arrow-left"></i> Atrás
                    </button>
                  <?php }
                  else{
                  ?>
                    <div class="col-md-6">&nbsp;</div>
                  <?php
                  }

*/
                  if($ic < $maxn){
                  ?>
                    <button class="btn btn-light border-secondary" role="button" onclick="setup(<?php echo $row['i_id']; ?>);return false;" id="up">
                      <i class="fas fa-arrow-right"></i> Adelante
                    </button>
                  <?php
                  } ?>
                </div>
              </div>
            <?php
          }
        ?>
      </div>
  <?php
    echo '
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>';
  ?>
    <script src="assets/js/dropzone-artimage.js"></script>
  <?php
    if($this->model->id){
      echo '<div class="col-md-3">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title" id="basic-layout-form">Actividades</h4>
                  <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i>Atrás </a>
                  <div class="heading-elements">
                    <ul class="list-inline mb-0">
                      <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                    </ul>
                  </div>-->
                </div>';

        echo $this->actividades($this->model->tipoprod);
    }
  }

 function precios(){
  $titulo = "".$this->model->nmb;
  echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
  echo '<a href="?modulo=articulos&accion=index">
          <button class="mb-2 mr-2 btn btn-warning">
            <i class="fa fa-arrow-left"></i> Atrás
          </button>
        </a>';
        if($this->model->tipoprod == "C"){
          $cantidadprod = busca($this->model->id,'articulo_proveedor','ap_activo = "1" AND ap_estatus = "1" AND ap_articulo','COUNT(ap_id)');
          if($cantidadprod > 0) $border = '';
          else $border = 'border-style: solid;
          border-color: red;
          border-width: medium;';
          echo'<a  data-fancybox data-type="ajax" data-src="popup/setarticuloproveedor.php?articulo='.$this->model->id.'&rand='.rand(1,999).'" href="javascript:;" >
            <button style="'.$border.'" class="mb-2 btn btn-primary">
              <i class="fa fa-plus"></i> Agregar proveedor
            </button>
          </a>';
        }

  echo '</div></div>';
    ?>
    <script>
      function setftec(){
        document.ftec.submit();
      }
    </script>
    <?php
    echo '
            <div class="col-md-9">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title" id="basic-layout-form">'.$titulo.'</h5>';
                  if($this->model->tipoprod == "C"){
                    if($cantidadprod > 0) echo '';
                    else echo '<div class="alert alert-danger col-md-12 text-xs-center mt-2 mb-2">Agrega un proveedor a tu producto</div>'; 
                  }
                  echo '
                  <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>-->
                </div>
                ';

  $valorusd = busca($_SESSION['emp'],'empresas','e_id','e_valorusd');
  if($this->model->ausd == "1") $checkusd = " active "; else $checkusd = "";
  if($this->model->aiva == "1") $checkiva = " active "; else $checkiva = "";
  if($valorusd == 0){
    $disabusd = ' readonly="readonly" disabled';
    $leyusd = ' data-toggle="tooltip" data-placement="top" title="Valor del dolar no definido, vaya al menu configuracion para establecer un precio" ';
  }
  
  if($this->model->tipoprod == "C"){
    echo '<script>
            function fixprov(proveedor){
              var conf = confirm("¿Deseas marcar este proveedor como predeterminado para compras?");
              if(conf == true){
                window.location.href="?modulo=articulos&accion=proveedoractive&articulo='.$this->model->id.'&proveedor=" + proveedor;
              }
            }
            function delprov(proveedor){
              var conf = confirm("¿El proveedor ha descontinuado el producto de su catalogo?\nEn la sección historial podrás reactivarlo cuando este disponible nuevamente");
              if(conf == true){
                window.location.href="?modulo=articulos&accion=delprodprov&articulo='.$this->model->id.'&proveedor=" + proveedor;
              }
            }
          </script>';
    echo '<div class="card mb-0 table-responsive col-12 col-md-12">
            <form method="post" action="?modulo=articulos&accion=updatecostos&articulo='.$this->model->id.'">
              <table width="100%" class="table-stripped table-bordered table-hover">
                <thead class="bg-light-blue bg-darken-2 text-white">
                  <tr>
                    <td width="6%">Fijar/Borrar</td>
                    <td width="20%">Proveedor</td>
                    <td width="32%">No. Parte</td>';
                      if(busca($this->model->id,'articulo_proveedor','ap_activo = "1" AND ap_estatus = "1" AND ap_articulo','ap_proveedor') == "3" && $_SESSION['emp'] == "1")
                      echo'<td width="15%">ID syscom</td>';
                    echo'
                    <td width="18%">Costo</td>
                    <td width="7%">USD</td>
                    <td width="7%">+IVA</td>
                    <td width="10%">Final</td>
                  </tr>
                </thead>
                <tbody>';
      $sql = 'SELECT * FROM articulo_proveedor WHERE ap_articulo = "'.$this->model->id.'"
              AND ap_estatus = "1"  ORDER BY ap_proveedor';
      $result = setq($sql);
      $valorusd = busca($_SESSION['emp'],'empresas','e_id','e_valorusd');

      while($row = $result->fetch_array()){
        $precio = $row['ap_costo'];
        if($row['ap_iva'] == "1"){
          $iva = "checked";
          $precio = $row['ap_costo']*1.16;
        }
        else $iva = "";

        if($row['ap_usd'] == "1"){
          $usd = "checked";
          $precio = $precio*$valorusd;
        }
        else $usd = "";
        if($row['ap_activo'] == "1") $classtr = ' class="bg-success" '; else $classtr = "";

        echo '<input type="hidden" name="proveedor'.$row['ap_proveedor'].'" value="'.$row['ap_proveedor'].'">';
        echo '<tr>
                <td '.$classtr.'>';
        if($row['ap_activo'] != "1"){
          echo '<button type="button" class="btn-sm btn-secondary" onclick="fixprov('.$row['ap_proveedor'].');"><i class="icon-crosshairs"></i></button>
                ';
        }
        else{
          echo '<button type="button" class="btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="Proveedor predeterminado" ><i class="icon-check-circle-o"></i></button>
                <input type="hidden" name="activo'.$row['ap_proveedor'].'" value="1" />';
        }
        echo '<button type="button" class="btn-sm btn-danger" onclick="delprov('.$row['ap_proveedor'].');"><i class="icon-eraser"></i></button>';

        echo '</td>
                <td>'.busca($row['ap_proveedor'],'proveedores','p_id','p_alias').'</td>
                <td><input type="text" class="form-control" name="noparteprov'.$row['ap_proveedor'].'" value="'.$row['ap_noparteprov'].'" onfocus="this.select();" /></td>';
                  if(busca($this->model->id,'articulo_proveedor','ap_activo = "1" AND ap_estatus = "1" AND ap_articulo','ap_proveedor') == "3" && $_SESSION['emp'] == "1")
                  echo '<td><input type="text" class="form-control" name="idsyscom" id="idsyscom" value="'.$row['ap_idproducto'].'" required/> ';
                echo '
                <td><input type="number" class="form-control" min="0" max="999999" step="0.01" name="costo'.$row['ap_proveedor'].'" value="'.$row['ap_costo'].'" onfocus="this.select();" /></td>
                <td '.$leyusd.'><input type="checkbox" '.$leyusd.' '.$disabusd.' name="usd'.$row['ap_proveedor'].'" '.$usd.' /></td>
                <td><input type="checkbox" name="iva'.$row['ap_proveedor'].'" '.$iva.' /></td>
                <td class="text-right">$'.number_format($precio,2).'</td>
              </tr>';
      }
      echo '<tr><td colspan="7">
                  <center><button type="submit" class="btn btn-success" id="" />
                    <i class="fa fa-redo2"></i> Actualizar costos
                  </button></center>
                </td></tr>';
      echo '    </tbody>
              </table>
            </form>
          </div>';

    echo '<div class="card col-12 col-md-12">
          <div class="table table-responsive" role="alert">
          <form method="post" action="?modulo=articulos&accion=updateprecios&articulo='.$this->model->id.'">
          <table class="table table-bordered mb-0 table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center" width="40%">Esquema</th>
                <th class="text-center" width="20%">Precio</th>
                <th class="text-center" width="20%">Utilidad($)</th>
                <th class="text-center" width="20%">Utilidad(%)</th>
              </tr>
            </thead>
            <tbody>';

              $sql = 'SELECT ep_id,ep_nmb,ap_costo,ap_precio
                      FROM esquema_precio INNER JOIN articulos_precios ON ep_id = ap_esquema
                      WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_estatus = "A" AND ap_activo = "1"
                      AND ap_articulo = "'.$this->model->id.'"';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                $utilidad = $row['ap_precio']-$row['ap_costo'];
                //$porut = (1-($row['ap_costo']/$row['ap_precio']))*100;
                $porut = (($utilidad/$row['ap_costo']))*100;  
                ?>
                  <tr>
                    <th><?php echo $row['ep_nmb'] ?></th>
                    <td class="number-align">
                      <input type="number" name="precio<?php echo $row['ep_id'] ?>" class="form-control number-align" value="<?php echo number_format($row['ap_precio'],2,'.','') ?>" step="0.01" required onfocus="this.select();" />
                    </td>
                    <td class="number-align">$<?php echo number_format($utilidad,2) ?></td>
                    <td class="number-align"  data-toggle="tooltip" data-placement="top" title="<?php echo '% = '.number_format($utilidad,2).' / '.number_format($row['ap_costo'],2); ?>"><?php echo number_format($porut,0) ?>%</td>
                  </tr>
                <?php
              }
          echo '</tbody>
          <tr><td colspan="4">
                  <center><button type="submit" class="btn btn-primary" id="" />
                    <i class="fa fa-redo2"></i> Actualizar precios
                  </button></center>
                </td></tr>
          </table></form>
        </div >
      </div>
    </div>';
  }

  elseif($this->model->tipoprod == "P" || $this->model->tipoprod == "S"){ //comentado
    $sql = 'SELECT * FROM articulo_proveedor WHERE ap_articulo = "'.$this->model->id.'"
            AND ap_estatus = "1" AND ap_proveedor = "0" ORDER BY ap_proveedor';
    $result = setq($sql);
    $row = $result->fetch_array();
    $precio = $row['ap_costo'];
    if($row['ap_iva'] == "1"){
      $iva = "checked";
      $precio = $row['ap_costo']*1.16;
    }
    else $iva = "";

    if($row['ap_usd'] == "1"){
      $usd = "checked";
      $precio = $precio*$valorusd;
    }
    else $usd = "";

    $valorusd = busca($_SESSION['emp'],'empresas','e_id','e_valorusd');

    echo '<div class="row mt-4">';
    echo '<div class="col-12 col-md-12 table table-responsive ">
            <form method="post" action="?modulo=articulos&accion=updatecostos&articulo='.$this->model->id.'&prov=1">
            <input type="hidden" name="proveedor" value="0" />
            <input type="hidden" name="noparteprov" value="'.$this->model->noparte.'" />
              <table width="100%" class="table-stripped table-bordered table-hover" hidden>
                <thead class="bg-light-blue bg-darken-2 text-white">
                  <tr>
                    <td>Costo</td>
                    <td width="7%">USD</td>
                    <td width="7%">+IVA</td>
                    <td width="10%">Final</td>
                    <td width="20%"></td>
                  </tr>
                </thead>
                <tbody>';
      echo '<tr>
              <td><input type="number" autofocus class="form-control" min="0" max="999999" step="0.0001" name="costo" value="'.$row['ap_costo'].'" onfocus="this.select();" /></td>
              <td><input type="checkbox" name="usd" '.$usd.' /></td>
              <td><input type="checkbox" name="iva" '.$iva.' /></td>
              <td class="text-right">$'.number_format($precio,2).'</td>
              <td><center><button type="submit" class="btn btn-success" id="" />
                    <i class="fa fa-redo2"></i> Actualizar costos
                  </button></center></td>
            </tr>';
      echo '    </tbody>
              </table>
            </form>
          </div>

          <div class=" col-12 col-md-12">
          <div class="table table-responsive" role="alert">
          <form method="post" action="?modulo=articulos&accion=updateprecios&articulo='.$this->model->id.'">
          <table class="table table-bordered mb-0 table-striped table-hover">
            <input type="hidden" name="proveedor" value="OK"/>
            <thead class="bg-light-blue bg-darken-2 text-white">
              <tr>
                <th class="text-center" width="40%">Esquema</th>
                <th class="text-center" width="20%">Precio</th>
                <th class="text-center" width="20%">Utilidad($)</th>
                <th class="text-center" width="20%">Utilidad(%)</th>
              </tr>
            </thead>
            <tbody>';
              $sqlep = 'SELECT ep_id,ep_nmb
                        FROM esquema_precio WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_estatus = "A"';
              $resultep = setq($sqlep);
              $nump = 0;
              while($rowep = $resultep->fetch_array()){
                $sql = 'SELECT ap_costo,ap_precio FROM esquema_precio INNER JOIN articulos_precios ON ep_id = ap_esquema
                        WHERE ep_id = "'.$rowep['ep_id'].'" AND ap_activo = "1" AND ap_articulo = "'.$this->model->id.'" ORDER BY ep_id';
                $result = setq($sql);
                if($result->num_rows > 0){
                  while($row = $result->fetch_array()){
                    $nump++;
                    $utilidad = $row['ap_precio']-$row['ap_costo'];
                    //$porut = (1-($row['ap_costo']/$row['ap_precio']))*100;
                    $porut = (($utilidad/$row['ap_precio']))*100;
                    ?>
                      <tr>
                        <th><?php echo $rowep['ep_nmb'] ?></th>
                        <td class="number-align">
                          <input type="number" name="precio<?php echo $rowep['ep_id'] ?>" class="form-control number-align" value="<?php echo number_format($row['ap_precio'],2,'.','') ?>" step="0.01" required onfocus="this.select();" />
                        </td>
                        <td class="number-align">$<?php echo number_format($utilidad,2) ?></td>
                        <td class="number-align"><?php echo number_format($porut,0) ?>%</td>
                      </tr>
                    <?php
                  }
                }else{
                  $nump++;
                  $utilidad = 0;
                  $porut = 0;
                  ?>
                    <tr>
                      <th><?php echo $rowep['ep_nmb'] ?></th>
                      <td class="number-align">
                        <input type="number" name="precio<?php echo $rowep['ep_id'] ?>" class="form-control number-align" value="<?php echo number_format(0,2,'.','') ?>" step="0.01" required onfocus="this.select();" />
                      </td>
                      <td class="number-align">$<?php echo number_format($utilidad,2) ?></td>
                      <td class="number-align"><?php echo number_format($porut,0) ?>%</td>
                    </tr>
                  <?php
                }
              }

              /*$sql = 'SELECT ep_id,ep_nmb,ap_costo,ap_precio
                      FROM esquema_precio INNER JOIN articulos_precios ON ep_id = ap_esquema
                      WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_estatus = "A" AND ap_activo = "1"
                      AND ap_articulo = "'.$this->model->id.'" ORDER BY ep_id';
              $result = setq($sql);
              $nump = 0;
              while($row = $result->fetch_array()){
                $nump++;
                $utilidad = $row['ap_precio']-$row['ap_costo'];
                //$porut = (1-($row['ap_costo']/$row['ap_precio']))*100;
                $porut = (($utilidad/$row['ap_precio']))*100;
                ?>
                  <tr>
                    <th><?php echo $row['ep_nmb'] ?></th>
                    <td class="number-align">
                      <input type="number" name="precio<?php echo $row['ep_id'] ?>" class="form-control number-align" value="<?php echo number_format($row['ap_precio'],2,'.','') ?>" step="0.01" required onfocus="this.select();" />
                    </td>
                    <td class="number-align">$<?php echo number_format($utilidad,2) ?></td>
                    <td class="number-align"><?php echo number_format($porut,0) ?>%</td>
                  </tr>
                <?php
              }*/
          if($nump == 0){
            $sqlp = 'SELECT ep_id,ep_nmb
                     FROM esquema_precio WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_estatus = "A"';
            $resultp = setq($sqlp);
            $nump = 0;
            while($rowp = $resultp->fetch_array()){
              $nump++;
              $utilidad = 0;
              $porut = 0;
              ?>
                <tr>
                  <th><?php echo $rowp['ep_nmb'] ?></th>
                  <td class="number-align">
                    <input type="number" name="precio<?php echo $rowp['ep_id'] ?>" class="form-control number-align" value="<?php echo number_format(0,2,'.','') ?>" step="0.01" required onfocus="this.select();" />
                  </td>
                  <td class="number-align">$<?php echo number_format($utilidad,2) ?></td>
                  <td class="number-align"><?php echo number_format($porut,0) ?>%</td>
                </tr>
              <?php
          }
        }
          echo '</tbody>
          <tr><td colspan="4">
                  <center><button type="submit" class="btn btn-primary" id="" />
                    <i class="fa fa-redo2"></i> Actualizar precios
                  </button></center>
                </td></tr>
          </table></form>
        </div >
      </div>
    </div>';
  }elseif($this->model->tipoprod == "M"){
    echo '<div class="row mt-4">
            <div class="col-12 col-md-12 mt-2" >
              <div class="mb-5 p-2">
                <label for="agregar" class">El producto seleccionado pertenece a la categoria de combos, para visualizar los precios dirigase a la opción de "Combo"</label><br>
              </div>
            </div>
          </div>
        </div>
      ';
  }

  echo '</div></div>';
    if($this->model->id){
      echo '<div class="col-md-3">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title" id="basic-layout-form">Actividades</h4>
                  <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i>Atrás </a>
                  <div class="heading-elements">
                    <ul class="list-inline mb-0">
                      <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                    </ul>
                  </div>-->
                </div>';
      echo $this->actividades($this->model->tipoprod);
    }
  }

  function combos(){
    ?>
    <script>
      
      $(document).ready(function() {
        $('#producto').on('keyup', function() {

          if (event.keyCode == 38 || event.keyCode == 40){
              //console.log('chi');
          }else{
            var key = $(this).val();
            var dataString = 'producto='+key;
            $.ajax({
              type: "POST",
              url: "query/suggestproducts2.php",
              data: {
                "producto": key,
                'from': "articulos"
              },
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
                  return false;
                });
              }
            });
          }
        });
      });
      function checksubmit(){
        document.getElementById("agregar").value = "JD";
        document.getElementById("agregar").disabled = true;
        return true;
      }
    </script>
    <?php
    $titulo = "".$this->model->nmb;
    $atras = '
    <a href="?modulo=articulos&accion=index">
      <button class="btn btn-sm btn-warning">
        <i class="fa fa-arrow-left"></i> Atrás
      </button>
    </a>
    ';
    toolbar($_GET['modulo'],$atras);

    echo '<div class="row mt-7">
      <div class="col-md-9">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title" id="basic-layout-form">'.$titulo.'</h5>
            <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>-->
          </div>';
          echo'<div class=" row mt-4 p-2">
            <form action="?modulo=articulos&accion=insertartcmb&id='.$this->model->id.'" method="post" onsubmit="checksubmit();">
              <input type="hidden" id="costo" name="costo" />
              <input type="hidden" id="importe" name="importe" />
              <input type="hidden" id="linean" name="linean" /> 
              <div class="row">';
                        
              echo'<div class="col-12 col-md-6 mt-2" >
                <div class="mb-5">
                  <label for="agregar">Producto</label><br>
                  <div class="input-group">
                    <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus />
                    <a accesskey="B" href="popup/buscarproduto?from=articulos&tipo=A&id='.$this->model->id.'" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                      <button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Usar el buscador ALT+B " data-original-title="Usar el buscador"><i class="fas fa-search"></i>Buscar</button>
                    </a>
                  </div>
                  <div id="suggestions"></div>
                  </div>
                </div>';
              echo'<div class="col-12 col-md-3 mt-2">
                  <div class="mb-5">
                    <label for="noparte"><b>Cantidad</b></label>
                    <input type="number" min="1" max="999999" step="1" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad requerida" required="required" />
                  </div>
                </div>';
              echo '<div class="col-6 col-md-3 mt-2">
                  <div class="mb-5" >
                    <label for="save"></label><br>
                    <button class="btn btn-primary" type="submit" id="agregar"><i class="fas fa-plus"></i>Agregar</button>
                  </div>
                </div>
              </div>';
              echo '
            </form>
            </div>';
            echo'<div class="table table-responsive col-12 col-md-12 p-2">
              <table width="100%" class="table table-stripped table-bordered table-hover">
                <thead class="bg-primary text-white">
                  <tr>
                    <td width="20%">Producto</td>
                    <td width="20%">Categoria</td>
                    <td width="20%">Precio</td>
                    <td width="15%">Cantidad</td>
                    <td width="5%">Borrar</td>
                  </tr>
                </thead>
                <tbody>';
                  $nRows = $this->model->result->num_rows;
                  if($nRows > 0){
                    $ar = array();
                    $f = 0;
                    $ar[$f]['hijo'] = $this->model->id;
                    while($rowc = $this->model->result->fetch_array()){
                      if($rowc['a_estatus'] == "I") {
                        $style = 'style="background-color:#E66363;"';
                        $style2 = 'style="--bs-table-hover-bg: #E66363 !important;"';
                      }
                      else {
                        $style = '';
                        $style2 = '';
                      }
                      $f++;
                      $ar[$f]['hijo'] = $rowc['ac_ahijo'];
                      $ar[$f]['cantidad'] = $rowc['ac_cantidad'];
                      $ar[$f]['modelo'] = $rowc['a_modelo'];
                      $precio = busca($rowc['ac_ahijo'], 'articulos_precios', 'ap_modelo IS NULL AND ap_activo = "1" AND ap_articulo', 'ap_precio');
                      echo '<tr '.$style2.'>
                        <td width="20%" '.$style.'>'.$rowc['a_nmb'].'</td>
                        <td width="20%" '.$style.'>'.$rowc['cat_nmb'].'</td>
                        <td width="20%" '.$style.' style="text-align: right">$ '.number_format($precio, 2).'</td>
                        <td style="width:25%;" ' . $style . '><input type="number" onchange="updatecant(' . $rowc['ac_id'] . ',' . $rowc['ac_ahijo'] . ');" min="1" step="1" value="'.$rowc['ac_cantidad'].'" name="cantidad'.$rowc['ac_id'].'" id="cantidad'.$rowc['ac_id'].'" class="form-control" placeholder="Cantidad dentro del paquete"></input ></td>
                        <td width="5%" '.$style.'>
                          <button type="button" class="btn-sm btn-danger" onclick="delprov('.$rowc['ac_id'].','.$rowc['ac_ahijo'].');">
                            <i class="fas fa-times-circle" style="color: #ffffff;"></i>
                          </button>
                        </td>
                      </tr>';
                    }
                  }
                echo '</tbody>
              </table>
            </div>';
          echo '<script>
            function delprov(acid,hijo){  
              var conf = confirm("¿Estas seguro que deseas eliminar el producto seleccionado?");
              if(conf == true){
                window.location.href="?modulo=articulos&accion=deleteartcmb&articulo='.$this->model->id.'&acid=" + acid+"&hijo="+hijo;
              }
            }
            function updatecant(alid,ligado){
              var cantidad = document.getElementById("cantidad"+alid).value;
              if(cantidad == "0"){
                alert("La cantidad no puede ser igual a CERO.");
                window.location.href="?modulo=articulos&accion=combos&articulo=' . $this->model->id . '";
              } else {
                window.location.href="?modulo=articulos&accion=updatecmb&articulo=' . $this->model->id . '&acid=" + alid+"&hijo="+ligado+"&cantidad="+cantidad;
              }
            }
          </script>
        </div>
      </div>
      <div class="col-md-3">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title" id="basic-layout-form">Actividades</h4>
          <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i> </a>
          <div class="heading-elements">
            <ul class="list-inline mb-0">
              <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
            </ul>
          </div>-->
        </div>';
        echo $this->actividades($this->model->tipoprod);

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

  function variantes(){
    
    $titulo = "".$this->model->nmb;
    $atras = '<a href="?modulo=articulos&accion=index">
            <button class="mb-2 mr-2 btn btn-sm btn-warning">
              <i class="fa fa-arrow-left"></i> Atrás
            </button>
          </a>';
    $variante = '<a data-fancybox data-type="ajax" data-src="popup/setvariante.php?articulo='.$this->model->id.'" href="javascript:;" >
                    <button type="button" class="mb-2 mr-2 btn btn-sm btn-primary">
                     <i class="fas fa-plus" style="color: #ffffff;"></i> Agregar variante
                    </button>
                  </a>';
      
    toolbar($_GET['modulo'],$atras, '', '', $variante);

    echo '<div class="row mt-5">
              <div class="col-md-9">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title" id="basic-layout-form">Variantes '.$titulo.'</h5>
                    <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>-->
                  </div>';

    $sql = 'SELECT COUNT(*) FROM articulos_variantes WHERE av_articulo = "'.$this->model->id.'" ';
    $result = setq($sql);
    list($numrows) = $result->fetch_array();

    echo '<div class="row mt-4">';
    if($numrows == 0){
      echo '  <div class="offset-1 col-10 col-md-10 alert alert-danger mb-2">
                <center>
                  <b>No existen Variantes de producto</b>
                  
                  </center>
              </div>
              ';
    }
    else{
      $sqlc = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$this->model->id.'"';
      $resultc = setq($sqlc);
      echo '
      <center>
        <div class="col-11 col-md-11 mb-2">
          <table width="100%" class="table table-stripped table-bordered table-hover">
            <thead class="bg-primary text-white">
              <tr>
              <td width="10%" class="p-2">CB</td>
                <td width="10%" class="p-2">Modelo</td>
                <td width="40%" class="p-2">Nombre</td>
                <td width="20%" class="p-2">Precio</td>
                <td width="20%" class="p-2">Acciones</td>
              </tr>
            </thead>
            <tbody>';
              
              while($row = $resultc->fetch_array()){
                $precio = busca($this->model->id, 'articulos_precios', 'ap_modelo = "'.$row['av_modelo'].'" AND ap_activo = "1" AND ap_articulo', 'ap_precio');
                echo '<tr >
                <td width="10%" class="p-2">'.$row['av_cb'].'</td>
                  <td width="10%" class="p-2">'.$row['av_modelo'].'</td>
                  <td width="40%" class="p-2">'.$row['av_nmb'].'</td>
                  <td width="20%" class="p-2">$'.number_format($precio, 2).'</td>
                  <td width="20%" >
                    <a data-fancybox data-type="ajax" data-src="popup/setvariante.php?articulo='.$this->model->id.'&modelo='.$row['av_modelo'].'" href="javascript:;" >
                      <button type="button" class="btn btn-primary btn-sm">
                        <i class="fas fa-pen" style="color: #ffffff;"></i>
                      </button>
                    </a>
                    <button type="button" class="btn-sm btn-danger" onclick="delvar(\''.$row['av_modelo'].'\');">
                      <i class="fas fa-times-circle" style="color: #ffffff;"></i>
                    </button>
                  </td>
                </tr>';
              }
              
            echo '</tbody>
          </table>
       </div>
      </center>  
      ';
      ?>
        <script>
          function delvar(modelo){
            var c = confirm("¿Desea eliminar esta variante?");
            if(c){
              window.location.href="?modulo=articulos&accion=borrarvariante&articulo=<?php echo $this->model->id; ?>&modelo="+modelo;
            }
          }
        </script>
      <?php


    }

    

    echo '</div>';
    echo '</div></div>';

    echo '<div class="col-md-3">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title" id="basic-layout-form">Actividades</h4>
                <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i>Atrás </a>
                <div class="heading-elements">
                  <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                  </ul>
                </div>-->
              </div>';
    echo $this->actividades($this->model->tipoprod);
  }

  function grupostamano(){
    $titulo = "".$this->model->nmb;
    echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
    echo '<a href="?modulo=articulos&accion=index">
            <button class="mb-2 mr-2 btn btn-warning">
              <i class="fa fa-arrow-left"></i> Listado de articulos
            </button>
          </a>';
    echo '<a href="?modulo=articulos&accion=variantes&articulo='.$this->model->id.'">
            <button class="mb-2 mr-2 btn btn-info">
              <i class="fa fa-arrow-left"></i> Regresar a Variantes
            </button>
          </a>';
      echo '</div></div>';

      echo '<div class="row">
              <div class="col-md-9">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title" id="basic-layout-form">'.$titulo.'</h5>
                    <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>-->
                  </div>';
      echo '<div class="table-responsive">
            <form method="post" action="?modulo=articulos&accion=newgrupotam&articulo='.$this->model->id.'">
            <table class="table table-striped table-bordered table-hover">
            <thead class="thead-inverse"><tr>
              <th width="25%">Nombre</th>
              <th width="65%">Entradas</th>
              <th width="10%"></th>
            </tr></thead>';
      while($row = $this->model->result->fetch_array()){
        $sqle = 'SELECT * FROM articulos_tamanos WHERE at_grupot = "'.$row['ag_id'].'" ORDER BY at_id ASC';
        $resulte = setq($sqle);
        $valores = "";
        while($rowe = $resule->fetch_array()){
          $valores.=$row['at_alias'].',';
        }
        if(strlen($valores) > 0) $valores = substr($valores,0,-1);

        echo '<tr>
                <td>'.$row['ag_nmb'].'</td>
                <td>'.$valores.'</td>
                <td>
                  <a href="?modulo=articulos&accion=grupostamano&detalle">
                    <button class="btn btn-primary"><i class="icon-edit2"></i></button>
                  </a>
                </td>
              </tr>';
      }
      echo '</table></form></div>';

      echo '</div></div>';

    echo '<div class="col-md-3">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title" id="basic-layout-form">Actividades</h4>
                <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i>Regresar </a>
                <div class="heading-elements">
                  <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                  </ul>
                </div>-->
              </div>';
    echo $this->actividades($this->model->tipoprod);
  }

  function composiciones(){

    ?>  
    <style>
      #myTableG {
        width: 100%;
        border-collapse: collapse;
      }

      #myTableG th, #myTableG td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
        /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
      }

      #myTableG tbody tr:nth-child(odd) td {
        /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
      }

      #myTableG tbody tr:nth-child(even) td {
        /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
      }
      
      #myTableG {
        font-size: 12px; /* Cambia el tamaño de letra deseado */
      }
    </style>
    <?php 
    $titulo = "".$this->model->nmb;
    $atras = '<a href="?modulo=articulos&accion=index">
            <button class="mb-2 mr-2 btn btn-sm btn-warning">
              <i class="fa fa-arrow-left"></i> Atrás
            </button>
          </a>';
      
    toolbar($_GET['modulo'],$atras, '', '');

    echo '
    <div class="alert alert-warning col-md-12" role="alert">
    <center>
      Las medidas de volumen y área están representadas en metros.
    </center>
    </div>';
    
    echo '<div class="row mt-5">
          <div class="col-md-9">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title" id="basic-layout-form">Composición de '.$titulo.'</h5>
                <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>-->
              </div>';

    $sql = 'SELECT COUNT(*) FROM articulos_variantes WHERE av_articulo = "'.$this->model->id.'" ';
    $result = setq($sql);
    list($numvariantes) = $result->fetch_array();

    $rtip = array('A' => 'Área', 'V' => 'Volumen', 'P' => 'Pieza');

    echo '<form action="?modulo=articulos&accion=insertartcomposicion&id=' . $this->model->id . '" method="post" onsubmit="checksubmit();">';
        echo '<div class="row">';
        echo '<div class="col-12 col-md-7 mt-2 ms-5" >
          <div class="form-group">
            <label for="agregar" class">Materia prima</label><br>
            <div class="input-group">
              <select id="mp" name="mp" class="form-control" onchange="getmp();" required> ';
                $sqlmp = 'SELECT * FROM pr_materiaprima WHERE pmp_estatus = "A"';
                $resultmp = setq($sqlmp);
                while($rowmp = $resultmp->fetch_array()){
                  echo '<option value="'.$rowmp['pmp_id'].'">'.$rowmp['pmp_nmb'].' - Material: '. $rowmp['pmp_material'].' - Unidad: '.$rtip[$rowmp['pmp_tipo']].'</option>';
                }
              echo '</select>
            </div>
          </div>
        </div>';
        echo '<div class="mb-5 col-12 col-md-2 mt-2">  
                <label>Color:</label>
                <input type="color" id="color" value="#000000" class="form-control" data-toggle="tooltip" data-placement="top" name="color" style="height: 90%;" disabled>
              </div>';
        echo '<div class="col-12 col-md-2 mt-2 row">
        <div class="form-group">
          <label><b>Cantidad</b></label>
          <input type="number" min="1" step="1" value="1" name="cantidad" class="form-control" placeholder="Cantidad de artículos ligados" required></input >
        </div>
      </div>
      <div class="mb-5 col-12 col-md-2 mt-2 ms-5" id="largodiv" hidden>  
          <label>Largo:</label>
          <input type="number" min="0.01" step="0.0001" id="largo" class="form-control" data-toggle="tooltip" data-placement="top" name="largo" value="0">
        </div>
        <div class="mb-5 col-12 col-md-2 mt-2" id="anchodiv" hidden>  
          <label>Ancho:</label>
          <input type="number" min="0.01" step="0.0001" id="ancho" class="form-control" data-toggle="tooltip" data-placement="top" name="ancho" value="0">
        </div>
        <div class="mb-5 col-12 col-md-2 mt-2" id="altodiv" hidden>  
          <label>Alto:</label>
          <input type="number" min="0.01" step="0.0001" id="alto" class="form-control" data-toggle="tooltip" data-placement="top" name="alto" value="0">
        </div>
      </div>
      ';
        

      if($numvariantes > 0){
        echo '<div class="row mt-4 ms-10">
          <div class="col-3 d-flex mt-4 "><input type="checkbox" class="form-check-input" id="todos" onclick="seltodo()"/>
        <label class="form-check-label variante"><b>Seleccionar todos</b></label> </div>
        </div>';
        echo '<div class="row mt-4 ms-10">';
        $sqlvar= 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$this->model->id.'"';
        $resultvar = setq($sqlvar);
        while($rowvar = $resultvar -> fetch_array()){
          echo '<div class="col-3 d-flex mt-4"><input type="checkbox" class="form-check-input varcheck" id="var'.$rowvar['av_modelo'].'" name="var'.$rowvar['av_modelo'].'"/>
          <label class="form-check-label">'.$rowvar['av_nmb'].'</label> </div>';
        }
        echo '</div>';
      }
      
      echo '
      <center>
        <div class="col-6 col-md-2 mt-2">
          <div class="form-group" >
            <label for="save"></label><br>
            <button class="btn btn-primary" type="submit" id="agregar"><i class="fas fa-plus"></i>Agregar</button>
          </div>
        </div>
      <center>
      </div>';

    echo '</form>';
      
      echo '<div class="row mt-4">';
      $sqlc = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$this->model->id.'"';
      $resultc = setq($sqlc);
      echo '
      <center>
        <div class="col-12 col-md-12 mb-2 card card-body">
          <table width="100%" class="table table-stripped table-bordered table-hover" id="myTableG"> 
            <thead class="bg-primary text-white">
              <tr>
                <th width="25%" class="p-2">Artículo</th>
                <th width="40%" class="p-2">Composición</th>
                <th width="25%" class="p-2">Cantidad</th>
                <th width="10%" class="p-2">Acciones</th>
              </tr>
            </thead>
            <tbody>';

            if($resultc -> num_rows == 0){
              $numdet = busca($this->model->id, 'articulos_composicion', 'ac_modelo ="" AND ac_articulo', 'COUNT(*)');
              if($numdet >0 ){
                echo '<tr>
                <td rowspan="'.($numdet+1).'">'.$this->model->nmb.'</td>
                </tr>
                ';
              }
              $compo = "";
              $cant = "";
              $acciones = "";
              $sqlcom = 'SELECT * FROM articulos_composicion WHERE ac_articulo = "'.$this->model->id.'" AND ac_modelo = ""';
              $resulcom = setq($sqlcom);
              while($rowcom = $resulcom -> fetch_array()){
                $tipo = busca($rowcom['ac_mp'], 'pr_materiaprima', 'pmp_id','pmp_tipo');
                $medidas = 'Medidas: ';
                if($tipo == "A") $medidas .= decimal_format($rowcom['ac_largo']).' X '.decimal_format($rowcom['ac_ancho']);
                else if($tipo == "V") $medidas .= decimal_format($rowcom['ac_ancho']).' X '.decimal_format($rowcom['ac_largo']).' X '.decimal_format($rowcom['ac_alto']);
                else $medidas = '';
                
                $mp = busca($rowcom['ac_mp'], 'pr_materiaprima', 'pmp_id', 'CONCAT(pmp_nmb," Material: ",pmp_material)');
                $compo = $mp.' '.$medidas.'<br>';
                $cant = '<input onchange="updatecant('.$rowcom['ac_id'].');" type="number" min="1" step="1" value="'.$rowcom['ac_cantidad'].'" id="cantidad'.$rowcom['ac_id'].'" name="cantidad'.$rowcom['ac_id'].'" class="form-control"></input >'."<br>"; 
                $acciones = '<button class="btn btn-sm btn-danger" onclick="delcomp('.$rowcom['ac_id'].');"><i class="fas fa-times-circle" style="color:#fff"></i>Borrar</button>';

                echo '<tr>';
                echo '<td>'.$compo.'</td>';
                echo '<td>'.$cant.'</td>';
                echo '<td>'.$acciones.'</td>';
                echo '</tr>';
              }
            } else {
              while($row = $resultc->fetch_array()){
                $numdet = busca($this->model->id, 'articulos_composicion', 'ac_modelo ="'.$row['av_modelo'].'" AND ac_articulo', 'COUNT(*)');
                echo '<tr>
                <td rowspan="'.($numdet+1).'">'.$row['av_nmb'].'</td>
                </tr>';
                $compo = "";
                $cant = "";
                $acciones = "";
                $sqlcom = 'SELECT * FROM articulos_composicion WHERE ac_articulo = "'.$this->model->id.'" AND ac_modelo = "'.$row['av_modelo'].'"';
                $resulcom = setq($sqlcom);
                if($resulcom->num_rows > 0){
                  while($rowcom = $resulcom -> fetch_array()){
                    $tipo = busca($rowcom['ac_mp'], 'pr_materiaprima', 'pmp_id','pmp_tipo');
                    $medidas = 'Medidas: ';
                    if($tipo == "A") $medidas .= decimal_format($rowcom['ac_largo']).' X '.decimal_format($rowcom['ac_ancho']);
                    else if($tipo == "V") $medidas .= decimal_format($rowcom['ac_ancho']).' X '.decimal_format($rowcom['ac_largo']).' X '.decimal_format($rowcom['ac_alto']);
                    else $medidas = '';
                    $mp = busca($rowcom['ac_mp'], 'pr_materiaprima', 'pmp_id', 'CONCAT(pmp_nmb," Material: ",pmp_material)');
                    $compo = $mp.' '.$medidas.'<br>';
                    $cant = '<input type="number" onchange="updatecant('.$rowcom['ac_id'].');" min="1" step="0.0001" value="'.$rowcom['ac_cantidad'].'" id="cantidad'.$rowcom['ac_id'].'" name="cantidad'.$rowcom['ac_id'].'" class="form-control" placeholder="Cantidad de artículos ligados"></input >'."<br>"; 
                    $acciones = '<button class="btn btn-sm btn-danger" onclick="delcomp('.$rowcom['ac_id'].');"><i class="fas fa-times-circle" style="color:#fff"></i>Borrar</button>';

                    echo '<tr>';
                    echo '<td>'.$compo.'</td>';
                    echo '<td>'.$cant.'</td>';
                    echo '<td>'.$acciones.'</td>';
                    echo '</tr>';
                  }
                }
                echo '<tr> <td colspan="4" style="background:#C4FFA6;"></td></tr>';                
              }
            }
            echo '</tbody>
          </table>
       </div>
      </center>  
      ';
      ?>
        <script>
          function delcomp(idcomp){
            var c = confirm("¿Desea eliminar esta composición?");
            if(c){
              window.location.href="?modulo=articulos&accion=deleteartcomposicion&id=<?php echo $this->model->id; ?>&acid="+idcomp;
            }
          }

          function updatecant(id){
            var cant = document.getElementById('cantidad'+id).value;
            window.location.href="?modulo=articulos&accion=updateartcomposicion&id=<?php echo $this->model->id; ?>&acid="+id+"&cantidad="+cant;
          }
          function getmp(){
            var mp = document.getElementById('mp');
            var largo = document.getElementById('largodiv');
            var ancho = document.getElementById('anchodiv');
            var alto = document.getElementById('altodiv');
            var largoin = document.getElementById('largo');
            var anchoin = document.getElementById('ancho');
            var altoin = document.getElementById('alto');
            var color = document.getElementById('color');
            $.ajax({
              type: "POST",
              url: "query/checkmp.php",
              data: {'mp': mp.value},
              success: function(dataRTN) {
                data = JSON.parse(dataRTN);
                color.value= data['color'];
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

          } 
          getmp();
        </script>

      <?php

    

    echo '</div></div>';

    echo '<div class="col-md-3">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title" id="basic-layout-form">Actividades</h4>
                <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i>Atrás </a>
                <div class="heading-elements">
                  <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                  </ul>
                </div>-->
              </div>';

    ?>
    <script>
      function seltodo(){
        var elementos = document.getElementsByClassName("varcheck");
        var todos = document.getElementById("todos");
        
        elementos.forEach(function(elemento) {
          if(todos.checked){
            elemento.checked = true;
          } else {
            elemento.checked = false;
          }
        });
      }
    </script>
  <?php
    echo $this->actividades($this->model->tipoprod);
  }

  function produccion(){

    ?>  
    <style>
      #myTableG {
        width: 100%;
        border-collapse: collapse;
      }

      #myTableG th, #myTableG td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
        /* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
      }

      #myTableG tbody tr:nth-child(odd) td {
        /* background-color: #ffffff; */ /* Fondo blanco para las filas impares */
      }

      #myTableG tbody tr:nth-child(even) td {
        /* background-color: #f9f9f9; */ /* Fondo gris claro para las filas pares */
      }
      
      #myTableG {
        font-size: 12px; /* Cambia el tamaño de letra deseado */
      }
    </style>
    <?php 
    $botones = '';
    $titulo = "".$this->model->nmb;
    $atras = '<a href="?modulo=articulos&accion=index">
            <button class="mb-2 mr-2 btn btn-sm btn-warning">
              <i class="fa fa-arrow-left"></i> Atrás
            </button>
          </a>';
    $botones .= '<a  data-fancybox data-type="ajax" data-src="popup/listaprocesos.php?articulo='.$this->model->id.'&rand='.rand(1,999).'" href="javascript:;" >
      <button class="mb-2 btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Agregar 
      </button>
    </a>';
    $sql = 'SELECT COUNT(*) FROM articulos_produccion WHERE apr_articulo != "'.$this->model->id.'"'; 
    $result = setq($sql);
    list($count) = $result -> fetch_array();
    if($count > 0){
      $botones.= '
        <a id="copiar" style="background: teal;" class="mb-2 btn btn-sm text-white" data-fancybox data-type="ajax" data-src="popup/copiarprocesos.php?articulo='.$this->model->id.'" href="javascript:;">
          <i class="far fa-copy" style="color: #fff"></i> Copiar procesos de...
        </a>
      ';
    }
    
      
    toolbar($_GET['modulo'],$atras, '','', $botones);


    echo '<div class="row mt-5">
              <div class="col-md-9">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title" id="basic-layout-form">Proceso de producción de '.$titulo.'</h5>
                    <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>-->
                  </div>';

   
       echo ' <script class="">

    function traerfases(fase){
      var planta = document.getElementById("planta").value;
      $.ajax({
        url: "query/traerfases.php",
        method: "POST",
        data: {planta: planta,
              fase: fase},
      }).success(function (data){
        document.getElementById("fase").innerHTML = data;
      });
    }

    function traerproceso(){
      var planta = document.getElementById("planta").value;
      var fase = document.getElementById("fase").value;
      $.ajax({
        url: "query/traerprocesos.php",
        method: "POST",
        data: {planta: planta,
              fase: fase},
      }).success(function (data){
        document.getElementById("proceso").innerHTML = data;
      });
    }

    </script>'; 
      
      echo '<div class="row mt-4">';
      echo '
      <center>
        <div class="col-12 col-md-12 mb-2 card card-body">
          <div class="table-responsive">
            <table width="100%" class="table table-stripped table-bordered table-hover" id="myTableG"> 
              <thead class="bg-primary text-white">
                <tr>
                  <th width="25%" class="p-2">Planta</th>
                  <th width="25%" class="p-2">Fase</th>
                  <th width="25%" class="p-2">Proceso</th>
                  <th width="10%" class="p-2">Orden</th>
                  <th width="15%" class="p-2">Borrar</th>
                </tr>
              </thead>
              <tbody>';
              $sqlpro = 'SELECT * FROM articulos_produccion WHERE apr_articulo = "'.$this->model->id.'" ORDER BY apr_orden ASC';
              $resultpro = setq($sqlpro);
              while($rowpro = $resultpro->fetch_array()){
                $planta = busca($rowpro['apr_planta'], 'pr_plantas', 'pp_id', 'pp_nmb');
                $fase = busca($rowpro['apr_fase'], 'pr_fases', 'pf_id', 'pf_nmb');
                $proceso = busca($rowpro['apr_proceso'], 'pr_procesos', 'ppr_id', 'ppr_nmb');
                $max = getmax('apr_orden', 'articulos_produccion', 'apr_articulo = "'.$this->model->id.'"', false);
                echo '<tr><td>'.$planta.'</td>
                <td>'.$fase.'</td>
                <td>'.$proceso.'</td>
                <td><input type="number" id="orden'.$rowpro['apr_id'].'" min="0" max="'.$max.'" class="form-control" value="'.$rowpro['apr_orden'].'" onchange="updateorden('.$rowpro['apr_id'].')" ></td>
                <td><button class="btn btn-danger" onclick="delpro('.$rowpro['apr_id'].');"><i class="fas fa-times-circle" style="color:#fff"></i>Borrar</button></td>
                </tr>
                ';
              }
              echo '</tbody>
            </table>
          </div>
        </div>
      </center>  
      ';
      ?>
        <script>
          function delpro(idpro){
            var c = confirm("¿Desea eliminar esta composición?");
            if(c){
              window.location.href="?modulo=articulos&accion=deleteproceso&id=<?php echo $this->model->id; ?>&aprid="+idpro;
            }
          }

          function updateorden(id){
            var campoNumero =document.getElementById('orden'+id);
            var valor = parseInt(campoNumero.value);

            if (valor < parseInt(campoNumero.min)) {
              alert("El valor debe se mayo o igual a "+campoNumero.min);
            } else if (valor > parseInt(campoNumero.max)) {
              alert("El valor debe ser mayor o igual a "+campoNumero.max);
            } else {  
              window.location.href="?modulo=articulos&accion=updateorden&id=<?php echo $this->model->id; ?>&aprid="+id+"&orden="+valor;
            }
            
          }
        </script>

      <?php

    

    echo '</div></div></div>';

    echo '<div class="col-md-3">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title" id="basic-layout-form">Actividades</h4>
                <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i>Atrás </a>
                <div class="heading-elements">
                  <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                  </ul>
                </div>-->
              </div>';

    ?>
    <script>
      function seltodo(){
        var elementos = document.getElementsByClassName("varcheck");
        var todos = document.getElementById("todos");
        
        elementos.forEach(function(elemento) {
          if(todos.checked){
            elemento.checked = true;
          } else {
            elemento.checked = false;
          }
        });
      }
    </script>
  <?php
    echo $this->actividades($this->model->tipoprod);
  }


  function actividades($tipocmb){
  ?>
<script>

  $(document).ready(function() {
      $('#buscarprod').on('keyup', function() {
        var key = $(this).val();
        //var empresa = $('#empresa').val();
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
            $('#buscarprod').val($('#'+id).attr('data'));
            //Hacemos desaparecer el resto de sugerencias
            $('#suggestions').fadeOut(1000);
            //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
            return false;
          });
        }
      });
    });
  });

  $(document).ready(function() {
      $('#buscarprod').on('keyup', function(e) {
        if(e.keyCode == '13'){
          var input = document.getElementById("buscarprod");
          if(input.value.trim() != ""){
            window.location.href = "?modulo=articulos&accion=edit&nmba="+input.value;
          }else{
            alert("El campo no puede estar vacío");
          }
        }

      });
  });

  function buscarprodbtn(){
    var input = document.getElementById("buscarprod");
    if(input.value.trim() != ""){
      window.location.href = "?modulo=articulos&accion=edit&nmba="+input.value;
    }else{
      alert("El campo no puede estar vacío");
    }
  } 
</script>


  <?php


    if($_GET['accion'] == "edit") $alerta = 'style="background-color: #77c8e5;"'; else $alerta = "";
    if($_GET['accion'] == "fichatecnica") $alertft = 'class="alert alert-info"'; else $alertft = "";
    if($_GET['accion'] == "images") $alertimg = 'style="background-color: #CAE8F3;"'; else $alertimg = "";
    if($_GET['accion'] == "combos") $alertcmb = 'style="background-color: #77c8e5;"'; else $alertcmb = "";
    if($_GET['accion'] == "precios") $alertprc = 'class="alert alert-info"'; else $alertprc = "";
    if($_GET['accion'] == "variantes") $alertvar = 'class="alert alert-info"'; else $alertvar = "";
    if($_GET['accion'] == "relacionados") $alertrel = 'class="alert alert-info"'; else $alertrel = "";
    if($_GET['accion'] == "articulosweb") $alertrelw = 'class="alert alert-info"'; else $alertrelw = "";
    if ($_GET['accion'] == "articulosligados")$alertartlig = 'class="alert alert-info"'; else $alertartlig = "";
    if ($_GET['accion'] == "composicion")$alertartcomp = 'class="alert alert-info"'; else $alertartcomp = "";

/*
                      <tr>
                        <th '.$alertft.'><a href="?modulo=articulos&accion=fichatecnica&articulo='.$this->model->id.'">
                          <i class="icon-ios-world warning"></i> Ficha técnica </a>
                        </th>
                      </tr>
                      <tr>
                        <th '.$alertvar.'><a href="?modulo=articulos&accion=variantes&articulo='.$this->model->id.'">
                          <i class="icon-tags warning"></i> Colores y Tamanos</a>
                        </th>
                      </tr>
                      <tr>
                        <th '.$alertrel.'><a href="?modulo=articulos&accion=relacionados&articulo='.$this->model->id.'">
                          <i class="icon-share-alt warning"></i> Productos relacionados</a>
                        </th>
                      </tr>
                      <tr>
                        <th><a href="?modulo=articulos&accion=history&articulo='.$this->model->id.'">
                          <i class="icon-server warning"></i> Historial </a>
                        </th>
                      </tr>
*/
//$imgtot = busca($this->model->id,'imagenes','i_idp','COUNT(*)');
$imgtot = busca($this->model->cb,'imagenes','i_idproducto','COUNT(*)');
$preciostot = busca($this->model->id,'articulos_precios','ap_activo = "1" AND ap_articulo','COUNT(*)');
$esquematot = busca($_SESSION['emp'],'esquema_precio','ep_empresa','COUNT(*)');
$artligtot = busca($this->model->id, 'articulos_ligados', 'al_articulo', 'COUNT(*)');
//echo "$imgtot $preciostot $esquematot";
if($imgtot > 0) $iconimg = '<i class="fas fa-check" style="color: #63e6bb;"></i>'; else $iconimg = '<i class="fas fa-times"  style="color: #FF2A00"></i>';
if($preciostot == $esquematot) $iconprecio = '<i class="icon-check green"></i>'; else $iconprecio = '<i class="fas fa-times red"></i>';
if ($artligtot > 0) $iconlig = '<i class="fas fa-check" style="color: #63e6bb;"></i>'; else $iconlig = '<i class="fas fa-times" style="color: #FF2A00"></i>';

$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');

    echo '<div class="card-body">
            <div class="form-body">
              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <table class="table table-striped table-bordered">
                    <thead class="bg-bs-primary bg-darken-2">
                      <tr><th>Elije una actividad</th></tr>
                    <thead>
                    <tbody>
                      <tr>
                        <th '.$alerta.'><a href="?modulo=articulos&accion=edit&articulo='.$this->model->id.'">
                          <i class="fas fa-home warning" style="color: #f0ad4e;"></i> Datos básicos </a>
                        </th>
                      </tr>
                      <tr>
                        <th '.$alertimg.'><a href="?modulo=articulos&accion=images&articulo='.$this->model->id.'">
                        <i class="fas fa-image" style="color: #f0ad4e;"></i> Imágenes </a> '.$iconimg.'
                        </th>
                      </tr>
                      ';
                      if($tipocmb == "M"){
                        echo'
                        <tr>
                          <th '.$alertcmb.'><a href="?modulo=articulos&accion=combos&articulo='.$this->model->id.'">
                          <i class="fas fa-box-open" style="color: #f0ad4e;"></i> Paquete </a>
                          </th>
                        </tr>
                        ';
                      } else {
                        echo '
                        <tr>
                          <th ' . $alertartlig . '><a href="?modulo=articulos&accion=articulosligados&articulo=' . $this->model->id . '">
                            <i class="fas fa-link" style="color: #f0ad4e;"></i> Complementos ('.$artligtot.')</a> 
                          </th>
                        </tr>
                        <tr>
                          <th '.$alertvar.'><a href="?modulo=articulos&accion=variantes&articulo='.$this->model->id.'">
                            <i class="fas fa-palette" style="color: #f0ad4e;"></i> Variantes</a>
                          </th>
                        </tr>';
                        if($grupo == "ADMIN" || $grupo == "PRODUCCION" || $grupo == "PRODART"){
                         $artcomp = busca($this->model->id, 'articulos_composicion', 'ac_articulo', 'COUNT(*)');
                          echo '
                          <tr>
                            <th><a href="?modulo=articulos&accion=composiciones&articulo='.$this->model->id.'">
                              <i class="fas fa-puzzle-piece" style="color: #f0ad4e;"></i> Composición ('.$artcomp.')</a>
                            </th>
                          </tr>';   
                          $artprod = busca($this->model->id, 'articulos_produccion', 'apr_articulo', 'COUNT(*)');
                          echo '
                          <tr>
                            <th><a href="?modulo=articulos&accion=produccion&articulo='.$this->model->id.'">
                              <i class="fas fa-project-diagram" style="color: #f0ad4e;"></i> Producción ('.$artprod.')</a>
                            </th>
                          </tr>';   
                          echo '
                          <tr class="">
                            <th class="">
                              <a href="?modulo=articulos&accion=piezas&articulo='.$this->model->id.'" class="">
                                <i class="fa fa-castle" style="color: #f0ad4e;"></i> Piezas
                              </a>
                            </th>
                          </tr>                          
                          ';
                        }
                      }
                        /* echo'
                      <tr>
                        <th '.$alertprc.'><a href="?modulo=articulos&accion=precios&articulo='.$this->model->id.'">
                          <i class="icon-dollar warning"></i> Proveedores - Costos</a> '.$iconprecio.'
                        </th>
                      </tr>'; */
                      /* if($_SESSION['emp'] == "1")
                      echo '
                      <tr>
                        <th '.$alertrelw.'>
                        <i class="icon-dribbble3 warning"></i> 
                        <a href="?modulo=articulos&accion=articulosweb&articulo='.$this->model->id.'">Articulos Web</a>
                        </th>
                      </tr>';
                      else echo ''; */
                      echo '
                      <tr>
                        <th>
                        <div class="fotm-group">
                          <div class="input-group">
                            <input type="text" name="buscarprod" id="buscarprod" placeholder="Buscar un Producto" class="form-control" required>
                            <a class="input-group-addon">
                              <button type="button" onclick="buscarprodbtn();" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                            </a>
                          </div>
                          <div id="suggestions" style="overflow: scroll; max-height: 400px; font-weight: 100;" class="dropdown ocultaoscroll" ></div>
                        </div>
                        </th>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>';
  echo '</div>';

  ?>


  <!-- Modal -->
  <div class="modal text-xs-left" id="animation" tabindex="-1" role="dialog" aria-labelledby="myModalLabel6" aria-hidden="true">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      <h4 class="modal-title" id="myModalLabel6"><i class="icon-dollar"></i> Lista de precios</h4>
      </div>
      <div class="modal-body">
        <div class="table table-responsive" role="alert">
          <table class="table table-bordered mb-0 table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center">Esquema</th>
                <th class="text-center">Precio</th>
                <th class="text-center">Utilidad($)</th>
                <th class="text-center">Utilidad(%)</th>
              </tr>
            </thead>
            <tbody>
            <?php
              $sql = 'SELECT ep_id,ep_nmb,ap_costo,ap_precio
                      FROM esquema_precio INNER JOIN articulos_precios ON ep_id = ap_esquema
                      WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_estatus = "A"
                      AND ap_activo = "1" AND ap_articulo = "'.$this->model->id.'"';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                $utilidad = $row['ap_precio']-$row['ap_costo'];
                $porut = (1-($row['ap_costo']/$row['ap_precio']))*100;
                ?>
                  <tr>
                    <th><?php echo $row['ep_nmb'] ?></th>
                    <td class="number-align">$<?php echo number_format($row['ap_precio'],2) ?></td>
                    <td class="number-align">$<?php echo number_format($utilidad,2) ?></td>
                    <td class="number-align"><?php echo number_format($porut,0) ?>%</td>
                  </tr>
                <?php
              }
            ?>
            </tbody>
          </table>
        </div >
      </div>
      <div class="modal-footer">
      <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
    </div>
  </div>

<!-- Basic Modals end -->
  <?php
  }

  function articulosligados(){
    ?>
    <script>
      $(document).ready(function() {
        $('#producto').on('keyup', function() {
          if (event.keyCode == 38 || event.keyCode == 40){
              //console.log('chi');
          }else{
            var key = $(this).val();
            var dataString = 'producto=' + key;
            $.ajax({
              type: "POST",
              url: "query/suggestproducts.php?from=ligados",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(500).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function() {
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#' + id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  
                  $('#cantidad').focus();
                  return false;
                });
              }
            });
          }
        });
      });

      function checksubmit() {
        document.getElementById("agregar").value = "JD";
        document.getElementById("agregar").disabled = true;
        return true;
      }
    </script>
    <?php
    $titulo = "" . $this->model->nmb;
    $atras = '<a href="?modulo=articulos&accion=index">
          <button class="mb-2 mr-2 btn btn-warning">
            <i class="fas fa-arrow-left"></i> Regresar a listado
          </button>
        </a>';
    toolbar($_GET['modulo'], $atras);
    echo '<div class="row mt-5">
      <div class="col-md-9">
        <div class="card">
          <div class="card-header p-1">
            <h5 class="card-title" id="basic-layout-form">' . $titulo . '</h5>
            <a class="heading-elements-toggle"><i class="fas fa-ellipsis-v"></i></a>
          </div>';
                  echo '<div class="row mt-4 p-2">
             <form action="?modulo=articulos&accion=insertartligado&id=' . $this->model->id . '" method="post" onsubmit="checksubmit();">
              <input type="hidden" id="costo" name="costo" />
              <input type="hidden" id="importe" name="importe" />
              <input type="hidden" id="linean" name="linean" />';
                  echo '<div class="row">
                  <div class="col-12 col-md-6 mt-2" >
                <div class="form-group">
                  <label for="agregar" class">Producto</label><br>
                  <div class="input-group">
                    <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus />
                    <a accesskey="B" href="popup/buscarproduto?from=articulos&tipo=A&id=' . $this->model->id . '" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                      <button type="button" class="btn btn-primary text-white" data-toggle="tooltip" data-placement="top" title="Usar el buscador ALT+B " data-original-title="Usar el buscador"><i class="fas fa-search" style="color: white"></i>Buscar</button>
                    </a>
                  </div>
                  <div id="suggestions"></div>
                  </div>
                </div>';
                  echo '<div class="col-12 col-md-3 mt-2 row">
                  <div class="form-group">
                    <label><b>Cantidad</b></label>
                    <input type="number" min="1" step="1" value="1" name="cantidad" class="form-control" placeholder="Cantidad de artículos ligados"></input >
                  </div>
                </div>';
                  echo '<div class="col-6 col-md-2 mt-2">
                  <div class="form-group" >
                    <label for="save"></label><br>
                    <button class="btn btn-primary" type="submit" id="agregar"><i class="fas fa-plus"></i>Agregar</button>
                  </div>
                </div>
                </div>';
                  echo '
            </form> ';
                  echo '<div class="table table-responsive col-12 col-md-12 mt-5">
              <table width="100%" class="table table-stripped table-bordered table-hover">
                <thead class="bg-primary text-white">
                  <tr>
                     <td style="width:5%;">Borrar</td> 
                    <td style="width:40%;">Producto</td>
                    <td style="width:55%;">Cantidad</td>
                  </tr>
                </thead>
                <tbody>';
                  $nRows = $this->model->result->num_rows;
                  if ($nRows > 0) {
                    $ar = array();
                    $f = 0;
                    $ar[$f]['ligado'] = $this->model->id;
                    while ($rowc = $this->model->result->fetch_array()) {
                      if($rowc['al_amodelo'] != NULL) $nmb = busca($rowc['al_aligado'], 'articulos_variantes', 'av_modelo = "'.$rowc['al_amodelo'].'" AND av_articulo', 'av_nmb');
                      else $nmb = $rowc['a_nmb'];

                      if ($rowc['a_estatus'] == "I")
                        $style = 'style="background-color:#E66363"';
                      else
                        $style = '';
                      $f++;
                      $ar[$f]['ligado'] = $rowc['al_aligado'];
                      $ar[$f]['cantidad'] = $rowc['al_cantidad'];
                      echo '<tr>
                         <td style="width:5%;" ' . $style . '>
                          <button type="button" class="btn-sm btn-danger" onclick="delprov(' . $rowc['al_id'] . ',' . $rowc['al_aligado'] . ');">
                            <i class="fas fa-trash-alt" style="color: #ffffff"></i>
                          </button>
                        </td> 
                        <td style="width:40%;" ' . $style . '>' . $nmb . '</td>
                        <td style="width:55%;" ' . $style . '><input type="number" onchange="updatecant(' . $rowc['al_id'] . ',' . $rowc['al_aligado'] . ');" min="1" step="1" value="'.$rowc['al_cantidad'].'" name="cantidad'.$rowc['al_id'].'" id="cantidad'.$rowc['al_id'].'" class="form-control" placeholder="Cantidad de artículos ligados"></input ></td>
                      </tr>';
                    }
                  }
                  echo '</tbody>
              </table>
            </div>
          </div>';
                  echo '<script>
            function delprov(alid,ligado){  
              var conf = confirm("¿Estas seguro que deseas eliminar el producto seleccionado?");
              if(conf == true){
                window.location.href="?modulo=articulos&accion=deleteartligado&articulo=' . $this->model->id . '&alid=" + alid+"&ligado="+ligado;
              }
            }
            function updatecant(alid,ligado){
              var cantidad = document.getElementById("cantidad"+alid).value;
              if(cantidad == "0"){
                alert("La cantidad no puede ser igual a CERO.");
                window.location.href="?modulo=articulos&accion=articulosligados&articulo=' . $this->model->id . '";
              } else {
                window.location.href="?modulo=articulos&accion=updateligado&articulo=' . $this->model->id . '&alid=" + alid+"&ligado="+ligado+"&cantidad="+cantidad;
              }
              
            }
          </script>';
                  '
        </div>';
                  echo '</div>
    </div>';
                  echo '<div class="col-md-3">
      <div class="card">
        <div class="card-header p-1">
          <h4 class="card-title" id="basic-layout-form">Actividades</h4>
          <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i> </a>
          <div class="heading-elements">
            <ul class="list-inline mb-0">
              <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
            </ul>
          </div>
        </div>';
      echo $this->actividades($this->model->tipoprod);
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

  function showpiezas(){
    ini_set('display_erorrs',1);
    toolbar("PIEZAS");
    // '.$this->actividades($this->model->tipoprod).'
    
    $sqlx = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$this->model->id.'" ';
    $resultx = setq($sqlx);

    echo '
    <div class="row mt-5">
      <div class="col-md-9">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Piezas de '.$this->model->nmb.' </h5>
          </div>
          <div class="card-body">

            <form action="?modulo=articulos&accion=insertpieza&articulo='.$this->model->id.'" class="row" method="POST">
              <div class="col-md-3">
                <label for="" class="">Nombre</label>
                <!-- <input name="nmb" type="text" class="form-control" required autofocus> -->
                <select name="pieza" id="" class="form-control" required autofocus>
                  <option value="1" class="">Selecciona Pieza</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="" class="">Cantidad</label>
                <input name="cantidad" type="text" class="form-control" required>
              </div>
              <div class="col-md-3">
                <label for="" class="">Color</label>
                <input type="color" name="color" id="" class="form-control form-control-lg">
              </div>
              <div class="col-md-3" hidden>
                <label for="" class="">Categoría</label>
                <select name="" id="" class="form-control" >
                  <option value="" class="">Selecciona Categoría</option>
                </select>
              </div>
              <div class="col-md-3" hidden>
                <label for="" class="">Imagen</label>
                <input type="file" class="form-control">
              </div>
              <div class="col-md-12">';
              // $resultx->num_rows = 0;
              if($resultx->num_rows > 0){
                echo '<div class="row mt-4 ms-10">
                  <div class="col-3 d-flex mt-4 "><input type="checkbox" class="form-check-input" id="todos" onclick="seltodo()"/>
                <label class="form-check-label variante"><b>Seleccionar todos</b></label> </div>
                </div>';
                echo '<div class="row mt-4 ms-10">';
                $sqlvar= 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$this->model->id.'"';
                $resultvar = setq($sqlvar);
                while($rowvar = $resultvar -> fetch_array()){
                  echo '
                  <div class="col-3 d-flex mt-4">
                    <input type="checkbox" class="form-check-input varcheck" id="var'.$rowvar['av_modelo'].'" name="variantes['.$rowvar['av_modelo'].']"/>
                  <label class="form-check-label">'.$rowvar['av_nmb'].'</label> </div>';
                }
                echo '</div>';
              }
              echo '  
              </div>
              <div class="col-md-12 mt-3 text-center">
                <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Guardar</button>
              </div>
            </form>

            <hr class="">

            <div class="table-responsive">
              <table class="table table-stripped table-bordered table-hover">
                <thead class="bg-primary text-white">
                  <tr class="">
                    <th class="">Nombre</th>
                    <th class="">Cantidad</th>
                    <th class="">Color</th>
                    <th class=""></th>
                  </tr>
                </thead>
                <tbody class="">';
                  while($row = $this->model->resultp->fetch_array()){
                    echo '
                    <tr class="">
                      <td class="">'.$row['az_pieza'].'</td>
                      <td class="">'.$row['az_cantidad'].'</td>
                      <td class=""><input type="color" name="" id="" class="form-control form-control-lg" value="'.$row['az_color'].'"></td>
                      <td class="">
                        <button class="btn btn-red btn-sm"><i class="icon-trash"></i></button>
                      </td>
                    </tr>
                    ';
                  }
                echo '
                </tbody>
              </table>
            </div>


          </div>
        </div>
      </div>
      <div class="col-md-3">
      </div>
    </div>
    ';
    echo '
    <script>
      function seltodo(){
        var elementos = document.getElementsByClassName("varcheck");
        var todos = document.getElementById("todos");
        
        elementos.forEach(function(elemento) {
          if(todos.checked){
            elemento.checked = true;
          } else {
            elemento.checked = false;
          }
        });
      }
    </script>
    
    ';

  }

}
?>

<script>
  function buscarcat(id){
    var input = document.getElementById('tienda');
    var cat = document.getElementById('categoria');
      console.log('si check '+id+' '+cat.value);
      $.ajax({
      url: "query/buscarcat.php",
      type: "POST",
      dataType: "html",
      data: {'id' : id,
            'cat' : cat.value},
        
    })
    .done(function(data){
      console.log('CB: '+data);
      if(data == 1){
        alert('Informacion Actualizada');
      }else{
        alert('Dirígete a "Ficha Web" para actualizar información');
      }
    });
  }

  
  //Tienda en Linea
</script>