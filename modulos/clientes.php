<?php
ini_set('display_errors', 0);
class clientes{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelclientes(isset($obj));
  }

  function index(){
    if(!isset($_REQUEST['nmb'])){
      $_REQUEST['nmb'] = NULL;
      $estatus = "A";
    }
    else{
      if(isset($_REQUEST['estatus'])) $estatus = "A";
      else $estatus  = "I";
    }

    
    $this->model->result($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['page']);
    $this->view = new viewclientes($this->model);
    $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['page']);
  }
  function edit(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;

    $this->model->select($_GET['id']);
    $this->view = new viewclientes($this->model);
    $this->view->edit();
  }
  function show(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;

    $this->model->select($_GET['id']);
    $this->view = new viewclientes($this->model);
    $this->view->show();
  }

  function setuser(){
    $telefono1 = trim(preg_replace("/[^0-9]/", "", $_POST['telefono1']));
    $telefono2 = trim(preg_replace("/[^0-9]/", "", $_POST['telefono2']));
    $i = 0;
    if($telefono2 != ""){
      $i = 1;
      $sqladd = ' AND (c_telefono1 = "'.$telefono2.'" OR c_telefono2 = "'.$telefono2.'")';
    } else if($telefono1 != ""){
      $i = 1;
      $sqladd = 'AND (c_telefono1 = "'.$telefono1.'" OR c_telefono2 = "'.$telefono1.'")';
    } else {
      $sqladd = '';
    }

    if(isset($_POST['id'])){
      $id= $_POST['id'];
      $descb = "ACTUALIZACIÓN DE DATOS DE CLIENTE";
      $sql = 'SELECT COUNT(*) FROM crm_clientes WHERE c_id != "'.$_POST['id'].'" '.$sqladd.'';
    }
    else{
      $id = getmax("c_id","crm_clientes");
      $descb = "REGISTRO BÁSICO DE CLIENTE";
      $sql = 'SELECT COUNT(*) FROM crm_clientes WHERE 1=1 '.$sqladd.'';
    }
    
    $result = setq($sql);
    list($existe) = $result ->fetch_array(); 
    if(!$existe){
      $this->model->setdata($id,$_POST['nmb'],$_POST['apellidos'],$_POST['alias'],$_POST['correo1'],$telefono1,$telefono2,$_POST['obs'],$_POST['almacen'],$_POST['precio'], date('Y-m-d H:i:s'), $_SESSION['uid']);
      $ok = $this->model->setuser();

      if ($ok) {
        // Ruta absoluta a la carpeta "bitacora" dentro de "modulos"
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'bitacora';
        $file = $dir . DIRECTORY_SEPARATOR . 'clientes' . $_SESSION['emp'] . '.txt';

        // Crear carpeta si no existe
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Abrir archivo en modo append
        $archivo = @fopen($file, 'a');
        if ($archivo !== false) {
            // Construir la línea de bitácora
            $linea = date('Y-m-d') . '|CLIU001|' . date('H:i:s') . '|' .
                    $this->model->id . '|' . $this->model->id . '|' .
                    $_SESSION['uid'] . '|C|' . PHP_EOL;

            // Escribir y cerrar archivo
            fwrite($archivo, $linea);
            fclose($archivo);
        } else {
            // Opcional: log para detectar errores de apertura
            error_log("No se pudo abrir archivo de bitácora: $file");
        }

        // Continuar con actualización
        $sqlupd = 'UPDATE crm_cotizaciones 
                  SET cc_destino = "' . $_POST['nmb'] . ' ' . $_POST['apellidos'] . '", 
                      cc_mensaje = "Hola ' . $_POST['nmb'] . ' ' . $_POST['apellidos'] . '\nA continuación encontrarás listados los productos de acuerdo con tu solicitud de cotización"
                  WHERE cc_cliente = "' . $this->model->id . '" 
                  AND cc_estatus NOT IN ("V", "A")';
        setq($sqlupd);
    }

      if($ok) bitacora($this->model->id,"CLIENTES",$descb,NULL,NULL);
      
      if(isset($_POST['flag'])){
        $sqlnewcliente = 'UPDATE crm_leads SET cl_estatus = "F" WHERE cl_id = "'.$_POST['flag'].'"';
        setq($sqlnewcliente);
        echo '
        <form hidden id="formreg" method="post" action="?modulo=tableros&accion=index" autocomplete="off">
          <input type="number" name="flag" value="1" hidden>
          <input type="text" id="aliasp" name="aliasp" value="'.$this->model->alias.'" hidden>        
        </form>
        <script>
          document.getElementById("formreg").submit();
        </script>
        ';
      } else{
        redirect("?modulo=clientes&accion=edit&id=".$this->model->id.'&ok='.$ok);
      }
    } else {
      echo '<script>
        alert("El cliente ya esta registrado con el número de telefono ingresado");
      </script>';
      redirect("?modulo=clientes&accion=edit&id=".$this->model->id.'&ok='.$ok);
    }
  }

  function bitacora(){
    $cliente = busca($_GET['cliente'],'crm_clientes','c_id','c_nmb');
    if($_REQUEST['fini']) $fini = $_REQUEST['fini']; else $fini = date('Y-m-01');
    if($_REQUEST['ffin']) $ffin = $_REQUEST['ffin']; else $ffin = date('Y-m-d',strtotime('last day of this month')); 
    //$fini = busca($cliente,'crm_bitacora','cb_cliente','MIN(cb_fecha)');
    //if(!$fini) $fini = date('Y')."-01-01";


    //$ffin = date('Y-m-d',strtotime('last day of this month'));

    if(!isset($_GET['cliente'])) $_GET['cliente'] = NULL;

    $this->model->select($_GET['cliente']);
    $this->model->resultbitacora($_GET['cliente'],$fini,$ffin);
    $this->view = new viewclientes($this->model);
    $this->view->bitacora($_GET['cliente'],$fini,$ffin);
  }

  function entrada_bitacora(){
    $desc = clearvminus($_POST['descripcion']);

    bitacora($_GET['cliente'],NULL,$desc,NULL,NULL);

    redirect("?modulo=clientes&accion=bitacora&cliente=".$_GET['cliente'].'&fini='.$_GET['fini'].'&ffin='.$_GET['ffin']);
  }

  function coment_bitacora(){

    $this->model->coment_bitacora($_GET['id'],$_GET['fini'],$_GET['ffin'],$_POST['coment']);
    $cliente = busca($_GET['id'],'crm_bitacora','cb_id','cb_cliente');

    redirect("?modulo=clientes&accion=bitacora&cliente=".$cliente.'&fini='.$_GET['fini'].'&ffin='.$_GET['ffin']);
  }

  function pin_bitacora(){

    $this->model->pin_bitacora($_GET['id']);
    $cliente = busca($_GET['id'],'crm_bitacora','cb_id','cb_cliente');

    redirect("?modulo=clientes&accion=bitacora&cliente=".$cliente.'&fini='.$_GET['fini'].'&ffin='.$_GET['ffin']);

  }

  function direcciones(){
    if(!isset($_GET['cliente'])) $_GET['cliente'] = NULL;

    $this->model->select($_GET['cliente']);
    $this->model->resultdir($_GET['cliente']);
    $this->view = new viewclientes($this->model);
    $this->view->direcciones();
  }

  function setdircliente(){

    if(isset($_POST['predeterminada'])) $this->model->predeterminado = "1"; else $this->model->predeterminado = 0;

    $this->model->setdataDir($_GET['dir'],$_GET['cliente'],$_POST['nmbdir'],$_POST['calle'],$_POST['tipodir'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['cp'],$_POST['municipio'],$_POST['estado'],$_POST['pais']);
    $this->model->setdircliente();

    $archivo = fopen('bitacora/clientes'.$_SESSION['emp'].'.txt','a');
    fwrite($archivo,''.date('Y-m-d').'|CLUD001|'.date('H:i:s').'|'.$this->model->id.'|'.$this->model->id.'|'.$_SESSION['uid'].'|C|'.PHP_EOL);
    fclose($archivo);

    redirect('?modulo=clientes&accion=direcciones&cliente='.$_GET['cliente'].'&lastid='.$this->model->id);
  }

  function insertcliente(){
    $dir = getmax('cd_id','crm_direcciones');

    $this->model->setdataDir($dir,$_GET['cliente'],$_POST['nmbdir'],$_POST['calle'],$_POST['tipodir'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['cp'],$_POST['municipio'],$_POST['estado'],$_POST['pais']);
    $this->model->insertdircliente();

    $archivo = fopen('bitacora/clientes'.$_SESSION['emp'].'.txt','a');
    fwrite($archivo,''.date('Y-m-d').'|CLID001|'.date('H:i:s').'|'.$this->model->id.'|'.$this->model->id.'|'.$_SESSION['uid'].'|C|'.PHP_EOL);
    fclose($archivo);

    redirect('?modulo=clientes&accion=direcciones&cliente='.$this->model->cliente.'&lastid='.$this->model->id);

  }

  function fiscales(){
    if(!isset($_GET['cliente'])) $_GET['cliente'] = NULL;

    $this->model->select($_GET['cliente']);
    $this->model->resultfiscales($_GET['cliente']);
    $this->view = new viewclientes($this->model);
    $this->view->fiscales();
  }

  function insertfiscliente(){  
    $idfisc = getmax('cf_id','crm_fiscales');

    if($_POST['direccion'] == "X"){
      $dir = getmax('cd_id','crm_direcciones');

      $this->model->setdataDir($dir,$_GET['cliente'],$_POST['nmfiscales'],$_POST['calle'],$_POST['tipodir'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['cp'],$_POST['municipio'],$_POST['estado'],$_POST['pais']);
      $this->model->insertdircliente();

      $_POST['direccion'] = $dir;
    }

    $this->model->setdataFis($idfisc,$_GET['cliente'],$_POST['nmfiscales'],$_POST['razonsocial'],$_POST['rfc'],$_POST['direccion'],$_POST['correo1'],$_POST['correo2'],$_POST['regimen'],$_POST['predeterminada']);
    $this->model->insertfiscliente();


    redirect('?modulo=clientes&accion=fiscales&cliente='.$this->model->cliente.'&lastid='.$this->model->id);
  }

  function updatefiscliente(){

    $this->model->setdataFis($_GET['idfis'],$_GET['cliente'],$_POST['nmfiscales'],$_POST['razonsocial'],$_POST['rfc'],$_POST['direccion'],$_POST['correo1'],$_POST['correo2'],$_POST['regimen'],$_POST['predeterminada']);
    $this->model->updatefiscliente();

    redirect('?modulo=clientes&accion=fiscales&cliente='.$_GET['cliente'].'&lastid='.$this->model->id);
  }

  function expediente(){
    $this->model->select($_GET['cliente']);
    //$this->model->resultexpediente();
    $this->view = new viewclientes($this->model);
    $this->view->expediente();
  }

  function setexpediente(){

    $this->model->resultexpediente();
    $this->model->setexpediente($_GET['cliente']);

    redirect('?modulo=clientes&accion=expediente&cliente='.$_GET['cliente']);
  }

  function precios(){
    if(!isset($_REQUEST['categoria']) || empty($_REQUEST['categoria'])) $_REQUEST['categoria'] = NULL;

    $this->model->select($_GET['cliente']);
    $this->model->resultprecios($_REQUEST['categoria']);
    $this->view = new viewclientes($this->model);
    $this->view->precios($_REQUEST['categoria']);
  }

  function preciocl(){
    $this->model->select($_GET['cliente']);

    for($i=1;$i<=$_POST['round'];$i++){
      $idart = $_POST['articulo'.$i];
      if(isset($_POST['modo'.$idart])) $modo = "%"; else $modo = "$";
      if(isset($_POST['masmenos'.$idart])) $masmenos = "+"; else $masmenos = "-";

      if($modo == "%") $dif = $_POST['precio'.$idart]*($_POST['valor'.$idart]/100);
      else $dif = $_POST['valor'.$idart];

      if($masmenos == "+") $preciof = $_POST['precio'.$idart]+$dif;
      else $preciof = $_POST['precio'.$idart]-$dif;

      $this->model->precioclient($_GET['cliente'],$idart,$_POST['costo'.$idart],$_POST['precio'.$idart],$modo,$masmenos,$_POST['valor'.$idart],$preciof);
    }

    redirect('?modulo=clientes&accion=precios&cliente='.$_GET['cliente'].'&categoria='.$_GET['categoria']);
  }

  function comodato(){
    $this->model->select($_GET['cliente']);
    $this->model->resultasignados($_GET['cliente']);
    $this->view = new viewclientes($this->model);
    $this->view->comodato();
  }
  function setcomodato(){
    if(isset($_POST['modo'])) $modo = "C"; else $modo = "R";
    if(isset($_POST['renovable'])) $renovable = "C"; else $renovable = "R";

    $this->model->setcomodato($_GET['cliente'],$_POST['activo'],$modo,$_POST['fini'],$_POST['ffin'],$_POST['importe'],$renovable);

    redirect('?modulo=clientes&accion=comodato&cliente='.$_GET['cliente']);
  }

  function reasignacion(){
    $this->model->select($_GET['id']);
    $this->model->resultareasignado($_GET['id']);
    $this->view = new viewclientes($this->model);
    $this->view->reasignacion();
  }

  function insasignacion(){
    $id = $_GET['id'];
    $this->model->select($_GET['id']);
    $this->model->insasignacion($id, $this->model->uregistro, $_POST['asignar'], $_SESSION['uid'], date('Y-m-d H:i:s'));
    $sql = 'UPDATE crm_clientes SET c_uregistro = "'.$_POST['asignar'].'" WHERE c_id = "'.$id.'"';
    setq($sql);
    redirect('?modulo=clientes&accion=reasignacion&id='.$_GET['id']);
  }  

  function insertdocumento(){
    $id = $_GET['cliente'];
    if($_FILES['archivo']['name']){
      $max = intval(busca($id, 'crm_documentos', 'cd_cliente', 'COUNT(*)'))+1;
      $nuevoid= $id.'-'.$max;
      $img = $_FILES['archivo']['name'];
      $extension = pathinfo($img, PATHINFO_EXTENSION);
      $nuevo_nombre = 'documento'.$nuevoid.'.'.$extension;
      if (isset($img) && $img != "") {
        $temp = $_FILES['archivo']['tmp_name'];
        if (move_uploaded_file($temp, 'docs/clientes/'.$nuevo_nombre)) {
          chmod('docs/clientes/'.$nuevo_nombre, 0777);
          $sql = 'INSERT INTO crm_documentos SET cd_cliente = "'.$id.'",
                                                 cd_descripcion = "'.$_POST['descripcion'].'",
                                                 cd_tipo = "'.$_POST['tipo'].'",
                                                 cd_estatus = "N",
                                                 cd_ugen = "'.$_SESSION['uid'].'",
                                                 cd_fgen = "'.date('Y-m-d H:i:s').'",
                                                 cd_archivo = "'.$nuevo_nombre.'"';
          setq($sql);
        }
        else {
          die('<div><b>Ocurrió algún error al subir el fichero. No pudo guardarse.</b></div>');
        }
        
      }
    } else {
      echo alert_back('Error al recibir la información');
    }

    redirect('?modulo=clientes&accion=expediente&cliente='.$_GET['cliente']);
  }

  function autorizardoc(){
    $id = $_GET['id'];
    $cliente = $_GET['cliente']; 

    $sql = 'UPDATE crm_documentos SET cd_estatus = "A", cd_fcambio = "'.date('Y-m-d H:i:s').'", cd_ucambio = "'.$_SESSION['uid'].'" WHERE cd_id = "'.$id.'"';
    setq($sql);

    redirect('?modulo=clientes&accion=expediente&cliente='.$_GET['cliente']);
  }

  function denegardoc(){
    $id = $_GET['id'];
    $cliente = $_GET['cliente']; 

    $sql = 'UPDATE crm_documentos SET cd_estatus = "C", cd_fcambio = "'.date('Y-m-d H:i:s').'", cd_ucambio = "'.$_SESSION['uid'].'" WHERE cd_id = "'.$id.'"';
    setq($sql);

    redirect('?modulo=clientes&accion=expediente&cliente='.$_GET['cliente']);
  }
}

class modelclientes{
  function select($id){
    $sql = 'SELECT * FROM crm_clientes WHERe c_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['c_id'];
    $this->alias = $row['c_alias'];
    $this->nmb = $row['c_nmb'];
    $this->apellidos = $row['c_apellidos'];
    $this->sexo = $row['c_sexo'];
    $this->fregistro = $row['c_fregistro'];
    $this->uregistro = $row['c_uregistro'];
    $this->telefono1 = $row['c_telefono1'];
    $this->telefono2 = $row['c_telefono2'];
    $this->correo1 = $row['c_correo1'];
    $this->correo2 = $row['c_correo2'];
    $this->estatus = $row['c_estatus'];
    $this->fnacimiento = $row['c_fnacimiento'];
    $this->almacen = $row['c_almacen'];
    $this->areageog = $row['c_areageog'];
    $this->origen = $row['c_origen'];
    $this->obs= $row['c_obs'];
    $this->precio= $row['c_precio'];
    $this->fregistro= $row['c_fregistro'];
    $this->uregistro= $row['c_uregistro'];
    
  }

  function result($fini,$ffin,$page){
    $bloque = 50;
    $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
    $sql = 'SELECT * FROM crm_clientes WHERE ';
    if($grupo != "ADMIN" && $grupo != "GERENCIA" && $grupo != "SUBGERENCIA") $sql.='c_uregistro = "'.$_SESSION['uid'].'"';
    else $sql .= "1=1";
    $sql.=' ORDER BY c_id DESC LIMIT 0,500';
    
    $result = setq($sql);
    if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
    $this->result = setq($sql);
    $this->resultt = setq($sql);
  }

  function setdata($id,$nmb,$apellidos,$alias,$email,$telefono1,$telefono2,$obs,$almacen,$precio, $fregistro, $uregistro){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'");
    $cambio = "";

    $this->id = $id;
    $this->nmb = str_replace($simbol,$cambio,mb_strtoupper(trim($nmb)));
    $this->apellidos = str_replace($simbol,$cambio,mb_strtoupper(trim($apellidos)));
    $this->alias = str_replace($simbol,$cambio,mb_strtoupper(trim($alias)));
    $this->email = str_replace($simbol,$cambio,mb_strtolower(trim($email)));
    $this->telefono1 = str_replace($simbol,$cambio,mb_strtolower(trim($telefono1)));
    $this->telefono2 = str_replace($simbol,$cambio,mb_strtolower(trim($telefono2)));
    $this->obs = str_replace($simbol,$cambio,trim($obs));
    $this->almacen = str_replace($simbol,$cambio,trim($almacen));
    $this->precio = str_replace($simbol,$cambio,trim($precio));
    $this->fregistro = trim($fregistro);
    $this->uregistro = trim($uregistro);

//    unique($this->alias,'c_nmb','crm_clientes');
  }

  function setuser(){
    $sql = 'INSERT INTO crm_clientes SET
            c_id = "'.$this->id.'",
            c_alias = "'.$this->alias.'",
            c_empresa = "'.$_SESSION['emp'].'",
            c_nmb= "'.$this->nmb.'",
            c_apellidos= "'.$this->apellidos.'",
            c_correo1= "'.$this->email.'",
            c_telefono1= "'.$this->telefono1.'",
            c_telefono2= "'.$this->telefono2.'",
            c_almacen= "'.$this->almacen.'",
            c_precio= "'.$this->precio.'",
            c_fregistro = "'.$this->fregistro.'",
            c_uregistro = "'.$this->uregistro.'",
            c_obs= "'.$this->obs.'"
            ON DUPLICATE KEY UPDATE
            c_alias = "'.$this->alias.'",
            c_empresa = "'.$_SESSION['emp'].'",
            c_nmb= "'.$this->nmb.'",
            c_apellidos= "'.$this->apellidos.'",
            c_correo1= "'.$this->email.'",
            c_telefono1= "'.$this->telefono1.'",
            c_almacen= "'.$this->almacen.'",
            c_precio= "'.$this->precio.'",
            c_telefono2= "'.$this->telefono2.'",
            c_obs= "'.$this->obs.'"';
    $ok = setq($sql);

    return($ok);
  }

  function resultbitacora($id,$fini,$ffin){
    $sql = 'SELECT * FROM crm_bitacora WHERE cb_cliente = "'.$id.'"
            AND DATE(cb_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"
            ORDER BY cb_fijo DESC,cb_proyecto, cb_fecha DESC';
    $this->result = setq($sql);
  }

  function coment_bitacora($id,$fini,$ffin,$coment){
    $comentario = clearvminus($coment);

    $sql = 'UPDATE crm_bitacora SET cb_comentario = "'.$comentario.'" WHERE cb_id = "'.$id.'"';
    setq($sql);
  }

  function pin_bitacora($id){
    if(busca($id,'crm_bitacora','cb_id','cb_fijo') == "0") $actp = "1"; else $actp = "0";

    $sql = 'UPDATE crm_bitacora SET cb_fijo = "'.$actp.'" WHERE cb_id = "'.$id.'"';
    setq($sql);
  }

  function resultdir($id){
    $sql = 'SELECT * FROM crm_direcciones WHERE cd_cliente = "'.$id.'"
            ORDER BY cd_predeterminada DESC,cd_id ASC';
    $this->resultdir = setq($sql);
    $this->id = $id;
  }

  function resultfiscales($id){
    $sql = 'SELECT * FROM crm_fiscales WHERE cf_cliente = "'.$id.'"
            ORDER BY cf_predeterminada DESC,cf_id ASC';
    $this->resultdir = setq($sql);
    $this->id = $id;
  }

  function setdataDir($id,$cliente,$nmbdir,$calle,$tipod,$nume,$numi,$colonia,$cp,$municipio,$estado,$pais){
    $this->id = clearvmayus($id);
    $this->cliente = clearvmayus($cliente);
    $this->nmbdir = clearvmayus($nmbdir);
    $this->calle = clearvmayus($calle);
    $this->tipod = clearvmayus($tipod);
    $this->nume = clearvmayus($nume);
    $this->numi = clearvmayus($numi);
    $this->colonia = clearvmayus($colonia);
    $this->cp = clearvmayus($cp);
    $this->municipio = clearvmayus($municipio);
    $this->estado = clearvmayus($estado);
    $this->pais = clearvmayus($pais);
  }

  function setdircliente(){
    $sql = 'UPDATE crm_direcciones SET
            cd_tipodir = "'.$this->tipod.'",
            cd_calle = "'.$this->calle.'",
            cd_nume = "'.$this->nume.'",
            cd_numi = "'.$this->numi.'",
            cd_colonia = "'.$this->colonia.'",
            cd_municipio = "'.$this->municipio.'",
            cd_cp = "'.$this->cp.'",
            cd_estado = "'.$this->estado.'",
            cd_pais = "'.$this->pais.'"
            WHERE cd_id = "'.$this->id.'"';
    setq($sql);

    if(isset($_POST['nmbdir'])){
      $sqlnm = 'UPDATE crm_direcciones SET cd_nmbdir = "'.$this->nmbdir.'" WHERE cd_id = "'.$this->id.'"';
      setq($sqlnm);
    }

    if($this->predeterminado == "1"){
      $sql0 = 'UPDATE crm_direcciones SET cd_predeterminada = "0" WHERE cd_cliente = "'.$this->cliente.'"';
      setq($sql0);

      $sql1 = 'UPDATE crm_direcciones SET cd_predeterminada = "1"  WHERE cd_id = "'.$this->id.'" AND cd_cliente = "'.$this->cliente.'"';
      setq($sql1);
    }
  }

  function insertdircliente(){
    $sql = 'INSERT INTO crm_direcciones SET
            cd_id = "'.$this->id.'",
            cd_nmbdir = "'.$this->nmbdir.'",
            cd_cliente = "'.$this->cliente.'",
            cd_tipodir = "'.$this->tipod.'",
            cd_calle = "'.$this->calle.'",
            cd_nume = "'.$this->nume.'",
            cd_numi = "'.$this->numi.'",
            cd_colonia = "'.$this->colonia.'",
            cd_municipio = "'.$this->municipio.'",
            cd_cp = "'.$this->cp.'",
            cd_estado = "'.$this->estado.'",
            cd_predeterminada = "0",
            cd_pais = "'.$this->pais.'" ';
    setq($sql);
  }

  function selectfisc($id,$idfisc){
    $sql = 'SELECT * FROM crm_fiscales WHERE cf_cliente= "'.$id.'" AND cf_id = "'.$idfisc.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->idf = $row['cf_id'];
    $this->cliente = $row['cf_cliente'];
    $this->nmfiscales = $row['cf_nmfiscales'];
    $this->rfc = $row['cf_rfc'];
    $this->razonsocial = $row['cf_razonsocial'];
    $this->regimen = $row['cf_regimen'];
    $this->direccion = $row['cf_direccion'];
    $this->predeterminada = $row['cf_predeterminada'];
    $this->correo1 = $row['cf_correo1'];
    $this->correo2 = $row['cf_correo2'];

    $sql = 'SELECT * FROM crm_direcciones WHERE cd_id= "'.$this->direccion.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->calle = $row['cd_calle'];
    $this->nume = $row['cd_nume'];
    $this->numi = $row['cd_nume'];
    $this->colonia = $row['cd_colonia'];
    $this->municipio = $row['cd_municipio'];
    $this->cp = $row['cd_cp'];
    $this->estado = $row['cd_estado'];
    $this->pais = $row['cd_pais'];

  }

  function setdataFis($id,$cliente,$nmfiscales,$razonsocial,$rfc,$direccion,$correo1,$correo2,$regimen,$predeterminada){
    $this->id = clearvmayus($id);
    $this->cliente = clearvmayus($cliente);
    $this->nmfiscales = clearvmayus($nmfiscales);
    $this->razonsocial = clearvmayus($razonsocial);
    $this->rfc = clearvmayus($rfc);
    $this->direccion = clearvmayus($direccion);
    $this->correo1 = clearvminus($correo1);
    $this->correo2 = clearvminus($correo2);
    $this->regimen = clearvminus($regimen);
    if($predeterminada) $this->predeterminada = 1; else $this->predeterminada = 0;
  }

  function insertfiscliente(){
    $sql = 'INSERT INTO crm_fiscales SET
            cf_id = "'.$this->id.'",
            cf_cliente = "'.$this->cliente.'",
            cf_nmfiscales = "'.$this->nmfiscales.'",
            cf_rfc = "'.$this->rfc.'",
            cf_razonsocial = "'.$this->razonsocial.'",
            cf_direccion = "'.$this->direccion.'",
            cf_correo1 = "'.$this->correo1.'",
            cf_regimen = "'.$this->regimen.'",
            cf_correo2 = "'.$this->correo2.'"';
    setq($sql);


    if($this->predeterminada == "1"){
      $sql0 = 'UPDATE crm_fiscales SET cf_predeterminada = "0" WHERE cf_cliente = "'.$this->cliente.'"';
      setq($sql0);

      $sql1 = 'UPDATE crm_fiscales SET cf_predeterminada = "1"
               WHERE cf_id = "'.$this->id.'" AND cf_cliente = "'.$this->cliente.'"';
      setq($sql1);
    }
  }

  function updatefiscliente(){
    $sql = 'UPDATE crm_fiscales SET
            cf_cliente = "'.$this->cliente.'",
            cf_nmfiscales = "'.$this->nmfiscales.'",
            cf_rfc = "'.$this->rfc.'",
            cf_razonsocial = "'.$this->razonsocial.'",
            cf_direccion = "'.$this->direccion.'",
            cf_correo1 = "'.$this->correo1.'",
            cf_regimen = "'.$this->regimen.'",
            cf_correo2 = "'.$this->correo2.'"
            WHERE cf_id = "'.$this->id.'"';
    setq($sql);


    if($this->predeterminada == "1"){
      $sql0 = 'UPDATE crm_fiscales SET cf_predeterminada = "0" WHERE cf_cliente = "'.$this->cliente.'"';
      setq($sql0);

      $sql1 = 'UPDATE crm_fiscales SET cf_predeterminada = "1"
               WHERE cf_id = "'.$this->id.'" AND cf_cliente = "'.$this->cliente.'"';
      setq($sql1);
    }
  }

  function progress($cliente){
    $sql = 'SELECT cs_id FROM crm_segmentos WHERE cs_estatus = "A"';
    $result = setq($sql);
    $respuestas = 0;
    while($row = $result->fetch_array()){
      $sqls = 'SELECT COUNT(*) FROM crm_clientesegmento WHERE cls_cliente = "'.$cliente.'"
               AND cls_segmento = "'.$row['cs_id'].'"';
      $results = setq($sqls);
      list($numresp) = $results->fetch_array();
      if($numresp) $respuestas++;
    }

    $preguntas = $result->num_rows;
    $calificacion = round(($respuestas/$preguntas)*100);

    return($calificacion);
  }

  function resultexpediente(){
    $sql = 'SELECT * FROM crm_segmentos WHERE cs_estatus = "A" ORDER BY cs_id';
    $this->resultexp = setq($sql);
  }

  function setexpediente($cliente){
    while($rowex = $this->resultexp->fetch_array()){
      $sql = 'INSERT INTO crm_clientesegmento SET
              cls_cliente = "'.$cliente.'",
              cls_segmento = "'.$rowex['cs_id'].'",
              cls_valor = "'.$_POST['valor'.$rowex['cs_id']].'"
              ON DUPLICATE KEY UPDATE
              cls_valor = "'.$_POST['valor'.$rowex['cs_id']].'"';
      if(!empty($_POST['valor'.$rowex['cs_id']]))
        setq($sql);
    }
  }

  function resultprecios($categoria){
    if($categoria) $sqlcat = ' AND cat_id = "'.$categoria.'"'; else $sqlcat = "";

    $sqlpre = 'SELECT a_id,a_nmb,cat_nmb,ep_nmb,ap_costo,ap_precio
               FROM articulos_precios
               INNER JOIN esquema_precio ON ap_esquema = ep_id
               INNER JOIN articulos ON a_id = ap_articulo
               INNER JOIN categorias ON a_categoria = cat_id
               WHERE ap_esquema = "'.$this->precio.'" AND ap_activo = "1" AND a_estatus = "A" '.$sqlcat.'
               ORDER BY cat_nmb, a_nmb';
    $this->resultprice = setq($sqlpre);
  }

  function precioclient($cliente,$articulo,$costo,$preciolt,$modo,$masmenos,$valor,$preciof){
    $sqlcl = 'INSERT INTO clientes_precios SET
              cp_articulo = "'.$articulo.'",
              cp_cliente = "'.$cliente.'",
              cp_costo = "'.$costo.'",
              cp_preciolista = "'.$preciolt.'",
              cp_modo = "'.$modo.'",
              cp_masmenos = "'.$masmenos.'",
              cp_valor = "'.$valor.'",
              cp_precio = "'.$preciof.'"
              ON DUPLICATE KEY UPDATE
              cp_costo = "'.$costo.'",
              cp_preciolista = "'.$preciolt.'",
              cp_modo = "'.$modo.'",
              cp_masmenos = "'.$masmenos.'",
              cp_valor = "'.$valor.'",
              cp_precio = "'.$preciof.'"
              ';
    setq($sqlcl);
  }

  function resultasignados($cliente){
    $sql = 'SELECT * FROM activo_almacen
            INNER JOIN activo_comodato ON aa_id = ac_activo
            INNER JOIN activos ON aa_activo = a_id
            WHERE aa_asignado = "'.$cliente.'"  AND ac_estatus = "A"
            ORDER BY aa_noserie';
    $this->result = setq($sql);
  }

  function setcomodato($cliente,$activo,$modo,$fini,$ffin,$importe,$renovable){
    $sql = 'INSERT INTO activo_comodato SET
            ac_cliente = "'.$cliente.'",
            ac_activo = "'.$activo.'",
            ac_modalidad = "'.$modo.'",
            ac_fechaini = "'.$fini.'",
            ac_ffin = "'.$ffin.'",
            ac_importe = "'.$importe.'",
            ac_autorenovable = "'.$renovable.'",
            ac_estatus = "A"';
    setq($sql);

    $idc = getmax('ac_id','activo_comodato');

    $sqla = 'UPDATE activo_almacen SET aa_estatus = "A",aa_asignado = "'.$cliente.'" WHERE aa_id = "'.$activo.'"';
    setq($sqla);

    if($importe > 0){
      include_once('cxcobrar.php');
      $cxc = new modelcxcobrar();
      $cxc->insertcxcobrar($_SESSION['emp'],$cliente,$idc,"O",$importe,$fini);
    }
  }

  function resultareasignado($id){
    $sql = 'SELECT * FROM crm_clientes_reasignacion WHERE cr_cliente = "'.$id.'"';
    $this->resultr = setq($sql);
  } 
  function insasignacion($cliente, $pertenecia,$vendedor, $uasigna, $fasigna){
    $sql = 'INSERT INTO crm_clientes_reasignacion SET cr_cliente = "'.$cliente.'",
                                                      cr_pertenecia = "'.$pertenecia.'",
                                                      cr_vendedor = "'.$vendedor.'",
                                                      cr_uasigna = "'.$uasigna.'",
                                                      cr_freasigna = "'.$fasigna.'"';
    setq($sql);                                                      
  } 
}

class viewclientes{
  var $model;
  function __construct($model){
    ?>
    <script>
    function checkguardar(){
      document.getElementById("sendform").innerHTML = "Guardando";
      document.getElementById("sendform").disabled = true;
      return true;
    }
    function checkguardar1(){
      document.getElementById("sendform1").innerHTML = "Guardando";
      document.getElementById("sendform1").disabled = true;
      return true;
    }
    </script>
    <?php
      $this->model = $model;
      $this->tipoc = array("C"=>"Cliente","P"=>"Prospécto");
      $this->arrtipodom = array("1"=>"Oficina","2"=>"Particular","3"=>"Entrega","4"=>"Adicional","5"=>"Punto de entrega","6"=>"Fiscal");
      $this->modalidades = array("C"=>"Comodato","R"=>"Renta");
  }

  function browse($fini,$ffin,$page){
    /* $numres = 50;
    $nr = $this->model->resultt->num_rows;
    $np = $nr/$numres; */

    $nuevo = '
    <a href="?modulo=clientes&accion=edit" class="btn-sm btn btn-primary">
      <i class="bi bi-plus-circle-fill"></i>
      Nuevo 
    </a>';

    

    toolbar($_GET['modulo'],'', '',$nuevo);


  

    echo '
    <div class="card mt-3">
    <div class="card-body ">';


    echo '
    <table class="" id="myTable">
      <thead class="">
        <tr class="">
          <th class="">Alias</th>
          <th class="">Telefono</th>
          <th class="">Correo</th>
          <th class="">Fecha de registro</th>
          <th class="">Estatus</th>
          <th class="">Acciones</th>
        </tr>
      </thead>
      <tbody class="">
      </tbody>
    </table>
    ';

    
    echo '
    </div>
    </div>';
    

    echo '
    
    <script>
      $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          processing: true,
          serverside: true,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatableclientes.php",
            type: "POST",
            datatype: "json",
          },
          pageLength: "50",
          responsivePriority: 1,
          createdRow: function(row, data, dataIndex) {
          const texto = data[4]; // Índice 4 = 5ta columna

          // Aplica color según el contenido del texto
          if (texto === "DOCUMENTACIÓN EN REVISION") {
            $("td", row).eq(4).css("background-color", "#f8d7da"); // rojo claro
          } else if (texto === "CLIENTE LOGRADO") {
            $("td", row).eq(4).css("background-color", "#d4edda"); // verde claro
          } else if (texto === "CLIENTE NUEVO") {
            $("td", row).eq(4).css("background-color", "#d1ecf1"); // azul claro
          }
        }
      });
    </script>
    
    ';

    /* if($_REQUEST['nmb']){

    }else{

    echo '
    <div class="col-md-12 text-xs-center">
    <div class="mb-3">
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <li class="page-item">
            <a class="page-link" href="?modulo=clientes&accion=index&page='.($_GET['page']-1).'" aria-label="Previous">
              <span aria-hidden="true">&laquo; Ant</span>
              <span class="sr-only">Anterior</span>
            </a>
          </li>';
          
    $numres = 50;
    $nr = $this->model->resultt->num_rows;
    $np = $nr/$numres;
    $paginaa = $page-1;
    $pagina = $page+1;
    $sqlpg = 'SELECT COUNT(*) FROM crm_clientes WHERE  c_empresa = "'.$_SESSION['emp'].'" AND c_estatus = "'.$estatus.'"';
    $resultpg = setq($sqlpg);
    list($numg) =  $resultpg->fetch_array();
    $pageres = ceil($numg/$numres);
    //die($pageres);

    if($pageres>10){
      if($_GET['page'] == 0) { $min = 1; $nombre = "Inicio";}
      else {$min = $_GET['page']-1; $nombre = "Inicio...";}
            if($min <= 1) $min = 1;

      if($_GET['page'] == ($pageres-1)) $max = ($pageres-1);
      else $max = $_GET['page']+3;
            if($max >= ($pageres-1)) $max = ($pageres-1);

      if($_GET['page'] == 0) $active = "active";
      else $active = "";
      echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=clientes&accion=index&page=0">'.$nombre.'</a></li>';
    }elseif($pageres<6){
      $max=$pageres;
      $min=0;
    }

    //for($i=0;$i<$pageres;$i++){
    for($i=$min;$i<$max;$i++){
        if($_GET['page'] == $i) $active = "active";
        else $active = "";
        if($i == 0) $nombre = "Inicio";
        else $nombre = $i;
      echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=clientes&accion=index&page='.$i.'">'.$nombre.'</a></li>';
    }
    
    if($pageres<9){

    }else{
    if($_GET['page'] == ($pageres-5)) $nombre = ($pageres-2);
    else $nombre = '... '.($pageres-2);
    if($_GET['page'] == $i) $active = "active";
    else $active = "";
    if($_GET['page'] >= ($pageres-4)) echo '';
    else echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=clientes&accion=index&page='.($pageres-2).'">'.$nombre.'</a></li>';
      echo '
      <li class="page-item '.$active.'"><a class="page-link" href="?modulo=clientes&accion=index&page='.($pageres-1).'">'.($pageres-1).'</a></li>';

    }
      echo '<li class="page-item">
                      <a class="page-link" href="?modulo=clientes&accion=index&page='.($_GET['page']+1).'" aria-label="Next">
                        <span aria-hidden="true">Sig &raquo;</span>
                        <span class="sr-only">Siguiente</span>
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>'; 

    } */

  }

  function edit(){

    if(isset($_POST['flag'])){
      $flag = '<input type="text" id="flag" name="flag" value="'.$_POST['idp'].'" hidden>';
      $nmb = $_POST['nmbp'];
      $correo = $_POST['correop'];
      $telefono = $_POST['telefonop'];
      $observaciones = $_POST['observacionesp'];
      $accion = "?modulo=leadscapturados&accion=index";
    } else{
      $nmb = $this->model->nmb;
      $correo = $this->model->correo1;
      $telefono = $this->model->telefono1;
      $observaciones = $this->model->obs;
      $accion = "?modulo=clientes&accion=index";
    }

    if($this->model->id){
      $flag = '';
      $titulo = 'Actualizar datos básicos de '.$this->model->alias;
    }
    else{
      $titulo = 'Registro básico de cliente';
      $this->model->almacen = "1";
    }

    $atras = '
    <a href='.$accion.'>
      <button class="mb-2 mr-2 btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>Regresar</button>
    </a>';

      toolbar($_GET['modulo'], $atras);
    ?>
    <script>
      function sendval(){
        document.getElementById("sologas").value = "1";
      }
    </script>
    <?php

 echo '
 <br>
  <div class="row">
    <div class="col-md-9">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title" id="basic-layout-form">'.$titulo.'</h4>
          <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
          <div class="heading-elements">
            <ul class="list-inline mb-0">
              <li><a data-bs-action="collapse"><i class="icon-minus4"></i></a></li>
              <li><a data-bs-action="expand"><i class="icon-expand2"></i></a></li>
              <li><a data-bs-action="close"><i class="icon-cross2"></i></a></li>
            </ul>
          </div>
        </div>';
  echo '<form autocomplete="off" name="cliedit" id="cliedit" action="?modulo=clientes&accion=setuser" method="post" >
        <input type="hidden" name="solog" value="0" id="sologas" />';
  if($this->model->id)
    echo '<input type="hidden" name="id" value="'.$this->model->id.'" />';
  echo '
        <div class="card-body in">
          <div class="card-block">
            <div class="form-body">
              <div class="row">
                <div class="col-md-6 col-sm-6">
                  <div class="input-group-desc">
                    <label class="name">Nombre(s) y apellidos*</label>';
                    echo $flag;
                    echo '<input class="form-control" autocomplete="off" type="text" value="'.$nmb.'" name="nmb" id="nmb" required="required" autofocus placeholder="Nombre del cliente">
                  </div>
                </div>

                <div class="col-md-3 col-sm-6" hidden>
                  <div class="input-group-desc">
                    <label class="label--desc">Apellidos</label>
                    <input class="input--style-3 form-control" type="text" value="'.$this->model->apellidos.'" name="apellidos" id="apellidos" placeholder="Apellidos">
                  </div>
                </div>

                <div class="col-md-3 col-sm-6">
                  <div class="input-group-desc">
                    <label class="label--desc">Nombre corto de cliente</label>
                    <input class="input--style-3 form-control" type="text" value="'.$this->model->alias.'" name="alias" id="alias" required="required" maxsize="25" placeholder="Escribe un nombre corto">
                  </div>
                </div>

                <div class="col-md-3 col-sm-6">
                  <div class="input-group-desc">
                    <label class="label--desc">Su correo principal</label>
                    <input class="input--style-3 form-control" type="email" value="'.$correo.'" name="correo1" id="email" required="required" placeholder="Correo electrónico" data-bs-toggle="tooltip" data-bs-placement="top" title="Es importante que si sea el correo de tu cliente" >
                  </div>
                </div>

              </div>
              <div class="row  mt-2">

                <div class="col-md-3 col-sm-6">
                  <div class="input-group-desc">
                    <label class="label--desc">Telefono fijo</label>
                    <input class="input--style-3 form-control" type="tel" value="'.$telefono.'" name="telefono1" id="telefono1" placeholder="Telefono fijo">
                  </div>
                </div>

                <div class="col-md-3 col-sm-6">
                  <div class="input-group-desc">
                    <label class="label--desc">Telefono celular</label>
                    <input class="input--style-3 form-control" type="tel" value="'.$this->model->telefono2.'" name="telefono2" placeholder="Telefono celular">
                  </div>
                </div>

                <div class="col-md-6 col-sm-6">
                  <div class="input-group-desc">
                    <label class="label--desc">Observaciones</label>
                    <textarea name="obs" class="form-control" placeholder="Si tienes alguna observación del cliente escribela a continuación">'.$observaciones.'</textarea >
                  </div>
                </div>
            </div>
            <br>
<!--            
            <div class="row mt-2" style="border-top: 1px solid">
              <div class="col-md-12"><h4 style="text-transform:uppercase; padding-top: 20px;">Información comercial</h4></div>              

              <div class="col-md-6 col-sm-6 mt-1">
                <div class="input-group-desc">
                  <label class="label--desc">Almacen predeterminado</label>
                    '.menu_select_db('almacenes','a_id','a_nmb',$this->model->almacen,'almacen','a_empresa = "1"',false,false,false,true).'
                </div>
              </div>

              <div class="col-md-6 col-sm-6 mt-1">
                <div class="input-group-desc">
                  <label class="label--desc">Esquema de precios base</label>
                    '.menu_select_db('esquema_precio','ep_id','ep_nmb',$this->model->precio,'precio','ep_empresa = "1"',false,false,false,true).'
                </div>
              </div>

          </div>
-->
          <div class="row mt-2">
            <center><button type="submit" id="sendform" class="btn btn-sm btn-success" ><i class="fas fa-save"></i> Guardar</button></center>
          </div>
        <!-- </div> -->
      </div>
    </div>
  </form>
  </div></div></div>';

  if($this->model->id)
    echo $this->actividades();

  }

  function bitacora($id,$fini,$ffin){
    $atras = '
    <a href="?modulo=clientes&accion=index">
      <button type="button" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>Atrás</button>
    </a>
    ';

    $entrada = '
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addcoment">
      <i class="fas fa-comment"></i> Agregar entrada
    </button>
    ';

    $filtro = '<form method="post">
          <input type="hidden" value="'.$id.'" name="id" />
          <!-- 
          <input type="hidden" value="'.$fini.'" name="fini" />
          <input type="hidden" value="'.$ffin.'" name="ffin" /> -->
          <div class="form-inline">
            <div class="mb-5">
              
            </div>
            <div class="mb-5">
              <!-- <label>Fechas Inicial</label> -->
              <input type="date" value="'.$fini.'" name="fini" class="form-control"/>
            </div>
            <div class="mb-5">
              <!-- <label>Fecha Final</label> -->
              <input type="date" value="'.$ffin.'" name="ffin" class="form-control" />
            </div>
            <div class="mb-5">
              <button type="submit" class="btn btn-success"><i class="fas fa-search"></i>Filtrar</button> 
            </div>
          </div>
          </form>';

    toolbar($_GET['modulo'], $atras, $filtro, $entrada);

    echo '<!-- Modal -->
          <div class="modal fade text-xs-left" id="addcoment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              </div>
              <div class="modal-body">
                <form action="?modulo=clientes&accion=entrada_bitacora&cliente='.$id.'&fini='.$fini.'&ffin='.$ffin.'" method="post">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="card">
                        <div class="card-header">
                          <h4 class="card-title" id="basic-layout-card-center">Agregar entrada a la bitacora</h4>
                          <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                          <div class="heading-elements">
                          </div>
                        </div>
                        <div class="card-body in">
                          <div class="card-block">
                            <div class="card-text">
                              <p class="text-justify">Agrega una entrada manual a la bitácora, esto es recomendable para dar retroalimentación sobre algun hallazgo ajeno a citas o proyectos en caso de requerir agregar de lo anterior mencionado, vaya a la sección correspondiente.</p>
                            </div>
                              <div class="form-body">

                                <div class="mb-5">
                                  <label for="fecha">Fecha de entrada</label>
                                  <input type="text" id="fecha" class="form-control" placeholder="name" name="fecha" value="'.fecha_formato(date('Y-m-d H:i:s'),true,false).'" readonly="readonly" >
                                </div>

                                <div class="mb-5">
                                  <label for="cliente">Alias del cliente</label>
                                  <input type="text" id="cliente" class="form-control" placeholder="title" name="cliente" value="'.busca($id,'crm_clientes','c_id','c_alias').'" readonly="readonly" >
                                </div>


                                <div class="mb-5">
                                  <label for="entrada">Entrada</label>
                                  <textarea id="entrada" class="form-control focus" placeholder="Escribe la entrada a la bitacora" autofocus="autofocus" name="descripcion" requiered="required"></textarea>
                                </div>
                              </div>

                              <div class="form-actions center">
                                <button type="button" class="btn btn-sm btn-warning mr-1" data-bs-dismiss="modal" >
                                  <i class="fas fa-window-close"></i> Cerrar sin guardar
                                </button>
                                <button type="submit" class="btn btn-sm btn-success">
                                  <i class="fas fa-check"></i> Guardar entrada
                                </button>
                              </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            </div>
          </div>';
    ?>
    <script>
      function sendval(){
        document.getElementById("sologas").value = "1";
      }
    </script>
    <?php

  $cliente = busca($id,'crm_clientes','c_id','c_nmb');
  $nr = busca($id,'remisiones','r_estatus = "A" AND r_cliente','COUNT(*)');
  if(!$nr) $numrem = 0;

  $ultimac = busca($_GET['id'],'remisiones','r_estatus = "A" AND r_cliente','MAX(r_faplica)');
  if(!$ultimac) $ucom = "No hay última compra";
  else $ucom = fecha_formato(date('Y-m-d',strtotime($ultimac)),false,true);

  $saldo = 'Debe pago de facturas';

  //ct_fini BETWEEN "'.$fini.'" AND "'.$ffin.'" AND 

  echo '
    <div class="row">
      <div class="col-xl-2 col-lg-4 col-xs-6">
        <div class="card">
          <div class="card-body">
            <div class="card-block">
              <div class="media">
                <div class="media-left media-middle">
                    <i class="icon-object-group indigo font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">
                    <h3>'.busca($_GET['id'],'crm_tableros','ct_cliente','COUNT(*)').'</h3>
                    <span>Tableros</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-lg-4 col-xs-6">
        <div class="card">
          <div class="card-body">
            <div class="card-block">
              <div class="media">
                <div class="media-left media-middle">
                  <i class="icon-ios-cart teal darken font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">
                  <h3>'.busca($_GET['id'],'remisiones','r_estatus = "A" AND r_cliente','COUNT(*)').'</h3>
                  <span>Compras</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-lg-4 col-xs-6">
        <div class="card">
          <div class="card-body">
            <div class="card-block">
              <div class="media">
                <div class="media-left media-middle">
                    <i class="icon-calendar3 indigo font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">
                    <h3>'.$ucom.'</h3>
                    <span class="">Ultima Compra</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- <div class="col-xl-2 col-lg-4 col-xs-6">
        <div class="card">
          <div class="card-body">
            <div class="card-block">
              <div class="media">
                <div class="media-left media-middle">
                    <i class="icon-user-tie teal darken font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">
                    <h3>'.busca($id,'crm_cliente_asesor','cca_cliente','cca_asesor').'</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> -->
      <div class="col-xl-3 col-lg-4 col-xs-6">
        <div class="card">
          <div class="card-body">
            <div class="card-block">
              <div class="media">
                <div class="media-left media-middle">
                    <i class="icon-glass indigo darken font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">';
                if(busca($_GET['id'],'crm_actividades INNER JOIN  crm_tableros ON ct_id = ca_tablero','ca_fecha >= "'.date('Y-m-d').'" AND ct_cliente','ca_fecha') == 0) $fecha = 'No hay fecha';
                else $fecha = busca($_GET['id'],'crm_actividades INNER JOIN  crm_tableros ON ct_id = ca_tablero','ca_fecha >= "'.date('Y-m-d').'" AND ct_cliente','ca_fecha');
                echo '
                    <h3 class="">'.$fecha.'</h3>
                    <span>Proxima cita</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-lg-4 col-xs-6">
        <div class="card">
          <div class="card-body">
            <div class="card-block">
              <div class="media">
                <div class="media-left media-middle">
                    <i class="icon-calculator3 teal darken font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">';
                if(busca($_GET['id'],'cxcobrar','cx_estatus IN ("A","N") AND cx_cliente','SUM(cx_importe - cx_abonado)') == 0) $val = 0;
                else $val = busca($_GET['id'],'cxcobrar','cx_estatus IN ("A","N") AND cx_cliente','SUM(cx_importe - cx_abonado)');
                echo '
                  <h3>$'.number_format($val,2).'</h3>
                  <span class="">Cxcobrar</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>';

    echo '
    <div class="card mt-3">
        <div class="card-body">
        <div class="">';
  echo '<div class="table-responsive">
          <table class="mb-0 table table-hover" id="myTable2">
            <thead class="bg-light-blue bg-darken-2 ">
              <tr>
                <th width="5%">Fijar</th>
                <th>Fecha entrada</th>
                <th>Usuario</th>
                <th>Descripción</th>
                <th>Documento Referencia</th>
                <th>Comentarios</th>
              </tr>
            </thead>';
  $idi = 0;
  echo '
  <script>
    function confpin(idp){
      var conf = confirm("¿Deseas fijar este pin hasta arriba de la bitacora?");
      if(conf == true){
        document.location.href="?modulo=clientes&accion=bitacora&accion=pin_bitacora&id=" + idp + "&fini='.$fini.'&ffin='.$ffin.'";
      }
    }
  </script>';
  while($row = $this->model->result->fetch_array()){
    if($row['cb_fijo'] == "0") $idi++;

    if($row['cb_idref'] == 0) $idref = "Sin referencia";
    else $idref = "Referencia";

    if($row['cb_fijo'] == "0") $pinbg = "secondary"; else $pinbg = "grey bg-darken-1";

    echo '<tr>
            <td class="bg-'.$pinbg.'"> <i class="fas fa-pushpin" onclick="confpin('.$row['cb_id'].');"></i> </td>
            <td>'.fecha_formato($row['cb_fecha'],true,true).'</td>
            <td>'.$row['cb_user'].'</td>
            <td>'.$row['cb_descripcion'].'</td>
            <td>'.$idref.'</td><td>';

    if($idi == 1 && $row['cb_user'] == $_SESSION['uid'])
      echo '<form name="formcoment" id="formcoment" action="?modulo=clientes&accion=coment_bitacora&id='.$row['cb_id'].'&fini='.$fini.'&ffin='.$ffin.'" method="post" >
        <textarea name="coment" id="coment" class="form-control" rows="2">'.$row['cb_comentario'].'</textarea>';

    echo '</td></tr>';
  }
  echo '</table>';
  echo '</div>
      </div>
    </div>
  </div>';


  echo '
  <div class="card mt-3">
      <div class="card-body">
      <div class="">';

  echo '
  <div class="table-responsive">
    <table class="mb-0 table table-hover" id="myTable">
      <thead class="bg-light-blue bg-darken-2 ">
        <tr class="">
          <th class="">Fecha</th>
          <th class="">Código</th>
          <th class="">Detalles</th>
          <th class="">Acción</th>
          <th class="">Relación</th>
          <th class="">Registro</th>
          <th class="">Usuario</th>
        </tr>
      </thead>
      <tbody class="">';

      $archivoRuta = 'bitacora/clientes'.$_SESSION['emp'].'.txt';
      $arc = fopen($archivoRuta, 'r'); // Intenta abrir el archivo en modo lectura ('r')
      if (!$arc) {
          $mensaje = 'No se pudo abrir el archivo.';
      } else {
        while(!feof($arc))  {
          $linea = fgets($arc);
          $arreglos = explode("|",$linea);
          if(date('Y-m-d', strtotime($arreglos[0])) >=  date('Y-m-d', strtotime($fini)) && date('Y-m-d', strtotime($arreglos[0])) <= date('Y-m-d', strtotime($ffin)) && $arreglos[4] == $_GET['id'] && $arreglos[6] == "C"){
            $buscamodulo = busca($arreglos[1],'bitacora_codigos','bc_id','bc_modulo');
            if($buscamodulo == "facturacion") { $tabla = 'crm_facturas'; $referencia = '<a href="'.$arreglos[3].'">'.busca($arreglos[3],'remisiones','r_id','r_nmb').' <br> '.busca($arreglos[3],'remisiones','r_id','r_observaciones').' </a>';}
            elseif($buscamodulo == "clientes") { $tabla = 'crm_clientes'; $referencia = '<a href="'.$arreglos[3].'">'.busca($arreglos[3],'crm_clientes','c_id','c_nmb').' <br> '.busca($arreglos[3],'crm_clientes','c_id','c_obs').' </a>';}
            elseif($buscamodulo == "remisiones") { $tabla = 'crm_facturas'; $referencia = '<a href="'.$arreglos[3].'">'.busca($arreglos[3],'remisiones','r_id','r_nmb').' <br> '.busca($arreglos[3],'remisiones','r_id','r_observaciones').' </a>';}
            elseif($buscamodulo == "cxcobrar") { $tabla = 'cxcobrar'; $tabla = 'crm_facturas'; $referencia = '<a href="'.$arreglos[3].'">'.busca($arreglos[3],'cxcobrar','cx_id','cx_nmb').' <br> '.busca($arreglos[3],'cxcobrar','cx_id','cx_observaciones').' </a>';}
            elseif($buscamodulo == "cotizaciones") { $tabla = 'crm_cotizaciones'; $referencia = '<a href="'.$arreglos[3].'">'.busca($arreglos[3],'crm_cotizaciones','cc_id','cc_nmb').' <br> '.busca($arreglos[3],'crm_cotizaciones','cc_id','cc_descripcion').' </a>'; }
            else $referencia = NULL;

            echo '
            <tr>
              <td class="">'.$arreglos[0].' '.$arreglos[2].'</td> <!-- fecha_formato($arreglos[0]." ".$arreglos[2],true,false) -->
              <td class="">'.$arreglos[1].'</td>
              <td class="">'.busca($arreglos[1],'bitacora_codigos','bc_id','bc_detalles').'</td>
              <td class="">'.busca($arreglos[1],'bitacora_codigos','bc_id','bc_accion').'</td>
              <td class="">'.$referencia.'</td>
              <td class=""><a href="index?modulo=clientes&accion=edit&id='.$arreglos[4].'" target="_blank">'.busca($arreglos[4],'crm_clientes','c_id','CONCAT(c_nmb," ",c_apellidos)').'</a></td>
              <td class="">'.$arreglos[5].'</td>
            </tr>
            ';
          }
      }
          fclose($arc); // Cierra el archivo al finalizar el ciclo while
      }

      echo '
      </tbody>
    </table>
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
          }
      });
    </script>';

    echo '
    <script>
        $("#myTable2").DataTable( {
            paging: true,
            scrollY: 400,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            }
        });
      </script>';

  }

  function direcciones(){
    echo '
        <div class="modal fade" id="adddireccion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header mb-2">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <label class="modal-title text-text-bold-600" id="myModalLabel33">Añadir dirección</label>
              </div>
              <form method="post" action="?modulo=clientes&accion=insertcliente&cliente='.$this->model->id.'" class="container" name="senddir" onsubmit="checkguardar();">
                <div class="card-body row">
                  <div class="mb-5 col-md-12 align-middle">
                    <label for="calle">Alias del domicilio</label>
                    <input type="text" name="nmbdir" id="nmbdir" class="form-control" placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" required="required" step="1" autofocus />
                  </div>
                  <div class="mb-5 col-12 col-md-6">
                    <label for="calle">Calle</label>
                    <input type="text" name="calle" id="calle" class="form-control" placeholder="La calle de tu domicilio" title="Se requiere un valor"  required="required" step="2" />
                  </div>
                  <div class="mb-5 col-6 col-md-3">
                    <label for="nume">Número Exterior</label>
                    <input type="text" name="nume" id="nume" class="form-control" placeholder="Número exterior" title="Se requiere un valor"  required="required" step="4" />
                  </div>
                  <div class="mb-5 col-6 col-md-3">
                    <label for="numi">Número interior</label>
                    <input type="text" name="numi" id="numi" class="form-control" placeholder="Número interior" title="Se requiere un valor" step="5"  />
                  </div>
                  <div class="mb-5 col-12 col-md-5">
                    <label for="colonia">Colonia</label>
                    <input type="text" name="colonia" id="colonia" class="form-control" placeholder="La colonia de tu domicilio" title="Se requiere un valor"  required="required" step="6"  />
                  </div>
                  <div class="mb-5 col-12 col-md-2">
                    <label for="cp">Código postal</label>
                    <input type="text" name="cp" id="cp" class="form-control" placeholder="El CP de tu domicilio" title="Se requiere un valor"  required="required" step="7"  />
                  </div>
                  <div class="mb-5 col-12 col-md-5">
                    <label for="municipio">Ciudad</label>
                    <input type="text" name="municipio" id="municipio" class="form-control" placeholder="La Ciudad de tu domicilio" title="Se requiere un valor"  required="required" step="8"  />
                  </div>
                  <div class="mb-5 col-12 col-md-5">
                    <label for="estado">Estado</label>
                    '.menu_select_db('estado','e_id','e_nmb','','estado','XXX',false, false, false, true,false).'
                  </div>
                  <div class="mb-5 col-12 col-md-3">
                    <label for="pais">Pais</label>
                    <input type="text" name="pais" id="pais" class="form-control" placeholder="El pais de tu domicilio" title="Se requiere un valor"  required="required" step="10"  />
                  </div>
                  <div class="mb-5 col-12 col-md-4">
                    <label for="tipod">Tipo de domicilio</label>';

                echo '<select name="tipodir" id="tipod" class="form-control" placeholder="Elige un tipo de domicilio" title="Se requiere un valor" required="required" step="3">';
                foreach($this->arrtipodom as $clave => $valor ){
                  echo '<option value="'.$clave.'">'.$valor.'</option>';
                }
                echo '</select>';

                echo '</div>
                  <div class="mb-5 col-12 col-md-12">
                    <label for="pais">.</label>
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> Guardar</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>';

/*     echo '<div class="row">
            <div class="mb-5 col-md-2">
              <a href="?modulo=clientes&accion=edit&id='.$this->model->id.'" >
                <button type="button" class="btn btn-warning mr-2 ml-2"><i class="icon-user4"></i> Datos de cliente</button>
              </a>
            </div>
            <div class="mb-5 col-md-2">
              <a href="?modulo=clientes&accion=index" >
                <button type="button" class="btn btn-info mr-2 ml-2"><i class="icon-th-list"></i> Listado de clientes</button>
              </a>
            </div>
            <div class="mb-5 col-md-2">
              <button type="button" class="btn btn-primary mr-2 ml-2" data-bs-toggle="modal" data-target="#adddireccion"><i class="fa fa-plus"></i> Agregar dirección </button>
            </div>
          </div>';
    echo '</div> */
        $datos = '
        <a href="?modulo=clientes&accion=edit&id='.$this->model->id.'" >
          <button type="button" class="btn btn-sm btn-warning mr-2 ml-2"><i class="fas fa-user"></i> Datos de cliente</button>
        </a>';
        $listado = '
        <a href="?modulo=clientes&accion=index" >
          <button type="button" class="btn btn-sm btn-info mr-2 ml-2"><i class="fas fa-th-list"></i> Listado de clientes</button>
        </a>';
        $agregardir = '<button type="button" class="btn btn-sm btn-primary mr-2 ml-2" data-bs-toggle="modal" data-bs-target="#adddireccion"><i class="fas fa-plus"></i> Agregar dirección </button>';
        toolbar($_GET['modulo'], '', '', $datos.$listado, $agregardir);
          echo '      
            <div class="main-card mb-3">
            <div class="card-body row" >';
  ?>
  <script>
    function sendval(){
      document.getElementById("sologas").value = "1";
    }
    function iconcollapse(idcol){
      var numdir = document.getElementById("numdirs").value;

      for(var i=1; i <= numdir;i++){
        var nmbdir = document.getElementById("nmbdir" + i).value;
        if(i == idcol){
          document.getElementById("nmbdir" + i).disabled = false;
          document.getElementById("update" + i).style.display = "";
        }
        else{
          document.getElementById("nmbdir" + i).disabled = true;
          document.getElementById("update" + i).style.display = "none";
        }
      }
    }
    function updatedir(numdir){
      document.getElementsByName("senddir" + numdir)[0].submit();
    }
    function newname(idcol){
      document.getElementById("divname"+idcol).removeAttribute("hidden");
      $("#nmbdir"+idcol).focus();
    }
  </script>
 <?php
 echo '<div id="accordionWrapa1" role="tablist" aria-multiselectable="true" class="col-md-9">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title" id="basic-layout-form">Direcciones de '.$this->model->alias.'</h4>
          </div>        ';

 $idcol = 0;
 while($rowd = $this->model->resultdir->fetch_array()){
   if($rowd['cd_predeterminada'] == 1){ $pred = "checked"; $ariae = '';} else{ $pred = ""; $ariae = "collapse in";}
   $idcol++;   

   if($idcol == 1){ $icondef = "icon-minus4"; $showup = ' '; } else{ $icondef = "fa fa-plus"; $showup = ' style="display:none;" '; }

  echo '  <div id="heading'.$idcol.'" class="card-header row border-top  mb-4">';
            echo '<button type="button" class="btn btn-sm btn-secondary col-md-1" onclick="newname('.$idcol.');" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Editar nombre de dirección"><i class="fas fa-pen-square"></i></button>';
            echo '<a data-bs-toggle="collapse" data-bs-parent="#accordionWrapa1" href="#accordion'.$idcol.'" aria-expanded="true" aria-controls="accordion1" class="card-title lead col-md-9" onclick="iconcollapse('.$idcol.')">
              <h6>'.$rowd['cd_nmbdir'].'</h6>
            </a>
            <button type="button" onclick="updatedir('.$idcol.');" class="btn btn-sm btn-success" id="update'.$idcol.'" '.$showup.'><i class="fa fa-redo"></i> Actualizar</button>
          </div>
          <form name="senddir'.$idcol.'" method="post" action="?modulo=clientes&accion=setdircliente&cliente='.$this->model->id.'&dir='.$rowd['cd_id'].'" id="accordion'.$idcol.'" role="tabpanel" aria-labelledby="heading'.$idcol.'" class="card-collapse '.$ariae.'" aria-expanded="true">
            <div class="card-body row">
            <div class="form-group col-12 col-md-12 dirname'.$idcol.'" id="divname'.$idcol.'" hidden>
                <label for="calle">Nombre del domicilio</label>
                <input type="text" name="nmbdir" id="nmbdir'.$idcol.'" class="form-control" data-bs-parent="#acordendirecciones" h placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" value="'.$rowd['cd_nmbdir'].'" required="required" step="1" />
              </div>
              <div class="mb-5 col-12 col-md-7">
                <label for="calle">Calle</label>
                <input type="text" name="calle" id="calle'.$idcol.'" class="form-control" placeholder="La calle de tu domicilio" title="Se requiere un valor"  required="required" step="2" value="'.$rowd['cd_calle'].'" />
              </div>';
          echo '
              <div class="mb-5 col-6 col-md-2">
                <label for="nume">Número Exterior</label>
                <input type="text" name="nume" id="nume'.$idcol.'" class="form-control" placeholder="Número exterior" title="Se requiere un valor"  required="required" step="4" value="'.$rowd['cd_nume'].'" />
              </div>
              <div class="mb-5 col-6 col-md-3">
                <label for="numi">Número interior</label>
                <input type="text" name="numi" id="numi'.$idcol.'" class="form-control" placeholder="Número interior" title="Se requiere un valor" step="5" value="'.$rowd['cd_numi'].'" />
              </div>
              <div class="mb-5 col-12 col-md-5">
                <label for="colonia">Colonia</label>
                <input type="text" name="colonia" id="colonia'.$idcol.'" class="form-control" placeholder="La colonia de tu domicilio" title="Se requiere un valor"  required="required" step="6" value="'.$rowd['cd_colonia'].'" />
              </div>
              <div class="mb-5 col-12 col-md-2">
                <label for="cp">Código postal</label>
                <input type="text" name="cp" id="cp'.$idcol.'" class="form-control" placeholder="El CP de tu domicilio" title="Se requiere un valor"  required="required" step="7" value="'.$rowd['cd_cp'].'" />
              </div>
              <div class="mb-5 col-12 col-md-5">
                <label for="municipio">Ciudad</label>
                <input type="text" name="municipio" id="municipio'.$idcol.'" class="form-control" placeholder="La Ciudad de tu domicilio" title="Se requiere un valor"  required="required" step="8" value="'.$rowd['cd_municipio'].'" />
              </div>
              <div class="mb-5 col-12 col-md-4">
                <label for="estado">Estado</label>
                '.menu_select_db('estado','e_id','e_nmb',$rowd['cd_estado'],'estado','XXX',false, false, false, true,false).'
              </div>
              <div class="mb-5 col-12 col-md-3">
                <label for="pais">Pais</label>
                '.menu_select_db('paises','p_id','p_nmb',$rowd['cd_pais'],'pais','XXX',false, false, false, true,false).'
              </div>';
          echo '<div class="mb-5 col-12 col-md-3">
                <label for="tipod">Tipo de domicilio</label>';

              echo '<select name="tipodir" id="tipod'.$idcol.'" class="form-control" placeholder="Elige un tipo de domicilio" title="Se requiere un valor" required="required" step="3">';
              foreach($this->arrtipodom as $clave => $valor ){
                if($clave == $rowd['cd_tipodir']) $sel = ' selected '; else $sel = "";
                echo '<option value="'.$clave.'" '.$sel.'>'.$valor.'</option>';
              }
          echo '</select></div>
              <div class="mb-5 col-12 col-md-2">
                <center>
                  <label for="predeterminada">Predeterminada</label><br>
                  <input type="checkbox" name="predeterminada" id="predeterminada'.$idcol.'" data-bs-toggle="toggle" data-bs-on="Si" data-bs-off="No" data-bs-onstyle="success" data-bs-offstyle="danger" '.$pred.'>
                </center>
              </div>
            </div>
          </form>
        ';
  }
  echo '</div>
      </div>';
 echo '<input type="hidden" id="numdirs" value="'.$idcol.'">';

    echo $this->actividades();
 }

 function fiscales(){
    ?>
    <script>
      function showdir(){
        var tipod = document.getElementById("ndireccion").value;
        if(tipod == "X"){
          document.getElementById("dcalle").style.display = "";
          document.getElementById("calle").required = true;

          document.getElementById("dnume").style.display = "";

          document.getElementById("dnumi").style.display = "";

          document.getElementById("dcolonia").style.display = "";
          document.getElementById("colonia").required = true;

          document.getElementById("dcp").style.display = "";
          document.getElementById("cp").required = true;

          document.getElementById("dciudad").style.display = "";
          document.getElementById("municipio").required = true;

          document.getElementById("destado").style.display = "";

          document.getElementById("dpais").style.display = "";
          document.getElementById("pais").required = true;

          document.getElementById("dtipod").style.display = "";
          document.getElementById("tipod").required = true;
        }
        else{
          document.getElementById("dcalle").style.display = "none";
          document.getElementById("calle").required = false;

          document.getElementById("dnume").style.display = "none";

          document.getElementById("dnumi").style.display = "none";

          document.getElementById("dcolonia").style.display = "none";
          document.getElementById("colonia").required = false;

          document.getElementById("dcp").style.display = "none";
          document.getElementById("cp").required = false;

          document.getElementById("dciudad").style.display = "none";
          document.getElementById("municipio").required = false;

          document.getElementById("destado").style.display = "none";

          document.getElementById("dpais").style.display = "none";
          document.getElementById("pais").required = false;

          document.getElementById("dtipod").style.display = "none";
          document.getElementById("tipod").required = false;
        }
      }

    </script>
    <?php
    echo '
        <div class="modal fade" id="adddireccion" tabindex="-1" role="dialog" aria-labelledby="ModalFiscales" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header mb-2">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <label class="modal-title text-text-bold-600" id="ModalFiscales">Añadir Datos fiscales</label>
              </div>
                <form name="senddir" onsubmit="checkguardar1();" method="post" action="?modulo=clientes&accion=insertfiscliente&cliente='.$this->model->id.'" class="container">
                <div class="card-body row">
                <div class="mb-5 col-12 col-md-12 dirname13" >
                  <label for="calle">Dale un nombre a tus datos, para identificarlos mejor</label>
                  <input type="text" name="nmfiscales" id="nmfiscales" class="form-control" data-bs-parent="#acordenfiscales" h placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" value="" required="required" step="1" />
                </div>
                <div class="mb-5 col-12 col-md-6">
                  <label for="razons">Razón Social</label>
                  <input type="text" name="razonsocial" id="razonsocial" class="form-control" placeholder="La Razón Social de tu domicilio" title="Se requiere un valor"  required="required" value="" />
                </div>
                <div class="mb-5 col-12 col-md-6">
                  <label for="calle">RFC</label>
                  <input type="text" name="rfc" id="rfc" class="form-control" placeholder="La RFC de tu domicilio" title="Se requiere un valor"  required="required"value="" />
                </div>
                ';
            echo '<div class="mb-5 col-12 col-md-5">
                  <label for="pais">Correo electrónico principal</label>
                  <input type="email" name="correo1" id="correo1" class="form-control" placeholder="El Correo principal de envio" title="Se requiere un valor"  required="required" value="" />
                </div>';
            echo '<div class="mb-5 col-12 col-md-5">
                  <label for="pais">Correo electrónico secundario</label>
                  <input type="email" name="correo2" id="correo2" class="form-control" placeholder="Llena solo si quieres un segundo correo" title="Se requiere un valor" value="" />
                </div>
                <div class="mb-5 col-12 col-md-2">
                  <center>
                    <label for="predeterminada">Predeterminada</label><br>
                    <input type="checkbox" name="predeterminada" id="predeterminada" data-bs-toggle="toggle" data-bs-on="Si" data-bs-off="No" data-bs-onstyle="success" data-bs-offstyle="danger" checked>
                  </center>
                </div>';
            echo '<div class="mb-5 col-12 col-md-12">
                  <label for="direccion">Dirección</label>';
            $sqld = 'SELECT * FROM crm_direcciones WHERE cd_cliente = "'.$this->model->id.'"
                     ORDER BY cd_predeterminada DESC,cd_id';
            $resultd = setq($sqld);
            echo '<select name="direccion" class="form-control" onchange="showdir();" id="ndireccion">';
            echo '<option value="0">Registrar sin domicilio</option>';
            while($rowd = $resultd->fetch_array()){
              echo '<option value="'.$rowd['cd_id'].'">'.$rowd['cd_nmbdir'].' - '.$rowd['cd_calle'].' '.$rowd['cd_nume'].' '.$rowd['cd_colonia'].' '.$rowd['cd_municipio'].'</option>';
            }
            echo '<option value="X">Usar una dirección diferente</option>';
            echo '</select></div>';

            echo '<div class="mb-5 col-12 col-md-6" id="dcalle" style="display:none;">
                    <label for="calle">Calle</label>
                    <input type="text" name="calle" id="calle" class="form-control" placeholder="La calle de tu domicilio" title="Se requiere un valor" />
                  </div>
                  <div class="mb-5 col-6 col-md-3" id="dnume" style="display:none;">
                    <label for="nume">Número Exterior</label>
                    <input type="text" name="nume" id="nume" class="form-control" placeholder="Número exterior" title="Se requiere un valor"  />
                  </div>
                  <div class="mb-5 col-6 col-md-3" id="dnumi" style="display:none;">
                    <label for="numi">Número interior</label>
                    <input type="text" name="numi" id="numi" class="form-control" placeholder="Número interior" title="Se requiere un valor" />
                  </div>
                  <div class="mb-5 col-12 col-md-5" id="dcolonia" style="display:none;">
                    <label for="colonia">Colonia</label>
                    <input type="text" name="colonia" id="colonia" class="form-control" placeholder="La colonia de tu domicilio" title="Se requiere un valor"  />
                  </div>
                  <div class="mb-5 col-12 col-md-2" id="dcp" style="display:none;">
                    <label for="cp">Código postal</label>
                    <input type="text" name="cp" id="cp" class="form-control" placeholder="El CP de tu domicilio" title="Se requiere un valor"  />
                  </div>
                  <div class="mb-5 col-12 col-md-5" id="dciudad" style="display:none;">
                    <label for="municipio">Ciudad</label>
                    <input type="text" name="municipio" id="municipio" class="form-control" placeholder="La Ciudad de tu domicilio" title="Se requiere un valor"  />
                  </div>
                  <div class="mb-5 col-12 col-md-3" id="destado" style="display:none;">
                    <label for="estado">Estado</label>
                    '.menu_select_db('estado','e_id','e_nmb','','estado','XXX',false, false, false, false,false).'
                  </div>
                  <div class="mb-5 col-12 col-md-3" id="dpais" style="display:none;">
                    <label for="pais">Pais</label>
                    <input type="text" name="pais" id="pais" class="form-control" placeholder="El pais de tu domicilio" title="Se requiere un valor"   />
                  </div>
                  <div class="mb-5 col-12 col-md-6" id="dtipod" style="display:none;">
                    <label for="tipod">Tipo de domicilio</label>';

            echo '<select name="tipodir" id="tipod" class="form-control" placeholder="Elige un tipo de domicilio" title="Se requiere un valor" >';
                  foreach($this->arrtipodom as $clave => $valor ){
                    echo '<option value="'.$clave.'">'.$valor.'</option>';
                  }
            echo '</select></div>';

            echo '<div class="mb-5 col-12 col-md-2">
                  <center><button type="submit" class="btn btn-primary" id="sendform1">Agregar datos fiscales</button></center>
                </div>
              </div>
            </form>
            </div>
          </div>
        </div>';

        $atras = '
        <a href="?modulo=clientes&accion=edit&id='.$this->model->id.'" >
          <button type="button" class="btn btn-sm btn-warning mr-2 ml-2"><i class="fas fa-user"></i> Datos de cliente</button>
        </a>';
        $listado = '
        <a href="?modulo=clientes&accion=index" >
          <button type="button" class="btn btn-sm btn-info mr-2 ml-2"><i class="fas fa-th-list"></i> Listado de clientes</button>
        </a>
        ';
        $datos = '
        <button type="button" class="btn btn-sm btn-primary mr-2 ml-2" data-bs-toggle="modal" data-bs-target="#adddireccion"><i class="fas fa-plus"></i> Agregar Datos fiscales </button>
        ';

          toolbar($_GET['modulo'], $atras, '', $listado, $datos);
    echo '
          <div class="main-card mb-3">
            <div class="card-body row" >';
  ?>
  <script>
    function sendval(){
      document.getElementById("sologas").value = "1";
    }
    function iconcollapse(idcol){
      var numdir = document.getElementById("numdirs").value;

      for(var i=1; i <= numdir;i++){
        if(i == idcol){
          document.getElementById("update" + i).style.display = "";
        }
        else{
          document.getElementById("update" + i).style.display = "none";
        }
      }
    }

    function updatedir(numdir){
      document.getElementsByName("senddir" + numdir)[0].submit();
    }

    function newname(idcol){
      document.getElementById("divname"+idcol).removeAttribute("hidden");
      $("#nmfiscales"+idcol).focus();
    }
  </script>
 <?php
 echo '<div id="accordionWrapa1" role="tablist" aria-multiselectable="true" class="col-md-9">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title" id="basic-layout-form">Datos fiscales de de '.$this->model->alias.'</h4>
          </div>        ';

 $idcol = 0;
 while($rowd = $this->model->resultdir->fetch_array()){
   if($rowd['cf_predeterminada'] == 1){ $pred = "checked"; $ariae = '';} else{ $pred = ""; $ariae = "collapse in";}
   $idcol++;   

   if($idcol == 1){ $icondef = "icon-minus4"; $showup = ' '; } else{ $icondef = "fa fa-plus"; $showup = ' style="display:none;" '; }

  echo '  <div id="heading'.$idcol.'" class="card-header row border-top  mb-4">
            <button type="button" onclick="newname('.$idcol.');" class="btn btn-sm btn-secondary col-md-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Editar nombre de dirección"><i class="fas fa-pen-square"></i></button>
            <a data-bs-toggle="collapse" data-bs-parent="#accordionWrapa1" href="#accordion'.$idcol.'" aria-expanded="true" aria-controls="accordion1" class="card-title lead col-md-9" onclick="iconcollapse('.$idcol.')">
              <h6>'.$rowd['cf_nmfiscales'].'</h6>
            </a>
            <button type="button" onclick="updatedir('.$idcol.');" class="btn btn-sm btn-success" id="update'.$idcol.'" '.$showup.'><i class="fa fa-redo"></i> Actualizar</button>
          </div>
          <form name="senddir'.$idcol.'" method="post" action="?modulo=clientes&accion=updatefiscliente&cliente='.$this->model->id.'&idfis='.$rowd['cf_id'].'" id="accordion'.$idcol.'" role="tabpanel" aria-labelledby="heading'.$idcol.'" class="card-collapse '.$ariae.'" aria-expanded="true">
            <div class="card-body row">
              <div class="form-group col-12 col-md-12 dirname'.$idcol.'" id="divname'.$idcol.'" hidden>
                <label for="calle">Identificador de datos fiscales</label>
                <input type="text" name="nmfiscales" id="nmfiscales'.$idcol.'" class="form-control" data-bs-parent="#acordenfiscales" h placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" value="'.$rowd['cf_nmfiscales'].'" required="required" step="1" />
              </div>
              <div class="mb-5 col-12 col-md-7">
                <label for="razons">Razón Social</label>
                <input type="text" name="razonsocial" id="razonsocial'.$idcol.'" class="form-control" placeholder="La Razón Social de tu domicilio" title="Se requiere un valor"  required="required" value="'.$rowd['cf_razonsocial'].'" />
              </div>
              <div class="mb-5 col-12 col-md-5">
                <label for="calle">RFC</label>
                <input type="text" name="rfc" id="rfc'.$idcol.'" class="form-control" placeholder="La RFC de tu domicilio" title="Se requiere un valor"  required="required"value="'.$rowd['cf_rfc'].'" />
              </div>';
              echo '
              <div class="mb-5 col-12 col-md-12">
                <label for="estado">Dirección</label>';
            $sqldir = 'SELECT * FROM crm_direcciones WHERE cd_cliente = "'.$this->model->id.'"
                     ORDER BY cd_predeterminada DESC,cd_id';
            $resultdir = setq($sqldir);
            echo '<select name="direccion" class="form-control">';
            echo '<option value="0">Registro sin domicilio</option>';
            while($rowdir = $resultdir->fetch_array()){
              if($rowdir['cd_id'] == $rowd['cf_direccion']) $sel = "selected"; else $sel = "";

              echo '<option value="'.$rowdir['cd_id'].'" '.$sel.'>'.$rowdir['cd_nmbdir'].' - '.$rowdir['cd_calle'].' '.$rowdir['cd_nume'].' '.$rowdir['cd_colonia'].' '.$rowdir['cd_municipio'].'</option>';
            }
            echo '<option value="0">Usar una dirección diferente</option>';
            echo '</select></div>
              <div class="mb-5 col-12 col-md-5">
                <label for="pais">Correo electrónico principal</label>
                <input type="email" name="correo1" id="correo1'.$idcol.'" class="form-control" placeholder="El Correo principal de envio" title="Se requiere un valor"  required="required" value="'.$rowd['cf_correo1'].'" />
              </div>
              <div class="mb-5 col-12 col-md-5">
                <label for="pais">Correo electrónico secundario</label>
                <input type="email" name="correo2" id="correo2'.$idcol.'" class="form-control" placeholder="Si no quieres un correo secundario, deja este campo vacio" title="Se requiere un valor" value="'.$rowd['cf_correo2'].'" />
              </div>';
          echo '
              <div class="mb-5 col-12 col-md-2">
                <center>
                  <label for="predeterminada">Predeterminada</label><br>
                  <input type="checkbox" name="predeterminada" id="predeterminada'.$idcol.'" data-bs-toggle="toggle" data-bs-on="Si" data-bs-off="No" data-bs-onstyle="success" data-bs-offstyle="danger" '.$pred.'>
                </center>
              </div>
              <div class="mb-5 col-12 col-md-12">
                <label for="pais">Regimen</label>
                '.menu_select_db('cfdi_regimenfiscal','cr_regimen','cr_descripcion',$rowd['cf_regimen'],'regimen','XXX',true,false,false,true).'
              </div>
            </div>
          </form>
        ';
  }
  echo '<input type="hidden" id="numdirs" value="'.$idcol.'">';

  echo '</div>
      </div>';
 echo '<input type="hidden" id="numdirs" value="'.$idcol.'">';

    echo $this->actividades();

 }

 function expedientes(){

  echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
          $("#myT").DataTable({
              paging: true,
              scrollY: 400,
              language: {
                  url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
              }
          });
      });
      </script>';

  $datos = '
  <a href="?modulo=clientes&accion=edit&id='.$this->model->id.'" >
    <button type="button" class="btn btn-sm btn-warning mr-2 ml-2"><i class="fas fa-user"></i> Datos de cliente</button>
  </a>
  ';

  $listado = '
  <a href="?modulo=clientes&accion=index" >
    <button type="button" class="btn btn-sm btn-info mr-2 ml-2"><i class="fas fa-th-list"></i> Listado de clientes</button>
  </a>
  ';

  $guardar = '
  <button type="button" class="btn btn-sm btn-success mr-2 ml-2" onclick="validasend();"><i class="fas fa-paper-plane"></i> Guardar registro</button>
  ';

  toolbar($_GET['modulo'], $datos, '', $listado, $guardar);
    ?>
      <script>
        function validasend(){
          var conf = confirm("Te recomendamos llenar todos los campos del expediente comercial\n¿Deseas guardar el registro?");
          if(conf == true){
            document.segment.submit();
          }
        }
      </script>
    <?php
    echo '<div class="main-card mb-3 row">
            <div class="card-body col-md-9" >';
              /* echo '<form method="post" name="segment" action="?modulo=clientes&accion=setexpediente&cliente='.$this->model->id.'">';
    while($row = $this->model->resultexp->fetch_array()){
      $sqld = 'SELECT * FROM crm_segmentosd WHERE cd_segmento = "'.$row['cs_id'].'"';
      $resultd = setq($sqld);
      if($resultd->num_rows > 0){
        if($row['cs_requerido'] == "1") $required = ' required="required" '; else $required = "";
        echo '<div class="mb-5 card row">
                <div class="col-md-7">
                  <h4 class="card-title">'.$row['cs_nmb'].'</h4>
                  <p>'.$row['cs_descripcion'].'</p>
                </div>
                <div class="col-md-4">
                  <select name="valor'.$row['cs_id'].'" class="form-control" id="segmento'.$row['cs_id'].'" '.$required.'>
                    <option value="">Elige una opción</option>';
        $lleno = 0;
        while($rowd = $resultd->fetch_array()){
          if(busca($row['cs_id'],'crm_clientesegmento','cls_cliente = "'.$this->model->id.'" AND cls_segmento','cls_valor') == $rowd['cd_id']){
            $sele = 'selected';
            $lleno = 1;
          }
          else $sele = "";
          echo '<option value="'.$rowd['cd_id'].'" '.$sele.'>'.$rowd['cd_valor'].'</option>';
        }
        echo '    </select>
                </div>';
        echo '<div class="col-md-1">';

        if($lleno == "1") echo '<div class="alert alert-success"><i class="fas fa-check"></i></div>';
        else echo '<div class="alert alert-danger"><i class="fas fa-times"></i></div>';

        echo '</div>
              </div>';
      }
    }
    echo '<center>
            <button type="submit" class="btn btn-sm btn-success mr-2 ml-2" ><i class="fas fa-paper-plane"></i> Guardar registro</button>
          </center>';
    echo '</form>'; */
    echo '<form method="post" name="segment" action="?modulo=clientes&accion=setexpediente&cliente='.$this->model->id.'">
    <div class="card mt-3">
    <div class="card-body">
    <div class="">
    <div class="table-responsive">
      <table class="mb-0 table table-hover" id="myT">
          <thead class="bg-light-blue bg-darken-2 ">
          <tr>
            <th>Nombre Segmento</th>
            <th>Descripcion</th>
            <th>Asignar</th>
            <th>Estatus</th>
          </tr>
        </thead>
        <tbody>';
          while($row = $this->model->resultexp->fetch_array()){
            $sqld = 'SELECT * FROM crm_segmentosd WHERE cd_segmento = "'.$row['cs_id'].'"';
            $resultd = setq($sqld);
            if($resultd->num_rows > 0){
              if($row['cs_requerido'] == "1") $required = ' required="required" '; else $required = "";
              echo '<tr>
                <td>'.$row['cs_nmb'].'</td>
                <td>'.$row['cs_descripcion'].'</td>
                <td>
                  <select name="valor'.$row['cs_id'].'" class="form-control" id="segmento'.$row['cs_id'].'" '.$required.'>
                    <option value="">Elige una opción</option>';
                    $lleno = 0;
                    while($rowd = $resultd->fetch_array()){
                      if(busca($row['cs_id'],'crm_clientesegmento','cls_cliente = "'.$this->model->id.'" AND cls_segmento','cls_valor') == $rowd['cd_id']){
                        $sele = 'selected';
                        $lleno = 1;
                      }
                      else $sele = "";
                      echo '<option value="'.$rowd['cd_id'].'" '.$sele.'>'.$rowd['cd_valor'].'</option>';
                    }
                  echo'</select>
                </td>';
                echo '<td>';
                  if($lleno == "1") echo '<div class="green font-size-small">Asignado <i class="icon-check"></i></div>';
                  else echo '<div class="red font-size-small">Sin registro <i class="icon-times"></i></div>';
                echo '</td>
              </tr>';
            }
          }
        echo '</tbody>
      </table>
    </div>
    </div>
    </div>
    </div>';
    /*echo '<center>
      <button type="submit" class="btn btn-success mr-2 ml-2" ><i class="icon-send-o"></i> Guardar registro</button>
    </center>'; */
  echo '</form>';
    echo '</div>';

    echo $this->actividades();
 }

function precios($categoria){

  $datos = '
  <a href="?modulo=clientes&accion=edit&id='.$this->model->id.'" >
    <button type="button" class="btn btn-sm btn-warning mr-2 ml-2"><i class="fas fa-user"></i> Datos de cliente</button>
  </a>
  ';

  $listado = '
  <a href="?modulo=clientes&accion=index" >
    <button type="button" class="btn btn-sm btn-info mr-2 ml-2"><i class="fas fa-th-list"></i> Listado de clientes</button>
  </a>
  ';

  $guardar = '
  <button type="button" class="btn btn-sm btn-success mr-2 ml-2" onclick="validasend();"><i class="fas fa-paper-plane"></i> Guardar registro</button>
  ';

  $filtro = '<form method="post" class="pt-1">
  Filtrar por categoria '.menu_select_db('categorias','cat_id','cat_nmb',$categoria,'categoria','cat_empresa = "'.$_SESSION['emp'].'"',false,false,true,false,'Mostrar todos');
  $filtro .= '</form>';
  toolbar($_GET['modulo'], $datos, $filtro, $listado, $guardar);
/* 
  echo '<div class="main-card mb-3 row">
          <div class="card-body col-md-10" >
        <form method="post" class="pt-1">
            Filtrar por categoria '.menu_select_db('categorias','cat_id','cat_nmb',$categoria,'categoria','cat_empresa = "'.$_SESSION['emp'].'"',false,false,true,false,'Mostrar todos');
  echo '</form>'; */
echo '
<div class="main-card mb-3 row">
<div class="card-body col-md-9">';
  echo '
  <div class="card mt-3">
      <div class="card-body">
      <div class="">';
  echo '<div class="table-responsive">
        <form action="?modulo=clientes&accion=preciocl&cliente='.$this->model->id.'&categoria='.$categoria.'" method="post" name="precioscli">
        <table class="mb-0 table table-hover" id="myTable">
        <thead class="bg-light-blue bg-darken-2">
          <tr>
            <th>Nombre</th>
            <th>Categoria</th>
            <th width="12%">Costo</th>
            <th width="12%">Precio Lista</th>
            <th>Modalidad</th>
            <th>+/-</th>
            <th width="10%">Valor</th>
            <th width="12%">Precio cliente</th>
          </tr>
        </thead>';
  ?>
    <script>
        function validasend(){
          var conf = confirm("La lista de precios del cliente se actualizará\n¿Deseas continuar?");
          if(conf == true){
            document.precioscli.submit();
          }
        }
      function calcular(idart){
        if(document.getElementById("modo" + idart).checked) var modo = "%";
        else var modo = "$";

        if(document.getElementById("masmenos" + idart).checked) var masmenos = "+";
        else var masmenos = "-";

        var valor = document.getElementById("valor" + idart).value;
        if(valor == undefined) valor = 0;
        valor = parseFloat(valor);

        var precio = document.getElementById("precio" + idart).value;
        if(precio == undefined) precio = 0;
        precio = parseFloat(precio);

        if(modo == "%"){
          var diferencia = precio*(valor/100);
        }
        else{
          var diferencia = valor;
        }

        if(masmenos == "+"){
          var preciofin = precio+diferencia;
        }
        else{
          var preciofin = precio-diferencia;
        }

        document.getElementById("preciofin" + idart).value = preciofin.toFixed(2);
      }
    </script>
  <?php
  $i = 0;
  while($row = $this->model->resultprice->fetch_array()){
    $i++;
    $sql = 'SELECT cp_modo,cp_masmenos,cp_valor,cp_precio FROM clientes_precios
            WHERE cp_articulo = "'.$row['a_id'].'" AND cp_cliente = "'.$this->model->id.'"';
    $result = setq($sql);
    if($result->num_rows > 0){
      list($modo,$masomenos,$valor,$preciofinal) = $result->fetch_array();
    }
    else{
      $modo = "%";
      $masomenos = "+";
      $valor = 0;
      $preciofinal = $row['ap_precio'];
    }

    if($modo == "%") $chmodo = "checked"; else $chmodo = "";
    if($masomenos == "+") $chmame = "checked"; else $chmame = "";

    if(!$row['ap_costo']) $row['ap_costo'] = 0;

    echo '<input type="hidden" name="articulo'.$i.'" value="'.$row['a_id'].'">';
    echo '<tr class="mt-1 mb-1">
            <td>'.$row['a_nmb'].'</td>
            <td>'.$row['cat_nmb'].'</td>
            <td><input type="number" class="form-control number-align" min="0"- max="'.$row['ap_costo'].'" value="'.round($row['ap_costo'],2).'" step="0.01" name="costo'.$row['a_id'].'" readonly /></td>
            <td><input type="number" class="form-control number-align" min="0" max="'.$row['ap_precio'].'" id="precio'.$row['a_id'].'" value="'.round($row['ap_precio'],2).'" step="0.01" name="precio'.$row['a_id'].'" readonly /></td>
            <td><input type="checkbox" onchange="calcular('.$row['a_id'].');" id="modo'.$row['a_id'].'" name="modo'.$row['a_id'].'" '.$chmodo.' class="flipswitchpodi" /></td>
            <td><input type="checkbox" onchange="calcular('.$row['a_id'].');" name="masmenos'.$row['a_id'].'" id="masmenos'.$row['a_id'].'" '.$chmame.' class="flipswitchmame" /></td>
            <td><input type="number" onchange="calcular('.$row['a_id'].');" class="form-control number-align" min="0" max="99999" value="'.$valor.'" step="0.01" id="valor'.$row['a_id'].'" name="valor'.$row['a_id'].'" onfocus="this.select();" /></td>
            <td><input type="number" class="form-control number-align" min="0" max="'.(99999+$row['ap_precio']).'" value="'.round($preciofinal,2).'" step="0.01" id="preciofin'.$row['a_id'].'" name="preciofin'.$row['a_id'].'" readonly /></td>
          </tr>';
  }
  echo '</table>
        <input type="hidden" name="round" value="'.$i.'" />
        </form>
        </div>
        </div>
        </div>
        </div>
        </div>';
  echo $this->actividades();

  echo '
  <script>
      $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          }
      });
    </script>';

 }

 function comodato(){
    echo '
        <div class="modal fade" id="addcomodato" tabindex="-1" role="dialog" aria-labelledby="ModalComodato" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header mb-2">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <label class="modal-title text-text-bold-600" id="Comodato">Añadir Comodato</label>
              </div>
                <form name="sendcomotato" method="post" action="?modulo=clientes&accion=setcomodato&cliente='.$this->model->id.'" class="container">
                <div class="card-body row">
                <div class="col-6 col-md-9" >
                  <label for="calle">Elige un activo para asignar</label>';
            $sql = 'SELECT * FROM activo_almacen INNER JOIN activos ON a_id = aa_activo
                    WHERE aa_estatus IN ("L","N") ORDER BY aa_almacen';
            $result = setq($sql);
            echo '<select name="activo" class="form-control" required>';
            while($row = $result->fetch_array()){
              echo '<option value="'.$row['aa_id'].'">'.$row['a_nmb'].' - '.$row['aa_noserie'].'</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div class="col-6 col-md-3">
                    <label for="calle">Modalidad</label><br>
                    <input type="checkbox" class="flipswitchcomod" name="modo" />
                  </div>';
            echo '<div class="col-6 col-md-3">
                    <label for="calle">Fecha de asignacion</label>
                    <input type="date" name="fini" value="'.date('Y-m-d').'" class="form-control" required />
                  </div>';
            echo '<div class="col-6 col-md-3">
                    <label for="calle">Fecha de recolección (Final)</label>
                    <input type="date" name="ffin" value="'.date('Y-m-d',strtotime('+ 1 month')).'" class="form-control" required />
                  </div>';
            echo '<div class="col-6 col-md-3">
                    <label for="calle">Importe</label>
                    <input type="number" min="0" max="999999" step="0.01" name="importe" value="5500" class="form-control" required />
                  </div>';
            echo '<div class="col-6 col-md-3">
                    <label for="calle">Autorenovable</label><br>
                    <input type="checkbox" class="flipswitchsn" name="renovable" />
                  </div>';

            echo '<div class="mb-5 col-12 col-md-12">
                  <center><button type="submit" class="btn btn-primary" >Asignar comodato</button></center>
                </div>
              </div>
            </form>
            </div>
          </div>
        </div>';


    $datos = '
    <a href="?modulo=clientes&accion=edit&id='.$this->model->id.'" >
      <button type="button" class="btn btn-sm btn-warning mr-2 ml-2"><i class="fas fa-user"></i> Datos de cliente</button>
    </a>
    ';

    $listado = '
    <a href="?modulo=clientes&accion=index" >
      <button type="button" class="btn btn-sm btn-info mr-2 ml-2"><i class="fas fa-th-list"></i> Listado de clientes</button>
    </a>
    ';

    $anadir = '
    <button type="button" class="btn btn-sm btn-primary mr-2 ml-2" data-bs-toggle="modal" data-bs-target="#addcomodato"><i class="fas fa-plus"></i> Añadir comodatos </button>
    ';

    toolbar($_GET['modulo'], $datos, '', $listado, $anadir);

  echo '
    <div class="card mt-3">
        <div class="card-body">
        <div class="">';
  echo '<div class="table-responsive">
        <table class="mb-0 table table-hover" id="myTable">
          <thead class="bg-light-blue bg-darken-2 ">
              <tr><th colspan="7">Activos asignados a '.$this->model->alias.'</th></tr>
              <tr>
                <th>Activo</th>
                <th>No. Serie</th>
                <th>Fecha asignación</th>
                <th>Fecha vencimiento</th>
                <th>Modalidad de comodato</th>
                <th>Importe</th>
                <th>Estatus</th>
              </tr>
            </thead>';

  while($row = $this->model->result->fetch_array()){
    if($row['ac_estatus'] == "A"){
      $colest = "success";
      $nmbest = "ASIGNADO";
    }
    else{
      $colest = "primary";
      $nmbest = "RECOGIDO";
    }
    echo '<tr>
            <td>'.$row['a_nmb'].'</td>
            <td>'.$row['aa_noserie'].'</td>
            <td>'.$row['ac_fechaini'].'</td>
            <td>'.$row['ac_ffin'].'</td>
            <td>'.$this->modalidades[$row['ac_modalidad']].'</td>
            <td>'.$row['ac_importe'].'</td>
            <td class="bg-'.$colest.'">'.$nmbest.'</td>
          </tr>';
  }
    echo '
    </table>
      </div>';

    echo '
    <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            }
        });
      </script>';

 }

 function actividades(){
  if($_GET['accion'] == "edit") $alerta = 'class="alert alert-info"'; else $alerta = "";
  if($_GET['accion'] == "bitacora") $alertbtc = 'class="alert alert-info"'; else $alertbtc = "";
  if($_GET['accion'] == "direcciones") $alertdir = 'class="alert alert-info"'; else $alertdir = "";
  if($_GET['accion'] == "precios") $alertprc = 'class="alert alert-info"'; else $alertprc = "";
  if($_GET['accion'] == "fiscales") $alertfisc = 'class="alert alert-info"'; else $alertfisc = "";
  if($_GET['accion'] == "expediente") $alertexp = 'class="alert alert-info"'; else $alertexp = "";

  echo '<div class="col-md-3">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title" id="basic-layout-form">Actividades</h4>';
            /* echo '<a class="heading-elements-toggle"><i class="fas fa-ellipsis font-medium-3"></i>Regresar </a>
            <div class="heading-elements">
              <ul class="list-inline mb-0">
                <li><a data-action="collapse"><i class="fas fa-minus"></i></a></li>
              </ul>
            </div>'; */
        echo '</div>';

  echo '<div class="card-body">
        <div class="card-block">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12 col-sm-12">
              <table class="table table-striped table-bordered">
                <thead class="bg-bs-primary bg-darken-2">
                  <tr><th>Elije una actividad</th></tr>
                <thead>
                  <tbody>
                    <tr>
                    <th '.$alerta.'><a href="?modulo=clientes&accion=edit&id='.$this->model->id.'">
                        <i class="fas fa-home warning"></i> Datos del cliente </a>
                      </th>
                    </tr>
                    <tr '.$alertdir.'>
                      <th><a href="?modulo=clientes&accion=direcciones&cliente='.$this->model->id.'">
                        <i class="fas fa-home warning"></i> Direcciones </a>
                      </th>
                    </tr>
                    <tr '.$alertfisc.'>
                      <th><a href="?modulo=clientes&accion=fiscales&cliente='.$this->model->id.'">
                        <i class="fas fa-list-alt warning"></i> Datos fiscales</th>
                      </a></tr>
                    <tr '.$alertfisc.'>
                      <th><a href="?modulo=clientes&accion=expediente&cliente='.$this->model->id.'">
                      <i class="fas fa-folder"></i> Expediente</th>
                    </a></tr>';
                    /* echo '<tr>
                      <th '.$alertbtc.'>
                      <a target="_BLANK" href="?modulo=clientes&accion=bitacora&id='.$this->model->id.'">
                        <i class="fas fa-list-alt warning"></i> Ver bitácora
                      </a>
                    </tr>';
                    echo '<tr>
                      <th>
                        <a href="?modulo=clientes&accion=reasignacion&id='.$this->model->id.'">
                          <i class="fas fa-users"></i> Reasignaciones
                        </a>
                      </th>
                    </tr>';
                    echo '<tr '.$alertexp.'>
                      <th>
                        <a href="?modulo=clientes&accion=expediente&cliente='.$this->model->id.'">
                          <i class="fas fa-folder-open warning"></i> Ver expediente</th>
                        </a>
                      </th>
                    </tr>
                    <tr '.$alertprc.'>
                      <th>
                      <a href="?modulo=clientes&accion=precios&cliente='.$this->model->id.'">
                        <i class="fas fa-tags warning"></i> Lista de precios
                      </a>
                    </tr>
                    <tr>
                      <th>
                      <a href="?modulo=remisiones&accion=edit&cliente='.$this->model->id.'">
                        <i class="fas fa-cart-plus warning"></i> Remisión
                      </a>
                    </tr>
                    <tr>
                      <th>
                      <a href="?modulo=clientes&accion=comodato&cliente='.$this->model->id.'">
                        <i class="fas fa-hand-paper warning"></i> Comodato
                      </a>
                    </tr>'; */

                  echo '</tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>';
  echo '</div>';
}

function reasignacion(){

  $atras = '
  <a href="?modulo=clientes&accion=index">
    <button class="mb-2 mr-2 btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>Regresar</button>
  </a>';

  toolbar($_GET['modulo'], $atras);
  ?>
  <script>
    function sendval(){
      document.getElementById("sologas").value = "1";
    }

    function enviarform(){
      var form= document.getElementById("insasignacion");
      form.submit();
    }
  </script>
  <?php

$vendedor  = busca($this->model->uregistro, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
echo '
<div class="row mt-5">
  <div class="col-md-9">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title" id="basic-layout-form">Reasignación del cliente '.$this->model->nmb.' '.$this->model->apellidos.'</h4>
        <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
        <h5 class="card-title"> Asignado al vendedor:  <b>'.$vendedor.'</b></h5>
        <div class="heading-elements">
          <ul class="list-inline mb-0">
            <li><a data-bs-action="collapse"><i class="icon-minus4"></i></a></li>
            <li><a data-bs-action="expand"><i class="icon-expand2"></i></a></li>
            <li><a data-bs-action="close"><i class="icon-cross2"></i></a></li>
          </ul>
        </div>
      </div>';
echo '<form autocomplete="off" name="insasignacion" id="insasignacion" action="?modulo=clientes&accion=insasignacion&id='.$this->model->id.'" method="post" >
        <div class="card-body in">
          <div class="card-block">
            <div class="form-body">
              <div class="col-12 col-md-4">
                <label>Reasignar a:</label>
                <select class="form-control" name="asignar" onchange="enviarform();">';
                  $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" && u_grupo = "VENTAS"';
                  $result = setq($sql);
                  while($row = $result -> fetch_array()){
                    if($this->model->uregistro == $row['u_id']) $sel = "selected";
                    else $sel = "";
                    echo '<option value="'.$row['u_id'].'" '.$sel.'>'.$row['u_nmb'].' '.$row['u_apellidos'].'</option>';
                  }
                echo '</select>
              </div>  
            </div>
          </div>
        </div>
      </form>
';
$sql = 'SELECT  * FROM crm_clientes_reasignacion WHERE cr_cliente = "'.$this->model->id.'"'; 
$result = setq($sql);
echo '<div class="col-12">
<center><h2>Historial de asignaciones</h2>
  <div class="col-10">
  <table class="table">                    
    <thead class="bg-primary text-white">
      <tr>
        <th>Pertenecia a</th>          
        <th>Reasignado a</th>
        <th>Fecha de reaginación</th>
        <th>Usuario que reasigno</th>
      </tr>
      <tbody>';
      if($result -> num_rows > 0){
        while($row = $result -> fetch_array()){
          echo '<tr>
            <td>'.busca($row['cr_pertenecia'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').' </td>
            <td>'.busca($row['cr_vendedor'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').' </td>
            <td>'.fecha_formato($row['cr_freasigna'], true, false).' </td>
            <td>'.busca($row['cr_uasigna'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').' </td>
          </tr>';
        }
      } else {
        echo '<tr>
          <td colspan="4" class="bg-success"><center> Sin reasignaciones </center></td>
        </tr>';
      }
      echo '</tbody>                  
    </thead>
  </table>
  </div>
</center>
</div>
</div>
</div>
';


  echo $this->actividades();

}

function expediente(){

  $atras = '
  <a href="?modulo=clientes&accion=index">
    <button class="mb-2 mr-2 btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i>Regresar</button>
  </a>';

  toolbar($_GET['modulo'], $atras);
  ?>
  <script>
    function sendval(){
      document.getElementById("sologas").value = "1";
    }

    function enviarform(){
      var form= document.getElementById("insasignacion");
      form.submit();
    }
  </script>
  <style>
    /* Ocultamos el checkbox original */
    .custom-checkbox {
      position: relative;
      display: inline-block;
      cursor: pointer;
      padding-left: 35px;
      margin: 10px;
      font-size: 16px;
      user-select: none;
    }

    .custom-checkbox input {
      position: absolute;
      opacity: 0;
      cursor: pointer;
    }

    /* Cuadro base del checkbox */
    .checkmark {
      position: absolute;
      top: 0;
      left: 0;
      height: 25px;
      width: 25px;
      border: 2px solid #ccc;
      border-radius: 4px;
      background-color: #fff;
      transition: background 0.3s, border 0.3s;
      text-align: center;
      line-height: 25px;
      font-weight: bold;
    }

    input:disabled + .checkmark {
       background-color: #e0e0e0; /* Gris claro */
      border-color: #aaa;        /* Opcional: cambia también el borde */
      cursor: not-allowed;
    }

    /* Estilo cuando está seleccionado (Autorizar) */
    .custom-checkbox.autorizar input:checked ~ .checkmark {
      background-color: #4CAF50; /* verde */
      border-color: #4CAF50;
      color: white;
      content: "✓";
    }

    .custom-checkbox.autorizar input:checked ~ .checkmark::after {
      content: "✓";
    }

    /* Estilo cuando está seleccionado (Denegar) */
    .custom-checkbox.denegar input:checked ~ .checkmark {
      background-color: #f44336; /* rojo */
      border-color: #f44336;
      color: white;
    }

    .custom-checkbox.denegar input:checked ~ .checkmark::after {
      content: "✕";
    }

    /* El ::after para mostrar el ícono */
    .custom-checkbox .checkmark::after {
      position: absolute;
      top: 0;
      left: 0;
      width: 25px;
      height: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }
  </style>
  <?php

$vendedor  = busca($this->model->uregistro, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
echo '
<div class="row mt-5">
  <div class="col-md-9">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title" id="basic-layout-form">Expediente del cliente '.$this->model->nmb.' '.$this->model->apellidos.'</h4>
        <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
        <div class="heading-elements">
          <ul class="list-inline mb-0">
            <li><a data-bs-action="collapse"><i class="icon-minus4"></i></a></li>
            <li><a data-bs-action="expand"><i class="icon-expand2"></i></a></li>
            <li><a data-bs-action="close"><i class="icon-cross2"></i></a></li>
          </ul>
        </div>
      </div>
      <div class="container mt-5">
        <h2 class="mb-4">Añadir documento</h2>
        <form action="?modulo=clientes&accion=insertdocumento&cliente='.$_GET['cliente'].'" method="post" enctype="multipart/form-data">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="descripcion" class="form-label">Descripción:</label>
              <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripción del archivo" required>
            </div>

            <div class="col-md-4">
              <label for="archivo" class="form-label">Archivo:</label>
              <input type="file" class="form-control" id="archivo" name="archivo" required>
            </div>

            <div class="col-md-3">
              <label for="tipo" class="form-label">Tipo de documento:</label>
              <select class="form-control" id="tipo" name="tipo" required>
                <option value="1">Documento del cliente</option>
                <option value="2">Documento interno</option>
              </select>
            </div>

            <div class="col-md-1 mt-11">
              <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
          </div>

          
        </form>
      </div>
      <div class="row mt-10">';
$sql = 'SELECT  * FROM crm_documentos WHERE cd_cliente = "'.$this->model->id.'" AND cd_tipo = "1"'; 
$result = setq($sql);
echo '<div class="col-12 col-md-6">
<center><h2>Documentos del cliente</h2>
  <div class="col-11">
  <table class="table">                    
    <thead class="bg-primary text-white">
      <tr>
        <th>Archivo</th>
        <th>Descripción</th>          
        <th>Aceptar</th>
        <th>Denegar</th>
      </tr>
      <tbody>';
      if($result -> num_rows > 0){
        while($row = $result -> fetch_array()){
          $dis = '';
          $check = '';
          $rec = '';
          $accionau = 'onclick="autorizardoc('.$row['cd_id'].', '.$_GET['cliente'].')"';
          $accionde = 'onclick="denegardoc('.$row['cd_id'].', '.$_GET['cliente'].')"';
          if($row['cd_estatus'] == "A"){
            $dis ='disabled';
            $check = 'checked';
            $accionau = '';
          } else if($row['cd_estatus'] == "C"){
            $dis ='disabled';
            $accion = 'background-color=#ffffff';
            $rec = 'checked';
            $accionde = '';
          }
          echo '<tr>
            <td><a target="_BLANK" href="docs/clientes/'.$row['cd_archivo'].'" class="btn btn-secondary"><i class="fas fa-eye"></i>Ver</a> </td>
            <td>'.$row['cd_descripcion'].' </td>
            <td><label class="custom-checkbox autorizar">
                <input type="checkbox" name="autorizacion" '.$dis.' '.$check.' '.$accionau.'>
                <span class="checkmark"></span>
              </label>
            </td>
            <td><label class="custom-checkbox denegar">
              <input type="checkbox" name="autorizacion"  '.$dis.' '.$rec.' '.$accionde.'>
              <span class="checkmark"></span>
            </label>
            </td>
          </tr>';
        }
      } else {
        echo '<tr>
          <td colspan="4" class="bg-success"><center> Sin documentos registrados </center></td>
        </tr>';
      }
      echo '</tbody>                  
    </thead>
  </table>
  </div>
</center>
</div>';

$sql = 'SELECT * FROM crm_documentos WHERE cd_cliente = "'.$this->model->id.'" AND cd_tipo = "2"'; 
$result = setq($sql);
echo '<div class="col-12 col-md-6">
<center><h2>Documentos internos</h2>
  <div class="col-11">
  <table class="table">                    
    <thead class="bg-primary text-white">
      <tr>
        <th>Archivo</th>
        <th>Descripción</th>          
        <th>Aceptar</th>
        <th>Denegar</th>
      </tr>
      <tbody>';
      if($result -> num_rows > 0){
        while($row = $result -> fetch_array()){
          $dis = '';
          $check = '';
          $rec = '';
          $accionau = 'onclick="autorizardoc('.$row['cd_id'].', '.$_GET['cliente'].')"';
          $accionde = 'onclick="denegardoc('.$row['cd_id'].', '.$_GET['cliente'].')"';
          if($row['cd_estatus'] == "A"){
            $dis ='disabled';
            $check = 'checked';
            $accionau = '';
          } else if($row['cd_estatus'] == "C"){
            $dis ='disabled';
            $accion = 'background-color=#ffffff';
            $rec = 'checked';
            $accionde = '';
          }
          echo '<tr>
            <td><a target="_BLANK" href="docs/clientes/'.$row['cd_archivo'].'" class="btn btn-secondary"><i class="fas fa-eye"></i>Ver</a> </td>
            <td>'.$row['cd_descripcion'].' </td>
            <td><label class="custom-checkbox autorizar">
                <input type="checkbox" name="autorizacion" '.$dis.' '.$check.' '.$accionau.'>
                <span class="checkmark"></span>
              </label>
            </td>
            <td><label class="custom-checkbox denegar">
              <input type="checkbox" name="autorizacion"  '.$dis.' '.$rec.' '.$accionde.'>
              <span class="checkmark"></span>
            </label>
            </td>
          </tr>';
        }
      } else {
        echo '<tr>
          <td colspan="4" class="bg-success"><center> Sin documentos registrados </center></td>
        </tr>';
      }
      echo '</tbody>                  
    </thead>
  </table>
  </div>
</center>
</div>';

echo '</div></div>
</div>
';
?>
  <script>
    
      function autorizardoc(id, cliente){
        const confirmacion = confirm("¿Estás seguro de autorizar este documento?");
        if (confirmacion) {
          window.location.href = '?modulo=clientes&accion=autorizardoc&cliente='+cliente+'&id='+id;
        }
      }

      function denegardoc(id, cliente){
        const confirmacion = confirm("¿Estás seguro de denegar este documento?");
        if (confirmacion) {
          window.location.href = '?modulo=clientes&accion=denegardoc&cliente='+cliente+'&id='+id;
        }
      }

  </script>
<?php

  echo $this->actividades();

}

}
?>