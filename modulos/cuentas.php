<?php
  //ini_set('display_errors',1);
  class cuentas {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelcuentas(isset($obj));
    }
    function index() {
      if(!isset($_GET['page']))    $_GET['page'] = 1;
      if(!isset($_REQUEST['estatus']))    $_REQUEST['estatus'] = NULL;
      if(!isset($_POST['nombre']))    $_POST['nombre'] = NULL;
      if(!isset($_POST['propietario']))    $_POST['propietario'] = NULL;

      $this->model->result($_GET['page'],$_REQUEST['estatus'],$_POST['nombre'],$_POST['propietario']);
      $this->view = new viewcuentas($this->model);
      $this->view->browse($_GET['page'],$_REQUEST['estatus'],$_POST['nombre'],$_POST['propietario']);
    }
    function prueba() {
      if(!isset($_GET['page']))    $_GET['page'] = 1;
      if(!isset($_REQUEST['estatus']))    $_REQUEST['estatus'] = NULL;
      if(!isset($_POST['nombre']))    $_POST['nombre'] = NULL;
      if(!isset($_POST['propietario']))    $_POST['propietario'] = NULL;

      $this->model->result($_GET['page'],$_REQUEST['estatus'],$_POST['nombre'],$_POST['propietario']);
      $this->view = new viewcuentas($this->model);
      $this->view->prueba($_GET['page'],$_REQUEST['estatus'],$_POST['nombre'],$_POST['propietario']);
    }
    function edit(){
      if (!isset($_GET['id'])) $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new viewcuentas($this->model);
      $this->view->edit();
    }
    function show(){
      if (!isset($_GET['id'])) $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new viewcuentas($this->model);
        $this->view->show();
    }
    function cortes(){
      if (!isset($_GET['id'])) $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new viewcuentas($this->model);
      $this->view->cortes();
    }
    function datosliquidez(){
      //die(var_dump($_POST['liquido']));
      if(!empty($_POST['liquido'])){
        foreach ($_POST['liquido'] as $clave => $valor) {
          $sql = 'UPDATE proyeccionesd SET
                  pd_liquidacion = "'.$valor.'"
                  WHERE pd_id = "'.$clave.'"';
          setq($sql);
        }
      }
      redirect('?modulo=cuentas&accion=showliquidez&id='.$_GET['id']);
    }
    function borrarvalores(){
      $sql = 'SELECT * FROM proyeccionesd WHERE pd_proyeccion = "'.$_GET['id'].'"';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $sqlup = 'UPDATE proyeccionesd SET
                  pd_liquidacion = "0.00"
                  WHERE pd_id = "'.$row['pd_id'].'"';
        setq($sqlup);
      }
      redirect('?modulo=cuentas&accion=showliquidez&id='.$_GET['id']);
    }
    function cerrarproyeccion(){
      $sql = 'UPDATE proyecciones SET
              p_estatus = "F",
              p_fechacierre = "'.date('Y-m-d').'"
              WHERE p_id = "'.$_GET['id'].'"';
      setq($sql);
      redirect('?modulo=cuentas&accion=proyecciones');
    }
    function disable() {
      $saldo = saldo_actual($_GET['id']);
      if($saldo > 0){
        $link = '';
        echo '<script>
          alert("ERROR: La cuenta no se puede cancelar si el saldo es diferente de 0.");
          window.location.href="?modulo=cuentas&accion=show&id='.$_GET['id'].'";
        </script>';
      }else{
        $this->model->disable($_GET['id']);
        $link = '?modulo=cuentas&accion=show&id='.$_GET['id'];
      }
      redirect($link);
    }
    function enable() {
      $this->model->enable($_GET['id']);
      redirect('?modulo=cuentas&accion=show&id='.$_GET['id']);
    }
    function updateord(){
      $this->model->updateord($_GET['id'],$_POST['orden']);
      redirect('?modulo=cuentas&accion=index');
    }
    function insert() {
      $this->id = getmax('cu_id','cuentas');
      $this->orden = getmax('cu_orden','cuentas');
      if(isset($_POST['enviacotiza'])) $enviacotiza = "1"; else $enviacotiza = "0";
      if(isset($_POST['caja'])) $caja = "1"; else $caja = "0";

      if($caja == "1"){
        $sql = 'UPDATE cuentas SET cu_caja = "0"';
        setq($sql);
      }

      $this->model->setData($this->id, $_POST['nmb'],$_POST['propietario'],$_POST['tipo'],0,$_POST['limite'],$this->orden,$_POST['cuenta'],$_POST['clabe'],$_POST['banco'],$_POST['numtarjeta'],$enviacotiza,$caja);
      $this->model->insert();

      /*$fecha=date('Y-m-d');
      $nuevafecha = strtotime ( 'last day of this month' , strtotime ( $fecha ) ) ;
      $nuevafecha = date ( 'Y-m-d' , $nuevafecha );

      $sql = 'SELECT MAX(c_id) FROM cortes';
      $result = setq($sql);
      list($maxid) = mysql_fetch_array($result);
      $maxid;

      $saldo=saldo_actual($this->id);
      $this->model->actualizarsaldo($this->id,$saldo);
      $this->model->actualizarp($this->id);
      $this->model->setDatap($maxid, $fecha,$nuevafecha,$this->id);
      $this->model->insertp();  */

      redirect('?modulo=cuentas&accion=show&id='.$this->id);
    }
    function update() {
      if(isset($_POST['enviacotiza'])) $enviacotiza = "1"; else $enviacotiza = "0";
      if(isset($_POST['caja'])) $caja = "1"; else $caja = "0";

      if($caja == "1"){
        $sql = 'UPDATE cuentas SET cu_caja = "0"';
        setq($sql);
      }

      $this->model->setData($_GET['id'], $_POST['nmb'],$_POST['propietario'],$_POST['tipo'],0,$_POST['limite'],NULL,$_POST['cuenta'],$_POST['clabe'],$_POST['banco'],$_POST['numtarjeta'],$enviacotiza,$caja);
      $this->model->update();

      redirect('?modulo=cuentas&accion=show&id='.$_GET['id']);
    }
    function insertp() {
      if(isset($_POST['cuenta'])) $_GET['id'] = $_POST['cuenta'];
      //die($_POST['fini'].' - '.$_POST['ffin'].' - '.$_GET['id']);
      //VALIDA QUE FECHAS NO ESTEN EMPATADAS
      if($_POST['fini']>=$_POST['ffin'])
        die(alert_back('La fecha final tiene que ser posterior a la fecha inicial',true));

      $error=0; $caderror="ERROR, ";
      $validacion1=busca($_GET['id'],'cortes','c_fini <= "'.$_POST['fini'].'" AND c_ffin >= "'.$_POST['fini'].'" AND c_cuenta','c_id');
      $validacion2=busca($_GET['id'],'cortes','c_ffin <= "'.$_POST['ffin'].'" AND c_ffin >= "'.$_POST['ffin'].'" AND c_cuenta','c_id');
      if(isset($validacion1)){
        $error=1;
        $caderror.=" La fecha inicial esta empatada con alguna de los periodos dados de alta.";
      }
      if(isset($validacion2)){
        $error=1;
        $caderror.=" La fecha final esta empatada con alguna de los periodos dados de alta.";
      }
      if($error==1){
          $caderror.=" Por favor revise los periodos dados de alta y/o cambie las fechas insertadas";
          die(alert_back($caderror,true));
      }

      //$activa = busca($_GET['id'],'cortes','c_estatus="A" AND c_cuenta','c_id');
      $activa = busca($_GET['id'],'cortes','c_cuenta','MAX(c_id)');
      //die($activa);
      if($activa){
          $saldocorte=saldo_actual($_GET['id']);
          $sql = 'UPDATE cortes SET c_saldo = "'.$saldocorte.'" WHERE c_id="'.$activa.'"';
          //setq($sql) or die($sql);
      } else{
          //$saldocorte=busca($_GET['id'],'cuentas','cu_id','cu_saldo');
          $saldocorte = $_POST['sinicial'];
      }
      //echo $activa.' - '.$saldocorte;
      //die();
      $sql = 'SELECT MAX(c_id) FROM cortes';
      $result = setq($sql);
      list($this->id) = $result->fetch_array();
      $this->id++;

      $saldo=saldo_actual($_GET['id']);
      $this->model->actualizarsaldo($_GET['id'],$saldo);
      $this->model->actualizarp($_GET['id']);
      $this->model->setDatap($this->id, $_POST['fini'],$_POST['ffin'],$_GET['id'],$saldocorte);
      $this->model->insertp();

      redirect('?modulo=cuentas&accion=cortes&id='.$_GET['id']);
    }
    function inserttraspaso(){
      $corteo = busca($_POST['origen'],'cortes','c_estatus = "A" AND c_cuenta','c_id');
      $corted = busca($_POST['destino'],'cortes','c_estatus = "A" AND c_cuenta','c_id');

      if($corteo && $corted){
        $error = $this->model->inserttraspaso(clearvmayus($_POST['observaciones']),$_POST['importe'],$corteo,$_POST['origen'],$corted,$_POST['destino']);
        $redir = '?modulo=cuentas&accion=show&id='.$_POST['destino'];
      }

      else
        $redir = '?modulo=cuentas&accion=index&errror=NOCORTE';

      redirect($redir);
    }
    function validacorte(){
      $this->model->validacorte("1","1");
    }
    function proyecciones(){
      if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-60 days'));
      if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('+15 days'));
      if (!isset($_REQUEST['nmb'])) $_POST['nmb'] = NULL;

      $this->model->resultproyecciones($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['nmb']);
      $this->view = new viewcuentas($this->model);
      $this->view->proyecciones($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['nmb']);
    }
    function insertproyeccion(){
      $this->model->insertproyeccion($_POST['nmb'],$_POST['fechaap'],$_POST['fechacierre']);

      redirect('?modulo=cuentas&accion=showproyeccion&id='.$this->model->id);
    }
    function updateproyeccion(){
      $this->model->updateproyeccion($_GET['id'],$_POST['fechacierre']);
      redirect('?modulo=cuentas&accion=proyecciones');
    }
    function showproyeccion(){
      $this->model->selectproyeccion($_GET['id']);
      $this->view = new viewcuentas($this->model);
      $this->view->showproyeccion();
    }
    function showliquidez(){
      $this->model->selectproyeccion($_GET['id']);
      $this->view = new viewcuentas($this->model);
      $this->view->showliquidez();
    }
    function showproyeccionresumen(){
      $this->model->selectproyeccion($_GET['id']);
      $this->view = new viewcuentas($this->model);
      $this->view->showresumen();
    }
    function setproyecciond(){

      if($_GET['from'] == "cxcobrar"){
        $sql = 'SELECT cx_id id,cx_cliente cliente,cx_referencia ref,cx_tipo tipo,(cx_importe-cx_abonado) importe,cx_observaciones observaciones,cx_fini fini
                FROM cxcobrar
                WHERE cx_estatus IN ("A","N") AND cx_tipo != "C" ORDER BY cx_fini ASC';
        $tipop = "C";
      }
      elseif($_GET['from'] == "cxpagar"){
        $sql = 'SELECT cp_id id,cp_proveedor cliente,cp_idref ref,cp_tipo tipo,(cp_importe-cp_abonado) importe,cp_observaciones observaciones,cp_fini fini
                FROM cxpagar
                WHERE cp_estatus IN ("A","N") ORDER BY cp_fini ASC';
        $tipop = "P";
      }
      $resultado = setq($sql);
      $i = 0;
      $importe = 0;
      while($row = $resultado->fetch_array()){
        if(isset($_POST['cuenta'.$row['id']]))
          $this->model->setproyecciond($_GET['proyeccion'],$row['fini'], $tipop,$_POST['nmb'.$row['id']],$_POST['original'.$row['id']],$_POST['monto'.$row['id']],$row['id']);
        else
          $this->model->delproyecciond(NULL,$_GET['proyeccion'],$tipop,$row['id']);
      }
        echo '<script>
              window.opener.location.reload();
              window.opener.location.href="?modulo=cuentas&accion=showproyeccion&id='.$_GET['proyeccion'].'";
              window.close();
            </script>';
    }
    function delproyecciond(){
      $this->model->delproyecciond($_GET['idp']);

      redirect('?modulo=cuentas&accion=showproyeccion&id='.$_GET['proyeccion']);
    }
    function showcorte(){
      $this->model->select($_GET['cuenta']);
      $this->view = new viewcuentas($this->model);
      $this->view->showcorte();
    }
  }
  /* -----------------------------------------------MODEL---------------------------------------------------------------------- */
  class modelcuentas {
    function setdata($id,$nomcta,$nomtitular,$tipo,$saldoini,$limite,$orden,$cuenta,$clabe,$banco,$numtarjeta,$enviacotiza, $caja) {
      mb_internal_encoding("UTF-8");
      $this->id = mb_strtoupper($id);
      $this->nomcta = mb_strtoupper($nomcta);
      $this->nomtitular = mb_strtoupper($nomtitular);
      $this->tipo = mb_strtoupper($tipo);
      $this->saldoini = mb_strtoupper($saldoini);
      $this->limite = $limite;
      $this->orden = $orden;
      $this->cuenta = $cuenta;
      $this->clabe = $clabe;
      $this->banco = $banco;
      $this->numtarjeta = $numtarjeta;
      $this->enviacotiza = $enviacotiza;
      $this->caja = $caja;
    }
    function result($page,$estatus,$nombre,$propietario){
      $page--;
      $pageresult = 20;
      $pag = ' LIMIT '.($page * $pageresult).','.$pageresult;
      $sqlf = ' WHERE cu_id!=0';
      if($estatus){
        $sqlf .= ' AND cu_estatus = "'.$estatus.'" ';
      }
      if($nombre){
        $sqlf .= ' AND cu_nmb = "'.$nombre.'" ';
      }
      if($propietario){
        $sqlf .= ' AND cu_propietario LIKE "%'.$propietario.'%" ';
      }

      $sql = 'SELECT * FROM cuentas '.$sqlf.' ORDER BY cu_orden ASC, cu_id ASC'.$pag;
      $this->result = setq($sql) or die($sql);
      $this->resultci = setq($sql) or die($sql);

      $sql = 'SELECT COUNT(*) FROM cuentas '.$sqlf.' ORDER BY cu_orden ASC, cu_id ASC';
      $this->resultt = setq($sql) or die($sql);
      list($nr) = $this->resultt->fetch_array();
      $this->np = $nr/$pageresult;
    }
    function cuentaactiva($cuenta){
      $sql = 'SELECT COUNT(*) FROM cortes
              WHERE c_cuenta = "'.$cuenta.'" AND c_fini <= "'.date('Y-m-d').'" AND c_ffin >= "'.date('Y-m-d').'" AND c_estatus = "A"';
      $result = setq($sql);
      list($numc) = $result->fetch_array();
      if($numc == 0){
        $ok = "NOTOK"; 
      }
      else{
        $sql = 'SELECT c_id FROM cortes
                WHERE c_cuenta = "'.$cuenta.'" AND c_fini <= "'.date('Y-m-d').'" AND c_ffin >= "'.date('Y-m-d').'" AND c_estatus = "A"';
        $result = setq($sql) or die($sql);
        list($ok) = $result->fetch_array();
      }
      return($ok);
    }
    function setdatap($id,$fini,$ffin,$cuenta,$saldo) {
      mb_internal_encoding("UTF-8");
      $this->id = $id;
      $this->fini = $fini;
      $this->ffin = $ffin;
      $this->cuenta = $cuenta;
      $this->saldo = $saldo;
    }
    function select($id) {
      $sql = 'SELECT * FROM cuentas where cu_id="'.$id.'"';
      $result = setq($sql) or die($sql);
      $row = $result->fetch_array();
      $this->id = $row['cu_id'];
      $this->nmb = $row['cu_nmb'];
      $this->propietario = $row['cu_propietario'];
      $this->saldo = $row['cu_saldo'];
      $this->estatus = $row['cu_estatus'];
      $this->tipo = $row['cu_tipo'];
      $this->cuenta = $row['cu_cuenta'];
      $this->clabe = $row['cu_clabe'];
      $this->banco = $row['cu_banco'];
      $this->limite = $row['cu_limite'];
      $this->numtarjeta= $row['cu_numtarjeta'];
      $this->enviacotiza= $row['cu_enviacotiza'];
      $this->caja = $row['cu_caja'];
    }
    function insert() {
      if(busca(clearvmayus($this->nomcta),'cuentas',' cu_nmb','COUNT(*)') > 0)
        die(alert_back("El Nombre de la Cuenta ya esta ocupado, intentelo con otro",true));

      $sql = 'INSERT INTO cuentas SET
              cu_id=  "'.$this->id.'",
              cu_nmb =  "'.$this->nomcta.'",
              cu_propietario =  "'.$this->nomtitular.'",
              cu_tipo =  "'.$this->tipo.'",
              cu_estatus = "A",
              cu_saldo =  "'.$this->saldoini.'",
              cu_orden =  "'.$this->orden.'",
              cu_banco =  "'.$this->banco.'",
              cu_cuenta =  "'.$this->cuenta.'",
              cu_clabe =  "'.$this->clabe.'",
              cu_enviacotiza =  "'.$this->enviacotiza.'",
              cu_numtarjeta =  "'.$this->numtarjeta.'",
              cu_caja =  "'.$this->caja.'",
              cu_limite =  "' .$this->limite.'"';
      //DIE($sql);
      setq($sql) or die(mysql_error() .'<br>'. $sql);
    }
    function insertp() {
      if(busca(clearvmayus($this->nomcta),'cuentas','cu_nmb','COUNT(*)') > 0)
        die(alert_back("El Nombre de la Cuenta ya esta ocupado, intentelo con otro",true));

      $sql = 'INSERT INTO cortes SET
            c_id=  "'.$this->id.'",
            c_fini =  "'.$this->fini.'",
            c_ffin =  "'.$this->ffin.'",
            c_cuenta =  "'.$this->cuenta.'",
            c_estatus = "A",
            c_saldo = "'.$this->saldo.'"';
      setq($sql);
    }
    function actualizarp($id) {
      $sql = 'UPDATE cortes SET c_estatus = "I" WHERE c_cuenta="'.$id.'"';
      setq($sql) or die($sql);
    }
    function actualizarsaldo($id,$saldo) {
      $sql = 'UPDATE cuentas SET cu_saldo = "'.$saldo.'" WHERE cu_id="'.$id.'"';
      setq($sql) or die($sql);
    }
    function update() {
      if(busca($this->nomcta,'cuentas','cu_id != "'.$this->id.'" AND cu_nmb','COUNT(*)') > 0)
        die(alert_back("El Nombre de la Cuenta ya esta ocupado, intentelo con otro",true));

      $sql = 'UPDATE cuentas SET
            cu_nmb =  "'.$this->nomcta.'",
            cu_propietario =  "'.$this->nomtitular.'",
            cu_tipo =  "'.$this->tipo.'",
            cu_banco =  "'.$this->banco.'",
            cu_cuenta =  "'.$this->cuenta.'",
            cu_clabe =  "'.$this->clabe.'",
            cu_numtarjeta =  "'.$this->numtarjeta.'",
            cu_enviacotiza =  "'.$this->enviacotiza.'",
            cu_limite =  "' .$this->limite.'",
            cu_caja =  "'.$this->caja.'"
            WHERE cu_id= "'.$this->id.'"';
      setq($sql) or die(mysql_error() .'<br>'. $sql);
    }
    function disable($id) {
      $sql = 'UPDATE cuentas SET cu_estatus = "I" WHERE cu_id="'.$id.'"';
      setq($sql) or die($sql);
    }
    function enable($id) {
      $sql = 'UPDATE cuentas SET cu_estatus = "A" WHERE cu_id="'.$id.'"';
      setq($sql) or die($sql);
    }
    function updateord($id,$orden){
      $sql = 'SELECT * FROM cuentas WHERE cu_orden >= '.$orden.' ';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $sql = 'UPDATE cuentas SET
                cu_orden = (cu_orden+1)
                WHERE cu_id = "'.$row['cu_id'].'"';
        setq($sql);
      }

      $sql = 'UPDATE  cuentas SET cu_orden = "'.$orden.'" WHERE cu_id = "'.$id.'"';
      setq($sql) or die($sql);
      $sql = 'SELECT * FROM cuentas ORDER BY cu_orden ASC';
      $result = setq($sql);
      $contador = 1;
      while($row = $result->fetch_array()){
        $sqlup = 'UPDATE cuentas SET
                  cu_orden = "'.$contador.'"
                  WHERE cu_id = "'.$row['cu_id'].'"';
        setq($sqlup);
        $contador++;
      }
    }
    //Funciones sobre cortesd
    function setdatacd($id,$corte,$fecha,$gen,$tipo,$ref,$monto,$tipom,$cuenta){
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
    function insertcd() {
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
      setq($sql);
    }
    function asignaricxc($ingreso,$cxcobrar,$importe, $from = ""){
      
      $return = "OK";

      $sqlcxc = 'SELECT cx_cliente,cx_importe,cx_abonado,cx_estatus,cx_referencia,cx_tipo
                FROM cxcobrar WHERE cx_id = "'.$cxcobrar.'"';
      $resultcxc = setq($sqlcxc);
      list($cxcliente,$cximporte,$cxabonado,$cxestatus,$cxref,$cxtipo) = $resultcxc->fetch_array();

      $sql='SELECT i_monto,i_cliente,i_cuenta,i_corte FROM ingresos WHERE i_id="'.$ingreso.'"';
      $result=setq($sql) or die($sql);
      list($monto,$cliente,$cuenta,$corte)=$result->fetch_array();
      $fecha=date('Y-m-d H:i:s');
      $user=$_SESSION['uid'];

      $asignado=busca($ingreso,'ingreso_cxcobrar','ic_ingreso','SUM(ic_monto)');
      if($asignado > $ingreso) $return = "8XMI01";
      else{
        $sqlm = 'SELECT MAX(ic_id) FROM ingreso_cxcobrar WHERE ic_ingreso="'.$ingreso.'" ';
        $resultm = setq($sqlm) or die($sqlm);
        list($id) = $resultm->fetch_array();
        $id++;

        $sqli='INSERT INTO ingreso_cxcobrar SET
            ic_id="'.$id.'",
            ic_ingreso="'.$ingreso.'",
            ic_cxcobrar="'.$cxcobrar.'",
            ic_monto="'.$importe.'",
            ic_fapli="'.date('Y-m-d H:i:s').'",
            ic_uapli="'.$_SESSION['uid'].'";';
        setq($sqli);
      }
      $totalcx = $cxabonado+$importe;
      $totalig = $asignado+$importe;

      if($from != "C"){
        $sql = 'UPDATE cxcobrar SET cx_abonado = cx_abonado+'.$importe.' WHERE cx_id = "'.$cxcobrar.'"';
        setq($sql);
        if($totalcx >= $cximporte){
          $sqli = 'UPDATE cxcobrar SET cx_estatus = "F", cx_ffin ="'.$fecha.'"
                  WHERE cx_id="'.$cxcobrar.'"';

          if($cxtipo == "R"){
            $sql3 = 'UPDATE remisiones SET
                      r_fliquidacion = "'.date('Y-m-d H:i:s').'",
                      r_uaplica = "'.$_SESSION['uid'].'"
                    WHERE r_id="' . $cxref. '"';
            setq($sql3);
          }
        }
        else 
          $sqli = 'UPDATE cxcobrar SET cx_estatus = "A", cx_ffin ="'.$fecha.'"
                  WHERE cx_id="'.$cxcobrar.'"';
        setq($sqli) or die($sqli);
      }

      if($from == "C") $estatus= "P";
      else $estatus = "F";

      if($totalig >= $monto){
          $sqli = 'UPDATE ingresos SET i_estatus = "'.$estatus.'" WHERE i_id="'.$ingreso.'"';
          setq($sqli) or die($sqli);
      }
    }
    function desasignarcxc($referencia,$tipo){
      $sqlcxc = 'SELECT cx_id FROM cxcobrar WHERE cx_tipo = "'.$tipo.'" AND cx_referencia = "'.$referencia.'"';
      $resultcxc = setq($sqlcxc);
      while($rowcx = $resultcxc->fetch_array()){
        $sql = 'SELECT * FROM ingreso_cxcobrar WHERE ic_cxcobrar="'.$rowcx['cx_id'].'"';
        $result = setq($sql);
        while($row = $result->fetch_array()){
          $sqld = 'DELETE FROM ingreso_cxcobrar WHERE ic_cxcobrar="'.$rowcx['cx_id'].'" AND ic_ingreso = "'.$row['ic_ingreso'].'"';
          setq($sqld);

          $squi = 'UPDATE ingresos SET i_estatus = "A" WHERE i_id = "'.$row['ic_ingreso'].'"';
          setq($squi);
        }

        $sqlai = 'SELECT SUM(ic_monto) FROM ingreso_cxcobrar WHERE ic_cxcobrar="'.$rowcx['cx_id'].'"';
        $resultai = setq($sqlai);
        list($sumai) = $resultai->fetch_array();
        if(!$sumai) $sumai = 0;

        $sqlucx = 'UPDATE cxcobrar SET cx_abonado = "'.$sumai.'", cx_estatus = "A"
                  WHERE cx_tipo = "'.$tipo.'" AND cx_referencia = "'.$referencia.'"';
        setq($sqlucx);
      }
    }
    function inserttraspaso($observaciones,$importe,$corteo,$origen,$corted,$destino){
      $sql = 'INSERT INTO traspasos SET
              t_descripcion = "'.$observaciones.'",
              t_importe = "'.$importe.'",
              t_origencorte = "'.$corteo.'",
              t_origen = "'.$origen.'",
              t_destinocorte = "'.$corted.'",
              t_destino = "'.$destino.'",
              t_fgen = "'.date('Y-m-d H:i:s').'",
              t_ugen = "'.$_SESSION['uid'].'"';
      setq($sql);

      $idtr = getmax('t_id','traspasos',false,false);
    }
    function validacorte($ffin,$automatico = "1"){
      $sql = 'SELECT * FROM cortes INNER JOIN cuentas ON cu_id = c_cuenta WHERE c_ffin < "'.date('Y-m-d').'"';
      $result = setq($sql);
      if($result->num_rows > 0){
        while($row = $result->fetch_array()){
          $sql2 = 'UPDATE cortes SET c_estatus = "I" WHERE c_id = "'.$row['c_id'].'"';
          setq($sql2);
        }
      }
      redirect('?modulo=cuentas&accion=index');
    } 
    function selectproyeccion($id){
      $sql = 'SELECT * FROM proyecciones WHERE p_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->idp = $row['p_id'];
      $this->nmb = $row['p_nmb'];
      $this->fechaap = $row['p_fechaap'];
      $this->usuario = $row['p_usuario'];
      $this->estatus = $row['p_estatus'];
    }
    function resultproyecciones($fini,$ffin,$nmb){
      $sql = 'SELECT * FROM proyecciones WHERE p_fechaap BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      if($nmb) $sql.=' AND p_nmb LIKE "%'.$nmb.'%" ';
      $sql.=' ORDER BY p_fechaap DESC';
      $this->result = setq($sql);
    }
    function insertproyeccion($nmb,$fechaap,$fechacierre){
      $sql = 'INSERT INTO proyecciones SET
              p_nmb = "'.clearvmayus($nmb).'",
              p_fechaap = "'.$fechaap.'",
              p_fechacierre = "'.$fechacierre.'",
              p_usuario = "'.$_SESSION['uid'].'"';
      setq($sql);

      $this->id = getmax('p_id','proyecciones',false,false);
    }
    function updateproyeccion($id,$fechacierre){
      $sql = 'UPDATE proyecciones SET
            p_fechacierre = "'.$fechacierre.'"
            WHERE p_id = "'.$id.'"';
      setq($sql);
    }
    function setproyecciond($proyeccion,$fecha, $tipop,$nmb,$totalc,$monto,$referencia){
      $sql = 'INSERT INTO proyeccionesd SET
              pd_proyeccion = "'.$proyeccion.'",
              pd_fecha = "'.$fecha.'",
              pd_tipo = "'.$tipop.'",
              pd_nmb = "'.$nmb.'",
              pd_totalc = "'.$totalc.'",
              pd_importe = "'.$monto.'",
              pd_fagregada = "'.date("Y-m-d H:i:s").'",
              pd_referencia = "'.$referencia.'",
              pd_uregistra = "'.$_SESSION['uid'].'"';
      setq($sql);
    }
    function delproyecciond($idpd,$proyeccion=NULL,$tipop=NULL,$referencia=NULL){
      if($idpd) $sqlpd = 'DELETE FROM proyeccionesd WHERE pd_id = "'.$idpd.'"';
      else $sqlpd = 'DELETE FROM proyeccionesd
                    WHERE pd_proyeccion = "'.$proyeccion.'" AND pd_tipo = "'.$tipop.'" AND pd_referencia = "'.$referencia.'" ';
      setq($sqlpd);
    }
  }
  /* -----------------------------------------------VIEW---------------------------------------------------------------------- */
  class viewcuentas {
    var $model;
    function __construct($model) {
      $this->tipocta = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
      $this->model = $model;
    }
    function browse($page,$estatus,$nombre,$propietario) {
      $e1 = ''; $e2 = ''; $est = '';
      if($estatus) $est = '&estatus='.$estatus;
      if($estatus == "A") $e1 = "selected";
      if($estatus == "I") $e2 = "selected";
      ?><script>
        $('body').on("keydown", function(e) { 
          if (e.altKey && e.which === 78) {
            var btn = document.getElementById('nuevo');
            btn.click();
            e.preventDefault();
          }
        });
        $('body').on("keydown", function(e){ 
          if (e.altKey && e.which === 82) {
            window.location.reload();
          }
        });
      </script>
      <?php
      $botones = '';
      $botones .= '<a  id="nuevo" data-fancybox data-type="ajax" data-src="popup/setcuenta.php?rand='.rand(1,1000).' " href="javascript:;" >
          <button type="button" class="btn btn-primary">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
          </button>
        </a>';
      $botones .= '<a data-fancybox data-type="ajax" data-src="popup/settraspaso.php?rand='.rand().'" href="javascript:;" >
          <button type="button" class="btn bg-teal bg-darken-3 text-white" style="background: teal;">
            <span class="glyphicon glyphicon-cog"></span><i class="fas fa-exchange-alt" style="color: #ffffff;"></i> Traspaso
          </button>
        </a>';
        $botones .='<a href="?modulo=cuentas&accion=proyecciones">
          <button type="button" class="btn btn-info"><i class="fas fa-dollar-sign" style="color: #ffffff;"></i> Ver proyecciones </button>
        </a>';
        $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setordenp.php?rand='.rand().'" href="javascript:;" >
          <button type="button" class="btn btn-success text-white">
            <span class="glyphicon glyphicon-cog"></span><i class="fas fa-user-secret" style="color: #ffffff;"></i> Prestamo
          </button>
        </a>';
        toolbar($_GET['modulo'], '', '', $botones);
        
        echo '<!--<a href="?modulo=cuentas&accion=validacorte" class="btn btn-blue">Valida corte</a>-->
      <div class="main-card mb-2 card mt-2">
        <div class="card-body">
          <div class="table-responsive text-medium">
            <center>
            <table class="table table-hover table-striped" id="myTable">
                <thead class="thead pt-1 pb-1">
                  <tr>
                    <th width="5%"></th>
                    <th width="20%">Nombre de la Cta.</th>
                    <th width="15%">Saldo</th>
                    <th width="10%">Limite</th>
                    <th width="15%">Tipo de Cta.</th>
                    <th width="15%">Propietario</th>
                    <th width="10%">Corte Activo</th>
                    <th width="5%">Orden</th>
                    <th width="5%">Editar</th>
                  </tr>
                </thead>
                <tbody> ';
                ?>  
                <?php
                  $totals = 0;
                  while ($row = $this->model->result->fetch_array()) {
                    $corte=busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_fini').' - '.busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_ffin');
                    if(!$corte) $corte="No existe corte";
                    $saldo =saldo_actual($row['cu_id']);
                    $totals+=$saldo;
                    $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
                    if($grupo != "FINANZAS" && $grupo != "ADMIN"){
                      $saldo = "0.00";
                      $totals = "0.00";
                    }
                    echo '<tr>
                            <th>
                            <div class="form-inline">
                              <a href="?modulo=cuentas&accion=show&id='.$row['cu_id'].'">
                                <button class="btn btn-sm btn-primary"><i class="fas fa-eye" style="color: #ffffff;"></i></button>
                              </a>
                            </div>
                            </th>
                            <td>' . $row['cu_nmb']. '</td>
                            <td class="number-align d-3">$'.number_format($saldo,2).'</td>
                            <td class="number-align d-3">$ ' . $row['cu_limite'] . '</td>
                            <td>' .$this->tipocta[$row['cu_tipo']]. '</td>
                            <td>' . $row['cu_propietario']. '</td>
                            <td>'.$corte.'</td>';
                    echo '<form action="?modulo=cuentas&accion=updateord&id='.$row['cu_id'].'" method="post" onsubmit="return checkSubmitenviar();">
                              <td><input type="number" class="form-control" onchange="submit();" name="orden" value="'.$row['cu_orden'].'" /></td>
                        </form>
                        <td>
                          <a data-fancybox data-type="ajax" data-src="popup/setcuenta.php?id='.$row['cu_id'].'" href="javascript:;">
                            <button type="button" class="btn btn-sm btn-info"><i class="fas fa-pen" style="color: #ffffff;"></i></button>
                          </a>
                        </td>';

                    echo '</tr></center>';
                  }
                  echo '<tbody>
                  <tfoot>';
                  echo '<tr><td colspan="2">&nbsp;</td><th>Total</th><td>$ '.number_format($totals,2).'</td><td></td><td></td><td></td><td></td><td></td></tr>';
                  echo '</tfoot></table></center>';
          echo '
          <script>
            function mandar(id){
              document.getElementById("page").value = id;
              document.getElementById("filtro").submit();
            }
          </script>
          
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
                  pageLength:50,
              });
            });
          </script>';
      echo'</div>';
      if($this->model->resultci->num_rows > 0){
        $sqlcu = 'SELECT DISTINCT(cu_id), cu_nmb FROM cortes INNER JOIN cuentas ON cu_id = c_cuenta 
                  WHERE c_estatus = "I" AND c_cuenta NOT IN 
                  (SELECT c_cuenta FROM cortes WHERE c_estatus = "A") ORDER BY c_ffin DESC';
        $resultcu = setq($sqlcu);
        if($resultcu->num_rows > 0){
          echo '<div class="row page-title-actions mt-2 mb-1">
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-target="#exampleModal">
              <i class="icon-inbox"></i> Nuevo Corte
            </button>
            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Nuevo Corte</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <form class="" action="?modulo=cuentas&accion=insertp" method="POST">
                    <div class="modal-body">
                      <div class="row p-1"> 
                        <div class="mb-5 col-md-10">
                          <label>Cuenta: </label>
                          <select id="cuenta" name="cuenta" class="form-control" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="Selecciona la cuenta para generar corte" data-original-title="" title="">';
                            while($rowcu = $resultcu->fetch_array()){
                              echo'<option value="'.$rowcu['cu_id'].'">'.$rowcu['cu_nmb'].'</option> ';
                            }
                          echo '</select>
                        </div>
                        <div class="mb-5 col-md-6">
                          <label>Fecha de inicio de corte: </label>
                          <input class="form-control" type="date" min="'.date('Y-m-d').'" max="'.date('Y-m-d').'" value="'.date('Y-m-d').'" name="fini">
                        </div>
                        <div class="mb-5 col-md-6">
                          <label>Fecha de final de corte: </label>
                          <input class="form-control" type="date" min="'.date('Y-m-d').'" value="'.date('Y-m-d', strtotime('last day of this month')).'" name="ffin">
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                      <button type="submit" class="btn btn-primary">Generar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>';
          //}
          ?><div class="main-card mb-2 card">
            <div class="card-body">
              <div class="btn btn-block" style="color: black!important;display: flex;justify-content: space-between;text-align:left;padding: 0rem;" onclick="mostrarsn();">
                <div class="col-md-11z">
                  <h2>Cuentas sin corte activo</h2>
                </div>
                <div class="col-md-1" style="display: flex;position: inherit;align-items: center;">
                  <i class="icon icon-chevron-down2" style="font-size: x-large;" id="cv1"></i>
                  <input type="hidden" id="vlch" value="0">
                </div>
              </div>
              <hr style="background: black;margin-top: 0rem;margin-bottom: 0.5rem;">
              <div class="table-responsive text-medium" id="tablasincorte" style="display:none">
                <center>
                  <table width="100%" border="0"  cellpadding="0" cellspacing="0" class="table table-hover table-striped" id="myTable">
                    <thead class="thead bg-primary text-white">
                      <tr>
                        <th width="5%"><strong></strong></th>
                        <th width="20%"><strong>Nombre de la Cta.</strong></th>
                        <th width="15%"><strong>Saldo</strong></th>
                        <th width="10%"><strong>Limite</strong></th>
                        <th width="15%"><strong>Tipo de Cta.</strong></th>
                        <th width="15%"><strong>Propietario</strong></th>
                        <th width="10%"><strong>Corte Activo</strong></th>
                        <th width="5%"><strong>Orden</strong></th>
                        <th width="5%"><strong>Editar</strong></th>
                      </tr>
                    </thead>
                    <tbody><?php
                      $totals = 0;
                      while ($row = $this->model->resultci->fetch_array()) {
                        $finic = busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_fini');
                        $ffinc = busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_ffin');
                        if(!$finic && !$ffinc) {
                          $corte = "No existe corte";
                          $saldo = 0;
                          echo '<tr>
                            <th>
                              <div class="form-inline">
                                <a href="?modulo=cuentas&accion=show&id='.$row['cu_id'].'">
                                  <button class="btn btn-primary"><i class="fa fa-eye"></i></button>
                                </a>
                              </div>
                            </th>
                            <td nowrap>'.$row['cu_nmb'].'</td>
                            <td nowrap class="number-align d-3">$'.number_format($saldo,2).'</td>
                            <td nowrap class="number-align d-3">$'. $row['cu_limite'].'</td>
                            <td nowrap>'.$this->tipocta[$row['cu_tipo']].'</td>
                            <td nowrap>'.$row['cu_propietario'].'</td>
                            <td nowrap>'.$corte.'</td>';
                            echo '<form action="?modulo=cuentas&accion=updateord&id='.$row['cu_id'].'" method="post" onsubmit="return checkSubmitenviar();">
                              <td><input type="number" class="form-control" onchange="submit();" name="orden" value="'.$row['cu_orden'].'" /></td>
                            </form>
                            <td>
                              <a data-fancybox data-type="ajax" data-src="popup/setcuenta.php?id='.$row['cu_id'].'" href="javascript:;">
                                <button type="button" class="btn btn-info"><i class="fa fa-pen"></i></button>
                              </a>
                            </td>';
                          echo '</tr>';
                        }
                      }
                    echo '</tbody>
                  </table>
                </center>
              </div>
            </div>
          </div>';
        }
        echo '<script>
          function mostrarsn(){
            var oculto = document.getElementById("vlch").value;
            if(oculto == 0){
              document.getElementById("tablasincorte").style.display = "block";
              document.getElementById("cv1").classList.replace("icon-chevron-down2","icon-chevron-up2");
              document.getElementById("vlch").value = "1";
            }else{
              document.getElementById("tablasincorte").style.display = "none";
              document.getElementById("cv1").classList.replace("icon-chevron-up2","icon-chevron-down2");
              document.getElementById("vlch").value = "0";
            }
          }
        </script>';
      }
    }
    function prueba($page,$estatus,$nombre,$propietario) {
      $e1 = ''; $e2 = ''; $est = '';
      if($estatus) $est = '&estatus='.$estatus;
      if($estatus == "A") $e1 = "selected";
      if($estatus == "I") $e2 = "selected";
      ?><script>
        $('body').on("keydown", function(e) { 
          if (e.altKey && e.which === 78) {
            var btn = document.getElementById('nuevo');
            btn.click();
            e.preventDefault();
          }
        });
        $('body').on("keydown", function(e) { 
          if (e.altKey && e.which === 82) {
            window.location.reload();
          }
        });
      </script>
      <div class="row page-title-actions mb-1">
        <a  id="nuevo" data-fancybox data-type="ajax" data-src="popup/setcuenta.php?rand=<?php echo rand(1,1000); ?>" href="javascript:;" >
          <button type="button" class="btn btn-primary">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
          </button>
        </a>
        <a data-fancybox data-type="ajax" data-src="popup/settraspaso.php?rand=<?php echo rand(); ?>" href="javascript:;" >
          <button type="button" class="btn bg-teal bg-darken-3 text-white">
            <span class="glyphicon glyphicon-cog"></span><i class="icon-exchange"></i> Traspaso
          </button>
        </a>
        <a href="?modulo=cuentas&accion=proyecciones">
          <button type="button" class="btn btn-purple"><i class="icon-dollar"></i> Ver proyecciones </button>
        </a>
        <a data-fancybox data-type="ajax" data-src="popup/setordenp.php?rand=<?php echo rand(); ?>" href="javascript:;" >
          <button type="button" class="btn btn-success text-white">
            <span class="glyphicon glyphicon-cog"></span><i class="icon-user-secret"></i> Prestamo
          </button>
        </a><?php
        ?><!--<a href="?modulo=cuentas&accion=validacorte" class="btn btn-blue">Valida corte</a>-->
      </div>
      <div class="main-card mb-2 card">
        <div class="card-body">
          <div class="table-responsive">
            <center>
              <table width="100%" border="0"  cellpadding="0" cellspacing="0" class="table table-hover table-striped">
                <thead class="thead bg-primary text-white">
                  <tr>
                    <th width="5%"><strong></strong></th>
                    <th width="20%"><strong>Nombre de la Cta.</strong></th>
                    <th width="15%"><strong>Saldo</strong></th>
                    <th width="10%"><strong>Limite</strong></th>
                    <th width="15%"><strong>Tipo de Cta.</strong></th>
                    <th width="15%"><strong>Propietario</strong></th>
                    <th width="10%"><strong>Corte Activo</strong></th>
                    <th width="5%"><strong>Orden</strong></th>
                    <th width="5%"><strong>Editar</strong></th>
                  </tr>
                </thead>
                <tbody><?php
                  $totals = 0;
                  while ($row = $this->model->result->fetch_array()) {
                    $finic = busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_fini');
                    $ffinc = busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_ffin');
                    if($finic && $ffinc) {
                      $corte = $finic.' - '.$ffinc;
                      $saldo = saldo_actual($row['cu_id']);
                      $totals += $saldo;
                      echo '<tr>
                        <th>
                          <div class="form-inline">
                            <a href="?modulo=cuentas&accion=show&id='.$row['cu_id'].'">
                              <button class="btn btn-primary"><i class="fa fa-eye"></i></button>
                            </a>
                          </div>
                        </th>
                        <td>' . $row['cu_nmb']. '</td>
                        <td class="number-align d-3">$'.number_format($saldo,2).'</td>
                        <td class="number-align d-3">$ ' . $row['cu_limite'] . '</td>
                        <td>' .$this->tipocta[$row['cu_tipo']]. '</td>
                        <td>' . $row['cu_propietario']. '</td>
                        <td>'.$corte.'</td>';
                        echo '<form action="?modulo=cuentas&accion=updateord&id='.$row['cu_id'].'" method="post" onsubmit="return checkSubmitenviar();">
                          <td><input type="number" class="form-control" onchange="submit();" name="orden" value="'.$row['cu_orden'].'" /></td>
                        </form>
                        <td>
                          <a data-fancybox data-type="ajax" data-src="popup/setcuenta.php?id='.$row['cu_id'].'" href="javascript:;">
                            <button type="button" class="btn btn-info"><i class="fa fa-pen"></i></button>
                          </a>
                        </td>';
                      echo '</tr>';
                    }
                  }
                  echo '<tr>
                    <td colspan="1">&nbsp;</td>
                    <th class="number-align d-3">Total</th>
                    <td class="number-align d-3">$'.number_format($totals,2).'</td>
                    <td colspan="6">&nbsp;</td>
                  </tr>';
                echo '</tbody>
              </table>
            </center>
          </div>
        </div>';
        /* ?><div class="card-body mt-2">
          <div class="btn btn-block" style="color: black!important;display: flex;justify-content: space-between;text-align:left;" onclick="mostrarsn();">
            <div class="col-md-11z">
              <h2>Cuentas sin corte activo</h2>
            </div>
            <div class="col-md-1" style="display: flex;position: inherit;align-items: center;">
              <i class="icon icon-chevron-down2" style="font-size: x-large;" id="cv1"></i>
              <input type="hidden" id="vlch" value="0">
            </div>
          </div>
          <hr style="background: black;">
          <div class="table-responsive" id="tablasincorte" style="display:none">
            <center>
              <table width="100%" border="0"  cellpadding="0" cellspacing="0" class="table table-hover table-striped">
                <thead class="thead bg-primary text-white">
                  <tr>
                    <th width="5%"><strong></strong></th>
                    <th width="20%"><strong>Nombre de la Cta.</strong></th>
                    <th width="15%"><strong>Saldo</strong></th>
                    <th width="10%"><strong>Limite</strong></th>
                    <th width="15%"><strong>Tipo de Cta.</strong></th>
                    <th width="15%"><strong>Propietario</strong></th>
                    <th width="10%"><strong>Corte Activo</strong></th>
                    <th width="5%"><strong>Orden</strong></th>
                    <th width="5%"><strong>Editar</strong></th>
                  </tr>
                </thead>
                <tbody><?php
                  $totals = 0;
                  while ($row = $this->model->resultci->fetch_array()) {
                    $finic = busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_fini');
                    $ffinc = busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_ffin');
                    if(!$finic && !$ffinc) {
                      $corte = "No existe corte";
                      $saldo = 0;
                      echo '<tr>
                        <th>
                          <div class="form-inline">
                            <a href="?modulo=cuentas&accion=show&id='.$row['cu_id'].'">
                              <button class="btn btn-primary"><i class="fa fa-eye"></i></button>
                            </a>
                          </div>
                        </th>
                        <td nowrap>'.$row['cu_nmb'].'</td>
                        <td nowrap class="number-align d-3">$'.number_format($saldo,2).'</td>
                        <td nowrap class="number-align d-3">$'. $row['cu_limite'].'</td>
                        <td nowrap>'.$this->tipocta[$row['cu_tipo']].'</td>
                        <td nowrap>'.$row['cu_propietario'].'</td>
                        <td nowrap>'.$corte.'</td>';
                        echo '<form action="?modulo=cuentas&accion=updateord&id='.$row['cu_id'].'" method="post" onsubmit="return checkSubmitenviar();">
                          <td><input type="number" class="form-control" onchange="submit();" name="orden" value="'.$row['cu_orden'].'" /></td>
                        </form>
                        <td>
                          <a data-fancybox data-type="ajax" data-src="popup/setcuenta.php?id='.$row['cu_id'].'" href="javascript:;">
                            <button type="button" class="btn btn-info"><i class="fa fa-pen"></i></button>
                          </a>
                        </td>';
                      echo '</tr>';
                    }
                  }
                echo '</tbody>
              </table>
            </center>
          </div>
        </div>'; */
      echo'</div>';
      if($this->model->resultci->num_rows > 0){
        $sqlcu = 'SELECT DISTINCT(cu_id), cu_nmb FROM cortes INNER JOIN cuentas ON cu_id = c_cuenta 
                  WHERE c_estatus = "I" AND c_cuenta NOT IN 
                  (SELECT c_cuenta FROM cortes WHERE c_estatus = "A") ORDER BY c_ffin DESC';
        $resultcu = setq($sqlcu);
        if($resultcu->num_rows > 0){
          echo '<div class="row page-title-actions mt-2 mb-1">
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-target="#exampleModal">
              <i class="icon-inbox"></i> Nuevo Corte
            </button>
            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Nuevo Corte</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <form class="" action="?modulo=cuentas&accion=insertp" method="POST">
                    <div class="modal-body">
                      <div class="row p-1"> 
                        <div class="mb-5 col-md-10">
                          <label>Cuenta: </label>
                          <select id="cuenta" name="cuenta" class="form-control" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="Selecciona la cuenta para generar corte" data-original-title="" title="">';
                            while($rowcu = $resultcu->fetch_array()){
                              echo'<option value="'.$rowcu['cu_id'].'">'.$rowcu['cu_nmb'].'</option> ';
                            }
                          echo '</select>
                        </div>
                        <div class="mb-5 col-md-6">
                          <label>Fecha de inicio de corte: </label>
                          <input class="form-control" type="date" min="'.date('Y-m-d').'" value="'.date('Y-m-d').'" name="fini">
                        </div>
                        <div class="mb-5 col-md-6">
                          <label>Fecha de final de corte: </label>
                          <input class="form-control" type="date" min="'.date('Y-m-d').'" value="'.date('Y-m-d', strtotime('last day of this month')).'" name="ffin">
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                      <button type="submit" class="btn btn-primary">Generar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>';
        }
        ?><div class="main-card mb-2 card">
          <div class="card-body">
            <div class="btn btn-block" style="color: black!important;display: flex;justify-content: space-between;text-align:left;padding: 0rem;" onclick="mostrarsn();">
              <div class="col-md-11z">
                <h2>Cuentas sin corte activo</h2>
              </div>
              <div class="col-md-1" style="display: flex;position: inherit;align-items: center;">
                <i class="icon icon-chevron-down2" style="font-size: x-large;" id="cv1"></i>
                <input type="hidden" id="vlch" value="0">
              </div>
            </div>
            <hr style="background: black;margin-top: 0rem;margin-bottom: 0.5rem;">
            <div class="table-responsive" id="tablasincorte" style="display:none">
              <center>
                <table width="100%" border="0"  cellpadding="0" cellspacing="0" class="table table-hover table-striped">
                  <thead class="thead bg-primary text-white">
                    <tr>
                      <th width="5%"><strong></strong></th>
                      <th width="20%"><strong>Nombre de la Cta.</strong></th>
                      <th width="15%"><strong>Saldo</strong></th>
                      <th width="10%"><strong>Limite</strong></th>
                      <th width="15%"><strong>Tipo de Cta.</strong></th>
                      <th width="15%"><strong>Propietario</strong></th>
                      <th width="10%"><strong>Corte Activo</strong></th>
                      <th width="5%"><strong>Orden</strong></th>
                      <th width="5%"><strong>Editar</strong></th>
                    </tr>
                  </thead>
                  <tbody><?php
                    $totals = 0;
                    while ($row = $this->model->resultci->fetch_array()) {
                      $finic = busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_fini');
                      $ffinc = busca($row['cu_id'],'cortes','c_estatus="A" AND c_cuenta','c_ffin');
                      if(!$finic && !$ffinc) {
                        $corte = "No existe corte";
                        $saldo = 0;
                        echo '<tr>
                          <th>
                            <div class="form-inline">
                              <a href="?modulo=cuentas&accion=show&id='.$row['cu_id'].'">
                                <button class="btn btn-primary"><i class="fa fa-eye"></i></button>
                              </a>
                            </div>
                          </th>
                          <td nowrap>'.$row['cu_nmb'].'</td>
                          <td nowrap class="number-align d-3">$'.number_format($saldo,2).'</td>
                          <td nowrap class="number-align d-3">$'. $row['cu_limite'].'</td>
                          <td nowrap>'.$this->tipocta[$row['cu_tipo']].'</td>
                          <td nowrap>'.$row['cu_propietario'].'</td>
                          <td nowrap>'.$corte.'</td>';
                          echo '<form action="?modulo=cuentas&accion=updateord&id='.$row['cu_id'].'" method="post" onsubmit="return checkSubmitenviar();">
                            <td><input type="number" class="form-control" onchange="submit();" name="orden" value="'.$row['cu_orden'].'" /></td>
                          </form>
                          <td>
                            <a data-fancybox data-type="ajax" data-src="popup/setcuenta.php?id='.$row['cu_id'].'" href="javascript:;">
                              <button type="button" class="btn btn-info"><i class="fa fa-pen"></i></button>
                            </a>
                          </td>';
                        echo '</tr>';
                      }
                    }
                  echo '</tbody>
                </table>
              </center>
            </div>
          </div>
        </div>';
        echo '<script>
          function mostrarsn(){
            var oculto = document.getElementById("vlch").value;
            if(oculto == 0){
              document.getElementById("tablasincorte").style.display = "block";
              document.getElementById("cv1").classList.replace("icon-chevron-down2","icon-chevron-up2");
              document.getElementById("vlch").value = "1";
            }else{
              document.getElementById("tablasincorte").style.display = "none";
              document.getElementById("cv1").classList.replace("icon-chevron-up2","icon-chevron-down2");
              document.getElementById("vlch").value = "0";
            }
          }
        </script>';
      }
    }
    function show(){
      $botones = "";
      
        $atras = '<a href="?modulo=cuentas&accion=index" onclick="this.onclick = function(){return false;}">
          <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atrás </button>
        </a>';
        '<!-- 
          <a data-fancybox data-type="ajax" data-src="popup/setcuenta.php?id='.$this->model->id.'" href="javascript:;" >
            <button type="button" class="btn btn-info"><i class="fa fa-pen"></i> Editar </button>
          </a> 
        -->';
        $botones .= '<a href=?modulo=cuentas&accion=cortes&id=' . $this->model->id . ' title="Ver cortes" onclick="this.onclick = function(){return false;}">
          <button class="btn btn-primary"><i class="fas fa-calendar-alt" style="color: #ffffff;"></i> Periodos</button>
        </a>';
        if($this->model->id != "1"){
          if ($this->model->estatus == "A") {
            $botones .= '<a href=?modulo=cuentas&accion=disable&id='.$this->model->id.' title="Deshabilitar Cuenta" onclick="this.onclick = function(){return false;}">
              <button class="btn btn-danger"><i class="fa fa-times"></i> Desactivar cuenta</button>
            </a>';
          } else {
            $botones.=  '<a href=?modulo=cuentas&accion=enable&id='.$this->model->id.' title="Habilitar Cuenta" onclick="this.onclick = function(){return false;}">
              <button class="btn btn-success"><i class="fa fa-check"></i> Activar cuenta</button>
            </a>';
          }
        }

        toolbar($_GET['modulo'], $atras, "", "", $botones);
      echo '<div class="table-responsive mt-5">
        <table width="90%" border="0" class="table">';
          echo '<thead class="thead bg-primary text-white pt-1 pb-1">
            <tr>
              <th align="center"><strong>Nombre de la Cta.</strong></th>
              <th align="center"><strong>Nombre del Titular</strong></th>
              <th align="center"><strong>Tipo de Cta</strong></th>
            </tr>
          </thead>';
          echo '<tr>
            <td>'.$this->model->nmb.'</td>
            <td>'.$this->model->propietario.'</td>
            <td>'.$this->tipocta[$this->model->tipo].'</td>
          </tr>';
          echo '<tr class="bg-primary  text-white pt-1 pb-1">
            <th align="center"><strong>Saldo inicial</strong></th>
            <th align="center"><strong>Saldo limite</strong></th>
            <th align="center"><strong>Saldo actual</strong></th>
          </tr>';
          echo '<tr>
            <td>$'.number_Format(busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_saldo'),2).'</td>
            <td>$'.$this->model->limite.'</td>
            <td>$'.number_format(saldo_actual($this->model->id),2).'</td>
          </tr>';
        echo '</table></center>';
      $cortea = busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_id');
      if(!$cortea){    
        echo ' <div class="col-md-12" oncontextmenu="return false" onkeydown="return false">
          <center>
            <img src="http://jdceo.jdsuite.mx/images/cuentas.gif" align="center" class="img-fluid" width="90%"/>
          </center>
        </div>
        <div class="col-md-1 col-xs-2" hidden>
          <a href="?modulo=clientes&accion=direcciones&cliente='.$_GET['cliente'].'">
            <button type="button" class="btn btn-red" align="left">
              <i class="icon fa fa-times"></i>
            </button>
          </a>
        </div>';
      }else
        //echo '<hr width="97%">';
      $tipom = array("P"=>"ABONO","C"=>"RETIRO");
      $tipo = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
      ?><script>
        function habilita(){
          if(document.getElementById("filtro").checked){
            document.getElementById("periodo").hidden=false;
            document.getElementById("fini").hidden=true;
            document.getElementById("ffin").hidden=true;
            document.getElementById("filtros").submit();
          }else{
            document.getElementById("periodo").hidden=true;
            document.getElementById("fini").hidden=false;
            document.getElementById("ffin").hidden=false;
            document.getElementById("filtros").submit();
          }
        }
      </script><?php
      $aux1=""; $aux2="hidden"; $aux3=""; $aux4="";
      if (!isset($_POST['corte']))    $_POST['corte'] = NULL;
      if(!isset($_POST['fini']))    $_POST['fini'] = date('Y-m-d',strtotime(busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_fini')));
      if(!isset($_POST['ffin']))    $_POST['ffin'] = date('Y-m-d',strtotime(busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_ffin')));
      if(!isset($_POST['filtro']))    $_POST['filtro'] = NULL;
      else{
        $aux1=" checked "; $aux2=""; $aux3=" hidden"; $aux4=" hidden";
      }
      echo '<center>
        <form name="filtros" id="filtros" method="post">
          <div class="row" style="display: flex;">';
            echo '<div class="bg-primary  text-white col-2 col-md-2 p-1">
              <label class="text-white">Fecha Inicial: </label>
            </div>
            <div class="bg-primary  text-white col-4 col-md-2 p-1">
              <input type="date" min="'.$_POST['fini'].'" value="'.$_POST['fini'].'" id="fini" name="fini" '.$aux3.' class="form-control" >
            </div>';
            echo '<div class="bg-primary  text-white col-4 col-md-2 p-1">
              <label class="text-white">Fecha final: </label>
            </div>
            <div class="bg-primary  text-white col-4 col-md-2 p-1">
              <input type="date" value="'.$_POST['ffin'].'" max="'.$_POST['ffin'].'" id="ffin" name="ffin" '.$aux4.' class="form-control" >
            </div>';
            echo'<div class="thead bg-primary  text-white p-1 col-4 col-md-2">&nbsp;
              <button type="submit" id="actualizar" class="btn btn-secondary"/><i class="fa fa-redo"></i> Actualizar</button>
            </div>';
            $corte = busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_id');
            echo'<div class="thead bg-primary text-white  p-1 col-4 col-md-2">
              <a href="formats/xlsestadocuenta.php?cuenta='.$this->model->id.'&corte='.$corte.'&filtro='.$_POST['filtro'].'&fini='.$_POST['fini'].'&ffin='.$_POST['ffin'].'" target="_blank">
                <button type="button" id="actualizar" class="btn btn-success"/><i class="fas fa-file-excel" style="color: #ffffff;"></i> Descargar excel</button>
              </a>
            </div>
          </div>
        </form>';
      echo '</center>  ';
      if (!isset($_POST['corte']))    $_POST['corte'] = NULL;
      if(!isset($_POST['fini']))    $_POST['fini'] = date('Y-m-01');
      if(!isset($_POST['ffin']))    $_POST['ffin'] = date('Y-m-d');
      if(!isset($_POST['filtro']))    $_POST['filtro'] = NULL;
      $sql1 = ""; $sql2 = "";
      if($_POST['filtro']) {
        if($_POST['corte']){
          $sql1.=' AND  c_id="'.$_POST['corte'].'" ';
          $sql2.= ' AND (t_origencorte="'.$_POST['corte'].'" OR t_destinocorte="'.$_POST['corte'].'")';
          $ftemp1=busca($_POST['corte'],'cortes','c_id','c_fini');
          $ftemp2=busca($_POST['corte'],'cortes','c_id','c_ffin');
          $aux1='al '.fecha_formato($ftemp1,false,true);
          $saldoinicial=busca($_POST['corte'],'cortes','c_id','c_saldo');
          $aux2=' al '.fecha_formato($ftemp2,false,true);
        }
      }
      else{
        $sql1.=' AND DATE(cd_fecha) BETWEEN "'.$_POST['fini'].'" AND "'.$_POST['ffin'].'"';
        $sql2.= ' AND DATE(t_fgen) BETWEEN "'.$_POST['fini'].'" AND "'.$_POST['ffin'].'"';
        $aux1= ' al '.fecha_formato($_POST['fini'],false,true);
        $saldoinicial=busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_saldo');
        $aux2=' al '.fecha_formato($_POST['ffin'],false,true);
      }
      $sql= 'SELECT cd_tipo,cd_monto,cd_idref,cd_fecha fechagen,cd_gen,cd_tipom,cd_cuenta,cd_reverso,cd_corte, cd_observaciones
              FROM cortes INNER JOIN cortesd ON c_id=cd_corte
              WHERE c_cuenta="'.$this->model->id.'" AND cd_condonar="0" '.$sql1.'
              UNION
              SELECT "P",t_importe,t_id,t_fgen,t_ugen,"TR",t_destino,NULL,NULL, NULL FROM traspasos
              WHERE t_origen="'.$this->model->id.'"
              '.$sql2.'
              UNION
              SELECT "C",t_importe,t_id,t_fgen,t_ugen,"TR",t_origen,NULL,NULL, NULL FROM traspasos
              WHERE t_destino="'.$this->model->id.'"
              '.$sql2.'
              ORDER BY fechagen ASC';
      $result = setq($sql) or die($sql);
      echo '<div class="table-responsive">
        <table border="0" class="table"  width="100%">
          <thead class="thead bg-primary  text-white pt-1 pb-1">
            <tr><th colspan="6"><b>MOVIMIENTOS</b></th></tr>
            <tr>
              <th><b>Fecha</b></th>
              <th><b>Generó</b></th>
              <th><b>Descripcion</b></th>
              <th width="15%"><b>Depositos</b></th>
              <th width="15%"><b>Retiros</b></th>
              <th width="15%"><b>Saldo</b></th>
            </tr>
          </thead>
          <tbody>';
            $fechacorte = busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_fini');
            $color1='style="background-color:#49E67D"';
            echo '<tr>
              <td>'.fecha_formato($fechacorte,true,true).'</td>
              <td> </td>
              <td> SALDO INICIAL </td>
              <td '.$color1.' class="number-align">$'.number_format($saldoinicial,2).'</td>
              <td class="number-align">'.$retiros.'</td>
              <td class="number-align">$'.number_format($saldoinicial,2).'</td>';
            echo ' </tr>';
            $totala=0;
            $totalr=0;
            $saldot=$saldoinicial;
            $totala+=$saldoinicial;
            while($row = $result->fetch_array()){
              if($row['cd_tipo']=="P"){
                $totalr=$totalr+$row['cd_monto'];
                $depositos='-';   $color1='';
                $retiros='$'.number_format($row['cd_monto'],2); $color2='style="background-color:#E67D31"';
                $saldot=$saldot-$row['cd_monto'];

                if($row['cd_tipom'] == "TR") $desc = 'TRASPASO ENVIADO A '.busca($row['cd_cuenta'],'cuentas','cu_id','cu_nmb');
                elseif($row['cd_tipom'] == "X"){
                  $idr = busca($row['cd_reverso'],'cortesd','cd_corte="'.$row['cd_corte'].'" AND cd_id','cd_idref');
                  $sqlcx = 'SELECT ic_cxcobrar,ic_ingreso FROM ingreso_cxcobrar WHERE ic_ingreso = "'.$idr.'" ';
                  $resultcx = setq($sqlcx) or die($sqlcx);
                  $desc = "REVERSO DEL MOVIMIENTO ";
                  if($resultcx->num_rows == 0) $desc .= busca($idr,'ingresos','i_id','i_observaciones');
                  else{
                    while($rowcx = $resultcx->fetch_array()){
                      $sqlxp = 'SELECT * FROM cxcobrar WHERE cx_id = "'.$rowcx['ic_cxcobrar'].'" ';
                      $resultxp = setq($sqlxp) or die($sqlxp);
                      $i = 0;
                      while($rowxp = $resultxp->fetch_array()){
                        if($rowxp['cx_tipo'] == "R") $desc .= 'Remisión '.busca($rowxp['cx_referencia'],'remisiones','r_id','r_folio');
                        elseif($rowxp['cx_tipo'] == "S") $desc .= "SOPORTE ".$rowxp['cx_referencia'].' de '.busca($rowxp['cx_referencia'],'proyectos','p_id','p_nmb');
                        //elseif($rowxp['cx_tipo'] == "P") $desc .= busca($rowxp['cx_referencia'],'prestamos','pr_id','pr_descripcion');
                        elseif($rowxp['cx_tipo'] == "P") $desc .= "RESTAMO DE ".busca($rowxp['cx_referencia'],'ordenesp','op_id','op_nmb');
                        elseif($rowxp['cx_tipo'] == "I") $desc .= busca($rowxp['cx_referencia'],'comisiones','c_id','c_descripcion');
                        elseif($rowxp['cx_tipo'] == "V") $desc .= busca(busca($rowxp['cx_referencia'],'valesp','vp_id','vp_vale'),'vales','v_id','v_nmb').' - '.busca($rowxp['cx_idref'],'valesp','vp_id','vp_descripcion');
                        elseif($rowxp['cx_tipo'] == "T") $desc .= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cx_fini']));
                        elseif($rowxp['cx_tipo'] == "A") $desc .= "COTIZACION ".busca($rowxp['cx_referencia'],'cotizaciones','c_id','c_cotizacion');
                        elseif($rowxp['cx_tipo'] == "Z") $desc .= "POLIZA ".busca($rowxp['cx_referencia'],'remisiones','r_id','r_nmb');
                        if(!$desc) $desc .= busca($rowcx['ic_ingreso'],'ingresos','i_id','i_observaciones');
                      }
                    }
                  }
                }else if ($row['cd_tipom'] == "C"){
                  $desc = 'ENTREGA DE EFECTIVO';
                }else{
                  $sqlcx = 'SELECT ec_cxpagar,ec_egreso FROM egreso_cxpagar WHERE ec_egreso = "'.$row['cd_idref'].'"';
                  $resultcx = setq($sqlcx) or die($sqlcx);
                  $desc = "";
                  $div = '';
                  if($resultcx->num_rows == 0) $desc = busca($row['cd_idref'],'egresos','e_id','e_observaciones');
                  elseif($resultcx->num_rows > 1) $div = ' / ';
                  while($rowcx = $resultcx->fetch_array()){
                    $sqlxp = 'SELECT * FROM cxpagar WHERE cp_id = "'.$rowcx['ec_cxpagar'].'" ';
                    $resultxp = setq($sqlxp) or die($sqlxp);
                    $i = 0;
                    while($rowxp = $resultxp->fetch_array()){
                      $desc .= $div;
                      if($rowxp['cp_tipo'] == "O") $desc.= "ORDEN PAGO ".busca($rowxp['cp_idref'],'ordenesp','op_id','op_nmb');
                      elseif($rowxp['cp_tipo'] == "C") $desc.= "COMPRAS ".busca($rowxp['cp_idref'],'ordenesc','o_id','o_folio');
                      elseif($rowxp['cp_tipo'] == "F") $desc.= busca($rowxp['cp_idref'],'gastosf','gf_id','gf_nmb').' - '.date('d-m-Y',strtotime($rowxp['cp_fini']));
                      elseif($rowxp['cp_tipo'] == "R") $desc.= busca($rowxp['cp_idref'],'prestamos','pr_id','pr_descripcion');
                      elseif($rowxp['cp_tipo'] == "G" || $rowxp['cp_tipo'] == "V") $desc.= busca($rowxp['cp_idref'],'gastos','g_id','g_descripcion');
                      elseif($rowxp['cp_tipo'] == "T") $desc.= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cp_fini']));
                      elseif($rowxp['cp_tipo'] == "M") $desc.= "ORDEN DE COMPRA MENOR";
                      elseif($rowxp['cp_tipo'] == "D") $desc .= $rowxp['cp_observaciones'];
                      elseif($rowxp['cp_tipo'] == "E") $desc .= $rowxp['cp_observaciones'];
                      else {
                        if(!$row['cd_observaciones']) $desc = busca($rowcx['ec_egreso'],'egresos','e_id','e_observaciones');
                        else $desc = $row['cd_observaciones'];
                      }
                      $i++;
                    }
                  }
                }
              }else{
                $totala=$totala+$row['cd_monto'];
                $depositos= '$'.number_format($row['cd_monto'],2);  $color1='style="background-color:#49E67D"';
                $retiros='-';  $color2='';
                $saldot=$saldot+$row['cd_monto'];

                if($row['cd_tipom'] == "TR") $desc = 'TRASPASO COBRADO DESDE '.busca($row['cd_cuenta'],'cuentas','cu_id','cu_nmb');
                elseif($row['cd_tipom'] == "X"){
                  $idr = busca($row['cd_reverso'],'cortesd','cd_corte="'.$row['cd_corte'].'" AND cd_id','cd_idref');
                  $sqlcx = 'SELECT ec_cxpagar,ec_egreso FROM egreso_cxpagar WHERE ec_egreso = "'.$idr.'"';
                  $resultcx = setq($sqlcx) or die($sqlcx);
                  $desc = "REVERSO DEL MOVIMIENTO ";
                  $div = '';
                  if($resultcx->num_rows == 0) $desc .= busca($idr,'egresos','e_id','e_observaciones');
                  elseif($resultcx->num_rows > 1) $div = ' / ';
                  while($rowcx = $resultcx->fetch_array()){
                    $sqlxp = 'SELECT * FROM cxpagar WHERE cp_id = "'.$rowcx['ec_cxpagar'].'" ';
                    $resultxp = setq($sqlxp) or die($sqlxp);
                    $i = 0;
                    while($rowxp = $resultxp->fetch_array()){
                      $desc .= $div;
                      if($rowxp['cp_tipo'] == "O") $desc.= "ORDEN PAGO ".busca($rowxp['cp_idref'],'ordenesp','op_id','op_nmb');
                      elseif($rowxp['cp_tipo'] == "C") $desc.= "COMPRAS ".busca($rowxp['cp_idref'],'ordenesc','o_id','o_folio');
                      elseif($rowxp['cp_tipo'] == "F") $desc.= busca($rowxp['cp_idref'],'gastosf','gf_id','gf_nmb').' - '.date('d-m-Y',strtotime($rowxp['cp_fini']));
                      elseif($rowxp['cp_tipo'] == "R") $desc.= busca($rowxp['cp_idref'],'prestamos','pr_id','pr_descripcion');
                      elseif($rowxp['cp_tipo'] == "G" || $rowxp['cp_tipo'] == "V") $desc.= busca($rowxp['cp_idref'],'gastos','g_id','g_descripcion');
                      elseif($rowxp['cp_tipo'] == "T") $desc.= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cp_fini']));
                      elseif($rowxp['cp_tipo'] == "D") $desc .= $rowxp['cp_observaciones'];
                      else {
                        if(!$row['cd_observaciones']) $desc .= busca($rowcx['ec_egreso'],'egresos','e_id','e_observaciones');
                        else $desc .= $row['cd_observaciones'];
                      }
                      $i++;
                    }
                  }
                }else if ($row['cd_tipom'] == "C"){
                  $desc = $row['cd_observaciones'];
                }
                else{
                  $sqlcx = 'SELECT ic_cxcobrar,ic_ingreso FROM ingreso_cxcobrar WHERE ic_ingreso = "'.$row['cd_idref'].'" ';
                  $resultcx = setq($sqlcx) or die($sqlcx);
                  $desc = "";
                  if($resultcx->num_rows == 0) $desc = busca($row['cd_idref'],'ingresos','i_id','i_observaciones');
                  while($rowcx = $resultcx->fetch_array()){
                    $sqlxp = 'SELECT * FROM cxcobrar WHERE cx_id = "'.$rowcx['ic_cxcobrar'].'" ';
                    $resultxp = setq($sqlxp) or die($sqlxp);
                    $i = 0;
                    while($rowxp = $resultxp->fetch_array()){
                      if($rowxp['cx_tipo'] == "R") $desc = 'Remisión '.busca($rowxp['cx_referencia'],'remisiones','r_id','r_folio');
                      elseif($rowxp['cx_tipo'] == "S") $desc = "SOPORTE ".$rowxp['cx_referencia'].' de '.busca($rowxp['cx_referencia'],'proyectos','p_id','p_nmb');
                      //elseif($rowxp['cx_tipo'] == "P") $desc = busca($rowxp['cx_referencia'],'prestamos','pr_id','pr_descripcion');
                      elseif($rowxp['cx_tipo'] == "P") $desc = "PRESTAMO DE ".busca($rowxp['cx_referencia'],'ordenesp','op_id','op_nmb');
                      elseif($rowxp['cx_tipo'] == "I") $desc = busca($rowxp['cx_referencia'],'comisiones','c_id','c_descripcion');
                      elseif($rowxp['cx_tipo'] == "V") $desc = busca(busca($rowxp['cx_referencia'],'valesp','vp_id','vp_vale'),'vales','v_id','v_nmb').' - '.busca($rowxp['cx_idref'],'valesp','vp_id','vp_descripcion');
                      elseif($rowxp['cx_tipo'] == "T") $desc = "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cx_fini']));
                      elseif($rowxp['cx_tipo'] == "A") $desc = "COTIZACION ".busca($rowxp['cx_referencia'],'cotizaciones','c_id','c_cotizacion');
                      elseif($rowxp['cx_tipo'] == "Z") $desc = "POLIZA ".busca($rowxp['cx_referencia'],'remisiones','r_id','r_nmb');
                      if(!$desc) $desc = busca($rowcx['ic_ingreso'],'ingresos','i_id','i_observaciones');
                    }
                  }
                }
              }
              echo '<tr>
                <td>'.fecha_formato($row['fechagen'],true,true).'</td>
                <td>'.$row['cd_gen'].'</td>
                <td>'.$desc.'</td>
                <td '.$color1.' class="number-align">'.$depositos.'</td>
                <td '.$color2.' class="number-align">'.$retiros.'</td>
                <td class="number-align">$'.number_format($saldot,2).'</td>';
              echo ' </tr>';
            }
            echo '<tr>
              <td colspan="3" style="text-align:right;"><b>Totales</b></td>
              <td class="number-align"><b>$'.number_format($totala,2).'</b></td>
              <td class="number-align"><b>$ '.number_format($totalr,2).'</b></td>
              <td class="number-align"><b>$ '.number_format($saldot,2).'</b></td>
            </tr>';
          echo '</tbody>
        </table>
      </div></center>';
    }
    function cortes(){
      $fechaini=date('Y-m-d',strtotime('first day of this month'));
      $fechafin=date('Y-m-d',strtotime('last day of this month'));
      $sqlp='SELECT * FROM cortes WHERE c_cuenta="'.$this->model->id.'"';
      $tipo = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
      $estatusc = array("A"=>"ACTIVO","I"=>"FINALIZADO");

      $atras = '<a href="?modulo=cuentas&accion=show&id='.$this->model->id.'">
        <button class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atrás</button>
      </a>';

      toolbar($_GET['modulo'], $atras);

      echo '<div class="table-responsive">
        <table width="100%" border="0" class="table table-striped">
          <thead class="bg-primary text-white">';
            echo '<tr>
              <th align="center"><strong>Nombre de la Cta.</strong></th>
              <th align="center"><strong>Nombre del Titular</strong></th>
              <th align="center" '.$value.'><strong>Tipo de Cta</strong></th>
            </tr>
          </thead>';
          echo '<tr>
            <td>'.$this->model->nmb.'</td>
            <td>'.$this->model->propietario.'</td>
            <td>'.$tipo[$this->model->tipo].'</td>
          </tr>
        </table>';
        echo '<table width="100%" border="0" class="table">
          <thead class="bg-primary text-white">
            <tr>
              <th align="center"><strong>Saldo inicial</strong></th>
              <th align="center"><strong>Saldo límite</strong></th>
              <th align="center"><strong>Saldo actual</strong></th>
            </tr>
          </thead>';
          echo '<tr>
            <td>$'.$this->model->saldo.'</td>
            <!-- <td>$'.number_Format(busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_saldo'),2).'</td> -->
            <td>$'.$this->model->limite.'</td>
            <td>$'.number_format(saldo_actual($this->model->id),2).'</td>
          </tr>';
        echo '</table>
      </div>';
      echo '<div class="table-responsive">
        <table width="100%" class="table">
          <tr>
            <td width="50%" valign="top">';
              if(isset($_GET['abrir'])){ $tra = 'style="background:#CC0000;color:#FFFFFF;"'; $ley = "Abrir nuevo corte";  }
              else{ $tra = ""; }
              if(busca($this->model->id,'cortes','c_estatus = "A" AND  c_cuenta','COUNT(*)') == 0){
                echo '<form action="?modulo=cuentas&accion=insertp&id='.$this->model->id.'" method="post" onsubmit="return checkSubmitenviar();">
                  <table class="table table-striped" width="100%">
                    <thead class="bg-primary text-white"><tr '.$tra.'><th colspan="3"><b>NUEVO CORTE</b></th></tr></thead>
                    <tr><td colspan="3">Al dar de alta un nuevo periodo, se finalizara el periodo activo actualmente.</td></tr>
                    <tr>
                      <th>Fecha inicio</th>
                      <th>fecha fin</th>';
                      $activa = busca($this->model->id,'cortes','c_cuenta','c_id');
                      if(!$activa) echo '<th>Saldo Inicial</th>';
                    echo '</tr>
                    <tr>
                      <td><input type="date" name="fini" min="'.date('Y-m-d').'" max="'.date('Y-m-d').'" value="'.date('Y-m-d').'" class="form-control" /></td>
                      <td><input type="date" name="ffin" min="'.date('Y-m-d').'" value="'.date('Y-m-t').'" class="form-control" /></td>';
                      $activa = busca($this->model->id,'cortes','c_cuenta','c_id');
                      if(!$activa) echo '<td><input type="numer" name="sinicial" value="'.$this->model->saldo.'" class="form-control" ></td>'; 
                    echo '</tr>';
                    echo'<tr>
                      <td colspan="3">
                        <button class="btn btn-info">
                          <i class="fa fa-check"></i> Aplicar
                        </button>
                      </td>
                    </tr>';
                  echo '</table>
                </form>
                </td></tr></table></div>';
              }else{
                echo'</td></tr></table></div>';
              }
      echo '<div class="table-responsive">
        <table width="100%" class="table">
          <td width="50%" valign="top">';
            echo '<table class="table" width="100%" >
              <form action="?modulo=cxpagos&accion=update&id='.$this->model->id.'" method="post" onsubmit="return checkSubmitenviar();">
                <thead class="bg-primary text-white"><tr><th colspan="5"><b>CORTES</b></th></tr></thead>
                <tr>
                  <th>Inicio</th>
                  <th>Fin</th>
                  <th>Estatus</th>
                  <th>Saldo Inicial</th>
                  <th>Acciones</th>
                </tr>';
                $resultp=setq($sqlp) or die($sqlp);
                while($row=$resultp->fetch_array()){
                  echo '<tr>
                    <td>'.$row['c_fini'].'</td>
                    <td>'.$row['c_ffin'].'</td>
                    <td>'.$estatusc[$row['c_estatus']].'</td>
                    <td>'.$row['c_saldo'].'</td>';
                    if($row['c_estatus'] == "I") echo '<td><a target="_blank" href="?modulo=cuentas&accion=showcorte&id='.$row['c_id'].'&cuenta='.$row['c_cuenta'].'" class="btn btn-success btn-sm"><i class="fas fa-history" style="color: #ffffff;"></i> Ver movimientos</a></td>';
                  echo '</tr>';
                }
              echo'</form>
            </table>';
          echo '</td>
        </table>
      </div>';
    }
    function proyecciones($fini,$ffin,$nmb){
      //Sección: E1 Encabezado - Botones de acción
        if(busca('N','proyecciones','p_estatus','COUNT(p_id)') > 0 ){
          $nuevo = '<button type="button" onclick="alertan();" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Crear nueva proyección"><i class="fa fa-pen"></i>Nuevo</button>  
          <script>
            function alertan(){
              alert("Para agregar una nueva proyeccion se tiene que cerrar la anterior.");
            }
          </script>';
        }else{
          $nuevo = '&nbsp;<a data-fancybox data-type="ajax" data-src="popup/setproyeccion.php?rand='.rand().'" href="javascript:;">
            <button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Crear nueva proyección"><i class="fa fa-pen"></i>Nuevo</button>
          </a>';
        }
        echo '
          <div class="mb-5">';
            $atras  = '<a href="?modulo=cuentas&accion=index">
              <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Cuentas</button>
            </a>';
            $filtro = '<button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
            </button>';

        toolbar($_GET['modulo'], $atras, $filtro, $nuevo);

        ?>
        <div id="filter-panel" class="col-md-9 collapse filter-panel">
          <div class="panel panel-default">
            <div class="panel-body">
              <form class="form-inline" role="form" method="post" action="?modulo=cuentas&accion=proyecciones">
                <div class="mb-5 mr-1">
                  <label for="tipom">Desde</label>
                  <input type="date" name="fini" id="fini" class="form-control" value="<?php echo $fini ?>" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Hasta</label>
                  <input type="date" name="ffin" id="ffin" class="form-control" value="<?php echo $ffin?>" />
                </div>
                <div class="mb-5 mr-1">
                  <label for="tipom">Nombre</label>
                  <input type="text" class="form-control" name="nmb" placeholder="Nombre de la proyección" onfocus="this.select();" value="<?php echo $nmb?>" />
                </div>
                <div class="mb-5">
                  <label for="">Acciones</label> <br>
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                  </button>
                  <a href="?modulo=remisiones&accion=index">
                    <button type="button" class="btn btn-warning">
                      <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i>  Limpiar
                    </button>
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="main-card mb-3 card">
        <div class="card-body"><?php
          echo '<div class="table-responsive">
            <table class="table table-striped">
              <thead class="thead bg-primary text-white pt-1 pb-1">
                <tr>
                  <th></th>
                  <th>Fecha</th>
                  <th>Usuario</th>
                  <th>Descripción</th>
                  <th>Estatus</th>
                </tr>
              </thead>';
              while($row = $this->model->result->fetch_array()){
                echo '<tr>
                  <td>
                    <a href="?modulo=cuentas&accion=showproyeccion&id='.$row['p_id'].'">
                      <button class="btn btn-primary"><i class="fas fa-share-square" style="color: #ffffff;"></i> Ver</button>
                    </a>';
                    if($row['p_estatus'] == "N"){
                      echo'<a data-fancybox data-type="ajax" data-src="popup/setproyeccion.php?id='.$row['p_id'].'&rand='.rand().'" href="javascript:;">
                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-placement="top" title="Cambiar fecha de cierre">
                          <i class="fa fa-pen"></i> Editar
                        </button>
                      </a>';
                    }

                  echo'</td>
                  <td>'.fecha_formato($row['p_fechaap'],false,false).'</td>
                  <td>'.$row['p_usuario'].'</td>
                  <td>'.$row['p_nmb'].'</td>
                  <td>'.$row['p_estatus'].'</td>
                </tr>';
              }
            echo '</table>
          </div>
        </div>
      </div>';
    }
    function showproyeccion(){
      $botones = "";
      if($this->model->estatus == "F") $disabled = "disabled";
      else $disabled = "";
      //Sección: E1 Encabezado - Botones de acción
      if($_GET['accion'] == 'showproyeccion') $active1 = 'active';
      elseif($_GET['accion'] == 'showproyeccionresumen') $active2 = 'active';
      elseif($_GET['accion'] == 'showliquidez') $active3 = 'active';
      
        $atras = '<a href="?modulo=cuentas&accion=proyecciones">
          <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Ver proyecciones </button>
        </a>';
        $botones .= '<a href="?modulo=cuentas&accion=showproyeccion&id='.$this->model->idp.'" class="btn btn-primary '.$active1.' ">Proyección</a>';
        $botones .=   '<a href="?modulo=cuentas&accion=showproyeccionresumen&id='.$this->model->idp.'" class="btn btn-success '.$active2.'">Resumen</a>';
        $botones .=   '<a href="?modulo=cuentas&accion=showliquidez&id='.$this->model->idp.'" class="btn btn-danger '.$active3.'">Liquidez</a>';
        if($this->model->estatus == "N"){
          $botones .= '<button onclick="cerrarp()" class="btn btn-danger">
            <i class="fa fa-times"></i> Finalizar Proyección
            </button>';
          echo '
          <script>
            function cerrarp(){
              var c = confirm("Al finalizar esta proyeccion los pagos realizados no formaran parte de este perido.");
              if(c){
                window.location.href="?modulo=cuentas&accion=cerrarproyeccion&id='.$this->model->idp.'"
              }
            }
          </script>';
        }

        toolbar($_GET['modulo'], $atras, "", "", $botones);
      echo '<div class="main-card mb-3 card mt-5">
        <div class="card-body row">';
          echo '<script>
            function confcac(idp){
              var conf = confirm("¿Deseas borrar este concepto de la proyeccion?");
              if(conf == true){
                window.location.href="?modulo=cuentas&accion=delproyecciond&proyeccion='.$this->model->idp.'&idp=" + idp;
              }
            }
          </script>';
          //Ingresos
          echo '<div class="col-6 col-md-6">
            <div class="table-responsive">
              <table class="table table-striped text-medium">
                <thead class="thead bg-teal bg-darken-2 text-white pt-1 pb-1" style="background: teal;">
                  <tr>
                    <th colspan="2" class="mt-1 mb-1">Ingresos</th>
                    <th colspan="2">
                      <a accesskey="B" href="popup/importproyeccion?from=cxcobrar&proyeccion='.$this->model->idp.'" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                        <button class="btn btn-sm btn-secondary" '.$disabled.'><i class="icon-mail-reply"></i> Importar ingresos</button>
                      </a>
                    </th>
                  </tr>
                  <tr>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th>Importe</th>
                    <th></th>
                  </tr>
                </thead>';
                $ingresos = 0;
                $saldocuentas = 0;
                $saldocxc = 0;
                $sql = 'SELECT * FROM proyeccionesd WHERE pd_proyeccion = "'.$this->model->idp.'" AND pd_tipo = "C"
                        AND pd_estatus IN ("N") ORDER BY pd_fecha ASC';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  if($row['pd_fagregada'] != NULL) $f = 'pd_fagregada';
                  else $f = 'p_fechaap';
                  if(busca($row['pd_referencia'],'cxcobrar','cx_id','cx_estatus') == "C") $this->model->delproyecciond($row['pd_id']);
                  else{
                    $sqlBUSCAR = 'SELECT SUM(ic_monto) FROM ingreso_cxcobrar INNER JOIN ingresos ON ic_ingreso =  i_id
                                  INNER JOIN proyeccionesd ON ic_cxcobrar = pd_referencia 
                                  INNER JOIN proyecciones ON p_id = pd_proyeccion WHERE p_id = "'.$this->model->idp.'" 
                                  AND pd_referencia = "'.$row['pd_referencia'].'" AND i_estatus = "F" AND pd_tipo = "C"'; 
                    if($this->model->estatus == "F") $sqlBUSCAR .= ' AND DATE(ic_fapli) BETWEEN DATE('.$f.') AND DATE(p_fechacierre)';
                    elseif($row['pd_fagregada'] != NULL) $sqlBUSCAR .= ' AND DATE(ic_fapli) >= DATE(pd_fagregada)';
                    else $sqlBUSCAR .= ' AND DATE(ic_fapli) >= DATE(p_fechaap)';
                    $resultBUSCAR = setq($sqlBUSCAR);
                    list($sumaingresos) = $resultBUSCAR->fetch_array();
                    if($row['pd_estatus'] == "N") $abonado = $sumaingresos;
                    else $abonado = 0;
                    $resta = $row['pd_importe'] - $abonado;
                    if($resta > 0){
                      echo '<tr>
                        <td>'.date('d-m-Y',strtotime($row['pd_fecha'])).'</td>
                        <td>'.$row['pd_nmb'].'</td>
                        <td class="number-align">$ '.number_format(($row['pd_importe'] - $abonado),2).'</td>
                        <td><button class="btn btn-danger" onclick="confcac('.$row['pd_id'].');" '.$disabled.'><i class="fa fa-trash"></i></button></td>
                      </tr>';
                      //$ingresos+=$row['pd_importe'];
                      $ingresos+=$row['pd_importe'] - $abonado;
                      $saldocxc += $row['pd_importe'] - $abonado;
                    }else{
                      $sqlupproyecccion = 'UPDATE proyeccionesd SET
                                          pd_estatus = "F"
                                          WHERE pd_id = "'.$row['pd_id'].'"';
                      setq($sqlupproyecccion);
                    }
                  }
                }
                echo '<tr class="" style="background: #1d2b36;">
                  <td></td>
                  <td class="number-align">Sumas de cuentas por cobrar</td>
                  <td class="number-align">$ '.number_format($saldocxc,2).'</td>
                </tr>';
                echo '
                <tr>
                  <td>
                  <div class="btn-group" bis_skin_checked="1">
                    <button class="btn bg-teal bg-darken-2 text-white btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                      Seleccionar Cuentas
                    <span class="tag tag-pill tag-default tag-danger tag-default tag-up"></span>
                    </button>
                    <div class="dropdown-menu" style="z-index:50; font-size: small; min-width: 290px;" bis_skin_checked="1"> <!-- style="max-height: 400px; overflow: scroll;" -->
                      <div class="table-responsive" bis_skin_checked="1">
                        <table class="table table-striped table-hover mb-0" style="font-size: x-small;">
                          <thead class="thead bg-teal bg-darken-2  text-white pt-1">
                            <tr>
                              <th>Estatus</th>
                              <th>Caja</th>
                              <th>Monto</th>
                            </tr>
                          </thead>
                          <tbody>';
                          $sql = 'SELECT cu_id,cu_nmb FROM cuentas WHERE cu_estatus = "A" AND cu_empresa = "1"  ORDER BY cu_orden';
                          $result = setq($sql);
                          while($row = $result->fetch_array()){
                            $saldo = saldo_actual($row['cu_id']);
                            //if($saldo > 0){
                              $existenproy = busca($row['cu_id'],'cuentas_proyecciones','cp_proyeccion = "'.$_GET['id'].'" AND cp_cuenta','cp_id');
                              if($existenproy){
                                $checked = "checked";
                              }else{
                                $checked = '';
                              }
                              echo '
                              <tr>
                                <td><input '.$checked.' type="checkbox" name="'.$row['cu_id'].'" id="'.$row['cu_id'].'" onchange="cambiarcuentaproy('.$row['cu_id'].','.$_GET['id'].')"></td>
                                <td>'.$row['cu_nmb'].'</td>
                                <!-- <td><input type="button" class="form-control form-control-sm" value="'.number_format($saldo,2).'"></td> -->
                                <td class="number-align">$ '.number_format($saldo,2).'</td>
                                <!-- <td><button data-toggle="tooltip" title="Traer monto completo" onclick="traermontocompleto('.$row['cu_id'].')" class="btn btn-success btn-sm"><i class="icon-dollar"></i></button></td> -->
                              </tr>';
                            //}
                          }
                          echo '
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  </td>
                </tr>
                ';
                //Saldo en cuentas
                $sql = 'SELECT cu_id,cu_nmb FROM cuentas WHERE cu_estatus = "A" AND cu_empresa = "1"  ORDER BY cu_orden';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  $saldo = saldo_actual($row['cu_id']);
                  //if($saldo > 0){
                    $existenproy = busca($row['cu_id'],'cuentas_proyecciones','cp_proyeccion = "'.$_GET['id'].'" AND cp_cuenta','cp_id');
                    if($existenproy){
                      $ingresos+=$saldo;
                      $saldocuentas += $saldo;
                      echo '
                      <tr>
                        <td>-</td>
                        <td>'.$row['cu_nmb'].'</td>
                        <!-- <td><input type="button" class="form-control form-control-sm" value="'.number_format($saldo,2).'"></td> -->
                        <td class="number-align">$ '.number_format($saldo,2).'</td>
                        <!-- <td><button data-toggle="tooltip" title="Traer monto completo" onclick="traermontocompleto('.$row['cu_id'].')" class="btn btn-success btn-sm"><i class="icon-dollar"></i></button></td> -->
                      </tr>';
                    }
                  //}
                }
                echo '
                <script class="">
                  function cambiarcuentaproy(id,proy){
                    $.ajax({
                      method: "POST",
                      url: "query/cuentasproyeccion.php",
                      data: {id: id,
                            idproy: proy}
                    }).success(function(data){
                      location.reload();
                    });
                  }
                </script>
                ';
                echo '
                <tr class="text-white" style="background: #1d2b36;">
                  <td></td>
                  <td class="number-align">Sumas de saldos en cuentas</td>
                  <td class="number-align">$ '.number_format($saldocuentas,2).'</td>
                </tr>';
                echo '<tr>
                  <td></td>
                  <td class="number-align">Total</td>
                  <td class="number-align">$ '.number_format($ingresos,2).'</td>
                </tr>';
              echo '</table>
            </div>';
          echo '</div>';
          //Egresos
          echo '<div class="col-6 col-md-6">
            <div class="table-responsive">
              <table class="table table-striped text-medium">
                <thead class="thead bg-danger text-white pt-1 pb-1" style="background:red;">
                  <tr>
                    <th colspan="2" class="mt-1 mb-1">Egresos</th>
                    <th colspan="2">
                      <a accesskey="B" href="popup/importproyeccion?from=cxpagar&proyeccion='.$this->model->idp.'" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                        <button class="btn btn-secondary" '.$disabled.'><i class="icon-mail-forward"></i> Importar egresos</button>
                      </a>
                    </th>
                  </tr>
                  <tr>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th>Importe</th>
                    <th></th>
                  </tr>
                </thead>';
                $egresos = 0;
                $sql = 'SELECT * FROM proyeccionesd WHERE pd_proyeccion = "'.$this->model->idp.'" AND pd_tipo = "P"
                        AND pd_estatus IN ("N") ORDER BY pd_fecha ASC';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  if($row['pd_fagregada'] != NULL) $f = 'pd_fagregada';
                  else $f = 'p_fechaap';
                  if(busca($row['pd_referencia'],'cxpagar','cp_id','cp_estatus') == "C") $this->model->delproyecciond($row['pd_id']);
                  else{
                    $sqlBUSCAR = 'SELECT SUM(ec_monto) FROM egreso_cxpagar INNER JOIN proyeccionesd ON ec_cxpagar = pd_referencia 
                                  INNER JOIN proyecciones ON p_id = pd_proyeccion INNER JOIN egresos ON ec_egreso = e_id
                                  WHERE p_id = "'.$this->model->idp.'" AND pd_referencia = "'.$row['pd_referencia'].'" AND e_estatus = "F"
                                  AND pd_tipo = "P"';
                    if($this->model->estatus == "F") $sqlBUSCAR .= ' AND DATE(ec_fapli) BETWEEN ('.$f.') AND (p_fechacierre)';
                    elseif($row['pd_fagregada'] != NULL) $sqlBUSCAR .= ' AND DATE(ec_fapli) >= DATE(pd_fagregada)';
                    else $sqlBUSCAR .= ' AND DATE(ec_fapli) >= (p_fechaap)';
                    $resultBUSCAR = setq($sqlBUSCAR);
                    //echo $sqlBUSCAR.' <br>';
                    list($sumadeegresos) = $resultBUSCAR->fetch_array(); 
                    if($row['pd_estatus'] == "N") $abonado = $sumadeegresos;
                    else $abonado = 0;
                    $resta = $row['pd_importe'] - $abonado;
                    //if($row['pd_referencia'] == "799") echo "$resta = ".$row['pd_importe']." - $abonado";
                    if($resta > 0){
                      echo '<tr>
                        <td>'.date('d-m-Y',strtotime($row['pd_fecha'])).'</td>
                        <td>'.$row['pd_nmb'].'</td>
                        <td class="number-align">'.number_format(($row['pd_importe'] - $abonado),2).'</td>
                        <td><button class="btn btn-danger" onclick="confcac('.$row['pd_id'].');" '.$disabled.'><i class="fa fa-trash"></i></button></td>
                      </tr>';
                      
                      $egresos+=$resta;
                    }else{
                      $sqlupproyecccion = 'UPDATE proyeccionesd SET
                                            pd_estatus = "F"
                                            WHERE pd_id = "'.$row['pd_id'].'"';
                      setq($sqlupproyecccion);
                    }
                  }
                }
                echo '<tr>
                  <td></td>
                  <td class="number-align">Total</td>
                  <td class="number-align">$ '.number_format($egresos,2).'</td>
                </tr>';
              echo '</table>
            </div>';
          echo '</div>';
          $meta = $ingresos-$egresos;
          if($meta < 0){
            $stylem = 'danger';
            $leym = 'Utilidad faltante para cubrir meta';
          }
          else{
            $stylem = 'success';
            $leym = 'Utilidad por encima de la meta';
          }
          echo '<div class="col-md-12 col-12 alert alert-'.$stylem.'">
            <center>'.$leym.' <h4>$'.number_format(abs($meta),2).'</h4></center>
          </div>';
        echo '</div>
      </div>';
    }
    function showresumen(){
      $botones = "";
      if($this->model->estatus == "F") $disabled = "disabled";
      else $disabled = "";
      //Sección: E1 Encabezado - Botones de acción
      if($_GET['accion'] == 'showproyeccion') $active1 = 'active';
      elseif($_GET['accion'] == 'showproyeccionresumen') $active2 = 'active';
      elseif($_GET['accion'] == 'showliquidez') $active3 = 'active';
      $atras = '<a href="?modulo=cuentas&accion=proyecciones">
        <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Ver proyecciones </button>
      </a>';
      $botones .= '<a href="?modulo=cuentas&accion=showproyeccion&id='.$this->model->idp.'" class="btn btn-primary '.$active1.' ">Proyección</a>';
      $botones .=   '<a href="?modulo=cuentas&accion=showproyeccionresumen&id='.$this->model->idp.'" class="btn btn-success '.$active2.'">Resumen</a>';
      $botones .=   '<a href="?modulo=cuentas&accion=showliquidez&id='.$this->model->idp.'" class="btn btn-danger '.$active3.'">Liquidez</a>';
      if($this->model->estatus == "N"){
        $botones .= '<button onclick="cerrarp()" class="btn btn-danger">
          <i class="fa fa-times"></i> Finalizar Proyección
          </button>';
        echo '<script>
          function cerrarp(){
            var c = confirm("Al finalizar esta proyeccion los pagos realizados no formaran parte de este perido.");
            if(c){
              window.location.href="?modulo=cuentas&accion=cerrarproyeccion&id='.$this->model->idp.'"
            }
          }
        </script>';
      }
      toolbar($_GET['modulo'], $atras, "", "", $botones);

      echo '<div class="main-card mb-3 card">
        <div class="card-body row">';
          echo '<script>
            function confcac(idp){
              var conf = confirm("¿Deseas borrar este concepto de la proyeccion?");
              if(conf == true){
                window.location.href="?modulo=cuentas&accion=delproyecciond&proyeccion='.$this->model->idp.'&idp=" + idp;
              }
            }
          </script>';
          //Ingresos
          echo '<div class="col-6 col-md-6">
            <div class="table-responsive">
              <table class="table table-striped text-medium">
                <thead class="thead bg-teal bg-darken-2 text-white pt-1 pb-1" style="background: teal;">
                  <tr>
                    <th colspan="2" class="mt-1 mb-1">Ingresos</th>
                    <th colspan="5">
                      <!-- 
                        <a accesskey="B" href="popup/importproyeccion?from=cxcobrar&proyeccion='.$this->model->idp.'" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                          <button class="btn btn-secondary"><i class="icon-mail-reply"></i> Importar ingresos</button>
                        </a>
                      --> 
                    </th> 
                  </tr>
                  <tr>
                    <th>Estatus</th>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th>Importe</th>
                    <th>Abono</th>
                    <th colspan="2">Saldo</th>
                  </tr>
                </thead>';
                $ingresos = 0;
                $saldoabono = 0;
                $sql = 'SELECT * FROM proyeccionesd WHERE pd_proyeccion = "'.$this->model->idp.'" AND pd_tipo = "C"
                        ORDER BY pd_fecha ASC';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  if($row['pd_fagregada'] != NULL) $f = 'pd_fagregada';
                  else $f = 'p_fechaap';

                  $sqlBUSCAR = 'SELECT SUM(ic_monto) FROM ingreso_cxcobrar INNER JOIN proyeccionesd ON ic_cxcobrar = pd_referencia 
                                INNER JOIN proyecciones ON p_id = pd_proyeccion INNER JOIN ingresos ON ic_ingreso = i_id
                                WHERE p_id = "'.$this->model->idp.'" AND pd_referencia = "'.$row['pd_referencia'].'" AND i_estatus = "F"
                                AND pd_tipo = "C"'; 
                  if($this->model->estatus == "F") $sqlBUSCAR .= ' AND DATE(ic_fapli) BETWEEN DATE('.$f.') AND DATE(p_fechacierre)';
                  elseif($row['pd_fagregada'] != NULL) $sqlBUSCAR .= ' AND DATE(ic_fapli) >= DATE(pd_fagregada)';
                  else $sqlBUSCAR .= ' AND DATE(ic_fapli) >= DATE(p_fechaap)';
                  $resultBUSCAR = setq($sqlBUSCAR);
                  list($sumaingresos) = $resultBUSCAR->fetch_array(); 
                  if($sumaingresos > 0 && $sumaingresos < $row['pd_importe']) $icon = '<i class="fa fa-plus warning" data-toggle="tooltip" title="Cuenta con abono"></i>';
                  elseif($sumaingresos == 0) $icon = '<i class="fa fa-times danger" data-toggle="tooltip" title="Cuenta no abonada"></i>';
                  elseif($sumaingresos == $row['pd_importe'] || $sumaingresos > $row['pd_importe']) $icon = '<i class="fa fa-check green" data-toggle="tooltip" title="Cuenta alcanzada"></i>';
                  else $icon = '';
                  //if(busca($row['pd_referencia'],'cxcobrar','cx_id','cx_estatus') == "F") $this->model->delproyecciond($row['pd_id']);
                  //else{
                  $ingresomenosa = ($row['pd_importe'] - $sumaingresos);
                  echo '<tr>
                    <td>'.$icon.'</td>
                    <td>'.date('d-m-Y',strtotime($row['pd_fecha'])).'</td>
                    <td>'.$row['pd_nmb'].'</td>
                    <td  class="number-align">$ '.number_format($row['pd_importe'],2).'</td>
                    <td colspan="2" class="number-align">$ '.number_format($sumaingresos,2).'</td>
                    <td class="number-align">$'.number_format($ingresomenosa,2).'</td>
                    <!-- <td><button class="btn btn-danger" onclick="confcac('.$row['pd_id'].');"><i class="fa fa-trash"></i></button></td> -->
                  </tr>';
                  $ingresos+=$row['pd_importe'];
                  $saldoabono += $sumaingresos;
                  $ingresomenosa = ($row['pd_importe'] - $sumaingresos);
                  //}
                }
                $saldototaling = $ingresos-$saldoabono;
                echo '<tr>
                  <td></td>
                  <td class="number-align">Total</td>
                  <td class="number-align" colspan="2">$ '.number_format($ingresos,2).'</td>
                  <td class="number-align" colspan="2">$ '.number_format($saldoabono,2).'</td>
                  <td class="number-align" colspan="2">$ '.number_format($saldototaling,2).'</td>
                </tr>';
              echo '</table>
            </div>';
          echo '</div>';
          //Egresos
          echo '<div class="col-6 col-md-6">
            <div class="table-responsive">
              <table class="table table-striped text-medium">
                <thead class="thead bg-danger bg-darken-2 text-white pt-1 pb-1" >
                  <tr>
                    <th colspan="2" class="mt-1 mb-1">Egresos</th>
                    <th colspan="5"> 
                      <!-- 
                        <a accesskey="B" href="popup/importproyeccion?from=cxpagar&proyeccion='.$this->model->idp.'" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                          <button class="btn btn-secondary" ><i class="icon-mail-forward"></i> Importar egresos</button> 
                        </a>
                      -->
                    </th> 
                  </tr>
                  <tr>
                    <th>Estatus</th>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th>Importe</th>
                    <th>Abonado</th>
                    <th colspan="2">Saldo</th>
                  </tr>
                </thead>';
                $egresos = 0;
                $saldoegresoabono = 0;
                $sql = 'SELECT * FROM proyeccionesd WHERE pd_proyeccion = "'.$this->model->idp.'" AND pd_tipo = "P"
                        ORDER BY pd_fecha ASC';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  if($row['pd_fagregada'] != NULL) $f = 'pd_fagregada';
                  else $f = 'p_fechaap';

                  $sqlBUSCAR = 'SELECT SUM(ec_monto) FROM egreso_cxpagar INNER JOIN proyeccionesd ON ec_cxpagar = pd_referencia 
                                INNER JOIN proyecciones ON p_id = pd_proyeccion INNER JOIN egresos ON ec_egreso = e_id
                                WHERE p_id = "'.$this->model->idp.'" AND pd_referencia = "'.$row['pd_referencia'].'" AND e_estatus = "F"
                                AND pd_tipo = "P"';
                  if($this->model->estatus == "F") $sqlBUSCAR .= ' AND DATE(ec_fapli) BETWEEN DATE('.$f.') AND DATE(p_fechacierre)';
                  elseif($row['pd_fagregada'] != NULL) $sqlBUSCAR .= ' AND DATE(ec_fapli) >= DATE(pd_fagregada)';
                  else $sqlBUSCAR .= ' AND DATE(ec_fapli) >= DATE(p_fechaap)';
                  $resultBUSCAR = setq($sqlBUSCAR);
                  list($sumadeegresos) = $resultBUSCAR->fetch_array(); 

                  if($sumadeegresos > 0 && $sumadeegresos < $row['pd_importe']) $icon = '<i class="fa fa-plus warning" data-toggle="tooltip" title="Cuenta con abono"></i>';
                  elseif($sumadeegresos == 0) $icon = '<i class="fa fa-times danger" data-toggle="tooltip" title="Cuenta no abonada"></i>';
                  elseif($sumadeegresos == $row['pd_importe'] || $sumadeegresos > $row['pd_importe']) $icon = '<i class="fa fa-check green" data-toggle="tooltip" title="Cuenta alcanzada"></i>';
                  else $icon = '';
                  //if(busca($row['pd_referencia'],'cxpagar','cp_id','cp_estatus') == "F") $this->model->delproyecciond($row['pd_id']);
                  //else{
                  $egresomenosa = ($row['pd_importe'] - $sumadeegresos);
                  echo '<tr>
                    <td>'.$icon.'</td>
                    <td>'.date('d-m-Y',strtotime($row['pd_fecha'])).'</td>
                    <td>'.$row['pd_nmb'].'</td>
                    <td class="number-align">$'.number_format($row['pd_importe'],2).'</td>
                    <td colspan="2" class="number-align">$'.number_format($sumadeegresos,2).'</td>
                    <td class="number-align">$'.number_format($egresomenosa,2).'</td>
                    <!-- <td><button class="btn btn-danger" onclick="confcac('.$row['pd_id'].');"><i class="fa fa-trash"></i></button></td> -->
                  </tr>';
                  $egresos+=$row['pd_importe'];
                  $saldoegresoabono +=$sumadeegresos;
                  //}
                }
                $saldototaleg = $egresos-$saldoegresoabono;
                echo '<tr>
                  <td></td>
                  <td class="number-align">Total</td>
                  <td class="number-align" colspan="2">$ '.number_format($egresos,2).'</td>
                  <td class="number-align" colspan="2">$ '.number_format($saldoegresoabono,2).'</td>
                  <td class="number-align" colspan="2">$ '.number_format($saldototaleg,2).'</td>
                </tr>';
              echo '</table>
            </div>';
          echo '</div>';
          $meta = $ingresos-$egresos;
          if($meta < 0){
            $stylem = 'danger';
            $leym = 'Utilidad faltante para cubrir meta';
          }
        echo '</div>
      </div>';
    }
    function showliquidez(){
      $botones = "";
      if($this->model->estatus == "F") $disabled = "disabled";
      else $disabled = "";
      //Sección: E1 Encabezado - Botones de acción
      if($_GET['accion'] == 'showproyeccion') $active1 = 'active';
      elseif($_GET['accion'] == 'showproyeccionresumen') $active2 = 'active';
      elseif($_GET['accion'] == 'showliquidez') $active3 = 'active';
      $atras = '<a href="?modulo=cuentas&accion=proyecciones">
        <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Ver proyecciones </button>
      </a>';
      $botones .= '<a href="?modulo=cuentas&accion=showproyeccion&id='.$this->model->idp.'" class="btn btn-primary '.$active1.' ">Proyección</a>';
      $botones .=   '<a href="?modulo=cuentas&accion=showproyeccionresumen&id='.$this->model->idp.'" class="btn btn-success '.$active2.'">Resumen</a>';
      $botones .=   '<a href="?modulo=cuentas&accion=showliquidez&id='.$this->model->idp.'" class="btn btn-danger '.$active3.'">Liquidez</a>';
      if($this->model->estatus == "N"){
        $botones .= '<button onclick="cerrarp()" class="btn btn-danger">
          <i class="fa fa-times"></i> Finalizar Proyección
          </button>';
        echo '<script>
          function cerrarp(){
            var c = confirm("Al finalizar esta proyeccion los pagos realizados no formaran parte de este perido.");
            if(c){
              window.location.href="?modulo=cuentas&accion=cerrarproyeccion&id='.$this->model->idp.'"
            }
          }
        </script>';
      }
      toolbar($_GET['modulo'], $atras, "", "", $botones);
      
      echo '<div class="main-card mb-3 card">
        <div class="card-body row">';
          echo '<script>
            function confcac(idp){
              var conf = confirm("¿Deseas borrar este concepto de la proyeccion?");
              if(conf == true){
                window.location.href="?modulo=cuentas&accion=delproyecciond&proyeccion='.$this->model->idp.'&idp=" + idp;
              }
            }
          </script>';
          //Ingresos
          echo '<div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped text-medium">
                <thead class="thead bg-teal bg-darken-2 text-white pt-1 pb-1" style="background: teal;">
                  <tr>
                    <th colspan="2" class="mt-1 mb-1">Ingresos</th>
                    <th colspan="2"><!-- <a accesskey="B" href="popup/importproyeccion?from=cxcobrar&proyeccion='.$this->model->idp.'" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false"> 
                      <!-- <button class="btn btn-secondary"><i class="fas fa-reply"></i> Importar ingresos</button>-->
                    </th>
                  </tr>
                  <tr>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th>Importe---</th>
                    <!--<th></th>-->
                  </tr>
                </thead>';
                $ingresos = 0;
                $sql = 'SELECT * FROM proyeccionesd WHERE pd_proyeccion = "'.$this->model->idp.'" AND pd_tipo = "C"
                        ORDER BY pd_fecha ASC';
                $result = setq($sql);
                while($row = $result->fetch_array()){
                  //if(busca($row['pd_referencia'],'cxcobrar','cx_id','cx_estatus') == "F") $this->model->delproyecciond($row['pd_id']);
                  //else{
                  /*echo '<tr>
                    <td>'.date('d-m-Y',strtotime($row['pd_fecha'])).'</td>
                    <td>'.$row['pd_nmb'].'</td>
                    <td class="number-align">$ '.number_format($row['pd_importe'],2).'</td>
                    <td><button class="btn btn-danger" onclick="confcac('.$row['pd_id'].');"><i class="fa fa-trash"></i></button></td>
                  </tr>';
                  $ingresos+=$row['pd_importe'];*/
                  //}
                }
                //Saldo en cuentas
                $sql = 'SELECT cu_id,cu_nmb FROM cuentas WHERE cu_estatus = "A" AND cu_empresa = "1"  ORDER BY cu_orden';
                $result = setq($sql);
                $saldo = 0;
                while($row = $result->fetch_array()){
                  $saldo += saldo_actual($row['cu_id']);
                  /*
                  if($saldo > 0){
                    echo '<tr>
                      <td>-</td>
                      <td>'.$row['cu_nmb'].'</td>
                      <td class="number-align">$ '.number_format($saldo,2).'</td>
                    </tr>';
                    $ingresos+=$saldo;
                  }*/
                }
                echo '<tr>
                  <td>-</td>
                  <td>SALDO CAJAS</td>
                  <td class="number-align">$ '.number_format($saldo,2).'</td>
                </tr>';
                echo '<tr>
                  <td></td>
                  <td class="number-align">Total</td>
                  <td class="number-align">$ '.number_format($saldo,2).'</td>
                </tr>
                <input id="saldocajas" value="'.$saldo.'" hidden>';
              echo '</table>
            </div>';
          echo '</div>';
          //Egresos
          echo '<form action="?modulo=cuentas&accion=datosliquidez&id='.$this->model->idp.'" method="POST">
            <div class="col-md-12">
              <div class="table-responsive">
                <table class="table table-striped text-medium">
                  <thead class="thead bg-danger bg-darken-2 text-white pt-1 pb-1">
                    <tr>
                      <th colspan="2" class="mt-1 mb-1">Egresos</th>
                      <th colspan="2">
                        <a accesskey="B" href="popup/importproyeccion?from=cxpagar&proyeccion='.$this->model->idp.'" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                          <button '.$disabled.' class="btn btn-secondary btn-sm" ><i class="fas fa-share"></i> Importar egresos</button>
                        </a>
                        <button type="button" '.$disabled.' onclick="borrarvalores('.$this->model->idp.');" class="btn btn-sm btn-warning"><i class="fa fa-trash"></i> Limpiar Valores</button>
                      </th>
                      <th colspan="2">';
                        if($this->model->estatus != "F")
                          echo '<p>Liquidar todo</p>
                          <input class="flipswitchliquidar" type="checkbox" id="ptodo" onchange="liquidartodo();" style="height: 35px; font-size: 10px;" disabled>';
                      echo '</th>
                    </tr>
                    <tr>
                      <th>Fecha</th>
                      <th>Referencia</th>
                      <th>Importe</th>
                      <th>Liquido</th>
                      <th>Historial</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>';
                  $egresos = 0;
                  $sql = 'SELECT * FROM proyeccionesd WHERE pd_proyeccion = "'.$this->model->idp.'" AND pd_tipo = "P"
                          AND pd_estatus = "N" ORDER BY pd_liquidacion DESC'; //pd_fecha ASC
                  $result = setq($sql);
                  $contador = 0;
                  while($row = $result->fetch_array()){
                    $contador++;
                    //if(busca($row['pd_referencia'],'cxpagar','cp_id','cp_estatus') == "F") $this->model->delproyecciond($row['pd_id']);
                    //else{
                    echo '<tr>
                      <td>'.date('d-m-Y',strtotime($row['pd_fecha'])).'</td>
                      <td>'.$row['pd_nmb'].'</td>
                      <td class="number-align">$'.number_format($row['pd_importe'],2).'</td>
                      <td><input tabindex="1" '.$disabled.' class="form-control number-align liquidez" name="liquido['.$row['pd_id'].']" id="'.$row['pd_id'].'" value="'.$row['pd_liquidacion'].'" onfocus="this.select();" placeholder="Monto de liquidez" min="0" onkeyup="verificarmax('.$row['pd_id'].')" onchange="sumar('.$row['pd_id'].');" type="number" step="0.01" max="'.$row['pd_importe'].'" autofocus ></td>
                      <td>';
                        if(busca($row['pd_referencia'],'egreso_cxpagar','ec_cxpagar','COUNT(ec_id)') > 0){
                          echo '<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-target="#exampleModal'.$row['pd_id'].'">
                            <i class="icon-history2"></i>
                          </button>
                          <!-- Modal -->
                          <div class="modal fade" id="exampleModal'.$row['pd_id'].'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel">Abonos a la cxpagar '.$row['pd_id'].'</h5>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </button>
                                </div>
                                <div class="modal-body">
                                  <center>
                                    <div class="col-md-12 table-responsive">
                                      <table class="table-hover table-stripped table-bordered">
                                        <thead> 
                                          <tr>
                                            <th>CXP</th>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Usuario</th> 
                                          </tr>
                                        </thead>
                                        <tbody>';
                                          $sqlcx = 'SELECT * FROM egreso_cxpagar INNER JOIN cxpagar ON cp_id = ec_cxpagar 
                                                    WHERE ec_cxpagar = "'.$row['pd_referencia'].'" ORDER BY DATE(ec_fapli) DESC';
                                          $resultcx = setq($sqlcx);
                                          $sumahistorial = 0;
                                          while($rowcx = $resultcx->fetch_array()){
                                            echo '<tr>
                                              <td>'.$rowcx['cp_observaciones'].'</td>
                                              <td>'.$rowcx['ec_fapli'].'</td>
                                              <td class="number-align">$ '.$rowcx['ec_monto'].'</td>
                                              <td>'.$rowcx['ec_uapli'].'</td>
                                            </tr>';
                                            $sumahistorial += $rowcx['ec_monto'];
                                          }
                                          echo '<tr>
                                            <td colspan="2">Total: </td>
                                            <td class="number-align">$'.number_format($sumahistorial,2).'</td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </div>
                                  </center>
                                </div>
                                <div class="modal-footer" style="border-top: 0px;">
                                </div>
                              </div>
                            </div>
                          </div>';
                        }             
                      echo '</td>
                      <td><button '.$disabled.' class="btn btn-danger" onclick="confcac('.$row['pd_id'].');"><i class="fa fa-trash"></i></button></td>
                    </tr>';
                    $egresos+=$row['pd_importe'];
                    //}
                  }
                  echo '<tr>
                    <td></td>
                    <td class="number-align">Total</td>
                    <td class="number-align">$ '.number_format($egresos,2).'</td>
                    <td id="totalsumaegresos">$ 0.00</td>
                  </tr>
                  <input value="'.$egresos.'" id="totalegresos" hidden>';
                echo '</table>
              </div>';
            echo '</div>';
            $meta = $ingresos-$egresos;
            if($meta < 0){
              $stylem = 'danger';
              $leym = 'Utilidad faltante para cubrir meta';
            }
            echo '<div class="col-md-12 text-xs-center p-1">
              <button type="submit" '.$disabled.' class="btn btn-success"><i class="fa fa-save"></i> Guardar datos</button>
              <!-- <a class="btn btn-info" href="#"><i class="fa fa-file-pdf"></i> Generar reporte</a> -->
            </div></div>
          </form>
          <button type="button" class="btn-flotante btn-round btn-lg" data-toggle="tooltip" title="Saldo en cajas es: $'.number_format($saldo,2).'"><i class="icon-dollar"></i></button>';
          echo '<script>
            function confcac(idp){
              var conf = confirm("¿Deseas borrar este concepto de la proyeccion?");
              if(conf == true){
                window.location.href="?modulo=cuentas&accion=delproyecciond&proyeccion='.$this->model->idp.'&idp=" + idp;
              }
            }
          </script>';
          ?><script>
            function verificarmax(id){
              var a = document.getElementById(id);
              if (parseFloat(a.value) < 0){
                a.value = 0;
              }
              if (parseFloat(a.value) > parseFloat($('#'+id).attr('max'))){
                a.value = parseFloat($('#'+id).attr('max'));
              }
            }
            function sumar(id){
              var valorsobrepasa = document.getElementById(id); 
              var suma = 0;
              $('.liquidez').each(function(){
                suma = parseFloat(suma) + parseFloat($(this).val());
              });
              if(parseFloat(suma) == parseFloat(document.getElementById("saldocajas").value)){
                $('.liquidez').each(function(){
                  if(parseFloat($(this).val()) == 0){
                    $(this).prop("readonly",true);
                  }
                });
              }else if(parseFloat(suma) > parseFloat(document.getElementById("saldocajas").value)){
                alert("El saldo en cajas no se puede superar");
                $('.liquidez').each(function(){
                  if(parseFloat($(this).val()) == 0){
                    $(this).prop("readonly",true);
                  }
                });
                valorsobrepasa.value = 0;
              }else{
                $('.liquidez').each(function(){
                  if(parseFloat($(this).val()) == 0){
                    $(this).prop("readonly",false);
                  }
                });
              }
              $("#totalsumaegresos").html("$ "+suma.toLocaleString('en-US', {minimumFractionDigits: 2}));
            }
            function pagartodo(){
              if(parseFloat(document.getElementById("saldocajas").value) >= parseFloat(document.getElementById("totalegresos").value)){
                $("#ptodo").prop("disabled",false);
              }else{
                $("#ptodo").prop("disabled",true);
              } 
            }
            function liquidartodo(){
              if($("#ptodo").prop("checked") == true){
                $('.liquidez').each(function(){
                  document.getElementById($(this).attr("id")).value =  parseFloat($(this).attr('max'));
                });
                sumar(undefined);
              }else{
                $('.liquidez').each(function(){
                  document.getElementById($(this).attr("id")).value =  0;
                });
                sumar(undefined);
              }
            }
            function borrarvalores(id){
              var c = confirm("¿Estás seguro de eliminar los valores de liquidación en la tabla?");
              if(c){
                window.location.href="?modulo=cuentas&accion=borrarvalores&id="+id;
              }
            }
            pagartodo(); 
            sumar();
          </script><?php
        echo '</div>
      </div>';
    }
    function showcorte(){
      /*
        echo '<div class="form-inline mb-5">
          <a href="?modulo=cuentas&accion=index" onclick="this.onclick = function(){return false;}">
            <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atrás </button>
          </a>
          <!-- 
            <a data-fancybox data-type="ajax" data-src="popup/setcuenta.php?id='.$this->model->id.'" href="javascript:;" >
              <button type="button" class="btn btn-info"><i class="fa fa-pen"></i> Editar </button>
            </a>
          -->';
          echo '<a href=?modulo=cuentas&accion=cortes&id=' . $this->model->id . ' title="Ver cortes" onclick="this.onclick = function(){return false;}">
            <button class="btn btn-primary"><i class="icon-calendar4"></i> Periodos</button>
          </a>';
          if ($this->model->estatus == "A") {
            echo '<a href=?modulo=cuentas&accion=disable&id='.$this->model->id.' title="Deshabilitar Cuenta" onclick="this.onclick = function(){return false;}">
              <button class="btn btn-danger"><i class="fa fa-times"></i> Desactivar cuenta</button>
            </a>';
          } else {
            echo '<a href=?modulo=cuentas&accion=enable&id='.$this->model->id.' title="Habilitar Cuenta" onclick="this.onclick = function(){return false;}">
              <button class="btn btn-success"><i class="fa fa-check"></i> Activar cuenta</button>
            </a>';
          }
        echo '</div></div>';
      */
      echo '<div class="table-responsive">
        <table width="90%" border="0" class="table table-hover">';
          echo '<thead class="thead bg-primary text-white pt-1 pb-1">
            <tr>
              <th align="center"><strong>Nombre de la Cta.</strong></th>
              <th align="center"><strong>Nombre del Titular</strong></th>
              <th align="center"><strong>Tipo de Cta</strong></th>
            </tr>
          </thead>';
          echo '<tr>
            <td>'.$this->model->nmb.'</td>
            <td>'.$this->model->propietario.'</td>
            <td>'.$this->tipocta[$this->model->tipo].'</td>
          </tr>'; 
          /*
            echo '<tr class="bg-primary  text-white pt-1 pb-1">
              <th align="center"><strong>Saldo inicial</strong></th>
              <th align="center"><strong>Saldo limite</strong></th>
              <th align="center"><strong>Saldo actual</strong></th>
            </tr>'; 
            echo '<tr>
              <td>$'.number_Format(busca($this->model->id,'cortes','c_estatus = "A" AND c_cuenta','c_saldo'),2).'</td>
              <td>$'.$this->model->limite.'</td>
              <td>$'.number_format(saldo_actual($this->model->id),2).'</td>
            </tr>';
          */
        echo '</table></center>';
        $cortea = busca($this->model->id,'cortes','c_id = "'.$_GET['id'].'" AND c_cuenta','c_id');
        if(!$cortea){    
          echo ' <div class="col-md-12" oncontextmenu="return false" onkeydown="return false">
            <center>
              <img src="http://jdceo.jdsuite.mx/images/cuentas.gif" align="center" class="img-fluid" width="90%"/>
            </center>
          </div>
          <div class="col-md-1 col-xs-2" hidden>
            <a href="?modulo=clientes&accion=direcciones&cliente='.$_GET['cliente'].'">
              <button type="button" class="btn btn-red" align="left">
                <i class="icon fa fa-times"></i>
              </button>
            </a>
          </div>';
        }else
          //echo '<hr width="97%">';
        $tipom = array("P"=>"ABONO","C"=>"RETIRO");
        $tipo = array("E"=>"EFECTIVO","D"=>"DEBITO","C"=>"CREDITO","A"=>"AHORRO");
        ?><script>
          function habilita(){
            if(document.getElementById("filtro").checked){
              document.getElementById("periodo").hidden=false;
              document.getElementById("fini").hidden=true;
              document.getElementById("ffin").hidden=true;
              document.getElementById("filtros").submit();
            }else{
              document.getElementById("periodo").hidden=true;
              document.getElementById("fini").hidden=false;
              document.getElementById("ffin").hidden=false;
              document.getElementById("filtros").submit();
            }
          }
        </script><?php
        $aux1=""; $aux2="hidden"; $aux3=""; $aux4="";
        if(!isset($_GET['id']))   $_POST['corte'] = NULL; else $_POST['corte'] = $_GET['id'];
        if(!isset($_POST['fini']))    $_POST['fini'] = date('Y-m-d',strtotime(busca($this->model->id,'cortes','c_id = "'.$_GET['id'].'" AND c_cuenta','c_fini')));
        if(!isset($_POST['ffin']))    $_POST['ffin'] = date('Y-m-d',strtotime(busca($this->model->id,'cortes','c_id = "'.$_GET['id'].'" AND c_cuenta','c_ffin')));
        if(!isset($_POST['filtro']))    $_POST['filtro'] = NULL;
        else{
          $aux1=" checked "; $aux2=""; $aux3=" hidden"; $aux4=" hidden";
        }
        echo '<center>
          <form name="filtros" id="filtros" method="post">
            <div class="row" style="display: flex;">';
              echo '<div class="bg-primary  text-white col-2 col-md-2 p-1">
                <label class="text-white">Fecha Inicial: </label>
              </div>
              <div class="bg-primary  text-white col-4 col-md-2 p-1">
                <input type="date" min="'.$_POST['fini'].'" value="'.$_POST['fini'].'" id="fini" name="fini" '.$aux3.' class="form-control" readonly>
              </div>';
              echo '<div class="bg-primary  text-white col-4 col-md-2 p-1">
                <label class="text-white">Fecha final: </label>
              </div>
              <div class="bg-primary  text-white col-4 col-md-2 p-1">
                <input type="date" value="'.$_POST['ffin'].'" max="'.$_POST['ffin'].'" id="ffin" name="ffin" '.$aux4.' class="form-control" readonly>
              </div>';
              echo'<div class="thead bg-primary  text-white p-1 col-4 col-md-2">&nbsp;
              </div>
              <div class="thead bg-primary text-white  p-1 col-4 col-md-2">
                <a href="formats/xlsestadocuenta.php?cuenta='.$this->model->id.'&corte='.$_GET['id'].'&filtro='.$_POST['filtro'].'&fini='.$_POST['fini'].'&ffin='.$_POST['ffin'].'" target="_blank">
                  <button type="button" id="actualizar" class="btn btn-success"/><i class="icon-file-excel-o"></i> Descargar excel</button>
                </a>
              </div>
            </div>
          </form>';
        echo '</center>  ';
        if(!isset($_GET['id']))   $_POST['corte'] = NULL; else $_POST['corte'] = $_GET['id'];
        if(!isset($_POST['fini']))    $_POST['fini'] = date('Y-m-01');
        if(!isset($_POST['ffin']))    $_POST['ffin'] = date('Y-m-d');
        if(!isset($_POST['filtro']))    $_POST['filtro'] = NULL;
        $sql1 = ""; $sql2 = "";
        if($_POST['filtro']) {
          if($_POST['corte']){
            $sql1.=' AND  c_id="'.$_POST['corte'].'" ';
            $sql2.= ' AND (t_origencorte="'.$_POST['corte'].'" OR t_destinocorte="'.$_POST['corte'].'")';
            $ftemp1=busca($_POST['corte'],'cortes','c_id','c_fini');
            $ftemp2=busca($_POST['corte'],'cortes','c_id','c_ffin');
            $aux1='al '.fecha_formato($ftemp1,false,true);
            $saldoinicial=busca($_POST['corte'],'cortes','c_id','c_saldo');
            $aux2=' al '.fecha_formato($ftemp2,false,true);
          }
        }
        else{
          $sql1.=' AND DATE(cd_fecha) BETWEEN "'.$_POST['fini'].'" AND "'.$_POST['ffin'].'"';
          $sql2.= ' AND DATE(t_fgen) BETWEEN "'.$_POST['fini'].'" AND "'.$_POST['ffin'].'"';
          $aux1= ' al '.fecha_formato($_POST['fini'],false,true);
          $saldoinicial=busca($this->model->id,'cortes','c_id = "'.$_GET['id'].'" AND c_cuenta','c_saldo');
          $aux2=' al '.fecha_formato($_POST['ffin'],false,true);
        }
        $sql= 'SELECT cd_tipo,cd_monto,cd_idref,cd_fecha fechagen,cd_gen,cd_tipom,cd_cuenta,cd_reverso,cd_corte, cd_observaciones
              FROM cortes INNER JOIN cortesd ON c_id=cd_corte
              WHERE c_cuenta="'.$this->model->id.'" AND cd_condonar="0" '.$sql1.'
              UNION
              SELECT "P",t_importe,t_id,t_fgen,t_ugen,"TR",t_destino,NULL,NULL, NULL FROM traspasos
              WHERE t_origen="'.$this->model->id.'"
              '.$sql2.'
              UNION
              SELECT "C",t_importe,t_id,t_fgen,t_ugen,"TR",t_origen,NULL,NULL, NULL FROM traspasos
              WHERE t_destino="'.$this->model->id.'"
              '.$sql2.'
              ORDER BY fechagen ASC';
        $result = setq($sql) or die($sql);
        echo '<div class="table-responsive">
          <table border="0" class="table"  width="100%">
            <thead class="thead bg-primary  text-white pt-1 pb-1">
              <tr><th colspan="6"><b>MOVIMIENTOS</b></th></tr>
              <tr>
                <th><b>Fecha</b></th>
                <th><b>Generó</b></th>
                <th><b>Descripcion</b></th>
                <th width="15%"><b>Depositos</b></th>
                <th width="15%"><b>Retiros</b></th>
                <th width="15%"><b>Saldo</b></th>
              </tr>
            </thead>
            <tbody>';
              $fechacorte = busca($this->model->id,'cortes','c_id = "'.$_GET['id'].'" AND c_cuenta','c_fini');
              $color1='style="background-color:#49E67D"';
              echo '<tr>
                <td>'.fecha_formato($fechacorte,true,true).'</td>
                <td> </td>
                <td> SALDO INICIAL </td>
                <td '.$color1.' class="number-align">$'.number_format($saldoinicial,2).'</td>
                <td class="number-align">'.$retiros.'</td>
                <td class="number-align">$'.number_format($saldoinicial,2).'</td>';
              echo'</tr>';
              $totala=0; 
              $totalr=0;
              $saldot=$saldoinicial;
              $totala+=$saldoinicial;
              while($row = $result->fetch_array()){
                if($row['cd_tipo']=="P"){
                  $totalr=$totalr+$row['cd_monto'];
                  $depositos='-';   $color1='';
                  $retiros='$'.number_format($row['cd_monto'],2); $color2='style="background-color:#E67D31"';
                  $saldot=$saldot-$row['cd_monto'];
                  if($row['cd_tipom'] == "TR") $desc = 'TRASPASO ENVIADO A '.busca($row['cd_cuenta'],'cuentas','cu_id','cu_nmb');
                  elseif($row['cd_tipom'] == "X"){
                    $idr = busca($row['cd_reverso'],'cortesd','cd_corte="'.$row['cd_corte'].'" AND cd_id','cd_idref');
                    $sqlcx = 'SELECT ic_cxcobrar,ic_ingreso FROM ingreso_cxcobrar WHERE ic_ingreso = "'.$idr.'" ';
                    $resultcx = setq($sqlcx) or die($sqlcx);
                    $desc = "REVERSO DEL MOVIMIENTO ";
                    if($resultcx->num_rows == 0) $desc .= busca($idr,'ingresos','i_id','i_observaciones');
                    else{
                      while($rowcx = $resultcx->fetch_array()){
                        $sqlxp = 'SELECT * FROM cxcobrar WHERE cx_id = "'.$rowcx['ic_cxcobrar'].'" ';
                        $resultxp = setq($sqlxp) or die($sqlxp);
                        $i = 0;
                        while($rowxp = $resultxp->fetch_array()){
                          if($rowxp['cx_tipo'] == "R") $desc .= 'Remisión '.busca($rowxp['cx_referencia'],'remisiones','r_id','r_folio');
                          elseif($rowxp['cx_tipo'] == "S") $desc .= "SOPORTE ".$rowxp['cx_referencia'].' de '.busca($rowxp['cx_referencia'],'proyectos','p_id','p_nmb');
                          //elseif($rowxp['cx_tipo'] == "P") $desc .= busca($rowxp['cx_referencia'],'prestamos','pr_id','pr_descripcion');
                          elseif($rowxp['cx_tipo'] == "P") $desc .= "RESTAMO DE ".busca($rowxp['cx_referencia'],'ordenesp','op_id','op_nmb');
                          elseif($rowxp['cx_tipo'] == "I") $desc .= busca($rowxp['cx_referencia'],'comisiones','c_id','c_descripcion');
                          elseif($rowxp['cx_tipo'] == "V") $desc .= busca(busca($rowxp['cx_referencia'],'valesp','vp_id','vp_vale'),'vales','v_id','v_nmb').' - '.busca($rowxp['cx_idref'],'valesp','vp_id','vp_descripcion');
                          elseif($rowxp['cx_tipo'] == "T") $desc .= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cx_fini']));
                          elseif($rowxp['cx_tipo'] == "A") $desc .= "COTIZACION ".busca($rowxp['cx_referencia'],'cotizaciones','c_id','c_cotizacion');
                          elseif($rowxp['cx_tipo'] == "Z") $desc .= "POLIZA ".busca($rowxp['cx_referencia'],'remisiones','r_id','r_nmb');
                          if(!$desc) $desc .= busca($rowcx['ic_ingreso'],'ingresos','i_id','i_observaciones');
                        }
                      }
                    }
                  }else if ($row['cd_tipom'] == "C"){
                    $desc = 'ENTREGA DE EFECTIVO';
                  }else{
                    $sqlcx = 'SELECT ec_cxpagar,ec_egreso FROM egreso_cxpagar WHERE ec_egreso = "'.$row['cd_idref'].'"';
                    $resultcx = setq($sqlcx) or die($sqlcx);
                    $desc = "";
                    $div = '';
                    if($resultcx->num_rows == 0) $desc = busca($row['cd_idref'],'egresos','e_id','e_observaciones');
                    elseif($resultcx->num_rows > 1) $div = ' / ';
                    while($rowcx = $resultcx->fetch_array()){
                      $sqlxp = 'SELECT * FROM cxpagar WHERE cp_id = "'.$rowcx['ec_cxpagar'].'" ';
                      $resultxp = setq($sqlxp) or die($sqlxp);
                      $i = 0;
                      while($rowxp = $resultxp->fetch_array()){
                        $desc .= $div;
                        if($rowxp['cp_tipo'] == "O") $desc.= "ORDEN PAGO ".busca($rowxp['cp_idref'],'ordenesp','op_id','op_nmb');
                        elseif($rowxp['cp_tipo'] == "C") $desc.= "COMPRAS ".busca($rowxp['cp_idref'],'ordenesc','o_id','o_folio');
                        elseif($rowxp['cp_tipo'] == "F") $desc.= busca($rowxp['cp_idref'],'gastosf','gf_id','gf_nmb').' - '.date('d-m-Y',strtotime($rowxp['cp_fini']));
                        elseif($rowxp['cp_tipo'] == "R") $desc.= busca($rowxp['cp_idref'],'prestamos','pr_id','pr_descripcion');
                        elseif($rowxp['cp_tipo'] == "G" || $rowxp['cp_tipo'] == "V") $desc.= busca($rowxp['cp_idref'],'gastos','g_id','g_descripcion');
                        elseif($rowxp['cp_tipo'] == "T") $desc.= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cp_fini']));
                        elseif($rowxp['cp_tipo'] == "M") $desc.= "ORDEN DE COMPRA MENOR";
                        elseif($rowxp['cp_tipo'] == "D") $desc .= $rowxp['cp_observaciones'];
                        elseif($rowxp['cp_tipo'] == "E") $desc .= $rowxp['cp_observaciones'];
                        else {
                          if(!$row['cd_observaciones']) $desc = busca($rowcx['ec_egreso'],'egresos','e_id','e_observaciones');
                          else $desc = $row['cd_observaciones'];
                        }
                        $i++;
                      }
                    }
                  }
                }else{
                  $totala=$totala+$row['cd_monto'];
                  $depositos= '$'.number_format($row['cd_monto'],2);  $color1='style="background-color:#49E67D"';
                  $retiros='-';  $color2='';
                  $saldot=$saldot+$row['cd_monto'];
                  if($row['cd_tipom'] == "TR") $desc = 'TRASPASO COBRADO DESDE '.busca($row['cd_cuenta'],'cuentas','cu_id','cu_nmb');
                  elseif($row['cd_tipom'] == "X"){
                    $idr = busca($row['cd_reverso'],'cortesd','cd_corte="'.$row['cd_corte'].'" AND cd_id','cd_idref');
                    $sqlcx = 'SELECT ec_cxpagar,ec_egreso FROM egreso_cxpagar WHERE ec_egreso = "'.$idr.'"';
                    $resultcx = setq($sqlcx) or die($sqlcx);
                    $desc = "REVERSO DEL MOVIMIENTO ";
                    $div = '';
                    if($resultcx->num_rows == 0) $desc .= busca($idr,'egresos','e_id','e_observaciones');
                    elseif($resultcx->num_rows > 1) $div = ' / ';
                    while($rowcx = $resultcx->fetch_array()){
                      $sqlxp = 'SELECT * FROM cxpagar WHERE cp_id = "'.$rowcx['ec_cxpagar'].'" ';
                      $resultxp = setq($sqlxp) or die($sqlxp);
                      $i = 0;
                      while($rowxp = $resultxp->fetch_array()){
                        $desc .= $div;
                        if($rowxp['cp_tipo'] == "O") $desc.= "ORDEN PAGO ".busca($rowxp['cp_idref'],'ordenesp','op_id','op_nmb');
                        elseif($rowxp['cp_tipo'] == "C") $desc.= "COMPRAS ".busca($rowxp['cp_idref'],'ordenesc','o_id','o_folio');
                        elseif($rowxp['cp_tipo'] == "F") $desc.= busca($rowxp['cp_idref'],'gastosf','gf_id','gf_nmb').' - '.date('d-m-Y',strtotime($rowxp['cp_fini']));
                        elseif($rowxp['cp_tipo'] == "R") $desc.= busca($rowxp['cp_idref'],'prestamos','pr_id','pr_descripcion');
                        elseif($rowxp['cp_tipo'] == "G" || $rowxp['cp_tipo'] == "V") $desc.= busca($rowxp['cp_idref'],'gastos','g_id','g_descripcion');
                        elseif($rowxp['cp_tipo'] == "T") $desc.= "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cp_fini']));
                        elseif($rowxp['cp_tipo'] == "D") $desc .= $rowxp['cp_observaciones'];
                        else {
                          if(!$row['cd_observaciones']) $desc .= busca($rowcx['ec_egreso'],'egresos','e_id','e_observaciones');
                          else $desc .= $row['cd_observaciones'];
                        }
                        $i++;
                      }
                    }
                  }else if ($row['cd_tipom'] == "C"){
                    $desc = $row['cd_observaciones'];
                  }
                  else{
                    $sqlcx = 'SELECT ic_cxcobrar,ic_ingreso FROM ingreso_cxcobrar WHERE ic_ingreso = "'.$row['cd_idref'].'" ';
                    $resultcx = setq($sqlcx) or die($sqlcx);
                    $desc = "";
                    if($resultcx->num_rows == 0) $desc = busca($row['cd_idref'],'ingresos','i_id','i_observaciones');
                    while($rowcx = $resultcx->fetch_array()){
                      $sqlxp = 'SELECT * FROM cxcobrar WHERE cx_id = "'.$rowcx['ic_cxcobrar'].'" ';
                      $resultxp = setq($sqlxp) or die($sqlxp);
                      $i = 0;
                      while($rowxp = $resultxp->fetch_array()){
                        if($rowxp['cx_tipo'] == "R") $desc = 'Remisión '.busca($rowxp['cx_referencia'],'remisiones','r_id','r_folio');
                        elseif($rowxp['cx_tipo'] == "S") $desc = "SOPORTE ".$rowxp['cx_referencia'].' de '.busca($rowxp['cx_referencia'],'proyectos','p_id','p_nmb');
                        //elseif($rowxp['cx_tipo'] == "P") $desc = busca($rowxp['cx_referencia'],'prestamos','pr_id','pr_descripcion');
                        elseif($rowxp['cx_tipo'] == "P") $desc = "PRESTAMO DE ".busca($rowxp['cx_referencia'],'ordenesp','op_id','op_nmb');
                        elseif($rowxp['cx_tipo'] == "I") $desc = busca($rowxp['cx_referencia'],'comisiones','c_id','c_descripcion');
                        elseif($rowxp['cx_tipo'] == "V") $desc = busca(busca($rowxp['cx_referencia'],'valesp','vp_id','vp_vale'),'vales','v_id','v_nmb').' - '.busca($rowxp['cx_idref'],'valesp','vp_id','vp_descripcion');
                        elseif($rowxp['cx_tipo'] == "T") $desc = "TRASPASO TAE ".date('d-m-y',strtotime($rowxp['cx_fini']));
                        elseif($rowxp['cx_tipo'] == "A") $desc = "COTIZACION ".busca($rowxp['cx_referencia'],'cotizaciones','c_id','c_cotizacion');
                        elseif($rowxp['cx_tipo'] == "Z") $desc = "POLIZA ".busca($rowxp['cx_referencia'],'remisiones','r_id','r_nmb');
                        if(!$desc) $desc = busca($rowcx['ic_ingreso'],'ingresos','i_id','i_observaciones');
                      }
                    }
                  }
                }
                echo '<tr>
                  <td>'.fecha_formato($row['fechagen'],true,true).'</td>
                  <td>'.$row['cd_gen'].'</td>
                  <td>'.$desc.'</td>
                  <td '.$color1.' class="number-align">'.$depositos.'</td>
                  <td '.$color2.' class="number-align">'.$retiros.'</td>
                  <td class="number-align">$'.number_format($saldot,2).'</td>';
                echo ' </tr>';
              }
              echo '<tr>
                <td colspan="3" style="text-align:right;"><b>Totales</b></td>
                <td class="number-align"><b>$'.number_format($totala,2).'</b></td>
                <td class="number-align"><b>$ '.number_format($totalr,2).'</b></td>
                <td class="number-align"><b>$ '.number_format($saldot,2).'</b></td>
              </tr>';
            echo '</tbody>
          </table>
        </div</center>
      </div>';
    }
  }
?>
