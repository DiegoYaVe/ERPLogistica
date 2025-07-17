<?php
  /* ini_set('display_errors',1); */
  class correosop{
    var $model;
    var $view; 
    function __construct(){
      $this->model = new modelcorreosop(isset($obj));
    }
    function index(){
      if(!isset($_GET['w']))
        $_GET['w'] = 1;
      $this->model->result($_GET['w']);
      $this->view = new viewcorreosop($this->model);
      $this->view->browse($_GET['w']);
    }
    function edit(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new viewcorreosop($this->model);
      $this->view->edit($_GET['w']);
    }
    function update(){
      $this->model->setdata($_POST['id'], $_POST['categoria'], $_POST['titulo'], $_POST['nmb'],$_POST['mail'], $_POST['pass'],$_POST['descripcion'],$_POST['ccp1'],$_POST['ccp2'],$_POST['ccp3'],$_POST['ccp4'],$_POST['ccp5'],$_POST['ccp6'],$_POST['ccp7'],$_POST['ccp8'],$_POST['ccp9'],$_POST['sms'],$_POST['txtsms'],$_POST['mauto'],$_POST['claveb']);
      $this->model->update($_POST['id']);
      redirect('?modulo=correosop&accion=index');
    }
    function insert(){
      $this->model->setdata($_POST['id'], $_POST['categoria'], $_POST['titulo'], $_POST['nmb'],$_POST['mail'],$_POST['pass'],$_POST['descripcion'],$_POST['ccp1'],$_POST['ccp2'],$_POST['ccp3'],$_POST['ccp4'],$_POST['ccp5'],$_POST['ccp6'],$_POST['ccp7'],$_POST['ccp8'],$_POST['ccp9'],$_POST['sms'],$_POST['txtsms'],$_POST['mauto'],$_POST['claveb']);
      $this->model->insert();
      redirect('?modulo=correosop&accion=index');
    }
    function categoria(){
      $this->model->resultcategoria();
      $this->view = new viewcorreosop($this->model);
      $this->view->browsecategoria();
    }
    function editcategoria(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      $this->model->selectcategoria($_GET['id']);
      $this->view = new viewcorreosop($this->model);
      $this->view->editcategoria();
    }
    function updatecategoria(){
      $this->model->setdatacategoria($_POST['id'], $_POST['nmb'], $_POST['estatus']);
      $this->model->updatecategoria($_POST['id']);
      redirect('?modulo=correosop&accion=categoria');
    }
    function insertcategoria(){
      $this->model->setdatacategoria($_POST['id'], $_POST['nmb'], $_POST['estatus']);
      $this->model->insertcategoria();
      redirect('?modulo=correosop&accion=categoria');
    }
    function show(){
      if(!isset($_GET['id']))
        $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new viewcorreosop($this->model);
      $this->view->show();
    }
    function editcuenta(){
      $this->model->selectcuenta($_GET['id']);
      $this->view  = new viewcorreosop($this->model);
      $this->view->showcuenta();
    }
    function updatecuenta(){
      $sql = 'UPDATE cuentasfp SET
              c_nmb = "'.$_POST['nombre'].'",
              c_descripcion = "'.str_replace('"','\"',$_POST['descripcion']).'"
              WHERE c_id = "'.$_GET['id'].'"';
      setq($sql);
      redirect('?modulo=correosop&accion=editcuenta&id='.$_GET['id'].'');
    }
  }

  class modelcorreosop{
    function result(){
      $sql = 'SELECT * FROM correosop ORDER BY c_id ASC';
      $this->result = setq($sql);
    }
    function select($correo){
      $sql = 'SELECT * FROM correosop WHERE c_id="'.$correo.'"';
      $result = setq($sql) or die($sql);
      $row = $result->fetch_array();
      $this->id = $row['c_id'];
      $this->mail = $row['c_mail'];
      $this->pass = $row['c_pass'];
      $this->nmb = $row['c_nmb'];
      $this->titulo = $row['c_titulo'];
      $this->descripcion = $row['c_descripcion'];
      $this->categoria = $row['c_categoria'];
      $this->ccp1 = $row['c_ccp1'];
      $this->ccp2 = $row['c_ccp2'];
      $this->ccp3 = $row['c_ccp3'];
      $this->ccp4 = $row['c_ccp4'];
      $this->ccp5 = $row['c_ccp5'];
      $this->ccp6 = $row['c_ccp6'];
      $this->ccp7 = $row['c_ccp7'];
      $this->ccp8 = $row['c_ccp8'];
      $this->ccp9 = $row['c_ccp9'];
      $this->ccp10 = $row['c_ccp10'];
      $this->sms = $row['c_sms'];
      $this->mauto = $row['c_automatico'];
      $this->txtsms = $row['c_textsms'];
      $this->claveb = $row['c_claveb'];
    }
    function setdata($id,$categoria,$titulo,$nmb,$mail,$pass,$descripcion,$ccp1,$ccp2,$ccp3,$ccp4,$ccp5,$ccp6,$ccp7,$ccp8,$ccp9,$sms,$txtsms,$mauto,$claveb){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","=","?","¡","*","+","~","^","[","°","|");
      $descripcion = str_replace('"','\"',$descripcion);
      //$simbol = array('"',"'");
      //$cambio = array('\"',"");
      $cambio = "";
      $this->nmb = str_replace($simbol,$cambio, mb_strtoupper(trim($nmb)));
      $this->id = $id;
      $this->mail = $mail;
      $this->pass = $pass;
      $this->categoria = mb_strtoupper($categoria);
      $this->titulo = $titulo;
      $this->descripcion = trim($descripcion);
      $this->ccp1 = $ccp1;
      $this->ccp2 = $ccp2;
      $this->ccp3 = $ccp3;
      $this->ccp4 = $ccp4;
      $this->ccp5 = $ccp5;
      $this->ccp6 = $ccp6;
      $this->ccp7 = $ccp7;
      $this->ccp8 = $ccp8;
      $this->ccp9 = $ccp9;
      $this->txtsms = $txtsms;
      $this->claveb = $claveb;
      $this->sms = 0;
      if($sms)
        $this->sms = 1;
      $this->mauto = 0;
      if($mauto)
        $this->mauto = 1;
    }
    function update($id){
      /* $passmail = 'SELECT c_pass FROM correosop WHERE c_id = "'.$id.'"';
      $resultpass = setq($passmail);
      list($pass) = $resultpass->fetch_array();

      $passmail2 = 'SELECT PASSWORD("' . $this->pass . '")';
      $resultpass2 = setq($passmail2);
      list($pass2) = $resultpass2->fetch_array();

      if($pass == $this->pass || $pass2 == $pass){
        $password = $pass;
      } else{
        $password = $pass2;
      } */
      $sql = 'UPDATE correosop SET
      c_nmb = "'.$this->nmb.'",
      c_categoria = "'.$this->categoria.'",
      c_descripcion = "'.$this->descripcion.'",
      c_titulo = "'.$this->titulo.'",
      c_mail = "'.$this->mail.'",
      c_pass = "'.$this->pass.'",
      c_textsms = "'.$this->txtsms.'",
      c_ccp1 = "'.$this->ccp1.'",
      c_ccp2 = "'.$this->ccp2.'",
      c_ccp3 = "'.$this->ccp3.'",
      c_claveb = "'.$this->claveb.'",
      c_sms = "'.$this->sms.'",
      c_automatico = "'.$this->mauto.'"
      WHERE c_id = "'.$id.'"';
      setq($sql);
      //mysqli_query($sql,$conexionw) or die($sql.mysql_error());
    }
    function insert(){
      /* $passmail = 'SELECT PASSWORD("' . $this->pass . '")';
      $resultpass = setq($passmail);
      list($pass) = $resultpass->fetch_array(); */

      $sqlm = 'SELECT MAX(c_id) FROM correosop';
      $result = setq($sqlm);
      list($this->id) = $result->fetch_array();
      $this->id++;
    
      $sql = 'INSERT INTO correosop SET
              c_id = "'.$this->id.'",
              c_nmb = "'.$this->nmb.'",
              c_categoria = "'.$this->categoria.'",
              c_descripcion = "'.$this->descripcion.'",
              c_titulo = "'.$this->titulo.'",
              c_mail = "'.$this->mail.'",
              c_pass = "'.$this->pass.'",
              c_textsms = "'.$this->txtsms.'",
              c_ccp1 = "'.$this->ccp1.'",
              c_ccp2 = "'.$this->ccp2.'",
              c_ccp3 = "'.$this->ccp3.'",
              c_sms = "'.$this->sms.'",
              c_claveb = "'.$this->claveb.'",
              c_automatico = "'.$this->mauto.'"';
      setq($sql);
      //mysql_query($sql,$conexionw) or die($sql);
    }
    function resultcategoria(){
      $sql = 'SELECT * FROM catcorreosop ORDER BY c_id ASC';
      //$this->result = mysql_query($sql,$conexionw) or die($sql);
      $this->result = setq($sql);
    }
    function selectcategoria($correo){
      $sql = 'SELECT * FROM catcorreosop WHERE c_id="'.$correo.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['c_id'];
      $this->nmb = $row['c_nmb'];
      $this->estatus = $row['c_estatus'];
    }
    function setdatacategoria($id,$nmb,$estatus){
      mb_internal_encoding("UTF-8");
      $this->id = $id;
      $this->nmb = mb_strtoupper($nmb);
      $this->estatus = $estatus;
    }
    function updatecategoria($id){
      $sql = 'UPDATE catcorreosop SET
      c_estatus = "'.$this->estatus.'",
      c_nmb = "'.$this->nmb.'"
      WHERE c_id = "'.$id.'"';
      //mysql_query($sql,$conexionw) or die($sql);
      setq($sql);
    }
    function insertcategoria(){
      $sql = 'SELECT MAX(c_id) FROM catcorreosop';
      //$result = mysql_query($sql) or die($sql);
      //list($this->id) = mysql_fetch_array($result);
      $result = setq($sql);
      list($this->id) = $result->fetch_array();
      $this->id++;
    
      $sql = 'INSERT INTO catcorreosop SET
      c_id = "'.$this->id.'",
      c_nmb = "'.$this->nmb.'",
      c_estatus = "'.$this->estatus.'"';
      //mysql_query($sql,$conexionw) or die($sql);
      setq($sql);
    }
    function selectcuenta($id){
      $sql = 'SELECT * FROM cuentasfp WHERE c_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['c_id'];
      $this->formap = $row['c_formap'];
      $this->nmb = $row['c_nmb'];
      $this->descripcion = $row['c_descripcion'];
      $this->limite = $row['c_limite'];
      $this->montou = $row['c_montou'];
      $this->estatus = $row['c_estatus'];
      $this->udig = $row['c_udig'];
      $this->factura = $row['c_factura'];
      $this->orden = $row['c_orden'];
      $this->adjunto = $row['c_adjunto'];
      $this->updated = $row['c_updated'];
      $this->cuentav = $row['c_cuentav'];
    }
  }

  class viewcorreosop{
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->model->aw = array("1"=>"JD SHOP","2"=>"JD SUITE","3"=>"ACADEMY JD SHOP","4"=>"JD CEO","5"=>"JD MARKETING");
      ?>
      <script>
        /*tinymce.init({
          selector:'textarea',
          auto_focus: 'element1',
          plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table contextmenu directionality',
            'emoticons template paste textcolor colorpicker textpattern imagetools'
          ],
          toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
          toolbar2: 'print preview | forecolor backcolor emoticons | fontselect fontsizeselect'
        });*/
      </script>
      <?php
    }
    function browse(){
      /* echo '</div> */
        $nuevo = '<a href="?modulo=correosop&accion=edit" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Nuevo</a>';
        $categorias = '<a href="?modulo=correosop&accion=categoria" type="button" class="btn btn-sm btn-success"><i class="fas fa-sort-amount-down-alt"></i> Categorias</a>';
        toolbar($_GET['modulo'], '','',$nuevo, $categorias);

        echo '
          <div class="card mt-3">
          <div class="card-body">
          <h3>Correos</h3>
            <div class="table-responsive">
              <table width="90%" class="mb-0 table table-hover table-striped" id="myTable">
                <thead class="bg-light-blue bg-darken-2">
                <tr>
                  <th class="p-1">Id</th>
                  <th class="p-1">Categoria</th>
                  <th class="p-1">Asunto</th>
                  <th class="p-1">Acción</th>
                  <th class="p-1">Correo</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>';
              echo '</tbody>
            </table>
          </div></div>
        ';

        echo '
        <script>
        function mandar(){
          $("#myTable").DataTable().clear().draw();
          $("#myTable").DataTable().destroy();
    
          $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            processing: true,
            serverside: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            ajax: {
              url: "query/datatablecorreosop.php",
              type: "POST",
              datatype: "json",
              data:{ 
                "busca": "index"
              }
            },
            pageLength: "50",
            responsivePriority: 1,
            columnDefs: [
              { type: "num", targets: 0 } // Especificar que la primera columna es de tipo numérico
            ],
            order: [[0, "asc"]] 
          });
        }
        
        mandar();
      </script>
      ';
    }
    function edit(){
      $sqlc = 'SELECT * FROM catcorreosop WHERE c_estatus = "A"';
      $resultc = setq($sqlc);
      $accion = 'insert';
      if (isset($this->model->id)){
        $accion = 'update';
      }
      ?>
      <script language="JavaScript">
        function checkSubmitedit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
      </script>  
      <script src="assets/js/tinymce/tinymce.min.js"></script>

      <script>
       
       tinymce.init({
          selector: '#cuerpo',
          plugins: [
              'a11ychecker', 'advlist', 'advcode', 'advtable', 'autolink', 'checklist', 'export',
              'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks',
              'powerpaste', 'fullscreen', 'formatpainter', 'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample', 'code'
          ],
          content_css: [
              "https://fonts.googleapis.com/css2?family=Josefin+Sans&display=swap",
          ],
          toolbar: 'undo redo | formatpainter casechange blocks | bold italic backcolor | ' +
              'alignleft aligncenter alignright alignjustify | ' +
              'bullist numlist checklist outdent indent | removeformat | a11ycheck code table help | codesample | code | nombrecliente | ' +
              'emailcliente | elementonombre | elementofolio | elementofecha | fechapedido | listaproductos | ptotal | recibopago | direnviopedido | detallepagonoacreditado | nombrevendasignado | ',
          setup: function (editor) {
              editor.ui.registry.addButton('nombrecliente', {
                  text: 'Nombre cliente',
                  onAction: function (_) {
                      editor.insertContent('--%nombrecliente%--');
                  }
              });
              editor.ui.registry.addButton('emailcliente', {
                  text: 'Email cliente',
                  onAction: function (_) {
                      editor.insertContent('--%emailcliente%--'); 
                  }
              });
              
              editor.ui.registry.addButton('fechapedido', {
                  text: 'Fecha pedido',
                  onAction: function (_) {
                      editor.insertContent('--%fechapedido%--'); 
                  }
              });
              editor.ui.registry.addButton('listaproductos', {
                  text: 'Lista productos',
                  onAction: function (_) {
                      editor.insertContent('--%listaproductos%--'); 
                  }
              });
              editor.ui.registry.addButton('ptotal', {
                  text: 'Total pedido',
                  onAction: function (_) {
                      editor.insertContent('--%ptotal%--'); 
                  }
              });
              editor.ui.registry.addButton('socioenviopedido', {
                  text: 'Socio repartidor',
                  onAction: function (_) {
                      editor.insertContent('--%socioenviopedido%--');
                  }
              });
              editor.ui.registry.addButton('direnviopedido', {
                  text: 'Dirección de envio',
                  onAction: function (_) {
                      editor.insertContent('--%direnviopedido%--'); 
                  }
              });
              editor.ui.registry.addButton('recibopago', {
                  text: 'Recibo de pago',
                  onAction: function (_) {
                      editor.insertContent('--%recibopago%--'); 
                  }
              });
              editor.ui.registry.addButton('detallepagonoacreditado', {
                  text: 'Detalle pago no acreditado',
                  onAction: function (_) {
                      editor.insertContent('--%detallepagonoacreditado%--'); 
                  }
              });
              editor.ui.registry.addButton('nombrevendasignado', {
                text: 'Nombre vendedor',
                  onAction: function (_) {
                      editor.insertContent('--%nombrevendasignado%--'); 
                  }
              });
              editor.ui.registry.addButton('mailcontacto', {
                text: 'Email contacto',
                  onAction: function (_) {
                      editor.insertContent('--%mailcontacto%--'); 
                  }
              });

              editor.ui.registry.addButton('elementonombre', {
                  text: 'Nombre operacion',
                  onAction: function (_) {
                      editor.insertContent('--%elementonombre%--');
                  }
              });
              editor.ui.registry.addButton('elementofolio', {
                  text: 'Folio operacion',
                  onAction: function (_) {
                      editor.insertContent('--%elementofolio%--');
                  }
              });
              editor.ui.registry.addButton('elementofecha', {
                  text: 'Fecha operacion',
                  onAction: function (_) {
                      editor.insertContent('--%elementofecha%--');
                  }
              });
          }
      });

      </script>
      <?php
       $atras = '
       <a type=button value="" onClick="javascript:history.go(-1);" id="atras" class="btn btn-sm btn-warning text-white">
         <i class="fa fa-arrow-left"></i> Atras
       </a>';
      /* echo '</div>     */  
      toolbar($_GET['modulo'], $atras, '', '', '');

      echo '
      <div class="col-xs-12 mt-1">
        <div class="card">';
          echo '<div class="card-header">
            <h4 class="card-title"> Agregar Correos </h4>
            <div class="heading-elements" bis_skin_checked="1">
              <ul class="list-inline mb-0">
                <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                <li><a data-action="reload"><i class="icon-reload"></i></a></li>
                <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                <li><a data-action="close"><i class="icon-cross2"></i></a></li>
              </ul>
            </div>
          </div> 
          <div class="card-body in">
            <form method="post" action="?modulo=correosop&accion='.$accion.'" onsubmit="return checkSubmitedit();">
              <div class="table-responsive">
                <table class="table bg-white table-bordered mt-3">
                  <tbody>';
                    if ($accion=='update'){
                      echo '<tr>
                        <th>ID</th>
                        <td>
                          <input type="hidden" name="id" value="'.$this->model->id.'">                          
                          <input value="'.$this->model->id.'" class="form-control" readonly>
                        </td>';
                    }
                    else {
                      echo '<tr>
                        <th>ID</th>
                        <td>
                          <input type="hidden" name="id" maxlength="3" value="'.$this->model->id.'">
                        </td>';
                    }
                    echo '<th class="">Categoría</th>
                    <td>
                      <select name="categoria" id="categoria" class="form-control" required>
                        <option value="" selected>Selecciona una categoría</option>';
                        while($rwc = mysqli_fetch_array($resultc)){
                          $sel = '';
                          if($this->model->categoria == $rwc['c_id'])
                            $sel = 'selected';
                          echo '<option value="'.$rwc['c_id'].'" '.$sel.'>'.$rwc['c_nmb'].'</option>';
                        }
                      echo '</select>
                    </td>
                    </tr>
                    <tr>
                      <th>Correo</th>
                      <td>
                        <input type="email" placeholder="Escribe un correo electrónico" name="mail" class="form-control" value="'.$this->model->mail.'" maxlength="80" required/>
                      </td>
                      <th>Contraseña</th>
                      <td>
                        <input type="password" name="pass" class="form-control" value="'.$this->model->pass.'" maxlength="70" required/>
                      </td>
                    </tr>';
                    echo '<tr>
                      <th>Acción</th>
                      <td>
                        <input type="text" name="nmb" class="form-control" value="'.$this->model->nmb.'" maxlength="100" required/>
                      </td>
                      <th>Clave de bitácota</th>
                      <td>
                        <input type="text" name="claveb" class="form-control" value="'.$this->model->claveb.'" maxlength="5" required/>
                      </td>
                    </tr>';
                    echo '<tr>
                      <th class="p-1" colspan="4">C.C.P.</th>
                    </tr>
                    <tr>
                      <td colspan="4">
                        <div class="row">
                          <div class="col-4">
                            <input type="email" placeholder="C.C.P." name="ccp1" class="form-control" value="'.$this->model->ccp1.'"/> 
                          </div>
                          <div class="col-4">
                            <input type="email" placeholder="C.C.P." name="ccp2" class="form-control" value="'.$this->model->ccp2.'"/>
                          </div>
                          <div class="col-4">
                            <input type="email" placeholder="C.C.P." name="ccp3" class="form-control" value="'.$this->model->ccp3.'"/>
                          </div>
                        </div> 
                      </td>
                    </tr> 
                    <tr>
                      <th colspan="4">Asunto</th>
                    </tr>
                    <tr>
                      <td colspan="4">
                        <input type="text" placeholder="Asunto del correo" name="titulo" class="form-control" value="'.$this->model->titulo.'" size="100" maxlength="100" required/>
                      </td>
                    </tr>
                    <tr>
                      <th colspan="4">Texto Correo</th>
                    </tr>
                    <tr>
                      <td colspan="4"><textarea class="form-control" id="cuerpo" name="descripcion" rows="20" cols="100">'.$this->model->descripcion.'</textarea></td>
                    </tr>
                    <tr>
                      <th colspan="4"></th>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div style="display: flex; justify-content: center; padding-top: 5px;">
              <!-- <a type="submit" name="save" value=" " id="guardar" class="btn btn-success text-white text-center btn-block"><i class="icon-floppy-o"></i> Guardar</a> -->
                <button type="submit" name="save" value=" " id="guardar" class="btn btn-success text-white text-center btn-block"><i class="fas fa-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>';
      echo '</div></div>';

    }
    function browsecategoria() {
    $nuevo = '
    <a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setcategoriacop.php" href="javascript:;">
      <i class="fa fa-plus"></i> Nuevo
    </a>';

    $atras = '<a href="?modulo=correosop&accion=index" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i> Atras</a>';

    toolbar($_GET['modulo'], $atras, '', $nuevo, '');
    ?>
    <div class="card mt-3">
    <div class="card-body">
    <?php

      echo '<div class="table-responsive">
        <table width="90%" class="mb-0 table table-hover table-striped" id="myTable">
          <thead class="bg-light-blue bg-darken-2">
            <tr>
              <th class="p-1">Id</th>
              <th class="p-1">Categoria</th>
              <th class="p-1">Estatus</th>
              <th class="p-1"></th>
            </tr>
          </thead>
          <tbody>';
            
          echo '</tbody>
        </table>
      </div>
      </div>';

      echo '
    <script>
    function mandar(){
      //var table = $("#myTable").DataTable();
      $("#myTable").DataTable().clear().draw();
      $("#myTable").DataTable().destroy();

      $("#myTable").DataTable( {
        paging: true,
        scrollY: 400,
        processing: true,
        serverside: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatablecorreosop.php",
          type: "POST",
          datatype: "json",
          data:{ 
            "busca": "categoria"
          }
        },
        pageLength: "50",
        responsivePriority: 1,
        columnDefs: [
          { type: "num", targets: 0 } // Especificar que la primera columna es de tipo numérico
        ],
        order: [[0, "asc"]] 
      });
    }
    
    mandar();
  </script>
  ';
    }
    function editcategoria(){
      $est = array('A' => 'Activo', 'D' => 'Inactivo');
      $accion = 'insertcategoria';
      if (isset($this->model->id)){$accion = 'updatecategoria';}
      ?><script language="JavaScript">
        function checkSubmitedit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
      </script><?php
      echo '</div>
      <div class="container">
        <div class="table-responsive" id="">
          <center>
            <table class="table table-bordered">
              <thead class="">
                <tr>
                  <a href="?modulo=correosop&accion=categoria" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras</a>
                </tr>
              </thead>
            </table>
          </center>
        </div>';   
        echo '<div class="table-responsive">
          <center>
            <form method="post" action="?modulo=correosop&accion='.$accion.'" onsubmit="return checkSubmitedit();">
              <table class="table table-bordered table-hover table-striped bg-white">
                <thead class="text-white" style="background: #1d2b36;">
                  <tr>
                    <th colspan="2" class="p-1 text-center">Agregar Categoría de Correos</th>
                  </tr>
                </thead>';
                echo '<tbody>';
                  if ($accion=='updatecategoria'){
                    echo '<tr>
                      <td>ID</td>
                      <td><input type="hidden" name="id" value="'.$this->model->id.'"><input type="text" class="form-control" value="'.$this->model->id.'" readonly></td>
                    </tr>';
                  }
                  else {
                    echo '<tr>
                      <td>ID</td>
                      <td><input type="hidden" name="id" maxlength="3" value="'.$this->model->id.'"></td>
                    </tr>';
                  }
                  echo '<tr>
                    <td>Categoría</td>
                    <td><input type="text" name="nmb"  class="form-control" value="'.$this->model->nmb.'" maxlength="100" required/></td>
                  </tr>
                  <tr>
                    <td>Estatus</td><td>'.menu_select_array($est,$this->model->estatus, 'estatus',true).'</td>
                  </tr>
                  <tr>
                    <th colspan="2"><button type="submit" name="save" value=" " id="guardar" class="btn btn-success text-white offset-md-5 col-md-2"><i class="icon-floppy-o"></i> Guardar</button></th>              
                  </tr>';    
                echo'</tbody>
              </table>
            </form>
          </center>
        </div>
      </div>';
    }
    function show(){
      $atras = '<a type=button value="" onClick="javascript:history.go(-1);" id="atras" class="btn btn-sm btn-warning text-white"><i class="fa fa-arrow-left"></i> Atras</a>';
      toolbar($_GET['modulo'], $atras);
      echo '
      <div class="col-xs-12 mt-1">
        <div class="card">';
          echo '<div class="card-header">
            <h4 class="card-title"> Visualización Correo </h4>
            <div class="heading-elements" bis_skin_checked="1">
              <ul class="list-inline mb-0">
                <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                <li><a data-action="reload"><i class="icon-reload"></i></a></li>
                <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                <li><a data-action="close"><i class="icon-cross2"></i></a></li>
              </ul>
            </div>
          </div> 
          <div class="card-body in">
            <div class="table-responsive">
              <table class="table bg-white table-bordered mt-3">
                <tbody>';
                  echo '<tr>
                    <th>ID</th>
                    <td>
                      <input type="text" name="id" maxlength="3" class="form-control" value="'.$this->model->id.'" readonly/>
                    </td>';
                    echo '<th class="">Categoría</th>
                    <td>
                      <input type="text" class="form-control" value="'.$this->model->nmb.'" readonly/>';
                    echo '</td>
                  </tr>
                  <tr>
                  <th>Correo</th>
                    <td>
                      <input type="email" name="mail" class="form-control" value="'.$this->model->mail.'" maxlength="80" required readonly/>
                    </td>
                    <th>Contraseña</th>
                    <td>
                      <input type="password" name="pass" class="form-control" value="'.$this->model->pass.'" maxlength="70" required readonly/>
                    </td>
                  </tr>';
                  echo '<tr>
                    <th>Acción</th>
                    <td>
                      <input type="text" name="nmb" class="form-control" value="'.$this->model->nmb.'" maxlength="100" required readonly/>
                    </td>
                    <th>Clave de bitácota</th>
                    <td>
                      <input type="text" name="claveb" class="form-control" value="'.$this->model->claveb.'" maxlength="5" required readonly/>
                    </td>
                  </tr>';
                  echo '<!--
                    <tr>
                      <th class="p-1" colspan="4">C.C.P.</th>
                    </tr>
                    <tr>
                      <td colspan="4">
                        <div class="row">
                          <div class="col-3">
                            <input type="email" name="ccp1" class="form-control" value="'.$this->model->ccp1.'"/> 
                          </div>
                          <div class="col-3">
                            <input type="email" name="ccp2" class="form-control" value="'.$this->model->ccp2.'"/>
                          </div>
                          <div class="col--3">
                            <input type="email" name="ccp3" class="form-control" value="'.$this->model->ccp3.'" />
                          </div>
                        </div> 
                      </td>
                    </tr>
                  -->
                  <tr>
                    <th colspan="4">Asunto</th>
                  </tr>
                  <tr>
                    <td colspan="4">
                      <input type="text" name="titulo" class="form-control" value="'.$this->model->titulo.'" size="100" maxlength="100" required readonly/>
                    </td>
                  </tr>
                  <tr>
                    <th colspan="4">Texto Correo</th>
                  </tr>
                  <tr>
                    <td colspan="4" ><div class="">'.$this->model->descripcion.'</div></td>
                  </tr>
                  <tr>
                    <th colspan="4"></th>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>';
      echo '</div></div>';
    }
    function showcuenta(){
      echo '<div class="card p-2">
        <a class="btn btn-warning" href="?modulo=correosop&accion=index"><i class="fa fa-arrow-left"></i> Atras</a>
        <h3>Cuenta '.$this->model->nmb.'</h3>
        <hr>
        <form class="" action="?modulo=correosop&accion=updatecuenta&id='.$this->model->id.'" method="POST">
          <div class="row">
            <div class="row col-md-6">
              <div class="col-md-12 mb-5">
                <label for="">Cuerpo</label>
                <input type="text" name="nombre" class="form-control" value="'.$this->model->nmb.'">
              </div>
              <div class="col-md-12 mb-5">
                <label for="">Cuerpo</label>
                <textarea name="descripcion" id="" rows="5">'.$this->model->descripcion.'</textarea>
              </div>
            </div>
          </div>
          <div class="row col-md-6">
          </div>
          <div class="">
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
          </div>
        </form>
      </div>';
    }
  }
?>