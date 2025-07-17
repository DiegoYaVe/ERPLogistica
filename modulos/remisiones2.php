<?php
  //ini_set('display_errors', 1);
  class remisiones {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelremisiones(isset($obj));
    }
    function index() {
      if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-60 days'));
      if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d');
      if (!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;
      if (!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if (!isset($_REQUEST['documento'])) $_REQUEST['documento'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
      if($grupo == "USER") $_REQUEST['cliente'] = busca(busca($_SESSION['uid'],'usuarios','u_id','u_almacen'),'crm_clientes','c_id','c_almacen');

      $this->model->result($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['cliente'],$_REQUEST['estatus'],$_REQUEST['documento'],$_REQUEST['page']);
      $this->view = new viewremisiones($this->model);
      $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['cliente'],$_REQUEST['estatus'],$_REQUEST['documento'],$_REQUEST['page']);
    } 
    function edit(){
      if(!isset($_REQUEST['id'])) $_REQUEST['id'] = NULL;

      if(!isset($_REQUEST['cliente'])){
        $_REQUEST['cliente'] = NULL;
        $idcliente = NULL;
      }
      else $idcliente = getIDcliente($_REQUEST['cliente']);

      $this->model->select($idcliente);
      $this->view = new viewremisiones($this->model);
      $this->view->edit($idcliente);
    }
    function show(){
      $this->model->select($_GET['id']);
      $this->model->resultd($_GET['id']);
      $this->view = new viewremisiones($this->model);
      $this->view->show($_GET['id']);
    }
    function insert(){
      $licenciarem = busca($_SESSION['emp'],'empresa_licencia','el_empresa','el_ventas');
      $sqltotrem = 'SELECT COUNT(*) FROM remisiones WHERE r_empresa = "'.$_SESSION['emp'].'"';
      $resultrem = setq($sqltotrem);
      $rowrem = $resultrem->fetch_array();

      if($rowrem['COUNT(*)'] >= $licenciarem){
        echo '
        <script>
            alert("La cantidad de ventas en tu licencia ha sido superado. No puedes agregar nuevas ventas. Contacta a tu agente de JD CEO.");
            window.location.href = "?modulo=remisiones&accion=index";
        </script>
        ';
      }else{
        if(isset($_POST['diva'])) $diva = "1"; else $diva = "0";
        $acronimo = busca($_SESSION['emp'],'empresas','e_id','e_siglas');
        $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-RM';
        $folioint = getmax('r_folio','remisiones','r_empresa = "'.$_SESSION['emp'].'"');
        if($folioint == "1"){
          $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
        }
        if($_POST['tablero'] == "XXX"){
          include_once('tableros.php');
          $table = new modeltableros();
          $folio = getmax('ct_folio','crm_tableros');
          if($folio == $acronimo.'1') $folio = $acronimo.str_pad(1,6,"0",STR_PAD_LEFT);
          $fini = explode(" ",date('Y-m-d H:i:s'));

          $table->setdata(NULL,$_POST['nmbac'],$folio,$_POST['cliente'],$_POST['responsable'],$fini[0],$fini[1],1,$fini[0],$_POST['niveltab'],"A");
          $table->insert();
          $_POST['tablero'] = $table->idt;
        }
        $this->model->setdata(NULL,$_POST['tablero'],$folioint,$_POST['cliente'],$_POST['almacen'],$_POST['descuento'],$_POST['nmbac'],$_POST['correo'],$_POST['responsable'],$_POST['ffin'],$diva);
        $this->model->insert();
      }
      redirect("?modulo=remisiones&accion=show&id=".$this->model->id);
    }
    function updateremision(){
      if(isset($_POST['diva'])) $diva = "1"; else $diva = "0";
      if($diva == "0"){
        $sql = 'UPDATE remisionesd SET rd_iva = "0" WHERE rd_remision = "'.$_GET['idremision'].'"';
        setq($sql);
      }
      $this->model->setdata($_GET['idremision'],$_POST['tablero'],$_POST['folioint'],NULL,$_POST['almacen'],$_POST['descuento'],$_POST['nmbac'],$_POST['correo'],$_POST['responsable'],$_POST['ffin'],$diva);
      $this->model->update();
      $this->model->calculaimporte($_GET['idremision']);

      redirect("?modulo=remisiones&accion=show&id=".$this->model->id);
    }
    function insertdetalle(){
      $this->model->id = $_GET['id'];
      $articulo = getIDprod($_POST['producto']);
      $count = busca($articulo,'remisionesd','rd_remision = "'.$_GET['id'].'" AND rd_articulo','COUNT(rd_id)');
      $bandera = 0;
      $estg="A";
      if($articulo == ""){
        $costo = 0;
        $nmbprod = $_POST['producto'];
        /*
            echo '<script>
                    alert("Error: El producto buscado no esta registrado en tu base de datos, verifica la información");
                    window.location.href="?modulo=remisiones&accion=show&id='.$_GET['id'].'";
                  </script>';
            die();

        */ 
      }else{
        //$viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
        $combo = busca($articulo,'articulos','a_id','a_tipoprod');
        if($combo == "M"){
          $sql = 'SELECT * FROM articulos_combos WHERE ac_articulo = "'.$articulo.'"';
          $result = setq($sql);
          while($row1 = $result->fetch_array()){
            $est = busca($row1['ac_ahijo'],'articulos','a_id','a_estatus');
            if($est == "I") $estg = "I";
          }

          if($estg == "A"){
            
            $result = setq($sql);
            while($row = $result->fetch_array()){
              $costo = busca($row['ac_ahijo'],'articulos_precios','ap_activo = "1" AND ap_articulo','ap_costo');
              $nmbprod = busca($row['ac_ahijo'],'articulos','a_id','a_nmb');
              $linean = busca($row['ac_ahijo'],'articulos','a_id','a_lineaneg');
              $idrec = getmax('rd_id','remisionesd','rd_remision');
              $sqlp = 'SELECT ap_precio FROM remisiones
                        INNER JOIN crm_clientes ON c_id = r_cliente
                        INNER JOIN articulos_precios ON ap_esquema = c_precio
                        INNER JOIN articulos ON ap_articulo = a_id
                        WHERE a_empresa = "'.$_SESSION['emp'].'" AND ap_activo = "1" AND r_id = "'.$_GET['id'].'"
                        AND ap_articulo = "'.$row['ac_ahijo'].'"';
              $resultp = setq($sqlp);
              list($imp) = $resultp->fetch_array();
              /*if(isset($_POST['iva'])) $impf = $imp*(1+($viva/100));
              else $impf = $imp;*/
              $this->model->setDatad($_GET['id'], $idrec, $row['ac_cantidad'], $row['ac_ahijo'],$imp,0,$costo,$linean,$nmbprod);
              $this->model->insertdetalle();
            }
            $bandera = 1;
          }
        }else{
          $estg = busca($articulo,'articulos','a_id','a_estatus');
          $costo = busca($articulo,'articulos_precios','ap_activo = "1" AND ap_articulo','ap_costo');
          $nmbprod = busca($articulo,'articulos','a_id','a_nmb');
        }
      }
      if($estg == "A"){
        if(isset($_POST['iva'])) $iva = 1; else $iva = 0;

        if($bandera == 0){
          $idrec = getmax('rd_id','remisionesd','rd_remision');
          $this->model->setDatad($_GET['id'], $idrec, $_POST['cantidad'], $articulo,$_POST['importe'],$iva,$costo,$_POST['linean'],$nmbprod);
          $this->model->insertdetalle();
        }
      
        $this->model->calculaimporte($_GET['id']);

        redirect("?modulo=remisiones&accion=show&id=".$this->model->id);
      }else{
        echo '<script>
                alert("El articulo no puede ser agregado por que esta en estatus inactivo.");
                  window.location.href="?modulo=remisiones&accion=show&id='.$_GET['id'].'";
            </script>';
      }
    }
    function updatedetalle(){

      if(isset($_POST['iva'])) $iva = 1; else $iva = 0;
      $viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
      $costo = busca($_GET['id'],'remisionesd','rd_id = "'.$_GET['idprod'].'" AND rd_remision','rd_costo');
      $nmbprod = clearvmayus($_POST['producto']);
      $diva = busca($_GET['id'],'remisiones','r_id','r_diva');
      if($diva == 1){
        $precionet = round($_POST['importe']*(1+$viva/100));
        if($_POST['preciodb'] == $precionet) $precio = $_POST['preciodb'];
        else $precio = $_POST['importe'];
      }else $precio = $_POST['preciodb'];

      $this->model->setDatad($_GET['id'], $_REQUEST['idprod'], $_POST['cantidad'], NULL,$precio,$iva,$costo,$_POST['linean'],$nmbprod);
      $this->model->updatedetalle();

      $this->model->calculaimporte($_GET['id']);

      redirect("?modulo=remisiones&accion=show&id=".$this->model->recepcion);
    }
    function delprod(){
      if(!isset($_GET['ex'])) $_GET['ex'] = NULL;
      $this->model->delprod($_GET['id'],$_GET['idprod'],$_GET['ex']);
      $this->model->calculaimporte($_GET['id']);

      redirect('?modulo=remisiones&accion=show&id='.$_GET['id']);
    }
    function aplicar(){
      
      $aplica = $this->model->aplicar($_GET['id']);
      $ok = "";
      if($aplica == "OK"){
        $this->model->select($_GET['id']);

        include_once('cxcobrar.php');
        $cuentaxc = new modelcxcobrar();
        $idcxc = $cuentaxc->insertcxcobrar($this->model->empresa,$this->model->cliente,$_GET['id'],'R',$this->model->total,$this->model->fliquidacion,$_POST['descripcion']);

        if(!isset($_POST['cxcobrar'])){
          include('cuentas.php');
          $cuent = new modelcuentas();
          $okcta = $cuent->cuentaactiva($_POST['cuenta']);

          if($okcta == "NOTOK") $ok = "&error=7XMR01";
          else{
            $corte = $okcta;
            //ID del ingreso

            include_once('ingresos.php');
            $folioing = getmax('i_id','ingresos','i_empresa = "'.$this->model->empresa.'"');
            $ingres = new Modelingresos();

            $ingres->setData($folioing,$this->model->empresa,$this->model->total,$this->model->cliente,$this->model->id,$_POST['cuenta'],date('Y-m-d H:i:s'),date('Y-m-d H:i:s'),$_SESSION['uid'],"N","INGRESO AUTOMÁTICO",NULL,$corte);
            $ingres->insert();
            $idcd = getmax('cd_id','cortesd','cd_corte = "'.$corte.'"');
            $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C",$ingres->iding,$this->model->total,"R",$_POST['cuenta']);
            $cuent->insertcd();

            $cuent->asignaricxc($ingres->iding,$idcxc,$this->model->total);
          }
        }
        if(isset($_POST['factura'])){
          include_once('facturas.php');
          $fact = new ModelFacturas();
          $fiscales = busca($this->model->cliente,'crm_fiscales','cf_predeterminada = "1" AND cf_cliente','cf_id');

          $idfac = $fact->insertfacturablanco($this->model->cliente,$fiscales,$_SESSION['emp'],"4.0");
          $fact->insertfacrem($idfac,$_GET['id']);
          $fact->setfiscales($_POST['fiscales'],$idfac);
          $fact->actualizapago($_POST['formapago'], "MXN", $_POST['metodopago'], NULL, $idfac, "1", "CONTADO");
          $sqlu = 'UPDATE facturas SET f_uso = "'.$_POST['uso'].'" WHERE f_id = "'.$idfac.'"';
          setq($sqlu) or die($sqlu);
        }

        $sql3 = 'UPDATE remisiones SET
                r_estatus = "A",
                r_faplica = "'.date('Y-m-d H:i:s').'",
                r_observaciones = "'.formmayus($_POST['descripcion'],true).'",
                r_uaplica = "'.$_SESSION['uid'].'"
                WHERE r_id="' . $this->model->id. '"';
        setq($sql3);

        $sql3 = 'UPDATE crm_tableros SET
                ct_estatus = "A"
                WHERE ct_id="' . $this->model->tablero. '"';
        setq($sql3);
        
        $estatuscl = busca($this->model->cliente,'crm_clientes','c_id','c_tipo');
        if($estatuscl == "P"){
          $sqlcliente = 'UPDATE crm_clientes SET c_tipo = "C" WHERE c_id = "'.$this->model->cliente.'"';
          setq($sqlcliente);
        }

        if(isset($_POST['imprimir']) && $aplica == "OK")//$_GET['message'] == "OK")
          echo '
            <script>
              window.open("formats/pdfremision.php?id='.$_GET['id'].'");
            </script>';

        redirect('?modulo=remisiones&accion=show&id='.$_GET['id'].'&message='.$aplica);

      }else{
        echo '<script>
          alert("El producto seleccionado: '.busca($aplica,'articulos','a_id','a_nmb').', no tiene existencia suficiente en el almacen, por favor verifica antes de poder aplicar la remisión.");
        </script>';
      }
    }
    function updateactivos(){
      $sql = 'DELETE FROM remision_activo WHERE ra_remision= "'.$_GET['remision'].'" AND ra_idr = "'.$_GET['id'].'"';
      setq($sql);

      $almacen = busca($_GET['remision'],'remisiones','r_id','r_almacen');
      $sql = 'SELECT activos.*,aa_noserie,aa_id FROM activo_almacen INNER JOIN activos ON aa_activo = a_id
              WHERE aa_almacen = "'.$almacen.'" AND aa_estatus = "L" ORDER BY aa_id ASC';
      $result = setq($sql);
      while($rowr = $result->fetch_array()){
        if(isset($_POST['activo'.$rowr['aa_id']]))
          $this->model->setactivorec($_GET['remision'],$_GET['id'],$rowr['aa_id']);
      }

      if(isset($_GET['from'])){
        echo '<script>
                opener.location.reload();
                window.close();
              </script>';
      }
    }
    function cancelar(){
      $motivo = clearvmayus($_REQUEST['motivoc']);
      $remision = $_GET['id'];

      include_once('cuentas.php');
      $cuent = new modelcuentas();
      $cuent->desasignarcxc($remision,"R");

      $sqlcxc = 'SELECT cx_id FROM cxcobrar WHERE cx_tipo = "R" AND cx_referencia = "'.$remision.'"';
      $resultcxc = setq($sqlcxc);
      while($rowcx = $resultcxc->fetch_array()){
        $sqlxx = 'UPDATE cxcobrar SET cx_estatus = "C" WHERE cx_id = "'.$rowcx['cx_id'].'"';
        setq($sqlxx);
      }
      $this->model->cancelar($remision,$motivo);

      redirect('?modulo=remisiones&accion=show&id='.$remision);
    }
    function recurrente(){
      if(isset($_POST['recurrencia'])){
        if($_POST['recurrencia'] == "1") $periodo = "s";
        else if($_POST['recurrencia'] == "2") $periodo = "q";
        else if($_POST['recurrencia'] == "3" || $_POST['recurrencia'] == "4" || $_POST['recurrencia'] == "5") $periodo = "m";
        else if($_POST['recurrencia'] == "6") $periodo = "a";

        if($periodo){
          if($periodo == "q") $dato = date("d");
          elseif(isset($_POST['semanal'])) $dato = $_POST['semanal'];
          else if(isset($_POST['mensual'])) $dato = $_POST['mensual'];
          else if(isset($_POST['prox']) && $_POST['prox'] != NULL) $dato = $_POST['prox'];

          if($dato){
            $siguiente = obtenerfecha($dato,$_POST['recurrencia']);
            $this->model->insertrecurrencia($_POST['idtablero'],$_GET['id'],$_POST['recurrencia'],"A",$siguiente,clearvmayus($_POST['nmbrem']));
          }else $error = "2";
        }else{
          $error = "2";
        }
      }else $error = "1";

      if(!$error){
        redirect('?modulo=remisiones&accion=index');
      }else if($error == "1"){
        echo '<script>
              alert("No se selecciono ninguna opcion para repetir la remisión seleccionada.");
              window.location.href="?modulo=remisiones&accion=show&id='.$_GET['id'].'";
          </script>';
      }else{
        echo '<script>
              alert("La información para repetir la remisión no esta completa o no se capturo, por favor vuelve a intentarlo.");
              window.location.href="?modulo=remisiones&accion=show&id='.$_GET['id'].'";
          </script>';
      }
    }

}

class modelremisiones {
  function result($fini,$ffin,$proveedor,$estatus,$documento,$page){
    $bloque = 50;
    $prov = clearvmayus($proveedor);
    $doc = clearvmayus($documento);

    $sql = 'SELECT * FROM remisiones WHERE r_empresa = "'.$_SESSION['emp'].'"
            AND DATE(r_falta) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
    if($proveedor) $sql.=' AND r_cliente IN (SELECT c_id FROM crm_clientes WHERE c_nmb LIKE "%'.$prov.'%" OR c_alias LIKE "%'.$prov.'%" OR c_apellidos LIKE "%'.$prov.'%")';

    $almacen = busca($_SESSION['uid'],'usuarios','u_id','u_almacen');

    if($grupo == "USER") $sql.=' AND r_cliente IN (SELECT c_id FROM crm_clientes WHERE c_almacen = "'.$almacen.'" )';

    if($documento) $sql.=' AND r_folio LIKE "%'.$doc.'%" ';
    if($estatus) $sql.=' AND r_estatus = "'.$estatus.'"';
    $sql.='ORDER BY r_id DESC ';
    $result = setq($sql);
    if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
    $this->resultrc = setq($sql);
    $this->resultt = setq($sql);
  }

  function select($id){
    $sql = 'SELECT * FROM remisiones WHERE r_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['r_id'];
    $this->empresa = $row['r_empresa'];
    $this->tablero = $row['r_tablero'];
    $this->cliente = $row['r_cliente'];
    $this->folio = $row['r_folio'];
    $this->estatus = $row['r_estatus'];
    $this->almacen = $row['r_almacen'];
    $this->nmb = $row['r_nmb'];
    $this->email = $row['r_email'];
    $this->fliquidacion= $row['r_fliquidacion'];
    $this->encargado= $row['r_encargado'];
    $this->falta = $row['r_falta'];
    $this->ualta = $row['r_ualta'];
    $this->uaplica = $row['r_uaplica'];
    $this->faplica = $row['r_faplica'];
    $this->ucan = $row['r_ucan'];
    $this->fcan = $row['r_fcan'];
    $this->descuento = $row['r_descuento'];
    $this->subtotal = $row['r_subtotal'];
    $this->iva = $row['r_iva'];
    $this->mdescuento = $row['r_mdescuento'];
    $this->total = $row['r_total'];
    $this->observaciones = $row['r_observaciones'];
  }

  function setdata($id,$tablero,$folioint,$cliente,$almacen,$descuento,$nmbac,$correo,$responsable,$ffin){
    $this->id = $id;
    $this->tablero = $tablero;
    $this->folioint = $folioint;
    $this->cliente = $cliente;
    $this->almacen= $almacen;
    $this->descuento = $descuento;
    $this->nmbac = clearvmayus($nmbac);
    $this->correo = $correo;
    $this->responsable = clearvmayus($responsable);
    $this->ffin = clearvmayus($ffin);
  }

  function insert(){

    $sql = 'INSERT INTO remisiones SET
            r_empresa = "'.$_SESSION['emp'].'",
            r_tablero = "'.$this->tablero.'",
            r_cliente = "'.$this->cliente.'",
            r_folio = "'.$this->folioint.'",
            r_almacen = "'.$this->almacen.'",
            r_descuento = "'.$this->descuento.'",
            r_nmb = "'.$this->nmbac.'",
            r_encargado = "'.$this->responsable.'",
            r_email = "'.$this->correo.'",
            r_fliquidacion = "'.$this->ffin.'",
            r_falta = "'.date('Y-m-d H:i:s').'",
            r_ualta = "'.$_SESSION['uid'].'",
            r_estatus	 = "N"';
    setq($sql);

    $this->id = getmax('r_id','remisiones','r_empresa = "'.$_SESSION['emp'].'"',false);
  }

  function update(){
    $sql = 'UPDATE remisiones SET
            r_almacen = "'.$this->almacen.'",
            r_descuento = "'.$this->descuento.'",
            r_nmb = "'.$this->nmbac.'",
            r_encargado = "'.$this->responsable.'",
            r_email = "'.$this->correo.'",
            r_fliquidacion = "'.$this->ffin.'",
            r_estatus	 = "N"
            WHERE r_id = "'.$this->id.'"';
    setq($sql);
  }

  function comprueba($id) {
    $validarex = true;
    $ok = "OK";

    if($ok == "OK"){
      $sql = 'SELECT count(*) FROM remisionesd WHERE rd_remision = "' . $id . '"';
      $result = setq($sql);
      list($cuenta) = $result->fetch_array();
      if ($cuenta >= 1) $ok = "OK";
      else $ok = 0;
    }

    if($ok == "OK"){
        $sql = 'SELECT SUM(rd_cantidad) FROM remisionesd WHERE rd_remision = "'.$id.'"
                AND rd_articulo IN (SELECT ap_producto FROM activo_producto)';
        $result = setq($sql);
        list($numprods) = $result->fetch_array();
        if(!$numprods) $numprods = 0;

        $sqlr = 'SELECT COUNT(*) FROM recepcion_activo WHERE ra_recepcion = "'.$id.'"';
        $resultr = setq($sqlr);
        list($numracs) = $resultr->fetch_array();
        if(!$numracs) $numracs = 0;

        if($numracs != $numprods) $ok = 0;
    }

    /*
        if($ok == "OK"){
          $sql = 'SELECT r_id,rd_articulo,r_almacen,rd_id,rd_precio,rd_costo,rd_iva,rd_cantidad
                  FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision
                  WHERE r_estatus = "N" AND r_id = "'.$id.'" AND rd_articulo != 0';
          $result1 = setq($sql);
          while($rowe = $result1->fetch_array()){
            $existeal = existencia($rowe['rd_articulo'],$rowe['r_almacen']);
            if($existeal < $rowe['rd_cantidad'] && $validarex == true){
              $ok = $rowe['rd_articulo'];
            }
          }
        }
    */

    if($ok == "OK"){
      //$sql = 'SELECT rd_cantidad,rd_articulo,rd_id FROM remisionesd INNER JOIN articulos ON a_id = rd_articulo
      $sql = 'SELECT COUNT(*) FROM remisionesd INNER JOIN articulos ON a_id = rd_articulo
              INNER JOIN activo_producto ON ap_producto = a_id
              WHERE rd_remision = "'.$id.'"
              GROUP BY rd_id';
      $result = setq($sql);
      while($row = $result->fetch_array()){
    /*
            $sqlac = 'SELECT a_capacidad FROM remision_activo INNER JOIN activos ON a_id = ra_activo
                      WHERE ra_remision = "'.$id.'" AND ra_idr = "'.$row['rd_id'].'"';
    */
        $sqlac = 'SELECT COUNT(*) FROM remision_activo
                  WHERE ra_remision = "'.$id.'" AND ra_idr = "'.$row['rd_id'].'"';
        $resultac = setq($sqlac);
        list($activos) = $resultac->fetch_array();
        if(!$activos) $activos = 0;

        if($activos != $row['rd_cantidad']){
          $ok = $rowe['rd_articulo'];
        }
      }

    }

    return($ok);
  }

  function selectd($id,$idd){
    $sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$id.'" AND rd_id = "'.$idd.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->idd = $row['rd_id'];
    $this->remisiond = $row['rd_remision'];
    $this->articulod = $row['rd_articulo'];
    $this->modelod = $row['rd_modelo'];
    $this->unidadd = $row['rd_unidad'];
    $this->nmbarticulod = $row['rd_nmbarticulo'];
    $this->lineand = $row['rd_linean'];
    $this->cantidadd = $row['rd_cantidad'];
    $this->preciod = $row['rd_precio'];
    $this->costod = $row['rd_costo'];
    $this->descuentod = $row['rd_descuento'];
    $this->ivad = $row['rd_iva'];
    $this->tipod = $row['rd_tipo'];
    $this->existenciaantd = $row['rd_existenciaant	'];

  }

  function resultd($id){
    $sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$id.'" ORDER BY rd_id';
    $this->resultr = setq($sql);
  }

  function setDatad($recepcion, $idrec, $cantidad, $articulo,$importe,$iva,$costo,$nmbarticulo, $modelo){
    $this->recepcion = $recepcion;
    $this->idrec = $idrec;
    $this->cantidad = $cantidad;
    $this->articulo = $articulo;
    $this->importe = $importe;
    $this->iva = $iva;
    $this->costo = $costo;
    $this->nmbarticulo = clearvmayus($nmbarticulo);
    $this->modelo = $modelo;
  }

  function insertdetalle(){
    $sql = 'INSERT INTO remisionesd SET
            rd_remision = "'.$this->id.'",
            rd_id = "'.$this->idrec.'",
            rd_articulo = "'.$this->articulo.'",
            rd_modelo = "'.$this->modelo.'",
            rd_cantidad = "'.$this->cantidad.'",
            rd_precio = "'.$this->importe.'",
            rd_costo = "'.$this->costo.'",
            rd_nmbarticulo = "'.$this->nmbarticulo.'",
            rd_iva = "'.$this->iva.'"';
    setq($sql);
  }

  function updatedetalle(){
    $sql = 'UPDATE remisionesd SET
            rd_cantidad = "'.$this->cantidad.'",
            rd_precio = "'.$this->importe.'",
            rd_costo = "'.$this->costo.'",
            rd_iva = "'.$this->iva.'"
            WHERE rd_remision = "'.$this->recepcion.'" AND rd_id = "'.$this->idrec.'"';
    setq($sql);
  }

  function delprod($remision,$idprod){
    $sql = 'DELETE FROM remisionesd WHERE rd_remision = "'.$remision.'" AND rd_id = "'.$idprod.'"';
    setq($sql);

    $sql = 'DELETE FROM remision_activo WHERE ra_remision = "'.$remision.'" AND ra_idr = "'.$idprod.'"';
    setq($sql);
  }

  function totalr($id){
    $total = 0;
    $pordes = busca($id,'remisiones','r_id','r_descuento');
    $descuento = 0;

    $sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$id.'" ';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $importe = ($row['rd_precio']*$row['rd_cantidad'])-$row['descuento'];
      $total+=$importe;
    }
    if($pordes > 0)
      $descuento = $total*($pordes/100);
    $total-=$descuento;

    return($total);
  }

  function aplicar($id,$validarex=true){
    $okex = "OK";
    /*
        $sql = 'SELECT r_id,rd_articulo,r_almacen,rd_id,rd_precio,rd_costo,rd_iva,rd_cantidad
                FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision
                WHERE r_estatus = "N" AND r_id = "'.$id.'" AND rd_articulo != "0"';
        $result1 = setq($sql);
        while($rowe = $result1->fetch_array()){
          $existeal = existencia($rowe['rd_articulo'],$rowe['r_almacen']);
          if($existeal < $rowe['rd_cantidad'] && $validarex == true){
            $okex = $rowe['rd_articulo'];
          }
        }

        if($okex == "OK"){
          $result1 = setq($sql);
          while($rowe = $result1->fetch_array()){
            $existeal = existencia($rowe['rd_articulo'],$rowe['r_almacen']);

            $sqld = 'UPDATE remisionesd SET rd_existenciaant = "'.$existeal.'" WHERE
                    rd_remision = "' .  $rowe['r_id'] . '" AND rd_id = "' . $rowe['rd_id'] . '" ';
            setq($sqld);
          }
          $result1 = setq($sql) or die($sql);
          while ($row = $result1->fetch_array()) {
            ajustaexistencia($row['rd_articulo'],$row['rd_cantidad'],$row['r_almacen'],"S");
          }
        }
    */

    return($okex);
  }

/*
function calculaimporte($cotizacion){
  $sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$cotizacion.'"';
  $result = setq($sql);
  $total = 0;
  $viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
  $pordes = busca($cotizacion,'remisiones','r_id','r_descuento');
  $descuento = 0;
  $totiva = 0;
  $subtotal = 0;
  while($row = $result->fetch_array()){
    $importe = $row['rd_precio']*$row['rd_cantidad'];

    if($pordes > 0){

      $descuni = $importe*($pordes/100);
      if($row['rd_iva'] == 0) $importe = ($importe/(1+($viva/100)));

      $impmd = $importe;
      $descuento+=$descuni;
    }
    else $impmd = $importe;

    $iva=$impmd*($viva/100);


    if($row['rd_iva'] == 1){
      $subtotal+=$impmd;
    }
    else{
      $subtotal+=$impmd;
    }

    $totiva+=$iva;
  }
  /*while($row = $result->fetch_array()){
    $importe = $row['rd_precio']*$row['rd_cantidad'];

    if($pordes > 0){
      if($row['rd_iva'] == 0) $importe = ($importe/(1+($viva/100)));

      $descuni = $importe*($pordes/100);
      $impmd = $importe-$descuni;
      $descuento+=$descuni;
    }
    else $impmd = $importe;

    $iva=$impmd*($viva/100);


    if($row['rd_iva'] == 1){
      $subtotal+=$impmd;
    }
    else{
      $subtotal+=($impmd-$iva);
    }

    $totiva+=$iva;
  } */
/*
  $total = $subtotal-$descuento+$totiva;

  $sql = 'UPDATE remisiones SET
          r_subtotal = "'.$subtotal.'",
          r_iva = "'.$totiva.'",
          r_mdescuento = "'.$descuento.'",
          r_total = "'.$total.'"
          WHERE r_id = "'.$cotizacion.'"';
  setq($sql);
}*/

function calculaimporte($cotizacion){
  $sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$cotizacion.'"';
  $result = setq($sql);
  $total = 0;
  $viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
  $pordes = busca($cotizacion,'remisiones','r_id','r_descuento');
  $descuento = 0;
  $totiva = 0;
  $subtotal = 0;
  while($row = $result->fetch_array()){
    $importe = $row['rd_precio']*$row['rd_cantidad'];

    if($pordes > 0){

      $descuni = $importe*($pordes/100);
      if($row['rd_iva'] == 0) $importe = ($importe/(1+($viva/100)));

      $impmd = $importe;
      $descuento+=$descuni;
    }
    else $impmd = $importe;

    $iva=$impmd*($viva/100);


    if($row['rd_iva'] == 1){
      $subtotal+=$impmd;
    }
    else{
      $subtotal+=($impmd-$iva);
    }

    $totiva+=$iva;
  }
  $total = $subtotal-$descuento+$totiva;

  $sql = 'UPDATE remisiones SET
          r_subtotal = "'.$subtotal.'",
          r_iva = "'.$totiva.'",
          r_mdescuento = "'.$descuento.'",
          r_total = "'.$total.'"
          WHERE r_id = "'.$cotizacion.'"';
  setq($sql);
}

  function setactivorec($id,$idrec,$idactivo){
    $sql = 'SELECT aa_estatus FROM activo_almacen WHERE aa_id = "'.$idactivo.'"';
    $result = setq($sql);
    list($estatus) = $result->fetch_array();
    if($estatus == "L"){
      $sqli = 'INSERT INTO remision_activo SET
               ra_remision= "'.$id.'",
               ra_idr = "'.$idrec.'",
               ra_activo = "'.$idactivo.'"';
      setq($sqli);
    }
  }

  function cancelar($remision,$motivo){
    $sql = 'SELECT r_almacen,r_estatus FROM remisiones WHERE r_id = "'.$remision.'"';
    $result = setq($sql);
    list($almacen,$estatus) = $result->fetch_array();

    $sql = 'UPDATE remisiones SET
            r_fcan = "'.date('Y-m-d H:i:s').'",
            r_ucan = "'.$_SESSION['uid'].'",
            r_estatus = "C",
            r_motivocan = "'.$motivo.'"
            WHERE r_id = "'.$remision.'"';
    setq($sql);

    if($estatus == "A"){
      $sqlrd = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$remision.'"';
      $resultrd = setq($sqlrd);
      while($rowrd = $resultrd->fetch_array()){
        if($rowrd['rd_articulo'] != 0) ajustaexistencia($rowrd['rd_articulo'],$rowrd['rd_cantidad'],$almacen,$modo="E",$fcaduca=NULL);
      }
    }

  }
  //
  function insertrecurrencia($tablero,$remision,$frecuencia,$est,$fecha,$nmb){
    $sql = 'INSERT INTO crm_tablerosrecurrentes SET
            ctr_tablero = "'.$tablero.'",
            ctr_remision = "'.$remision.'",
            ctr_recurrencia = "'.$frecuencia.'",
            ctr_estatus = "'.$est.'",
            ctr_faparicion = "'.$fecha.'",
            ctr_nmb = "'.$nmb.'"';
    $result = setq($sql) or die($sql);
  }
}

class viewremisiones {
  var $model;

function __construct($model) {
  $this->model = $model;
  $this->tipodoc = array("F"=>"Factura","R"=>"Remisión");
}

function browse($fini,$ffin,$proveedor,$estatus,$documento,$page) {
?>
<script language="JavaScript">
function checkSubmitmovimiento() {
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
</script>
<?php
  $this->model->aestatus = array("A" => "Finalizado","P" => "En espera de autorización","N" => "En Captura","C" => "Cancelado");
  $this->model->nestatus = array("A" => "btn-success","P" => "bg-amber bg-darken-2","N"=>"bg-yellow bg-darken-3","C" => "btn-danger");

  $accion = 'insert';
  if (isset($this->model->id)) {
    $accion = 'update';
  }
   //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions">';
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
  ?>
    <div class="mb-5">
      <a href="?modulo=remisiones&accion=edit">
        <button type="button" class="btn btn-primary">
          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
        </button>
      </a>
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>

    <div id="filter-panel" class="col-md-12 collapse filter-panel mb-2">
      <div class="panel panel-default">
        <div class="panel-body">
          <form class="form-inline" role="form" method="post" action="?modulo=remisiones&accion=index">
            <div class="mb-5 mr-1">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="<?php echo $fini ?>" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="<?php echo $ffin?>" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Cliente</label>
              <input type="text" class="form-control" name="cliente" placeholder="Nombre del cliente que buscas" onfocus="this.select();" value="<?php echo $proveedor ?>" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value="" <?php echo $selt; ?> >Todos</option>
                <option value="N" <?php echo $seln; ?> >En captura</option>
                <option value="A" <?php echo $sela; ?> >Aplicados</option>
                <option value="P" <?php echo $selp; ?> >Espera de aplicación</option>
                <option value="C" <?php echo $selc; ?> >Cancelados</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="">Aciones</label> <br>
              <button type="submit" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i>
              </button>
              <a href="?modulo=remisiones&accion=index"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i>
              </button></a>
            </div>
          </form>
        </div>
      </div>
    </div>
    </div>
    <div class="main-card mb-3 card">
    <div class="card-body row">

  <?php
  echo '<div class="table table-responsive">
        <table class="table table-hover table-striped">
        <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
          <tr>
            <th>Folio</th>
            <th>Cliente</th>
            <th>Nombre</th>
            <th>Último Movimiento</th>
            <th>Estatus</th>
            <th>Importe</th>
            <th></th>
          </tr>
        </thead>';
  while($row = $this->model->resultrc->fetch_array()){
    if($row['r_estatus'] == "A"){
      $lastm = "Aplicación: ".$row['r_uaplica'].'<br>'.date('d-m-Y',strtotime($row['r_faplica']));
    }
    elseif($row['r_estatus'] == "C"){
      $lastm = "Cancelación: ".$row['r_ucan'].'<br>'.date('d-m-Y',strtotime($row['r_fcan']));
    }
    else{
      $lastm = "Registro: ".$row['r_ualta'].'<br>'.date('d-m-Y',strtotime($row['r_falta']));
    }

    echo '<tr>
            <th>'.$row['r_folio'].'</th>
            <td>'.busca($row['r_cliente'],'crm_clientes','c_id','c_nmb').'</td>
            <td>'.$row['r_nmb'].'</td>
            <td>'.$lastm.'</td>
            <td class="'.$this->model->nestatus[$row['r_estatus']].'">'.$this->model->aestatus[$row['r_estatus']].'</td>
            <td class="number-align">$'.number_format($row['r_total'],2).'</td>
            <td>
              <a href="?modulo=remisiones&accion=show&id='.$row['r_id'].'">
                <button type="button" class="btn-sm btn-info"><i class="fa fa-eye"></i> Detalles</button>
              </a>';
      if($row['r_estatus'] == "A")
        echo '<a target="_BLANK" href="formats/pdfremision?id='.$row['r_id'].'">
                <button type="button" class="btn-sm btn-secondary"><i class="icon-print"></i> Imprimir</button>
              </a>';

    echo '</td>
          </tr>';
  }
  echo '</table></div>';
  if($_REQUEST['cliente']){

  }elseif($_REQUEST['fini'] == date('Y-m-d',strtotime('-60 days')) && $_REQUEST['ffin'] == date('Y-m-d')){

  echo '
  <div class="col-md-12 text-xs-center">
  <div class="mb-3">
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <li class="page-item">
          <a class="page-link" href="?modulo=remisiones&accion=index&page='.($_GET['page']-1).'" aria-label="Previous">
            <span aria-hidden="true">&laquo; Ant</span>
            <span class="sr-only">Anterior</span>
          </a>
        </li>';
        
  $numres = 50;
  $nr = $this->model->resultt->num_rows;
  $np = $nr/$numres;
  $paginaa = $page-1;
  $pagina = $page+1;
  $sqlpg = 'SELECT * FROM remisiones WHERE r_empresa = "'.$_SESSION['emp'].'"
  AND DATE(r_falta) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
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
    echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=remisiones&accion=index&page=0">'.$nombre.'</a></li>';
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
    echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=remisiones&accion=index&page='.$i.'">'.$nombre.'</a></li>';
  }
  
  if($pageres<9){

  }else{
  if($_GET['page'] == ($pageres-5)) $nombre = ($pageres-2);
  else $nombre = '... '.($pageres-2);
  if($_GET['page'] == $i) $active = "active";
  else $active = "";
  if($_GET['page'] >= ($pageres-4)) echo '';
  else echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=remisiones&accion=index&page='.($pageres-2).'">'.$nombre.'</a></li>';
    echo '
    <li class="page-item '.$active.'"><a class="page-link" href="?modulo=remisiones&accion=index&page='.($pageres-1).'">'.($pageres-1).'</a></li>';

  }
    echo '<li class="page-item">
                    <a class="page-link" href="?modulo=remisiones&accion=index&page='.($_GET['page']+1).'" aria-label="Next">
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

function edit($cliente){

  ?>
    <script>
    $(document).ready(function() {
        $('#cliente').on('keyup', function() {
          var key = $(this).val();
//          var empresa = $('#empresa').val();
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
              //Editamos el valor del input con data de la sugerencia pulsada
              $('#cliente').val($('#'+id).attr('data'));
              //Hacemos desaparecer el resto de sugerencias
              $('#suggestions-block').fadeOut(1000);
              setname();
//              alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
              return false;
            });
          }
        });
      });
    });
    </script>
  <?php
    echo '<div class="page-title-actions">
            <div class="row">
              <form action="?modulo=remisiones&accion=edit" method="post" autocomplete="off" name="actualizaclie">
                <div class="col-sm-12 col-md-1">';
    echo '        <a href="?modulo=remisiones&accion=index">
                    <button type="button" class="mb-2 mr-2 btn btn-warning">
                      <i class="fa fa-arrow-left"></i>Regresar
                    </button>
                  </a>
                </div>';
    echo '      <div class="col-12 col-sm-12 col-md-6">';
    echo '        <div class="mb-5">
                    <input type="text" name="cliente" id="cliente" placeholder="Nombre o alias del cliente" class="search_query form-control" value="'.$_REQUEST['cliente'].'" required autofocus >
                    <label for="nmb" class="mt-1">Nombre o alias del cliente</label>
                  </div>
                  <div id="suggestions-block"></div>';
      echo '    </div>
                <div class="col-12 col-sm-12 col-md-4 text-sm-center">
                  <button type="submit" class="btn btn-primary"><i class="icon-user-check"></i> Actualizar</button>
                  <a accesskey="B" href="popup/buscarclienterem?from=remisiones" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                    <button type="button" class="btn btn-info" ><i class="fa fa-search4"></i> Buscar</button>
                  </a>
                  <a href="?modulo=clientes&accion=edit" target="_BLANK">
                    <button type="button" class="btn bg-indigo bg-darken-1 text-white" ><i class="icon-user-plus2"></i> Nuevo</button>
                  </a>
                </div>
              </form>
            </div>';
    echo '</div>';

    if($cliente){
      include('modulos/clientes.php');
      $client = new modelclientes();
      $client->select($cliente);

      echo '<div class="row">
              <div class="col-8 col-md-8 card p-1 border-blue-grey">
                Crear Remisión para '.$client->nmb.' '.$client->apellidos.'
              </div>
              <div class="col-2 col-md-2">
                <a target="_BLANK" href="?modulo=clientes&accion=edit&id='.$client->id.'">
                  <button class="btn btn-secondary"><i class="icon-ios-person"></i> Ver Cliente</button>
                </a>
              </div>
              <div class="col-2 col-md-2">
                <a data-fancybox data-type="ajax" data-src="popup/setremision.php?cliente='.$client->id.'" href="javascript:;" >
                  <button class="btn bg-grey bg-darken-2 text-white"><i class="icon-cash"></i> Crear Remisión</button>
                </a>
              </div>
              ';
              
        echo '<div class="col-12 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <div class="card-block">
                      <div class="media">
                        <div class="media-body text-xs-left">
                          <h3 class="teal bg-darken-3">Tableros abiertos</h3>
                        </div>
                        <div class="p-2 text-xs-center bg-teal bg-darken-3 media-right media-middle">
                            <i class="icon-calculator4 font-medium-2 white"></i>
                        </div>
                      </div>
                    </div>
                  </div>';
        $sql = 'SELECT * FROM crm_tableros WHERE ct_cliente = "'.$client->id.'" AND ct_estatus  IN ("N","P","G","L")
                ORDER BY ct_fcierre DESC,ct_folio DESC';
        $result = setq($sql);
        if($result->num_rows > 0){
          echo '<div class="table-responsive">
                <table class="table table-hover table-striped">
                <thead class="thead-inverse">
                  <tr>
                    <th>Tablero</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Ver</th>
                  </tr>
                <thead>';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            echo '<tr>
                    <th>'.$row['ct_folio'].'</th>
                    <th>'.$row['ct_nmb'].'</th>
                    <th>'.date('d-m-Y',strtotime($row['ct_fcierre'])).'</th>
                    <th>
                      <a target="_BLANK" href="?modulo=tableros&accion=show&id='.$row['ct_id'].'">
                        <button class="btn btn-info"><i class="icon-mail-forward"></i></button>
                      </a>
                    </th>
                  </tr>';
          }
          echo '</table></div></div>';
        }
        else
          echo '<div class="alert alert-danger text-xs-center text-md-center"><h3>Sin Tableros abiertos</h3></div>
          </div>';

        echo '</div>';

        echo '<div class="col-12 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <div class="card-block">
                      <div class="media">
                        <div class="media-body text-xs-left">
                          <h3 class="indigo bg-darken-4">Últimas 5 ventas</h3>
                        </div>
                        <div class="p-2 text-xs-center bg-indigo bg-darken-4 media-right media-middle">
                            <i class="icon-coin-dollar font-medium-2 white"></i>
                        </div>
                      </div>
                    </div>
                  </div>';
        $sql = 'SELECT * FROM remisiones WHERE r_cliente = "'.$client->id.'"
                ORDER BY r_falta DESC,r_id DESC';
        $result = setq($sql);
        if($result->num_rows > 0){
          echo '<div class="table-responsive">
                <table class="table table-hover table-striped">
                <thead class="thead-inverse">
                  <tr>
                    <th>Folio</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Importe</th>
                    <th>Ver</th>
                  </tr>
                <thead>';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            echo '<tr>
                    <th>'.$row['r_folio'].'</th>
                    <th>'.$row['r_nmb'].'</th>
                    <th>'.date('d-m-Y',strtotime($row['r_falta'])).'</th>
                    <th>'.$row['r_total'].'</th>
                    <th>
                      <a target="_BLANK" href="?modulo=remisiones&accion=show&id='.$row['r_id'].'">
                        <button class="btn btn-info"><i class="icon-mail-forward"></i></button>
                      </a>
                    </th>
                  </tr>';
          }
          echo '</table></div></div>';
        }
        else
          echo '<div class="alert alert-danger text-xs-center text-md-center"><h3>Sin Ventas previas</h3></div>';

        echo '  </div>
              </div>
            </div>';
    }
    else{
      if(isset($_REQUEST['cliente'])){
        echo '<div class="row">
                <div class="col-12 col-md-12 alert alert-danger">
                  No existe un cliente que coincida con <span class="font-medium-2 text-bold-800 text-uppercase">'.$_POST['cliente'].'<span>
                </div>
              </div>  ';
      }
    }
}

function show($idremision){
$cliente = busca($idremision,'remisiones','r_id','r_cliente');
$esquema = busca($cliente,'crm_clientes','c_id','c_precio');
$tableroreg = busca($_GET['id'],'remisiones','r_id','r_tablero');
echo '
<div>
  <input type="text" name="esquema" id="esquema" value="'.$esquema.'" readonly hidden>
</div>
';
?>

   <script>
    $(document).ready(function() {
        $('#producto').on('keyup', function() {
          var key = $(this).val();
//          var empresa = $('#empresa').val();
          var dataString = 'producto='+key;
        $.ajax({
          type: "POST",
          url: "query/suggestproducts.php",
          data: dataString,
          success: function(data) {
            //Escribimos las sugerencias que nos manda la consulta
            $('#suggestions').fadeIn(500).html(data);
            //Al hacer click en alguna de las sugerencias
            $('.suggest-element').on('click', function(){
              //Obtenemos la id unica de la sugerencia pulsada
              var id = $(this).attr('id');
              var cb = $(this).attr('value');
              var esquema = $('#esquema').attr('value');
              //var esquema = document.getElementById('esquema');
              //Editamos el valor del input con data de la sugerencia pulsada
              $('#producto').val($('#'+id).attr('data'));
              //Hacemos desaparecer el resto de sugerencias
              $('#suggestions').fadeOut(500); 
              $.ajax({
              type: "POST",
              url: "query/setprecio.php",
              data: {'cb': cb,
                    'esquema' : esquema},
              success: function(dataRTN) {
                console.log(dataRTN);
                var importe = document.getElementById('importe');
                importe.value = dataRTN;
              }
              });
              //setname();
//              alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
              return false;
            });
          }
        });
      });
    });

    function motivacancela(remision){
      var motivocan = prompt("¿Cual es el motivo de la Motivo de canelación?");
      if(motivocan.length > 0){
        window.location.href="?modulo=remisiones&accion=cancelar&id=" + remision + "&motivoc=" + motivocan;
      }
    }
    </script>
<?php
  if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
  if ($_GET['alerta'] == 1) {
    echo alert("El articulo no Existe", true);
  }
  if($this->model->estatus == "N") $leyenda = 'Añadir productos a la Remisión ';
  else  $leyenda = 'Lista de productos en la Remisión ';


  echo '<script>
          function delprod(idprod){
            var conf = confirm("¿Deseas borrar el producto de la remisión?");
            if(conf == true){
              window.location.href="?modulo=remisiones&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
            }
          }
          function validafac(){
            var conf = confirm("¿Deseas enviar la remisión a la factura?");
            if(conf == true){
              window.location.href="?modulo=facturas&accion=facturarem&remision='.$this->model->id.'";
            }
          }
        </script>';

  echo '<div class="row page-title-actions">';
  if(isset($_GET['message']) && $_GET['message'] != "OK"){
    echo '<div class="col-12 alert alert-danger">Error de existencias '.busca($_GET['message'],'articulos','a_id','a_nmb').'</div>';
  }
  echo '<div class="col-md-12 mb-1">';
  /*if(isset($_GET['tab'])) echo '<a href="?modulo=tableros&accion=show&id='.$_GET['tab'].'" accesskey="">';
  else echo '  <a href="?modulo=remisiones&accion=index" accesskey="">'; */
  echo '<a href="?modulo=remisiones&accion=index" accesskey="">';
  echo '
            <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
          </a>';
  echo '
      <a href="?modulo=tableros&accion=show&id='.$tableroreg.'">
        <button type="button" class="btn btn-purple ml-1"><i class="icon-dollar"></i> Tablero </button>
      </a>
  ';

  if($this->model->estatus == "N"){
    echo '<a data-fancybox data-type="ajax" data-src="popup/setremision.php?idrem='.$this->model->id.'" href="javascript:;">
            <button type="button" class="btn btn-primary ml-1" data-toggle="tooltip" data-placement="top" title="Modificar información de la remisión"><i class="fa fa-pen"></i>Modificar</button>
          </a>';
  }

  if($this->model->estatus != "A"){
    if ($this->model->comprueba($this->model->id) == "OK") {
      if($this->model->estatus != "C")
      echo '<a data-fancybox data-type="ajax" data-src="popup/aplicaremision.php?id='.$this->model->id.'&rand='.rand(1,100).'" href="javascript:;">
            <button class="btn btn-success ml-1" onclick="aplicamov()"><i class="fa fa-check"></i> Aplicar</button></a>';
    }
    else{
      echo '<button class="btn btn-danger ml-1"  data-toggle="tooltip" data-placement="top" title="Revisar existencias del almacen y precios de tu remisión"><i class="icon-exclamation"></i> Aplicar</button>';
    }
  }
  else{ //Estatus = "A"
    echo '<a target="_BLANK" href="formats/pdfremision.php?id='.$this->model->id.'">
            <button type="button" class="btn btn-secondary ml-1"><i class="icon-print"></i> Imprimir</button>
          </a>';

    if(busca($this->model->id,'factura_remision','fr_remision','COUNT(*)') == 0)
      echo '<button class="btn bg-grey bg-darken-2 text-white ml-1" onclick="validafac('.$this->model->id.')"><i class="icon-code-working"></i> Facturar</button>';
    else
      echo '<a target="_BLANK" href="?modulo=facturas&accion=showfactura&factura='.busca($this->model->id,'factura_remision','fr_remision','fr_factura').'">
              <button class="btn btn-success ml-1"><i class="icon-code-working"></i> Facturada</button>
            </a>';
    //
    if(busca($this->model->id,'crm_tablerosrecurrentes','ctr_estatus = "A" AND ctr_remision','COUNT(*)') == 0){
      echo '<a data-fancybox data-type="ajax" data-src="popup/setrecurrente.php?tablero='.$this->model->tablero.'&remision='.$this->model->id.'&ran='.rand(1,999).'" href="javascript:;" >
              <button type="button" class="btn bg-cyan bg-darken-1 text-white ml-1">
                <span class="glyphicon glyphicon-cog"></span><i class="icon-repeat2"></i> Recurrente
              </button>
            </a>';
    }
    if(busca($this->model->id,'remisiones_devoluciones','rd_estatus IN ("N","A") AND rd_remision','COUNT(*)') == 0){
      echo '<a accesskey="B" href="popup/importarprodsdev.php?remision='.$this->model->id.'&tablero='.$this->model->tablero.'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
              <button type="button" class="btn btn-secondary ml-1 ">
                <span class="glyphicon glyphicon-cog"></span><i class="icon icon-undo3"></i>Devolucion
              </button>
            </a>';
    }
  }

  if($this->model->estatus != "C"){
    echo '<button class="btn btn-danger ml-1" onclick="motivacancela('.$this->model->id.');" data-toggle="tooltip" data-placement="top" title="Cancelar Remisión"><i class="fa fa-times"></i> Cancelar</button>';
  }

  echo '</div>';
  echo '<div class="col-md-6 card p-1 bg-grey bg-lighten-2">
          '.$this->model->folio.'  '.busca($this->model->cliente,'crm_clientes','c_id','CONCAT(c_nmb," ",c_apellidos)').'
        </div>
        <div class="col-md-6 card p-1 bg-blue-grey bg-lighten-4">Almacen: '.busca($this->model->almacen,'almacenes','a_id','a_nmb').'</div>';

  if($this->model->estatus == "A"){
    //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
  }

  echo '</div></div>';

  $readon = "";
  $exisantm = "";
  $miva = "";
  if($this->model->estatus == "N"){
    if(!isset($_GET['idd'])){
      $this->model->selectd($this->model->id,NULL);
      $action = "insertdetalle";
      $idprod = "";
      $this->model->cantidadd = 1;
    }
    else {
      $action= "updatedetalle";
      $idprod = $_GET['idd'];
      $this->model->selectd($this->model->id,$idprod);
      if($this->model->ivad == 1) $miva = "checked";
    }
    echo '<div class="row bg-grey bg-lighten-2">
            <div class="alert alert-primary h6 col-12 col-md-12"><center><b>Captura de productos</b></center></div>
            <form method="post" autocomplete="off" action="?modulo=remisiones&accion='.$action.'&id='.$this->model->id.'">
            <input type="hidden" name="idprod" value="'.$idprod.'" />
            <input type="hidden" name="costo" id="costo" value="'.$this->model->costod.'" />
              <div class="col-4 col-md-2">
                <div class="mb-5">
                  <label for="cant">Cantidad</label><br>
                  <input type="number" min="0" value="'.$this->model->cantidadd.'" max="9999999" step="0.01" name="cantidad" id="cantidad" placeholder="Cantidad" class="form-control" required="required" tabindex="1" />
                </div>
              </div>
              <div class="col-8 col-md-3">
                <div class="mb-5">
                  <label for="agregar" class">Producto</label><br>
                  <input type="text" name="producto" id="producto" value="'.$this->model->nmbarticulod.'" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus tabindex="2" >
                  <a accesskey="B" href="popup/buscarproduto?from=remisiones&accion=insertd&id='.$this->model->id.'&tipo=R" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                    <button type="button" class="btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="Usar el buscador ALT+B " data-original-title="Usar el buscador"><i class="fa fa-search4"></i>Buscar</button>
                  </a>
                 <div id="suggestions"></div>
                </div>
              </div>
              <div class="col-4 col-md-2">
                <div class="mb-5">
                  <label for="cant">Precio unitario </label><br>
                  <input type="number" min="0" max="9999999" step="0.01" value="'.$this->model->preciod.'" name="importe" id="importe" placeholder="Importe del concepto" class="form-control" required="required" tabindex="3" />
                </div>
              </div>
              <div class="col-4 col-md-1">
                <div class="mb-5">
                  <label for="cant">IVA</label><br>
                  <input type="checkbox" name="iva" '.$miva.' tabindex="4" />
                </div>
              </div>
              <div class="col-4 col-md-2">
                <div class="mb-5">
                  <label for="cant">Linea negocio</label><br>';
            $sql = 'SELECT * FROM lineas_negocio WHERE ln_empresa = "'.$_SESSION['emp'].'" AND ln_estatus = "A" ORDER BY ln_nmb ASC';
            $result = setq($sql);
            echo '<select name="linean" id="linean" required="required" class="form-control" tabindex="5">
            <option value="" disabled selected>Elige una linea</option>';
            while($row = $result->fetch_array()){
              if($this->model->lineand == $row['ln_id']) $linean = "selected"; else $linean = "";
              echo '<option value="'.$row['ln_id'].'" '.$linean.'>'.$row['ln_alias'].'- '.$row['ln_nmb'].'</option>';
            }
            echo '</select>
                </div>
              </div>
              <div class="col-12 col-md-1">
                <div class="mb-5">
                <label>.</label><br>
                  <button type="submit" class="btn btn-success" tabindex="6"><i class="icon-android-send"></i> Agregar</button>
                </div>
              </div>
            </form>
          </div>';
    $disab  = "";
  }
  else{
    echo '<div class="col-12 col-md-12 bg-primary p-1 text-white h5">
            '.$leyenda.' - '.$this->model->nmb.'
          </div>';
    $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
    $disab = ' disabled ';
    $exisantm = "Existencia antes del movimiento";
  }

  echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
        <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
          <tr>
            <th>Modelo</th>
            <th>Artículo</th>
            <th>Piezas vendidas</th>
            <th>Precio unitario</th>
            <th>+IVA</th>
            <th>Importe total</th>
            <th>'.$exisantm.'</th>
          </tr>
        </thead>';
  $subtotal = 0;
  $totiva = 0;
  $poriva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva')/100;

  while($row = $this->model->resultr->fetch_array()){
    if($row['rd_iva'] == "1"){
      $precio = $row['rd_precio'];
      $chiva = "checked";
    }
    else{
      $paraiva = (1+$poriva);
//      $precio = $row['rd_precio']/$paraiva;
      $precio = $row['rd_precio'];
      $chiva = "";
    }

    $importe = $precio*$row['rd_cantidad'];

    $sql = 'SELECT SUM(rd_cantidad) FROM remisionesd WHERE rd_remision = "'.$this->model->id.'"
            AND rd_articulo IN (SELECT ap_producto FROM activo_producto) AND rd_articulo = "'.$row['rd_articulo'].'"';
    $result = setq($sql);
    list($numprods) = $result->fetch_array();
    if($numprods > 0){
      $numact = busca($this->model->id,'remision_activo','ra_idr = "'.$row['rd_id'].'" AND ra_remision','COUNT(*)');
      $buttonact = '<br><a href="popup/elijeactivos-remision?modulo='.$_GET['modulo'].'&accion=seleccionar&remision='.$this->model->id.'&idrec='.$row['rd_id'].'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
              <button type="button" class="btn-sm bg-teal bg-darken-2 text-white"><i class="icon-cup"></i> Seleccionar activos
                 <div class="tag tag-pill tag-danger">'.$numact.'</div>
              </button>
            </a>';
    }
    else $buttonact = "";

    if($row['rd_articulo'] != 0) $modelo = busca($row['rd_articulo'],'articulos','a_id','a_modelo'); else $modelo = "-";

    echo '<tr><form method="post" action="?modulo=remisiones&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['rd_id'].'">
            <td>'.$modelo.'</td>
            <td>'.$row['rd_nmbarticulo'].$buttonact.'</td>
            <td>
              <input type="number" value="'.number_format($row['rd_cantidad'],2,'.','').'" min="0" '.$readon.' max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
            </td>
            <td>
              <input type="number" value="'.number_format($precio,2,'.','').'" min="0" '.$disab.' max="9999999" step="0.01" name="importe" placeholder="Monto unitario" class="form-control number-align" required="required" onfocus="this.select();" />
            </td>
            <td><input type="checkbox" name="iva" '.$chiva.' '.$readon.' onchange="submit();" /></td>
            <td class="number-align ">
              $ '.number_format($importe,2,'.',',').'
            </td>
            <td><center>';

  if($this->model->estatus == "N")
        echo '<button type="submit" class="btn btn-primary"><i class="fa fa-redo"></i></button>
              <a href="?modulo=remisiones&accion=show&id='.$this->model->id.'&idd='.$row['rd_id'].'">
                <button type="button" class="btn btn-info"><i class="fa fa-pen"></i> </button>
              </a>
              <button type="button" class="btn btn-danger" onclick="delprod('.$row['rd_id'].')"><i class="fa fa-trash"></i> </button>
              ';
  else
    echo $row['rd_existenciaant'];

      echo '</center></td>
          </form></tr>';
  }
  echo '<tfoot>';
  $totalf = $subtotal+$totiva;
  if($this->model->iva> 0){
    echo '<tr class="p-1 bg-grey bg-lighten-2">
            <td colspan="3">&nbsp;</td>
            <td colspan="2" class="number-align  h4">Subtotal</td>
            <td class="number-align h4">$'.number_format($this->model->subtotal,2).'</td>
          </tr>';
    if($this->model->mdescuento > 0)
      echo '<tr class="p-1 bg-grey bg-lighten-3">
              <td colspan="3" >&nbsp;</td>
              <td colspan="2"  class="number-align h4">Descuento '.$this->model->descuento.'%</td>
              <td  class="number-align bg-grey bg-lighten-3 h4">$'.number_format($this->model->mdescuento,2).'</td>
            </tr>';


    echo '<tr class="p-1 bg-grey bg-lighten-3">
            <td colspan="3" >&nbsp;</td>
            <td colspan="2"  class="number-align h4">IVA</td>
            <td  class="number-align bg-grey bg-lighten-3 h4">$'.number_format($this->model->iva,2).'</td>
          </tr>';

    echo '<tr class="p-1 bg-grey bg-lighten-2">
            <td colspan="3">&nbsp;</td>
            <td colspan="2"class="number-align h4">Total</td>
            <td  class="number-align h4">$'.number_format($this->model->total,2).'</td>
          </tr>';
  }
  else{
    echo '<tr class="p-1 bg-grey bg-lighten-2">
            <td colspan="3">&nbsp;</td>
            <td colspan="2"  class="number-align h4">Total</td>
            <td  class="number-align h4">$'.number_format($this->model->total,2).'</td>
          </tr>';
  }
  echo '
        </tfoot>';
  echo '</table></div>';

}

}
?>