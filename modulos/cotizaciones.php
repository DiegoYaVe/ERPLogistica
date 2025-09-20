<?php
  //ini_set('display_errors', 1);
  class cotizaciones{
    function __construct(){
      $this->model = new modelcotizaciones($obj);
    }
    function index(){
      include('tableros.php');
      $tablero = new modeltableros();

      $this->model->select($_GET['id']);
      $tablero->select($this->model->id);
      $this->model->resultcd($_GET['id']);
      $this->view = new viewcotizaciones($this->model);
      $this->view->index();
    }
    function insertcotiza(){
      $acronimo = busca(1,'empresas','e_id','e_siglas');
      $sqlm = 'SELECT COUNT(*) FROM crm_cotizaciones WHERE cc_folio LIKE "CT'.$acronimo.'%"';
      $resultm = setq($sqlm);
      list($maxcot) = $resultm->fetch_array();
      $maxcot++;
      $uuid = $this->model->guidv4();

      $foliocot = 'CT'.$acronimo.str_pad($maxcot,6,"0",STR_PAD_LEFT);
      if(isset($_POST['iva'])) $iva = "1"; else $iva = "0";
      if(isset($_POST['total'])) $total = "1"; else $total = "0";
      if(isset($_POST['reccliente'])) $envio = "C"; else $envio = "P";

      $nmbagente = busca($_POST['responsable'],'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)');
      $puestoagente = busca($_POST['responsable'],'usuarios','u_id','u_puesto');
      $copiamail = busca($_POST['responsable'],'usuarios','u_id','u_mailcorp');

      if($_POST['direcciones'] == "XXX" && isset($_POST['setdir'])){
        $pais = $_POST['pais'];
        $cl = busca($_GET['tablero'], 'crm_tableros', 'ct_id', 'ct_cliente');
        
        if($pais == "146"){
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
          cd_predeterminada = "0",
          cd_recibe = "'.$_POST['recibe'].'",
          cd_pais = "146"';
          setq($sql);
          $direccion = getmax('cd_id', 'crm_direcciones', false, false);
        } else {
          $sql = 'INSERT INTO crm_direcciones SET
          cd_nmbdir = "Cotizacion '.$_POST['nmbac'].'",
          cd_cliente = "'.$cl.'",
          cd_tipodir = "2",
          cd_calle = "'.$_POST['direccionint'].'",
          cd_pais = "'.$pais.'",
          cd_recibe = "'.$_POST['recibe'].'"';
          setq($sql);
          $direccion = getmax('cd_id', 'crm_direcciones', false, false);
        }
       
      } else if(isset($_POST['setdir'])){
        $direccion = $_POST['direcciones'];
        $pais = $_POST['pais'];
        if($pais == "146"){
          $sql = 'UPDATE crm_direcciones SET cd_calle = "'.$_POST['calle'].'",
                                          cd_nume = "'.$_POST['exterior'].'",
                                          cd_numi = "'.$_POST['interior'].'",
                                          cd_colonia = "'.$_POST['col'].'",
                                          cd_municipio = "'.$_POST['municipio'].'",
                                          cd_cp = "'.$_POST['cp'].'",
                                          cd_estado = "'.$_POST['estadoh'].'",
                                          cd_recibe = "'.$_POST['recibe'].'",
                  WHERE cd_id = "'.$direccion.'"';
          setq($sql);                
        } else {
          $sql = 'UPDATE crm_direcciones SET cd_calle = "'.$_POST['direccionint'].'",
                                            cd_recibe = "'.$_POST['recibe'].'",
                                            cd_pais = "'.$pais.'"
                  WHERE cd_id = "'.$direccion.'"';
          setq($sql);   
        }
      } else {
        $direccion = "0";
      }

      if($direccion != "0" && $direccion != ''){
        $cldir = busca($direccion, 'crm_direcciones', 'cd_id', 'cd_cliente');
        if($cldir != $cl){
          $sqlupd = 'UPDATE crm_cotizaciones SET cc_direnvio = "0" WHERE cc_id = "'.$_GET['idcotiza'].'"';
          setq($sqlupd);
          echo '<script> alert("Error al guardar la dirección, intentelo de nuevo.")</script>';
          redirect('?modulo=cotizaciones&accion=index&id='.$this->model->idc);
          die();
        }
      }
      $this->model->SetData(NULL,$_GET['tablero'],$foliocot,$_POST['nmbac'],$_POST['ffin'],$_POST['descripcion'],$iva,$total,"N","0",$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['responsable'],$_POST['descuento'],$_POST['montodescuento'],$nmbagente,$puestoagente,$copiamail, $direccion,$_POST['observadir'],$uuid,$_POST['almacen'], $envio);
      $this->model->insertcotiza();

      $sql = 'INSERT INTO crm_cotizaciones_historial SET cch_cotizacion = "'.$this->model->idc.'", cch_fecha = "'.date('Y-m-d H:i:s').'", cch_user = "'.$_SESSION['uid'].'", cch_descripcion = "CREACIÓN DE LA COTIZACIÓN"';
      setq($sql);

      $sql = 'SELECT * FROM condicionesc WHERE c_obligatorio = "1" AND c_estatus = "A"';
      $result = setq($sql);
      while($row = $result ->fetch_array()){
        $_POST['estatus'] = "A";
        $_POST['orden'] = getmax('cf_orden','crm_cotizaciones_condiciones', 'cf_cotizacion = "'.$_GET['idc'].'"');
        $_GET['idc'] = $this->model->idc;
        $this->model->setDataCondicion($row['c_nmb']);
        $this->model->insertcondicion($row['c_id']);
      }
      redirect('?modulo=cotizaciones&accion=index&id='.$this->model->idc);
    }
    function updatecotiza(){
      //foreachdie();
      $this->model->select($_GET['idcotiza']);
      if($this->model->estatus == "N" || $this->model->estatus == "R" || $this->model->estatus == "D"){
        if(isset($_POST['iva'])) $iva = "1"; else $iva = "0";
        if(isset($_POST['total'])) $total = "1"; else $total = "0";
        if(isset($_POST['reccliente'])) $envio = "C"; else $envio = "P";
      } else {
        $iva = $this->model->diva;
        $total = $this->model->mtotal;
        $envio = $this->model->envio;
      }

      $nmbagente = busca($_POST['responsable'],'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)');
      $puestoagente = busca($_POST['responsable'],'usuarios','u_id','u_puesto');
      $copiamail = busca($_POST['responsable'],'usuarios','u_id','u_mailcorp');

      $sql = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$_GET['idcotiza'].'"';
      $result = setq($sql);

      if($result->num_rows > 0){
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        $mtotal = busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_mtotal');
        $miva = busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_diva');
        while($row = $result->fetch_array()){
          if($total == "0" || $mtotal == "0"){
            $proceso = "0";
            if($total == "0" && $miva == "1") $proceso = "1";
            elseif($total == "1" && $iva == "1") $proceso = "1";
          }
          else $proceso = "1";

          if($miva == $iva && $total == $mtotal) $proceso = "0";

          if($proceso == "1"){
            if($row['cdm_iva'] == "1" && $iva == "0"){
              $precio = $row['cdm_precio'] * (1+($viva/100));
              $sqlup = 'UPDATE crm_cotizacionesd SET cdm_precio = "'.$precio.'" WHERE cdm_id = "'.$row['cdm_id'].'"';
              setq($sqlup);
            }elseif($row['cdm_iva'] == "1" && $iva == "1"){
              $precio = $row['cdm_precio'] / (1+($viva/100));
              $sqlup = 'UPDATE crm_cotizacionesd SET cdm_precio = "'.$precio.'" WHERE cdm_id = "'.$row['cdm_id'].'"';
              setq($sqlup);
            }
          }
        }
      }
      $cl = busca($_GET['tablero'], 'crm_tableros', 'ct_id', 'ct_cliente');

      if($_POST['direcciones'] == "XXX" && isset($_POST['setdir'])){
        $pais = $_POST['pais'];
        
        if($pais == "146"){
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
          cd_predeterminada = "0",
          cd_recibe = "'.$_POST['recibe'].'",
          cd_pais = "146"';
          setq($sql);
          $direccion = getmax('cd_id', 'crm_direcciones', false, false);
        } else {
          $sql = 'INSERT INTO crm_direcciones SET
          cd_nmbdir = "Cotizacion '.$_POST['nmbac'].'",
          cd_cliente = "'.$cl.'",
          cd_tipodir = "2",
          cd_nume = "",
          cd_numi = "",
          cd_colonia = "",
          cd_municipio = "",
          cd_cp = "",
          cd_estado = "",
          cd_predeterminada = "0",
          cd_calle = "'.$_POST['direccionint'].'",
          cd_recibe = "'.$_POST['recibe'].'",
          cd_pais = "'.$pais.'"';
          setq($sql);
          $direccion = getmax('cd_id', 'crm_direcciones', false, false);
        }
       
      } else if(isset($_POST['setdir'])){
        $direccion = $_POST['direcciones'];
        $pais = $_POST['pais'];
        if($pais == "146"){
          $sql = 'UPDATE crm_direcciones SET cd_calle = "'.$_POST['calle'].'",
                                          cd_nume = "'.$_POST['exterior'].'",
                                          cd_numi = "'.$_POST['interior'].'",
                                          cd_colonia = "'.$_POST['col'].'",
                                          cd_municipio = "'.$_POST['municipio'].'",
                                          cd_cp = "'.$_POST['cp'].'",
                                          cd_recibe = "'.$_POST['recibe'].'",
                                          cd_estado = "'.$_POST['estadoh'].'"
                  WHERE cd_id = "'.$direccion.'"';
          setq($sql);                
        } else {
          $sql = 'UPDATE crm_direcciones SET cd_calle = "'.$_POST['direccionint'].'",
                                            cd_pais = "'.$pais.'",
                                            cd_nume = "'.$_POST['exterior'].'",
                                            cd_numi = "",
                                            cd_colonia = "",
                                            cd_municipio = "",
                                            cd_cp = "",
                                            cd_recibe = "'.$_POST['recibe'].'",
                                            cd_estado = ""
                  WHERE cd_id = "'.$direccion.'"';
          setq($sql);   
        }
      } else {
        $direccion = "0";
      }

      if(!isset($_POST['direcciones'])) $direccion = $this->model->direnvio;
      if(!isset($_POST['responsable'])) $_POST['responsable'] = $this->model->responsable;
      if(!isset($_POST['almacen'])) $_POST['almacen'] = $this->model->almacen;

      if($direccion != "0" && $direccion != ''){
        $cldir = busca($direccion, 'crm_direcciones', 'cd_id', 'cd_cliente');
        if($cldir != $cl){
          $sqlupd = 'UPDATE crm_cotizaciones SET cc_direnvio = "0" WHERE cc_id = "'.$_GET['idcotiza'].'"';
          setq($sqlupd);
          echo '<script> alert("Error al guardar la dirección, intentelo de nuevo.")</script>';
          redirect('?modulo=cotizaciones&accion=index&id='.$this->model->id);
          die();
        }
      }

      $this->model->SetData($_GET['idcotiza'],$_GET['tablero'],NULL,$_POST['nmbac'],$_POST['ffin'],$_POST['descripcion'],$iva,$total,$_POST['estatus'],"0",$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['responsable'],$_POST['descuento'], $_POST['impdescuento'] ,$nmbagente,$puestoagente,$copiamail,$direccion,$_POST['observadir'],NULL,$_POST['almacen'], $envio);
      $this->model->updatecotiza();

      $this->model->calculaimporte($_GET['idcotiza']);
      redirect('?modulo=cotizaciones&accion=index&id='.$this->model->id);
    }
    function insertprod(){
      
      //$cantidad = $_POST['cantidadorig'];
      $cantidad = '1';
      $idprod = getIDprod($_POST['producto']);
      if(!$idprod){ 
        list($idprod, $modelo) = getIDprodVar($_POST['producto']);
        if(!$idprod){
        echo '<script>
                alert("Error: El servicio buscado no esta registrado en tu base de datos, verifica la información");
                window.location.href="?modulo=cotizaciones&accion=index&id='.$_GET['id'].'";
              </script>';
        die();
        }
      } else {
        $modelo = NULL;
      }
      if($idprod) {
        if($modelo) $count = busca($idprod,'crm_cotizacionesd','cdm_cotizacion = "'.$_GET['id'].'" AND cdm_modelo = "'.$modelo.'" AND cdm_articulo','COUNT(cdm_id)');
        else $count = busca($idprod,'crm_cotizacionesd','cdm_cotizacion = "'.$_GET['id'].'" AND cdm_modelo = "" AND cdm_articulo','COUNT(cdm_id)');
      } 
      else $count = NULL;

      $combo = busca($idprod,'articulos','a_id','a_tipoprod');

      if($count == 0 || $combo == "M"){
        $ivades = 0;
       

        if($idprod){
          if($modelo){
            $costo = busca($idprod,'articulos_precios','ap_activo = "1" AND "'.$modelo.'" AND ap_articulo','ap_costo');
            $nmbprod = busca($idprod,'articulos','a_id','a_nmb')." ".busca($idprod,'articulos_variantes','av_modelo = "'.$modelo.'" AND av_articulo','av_nmb');
          }else {
            $costo = busca($idprod,'articulos_precios','ap_activo = "1" AND ap_articulo','ap_costo');
            $nmbprod = busca($idprod,'articulos','a_id','a_nmb');
          }
          
          $viva = busca("1",'configuracionesp','c_id','c_iva');
          if(busca($_GET['id'],'crm_cotizaciones','cc_id','cc_diva') == 1){
            $ivades = 1;
            $precio = $_POST['impunitario']/(1+($viva/100));
          }else {
            $ivades = 0;
            $precio = $_POST['impunitario'];
          }

          $estg = "A";
          if($combo == "M"){
            $sqlcombo = 'SELECT * FROM articulos_combos WHERE ac_articulo = "'.$idprod.'"';
            $resultcombo = setq($sqlcombo);
            while($row1 = $resultcombo->fetch_array()){
              $est = busca($row1['ac_ahijo'],'articulos','a_id','a_estatus');
              if($est == "I") {
                $estg = "I";
              }
            }
          }else{
            $estg = busca($idprod,'articulos','a_id','a_estatus');
          }
        }else{
          $precio = $_POST['impunitario'];
          $viva = busca("1",'configuracionesp','c_id','c_iva');
          if(busca($_GET['id'],'crm_cotizaciones','cc_id','cc_diva') == 1){
            $ivades = 1;
            $precio = $precio/(1+($viva/100));
          }else {
            $ivades = 0;
          }

          $estg = "A";
          $costo = 0;   
          $idprod = NULL;
          $nmbprod = formmayus($_POST['producto']);
          $esprecio = busca(busca(busca($_GET['id'],'crm_cotizaciones','cc_id','cc_tablero'),'crm_tableros','ct_id','ct_cliente'),'crm_clientes','c_id','c_precio');
        }
        $inventariado = busca($idprod, 'articulos', 'a_id', 'a_inventariado');
        
        if($estg == "A" && $inventariado == "1"){
          $this->model->insertprod($_GET['id'],$idprod,$nmbprod,$cantidad,$precio,$costo,$ivades, $modelo);
          if($idprod){
            $sql='SELECT * FROM articulos_ligados WHERE al_articulo="'.$idprod.'"';
            $res=setq($sql);
            if($res -> num_rows > 0) $this->model->setdescripcion($_GET['id'],$this->model->idcd,"COMPLEMENTOS DE CADA ARTÍCULO:");
            while($rw=$res->fetch_array()){
              if($rw['al_amodelo']) $nmb = busca($rw['al_aligado'], 'articulos_variantes', 'av_modelo = "'.$rw['al_amodelo'].'" AND av_articulo', 'av_nmb');
              else $nmb = busca($rw['al_aligado'], 'articulos', 'a_id', 'a_nmb');
              $this->model->setdescripcion($_GET['id'],$this->model->idcd,number_format($rw['al_cantidad']).' '.mb_strtolower($nmb));
            }
          }
          if($combo == "M"){
            //$i=0;
            $con = 0;
            $sqlcombo = 'SELECT * FROM articulos_combos WHERE ac_articulo = "'.$idprod.'"';
            echo '<script> console.log("'.$sqlcombo.'") </script>';
            echo $sqlcombo;
            $resultcombo = setq($sqlcombo);
            while($row1 = $resultcombo->fetch_array()){
              //$i++;
              $sqlcam = 'INSERT INTO crm_cotizaciones_cambios SET cca_cotizacion = "'.$_GET['id'].'",
                                                                  cca_cotizaciond = "'.$this->model->idcd.'",
                                                                  cca_articulo = "'.$row1['ac_ahijo'].'",
                                                                  cca_cantidad = "'.$row1['ac_cantidad'].'",
                                                                  cca_cambio = "",
                                                                  cca_estatus = "N",
                                                                  cca_ugen = "'.$_SESSION['uid'].'",
                                                                  cca_fgen = "'.date('Y-m-d H:i:s').'"';
              setq($sqlcam);                                                              
              $var = busca($row1['ac_ahijo'], 'articulos_variantes','av_articulo','COUNT(*)');
              $con += $var;
              if($var == 0){
                $sqlinsv= 'INSERT INTO crm_cotizacion_variantes SET ccv_cotizacion = "'.$_GET['id'].'",
                                                                ccv_cotizaciond = "'.$this->model->idcd.'",
                                                                ccv_articulo = "'.$row1['ac_ahijo'].'",
                                                                ccv_modelo = "",
                                                                ccv_cantidad = "'.$row1['ac_cantidad'].'",
                                                                ccv_existenciaant = "0"';
                setq($sqlinsv);
              }
            } 
            if($con == 0){
              $sqlupd = 'UPDATE crm_cotizacionesd SET cdm_completo = "1" WHERE cdm_cotizacion = "'.$_GET['id'].'" AND cdm_id = "'.$this->model->idcd.'"';
              setq($sqlupd);
              $this->model->setdescripcion($_GET['id'], $this->model->idcd, "INCLUYE");
              $resultcombo2 = setq($sqlcombo);
              while($row2 = $resultcombo2->fetch_array()){
                $this->model->setdescripcion($_GET['id'], $this->model->idcd, number_format($row2['ac_cantidad'], 0)." - ".mb_strtolower(busca($row2['ac_ahijo'], 'articulos', ' a_id', 'a_nmb')));
              }
            }
            /* if($i == 0){
              $this->model->setdescripcion($_GET['id'], $this->model->idcd, "INCLUYE");
              while($row2 = $resultcombo->fetch_array()){
                $this->model->setdescripcion($_GET['id'], $this->model->idcd, number_format($row2['ac_cantidad'], 0)." - ".mb_strtolower(busca($row2['ac_ahijo'], 'articulos', ' a_id', 'a_nmb')));
              }
            } */
            $expira = busca($idprod, 'articulos', 'a_id', 'a_expira');
            $fechacot = busca($_GET['id'], 'crm_cotizaciones', 'cc_id', 'cc_ffin');
            if($expira < $fechacot){
              $sqlupd = 'UPDATE crm_cotizaciones SET cc_ffin = "'.$expira.'" WHERE cc_id = "'.$_GET['id'].'"';
              setq($sqlupd);
            } 
          }

          $diva = busca($_GET['id'],'crm_cotizaciones','cc_id','cc_diva');
          
          $this->model->calculaimporte($_GET['id']);
          /* if($combo == "M"){
            $cdmArt = busca($idprod,'crm_cotizacionesd','cdm_cotizacion = "'.$_GET['id'].'" AND cdm_articulo','cdm_id');
            $resultcombo = setq($sqlcombo);
            $this->model->setdescripcion($_GET['id'],$cdmArt,'INCLUYE');
            while($row = $resultcombo->fetch_array()){
              $var = busca($row['ac_ahijo'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
              if($var == 0)
              $this->model->setdescripcion($_GET['id'],$cdmArt,$row['ac_cantidad'].' - '.busca($row['ac_ahijo'],'articulos','a_id','a_nmb'));
            }
          } */
        }else{
          echo '<script>
                alert("El articulo no puede ser agregado por que esta en estatus inactivo o no esta inventariado.");
                  window.location.href="?modulo=cotizaciones&accion=index&id='.$_GET['id'].'";
            </script>';
        }
      } else{
        if($count && $count > 0){
          if($modelo) $cnmb = busca($idprod,'crm_cotizacionesd','cdm_cotizacion = "'.$_GET['id'].'" AND cdm_modelo = "'.$modelo.'" AND cdm_articulo','cdm_id');
          else $cnmb = busca($idprod,'crm_cotizacionesd','cdm_cotizacion = "'.$_GET['id'].'" AND cdm_articulo','cdm_id');
        }else{
          $nmbprod = formmayus($_POST['producto']);
          $cnmb = busca($nmbprod,'crm_cotizacionesd','cdm_cotizacion = "'.$_GET['id'].'" AND cdm_nmbarticulo','cdm_id');
        }

        if($cnmb){
          $sumcant = busca($cnmb,'crm_cotizacionesd','cdm_id','cdm_cantidad');
          $nuevacantidad = $sumcant + $cantidad;
          $sql = 'UPDATE crm_cotizacionesd SET cdm_cantidad = "'.$nuevacantidad.'" WHERE cdm_id = "'.$cnmb.'"';
          setq($sql);
        }else{
          $idprod = NULL;
          $lineaneg = NULL;
          if(!$costo) $costo = 0;
          if(!$precio) $precio = 0;
          $this->model->insertprod($_GET['id'],$idprod,$nmbprod,$cantidad,$precio,$costo,0, $modelo);
        }

        $this->model->calculaimporte($_GET['id']);
      }
      redirect('?modulo=cotizaciones&accion=index&id='.$_GET['id']);
    }
    function delconcepto(){
      $this->model->delconcepto($_GET['cotizacion'],$_GET['iddesc']);
      $sql = 'UPDATE crm_cotizaciones SET cc_descuento = "0", cc_montodescuento = "0" WHERE cc_id = "'.$_GET['cotizacion'].'"';
      setq($sql);
      $this->model->calculaimporte($_GET['cotizacion']);

      redirect('?modulo=cotizaciones&accion=index&id='.$_GET['cotizacion']);
    }
    function updateprod(){
      if(isset($_POST['miva']) && $_POST['miva'] == "on") $miva = 1; 
      elseif($_POST['miva'] == "1") $miva = 1;
      else $miva = 0;

      $viva = busca(1,'configuracionesp','c_id','c_iva');
      $diva = busca($_GET['cotizacion'],'crm_cotizaciones','cc_id','cc_diva');

      $precio = $_POST['precio'];
      
      //$this->model->updateprod($_GET['cotizacion'],$_GET['id'],$_POST['cantidad'],$precio,$miva);
      $this->model->updateprod($_GET['cotizacion'],$_GET['id'],'1',$precio,$miva);

      $this->model->calculaimporte($_GET['cotizacion']);

      redirect('?modulo=cotizaciones&accion=index&id='.$_GET['cotizacion']);
    }
    function setdescripcion(){
      $this->model->setdescripcion($_GET['cotizacion'],$_POST['iddescripcion'],formmayus($_POST['valuedescripcion']));

      redirect('?modulo=cotizaciones&accion=index&id='.$_GET['cotizacion'].'&last='.$_POST['iddescripcion']);
    }
    function deldescripcion(){
      $this->model->deldescripcion($_GET['cotizacion'],$_GET['iddesc']);

      redirect('?modulo=cotizaciones&accion=index&id='.$_GET['cotizacion']);

    }
    function finalcaptura(){
      if(isset($_POST['iva'])) $iva = "1"; else $iva = "0";
      if(isset($_POST['total'])) $total = "1"; else $total = "0";
      if(isset($_POST['tablaprod'])) $tablaprod = "1"; else $tablaprod = "0";
      if(isset($_POST['firma'])) $firma = "1"; else $firma = "0";
      if(isset($_POST['cuentas'])) $cuentas = "1"; else $cuentas = "0";
      if(isset($_POST['msi'])) $msi = "1"; else $msi = "0";

      $nmbagente = busca($_POST['responsable'],'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)');
      $puestoagente = busca($_POST['responsable'],'usuarios','u_id','u_puesto');
      $copiamail = busca($_POST['responsable'],'usuarios','u_id','u_mailcorp');

      $sql = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$_GET['idcotiza'].'"';
      $result = setq($sql);

      if($result->num_rows > 0){
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        $mtotal = busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_mtotal');
        $miva = busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_diva');
        while($row = $result->fetch_array()){
          if($total == "0" || $mtotal == "0"){
            $proceso = "0";
            if($total == "0" && $miva == "1") $proceso = "1";
            elseif($total == "1" && $iva == "1") $proceso = "1";
          }
          else $proceso = "1";

          if($miva == $iva && $total == $mtotal) $proceso = "0";

          if($proceso == "1"){
            if($row['cdm_iva'] == "1" && $iva == "0"){
              $precio = $row['cdm_precio'] * (1+($viva/100));
              $sqlup = 'UPDATE crm_cotizacionesd SET cdm_precio = "'.$precio.'" WHERE cdm_id = "'.$row['cdm_id'].'"';
              setq($sqlup);
            }elseif($row['cdm_iva'] == "1" && $iva == "1"){
              $precio = $row['cdm_precio'] / (1+($viva/100));
              $sqlup = 'UPDATE crm_cotizacionesd SET cdm_precio = "'.$precio.'" WHERE cdm_id = "'.$row['cdm_id'].'"';
              setq($sqlup);
            }
          } 
        }
      }
      $this->model->select($_GET['idcotiza']);
      //$this->model->SetData($_GET['idcotiza'],$_GET['tablero'],NULL,$_POST['nmbac'],$_POST['ffin'],$_POST['descripcion'],$iva,$total,"N","0",$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['responsable'],$_POST['descuento'],$nmbagente,$puestoagente,$copiamail,$this->model->direnvio,$this->model->observadir,NULL,$_POST['almacen']);
      //$this->model->updatecotiza();
      //$this->model->calculaimporte($_GET['idcotiza']);

      $this->model->SetData($_GET['idcotiza'],$_GET['tablero'],$this->model->folio,$_POST['nmbac'],$_POST['ffin'],$_POST['descripcion'],$iva,$total,"A","0",$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['responsable'],$_POST['descuento'],$_POST['montodescuento'],$nmbagente,$puestoagente,$copiamail,$this->model->direnvio,$this->model->observadir,NULL,$_POST['almacen'], "0");
      $this->model->finalcotiza($_POST['mensaje']); 
      //$this->model->sendmailcotiza($_GET['idcotiza']);

      $sqltab = 'UPDATE crm_tableros SET ct_nnegociacion = "2" WHERE ct_id = "'.$_GET['tablero'].'"';
      setq($sqltab);

      $sqlhist = 'INSERT INTO crm_cotizaciones_historial SET cch_cotizacion = "'.$_GET['idcotiza'].'", cch_fecha = "'.date('Y-m-d H:i:s').'", cch_user = "'.$_SESSION['uid'].'", cch_descripcion = "COTIZACIÓN ENVIADA A CLIENTE"';
      setq($sqlhist);

      /* echo '
      <script>
        $.ajax({
          url: "task/notificacionpush.php",
          method: "POST",
          data: {"grupo": "FINANZAS",
                  "estatus": "A",
                  "cotizacion": "'.$_GET['idcotiza'].'"},
        }).done(function(data){
        });          
        </script>
      '; */

      echo '
      <script>
        $.ajax({
          url: "task/notificacionpush.php",
          method: "POST",
          data: {"grupo": "VENTAS",
                  "estatus": "A",
                  "cotizacion": "'.$_GET['idcotiza'].'"},
        }).done(function(data){
        });          
        </script>
      '; 
      
      if(isset($_POST['checkmail'])){
        if(isset($_POST['cuentas'])) $sendcuenta = "1";
        else $sendcuenta = "0";
        echo '
      <script>
        $.ajax({
          url: "correocliente.php",
          method: "POST",
          data: {
            "cotizacion": "'.$_GET['idcotiza'].'",
            "correo": "'.$_POST['correo'].'",
            "copia": "'.$_POST['copiamail'].'",
            "cuentas": "'.$sendcuenta .'",
          },
        }).done(function(data){
          window.location.href = "?modulo=cotizaciones&accion=index&id='.$_GET['idcotiza'].'";
        });          
        </script>
      ';
        /* if(isset($this->model->responsable)) $send = checkmail($this->model->responsable);
        else $send = "0"; */

        //if($send == "1")
        /* $this->model->sendmailcotiza($_GET['idcotiza']); */
        /* else 
          echo '<script>
            alert("No es posible enviar la cotización al destinatario por correo, debido a que no esta configurado correctamente el correo empresarial.\nPor favor termina de capturar la infromación para poder enviar correctamente la cotización");
            window.location.href="?modulo=cotizaciones&accion=index&id='.$_GET['idcotiza'].'";
          </script>'; */

      }
      
      if(!isset($_POST['checkmail']))redirect('?modulo=cotizaciones&accion=index&id='.$_GET['idcotiza']);
    }
    function finarticuloscaptura(){
      $cotiza = $_GET['idcotiza'];

      $sql = 'UPDATE crm_cotizaciones SET cc_estatus = "D" WHERE cc_id = "'.$cotiza.'"';
      setq($sql);

      $sqlhist = 'INSERT INTO crm_cotizaciones_historial SET cch_cotizacion = "'.$cotiza.'", cch_fecha = "'.date('Y-m-d H:i:s').'", cch_user = "'.$_SESSION['uid'].'", cch_descripcion = "FIN DE LA CAPTURA DE ARTÍCULOS"';
        setq($sqlhist);

      redirect('?modulo=cotizaciones&accion=index&id='.$_GET['idcotiza']);
    }
    function setadjunto(){
      if($_POST['typedoc'.$_POST['sended']] == "D"){
        $document = $_POST['adjunto'];
      }else{
        $target_path = "docs/adjcot/";
        $extension = pathinfo($_FILES['fileadj']['name'], PATHINFO_EXTENSION);

        $target_path = $target_path.$_FILES['fileadj']['name'];
        /* echo 'archivo: '.$_FILES['fileadj']['tmp_name'].' ruta: '.$target_path;
        die(); */
        if(move_uploaded_file($_FILES['fileadj']['tmp_name'], $target_path)) {
            $document = $_FILES['fileadj']['name'];
        } else{
            echo alert_back("Error: El documento no pudo ser adjunto");
             redirect("?modulo=cotizaciones&accion=index&id=".$_GET['id']); 
        }
      }

      $sql = 'UPDATE crm_cotizaciones SET cc_adjunto = "'.$document.'" WHERE cc_id = "'.$_GET['id'].'"';
      setq($sql);

      redirect("?modulo=cotizaciones&accion=index&id=".$_GET['id']);
    }
    function deladjunto(){
      $sql = 'UPDATE crm_cotizaciones SET cc_adjunto = NULL WHERE cc_id = "'.$_GET['id'].'"';
      setq($sql);

      redirect("?modulo=cotizaciones&accion=index&id=".$_GET['id']);
    }
    function sendmailcotiza(){
      /* foreachdie(); */
    if(isset($_POST['cuentas'])) $sendcuenta = "1";
    else $sendcuenta = "0";
    echo '
    <script>
        $.ajax({
          url: "correocliente.php",
          method: "POST",
          data: {
            "cotizacion": "'.$_GET['id'].'",
            "correo": "'.$_POST['correo'].'",
            "copia": "'.$_POST['copiamail'].'",
            "cuentas": "'.$sendcuenta.'"
          },
        }).done(function(datax){
          console.log(datax);
          window.location.href = "?modulo=cotizaciones&accion=index&id='.$_GET['id'].'";
        });          
        </script>
      ';
    /* $this->model->sendmailcotiza($_GET['id']); */
    /* redirect("?modulo=cotizaciones&accion=index&id=".$_GET['id']); */
    }
    function clonar(){
      $this->model->select($_GET['idcotiza']);

      if($_POST['setclone'] == "O"){
        $acrof = busca(1,'empresas','e_id','e_siglas');
        $folio = getmax('ct_folio','crm_tableros');
        $cliente = getIDcliente($_POST['cliente']);
        if(!$cliente){
          die(alert_back('Error: el cliente no esta registrado',true));
        }
        $fini = explode("T",$_POST['finit']);

        include_once('tableros.php');
        $table = new modeltableros();

        if($_POST['tablero']){
          $this->model->tablero = $_POST['tablero'];
        }else{
          $table->setdata(NULL,$_POST['nmbt'],$folio,$cliente,$this->model->responsable,$fini[0],$fini[1],1,date('Y-m-d',strtotime($fini[0].' +15 days')),1,"R", "0");
          $table->insert();
          $this->model->tablero = $table->idt;
        }

        $this->model->destino = busca($cliente,'crm_clientes','c_id','c_nmb');
        $this->model->telefono = busca($cliente,'crm_clientes','c_id','c_telefono1');
        $this->model->correo = busca($cliente,'crm_clientes','c_id','c_correo1');
      }
      else{
        $this->model->destino = $_POST['destino'];
        $this->model->telefono = $_POST['telefono'];
        $this->model->correo = $_POST['correo'];
      }

      $acronimo = busca(1,'empresas','e_id','e_siglas');
      $sqlm = 'SELECT COUNT(*) FROM crm_cotizaciones WHERE cc_folio LIKE "CT'.$acronimo.'%"';
      $resultm = setq($sqlm);
      list($maxcot) = $resultm->fetch_array();
      $maxcot++;
      $foliocot = 'CT'.$acronimo.str_pad($maxcot,6,"0",STR_PAD_LEFT);

      $this->model->uuid = $this->model->guidv4();
      $this->model->fini = date('Y-m-d');
      //$this->model->ffin = date('Y-m-d',strtotime('+15 days'));
      $this->model->ffin = $this->model->ffin;
      $this->model->foliocot = $foliocot;
      $this->model->nmbac = $_POST['nmbac'];
      $this->model->descripcion = $_POST['descripcion'];

      
      if($this->model->estatus != "V") {
        $sqlupd = 'UPDATE crm_cotizaciones SET cc_estatus = "C" WHERE cc_id = "'.$this->model->id.'"';
        setq($sqlupd);
      }

      $this->model->estatus = "R";
      if(isset($_POST['iva'])) $this->model->diva = "1"; else $this->model->diva = "0";
      if(isset($_POST['total'])) $this->model->mtotal = "1"; else $this->model->mtotal = "0";

      $this->model->insertcotiza();
      $this->model->idcotizacion = $this->model->idc;

      $this->model->resultcd($_GET['idcotiza']);
      $viva = busca("1",'configuracionesp','c_id','c_iva');
      $divaclone = busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_diva');
      while($rowd = $this->model->resultcd->fetch_array()){
        $precio = $rowd['cdm_precio'];
        if($divaclone != $this->model->diva){
          if($rowd['cdm_iva'] == "1" && $this->model->diva == "1") {
            $precio = $rowd['cdm_precio'] / (1+($viva/100));
          }elseif($rowd['cdm_iva'] == "1" && $this->model->diva == "0") {
            $precio = $rowd['cdm_precio'] * (1+($viva/100));
            //$this->model->calculaimporte($this->model->idcotizacion,1);
          }
        }
        $this->model->insertprod($this->model->idcotizacion,$rowd['cdm_articulo'],$rowd['cdm_nmbarticulo'],$rowd['cdm_cantidad'],$precio,$rowd['cdm_costo'],$rowd['cdm_iva'], $rowd['cdm_modelo']);
        $sqlcd = 'SELECT * FROM crm_cotizacionesdd WHERE cdd_cotizacion = "'.$_GET['idcotiza'].'" AND cdd_idd = "'.$rowd['cdm_id'].'"';
        $resultcdd = setq($sqlcd);
        while($rowcdd = $resultcdd->fetch_array()){
          $this->model->setdescripcion($this->model->idcotizacion,$this->model->idcd,mb_strtolower(strip_tags(trim($rowcdd['cdd_nmb']))));
        } 
        $sqlcv = 'SELECT * FROM crm_cotizacion_variantes WHERE ccv_cotizacion = "'.$_GET['idcotiza'].'" AND ccv_cotizaciond = "'.$rowd['cdm_id'].'"';
        $resultcv = setq($sqlcv);
        while($rowcv = $resultcv -> fetch_array()){
          $this->model->setvariantecmb($this->model->idcotizacion, $this->model->idcd, $rowcv['ccv_articulo'], $rowcv['ccv_modelo'], $rowcv['ccv_cantidad']);
        }
        $sqlcb = 'SELECT * FROM crm_cotizaciones_cambios WHERE cca_cotizacion = "'.$_GET['idcotiza'].'" AND cca_cotizaciond = "'.$rowd['cdm_id'].'" AND cca_estatus = "N"';
        $resultcb = setq($sqlcb);
        while($rowcb = $resultcb -> fetch_array()){
          $this->model->setcambioscmb($this->model->idcotizacion, $this->model->idcd, $rowcb['cca_articulo'], $rowcb['cca_cantidad'], $_SESSION['uid'], date('Y-m-d H:i:s'));
        }
      }

      $sql = 'SELECT * FROM crm_cotizaciones_condiciones WHERE cf_cotizacion = "'.$_GET['idcotiza'].'"';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $sql2 = 'INSERT INTO crm_cotizaciones_condiciones SET
                cf_condicion = "'.$row['cf_condicion'].'",
                cf_cotizacion = "'.$this->model->idcotizacion.'",
                cf_descripcion = "'.$row['cf_descripcion'].'",
                cf_estatus = "'.$row['cf_estatus'].'",
                cf_orden = "'.$row['cf_orden'].'"';
        setq($sql2);
      }

      //$this->model->calculaimporte($this->model->idcotizacion);
      if($this->model->diva == "1") $this->model->calculaimporte($this->model->idcotizacion);
      else $this->model->calculaimporte($this->model->idcotizacion,1);

      

      redirect("?modulo=cotizaciones&accion=index&id=".$this->model->idcotizacion);
    }
    function cancelar(){
      $tablero = busca($_GET['id'],'crm_cotizaciones','cc_id','cc_tablero');
      $sqlc = 'UPDATE crm_cotizaciones SET cc_estatus = "C" WHERE cc_id = "'.$_GET['id'].'"';
      $actualizacionExitosa = setq($sqlc);

      if ($actualizacionExitosa) {
      echo '
      <script>
        $.ajax({
          url: "task/notificacionpush.php",
          method: "POST",
          data: {"grupo": "FINANZAS",
                  "estatus": "C",
                  "cotizacion": "'.$_GET['id'].'"},
        }).done(function(data){ 
        });          
        </script>
      ';

      echo '
        <script>
        $.ajax({
          url: "query/correocotizaciones.php",
          method: "POST",
          data: {"cotizacion": "'.$_GET['id'].'",
                  "correoop": "3"
                },
        }).done(function(data){
          window.location("?modulo=tableros&accion=show&id='.$tablero.'");
        });          
        </script>';
      }


      /* redirect("?modulo=tableros&accion=show&id=".$tablero); */
    }
    function vender(){
      $this->model->select($_GET['id']);
      $this->model->cliente = busca($this->model->tablero,'crm_tableros','ct_id','ct_cliente');
      
        include_once('remisiones.php');
        $remis = new modelremisiones();
        $acronimo = busca(1,'empresas','e_id','e_siglas');
        $acrof = busca(1,'empresas','e_id','e_siglas').'-RM';
        $folioint = getmax('r_folio','remisiones');
        if($folioint == "1"){
          $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
        }
        if($this->model->montodescuento != 0){
          $descuento = ($this->model->montodescuento*100)/$this->model->importe;
        } else {
          $descuento = $this->model->descuento;
        }

        $this->model->cliente = busca($this->model->tablero,'crm_tableros','ct_id','ct_cliente');
        $remis->setdata(NULL,$this->model->tablero,$folioint,$this->model->cliente,$this->model->almacen,$descuento,$this->model->nmb,$this->model->maildestino,$this->model->responsable,date('Y-m-d H:i:s', strtotime('+ 10 days')),$this->model->diva, $this->model->precioenvio);
        $remis->insert();

        $sqlcd = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$this->model->id.'" ORDER BY cdm_id';
        $resulcd = setq($sqlcd);
        $viva = busca("1",'configuracionesp','c_id','c_iva');
        $diva = busca($remis->id,'remisiones','r_id','r_diva');
        while($rowd = $resulcd->fetch_array()){
            $idrec = getmax('rd_id','remisionesd','rd_remision');

            if($rowd['cdm_iva'] == "1" && $diva == "1"){
              //$preciof = $rowd['cdm_precio'] / (1+($viva/100));
              $preciof = $rowd['cdm_precio'];
            }else $preciof = $rowd['cdm_precio'];
            $remis->setDatad($remis->id, $idrec, $rowd['cdm_cantidad'], $rowd['cdm_articulo'],$preciof,$rowd['cdm_iva'],$rowd['cdm_costo'],$rowd['cdm_nmbarticulo'], $rowd['cdm_modelo']);
            $remis->insertdetalle();

            $sqlc = 'SELECT * FROM crm_cotizacionesc
            WHERE ccc_cotizacion = "'.$this->model->id.'" AND ccc_cotizaciond = "'.$rowd['cdm_id'].'"
            ORDER BY
              CASE
                WHEN ccc_ligado IS NULL OR ccc_ligado = "" THEN 0
                ELSE 1
              END, ccc_ligado;
            ';
            $resultc = setq($sqlc);
            $actualizar = array();
            $io = 0;
            while($rowc = $resultc->fetch_array()){
              $remis->setDatac($remis->id, $idrec, $rowc['ccc_articulo'], $rowc['ccc_modelo'], $rowc['ccc_numero'], $rowc['ccc_tipoenvio'], $rowc['ccc_paqueteria'], $rowc['ccc_sucursal'], $rowc['ccc_combo'], "N", $rowc['ccc_observacion'], $rowc['ccc_embarque'], $rowc['ccc_fenvio'], $rowc['ccc_guia'], $rowc['ccc_ligado']);
              $remis->insertdetallec();

              $idremisionc = getmax('rc_id','remisionesc',false, false);

              if(empty($rowc['ccc_ligado'])){
                $actualizar[$io][0] = $rowc['ccc_id'];
                $actualizar[$io][1] = $idremisionc;
                $io++;
              } 
            }

              for($l = 0; $l < count($actualizar); $l++){
                $cccid = $actualizar[$l][0];
                $rcid = $actualizar[$l][1];

                $sqldc = 'UPDATE remisionesc SET
                        rc_ligado = "'.$rcid.'"
                        WHERE rc_ligado = "'.$cccid.'" AND rc_remision = "'.$remis->id.'" AND rc_remisiond = "'.$idrec.'"';
                setq($sqldc);
              }
        }

       

        $remis->calculaimporte($remis->id);

        $redirect = "?modulo=remisiones&accion=show&id=".$remis->id;
      

      $sqlupd = 'UPDATE crm_cotizaciones SET cc_estatus = "V", cc_remision = "'.$remis->id.'"
              WHERE cc_id = "'.$_GET['id'].'"';
      $actualizacionExitosa = setq($sqlupd);

      if ($actualizacionExitosa) {
      $sqlhist = 'INSERT INTO crm_cotizaciones_historial SET cch_cotizacion = "'.$this->model->idc.'", cch_fecha = "'.date('Y-m-d H:i:s').'", cch_user = "'.$_SESSION['uid'].'", cch_descripcion = "COTIZACIÓN VENDIDA"';
      setq($sqlhist);
      echo '
      <script>
        $.ajax({
          url: "task/notificacionpush.php",
          method: "POST",
          data: {"grupo": "FINANZAS",
                  "estatus": "A",
                  "cotizacion": "'.$this->model->id.'"},
        }).done(function(data){
        });          
        </script>
      ';

      echo '
        <script>
        $.ajax({
          url: "query/correocotizaciones.php",
          method: "POST",
          data: {"cotizacion": "'.$this->model->id.'",
                  "correoop": "4"
                },
        }).done(function(data){
        });          
        </script>';
      }

      redirect($redirect);

    }
    function insertcondicion(){

      if($_POST['condicion'] != "" && isset($_POST['condicion'])){
        $this->model->setDataCondicion($_POST['condicion']);
        $this->model->insertcondicion("0");
      }

      //die('popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$_GET['idc'].'');
      redirect('popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$_GET['idc'].'');
    }
    function insertnota(){
      
      if($_POST['condicion'] != "" && isset($_POST['condicion'])){
        $this->model->setDataCondicion($_POST['condicion']);
        $this->model->insertnota();
      }
      //die('popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$_GET['idc'].'');
      redirect('popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$_GET['idc'].'');
    }
    function deletecondicion(){
      $id = $_GET['id'];
      $idc = $_GET['idc'];
      $this->model->deletecondicion();
      redirect('popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$_GET['idc'].'');
    }
    function actualizacondicion(){
      if($_POST['condicion'] != "" && isset($_POST['condicion'])){
        $this->model->setDataCondicion($_POST['condicion']);
        $this->model->actualizacondicion();
      }
      redirect('popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$_GET['idc'].'');
    }
    function seleccionarcondiciones(){
      $sql = 'SELECT * FROM condicionesc';
      $result = setq($sql);
      while($row = $result -> fetch_array()){
        if(isset($_POST['con'.$row['c_id']])){
          $check = busca($_GET['idc'], 'crm_cotizaciones_condiciones', 'cf_condicion = "'.$row['c_id'].'" AND cf_cotizacion','cf_id');
          if(!$check){
            $_POST['estatus'] = "A";
            $_POST['orden'] = getmax('cf_orden','crm_cotizaciones_condiciones', 'cf_cotizacion = "'.$_GET['idc'].'"');
            $this->model->setDataCondicion($row['c_nmb']);
            $this->model->insertcondicion($row['c_id']);
          }
        } else {
          $check = busca($_GET['idc'], 'crm_cotizaciones_condiciones', 'cf_condicion = "'.$row['c_id'].'" AND cf_cotizacion','cf_id');
          if($check){
            $_GET['id'] = $check;
            $this->model->deletecondicion();
          }
        }
      }

      redirect('popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$_GET['idc'].'');
    }
    function nuevacondicion(){

      $this->model->insertnuevac();
      $_POST['estatus'] = "A";
      $orden = busca($_GET['idc'],'crm_cotizaciones_condiciones','cf_cotizacion','MAX(cf_orden)');
      if($orden != "") $orden++;
      else $orden = 0;
      $_POST['orden'] = $orden;
      $this->model->setDataCondicion($_POST['nomc']);
      $this->model->insertcondicion('0');
      redirect('popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$_GET['idc'].'');
    }
    function updateconc(){

      $this->model->updateconc($_POST['cotizacion'],$_POST['idc'], clearvmayus($_POST['nmbp']));

      redirect('?modulo=cotizaciones&accion=index&id='.$_POST['cotizacion']);
    }
    function cambiarcantidad(){
      if(isset($_REQUEST['cantidad']) && isset($_REQUEST['id']) && isset($_REQUEST['cotizacion'])){
        $sql = 'UPDATE crm_cotizacionesd SET
                cdm_cantidad = "'.$_REQUEST['cantidad'].'"
                WHERE cdm_id = "'.$_REQUEST['id'].'"';
        setq($sql);

        $this->model->calculaimporte($_REQUEST['cotizacion']);
        
      } 

      redirect("?modulo=cotizaciones&accion=index&id=".$_REQUEST['cotizacion']);
    }
    function cambiarln(){
      if(isset($_REQUEST['linean'])){
        $sql = 'UPDATE crm_cotizacionesd SET
                cdm_lineaneg = "'.$_REQUEST['linean'].'"
                WHERE cdm_id = "'.$_GET['id'].'"';
        setq($sql);
      } 
      redirect("?modulo=cotizaciones&accion=index&id=".$_GET['cotizacion']);
    }
    function seleccionarenvio(){
      //foreachdie();
      $cotiza = $_GET['idcotiza'];
      $direccion = busca($cotiza, 'crm_cotizaciones', 'cc_id', 'cc_direnvio'); 
      $cp = busca($direccion, 'crm_direcciones', 'cd_id', 'cd_cp');
      $n= 0;
      //$suc = busca($cp, 'paqueterias_cobertura', 'pc_cp', 'pc_sucursal');
      $k=1;
      $sql = 'SELECT * FROM crm_cotizacionesd INNER JOIN articulos ON cdm_articulo = a_id WHERE cdm_cotizacion = "'.$cotiza.'"';
      $result = setq($sql);
      while($row = $result -> fetch_array()){
        $valida = busca($cotiza, 'crm_cotizacionesc', 'ccc_cotizaciond = "'.$row['cdm_id'].'" AND ccc_cotizacion', 'ccc_id');
        $l=1;
        for($i=1;$i<=$row['cdm_cantidad']; $i++){
          if($row['a_tipoprod'] == "M"){
            $sqlvar = 'SELECT * FROM crm_cotizacion_variantes WHERE ccv_cotizacion = "'.$cotiza.'" AND ccv_cotizaciond = "'.$row['cdm_id'].'"';
            $resultvar = setq($sqlvar);
            while($rowvar =$resultvar -> fetch_array()){
              for($j = 1; $j <= $rowvar['ccv_cantidad']; $j++){
                if($_POST['tipo'.$k] == "E" || $_POST['tipo'.$k] == "D"){
                  $n++;
                  $pqt = $_POST['paqueteria'.$k];
                } else if($_POST['tipo'.$k] == "O") {
                  $pqt = $_POST['paqueteriaOcurre'.$k];
                } else {
                  $pqt = "0";
                }
                if($_POST['tipo'.$k] == "O") {
                  $sucins = $_POST['sucursal'.$k];
                  $n++;
                }
                else $sucins = "0";
                if($_POST['tipo'.$k] == "C") $estatus = "P";
                else $estatus = "N";
                /* $numero = getmax('ccc_numero', 'crm_cotizacionesc WHERE ccc_combo = "'.$l.'" 
                          AND ccc_cotizaciond = "'.$row['cdm_id'].'" 
                          AND ccc_articulo = "'.$rowvar['ccv_articulo'].'"', false, true); */
                if($valida){
                  $sqlins = 'UPDATE crm_cotizacionesc SET ccc_tipoenvio = "'.$_POST['tipo'.$k].'",
                                                          ccc_paqueteria = "'.$pqt.'",
                                                          ccc_sucursal = "'.$sucins.'",
                                                          ccc_estatus = "'.$estatus.'"
                                                          ccc_fenvio = "'.$_POST['fenvio'.$k].'"
                             WHERE ccc_cotizacion = "'.$cotiza.'" AND ccc_cotizaciond = "'.$row['cdm_id'].'" AND 
                                  ccc_articulo = "'.$rowvar['ccv_articulo'].'" AND
                                  ccc_modelo = "'.$rowvar['ccv_modelo'].'" AND 
                                  ccc_numero = "'.$j.'" AND 
                                  ccc_combo = "'.$l.'"';
                  setq($sqlins);
                } else {
                  $sqlins = 'INSERT INTO crm_cotizacionesc SET ccc_cotizacion = "'.$cotiza.'",
                                                              ccc_cotizaciond = "'.$row['cdm_id'].'",
                                                              ccc_articulo = "'.$rowvar['ccv_articulo'].'",
                                                              ccc_modelo = "'.$rowvar['ccv_modelo'].'",
                                                              ccc_numero= "'.$j.'",
                                                              ccc_tipoenvio= "'.$_POST['tipo'.$k].'",
                                                              ccc_paqueteria= "'.$pqt.'",
                                                              ccc_sucursal= "'.$sucins.'",
                                                              ccc_fenvio = "'.$_POST['fenvio'.$k].'",
                                                              ccc_combo = "'.$l.'",
                                                              ccc_estatus = "'.$estatus.'"';
                  setq($sqlins);
                  /* if($_POST['tipo'.$k] == "C" || $_POST['tipo'.$k] == "P"){ */
                    $cccid = getmax('ccc_id', 'crm_cotizacionesc', false, false);
                    $sqllig = 'SELECT * FROM articulos_ligados WHERE al_articulo = "'.$rowvar['ccv_articulo'].'"';
                    $resultlig = setq($sqllig); 
                    while($rowlig = $resultlig -> fetch_array()){
                      $sqlinslig = 'INSERT INTO crm_cotizacionesc SET
                          ccc_cotizacion = "'.$cotiza.'",
                          ccc_cotizaciond = "'.$row['cdm_id'].'",
                          ccc_articulo = "'.$rowlig['al_aligado'].'",
                          ccc_modelo = "'.$rowlig['al_amodelo'].'",
                          ccc_numero = "'.$j.'",
                          ccc_tipoenvio = "'.$_POST['tipo'.$k].'",
                          ccc_paqueteria = "'.$pqt.'",
                          ccc_sucursal = "'.$sucins.'",
                          ccc_combo = "'.$l.'",
                          ccc_estatus = "'.$estatus.'",
                          ccc_fenvio = "'.$_POST['fenvio'.$k].'",
                          ccc_ligado = "'.$cccid.'"';
                      setq($sqlinslig);
                    }
                  /* } */

                
                } 
                //echo $sqlins.'<br>';           
                
                //echo 'K: '.$k."pqt: ".$pqt." pqtOcurre: ".$_POST['paqueteriaOcurre'.$k].' tipo: '.$_POST['tipo'.$k].'<br>';
                $k++;
              }
            }
            $l++;
          } else {
            if(!$row['cdm_modelo']){
              if($_POST['tipo'.$k] == "E" || $_POST['tipo'.$k] == "D"){
                $pqt = $_POST['paqueteria'.$k];
                $n++;
              }else if($_POST['tipo'.$k] == "O") {
                $pqt = $_POST['paqueteriaOcurre'.$k];
              } else {
                $pqt = "0";
              }
              if($_POST['tipo'.$k] == "O") {
                $sucins = $_POST['sucursal'.$k];
                $n++;
              }
              else $sucins = "0";
              if($_POST['tipo'.$k] == "C") $estatus = "P";
              else $estatus = "N";
              //echo 'K: '.$k."pqt: ".$pqt." pqtOcurre: ".$_POST['paqueteriaOcurre'.$k].' tipo: '.$_POST['tipo'.$k].'<br>';
              if($valida){
                $sqlins2 = 'UPDATE crm_cotizacionesc SET ccc_tipoenvio = "'.$_POST['tipo'.$k].'",
                                                        ccc_paqueteria = "'.$pqt.'",
                                                        ccc_sucursal = "'.$sucins.'",
                                                        ccc_estatus = "'.$estatus.'",
                                                        ccc_fenvio = "'.$_POST['fenvio'.$k].'"
                            WHERE ccc_cotizacion = "'.$cotiza.'" AND ccc_cotizaciond = "'.$row['cdm_id'].'" AND 
                                ccc_articulo = "'.$row['a_id'].'" AND
                                ccc_modelo = "" AND 
                                ccc_numero = "'.$i.'" AND 
                                ccc_combo = "0"';
                setq($sqlins2);
              }else {
                $sqlins2 = 'INSERT INTO crm_cotizacionesc SET ccc_cotizacion = "'.$cotiza.'",
                                      ccc_cotizaciond = "'.$row['cdm_id'].'",
                                      ccc_articulo = "'.$row['a_id'].'",
                                      ccc_modelo = "",
                                      ccc_numero= "'.$i.'",
                                      ccc_tipoenvio= "'.$_POST['tipo'.$k].'",
                                      ccc_paqueteria= "'.$pqt.'",
                                      ccc_sucursal = "'.$sucins.'",
                                      ccc_fenvio = "'.$_POST['fenvio'.$k].'",
                                      ccc_combo = "0",
                                      ccc_estatus = "'.$estatus.'"';
                setq($sqlins2);
                /* if($_POST['tipo'.$k] == "C" || $_POST['tipo'.$k] == "P"){ */
                  $cccid = getmax('ccc_id', 'crm_cotizacionesc', false, false);
                  $sqllig = 'SELECT * FROM articulos_ligados WHERE al_articulo = "'.$row['a_id'].'"';
                  $resultlig = setq($sqllig); 
                  while($rowlig = $resultlig -> fetch_array()){
                    $sqlinslig = 'INSERT INTO crm_cotizacionesc SET
                        ccc_cotizacion = "'.$cotiza.'",
                        ccc_cotizaciond = "'.$row['cdm_id'].'",
                        ccc_articulo = "'.$rowlig['al_aligado'].'",
                        ccc_modelo = "",
                        ccc_numero = "'.$i.'",
                        ccc_tipoenvio = "'.$_POST['tipo'.$k].'",
                        ccc_paqueteria = "'.$pqt.'",
                        ccc_sucursal = "'.$sucins.'",
                        ccc_combo = "0",
                        ccc_estatus = "'.$estatus.'",
                        ccc_fenvio = "'.$_POST['fenvio'.$k].'",
                        ccc_ligado = "'.$cccid.'"';
                    setq($sqlinslig);
                  }
                /* }  */
              }   
              //echo $sqlins2.'<br>';
              
              //echo 'K: '.$k."pqt: ".$pqt." pqtOcurre: ".$_POST['paqueteriaOcurre'.$k].' tipo: '.$_POST['tipo'.$k].'<br>';
              $k++;
            } else {
              if($_POST['tipo'.$k] == "E" || $_POST['tipo'.$k] == "D"){
                $pqt = $_POST['paqueteria'.$k];
                $n++;
              }else if($_POST['tipo'.$k] == "O") {
                $pqt = $_POST['paqueteriaOcurre'.$k];
              } else {
                $pqt = "0";
              }
              if($_POST['tipo'.$k] == "O") {
                $sucins = $_POST['sucursal'.$k];
                $n++;
              }
              else $sucins = "0";
              if($_POST['tipo'.$k] == "C") $estatus = "P";
              else $estatus = "N";
              //echo 'K: '.$k."pqt: ".$pqt." pqtOcurre: ".$_POST['paqueteriaOcurre'.$k].' tipo: '.$_POST['tipo'.$k].'<br>';
              if($valida){
                $sqlins2 = 'UPDATE crm_cotizacionesc SET ccc_tipoenvio = "'.$_POST['tipo'.$k].'",
                                                        ccc_paqueteria = "'.$pqt.'",
                                                        ccc_sucursal = "'.$sucins.'",
                                                        ccc_estatus = "'.$estatus.'",
                                                        ccc_fenvio = "'.$_POST['fenvio'.$k].'"
                            WHERE ccc_cotizacion = "'.$cotiza.'" AND ccc_cotizaciond = "'.$row['cdm_id'].'" AND 
                                ccc_articulo = "'.$row['a_id'].'" AND
                                ccc_modelo = "'.$row['cdm_modelo'].'" AND 
                                ccc_numero = "'.$i.'" AND 
                                ccc_combo = "0"';
                setq($sqlins2);                                
              }else {
                $sqlins2 = 'INSERT INTO crm_cotizacionesc SET ccc_cotizacion = "'.$cotiza.'",
                                      ccc_cotizaciond = "'.$row['cdm_id'].'",
                                      ccc_articulo = "'.$row['a_id'].'",
                                      ccc_modelo = "'.$row['cdm_modelo'].'",
                                      ccc_numero= "'.$i.'",
                                      ccc_tipoenvio= "'.$_POST['tipo'.$k].'",
                                      ccc_paqueteria= "'.$pqt.'",
                                      ccc_fenvio = "'.$_POST['fenvio'.$k].'",
                                      ccc_combo = "0",
                                      ccc_estatus = "'.$estatus.'"';
                 setq($sqlins2);
                /* if($_POST['tipo'.$k] == "C" || $_POST['tipo'.$k] == "P"){ */
                  $cccid = getmax('ccc_id', 'crm_cotizacionesc', false, false);
                  $sqllig = 'SELECT * FROM articulos_ligados WHERE al_articulo = "'.$row['a_id'].'"';
                  $resultlig = setq($sqllig); 
                  while($rowlig = $resultlig -> fetch_array()){
                    $sqlinslig = 'INSERT INTO crm_cotizacionesc SET
                        ccc_cotizacion = "'.$cotiza.'",
                        ccc_cotizaciond = "'.$row['cdm_id'].'",
                        ccc_articulo = "'.$rowlig['al_aligado'].'",
                        ccc_modelo = "'.$rowlig['al_amodelo'].'",
                        ccc_numero = "'.$i.'",
                        ccc_tipoenvio = "'.$_POST['tipo'.$k].'",
                        ccc_paqueteria = "'.$pqt.'",
                        ccc_sucursal = "'.$sucins.'",
                        ccc_combo = "0",
                        ccc_estatus = "'.$estatus.'",
                        ccc_fenvio = "'.$_POST['fenvio'.$k].'",
                        ccc_ligado = "'.$cccid.'"';
                    setq($sqlinslig);
                  }
                /* }       */                
              }   
              //echo $sqlins2.'<br>';
              
              //echo 'K: '.$k."pqt: ".$pqt." pqtOcurre: ".$_POST['paqueteriaOcurre'.$k].' tipo: '.$_POST['tipo'.$k].'<br>';
              $k++;
            }
          }
        }
      }
      /* if($sucins != "0" && $sucins != ""){ //Actualiza la dirección a la de la sucursal seleccionada
        $calle = busca($sucins, 'paqueterias_sucursales', 'ps_id', 'ps_direccion');
        $colonia = busca($sucins, 'paqueterias_sucursales', 'ps_id', 'ps_plaza');
        $cp = busca($sucins, 'paqueterias_sucursales', 'ps_id', 'ps_cp');
        $sqlupd = 'UPDATE crm_direcciones SET cd_calle = "'.$calle.' '.$colonia.'", cd_colonia = "", cd_municipio = "", cd_cp = "'.$cp.'" WHERE cd_id = "'.$direccion.'"';
        setq($sqlupd);
      } */
      $envio = 0;
      $result = setq($sql);
      while($row = $result -> fetch_array()){
        if($row['a_tipoprod'] == "M"){
          $sqlcmb = 'SELECT * FROM crm_cotizaciones_cambios WHERE cca_cotizacion = "'.$cotiza.'"';
          $resultcmb = setq($sqlcmb);
          while($rowcmb = $resultcmb -> fetch_array()){
            $ntar = busca($rowcmb['cca_articulo'], 'articulos', 'a_id', 'a_tarifa');
            if($ntar){
              $tarifa = busca($ntar, 'tarifas_envios', 'te_id', 'te_costo')*$rowcmb['cca_cantidad']*$row['cdm_cantidad'];
              if($tarifa) $envio+= floatval($tarifa);
            }
          }
        } else {
          if($row['a_tarifa']){
            $tarifa = busca($row['a_tarifa'], 'tarifas_envios', 'te_id','te_costo')*$row['cdm_cantidad'];
            if($tarifa) $envio+= floatval($tarifa);
          }
        }
      }
      if($n == 0){
        $estatus = "P";
        $url= '?modulo=cotizaciones&accion=index&id='.$_GET['idcotiza'];
      }
      else {
        $estatus = "L";
        $tablero = busca($_GET['idcotiza'], 'crm_cotizaciones', 'cc_id', 'cc_tablero');
        $url= '?modulo=tableros&accion=show&id='.$tablero;
      }
      $envio = 0;
      $sqlupd = 'UPDATE crm_cotizaciones SET cc_estatus = "'.$estatus.'", cc_precioenvio = "'.$envio.'" WHERE cc_id = "'.$_GET['idcotiza'].'"';
      $consulta = setq($sqlupd);
      if($consulta && $n > 0){
        echo '
        <script>
        $.ajax({
          url: "task/notificacionpush.php",
          method: "POST",
          async: false, // Hace la llamada AJAX síncrona
          data: {"grupo": "LOGISTIC",
                  "estatus": "L",
                  "cotizacion": "'.$_GET['idcotiza'].'"},
        }).done(function(data){
          console.log(data);
        });          
        </script>';
      } 
      $sql = 'INSERT INTO crm_cotizaciones_historial SET cch_cotizacion = "'.$_GET['idcotiza'].'", cch_fecha = "'.date('Y-m-d H:i:s').'", cch_user = "'.$_SESSION['uid'].'", cch_descripcion = "FIN DE CAPTURA DE FORMAS DE ENVÍO"';
      setq($sql);
      echo '<script>
      window.open("formats/pdfcotizacion.php?idcotiza='.$_GET['idcotiza'].'&descarga=1", "_blank");
      window.location.href = "'.$url.'";
      </script>';
      
    }

    function enviaryvender(){
      include_once('cuentas.php');
      include_once('remisiones.php');
      include_once('ingresos.php');
      include_once('cxcobrar.php');
      $cuent = new modelcuentas();
      $remis = new modelremisiones();
      $ingres = new Modelingresos();
      $cuentaxc = new modelcxcobrar();
      $cotiza = $_GET['idcotiza'];

      /* INICIO Inserción de los articulos en tipo de envio Por definir */

      $n= 0;
      //$suc = busca($cp, 'paqueterias_cobertura', 'pc_cp', 'pc_sucursal');
      $k=1;
      $estatus = "N";
      $sql = 'SELECT * FROM crm_cotizacionesd INNER JOIN articulos ON cdm_articulo = a_id WHERE cdm_cotizacion = "'.$cotiza.'"';
      $result = setq($sql);
      while($row = $result -> fetch_array()){
        $l=1;
        for($i=1;$i<=$row['cdm_cantidad']; $i++){
          if($row['a_tipoprod'] == "M"){
            $sqlvar = 'SELECT * FROM crm_cotizacion_variantes WHERE ccv_cotizacion = "'.$cotiza.'" AND ccv_cotizaciond = "'.$row['cdm_id'].'"';
            $resultvar = setq($sqlvar);
            while($rowvar =$resultvar -> fetch_array()){
              for($j = 1; $j <= $rowvar['ccv_cantidad']; $j++){
                
                /* $numero = getmax('ccc_numero', 'crm_cotizacionesc WHERE ccc_combo = "'.$l.'" 
                          AND ccc_cotizaciond = "'.$row['cdm_id'].'" 
                          AND ccc_articulo = "'.$rowvar['ccv_articulo'].'"', false, true); */
               $sqlins = 'INSERT INTO crm_cotizacionesc SET ccc_cotizacion = "'.$cotiza.'",
                                                              ccc_cotizaciond = "'.$row['cdm_id'].'",
                                                              ccc_articulo = "'.$rowvar['ccv_articulo'].'",
                                                              ccc_modelo = "'.$rowvar['ccv_modelo'].'",
                                                              ccc_numero= "'.$j.'",
                                                              ccc_tipoenvio= "P",
                                                              ccc_paqueteria= "0",
                                                              ccc_fenvio = "'.date('Y-m-d H:i:s').'",
                                                              ccc_combo = "'.$l.'",
                                                              ccc_estatus = "'.$estatus.'"';
                
                
                
                setq($sqlins);
                $cccid = getmax('ccc_id', 'crm_cotizacionesc', false, false);
                $sqllig = 'SELECT * FROM articulos_ligados WHERE al_articulo = "'.$rowvar['ccv_articulo'].'"';
                    $resultlig = setq($sqllig); 
                    while($rowlig = $resultlig -> fetch_array()){
                      $sqlinslig = 'INSERT INTO crm_cotizacionesc SET
                          ccc_cotizacion = "'.$cotiza.'",
                          ccc_cotizaciond = "'.$row['cdm_id'].'",
                          ccc_articulo = "'.$rowlig['av_aligado'].'",
                          ccc_modelo = "'.$rowlig['av_amodelo'].'",
                          ccc_numero = "'.$j.'",
                          ccc_tipoenvio = "P",
                          ccc_paqueteria = "0",
                          ccc_combo = "'.$l.'",
                          ccc_estatus = "'.$estatus.'",
                          ccc_fenvio = "'.date('Y-m-d H:i:s').'",
                          ccc_ligado = "'.$cccid.'"';
                      setq($sqlinslig);
                    }
                //echo 'K: '.$k."pqt: ".$pqt." pqtOcurre: ".$_POST['paqueteriaOcurre'.$k].' tipo: '.$_POST['tipo'.$k].'<br>';
                $k++;
              }
            }
            $l++;
          } else {
            if(!$row['cdm_modelo']){
              
                $sqlins2 = 'INSERT INTO crm_cotizacionesc SET ccc_cotizacion = "'.$cotiza.'",
                                      ccc_cotizaciond = "'.$row['cdm_id'].'",
                                      ccc_articulo = "'.$row['a_id'].'",
                                      ccc_modelo = "",
                                      ccc_numero= "'.$i.'",
                                      ccc_tipoenvio= "P",
                                      ccc_paqueteria= "0",
                                      ccc_fenvio = "'.date('Y-m-d H:i:s').'",
                                      ccc_combo = "0",
                                      ccc_estatus = "'.$estatus.'"';
              //echo $sqlins2.'<br>';
              setq($sqlins2);
              $cccid = getmax('ccc_id', 'crm_cotizacionesc', false, false);
              $sqllig = 'SELECT * FROM articulos_ligados WHERE al_articulo = "'.$row['a_id'].'"';
                  $resultlig = setq($sqllig); 
                  while($rowlig = $resultlig -> fetch_array()){
                    $sqlinslig = 'INSERT INTO crm_cotizacionesc SET
                      ccc_cotizacion = "'.$cotiza.'",
                      ccc_cotizaciond = "'.$row['cdm_id'].'",
                      ccc_articulo = "'.$rowlig['al_aligado'].'",
                      ccc_modelo = "",
                      ccc_numero = "'.$i.'",
                      ccc_tipoenvio = "P",
                      ccc_paqueteria = "0",
                      ccc_combo = "0",
                      ccc_estatus = "'.$estatus.'",
                      ccc_fenvio = "'.date('Y-m-d H:i:s').'",
                      ccc_ligado = "'.$cccid.'"';
                    setq($sqlinslig);
                  }
              //echo 'K: '.$k."pqt: ".$pqt." pqtOcurre: ".$_POST['paqueteriaOcurre'.$k].' tipo: '.$_POST['tipo'.$k].'<br>';
              $k++;
            } else {
              
                $sqlins2 = 'INSERT INTO crm_cotizacionesc SET ccc_cotizacion = "'.$cotiza.'",
                                      ccc_cotizaciond = "'.$row['cdm_id'].'",
                                      ccc_articulo = "'.$row['a_id'].'",
                                      ccc_modelo = "'.$row['cdm_modelo'].'",
                                      ccc_numero= "'.$i.'",
                                      ccc_tipoenvio= "P",
                                      ccc_paqueteria= "0",
                                      ccc_fenvio = "'.date('Y-m-d H:i:s').'",
                                      ccc_combo = "0",
                                      ccc_estatus = "'.$estatus.'"';   
              //echo $sqlins2.'<br>';
              setq($sqlins2);
              $cccid = getmax('ccc_id', 'crm_cotizacionesc', false, false);
              $sqllig = 'SELECT * FROM articulos_ligados WHERE al_articulo = "'.$row['a_id'].'"';
              $resultlig = setq($sqllig); 
              while($rowlig = $resultlig -> fetch_array()){
                $sqlinslig = 'INSERT INTO crm_cotizacionesc SET
                    ccc_cotizacion = "'.$cotiza.'",
                    ccc_cotizaciond = "'.$row['cdm_id'].'",
                    ccc_articulo = "'.$rowlig['al_aligado'].'",
                    ccc_modelo = "'.$rowlig['al_amodelo'].'",
                    ccc_numero = "'.$i.'",
                    ccc_tipoenvio = "P",
                    ccc_paqueteria = "0",
                    ccc_combo = "0",
                    ccc_estatus = "'.$estatus.'",
                    ccc_fenvio = "'.date('Y-m-d H:i:s').'",
                    ccc_ligado = "'.$cccid.'"';
                setq($sqlinslig);
              }
              //echo 'K: '.$k."pqt: ".$pqt." pqtOcurre: ".$_POST['paqueteriaOcurre'.$k].' tipo: '.$_POST['tipo'.$k].'<br>';
              $k++;
            }
          }
        }
      }
      /* FIN Inserción de los articulos en tipo de envio Por definir */

      /* INICIO Aplicar cotización y creación de la remisión*/
     
      $this->model->select($cotiza);
      $this->model->cliente = busca($this->model->tablero,'crm_tableros','ct_id','ct_cliente');    
      

      $acrof = busca(1,'empresas','e_id','e_siglas').'-RM';
      $folioint = getmax('r_folio','remisiones');
      if($folioint == "1"){
        $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
      }

      if(isset($_POST['iva'])) $diva="1";
      else $diva= "0";
      if(isset($_POST['total'])) $total="1";
      else $total= "0";

      $remis->setdata(NULL,$this->model->tablero,$folioint,$this->model->cliente,$_POST['almacen'],$this->model->descuento,$this->model->nmb,$_POST['correo'],$_POST['responsable'],$_POST['ffin'],$diva, "0");
      $remis->insert();
      
      $sqlcd = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$this->model->id.'" ORDER BY cdm_id';
      $resulcd = setq($sqlcd);
      $viva = busca("1",'configuracionesp','c_id','c_iva');
      $diva = busca($remis->id,'remisiones','r_id','r_diva');
      while($rowd = $resulcd->fetch_array()){
          $idrec = getmax('rd_id','remisionesd','rd_remision');

          if($rowd['cdm_iva'] == "1" && $diva == "1"){
            //$preciof = $rowd['cdm_precio'] / (1+($viva/100));
            $preciof = $rowd['cdm_precio'];
          }else $preciof = $rowd['cdm_precio'];
          $remis->setDatad($remis->id, $idrec, $rowd['cdm_cantidad'], $rowd['cdm_articulo'],$preciof,$rowd['cdm_iva'],$rowd['cdm_costo'],$rowd['cdm_nmbarticulo'], $rowd['cdm_modelo']);
          $remis->insertdetalle();

          $sqlc = 'SELECT * FROM crm_cotizacionesc
          WHERE ccc_cotizacion = "'.$this->model->id.'" AND ccc_cotizaciond = "'.$rowd['cdm_id'].'"
          ORDER BY
            CASE
              WHEN ccc_ligado IS NULL OR ccc_ligado = "" THEN 0
              ELSE 1
            END, ccc_ligado;
          ';
          $resultc = setq($sqlc);
          $actualizar = array();
          $io = 0;
          while($rowc = $resultc->fetch_array()){
            $remis->setDatac($remis->id, $idrec, $rowc['ccc_articulo'], $rowc['ccc_modelo'], $rowc['ccc_numero'], $rowc['ccc_tipoenvio'], $rowc['ccc_paqueteria'], $rowc['ccc_sucursal'], $rowc['ccc_combo'], "N", $rowc['ccc_observacion'], $rowc['ccc_embarque'], $rowc['ccc_fenvio'], $rowc['ccc_guia'], $rowc['ccc_ligado']);
            $remis->insertdetallec();

            $idremisionc = getmax('rc_id','remisionesc',false, false);

            if(empty($rowc['ccc_ligado'])){
              $actualizar[$io][0] = $rowc['ccc_id'];
              $actualizar[$io][1] = $idremisionc;
              $io++;
            } 
          }

            for($l = 0; $l < count($actualizar); $l++){
              $cccid = $actualizar[$l][0];
              $rcid = $actualizar[$l][1];

              $sqldc = 'UPDATE remisionesc SET
                      rc_ligado = "'.$rcid.'"
                      WHERE rc_ligado = "'.$cccid.'" AND rc_remision = "'.$remis->id.'" AND rc_remisiond = "'.$idrec.'"';
              setq($sqldc);
            }
      }

      $remis->calculaimporte($remis->id);
      $idrem = $remis->id;
      
      $sql = 'UPDATE crm_cotizaciones SET cc_estatus = "V", cc_remision = "'.$remis->id.'"
              WHERE cc_id = "'.$this->model->id.'"';
      $updsts = setq($sql);

      if ($updsts) {
        $sqlhist = 'INSERT INTO crm_cotizaciones_historial SET cch_cotizacion = "'.$this->model->idc.'", cch_fecha = "'.date('Y-m-d H:i:s').'", cch_user = "'.$_SESSION['uid'].'", cch_descripcion = "COTIZACIÓN VENDIDA"';
        setq($sqlhist);
      }
      /* FIN Aplicar la cotización y creación de la remisión*/
      
      /* INICIO Aplicar remisión y creación de la cxcobrar*/
      $remis->select($idrem);
      $idcxc = $cuentaxc->insertcxcobrar($remis->cliente,$remis->id,'R',$remis->total,date('Y-m-d H:i:s'),"");
      $cuentaxc->addproyeccion($idcxc);
      /*$okcta = $cuent->cuentaactiva($_POST['cuenta']);
      $corte = $okcta;

       $folioing = getmax('i_id','ingresos');
      $ingres->setData($folioing,$remis->total,$remis->cliente,$remis->id,$_POST['cuenta'],date('Y-m-d H:i:s'),date('Y-m-d H:i:s'),$_SESSION['uid'],"P","INGRESO A REMISIÓN ".$remis->folio,NULL,$corte);
      $ingres->insert(); 
      $idcd = getmax('cd_id','cortesd','cd_corte = "'.$corte.'"');
      $cuent->setdatacd($idcd,$corte,date('Y-m-d H:i:s'),$_SESSION['uid'],"C",$ingres->iding,$remis->total,"R",$_POST['cuenta']);
      $cuent->insertcd();

      $cuent->asignaricxc($ingres->iding,$idcxc,$remis->total, "C"); */

      $sql3 = 'UPDATE crm_tableros SET
                ct_estatus = "A"
                WHERE ct_id="' . $remis->tablero. '"';
      setq($sql3);

      $sql3 = 'UPDATE remisiones SET
      r_estatus = "A",
      r_faplica = "'.date('Y-m-d H:i:s').'",
      r_observaciones = "'.formmayus($_POST['descripcion'],true).'",
      r_uaplica = "'.$_SESSION['uid'].'"
      WHERE r_id="' . $remis->id. '"';
      $actualizacionExitosa = setq($sql3);
      /* FIN Aplicar remisión y creación de la cxcobrar*/

      /* Envío del correo electronico */
      if(isset($_POST['checkmail'])){
        echo '
          <script>
          $.ajax({
            url: "query/correocotizaciones.php",
            method: "POST",
            data: {"remision": "'.$idrem.'",
                    "correoop": "5"
                  },
          }).done(function(data){
          });          
          </script>';
      }
      /* Envío del correo electronico */

      redirect("?modulo=remisiones&accion=show&id=".$idrem);
    }
  }

  class modelcotizaciones{
    function select($id){
      $sql = 'SELECT * FROM crm_cotizaciones WHERE cc_id = "'.$id.'" ';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['cc_id'];
      $this->tablero = $row['cc_tablero'];
      $this->folio = $row['cc_folio'];
      $this->nmb = $row['cc_nmb'];
      $this->fini = $row['cc_fini'];
      $this->ffin = $row['cc_ffin'];
      $this->descripcion = $row['cc_descripcion'];
      $this->destino = $row['cc_destino'];
      $this->maildestino = $row['cc_maildestino'];
      $this->teldestino = $row['cc_teldestino'];
      $this->responsable = $row['cc_agente'];
      $this->diva = $row['cc_diva'];
      $this->mtotal = $row['cc_mtotal'];
      $this->descuento = $row['cc_descuento'];
      $this->montodescuento = $row['cc_montodescuento'];
      $this->importe = $row['cc_importe'];
      $this->estatus = $row['cc_estatus'];
      $this->probabilidad = $row['cc_probabilidad'];
      $this->copiamail = $row['cc_copiamail'];
      $this->nmbagente = $row['cc_nmbagente'];
      $this->puestoagente	 = $row['cc_puestoagente'];
      $this->mensaje	 = $row['cc_mensaje'];
      $this->tabla = $row['cc_tabla'];
      $this->firma = $row['cc_firma'];
      $this->cuentas = $row['cc_cuentas'];
      $this->subtotal= $row['cc_subtotal'];
      $this->iva= $row['cc_iva'];
      $this->remision= $row['cc_remision'];
      $this->direnvio= $row['cc_direnvio'];
      $this->observadir= $row['cc_observadir'];
      $this->msi= $row['cc_msi'];
      $this->uuid= $row['cc_uuid'];
      $this->almacen = $row['cc_almacen'];
      $this->precioenvio = $row['cc_precioenvio'];
      $this->envio = $row['cc_envio'];
      $this->motivo = $row['cc_motivo'];
      $this->preciodolars = $row['cc_preciodolares'];
    }
    function SetData($id,$tablero,$foliocot,$nmbac,$ffin,$descripcion,$diva,$total,$estatus,$probabilidad,$destino,$telefono,$correo,$responsable,$descuento,$montodescuento,$nmbagente,$puestoagente,$copiamail,$direnvio,$observadir,$uuid,$almacen,$envio,$tablaprod=NULL,$firma=NULL,$cuentas=NULL,$msi=NULL){
      $this->id = clearvmayus($id);
      $this->tablero = clearvmayus($tablero);
      $this->foliocot = clearvmayus($foliocot);
      $this->nmbac = clearvmayus($nmbac);
      $this->ffin = clearvmayus($ffin);
      $this->descripcion = clearvmayus($descripcion);
      $this->diva= clearvmayus($diva);
      $this->mtotal= clearvmayus($total);
      $this->estatus= clearvmayus($estatus);
      $this->probabilidad= clearvmayus($probabilidad);
      $this->destino= clearvmayus($destino);
      $this->telefono= clearvmayus($telefono);
      $this->correo= clearvminus($correo);
      $this->responsable= clearvmayus($responsable);
      $this->descuento= clearvmayus($descuento);
      $this->montodescuento= clearvmayus($montodescuento);
      $this->nmbagente= clearvmayus($nmbagente);
      $this->puestoagente= clearvmayus($puestoagente);
      $this->copiamail= clearvminus($copiamail);
      $this->tablaprod= clearvmayus($tablaprod);
      $this->firma= clearvmayus($firma);
      $this->cuentas= clearvmayus($cuentas);
      $this->direnvio= clearvmayus($direnvio);
      $this->observadir= clearvmayus($observadir);
      $this->msi= clearvmayus($msi);
      $this->uuid = $uuid;
      $this->almacen = $almacen;
      $this->envio = $envio; 
    }

    function insertcotiza(){
      $this->mensaje = "Hola ".$this->destino."

      A continuación encontrarás listados los servicios de acuerdo con tu solicitud de cotización

      ";

      $sql = 'INSERT INTO crm_cotizaciones SET
              cc_tablero = "'.$this->tablero.'",
              cc_folio = "'.$this->foliocot.'",
              cc_nmb = "'.$this->nmbac.'",
              cc_ffin = "'.$this->ffin.'",
              cc_fini = "'.date('Y-m-d H:i:s').'",
              cc_descripcion = "'.$this->descripcion.'",
              cc_destino = "'.$this->destino.'",
              cc_maildestino = "'.$this->correo.'",
              cc_teldestino = "'.$this->telefono.'",
              cc_agente = "'.$this->responsable.'",
              cc_diva = "'.$this->diva.'",
              cc_mtotal = "'.$this->mtotal.'",
              cc_descuento= "'.$this->descuento.'",
              cc_montodescuento= "'.$this->montodescuento.'",
              cc_estatus = "'.$this->estatus.'",
              cc_nmbagente = "'.$this->nmbagente.'",
              cc_puestoagente = "'.$this->puestoagente.'",
              cc_copiamail = "'.$this->copiamail.'",
              cc_mensaje = "'.$this->mensaje.'",
              cc_direnvio = "'.$this->direnvio.'",
              cc_observadir = "'.$this->observadir.'",
              cc_msi= "'.$this->msi.'",
              cc_uuid = "'.$this->uuid.'",
              cc_almacen = "'.$this->almacen.'",
              cc_envio = "'.$this->envio.'",
              cc_probabilidad = "'.$this->probabilidad.'"';
      setq($sql);

      $this->idc = getmax('cc_id','crm_cotizaciones',false,false);
    }
    function updatecotiza(){
      $sql = 'UPDATE crm_cotizaciones SET
              cc_nmb = "'.$this->nmbac.'",
              cc_ffin = "'.$this->ffin.'",
              cc_descripcion = "'.$this->descripcion.'",
              cc_destino = "'.$this->destino.'",
              cc_maildestino = "'.$this->correo.'",
              cc_teldestino = "'.$this->telefono.'",
              cc_agente = "'.$this->responsable.'",
              cc_diva = "'.$this->diva.'",
              cc_mtotal = "'.$this->mtotal.'",
              cc_descuento= "'.$this->descuento.'",
              cc_montodescuento= "'.$this->montodescuento.'",
              cc_estatus = "'.$this->estatus.'",
              cc_direnvio = "'.$this->direnvio.'",
              cc_observadir = "'.$this->observadir.'",
              cc_msi= "'.$this->msi.'",
              cc_almacen = "'.$this->almacen.'",
              cc_envio = "'.$this->envio.'",
              cc_probabilidad = "'.$this->probabilidad.'"
              WHERE cc_id = "'.$this->id.'"';
      setq($sql);
    }
    function resultcd($id){
      $sql = 'SELECT * FROM crm_cotizacionesd
              WHERE cdm_cotizacion = "'.$id.'" ORDER BY cdm_id ASC';
      $this->resultcd = setq($sql);
    }
    function insertprod($id,$idprod,$nmbprod,$cantidad,$precio,$costo,$miva, $modelo){
      $sql = 'INSERT INTO crm_cotizacionesd SET
              cdm_cotizacion = "'.$id.'",
              cdm_articulo = "'.$idprod.'",
              cdm_modelo = "'.$modelo.'",
              cdm_nmbarticulo = "'.$nmbprod.'",
              cdm_cantidad = "'.$cantidad.'",
              cdm_precio = "'.$precio.'",
              cdm_costo = "'.$costo.'",
              cdm_iva = "'.$miva.'"';
      setq($sql);

      $this->idcd = getmax('cdm_id','crm_cotizacionesd','cdm_cotizacion = "'.$id.'"',false);
    }
    function updateconc($cotizacion,$idconc,$nmbprod){
      $sql = 'UPDATE crm_cotizacionesd SET
              cdm_nmbarticulo = "'.$nmbprod.'"
              WHERE cdm_cotizacion = "'.$cotizacion.'" AND cdm_id = "'.$idconc.'"';
      setq($sql);
    }
    function updateprod($cotizacion,$id,$cantidad,$precio,$miva){
      $sql = 'UPDATE crm_cotizacionesd SET
              cdm_cantidad = "'.$cantidad.'",
              cdm_precio = "'.$precio.'",
              cdm_iva = "'.$miva.'"
              WHERE cdm_cotizacion = "'.$cotizacion.'" AND cdm_id = "'.$id.'"';
      setq($sql);
    }
    function delconcepto($cotizacion,$iddesc){
      $sql = 'DELETE FROM crm_cotizacionesd WHERE cdm_id = "'.$iddesc.'" AND cdm_cotizacion = "'.$cotizacion.'"';
      setq($sql);

      $sql = 'DELETE FROM crm_cotizacionesdd WHERE cdd_cotizacion = "'.$cotizacion.'" AND cdd_idd = "'.$iddesc.'"';
      setq($sql);

      $sql = 'DELETE FROM crm_cotizacion_variantes WHERE ccv_cotizacion = "'.$cotizacion.'" AND ccv_cotizaciond = "'.$iddesc.'"';
      setq($sql);

      $sql = 'DELETE FROM crm_cotizaciones_cambios WHERE cca_cotizacion = "'.$cotizacion.'" AND cca_cotizaciond = "'.$iddesc.'"';
      setq($sql);
    }
    function setdescripcion($cotizacion,$iddescripcion,$valuedescripcion){
      $sql = 'INSERT INTO crm_cotizacionesdd SET
              cdd_cotizacion = "'.$cotizacion.'",
              cdd_idd = "'.$iddescripcion.'",
              cdd_nmb = "'.$valuedescripcion.'"';
      setq($sql);
    }
    function deldescripcion($cotizacion,$iddescripcion){
      $sql = 'DELETE FROM crm_cotizacionesdd WHERE cdd_id = "'.$iddescripcion.'"';
      setq($sql);
    }
    function calculaimporte($cotizacion){
      $viva = busca(1,'configuracionesp','c_id','c_iva')/100;
      $diva = busca($cotizacion,'crm_cotizaciones','cc_id','cc_diva');
      $montodes = busca($cotizacion,'crm_cotizaciones','cc_id','cc_montodescuento');
      $tablero = busca($cotizacion, 'crm_cotizaciones', 'cc_id' ,'cc_tablero');
      $firstcot = busca($tablero. 'ORDER BY cc_id ASC LIMIT 1', 'crm_cotizaciones', 'cc_tablero', 'cc_id');
      if($montodes == 0) $pordes = busca($cotizacion,'crm_cotizaciones','cc_id','cc_descuento');
      else $pordes = 0;
      $descuento = 0;
      $totiva = 0;
      $subtotal = 0;
      $iva = 0;
      $descuentot = 0;
      $subtotalfin = 0;
      $total = 0;

      $sql = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$cotizacion.'" ';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $desc = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_descuento');
        //echo busca2($row['cdm_articulo'], 'articulos', 'a_id', 'a_descuento').' desc: '.$desc.'<br>';
        $precio = $row['cdm_precio'];
        $importe = $precio*$row['cdm_cantidad'];
        if($desc == "1"){
          $descuni = $importe*($pordes/100);
          $impmd = $importe-$descuni;
          $subtotal+=$impmd;
          $descuentot+=$descuni;
        } else {
          $subtotal+=$importe;
        }
        $envio = $row['cdm_envio'];
        if($diva == "1"){
          if($row['cdm_iva'] == "1"){
            $iva=$impmd*$viva;
          }
          elseif($row['cdm_iva'] == "0"){
            $iva= 0;
          }
        }else{
          $iva = 0; 
        }
        $subtotalfin+=$importe;
        $totiva+=$iva;
      }
      $totalse = $subtotal+$totiva; 
      $total = round($subtotal)+$totiva+$envio;


      if($montodes != 0 && $result->num_rows > 0){
         $descuentot = $montodes;
         $total -= $descuentot; 
      }



      $sql = 'UPDATE crm_cotizaciones SET
              cc_subtotal = "'.$subtotalfin.'",
              cc_iva = "'.$totiva.'",
              cc_impdescuento = "'.round($descuentot).'",
              cc_importe = "'.$total.'"
              WHERE cc_id = "'.$cotizacion.'"';
      setq($sql);

      if($cotizacion == $firstcot){
        $updvalor = 'UPDATE crm_tableros SET ct_valor = "'.$totalse.'" WHERE ct_id = "'.$tablero.'"';
        setq($updvalor);
      }
    }
    function finalcotiza($mensaje){
      $sqltp = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$_GET['idcotiza'].'" ORDER BY cdm_id ASC';
      $result = setq($sqltp);

      $sql = 'UPDATE crm_cotizaciones SET
              cc_tablero = "'.$this->tablero.'",
              cc_folio = "'.$this->foliocot.'",
              cc_nmb = "'.$this->nmbac.'",
              cc_ffin = "'.$this->ffin.'",
              cc_fini = "'.date('Y-m-d H:i:s').'",
              cc_descripcion = "'.$this->descripcion.'",
              cc_destino = "'.$this->destino.'",
              cc_maildestino = "'.$this->correo.'",
              cc_teldestino = "'.$this->telefono.'",
              cc_agente = "'.$this->responsable.'",
              cc_diva = "'.$this->diva.'",
              cc_mtotal = "'.$this->mtotal.'",
              cc_descuento= "'.$this->descuento.'",
              cc_estatus = "'.$this->estatus.'",
              cc_nmbagente = "'.$this->nmbagente.'",
              cc_puestoagente = "'.$this->puestoagente.'",
              cc_copiamail = "'.$this->copiamail.'",
              cc_mensaje = "'.formmayus($mensaje,false).'",
              cc_tabla= "'.$this->tablaprod.'",
              cc_firma= "'.$this->firma.'",
              cc_cuentas= "'.$this->cuentas.'",
              cc_probabilidad = "'.$this->probabilidad.'",
              cc_subtotal = "'.$this->subtotal.'",
              cc_iva = "'.$this->iva.'",
              cc_direnvio = "'.$this->direnvio.'",
              cc_observadir = "'.$this->observadir.'",
              cc_msi= "'.$this->msi.'",
              cc_importe = "'.$this->importe.'"
              WHERE cc_id = "'.$this->id.'"';
      setq($sql);
    }
    function cotizacompleta($id){
      $completa = "OK";
      $sql = 'SELECT COUNT(*) FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$id.'"';
      $result = setq($sql);
      list($prods) = $result->fetch_array();

      if($prods > 0){
        $completa = "OK";
      } else {
        $completa = "NOTOK";
      }
      return($completa);
    } 
    function compruebacmb($id){
      $ok = "OK";

      if($ok == "OK"){
        $sql = 'SELECT cc_id,cdm_articulo,cc_almacen,cdm_id, cdm_cantidad, a_tipoprod, cdm_modelo, cdm_completo, a_id
                FROM crm_cotizacionesd INNER JOIN crm_cotizaciones ON cc_id = cdm_cotizacion
                INNER JOIN articulos ON a_id = cdm_articulo
                WHERE cc_id = "'.$id.'" AND a_tipoprod = "M"';
        $result = setq($sql);
        while($row = $result->fetch_array()){
          if($row['cdm_completo'] == 0){
            $ok = $row['a_id'];
          }
        }
      }
      return($ok);
    }
    function compruebaactivo($id){
      $ok = "OK";

      if($ok == "OK"){
        $sql = 'SELECT a_id, a_tipoprod, a_expira, a_estatus
                FROM crm_cotizacionesd INNER JOIN crm_cotizaciones ON cc_id = cdm_cotizacion
                INNER JOIN articulos ON a_id = cdm_articulo
                WHERE cc_id = "'.$id.'" AND a_tipoprod = "M"';
        $result = setq($sql);
        while($row = $result->fetch_array()){
          if($row['a_estatus'] == "I"){
            $ok = $row['a_id'];
          }
        }
      }
      return($ok);
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
                      <th class="number-import">'.number_format($impdesc,2).'</th>
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
    function selectcondiciones($id){
      $sql='SELECT * FROM crm_cotizaciones_condiciones WHERE cf_cotizacion = "'.$id.'" order by cf_orden';
      $this->selectcondiciones=setq($sql) or die($sql);
    }
    function setDataCondicion($condicion){
      mb_internal_encoding("UTF-8");
        $simbol = array('"',"'");
        $cambio = array('\"',"\'");
        $this->condicion=str_replace($simbol,$cambio, mb_strtoupper($condicion));
    }
    function insertcondicion($condicion){
        if(isset($_POST['estatus'])) $_POST['estatus']="A";
        else $_POST['estatus']="I";

        $sql='INSERT INTO crm_cotizaciones_condiciones SET
              cf_descripcion="'.$this->condicion.'",
              cf_cotizacion="'.$_GET['idc'].'",
              cf_estatus="'.$_POST['estatus'].'",
              cf_orden="'.$_POST['orden'].'",
              cf_condicion = "'.$condicion.'",
              cf_tipo = "C"';
      setq($sql); 
    }
    function insertnota(){
      if(isset($_POST['estatus'])) $_POST['estatus']="A";
      else $_POST['estatus']="I";

      $sql='INSERT INTO crm_cotizaciones_condiciones SET
            cf_descripcion="'.$this->condicion.'",  
            cf_cotizacion="'.$_GET['idc'].'",
            cf_estatus="'.$_POST['estatus'].'",
            cf_orden="'.$_POST['orden'].'",
            cf_tipo = "N"';
    setq($sql) or die($sql); 
  }
    function deletecondicion(){
      $sql='DELETE FROM crm_cotizaciones_condiciones WHERE cf_id="'.$_GET['id'].'"';
      setq($sql) or die($sql);
    }
    function actualizacondicion(){
      if(isset($_POST['estatus'])) $_POST['estatus']="A";
      else $_POST['estatus']="I";

      $sql='
            UPDATE crm_cotizaciones_condiciones SET
            cf_descripcion="'.$this->condicion.'",
            cf_cotizacion="'.$_GET['idc'].'",
            cf_estatus="'.$_POST['estatus'].'",
            cf_orden="'.$_POST['orden'].'"
            WHERE cf_id="'.$_GET['id'].'"';
      setq($sql) or die($sql);
    }
    function insertnuevac(){
      $sql = 'SELECT MAX(c_id) FROM condicionesc';
      $result = setq($sql);
      $row = mysqli_fetch_array($result);
      $sql = '
        INSERT INTO condicionesc SET
        c_id = "'.($row['MAX(c_id)']+1).'",
        c_nmb = "'.$_POST['nomc'].'",
        c_estatus = "A",
        c_empresa = "'.$_SESSION['emp'].'"';

      setq($sql) or die($sql);
    }
    function guidv4() {
      $uuid = array(
        'time_low'  => 0,
        'time_mid'  => 0,
        'time_hi'  => 0,
        'clock_seq_hi' => 0,
        'clock_seq_low' => 0,
        'node'   => array()
      );

      $uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
      $uuid['time_mid'] = mt_rand(0, 0xffff);
      $uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
      $uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
      $uuid['clock_seq_low'] = mt_rand(0, 255);

      for ($i = 0; $i < 6; $i++) {
        $uuid['node'][$i] = mt_rand(0, 255);
      }

      $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
        $uuid['time_low'],
        $uuid['time_mid'],
        $uuid['time_hi'],
        $uuid['clock_seq_hi'],
        $uuid['clock_seq_low'],
        $uuid['node'][0],
        $uuid['node'][1],
        $uuid['node'][2],
        $uuid['node'][3],
        $uuid['node'][4],
        $uuid['node'][5]
      );

      return $uuid;
    }

    function setvariantecmb($idc, $idcd, $articulo, $modelo, $cantidad){
      $sql = 'INSERT INTO crm_cotizacion_variantes SET ccv_cotizacion = "'.$idc.'",
                                                      ccv_cotizaciond = "'.$idcd.'",
                                                      ccv_articulo = "'.$articulo.'",
                                                      ccv_modelo = "'.$modelo.'",
                                                      ccv_cantidad = "'.$cantidad.'"';
      setq($sql);                                                      
    }
    function setcambioscmb($idc, $idcd, $articulo, $cantidad, $usuario, $fecha){
      $sqlcam = 'INSERT INTO crm_cotizaciones_cambios SET cca_cotizacion = "'.$idc.'",
                                                                  cca_cotizaciond = "'.$idcd.'",
                                                                  cca_articulo = "'.$articulo.'",
                                                                  cca_cantidad = "'.$cantidad.'",
                                                                  cca_cambio = "",
                                                                  cca_estatus = "N",
                                                                  cca_ugen = "'.$usuario.'",
                                                                  cca_fgen = "'.$fecha.'"';
      setq($sqlcam); 
    }

    function checardisponibles($id, $almacen){
      $datos = array();
      $faltantes = array();
      $datos['error'] = 0; 
      $i = 0;
      $exist = 0;
      $sql = 'SELECT * FROM crm_cotizacionesd INNER JOIN articulos ON a_id = cdm_articulo
              WHERE cdm_cotizacion = "'.$id.'" ORDER BY cdm_id ASC';
      $resultcd = setq($sql);
      while($row = $resultcd->fetch_array()){
        $tipoprod = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_tipoprod');
        if($tipoprod != "M"){
          if($row['a_inventariado'] == "1"){
            if($row['cdm_modelo'] == ""){
              $exist = existencia($row['cdm_articulo'], $almacen);
            }else{
              $exist = existenciaModelo($row['cdm_articulo'], $almacen,$row['cdm_modelo']);
            }
            if($exist < $row['cdm_cantidad']){
              $datos['error'] = 1; //No hay existencia
              $nmb = $row['a_nmb'];
              if($row['cdm_modelo'] == "")  $nmb .= ' '.busca($row['cdm_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['cdm_modelo'].'" AND av_articulo', 'av_nmb');
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
                if($rowal['al_amodelo'] == "")  $nmb .= ' '.busca( $rowal['al_aligado'], 'articulos_variantes', 'av_modelo = "'. $rowal['al_amodelo'].'" AND av_articulo', 'av_nmb');
                $datos['articulos'][$i] = number_format($row['cdm_cantidad']*$rowal['al_cantidad'], 0).' - '.$nmb; 
                $i++;
              }
            }
          }
        } else {
          $sqlvar = 'SELECT * FROM crm_cotizacion_variantes INNER JOIN articulos ON ccv_articulo = a_id WHERE ccv_cotizacion = "'.$this->model->id.'" AND ccv_cotizaciond = "'.$row['cdm_id'].'"';
          $resultvar = setq($sqlvar);
          while($rowvar = $resultvar -> fetch_array()){
            if($rowvar['a_inventariado'] == "1"){
              if($rowvar['ccv_modelo'] != "") $exist = existenciaModelo($rowvar['ccv_articulo'], $almacen,$rowvar['ccv_modelo']);
              else $exist = existencia($rowvar['ccv_articulo'], $almacen);
              //echo 'exist: '.$exist.' utilizo: '.$row['cdm_cantidad']*$rowvar['ccv_cantidad']. ' <br>';
              if($exist < $row['cdm_cantidad']*$rowvar['ccv_cantidad']) {
                $datos['error'] = 1; //No hay existencia
                $nmb = $rowvar['a_nmb'];
                if($rowvar['ccv_modelo'] == "")  $nmb .= ' '.busca( $rowvar['ccv_articulo'], 'articulos_variantes', 'av_modelo = "'. $rowvar['ccv_modelo'].'" AND av_articulo', 'av_nmb');
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
                  if($rowal['al_amodelo'] == "")  $nmb .= ' '.busca( $rowal['al_aligado'], 'articulos_variantes', 'av_modelo = "'. $rowal['al_amodelo'].'" AND av_articulo', 'av_nmb');
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

    function enviodescuento($id){
      $ok = "OK";

      $descuento = busca($id,'crm_cotizaciones','cc_id','cc_descuento');
      if($descuento > 0){
        $sql = 'SELECT COUNT(*) FROM crm_cotizacionesc
                WHERE ccc_cotizacion = "'.$id.'" AND ccc_tipoenvio = ""';
        $result = setq($sql);
        list($pordefinir) = $result->fetch_array();
        if(!$pordefinir) $pordefinir = 0;
      }
      if($pordefinir > 0) $ok = "NOTOK";

      $ok = "OK";
      return($ok);
    }
  }

  class viewcotizaciones {
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->model->aest = array("A" => "Activo","I" => "Inactivo");
      ?>
      <script language="JavaScript">
        function checkSubmitedit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
        function confirmacancela(idcotiza){
          var conf =  confirm("¿Deseas cancelar esta cotización?");
          if(conf == true){
            document.location.href="?modulo=cotizaciones&accion=cancelar&id=" + idcotiza;
          }
        }
        function finalizarcotiza(cotizacion){
          var conf =  confirm("La cotización pasará a una remisión\n¿Deseas continuar?");
          if(conf == true){
            document.location.href="?modulo=cotizaciones&accion=vender&id=" + cotizacion;
          }
        }
        function finarticulos(cotiza){
          var conf =  confirm("¿Finalizar la captura de los articulos de la cotización?");
          if(conf == true){
            document.location.href="?modulo=cotizaciones&accion=finarticuloscaptura&idcotiza=" + cotiza;
          }
        }
        function finenvio(cotiza, direccion){
          var form = document.getElementById("formenvios");
          var elements = form.elements;
          //if(direccion != "0" && direccion != ""){
            var j = 0;
            for (var i = 0; i < elements.length; i++) {
              if (!elements[i].checkValidity()) {
                elements[i].style.borderColor = "red";
                elements[i].style.borderWidth = "3";
                j = 1;
              }
            }
            if(j == 0){
              var conf =  confirm("¿Finalizar la captura de las formas de envío de la cotización?");
              if(conf == true){
                document.getElementById("formenvios").submit();
              } 
            }else {
                alert("Algunos artículos estan sin sucursal de entrega");
            }
          /* } else {
            alert("Configure la dirección de envío para poder continuar.");
            form.click();
            setTimeout(function() {
                // Selecciona el elemento dentro del FancyBox y haz clic en él
                var checkdir = document.getElementById("setdir");
                checkdir.click();
                setTimeout(function(){
                  var direcciones = document.getElementById("direcciones");
                  var obs = document.getElementById("observadir");
                  direcciones.style.borderColor = "red";
                  direcciones.style.borderWidth = "3";
                  obs.focus();
                }, 200 );
            }, 500);
          } */
        }

        function existencias(texto, remision){
          Swal.fire({
          icon: 'error',
          title: 'No es posible completar la venta ya que los siguientes artículos se encuentran sin existencia.',
          html: texto,
          //showDenyButton: true,
          confirmButtonText: '<i class="fas fa-check" style="color:#ffffff"></i> Entendido',
          //denyButtonText: '<i class="fas fa-times" style="color:#ffffff"></i> Cancelar',
          }).then((result) => {
             Swal.close();
          });
        }

      </script>
      <?php
    }
    function index(){
      $comp = $this->model->compruebaactivo($this->model->id);
      $permi = ['A', 'V', 'C'];
      if($comp != "OK" && !in_array($this->model->estatus, $permi)){
        $nmb = busca($comp, 'articulos', 'a_id','a_nmb');
        echo '<script>
          alert("El artículo '.$nmb.' se encuentra inactivo.");
        </script>';
        $inactivo = "1";
        $this->model->estatus = "C";
      } else {
        $inactivo = "0";
      }
      $izquierda = '';
      $izquierda .= '
      <a href="?modulo=tableros&accion=show&id='.$this->model->tablero.'">
      <button type="button" class=" btn btn-warning"><i class="fa fa-arrow-left"></i>Tablero</button>
      </a>';  

      if($this->model->envio == "C"){
        $icon = "fas fa-hand-holding-usd";
        $text = "Vender";
      } else {
        $icon = "fas fa-truck-loading";
        $text = "Finalizar captura";
      }

      if($this->model->estatus == "D" && $this->model->direnvio == "0") $stylebtn = 'style="border: 3px solid red;"';
      else $stylebtn = "";


      //Acciones en estatus N
      $numcond = busca($this->model->id,'crm_cotizaciones_condiciones','cf_cotizacion','COUNT(*)');
      if($this->model->estatus != "C")

      if($this->model->estatus != "V" && $this->model->estatus != "C"){
        $izquierda .= '<button type="button" class="btn btn bg-danger text-white " onclick="confirmacancela('.$this->model->id.');" data-toggle="tooltip" data-placement="top" title="Cancelar cotización"><i class="fas fa-times" style="color: #ffffff;"></i>Cancelar</button>';
        $izquierda .= '<a href="popup/cotizaciones-condiciones?modulo='.$_GET['modulo'].'&accion=insertd&id='.$this->model->id.'" onclick="window.open(this.href,\'window\',\'width=1070, height=650\');return false"  id="xbuscar" data-toggle="tooltip"data-placement="top" title="Condiciones Comerciales">
            <button class="btn  text-white" style="background: teal;"><i class="fas fa-search" style="color: #ffffff;"></i>Condiciones <span class="badge bg-danger rounded-pill"> '.$numcond.'</span></button> 
        </a>';
      }
      
      $izquierda .= '<a id="fancyupd" data-fancybox data-type="ajax" data-src="popup/setcotiza.php?tablero='.$this->model->tablero.'&idcotiza='.$this->model->id.'&rand="'.rand().'"" href="javascript:;">
          <button type="button" id="updcotiza" class="btn btn-primary " data-toggle="tooltip" data-placement="top" title="Modificar información de la cotización" '.$stylebtn.'><i class="fa fa-pen"></i>Modificar</button>
        </a>';
      if($this->model->estatus == "N" || $this->model->estatus == "R"){
        
          $izquierda .= '<a href="formats/pdfcotizacion.php?idcotiza='.$this->model->id.'" target="_BLANK">
          <button type="button" class="btn btn-danger  text-white" data-toggle="tooltip" data-placement="top" title="Vista previa de la cotización"><i class="fa fa-file-pdf"></i>PDF</button>
        </a>';

        if($this->model->cotizacompleta($this->model->id) == "OK" && $this->model->compruebacmb($this->model->id) == "OK" && ($this->model->estatus == "N" || $this->model->estatus == "R"))
          if($this->model->envio == "P")  
            $izquierda .= '
              <button type="button" class="btn  text-white" style="background: rebeccapurple;" onclick="finarticulos('.$this->model->id.');" data-toggle="tooltip" data-placement="top" title="Finalizar captura de artículos"><i class="fas fa-truck-loading" style="color: #ffffff"></i>Finalizar captura</button>';
          else {
            $datos = $this->model->checardisponibles($this->model->id, $this->model->almacen);
            if($datos['error'] == 0){
              $izquierda .= '<a data-fancybox data-type="ajax" data-src="popup/cerrarcotiza.php?tablero='.$this->model->tablero.'&idcotiza='.$this->model->id.'" href="javascript:;">
              <button type="button" class="btn btn-success text-white" data-toggle="tooltip" data-placement="top" title="Finalizar la captura  de artículos y vender"><i class="fas fa-hand-holding-usd" style="color: #ffffff"></i>Enviar y vender</button>
              </a>';
            } else {
              $texto = "";
              foreach ($datos['articulos'] as $key) {
                $texto .= $key.'<br>';
              }
                $izquierda .= '<button class="btn btn-danger btn-sm me-2" onclick="existencias(\''.$texto.'\', '.$this->model->id.')"><i class="fas fa-check" style="color: #ffffff;"></i> Enviar y vender</button>';
            }
          }
        else if($this->model->cotizacompleta($this->model->id) != "OK")
        $izquierda .= '
          <button type="button" class="btn  btn-danger text-white" data-toggle="tooltip" data-placement="top" title="Aún no se ha capturado ningun artículo"><i class="'.$icon.'" style="color: #ffffff"></i>'.$text.'</button>';
        else if ($this->model->compruebacmb($this->model->id) != "OK")
        $izquierda .= '
          <button type="button" class="btn  btn-danger text-white" data-toggle="tooltip" data-placement="top" title="Configure los paquetes para continuar"><i class="'.$icon.'" style="color: #ffffff"></i>'.$text.'</button>';
        $izquierda .=   
        '<a data-fancybox data-type="ajax" data-src="popup/adjuntardoccotiza?id='.$this->model->id.'" href="javascript:;">
          <button type="button" class="btn btn-info " data-toggle="tooltip" data-placement="top" title="Ver documentos adjuntos"><i class="fa fa-paperclip"></i>Adjuntar</button>
        </a>';
        /* $izquierda .= '<button type="button" class="btn btn bg-danger text-white " onclick="confirmacancela('.$this->model->id.');" data-toggle="tooltip" data-placement="top" title="Cancelar cotización"><i class="fas fa-times" style="color: #ffffff;"></i>Cancelar</button>'; */
        
      }
      if($this->model->estatus == "A"){
        $izquierda .= 
        '<a href="formats/pdfcotizacion.php?idcotiza='.$this->model->id.'" target="_BLANK">
          <button type="button" class="btn btn-danger  text-white" data-toggle="tooltip" data-placement="top" title="Vista previa de la cotiazación"><i class="fa fa-file-pdf"></i>PDF</button>
        </a>';
        
        $izquierda .= '<a data-fancybox data-type="ajax" data-src="popup/reenviarcotiza?cotizacion='.$this->model->id.'&rand='.rand().'" href="javascript:;">
          <button type="button" class="btn btn-info " data-toggle="tooltip" data-placement="top" title="Ver documentos adjuntos"><i class="fas fa-envelope" style="color: #ffffff;"></i>Reenviar correo</button>
        </a>';
        $izquierda .= '<a data-fancybox data-type="ajax" data-src="popup/clonecotiza?idcotiza='.$this->model->id.'&rand='.rand().'" href="javascript:;">
        
        <button type="button" class="btn btn-primary " data-toggle="tooltip" data-placement="top" title="Ver documentos adjuntos"><i class="fas fa-clone" style="color: #ffffff;"></i>Clonar</button>
        </a>';        
        $izquierda .= '<a data-fancybox data-type="ajax" data-src="popup/sendwhats?id='.$this->model->id.'&rand='.rand().'" href="javascript:;">
          <button type="button" class="btn  text-white" data-toggle="tooltip" data-placement="top" title="Enviar mensaje al cliente por whatsapp" style="background: #25D366;"><i class="fab fa-whatsapp" style="color: #ffffff;"></i> WhatsApp</button>
        </a>';
        if($this->model->estatus == "A"){
          $izquierda .=   
          '<button type="button" class="btn btn-success " data-toggle="tooltip" data-placement="top" title="Finalizar la cotización y venderla" onclick="finalizarcotiza('.$this->model->id.')"><i class="fa fa-check"></i>Vender</button>';
        }
      }
      if($this->model->estatus == "P"){
        $izquierda .= 
        '<a href="formats/pdfcotizacion.php?idcotiza='.$this->model->id.'" target="_BLANK">
          <button type="button" class="btn btn-danger  text-white" data-toggle="tooltip" data-placement="top" title="Vista previa de la cotiazación"><i class="fa fa-file-pdf"></i>PDF</button>
        </a>';
        if($this->model->enviodescuento($this->model->id) == "OK")
          $izquierda .= '<a data-fancybox data-type="ajax" data-src="popup/cerrarcotiza.php?tablero='.$this->model->tablero.'&idcotiza='.$this->model->id.'" href="javascript:;">
                          <button type="button" class="btn btn-success " data-toggle="tooltip" data-placement="top" title="Finalizar captura de cotización"><i class="fa fa-check"></i>Enviar a cliente</button>
                        </a>';
        else
          $izquierda .= '<button type="button" class="btn btn-danger " title="Error: No se puede finalizar la cotización, en envío por definir no se puede aplicar descuento" >
                          <i class="fa fa-check"></i>Enviar a cliente</button>';
      }
      if($this->model->estatus == "V"){
        
        $izquierda .= '<a data-fancybox data-type="ajax" data-src="popup/clonecotiza.php?tablero='.$this->model->tablero.'&idcotiza='.$this->model->id.'" href="javascript:;">
          <button type="button" class="btn bg-teal bg-darken-3  text-white" data-toggle="tooltip" data-placement="top" title="Copiar cotización" style="background:teal;"><i class="far fa-clone" style="color: #ffffff;"></i>Clonar</button>
        </a>';
        $izquierda .= '<a href="?modulo=remisiones&accion=show&id='.$this->model->remision.'" target="_BLANK">
          <button type="button" class="btn btn-success  text-white" data-toggle="tooltip" data-placement="top" title="Vista previa de la cotización"><i class="fab fa-wpforms" style="color: #ffffff;"></i>Ver remisión</button>
        </a>';
      }

      if($this->model->estatus == "D"){
        $izquierda .= '
            <button type="button" onclick="finenvio('.$this->model->id.', '.$this->model->direnvio.');" class="btn btn-success " data-toggle="tooltip" data-placement="top" title="Finalizar seleccion de envios"><i class="fa fa-check"></i>Finalizar</button>
          ';
        
      }

      $izquierda .= '<button id="nuevo" type="button" class="btn  btn-secondary" data-bs-toggle="modal" data-bs-target="#historial" data-placement="top" title="Ver los movimientos de la cotización">
        <i class="fas fa-history""></i> Historial
      </button>';

      toolbar($_GET['modulo'].' - '.$this->model->folio,$izquierda);

      echo '<div class="modal fade text-xs-left" id="historial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title text-text-bold-600" id="myModalLabel33">Historial de movimientos de la cotización</h4>
            </div>
            <div class="modal-body row">
              <div class="table table-responsive col-12 col-md-12 p-2">
                <table width="100%" class="table table-stripped table-bordered table-hover table-bordered border-primary">
                  <thead class="bg-primary text-white">
                    <tr>
                      <td width="40%">Descripción</td>
                      <td width="30%">Fecha</td>
                      <td width="30%">Usuario que generó el movimiento</td>
                    </tr>
                  </thead>
                  <tbody>';
                    $sql = 'SELECT * FROM crm_cotizaciones_historial WHERE cch_cotizacion = "'.$this->model->id.'"';
                    $result = setq($sql);
                    while($rowc = $result->fetch_array()){
                    echo '<tr>
                      <td width="40%">'.$rowc['cch_descripcion'].'</td>
                      <td width="30%" >'.fecha_formato($rowc['cch_fecha'], true, false).'</td>
                      <td width="25%" >'.$rowc['cch_user'].'</td>
                    </tr>';
                    }
                  echo '</tbody>
                </table>
              </div>
              <center>
                <div class="col-12">
                  <button class="btn btn-danger" onclick="cerrarModal();"><i class="fas fa-times-circle"> </i> Cerrar </button>
                </div>
              </center>
            </div>
          </div>
        </div>
      </div>';
      ?>
      <script>
function setdireccion(){

  var check = document.getElementById('setdir');
  var campos = document.getElementById('camposdir');
  var seldir = document.getElementById("direcciones");
  var cp = document.getElementById("cp");
  if(check.checked){
    campos.hidden = false;
    cp.setAttribute("required", "required");
    cambiardir();
  } else {
    campos.hidden = true;
    cp.removeAttribute("required");
  }
  setpais();
}

        function cerrarModal() {
          var modal = document.getElementById('historial');
          $(modal).modal('hide');
        }
        $(document).ready(function() {
          $('#producto').on('keyup', function() {
            if (event.keyCode == 38 || event.keyCode == 40){
              //console.log('chi');
            }else{
            var key = $(this).val();
            var dataString = 'producto='+key;
            $.ajax({
              type: "POST",
              url: "query/suggestproducts2.php",
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
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(500);
                  $.ajax({
                    type: "POST",
                    url: "query/setprecio.php",
                    data: {'cb': cb},
                    success: function(dataRTN) {
                      //console.log(dataRTN);
                      var importe = document.getElementById('impunitario');
                      importe.value = dataRTN;
                    }
                  });
                  $('#cantidadorig').focus();
                  $('#cantidadorig').select();
                  //setname();
                  //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
                  return false;
                });
              }
            });
            }
          });
        }); 

      </script>
      <?php
      if($this->model->estatus == "N"  || $this->model->estatus == "R"){
        if(isset($_GET['last'])) $focus = '';
        else $focus = 'autofocus';
        $cliente = busca($this->model->tablero,'crm_tableros','ct_id','ct_cliente');
        $esquema = busca($cliente,'crm_clientes','c_id','c_precio');
        //echo htmlspecialchars($_SERVER["PHP_SELF"]);
        echo '<input type="hidden" id="idocotizacion" name="idocotizacion" value="'.$this->model->id.'" />
        <input type="text" id="esquema" value="'.$esquema.'" readonly hidden>
          <div class="card mt-3">
            <div class="card-body">
            <div class="card-header">
              <h4 class="" >';
            echo '</div>
          <form method="post" action="?modulo=cotizaciones&accion=insertprod&id='.$this->model->id.'" autocomplete="off" class="mt-2 container">
            <div class="row">
              <div class="col-auto mb-5">
                <label>Buscar</label><br>
                <a accesskey="B" href="popup/productocotiza?cotiza='.$this->model->id.'&accion=insertd" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                  <button type="button" class="btn btn-primary"><i class="fa fa-search"></i>Buscar servicio</button>
                </a>
              </div>
              <div class="col-8 col-md-5">
                <label for="agregar" class">Servicios</label>
                <input type="text" name="producto" onblur="buscaprod();" id="producto" placeholder="Escribe un fragmento de tu servicio" class="search_query form-control" required '.$focus.' tabindex="1"/>
                <div id="suggestions" class="ocultaoscroll" style="max-height: 400px; overflow: overlay;"></div>
              </div>
              <div hidden class="col-6 col-md-2">
                <label for="agregar" class">Cantidad</label>
                <input type="number" min="1" max="99999" step="1" value="1" name="cantidadorig" id="cantidadorig" placeholder="Cantidad" class="form-control" required tabindex="2">
              </div>';
              $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
              if($grupo == "ADMIN" || $grupo == "GERENCIA") $read = '';
              else $read = 'readonly';
              echo  '<div class="col-8 col-md-3">
                <label for="precio" class">Precio unitario</label>
                <input type="number" min="0.01" max="9999999" step="0.01" name="impunitario" id="impunitario" placeholder="Importe del concepto" class="form-control" required  tabindex="3" '.$read.'>
              </div>
              <div class="col-auto">
                <label >Enviar</label><br>
                <button tabindex="3" type="submit" id="submitfcotiza" class="btn btn-success"><i class="fa fa-paper-plane"></i> Enviar</button>
              </div>
            </div>
          </form>';
        $readonestatus = "";
      }else $readonestatus = ' disabled ';

      if($this->model-> estatus != "D"){
        
        echo '<div class="row">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table">
              <thead class="thead-active bg-primary text-white">
                <tr>
                  <th width="35%">Servicio</th>
                  <!-- <th width="10%">Cantidad</th> -->
                  <th width="17%">Precio</th>
                  <th width="15%">+ IVA</th>
                  <th width="18%">Importe</th>
                  <th width="15%"></th>
                </tr>
              </thead>';
              ?>
              <script>
                function buscaprod(){
                  /* var modelo = document.getElementById("producto").value;
                  var esquema = $('#esquema').attr('value');
                  $.ajax({
                    url: 'query/buscarmodelo.php',
                    method: 'POST',
                    data: {'modelo': modelo,
                            'esquema': esquema},
                  }).done(function(data){
                    if(data == undefined || data == "") console.log("");
                    else document.getElementById("impunitario").value = data;
                  }); */
                }
                function enviardescripcion(idprod){
                  document.getElementById("iddescripcion").value = idprod;
                  document.getElementById("valuedescripcion").value = document.getElementById("descripcion" + idprod).value;
                  document.senddescripcion.submit();
                }
                function updatecpt(idcd){
                  document.getElementById("update" + idcd).disabled = true;
                  document.update+idcd.submit();
                }
                function deletecpt(idcd, descuento){
                  console.log("descuento: "+descuento);
                  var idcotiza = document.getElementById("idocotizacion").value;
                  if(descuento > 0){
                    var conf = confirm("¿Eliminar el concepto de la cotización? Al eliminarlo, se quitará el descuento de la cotización");
                  } else {
                    var conf = confirm("¿Eliminar el concepto de la cotización?");
                  }
                  if(conf == true){
                    document.location.href="?modulo=cotizaciones&accion=delconcepto&cotizacion=" + idcotiza + "&iddesc=" + idcd;
                  }
                }
                function updatedesc(id){
                  $("#btnsendc"+id).click();
                }
                function seteditacotiza(cotiza,idcon){
                  document.getElementById("nmbprod" + idcon).style.display = "none";
                  document.getElementById("btnedit" + idcon).style.display = "none";
                  document.getElementById("nmbconcepto" + idcon).style.display = "";
                  var concepto = document.getElementById("nmbprod" + idcon).innerHTML;

                  document.getElementById("nmbconcepto" + idcon).focus();
                  nmbconcepto = concepto.trim();
                  document.getElementById("nmbconcepto" + idcon).value = nmbconcepto;
                  document.getElementById("btnsendc" + idcon).style.display = "";
                  document.getElementById("descripcion" + idcon).style.display = "none";
                }
                function refreshconc(idcotiza,idcon){
                  var nmbprod = document.getElementById("nmbconcepto" + idcon).value;
                  var idcotizacion = idcotiza;
                  var idconcepto = idcon;

                  document.getElementById("idcotizacion").value = idcotizacion;
                  document.getElementById("idconc").value = idconcepto;
                  document.getElementById("nmbprod").value = nmbprod;
                  if(nmbprod != "" && nmbprod != undefined){
                    document.updateconcentp.submit();
                  }
                  else{
                    alert("Se requiere un valor para la descripcion");
                  }

                }
              </script>
              <?php
              $totiva = 0;
              $subtotal = 0;
              $descuento = 0;
              $iva = 0;
              $viva = busca(1,'configuracionesp','c_id','c_iva')/100;
              $articulo = "";
              echo '<form name="updateconcentp" method="post" action="?modulo=cotizaciones&accion=updateconc">
                <input type="hidden" name="cotizacion" id="idcotizacion" />
                <input type="hidden" name="idc" id="idconc" />
                <input type="hidden" name="nmbp" id="nmbprod" />
              </form>';
              //die($this->model->diva);
              while($row = $this->model->resultcd->fetch_array()){
                echo'<script>
                  function updatecant'.$row['cdm_id'].'(){
                    var cantidad = document.getElementById("cantidad'.$row['cdm_id'].'").value;
                    document.getElementById("newcant'.$row['cdm_id'].'").value = cantidad;
                    var form = document.getElementById("updatecantidad'.$row['cdm_id'].'");
                    document.updatecantidad'.$row['cdm_id'].'.submit();
                  }
                  function updateln'.$row['cdm_id'].'(){
                    var linean = document.getElementById("lineaneg'.$row['cdm_id'].'").value;
                    document.getElementById("newln'.$row['cdm_id'].'").value = linean;
                    document.updatelineaneg'.$row['cdm_id'].'.submit();
                  }
                </script>';
                echo '<form name="updatecantidad'.$row['cdm_id'].'" method="post" action="?modulo=cotizaciones&accion=cambiarcantidad&id='.$row['cdm_id'].'&cotizacion='.$_GET['id'].'">
                  <input type="hidden" name="cantidad" id="newcant'.$row['cdm_id'].'" />
                </form>';
                echo '<form name="updatelineaneg'.$row['cdm_id'].'" method="post" action="?modulo=cotizaciones&accion=cambiarln&id='.$row['cdm_id'].'&cotizacion='.$_GET['id'].'">
                  <input type="hidden" name="linean" id="newln'.$row['cdm_id'].'" />
                </form>';
                $desc = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_descuento');
                $preciodb = $row['cdm_precio'];
                if($row['cdm_iva'] == "1"){
                  $chiva = "checked";
                  $precio = $row['cdm_precio'];
                  $importe = $row['cdm_precio']*$row['cdm_cantidad'];
                  if($desc){
                    $descuento += $importe*($this->model->descuento/100);
                  }
                  $iva += $importe * ($viva);
                }else{
                  $precio = $row['cdm_precio'];
                  $chiva = "";
                  $importe = $row['cdm_precio']*$row['cdm_cantidad'];
                  if($desc){
                    $descuento += $importe*($this->model->descuento/100);
                  }
                  $iva += 0;
                }
                $subtotal+= $importe;

                $buttonact = "";
                $numvar = 0;
                $tipoprod = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_tipoprod');
                $inventariado = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_inventariado');

                if($tipoprod == "M"){
                  $sqlcmb = 'SELECT * FROM articulos_combos WHERE ac_articulo = "'.$row['cdm_articulo'].'"';
                  $resultcmb = setq($sqlcmb);
                  while($rowcmb = $resultcmb -> fetch_array()){
                    $numvar .= busca($rowcmb['ac_ahijo'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
                    //$cant = busca($rowcmb['ac_ahijo'], 'remision_variantes', 'rv_remision = "'.$this->model->id.'" AND rv_remisiond = "'.$row['cdm_id'].'" AND rv_articulo', 'SUM(rv_cantidad)');
                    if(!$cant) $cant = "0";
                  }
                  //if($numvar != 0){
                    $buttonact ='
                      <a href="popup/elijevariantes-cotizacion?cotizacion='.$this->model->id.'&cotizaciond='.$row['cdm_id'].'" onclick="window.open(this.href,\'cotizaciones\',\'width=890, height=800\');return false">
                        <button type="button" class="btn text-white mt-1" style="background: teal;" data-toggle="tooltip" data-placement="top" title="Seleccione las variantes para este producto"><i class="fas fa-tools" style="color: #ffffff;"></i> Configuración de artículos para '.busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_nmb').'
                        </button>
                      </a>';
                  //}
                }
                if($tipoprod != "M"){
                  if($inventariado =="1"){
                    $articulo = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_nmb');
                    if($row['cdm_modelo'] == "")
                      $exist = existencia($row['cdm_articulo'], $this->model->almacen);
                    else{
                      $exist = existenciaModelo($row['cdm_articulo'], $this->model->almacen, $row['cdm_modelo']);
                      $articulo .= ' '.busca($row['cdm_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['cdm_modelo'].'" AND av_articulo', 'av_nmb');
                    }
                  } else {
                    $exist = $row['cdm_cantidad'] + 1;
                      $articulo = "";
                  }
                  $sqlal = 'SELECT * FROM articulos_ligados INNER JOIN articulos ON a_id = al_aligado WHERE al_articulo = "'.$row['cdm_articulo'].'"';
                  $resultal = setq($sqlal);
                  while($rowal = $resultal -> fetch_array()){
                    if($rowal['a_inventariado'] == "1"){
                      if($rowal['al_amodelo'] != "") $exist = existenciaModelo($rowal['al_aligado'], $this->model->almacen, $rowal['al_amodelo']);
                      else $exist = existencia($rowal['al_aligado'], $this->model->almacen);
                      if($exist < $row['cdm_cantidad']*$rowal['al_cantidad']) {
                        $exist = $row['cdm_cantidad'] - 1;
                        $articulo = busca($rowal['al_aligado'], 'articulos', 'a_id', 'a_nmb');
                        if($rowal['al_amodelo'] != "") $articulo .= ' '.busca($rowal['al_amodelo'], 'articulos_variantes', 'av_articulo = "'.$rowal['al_aligado'].'" AND av_modelo', 'av_nmb');
                        break;
                      }
                    } else {
                      $exist = $row['cdm_cantidad'] + 1;
                      $articulo = "";
                    }
                  }
                } else {
                  $break = false;
                  $completo = busca($this->model->id, 'crm_cotizacionesd', 'cdm_id = "'.$row['cdm_id'].'" AND cdm_cotizacion', 'cdm_completo');
                  if($completo != "0"){
                    $sqlvar = 'SELECT * FROM crm_cotizacion_variantes WHERE ccv_cotizacion = "'.$this->model->id.'" AND ccv_cotizaciond = "'.$row['cdm_id'].'"';
                    $resultvar = setq($sqlvar);
                    while($rowvar = $resultvar -> fetch_array()){
                      if($break) break;
                      if($rowvar['ccv_modelo'] != "") $exist = existenciaModelo($rowvar['ccv_articulo'],$this->model->almacen , $rowvar['ccv_modelo']);
                      else $exist = existencia($rowvar['ccv_articulo'], $this->model->almacen);
                      //echo 'exist: '.$exist.' utilizo: '.$row['cdm_cantidad']*$rowvar['ccv_cantidad']. ' <br>';
                      if($exist < $row['cdm_cantidad']*$rowvar['ccv_cantidad']) {
                        $exist = $row['cdm_cantidad'] - 1;
                        $articulo = busca($rowvar['ccv_articulo'], 'articulos', 'a_id', 'a_nmb');
                        if($rowvar['ccv_modelo'] != "") $articulo .= ' '.busca($rowvar['ccv_modelo'], 'articulos_variantes', 'av_articulo = "'.$rowvar['ccv_articulo'].'" AND av_modelo', 'av_nmb');
                        if($_SESSION['uid'] == "ADMIN") echo 'Articulo'. $rowvar['ccv_articulo'];
                        break;
                      } else {
                        $sqlal = 'SELECT * FROM articulos_ligados INNER JOIN articulos ON a_id = al_aligado WHERE al_articulo = "'.$rowvar['ccv_articulo'].'"';
                        $resultal = setq($sqlal);
                        while($rowal = $resultal -> fetch_array()){
                          if($rowal['a_inventariado'] == "1"){
                            if($rowal['al_amodelo'] != "") $exist = existenciaModelo($rowal['al_aligado'], $this->model->almacen, $rowal['al_amodelo']);
                            else $exist = existencia($rowal['al_aligado'], $this->model->almacen);
                            //echo 'exist: '.$exist.' utilizo: '. $row['cdm_cantidad']*$rowvar['ccv_cantidad']*$rowal['al_cantidad']. ' <br>';
                            if($exist < $row['cdm_cantidad']*$rowvar['ccv_cantidad']*$rowal['al_cantidad']) {
                              //echo 'ENTRO!';
                              $exist = $row['cdm_cantidad'] - 1;
                              $articulo = busca($rowal['al_aligado'], 'articulos', 'a_id', 'a_nmb');
                              if($rowal['al_amodelo'] != "") $articulo .= ' '.busca($rowal['al_amodelo'], 'articulos_variantes', 'av_articulo = "'.$rowal['al_aligado'].'" AND av_modelo', 'av_nmb');
                              $break = true;
                              if($_SESSION['uid'] == "ADMIN") echo 'Articulo'. $rowal['al_aligado'];
                              break;
                            }
                          } else {
                            $exist = $row['cdm_cantidad'] + 1;
                          }
                        }
                      }
                    }
                  } else {
                    $exist = $row['cdm_cantidad'];
                  }
                } 
                
                //echo 'exist final: '.$exist. ' cantidad'. $row['cdm_cantidad'].' tipoprod: '.$tipoprod.'<br>';
                if(($exist < $row['cdm_cantidad']) && $tipoprod !== "M") {
                  //echo "Entro primer if".'<br>';
                  $back = 'style="background: #FF7B7B"  data-bs-toggle="tooltip" data-bs-placement="top" title="El articulo '.$articulo.' no tiene existencia suficiente dentro del almacén"';
                }
                else if(($exist < $row['cdm_cantidad']) && $tipoprod == "M"){
                  //echo "Entro segundo if".'<br>';
                  $back = 'style="background: #FF7B7B"  data-bs-toggle="tooltip" data-bs-placement="top" title="El articulo '.$articulo.' no tiene existencia suficiente dentro del almacén"';
                }
                else {
                  //echo 'entro al else'.'<br>';
                  $back = "";
                }
                //echo $back;
                echo '<tr '.$back.'>
                  <form method="post" action="?modulo=cotizaciones&accion=updateprod&cotizacion='.$this->model->id.'&id='.$row['cdm_id'].'" name="update'.$row['cdm_id'].'"> 
                    ';
                    echo '<td>';
                      if($this->model->estatus == "N" || $this->model->estatus == "R"){
                        echo'<script>
                          $(document).ready(function(){
                            $("#nmbconcepto'.$row['cdm_id'].'").keypress(function(e) {
                              var code = (e.keyCode ? e.keyCode : e.which);
                              if(code==13){
                                $("#btnsendc'.$row['cdm_id'].'").click();
                              }
                            });
                          });
                        </script>';
                        echo '<input type="text" class="form-control border-blue-grey border-darken-2" name="nmbconcepto'.$row['cdm_id'].'" id="nmbconcepto'.$row['cdm_id'].'" autocomplete="off" style="display:none" onchange="updatedesc('.$row['cdm_id'].');" />';
                       
                        echo '<button type="button" id="btnsendc'.$row['cdm_id'].'" onclick="refreshconc('.$this->model->id.','.$row['cdm_id'].');" class=" btn-xs btn-primary mb-2 text-white" style="display:none;"><i class="fa fa-redo" style="color: #ffffff;"></i> Actualizar</button>';
                        /* echo '<button type="button" class="btn btn-link btn-icon-primary rounded " id="btnedit'.$row['cdm_id'].'" onclick="seteditacotiza('.$this->model->id.','.$row['cdm_id'].');">
                          <i class="fa fa-pen"></i>
                        </button>'; */
                      }
                      
                      echo '<span class="font-small-3" id="nmbprod'.$row['cdm_id'].'">
                        '.$row['cdm_nmbarticulo'].'
                      </span><br>';
                      echo $buttonact."<br>";
                      if(isset($_GET['last']) && $_GET['last'] == $row['cdm_id']) $autofuc = 'autofocus="autofocus"; '; else $autofuc = "";
                      if($this->model->estatus == "N"  || $this->model->estatus == "R")
                        echo '<br><input type="text" class="form-control border-blue-grey border-darken-2  mt-1 mb-1" name="descripcion'.$row['cdm_id'].'" id="descripcion'.$row['cdm_id'].'" onchange="enviardescripcion('.$row['cdm_id'].');" placeholder="Agregar una descripción" autocomplete="off" '.$autofuc.' />';
                      $sqldd = 'SELECT cdd_id,cdd_nmb FROM crm_cotizacionesdd WHERE cdd_cotizacion = "'.$this->model->id.'"
                                AND cdd_idd = "'.$row['cdm_id'].'" ORDER BY cdd_id ASC';
                      $resultdd = setq($sqldd);
                      /*while($rowdd = $resultdd->fetch_array()){
                        echo '<p class="p-1" style="font-size: x-small;">';
                        if($this->model->estatus == "N")
                          echo '<a href="?modulo=cotizaciones&accion=deldescripcion&cotizacion='.$this->model->id.'&iddesc='.$rowdd['cdd_id'].'">
                                  <button type="button" class="btn-xs btn-danger rounded"><i class="fa fa-trash"></i></button> ';

                        echo '</a>'.$rowdd['cdd_nmb'].'</p>';
                      } */
                      while($rowdd = $resultdd->fetch_array()){
                        if($this->model->estatus == "N"  || $this->model->estatus == "R"){
                          $displayflex = 'col-md-2';
                          $displayflex2 = 'display: flex;';
                        }else{
                          $displayflex = 'col-md-12';
                          $displayflex2 = 'display:';
                        }
                        echo '<div style="'.$displayflex2.'">
                          <div class="'.$displayflex.'" style="font-size: x-small; margin-bottom: 5px;">';
                            if($this->model->estatus == "N"  || $this->model->estatus == "R")
                                echo '<a href="?modulo=cotizaciones&accion=deldescripcion&cotizacion='.$this->model->id.'&iddesc='.$rowdd['cdd_id'].'">
                                  <button style="padding: 5px;" type="button" class="btn  btn-link btn-icon-danger"><i class="fa fa-trash"></i></button>
                                </a>
                              </div>';
                          echo '<div class="col-md-10" style="    flex-wrap: wrap;
                          font-size: x-small;
                          align-content: center;
                          display: flex;;">'.$rowdd['cdd_nmb'].'</div> 
                        </div>';
                      }
                      if($desimp > 0){
                        if($row['cdm_iva'] == "1") $preciomiva = $precio-$desimp;
                        else $preciomiva = $precio-(($this->model->descuento/100)*$precio);
                        $toltdes = 'data-toggle="tooltip" data-placement="top" title="Precio con descuento '.number_format($preciomiva,2).'"';
                      }else $toltdes = "";
                      //poner dos input que se llame preciodb con valor precio en db y uno precioshow con valor precio en pantalla
                      echo '<input name="preciodb" value="'.$preciodb.'" hidden>
                      <input name="precioshow" value="'.$precio.'" hidden>'; 
                      /*  
                        echo '</td>
                        <td><input type="number" class="form-control number-align" min="0" max="9999" name="cantidad" step="0.01" value="'.number_format($row['cdm_cantidad'],2,'.','').'" '.$readonestatus.' onfocus="this.select()" onchange="submit();" /></td>
                        <td><input type="number" class="form-control number-align" min="0" max="999999" name="precio" '.$toltdes.' step="0.01" value="'.number_format($precio,2,'.','').'"  '.$readonestatus.' onfocus="this.select()" onchange="submit();" /></td>
                        <td><input type="checkbox"  class="text-center" name="miva" '.$chiva.' '.$readonestatus.' onchange="submit();" /></td>
                        <td class="number-align">'.number_format($importe,2,'.',',').'</td>
                        <td>';
                      */
                      echo'<script>
                        $(document).ready(function(){
                          $("#cantidad'.$row['cdm_id'].'").keypress(function(e) {
                            var code = (e.keyCode ? e.keyCode : e.which);
                            if(code==13){
                              var cantidad = document.getElementById("cantidad'.$row['cdm_id'].'").value;
                              document.getElementById("newcant'.$row['cdm_id'].'").value = cantidad;
                              var form = document.getElementById("updatecantidad'.$row['cdm_id'].'");
                              document.updatecantidad'.$row['cdm_id'].'.submit();
                            }
                          });
                          $("#precio'.$row['cdm_id'].'").keypress(function(e) {
                            var code = (e.keyCode ? e.keyCode : e.which);
                            if(code==13){
                              $("#update'.$row['cdm_id'].'").click();
                            }
                          });
                        });
                      </script>';
                    echo '</td>
                    <!-- <td><input type="number"  hidden onchange="updatecant'.$row['cdm_id'].'()" class="form-control number-align" min="0" max="9999" name="cantidad" id="cantidad'.$row['cdm_id'].'" step="0.01" value="'.number_format($row['cdm_cantidad'],2,'.','').'" '.$readonestatus.' onfocus="this.select()" /></td> -->
                    <script>
                      function cambiarcantidad(id,cotizacion){
                        var cant = document.getElementById("rdcantidad").value;
                        $("#cantidad'.$row['cdm_id'].'").keypress(function(e) {
                          var code = (e.keyCode ? e.keyCode : e.which);
                          if(code==13){
                            $("#update'.$row['cdm_id'].'").click();
                          }
                        });
                        //window.location.href="?modulo=cotizaciones&accion=cambiarcantidad&id="+id+"&cantidad="+cant+"&cotizacion="+cotizacion;
                      }
                    </script>
                    <!-- ';
                    $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id','u_grupo');

                    if($grupo == "ADMIN" || $grupo == "GERENCIA") $min = 0;
                    else $min = $row['cdm_precio'];
                    echo '<form method="post" action="?modulo=cotizaciones&accion=updateprod&cotizacion='.$this->model->id.'&id='.$row['cdm_id'].'" name="update'.$row['cdm_id'].'">
                    <input type="number" class="form-control number-align" min="0" max="9999" name="cantidad" id="cantidad'.$row['cdm_id'].'" step="0.01" value="'.number_format($row['cdm_cantidad'],2,'.','').'" '.$readonestatus.' onfocus="this.select()" hidden/> -->
                    <td><input type="number" class="form-control number-align" min="'.$min.'" max="999999" name="precio" id="precio'.$row['cdm_id'].'" '.$toltdes.' step="0.01" value="'.number_format($precio,2,'.','').'"  '.$readonestatus.' '.$readpre.' onfocus="this.select()"  /></td>';
                    if($this->model->diva == "1") echo'<td><input type="checkbox"  class="text-center" name="miva" '.$chiva.' '.$readonestatus.' onchange="submit();" /></td>';
                    else echo '<td><input type="hidden" name="miva" value="'.$row['cdm_iva'].'"/></td>';
                    echo'<td class="number-align">'.number_format($importe,2,'.',',').'</td>
                    <td>';
                      if($this->model->estatus == "N" || $this->model->estatus == "R")
                        echo '<div class="mb-5">
                          <button type="button"  class="btn  btn-danger" onclick="deletecpt('.$row['cdm_id'].', '.$this->model->descuento.')"><i class="fa fa-trash"></i>Borrar</button>
                        </div>';
                        
                        if($this->model->estatus != "P" && $this->model->estatus != "A" && $this->model->estatus != "V")
                        echo '<div class="mb-5">
                          <button class="btn btn-info " id="update'.$row['cdm_id'].'"><i class="fa fa-redo"></i> Actualizar</button> 
                        </div>';
                        echo '<div class="mb-5">
                          <a data-fancybox data-type="ajax" data-src="popup/fichaproducto.php?articulo='.$row['cdm_articulo'].'&modelo='.$row['cdm_modelo'].'&rand='.rand().'" href="javascript:;">
                            <button type="button" class="btn btn-success"><i class="fas fa-clipboard-list"></i> Ficha del servicio</button>
                          </a>
                        </div>';
                    echo '</td>
                  </form>
                </tr>';
              }
              if($this->model->resultcd->num_rows > 0){
                $descuento += $this->model->montodescuento;
              }
              if($this->model->diva == "1" || $descuento > 0 || $this->model->precioenvio > 0){
                echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
                  <td colspan="3">&nbsp;</td><td colspan="1">Subtotal</td>
                  <td class="number-align">$ '.number_format(($this->model->subtotal),2).'</td>
                </tr>';
              }
              if($this->model->diva == "1"){
                $total = $subtotal+$totiva;
                if($descuento > 0){
                  echo '<tr class="p-1 bg-grey bg-lighten-3 h4">
                    <td colspan="3">&nbsp;</td><td colspan="1">Descuento</td>
                    <td class="number-align">$ '.number_format(round($descuento),2).'</td>
                  </tr>';
                }
                echo '<tr class="p-1 bg-grey bg-lighten-4 h4">
                  <td colspan="3">&nbsp;</td><td colspan="1">IVA</td>
                  <td class="number-align">$ '.number_format($this->model->iva,2).'</td>
                </tr>';
                if($this->model->precioenvio != 0){
                  echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
                  <td colspan="3">&nbsp;</td><td colspan="1">Costo de envío</td>
                  <td class="number-align">$ '.number_format($this->model->precioenvio,2).'</td>
                 </tr>';
                } 
                $total = $this->model->importe + $this->model->precioenvio;
                echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
                  <td colspan="3">&nbsp;</td><td colspan="1">Total</td>
                  <td class="number-align">$ '.number_format($total,2).'</td>
                </tr>';
              }else{
                if($this->model->mtotal == "1"){
                  if($descuento > 0)
                    echo '
                    <tr class="p-1 bg-grey bg-lighten-3 h4">
                      <td colspan="3">&nbsp;</td><td colspan="1">Descuento</td>
                      <td class="number-align">$ '.number_format(round($descuento),2).'</td>
                    </tr>';
                    if($this->model->precioenvio != 0){
                      echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
                      <td colspan="3">&nbsp;</td><td colspan="1">Costo de envío</td>
                      <td class="number-align">$ '.number_format($this->model->precioenvio,2).'</td>
                     </tr>';
                    } 
                    $total = $this->model->importe + $this->model->precioenvio;
                    echo '<tr class="p-1 bg-grey bg-lighten-1 h4">
                      <td colspan="3">&nbsp;</td><td colspan="1">Total</td>
                      <td class="number-align">$ '.number_format($total,2).'</td>
                    </tr>';
                }
              }
              echo '<form method="post" name="senddescripcion" action="?modulo=cotizaciones&accion=setdescripcion&cotizacion='.$this->model->id.'">
                <input type="hidden" id="iddescripcion" name="iddescripcion" />
                <input type="hidden" id="valuedescripcion" name="valuedescripcion" />
              </form>';
            echo '</table>
          </div>
        </div>';
      } else {
/* ======== BLOQUE NUEVO: CHOFERES + KM + TARIFARIO ======== */

/* 1) Precio de combustible activo */
$precioCombustible = 0.0;
$r = setq('SELECT cc_precio FROM config_combustible WHERE cc_activo="S" ORDER BY cc_vigente_desde DESC, cc_id DESC LIMIT 1');
if ($r && $row = $r->fetch_array()) $precioCombustible = floatval($row[0]);

/* 2) Choferes activos */
$choferes = [];
$qCh = 'SELECT u_id, CONCAT(u_nmb, " ", u_apellidos) AS nombre
       FROM usuarios WHERE u_estatus="A" AND u_grupo="CHOFER" ORDER BY nombre ASC';
$rsCh = setq($qCh);
while($rsCh && $rowCh = $rsCh->fetch_assoc()){
  $choferes[] = $rowCh;
}

/* 3) Tarifario (insumos base; los km se aplican aquí en cotización) */
$tarifario = [];
$qTf = 'SELECT tc_id, tc_unidad, tc_rendimiento, tc_capacidad_tanque, tc_casetas, tc_var_desgaste
        FROM tarifario_costos WHERE tc_estatus="A" ORDER BY tc_unidad ASC';
$rsTf = setq($qTf);
while($rsTf && $rowTf = $rsTf->fetch_assoc()){
  // normaliza a float
  $rowTf['tc_rendimiento']      = floatval($rowTf['tc_rendimiento']);
  $rowTf['tc_capacidad_tanque'] = floatval($rowTf['tc_capacidad_tanque']);
  $rowTf['tc_casetas']          = floatval($rowTf['tc_casetas']);
  $rowTf['tc_var_desgaste']     = floatval($rowTf['tc_var_desgaste']);
  $tarifario[] = $rowTf;
}
?>

<!-- ======== UI: Choferes + KM + Tarifario ======== -->
<div class="card mt-3">
  <div class="card-header">
    <b>Asignación de chofer(es) y kilómetros (manual/automático)</b>
  </div>
  <div class="card-body">
    <div class="row g-3">

      <!-- Choferes (multi) -->
      <div class="col-12">
        <label class="form-label"><b>Chofer(es)</b></label>
        <select name="choferes[]" id="chf_choferes" class="form-control" multiple size="6">
          <?php foreach($choferes as $ch): ?>
            <option value="<?= htmlspecialchars($ch['u_id']) ?>"><?= htmlspecialchars($ch['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
        <small class="text-muted">Usa Ctrl/Cmd para seleccionar varios.</small>
      </div>

      <!-- Modo KM -->
      <div class="col-md-6">
        <label class="form-label"><b>Modo de kilómetros</b></label>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="km_mode" id="chf_km_manual" value="manual" checked>
          <label class="form-check-label" for="chf_km_manual">Manual</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="km_mode" id="chf_km_auto" value="auto">
          <label class="form-check-label" for="chf_km_auto">Automático (Google Maps)</label>
        </div>
      </div>

      <!-- KM manual -->
      <div class="col-md-6" id="chf_kmManualBox">
        <label class="form-label"><b>Kilómetros (manual)</b></label>
        <input type="number" step="0.01" min="1" class="form-control" id="chf_km" value="1">
      </div>

    </div>

    <!-- KM automático -->
    <div id="chf_kmAutoBox" class="mt-3" style="display:none;">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Origen</label>
          <input type="text" id="chf_gm_origen" class="form-control" placeholder="Ingresa origen">
        </div>
        <div class="col-md-6">
          <label class="form-label">Destino</label>
          <input type="text" id="chf_gm_destino" class="form-control" placeholder="Ingresa destino">
        </div>
        <div class="col-12">
          <label class="form-label">Paradas intermedias</label>
          <div id="chf_gm_paradas_wrap"></div>
          <button type="button" class="btn btn-sm btn-secondary mt-2" id="chf_btnAddParada">
            <i class="fas fa-plus"></i> Agregar parada
          </button>
        </div>
        <div class="col-md-6">
          <button type="button" class="btn btn-info mt-2" id="chf_btnCalcularRuta">
            <i class="fas fa-route"></i> Calcular ruta
          </button>
          <small class="text-muted ms-2">Se calcularán los KM y se aplicará el tarifario.</small>
        </div>
        <div class="col-12">
          <div id="chf_gm_mapa" style="width:100%; height:280px; border:1px solid #e1e1e1; border-radius:6px;"></div>
        </div>
      </div>
    </div>

    <hr/>

    <!-- Selección de unidad del tarifario -->
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label"><b>Unidad (tarifario)</b></label>
        <select id="chf_tarifario" class="form-control">
          <option value="">-- Selecciona una unidad --</option>
          <?php foreach($tarifario as $t): ?>
            <option value="<?= (int)$t['tc_id'] ?>">
              <?= htmlspecialchars($t['tc_unidad']) ?>
              (rend: <?= number_format($t['tc_rendimiento'],4) ?> km/l)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Precio combustible (global)</label>
        <input type="text" id="chf_precio" class="form-control" value="<?= number_format($precioCombustible,4,'.','') ?>" readonly>
      </div>

      <div class="col-md-3">
        <label class="form-label">KM usados</label>
        <input type="text" id="chf_km_mostrado" class="form-control" readonly>
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-md-2"><label class="form-label">TANQUES</label> <input type="text" id="chf_tanques" class="form-control" readonly></div>
      <div class="col-md-2"><label class="form-label">COMBUSTIBLE ($)</label> <input type="text" id="chf_combustible" class="form-control" readonly></div>
      <div class="col-md-2"><label class="form-label">CASETAS ($)</label> <input type="text" id="chf_casetas" class="form-control" readonly></div>
      <div class="col-md-2"><label class="form-label">DESGASTE ($)</label> <input type="text" id="chf_desgaste" class="form-control" readonly></div>
      <div class="col-md-2"><label class="form-label">OPERADOR ($)</label> <input type="text" id="chf_operador" class="form-control" readonly></div>
      <div class="col-md-2"><label class="form-label">TOTAL ($)</label> <input type="text" id="chf_total" class="form-control" readonly></div>
      <div class="col-md-2 mt-2"><label class="form-label">VENTA DVL ($)</label> <input type="text" id="chf_venta" class="form-control" readonly></div>
    </div>

    <!-- Hidden para enviar con el form existente -->
    <input type="hidden" name="chf_km_mode" id="chf_km_mode" value="manual">
    <input type="hidden" name="chf_km_total" id="chf_km_total" value="1">
    <input type="hidden" name="chf_tarifario_id" id="chf_tarifario_id" value="">
    <input type="hidden" name="chf_costo_total" id="chf_costo_total" value="">
    <input type="hidden" name="chf_costo_venta" id="chf_costo_venta" value="">
    <input type="hidden" name="chf_ruta_origen" id="chf_ruta_origen" value="">
    <input type="hidden" name="chf_ruta_destino" id="chf_ruta_destino" value="">
    <input type="hidden" name="chf_ruta_paradas" id="chf_ruta_paradas" value="[]">

  </div>
</div>
<script>
(function(){
  // ---------- Datos del servidor ----------
  const TARIFARIO = <?php echo json_encode($tarifario, JSON_NUMERIC_CHECK); ?>;
  const PRECIO    = parseFloat(document.getElementById('chf_precio').value) || 0;

  // ---------- Elementos ----------
  const kmManual     = document.getElementById('chf_km_manual');
  const kmAuto       = document.getElementById('chf_km_auto');
  const boxManual    = document.getElementById('chf_kmManualBox');
  const boxAuto      = document.getElementById('chf_kmAutoBox');
  const kmInput      = document.getElementById('chf_km');
  const kmMostrado   = document.getElementById('chf_km_mostrado');
  const selTarifario = document.getElementById('chf_tarifario');

  const outTanques   = document.getElementById('chf_tanques');
  const outComb      = document.getElementById('chf_combustible');
  const outCas       = document.getElementById('chf_casetas');
  const outDesg      = document.getElementById('chf_desgaste');
  const outOper      = document.getElementById('chf_operador');
  const outTotal     = document.getElementById('chf_total');
  const outVenta     = document.getElementById('chf_venta');

  const hMode   = document.getElementById('chf_km_mode');
  const hKm     = document.getElementById('chf_km_total');
  const hTarId  = document.getElementById('chf_tarifario_id');
  const hTot    = document.getElementById('chf_costo_total');
  const hVen    = document.getElementById('chf_costo_venta');
  const hOrig   = document.getElementById('chf_ruta_origen');
  const hDest   = document.getElementById('chf_ruta_destino');
  const hStops  = document.getElementById('chf_ruta_paradas');

  // ---------- Toggle modo ----------
  function toggleKmMode(){
    if(kmAuto.checked){
      boxManual.style.display = 'none';
      boxAuto.style.display   = '';
      hMode.value = 'auto';
    } else {
      boxManual.style.display = '';
      boxAuto.style.display   = 'none';
      hMode.value = 'manual';
    }
    recalc(); // recalcula por si cambia
  }
  [kmManual, kmAuto].forEach(el => el.addEventListener('change', toggleKmMode));
  toggleKmMode();

  // ---------- Util ----------
  function toNum(v){ const n=parseFloat(v); return isNaN(n)?0:n; }
  function numFmt2(n){ return (toNum(n)).toFixed(2); }
  function numFmt3(n){ return (toNum(n)).toFixed(3); }
  function numFmt4(n){ return (toNum(n)).toFixed(4); }

  // Busca el registro de tarifario
  function getTarifarioById(id){
    id = parseInt(id||0);
    return TARIFARIO.find(t => parseInt(t.tc_id) === id) || null;
  }

  // ---------- Re-cálculo costos ----------
  function recalc(){
    const tarId = parseInt(selTarifario.value||0);
    const t = getTarifarioById(tarId);
    const km = toNum(kmManual.checked ? kmInput.value : hKm.value);
    kmMostrado.value = numFmt2(km);

    // refleja hidden
    hKm.value   = km;
    hTarId.value = tarId || '';

    if(!t || km <= 0){
      outTanques.value = outComb.value = outCas.value = outDesg.value = outOper.value = outTotal.value = outVenta.value = '';
      hTot.value = hVen.value = '';
      return;
    }

    const rend = toNum(t.tc_rendimiento);
    const cap  = toNum(t.tc_capacidad_tanque);
    const cas  = toNum(t.tc_casetas);
    const vdes = toNum(t.tc_var_desgaste);
    const prec = PRECIO;

    const tanq = (rend > 0 ? (km / rend) : 0);
    const comb = prec * cap * tanq;
    const desg = km * (vdes * 10);  // tu ajuste
    const oper = km * (vdes * 10);  // tu ajuste
    const total = comb + cas + desg + oper;
    const venta = total * 1.5;

    outTanques.value = numFmt4(tanq);
    outComb.value    = numFmt2(comb);
    outCas.value     = numFmt2(cas);
    outDesg.value    = numFmt2(desg);
    outOper.value    = numFmt2(oper);
    outTotal.value   = numFmt2(total);
    outVenta.value   = numFmt2(venta);

    hTot.value = total;
    hVen.value = venta;
  }

  // eventos
  ['input','change'].forEach(ev => {
    kmInput.addEventListener(ev, recalc);
    selTarifario.addEventListener(ev, recalc);
  });

  // ---------- Google Maps ----------
  // Requiere cargar en tu layout:

  let gMap, gDirSvc, gDirRend, chfParadaIdx = 0;

  function gAddParadaInput(val=''){
    const wrap = document.getElementById('chf_gm_paradas_wrap');
    const id = 'chf_parada_' + (++chfParadaIdx);
    const row = document.createElement('div');
    row.className = 'input-group mb-2';
    row.innerHTML = `
      <input type="text" class="form-control chf_gm_parada" id="${id}" placeholder="Parada intermedia" value="${val}">
      <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove();"><i class="fas fa-times"></i></button>
    `;
    wrap.appendChild(row);
    if(window.google && google.maps && google.maps.places){
      new google.maps.places.Autocomplete(document.getElementById(id), { types: ['geocode'] });
    }
  }
  document.getElementById('chf_btnAddParada').addEventListener('click', ()=> gAddParadaInput());

  function gInit(){
    if(!(window.google && google.maps)) return;
    gMap = new google.maps.Map(document.getElementById('chf_gm_mapa'), {
      center:{lat:19.4326,lng:-99.1332}, zoom:6, mapTypeId:'roadmap'
    });
    gDirSvc  = new google.maps.DirectionsService();
    gDirRend = new google.maps.DirectionsRenderer({ map:gMap });
    new google.maps.places.Autocomplete(document.getElementById('chf_gm_origen'),  { types: ['geocode'] });
    new google.maps.places.Autocomplete(document.getElementById('chf_gm_destino'), { types: ['geocode'] });
  }
  if(window.google && google.maps){ gInit(); }

  document.getElementById('chf_btnCalcularRuta').addEventListener('click', function(){
    if(!(window.google && google.maps)){
      alert('Falta cargar Google Maps JS (agrega tu API Key).');
      return;
    }
    const origen  = document.getElementById('chf_gm_origen').value.trim();
    const destino = document.getElementById('chf_gm_destino').value.trim();
    if(!origen || !destino){ alert('Indica origen y destino.'); return; }

    const wpEls = document.querySelectorAll('.chf_gm_parada');
    const waypoints = [];
    const stops = [];
    wpEls.forEach(el=>{
      const v = el.value.trim();
      if(v){ waypoints.push({location:v, stopover:true}); stops.push(v); }
    });

    gDirSvc.route({
      origin: origen,
      destination: destino,
      waypoints: waypoints,
      optimizeWaypoints: false,
      travelMode: google.maps.TravelMode.DRIVING
    }, function(res, status){
      if(status !== google.maps.DirectionsStatus.OK){
        alert('No fue posible calcular la ruta: ' + status);
        return;
      }
      gDirRend.setDirections(res);
      let meters = 0;
      res.routes[0].legs.forEach(l => meters += (l.distance?.value || 0));
      const kmCalc = (meters/1000);
      // guarda en hidden
      hMode.value = 'auto';
      hKm.value   = kmCalc.toFixed(2);
      kmMostrado.value = kmCalc.toFixed(2);
      document.getElementById('chf_km_auto').checked = true;
      document.getElementById('chf_km_manual').checked = false;

      hOrig.value = origen;
      hDest.value = destino;
      hStops.value = JSON.stringify(stops);

      // recalcula con los km automáticos
      recalc();
    });
  });

  // inicial
  recalc();
})();
</script>
  <script src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY&libraries=places"></script>
<?php       
}
        //Condiciones comerciales ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if( $this->model->estatus == "A"){
          $sqlf='SELECT * FROM  crm_cotizaciones_condiciones WHERE cf_cotizacion = "'.$this->model->id.'" order by cf_orden';
          $resultf = setq($sqlf);
          echo '<center>
            <div class="container row mt-2">';
              while($rowf =$resultf->fetch_array()){
                echo '<div class="col-md-12 border-bottom">'.$rowf['cf_descripcion'].'</div>';
              }
            echo '</div>
          </center>';
        }
      echo '</div>';
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

        function paqueteriaOcurre(k) {
          var idPaqueteria = document.getElementById('paqueteriaOcurre' + k).value;
          var data = {
            idPaqueteria: idPaqueteria,
            cp: "<?php echo $cp; ?>"
          };
          $.ajax({
            url: 'query/selectsucursales.php',
            method: 'POST',
            dataType: 'json',
            data: data, // Los datos que quieres enviar
            success: function (data) {
              //console.log(data);
              // La función que se ejecuta cuando la consulta AJAX es exitosa
              var select = $('#sucursal' + k);

              // Limpia las opciones actuales en el select
              select.empty();

              if (data.length === 0 || data === 1 || data === 2) {
                // Si no hay resultados o el valor es 1 o 2, agrega una opción "Sin resultados"
                select.append($('<option></option>')
                  .attr('value', '')
                  .text('SIN RESULTADOS'));
              } else {
                // Si hay resultados, llena el select con las opciones obtenidas de la consulta
                $.each(data, function (key, value) {
                  select.append($('<option></option>')
                    .attr('value', value.id)
                    .text(value.nombre));
                });
              }
            }
          });
        }
      </script>
      <?php
      //////////////////////////////////////////////////////////////////////////////////////////////////////////
    }
  }
?>