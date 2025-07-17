<?php
use viewecommerceconfig as GlobalViewecommerceconfig;
ini_set('display_errors',1);
class ecommerceconfig{
  var $model;
  var $view;

  function __construct(){
    $this->model = new modelecommerceconfig();
  }
  
  function index(){
    $this->model->resultsl();
    $this->view = new viewecommerceconfig($this->model);
    $this->view->showsl();
  }

  function insertimagen(){
    $this->model->insertimagen();
    redirect('?modulo=ecommerceconfig&accion=index');
  }
  function deleteimagen(){
    $this->model->deleteimagen($_GET['id']);
    redirect('?modulo=ecommerceconfig&accion=index');
  }
  function updateorder(){
    $existemismoorden = busca($_GET['orden'],'imgslider','i_orden','i_id');
    if($existemismoorden){
      $sql = 'SELECT * FROM imgslider WHERE i_orden >= "'.$_GET['orden'].'"';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $sqlup = 'UPDATE imgslider SET
                  i_orden = "'.($row['i_orden']+1).'"
                  WHERE i_id = "'.$row['i_id'].'"';
        setq($sqlup);
      }
      $sqlup = 'UPDATE imgslider SET
                i_orden = "'.$_GET['orden'].'"
                WHERE i_id = "'.$_GET['id'].'"';
      setq($sqlup);
    }else{
      $sqlup = 'UPDATE imgslider SET
                i_orden = "'.$_GET['orden'].'"
                WHERE i_id = "'.$_GET['id'].'"';
      setq($sqlup);
    }

    redirect('?modulo=ecommerceconfig&accion=index');
  }
  function cupones(){
    $this->model->resultcup();
    $this->view = new viewecommerceconfig($this->model);
    $this->view->browsecup();
  }
  function editcupon(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;
    $this->model->selectcupon($_GET['id']);
    $this->view = new viewecommerceconfig($this->model);
    $this->view->editcupon();
  } 
  function insertcupon(){
    $this->model->setdatacupon($_POST['nmb'],$_POST['codigo'],$_POST['monto'],$_POST['porcentaje'],$_POST['limite'],$_POST['caducidad'],$_POST['estatus'],$_POST['web'],$_POST['usarcompleto'],$_POST['linean']);
    $this->model->insertcupon();
    redirect('?modulo=ecommerceconfig&accion=cupones');
  }
  function updatecupon(){
    $this->model->setdatacupon($_POST['nmb'],$_POST['codigo'],$_POST['monto'],$_POST['porcentaje'],$_POST['limite'],$_POST['caducidad'],$_POST['estatus'],$_POST['web'],$_POST['usarcompleto'],$_POST['linean']);
    $this->model->updatecupon($_GET['id']);
    redirect('?modulo=ecommerceconfig&accion=editcupon&id='.$_GET['id']);
  }
  function cuponesd(){
    $this->model->selectcupon($_GET['id']);
    if(!isset($_REQUEST['marca'])) $_REQUEST['marca']=NULL;
    if(!isset($_REQUEST['concepto'])) $_REQUEST['concepto']=NULL;
    if(!isset($_REQUEST['departamento'])) $_REQUEST['departamento']=NULL;
    $this->model->resultcd($_GET['id'],$_REQUEST['marca'],$_REQUEST['concepto'],$_REQUEST['departamento']);
    $this->view = new viewecommerceconfig($this->model);
    $this->view->cuponesd($_GET['id'],$_REQUEST['marca'],$_REQUEST['concepto'],$_REQUEST['departamento']);
  }
  function cupond(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;
    if(!isset($_REQUEST['marca'])) $_REQUEST['marca'] = NULL;
    if(!isset($_REQUEST['concepto'])) $_REQUEST['concepto'] = NULL;
    if(!isset($_REQUEST['departamento'])) $_REQUEST['departamento'] = NULL;
    $this->model->selectcupon($_GET['id']);
    $this->model->resultac($_REQUEST['marca'],$_REQUEST['concepto'],$_REQUEST['departamento'],$_GET['id']);
    $this->view = new viewecommerceconfig($this->model);
    $this->view->cupond($_REQUEST['marca'],$_REQUEST['concepto'],$_REQUEST['departamento']);
  }
  function insertcupond(){
    $this->model->selectcupon($_GET['id']);
    $this->model->resultac($_POST['marca'],$_POST['concepto'],$_POST['departamento'],$_GET['id']);
    $this->model->insertcupond($_GET['id']);
    redirect('?modulo=ecommerceconfig&accion=cuponesd&id='.$_GET['id']);
  }
  function faqs(){
    $this->model->resultfaqs();
    $this->view=new viewecommerceconfig($this->model);
    $this->view->browsefaqs();
  }
  function editfaqs(){
    if(!isset($_GET['id'])) $_GET['id']=NULL;
      $this->model->selectfaqs($_GET['id']);
      $this->view=new viewecommerceconfig($this->model);
    $this->view->editfaqs();
  }
  function updatefaqs(){
    if(busca(trim($_POST['nmb']),'faqs','f_id!="'.$_GET['id'].'" AND f_descripcion','count(*)')>0)
      die(alert_back('Ya existe un descripcion con el mismo nombre',true));
    $estatus="A";
    if(!isset($_POST['estatus']))   $estatus="I";
    $this->model->updatefaqs($_GET['id'],$estatus,$_POST['categoria'],$_POST['nmb'],$_POST['descripcion']);
    redirect("?modulo=ecommerceconfig&accion=faqs");
  }
  function insertfaqs(){
    if(busca(trim($_POST['nmb']),'faqs','f_descripcion','count(*)')>0)
    die(alert_back('Ya existe un descripcion con el mismo nombre',true));
    $estatus="A";
    if(!isset($_POST['estatus']))   $estatus="I";
    //      $this->model->setData($_POST['nmb'],$_POST['descripcion']);
    $this->model->insertfaqs($estatus,$_POST['categoria'],$_POST['nmb'],$_POST['descripcion']);
    redirect('?modulo=ecommerce&accion=editfaqs&id='.$_GET['id']);
  }
  function formasp(){
    $this->model->resultformasp();
    $this->view = new viewecommerceconfig($this->model);
    $this->view->browseformasp();
  }
  function editformasp(){
    if(!isset($_GET['id']))
      $_GET['id'] = NULL;
    $this->model->selectformasp($_GET['id']);
    $this->view = new viewecommerceconfig($this->model);
    $this->view->editformasp();
  }
  function updateformasp() {
    if(busca($_POST['nmb'],'formaspago','f_id != "'.$_POST['id'].'" AND f_nmb','COUNT(*)') > 0){
      echo alert_back("El nombre ya existe",true);
    }
    $this->model->setDataformasp($_POST['id'], $_POST['nmb'], $_POST['estatus'], $_POST['descripcion'],$_POST['montoa'],$_POST['porcentaje'],$_POST['subf']);
    $this->model->updateformasp();
    redirect('?modulo=ecommerceconfig&accion=formasp&id='.$this->model->id);
  }
  function insertformasp() {
    if(!isset($_POST['id']))
      $_POST['id'] = NULL;
    if(busca($_POST['nmb'],'formaspago','f_nmb','COUNT(*)') > 0){
      echo alert_back("El nombre ya existe",true);
    }
    $this->model->setDataformasp($_POST['id'], $_POST['nmb'], $_POST['estatus'], $_POST['descripcion'],$_POST['montoa'],$_POST['porcentaje'],$_POST['subf']);
    $this->model->insertformasp();
    header('Location:?modulo=formaspago&accion=index');
  }
  function setmsi(){
    $sqlu = 'UPDATE formaspago SET f_predeterminado = 0';
    setq($sqlu);
  
    $sql = 'UPDATE formaspago SET
            f_cuotafija = "'.$_POST['cuotafija'].'",
            f_pcom = "'.$_POST['pcom'].'",
            f_pcom6msi = "'.$_POST['pcom6msi'].'",
            f_predeterminado = "1"
            WHERE f_id = "'.$_GET['id'].'"';
    setq($sqls);
  
    redirect('?modulo=ecomerceconfig&accion=formasp');
  }
  function carouselyt(){
    $this->model->resultcarouselyt();
    $this->view = new GlobalViewecommerceconfig($this->model);
    $this->view->showcarousel(); 
  }
  function editcarousel(){
    $this->model->selectcarousel($_GET['id']);
    $this->view = new GlobalViewecommerceconfig($this->model);
    $this->view->editcarousel();
  }
  function updatecarousel(){
    if($_POST['estatus']) $estatus = "A"; 
    else $estatus = "I";

    if($_FILES['imagen']['name']){
      $id = $_GET['id'];
      //$id = getmax('p_id','publicaciones');
      //Recogemos el imagen enviado por el formulario
      $imagen = $_FILES['imagen']['name'];
      $extension = pathinfo($imagen, PATHINFO_EXTENSION);
      $nuevo_nombre = 'imagen-'.$id.'.'.$extension;
      //Si el imagen contiene algo y es diferente de vacio
      if (isset($imagen) && $imagen != "") {
          //Obtenemos algunos datos necesarios sobre el imagen
          $tipo = $_FILES['imagen']['type'];
          $tamano = $_FILES['imagen']['size'];
          $temp = $_FILES['imagen']['tmp_name'];
          
          //list($imagenw, $imagenh, $tipox, $atributos) = getimagesize($_FILES['imagen']['tmp_name']); 2000000
          //Se comprueba si el imagen a cargar es correcto observando su extensión y tamaño
        if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "webp") || strpos($tipo, "jpg") || strpos($tipo, "png") || strpos($tipo, "mp4")) && ($tamano < (9000000) ))) {
            die('<div><b>Error. La extensión o el tamaño de los imagens no es correcta.<br/>
            - Se permiten .gif, .jpg, .png. y de 200 kb como máximo.</b></div>');
        }
        else {
            //Si la imagen es correcta en tamaño y tipo
            //Se intenta subir al servidor
            if (move_uploaded_file($temp, '../jdshop.mx/images/tutorialesjdshop/'.$nuevo_nombre)) {
                //Cambiamos los permisos del imagen a 777 para poder modificarlo posteriormente
                chmod('../jdshop.mx/images/tutorialesjdshop/'.$nuevo_nombre, 0777);
                //Mostramos el mensaje de que se ha subido co éxito
                //die ('<div><b>Se ha subido correctamente la imagen.</b></div>');
                //Mostramos la imagen subida
                //die ('<p><img src="img/imagens/'.$imagen.'"></p>');
                $ruta = 'images/tutorialesjdshop/'.$nuevo_nombre.'';
                $sql = 'UPDATE videos_jdshop SET
                        vj_imagen = "'.$ruta.'"
                        WHERE vj_id = "'.$id.'"';
                setq($sql);
            }
            else {
              //Si no se ha podido subir la imagen, mostramos un mensaje de error
              die('<div><b>Ocurrió algún error al subir el fichero. No pudo guardarse.</b></div>');
            }
          }
      }
    }
    $this->model->setdatacarousel($_GET['id'],$_POST['nmb'],$_POST['url'],$estatus,NULL);
    $this->model->updatecarousel();
    redirect('?modulo=ecommerceconfig&accion=editcarousel&id='.$this->model->id.'');
  }
  function insertcarousel(){
    if($_POST['estatus']) $estatus = "A"; 
    else $estatus = "I";

    $id = getmax('vj_id','videos_jdshop');
    if($_FILES['imagen']['name']){
      //$id = $_GET['id'];
      //Recogemos el imagen enviado por el formulario
      $imagen = $_FILES['imagen']['name'];
      $extension = pathinfo($imagen, PATHINFO_EXTENSION);
      $nuevo_nombre = 'imagen-'.$id.'.'.$extension;
      //Si el imagen contiene algo y es diferente de vacio
      if (isset($imagen) && $imagen != "") {
          //Obtenemos algunos datos necesarios sobre el imagen
          $tipo = $_FILES['imagen']['type'];
          $tamano = $_FILES['imagen']['size'];
          $temp = $_FILES['imagen']['tmp_name'];
          
          //list($imagenw, $imagenh, $tipox, $atributos) = getimagesize($_FILES['imagen']['tmp_name']); 2000000
          //Se comprueba si el imagen a cargar es correcto observando su extensión y tamaño
        if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "webp") || strpos($tipo, "jpg") || strpos($tipo, "png") || strpos($tipo, "mp4")) && ($tamano < (9000000) ))) {
            die('<div><b>Error. La extensión o el tamaño de los imagens no es correcta.<br/>
            - Se permiten .gif, .jpg, .png. y de 200 kb como máximo.</b></div>');
        }
        else {
            //Si la imagen es correcta en tamaño y tipo
            //Se intenta subir al servidor
            if (move_uploaded_file($temp, '../jdshop.mx/images/tutorialesjdshop/'.$nuevo_nombre)) {
                //Cambiamos los permisos del imagen a 777 para poder modificarlo posteriormente
                chmod('../jdshop.mx/images/tutorialesjdshop/'.$nuevo_nombre, 0777);
                //Mostramos el mensaje de que se ha subido co éxito
                //die ('<div><b>Se ha subido correctamente la imagen.</b></div>');
                //Mostramos la imagen subida
                //die ('<p><img src="img/imagens/'.$imagen.'"></p>');
                $ruta = 'images/tutorialesjdshop/'.$nuevo_nombre.'';
            }
            else {
              //Si no se ha podido subir la imagen, mostramos un mensaje de error
              die('<div><b>Ocurrió algún error al subir el fichero. No pudo guardarse.</b></div>');
            }
          }
      }
    }
    $this->model->setdatacarousel(NULL,$_POST['nmb'],$_POST['url'],$estatus,$ruta);
    $this->model->insertcarousel();
    redirect('?modulo=ecommerceconfig&accion=editcarousel&id='.$id.'');
  }
  function borrarimg(){
    $id = $_GET['id'];
    $ruta = busca($id,'videos_jdshop','vj_id','vj_imagen');
    unlink('../jdshop.mx/'.$ruta.'');
    $sql = 'UPDATE videos_jdshop SET
            vj_imagen = ""
            WHERE vj_id = "'.$id.'"';
    setq($sql);
    redirect('?modulo=ecommerceconfig&accion=editcarousel&id='.$id.'');
  }
  function resenas(){
    if(!isset($_REQUEST['page'])) $_REQUEST['page'] = NULL;

    $this->model->resultresenas($_REQUEST['page']);
    $this->view = new GlobalViewecommerceconfig($this->model);
    $this->view->browseresenas($_REQUEST['page']);
  }
  function dictamenresena(){
    $sql = 'UPDATE resenas SET
            r_estatus = "'.$_GET['estatus'].'"
            WHERE r_id = "'.$_GET['id'].'"';
    setq($sql);
    redirect('?modulo=ecommerceconfig&accion=resenas');
  }


  
  

  

}

class modelecommerceconfig{

  function resultsl(){
    $sql='SELECT * FROM imgslider ORDER BY i_orden ASC';
    $this->result = setq($sql) or die($sql);
  }

  function insertimagen(){
    $carpetaDestino = '../jdshop.mx/slider/';
    //$carpetaDestino = '../www/jdshop.mx/slider/';
      # si hay algun archivo que subir
      if($_FILES["archivo"]["name"][0]){
          # recorremos todos los arhivos que se han subido
          for($i=0;$i<count($_FILES["archivo"]["name"]);$i++){
            if( $_FILES["archivo"]["size"][$i] <= 2367575 ) {
              $imagen = getimagesize($_FILES["archivo"]["tmp_name"][$i]);
              $ancho = $imagen[0];
              $alto = $imagen[1];
              if($ancho > $alto AND $ancho <= 1200){
              if($_FILES["archivo"]["type"][$i]=="image/jpeg" || $_FILES["archivo"]["type"][$i]=="image/jpg" || $_FILES["archivo"]["type"][$i]=="image/gif" || $_FILES["archivo"]["type"][$i]=="image/png"){
                  # si exsite la carpeta o se ha creado
                  if(file_exists($carpetaDestino) || @mkdir($carpetaDestino)){
                    $ext = pathinfo($_FILES ["archivo"] ["name"][$i], PATHINFO_EXTENSION);
                      $j=0;
                      $origen = $_FILES["archivo"]["tmp_name"][$i];
                      $sql= 'SELECT MAX(i_id) FROM imgslider';
                      $result = setq($sql) or die($sql);;
                    list($j) = $result->fetch_array();
                      $j = intval($j);
                    $j++;
                      $destino = $carpetaDestino.$j.'.'.$ext;
                      $msje = $j.'.'.$ext;
                      $nmb = $j;
                      # movemos el archivo
                      if(@move_uploaded_file($origen, $destino)){
                          //echo "<br>".$_FILES["archivo"]["name"][$i]." movido correctamente<br>";
                          $sql= 'SELECT MAX(i_id) FROM imgslider';
                          $result = setq($sql) or die($sql);;
                        list($idi) = $result->fetch_array();
                        $idi++;
  
                          $sql='
                          INSERT INTO imgslider SET
                          i_id = "'.$idi.'",
                          i_nmb = "'.$nmb.'",
                          i_ext = "'.$ext.'",
                          i_orden = "'.$idi.'"';
                          setq($sql) or die($sql);
                      }
                      else{
                          echo "<br>No se ha podido mover el archivo: ".$_FILES["archivo"]["name"][$i];
                      }
                  }
                  else{
                      echo "<br>No se ha podido crear la carpeta: up/".$user;
                  }
              }
              else{
                  echo "<br>".$_FILES["archivo"]["name"][$i]." - NO es imagen jpg";
              }
            }
            else{
               die(alert_back('El Ancho de la imagen debe ser menor a 1200px y el alto no puede superar el ancho',true));
            }
          }
          else {
            die(alert_back('Subir una imagen menor a 2MB',true));
            echo 'el archivo es demasiado grande';
          }
          }
      }else{
          echo "<br>No se ha subido ninguna imagen";
      }
  }

  function deleteimagen($id){
    $dirjd = '../jdshop.mx/';
    //$dirjd = '../www/jdshop.mx/';
    $sql = 'SELECT COUNT(*) FROM imgslider';
    $result = setq($sql) or die($sql);
    list($nimg) = $result->fetch_array();
    if($nimg == 1)
      die(alert_back("Error al elminiar imagen el slider debe tener al menos una imagen",true));
  
    $sql = 'SELECT i_nmb, i_ext FROM imgslider WHERE i_id = "'.$id.'"';
    $rs = setq($sql) or die($sql);
    list($nombre, $ext) = $rs->fetch_array();
  
    $sql= 'DELETE FROM imgslider WHERE i_id = "'.$id.'"';
    if(unlink($dirjd.'slider/'.$nombre.'.'.$ext)){
      setq($sql) or die('<br>');
    }
    elseif(!file_exists($dirjd.'slider/'.$nombre.'.'.$ext)){
      setq($sql) or die('<br>');
    }
    else
      die('No se puede eliminar');
  }

  function resultcup(){
    $sql='SELECT * FROM cupones ORDER BY c_id ASC';
    $this->resultc = setq($sql) or die($sql);
  }

  function selectcupon($id){
    $sql = 'SELECT * FROM cupones WHERE c_id = "'.$id.'"';
    $rs = setq($sql) or die($sql);
    $rw = $rs->fetch_array();
    $this->id = $rw['c_id'];
    $this->nmb = $rw['c_nmb'];
    $this->codigo = $rw['c_codigo'];
    $this->monto = $rw['c_monto'];
    $this->porcentaje = $rw['c_porcentaje'];
    $this->limite = $rw['c_limite'];
    $this->usados = $rw['c_usados'];
    $this->estatus = $rw['c_estatus'];
    $this->caducidad = $rw['c_caducidad'];
    $this->ualta = $rw['c_ualta'];
    $this->falta = $rw['c_falta'];
    $this->web = $rw['c_web'];
    $this->usarcompleto = $rw['c_usarcompleto'];
    $this->linean = $rw['c_linean'];
  }

  function setdatacupon($nmb,$codigo,$monto,$porcentaje,$limite,$caducidad,$estatus,$web,$usarcompleto,$linean){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","=","?","¡","*","+","~","^","[","°","|");
    $this->nmb = str_replace($simbol,"", mb_strtoupper(trim($nmb)));
    $this->codigo = trim($codigo);
    $this->monto = $monto;
    $this->limite = $limite;
    $this->caducidad = $caducidad;
    $this->ualta = $_SESSION['uid'];
    $this->falta = date('Y-m-d H:i:s');
    $this->web = $web;
    $this->porcentaje = 0;
    if($porcentaje)
      $this->porcentaje = 1;
  
    if($usarcompleto) $this->usarcompleto = 1;
    else $this->usarcompleto = 0;
  
    if($linean) $this->linean = $linean;
    else $this->linean = 0;
  
    $this->estatus = "I";
    if($estatus)
      $this->estatus = "A";
  }

  function insertcupon(){
    $sql = 'SELECT MAX(c_id) FROM cupones';
    $result = setq($sql) or die($sql);
    list($this->id) = $result->fetch_array();
    $this->id++;
    $sql = 'INSERT INTO cupones SET
    c_id = "'.$this->id.'",
    c_nmb = "'.$this->nmb.'",
    c_codigo = "'.$this->codigo.'",
    c_monto = "'.$this->monto.'",
    c_porcentaje = "'.$this->porcentaje.'",
    c_limite = "'.$this->limite.'",
    c_usados = "0",
    c_estatus = "'.$this->estatus.'",
    c_caducidad = "'.$this->caducidad.'",
    c_falta = "'.$this->falta.'",
    c_ualta = "'.$this->ualta.'",
    c_usarcompleto = "'.$this->usarcompleto.'",
    c_linean = "'.$this->linean.'",
    c_web = "'.$this->web.'"';
    setq($sql) or die($sql);
  }

  function updatecupon($id){
    $sql = 'UPDATE cupones SET
    c_nmb = "'.$this->nmb.'",
    c_codigo = "'.$this->codigo.'",
    c_monto = "'.$this->monto.'",
    c_porcentaje = "'.$this->porcentaje.'",
    c_limite = "'.$this->limite.'",
    c_estatus = "'.$this->estatus.'",
    c_caducidad = "'.$this->caducidad.'",
    c_usarcompleto = "'.$this->usarcompleto.'",
    c_linean = "'.$this->linean.'",
    c_web = "'.$this->web.'"
    WHERE c_id = "'.$id.'"';
    setq($sql) or die($sql);
  }

  function resultcd($id,$marca,$concepto,$departamento){
    $sqladd = '';
    if($marca!=NULL) $sqladd.=' AND a_marca="'.$marca.'" ';
    if($concepto!=NULL) $sqladd.=' AND a_concepto="'.$concepto.'" ';
    if($departamento!=NULL) $sqladd.=' AND a_departamento="'.$departamento.'" ';
    //$sqladd.=' AND a_lineaneg="'.$this->linean.'"';
  
    $sql='SELECT * FROM cuponespd
    INNER JOIN articulos ON c_producto = a_cb
    WHERE a_estatus = "A" AND a_empresa = "1" '.$sqladd.' AND c_cupon = "'.$id.'"';
    $this->result = setq($sql) or die($sql);
  }

  function resultac($marca,$concepto,$departamento,$id){
    $sqladd = NULL;
    if($marca != NULL)
      $sqladd .= ' AND a_marca = "'.$marca.'" ';
    if($concepto != NULL)
      $sqladd .= ' AND a_concepto = "'.$concepto.'" ';
    if($departamento!=NULL)
      $sqladd .= ' AND a_departamento = "'.$departamento.'" ';
  
    //$sqladd .= ' AND a_lineaneg = "'.$this->linean.'"';
  
    $sql='SELECT * FROM articulos
    WHERE a_estatus = "A" AND a_empresa = "1" '.$sqladd.'
    ORDER BY a_id ASC';
    $this->result = setq($sql) or die($sql);
    $this->result2 = setq($sql) or die($sql);
  }

  function insertcupond($id){
    //print_r($_POST);
  
    while($row = $this->result->fetch_array()){
      if(isset($_POST['chk'.$row['a_cb']])){
        $sqlin = 'INSERT IGNORE INTO cuponespd SET
        c_cupon = "'.$id.'",
        c_producto = "'.$row['a_cb'].'"
        ';
        setq($sqlin) or die($sqlin);
        //echo $sqlin .'<----';
      }
      else{
        if(busca($row['a_cb'],'cuponespd','c_cupon = "'.$id.'" AND c_producto','COUNT(c_producto)') > 0){
          $sqld = 'DELETE FROM cuponespd WHERE
          c_producto = "'.$row['a_cb'].'"
          AND c_cupon = "'.$id.'"';
          setq($sqld) or die($sqld);
          //echo $sqld.'xxxxx';
        }
      }
    }
    
  }

  function resultfaqs(){
    $sql='SELECT * FROM faqs';
    $this->result=setq($sql);
  }

  function selectfaqs($id){
    $sql='SELECT * FROM faqs WHERE f_id="'.$id.'"';
    $rs=setq($sql) or die($sql);
    $rw=$rs->fetch_array();
    $this->id=$rw['f_id'];
    $this->nmb=$rw['f_nmb'];
    $this->categoria=$rw['f_categoria'];
    $this->descripcion=$rw['f_descripcion'];
    $this->estatus=$rw['f_estatus'];
  }

  function updatefaqs($id,$estatus,$categoria,$nmb,$descripcion){
    $this->descripcion = str_replace('"','\"',$descripcion);

    $sql='
      UPDATE faqs SET
      f_nmb="'.$nmb.'",
      f_categoria="'.$categoria.'",
      f_descripcion="'.$this->descripcion.'",
      f_estatus="'.$estatus.'"
      WHERE f_id="'.$id.'"
    ';
     setq($sql) or die($sql);
  }

  function insertfaqs($estatus,$categoria,$nmb,$descripcion){
    $sql='SELECT MAX(f_id) FROM faqs';
    $rs=setq($sql) or die($sql);
    list($id)=$rs->fetch_array();
    $id++;

    $this->descripcion = str_replace('"','\"',$descripcion);

    $sql='
      INSERT INTO faqs SET
      f_id="'.$id.'",
      f_categoria="'.$categoria.'",
      f_nmb="'.$nmb.'",
      f_descripcion="'.$this->descripcion.'",
      f_estatus="'.$estatus.'"
    ';
    if($this->nmb!=="") setq($sql) or die($sql);
  }

  function resultformasp(){
    $sql = 'SELECT * FROM formaspago ORDER BY f_id ASC';
    $this->result = setq($sql) or die($sql);
  }

  function selectformasp($id){
    $sql = 'SELECT * FROM formaspago WHERE f_id= "'.$id.'"';
    $result = setq($sql) or die($sql);
    $row = $result->fetch_array();
    $this->id = $row['f_id'];
    $this->nmb = $row['f_nmb'];
    $this->descripcion = $row['f_descripcion'];
    $this->porcentajea = $row['f_porcentajea'];
    $this->montoa = $row['f_montoa'];
    $this->subf = $row['f_subforma'];
    $this->estatus = $row['f_estatus'];
    $this->logo = $row['f_logo'];
    $this->ext = $row['f_ext'];
    $this->cuentav = $row['f_cuentav'];
  }

  function setDataformasp($id, $nmb, $estatus,$descripcion,$montoa,$porcentaje,$subf){
    mb_internal_encoding("UTF-8");
    $descripcion = str_replace('"','\"',$descripcion);
  
    $this->id = $id;
    $this->nmb = mb_strtoupper(trim($nmb));
    $this->montoa = $montoa;
    $this->porcentajea = "0";
    if($porcentaje)
      $this->porcentajea = "1";
    $this->estatus = "I";
    if($estatus)
      $this->estatus = "A";
    $this->descripcion = $descripcion;
    $this->subf = 0;
    if($subf)
      $this->subf = 1;
  }

  function updateformasp(){
    $sql = 'UPDATE formaspago SET
    f_nmb = "'.$this->nmb.'",
    f_descripcion = "'.$this->descripcion.'",
    f_estatus = "'.$this->estatus.'",
    f_montoa = "' . $this->montoa . '",
    f_porcentajea = "' . $this->porcentajea . '",
    f_subforma = "' . $this->subf . '",
    f_updated = "0"
    WHERE f_id = "'.$this->id.'"';
    setq($sql) or die($sql.mysql_error());
  }

  function insertformasp(){
    $sql = 'SELECT MAX(f_id) FROM formaspago';
    $result = setq($sql) or die($sql);
    list($this->id) = $result->fetch_array();
    $this->id++;
  
    $sql = 'INSERT INTO formaspago SET
    f_id = "' . $this->id . '",
    f_nmb = "' . $this->nmb . '",
    f_descripcion = "' . $this->descripcion . '",
    f_montoa = "' . $this->montoa . '",
    f_porcentajea = "' . $this->porcentajea . '",
    f_subforma = "' . $this->subf . '",
    f_estatus = "'.$this->estatus.'"';
    setq($sql) or die($sql);
  }

  function resultcarouselyt(){
    $sql = 'SELECT * FROM videos_jdshop WHERE vj_estatus = "A"';
    $this->result = setq($sql);
    
  }

  function selectcarousel($id){
    $sql = 'SELECT * FROM videos_jdshop WHERE vj_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['vj_id'];
    $this->nmb = $row['vj_nmb'];
    $this->url = $row['vj_url'];
    $this->imagen = $row['vj_imagen'];
    $this->estatus = $row['vj_estatus'];
    

  }

  function setdatacarousel($id,$nmb,$url,$estatus,$ruta){
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->url = $url;
    $this->estatus = $estatus;
    $this->ruta = $ruta;

  }
  function updatecarousel(){
    $sql = 'UPDATE videos_jdshop SET
            vj_nmb = "'.$this->nmb.'",
            vj_url = "'.$this->url.'",
            vj_estatus = "'.$this->estatus.'"
            WHERE vj_id = "'.$this->id.'"';
    setq($sql);
  }
  function insertcarousel(){
    $sql = 'INSERT INTO videos_jdshop SET
            vj_nmb = "'.$this->nmb.'",
            vj_url = "'.$this->url.'",
            vj_imagen = "'.$this->ruta.'",
            vj_estatus = "'.$this->estatus.'"';
    setq($sql);
  } 
  function resultresenas($pagina){
    $bloque = 50;
    $sql = 'SELECT * FROM resenas ORDER BY r_id DESC LIMIT '.($pagina*$bloque).','.$bloque.'';
    $this->result = setq($sql);
  }


}

class viewecommerceconfig{
  var $model;
  function __construct($model){
    $this->model = $model;
    $this->model->aest = array("A" => "ACTIVO","I" => "INACTIVO");
  }


  function showsl(){


    echo '
    <div class="card p-2">
      <h3>Configuraciones de E-Commerce</h3>
      <hr>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link active" id="profile-tab" href="?modulo=ecommerceconfig&accion=index">Slider</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab" href="?modulo=ecommerceconfig&accion=cupones">Cupones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab2" href="?modulo=ecommerceconfig&accion=faqs">FAQs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=formasp">Formas de Pago</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=carouselyt">Carousel YouTube</a> 
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=resenas">Aprobaciones Reseñas</a> 
        </li>
      </ul>
      <div class="tab-content px-1 pt-1" bis_skin_checked="1">
  
    ';

    echo '

    <div class="card row p-2">
      <div class="col-md-12 mb-2">
        <form action="?modulo=ecommerceconfig&accion=insertimagen&id='.$_GET['id'].'" method="post" enctype="multipart/form-data" onsubmit="return checkSubmitguardar();">
        <h3>Slider Tienda</h3>
        <hr>
        <div class="col-md-12 mb-1" >
          <label>Seleccionar una imagen</label><br>
          <input type="file" name="archivo[]" multiple="multiple" class="form-control select2-container" style="max-width: 300px" required>  
          <button type="submit" id="guardar" class="btn btn-success" ><i class="fa fa-save"></i> Guardar</button>
        </div>
        </form>
      </div>
      <div class="col-md-12 ">
      <h3>Imagenes:</h3>
      <!-- <a data-type="ajax" data-fancybox data-src="?modulo=ecommerce&accion=vieworder" class="btn btn-info" href="javascript:;"><i class="icon-sort-numeric-asc2"></i> Ordenar imagenes</a> -->
      <hr>';

      $contador = 0; 
      echo '';
      while($rw = $this->model->result->fetch_array() ){
        $contador++;
        echo '
        <div class="col-md-6 mb-1" draggable="true" ondragstart="drag(event)" id="drag1">
          <a target="_blank" href="../jdshop.mx/slider/'.$rw['i_nmb'].'.'.$rw['i_ext'].'">
          <img class="img-fluid" src="https://jdshop.mx/slider/'.$rw['i_nmb'].'.'.$rw['i_ext'].'"></a>
          <div class="form-inline mt-1">
            <label>Orden:</label>
            <input class="form-control select2-container" id="'.$rw['i_id'].'" onchange="ordenar('.$rw['i_id'].')" value="'.$rw['i_orden'].'" style="max-width: 50px;" placeholder="Orden">
            <button type="button" class="btn btn-danger btn-sm" id="borrar" onclick="confdel('.$rw['i_id'].')"><i class="fa fa-trash"></i></button>
          </div>
        </div>
        ';
      }
      
      echo '
      <!-- 
      <script>  
        function allowDrop(ev) {ev.preventDefault();}  
        function drag(ev) {ev.dataTransfer.setData("text/html", ev.target.id);}  
        function drop(ev) {  
        ev.preventDefault();  
        var data = ev.dataTransfer.getData("text/html");  
        ev.target.appendChild(document.getElementById(data));  
        }  
      </script>
      -->
      <script>
        function ordenar(id){
          var a = document.getElementById(id);
          window.location.href="?modulo=ecommerceconfig&accion=updateorder&orden="+a.value+"&id="+id;
        }
      </script>
      </div>
      </div>
    </div>
    </div>
    ';

      ?>
      <script>
      function checkSubmitguardar() {
        document.getElementById("guardar").value = "JD";
        document.getElementById("guardar").disabled = true;
        return true;
      }
      function confdel(img){
        var conf = confirm("Desea eliminar la imagen del slider?");
        if(conf){
          document.location.href = "?modulo=ecommerceconfig&accion=deleteimagen&id="+img;
        }
      }
      </script>
    <?php
  }

  function browsecup(){

    echo '
    <div class="card p-2">
      <h3>Configuraciones de E-Commerce</h3>
      <hr>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link" id="profile-tab" href="?modulo=ecommerceconfig&accion=index">Slider</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" id="about-tab" href="?modulo=ecommerceconfig&accion=cupones">Cupones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab2" href="?modulo=ecommerceconfig&accion=faqs">FAQs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=formasp">Formas de Pago</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=carouselyt">Carousel YouTube</a> 
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=resenas">Aprobaciones Reseñas</a> 
        </li>
      </ul>
      <div class="tab-content px-1 pt-1" bis_skin_checked="1">
    ';
    $aweb = array("0"=>"Ambas","1"=>"JD Shop","2"=>"JD Suite");

    echo '
    <div class="mb-1">
      <a href="?modulo=ecommerceconfig&accion=editcupon" type="button" class="btn btn-info" ><i class="fa fa-plus"></i> Nuevo</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>Excepciones</th>
            <th>Nombre</th>
            <th>Codigo</th>
            <th>Monto</th>
            <th>Limite</th>
            <th>Usados</th>
            <th>Caducidad</th>
            <th>Acciones</th>
            <th>Estatus</th>
            <th>WEB</th>
          </tr>
        </thead>
        <tbody>';
        while($rw = $this->model->resultc->fetch_array()){
          echo '
          <tr>
            <th><a href="?modulo=ecommerceconfig&accion=cuponesd&id='.$rw['c_id'].'" class="btn btn-warning"><i class="fa fa-times"></i> Excepciones</a></th>
            <td>'.$rw['c_nmb'].'</td>
            <td>'.$rw['c_codigo'].'</td>';
            if($rw['c_porcentaje'] == 1)
              echo '<td>'.$rw['c_monto'].' %</td>';
            else
              echo '<td>$ '.$rw['c_monto'].'</td>';
            echo '
            <td>'.$rw['c_limite'].'</td>
            <td>'.$rw['c_usados'].'</td>
            <td>'.$rw['c_caducidad'].'</td>
            <td width="8%"><a href="?modulo=ecommerceconfig&accion=editcupon&id='.$rw['c_id'].'" type="button" class="btn btn-primary"><i class="icon-edit"></i> Editar</a></td>';
            if($rw['c_estatus'] == "A")
            echo '<td class="text-white" style= "background:#45C431">ACTIVADO</td>';
              //echo '<td width="9%" style= "background:#45C431"><a href="?modulo=descuentos&accion=desactivarcupon&id='.$rw['c_id'].'"><input type="button" class="botonb" id="desactivar" /></a></td>';
            else
              echo '<td class="text-white" style= "background:#45C431">DESACTIVADO</td>'; 
              //echo '<td width="9%" style= "background:#CC0000"><a href="?modulo=descuentos&accion=activarcupon&id='.$rw['c_id'].'&"><input type="button" class="botonb" id="activar" /></a></td>';
            echo '<td>'.$aweb[$rw['c_web']].'</td>
          </tr>';
        }
        echo '
        </tbody>
      </table>
    </div>';

  }

  function editcupon(){
    ?>
    <script>
    function generacupon(){
      var cupon = "";
      var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
      for( var i = 0; i < 10; i++ ){
        cupon += possible.charAt(Math.floor(Math.random() * possible.length));
      }
      if($('#codigoa').is(':checked')){
        $('#codigo').val(cupon);
      }
      else{
        $('#codigo').val('');
      }
    }
    function checksubmit() {
      document.getElementById("guardar").value = "JD";
      document.getElementById("guardar").disabled = true;
      return true;
    }
    </script>
    <?php
    $aweb = array("0"=>"Ambas","1"=>"JD Shop","2"=>"JD Suite");
    if($this->model->estatus == NULL)
      $estatus = "checked";
    elseif($this->model->estatus == "A")
      $estatus = "checked";
    else
      $estatus = "";
    $selp = "";
    if($this->model->porcentaje == "1")
      $selp = "checked";
    $seluc = "";
    if($this->model->usarcompleto == "1")
      $seluc = "checked";
  
  
    if($this->model->id == NULL){
      $titulo = "Nuevo Cupón";
      $enlace = "insertcupon";
    }
    else{
      $titulo = "Modificar Cupón";
      $enlace = "updatecupon&id=".$this->model->id;
    }
    echo '
    <div class="card p-2">
    <div class="">
      <a href="?modulo=ecommerceconfig&accion=cupones" type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras</a>
    </div>
    <h3>'.$titulo.' '.$this->model->nmb.'</h3>
    <hr>';
    if($this->model->caducidad == NULL)
      $this->model->caducidad = date('Y-m-d', strtotime(''.date('Y-m-d').' + 30 days'));
  
    echo '
    <form action="?modulo=ecommerceconfig&accion='.$enlace.'" method="post" onsubmit="return checksubmit();">
    <div class="row"> 
      <div class="col-md-3 mb-5 height-100">
        <label>Nombre del Cupon</label>
        <input type="text" name="nmb" id="nmb" value="'.$this->model->nmb.'" class="form-control" autofocus required>
      </div>
      <div class="col-md-3 mb-5 height-100">
        <label>Codigo</label>
        <input type="text" name="codigo" id="codigo" value="'.$this->model->codigo.'" class="form-control" required>
        <input  type="checkbox" id="codigoa" onclick="generacupon();"  /> Generar
      </div>
      <div class="col-md-3 mb-5 height-100">
        <label>Monto</label>
        <input type="number" name="monto" min="0" class="form-control" step="any" id="monto" value="'.$this->model->monto.'" required>
      </div>
      <div class="col-md-3 mb-5 height-100">
        <label>Porcentaje</label><br>
        <input type="checkbox" class="flipswitchliquidar" name="porcentaje" '.$selp.'>
      </div>
      <div class="col-md-3 mb-5 height-100">
        <label>Usar Completo</label><br>
        <input class="flipswitchliquidar" title="El cupón puede ser usado varias veces por el cliente, hasta agotarlo" data-toggle="tooltip" style=" font-size:20px;" type="checkbox" name="usarcompleto" '.$seluc.'></td>
      </div>
      <div class="col-md-3 mb-5 height-100">
        <label>Limite</label>
        <input class="form-control" type="number" name="limite" min="1" value="'.$this->model->limite.'" required>
      </div>
      <div class="col-md-3 mb-5 height-100">
        <label>Caducidad</label>
        <input class="form-control" type="date" name="caducidad" value="'.$this->model->caducidad.'" required>
      </div>
      <div class="col-md-3 mb-5 height-100">
        <label>Webs</label>
        <select name="web" id="web" class="form-control">';
        foreach($aweb as $clave => $item){
          $sel = '';
          if($clave == $this->model->web)
            $sel = 'selected';
          echo '<option value="'.$clave.'" '.$sel.'>'.$item.'</option>';
        }
      echo '
        </select>
      </div>';
      $sqll = 'SELECT * FROM lineas_negocio WHERE ln_estatus = "A" AND ln_empresa = "1"';
      $resultl = setq($sqll) or die($sqll);

      echo '
      <div class="col-md-3 mb-5 height-100">
        <label>Linea de negocio aplicable</label>
        <select name="linean" id="linean" class="form-control">';
        if(!$this->model->linean || empty($this->model->linean)) $sel = "selected"; else $sel = "";
        echo '<option value="" '.$sel.'>Todas</option>';
        while($rowl = $resultl->fetch_array()){
          if($rowl['ln_id'] == $this->model->linean) $sel = 'selected';
          else $sel = "";
          echo '<option value="'.$rowl['ln_id'].'" '.$sel.'>'.$rowl['ln_nmb'].'</option>';
        }

        echo '
        </select>
      </div>';
      echo '
      <div class="col-md-3 mb-5 height-100">
        <label>Estatus</label><br>
        <input type="checkbox" data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" 
        data-on="ACTIVO" data-off="INACTIVO"
        class="toggle btn btn-success" name="estatus" id="estatus" '.$estatus.'>
      </div>
      <div class="col-md-12 text-xs-center ">
        <button type="submit" id="guardar" class="btn btn-success" ><i class="fa fa-save"></i> Guardar</button>
      </div>
    </div>
    </form>
    </div>';
  }

  function cuponesd($marca,$concepto,$departamento){
    ?>
    <script>
    function checkSubmitaplicar() {
      document.getElementById("aplicar").value = "JD";
      document.getElementById("aplicar").disabled = true;
      return true;
    }
    function seleccionar_todo(){
      for (i=0;i<document.insertproductos.elements.length;i++)
        if(document.insertproductos.elements[i].type == "checkbox")
          document.insertproductos.elements[i].checked = 1;
    }
    function deseleccionar_todo(){
      for (i=0;i<document.insertproductos.elements.length;i++)
        if(document.insertproductos.elements[i].type == "checkbox")
          document.insertproductos.elements[i].checked = 0;
    }
    </script>
    <?php
      echo '
      <div class="card p-2">
        <div class="mb-1">
          <a href="?modulo=ecommerceconfig&accion=cupones" class="btn btn-warning" id="regresar"><i class="fa fa-arrow-left"></i> Atras</a>
          <a href="?modulo=ecommerceconfig&accion=cupond&id='.$this->model->id.'" class="btn btn-info" id="agregar"><i class="fa fa-plus"></i> Agregar</a>
        </div>';
        echo '
        <div class="table-responsive">
          <table class="table table-hover table-striped table-bordered">
          <thead>
            <tr>
              <th colspan="3">Productos Excluidos del cupon: '.$this->model->nmb.'</th>
            </tr>
            <tr>
              <th>CB</th>
              <th>Producto</th>
              <th>Linea</th>
            </tr>
          </thead>
          <tbody>';
          while($rw = $this->model->result->fetch_array()){
            $descuento = $this->model->descuento;
            echo '<tr>';
              //echo '<th>'.$rw['a_id'].'</th>';
              echo '<th>'.$rw['a_cb'].'</th>';
              echo '<td>'.$rw['a_nmb'].'</td>';
              echo '<td>'.busca($rw['a_linea'],'lineas_negocio','ln_id','ln_nmb').'</td>';
            echo '</tr>';
          }
          echo '
          </tbody>
          </table>
        </div>
      </div>';
  }

  function cupond($marca,$concepto,$departamento){
      ?>
      <script>
      function checksubmit() {
        document.getElementById("guardar").value = "JD";
        document.getElementById("guardar").disabled = true;
        return true;
      }
      function seleccionar_todo(){
        for (i=0;i<document.insertproductos.elements.length;i++)
          if(document.insertproductos.elements[i].type == "checkbox")
            document.insertproductos.elements[i].checked = 1;
      }
      function deseleccionar_todo(){
        for (i=0;i<document.insertproductos.elements.length;i++)
          if(document.insertproductos.elements[i].type == "checkbox")
            document.insertproductos.elements[i].checked = 0;
      }
      </script>
      <?php
      echo '
      <div class="card p-2">';
        echo '
        <form method="post">

        </form>';
        /*
        <table width="90%">
        <tr>
        <th>Marca</th>
        <td>'.menu_select_db('marcas', 'm_id', 'm_nmb', $marca,'marca' , 'm_estatus = "A"', false, false, true, false, false,false,false,false).'</td>
        <th>Concepto</th>
        <td>'.menu_select_db('conceptos', 'c_id', 'c_nmb', $concepto,'concepto' , 'XXX', false, false, true, false, false,false,false,false).'</td>
        <th>Departamento</th>
        <td>'.menu_select_db('departamentos', 'd_id', 'd_nmb', $departamento,'departamento' , 'XXX', false, false,true, false, false,false,false,false).'</td>
        </tr>
        </table>
        */
      
        echo '
        <form name="insertproductos" action="?modulo=ecommerceconfig&accion=insertcupond&id='.$this->model->id.'" method="post" onsubmit="return checksubmit();">
          <input type="hidden" name="marca" value="'.$marca.'"/>
          <input type="hidden" name="concepto" value="'.$concepto.'"/>
          <input type="hidden" name="departamento" value="'.$departamento.'"/>

          <div class="form-inline mb-1">
            <a href="?modulo=ecommerceconfig&accion=cuponesd&id='.$this->model->id.'" class="btn btn-warning" id="regresar"><i class="fa fa-arrow-left"></i> Atras</a>
            <button type="button" id="marcartodos" class="btn btn-info"  onclick="seleccionar_todo();"/><i></i> Marcar Todos</button>
            <button type="button" id="desmarcartodos" class="btn btn-danger" onclick="deseleccionar_todo()"/><i></i> Desmarcar Todos</button>
            <button type="submit" id="guardar" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
          </div>';
        
          echo '
          <table class="table table-bordered table-hover table-striped" >
            <thead>
              <tr>
                <th colspan="9">Productos a excluir en cupon: '.$this->model->nmb.'</th>
              </tr>
              <tr>
                <th></th>
                <th>Id</th>
                <th>Producto</th>
                <th>Linea</th>
              </tr>
            </thead>
            <tbody>';
          while($rw = $this->model->result->fetch_array()){
            $sel = '';
            if(busca($rw['a_cb'],'cuponespd','c_cupon = "'.$this->model->id.'" AND c_producto','COUNT(c_producto)') > 0){
              $sel = 'checked';
            }
            echo '<tr>';
              echo '<td><input type="checkbox" name="chk'.$rw['a_cb'].'" '.$sel.'/></td>';
              echo '<th>'.$rw['a_cb'].'</th>';
              echo '<td>'.$rw['a_nmb'].'</td>';
              echo '<td>'.busca($rw['a_lineaneg'],'lineas_negocio','ln_id','ln_nmb').'</td>';
            echo '</tr>';
          }
          echo '
            </tbody>
          </table>
        </form> 
      </div>';
  }

  function browsefaqs(){
    echo '
    <div class="card p-2">
      <h3>Configuraciones de E-Commerce</h3>
      <hr>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link" id="profile-tab" href="?modulo=ecommerceconfig&accion=index">Slider</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab" href="?modulo=ecommerceconfig&accion=cupones">Cupones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" id="about-tab2" href="?modulo=ecommerceconfig&accion=faqs">FAQs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=formasp">Formas de Pago</a>
        </li>
        <li class="nav-item">
          <a class="nav-link " id="about-tab3" href="?modulo=ecommerceconfig&accion=carouselyt">Carousel YouTube</a> 
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=resenas">Aprobaciones Reseñas</a> 
        </li>
      </ul>
      <div class="tab-content px-1 pt-1" bis_skin_checked="1">
    ';
  
      echo '
      <div class="mb-1">
      <a href="?modulo=ecommerceconfig&accion=editfaqs" class="btn btn-info" id="nuevo" accesskey="N"><i class="fa fa-plus"></i> Nuevo</a>
      </div>';


      echo '
        <table class="table table-hover table-striped table-bordered">
          <thead>
            <tr>
                <!-- <th>Id</th> -->
                <th>Pregunta</th>
                <th>Categoría</th>
                <th width="8%">Estatus</th>
                <th width="8%">Acciones</th>
            </tr>
          </thead>
          <tbody>';
          while($rw=$this->model->result->fetch_array()){
            echo '<tr>';
            //echo '<th><a href="?modulo=faqs&accion=editfaqs&id='.$rw['f_id'].'">'.$rw['f_id'].'</a></th>';
            echo '<td>'.$rw['f_nmb'].'</td>';
            echo '<td>'.busca($rw['f_categoria'],'faqscat','f_id','f_nmb').'</td>';
            if($rw['f_estatus'] == "A")
              //echo '<td width="9%" style= "background:#45C431"><center><b><a href="?modulo=faqs&accion=desactivar&id='.$rw['f_id'].'"><input type="button" class="botonb" id="desactivar" /></a>';
              echo '<td class="text-white" style= "background:#45C431">ACTIVA</td>';
            else
            //echo '<td width="9%" style= "background:#CC0000"><center><b><a href="?modulo=faqs&accion=activar&id='.$rw['f_id'].'"><input type="button" class="botonb" id="activar" /></a>';
            echo '<td class="text-white" style= "background:#CC0000">DESACTIVADO</td>';
            echo '<td><a href="?modulo=ecommerceconfig&accion=editfaqs&id='.$rw['f_id'].'" class="btn btn-primary" ><i class="icon-edit"></i> Editar</a></td>';
            echo '</tr>';
          }
      echo '
          </tbody>
        </table>';

  }

  function editfaqs(){
    echo '
    <div class="card p-2">
      <div>
        <a href="?modulo=ecommerceconfig&accion=faqs" class="btn btn-warning" accesskey="X"><i class="fa fa-arrow-left"></i> Atras</a>
      </div>';
      if($_GET['id']==NULL){
        $titulo="Agregar una pregunta";
        $accion="insertfaqs";
      }
      else{
        $titulo="Editar la pregunta";
        $accion='updatefaqs&id='.$this->model->id.'';
      }
      if($this->model->estatus==NULL) $estatus="checked";
      else{
        if($this->model->estatus=="A") $estatus="checked"; else $estatus="";
      }
      echo '
      <h3>'.$titulo.'</h3>
      <hr>
      <form id="faddcontacto" action="?modulo=ecommerceconfig&accion='.$accion.'" method="post" onsubmit="return checkSubmitdetalle();">
        <div class="row">
          <div class="col-md-6">
            <div class="col-md-6 mb-5">
              <label>Pregunta</label>
              <input class="form-control" type="text" placeholder="En forma de pregunta, pero sin signos de interrogación" size="65" name="nmb" value="'.$this->model->nmb.'" autofocus>
            </div>
            <div class="col-md-6 mb-5">
              <label>Catgoría</label>
              <select name="categoria" class="form-control" required>
                <option></option>';
                $sql='SELECT * FROM faqscat WHERE f_estatus="A"';
                $rs=setq($sql) or die($sql);
                while($rw=$rs->fetch_array()){
                  if($this->model->categoria==$rw['f_id']) $selec="selected"; else $selec="";
                  if($rw['f_web'] == "1") $web = "SUITE"; else $web = "SHOP";
                  echo '<option value="'.$rw['f_id'].'" '.$selec.'>'.$rw['f_nmb'].' ('.$web.')</option>';
                }
                echo '
              </select>
            </div>
            <div class="col-md-12 mb-5">
              <label>Estatus</label>
              <input data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" 
              data-on="ACTIVO" data-off="INACTIVO"
              class="toggle btn btn-success" type="checkbox" name="estatus" '.$estatus.' />
            </div>
          </div>
          <div class="col-md-6">
            <div class="col-md-12 mb-5">
              <label>Respuesta</label>
              <textarea class="form-control" name="descripcion" rows="7" cols="60">'.$this->model->descripcion.'</textarea>
            </div>
          </div>
          <div class="col-md-12 mb-5 text-xs-center">
            <button type="submit" id="guardar" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
          </div>
        </div>
      </form>
    </div>';
  }

  function browseformasp(){

    echo '
    <div class="card p-2">
      <h3>Configuraciones de E-Commerce</h3>
      <hr>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link" id="profile-tab" href="?modulo=ecommerceconfig&accion=index">Slider</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab" href="?modulo=ecommerceconfig&accion=cupones">Cupones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab2" href="?modulo=ecommerceconfig&accion=faqs">FAQs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" id="about-tab3" href="?modulo=ecommerceconfig&accion=formasp">Formas de Pago</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=carouselyt">Carousel YouTube</a> 
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=resenas">Aprobaciones Reseñas</a> 
        </li>
      </ul>
      <div class="tab-content px-1 pt-1" bis_skin_checked="1">
      ';

      echo '
      <div class="mb-1">
        <a href="?modulo=ecommerceconfig&accion=editformasp" class="btn btn-info" id="nuevo"><i class="fa fa-plus"></i> Nuevo</a>
      </div>';
      echo '
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Estatus</th>
              <th>Calula MSI</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>';
          while ($row = $this->model->result->fetch_array()) {
            echo '
            <tr>
              <!-- <th><a href="?modulo=formaspago&accion=show&id='.$row['f_id'].'">'.$row['f_id'].'</a></th> -->
              <td>'.$row['f_nmb'].'</td>';
              if($row['f_estatus'] == "A") $bg = "bg-green text-white";
              else $bg = "bg-danger text-white";
              echo '
              <td class="'.$bg.'">'.$this->model->aest[$row['f_estatus']].'</td>';
              if($row['f_predeterminado'] == "1"){
              echo '
                <td> <!-- style="background:#00CC33;" -->
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-target="#exampleModal'.$row['f_id'].'">
                    <i class="icon-calculator4"></i> Calcular MSI
                  </button>
                </td>

                <!-- Modal -->
                <div class="modal fade" id="exampleModal'.$row['f_id'].'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Añade montos para realizar el calculo de 6 MSI</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">';
                        $cuotafija = busca($row['f_id'],'formaspago','f_id','f_cuotafija');
                        $pcom = busca($row['f_id'],'formaspago','f_id','f_pcom');
                        $pcom6msi = busca($row['f_id'],'formaspago','f_id','f_pcom6msi');
                        echo '<form method="post" action="?modulo=ecommerceconfig&accion=setmsi&id='.$row['f_id'].'">';

                          echo '<div class="row">
                                <div class="col-md-4 mb-5 height-100">
                                  <label>Cuota fija de comisión</label>
                                  <input class="form-control" type="number" name="cuotafija" min="0" onfocus="this.select();" max="99999" step="0.01" value="'.$cuotafija.'" >
                                </div>';
                          echo '<div class="col-md-4 mb-5 height-100">
                                  <label>Porcentaje de comisión por uso</label>
                                  <input class="form-control" style="text-align:right;" type="number" name="pcom" min="0" onfocus="this.select();" max="99999" step="0.01" value="'.$pcom.'">
                                </div>';
                          echo '<div class="col-md-4 mb-5 height-100">
                                  <label>Porcentaje de comisión a 6MSI</label>
                                  <input class="form-control" style="text-align:right;" type="number" name="pcom6msi" min="0" onfocus="this.select();" max="99999" step="0.01" value="'.$pcom6msi.'">
                                </div>';
                          echo '<div class="col-md-12 mb-5 text-xs-center">
                                  <button type="submit" class="btn btn-success" id="guardar"><i class="fa fa-save"></i> Guardar</button
                                </div>';
                        echo '</form>';
                        echo '
                      </div>
                    </div>
                  </div>
                </div>';
              }else{
              echo '
                <td>-</td>
              ';
              }
              echo '<td><a href="?modulo=ecommerceconfig&accion=editformasp&id='.$row['f_id'].'" class="btn btn-primary"><i class="icon-edit"></i> Editar</a></td>';
            echo '
              </tr>';
          }
          echo '
          </tbody>
        </table>
      </div>
    </div>';
  }

  function editformasp(){
    ?>
    <script language="JavaScript">
    function checkSubmitguardar() {
      document.getElementById("guardar").value = "JD";
      document.getElementById("guardar").disabled = true;
      return true;
    }
    </script>
    <?php
      echo '
      <div class="card p-2">
        <div class="">
          <a href="?modulo=ecommerceconfig&accion=formasp" id="cancelar" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras</a>
        </div>
        <h3>Forma de pago</h3>
        <hr>';
        $accion = 'insertformasp';
        if (isset($this->model->id)){
          $accion = 'updateformasp';
        }
        $tooltip = 'data-toggle="tooltip" title="Si se marca esta opcion el monto adicional sera el porcentaje a añadir"';
        $tooltip2 = 'data-toggle="tooltip" title="Esta opcion permite sustituir la siguiente cadena 0000000000000000 por una de las cuentas configuradas de esta forma de pago de acuerdo a su limite de venta"';
        echo '
        <div class="row">
        
          <form method="post" action="?modulo=ecommerceconfig&accion='.$accion.'" onsubmit="return checkSubmitguardar();">';
          if ($accion == 'updateformasp') {
            echo '
              <input type="hidden" name="id" value="'.$this->model->id.'">';
          }
          echo '
          <div class="col-md-8">
            <div class="col-md-3 mb-5 height-100">
              <label>Nombre</label>
              <input class="form-control" type="text" name="nmb" maxlength="100" value="'.$this->model->nmb.'" required autofocus placeholder="Campo Obligatorio">
            </div>
            <div class="col-md-3 mb-5 height-100">
              <label>Monto Adicional</label>
              <input class="form-control" type="number" name="montoa" min="0" step="any" value="'.number_format($this->model->montoa).'" />
            </div>
            <div class="col-md-3 mb-5 height-100">
              <label>Porcentaje</label><br>';
        if($this->model->porcentajea == "0")
          echo'<input '.$tooltip.' type="checkbox" class="flipswitch" name="porcentaje">';
        else
          echo'<input '.$tooltip.' type="checkbox" class="flipswitch" name="porcentaje" checked>';
      echo '</div>
            <div class="col-md-3 mb-5 height-100">
              <label>Estatus</label><br>';
        if($this->model->estatus == "I")
          echo'<input type="checkbox" data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" 
          data-on="ACTIVO" data-off="INACTIVO"
          class="toggle btn btn-success" name="estatus">';
        else
          echo'<input type="checkbox" data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" 
          data-on="ACTIVO" data-off="INACTIVO"
          class="toggle btn btn-success" name="estatus" checked>';
      echo '</div>
            <div class="col-md-3 mb-5" height-100>
              <label>Cuentas Configurables</label><br>';
        if($this->model->subf == "0")
          echo'<input  '.$tooltip2.' type="checkbox" class="flipswitch" name="subf">';
        else
          echo'<input '.$tooltip2.' type="checkbox" class="flipswitch" name="subf" checked>';
      echo '</div>
          </div>
          <div class="col-md-4">
            <div class="col-md-12 mb-5">
              <label>Descripcion</label>
              <textarea class="form-control" name="descripcion" rows="10" cols="100">'.$this->model->descripcion.'</textarea>
            </div>
        ';
      echo '
          </div>
        <div class="col-md-12 mb-5 text-xs-center">
          <button id="guardar" type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guadar</button>
        </div>
        </form>
      </div>';
  }

  function showcarousel(){


    echo '
    <div class="card p-2">
      <h3>Configuraciones de E-Commerce</h3>
      <hr>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link" id="profile-tab" href="?modulo=ecommerceconfig&accion=index">Slider</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab" href="?modulo=ecommerceconfig&accion=cupones">Cupones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab2" href="?modulo=ecommerceconfig&accion=faqs">FAQs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=formasp">Formas de Pago</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" id="about-tab3" href="?modulo=ecommerceconfig&accion=carouselyt">Carousel YouTube</a> 
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=resenas">Aprobaciones Reseñas</a> 
        </li>
      </ul>
      <div class="tab-content px-1 pt-1" bis_skin_checked="1">';

      echo '
      <a href="?modulo=ecommerceconfig&accion=editcarousel" class="btn btn-primary mb-1"><i class="fa fa-plus"></i> Nuevo</a>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered">
          <thead class="">
            <tr>
              <th>Nombre</th>
              <th>Imagen</th>
              <th>Estatus</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>';
          while($row = $this->model->result->fetch_array()){
            echo '
            <tr>
              <td>'.$row['vj_nmb'].'</td>
              <td><img src="../jdshop.mx/'.$row['vj_imagen'].'" style="max-width: 100px; max-height: 100px;"></td>';
              if($row['vj_estatus'] == "A") $estatus = "ACTIVO";
              else $estatus = "INACTIVO";
              echo '
              <td>'.$estatus.'</td>
              <td><a class="btn btn-info" href="?modulo=ecommerceconfig&accion=editcarousel&id='.$row['vj_id'].'"><i class="icon-edit"></i> Editar</a></td>
            </tr>
            ';
          }
          echo '
          </tbody>
        </table>
      </div>';


    echo '
    </div>
    ';

  }

  function editcarousel(){
   if($this->model->id) $accion = 'updatecarousel&id='.$this->model->id.'';
   else $accion = 'insertcarousel';

   if($this->model->id) $titulo = 'Editar Video';
   else $titulo = 'Insertar video';

   if($this->model->estatus == "A") $ch = 'checked';
   elseif ($this->model->estatus == "A") $ch = '';
   else $ch = '';
   echo '
   <div class="mb-1">
    <a href="?modulo=ecommerceconfig&accion=carouselyt" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras</a>
   </div>
   <div class="card p-2">
    <h3>'.$titulo.' '.$this->model->nmb.'</h3>
    <hr>
    <form method="POST" action="?modulo=ecommerceconfig&accion='.$accion.'" enctype="multipart/form-data">
    <div class="row">
    <div class="col-md-6">
      <div class="col-md-12 mb-5">
        <label for="" class="">Nombre</label>
        <input name="nmb" id="nmb" type="text" class="form-control" value="'.$this->model->nmb.'">
      </div>
      <div class="col-md-12 mb-5">
        <label for="" class="">URL</label>
        <input name="url" id="url" type="text" class="form-control" value="'.$this->model->url.'">
      </div>
      <div class="col-md-12 mb-5">
        <label for="" class="">Estatus</label><br>
        <input type="checkbox" class="flipswitch" name="estatus" id="estatus" '.$ch.'>
      </div>
    </div>
    <div class="col-md-3">
      <div class="col-md-12 mb-5">
        <label for="" class="">Imagen</label>';
        if($this->model->imagen){
          echo '
            <br>
            <img src="../jdshop.mx/'.$this->model->imagen.'" alt="" class="img-fluid">
            <button type="button" onclick="borrarimg('.$this->model->id.');" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
            <script>
              function borrarimg(id){
                var c = confirm("¿Deseas eliminar la imagen?");
                if(c){
                  window.location.href="?modulo=ecommerceconfig&accion=borrarimg&id="+id;
                }
              }
            </script>
          ';
        }else{
          echo '
          <br>
          <input class="form-control" type="file" name="imagen" id="imagen"  accept="image/png, image/jpeg, image/gif, image/webp, image/jpg">
          ';
        }
        echo '
      </div>
    </div>
    <div class="col-md-9 text-xs-center">
      <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Guardar</button>
    </div>
    </div>
    </form>
    </div>
   '; 
  }

  function browseresenas($page){
    echo '
    <div class="card p-2">
      <h3>Configuraciones de E-Commerce</h3>
      <hr>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link" id="profile-tab" href="?modulo=ecommerceconfig&accion=index">Slider</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab" href="?modulo=ecommerceconfig&accion=cupones">Cupones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab2" href="?modulo=ecommerceconfig&accion=faqs">FAQs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=formasp">Formas de Pago</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="about-tab3" href="?modulo=ecommerceconfig&accion=carouselyt">Carousel YouTube</a> 
        </li>
        <li class="nav-item">
          <a class="nav-link active" id="about-tab3" href="?modulo=ecommerceconfig&accion=resenas">Aprobaciones Reseñas</a> 
        </li>
      </ul>
      <div class="tab-content px-1 pt-1" bis_skin_checked="1">';

        echo '
        <div class="table-responsive">
          <table class="table table-hover table-bordered table-striped">
            <thead class="">
              <tr class="">
                <th>Respuesta</th>
                <th>Calificación</th>
                <th>Articulo</th>
                <th>Cliente</th>
                <th>Estatus</th>
                <th class="">Acciones</th>
              </tr>
            </thead>
            <tbody>';
            $estatusresenas = array("P"=>"PENDIENTE","A"=>"PUBLICADO","X"=>"CANCELADO");
            $bgresenas = array("P"=>"bg-warning","A"=>"bg-success","X"=>"bg-danger");
            while($row = $this->model->result->fetch_array()){
              echo '
              <tr>
                <td>'.$row['r_descripcion'].'</td>
                <td>'.$row['r_calificacion'].'</td>
                <td>'.busca($row['r_articulo'],'articulos','a_id','a_nmb').'</td>
                <td>'.busca($row['r_cliente'],'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)').'</td>
                <td class="text-white '.$bgresenas[$row['r_estatus']].'">'.$estatusresenas[$row['r_estatus']].'</td>
                <td nowrap>
                  <button data-toggle="tooltip" title="Publicar reseña" onclick="publicar('.$row['r_id'].');" class="btn btn-success"><i class="fa fa-check"></i></button>
                  <button data-toggle="tooltip" title="Cancelar reseña" onclick="cancelar('.$row['r_id'].');" class="btn btn-danger"><i class="fa fa-times"></i></button>
                </td>
              </tr>';
            }
            echo '
            <script>
              function publicar(id){
                var c = confirm("¿Estás seguro de publicar este comentario?");
                if(c){
                  window.location.href="?modulo=ecommerceconfig&accion=dictamenresena&estatus=A&id="+id;
                }
              }
              function cancelar(id){
                var c = confirm("¿Estás seguro de cancelar este comentario?");
                if(c){
                  window.location.href="?modulo=ecommerceconfig&accion=dictamenresena&estatus=X&id="+id;
                }
              }
            </script>
            </tbody>
          </table>';
          if($_REQUEST['page'] == 0){
            $hidden = 'style="pointer-events: none;
            background: #70707026;
            color: black;"';
          }else $hidden = "";
      
          echo '
          <div class="col-md-12 text-xs-center">
            <div class="mb-3">
              <nav aria-label="Page navigation">
                <ul class="pagination">
                  <li class="page-item">
                    <a class="page-link" onclick="mandar('.($_REQUEST['page']-1).')" aria-label="Previous" '.$hidden.'>
                      <span aria-hidden="true">&laquo; Ant</span>
                      <span class="sr-only">Anterior</span>
                    </a>
                  </li>';
      
                  echo '
                  <script>
                    function mandar(id){
                      window.location.href="?modulo=ecommerceconfig&accion=resenas&page="+id;
                    }
                  </script>';
      
                  $bloque = 50; 
                  $sqlpg = 'SELECT COUNT(*) FROM resenas ORDER BY r_id DESC ';
                  $resultpg = setq($sqlpg);
                  list($numg) =  $resultpg->fetch_array();
                  $pageres = ceil($numg/$bloque);
                  //die($numg);
                  if($pageres>=10){
                    if($_REQUEST['page'] == 0) { $min = 1; $nombre = "Inicio";}
                    else {$min = $_REQUEST['page']-1; $nombre = "Inicio...";}
                    if($min <= 1) $min = 1;
      
                    if($_REQUEST['page'] == ($pageres-1)) $max = ($pageres-1);
                    else $max = $_REQUEST['page']+3;
                    if($max >= ($pageres-1)) $max = ($pageres-1);
      
                    if($_REQUEST['page'] == 0) $active = "active";
                    else $active = "";
                    echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar(0)">'.$nombre.'</a></li>';
                  }elseif($pageres<=9){
                    $max=$pageres;
                    $min=0;
                  }
                  //for($i=0;$i<$pageres;$i++){
                  for($i=$min;$i<$max;$i++){
                    if($_REQUEST['page'] == $i) $active = "active";
                    else $active = "";
                    if($i == 0) $nombre = "Inicio";
                    else $nombre = $i;
                    echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.$i.')">'.$nombre.'</a></li>';
                  }
                  if($pageres<=9){
                  }else{
                    if($_REQUEST['page'] == ($pageres-5)) $nombre = ($pageres-2);
                    else $nombre = '... '.($pageres-2);
                    if($_REQUEST['page'] == $i) $active = "active";
                    else $active = "";
                    if($_REQUEST['page'] >= ($pageres-4)) echo '';
                    else echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.($pageres-2).')">'.$nombre.'</a></li>';
                    echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.($pageres-1).')">'.($pageres-1).'</a></li>';
                  }
                  if($_REQUEST['page'] == ($pageres-1) || $pageres <= 1) $hidden2 = 'style="pointer-events: none;
                  background: #70707026;
                  color: black;"';
                  else $hidden2 = '';
                  echo '<li class="page-item">
                    <a class="page-link" onclick="mandar('.($_REQUEST['page']+1).')" aria-label="Next" '.$hidden2.'>
                      <span aria-hidden="true">Sig &raquo;</span>
                      <span class="sr-only">Siguiente</span>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>';



      echo '
      </div>
      ';    
  }

  
}
?>