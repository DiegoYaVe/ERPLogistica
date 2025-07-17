<?php
  //ini_set('display_errors','1');
  $GLOBALS['menu'] = "Egresos";
  class cxpagar {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelcxpagar(isset($obj));
    }
    function index() {
      if(!isset($_GET['page']))    $_GET['page'] = 1;
      $lastdt = date('Y-m-d',strtotime(busca('A','cxpagar','cp_estatus = "N" OR cp_estatus','MIN(cp_fini)')));
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime(min(date('Y-m-d'),$lastdt)));
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('next sunday'));
      $this->model->setgastosfijos($_REQUEST['ffin']);
      /*
        do {
          pprestamos($_REQUEST['ffin']);
          $nump = pproyecciones($_REQUEST['ffin']);
        } while ($nump > 0);
      */
      if(!isset($_POST['estatus']))    $_POST['estatus'] = "T";
      if(!isset($_POST['tipo']))    $_POST['tipo'] = NULL;
      $this->model->result($_GET['page'],$_REQUEST['fini'],$_REQUEST['ffin'],$_POST['estatus'],$_POST['tipo'],$_POST['proveedor']);
      $this->view = new viewcxpagar($this->model);
      $this->view->browse($_GET['page'],$_REQUEST['fini'],$_REQUEST['ffin'],$_POST['estatus'],$_POST['tipo'],$_POST['proveedor']);
    }
    function show(){
      $this->model->selectpagos($_GET['idcxp']);
      $this->model->select($_GET['idcxp']);
      $this->view = new viewcxpagar($this->model);
      $this->view->show();
    }
    function pago() {
      if (!isset($_GET['id'])) $_GET['id'] = NULL;
      if (!isset($_REQUEST['cuenta'])) $_REQUEST['cuenta'] = NULL;
      if(!isset($_POST['tipo']))    $_POST['tipo'] = NULL;
      //$this->model->select($_GET['id']);
      $this->view = new viewcxpagar($this->model);
      $this->view->pago($_REQUEST['cuenta'],$_POST['tipo']);
    }
    function condonar() {
      die();
      if (!isset($_GET['id'])) $_GET['id'] = NULL;
      if (!isset($_REQUEST['cuenta'])) $_REQUEST['cuenta'] = NULL;
      if(!isset($_POST['tipo']))    $_POST['tipo'] = NULL;
      //$this->model->select($_GET['id']);
      $this->view = new viewcxpagar($this->model);
      $this->view->condonar($_REQUEST['cuenta'],$_POST['tipo']);
    }
    function insertpago(){
      $abonado=busca($_POST['cuenta'],'cxpagar','cp_id','cp_abonado');
      if(!isset($abonado)) $abonado=0;
      $fecha=date('Y-m-d H:i:s');
      $gen=$_SESSION['uid'];
      $tipo="P";
      $total=$abonado;
      $solicitado=busca($_POST['cuenta'],'cxpagar','cp_id','cp_importe');
      $sqlp="SELECT * FROM cuentas INNER JOIN cortes ON cu_id=c_cuenta
            WHERE cu_estatus='A' AND cu_tipo!='A' AND c_estatus='A' ORDER BY cu_orden ASC, cu_id ASC";
      $resultp=setq($sqlp) or die($sqlp);
      while($rwp=$resultp->fetch_array()){
        if($_POST[$rwp['cu_id'].'c']){
          $corte=busca($rwp['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_id');
          $referencia=busca($_POST['cuenta'],'cxpagar','cp_id','cp_idref');
          $tipom=busca($_POST['cuenta'],'cxpagar','cp_id','cp_tipo');

          $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
          $result = setq($sql);
          list($id) = $result->fetch_array();
          $id++;

          $this->model->setdata($id,$corte,$fecha,$gen,$tipo,$referencia,$_POST[$rwp['cu_id']],$tipom,$_POST['cuenta']);
          $this->model->insert();

          $total=$total+$_POST[$rwp['cu_id']];
        }
      }
      $proyecto = busca($_POST['cuenta'],'cxpagar','cp_id','cp_proyecto');
      /*if(busca($proyecto,'bitacora','b_proyecto','b_id')){
        addbitacorad("PAGO DE ORDEN DE COMPRA ".busca($_POST['cuenta'],'cxpagar','cp_id','cp_idref').' POR $'.number_format($total,2),busca($proyecto,'bitacora','b_proyecto','b_id'));
      }*/

      if($total==$solicitado){
        $sqli = 'UPDATE cxpagar SET cp_estatus = "F", cp_abonado="'.$total.'", cp_ffin ="'.$fecha.'" WHERE cp_id="'.$_POST['cuenta'].'"';
        setq($sqli) or die($sqli);
        /*if(busca($proyecto,'bitacora','b_proyecto','b_id')){
          addbitacorad("ORDEN DE COMPRA FINALIZADO ".busca($_POST['cuenta'],'cxpagar','cp_id','cp_idref'),busca($proyecto,'bitacora','b_proyecto','b_id'));
        } */
      }else{
          $sqli = 'UPDATE cxpagar SET cp_estatus = "A", cp_abonado="'.$total.'" WHERE cp_id="'.$_POST['cuenta'].'"';
          setq($sqli) or die($sqli);
          /*
          $existeproyeccion = busca($_POST['cuenta'],'proyeccionesd INNER JOIN proyecciones ON p_id = pd_proyeccion ','p_estatus = "N" AND pd_referencia','pd_id');
          if($existeproyeccion){
            $sqlupproyecccion = 'UPDATE proyeccionesd SET
                                pd_importe = "'.$solicitado-$total.'"
                                WHERE pd_id = "'.$existeproyeccion.'"';
            setq($sqlupproyecccion);
          } */
      }

      if($_GET['remision'])   $link='Location:?modulo=panel&accion=pagos&id='.$_GET['remision'];
      else    $link='Location:?modulo=cxpagar&accion=index';
      header($link);
    }
    function condonarpago(){
      $grupo=busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
      if(($grupo!="ADMIN") AND ($grupo!="SUPER"))
        die(alert_back("El usuario no tiene permiso de realizar condonaciones",true));

      $sqli = ' UPDATE cxpagar SET
                cp_estatus = "C",
                cp_observaciones="PAGO CONDONADO",
                cp_ffin ="'.date('Y-m-d H:i:s').'"
                WHERE cp_id="'.$_REQUEST['cuenta'].'" ';
      setq($sqli) or die($sqli);
      
      $tipo = busca($_REQUEST['cuenta'],'cxpagar','cp_id','cp_tipo');
      if($tipo == "V" || $tipo == "G"){
        $idref = busca($_REQUEST['cuenta'],'cxpagar','cp_id','cp_idref');
        $sqlg = 'UPDATE gastos SET g_estatus = "C" WHERE g_id = "'.$idref.'"';
        setq($sqlg) or die($sqlg);
      }

      $link='?modulo=cxpagar&accion=index';

      redirect($link);
    }
    function insertcxpagar(){
      $id = $this->model->insertcxpagar($_POST['empresa'],$_POST['proveedor'],NULL,$_POST['tipo'],$_POST['importe'],$_POST['fecha'],$_POST['observaciones']);
      $this->model->addproyeccion($id);
      redirect('?modulo=cxpagar&accion=index');
    }
    function cancelarpago(){
      $grupo=busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
      if(($grupo!="ADMIN")AND($grupo!="FINANZAS"))
        die(alert_back("El usuario no tiene permiso de realizar condonaciones",true));
      
      if(busca($_GET['corte'],'cortes','c_id','c_estatus') == "A"){
        $cuenta = busca($_GET['corte'],'cortesd','cd_id = "'.$_GET['id'].'" AND cd_corte','cd_cuenta');
        $sqli = 'DELETE FROM cortesd WHERE cd_corte="'.$_GET['corte'].'" AND cd_id="'.$_GET['id'].'"';
        setq($sqli) or die($sqli);

        $sql = 'SELECT cd_monto FROM cortesd WHERE cd_cuenta = "'.$cuenta.'"';
        $result = setq($sql) or die($sql);
        $abonado = 0;
        while($row = $result->fetch_array()){
          $abonado+=$row['cd_monto'];
        }

        $sqlu = 'UPDATE cxpagar SET cp_abonado = "'.$abonado.'" WHERE cp_id = "'.$cuenta.'"';
        setq($sqlu) or die($sqlu);
      }else{
        alert_back("No es posible cancelar el pago, porque no corresponde al corte actual de la cuenta ",true);
      }

      if($_GET['remision'])   $link='Location:?modulo=cxpagar&accion=pago&remision='.$_GET['remision'];
      else    $link='Location:?modulo=cxpagar&accion=index';

      redirect($link);
    }
    function insertop(){
      $concesionario=busca($_POST['concesionario'],'concesionarios','c_id','c_nmb');
      if(!isset($concesionario)) die(alert_back('El nombre del cliente es incorrecto, por favor intente de nuevo',true));
      else $nombre=$concesionario;

      $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-OP';
      $folioint = getmax('op_folio','ordenesp','op_empresa = "'.$_SESSION['emp'].'"');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }

      $user=$_SESSION['uid'];
      $fecha=date('Y-m-d H:i:s');
      $proyecto = $_GET['tablero'];

      $this->model->setDataOp(NULL,$_POST['tipo'],$nombre,$proyecto,$_POST['importe'],$user,$fecha,$folioint,$_POST['concepto']);
      $idop = $this->model->insertOp();
      $provpredet = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_id');
      if($_POST['tipo'] == "O"){
        $idcp = $this->model->insertcxpagar($_SESSION['emp'],$provpredet,$idop,"O",$_POST['importe'],$fecha,$_POST['concepto']);
        $this->model->addproyeccion($idcp);
      }
      else {
        include_once('cxcobrar.php');
        $cxc = new modelcxcobrar();
        if(isset($_POST['direccion'])){
          $idcp = $this->model->insertcxpagar($_SESSION['emp'],$provpredet,$idop,"P",$_POST['importe'],$fecha,"PAGO DE PRESTAMO A ".$concesionario);
          $this->model->addproyeccion($idcp);
          $idcc = $cxc->insertcxcobrar($_SESSION['emp'],$_POST['concesionario'],$idop,"P",$_POST['importe'],$_POST['fechap'],"COBRO DE PRESTAMO A ".$concesionario);
          $cxc->addproyeccion($idcc);
        }else{
          $idcc = $cxc->insertcxcobrar($_SESSION['emp'],$_POST['concesionario'],$idop,"P",$_POST['importe'],$fecha,"RECEPCIÓN DE PRESTAMO DE ".$concesionario);
          $cxc->addproyeccion($idcc);
          $idcp = $this->model->insertcxpagar($_SESSION['emp'],$provpredet,$idop,"P",$_POST['importe'],$_POST['fechap'],"PAGO DE PRESTAMO A ".$concesionario);
          $this->model->addproyeccion($idcp);
        }
      }

      $sql = 'UPDATE ordenesp SET op_cxpagar = "'.$idcp.'" WHERE op_id = "'.$idop.'"';
      setq($sql);

      if(isset($_GET['tablero'])) $link = '?modulo=tableros&accion=show&id='.$_GET['tablero'];
      else $link = '?modulo=cxpagar&accion=index';
      redirect($link);
    }
    function programar(){
      $this->model->updatecxp("C", "PAGO PROGRAMADO", $_GET['idcxp']);
      for($i=0;$i<sizeof($_POST['fprog']);$i++){
        $id = $this->model->insertcxpagar($_POST['empresa'],$_POST['idpv'],$_POST['ref'],$_POST['tipo'],$_POST['monto'][$i],$_POST['fprog'][$i],$_POST['observaciones']);
        $this->model->addproyeccion($id);
        $this->model->insertprog($_GET['idcxp'],$id,$_POST['monto'][$i]);
      }
      redirect('?modulo=cxpagar&accion=index');
    }
  }

  /* -----------------------------------------------MODEL---------------------------------------------------------------------- */

  class modelcxpagar {
    function result($page,$fini,$ffin,$estatus,$tipo,$proveedor){
      $page--;
      $pageresult = 75;
      $pag = ' LIMIT '.($page * $pageresult).','.$pageresult;
      $sqlf = ' WHERE DATE(cp_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      //if($proveedor) $sqlf.=' AND cp_proveedor = "'.$proveedor.'" ';
      if($proveedor && !empty($proveedor)){
        $sqlP=' INNER JOIN proveedores ON cp_proveedor = p_id  ';
        $sqlf.=' AND (p_nmb LIKE "%'.$proveedor.'%" OR p_alias LIKE "%'.$proveedor.'%")';
      }
      else $sqlP = "";

      if($tipo == "O") $sqlf .= ' AND cp_tipo IN ("O","M")';
      elseif($tipo) $sqlf .= ' AND cp_tipo = "'.$tipo.'" ';

      if($estatus){
        if($estatus == "T") $sqlf .= ' AND cp_estatus IN ("N","A")';
        else $sqlf .= ' AND cp_estatus = "'.$estatus.'" ';
      }else $sqlf .= '';

      $sql = 'SELECT cp_fini,cp_importe,cp_abonado,cp_tipo,cp_idref,cp_proyecto,cp_estatus,cp_id,cp_proveedor,cp_observaciones
              FROM cxpagar '.$sqlP.' '.$sqlf.' AND cp_importe!=0 AND cp_empresa = "'.$_SESSION['emp'].'" ';
      $sql.= ' ORDER BY cp_fini DESC,cp_id DESC'.$pag;
      $this->result = setq($sql) or die($sql);

      $sql = 'SELECT COUNT(*) FROM cxpagar '.$sqlP.' '.$sqlf.' AND cp_importe!=0 ORDER BY cp_fini DESC, cp_id DESC';
      $this->resultt = setq($sql) or die($sql);
      list($nr) = $this->resultt->fetch_array();
      $this->np = $nr/$pageresult;
    }
    function select($id) {
      $sql = 'SELECT * FROM cxpagar WHERE cp_id= "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['cp_id'];
      $this->proveedor = $row['cp_proveedor'];
      $this->idref = $row['cp_idref'];
      $this->tipo = $row['cp_tipo'];
      $this->monto = $row['cp_importe'];
      $this->abonado = $row['cp_abonado'];
      $this->estatus = $row['cp_estatus'];
      $this->fechaini = $row['cp_fini'];
      $this->fechafin = $row['cp_ffin'];
      $this->observaciones = $row['cp_observaciones'];
    }
    function setdata($id,$corte,$fecha,$gen,$tipo,$ref,$monto,$tipom,$cuenta){
      mb_internal_encoding("UTF-8");
      $this->id = $id;
      $this->corte = $corte;
      $this->fecha = $fecha;
      $this->gen = $gen;
      $this->tipo = $tipo;
      $this->ref = $ref;
      $this->monto = $monto;
      $this->tipom = $tipom;
      $this->cuenta = $cuenta;
    }
    function insert() {
      $sql = 'INSERT INTO cortesd SET
              cd_id = "'.$this->id.'",
              cd_corte = "'.$this->corte.'",
              cd_fecha = "'.$this->fecha.'",
              cd_gen = "'.$this->gen.'",
              cd_tipo = "'.$this->tipo.'",
              cd_idref = "'.$this->ref.'",
              cd_monto = "'.$this->monto.'",
              cd_tipom = "'.$this->tipom.'",
              cd_cuenta = "'.$this->cuenta.'" ';
      setq($sql) or die($sql);
    }
    function insertcxpagar($empresa,$cliente,$referencia,$tipo,$importe,$fini,$observaciones){
      $sql = 'INSERT INTO cxpagar SET
              cp_empresa = "'.$empresa.'",
              cp_proveedor = "'.$cliente.'",
              cp_idref = "'.$referencia.'",
              cp_tipo = "'.$tipo.'",
              cp_importe = "'.$importe.'",
              cp_observaciones = "'.$observaciones.'",
              cp_abonado = "0",
              cp_estatus = "N",
              cp_fini = "'.$fini.'"';
      setq($sql);
      $idc = getmax('cp_id','cxpagar','cp_empresa = "'.$empresa.'"',false);
      return($idc);
    }
    function setgastosfijos($ffin){
      $proveedor=1;
      $tipocx="P";
      $sql='SELECT * FROM gastosf WHERE gf_estatus="A" AND gf_fechaap <= "'.$ffin.'" AND gf_empresa = "'.$_SESSION['emp'].'"';
      $result=setq($sql);
      $resultn=setq($sql);
      $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_id');
      while($row=$result->fetch_array()){
        $idcp = $this->insertcxpagar($_SESSION['emp'],$proveedor,$row['gf_id'],"F",$row['gf_importe'],$row['gf_fechaap'],$row['gf_nmb']);
        $this->addproyeccion($idcp);
        $this->setgastofcxpagar($row['gf_id'],$idcp,$row['gf_importe'],$row['gf_nmb'],$row['gf_fechaap']);
        ////////////////////// SE ACTUALIZA LA PROYECCION
        $fecha = $row['gf_fechaap'];
        //$elementos = explode('-', $ffin);
        if($row['gf_iteracion']=='M'){
          $nuevafecha = strtotime ( '+1 month' , strtotime ( $fecha ) ) ;
          $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
        }elseif($row['gf_iteracion']=='Q'){
          $fectemp = strtotime ( 'first day of this month' , strtotime ( $fecha ) ) ;
          $fectemp = date ( 'Y-m-d' , $fectemp );
          $fectemp = strtotime ( '+14 day' , strtotime ( $fectemp ) ) ;
          $fectemp = date ( 'Y-m-d' , $fectemp );
          if($fecha<=$fectemp){
            $nuevafecha = strtotime ( 'last day of this month' , strtotime ( $fecha ) ) ;
            $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
          }else{
            $nuevafecha = strtotime ( '+15 day' , strtotime ( $fecha ) ) ;
            $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
          }
        }elseif($row['gf_iteracion']=='S'){
          $nuevafecha = strtotime ( '+7 day' , strtotime ( $fecha ) ) ;
          $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
        }

        if($row['gf_iteracion']=='U'){
          $sqlu = 'UPDATE gastosf SET gf_estatus = "F" WHERE gf_id="'.$row['gf_id'].'"';
          setq($sqlu);
        }else{
          $sqlu = 'UPDATE gastosf SET gf_fechaap = "'.$nuevafecha.'" WHERE gf_id="'.$row['gf_id'].'"';
          setq($sqlu);
        }
      }
      $numc = $resultn->num_rows;
      return($numc);
    }
    function setgastofcxpagar($gastof,$cxpagar,$importe,$nmb,$fecha){
      $sqli = 'INSERT INTO gastosf_cxpagar SET
                gfx_gastof = "'.$gastof.'",
                gfx_cxpagar = "'.$cxpagar.'",
                gfx_importe = "'.$importe.'",
                gfx_nmb = "'.$nmb.'",
                gfx_fecha = "'.$fecha.'"';
      setq($sqli);
    }
    function setDataOp($id,$tipo,$nombre,$proyecto,$importe,$user,$fecha,$folio,$concepto){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
      $simboldi = array('\"',"\'","\#","\$","\%", "\&","\/","\(","\)","\=","\?","\¡","\*","\+","\~","\^","\[","\°","\|","\{","\}","\[","\]");

      $this->id= $id;
      $this->tipo=$tipo;
      $this->nombre=str_replace($simbol,$simboldi, mb_strtoupper(trim($nombre)));
      $this->proyecto= $proyecto;
      $this->importe= $importe;
      $this->user= $user;
      $this->fecha= $fecha;
      $this->folio = $folio;
      $this->concepto = clearvmayus($concepto);
    }
    function insertOp(){
      $sql='INSERT INTO ordenesp SET
            op_tipo="'.$this->tipo.'",
            op_nmb="'.$this->nombre.'",
            op_proyecto="'.$this->proyecto.'",
            op_importe="'.$this->importe.'",
            op_estatus="N",
            op_fechac="'.$this->fecha.'",
            op_folio = "'.$this->folio.'",
            op_empresa = "'.$_SESSION['emp'].'",
            op_concepto = "'.$this->concepto.'",
            op_userc="'.$this->user.'"';
      setq($sql);

      $idc = getmax('op_id','ordenesp',NULL,false);
      return($idc);
    }
    function selectpagos($idcxp){
      $sql = 'SELECT * FROM egreso_cxpagar INNER JOIN egresos ON ec_egreso = e_id 
              WHERE ec_cxpagar = "'.$idcxp.'" AND e_estatus != "C" ORDER BY ec_fapli DESC';
      $this->resultpg = setq($sql) or die($sql);
      $this->resultpg1 = setq($sql) or die($sql);
    }
    function insertprog($padre,$hijo,$monto){
      $sql = 'INSERT INTO cxpagar_programacion SET 
              cxp_padre = "'.$padre.'",
              cxp_hijo = "'.$hijo.'", 
              cxp_monto = "'.$monto.'"';
      setq($sql);
    }
    function updatecxp($est,$obs,$idcxp){
      $sql = 'UPDATE cxpagar SET
              cp_estatus = "'.$est.'",
              cp_observaciones = "'.$obs.'"
              WHERE cp_id = "'.$idcxp.'"';
      setq($sql);
    }
    function addproyeccion($idcxp){
      $this->select($idcxp);
      $feccxp = strtotime(date("Y-m-d",strtotime($this->fechaini)));
      $fechahoy = strtotime(date("Y-m-d",time()));
      $fechaliquidacion = "";

      if($fechahoy >= $feccxp) $fechaliquidacion = date("Y-m-d");
      else $fechaliquidacion = date("Y-m-d",strtotime($this->fechaini));
      
      $sqlproy = 'SELECT p_id,p_fechaap,p_fechacierre FROM proyecciones WHERE p_estatus = "N"';
      $resultproy = setq($sqlproy);
      list($idp,$fap,$fcp) = $resultproy->fetch_array();
      
      if($fechaliquidacion >= $fap && $fechaliquidacion <= $fcp) {
        $feccxp = date("Y-m-d",strtotime($this->fechaini));
        if($_SESSION['emp'] == "1"){
          include_once('cuentas.php');
          $proyeccion = new modelcuentas();

          $desc = $this->observaciones;
          if($this->tipo == "C"){
            $refer = busca($this->proveedor,'proveedores','p_id','p_alias');
            $desc.=' '.busca(busca($this->idref,'ordenesc','o_id','o_tablero'),'crm_tableros','ct_id','ct_nmb').' - '.$refer;
          }
          elseif($this->tipo == "T"){
            $refer = busca($this->proveedor,'proveedores','p_id','p_alias');
            $desc.=' '.busca($this->idref,'tae','t_id','t_fecha').' - '.$refer;
          }
          elseif($this->tipo == "M"){
            $tp = 'Orden de Compra Menor';
            $refer = busca($this->proveedor,'empresas','e_id','e_siglas');
            $desc.=' '.busca(busca($this->idref,'ordenesp','op_id','op_proyecto'),'crm_tableros','ct_id','ct_nmb').' - '.$refer;
          }
          elseif($this->tipo == "O"){
            $refer = busca($this->proveedor,'empresas','e_id','e_siglas');
            $desc.=' - '.busca(busca($this->idref,'ordenesp','op_id','op_proyecto'),'crm_tableros','ct_id','ct_nmb').' - '.$refer;
          }
          else{
            $refer = busca($this->proveedor,'empresas','e_id','e_siglas');
            if($this->tipo == "F") $desc = busca($this->idref,'gastosf','gf_id','gf_nmb');
          }
          $proyeccion->setproyecciond($idp,$feccxp,"P",$desc,$this->monto,$this->monto,$idcxp);
        }
      }
    }
  }

  /* -----------------------------------------------VIEW---------------------------------------------------------------------- */

  class viewcxpagar {
    var $model;
    function __construct($model){
      $this->model = $model;
      ?>
      <script language="JavaScript">
        function checksubmit(){
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
        function checksubmitaplicar(){
          document.getElementById("aplicar").value = "JD";
          document.getElementById("aplicar").disabled = true;
          return true;
        }
      </script>
      <?php
    }
    function browse($page,$fini,$ffin,$estatus,$tipo,$proveedor){
      $tipop = array("C"=>"ORDEN DE COMPRA","M"=>"COMPRA MENOR","T"=>"PAGO TAE","P"=>"PROYECCION","R"=>"PRESTAMO","V"=>"VALE","O"=>"ORDEN DE PAGO","E"=>"RECEPCIÓN DE MERCANCÍA");
      $estatusp = array("N"=>"NUEVA","F"=>"FINALIZADO","C"=>"CANCELADO","A"=>"ABONADO","P"=>"NUEVO VALE");
      $e1 = '';
      $e2 = '';
      $e3 = '';
      $e4 = '';
      $e5 = '';
      $e6 = '';
      if($estatus == "N") $e1 = "selected";
      if($estatus == "F") $e2 = "selected";
      if($estatus == "A") $e3 = "selected";
      if($estatus == "C") $e4 = "selected";
      if($estatus == "T") $e5 = "selected";
      if(!$estatus) $e6 = "selected";
      /*$t1 = '';
      $t2 = '';
      $t3 = '';
      $t4 = '';
      $t5 = '';
      $t6 = '';
      $t7 = '';
      if($tipo == "C") $t1 = "selected";
      if($tipo == "M") $t2 = "selected";
      if($tipo == "T") $t3 = "selected";
      if($tipo == "P") $t4 = "selected";
      if($tipo == "R") $t5 = "selected";
      if($tipo == "A") $t6 = "selected";
      if($tipo == "O") $t7 = "selected";*/


      $t1 = ''; $t2 = ''; $t3 = ''; $t4 = ''; $t5 = ''; $t6 = ''; $t7 = ''; $t8 = ''; $t9 = '';
      if($tipo == "C") $t1 = "selected";
      if($tipo == "M") $t2 = "selected";
      if($tipo == "T") $t3 = "selected";
      if($tipo == "P") $t4 = "selected";
      if($tipo == "R") $t5 = "selected";
      if($tipo == "A") $t6 = "selected";
      if($tipo == "O") $t7 = "selected";
      if($tipo == "F") $t8 = "selected";
      if($tipo == "G") $t9 = "selected";
      //Sección: E1 Encabezado - Botones de acción
        //Sección: E2 Encabezado - Filtros
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

       $nuevo = '<a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setegreso.php?rand='. rand(1,100) .'" href="javascript:;" >
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>';
        $filtro = '
              <form class="form-inline" role="form" method="post" action="?modulo=cxpagar&accion=index">
                <div class="mb-5">
                  <label for="tipom">Desde:</label>
                  <input type="date" name="fini" id="fini" class="form-control" value="'. $fini .'" />
                </div>
                <div class="mb-5">
                  <label for="tipom">Hasta:</label>
                  <input type="date" name="ffin" id="ffin" class="form-control" value="'. $ffin.'" />
                </div>
                <div class="mb-5">
                  <label for="tipo">Tipo:</label>
                  <select class="form-control" name="tipo" id="tipo" >
                    <option value="" '. $t6 .' >Todos</option>
                    <option value="G" '. $t9 .'>Gasto</option>
                    <option value="C" '. $t1 .' >Compra</option>
                    <option value="F" '. $t8 .' >Gasto Fijo</option>
                    <option value="O" '. $t7 .' >Orden de pago</option>
                    <option value="P" '. $t4 .' >Prestamo</option>
                    <option value="T" '. $t3 .' >Tiempo aire</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="tipom">Estatus:</label>
                  <select class="form-control" name="estatus" id="estatus" >
                    <option value="" '. $e6 .' >Todos</option>
                    <option value="T" '. $e5 .'>En línea </option>
                    <option value="N" '. $e1 .' >Nuevos</option>
                    <option value="A" '. $e3.' >Abonados</option>
                    <option value="F" '. $e2 .' >Finalizados</option>
                    <option value="C" '. $e4 .' >Cancelados</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
                  </button>
                  <a href="?modulo=cxpagar&accion=index">
                    <button type="button" class="btn btn-warning">
                      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                    </button>
                  </a>
                </div>
              </form>';

          toolbar($_GET['modulo'], "", $filtro, $nuevo);
          
          echo '<form name="setcondonar" id="setcondonar" method="get"  >
            <input type="hidden" name="modulo" value="cxpagar" />
            <input type="hidden" name="accion" value="condonarpago" />
            <input type="hidden" name="cuenta" id="idcxpcon" />
          </form>';
          echo '
          <div class="main-card mb-2 card mt-2">
          <div class="card-body">
          <div class="table-responsive text-medium">
          <center>
            <table width="100%" class="table table-striped" id="myTable">
              <thead class="thead bg-blue bg-accent-3 pt-1 pb-1">
                <tr>
                  <th width="5%"><b>ID</b></th>
                  <th><b>Fecha inicio</b></th>
                  <th><b>Proveedor</b></th>
                  <th><b>Tipo</b></th>
                  <th><b>Descripcion</b></th>
                  <th><b>Monto</b></th>
                  <th><b>Abonado</b></th>
                  <th><b>Saldo</b></th>
                  <th><b>Estatus</b></th>
                  <th width="5%"><b>Ver</b></th>
                </tr>
              </thead>
              <tbody>';
                $sumc = 0;
                $suma = 0;
                $sump = 0;
                while($row = $this->model->result->fetch_array()){
                  $fecha = explode(" ", $row['cp_fini']);
                  $saldo=$row['cp_importe']-$row['cp_abonado'];
                  $desc = $row['cp_observaciones'];
                  if($row['cp_tipo'] == "C"){
                    $proveedor = busca($row['cp_proveedor'],'proveedores','p_id','p_nmb');
                    $desc.=" COMPRAS ".busca(busca($row['cp_idref'],'ordenesc','o_id','o_tablero'),'crm_tableros','ct_id','ct_nmb');
                  }elseif($row['cp_tipo'] == "T"){
                    $proveedor = busca($row['cp_proveedor'],'proveedores','p_id','p_nmb');
                    $desc.=" TIEMPO AIRE ".busca($row['cp_idref'],'tae','t_id','t_fecha');
                  }elseif($row['cp_tipo'] == "O"){
                    //$proveedor = busca($row['cp_proveedor'],'empresas','e_id','e_nmb');
                    $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_nmb');
                    $desc.=" ORDEN PAGO ".busca($row['cp_idref'],'ordenesp','op_id','op_nmb');
                  }elseif($row['cp_tipo'] == "M"){
                    $proveedor = busca($_SESSION['emp'],'proveedores','p_predeterminado = "1" AND p_empresa','p_nmb');
                    $desc.=" ORDEN DE COMPRA MENOR".busca($row['cp_idref'],'ordenesp','op_id','op_nmb');
                  }else{
                    $proveedor = busca($row['cp_proveedor'],'proveedores','p_id','p_nmb');
                    if($row['cp_tipo'] == "F") $desc = busca($row['cp_idref'],'gastosf','gf_id','gf_nmb');
                    elseif($row['cp_tipo'] == "G") $desc = busca($row['cp_idref'],'gastos','g_id','g_descripcion');
                  }
    
                  if($row['cp_estatus']=="N" || $row['cp_estatus']=="P") $color="style='background-color:#FF5C5C;color:white;'";
                  elseif($row['cp_estatus']=="A") $color="style='background-color:#FFE447'";
                  elseif($row['cp_estatus']=="F") $color="style='background-color:#47B6FF'";
                  elseif($row['cp_estatus']=="C") {
                    $color="style='background-color:#000000; color:white;'";
                    $saldo=0;
                  }

                  if(($row['cp_estatus']=='F') OR ($row['cp_estatus']=='C')) $link="";
                  else $link='<a data-fancybox data-type="ajax" data-src="popup/setegreso.php?idcxp='.$row['cp_id'].'&rand='.rand(1,100).'" href="javascript:;" >';

                  echo '<tr>';
                    echo '<th>
                      '.$link.'
                        <button class="btn btn-sm btn-info"><i class="fas fa-dollar-sign" data-toggle="tooltip" data-placement="top" title="Ingresar pagos"></i></button>
                      </a>
                    </th>';
                    echo '<td class="text-medium">'.fecha_formato(date('Y-m-d',strtotime($row['cp_fini'])),false,true).'</td>';
                    echo '<td class="text-medium">'.$proveedor.'</td>';
                    echo '<td class="text-medium">'.busca($row['cp_tipo'],'cortes_tipom','ct_tipo = "P" AND ct_clave','ct_descripcion').'</td>';
                    echo '<td class="text-medium">'.$desc.'</td>';
                    echo '<td class="number-align text-medium">$ '.number_format($row['cp_importe'],2).'</td>';
                    echo '<td class="number-align text-medium">$ '.number_format($row['cp_abonado'],2).'</td>';
                    echo '<td class="number-align text-medium">$ '.number_format($saldo,2).'</td>';
                    echo '<td '.$color.'>'.$estatusp[$row['cp_estatus']].'</td>';
                    echo '<th> 
                      <a href="?modulo=cxpagar&accion=show&idcxp='.$row['cp_id'].'">
                        <button class="btn btn-sm btn-info"><i class="fa fa-eye"></i></button>
                      </a>
                    </th>';
                  echo ' </tr>';
                  $sumc+=$row['cp_importe'];
                  $suma+=$row['cp_abonado'];
                  $sump+=$saldo;
                }
                echo '</tbody><tfoot><tr>
                  <td colspan="5" style="text-align:right;">Totales</td>
                  <td class="number-align">$ '.number_format($sumc,2).'</td>
                  <td class="number-align">$ '.number_format($suma,2).'</td>
                  <td class="number-align">$ '.number_format($sump,2).'</td>
                </tr></tfoot>';
              echo'
            </table>
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
    function show(){
      $botones = "";
      $tipo = busca($_GET['idcxp'],"cxpagar INNER JOIN cortes_tipom ON cp_tipo = ct_clave","ct_tipo = 'P' AND cp_id","ct_descripcion");
      if($this->model->estatus == "C" || $this->model->estatus == "P" || $this->model->estatus == "F") {
        $style1 = "";
        $style = 'display:none;';
      }else {
        $style = 'display:none';
        $style1 = "";
      }
      $rest = $this->model->monto - $this->model->abonado;
      $maximp = ' max="'.$rest.'" ';
      $atras = '<a href="?modulo=cxpagar&accion=index">
            <button class="mr-1 btn btn-warning">
              <i class="fa fa-arrow-left"></i> Atrás
            </button>
          </a>';
          if($this->model->estatus != "C" && $this->model->estatus != "P" && $this->model->estatus != "F") {
            if($this->model->tipo == "V"){
              $estatusVale = busca($this->model->idref,'gastos','g_id','g_estatus');
              if($estatusVale == "P"){
                $botones.= '<a data-fancybox data-type="ajax" data-src="popup/setgasto.php?id='.$this->model->idref.'" href="javascript:;" >
                  <button type="button" class="btn bg-cyan bg-darken-1 text-white mr-1"><i class="fa fa-pen"></i> Cambiar</button>
                </a>';
              }
            }
            $botones .='<button class="mr-1 btn btn-primary" id="agregar" onclick="addfechas(1);" >
              <i class="fas fa-calendar-alt"></i> Programar
            </button>';
            $botones .= '<button class="mr-1 btn btn-danger" id="cancelar" onclick="addfechas(0);" style="'.$style.'">
              <i class="fas fa-times"></i> Cancelar programación
            </button>';
            $botones .='<button class="mr-1 btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Si tu cuenta por pagar será pagada en parcialidades, programa las fechas de pago." data-bs-trigger="click" >
            <i class="fas fa-question-circle"></i>
            </button>';
          }
      toolbar($_GET['modulo'], $atras, "", "", $botones);
      
      echo '<div class="card p-2" style="">
        <div class="row">
          <div class="text-xs-center col-md-4">
            <div class="mb-5">
              <center>
                <div class="alert alert-info">Tipo: <b>'.$tipo.'</b></div>
                <!-- <input type="text" name="tipo" id="tipo" value="'.$tipo.'" class="form-control" placeholder="Tipo de cuenta" readonly style="background-color:transparent; text-align:center;border-color:black;font-weight:bold"/> -->
              </center>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-5">
              <center>';
                $proveedor = busca($this->model->proveedor,'proveedores',' p_id','p_alias');
                echo '<div class="col-md-12 alert alert-success text-xs-center">Proveedor: <b>'.$proveedor.'</b></div>
              </center>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-5">
              <center>
                <input type="hidden" name="maximp" value="'.$rest.'" id="maximp" class="form-control"/>
                <div class="col-md-12 alert alert-danger text-xs-center">Importe por pagar: <b>$'.number_format($rest,2).'</b></div>
              </center>
            </div>
          </div>
          <div class="table-responsive" id="pagos" '.$style1.'>
            <div class="col-md-12 alert alert-primary text-xs-center">Historial de Pagos para la Cuenta del Proveedor "'.$proveedor.'"</div>
            <table width="100%" class="table table-striped table-bordered">
              <thead>
                <tr style="background-color:#1c3249;color:white;">
                  <th>Fecha</th>
                  <th>Monto</th>
                  <th>Cuenta</th>
                </tr>
              </thead>
              <tbody>';
                $adjuntos = 0;
                while($row = $this->model->resultpg->fetch_array()){
                  if(!empty($row['e_adjunto']) && $row['e_adjunto']) $adjuntos++;
                  $cuenta = busca($row['ec_egreso'],"egresos INNER JOIN cuentas ON e_cuenta = cu_id","e_id","cu_nmb");
                  echo'<tr style="background-color:#c0c8d1">
                    <td>'.$row['ec_fapli'].'</td>
                    <td>$'.number_format($row['ec_monto'],2 ).'</td>
                    <td>'.$cuenta.'</td>
                  </tr>';
                }
                if($this->model->resultpg->num_rows == 0){
                  echo '<tr style="background-color:#c0c8d1">
                    <th colspan="3" style="text-align:center">Sin registro de pagos</th>
                  </tr>';
                }
              echo'</tbody>
            </table>';
            if($adjuntos > 0){
              echo '<div class="col-md-12">
                <div class="col-md-12 alert alert-primary text-xs-center">Comprobante(s)</div>';
                while($rowad = $this->model->resultpg1->fetch_array()){
                  if(!empty($rowad['e_adjunto']) && file_exists('Adjuntos/'.$_SESSION['emp'].'/'.$rowad['e_adjunto']))
                  echo'<a target="_BLANK" href="../Adjuntos/'.$_SESSION['emp'].'/'.$rowad['e_adjunto'].'">
                    <button type="button" class="btn btn-secondary"><i class="icon-attachment"></i> '.$rowad['e_adjunto'].'</button>
                  </a>';
                }
              echo'</div>';
            }
          echo'</div>';
          if($this->model->estatus != "C" && $this->model->estatus != "P" && $this->model->estatus != "F") {
            echo'<div class="fechas" id="fechasform" style="'.$style.'">
              <div class="col-md-12 alert alert-primary text-xs-center">Pagos Programados de la Cuenta Por Pagar Para el Proveedor "'.$proveedor.'"</div>
              <form id="formfechas" action="?modulo=cxpagar&accion=programar&idcxp='.$_GET['idcxp'].'" method="post">
                <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
                <input type="hidden" name="ref" value="'.$this->model->idref.'" />
                <input type="hidden" name="tipo" value="'.$this->model->tipo.'" />
                <input type="hidden" name="observaciones" value="'.$this->model->observaciones.'" />
                <div class="col-md-3 text-xs-center" style="float:right">
                  <label>Agregar Fecha: </label> <br>
                  <button type="button" class="btn btn-primary" id="addfecha"><i class="fa fa-plus"></i> Agregar fecha </button>
                </div>
                <div class="" id="fechas">
                </div>
                <div class="col-md-12 row">
                  <div class="col-md-4 ">
                    <div class="mb-5">
                      <label for"nmbac">Fecha de pago: 1</label>
                      <input type="date" name="fprog[]" value="'.date('Y-m-d').'" min="'.date('Y-m-d').'" id="fprog[]" class="form-control" placeholder="Fecha programada" required/>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-5">
                      <label for"nmbac">Importe de pago en fecha: 1</label>
                      <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="monto[]" value="'.$rest.'" step="0.01" id="monto[]" class="form-control monto number-align" placeholder="Importe de pago" required/>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-12 text-xs-center" style="display:flex;justify-content: center;align-items: center;">
                  <button type="button" class="btn btn-success" id="guardar" onclick="validimporte();"><i class="fas fa-university"></i> GUARDAR </button>
                </div>
              </form>
            </div>';
          }
        echo'</div>
      </div>';

      echo'<script>
        function addfechas(i){
          pagos = document.getElementById("pagos");
          btncancelar = document.getElementById("cancelar");
          fechas = document.getElementById("fechasform");
          agregar = document.getElementById("agregar");
          if(i == 1){
            pagos.style.display = "none";
            btncancelar.style.display = "inline";
            agregar.style.display = "none";
            fechas.style.display = "block";
          }else{
            pagos.style.display = "block";
            btncancelar.style.display = "none";
            agregar.style.display = "inline";
            fechas.style.display = "none";
          }
        }
        function validimporte(){
          monto = document.getElementsByClassName("monto");
          var imp = document.getElementById("maximp").value;
          var suma = 0;
          for(var i=0;i<monto.length;i++){
            valor = parseFloat(monto[i].value);
            suma += valor;
          }
          if(suma != imp){
            alert("Los suma de los montos, de los pagos programados, no corresponden al total de la cuenta, por favor verifica los montos antes de continuar");
          }else{
            document.getElementById("formfechas").submit();
            return true;
          }
        }
      </script>';
    }
    function pago($cuenta,$tipo){
        $tipop = array("C"=>"ORDEN DE COMPRA","M"=>"COMPRA MENOR","T"=>"PAGO TAE","P"=>"PROYECCION","R"=>"PRESTAMO","V"=>"VALE","O"=>"ORDEN DE PAGO");
        $tipoc = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
        ?>
        <script language="JavaScript">
          function habilita(aux){
            var resta = 0;
            document.getElementById("validar").checked=false;
            var totalp = document.getElementById("total").value;
            var aux1= aux+"c";
            var cuadro=parseFloat(document.getElementById("abonot").value);
            if(cuadro >= 0){resta = totalp-cuadro;}
            else{resta = Math.abs(cuadro);}
            
            var ant=parseFloat(document.getElementById(aux).value);

            if(document.getElementById(aux1).checked){
              var nuevo=cuadro+ant;
              document.getElementById(aux).disabled=false;
              document.getElementById(aux).value=resta;
              document.getElementById(aux).focus();
              document.getElementById(aux).select();
              document.getElementById("abonot").value=nuevo.toFixed(2);
              valida(aux);
              if(nuevo < 0){document.getElementById("aplicar").style.display = "none";}
              else{document.getElementById("aplicar").style.display = "";}
            }else{
              var nuevo=cuadro-ant;
              if(nuevo < 0){nuevo = 0;}
              document.getElementById(aux).disabled=true;
              document.getElementById(aux).value=0;
              document.getElementById("abonot").value=nuevo.toFixed(2);
              if(nuevo < 0){document.getElementById("aplicar").style.display = "none";}
              else{document.getElementById("aplicar").style.display = "";}
            }
            if(cuadro<0){document.getElementById("abonot").value=0;}
          }
          function valida(aux){
            document.getElementById("validar").checked=false;
            var total=parseFloat(document.getElementById("total").value);
            var cont=parseFloat(document.getElementById("cont").value);
            var abono=0;
            var comple="";
            for(i=1;i<=cont;i++){
              comple=i+"c";
              if(document.getElementById(comple).checked){
                var temp=parseFloat(document.getElementById(i).value);
                if(isNaN(temp)){
                  document.getElementById(i).value=0;
                  temp = 0;
                  document.getElementById(aux+"c").checked = false;
                  document.getElementById(aux).disabled=true;
                }
                if(temp == 0){
                  document.getElementById(aux+"c").checked = false;
                  document.getElementById(aux).disabled=true;
                }
                abono=abono+temp;
              }
            }
            abono=parseFloat(abono);
            if(abono>total){
              //var ant=parseInt(document.getElementById(aux).value);
              //var cuadro=parseInt(document.getElementById("abonot").value);
              //var nuevo=cuadro-ant;
              document.getElementById(aux).value=0;
              //document.getElementById("abonot").value=nuevo;
              alert("El monto del abono supera el cobro");
            }else{
              document.getElementById("abonot").value='';
              document.getElementById("abonot").value=abono;
            }
          }
        </script>
        <?php
        $t1 = '';
        $t2 = '';
        $t3 = '';
        $t4 = '';
        $t5 = '';
        $t6 = '';
        $t7 = '';
        if($tipo == "C") $t1 = "selected";
        if($tipo == "M") $t2 = "selected";
        if($tipo == "T") $t3 = "selected";
        if($tipo == "P") $t4 = "selected";
        if($tipo == "R") $t5 = "selected";
        if($tipo == "V") $t6 = "selected";
        if($tipo == "O") $t7 = "selected";

        if($_GET['remision']){ $var1="&remision=".$_GET['remision']; $titulo=" DE REMISION: ".busca($_GET['remision'],'remisiones','r_id','r_nmb');}
        else  { $var1=""; $titulo=""; }
        
        $sqla = 'SELECT * FROM cxpagar WHERE cp_estatus IN ("A","N") AND cp_importe!=0 ';
        if($tipo) $sqla .= ' AND cp_tipo="'.$tipo.'"';
        if($_GET['remision']) $sqla .= ' AND cp_proyecto="'.$_GET['remision'].'"';
        $sqlp = "SELECT * FROM cuentas INNER JOIN cortes ON cu_id=c_cuenta WHERE cu_estatus='A' 
                AND cu_tipo!='A' AND c_estatus='A' ORDER BY cu_orden ASC, cu_id ASC";
        
        echo '<div class="div2" id="bboton01">';
          if($_GET['remision'])
            echo '<a href="?modulo=panel&accion=pagos&id='.$_GET['remision'].'" accesskey="n"><input type="button" class="botonb" id="proyectoi"></a>';
          else
            echo '<a href="?modulo=cxpagar&accion=index" accesskey="z"><input type="button" value="" class="botonb" id="atras"></a>';
        echo '</div>
      </div>';
      echo '<table width="100%"><tr><td width="50%" valign="top">';
        echo '<form action="?modulo=cxpagar&accion=pago'.$var1.'" method="post" onsubmit="return checkSubmitenviar();">';
          if($_GET['remsion'])
            echo '<b>Tipo: </b><select name="tipo" id="tipo" onchange="this.form.submit()">
              <option value="">TODOS</option>
              <option value="C" '.$t1.'>ORDENES DE COMPRA</option>
              <option value="M" '.$t2.'>COMPRAS MENORES</option>
              <option value="V" '.$t6.'>VALES</option>
              <option value="O" '.$t7.'>ORDENES DE PAGO</option>
            </select>&nbsp;  <br>';
          else
            echo '<b>Tipo: </b><select name="tipo" id="tipo" onchange="this.form.submit()">
              <option value="">TODOS</option>
              <option value="C" '.$t1.'>ORDENES DE COMPRA</option>
              <option value="M" '.$t2.'>COMPRAS MENORES</option>
              <option value="T" '.$t3.'>PAGOS DE TAE</option>
              <option value="P" '.$t4.'>PROYECCIONES</option>
              <option value="R" '.$t5.'>PRESTAMOS</option>
              <option value="V" '.$t6.'>VALES</option>
              <option value="O" '.$t7.'>ORDENES DE PAGO</option>
            </select>&nbsp;  <br>';
          echo '<table class="lista" width="100%" >
            <thead>
              <tr>
                <th colspan="7"><b>CUENTAS POR PAGAR'.$titulo.'</b></th>
              </tr>
            </thead>
            <tr>
              <th>Referencia</th>
              <th>Proveedor/descripcion</th>
              <th>Monto</th>
              <th>Abonado</th>
              <th>Saldo</th>
              <th></th>';
              $resulta=setq($sqla) or die($sqla);
              while($row=$resulta->fetch_array()){
                if($cuenta==$row['cp_id'])  $aux1="checked";
                else $aux1="";
                $saldo=($row['cp_importe'])-($row['cp_abonado']);

                if($row['cp_estatus']=="N") $color="style='background-color:#FF5C5C;color:white;'";
                else if($row['cp_estatus']=="A") $color="style='background-color:#FFE447'";

                if(($row['cp_tipo']=="C")or($row['cp_tipo']=="M")){ $link='?modulo=ordenesc&accion=show&id=';  $proveedor=busca($row['cp_proveedor'],'proveedores','p_id','p_nmb');}
                else if($row['cp_tipo']=="T"){ $link='?modulo=tae&accion=show&id='; $proveedor=busca($row['cp_proveedor'],'proveedores','p_id','p_nmb');}
                else if($row['cp_tipo']=="P"){ $link='?modulo=proyecciones&accion=show&id='; $proveedor=busca($row['cp_idref'],'proyecciones','p_id','p_nmb').' '.fecha_formato($row['cp_fini'],true,true);}
                else if($row['cp_tipo']=="R"){ $link='?modulo=prestamos&accion=show&id=';  $proveedor=busca($row['cp_idref'],'prestamos','pr_id','pr_prestatario');}
                else if($row['cp_tipo']=="V"){ $link='?modulo=valesp&accion=show&id=';  $proveedor=substr(busca($row['cp_idref'],'valesp','vp_id','vp_descripcion'),0,20);}
                else if($row['cp_tipo']=="O"){ $link='?modulo=ordenesp&accion=show&id=2&remision='.$row['cp_proyecto'].'&id=';   $proveedor=busca(busca($row['cp_idref'],'ordenesp','op_id','op_proyecto'),'remisiones','r_id','r_nmb');}
                echo '</tr>
                <tr>
                  <td>'.$row['cp_idref'].' - <a target="_BLANK" href="'.$link.$row['cp_idref'].'">'.$tipop[$row['cp_tipo']].'</a></td>
                  <td>'.$proveedor.'</td>
                  <td>$ '.$row['cp_importe'].'</td>
                  <td>$ '.$row['cp_abonado'].'</td>
                  <td '.$color.' >$ '.number_format($saldo,2).'</td>
                  <td><input type="radio" name="cuenta" value="'.$row['cp_id'].'" '.$aux1.' onclick="submit();" /></td>
                </tr>';
              }
          echo'</table>
        </form>';

        echo '</td><td width="50%" valign="top"  >';
          if($cuenta){
            $estatus=busca($cuenta,'cxpagar','cp_id','cp_estatus');
            if(($estatus=="N") OR ($estatus=="A")){
              $tipoc = busca($cuenta,'cxpagar','cp_id','cp_tipo');
              $proy = busca($cuenta,'cxpagar','cp_id','cp_proyecto');
              $idref = busca($cuenta,'cxpagar','cp_id','cp_idref');

              if($tipoc == "M" || $tipoc == "C") $desc = "COMPRAS ".busca($proy,'remisiones','r_id','r_nmb');
              elseif($tipoc == "P") $desc = busca($idref,'proyecciones','p_id','p_nmb').' - '.fecha_formato($row['cp_fini'],true,true);
              elseif($tipoc == "R") $desc = busca($idref,'prestamos','pr_id','pr_descripcion');
              elseif($tipoc == "V") $desc = busca(busca($idref,'valesp','vp_id','vp_vale'),'vales','v_id','v_nmb').' - '.busca($idref,'valesp','vp_id','vp_descripcion');
              elseif($tipoc == "T") $desc = "TRASPASO TAE ".date('d-m-y',strtotime($row['cp_fini']));
              elseif($tipoc == "O") $desc = "ORDEN DE PAGO ".busca(busca($idref,'ordenesp','op_id','op_proyecto'),'remisiones','r_id','r_nmb');
              else $desc = "-";

              $monto=busca($cuenta,'cxpagar','cp_id','cp_importe');
              $abonado=busca($cuenta,'cxpagar','cp_id','cp_abonado');
              $totala=$monto-$abonado;
              echo '<form action="?modulo=cxpagar&accion=insertpago'.$var1.'" method="post" onsubmit="return checkSubmitenviar();">
                <table class="lista" width="100%" >
                  <thead>
                    <tr>
                      <th colspan="7">
                        <b>
                          REALIZAR PAGO DE  '.$tipop[$tipoc].' '.busca($cuenta,'cxpagar','cp_id','cp_idref').'<br>'.$desc.'
                        </b>
                      </th>
                    </tr>
                  </thead>
                  <tr>
                    <td colspan="4">
                      <span style="font-size:20px" >Total a pagar:&nbsp;&nbsp;</span>
                      <span style="font-size:40px" > $'.number_format($totala,2).'</span>
                    </td>
                  </tr>
                  <tr>
                    <input type="hidden" name="total" id="total" value="'.$totala.'" />
                    <input type="hidden" name="cuenta" id="cuenta" value="'.$cuenta.'" />
                    <th>Cuenta</th>
                    <th>Disponible</th>
                    <th></th>
                    <th>Abono</th>';
                    $cont=0;
                    $resultp=setq($sqlp) or die($sqlp);
                    while($rwp=$resultp->fetch_array()){
                      $saldoact=saldo_actual($rwp['cu_id']);
                      $disponible=disponible($rwp['cu_id'],$saldoact);
                      $cont++;
                      echo '</tr>
                      <tr>
                        <td>'.$rwp['cu_nmb'].'</td>
                        <td>'.number_format($disponible,2).'</td>';
                        if($cont==1)
                          echo '<td>
                            <input type="checkbox" id="'.$cont.'c" name="'.$rwp['cu_id'].'c" onclick="habilita('.$cont.');" checked />
                          </td>
                          <td>
                            <input type="number" step="0.01" onfocus="this.select();" style="width:70px;"  min="0" max="'.$disponible.'" name="'.$rwp['cu_id'].'" value="'.$totala.'" onchange="valida('.$cont.');" id="'.$cont.'" />
                          </td>';
                        else
                          echo '<td>
                            <input type="checkbox" id="'.$cont.'c" name="'.$rwp['cu_id'].'c" onclick="habilita('.$cont.');" />
                          </td>
                          <td>
                            <input type="number" step="0.01" onfocus="this.select();" style="width:70px;" disabled min="0" max="'.$disponible.'" name="'.$rwp['cu_id'].'" value="0.00" onchange="valida('.$cont.');" id="'.$cont.'" />  
                          </td>';
                      echo '</tr>';
                    }
                  echo '<input type="hidden" id="cont" value='.$cont.' />
                  <tr>
                    <td colspan="2"></td>
                    <td ><b>Total:</b></td>
                    <td>
                      <input type="number" onfocus="this.select();" style="width:70px;" id="abonot" readonly value="0.0"/>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="5"><b>Validar&nbsp;</b><input type="checkbox" required id="validar" /></td>
                  </tr>';
                  echo'<tr>
                    <td colspan="5"><input type="submit" value="" id="aplicar" class="botont"></td>
                  </tr>';
                echo '</table>
              </form>';
            }
          }
        echo '</td>
      </table>';
    }
    function condonar($cuenta,$tipo){
        $tipop = array("C"=>"ORDEN DE COMPRA","M"=>"COMPRA MENOR","T"=>"PAGO TAE","P"=>"PROYECCION","R"=>"PRESTAMO","V"=>"VALE","O"=>"ORDEN DE PAGO");
        $tipoc = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
        ?>
        <script language="JavaScript">
          function habilita(aux){
            var resta = 0;
            document.getElementById("validar").checked=false;
            var totalp = document.getElementById("total").value;
            var aux1= aux+"c";
            var cuadro=parseFloat(document.getElementById("abonot").value);
            if(cuadro >= 0){resta = totalp-cuadro;}
            else{resta = Math.abs(cuadro);}
            var ant=parseFloat(document.getElementById(aux).value);
            if(document.getElementById(aux1).checked){
              var nuevo=cuadro+ant;
              document.getElementById(aux).disabled=false;
              document.getElementById(aux).value=resta;
              document.getElementById("abonot").value=nuevo.toFixed(2);
              valida(aux);
              if(nuevo < 0){document.getElementById("aplicar").style.display = "none";}
              else{document.getElementById("aplicar").style.display = "";}
            }else{
              var nuevo=cuadro-ant;
              document.getElementById(aux).disabled=true;
              document.getElementById(aux).value=0;
              document.getElementById("abonot").value=nuevo.toFixed(2);
              if(nuevo < 0){document.getElementById("aplicar").style.display = "none";}
              else{document.getElementById("aplicar").style.display = "";}
            }
          }
          function valida(aux){
            document.getElementById("validar").checked=false;
            var total=parseFloat(document.getElementById("total").value);
            var cont=parseFloat(document.getElementById("cont").value);
            var abono=0;
            var comple="";
            for(i=1;i<=cont;i++){
              comple=i+"c";
              if(document.getElementById(comple).checked){
                var temp=parseFloat(document.getElementById(i).value);
                abono=abono+temp;
              }
            }
            abono=parseFloat(abono);
            if(abono>total){
              //var ant=parseInt(document.getElementById(aux).value);
              //var cuadro=parseInt(document.getElementById("abonot").value);
              //var nuevo=cuadro-ant;
              document.getElementById(aux).value=0;
              //document.getElementById("abonot").value=nuevo;
              alert("El monto del abono supera el cobro");
            }else{
              document.getElementById("abonot").value='';
              document.getElementById("abonot").value=abono;
            }
          } 
        </script>
        <?php
        $t1 = '';
        $t2 = '';
        $t3 = '';
        $t4 = '';
        $t5 = '';
        $t6 = '';
        $t7 = '';
        if($tipo == "C") $t1 = "selected";
        if($tipo == "M") $t2 = "selected";
        if($tipo == "T") $t3 = "selected";
        if($tipo == "P") $t4 = "selected";
        if($tipo == "R") $t5 = "selected";
        if($tipo == "V") $t6 = "selected";
        if($tipo == "O") $t7 = "selected";

        if($_GET['remision']){ $var1="&remision=".$_GET['remision']; $titulo=" DE REMISION: ".busca($_GET['remision'],'remisiones','r_id','r_nmb');}
        else  { $var1=""; $titulo=""; }

        $sqla = 'SELECT * FROM cxpagar WHERE cp_estatus IN ("A","N") AND cp_importe!=0 ';
        if($tipo) $sqla .= ' AND cp_tipo="'.$tipo.'"';
        if($_GET['remision']) $sqla .= ' AND cp_proyecto="'.$_GET['remision'].'"';
        echo '<div class="div2" id="bboton01">';
          echo '<a href="?modulo=cxpagar&accion=index" accesskey="z"><input type="button" value="" class="botonb" id="atras"></a>';
        echo '</div>
      </div>';
      echo '</td><td width="50%" valign="top"  >';
        if($cuenta){
          $estatus=busca($cuenta,'cxpagar','cp_id','cp_estatus');
          $monto=busca($cuenta,'cxpagar','cp_id','cp_importe');
          $abonado=busca($cuenta,'cxpagar','cp_id','cp_abonado');
          $restante=$monto-$abonado;
          $sqlc = 'SELECT * FROM cortesd WHERE cd_cuenta="'.$cuenta.'"';
          $resultc = setq($sqlc) or die($sqlc);
          if(mysql_num_rows($resultc) > 0){
            $aux1='CONDONACION PARA ';
            $aux2='Total a condonar: ';
            $aux3='Motivo para condonar: ';
            $cant=$restante;
          }else{
            $aux1='CANCELACION DE ';
            $aux2='Monto de cuenta por pagar a cancelar: ';
            $aux3='Motivo para condonar: ';
            $cant=$monto;
          }
          //$totala=$monto-$abonado;
          echo '<form action="?modulo=cxpagar&accion=condonarpago'.$var1.'" method="post" onsubmit="return checkSubmitenviar();">
            <table class="lista" width="100%" >
              <thead>
                <tr>
                  <th colspan="2"><b>'.$aux1.$tipop[busca($cuenta,'cxpagar','cp_id','cp_tipo')].' '.busca($cuenta,'cxpagar','cp_id','cp_idref').'</b></th>
                </tr>
              </thead>
              <tr>
                <td colspan="2">
                  <span style="font-size:20px" >'.$aux2.'&nbsp;&nbsp;</span>
                  <span style="font-size:40px" > $'.number_format($cant,2).'</span>
                </td>
              </tr>
              <tr>
                <input type="hidden" name="cuenta" id="cuenta" value="'.$cuenta.'" />
                <th colspan="2"><b>Motivo</b></th>
              </tr>';
              echo '<tr><td colspan="2"><textarea name="motivo" cols="60" rows="4" >'.$aux3.'</textarea></td></tr>';
              echo '<tr><th><b>Usuario</b></th><th><b>Contraseña</b></th></tr>';
              echo '<tr><td><input type="text" name="user" /></td><td><input type="password" name="pass"/></td></tr>';
              echo '<tr><td colspan="2"><b>Validar&nbsp;</b><input type="checkbox" required id="validar" /></td></tr>';
              echo'<tr><td colspan="2"><input type="submit" value="" id="aplicar" class="botont"></td></tr>';
            echo '</table>
          </form>';
        }
      echo '</td></table>';
    }
  }
?>
<script>
  $(document).ready(function(){
    var campos_max = 10;
    var x = 2;
    $('#addfecha').click (function(e) {
      e.preventDefault();
      if (x < campos_max) {
        $('#fechas').prepend('<div class="col-lg-12 mb-1">\
        <div class="row">\
          <div class="col-md-4 col-sm-5 col-xs-10">\
            <div class="mb-5">\
              <label>Fecha de pago:'+x+'</label>\
              <input type="date" name="fprog[]" id="fprog[]" class="form-control fprog" placeholder="Fecha programada" required/>\
            </div>\
          </div>\
          <div class="col-md-4 col-sm-5 col-xs-10">\
            <div class="mb-5">\
              <label for"nmbac">Importe de pago en fecha:'+x+'</label>\
              <div class="input-group">\
                <span class="input-group-text">$</span>\
                <input type="number" name="monto[]" value="" step="0.01" id="monto[]" class="form-control monto number-align" placeholder="Importe de pago" required/>\
              </div>\
            </div>\
          </div>\
          </div>\
           <a class="btn btn-sm btn-danger text-white remover_campo" title="Quitar"><i class="fa fa-trash"></i> Quitar</a>\
          </div>'
        );
        x++;
      }
    });
    $("#fechas").on("click",".remover_campo",function(e) {
      e.preventDefault();
      $(this).parent("div").remove();
      x--;
    });
  });
</script>