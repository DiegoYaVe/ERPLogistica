<?php
  //ini_set('display_errors','1');
  $GLOBALS['menu'] = "Ingresos";
  class cxcobrar {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelcxcobrar(isset($obj));
    }
    function index() {
      if(!isset($_GET['page']))    $_GET['page'] = 1;
      //if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime(busca('A','cxcobrar','cx_estatus = "N" OR cx_estatus','MIN(cx_fini)')));
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime(busca("A",'cxcobrar','cx_estatus = "N" || cx_estatus','MIN(cx_fini)')));
      if(!isset($_REQUEST['ffin'])){  
        $sql = 'SELECT * FROM cxcobrar WHERE DATE(cx_fini)
                BETWEEN "'.date('Y-m-t').'" AND "'.date('Y-m-d', strtotime('first sunday of next month')).'"
                AND cx_estatus IN ("A","N")';
        $result = setq($sql);
        if($result->num_rows > 0) $_REQUEST['ffin'] = date('Y-m-d', strtotime('first sunday of next month'));
        else $_REQUEST['ffin'] = date('Y-m-t');
      }
      if(!isset($_POST['estatus']))    $_POST['estatus'] = "T";
      if(!isset($_POST['tipo']))    $_POST['tipo'] = NULL;
      if(!isset($_POST['cliente']))    $_POST['cliente'] = NULL;

      $this->model->result($_GET['page'],$_REQUEST['fini'],$_REQUEST['ffin'],$_POST['estatus'],$_POST['tipo'],trim($_POST['cliente']));
      $this->view = new viewcxcobrar($this->model);
      $this->view->browse($_GET['page'],$_REQUEST['fini'],$_REQUEST['ffin'],$_POST['estatus'],$_POST['tipo'],$_POST['cliente']);
    }
    function show(){
      $this->model->selectabonos($_GET['idcxc']);
      $this->model->select($_GET['idcxc']);
      $this->view = new viewcxcobrar($this->model);
      $this->view->show();
    }
    function cobro() {
      if (!isset($_GET['id']))            $_GET['id'] = NULL;
      if (!isset($_REQUEST['cuenta']))    $_REQUEST['cuenta'] = NULL;
      if(!isset($_POST['tipo']))          $_POST['tipo'] = NULL;
      //$this->model->select($_GET['id']);
      $this->view = new viewcxcobrar($this->model);
      $this->view->cobro($_REQUEST['cuenta'],$_POST['tipo']);
    }
    function condonacion() {
      if (!isset($_GET['id']))            $_GET['id'] = NULL;
      if (!isset($_REQUEST['cuenta']))    $_REQUEST['cuenta'] = NULL;
      if(!isset($_POST['tipo']))          $_POST['tipo'] = NULL;
      //$this->model->select($_GET['id']);
      $this->view = new viewcxcobrar($this->model);
      $this->view->condonacion($_REQUEST['cuenta'],$_POST['tipo']);
    }
    function insertcxcobrar(){
      $id = $this->model->insertcxcobrar($_POST['cliente'],NULL,$_POST['tipo'],$_POST['importe'],$_POST['fecha'],$_POST['observaciones']);
      $this->model->addproyeccion($id);
      redirect('?modulo=cxcobrar&accion=index');
    }
    function insertcobro(){
      $abonado=busca($_POST['cuenta'],'cxcobrar','cx_id','cx_abonado');
      if(!isset($abonado)) $abonado=0;
      $fecha=date('Y-m-d H:i:s');
      $gen=$_SESSION['uid'];
      $tipo="C";
      $total=$abonado;
      $solicitado=busca($_POST['cuenta'],'cxcobrar','cx_id','cx_importe');
      $sqlp = "SELECT * FROM cuentas INNER JOIN cortes ON cu_id=c_cuenta WHERE cu_estatus='A-' 
                AND cu_tipo!='A' AND c_estatus='A' ORDER BY cu_orden ASC, cu_id ASC";
      $resultp=setq($sqlp) or die($sqlp);
      while($rwp=$resultp->fetch_array()){
        if($_POST[$rwp['cu_id'].'c']){
          $corte=busca($rwp['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_id');
          $referencia=busca($_POST['cuenta'],'cxcobrar','cx_id','cx_referencia');
          $tipom=busca($_POST['cuenta'],'cxcobrar','cx_id','cx_tipo');

          $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
          $result = setq($sql);
          list($id) = $result->fetch_array();
          $id++;
          $this->model->setdata($id,$corte,$fecha,$gen,$tipo,$referencia,$_POST[$rwp['cu_id']],$tipom,$_POST['cuenta']);
          $this->model->insert();

          $total=$total+$_POST[$rwp['cu_id']];
        }
      }

      $proyecto = busca($_POST['cuenta'],'cxcobrar','cx_id','cx_proyecto');
      if($total==$solicitado){
        $sqli = 'UPDATE cxcobrar SET cx_estatus = "F", cx_abonado="'.$total.'", cx_ffin ="'.$fecha.'" WHERE cx_id="'.$_POST['cuenta'].'"';
        setq($sqli) or die($sqli);
        /*
          if($proyecto)
            addbitacorad("FINIQUITO DE PROYECTO ".busca($proyecto,'proyectos','p_id','p_nmb').' POR $'.number_format($total,2),busca($proyecto,'bitacora','b_proyecto','b_id'));

        */
      }else{
        $sqli = 'UPDATE cxcobrar SET cx_estatus = "A", cx_abonado="'.$total.'" WHERE cx_id="'.$_POST['cuenta'].'"';
        setq($sqli) or die($sqli);
      }
      if($_GET['proyecto'])   $link='Location:?modulo=panel&accion=cobros&id='.$_GET['proyecto'];
      else    $link='Location:?modulo=cxcobrar&accion=index';
      redirect($link);
    }
    function condonarcobro(){
      $grupo=busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
      if(($grupo!="ADMIN") AND ($grupo!="SUPER"))
        die(alert_back("El usuario no tiene permiso de realizar condonaciones",true));

      $sqli = ' UPDATE cxcobrar SET
                cx_estatus = "C",
                cx_observaciones="COBRO CONDONADO",
                cx_ffin ="'.date('Y-m-d H:i:s').'",
                cx_condonado = "1"
                WHERE cx_id="'.$_REQUEST['cuenta'].'" ';
      setq($sqli) or die($sqli);
      $link='?modulo=cxcobrar&accion=index';
      redirect($link);
    }
    function inserttae(){
      $sql = 'SELECT * FROM tae INNER JOIN clientestaed ON t_cliente = ct_ctae
              WHERE t_estatus = "N" ORDER BY t_fecha ASC ';
      $result = setq($sql);

      while($row = $result->fetch_array()){
        if(isset($_POST['tae'.$row['t_id']])){
          $cliente = busca($row['ct_clientetae'],'clientestae','c_id','c_cliente');
          $idxc = $this->model->insertcxcobrar($cliente,$row['t_id'],'T',$row['t_importe'],date('Y-m-d'),"RECARGA TIEMPO AIRE");
          $this->model->addproyeccion($idxc);

          include_once('cxpagar.php');
          $cxp = new modelcxpagar();
          $idxp = $cxp->insertcxpagar(1,7,$row['t_id'],"T",$row['t_importe'],date('Y-m-d'),"PAGO TAE ".$row['t_id']);
          $cxp->addproyeccion($idxp);

          $sqlt = 'UPDATE tae SET
                  t_cxcobrar = "'.$idxc.'",
                  t_cxpagar = "'.$idxp.'",
                  t_estatus = "A"
                  WHERE t_id = "'.$row['t_id'].'"';
          setq($sqlt) or die($sqlt);
        }
      }
      redirect('?modulo=cxcobrar&accion=index');
    }
    function programar(){
      $obs = busca($_GET['idcxc'],'cxcobrar','cx_id','cx_observaciones');
      $cliente = busca($_GET['idcxc'],'cxcobrar','cx_id','cx_cliente');
      $this->model->updatecxc("C", "PAGO PROGRAMADO", $_GET['idcxc']);
      for($i=0;$i<sizeof($_POST['fprog']);$i++){
        $id = $this->model->insertcxcobrar($cliente,$_POST['ref'],$_POST['tipo'],$_POST['monto'][$i],$_POST['fprog'][$i],$obs);
        $this->model->addproyeccion($id);
        $this->model->insertprog($_GET['idcxc'],$id,$_POST['monto'][$i]);
      }
      redirect('?modulo=cxcobrar&accion=index');
    }
  }

  /* -----------------------------------------------MODEL---------------------------------------------------------------------- */

  class modelcxcobrar{
    function result($page,$fini,$ffin,$estatus,$tipo,$cliente){
      $page--;
      $pageresult = 50;
      $pag = ' LIMIT '.($page * $pageresult).','.$pageresult;
      $sqlf = ' WHERE DATE(cx_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      if($tipo) $sqlf .= ' AND cx_tipo = "'.$tipo.'" ';

      if($estatus){
        if($estatus == "T") $sqlf .= ' AND cx_estatus IN ("N","A")';
        else $sqlf .= ' AND cx_estatus = "'.$estatus.'" ';
      }else $sqlf .= '';

      if($cliente && !empty($cliente)){
        $sqlc=' INNER JOIN crm_clientes ON cx_cliente = c_id  ';
        $sqlf.=' AND (c_nmb LIKE "%'.$cliente.'%" OR c_alias LIKE "%'.$cliente.'%")';
      }
      else $sqlc = "";

      

      $sql = 'SELECT * FROM cxcobrar INNER JOIN cortes_tipom ON cx_tipo = ct_clave
              '.$sqlc.$sqlf.' AND ct_tipo = "X"
              AND cx_importe!=0 ORDER BY cx_fini DESC,cx_id DESC'.$pag;
      $this->result = setq($sql);

      $sql = 'SELECT COUNT(*) FROM cxcobrar '.$sqlc.$sqlf.' AND cx_importe!=0 ORDER BY cx_fini DESC, cx_id DESC';
      $this->resultt = setq($sql) or die($sql);
      list($nr) = $this->resultt->fetch_array();
      $this->np = $nr/$pageresult;
    }
    function select($id) {
      $sql = 'SELECT * FROM cxcobrar INNER JOIN cortes_tipom ON cx_tipo = ct_clave WHERE cx_id= "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['cx_id'];
      $this->cliente = $row['cx_cliente'];
      $this->referencia = $row['cx_referencia'];
      $this->tipo = $row['cx_tipo'];
      $this->importe = $row['cx_importe'];
      $this->abonado = $row['cx_abonado'];
      $this->fini = $row['cx_fini'];
      $this->ffin = $row['cx_ffin'];
      $this->estatus = $row['cx_estatus'];
      $this->observaciones = $row['cx_observaciones'];
      $this->condonado = $row['cx_condonado'];
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
    function insertcxcobrar($cliente,$referencia,$tipo,$importe,$fini,$observaciones){
      $sql = 'INSERT INTO cxcobrar SET
              cx_cliente = "'.$cliente.'",
              cx_referencia = "'.$referencia.'",
              cx_tipo = "'.$tipo.'",
              cx_importe = "'.$importe.'",
              cx_observaciones = "'.$observaciones.'",
              cx_abonado = "0",
              cx_estatus = "N",
              cx_fini = "'.$fini.'"';
      setq($sql);
      $idc = getmax('cx_id','cxcobrar',false,false);
      return($idc);
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
              cd_cuenta = "'.$this->cuenta.'"';
      setq($sql) or die($sql);
    }
    function pagonoac($cuenta,$observaciones){
      $idsuite = busca($cuenta,'cxcobrar','cx_id','cx_idsuite');
      $clienteceo = busca($cuenta,'cxcobrar','cx_id','cx_cliente');
      $cliente = busca($clienteceo,'crm_clientes','c_id','c_idsuite');

      $mysqli = mysqli_connect("localhost",'aqlzfcar_root','Wptyeall.0','aqlzfcar_kioscoweb');

      $sql = 'UPDATE pedidoscl SET p_estatus = "A" WHERE p_id = "'.$idsuite.'"';
      $mysqli->query($sql);

      $sqlr = 'SELECT r_id FROM reportepago WHERE r_pedido = "'.$idsuite.'" AND r_estatus = "N"';
      $result = $mysqli->query($sqlr) or die($sqlr);
      list($idr) = $result->fetch_array();

      $sqlrp = 'UPDATE reportepago SET r_estatus = "C", r_observacion = "'.utf8_decode(mb_strtoupper($observaciones)).'"
                WHERE r_id = "'.$idr.'"';
      $mysqli->query($sqlrp) or die($sqlrp);

      $mysqli->close();
      include('db.php');

      $sqlr = 'UPDATE remisiones SET
              r_estatus = "C",
              r_ucan = "'.$_SESSION['uid'].'",
              r_fcan = "'.date('Y-m-d H:i:s').'",
              r_motivocan = "'.utf8_decode(mb_strtoupper($observaciones)).'"
              WHERE r_idsuite = "'.$idsuite.'"';
      setq($sqlr) or die($sqlr);

      sendmailop("2",4,$cliente,$idsuite,NULL,"cpago",$idr);
    }
    function selectabonos($idcxc){
      $sql = 'SELECT * FROM ingreso_cxcobrar INNER JOIN ingresos ON ic_ingreso = i_id
              WHERE ic_cxcobrar = "'.$idcxc.'" AND i_estatus != "C" ORDER BY ic_fapli DESC';
      $this->resultab = setq($sql);
      $this->resultab1 = setq($sql);
    }
    function insertprog($padre,$hijo,$monto){
      $sql = 'INSERT INTO cxcobrar_programacion SET 
              cxc_padre = "'.$padre.'",
              cxc_hijo = "'.$hijo.'", 
              cxc_monto = "'.$monto.'"';
      setq($sql);
    }
    function updatecxc($est,$obs,$idcxc){
      $sql = 'UPDATE cxcobrar SET
              cx_estatus = "'.$est.'",
              cx_observaciones = "'.$obs.'"
              WHERE cx_id = "'.$idcxc.'"';
      setq($sql);
    }
    function addproyeccion($idcxc){
      if($_SESSION['emp'] == "1"){
        $this->select($idcxc);
        //$feccxp = date("Y-m-d",strtotime($this->fini));
        $feccxp = strtotime(date("Y-m-d",strtotime($this->fini)));
        $fechahoy = strtotime(date("Y-m-d",time()));
        $fechaliquidacion = "";

        if($fechahoy >= $feccxp) $fechaliquidacion = date("Y-m-d");
        else $fechaliquidacion = date("Y-m-d",strtotime($this->fini));
        
        $sqlproy = 'SELECT p_id,p_fechaap,p_fechacierre FROM proyecciones WHERE p_estatus = "N"';
        $resultproy = setq($sqlproy);
        list($idp,$fap,$fcp) = $resultproy->fetch_array();
        
        if($fechaliquidacion >= $fap && $fechaliquidacion <= $fcp) {
          include_once('cuentas.php');
          $proyeccion = new modelcuentas();
  
          $refer=busca($this->cliente,'crm_clientes','c_id','c_alias');
  
          if($this->tipo == "R") $desc = busca($this->referencia,'remisiones','r_id','r_nmb').' - '.$refer;
          else $desc = $this->observaciones;
  
          $proyeccion->setproyecciond($idp,date("Y-m-d",strtotime($this->fini)),"C",$desc,$this->importe,$this->importe,$idcxc);
        }
      }
    }
  }

  /* -----------------------------------------------VIEW---------------------------------------------------------------------- */

  class viewcxcobrar {
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
    function browse($page,$fini,$ffin,$estatus,$tipo,$cliente){
      $tipop = array("T"=>"COBRO TAE","Y"=>"PROYECTO","S"=>"SOPORTE","Z"=>"POLIZA","P"=>"PRESTAMO","A"=>"COTIZACION","I"=>"COMISION");
      $estatusp = array("N"=>"NUEVA","F"=>"FINALIZADO","C"=>"CANCELADO","A"=>"ABONADO","T"=>"TODOS");

      $seln = '';
      $selp = '';
      $sela = '';
      $selc = '';
      $selt = '';
      $sele = '';
      if($estatus == "N")
        $seln = "selected";
      elseif($estatus == "F")
        $selp = "selected";
      elseif($estatus == "A")
        $sela = "selected";
      elseif($estatus == "C")
        $selc = "selected";
      elseif($estatus == "T")
        $sele = "selected";
      else
        $selt = "selected";

      $t1 = '';
      $t2 = '';
      $t3 = '';
      $t4 = '';
      $t5 = '';
      $t6 = '';
      $t7 = '';
      if($tipo == "P") $t1 = "selected";
      elseif($tipo == "C") $t2 = "selected";
      elseif($tipo == "A") $t3 = "selected";
      elseif($tipo == "O") $t4 = "selected";
      elseif($tipo == "R") $t5 = "selected";
      else $t7 = "selected";
      


      //Sección: E1 Encabezado - Botones de acción
      //Sección: E2 Encabezado - Filtros
      $vtae = validatae(date('Y-m-d',strtotime('-20 days')),date('Y-m-d'));
      if($vtae > 0){
        echo '<a data-fancybox data-type="ajax" data-src="popup/importae.php?id='.rand(1,999).'" href="javascript:;" >
            <button type="button" class="btn btn-warning">
              <i class="icon-mobile-phone"></i> TAE
              <div class="tag tag-pill tag-danger">'.$vtae.'</div>
            </button>
          </a>'; 

      } 
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
          $("#cliente").focus();
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
          $nuevo = '<a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setcxclote.php?rand='. rand(1,999).'" href="javascript:;" >
            <button type="button" class="btn btn-sm btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>';
          $filtrar = '
                <form class="" role="form" method="post" action="?modulo=cxcobrar&accion=index">
                  <div class="mb-5">
                    <label for="tipom">Desde:</label>
                    <input type="date" name="fini" id="fini" class="form-control" value="'. $fini .'" />
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Hasta:</label>
                    <input type="date" name="ffin" id="ffin" class="form-control" value="'. $ffin.'" />
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Cliente:</label>
                    <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Nombre del cliente que buscas" onfocus="this.select();" value="'. $cliente .'" />
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Tipo:</label>
                    <select class="form-control" name="tipo" id="tipo" >
                      <option value="" '. $t7 .' >Todos</option>  
                      <option value="R" '. $t5 .' >Remisiones</option>
                      <option value="P" '. $t1 .' >Recepción prestamo</option>
                      <option value="C" '. $t2 .' >Comisión recibida</option>
                      <option value="A" '. $t3 .' >Acreedores riversos</option>
                      <option value="O" '. $t4 .' >Otros ingresos</option>
                    </select>
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Estatus:</label>
                    <select class="form-control" name="estatus" id="estatus" >
                      <option value="" '. $selt .' >Todos</option>
                      <option value="T" '. $sele .' >En línea</option>
                      <option value="N" '. $seln .' >Nuevos</option>
                      <option value="A" '. $sela .' >Abonados</option>
                      <option value="F" '. $selp .' >Finalizados</option>
                      <option value="C" '. $selc .' >Cancelados</option>
                    </select>
                  </div><!-- form group [search] -->
                  <div class="mb-5">
                    <label for="">Acciones:</label><br>
                    <button type="submit" class="btn btn-info">
                      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-redo"></i>Filtrar
                    </button>
                    <a href="?modulo=cxcobrar&accion=index">
                      <button type="button" class="btn btn-warning">
                        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle" style="color: #ffffff;"></i> LImpiar
                      </button>
                    </a>
                  </div>
                </form>';

        toolbar($_GET['modulo'], "", $filtrar, $nuevo);

        echo '<div class="main-card mb-3 card mt-3">
          <div class="card-body row">';
            ?><?php
            echo '
            <form name="setcondonar" id="setcondonar" method="get"  >
              <input type="hidden" name="modulo" value="cxcobrar" />
              <input type="hidden" name="accion" value="condonarcobro" />
              <input type="hidden" name="cuenta" id="idcxccon" />
            </form>
            <div class="table-responsive text-medium">
            <center>
              <table width="100%" class="table table-striped" id="myTable">
                <thead class="thead pt-1 pb-1">
                  <tr>
                    <th width="5%"><b>ID</b></th>
                    <th><b>Fecha</b></th>
                    <th><b>Cliente</b></th>
                    <th><b>Tipo</b></th>
                    <th><b>Descripción</b></th>
                    <th><b>Monto</b></th>
                    <th><b>Abonado</b></th>
                    <th><b>Saldo</b></th>
                    <th><b>Estatus</b></th>
                    <th width="5%"><b>Ver</b></th>
                  </tr>
                </thead>
                <tbody>';
                  $sumamo = 0;
                  $sumaab = 0;
                  $sumasa = 0;
                  while($row = $this->model->result->fetch_array()){
                    $fecha = explode(" ", $row['cx_fini']);
                    $saldo=$row['cx_importe']-$row['cx_abonado'];
                    if($row['cx_tipo'] == "R"){
                      $desc = busca($row['cx_referencia'],'remisiones','r_id','r_nmb');
                      if(busca($row['cx_referencia'],'remisiones','r_id','r_pedidoweb') != NULL){
                        $desc = busca($row['cx_referencia'],'remisiones','r_id','r_nmb').'
                        <a target="_BLANK" href="?modulo=pedidosclw&accion=show&id='.busca($row['cx_referencia'],'remisiones','r_id','r_pedidoweb').'">
                          <button class="btn btn-primary"><i class="icon-world"></i></button>
                        </a>';
                      }
                    }
                    else $desc = $row['cx_observaciones'];
                    if($row['cx_estatus']=="N") $color="style='background-color:#FF5C5C;color:white;'";
                    else if($row['cx_estatus']=="A") $color="style='background-color:#FFE447'";
                    else if($row['cx_estatus']=="F") $color="style='background-color:#47B6FF'";
                    else if($row['cx_estatus']=="C") {
                      $color="style='background-color:#000000; color:white;'";
                      $saldo=0;
                    }
                    $cliente=busca($row['cx_cliente'],'crm_clientes','c_id','c_alias');
                    if($cliente) $cliente=busca($row['cx_cliente'],'crm_clientes','c_id','c_alias');
                    else $cliente=$row['cx_cliente'];

                    if($row['cx_tipo'] == "P") $cliente=busca($row['cx_cliente'],'concesionarios','c_id','c_nmb');
                    
                    if(($row['cx_estatus']=='F') OR ($row['cx_estatus']=='C')) $link="";
                    else $link='<a data-fancybox data-type="ajax" data-src="popup/setingreso.php?idcxc='.$row['cx_id'].'&rand='.rand(1,100).'" href="javascript:;" >';

                    $sumamo+=$row['cx_importe'];
                    $sumaab+=$row['cx_abonado'];
                    $sumasa+=$saldo;

                    //if($row['cx_idsuite'] != NULL) $suite = '<a id="popup" target="_BLANK" href="?modulo=pedidosclw&accion=verpago&idped='.$row['cx_idsuite'].'&idcc='.$row['cx_id'].'">'.$row['cx_idsuite'].'</a>';
                    /*else*/ $suite = "-";
                    echo '<tr>';
                      echo '<th>
                        '.$link.'
                          <button class="btn btn-sm btn-info"><i class="fas fa-dollar-sign" style="color: #ffffff;"></i></button>
                        </a>
                      </th>';
                      echo '<td>'.fecha_formato(date('Y-m-d',strtotime($row['cx_fini'])),false,true).'</td>';
                      echo '<td>'.$cliente.'</td>';
                      echo '<td>'.$row['ct_descripcion'].'</td>';
                      echo '<td>'.$desc.'</td>';
                      echo '<td class="number-align">$'.number_format($row['cx_importe'],2).'</td>';
                      echo '<td class="number-align">$'.number_format($row['cx_abonado'],2).'</td>';
                      echo '<td class="number-align">$'.number_format($saldo,2).'</td>';
                      echo '<td '.$color.'>'.$estatusp[$row['cx_estatus']].'</td>';
                      echo'<th>
                        <a href="?modulo=cxcobrar&accion=show&idcxc='.$row['cx_id'].'">
                          <button class="btn btn-sm btn-info"><i class="fa fa-eye"></i></button>
                        </a>
                      </th>';
                      //echo '<td><a id="popup" href="popup/detallecobro.php?id='.$row['cx_id'].'" accesskey="d"><input type="button" value="" id="detalles" class="botonb"></a></td>';
                    echo ' </tr>';
                  }
                echo '</tbody>';
              echo '<tfoot>';
              echo '<tr>
                    <td colspan="5" style="text-align:right;">Totales</td>
                    <td class="number-align">$'.number_format($sumamo,2).'</td>
                    <td class="number-align">$'.number_format($sumaab,2).'</td>
                    <td class="number-align">$'.number_format($sumasa,2).'</td>
                    <td></td>
                    <td></td>
                                       
                    </tr>';    
              echo '</tfoot>
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
      </script>
        ';
    }
    function show(){
      $estatusa = array("P"=>"POR APROBAR","F"=>"APROBADO", "C"=> "CANCELADO");
      $tipo = busca($_GET['idcxc'],"cxcobrar INNER JOIN cortes_tipom ON cx_tipo = ct_clave","ct_tipo = 'X' AND cx_id","ct_descripcion");
      if($this->model->estatus == "C" || $this->model->estatus == "F") {
        $style1 = "";
        $style = 'display:none;';
      }else {
        $style = 'display:none';
        $style1 = "";
      }
      $rest = $this->model->importe - $this->model->abonado;
      $maximp = ' max="'.$rest.'" ';
      if($this->model->tipo == "R"){
        $desc = busca($this->model->referencia,'remisiones','r_id','r_nmb');
        if(busca($this->model->referencia,'remisiones','r_id','r_pedidoweb') != NULL){
          $desc = busca($this->model->referencia,'remisiones','r_id','r_nmb').'
          <a target="_BLANK" href="?modulo=pedidosclw&accion=show&id='.busca($this->model->referencia,'remisiones','r_id','r_pedidoweb').'">
            <button class="btn btn-primary"><i class="icon-world"></i></button>
          </a>';
        }
      }else $desc = $this->model->observaciones;
      $otros = '';
      $atras = '<a href="?modulo=cxcobrar&accion=index">
              <button class="mr-1 btn btn-sm btn-warning">
                <i class="fa fa-arrow-left"></i> Atrás
              </button>
            </a>';
      if($this->model->estatus != "C" && $this->model->estatus != "P" && $this->model->estatus != "F") {
        $otros .='<button class="mr-1 btn btn-primary btn-sm" id="agregar" title="Si tu cuenta por cobrar será pagada en parcialidades, programa las fechas de pago" onclick="addfechas(1);">
          <i class="far fa-calendar-alt" style="color: #ffffff;"></i> Programar
        </button>';
        $otros .= '<button class="mr-1 btn btn-sm btn-danger" id="cancelar" onclick="addfechas(0);" style="'.$style.'">
          <i class="fas fa-times-circle" style="color: #ffffff;"></i> Cancelar programación
        </button>';
        $otros .= '<button class="mr-1 btn btn-sm btn-secondary " data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Si tu cuenta por cobrar será pagada en parcialidades, programa las fechas de pago." data-trigger="click" >
          <i class="fas fa-question-circle"></i>
        </button>';
      }

      toolbar($_GET['modulo'], $atras, "", "", $otros);
      echo '<div class="card p-2 mt-5"  style="">
        <div class="row">
          <div class="text-xs-center col-md-4" >
            <div class="mb-5">
              <center>
                <div class="alert alert-info">Tipo: <b>'.$tipo.'</b></div>
              </center>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-5">
              <center>';
                if($this->model->tipo == "P") $cliente = busca($this->model->cliente,'concesionarios','c_id','c_nmb');
                else $cliente = busca($this->model->cliente,'crm_clientes','c_id','c_alias');
                echo '<div class="col-md-12 alert alert-success text-xs-center">Cliente: <b>'.$cliente.'</b></div>';
                echo '<input type="hidden" name="idcl" value="'.$this->model->cliente.'" id="idcl" class="form-control" placeholder="Id Cliente" readonly/>';
                echo'
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
          <div id="abonos" class="table-responsive" '.$style1.'>
            <div class="col-md-12 alert alert-primary text-xs-center">Historial de cobros para la cuenta "'.$desc.'"</div>
            <center>
              <table class="table table-striped table-hover table-bordered" style="width:50%">
                <thead>
                  <tr style="background-color:#1c3249;color:white;">
                    <th>Fecha</th>
                    <th>Cuenta</th>
                    <th>Monto</th>
                    <th>Estatus</th>
                    <th>Registró</th>
                  </tr>
                </thead>
                <tbody>';
                  $adjuntos = 0;
                  $totabonos = 0;
                  while($row = $this->model->resultab->fetch_array()){
                    if(!empty($row['i_adjunto']) && $row['i_adjunto']) $adjuntos++;
                    $cuenta = busca($row['ic_ingreso'],"ingresos INNER JOIN cuentas ON i_cuenta = cu_id","i_id","cu_nmb");
                    $totabonos += $row['ic_monto'];
                    echo'<tr style="background-color:#c0c8d1">
                      <td>'.$row['ic_fapli'].'</td>
                      <td>'.$cuenta.'</td>
                      <td class="number-align">$'.number_format($row['ic_monto'],2 ).'</td>
                      <td class="">'.$estatusa[$row['i_estatus']].'</td>
                      <td class="">'.$row['ic_uapli'].'</td>
                    </tr>';
                  }
                  if($this->model->resultab->num_rows == 0){
                    echo '<tr style="background-color:#c0c8d1">
                      <th colspan="4" style="text-align:center">Sin registro de cobros</th>
                    </tr>';
                  }else{
                    echo '<tr class="p-1 bg-grey bg-lighten-3">
                      <td >&nbsp;</td>
                      <td >Total</td>
                      <td class="number-align">$ '.number_format($totabonos,2).'</td>
                    </tr>';
                  }
                echo'</tbody>';
              echo'</table>
            </center>';
            if($adjuntos > 0){
              echo '<div class="col-md-12">
                <div class="col-md-12 alert alert-primary text-xs-center">Comprobante(s)</div>';
                while($rowad = $this->model->resultab1->fetch_array()){
                  if(!empty($rowad['i_adjunto']) && file_exists('Adjuntos/'.$_SESSION['emp'].'/'.$rowad['i_adjunto']))
                  echo'<a target="_BLANK" href="../Adjuntos/'.$_SESSION['emp'].'/'.$rowad['i_adjunto'].'">
                    <button type="button" class="btn btn-secondary"><i class="icon-attachment"></i> '.$rowad['i_adjunto'].'</button>
                  </a>';
                }
              echo'</div>';
            }
          echo'</div>';
          if($this->model->estatus != "C" && $this->model->estatus != "P" && $this->model->estatus != "F") {
            echo'<div class="fechas" id="fechasform" style="'.$style.'">
              <div class="col-md-12 alert alert-primary text-xs-center">Pagos programados de la cuenta por cobrar para '.$desc.'</div>
              <form id="formfechas" action="?modulo=cxcobrar&accion=programar&idcxc='.$_GET['idcxc'].'" method="post"> />
                <input type="hidden" name="ref" value="'.$this->model->referencia.'" />
                <input type="hidden" name="tipo" value="'.$this->model->tipo.'" />
                <div class="col-md-3 text-xs-center">
                  <label>Agregar Nueva Fecha: </label> <br>
                  <button type="button" class="btn btn-sm btn-primary" id="addfecha"><i class="fa fa-plus"></i> Agregar fecha </button>
                </div>
                <div class="" id="fechas">
                </div>
                <div class="col-md-12 row">
                  <div class="col-md-4">
                    <div class="mb-5">
                      <label for"nmbac">Fecha de pago: 1</label>
                      <input type="date" name="fprog[]" value="'.date('Y-m-d').'" min="'.date('Y-m-d').'" id="fprog[]" class="form-control fprog" placeholder="Fecha programada" required/>
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
                  <button type="button" class="btn btn-success" id="guardar" onclick="validimporte();"><i class="fa fa-save"></i> GUARDAR </button>
                </div>
              </form>
            </div>';
          }
        echo'</div>
      </div>';
      
      /// SELECTMAMALON
      echo'
      <script>
        function addfechas(i){
          abonos = document.getElementById("abonos");
          btncancelar = document.getElementById("cancelar");
          fechas = document.getElementById("fechasform");
          agregar = document.getElementById("agregar");

          if(i == 1){
            abonos.style.display = "none";
            btncancelar.style.display = "inline";
            agregar.style.display = "none";
            fechas.style.display = "block";
          }else{
            abonos.style.display = "block";
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
    function cobro($cuenta,$tipo){
      $tipop = array("T"=>"COBRO TAE","Y"=>"PROYECTO","S"=>"SOPORTE","Z"=>"POLIZA","P"=>"PRESTAMO","A"=>"COTIZACION","I"=>"COMISION");
      $tipoc = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
      ?>
      <script language="JavaScript">
        function habilita(aux){
          var resta = 0;
          document.getElementById("validar").checked=false;
          var totalp = document.getElementById("total").value;
          var aux1= aux+"c";
          var cuadro=parseFloat(document.getElementById("abonot").value);
          if(cuadro >= 0){
            resta = totalp-cuadro;
          }else{
            resta = Math.abs(cuadro);
          }
          var ant=parseFloat(document.getElementById(aux).value);

          if(document.getElementById(aux1).checked){
            var nuevo=cuadro+ant;
            document.getElementById(aux).disabled=false;
            document.getElementById(aux).value=resta;
            document.getElementById("abonot").value=nuevo.toFixed(2);
            valida(aux);
            if(nuevo < 0){
              document.getElementById("aplicar").style.display = "none";
            }
            else{
              document.getElementById("aplicar").style.display = "";
            }
          }else{
            var nuevo=cuadro-ant;
            document.getElementById(aux).disabled=true;
            document.getElementById(aux).value=0;
            document.getElementById("abonot").value=nuevo.toFixed(2);
            if(nuevo < 0){
              document.getElementById("aplicar").style.display = "none";
            }
            else{
              document.getElementById("aplicar").style.display = "";
            }
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
      if($tipo == "T") $t1 = "selected";
      if($tipo == "Y") $t2 = "selected";
      if($tipo == "S") $t3 = "selected";
      if($tipo == "Z") $t4 = "selected";
      if($tipo == "P") $t5 = "selected";
      if($tipo == "A") $t6 = "selected";

      if($_GET['proyecto']){ $var1="&proyecto=".$_GET['proyecto']; $titulo=" DE PROYECTO: ".busca($_GET['proyecto'],'proyectos','p_id','p_nmb');}
      else  { $var1=""; $titulo=""; }

      $sqla='SELECT * FROM cxcobrar WHERE cx_estatus IN ("A","N") AND cx_importe!=0 ';
      if($tipo)    $sqla.=' AND cx_tipo="'.$tipo.'"';
      if($_GET['proyecto'])    $sqla.=' AND cx_proyecto="'.$_GET['proyecto'].'"';

      $sqlp = "SELECT * FROM cuentas INNER JOIN cortes ON cu_id=c_cuenta WHERE cu_estatus='A' 
              AND cu_tipo!='A' AND c_estatus='A' ORDER BY cu_orden ASC, cu_id ASC";
        echo '
        <div class="div2" id="bboton01">';
          if($_GET['proyecto'])
            echo '<a href="?modulo=panel&accion=cobros&id='.$_GET['proyecto'].'" accesskey="n"><input type="button" class="botonb" id="proyectoi"></a>';
          else
            echo '<a href="?modulo=cxcobrar&accion=index" accesskey="z"><input type="button" value="" class="botonb" id="atras"></a>';
          echo '
        </div>
      </div>';
      echo '<table width="100%">
        <tr>
          <td width="50%" valign="top">';
            echo '<form action="?modulo=cxcobrar&accion=cobro'.$var1.'" method="post" onsubmit="return checkSubmitenviar();">';
              if($_GET['proyecto'])
                echo '<b>Tipo: </b><select name="tipo" id="tipo" onchange="this.form.submit()">
                  <option value="">TODOS</option>
                  <option value="Y" '.$t2.'>PROYECTO</option>
                  <option value="S" '.$t3.'>SOPORTE</option>
                  <option value="Z" '.$t4.'>POLIZA</option>
                  <option value="A" '.$t6.'>COTIZACION</option>
                </select>&nbsp;  ';
              else
                echo '<b>Tipo: </b><select name="tipo" id="tipo" onchange="this.form.submit()">
                  <option value="">TODOS</option>
                  <option value="T" '.$t1.'>COBRO TAE</option>
                  <option value="Y" '.$t2.'>PROYECTO</option>
                  <option value="S" '.$t3.'>SOPORTE</option>
                  <option value="Z" '.$t4.'>POLIZA</option>
                  <option value="P" '.$t5.'>PRESTAMOS</option>
                  <option value="A" '.$t6.'>COTIZACION</option>
                </select>&nbsp; ';
              echo '<table class="lista" width="100%" >
                <thead>
                  <tr>
                    <th colspan="7"><b>CUENTAS POR COBRAR'.$titulo.'</b></th>
                  </tr>
                </thead>
                <tr>
                  <th>Referencia</th>
                  <th>Cliente</th>
                  <th>Monto</th>
                  <th>Abonado</th>
                  <th>Saldo</th>
                  <th></th>';
                  $resulta=setq($sqla) or die($sqla);
                  while($row=$resulta->fetch_array()){
                    if($cuenta==$row['cx_id'])  $aux1="checked";
                    else $aux1="";
                    $saldo=($row['cx_importe'])-($row['cx_abonado']);

                    if($row['cx_estatus']=="N") $color="style='background-color:#FF5C5C;color:white;'";
                    else if($row['cx_estatus']=="A") $color="style='background-color:#FFE447'";

                    if($row['cx_tipo']=="T") $link='?modulo=tae&accion=show&id=';
                    if($row['cx_tipo']=="P") $link='?modulo=prestamos&accion=show&id=';
                    if($row['cx_tipo']=="A") $link='?modulo=cotizaciones&accion=show&id=';
                    else $link='?modulo=panel&accion=index&id=';

                    if(busca($row['cx_cliente'],'crm_clientes','c_id','c_alias')) $cliente=busca($row['cx_cliente'],'crm_clientes','c_id','c_alias');
                    else $cliente=$row['cx_cliente'];
                    echo '</tr>
                    <tr>
                      <td>'.$row['cx_referencia'].' - <a target="_BLANK" href="'.$link.$row['cx_referencia'].'">'.$tipop[$row['cx_tipo']].'</a></td>
                      <td>'.$cliente.'</td>
                      <td>$ '.$row['cx_importe'].'</td>
                      <td>$ '.$row['cx_abonado'].'</td>
                      <td '.$color.' >$ '.number_format($saldo,2).'</td>
                      <td><input type="radio" name="cuenta" value="'.$row['cx_id'].'" '.$aux1.' onclick="submit();" /></td>
                    </tr>';
                  }
              echo '</table>
            </form>';
          echo '</td>
          <td width="50%" valign="top"  >';
            if($cuenta){
              $estatus=busca($cuenta,'cxcobrar','cx_id','cx_estatus');
              if(($estatus=="N") OR ($estatus=="A")){
                $monto=busca($cuenta,'cxcobrar','cx_id','cx_importe');
                $abonado=busca($cuenta,'cxcobrar','cx_id','cx_abonado');
                $totala=$monto-$abonado;
                echo '<form action="?modulo=cxcobrar&accion=insertcobro'.$var1.'" method="post" onsubmit="return checkSubmitenviar();">
                  <table class="lista" width="100%" >
                    <thead>
                      <tr>
                        <th colspan="7"><b>MODOS DE COBRO PARA '.$tipop[busca($cuenta,'cxcobrar','cx_id','cx_tipo')].' '.busca($cuenta,'cxcobrar','cx_id','cx_referencia').'</b></th>
                      </tr>
                    </thead>
                    <tr>
                      <td colspan="5">
                        <span style="font-size:20px" >Total a cobrar:&nbsp;&nbsp;</span>
                        <span style="font-size:40px" > $'.number_format($totala,2).'</span>
                      </td>
                    </tr>
                    <tr>
                      <input type="hidden" name="total" id="total" value="'.$totala.'" />
                      <input type="hidden" name="cuenta" id="cuenta" value="'.$cuenta.'" />
                      <th>Cuenta</th>
                      <th>Tipo</th>
                      <th></th>
                      <th>Abono</th>';
                      $cont=0;
                      $resultp=setq($sqlp) or die($sqlp);
                      while($rwp=$resultp->fetch_array()){
                        $cont++;
                        echo '
                        </tr>
                        <tr>
                          <td>'.$rwp['cu_nmb'].'</td>
                          <td>'.$tipoc[$rwp['cu_tipo']].'</td>';
                          if($cont==1)
                            echo '<td><input type="checkbox" id="'.$cont.'c" name="'.$rwp['cu_id'].'c" onclick="habilita('.$cont.');" checked /></td>
                            <td><input type="number" onfocus="this.select();" step="0.01" style="width:70px;" min="0" max="'.$disponible.'"  onfocus="this.select();" name="'.$rwp['cu_id'].'" value="'.$totala.'" onchange="valida('.$cont.');" id="'.$cont.'" /></td>';
                          else
                            echo '<td><input type="checkbox" id="'.$cont.'c" name="'.$rwp['cu_id'].'c" onclick="habilita('.$cont.');" /></td>
                            <td><input type="number" onfocus="this.select();" step="0.01" style="width:70px;" disabled min="0" max="'.$disponible.'" onfocus="this.select();" name="'.$rwp['cu_id'].'" value="0.00" onchange="valida('.$cont.');" id="'.$cont.'" /></td>';
                        echo '</tr>';
                      }
                    echo '<input type="hidden" id="cont" value='.$cont.' />
                    <tr>
                      <td colspan="2"></td>
                      <td><b>Total:</b></td>
                      <td>
                        <input type="number" onfocus="this.select();" style="width:70px;" id="abonot" readonly value="'.$totala.'"/>
                      </td>
                    </tr>
                    <tr><td colspan="4"><b>Validar&nbsp;</b><input type="checkbox" required id="validar" /></td></tr>';
                    echo'<tr><td colspan="4"><input type="submit" value="" id="aplicar" class="botont"></td></tr>';
                    echo '
                  </table>
                </form>';
              }
            }
          echo '</td>
        </tr>
      </table>';
    }
    function condonacion($cuenta,$tipo){
      $tipop = array("T"=>"COBRO TAE","Y"=>"PROYECTO","S"=>"SOPORTE","Z"=>"POLIZA","P"=>"PRESTAMO","A"=>"COTIZACION","I"=>"COMISION");
          $tipoc = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
      ?>
      <script language="JavaScript">
        function habilita(aux){
          var resta = 0;
          document.getElementById("validar").checked=false;
          var totalp = document.getElementById("total").value;
          var aux1= aux+"c";
          var cuadro=parseFloat(document.getElementById("abonot").value);
          if(cuadro >= 0){
            resta = totalp-cuadro;
          }
          else{
            resta = Math.abs(cuadro);
          }
          var ant=parseFloat(document.getElementById(aux).value);

          if(document.getElementById(aux1).checked){
              var nuevo=cuadro+ant;
              document.getElementById(aux).disabled=false;
              document.getElementById(aux).value=resta;
              document.getElementById("abonot").value=nuevo.toFixed(2);
              valida(aux);
              if(nuevo < 0){
                document.getElementById("aplicar").style.display = "none";
              }
              else{
                document.getElementById("aplicar").style.display = "";
              }
          }
          else{
              var nuevo=cuadro-ant;
              document.getElementById(aux).disabled=true;
              document.getElementById(aux).value=0;
              document.getElementById("abonot").value=nuevo.toFixed(2);
              if(nuevo < 0){
                document.getElementById("aplicar").style.display = "none";
              }
              else{
                document.getElementById("aplicar").style.display = "";
              }
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
        if($tipo == "T") $t1 = "selected";
        if($tipo == "Y") $t2 = "selected";
        if($tipo == "S") $t3 = "selected";
        if($tipo == "Z") $t4 = "selected";
        if($tipo == "P") $t5 = "selected";
        if($tipo == "A") $t6 = "selected";

        if($_GET['proyecto']){ $var1="&proyecto=".$_GET['proyecto']; $titulo=" DE PROYECTO: ".busca($_GET['proyecto'],'proyectos','p_id','p_nmb');}
        else  { $var1=""; $titulo=""; }

        $sqla='SELECT * FROM cxcobrar WHERE cx_estatus IN ("A","N") AND cx_importe!=0 ';
        if($tipo)    $sqla.=' AND cx_tipo="'.$tipo.'"';
        if($_GET['proyecto'])    $sqla.=' AND cx_proyecto="'.$_GET['proyecto'].'"';

        $sqlp="SELECT * FROM cuentas INNER JOIN cortes ON cu_id=c_cuenta
          WHERE cu_estatus='A' AND cu_tipo!='A' AND c_estatus='A' ORDER BY cu_orden ASC, cu_id ASC";

        echo '<div class="div2" id="bboton01">';
        if($_GET['proyecto'])
          echo '<a href="?modulo=panel&accion=cobros&id='.$_GET['proyecto'].'" accesskey="n"><input type="button" class="botonb" id="proyectoi"></a>';
        else
          echo '<a href="?modulo=cxcobrar&accion=index" accesskey="z"><input type="button" value="" class="botonb" id="atras"></a>';
        echo '</div></div>';

      echo '</td><td width="50%" valign="top"  >';

        if($cuenta){
          $estatus=busca($cuenta,'cxcobrar','cx_id','cx_estatus');
          $monto=busca($cuenta,'cxcobrar','cx_id','cx_importe');
          $abonado=busca($cuenta,'cxcobrar','cx_id','cx_abonado');
          $abonado=$monto-$abonado;

          $sqlc='SELECT * FROM cortesd WHERE cd_cuenta="'.$cuenta.'" AND cd_tipo="C"';
          $resultc=setq($sqlc) or die($sqlc);
          if(mysql_num_rows($resultc) > 0){
              $aux1='CONDONACION PARA ';
              $aux2='Total a condonar: ';
              $aux3='Motivo de condonación: ';
              $cant=$abonado;
          }
          else{
              $aux1='CANCELACION DE ';
              $aux2='Monto de cuenta por cobrar a cancelar: ';
              $aux3='Motivo de cancelación: ';
              $cant=$monto;
          }

          echo '<form action="?modulo=cxcobrar&accion=condonarcobro'.$var1.'" method="post" onsubmit="return checkSubmitenviar();">
            <table class="lista" width="100%" >
            <thead><tr><th colspan="2"><b>'.$aux1.$tipop[busca($cuenta,'cxcobrar','cx_id','cx_tipo')].' '.busca($cuenta,'cxcobrar','cx_id','cx_referencia').'</b></th></tr></thead>
            <tr><td colspan="2"><span style="font-size:20px" >'.$aux2.'&nbsp;&nbsp;</span>
            <span style="font-size:40px" > $'.number_format($cant,2).'</span></td></tr>';

          if(busca($cuenta,'cxcobrar','cx_id','cx_idsuite')){
            echo '<tr><input type="hidden" name="cuenta" id="cuenta" value="'.$cuenta.'" />
                      <th>Motivo de Cancelación Interno</th>
                      <th>Motivo de Pago rechazado</th>
                  </tr>';
          ?>
            <script>
              function copiarmot(){
                document.getElementById("motivo2").value = document.getElementById("motivo1").value;
                document.getElementById("copiar1").checked = false;
                document.getElementById("user").focus();
              }
            </script>
          <?php
            echo '<tr>
                    <td><textarea name="motivo" id="motivo1" cols="60" rows="2" autofocus >'.$aux3.' </textarea></td>
                    <td>
                    Copiar anterior<input type="checkbox" id="copiar1" onchange="copiarmot();">
                    <br>
                    <textarea name="motivo" id="motivo2" cols="60" rows="2" > </textarea>
                    </td>
                  </tr>';
          }
          else{
            echo '<tr><input type="hidden" name="cuenta" id="cuenta" value="'.$cuenta.'" />
                      <th colspan="2">Motivo</th></tr>';
            echo '<tr><td colspan="2"><textarea name="motivo" cols="60" rows="4" >'.$aux3.' </textarea></td></tr>';
          }
          echo '<tr><th><b>Usuario</b></th><th><b>Contraseña</b></th></tr>';
          echo '<tr><td><input type="text" id="user" name="user" /></td><td><input type="password" name="pass"/></td></tr>';

          echo '<tr><td colspan="4"><b>Validar&nbsp;</b><input type="checkbox" required id="validar" /></td></tr>';
          echo'<tr><td colspan="4"><input type="submit" value="" id="aplicar" class="botont"></td></tr>';
          echo '</table></form>';
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
        if(x>2){
          var input = document.getElementsByClassName("fprog");
          //var variableminus = "foprog["+(x-1)+"].value";
          //var variable = "foprog["+(x)+"].value";
          //console.log(variable+' '+variableminus);
          /*const date = new Date(input[(0)].value);
          const date2 = new Date(input[(1)].value);
          var dias = (date2.getDate() - date.getDate());
          console.log(dias);
          if(date2.getMonth()+1 < 10) var mes = '0'+(date2.getMonth()+1);
          else var mes = (date2.getMonth()+1);
          if((date2.getDate()+(dias+1)) < 10) var diasfin = '0'+(date2.getDate()+(dias+1));
          else var diasfin = (date2.getDate()+(dias+1));
          var resultado = date2.getFullYear() +'-'+ mes +'-'+ diasfin; 
          console.log(resultado+' '+mes+' '+diasfin+' '+date2.getFullYear());*/
          //var variable = "fprog"+x;
          //document.getElementById(variable.id).focus();
          let date2;
          let date;
          for (var i = 0; i < input.length; i++) {
            if(i === 0) {
              date = new Date(input[(i)].value);
            }
            if(i > 0){
              date = new Date(input[(i-1)].value);
              date2 = new Date(input[(i)].value);
            }
          }
          var dias = (date2.getDate() - date.getDate());
          if(date2.getMonth()+1 < 10) var mes = '0'+(date2.getMonth()+1);
          else var mes = (date2.getMonth()+1);
          if((date2.getDate()+(dias+1)) < 10) var diasfin = '0'+(date2.getDate()+(dias+1));
          else var diasfin = (date2.getDate()+(dias+1));
          var resultado = date2.getFullYear() +'-'+ mes +'-'+ diasfin; 
          //var resultado = 0;
        }
        $('#fechas').prepend('<div class="col-md-12 mb-1">\
          <div class="row">\
            <div class="col-md-4">\
              <div class="mb-5">\
                <label>Fecha de pago:'+x+'</label>\
                <input type="date" name="fprog[]" id="fprog[]" class="form-control fprog" placeholder="Fecha programada" value="'+resultado+'" required />\
              </div>\
            </div>\
            <div class="col-md-4">\
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