<?php
  //ini_set('display_errors', 1);
  class ordenesc {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelordenesc(isset($obj));
    }
    function index() {
      if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-60 days'));
      if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d');
      if (!isset($_REQUEST['proveedor'])) $_REQUEST['proveedor'] = NULL;
      if (!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if (!isset($_REQUEST['folio'])) $_REQUEST['folio'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->result($_REQUEST['fini'],$_REQUEST['ffin'],trim($_REQUEST['proveedor']),$_REQUEST['estatus'],$_REQUEST['folio'],$_REQUEST['page']);
      $this->view = new viewordenesc($this->model);
      $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['estatus'],$_REQUEST['folio'],$_REQUEST['page']);
    }
    function show(){ 
      $this->model->select($_GET['id']);
      $this->model->resultd($_GET['id']);
      $this->view = new viewordenesc($this->model);
      $this->view->show();
    }
    function showoc(){ 
      $this->model->select($_GET['id']);
      $this->model->resultd($_GET['id']);
      $this->view = new viewordenesc($this->model);
      $this->view->showoc();
    } 
    function updatee(){
      $this->model->updatee($_GET['id']);
      $estatus = busca($_GET['id'],'ordenesc','o_id','o_estatus');
      if($estatus == "P") $dir = "showoc"; else $dir = "show";
      redirect('?modulo=ordenesc&accion='.$dir.'&id='.$_GET['id'].'');
    }
    function insert(){
      $acronimo = busca($_SESSION['emp'],'empresas','e_id','e_siglas');
      $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-OC';
      $folioint = getmax('o_folio','ordenesc','o_empresa = "'.$_SESSION['emp'].'"');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }
      if($_POST['tipo'] == "") $estatus = "N"; else $estatus = "P";
      if($_POST['diva'] == "") $diva = "0"; else $diva = "1";
      $this->model->setdata(NULL,$_SESSION['emp'],$folioint,$_POST['proveedor'],$_POST['fechacom'],$_POST['descripcion'],$estatus,0,0,0,0,$_POST['solicita'],$_POST['tablero'],$diva,$_POST['almacen']);
      $this->model->insert();

      if($estatus == "P") $dir = "showoc"; else $dir = "show";
      redirect('?modulo=ordenesc&accion='.$dir.'&id='.$this->model->id.'');
    }
    function update(){

      if($_POST['tipo'] == "") $estatus = "N"; else $estatus = "P";
      if($_POST['diva'] == "") $diva = "0"; else $diva = "1";

      $sql = 'SELECT * FROM ordenescd WHERE od_ordenc = "'.$_GET['id'].'"';
      $result = setq($sql);

      if($result->num_rows > 0){
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        $miva = busca($_GET['id'],'ordenesc','o_id','o_diva');
        while($row = $result->fetch_array()){
          $proceso = "1";
          if($miva == $diva) $proceso = "0";

          if($proceso == "1"){
            if($row['od_iva'] == "1" && $diva == "0"){
              $precio = $row['od_precio'] * (1+($viva/100));
              $sqlup = 'UPDATE ordenescd SET od_precio = "'.$precio.'" WHERE od_id = "'.$row['od_id'].'"';
              setq($sqlup);
            }elseif($row['od_iva'] == "1" && $diva == "1"){
              $precio = $row['od_precio'] / (1+($viva/100));
              $sqlup = 'UPDATE ordenescd SET od_precio = "'.$precio.'" WHERE od_id = "'.$row['od_id'].'"';
              setq($sqlup);
            }
          }
        }
      }


      $this->model->setdata($_REQUEST['id'],$_SESSION['emp'],$folioint,$_POST['proveedor'],$_POST['fechacom'],$_POST['descripcion'],$estatus,0,0,0,0,$_POST['solicita'],0,$diva,$_POST['almacen']);
      $this->model->update();

      $this->model->setprecio($_REQUEST['id']);

      $estatus = busca($_GET['id'],'ordenesc','o_id','o_estatus');
      if($estatus == "P") $dir = "showoc"; else $dir = "show";
      redirect('?modulo=ordenesc&accion='.$dir.'&id='.$this->model->id.'');

    }
    function insertdetalle(){
      $this->model->id = $_GET['id'];
      $articulo = getIDmp($_POST['producto']);

      if($articulo) {
        $sql = 'SELECT pmp_nmb, pmp_tipo FROM pr_materiaprima WHERE pmp_id = "'.$articulo.'"';
        $result = setq($sql);
        list($nmbprod, $tipo) = $result->fetch_array();
        if($tipo == "A") $nmbprod .=' Medidas: '.$_POST['largo'].' X '.$_POST['ancho'];
        else if($tipo == "V") $nmbprod .=' Medidas: '.$_POST['largo'].' X '.$_POST['ancho'].' X '.$_POST['alto'];
        if(isset($_POST['iva'])) $iva = 1; else $iva = 0;
        $importe = $_POST['importe'];
        
        $viva = $viva = busca("1",'configuracionesp','c_id','c_iva')/100;
        $diva = busca($this->model->id,'ordenesc','o_id','o_diva');
        if($diva == "1") {
          $importe = $_POST['importe'] / (1+$viva);
          $iva = 1;
        }else{
          $importe = $_POST['importe'];
          $iva = 0;
        }
        
        $idrec = getmax('od_id','ordenescd','od_ordenc');
        $this->model->setDatad($_GET['id'], $idrec, $_POST['cantidad'], $articulo,$nmbprod,$importe,$iva,$_POST['ln'],$_POST['nparte'],$_POST['largo'], $_POST['ancho'],$_POST['alto']);
        $this->model->insertdetalle();
        $this->model->setprecio($_GET['id']);
        $estatus = busca($_GET['id'],'ordenesc','o_id','o_estatus');
        if($estatus == "P") $dir = "showoc"; else $dir = "show";
      }
      redirect('?modulo=ordenesc&accion='.$dir.'&id='.$this->model->id.'');
    }
    function updatedetalle(){
      if(isset($_POST['iva']) && $_POST['iva'] == "on") $iva = 1; 
      elseif($_POST['iva'] == "1") $iva = 1;
      else $iva = 0;

      $viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');

      $precio =  $_POST['precio'];

      $this->model->setDatad($_GET['id'], $_GET['idprod'], $_POST['cantidad'], NULL,NULL,$precio,$iva,$_POST['ln'],NULL, '', '', '');
      $this->model->updatedetalle();

      $this->model->setprecio($_GET['id']);

      $estatus = busca($_GET['id'],'ordenesc','o_id','o_estatus');
      if($estatus == "P") $dir = "showoc"; else $dir = "show";
      redirect('?modulo=ordenesc&accion='.$dir.'&id='.$this->model->ordenc.'');
    }
    function delprod(){
      $this->model->delprod($_GET['id'],$_GET['idprod']);
      $this->model->setprecio($_GET['id']);

      $estatus = busca($_GET['id'],'ordenesc','o_id','o_estatus');
      if($estatus == "P") $dir = "showoc"; else $dir = "show";
      redirect('?modulo=ordenesc&accion='.$dir.'&id='.$_GET['id'].'');
    }
    function aplicar(){
      $this->model->select($_GET['id']);
      //if($this->model->estatus == "N" ) $this->model->updateaplicar($_GET['id'],$_POST['vendedor'],$_POST['mailvendedor'],$_POST['fechacom'],$_POST['receptor'],$_POST['destino'],$_POST['descripcion']);
      $this->model->updateaplicar($_GET['id'],$_POST['vendedor'],$_POST['mailvendedor'],$_POST['fechacom'],$_POST['receptor'],$_POST['destino'],$_POST['descripcion']);

      if($this->model->estatus == "N") {
        $estatus = "P";
        if(isset($_POST['checkmail'])) $sendemail = 1; else $sendemail = 0;
        $link = '?modulo=ordenesc&accion=index';
      }
      elseif($this->model->estatus == "P") {
        $estatus = "A";
        if(isset($_POST['checkmail'])) $sendemail = 1; else $sendemail = 0;
        $link = '?modulo=ordenesc&accion=showoc&id=' . $_GET['id'];
      }
      $this->model->aplicar($_GET['id'],$estatus,$sendemail);
      //$this->model->imprimir($_GET['movimiento'],$_GET['tipo']);
      //if($this->model->estatus == "P") $link = '?modulo=ordenesc&accion=index';
      //elseif($this->model->estatus == "A") $link = '?modulo=ordenesc&accion=showoc&id=' . $_GET['id'];
      redirect($link);
    }
    function importremd(){
      $tablero = busca($_GET['ordenc'],'ordenesc','o_id','o_tablero');
      $query = 'SELECT * FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision
                WHERE r_tablero = "'.$tablero.'" ORDER BY rd_nmbarticulo';
      $resultado = setq($query);
      $i = 0;
      while($row = $resultado->fetch_array()){
        if(isset($_POST['acpadre'.$row['rd_id']])){
          $sqlcmb = 'SELECT * FROM articulos_combos WHERE ac_articulo = "'.$row['rd_articulo'].'"';
          $resultcmb = setq($sqlcmb);
          while($rowcmb = $resultcmb->fetch_array()){
            if(isset($_POST['prod'.$rowcmb['ac_ahijo']])){
              $existe = busca($_GET['ordenc'],'ordenescd','od_articulo = "'.$rowcmb['ac_ahijo'].'" AND od_ordenc','COUNT(*)');
              if($existe == 0){
                $idrec = getmax('od_id','ordenescd','od_ordenc');
                $cantidad = $_POST['cantidad'.$rowcmb['ac_ahijo']];
                $costo = $_POST['costo'.$rowcmb['ac_ahijo']];
                $ln = busca($rowcmb['ac_ahijo'],'articulos','a_id','a_lineaneg');
                $nmbprod = busca($rowcmb['ac_ahijo'],'articulos','a_id','a_nmb');
                $iva = 0;
                $importe = $costo;
                $noparte = NULL;
                $precio = 'SELECT ap_iva,ap_usd,ap_noparteprov FROM articulo_proveedor WHERE ap_articulo = "'.$rowcmb['ac_ahijo'].'" AND ap_activo = "1"';
                $result = setq($precio);
                list($aiva,$usd,$noparte) = $result->fetch_array();
                if($aiva == "1") {
                  $iva = 1;
                  $importe = $costo * 1.16;
                }
                if($usd == "1") $importe = $costo * (busca($_SESSION['emp'],'empresas','e_id','e_valorusd'));

                if($cantidad > 0){
                  $this->model->setDatad($_GET['ordenc'], $idrec, $cantidad, $rowcmb['ac_ahijo'],$nmbprod,$importe,$iva,$ln,$noparte);
                  $this->model->insertdetalle();
                }
              }
            }
          }
        }else{
          $idrec = getmax('od_id','ordenescd','od_ordenc');
          $cantidad = $_POST['cantidad'.$row['rd_id']];
          $costo = $_POST['costo'.$row['rd_id']];
          $articulo = busca($row['rd_id'],'remisionesd','rd_id','rd_articulo');
          $ln = busca($articulo,'articulos','a_id','a_lineaneg');
          $nmbprod = busca($row['rd_id'],'remisionesd','rd_id','rd_nmbarticulo');
          $iva = 0;

          $importe = $costo;
          $noparte = NULL;
          if($articulo){
            $precio = 'SELECT ap_iva,ap_usd,ap_noparteprov FROM articulo_proveedor WHERE ap_articulo = "'.$articulo.'" AND ap_activo = "1"';
            $result = setq($precio);
            list($aiva,$usd,$noparte) = $result->fetch_array();
            if($aiva == "1") {
              $iva = 1;
              $importe = $costo * 1.16;
            }
            if($usd == "1") $importe = $costo * (busca($_SESSION['emp'],'empresas','e_id','e_valorusd'));
          }
          
          if($cantidad > 0){
            $this->model->setDatad($_GET['ordenc'], $idrec, $cantidad, $articulo,$nmbprod,$importe,$iva,$ln,$noparte);
            $this->model->insertdetalle();
          }
        }
      }
      $this->model->setprecio($_GET['ordenc']);
      echo '<script>
              window.opener.location.reload();
              window.close();
            </script>';
    }
    function reenviarcorreo(){
      $this->model->select($_GET['id']);

      $this->model->destino = $_POST['destino'];
      $this->model->mailvendedor = mb_strtolower($_POST['mailvendedor']);
      //$this->model->sendmail($_GET['id']);
      if(isset($this->model->solicita)){
        if($this->model->solicita != "JD SHOP") $send = checkmail($this->model->solicita);
        else $send = "1";
      }else $send = "0";

      if($send == "1"){
        $this->model->sendmail($_GET['id'],$this->model->estatus);
        redirect('?modulo=ordenesc&accion=show&id=' . $_GET['id']);
      }
      else 
        echo '<script>
          alert("No es posible enviar la orden de compra por correo al proveedor, debido a que no esta configurado correctamente el correo empresarial.\nPor favor termina de capturar la infromación para poder enviar correctamente la orden de compra");
          window.location.href="?modulo=ordenesc&accion=show&id='.$_GET['id'].'";
        </script>';
    }
    function cambiarcantidad(){
      if(isset($_REQUEST['cantidad'])){
        $sql = 'UPDATE ordenescd SET
                od_cantidad = "'.$_REQUEST['cantidad'].'"
                WHERE od_id = "'.$_GET['id'].'"';
        setq($sql);

        $this->model->setprecio($_GET['ordenc']);
      } 
      redirect("?modulo=ordenesc&accion=showoc&id=".$_GET['ordenc']);
    }
  }

  class modelordenesc {
    function result($fini,$ffin,$proveedor,$estatus,$documento,$page){  //filtro
      $bloque = 50;
      $prov = clearvmayus($proveedor);
      $doc = clearvmayus($documento);

      $sql = 'SELECT * FROM ordenesc WHERE o_empresa = "'.$_SESSION['emp'].'"
              AND DATE(o_fgen) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      if($proveedor) $sql.=' AND o_proveedor IN (SELECT p_id FROM proveedores WHERE p_nmb LIKE "%'.$prov.'%" OR p_alias LIKE "%'.$prov.'%")';
      if($documento) $sql.=' AND o_folio LIKE "%'.$doc.'%" ';
      if($estatus) $sql.=' AND o_estatus = "'.$estatus.'"';
      $sql.='ORDER BY o_id DESC';
      $result = setq($sql);
      if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
      $this->resultrc = setq($sql);
      $this->resultt = setq($sql);
    }
    function select($id){
      $sql = 'SELECT * FROM ordenesc WHERE o_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['o_id'];
      $this->empresa = $row['o_empresa'];
      $this->proveedor = $row['o_proveedor'];
      $this->folio = $row['o_folio'];
      $this->fechacom= $row['o_fechacom'];
      $this->observaciones = $row['o_observaciones'];
      $this->estatus = $row['o_estatus'];
      $this->fgen = $row['o_fgen'];
      $this->ugen = $row['o_ugen'];
      $this->fapli = $row['o_fapli'];
      $this->uapli = $row['o_uapli'];
      $this->fcan = $row['o_fcan'];
      $this->ucan = $row['o_ucan'];
      $this->subtotal = $row['o_subtotal'];
      $this->iva = $row['o_iva'];
      $this->descuento = $row['o_descuento'];
      $this->total = $row['o_total'];
      $this->solicita = $row['o_solicita'];
      $this->receptor = $row['o_receptor'];
      $this->destino = $row['o_destino'];
      $this->vendedor = $row['o_vendedor'];
      $this->mailvendedor = $row['o_mailvendedor'];
      $this->tablero = $row['o_tablero'];
      $this->diva = $row['o_diva'];
      $this->almacen = $row['o_almacen'];
    }
    function setdata($id,$empresa,$folio,$proveedor,$fechacom,$observaciones,$estatus,$subtotal,$iva,$descuento,$total,$solicita,$tablero,$diva,$almacen){
      $this->id = $id;
      $this->folio = $folio;
      $this->proveedor = $proveedor;
      $this->fechacom = $fechacom ;
      $this->observaciones = clearvmayus($observaciones);
      $this->estatus = $estatus;
      $this->subtotal = $subtotal;
      $this->iva = $iva;
      $this->descuento = $descuento;
      $this->total = clearvmayus($total);
      $this->solicita = $solicita;
      $this->tablero = $tablero;
      $this->diva = $diva;
      $this->almacen = $almacen;
    }
    function insert(){
      $sql = 'INSERT INTO ordenesc SET
              o_empresa = "'.$_SESSION['emp'].'",
              o_folio = "'.$this->folio.'",
              o_tablero = "'.$this->tablero.'",
              o_fechacom= "'.$this->fechacom.'",
              o_solicita= "'.$this->solicita.'",
              o_proveedor = "'.$this->proveedor.'",
              o_observaciones = "'.$this->observaciones.'",
              o_fgen = "'.date('Y-m-d H:i:s').'",
              o_ugen = "'.$_SESSION['uid'].'",
              o_diva = "'.$this->diva.'",
              o_almacen = "'.$this->almacen.'",
              o_estatus	 = "'.$this->estatus.'"';
      setq($sql);

      $this->id = getmax('o_id','ordenesc','o_empresa = "'.$_SESSION['emp'].'"',false);
      return($this->id);
    }
    function updatee($id){
      $sql = 'UPDATE ordenesc SET
              o_estatus = "N"
              WHERE o_id = "'.$id.'"';
      setq($sql);
    }
    function update(){
      $sql = 'UPDATE ordenesc SET
              o_fechacom= "'.$this->fechacom.'",
              o_solicita= "'.$this->solicita.'",
              o_proveedor = "'.$this->proveedor.'",
              o_observaciones = "'.$this->observaciones.'",
              o_estatus = "'.$this->estatus.'",
              o_almacen = "'.$this->almacen.'",
              o_diva = "'.$this->diva.'"
              WHERE o_id = "'.$this->id.'"';
      setq($sql);
    }
    function resultd($id){
      $sql = 'SELECT * FROM ordenescd WHERE od_ordenc = "'.$id.'" ORDER BY od_id';
      $this->resultr = setq($sql);
    }
    function setDatad($ordenc, $idorden, $cantidad, $articulo,$nmbarticulo, $importe, $iva, $ln,$nparte, $largo, $ancho, $alto){
      $this->ordenc = $ordenc;
      $this->idorden = $idorden;
      $this->cantidad = $cantidad;
      $this->articulo = $articulo;
      $this->nmbarticulo = $nmbarticulo;
      $this->importe = $importe;
      $this->iva = $iva;
      $this->ln = $ln;
      $this->nparte = $nparte;
      $this->largo = $largo;
      $this->ancho = $ancho;
      $this->alto = $alto;  
    }
    function insertdetalle(){
      $sql = 'INSERT INTO ordenescd SET
              od_ordenc = "'.$this->ordenc.'",
              od_id = "'.$this->idorden.'",
              od_articulo = "'.$this->articulo.'",
              od_nmbarticulo = "'.$this->nmbarticulo.'",
              od_cantidad = "'.$this->cantidad.'",
              od_precio = "'.$this->importe.'",
              od_iva = "'.$this->iva.'",
              od_nparte = "'.$this->nparte.'",  
              od_linean = "'.$this->ln.'",
              od_largo = "'.$this->largo.'",
              od_ancho = "'.$this->ancho.'",
              od_alto = "'.$this->alto.'"';
      setq($sql);
    }
    function updatedetalle(){
      $sql = 'UPDATE ordenescd SET
              od_cantidad = "'.$this->cantidad.'",
              od_precio = "'.$this->importe.'",
              od_iva = "'.$this->iva.'"
              WHERE od_ordenc = "'.$this->ordenc.'" AND od_id = "'.$this->idorden.'"';
      setq($sql);
    }
    function aplicar($id,$estatus,$sendemail){
      if($estatus == "P") $sqla = ', o_diva = "1"';
      else $sqla = '';
      $sql3 = 'UPDATE ordenesc SET  o_fapli = "'.date('Y-m-d H:i:s').'", o_uapli = "'.$_SESSION['uid'].'",o_estatus = "'.$estatus.'"
              '.$sqla.' WHERE o_id="' . $id . '"';
      setq($sql3);

      if($sendemail == 1){
        if($this->solicita != "JD SHOP") $send = checkmail($this->solicita);
        else $send = "1";

        if($send == "1") $this->sendmail($id,$estatus);
        else 
          echo '<script>
            alert("No es posible enviar la orden de compra por correo al proveedor, debido a que no esta configurado correctamente el correo empresarial.\nPor favor termina de capturar la infromación para poder enviar correctamente la orden de compra");
            window.location.href="?modulo=ordenesc&accion=show&id='.$_GET['id'].'";
          </script>';
      }
      if($estatus == "A"){
        //echo 'crear cxpagar <br>';
        include_once('cxpagar.php');
        $cxp = new modelcxpagar();
        $idcxp = $cxp->insertcxpagar($this->empresa,$this->proveedor,$this->id,"C",$this->total,date('Y-m-d H:i:s'),"ORDEN DE COMPRA ".$this->folio);
        $cxp->addproyeccion($idcxp);
      }
    }
    function delprod($id,$idprod){
      $sql = 'DELETE FROM ordenescd WHERE od_ordenc = "'.$id.'" AND od_id = "'.$idprod.'"';
      setq($sql);
    }
    function setprecio($id){
      $viva = busca(1,'configuracionesp','c_id','c_iva')/100;
      $diva = busca($id,'ordenesc','o_id','o_diva');
      $sql = 'SELECT * FROM ordenescd WHERE od_ordenc = "'.$id.'"';
      $result = setq($sql);
      
      $total = 0;
      $descuento = 0;
      $totiva = 0;
      $iva = 0;
      $subtotalfin = 0;
      while($row = $result->fetch_array()){
        $precio = $row['od_precio'];
        $importe = $precio*$row['od_cantidad'];
        if($diva == "1"){
          if($row['od_iva'] == "1"){
            $iva=$importe*$viva;
          }elseif($row['od_iva'] == "0"){
            $iva= 0;
          }
        }else {
          $iva = 0;
        }

        $subtotalfin+= $importe;
        $totiva+=$iva;
      }
      $total = $subtotalfin+$totiva;
    
      $sql = 'UPDATE ordenesc SET
              o_subtotal = "'.$subtotalfin.'",
              o_descuento = "'.$descuento .'",
              o_iva = "'.$totiva.'",
              o_total = "'.$total.'"
              WHERE o_id = "'.$id.'" ';
      setq($sql);
    }
    function updateaplicar($id,$vendedor,$mailvendedor,$fechacom,$receptor,$destino,$descripcion){
      $sql = 'UPDATE ordenesc SET
              o_vendedor = "'.clearvmayus($vendedor).'",
              o_mailvendedor = "'.clearvminus($mailvendedor).'",
              o_fechacom = "'.$fechacom.'",
              o_receptor = "'.clearvmayus($receptor).'",
              o_destino = "'.clearvmayus($destino).'",
              o_observaciones = "'.clearvmayus($descripcion).'"
              WHERE o_id = "'.$id.'"';
      setq($sql);
    }
    function sendmail($ordenc,$estatus){
      
      $this->select($ordenc);

      require_once('lib/mailer/class.phpmailer.php');
      require_once("lib/mailer/class.smtp.php");
      $mail = new PHPMailer(); // defaults to using php "mail()"
      $asunto = 'ORDEN DE COMPRA '.$this->folio;
      
      if($this->solicita != "JD SHOP"){
        $puestoi = $this->solicita;
        $correoi = busca($this->solicita,'usuarios','u_id','u_mailcorp');
        $telefonoi = busca($this->solicita,'usuarios','u_id','u_telefono');
        $password = base64_decode(busca($this->solicita,'usuarios','u_id','u_contraseñacorp'));
        $host = busca($this->solicita,'usuarios','u_id','u_host');
        $seguridad = busca($this->solicita,'usuarios','u_id','u_seguridad');
        $puerto = busca($this->solicita,'usuarios','u_id','u_puerto');
        $remitente = busca($this->solicita,'usuarios','u_id','u_remitente');
      }
      else{
        $puestoi = "JD SHOP";
        $correoi = "contacto@jdshop.mx";
        $telefonoi = "771-104-1684";
        $password = "WpJDShop2022#";
        $seguridad = 0;
        $dominio = explode('@',$correoi);
        $host = $dominio[1];
        $puerto = 587;
        $remitente = "JD SHOP";
      }
      
        //echo $_GET['id'].' '.$this->folio.' ';
        if($this->solicita == "JD SHOP"){
          $_GET['id'] = $this->id;
        }

      if(isset($_GET['id'])){
        if($this->estatus == "P")include_once('formats/pdfordenc_save.php');
        elseif($this->estatus == "A") include_once('formats/pdfordencmail.php');

        $mail->AddAttachment('docs/ordenc/'.$this->folio.'.pdf');
      }

        $body = '<style type="text/css">
              .tablecot th, .tablecot td {
                padding-top: 5px;
                padding-bottom: 2px;
                padding-left: 10px;
                padding-right: 10px;
              }
              .tablecot th{
                background:#D4D4D4;
                border-bottom: 2px solid #000000;
              }
              .title-table{
                font-size:16px;
              }
              .number-import{
                font-size: 15px;
                text-align: right;
              }
              .logotip{
                width: 45px;
                height: 38px;
              }
              .cabeza{
                font-size: 18px;
                color: #003D7A;
                margin-bottom: 8px;
                text-align: center;
                margin-top: 10px;
              }
              .cliente{
                margin-top: 10px;
                margin-bottom: 10px;
                font-size: 15px;
                color: #003D7A;
                line-height: 1.3;
              }
              .cotiza{
                margin-top: 10px;
                margin-bottom: 10px;
                font-size: 13px;
                color: #333333;
                line-height: 1.3;
              }
              .fif {
                display: inline-block; /*Or inline-block*/
                margin-right: 30px;
                vertical-align: top; /* here */
                margin-top: 50px;
              }
            </style>';
        $logo = busca($_SESSION['emp'],'empresas','e_id','e_logo');
        if(file_exists($logo) && $logo != NULL){
          $body.='<center><img src="'.$logo.'"  width="160" height="130" /></center>';
        }
        include_once('modulos/proveedores.php');
        $prov= new modelproveedores();
        $prov->select($this->proveedor);

        include_once('modulos/config.php');
        $empres = new modelconfig();
        $empres->select($_SESSION['emp']);

        if($_POST['receptor']) $receptor = $_POST['receptor'];
        else $receptor = $this->receptor;

        if($_POST['destino']) $destino = $_POST['destino'];
        else $destino = $this->destino;

        if($_POST['descripcion']) $descripcion = $_POST['descripcion'];
        else $descripcion = $this->observaciones;

        if($estatus == "A" || $this->solicita == "JD SHOP"){
          if($this->solicita == "JD SHOP") $encabezado = "Solicitud de cotización: ";
          else $encabezado = "Orden de compra: ";

          $encabezado = '<div class="cabeza"><b>'.$encabezado.' </b>'.$this->folio.'</div>';
          $bodycliente = '<hr><div class="cliente" contenteditable="true">
                          <div class="fif">
                              <div><b>Fecha envío: </b>'.date('d/m/Y',strtotime($this->fapli)).'</div>
                              <div><b>Observaciones </b>'.$descripcion.'</div>
                            </div>
                          <div class="fif">
                            <div><b>Recibe: </b>'.$receptor.'</div>
                            <div><b>Dirección envio: </b>'.$destino.'</div>
                          </div>
                        </div>';
        }elseif($estatus == "P"){
          $encabezado = '<div class="cabeza"><b>Solicitud de cotización</b></div>';
          $bodycliente = '';
        }
        $bodyproveedor = '<hr>';

        $this->mensaje = "Solicito se me cotice la siguiente orden de compra<br>";
        $body.=utf8_decode($encabezado);
        $body.=utf8_decode($bodycliente);
        $body.=utf8_decode($bodyproveedor);
        $body.= utf8_decode(nl2br($this->mensaje));
        if($_POST['mailvendedor']) $mailvendedor = $_POST['mailvendedor'];
        else $mailvendedor = $this->mailvendedor;
        $nombreor = busca($puestoi,'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)');
        $mail->setFrom($correoi, utf8_decode($remitente));
        $mail->AddAddress($mailvendedor, utf8_decode($this->vendedor));
        if($_SESSION['emp'] == 1) $mail->addCC("supervision@jdshop.mx");

        $mail->ConfirmReadingTo = $correoi;
        $mail->Subject = utf8_decode($asunto);

          $table = "";
          $sqltp = 'SELECT * FROM ordenescd WHERE od_ordenc = "'.$this->id.'" ORDER BY od_id ASC';
          $result = setq($sqltp);
          if($result->num_rows > 0){
            $table.='<table widht="100%" class="tablecot" style="margin-top:5px;">
                    <thead><tr><td colspan="5" class="title-table" >Productos solicitados</td></tr>
                    <tr>
                    <th width="10%" class="title-table">Cantidad</th>
                    <th width="12%" class="title-table">Modelo</th>
                    <th width="50%" class="title-table">Descripcion</th>
                  </tr><thead>';
          $result = setq($sqltp);
          $tasaiva = busca(1,'configuracionesp','c_id','c_iva')/100;

            while($row = $result->fetch_array()){
              if($row['od_articulo']) $modelo = busca($row['od_articulo'],'articulos','a_id','a_modelo');
              else $modelo = "-";
              
              $table.='<tr>
                    <td>'.$row['od_cantidad'].'</th>
                    <td>'.$modelo.'</th>
                    <td>'.$row['od_nmbarticulo'].'</th>
                  </tr>';
            }
          }
          $table.='</table>';
          $body.=utf8_decode($table);

        $body.=utf8_decode('<hr>Mensaje generado automáticamente por JD CEO 2.0');

        $mail->MsgHTML($body);
        

          $mail->Host = $host;
          $mail->Port = $puerto;
          $mail->IsSMTP();
          if($seguridad != 0){
            $mail->SMTPSecure = $seguridad;
          }
          $mail->SMTPAuth = true;
          $mail->Username = $correoi;
          $mail->Password = $password;
          //$mail->SMTPDebug = 3;
          
          //echo $host.' '.$puerto.' '.$seguridad.' '.$correoi.' '.$password;
        //envío el mensaje, comprobando si se envió correctamente
          
      if(!$mail->Send()){
          unlink('docs/ordenc/'.$this->folio.'.pdf');
          //echo "No enviado";
      }else{
        //echo "Enviado";
      }
        
      //die();

    }
    /*function sendmail($ordenc){
      var_dump($this->select($ordenc));
      echo 'dato: '.$this->id.'<br>';
      echo 'dato: '.$this->empresa.'<br>';
      echo 'dato: '.$this->proveedor.'<br>';
      echo 'dato: '.$this->folio.'<br>';
      echo 'dato: '.$this->fechacom.'<br>';
      echo 'dato: '.$this->observaciones.'<br>';
      echo 'dato: '.$this->estatus.'<br>';
      echo 'dato: '.$this->fgen.'<br>';
      echo 'dato: '.$this->ugen.'<br>';
      echo 'dato: '.$this->fapli.'<br>';
      echo 'dato: '.$this->uapli.'<br>';
      echo 'dato: '.$this->fcan.'<br>';
      echo 'dato: '.$this->ucan.'<br>';
      echo 'dato: '.$this->subtotal.'<br>';
      echo 'dato: '.$this->iva.'<br>';
      echo 'dato: '.$this->descuento.'<br>';
      echo 'dato: '.$this->total.'<br>';
      echo 'dato: '.$this->solicita.'<br>';
      echo 'dato: '.$this->receptor.'<br>';
      echo 'dato: '.$this->destino.'<br>';
      echo 'dato: '.$this->vendedor.'<br>';
      echo 'dato: '.$this->mailvendedor.'<br>';
      echo 'dato: '.$this->tablero.'<br>';
      if($_POST['mailvendedor']) $mailvendedor = $_POST['mailvendedor'];
      else $mailvendedor = $this->mailvendedor;

      echo 'mail: '.$mailvendedor;
      die();
    }*/
  }

  class viewordenesc{
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->tipodoc = array("F"=>"Fiscal","C"=>"Contable");
    }
    function browse($fini,$ffin,$proveedor,$estatus,$documento,$page) {
      ?>
      <script language="JavaScript">
        function checksubmit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
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
          var btn = document.getElementById("filtrar");
          btn.click();
          $("#proveedor").focus();
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
      $this->model->aestatus = array("F" => "Recibido","A" => "Aplicado","P" => "En espera de autorización","N" => "En Captura","C" => "Cancelado");
      $this->model->nestatus = array("F" => "btn-primary","A" => "btn-success","P" => "bg-amber bg-darken-2","N"=>"bg-yellow bg-darken-3","C" => "btn-danger");
  
        $accion = 'insert';
        if (isset($this->model->id))$accion = 'update';
        //Sección: E1 Encabezado - Botones de acción
          //Sección: E2 Encabezado - Filtros
          $selt = '';
          $sela = '';
          $seln = '';
          $selp = '';
          $selc = '';
          if($estatus == "A") $sela = "selected";
          if($estatus == "N") $seln = "selected";
          if($estatus == "P") $selp = "selected";
          if($estatus == "C") $selc = "selected";
          else $selt = 'selected';


          $botones = '';
          $filtro = '';
          $botones .= '
          <a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setordenc.php" href="javascript:;">
            <button type="button" class="btn btn-primary btn-sm">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>          
          ';

          $filtro .= '
          <form class="form-inline" role="form" method="post" id="filtro">
            <div class="mb-5">
              <label for="tipom">Desde:</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta:</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Estatus:</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value="" '.$selt.' >Todos</option>
                <option value="N" '.$seln.' >En captura</option>
                <option value="A" '.$sela.' >Aplicados</option>
                <option value="P" '.$selp.' >Espera de Autorización</option>
                <option value="C" '.$selc.' >Cancelados</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones:</label> <br>
              <button type="button" onclick="mandar(0)" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
              </button>
              <!-- <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-warning"> -->
              <a href="?modulo=ordenesc&accion=index">
                <button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button>
              </a>
            </div>
          </form>
          ';
          toolbar($_GET['modulo'],$botones,$filtro);

          echo '
          <div class="card mt-3">
          <div class="card-body row">
          <div class="table table-responsive">
            <table class="table table-hover table-striped" id="myTable">
              <thead class="thead bg-blue bg-darken-3 ">
                <tr>
                  <th>Folio</th>
                  <th>Proveedor</th>
                  <th>Último Movimiento</th>
                  <th>Fecha de compra</th>
                  <th>Almacen</th>
                  <th>Importe</th>
                  <th>Observaciones</th>
                  <th>Estatus</th>
                  <th></th>
                </tr>
              </thead>';
              while($row = $this->model->resultrc->fetch_array()){
                if($row['o_estatus'] == "P" || $row['o_estatus'] == "A") $dir = "showoc"; else $dir = "show";
                if($row['o_estatus'] == "A") $lastm = "Aplicación: <b>".$row['o_uapli'].'</b><br>'.date('d-m-Y',strtotime($row['o_fapli']));
                elseif($row['o_estatus'] == "C") $lastm = "Cancelación: <b>".$row['o_ucan'].'</b><br>'.date('d-m-Y',strtotime($row['o_fcan']));
                else $lastm = "Registro: <b>".$row['o_ugen'].'</b><br>'.date('d-m-Y',strtotime($row['o_fgen']));
                
                if($row['o_tipooc'] == "A") $tipor = "Fiscal";
                else $tipor = "Contable";

                echo '<tr>
                  <th>'.$row['o_folio'].'</th>
                  <td>'.busca($row['o_proveedor'],'proveedores','p_id','p_alias').'</td>
                  <td>'.$lastm.'</td>
                  <td>'.fecha_formato($row['o_fechacom'],false,false).'</td>
                  <td>'.busca($row['o_almacen'],'pr_almacenes','pa_id','pa_nmb').'</td>
                  <td class="number-align">$'.number_format($row['o_total'],2).'</td>
                  <td>'.$row['o_observaciones'].'</td>
                  <td class="'.$this->model->nestatus[$row['o_estatus']].'">'.$this->model->aestatus[$row['o_estatus']].'</td>
                  <td>
                    <a href="?modulo=ordenesc&accion='.$dir.'&id='.$row['o_id'].'">
                      <button type="button" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> Detalles</button>
                    </a>';
                    if($row['o_estatus'] == "A"){
                      echo '<a target="_BLANK" href="formats/pdfordenc.php?id='.$row['o_id'].'">
                        <button type="button" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Imprimir</button>
                      </a>';
                      echo '<a data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?ordenc='.$row['o_id'].'" href="javascript:;">
                        <button type="button" class="btn btn-sm btn-primary">
                          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-cart-arrow-down"></i> Recibir
                        </button>
                      </a>';
                    }elseif($row['o_estatus'] == "F"){
                      echo '<a target="_BLANK" href="formats/pdfordenc.php?id='.$row['o_id'].'">
                        <button type="button" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Imprimir</button>
                      </a>';
                    }
                  echo '</td>
                </tr>';
              }
            echo '</table>
          </div>
          <script>
            $("#myTable").DataTable( {
                paging: true,
                scrollY: 400,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                },
            });
          </script>
          
          ';
          //filtro

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
                  $sqlpg = 'SELECT COUNT(*) FROM ordenesc WHERE o_empresa = "'.$_SESSION['emp'].'"
                            AND DATE(o_fgen) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
                  if($proveedor) $sqlpg.=' AND o_proveedor IN (SELECT p_id FROM proveedores WHERE p_nmb LIKE "%'.$prov.'%" OR p_alias LIKE "%'.$prov.'%")';
                  if($documento) $sqlpg.=' AND o_folio LIKE "%'.$documento.'%" ';
                  if($estatus) $sqlpg.=' AND o_estatus = "'.$estatus.'"';
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
       
        echo'</div>
      </div>';
    }
    function show(){
      ?>
      <script>
        $(document).ready(function() {
          $('#producto').on('keyup', function() {
            if (event.keyCode == 38 || event.keyCode == 40){
              //console.log('chi');
            }else{
            var key = $(this).val();
            //var empresa = $('#empresa').val();
            var dataString = 'producto='+key;
            $.ajax({
              type: "POST",
              url: "query/suggestmp.php",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  var valor = $(this).attr('value');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  $.ajax({
                    type: "POST",
                    url: "query/checkmp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var largo = document.getElementById('largodiv');
                      var ancho = document.getElementById('anchodiv');
                      var alto = document.getElementById('altodiv');
                      var largoin = document.getElementById('largo');
                      var anchoin = document.getElementById('ancho');
                      var altoin = document.getElementById('alto');
                      data = JSON.parse(dataRTN);
                      if(data['tipo'] == "A"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = true;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.removeAttribute('min');
                      } else if(data['tipo'] == "V"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = false;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.setAttribute('min', '0.01');
                      } else if(data['tipo'] == "P"){
                        largo.hidden = true;
                        ancho.hidden = true;
                        alto.hidden = true;
                        largoin.removeAttribute('min');
                        anchoin.removeAttribute('min');
                        altoin.removeAttribute('min');
                      }
                    }
                  });
                  $.ajax({
                    type: "POST",
                    url: "query/setpreciomp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var costo = document.getElementById('costo');
                      costo.value=dataRTN;
                    }
                  });
                  $("#cantidad").focus();
                  return false;
                });
              }
            });
          }
          });
        });
      </script>
      <?php
      if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
      if ($_GET['alerta'] == 1) echo alert("El articulo no Existe", true);

      echo '<script>
        function delprod(idprod){
          var conf = confirm("¿Deseas borrar el producto de la orden de compra?");
          if(conf == true){
            window.location.href="?modulo=ordenesc&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
          }
        }
        function aplicamov(){
          var conf = confirm("Deseas aplicar esta orden de compra\n¿Deseas continuar?");
          if(conf == true){
            window.location.href="?modulo=ordenesc&accion=aplicar&id='.$this->model->id.'";
          }
        }
        function changeln(){
          var ln = document.getElementById("linean");
          ln.removeAttribute("style");
          $("#toolln").prop("hidden","true");
        }
      </script>';
        $botones = '';
            if($this->model->tablero){
              $botones .= '<a href="?modulo=tableros&accion=show&id='.$this->model->tablero.'" acceskey="">
                <button type="button" class="btn btn-warning btn-sm"><i class="fa fa-arrow-left"></i> Tablero </button>
              </a>
              <a href="?modulo=ordenesc&accion=index" accesskey="">
                <button type="button" class="btn bg-orange bg-darken-1 text-white btn-sm"><i class="fas fa-reply"></i> Listado Ordenes </button>
              </a>';
            }else{
              $botones .= '<a href="?modulo=ordenesc&accion=index" accesskey="">
                <button type="button" class="btn btn-warning btn-sm"><i class="fa fa-arrow-left"></i> Atrás </button>
              </a>';
            }
            if($this->model->estatus == "A")
              $botones .= '<a target="_BLANK" href="formats/pdfordenc.php?id='.$this->model->id.'">
                <button type="button" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Imprimir</button>
              </a>
              <a data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-info btn-sm">
                  <span class="glyphicon glyphicon-cog"></span><i class="fas fa-envelope-open"></i> Reenviar correo
                </button>
              </a>
              <a data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?ordenc='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-primary btn-sm">
                  <span class="glyphicon glyphicon-cog"></span><i class="fas fa-cart-arrow-down"></i> Recibir
                </button>
              </a>';
            elseif($this->model->estatus == "N" || $this->model->estatus == "P"){
              $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setordenc.php?id='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-primary btn-sm">
                  <span class="glyphicon glyphicon-cog"></span><i class="fa fa-pen"></i> Editar
                </button>
              </a>';
              $botones .= '
              <script>
              function autorizar(){
                var a = confirm("¿Estás seguro de aplicar la orden de compra: '.$this->model->folio.'? Se creará una cuenta por pagar.");
                if(a){
                  window.location.href="?modulo=ordenesc&accion=aplicar&id='.$this->model->id.'"
                }
              }
              </script>
              ';
              if(busca($this->model->id,'ordenescd','od_ordenc','COUNT(*)') > 0){
              if($this->model->estatus == "N"){
                $direccion = 'data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;"';
              }elseif($this->model->estatus == "P"){
                $direccion = 'onclick="autorizar();"';
              }

              $botones .= '<a '.$direccion.'>
                <button type="button" class="btn btn-success btn-sm">
                  <span class="glyphicon glyphicon-cog"></span><i class="fa fa-check"></i> Aplicar
                </button>
              </a>';
              }
              if(busca($this->model->tablero,'remisiones','r_estatus = "A" AND r_tablero','COUNT(*)') > 0){
                $botones .= '<a href="popup/importarprodsordenc.php?ordenc='.$this->model->id.'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                  <button type="button" class="btn btn-secondary btn-sm"><i class="fas fa-download"></i>Importar producto</button>
                </a>';
              }
              if($this->model->estatus == "P"){
                $botones .= '
                <script>
                function denegar(){
                var a = confirm("¿Seguro que quieres denegar la orden de compra: '.$this->model->folio.'?");
                  if(a){
                    window.location.href="?modulo=ordenesc&accion=updatee&id='.$this->model->id.'";
                  }
                }
                </script>';
                $botones .= '<button type="button" onclick="denegar();" class="btn btn-danger text-white btn-sm"><i class="fa fa-times"></i> Denegar</button>';
              }
            }

          if($this->model->estatus == "A"){
            //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
          }

          if($this->model->estatus == "N") $leyenda = 'Añadir productos a la Requisición'; //$leyenda = 'Añadir productos a la Orden de compra ';
          else  $leyenda = 'Lista de productos en la Orden de compra ';

          if($this->model->estatus == "N"){
            $hidden = "hidden";
            $col1 = "col-md-4 col-lg-4";
            $col2 = "col-md-4 col-lg-3";
            $col3 = "col-md-12 col-lg-2 text-xs-center";
            $col4 = '';
          }else{
            $hidden = "";
            $col1 = "col-md-2 col-lg-3";
            $col2 = "col-md-2 col-lg-1";
            $col3 = "col-md-2 text-xs-center";
            $col4 = 'hidden';
          }

      $readon = "";
      $exisantm = "";
      $miva = "";

      toolbar($_GET['modulo'],$botones);
      if($this->model->estatus == "N" || $this->model->estatus == "P"){
        echo '
        <div class="card mt-3">
        <div class="card-body">
          <form method="post" class="row" autocomplete="off" action="?modulo=ordenesc&accion=insertdetalle&id='.$this->model->id.'" name="ordencompra" onsubmit="checksubmit();">
            <input type="hidden" id="lineann" name="lineann" />
            <input type="hidden" id="importe" name="importe" />
            <div class="row">
            <div class="mt-1 col-6 col-md-6 alert alert-primary" data-toggle="tooltip" data-placement="top" title="'.$this->model->observaciones.'">
              '.$leyenda.'
                - 
              '.$this->model->folio.'
            </div>';
            if($this->model->estatus == "N"){
              echo '
              <div class="mt-1 col-6 col-md-6  alert alert-success" >
                Almacen: "'.busca($this->model->almacen,'pr_almacenes','pa_id','pa_nmb').'"
              </div>
              ';
            }
            echo '
            </div>
            <div class="'.$col1.'">
              <div class="mb-5">
                <label for="agregar">Producto</label><br>
                <div class="input-group">
                  <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus >
                </div>
                <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: overlay;"></div>
              </div>
            </div>
            <div class="'.$col2.'">
              <div class="mb-5">
                <label for="cant">Cantidad</label><br>
                <input type="number" min="0" value="1" onfocus="this.select()" max="9999999" step="0.01" name="cantidad" id="cantidad" placeholder="Cantidad" class="form-control" required="required" />
              </div>
            </div>
            <div class="mb-5 col-12 col-md-2 ms-5" id="largodiv" hidden>  
              <label>Largo:</label>
              <input type="number" min="0.01" step="0.01" id="largo" class="form-control" data-toggle="tooltip" data-placement="top" name="largo" value="0" onfocus="this.select();">
            </div>
            <div class="mb-5 col-12 col-md-2" id="anchodiv" hidden>  
              <label>Ancho:</label>
              <input type="number" min="0.01" step="0.01" id="ancho" class="form-control" data-toggle="tooltip" data-placement="top" name="ancho" value="0" onfocus="this.select();">
            </div>
            <div class="mb-5 col-12 col-md-2" id="altodiv" hidden>  
              <label>Alto:</label>
              <input type="number" min="0.01" step="0.01" id="alto" class="form-control" data-toggle="tooltip" data-placement="top" name="alto" value="0" onfocus="this.select();">
            </div>
            <div class="'.$col3.'">
              <div class="mb-5">
                <label></label><br>
                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Agregar producto</button>
              </div>
            </div>
          </form>
        </div>
        </div>';
      }else{
        echo '<div class="mt-1 col-8 col-md-8 alert alert-primary">
          '.$leyenda.' - '.$this->model->folio.'
        </div>';
        $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
      }

      echo '
      <div class="card">
      <div class="card-body">
      <div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
          <thead class="thead bg-primary text-white bg-darken-3 pt-1 pb-1">
            <tr>
              <th width="30%">Artículo</th>
              <th width="20%">Piezas Solicitadas</th>
              <th '.$hidden.'>Precio unitario</th>
              <th '.$hidden.'>IVA</th>
              <th '.$hidden.'>Importe total</th>
              <th>Acciones</th>
            </tr>
          </thead>';
          $subtotal = 0;
          $totiva = 0;
          $poriva = busca(1,'configuracionesp','c_id','c_iva')/100;
          if($this->model->estatus == "A") $readonly = "disabled";
          while($row = $this->model->resultr->fetch_array()){
            $preciodb = $row['od_precio'];
            if($row['od_iva'] == "1"){
              $chiva = 'checked';
              //$precio = $row['od_precio']*(1+$poriva);
              /////////$precio = $row['od_precio'] / (1+$poriva);
              $precio = $row['od_precio'];
              $importe = $precio*$row['od_cantidad'];
              $iva = $importe*($poriva);
            }else{
              $chiva = "";
              $precio = $row['od_precio'];
              $importe = $precio*$row['od_cantidad'];
            }
            if($row['od_nparte']) $modelo = $row['od_nparte']; else $modelo = 'Sin asignar';
            echo '<tr>
              <form method="post" action="?modulo=ordenesc&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['od_id'].'">
                <td data-toggle="tooltip" title="Modelo/No. de parte: '.$modelo.'">'.$row['od_nmbarticulo'].'</td>';
                echo '<td>
                  <input type="number" value="'.number_format($row['od_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>';
                if($this->model->estatus != "N")
                echo '
                <td class="number-align" >
                  <input type="hidden" name="preciodb" value="'.$preciodb.'"/>
                  <input type="hidden" name="precioshow" value="'.$importe.'" hidden>
                  <input type="number" value="'.number_format($importe,2,'.','').'" min="0" max="9999999" step="0.01" name="precio" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>
                <td ><input type="checkbox" name="iva" '.$chiva.' '.$readon.' onchange="submit();" /></td>
                <td >
                  <input type="number" value="'.number_format($importe,2,'.','').'" min="0" max="9999999" step="0.01" name="importe" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" readonly="readonly"  />
                </td>';
                if($this->model->estatus == "N" || $this->model->estatus == "P")
                  echo '<td>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-redo"></i> Actualizar</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="delprod('.$row['od_id'].')"><i class="fa fa-trash"></i> Borrar</button>';
                  echo '</td>
              </form>
            </tr>';
          }
          echo '<tfoot>';
            if($this->model->iva > 0){
              if($this->model->estatus == "P" || $this->model->estatus == "A"){
                echo '<tr class="p-1 bg-grey bg-lighten-2">
                  <td colspan="2">&nbsp;</td>
                  <td colspan="2" class="number-align h4">Subtotal</td>
                  <td class="number-align h4">$'.number_format($this->model->subtotal,2).'</td>
                </tr>
                <tr class="p-1 bg-grey bg-lighten-3">
                  <td colspan="2" >&nbsp;</td>
                  <td colspan="2"  class="number-align h4">IVA</td>
                  <td  class="number-align h4">$'.number_format($this->model->iva,2).'</td>
                </tr>';
              }
            }
            if($this->model->estatus != "N")
            echo '<tr class="p-1 bg-grey bg-lighten-2">
              <td colspan="2">&nbsp;</td>
              <td colspan="2"  class="number-align h4">Total</td>
              <td  class="number-align h4">$'.number_format($this->model->total,2).'</td>
            </tr>';
          echo '</tfoot>';
        echo '</table>
      </div>
      </div>
      </div>';
      ?>
      <script>  
        let listGroup = document.getElementById('suggestions');
        // Asignar evento al campo de texto
        document.querySelector('#producto').addEventListener('keydown', e => {
          if(!listGroup) {
            return; // No existe la lista
          }    
          // Obtener todos los elementos
          let items = listGroup.querySelectorAll('a');
          var a = document.getElementById("suggestions");
          // Saber si alguno está activo
          let actual = Array.from(items).findIndex(item => item.classList.contains('active'));
          //let actual = 0;
          // Analizar tecla pulsada
          if(e.keyCode == 13) {
            // Tecla Enter, evitar que se procese el formulario
            e.preventDefault();
            // ¿Hay un elemento activo?
            if(items[actual]) {
              // Hacer clic
              items[actual].click();
            }
          } 
          if(e.keyCode == 38 || e.keyCode == 40) {
            // Flecha arriba (restar) o abajo (sumar)
            if(items[actual]) {
              // Solo si hay un elemento activo, eliminar clase
              items[actual].classList.remove('active');
              //items[actual].className += " active";
            }
            // Calcular posición del siguiente
            if((e.keyCode == 38)){
              a.scrollTop -= ((items[actual].clientHeight - 1));
              actual += -1;
            }else{
              if(actual > 1) a.scrollTop += ((items[actual-2].offsetHeight + 1));            
              actual += 1;
            }
            //actual += (e.keyCode == 38) ? -1 : 1;
            // Asegurar que está dentro de los límites
            if(actual < 0) {
              actual = 0;
            } else if(actual >= items.length) {
              actual = items.length - 1;
            } 
            // Asignar clase activa
            items[actual].classList.add('active');
            items[actual].setAttribute('autofocus','');
            //items[actual].className += " active";
          }
        });
        // En la función donde generas la lista debes activar evento clic para cada elemento
        // Para este ejemplo se hace manual
        listGroup.querySelectorAll('a').forEach(a => {
          a.addEventListener('click', e => {
            // Asignar valor al campo
            document.querySelector('#producto').value = e.currentTarget.textContent;
            // Aquí deberías cerrar la lista y/o eliminar el contenido
          });
        });
      </script>
      <?php
    }
    function showoc(){
      ?>
      <script>
        $(document).ready(function() {
          $('#producto').on('keyup', function() {
            if (event.keyCode == 38 || event.keyCode == 40){
              //console.log('chi');
            }else{
            var key = $(this).val();
            //var empresa = $('#empresa').val();
            var dataString = 'producto='+key;
            $.ajax({
              type: "POST",
              url: "query/suggestmp.php",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  var valor = $(this).attr('value');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  $.ajax({
                    type: "POST",
                    url: "query/checkmp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var largo = document.getElementById('largodiv');
                      var ancho = document.getElementById('anchodiv');
                      var alto = document.getElementById('altodiv');
                      var largoin = document.getElementById('largo');
                      var anchoin = document.getElementById('ancho');
                      var altoin = document.getElementById('alto');
                      data = JSON.parse(dataRTN);
                      if(data['tipo'] == "A"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = true;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.removeAttribute('min');
                      } else if(data['tipo'] == "V"){
                        largo.hidden = false;
                        ancho.hidden = false;
                        alto.hidden = false;
                        largoin.setAttribute('min', '0.01');
                        anchoin.setAttribute('min', '0.01');
                        altoin.setAttribute('min', '0.01');
                      } else if(data['tipo'] == "P"){
                        largo.hidden = true;
                        ancho.hidden = true;
                        alto.hidden = true;
                        largoin.removeAttribute('min');
                        anchoin.removeAttribute('min');
                        altoin.removeAttribute('min');
                      }
                    }
                  });
                  $.ajax({
                    type: "POST",
                    url: "query/setpreciomp.php",
                    data: {'mp': valor},
                    success: function(dataRTN) {
                      var costo = document.getElementById('costo');
                      costo.value=dataRTN;
                    }
                  });
                  $("#cantidad").focus();
                  return false;
                });
              }
            });
          }
          });
        });
      </script>
      <?php
      if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
      if ($_GET['alerta'] == 1) echo alert("El articulo no Existe", true);
      echo '<script>
        function delprod(idprod){
          var conf = confirm("¿Deseas borrar el producto de la orden de compra?");
          if(conf == true){
            window.location.href="?modulo=ordenesc&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
          }
        }
        function aplicamov(){
          var conf = confirm("Deseas aplicar esta orden de compra\n¿Deseas continuar?");
          if(conf == true){
            window.location.href="?modulo=ordenesc&accion=aplicar&id='.$this->model->id.'";
          }
        }
        function changeln(){
          var ln = document.getElementById("linean");
          ln.removeAttribute("style");
          $("#toolln").prop("hidden","true");
        }
      </script>';
        $botones = '';
        if($this->model->tablero){
          $botones.= '<a href="?modulo=tableros&accion=show&id='.$this->model->tablero.'" acceskey="">
            <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Tablero </button>
          </a>';
          $atras = '<a href="?modulo=ordenesc&accion=index" accesskey="">
            <button type="button" class="btn bg-orange bg-darken-1 text-white"><i class="fas fa-reply"></i> Listado Ordenes </button>
          </a>';
        }else{
          $atras = '<a href="?modulo=ordenesc&accion=index" accesskey="">
            <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atrás </button>
          </a>';
        }
        if($this->model->estatus == "P" && busca($this->model->id,'ordenescd','od_ordenc','COUNT(*)') > 0)
          $botones.= '
          <a target="_blank" href="formats/pdfrequisicion.php?id='.$this->model->id.'" class="btn btn-green text-white" style="background: teal;"><i class="fas fa-print" style="color: #fff"></i> Imprimir Guía de Surtido</a>
          ';
          
        if($this->model->estatus == "A")
          $botones.= '<a target="_BLANK" href="formats/pdfordenc.php?id='.$this->model->id.'">
            <button type="button" class="btn btn-secondary"><i class="fas fa-print"></i> Imprimir</button>
          </a>
          <a data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <span class="glyphicon glyphicon-cog"></span><i class="fas fa-envelope-open"></i> Reenviar correo
            </button>
          </a>
          <a data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?ordenc='.$this->model->id.'" href="javascript:;">
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fas fa-cart-arrow-down"></i> Recibir
            </button>
          </a>';
        elseif($this->model->estatus == "N" || $this->model->estatus == "P"){
          $botones.='<a data-fancybox data-type="ajax" data-src="popup/setordenc.php?id='.$this->model->id.'" href="javascript:;">
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-pen"></i> Editar
            </button>
          </a>';
          $botones.= '<script>
            function autorizar(){
              var a = confirm("¿Estás seguro de aplicar la orden de compra: '.$this->model->folio.'? Se creará una cuenta por pagar.");
              if(a){
                window.location.href="?modulo=ordenesc&accion=aplicar&id='.$this->model->id.'"
              }
            }
          </script>';
          if(busca($this->model->id,'ordenescd','od_ordenc','COUNT(*)') > 0){
            if($this->model->estatus == "N"){
              $direccion = 'data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;"';
            }elseif($this->model->estatus == "P"){
              $direccion = 'onclick="autorizar();"';
            }
            $direccion = 'data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;"';
            $botones.= '<a '.$direccion.'>
              <button type="button" class="btn btn-success">
                <span class="glyphicon glyphicon-cog"></span><i class="fa fa-check"></i> Aplicar
              </button>
            </a>';
          }
          if(busca($this->model->tablero,'remisiones','r_estatus = "A" AND r_tablero','COUNT(*)') > 0){
            $botones.= '<a href="popup/importarprodsordenc.php?ordenc='.$this->model->id.'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
              <button type="button" class="btn btn-secondary"><i class="fas fa-download"></i>Importar producto</button>
            </a>';
          }
          if($this->model->estatus == "P"){
            $botones.= '<script>
              function denegar(){
                var a = confirm("¿Seguro que quieres denegar la orden de compra: '.$this->model->folio.'?");
                if(a){
                  window.location.href="?modulo=ordenesc&accion=updatee&id='.$this->model->id.'";
                }
              }
            </script>';
            $botones.= '<button type="button" onclick="denegar();" class="btn btn-danger text-white"><i class="fa fa-times"></i> Denegar</button>';
          }
        }

        toolbar('ORDEN DE COMPRA', $atras, '','',$botones);
        
        if($this->model->estatus == "A"){
          //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
        }
        if($this->model->estatus == "N") $leyenda = 'Añadir productos a la Requisición'; //$leyenda = 'Añadir productos a la Orden de compra ';
        else  $leyenda = 'Lista de productos en la Orden de compra ';
        if($this->model->estatus == "N"){
          $hidden = "hidden";
          $col1 = "col-md-6 col-lg-5";
          $col2 = "col-md-6 col-lg-3";
          $col3 = "col-md-12 col-lg-2 text-xs-center";
          //$col = ""; 
        }else{
          $hidden = "";
          $col1 = "col-md-4 col-lg-3";
          $col2 = "col-md-2 col-lg-1";
          $col3 = "col-md-2 col-lg-2 text-xs-center";
        }

      $readon = "";
      $exisantm = "";
      $miva = "";
      if($this->model->diva == "1") $hiddeniva = "";
      else $hiddeniva = "hidden";
      if($this->model->estatus == "N" || $this->model->estatus == "P"){
        echo '
        <div class="card">
          <div class="card-body">
          <form method="post" class="row" autocomplete="off" action="?modulo=ordenesc&accion=insertdetalle&id='.$this->model->id.'" name="ordencompra" onsubmit="checksubmit();">
            <input type="hidden" id="lineann" name="lineann" />
            <input type="hidden" id="importe" name="importe" />
            <div class="row">
            <div class="mt-1 col-6 col-md-6 alert alert-primary" data-toggle="tooltip" data-placement="top" title="'.$this->model->observaciones.'">
              '.$leyenda.'
                - 
              '.$this->model->folio.'
            </div>';
            if($this->model->estatus == "P"){
              echo '
              <div class="mt-1 col-6 col-md-6 alert alert-success">
                Almacen: "'.busca($this->model->almacen,'pr_almacenes','pa_id','pa_nmb').'"
              </div>
              ';
            }
            echo '
            </div>
            <div class="'.$col1.'">
              <div class="mb-5">
                <label for="agregar">Producto</label><br>
                <div class="input-group">
                  <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus >
                </div>
                <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: overlay;"></div>
              </div>
            </div>
            <div class="'.$col2.'">
              <div class="mb-5">
                <label for="cant">Cantidad</label><br>
                <input type="number" min="0" value="1" onfocus="this.select()" max="9999999" step="0.01" name="cantidad" id="cantidad" placeholder="Cantidad" class="form-control" required="required" />
              </div>
            </div>
            <div class="mb-5 col-12 col-md-2 ms-5" id="largodiv" hidden>  
              <label>Largo:</label>
              <input type="number" min="0.01" step="0.01" id="largo" class="form-control" data-toggle="tooltip" data-placement="top" name="largo" value="0" onfocus="this.select();">
            </div>
            <div class="mb-5 col-12 col-md-2" id="anchodiv" hidden>  
              <label>Ancho:</label>
              <input type="number" min="0.01" step="0.01" id="ancho" class="form-control" data-toggle="tooltip" data-placement="top" name="ancho" value="0" onfocus="this.select();">
            </div>
            <div class="mb-5 col-12 col-md-2" id="altodiv" hidden>  
              <label>Alto:</label>
              <input type="number" min="0.01" step="0.01" id="alto" class="form-control" data-toggle="tooltip" data-placement="top" name="alto" value="0" onfocus="this.select();">
            </div>
            <div class="col-md-2 col-lg-2" '.$hidden.'>
              <div class="mb-5">
                <label for="cant">Costo</label>
                <span  title="Costo de compra antes de aplicar IVA"><i class="fas fa-question-circle"></i></i></span><br>
                <input type="number" min="0" max="9999999" step="0.01" id="costo" name="importe" placeholder="Importe del concepto" class="form-control" required="required" />
              </div>
            </div>
            <div class="col-md-1 col-lg-1" '.$hidden.' hidden>
              <div class="mb-5">
                <center>
                  <label for="cant">IVA</label><br>
                  <input type="checkbox" name="iva" '.$miva.' />
                <center>
              </div>
            </div>
            <div class="'.$col3.'">
              <div class="mb-5">
                <label></label><br>
                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Agregar</button>
              </div>
            </div>
          </form>
        </div>
        </div>';
      }else{
        echo '<div class="row">
          <div class="mt-1 col-6 col-md-6 alert alert-primary">
            '.$leyenda.' - '.$this->model->folio.'
          </div>';
        
          echo '
            <div class="mt-1 col-6 col-md-6 alert alert-success" >
              Almacen: "'.busca($this->model->almacen,'pr_almacenes','pa_id','pa_nmb').'"
            </div>
          </div>
          ';
        
        $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
      }
      echo '
      <div class="card">
      <div class="card-body">
      <div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
          <thead class="thead bg-primary text-white">
            <tr>
              <th width="30%">Artículo</th>
              <th>Piezas Solicitadas</th>
              <th '.$hidden.'>Precio unitario</th>
              <th '.$hidden.' '.$hiddeniva.'>+IVA</th>
              <th '.$hidden.'>Importe total</th>
              <th>Acciones</th>
            </tr>
          </thead>';
          $subtotal = 0;
          $totiva = 0;
          $iva = 0;
          $poriva = busca(1,'configuracionesp','c_id','c_iva')/100;
          if($this->model->estatus == "A") $readonly = "disabled";
          while($row = $this->model->resultr->fetch_array()){
            $preciodb = $row['od_precio'];
            if($row['od_iva'] == "1"){
              $chiva = 'checked';
              $precio = $row['od_precio'];
              $importe = $precio*$row['od_cantidad'];
              $iva += $importe*($poriva);
            }else{
              $chiva = "";
              $precio = $row['od_precio'];
              $importe = $precio*$row['od_cantidad'];
            }
            if($row['od_nparte']) $modelo = $row['od_nparte']; else $modelo = 'Sin asignar';
            echo'<script>
              function updatecant'.$row['od_id'].'(){
                var cantidad = document.getElementById("occantidad'.$row['od_id'].'").value;
                document.getElementById("newcant'.$row['od_id'].'").value = cantidad;
                document.updatecantidad'.$row['od_id'].'.submit();
              }
            </script>';
            echo '<form name="updatecantidad'.$row['od_id'].'" method="post" action="?modulo=ordenesc&accion=cambiarcantidad&id='.$row['od_id'].'&ordenc='.$_GET['id'].'">
              <input type="hidden" name="cantidad" id="newcant'.$row['od_id'].'" />
            </form>';
            echo '<tr>
                <td data-toggle="tooltip" title="Modelo/No. de parte: '.$modelo.'">'.$row['od_nmbarticulo'].'</td>';
                echo '<td>
                  <input type="number" id="occantidad'.$row['od_id'].'" onchange="updatecant'.$row['od_id'].'()" value="'.number_format($row['od_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>';
                echo'<form method="post" action="?modulo=ordenesc&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['od_id'].'">';
                if($this->model->estatus != "N")
                echo '
                <input type="number"  value="'.number_format($row['od_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' hidden />
                <td class="number-align" >
                  <input type="hidden" name="preciodb" value="'.$preciodb.'"/>
                  <input type="hidden" name="precioshow" value="'.$importe.'" hidden>
                  <input type="number" value="'.number_format($precio,2,'.','').'" min="0" max="9999999" step="0.01" name="precio" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>
                <td '.$hiddeniva.'><input type="checkbox" name="iva" '.$chiva.' '.$readon.' onchange="submit();" /></td>
                <td >
                  <input type="number" value="'.number_format($importe,2,'.','').'" min="0" max="9999999" step="0.01" name="importe" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" readonly="readonly"  />
                </td>';
                if($this->model->estatus == "N" || $this->model->estatus == "P")
                  echo '<td>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-redo"></i> Actualizar</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="delprod('.$row['od_id'].')"><i class="fa fa-trash"></i> Borrar</button>';
                  echo '</td>
              </form>
            </tr>';
          }
          echo '<tfoot>';
            if($this->model->diva == "1"){
              echo '<tr class="p-1 bg-grey bg-lighten-2">
                <td colspan="2">&nbsp;</td>
                <td colspan="2" class="number-align  h4">Subtotal</td>
                <td class="number-align  h4">$'.number_format($this->model->subtotal,2).'</td>
              </tr>
              <tr class="p-1 bg-grey bg-lighten-3">
                <td colspan="2" >&nbsp;</td>
                <td colspan="2"  class="number-align  h4">IVA</td>
                <td  class="number-align  h4">$'.number_format($this->model->iva,2).'</td>
              </tr>';
            }
            if($this->model->diva == "1") $colspan = "2";
            else $colspan = "1";
            if($this->model->estatus != "N")
            echo '<tr class="p-1 bg-grey bg-lighten-2">
              <td colspan="'.$colspan.'">&nbsp;</td>
              <td colspan="2" class="number-align  h4">Total</td>
              <td  class="number-align  h4">$'.number_format($this->model->total,2).'</td>
            </tr>';
          echo '</tfoot>';
        echo '</table>
      </div>
      </div>
      </div>';
      ?>
      <script>  
        let listGroup = document.getElementById('suggestions');
        // Asignar evento al campo de texto
        document.querySelector('#producto').addEventListener('keydown', e => {
          if(!listGroup) {
            return; // No existe la lista
          }    
          // Obtener todos los elementos
          let items = listGroup.querySelectorAll('a');
          var a = document.getElementById("suggestions");
          // Saber si alguno está activo
          let actual = Array.from(items).findIndex(item => item.classList.contains('active'));
          //let actual = 0;
          // Analizar tecla pulsada
          if(e.keyCode == 13) {
            // Tecla Enter, evitar que se procese el formulario
            e.preventDefault();
            // ¿Hay un elemento activo?
            if(items[actual]) {
              // Hacer clic
              items[actual].click();
            }
          } 
          if(e.keyCode == 38 || e.keyCode == 40) {
            // Flecha arriba (restar) o abajo (sumar)
            if(items[actual]) {
              // Solo si hay un elemento activo, eliminar clase
              items[actual].classList.remove('active');
              //items[actual].className += " active";
            }
            // Calcular posición del siguiente
            if((e.keyCode == 38)){
              a.scrollTop -= ((items[actual].clientHeight - 1));
              actual += -1;
            }else{
              if(actual > 1) a.scrollTop += ((items[actual-2].offsetHeight + 1));            
              actual += 1;
            }
            //actual += (e.keyCode == 38) ? -1 : 1;
            // Asegurar que está dentro de los límites
            if(actual < 0) {
              actual = 0;
            } else if(actual >= items.length) {
              actual = items.length - 1;
            } 
            // Asignar clase activa
            items[actual].classList.add('active');
            items[actual].setAttribute('autofocus','');
            //items[actual].className += " active";
          }
        });
        // En la función donde generas la lista debes activar evento clic para cada elemento
        // Para este ejemplo se hace manual
        listGroup.querySelectorAll('a').forEach(a => {
          a.addEventListener('click', e => {
            // Asignar valor al campo
            document.querySelector('#producto').value = e.currentTarget.textContent;
            // Aquí deberías cerrar la lista y/o eliminar el contenido
          });
        });
      </script>
      <?php
    }
  }
?>