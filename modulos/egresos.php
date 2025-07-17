<?php
  //ini_set('display_errors','1');
  $GLOBALS['menu'] = "egresos";
  class egresos{
    var $model;
    var $view;
    function __construct(){ // constructor
      $this->model= new Modelegresos; // crea modelo
    }
    function index(){
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if(!isset($_REQUEST['fini']))    $_REQUEST['fini'] = date('Y-m-01');
      if(!isset($_REQUEST['ffin']))    $_REQUEST['ffin'] = date('Y-m-d');
      if(!isset($_REQUEST['proveedor']))    $_REQUEST['proveedor'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->result($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin'],trim($_REQUEST['proveedor']),$_REQUEST['page']);
      $this->view=new Viewegresos($this->model);
      $this->view->browse($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['page']);
    }
    function edit() {
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new Viewegresos($this->model);
      $this->view->edit();
    }
    function show() {
      $proveedor=busca($_GET['id'],'egresos','e_id','e_proveedor');
      if(!isset($_REQUEST['fini'])){
        $fini1 = busca($proveedor,'cxpagar','(cp_estatus = "N" OR cp_estatus="A") AND cp_proveedor','DATE(MIN(cp_fini))');
        if(!$fini1) $_REQUEST['fini'] = date('Y-m-d');
        else $_REQUEST['fini'] = date('Y-m-d',strtotime($fini1));
      }
      if(!isset($_REQUEST['ffin'])){
        $maxdate = busca($proveedor,'cxpagar','(cp_estatus = "N" OR cp_estatus="A") AND cp_proveedor','DATE(MAX(cp_fini))');
        if(isset($_GET['idcxp'])) $_REQUEST['ffin'] =  date('Y-m-d',strtotime($maxdate.' + 1 day'));
        else $_REQUEST['ffin'] = date('Y-m-d',strtotime('sunday this week'));
      }
      if(!isset($_REQUEST['tipo']))    $_REQUEST['tipo'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new Viewegresos($this->model);
      $this->view->show($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['tipo']);
    }
    function insert(){
      //Verifico cuenta activa
      $fecha = str_replace("T"," ",$_POST['fecha']);
      include('cuentas.php');
      $cuent = new modelcuentas();
      $okcta = $cuent->cuentaactiva($_POST['cuenta']);
      if($okcta == "NOTOK"){
        echo '<script>
          var conf = confirm("La fecha de corte de la cuenta ha finalizado, ¿Desea abrir un corte nuevo a la cuenta?")
          if(conf == false){document.location.href="?modulo=egresos&accion=edit";}
          else{document.location.href="?modulo=egresos&accion=cortenuevo&cuenta='.$_POST['cuenta'].'&nextmod=egresos&nexta=edit&idcxp='.$_GET['idcxp'].'";}
        </script>';
        $link=' ?modulo=egresos&accion=edit';
      }else{
        $corte = $okcta;
        $proveedor = $_REQUEST['proveedor'];
        //ID del corted
        $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
        $result = setq($sql);
        list($idcd) = $result->fetch_array();
        $idcd++;
        if(!$proveedor)
          die(alert_back('El proveedor ingresado no se encuentra en la lista de proveedores',true));

        $fechareg=date('Y-m-d H:i:s');
        $user=$_SESSION['uid'];
        $estatus='N';

        if(isset($_FILES['adjunto']['name'])){
          $diradj = 'Adjuntos/'.$_SESSION['emp'];
          if (!is_dir($diradj)) {
            @mkdir($diradj, 0777);
          }
          $info = new SplFileInfo(basename($_FILES['adjunto']['name']));
          $ext = $info->getExtension();
          $gid = getmax('e_id','egresos');
          $target = $diradj.'/egreso'.$gid.'.'.$ext;
          if(move_uploaded_file($_FILES['adjunto']['tmp_name'], $target)) $target = 'egreso'.$gid.'.'.$ext;
          else $target = NULL;
        }else $target = NULL;

        if(isset($_POST['importelote']) && $_POST['importelote'] < $_POST['importe']) $estlote = 'A';
        else $estlote = 'F';

        $this->model->setData(NULL,$_SESSION['emp'],$_POST['importe'],$proveedor,$_POST['referencia'],$_POST['cuenta'],$fecha,$fechareg,$user,$estatus,$_POST['observaciones'],$target,$corte);
        $this->model->insert();

        $tipoap = busca($_GET['idcxp'],'cxpagar','cp_id','cp_tipo');
        if($tipoap == "V") $tp = 'V';
        else $tp = 'C';
        $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"P",$this->model->idegr,$_POST['importe'],$tp,$_POST['cuenta']);
        $cuent->insertcd();

        $j = 0;
        if(isset($_POST['idlist'])) $j = sizeof($_POST['idlist']);
        else if(isset($_GET['idcxp']))$j = 1;
    
        if(isset($_GET['idcxp']) || isset($_POST['idlist'])){
          for($i=0;$i<$j;$i++){
            if(isset($_POST['idlist'])) {
              $idcp = $_POST['idlist'][$i];
              $abonado = $_POST['saldolist'][$i];
              $importelt = $_POST['saldolist'][$i];
            }else {
              $idcp = $_GET['idcxp'];
              $importelt = $_POST['importe'];
              $abonado = $_POST['importe'];//busca($_GET['idcxp'],'cxpagar','cp_id','(cp_importe - cp_abonado)');
            }
            $this->model->asignar($this->model->idegr,$idcp,$abonado,$importelt);
          }
          $sqli = 'UPDATE egresos SET e_estatus = "'.$estlote.'" WHERE e_id="'.$this->model->idegr.'"';
          setq($sqli) or die($sqli);
          if(isset($_GET['from'])) $link = '?modulo=gastos&accion=index';
          else $link='?modulo=cxpagar&accion=index';
        }else{
          $link='?modulo=egresos&accion=show&id='.$this->model->idegr.'&from='.$_GET['from'].'&idcxp='.$_GET['idcxp'];
        }
      }
      redirect($link);
    }
    function detalles(){
      $sql='SELECT * FROM egresos WHERE e_id="'.$_GET['id'].'"';
      $result=setq($sql) or die($sql);
      $row=$result->fetch_array();
      echo '<table class="lista" border="0" cellspacing="3" width="100%">';
        echo '<thead><tr><th colspan="4"><b>egreso '.$row['e_id'].'</b></th></tr></thead>';
        echo '<tr>
          <th>Adjunto</th>
          <th colspan="2">Observaciones</th>
        </tr>';
        echo '<tr>';
          if($row['e_adjunto']){
            $ext=end(explode(".", $row['e_adjunto']));
            echo '<td>';
              if(in_array($ext, $this->extenciones))
              echo '<a target="_blank" href="'.$row['e_adjunto'].'"><input type="button" value="" class="botont" id="mostrar"></a><br>';
              echo '<a href="'.$row['e_adjunto'].'" download="Comprobante gasto variable '.$row['e_id'].'">Descargar Archivo</a>';
            echo '</td>';
          }else{
            echo '<td></td>';
          }
          echo '<td colspan="2">'.$row['e_observaciones'].'</td>';
        echo '</tr>
      </table>';
    }
    function asignar(){
      if(!isset($_GET['tipo']))    $_GET['tipo'] = NULL;

      $sql='SELECT e_monto,e_proveedor,e_cuenta,e_corte FROM egresos WHERE e_id="'.$_GET['id'].'"';
      $result=setq($sql);
      list($monto,$proveedor,$cuenta,$corte)=$result->fetch_array();

      $sql='SELECT * FROM cxpagar WHERE cp_proveedor="'.$proveedor.'"  AND cp_estatus IN ("A","N")
            AND DATE(cp_fini) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" ';
      if($_GET['tipo']){
        $sql.=' AND cp_tipo="'.$_GET['tipo'].'"';
      }
      $result=setq($sql);
      while($row=$result->fetch_array()){
        if(($_POST['c'.$row['cp_id']]) AND ($_POST['i'.$row['cp_id']]>0)){
          $this->model->asignar($_GET['id'],$row['cp_id'],$_POST['i'.$row['cp_id']],$row['cp_abonado']);
        }
      }
      $egreso = $_GET['id'];

      $sqlm = 'SELECT * FROM egreso_cxpagar INNER JOIN egresos ON e_id = ec_egreso
                WHERE ec_egreso="'.$egreso.'" AND ec_egreso != "C"';
      $resultm = setq($sqlm);
      $total = 0;
      while($rowi = $resultm->fetch_array()){
        $total+=$rowi['ec_monto'];
      }
      
      if($total >= $monto){
        $sqli = 'UPDATE egresos SET e_estatus = "F" WHERE e_id="'.$egreso.'"';
        setq($sqli);
      }elseif($total > 0){
        $sqli = 'UPDATE egresos SET e_estatus = "A" WHERE e_id="'.$egreso.'"';
        setq($sqli);
      }

      if(isset($_GET['cxpagar'])) $link='?modulo=cxpagar&accion=index';
      else $link='?modulo=egresos&accion=index';

      redirect($link);
    }
    function fondear(){
      $sql='SELECT e_monto,e_proveedor,e_cuenta,e_corte FROM egresos WHERE e_id="'.$_GET['id'].'"';
      $result=setq($sql) or die($sql);
      list($monto,$proveedor,$cuenta,$corte)=$result->fetch_array();
      $fecha=date('Y-m-d H:i:s');
      $user=$_SESSION['uid'];

      $asignado=busca($_GET['id'],'egreso_cxpagar','ec_egreso','SUM(ec_monto)');
      $monto=$monto-$asignado;

      $sql='SELECT * FROM cxpagar WHERE cp_proveedor="'.$proveedor.'"  AND cp_estatus IN ("A","N") ORDER BY cp_fini ASC ';
      $result=setq($sql) or die($sql);
      $temp=$monto;
      $asigtot = 0;
      while($row=$result->fetch_array()){
        if($temp>0){
          $saldo=$row['cp_importe']-$row['cp_abonado'];
          if($temp>$saldo){
            $asig=$saldo;
            $temp=$temp-$saldo;
          }else{
            $asig=$temp;
            $temp=0;
          }
          $asigtot += $asig;
          $sqlm = 'SELECT MAX(ec_id) FROM egreso_cxpagar WHERE ec_egreso="'.$_GET['id'].'" ';
          $resultm = setq($sqlm) or die($sqlm);
          list($id) = $resultm->fetch_array();
          $id++;

          $sqli='INSERT INTO egreso_cxpagar SET
              ec_id="'.$id.'",
              ec_egreso="'.$_GET['id'].'",
              ec_cxpagar="'.$row['cp_id'].'",
              ec_monto="'.$asig.'",
              ec_fapli="'.$fecha.'",
              ec_uapli="'.$user.'";';
          setq($sqli) or die($sqli);

          $total=$row['cp_abonado']+$asig;

          if($total >= $row['cp_importe']){
            $sqli = 'UPDATE cxpagar SET cp_estatus = "F", cp_abonado="'.$total.'", cp_ffin ="'.$fecha.'"
                      WHERE cp_id="'.$row['cp_id'].'"';
            setq($sqli) or die($sqli);
            //$this->model->completaventa($row['cp_id']);
          }else{
            $sqli = 'UPDATE cxpagar SET cp_estatus = "A", cp_abonado="'.$total.'" WHERE cp_id="'.$row['cp_id'].'"';
            setq($sqli) or die($sqli);
          }
        }else{
          break;
        }
      }

      if($asigtot >= $monto){
        $sqli = 'UPDATE egresos SET e_estatus = "F" WHERE e_id="'.$_GET['id'].'"';
        setq($sqli) or die($sqli);
      }elseif($asigtot > 0){
        $sqli = 'UPDATE egresos SET e_estatus = "A" WHERE e_id="'.$_GET['id'].'"';
        setq($sqli) or die($sqli);
      }

      $link='?modulo=egresos&accion=show&id='.$_GET['id'].'&from=egresos';
      redirect($link);
    }
    function cortenuevo(){
      include('cuentas.php');
      $cuent = new modelcuentas();
      //DIE("--");
      $sql = 'SELECT MAX(p_id) FROM cortes';
      $result = setq($sql);
      list($cuent->id) = $result->fetch_array();
      $cuent->id++;

      $saldo=saldo_actual($_GET['cuenta']);
      $cuent->actualizarsaldo($_GET['cuenta'],$saldo);
      $cuent->actualizarp($_GET['cuenta']);
      $cuent->setDatap($cuent->id, date('Y-m-d'),date('Y-m-d',strtotime('last day of this month')),$_GET['cuenta'],$saldo);
      $cuent->insertp();

      $link='?modulo=egresos&accion=edit&idcxp='.$_GET['idcxp'];
      redirect($link);
    }
    function cancelaring(){
      $this->model->select($_GET['id']);
      $corteact = busca($this->model->cuenta,'cortes','c_estatus = "A" AND c_cuenta','c_id');
      if($this->model->corte == $corteact){
        $sql = 'SELECT * FROM egreso_cxpagar WHERE ec_egreso = "'.$this->model->id.'"';
        $result = setq($sql) or die($sql);
        $resultn = setq($sql) or die($sql);
        if($resultn->num_rows > 0){
          while($row = $result->fetch_array()){
            $sql = 'UPDATE cxpagar SET cp_abonado = cp_abonado-'.$row['ec_monto'].'
                      WHERE cp_id = "'.$row['ec_cxpagar'].'"';
            setq($sql) or die($sql);

            if(busca($row['ec_cxpagar'],'cxpagar','cp_id','cp_abonado') == 0)
              $sqlu = 'UPDATE cxpagar SET cp_estatus = "N" WHERE cp_id = "'.$row['ec_cxpagar'].'"';
            else
              $sqlu = 'UPDATE cxpagar SET cp_estatus = "A" WHERE cp_id = "'.$row['ec_cxpagar'].'"';
            setq($sqlu) or die($sqlu);

            /*$sqldel = 'DELETE FROM egreso_cxpagar WHERE ec_id = "'.$row['ec_id'].'" AND ec_egreso = "'.$row['ec_egreso'].'" 
                        AND ec_cxpagar = "'.$row['ec_cxpagar'].'"';
            setq($sqldel);*/
          }
        }

        $sqle = 'UPDATE egresos SET e_estatus = "C" WHERE e_id = "'.$this->model->id.'"';
        setq($sqle) or die($sqle);

        $sqlcd = 'SELECT cd_id,cd_idref FROM cortesd WHERE cd_tipo = "P" AND cd_idref = "'.$this->model->id.'"';
        $resultcd = setq($sqlcd);
        list($idcorteor) = $resultcd->fetch_array();

        include('cuentas.php');
        $cuent = new modelcuentas();

        //ID del corted
        $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$this->model->corte.'"';
        $result = setq($sql);
        list($idcd) = $result->fetch_array();
        $idcd++;

        $cuent->setdatacd($idcd,$this->model->corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C","",$this->model->monto,"X",$this->model->cuenta);
        $cuent->insertcd();

        $sql = 'UPDATE cortesd SET cd_reverso = "'.$idcorteor.'" WHERE cd_id = "'.$idcd.'" AND cd_corte = "'.$this->model->corte.'"';
        setq($sql);

        $sql = 'UPDATE cortesd SET cd_reverso = "'.$idcd.'",cd_observaciones = "MOVIMIENTO '.$idcorteor.'" WHERE cd_id = "'.$idcorteor.'" AND cd_corte = "'.$this->model->corte.'"';
        setq($sql);

        if(!isset($_GET['from']))
          $link=' ?modulo=cuentas&accion=show&id='.$this->model->cuenta;
        else{
          if($_GET['from'] == "egresos") $link=' ?modulo=egresos&accion=index';
          else $link=' ?modulo=egresos&accion=index';
        }
      }else{
        $link = '';
        echo '<script>
          alert("No es posible cancelar el egreso actual, verifique que el egreso pertenezca al corte actual de la cuenta.");
          window.location.href="?modulo=egresos&accion=show&id='.$_GET['id'].'&from='.$_GET['from'].'";
        </script>';
      }
  
      redirect($link);
    }
  }

  class Modelegresos{
    function result($estatus,$fini,$ffin,$proveedor,$page) { //filtro 
      $bloque = 50;
      $sql = 'SELECT * FROM egresos INNER JOIN proveedores ON e_proveedor=p_id WHERE DATE(e_fgen) BETWEEN "'.$fini.'" AND "'.$ffin.'"  
              AND e_empresa = "'.$_SESSION['emp'].'" ';
      if($estatus){
        $sql .= ' AND e_estatus = "'.$estatus.'" ';
      }
      if($proveedor){
        $sql .= ' AND p_nmb LIKE "%'.$proveedor.'%" ';
      }
      $sql .= ' ORDER BY e_fgen DESC,e_id DESC ';
      $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
      $this->result = setq($sql) or die($sql);
      $this->resultt = setq($sql) or die($sql);
    }
    function setData($id,$empresa,$monto,$proveedor,$referencia,$cuenta,$fecha,$fechareg,$user,$estatus,$observaciones,$adjunto,$corte){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
      $simboldi = array('\"',"\'","\#","\$","\%", "\&","\/","\(","\)","\=","\?","\¡","\*","\+","\~","\^","\[","\°","\|","\{","\}","\[","\]");
      $this->id = $id;
      $this->empresa= $empresa;
      $this->monto= $monto;
      $this->proveedor= $proveedor;
      $this->referencia= str_replace($simbol,$simboldi,mb_strtoupper(trim($referencia)));
      $this->cuenta=$cuenta;
      $this->fecha=$fecha;
      $this->fgen=$fechareg;
      $this->user=$user;
      $this->estatus=$estatus;
      $this->observaciones= str_replace($simbol,$simboldi,mb_strtoupper(trim($observaciones)));
      $this->adjunto=$adjunto;
      $this->corte=$corte;
    } 
    function insert(){
      $sql='INSERT INTO egresos SET
            e_monto="'.$this->monto.'",
            e_proveedor="'.$this->proveedor.'",
            e_empresa="'.$this->empresa.'",
            e_referencia="'.$this->referencia.'",
            e_cuenta="'.$this->cuenta.'",
            e_fecha="'.$this->fecha.'",
            e_fgen="'.$this->fgen.'",
            e_ugen="'.$this->user.'",
            e_corte="'.$this->corte.'",
            e_estatus="'.$this->estatus.'",
            e_adjunto="'.$this->adjunto.'",
            e_observaciones="'.$this->observaciones.'"';
      setq($sql);
      $this->idegr = getmax('e_id','egresos',false,false);
    }
    function select($id) {
      $sql = 'SELECT * FROM egresos WHERE e_id= "'.$id.'"';
      $result = setq($sql) or die($sql);
      if($result)$row = $result->fetch_array();
      $this->id= $row['e_id'];
      $this->monto= $row['e_monto'];
      $this->proveedor=$row['e_proveedor'];
      $this->referencia=$row['e_referencia'];
      $this->cuenta= $row['e_cuenta'];
      $this->fecha= $row['e_fecha'];
      $this->fgen= $row['e_fgen'];
      $this->ugen= $row['e_ugen'];
      $this->corte= $row['e_corte'];
      $this->estatus= $row['e_estatus'];
      $this->adjunto= $row['e_adjunto'];
      $this->observaciones= $row['e_observaciones'];
    }
    function asignar($egreso,$idcxp,$importecxc,$abonado){  
      $fecha=date('Y-m-d H:i:s');
      $user=$_SESSION['uid'];
      $asignado=busca($egreso,'egreso_cxpagar','ec_egreso','SUM(ec_monto)');
      $abonoprev = busca($idcxp,'cxpagar','cp_id','SUM(cp_abonado)');
      if(!$abonoprev) $abonoprev = 0;
      $sqlm = 'SELECT MAX(ec_id) FROM egreso_cxpagar WHERE ec_egreso="'.$egreso.'" ';
      $resultm = setq($sqlm) or die($sqlm);
      list($id) = $resultm->fetch_array();
      $id++;
      $sqli = 'INSERT INTO egreso_cxpagar SET
              ec_id="'.$id.'",
              ec_egreso="'.$egreso.'",
              ec_cxpagar="'.$idcxp.'",
              ec_monto="'.$importecxc.'",
              ec_fapli="'.$fecha.'",
              ec_uapli="'.$user.'";';
      setq($sqli) ;

      $total=$importecxc+$abonoprev;

      $totalcxc = busca($idcxp,'cxpagar','cp_id','cp_importe');
      if($total >= $totalcxc){
        $sqli = 'UPDATE cxpagar SET cp_estatus = "F", cp_abonado="'.$total.'", cp_ffin ="'.$fecha.'"
                  WHERE cp_id="'.$idcxp.'"';
        setq($sqli) or die($sqli);

        $tipoap = busca($idcxp,'cxpagar','cp_id','cp_tipo');
        $sqlcx = 'UPDATE cortesd SET cd_tipom = "'.$tipoap.'" WHERE cd_tipo = "C" AND cd_idref = "'.$egreso.'"';
        setq($sqlcx);
        if($tipoap == "O"){
          $idop = busca($idcxp,'cxpagar','cp_id','cp_idref');
          $sql = 'UPDATE ordenesp SET
                  op_autorizo = "'.$_SESSION['uid'].'",
                  op_fechaa = "'.date('Y-m-d H:i:s').'",
                  op_estatus = "A"
                  WHERE op_id = "'.$idop.'"';
          setq($sql);
        }elseif($tipoap == "V"){
          $idg = busca($idcxp,'cxpagar','cp_id','cp_idref');
          $sql = 'UPDATE gastos SET g_estatus = "A" WHERE g_id = "'.$idg.'"';
          setq($sql);
        }
      }else{
        $sqli = 'UPDATE cxpagar SET cp_estatus = "A", cp_abonado="'.$total.'"
                  WHERE cp_id="'.$idcxp.'"';
        setq($sqli) or die($sqli);

        $tipoap = busca($idcxp,'cxpagar','cp_id','cp_tipo');
        if($tipoap == "V"){
          $idg = busca($idcxp,'cxpagar','cp_id','cp_idref');
          $sql = 'UPDATE gastos SET g_estatus = "X" WHERE g_id = "'.$idg.'"';
          setq($sql);
        }
      }

      $existeproyeccion = busca($idcxp,'proyeccionesd INNER JOIN proyecciones ON p_id = pd_proyeccion ','p_estatus = "N" AND pd_tipo = "P" AND pd_referencia','pd_id');
      if($existeproyeccion){
        $importe = busca($existeproyeccion,'proyeccionesd','pd_id','pd_importe');
        $ecxp = busca($idcxp,'cxpagar','cp_id','cp_estatus');
        if($importe == $importecxc || $ecxp == "F"){
          $sqlupproyecccion = 'UPDATE proyeccionesd SET
                              pd_estatus = "F"
                              WHERE pd_id = "'.$existeproyeccion.'"';
          setq($sqlupproyecccion);
        }
      }
    }
    function completaventa($ccid){
      $mysqli = mysql_connect("localhost",'aqlzfcar_root','Wptyeall.0','aqlzfcar_kioscoweb');
      if(!mysql_connect_errno()){
        $sqlpc = 'SELECT p_id FROM pedidoscl WHERE p_estatus IN ("F","R")';
        $resultpc = $mysqli->query($sqlpc) or die($sqlpc);
        $pedidoscld = "";
        while($rowpc = $resultpc->fetch_array()){
          $pedidoscld.='"'.$rowpc['p_id'].'",';
        }
        $pedidoscld = substr($pedidoscld,0,-1);
        //inserto requisición y orden de compra
        $sql='SELECT cp_proyecto,cp_idsuite FROM cxpagar WHERE cp_id="'.$ccid.'"';
        $result = setq($sql) or die($sql);
        list($proyecto,$idsuite) = $result->fetch_array();
        $remision = busca($proyecto,'remision_proyecto','rp_proyecto','rp_remision');

        $sqlrd = 'SELECT * FROM remisionesd INNER JOIN articulos ON a_id = rd_articulo
                  WHERE rd_remision = "'.$remision.'" AND a_tipoprod != "P"';
        $resultrd = setq($sqlrd) or die($sqlrd);
        $nump = mysql_num_rows($resultrd);

        if($nump > 0 && $idsuite){
          $resultrd = setq($sqlrd) or die($sqlrd);
          //compruebo disponibilidad del producto
          $prodst = array();
          $cantst = array();
          while($rowd = $resultrd->fetch_array()){
            $sql = 'SELECT sum(e_cantidad) as cantidad FROM existencias
                    WHERE e_articulo="'.$rowd['rd_articulo'].'" AND e_almacen IN
                    (SELECT a_id FROM almacenes WHERE a_vendible = "1")';
            $result=setq($sql) or die($sql);
            list($stock) = $result->fetch_array();
            if(!$stock) $stock = 0;
            $sqls = 'SELECT SUM(sd_cantsur) FROM surtidosd INNER JOIN surtidos ON s_id = sd_surtido
                      WHERE sd_articulo = "'.$rowd['rd_articulo'].'" AND s_estatus = "N"';
            $results = setq($sqls) or die($sqls);
            list($ensurtido) = $results->fetch_array($results);
            if(!$ensurtido) $ensurtido = 0;

            $sqlr = 'SELECT SUM(rd_cantidad) FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision
                      WHERE rd_articulo = "'.$rowd['rd_articulo'].'" AND r_idsuite IS NOT NULL
                      AND r_id != "'.$rowd['rd_remision'].'" AND r_estatus = "A"
                      AND r_id NOT IN (SELECT DISTINCT(s_remision) FROM surtidos INNER JOIN surtidosd ON s_id = sd_surtido)';
            if(!empty($pedidoscld)) $sqlr.=' AND r_idsuite NOT IN ('.$pedidoscld.') ';
            $resultr = setq($sqlr) or die($sqlr.' '.mysql_error());
            list($enremision) = $resultr->fetch_array();
            if(!$enremision) $enremision = 0;

            $disponible = $stock-$ensurtido-$enremision;
            if($disponible <= 0){
              array_push($prodst,$rowd['rd_articulo']);
              array_push($cantst,$rowd['rd_cantidad']);
            }
          }
          $i = 0;
          foreach($prodst as $articulo){
            $nmbarticulo = busca($articulo,'articulos','a_id','a_nmb');
            //Se inicia proceso de orden de compra para el producto
            $sqlrq = 'SELECT MAX(r_id) FROM requisiciones';
            $resultrq = setq($sqlrq) or die($sqlrq);
            list($idrq) = mysql_fetch_array($resultrq);
            $idrq++;
            //Requisición
            $sqlirq = 'INSERT INTO requisiciones SET
                        r_id = "'.$idrq.'",
                        r_user = "'.$_SESSION['uid'].'",
                        r_fecha = "'.date('Y-m-d H:i:s').'",
                        r_proyecto = "'.$proyecto.'",
                        r_estatus = "A"';
            setq($sqlirq) or die($sqlirq);
            //Vamos a ver
            $ugen = $_SESSION['uid'];
            $resultrd = setq($sqlrd) or die($sqlrd);
            while($rowd = mysql_fetch_array($resultrd)){
              $sqlmrd = 'SELECT MAX(rd_id) FROM requisicionesd WHERE rd_requisicion = "'.$idrq.'"';
              $resultmrd = setq($sqlmrd) or die($sqlmrd);
              list($idrd) = mysql_fetch_array($resultmrd);
              $idrd++;
              $linean = busca($articulo,'articulos','a_id','a_linea');
              $sqlird = 'INSERT INTO requisicionesd SET
                          rd_id = "'.$idrd.'",
                          rd_requisicion = "'.$idrq.'",
                          rd_producto = "'.$articulo.'",
                          rd_cantidad = "'.$cantst[$i].'",
                          rd_destino = "P",
                          rd_nmbproducto = "'.utf8_decode(mb_strtoupper($nmbarticulo)).'",
                          rd_ugen = "'.$ugen.'"';
              setq($sqlird) or die($sqlird);
              //Proyecto requisicion
              $sqlid = 'INSERT INTO proyecto_requisicion SET
                        pr_proyecto = "'.$proyecto.'",
                        pr_requisicion = "'.$idrq.'"';
              setq($sqlid) or die($sqlid);
              $tipop = busca($articulo,'articulos','a_id','a_tipoprod');
              if($tipop == "C"){
                $sku = busca($articulo,'articulos','a_id','a_sku');
                $proveedor = busca($sku,'indexproveedores','e_usar = "1" AND e_articulo','e_proveedor');
                if($proveedor){
                  $sqli = 'INSERT INTO pedido_ordenc SET
                            po_requisicion = "'.$idrq.'",
                            po_articulo = "'.$articulo.'",
                            po_nmbart = "'.utf8_decode($nmbarticulo).'",
                            po_cantidad = "'.$cantst[$i].'",
                            po_proveedor = "'.$proveedor.'",
                            po_observaciones = "PRODUCTO PEDIDO '.$idsuite.' POR WEB"';
                  setq($sqli) or die($sqli);
                }
              }elseif($tipop == "M"){   //INicio de combos
                $sqlcom = 'SELECT  * FROM articulos_combo WHERE ac_articulo = "'.$articulo.'"';
                $resultcom = setq($sqlcom) or die($sqlcom);
                while($rowcom = mysql_fetch_array($resultcom)){
                  $sku = busca($rowcom['ac_articulocom'],'articulos','a_id','a_sku');
                  $proveedor = busca($sku,'indexproveedores','e_usar = "1" AND e_articulo','e_proveedor');
                  if($proveedor){
                    $sqli = 'INSERT INTO pedido_ordenc SET
                              po_requisicion = "'.$idrq.'",
                              po_articulo = "'.$rowcom['ac_articulocom'].'",
                              po_nmbart = "'.utf8_decode(busca($rowcom['ac_articulocom'],'articulos','a_id','a_nmb')).'",
                              po_cantidad = "'.($cantst[$i]*$rowcom['ac_cantidad']).'",
                              po_proveedor = "'.$proveedor.'",
                              po_observaciones = "PRODUCTO PEDIDO '.$idsuite.' POR WEB"';
                    setq($sqli) or die($sqli);
                  }
                }
              } //Fin de agregar combos
            }
            $i++;
          }
          $sqlar = 'UPDATE requisiciones SET
                    r_estatus = "A",
                    r_faplica = "'.date('Y-m-d H:i:s').'",
                    r_autoriza = "'.$_SESSION['uid'].'",
                    r_uaplica = "'.$_SESSION['uid'].'"
                    WHERE r_id = "'.$idrq.'"';
          setq($sqlar) or die($sqlar);
          $sqlpo = 'SELECT DISTINCT(po_proveedor) po_proveedor FROM pedido_ordenc
                    WHERE po_requisicion = "'.$idrq.'"';
          $resultpo = setq($sqlpo) or die($sqlpo);
          include_once('pedidos.php');
          $pedi = new modelPedidos();
          $pedid = new modelPedidosDetalle();
          $empresa = 1;
          while($rowpo = mysql_fetch_array($resultpo)){
            $sqlid = 'SELECT MAX(p_id) FROM pedidos';
            $resultid = setq($sqlid) or die($sqlid);
            list($id) = mysql_fetch_array($resultid);
            $id++;

            $sqlmp = 'SELECT MAX(p_id) FROM pedidos WHERE p_empresa = "'.$empresa.'"';
            $resultmp = setq($sqlmp) or die($sqlmp);
            list($maxp) = mysql_fetch_row($resultmp);
            $maxp++;

            $ordencnew = busca($empresa,'configuracion','c_consecutivo','c_preordenc').str_pad($maxp,5,"0",STR_PAD_LEFT);
            $pedi->setdata($id,$rowpo['po_proveedor'],"GENERADA DESDE LA REQUISICIÓN NÚMERO ".$idrq,$ordencnew,$empresa,NULL,NULL,date('Y-m-d H:i:s'),$proyecto);
            $pedi->insert();

            $sqldpo = 'SELECT * FROM pedido_ordenc
                      WHERE po_requisicion = "'.$idrq.'" AND po_proveedor = "'.$rowpo['po_proveedor'].'"';
            $resultdpo = setq($sqldpo) or die($sqldpo);
            while($rowdpo = mysql_fetch_array($resultdpo)){
              $sqlid = 'SELECT MAX(pd_id) FROM pedidosd WHERE pd_pedido = "'.$pedi->id.'"';
              $resultid = setq($sqlid) or die($sqlid);
              list($idd) = mysql_fetch_array($resultid);
              $idd++;
              $pedid->insertdetalle($pedi->id,$idd,$rowdpo['po_articulo'],$rowdpo['po_cantidad'],$rowdpo['po_nmbart']);

              $sqluoc = 'UPDATE pedido_ordenc SET po_ordenc = "'.$pedi->id.'"
                          WHERE po_requisicion = "'.$idrq.'" AND po_articulo = "'.$rowdpo['po_articulo'].'"
                          AND po_proveedor = "'.$rowpo['po_proveedor'].'"';
              setq($sqluoc) or die($sqluoc);
            }
            $sqlart = 'SELECT * FROM pedidosd WHERE pd_pedido = "'.$pedi->id.'" ';
            $resultart = setq($sqlart) or die($sqlart);
            $importe = 0;
            while($rowap = mysql_fetch_array($resultart)){
              $preciot = $rowap['pd_preciou']*$rowap['pd_cantidad'];
              if($rowap['pd_ivai'] == 1){ $iva = $preciot*0.16; }
              else{ $iva = 0; }
              $importe+=$preciot+$iva;
            }
            $pedi->insertaXpagar($pedi->id,$importe);
            $pedi->aplicar($pedi->id);
          }
        }
        //Se inserta Solicitud de servicio si hay Suite
        $sqlrd = 'SELECT * FROM remisionesd
                  WHERE rd_remision = "'.$remision.'"
                  AND rd_linean IN (SELECT ln_id FROM lineas_negocio WHERE ln_instalacion = "1")';
        $resultrd = setq($sqlrd) or die($sqlrd);
        $numpin = mysql_num_rows($resultrd);
        if($numpin > 0){
          $resultrd = setq($sqlrd) or die($sqlrd);
          include_once('programacion.php');
          $prog = new ModelProgramacion();

          $proveedor = busca($remision,'remisiones','r_id','r_proveedor');
          while($rowrd = mysql_fetch_array($resultrd)){
            $sqlmot = 'SELECT MAX(o_id) FROM ordenestrab';
            $resultmot = setq($sqlmot) or die($sqlmot);
            list($mot) = mysql_fetch_array($resultmot);
            $mot++;

            $ingeniero = $prog->asignaing();
            $prog->SetDataprog($mot,$remision,$idsuite,$proveedor,date('Y-m-d'),$rowrd['rd_articulo'],NULL,NULL,$ingeniero,NULL,"N");
            $prog->insertprog();
          }
        }
        include_once('pedidosclw.php');
        $pediw = new modelpedidosclw();
        $sqlkc = 'SELECT p_web FROM pedidoscl WHERE p_id = "'.$idsuite.'"';
        $resultkc = $mysqli->query($sqlkc) or die($sqlkc);
        list($web) = $resultkc->fetch_array();
        $mysqli->close();
        if($web == 1){
          $pediw->confirmpagosh($idsuite);
        }elseif($web == 2){
          $pediw->confirmpagosu($idsuite);
        }
      }
    }
  }

  class Viewegresos {
    function __construct($model) {
      $this->model = $model;
      $this->extenciones = array('PNG','JPG','JPEG','GIF','RAW','BMP','TXT','PDF','png','jpg','jpeg','gif','raw','bmp','txt','pdf');
      $this->estatusp = array("N"=>"EN CAPTURA","A"=>"APLICADO","C"=>"CANCELADO","F"=>"FINALIZADO");
      $this->tipop = array("D"=>"DEVOLUCIONES","X"=>"REVERSO","E"=>"PAGO A PROVEEDOR","V"=>"VALE","T"=>"TIEMPO AIRE","P"=>"PRESTAMO PAGADO","O"=>"ORDEN DE PAGO","F"=>"GASTO FIJO","C"=>"PAGO A PROVEEDOR","G"=>"GASTOS");
      $this->estatuscx = array("N"=>"NUEVA","F"=>"FINALIZADO","C"=>"CANCELADO","A"=>"ABONADO","T"=>"TODOS");
    }
    function browse($estatus,$fini,$ffin,$proveedor,$page) {
      $selt = '';
      $seln = '';
      $sela = '';
      $self = '';
      $selc = '';
      if($estatus) $est = '&estatus='.$estatus;
      if($estatus == "N") $seln = "selected";
      elseif($estatus == "A") $sela = "selected";
      elseif($estatus == "F") $self = "selected";
      elseif($estatus == "C") $selc = "selected";
      else $selt = '';
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
        $nuevo = '<a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setegreso.php?o=1&rand='. rand(1,100) .'" href="javascript:;" >
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>';
          
        $filtro = '
            <form class="form-inline" role="form" method="post" action="?modulo=egresos&accion=index" id="filtro">
              <input name="page" id="page" value="'. $page .'" hidden> 
                <div class="mb-5">
                  <label for="tipom">Desde:</label>
                  <input type="date" name="fini" id="fini" class="form-control" value="'. $fini .'" />
                </div>
                <div class="mb-5">
                  <label for="tipom">Hasta:</label>
                  <input type="date" name="ffin" id="ffin" class="form-control" value="'. $ffin.'" />
                </div>
                <div class="mb-5">
                  <label for="tipom">Estatus:</label>
                  <select class="form-control" name="estatus" id="estatus" >
                    <option value="" '. $selt .' >Todos</option>
                    <option value="N" '. $seln .' >En Proceso</option>
                    <option value="A" '. $sela .' >En aplicacipon</option>
                    <option value="F" '. $self .' >Finalizados</option>
                    <option value="C" '. $selc .' >Cancelados</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                  <button type="button" onclick="mandar(0)" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
                  </button>
                  <a href="?modulo=egresos&accion=index">
                    <button type="button" class="btn btn-warning">
                      <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                    </button>
                  </a>
                </div>
              </form>';

          toolbar($_GET['modulo'], "", $filtro, $nuevo);
          
          echo '
          <div class="main-card mb-2 card mt-2">
          <div class="card-body">
          <div class="table-responsive text-medium">
            <center>
              <table class="table table-hover table-striped" id="myTable">
                <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
                  <tr>
                    <th>Ver</th>
                    <th>Proveedor</th>
                    <th>Registró</th>
                    <th>Fecha</th>
                    <th>Cuenta</th>
                    <th>Monto</th>
                    <th>Estatus</th>
                  </tr>
                </thead>';
                if ($this->model->result){
                  while ($row=$this->model->result->fetch_array()){
                    if($proveedor == "X") $proveedor = busca($row['e_empresa'],'empresas','e_id','e_nmb');
                    else $proveedor = $row['p_nmb'];
                    echo '<tr>
                      <th>
                        <a href="?modulo=egresos&accion=show&id='.$row['e_id'].'&from=egresos">
                          <button class="btn btn-info"><i class="fas fa-chevron-circle-right"></i></button>
                        </a>
                      </th>';
                      echo '<td align=center>'.$proveedor.'</td>';
                      echo '<td align=center>'.$row['e_ugen'].'</td>';
                      echo '<td align=center>'.fecha_formato($row['e_fgen'],true,true).'</td>';
                      echo '<td align=center>'.busca($row['e_cuenta'],'cuentas','cu_id','cu_nmb').'</td>';
                      echo '<td class="number-align">$ '.number_format($row['e_monto'],2).'</td>';
                      if($row['e_estatus'] == "N"){
                        echo '<td style= "background:#EDE619">NUEVO</a></td>';
                      }else if($row['e_estatus'] == "A"){
                        echo '<td style="background:#64AFF0">EN APLICACIÓN</a></td>';
                      }else if($row['e_estatus'] == "C"){
                        echo '<td style="background:#F03D21;color:#FFFFFF;">CANCELADO</a></td>';
                      }else if($row['e_estatus'] == "F"){
                        echo '<td style="background:#1BF030;">FINALIZADO</a></td>';
                      }
                    echo '</tr>';
                  }
                }
              echo '</table>
            </center>
          </div>';

          ?>
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
          </script>

          <?php
          
    }
    function edit() {
      $con = 'SELECT p_nmb FROM proveedores';
      $query = setq($con) or die($con);
      while ($row = mysql_fetch_array($query)) {
        $elementos[] = '"'.$row['p_nmb'].'"';
      }
      $arreglo = implode(", ", $elementos);
      ?>
      <script>
        $(function(){
          var availableTags = new Array(<?php echo $arreglo; ?>);
          $("#tags").autocomplete({
            source: availableTags
          });
        });
        function checkSubmitguardar() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
      </script>
      <?php
      $sqlp = "SELECT cu_id, cu_nmb FROM cuentas INNER JOIN cortes ON cu_id=c_cuenta
                WHERE cu_estatus='A' AND cu_tipo IN ('D','E') AND c_estatus='A'
                ORDER BY cu_orden ASC, cu_id ASC";
      $result=setq($sqlp) or die($sqlp);
      if(isset($_GET['idcxp']) || isset($_GET['cxpagar'])) $link = "?modulo=cxcobros&accion=index";
      else $link = "?modulo=egresos&accion=index";
      echo '<div class="div2" id="bboton01">
        <a href="'.$link.'" accesskey="t"><input type="button" value=" " class="botonb" id="atras"></a>
      </div></div>';
      if (isset($this->model->id)) {
        $accion = 'update&id='.$this->model->id;
        $monto=$this->model->monto;
      }else{
        $accion = 'insert';
        if(isset($_GET['idcxp'])){
          $monto = busca($_GET['idcxp'],'cxpagar','cp_id','(cp_importe-cp_abonado)');
          $this->model->proveedor = busca(busca($_GET['idcxp'],'cxpagar','cp_id','cp_proveedor'),'proveedores','p_id','p_nmb');
        }
        else $monto=0;
      }
      if(isset($_GET['ref'])) $this->model->referencia = $_GET['ref'];
      echo '<form autocomplete="off" method=post name="pro" action="?modulo=egresos&accion='.$accion.'&idcxp='.$_GET['idcxp'].'&from='.$_GET['from'].'" enctype="multipart/form-data" onsubmit="return checkSubmitguardar();" >
        <center>
          <table class="lista" border="0" cellspacing="3" width="100%">
            <thead>
              <tr><th colspan="3"><b>egreso NUEVO</b></th></tr>
            </thead>
            <tbody>';
              echo '<tr>
                <th>Monto</th>
                <th>Proveedor</th>
                <th>Referencia</th>
              </tr>';
              echo '<tr>
                <td><input type="number" onfocus="this.select();" style="width:80px" min="0.01" name="monto" step="0.01" value="'.number_format($monto,2,'.','').'" autofocus required /></td>
                <td><input type="text" name="proveedor" value="'.$this->model->proveedor.'" id="tags" required autofocus placeholder="Nombre del proveedor..." style="width:100%" /></td>
                <td><input type="text" name="referencia" value="'.$this->model->referencia.'" maxlength="50" autofocus placeholder="Autorizacion o referencia..." style="width:100%" /></td>';
              echo '</tr>';
              echo '<tr>
                <th>Adjunto</th>
                <th>Cuenta a ingresar monto</th>
                <th>Observaciones</th>
              </tr>';
              echo '<tr>';
                echo '<td><input type="file" name="archivo" /></td>';
                if(isset($_GET['cta'])) $cuenta = $_GET['cta']; else $cuenta = NULL;
                echo '<td><br>
                  <select name="cuenta" >';
                    while($row=mysql_fetch_array($result)){
                      if($row['cu_id'] == $cuenta) $sel = 'selected'; else $sel = "";
                      echo '<option value="'.$row['cu_id'].'" '.$sel.'>'.$row['cu_nmb'].' - Saldo: $'.number_format(saldo_actual($row['cu_id']),2).'</option>';
                    }
                  echo '</select><br>
                </td>';
                echo '<td><textarea name="observaciones" cols="80" rows="3" >'.$this->model->observaciones.'</textarea></td>';
              echo '</tr>';
              echo'<tr>
                <td align="center" colspan="3">
                  <center><b>Validar: </b><input type="checkbox" required ></center>
                </td>
              </tr>
              <tr>
                <td align="center" colspan="3">
                  <center><input type="submit" value="" id="guardar" class="botont"></center>
                </td>
              </tr>
            </tbody>
          </table>
        </center>
      </form>';
    }
    function show($fini,$ffin,$tipo){
      ?>
      <script>
        function cancelar(aux){
          var conf = confirm("¿Deseas cancelar este gasto?");
          if(conf) document.location.href = "?modulo=egresos&accion=cancelar&id="+aux;
        }
        function fondear(aux){
          var conf = confirm("¿Deseas aplicar el saldo restante, desde la cuenta mas antigua a la mas actual?");
          if(conf) document.location.href = "?modulo=egresos&accion=fondear&id="+aux;
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
      if($tipo == "T") $t1 = "selected";
      if($tipo == "Y") $t2 = "selected";
      if($tipo == "S") $t3 = "selected";
      if($tipo == "Z") $t4 = "selected";
      if($tipo == "P") $t5 = "selected";
      if($tipo == "A") $t6 = "selected";
      if($tipo == "I") $t7 = "sleected";
      //validar el estatus del corte
      $estcorte = busca($this->model->corte,'cortes','c_id','c_estatus');
      $botones = "";

     
        $atras = '<a href="?modulo=egresos&accion=index" accesskey="t">
          <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Atrás </button>
        </a>';
        echo '<script>
          function cancelaing(){
            var conf = confirm("Al cancelar el egreso las cuentas por pagar asignadas serán liberadas\n¿Deseas continuar?");
            if(conf == true){
              document.location.href="?modulo=egresos&accion=cancelaring&id='.$this->model->id.'&from='.$_GET['from'].'";
              document.getElementById("cancelar").disable == true;
            }
          }
        </script>';
        if($this->model->estatus != "C")
        $botones .= '<button class="btn btn-danger ml-1" onclick="cancelaing();" data-toggle="tooltip" data-placement="top" title="Cancelar egreso"><i class="fa fa-times"></i> Cancelar</button>';
        toolbar($_GET['modulo'], $atras, "", "", $botones);
        echo '
        <div class="main-card mb-3 mt-2" style="background: white ">
        <div class="card-body row">
        <center>
          <div class="alert alert-primary">Resumen de egreso</div>
          <div class="table table-responsive table-hover">
            <table class="table table-hover table-striped">
              <thead class="thead bg-primary text-white pt-1 pb-1">';
                echo '<tr>
                  <th>Monto</th>
                  <th>proveedor</th>
                  <th>Observaciones</th>
                  <th>Fecha y usuario</th>
                  <th>Fecha del egreso</th>
                  <th>Ingresdo a cuenta</th>
                </tr>
              </thead>
              <tbody>';
                echo '<tr>
                  <td>$ '.number_format($this->model->monto,2).'</td>';
                  echo '<td>'.busca($this->model->proveedor,'proveedores','p_id','p_nmb').'</td>';
                  echo '<td>'.$this->model->observaciones.'</td>';
                  echo '<td>'.fecha_formato($this->model->fgen,true,true).'  '.$this->model->ugen.'</td>';
                  echo '<td>'.fecha_formato($this->model->fecha,true,true).'</td>';
                  echo '<td>'.busca($this->model->cuenta,'cuentas','cu_id','cu_nmb').'</td>';
                echo '</tr>';
              echo '</tbody>
            </table>
          </div>
        </center>';
        if($this->model->estatus=="A" || $this->model->estatus=="N"){
          $asignado=busca($this->model->id,'egreso_cxpagar','ec_egreso','SUM(ec_monto)');
          if(!$asignado) $asignado=0;
          $restante=$this->model->monto-$asignado;

          $sqlfn = 'SELECT * FROM cxpagar WHERE cp_proveedor="'.$this->model->proveedor.'"
                  AND cp_estatus IN ("A","N") AND DATE(cp_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
          if($tipo){
            $sqlfn.=' AND cp_tipo="'.$tipo.'"';
          }
          $resultfn = setq($sqlfn);
          echo '<table width="100%">
            <tr>
              <td colspan="2" align="center">';
                echo '<div style="font-size:28px;">SALDO RESTANTE $
                  <span style="font-size:35px;" id="montosinasig"> '.number_format($restante,2).'</span>
                </div>';
              echo '</td>
              <td colspan="2" align="center">';
                if($restante > 0 && $resultfn->num_rows > 0 && $estcorte == "A")
                  echo '<button type="button" onclick="fondear('.$this->model->id.');" class="btn btn-info" id="fondear" data-toggle="tooltip" data-placement="top" title="Aplicar saldo a notas más antiguas">
                    <i class="icon-briefcase"></i> Fondear
                  </button>';
              echo '</td>
              <td colspan="2" align="center">';
                echo '<div style="font-size:28px;">SALDO ASIGNADO&nbsp;&nbsp;$
                  <span style="font-size:35px;" id="montoasig">'.number_format($asignado,2).'</span>
                </div>';
              echo '</td>
            </tr>';
            if($restante > 0 && $estcorte == "A"){
              echo '<form action="?modulo=egresos&accion=show&id='.$this->model->id.'&from=cxpagar" method="post" onsubmit="return checkSubmitenviar();">
                <table width="100%"><tr><td colspan="2" align="center">';
                  echo '<tr>
                    <td align="center"><b>Tipo: </b>
                      <select name="tipo" id="tipo" onchange="this.form.submit()" class="form-control">
                        <option value="">TODOS</option>';
                        $sqlt = 'SELECT * FROM cortes_tipom WHERE ct_tipo = "P" AND ct_clave NOT IN ("T","X")';
                        $resultt = setq($sqlt);
                        while($rowt = $resultt->fetch_array()){
                          if($tipo == $rowt['ct_clave']) $selt = 'selected';
                          else $selt = '';
                          echo '<option value="'.$rowt['ct_clave'].'" '.$selt.'>'.$rowt['ct_descripcion'].'</option>';
                        }
                      echo '</select>
                    </td>
                    <td align="center" colspan="2"><b>Fecha Inicial: </b><input type="date" class="form-control" value="'.$fini.'" name="fini"></td>
                    <td align="center" colspan="2"><b>Fecha Final: </b><input type="date" class="form-control" value="'.$ffin.'" name="ffin"></td>
                    <td align="center">
                      <button type="submit" class="btn btn-primary"><i class="icon-ios-refresh-empty"></i> Actualizar</button>
                    </td>
                  </tr>
                </table>
              </form>';
              $sql = 'SELECT * FROM cxpagar WHERE cp_proveedor="'.$this->model->proveedor.'"
                      AND cp_estatus IN ("A","N") AND DATE(cp_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
              if($tipo){
                $cad='&fini='.$fini.'&ffin='.$ffin.'&tipo='.$tipo;
                $sql.=' AND cp_tipo="'.$tipo.'"';
              }else{
                $cad='&fini='.$fini.'&ffin='.$ffin;
              }
              $sql.='ORDER BY cp_fini DESC, cp_id DESC';
              $result=setq($sql) or die($sql);
              if($result->num_rows > 0 && $estcorte == "A"){
                echo '<center>
                  <form action="?modulo=egresos&accion=asignar&id='.$this->model->id.$cad.'" method="post" onsubmit="return checkSubmitenviar();">
                    <div id="diving" class="diving table-responsive">
                      <table class="table table-striped" width="100%">
                        <input type="hidden" id="restapas" value="'.$restante.'" />';
                        echo '<thead class="thead bg-light-blue bg-darken-2 text-white pt-1 pb-1">
                          <tr>
                            <th colspan="9"><b>Cuentas por pagar de '.busca($this->model->proveedor,'proveedores','p_id','p_nmb').'
                              &nbsp;&nbsp;del '.fecha_formato($fini,false,true).' al '.fecha_formato($ffin,false,true).'</b>
                            </th>
                          </tr>';
                          echo '<tr>
                            <th></th>
                            <th>Tipo</th>
                            <th>Descripcion</th>
                            <th>Fecha aplicacion</th>
                            <th>Estatus</th>
                            <th>Saldo</th>
                            <th>Abonar</th>
                          </tr>
                        </thead>
                        <tbody>';
                          $cont=0;
                          while($row=$result->fetch_array()){
                            $fecha = explode(" ", $row['cp_fini']);
                            $saldo=$row['cp_importe']-$row['cp_abonado'];
                            $cont++;
                            $setcxc = NULL;
                            if(isset($_GET['idcxp']) && $row['cp_id'] == $_GET['idcxp']){
                              $setcxc = $cont;
                            }
                            $desc = busca($row['cp_tipo'],'cortes_tipom','ct_tipo = "P" AND ct_clave','ct_descripcion');
                            if($row['cp_tipo'] == "R"){
                              $desc.=' '.busca($row['cp_referencia'],'remisiones','r_id','r_folio');
                            }
                            $desc = $row['cp_observaciones'];
                            if($row['cp_tipo'] == "C"){
                              $desc.=" COMPRAS ".busca(busca($row['cp_idref'],'ordenesc','o_id','o_tablero'),'crm_tableros','ct_id','ct_nmb');
                            }elseif($row['cp_tipo'] == "T"){
                              $desc.=" TIEMPO AIRE ".busca($row['cp_idref'],'tae','t_id','t_fecha');
                            }elseif($row['cp_tipo'] == "O"){
                              $desc.=" ORDEN PAGO ".busca($row['cp_idref'],'ordenesp','op_id','op_nmb');
                            }elseif($row['cp_tipo'] == "M"){
                              $desc.=" ORDEN DE COMPRA MENOR".busca($row['cp_idref'],'ordenesp','op_id','op_nmb');
                            }else{
                              if($row['cp_tipo'] == "F") $desc = busca($row['cp_idref'],'gastosf','gf_id','gf_nmb');
                              elseif($row['cp_tipo'] == "G") $desc = busca($row['cp_idref'],'gastos','g_id','g_descripcion');
                            }
                            if($row['cp_estatus']=="N") $color="style='background-color:#FF5C5C;color:white;'";
                            else if($row['cp_estatus']=="A") $color="style='background-color:#FFE447'";
                            else if($row['cp_estatus']=="F") $color="style='background-color:#47B6FF'";
                            else if($row['cp_estatus']=="C") {
                              $color="style='background-color:#000000; color:white;'";
                              $saldo=0;
                            }
                            if(($row['cp_estatus']=='F') OR ($row['cp_estatus']=='C')) $link="#";
                            else $link='?modulo=cxcobrar&accion=cobro&cuenta='.$row['cp_id'];
                            echo '<input type="hidden" id="sal'.$cont.'" value="'.$saldo.'">';
                            echo '<tr>';
                              echo '<td><input type="checkbox" id="c'.$cont.'" name="c'.$row['cp_id'].'" onclick="habilitar('.$cont.');" tabindex="'.$cont.'" /></td>';
                              echo '<td>'.$this->tipop[$row['cp_tipo']].'</td>';
                              echo '<td>'.$desc.'</td>';
                              echo '<td>'.fecha_formato($row['cp_fini'],true,true).'</td>';
                              echo '<td '.$color.'>'.$this->estatuscx[$row['cp_estatus']].'</td>';
                              echo '<td>$ '.number_format($saldo,2).'</td>';
                              echo '<td><input type="number" id="i'.$cont.'" name="i'.$row['cp_id'].'" class="form-control" value="0" onchange="calcular('.$cont.');" max="'.$saldo.'" min="0" disabled step="0.01" onfocus="this.select();" /></td>';
                              //echo '<td><a id="popup" href="popup/detallecobro.php?id='.$row['cp_id'].'" accesskey="d"><input type="button" value="" id="detalles" class="botont"></a></td>';
                            echo '</tr>';
                          }
                          if($setcxc){
                            ?>
                            <script type="text/javascript">
                              $(document).ready(function() {
                                $("#c" + <?php echo $setcxc; ?>).click();
                              });
                            </script>
                            <?php
                          }
                          echo '<tr><td colspan="9"><center>VALIDAR&nbsp;<input type="checkbox" id="validador" onclick="validar();" required /></center></td></tr>';
                          echo '<tr>
                            <td colspan="9">
                              <center>
                                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Aplicar egreso</button>
                              </center>
                            </td>
                          </tr>';
                          echo '<input type="hidden" id="cont" value="'.$cont.'" />
                          <input type="hidden" id="restante" value="'.$restante.'" />
                          <input type="hidden" id="asignado" value="'.$asignado.'" />
                          <input type="hidden" id="monto" value="'.$restante.'" />
                        </tbody>
                      </table>
                    </div>
                  </form>
                </center>  
              </div>
              </div>';
              }
            }
        }
        $sql = 'SELECT * FROM egreso_cxpagar INNER JOIN cxpagar ON ec_cxpagar=cp_id
                INNER JOIN cortesd ON cd_idref = ec_egreso
                INNER JOIN cortes_tipom ON ct_clave = cd_tipom
                WHERE ec_egreso="'.$_GET['id'].'" AND cd_tipo = "P" AND ct_tipo = "P"';
        $result=setq($sql) or die($sql);
        echo '<div class="table table-responsive table-hover">
          <table class="table table-hover table-striped">
            <thead class="thead bg-primary text-white pt-1 pb-1">';
              echo '<tr><th colspan="9"><b>ABONOS REGISTRADOS</b></th></tr>';
              if($result->num_rows > 0){
                  echo '<tr>
                    <th>Tipo</th>
                    <th>Descripcion</th>
                    <th>Estatus</th>
                    <th>Fecha aplicacion</th>
                    <th>Género</th>
                    <th>Monto</th>
                  </tr>
                </thead>';
                $cont=0;
                while($row=$result->fetch_array()){
                  if($row['cp_estatus']=="N") $color="style='background-color:#FF5C5C;color:white;'";
                  else if($row['cp_estatus']=="A") $color="style='background-color:#FFE447'";
                  else if($row['cp_estatus']=="F") $color="style='background-color:#47B6FF'";
                  else if($row['cp_estatus']=="C") {
                    $color="style='background-color:#000000; color:white;'";
                  }
                  $desc = $row['ct_descripcion'];
                  if($row['cp_tipo'] == "R") $desc.=' '.busca($row['cp_referencia'],'remisiones','r_id','r_folio');
                  echo '<tr>';
                    echo '<td>'.$row['cp_id'].' - '.$this->tipop[$row['cp_tipo']].'</td>';
                    echo '<td>'.$desc.'</td>';
                    echo '<td '.$color.'>'.$this->estatuscx[$row['cp_estatus']].'</td>';
                    echo '<td>'.fecha_formato($row['ec_fapli'],true,true).'</td>';
                    echo '<td>'.$row['ec_uapli'].'</td>';
                    echo '<td>$ '.number_format($row['ec_monto'],2).'</td>';
                  echo '</tr>';
                  $cont +=$row['ec_monto'];
                }
                echo '<tr><td colspan="5" style="text-align:right;"><b>TOTAL</b></td><td><b>$ '.number_format($cont,2).'</b></td></tr>';
              }else{
                echo '<tr><td>NO EXISTEN PAGOS REGISTRADOS</td></tr>';
              }
            echo '</tbody>
          </table>
        </div>';
        if(!empty($this->model->adjunto) && file_exists('Adjuntos/'.$_SESSION['emp'].'/'.$this->model->adjunto)){
          echo'<div class="col-12 col-md-12 row">
            <center>
              <a target="_BLANK" href="../Adjuntos/'.$_SESSION['emp'].'/'.$this->model->adjunto.'">
                <button type="button" class="btn btn-secondary"><i class="fas fa-paperclip"></i> '.$this->model->adjunto.'</button>
              </a>
            </center>
          </div>';
        }
        ?>
        <script>
          function habilitar(aux){
            if(document.getElementById('c'+aux).checked){
              document.getElementById('i'+aux).disabled=false;
              var restante = document.getElementById("restapas").value;
              var ccob = document.getElementById("sal" + aux).value;
              var pagar = Math.min(restante,ccob);
              document.getElementById('i'+aux).value=pagar;
              calcular(aux);
              document.getElementById('i'+aux).focus();
            }else{
              document.getElementById('i'+aux).value=0;
              calcular(aux);
              document.getElementById('i'+aux).disabled=true;
            }
          }
          function calcular(reci){
            var cont=parseFloat(document.getElementById('cont').value);
            var restante=parseFloat(document.getElementById('restante').value);
            var asignado=parseFloat(document.getElementById('asignado').value);
            var monto=parseFloat(document.getElementById('monto').value);
            var restapas=parseFloat(document.getElementById('restapas').value);
            var total=0;
            for(i=1;i<=cont;i++){
              if(document.getElementById('c'+i).checked){
                var aux = parseFloat(document.getElementById('i'+i).value);
                total=total+aux;
              }else{
              }
            }
            if(total>restante){
              alert('El saldo asignado supera el disponible.');
              document.getElementById('i'+reci).value=restapas;
              var total=0;
              for(i=1;i<=cont;i++){
                if(document.getElementById('c'+i).checked){
                  var aux = parseFloat(document.getElementById('i'+i).value);
                  if(aux<=0){
                    document.getElementById('i'+i).disabled=true;
                    document.getElementById('c'+i).checked = false;
                  }
                  total=total+aux;
                }
              }
              var aux3=total.toFixed(2);
              var aux4=(monto-total).toFixed(2);
              document.getElementById('montoasig').innerHTML=aux3;
              document.getElementById('montosinasig').innerHTML=aux4;
              document.getElementById('restapas').value=aux4;
            }else{
              var aux3=total.toFixed(2);
              var aux4=(monto-total).toFixed(2);
              document.getElementById('montoasig').innerHTML=aux3;
              document.getElementById('montosinasig').innerHTML=aux4;
              document.getElementById('restapas').value=aux4;
            }
          }
          function validar(){
            var cont=parseFloat(document.getElementById('cont').value);
            var total=0;
            for(i=1;i<=cont;i++){
              if(document.getElementById('c'+i).checked){
                var aux = parseFloat(document.getElementById('i'+i).value);
                total=total+aux;
              }
            }
            if(total<=0){
              alert('Tienes que abonar al menos a una cuenta para poder aplicar.');
              document.getElementById('validador').checked=false;
            }
          }
        </script>
        <?php
    }
  }
?>