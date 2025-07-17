<?php
  ini_set('display_errors','1');
  $GLOBALS['menu'] = "Ingresos";
  class ingresos{
    var $model;
    var $view;
    function __construct(){ // constructor
      $this->model= new Modelingresos; // crea modelo
    }
    function index(){
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if(!isset($_REQUEST['fini']))    $_REQUEST['fini'] = date('Y-m-d', strtotime('-14 days'));
      if(!isset($_REQUEST['ffin']))    $_REQUEST['ffin'] = date('Y-m-d');
      if(!isset($_REQUEST['cliente']))    $_REQUEST['cliente'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->result($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin'],trim($_REQUEST['cliente']),$_REQUEST['page']);
      $this->view=new Viewingresos($this->model);
      $this->view->browse($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['cliente'],$_REQUEST['page']);
    }
    function edit() {
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new Viewingresos($this->model);
      $this->view->edit();
    }
    function show() {
      $cliente=busca($_GET['id'],'ingresos','i_id','i_cliente');
      if(!isset($_REQUEST['fini'])) {
        $fini1 = busca($cliente,'cxcobrar','(cx_estatus = "N" OR cx_estatus="A") AND cx_cliente','DATE(MIN(cx_fini))');
        if(!$fini1) $_REQUEST['fini'] = date('Y-m-d');
        else $_REQUEST['fini'] = date('Y-m-d',strtotime($fini1));
      }
      if(!isset($_REQUEST['ffin'])){
        $maxdate = busca($cliente,'cxcobrar','(cx_estatus = "N" OR cx_estatus="A") AND cx_cliente','DATE(MAX(cx_fini))');
        if(isset($_GET['idcxc'])) $_REQUEST['ffin'] =  date('Y-m-d',strtotime($maxdate.' + 1 day'));
        else $_REQUEST['ffin'] = date('Y-m-d',strtotime($_REQUEST['fini'].' + 1 day'));
      }
      if(!isset($_REQUEST['tipo']))    $_REQUEST['tipo'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new Viewingresos($this->model);
      $this->view->show($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['tipo']);
    }
    /*function insert(){
      //Verifico cuenta activa
      $fecha = str_replace("T"," ",$_POST['fecha']);
      include('cuentas.php');
      $cuent = new modelcuentas();
      $okcta = $cuent->cuentaactiva($_POST['cuenta']);

      if($okcta == "NOTOK"){
        echo '<script>
                var conf = confirm("La fecha de corte de la cuenta ha finalizado, ¿Desea abrir un corte nuevo a la cuenta?")
                if(conf == false){
                  document.location.href="?modulo=ingresos&accion=edit";
                }
                else{
                  document.location.href="?modulo=ingresos&accion=cortenuevo&cuenta='.$_POST['cuenta'].'&nextmod=ingresos&nexta=edit&idcxc='.$_GET['idcxc'].'";
                }
              </script>';
        $link=' ?modulo=ingresos&accion=edit';
        die();
      }
      else{
        $corte = $okcta;
        $cliente = $_REQUEST['cliente'];
        //ID del corted
        $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
        $result = setq($sql);
        list($idcd) = $result->fetch_array();
        $idcd++;

        if(!$cliente)
            die(alert_back('El cliente ingresado no se encuentra en la lista de crm_clientes',true));

        $fechareg=date('Y-m-d H:i:s');
        $user=$_SESSION['uid'];
        $estatus='N';

        if(isset($_FILES['archivo']['name'])){
          $target = 'adjuntos/';
          $ext = substr($_FILES['archivo']['name'], -3);
          if($_FILES['archivo']['name']){
            $target = $target.'ingreso'.$_GET['id'].'.'.$ext;
            if(move_uploaded_file($_FILES['archivo']['tmp_name'], $target)) {
              //echo 'El archivo '.basename($_FILES['archivo']['name']).' ha sido subido';
            }else{
                $error = "8XMI02";
            }

          }
          else{
            $target=NULL;
          }
        }
        else $target = NULL;
        if(isset($_POST['importelote']) && $_POST['importelote'] && $_POST['importelote'] <= $_POST['importe']) $_POST['importe'] = $_POST['importelote'];

        $this->model->setData(NULL,$_SESSION['emp'],$_POST['importe'],$cliente,$_POST['referencia'],$_POST['cuenta'],$fecha,$fechareg,$user,$estatus,$_POST['observaciones'],$target,$corte);
        $this->model->insert();
        $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C",$this->model->iding,$_POST['importe'],"R",$_POST['cuenta']);
        $cuent->insertcd();

        if(isset($_POST['idlist'])) $j = sizeof($_POST['idlist']);
        else if(isset($_GET['idcxc']))$j = 1;

        if(isset($_GET['idcxc']) || $_POST['idlist']){
          for($i=0; $i<$j; $i++){
            if(isset($_POST['idlist'])){
              $_GET['idcxc'] = $_POST['idlist'][$i];
              $_POST['importe'] = $_POST['saldolist'][$i];
            }
            $abonado = busca($_GET['idcxc'],'cxcobrar','cx_id','cx_abonado');
            $this->model->asignar($this->model->iding,$_GET['idcxc'],$_POST['importe'],$abonado);
            $tae = busca($_GET['idcxc'],'tae','t_cxcobrar','t_id');
            if($tae){
              $sqlTAE = 'SELECT d_id,d_factura,d_cliente,cl_ceo,d_formapago FROM depositos INNER JOIN clientes ON d_cliente = cl_id 
                          WHERE d_idtae = "'.$tae.'"';
              $resultTAE = setqJDTAE($sqlTAE);
              list($idDep,$facturaT,$clienteTAE,$clienteCEO,$formaPago) = $resultTAE->fetch_array();

              if($facturaT != null && $facturaT != ""){
                if($facturaT == "S"){
                  if($clienteCEO != null && $clienteCEO != ""){
                    include_once('facturas.php');
                    $fact = new ModelFacturas();
                    $fiscales = busca($clienteCEO,'crm_fiscales','cf_predeterminada = "1" AND cf_cliente','cf_id');

                    if($fiscales != null && $fiscales != "")
                      $bandera = "1";
                    else{
                      $bandera = "2";
                    }

                    if($formaPago != null && $formaPago != ""){
                      if($formaPago == "1" || $formaPago == "5")
                        $fpago = "1";
                      else if ($formaPago == "3" || $formaPago == "4")
                        $fpago = "3";
                      else if ($formaPago == "2")
                        $fpago = "2";
                    }

                    if($bandera == "1")
                      $idfac = $fact->insertfacturablanco($clienteCEO,$fiscales,$_SESSION['emp'],"4.0");
                    else if ($bandera == "2")
                      $idfac = $fact->insertfacturablanco($clienteCEO,"0",$_SESSION['emp'],"4.0");

                    $fact->actualizapago($fpago, "MXN", "PUE", NULL, $idfac, "1", "CONTADO");
                    $sqlu = 'UPDATE facturas SET f_uso = "G03", f_estatus = "P" WHERE f_id = "'.$idfac.'"';
                    setq($sqlu) or die($sqlu);

                    $idfacd = $fact->insertcon($idfac,"1",$_POST['importe'],"E48","TIEMPO AIRE ELECTRÓNICO","83111600","0","1","1");
                    
                  }
                }
              }
              $sqlt = 'UPDATE tae SET t_estatus = "A" WHERE t_cxcobrar = "'.$_GET['idcxc'].'"';
              setq($sqlt) or die($sqlt);

              $fAplica = 'UPDATE depositos SET d_faplica = "'.date('Y-m-d H:i:s').'" WHERE d_id = "'.$idDep.'" AND d_idtae = "'.$tae.'"';
              $resultAplica = setqJDTAE($fAplica);
            }
          }

          $sqli = 'UPDATE ingresos SET i_estatus = "F" WHERE i_id="'.$this->model->iding.'"';
          setq($sqli) or die($sqli);
          $tae = busca($_GET['idcxc'],'tae','t_cxcobrar','t_id');
          $link='?modulo=cxcobrar&accion=index';
        }
        else{
          $link='?modulo=ingresos&accion=show&id='.$this->model->iding.'&from='.$_GET['from'].'&idcxc='.$_GET['idcxc'];
        }

      }
        redirect($link);
    }*/
    function insert(){
      //foreachdie();
      //Verifico cuenta activa
      $fecha = str_replace("T"," ",$_POST['fecha']);
      include('cuentas.php');
      $cuent = new modelcuentas();
      $okcta = $cuent->cuentaactiva($_POST['cuenta']);

      if($okcta == "NOTOK"){
        echo '<script>
          var conf = confirm("La fecha de corte de la cuenta ha finalizado, ¿Desea abrir un corte nuevo a la cuenta?")
          if(conf == false){ document.location.href="?modulo=ingresos&accion=edit";}
          else{document.location.href="?modulo=ingresos&accion=cortenuevo&cuenta='.$_POST['cuenta'].'&nextmod=ingresos&nexta=edit&idcxc='.$_GET['idcxc'].'";}
        </script>';
        $link=' ?modulo=ingresos&accion=edit';
        die();
      }else{
        $corte = $okcta;
        $cliente = $_REQUEST['cliente'];
        //ID del corted
        $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
        $result = setq($sql);
        list($idcd) = $result->fetch_array();
        $idcd++;

        if(!$cliente) die(alert_back('El cliente ingresado no se encuentra en la lista de crm_clientes',true));

        $fechareg=date('Y-m-d H:i:s');
        $user=$_SESSION['uid'];
        $estatus='N';

        if(isset($_FILES['adjunto']['name'])){
          $diradj = 'Adjuntos/ingresos';
          if (!is_dir($diradj)) {
            @mkdir($diradj, 0777);
          }
          $info = new SplFileInfo(basename($_FILES['adjunto']['name']));
          $ext = $info->getExtension();
          $gid = getmax('i_id','ingresos');
          $target = $diradj.'/ingreso'.$gid.'.'.$ext;
          if(move_uploaded_file($_FILES['adjunto']['tmp_name'], $target)) $target = 'ingreso'.$gid.'.'.$ext;
          else $target = NULL;
        }else $target = NULL;

        $this->model->setData(NULL,$_POST['importe'],$cliente,$_POST['referencia'],$_POST['cuenta'],$fecha,$fechareg,$user,$estatus,$_POST['observaciones'],$target,$corte, "1");
        $this->model->insert();
        $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C",$this->model->iding,$_POST['importe'],"R",$_POST['cuenta']);
        $cuent->insertcd();

        if(isset($_POST['idlist'])) $j = sizeof($_POST['idlist']);
        else if(isset($_GET['idcxc']))$j = 1;

        if(isset($_POST['importelote']) && $_POST['importelote'] < $_POST['importe']) $estlote = 'A';
        else $estlote = 'F';

        $idproy = busca("N","proyecciones","p_estatus","p_id");

        if(isset($_GET['idcxc']) || $_POST['idlist']){
          for($i=0; $i<$j; $i++){
            if(isset($_POST['idlist'])){
              $idcc = $_POST['idlist'][$i];
              $importelt = $_POST['saldolist'][$i];
            }else{
              $idcc = $_GET['idcxc'];
              $importelt = $_POST['importe'];
            }
            $abonado = busca($idcc,'cxcobrar','cx_id','cx_abonado');
            $this->model->asignar($this->model->iding,$idcc,$importelt,$abonado);
            $tae = busca($idcc,'tae','t_cxcobrar','t_id');
            if($tae){
              $sqlTAE = 'SELECT d_id,d_factura,d_cliente,cl_ceo,d_formapago FROM depositos INNER JOIN clientes ON d_cliente = cl_id 
                          WHERE d_idtae = "'.$tae.'"';
              $resultTAE = setqJDTAE($sqlTAE);
              list($idDep,$facturaT,$clienteTAE,$clienteCEO,$formaPago) = $resultTAE->fetch_array();

              if($facturaT != null && $facturaT != ""){
                if($facturaT == "S"){
                  if($clienteCEO != null && $clienteCEO != ""){
                    include_once('facturas.php');
                    $fact = new ModelFacturas();
                    $fiscales = busca($clienteCEO,'crm_fiscales','cf_predeterminada = "1" AND cf_cliente','cf_id');

                    if($fiscales != null && $fiscales != "")
                      $bandera = "1";
                    else{
                      $bandera = "2";
                    }

                    if($formaPago != null && $formaPago != ""){
                      if($formaPago == "1" || $formaPago == "5")
                        $fpago = "1";
                      else if ($formaPago == "3" || $formaPago == "4")
                        $fpago = "3";
                      else if ($formaPago == "2")
                        $fpago = "2";
                    }

                    if($bandera == "1")
                      $idfac = $fact->insertfacturablanco($clienteCEO,$fiscales,$_SESSION['emp'],"4.0");
                    else if ($bandera == "2")
                      $idfac = $fact->insertfacturablanco($clienteCEO,"0",$_SESSION['emp'],"4.0");

                    $fact->actualizapago($fpago, "MXN", "PUE", NULL, $idfac, "1", "CONTADO");
                    $sqlu = 'UPDATE facturas SET f_uso = "G03", f_estatus = "P" WHERE f_id = "'.$idfac.'"';
                    setq($sqlu) or die($sqlu);

                    $idfacd = $fact->insertcon($idfac,"1",$importelt,"E48","TIEMPO AIRE ELECTRÓNICO","83111600","0","1","1");
                    
                  }
                }
              }
              $sqlt = 'UPDATE tae SET t_estatus = "A" WHERE t_cxcobrar = "'.$idcc.'"';
              setq($sqlt) or die($sqlt);

              $fAplica = 'UPDATE depositos SET d_faplica = "'.date('Y-m-d H:i:s').'" WHERE d_id = "'.$idDep.'" AND d_idtae = "'.$tae.'"';
              $resultAplica = setqJDTAE($fAplica);
            }
          }

          $sqli = 'UPDATE ingresos SET i_estatus = "'.$estlote.'" WHERE i_id="'.$this->model->iding.'"';
          setq($sqli) or die($sqli);
          //$tae = busca($_GET['idcxc'],'tae','t_cxcobrar','t_id');
          $link='?modulo=cxcobrar&accion=index';
        }
        else{
          if(!isset($_GET['from'])) $_GET['from'] = "ingresos";
          $link='?modulo=ingresos&accion=show&id='.$this->model->iding.'&from='.$_GET['from'].'&idcxc='.$_GET['idcxc'];
        }
      }
      redirect($link);
    }
    function detalles(){
      $sql='SELECT * FROM ingresos WHERE i_id="'.$_GET['id'].'"';
      $result=setq($sql) or die($sql);
      $row=$result->fetch_array();
      echo '<table class="lista" border="0" cellspacing="3" width="100%">';
        echo '<thead><tr><th colspan="4"><b>INGRESO '.$row['i_id'].'</b></th></tr></thead>';
        echo '<tr>
          <th>Adjunto</th>
          <th colspan="2">Observaciones</th>
        </tr>';
        echo '<tr>';
          if($row['i_adjunto']){
            $ext=end(explode(".", $row['i_adjunto']));
            echo '<td>';
              if(in_array($ext, $this->extenciones))
                echo '<a target="_blank" href="'.$row['i_adjunto'].'"><input type="button" value="" class="botont" id="mostrar"></a><br>';
              echo '<a href="'.$row['i_adjunto'].'" download="Comprobante gasto variable '.$row['i_id'].'">Descargar Archivo</a>';
            echo '</td>';
          }else{
            echo '<td></td>';
          }
          echo '<td colspan="2">'.$row['i_observaciones'].'</td>';
        echo '</tr>
      </table>';
    }
    function asignar(){
      if(!isset($_GET['tipo']))    $_GET['tipo'] = NULL;

      $sql='SELECT i_monto,i_cliente,i_cuenta,i_corte FROM ingresos WHERE i_id="'.$_GET['id'].'"';
      $result=setq($sql) or die($sql);
      list($monto,$cliente,$cuenta,$corte)=$result->fetch_array();

      $sqlx='SELECT * FROM cxcobrar WHERE cx_cliente="'.$cliente.'"  AND cx_estatus IN ("A","N")
            AND DATE(cx_fini) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" ';
      if($_GET['tipo']){
        $sqlx.=' AND cx_tipo="'.$_GET['tipo'].'"';
      }
      $resultx = setq($sqlx);
      while($row=$resultx->fetch_array()){
        if(($_POST['c'.$row['cx_id']]) AND ($_POST['i'.$row['cx_id']]>0)){
          $this->model->asignar($_GET['id'],$row['cx_id'],$_POST['i'.$row['cx_id']],$row['cx_abonado']);
        }
      }
      $ingreso = $_GET['id'];
      $sqlm = 'SELECT * FROM ingreso_cxcobrar INNER JOIN ingresos ON i_id = ic_ingreso
              WHERE ic_ingreso="'.$ingreso.'" AND ic_ingreso != "C"';
      $resultm = setq($sqlm) or die($sqlm);
      $total = 0;
      while($rowi = $resultm->fetch_array()){
        $total+=$rowi['ic_monto'];
      }

      if($total >= $monto){
        $sqli = 'UPDATE ingresos SET i_estatus = "F" WHERE i_id="'.$_GET['id'].'"';
        setq($sqli) or die($sqli);
        
      }else if($total > 0){
        $sqli = 'UPDATE ingresos SET i_estatus = "A" WHERE i_id="'.$_GET['id'].'"';
        setq($sqli) or die($sqli);
      }
      $link='?modulo=ingresos&accion=show&id='.$_GET['id'].'&from=ingresos';
      redirect($link);
    }
    function fondear(){
      $sql='SELECT i_monto,i_cliente,i_cuenta,i_corte FROM ingresos WHERE i_id="'.$_GET['id'].'"';
      $result=setq($sql) or die($sql);
      list($monto,$cliente,$cuenta,$corte)=$result->fetch_array();
      $fecha=date('Y-m-d H:i:s');
      $user=$_SESSION['uid'];

      $asignado=busca($_GET['id'],'ingreso_cxcobrar','ic_ingreso','SUM(ic_monto)');
      $monto=$monto-$asignado;
      $asigtot = 0;
      $sql='SELECT * FROM cxcobrar WHERE cx_cliente="'.$cliente.'"  AND cx_estatus IN ("A","N") ORDER BY cx_fini ASC ';
      $result=setq($sql) or die($sql);
      $temp=$monto;
      while($row=$result->fetch_array()){
        if($temp>0){
          $saldo=$row['cx_importe']-$row['cx_abonado'];
          if($temp>$saldo){
            $asig=$saldo;
            $temp=$temp-$saldo;
          }else{
            $asig=$temp;
            $temp=0;
          }
          $asigtot += $asig;
          $sqlm = 'SELECT MAX(ic_id) FROM ingreso_cxcobrar WHERE ic_ingreso="'.$_GET['id'].'" ';
          $resultm = setq($sqlm) or die($sqlm);
          list($id) = $resultm->fetch_array();
          $id++;

          $sqli='INSERT INTO ingreso_cxcobrar SET
              ic_id="'.$id.'",
              ic_ingreso="'.$_GET['id'].'",
              ic_cxcobrar="'.$row['cx_id'].'",
              ic_monto="'.$asig.'",
              ic_fapli="'.$fecha.'",
              ic_uapli="'.$user.'";';
          setq($sqli) or die($sqli);

          $total=$row['cx_abonado']+$asig;

          if($total >= $row['cx_importe']){
            $sqli = 'UPDATE cxcobrar SET cx_estatus = "F", cx_abonado="'.$total.'", cx_ffin ="'.$fecha.'"
                      WHERE cx_id="'.$row['cx_id'].'"';
            setq($sqli) or die($sqli);
            $this->model->completaventa($row['cx_id']);
          }
          else{
            $sqli = 'UPDATE cxcobrar SET cx_estatus = "A", cx_abonado="'.$total.'" WHERE cx_id="'.$row['cx_id'].'"';
            setq($sqli) or die($sqli);
          }
        }else{
          break;
        }
      }

      if($asigtot == $monto){
        $sqli = 'UPDATE ingresos SET i_estatus = "F" WHERE i_id="'.$_GET['id'].'"';
        setq($sqli) or die($sqli);
      }elseif($asigtot > 0){
        $sqli = 'UPDATE ingresos SET i_estatus = "A" WHERE i_id="'.$_GET['id'].'"';
        setq($sqli) or die($sqli);
      }

      $link='?modulo=ingresos&accion=show&id='.$_GET['id'].'&from=ingresos';
      redirect($link);
    }
    function cortenuevo(){
      include('cuentas.php');
      $cuent = new modelcuentas();
                  //DIE("--");
      $sql = 'SELECT MAX(c_id) FROM cortes';
      $result = setq($sql);
      list($cuent->id) = $result->fetch_array();
      $cuent->id++;

      $saldo=saldo_actual($_GET['cuenta']);
      $cuent->actualizarsaldo($_GET['cuenta'],$saldo);
      $cuent->actualizarp($_GET['cuenta']);
      $cuent->setDatap($cuent->id, date('Y-m-d'),date('Y-m-d',strtotime('last day of this month')),$_GET['cuenta'],$saldo);
      $cuent->insertp();

      $link='?modulo=ingresos&accion=edit&idcxc='.$_GET['idcxc'];
      redirect($link);
    }
    function cancelaring(){
      $this->model->select($_GET['id']);
      $corteact = busca($this->model->cuenta,'cortes','c_estatus = "A" AND c_cuenta','c_id');
      $saldoact = saldo_actual($this->model->cuenta);
      /* echo 'corte: '.$this->model->corte.' corteact:'.$corteact.' saldoact: '.$saldoact.' monto:'.$this->model->monto; 
      die(); */
      //if($this->model->corte === $corteact && $saldoact >= $this->model->monto){
        if($this->model->estatus != "P"){
          $sql = 'SELECT * FROM ingreso_cxcobrar WHERE ic_ingreso = "'.$this->model->id.'"';
          $result = setq($sql) or die($sql);
          $resultn = setq($sql) or die($sql);
          if($resultn->num_rows > 0){
            while($row = $result->fetch_array()){
              $sql = 'UPDATE cxcobrar SET cx_abonado=cx_abonado-'.$row['ic_monto'].'
                      WHERE cx_id = "'.$row['ic_cxcobrar'].'"';
              setq($sql) or die($sql);

              if(busca($row['ic_cxcobrar'],'cxcobrar','cx_id','cx_abonado') == 0)
                $sqlu = 'UPDATE cxcobrar SET cx_estatus = "N" WHERE cx_id = "'.$row['ic_cxcobrar'].'"';
              else
                $sqlu = 'UPDATE cxcobrar SET cx_estatus = "A" WHERE cx_id = "'.$row['ic_cxcobrar'].'"';
              setq($sqlu) or die($sqlu);

              /*$sqldel = 'DELETE FROM ingreso_cxcobrar WHERE ic_id = "'.$row['ic_id'].'" AND ic_ingreso = "'.$row['ic_ingreso'].'" 
                          AND ic_cxcobrar = "'.$row['ic_cxcobrar'].'"';
              setq($sqldel);*/
            }
          }
        }
        /* $idcxc = busca($this->model->id, 'ingreso_cxcobrar', 'ic_ingreso', 'ic_cxcobrar');
        $remision = busca($idcxc, 'cxcobrar', 'cx_id', 'cx_referencia');
        $valida= busca($idcxc, 'ingresos INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso INNER JOIN cxcobrar ON cx_id = ic_cxcobrar', 'i_estatus = "F" AND cx_id', 'COUNT(*)');
        $valida2 = busca($remision, 'cajasd', 'cd_remision', 'COUNT(*)');
        if(intval($valida) == 0 && intval($valida2) == 0){
          $sqlupd = 'UPDATE remisiones SET r_estatus = "N" WHERE r_id = "'.$remision.'"';
          setq($sqlupd);
        } */
        $sqle = 'UPDATE ingresos SET i_estatus = "C", i_ucan = "'.$_SESSION['uid'].'", i_fcan = "'.date('Y-m-d H:i:s').'", i_motivocan = "'.$_GET['motivo'].'"
                WHERE i_id = "'.$this->model->id.'"';
        setq($sqle) or die($sqle);

        if($this->model->estatus != "P"){
          $sqlcd = 'SELECT cd_id FROM cortesd WHERE cd_tipo = "C" AND cd_idref = "'.$this->model->id.'"';
          $resultcd = setq($sqlcd);
          list($idcorteor) = $resultcd->fetch_array();
        

          include('cuentas.php');
          $cuent = new modelcuentas();

          //ID del corted
          $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$this->model->corte.'"';
          $result = setq($sql);
          list($idcd) = $result->fetch_array();
          $idcd++;
          $cuent->setdatacd($idcd,$this->model->corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"P","",$this->model->monto,"X",$this->model->cuenta);
          $cuent->insertcd();

          $sql = 'UPDATE cortesd SET cd_reverso = "'.$idcorteor.'" WHERE cd_id = "'.$idcd.'" AND cd_corte = "'.$this->model->corte.'"';
          setq($sql);

          $sql = 'UPDATE cortesd SET cd_reverso = "'.$idcd.'", cd_observaciones = "MOVIMIENTO '.$idcorteor.'" WHERE cd_id = "'.$idcorteor.'" AND cd_corte = "'.$this->model->corte.'"';
          setq($sql);
        }

        if(!isset($_GET['from']))
          $link=' ?modulo=cuentas&accion=show&id='.$this->model->cuenta;
        else{
          if($_GET['from'] == "ingresos") $link=' ?modulo=ingresos&accion=index';
          else $link=' ?modulo=ingresos&accion=index';
        }

        $cuerpo = 'Ingreso NO aprobado';
        $grupos = '"VENTAS","ADMIN","GERENCIA"';
        echo "
        <script>
          $.ajax({
            url: 'task/notificacionpushgrupos.php',
            method: 'POST',
            data: {
                    'tipo': 'INGRESONOAPROBADO',
                    'grupos': '".$grupos."',
                    'ingreso': '".$this->model->id."',
                    'cuerpo': '".$cuerpo."'
                  },
          }).done(function(data){
            window.location.href = '".$link."';
          });          
          </script>
        ";

        /* redirect($link); */
      /* }else{
        echo '<script>
          alert("No es posible cancelar el ingreso actual, verifique que el ingreso este en el corte actual de la cuenta o que su cuenta tenga saldo suficiente para generar el egreso.");
          window.location.href="?modulo=ingresos&accion=show&id='.$_GET['id'].'&from='.$_GET['from'].'";
        </script>';
      } */
    }

    function confirmarpago(){
      //foreachdie();
      $ing = $_GET['id'];

      $sql = 'SELECT cx_id, ic_monto, cx_abonado, cx_referencia FROM ingreso_cxcobrar INNER JOIN cxcobrar ON  ic_cxcobrar = cx_id WHERE ic_ingreso = "'.$ing.'"';
      $result = setq($sql);
      list($idcxc, $monto, $abonado, $remision) = $result -> fetch_array();
      $tablero = busca($remision, 'remisiones', 'r_id', 'r_tablero');

      $valida= busca($idcxc, 'ingresos INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso INNER JOIN cxcobrar ON cx_id = ic_cxcobrar', 'i_estatus = "F" AND cx_id', 'COUNT(*)');
      $valida2 = busca($remision, 'cajasd', 'cd_estatus = "A" AND cd_remision', 'COUNT(*)');
      if(intval($valida) == 0 && intval($valida2) == 0){
        
        include('remisiones.php');
        $rem = new modelremisiones();
        $rem -> aplicar($remision);
        $uaplica = busca($ing, 'ingresos', 'i_id', 'i_ugen');
        $faplica = busca($ing, 'ingresos', 'i_id', 'i_fgen');
        $sqlupd = 'UPDATE remisiones SET r_estatus = "A", r_uaplica="'.$uaplica.'", r_faplica="'.$faplica.'"  WHERE r_id = "'.$remision.'"';
        setq($sqlupd);
      }

      $this->model->asignar($ing, $idcxc, $monto, $abonado, "P");

      $cuenta = busca($ing, 'ingresos','i_id', 'i_cuenta'); 
      include('cuentas.php');
      $cuent = new modelcuentas();
      $okcta = $cuent->cuentaactiva($cuenta);
      $corte = $okcta;
      $idcd = getmax('cd_id','cortesd','cd_corte = "'.$corte.'"');
      $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C",$ing,$monto,"R",$cuenta);
      $cuent->insertcd();

      /* $sql = 'UPDATE remisiones SET r_ffin = "'.date('Y-m-d',strtotime('+10 days')).'" WHERE r_id = "'.$remision.'"';
      setq($sql); */

      $cuerpo = 'Ingreso aprobado';
      $grupos = '"VENTAS","ADMIN","GERENCIA"';
      echo "
      <script>
        $.ajax({
          url: 'task/notificacionpushgrupos.php',
          method: 'POST',
          data: {
                  'tipo': 'INGRESOAPROBADO',
                  'grupos': '".$grupos."',
                  'ingreso': '".$ing."',
                  'cxc': '".$idcxc."',
                  'cuerpo': '".$cuerpo."'
                },
        }).done(function(data){
          
        });          
        </script>
      ";

      $grupos = '"LOGISTIC"';
        echo "
        <script>
          $.ajax({
            url: 'task/notificacionpushgrupos.php',
            method: 'POST',
            data: {
                    'tipo': 'REMISIONGUIA',
                    'grupos': '".$grupos."',
                    'ingreso': '".$ing."',
                    'cuerpo': '".$cuerpo."'
                  },
          }).done(function(data){
            window.location.href = '?modulo=ingresos&accion=show&id=".$_GET['id']."&from=ingresos';
          });          
          </script>
        ";
      /* redirect('?modulo=ingresos&accion=show&id='.$_GET['id'].'&from=ingresos'); */
    }
  }

  class Modelingresos{

    function completalicencia($idcxc){
         // PROCESO DESDE TYE
         $existelicenciarenovacion = busca(busca($idcxc,'cxcobrar','cx_id','cx_referencia'),'licencias_renovaciones','lr_remision','lr_id');
         if($existelicenciarenovacion){
           $lmid = busca($existelicenciarenovacion,'licencias_renovaciones','lr_id','lr_modalidad');
           
    
           $sqlmod = 'SELECT lm_usuarios, lm_ventas, lm_producto, lm_tipo, lm_periodo, lm_articulos FROM licencias_modalidades WHERE lm_id = "'.$lmid.'"';
           $resultmod = setq($sqlmod);
           list($usuarios,$ventas,$productoid,$tipo,$periodo,$articulos) = $resultmod->fetch_array();
    
           $sqlicrn = 'SELECT lr_licencia, lr_importe, lr_fini, lr_ffin, lr_flimite FROM licencias_renovaciones WHERE lr_id = "'.$existelicenciarenovacion.'"';
           $resulticrn = setq($sqlicrn);
           list($empresalic,$importe,$fini,$ffin,$flimite) = $resulticrn->fetch_array();
    
           if($periodo == "M") $ffinlic = date('Y-m-d', strtotime($fini.' +1 month'));
           elseif($periodo == "A") $ffinlic = date('Y-m-d', strtotime($fini.' +1 year'));
           elseif($periodo == "T") $ffinlic = date('Y-m-d', strtotime($fini.' +3 month'));
    
           $empresacliente = busca($empresalic,'licencias','l_id','l_folder');
           $totlicu = 0;
    
           if($tipo == "L"){
    
             $sqllica = 'SELECT * FROM licencias_renovaciones WHERE lr_licencia = "'.$empresalic.'" AND lr_estatus = "A" ';
             $resultlica = setq($sqllica);
             while($rowlica = $resultlica->fetch_array()){
               $tipomodA = busca($rowlica['lr_modalidad'],'licencias_modalidades','lm_id','lm_tipo');
               if($tipomodA == "L"){
                 $sql = 'UPDATE licencias_renovaciones SET
                         lr_estatus = "X"
                         WHERE lr_id = "'.$rowlica['lr_id'].'"';
                 setq($sql);
               }elseif($tipomodA == "U"){
                 $totlicu++;
               }
             }
             
    
             $sqlinsertdatoslic = 'UPDATE empresa_licencia SET
                                   el_modalidad = "'.$lmid.'",
                                   el_usuarios = "'.($usuarios+$totlicu).'",
                                   el_ventas = "'.$ventas.'",
                                   el_articulos = "'.$articulos.'",
                                   el_fini = "'.$fini.'",
                                   el_ffin = "'.$ffinlic.'",
                                   el_fpago = "'.$flimite.'",
                                   el_estatus = "A",
                                   el_importe = "'.$importe.'"
                                   WHERE el_id = "'.$empresalic.'"';
             setqjdsuite($sqlinsertdatoslic);
    
    
    
             $sql = 'UPDATE licencias_renovaciones SET
                     lr_estatus = "A"
                     WHERE lr_id = "'.$existelicenciarenovacion.'"';
             setq($sql);
     
           }elseif($tipo == "U"){
    
             $sqlbuscarjdsuite = 'SELECT el_usuarios FROM empresa_licencia WHERE el_id = "'.$empresalic.'"';
             $resultbuscajdsuite = setqjdsuite($sqlbuscarjdsuite);
             list($usuariosexis) = $resultbuscajdsuite->fetch_array();
             $nuevousu = $usuariosexis+$usuarios;
             $updateusers = 'UPDATE empresa_licencia SET
                             el_usuarios = "'.$nuevousu.'"
                             WHERE el_id = "'.$empresalic.'"';
             setqjdsuite($updateusers);
    
             
             $sql = 'UPDATE licencias_renovaciones SET
                     lr_estatus = "A"
                     WHERE lr_id = "'.$existelicenciarenovacion.'"';
             setq($sql);
     
           }elseif($tipo == "P"){
    
             $sql = 'UPDATE licencias_adicionales SET
                     la_estatus = "A"
                     WHERE la_cxcobrar = "'.$idcxc.'"';
             setqjdsuite($sql);
     
           }elseif($tipo == "F"){
    
    
             $sql = 'INSERT INTO folios SET
                     f_cliente = "'.$empresacliente.'",
                     f_contratados = "'.$articulos.'",
                     f_fcontrato = "'.date('Y-m-d').'",
                     f_usados = "0",
                     f_estatus = "1",
                     f_asigno = "'.$_SESSION['uid'].'",
                     f_pago = "'.date('Y-m-d').'",
                     f_refpago = "",
                     f_monto = "'.$importe.'",
                     f_aviso = "N",
                     f_idfd = NULL';
             setqjdsuite($sql);
    
             $sql = 'UPDATE licencias_adicionales SET
                     la_estatus = "A"
                     WHERE la_cxcobrar = "'.$idcxc.'"';
             setqjdsuite($sql);
    
           }
    
    
         }
    
         // PROCESO DESDE JDSUITE
         $existeidcxc = buscajdsuite($idcxc,'licencias_adicionales','la_cxcobrar','la_id');
         if($existeidcxc){
           
           $tipolic = buscajdsuite($existeidcxc,'licencias_adicionales','la_id','la_tipo');
           
           $idproducto = buscajdsuite($existeidcxc,'licencias_adicionales','la_id','la_paquete');
           $sqlmod = 'SELECT lm_id, lm_usuarios, lm_ventas, lm_producto, lm_tipo, lm_periodo, lm_articulos, lm_precio FROM licencias_modalidades WHERE lm_producto = "'.$idproducto.'"';
           $resultmod = setq($sqlmod);
           list($modalidad,$usuarios,$ventas,$productoid,$tipo,$periodo,$articulos,$importe) = $resultmod->fetch_array();
           
    
           $empresacliente = buscajdsuite($existeidcxc,'licencias_adicionales','la_id','la_empresa');
           $empresalic = busca($empresacliente,'licencias','l_folder','l_id');
           $remisonid = busca($idcxc,'cxcobrar','cx_id','cx_referencia');
    
    
           $numl = busca($empresalic,'licencias_renovaciones','lr_licencia','COUNT(*)');
           $diasg = busca($empresalic,'licencias','l_id','l_diasgracia');
           $fini = date('Y-m-d');
    
    
           $sql = 'UPDATE licencias_adicionales SET
                   la_estatus = "A",
                   la_fini = "'.date('Y-m-d').'"
                   WHERE la_id = "'.$existeidcxc.'"';
           setqjdsuite($sql);
    
           if($tipolic == "F"){
         
             $sql = 'INSERT INTO folios SET
                     f_id = "1",
                     f_cliente = "'.$empresacliente.'",
                     f_contratados = "'.$articulos.'",
                     f_fcontrato = "'.date('Y-m-d').'", 
                     f_usados = "0",
                     f_estatus = "1",
                     f_asigno = "'.$_SESSION['uid'].'",
                     f_pago = "'.date('Y-m-d').'",
                     f_refpago = "",
                     f_monto = "'.$importe.'",
                     f_aviso = "N",
                     f_idfd = NULL
                     ON DUPLICATE KEY UPDATE
                     f_contratados = "'.$articulos.'",
                     f_fcontrato = "'.date('Y-m-d').'", 
                     f_usados = "0",
                     f_estatus = "1",
                     f_asigno = "'.$_SESSION['uid'].'",
                     f_pago = "'.date('Y-m-d').'",
                     f_refpago = "",
                     f_monto = "'.$importe.'",
                     f_aviso = "N",
                     f_idfd = NULL';
             setqjdsuite($sql);
    
           }elseif($tipolic == "L" || $tipolic == "U"){
    
             $totlicu = 0;
             $sqllica = 'SELECT * FROM licencias_renovaciones WHERE lr_licencia = "'.$empresalic.'" AND lr_estatus = "A" ';
             $resultlica = setq($sqllica);
             while($rowlica = $resultlica->fetch_array()){
               $tipomodA = busca($rowlica['lr_modalidad'],'licencias_modalidades','lm_id','lm_tipo');
               if($tipomodA == "L"){
                 $sql = 'UPDATE licencias_renovaciones SET
                         lr_estatus = "X"
                         WHERE lr_id = "'.$rowlica['lr_id'].'"';
                 setq($sql);
               }elseif($tipomodA == "U"){
                 $totlicu++;            
               }
             }
    
             if($periodo == "M") $ffin = date('Y-m-d', strtotime($fini.' +1 month'));
             elseif($periodo == "A") $ffin = date('Y-m-d', strtotime($fini.' +1 year'));
             elseif($periodo == "T") $ffin = date('Y-m-d', strtotime($fini.' +3 month'));
             //$ffin = date('Y-m-d',strtotime($fini.'+1 month'));
             $flim = date('Y-m-d',strtotime($ffin.'+'.$diasg.' days'));
         
             $clave = str_pad($existeidcxc,4,"0",STR_PAD_LEFT).str_pad(date('Y'),4,"0",STR_PAD_LEFT).str_pad(date('m'),4,"0",STR_PAD_LEFT).str_pad($numl,3,"0",STR_PAD_LEFT); //4num empresa-4 año-2mes-3numlic
         
             $sql = 'INSERT INTO licencias_renovaciones SET
                     lr_licencia = "'.$empresalic.'",
                     lr_clave = "'.$clave.'",
                     lr_importe = "'.$importe.'",
                     lr_fecha = "'.date('Y-m-d').'",
                     lr_fini = "'.$fini.'",
                     lr_ffin = "'.$ffin.'",
                     lr_flimite = "'.$flim.'",
                     lr_modalidad = "'.$modalidad.'",
                     lr_estatus = "A",
                     lr_remision = "'.$remisonid.'"';
             setq($sql);
    
             if($tipo == "L"){
    
               $sqlinsertdatoslic = 'UPDATE empresa_licencia SET
                                     el_modalidad = "'.$modalidad.'",
                                     el_usuarios = "'.($usuarios+$totlicu).'",
                                     el_ventas = "'.$ventas.'",
                                     el_articulos = "'.$articulos.'",
                                     el_fini = "'.$fini.'",
                                     el_ffin = "'.$ffin.'",
                                     el_fpago = "'.$flim.'",
                                     el_estatus = "A",
                                     el_importe = "'.$importe.'"
                                     WHERE el_id = "'.$empresalic.'"';
               setqjdsuite($sqlinsertdatoslic);
    
             }elseif($tipo == "U"){
    
               $sqlbuscarjdsuite = 'SELECT el_usuarios FROM empresa_licencia WHERE el_id = "'.$empresalic.'"';
               $resultbuscajdsuite = setqjdsuite($sqlbuscarjdsuite);
               list($usuariosexis) = $resultbuscajdsuite->fetch_array();
               $nuevousu = $usuariosexis+$usuarios;
               $updateusers = 'UPDATE empresa_licencia SET
                               el_usuarios = "'.$nuevousu.'"
                               WHERE el_id = "'.$empresalic.'"';
               setqjdsuite($updateusers);
    
             }
    
    
    
           }
    
    
    
         }
    
      

      ////////////////////////////
    }

    function result($estatus,$fini,$ffin,$cliente,$page) { //filtro
      $bloque = 50;
      $sqlf = ' WHERE DATE(i_fgen) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      if($estatus){
        $sqlf .= ' AND i_estatus = "'.$estatus.'" ';
      }
      if($cliente){
        $clid = ""; $ccid = "";
        $sqlcl = 'SELECT c_id FROM crm_clientes WHERE c_nmb LIKE "%'.$cliente.'%" OR c_alias LIKE "%'.$cliente.'%" ';
        $resultcl = setq($sqlcl);
        $nrowcl = $resultcl->num_rows;
        $b = 0;
        while($rowcl = $resultcl->fetch_array()){
          $b++;
          $clid .= '"'.$rowcl['c_id'].'"';
          if($b != $nrowcl) $clid .= ',';
        }

        $sqlcc = 'SELECT c_id FROM concesionarios WHERE c_nmb LIKE "%'.$cliente.'%"';
        $resultcc = setq($sqlcc);
        $nrow = $resultcc->num_rows;
        $a = 0;
        while($rowcc = $resultcc->fetch_array()){
          $a++;
          $ccid .= '"'.$rowcc['c_id'].'"';
          if($a != $nrow) $ccid .= ',';
        }

        if($clid && $ccid) $coma = ',';
        $sqlf .= ' AND i_cliente IN ('.$clid.$coma.$ccid.')';
      }
      $sql = 'SELECT * FROM ingresos '.$sqlf.' ORDER BY i_fgen DESC,i_id DESC';
      $this->result = setq($sql) or die($sql);
      $this->resultt = setq($sql) or die($sql);
    }
    function setData($id,$monto,$cliente,$referencia,$cuenta,$fecha,$fechareg,$user,$estatus,$observaciones,$adjunto,$corte, $fpago, $comision = 0){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
      $simboldi = array('\"',"\'","\#","\$","\%", "\&","\/","\(","\)","\=","\?","\¡","\*","\+","\~","\^","\[","\°","\|","\{","\}","\[","\]");
      $this->id = $id;
      $this->monto= $monto;
      $this->cliente= $cliente;
      $this->referencia= str_replace($simbol,$simboldi,mb_strtoupper(trim($referencia)));
      $this->cuenta=$cuenta;
      $this->fecha=$fecha;
      $this->fgen=$fechareg;
      $this->user=$user;
      $this->estatus=$estatus;
      $this->observaciones= str_replace($simbol,$simboldi,mb_strtoupper(trim($observaciones)));
      $this->adjunto=$adjunto;
      $this->corte=$corte;
      $this->fpago=$fpago;
      $this->comision=$comision;
    }
    function insert(){
      $sql='INSERT INTO ingresos SET
      i_monto="'.$this->monto.'",
      i_cliente="'.$this->cliente.'",
      i_referencia="'.$this->referencia.'",
      i_cuenta="'.$this->cuenta.'",
      i_fecha="'.$this->fecha.'",
      i_fgen="'.$this->fgen.'",
      i_ugen="'.$this->user.'",
      i_corte="'.$this->corte.'",
      i_estatus="'.$this->estatus.'",
      i_adjunto="'.$this->adjunto.'",
      i_fpago="'.$this->fpago.'",
      i_comision="'.$this->comision.'",
      i_observaciones="'.$this->observaciones.'"';
      setq($sql);
      $this->iding = getmax('i_id','ingresos',false,false);
    }
    function select($id) {
      $cing = busca($id,'ingreso_cxcobrar','ic_ingreso','COUNT(*)');
      if($cing > 0){
        $sqlixc = ' INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso INNER JOIN cxcobrar 
                  ON ic_cxcobrar = cx_id ';
      }else {
        $sqlixc = '';
      }
      $sql = 'SELECT * FROM ingresos '.$sqlixc.' WHERE i_id= "'.$id.'"';
      $result = setq($sql) or die($sql);
      if($result)$row = $result->fetch_array();
      $this->id= $row['i_id'];
      $this->monto= $row['i_monto'];
      $this->cliente=$row['i_cliente'];
      $this->referencia=$row['i_referencia'];
      $this->cuenta= $row['i_cuenta'];
      $this->fecha= $row['i_fecha'];
      $this->fgen= $row['i_fgen'];
      $this->ugen= $row['i_ugen'];
      $this->corte= $row['i_corte'];
      $this->estatus= $row['i_estatus'];
      $this->adjunto= $row['i_adjunto'];
      $this->observaciones= $row['i_observaciones'];
      $this->clientecxc = $row['i_cliente'];
      $this->fpago = $row['i_fpago'];
      $this->comision = $row['i_comision'];
      if($cing > 0) $this->tipo = $row['cx_tipo'];
    }
    function asignar($ingreso,$idcxc,$importecxc,$abonado, $estatus = "F"){
      $fecha=date('Y-m-d H:i:s');
      $user=$_SESSION['uid'];
      $asignado=busca($ingreso,'ingreso_cxcobrar','ic_ingreso','SUM(ic_monto)');
      $sqlm = 'SELECT MAX(ic_id) FROM ingreso_cxcobrar WHERE ic_ingreso="'.$ingreso.'" ';
      $resultm = setq($sqlm) or die($sqlm);
      list($id) = $resultm->fetch_array();
      $id++;
      if($estatus != "P"){
        $sqli='INSERT INTO ingreso_cxcobrar SET
              ic_id="'.$id.'",
              ic_ingreso="'.$ingreso.'",
              ic_cxcobrar="'.$idcxc.'",
              ic_monto="'.$importecxc.'",
              ic_fapli="'.$fecha.'",
              ic_uapli="'.$user.'";';
       setq($sqli);
      } else {
        $sqlupd = 'UPDATE ingresos SET i_estatus = "F" WHERE i_id = "'.$ingreso.'"';
        setq($sqlupd);
        $estatus = "F";
      }
      if($estatus == "F"){  
        $total=$abonado+$importecxc;
        $totalcxc = busca($idcxc,'cxcobrar','cx_id','cx_importe');
        if($total >= $totalcxc){
          $sqli = 'UPDATE cxcobrar SET cx_estatus = "F", cx_abonado="'.$total.'", cx_ffin ="'.$fecha.'"
                  WHERE cx_id="'.$idcxc.'"';
          setq($sqli) or die($sqli);
          $tipoap = busca($idcxc,'cxcobrar','cx_id','cx_tipo');
          $sqlcx = 'UPDATE cortesd SET cd_tipom = "'.$tipoap.'" WHERE cd_tipo = "C" AND cd_idref = "'.$ingreso.'"';
          setq($sqlcx);

          $remision = busca($idcxc,'cxcobrar','cx_id','cx_referencia');
          $envio = busca($remision, 'crm_cotizaciones', 'cc_remision', 'cc_envio');

          //if($envio == "P"){
            $sql = 'UPDATE remisiones SET r_estatus = "F" WHERE r_id = "'.$remision.'"';
            setq($sql);

            $sqlarticulos = 'UPDATE remisionesc SET rc_estatus = "P" WHERE rc_estatus = "N" AND rc_tipoenvio = "C" AND rc_remision = "'.$remision.'"';
            setq($sqlarticulos);

            $sqlarticulos2 = 'UPDATE remisionesc SET rc_estatus = "A" WHERE rc_estatus = "N" AND rc_tipoenvio IN ("D", "O") AND rc_remision = "'.$remision.'"';
            setq($sqlarticulos2);
          //}
          //$this->completaventa($idcxc);
        }else{
          $sqli = 'UPDATE cxcobrar SET cx_estatus = "A", cx_abonado="'.$total.'"
                    WHERE cx_id="'.$idcxc.'"';
          setq($sqli) or die($sqli);
        }

        $existeproyeccion = busca($idcxc,'proyeccionesd INNER JOIN proyecciones ON p_id = pd_proyeccion ','p_estatus = "N" AND pd_tipo = "C" AND pd_referencia','pd_id');
        if($existeproyeccion){
          $ecxc = busca($idcxc,'cxcobrar','cx_id','cx_estatus');
          $importe = busca($existeproyeccion,'proyeccionesd','pd_id','pd_importe');
          if($ecxc == "F" || $importe == $importecxc){
            $sqlupproyecccion = 'UPDATE proyeccionesd SET
                                  pd_estatus = "F"
                                  WHERE pd_id = "'.$existeproyeccion.'"';
            setq($sqlupproyecccion);
          }
        }
      }
    }

    function completaventa($ccid){
      //inserto requisición y orden de compra
      $sql='SELECT cx_referencia FROM cxcobrar WHERE cx_id="'.$ccid.'"';
      $result = setq($sql) or die($sql);
      list($remision) = $result->fetch_array();

      $sqlrd = 'SELECT * FROM remisionesd INNER JOIN articulos ON a_id = rd_articulo
                WHERE rd_remision = "'.$remision.'" AND rd_articulo != ""';
      $resultrd = setq($sqlrd);
      $nump = $resultrd->num_rows;

      $pedidoweb = busca($remision,'remisiones','r_id','r_pedidoweb');
      $clienteceo = busca($remision,'remisiones','r_id','r_cliente');
      if($nump > 0 && $remision && $pedidoweb){
        $sqlut = 'UPDATE crm_tableros SET ct_estatus = "A" WHERE ct_pedidoweb = "'.$pedidoweb.'"';
        setq($sqlut);
        $resultrd = setq($sqlrd) or die($sqlrd);
        //compruebo disponibilidad del producto
        $i = 0;
        //Exploro proveedores y genero su orden de compra
        $acrof = busca(1,'empresas','e_id','e_siglas').'-OC';
        $sqlap = 'SELECT DISTINCT(ap_proveedor) FROM articulo_proveedor INNER JOIN remisionesd ON ap_articulo = rd_articulo
                  WHERE rd_remision = "'.$remision.'" AND ap_activo = "1" AND ap_estatus = "1"';
        $resultap = setq($sqlap);
        while($rowap = $resultap->fetch_array()){
          include_once('ordenesc.php');
          $ordn = new modelordenesc();
          $folioint = getmax('o_folio','ordenesc');
          if($folioint == "1") $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
          $tablero = busca($remision,'remisiones','r_id','r_tablero'); //insertar tablero en orden de compra.
          $ordn->setdata(NULL,$empresa,$folioint,$rowap['ap_proveedor'],date('Y-m-d'),"PEDIDO ".$pedidoweb." JD SHOP","P",0,0,0,0,"JD SHOP",$tablero,1,2);
          $idorden = $ordn->insert();
          $sqldp = 'SELECT rd_articulo,rd_cantidad,ap_costo,rd_nmbarticulo,rd_linean,ap_usd,a_noparte
                    FROM remisionesd INNER JOIN articulo_proveedor ON rd_articulo = ap_articulo
                    INNER JOIN articulos ON rd_articulo = a_id
                    WHERE ap_proveedor = "'.$rowap['ap_proveedor'].'" AND ap_activo = "1" AND ap_estatus = "1"
                    AND rd_remision = "'.$remision.'"';
          $resultdp = setq($sqldp);
          while($rowdp = $resultdp->fetch_array()){
            $idrec = getmax('od_id','ordenescd','od_ordenc');
            //Hago conversión usd
            $vusd = busca(1,'empresas','e_id','e_valorusd');
            if($rowdp['ap_usd'] == "1" && $vusd != 0) $costo = $rowdp['ap_costo']*$vusd;
            else $costo = $rowdp['ap_costo'];
            $ordn->setDatad($idorden, $idrec, $rowdp['rd_cantidad'], $rowdp['rd_articulo'],$rowdp['rd_nmbarticulo'],$costo,0,$rowdp['rd_linean'],$rowdp['a_noparte']);
            $ordn->insertdetalle();
            $ordn->setprecio($idorden);
          }
          $ordn->select($idorden);
          $sql3 = 'UPDATE ordenesc SET  o_fapli = "'.date('Y-m-d H:i:s').'", o_uapli = "JD SHOP",o_estatus = "P"
                    WHERE o_id="' . $idorden . '"';
          setq($sql3);
          $clienteweb = busca($pedidoweb,'pedidoscl','p_id','p_cliente');
          $receptor = busca($clienteceo,'crm_clientes','c_id','CONCAT(c_nmb," ",c_apellidos)');
          $telw =  busca($clienteceo,'crm_clientes','c_id','c_telefono1');
          $idcotiza = busca($pedidoweb,'pedidoscl','p_id','p_cotizacion');
          if($idcotiza){
            $dirweb = busca($idcotiza,'crm_cotizaciones','cc_id','cc_direnvio');
            //$sqlde = 'SELECT CONCAT(cd_calle," ",cd_nume," ",cd_numi," ",cd_colonia," ",cd_municipio," ",e_nmb," ",cd_cp," TEL: '.$telw.'")
            $sqlde = 'SELECT CONCAT("CALLE: ", cd_calle," ",cd_nume," ",cd_numi,", COL: ",cd_colonia,", MUNICIPIO: ",cd_municipio,", ESTADO: ",e_nmb,", CP: ",cd_cp,", TEL: '.$telw.'")
                      FROM crm_direcciones INNER JOIN estados ON e_clave = cd_estado
                      WHERE cd_cliente = "'.$clienteceo.'" AND cd_id = "'.$dirweb.'"';
            $resultde = setq($sqlde);
          }
          else{
            //$telw =  busca($clienteceo,'crm_clientes','c_id','c_telefono1');
            $telw = "";
            $direweb = busca($pedidoweb,'pedidoscl','p_id','p_direccion');
            //$sqlde = 'SELECT CONCAT(d_calle," ",d_nume," ",d_numi," ",d_colonia," ",d_municipio," ",d_estado," ",d_cp," TEL: ",d_telefono)
            $sqlde = 'SELECT CONCAT("CALLE: ",d_calle," ",d_nume," ",d_numi,", COL: ",d_colonia,", MUNICIPIO: ",d_municipio,", ESTADO: ",d_estado,", CP: ",d_cp,", TEL: ",d_telefono)
                      FROM direnvio
                      WHERE d_cliente = "'.$clienteweb.'" AND d_id= "'.$direweb.'"';
            $resultde = setq($sqlde);
          }
          list($direnvio) = $resultde->fetch_array();

          $sql = 'SELECT pc_nmb,pc_correo FROM proveedores_contactos WHERE pc_proveedor = "'.$ordn->proveedor.'" LIMIT 0,1';
          $result = setq($sql);
          list($nombrepc,$correopc) = $result->fetch_array();
          //$correopc = 'caepegu08@gmail.com';
          $ordn->updateaplicar($idorden,$nombrepc,$correopc,date('Y-m-d'),$receptor,$direnvio,"PEDIDO GENERADO AUTOMATICAMENTE DESDE JD SHOP");
          $ordn->sendmail($idorden,"A");

        /*
          include_once('cxpagar.php');
          $cxp = new modelcxpagar();
          $cxp->insertcxpagar($empresa,$rowap['ap_proveedor'],$idorden,"C",$ordn->total,date('Y-m-d H:i:s'),"ORDEN DE COMPRA ".$idorden);

        */        }
        include_once('pedidosclw.php');
        $pediw = new modelpedidosclw();
        $pediw->confirmpagosh($pedidoweb);
      }
    }
  }

  class Viewingresos {
    function __construct($model) {
      $this->model = $model;
      $this->extenciones = array('PNG','JPG','JPEG','GIF','RAW','BMP','TXT','PDF','png','jpg','jpeg','gif','raw','bmp','txt','pdf');
      $this->estatusp = array("N"=>"EN CAPTURA","A"=>"APLICADO","C"=>"CANCELADO","F"=>"FINALIZADO","P"=>"POR CONFIRMAR");
      $this->tipop = array("R"=>"REMISIÓN","P"=>"PRESTAMO","C"=>"COMISIÓN","A"=>"ACREEDORES DIVERSOS","O"=>"OTROS INGRESOS");
      $this->estatuscx = array("N"=>"NUEVA","F"=>"FINALIZADO","C"=>"CANCELADO","A"=>"ABONADO","T"=>"TODOS", "P"=>"PENDIENTE");
    }
    function browse($estatus,$fini,$ffin,$cliente,$page) {
      $selt = '';
      $seln = '';
      $sela = '';
      $self = '';
      $selc = '';
      $selp = '';
      if($estatus) $est = '&estatus='.$estatus;
      if($estatus == "N") $seln = "selected";
      elseif($estatus == "A") $sela = "selected";
      elseif($estatus == "F") $self = "selected";
      elseif($estatus == "C") $selc = "selected";
      elseif($estatus == "P") $selc = "selected";
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
        $nuevo = '<a  id="nuevo" data-fancybox data-type="ajax" data-src="popup/setingreso.php?rand='. rand(1,999).'" href="javascript:;" >
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>';
        $filtro = '<form class="form-inline" role="form" method="post" action="?modulo=ingresos&accion=index" id="filtro">
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
                  <label for="tipom">Cliente:</label>
                  <input onkeyup="ceropage();" type="text" class="form-control" id="cliente" name="cliente" placeholder="Nombre del cliente que buscas" onfocus="this.select();" value="'.$cliente .'" />
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
                    <option value="N" '.$seln .' >En Proceso</option>
                    <option value="A" '.$sela .' >En aplicación</option>
                    <option value="F" '.$self .' >Finalizados</option>
                    <option value="C" '.$selc .' >Cancelados</option>
                    <option value="P" '.$selp .' >Pendientes de confirmar</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                  <button  class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
                  </button>
                  <a href="?modulo=ingresos&accion=index">
                    <button class="btn btn-warning">
                      <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
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
              <table class="table" id="myTable">
                <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
                  <tr>
                    <th>Ver</th>
                    <th>Cliente</th>
                    <th>Registró</th>
                    <th>Fecha</th>
                    <th>Cuenta</th>
                    <th>Monto</th>
                    <th>Estatus</th>
                  </tr>
                </thead>';
                if ($this->model->result){
                  while ($row=$this->model->result->fetch_array()) {
                    if(busca(busca($row['i_id'],'ingreso_cxcobrar','ic_ingreso','ic_cxcobrar'),'cxcobrar','cx_id','cx_tipo') == "P"){
                      $alias = busca($row['i_cliente'],'concesionarios','c_id','c_nmb');
                    }else{
                      $alias = busca($row['i_cliente'],'crm_clientes','c_id','c_nmb').' '.busca($row['i_cliente'],'crm_clientes','c_id','c_apellidos');
                    }
                    echo '<tr>
                      <th>
                        <a href="?modulo=ingresos&accion=show&id='.$row['i_id'].'&from=ingresos">
                          <button class="btn btn-info"><i class="fas fa-chevron-circle-right"></i></button>
                        </a>
                      </th>';
                      echo '<td align=center>'.$alias.'</td>';
                      echo '<td align=center>'.busca($row['i_ugen'],'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)').'</td>';
                      echo '<td align=center>'.fecha_formato($row['i_fgen'],true,true).'</td>';
                      echo '<td align=center>'.busca($row['i_cuenta'],'cuentas','cu_id','cu_nmb').'</td>';
                      echo '<td class="number-align">$ '.number_format($row['i_monto'],2).'</td>';
                      if($row['i_estatus'] == "N"){
                        echo '<td class="alert" style= "background:#EDE619">NUEVO</a></td>';
                      }else if($row['i_estatus'] == "A"){
                        echo '<td class="alert" style="background:#64AFF0">EN APLICACIÓN</a></td>';
                      }else if($row['i_estatus'] == "C"){
                        echo '<td class="alert" style="background:#F03D21;color:#FFFFFF;">CANCELADO</a></td>';
                      }else if($row['i_estatus'] == "F"){
                        echo '<td class="alert" style="background:#1BF030;">FINALIZADO</a></td>';
                      }else if($row['i_estatus'] == "P"){
                        echo '<td class="alert" style="background:#FFB162;">POR CONFIRMAR</a></td>';
                      }
                    echo '</tr>';
                  }
                }
              echo '</table>
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
    function edit() {
      $con = 'SELECT c_nmb FROM crm_clientes';
      $query = setq($con) or die($con);
      while ($row = mysql_fetch_array($query)) {
        $elementos[] = '"'.$row['c_nmb'].'"';
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
      if(isset($_GET['idcxc']) || isset($_GET['cxcobrar'])) $link = "?modulo=cxcobrar&accion=index";
      else $link = "?modulo=ingresos&accion=index";
      echo '<div class="div2" id="bboton01">
        <a href="'.$link.'" accesskey="t"><input type="button" value=" " class="botonb" id="atras"></a>
      </div></div>';
      if (isset($this->model->id)) {
        $accion = 'update&id='.$this->model->id;
        $monto=$this->model->monto;
      }
      else{
        $accion = 'insert';
        if(isset($_GET['idcxc'])){
          $monto = busca($_GET['idcxc'],'cxcobrar','cx_id','(cx_importe-cx_abonado)');
          $this->model->cliente = busca(busca($_GET['idcxc'],'cxcobrar','cx_id','cx_cliente'),'crm_clientes','c_id','c_alias');
        }
        else $monto=0;
      }
      if(isset($_GET['ref'])) $this->model->referencia = $_GET['ref'];
      echo '<form autocomplete="off" method=post name="pro" action="?modulo=ingresos&accion='.$accion.'&idcxc='.$_GET['idcxc'].'&from='.$_GET['from'].'" enctype="multipart/form-data" onsubmit="return checkSubmitguardar();" >
        <center>
          <table class="lista" border="0" cellspacing="3" width="100%">
            <thead>
              <tr><th colspan="3"><b>INGRESO NUEVO</b></th></tr>
            </thead>
            <tbody>';
              echo '<tr>
                <th>Monto</th>
                <th>Cliente</th>
                <th>Referencia</th>
              </tr>';
              echo '<tr>
                <td><input type="number" onfocus="this.select();" style="width:80px" min="0.01" name="monto" step="0.01" value="'.number_format($monto,2,'.','').'" autofocus required /></td>
                <td><input type="text" name="cliente" value="'.$this->model->cliente.'" id="tags" required autofocus placeholder="Nombre del cliente..." style="width:100%" /></td>
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
          if(conf) document.location.href = "?modulo=ingresos&accion=cancelar&id="+aux;
        }
        function fondear(aux){
          var conf = confirm("¿Deseas aplicar el saldo restante, desde la cuenta mas antigua a la mas actual?");
          if(conf) document.location.href = "?modulo=ingresos&accion=fondear&id="+aux;
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

      $estcorte = busca($this->model->corte,'cortes','c_id','c_estatus'); 
      $botones = "";

        $atras = '<a href="?modulo=ingresos&accion=index" accesskey="t">
          <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Atrás </button>
        </a>';
        echo '<script>
          function cancelaing(){
            
              Swal.fire({
                  icon: "question",
                  title: "Motivo de la cancelación..",
                  input: "textarea",
                  inputPlaceholder: "Escribe aquí...",
                  showCancelButton: true,
                  cancelButtonText: "Cancelar",
                  confirmButtonText: "Guardar",
                  inputValidator: (value) => {
                    if (!value || value=="") {
                      return "Debes ingresar un motivo antes de guardar.";
                    }
                  },
                  preConfirm: (texto) => {
                    //ccmotivo.value = texto;
                    document.location.href="?modulo=ingresos&accion=cancelaring&id='.$this->model->id.'&from='.$_GET['from'].'&motivo="+texto;
                    document.getElementById("cancelar").disable == true;
                  } 
                });
            
          }
          function aplicaring(){
            var conf = confirm("¿Deseas aplicar este ingreso?");
            if(conf == true){
              document.location.href="?modulo=ingresos&accion=confirmarpago&id='.$this->model->id.'&from='.$_GET['from'].'";
              document.getElementById("aplicar").disable == true;
            }
          }
        </script>';
        if($this->model->estatus == "P")
          $botones.= '<button class="btn btn-success me-1" onclick="aplicaring();" data-toggle="tooltip" data-placement="top" title="Aplicar Ingreso"><i class="fas fa-check"></i> Aplicar</button>';
        
        if($this->model->estatus != "C" && $this->model->estatus != "F")
          $botones.= '<button class="btn btn-danger" onclick="cancelaing();" data-toggle="tooltip" data-placement="top" title="Cancelar Ingreso"><i class="fa fa-times"></i> Cancelar</button>';
        
        $i = 0;
        $adjunto = busca($this->model->id, 'ingresos', 'i_id', 'i_adjunto');
        $path = 'adjuntos/ingresos/';

        if($adjunto){
          $botones .= '
            <a target="_BLANK" href="'.$path.$adjunto.'">
              <button type="button" class="btn btn-primary"> <i class="far fa-file"></i> Comprobante</button>
            </a>';
        }

        toolbar($_GET['modulo'], $atras, "", "", $botones);
        echo '<div class="main-card mb-3 mt-2" style="background: white ">
        <div class="card-body row">
        <center>
          <div class="alert alert-primary mt-5">Resumen de ingreso</div>
          <div class="table table-responsive table-hover">
            <center>
              <table class="table table-hover table-striped">
                <thead class="thead bg-primary text-white pt-1 pb-1">';
                  echo '<tr>
                    <th>Monto</th>
                    <th>Comisión</th>
                    <th>Cliente</th>
                    <th>Observaciones</th>
                    <th>Fecha y usuario</th>
                    <th>Fecha del ingreso</th>
                    <th>Ingresdo a cuenta</th>
                  </tr>
                </thead>
                <tbody>';
                  echo '<tr>
                    <td>$ '.number_format($this->model->monto,2).'</td>
                    <td>$ '.number_format($this->model->comision,2).'</td>';
                    
                    if($this->model->tipo == "P") echo '<td>'.busca($this->model->clientecxc,'concesionarios','c_id','c_nmb').'</td>';
                    else echo '<td>'.busca($this->model->cliente,'crm_clientes','c_id','c_alias').'</td>';
                    echo '<td>'.$this->model->observaciones.'</td>';
                    echo '<td>'.fecha_formato($this->model->fgen,true,true).'  '.busca($this->model->ugen,'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)').'</td>';
                    echo '<td>'.fecha_formato($this->model->fecha,true,true).'</td>';
                    echo '<td>'.busca($this->model->cuenta,'cuentas','cu_id','cu_nmb').'</td>';
                  echo '</tr>';
                echo '</tbody>
              </table>
            </center>';

          if($this->model->estatus=="A" || $this->model->estatus=="N"){
            $asignado=busca($this->model->id,'ingreso_cxcobrar','ic_ingreso','SUM(ic_monto)');
            if(!$asignado) $asignado=0;
            $restante=$this->model->monto-$asignado;

            $sqlfn = 'SELECT * FROM cxcobrar WHERE cx_cliente="'.$this->model->cliente.'"  AND cx_estatus IN ("A","N") 
                    AND DATE(cx_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
            if($tipo){
              $sqlfn.=' AND cx_tipo="'.$tipo.'"';
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
                echo '<form action="?modulo=ingresos&accion=show&id='.$this->model->id.'" method="post" onsubmit="return checkSubmitenviar();">
                  <table width="100%"><tr><td colspan="2" align="center">';
                    echo '<tr>
                      <td align="center"><b>Tipo: </b>
                        <select name="tipo" id="tipo" onchange="this.form.submit()" class="form-control">
                          <option value="">TODOS</option>';
                          $sqlt = 'SELECT * FROM cortes_tipom WHERE ct_tipo = "C"';
                          $resultt = setq($sqlt);
                          while($rowt = $resultt->fetch_array()){
                            echo '<option value="'.$rowt['ct_clave'].'">'.$rowt['ct_descripcion'].'</option>';
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
                $sql = 'SELECT * FROM cxcobrar WHERE cx_cliente="'.$this->model->cliente.'"  AND cx_estatus IN ("A","N") 
                        AND DATE(cx_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
                if($tipo){
                  $cad='&fini='.$fini.'&ffin='.$ffin.'&tipo='.$tipo;
                  $sql.=' AND cx_tipo="'.$tipo.'"';
                }else{
                  $cad='&fini='.$fini.'&ffin='.$ffin;
                }
                $sql.='ORDER BY cx_fini DESC, cx_id DESC';
                $result=setq($sql) or die($sql);
                if($result->num_rows > 0){
                  echo '<center>
                    <form action="?modulo=ingresos&accion=asignar&id='.$this->model->id.$cad.'" method="post" onsubmit="return checkSubmitenviar();">
                      <div id="diving" class="diving table-responsive">
                        <table class="table table-striped" width="100%">
                          <input type="hidden" id="restapas" value="'.$restante.'" />';
                          echo '<thead class="thead bg-light-blue bg-darken-2 text-white pt-1 pb-1">
                            <tr>
                              <th colspan="9"><b>Cuentas por cobrar de '.busca($this->model->cliente,'crm_clientes','c_id','c_nmb').'
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
                              $fecha = explode(" ", $row['cx_fini']);
                              $saldo=$row['cx_importe']-$row['cx_abonado'];
                              $cont++;
                              $setcxc = NULL;
                              if(isset($_GET['idcxc']) && $row['cx_id'] == $_GET['idcxc']){
                                $setcxc = $cont;
                              }
                              $desc = busca($row['cx_tipo'],'cortes_tipom','ct_tipo = "X" AND ct_clave','ct_descripcion');
                              if($row['cx_tipo'] == "R"){
                                $desc.=' '.busca($row['cx_referencia'],'remisiones','r_id','r_folio');
                              }
                              if($row['cx_estatus']=="N") $color="style='background-color:#FF5C5C;'";
                              else if($row['cx_estatus']=="A") $color="style='background-color:#FFE447'";
                              else if($row['cx_estatus']=="F") $color="style='background-color:#47B6FF'";
                              else if($row['cx_estatus']=="C") {
                                $color="style='background-color:#000000";
                                $saldo=0;
                              }
                              if(($row['cx_estatus']=='F') OR ($row['cx_estatus']=='C')) $link="#";
                              else $link='?modulo=cxcobrar&accion=cobro&cuenta='.$row['cx_id'];
                              echo '<input type="hidden" id="sal'.$cont.'" value="'.$saldo.'">';
                              echo '<tr>';
                                if($estcorte == "A")
                                  echo '<td><input type="checkbox" id="c'.$cont.'" name="c'.$row['cx_id'].'" onclick="habilitar('.$cont.');" tabindex="'.$cont.'" /></td>';
                                else 
                                  echo '<td><input type="hidden"/></td>';
                                echo '<td>'.$this->tipop[$row['cx_tipo']].'</td>';
                                echo '<td>'.$desc.'</td>';
                                echo '<td>'.fecha_formato($row['cx_fini'],true,true).'</td>';
                                echo '<td '.$color.'>'.$this->estatuscx[$row['cx_estatus']].'</td>';
                                echo '<td>$ '.number_format($saldo,2).'</td>';
                                echo '<td><input type="number" id="i'.$cont.'" name="i'.$row['cx_id'].'" class="form-control" value="0" onchange="calcular('.$cont.');" max="'.$saldo.'" min="0" disabled step="0.01" onfocus="this.select();" /></td>';
                                //echo '<td><a id="popup" href="popup/detallecobro.php?id='.$row['cx_id'].'" accesskey="d"><input type="button" value="" id="detalles" class="botont"></a></td>';
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
                            echo '<tr>
                              <td colspan="9"><center>VALIDAR&nbsp;<input type="checkbox" id="validador" onclick="validar();" required /></center></td>
                            </tr>';
                            echo '<tr>
                              <td colspan="9">
                                <center>
                                  <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Aplicar ingreso</button>
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
          $sql = 'SELECT * FROM ingreso_cxcobrar INNER JOIN cxcobrar ON ic_cxcobrar=cx_id
                  INNER JOIN cortesd ON cd_idref = ic_ingreso
                  INNER JOIN cortes_tipom ON ct_clave = cd_tipom
                  WHERE ic_ingreso="'.$_GET['id'].'" AND cd_tipo = "C" AND ct_tipo = "C" GROUP BY ic_ingreso';
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
                      //echo 'row: '.$row['cx_ingreso'];
                      //echo $row['ct_descripcion'];
                      if($row['cx_estatus']=="N") $color="style='background-color:#FF5C5C;'";
                      else if($row['cx_estatus']=="A") $color="style='background-color:#FFE447'";
                      else if($row['cx_estatus']=="F") $color="style='background-color:#47B6FF'";
                      else if($row['cx_estatus']=="C") {
                        $color="style='background-color:#000000;'";
                      }
                      $desc = $row['ct_descripcion'];
                      if($row['cx_tipo'] == "R") $desc.=' '.busca($row['cx_referencia'],'remisiones','r_id','r_folio');
                      if($row['cx_tipo'] == "P") $desc.=' '.busca($row['cx_referencia'],'ordenesp','op_id','op_folio');
                      echo '<tr>';
                        echo '<td>'.$row['cx_id'].' - '.$this->tipop[$row['cx_tipo']].'</td>';
                        echo '<td>'.$desc.'</td>';
                        echo '<td '.$color.'>'.$this->estatuscx[$row['cx_estatus']].'</td>';
                        echo '<td>'.fecha_formato($row['ic_fapli'],true,true).'</td>';
                        echo '<td>'.$row['ic_uapli'].'</td>';
                        echo '<td>$ '.number_format($row['ic_monto'],2).'</td>';
                      echo '</tr>';
                      $cont +=$row['ic_monto'];
                    }
                    echo '<tr>
                      <td colspan="5" style="text-align:right;"><b>TOTAL</b></td><td><b>$ '.number_format($cont,2).'</b></td>
                    </tr>';
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