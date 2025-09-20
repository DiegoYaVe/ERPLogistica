<?php
  //ini_set('display_errors',1);
  class config{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelconfig(isset($obj));
    }
    function index(){
      if(!isset($_REQUEST['empresa'])) $_REQUEST['empresa'] = 1;
      $this->model->result($_REQUEST['empresa']);
      $this->view = new viewconfig($this->model);
      $this->view->datos();
    }
    function edit(){
      if(!isset($_REQUEST['empresa'])) $_REQUEST['empresa'] = NULL;
      $this->model->select($_REQUEST['empresa']);
      $this->view = new viewconfig($this->model);
      $this->view->edit();
    }
    function uploadcer(){
      $this->model->certupload(); //consulta modelo
      redirect("?modulo=config&accion=edit&empresa=".$_SESSION['emp'].$error);
    }
    function insert(){
      $error =  0;
      $this->model->setdata($_GET['id'],$_POST['nmb'],$_POST['tel'],$_POST['whatsapp'],$_POST['correo'],$_POST['correofac'],$_POST['razons'],$_POST['rfc'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['municipio'],$_POST['cp'],$_POST['estado'],$_POST['pais'],$_POST['grupo'],"A",$_POST['usd'],$_POST['host'],$_POST['port'],$_POST['seguridad'],$_POST['correopas'], $_POST['iva'],$_POST['siglas']);
      if(!empty($_FILES['logo'])){
        if (isset($_FILES['logo']['name'])) {
          $nombre_archivo = $_FILES['logo']['name'];
          $tipo_archivo = $_FILES['logo']['type'];
          $tamano_archivo = $_FILES['logo']['size'];
          if($tamano_archivo > 3000000) $error = "6XMUE2";
          else{
            if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg") || strpos($tipo_archivo, "jpg") || strpos($tipo_archivo, "png")))) {
              $error = "1XMCE3";
            }
            else{
              $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
              $destino = 'images/empresas/logo'.$_GET['id'].'.'.$extension;
              if (move_uploaded_file($_FILES['logo']['tmp_name'],  $destino)){
                $error="1";
                $sql = 'UPDATE empresas SET e_logo = "'.$destino.'" WHERE e_id = "'.$_GET['id'].'"';
                setq($sql);
              }
              else $error = "1XMCE4";
            }
          }
        }
        else{
          $error = "1";
        }
      }
      $this->model->insert();
      if($error != 0) $coderror = '&error='.$error; else $coderror = "";
      redirect("?modulo=config&accion=edit&empresa=".$this->model->id.$coderror);
    }
    function update(){
      $error =  0;
      $this->model->setdata($_GET['id'],$_POST['nmb'],$_POST['tel'],$_POST['whatsapp'],$_POST['correo'],$_POST['correofac'],$_POST['razons'],$_POST['rfc'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['municipio'],$_POST['cp'],$_POST['estado'],$_POST['pais'],$_POST['grupo'],"A",$_POST['usd'],$_POST['host'],$_POST['port'],$_POST['seguridad'],$_POST['correopas'], $_POST['iva'],$_POST['siglas']);
      if(!empty($_FILES['logo'])){
        if (isset($_FILES['logo']['name'])) {
          $nombre_archivo = $_FILES['logo']['name'];
          $tipo_archivo = $_FILES['logo']['type'];
          $tamano_archivo = $_FILES['logo']['size'];
          if($tamano_archivo > 3000000) $error = "6XMUE2";
          else{
            if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg") || strpos($tipo_archivo, "jpg") || strpos($tipo_archivo, "png")))) {
              $error = "1XMCE3";
            }
            else{
              $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
              $destino = 'images/empresas/logo'.$_GET['id'].'.'.$extension;
              if (move_uploaded_file($_FILES['logo']['tmp_name'],  $destino)){
                $error="1";
                $sql = 'UPDATE empresas SET e_logo = "'.$destino.'" WHERE e_id = "'.$_GET['id'].'"';
                setq($sql);
              }
              else $error = "1XMCE4";
            }
          }
        }
        else{
          $error = "1";
        }
      }
      $this->model->update();
      if($error != 0) $coderror = '&error='.$error; else $coderror = "";
      redirect("?modulo=config&accion=edit&empresa=".$this->model->id.$coderror);
    }
    function dellogo(){
      $this->model->select($_SESSION['emp']);
      if(unlink($this->model->logo)){
        $sqlu = 'UPDATE empresas SET e_logo = NULL WHERE e_id = "'.$_SESSION['emp'].'"';
        setq($sqlu);
      }
      redirect("?modulo=config&accion=edit&empresa=".$_SESSION['emp'].$coderror);
    }
  }

  class modelconfig{
    function select($idc){
      $sql = 'SELECT * FROM empresas WHERE e_id = "'.$idc.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['e_id'];
      $this->nmb= $row['e_nmb'];
      $this->razons= $row['e_razons'];
      $this->rfc= $row['e_rfc'];
      $this->calle= $row['e_calle'];
      $this->nume= $row['e_nume'];
      $this->numi= $row['e_numi'];
      $this->colonia= $row['e_colonia'];
      $this->ciudad= $row['e_ciudad'];
      $this->estado= $row['e_estado'];
      $this->pais= $row['e_pais'];
      $this->cp= $row['e_cp'];
      $this->regimen= $row['e_regimen'];
      $this->tel= $row['e_tel'];
      $this->whatsapp= $row['e_whatsapp'];
      $this->correo= $row['e_correo'];
      $this->correofac= $row['e_correofac'];
      $this->estatus= $row['e_estatus'];
      $this->logo = $row['e_logo'];
      $this->web = $row['e_web'];
      $this->cfdcertificado = $row['e_certificado'];
      $this->usd = $row['e_valorusd'];
      $this->host = $row['e_host'];
      $this->puerto = $row['e_puerto'];
      $this->seguridad = $row['e_seguridad'];
      $this->correopas = $row['e_passfac'];
      $this->iva = $row['e_iva'];
      $this->siglas = $row['e_siglas'];
    }
    function setdata($id,$nmb,$tel,$whatsapp,$correo,$correofac,$razons,$rfc,$calle,$nume,$numi,$colonia,$municipio,$cp,$estado,$pais,$grupo,$estatus = "A",$usd,$host,$port,$seguridad,$correopas, $iva, $siglas){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'");
      $cambio = "";
      $this->id = clearvmayus($id);
      $this->nmb = clearvmayus($nmb);
      $this->tel = clearvmayus($tel);
      $this->whatsapp = clearvmayus($whatsapp);
      $this->correo = clearvminus($correo);
      $this->correofac = clearvminus($correofac);
      $this->razons = clearvmayus($razons);
      $this->rfc = clearvmayus($rfc);
      $this->calle = clearvmayus($calle);
      $this->nume = clearvmayus($nume);
      $this->numi = clearvmayus($numi);
      $this->colonia = clearvmayus($colonia);
      $this->municipio = clearvmayus($municipio);
      $this->estado =clearvmayus($estado);
      $this->pais = clearvmayus($pais);
      $this->grupo = clearvmayus($grupo);
      $this->cp = clearvmayus($cp);
      $this->estatus = clearvmayus($estatus);
      $this->usd = clearvmayus($usd);
      $this->host = clearvmayus($host,false);
      $this->port = clearvmayus($port,false);
      $this->seguridad = clearvmayus($seguridad,false);
      $this->correopas = clearvmayus($correopas,false);
      $this->iva = $iva;
      $this->siglas = clearvmayus($siglas,false);
    }
    function result($contrato){
      $sql = 'SELECT * FROM empresas WHERE e_contrato = "'.$contrato.'" AND e_id = "'.$_SESSION['emp'].'" ORDER BY e_id DESC';
      $this->result = setq($sql);
    }
    function insert(){
      $sqlemp = 'INSERT INTO empresas SET
                e_contrato = "'.$_SESSION['emp'].'",
                e_nmb = "'.$this->nmb.'",
                e_correo  = "'.$this->correo.'",
                e_tel  = "'.$this->tel.'",
                e_whatsapp  = "'.$this->whatsapp.'",
                e_estatus = "'.$this->estatus.'",
                e_valorusd = "'.$this->usd.'",
                e_correofac = "'.$this->correofac.'",
                e_host = "'.$this->host.'",
                e_puerto = "'.$this->port.'",
                e_seguridad = "'.$this->seguridad.'",
                e_passfac = "'.base64_encode($this->correopas).'",
                e_iva = "'.$this->iva.'",
                e_passfac = "'.$this->siglas.'"';
      setq($sqlemp);
      $sql = 'SELECT MAX(e_id) FROM '.$empresas.' ';
      $result = setq($sql);
      list($this->id) = $result->fetch_array();
    }
    function update(){
      $sqlemp = 'UPDATE empresas SET
                e_nmb = "'.$this->nmb.'",
                e_correo  = "'.$this->correo.'",
                e_tel  = "'.$this->tel.'",
                e_whatsapp  = "'.$this->whatsapp.'",
                e_estatus = "'.$this->estatus.'",
                e_valorusd = "'.$this->usd.'",    
                e_host = "'.$this->host.'",
                e_puerto = "'.$this->port.'",
                e_seguridad = "'.$this->seguridad.'",
                e_passfac = "'.base64_encode($this->correopas).'",
                e_iva = "'.$this->iva.'",
                e_siglas = "'.$this->siglas.'"
                WHERE e_id = "'.$this->id.'"';
      setq($sqlemp);

      $sqlfac = 'UPDATE empresas SET
                e_razons = "'.$this->razons.'",
                e_calle = "'.$this->calle.'",
                e_rfc = "'.$this->rfc.'",
                e_nume = "'.$this->nume.'",
                e_numi = "'.$this->numi.'",
                e_colonia = "'.$this->colonia.'",
                e_ciudad = "'.$this->municipio.'",
                e_estado = "'.$this->estado.'",
                e_pais = "'.$this->pais.'",
                e_cp = "'.$this->cp.'",
                e_correofac = "'.$this->correofac.'",
                e_regimen = "'.$this->grupo.'",
                e_estatus = "'.$this->estatus.'"
                WHERE e_id = "'.$this->id.'"';
      setq($sqlfac);
    }
    function certupload() {
      $certok = 0;
      $keytok = 0;
      $error = "";

      $target = 'cfd/' . basename($_FILES['uploadcer']['name']);
      $extension = substr($_FILES['uploadcer']['name'], -3);
      if ($extension != 'cer') {
        $error.="&E1=1";
      }
      else{
        $nombrecer = explode(".", $_FILES['uploadcer']['name']);
        $nombrekey = explode(".", $_FILES['uploadkey']['name']);
        if (!is_dir('cfd')) {
          @mkdir('cfd', 0777);
        }
        if (!is_dir('cfd/'.$_SESSION['emp'])) {
          @mkdir('cfd/'.$_SESSION['emp'], 0777);
        }
        if (!copy($_FILES['uploadcer']['tmp_name'], "cfd/".$_SESSION['emp'].'/'. $_FILES['uploadcer']['name'])) {
          $error.="&E2=1";
        }
        else {
          system('openssl  x509 -inform DER -outform PEM -in cfd/'.$_SESSION['emp'].'/'. $_FILES['uploadcer']['name'] . ' -pubkey >cfd/'.$_SESSION['emp'].'/'.$_GET['rfc'].'.cer.pem');

          if(file_exists('cfd/'.$_GET['id'].'/'.$_GET['rfc'].'.cer.pem')){
            $certok = 1;
            $sql = 'UPDATE empresas SET e_certificado = "'.substr($_FILES['uploadcer']['name'],0, -4).'" 
                    WHERE e_id = "'.$_SESSION['emp'].'"';
            setq($sql) or die($sql);
          }
          else{
            $error.="&E3=1";
          }
        }
      }

      $password=$_POST['password'];
      $target = 'cfd/' . basename($_FILES['uploadkey']['name']);
      //   die($_FILES['uploadkey']['name'].'asd');
      $extension = substr($_FILES['uploadkey']['name'], -3);
      if ($extension != 'key') {
        $error.="&E4=1";
      }
      $nombre = explode(".", $_FILES['uploadkey']['name']);
      if (!is_dir('cfd')) {
        @mkdir('cfd', 0777);
      }
      if (!is_dir('cfd/'.$_SESSION['emp'])) {
        @mkdir('cfd/'.$_SESSION['emp'], 0777);
      }
      if (!copy($_FILES['uploadkey']['tmp_name'], "cfd/".$_SESSION['emp'].'/'. $_FILES['uploadkey']['name'])) {
        $error.="&E5=1";
      }
      else {
        // pkcs8 -inform DER              -in cfd/'.$_FILES['uploadkey']['name'].' -out cfd/'.$model->rfc.'.key.pem -passin pass:"'.$password.'"'
        system('openssl  pkcs8 -inform DER -outform PEM -in cfd/'.$_SESSION['emp'].'/'.$_FILES['uploadkey']['name'].' -out  cfd/'.$_SESSION['emp'].'/'.$_GET['rfc'].'.key.pem -passin pass:"'.$password.'"');
        if(file_exists('cfd/'.$_SESSION['emp'].'/'.$_GET['rfc'].'.key.pem')){
          $keytok = 1;
          if (filesize('cfd/'.$_SESSION['emp'].'/'.$_GET['rfc'].'.key.pem') == 0){ $error.="&E6=1"; }
        }
        else{
          $error.="&E7=1";
        }
      }
      //return($error);
      die($error);
    }
  }

  class viewconfig{
    var $model;
    function __construct($model){
      ?>
      <script>
        function checkguardar(){
          document.getElementById("guardar").innerHTML = "GUARDAR";
          document.getElementById("guardar").disabled = true;
          return true;
        }
      </script>
      <?php
      $this->model = $model;
      $this->nmbestatus = array("A"=>"Empresa Activa","P"=>"Empresa en Pausa","I"=>"Empresa Inactiva");
      $this->colestatus = array("A"=>"success","P"=>"primary","I"=>"danger");
    }
    function edit(){
      $url = 'index';

      $atras = '<a href="?modulo=config&accion='.$url.'" type="button" value="Atrás" class="btn btn-sm btn-warning" ><i class="icon-arrow-left"></i> Atrás</a>';

      toolbar($_GET['modulo'],$atras);

      ?>


      <script>
        function valorusd(){
          var vlusd = document.getElementById("valordolar").innerHTML;
          if(vlusd === "" || vlusd === null){
            $.ajax({
              url : "https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno?token=bb782109cdde04d4733bda39fccaa07169c84c57c946f705abbd02d0d18692a6",
              json : "callback",
              dataType : "json", //Se utiliza JSONP para realizar la consulta cross-site
              success : function(response) {  //Handler de la respuesta
                var series=response.bmx.series;		
                //Se carga una tabla con los registros obtenidos
                for(var i in series) {
                  var serie=series[i];
                  var datosfch = serie.datos;
                  var reg=" "+datosfch[i].fecha+" "+datosfch[i].dato+" ";
                  document.getElementById("fechausd").innerHTML = serie.datos[i].fecha;
                  document.getElementById("valordolar").innerHTML = serie.datos[i].dato;
                }
              }
            });
          }
        }
        function usarUSD(){
          var usd = document.getElementById("valordolar").innerHTML;
          document.getElementById("usd").value = usd;
        }
      </script>
      <?php
          $accion = 'update';
          $titulo = 'Actualizar Datos';
          if(!$this->model->id){
            $accion = 'insert';
            $titulo = 'Agregar empresa';
          }
        echo '
      <div class="main-card mb-3 mt-3 card">
        <form autocomplete="off" name="datosemp" id="datosemp" action="?modulo=config&accion='.$accion.'&id='.$this->model->id.'" method="post" enctype="multipart/form-data" onsubmit="checkguardar();">
          <div class="card-body row">';
            if(!$this->model->pais) $this->model->pais = "MEXICO";
            echo '<div class="col-xl-5 col-md-12 center">
              <div class="">
                <h5 class="card-title p-1">'.$titulo.'</h5>
                <div class="row">
                <div class="col-md-12">
                  <div class="position-relative mb-5">
                    <b><label for="nmb" class="">Nombre de la empresa *</label></b>
                    <input type="text" name="nmb" id="nmb" value="'.$this->model->nmb.'" placeholder="Nombre de la empresa" class="form-control" autofocus>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="position-relative mb-5">
                    <b><label for="tel" class="">Telefono fijo *</label></b>
                    <input type="tel" name="tel" id="tel" value="'.$this->model->tel.'" placeholder="Teléfono fijo" class="form-control" onchange="quitaespaciost();">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="position-relative mb-5">
                    <b><label for="whatsapp" class="">Whatsapp Busines</label></b>
                    <input type="tel" name="whatsapp" id="whatsapp" value="'.$this->model->whatsapp.'" placeholder="Teléfono con whatsapp" class="form-control" onchange="quitaespaciosw();">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="position-relative mb-5">
                    <b><label for="correo" class="">Correo corporativo *</label></b>
                    <input type="email" style="text-transform:lowercase;" name="correo" id="correo" value="'.$this->model->correo.'" placeholder="Correo genérico de la empresa" class="form-control">
                  </div>
                </div>
                <!-- MODAL AYUDA VALOR USD-->
                <div class="modal fade" id="modalUSD" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title" id="exampleModalLabel">Valor del Dólar</h5>
                      </div>
                      <div class="modal-body">
                        <div class="col-12">
                          <div class="table-responsive">
                            <center>
                              <table width="100%" class="mb-0 table table-hover">
                                <thead>
                                  <tr>
                                    <th width="35%">Fecha de registro</th>
                                    <th width="35%">Valor</th>
                                    <th width="30%"></th>
                                  </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tr>
                                  <td><label id="fechausd"></label></td>
                                  <td><label id="valordolar"></label></td>
                                  <td>
                                    <button class="btn btn-success" type="button" onclick="usarUSD();" data-dismiss="modal">
                                      <i class="fa fa-check"></i> Usar
                                    </button>
                                  </td>
                                </tr>
                              </table>
                            </center>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-5">
                    <label><b>Valor USD * </b> <a data-bs-toggle="modal" data-target="#modalUSD" onclick="valorusd();"><i class="icon-question"></i></a></label>
                    <input type="number" value="'.$this->model->usd.'" id="usd" class="form-control" name="usd" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-5">
                    <label><b>IVA * </b></label>
                    <input type="number" step="0.01" value="'.$this->model->iva.'" id="iva" class="form-control" name="iva" required>
                  </div>
                </div>
                <div class="col-md-10 ">
                  <div class="position-relative mb-5">
                    <b><label for="logo" class="">Logotipo de la empresa</label></b>';
                    if(empty($this->model->logo) && !file_exists($this->model->logo)){
                      echo '<input type="file" name="logo" id="logo" value="'.$this->model->correofac.'" placeholder="Elija la imagen de su logotipo" class="form-control"  accept="image/*"></div></div>';
                      echo '<div class="col-md-2 text-xs-center">
                        <b><label for="logo" class="">Caracteristicas</label></b>
                        <button type="button" class="btn btn-secondary" data-toggle="popover" data-placement="top" data-container="body"
                          data-original-title="Característica de la imagen" data-content="- Imagen en formato PNG o JPG, Resolución recomendada 480X300, Fondo blanco o con transparencia, Si tu logotipo es cuadrado, se recomienda poner margenes blancos para cumplir con la resolución">
                          <i class="icon-question2"></i>
                        </button>
                      </div>';
                    }else{
                      echo '<img src="'.$this->model->logo.'" class="img-thumbnail" ></div></div>';
                      echo '<div class="col-md-2 text-xs-center">
                        <a href="?modulo=config&accion=dellogo&empresa='.$this->model->id.'">
                          <button type="button" class="btn btn-danger">
                            <i class="fa fa-trash"></i>
                          </button>
                        </a>
                      </div>';
                    }
                  echo '</div>
                </div>';
                echo '<div class="col-md-6">
                  <div class="position-relative mb-5">
                    <b><label for="correofac" class="">Correo para envio de facturas *</label></b>
                    <input type="email" style="text-transform:lowercase;" name="correofac" id="correofac" value="'.$this->model->correofac.'" placeholder="Correo para facturación" class="form-control">
                  </div>';
                  if($this->model->host == "tls://smtp.gmail.com:587"){
                    $gmailcorp = "checked";
                    $display = "block";
                    $readonly = "readonly";
                    $disabled = "disabled";
                    if($this->model->seguridad == "tls") $tls = "selected";
                    elseif($this->model->seguridad == "ssl") $ssl = "selected";
                    else $ninguna = "selected";
                  }elseif($this->model->host == "tls://smtp.office365.com:587"){
                    $outlookcorp = "checked";
                    $display = "block";
                    $readonly = "readonly";
                    $disabled = "disabled";
                    if($this->model->seguridad == "tls") $tls = "selected";
                    elseif($this->model->seguridad == "ssl") $ssl = "selected";
                    else $ninguna = "selected";
                  }elseif($this->model->host == ""){
                    $display = "none";
                  }else{
                    $servidorcorp = "checked";
                    $display = "block";
                    $readonly = "";
                    $disabled = "";
                    if($this->model->seguridad == "tls") $tls = "selected";
                    elseif($this->model->seguridad == "ssl") $ssl = "selected";
                    else $ninguna = "selected";
                  }
                echo '</div>
                <div class="col-md-6">
                  <div class="mb-5">
                    <div class="mb-5">
                      <label><b>Proveedor de Correo de Facturación *<a data-bs-toggle="modal" data-target="#exampleModalAYUDA"><i class="icon-question"></i></a></b></label>
                      <div class="input-group" required>
                        <label class="display-inline-block custom-control custom-radio ml-1">
                          <input type="radio" name="provco" '.$gmailcorp.' onchange="gmailfac();" class="custom-control-input">
                          <span class="custom-control-indicator"></span>
                          <span class="custom-control-description ml-0">Gmail</span>
                        </label>
                        <label class="display-inline-block custom-control custom-radio" >
                          <input type="radio" name="provco" '.$outlookcorp.' onchange="outlookfac();"  class="custom-control-input">
                          <span class="custom-control-indicator"></span>
                          <span class="custom-control-description ml-0">Outlook</span>
                        </label>
                        <label class="display-inline-block custom-control custom-radio" >
                          <input type="radio" name="provco" '.$servidorcorp.' onchange="servidorfac();" class="custom-control-input">
                          <span class="custom-control-indicator"></span>
                          <span class="custom-control-description ml-0">Servidor de E-mail</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="mb-5" id="condicionescorreo" style="display: '.$display.' ">
                    <div class="mb-5">
                      <label for="">Host: *</label>
                      <input class="form-control" name="host" value="'.$this->model->host.'" type="text" '.$readonly.'>
                      <label for=""> Puerto: *</label>
                      <input type="number" name="port" class="form-control" value="'.$this->model->puerto.'" '.$readonly.' >
                      <label for=""> Tipo de Seguridad: *</label> 
                      <select name="seguridad" class="form-control" '.$readonly.' '.$disabled.'> 
                        <option value="0" '.$ninguna.' >Ninguna</option> 
                        <option value="tls" '.$tls.' >tls</option> 
                        <option value="ssl" '.$ssl.' >ssl</option>
                      </select>
                      <label for="">Contraseña: *</label>
                      <input type="password" id="correopas" class="form-control" name="correopas" data-toggle="tooltip" data-placement="top" title="Sensible a Mayusculas, Minusculas, números y signos" value="'.base64_decode($this->model->correopas).'" required>
                      <button id="show_password" class="btn btn-primary" type="button" onclick="mostrarPasswordCorp2()"> <span class="fa fa-eye"></span> </button> <br> 
                      <button type="button" data-toggle="tooltip" data-placement="top" title="Asegurate que la información haya sido guardada antes de comprobar los datos" onclick="comprobarfac();" class="btn btn-success mt-1"><i class="icon-check"></i> Comprobar</button>
                    </div>
                  </div>
                </div>';
              echo '</div>';
              echo '<!-- MODAL AYUDACORP-->
              <div class="modal fade" id="exampleModalAYUDA" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Configuracion Correo Facturación</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-1" style="font-size: small;">
                          El correo para envío de facturas funciona para realizar envios por correo electronico de manera automatica en el modulo de facturación del sistema JD CEO.
                          Por ello es importante tener configurado este apartado. 
                          Se cuenta con 3 tipos de configuraciones:
                      </div>
                      <button class="btn btn-primary btn-sm mb-1" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        Información Para Gmail
                      </button>
                      <button class="btn btn-primary btn-sm mb-1" type="button" data-toggle="collapse" data-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample">
                        Información Para Outlook
                      </button>
                      <button class="btn btn-primary btn-sm mb-1" type="button" data-toggle="collapse" data-target="#collapseExample3" aria-expanded="false" aria-controls="collapseExample">
                        Información Para Servidor
                      </button>
                      <div class="collapse" id="collapseExample">
                        <div class="p-1 card-body" style="font-size: small;">
                        <h4>Gmail</h4>
                          <ul>
                            <li>Activa la verificación en dos pasos para tu cuenta de gmail desde <a target="_blank" href="https://myaccount.google.com/u/2/signinoptions/two-step-verification/enroll-welcome?hl=es">aquí</a>.</li>
                            <li>Una vez configurada la verificación en dos pasos, genera una contraseña para aplicación desde <a target="_blank" href="https://myaccount.google.com/apppasswords?rapt=AEjHL4MYMudlCbNT3QS73VGi3I5Eg-Dd0eNl_oGn7uDFWidx7h7IISO3XhYLx_KTqNr1mWxRxJ0dNiCZMF4cl8QC1-4AhBgeTw">aquí</a>.</li>
                            <li>Seleccionar "Correo electrónico" y en dispositivo "Otra" para personalizar el nombre a guardar.</li>
                            <li>Al pulsar en "Generar" se crea una contraseña de 16 digitos la cual se utilizará en el campo de "Contraseña" de la configuración del correo para envío de facturas.</li>
                            <li>Pulsar en "Comprobar" para verificar el correcto funcionamiento del correo</li>
                          </ul>
                        </div>
                      </div>
                      <div class="collapse" id="collapseExample2">
                        <div class="p-1  card-body" style="font-size: small;">
                        <h4>Outlook</h4>
                          <ul>
                            <li>Completa los campos de correo para envío de facturas con tu direccion de correo electronico de de outlook(live, hotmail).</li>
                            <li>Una vez guardados presiona el boton "Comprobar"</li>
                            <li>Dirigete al apartado de seguridad de outlook desde <a target="_blank" href="https://account.live.com/Activity?mkt=es-MX&refd=account.microsoft.com&refp=security">aqui</a>.</li>
                            <li>En actividad inusal se mostrará una advertencia de sincronización automatica desde la ip "162.241.92.192". Autorizar uso pulsando en "Fui yo"</li>
                            <li>Pulsar nuevamente el boton "Comprobar" de JD CEO</li>
                          </ul>
                        </div>
                      </div>
                      <div class="collapse" id="collapseExample3">
                        <div class="p-1 card-body" style="font-size: small;">
                        <h4>Servidor</h4>
                          <ul>
                            <li>Si cuentas con un servicio de correo electronico proveído por un hostin, debes llenar los datos con la información proporcionada desde tu servidor.</li>
                          </ul>
                        </div>
                      </div>

                    </div>
                    <div class="modal-footer">
                      <!-- 
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" class="btn btn-primary">Save changes</button> -->
                    </div>
                  </div>
                </div>
              </div>';
    if($this->model->id){
      echo '<div class="col-xl-7 col-md-12 center">
        <div class="">
          <h5 class="card-title p-1">Datos fiscales</h5>
          <div class="row">
            <div class="col-md-12">
              <div class="position-relative mb-5">
                <b><label for="razons" class="">Razon Social *</label></b>
                <input type="text" name="razons" id="razons" value="'.$this->model->razons.'" placeholder="Razón Social" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="position-relative mb-5">
                <b><label for="rfc" class="">RFC *</label></b>
                <input type="text" name="rfc" id="rfc" value="'.$this->model->rfc.'" placeholder="RFC" class="form-control text-uppercase">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-5">
                <label><b>ACRÓNIMO FOLIO * </b></label>
                <input type="text" value="'.$this->model->siglas.'" id="iva" class="form-control" name="siglas" required>
              </div>
            </div>
            <div class="col-md-8">
              <div class="position-relative mb-5">
                <b><label for="id" class="">Calle *</label></b>
                <input type="text" name="calle" id="calle" value="'.$this->model->calle.'" placeholder="Calle" class="form-control">
              </div>
            </div>
            <div class="col-md-3">
              <div class="position-relative mb-5">
                <b><label for="nume" class="">Número exterior *</label></b>
                <input type="text" name="nume" id="nume" value="'.$this->model->nume.'" placeholder="Número exterior" class="form-control">
              </div>
            </div>
            <div class="col-md-3">
              <div class="position-relative mb-5">
                <b><label for="numi" class="">Número interior *</label></b>
                <input type="text" name="numi" id="numi" value="'.$this->model->numi.'" placeholder="Número interior" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="position-relative mb-5">
                <b><label for="colonia" class="">Colonia *</label></b>
                <input type="text" name="colonia" id="colonia" value="'.$this->model->colonia.'" placeholder="Colonia" class="form-control">
              </div>
            </div>
            <div class="col-md-9">
              <div class="position-relative mb-5">
                <b><label for="municipio" class="">Municipio/Ciudad *</label></b>
                <input type="text" name="municipio" id="municipio" value="'.$this->model->ciudad.'" placeholder="Ciudad/Municipio" class="form-control">
              </div>
            </div>
            <div class="col-md-3">
              <div class="position-relative mb-5">
                <b><label for="cp" class="">Código postal *</label></b>
                <input type="number" name="cp" id="cp" value="'.$this->model->cp.'" placeholder="CP" class="form-control">
              </div>
            </div>
            <div class="col-md-7">
              <div class="position-relative mb-5">
                <b><label for="estado" class="">Estado *</label></b>
                <input type="text" name="estado" id="estado" value="'.$this->model->estado.'" placeholder="Estado" class="form-control">
              </div>
            </div>
            <div class="col-md-5">
              <div class="position-relative mb-5">
                <b><label for="pais" class="">Pais *</label></b>
                <input type="text" name="pais" id="pais" value="'.$this->model->pais.'" placeholder="Pais" class="form-control">
              </div>
            </div>
            <div class="col-md-12">
              <div class="position-relative mb-5">
                <b><label for="regimen" class="">Regimen fiscal *</label></b>
                <select id="pref-orderby" class="form-control" name="grupo" placeholder="Grupo" required>
                  <option value="">Elige tu regimen fiscal</option>';
          $sqlr = 'SELECT * FROM cfdi_regimenfiscal ORDER BY cr_regimen';
          $resultr = setq($sqlr);
          while($rowr = $resultr->fetch_array()){
            if($this->model->regimen == $rowr['cr_regimen']) $sel = "selected"; else $sel = "";
            echo '<option value="'.$rowr['cr_regimen'].'" '.$sel.' >'.$rowr['cr_regimen'].' '.$rowr['cr_descripcion'].'</option>';
          }
          echo '</select>
              </div>
            </div>';
            echo '
            <div class="offset-md-1 col-md-8 text-xs-center">
              <button id="guardar" class="mt-1 mb-1 btn btn-success"><i class="fa fa-save"></i> Guardar</button>
            </div>
            </form>
            ';

            if($this->model->rfc != "" || $this->model->rfc != NULL){
              echo '<form action="?modulo=config&accion=uploadcer&empresa='.$_GET['empresa'].'&rfc='.$this->model->rfc.'" id="form2" method="POST"  enctype="multipart/form-data">';
              echo '
              <div class="col-md-6">
                <div class="mb-5">
                  <label><b>Certificado de Facturación: </b></label>
                  <input type="file" name="uploadcer" id="uploadcer" value="" class="form-control"  accept=".cer" >
                </div>
                <div class="mb-5">
                  <label><b>Llave de Facturación: </b></label>
                  <input type="file" name="uploadkey" id="uploadkey" value="" class="form-control"  accept=".key" >
                  <label><b>Contraseña: </b></label>
                  <input type="password" class="form-control" name="password" >
                  <button class="btn btn-success mt-1"> Subir</button>
                </div>
              </form>
              </div>


          </div>
        </div>
      </div>';
    }

    echo '
  </div>

  ';


echo '

  <script>
    function subform(){
      document.getElementById("form2").submit();
    }
  </script>

    ';

    echo '
  
    <script>
    function quitaespaciost(){
      original = document.getElementById("tel").value;
      var x;
      var sCadenaSinBlancos = "";
      for (x=0; x < original.length; x++) {
        if (original.charAt(x) != " ")
        sCadenaSinBlancos+=original.charAt(x);
      }
      document.getElementById("tel").value = sCadenaSinBlancos;
    }
    function quitaespaciosw(){
      original = document.getElementById("whatsapp").value;
      var x;
      var sCadenaSinBlancos = "";
      for (x=0; x < original.length; x++) {
        if (original.charAt(x) != " ")
        sCadenaSinBlancos+=original.charAt(x);
      }
      document.getElementById("whatsapp").value = sCadenaSinBlancos;
    }
   </script>
    ';
  }

  }

  function datos(){

    toolbar($_GET['modulo']);

    echo '
    <div class="card mt-3">';
    echo '
    <div class="card-body">
    <div class="table-responsive">
    <table class="table table-hover table-striped table-bordered rounded">
    <thead class="thead-primary">
    <tr>
    <th colspan="4">Datos de Empresa</th>
    </tr>
    </thead>
    ';
    echo '
    <thead class="thead-dark ">
    <tr>
    <th>Nombre</th>
    <th>Estatus</th>
    <th>Editar</th>
    </tr>
    </thead>';
    while($row = $this->model->result->fetch_array()){
      echo '<tr>
      <td>'.$row['e_nmb'].'</td>
      <td>
        <div class="alert alert-'.$this->colestatus[$row['e_estatus']].' no-border" role="alert">
          '.$this->nmbestatus[$row['e_estatus']].'
        </div>
      </td>
      <td>
        <a href="?modulo=config&accion=edit&empresa='.$row['e_id'].'">
          <button type="button" class="btn btn-secondary" /><i class="icon-edit2"></i> Ver o cambiar</button>
        </a>
      </td>
      </tr>';
      echo '
      <div class="card">
        <div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped">
              <thead class="bg-light-blue bg-darken-2 text-white">
                <tr>
                  <th colspan="2">Datos Empresa:</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Correo:</td>
                  <td>'.$row['e_correo'].'</td>
                </tr>
                <tr>
                  <td>Telefono: </td>
                  <td>'.$row['e_tel'].'</td>
                </tr>
                <tr>
                  <td>WhatsApp: </td>
                  <td>'.$row['e_whatsapp'].'</td>
                </tr>
                <tr>
                  <td>RFC: </td>
                  <td>'.$row['e_rfc'].'</td>
                </tr>
                <tr>
                  <td>Direccion:</td>
                  <td><div>Calle: '.$row['e_calle'].'  <br>
                  Número EXT: '.$row['e_nume'].' <br>
                  Número INT: '.$row['e_numi'].' <br>
                  Colonia: '.$row['e_colonia'].' <br>
                  CP: '.$row['e_cp'].' <br>
                  Ciudad: '.$row['e_ciudad'].' <br>
                  Estado: '.$row['e_estado'].' <br>
                  País: '.$row['e_pais'].' <br> </div></td>
                </tr>
                <tr>
                  <td>Logo empresarial: </td>
                  <td><img class="img-fluid" style="max-width: 300px;" src="'.$row['e_logo'].'"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>';
    }
    echo '</table>
    </div>';

  }
}
?>