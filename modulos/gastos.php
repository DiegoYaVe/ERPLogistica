<?php
  ini_set('display_errors', 1);
  class Gastos{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelGastos($obj);
    }
    function index(){
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if(!isset($_REQUEST['fini']))    $_REQUEST['fini'] = date('Y-m-01');
      if(!isset($_REQUEST['ffin']))    $_REQUEST['ffin'] = date('Y-m-d');
      if(!isset($_REQUEST['categoria']))    $_REQUEST['categoria'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->result($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin'],trim($_REQUEST['categoria']),$_REQUEST['page']);
      $this->view = new viewGastos($this->model);
      $this->view->browse($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['categoria'],$_REQUEST['page']);
    }
    function edit(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_REQUEST['empresa'])) $_REQUEST['empresa'] = 1;

      $this->model->select($_GET['id']);
      $this->view = new viewGastos($this->model);
      $this->view->edit($_REQUEST['empresa']);
    }
    function show(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new viewGastos($this->model);
      $this->view->show();
    }
    function insert(){

      $estatus = "P";

      $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-GS';
      $folioint = getmax('g_folio','gastos','g_empresa = "'.$_SESSION['emp'].'"');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }
      
      if(isset($_FILES['adjunto']['name'])){
        $diradj = 'Adjuntos/'.$_SESSION['emp'];
        if (!is_dir($diradj)) {
          @mkdir($diradj, 0777);
        }
        $info = new SplFileInfo(basename($_FILES['adjunto']['name']));
        $ext = $info->getExtension();
        $target = $diradj.'/'.$folioint.'.'.$ext;
        if(move_uploaded_file($_FILES['adjunto']['tmp_name'], $target)) $target = $folioint.'.'.$ext;
        else $target = NULL;
      }else $target = NULL;

      if(!isset($_POST['tablero'])) $_POST['tablero'] = NULL;
      if($_POST['tipo'] == "V") $concepto = $_POST['conceptov'];
      else $concepto = $_POST['conceptog'];
      $this->model->setdata(NULL, $_POST['descripcion'], $concepto, $_POST['importe'], $_POST['cuenta'], $_POST['tipo'], $_POST['empresa'], $_POST['fecha'],$estatus,$_POST['tablero'],$folioint,$target,$_POST['depto']);
      $this->model->insert();

      if($this->model->tipo == "G"){
        //Aplico pago del gasto
        include_once('cuentas.php');
        $cuent = new modelcuentas();
        $okcta = $cuent->cuentaactiva($_POST['cuenta']);

        if($okcta == "NOTOK"){
          die("Error en la cuenta, no hay corte activo");
        }
        else{
          include_once('cxpagar.php');
          $cxp = new modelcxpagar();
          $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_id');
          $idcxp = $cxp->insertcxpagar($this->model->empresa,$proveedor,$this->model->id,"G",$this->model->importe,$this->model->fecha,$this->model->descripcion);

          include_once('egresos.php');
          $egreso = new Modelegresos();
          $egreso->setData(NULL,$this->model->empresa,$this->model->importe,$proveedor,$idcxp,$this->model->cuenta,$this->model->fecha,date('Y-m-d H:i:s'),$_SESSION['uid'],"N",$_POST['observaciones'],$target,$corte);
          $egreso->insert();

          //ID del corted
          $corte = $okcta;
          $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
          $result = setq($sql);
          list($idcd) = $result->fetch_array();
          $idcd++;

          $fechacd = date('Y-m-d H:i:s',strtotime(str_replace("T"," ",$_POST['fecha'])));
          $cuent->setdatacd($idcd,$corte,$fechacd,$_SESSION['uid'],"P",$egreso->idegr,$this->model->importe,"G",$this->model->cuenta);
          $cuent->insertcd();

          $egreso->asignar($egreso->idegr,$idcxp,$this->model->importe,$this->model->importe);

          $sqli = 'UPDATE egresos SET e_estatus = "F" WHERE e_id="'.$egreso->idegr.'"';
          setq($sqli);

          $this->model->aplicado($this->model->id);
        }
      }else{
        include_once('cxpagar.php');
        $cxp = new modelcxpagar();
        $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_id');
        $idcxp = $cxp->insertcxpagar($this->model->empresa,$proveedor,$this->model->id,"V",$this->model->importe,$this->model->fecha,$this->model->descripcion);
        $cxp->addproyeccion($idcxp);
      }

      if($_POST['tablero']) $link = '?modulo=tableros&accion=show&id='.$_POST['tablero'];
      else $link = '?modulo=gastos&accion=index&id='.$this->model->id;

      redirect($link);
    }
    function update(){
      $estatus = "P";
      $this->model->setdata($_REQUEST['id'], $_POST['descripcion'], $_POST['conceptog'], $_POST['importe'], $_POST['cuenta'], $_POST['tipo'], $_POST['empresa'], $_POST['fecha'],$estatus,NULL,NULL);
      $this->model->update();

      //Aplico pago del gasto
      include_once('cuentas.php');
      $cuent = new modelcuentas();
      $okcta = $cuent->cuentaactiva($_POST['cuenta']);

      if($okcta == "NOTOK"){
        die("Error en la cuenta, no hay corte activo");
      }else{
        //include_once('cxpagar.php');
        //$cxp = new modelcxpagar();
        //$idcxp = $cxp->insertcxpagar($this->model->empresa,"1",$this->model->id,"G",$this->model->importe,$this->model->fecha,$this->model->descripcion);
        $idcxp = busca($_REQUEST['id'],'cxpagar','cp_tipo = "'.$_POST['tipo'].'" AND cp_idref','cp_id');

        include_once('egresos.php');
        $egreso = new Modelegresos();
        $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_id');
        $egreso->setData(NULL,$this->model->empresa,$this->model->importe,$proveedor,$idcxp,$this->model->cuenta,$this->model->fecha,date('Y-m-d H:i:s'),$_SESSION['uid'],"N",$_POST['observaciones'],$target,$corte);
        $egreso->insert();

        //ID del corted
        $corte = $okcta;
        $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
        $result = setq($sql);
        list($idcd) = $result->fetch_array();
        $idcd++;

        $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"P",$egreso->idegr,$this->model->importe,"G",$this->model->cuenta);
        $cuent->insertcd();

        $egreso->asignar($egreso->idegr,$idcxp,$this->model->importe,$this->model->importe);

        $sqli = 'UPDATE egresos SET e_estatus = "F" WHERE e_id="'.$egreso->idegr.'"';
        setq($sqli);
        $this->model->aplicado($this->model->id);
      }

      redirect('?modulo=gastos&accion=show&id='.$this->model->id);
    }
    function indexcategorias(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_POST['nmb'])) $_POST['nmb'] = NULL;
      if(!isset($_POST['tipo'])) $_POST['tipo'] = NULL;
      if(!isset($_POST['tipof'])) $_POST['tipof'] = NULL;

      $this->model->selectcat($_GET['id']);

      $this->model->resultcategoriag(trim($_POST['nmb']),$_POST['tipo'],$_POST['tipof']);
      $this->view = new viewGastos($this->model);
      $this->view->indexcategorias($_POST['nmb'],$_POST['tipo'],$_POST['tipof']);
    }
    function insertcat(){
      $this->model->setdataGC(NULL,$_POST['nmb'],$_POST['color'],$_POST['tipo'],$_POST['financiero'],$_SESSION['emp']);
      $this->model->insertcat();
      if(!$_GET['o']) $link = '?modulo=gastos&accion=indexcategorias';
      else{
        if($_GET['o'] == "f") $link = '?modulo=gastos&accion=gastosfijos';
        else $link = '?modulo=gastos&accion=index';
      }
      redirect($link);
    }
    function updatecat(){
      $this->model->setdataGC($_GET['id'],$_POST['nmb'],$_POST['color'],$_POST['tipo'],$_POST['financiero'],$_SESSION['emp']);
      $this->model->updatecat();
      redirect('?modulo=gastos&accion=indexcategorias');
    }
    function cancelar(){
      $ok = $this->model->cancelar($_GET['id']);
      if($ok == 1) $link = '?modulo=gastos&accion=index';
      else {
        echo '<script>
          alert("No es posible cancelar el gasto, verifique que el gasto pertenezca al corte actual de la cuenta en que se genero.");
          window.location.href="?modulo=gastos&accion=show&id='.$_GET['id'].'";
        </script>';
        $link = '';
      }
      redirect($link);
    }
    function gastosfijos(){
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = 'A';
      if(!isset($_POST['nombre']))    $_POST['nombre'] = NULL;
      if(!isset($_POST['categoria']))    $_POST['categoria'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->resultGastof($_GET['page'],$_REQUEST['estatus'],$_POST['nombre'],trim($_POST['categoria']),$_REQUEST['page']);
      $this->view=new viewGastos($this->model);
      $this->view->browseGastof($_GET['page'],$_REQUEST['estatus'],$_POST['nombre'],$_POST['categoria'],$_REQUEST['page']);
    }
    function insertgastof(){
      $this->model->setDatagastof(NULL,$_SESSION['emp'],$_POST['nmb'],$_POST['categoria'],$_POST['importe'],$_POST['iteracion'],$_POST['fechaap']);
      $this->model->insertgastof();
      redirect('?modulo=gastos&accion=gastosfijos&id='.$this->model->idgf);
    }
    function updategastof(){
      $this->model->setDatagastof($_GET['id'],$_SESSION['emp'],$_POST['nmb'],$_POST['categoria'],$_POST['importe'],$_POST['iteracion'],$_POST['fechaap']);
      $this->model->deletegastof($_GET['id']);
      $this->model->updategastof();
      redirect('?modulo=gastos&accion=gastosfijos&id='.$_GET['id']);
    }
    function desactivargastof() {
      $this->model->deletegastof($_GET['id']);
      $this->model->desactivargastof($_GET['id']);
      redirect('?modulo=gastos&accion=gastosfijos&id='.$_GET['id']);
    }
    function activagastof() {
      $this->model->activargastof($_GET['id']);
      redirect('?modulo=gastos&accion=gastosfijos&id='.$_GET['id']);
    }
    // Comprobante del gasto, para contabilidad
    /*
      function insertcomp(){
        $ext = pathinfo($_FILES ["comprobante"] ["name"], PATHINFO_EXTENSION);
        $nameticket = substr($_FILES ["comprobante"] ["name"],0,(-1*(strlen($ext)+1)));

        if(file_exists("adjuntos/" . $nameticket . '.' . $ext) AND $nameticket ){
          die(alert_back("Error: El nombre del archivo ya existe ".$nameticket . "." . $ext." ",true));
        }
        $ida=0;
        if(move_uploaded_file($_FILES ["comprobante"] ["tmgf_name"], "adjuntos/" . $nameticket . '.' . $ext)){
        $sql = 'SELECT MAX(a_id) FROM adjuntos';
        $result = setq($sql) or die($sql);
        list($ida)=mysql_fetch_row($result);
        $ida++;

          $sqlia = 'INSERT INTO adjuntos SET
                  a_id = "'.$ida.'",
                  a_nmb = "'.$nameticket.'",
                  a_ext = "'.$ext.'"';
          setq($sqlia) or die($sqlia);
        }

        $this->model->setdatacomp($_POST['id'], $_POST['detalle'], $_POST['importe'], $_POST['tipo'], $ida );
        $this->model->insertcomp();

        redirect('?modulo=gastos&accion=show&id='.$_POST['id'].'');
      }
      function aplicargasto(){
        $this->model->select($_GET['id']);
        include_once('cuentas.php');
        $cuentas=new modelcuentas();
        $cuentas->insertedoctaman($this->model->cuenta,'C','G',$this->model->id,$this->model->importe,$this->model->descripcion);
        $sql='select sum(gd_monto) FROM gastosd WHERE gd_idgasto="'.$this->model->id.'" AND gd_tipo="2"';
        $rs=setq($sql) or die($sql);
        list($reintegro)=$rs->fetch_array();
        if($reintegro>0)
        $cuentas->insertedoctaman($this->model->cuenta,'A','I',$this->model->id,$reintegro,'Reintegro del gasto: '.$this->model->id);
        $this->model->aplicado($_GET['id']);
        redirect('?modulo=gastos&accion=show&id='.$this->model->id);
      }
    */
    function autorizarv(){
      if($_POST['tipo'] == "V"){
        $this->model->setdata($_REQUEST['id'], $_POST['descripcion'], $_POST['conceptog'], $_POST['importe'], $_POST['cuenta'], $_POST['tipo'], $_POST['empresa'], $_POST['fecha'],"X",NULL,NULL);
        $this->model->updatevale();
        $idcxp = busca($_REQUEST['id'],'cxpagar','cp_tipo = "V" AND cp_idref','cp_id');
        $sqlupd = 'UPDATE cxpagar SET cp_importe = "'.$_POST['importe'].'", cp_fini = "'.$_POST['fecha'].'"
                    WHERE cp_id = "'.$idcxp.'"';
        setq($sqlupd);

        if($_SESSION['emp'] == "1"){
          $proyeccion = busca($idcxp,'proyeccionesd','pd_tipo = "P" AND pd_referencia','pd_id');
          if($proyeccion){
            $sqlproy = 'SELECT p_id,p_fechaap,p_fechacierre FROM proyecciones WHERE p_estatus = "N"';
            $resultproy = setq($sqlproy);
            list($idp,$fap,$fcp) = $resultproy->fetch_array();
            
            $fechav = date("Y-m-d",strtotime($_POST['fecha']));
            if($fechav >= $fap && $fechav <= $fcp){
              $sqlupproy = 'UPDATE proyeccionesd SET 
                            pd_totalc = "'.$_POST['importe'].'",
                            pd_importe = "'.$_POST['importe'].'"
                            WHERE pd_id = "'.$proyeccion.'" AND pd_referencia = "'.$idcxp.'"';
              setq($sqlupproy);
            }else{
              include_once("cuentas.php");
              $mcuentas = new modelcuentas();
              $mcuentas->delproyecciond($proyeccion);
            }
          }
        }
        $link = '?modulo=gastos&accion=show&id='.$this->model->id;
      }else $link = '?modulo=gastos&accion=index';

      redirect($link);
    }
  }

  class modelGastos{
    function result($estatus,$fini,$ffin,$categoria,$page){ //filtro
      $bloque = 50;
      $sql = 'SELECT * FROM gastos INNER JOIN gastos_categoria ON gc_id = g_categoria WHERE 1';
      if($estatus){
        $sql .= ' AND g_estatus = "'.$estatus.'" ';
      }
      if($categoria){
        $sql .= ' AND gc_nmb LIKE "%'.$categoria.'%"';
      }
      if($fini && $ffin)  $sql.= ' AND DATE(g_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      $sql .= ' ORDER BY g_fecha DESC ';
      $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
      $this->result = setq($sql) or die($sql.' '.mysql_error());
      $this->resultt = setq($sql) or die($sql.' '.mysql_error());
    }
    function select($Gastos){
      $sql = 'SELECT * FROM gastos WHERE g_id="'.$Gastos.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['g_id'];
      $this->categoria = $row['g_categoria'];
      $this->importe = $row['g_importe'];
      $this->descripcion = $row['g_descripcion'];
      $this->tipo = $row['g_tipo'];
      $this->empresa = $row['g_empresa'];
      $this->tablero = $row['g_tablero'];
      $this->fecha = $row['g_fecha'];
      $this->user = $row['g_user'];
      $this->estatus = $row['g_estatus'];
      $this->adjunto = $row['g_adjunto'];
      $this->notaofac = $row['g_notaofac'];
      $this->numnf = $row['g_numnf'];
      $this->reintegro = $row['g_reintegro'];
      $this->fechaacr = $row['g_fechaacr'];
      $this->fiscal = $row['g_fiscal'];
      $this->depto = $row['g_depto'];
      $this->folioint = $row['g_folio'];
    }
    function resultd($id){
      $sql = 'SELECT * FROM gastosd WHERE gd_idgasto = "'.$id.'" ORDER BY gd_descripcion ASC';
      $this->result = setq($sql) or die($sql);
    }
    function setdata($id, $descripcion, $conceptog, $importe, $cuenta, $tipo,$empresa,$fecha,$estatus,$tablero,$folio,$adjunto = NULL,$depto){
      mb_internal_encoding("UTF-8");
      $this->id = $id;
      $this->descripcion=clearvmayus($descripcion);
      $this->categoriag = $conceptog;
      $this->importe = $importe;
      $this->cuenta = $cuenta;
      $this->tipo = $tipo;
      $this->empresa = $empresa;
      $this->fecha= $fecha;
      $this->estatus= $estatus;
      $this->tablero = $tablero;
      $this->folio = $folio;
      $this->adjunto = $adjunto;
      $this->depto = $depto;
    }
    function insert(){
      $sql = 'INSERT INTO gastos SET
              g_descripcion = "'.$this->descripcion.'",
              g_empresa = "'.$this->empresa.'",
              g_categoria = "'.$this->categoriag.'",
              g_tipo = "'.$this->tipo.'",
              g_importe = "'.$this->importe.'",
              g_user = "'.$_SESSION['uid'].'",
              g_fecha = "'.$this->fecha.'",
              g_tablero = "'.$this->tablero.'",
              g_estatus = "'.$this->estatus.'",
              g_adjunto = "'.$this->adjunto.'",
              g_depto = "'.$this->depto.'",
              g_folio = "'.$this->folio.'"';
      setq($sql);
      $this->id = getmax('g_id','gastos',false,false);
    }
    function update(){
      $sql = 'UPDATE gastos SET
              g_descripcion = "'.$this->descripcion.'",
              g_empresa = "'.$this->empresa.'",
              g_categoria = "'.$this->categoriag.'",
              g_importe = "'.$this->importe.'",
              g_user = "'.$_SESSION['uid'].'",
              g_fecha = "'.$this->fecha.'",
              g_depto = "'.$this->depto.'",
              g_estatus = "'.$this->estatus.'"
              WHERE g_id = "'.$this->id.'"';
      setq($sql) or die($sql);
    }
    function aplicado($id){
      $sql='UPDATE gastos SET g_estatus="A" WHERE g_id="'.$id.'"';
      setq($sql) or die($sql);
    }
    function selectcat($id){
      $sql='SELECT * FROM gastos_categoria WHERE gc_id="'.$id.'"';
      $result =setq($sql) or die($sql);
      $row=$result->fetch_array();
      $this->id=$row['gc_id'];
      $this->nmb=$row['gc_nmb'];
      $this->color=$row['gc_color'];
      $this->tipo=$row['gc_tipo'];
      $this->empresa	=$row['gc_empresa'];
      $this->estatus=$row['gc_estatus'];
    }
    function resultcategoriag($nmb,$tipo,$tipof){
      $sql='SELECT * FROM gastos_categoria WHERE gc_empresa = "'.$_SESSION['emp'].'"';
      if($nmb) $sql.=' AND gc_nmb LIKE "%'.$nmb.'%"';
      if($tipo) $sql .= ' AND gc_tipo = "'.$tipo.'"';
      if($tipof) $sql .= ' AND gc_financiero = "'.$tipof.'"';
      $sql.=' ORDER BY gc_tipo, gc_nmb';
      $this->resultc=setq($sql) or die($sql);
    }
    function setdataGC($id,$nmb,$color,$tipo,$financiero,$empresa){
      $this->id = $id;
      $this->nmbcat = clearvmayus($nmb);
      $this->color = clearvmayus($color);
      $this->tipo = clearvmayus($tipo);
      $this->financiero = clearvmayus($financiero);
      $this->empresa = $empresa;
    }
    function insertcat(){
      $sql='INSERT INTO gastos_categoria SET
            gc_nmb="'.$this->nmbcat.'",
            gc_tipo="'.$this->tipo.'",
            gc_financiero="'.$this->financiero.'",
            gc_color="'.$this->color.'",
            gc_empresa="'.$this->empresa.'",
            gc_estatus="A"';
      setq($sql);

      $this->idgc = getmax('gc_id','gastos_categoria','gc_empresa = "'.$_SESSION['emp'].'"',false);
    }
    function updatecat(){
      $sql='UPDATE gastos_categoria SET
            gc_nmb="'.$this->nmbcat.'",
            gc_financiero="'.$this->financiero.'",
            gc_tipo="'.$this->tipo.'",
            gc_color="'.$this->color.'",
            gc_empresa="'.$this->empresa.'",
            gc_estatus="A"
            WHERE gc_id="'.$this->id.'"';
      setq($sql);
    }
    //Gastos fijos
    function resultGastof($pagex,$estatus,$nombre,$rubro,$page) {
      $bloque = 50;
      $sql = 'SELECT * FROM gastosf INNER JOIN gastos_categoria ON gc_id = gf_categoria WHERE gf_id <>0 AND gf_empresa = "'.$_SESSION['emp'].'"';
      if($nombre) $sql.=' AND gf_nmb LIKE "%'.$nombre.'%" ';
      if($estatus) $sql.=' AND gf_estatus = "'.$estatus.'" ';
      if($rubro) $sql.=' AND gc_nmb LIKE "%'.$rubro.'%"';

      $sql.=' ORDER BY gf_id DESC ';
      $result = setq($sql);
      if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
      $this->result = setq($sql);
      $this->resultt = setq($sql);
    }
    function selectgastof($id) {
      $sql = 'SELECT * FROM gastosf WHERE gf_id= "' . $id . '"';
      $result = setq($sql);
      if($result) $row = $result->fetch_array();

      $this->id= $row['gf_id'];
      $this->nmb= $row['gf_nmb'];
      $this->empresa= $row['gf_empresa'];
      $this->categoria=$row['gf_categoria'];
      $this->importe=$row['gf_importe'];
      $this->ucreo= $row['gf_ucreo'];
      $this->fechaap= $row['gf_fechaap'];
      $this->fcreo= $row['gf_fcreo'];
      $this->estatus= $row['gf_estatus'];
      $this->iteracion= $row['gf_iteracion'];
    }
    function setDataGastof($id,$empresa,$nombre,$categoria,$importe,$iteracion,$fechaap){
      $this->id= $id;
      $this->empresa=clearvmayus($empresa);
      $this->nmb=clearvmayus($nombre);
      $this->categoria= clearvmayus($categoria);
      $this->importe=$importe;
      $this->fechaap=$fechaap;
      $this->iteracion=$iteracion;
    }
    function insertgastof(){
      $sql='INSERT INTO gastosf SET
            gf_nmb="'.$this->nmb.'",
            gf_empresa="'.$this->empresa.'",
            gf_categoria="'.$this->categoria.'",
            gf_importe="'.$this->importe.'",
            gf_estatus="A",
            gf_iteracion="'.$this->iteracion.'",
            gf_ucreo="'.$_SESSION['emp'].'",
            gf_fcreo="'.date('Y-m-d H:i:s').'",
            gf_fechaap="'.$this->fechaap.'"';
      setq($sql);

      $this->idgf = getmax('gf_id','gastosf','gf_empresa = "'.$_SESSION['emp'].'"',false);
    }
    function updategastof(){
      $sql='UPDATE gastosf SET
            gf_nmb="'.$this->nmb.'",
            gf_empresa="'.$this->empresa.'",
            gf_categoria="'.$this->categoria.'",
            gf_importe="'.$this->importe.'",
            gf_estatus="A",
            gf_iteracion="'.$this->iteracion.'",
            gf_ucreo="'.$_SESSION['emp'].'",
            gf_fcreo="'.date('Y-m-d H:i:s').'",
            gf_fechaap="'.$this->fechaap.'"
            WHERE gf_id="'.$this->id.'"';
      setq($sql) or die($sql);
    }
    function activargastof($id) {
      $sql = 'UPDATE gastosf SET gf_estatus = "A" WHERE gf_id="'.$id.'"';
      setq($sql) or die($sql);
    }
    function desactivargastof($id) {
      $sql = 'UPDATE gastosf SET gf_estatus = "I" WHERE gf_id="'.$id.'"';
      setq($sql) or die($sql);
    }
    function deletegastof($id){
      $sql = 'DELETE FROM cxpagar WHERE cp_tipo = "F" AND cp_estatus = "N" AND cp_idref = "'.$id.'"';
      setq($sql) or die($sql);
    }
    function cancelar($id){
      $this->select($id);
      $cxpagar = busca($id,'cxpagar','cp_tipo = "'.$this->tipo.'" AND cp_idref','cp_id');
      $egreso = busca($cxpagar,'egreso_cxpagar','ec_cxpagar','ec_egreso');
      $corte = busca($egreso,'cortesd','cd_tipom = "'.$this->tipo.'" AND cd_tipo = "P" AND cd_idref','cd_corte');

      $corteact = busca($corte,'cortes','c_estatus = "A" AND c_id','COUNT(*)');
      $cuenta1 = busca($corte,'cortes','c_id','c_cuenta');

      if($corteact == 1){
        $sql = 'UPDATE cxpagar SET cp_abonado = 0, cp_estatus = "C" WHERE cp_id = "'.$cxpagar.'"';
        setq($sql) or die($sql);

        $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
        $result = setq($sql);
        list($idcd) = $result->fetch_array();
        $idcd++;

        include('cuentas.php');
        $cuent = new modelcuentas();

        $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C",$id,$this->importe,"X",$cuenta1);
        $cuent->insertcd();

        $sqlg = 'UPDATE gastos SET g_estatus = "C" WHERE g_id = "'.$id.'"';
        setq($sqlg) or die($sqlg);
        
        $sqlg = 'UPDATE egresos SET e_estatus = "C" WHERE e_id = "'.$egreso.'"';
        setq($sqlg) or die($sqlg);
        
        $sqlg = 'DELETE FROM  egreso_cxpagar WHERE ec_egreso = "'.$egreso.'"';
        //setq($sqlg) or die($sqlg);
        
        $sqlcd = 'SELECT cd_id FROM cortesd WHERE cd_tipo = "P" AND cd_idref = "'.$egreso.'"';
        $resultcd = setq($sqlcd);
        list($idcorteor) = $resultcd->fetch_array();

        $sql = 'UPDATE cortesd SET cd_reverso = "'.$idcorteor.'" WHERE cd_id = "'.$idcd.'" AND cd_corte = "'.$corte.'"';
        setq($sql);
        
        $sql = 'UPDATE cortesd SET cd_reverso = "'.$idcd.'" WHERE cd_id = "'.$idcorteor.'" AND cd_corte = "'.$corte.'"';
        setq($sql);
        
        $ok = 1;
      }else $ok = 0;

      return $ok;
    }
    function updatevale(){
      $sql = 'UPDATE gastos SET
              g_importe = "'.$this->importe.'",
              g_user = "'.$_SESSION['uid'].'",
              g_fecha = "'.$this->fecha.'",
              g_estatus = "'.$this->estatus.'"
              WHERE g_id = "'.$this->id.'"';
      setq($sql) or die($sql);
    }
    /*
      function setdatasub($Gastos,$subGastos,$modulo,$accion,$orden){
        $this->id = $Gastos;
        $this->subGastos = $subGastos;
        $this->modulo = $modulo;
        $this->accion = $accion;
        $this->orden = $orden;
      }
      function setdatacomp($id,$detalle,$importe,$tipo,$add){
        mb_internal_encoding("UTF-8");
            $vowels = array('"',"'");
            $this->id = $id;
            $this->importe = $importe;
            $this->tipo = $tipo;
            $this->add = $add;
            $this->detalle=str_replace($vowels,"", mb_strtoupper($detalle));
      }
      function deletecomp($id,$idd){
        $sql = 'DELETE FROM gastosd
        WHERE gd_id = "'.$idd.'" AND gd_idgasto = "'.$id.'"';
        setq($sql) or die($sql);
      }
      function insertcomp(){

        $sql= 'SELECT MAX(gd_id) FROM gastosd';
            $result = setq($sql);
          list($this->idd) = $result->fetch_array();
          $this->idd++;

        $sql = 'INSERT INTO gastosd SET
        gd_id = "'.$this->idd.'",
        gd_descripcion = "'.$this->detalle.'",
        gd_fecha = "'.date('Y-m-d H:i:s').'",
        gd_usuario =  "'.$_SESSION['uid'].'",
        gd_add = "'.$this->add.'",
        gd_idgasto = "'.$this->id.'",
        gd_monto = "'.$this->importe.'",
        gd_tipo = "'.$this->tipo.'"
        ';
        setq($sql) or die($sql);
      }
      function deletesub(){
        $sql = 'DELETE FROM Gastosd
        WHERE md_Gastos = "'.$_GET['Gastos'].'" AND md_id = "'.$_GET['Gastosd'].'"
        ';
        setq($sql) or die($sql);
      }
    */
  }

  class viewGastos {
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->tipocg = array("G"=>"Gasto","V"=>"Vale","F"=>"Gasto Fijo");
      $this->clasificacioncg = array("O"=>"Operacional","A"=>"Administración","V"=>"Ventas","N"=>"No operacional","I"=>"Impuestos");
    }
    function browse($estatus,$fini,$ffin,$categoria,$page) {
      
        //Sección: E2 Encabezado - Filtros
        if($fini == "") $fini = date('Y-m-01');
        if($ffin == "") $ffin = date('Y-m-d');
        if($estatus == "T") $selt = "selected";
        elseif($estatus == "F") $self = "selected";
        elseif($estatus == "C") $selc = "selected";
        elseif($estatus == "P") $selp = "selected";
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
              $("#categoria").focus();
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
        $botones = ""; 
        $nuevo = '<a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setgasto.php?rand='.rand() .'" href="javascript:;" >
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>';
        $botones .= '<a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setgastocategoria.php?o=v" href="javascript:;" >
            <button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Agregar categoria
            </button>
          </a>';
        $filtro ='
              <form class="form-inline" role="form" method="post" action="?modulo=gastos&accion=index" id="filtro"> 
              <input name="page" id="page" value="'.$page .'" hidden> 
                <div class="mb-5">
                  <label for="tipom">Desde:</label>
                  <input type="date" name="fini" id="fini" class="form-control" value="'.$fini .'" />
                </div>
                <div class="mb-5">
                  <label for="tipom">Hasta:</label>
                  <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
                </div>
                <div class="mb-5">
                  <label for="tipom">Categoria:</label>
                  <input onkeyup="ceropage();" type="text" class="form-control" id="categoria" name="categoria" placeholder="Nombre de la categoria" onfocus="this.select();" value="'.$categoria .'" />
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
                    <option value="" '.$selt .' >Todos</option>
                    <option value="A" '.$self .' >Aplicados</option>
                    <option value="P" '.$selp .' >Pendientes</option>
                    <option value="C" '.$selc .' >Cancelados</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                  <button type="button" onclick="mandar(0)" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
                  </button>
                  <a href="?modulo=gastos&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                  </button></a>
                </div>
              </form>';

          toolbar($_GET['modulo'], "", $filtro, $nuevo, $botones);
          echo '
          <div class="main-card mb-2 card mt-2">
          <div class="card-body">
          <div class="table-responsive text-medium" id="table-responsive">
            <center>
              <table border="0" cellspacing="0" width="80%" class="table table-hover table-striped" id="myTable">
                <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
                  <tr>
                    <th></th>
                    <th>Descripción</th>
                    <th>Categoria</th>
                    <th>Fecha</th>
                    <th>Cta Origen</th>
                    <th>Importe</th>
                    <th>Usuario</th>
                    <th>Estatus</th>
                  </tr>
                </thead>';
                while ($row = $this->model->result->fetch_array()) {
                  $cuenta = buscacuenta("P",$row['g_tipo'],$row['g_id']);
                  echo '<tr>
                    <th>
                      <a href="?modulo=gastos&accion=show&id='.$row['g_id'].'">
                        <button class="btn btn-info"><i class="fas fa-chevron-circle-right"></i></button>
                      </a>
                    </th>
                    <td>'.$row['g_descripcion'].'</td>
                    <td>'.busca($row['g_categoria'],'gastos_categoria','gc_id','gc_nmb').'</td>
                    <td>'.fecha_formato(date('Y-m-d',strtotime($row['g_fecha'])),false,false).'</td>
                    <td>'.$cuenta.'</td>
                    <td>$'.number_format($row['g_importe'],2).'</td>';
                    echo '<td>'.$row['g_user'].'</td>';
                    if($row['g_estatus']=="A"){echo'<td  style= "background:#45C431"> Aplicado</td>';  }
                    if($row['g_estatus']=="P"){echo'<td  style= "background:#F1D837"> Pendiente de comprobación</td>';  }
                    if($row['g_estatus']=="C"){echo'<td style= "background:#CC0000" >Cancelado</td>';}
                    if($row['g_estatus']=="X"){echo'<td style= "background:#1ddbd6" >Pendiente de pago</td>';}
                  echo '</tr>';
                }
              echo '</table>
            </center>
          </div>
          </div>
          </div>
          
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
                pageLength: "50",
                
            });
          });
          </script>';
    }
    function indexcategorias($nmb,$tipo,$tipof){
        //Sección: E2 Encabezado - Filtros
        $selg = "";
        $selv = "";
        $selt = "";
        if($tipo == "G") $selg = " selected ";
        elseif($tipo == "V") $selv = " selected ";
        elseif($tipo == "F") $self = " selected ";
        $selco = "";
        $selca = "";
        $selcv = "";
        $selcn = "";
        $selci = "";
        if($tipof == "O") $selco = " selected ";
        elseif($tipof == "V") $selcv = " selected ";
        elseif($tipof == "A") $selca = " selected ";
        elseif($tipof == "N") $selcn = " selected ";
        elseif($tipof == "I") $selci = " selected ";
        else $selct = "selected";
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
              $("#nmb").focus();
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
       
        $nuevo = '<a id="nuevo" class="btn btn-primary" data-fancybox data-type="ajax" data-src="popup/setgastocategoria.php" href="javascript:;">
            <i class="fa fa-plus"></i> Nuevo
          </a>';
          
        $filtro = '
              <form class="form-inline" role="form" method="post" action="?modulo=gastos&accion=indexcategorias">
                <div class="mb-5">
                  <label for="tipom">Categoria:</label>
                  <input type="text" class="form-control" id="nmb" name="nmb" placeholder="Nombre de la categoria" onfocus="this.select();" value="'.$nmb .'" />
                </div>
                <div class="mb-5">
                  <label for="tipom">Tipo:</label>
                  <select class="form-control" name="tipo" id="tipo" >
                    <option value="" '.$selt .' >Todos</option>
                    <option value="G" '.$selg .' >Gastos</option>
                    <option value="F" '.$self .' >Fijos</option>
                    <option value="V" '.$selv .' >Vales</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="tipom">Clasificación financiera:</label>
                  <select class="form-control" name="tipof" id="tipof" >
                    <option value="" '.$selct .' >Todos</option>
                    <option value="O" '.$selco .' >Operacion</option>
                    <option value="A" '.$selca .' >Administración</option>
                    <option value="V" '.$selcv .' >Ventas</option>
                    <option value="N" '.$selcn .' >No Operacional</option>
                    <option value="I" '.$selci .' >Impuestos</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Filtrar
                  </button>
                  <a href="?modulo=gastos&accion=indexcategorias">
                    <button type="button" class="btn btn-warning">
                      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                    </button>
                  </a>
                </div>
              </form>';

          toolbar($_GET['modulo'], "", $filtro, $nuevo);
          
          $seltg = ""; $seltv = ""; $seltf = "";
          $selfco = ""; $selfca = ""; $selfcv = ""; $selfcn = ""; $selfci = "";
          if($this->model->id){
            $accion = 'updatecat&id='.$this->model->id.'"';
            if($this->model->tipo == "G") $seltg = "";
            elseif($this->model->tipo == "F") $seltf = "selected";
            else $seltv = "selected";
            if($this->model->financiero == "O") $selfco = "selected";
            elseif($this->model->financiero == "A") $selfca = "selected";
            elseif($this->model->financiero == "V") $selfcv = "selected";
            elseif($this->model->financiero == "N") $selfcn = "selected";
            elseif($this->model->financiero == "I") $selfci = "selected";
          }
          else $accion = "insertcat";
          /*
            echo '<center>
              <form method="post" action="?modulo=gastos&accion='.$accion.'" onsubmit="return checkSubmitedit();">
                <div class="row">
                  <div class="col-md-12 col-12 alert alert-primary">Agregar nueva categoría</div>
                  <div class="col-md-4 col-sm-8 ">
                    <div class="mb-5">
                      <label for="nmb">Nombre de la categoria</label>
                      <input type="text" name="nmb" value="'.$this->model->nmb.'" required class="form-control" autofocus />
                    </div>
                  </div>
                  <div class="col-md-2 col-sm-4">
                    <div class="mb-5">
                      <label for="tipo">Tipo de categoria</label>
                      <select name="tipo" id="tipo" class="form-control">
                        <option value="G" '.$seltg.'>Gasto</option>
                        <option value="V" '.$seltv.'>Vale</option>
                        <option value="F" '.$seltf.'>Gasto Fijo</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-2 col-sm-6">
                    <div class="mb-5">
                      <label for="tipo">Tipo de categoria</label>
                      <select class="form-control" name="financiero" id="financiero" >
                        <option value="O" '.$selco.' >Operacion</option>
                        <option value="A" '.$selca.' >Administración</option>
                        <option value="V" '.$selcv.' >Ventas</option>
                        <option value="N" '.$selcn.' >No Operacional</option>
                        <option value="I" '.$selci.' >Impuestos</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-2 col-sm-6">
                    <div class="mb-5">
                      <label for="tipo">Color de categoria <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Color que aparecerá en los reportes"></i></label>
                      <input type="color" name="color" class="form-control" id="color" value="'.$this->model->color.'" required>
                    </div>
                  </div>';
                  echo '<div class="col-md-2 col-sm-12">
                    <label for="tipo"></label>.<br>
                    <button type="submit" name="save" id="guardar" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
                  </div>
                </div>
              </form>
            </center>';
          */
          echo '
          <div class="main-card mb-2 card mt-2">
          <div class="card-body">
          <div class="table-responsive">
            <center>
              <table class="table table-stripped" id="myTable">
                <thead class="thead pt-1 pb-1">
                  <tr>
                    <th></th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Clasificación</th>
                    <th width="10%">Color</th>
                    <th width="15%"></th>
                  </tr>
                </thead>
                <tbody>';
                  while($row=$this->model->resultc->fetch_array()){
                    echo '<tr>';
                      echo '<td>
                        <a data-fancybox data-type="ajax" data-src="popup/setgastocategoria.php?id='.$row['gc_id'].'" href="javascript:;">
                          <button type="button" class="btn btn-info"><i class="fa fa-pen-square"></i></button>
                        </a>
                      </td>';
                      echo '<td>'.$row['gc_nmb'].'</td>';
                      echo '<td>'.$this->tipocg[$row['gc_tipo']].'</td>';
                      echo '<td>'.$this->clasificacioncg[$row['gc_financiero']].'</td>';
                      echo '<td style="background:'.$row['gc_color'].'">&nbsp;</td>';
                    echo '</tr>';
                  }
                echo '</tbody>
              </table>
            </center>
          </div>
          
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
                  pageLength: "50",
                  
              });
            });
          </script>';
    }
    /*
      function edit($empresa){
        $accion = 'insert';
        if (isset($this->model->id)){$accion = 'update'; }
        $conc = 'SELECT u_id FROM usuarios WHERE u_estatus="A"'; //consulta para seleccionar las palabras a buscar, esto va a depender de su base de datos
        $queryc = setq($conc) or die($conc);
        ?><script language="JavaScript">
          function checkSubmitedit() {
            document.getElementById("guardar").value = "JD";
            document.getElementById("guardar").disabled = true;
            return true;
          }
          $(function() {
            <?php
            while ($rowc = $queryc->fetch_array()) {//se reciben los valores y se almacenan en un arreglo
              $elementosc[] = '"' . $rowc['u_id'] . '"';
            }
            $arregloc = implode(", ", $elementosc); //junta los valores del array en una sola cadena de texto
            ?>
            var availableTags = new Array(<?php echo $arregloc; ?>);//imprime el arreglo dentro de un array de javascript
            $("#tagsc").autocomplete({
              source: availableTags
            });
          });
        </script><?php
        if(!$this->model->empresa) $this->model->empresa = $empresa;
        echo '<div class="div2" id="bboton01">
          <input type=button value=" " onClick="javascript:history.go(-1);" id="atras" class="botonb">
        </div></div>';
        echo '<center>
          <input type="hidden" value="'.$this->model->id.'" name="id"/>
          <table border="0" cellspacing="1" width="50%" class="lista">
            <thead><tr><th colspan="2"><b>Gastos</b></th></tr><thead>';
            $sql='SELECT * FROM configuracion INNER JOIN empresa_usuario ON c_consecutivo=eu_empresa WHERE eu_usuario="'.$_SESSION['uid'].'"';
            $rs=setq($sql) or die($sql);
            echo '<tr>
              <th>Empresa</th>
              <td>
                <form method="post">
                  <select name="empresa" required autofocus onchange="submit();">';
                    echo '<option value=""></option>';
                    while($rw=$rs->fetch_array()){
                      if($this->model->empresa == $rw['c_consecutivo']) $check = "selected";
                      else $check = "";
                      echo '<option value="'.$rw['c_consecutivo'].'" '.$check.'>'.$rw['c_id'].'</option>';
                    }
                  echo '</select>
                </form>
              </td>
            </tr>';
            echo '<form method="post" enctype="multipart/form-data" action="?modulo=gastos&accion='.$accion.'" onsubmit="return checkSubmitedit();">
              <input type="hidden" name="empresa" value="'.$this->model->empresa.'" />
              <tr><th>Descripción</th><td><input type="text"  maxlength="100" name="descripcion" value="'.$this->model->descripcion.'"></td></tr>
              <tr>
                <th>importe</th>
                <td><input type="number" step="0.01"  id="importe" name="importe" value="'.$this->model->importe.'"></td>
              </tr>
              <tr><th>Categoría</th>
                <td>
                  '.menu_select_db('gastos_categoria','gc_id','gc_nmb',$this->model->categoriag,'categoriag','gc_estatus = "A"',  false, false, false, true).'
                </td>
              </tr>
              <tr><th>Cta. Origen</th>
                <td>
                  '.menu_select_db('cuentas','c_id','c_aliasc',$this->model->cuenta,'cuenta','c_estatus = "A" AND c_empresa = "'.$this->model->empresa.'"',  false, false, false, true).'
                </td>
              </tr>
              <tr>
                <th>Asignado a</th>
                <td><label for="tagsc"><input id="tagsc"  type="text" name="adignado" value="'.$this->model->asignado.'"></label></td>
              </tr>
              <tr>
                <th>Gasto en una sola exhibición</th>
                <td><input type="checkbox" name="unitario" value=" "></td>
              </tr>';
              echo'<tr><td colspan="2"><input type="submit" name="save" value=" " id="guardar" class="botont"></td></tr>
            </form>
          </table>';
        echo '</center></div>';
      }
      function editcat(){
        $accion = 'insertcat';
        if (isset($this->model->id)){$accion = 'updatecat&id='.$_GET['id'];}
        ?><script language="JavaScript">
          function checkSubmitedit() {
            document.getElementById("guardar").value = "JD";
            document.getElementById("guardar").disabled = true;
            return true;
          }
        </script><?php
        echo '<div class="div2" id="bboton01">
          <input type=button value=" " onClick="javascript:history.go(-1);" id="atras" class="botonb">
        </div></div>';
        echo '<center>
          <form method="post" action="?modulo=gastos&accion='.$accion.'" onsubmit="return checkSubmitedit();">
            <table border="0" cellspacing="1" width="50%" class="lista">
              <thead><tr><th colspan="2">Gastos</th></tr><thead>';
              echo '<tr><td>Descripción</td><td><input type="text" name="nmbcat" maxlength="50" value="'.$this->model->nmb.'" autofocus></td></tr>';
              echo '<tr><td colspan="2"><input type="submit" name="save" value=" " id="guardar" class="botont"></td></tr>
            </table>
          </form>
        </center>
        </div>';
      }
    */
    function show() {
      $botones = "";
      $atras = '
        <a href="?modulo=gastos&accion=index"  accesskey="r">
          <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atrás </button>
        </a>';
        if($this->model->estatus == 'P'){
          $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setgasto.php?id='.$this->model->id.'" href="javascript:;" >
            <button type="button" class="btn btn-primary ml-1"><i class="fa fa-pen"></i> Cambiar</button>
          </a>';
        }
        if($this->model->estatus == 'X' || $this->model->estatus == 'P'){
          if($this->model->tipo == "V"){
            $idcxp = busca($this->model->id,'cxpagar','cp_tipo = "V" AND cp_idref','cp_id');
            $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setegreso.php?from=gastos&idcxp='.$idcxp.'&rand='.rand(1,100).'" href="javascript:;" >
              <button type="button" class="btn btn-success ml-1"><i class="fa fa-check"></i> Finalizar </button>
            </a>';
          }else{
            $botones .= '<a data-fancybox data-type="ajax" data-src="popup/aplicagasto.php?id='.$this->model->id.'" href="javascript:;" >
              <button type="button" class="btn btn-success ml-1"><i class="fa fa-check"></i> Finalizar </button>
            </a>';
          }
        }
        if($this->model->estatus == 'A') $botones .='<button type="button" onclick="confirmacancela();" class="btn btn-danger ml-1"><i class="fa fa-times"></i> Cancelar </button>';

        toolbar($_GET['modulo'], $atras, "", "", $botones);
        echo '<script>
          function confirmacancela(){
            con = confirm("Al cancelar este gasto se insertará el reverso a la cuenta\n ¿Deseas Continuar?");
            if(con == true){
              document.location.href = "?modulo=gastos&accion=cancelar&id='.$this->model->id.'";
            }
          }
        </script>';
      echo '<form name="setcondonar" id="setcondonar" method="get"  >
        <input type="hidden" name="modulo" value="cxpagar" />
        <input type="hidden" name="accion" value="condonarpago" />
        <input type="hidden" name="cuenta" id="idcxpcon" />
      </form>';
      //Sección: E2 Encabezado - Filtros
      echo '<div class="main-card mb-3 mt-2" style="background: white ">
        <div class="card-body row">';
          $cuenta = buscacuenta("P","G",$this->model->id);
          echo '<div class="table table-responsive">
            <table border="0" cellspacing="1" width="50%" class="table table-hover table-striped" >
              <thead class="thead bg-primary text-white pt-1 pb-1">
                <tr><th colspan=4><b>Gastos</b></th></tr>
              </thead>
              <tr>
                <th><b>Descripción</b></th>
                <td>'.$this->model->descripcion.'</td>
                <th><b>Importe</b></th>
                <td><input type="hidden" id="totalg" value="'.$this->model->importe.'">$'.number_format($this->model->importe,2).'</td>
              </tr>';
              $categoria = busca($this->model->categoria,'gastos_categoria','gc_id','gc_nmb');
              echo'<tr>
                <th></b>Categoría</b></th>
                <td>'.$categoria.'</td>
                <th><b>Fecha</b></th>
                <td>'.$this->model->fecha.'</td>
              </tr>
              <tr>
                <th><b>Genero</b></th>
                <td>'.$this->model->user.'</td>
                <th><b>Cta Origen </b></th>
                <td>'.$cuenta.'</td>
              </tr>
            </table>
            
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
                    pageLength: "50",
                    
                });
              });
            </script>';
            /*
              ?>
              <script language="JavaScript">
                function checkSubmitinsertaccion() {
                  document.getElementById("guardar").value = "JD";
                  document.getElementById("guardar").disabled = true;
                  return true;
                }
              </script>
              <?php
              if($this->model->unitario==1){
                if(isset($this->model->add) && $this->model->add>0){
                  echo'<table border="0" cellspacing="1" width="50%" class="lista">';
                    echo '<tr>
                      <td>
                        <a href="adjuntos/'.busca($this->model->add,'adjuntos','a_id','a_nmb').'.'.busca($this->model->add,'adjuntos','a_id','a_ext').'" target="_BLANK">'.busca($this->model->add,'adjuntos','a_id','a_nmb').'.'.busca($this->model->add,'adjuntos','a_id','a_ext').'</a>
                      </td>
                    </tr>';
                }
              }else{
                $accion = 'insertcomp';
                $tip = array("1"=>"Gasto","2"=>"Reintegro" );
                $aplicacancela =" "; 
                if($this->model->estatus=="A" || $this->model->estatus=="C") $aplicacancela =' style="display:none"';
                echo '<center>
                <table border="0" cellspacing="1" width="90%">
                <tr valign="top"><td width="40%">
                <table '.$aplicacancela.' border="0" cellspacing="1" width="100%" class="lista">
                <thead><tr><th colspan="2"><b>Comprobación de Gasto</b></th></tr></thead>
                <tr><th><center>Gastos</th><td><input type="hidden" name="id" value="'.$this->model->id.'">'.$this->model->id.'</td></tr>';
                  echo '<input type="hidden" name="id" value="'.$this->model->id.'">';
                  echo '<tr><th><center>Detalle</th><td> <input type="text" value="" name="detalle" maxlength="15" autofocus></td></tr>';
                  echo '<tr><th><center>importe</th><td> <input type="number" step="0.01" value="" name="importe" id="importeg"></td></tr>';
                  echo '<tr><th><center>Tipo</th><td>
                      <select name="tipo" id="tipo" >
                      <option value="1">'.$tip[1].'</option>
                      <option value="2">'.$tip[2].'</option>
                      </select>
                  </td></tr>';
                echo '<tr><th><center>Adjuntar Comprobante</th><td><input type="file" name="comprobante" /></td></tr>';
                echo'<tr><td colspan="2"><center><input type="submit" name="submit" value="" id="guardar" class="botont"></td></tr>
                </table></div></form>
                </td>
                <td >
                <table border="0" cellspacing="1" width="100%" class="lista">
                <thead><tr>
                <th>ID</th>
                <th>Detalle</th>
                <th>importe</th>
                <th>Tipo</th>
                <th>Comprobante Adj.</th>
                <th>Usuario</th>
                <th '.$aplicacancela.'></th>
                </tr><thead><tbody>';
                $tot=0;

                while ($row = $this->model->result->fetch_array()) {
                  echo '
                  <tr>
                  <th>'.$row['gd_id'].'</td>
                  <td>'.$row['gd_descripcion'].'</td>
                  <td>$'.number_format($row['gd_monto'],2).'</td>';
                  $tot+=$row['gd_monto'];
                  if($row['gd_tipo']==1) echo '<td>Gasto</td>';
                  else echo '<td>Reintegro</td>';

                  if($row['gd_add']>0){
                              echo '<center>
                              <td>
                                  <a href="adjuntos/'.busca($row['gd_add'],'adjuntos','a_id','a_nmb').'.'.busca($row['gd_add'],'adjuntos','a_id','a_ext').'" target="_BLANK">'.busca($row['gd_add'],'adjuntos','a_id','a_nmb').'.'.busca($row['gd_add'],'adjuntos','a_id','a_ext').'</a><br><br>
                              </td>
                              ';
                }
                else{
                  echo "<td></td>";
                }

                echo'<td width="5%">'.$row['gd_usuario'].'</td>
                  <td '.$aplicacancela.' width="15%"><a href="?modulo=gastos&accion=deletecomp&id='.$this->model->id.'&idd='.$row['gd_id'].'" onclick="this.onclick = function(){return false;}">
                  <input type="button" value="" id="borrar" class="botont"></a></td>
                <tr> ';
                }
                if($tot<$this->model->importe){$col="#FF0505";}
                else{ $col="#45C431";}
              ?>
              <script>
                  var x;
                      x=$(document).ready(inicio);
                      function inicio(){
                        var x;
                        x=$("#importeg").change(importes);
                        x=$("#guardar").click(importes);
                      }
                      function importes(){
                        var importeg=$("#importeg").val();
                        var importet=$("#importet").val();
                        var totalg=$("#totalg").val();
                        importeg=parseFloat(importeg);
                        importet=parseFloat(importet);
                        totalg=parseFloat(totalg);
                        var total=importet+importeg;
                        if(total<=totalg){
                          return true;
                        }
                        else{
                          alert("Se paso de la cantidad del gasto");
                          return false;
                        }

                      }


              </script>
              <?php
                echo '<tr><th colspan="2">TOTAL COMPROBADO</td><td style="background:'.$col.'"><input type="hidden" name="totalg" id="importet" value="'.$tot.'">$'.number_format($tot,2).'</td></tr>';
                echo '<tbody></table></td></center>';
                }
            */
          echo '</div>';
          if(!empty($this->model->adjunto) && file_exists('Adjuntos/'.$_SESSION['emp'].'/'.$this->model->adjunto)){
            echo'<div class="col-12 col-md-12 row">
              <center>
                <a target="_BLANK" href="../Adjuntos/'.$_SESSION['emp'].'/'.$this->model->adjunto.'">
                  <button type="button" class="btn btn-secondary"><i class="fas fa-paperclip"></i> '.$this->model->adjunto.'</button>
                </a>
              </center>
            </div>';
          }
        echo'</div>
      </div>';
    }
    function browseGastof($pagex,$estatus,$nombre,$rubro,$page) {
      $iteracionp = array("S"=>"SEMANAL","Q"=>"QUINCENAL","M"=>"MENSUAL","U"=>"UNICA");
      $estatusp = array("A"=>"ACTIVO","I"=>"INACTIVO","F"=>"FINALIZADO","C"=>"CANCELADO");
        if($estatus == "T") $selt = "selected";
        elseif($estatus == "F") $self = "selected";
        elseif($estatus == "A") $sela = "selected";
        elseif($estatus == "I") $selc = "selected";
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
              $("#categoria").focus();
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
        $botones="";
        $nuevo ='<a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setgastofijo.php" href="javascript:;" >
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>';
         $botones .= '<a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setgastocategoria.php?o=f" href="javascript:;" >
            <button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Agregar categoria
            </button>
          </a>';
          
        $filtro = '<form class="form-inline" role="form" method="post" action="?modulo=gastos&accion=gastosfijos">
                <div class="mb-5">
                  <label for="tipom">Categoria:</label>
                  <input type="text" class="form-control" id="categoria" name="categoria" placeholder="Nombre de la categoria" onfocus="this.select();" value="'.$rubro .'" />
                </div>
                <div class="mb-5">
                  <label for="tipom">Estatus:</label>
                  <select class="form-control" name="estatus" id="estatus" >
                    <option value="" '.$selt .' >Todos</option>
                    <option value="A" '.$sela .' >Activos</option>
                    <option value="F" '.$self .' >Finalizados</option>
                    <option value="I" '.$selc .' >Cancelados</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar 
                  </button>
                  <a href="?modulo=gastos&accion=gastosfijos">
                    <button type="button" class="btn btn-warning">
                      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                    </button>
                  </a>
                </div>
              </form>';
          
          toolbar($_GET['modulo'], "", $filtro, $nuevo, $botones);    
          echo '
          <div class="main-card mb-2 card mt-2">
          <div class="card-body">
          <center>
            <div class="table-responsive text-medium">
              <table class="table table-striped" id="myTable">
                <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
                  <tr>
                    <th align=center style="width:5%"><b></b></th>
                    <th align="center"><b>Nombre</b></th>
                    <th align=center><b>Categoria</b></th>
                    <th align=center><b>Iteracion</b></th>
                    <th align=center><b>Fecha programada</b></th>
                    <th align=center><b>Importe</b></th>
                    <th align=center><b>Estatus</b></th>
                  </tr>
                </thead>
                <tbody>';
                  while ($row = $this->model->result->fetch_array()) {
                    echo '<tr>
                      <th align=center>';
                        echo'<a data-fancybox data-type="ajax" data-src="popup/setgastofijo.php?id='.$row['gf_id'].'" href="javascript:;" >
                          <button class="btn btn-info"><i class="fas fa-chevron-circle-right"></i></button>
                        </a>';
                      echo'</th>';
                      echo '<td align=center>'.$row['gf_nmb'].'</td>';
                      echo '<td align=center>'.busca($row['gf_categoria'],'gastos_categoria','gc_id','gc_nmb').'</td>';
                      echo '<td align=center>'.$iteracionp[$row['gf_iteracion']].'</td>';
                      echo '<td align=center>'.fecha_formato($row['gf_fechaap'],false,false).'</td>';
                      echo '<td align=center>'.number_format($row['gf_importe'],2).'</td>';
                      if($row['gf_estatus']=='A')
                        echo '<td align=center style="background:#25FF3B" >'.$estatusp[$row['gf_estatus']].'</td>';
                      else if($row['p_estatus']=='F')
                        echo '<td align=center style="background:#54C6FF" >'.$estatusp[$row['gf_estatus']].'</td>';
                      else
                        echo '<td align=center style="background:#FF4A32" >'.$estatusp[$row['gf_estatus']].'</td>';
                    echo '</tr>';
                  }
                echo'</tbody>
              </table>
            </div>
          </center>
          
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
                pageLength: "50",
                
            });
          });
          </script>';
          /*
            if($_REQUEST['categoria']){
            }else{ 
              echo '<div class="col-md-12 text-xs-center">
                <div class="mb-3">
                  <nav aria-label="Page navigation">
                    <ul class="pagination">
                      <li class="page-item">
                        <a class="page-link" href="?modulo=gastos&accion=gastosfijos&page='.($_GET['page']-1).'" aria-label="Previous">
                          <span aria-hidden="true">&laquo; Ant</span>
                          <span class="sr-only">Anterior</span>
                        </a>
                      </li>';
                      $numres = 50;
                      $nr = $this->model->resultt->num_rows;
                      $np = $nr/$numres;
                      $paginaa = $page-1;
                      $pagina = $page+1;
                      $sqlpg = 'SELECT COUNT(*) FROM gastosf WHERE gf_empresa = "'.$_SESSION['emp'].'" AND gf_id <> 0';
                      $resultpg = setq($sqlpg);
                      list($numg) =  $resultpg->fetch_array();
                      $pageres = ceil($numg/$numres);
                      //die($numres);
                      if($pageres>10){
                        if($_GET['page'] == 0) { $min = 1; $nombre = "Inicio";}
                        else {$min = $_GET['page']-1; $nombre = "Inicio...";}
                        if($min <= 1) $min = 1;
                        if($_GET['page'] == ($pageres-1)) $max = ($pageres-1);
                        else $max = $_GET['page']+3;
                        if($max >= ($pageres-1)) $max = ($pageres-1);
                        if($_GET['page'] == 0) $active = "active";
                        else $active = "";
                        echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=gastos&accion=gastosfijos&page=0">'.$nombre.'</a></li>';
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
                        echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=gastos&accion=gastosfijos&page='.$i.'">'.$nombre.'</a></li>';
                      }
                      if($pageres<9){
                      }else{
                        if($_GET['page'] == ($pageres-5)) $nombre = ($pageres-2);
                        else $nombre = '... '.($pageres-2);
                        if($_GET['page'] == $i) $active = "active";
                        else $active = "";
                        if($_GET['page'] >= ($pageres-4)) echo '';
                        else echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=gastos&accion=gastosfijos&page='.($pageres-2).'">'.$nombre.'</a></li>';
                        echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=gastos&accion=gastosfijos&page='.($pageres-1).'">'.($pageres-1).'</a></li>';
                      }
                      echo '<li class="page-item">
                        <a class="page-link" href="?modulo=gastos&accion=gastosfijos&page='.($_GET['page']+1).'" aria-label="Next">
                          <span aria-hidden="true">Sig &raquo;</span>
                          <span class="sr-only">Siguiente</span>
                        </a>
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>'; 
            }
          */
        echo'</div>
      </div>';
    }
  }
?>