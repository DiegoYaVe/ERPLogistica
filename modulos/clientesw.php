<?php
//  ini_set('display_errors', 1);
  class clientesw{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelclientesw(isset($obj));
    }
    function index(){
      if(!isset($_POST['cliente']))
        $_POST['cliente'] = NULL;
      if(!isset($_POST['email']))
        $_POST['email'] = NULL;
      if(!isset($_GET['estatus']))
        $_GET['estatus'] = 'A';
      if(!isset($_REQUEST['carrito']))
        $_REQUEST['carrito'] = 0;
      if(!isset($_REQUEST['webo']))
        $_REQUEST['webo'] = NULL;
      /*if(!isset($_GET['page']))
        $_GET['page'] = 1;
      $page = $_GET['page'] - 1; */
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->result($_REQUEST['page'],20,$_POST['cliente'],$_GET['estatus'],$_POST['email'],$_REQUEST['carrito'],$_POST['apellidos'],$_REQUEST['webo']);
      $this->view=new viewclientesw($this->model);
      $this->view->browse($_REQUEST['page'],20,$_POST['cliente'],$_GET['estatus'],$_POST['email'],$_REQUEST['carrito'],$_POST['apellidos'],$_REQUEST['webo']);
    }
    function edit(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      $this->model->select($_GET['id']);

      //$this->model->selectsucxidcliente($_GET['id']);
      $this->view = new viewclientesw($this->model);
      $this->view->edit();
    }
    function autorizacl(){
      $this->model->select($_GET['idc']);

      $sqlcl = 'SELECT c_idc FROM clientes WHERE c_id = "'.$_GET['idc'].'" AND c_idc != 0';
      $result = setq($sqlcl);
      list($clienteceo) = $result->fetch_array();
      $sqlc = 'SELECT * FROM clientes WHERE c_id = "'.$_GET['idc'].'"';
      $result = setq($sqlc);
      $rowc = $result->fetch_array();

      if(!$clienteceo){
        $alias= $rowc['c_nmb'].' JD SHOP';

        $sqlal = 'SELECT COUNT(*) FROM crm_clientes WHERE c_alias = "'.$alias.'"';
        $resultal = setq($sqlal) or die($sqlal);
        list($coincide) = $resultal->fetch_array();
        if($coincide > 0) $alias=$alias.'_'.($coincide+1);

        $sqlcc='SELECT MAX(c_id) FROM crm_clientes';
        $resultcc= setq($sqlcc) or die($sqlcc);
        list($idcl) = $resultcc->fetch_array();
        $idcl++;

        $sqlic ='
          INSERT INTO crm_clientes SET
          c_id="'.$idcl.'",
          c_empresa="1",
          c_almacen="1",
          c_alias = "'.$alias.'",
          c_nmb = "'.$rowc['c_nmb'].'",
          c_apellidos = "'.$rowc['c_apellidos'].'",
          c_sexo = "'.$rowc['c_sexo'].'",
          c_tipo = "P",
          c_fregistro = "'.date('Y-m-d H:i:s').'",
          c_uregistro = "JDSHOP",
          c_telefono1 = "'.$rowc['c_telefono'].'",
          c_telefono2 = "'.$rowc['c_celular'].'",
          c_correo1 = "'.$rowc['c_mail'].'",
          c_estatus="A",
          c_obs="CLIENTE AUTOMÁTICO DE JD SHOP",
          c_precio="'.$rowc['c_esquema'].'"';
        setq($sqlic) ;

        $sql = 'UPDATE clientes SET c_idc = "'.$idcl.'" WHERE c_id = "'.$_GET['idc'].'"';
        setq($sql);

        $sqldd = 'SELECT * FROM direnvio WHERE d_cliente = "'.$_GET['idc'].'"';
        $resultdd = setq($sqldd);
        while($rowd = $resultdd->fetch_array()){
          $sqlcc='SELECT MAX(cd_id) FROM crm_direcciones';
          $resultcc= setq($sqlcc) or die($sqlcc);
          list($iddir) = $resultcc->fetch_array();
          $iddir++;
          //cd_estado = "'.clearvmayus(busca($rowd['d_estado'],'estado','e_id','e_nmb')).'",
          $sqldr = 'INSERT INTO crm_direcciones SET
                    cd_id = "'.$iddir.'",
                    cd_cliente = "'.$idcl.'",
                    cd_tipodir = "6",
                    cd_calle = "'.$rowd['d_calle'].'",
                    cd_nume = "'.$rowd['d_nume'].'",
                    cd_numi = "'.$rowd['d_numi'].'",
                    cd_colonia = "'.$rowd['d_colonia'].'",
                    cd_municipio = "'.$rowd['d_municipio'].'",
                    cd_cp = "'.$rowd['d_cp'].'",
                    cd_estado = "'.busca($rowd['d_estado'],'estado','e_nmb','e_id').'",
                    cd_pais = "'.$rowd['d_pais'].'",
                    cd_predeterminada = "1",
                    cd_nmbdir = "'.$alias.'"';
          setq($sqldr);
        }
      }
      else{
        $idcl = $clienteceo;
        $sqlic ='
          UPDATE crm_clientes SET
          c_empresa="1",
          c_almacen="1",
          c_nmb = "'.$rowc['c_nmb'].'",
          c_apellidos = "'.$rowc['c_apellidos'].'",
          c_sexo = "'.$rowc['c_sexo'].'",
          c_tipo = "P",
          c_fregistro = "'.date('Y-m-d H:i:s').'",
          c_uregistro = "JDSHOP",
          c_telefono1 = "'.$rowc['c_telefono'].'",
          c_telefono2 = "'.$rowc['c_celular'].'",
          c_correo1 = "'.$rowc['c_mail'].'",
          c_estatus="A",
          c_obs="CLIENTE AUTOMÁTICO DE JD SHOP",
          c_precio="'.$rowc['c_esquema'].'"
          WHERE c_id="'.$idcl.'"';
        setq($sqlic) or die($sqlic);
      }
      redirect('?modulo=tableros&accion=index');

    }
    function insert(){
      $existecorreo = busca(trim($_POST['mail']),'clientes','c_mail','c_id');
      if($existecorreo){
        die(alert_back("El correo ya existe entre los clientes",true));
      }
      $sql = 'SELECT MAX(c_id) FROM clientes';
      $result = setq($sql) or die($sql);
      list($id) = $result->fetch_array();
      $id++;

      if($_POST['passcl'] != NULL){
        $passcl = $_POST['passcl'];
      }else{
        $passcl = NULL;
      }

      /* $sqld = 'SELECT MAX(d_id) FROM direnvio';
      $resultd = setq($sqld) or die($sqld);
      list($tdid) = $resultd>fetch_array();
      $did++; */

      $okcodigo = false;
      $codigo = NULL;

      $this->model->setData($id,$_POST['gender'],$_POST['nombre'],$_POST['apellidos'],$_POST['fnacimiento'],$_POST['mail'],$_POST['telefono'],$_POST['celular'],$_POST['estatus'],$_POST['esquema']
      ,$_POST['razon'],$_POST['rfc'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['cp'],$_POST['municipio'],$_POST['estado'],$_POST['pais'],$_POST['facturacion'],$passcl,$_POST['refimenfis']);
      $this->model->setdatadir($did,$id,$_POST['calles'],$_POST['numeros'],$_POST['interior'],$_POST['colonias'],
      $_POST['cps'],$_POST['municipios'],$_POST['estados'],$_POST['paiss'],$_POST['telefono'],$_POST['referencia'],"1");

      /* if(isset($_POST['facturacion'])){
        $rfc = busca($this->model->rfc,'clientes','c_rfc','c_rfc');
        if($rfc){
          die(alert_back("El RFC ya existe",true));
        }
      } */
      $this->model->insert();
      redirect('?modulo=clientesw&accion=index');
    }
    function update(){
      if($_POST['passcl'] != NULL){
        $passcl = $_POST['passcl'];
      }else{
        $passcl = NULL;
      }


      $this->model->setData($_POST['id'],$_POST['gender'],$_POST['nombre'],$_POST['apellidos'],$_POST['fnacimiento'],$_POST['mail'],$_POST['telefono'],$_POST['celular'],$_POST['estatus'],$_POST['esquema']
      ,$_POST['razon'],$_POST['rfc'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['cp'],$_POST['municipio'],$_POST['estados'],$_POST['pais'],$_POST['facturacion'],$passcl,$_POST['refimenfis']);
      $this->model->update($chkenvio,$ipcliente,$_POST['esquema']);
      //$did = busca($_POST['id'],'direnvio','d_predeterminado="1" AND d_cliente','d_id');
      $did = $_POST['did'];

      /* $this->model->setdatadir($did,$_POST['id'],$_POST['calles'],$_POST['numeros'],$_POST['interior'],$_POST['colonias'],
      $_POST['cps'],$_POST['municipios'],$_POST['estados'],$_POST['paiss'],$_POST['telefono'],$_POST['referencia'],"1");
      $this->model->updatedir(); */
      //redirect('?modulo=clientesw&accion=show&id='.$_POST['id'].'');
      redirect('?modulo=clientesw&accion=edit&id='.$_POST['id'].'&opt='.$_POST['opt'].'');
    }
    function disable(){
      $this->model->select($_GET['id']);
      $this->model->disable();
      header('Location:?modulo=clientesw&accion=show&id='.$this->model->id);
    }
    function enable(){
      $this->model->select($_GET['id']);
      $this->model->enable();
      header('Location:?modulo=clientesw&accion=show&id='.$this->model->id);
    }
    function show(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->model->selectenvio($_GET['id']);
      //$this->model->resultSucursal($_GET['id']);
      $this->view = new viewclientesw($this->model);
      $this->view->show();
    }
    function direcciones(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      $this->model->resultdir($_GET['id']);
      $this->view = new viewclientesw($this->model);
      $this->view->direcciones($_GET['id']);
    }
    function editdir(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      $this->model->selectdir($_GET['id']);
      $this->view = new viewclientesw($this->model);
      $this->view->editdir($_GET['c']);
    }
    function insertdir(){
      if($_POST['predeterminado']) $predeterminado = "1";
      else $predeterminado = "0";
      $estado = busca($_POST['estado'],'estado','e_id','e_nmb');
      $this->model->setdatadir($_POST['id'],$_POST['cliente'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],
      $_POST['cp'],$_POST['municipio'],$estado,$_POST['pais'],$_POST['telefono'],$_POST['referencia'],$predeterminado);
      $this->model->insertdir();
      //  $this->model->updatemismo($_POST['cliente'],'0');
      redirect('?modulo=clientesw&accion=edit&id='.$this->model->cliente.'&opt=d');
    }
    /*     function updatedir(){
      $estado = busca($_POST['estado'],'estado','e_id','e_nmb');
      $this->model->setdatadir($_POST['id'],$_POST['cliente'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],
      $_POST['cp'],$_POST['municipio'],$estado,$_POST['pais'],$_POST['telefono'],$_POST['referencia'],$_POST['predeterminado']);
      $this->model->updatedir();
      $this->model->updatemismo($_POST['cliente'],'0');
      redirect('?modulo=clientesw&accion=direcciones&id='.$this->model->cliente);
    } */
    function deletedir(){
      $cliente = busca($_GET['id'],'direnvio','d_id','d_cliente');
      $this->model->deletedir($_GET['id']);
      header('Location: ?modulo=clientesw&accion=direcciones&id='.$cliente.'');
    }
    function updatedir(){
      if(isset($_POST['predeterminada'])){
        $pred = "1";
      }else{
        $pres = "0";
      }
      $did = $_POST['did'];
      $this->model->setdatadir($did,$_POST['id'],$_POST['calles'],$_POST['numeros'],$_POST['interior'],$_POST['colonias'],
      $_POST['cps'],$_POST['municipios'],$_POST['estados'],$_POST['paiss'],$_POST['telefono'],$_POST['referencia'],$pred);
      $this->model->updatedir();
      //redirect('?modulo=clientesw&accion=show&id='.$_POST['id'].'');
      redirect('?modulo=clientesw&accion=edit&id='.$_POST['id'].'&opt=d');
    }
    //-------
  function insertext(){    //if(!isset($_GET['suc']) || empty( $_GET['suc'])) $_GET['suc'] = NULL;
       if(isset($_POST['nombre']) and isset($_POST['informacion'])){
         $this->model->setDataExt($_GET['cliente'],$_GET['suc'],$_POST['nombre'],$_POST['informacion']);
     $this->model->insertext();
      }else{
      header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&selec=servicio');
      }
    header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&selec=servicio');
  }
  function updateext(){	  //if(!isset($_GET['suc']) || empty( $_GET['suc'])) $_GET['suc'] = NULL;
    if($_POST['nombre'] and $_POST['informacion']){
      $this->model->setDataExt($_GET['cliente'],$_GET['suc'],$_POST['nombre'],$_POST['informacion']);
     $this->model->updateext();
    }else{
      header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&selec=servicio');
    }
    header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&selec=servicio');
  }


  function showsuc(){
    if(!isset($_GET['suc']))
      $_GET['suc']=NULL;
    $this->model->selectsucursal($_GET['suc']);
        $this->model->selectclien($this->model->clienteid);
    $this->model->selectext($_GET['suc']);
    $this->view=new viewclientesw($this->model);
    $this->view->showsuc();
  }
  function editsucursal(){
      if(!isset($_POST['nmbequipo']))
      $_POST['nmbequipo']=NULL;
    if(!isset($_GET['id']))
      $_GET['id']=NULL;
    $this->model->selectsucursal($_GET['id']);
    $this->view=new viewclientesw($this->model);
    $this->view->editsucursal();
  }
  function detallesuc(){
    if(!isset($_GET['select'])) $_GET['select']=NULL;
    if(!isset($_GET['id'])) $_GET['id']=NULL;
    if(!isset($_GET['suc'])) $_GET['suc']=NULL;
    if(!isset($_GET['cliente'])) $_GET['cliente']=NULL;
    $this->model->selectext1($_GET['id']);
    $this->model->selectsuc($_GET['suc']);
    $this->view=new viewclientesw($this->model);
    $this->view->detallesuc($_GET['cliente'],$_GET['suc']);
  }
  function insertsuc(){             //($id,$estatus,$calle,$exterior,$interior,$colonia,$codigoP,$municipio,$estado,$pais,$clienteid)
    $estatus="A";
    $this->model->setDataSucursal($_POST['id'],$estatus,$_POST['nombre'],$_POST['calle'],$_POST['exterior'],$_POST['interior'],$_POST['colonia'],$_POST['codigoP'],$_POST['municipio'],$_POST['estado'],$_POST['pais'],$_POST['emails']);
    $this->model->insertSuc();
    header('Location:?modulo=clientesw&accion=show&id='.$_GET['cliente'].'');
  }
  function updatesuc(){             //($id,$estatus,$calle,$exterior,$interior,$colonia,$codigoP,$municipio,$estado,$pais,$clienteid)
    $this->model->setDataSucursal($_POST['id'],$_POST['estatus'],$_POST['nombre'],$_POST['calle'],$_POST['exterior'],$_POST['interior'],$_POST['colonia'],$_POST['codigoP'],$_POST['municipio'],$_POST['estado'],$_POST['pais'],$_POST['sucursal'],$_POST['emails']);
    $this->model->updateSuc();
    header('Location:?modulo=clientesw&accion=show&id='.$_GET['cliente'].'');
  }
  function activarsuc(){
    $this->model->selectsucursal($_GET['id']);
    $this->model->activarsuc();
    header('Location:?modulo=clientesw&accion=show&id='.$this->model->clienteid.'');
  }
  function desactivarsuc(){
    $this->model->selectsucursal($_GET['id']);
    $this->model->desactivarsuc();
    header('Location:?modulo=clientesw&accion=show&id='.$this->model->clienteid.'');
  }
  function eliminardetsuc(){
    $this->model->eliminardetsuc($_GET['id']);
      header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc']);
  }
  function filtrar(){
    $this->model->filtrar($_POST['filtrocliente']);
    header('Location:?modulo=clientesw&accion=index&id='.$_POST['filtrocliente']);
  }
  function addnmbequipo(){
    $this->model->setDataaddnmbequipo($_POST['nmbequipo']);
    $this->model->addnmbequipo();
    header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&idnmb='.$this->model->id.'&select=equipo');
  }
  function editnmbequipo(){
    $this->model->setDataaddnmbequipo($_POST['nmbequipo']);
    $this->model->editnmbequipo();
    header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'');
  }
  function addservicioequipo(){
    $this->model->setDataaddservicioequipo($_POST['valor1'],$_POST['valor2'],$_POST['valor3']);
    $this->model->addservicioequipo();
    header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&idnmb='.$_GET['idnmb'].'&select=equipo');
  }
  function updateservicioequipo(){
    $this->model->setDataaddservicioequipo($_POST['valor1'],$_POST['valor2'],$_POST['valor3']);
    $this->model->updateservicioequipo();
    header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&idnmb='.$_GET['idnmb'].'&select=equipo');
  }
  function eliminarnmbequipo(){
    $this->model->eliminarnmbequipo($_GET['id']);
    header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].$this->model->error);
  }
  function eliminardetequipo(){
    $this->model->eliminardetequipo($_GET['id']);
    header('Location:?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&idnmb='.$_GET['idnmb'].'&select=equipo');
  }

    function edocta(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_POST['fini'])){
        $_POST['fini']=date('Y-m-').'01';
        $_POST['ffin']=date('Y-m-d');
      }
      $this->model->select($_GET['id']);
      $this->model->resultedoctac($_GET['id'],$_POST['fini'],$_POST['ffin']);
      $this->view = new viewclientesw($this->model);
      $this->view->edocta($_GET['id']);
    }

function bonificacion(){
  $host = busca(1,'configuracion','c_consecutivo','c_urlr');
  $user = busca(1,'configuracion','c_consecutivo','c_userr');
  $db = busca(1,'configuracion','c_consecutivo','c_dbr');
  $pass = busca(1,'configuracion','c_consecutivo','c_passr');
  if(!empty($host) AND !empty($user) AND !empty($db) AND !empty($pass)){
    $this->model->syncbonif($host,$user,$db,$pass,$_GET['id']);
  }
  if(!isset($_GET['id']))
    $_GET['id'] = NULL;
  $this->model->selectbonif($_GET['id']);
  $this->view = new viewclientesw($this->model);
  $this->view->bonificacion($_GET['id']);
}

function addbonif(){
  echo '
  <form action="?modulo=clientesw&accion=insertbonif&id='.$_GET['id'].'" method="post">
  <table class="lista" width="100%">
  <thead>
  <tr><th colspan="2">Nueva Bonificacion</th></tr>
  </thead><tbody>
  <tr><th>Monto</th><td><input type="number" name="monto" min="1" required style="width:50%"/></td></tr>
  <tr><th>Motivo</th><td><input type="text" name="motivo" maxlenght="150" required/></td></tr>
  <tr><td colspan="2"><input type="checkbox" required/> Autorización</td></tr>
  <tr><td colspan="2"><input type="submit" value="" id="aplicar" class="botont"/></td></tr>
  </tbody><table>
  </form>
  ';
}

function insertbonif(){
  if(!isset($_GET['id']))
    $_GET['id'] = NULL;
  $this->model->insertbonif($_GET['id'],$_POST['monto'],$_POST['motivo']);
  header('Location: ?modulo=clientesw&accion=bonificacion&id='.$_GET['id']);
}

function cancelboni(){
  if(!isset($_GET['id']))
    $_GET['id'] = NULL;

  $sqld = 'UPDATE bonificaciones SET b_estatus = "C",b_updated = "0" WHERE b_id = "'.$_GET['b'].'" AND b_cliente = "'.$_GET['id'].'"';

  setq($sqld) or die($sqld);
  header('Location: ?modulo=clientesw&accion=bonificacion&id='.$_GET['id']);
}

function listpedidos(){
  $host = busca(1,'configuracion','c_consecutivo','c_urlr');
  $user = busca(1,'configuracion','c_consecutivo','c_userr');
  $db = busca(1,'configuracion','c_consecutivo','c_dbr');
  $pass = busca(1,'configuracion','c_consecutivo','c_passr');
  if(!empty($host) AND !empty($user) AND !empty($db) AND !empty($pass)){
   // $this->model->sync($host,$user,$db,$pass);
  }
  if(!isset($_GET['page']))
    $_GET['page'] = 1;
  $page = $_GET['page'] - 1;

  $this->model->resultpedidos($page,20,$_GET['id']);
  $this->view=new viewclientesw($this->model);
  $this->view->browsepedidos($_GET['page'],20);
}
function updatedf(){
?>
<script>
$(document).ready(inicio);
function inicio(){
    $("#nombre").focusout(validanombre);
    $("#rfc").focusout(validarfc);
    //$("#calle").focusout(validacalle);
    $("#apellidos").focusout(validaapellidos);
}
function validaapellidos(){
  if (!nombre_valido($(this).val())) $(".error.apellidos").show();
  else $(".error.apellidos").hide();
}
function validacalle(){
  if (!alfanumerico_valido($(this).val())) $(".error.calle").show();
  else $(".error.calle").hide();
}
function validarfc(){
  if (!rfc_valido($(this).val())) $(".error.rfc").show();
  else $(".error.rfc").hide();
}
function validanombre(){
  if (!nombre_valido($(this).val())) $(".error.nombre").show();
  else $(".error.nombre").hide();
}
function nombre_valido(valor) {
    var reg = /^([a-z ñáéíóú]{2,150})$/i;
    if (reg.test(valor)) return true;
    else return false;
}
function rfc_valido(valor) {
    var reg = /^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$/;
    if (reg.test(valor)) return true;
    else return false;
}
function alfanumerico_valido(valor) {
    var reg = /^[0-9a-zA-Zñ]+( ?[0-9a-zA-Zñ])+$/;
    if (reg.test(valor)) return true;
    else return false;
}
</script>
<?php
  if(!isset($_GET['id']))
    $_GET['id'] = NULL;
  if(!isset($_GET['c']))
    $_GET['c'] = NULL;

  $this->model->select($_GET['c']);
  echo ' <form action="?modulo=clientesw&accion=updatedfact&id='.$_GET['id'].'&c='.$_GET['c'].'" method="post">';
  echo '<table class="lista" id="dfacturacion" border="1" width="100%">
  <thead>
  <tr>
  <th colspan="4">Información Fiscal</th>
  </tr>
  </thead>
  <!--<tr>
      <td colspan="4"><input type="checkbox" name="chkenvio" id="chkenvio" '.$chkigual.'>La dirección de envio es la misma que la de facturación</td>
  </tr>-->
  <tr>
  <th>RFC</th>
  <td><input type="text" name="rfc" id="rfc" maxlength="13" value="'.$this->model->rfc.'" required title="INGRESAR UN RFC VALIDO Y SIN ESPACIOS" pattern="^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$" >
  <br><label style="color:red; display:none;" class="error rfc">Ingrese un RFC válido</label></td>

  <th>Razon Social*</th>
  <td><input type="text" id="razon" name="razon" maxlength="100" value="'.$this->model->razon.'" required>
  <br><label style="color:red; display:none;" class="error razon">Ingrese un Razon Social válido</label></td>
  </tr>
  <tr class="denvio">
  <th>Calle*</th>
  <td><input type="text" name="calle" id="calle" maxlength="250" value="'.$this->model->calle.'" required>
  <br><label style="color:red; display:none;" class="error calle">Ingrese una Calle válida</label></td>
  <th>Número Exterior*</th>
  <td><input type="text" id="nume" name="nume" maxlength="15" value="'.$this->model->nume.'"  required></td>
  </tr><tr  class="denvio">
  <th>Número Interior</th>
  <td><input type="text" id="numi" name="numi" maxlength="15" value="'.$this->model->numi.'"></td>
  <th>Colonia*</center></th>
  <td><input type="text" id="colonia" name="colonia" maxlength="100" value="'.$this->model->colonia.'" required></td>
  </tr><tr  class="denvio">
  <th>Codigo Postal*</th>
  <td><input type="text" id="cp" name="cp" maxlength="5" value="'.$this->model->cp.'" pattern="[0-9]{5}" required></td>
  <th>Municipio*</th>
  <td><input type="text" id="municipio" name="municipio" maxlength="100" value="'.$this->model->municipio.'" required> </td>
  </tr><tr class="denvio">
  <th>Estado*</th>
  <td>
  ';
        $sql='SELECT * FROM estado WHERE e_estatus="A"';
        $rs=setq($sql) or die($sql);

  echo '<select name="estado" id="estado" required>
            <option >SELECCIONAR ESTADO</option>';
        while($rw1=$rs->fetch_array()){
          if($this->model->estado==$rw1['e_id']) $sele='selected';else $sele=NULL;
          echo '<option value="'.$rw1['e_id'].'" '.$sele.'>'.$rw1['e_nmb'].'</option>';
        }
  echo '</select></td>
  <th>País*</th>
  <td><input type="text" id="pais" name="pais" maxlength="100" value="'.$this->model->pais.'" required></td>
  </tr>
  <tr><td colspan="4"><input type="submit" value="" id="guardar" class="botont"/></td></tr>
  </table>';
  echo '</form>';

}
function updatedfact(){
  $this->model->setdatafact($_POST['razon'],$_POST['rfc'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['cp'],$_POST['municipio'],$_POST['estado'],$_POST['pais']);

  $sql = 'UPDATE clientes SET
  c_rfc = "'.$this->model->rfc.'",
  c_razon = "'.$this->model->razon.'",
  c_calle = "'.$this->model->calle.'",
  c_nume = "'.$this->model->nume.'",
  c_numi = "'.$this->model->numi.'",
  c_colonia = "'.$this->model->colonia.'",
  c_cp = "'.$this->model->cp.'",
  c_municipio = "'.$this->model->municipio.'",
  c_estado = "'.$this->model->estado.'",
  c_pais = "'.$this->model->pais.'"
  WHERE c_id = "'.$_GET['c'].'"';
  setq($sql) or die($sql);

  $host = busca(1,'configuracion','c_consecutivo','c_urlr');
  $user = busca(1,'configuracion','c_consecutivo','c_userr');
  $db = busca(1,'configuracion','c_consecutivo','c_dbr');
  $pass = busca(1,'configuracion','c_consecutivo','c_passr');
  if(!empty($host) AND !empty($user) AND !empty($db) AND !empty($pass)){
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
    $mysqli->real_connect($host,$user,$pass,$db);
    if ($mysqli->ping()){
      $mysqli->query($sql) or die($sql);
    }
    $mysqli->close();
    include("db.php");
  }

  header('Location: ?modulo=pedidoscl&accion=show&id='.$_GET['id']);
}

function adddir(){
   echo '<center>
  <form autocomplete="off" method="post" name="pro"  action="?modulo=clientesw&accion='.$accion.$envio.'" onsubmit="return checkSubmitguardar();">
  <table border="1" class="lista" width="50%">';
  echo '<thead><tr><th colspan="2">'.$titulo.'</th></tr></thead>';
  echo '<tr><th><b>Id</b><input type="hidden" name="id" value="'.$this->model->id.'"></th><td>'.$this->model->id.'</td></tr>';
  echo '<tr><th><b>Cliente</b><input type="hidden" name="cliente" value="'.$cliente.'"></th><td>'.busca($cliente,'clientes','c_id','c_nmb').' '.busca($cliente,'clientes','c_id','c_apellidos').'</td></tr>';
  echo '<tr><th><b>Calle</b></th><td>
  <input type="text" name="calle" size="25" maxlength="80"
  value="' . $this->model->calle . '" required autofocus></td></tr>';
  echo '<tr><th><b>Número Exterior</b></th><td>
  <input type="text" name="nume" size="15" maxlength="80"
  value="' . $this->model->nume . '" required></td></tr>';
  echo '<tr><th><b>Número Interior</b></th><td>
  <input type="text" name="numi" size="15" maxlength="80"
  value="' . $this->model->numi . '"></td></tr>';
  echo '<tr><th><b>Colonia</b></th><td>
  <input type="text" name="colonia" size="15" maxlength="80"
  value="' . $this->model->colonia . '" required></td></tr>';
  echo '<tr><th><b>Codigo postal</b></th><td>
  <input type="text" name="cp" size="5" maxlength="5"
  value="' . $this->model->cp . '" required></td></tr>';
  echo '<tr><th><b>Municipio</b></th><td>
  <input type="text" name="municipio" size="25" maxlength="100"
  value="' . $this->model->municipio. '" required></td></tr>';
  echo '<tr><th>Estado</th><td>
  ';
        $sql='SELECT * FROM estado WHERE e_estatus="A"';
        $rs=setq($sql) or die($sql);

  echo '<select name="estado" id="estado" required>
            <option >SELECCIONAR ESTADO</option>';
        while($rw1=$rs->fetch_array()){
          if($this->model->estado==$rw1['e_id']) $sele='selected';else $sele=NULL;
          echo '<option value="'.$rw1['e_id'].'" '.$sele.'>'.$rw1['e_nmb'].'</option>';
        }
  echo '</select>
  </td></tr>';
  echo '<tr><th>Pais</th><td>
  <input type="text" name="pais" size="25" maxlength="100"
  value="' . $this->model->pais. '" required></td></tr>';
  echo '<tr><th><b>Teléfono</b></th><td>
  <input type="text" name="telefono" size="15" maxlength="15"
  value="' . $this->model->telefono . '"></td></tr>';
  echo '<tr><th><b>Referencia</b></th><td>
  <textarea name="referencia" cols="50" rows="5" maxlength="250">' . $this->model->referencia.'</textarea></td></tr>';
  if ($this->model->predeterminado == 1){
    echo'<tr><th><b>Dirección Predeterminada</b></th><td>
  <input type="checkbox" name="predeterminado" checked> </td></tr>';
  }
  else{
    echo'<tr><th><b>Dirección Predeterminada</b></th><td>
  <input type="checkbox" name="predeterminado"> </td></tr>';
  }
  echo'<tr><td align="center" colspan="2">
  <center><input type="submit" value="" id="guardar" class="botont"></center>
  </td></tr>
  </table></form>';
}

function jdshopacademy(){
  $this->model->resultacademy();
  $this->view = new viewclientesw($this->model);
  $this->view->browseacademy();
}
function editacademy(){
  $this->model->selectacademy($_GET['id']);
  $this->view = new viewclientesw($this->model);
  $this->view->editacademy();
}
function updateacademy(){
  if($_POST['estatus']) $estatus = "A";
  else $estatus = "I";
  $this->model->setdataacademy($_GET['id'],$_POST['nombre'],$_POST['apellidos'],$_POST['correo'],$estatus,$_POST['grupo'],$_POST['telefono']); 
  $this->model->updateacademy();
  if($_POST['preguntapass']){
    $sql = 'UPDATE usuarios SET
            u_contraseña = PASSWORD("'.$_POST['password'].'")
            WHERE u_id = "'.$this->model->id.'"';
    setqacademy($sql);
  }

  redirect('?modulo=clientesw&accion=editacademy&id='.$this->model->id.'');
}
function insertacademy(){

  if($_POST['estatus']) $estatus = "A";
  else $estatus = "I";


  $existecorreo = buscaAcademy($_POST['correo'],'usuarios','u_correo','u_id');
  if($existecorreo){
    die(alert_back("El correo ingresado ya existe, verificar",true));
  }

  $this->model->setdataacademy(NULL,$_POST['nombre'],$_POST['apellidos'],$_POST['correo'],$estatus,$_POST['grupo'],$_POST['telefono']); 
  $this->model->insertacademy();
  $id = getmaxacademy('u_id','usuarios',false,false);

  if($_POST['preguntapass']){
    $sql = 'UPDATE usuarios SET
            u_contraseña = PASSWORD("'.$_POST['password'].'")
            WHERE u_id = "'.$id.'"';
    setqacademy($sql);
  }

  $sqlbusca = 'SELECT ra_id FROM registros_academy WHERE ra_correo = "'.$_POST['correo'].'"';
  $resultbusca = setq($sqlbusca);
  if($resultbusca->num_rows == 0){
    $sql =  'INSERT INTO registros_academy SET
            ra_correo = "'.$_POST['correo'].'",
            ra_fechar = "'.date('Y-m-d').'",
            ra_registrado = "1",
            ra_origen = "1"'; // origen 1 == jdshop.mx
    setq($sql);
  }else{
    list($iduc) = $resultbusca->fetch_array();
    $sql = 'UPDATE registros_academy SET
            ra_registrado = "1"
            WHERE ra_id = "'.$iduc.'"';
    setq($sql);
  }

  redirect('?modulo=clientesw&accion=editacademy&id='.$id.'');
}
function addcursopedido(){

  
  $precio = buscaacademy($_POST['curso'],'cursos','c_id','c_costo');
  $id = $_GET['id'];
  $sql = 'INSERT INTO usuarios_pedidos SET
          up_usuario = "'.$_GET['id'].'",
          up_curso = "'.$_POST['curso'].'",
          up_estatus = "A",
          up_costo = "'.$precio.'",
          up_fpago = "1",
          up_fecha = "'.date('Y-m-d H:i:s').'"';
  setqacademy($sql);

  ///

  $pedido = buscaacademy($_GET['id'],'usuarios_pedidos','up_curso = "'.$_POST['curso'].'" AND up_estatus = "A" AND up_usuario','up_id');

  $sql = 'SELECT * FROM usuarios_pedidos INNER JOIN usuarios ON u_id = up_usuario INNER JOIN cursos ON c_id = up_curso INNER JOIN formaspago ON f_id = up_fpago WHERE up_id = "'.$pedido.'"';
  $result = setqacademy($sql);
  $row = $result->fetch_array();

  $sqlcliente = 'SELECT * FROM usuarios WHERE u_id = "'.$row['up_usuario'].'"';
  $resultcliente = setqacademy($sqlcliente);
  $rowcliente = $resultcliente->fetch_array();
  //$row['up_costo'] =  "0.01";

  $sql = 'INSERT INTO crm_tableros SET
          ct_empresa = "1",
          ct_folio = "1",
          ct_cliente = "1560",
          ct_agente = "JDSHOP ACADEMY",
          ct_fini = "'.date('Y-m-d').'",
          ct_hini = "'.date('H:i:s').'",
          ct_visibility = "1",
          ct_nmb = "CURSO JDSHOP ACADEMY - '.clearvmayus($row['c_nmb']).'",
          ct_fcierre = "'.date('Y-m-d', strtotime('+15 days')).'",
          ct_estatus = "N",
          ct_pedidoweb = NULL,
          ct_nnegociacion = "2"';
  setq($sql);

  $idtablero = getmax('ct_id','crm_tableros',false,false);  
  $folioint = getmax('r_folio','remisiones','r_empresa = "1"');

  $sql = 'INSERT INTO remisiones SET
          r_tablero = "'.$idtablero.'",
          r_folio = "'.$folioint.'",
          r_cliente = "1560",
          r_empresa = "1",
          r_almacen = "1",
          r_estatus = "N",
          r_subtotal = "'.$row['up_costo'].'",
          r_iva = "0.00",
          r_mdescuento = "0.00",
          r_total = "'.$row['up_costo'].'",
          r_descuento = "0.00",
          r_nmb = "CURSO JDSHOP ACADEMY - '.clearvmayus($rowcliente['u_nmb']).' '.clearvmayus($rowcliente['u_apellidos']).'",
          r_email = "'.$rowcliente['u_correo'].'",
          r_encargado = "JDSHOP ACADEMY",
          r_fliquidacion = "'.date('Y-m-d').'",
          r_ualta = "JDSHOP ACADEMY",
          r_falta = "'.date('Y-m-d H:i:s').'",
          r_faplica = "'.date('Y-m-d H:i:s').'",
          r_diva = "0"';
  setq($sql);
  $idremision = getmax('r_id','remisiones',false,false);
  $sql = 'INSERT INTO remisionesd SET
          rd_remision = "'.$idremision.'",
          rd_articulo = "0",
          rd_nmbarticulo = "CURSO - '.clearvmayus($row['c_nmb']).'",
          rd_linean = "19",
          rd_cantidad = "1",
          rd_precio = "'.$row['up_costo'].'",
          rd_costo = "0.00",
          r_descuento = "0.00",
          rd_iva = "0"';
  setq($sql);

  /* $sql = 'INSERT INTO reportepago SET
          r_pedido = "'.$pedido.'",
          r_cuenta = "'.$cuenta.'",
          r_fecha = "'.date('Y-m-d', strtotime($fecha)).'",
          r_hora = "'.date('H:i', strtotime($hora)).'",
          r_monto = "'.$monto.'",
          r_referencia = "'.$referencia.'",
          r_observacion = "'.$observaciones.'",
          r_estatus = "R",
          r_falta = "'.date('Y-m-d').'",
          r_adj = "'.$ruta_nuevo_destino.'"';
  setqacademy($sql);s

  $sqladdr = 'UPDATE reportepago SET
              r_remision = "'.$idremision.'"
              WHERE r_pedido = "'.$pedido.'"';
  setqacademy($sqladdr); */


  redirect('?modulo=clientesw&accion=editacademy&id='.$id.'');
}

function estatusacademy(){
  $id = $_GET['id'];
  $sql = 'UPDATE usuarios_pedidos SET
          up_estatus = "'.$_GET['estatus'].'"
          WHERE up_id = "'.$id.'"';
  setqacademy($sql);
  redirect('?modulo=clientesw&accion=editacademy&id='.$_GET['cliente'].'');
}

}


class modelclientesw{

function result($page,$pageresult,$cliente,$estatus,$email,$carrito,$apellidos,$webo){
  $bloque = 50;
  $addsql = '';
  if($cliente){
    $sqlcl = 'SELECT c_id FROM clientes WHERE c_nmb LIKE "%'.$cliente.'%"';
    if($apellidos)
      $sqlcl .= 'UNION SELECT c_id FROM clientes WHERE c_apellidos LIKE "%'.$apellidos.'%"';
    $addsql .= ' AND c_id IN('.$sqlcl.')';
  }
  elseif($apellidos)
    $addsql .= ' AND c_apellidos LIKE "%'.$apellidos.'%"';
  if($email)
    $addsql .= ' AND c_mail LIKE "%'.$email.'%"';
  $sqlc = '';
  if($carrito == 1){
    $sqlc = 'AND c_id IN (SELECT DISTINCT p_cliente FROM pedidoscl WHERE p_estatus = "N")';
  }
  elseif($carrito == 2){
    $sqlc = 'AND c_id NOT IN (SELECT DISTINCT p_cliente FROM pedidoscl WHERE p_estatus = "N")';
  }
  if($webo) $sqlc.=' AND c_weborigen = "'.$webo.'"';

  $sql='SELECT * FROM clientes WHERE c_estatus = "'.$estatus.'" '.$addsql.' '.$sqlc.' ORDER BY c_id DESC LIMIT '.($bloque*$page).','.$bloque.'';
  $this->result = setq($sql) or die($sql);
  $this->resultt = setq($sql) or die($sql);
}

function select($id){
  $sql = 'SELECT * FROM clientes WHERE c_id = "'.$id.'"';
  $result = setq($sql) or die($sql);
  $row = $result->fetch_array();
  $this->id = $row['c_id'];
  $this->nombre = $row['c_nmb'];
  $this->apellidos = $row['c_apellidos'];
  $this->sexo = $row['c_sexo'];
  $this->fnacimiento = $row['c_fnacimiento'];
  $this->mail = $row['c_mail'];
  $this->telefono = $row['c_telefono'];
  $this->celular = $row['c_celular'];
  $this->estatus = $row['c_estatus'];
  $this->rfc = $row['c_rfc'];
  $this->razon = $row['c_razon'];
  $this->calle = $row['c_calle'];
  $this->nume = $row['c_nume'];
  $this->numi = $row['c_numi'];
  $this->colonia = $row['c_colonia'];
  $this->cp = $row['c_cp'];
  $this->municipio = $row['c_municipio'];
  $this->estado = $row['c_estado'];
  $this->pais = $row['c_pais'];
  $this->chkigual = $row['c_chkigual'];
  $this->facturacion = $row['c_facturacion'];
  $this->ocupacion = $row['c_ocupacion'];
  $this->bonificacion = $row['c_bonificacion'];
  $this->falta = $row['c_falta'];
  $this->hcomentario = $row['c_hcomentario'];
  $this->origenc = $row['c_origen'];
  $this->esquema = $row['c_esquema'];
  $this->sendcat = $row['c_sendcat'];
  $this->idc = $row['c_idc'];
  $this->regimen = $row['c_regimenfis'];
}

function setData($id,$sexo,$nombre,$apellidos,$fnacimiento,$mail,$telefono,$celular,$estatus,$esquema,$razon,$rfc,$calle,$nume,$numi,$colonia,$cp,$municipio,$estado,$pais,$facturacion,$passcl,$regimenfis){
  mb_internal_encoding("UTF-8");
  $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
  $cambio = "";

  $this->id = $id;
  $this->sexo = $sexo;
  $this->nombre = str_replace($simbol,$cambio, mb_strtoupper(trim($nombre)));
  $this->apellidos = str_replace($simbol,$cambio, mb_strtoupper(trim($apellidos)));
  $this->fnacimiento = $fnacimiento;
  $this->mail = trim($mail);
  $this->telefono = $telefono;
  $this->celular = $celular;
  $this->esquema = $esquema;

  $this->razon = str_replace($simbol,$cambio, mb_strtoupper(trim($razon)));
  $this->rfc = str_replace($simbol,$cambio, mb_strtoupper(trim($rfc)));
  $this->calle = str_replace($simbol,$cambio, mb_strtoupper(trim($calle)));
  $this->nume = str_replace($simbol,$cambio, mb_strtoupper(trim($nume)));
  $this->numi = str_replace($simbol,$cambio, mb_strtoupper(trim($numi)));
  $this->colonia = str_replace($simbol,$cambio, mb_strtoupper(trim($colonia)));
  $this->cp = $cp;
  $this->municipio = str_replace($simbol,$cambio, mb_strtoupper(trim($municipio)));
  $this->estado = str_replace($simbol,$cambio, mb_strtoupper(trim($estado)));
  $this->pais = str_replace($simbol,$cambio, mb_strtoupper(trim($pais)));
  $this->estatus = "I";
  $this->passcl  = $passcl;
  $this->regimenfis = $regimenfis;

  if($estatus)
    $this->estatus = "A";

  $this->facturacion = "0";
  if($facturacion)
    $this->facturacion = "1";
}

function setdatafact($razon,$rfc,$calle,$nume,$numi,$colonia,$cp,$municipio,$estado,$pais){
  mb_internal_encoding("UTF-8");
  $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
  $cambio = "";

  $this->razon = str_replace($simbol,$cambio, mb_strtoupper(trim($razon)));
  $this->rfc = str_replace($simbol,$cambio, mb_strtoupper(trim($rfc)));
  $this->calle = str_replace($simbol,$cambio, mb_strtoupper(trim($calle)));
  $this->nume = str_replace($simbol,$cambio, mb_strtoupper(trim($nume)));
  $this->numi = str_replace($simbol,$cambio, mb_strtoupper(trim($numi)));
  $this->colonia = str_replace($simbol,$cambio, mb_strtoupper(trim($colonia)));
  $this->cp = $cp;
  $this->municipio = str_replace($simbol,$cambio, mb_strtoupper(trim($municipio)));
  $this->estado = str_replace($simbol,$cambio, mb_strtoupper(trim($estado)));
  $this->pais = str_replace($simbol,$cambio, mb_strtoupper(trim($pais)));
}

function insert(){

  if($this->passcl != NULL){
    //$sql = 'SELECT PASSWORD("'.$this->passcl.'") FROM clientes WHERE c_id = "'.$this->id.'"';
    //$result = setq($sql);
    //$row = $result->fetch_array();
    $concat = ', c_password = PASSWORD("'.$this->passcl.'")';
  }else{
    $concat = ' ';
  }

  $sql = 'INSERT INTO clientes SET
  c_id = "'.$this->id.'",
  c_nmb = "'.$this->nombre.'",
  c_apellidos = "'.$this->apellidos.'",
  c_sexo = "'.$this->sexo.'",
  c_fnacimiento = "'.$this->fnacimiento.'",
  c_mail = "'.$this->mail.'",
  c_telefono = "'.$this->telefono.'",
  c_celular = "'.$this->celular.'",
  c_rfc = "'.$this->rfc.'",
  c_razon = "'.$this->razon.'",
  c_calle = "'.$this->calle.'",
  c_nume = "'.$this->nume.'",
  c_numi = "'.$this->numi.'",
  c_colonia = "'.$this->colonia.'",
  c_cp = "'.$this->cp.'",
  c_regimenfis = "'.$this->regimenfis.'",
  c_municipio = "'.$this->municipio.'",
  c_estado = "'.$this->estado.'",
  c_pais = "'.$this->pais.'",
  c_estatus = "'.$this->estatus.'",
  c_facturacion = "'.$this->facturacion.'",
  c_esquema = "'.$this->esquema.'",
  c_falta = "'.date('Y-m-d H:i:s').'",
  c_ualta = "'.$_SESSION['uid'].'" '.$concat.'';
  setq($sql);

  /*
    $sqld = 'INSERT INTO direnvio SET
    d_id = "'.$this->did.'",
    d_cliente = "'.$this->id.'",
    d_calle = "'.$this->calles.'",
    d_nume = "'.$this->numes.'",
    d_numi = "'.$this->numis.'",
    d_colonia = "'.$this->colonias.'",
    d_cp = "'.$this->cps.'",
    d_municipio = "'.$this->municipios.'",
    d_estado = "'.$this->estados.'",
    d_pais = "'.$this->paiss.'",
    d_telefono = "'.$this->telefonos.'",
    d_referencia = "'.$this->referencias.'",
    d_predeterminado = "1"';
    setq($sqld);
  */

  if(busca($this->mail,'registros_distribuidores','rd_correo','COUNT(*)') == 0){
    $sql = 'INSERT INTO registros_distribuidores SET
            rd_nmb = "'.$this->nombre .' '.$this->apellidos.'",
            rd_telefono = "'.$this->telefono .'",
            rd_correo = "'.$this->mail .'",
            rd_fecharegistro = "'.date('Y-m-d') .'",
            rd_estatus = "A"';
    setq($sql);
  }
}

function update(){
  if($this->passcl != NULL){
    $sql = 'SELECT PASSWORD("'.$this->passcl.'") FROM clientes WHERE c_id = "'.$this->id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $concat = ', c_password = "'.$row['PASSWORD("'.$this->passcl.'")'].'"';
  }else{
    $concat = ' ';
  }

  $sql = 'UPDATE clientes SET
  c_nmb = "'.$this->nombre.'",
  c_apellidos = "'.$this->apellidos.'",
  c_sexo = "'.$this->sexo.'",
  c_fnacimiento = "'.$this->fnacimiento.'",
  c_mail = "'.$this->mail.'",
  c_telefono = "'.$this->telefono.'",
  c_celular = "'.$this->celular.'",
  c_rfc = "'.$this->rfc.'",
  c_razon = "'.$this->razon.'",
  c_calle = "'.$this->calle.'",
  c_nume = "'.$this->nume.'",
  c_numi = "'.$this->numi.'",
  c_colonia = "'.$this->colonia.'",
  c_cp = "'.$this->cp.'",
  c_regimenfis = "'.$this->regimenfis.'",
  c_municipio = "'.$this->municipio.'",
  c_estado = "'.$this->estado.'",
  c_pais = "'.$this->pais.'",
  c_estatus = "'.$this->estatus.'",
  c_facturacion = "'.$this->facturacion.'",
  c_esquema = "'.$this->esquema.'",
  c_fmod = "'.date('Y-m-d H:i:s').'",
  c_umod = "'.$_SESSION['uid'].'",
  c_updated = "0"'.$concat.'
  WHERE c_id = "'.$this->id.'"';
  setq($sql) or die($sql);
}
function enable(){
  $sql='UPDATE clientes set c_estatus = "A",c_updated = "0" WHERE c_id="'.$this->id.'"';
  setq($sql) or die($sql);
  //  $sql='UPDATE sucursales SET s_estatus="A" WHERE s_clienteid="'.$_GET['id'].'"';
  //  setq($sql) or die($sql);
}

function disable(){
  //if($_GET['id'] !== "1"){
    $sql='UPDATE clientes set c_estatus = "I",c_updated = "0" WHERE c_id="'.$this->id.'"';
    setq($sql) or die($sql);
    /*$sql='UPDATE sucursales SET s_estatus="I" WHERE s_clienteid="'.$_GET['id'].'"';
    setq($sql) or die($sql);
  }
  else{
    $this->error="&error=1";
  }*/
}

function selectenvio($id){
  $sql = 'SELECT * FROM direnvio WHERE d_cliente = "'.$id.'" AND d_predeterminado = "1"';
  $result = setq($sql);
  $row = $result->fetch_array();
  $this->eid = $row['d_id'];
  $this->etelefono = $row['d_telefono'];
  $this->ecalle = $row['d_calle'];
  $this->enume = $row['d_nume'];
  $this->enumi = $row['d_numi'];
  $this->ecolonia = $row['d_colonia'];
  $this->ecp = $row['d_cp'];
  $this->emunicipio = $row['d_municipio'];
  $this->eestado = $row['d_estado'];
  $this->epais = $row['d_pais'];
  $this->ereferencia = $row['d_referencia'];
}

function resultdir($id){
  $sql = 'SELECT * FROM direnvio WHERE d_id = "'.$id.'" ORDER BY d_id ASC';
  $this->result = setq($sql) or die($sql);
}

function selectdir($id){
  $sql = 'SELECT * FROM direnvio WHERE d_id = "'.$id.'"';
  $result = setq($sql) or die($sql);
  $row = $result->fetch_array();
  $this->id = $row['d_id'];
  $this->cliente = $row['d_cliente'];
  $this->calle = $row['d_calle'];
  $this->nume = $row['d_nume'];
  $this->numi = $row['d_numi'];
  $this->municipio = $row['d_municipio'];
  $this->cp = $row['d_cp'];
  $this->colonia = $row['d_colonia'];
  $this->estado = $row['d_estado'];
  $this->pais = $row['d_pais'];
  $this->telefono = $row['d_telefono'];
  $this->referencia = $row['d_referencia'];
  $this->predeterminado = $row['d_predeterminado'];
}

function setdatadir($id,$cliente,$calle,$nume,$numi,$colonia,$cp,$municipio,$estado,$pais,$telefono,$referencia,$predeterminado){
  mb_internal_encoding("UTF-8");
  $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
  $cambio = "";

  $this->did = $id;
  $this->cliente = $cliente;
  $this->calles = str_replace($simbol,$cambio, mb_strtoupper(trim($calle)));
  $this->numes = str_replace($simbol,$cambio, mb_strtoupper(trim($nume)));
  $this->numis = str_replace($simbol,$cambio, mb_strtoupper(trim($numi)));
  $this->colonias = str_replace($simbol,$cambio, mb_strtoupper(trim($colonia)));
  $this->cps = $cp;
  $this->municipios = str_replace($simbol,$cambio, mb_strtoupper(trim($municipio)));
  //$this->estados = str_replace($simbol,$cambio, mb_strtoupper(trim($estado)));
  $this->estados = str_replace($simbol,$cambio, trim($estado));
  $this->paiss = str_replace($simbol,$cambio, mb_strtoupper(trim($pais)));
  $this->telefonos = $telefono;
  $this->referencias = str_replace($simbol,$cambio, mb_strtoupper(trim($referencia)));;

  $this->predeterminado = 0;
  if($predeterminado)
    $this->predeterminado = 1;
}

function insertdir(){
  $sql = 'SELECT MAX(d_id) FROM direnvio';
  $result = setq($sql) or die($sql);
  list($this->id) = $result->fetch_array();
  $this->id++;

  $sqli = 'INSERT INTO direnvio SET
  d_id = "'.$this->id.'",
  d_cliente = "'.$this->cliente.'",
  d_calle = "'.$this->calles.'",
  d_nume = "'.$this->numes.'",
  d_numi = "'.$this->numis.'",
  d_colonia = "'.$this->colonias.'",
  d_cp = "'.$this->cps.'",
  d_municipio = "'.$this->municipios.'",
  d_estado = "'.$this->estados.'",
  d_pais = "'.$this->paiss.'",
  d_telefono = "'.$this->telefonos.'",
  d_referencia = "'.$this->referencias.'",
  d_predeterminado = "'.$this->predeterminado.'"';
  setq($sqli) or die($sqli);

  if($this->predeterminado == 1){
    $sqlu = 'UPDATE direnvio SET
    d_predeterminado = "0" WHERE d_id != "'.$this->id.'"AND d_cliente = "'.$this->cliente.'"';
    setq($sqlu) or die($sqlu);
  }
}


function updatedir(){
  $sqlu = 'UPDATE direnvio SET
  d_calle = "'.$this->calles.'",
  d_nume = "'.$this->numes.'",
  d_numi = "'.$this->numis.'",
  d_colonia = "'.$this->colonias.'",
  d_cp = "'.$this->cps.'",
  d_municipio = "'.$this->municipios.'",
  d_estado = "'.$this->estados.'",
  d_pais = "'.$this->paiss.'",
  d_telefono = "'.$this->telefonos.'",
  d_referencia = "'.$this->referencias.'",
  d_predeterminado = "'.$this->predeterminado.'"
  WHERE d_id = "'.$this->did.'"';
  setq($sqlu) or die($sqlu);
  if($this->predeterminado == 1){
    $sqlu = 'UPDATE direnvio SET
    d_predeterminado = "0" WHERE d_id != "'.$this->did.'"AND d_cliente = "'.$this->cliente.'"';
    setq($sqlu) or die($sqlu);
  }
}

function deletedir($id){
  $sqld = 'DELETE FROM direnvio WHERE d_id = "'.$id.'"';
  setq($sqld) or die($sqld);
}

//----------
function resultest($esta){
    if(!isset($_POST['filtroproveedor'])){
        $paginas=($_GET['pag']-1)*20;
        $pag = ' limit 20 offset '.$paginas.'';
    }
    $sql='SELECT * FROM clientes where c_estatus="'.$esta.'"  ORDER BY c_alias '.$pag;
    $this->resultest=setq($sql) or die($sql);
  }

  function selectext($ids){
    $sql = 'SELECT * FROM sucextra WHERE e_sucursal="'.$ids.'"';
    $this->selectext=setq($sql) or die($sql);
  }
  function selectext1($idc){
    $sql = 'SELECT * FROM sucextra WHERE e_id="'.$idc.'"';
    $result = setq($sql);
    $row=$result->fetch_array();
    $this->eid=$row['e_id'];
    $this->enombre=$row['e_nmb'];
    $this->einformacion=$row['e_informacion'];
  }
  function selectsuc($ids){
    $sql='SELECT * FROM sucursales WHERE s_id="'.$ids.'"';
    $result = setq($sql);
    $row=$result->fetch_array();
    $this->sid=$row['s_id'];
    $this->sclienteid=$row['s_clienteid'];
  }
    function selectsucxidcliente($id){
        $sql='SELECT * FROM sucursales WHERE s_clienteid="'.$id.'"';
        $result=setq($sql);
        $row=$result->fetch_array();
        $this->email=$row['s_email'];
        $this->calle=$row['s_calle'];
    	$this->exterior=$row['s_exterior'];
    $this->interior=$row['s_interior'];
    $this->colonia=$row['s_colonia'];
    $this->municipio=$row['s_municipio'];
        $this->extension=$row['s_ext'];
    $this->estado=$row['s_estado'];
    $this->pais=$row['s_pais'];
    $this->codigoP=$row['s_cp'];
        $this->telefono=$row['s_telefono'];
        $this->celular=$row['s_celular'];
        $this->pais=$row['s_pais'];
    }


  function activarsuc(){
    $sql='SELECT s_clienteid FROM clientes INNER JOIN sucursales ON c_id=s_clienteid WHERE s_id="'.$_GET['id'].'"';
      $res=setq($sql) or die($sql);
      list($id) = mysql_fetch_array($res);
      $sql='SELECT c_estatus FROM clientes WHERE c_id="'.$id.'"';
      $res=setq($sql) or die($sql);
      list($estatus) = mysql_fetch_array($res);
      if($estatus=='A'){
        $sql='UPDATE Sucursales SET s_estatus="A" WHERE s_id="'.$_GET['id'].'"';
    setq($sql) or die($sql);
      }
  }
  function desactivarsuc(){
    $sql='UPDATE sucursales SET s_estatus="I" WHERE s_id="'.$_GET['id'].'"';
    setq($sql) or die($sql);
  }
    function reemplazar($texto){
      $simbol = array('"',"'");
      $cambio = array('\"',"\'");
      $texto=str_replace($simbol,$cambio, $texto);
      return $texto;
    }

    function setDataExt($cliente,$sucursal,$nombre,$informacion){
      mb_internal_encoding("UTF-8");
      $vowels = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|");
      $this->cliente=str_replace($vowels,"", mb_strtoupper(trim($cliente)));
    $this->sucursal=str_replace($vowels,"", mb_strtoupper(trim($sucursal)));
    $this->nombre=str_replace($vowels,"", mb_strtoupper(trim($nombre)));
        $this->informacion=str_replace($vowels,"", mb_strtoupper(trim($informacion)));
  }
    function setDataaddnmbequipo($nmbequipo){
      mb_internal_encoding("UTF-8");
        $vowels = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|");
        $this->nmbequipo=str_replace($vowels," ", mb_strtoupper(trim($nmbequipo)));
    }
    function setDataaddservicioequipo($valor1,$valor2,$valor3){
      mb_internal_encoding("UTF-8");
        $vowels = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
        $this->valor1=str_replace($vowels,"", mb_strtoupper(trim($valor1)));
        $this->valor2=str_replace($vowels,"", mb_strtoupper(trim($valor2)));
        $this->valor3=str_replace($vowels,"", mb_strtoupper(trim($valor3)));
    }
    function addservicioequipo(){
      $sql= 'SELECT MAX(si_id) FROM serviciosinfo';
      $result = setq($sql);
    list($this->id1) = mysql_fetch_array($result);
    $this->id1++;
      $sql='
         INSERT INTO  serviciosinfo SET
         si_id="'.$this->id1.'",
         si_valor1="'.$this->valor1.'",
         si_valor2="'.$this->valor2.'",
         si_valor3="'.$this->valor3.'",
         si_idnombre="'.$_GET['idnmb'].'"
      ';
      setq($sql) or die($sql);
    }
    function updateservicioequipo(){
      $sql='
         UPDATE serviciosinfo SET
         si_valor1="'.$this->valor1.'",
         si_valor2="'.$this->valor2.'",
         si_valor3="'.$this->valor3.'"
         where si_id="'.$_GET['id'].'"';
       setq($sql) or die($sql);
    }
    function addnmbequipo(){
      $sql= 'SELECT MAX(ss_id) FROM surcusal_servicioinfo';
      $result = setq($sql);
    list($this->id) = mysql_fetch_array($result);
    $this->id++;
      $sql='
         INSERT INTO  surcusal_servicioinfo SET
         ss_id="'.$this->id.'",
         ss_nmb="'.$this->nmbequipo.'",
         ss_idcliente="'.$_GET['cliente'].'",
         ss_idsucursal="'.$_GET['suc'].'" ';
      setq($sql) or die($sql);
    }
    function editnmbequipo(){
      $sql='
         UPDATE  surcusal_servicioinfo SET
         ss_nmb="'.$this->nmbequipo.'"
         WHERE ss_id='.$_GET['id'].' ';
      setq($sql) or die($sql);
    }

  function insertext(){
    $sql='
      INSERT INTO sucextra SET
      e_cliente = "'.$this->cliente.'",
      e_sucursal = "'.$this->sucursal.'",
      e_nmb = "'.$this->nombre.'",
      e_informacion = "'.$this->informacion.'"';
    setq($sql) or die(mysql_error().$sql);
  }

  function selectsucursal($id){
    $sql='SELECT * FROM sucursales WHERE s_id="'.$id.'"';
    $result = setq($sql) or die($sql);
    $row=mysql_fetch_array($result);
    $this->id=$row['s_id'];
    $this->estatus=$row['s_estatus'];
        $this->nombre=$row['s_nmb'];
    $this->calle=$row['s_calle'];
    $this->exterior=$row['s_exterior'];
    $this->interior=$row['s_interior'];
    $this->colonia=$row['s_colonia'];
    $this->codigoP=$row['s_cp'];
    $this->municipio=$row['s_municipio'];
    $this->estado=$row['s_estado'];
    $this->pais=$row['s_pais'];
    $this->telefono=$row['s_telefono'];
    $this->celular=$row['s_celular'];
    $this->clienteid=$row['s_clienteid'];
        $this->emails=$row['s_email'];
  }
  function resultSucursal($idc){
    $sql='SELECT * FROM sucursales WHERE s_clienteid="'.$idc.'"';
    $this->resultSucursal=setq($sql) or die($sql);
  }
  function setDataSucursal($id,$estatus,$nombre,$calle,$exterior,$interior,$colonia,$codigoP,$municipio,$estado,$pais,$emails){
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
      $this->id=str_replace($vowels,"", mb_strtoupper(trim($id)));
      $this->estatus=str_replace($vowels,"", mb_strtoupper(trim($estatus)));
      $this->nombre=str_replace($vowels,"", mb_strtoupper(trim($nombre)));
      $this->calle=str_replace($vowels,"", mb_strtoupper(trim($calle)));
      $this->exterior=str_replace($vowels,"", mb_strtoupper(trim($interior)));
      $this->interior=str_replace($vowels,"", mb_strtoupper(trim($valor1)));
      $this->colonia=str_replace($vowels,"", mb_strtoupper(trim($colonia)));
      $this->codigoP=str_replace($vowels,"", mb_strtoupper(trim($codigoP)));
      $this->municipio=str_replace($vowels,"", mb_strtoupper(trim($municipio)));
      $this->estado=str_replace($vowels,"", mb_strtoupper(trim($estado)));
      $this->pais=str_replace($vowels,"", mb_strtoupper(trim($pais)));
      $this->clienteid=str_replace($vowels,"", mb_strtoupper(trim($clienteid)));
      $this->emails=str_replace($vowels,"", mb_strtoupper(trim($emails)));
  }
  function insertSuc(){
    $sql='SELECT MAX(s_id) FROM sucursales';
    $result=setq($sql);
    list($this->id)=mysql_fetch_array($result);
    $this->id++;
    $sql='
      INSERT INTO sucursales SET
      s_id="'.$this->id.'",
      s_estatus="'.$this->estatus.'",
            s_nmb="'.$this->nombre.'",
      s_calle="'.$this->calle.'",
      s_exterior="'.$this->exterior.'",
      s_interior="'.$this->interior.'",
      s_colonia="'.$this->colonia.'",
      s_cp="'.$this->codigoP.'",
      s_municipio="'.$this->municipio.'",
      s_estado="'.$this->estado.'",
      s_pais="'.$this->pais.'",
            s_email="'.$_POST['emails'].'",
            s_telefono="'.$_POST['telefono'].'",
            s_celular="'.$_POST['celular'].'",
      s_clienteid="'.$_GET['cliente'].'"		';
    setq($sql) or die($sql);
  }
  function updateSuc(){
    $sql='
      UPDATE sucursales SET
      s_id="'.$this->id.'",
            s_nmb="'.$this->nombre.'",
      s_calle="'.$this->calle.'",
      s_exterior="'.$this->exterior.'",
      s_interior="'.$this->interior.'",
      s_colonia="'.$this->colonia.'",
      s_cp="'.$this->codigoP.'",
      s_municipio="'.$this->municipio.'",
      s_estado="'.$this->estado.'",
      s_pais="'.$this->pais.'",
      s_clienteid="'.$_GET['cliente'].'",
            s_telefono="'.$_POST['telefono'].'",
            s_celular="'.$_POST['celular'].'",
            s_email="'.$_POST['emails'].'"
      WHERE s_id="'.$this->id.'"		';
    setq($sql) or die($sql);
  }
    function eliminardetsuc($id){
      $sql='DELETE FROM sucextra where e_id='.$id.'';
      setq($sql) or die($sql);
    }
    function eliminarnmbequipo($id){
      $sql='SELECT si_id FROM serviciosinfo WHERE si_idnombre="'.$id.'" LIMIT 1';
      $res=setq($sql) or die($sql);
      $row=mysql_fetch_array($res);
      $this->error='&error=1';
      if(!isset($row['si_id'])){
        $sql='DELETE FROM surcusal_servicioinfo where ss_id='.$id.' ';
        setq($sql) or die($sql);
        $sql='DELETE FROM serviciosinfo where si_idnombre='.$id.' ';
        setq($sql) or die($sql);
        $this->error=' ';
      }
    }
    function eliminardetequipo($id){
      $sql='DELETE FROM serviciosinfo where si_id='.$id.'    ';
      setq($sql) or die($sql);
    }
    function updateext(){
    $sql='
      UPDATE sucextra SET
      e_nmb = "'.$this->nombre.'",
      e_informacion = "'.$this->informacion.'"
            WHERE e_id="'.$_GET['id'].'" ';
     setq($sql);
  }
    function filtrar($nmb){
      $sql = 'SELECT * FROM clientes WHERE c_nmb LIKE "%'.$nmb.'%" or c_alias LIKE "%'.$nmb.'%" order by c_alias';
      $this->filtrar = setq($sql) or die($sql);
    }
    function resulttotal($id){
    $sql='SELECT t_id FROM tickets WHERE t_cliente="'.$id.'"';
     $this->resulttotal=setq($sql) or die($sql);
  }
   function updatemismo($id,$valor){
     $sql='UPDATE clientes SET
        c_chkigual="'.$valor.'"
        WHERE c_id="'.$id.'"';
     setq($sql) or die($sql);
   }
function selectbonif($cliente){
  $sqlb = 'SELECT * FROM bonificaciones WHERE b_cliente = "'.$cliente.'" ORDER BY b_id DESC,b_fecha DESC';
  $this->resultb = setq($sqlb) or die($sqlb);
}

function resultpedidos($page,$pageresult,$cliente){
  $sql = 'SELECT * FROM pedidoscl WHERE p_cliente = "'.$cliente.'" ORDER BY p_id DESC LIMIT '.($page*$pageresult).','.$pageresult.' ';
  $this->resultp = setq($sql) or die($sql);
}

function insertbonif($cliente,$monto,$motivo){
  $sql = 'SELECT MAX(b_id) FROM bonificaciones';
  $result = setq($sql) or die($sql);
  list($idb) = $result->fetch_array();
  $idb++;
  mb_internal_encoding("UTF-8");
  $vowels = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|");

  $sqlin = 'INSERT INTO bonificaciones SET
  b_id = "'.$idb.'",
  b_cliente = "'.$cliente.'",
  b_monto = "'.$monto.'",
  b_estatus = "N",
  b_motivo = "'.str_replace($vowels,"", mb_strtoupper(trim($motivo))).'",
  b_fecha = "'.date('Y-m-d').'",
  b_hora = "'.date('H:i:s').'"
  ';
  setq($sqlin) or die($sqlin);

}


function sync($host,$user,$db,$pass){
  $mysqli = mysqli_init();
  $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
  $mysqli->real_connect($host,$user,$pass,$db);
  if ($mysqli->ping()){
    $conexionec = mysql_connect($host,$user,$pass);
    mysql_select_db($db,$conexionec);
    setq("SET NAMES 'utf8'",$conexionec);

    $conexionloc = mysql_connect('localhost','root','');
    mysql_select_db('ceoaguas',$conexionloc);
    setq("SET NAMES 'utf8'",$conexionloc);

    $sql = 'SELECT * FROM clientes WHERE c_updated = "0"';
    $result = setq($sql,$conexionec) or die($sql);
    $simbol = array('"',"'");
    while($row = $result->fetch_array()){
      $sqlup = 'INSERT INTO clientes SET
      c_id = "'.$row['c_id'].'",
      c_nmb = "'.str_replace($simbol,"",$row['c_nmb']).'",
      c_apellidos = "'.str_replace($simbol,"",$row['c_apellidos']).'",
      c_sexo = "'.$row['c_sexo'].'",
      c_fnacimiento = "'.$row['c_fnacimiento'].'",
      c_mail = "'.$row['c_mail'].'",
      c_telefono = "'.$row['c_telefono'].'",
      c_celular = "'.$row['c_celular'].'",
      c_password = "'.$row['c_password'].'",
      c_estatus = "'.$row['c_estatus'].'",
      c_rfc = "'.$row['c_rfc'].'",
      c_razon = "'.str_replace($simbol,"",$row['c_razon']).'",
      c_calle = "'.str_replace($simbol,"",$row['c_calle']).'",
      c_nume = "'.str_replace($simbol,"",$row['c_nume']).'",
      c_numi = "'.str_replace($simbol,"",$row['c_numi']).'",
      c_colonia = "'.str_replace($simbol,"",$row['c_colonia']).'",
      c_cp = "'.$row['c_cp'].'",
      c_municipio = "'.str_replace($simbol,"",$row['c_municipio']).'",
      c_estado = "'.$row['c_estado'].'",
      c_pais = "'.$row['c_pais'].'",
      c_chkigual = "'.$row['c_chkigual'].'",
      c_ocupacion = "'.$row['c_ocupacion'].'",
      c_esquema = "'.$row['c_esquema'].'",
      c_ipcliente = "'.$row['c_ipcliente'].'",
      c_ualta = "'.$row['c_ualta'].'",
      c_falta = "'.$row['c_falta'].'",
      c_umod = "'.$row['c_umod'].'",
      c_fmod = "'.$row['c_fmod'].'",
      c_hcomentario = "'.$row['c_hcomentario'].'",
      c_origen = "'.$row['c_origen'].'",
      c_bonificacion = "'.$row['c_bonificacion'].'",
      c_promociones = "'.$row['c_promociones'].'",
      c_updated = "1"
      ON DUPLICATE KEY UPDATE
      c_nmb = "'.str_replace($simbol,"",$row['c_nmb']).'",
      c_apellidos = "'.str_replace($simbol,"",$row['c_apellidos']).'",
      c_sexo = "'.$row['c_sexo'].'",
      c_fnacimiento = "'.$row['c_fnacimiento'].'",
      c_mail = "'.$row['c_mail'].'",
      c_telefono = "'.$row['c_telefono'].'",
      c_celular = "'.$row['c_celular'].'",
      c_password = "'.$row['c_password'].'",
      c_estatus = "'.$row['c_estatus'].'",
      c_rfc = "'.$row['c_rfc'].'",
      c_razon = "'.str_replace($simbol,"",$row['c_razon']).'",
      c_calle = "'.str_replace($simbol,"",$row['c_calle']).'",
      c_nume = "'.str_replace($simbol,"",$row['c_nume']).'",
      c_numi = "'.str_replace($simbol,"",$row['c_numi']).'",
      c_colonia = "'.str_replace($simbol,"",$row['c_colonia']).'",
      c_cp = "'.$row['c_cp'].'",
      c_municipio = "'.str_replace($simbol,"",$row['c_municipio']).'",
      c_estado = "'.$row['c_estado'].'",
      c_pais = "'.$row['c_pais'].'",
      c_chkigual = "'.$row['c_chkigual'].'",
      c_ocupacion = "'.$row['c_ocupacion'].'",
      c_esquema = "'.$row['c_esquema'].'",
      c_ipcliente = "'.$row['c_ipcliente'].'",
      c_ualta = "'.$row['c_ualta'].'",
      c_falta = "'.$row['c_falta'].'",
      c_umod = "'.$row['c_umod'].'",
      c_fmod = "'.$row['c_fmod'].'",
      c_hcomentario = "'.$row['c_hcomentario'].'",
      c_origen = "'.$row['c_origen'].'",
      c_bonificacion = "'.$row['c_bonificacion'].'",
      c_promociones = "'.$row['c_promociones'].'",
      c_updated = "1"';
      setq($sqlup,$conexionloc) or die($sqlup);
    }

    $sqlup = 'UPDATE clientes SET c_updated = "1" WHERE c_updated = "0"';
    setq($sqlup,$conexionec) or die($sqlup);

    $result = setq($sql,$conexionloc) or die($sql);
    while($row = mysql_fetch_array($result)){
      $sqlup = 'INSERT INTO clientes SET
      c_id = "'.$row['c_id'].'",
      c_nmb = "'.str_replace($simbol,"",$row['c_nmb']).'",
      c_apellidos = "'.str_replace($simbol,"",$row['c_apellidos']).'",
      c_sexo = "'.$row['c_sexo'].'",
      c_fnacimiento = "'.$row['c_fnacimiento'].'",
      c_mail = "'.$row['c_mail'].'",
      c_telefono = "'.$row['c_telefono'].'",
      c_celular = "'.$row['c_celular'].'",
      c_password = "'.$row['c_password'].'",
      c_estatus = "'.$row['c_estatus'].'",
      c_rfc = "'.$row['c_rfc'].'",
      c_razon = "'.str_replace($simbol,"",$row['c_razon']).'",
      c_calle = "'.str_replace($simbol,"",$row['c_calle']).'",
      c_nume = "'.str_replace($simbol,"",$row['c_nume']).'",
      c_numi = "'.str_replace($simbol,"",$row['c_numi']).'",
      c_colonia = "'.str_replace($simbol,"",$row['c_colonia']).'",
      c_cp = "'.$row['c_cp'].'",
      c_municipio = "'.str_replace($simbol,"",$row['c_municipio']).'",
      c_estado = "'.$row['c_estado'].'",
      c_pais = "'.$row['c_pais'].'",
      c_chkigual = "'.$row['c_chkigual'].'",
      c_ocupacion = "'.$row['c_ocupacion'].'",
      c_esquema = "'.$row['c_esquema'].'",
      c_ipcliente = "'.$row['c_ipcliente'].'",
      c_ualta = "'.$row['c_ualta'].'",
      c_falta = "'.$row['c_falta'].'",
      c_umod = "'.$row['c_umod'].'",
      c_fmod = "'.$row['c_fmod'].'",
      c_hcomentario = "'.$row['c_hcomentario'].'",
      c_origen = "'.$row['c_origen'].'",
      c_bonificacion = "'.$row['c_bonificacion'].'",
      c_promociones = "'.$row['c_promociones'].'",
      c_updated = "1"
      ON DUPLICATE KEY UPDATE
      c_nmb = "'.str_replace($simbol,"",$row['c_nmb']).'",
      c_apellidos = "'.str_replace($simbol,"",$row['c_apellidos']).'",
      c_sexo = "'.$row['c_sexo'].'",
      c_fnacimiento = "'.$row['c_fnacimiento'].'",
      c_mail = "'.$row['c_mail'].'",
      c_telefono = "'.$row['c_telefono'].'",
      c_celular = "'.$row['c_celular'].'",
      c_password = "'.$row['c_password'].'",
      c_estatus = "'.$row['c_estatus'].'",
      c_rfc = "'.$row['c_rfc'].'",
      c_razon = "'.str_replace($simbol,"",$row['c_razon']).'",
      c_calle = "'.str_replace($simbol,"",$row['c_calle']).'",
      c_nume = "'.str_replace($simbol,"",$row['c_nume']).'",
      c_numi = "'.str_replace($simbol,"",$row['c_numi']).'",
      c_colonia = "'.str_replace($simbol,"",$row['c_colonia']).'",
      c_cp = "'.$row['c_cp'].'",
      c_municipio = "'.str_replace($simbol,"",$row['c_municipio']).'",
      c_estado = "'.$row['c_estado'].'",
      c_pais = "'.$row['c_pais'].'",
      c_chkigual = "'.$row['c_chkigual'].'",
      c_ocupacion = "'.$row['c_ocupacion'].'",
      c_esquema = "'.$row['c_esquema'].'",
      c_ipcliente = "'.$row['c_ipcliente'].'",
      c_ualta = "'.$row['c_ualta'].'",
      c_falta = "'.$row['c_falta'].'",
      c_umod = "'.$row['c_umod'].'",
      c_fmod = "'.$row['c_fmod'].'",
      c_hcomentario = "'.$row['c_hcomentario'].'",
      c_origen = "'.$row['c_origen'].'",
      c_bonificacion = "'.$row['c_bonificacion'].'",
      c_promociones = "'.$row['c_promociones'].'",
      c_updated = "1"';
      setq($sqlup,$conexionec) or die($sqlup);
    }

    $sqlup = 'UPDATE clientes SET c_updated = "1" WHERE c_updated = "0"';
    setq($sqlup,$conexionloc) or die($sqlup);

    $sql = 'SELECT * FROM direnvio WHERE d_updated = "0"';
    $result = setq($sql,$conexionec) or die($sql);
    while($row = mysql_fetch_array($result)){
      $sqlup = 'INSERT INTO direnvio SET
      d_id = "'.$row['d_id'].'",
      d_cliente = "'.$row['d_cliente'].'",
      d_calle = "'.$row['d_calle'].'",
      d_nume = "'.$row['d_nume'].'",
      d_numi = "'.$row['d_numi'].'",
      d_colonia = "'.$row['d_colonia'].'",
      d_cp = "'.$row['d_cp'].'",
      d_municipio = "'.$row['d_municipio'].'",
      d_estado = "'.$row['d_estado'].'",
      d_pais = "'.$row['d_pais'].'",
      d_telefono = "'.$row['d_telefono'].'",
      d_referencia = "'.$row['d_referencia'].'",
      d_predeterminado = "'.$row['d_predeterminado'].'",
      d_updated = "1"
      ON DUPLICATE KEY UPDATE
      d_cliente = "'.$row['d_cliente'].'",
      d_calle = "'.$row['d_calle'].'",
      d_nume = "'.$row['d_nume'].'",
      d_numi = "'.$row['d_numi'].'",
      d_colonia = "'.$row['d_colonia'].'",
      d_cp = "'.$row['d_cp'].'",
      d_municipio = "'.$row['d_municipio'].'",
      d_estado = "'.$row['d_estado'].'",
      d_pais = "'.$row['d_pais'].'",
      d_telefono = "'.$row['d_telefono'].'",
      d_referencia = "'.$row['d_referencia'].'",
      d_predeterminado = "'.$row['d_predeterminado'].'",
      d_updated = "1"';
      setq($sqlup,$conexionloc) or die($sqlup);
    }

    $sqlup = 'UPDATE direnvio SET d_updated = "1" WHERE d_updated = "0"';
    setq($sqlup,$conexionec) or die($sqlup);

    $result = setq($sql,$conexionloc) or die($sql);
    while($row = mysql_fetch_array($result)){
      $sqlup = 'INSERT INTO direnvio SET
      d_id = "'.$row['d_id'].'",
      d_cliente = "'.$row['d_cliente'].'",
      d_calle = "'.$row['d_calle'].'",
      d_nume = "'.$row['d_nume'].'",
      d_numi = "'.$row['d_numi'].'",
      d_colonia = "'.$row['d_colonia'].'",
      d_cp = "'.$row['d_cp'].'",
      d_municipio = "'.$row['d_municipio'].'",
      d_estado = "'.$row['d_estado'].'",
      d_pais = "'.$row['d_pais'].'",
      d_telefono = "'.$row['d_telefono'].'",
      d_referencia = "'.$row['d_referencia'].'",
      d_predeterminado = "'.$row['d_predeterminado'].'",
      d_updated = "1"
      ON DUPLICATE KEY UPDATE
      d_cliente = "'.$row['d_cliente'].'",
      d_calle = "'.$row['d_calle'].'",
      d_nume = "'.$row['d_nume'].'",
      d_numi = "'.$row['d_numi'].'",
      d_colonia = "'.$row['d_colonia'].'",
      d_cp = "'.$row['d_cp'].'",
      d_municipio = "'.$row['d_municipio'].'",
      d_estado = "'.$row['d_estado'].'",
      d_pais = "'.$row['d_pais'].'",
      d_telefono = "'.$row['d_telefono'].'",
      d_referencia = "'.$row['d_referencia'].'",
      d_predeterminado = "'.$row['d_predeterminado'].'",
      d_updated = "1"';
      setq($sqlup,$conexionec) or die($sqlup);
    }

    $sqlup = 'UPDATE direnvio SET d_updated = "1" WHERE d_updated = "0"';
    setq($sqlup,$conexionloc) or die($sqlup);
  }
  else{
    die("Error de Conexion con E-Commerce");
  }
  mysql_close($conexionec);
  mysql_close($conexionloc);
  $mysqli->close();
  include("db.php");
}

function syncbonif($host,$user,$db,$pass,$cliente){
  $mysqli = mysqli_init();
  $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
  $mysqli->real_connect($host,$user,$pass,$db);
  if ($mysqli->ping()){
    $conexionec = mysql_connect($host,$user,$pass);
    mysql_select_db($db,$conexionec);
    setq("SET NAMES 'utf8'",$conexionec);

    $conexionloc = mysql_connect('localhost','root','');
    mysql_select_db('ceoaguas',$conexionloc);
    setq("SET NAMES 'utf8'",$conexionloc);

    $sql = 'SELECT * FROM bonificaciones WHERE b_updated = "0" AND b_cliente = "'.$cliente.'"';
    $result = setq($sql,$conexionec) or die($sql);
    while($row = mysql_fetch_array($result)){
      $sqlup = 'INSERT INTO bonificaciones SET
      b_id = "'.$row['b_id'].'",
      b_cliente = "'.$row['b_cliente'].'",
      b_monto = "'.$row['b_monto'].'",
      b_estatus = "'.$row['b_estatus'].'",
      b_motivo = "'.$row['b_motivo'].'",
      b_fecha = "'.$row['b_fecha'].'",
      b_hora = "'.$row['b_hora'].'",
      b_pedido = "'.$row['b_pedido'].'",
      b_updated = "1"
      ON DUPLICATE KEY UPDATE
      b_cliente = "'.$row['b_cliente'].'",
      b_monto = "'.$row['b_monto'].'",
      b_estatus = "'.$row['b_estatus'].'",
      b_motivo = "'.$row['b_motivo'].'",
      b_fecha = "'.$row['b_fecha'].'",
      b_hora = "'.$row['b_hora'].'",
      b_pedido = "'.$row['b_pedido'].'",
      b_updated = "1"';
      setq($sqlup,$conexionloc) or die($sqlup);
    }

    $sqlup = 'UPDATE bonificaciones SET b_updated = "1" WHERE b_updated = "0" AND b_cliente = "'.$cliente.'"';
    setq($sqlup,$conexionec) or die($sqlup);

    $result = setq($sql,$conexionloc) or die($sql);
    while($row = mysql_fetch_array($result)){
      $sqlup = 'INSERT INTO bonificaciones SET
      b_id = "'.$row['b_id'].'",
      b_cliente = "'.$row['b_cliente'].'",
      b_monto = "'.$row['b_monto'].'",
      b_estatus = "'.$row['b_estatus'].'",
      b_motivo = "'.$row['b_motivo'].'",
      b_fecha = "'.$row['b_fecha'].'",
      b_hora = "'.$row['b_hora'].'",
      b_pedido = "'.$row['b_pedido'].'",
      b_updated = "1"
      ON DUPLICATE KEY UPDATE
      b_cliente = "'.$row['b_cliente'].'",
      b_monto = "'.$row['b_monto'].'",
      b_estatus = "'.$row['b_estatus'].'",
      b_motivo = "'.$row['b_motivo'].'",
      b_fecha = "'.$row['b_fecha'].'",
      b_hora = "'.$row['b_hora'].'",
      b_pedido = "'.$row['b_pedido'].'",
      b_updated = "1"';
      setq($sqlup,$conexionec) or die($sqlup);
    }

    $sqlup = 'UPDATE bonificaciones SET b_updated = "1" WHERE b_updated = "0" AND b_cliente = "'.$cliente.'"';
    setq($sqlup,$conexionloc) or die($sqlup);
  }
  else{
    die("Error de Conexion con E-Commerce");
  }
  mysql_close($conexionec);
  mysql_close($conexionloc);
  $mysqli->close();
  include("db.php");
}

function resultacademy(){
  $sql = 'SELECT * FROM usuarios ORDER BY u_id DESC';
  $this->result = setqacademy($sql);
}
function selectacademy($id){
  $sql ='SELECT * FROM usuarios WHERE u_id = "'.$id.'"';
  $result = setqacademy($sql);
  $row = $result->fetch_array();
  $this->id = $row['u_id'];
  $this->nmb = $row['u_nmb'];
  $this->apellidos = $row['u_apellidos'];
  $this->correo = $row['u_correo'];
  $this->telefono = $row['u_telefono'];
  $this->estatus = $row['u_estatus'];
  $this->grupo = $row['u_grupo'];
  //$this->id = $row['u_id'];

}

function setdataacademy($id,$nmb,$apellidos,$correo,$estatus,$grupo,$telefono){
  $this->id = $id;
  $this->nmb = clearvmayus($nmb);
  $this->apellidos = clearvmayus($apellidos);
  $this->correo = $correo;
  $this->estatus = clearvmayus($estatus);
  $this->grupo = clearvmayus($grupo);
  $this->telefono = $telefono;
  
}
function updateacademy(){
  $sql = 'UPDATE usuarios SET
          u_nmb = "'.$this->nmb.'",
          u_apellidos = "'.$this->apellidos.'",
          u_correo = "'.$this->correo.'",
          u_estatus = "'.$this->estatus.'",
          u_grupo = "'.$this->grupo.'",
          u_telefono = "'.$this->telefono.'"
          WHERE u_id = "'.$this->id.'"';
  setqacademy($sql);
}
function insertacademy(){
  $sql = 'INSERT INTO usuarios SET
          u_nmb = "'.$this->nmb.'",
          u_apellidos = "'.$this->apellidos.'",
          u_correo = "'.$this->correo.'",
          u_estatus = "'.$this->estatus.'",
          u_grupo = "'.$this->grupo.'",
          u_telefono = "'.$this->telefono.'"';
  setqacademy($sql);
}


}

class viewclientesw{
  var $model;

function __construct($model){
  $this->model=$model;
  $this->estp = array("N"=>"En Carrito de Compra","P"=>"Preordenado","A"=>"Proceso de pago","R"=>"Pago Reportado","F"=>"Pago Autorizado","S"=>"Proceso de Surtido","E"=>"Empacado","X"=>"Finalizado","C"=>"Cancelado");
  $this->weborigen = array("1"=>"SHOP","2"=>"SUITE");
}

function browse($page,$pageresult,$cliente,$estatus,$email,$carrito,$apellidos,$webo){
  if(!isset($_GET['error']))
    $_GET['error']=NULL;
  if($_GET['error']=='1'){
    echo "<script languaje='javascript'>alert('Publico en General no se puede desactivar')</script>";
  }

  $sql='SELECT COUNT(*) as maximo FROM clientes WHERE c_estatus = "'.$estatus.'"';
  $result = setq($sql) or die($sql);
  list($numre) = $result->fetch_array();
  $np = $numre / $pageresult;

  $dcart = '';
  if($carrito == 1)
    $dcart = '&carrito=1';
  elseif($carrito == 2)
    $dcart = '&carrito=2';

  /* if('SI' == 1) echo 'SI ES';
  elseif('SI' == 0) echo  'NO ES';
  else echo 'NADA'; */



  echo '<div class="page-title-actions">
  <a href="?modulo=clientesw&accion=edit">
    <button type="button" class="btn btn-primary mb-1"><i class="fa fa-plus"></i> Nueva </button>
  </a>
  <a href="?modulo=clientesw&accion=jdshopacademy" class="mb-1 btn btn-light-green"><i class="icon-video-camera2"></i> JDSHOP Academy</a>


  <button accesskey="L" id="filtrar" type="button" class="btn btn-info mb-1"  data-toggle="collapse" data-target="#filter-panel">
    <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
  </button>';

  if($estatus == "A")
    echo '<a href="?modulo=clientesw&accion=index&estatus=I">
            <button class="btn btn-danger mb-1"><i class="icon-times-circle-o"></i> Inactivos</button>
          </a>';
  else
    echo '<a href="?modulo=clientesw&accion=index&estatus=A">
            <button class="btn btn-success mb-1"><i class="fa fa-check-circle-o"></i> Activos</button>
          </a>';
  echo '</div></div>';
    $onkeyup='onkeyup ="var key = window.event.keyCode;if(key==13){ var time=setTimeout(submit(), 1000);   }"';

  echo '
  <div id="filter-panel" class="collapse filter-panel col-md-12 mb-1">
  <form autocomplete="off" class="form-inline" method=post  onsubmit="return checkSubmitenviar();">
  <div class="mb-5">
  <label for="tags">Cliente:</label>
  <input id="tags" type="text" value="'.$cliente.'" name="cliente" class="form-control"  '.$onkeyup.'  autofocus placeholder="Nombre Cliente">
  </div>
  <div class="mb-5">
  <label for="tagsap">Apellidos:</label>
  <input id="tagsap" type="text" value="'.$apellidos.'" class="form-control" name="apellidos"  '.$onkeyup.' placeholder="Apellidos Cliente">
  </div>
  <div class="mb-5">
  <label for="tagse">Correo electrónico:</label>
  <input id="tagse" type="text" value="'.$email.'" class="form-control" name="email"  '.$onkeyup.' placeholder="Correo Cliente">
  </div>

  </form>
  </div>
  <div class="table-responsive">
  <table  class="mb-0 table table-hover table-striped">
  <thead class="bg-light-blue bg-darken-2 text-white">
  <tr>
  <th width="5%"><center><b>Id</b></center></th>
  <th width="25%"><center><b>Nombre</b></center></th>
  <th width="13%"><center><b>Esquema</b></center></th>
  <th width="25%"><center><b>Fecha de Registro</b></center></th>
  <th width="13%"><center><b>Teléfono</b></center></th>
  <th width="13%"><center><b>Celular</b></center></th>
  <th width="10%"><center><b>Correo Electrónico</b></center></th>
  <th width="10%"><center>&nbsp;</center></th>
  <th width="10%"><center>&nbsp;</center></th>
  </tr></thead>';

  $asexo = array("M"=>"Masculino", "F"=>"Femenino","" => "");
  while($row = $this->model->result->fetch_array()){
    echo '<tr>
    <th><center><b>'.$row['c_id'].'</b></center></th>
    <td><center>'.$row['c_nmb'].' '.$row['c_apellidos'].'</center></td>
      <td><center>'.busca($row['c_esquema'],'esquemas','e_id','e_nmb').'</center></td>
    <td><center>'.fecha_formato($row['c_falta'],true,true).'</center></td>
    <td><center>'.$row['c_telefono'].'</center></td>
    <td><center>'.$row['c_celular'].'</center></td>
      <td><center>'.$row['c_mail'].'</center></td>
      <td><center>'.$this->weborigen[$row['c_weborigen']].'</center></td>
      ';
    echo '<td>
            <a href="?modulo=clientesw&accion=edit&id='.$row['c_id'].'" onclick="this.onclick=function(){return false;}"> <!-- href="?modulo=clientesw&accion=show&id='.$row['c_id'].'" onclick="this.onclick=function(){return false;}" -->
              <button class="btn btn-info"><i class="icon-smile-o"></i></button>
            </a>
        </td>';
    echo '</tr>';
  }
  echo '</table></div>';
  if($_REQUEST['cliente'] || $_REQUEST['apellidos'] || $_REQUEST['email']){

  }else{

  echo '
  <div class="col-md-12 text-xs-center">
  <div class="mb-3">
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <li class="page-item">
          <a class="page-link" href="?modulo=clientesw&accion=index&page='.($_GET['page']-1).'" aria-label="Previous">
            <span aria-hidden="true">&laquo; Ant</span>
            <span class="sr-only">Anterior</span>
          </a>
        </li>';
        
  $numres = 50;
  $nr = $this->model->resultt->num_rows;
  $np = $nr/$numres;
  $paginaa = $page-1;
  $pagina = $page+1;
  $sqlpg = 'SELECT COUNT(*) FROM clientes WHERE c_estatus = "'.$estatus.'"';
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
    echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=clientesw&accion=index&page=0">'.$nombre.'</a></li>';
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
    echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=clientesw&accion=index&page='.$i.'">'.$nombre.'</a></li>';
  }
  
  if($pageres<9){

  }else{
  if($_GET['page'] == ($pageres-5)) $nombre = ($pageres-2);
  else $nombre = '... '.($pageres-2);
  if($_GET['page'] == $i) $active = "active";
  else $active = "";
  if($_GET['page'] >= ($pageres-4)) echo '';
  else echo '<li class="page-item '.$active.'"><a class="page-link" href="?modulo=clientesw&accion=index&page='.($pageres-2).'">'.$nombre.'</a></li>';
    echo '
    <li class="page-item '.$active.'"><a class="page-link" href="?modulo=clientesw&accion=index&page='.($pageres-1).'">'.($pageres-1).'</a></li>';

  }
    echo '<li class="page-item">
                    <a class="page-link" href="?modulo=clientesw&accion=index&page='.($_GET['page']+1).'" aria-label="Next">
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

function edit(){
  $url='index';
    //if($this->model->id)
    //$url='show&id='.$this->model->id;

  echo '<div class="mb-1" id="">
  <a href="?modulo=clientesw&accion='.$url.'" type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras</a>
  </div>';
  echo '<span id="long"></span>';
  echo '<span id="latitud"></span>';
?>
<script type="text/javascript">
  var x,
  x = $(document);
  x.ready(inicio);
  function inicio(){
    //navigator.geolocation.getCurrentPosition(viewMap,ViewError,{timeout:1000});
      $("#nombre").focusout(validanombre);
      $("#alias").focusout(validaalias);
      $("#rfc").focusout(validarfc);
      $("#mail").focusout(validamail);
      //$("#calle").focusout(validacalle);
      $("#apellidos").focusout(validaapellidos);
      //$(window).bind('beforeunload', nosalir);
      $('#fcliente').submit(salir);
      $("#generar").click(generar);
  }
  function viewMap (position) {
    var lon = position.coords.longitude;	//guardamos la longitud
    var lat = position.coords.latitude;		//guardamos la latitud

    //var link = "http://maps.google.com/?ll="+lat+","+lon+"&z=14";
    //	document.getElementById("long").innerHTML = lon;
    //document.getElementById("latitud").innerHTML = lat;
    //	document.getElementById("link").href = link;
  }

  function ViewError (error) {
    //alert(error.code);
  }

  function nosalir(){
      return 'Guarde los datos antes de continuar, de lo contrario perderán los cambios';
  }
  function salir(){
      $(window).unbind('beforeunload');
      $("#progressbar").show().css({ opacity: 0.5 });
      setTimeout(2000);
  }
  function validaapellidos(){
    if (!nombre_valido($(this).val())) $(".error.apellidos").show();
    else $(".error.apellidos").hide();
  }
  function validacalle(){
    if (!alfanumerico_valido($(this).val())) $(".error.calle").show();
    else $(".error.calle").hide();
  }
  function validarfc(){
    if (!rfc_valido($(this).val())) $(".error.rfc").show();
    else $(".error.rfc").hide();
  }
  function validanombre(){
    if (!nombre_valido($(this).val())) $(".error.nombre").show();
    else $(".error.nombre").hide();
  }
  function validaalias(){
    if (!nombre_valido($(this).val())) $(".error.alias").show();
    else $(".error.alias").hide();
  }
  function validamail(){
    correo = $('#mail').val();
    $.post("jquery/checkmailw",{
      correo: correo,
    },function(resp){
      if (resp > 0){
        $(".error.mail").show();
        $('#mail').val('');
      }
      else{
        $(".error.mail").hide();
      }
    });
  }
  function nombre_valido(valor) {
      var reg = /^([a-z ñáéíóú]{2,150})$/i;
      if (reg.test(valor)) return true;
      else return false;
  }
  function rfc_valido(valor) {
      var reg = /^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$/;
      if (reg.test(valor)) return true;
      else return false;
  }
  function alfanumerico_valido(valor) {
      var reg = /^[0-9a-zA-Zñ]+( ?[0-9a-zA-Zñ])+$/;
      if (reg.test(valor)) return true;
      else return false;
  }
  function generar(){
    var emp="P";
    var i;
    var num=0;
    var cliente=$('#cliente').val();
    var idcliente=$('#idcliente').val();
      num1=Math.floor((Math.random() * 10) );
      num2=Math.floor((Math.random() * 10) );
      num3=Math.floor((Math.random() * 10) );
    $("#nmb").val(emp+cliente+""+num1+""+num2+""+num3);
  }

  function muestrat(){
    if(document.getElementById("tcredito").checked){
      document.getElementById("mc").style.display = "";
      document.getElementById("dc").style.display = "";
      document.getElementById("mc2").style.display = "";
      document.getElementById("dc2").style.display = "";
    }
    else{
      document.getElementById("mc").style.display = "none";
      document.getElementById("dc").style.display = "none";
      document.getElementById("mc2").style.display = "none";
      document.getElementById("dc2").style.display = "none";
    }
  }
  function checkSubmitguardar() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
  }

  function enablefac(){
    if(document.getElementById("facturacion").checked){
      document.getElementById("dfacturacion").style.display = "";
    $("#rfc").attr("required", "true");
    $("#razon").attr("required", "true");
    $("#calle").attr("required", "true");
    $("#nume").attr("required", "true");
    $("#colonia").attr("required", "true");
    $("#cp").attr("required", "true");
    $("#municipio").attr("required", "true");
    $("#estado").attr("required", "true");
    $("#pais").attr("required", "true");
    }
    else{
      $("#rfc").removeAttr("required");
      $("#razon").removeAttr("required");
    $("#calle").removeAttr("required");
    $("#nume").removeAttr("required");
    $("#colonia").removeAttr("required");
    $("#cp").removeAttr("required");
    $("#municipio").removeAttr("required");
    $("#estado").removeAttr("required");
    $("#pais").removeAttr("required");
      document.getElementById("dfacturacion").style.display = "none";
    }
  }

  function preguntapassf(){
    var box = document.getElementById('preguntapass');
    if(box.checked){
      var intputcontra = document.getElementById('passcl');
      //$('#passcl').css('display','inline');
      $('#passcl').attr('disabled',false);
    }else{
      //$('#passcl').css('display','none');
      $('#passcl').attr('disabled',true);
    }

  }
</script>
<?php
  $accion = 'insert';
  $titulo='Nuevo Cliente';
  if(isset($this->model->id)){
    $accion='update';
    $titulo='Actualizar Datos del Cliente';
  }
  $chkhcom1=NULL;$chkhcom2=NULL;
  if($this->model->hcomentario=="1") $chkhcom1="checked";
  if($this->model->hcomentario=="2") $chkhcom2="checked";
  $chksexf=NULL; $chksexm=NULL;
  if($this->model->sexo==NULL){
    $chksexf='checked';
  }
  else{
    if($this->model->sexo=='F') $chksexf='checked';
    if($this->model->sexo=='M') $chksexm='checked';
  }

  echo '
  <form autocomplete="off" name="fcliente" action="?modulo=clientesw&accion='.$accion.'" id="fcliente" method="post" onsubmit="return checkSubmitguardar();">
  <div class="row" style="display: flex;">
  <input type="" class="form-control" name="id" value="'.$this->model->id.'" hidden>
  <div class="col-lg-9 disp" id="b" style="display: ;">
  <div class="card">
    <div class="card-header mb-2">
      <h3>'.clearvmayus($titulo).'</h3>
      <input name="opt" id="opt" value="" hidden>
    </div>
      <div class="row nowrap card-block">
        <div class="col-lg-3 mb-5">
          <label>Nombre*</label>
          <input type="text" id="nombre" name="nombre" class="form-control" maxlength="250" value="'.$this->model->nombre.'"  placeholder="Nombre de Cliente" required autofocus>
        </div>
        <div class="col-lg-3 mb-5">
          <label>Apellidos</label>
          <input type="text" id="apellidos" name="apellidos" maxlength="250" class="form-control" placeholder="Apellidos de Cliente" value="'.$this->model->apellidos.'" >
        </div>
        <div class="col-lg-3 mb-5">
          <label>Correo*</label>
          <input type="email" id="mail" name="mail" maxlength="100"  class="form-control" placeholder="Correo de Cliente" value="'.$this->model->mail.'" required>
        </div>
        <div class="col-lg-3 mb-5">
          <label>Telefono</label>
          <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Telefono de Cliente" value="'.$this->model->telefono.'" onchange="quitaespaciost();">
        </div>
        <div class="col-lg-3 mb-5">
          <label>Telefono 2</label>
          <input type="text" id="celular" name="celular" placeholder="Segundo Telefono de Cliente" class="form-control" value="'.$this->model->celular.'" onchange="quitaespaciost2();">
        </div>
        <div class="col-lg-2 mb-5">
          <label>Estatus</label>';
          if($this->model->estatus == "I") $est = '';
          else $est = 'checked';
          echo '
          <input type="checkbox" name="estatus" '.$est.' data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" data-on="ACTIVO" data-off="INACTIVO">
          
        </div>
        <div class="col-lg-3 mb-5">
          <label>Esquema</label>
          <select name="esquema" class="form-control" required>
            <option value="">Seleccionar un esquema</option>   ';
            $sql='SELECT * FROM esquemas WHERE e_estatus="1"';
            $rs=setq($sql) or die($sql);
            while($rwes=$rs->fetch_array()){
              if($this->model->esquema==$rwes['e_id']) $selesq='selected'; else $selesq="";
              echo '<option value="'.$rwes['e_id'].'" '.$selesq.'>'.$rwes['e_nmb'].'</option>';
            }
          echo '
          </select>
        </div>

        <div class="col-lg-4 form-inline mb-5">
          <label>Cambiar Contraseña</label><br>
          <div class="mb-5">
            <input type="checkbox" id="preguntapass" data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" data-on="SI" data-off="NO" onchange="preguntapassf();">
          </div>
          <div class="mb-5">
            <input class="form-control" name="passcl" type="password" id="passcl" style="" placeholder="Nueva Contraseña de Cuenta" disabled>
          </div>
        </div>
        <div class="col-lg-3 mb-5">
          <label>Fecha de N.</label>
          <input type="date" id="fnacimiento" name="fnacimiento" class="form-control" value="'.$this->model->fnacimiento.'"  >
        </div>
        <div class="col-lg-12 text-xs-center">
          <button id="guardar"  type="submit"  value=""  class="btn btn-success text-white"><i class="fa fa-save"></i> Guardar</button>
        </div>
      </div>
  </div>
  </div>
  <script class="">
  function quitaespaciost(){
    original = document.getElementById("telefono").value;
    var x;
    var sCadenaSinBlancos = "";
    for (x=0; x < original.length; x++) {
      if (original.charAt(x) != " ")
        sCadenaSinBlancos+=original.charAt(x);
    }
    document.getElementById("telefono").value = sCadenaSinBlancos;
  }
  function quitaespaciost2(){
    original = document.getElementById("celular").value;
    var x;
    var sCadenaSinBlancos = "";
    for (x=0; x < original.length; x++) {
      if (original.charAt(x) != " ")
        sCadenaSinBlancos+=original.charAt(x);
    }
    document.getElementById("celular").value = sCadenaSinBlancos;
  }
  </script>
  ';

  echo '

  <div class="col-lg-9 disp" id="f" style="display: none;">
  <div class="card">
    <div class="card-header mb-2">
      <h3>DATOS FISCALES DE '.clearvmayus($this->model->nombre).'</h3>
    </div>
    <div class="row nowrap card-block">
      <div class="col-md-4 mb-5">
        <label>RFC*</label>
        <input type="text" name="rfc" id="rfc" class="form-control" maxlength="13" value="'.$this->model->rfc.'" '.$rrfc.' title="INGRESAR UN RFC VALIDO Y SIN ESPACIOS" pattern="^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$" >
      </div>
      <div class="col-md-4 mb-5">
        <label>Razón Social*</label>
        <input type="text" id="razon" name="razon" class="form-control" maxlength="100" value="'.$this->model->razon.'" '.$rrazon.'>
      </div>
      <div class="col-md-4 mb-5">
        <label>Calle*</label>
        <input type="text" name="calle" id="calle" class="form-control" maxlength="250" value="'.$this->model->calle.'" '.$rcalle.'>
      </div>
      <div class="col-md-2 mb-5">
        <label>No. Exterior*</label>
        <input type="text" id="nume" name="nume" class="form-control" maxlength="15" value="'.$this->model->nume.'"  '.$rnume.'>
      </div>
      <div class="col-md-2 mb-5">
        <label>No. Interior</label>
        <input type="text" id="numi" name="numi" class="form-control" maxlength="15" value="'.$this->model->numi.'">
      </div>
      <div class="col-md-3 mb-5">
        <label>Colonia*</label>
        <input type="text" id="colonia" name="colonia" class="form-control" maxlength="100" value="'.$this->model->colonia.'" '.$rcolonia.'>
      </div>
      <div class="col-md-2 mb-5">
        <label>Código Postal*</label>
        <input type="text" id="cp" name="cp" maxlength="5" class="form-control" value="'.$this->model->cp.'" pattern="[0-9]{5}" '.$rcp.'>
      </div>
      <div class="col-md-3 mb-5">
        <label>Municipio*</label>
        <input type="text" id="municipio" name="municipio" class="form-control" maxlength="100" value="'.$this->model->municipio.'" '.$rmuni.'>
      </div>
      <div class="col-md-4 mb-5">
        <label>Estado*</label>';
        $sql='SELECT * FROM estado WHERE e_estatus="A"';
        $rs=setq($sql) or die($sql);

        echo '
        <select name="estado" class="form-control" id="estado" '.$restado.'>
          <option >SELECCIONAR ESTADO</option>';
        while($rw1=$rs->fetch_array()){
          if($this->model->estado==$rw1['e_id']) $sele='selected';else $sele=NULL;
          echo '<option value="'.$rw1['e_id'].'" '.$sele.'>'.$rw1['e_nmb'].'</option>';
        }
        echo '
        </select>
      </div>
      <div class="col-md-4 mb-5">
        <label>País*</label>
        <input type="text" class="form-control" id="pais" name="pais" maxlength="100" value="'.$this->model->pais.'" '.$rpais.'>
      </div>
      <div class="col-md-4 mb-5">
        <label>Régimen Fiscal</label>
        <select name="refimenfis" id="regimenfis" class="form-control">
          <option value="">Selecciona un Regimen Fiscal</option>';  
          $sql = 'SELECT * FROM cfdi_regimenfiscal ORDER BY cr_regimen';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            if($this->model->regimen == $row['cr_regimen']) $selected = 'selected';
            else $selected = '';
            echo '<option value="'.$row['cr_regimen'].'" '.$selected.'>'.$row['cr_regimen'].' - '.$row['cr_nmb'].'</option>';
          }
          echo '
        </select>
      </div>
      <div class="col-lg-12 text-xs-center">
        <button id="guardar"  type="submit"  value=""  class="btn btn-success text-white"><i class="fa fa-save"></i> Guardar</button>
      </div>
    </div>
  </div>
  </div>



  </form>  
  ';

  
  $marca = busca($_GET['id'],'direnvio','d_predeterminado="1" AND d_cliente','d_id');
  $sql = 'SELECT * FROM direnvio WHERE d_cliente="'.$_GET['id'].'" ORDER BY d_predeterminado DESC ';
  $rs = setq($sql) or die($sql);
  //$rw = $rs->fetch_array();

  $facturacion = '';
  $showdf = 'style="display:none;"';
  $rrfc = '';
  $rrazon = '';
  $rcalle = '';
  $rnume = '';
  $rcolonia = '';
  $rmuni = '';
  $restado = '';
  $rpais = '';
  $rcp = '';
  if($this->model->facturacion == "1"){
    $facturacion = "checked";
    $showdf = '';
    $rrfc = '';
    $rrazon = '';
    $rcalle = '';
    $rnume = '';
    $rcolonia = '';
    $rmuni = '';
    $restado = '';
    $rpais = '';
    $rcp = '';
  }

  
  ?>
  <script>
  function iconcollapse(idcol){
    var numdir = document.getElementById("numdirs").value;

/*     for(var i=1; i <= numdir;i++){
      //var nmbdir = document.getElementById("nmbdir" + i).value;
      if(i == idcol){
        //document.getElementById("nmbdir" + i).disabled = false;
        document.getElementById("update" + i).style.display = "";
      }
      else{
        //document.getElementById("nmbdir" + i).disabled = true;
        document.getElementById("update" + i).style.display = "none";
      }
    }
     */
    var id = document.getElementById("heading"+idcol);
    var id2 = document.getElementById("btn"+idcol);
    var demas = document.getElementsByClassName("activado");
    $(demas).removeClass("activado");
    id.classList.add("activado");

    console.log($("#accordion"+idcol).attr("aria-expanded"));
    if($("#accordion"+idcol).attr("aria-expanded") == "true"){
      var icon = document.getElementById("drop"+idcol);
      icon.classList.replace("icon-minus","fa fa-plus");
    }else{

      var icon = document.getElementById("drop"+idcol);
      icon.classList.replace("fa fa-plus","icon-minus");
    }
  


  }
  </script>
  <?php

  echo '
  <div class="col-lg-9 disp" id="d" style="display: none;">
  <div class="card">
  <div class="card-header mb-2" style="display: flex; justify-content: space-between;">
    <h3>DIRECCIONES DE '.clearvmayus($this->model->nombre).'</h3>
    <a data-fancybox data-type="ajax" href="javascript:;" data-src="popup/direnviocliente.php?cliente='.$this->model->id.'&rand='.rand(1,9999).'" style="display: flex; align-items: flex-end;">
      <button type="button" class="btn btn-primary ml-1"><i class="fa fa-plus"></i> Nueva </button>
    </a>
  </div>
  <div id="accordionWrapa1" role="tablist" aria-multiselectable="true">
  ';

  while($rw = $rs->fetch_array()){
    if($rw['d_predeterminado']) $pred = "checked"; else $pred = "";
    $idcol++; 
    if($idcol == 1){ $ariae = ' in '; $icondef = "icon-minus4"; $showup = ' '; } else{ $ariae = ""; $icondef = "fa fa-plus"; $showup = ' style="display:none;" '; }
    if($pred == "checked"){
      $bck = "activado";
      $span = '<span class="tag tag-warning" data-toggle="tooltip" title="Dirección Predeterminada"><i class="icon-star"></i></span>';
      $blanquito = "blanquito";
      $icon = "icon-minus";
    }else{ 
      $bck = "";
      $span = "";
      $blanquito = "";
      $icon = "fa fa-plus";
    }
    echo '
    
    <a id="btn'.$idcol.'" data-toggle="collapse" data-parent="#accordionWrapa1" href="#accordion'.$idcol.'" aria-expanded="true" aria-controls="accordion1" class="card-title lead '.$blanquito.' blanquito" onclick="iconcollapse('.$idcol.')">
      <div id="heading'.$idcol.'" class="card-header pt-1 rounded desactivado2 '.$bck.'" >
        <div class="mb-5 " style="display: flex; justify-content: space-between;">
            <h6>'.$rw['d_calle'].' <i id="drop'.$idcol.'" class="'.$icon.'"></i></h6>
            '.$span.'
        </div>
        
        <div class="mb-5" id="update'.$idcol.'" '.$showup.'>
          <!-- <button type="button" onclick="updatedir('.$idcol.');" class="btn btn-success" id="update'.$idcol.'" '.$showup.'><i class="fa fa-redo"></i> Actualizar</button> -->
        </div>

      </div>
    </a>
    ';

    echo '
    <form action="?modulo=clientesw&accion=updatedir" method="POST" id="accordion'.$idcol.'" role="tabpanel" aria-labelledby="heading'.$idcol.'" class="card-collapse collapse '.$ariae.'" aria-expanded="true">
    <div class="row nowrap card-block">
      <input value="'.$this->model->id.'" name="id" hidden>
      <input value="'.$rw['d_id'].'" name="did" hidden>
      <div class="col-lg-4 mb-5">
        <label>Calle*</label>
        <input type="text" name="calles" class="form-control" id="calles" value="'.$rw['d_calle'].'"  />
      </div>
      <div class="col-lg-2 mb-5">
        <label>No. Exterior*</label>
        <input type="text" name="numeros" id="numeros" class="form-control" value="'.$rw['d_nume'].'" />
      </div>
      <div class="col-lg-2 mb-5">
        <label>No. Interior</label>
        <input type="text" name="interior" id="interior" class="form-control" value="'.$rw['d_numi'].'"/>
      </div>
      <div class="col-lg-4 mb-5">
        <label>Colonia*</label>
        <input type="text" name="colonias" id="colonias" class="form-control" value="'.$rw['d_colonia'].'" />
      </div>
      <div class="col-lg-2 mb-5">
      <label>Codigo Postal*</label>
        <input type="text" name="cps" id="cps" class="form-control" value="'.$rw['d_cp'].'"/ >
      </div>
      <div class="col-lg-3 mb-5">
        <label>Municipio*</label>
        <input type="text" name="municipios" class="form-control" id="municipios" value="'.$rw['d_municipio'].'" / >
      </div>
      <div class="col-lg-3 mb-5">
        <label>Estado*</label>';
        $sql='SELECT * FROM estado WHERE e_estatus="A"';
        $res=setq($sql) or die($sql);
        echo '
        <select name="estados" class="form-control" id="estados" >
          <option value="">SELECCIONAR ESTADO</option>';
          while($rw1=$res->fetch_array()){
            if($rw['d_estado']==$rw1['e_nmb']) $sele='selected';else $sele='';
            echo '<option value="'.$rw1['e_nmb'].'" '.$sele.'>'.$rw1['e_nmb'].'</option>';
          }
        echo '
        </select>
      </div>
      <div class="col-md-2 mb-5">
        <label>Telefono*</label>
        <input name="telefono" value="'.$rw['d_telefono'].'" class="form-control" required>
      </div>
      <div class="col-lg-2 mb-5">
        <label>País*</label>
        <input type="text" name="paiss" id="paiss" class="form-control" value="'.$rw['d_pais'].'"/ >
      </div>
      <div class="col-lg-12 mb-5">
        <label>Referencia de Envío*</label>
        <textarea name="referencia" class="form-control" rows="4" cols="35">'.$rw['d_referencia'].'</textarea>
      </div>
      <div class="mb-5 col-md-4 col-lg-2">
        <center>
          <label for="predeterminada">Predeterminada</label><br>
          <input type="checkbox" name="predeterminada" id="predeterminada'.$idcol.'" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="danger" '.$pred.'>
        </center>
      </div>
      <div class="col-lg-12 text-xs-center">
        <button id="guardar"  type="submit"  value=""  class="btn btn-success text-white"><i class="fa fa-save"></i> Guardar</button>
      </div>
    </div>
    </form>
    
    
    ';
  }

  echo '</div>
        </div>
        </div>';
  echo '<input type="hidden" id="numdirs" value="'.$idcol.'">';



  
  $this->actividades();
  echo '
  </div>';

  echo '
  <script>
  function displayform(id){
    if(id == "") id = "b";
    var divs = document.getElementsByClassName("disp");
    var btns = document.getElementsByClassName("activado2");
    document.getElementById("opt").value = id;
    var i = 0; 
    for(i = 0; i < divs.length; i++){
      $("#"+divs[i].id).css("display","none");
    } 
    $("#"+btns[0].id).removeClass("activado2");
    console.log(btns[0]);
    //$("#"+btns[0].id).removeClass("text-white");
    $("#"+id).slideDown( "fast", function() {
      // Animation complete
    });
    $("#btn"+id).addClass("activado2");
    //$("#btn"+id).addClass("text-white");
  }
  displayform("'.$_GET['opt'].'");
  </script>';







  /* 
    echo '
    <div class="card" id="tabla01"><center>
    <form autocomplete="off" name="fcliente" action="?modulo=clientesw&accion='.$accion.'" id="fcliente" method="post" onsubmit="return checkSubmitguardar();">
    <div class="table-responsive">
    <table width="100%" class="table table-bordered table-hover table-striped">
    <thead>
    <tr><th colspan="4" class="bg-light-blue bg-darken-2 text-white">'.$titulo.'</th></tr>
    </thead>';
    echo '<tr>
    <th colspan="4">Datos Personales</th>
    </tr><tr>
    <th>Id</th><td><input type="" class="form-control" name="id" value="'.$this->model->id.'" readonly></td>
    <th>Género</th>
    <td>
    <input type="radio" name="gender" value="F" '.$chksexf.'>Femenino<br>
    <input type="radio" name="gender" value="M" '.$chksexm.'>Masculino<br>
    </td>
    </tr>
    <tr>
    <th>Nombre*</th>
    <td><input type="text" id="nombre" name="nombre" class="form-control" maxlength="250" value="'.$this->model->nombre.'"  required autofocus>
    <br><label style="color:red; display:none;" class="error nombre">Ingrese un nombre válido</label></td>
    <th>Apellidos*</th>
    <td><input type="text" id="apellidos" name="apellidos" maxlength="250" class="form-control" value="'.$this->model->apellidos.'" >
    <br><label style="color:red; display:none;" class="error apellidos">Ingrese un apellido válido</label></td>
    </tr><tr>
    <th>Fecha de Nacimiento*</center></th>
    <td><input type="date" id="fnacimiento" name="fnacimiento" class="form-control" value="'.$this->model->fnacimiento.'"  ></td>
    <th>Correo*</th>
    <td><input type="email" id="mail" name="mail" maxlength="100"  class="form-control" value="'.$this->model->mail.'" required>
    <br><label style="color:red; display:none;" class="error mail">El correo ya se encuentra en uso</label>
    </td>
    </tr><tr>
    <th>Teléfono*</th>
    <td><input type="text" id="telefono" name="telefono" maxlength="10" class="form-control" value="'.$this->model->telefono.'" >
    </td>
    <th>Teléfono Movil*</th>
    <td><input type="text" id="celular" name="celular" maxlength="10" class="form-control" value="'.$this->model->celular.'"></td>
    </tr><tr>
    <th>Estatus</th> ';

    $est = 'checked';
    if($this->model->estatus == "I")
      $est = '';
    echo '<td><input type="checkbox" name="estatus" '.$est.'></td>
      <th>Esquema</th>';
    $sql='SELECT * FROM esquemas WHERE e_estatus="1"';
    $rs=setq($sql) or die($sql);

    echo '<td><select name="esquema" class="form-control" required>
          <option value="">Seleccionar un esquema</option>   ';
          while($rwes=$rs->fetch_array()){
            if($this->model->esquema==$rwes['e_id']) $selesq='selected'; else $selesq="";
            echo '<option value="'.$rwes['e_id'].'" '.$selesq.'>'.$rwes['e_nmb'].'</option>';
          }
    echo '</select></td>
    </tr>
    <tr>
          <th>Cambiar contraseña <input type="checkbox" id="preguntapass" onchange="preguntapassf();"></th>
          <td><input class="form-control" name="passcl" type="password" id="passcl" disabled></td>
    </tr>
    </table>';
    $marca = busca($_GET['id'],'direnvio','d_predeterminado="1" AND d_cliente','d_id');
    $sql = 'SELECT * FROM direnvio WHERE d_predeterminado="1" AND d_cliente="'.$_GET['id'].'"';
    $rs = setq($sql) or die($sql);
    $rw = $rs->fetch_array();

    $facturacion = '';
    $showdf = 'style="display:none;"';
    $rrfc = '';
    $rrazon = '';
    $rcalle = '';
    $rnume = '';
    $rcolonia = '';
    $rmuni = '';
    $restado = '';
    $rpais = '';
    $rcp = '';
    if($this->model->facturacion == "1"){
      $facturacion = "checked";
      $showdf = '';
      $rrfc = '';
      $rrazon = '';
      $rcalle = '';
      $rnume = '';
      $rcolonia = '';
      $rmuni = '';
      $restado = '';
      $rpais = '';
      $rcp = '';
    } 
  */

/*   //direccion de envio
 echo ' <br>';
  echo '<div class="table-responsive"> <table  class="table table-bordered table-hover table-striped" border="1" width="100%">
  <thead>
    <tr>
        <th colspan="4" class="bg-light-blue bg-darken-2 text-white">Datos de envio</th>
    </tr>
  </thead>
    <tr>
        <th width="20%">Calle*</th>
        <td width="30%"><input type="text" name="calles" class="form-control" id="calles" value="'.$rw['d_calle'].'"  /></td>
        <th width="20%">Número Exterior*</th>
        <td width="30%"><input type="text" name="numeros" id="numeros" class="form-control" value="'.$rw['d_nume'].'" /></td>
    </tr>
    <tr>
        <th>Número interior</th>
        <td><input type="text" name="interior" id="interior" class="form-control" value="'.$rw['d_numi'].'"/></td>
        <th>Colonia*</th>
        <td><input type="text" name="colonias" id="colonias" class="form-control" value="'.$rw['d_colonia'].'" /></td>
    </tr>
    <tr>
        <th>Codigo Postal*</th>
        <td><input type="text" name="cps" id="cps" class="form-control" value="'.$rw['d_cp'].'"/ ></td>
        <th>Municipio*</th>
        <td><input type="text" name="municipios" class="form-control" id="municipios" value="'.$rw['d_municipio'].'" / ></td>
    </tr>
    <tr>
        <th>Estado*</th>
        <td>';
        $sql='SELECT * FROM estado WHERE e_estatus="A"';
        $rs=setq($sql) or die($sql);

  echo '<select name="estados" class="form-control" id="estados" >
            <option value="">SELECCIONAR ESTADO</option>';
        while($rw1=$rs->fetch_array()){
          if($rw['d_estado']==$rw1['e_id']) $sele='selected';else $sele=NULL;
          echo '<option value="'.$rw1['e_id'].'" '.$sele.'>'.$rw1['e_nmb'].'</option>';
        }
  echo '</select>
        </td>
        <th>País*</th>
        <td><input type="text" name="paiss" id="paiss" class="form-control" value="'.$rw['d_pais'].'"/ ></td>
    </tr>
    <tr>
    <th>Referencia de envío</th>
    <td><textarea name="referencia" class="form-control" rows="4" cols="35">'.$rw['d_referencia'].'</textarea></td>
    <th></th>
    <td></td>
    </tr><tr>
    <td colspan="4"><input type="checkbox" name="facturacion" id="facturacion" '.$facturacion.' onclick="enablefac();">Habilitar Facturacion</td>
    </tr>
    </table></div>';

  //datos de facturacion
  $chkigual = '';
  if($this->model->chkigual == "1") $chkigual="checked";
  echo '<br> <div class="table-responsive">
  <table class="table table-hover table-striped table-bordered" id="dfacturacion" border="1" width="100%" '.$showdf.'>
  <thead>
  <tr>
  <th colspan="4" class="bg-light-blue bg-darken-2 text-white">Información Fiscal</th>
  </tr>
  </thead>
  <tr>
      <td colspan="4"><input type="checkbox" class="" name="chkenvio" id="chkenvio" '.$chkigual.'>La dirección de envio es la misma que la de facturación</td>
  </tr>
  <tr>
  <th>RFC</th>
  <td><input type="text" name="rfc" id="rfc" class="form-control" maxlength="13" value="'.$this->model->rfc.'" '.$rrfc.' title="INGRESAR UN RFC VALIDO Y SIN ESPACIOS" pattern="^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$" >
  <br><label style="color:red; display:none;" class="error rfc">Ingrese un RFC válido</label></td>

  <th>Razon Social*</th>
  <td><input type="text" id="razon" name="razon" class="form-control" maxlength="100" value="'.$this->model->razon.'" '.$rrazon.'>
  <br><label style="color:red; display:none;" class="error razon">Ingrese un Razon Social válido</label></td>
  </tr>
  <tr class="denvio">
  <th>Calle*</th>
  <td><input type="text" name="calle" id="calle" class="form-control" maxlength="250" value="'.$this->model->calle.'" '.$rcalle.'>
  <br><label style="color:red; display:none;" class="error calle">Ingrese una Calle válida</label></td>
  <th>Número Exterior*</th>
  <td><input type="text" id="nume" name="nume" class="form-control" maxlength="15" value="'.$this->model->nume.'"  '.$rnume.'></td>
  </tr><tr  class="denvio">
  <th>Número Interior</th>
  <td><input type="text" id="numi" name="numi" class="form-control" maxlength="15" value="'.$this->model->numi.'"></td>
  <th>Colonia*</center></th>
  <td><input type="text" id="colonia" name="colonia" class="form-control" maxlength="100" value="'.$this->model->colonia.'" '.$rcolonia.'></td>
  </tr><tr  class="denvio">
  <th>Codigo Postal*</th>
  <td><input type="text" id="cp" name="cp" maxlength="5" class="form-control" value="'.$this->model->cp.'" pattern="[0-9]{5}" '.$rcp.'></td>
  <th>Municipio*</th>
  <td><input type="text" id="municipio" name="municipio" class="form-control" maxlength="100" value="'.$this->model->municipio.'" '.$rmuni.'> </td>
  </tr><tr class="denvio">
  <th>Estado*</th>
  <td>
  ';
        $sql='SELECT * FROM estado WHERE e_estatus="A"';
        $rs=setq($sql) or die($sql);

  echo '<select name="estado" class="form-control" id="estado" '.$restado.'>
            <option >SELECCIONAR ESTADO</option>';
        while($rw1=mysql_fetch_array($rs)){
          if($this->model->estado==$rw1['e_id']) $sele='selected';else $sele=NULL;
          echo '<option value="'.$rw1['e_id'].'" '.$sele.'>'.$rw1['e_nmb'].'</option>';
        }
  echo '</select></td>
  <th>País*</th>
  <td><input type="text" class="form-control" id="pais" name="pais" maxlength="100" value="'.$this->model->pais.'" '.$rpais.'></td>
  </tr></table> </div>'; */
  ?>
  <script>
    var x;
    x=$(document);
    x.ready(inicio);
    function inicio(){
      $("#chkenvio").click(mostocul);
    }
    function mostocul(){
      if($("#chkenvio").is(':checked')){
        //$(".denvio").hide();
        var calles=$('#calles').val();
        var numeros=$('#numeros').val();
        var interior=$('#interior').val();
        var colonias=$('#colonias').val();
        var cps=$('#cps').val();
        var municipios=$('#municipios').val();
        var estados=$('#estados').val();
        var paiss=$('#paiss').val();
        $('#calle').val(calles);
        $('#nume').val(numeros);
        $('#numi').val(interior);
        $('#colonia').val(colonias);
        $('#cp').val(cps);
        $('#municipio').val(municipios);
        $('#estado').val(estados);
        $('#pais').val(paiss);

      }
      else{
        //$(".denvio").show();
        $('#calle').val('');
        $('#nume').val('');
        $('#numi').val('');
        $('#colonia').val('');
        $('#cp').val('');
        $('#municipio').val('');
        $('#estado').val('');
        $('#pais').val('');
      }
    }
  </script>
  <?php

/*   echo '
  <table class="table table-hover table-bordered table-striped text-xs-center" border="1" width="100%"><tr><td colspan="4">
  <button id="guardar"  type="submit"  value=""  class="btn btn-success text-white"><i class="fa fa-save"></i> Guardar</button>
  </td></tr>
  </table></form>'; */
}

function show(){
  echo '<div class="row page-title-actions">';
  echo '<a href="?modulo=clientesw&accion=index&w='.$this->model->id.'"onclick="this.onclick = function(){return false;}">
          <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
        </a>
  <a href="?modulo=clientesw&accion=edit&id='.$this->model->id.'" onclick="this.onclick = function(){return false;}">
    <button type="button" class="btn btn-primary ml-1"><i class="fa fa-pen"></i> Editar </button>
  </a>
  <a href="?modulo=clientesw&accion=direcciones&id='.$this->model->id.'" onclick="this.onclick = function(){return false;}">
    <button type="button" class="btn btn-info ml-1"><i class="icon-home4"></i> Ver direcciones </button>
  </a>';

  if(!$this->model->idc){
    echo '<button class="btn btn-success" onclick="confirmaaut('.$this->model->id.');"><i class="icon-study"></i> Autorizar cliente</button>';
    echo '<input type="hidden" id="clientweb" value="'.$this->model->nombre.' '.$this->model->apellidos.'">';
  }
 ?>
  <script>
    function confirmaaut(idaut){
      var clie = document.getElementById("clientweb").value;
      var conf = confirm("Al marcar a " + clie + "pasará a la lista de clientes de JD CEO\n¿Deseas continuar?");
      if(conf == true){
        window.location.href="?modulo=clientesw&accion=autorizacl&idc=" + idaut;
      }
    }
  </script>
<?php

  //echo '<a '.$chvercliente.'  href="?modulo=clientesw&accion=detallesuc&cliente='.$this->model->id.'&suc='.$row1['s_id'].'" onclick="this.onclick = function(){return false;}"><input type="button" value=" " id="detalles" class="botonb" accesskey="e"></a>';
/*
  if($this->model->estatus == "A"){
    //echo '<a href="?modulo=cotizaciones&accion=edit&estado=anadir&id='.$this->model->id.'&nmb='.busca($this->model->id,'clientes','c_id','c_nmb') .'"><input type="button" class="botonb" id="nvocoti" /></a>';
    echo '<a href="?modulo=clientesw&accion=bonificacion&id='.$this->model->id.'"><input type="button" class="botonb" id="edocta" /></a>';
    echo '<a href="?modulo=clientesw&accion=listpedidos&id='.$this->model->id.'"><input type="button" class="botonb" id="detallar" /></a>';
    echo '<a href="?modulo=clientesw&accion=disable&id='.$this->model->id .'"><input type="button" class="botonb" id="desactivar" /></a>';
  }
  else
    echo '<a href="?modulo=clientesw&accion=enable&id='.$this->model->id .'"><input type="button" class="botonb" id="activar" /></a>';

*/
//  echo '<a id="popup" href="modulos/contrasenac.php?id='.$_GET['id'].'"><input type="button" id="cambpass" class="botonb"/></a>';

  echo '</div></div>';
  echo '<div class="table-responsive">
  <table class="table table-hover table-striped">
  <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1"><tr>
  <th colspan="4">Datos del Cliente</th>
  </tr>
  </thead>';
  $extension=NULL;
  $aest = array("A"=>"ACTIVO","I"=>"INACTIVO");
  $asexo = array("M"=>"MASCULINO","F"=>"FEMENINO");
  if(empty($this->model->telefono)) $this->model->telefono = "N/A";
  if(empty($this->model->celular)) $this->model->celular = "N/A";
  echo '
  <tr>
  <th>No Cliente</th><td>'.$this->model->id.'</td>
  <th>Nombre</th><td>'.$this->model->nombre.' '.$this->model->apellidos.'</td>
  </tr><tr>
  <th>Género</th><td>'.$asexo[$this->model->sexo].'</td>
  <th>Fecha de Nacimiento</th><td>'.$this->model->fnacimiento.'</td>
  </tr><tr>
  <th>Número Telefónico</th><td>'.$this->model->telefono.'</td>
  <th>Número Celular</th><td>'.$this->model->celular.'</td>
  </tr><tr>
  <th>E-mail</th><td>'.$this->model->mail.'</td>
  <th>Estatus<b></th><td>'.$aest[$this->model->estatus].'</td>
  </tr>
  <tr>
  <th>Fecha de Registro</th><td>'.$this->model->falta.'</td>
  </tr>

  </table>';

  echo '<br>
  <table width="100%">
  <tr>
  <td width="50%" valign="top">
  <div class="table-responsive">
  <table class="table table-hover table-striped">
  <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
  <tr><!--<th width="1%"><a href=""><input type="button" value="" id="editar" class="botont"/></a></th>-->
  <th colspan="2">Datos de Facturación</th></tr></thead>
  <tr><th>RFC</th><td>'.$this->model->rfc.'</td></tr>
  <tr><th>Razon Social</th><td>'.$this->model->razon.'</td></tr>
  <tr><th>Dirección</th><td>'.$this->model->calle.' '.$this->model->nume.'<br>
  '.$this->model->numi.' '.$this->model->colonia.'<br>
  '.$this->model->cp.' '.$this->model->municipio.'<br>
  '.busca($this->model->estado,'estado','e_id','e_nmb').' '.$this->model->pais.'</td></tr>
  </table>
  </td>';

  echo '<td width="50%" valign="top">
  <div class="table-responsive">
  <table class="table table-hover table-striped">
  <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
  <tr><th width="1%">
    <a href="?modulo=clientesw&accion=editdir&id='.$this->model->eid.'&c='.$this->model->id.'">
      <button class="btn-sm btn-primary"><i class="fa fa-pen"></i></button>
    </a>
  </th>
  <th>Datos de Envio Predeterminados</th></tr></thead>
  <tr><th>Dirección</th><td>'.$this->model->ecalle.' '.$this->model->enume.'<br>
  '.$this->model->enumi.' '.$this->model->ecolonia.'<br>
  '.$this->model->ecp.' '.$this->model->emunicipio.'<br>
  '.busca($this->model->eestado,'estado','e_id','e_nmb').' '.$this->model->epais.'</td></tr>
  <tr><th>Telefono</th><td>'.$this->model->etelefono.'</td></tr>
  <tr><th>Referencia</th><td>'.$this->model->ereferencia.'</td></tr>
  </table>
  </td>
  </tr>
  </table>';

  /*echo '<tr '.$chvercliente.'>
  <th colspan="3"><center><b>Dirección<b></center></th>
  <th><center><b>Ciudad<b></center></th>
  <th><center><b>Estado<b></center></th>
  <th><center><b>País<b></center></th>
  </tr><tr '.$chvercliente.'>
  <td colspan="3"><center>'.$direccion.'</center></td>
  <td><center>'.$row1['s_municipio'].'</center></td>
  <td><center>'.$row1['s_estado'].'</center></td>
  <td><center>'.$row1['s_pais'].'</center></td>
  </tr>
  </table>';*/


  /*echo '
  <div '.$chversucursales.'><a  href="?modulo=clientesw&accion=editSucursal&sucursal='.$_GET['id'].'"><input type ="button" value=" " class="botont" id="nuevo"></a>
  <table '.$chversucursales.' width="100%" class="lista">
  <thead>
  <tr><th colspan="6"><center><b>Matriz y Sucursales</b></center></th>
  </tr><tr>
  <th width="6%"><b>Id</b></th>
  <th width="30%"><b>Nombre</b></th>
  <th width="10%"><b>Teléfono</b></th>
  <th width="35%"><b>Dirección</b></th>
  <th width="8%"><b>Editar</b></th>
  <th width="8%"><b>Estatus</b></th>
  </tr></thead>';
  while($row=mysql_fetch_array($this->model->resultSucursal)){
  if($row['s_interior']==0)
    $interior=" ";
    else
      $interior=$row['s_interior'];

    if($row['s_cp']=="0")
      $row['s_cp']="";
  echo '<tr>
  <th><center><a href="?modulo=clientesw&accion=detallesuc&cliente='.$_GET['id'].'&suc='.$row['s_id'].'" onclick="this.onclick=function(){return false;}">'.$row['s_id'].'</a></center></th>
    <td>'.$row['s_nmb'].'</a></td>
    <td>'.$row['s_telefono'].'</a></td>
  <td><center>'.$row['s_calle'].' '.$row['s_exterior'].' '.$row['s_interior'].' '.$row['s_colonia'].' '.$row['s_cp'].' '.$row['s_municipio'].' '.$row['s_estado'].'</center></td>';
    echo '<td>'; if($row['s_nmb']!=='MATRIZ')	echo '<a href="?modulo=clientesw&accion=editsucursal&id='.$row['s_id'].'&sucursal='.$_GET['id'].'"><input type=button value=" " id="editar" class="botonb"></a>'; echo '</td>';
    if($row['s_nmb']=='MATRIZ') echo '<td>';
    if($row['s_nmb']!=='MATRIZ'){
      if($row['s_estatus']=='A' )
        echo '<td style= "background:#45C431"><a href="?modulo=clientesw&accion=desactivarsuc&id='.$row['s_id'].'"><input type="button" class="botont" id="desactivar"></td>';
      else
        echo '<td style= "background:#CC0000"><a href="?modulo=clientesw&accion=activarsuc&id='.$row['s_id'].'"><input type="button" class="botont" id="activar"></a></td>';
    }
    if($row['s_nmb']=='MATRIZ')
      echo '</td>';
  echo '</tr>';
  }*/
}

function direcciones($cliente){
  echo '<div class="row page-title-actions">
  <a href="?modulo=clientesw&accion=show&id='.$cliente.'" onclick="this.onclick = function(){return false;}">
    <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
  </a>
  <a href="?modulo=clientesw&accion=editdir&c='.$cliente.'" onclick="this.onclick = function(){return false;}">
    <button type="button" class="btn btn-primary ml-1"><i class="fa fa-plus"></i> Nueva </button>
  </a>
  </div>
  </div>';
  echo '<div class="table-responsive">
  <table class="table table-hover table-striped">
  <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
  <tr><th colspan="10">Direcciones de "'.busca($cliente,'clientes',  'c_id','c_nmb').' '.busca($cliente,'clientes',  'c_id','c_apellidos').'"</th></tr>
  <tr>
  <th>Calle</th>
  <th>Numero</th>
  <th>Colonia</th>
  <th>CP</th>
  <th>Municipio</th>
  <th>Estado</th>
  <th>Telefono</th>
  <th></th>
  <th></th>
  </tr>
  </thead><tbody>';
  while($row = $this->model->result->fetch_array()){
    if($row['d_predeterminado'] == "1")
      $color = 'style="background: #30CC38"';
    else
      $color = '';
    echo '
    <tr>
      <td '.$color.'>'.$row['d_calle'].'</td>
      <td '.$color.'>'.$row['d_nume'].'</td>
      <td '.$color.'>'.$row['d_colonia'].'</td>
      <td '.$color.'>'.$row['d_cp'].'</td>
      <td '.$color.'>'.$row['d_municipio'].'</td>
      <td '.$color.'>'.$row['d_estado'].'</td>
      <td '.$color.'>'.$row['d_telefono'].'</td>';
      echo '<td width="1%"><a href="?modulo=clientesw&accion=editdir&id='.$row['d_id'].'&c='.$cliente.'" class="btn btn-info"><i class="icon-edit"></i> Editar</a></td>';
      if($row['d_predeterminado'] == "0"){
        echo '<td width="1%"><a href="?modulo=clientesw&accion=deletedir&id='.$row['d_id'].'&c='.$cliente.'" class="btn btn-danger"><i class="fa fa-trash"></i> Eliminar</a></td>';
      }
    echo '</tr>
    ';
  }
  echo '</tbody><table>';
}

function editdir($cliente){
?>
<script language="JavaScript">
function checkSubmitguardar() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
</script>
<?php
  $accion = 'insertdir';
  $titulo = "Nueva Dirección de Envio";
  if ($this->model->id){
    $accion = 'updatedir';
    $titulo = "Actualizar Dirección de Envio";
  }
  $envio = '';
  if(isset($_GET['envio']))
    $envio = '&envio='.$_GET['envio'];

  echo '<div class="row page-title-actions">
          <a href="?modulo=clientesw&accion=direcciones&id='.$cliente.'">
            <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
          </a>
  </div>
  </div>';

  echo '
  <form autocomplete="off" method=post name="pro"  action="?modulo=clientesw&accion='.$accion.$envio.'" onsubmit="return checkSubmitguardar();">
  <div class="table-responsive">
  <table class="table table-hover table-striped">';
  echo '<thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1"><tr><th colspan="2">'.$titulo.'</th></tr></thead>';
  echo '<tr><th><b>Id</b><input type="hidden" name="id" value="'.$this->model->id.'"></th><td>'.$this->model->id.'</td></tr>';
  echo '<tr><th><b>Cliente</b><input type="hidden" name="cliente" value="'.$cliente.'"></th><td>'.busca($cliente,'clientes','c_id','c_nmb').' '.busca($cliente,'clientes','c_id','c_apellidos').'</td></tr>';
  echo '<tr><th><b>Calle</b></th><td>
  <input type="text" name="calle" size="25" maxlength="80" class="form-control"
  value="' . $this->model->calle . '" required autofocus></td></tr>';
  echo '<tr><th><b>Número Exterior</b></th><td>
  <input type="text" name="nume" size="15" maxlength="80" class="form-control"
  value="' . $this->model->nume . '" required></td></tr>';
  echo '<tr><th><b>Número Interior</b></th><td>
  <input type="text" name="numi" size="15" maxlength="80" class="form-control"
  value="' . $this->model->numi . '"></td></tr>';
  echo '<tr><th><b>Colonia</b></th><td>
  <input type="text" name="colonia" size="15" maxlength="80" class="form-control"
  value="' . $this->model->colonia . '" required></td></tr>';
  echo '<tr><th><b>Codigo postal</b></th><td>
  <input type="text" name="cp" size="5" maxlength="5" class="form-control"
  value="' . $this->model->cp . '" required></td></tr>';
  echo '<tr><th><b>Municipio</b></th><td>
  <input type="text" name="municipio" size="25" maxlength="100" class="form-control"
  value="' . $this->model->municipio. '" required></td></tr>';
  echo '<tr><th>Estado</th><td>
  ';
  $sql='SELECT * FROM estado WHERE e_estatus="A"';
  $rs=setq($sql) or die($sql);

  echo '<select name="estado" id="estado" class="form-control" required>
            <option >SELECCIONAR ESTADO</option>';
        while($rw1=$rs->fetch_array()){
          if($this->model->estado==$rw1['e_nmb']) $sele='selected';else $sele=NULL;
          echo '<option value="'.$rw1['e_id'].'" '.$sele.'>'.$rw1['e_nmb'].'</option>';
        }
  echo '</select>
  </td></tr>';
  echo '<tr><th>Pais</th><td>
  <input type="text" name="pais" size="25" maxlength="100" class="form-control"
  value="' . $this->model->pais. '" required></td></tr>';
  echo '<tr><th><b>Teléfono</b></th><td>
  <input type="text" name="telefono" size="15" maxlength="15" class="form-control"
  value="' . $this->model->telefono . '"></td></tr>';
  echo '<tr><th><b>Referencia</b></th><td>
  <textarea name="referencia" cols="50" rows="5" maxlength="250" class="form-control">' . $this->model->referencia.'</textarea></td></tr>';
  if ($this->model->predeterminado == 1){
    echo'<tr><th><b>Dirección Predeterminada</b></th><td>
  <input type="checkbox" name="predeterminado" checked> </td></tr>';
  }
  else{
    echo'<tr><th><b>Dirección Predeterminada</b></th><td>
  <input type="checkbox" name="predeterminado"> </td></tr>';
  }
  echo'<tr><td align="center" colspan="2">
  <center><button type="submit" id="guardar" class="btn btn-success"><i class="fa fa-save"></i></button></center>
  </td></tr>
  </table></form>';
}

//--------------------------
  /*######################## EDITSUCURSAL#####################################3 */
  function editsucursal(){
    echo '
      <div class="div2" id="bboton01">
      <input type="button" value=" " onClick="javascript:history.go(-1);" id="atras" class="botonb">
      </div>
      </div>
    ';
    ?>
        <script>
        var x;
        x=$(document);
        x.ready(inicio);
        function inicio(){
          var nmb=$("#nmbsuc");
          nmb.blur(validasuc);
        }
        function validasuc(){
          var nmb=$("#nmbsuc").val().toLowerCase();
          if( nmb=="matriz"){
            alert("el nombre debe de ser difirente a matriz");
            nmb=$("#nmbsuc").val("");
            nmb=$("#nmbsuc").focus();
          }
        }
        </script>
    <script language="JavaScript">
    function checkSubmitenviar(){
      document.getElementById("enviar").value="JD";
      document.getElementById("guardar").disabled=true;
      return true;
    }
    </script>
    <?php
    $accion ='insertsuc';
    $titulo="Alta Sucursal";
    if(isset($_GET['id'])){
      $accion='updatesuc';
      $titulo="Actualizar Sucursal";
    }
    echo '
      <form autocomplete="off" method=post action="?modulo=clientesw&accion='.$accion.'&cliente='.$_GET['sucursal'].'" onsubmit="return checkSubmitenviar();">
      <table border="0" cellspacing="1" width="100%" class="lista">
      <thead><tr><th colspan="6"><b>'.$titulo.'<b></th></tr></thead>
    ';
    if($accion=='updatesuc'){
      echo '<tr><th><b>Id</b></th><td><input type="hidden" name="id" value="'.$_GET['id'].'">'.$_GET['id'].'</td>';
      $sql='SELECT * FROM clientes WHERE c_id="'.$this->model->clienteid.'"';
      $result=setq($sql) or die($sql);
      $row=mysql_fetch_array($result);
      echo '<th><b>Cliente</b></th><td><input type="hidden" name="idnombre" value="'.$row['c_alias'].'">'.$row['c_alias'].'</td>
            ';
    }
    else{
      $sql='SELECT * FROM clientes WHERE c_id="'.$_GET['sucursal'].'"';
      $result=setq($sql) or $die($sql);
      $row=mysql_fetch_array($result);
      echo '<tr><th><b>Id</b></th><td><input type="hidden" name="id" value="'.$this->model->id.'"></td>
            <th><b>Cliente</b></th><td><input type="hidden" name="idnombre" value="'.$row['c_alias'].'">'.$row['c_alias'].'</td>
                <th><b>Nombre Sucursal</b></th>
        <td><input type="text" name="nombre" id ="nmbsuc" maxlength="200" value="'.$this->model->nombre.'" required autofocus></td>
            </tr>';
    }
    echo '</tr>
      <tr>
                <th><b>Correo Electronico</b></th>
        <td><input type="text" name="emails" maxlength="200" value="'.$this->model->emails.'"></td>
                <th><b>Telefono</b></th>
        <td><input type="text" name="telefono" maxlength="200" value="'.$this->model->telefono.'" ></td>
                <th><b>Celular</b></th>
        <td><input type="text" name="celular" maxlength="200" value="'.$this->model->celular.'" ></td>
            </tr>
      <tr>
                <th><b>Calle</b></th>
        <td><input type="text" name="calle" maxlength="200" value="'.$this->model->calle.'" ></td>
        <th><b>Numero Exterior</b></th>
        <td><input type="text" name="exterior" maxlength="200" value="'.$this->model->exterior.'"></td>
        <th><b>Numero interior</b></th>
        <td><input type="text" name="interior" maxlength="200" value="'.$this->model->interior.'" ></td>
            </tr>
      <tr>
                <th><b>Colonia</b></th>
        <td><input type="text" name="colonia" maxlength="200" value="'.$this->model->colonia.'" ></td>
        <th><b>Codigo Postal</b></th>
        <td><input type="text" name="codigoP" maxlength="200" value="'.$this->model->codigoP.'" ></td>
        <th><b>Municipio</b></th>
        <td><input type="text" name="municipio" maxlength="200" value="'.$this->model->municipio.'" ></td>
            </tr>
      <tr>
                <th><b>Estado</b></th>
        <td><input type="text" name="estado" maxlength="200" value="'.$this->model->estado.'" ></td>
        <th><b>Pais</b></th>
        <td><input type="text" name="pais" maxlength="200" value="'.$this->model->pais.'" ></td>
      </tr>';
    echo '<tr><td colspan="6"><center><input id="guardar" type="submit" name="save" value=" " class="botont"></center></td></tr>
        </table></form>';
  }
  /*######################## show detalles #####################################3 */
  function showsuc(){
    echo '
      <div class="div2" id="bboton01">
      <input type="button" value=" " onClick="javascript:history.go(-1);" id="atras" class="botonb">
      </div>
      </div>';
    echo '<center><table width="80%" class="lista">
      <thead>
      <tr>
        <th colspan="4"><b>Sucursal</b></th>
      </tr>
      </thead>';
    echo '
      <tr>
        <th><b>Id</b></th>
        <td>'.$_GET['suc'].'</td>
        <th><b>Nombre</b></th>
        <td>'.$this->model->nombre.'</td>
      </tr></table></center>';
        	//<a href="?modulo=clientesw&accion=editsucdet&cliente='.$this->model->id.'&suc='.$_GET['id'].'"><input type ="button" value=" " class="botont" id="nuevo" accesskey="N" ></a>
    echo '
      <center>
             <a id="popup" href="modulos/popup/detallenuevo.php?cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'" onclick="this.onclick = function(){return false;}"><input type="button" value=" " class="botont" id="nuevo" accesskey="n"></a>
       <table width="60%" class="lista">
      <thead>
      <tr>
        <th colspan="6"><center><b>Detalles</b></center></th>
      </tr>
      <tr>
        <th>Id</th>
                <th>nombre</th>
        <th>Informacion</th>
      </tr>
      </thead>';
    while($row=mysql_fetch_array($this->model->selectext)){
      echo '
        <tr>
          <th><a href="?modulo=clientesw&accion=editsucdet&id='.$row['e_id'].'" onclick="this.onclick=function(){return false;}">'.$row['e_id'].'</a></th>
          <td>'.$row['e_nmb'].'</td>
          <td>'.$row['e_informacion'].'</td>
                    <td><a href="?modulo=clientesw&accion=desactivarsuc&id='.$row['s_id'].'"><input type="button" class="botont" id="borrar"></td>
        </tr>';
    }
  }
  /*######################## agregar y editar servicios #####################################3 */
    function detallesuc(){
      echo '
      <div class="div2" id="bboton01">
            <a href="?modulo=clientesw&accion=show&id='.$_GET['cliente'].'"><input type=button value=" " id="atras" class="botonb"></a>
      </div>
      </div>';
            if(!isset($_GET['error'])) $_GET['error']=NULL;
        if($_GET['error']=='1'){
         echo "<script languaje='javascript'>alert('El elemento tiene atributos configurados')</script>";
      }
        ?>
    <script type="text/javascript">
      function CheckSubmitenviar(){
        document.getElementById("guardar").value="JD";
        document.getElementById("guardar").disabled=true;
        return true;
      }
    </script>
    <?php
        $autofocuse="";
        $autofocus=' ';
        $autofocuss=" ";
        if($_GET['select']=='equipo') $autofocuse=' autofocus ';
        if($_GET['select']=='editequipo') $autofocuse=' autofocus ';
        if($_GET['select']=='servicio' || !$_GET['select']) $autofocuss=' autofocus ';
        echo '<center><table width="100%">
                <tr>
                    <td width="50%" valign="top">';
                    //###########COLUMNA##############################################
                    //#########################################################3
                    if($_GET['select']=='servicio'){
                    }
                        $sql='SELECT * FROM sucextra where e_sucursal='.$_GET['suc'].'';
                        $result=setq($sql) or die($sql);
                        $accion='insertext';
                        $id=' ';
                        if(isset($_GET['id'])){
                        	$accion='updateext';
                            $id='&id='.$_GET['id'];
                        }
                            echo '
                        <center>
                          <form  action=?modulo=clientesw&accion='.$accion.'&suc='.$_GET['suc'].'&cliente='.$_GET['cliente'].$id.' method=post onsubmit="return checkSubmitdetalle();">
                          <table width="100%" border="0" cellpadding="0" cellspacing="5" class="lista">
    	                    <thead><tr>
    	                    <th colspan="2"><center><b>Servicios</b></center></th> </tr>
                            </thead>';
                            echo '  	<tr>
              	            <th><b>Nombre</b></th>
              	            <th><b>Valor</b></th>
                        	</tr>
                        	<tr>
                          	<td><input type="text" name="nombre" maxlength="200" value="'.$this->model->enombre.'" required '.$autofocuss.'></td>
                          	<td><textarea name="informacion" maxlength="699" rows="4" cols="40" required >'.$this->model->einformacion.'</textarea></td>
                                    <tr>
                          	<td colspan="2"><center><input type=submit value=" " id="guardar" class="botont"></center></td>
                                    </tr>
                        	</tr>';
                echo     '</table></form></center>';
                        echo'<center><table width="100%" border="0" cellpadding="0" cellspacing="5" class="lista">';
        	            while($row=mysql_fetch_array($result)){
                      echo '
          	            <tr>
          	            	<td width="20%">'.$row['e_nmb'].'</td>
          		            <td>'.nl2br($row['e_informacion']).'</td>
          		            <td width="13%"><a href="?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&id='.$row['e_id'].'&select=servicio"><input type="button" class="botonb" id="editar" /></a></td>
          		            <td width="13%"><a href="?modulo=clientesw&accion=eliminardetsuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&id='.$row['e_id'].'"><input type="button" class="botonb" id="borrar" /></a></td>
                      	</tr>
                      	';
                    	}echo '</table></center>';
                        //########################################################################################
                        //############Cierra columna############################################################################
                        //########################################################################################
                    echo '</td>
                    <td valign="top" width="50%">';
                    //########################################################################################
                        //############CCOLUMNA NUEVA############################################################################
                        //########################################################################################
                        //<form  action=?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&select=equipo method=post onsubmit="return checkSubmitdetalle();">
                        if($_GET['select']=='equipo'){
                          $sql='SELECT * FROM surcusal_servicioinfo WHERE ss_id="'.$_GET['idnmb'].'"';
                          $result= setq($sql);
                          $row1=mysql_fetch_array($result);
                          $accion='addservicioequipo';
                          $id='&idnmb='.$_GET['idnmb'];
                          if($_GET['id']){
                        	$accion='updateservicioequipo';
                            $id='&id='.$_GET['id'].'&idnmb='.$_GET['idnmb'];
                            $sql='SELECT * FROM serviciosinfo where si_id='.$_GET['id'].'';
                            $result=setq($sql) or die($sql);
                            $row2=mysql_fetch_array($result);
                            $valor1=$row2['si_valor1'];
                            $valor2=$row2['si_valor2'];
                            $valor3=$row2['si_valor3'];
                          }
                          if(!isset($valor1)) $valor1=NULL;
                          if(!isset($valor2)) $valor2=NULL;
                          if(!isset($valor3)) $valor3=NULL;
                          echo '<center>
                                <form  action=?modulo=clientesw&accion='.$accion.'&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].$id.' method=post onsubmit="return checkSubmitdetalle();">
                                    <table  width="100%" border="0" cellpadding="0" cellspacing="5" class="lista">
                                          <tr>
                                          <thead>
                                            <th colspan="4">Equipo<input type="hidden" name="id" value="'.$row1['ss_nmb'].'"> '.$row1['ss_nmb'].'</th>
                                            </thead>
                                          </tr>
                                          <tr>
                                            <th>Valor 1</th>
                                            <th>Valor 2</th>
                                            <th>Valor 3</th>
                                          </tr>
                                          <tr>
                                            <td><input type="text" name="valor1" id="valor1" maxlength="50" value="'.$valor1.'" required  '.$autofocuse.'></td>
                                            <td><input type="text" name="valor2" id="valor1" maxlength="50" value="'.$valor2.'" required ></td>
                                            <td><input type="text" name="valor3" id="valor3" maxlength="50" value="'.$valor3.'"  ></td>
                                          </tr>
                                          <tr>
                                            <td colspan="3" width="16%"><center><input type=submit value=" " id="guardar" class="botont"></center></td>
                                          </tr>
                                    <table>
                                </form>
                            </center>
                        ';
                        $sql='SELECT * FROM serviciosinfo where si_idnombre="'.$_GET['idnmb'].'"';
                        $result=setq($sql) or die($sql);
                        echo '<table  width="100%" border="0" cellpadding="0" cellspacing="5" class="lista">';
                        while($row=mysql_fetch_array($result)){
                          echo '
                            <tr>
                                <td>'.$row['si_valor1'].'</td>
                                <td>'.$row['si_valor2'].'</td>
                                <td>'.$row['si_valor3'].'</td>
                                <td><a href="?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&idnmb='.$row1['ss_id'].'&id='.$row['si_id'].'&select=equipo"><input type="button" class="botonb" id="editar" /></a></td>
                                <td><a href="?modulo=clientesw&accion=eliminardetequipo&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&idnmb='.$row1['ss_id'].'&id='.$row['si_id'].'&select=equipo"><input type="button" class="botonb" id="borrar" /></a></td>
                            </tr>
                          ';
                        } echo '<tr><td colspan="5"><center><a href="?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'"><input type="button" class="botont" id="aplicar" accesskey="S"/></center></td></tr></table>';
                        }else{
                          //###################################################################################33
                          //###################################################################################33
                          //###################################################################################33
                          $accion='addnmbequipo';
                          $id=' ';
                          $equipo="";
                          if($_GET['select']=='editequipo'){
                        	$accion='editnmbequipo';

                            $id='&id='.$_GET['id'];

                            $equipo = busca($_GET['id'],'surcusal_servicioinfo','ss_id','ss_nmb');
                          }
                          echo '<center>
                                <form  action=?modulo=clientesw&accion='.$accion.'&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].$id.' method=post onsubmit="return checkSubmitdetalle();">
                                    <table  width="100%" border="0" cellpadding="0" cellspacing="5" class="lista">
                                          <tr>
                                            <thead>
                                            <th colspan="2"><b>Equipos </b></th>
                                            </thead>
                                          </tr>
                                          <tr>
                                            <th>Nombre</th>
                                            <th></th>
                                          </tr>
                                          <tr>
                                            <td ><input type="text" name="nmbequipo" maxlength="50" value="'.$equipo.'" required  '.$autofocuse.'></td>
                                            <td colspan="2"><center><input type=submit value=" " id="guardar" class="botont"></center></td>
                                          </tr>
                                    <table>
                                </form>
                            </center>';
                        $sql='SELECT * FROM surcusal_servicioinfo WHERE ss_idsucursal="'.$_GET['suc'].'"';
                        $result=setq($sql) or die($sql);
                        echo '<table  width="100%" border="0" cellpadding="0" cellspacing="5" class="lista">';
                        while($row=mysql_fetch_array($result)){
                          $sql='SELECT * FROM surcusal_servicioinfo
                            INNER JOIN serviciosinfo ON ss_id=si_idnombre
                            WHERE ss_id='.$row['ss_id'].'';
                            $result1=setq($sql) or die($sql);
                            $detalle="";
                            while($tool=mysql_fetch_array($result1)){
                              $detalle.='
                            #Detalle: '.$tool['si_valor1'].'
                            | '.$tool['si_valor2'].'
                            | '.$tool['si_valor3'].'#';
                            }
                            echo '<tr>
                                    <th width="8%"><center><b><a href="?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&idnmb='.$row['ss_id'].'&select=equipo" onclick="this.onclick=function(){return false;}" accesskey="M">'.$row['ss_id'].'</a></b></center></th>
                                    <td  data-tooltip="'.$detalle.'" class="tooltip-bottom style=" font-size:13px;"="">'.$row['ss_nmb'].'</td>
                                    <td width="13%"><a href="?modulo=clientesw&accion=detallesuc&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&id='.$row['ss_id'].'&select=editequipo"><input type="button" class="botonb" id="editar" /></a></td>
          		                <td width="13%"><a href="?modulo=clientesw&accion=eliminarnmbequipo&cliente='.$_GET['cliente'].'&suc='.$_GET['suc'].'&id='.$row['ss_id'].'"><input type="button" class="botonb" id="borrar" /></a></td>
                                </tr>';
                        } echo '</table>';
                    }
    }

    function edocta(){
      $meses = array("1"=>"Ene","2"=>"Feb","3"=>"Mar","4"=>"Abr","5"=>"May","6"=>"Jun","7"=>"Jul","8"=>"Ago","9"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dic");

      echo '<div class="div2" id="bboton01">

            </div>
      </div>';


      echo '<center><table class="lista" width="80%">
        <thead>
            <tr>
                <th><b>Cliente: &nbsp;&nbsp;'.$this->model->nombre.'</b></th>
            </tr>
        </thead>
        </table></center>';
        echo '
            <table align="center" width="%80">
            <tr>
            <form method="post">
                <th>Rango de fecha</th>
                <td>Desde</td>
                <td><input type="date" name="fini" id="fini" value="'.$_POST['fini'].'" onchange="submit();"></td>
                <td>Hasta</td>
                <td><input type="date" name="ffin" id="ffin" value="'.$_POST['ffin'].'" onchange="submit();"></td>
            </form>
            </tr>
            </table>
        ';
      echo '<center><thead>
        <table class="lista" cellspacing="0" width="80%"><thead>
            <tr>
                <th>Empresa</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Cargo</th>
                <th>Abono</th>
                <th>Saldo</th>
            </tr></thead>';

        while($row = mysql_fetch_array($this->model->result)){
          if($row['estatus'] != "C"){
            $saldof = $this->model->saldof;

                if($row['tipo'] == "C"){
                    $cargo = number_format($row['importe'],2);
                    $abono = "-";
                    $this->model->saldof-=$row['importe'];
                }
                else{
                    $cargo = "-";
                    $abono = number_format($row['importe'],2);
                    $this->model->saldof+=$row['importe'];
                }
          echo '<tr>
                <td>'.busca($row['empresa'],'configuracion','c_consecutivo','c_id').'</td>
                <td>'.date('d',strtotime($row['fecha'])).' - '.$meses[date('n',strtotime($row['fecha']))].' - '.date('Y',strtotime($row['fecha'])).'</td>
                <td>'.$row['concepto'].'</td>
                <td>'.$cargo.'</td>
                <td>'.$abono.'</td>
                <td>'.number_format($saldof,2).'</td>
              </tr>';

          }

        }
        echo '</table></center>';

    }


function bonificacion(){
?>
<script>
function confirmcancel(cli,boni){
  var conf = confirm("¿Deseas cacnelar la bonificacion #"+boni+"?")
  if(conf == true){
    document.location.href = "?modulo=clientesw&accion=cancelboni&id="+cli+"&b="+boni;
  }
}
</script>
<?php
  echo '<div class="div2" id="bboton01">
  <a id="popup" href="?modulo=clientesw&accion=addbonif&id='.$_GET['id'].'"><input type="button" value="" id="nuevo" class="botonb" /></a>
  </div>
  </div>';

  echo '<center><thead>
    <table class="lista" width="80%"><thead>
    <tr><th colspan="9">Bonificaciones de '.busca($_GET['id'],'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)').'</th></tr>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Motivo</th>
            <th>Monto</th>
            <th>Total</th>
            <th>Estatus</th>
            <th>Pedido</th>
            <th>Cancelar</th>
        </tr></thead>';
    $totalb = busca($_GET['id'],'bonificaciones','b_estatus = "N" AND b_cliente','SUM(b_monto)');

    while($row = mysql_fetch_array($this->model->resultb)){
      echo '<tr>
      <td>'.$row['b_id'].'</td>
      <td>'.$row['b_fecha'].'</td>
      <td>'.$row['b_hora'].'</td>
      <td>'.$row['b_motivo'].'</td>
      <td>$ '.number_format($row['b_monto'],2).'</td>';
      if($row['b_estatus'] == "A"){
        echo '<td>$ '.number_format($totalb,2).'</td>';
        echo '
        <td style="background:#30CC38;">Aplicada</td>
        <td>'.$row['b_pedido'].'</td>';
        $totalb += $row['b_monto'];
      }
      else if($row['b_estatus'] == "N"){
        echo '<td>$ '.number_format($totalb,2).'</td>';
        echo '
        <td>Pendiente</td>
        <td></td>
        <td><input type="button" value="" id="cancelar" class="botont" onclick="confirmcancel('.$_GET['id'].','.$row['b_id'].');"/></td>
        ';
        $totalb -= $row['b_monto'];
      }
      else if($row['b_estatus'] == "C"){
        echo '<td>$ '.number_format($totalb,2).'</td>';
        echo '
        <td style="background:#FF0000;">Cancelada</td>';
        $totalb += $row['b_monto'];
      }
      echo '</tr>';
    }

    echo '</table></center>';

}

function browsepedidos($page,$pageresult){
  $sql='SELECT COUNT(*) as maximo FROM pedidoscl WHERE p_cliente = "'.$_GET['id'].'"';
  $result = setq($sql) or die($sql);
  list($numre) = mysql_fetch_array($result);
  $np = $numre / $pageresult;

  echo '<div class="div2" id="bboton01">
  <a href="?modulo=clientesw&accion=show&id='.$_GET['id'].'"><input type="button" value=" " class="botonb" id="regresar"></a>';
  if($page > 1)
    echo '<a href="?modulo=clientesw&accion=listpedidos&id='.$_GET['id'].'&page='.($page-1).'"><input type="button" value=" " class="botonb" id="atras"></a>';
  if($page < $np)
    echo '<a href="?modulo=clientesw&accion=listpedidos&id='.$_GET['id'].'&page='.($page+1).'"><input type="button" value=" " class="botonb" id="adelante"></a>';
  echo '</div></div>';
    $onkeyup='onkeyup ="var key = window.event.keyCode;if(key==13){ var time=setTimeout(submit(), 1000);   }"';

  echo '<center>
  <form autocomplete="off" method=post  onsubmit="return checkSubmitenviar();">
  </form>
  <table width="100%" class="lista">
  <thead>
  <tr><th colspan="8">Pedidos de '.busca($_GET['id'],'clientes','c_id','CONCAT(c_nmb," ",c_apellidos)').'</th></tr>
  <tr>
  <th width="5%"><center><b>Id</b></center></th>
  <th><center><b>Forma de pago</b></center></th>
  <th><center><b>Factura</b></center></th>
  <th><center><b>Paqueteria</b></center></th>
  <th><center><b>Guias</b></center></th>
  <th><center><b>Total</b></center></th>
  <th><center><b>Fecha</b></center></th>
  <th><center><b>Estatus</b></center></th>
  </tr></thead>';

  $asexo = array("M"=>"Masculino", "F"=>"Femenino","" => "");
  while($row = mysql_fetch_array($this->model->resultp)){
  echo '<tr>';
    echo '<th><center><b><a href="?modulo=pedidoscl&accion=show&id='.$row['p_id'].'" onclick="this.onclick=function(){return false;}">'.$row['p_id'].'</a></b></center></th>
    <td><center>'.busca($row['p_fpago'],'formaspago','f_id','f_nmb').'</center></td>
    <td><center>'.$row['p_factura'].'</center></td>
    <td><center>'.busca($row['p_paqueteria'],'paqueterias','p_id','p_nmb').'</center></td>
    <td><center>'.$row['p_nguias'].'</center></td>
    <td><center>$ '.number_format($row['p_total'],2).'</center></td>
    <td><center>'.$row['p_fechagen'].'</center></td>
    <td><center>'.$this->estp[$row['p_estatus']].'</center></td>
    ';
  echo '</tr>';
  }
  echo '</table>';
}

function actividades(){

  echo '
    <div class="col-lg-3">
    <div class="card">
      <div class="card-header mb-1">
        <h3>Acciones</h3>
      </div>
      <div class="bg-white col-md-12 col-sm-12 card-block">
        <table class="table table-dark table-striped table-bordered table-hover">
          <thead class="bg-light-blue bg-darken-2 text-white">
            <tr><th>Elije una actividad</th></tr>
          <thead>
          <tbody>
            <tr>
              <th '.$alerta.' class="activado2" id="btnb"><a onclick="displayform(\'b\');">
                <i class="icon-user warning"></i> Datos del básicos </a>
              </th>
            </tr>
            <tr '.$alertdir.'>
              <th id="btnd"><a  onclick="displayform(\'d\');">
                <i class="icon-home warning"></i> Direcciones </a>
              </th>
            </tr>
            <tr '.$alertfisc.'>
              <th id="btnf"><a  onclick="displayform(\'f\');">
                <i class="icon-list-alt warning"></i> Datos fiscales</th>
              </a>
            </tr>
            <tr >
            <th id="btnf"><a  style="color: inherit;" href="?modulo=pedidosclw&accion=index&clienteid='.$this->model->id.'">
              <i class="icon-box warning"></i> Historial de pedidos</th>
            </a>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
    </div>
  ';

  
}

function browseacademy(){
  echo '
  <div class="page-title-actions mb-1">
    <a href="?modulo=clientesw&accion=editacademy" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo</a>
    <a href="?modulo=clientesw&accion=index" class="btn btn-info"><i class="icon-shop"></i> JDSHOP Tienda</a>
  </div>
  <div class="">
    <div class="table-responsive">
      <table class="table table-striped table-hover table-bordered">
        <thead class="bg-light-blue bg-darken-2 text-white">
         <tr class="">
          <th class="">Nombre</th>
          <th class="">Correo</th>
          <th class="">Telefono</th>
          <th class="">Ciudad</th>
          <th class="">Estatus</th>
          <td class="">Acciones</td>
         </tr>
        </thead>
        <tbody class="">';
        while($row = $this->model->result->fetch_array()){
          echo '
          <tr class="">
            <td class="">'.$row['u_nmb'].'</td>
            <td class="">'.$row['u_correo'].'</td>
            <td class="">'.$row['u_telefono'].'</td>
            <td class="">'.$row['u_ciudad'].'</td>
            <td class="">'.$row['u_estatus'].'</td>
            <td class=""><a href="?modulo=clientesw&accion=editacademy&id='.$row['u_id'].'" class="btn btn-warning"><i class="icon-edit"></i></a></td>

          </tr>
          ';
        }
        echo '
        </tbody>
      </table>
    </div>
  </div>
  
  
  ';
  

}

function editacademy(){
  if($this->model->id) {
    $accion = 'updateacademy&id='.$this->model->id.'';
    $titulo = 'Actualizar a: ';
  }else{
    $accion = 'insertacademy';
    $titulo = 'Insertar alumno';
  }
  echo '
  <div class="page-title-actions mb-1">
    <a href="?modulo=clientesw&accion=jdshopacademy" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras</a>
  </div>

  <div class="card col-md-12">
    <h3 class="">'.$titulo.' '.$this->model->nmb.'</h3>
    <hr class="">
    <form action="?modulo=clientesw&accion='.$accion.'" method="POST" class="">
    <div class="row p-1">
      <div class="col-md-3 mb-5">
        <label for="">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="'.$this->model->nmb.'" required>
      </div>
      <div class="col-md-3 mb-5">
        <label for="" class="">Apellidos</label>
        <input type="text" name="apellidos"  class="form-control" value="'.$this->model->apellidos.'">
      </div>
      <div class="col-md-3 mb-5">
        <label for="" class="">Correo</label>
        <input type="email" name="correo"  class="form-control" value="'.$this->model->correo.'" required>
      </div>
      <div class="col-md-3 mb-5">
        <label for="" class="">Telefono</label>
        <input type="text" id="telefono" name="telefono" class="form-control" value="'.$this->model->telefono.'" onchange="quitaespaciost();" required>
      </div>
      <script>
      function quitaespaciost(){
        original = document.getElementById("telefono").value;
        var x;
        var sCadenaSinBlancos = "";
        for (x=0; x < original.length; x++) {
          if (original.charAt(x) != " ")
            sCadenaSinBlancos+=original.charAt(x);
        }
        document.getElementById("telefono").value = sCadenaSinBlancos;
      }
      </script>
      <div class="col-md-3 mb-5">
        <label for="" class="">Grupo</label>
        <select name="grupo" id="grupo" class="form-control" required>
          <option value="" class="">Selecciona un grupo</option>';
          if($this->model->grupo == "ADMIN") $selectedad = 'selected';
          elseif($this->model->grupo == "ALUMNO") $selectedal = 'selected';
          echo '
          <option value="ALUMNO" class="" '.$selectedal.'>ALUMNO</option>
          <option value="ADMIN" class="" '.$selectedad.'>ADMIN</option>
        </select>
      </div>
      <div class="col-md-3 mb-5">
        <label for="" class="">Estatus</label><br>';
        if($this->model->estatus == "A") $ch = 'checked';
        else $ch = '';
        echo '
        <input type="checkbox" data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" data-on="ACTIVO" data-off="INACTIVO" name="estatus" id="" '.$ch.'>
      </div>
      <div class="col-md-3 mb-5 form-inline">
        <label for="" class="">Contraseña</label><br>
        <div class="mb-5">
          <input type="checkbox" id="preguntapass" name="preguntapass" data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" data-on="SI" data-off="NO" onchange="preguntapassf();">
        </div>
        <div class="mb-5">
          <input id="password" type="password" name="password" class="form-control" value="" disabled>
        </div>
      </div>
      <div class="col-md-12 text-xs-center mb-5">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
      </div>
    </div>
    </form>';

    if($this->model->id){
    echo '
    <div class="" style=""> <!-- display: flex; justify-content: space-between; -->
      <h3 class="">Cursos de '.$this->model->nmb.'</h3> 
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-target="#exampleModal"><i class="fa fa-plus"></i> Agregar</button>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Permitir acceso a curso</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="?modulo=clientesw&accion=addcursopedido&id='.$this->model->id.'" class="" method="POST">
          <div class="modal-body">
            <div class="col-md-12 mb-5">
              <label for="" class="">Curso</label>
              <select name="curso" id="" class="form-control">';
              $sql = 'SELECT * FROM cursos WHERE c_estatus = "A" ';
              $result = setqacademy($sql); 
              while($row = $result->fetch_array()){
                echo '
                <option value="'.$row['c_id'].'" class="">'.$row['c_nmb'].'</option>
                ';
              }

            echo '
              </select>
            </div>
          </div>
          <div class="text-xs-center mb-5">
              <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Agregar</button>
          </div>
          </form>
        </div>
      </div>
    </div>
    <hr class="">
    <div class="table-responsive">
    <table class="table table-hover table-bordered table-striped">
      <thead>
        <tr>
          <th>Curso</th>
          <th>Estatus</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>';
    $sql = 'SELECT * FROM usuarios_pedidos INNER JOIN cursos ON up_curso = c_id WHERE up_usuario = "'.$this->model->id.'"';
    $result = setqacademy($sql);
    $estatuspedidos = array("C"=>"CANCELADO","A"=>"ACREDITADO");  
    while($row = $result->fetch_array()){
      echo '
      <tr>
        <td>'.$row['c_nmb'].'</td>
        <td>'.$estatuspedidos[$row['up_estatus']].'</td>
        <td class="">
          <button data-toggle="tooltip" title="Activar curso para '.$this->model->nmb.'" onclick="aceptarcurso('.$row['up_id'].');" class="btn btn-success btn-sm"><i class="fa fa-check"></i></button>
          <button data-toggle="tooltip" title="Denegar curso para '.$this->model->nmb.'" onclick="denegarcurso('.$row['up_id'].');" class="btn btn-danger btn-sm"><i class="fa fa-times"></i></button>
          <script class="">
            function aceptarcurso(id){
              var c = confirm("¿Estas seguro de acreditar este curso?");
              if(c){
                window.location.href="?modulo=clientesw&accion=estatusacademy&estatus=A&cliente='.$this->model->id.'&id="+id;
              }
            }
            function denegarcurso(id){
              var c = confirm("¿Estas seguro de acreditar este curso?");
              if(c){
                window.location.href="?modulo=clientesw&accion=estatusacademy&cliente='.$this->model->id.'&estatus=C&id="+id;
              }
            }
          </script>
        </td>
      </tr>
      ';
      
    }
    

    echo '
      </tbody>
      </table> 
    </div>';
    }
  echo '
  </div>
  ';

  ?>
  <script>
  function preguntapassf(){
    var box = document.getElementById('preguntapass');
    if(box.checked){
      var intputcontra = document.getElementById('passcl');
      //$('#passcl').css('display','inline');
      $('#password').attr('disabled',false);
    }else{
      //$('#passcl').css('display','none');
      $('#password').attr('disabled',true);
    }
  }
  </script>
  <?php
  


}


}
class viewclienteswd {

var $model;

    function __construct($model) {
       $this->model = $model;
        $this->tipop = array("F"=>"Persona Física","M"=>"Persona Moral");
        $this->tipof = array("D"=>"Factura por Tiro Directo","R"=>"Factura por Rutas de Reparto","P"=>"Factura Previa a Reparto");
    }

    function show() {
        echo '
        <center><table width="100%" border="0"  class="lista"><thead>
                <tr>
                    <th align="center"><strong>RFC del Cliente</strong></th>
                    <th colspan="2" align="center" ><strong>Razón Social del Cliente </strong></th>
                    <th align="center"><strong>Contacto</strong></th>
                </tr></thead>
                <tr>
                    <td align="center">'.$this->model->rfc.'</td>
                    <td colspan="2" align="left" >'.$this->model->razon.'</td>
                    <td align="left" >'.$this->model->contacto.'</td>
                </tr>
                <tr>
                    <th  align="center"><strong>Calle</strong></th>
                    <th  align="center"><strong>Núm. Ext. </strong></th>
                    <th  align="center">Número Int.</th>
                    <th  align="center"><strong>Colonia</strong></th>
                </tr>';
        echo ' <tr>
                    <td  align="center">'. $this->model->calle.'</td>
                    <td  align="center">'.$this->model->nume.'</td>
                    <td  align="center">'.$this->model->numi.'</td>
                    <td  align="center">'.$this->model->colonia.'</td>
                </tr>
                <tr>
                    <th  align="center"><strong>Municipio / Delegación</strong></th>
                    <th  align="center"><strong>Estado</strong></th>
                    <th align="center" ><strong>Código Postal </strong></th>
                    <th  align="center"><strong>País</strong></th>
                </tr>

                <tr>
                    <td  align="center">'.$this->model->municipio.'</td>
                    <td  align="center">'.$this->model->estado.'</td>
                    <td align="center" >'.$this->model->cp.'</td>
                    <td  align="center">'.$this->model->pais.'</td>
                </tr>

                <tr>
                    <th  align="center" ><strong>Correo electronico </strong></th>
                </tr>
                <tr>
                    <td colspan="1" align="left" >'.$this->model->email.'</td>
                 </tr>

            </table></center>';
    }
}
?>