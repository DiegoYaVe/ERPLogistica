<?php
  /* if($_SESSION['uid'] == "ADMIN") */
  ini_set('display_errors', 0);
  class tableros{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modeltableros(isset($obj));
    }
    function index(){
      //echo 'token: '; print_r($_SESSION['access_token']);
      //echo '<br>'.' refresh '.$_SESSION['refresh_token'];
      /*if (isset($_GET['code'])) {
        require_once 'vendor/autoload.php';
        $client = new Google_Client();
        $client->setAuthConfig("vendor/credentials.json");
        //$client->addScope('https://www.googleapis.com/auth/calendar');
        //$client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
        $client->setAccessType("offline");
        $client->setPrompt("consent");
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        $_SESSION['refresh_token'] = $client->getRefreshToken(); 
        crearActividad($_SESSION['e_nombre'],$_SESSION['e_desc'],$_SESSION['e_fecha'],$_SESSION['e_location'],$_SESSION['e_resp'],$_SESSION['accionT']);
      }*/ 

      if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "O";
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-10 days'));
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
      if(!isset($_REQUEST['agente'])) $_REQUEST['agente'] = NULL;
      

      /* $this->model->result(trim($_REQUEST['nmb']),$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['page'],$_REQUEST['agente']); */
      $this->model->result(trim($_REQUEST['nmb']),$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['page'],$_REQUEST['agente']);
      $this->view = new viewtableros($this->model);
      $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['page'],$_REQUEST['agente']);
    }
    function insert() {
  $acrof = busca($_SESSION['emp'], 'empresas', 'e_id', 'e_siglas');
  $folio = getmax('ct_folio', 'crm_tableros');
  $cliente = getIDcliente($_POST['cliente']);
  $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');

  // Conexión para escape
  $db = new mysqli("localhost", "root", "", "erp_logistica_dvl");

  if (!$cliente) {
    $cliente = busca($_POST['telefonocl'], 'crm_clientes', 'c_telefono1 = "' . $_POST['telefonocl'] . '" OR c_telefono2', 'c_id');
    if (!$cliente) {
      $sql = 'INSERT INTO crm_clientes SET c_alias = "' . $db->real_escape_string($_POST['nmbcl']) . '",
                                            c_nmb = "' . $db->real_escape_string($_POST['nmbcl']) . '",
                                            c_telefono2 = "' . $db->real_escape_string($_POST['telefonocl']) . '",
                                            c_uregistro = "' . $_SESSION['uid'] . '",
                                            c_fregistro = "' . date('Y-m-d H:i:s') . '"';
      setq($sql);
      $cliente = getmax('c_id', 'crm_clientes', false, false);
    }
  } else {
    $uregistro = busca($cliente, 'crm_clientes', 'c_id', 'c_uregistro');
    if ($grupo != "ADMIN" && $grupo != "GERENCIA") {
      if (trim($uregistro) != trim($_SESSION['uid'])) {
        echo '<script> alert("No se puede generar el tablero, el cliente ya está registrado por otro vendedor. Registro: ' . $uregistro . ' usuario: ' . $_SESSION['uid'] . '");</script>';
        redirect('?modulo=tableros&accion=index');
        die();
      }
    }

    $sql = 'UPDATE crm_clientes SET c_alias = "' . $db->real_escape_string($_POST['nmbcl']) . '",
                                    c_nmb = "' . $db->real_escape_string($_POST['nmbcl']) . '",
                                    c_telefono2 = "' . $db->real_escape_string($_POST['telefonocl']) . '" WHERE c_id = "' . $cliente . '"';
    setq($sql);

    // ✅ CORREGIDO: Mensaje completo, concatenado y escapado
    $mensaje = 'Hola ' . $_POST['nmbcl'] . ' A continuación encontrarás listados los productos de acuerdo con tu solicitud de cotización';
    $mensaje_escapado = $db->real_escape_string($mensaje);
    $nmbcl_escapado = $db->real_escape_string($_POST['nmbcl']);

    $sqlupd = 'UPDATE crm_cotizaciones 
               SET cc_destino = "' . $nmbcl_escapado . '",
                   cc_mensaje = "' . $mensaje_escapado . '"
               WHERE cc_cliente = "' . $cliente . '" AND cc_estatus NOT IN ("V", "A")';
    setq($sqlupd);
  }

  if (isset($_POST['idlead'])) {
    $sqll = 'SELECT cl_cp, cl_correo FROM crm_leads WHERE cl_id = "' . $_POST['idlead'] . '"';
    $result = setq($sqll);
    list($cp, $correo) = $result->fetch_array();

    if ($cp) {
      $direccion = busca($cp, 'crm_direcciones', 'cd_cliente = "' . $cliente . '" AND cd_cp', 'cd_id');
      if (!$direccion) {
        $sqlins = 'INSERT INTO crm_direcciones SET cd_cp = "' . $db->real_escape_string($cp) . '",
                                                      cd_cliente = "' . $cliente . '"';
        setq($sqlins);
        $direccion = getmax('cd_id', 'crm_direcciones', false, false);
      } else {
        $direccion = "0";
      }
    }

    if ($correo) {
      $sqlupd = 'UPDATE crm_clientes SET c_correo1 = "' . $db->real_escape_string($correo) . '" WHERE c_id = "' . $cliente . '"';
      setq($sqlupd);
    }
  }

  $fini = explode("T", $_POST['fini']);

  $this->model->setdata(
    NULL,
    $_POST['nmb'],
    $folio,
    $cliente,
    $_POST['responsable'],
    $fini[0],
    $fini[1],
    $_POST['visibility'],
    $_POST['fcierre'],
    $_POST['niveltab'],
    "A",
    $_POST['valor']
  );
  $this->model->insert();

  include_once('cotizaciones.php');
  $cotiza = new modelcotizaciones;
  $acronimo = busca(1, 'empresas', 'e_id', 'e_siglas');
  $sqlm = 'SELECT COUNT(*) FROM crm_cotizaciones WHERE cc_folio LIKE "CT' . $acronimo . '%"';
  $resultm = setq($sqlm);
  list($maxcot) = $resultm->fetch_array();
  $maxcot++;

  $uuid = $cotiza->guidv4();

  $foliocot = 'CT' . $acronimo . str_pad($maxcot, 6, "0", STR_PAD_LEFT);
  $iva = isset($_POST['iva']) ? "1" : "0";
  $total = isset($_POST['total']) ? "1" : "0";
  $envio = isset($_POST['reccliente']) ? "C" : "P";

  $nmbagente = busca($_POST['responsable'], 'usuarios', 'u_id', 'CONCAT(u_nmb," ",u_apellidos)');
  $puestoagente = busca($_POST['responsable'], 'usuarios', 'u_id', 'u_puesto');
  $copiamail = busca($_POST['responsable'], 'usuarios', 'u_id', 'u_mailcorp');
  $nmbcliente = busca($cliente, 'crm_clientes', 'c_id', 'c_nmb');
  $telcliente = busca($cliente, 'crm_clientes', 'c_id', 'c_telefono2');
  $mailcliente = busca($cliente, 'crm_clientes', 'c_id', 'c_correo1');

  $cotiza->SetData(
    NULL,
    $this->model->idt,
    $foliocot,
    $_POST['nmb'],
    $_POST['fcierre'],
    "",
    "0",
    "1",
    "N",
    "0",
    $nmbcliente,
    $telcliente,
    $mailcliente,
    $_POST['responsable'],
    "0",
    "0",
    $nmbagente,
    $puestoagente,
    $copiamail,
    $direccion,
    "",
    $uuid,
    "1",
    "P"
  );
  $cotiza->insertcotiza();

  $sql = 'SELECT * FROM condicionesc WHERE c_obligatorio = "1" AND c_estatus = "A"';
  $result = setq($sql);
  while ($row = $result->fetch_array()) {
    $_POST['estatus'] = "A";
    $_POST['orden'] = getmax('cf_orden', 'crm_cotizaciones_condiciones', 'cf_cotizacion = "' . $cotiza->idc . '"');
    $_GET['idc'] = $cotiza->idc;
    $cotiza->setDataCondicion($row['c_nmb']);
    $cotiza->insertcondicion($row['c_id']);
  }

  $sqlch = 'INSERT INTO crm_cotizaciones_historial SET cch_cotizacion = "' . $cotiza->idc . '",
                                                       cch_fecha = "' . date('Y-m-d H:i:s') . '",
                                                       cch_user = "' . $_SESSION['uid'] . '",
                                                       cch_descripcion = "CREACIÓN DE LA COTIZACIÓN"';
  setq($sqlch);

  redirect('?modulo=cotizaciones&accion=index&id=' . $cotiza->idc);
}

    function show(){
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "S";
      if(!isset($_REQUEST['tipo'])) $_REQUEST['tipo'] = "T";
      $this->model->select($_GET['id']);
      $this->model->resultactividades($_GET['id'],$_REQUEST['estatus'], $_REQUEST['tipo']);
      $this->view = new viewtableros($this->model);
      $this->view->show($_REQUEST['estatus'], $_REQUEST['tipo']);
    }
    function insertact(){
      if(!isset($_POST['liga'])) $_POST['liga'] = NULL;
      if(!isset($_GET['tablero'])) $tablero = 0;
      else $tablero = $_GET['tablero'];

      $this->model->SetDataActividad(NULL,$tablero,$_POST['actividad'],$_POST['nmbac'],$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['fini'],$_POST['duracion'],$_POST['showas'],$_POST['descripcion'],$_POST['lugar'],$_POST['liga'],$_POST['ubicacion']);
      $this->model->idt = $this->model->insertact();

      if($_POST['actividad'] == "8"){
        $sqlupduser = 'UPDATE crm_actividades SET ca_user = "'.$_SESSION['uid'].'" WHERE ca_id = "'.$this->model->idt.'"';
        setq($sqlupduser);
      }

      include('usuarios.php');
      $user = new modelusuarios();
      $user->select($_SESSION['uid']);

      $this->model->SetDataActdd($this->model->idt,"U",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_SESSION['uid'],NULL);
      $this->model->Invitadosact();

      if($_POST['responsable'] != $_SESSION['uid']){
        $user->select($_POST['responsable']);
        $this->model->SetDataActdd($this->model->idt,"R",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['responsable'],NULL);
        $this->model->Invitadosact();
      }
      if(isset($_POST['inv'])){
        for($i=0;$i<sizeof($_POST['inv']);$i++){
          $user->select($_POST['inv'][$i]);
          if(busca($_POST['inv'][$i],'crm_actividadesd','cad_actividad='.$this->model->idt.' AND cad_user','COUNT(*)') == 0){
            $this->model->SetDataActdd($this->model->idt,"I",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['inv'][$i],NULL);
            $this->model->Invitadosact();
          }
        }
      }

      if(isset($_GET['tablero'])){
        $this->model->SetDataActdd($this->model->idt,"C",$_POST['destino'],$_POST['telefono'],$_POST['correo'],NULL,"A",NULL,NULL);
        $this->model->Invitadosact();
      }
      if(isset($_POST['ext'])){
        for($i=0;$i<sizeof($_POST['ext']);$i++){
          $this->model->SetDataActdd($this->model->idt,"E",$_POST['ext'][$i],$_POST['teli'][$i],$_POST['corr'][$i],NULL,"A",NULL,NULL);
          $this->model->Invitadosact();
        }
      }
      /*if (isset($_SESSION['access_token']) && $_SESSION['access_token'] != "") {
        if($this->model->tablero == 0) $_SESSION['accionT'] = redirect('?modulo=actividades&accion=index');
        else $_SESSION['accionT'] = redirect('?modulo=tableros&accion=show&id='.$this->model->tablero);
        require_once 'vendor/autoload.php';
        $client = new Google_Client();
        $client->setAuthConfig("vendor/credentials.json");
        //$client->addScope('https://www.googleapis.com/auth/calendar');
        //$client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
        $client->setAccessType("offline");
        $client->setPrompt("consent");
        //$client->authenticate($_GET['code']);
        //$_SESSION['refresh_token'] = $client->getRefreshToken();
        crearActividad($_POST['nmbac'],$_POST['descripcion'],$_POST['fini'],$_POST['lugar'],$_POST['correoresp'],$_SESSION['accionT']);
      }else{
        if($this->model->tablero == 0) $_SESSION['nredirect'] = "actividad";
        else $_SESSION['nredirect'] = $this->model->tablero;
        crearToken($_POST['nmbac'],$_POST['descripcion'],$_POST['fini'],$_POST['lugar'],$_POST['correoresp']);
      }*/

      if(isset($_GET['cal'])) $link = '?modulo=actividades&accion=show';
      elseif($this->model->tablero == 0) $link = '?modulo=actividades&accion=index';
      else $link = '?modulo=tableros&accion=show&id='.$this->model->tablero;

      redirect($link);
    }
    function updateact(){
      if(!isset($_POST['liga'])) $_POST['liga'] = NULL;
      if(isset($_GET['origen']) && $_GET['origen'] == "1") $_GET['tablero'] = 0;
      
      $this->model->SetDataActividad($_GET['idact'],$_GET['tablero'],$_POST['actividad'],$_POST['nmbac'],$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['fini'],$_POST['duracion'],$_POST['showas'],$_POST['descripcion'],$_POST['lugar'],$_POST['liga'],$_POST['ubicacion']);
      $this->model->updateact();

      include('usuarios.php');
      $user = new modelusuarios();

      if(isset($_POST['inv'])){
        for($i=0;$i<sizeof($_POST['inv']);$i++){
          $user->select($_POST['inv'][$i]);
          if(isset($_POST['idi'.$_POST['inv'][$i]])){
            $this->model->SetDataActdd($_GET['idact'],"I",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['inv'][$i],$_POST['idi'.$_POST['inv'][$i]]);
          }else{
            if(busca($_POST['inv'],'crm_actividadesd','cad_actividad='.$_GET['idact'].' AND cad_user','COUNT(*)') == 0){
              $this->model->SetDataActdd($_GET['idact'],"I",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['inv'][$i],NULL);
              $this->model->Invitadosact();
            }
          }
        }
      }

      if(isset($_GET['tablero'])){
        $this->model->SetDataActdd($_GET['idact'],"C",$_POST['destino'],$_POST['telefono'],$_POST['correo'],NULL,"A",NULL,$_POST['idcliente']);
        $this->model->UpdInvitadosact();
      }
      $externo = $_POST['ex'];
      if(isset($_POST['ext'])){
        for($i=0;$i<sizeof($_POST['ext']);$i++){
          if($_POST['ex'] > 0 && $externo != 0){
            $this->model->SetDataActdd($_GET['idact'],"E",$_POST['ext'][$i],$_POST['teli'][$i],$_POST['corr'][$i],NULL,"A",NULL,$_POST['ide'.($i+1)]);
            $this->model->UpdInvitadosact();
            $externo --;
          }else{
            $this->model->SetDataActdd($_GET['idact'],"E",$_POST['ext'][$i],$_POST['teli'][$i],$_POST['corr'][$i],NULL,"A",NULL,NULL);
            $this->model->Invitadosact();
          }
        }
      }

      if(isset($_GET['cal'])) $link = '?modulo=actividades&accion=show';
      elseif($this->model->tablero == 0) $link = '?modulo=actividades&accion=index';
      else $link = '?modulo=tableros&accion=show&id='.$this->model->tablero;

      redirect($link);
    }
    function estatusactividad(){
      $sql = 'UPDATE crm_actividades SET ca_estatus = "'.mb_strtoupper($_GET['estatus']).'"';
      if(isset($_GET['desc'])) $sql .= ', ca_descripcion = "'.clearvmayus($_GET['desc']).'"';
      $sql .= 'WHERE ca_id = "'.$_GET['id'].'"';
      setq($sql);

      if(!isset($_GET['origen']) || $_GET['origen'] == 2) {
        $tablero = busca($_GET['id'],'crm_actividades','ca_id','ca_tablero');
        redirect('?modulo=tableros&accion=show&id='.$tablero);
      }else redirect('?modulo=actividades&accion=index');
    }
    function deladjact(){
      $tablero = busca($_GET['idact'],'crm_actividades','ca_id','ca_tablero');
      $this->model->deladjact($_GET['idact']);

      $link = '?modulo=actividades&accion=index&ida='.$_GET['idact'];

      redirect($link);
    }
    function endtablero(){
      $this->model->endtablero($_GET['id'],$_GET['estatus']);
      $recurrente = busca($_GET['id'],'crm_tablerosrecurrentes','ctr_estatus = "A" AND ctr_tablero','COUNT(*)');
      if($recurrente > 0) $this->model->endrecurrente($_GET['id']);
      redirect('?modulo=tableros&accion=index');
    }
    function updateneg(){
      if(!isset($_GET['tablero'])) $tablero = $_GET['id'];
      else $tablero = $_GET['tablero'];

      if(isset($_POST['niveltab'])) $nivel = $_POST['niveltab'];
      else $nivel = $_POST['negtab'.$tablero];

      $this->model->updateneg($tablero,$nivel);
      if(isset($_GET['tablero'])) redirect('?modulo=tableros&accion=show&id='.$_GET['tablero']);
      else redirect('?modulo=tableros&accion=index');
    }
    function updatetablero(){
      if($_POST['niveltab'] != 4){
        $this->model->setdata($_GET['id'],$_POST['nmb'],NULL,NULL,$_POST['responsable'],NULL,NULL,NULL,$_POST['fcierre'],$_POST['niveltab'],NULL, $_POST['valor']);
        $this->model->updatetablero();
        $sql = 'UPDATE crm_clientes SET c_alias = "'.$_POST['nmbcl'].'",
                                        c_nmb = "'.$_POST['nmbcl'].'",
                                        c_telefono2 = "'.$_POST['telcl'].'" WHERE c_id = "'.$_POST['idcliente'].'"';
        setq($sql);                                      
        $link = '?modulo=tableros&accion=show&id='.$_GET['id'];
      }else{
        $this->model->updateneg($_GET['id'],$_POST['niveltab']);
        $link = '?modulo=tableros&accion=index';
      }

      redirect($link);
    }
  }

  class modeltableros{
    function select($id){
      $sql = 'SELECT * FROM crm_tableros WHERE ct_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['ct_id'];
      $this->empresa = $row['ct_empresa'];
      $this->nmb = $row['ct_nmb'];
      $this->folio = $row['ct_folio'];
      $this->cliente = $row['ct_cliente'];
      $this->agente = $row['ct_agente'];
      $this->fini = $row['ct_fini'];
      $this->hini = $row['ct_hini'];
      $this->visibility = $row['ct_visibility'];
      $this->fcierre = $row['ct_fcierre'];
      $this->estatus = $row['ct_estatus'];
      $this->negociacion = $row['ct_nnegociacion'];
      $this->valor = $row['ct_valor'];
    }
    function result($nmb,$estatus,$fini,$ffin,$page,$agente){ //filtro 
      $bloque = 20;
      $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
      $sql = 'SELECT * FROM crm_tableros WHERE ct_fini BETWEEN "'.$fini.'" AND "'.$ffin.'"';
      
      /*
      if($grupo == "ADMIN" || $grupo == "GERENCIA" || $grupo == "SUBGERENCIA"){
        if($agente)$sql .= ' AND ct_agente = "'.$agente.'"';
        else  $sql .= "";
      } else {
        $sql .= ' AND ct_agente = "'.$_SESSION['uid'].'"';
      }
        */
    /* 
      if($estatus == "O") $sql.=' AND ct_estatus IN ("N","P","G","V","A") ';
      elseif($estatus == "W") $sql.=' AND ct_estatus IN ("F","V","A") ';
      elseif($estatus == "I") $sql.=' AND ct_estatus IN ("N","P") ';
      elseif($estatus == "L") $sql.=' AND ct_estatus IN ("X","C") ';
      elseif($estatus == "T") $sql.=' ';
      else $sql.=' AND ct_estatus = "'.$estatus.'" ';
 */
      if($nmb) $sql.= ' AND (ct_nmb LIKE "%'.$nmb.'%" OR ct_cliente IN
                        (SELECT c_id FROM crm_clientes WHERE c_nmb LIKE "%'.$nmb.'%" OR c_alias LIKE "%'.$nmb.'%" OR c_apellidos LIKE "%'.$nmb.'%"))';
      //$result = setq($sql);
      $sql.= ' ORDER BY ct_id DESC ';
       $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
      //die($sql);
      $this->result = setq($sql);
      $this->resultt = setq($sql);
    }
    function setdata($id,$nmb,$folio,$cliente,$responsable,$fini,$hini,$visibility,$fcierre,$niveltab,$estatus, $valor){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'");
      $cambio = "";

      $this->idT = $id;
      $this->nmb = clearvmayus($nmb);
      $this->folio = clearvmayus($folio);
      $this->cliente = clearvmayus($cliente);
      $this->agente = clearvmayus($responsable);
      $this->fini = clearvmayus($fini);
      $this->hini = clearvmayus($hini);
      $this->visibility = clearvmayus($visibility);
      $this->fcierre = clearvmayus($fcierre);
      $this->estatus = clearvmayus($estatus);
      $this->negociacion = $niveltab;
      $this->valor = $valor;
    }
    function insert(){
      $sql = 'INSERT INTO crm_tableros SET
              ct_nmb = "'.$this->nmb.'",
              ct_folio = "'.$this->folio.'",
              ct_cliente = "'.$this->cliente.'",
              ct_agente = "'.$this->agente.'",
              ct_fini = "'.$this->fini.'",
              ct_hini = "'.$this->hini.'",
              ct_visibility = "'.$this->visibility.'",
              ct_nnegociacion = "'.$this->negociacion.'",
              ct_valor = "'.$this->valor.'",
              ct_fcierre = "'.$this->fcierre.'"';
      setq($sql);

      $this->idt = getmax('ct_id','crm_tableros',false,false);
    }
    function resultactividades($id,$estatus,$tipo){
      $sql = 'SELECT crm_actividades.*,crm_acciones.ca_icono icon FROM crm_actividades
              INNER JOIN crm_acciones ON crm_actividades.ca_accion = crm_acciones.ca_id
              WHERE crm_actividades.ca_tablero = "'.$id.'" ';
      if($estatus == "X") $sql.= ' AND crm_actividades.ca_estatus IN ("C") ';
      elseif($estatus == "S") $sql.= ' AND crm_actividades.ca_estatus IN ("N","P","F") ';
      elseif($estatus == "T") $sql.= '  ';
      else $sql.='  AND crm_actividades.ca_estatus IN ("'.$estatus.'") ';

      if($tipo != "T") $sql.=' AND crm_actividades.ca_accion = "'.$tipo.'" ';

      $sql.=' ORDER BY crm_actividades.ca_estatus DESC, crm_actividades.ca_fecha DESC,crm_actividades.ca_hora ASC';
      $this->resultac = setq($sql);
    }
    function SetDataActividad($id,$tablero,$actividad,$nmbac,$destino,$telefono,$correo,$fini,$duracion,$showas,$descripcion,$lugar,$liga,$ubicacion){
      $fini = str_replace('T',' ',$fini);

      $this->id = $id;
      $this->tablero = $tablero;
      $this->actividad = clearvmayus($actividad);
      $this->nmbac = clearvmayus($nmbac);
      $this->fecha = date('Y-m-d',strtotime($fini));
      $this->hora = date('H:i:s',strtotime($fini));
      $this->duracion = $duracion;
      $this->showas = $showas;
      $this->descripcion = clearvmayus($descripcion);
      $this->lugar = clearvmayus($lugar);
      $this->liga = clearvminus($liga);
      $this->ubicacion = clearvminus($ubicacion);
    }
    function insertact(){
      $sql = 'INSERT INTO crm_actividades SET
              ca_tablero = "'.$this->tablero.'",
              ca_accion = "'.$this->actividad.'",
              ca_nmb = "'.$this->nmbac.'" ,
              ca_fecha = "'.$this->fecha.'",
              ca_hora = "'.$this->hora.'",
              ca_duracion = "'.$this->duracion.'",
              ca_lugar = "'.$this->lugar.'",
              ca_ubicacion = "'.$this->ubicacion.'",
              ca_descripcion = "'.$this->descripcion.'",
              ca_vercomo = "'.$this->showas.'"';
      setq($sql);
      $maxcu = getmax("ca_id","crm_actividades",false,false);

      if (isset($_FILES['adjunto']['name'])) {
        $diradj = 'Adjuntos/'.$_SESSION['emp'].'/actividades';
        if (!is_dir($diradj)) {
          @mkdir($diradj, 0777);
        }
        $info = new SplFileInfo(basename($_FILES['adjunto']['name']));
        $nombre_archivo = $_FILES['adjunto']['name'];
        $tamano_archivo = $_FILES['adjunto']['size'];
        $ext = $info->getExtension();
        $target = $diradj.'/'.$nombre_archivo;
        if($tamano_archivo > 3000000) $error = "6XMUE2";
        else{
          if (move_uploaded_file($_FILES['adjunto']['tmp_name'],  $target)){
            $sql = 'UPDATE crm_actividades SET ca_adjunto = "'.$nombre_archivo.'", ca_adjuntoext = "'.$ext.'"
                    WHERE ca_id = "'.$maxcu.'"';
            setq($sql);
          }
        }
      }

      return($maxcu);
    }
    function updateact(){
      $sql = 'UPDATE crm_actividades SET
              ca_nmb = "'.$this->nmbac.'" ,
              ca_fecha = "'.$this->fecha.'",
              ca_hora = "'.$this->hora.'",
              ca_duracion = "'.$this->duracion.'",
              ca_lugar = "'.$this->lugar.'",
              ca_ubicacion = "'.$this->ubicacion.'",
              ca_descripcion = "'.$this->descripcion.'",
              ca_vercomo = "'.$this->showas.'"
              WHERE ca_id = "'.$this->id.'"';
      setq($sql);
      
      if (isset($_FILES['adjunto']['name'])) {
        $diradj = 'Adjuntos/'.$_SESSION['emp'].'/actividades';
        if (!is_dir($diradj)) {
          @mkdir($diradj, 0777);
        }
        $info = new SplFileInfo(basename($_FILES['adjunto']['name']));
        $nombre_archivo = $_FILES['adjunto']['name'];
        $tamano_archivo = $_FILES['adjunto']['size'];
        $ext = $info->getExtension();
        $target = $diradj.'/'.$nombre_archivo;
        if($tamano_archivo > 3000000) $error = "6XMUE2";
        else{
          if (move_uploaded_file($_FILES['adjunto']['tmp_name'],  $target)){
            $sql = 'UPDATE crm_actividades SET ca_adjunto = "'.$nombre_archivo.'", ca_adjuntoext = "'.$ext.'"
                    WHERE ca_id = "'.$this->id.'"';
            setq($sql);
          }
        }
      }
    }
    function SetDataActdd($idact,$tipo,$destino,$telefono,$correo,$observaciones,$estatus,$user,$cadid){
      $this->idact = $idact;
      $this->tipo = clearvmayus($tipo);
      $this->destino = clearvmayus($destino);
      $this->telefono = $telefono;
      $this->correo = clearvminus($correo);
      $this->observaciones = clearvmayus($observaciones);
      $this->estatus = clearvmayus($estatus);
      $this->user = clearvmayus($user);
      $this->cadid = $cadid;
    }
    function Invitadosact(){
      $sql = 'INSERT INTO crm_actividadesd SET
              cad_actividad = "'.$this->idact.'",
              cad_tipomiembro = "'.$this->tipo.'",
              cad_miembro = "'.$this->destino.'",
              cad_user = "'.$this->user.'",
              cad_correo = "'.$this->correo.'",
              cad_telefono = "'.$this->telefono.'",
              cad_observaciones = "'.$this->observaciones.'",
              cad_estatus= "'.$this->estatus.'"';
      setq($sql);
    }
    function UpdInvitadosact(){
      $sql = 'UPDATE crm_actividadesd SET
              cad_miembro = "'.$this->destino.'",
              cad_correo = "'.$this->correo.'",
              cad_telefono = "'.$this->telefono.'",
              cad_observaciones = "'.$this->observaciones.'",
              cad_estatus= "'.$this->estatus.'"
              WHERE cad_actividad = "'.$this->idact.'" AND cad_id = "'.$this->cadid.'"';
      setq($sql);
    }
    function selectact($id){
      $sql = 'SELECT * FROM crm_actividades WHERE ca_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->ida = $row['ca_id'];
      $this->tableroa = $row['ca_tablero'];
      $this->accion = $row['ca_accion'];
      $this->nmb = $row['ca_nmb'];
      $this->hora = $row['ca_hora'];
      $this->duracion = $row['ca_duracion'];
      $this->lugar = $row['ca_lugar'];
      $this->ubicacion = $row['ca_ubicacion'];
      $this->adjunto = $row['ca_adjunto'];
      $this->descripcion = $row['ca_descripcion'];
      $this->liga = $row['ca_liga'];
      $this->vercomo = $row['ca_vercomo'];
      $this->estatus = $row['ca_estatus'];
    }
    function deladjact($idact){
      $adjuntoext = busca($idact,'crm_actividades','ca_id','ca_adjuntoext');

      $sql = 'UPDATE crm_actividades SET ca_adjunto = "", ca_adjuntoext = NULL
              WHERE ca_id = "'.$idact.'"';
      setq($sql);

      unlink('Adjuntos/'.$_GET['idact'].'.'.$adjuntoext);
    }
    function endtablero($id,$estatus){
      $sql = 'UPDATE crm_tableros SET ct_estatus = "'.$estatus.'" WHERE ct_id = "'.$id.'"';
      setq($sql);

      if($estatus == "C"){
        /* $sqlrem = 'UPDATE remisiones SET r_estatus = "C", r_ufcan WHERE r_tablero = "'.$id.'" AND r_estatus = "N"';
        setq($sqlrem); */
        $sqlcot = 'UPDATE crm_cotizaciones SET cc_estatus = "C" WHERE cc_tablero = "'.$id.'" AND cc_estatus IN ("N","E")';
        setq($sqlcot);
      }
    }
    function endrecurrente($id){
      $sql = 'UPDATE crm_tablerosrecurrentes SET ctr_estatus = "F" WHERE ctr_tablero = "'.$id.'"';
      setq($sql);
    }
    function updateneg($tablero,$nivelneg){
      $sql = 'UPDATE crm_tableros SET ct_nnegociacion = "'.$nivelneg.'" ';
      if($nivelneg == 4){ $sql .= ', ct_estatus = "X"';}
      $sql .= ' WHERE ct_id = "'.$tablero.'"';
      setq($sql);
    }
    function updatetablero(){
      $sql = 'UPDATE crm_tableros SET ct_agente = "'.$this->agente.'", ct_nmb = "'.$this->nmb.'", ct_fcierre = "'.$this->fcierre.'",
              ct_nnegociacion = "'.$this->negociacion.'", ct_valor = "'.$this->valor.'" WHERE ct_id = "'.$this->idT.'"';
      setq($sql);
    }
  }

  class viewtableros{
    var $model;
    function __construct($model){
      ?>
      <script>
      function checkguardar(){
        document.getElementById("sendform").innerHTML = "Guardando";
        document.getElementById("sendform").disabled = true;
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
          $("#nmb").focus();
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
        $this->model = $model;
        $this->tipoc = array("C"=>"Cliente","P"=>"Prospécto");
        $this->estatust = array("N"=>"Abierto","P"=>"Construcción","G"=>"Negociación","L"=>"Aplazado","A"=>"Vendido","F"=>"Finalizado","C"=>"Cancelado","O"=>"En Linea","X"=>"Perdido","W"=>"Ganados");
        $this->colestatust = array("N"=>"bg-warning bg-accent-1","P"=>"bg-orange bg-accent-2","G"=>"bg-indigo bg-accent-3","L"=>"bg-amber bg-darken-4","A"=>"bg-success bg-accent-3","C"=>"bg-red bg-accent-4","F"=>"bg-primary bg-darken-2","X"=>"bg-red bg-darken-2");
    }
    function browse($nmb,$estatus,$fini,$ffin,$page,$agente){
      $sel = [];

      ?>
      <style>
        [type=radio] {
          position: absolute;
          opacity: 0;
          width: 0;
          height: 0;
        }
      </style>
      <?php
      
        /* $numres = 50;
        $nr = $this->model->resultt->num_rows;
        $np = $nr/$numres; */

        if(isset($_POST['flag'])){
            $flag = '<input type="text" id="aliasp" value="'.$_POST['aliasp'].'" hidden>';
          if(isset($_POST['idlead'])){
            $flag .= '<input type="text" id="idlead" value="'.$_POST['idlead'].'" hidden>';
          }
        } else{
          $flag = '';
        }
        if($estatus == "A") $chest = "checked"; else $chest = "";

        foreach($this->estatust as $estal => $estanmb){
          if($estatus == $estal)
            $sel[$estal]= "selected";
          else $sel[$estal]  = "";
        }
        if($estatus == "T") $selt = 'selected';

        $nuevo = '
        <button id="nuevo" type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newtablero" >
          <i class="fa fa-plus"></i> Nuevo
        </button>';


        $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
       
        if($grupo != "ADMIN" && $grupo != "GERENCIA") {
          $visibilidad = 'style="display: none;"';
          $inp = 'style="display: block;"';
          $agente = $_SESSION['uid'];
        }else {
          $visibilidad = 'style="display: block;"';
          $inp = 'style="display: none;"';
        }

        $filtro = '
        <form autocomplete="off" action="?modulo=tableros&accion=index" method="post" id="filtro" onsubmit="return checkSubmitenviar();" class="">
            <div class="position-relative has-icon-left" hidden>
              <input type="text" id="page" name="page" class="form-control" value="'.$page.'" required>
            </div>
          <div class="mb-5">
            <label class="mr-sm-2">Nombre:</label>
            <div class="position-relative has-icon-left">
              <input type="text" id="nmb" name="nmb" class="form-control" value="'.$nmb.'" placeholder="Nombre del cliente o del tablero">
            </div>
          </div>
          <div class="mb-5">
            <label class="mr-sm-2">Responsable:</label>
            <select name="agente" id="agentefiltro" class="form-control" '.$visibilidad.'>
              <option value="">TODOS</option>';
              //menu_select_db('usuarios','u_id','u_id',$agente,'agente','u_empresa = "'.$_SESSION['emp'].'" AND u_estatus = "A"',false,false,true,false,"Todos")
              $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A"';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                if($row['u_id'] != "ADMIN"){
                  if($row['u_id'] == $agente) $agenteselect = 'selected';
                  else $agenteselect = '';
                  $filtro.= '<option value="'.$row['u_id'].'" '.$agenteselect.'>'.$row['u_nmb'].' '.$row['u_apellidos'].'</option>';
                }
              }

              $filtro.= '
            </select>
            <input name="agentefiltro" id="agentefiltro" class="form-control" type="text" value="'.$_SESSION['uid'].'" '.$inp.' disabled> 
          </div>
          <div class="mb-5">
            <label class="mr-sm-2">Desde:</label>
            <div class="position-relative has-icon-left">
              <input type="date" id="fini" name="fini" class="form-control" value="'.$fini.'" required>
              <input type="hidden" id="finilim" name="finilim" class="form-control" value="'.$fini.'">
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-5">
            <label class="mr-sm-2">Hasta:</label>
            <div class="position-relative has-icon-left">
              <input type="date" id="ffin" name="ffin" class="form-control" value="'.$ffin.'" >
              <input type="hidden" id="ffinlim" name="ffinlim" class="form-control" value="'.$ffin.'" >
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <!-- <div class="mb-5">
            <label class="mr-sm-2">Estatus:</label>
            <select name="estatus" id="estatusfiltro" class="form-control" >
              <option value="N" '.$sel['N'].' >Abiertos</option>
              <option value="O" '.$sel['O'].' >En linea</option>
              <option value="W" '.$sel['W'].' >Ganados</option>
              <option value="P" '.$sel['P'].' >En construcción</option>
              <option value="G" '.$sel['G'].' >Negociación</option>
              <option value="L" '.$sel['L'].' >Aplazado</option>
              <option value="A" '.$sel['A'].' >Vendido</option>
              <option value="F" '.$sel['F'].' >Finalizado</option>
              <option value="C" '.$sel['C'].' >Cancelado</option>
              <option value="X" '.$sel['X'].' >Perdido</option>
              <option value="T" '.$selt.'>Todos</option>
            </select>
          </div> -->
          <div class="mb-5">
            <label>Acciones:</label><br>
            <button type="button" onclick="mandar(0)" class="btn btn-success"><i class="fas fa-filter" style="color: #ffffff;"></i> Filtrar </button>
            <button type="button" onclick="mandar(1)" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span><i class="fas fa-times-circle" style="color: #ffffff;"></i> Limpiar
            </button>
          </div>
        </form>
        <script>
          /* function mandar(id){
            var agente = document.getElementById("agentefiltro");
            var fini = document.getElementById("fini"); 
            var ffin = document.getElementById("ffin");
            var fini2 = document.getElementById("finilim");
            var ffin2 = document.getElementById("ffinlim");
            var nmb = document.getElementById("nmb");
            //var estatus = document.getElementById("estatusfiltro");

            if(id == "1"){
              agente.value = "";
              fini.value= fini2.value;
              ffin.value= ffin2.value;
              //estatus.value= "O";
              nmb.value = "";
              
            }  

            //var table = $("#myTable").DataTable();
            $("#myTable").DataTable().clear().draw();
            $("#myTable").DataTable().destroy();

            var windowHeight = $(window).height();

            $("#myTable").DataTable( {
              "order": [[3, "desc"]],
              paging: true,
              scrollY: 400,
              scrollY: windowHeight * 0.4175,
              processing: true,
              serverside: true,
              language: {
                  url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
              },
              ajax: {
                url: "query/datatabletableros.php",
                type: "POST",
                datatype: "json",
                data:{ "agente": agente.value,
                  "fini": fini.value,
                  "ffin": ffin.value,
                  
                  "nmb": nmb.value,
                },
              },
              pageLength: "50",
              responsivePriority: 1
            });
          } */

          
          
        </script>
        ';
        toolbar($_GET['modulo'],'',$filtro,$nuevo);
          ?>
          <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Aquí puedes poner el código de la función que deseas ejecutar
      sendFlag();
    });
    
    function sendFlag() {
        var aliasp = document.getElementById("aliasp");
        var flag = document.getElementById("flag");
        var idcliente = document.getElementById("nmbcl");
        if(idcliente.value != ""){
            if(aliasp){
            document.getElementById("nuevo").click();
            document.getElementById("cliente").value = idcliente.value;
          } 
        }
      }

            function setname(){
              var cliente = document.getElementById("cliente").value;
              var proyname = "Tablero de " + cliente;
              document.getElementById("nmbt").value = proyname;
            }
            function nivelta(nivt){
              document.getElementById("niveltab").value = nivt;
              if(nivt == 1){
                document.getElementById("op1").classList.add("btn-danger");
                document.getElementById("op2").classList.remove("btn-warning");
                document.getElementById("op3").classList.remove("btn-info");
              }else if(nivt == 2){
                document.getElementById("op1").classList.remove("btn-danger");
                document.getElementById("op2").classList.add("btn-warning");
                document.getElementById("op3").classList.remove("btn-info");
              }else if(nivt == 3){
                document.getElementById("op1").classList.remove("btn-danger");
                document.getElementById("op2").classList.remove("btn-warning");
                document.getElementById("op3").classList.add("btn-info");
              }
            }
            function niveltab(nivt,tablero){
              document.getElementById("negtab"+tablero).value = nivt;
              if(nivt == 1){
                document.getElementById("opt1"+tablero).classList.add("btn-danger");
                document.getElementById("opt2"+tablero).classList.remove("btn-warning");
                document.getElementById("opt3"+tablero).classList.remove("btn-info");
                document.getElementById("opt4"+tablero).classList.remove("btn-danger");
                //document.getElementById("setniveltab").submit();
              }else if(nivt == 2){
                document.getElementById("opt1"+tablero).classList.remove("btn-danger");
                document.getElementById("opt2"+tablero).classList.add("btn-warning");
                document.getElementById("opt3"+tablero).classList.remove("btn-info");
                document.getElementById("opt4"+tablero).classList.remove("btn-danger");
                //document.getElementById("setniveltab").submit();
              }else if(nivt == 3){
                document.getElementById("opt1"+tablero).classList.remove("btn-danger");
                document.getElementById("opt2"+tablero).classList.remove("btn-warning");
                document.getElementById("opt3"+tablero).classList.add("btn-info");
                document.getElementById("opt4"+tablero).classList.remove("btn-danger");
                //document.getElementById("setniveltab").submit();
              }else if(nivt == 4){
                document.getElementById("opt1"+tablero).classList.remove("btn-danger");
                document.getElementById("opt2"+tablero).classList.remove("btn-warning");
                document.getElementById("opt3"+tablero).classList.remove("btn-info");
                document.getElementById("opt4"+tablero).classList.add("btn-danger");
              }
            }
          </script>
          <script>
            $(document).ready(function() {
              $('#cliente').on('keyup', function() {
                var key = $(this).val();
                var dataString = 'cliente='+key;
                $.ajax({
                  type: "POST", 
                  url: "query/suggestclientes.php",
                  data: dataString,
                  success: function(data) {
                    //Escribimos las sugerencias que nos manda la consulta
                    $('#suggestions-block').fadeIn(1000).html(data);
                    //Al hacer click en alguna de las sugerencias
                    $('.suggest-element').on('click', function(){
                      //Obtenemos la id unica de la sugerencia pulsada
                      var id = $(this).attr('id');
                      $.ajax({
                        type: "POST",
                        url: "query/setnmbtablero.php",
                        data: {'idcl': id},
                        success: function(dataRTN) {
                          var datos = JSON.parse(dataRTN);
                          //console.log(datos);
                          var nmbtb = document.getElementById('nmbt');
                          nmbtb.value = datos['nmbtablero'];
                          var nmbcl = document.getElementById('nmbcl');
                          nmbcl.value = datos['nmb'];
                          var telefono = document.getElementById('telefonocl');
                          telefono.value = datos['telefono'];
                          var cliente = document.getElementById('idcliente');
                          cliente.value = datos['id'];
                        }
                      });
                      //Editamos el valor del input con data de la sugerencia pulsada
                      $('#cliente').val($('#'+id).attr('data'));
                      //Hacemos desaparecer el resto de sugerencias
                      $('#suggestions-block').fadeOut(200);
                      document.getElementById('nmbt').focus();
                      return false;
                    });
                  }
                });
              });
            });

            function cerrarmodal (){
              $('#cambioscotiza').modal('hide');
            }
          </script>
          <?php
          //Modal - Nuevo deal - TABLERO
          echo '
          <div class="modal fade text-xs-left" id="newtablero" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title" id="myModalLabel33">Nuevo tablero</h4>
                </div>
                <form method="post" action="?modulo=tableros&accion=insert" autocomplete="off" >
                <div class="modal-body row">';
                echo $flag;

                  if(isset($_POST['idlead'])){
                    $sql = 'SELECT cl_nmb, cl_telefono FROM crm_leads WHERE cl_id = "'.$_POST['idlead'].'"';
                    $result = setq($sql);
                    list($nmbcl, $telefonocl) = $result -> fetch_array();
                    $cliente = busca($telefonocl, 'crm_clientes', 'c_telefono2 = "'.$telefonocl.'" OR c_telefono2', 'c_id');
                    if(!$cliente) {
                      $cliente = 0;
                    }
                    $nmbtablero = $telefonocl.' - '.date('Y-m-d');                    
                  } else {
                    $cliente = 0;
                    $nmbcl = NULL;
                    $telefonocl = NULL;
                    $nmbtablero = NULL;
                  }
                  echo '<input type="hidden" value="'.$_POST['idlead'].'" name="idlead" id="idlead">';
                  echo '<input type="hidden" value="'.$cliente.'" name="idcliente" id="idcliente">';
                  echo '
                  <div class="col-md-9">
                    <div class="mb-5">  
                      <label>Cliente: </label>
                      <input type="text" name="cliente" id="cliente" value="'.$nmbcl.'" placeholder="Nombre o alias del cliente" class="search_query form-control" >';
                    echo '</div>
                    <div id="suggestions-block"></div>
                  </div>
                  <div class="col-md-3" style="align-items: end; display: flex;">
                    <div class="mb-5">  
                      <a href="?modulo=clientes&accion=edit">
                        <button type="button" class="btn btn-primary mt-2" data-toggle="tooltip" data-placement="top" title="Agregar nuevo cliente"><i class="fa fa-plus"></i> Nuevo cliente</button>
                      </a>
                    </div>
                    <div id="suggestions-block"></div>
                  </div>
                  <div class="col-7 col-md-7 col-xs-12">
                    <label>Nombre del cliente: </label>
                    <input type="text" name="nmbcl" id="nmbcl" placeholder="Nombre del cliente" value="'.$nmbcl.'" class="search_query form-control" onfocus="this.select();">
                  </div>
                  <div class="col-5 col-md-5 col-xs-12">
                    <label>Telefono del cliente: </label>
                    <input type="text" name="telefonocl" id="telefonocl" placeholder="Telefono del cliente" value="'.$telefonocl.'" class="search_query form-control" onfocus="this.select();">
                  </div>';


                    echo '<div class="col-md-5 col-xs-12 mt-2">';
                      echo '<label>Nombre proyecto: </label>
                      <div class="mb-5">
                        <input type="text" name="nmb" value="'.$nmbtablero.'" id="nmbt" placeholder="Nombre del tablero/proyecto" class="form-control" onfocus="this.select();" required>
                      </div>
                    </div>
                    <div class="col-md-4 col-xs-12 mt-2">
                      <label>Fecha inicio: </label>
                      <div class="mb-5">
                        <div class="position-relative has-icon-left">
                          <input type="datetime-local" id="fini" name="fini" class="form-control" value="'.date('Y-m-d').'T'.date('H:i:s').'" step="any" required>
                          <div class="form-control-position">
                            <i class="icon-calendar5"></i>
                          </div>
                        </div>
                      </div>
                    </div>';
                    $mesactual = date('m');
                    $vdias = date('m',strtotime('+20 days'));
                    if($mesactual != $vdias) $fecha = date('Y-m-d',strtotime('last day of this month'));
                    else $fecha = date('Y-m-d',strtotime('+20 days'));

                    echo '<div class="col-md-3 col-xs-12 mt-2">
                      <label>Fecha Cierre: </label>
                      <div class="mb-5">
                        <div class="position-relative has-icon-left">
                          <input type="date" id="fcierre" name="fcierre" class="form-control" value="'.$fecha.'" required readonly>
                          <div class="form-control-position">
                            <i class="icon-calendar5"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 col-xs-12">';
                      $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
                      if($grupo != "ADMIN") {
                        $sel = 'style="display: none;"';
                        $inp = 'style="display: block;"';
                      }else {
                        $sel = 'style="display: block;"';
                        $inp = 'style="display: none;"';
                      }
                      echo '<label>Propietario: </label>
                      <div class="mb-5">
                        <select class="form-control" searchable="Responsable del tablero" name="responsable" '.$sel.'>';
                          $sqlu = 'SELECT u_nuser,u_id,u_nmb, u_apellidos FROM usuarios WHERE u_estatus = "A" AND u_grupo = "VENTAS" OR u_grupo = "GERENCIA"';
                          $resultu = setq($sqlu);
                          while($rowu = $resultu->fetch_array()){
                            if($rowu['u_id'] != "ADMIN"){
                              if($_SESSION['uid'] == $rowu['u_id']) $seldis = "selected"; else $seldis = "";
                              echo '<option value="'.$rowu['u_id'].'" '.$seldis.'>'.$rowu['u_nmb'].' '.$rowu['u_apellidos'].'</option>';
                            }
                          }
                        echo '</select>
                        <input name="responsable" class="form-control" type="text" value="'.busca($_SESSION['uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'" '.$inp.' disabled> 
                      </div>
                    </div>
                    <div class="col-md-4 col-xs-12" hidden>
                      <label>Visibilidad: </label>
                      <div class="mb-5">
                        <select class="form-control" searchable="Visibilidad" name="visibility" >
                          <option value="1" selected > PÚBLICO <em>Todos los perfiles con acceso a tableros pueden ver tu tablero</em> </option>
                          <option value="2"  > PRIVADO <em>Solo administradores y el responsable podrán ver este tablero</em> </option>
                        </select>
                      </div>
                    </div>
                    <input type="hidden" value="3" name="niveltab" id="niveltab" />
                    <div class="col-md-8 col-xs-12 row mb-5">
                      <label>Nivel de negociación: </label> <br>
                      <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-secondary btn-min-width mr-1 mb-1" id="op1"  onclick="nivelta(1)">
                          <input type="radio" name="options" id="option1" hidden> Caliente
                        </label>
                        <label class="btn btn-secondary btn-min-width mr-1 mb-1" id="op2" onclick="nivelta(2)">
                          <input type="radio" name="options" id="option2" hidden> Tibio
                        </label>
                        <label class="btn btn-secondary btn-min-width mr-1 mb-1 active btn-info" id="op3" onclick="nivelta(3)">
                          <input type="radio" name="options" id="option3" checked hidden> Frio
                        </label>
                      </div>
                    </div>
                    <div class="col-6 col-md-5" hidden>
                      <div class="mb-5" title="Estimación de la compra del cliente">
                        <h4>Valor($): </h4>
                        <input id="valor" name="valor" type="number" min="0" value="0" class="form-control">
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer p-2">
                    <center><button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Registrar tablero</button></center>
                  </div>
                </form>
              </div>
            </div>
          </div>';
          //Modal - Nueva actividad
          echo '<div class="modal fade text-xs-left" id="newactivity" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                  <label class="modal-title text-text-bold-600" id="myModalLabel33">Agregar actividad</label>
                </div>
                <form method="post" action="?modulo=tableros&accion=insert" >
                  <div class="modal-body row">
                    <div class="col-md-12 col-xs-12">
                      <label>Tablero asignado: </label>
                      <div class="mb-5">
                        <input type="text" name="cliente" id="clientet" placeholder="Nombre o alias del cliente" class="form-control" required readonly >
                      </div>
                    </div>
                    <div class="col-md-5 col-xs-12">
                      <label>Nombre proyecto: </label>
                      <div class="mb-5">
                        <input type="text" name="nmb" id="nmbt" placeholder="Nombre del tablero/proyecto" class="form-control" required>
                      </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                      <label>Fecha inicio: </label>
                      <div class="mb-5">
                        <div class="position-relative has-icon-left">
                          <input type="datetime-local" id="fini" name="fini" class="form-control" value="'.date('Y-m-d').'T'.date('H:i:s').'" step="any" required>
                          <div class="form-control-position">
                            <i class="icon-calendar5"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                      <label>Fecha Cierre: </label>
                      <div class="mb-5">
                        <div class="position-relative has-icon-left">
                          <input type="date" id="fcierre" name="fcierre" class="form-control" value="'.date('Y-m-d',strtotime('+15 days')).'" required>
                          <div class="form-control-position">
                            <i class="icon-calendar5"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-2 col-xs-12">';
                    $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
                    if($grupo != "ADMIN") {
                      $sel = 'style="display: none;"';
                      $inp = 'style="display: block;"';
                    }else {
                      $sel = 'style="display: block;"';
                      $inp = 'style="display: none;"';
                    }
                      echo '<label>Propietario: </label>
                      <div class="mb-5">
                        <select class="form-control" searchable="Responsable del tablero" name="responsable" '.$sel.'>';
                          $sqlu = 'SELECT u_nuser,u_id,u_nmb,u_apellidos FROM usuarios WHERE u_estatus = "A" AND u_grupo = "VENTAS" OR u_grupo = "GERENCIA"';
                          $resultu = setq($sqlu);
                          while($rowu = $resultu->fetch_array()){
                            if($rowu['u_id'] != "ADMIN"){
                              if($_SESSION['uid'] == $rowu['u_id']) $seldis = "selected"; else $seldis = "";
                              echo '<option value="'.$rowu['u_id'].'" '.$seldis.'>'.$rowu['u_nmb'].' '.$rowu['u_apellidos'].'</option>';
                            }
                          }
                        echo '</select>
                      <input name="responsable" class="form-control" type="text" value="'.busca($_SESSION['uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ",u_apellidos)').'" '.$inp.' disabled> 
                      </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                      <label>Visibilidad: </label>
                      <div class="mb-5">
                        <select class="form-control" searchable="Visibilidad" name="visibility" >
                          <option value="1"  > PÚBLICO <em>Todos los perfiles con acceso a tableros pueden ver tu tablero</em> </option>
                          <option value="2" selected > PRIVADO <em>Solo administradores y el responsable podrán ver este tablero</em> </option>
                        </select>
                      </div>
                    </div>
                    <input type="hidden" value="2" name="niveltab" id="niveltab" />
                    <div class="col-md-6 col-xs-12 row mb-5">
                      <label>Nivel de negociación: </label>
                      <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-danger btn-min-width mr-1 mb-1"  onclick="nivelta(1)">
                          <input type="radio" name="options" id="option1"> Caliente
                        </label>
                        <label class="btn btn-warning btn-min-width mr-1 mb-1 active"  onclick="nivelta(2)">
                          <input type="radio" name="options" id="option2" checked> Tibio
                        </label>
                        <label class="btn btn-info btn-min-width mr-1 mb-1" onclick="nivelta(3)">
                          <input type="radio" name="options" id="option3"> Frio
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12 col-12">
                    <center><button type="submit" class="btn btn-primary">Registrar tablero</button></center>
                  </div>
                  <div class="modal-footer">
                    <input type="reset" class="btn btn-outline-secondary btn-lg" data-dismiss="modal" value="Cerrar">
                  </div>
                </form>
              </div>
            </div>
          </div>';
          //filtro

      // Modal - Cambios en paquetes
      echo '
      <div class="col-10 modal fade text-xs-left" id="cambioscotiza" tabindex="-1" role="dialog" aria-labelledby="c" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="modalCotiza">Historial de cambios en paquetes</h4>
            </div>
            <div class="modal-body row">
              <div class="col-md-12">
                <table class="table ">
                  <thead class="bg-primary text-white">
                    <th>Tablero</th>
                    <th>Cotización</th>
                    <th>Artículo original</th>
                    <th>Cambió a</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                  </thead>
                  <tbody>
                  ';
                  $sql = 'SELECT ct_nmb, cc_folio, cca_fgen, cca_ugen, cca_articulo, cca_cambio FROM crm_cotizaciones_cambios INNER JOIN crm_cotizaciones ON cc_id = cca_cotizacion INNER JOIN crm_tableros  ON cc_tablero = ct_id ORDER BY cca_fgen DESC';
                  $result = setq($sql);
                  while($row = $result -> fetch_array()){
                    echo '<tr>
                    <td>'.$row['ct_nmb'].' </td>
                    <td>'.$row['cc_folio'].' </td>
                    <td>'.busca($row['cca_articulo'], 'articulos', 'a_id', 'a_nmb').' </td>
                    <td>'.busca($row['cca_cambio'], 'articulos', 'a_id', 'a_nmb').' </td>
                    <td>'.$row['cca_ugen'].' </td>
                    <td>'.$row['cca_fgen'].' </td>
                    </tr>';
                  }
                  echo '
                  </tbody>
                </table>
              </div>  
            </div>
            <div class="modal-footer p-2">
              <center><button class="btn btn-danger" onclick="cerrarmodal();"><i class="fas fa-times-circle"></i> Cerrar</button></center>
            </div>
          </div>
        </div>
      </div>';
        echo '
      <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table width="90%" class="mb-0 table" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">
                    <tr>
                      <th width="20%"><b>Título</b></th>
                      <th width="15%"><b>Cliente</b></th>
                      <th width="15%"><b>Vendedor</b></th>
                      <th width="10%"><b>Apertura</b></th>
                      <th width="10%"><b>Cierre</b></th>
                      <th width="20%"><b>Estatus</b></th>
                      <th width="10%"><b>Acción</b></th>
                    </tr>
                  </thead>';
                  $i = 0;
                  $arropen = array("N","P","G","V","C","X");
                  while($row = $this->model->result->fetch_array()){
                    $chneg1 = '';
                    $activeneg1 = '';
                    $chneg2 = '';
                    $activeneg2 = '';
                    $chneg3 = '';
                    $activeneg3 = '';
                    $chneg4 = '';
                    $activeneg4 = '';
                    $bgbutton = '';
                    if($row['ct_nnegociacion'] == 1) {$chneg1 = 'checked'; $activeneg1 = 'btn-danger active'; $bgbutton = $activeneg1; }
                    elseif($row['ct_nnegociacion'] == 2) {$chneg2 = 'checked'; $activeneg2 = 'btn-warning active'; $bgbutton = $activeneg2; }
                    elseif($row['ct_nnegociacion'] == 4) {$chneg4 = 'checked'; $activeneg4 = 'btn-danger active'; $bgbutton = $activeneg4; }
                    else {$chneg3 = 'checked'; $activeneg3 = 'btn-info active'; $bgbutton = $activeneg3; }
                    $firstDate  = new DateTime(date('Y-m-d'));
                    $secondDate = new DateTime($row['ct_fcierre']);
                    $intvl = $firstDate->diff($secondDate);

                    if(in_Array($row['ct_estatus'],$arropen)){
                      if($row['ct_fcierre'] < date('Y-m-d')) $alrt = ' class="alert alert-danger no-border" role="alert" ';
                      elseif($intvl->days > 7) $alrt = ' class="alert alert-info no-border" role="alert" ';
                      elseif($intvl->days > 5) $alrt = ' class="alert alert-success no-border" role="alert" ';
                      elseif($intvl->days >= 0) $alrt = ' class="alert alert-warning no-border" role="alert" ';
                    }else $alrt = ' class="alert alert-success no-border" role="alert" ';

                    $remisiones = busca($row['ct_id'], "remisiones", "r_estatus = 'N' AND r_tablero", "COUNT(*)");
                    $cotizaciones = busca($row['ct_id'], "crm_cotizaciones", "cc_estatus = 'P' AND cc_tablero", "COUNT(*)");
                    $numbanner = intval($remisiones) + intval($cotizaciones);
                    $numbanner += intval(busca("D", 'crm_cotizaciones', 'cc_motivo IS NOT NULL AND cc_tablero = "'.$row['ct_id'].'" AND cc_estatus', 'COUNT(*)'));

                    $texto = '';
                    if($cotizaciones > 0){
                      if($remisiones > 0){
                        $texto .= $cotizaciones.' cotizaciones pendientes de envío y '.$remisiones.' remisiones nuevas';
                      } else{
                        $texto .= $cotizaciones.' cotizaciones pendientes de envío';
                      }
                    } else{
                      if($remisiones > 0){
                        $texto .= $remisiones.' remisiones nuevas';
                      }
                    }

                    if($numbanner != 0){
                      $banner = '&nbsp;<span data-bs-toggle="tooltip" data-bs-placement="top" title="'.$texto.'" class="badge badge-danger">'.$numbanner.'</span>';
                    } else{
                      $banner = "";
                    }
                    
                    $nmbcl = busca($row['ct_cliente'],'crm_clientes','c_id','c_nmb');
                    $apellidos = busca($row['ct_cliente'],'crm_clientes','c_id','c_apellidos');
                    $cliente = $nmbcl.' '.$apellidos;  
                    
                    
                    
                    echo '<tr>
                      <th>'.$row['ct_nmb'].$banner.'</th>
                      <td>'.$cliente.'</td>
                      <td>'.busca($row['ct_agente'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
                      <td >'.$row['ct_fini'].'</td>
                      <td '.$alrt.' data-toggle="tooltip" data-placement="top" title="'.$intvl->days.' dias restantes">'.$row['ct_fcierre'].'</td>';
                        /* echo '<a data-fancybox data-type="ajax" data-src="popup/newactividad.php?tablero='.$row['ct_id'].'&act=2" href="javascript:;">
                          <button type="button" class="btn btn-sm btn-secondary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Nueva actividad"><i class="fa fa-plus"></i> Actividad</button>
                        </a>';
                        echo '<a data-fancybox data-type="ajax" data-src="popup/setcotiza.php?tablero='.$row['ct_id'].'" href="javascript:;">
                          <button type="button" class="btn btn-sm btn-secondary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Crear una nueva cotización"><i class="fa fa-comments-dollar"></i> Cotización</button>
                        </a>';
                        $numac = busca($row['ct_id'],'crm_actividades','ca_tablero','COUNT(*)'); */
                        $countcotN = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus IN ("N", "D", "R")  AND cc_tablero' ,'COUNT(*)');
                        $countcotL = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus = "L"  AND cc_tablero' ,'COUNT(*)');
                        $countcotA = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus = "A"  AND cc_tablero' ,'COUNT(*)');
                        $countcotP = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus = "P"  AND cc_tablero' ,'COUNT(*)');
                        $countremN = busca($row['ct_id'], 'remisiones', 'r_estatus = "N" AND r_tablero', 'COUNT(*)');
                        $countremdp = busca($row['ct_id'], 'remisiones', 'r_estatus IN ("D", "P") AND r_tablero', 'COUNT(*)');   
                        $countremV = busca($row['ct_id'], 'remisiones INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_estatus IN ("N", "A") AND r_estatus = "A" AND r_tablero' ,'COUNT(*)');
                        $countremF = busca($row['ct_id'], 'remisiones INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_estatus = "F" AND r_estatus IN ("F") AND r_tablero' ,'COUNT(*)');
                        $countenvP = busca($row['ct_id'], 'remisiones INNER JOIN guias_articulos ON ga_cotizacion = r_id', 'ga_estatus IN ("P", "A") AND r_tablero' ,'COUNT(*)');
                        $countenvF = busca($row['ct_id'], 'remisiones INNER JOIN guias_articulos ON ga_cotizacion = r_id', 'ga_estatus = "F" AND r_tablero' ,'COUNT(*)');
                        $countartF = busca($row['ct_id'], 'remisiones INNER JOIN remisionesc ON rc_remision = r_id', 'rc_estatus = "F" AND r_tablero' ,'COUNT(*)');
                        $countcotC = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus = "C" AND cc_tablero' ,'COUNT(*)');
                        $countremC = busca($row['ct_id'], 'remisiones', 'r_estatus = "C" AND r_tablero' ,'COUNT(*)');
                        if($countcotN > 0){
                          echo '<td class="alert" style="background:#F4D03F">Cotización en construcción</td>';
                        } else if($countcotL >0){
                          echo '<td class="alert" style="background:#F5B041">Esperando costo de logística</td>';
                        }else if($countcotP>0){
                          echo '<td class="alert" style="background:#E67E22">Cotización pendiente de envío</td>';
                        }else if($countcotA>0){
                          echo '<td class="alert" style="background:#58D68D">Cotización enviada</td>';
                        }else if($countremN>0){
                          echo '<td class="alert text-white" style="background:#C0392B">Esperando aplicación de remisión</div>';
                        }else if($countremdp>0){
                          echo '<td class="alert text-white" style="background:#8B57E5">Esperando primer abono</div>';
                        }else if($countremV>0){
                          echo '<td class="alert" style="background:#45B39D">Vendido-abonado</td>';
                        }else if($countremF>0){
                          echo '<td class="alert" style="background:#AAB7B8">Artículos pendientes de envío</td>';
                        }else if($countenvP>0){
                          echo '<td class="alert text-white" style="background:#AAB7B8">Preparado para envío</td>';
                        }else if($countenvF>0 || $countartF>0 ){
                          echo '<td class="alert text-white" style="background:#8E44AD ">Articulos enviados</td>';
                        } else if($countcotC>0){
                          echo '<td class="alert text-white" style="background:#000000 ">Cotizaciones canceladas</td>';
                        } else if($countremC>0){
                          echo '<td class="alert text-white" style="background:#000000 ">Remisiones canceladas</td>';
                        } else if($row['ct_estatus'] == "X"){
                          echo '<td class="alert" style="background:#C0392B">Tablero perdido</td>';
                        }
                      /* echo '<td class="alert '.$this->colestatust[$row['ct_estatus']].' text-middle">'.$this->estatust[$row['ct_estatus']].'</td>'; */
                      echo '<td>
                        <a href="?modulo=tableros&accion=show&id='.$row['ct_id'].'">
                          <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-eye"></i></button>
                        </a>';
                        $arrayneg = array("N","P","G","V","O","L");
                        if(in_array($row['ct_estatus'],$arrayneg)){
                          echo '<button type="button" class="btn '.$bgbutton.' btn-sm text-white" data-bs-toggle="modal" data-bs-target="#tabnegociacion'.$row['ct_id'].'"   data-placement="top" >
                            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-thermometer"></i>
                          </button>';
                          //Modal - Nuevo deal
                          echo '<div class="modal fade text-xs-left" id="tabnegociacion'.$row['ct_id'].'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
                            <div class="modal-dialog modal-md" role="document">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <label class="modal-title text-text-bold-600" id="myModalLabel33">Actualizar tablero '.$row['ct_nmb'].'</label>
                                </div>
                                <form method="post" name="setniveltab" id="setniveltab" action="?modulo=tableros&accion=updateneg&id='.$row['ct_id'].'" autocomplete="off" >
                                  <div class="modal-body row">
                                    <input type="hidden" value="'.$row['ct_nnegociacion'].'" name="negtab'.$row['ct_id'].'" id="negtab'.$row['ct_id'].'" />
                                    <div class="col-md-12 col-xs-12">
                                      <center>
                                        <h5>Nivel de negociación: </h5> <br>
                                        <div class="btn-group" data-toggle="buttons">
                                          <label class="btn btn-secondary btn-min-width mr-1 mb-1 '.$activeneg1.'" id="opt1'.$row['ct_id'].'"  onclick="niveltab(1,'.$row['ct_id'].')">
                                            <input type="radio" name="option'.$row['ct_id'].'" id="options1'.$row['ct_id'].'" '.$chneg1.' > Caliente
                                          </label>
                                          <label class="btn btn-secondary btn-min-width mr-1 mb-1 '.$activeneg2.'" id="opt2'.$row['ct_id'].'" onclick="niveltab(2,'.$row['ct_id'].')">
                                            <input type="radio" name="option'.$row['ct_id'].'" id="options2'.$row['ct_id'].'" '.$chneg2.'> Tibio
                                          </label>
                                          <label class="btn btn-secondary btn-min-width mr-1 mb-1 '.$activeneg3.'" id="opt3'.$row['ct_id'].'" onclick="niveltab(3,'.$row['ct_id'].')">
                                            <input type="radio" name="option'.$row['ct_id'].'" id="options3'.$row['ct_id'].'" '.$chneg3.' > Frio
                                          </label>
                                          <label class="btn btn-secondary btn-min-width mr-1 mb-1 '.$activeneg4.'" id="opt4'.$row['ct_id'].'" onclick="niveltab(4,'.$row['ct_id'].')">
                                            <input type="radio" name="option'.$row['ct_id'].'" id="options4'.$row['ct_id'].'" '.$chneg4.'> Perdido
                                          </label>
                                        </div>
                                      </center>
                                    </div>
                                  </div>
                                  <div class="modal-footer p-2">
                                    <center><button type="submit" class="btn btn-primary"><i class="fa fa-redo"></i> Actualizar</button></center>
                                  </div>
                                </form>
                              </div>
                            </div>
                          </div>';
                        }
                      echo'</td>
                    </tr>';
                  }
                echo '</table>
              </center>
            </div>
          </div>
        </div>
      </div>';

      if($_REQUEST['page'] == 0){
        $hidden = 'style="pointer-events: none;
        background: #70707026;
        color: black;"';
      }else $hidden = "";
  
      echo '<div class="col-md-12 mt-5 text-xs-center">
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
                  document.getElementById("page").value = id;
                  document.getElementById("filtro").submit();
                }
              </script>';
  
              //$_REQUEST['categoria'],$_REQUEST['marca'],$_REQUEST['page'],$_REQUEST['unidad'],$_REQUEST['linean'],$_REQUEST['esquema']
      
  
      $bloque = 20;

      $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
      $sql = 'SELECT COUNT(*) FROM crm_tableros WHERE ct_fini BETWEEN "'.$fini.'" AND "'.$ffin.'"';
      
      if($grupo == "ADMIN" || $grupo == "GERENCIA" || $grupo == "SUBGERENCIA"){
        if($agente)$sql .= ' AND ct_agente = "'.$agente.'"';
        else  $sql .= "";
      } else {
        $sql .= ' AND ct_agente = "'.$_SESSION['uid'].'"';
      }
      
      if($nmb) $sql.= ' AND (ct_nmb LIKE "%'.$nmb.'%" OR ct_cliente IN
                        (SELECT c_id FROM crm_clientes WHERE c_nmb LIKE "%'.$nmb.'%" OR c_alias LIKE "%'.$nmb.'%" OR c_apellidos LIKE "%'.$nmb.'%"))';
      
      $resultpg = setq($sql);
      list($numg) =  $resultpg->fetch_array();
      $pageres = ceil($numg/$bloque);
  
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
    </div>'; 

      

      /* echo '
      <script>
      var windowHeight = $(window).height();                  
        $("#myTable").DataTable( {
            paging: true,
            scrollY: windowHeight * 0.5,
            "order": [[3, "desc"]],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength: 50,  
        });
      </script>
      
      
      '; */
      /* if($_REQUEST['page'] == 0){
        $hidden = 'style="pointer-events: none;
        background: #70707026;
        color: black;"';
      }else $hidden = "";

      echo '<div class="col-md-12 text-xs-center">
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
                  document.getElementById("page").value = id;
                  document.getElementById("filtro").submit();
                }
              </script>';

              $bloque = 50; 
              $sqlpg = 'SELECT COUNT(*) FROM crm_tableros WHERE  ct_empresa = "'.$_SESSION['emp'].'"
                        AND ct_fini BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
              if($agente) $sqlpg.=' AND ct_agente = "'.$agente.'"';
              if($estatus == "O") $sqlpg.=' AND ct_estatus IN ("N","P","G","V","A") ';
              elseif($estatus == "W") $sqlpg.=' AND ct_estatus IN ("F","V","A") ';
              elseif($estatus == "I") $sqlpg.=' AND ct_estatus IN ("N","P") ';
              elseif($estatus == "L") $sqlpg.=' AND ct_estatus IN ("X","C") ';
              elseif($estatus == "T") $sqlpg.=' ';
              else $sqlpg.=' AND ct_estatus = "'.$estatus.'" ';

              if($nmb) $sqlpg.= ' AND (ct_nmb LIKE "%'.$nmb.'%" OR ct_cliente IN
                (SELECT c_id FROM crm_clientes WHERE c_nmb LIKE "%'.$nmb.'%" OR c_alias LIKE "%'.$nmb.'%" OR c_apellidos LIKE "%'.$nmb.'%"))';
              $sqlpg.=' ORDER BY ct_id DESC ';
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
      </div>';  */
      //echo $_REQUEST['page'].' '.$pageres;


    }
    function show($estatus,$tipo){
      ?>
      <style>
        [type=radio] {
          position: absolute;
          opacity: 0;
          width: 0;
          height: 0;
        }
      </style>
      <script>
        /*function confirma(idac){
          var actividad = document.getElementById("nmbact" + idac).value;
          var conf = confirm("¿Deseas Marcar la actividad como realizada?");
          if(conf == true){
            $("#pop"+idac).click();
          }
        }
        function cancela(idac){
          var actividad = document.getElementById("nmbact" + idac).value;
          var conf = confirm("¿Deseas Marcar la actividad como Cancelada?");
          if(conf == true){
            $("#popc"+idac).click();
          }
        }*/
        function confirma(idac){
          var conf = confirm("¿Deseas Marcar la actividad como realizada?");
          if(conf == true){
            document.location.href="?modulo=tableros&accion=estatusactividad&estatus=F&id="+idac+"&origen=2";
          }
        }
        function cancela(idac){
          var conf = confirm("¿Deseas Marcar la actividad como cancelada?");
          if(conf == true){
            document.location.href="?modulo=tableros&accion=estatusactividad&estatus=C&id="+idac+"&origen=2";
          }
        }
        function cerrartablero(idac,estatus,recurrente){
          if(estatus == "F") {
            if(parseFloat(recurrente) === 0 || recurrente === null) var textc = "¿Deseas Cerrar el tablero seleccionado? ";
            else if(parseFloat(recurrente) > 0 || recurrente !== null) var textc = "El tablero contiene remisiones recurrentes, ¿Deseas Cerrar el tablero seleccionado?\nAl aceptar se cancelarán las remisiones recurrentes.";
          }
          else var textc = "¿Deseas marcar el tablero seleccionado como perdido? ";
          var conf = confirm(textc);
          if(conf == true){
            document.location.href="?modulo=tableros&accion=endtablero&estatus=" + estatus + "&id=" + idac;
          }
        }
        function nivelta(nivt){
          document.getElementById("niveltab").value = nivt;
          if(nivt == 1){
            document.getElementById("op1").classList.add("btn-danger");
            document.getElementById("op2").classList.remove("btn-warning");
            document.getElementById("op3").classList.remove("btn-info");
            document.getElementById("op4").classList.remove("btn-danger");
          }else if(nivt == 2){
            document.getElementById("op1").classList.remove("btn-danger");
            document.getElementById("op2").classList.add("btn-warning");
            document.getElementById("op3").classList.remove("btn-info");
            document.getElementById("op4").classList.remove("btn-danger");
          }else if(nivt == 3){
            document.getElementById("op1").classList.remove("btn-danger");
            document.getElementById("op2").classList.remove("btn-warning");
            document.getElementById("op3").classList.add("btn-info");
            document.getElementById("op4").classList.remove("btn-danger");
          }else if(nivt == 4){
            document.getElementById("op1").classList.remove("btn-danger");
            document.getElementById("op2").classList.remove("btn-warning");
            document.getElementById("op3").classList.remove("btn-info");
            document.getElementById("op4").classList.add("btn-danger");
          }
        }

        function imprimirticket(idcd){
          //console.log('entro a la funcion');
          $.ajax({
            type: "POST",
            url: "query/ticketventa.php",
            data: {"idcd": idcd},
            success: function(data){
              console.log(data);
              var datax = JSON.parse(data);
              var nmbart = datax['articulos'].split('/');
              var cantart = datax['cantidad'].split('/');
              var preart = datax['precio'].split('/');
              var ventimp = window.open('about:blank', 'ImprimirTicket');
              ventimp.document.open();
              ventimp.document.write('<html><head><title>' + document.title + '</title>');
              //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
              ventimp.document.write('</head><body>');
              ventimp.document.write('<div style="width:402px;height:200px">');
              ventimp.document.write('<center><img src="img/UNICO-LOGO-OFICIAL.png" height="200" width="200px" alt="img/UNICO-LOGO-OFICIAL.png"');
              ventimp.document.write('</div>');
              ventimp.document.write('<div width="100%" style="font-size:14px">'+datax['direccion']+'</div>');
              ventimp.document.write('<div width="100%" style="font-size:14px">'+datax['telefono']+'</div></center>');
              ventimp.document.write('<table width="402px"><tr>');
              ventimp.document.write('<td width="50%" colspan="3" align="center" style="font-size:20px">FECHA</td></tr>');
              ventimp.document.write('<tr><td width="50%" colspan="3" align="center" style="font-size:20px">'+datax['fecha']+'</td></tr>');
              ventimp.document.write('<tr><td colspan="3" height="10px"></td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Folio:</td><td align="left" colspan="2" style="font-size:20px">'+datax['folio']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Genera:</td><td align="left" colspan="2" style="font-size:20px">'+datax['genera']+'</td></tr>');
              ventimp.document.write('<tr><td colspan="3" height="15px"></td></tr>');
              ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
              ventimp.document.write('<tr><td width="50%" style="font-size:20px">PRODUCTO</td><td align="center" width="20%" style="font-size:20px">CANTIDAD</td><td align="center" width="30%" style="font-size:20px">PRECIO</td></tr>');
              ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
              for(var i=0; i<nmbart.length;i++){
                ventimp.document.write('<tr><td width="50%" style="font-size:20px">'+nmbart[i]+'</td><td align="center" width="20%" style="font-size:20px">'+cantart[i]+'</td><td align="center" width="30%" style="font-size:20px">$'+preart[i]+'</td></tr>');
                ventimp.document.write('<tr><td colspan="3" height="5px"></td></tr>');
              }
              ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Efectivo recibido:</td><td align="right" style="font-size:14px">$'+datax['efectivorec']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Efectivo cobrado:</td><td align="right" style="font-size:14px">$'+datax['efectivo']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Cambio:</td><td align="right" style="font-size:14px">$'+datax['cambio']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Tarjeta:</td><td align="right" style="font-size:14px">$'+datax['tarjeta']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Total cobrado:</td><td align="right" style="font-size:14px">$'+datax['totalcd']+'</tr>');
              ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
              ventimp.document.write('<tr><td width="100%" colspan="3" align="center" style="font-size:20px">CUENTA</td>');
              if(datax['descuentop'] != "0.00"){
                ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">% Descuento:</td><td align="right" style="font-size:20px">'+datax['descuentop']+'</td></tr>');
                ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Subtotal:</td><td align="right" style="font-size:20px">$'+datax['subtotal']+'</td></tr>');
                ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Descuento:</td><td align="right" style="font-size:20px">$'+datax['descuento']+'</td></tr>');
              }
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Total:</td><td align="right" style="font-size:20px">$'+datax['totalrem']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Abonado:</td><td align="right" style="font-size:20px">$'+datax['abonado']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Saldo:</td><td align="right" style="font-size:20px">$'+datax['saldo']+'</tr>');
              
              ventimp.document.write('<tr><td colspan="3" height="20px"></td></tr>');
              ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:20px">Gracias por su compra</td></tr>');
              ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:20px">Este recibo no es un comprobante fiscal</td></tr>');
              ventimp.document.write('</table></body></html>');
              ventimp.document.close();
              ventimp.focus();

              ventimp.addEventListener('load', function () {
                //ventimp.document.open(); 
                  window.close(); 
                  ventimp.print();
                  // Realiza las operaciones en el documento de la ventana emergente aquí
                  ventimp.document.close();
              });
            }
          });
        }
      </script>
      <?php
      $clienteAlias = busca($this->model->cliente, 'crm_clientes','c_id','c_alias');
      $nmbcl = busca($this->model->cliente, 'crm_clientes','c_id','c_nmb');
      $telcl = busca($this->model->cliente, 'crm_clientes','c_id','c_telefono2');
      $chneg1 = '';
      $activeneg1 = '';
      $chneg2 = '';
      $activeneg2 = '';
      $chneg3 = '';
      $activeneg3 = '';
      $chneg4 = '';
      $activeneg4 = '';
      $bgbutton = '';
      if($this->model->negociacion == 1) {$chneg1 = 'checked'; $activeneg1 = 'btn-danger active'; $bgbutton = $activeneg1; }
      elseif($this->model->negociacion == 2) {$chneg2 = 'checked'; $activeneg2 = 'btn-warning active'; $bgbutton = $activeneg2; }
      elseif($this->model->negociacion == 4) {$chneg4 = 'checked'; $activeneg4 = 'btn-danger active'; $bgbutton = $activeneg4; }
      else {$chneg3 = 'checked'; $activeneg3 = 'btn-info active'; $bgbutton = $activeneg3; }

      $arrayneg = array("N","P","G","V","O","L");
      if(in_array($this->model->estatus,$arrayneg)){
        $read= ''; 
        $hd = '';
        
      }else {
        $read = 'readonly';
        $hd = 'hidden';
        $action = '';
      }
      $action = '?modulo=tableros&accion=updatetablero&id='.$_GET['id'];

      $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
      if($grupo != "ADMIN") {
        $sel = 'style="display: none;"';
        $inp = 'style="display: block;"';
      }else {
        $sel = 'style="display: block;"';
        $inp = 'style="display: none;"';
      }

      $arrayneg = array("N","P","G","V","O","L");
      $ests = " ";
      $estN = " ";
      $estP = " ";
      $estF = " ";
      $estC = " ";
      $estT = " ";
      if($estatus == "S") $ests = " selected ";
      elseif($estatus == "N") $estN = " selected ";
      elseif($estatus == "P") $estP = " selected ";
      elseif($estatus == "F") $estF = " selected ";
      elseif($estatus == "C") $estC = " selected ";
      elseif($estatus == "T") $estT = " selected ";

      echo '<div class="modal fade text-xs-left" id="newtablero" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title text-text-bold-600" id="myModalLabel33">Información del tablero</h4>
            </div>
            <form method="post" action="'.$action.'" autocomplete="off" >
              <div class="modal-body row">
                <div class="col-md-9 col-xs-12">
                  <div class="mb-5">  
                    <label>Cliente: </label>
                    <input type="text" value="'.$clienteAlias.'" placeholder="Nombre o alias del cliente" class="form-control" disabled>
                    <input type="hidden" value="'.$this->model->cliente.'" name="idcliente" id="idcliente">
                  </div>
                </div>
                <div class="col-md-3 col-xs-12">
                  <div class="mb-5">  
                    <a href="?modulo=clientes&accion=edit&id='.$this->model->cliente.'" target="_BLANK">
                      <center>
                        <button type="button" class="btn bg-info mt-6 text-white" data-toggle="tooltip" data-placement="top" title="Ver información del cliente"><i class="fa fa-eye" style="color: #ffffff;"></i> Ver cliente</button>
                      </center>
                    </a>
                  </div>
                </div>
                <div class="col-7 col-md-7 col-xs-12" >
                  <label>Nombre del cliente: </label>
                  <input type="text" value="'.$nmbcl.'" name="nmbcl" placeholder="Nombre del cliente" class="form-control" '.$read.'>
                </div>
                <div class="col-5 col-md-5 col-xs-12" >
                  <label>Telefono del cliente: </label>
                  <input type="text" value="'.$telcl.'" name="telcl" placeholder="Telefono del cliente" class="form-control" '.$read.'>
                </div>
                <div class="col-md-5 col-xs-12 mt-2">
                  <label>Nombre proyecto: </label>
                  <div class="mb-5">
                    <input type="text" name="nmb" id="nmbt" value="'.$this->model->nmb.'" placeholder="Nombre del tablero/proyecto" class="form-control" onfocus="this.select();" required '.$read.'>
                  </div>
                </div>
                <div class="col-md-4 col-xs-12  mt-2">
                  <label>Fecha inicio: </label>
                  <div class="mb-5">
                    <div class="position-relative has-icon-left">
                      <input type="datetime" class="form-control" value="'.$this->model->fini." ".$this->model->hini.'" required '.$read.' step="any">
                      <div class="form-control-position">
                        <i class="icon-calendar5"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 col-xs-12  mt-2">
                  <label>Fecha Cierre: </label>
                  <div class="mb-5">
                    <div class="position-relative has-icon-left">
                      <input type="date" id="fcierre" name="fcierre" class="form-control" value="'.$this->model->fcierre.'" required '.$read.' >
                      <div class="form-control-position">
                        <i class="icon-calendar5"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 col-xs-12">
                  <label>Propietario: </label>
                  <div class="mb-5">
                    <select class="form-control" searchable="Responsable del tablero" name="responsable" '.$sel.'>';
                      $sqlu = 'SELECT u_nuser,u_id,u_nmb, u_apellidos FROM usuarios WHERE u_estatus = "A" AND u_grupo = "VENTAS" OR u_grupo = "GERENCIA"';
                      $resultu = setq($sqlu);
                      while($rowu = $resultu->fetch_array()){
                        if($rowu['u_id'] != "ADMIN"){
                          if($this->model->agente == $rowu['u_id']) $seldis = "selected"; else $seldis = "";
                          echo '<option value="'.$rowu['u_id'].'" '.$seldis.'>'.$rowu['u_nmb'].' '.$rowu['u_apellidos'].'</option>';
                        }
                      }
                    echo '</select>
                    <input name="responsable" class="form-control" type="text" value="'.busca($this->model->agente, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'" '.$inp.' disabled> 
                  </div>
                </div>
                <input type="hidden" value="'.$this->model->negociacion.'" name="niveltab" id="niveltab" />
                <div class="col-md-8 col-xs-12 row mb-5" '.$hd.'>
                  <label>Nivel de negociación: </label> <br>
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-secondary btn-min-width mr-1 mb-1 '.$activeneg1.'" id="op1"  onclick="nivelta(1)">
                      <input type="radio" name="options" id="option1" '.$chneg1.'> Caliente
                    </label>
                    <label class="btn btn-secondary btn-min-width mr-1 mb-1 '.$activeneg2.'" id="op2" onclick="nivelta(2)">
                      <input type="radio" name="options" id="option2" '.$chneg2.'> Tibio
                    </label>
                    <label class="btn btn-secondary btn-min-width mr-1 mb-1 '.$activeneg3.'" id="op3" onclick="nivelta(3)">
                      <input type="radio" name="options" id="option3" '.$chneg3.'> Frio
                    </label>
                    <label class="btn btn-secondary btn-min-width mr-1 mb-1 '.$activeneg4.'" id="op4" onclick="nivelta(4)">
                      <input type="radio" name="options" id="option4" '.$chneg4.'> Perdido
                    </label>
                  </div>
                </div>
                <div class="col-6 col-md-5" hidden>
                  <div class="mb-5">
                    <h4>Valor($): </h4>
                    <input id="valor" name="valor" type="number" min="0" class="form-control" value="'.$this->model->valor.'" '.$read.'>
                  </div>
                </div>
              </div>
              <div class="modal-footer p-2">
                <center><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Actualizar tablero</button></center>
              </div>
            </form>
          </div>
        </div>
      </div>';
      $clienteNmb = busca($this->model->cliente, 'crm_clientes','c_id','c_nmb');
          $botones = '';
          $botones .= '<a href="?modulo=tableros&accion=index">
            <button type="button" class="btn btn-warning btn-sm"><i class="fa fa-arrow-left"></i>Atrás</button>
          </a>';
          $botones .= '<button id="nuevo" type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#newtablero" data-placement="top" title="Ver la información del tablero">
              <i class="fa fa-info"></i> Información
            </button>';
          if($this->model->estatus == "A" || $this->model->estatus == "N"){
            $botones .= '<a data-fancybox data-type="ajax" data-src="popup/newactividad.php?tablero='.$this->model->id.'&act=2" href="javascript:;">
              <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" data-placement="top" title="Nueva actividad"><i class="fa fa-plus"></i> Actividad</button>
            </a>';
            
            /* $botones .= '<a data-fancybox accesskey="M" data-type="ajax" data-src="popup/setremision.php?cliente='.$this->model->cliente.'&tablero='.$this->model->id.'&rand='.rand().'" href="javascript:;">
              <button class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Crear una nueva remisión (Alt+M)"><i class="fa fa-dollar-sign"></i> Remisión</button>
            </a>'; */
          }
          $botones .= '<a data-fancybox accesskey="C" data-type="ajax" data-src="popup/setcotiza.php?tablero='.$this->model->id.'&tablero='.$this->model->id.'&rand='.rand().'" href="javascript:;">
              <button type="button" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="Crear una nueva cotización (Alt+C)"><i class="fa fa-comments-dollar"></i>Cotización</button>
            </a>';

          
          $run = false;
          $gastos = busca($this->model->id,'gastos','g_estatus IN ("N","P") AND g_tablero','COUNT(*)');
          if($gastos > 0){
            $sqli = ' INNER JOIN gastos ON ct_id = g_tablero';
            $sqlic = ' AND g_estatus IN ("N","P")';
            $run = true;
          }
          $ordenesp = busca($this->model->id,'ordenesp','op_estatus = "N" AND op_proyecto','COUNT(*)');
          if($ordenesp > 0){
            $sqli = ' INNER JOIN ordenesp ON ct_id = op_proyecto';
            $sqlic = ' AND op_estatus = "N"';
            $run = true;
          }
          $rem = busca($this->model->id,'remisiones','r_estatus = "A" AND r_tablero','COUNT(*)');
          if($rem > 0){
            $sqli = ' INNER JOIN remisiones ON ct_id = r_tablero INNER JOIN cxcobrar ON cx_referencia = r_id';
            $sqlic = ' AND cx_estatus IN ("N","A")';
            $run = true;
          }
          $orden = busca($this->model->id,'ordenesc','o_estatus = "A" AND o_tablero','COUNT(*)');
          if($orden > 0){
            $sqli = ' INNER JOIN ordenesc ON ct_id = o_tablero INNER JOIN cxpagar ON o_id = cp_idref';
            $sqlic = ' AND cp_estatus IN ("N","A")';
            $run = true;
          }
          $sqltp = 'SELECT COUNT(*) FROM crm_tableros '.$sqli.'
                    WHERE ct_id = "'.$this->model->id.'" '.$sqlic.'';
          if($run) {
            $resulttp = setq($sqltp);
            list($coincidencias) = $resulttp->fetch_array();
          }else $coincidencias = 0;
          if($this->model->estatus == "A"){
            $recurrente = busca($this->model->id,'crm_tablerosrecurrentes','ctr_estatus = "A" AND ctr_tablero','COUNT(*)');
            if($coincidencias == 0){
              $botones .= '
              <button type="button" class="btn btn-sm btn-success" onclick="cerrartablero( '.$this->model->id.' ,\'F\','.$recurrente.');" data-toggle="tooltip" data-placement="top" title="Cerrar tablero al finalizar la negociación"><i class="fa fa-check"></i> Cerrar Tablero</button>
              ';
            }

              //echo'<button type="button" class="btn btn-success text-white mr-1" onclick="cerrartablero('.$this->model->id.',F,'.$recurrente.');"><i class="fa fa-check"></i> Cerrar Tablero</button>';
          }elseif($this->model->estatus == "N"){
            if($coincidencias == 0)
            $botones .= '
            <button type="button" class="btn btn-sm btn-danger" onclick="cerrartablero('.$this->model->id.',\'C\',\'0\');" data-toggle="tooltip" data-placement="top" title="Dar por perdido el tablero"><i class="fa fa-times"></i> Tablero Perdido</button>
            ';
              //echo'<button type="button" class="btn btn-danger text-white mr-1" onclick="cerrartablero('.$this->model->id.',C,0);"><i class="fa fa-times"></i> Tablero Perdido</button>';
          }
          toolbar($_GET['modulo'],$botones);
          $countcotN = busca($this->model->id, 'crm_cotizaciones', 'cc_estatus IN ("N", "D", "R")  AND cc_tablero' ,'COUNT(*)');
          $countcotL = busca($this->model->id, 'crm_cotizaciones', 'cc_estatus = "L"  AND cc_tablero' ,'COUNT(*)');
          $countcotA = busca($this->model->id, 'crm_cotizaciones', 'cc_estatus = "A"  AND cc_tablero' ,'COUNT(*)');
          $countcotP = busca($this->model->id, 'crm_cotizaciones', 'cc_estatus = "P"  AND cc_tablero' ,'COUNT(*)');
          $countremN = busca($this->model->id, 'remisiones', 'r_estatus = "N" AND r_tablero', 'COUNT(*)');
          $countremdp = busca($this->model->id, 'remisiones', 'r_estatus IN ("D", "P") AND r_tablero', 'COUNT(*)');   
          $countremV = busca($this->model->id, 'remisiones INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_estatus IN ("N", "A") AND r_estatus = "A" AND r_tablero' ,'COUNT(*)');
          $countremF = busca($this->model->id, 'remisiones INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_estatus = "F" AND r_estatus IN ("F") AND r_tablero' ,'COUNT(*)');
          $countenvP = busca($this->model->id, 'remisiones INNER JOIN guias_articulos ON ga_cotizacion = r_id', 'ga_estatus IN ("P", "A") AND r_tablero' ,'COUNT(*)');
          $countenvF = busca($this->model->id, 'remisiones INNER JOIN guias_articulos ON ga_cotizacion = r_id', 'ga_estatus = "F" AND r_tablero' ,'COUNT(*)');
          $countartF = busca($this->model->id, 'remisiones INNER JOIN remisionesc ON rc_remision = r_id', 'rc_estatus = "F" AND r_tablero' ,'COUNT(*)');
          $countcotC = busca($this->model->id, 'crm_cotizaciones', 'cc_estatus = "C" AND cc_tablero' ,'COUNT(*)');
          $countremC = busca($this->model->id, 'remisiones', 'r_estatus = "C" AND r_tablero' ,'COUNT(*)');
          
      echo '
      <div class="card mt-3">
      <div class="card-body row">';
        echo '<div class="d-flex" style="justify-content: space-between">';
        if($countcotN > 0){
          echo '<div class="alert col-4" style="background:#F4D03F"><center>EN CONSTRUCCIÓN</center></div>';
        } else if($countcotL >0){
          echo '<div class="alert col-4" style="background:#F5B041"><center>ESPERANDO COSTO DE LOGÍSTICA</center></div>';
        }else if($countcotP>0){
          echo '<div class="alert col-4" style="background:#E67E22"><center>COTIZACIÓN PENDIENTE DE ENVÍO</center></div>';
        }else if($countcotA>0){
          echo '<div class="alert col-4" style="background:#58D68D"><center>COTIZACIÓN ENVIADA</center></div>';
        }else if($countremN>0){
          echo '<div class="alert col-4 text-white" style="background:#C0392B"><center>ESPERANDO APLICACIÓN DE REMISIÓN</center></div>';
        }else if($countremdp>0){
          echo '<div class="alert col-4 text-white" style="background:#8B57E5"><center>ESPERANDO PRIMER ABONO</center></div>';
        }else if($countremV>0){
          echo '<div class="alert col-4" style="background:#45B39D"><center>VENDIDO-ABONADO</center></div>';
        }else if($countremF>0){
          echo '<div class="alert col-4" style="background:#AAB7B8"><center>ARTÍCULOS PENDIENTES DE ENVÍO</center></div>';
        }else if($countenvP>0){
          echo '<div class="alert col-4 text-white" style="background:#AAB7B8"><center>PREPARADO PARA ENVÍO</center></div>';
        }else if($countenvF>0  || $countartF>0){
          echo '<div class="alert col-4 text-white" style="background:#8E44AD"><center>ARTICULOS ENVIADOS</center></div>';
        } else if($this->model->id == "X"){
          echo '<div class="alert col-4 text-white" style="background:#C0392B"><center>TABLERO PERDIDO</center></div>';
        }  else if($countcotC>0){
          echo '<div class="alert text-white" style="background:#000000 "><center>Cotizaciones canceladas</center></div>';
        } else if($countremC>0){
          echo '<div class="alert text-white" style="background:#000000 "><center>Remisiones canceladas</center></div>';
        }
        echo '<h1> VALOR: $'.number_format($this->model->valor, 2).' </h1>
        </div>';
          echo '<div class="col-md-12 mt-5">
            <div class="table-responsive">
              <center>
                <table width="100%" class="mb-0 table ">
                  <thead class="bg-primary text-white">
                    <tr>
                      <th width="14%"><b>Folio</b></th>
                      <th width="20%"><b>Actividad</b></th>
                      <th width="15%"><b>Ejecución</b></th>
                      <th width="23%"><b>Descripción</b></th>
                      <th width="8%"><b>Importe</b></th>
                      <th width="5%"><b>Estatus</b></th>
                      <th width="15%"><b>Acción</b></th>
                    </tr>
                  </thead>
                  <tbody>';
                  $sql = 'SELECT r_falta fecha, r_folio folio, "REMISION" actividad, "R" tipo, r_nmb descripcion,r_estatus estatus,r_id id, r_total importe 
                  FROM remisiones WHERE r_tablero = '.$this->model->id.' 
                  UNION SELECT i_fecha, CONCAT("INGRESO ",i_id),"INGRESO","ING", i_observaciones, i_estatus estatus,i_id, i_monto
                  FROM ingresos WHERE i_id IN (SELECT ic_ingreso FROM ingreso_cxcobrar WHERE ic_cxcobrar IN (SELECT cx_id FROM cxcobrar INNER JOIN remisiones ON r_id = cx_referencia WHERE r_tablero = '.$this->model->id.')) 
                  UNION SELECT rc_fenvio,ga_nmb,"GUÍAS","GA",r_folio,ga_estatus,ga_id, ""
                  FROM guias_articulos INNER JOIN remisionesc ON rc_remision = ga_cotizacion INNER JOIN remisiones ON r_id = ga_cotizacion WHERE ga_cotizacion IN (SELECT r_id FROM remisiones WHERE r_tablero = "'.$this->model->id.'") GROUP BY ga_id
                  UNION SELECT op_fechac,op_folio,"ORDEN DE PAGO","OP",op_concepto,op_estatus,op_id, op_importe
                  FROM ordenesp WHERE op_proyecto = '.$this->model->id.'
                  UNION SELECT cd_fgen,r_folio,"INGRESO EN CAJA","INGC",CONCAT("Efectivo: $",cd_efectivo," Tarjeta: $",cd_tarjeta),cd_estatus,cd_id, cd_total
                  FROM cajasd INNER JOIN remisiones ON r_id = cd_remision WHERE r_tablero = '.$this->model->id.'
                  UNION SELECT f_fcreo, "FACTURA","FACTURA","FAC", "",f_estatus,f_id, (f_subtotal + f_iva) AS suma
                  FROM facturas WHERE f_idremision = '.$this->model->id.'
                  UNION SELECT cc_fini,cc_folio,"COTIZACIÓN","C",cc_nmb,cc_estatus,cc_id,cc_importe
                  FROM crm_cotizaciones WHERE cc_tablero = '.$this->model->id.' UNION SELECT o_fgen,o_folio,"ORDEN DE COMPRA","OC",o_observaciones,o_estatus,o_id,o_total
                  FROM ordenesc WHERE o_tablero = '.$this->model->id.'
                  UNION SELECT g_fecha,g_folio,"GASTO/VALE","G",g_descripcion,g_estatus,g_id,g_importe FROM gastos 
                  WHERE g_tablero = '.$this->model->id.'
                  ORDER BY fecha DESC';
                   /* UNION SELECT TIMESTAMP(crm_actividades.ca_fecha,crm_actividades.ca_hora),NULL,crm_actividades.ca_nmb,"A",
                  crm_actividades.ca_descripcion,crm_actividades.ca_estatus,crm_actividades.ca_id,NULL FROM crm_actividades INNER JOIN crm_acciones ON 
                  crm_actividades.ca_accion = crm_acciones.ca_id  WHERE ca_tablero = '.$this->model->id.'  */
                  $result = setq($sql);
                  $cont = 1;
                  $nguias = 0;
                  while($row = $result->fetch_array()){
                    if($row['tipo'] == "A"){
                      $accion = busca($row['id'],'crm_actividades','ca_id','ca_accion');
                      $iconact = busca($accion,'crm_acciones','ca_id','ca_icono');
                      $icon = '<i class="'.$iconact.'"></i>';
                    }else $icon = '<i class="icon-money"></i>';

                    if(($row['estatus'] == "P" && $row['tipo'] == "C") || ($row['estatus'] == "N" && $row['tipo'] == "R") || ($row['estatus'] == "D" && $row['tipo'] == "C")){
                      $banner = '&nbsp;<span data-toggle="tooltip" data-placement="top" class="badge badge-success"><i class="fas fa-check-circle" style="color: white;"></i></span>';
                      if($row['estatus'] == "D" && $row['tipo'] == "C"){
                        $nbanner = intval(busca("D", 'crm_cotizaciones', 'cc_motivo IS NOT NULL AND cc_folio = "'.$row['folio'].'" AND cc_estatus', 'COUNT(*)'));
                        if($nbanner > 0){
                          $banner = '&nbsp;<span data-toggle="tooltip" data-placement="top" class="badge badge-success"><i class="fas fa-check-circle" style="color: white;"></i></span>';
                        }
                      }
                    } else{
                      $banner = "";
                    }

                    echo '
                      <tr>';
                        echo'<td><label class="font-small-3"><b>'.$row['folio'].$banner.'</b></label></td>
                        <td><label class="font-small-3"><b>'.$icon.' '.$row['actividad'].'</b></label></td>';
                        if($row['tipo'] != "GA") echo '<td><label class="font-small-3">'.fecha_formato($row['fecha'],true,false).'</label></td>';
                        else {
                          $fecha = substr($row['fecha'], 0, 10);
                          echo '<td><label class="font-small-3">'.fecha_formato($fecha, false, false).'</label></td>';
                        }                    
                        if($row['tipo'] == "OC"){
                          $totalOC = busca($row['id'],'ordenesc','o_id','o_total');
                          echo'<td><label class="font-small-3">'.$row['descripcion'].' </label></td>';
                        }else{
                          if($row['tipo'] == "GA"){
                            $idremision = busca($row['id'], 'guias_articulos', 'ga_id', 'ga_cotizacion');
                            if($cont == 1){
                              $nguias = busca($idremision, 'guias_articulos', 'ga_cotizacion', 'COUNT(*)');
                            }

                            
                            $remision = busca($idremision, 'remisiones', 'r_id', 'r_folio');
                            echo'<td><label class="font-small-3">GUÍA '.$cont.' DE '.$nguias.' DE LA REMISIÓN '.$remision.'</label></td>';
                            $cont++;
                          } else if ($row['tipo'] == "ING" && $row['estatus'] == "C"){
                            $motivo = busca($row['id'], 'ingresos','i_id','i_motivocan');
                            echo'<td><label class="font-small-3">Motivo de la cancelación: <b>'.$motivo.'</b></label></td>';
                          } else{
                            echo'<td><label class="font-small-3">'.$row['descripcion'].'</label></td>';
                          }
                        }
                        if($row['importe'])
                          echo'<td class="text-medium" style="text-align:rigth">$'.number_format($row['importe'],2).'</td>';
                        else 
                          echo'<td><label class="font-small-3"> - </label></td>';
                        if($row['tipo'] == "OP"){
                          $cxp = busca($row['id'],'ordenesp','op_id','op_cxpagar');
                          if($row['estatus'] == "N"){
                            echo '<td class="alert alert-warning no-border" role="alert">Pendiente de pago</td>';
                            echo '<td>
                              <a target="_BLANK" href="?modulo=cxpagar&accion=show&idcxp='.$cxp.'" >
                                <button type="button" class="btn btn-info" /><i class="fa fa-pen"></i></button>
                              </a>
                            </td>';
                          }elseif($row['estatus'] == "A"){ 
                            echo '<td class="alert bg-success bg-light-4 no-border" role="alert">Pagada</td>';
                            echo '<td>
                              <a href="?modulo=cxpagar&accion=show&idcxp='.$cxp.'" >
                                <button type="button" class="btn btn-info" /><i class="fa fa-eye"></i></button>
                              </a>
                            </td>';
                          }
                        }elseif($row['tipo'] == "C"){
                          if($row['estatus'] == "N") echo '<td class="alert no-border" style="background: #ffc107" role="alert">En construcción</td>';
                          elseif($row['estatus'] == "V") echo '<td class="alert no-border text-white" style="background: #007bff" role="alert">Vendida</td>';
                          elseif($row['estatus'] == "L") echo '<td class="alert no-border text-white" style="background: #6c757d" role="alert">En logística</td>';
                          elseif($row['estatus'] == "A") echo '<td class="alert no-border text-white" style="background: #20c997" role="alert">Aplicada</td>';
                          elseif($row['estatus'] == "R") echo '<td class="alert no-border text-white" style="background: #6610f2" role="alert">Replicada</td>';
                          elseif($row['estatus'] == "P") echo '<td class="alert no-border" style="background: #fd7e14" role="alert">Pendiente de envío</td>';
                          elseif($row['estatus'] == "D") echo '<td class="alert no-border text-white" style="background: #17a2b8" role="alert">Definición de envíos</td>';
                          else echo '<td class="alert no-border text-white" style="background: #dc3545" role="alert">Cancelada</td>';
                          echo '<td>';
                            if($row['estatus'] == "N" || $row['estatus'] == "R"){
                              echo '<a href="?modulo=cotizaciones&accion=index&id='.$row['id'].'" >
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-pen"></i></button>
                              </a>';
                            }else{
                              echo '<a href="?modulo=cotizaciones&accion=index&id='.$row['id'].'" >
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-eye"></i></button>
                              </a>';
                            }
                            if($row['estatus'] != "N"){
                              echo '<a target="_BLANK" href="formats/pdfcotizacion.php?idcotiza='.$row['id'].'" >
                                <button type="button" class="btn btn-sm btn-danger" /><i class="fa fa-file-pdf"></i></button>
                              </a>';
                            }
                          echo '</td>';
                        }elseif($row['tipo'] == "R"){
                          if($row['estatus'] == "N") echo '<td class="alert no-border" role="alert" style="background: #FFEAAB">En construcción</td>';
                          elseif($row['estatus'] == "A")echo '<td class="alert alert-success no-border" role="alert" style="background: #B0FFE7">Vendida</td>';
                          elseif($row['estatus'] == "D")echo '<td class="alert alert-success no-border" role="alert" style="background: #B7DAFF">Por aprobar en caja</td>';
                          elseif($row['estatus'] == "P")echo '<td class="alert alert-success no-border" role="alert" style="background: #B7DAFF">Por aprobar en finanzas</td>';
                          elseif($row['estatus'] == "F")echo '<td class="alert no-border text-white" role="alert" style="background: #20c997">Liquidada</td>';
                          elseif($row['estatus'] == "FE")echo '<td class="alert no-border text-white" role="alert" style="background: #8B4DFF">Liquidada y enviada</td>';
                          else echo '<td class="alert alert-danger no-border" role="alert" style="background: #FFCBD0">Cancelada</td>';
                          echo '<td>';
                            if($row['estatus'] == "N"){
                              echo '<a target="_BLANK" href="?modulo=remisiones&accion=show&id='.$row['id'].'" >
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-pen"></i></button>
                              </a>';
                            }else{
                              echo '<a href="?modulo=remisiones&accion=show&id='.$row['id'].'">
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-eye"></i></button>
                              </a>';
                            }
                            if($row['estatus'] != "N"){
                              echo '<a target="_BLANK" href="formats/notadeventa.php?id='.$row['id'].'">
                                <button type="button" class="btn btn-sm  btn-danger" /><i class="fa fa-file-pdf"></i></button>
                              </a>';
                            }
                          echo '</td>';
                        }elseif($row['tipo'] == "OC"){
                          if($row['estatus'] == "N") echo '<td class="alert alert-warning no-border" role="alert">En captura</td>';
                          elseif($row['estatus'] == "A") echo '<td class="alert bg-amber bg-darken-4 no-border" role="alert">Aplicada</td>';
                          elseif($row['estatus'] == "P") echo '<td class="alert bg-amber bg-darken-4 no-border" role="alert">Espera de Autorización</td>';
                          elseif($row['estatus'] == "C") echo '<td class="alert alert-success no-border" role="alert">Cancelada</td>';
                          elseif($row['estatus'] == "R" || $row['estatus'] == "F") echo '<td class="alert bg-primary no-border" role="alert">Recibida</td>';
                          echo '<td>';
                            if($row['estatus'] == "N"){
                              echo '<a target="_BLANK" href="?modulo=ordenesc&accion=show&id='.$row['id'].'" >
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-pen"></i></button>
                              </a>';
                            }else{
                              echo '<a href="?modulo=ordenesc&accion=show&id='.$row['id'].'" >
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-eye"></i></button>
                              </a>';
                            }
                            if($row['estatus'] != "N"){
                              echo '<a target="_BLANK" href="formats/pdfordenc.php?id='.$row['id'].'" >
                                <button type="button" class="btn btn-sm btn-danger" /><i class="fa fa-file-pdf"></i></button>
                              </a>';
                            }
                            if($row['estatus'] == "A"){
                              echo '<a data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?ordenc='.$row['id'].'" href="javascript:;">
                                <button type="button" class="btn btn-sm btn-primary">
                                  <span class="glyphicon glyphicon-cog"></span><i class="icon-cart-arrow-down"></i>
                                </button>
                              </a>';
                            }
                          echo '</td>';
                        }elseif($row['tipo'] == "A"){
                          echo '<input type="hidden" name="nmbact'.$row['id'].'" id="nmbact'.$row['id'].'" value="'.busca($row['id'],'crm_actividades','ca_id','ca_nmb').'">';
                          if($row['estatus'] == "N"){ $alrt = ' class="alert alert-warning no-border" role="alert" '; $estatus = "Nuevo"; }
                          elseif($row['estatus'] == "P"){ $alrt = ' class="alert alert-primary no-border" role="alert" '; $estatus = "Proceso"; }
                          elseif($row['estatus'] == "F"){ $alrt = ' class="alert alert-success no-border" role="alert" '; $estatus = "Finalizado"; }
                          else{  $alrt = ' class="alert alert-danger no-border" role="alert" '; $estatus = "Cancelado"; }
                          echo '<td '.$alrt.'>'.$estatus.'</td>';
                          echo '<td>';
                            if($row['estatus'] == "N" || $row['estatus'] == "P"){
                              $usermodif = 1;
                              $iconbtn = 'pen';
                              if(busca($_SESSION['uid'],'crm_actividadesd','cad_tipomiembro IN ("U","R") AND cad_actividad = "'.$row['id'].'" AND cad_user','COUNT(*)') > 0) $useract = '&user=U';
                              else {
                                $useract = '&readon=1';
                                $usermodif = 0;
                                $iconbtn = 'eye';
                              }
                              echo '<a data-fancybox data-type="ajax" data-src="popup/setactividad.php?tablero='.$this->model->id.'&idact='.$row['id'].$useract.'" href="javascript:;" >
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-'.$iconbtn.'"></i></button>
                              </a>';
                              
                              if($usermodif == 1){
                                echo '<button type="button" class="btn btn-sm btn-success" onclick="confirma('.$row['id'].');"><i class="fa fa-check"></i></button>&nbsp';
                                echo '<button type="button" class="btn btn-sm btn-danger" onclick="cancela('.$row['id'].');"><i class="fa fa-times"></i></button>';
                                /*echo '<button type="button" class="btn btn-success" onclick="confirma('.$row['id'].');"/><i class="fa fa-check"></i></button>
                                <a data-fancybox data-type="ajax" id="pop'.$row['id'].'" data-src="popup/setactividad.php?idact='.$row['id'].'&confirma=1&readon=1" href="javascript:;"></a>';
                                echo '<button type="button" class="btn btn-danger" onclick="cancela('.$row['id'].');"/><i class="fa fa-times"></i></button>
                                <a data-fancybox data-type="ajax" id="popc'.$row['id'].'" data-src="popup/setactividad.php?idact='.$row['id'].'&cancela=1&readon=1" href="javascript:;"></a>';*/
                              }
                            }elseif($row['estatus'] == "F"){
                              echo '<a data-fancybox data-type="ajax" data-src="popup/newactividad.php?tablero='.$this->model->id.'&act='.busca($row['id'],'crm_actividades','ca_id','ca_accion').'&clone='.$row['id'].'" href="javascript:;">
                                <button class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Clonar actividad"><i class="icon-clone"></i></button>
                              </a>'; 
                            }
                          echo '</td>';
                        }elseif($row['tipo'] == "G"){
                          if($row['estatus'] == "N") echo '<td class="alert alert-warning no-border" role="alert">En captura</td>';
                          elseif($row['estatus'] == "A")echo '<td class="alert alert-success no-border" role="alert">Aplicado</td>';
                          elseif($row['estatus'] == "C")echo '<td class="alert alert-danger no-border" role="alert">Cancelado</td>';
                          else echo '<td class="alert bg-amber bg-darken-4 no-border" role="alert">Pendiente de aprobación</td>';
                          echo '<td>';
                            if($row['estatus'] == "N"){
                              echo '<a target="_BLANK" href="?modulo=gastos&accion=show&id='.$row['id'].'">
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-pen"></i></button>
                              </a>';
                            }else{
                              echo '<a target="_BLANK" href="?modulo=gastos&accion=show&id='.$row['id'].'">
                                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-eye"></i></button>
                              </a>';
                            }
                          echo '</td>';
                        } else if($row['tipo'] == "ING"){
                          if($row['estatus'] == "P") echo '<td class="alert" style="background:#FFB162;">POR CONFIRMAR</td>';
                          elseif($row['estatus'] == "F")echo '<td class="alert text-white" style="background:#3F92FF ;">AUTORIZADO</a></td>';
                          elseif($row['estatus'] == "C")echo '<td class="alert" style="background:#F03D21;color:#FFFFFF;">CANCELADO</td>';
                          $i = 0;
                          $adjunto = busca($row['id'], 'ingresos', 'i_id', 'i_adjunto');
                          $path = 'adjuntos/ingresos/';

                          if($adjunto != ""){
                            echo '<td>
                            <a target="_BLANK" href="'.$path.$adjunto.'">
                              <button type="button" class="btn btn-primary"><i class="fas fa-file-image"></i></button>
                            </a>
                            </td>';
                          }
                        } else if($row['tipo'] == "GA"){
                          $remision = busca($row['id'], 'guias_articulos', 'ga_id', 'ga_cotizacion');
                          $count = busca($remision, 'remisionesc', 'rc_guia = "'.$row['id'].'" AND rc_remision', 'COUNT(*)');
                          if(($row['estatus'] == "A" || $row['estatus'] == "P") && floatval($count) > 0) echo '<td class="alert" style="background:#FFB162;">PREPARADA</td>';
                          else if(($row['estatus'] == "A" || $row['estatus'] == "P") && floatval($count) == 0) echo '<td class="alert" style="background:#FFB162;">EN CARGA DE ARTÍCULOS</td>'; 
                          elseif($row['estatus'] == "F") echo '<td class="alert text-white" style="background:#3F92FF ;">ENVIADA</a></td>';
                          echo '<td>';
                          if($count > 0)
                          echo '<a target="_BLANK" href="formats/pdfguias.php?idrem='.$remision.'" >
                              <button type="button" class="btn btn-sm btn-danger" /><i class="fa fa-file-pdf"></i></button>
                            </a>';
                          $countimgp = busca($row['id'], 'guias_imagenes', 'gi_tipo = "P" ANd gi_guia', 'COUNT(*)');
                          if($countimgp > 0)
                          echo '<a id="nuevo" class="btn btn-sm btn-success" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia='.$row['id'].'&tipo=P" href="javascript:;">
                            <i class="fas fa-truck-loading"></i>
                          </a>';
                          $countimge = busca($row['id'], 'guias_imagenes', 'gi_tipo = "E" ANd gi_guia', 'COUNT(*)');
                          if($countimge > 0)
                          echo '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia='.$row['id'].'&tipo=E" href="javascript:;">
                            <i class="fas fa-plane-departure"></i>
                          </a>';
                          echo '</td>';
                          
                        } else if($row['tipo'] == "FAC"){
                          $estatus = array("N" => "Proceso de Captura", "P" => "Lista para ser Timbrada", "T" => "Timbrada", "C" => "Cancelada", "Q" => "Solicitud de Cancelación");
                          $colestatus = array("N" => "#FFCC33", "P" => "#FF9933", "T" => "#00CC66", "C" => "#990000", "Q" => "#3399FF");
                          echo '<td class="alert" style="background:'.$colestatus[$row['estatus']].';">'.$estatus[$row['estatus']].'</td>';
                          echo '<td>
                          <a href="?modulo=facturas&accion=showfactura&factura='.$row['id'].'" id="ver" class="btn btn-sm btn-success">
                            <i class="fas fa-sign-out-alt"></i>
                          </a>';
                          echo '</td>';
                        } else if($row['tipo'] == "INGC"){
                          if($row['estatus'] == "A")echo '<td class="alert text-white" style="background:#3F92FF ;">APLICADO</a></td>';

                          echo '<td>
                          <button class="btn btn-secondary btn-sm" type="button" onclick="imprimirticket('.$row['id'].');" ><i class="fas fa-print"></i></button></td>';
                        }
                  } //// FIN WHILE
                    echo '</tr>
                  </tbody>
                </table>
              </center>
            </div>
           
          </div>
        </div>
      ';
      $arrayResumen = array("A","F","C");
      if(in_array($this->model->estatus,$arrayResumen)){
        echo '<div class="main-card mb-3" >
          <div class="card-body row" >';
            echo '<div class="col-md-12 col-12 col-sm-12">
              <div class="table-responsive">
                <center>
                  <table class="mb-0 table table-hover table-striped" style="width:60%">
                    <thead class="bg-primary bg-darken-2 text-white" style="background: light-blue;">
                      <tr class="bg-primary bg-darken-2 text-white" style="background: light-blue;">
                        <th colspan="6" style="text-align:center">RESUMEN</th>
                      </tr> 
                      <tr>
                        <th width="10%"><b>Cobrado</b></th>
                        <th width="10%"><b>Por cobrar</b></th>
                        <th width="10%"><b>Pagado</b></th>
                        <th width="10%"><b>Por pagar</b></th>
                        <th width="10%"><b>Acreditada</b></th>
                        <th width="10%"><b>Por acreditar</b></th>
                      </tr>
                    </thead>
                    <tbody>';
                      $sumcobrado = 0;
                      $sumpencobrado = 0;
                      $sumpagado = 0;
                      $sumpenpagado = 0;
                      $sumacreditada = 0;
                      $sumpenacreditada = 0;
                      $sqlIngreso = 'SELECT op_id ID, "O" tipo FROM ordenesp WHERE op_proyecto = "'.$this->model->id.'" UNION
                                      SELECT o_id ID, "C" tipo FROM ordenesc WHERE o_tablero = "'.$this->model->id.'" UNION
                                      SELECT g_id ID, "G" tipo FROM gastos WHERE g_tipo = "G" AND g_tablero = "'.$this->model->id.'" UNION
                                      SELECT g_id ID, "V" tipo FROM gastos WHERE g_tipo = "V" AND g_tablero = "'.$this->model->id.'" UNION
                                      SELECT r_id, "R" tipo FROM remisiones WHERE r_tablero = "'.$this->model->id.'"';
                      $resultIngreso = setq($sqlIngreso);
                      while($row = $resultIngreso->fetch_array()){
                        if($row['tipo'] == "R"){
                          $sqlcxc = 'SELECT cx_importe,cx_abonado,cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$row['ID'].'" AND cx_tipo = "R"';
                          $resultcxc = setq($sqlcxc);
                          list($cxcimp, $cxcabo, $cxcest) = $resultcxc->fetch_array();
                          $sumcobrado = $sumcobrado+$cxcabo;
                          if($cxcest != "F") {
                            $sumpencobrado = $sumpencobrado + ($cxcimp - $cxcabo);
                          }
                        }else{
                          $sqlcxc = 'SELECT cp_importe,cp_abonado,cp_estatus FROM cxpagar WHERE cp_idref = "'.$row['ID'].'" AND cp_tipo = "'.$row['tipo'].'"';
                          $resultcxc = setq($sqlcxc);
                          list($cxpimp, $cxpabo, $cxpest) = $resultcxc->fetch_array();
                          $sumpagado = $sumpagado+$cxpabo;
                          if($cxpest != "F") {
                            $sumpenpagado = $sumpenpagado + ($cxpimp - $cxpabo);
                          }
                        }
                      }

                      $sumacreditada = $sumcobrado - $sumpagado;
                      $sumpenacreditada = $sumpencobrado - $sumpenpagado;

                      if($sumacreditada < 0) {
                        $sumacreditada = abs($sumacreditada);
                        $color = 'red';
                      }
                      if($sumpenacreditada < 0) {
                        $sumpenacreditada = abs($sumpenacreditada);
                        $color1 = 'red';
                      }
                      
                      echo'<tr>';
                        echo'<td class="number-align">$ '.number_format($sumcobrado,2).'</td>
                        <td class="number-align">$ '.number_format($sumpencobrado,2).'</td>';

                        echo'<td class="number-align">$ '.number_format($sumpagado,2).'</td>
                        <td class="number-align">$ '.number_format($sumpenpagado,2).'</td>';

                        echo'<td class="number-align" style="color:'.$color.'">$ '.number_format($sumacreditada,2).'</td>
                        <td class="number-align" style="color:'.$color1.'">$ '.number_format($sumpenacreditada,2).'</td>';

                      echo'</tr>';
                      echo' <tr class="bg-primary bg-darken-2 text-white">
                        <th colspan="2" style="text-align:center"><b>Ingresos</b></th>
                        <th colspan="2" style="text-align:center"><b>Egresos</b></th>
                        <th colspan="2" style="text-align:center"><b>Utilidad</b></th>
                      </tr>
                      <tr>
                        <td colspan="2" style="text-align:center">$ '.number_format(($sumpencobrado + $sumcobrado),2).'</td>
                        <td colspan="2" style="text-align:center">$ '.number_format(($sumpenpagado + $sumpagado),2).'</td>
                        <td colspan="2" style="text-align:center">$ '.number_format(($sumpenacreditada + $sumacreditada),2).'</td>
                      </tr>
                    </tbody>
                  </table>
                </center>
              </div>
            </div>
          </div>
        </div>';
      }
    }
  }
?>