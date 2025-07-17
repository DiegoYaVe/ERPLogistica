<?php
//ini_set('display_errors', 1);
class clientes{
  var $model;
  var $view;
  function clientes(){
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

    $grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
    if($grupo == "USER") $_REQUEST['almacen'] = busca($_SESSION['uid'],'usuarios','u_id','u_almacen');
    if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
    $this->model->result($_REQUEST['nmb'],$estatus,$_REQUEST['almacen'],$_REQUEST['page']);
    $this->view = new viewclientes($this->model);
    $this->view->browse($_REQUEST['nmb'],$estatus,$_REQUEST['almacen'],$_REQUEST['page']);
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
    if(isset($_POST['id'])){
      $id= $_POST['id'];
      $descb = "ACTUALIZACIÓN DE DATOS DE CLIENTE";
    }
    else{
      $id = getmax("c_id","crm_clientes");
      $descb = "REGISTRO BÁSICO DE CLIENTE";
    }

    $this->model->setdata($id,$_POST['nmb'],$_POST['apellidos'],$_POST['alias'],$_POST['correo1'],$_POST['telefono1'],$_POST['telefono2'],$_POST['obs'],$_POST['almacen'],$_POST['precio']);
    $ok = $this->model->setuser();

    if($ok) bitacora($this->model->id,"CLIENTES",$descb,NULL,NULL);

    redirect("?modulo=clientes&accion=edit&id=".$this->model->id.'&ok='.$ok);
  }

  function bitacora(){
    $cliente = busca($_GET['cliente'],'crm_clientes','c_id','c_nmb');
    $fini = busca($cliente,'crm_bitacora','cb_cliente','MIN(cb_fecha)');
    if(!$fini) $fini = date('Y')."-01-01";

    $ffin = date('Y-m-d',strtotime('last day of this month'));

    if(!isset($_GET['cliente'])) $_GET['cliente'] = NULL;
    if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = $fini;
    if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = $ffin;

    $this->model->select($_GET['cliente']);
    $this->model->resultbitacora($_GET['cliente'],$_REQUEST['fini'],$ffin);
    $this->view = new viewclientes($this->model);
    $this->view->bitacora($_GET['cliente'],$_REQUEST['fini'],$ffin);
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

    redirect('?modulo=clientes&accion=direcciones&cliente='.$_GET['cliente'].'&lastid='.$this->model->id);
  }

  function insertcliente(){
    $dir = getmax('cd_id','crm_direcciones');

    $this->model->setdataDir($dir,$_GET['cliente'],$_POST['nmbdir'],$_POST['calle'],$_POST['tipodir'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['cp'],$_POST['municipio'],$_POST['estado'],$_POST['pais']);
    $this->model->insertdircliente();

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
    $idfisc = getmax('cd_id','crm_direcciones');

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
    $this->model->resultexpediente();
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
  }

  function result($nmb,$estatus,$almacen,$page){
    $bloque = 50;
    $sql = 'SELECT * FROM crm_clientes WHERE  c_empresa = "'.$_SESSION['emp'].'" AND c_estatus = "'.$estatus.'" ';
    if($nmb) $sql.= ' AND c_nmb LIKE "%'.$nmb.'%" OR c_alias LIKE "%'.$nmb.'%"';
    if($almacen) $sql.= ' AND c_almacen = "'.$almacen.'" ';
    $sql.=' ORDER BY c_id ASC ';
    $result = setq($sql);
    if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
    $this->result = setq($sql);
    $this->resultt = setq($sql);
  }

  function setdata($id,$nmb,$apellidos,$alias,$email,$telefono1,$telefono2,$obs,$almacen,$precio){
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
}

class viewclientes{
  var $model;
  function viewclientes($model){
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

  function browse($nmb,$estatus,$almacen,$page){
    /* $numres = 50;
    $nr = $this->model->resultt->num_rows;
    $np = $nr/$numres; */

    if($estatus == "A") $chest = "checked"; else $chest = "";

    echo '<div class="page-title-actions"> <div class="d-inline-block dropdown">';

    echo '</div>';
    echo '<form autocomplete="off" action="?modulo=clientes&accion=index" method="post" onsubmit="return checkSubmitenviar();" class="row form-inline mb-1">
          <div class="mb-5 col-md-2 p-0">
          <br>
            <a href="?modulo=clientes&accion=edit" >
              <button type="button" class="btn btn-primary mr-2 ml-2"><i class="fa fa-plus"></i> Nuevo </button>
            </a>
          </div>
          <div class="mb-5 col-md-3">
            <label class="mr-sm-2"><b>Filtrar por nombre: </b></label>
            <input type="text" name="nmb" value="'.$nmb.'" size="30" placeholder="Nombre o fragmento del cliente" onfocus="this.select();" autofocus class="form-control mr-2">
          </div>
          <div class="mb-5 col-md-3">
            <label class="mr-sm-2"><b>Filtrar por almacen: </b></label>';

    $sqlal = 'SELECT * FROM almacenes WHERE a_empresa = "'.$_SESSION['emp'].'"';
    if(busca($_SESSION['uid'],'usuarios','u_id','u_grupo') == "USER"){
      $sqlal.= ' AND a_id = "'.busca($_SESSION['uid'],'usuarios','u_id','u_almacen').'"';
    }
    $resultal = setq($sqlal);
    echo '<select name="almacen" class="form-control" onchange="submit();">';
    echo '<option value="">Todos los almacenes</option>';
    while($row = $resultal->fetch_array()){
      if($almacen == $row['a_id']) $sele = 'selected'; else $sele = "";
      $clientealmacen = busca($row['a_id'],'crm_clientes','c_estatus = "'.$estatus.'" AND c_empresa = "'.$_SESSION['emp'].'" AND c_almacen','COUNT(*)');

      echo '<option value="'.$row['a_id'].'" '.$sele.'>'.$row['a_nmb'].' ('.$clientealmacen.')</option>';
    }
    echo '</select>';

    echo '</div>
          <div class="mb-5 col-md-3">
            <label class="mr-sm-2"><b>Filtrar por estatus: </b></label><br>
            <input type="checkbox" name="estatus" '.$chest.' data-toggle="toggle" id="toogle-estatus" data-size="mini" data-onstyle="success" data-offstyle="danger" data-on="Activos" data-off="Inactivos" onchange="submit();" >
          </div>
    </form>';

    echo '</div>';

    echo '</div>
    <div class="main-card mb-3">
    <div class="card-body row" >';
    //overflow-x: clip; overflow: unset;  
    echo '<div class="col-md-12 col-12 col-sm-12">
    <div class="table-responsive" style="">
    <center><table width="90%" class="mb-0 table table-hover table-striped">
    <thead class="bg-light-blue bg-darken-2 text-white">
    <tr>
    <th><b>Alias</b></th>
    <th><b>Telefono</b></th>
    <th><b>Correo</b></th>
    <th><b>Almacen</b></th>
    <th><b>Tipo registro</b></th>
    <th colspan="2"><b>Ver</b></th>
    <th><b>% registro completo</b></th>
    </tr></thead>';
    $i = 0;
    while($row = $this->model->result->fetch_array()){
      $progress = $this->model->progress($row['c_id']);
      if($progress < 30) $colpr = "danger";
      elseif($progress < 60) $colpr = "warning";
      elseif($progress < 90) $colpr = "info";
      else $colpr = "primary";

      echo '<tr>
        <th>'.$row['c_alias'].'</th>
        <td>'.$row['c_telefono1'].'</td>
        <td>'.$row['c_correo1'].'</td>
        <td>'.busca($row['c_almacen'],'almacenes','a_id','a_nmb').'</td>
        <td>'.$this->tipoc[$row['c_tipo']].'</td>
        <td>
          <div class="btn-group dropleft">
            <button type="button" class="btn btn-indigo dropdown-toggle" data-toggle="dropdown" >
              <i class="icon-gears"></i> Opciones
            </button>
            <div class="dropdown-menu" style="position: unset;"> <!-- unset -->
              <a class="dropdown-item" href="?modulo=remisiones&accion=edit&cliente='.$row['c_alias'].'">Realizar una venta</a>
              <a class="dropdown-item" href="?modulo=clientes&accion=direcciones&cliente='.$row['c_id'].'">Ver direcciones</a>
              <a class="dropdown-item" href="?modulo=clientes&accion=fiscales&cliente='.$row['c_id'].'">Ver Datos fiscales</a>
              <a class="dropdown-item" href="?modulo=clientes&accion=precios&cliente='.$row['c_id'].'">Lista de precios</a>
              <a class="dropdown-item" href="?modulo=clientes&accion=comodato&cliente='.$row['c_id'].'">Asignar comodato</a>
              <a class="dropdown-item" target="_BLANK" href="?modulo=clientes&accion=bitacora&id='.$row['c_id'].'">Ver bitácora</a>
<!--
              <a class="dropdown-item" href="#">Programar cita</a>
              <a class="dropdown-item" href="#">Ver proyectos</a>
              <a class="dropdown-item" href="#">Estado de cuenta</a>
-->
            </div>
          </div>
        </td>
        <td>
          <a href="?modulo=clientes&accion=edit&id='.$row['c_id'].'">
            <button type="button" class="btn btn-info" /><i class="icon-edit2"></i> Editar</button>
          </a>
          <a target="_BLANK" href="?modulo=remisiones&accion=edit&cliente='.$row['c_id'].'">
            <button type="button" class="btn btn-success" /><i class="icon-play-circle-o"></i> Dashboard</button>
          </a>
        </td>
        <td>
          <div class="text-xs-center">
            <div class="text-xs-center" id="example-caption-4">Expediente al '.$progress.'% </div>
            <a href="?modulo=clientes&accion=expediente&cliente='.$row['c_id'].'">
              <progress class="progress progress-'.$colpr.'" value="'.$progress.'" max="100" style="height: 20px;"></progress>
            </a>
          </div>
        </td>
      </tr>';
    }
    echo '</table>';
    if($_REQUEST['nmb']){

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

    }

  }

  function edit(){
    if($this->model->id){
      $titulo = 'Actualizar datos básicos de '.$this->model->alias;
    }
    else{
      $titulo = 'Registro básico de cliente';
      $this->model->almacen = "1";
    }

    echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
    echo '<a href="?modulo=clientes&accion=index">
      <button class="mb-2 mr-2 btn btn-warning"><i class="fa fa-arrow-left"></i>Regresar</button></a>';
    echo '</div></div>';

    ?>
    <script>
      function sendval(){
        document.getElementById("sologas").value = "1";
      }
    </script>
    <?php

 echo '
  <div class="row">
    <div class="col-md-10">
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
  echo '<form autocomplete="off" name="cliedit" id="cliedit" action="?modulo=clientes&accion=setuser" method="post" >
        <input type="hidden" name="solog" value="0" id="sologas" />';
  if($this->model->id)
    echo '<input type="hidden" name="id" value="'.$this->model->id.'" />';
  echo '
        <div class="card-body collapse in">
          <div class="card-block">
            <div class="form-body">
              <div class="row">
                <div class="col-md-3 col-sm-6">
                  <div class="input-group-desc">
                    <label class="name">Nombre(s)*</label>
                    <input class="form-control" autocomplete="off" type="text" value="'.$this->model->nmb.'" name="nmb" id="nmb" required="required" autofocus placeholder="Nombre del cliente">
                  </div>
                </div>

                <div class="col-md-3 col-sm-6">
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
                    <input class="input--style-3 form-control" type="email" value="'.$this->model->correo1.'" name="correo1" id="email" required="required" placeholder="Correo electrónico" data-toggle="tooltip" data-placement="top" title="Es importante que si sea el correo de tu cliente" >
                  </div>
                </div>

              </div>
              <div class="row  mt-2">

                <div class="col-md-3 col-sm-6">
                  <div class="input-group-desc">
                    <label class="label--desc">Telefono fijo</label>
                    <input class="input--style-3 form-control" type="tel" value="'.$this->model->telefono1.'" name="telefono1" id="telefono1" placeholder="Telefono fijo">
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
                    <textarea name="obs" class="form-control" placeholder="Si tienes alguna observación del cliente escribela a continuación">'.$this->model->obs.'</textarea >
                  </div>
                </div>
            </div>
            <div class="row mt-2" style="border-top: 1px solid">
              <div class="col-md-12"><h4 style="text-transform:uppercase;">Información comercial</h4></div>

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
          <div class="row  mt-2">
            <center><button type="submit" id="sendform" class="btn btn-success" ><i class="fas fa-save"></i> Guardar</button></center>
          </div>
        </div>
      </div>
    </div>
  </form>
  </div></div>';

  if($this->model->id)
    echo $this->actividades();

  }

  function bitacora($id,$fini,$ffin){
    echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
    echo '<form method="post">
          <input type="hidden" value="'.$id.'" name="id" />
          <input type="hidden" value="'.$fini.'" name="fini" />
          <input type="hidden" value="'.$ffin.'" name="ffin" />
          <a href="?modulo=clientes&accion=index">
            <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i></button>
          </a>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-target="#addcoment">
           <i class="icon-comment"></i> Agregar entrada
          </button>
          ';
    echo 'Fechas Inicial &nbsp; <input type="date" value="'.$fini.'" name="fini" />';
    echo ' Fecha Final <input type="date" value="'.$ffin.'" name="ffin" />';
    echo '</form></div></div>';

    echo '<!-- Modal -->
          <div class="modal fade text-xs-left" id="addcoment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                          <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                          <div class="heading-elements">
                          </div>-->
                        </div>
                        <div class="card-body collapse in">
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
                                <button type="button" class="btn btn-warning mr-1" data-dismiss="modal" >
                                  <i class="icon-cross2"></i> Cerrar sin guardar
                                </button>
                                <button type="submit" class="btn btn-success">
                                  <i class="fa fa-check2"></i> Guardar entrada
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

  $ultimac = busca($id,'remisiones','r_estatus = "A" AND r_cliente','MIN(r_faplica)');
  if(!$ultimac) $ucom = "No hay última compra";

  else $ucom = fecha_formato(date('Y-m-d',strtotime($ultimac)),false,true);
  $saldo = 'Debe pago de facturas';

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
                    <h3>278</h3>
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
                  <h3>'.$numrem.'</h3>
                  <span>Compras</span>
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
                    <i class="icon-calendar3 indigo font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">
                    <span>'.$ucom.'</span>
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
                    <i class="icon-user-tie teal darken font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">
                    <h3>'.busca($id,'crm_cliente_asesor','cca_cliente','cca_asesor').'</h3>
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
                    <i class="icon-glass indigo darken font-large-2 float-xs-left"></i>
                </div>
                <div class="media-body text-xs-right">
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
                <div class="media-body text-xs-right">
                  <span>'.$saldo.'</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>';

   echo '<div class="row">
          <div class="col-md-12">';
  echo '<div class="table-responsive">
          <table class="table table-striped">
            <thead class="bg-light-blue bg-darken-2 text-white">
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
            <td class="bg-'.$pinbg.'"> <i class="icon-pushpin" onclick="confpin('.$row['cb_id'].');"></i> </td>
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
  echo '</div></div>';
  echo '</div>';
  }

  function direcciones(){
    echo '
        <div class="modal fade" id="adddireccion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header mb-2">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                    '.menu_select_db('estados','e_clave','e_nmb','','estado','XXX',false, false, false, true,false).'
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
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>';

    echo '<div class="row">
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
    echo '</div>
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
  </script>
 <?php
 echo '<div id="accordionWrapa1" role="tablist" aria-multiselectable="true" class="col-md-10">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title" id="basic-layout-form">Direcciones de '.$this->model->alias.'</h4>
          </div>        ';

 $idcol = 0;
 while($rowd = $this->model->resultdir->fetch_array()){
   if($rowd['cd_predeterminada']) $pred = "checked"; else $pred = "";
   $idcol++;
   if($idcol == 1){ $ariae = ' in '; $icondef = "icon-minus4"; $showup = ' '; } else{ $ariae = ""; $icondef = "fa fa-plus"; $showup = ' style="display:none;" '; }

  echo '  <div id="heading'.$idcol.'" class="card-header row border-top  mb-4">
            <button type="button" class="btn btn-secondary col-md-1" data-toggle="tooltip" data-placement="top" data-original-title="Editar nombre de dirección"><i class="fa fa-pen-square-o"></i></button>
            <a data-toggle="collapse" data-parent="#accordionWrapa1" href="#accordion'.$idcol.'" aria-expanded="true" aria-controls="accordion1" class="card-title lead col-md-9" onclick="iconcollapse('.$idcol.')">
              <h6>'.$rowd['cd_nmbdir'].'</h6>
            </a>
            <button type="button" onclick="updatedir('.$idcol.');" class="btn btn-success" id="update'.$idcol.'" '.$showup.'><i class="fa fa-redo"></i> Actualizar</button>
          </div>
          <form name="senddir'.$idcol.'" method="post" action="?modulo=clientes&accion=setdircliente&cliente='.$this->model->id.'&dir='.$rowd['cd_id'].'" id="accordion'.$idcol.'" role="tabpanel" aria-labelledby="heading'.$idcol.'" class="card-collapse collapse '.$ariae.'" aria-expanded="true">
            <div class="card-body row">
              <div class="mb-5 col-12 col-md-12 dirname'.$idcol.'" style="display:none">
                <label for="calle">Nombre del domicilio</label>
                <input type="text" name="nmbdir" id="nmbdir'.$idcol.'" class="form-control" data-parent="#acordendirecciones" h placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" value="'.$rowd['cd_nmbdir'].'" readonly="readonly" required="required" step="1" />
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
                '.menu_select_db('estados','e_clave','e_nmb',$rowd['cd_estado'],'estado','XXX',false, false, false, true,false).'
              </div>
              <div class="mb-5 col-12 col-md-3">
                <label for="pais">Pais</label>
                <input type="text" name="pais" id="pais'.$idcol.'" class="form-control" placeholder="El pais de tu domicilio" title="Se requiere un valor"  required="required" step="10" value="'.$rowd['cd_pais'].'" />
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
                  <input type="checkbox" name="predeterminada" id="predeterminada'.$idcol.'" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="danger" '.$pred.'>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <label class="modal-title text-text-bold-600" id="ModalFiscales">Añadir Datos fiscales</label>
              </div>
                <form name="senddir" onsubmit="checkguardar1();" method="post" action="?modulo=clientes&accion=insertfiscliente&cliente='.$this->model->id.'" class="container">
                <div class="card-body row">
                <div class="mb-5 col-12 col-md-12 dirname13" >
                  <label for="calle">Dale un nombre a tus datos, para identificarlos mejor</label>
                  <input type="text" name="nmfiscales" id="nmfiscales" class="form-control" data-parent="#acordenfiscales" h placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" value="" required="required" />
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
                    <input type="checkbox" name="predeterminada" id="predeterminada" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="danger" checked>
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
                    '.menu_select_db('estados','e_clave','e_nmb','','estado','XXX',false, false, false, false,false).'
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

    echo '<div class="row">
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
              <button type="button" class="btn btn-primary mr-2 ml-2" data-bs-toggle="modal" data-target="#adddireccion"><i class="fa fa-plus"></i> Agregar Datos fiscales </button>
            </div>
          </div>';
    echo '</div>
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
  </script>
 <?php
 echo '<div id="accordionWrapa1" role="tablist" aria-multiselectable="true" class="col-md-10">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title" id="basic-layout-form">Datos fiscales de de '.$this->model->alias.'</h4>
          </div>        ';

 $idcol = 0;
 while($rowd = $this->model->resultdir->fetch_array()){
   if($rowd['cf_predeterminada']) $pred = "checked"; else $pred = "";
   $idcol++;
   if($idcol == 1){ $ariae = ' in '; $icondef = "icon-minus4"; $showup = ' '; } else{ $ariae = ""; $icondef = "fa fa-plus"; $showup = ' style="display:none;" '; }

  echo '  <div id="heading'.$idcol.'" class="card-header row border-top  mb-4">
            <button type="button" class="btn btn-secondary col-md-1" data-toggle="tooltip" data-placement="top" data-original-title="Editar nombre de dirección"><i class="fa fa-pen-square-o"></i></button>
            <a data-toggle="collapse" data-parent="#accordionWrapa1" href="#accordion'.$idcol.'" aria-expanded="true" aria-controls="accordion1" class="card-title lead col-md-9" onclick="iconcollapse('.$idcol.')">
              <h6>'.$rowd['cf_nmfiscales'].'</h6>
            </a>
            <button type="button" onclick="updatedir('.$idcol.');" class="btn btn-success" id="update'.$idcol.'" '.$showup.'><i class="fa fa-redo"></i> Actualizar</button>
          </div>
          <form name="senddir'.$idcol.'" method="post" action="?modulo=clientes&accion=updatefiscliente&cliente='.$this->model->id.'&idfis='.$rowd['cf_id'].'" id="accordion'.$idcol.'" role="tabpanel" aria-labelledby="heading'.$idcol.'" class="card-collapse collapse '.$ariae.'" aria-expanded="true">
            <div class="card-body row">
              <div class="mb-5 col-12 col-md-12 dirname'.$idcol.'">
                <label for="calle">Identificador de datos fiscales</label>
                <input type="text" name="nmfiscales" id="nmfiscales'.$idcol.'" class="form-control" data-parent="#acordenfiscales" h placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" value="'.$rowd['cf_nmfiscales'].'" readonly="readonly" required="required" step="1" />
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
                  <input type="checkbox" name="predeterminada" id="predeterminada'.$idcol.'" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="danger" '.$pred.'>
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

 function expediente(){
    echo '<div class="row">
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
              <button type="button" class="btn btn-success mr-2 ml-2" onclick="validasend();"><i class="fa fa-paper-plane-o"></i> Guardar registro</button>
            </div>
          </div>';
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
            <div class="card-body col-md-10" >
              <form method="post" name="segment" action="?modulo=clientes&accion=setexpediente&cliente='.$this->model->id.'">';
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

        if($lleno == "1") echo '<div class="alert alert-success"><i class="fa fa-check"></i></div>';
        else echo '<div class="alert alert-danger"><i class="icon-times"></i></div>';

        echo '</div>
              </div>';
      }
    }
    echo '<center>
            <button type="submit" class="btn btn-success mr-2 ml-2" ><i class="fa fa-paper-plane-o"></i> Guardar registro</button>
          </center>';
    echo '</form></div>';

    echo $this->actividades();

 }

function precios($categoria){
  echo '<div class="row">
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
            <button type="button" class="btn btn-success mr-2 ml-2" onclick="validasend();"><i class="fa fa-paper-plane-o"></i> Guardar registro</button>
          </div>
        </div>';

  echo '<div class="main-card mb-3 row">
          <div class="card-body col-md-10" >
            <form method="post" class="pt-1">';
  echo 'Filtrar por categoria '.menu_select_db('categorias','cat_id','cat_nmb',$categoria,'categoria','cat_empresa = "'.$_SESSION['emp'].'"',false,false,true,false,'Mostrar todos');
  echo '</form>';

  echo '<div class="table-responsive">
        <form action="?modulo=clientes&accion=preciocl&cliente='.$this->model->id.'&categoria='.$categoria.'" method="post" name="precioscli">
        <table class="table table-striped table-hover">
        <thead class="bg-light-blue bg-darken-2 text-white">
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
        </form></div></div>';
  echo $this->actividades();

 }

 function comodato(){
    echo '
        <div class="modal fade" id="addcomodato" tabindex="-1" role="dialog" aria-labelledby="ModalComodato" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header mb-2">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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

  echo '<div class="row">
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
              <button type="button" class="btn btn-primary mr-2 ml-2" data-bs-toggle="modal" data-target="#addcomodato"><i class="fa fa-plus"></i> Añadir comodatos </button>
            </div>
        </div>';

  echo '<div class="table-responsive">
          <table class="table table-striped">
            <thead class="bg-light-blue bg-darken-2 text-white">
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
  echo '</table>
        </div>';

 }

  function actividades(){

    if($_GET['accion'] == "edit") $alerta = 'class="alert alert-info"'; else $alerta = "";
    if($_GET['accion'] == "bitacora") $alertbtc = 'class="alert alert-info"'; else $alertbtc = "";
    if($_GET['accion'] == "direcciones") $alertdir = 'class="alert alert-info"'; else $alertdir = "";
    if($_GET['accion'] == "precios") $alertprc = 'class="alert alert-info"'; else $alertprc = "";
    if($_GET['accion'] == "fiscales") $alertfisc = 'class="alert alert-info"'; else $alertfisc = "";
    if($_GET['accion'] == "expediente") $alertexp = 'class="alert alert-info"'; else $alertexp = "";

  echo '<div class="col-md-2">
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

  echo '<div class="card-body collapse in">
          <div class="card-block">
            <div class="form-body">
              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <table class="table table-dark table-striped table-bordered table-hover">
                    <thead class="bg-light-blue bg-darken-2 text-white">
                      <tr><th>Elije una actividad</th></tr>
                    <thead>
                    <tbody>
                      <tr>
                        <th '.$alerta.'><a href="?modulo=clientes&accion=edit&id='.$this->model->id.'">
                          <i class="icon-home warning"></i> Datos del cliente </a>
                        </th>
                      </tr>
                      <tr '.$alertdir.'>
                        <th><a href="?modulo=clientes&accion=direcciones&cliente='.$this->model->id.'">
                          <i class="icon-home warning"></i> Direcciones </a>
                        </th>
                      </tr>
                      <tr '.$alertfisc.'>
                        <th><a href="?modulo=clientes&accion=fiscales&cliente='.$this->model->id.'">
                          <i class="icon-list-alt warning"></i> Datos fiscales</th>
                        </a></tr>
                      <tr>
                        <th '.$alertbtc.'>
                        <a target="_BLANK" href="?modulo=clientes&accion=bitacora&id='.$this->model->id.'">
                          <i class="icon-list-alt warning"></i> Ver bitácora
                        </a>
                      </tr>
                      <tr '.$alertexp.'>
                        <th>
                          <a href="?modulo=clientes&accion=expediente&cliente='.$this->model->id.'">
                            <i class="icon-folder-open warning"></i> Ver expediente</th>
                          </a>
                        </th>
                      </tr>
                      <tr '.$alertprc.'>
                        <th>
                        <a href="?modulo=clientes&accion=precios&cliente='.$this->model->id.'">
                          <i class="icon-tags warning"></i> Lista de precios
                        </a>
                      </tr>
                      <tr>
                        <th>
                        <a href="?modulo=remisiones&accion=edit&cliente='.$this->model->id.'">
                          <i class="icon-cart-plus warning"></i> Remisión
                        </a>
                      </tr>
                      <tr>
                        <th>
                        <a href="?modulo=clientes&accion=comodato&cliente='.$this->model->id.'">
                          <i class="icon-hand-paper-o warning"></i> Comodato
                        </a>
                      </tr>

                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>';
  echo '</div>';
  }

}
?>