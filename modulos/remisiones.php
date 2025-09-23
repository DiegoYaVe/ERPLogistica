<?php
  //ini_set('display_errors', 1);
  class remisiones {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelremisiones(isset($obj));
    }
    function index() {
      if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-30 days'));
      if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d'); 
      if (!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;
      if (!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if (!isset($_REQUEST['documento'])) $_REQUEST['documento'] = NULL;

      $grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
      /* if(!isset($_REQUEST['vendedor'])){ 
        if($grupo == "ADMIN" || $grupo == "GERENCIA" || $grupo == "FINANZAS") $vendedor = NULL;
        else $vendedor = $_SESSION['uid'];
      }else {
        if($grupo == "ADMIN" || $grupo == "GERENCIA" || $grupo == "FINANZAS") $vendedor = $_REQUEST['vendedor'];
        else $vendedor = $_SESSION['uid'];
      } */

      $this->model->result($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['cliente'],$_REQUEST['estatus'],$_REQUEST['documento'], $vendedor);
      $this->view = new viewremisiones($this->model);
      $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['cliente'],$_REQUEST['estatus'],$_REQUEST['documento'], $vendedor);
    }   
    function entregaparcial(){
      //foreachdie();
      $remision = $_REQUEST['remision'];
      $sql = 'SELECT * FROM remisionesc INNER JOIN articulos ON a_id = rc_articulo WHERE rc_remision = "'.$remision.'" AND rc_ligado is NULL ';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $sqlrc = 'SELECT rc_tipoenvio, rc_estatus, rc_embarque FROM remisionesc WHERE rc_id = "'.$row['rc_id'].'"';
        $resultrc = setq($sqlrc);
        list($tenvio, $eenvio, $embarcado) = $resultrc->fetch_array();
        if($tenvio == "C"){
          if(($eenvio == "P" && $embarcado == 0) || ($eenvio == "N")){
            if(isset($_REQUEST['entregar'.$row['rc_id']])){
              $sqlup = 'UPDATE remisionesc SET
                        rc_estatus = "P"
                        WHERE rc_id = "'.$row['rc_id'].'"';
              setq($sqlup);
    
              $sqlup2 = 'UPDATE remisionesc SET
                        rc_estatus = "P"
                        WHERE rc_ligado = "'.$row['rc_id'].'"';
              setq($sqlup2);
              
            }else{ 
              if($row['rc_estatus'] == "P"){
                $sqlup = 'UPDATE remisionesc SET
                          rc_estatus = "N"
                          WHERE rc_id = "'.$row['rc_id'].'"';
                setq($sqlup);
    
                $sqlup2 = 'UPDATE remisionesc SET
                        rc_estatus = "N"
                        WHERE rc_ligado = "'.$row['rc_id'].'"';
                setq($sqlup2);
              }
            }
          }
        } else{
        $eenvio = busca($row['rc_id'], 'remisionesc', 'rc_id', 'rc_estatus');
        if($eenvio == "N" || $eenvio == "A"){
          
        if(isset($_REQUEST['entregar'.$row['rc_id']])){
          $sqlup = 'UPDATE remisionesc SET
                    rc_estatus = "A"
                    WHERE rc_id = "'.$row['rc_id'].'"';
          setq($sqlup);

          $sqlup2 = 'UPDATE remisionesc SET
                    rc_estatus = "A"
                    WHERE rc_ligado = "'.$row['rc_id'].'"';
          setq($sqlup2);
          
        }else{
          if($row['rc_estatus'] == "A"){
            $sqlup = 'UPDATE remisionesc SET
                      rc_estatus = "N"
                      WHERE rc_id = "'.$row['rc_id'].'"';
            setq($sqlup);

            $sqlup2 = 'UPDATE remisionesc SET
                    rc_estatus = "N"
                    WHERE rc_ligado = "'.$row['rc_id'].'"';
            setq($sqlup2);
          }
        }
      }
      }
      }
      redirect('?modulo=remisiones&accion=show&id='.$remision);
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

    function garantia(){
      $this->model->resultg($_GET['id']);
      $this->view = new viewremisiones($this->model);
      $this->view->garantia($_GET['id']);
    }

    function seegarantias(){
      $this->model->results($_GET['id']);
      $this->view = new viewremisiones($this->model);
      $this->view->artengarantia($_GET['id']);
    }

    function sendproduccion (){
      $id = $_GET['id'];      
      $link = $_GET['link'];

      $this->model->accionprod($id, "P"); //P: Pendiente de aprobación en producción
      /* if($link == 1){
        $linkred = "?modulo=fabricagarantias&accion=index";
      } else { */
        $remision = busca($id, 'garantias_articulos', 'ga_id', 'ga_remision');
        $linkred = "?modulo=remisiones&accion=seegarantias&id=".$remision;
      /* } */
      redirect($linkred);
    }

    function insertgarantia(){
      if(isset($_GET['id'])){
        $rcid = $_GET['id'];
      } else {
        $rcid = '';
      }

      $descripcion = $_POST['descripcion'];
      $direccion = $_POST['direcciones'];

      $cliente = $_POST['cliente'];
      $remision = '';
      if(isset($_GET['tipo'])){
        $sqlart = 'SELECT rc_articulo, rc_modelo, rc_remision FROM remisionesc WHERE rc_id = "'.$rcid.'"';
        $resultart = setq($sqlart);
        list($articulo, $modelo, $remision) = $resultart->fetch_array();


        $articuloname = busca($articulo, "articulos", "a_id", "a_nmb");
        $var = busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'COUNT(*)');
        if ($var > 0) {
          $articuloname .= " " . busca($articulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'av_nmb');
        }

        $folio = busca($remision, 'remisiones', 'r_id', 'r_folio');
        $linkred = "?modulo=remisiones&accion=seegarantias&id=".$_POST['remision'];
      } else{
        $remision = $_POST['remision'];
        $folio = $_POST['remision'];
        $articulo = $_POST['articulo'];
        $modelo = $_POST['modelo'];
        $articuloname = $articulo . " " . $modelo;
        $linkred = "?modulo=remisiones&accion=garantia&id=".$_POST['remision'];
      }
      $_POST['nmbac'] = ' garantia';

      if(isset($_POST['estatusp'])){
        $_POST['exterior'] = '';
        $_POST['interior'] = '';
        $_POST['cp'] = '';
        $_POST['estadoh'] = '';
        $_POST['estado'] = '';
        $_POST['munidesc'] = '';
        $_POST['municipio'] = '';
        $_POST['col'] = '';
        $_POST['coldesc'] = '';
        $preciodolares = $_POST['precio'];
        $tcambio = $_POST['tcambio'];
        $precio = (floatval($tcambio) * floatval($preciodolares));
      } else{
        $precio = $_POST['precio'];
        $tcambio = "1";
        $preciodolares = 0;
      }
      $importe = $precio;
      $direccion = $this->insertdireccion();
      $referencia = $this->model->insertgarantia($articulo, $modelo, $descripcion, $rcid, $remision, $direccion, $cliente, $precio, $tcambio, $preciodolares);
      /* include("cuentas.php");
      $cuentas = new modelcuentas(); */
      $cxcobrar = $this->model->creargarantiaCXC($cliente, $referencia, $importe, $folio, $articuloname);
      /* $cuentas->asignaricxc($ingreso,$cxcobrar,$importe,$from); */
      redirect($linkred);
    }

    function confirmarecibido(){
      $id = $_GET['id'];

      $remision = busca($id, 'garantias_articulos', 'ga_id', 'ga_remision');

      $this->model->accionprod2($id, "M"); //R: Garantía recibida de producción
      redirect("?modulo=remisiones&accion=seegarantias&id=".$remision);
    }

    function insertdireccion(){
      if(isset($_POST['estatusp'])){
        $sqlf = ', cd_pais = "'.$_POST['pais'].'"';
      } else{
        $sqlf = ', cd_pais = "146"';
      }

      if($_POST['direcciones'] == "XXX"){
        $cl = $_POST['cliente'];

        $sql = 'INSERT INTO crm_direcciones SET
            cd_nmbdir = "Cotizacion '.$_POST['nmbac'].'",
            cd_cliente = "'.$cl.'",
            cd_tipodir = "2",
            cd_calle = "'.$_POST['calle'].'",
            cd_nume = "'.$_POST['exterior'].'",
            cd_numi = "'.$_POST['interior'].'",
            cd_colonia = "'.$_POST['col'].'",
            cd_municipio = "'.$_POST['municipio'].'",
            cd_cp = "'.$_POST['cp'].'",
            cd_estado = "'.$_POST['estadoh'].'",
            cd_predeterminada = "0"'.$sqlf.'';
            
        setq($sql);
        $direccion = getmax('cd_id', 'crm_direcciones', false, false);
      } else {
        $direccion = $_POST['direcciones'];
        $sql = 'UPDATE crm_direcciones SET cd_calle = "'.$_POST['calle'].'",
                      cd_nume = "'.$_POST['exterior'].'",
                      cd_numi = "'.$_POST['interior'].'",
                      cd_colonia = "'.$_POST['col'].'",
                      cd_municipio = "'.$_POST['municipio'].'",
                      cd_cp = "'.$_POST['cp'].'",
                      cd_estado = "'.$_POST['estadoh'].'"'.$sqlf.'
                      WHERE cd_id = "'.$direccion.'"';
        setq($sql);        
      }
      if(!isset($_GET['garantia'])){
        $sqlc = 'UPDATE crm_cotizaciones SET cc_direnvio = "'.$direccion.'"
                WHERE cc_remision = "'.$_POST['remision'].'"';
        setq($sqlc);
        $linkred = "?modulo=remisiones&accion=prodpordefinir&id=".$_POST['remision']."&tipo=2";
        redirect($linkred);
      } else{
        return $direccion;
      }
       /* else {
        $direccion = "0";
      } */
    }

    function prodpordefinir()
    {
      $this->view = new viewremisiones($this->model);
      $this->view->prodpordefinir();
    }

    function setprodpordefinir()
  {    
    $idcotiza = $_POST['idremision'];
    $resultado = $this->model->setprodpordefinir($idcotiza);
    $narticulosdo = intval($resultado[0]);
    $narticulosc = intval($resultado[1]);



    if($narticulosdo > 0){
      if($narticulosdo == 1){
        $cuerpo = $narticulosdo.' nuevo servicio listo para asignarle guía';
      } else{
        $cuerpo = $narticulosdo.' nuevos servicio listos para asignarles guía';
      }
      $grupos = '"LOGISTIC"';
          echo "
          <script>
            $.ajax({
              url: 'task/notificacionpushgrupos.php',
              method: 'POST',
              data: {
                      'tipo': 'PORDEFINIR',
                      'grupos': '".$grupos."',
                      'cotizacion': '".$idcotiza."',
                      'cuerpo': '".$cuerpo."'
                    },
            }).done(function(data){
            });          
            </script>
          ";
    } 

    if($narticulosc > 0){
      if($narticulosc == 1){
        $cuerpo = $narticulosc.' nuevo servicio listo para ser embarcado';
      } else{
        $cuerpo = $narticulosc.' nuevos servicios listos para ser embarcados';
      }
      $grupos = '"LOGISTIC"';
          echo "
          <script>
            $.ajax({
              url: 'task/notificacionpushgrupos.php',
              method: 'POST',
              data: {
                      'tipo': 'PORDEFINIR',
                      'grupos': '".$grupos."',
                      'cotizacion': '".$idcotiza."',
                      'cuerpo': '".$cuerpo."'
                    },
            }).done(function(data){
            });          
            </script>
          ";
    }
    
      echo '
      <script>
      window.location.href = "?modulo=remisiones&accion=prodpordefinir&tipo=2";
      </script>
      ';
      /* redirect('?modulo=guias&accion=prodpordefinir'); */
  }
    function insert(){
      if(isset($_POST['diva'])) $diva = "1"; else $diva = "0";
      $acronimo = busca($_SESSION['emp'],'empresas','e_id','e_siglas');
      $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-RM';
      $folioint = getmax('r_folio','remisiones');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }
      if($_POST['tablero'] == "XXX"){
        include_once('tableros.php');
        $table = new modeltableros();
        $folio = getmax('ct_folio','crm_tableros');
        if($folio == $acronimo.'1') $folio = $acronimo.str_pad(1,6,"0",STR_PAD_LEFT);
        $fini = explode(" ",date('Y-m-d H:i:s'));

        $table->setdata(NULL,$_POST['nmbac'],$folio,$_POST['cliente'],$_POST['responsable'],$fini[0],$fini[1],1,$fini[0],$_POST['niveltab'],"A", $valor);
        $table->insert();
        $_POST['tablero'] = $table->idt;
      }
      $this->model->setdata(NULL,$_POST['tablero'],$folioint,$_POST['cliente'],$_POST['almacen'],$_POST['descuento'],$_POST['nmbac'],$_POST['correo'],$_POST['responsable'],$_POST['ffin'],$diva);
      $this->model->insert();

      redirect("?modulo=remisiones&accion=show&id=".$this->model->id);
    }
    function updateremision(){
      if(isset($_POST['diva'])) $diva = "1"; else $diva = "0";
      $sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$_GET['idremision'].'"';
      $result = setq($sql);

      if($result->num_rows > 0){
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        $miva = busca($_GET['idremision'],'remisiones','r_id','r_diva');
        while($row = $result->fetch_array()){
          $proceso = "0";
          if($miva != $diva) $proceso = "1";

          if($proceso == "1"){
            if($row['rd_iva'] == "1" && $diva == "0"){
              $precio = $row['rd_precio'] * (1+($viva/100));
              $sqlup = 'UPDATE remisionesd SET rd_precio = "'.$precio.'" WHERE rd_id = "'.$row['rd_id'].'"';
              setq($sqlup);
            }elseif($row['rd_iva'] == "1" && $diva == "1"){
              $precio = $row['rd_precio'] / (1+($viva/100));
              $sqlup = 'UPDATE remisionesd SET rd_precio = "'.$precio.'" WHERE rd_id = "'.$row['rd_id'].'"';
              setq($sqlup);
            }
          }
        }
      }
      $estatus = busca($_GET['idremision'], 'remisiones', 'r_id', 'r_estatus'); 
      if($estatus == "A" || $estatus == "F" || $estatus == "FE"){
        $sqlupd = 'UPDATE remisiones SET r_faplica= "'.$_POST['faplica'].'" WHERE r_id = "'.$_GET['idremision'].'"';
        setq($sqlupd);
      }
      if(isset($_POST['envio'])){
        $envio = $_POST['envio'];
        $sqlupd = 'UPDATE remisiones SET r_precioenvio = "'.$envio.'" WHERE r_id = "'.$_GET['idremision'].'"';
        setq($sqlupd);
      }
      $this->model->setdata($_GET['idremision'],$_POST['tablero'],$_POST['folioint'],NULL,$_POST['almacen'],$_POST['descuento'],$_POST['nmbac'],$_POST['correo'],$_POST['responsable'],$_POST['ffin'],$diva);
      $this->model->update();
      $this->model->calculaimporte($_GET['idremision'], true);
      if(isset($_POST['envio'])){
        $remision = $_GET['idremision'];
        $sqlcxc = 'SELECT cx_id, cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$remision.'" AND cx_tipo = "R"';
        $resultcxc = setq($sqlcxc);
        list($cxid, $importe, $abonado, $estatuscxc) = $resultcxc -> fetch_array();
        $sqlr = 'SELECT r_total, r_estatus FROM remisiones WHERE r_id = "'.$remision.'"';
        $resultr = setq($sqlr);
        list($total, $estatusr) = $resultr -> fetch_array();
        $estatusr = "A";
        if($abonado == 0){
          $estatuscxc = "N"; 
        } else if($abonado < $total){
          $estatuscxc = "A";
        } else if($abonado >= $total){
          $estatuscxc = "F";
          $estatusr = "F";
          $sqlupd = 'UPDATE remisiones SET r_estatus = "F" WHERE r_id = "'.$remision.'"';
          setq($sqlupd);
          $sqlarticulos = 'UPDATE remisionesc SET rc_estatus = "P" WHERE rc_estatus = "N" AND rc_tipoenvio = "C" AND rc_remision = "'.$remision.'"';
          setq($sqlarticulos);
          $sqlarticulos2 = 'UPDATE remisionesc SET rc_estatus = "A" WHERE rc_estatus = "N" AND rc_tipoenvio IN ("D", "O") AND rc_remision = "'.$remision.'"';
          setq($sqlarticulos2);
        }
        $sqlupdr = 'UPDATE remisiones SET r_estatus = "'.$estatusr.'" WHERE r_id = "'.$remision.'"';
        setq($sqlupdr);
        $sqlupdcxc = 'UPDATE cxcobrar SET cx_importe = "'.$total.'", cx_estatus = "'.$estatuscxc.'" WHERE cx_id = "'.$cxid.'"';
        setq($sqlupdcxc);
      }
      redirect("?modulo=remisiones&accion=show&id=".$this->model->id);
    }
    function insertdetalle(){
      $this->model->select($_GET['id']);
      $articulo = getIDprod($_POST['producto']);
      $count = busca($articulo,'remisionesd','rd_remision = "'.$_GET['id'].'" AND rd_articulo','COUNT(rd_id)');
      $viva = busca("1",'configuracionesp','c_id','c_iva');
      $bandera = 0;
      $estg="A";
      if(!$articulo){ 
        list($articulo, $modelo) = getIDprodVar($_POST['producto']);
        if(!$articulo){
        echo '<script>
                alert("Error: El servicio buscado no esta registrado en tu base de datos, verifica la información");
                window.location.href="?modulo=remisiones&accion=show&id='.$_GET['id'].'";
              </script>';
        die();
        }else {
          $estg = busca($articulo,'articulos_variantes','av_modelo = "'.$modelo.'" AND av_articulo','av_estatus');
          $costo = busca($articulo,'articulos_precios','ap_activo = "1" AND ap_modelo = "'.$modelo.'" AND ap_articulo','ap_costo');
          $nmbprod = busca($articulo,'articulos_variantes','av_modelo = "'.$modelo.'" AND av_articulo','av_nmb');
        }
      }else{
        $modelo = NULL;
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        $combo = busca($articulo,'articulos','a_id','a_tipoprod');
        if($combo == "M"){
          $sql = 'SELECT * FROM articulos_combos WHERE ac_articulo = "'.$articulo.'"';
          $result = setq($sql);
          while($row1 = $result->fetch_array()){
            $est = busca($row1['ac_ahijo'],'articulos','a_id','a_estatus');
            if($est == "I") $estg = "I";
          }

          if($estg == "A"){
            $costo = busca($articulo,'articulos_precios','ap_activo = "1" AND ap_articulo','ap_costo');
            $nmbprod = busca($articulo,'articulos','a_id','a_nmb');

            /*$result = setq($sql);
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
              //if(isset($_POST['iva'])) $impf = $imp*(1+($viva/100));
              //else $impf = $imp;
              $ivades = 0;
              if(busca($_GET['id'],'remisiones','r_id','r_diva') == 1){
                $ivades = 1;
                $imp = $imp;
              }
              //$ivades = 0; ///////////////////////////////////////////////////////
              //$imp = $_POST['importe']; /////////////////////////////////////////////
              $this->model->setDatad($_GET['id'], $idrec, $row['ac_cantidad'], $row['ac_ahijo'],$imp,$ivades,$costo,$linean,$nmbprod);
              $this->model->insertdetalle();
            }
            $bandera = 1;*/
          }
        }else{
          $estg = busca($articulo,'articulos','a_id','a_estatus');
          $costo = busca($articulo,'articulos_precios','ap_activo = "1" AND ap_articulo','ap_costo');
          $nmbprod = busca($articulo,'articulos','a_id','a_nmb');
        }
      }
      $categoria = array();
      $k =0;
      $sqlcat = 'SELECT cat_id FROM categorias WHERE cat_inflable = "1"';
      $resultcat = setq($sqlcat);
      while($rowcat = $resultcat -> fetch_array()){
        $categoria[$k] = $rowcat['cat_id'];
        $k++;
      }
      $carart = busca($articulo, 'articulos', 'a_id', 'a_categoria');
      if(in_array($carart, $categoria)){
        echo '<script>
          alert("Error: No se puede insertar este artículo a la remisión");
        </script>';
        redirect("?modulo=remisiones&accion=show&id=".$this->model->id);
        die();
      }
      if($this->model->estatus == "A"){
        $existencia = existenciaModelo($articulo, $this->model->almacen, $modelo);
        //echo 'existencia: '.$existencia.' solicitud: '.$_POST['cantidad'];
        if($existencia < $_POST['cantidad']){
          echo '<script>
            alert("Error: No hay existencia suficiente para insertar este artículo a la remisión");
          </script>';
          redirect("?modulo=remisiones&accion=show&id=".$this->model->id);
          die();
        }     
      }
      
      if($estg == "A"){
        if(isset($_POST['iva'])) $iva = 1; else $iva = 0;

        $ivades = 0;
        $imp =$_POST['importecon'];
        if(busca($_GET['id'],'remisiones','r_id','r_diva') == 1){
          $ivades = 1;
          $imp = $_POST['importecon']/(1+($viva/100));
          //$imp = $_POST['importe'];
        }else{
          $ivades = 0;
          $imp = $_POST['importecon'];
        }
        //$ivades = 0; ///////////////////////////////////////////////////////
        //$imp = $_POST['importe']; /////////////////////////////////////////////
        if($bandera == 0){
          $idrec = getmax('rd_id','remisionesd','rd_remision');
          $this->model->setDatad($_GET['id'], $idrec, $_POST['cantidad'], $articulo,$imp,$ivades,$costo,$nmbprod, $modelo);
          $this->model->insertdetalle();
          if($this->model->estatus == "A"){
            ajustaexistencia($articulo, $_POST['cantidad'], $this->model->almacen, "S", "", $modelo, $_GET['id'], "R");
          }
        }

        $sqlrc = 'SELECT rc_tipoenvio, rc_paqueteria, rc_sucursal, rc_fenvio FROM remisionesc WHERE rc_remision = "'.$_GET['id'].'"';
        $resultrc = setq($sqlrc);
        list($tipoenvio, $paqueteria, $sucursal, $fenvio) = $resultrc -> fetch_row();

        for($i = 1; $i <= $_POST['cantidad']; $i++){
          $this->model->setDatac($_GET['id'],$idrec, $articulo, $modelo, $i, $tipoenvio, $paqueteria, $sucursal, '0', 'N', '', 'NULL', $fenvio, '', '');
          $this->model->insertdetallec();
        }
      
        $this->model->calculaimporte($_GET['id'], true);

        redirect("?modulo=remisiones&accion=show&id=".$this->model->id);
      }else{
        echo '<script>
                alert("El articulo no puede ser agregado por que esta en estatus inactivo.");
                  window.location.href="?modulo=remisiones&accion=show&id='.$_GET['id'].'";
            </script>';
      }
    }
    function updatedetalle(){
      if(isset($_POST['iva']) && $_POST['iva'] == "on") $iva = 1; 
      elseif($_POST['iva'] == "1") $iva = 1;
      else $iva = 0;
      $this->model->select($_GET['id']);
      if($this->model->estatus != "F" && $this->model->estatus != "FE" && $this->model->estatus != "A" && $this->model->estatus != "C"){
        $sqldet = 'SELECT * FROM remisionesd WHERE rd_id = "'.$_GET['idprod'].'"';
        $resultdet = setq($sqldet);
        $sqlrc = 'SELECT * FROM remisionesc WHERE rc_remisiond = "'.$_GET['idprod'].'" AND rc_numero = "1"';
        $resultrc = setq($sqlrc);
        list($tipoenvio, $paqueteria, $sucursal, $fenvio) = $resultrc -> fetch_array();
        
        $tipoenvio = busca($_GET['idprod'], 'remisionesc', 'rc_numero = "1" AND rc_remisiond', 'rc_tipoenvio');
        $rowdet = $resultdet -> fetch_array();
        $diferencia = abs($_POST['cantidad'] - $rowdet['rd_cantidad']);
        if($rowdet['rd_cantidad'] < $_POST['cantidad']){
          if($this->model->estatus == "A"){
            $existencia = existenciaModelo($rowdet['rd_articulo'], $this->model->almacen, $rowdet['rd_modelo']);
            if($existencia < $diferencia){
              echo '<script> alert("No hay existencia suficiente en el almacen para añadir a la venta.")</script>';
              die();
            } else {
              ajustaexistencia($rowdet['rd_articulo'], $diferencia, $this->model->almacen, "S", "", $rowdet['rd_modelo']);
            }
          }
          $rowdet['rd_cantidad']++;
          echo 'entra a insertar en remisionesc';
          for($i = $rowdet['rd_cantidad']; $i <= $_POST['cantidad']; $i++){
            $sqlins = 'INSERT INTO remisionesc SET rc_remision = "'.$_GET['id'].'",
                                                rc_remisiond = "'.$_GET['idprod'].'",
                                                rc_articulo = "'.$rowdet['rd_articulo'].'",
                                                rc_modelo = "'.$rowdet['rd_modelo'].'",
                                                rc_numero = "'.$i.'",
                                                rc_tipoenvio = "'.$tipoenvio.'",
                                                rc_paqueteria = "'.$paqueteria.'",
                                                rc_sucursal = "'.$sucursal.'",
                                                rc_combo = "0",
                                                rc_estatus = "N",
                                                rc_fenvio = "'.$fenvio.'"
                                                ';
            setq($sqlins);
          }
          
        } else if($rowdet['rd_cantidad'] > $_POST['cantidad']){
          if($this->model->estatus == "A"){
            ajustaexistencia($rowdet['rd_articulo'], $diferencia, $this->model->almacen, "E", "", $rowdet['rd_modelo']);
          }
          $sqldel = 'DELETE FROM remisionesc WHERE rc_numero > "'.$_POST['cantidad'].'" AND rc_remisiond = "'.$_GET['idprod'].'"';
          setq($sqldel);
        }
        
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        $costo = busca($_GET['id'],'remisionesd','rd_id = "'.$_GET['idprod'].'" AND rd_remision','rd_costo');
        $nmbprod = clearvmayus($_POST['producto']);
        $precio = $_POST['importe'];

        $this->model->setDatad($_GET['id'], $_REQUEST['idprod'], $_POST['cantidad'], NULL,$precio,$iva,$costo,$nmbprod, '');
        $this->model->updatedetalle();

        $alrem = busca($_GET['id'],'remisiones','r_id','r_almacen');
        $articulo = busca($_GET['idprod'],'remisionesd','rd_id','rd_articulo');
        if($articulo != ""){
          $compexiste = $this->model->comprueba($_GET['id'],$alrem,$articulo);
          if($compexiste !== "OK"){
            echo '<script>
              alert("El servicio seleccionado: '.busca($articulo,'articulos','a_id','a_nmb').', no tiene existencia suficiente en el almacen, por favor verifica antes de poder aplicar la remisión.");
              window.location.href="?modulo=remisiones&accion=delprod&id='.$_GET['id'].'&idprod='.$articulo.'&ex=1";
            </script>';
          }
        }

        $this->model->calculaimporte($_GET['id'], true);
      }
      redirect("?modulo=remisiones&accion=show&id=".$this->model->recepcion);
    }
    function delprod(){
      $this->model->select($_GET['id']);
      if(!isset($_GET['ex'])) $_GET['ex'] = NULL;
      $sql = 'SELECT rd_articulo, rd_modelo, rd_cantidad FROM remisionesd WHERE rd_id = "'.$_GET['idprod'].'"';
      $result = setq($sql);
      list($articulo, $modelo, $cantidad) = $result -> fetch_array();
      if($this->model->estatus == "A"){
        ajustaexistencia($articulo, $cantidad, $this->model->almacen, "E", "", $modelo, $_GET['id'], "R");
      }
      $this->model->delprod($_GET['id'],$_GET['idprod'],$_GET['ex']);
      $sqldel = 'DELETE FROM remisionesc WHERE rc_remisiond = "'.$_GET['idprod'].'"';
      setq($sqldel);
      
      $this->model->calculaimporte($_GET['id'], true);

      redirect('?modulo=remisiones&accion=show&id='.$_GET['id']);
    } 
    function aplicar(){
      //foreachdie();
      /* if(!isset($_POST['ingresocaja'])) $aplica = $this->model->aplicar($_GET['id']);
      else  */
      $datos = $this->model->existenciaapartados($_GET['id']);
      if($datos['error'] == "0"){
        if(isset($_POST['ingresocaja'])) {
          $caja = busca('A', 'cajas', 'c_estatus', 'c_id');
          if(!$caja){
            echo '<script>
              alert("Para poder insertar el abono a la caja, debe de haber una caja abierta.");
            </script>';

            redirect('?modulo=remisiones&accion=show&id='.$_GET['id']);
          }
        }
        if(isset($_POST['clienteenvio'])) $clienteenvio = "1";
        else $clienteenvio = 0;
        $sql3 = 'UPDATE remisiones SET r_estatus = "P", r_precioenvio = "'.$_POST['envio'].'", r_uaplica = "'.$_SESSION['uid'].'", r_faplica = "'.date('Y-m-d H:i:s').'", r_enviocliente = "'.$clienteenvio.'" WHERE r_id="' . $_GET['id']. '"';
        setq($sql3);
        $this->model->calculaimporte($_GET['id']);
        $this->model->select($_GET['id']);
        include_once('cxcobrar.php');
        $cuentaxc = new modelcxcobrar();
        $idcxc = busca($_GET['id'], 'cxcobrar', 'cx_tipo = "R" AND cx_referencia', 'cx_id');
        if(!$idcxc){
          $idcxc = $cuentaxc->insertcxcobrar($this->model->cliente,$_GET['id'],'R',$this->model->total,$this->model->fliquidacion,$_POST['descripcion']);
          $cuentaxc->addproyeccion($idcxc);
        }
        $remision = $this->model->id;
        $fecha = str_replace("T"," ",$_POST['fecha']);
        
        $cliente = $this->model->cliente;
        $fechareg=date('Y-m-d H:i:s');
        $user=$_SESSION['uid'];
        $estatus='P';
        $ind = 'I';
        include('cuentas.php');
        $cuent = new modelcuentas();

       if(!isset($_POST['ingresocaja'])){
          if(isset($_FILES['adjunto']['name'])){
            $diradj = 'adjuntos/ingresos';
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
          if($_POST['formapagoing'] == "23"){
            $cuenta = $_POST['cxsaldos'];
            $estatus = "F";
            $_POST['cuenta'] = "";
            $corte = '';
            $_POST['comision'] = '0';
            $ind = "F";
            $sqlcx = 'SELECT cx_abonado, cx_importe FROM cxcobrar WHERE cx_id = "'.$cuenta.'"';
            $resultcx = setq($sqlcx);
            list($abonosal, $importesal) = $resultcx -> fetch_array();
            $newabono = $abonosal + $_POST['importe'];
            if($newabono >= $importesal){
              $estcxc = "F";
              $remision = busca($cuenta, 'cxcobrar', 'cx_id', 'cx_referencia');
              $penalizacion = busca($remision, 'penalizaciones', 'p_remision', 'p_id');
              $sqlupdp = 'UPDATE penalizaciones SET p_estatus = "F" WHERE p_id = "'.$penalizacion.'"';
              setq($sqlupdp);
            } else {
              $estcxc = "A";
            }
            $sqlupdcxc = 'UPDATE cxcobrar SET cx_abonado = "'.$newabono.'", cx_estatus = "'.$estcxc.'" WHERE cx_id = "'.$cuenta.'"';
            setq($sqlupdcxc);
            $aplica = $this->model->aplicar($_GET['id']);
            $sqlupd = 'UPDATE remisiones SET r_estatus = "A" WHERE r_id="' . $_GET['id']. '"';
            setq($sqlupd);
          } else {
            $cuenta = $_POST['cuenta'];
            $okcta = $cuent->cuentaactiva($_POST['cuenta']);
            $corte = $okcta;
          }
          //ID del ingreso
          include_once('ingresos.php');
          $ing = new Modelingresos();

          $ing->setData(NULL,$_POST['importe'],$cliente,$_POST['referencia'],$cuenta,$fecha,$fechareg,$user,$estatus,$_POST['observaciones'],$target,$corte, $_POST['formapagoing'], $_POST['comision']);
          $ing->insert();
          
          $importelt = $_POST['importe'];
          $abonado = busca($idcxc,'cxcobrar','cx_id','cx_abonado');
          $ing->asignar($ing->iding,$idcxc,$importelt,$abonado, $ind);
        } else {
          
          /* if($_POST['formapagoing'] == "1") $cuenta = "0";
          else $cuenta = busca('1', 'cuentas', 'cu_caja', 'cu_id');
          $sqlins = 'INSERT INTO cajasd SET cd_caja = "'.$caja.'",
                                        cd_remision = "'.$remision.'",
                                        cd_monto = "'.$_POST['importe'].'",
                                        cd_cuenta = "'.$cuenta.'",
                                        cd_fpago = "'.$_POST['formapagoing'].'",
                                        cd_fgen = "'.$fecha.'",
                                        cd_ugen = "'.$_SESSION['uid'].'",
                                        cd_estatus = "P"  
                                        ';
          setq($sqlins);      */
        }

        if(isset($_POST['factura'])){
          include_once('facturas.php');
          $fact = new ModelFacturas();
          //$fiscales = busca($this->model->cliente,'crm_fiscales','cf_predeterminada = "1" AND cf_cliente','cf_id');
          $fiscales = $_POST['fiscales'];
          if(!isset($_POST['fiscales'])) $fiscales = "0";

          $idfac = $fact->insertfacturablanco($this->model->cliente,$fiscales,1,"4.0");
          $fact->insertfacrem($idfac,$_GET['id']);
          $fact->setfiscales($_POST['fiscales'],$idfac);
          $fact->actualizapago($_POST['formapagoing'], "MXN", $_POST['metodopago'], NULL, $idfac, "1", "CONTADO");
          $sqlu = 'UPDATE facturas SET f_uso = "'.$_POST['uso'].'" WHERE f_id = "'.$idfac.'"';
          setq($sqlu) or die($sqlu);
        }

        

        

        /* $actualizacionExitosa = 1; */

        //if ($actualizacionExitosa) {
          
        $estatuscl = busca($this->model->cliente,'crm_clientes','c_id','c_tipo');
        if($estatuscl == "P"){
          $sqlcliente = 'UPDATE crm_clientes SET c_tipo = "C" WHERE c_id = "'.$this->model->cliente.'"';
          setq($sqlcliente);
        }

        $tablero = busca($_GET['id'], 'remisiones', 'r_id', 'r_tablero');
        $sqlupd = 'UPDATE crm_tableros SET ct_estatus = "F" WHERE ct_id = "'.$tablero.'" '; 
        setq($sqlupd);
        echo '
          <script>
          $.ajax({
            url: "query/correocotizaciones.php",
            method: "POST",
            data: {"remision": "'.$this->model->id.'",
                    "correoop": "5"
                  },
          }).done(function(data){
          });          
          </script>';

          echo '
        <script>
          $.ajax({
            url: "task/notificacionpush.php",
            method: "POST",
            data: { 
                    "remision": "'.$this->model->id.'",
                    "estatus": "P"},
          }).done(function(data){
            window.location.href = "?modulo=remisiones&accion=show&id='.$_GET['id'].'&message='.$aplica.'";
          });          
          </script>
        ';       

         /*  echo '<script>
          $.ajax({
            url: "correocliente.php",
            method: "POST",
            data: {
              "remision": "'.$this->model->id.'",
              "correo": "'.$this->model->email.'"
            },
          }).done(function(datax){
            console.log(datax);
            window.location.href = "?modulo=remisiones&accion=index&id='.$_GET['id'].'&message='.$aplica.'";
          });          
          </script>'; */
        //}

        

        /*  if(isset($_POST['imprimir']) && $aplica == "OK")//$_GET['message'] == "OK")
          echo '
            <script>
              window.open("formats/pdfremision.php?id='.$_GET['id'].'");
            </script>'; */

       /* redirect('?modulo=remisiones&accion=show&id='.$_GET['id'].'&message='.$aplica); */

      }else{
        echo '<script>
          alert("El servicio seleccionado: '.$datos['articulos']['0'].', no tiene existencia suficiente en el almacen, por favor verifica antes de poder aplicar la remisión.");
        </script>';
        redirect('?modulo=remisiones&accion=show&id='.$_GET['id']);
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
      $estatus = busca($remision, 'remisiones', 'r_id', 'r_estatus');
      $cliente = busca($remision, 'remisiones', 'r_id', 'r_cliente');
      $tablero = busca($remision, 'remisiones', 'r_id', 'r_tablero');
      $nmb = busca($remision, 'crm_clientes', 'c_id', 'c_nmb');
      $ape = busca($remision, 'crm_clientes', 'c_id', 'c_apellidos');
      $nmbcliente = $nmb. ' '.$ape;

      if($estatus == 'A' || $estatus == 'F'){
        include_once('cuentas.php');
        include_once('cxcobrar.php');
        $cuent = new modelcuentas();
        $cxc = new modelcxcobrar();
        
        //$cuent->desasignarcxc($remision,"R");
        $importe = 0; 
        $sqlcxc = 'SELECT cx_id, cx_abonado FROM cxcobrar WHERE cx_tipo = "R" AND cx_referencia = "'.$remision.'"';
        $resultcxc = setq($sqlcxc);
        while($rowcx = $resultcxc->fetch_array()){
          $sqlxx = 'UPDATE cxcobrar SET cx_estatus = "C" WHERE cx_id = "'.$rowcx['cx_id'].'"';
          setq($sqlxx);
          $importe += $rowcx['cx_abonado'];
        }
        if($importe > 0){
          $sqlins = 'INSERT INTO penalizaciones SET p_tablero = "'.$tablero.'",
                                                    p_cliente = "'.$cliente.'",
                                                    p_remision = "'.$remision.'",
                                                    p_monto = "'.$importe.'",
                                                    p_estatus = "N", 
                                                    p_fgen = "'.date('Y-m-d H:i:s').'",
                                                    p_ugen = "'.$_SESSION['uid'].'"
                                                    ';
          setq($sqlins);                                                    
          //$cxc -> insertcxcobrar($cliente, $remision, "S", $importe, date('Y-m-d'), "Saldo del cliente ".$nmbcliente);
        }
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
        }else $error = "2";
      }else $error = "1";

      if(!$error) redirect('?modulo=remisiones&accion=show&id='.$_GET['id']);
      else if($error == "1"){
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
    function cambiarcantidad(){
      if(isset($_REQUEST['cantidad'])){
        $sqldet = 'SELECT * FROM remisionesd WHERE rd_id = "'.$_GET['id'].'"';
        $resultdet = setq($sqldet);
        $rowdet = $resultdet -> fetch_array();
        $this->model->select($rowdet['rd_remision']);
        if($this->model->estatus != "F" && $this->model->estatus != "FE" && $this->model->estatus != "C"){
          $sqlrc = 'SELECT rc_tipoenvio, rc_paqueteria, rc_sucursal, rc_fenvio FROM remisionesc WHERE rc_remisiond = "'.$_GET['id'].'" AND rc_numero = "1"';
          $resultrc = setq($sqlrc);
          list($tipoenvio, $paqueteria, $sucursal, $fenvio) = $resultrc -> fetch_array();
          $tipoenvio = busca($_GET['id'], 'remisionesc', 'rc_numero = "1" AND rc_remisiond', 'rc_tipoenvio');
          $diferencia = abs($_REQUEST['cantidad'] - $rowdet['rd_cantidad']);
          if($rowdet['rd_cantidad'] < $_REQUEST['cantidad']){
            $existencia = existenciaModelo($rowdet['rd_articulo'], $this->model->almacen, $rowdet['rd_modelo']);
            if($this->model->estatus == "A"){
              if($existencia < $diferencia){
                echo '<script> alert("No hay existencia suficiente en el almacen para añadir a la venta.")</script>';
                die();
              } else {
                ajustaexistencia($rowdet['rd_articulo'], $diferencia, $this->model->almacen, "S", "", $rowdet['rd_modelo']);
              }
            }
            $rowdet['rd_cantidad']++;
            for($i = $rowdet['rd_cantidad']; $i <= $_REQUEST['cantidad']; $i++){
              $sqlins = 'INSERT INTO remisionesc SET rc_remision = "'.$rowdet['rd_remision'].'",
                                                  rc_remisiond = "'.$_GET['id'].'",
                                                  rc_articulo = "'.$rowdet['rd_articulo'].'",
                                                  rc_modelo = "'.$rowdet['rd_modelo'].'",
                                                  rc_numero = "'.$i.'",
                                                  rc_tipoenvio = "'.$tipoenvio.'",
                                                  rc_paqueteria = "'.$paqueteria.'",
                                                  rc_sucursal = "'.$sucursal.'",
                                                  rc_combo = "0",
                                                  rc_estatus = "N",
                                                  rc_fenvio = "'.$fenvio.'"
                                                  ';
              setq($sqlins);
            }
            
          } else if($rowdet['rd_cantidad'] > $_REQUEST['cantidad']){
            if($this->model->estatus == "A"){
              ajustaexistencia($rowdet['rd_articulo'], $diferencia, $this->model->almacen, "E", "", $rowdet['rd_modelo']);
            }
            $sqldel = 'DELETE FROM remisionesc WHERE rc_numero > "'.$_REQUEST['cantidad'].'" AND rc_remisiond = "'.$_GET['id'].'"';
            setq($sqldel);
          }
          $sql = 'UPDATE remisionesd SET
                  rd_cantidad = "'.$_REQUEST['cantidad'].'"
                  WHERE rd_id = "'.$_GET['id'].'"';
          setq($sql);
          

          $this->model->calculaimporte($_GET['remision'], true);
        }
      } 
      redirect("?modulo=remisiones&accion=show&id=".$_GET['remision']);
    }
    function abonaremision(){
      //foreachdie();
      include_once('ingresos.php');
      $ing = new Modelingresos();
      include_once('cuentas.php');
      $cuent = new modelcuentas();

      $remision = $_POST['remision'];
      $rfolio = busca($remision, 'remisiones', 'r_id', 'r_folio');
      $fecha = str_replace("T"," ",$_POST['fecha']);
      $okcta = $cuent->cuentaactiva($_POST['cuenta']);

      $corte = $okcta;
      $cliente = busca($remision, 'remisiones', 'r_id', 'r_cliente');
      //ID del corted
      /* $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
      $result = setq($sql);
      list($idcd) = $result->fetch_array();
      $idcd++; */

      $fechareg=date('Y-m-d H:i:s');
      $user=$_SESSION['uid'];
      $estatus='P';
      $ind = 'I';

      if(isset($_FILES['adjunto']['name'])){
        $diradj = 'adjuntos/ingresos';
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

      if($_POST['formapagoing'] == "23"){
        $cuenta = $_POST['cxsaldos'];
        $estatus = "F";
        $_POST['cuenta'] = "";
        $corte = '';
        $_POST['comision'] = '0';
        $ind = "F";
        $sqlcx = 'SELECT cx_abonado, cx_importe FROM cxcobrar WHERE cx_id = "'.$cuenta.'"';
        $resultcx = setq($sqlcx);
        list($abonosal, $importesal) = $resultcx -> fetch_array();
        $newabono = $abonosal + $_POST['importe'];
        if($newabono >= $importesal){
          $estcxc = "F";
          $remisionpen = busca($cuenta, 'cxcobrar', 'cx_id', 'cx_referencia');
          $penalizacion = busca($remisionpen, 'penalizaciones', 'p_remision', 'p_id');
          $sqlupdp = 'UPDATE penalizaciones SET p_estatus = "F" WHERE p_id = "'.$penalizacion.'"';
          setq($sqlupdp);
        } else {
          $estcxc = "A";
        }
        $sqlupdcxc = 'UPDATE cxcobrar SET cx_abonado = "'.$newabono.'", cx_estatus = "'.$estcxc.'" WHERE cx_id = "'.$cuenta.'"';
        setq($sqlupdcxc);
      } else {
        $cuenta = $_POST['cuenta'];
        $okcta = $cuent->cuentaactiva($_POST['cuenta']);
        $corte = $okcta;
      }
      $ing->setData(NULL,$_POST['importe'],$cliente,$_POST['referencia'],$cuenta,$fecha,$fechareg,$user,$estatus,$_POST['observaciones'],$target,$corte, $_POST['formapagoing'], $_POST['comision']);
      $ing->insert();
      /* $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C",$ing->iding,$_POST['importe'],"R",$_POST['cuenta']);
      $cuent->insertcd(); */
      
      $idcc = $_POST['idcxc'];
      $importelt = $_POST['importe'];
      $abonado = busca($idcc,'cxcobrar','cx_id','cx_abonado');
      $ing->asignar($ing->iding,$idcc,$importelt,$abonado, $ind);
      
      echo '
      <script>
        $.ajax({
          url: "task/notificacionpush.php",
          method: "POST",
          data: { "remision": "'.$remision.'",
                  "estatus": "A",
                  "cuerpo": "Abono a remisión '.$rfolio.'"
                },
        }).done(function(data){
          window.location.href = "?modulo=remisiones&accion=show&id='.$remision.'";
        });          
        </script>
      ';  
      
      /* redirect("?modulo=remisiones&accion=show&id=".$remision); */
    }

	function abonargarantia(){
      //foreachdie();
      include_once('ingresos.php');
      $ing = new Modelingresos();
      include_once('cuentas.php');
      $cuent = new modelcuentas();

      $garantia = $_POST['garantia'];
      $fecha = str_replace("T"," ",$_POST['fecha']);
      $okcta = $cuent->cuentaactiva($_POST['cuenta']);

      $corte = $okcta;
      $cliente = busca($garantia, 'garantias_articulos', 'ga_id', 'ga_cliente');
      //ID del corted
      /* $sql = 'SELECT MAX(cd_id) FROM cortesd WHERE cd_corte="'.$corte.'"';
      $result = setq($sql);
      list($idcd) = $result->fetch_array();
      $idcd++; */

      $fechareg=date('Y-m-d H:i:s');
      $user=$_SESSION['uid'];
      $estatus='P';

      if(isset($_FILES['adjunto']['name'])){
        $diradj = 'adjuntos/ingresos';
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

      if(!isset($_POST['ingresocaja'])){
        $ing->setData(NULL,$_POST['importe'],$cliente,$_POST['referencia'],$_POST['cuenta'],$fecha,$fechareg,$user,$estatus,$_POST['observaciones'],$target,$corte, $_POST['formapago'], $_POST['comision']);
        $ing->insert();
        /* $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C",$ing->iding,$_POST['importe'],"R",$_POST['cuenta']);
        $cuent->insertcd(); */
        
        $idcc = $_POST['idcxc'];
        $importelt = $_POST['importe'];
        $abonado = busca($idcc,'cxcobrar','cx_id','cx_abonado');
        $ing->asignar($ing->iding,$idcc,$importelt,$abonado, "I");
      } else {
        if(isset($_POST['ingresocaja'])) {
          $caja = busca('A', 'cajas', 'c_estatus', 'c_id');
          if(!$caja){
            echo '<script>
              alert("Para poder insertar el abono a la caja, debe de haber una caja abierta.");
            </script>';

            if(isset($_GET['fa'])){
              redirect("?modulo=fabricagarantias&accion=index");
            } else{
              $remision = busca($garantia, 'garantias_articulos', 'ga_id', 'ga_remision');
              redirect("?modulo=remisiones&accion=seegarantias&id=".$remision);
            }
          }
        }
        if($_POST['formapago'] == "1") $cuenta = "0"; 
        else $cuenta = busca('1', 'cuentas', 'cu_caja', 'cu_id');

          $sqlins = 'INSERT INTO cajasd SET cd_caja = "'.$caja.'",
                                        cd_garantia = "'.$garantia.'",
                                        cd_monto = "'.$_POST['importe'].'",
                                        cd_cuenta = "'.$cuenta.'",
                                        cd_fpago = "'.$_POST['formapago'].'",
                                        cd_fgen = "'.$fecha.'",
                                        cd_ugen = "'.$_SESSION['uid'].'",
                                        cd_estatus = "P"  
                                        ';
          setq($sqlins);     
      }
      
      if(isset($_GET['fa'])){
        redirect("?modulo=fabricagarantias&accion=index");
      } else{
        $remision = busca($garantia, 'garantias_articulos', 'ga_id', 'ga_remision');
        redirect("?modulo=remisiones&accion=seegarantias&id=".$remision);
      }
      /* redirect("?modulo=remisiones&accion=show&id=".$remision); */
    }

  function sendmailcotiza($id){
      $this->model->select($id);
      /* if(isset($this->model->responsable)) $send = checkmail($this->model->responsable);
      else $send = "0"; */
      $this->model->sendmailcotiza($id);
      redirect("?modulo=cotizaciones&accion=index&id=".$_GET['id']);
  }
  function aprobargerencia(){
    $id = $_GET['id'];
    $almacen = busca($id, 'remisiones', 'r_id', 'r_almacen');
    $sql = 'SELECT COUNT(*) cantidad, rc_articulo, rc_modelo, a_nmb FROM remisionesc INNER JOIN articulos ON a_id = rc_articulo WHERE rc_remision = "'.$id.'" AND a_inventariado = "1" GROUP BY rc_articulo, rc_modelo';
    $result = setq($sql);
    while($row = $result ->fetch_array()){
      $existencia = existenciaModelo($row['rc_articulo'], $almacen, $row['rc_modelo']);
      $apartados = busca($row['rc_modelo'], 'remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_tipo = "R"
       AND cx_estatus = "N" AND r_estatus != "C" AND rc_articulo = "'.$row['rc_articulo'].'" AND rc_remision != "'.$id.'" 
      AND (SELECT COUNT(*) FROM ingresos INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso WHERE ic_cxcobrar = cx_id AND i_estatus = "P") > 0 
      AND rc_modelo', 'COUNT(*)');
      //echo busca2($row['rc_modelo'], 'remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_tipo = "R" AND cx_estatus = "N" AND r_estatus != "C" AND rc_articulo = "'.$row['rc_articulo'].'" AND rc_remision != "'.$remision.'" AND rc_modelo', 'COUNT(*)');
      //echo 'cantidad: '.$row['cantidad'].' existencia: '.$existencia.' apartados: '.$apartados.'<br>';
      if($row['cantidad'] > ($existencia-$apartados)){
        $sqlfal = 'SELECT rf_id, rf_cantidad FROM remision_faltantes WHERE rf_articulo = "'.$row['rc_articulo'].'" AND rf_modelo = "'.$row['rc_modelo'].'" AND rf_estatus = "N" AND rf_remision = "'.$id.'"';
          $resulfal = setq($sqlfal);
          list($idfal, $canfal) = $resulfal -> fetch_array();
          if($idfal){
            $cantnue = $row['cantidad'];
            $sqlrem = 'UPDATE remision_faltantes SET rf_cantidad = "'.$cantnue.'" WHERE rf_id = "'.$idfal.'"';
          } else {
            $sqlrem = 'INSERT INTO remision_faltantes SET rf_articulo = "'.$row['rc_articulo'].'",
                                                        rf_modelo = "'.$row['rc_modelo'].'",
                                                        rf_cantidad = "'.$row['cantidad'].'",
                                                        rf_estatus = "N",
                                                        rf_remision = "'.$id.'"
                                                        ';
          }
          setq($sqlrem);
      }
    }

    $sql = 'UPDATE remisiones SET r_estatus = "D" WHERE r_id = "'.$id.'"';

    redirect("?modulo=remisiones&accion=show&id=".$id);
  }
}

  class modelremisiones {
    function result($fini,$ffin,$proveedor,$estatus,$documento, $vendedor){ //filtro
      $bloque = 50;
      $prov = clearvmayus($proveedor);
      $doc = clearvmayus($documento);
      $sqlf = '';
      if($vendedor){
        $sqlf = ' AND r_encargado = "'.$vendedor.'"';
      }
      $sql = 'SELECT * FROM remisiones WHERE 1 = 1 '.$sqlf.'
              AND DATE(r_falta) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      
      $almacen = busca($_SESSION['uid'],'usuarios','u_id','u_almacen');
      //if($grupo == "USER") $sql.=' AND r_cliente IN (SELECT c_id FROM crm_clientes WHERE c_almacen = "'.$almacen.'" )';
      if($documento) $sql.=' AND r_folio LIKE "%'.$doc.'%" ';
      if($proveedor) $sql.=' AND r_cliente IN (SELECT c_id FROM crm_clientes WHERE c_nmb LIKE "%'.$proveedor.'%" OR c_apellidos LIKE "%'.$proveedor.'%" OR c_alias LIKE "%'.$proveedor.'%")';
      if($estatus) {
        if($estatus == "A"){
          $sql.=' AND r_estatus IN ("A", "F", "FE")';
        }else {
          $sql.=' AND r_estatus = "'.$estatus.'"';
        }
        
      }
      $sql.='ORDER BY r_id DESC ';
      $result = setq($sql);
      //if($result->num_rows > $bloque) 
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
      $this->diva = $row['r_diva'];
      $this->motivocan = $row['r_motivocan'];
      $this->precioenvio = $row['r_precioenvio'];
    }
    function setdata($id,$tablero,$folioint,$cliente,$almacen,$descuento,$nmbac,$correo,$responsable,$ffin,$diva, $precioenvio = 0){
      $this->id = $id;
      $this->tablero = $tablero;
      $this->folioint = $folioint;
      $this->cliente = $cliente;
      $this->almacen= $almacen;
      $this->descuento = $descuento;
      $this->nmbac = clearvmayus($nmbac);
      $this->correo = clearvminus($correo);
      $this->responsable = clearvmayus($responsable);
      $this->ffin = clearvmayus($ffin);
      $this->diva = $diva;
      $this->precioenvio = $precioenvio;
    }
    function insert(){
      $sql = 'INSERT INTO remisiones SET
              r_tablero = "'.$this->tablero.'",
              r_cliente = "'.$this->cliente.'",
              r_folio = "'.$this->folioint.'",
              r_almacen = "'.$this->almacen.'",
              r_descuento = "'.$this->descuento.'",
              r_diva = "'.$this->diva.'",
              r_nmb = "'.$this->nmbac.'",
              r_encargado = "'.$this->responsable.'",
              r_email = "'.$this->correo.'",
              r_fliquidacion = "'.$this->ffin.'",
              r_falta = "'.date('Y-m-d H:i:s').'",
              r_ualta = "'.$_SESSION['uid'].'",
              r_precioenvio = "'.$this->precioenvio.'",
              r_estatus	 = "N"';
      setq($sql);

      $this->id = getmax('r_id','remisiones',false,false);
    }
    function update(){
      $sql = 'UPDATE remisiones SET
              r_almacen = "'.$this->almacen.'",
              r_descuento = "'.$this->descuento.'",
              r_nmb = "'.$this->nmbac.'",
              r_encargado = "'.$this->responsable.'",
              r_diva = "'.$this->diva.'",
              r_email = "'.$this->correo.'",
              r_fliquidacion = "'.$this->ffin.'"
              WHERE r_id = "'.$this->id.'"';
      setq($sql);
    }

    function accionprod ($id, $estatus){
      $sql = 'UPDATE garantias_articulos SET
              ga_estatus = "'.$estatus.'"
              WHERE ga_id = "'.$id.'"';
      setq($sql);
    }

    function accionprod2 ($id, $estatus){

      if($_POST['tipo'] == "D"){
        $paqueteria = $_POST['paqueteriaDom'];
        $sucursal = 0;
      } else if($_POST['tipo'] == "O"){
        $paqueteria = $_POST['paqueteriaDom'];
        $sucursal = $_POST['sucursalOcurre'];
      } else{
        //Recoge cliente
        $paqueteria = 0;
        $sucursal = 0;
      }
      $fenvio = $_POST['fechaEnvio'];

      $sql = 'UPDATE garantias_articulos SET
              ga_estatus = "'.$estatus.'", ga_tipoenvio = "'.$_POST['tipo'].'", 
              ga_sucursal = "'.$sucursal.'", ga_paqueteria = "'.$paqueteria.'", ga_fenvio = "'.$fenvio.'"
              WHERE ga_id = "'.$id.'"';
      setq($sql);
    }

    function comprueba($id,$almacen,$articulo = NULL) {
      $ok = "OK";

      if($ok == "OK"){
        $sql = 'SELECT count(*) FROM remisionesd WHERE rd_remision = "'.$id .'"';
        $result = setq($sql);
        list($cuenta) = $result->fetch_array();
        if ($cuenta >= 1) $ok = "OK";
        else $ok = 0;
      }

      return($ok);
    }

    function insertgarantia($articulo, $modelo, $descripcion, $rcid, $remision, $direccion, $cliente, $precio, $tcambio, $preciodolares){
    $respuesta = 0;
    $ruta = 'img/garantias/';

    if($preciodolares == 0){
      $sqlf = 'ga_preciodolares = NULL, ';
    } else{
      $sqlf = 'ga_preciodolares = "'.$preciodolares.'", ';
    }

    if(!isset($_GET['save'])){
      if(!empty($rcid)){
        $query = 'INSERT INTO garantias_articulos SET
                ga_articulo = "'.$articulo.'",
                ga_modelo = "' . $modelo . '",
                ga_fini = "' . date("Y-m-d H:i:s") . '",
                ga_direccion = "'.$direccion.'",
                ga_cliente = "'.$cliente.'",
                ga_descripcion = "'.$descripcion.'",
                ga_estatus = "A",
                ga_rcid = "'.$rcid.'",
                ga_precio = "'.$precio.'",'.$sqlf.'
                ga_tcambio = "'.$tcambio.'",
                ga_remision = "'.$remision.'"
                ';
      } else{
        $query = 'INSERT INTO garantias_articulos SET
                ga_articulo = "'.$articulo.'",
                ga_modelo = "' . $modelo . '",
                ga_fini = "' . date("Y-m-d H:i:s") . '",
                ga_direccion = "'.$direccion.'",
                ga_cliente = "'.$cliente.'",
                ga_descripcion = "'.$descripcion.'",
                ga_estatus = "A",
                ga_rcid = NULL,
                ga_precio = "'.$precio.'",'.$sqlf.'
                ga_tcambio = "'.$tcambio.'",
                ga_remision = "'.$remision.'"';
      }
      setq($query);
      $garantia = getmax('ga_id', 'garantias_articulos', false, false);   
    } else{
      $garantia = $rcid;
    }


    if ($_FILES['archivos']['name']) {  
      /* die("Entra 1 G:".$garantia." y ".$descripcion); */
      for ($i = 0; $i <= count($_FILES['archivos']['name']); $i++) {
        //Recogemos el archivos enviado por el formulario
        $archivos = $_FILES['archivos']['name'][$i];
        //echo $archivos."\n";
        /* $max = getmax('gi_orden', 'garantias_imagenes', 'gi_garantia = "' . $garantia . '"'); */
        $extension = pathinfo($archivos, PATHINFO_EXTENSION);
        $nombre = pathinfo($archivos, PATHINFO_FILENAME);
        $nuevo_nombre = $nombre;
        //Si el archivos contiene algo y es diferente de vacio
        if (isset($archivos) && $archivos != "") {
          //Obtenemos algunos datos necesarios sobre el archivos
          $tipo = $_FILES['archivos']['type'][$i];
          $tamano = $_FILES['archivos']['size'][$i];
          $temp = $_FILES['archivos']['tmp_name'][$i];

          //list($archivosw, $archivosh, $tipox, $atributos) = getimagesize($_FILES['archivos']['tmp_name']); 2000000
          //Se comprueba si el archivos a cargar es correcto observando su extensión y tamaño
          //die($tipo);
          if (!($tamano < (300000000))) {
                $respuesta = 2;  //Error. La extensión o el tamaño de los pdf no es correcta. Se permiten .gif, .jpg, .png. y de 200 kb como máximo
          } else {
            $fechaHora = date("Y-m-d H:i:s"); // Obtiene la fecha y hora actual en el formato predeterminado
            $fechaHoraSinGuionesNiPuntos = str_replace(array("-", ":", " "), "", $fechaHora);

            //Si la archivos es correcta en tamaño y tipo
            //Se intenta subir al servidor
            $destination_path = getcwd() . DIRECTORY_SEPARATOR;
            $target_path = $destination_path . $ruta . $nuevo_nombre . $fechaHoraSinGuionesNiPuntos . '.' . $extension;
            //echo $temp." , path: ".$target_path;
            if (move_uploaded_file($temp, $target_path)) {
              //Cambiamos los permisos del archivos a 777 para poder modificarlo posteriormente
              chmod($target_path, 0777);
              //Mostramos el mensaje de que se ha subido co éxito
              //die ('<div><b>Se ha subido correctamente la imagen.</b></div>');
              //Mostramos la imagen subida
              //die ('<p><img src="img/imagens/'.$imagen.'"></p>');
              //$ruta = $rutatipo.$nuevo_nombre;

              
                $sql = 'INSERT INTO garantias_imagenes SET
                        gi_garantia = "'.$garantia.'",
                        gi_nmb = "' . $nuevo_nombre  . $fechaHoraSinGuionesNiPuntos . '",
                        gi_ext = "' . $extension . '",
                        gi_tipo = "G"';
                setq($sql);
            } else {
              //Si no se ha podido subir la imagen, mostramos un mensaje de error
              
              $respuesta = 1;  //Ocurrió algún error al subir los archivos. No pudo guardarse.
            }
          }
        }
      }
    } else{
      $respuesta = 3;
    }
    return $garantia;
    }

	function creargarantiaCXC($cliente, $referencia, $importe, $folio, $articulo){
      $idcxc = intval(busca($referencia, 'cxcobrar', 'cx_tipo = "B" AND cx_referencia', 'cx_id'));
      if(!empty($idcxc)){
        $cxc = $idcxc;
      } else{
      $sql = 'INSERT INTO cxcobrar SET
              cx_cliente = "'.$cliente.'",
              cx_referencia = "'.$referencia.'",
              cx_tipo = "B",
              cx_importe = "'.$importe.'",
              cx_observaciones = "PRECIÓ DE REPARACIÓN DEL ARTÍCULO '.$articulo.' DE LA REMISIÓN '.$folio.'",
              cx_abonado = "0",
              cx_estatus = "N",
              cx_fini = "'.date('Y-m-d H:i:s').'"';
      setq($sql);
      $cxc = getmax('cx_id', 'cxcobrar', false, false);
      }

      return $cxc;
    }

    function crearingreso(){
    /*   $cuent = new modelcuentas();
      $corte = $cuent->cuentaactiva($_POST['cuenta']);
      $sql='INSERT INTO ingresos SET
      i_monto="'.$monto.'",
      i_cliente="'.$cliente.'",
      i_referencia="'.$referencia.'",
      i_cuenta="'.$cuenta.'",
      i_fecha="'.$fecha.'",
      i_fgen="'.$fgen.'",
      i_ugen="'.$_SESSION['eid'].'",
      i_corte="'.$corte.'",
      i_estatus="'.$this->estatus.'",
      i_adjunto="'.$this->adjunto.'",
      i_fpago="'.$this->fpago.'",
      i_comision="'.$this->comision.'",
      i_observaciones="'.$this->observaciones.'"';
      setq($sql);
      $this->iding = getmax('i_id','ingresos',false,false); */
    }

    function crearRemision($tablero, $cliente, $folioint, $almacen, $nmbac, $responsable, $correo, $precioenvio, $observaciones){

      $sql = 'INSERT INTO remisiones SET
              r_tablero = "'.$tablero.'",
              r_cliente = "'.$cliente.'",
              r_folio = "'.$folioint.'",
              r_almacen = "'.$almacen.'",
              r_nmb = "'.$nmbac.'",
              r_encargado = "'.$responsable.'",
              r_ualta = "'.$_SESSION['uid'].'",
              r_email = "'.$correo.'",
              r_total = "'.$precioenvio.'",
              r_subtotal = "'.$precioenvio.'",
              r_observaciones = "'.$observaciones.'",
              r_falta = "'.date('Y-m-d H:i:s').'",
              r_diva = "0",
              r_estatus	 = "A"';
      setq($sql);
  
      return getmax('r_id','remisiones',false,false);
    }

    function crearCXC($cliente, $idnuevaremision, $precioenvio, $folioint){
      $sql = 'INSERT INTO cxcobrar SET
              cx_cliente = "'.$cliente.'",
              cx_referencia = "'.$idnuevaremision.'",
              cx_tipo = "R",
              cx_importe = "'.$precioenvio.'",
              cx_observaciones = "COSTO DE ENVÍO DE LA REMISION '.$folioint.'",
              cx_abonado = "0",
              cx_estatus = "N",
              cx_fini = "'.date("Y-m-d").'"';
      setq($sql);
    }
    function insertRemisionD($idnuevaremision, $nmbac, $cantidad, $precioenvio){
        $sql = 'INSERT INTO remisionesd SET
          rd_remision = "'.$idnuevaremision.'",
          rd_articulo = "0",
          rd_nmbarticulo = "'.$nmbac.'",
          rd_cantidad = "'.$cantidad.'",
          rd_precio = "'.$precioenvio.'",
          rd_costo = "0.00",
          r_descuento = "0.00",
          rd_iva = "0"';
        setq($sql);
    }

    function  sonTodosIgualesA($arreglo, $valor){
      $todosSonC = true;

      foreach ($arreglo as $elemento) {
        if ($elemento !== $valor) {
            $todosSonC = false; // Si encontramos un elemento que no pertenece, establecemos la bandera a false
            break; // Salimos del bucle
        }
    }
    return $todosSonC;
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
      $this->cantidadd = $row['rd_cantidad'];
      $this->preciod = $row['rd_precio'];
      $this->costod = $row['rd_costo'];
      $this->descuentod = $row['rd_descuento'];
      $this->ivad = $row['rd_iva'];
      $this->tipod = $row['rd_tipo'];
      $this->existenciaantd = $row['rd_existenciaant'];
    }
    function resultd($id){
      $sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$id.'" ORDER BY rd_id';
      $this->resultr = setq($sql);
    }

    function resultg($id){ //filtro
      $sql = 'SELECT * FROM remisionesc WHERE rc_remision = "'.$id.'" AND rc_estatus = "F" AND rc_ligado IS NULL';
      $this->resultg = setq($sql);
    }

    function results($remision){ //filtro
      $sql = 'SELECT * FROM garantias_articulos WHERE ga_remision = "'.$remision.'" ORDER BY ga_fini DESC';
      $this->results = setq($sql);
    } 

    function setDatad($recepcion, $idrec, $cantidad, $articulo,$importe,$iva,$costo,$nmbarticulo, $modelo){
      $this->recepcion = $recepcion;
      $this->idrec = $idrec;
      $this->cantidad = $cantidad;
      $this->articulo = $articulo;
      $this->modelo = $modelo;
      $this->importe = $importe;
      $this->iva = $iva;
      $this->costo = $costo;
      $this->nmbarticulo = clearvmayus($nmbarticulo);
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
    function delprod($remision,$idprod,$ex){
      if($ex) $sql = 'DELETE FROM remisionesd WHERE rd_remision = "'.$remision.'" AND rd_articulo = "'.$idprod.'"';
      else {
        $sql = 'DELETE FROM remisionesd WHERE rd_remision = "'.$remision.'" AND rd_id = "'.$idprod.'"';

        $sql1 = 'DELETE FROM remision_activo WHERE ra_remision = "'.$remision.'" AND ra_idr = "'.$idprod.'"';
        setq($sql1);
      }
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
      if($okex == "OK"){
        $almacen = busca($id, 'remisiones', 'r_id', 'r_almacen');
        $sqldet = 'SELECT * FROM remisionesc INNER JOIN articulos ON a_id = rc_articulo WHERE rc_remision = "'.$id.'" AND a_inventariado = "1"';
        $resultdet = setq($sqldet);
        while($rowdet = $resultdet -> fetch_array()){
          ajustaexistencia($rowdet['rc_articulo'], 1, $almacen, "S", "", $rowdet['rc_modelo'], $id, "R");
        }
      }
      return($okex);
    }
    function calculaimporte($cotizacion, $updcxc = NULL){
      $viva = busca(1,'configuracionesp','c_id','c_iva')/100;
      $pordes = busca($cotizacion,'remisiones','r_id','r_descuento');
      $diva = busca($cotizacion,'remisiones','r_id','r_diva');
      $envio = busca($cotizacion,'remisiones','r_id','r_precioenvio');
    
      $total = 0;
      $descuento = 0;
      $totiva = 0;
      $subtotal = 0;
      $iva = 0;
      $descuentot = 0;
      $subtotalfin = 0;
    
      $sql = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$cotizacion.'"';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $desc = busca($row['rd_articulo'], 'articulos', 'a_id', 'a_descuento');
        $precio = $row['rd_precio'];
        $importe = $precio*$row['rd_cantidad'];
        //echo busca2($row['rd_articulo'], 'articulos', 'a_id', 'a_descuento').' desc: '.$desc.'<br>';
        if($desc == "1"){
          $descuni = $importe*($pordes/100);
          $impmd = $importe-$descuni;
          $subtotal+=$impmd;
          $descuentot+=$descuni;
          //echo "descuento: ".$descuentot.'<br>';
        } else{
          $subtotal += $importe;
        }
        
        if($diva == "1"){
          if($row['rd_iva'] == "1"){  
            $iva=$impmd*($viva);
          }
          elseif($row['rd_iva'] == "0"){
            $iva= 0;
          }
        }else{
          $iva= 0;
        }
    
        
        $subtotalfin+= $importe;
        //echo "importe: ".$subtotalfin.'<br>';
        $totiva+=$iva;
      }
      //echo "subtotal: ".$subtotal." totiva: ".$totiva." envio: ".$envio.'<br>';
      $total = round($subtotal)+$totiva+$envio;
      $sql = 'UPDATE remisiones SET
              r_subtotal = "'.$subtotalfin.'",
              r_iva = "'.$totiva.'",
              r_mdescuento = "'.round($descuentot).'",
              r_total = "'.$total.'"
              WHERE r_id = "'.$cotizacion.'"';
      setq($sql);

      if($updcxc){
        $cxc = busca($cotizacion, 'cxcobrar', 'cx_tipo = "R" AND cx_referencia', 'cx_id');
        if($cxc){
          $sqlupdcxc = 'UPDATE cxcobrar SET cx_importe = "'.$total.'" WHERE cx_id = "'.$cxc.'"';
          setq($sqlupdcxc);
        }
      } 
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
    function cancelar($remision,$motivo, $vencida = NULL){
      $sql = 'SELECT r_almacen,r_estatus FROM remisiones WHERE r_id = "'.$remision.'"';
      $result = setq($sql);
      list($almacen,$estatus) = $result->fetch_array();

      $sql = 'UPDATE remisiones SET
              r_fcan = "'.date('Y-m-d H:i:s').'",
              r_ucan = "'.$_SESSION['uid'].'",
              r_estatus = "C",
              r_motivocan = "'.$motivo.'",
              r_vencida = "'.$vencida.'"
              WHERE r_id = "'.$remision.'"';
      $actualizacionExitosa = setq($sql);

      $sqldel = 'DELETE FROM remision_faltantes WHERE rf_remision = "'.$remision.'"';
      setq($sqldel);

      if ($actualizacionExitosa) {
      echo '
      <script>
        $.ajax({
          url: "task/notificacionpush.php",
          method: "POST",
          data: { "remision": "'.$remision.'",
                  "grupo": "FINANZAS",
                  "estatus": "C"},
        }).done(function(data){
        });          
        </script>
      ';

      /* echo '
        <script>
        $.ajax({
          url: "query/correocotizaciones.php",
          method: "POST",
          data: {"remision": "'.$remision.'",
                  "correoop": "6"
                },
        }).done(function(data){
        });          
        </script>'; */
      }

      if($estatus == "A" || $estatus == "F"){
        $sql = 'SELECT rc_articulo, rc_modelo, COUNT(*) AS cantidad, r_almacen FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN articulos ON rc_articulo = a_id WHERE rc_remision = "'.$remision.'" AND rc_estatus != "F" GROUP BY rc_articulo, rc_modelo';
        $result1 = setq($sql);
        while ($rowe = $result1->fetch_array()) {
          ajustaexistencia($rowe['rc_articulo'], $rowe['cantidad'], $rowe['r_almacen'], "E", '', $rowe['rc_modelo']);
        }

        $sqlfal = 'SELECT * FROM remision_faltantes WHERE rf_remision = "'.$remision.'"';
        $resultfal = setq($sqlfal);
        if($resultfal -> num_rows > 0)
        while($rowfal = $resultfal -> fetch_array()){
          $cant = busca($rowfal['rf_articulo'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_modelo = "'.$rowfal['rf_modelo'].'" AND xp_articulo', 'xp_cantidad');
          if($cant){
            $newcant = $cant - $rowfal['rf_cantidad'];
            $idxp = busca($rowfal['rf_articulo'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_modelo = "'.$rowfal['rf_modelo'].'" AND xp_articulo', 'xp_id');
            if($idxp){
              $updxp = 'UPDATE existencia_pendiente SET xp_cantidad = "'.$newcant.'" WHERE xp_id = "'.$idxp.'"';
              setq($updxp);
            }
          }
        }
        
        $updremf = 'UPDATE remision_faltantes SET rf_estatus = "F" WHERE rf_remision = "'.$remision.'"';
        setq($updremf);
      }
    }
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

    function sendmailcotiza($cotiza){
      $this->select($cotiza);
      include_once('formats/pdfcotizacion_save.php');
      require_once('lib/mailer/class.phpmailer.php');
      require_once("lib/mailer/class.smtp.php");
      $mail = new PHPMailer(); // defaults to using php "mail()"
      $asunto = 'COTIZACIÓN '.$this->folio;
      $nombre = $this->destino;  
      /*
        $puestoi = $this->responsable;
        $correoi = busca($this->responsable,'usuarios','u_id','u_mailcorp');
        $telefonoi = busca($this->responsable,'usuarios','u_id','u_telefono'); 
      */
      $puestoi = $this->responsable;
      $correoi = busca($this->responsable,'usuarios','u_id','u_mailcorp');
      $telefonoi = busca($this->responsable,'usuarios','u_id','u_telefono');
      $password = base64_decode(busca($this->responsable,'usuarios','u_id','u_contraseñacorp'));
      $host = busca($this->responsable,'usuarios','u_id','u_host');
      $seguridad = busca($this->responsable,'usuarios','u_id','u_seguridad');
      $puerto = busca($this->responsable,'usuarios','u_id','u_puerto');
      $remitente = busca($this->responsable,'usuarios','u_id','u_remitente');
      $dominio = explode('@',$correoi);

      $mail->Host = $host;
      $mail->Port = $puerto;
      $mail->IsSMTP();
      if($seguridad != 0) $mail->SMTPSecure = $seguridad;
      //$mail->SMTPDebug = 2;
      
      $mail->SMTPAuth = true;
      $mail->Username = $correoi;
      $mail->Password = $password;

      if(busca(busca($this->tablero,'crm_tableros','ct_id','ct_cliente'),'clientes','c_idc','COUNT(*)') == 0)
        $mail->AddAttachment('docs/cotizaciones/'.$this->folio.'.pdf');

      $adjunto = busca($this->id,'crm_cotizaciones','cc_id','cc_adjunto');
      if($adjunto && !empty($adjunto)) $mail->AddAttachment("docs/adjcot/".$_SESSION['emp']."/".$adjunto);
      $this->select($_GET['idcotiza']);
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
              display: inline-block; /* Or inline-block */
              margin-right: 30px;
              vertical-align: top; /* here */
              margin-top: 50px;
            }
          </style>';
      /*
        $logo = busca($_SESSION['emp'],'empresas','e_id','e_logo');
        if(file_exists($logo)){
          $body.='<center><img src="'.$logo.'"  width="160" height="130" /></center>';
        }
      */
      include_once('modulos/clientes.php');
      $client = new modelclientes();
      $client->select(busca($this->tablero,'crm_tableros','ct_id','ct_cliente'));

      include_once('modulos/config.php');
      $empres = new modelconfig();
      $empres->select($_SESSION['emp']);

      if($this->model->direnvio)
        $direnvio = busca($this->model->direnvio,'crm_direcciones INNER JOIN estados ON cd_estado = e_id','cd_id','CONCAT(cd_calle," ",cd_nume," ",cd_numi," ",cd_colonia," ",cd_municipio," ",e_id," ",cd_cp)');
      else $direnvio = "";
      /* $encabezado = '<div class="cabeza"><b>Cotización: </b>'.$this->folio.'</div>';
      $bodycliente = '<hr><div class="cliente" contenteditable="true">
                          <div class="fif">
                            <div><b>Cliente: </b>'.$client->nmb.' '.$client->apellidos.'</div>
                            <div><b>Teléfono(s) </b>'.$client->telefono1.' - '.$client->telefono2.'</div>
                            <div><b>Correo(s) </b>'.$client->correo1.' - '.$client->correo2.'</div>
                          </div>
                          <div class="fif">
                              <div><b>Agente: </b>'.$this->nmbagente.'  </div>
                          </div>
                          <div class="fif">
                              <div><b>Fecha envío: </b>'.date('d/m/Y H:i:s').'</div>
                              <div><b>Vigencia </b>'.fecha_formato($this->ffin,false,true).'</div>
                              '.$direnvio.'
                            </div>
                        </div>'; */
      $encabezado = '<div class="cabeza"><b>Cotización: </b>'.$this->folio.'</div>';
      $bodycliente = '<hr><div class="cliente" contenteditable="true">
                          <div class="fif">
                            <div><b>Cliente: </b>'.$this->destino.'</div>
                            <div><b>Teléfono(s) </b>'.$this->teldestino.'</div>
                            <div><b>Correo(s) </b>'.$this->maildestino.'</div>
                          </div>
                          <div class="fif">
                              <div><b>Agente: </b>'.$this->nmbagente.'  </div>
                          </div>
                          <div class="fif">
                              <div><b>Fecha envío: </b>'.date('d/m/Y H:i:s').'</div>
                              <div><b>Vigencia </b>'.fecha_formato($this->ffin,false,true).'</div>
                              '.$direnvio.'
                            </div>
                        </div>';
      $bodyproveedor = '<hr>';
      $body.=utf8_decode($encabezado);
      $body.=utf8_decode($bodycliente);
      $body.=utf8_decode($bodyproveedor);
      $body.= utf8_decode(nl2br($this->mensaje));

      $mail->setFrom($correoi, utf8_decode($this->nmbagente));
      $mail->AddAddress($this->correo, utf8_decode($nombre));

      if($this->copiamail){
        $correos = explode(',',$this->copiamail);
        foreach($correos as $copias){
          $mail->addCC($copias);
        }
      }

      $mail->ConfirmReadingTo = $correoi;
      $mail->Subject = utf8_decode($asunto);

      if($this->tablaprod == "1" || $this->tabla == "1"){
        $table = "";
        $sqltp = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$this->id.'" ORDER BY cdm_id ASC';
        $result = setq($sqltp);
        if($result->num_rows > 0){
          $table.='<table widht="100%" class="tablecot" style="margin-top:5px;">
                      <thead><tr><td colspan="5" class="title-table" >Servicios cotizados</td></tr>
                      <tr>
                      <th width="10%" class="title-table">Cantidad</th>
                      <th width="12%" class="title-table">Modelo</th>
                      <th width="50%" class="title-table">Descripcion</th>
                      <th width="13%" class="title-table">Precio unitario</th>
                      <th width="15%" class="title-table">Importe</th>
                    </tr><thead>';
          $result = setq($sqltp);
          $tasaiva = busca(1,'configuracionesp','c_id','c_iva')/100;
          $totiva = 0;
          while($row = $result->fetch_array()){
            $precio = $row['cdm_precio'];
            $importe = $row['cdm_cantidad']*$precio;

            $table.='<tr>
                  <td>'.$row['cdm_cantidad'].'</th>
                  <td>'.busca($row['cdm_articulo'],'articulos','a_id','a_modelo').'</th>
                  <td>'.$row['cdm_nmbarticulo'].'</th>
                  <td class="number-import">'.number_format($precio,2).'</th>
                  <td class="number-import">'.number_format($importe,2).'</th>
                </tr>';
          }
          if($this->mtotal == "1"){
            if($this->diva == 1 || $this->descuento > 0)
              $table.='<tr>
                    <td colspan="3">&nbsp;</th>
                    <th>Subtotal</th>
                    <th class="number-import">'.number_format($this->subtotal,2).'</th>
                  </tr>';

              if($this->descuento > 0){
                $impdesc = ($this->subtotal*($cotiza->descuento/100));
                $table.='<tr>
                      <td colspan="3">&nbsp;</th>
                      <th>Descuento</th>
                      <th class="number-import">'.number_format(round($impdesc),2).'</th>
                    </tr>';
              }
              if($this->diva == 1)
                $table.='<tr>
                      <td colspan="3">&nbsp;</th>
                      <th>IVA</th>
                      <th class="number-import">'.number_format($this->iva,2).'</th>
                    </tr>
                    <tr>
                      <td colspan="3">&nbsp;</th>
                      <th>Total</th>
                      <th class="number-import">'.number_format($this->importe,2).'</th>
                    </tr>';
              else{
                $table.='
                    <tr>
                      <td colspan="3">&nbsp;</th>
                      <th>Total</th>
                      <th class="number-import">'.number_format($this->importe,2).'</th>
                    </tr>';
              }
            }
          }
        $table.='</table>';
        $body.=utf8_decode($table);
      }
      if($this->cuentas == "1"){
        $sql = 'SELECT * FROM cuentas WHERE cu_empresa = "'.$_SESSION['emp'].'" AND cu_enviacotiza = "1" ORDER BY cu_orden ASC';
        $result = setq($sql);
        if($result->num_rows > 0){
          $result = setq($sql);
          $cuentas = '<hr>
                      <div class="cabeza"><b>Nuestras Cuentas Bancarias</b></div>
                      <div class="cliente" contenteditable="true">';
          while($row = $result->fetch_array()){
            $cuentas.='   <div class="fif">
                          <div><b>Banco: </b>'.$row['cu_banco'].'</div>
                          <div><b>Cuenta: </b>'.$row['cu_cuenta'].'</div>
                          <div><b>CLABE </b>'.$row['cu_clabe'].'</div>';
            if($row['cu_numtarjeta'] && $row['cu_numtarjeta'] != "0")
              $cuentas.='<div><b>Tarjeta </b>'.$row['cu_numtarjeta'].'</div>';
            $cuentas.='</div><hr>';
          }
          $cuentas.='</div>';
        }
      }

      $body.=utf8_decode($cuentas);
      $body.=utf8_decode('<hr>Mensaje generado automáticamente por JD CEO 2.0');
      $mail->MsgHTML($body);
      //envío el mensaje, comprobando si se envió correctamente
      if(!$mail->send())
        unlink('docs/cotizaciones/'.$this->folio.'.pdf');
    }

    function setprodpordefinir($idremision) 
    {
      $contador = 0;
      $contador2 = 0;
      $contador3 = 0;
      $estatusG = array();
      $articulosmodelos = array();
      
      $estatusr = busca($idremision, 'remisiones', 'r_id', 'r_estatus');
      $query = 'SELECT rc_id, rc_modelo, rc_articulo, rc_remisiond FROM remisionesc WHERE rc_remision = "' . $idremision . '"';
      $result = setq($query);
      while ($row = $result->fetch_array()) {
        if (isset($_POST['select' . $row['rc_id']])) {
          if($_POST['tipoenvio' . $row['rc_id']] == "C"){
            $paqueteria = 0;
            $sucursal = 0;
            $estatus = "P";
            $tenvio = "C";  
            $contador3++;        
            array_push($estatusG, $tenvio);
          } else if($_POST['tipoenvio' . $row['rc_id']] == "O"){
            $paqueteria = $_POST['paqueteria' . $row['rc_id']];
            $sucursal = $_POST['sucursal' . $row['rc_id']];
            $estatus = "N";
            $tenvio = "O";
            $contador2++;
            array_push($estatusG, $tenvio);
          } else{
            $paqueteria = $_POST['tipoenvio' . $row['rc_id']];
            $sucursal = 0;
            $estatus = "N";
            $tenvio = "D";
            array_push($estatusG, $tenvio);
            $contador2++;
          }

          if($estatusr == "F"){
            if($_POST['tipoenvio' . $row['rc_id']] != "C"){
              $estatus = "A";
            }
          }
  
          /* die("Paqueteria: ".$paqueteria.", sucursal: ".$sucursal." y estatus: ".$tenvio); */
  
  
          $sql = 'UPDATE remisionesc SET 
                  rc_estatus = "'.$estatus.'", 
                  rc_tipoenvio = "'.$tenvio.'", 
                  rc_paqueteria = "'.$paqueteria.'", 
                  rc_sucursal = "'.$sucursal.'",
                  rc_observacion = "'.$_POST['obs' . $row['rc_id']].'"
                  WHERE rc_id = "' . $row['rc_id'] . '"';
          setq($sql);

          $sql2 = 'UPDATE remisionesc SET 
                  rc_estatus = "'.$estatus.'", 
                  rc_tipoenvio = "'.$tenvio.'", 
                  rc_paqueteria = "'.$paqueteria.'", 
                  rc_sucursal = "'.$sucursal.'",
                  rc_observacion = "'.$_POST['obs' . $row['ccc_id']].'"
                  WHERE rc_ligado = "' . $row['rc_articulo'] . '" AND rc_remision = "'.$idremision.'" AND rc_remisiond = "'.$row['rc_remisiond'].'"';
          setq($sql2);
          $contador++;
        }
      }
  
      $response = $this->sonTodosIgualesA($estatusG, "C");
      if(!$response){
      
      //Creamos la remision de esta ccotización
      $acrof = busca(1,'empresas','e_id','e_siglas').'-RM';
      $folioint = getmax('r_folio','remisiones');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }
  
      $sqlr = 'SELECT * FROM remisiones WHERE r_id = "'.$idremision.'"';
      $resultr = setq($sqlr);
      $rowr = $resultr->fetch_array();
      $tablero = $rowr['r_tablero'];
      //Abrimos el tablero si está finalizado
      $estatustab = busca($tablero, 'crm_tableros', 'ct_id', "ct_estatus");
  
      if($estatustab == "F"){
        $sqltab = 'UPDATE crm_tableros SET ct_estatus = "A"  WHERE ct_id = "'.$tablero.'"';
        setq($sqltab);
      }
  
      $cliente = $rowr['r_cliente'];
      $almacen = $rowr['r_almacen'];
      $nmbac = 'COSTO DE ENVÍO EXTRA DE REMISIÓN '.$rowr['r_folio'];
      $responsable = $rowr['r_encargado'];
  
      $correo = $rowr['r_email'];
      $precioenvio = floatval($_POST['precio']);
      $observaciones = $_POST['comentario'];
      
      if($precioenvio > 0){
        /* $idnuevaremision = $this->crearRemision($tablero, $cliente, $folioint, $almacen, $nmbac, $responsable, $correo, $precioenvio, $observaciones);
        $this->crearCXC($cliente, $idnuevaremision, $precioenvio, $folioint);
        $this->insertRemisionD($idnuevaremision, $nmbac, "1", $precioenvio); */
        $sqlcxc = 'SELECT cx_id, cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$idremision.'" AND cx_tipo = "R"';
        $resultcxc = setq($sqlcxc);
        list($cxid, $importe, $abonado, $estatuscxc) = $resultcxc -> fetch_array();
        $sqlr = 'SELECT r_total, r_precioenvio, r_estatus FROM remisiones WHERE r_id = "'.$idremision.'"';
        $resultr = setq($sqlr);
        list($total, $envio, $estatusr) = $resultr -> fetch_array();
        $newtotal = $total + $_POST['precio'];
        $newenvio = $envio + $_POST['precio'];
        $newestatus = "A";
        $sqlupdr = 'UPDATE remisiones SET r_total = "'.$newtotal.'", r_precioenvio = "'.$newenvio.'", r_estatus = "'.$newestatus.'" WHERE r_id = "'.$idremision.'"';
        setq($sqlupdr);
        $newimporte= $importe + $_POST['precio'];
        if($abonado == $newimporte){
          $newestcxc = "F"; 
        } else {
          $newestcxc = "A";
        }
        $sqlupdcxc = 'UPDATE cxcobrar SET cx_importe = "'.$newimporte.'", cx_estatus = "'.$newestcxc.'" WHERE cx_id = "'.$cxid.'"';
        setq($sqlupdcxc);
      }
    } 
  
    $formaenvios = array();  
    $formaenvios[0] = $contador2;
    $formaenvios[1] = $contador3;
    return $formaenvios;
  }
  function checardisponibles($id){
    $datos = array();
    //$faltantes = array();
    $datos['error'] = 0; 
    $i = 0;
    $exist = 0;
    $cotizacion = busca($id, 'crm_cotizaciones', 'cc_remision', 'cc_id');
    $almacen = busca($id, 'remisiones', 'r_id', 'r_almacen');
    $sql = 'SELECT * FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo
            WHERE cdm_cotizacion = "'.$cotizacion.'" ORDER BY cdm_id ASC';
    $resultcd = setq($sql);
    while($row = $resultcd->fetch_array()){
      $tipoprod = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_tipoprod');
      if($tipoprod != "M"){
        if($row['a_inventariado'] == "1"){
            if($row['cdm_modelo'] == ""){
            $exist = existencia($row['cdm_articulo'], $almacen );
          }else{
            $exist = existenciaModelo($row['cdm_articulo'], $almacen,$row['cdm_modelo']);
          }
          if($exist < $row['cdm_cantidad']){
            $datos['error'] = 1; //No hay existencia
            $nmb = $row['a_nmb'];
            if($row['cdm_modelo'] != "")  $nmb .= ' '.busca($row['cdm_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['cdm_modelo'].'" AND av_articulo', 'av_nmb');
            $datos['articulos'][$i] = number_format($row['cdm_cantidad'], 0).' - '.$nmb; 
            $i++;
          }
        }
        $sqlal = 'SELECT * FROM articulos_ligados INNER JOIN articulos ON a_id = al_aligado WHERE al_articulo = "'.$row['cdm_articulo'].'"';
        $resultal = setq($sqlal);
        while($rowal = $resultal -> fetch_array()){
          if($rowal['a_inventariado'] == "1"){
            if($rowal['al_amodelo'] != "") $exist = existenciaModelo( $rowal['al_aligado'], $almacen, $rowal['al_amodelo']);
            else $exist = existencia($rowal['al_aligado'], $almacen);
            if($exist < $row['cdm_cantidad']*$rowal['al_cantidad']) {    
              $datos['error'] = 1; //No hay existencia
              $nmb = $rowal['a_nmb'];
              if($rowal['al_amodelo'] != "")  $nmb .= ' '.busca( $rowal['al_aligado'], 'articulos_variantes', 'av_modelo = "'. $rowal['al_amodelo'].'" AND av_articulo', 'av_nmb');
              $datos['articulos'][$i] = number_format($row['cdm_cantidad']*$rowal['al_cantidad'], 0).' - '.$nmb; 
              $i++;
            }
          }
        }
      } else {
        $sqlvar = 'SELECT * FROM crm_cotizacion_variantes INNER JOIN articulos ON ccv_articulo = a_id WHERE ccv_cotizacion = "'.$cotizacion.'" AND ccv_cotizaciond = "'.$row['cdm_id'].'"';
        $resultvar = setq($sqlvar);
        while($rowvar = $resultvar -> fetch_array()){
          if($row['a_inventariado'] == "1"){
            if($rowvar['ccv_modelo'] != "") $exist = existenciaModelo($rowvar['ccv_articulo'], $almacen,$rowvar['ccv_modelo']);
            else $exist = existencia($rowvar['ccv_articulo'], $almacen);
            //echo 'exist: '.$exist.' utilizo: '.$row['cdm_cantidad']*$rowvar['ccv_cantidad']. ' <br>';
            if($exist < ($row['cdm_cantidad']*$rowvar['ccv_cantidad'])) {
              $datos['error'] = 1; //No hay existencia
              $nmb = $rowvar['a_nmb'];
              if($rowvar['ccv_modelo'] != "") {
                $nmb .= ' '.busca($rowvar['ccv_articulo'], 'articulos_variantes', 'av_modelo = "'. $rowvar['ccv_modelo'].'" AND av_articulo', 'av_nmb');
              }
              $datos['articulos'][$i] = number_format($row['cdm_cantidad']*$rowvar['ccv_cantidad'], 0).' - '.$nmb; 
              $i++;
            }
          }
          $sqlal = 'SELECT * FROM articulos_ligados INNER JOIN articulos ON a_id = al_aligado WHERE al_articulo = "'.$rowvar['ccv_articulo'].'"';
          $resultal = setq($sqlal);
          while($rowal = $resultal -> fetch_array()){
            if($rowal['a_inventariado'] == "1"){
              if($rowal['al_amodelo'] != "") $exist = existenciaModelo( $rowal['al_aligado'], $almacen,$rowal['al_amodelo']);
              else $exist = existencia($rowal['al_aligado'], $almacen);
              //echo 'exist: '.$exist.' utilizo: '. $row['cdm_cantidad']*$rowvar['ccv_cantidad']*$rowal['al_cantidad']. ' <br>';
              if($exist < $row['cdm_cantidad']*$rowvar['ccv_cantidad']*$rowal['al_cantidad']) {
                //echo 'ENTRO!';
                $datos['error'] = 1; //No hay existencia
                $nmb = $rowal['a_nmb'];
                if($rowal['al_amodelo'] != "")  $nmb .= ' '.busca( $rowal['al_aligado'], 'articulos_variantes', 'av_modelo = "'. $rowal['al_amodelo'].'" AND av_articulo', 'av_nmb');
                $datos['articulos'][$i] = number_format($row['cdm_cantidad']*$rowvar['ccv_cantidad']*$rowal['al_cantidad'], 0).' - '.$nmb; 
                $i++;
              }
            }
          } 
        }
      } 
    }
    return $datos;
  }

  function setDatac($remision,$remisiond,$articulo,$modelo,$numero,$tipoenvio,$paqueteria,$sucursal,$combo,$estatus,$observacion,$embarque,$fenvio,$guia,$ligado){
    $this->remision = $remision;
    $this->remisiond = $remisiond;
    $this->articulo = $articulo;
    $this->modelo = $modelo;
    $this->numero = $numero;
    $this->tipoenvio = $tipoenvio;
    $this->paqueteria = $paqueteria;
    $this->sucursal = $sucursal;
    $this->combo = $combo;
    $this->estatus = $estatus;
    $this->observacion = clearvmayus($observacion);
    $this->embarque = $embarque;
    $this->fenvio = $fenvio;
    $this->guia = $guia;
    $this->ligado = $ligado;
  }

  function insertdetallec(){
    $sql = 'INSERT INTO remisionesc SET
            rc_remision = "'.$this->id.'",
            rc_remisiond = "'.$this->idrec.'",
            rc_articulo = "'.$this->articulo.'",
            rc_modelo = "'.$this->modelo.'",
            rc_numero = "'.$this->numero.'",
            rc_tipoenvio = "'.$this->tipoenvio.'",
            rc_paqueteria = "'.$this->paqueteria.'",
            rc_sucursal = "'.$this->sucursal.'",
            rc_combo = "'.$this->combo.'",
            rc_estatus = "'.$this->estatus.'",
            rc_observacion = "'.$this->observacion.'",
            rc_embarque = "'.$this->embarque.'",
            rc_fenvio = "'.$this->fenvio.'",
            rc_guia = NULL,';
            if(empty($this->ligado)){
              $sql .= 'rc_ligado = NULL';
            } else{
              $sql .= 'rc_ligado = "'.$this->ligado.'"';
            }
    setq($sql);
  }

  function existenciaapartados($remision){
    $ok = 'OK';
    $datos = array();
    $datos['error'] = 0; 
    $i = 0;
    $almacen = busca($remision, 'remisiones', 'r_id', 'r_almacen');
    $sql = 'SELECT COUNT(*) cantidad, rc_articulo, rc_modelo, a_nmb FROM remisionesc INNER JOIN articulos ON a_id = rc_articulo WHERE rc_remision = "'.$remision.'" AND a_inventariado = "1" GROUP BY rc_articulo, rc_modelo';
    $result = setq($sql);
    while($row = $result ->fetch_array()){
      $existencia = existenciaModelo($row['rc_articulo'], $almacen, $row['rc_modelo']);
      $apartados = busca($row['rc_modelo'], 'remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_tipo = "R"
       AND cx_estatus = "N" AND r_estatus != "C" AND rc_articulo = "'.$row['rc_articulo'].'" AND rc_remision != "'.$remision.'" 
      AND (SELECT COUNT(*) FROM ingresos INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso WHERE ic_cxcobrar = cx_id AND i_estatus = "P") > 0 
      AND rc_modelo', 'COUNT(*)');
      //echo busca2($row['rc_modelo'], 'remisionesc INNER JOIN remisiones ON r_id = rc_remision INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_tipo = "R" AND cx_estatus = "N" AND r_estatus != "C" AND rc_articulo = "'.$row['rc_articulo'].'" AND rc_remision != "'.$remision.'" AND rc_modelo', 'COUNT(*)');
      //echo 'cantidad: '.$row['cantidad'].' existencia: '.$existencia.' apartados: '.$apartados.'<br>';
      if($row['cantidad'] > ($existencia-$apartados)){
        $aprobada = busca($remision, 'remision_faltantes' , 'rf_articulo ="'.$row['rc_articulo'].'" AND rf_modelo = "'.$row['rc_modelo'].'" AND rf_estatus = "F" AND rf_remision' ,'COUNT(*)');
        if($aprobada <= 0){
          $datos['error'] = 1; //No hay existencia
          $nmb = $row['a_nmb'];
          if($row['rc_modelo'] != "")  $nmb .=' '.busca($row['rc_modelo'], 'articulos_variantes', 'av_articulo = "'.$row['rc_articulo'].'" AND av_modelo', 'av_nmb');
          $datos['articulos'][$i] = number_format($row['cantidad'], 0).' - '.$nmb; 
          $i++;
        }
      }
    }

    return $datos;
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
            $("#cliente").focus();
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
      $this->model->aestatus = array("A" => "Vendida","D" => "Por aprobar en caja","P" => "Por aprobar en finanzas","N" => "En Captura","C" => "Cancelado", "F" => "Liquidada" , "FE" => "Liquidada y enviada");
      $this->model->nestatus = array("A" => 'class="alert" style="background: #B0FFE7"',"D" => 'class="alert" style="background: #B7DAFF"',"P" => 'class="alert" style="background: #B7DAFF"',"N"=>'class="alert" style="background: #FFEAAB"',"C" => 'class="alert" style="background: #FFCBD0"', "F" => 'class="alert text-white" style="background: #20c997"', "FE" => 'class="alert text-white" style="background: #0C00FF"');
 
      $accion = 'insert';
      if (isset($this->model->id)) {
        $accion = 'update';
      }
      //Sección: E1 Encabezado - Botones de acción
      echo '';
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
        //VERIFICACIÖN DE LICENCIA REMISION
        $licenciarem = busca($_SESSION['emp'],'empresa_licencia','el_empresa','el_ventas');
        $sqltotrem = 'SELECT COUNT(*) FROM remisiones WHERE r_empresa = "'.$_SESSION['emp'].'"';
        $resultrem = setq($sqltotrem);
        $rowrem = $resultrem->fetch_array();

        $accionbtn = 'href="?modulo=remisiones&accion=edit"';

        $varbtn = '<a id="nuevo" '.$accionbtn.'>
          <button type="button" class="btn btn-primary">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
          </button>
        </a>';
        /* echo '<script>
          function alertarem(){
            alert("La cantidad de ventas en tu licencia ha sido superado. No puedes agregar nuevas ventas. Contacta a tu agente de JD CEO.");
          }
        </script>';
        echo '<div class="mb-5">
          '.$varbtn.'
          <button id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
          </button>
        </div>'; */
        
        $botones = '';
        $botones .= '
          <a id="nuevo" '.$accionbtn.'>
            <button type="button" class="btn btn-primary btn-sm">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
            </button>
          </a>
        ';

        $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');


        $filtro = '
          <form class="" role="form" method="post" action="?modulo=remisiones&accion=index" id="filtro">
            <div class="mb-5">
              <label for="">Cliente</label>
              <input type="text" name="cliente" id="cliente" placeholder="Nombre del cliente" class="form-control" value="'.$proveedor.'" />
            </div>';
            $filtro .='<div class="mb-5">
                <label for="tipom">Asesor</label>';
            if($grupo == "GERENCIA" || $grupo == "ADMIN" || $grupo == "FINANZAS"){
              $sqlvd = 'SELECT * FROM usuarios WHERE u_grupo = "GERENCIA" OR u_grupo = "VENTAS"';
              $resultvd = setq($sqlvd);
              $filtro .= '<select id="vendedor" name="vendedor" class="form-control">
              <option value="" selected>TODOS</option>';
              while($rowvd = $resultvd -> fetch_array()){
                if($rowvd['u_id'] == $vendedor) $sel = 'selected';  
                else $sel = '';
                $filtro.='<option value="'.$rowvd['u_id'].'" '.$sel.'>'.$rowvd['u_nmb'].' '.$rowvd['u_apellidos'].'</option>';
              }
              $filtro .= '</select>';
            } else {
              $nmb = busca($_SESSION['uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
              $filtro .= '<input type="text" name="vendedor" id="vendedor" class="form-control" value="'.$nmb.'" readonly/>';
            }
            $filtro .= '</div>';
            $filtro .='<div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >
                <option value="" '.$selt.'>Todos</option>
                <option value="N" '.$seln.'>En captura</option>
                <option value="A" '.$sela.'>Vendidas</option>
                <option value="P" '.$selp.'>Por aprobar ingreso</option>
                <option value="C" '.$selc.'>Cancelados</option>
              </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=remisiones&accion=index"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';
          $grupouser = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
          $nproductos = 0;
         /*  $sqlq = 'SELECT r_id FROM remisiones WHERE r_estatus = "F"';
          // $sqlq = 'SELECT r_id, rc_remision FROM remisiones INNER JOIN remisionc ON r_id = rc_remision WHERE r_estatus = "F"';
          if($grupouser != "ADMIN"){
            if($grupouser != "GERENCIA"){
              $sqlq.= ' AND r_encargado LIKE "%'.$_SESSION['uid'].'%"';
            }
          }
          $sqlq.=' ORDER BY r_id DESC';
          $resultq = setq($sqlq);
          while($rowq = $resultq->fetch_array()){  
          $remision = $rowq ['r_id'];
          $articulosc = intval(busca($remision, 'remisionesc', 'rc_tipoenvio IN ("P") AND rc_remision', 'COUNT(*)'));
          if($articulosc > 0){
            $nproductos++;
          }
          }

          if($nproductos > 0){
            $etiqueta = '&nbsp;<span class="badge bg-danger">'.$nproductos.'</span>';
          } else{ */
            $etiqueta = '';
          /* } */

          /* $pordefinir = '
          <a href="?modulo=remisiones&accion=prodpordefinir&tipo=2">
            <button type="button" class="btn btn-sm btn-secondary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="servicios por definir forma de envío"><i class="fas fa-file-signature"></i> servicios por definir'.$etiqueta.'</button>
          </a>
          '; */
          $pordefinir = '';
      
      toolbar($_GET['modulo'],"" ,$filtro, $pordefinir);
      ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '<div class="table table-responsive text-medium">
          <center>
            <table class="table" id="myTable">
              <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
                <tr>
                  <th>Folio</th>
                  <th>Cliente</th>
                  <th>Nombre</th>
                  <th>Último Movimiento</th>
                  <th>Límite de pago</th>
                  <th>Estatus</th>
                  <th>Importe</th>
                  <th></th>
                </tr>
              </thead>';
              while($row = $this->model->resultrc->fetch_array()){
                if($row['r_estatus'] == "A" || $row['r_estatus'] == "F" || $row['r_estatus'] == "FE"){
                  $lastm = "Aplicación: ".busca($row['r_uaplica'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ",u_apellidos)').'<br>'.date('d-m-Y',strtotime($row['r_faplica']));
                }
                elseif($row['r_estatus'] == "C"){
                  $lastm = "Cancelación: ".busca($row['r_ucan'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ",u_apellidos)').'<br>'.date('d-m-Y',strtotime($row['r_fcan']));
                }
                else{
                  $lastm = "Registro: ".busca($row['r_ualta'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ",u_apellidos)').'<br>'.date('d-m-Y',strtotime($row['r_falta']));
                }
                $sql = 'SELECT cx_id, cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$row['r_id'].'"';
                $result = setq($sql);
                list($idcxc, $importe, $abonado, $estcxc) = $result -> fetch_array();
                $sqlb = 'SELECT SUM(i_monto) FROM ingresos WHERE i_id IN (SELECT ic_ingreso FROM ingreso_cxcobrar WHERE ic_cxcobrar ="'.$idcxc.'") AND i_estatus="P"';
                $resultb = setq($sqlb);
                list($pend) = $resultb -> fetch_array();
                $sqlc = 'SELECT SUM(cd_monto) FROM cajasd WHERE cd_remision = "'.$row['r_id'].'"';
                $rest = $importe - $abonado - $pend;
                $nmb = busca($row['r_cliente'],'crm_clientes','c_id','c_nmb');
                $apellidos = busca($row['r_cliente'],'crm_clientes','c_id','c_apellidos');
                $nmbcl = $nmb.' '.$apellidos;
                echo '<tr>
                  <th>'.$row['r_folio'].'</th>
                  <td>'.$nmbcl.'</td>
                  <td>'.$row['r_nmb'].'</td>
                  <td>'.$lastm.'</td>
                  <td>'.date('d-m-Y',strtotime($row['r_fliquidacion'])).'</td>
                  <td '.$this->model->nestatus[$row['r_estatus']].'>'.$this->model->aestatus[$row['r_estatus']].'</td>
                  <td class="number-align">$'.number_format($row['r_total'],2).'</td>
                  <td>
                    <a href="?modulo=remisiones&accion=show&id='.$row['r_id'].'">
                      <button type="button" class="btn-sm btn-info btn" title="Detalles"><i class="fa fa-eye"></i> </button>
                    </a>';
                    if($row['r_estatus'] != "N" && $row['r_estatus'] != "C"){
                      echo '<a target="_BLANK" href="formats/notadeventa?id='.$row['r_id'].'">
                        <button type="button" class="btn-sm btn-secondary btn" title="Imprimir"><i class="fa fa-print"></i></button>
                      </a>';
                      if($rest== 0){ 
                        $styleabon = "success";
                        $nmbabon = "Historial de pagos";
                      }
                      else {
                        $styleabon = "warning";
                        $nmbabon = "Abonar a la cuenta";
                      }
                      echo '<a data-fancybox data-type="ajax" data-src="popup/abonaremision.php?remision='.$row['r_id'].'&rand='.rand(1,999).'" href="javascript:;">
                      <button class="btn btn-sm btn-'.$styleabon.' text-white" title="'.$nmbabon.'"><i class="fas fa-money-bill-wave" style="color: #ffffff;"></i></button>
                    </a>';
                    }
                  echo '</td>
                </tr>';
              }
            echo '</table>
            <center>
            </div>
          </div>';

          echo '
          <script>
            function mandar(id){

              document.getElementById("filtro").submit();
            }
          </script>
          <script>
          $(document).ready(function () {
            var windowHeight = $(window).height();
            $("#myTable").DataTable( {
                paging: true,
                //"order": [[3, "desc"]],
                ordering: false,
                scrollY: windowHeight * 0.5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                },
                responsivePriority: 1,
                pageLength:50,
            });
          });
          </script>
          ';

          if($_REQUEST['page'] == 0){
            $hidden = 'style="pointer-events: none;
            background: #70707026;
            color: black;"';
          }else $hidden = "";

          //filtro
          
          

        echo'</div>
      </div>';
    }
    function edit($cliente){
      if(!isset($_GET['cliente'])) $_GET['cliente'] = $cliente;
      $nmbc = busca($_GET['cliente'],'crm_clientes','c_id','c_alias');
      ?>
      <script>
        function setname(){
          //var cliente = document.getElementById("cliente").value;
          //var proyname = "Tablero de " + cliente;
          //document.getElementById("nmbt").value = proyname;
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
          }else if(nivt == 2){
            document.getElementById("opt1"+tablero).classList.remove("btn-danger");
            document.getElementById("opt2"+tablero).classList.add("btn-warning");
            document.getElementById("opt3"+tablero).classList.remove("btn-info");
          }else if(nivt == 3){
            document.getElementById("opt1"+tablero).classList.remove("btn-danger");
            document.getElementById("opt2"+tablero).classList.remove("btn-warning");
            document.getElementById("opt3"+tablero).classList.add("btn-info");
          }
        }
        $(document).ready(function() {
          $('#cliente1').on('keyup', function() {
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
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#cliente1').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions-block').fadeOut(1000);
                  setname();
                  return false;
                });
              }
            });
          });
        });
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
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#cliente').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions-block').fadeOut(1000);
                  return false;
                });
              }
            });
          });
        });
      </script>
      <?php
      if(isset($_GET['cliente'])) $href = '?modulo=clientes&accion=edit&id='.$_GET['cliente'];
      else $href = '?modulo=remisiones&accion=index';
      $boton = '<a href="'.$href.'">
        <button type="button" class="mb-2 mr-2 btn btn-warning">
          <i class="fa fa-arrow-left"></i>Atrás
        </button>
      </a>';
      toolbar($_GET['modulo'], $boton);
      echo '<div class="page-title-actions">
          <form action="?modulo=remisiones&accion=edit" method="post" autocomplete="off" name="actualizaclie">
            <div class="row mt-5">
              <div class="col-12 col-sm-12 col-md-6">
                <div class="mb-5">
                  <input type="text" name="cliente" id="cliente" placeholder="Nombre o alias del cliente" class="search_query form-control" value="'.$nmbc.'" required autofocus >
                  <label for="nmb" class="mt-1">Nombre o alias del cliente</label>
                </div>
                <div id="suggestions-block"></div>
              </div>
              <div class="col-12 col-sm-12 col-md-6">
                <button type="submit" class="btn btn-primary"><i class="fas fa-redo" style="color: #ffffff;"></i> Actualizar</button>
                <a accesskey="B" href="popup/buscarclienterem?from=remisiones" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                  <button type="button" class="btn btn-info text-white" ><i class="fas fa-search" style="color: #ffffff;"></i> Buscar</button>
                </a>
                <a href="?modulo=clientes&accion=edit" target="_BLANK">
                  <button type="button" class="btn text-white" style="background: indigo;"><i class="fas fa-user-plus" style="color: #ffffff;"></i> Nuevo</button>
                </a>
              </div>
            </div>
          </form>
      </div>';
      if($cliente){
        include('modulos/clientes.php');
        $client = new modelclientes();
        $client->select($cliente);
        echo '<div class="row">
          <div class="col-md-12">
            <div class="col-6 col-md-6 alert alert-grey bg-darken-1 ">
              Crear Remisión para: '.$client->nmb.' '.$client->apellidos.'
            </div>
            <div class="col-6 col-md-10">
              <a target="_BLANK" href="?modulo=clientes&accion=edit&id='.$client->id.'">
                <button class="btn btn-secondary"><i class="fas fa-user-alt"></i> Ver Cliente</button>
              </a>
              <a data-fancybox data-type="ajax" data-src="popup/setremision.php?cliente='.$client->id.'" href="javascript:;" >
                <button class="btn bg-grey bg-darken-2 text-white" style="background: grey" ><i class="fas fa-money-bill-alt" style="color: #ffffff;"></i> Crear Remisión</button>
              </a>
              <button id="nuevo" type="button" class="btn btn-primary mr-2" data-bs-toggle="modal" data-target="#newtablero">
                <i class="fa fa-plus"></i> Nuevo Tablero
              </button> <!-- BOTON TABLEROS -->
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="card">
              <div class="card-body">
                <div class="card-block">
                  <div class="media">
                    <div class="media-body text-xs-left">
                      <h3 class="teal bg-darken-3">Tableros abiertos</h3>
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
                            <button class="btn btn-info"><i class="fas fa-share" style="color: #ffffff;"></i></button>
                          </a>
                        </th>
                      </tr>';
                    }
                  echo'</table>
                </div>
                </div>';
              }else
                echo '<div class="alert alert-danger text-xs-center text-md-center"><h3>Sin Tableros abiertos</h3></div>
            </div>';
          echo'</div>';
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
                      ORDER BY r_falta DESC,r_id DESC LIMIT 0,5';
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
                        <th class="number-align">$'.number_format($row['r_total'],2).'</th>
                        <th>
                          <a target="_BLANK" href="?modulo=remisiones&accion=show&id='.$row['r_id'].'">
                            <button class="btn btn-info"><i class="fas fa-share" style="color: #ffffff;"></i></button>
                          </a>
                        </th>
                      </tr>';
                    }
                  echo '</table>
                </div>
                </div>';
              }else
                echo '<div class="alert alert-danger text-xs-center text-md-center"><h3>Sin Ventas previas</h3></div>';
            echo'</div>
          </div>
        </div>';
      }else{
        if(isset($_REQUEST['cliente'])){
          echo '<div class="row">
            <div class="col-12 col-md-12 alert alert-danger">
              El cliente esta inactivo o no existe un cliente que coincida con <span class="font-medium-2 text-bold-800 text-uppercase">'.$_REQUEST['cliente'].'<span>
            </div>
          </div>  ';
        }
      }
      //Modal - Nuevo deal
      echo '
      <div class="modal fade text-xs-left" id="newtablero" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <label class="modal-title text-text-bold-600" id="myModalLabel33">Nuevo tablero</label>
            </div>
            <form method="post" action="?modulo=tableros&accion=insert" autocomplete="off" >
              <div class="modal-body row">
                <div class="col-md-12 col-xs-12">
                  <div class="mb-5">  
                    <label>Cliente: </label>
                    <input type="text" name="cliente" id="cliente1" placeholder="Nombre o alias del cliente" class="search_query form-control" value="'.$nmbc.'" required >
                  </div>
                  <div id="suggestions-block"></div>
                </div>
                <div class="col-md-5 col-xs-12">
                  <label>Nombre proyecto: </label>
                  <div class="mb-5">
                    <input type="text" name="nmb" id="nmbt" placeholder="Nombre del tablero/proyecto" class="form-control" onfocus="this.select();" required>
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
                <div class="col-md-4 col-xs-12">
                  <label>Propietario: </label>
                  <div class="mb-5">
                    <select class="form-control" searchable="Responsable del tablero" name="responsable" >';
                      $sqlu = 'SELECT u_nuser,u_id,u_nmb FROM usuarios WHERE u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"';
                      $resultu = setq($sqlu);
                      while($rowu = $resultu->fetch_array()){
                        if($_SESSION['uid'] == $rowu['u_id']) $seldis = "selected"; else $seldis = "";
                        echo '<option value="'.$rowu['u_id'].'" '.$seldis.'>'.$rowu['u_id'].'</option>';
                      }
                    echo '</select>
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
                <input type="hidden" value="2" name="niveltab" id="niveltab" />
                <div class="col-md-8 col-xs-12 row mb-5">
                  <label>Nivel de negociación: </label> <br>
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-secondary btn-min-width mr-1 mb-1" id="op1"  onclick="nivelta(1)">
                      <input type="radio" name="options" id="option1"> Caliente
                    </label>
                    <label class="btn btn-secondary btn-min-width mr-1 mb-1 active btn-warning" id="op2" onclick="nivelta(2)">
                      <input type="radio" name="options" id="option2" checked> Tibio
                    </label>
                    <label class="btn btn-secondary btn-min-width mr-1 mb-1" id="op3" onclick="nivelta(3)">
                      <input type="radio" name="options" id="option3"> Frio
                    </label>
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
    }
    function show($idremision){
      $cliente = busca($idremision,'remisiones','r_id','r_cliente');
      $esquema = busca($cliente,'crm_clientes','c_id','c_precio');
      $tableroreg = busca($_GET['id'],'remisiones','r_id','r_tablero');
      $sql = 'SELECT cx_id, cx_importe, cx_abonado, cx_estatus FROM cxcobrar WHERE cx_referencia = "'.$this->model->id.'"';
      $result = setq($sql);
      list($idcxc, $importe, $abonado, $estcxc) = $result -> fetch_array();
      $sqlb = 'SELECT SUM(i_monto) FROM ingresos WHERE i_id IN (SELECT ic_ingreso FROM ingreso_cxcobrar WHERE ic_cxcobrar ="'.$idcxc.'") AND i_estatus="P"';
      $resultb = setq($sqlb);
      list($pend) = $resultb -> fetch_array();
      $rest = $importe - $abonado - $pend;
      echo '<div>
        <input type="text" name="esquema" id="esquema" value="'.$esquema.'" readonly hidden>
        <input type="text" id="fechatck" value="'.$this->model->faplica.'" hidden>
        <input type="text" id="foliotck" value="'.$this->model->folio.'" hidden>
        <input type="text" id="ugeneratck" value="'.$this->model->uaplica.'" hidden>
      </div>';
      ?>
      <script>
        $(document).ready(function() {
          $('#producto').on('keyup', function() {
            if (event.keyCode == 38 || event.keyCode == 40){
              //console.log('chi');
            }else{
            var key = $(this).val();
            //var empresa = $('#empresa').val();
            $.ajax({
              type: "POST",
              url: "query/suggestproducts2.php",
              data: {
                'producto':key,
                'desde':'remision'
              },
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
                      //console.log(dataRTN);
                      var importe = document.getElementById('importecon');
                      importe.value = dataRTN;
                    }
                  });
                  //setname();
                  //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
                  $("#cantidad").focus();
                  return false;
                });
              }
            });
            }
          });
        });
        function motivacancela(remision){
          var motivocan = prompt("¿Cuál es el motivo de la Motivo de cancelación?");
          if(motivocan.length > 0){
            window.location.href="?modulo=remisiones&accion=cancelar&id=" + remision + "&motivoc=" + motivocan;
          }
        }
        function existencias(texto, remision, dif){
          Swal.fire({
          icon: 'error',
          title: 'Es necesario que el gerente apruebe esta venta ya que los siguientes artículos se encuentran sin existencia.',
          html: texto,
          showDenyButton: true,
          confirmButtonText: '<i class="fas fa-check" style="color:#ffffff"></i>Mandar',
          denyButtonText: '<i class="fas fa-times" style="color:#ffffff"></i> Cancelar',
          }).then((result) => {
            console.log(dif)
            if(dif==2){
              if (result.isConfirmed) {
                window.location.href="?modulo=remisiones&accion=aprobargerencia&id=" + remision;
              } else {
                Swal.close();
              }
            }
          });
        }
      </script>
      <?php
      if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
      if ($_GET['alerta'] == 1) echo alert("El articulo no Existe", true);
      /* if($this->model->estatus == "N") $leyenda = 'Añadir productos a la Remisión '.$this->model->folio." ";
      else  */ $leyenda = 'Lista de servicios en la Remisión '.$this->model->folio." ";
      echo '<script>
        function delprod(idprod){
          var conf = confirm("¿Deseas borrar el servicio de la remisión?");
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
        function changeln(){
          var ln = document.getElementById("linean");
          ln.removeAttribute("style");
          $("#toolln").prop("hidden","true");
        }
      </script>';
          $botones = '';
          /* if(isset($_GET['message']) && $_GET['message'] != "OK"){
            echo '<div class="col-12 alert alert-danger">Error de existencias '.busca($_GET['message'],'articulos','a_id','a_nmb').'</div>';
          } */
          if($tableroreg){
            /* $botones .= '<a href="?modulo=tableros&accion=show&id='.$tableroreg.'" acceskey="">
              <button type="button" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i> Tablero </button>
            </a>'; */
            $botones .= '<a href="?modulo=remisiones&accion=index">
              <button type="button" class="btn btn-info btn-sm text-white"><i class="fa fa-list"></i> Remisiones </button>
            </a>';
          }else {
            $botones .= '<a href="?modulo=remisiones&accion=index" acceskey="">
              <button type="button" class="btn btn-warning btn-sm"><i class="fa fa-arrow-left"></i> 
               </button>
            </a>';
          }
          /* if($_SESSION['uid'] == 'ADMIN')
          $botones .=   
          '<a data-fancybox data-type="ajax" data-src="popup/adjuntardocremision?id='.$this->model->id.'" href="javascript:;">
            <button type="button" class="btn btn-sm text-white" style="background: brown" data-toggle="tooltip" data-placement="top" title="Ver documentos adjuntos"><i class="fa fa-paperclip" style="color: #fff"></i>Adjuntar archivo</button>
          </a>';  */
          
          if($this->model->estatus == "N"){
            $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setremision.php?idrem='.$this->model->id.'" href="javascript:;">
              <button type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Modificar información de la remisión"><i class="fa fa-pen"></i>Modificar</button>
            </a>';
          }
          if($this->model->estatus != "C" && $this->model->estatus != "N"){
            $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setremision.php?read=1&idrem='.$this->model->id.'" href="javascript:;">
              <button type="button" class="btn btn-secondary btn-sm"><i class="fa fa-info"></i> Información</button>
            </a>';
          }
          if($this->model->estatus == "N"){
            if($this->model->estatus != "C"){
              $datos = $this->model->existenciaapartados($this->model->id);
              $fal = busca($this->model->id, 'remision_faltantes', 'rf_estatus="F" AND rf_remision', 'COUNT(*)');
              $cmp = busca($this->model->id, 'remision_faltantes', 'rf_remision', 'COUNT(*)');
              $texto = "";
              foreach ($datos['articulos'] as $key) {
                $texto .= $key.'<br>';
              }

              if($datos['error'] == 0){ //|| $fal > 0
                $botones .= '<a data-fancybox data-type="ajax" data-src="popup/aplicaremision.php?id='.$this->model->id.'&rand='.rand(1,100).'" href="javascript:;">
                  <button class="btn btn-success btn-sm" onclick="aplicamov()"><i class="fas fa-check" style="color: #ffffff;"></i> Aplicar</button>
                </a>';
              }/* else if($cmp > 0){
                $botones .= '<button class="btn btn-danger btn-sm me-2" onclick="existencias(\''.$texto.'\', '.$this->model->id.', 1)"><i class="fas fa-check" style="color: #ffffff;"></i> Aplicar</button>';
              } */ else {
                $botones .= '<button class="btn btn-danger btn-sm me-2" onclick="existencias(\''.$texto.'\', '.$this->model->id.', 2)"><i class="fas fa-check" style="color: #ffffff;"></i> Aplicar</button>';
              }
            }
            
          }else if($this->model->estatus != "N" && $this->model->estatus != "D" && $this->model->estatus != "C"){
            $botones .= '<a data-fancybox data-type="ajax" data-src="popup/entregaparcial.php?remision='.$this->model->id.'&rand='.rand(1,999).'" href="javascript:;">
              <button class="btn  btn-sm text-white btn-primary"><i class="fas fa-suitcase-rolling" style="color: #ffffff"></i> Entrega parcial</button>
            </a>';
            $botones .= '<a target="_BLANK" href="formats/notadeventa.php?id='.$this->model->id.'">
              <button type="button" class="btn btn-secondary btn-sm"><i class="fa fa-print"></i> Imprimir</button>
            </a>
             <!-- <button type="button" class="btn btn-success btn-sm" onclick="imprimirticket();"><i class="fa fa-print"></i>Ticket</button> -->
            ';
            if($rest == 0)$nmb = "Pagos";
            else $nmb = "Abonar";
            $botones .= '<a data-fancybox data-type="ajax" data-src="popup/abonaremision.php?remision='.$this->model->id.'&rand='.rand(1,999).'" href="javascript:;">
                  <button class="btn btn-sm text-white" style="background: teal;"><i class="fas fa-money-bill-wave" style="color: #ffffff;"></i> '.$nmb.'</button>
                </a>';
            $factrem = busca($this->model->id,'factura_remision','fr_remision','COUNT(*)');
            $sumconceptos = busca($this->model->id,'remisionesd','rd_remision','SUM(rd_cantidad)');
            $numconceptos = busca($this->model->id,'remisionesd','rd_remision','COUNT(*)');
            $factconceptos = 0;
            $factcantidad = 0;
            $total = busca($this->model->id, 'cxcobrar', 'cx_referencia', 'cx_importe');
            $factabono = busca($this->model->id, 'factura_abonos', 'fa_remision','SUM(fa_monto)');
            if(($this->model->estatus == "F" && ($total > $factabono)) || $factabono == 0 )
              $botones .= '<a data-fancybox data-type="ajax" data-src="popup/facturarabono.php?or=1&remision='.$this->model->id.'&rand='.rand(1,999).'" href="javascript:;">
                <button class="btn  btn-sm text-white" style="background: grey"><i class="far fa-file-code" style="color: #ffffff"></i> Facturar</button>
              </a>';
            else $botones .= '<a data-fancybox data-type="ajax" data-src="popup/facturarabono.php?or=1&remision='.$this->model->id.'&rand='.rand(1,999).'" href="javascript:;">
              <button class="btn btn-success btn-sm text-white"><i class="far fa-file-code" style="color: #ffffff;"></i> Facturada</button>
            </a>';

            $botones .= '<a target="_BLANK" href="formats/poliza-garantia.php?id='.$this->model->id.'">
                          <button class="mr-1 btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Poliza de garantia"><i class="fas fa-tools"></i> Poliza de garantia</button>
                        </a>';
            

            /* elseif($factrem > 0){
              $sqlfact = 'SELECT DISTINCT(fr_factura) FROM factura_remision WHERE fr_remision = "'.$this->model->id.'"';
              $resultfact = setq($sqlfact);
              while($rowfact = $resultfact->fetch_array()){
                $factconceptos += busca($rowfact['fr_factura'],'facturasd','d_factura','COUNT(*)');
                $factcantidad += busca($rowfact['fr_factura'],'facturasd','d_factura','SUM(d_cantidad)');
              }

              if($factconceptos == $numconceptos && $factcantidad == $sumconceptos){
                if($factrem == 1) $hr = '<a target="_BLANK" href="?modulo=facturas&accion=showfactura&factura='.busca($this->model->id,'factura_remision','fr_remision','fr_factura').'">';
                else
                $hr = '<a data-fancybox data-type="ajax" data-src="popup/setfactremision.php?or=1&remision='.$this->model->id.'&ran='.rand(1,999).'" href="javascript:;">';
                $botones .= ''.$hr.'
                  <button class="btn btn-success btn-sm text-white"><i class="far fa-file-code" style="color: #ffffff;"></i> Facturada</button>
                </a>';
              }else{
                $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setfactremision.php?remision='.$this->model->id.'&ran='.rand(1,999).'" href="javascript:;">
                  <button class="btn btn-success btn-sm text-white"><i class="far fa-file-code" style="color: #ffffff;"></i> Facturar</button>
                </a>';
              }
            } */
            if(busca($this->model->id,'crm_tablerosrecurrentes','ctr_estatus IN ("A","F") AND ctr_remision','COUNT(*)') == 0){
              /* $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setrecurrente.php?tablero='.$this->model->tablero.'&remision='.$this->model->id.'&ran='.rand(1,999).'" href="javascript:;" >
                <button type="button" class="btn bg-cyan bg-darken-1 text-white btn-sm">
                  <span class="glyphicon glyphicon-cog"></span><i class="icon-repeat2"></i> Recurrente
                </button>
              </a>'; */
            }else{
              /* $botones .= '<a data-fancybox data-type="ajax" data-src="popup/setrecurrenteinfo.php?tablero='.$this->model->tablero.'&remision='.$this->model->id.'&ran='.rand(1,999).'" href="javascript:;" >
                <button type="button" class="btn btn-sm text-white">
                  <i class="icon fa fa-check"></i> <i class="icon icon-repeat2"></i> Recurrente
                </button>
              </a>'; */
            }
            /* if(busca($this->model->id,'remisiones_devoluciones','rd_estatus IN ("N","A") AND rd_remision','COUNT(*)') == 0){
              $botones .= '<a accesskey="B" href="popup/importarprodsdev.php?remision='.$this->model->id.'&tablero='.$this->model->tablero.'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                <button type="button" class="btn btn-sm btn-secondary ">
                  <span class="glyphicon glyphicon-cog"></span><i class="fas fa-undo"></i>Devolucion
                </button>
              </a>';
            } */
          }
          $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
          if($this->model->estatus != "C" && $this->model->estatus != "D" && $this->model->estatus != "FE" && ($grupo == "GERENCIA" || $grupo == "ADMIN")){
            $botones .= '<button class="mr-1 btn btn-danger btn-sm" onclick="motivacancela('.$this->model->id.');" data-toggle="tooltip" data-placement="top" title="Cancelar Remisión"><i class="fa fa-times"></i> Cancelar</button>';
          }

        //Buscamos todos los artículos padre de la remision que ya se hayan embarcado
        $narticulos = intval(busca($_GET['id'], 'remisionesc', 'rc_estatus = "F" AND rc_remision', 'COUNT(*)'));
        /* $remi = busca2($_GET['id'], 'remisionesc', 'rc_estatus = "F" AND rc_remision', 'COUNT(*)'); */
        if($narticulos > 0){
          $botones .= '
          <a href="?modulo=remisiones&accion=garantia&id='.$_GET['id'].'"><button class="btn btn-success btn-sm text-white"><i class="far fa-file-code" style="color: #ffffff;"></i> Garantías</button></a>';
        }

        /* $botones .= '
      <a data-fancybox="" data-type="ajax" data-src="popup/setartsinremision.php" href="javascript:;">
        <button type="button" class="btn btn-sm btn-primary">
          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-plus"></i> Garantía sin remisión
        </button>
      </a>'; */

        toolbar('REMISIÓN '.$this->model->folio,$botones);
      
      
      $exisantm = "";
      $miva = "";
      /* echo '
      <div class="col-12 mt-3" style="text-align: end;justify-content: end;display: flex;">
        <div class="col-12 col-md-4 alert alert-primary">
          <h3>Formas de envío seleccionadas</h3>';
          $tipos = array('O'=>'ENVÍO A OCURRE','P'=>'PENDIENTE POR DEFINIR','D'=>'ENVÍO A DOMICILIO','C'=>'RECOGE CLIENTE');
          $sqlfe ='SELECT COUNT(*) AS cantidad, rc_tipoenvio FROM remisionesc WHERE rc_remision ="'.$this->model->id.'" AND rc_ligado IS NULL GROUP BY rc_tipoenvio';
          $resultfe = setq($sqlfe); 
          while($rowfe = $resultfe -> fetch_array()){
            echo $rowfe['cantidad'].' artículo(s) - '.$tipos[$rowfe['rc_tipoenvio']].'<br>';
          }
        echo '</div>
        </div>'; */
      if($this->model->estatus == "N" || $this->model->estatus == "A" || $this->model->estatus == "D" || $this->model->estatus == "P"){
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
        echo '
        <div class="card mt-3">
          <div class="card-body row">';
          echo '<div class="col-md-6 p-3  bg-light-dark alert alert-primary">
          '.$this->model->folio.' - '.$this->model->nmb.' - '.busca($this->model->cliente,'crm_clientes','c_id','CONCAT(c_nmb," ",c_apellidos)').'
        </div>
        <div class="col-md-6 p-3 bg-light-dark alert alert-primary" hidden>Almacen: '.busca($this->model->almacen,'almacenes','a_id','a_nmb').'</div>';
        if($this->model->estatus == "A"){
          //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
        }
        
       /*  echo '
          <div class="alert alert-primary col-md-12"><center><b>Captura de servicios</b></center></div>
          <form method="post" class="row" autocomplete="off" action="?modulo=remisiones&accion='.$action.'&id='.$this->model->id.'">
            <input type="hidden" name="idprod" value="'.$idprod.'" />
            <input type="hidden" name="costo" id="costo" value="'.$this->model->costod.'" />
            <div class="col-md-3 col-sm-12">
              <div class="mb-5">
                <label for="agregar" class">Servicios</label><br>
                <div>
                  <input type="text" name="producto" id="producto" value="'.$this->model->nmbarticulod.'" placeholder="Escribe un fragmento de tu servicio" class="search_query form-control" required="required"  autofocus tabindex="1" >
                </div>
                <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: overlay;"></div>
              </div>
            </div>
            <div class="col-md-2 col-sm-12">
              <div class="mb-5">
                <label for="cant">Cantidad</label><br>
                <input type="number" min="0" value="'.$this->model->cantidadd.'" max="9999999" step="0.01" name="cantidad" id="cantidad" placeholder="Cantidad" class="form-control" required="required" tabindex="2" />
              </div>
            </div>
            <div class="col-md-2 col-sm-12">
              <div class="mb-5">
                <label for="cant">Precio unitario </label><br>
                <input type="number" min="0" max="9999999" step="0.01" value="'.$this->model->preciod.'" name="importecon" id="importecon" placeholder="Importe del concepto" class="form-control" required="required" tabindex="3" />
              </div>
            </div>
            <div class="col-md-2 col-sm-12">
              <div class="mb-5">
                <label>.</label><br>
                <button type="submit" class="btn btn-sm btn-success" tabindex="6"><i class="fa fa-paper-plane"></i> Agregar</button>
              </div>
            </div>
          </form>
        </div>
      </div>'; */
        $disab  = "";
      }else{
        if($this->model->estatus == "C")
          echo '<div class="col-12 col-md-12 alert alert-danger text-xs-center text-md-center h5">
            '.$this->model->motivocan.'
          </div>';
        $disab = ' disabled ';
        if($this->model->estatus == "N")$exisantm = "";
        else $exisantm = "Existencia antes del movimiento";
      }
      echo'<div class="col-12 col-md-12 bg-primary p-1 text-white h5">
        '.$leyenda.' - '.$this->model->nmb.'
      </div>';
      echo '
      <div class="card">
      <div class="card-body">
      <div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
          <thead class="thead thead-active bg-primary text-white text-white pt-1 pb-1">
            <tr>
              <th>Modelo</th>
              <th>Artículo</th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
               <th></th>
              <th></th>
            </tr>
          </thead>';
          $subtotal = 0;
          $totiva = 0;
          $descuento = 0;
          $iva = 0;
          $pordes = busca($this->model->id,'remisiones','r_id','r_descuento');
          $viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
          $poriva = $viva/100;
          while($row = $this->model->resultr->fetch_array()){ //VIVA MEX
            $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
            $check = busca($row['rd_articulo'],'articulos', 'a_categoria IN(SELECT cat_id FROM categorias WHERE cat_inflable != "1") AND a_id','COUNT(*)');       
            if($this->model->estatus != "F" && $this->model->estatus != "FE" && $this->model->estatus != "C" && $check > 0) $readon = "";
            echo'<script>
              function updatecant'.$row['rd_id'].'(){
                var cantidad = document.getElementById("rdcantidad'.$row['rd_id'].'").value;
                document.getElementById("newcant'.$row['rd_id'].'").value = cantidad;
                //var form = document.getElementById("updatecantidad'.$row['rd_id'].'");
                document.updatecantidad'.$row['rd_id'].'.submit();
              }
            </script>';
            echo '<form name="updatecantidad'.$row['rd_id'].'" method="post" action="?modulo=remisiones&accion=cambiarcantidad&id='.$row['rd_id'].'&remision='.$_GET['id'].'">
              <input type="hidden" name="cantidad" id="newcant'.$row['rd_id'].'"  />
            </form>';
            $precio = $row['rd_precio'];
            $importe = $precio*$row['rd_cantidad'];
            if($row['rd_iva'] == "1"){
              $chiva = "checked";
              $iva += $importe*$viva;
              
            }elseif($row['rd_iva'] == "0"){
              $chiva = "";
              $iva += 0;
            }
            $subtotal+= $importe;
            $desc = busca($row['rd_articulo'], 'articulos', 'a_id','a_descuento');
            if($desc == "1") $descuento += $importe*($pordes/100);
            $buttonact = "";
            $numvar = 0;
            if($row['rd_articulo'] != 0) $modelo = busca($row['rd_articulo'],'articulos','a_id','a_modelo'); else $modelo = "-";
            
            echo '<tr>
              <!-- <form method="post" action="?modulo=remisiones&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['rd_id'].'"> -->
                <td>'.$modelo.'</td>
                <td>'.$row['rd_nmbarticulo']."<br>".$buttonact.'</td>
                <td hidden>
                  <input id="rdcantidad'.$row['rd_id'].'" onchange="updatecant'.$row['rd_id'].'()" type="number" value="'.number_format($row['rd_cantidad'],2,'.','').'" min="0" '.$readon.' max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                  <script>
                    function cambiarcantidad(id,remision){
                      var cant = document.getElementById("rdcantidad").value;
                      window.location.href="?modulo=remisiones&accion=cambiarcantidad&id="+id+"&cantidad="+cant+"&remision="+remision;
                    }
                  </script>
                </td>
              <form method="post" action="?modulo=remisiones&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['rd_id'].'">
              <input type="number" value="'.number_format($row['rd_cantidad'],2,'.','').'" min="0" '.$readon.' max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' hidden/>
                <td hidden>
                  <input name="preciodb" value="'.$preciodb.'" hidden>
                  <input name="precioshow" value="'.$precio.'" hidden>
                  <input type="number" value="'.number_format($precio,2,'.','').'" min="0" '.$disab.' max="9999999" step="0.01" name="importe" placeholder="Monto unitario" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.'/>
                </td>';
                if($this->model->diva == "1") echo'<td><input type="checkbox" name="iva" '.$chiva.' '.$readon.' onchange="submit();"  hidden/></td>';
                else echo '<td hidden><input type="hidden" name="iva" value="'.$row['rd_iva'].'"/></td>';
                echo'<td class="number-align " hidden>
                  $ '.number_format($importe,2,'.',',').'
                </td>
                <td>
                  <center>';
                      $check = busca($row['rd_articulo'],'articulos', 'a_categoria IN(SELECT cat_id FROM categorias WHERE cat_inflable != "1") AND a_id','COUNT(*)');
                      
                      if($this->model->estatus != "F" && $this->model->estatus != "FE" && $this->model->estatus != "C" && $check > 0){
                        echo '<!--<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-redo"></i></button>
                        <a href="?modulo=remisiones&accion=show&id='.$this->model->id.'&idd='.$row['rd_id'].'">
                          <button type="button" class="btn btn-sm btn-info"><i class="fa fa-pen"></i> </button>
                        </a>-->
                        <!-- <button type="button" class="btn btn-sm btn-danger" onclick="delprod('.$row['rd_id'].')"><i class="fa fa-trash"></i> </button> -->';
                      }
                      
                  echo '</center>
                </td> 
              </form>
            </tr>';
            echo '
              <input type="text" class="nmbprodtck" value="'.$row['rd_nmbarticulo'].'" hidden>
              <input type="text" class="modelotck" value="'.$modelo.'" hidden>
              <input type="text" class="cantidadprodtck" value="'.number_format($row['rd_cantidad'],2,'.','').'" hidden>
              <input type="text" class="precioprodtck" value="'.number_format($precio,2,'.',',').'" hidden>
            ';


          }
          echo '<tfoot>';
            //$totalf = $subtotal+$totiva;
            if($this->model->diva == "1" || $this->model->precioenvio > 0 || $descuento >0)
            echo '<tr class="p-1 bg-grey bg-lighten-2">
                <td colspan="3">&nbsp;</td>
                <td colspan="2" class="number-align  h4">Subtotal</td>
                <td class="number-align h4">$'.number_format($this->model->subtotal,2).'</td>
              </tr>';
            if($this->model->diva == "1"){
              if($descuento > 0)
                echo '<tr class="p-1 bg-grey bg-lighten-3">
                  <td colspan="3" >&nbsp;</td>
                  <td colspan="2"  class="number-align h4">Descuento</td>
                  <td  class="number-align bg-grey bg-lighten-3 h4">$'.number_format(round($descuento),2).'</td>
                </tr>';
              echo '<tr class="p-1 bg-grey bg-lighten-2">
                <td colspan="3" >&nbsp;</td>
                <td colspan="2"  class="number-align h4">IVA</td>
                <td  class="number-align bg-grey bg-lighten-2 h4">$'.number_format($this->model->iva,2).'</td>
              </tr>';
              if($this->model->precioenvio > 0)
              echo '<tr class="p-1 bg-grey bg-lighten-3">
                <td colspan="3">&nbsp;</td>
                <td colspan="2"class="number-align h4">Costo de envío</td>
                <td  class="number-align h4">$'.number_format($this->model->precioenvio,2).'</td>
              </tr>';
              echo '<tr class="p-1 bg-grey bg-lighten-3">
                <td colspan="3">&nbsp;</td>
                <td colspan="2"class="number-align h4"><h4>Total</h4></td>
                <td  class="number-align h4"><h4>$'.number_format($this->model->total,2).'</h4></td>
              </tr>';
            }else{
              if($descuento > 0)
                echo '<tr class="p-1 bg-grey bg-lighten-3">
                  <td colspan="3" >&nbsp;</td>
                  <td colspan="2"  class="number-align h4">Descuento</td>
                  <td  class="number-align bg-grey bg-lighten-3 h4">$'.number_format(round($descuento),2).'</td>
                </tr>';
                if($this->model->precioenvio > 0)
                echo '<tr class="p-1 bg-grey bg-lighten-3">
                  <td colspan="3">&nbsp;</td>
                  <td colspan="2"class="number-align h4">Costo de envío</td>
                  <td  class="number-align h4">$'.number_format($this->model->precioenvio,2).'</td>
                </tr>';
                echo '<tr class="p-1 bg-grey bg-lighten-2">
                  <td colspan="3" hidden>&nbsp;</td>
                  <td colspan="2"  class="number-align h4"><h4>Total</h4></td>
                  <td  class="number-align h4"><h4>$'.number_format($this->model->total,2).'</h4></td>
                </tr>';
            }
          echo'</tfoot>';
        $logo = busca($_SESSION['emp'],'empresas','e_id','e_logo');
        echo '</table>
      </div>
      </div>
      </div>
      <input type="text" id="subtotaltck" value="'.number_format($this->model->subtotal,2).'" hidden>
      <input type="text" id="ivatck" value="'.number_format($this->model->iva,2).'" hidden>
      <input type="text" id="totaltck" value="'.number_format($this->model->total,2).'" hidden>
      <input type="text" id="descuentotck" value="'.number_format($descuento,2).'" hidden>
      <input type="text" id="pordestck" value="'.$pordes.'" hidden>
      <input type="text" id="logotck" value="'.$logo.'" hidden>';
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
      <script>
        function imprimirticket(){
          var ficha = document.getElementById("fechatck").value;
          var ficha1 = document.getElementById("foliotck").value;
          var ficha2 = document.getElementById("ugeneratck").value;
          var ficha3 = document.getElementById("subtotaltck").value;
          var ficha4 = document.getElementById("ivatck").value; 
          var ficha5 = document.getElementById("totaltck").value;
          var ficha6 = document.getElementById("descuentotck").value;
          var ficha7 = document.getElementById("pordestck").value;
          /*var cambio = document.getElementById("cambio1").value;
          var efectivo = document.getElementById("efectivo1").value;
          var tarjeta = document.getElementById("tarjeta1").value;
          var trans = document.getElementById("trans1").value;
          var vale = document.getElementById("vale1").value;
          var cheque = document.getElementById("cheque1").value; */
          //var logo = document.getElementById("logo").value;
          var logo = document.getElementById("logotck").value;
          nmbart = document.getElementsByClassName("nmbprodtck");
          cantart = document.getElementsByClassName("cantidadprodtck");
          preart = document.getElementsByClassName("precioprodtck");
          var ventimp = window.open(' ', 'Imprimir Ticket');
          ventimp.document.open();
          ventimp.document.write('<html><head><title>' + document.title + '</title>');
          //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
          ventimp.document.write('</head><body >');
          ventimp.document.write('<div style="width:302px;height:200px">');
          ventimp.document.write('<center><img src="../'+logo+'" height="200" width="250px"></center>');
          ventimp.document.write('</div>');
          ventimp.document.write('<table width="302px"><tr>');
          ventimp.document.write('<td width="50%" colspan="3" align="center" style="font-size:14px">FECHA</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="3" align="center" style="font-size:12px">'+ficha+'</td></tr>');
          ventimp.document.write('<tr><td colspan="3" height="10px"></td></tr>');
          ventimp.document.write('<tr><td width="50%" align="center" style="font-size:14px">Folio:</td><td align="left" colspan="2" style="font-size:14px">'+ficha1+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" align="center" style="font-size:14px">Genera:</td><td align="left" colspan="2" style="font-size:14px">'+ficha2+'</td></tr>');
          ventimp.document.write('<tr><td colspan="3" height="15px"></td></tr>');
          ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="50%" style="font-size:14px">SERVICIO</td><td align="center" width="20%" style="font-size:14px">CANTIDAD</td><td align="center" width="30%" style="font-size:14px">PRECIO</td></tr>');
          ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
          for(var i=0; i<nmbart.length;i++){
            ventimp.document.write('<tr><td width="50%" style="font-size:12px">'+nmbart[i].value+'</td><td align="center" width="20%" style="font-size:12px">'+cantart[i].value+'</td><td align="center" width="30%" style="font-size:12px">$'+preart[i].value+'</td></tr>');
            ventimp.document.write('<tr><td colspan="3" height="5px"></td></tr>');
          }
          ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">% Descuento:</td><td align="right" style="font-size:14px">'+ficha7+'</td></tr>');
          ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Subtotal:</td><td align="right" style="font-size:14px">$'+ficha3+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">IVA:</td><td align="right" style="font-size:14px">$'+ficha4+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Descuento:</td><td align="right" style="font-size:14px">$'+ficha6+'</td></tr>');
          ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Total:</td><td align="right" style="font-size:14px">$'+ficha5+'</tr>');
          ventimp.document.write('<tr><td colspan="3" height="20px"></td></tr>');
          ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:12px">Gracias por su compra</td></tr>');
          ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:12px">Este recibo no es un comprobante fiscal</td></tr>');
          ventimp.document.write('</table></body></html>');
          ventimp.document.close();
          ventimp.focus();

          ventimp.onload = function() {
            //document.boton.submit();
            ventimp.print();
            //boton = ventimp.document.getElementsByClassName("action-button");
            //ventimp.console.log("evento onload "+boton.length);
            ventimp.close();
          };
        }
      </script> 

      <?php
      
    }

    function prodpordefinir()
    {
  
      $atras = '
    <a href="?modulo=remisiones&accion=index">
      <button type="button" class="btn btn-sm btn-warning mb-1 mr-1" data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
    </a>';
  
      $filtro = '
    <form class="form-inline" role="form" method="post" action="?modulo=articulos&accion=index" id="filtro">
    <div class="mb-5" hidden>
      <label class="mr-sm-2">Page:</label>
      <div class="position-relative has-icon-left">
        <input type="text" id="page" name="page" class="form-control" value="" required>
      </div>
    </div>
    <div class="mb-5">
      <label class="mr-sm-2">Fecha desde:</label>
      <div class="position-relative has-icon-left">
        <input type="date" id="fini" name="fini" class="form-control" value="" required>
        <input hidden type="date" id="fini2" class="form-control" value="' . date('Y-m-d', strtotime('-20 days')) . '">
        <div class="form-control-position">
          <i class="icon-calendar5"></i>
        </div>
      </div>
    </div>
    <div class="mb-5">
      <label class="mr-sm-2">Fecha hasta:</label>
      <div class="position-relative has-icon-left">
        <input type="date" id="ffin" name="ffin" class="form-control" value="" required>
        <input hidden type="date" id="ffin2" class="form-control" value="' . date('Y-m-d', strtotime('last day of this month')) . '">
        <div class="form-control-position">
          <i class="icon-calendar5"></i>
        </div>
      </div>
    </div>
      <div class="mb-5">
        <label for="">Acciones:</label> <br>
      <button type="button" onclick="mandar(0)" class="btn btn-info">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-redo"></i> Actualizar
      </button>
      <button type="button" onclick="mandar(1)" class="btn btn-warning">
        <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
      </button>
      </div>
    </form>';

    $folio = busca($_GET['id'], 'remisiones', 'r_id', 'r_folio');
  
      toolbar('ENVIOS POR DEFINIR '.$folio, $atras, $filtro);
  
      ?>
      <div class="card mt-3">
        <div class="card-body">
          <?php
          echo '
      <div class=" col-12 table-responsive">
        <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
        <thead class="bg-light-blue bg-darken-2 ">
          <tr>
            <th><b>Folio</b></th>
            <th><b>Nombre</b></th>
            <th><b>Encargado</b></th>
            <th><b>Almacen</b></th>
            <th><b>Cliente</b></th>
            <!-- <th><b>Fecha</b></th> -->
            <th><b>Estatus</b></th>
            <th><b></b></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
        </div>
      </div>';
  
          echo ' 
      <script>
      function mandar(id){
        var ugen = "'.$_SESSION['uid'].'";
        var fini2 = document.getElementById("fini2");
        var ffin2 = document.getElementById("ffin2");
        var fini = document.getElementById("fini");
        var ffin = document.getElementById("ffin");
  
        if(id == 1){
          fini.value = fini2.value;
          ffin.value = ffin2.value;
        }
  
        //var table = $("#myTable").DataTable();
        $("#myTable").DataTable().clear().draw();
        $("#myTable").DataTable().destroy();
  
        $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          processing: true,
          serverside: true,
          ordering: false,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatableprodpordefinir.php",
            type: "POST",
            datatype: "json",
            data:{ 
              "tipo" : "2",
              "ugen" : ugen,
              "fini" : fini.value,
              "ffin" : ffin.value
            }
          },
          pageLength: "50",
          responsivePriority: 1,
        });
      }
      
      mandar(1);
    </script>
    ';
    }

    function garantia($id) {
      ?>
      <script language="JavaScript">
        function checkSubmitmovimiento() {
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
            $("#cliente").focus();
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

      $atras = '
      <a href="?modulo=remisiones&accion=show&id='.$id.'">
        <button type="button" class="btn btn-sm btn-warning " data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
      </a>';

      $seegarantias = '
      <a href="?modulo=remisiones&accion=seegarantias&id='.$id.'">
        <button type="button" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> Artículos en garantía
        </button>
      </a>';

      $folio = busca($id, "remisiones", "r_id", "r_folio");
      toolbar("ARTÍCULOS REMISIÓN ".$folio, $atras,'','',$seegarantias);
      //Inicio de la condición de la fecha de garantía
      ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '<div class="table table-responsive text-medium">
          <center>
            <table class="table" id="myTable">
              <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
                <tr>
                  <th>Nombre artículo</th>
                  <!-- <th>Cliente</th> -->
                  <th>Fecha envío</th>
                  <th>Paquetería</th>
                  <th></th>
                </tr>
              </thead>';
              while($row = $this->model->resultg->fetch_array()){

                $response = busca($row['rc_id'], 'garantias_articulos', 'ga_estatus IN ("P", "A", "E", "R") AND ga_rcid', 'COUNT(*)');
                if($response == 0){
                echo '
                <tr>';
                $diassuma = busca("1", "configuracionesp", "c_id", "c_dgarantia");
                $fenvio= $row['rc_fenvio'];
                $fecha_nueva = date("Y-m-d", strtotime($fenvio . " + " . $diassuma . " days"));
                if($fecha_nueva > date("Y-m-d")){
                  $nmba = busca($row['rc_articulo'], "articulos", "a_id", "a_nmb");
                  $var = busca($row['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $row['rc_modelo'] . '" AND av_articulo', 'COUNT(*)');
                  if ($var > 0) {
                    $nmba .= " " . busca($row['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $row['rc_modelo'] . '" AND av_articulo', 'av_nmb');
                  }
    
                  $fenvio = cambiar_fecha($fenvio);

                  if($row['rc_paqueteria'] == 0){
                    $paqueteria = "RECOGE CLIENTE";
                  } else{
                    $paqueteria = busca($row['rc_paqueteria'], 'paqueterias', 'p_id', 'p_nmb');
                  }
                    echo '<td>'.$nmba . " " . $row['rc_numero'].'</td>
                    <!-- <td></td> -->
                    <td>'.$fenvio.'</td>
                    <td>'.$paqueteria.'</td>
                    <td>
                    <a data-fancybox="" data-type="ajax" data-src="popup/setgarantia.php?ids='.$row['rc_id'].'&tipo=1" href="javascript:;">
                      <button type="button" class="btn btn-sm btn-primary">
                        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
                      </button>
                    </a>
                    </td>';
                }
                echo '</tr>';
                }
              }
            echo '</table>
            <center>
            </div>
          </div>';
        echo'</div>
      </div>';
      //Fin de la condición de la fecha de garantía

      echo '
          <script> 
          $(document).ready(function () {
            var windowHeight = $(window).height();
            $("#myTable").DataTable( {
                paging: true,
                ordering: false,
                scrollY: windowHeight * 0.5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                },
                responsivePriority: 1,
                pageLength:50,
            });
          });
          </script>';
    }

    function artengarantia($id) {
      ?>
      <script language="JavaScript">
        function checkSubmitmovimiento() {
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
            $("#cliente").focus();
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

      $atras = '
      <a href="?modulo=remisiones&accion=garantia&id='.$id.'">
        <button type="button" class="btn btn-sm btn-warning " data-toggle="tooltip" data-placement="top"><i class="fa fa-arrow-left"></i> Atrás</button>
      </a>';



      $folio = busca($id, "remisiones", "r_id", "r_folio");
      toolbar("ARTÍCULOS EN GARANTÍA REMISIÓN ".$folio, $atras);
      //Inicio de la condición de la fecha de garantía
      ?>
      <div class="card mt-3">
        <div class="card-body row">
        <div class="col-md-12 col-12 col-sm-12">
          <?php
          echo '<div class="table table-responsive text-medium">
          <center>
            <table class="table" id="myTable">
              <thead class="thead bg-blue bg-darken-3 pt-1 pb-1">
                <tr>
                  <th>Nombre artículo</th>
                  <th>Fecha entrada</th>
                  <th>Fecha salida</th>
                  <th>Estatus</th>
                  <th>Descripción</th>';
                  $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
                  if($grupo == "ADMIN" || $grupo == "GERENCIA" || $grupo == "LOGISTIC"){
                    echo '
                    <th></th>
                    ';
                  }
                echo '</tr>
              </thead>';
              while($rowga = $this->model->results->fetch_array()){

                $response = busca($rowga['ga_remision'], 'garantias_articulos', 'ga_estatus IN ("M","C","P","A","E","R","F") AND ga_remision', 'COUNT(*)');
                if($response > 0){
                echo '
                <tr>';

                $sqlrc = 'SELECT * FROM remisionesc WHERE rc_id = "'.$rowga['ga_rcid'].'"';
                $resultrc = setq($sqlrc);
                $row = $resultrc->fetch_array();

                $diassuma = busca("1", "configuracionesp", "c_id", "c_dgarantia");
                $fenvio= $row['rc_fenvio'];
                $fecha_nueva = date("Y-m-d", strtotime($fenvio . " + " . $diassuma . " days"));
                if($fecha_nueva > date("Y-m-d")){
                  $nmba = busca($row['rc_articulo'], "articulos", "a_id", "a_nmb");
                  $var = busca($row['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $row['rc_modelo'] . '" AND av_articulo', 'COUNT(*)');
                  if ($var > 0) {
                    $nmba .= " " . busca($row['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $row['rc_modelo'] . '" AND av_articulo', 'av_nmb');
                  }
    
                  $fentrada = fecha_formato($rowga['ga_fini'], true, false);
                  $descripcion = $rowga['ga_descripcion'];
                  $url = '';
                  if($rowga['ga_estatus'] == "A"){
                    $estatus = "PENDIENTE DE ENVÍO A PRODUCCIÓN";
                    $url = '&save=1';
                  } else if($rowga['ga_estatus'] == "E"){
                    $estatus = "REPARACIÓN EN PRODUCCIÓN";
                  } else if($rowga['ga_estatus'] == "P"){
                    $estatus = "PENDIENTE DE APROBACIÓN";
                  } else if($rowga['ga_estatus'] == "R"){
                    $estatus = "CONFIRMAR DE RECÍBIDO DE PRODUCCIÓN";
                  } else if($rowga['ga_estatus'] == "F"){
                    $estatus = "FINALIZADO";
                  } else if($rowga['ga_estatus'] == "M"){
                    $estatus = "RECIBIDO EN LA FÁBRICA";
                  } else {
                    $estatus = "NO APROBADA";
                  }

                  if(empty($rowga['ga_ffin'])){
                    $fsalida = "No disponible";
                  } else{
                    $fsalida = fecha_formato($rowga['ga_ffin'], true, false);
                  }

                    echo '<td>'.$nmba . " " . $row['rc_numero'].'</td>
                    <td>'.$fentrada.'</td>
                    <td>'.$fsalida.'</td>
                    <td>'.$estatus.'</td>
                    <td>'.$descripcion.'</td>';
                    if($grupo == "ADMIN" || $grupo == "GERENCIA" || $grupo == "LOGISTIC"){
                      echo '
                      <td>
                      <a data-fancybox="" data-type="ajax" data-src="popup/setaprobargarantia.php?id='.$rowga['ga_id'].'&tipo=1'.$url.'&origen=R&fa=1" href="javascript:;">
                        <button type="button" class="btn btn-sm btn-primary">
                          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
                        </button>
                      </a>';
                      $cxc = busca($rowga['ga_id'], 'cxcobrar', 'cx_tipo = "B" AND cx_referencia', 'cx_id');
                      if(!empty($cxc)){
                        echo '<a data-fancybox="" data-type="ajax" data-src="popup/abonargarantia.php?garantia='.$rowga['ga_id'].'" href="javascript:;">
                        <button type="button" class="btn btn-sm btn-success">
                          <span class="glyphicon glyphicon-cog"></span><i class="fas fa-money-bill-wave"></i>
                        </button>
                      </a>';
                      }
                      echo '
                      </td>
                      ';
                    }
                }
                echo '</tr>';
                }
              }
            echo '</table>
            <center>
            </div>
          </div>';
        echo'</div>
      </div>';
      //Fin de la condición de la fecha de garantía

      echo '
          <script> 
          $(document).ready(function () {
            var windowHeight = $(window).height();
            $("#myTable").DataTable( {
                paging: true,
                ordering: false,
                scrollY: windowHeight * 0.5,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                },
                responsivePriority: 1,
                pageLength:50,
            });
          });
          </script>';
    }
  }
?>