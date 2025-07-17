<?php
  ini_set('display_errors', 1);
  class registrosw{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelregistrosw($obj);
    }
    function index(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "R";

      if($_REQUEST['estatus'] != "R") $fini = date("Y-m-01");
      else $fini = busca("R",'registros_distribuidores','rd_estatus','MIN(rd_fecharegistro)');

      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date("Y-m-d",strtotime($fini));
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date("Y-m-d");

      $this->model->result($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin']);
      $this->view = new viewlregistrosw($this->model);
      $this->view->browse($_REQUEST['estatus'],$_REQUEST['fini'],$_REQUEST['ffin']);
    }
    function show(){
      $this->model->select($_GET['id']);
      $this->view = new viewlregistrosw($this->model);
      $this->view->show();
    }
    function autorizar(){
      $this->model->autorizar($_GET['id'],$_POST['estatus'],$_POST['comevaluacion']);
      $this->model->setuserweb($_GET['id']);
      if($_POST['estatus'] == "A") $this->model->sendmailacepta($_GET['id']);
      else $this->model->sendmaildeclina($_GET['id']);

      redirect('?modulo=registrosw&accion=index');
    }
    function registrosacademy(){
      $_REQUEST['origen'] = "1";
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date("Y-m-01");
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date("Y-m-d");
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "R";
        
      $this->model->resultacademy($_REQUEST['origen'],$_REQUEST['fini'],$_REQUEST['ffin'], $_REQUEST['estatus']);
      $this->view = new viewlregistrosw($this->model);
      $this->view->showacademy($_REQUEST['origen'],$_REQUEST['fini'],$_REQUEST['ffin'], $_REQUEST['estatus']);
    }
    function registrosboletin(){
      $this->model->registrosboletin();
      $this->view = new viewlregistrosw($this->model);
      $this->view->showboletin();
    }
    function showcliente(){
      $this->model->setcursos($_GET['id']);
      $this->view = new viewlregistrosw($this->model);
      $this->view->showcliente();
    }
    function dictamen(){
      $this->model->selacademy($_GET['id']);
      $this->view = new viewlregistrosw($this->model);
      $this->view->dictamen($_GET['id']);
    }
    function finalizaracademy(){
      $this->model->updateacademy($_GET['id'],$_REQUEST['estatus'],clearvmayus($_REQUEST['comentarios']),$_REQUEST['pedido']);
      if($_REQUEST['estatus'] == "A") $mailop = '3';
      else $mailop = '4';
      
      $this->model->sendmailacademy(3,$mailop,$_REQUEST['cliente'],"3",$_REQUEST['pedido'],clearvmayus($_REQUEST['comentarios']));

      redirect('?modulo=registrosw&accion=registrosacademy&p=1');
    }
    function resumen(){
      $fini1 = busca("R",'registros_distribuidores','rd_estatus','MIN(rd_fecharegistro)');
      $fini2 = buscaAcademy("R",'reportepago','r_estatus','MIN(r_fecha)');

      if($fini1 <= $fini2) $fini = $fini1;
      else $fini = $fini2;

      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date("Y-m-d",strtotime($fini));
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date("Y-m-d");

      $this->model->resultresumen($_REQUEST['fini'],$_REQUEST['ffin']);
      $this->view = new viewlregistrosw($this->model);
      $this->view->resumen($_REQUEST['fini'],$_REQUEST['ffin']);
    }
  }

  class modelregistrosw{
    function result($estatus,$fini,$ffin){
      /*if($_REQUEST['fecha']){
        $mes = ' AND date_format(rd_fecharegistro,"%M-%Y") = "'.$_REQUEST['fecha'].'" ';
      }else{
        $mes = '';
      }*/

      if($fini && $ffin) {
        $mes = ' AND DATE(rd_fecharegistro) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
      }else $mes = '';

      if($estatus == "T")
        $sql = 'SELECT * FROM registros_distribuidores WHERE 1 '.$mes.' ORDER BY rd_fecharegistro DESC ';
      else
        $sql = 'SELECT * FROM registros_distribuidores WHERE rd_estatus = "'.$estatus.'" '.$mes.' ORDER BY rd_fecharegistro DESC';

      if($estatus != "T") $es = 'rd_estatus = "'.$estatus.'"';
      else $es = '1';
      $sql2 = 'SELECT COUNT(*) FROM registros_distribuidores WHERE '.$es.' '.$mes.' ORDER BY rd_fecharegistro DESC';

      $this->result = setq($sql);
      $resultx = setq($sql2);
      list($nrows) = $resultx->fetch_array();
      $this->resultx = $nrows;
      $this->resultt = setq($sql2);
    }
    function resultacademy($origen,$fini,$ffin,$estatus){
      $sql = 'SELECT * FROM registros_academy WHERE 1 ';
      if($origen) $sql .= ' AND ra_origen = "'.$origen.'" '; 
      if($estatus == "R") $sql .= ' AND ra_registrado = "1"';
      elseif($estatus == "B") $sql .= ' AND ra_registrado = "0"';
      if($fini && $ffin) $sql .= ' AND ra_fechar BETWEEN "'.$fini.'" AND "'.$ffin.'"';
      $sql .= 'ORDER BY ra_fechar DESC';
      $this->result = setq($sql);  
    }
    function select($id){
      $sql = 'SELECT * FROM registros_distribuidores WHERE rd_id="'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['rd_id'];
      $this->nmb = $row['rd_nmb'];
      $this->telefono = $row['rd_telefono'];
      $this->correo = $row['rd_correo'];
      $this->fecharegistro = $row['rd_fecharegistro'];
      $this->razonsocial = $row['rd_razonsocial'];
      $this->estado = $row['rd_estado'];
      $this->ciudad = $row['rd_ciudad'];
      $this->comentarios = $row['rd_comentarios'];
      $this->rfc = $row['rd_rfc'];
      $this->cedulanotfiscal = $row['rd_cedulanotfiscal'];
      $this->compdomicilio = $row['rd_compdomicilio'];
      $this->identificacion = $row['rd_identificacion'];
      $this->relproveedores = $row['rd_relproveedores'];
      $this->comprobanteventas = $row['rd_comprobanteventas'];
      $this->infopromociones = $row['rd_infopromociones'];
      $this->fevaluacion = $row['rd_fevaluacion'];
      $this->uevaluacion = $row['rd_uevaluacion'];
      $this->comevaluacion = $row['rd_comevaluacion'];
      $this->estatus= $row['rd_estatus'];
    }
    function autorizar($solicitud,$estatus,$comens){
      $sql = 'UPDATE registros_distribuidores SET
              rd_comevaluacion = "'.clearvmayus($comens).'",
              rd_fevaluacion = "'.date('Y-m-d H:i:s').'",
              rd_uevaluacion = "'.$_SESSION['uid'].'",
              rd_estatus = "'.$estatus.'"
              WHERE rd_id = "'.$solicitud.'"';
      setq($sql);
    }
    function setuserweb($solicitud){
      $this->select($solicitud);

      $idcl = getmax("c_id",'clientes');
      include('clientesw.php');
      $cliw = new modelclientesw();
      if($this->rfc) $facturacion = "1";
      $regimen = '';
      $pass = '';

      $cliw->setData($idcl,"M",$this->nmb,"","",$this->correo,$this->telefono,"","A","2",$this->razonsocial,$this->rfc,$calle,$nume,$numi,$colonia,$cp,$municipio,$estado,$pais,$facturacion,$pass,$regimen);
      if(busca($this->correo,'clientes','c_mail','COUNT(*)') == 0)
        $cliw->insert();
      else $idcl = busca($this->correo,'clientes','c_mail','c_id');

      $sqlrd = 'UPDATE registros_distribuidores SET rd_idcliente = "'.$idcl.'" WHERE rd_id = "'.$solicitud.'"';
      setq($sqlrd);
    }
    function sendmailacepta($solicitud){
      $this->select($solicitud);
      $sql = 'UPDATE clientes SET c_esquema = "2",c_estatus = "A" WHERE c_mail = "'.$this->correo.'" ';
      setq($sql);

      require_once('lib/mailer/class.phpmailer.php');
      require_once("lib/mailer/class.smtp.php");
      $mail = new PHPMailer(); // defaults to using php "mail()"
      $asunto = "¡FELICIDADES! Ya eres un distribuidor de JDShop";
      $nombre = $this->destino;
      $puestoi = "JD SHOP";
      $correoi = "noreply@jdshop.mx";
      $telefonoi = "771-104-1684";
      $copias = "registros-distribuidor@jdshop.mx";
      /*
        $logo = busca($_SESSION['emp'],'empresas','e_id','e_logo');
        if(file_exists($logo)){
          $body.='<center><img src="'.$logo.'"  width="160" height="130" /></center>';
        }
      */
      $encabezado = '<div class="cabeza"><h2>¡Felicidades! '.$this->nmb.'</h2></div>';
      $bodycliente = '<hr>
        <div>
          <p>Permiteme darte la bienvenida al club de distribuidores de JD Shop<br>
          Donde podrás encontrar promociones, capacitaciones y todos los productos necesarios, para ser un
          experto en videovigilancia y redes</p>
        </div>
        <div></div>';
      $nocliente = busca($solicitud,'registros_distribuidores','rd_id','rd_idcliente');
      $pass = RandomString(8,FALSE,TRUE);
      $sql = 'UPDATE clientes SET c_password = PASSWORD("'.$pass.'") WHERE c_id = "'.$nocliente.'"';
      setq($sql);

      $bodyproveedor = '<hr>
                      <div>
                        <p>
                          Ahora te daré la información de tu cuenta, para poder acceder a JD Shop y comenzar tu distribución
                          <br>
                          No Cliente: '.$nocliente.'<br>
                          Correo: '.$this->correo.'<br>
                          Contraseña: '.$pass.'<br><br>
                          Para acceder a JD Shop, deberás entrar usando tu correo electrónico y esta contraseña.
                          <br>'.$this->comevaluacion.'
                          <br /><br /> <strong>Únete a nuestra comunidad de JD Shop en</strong> <a href="https://www.facebook.com/JDShopmx">Facebook dando clic aquí.</a><br />Pero si lo tuyo es Twitter<a href="https://twitter.com/JDShopmx">síguenos dando clic aquí.</a> <br />O el consentido <a href="https://www.instagram.com/jdshopmx/">Instagram</a> ¡Espero tus comentarios pronto!<br /><br />Te estaremos esperando en<br /><strong><a href="https://jdshop.mx/">JD SHOP MX</a></strong><br /><br /><br /><span style="font-size: 8pt;">-----Mensaje Generado automáticamente por JD Shop-----</span><br /><span style="font-size: 8pt;"> Por favor no respondas a este email, ya que no podremos contestarte</span></p>
                        </p>
                      </div>';

      $body.=utf8_decode($encabezado);
      $body.=utf8_decode($bodycliente);
      $body.=utf8_decode($bodyproveedor);
      $body.= utf8_decode(nl2br($this->mensaje));

      $mail->setFrom($correoi, utf8_decode($puestoi));
      $mail->AddAddress($this->correo, utf8_decode($this->nmb));
      $mail->addCC($copias);

      $mail->ConfirmReadingTo = "registros-distribuidor@jdshop.mx";
      $mail->Subject = utf8_decode($asunto);
      $body.=utf8_decode('<hr>Mensaje generado automáticamente por JD CEO 2.0');

      $mail->MsgHTML($body);
      $dominio = explode('@',$correoi);

      $mail->Host = $dominio[1];
      $mail->Port = 465;
      $mail->IsSMTP();
      $mail->SMTPSecure = "ssl";
      //      $mail->SMTPDebug = 2;
      $mail->SMTPAuth = true;
      $mail->Username = $correoi;
      $mail->Password = "WpJDTye2020#";

      //envío el mensaje, comprobando si se envió correctamente
      $mail->Send();
    }
    function sendmaildeclina($solicitud){
      $this->select($solicitud);
      $sql = 'UPDATE clientes SET c_esquema = "1",c_estatus = "A" WHERE c_mail = "'.$this->correo.'" ';
      setq($sql);

      require_once('lib/mailer/class.phpmailer.php');
      require_once("lib/mailer/class.smtp.php");
      $mail = new PHPMailer(); // defaults to using php "mail()"
      $asunto = "¡Te tengo noticias de JD Shop!";
      $nombre = $this->destino;
      $puestoi = "JD SHOP";
      $correoi = "noreply@jdshop.mx";
      $telefonoi = "771-104-1684";
      $copias = "registros-distribuidor@jdshop.mx";
      /*
        $logo = busca($_SESSION['emp'],'empresas','e_id','e_logo');
        if(file_exists($logo)){
          $body.='<center><img src="'.$logo.'"  width="160" height="130" /></center>';
        }
      */
      $encabezado = '<div class="cabeza"><h2>¡Hola! '.$this->nmb.' te tengo una mala noticia</h2></div>';
      $bodycliente = '<hr>
                    <div>
                      <p>Desafortunadamente tu solicitud no cumplió con los requerimientos mínimos
                      para aceptarte como distribuidor, sin embargo, aún puedes comprar en JD Shop<br>
                      Te recomiendo que revises '.$this->comevaluacion.'</p>
                    </div>
                    <div></div>';
      $nocliente = busca($solicitud,'registros_distribuidores','rd_id','rd_idcliente');
      $pass = RandomString(8);
      $sql = 'UPDATE clientes SET C_password = PASSWORD("'.$pass.'") WHERE c_id = "'.$nocliente.'"';
      setq($sql);

      $bodyproveedor = '<hr>
                  <div>
                    <p>
                      Ahora te daré la información de tu cuenta, para poder acceder a JD Shop y veas nuestros productos<br>
                      No Cliente: '.$nocliente.'<br>
                      Correo: '.$this->correo.'<br>
                      Contraseña: '.$pass.'<br><br>
                      Para acceder a JD Shop, deberás entrar usando tu correo electrónico y esta contraseña.
                      <br>'.$this->comevaluacion.'
                      <br /><br /> <strong>Únete a nuestra comunidad de JD Shop en</strong> <a href="https://www.facebook.com/JDShopmx">Facebook dando clic aquí.</a><br />Pero si lo tuyo es Twitter<a href="https://twitter.com/JDShopmx">síguenos dando clic aquí.</a> <br />O el consentido <a href="https://www.instagram.com/jdshopmx/">Instagram</a> ¡Espero tus comentarios pronto!<br /><br />Te estaremos esperando en<br /><strong><a href="https://jdshop.mx/">JD SHOP MX</a></strong><br /><br /><br /><span style="font-size: 8pt;">-----Mensaje Generado automáticamente por JD Shop-----</span><br /><span style="font-size: 8pt;"> Por favor no respondas a este email, ya que no podremos contestarte</span></p>
                    </p>
                  </div>';

      $body.=utf8_decode($encabezado);
      $body.=utf8_decode($bodycliente);
      $body.=utf8_decode($bodyproveedor);
      $body.= utf8_decode(nl2br($this->mensaje));

      $mail->setFrom($correoi, utf8_decode($puestoi));
      $mail->AddAddress($this->correo, utf8_decode($this->nmb));
      $mail->addCC($copias);

      $mail->ConfirmReadingTo = "registros-distribuidor@jdshop.mx";
      $mail->Subject = utf8_decode($asunto);
      $body.=utf8_decode('<hr>Mensaje generado automáticamente por JD CEO 2.0');

      $mail->MsgHTML($body);
      $dominio = explode('@',$correoi);

      $mail->Host = $dominio[1];
      $mail->Port = 465;
      $mail->IsSMTP();
      $mail->SMTPSecure = "ssl";
      //      $mail->SMTPDebug = 2;
      $mail->SMTPAuth = true;
      $mail->Username = $correoi;
      $mail->Password = "WpJDTye2020#";

      //envío el mensaje, comprobando si se envió correctamente
      $mail->Send();
    }
    function registrosboletin(){
      $sql = 'SELECT * FROM boletines';
      $this->result = setq($sql);
    }
    function setcursos($cliente){
      $sqlInf = 'SELECT * FROM usuarios WHERE u_id = "'.$cliente.'"';

      $sql = 'SELECT * FROM usuarios_pedidos INNER JOIN cursos ON c_id = up_curso 
              INNER JOIN usuarios ON u_id = up_usuario INNER JOIN formaspago ON f_id = up_fpago
              WHERE up_usuario = "'.$cliente.'" ORDER BY up_fecha DESC';
      
      $this->resulti = setqAcademy($sqlInf);
      $this->resultp = setqAcademy($sql);
      $this->resultpp = setqAcademy($sql);
    }
    function reporteacademy($estatus,$fini,$ffin){
      $sql = 'SELECT * FROM reportepago INNER JOIN usuarios_pedidos ON r_pedido = up_id 
              INNER JOIN usuarios ON up_usuario = u_id INNER JOIN cursos ON up_curso = c_id WHERE 1';
      if($estatus) $sql .= ' AND r_estatus = "'.$estatus.'"';
      if($fini && $ffin) $sql .= ' AND DATE(r_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
      $sql .= ' ORDER BY up_fecha DESC';
    
      $this->resultrc = setqAcademy($sql);
      $this->resultrcc = setqAcademy($sql);
    }
    function selacademy($reporte){
      $sql = 'SELECT * FROM reportepago INNER JOIN usuarios_pedidos ON r_pedido = up_id 
              INNER JOIN usuarios ON up_usuario = u_id INNER JOIN cursos ON up_curso = c_id
              INNER JOIN formaspago ON f_id = up_fpago
              WHERE r_id = "'.$reporte.'"';
      $result = setqAcademy($sql);
      $row = $result->fetch_array();

      $this->cliente = $row['u_id'];
      $this->nmb = $row['u_nmb'];
      $this->apellidos = $row['u_apellidos'];
      $this->correo = $row['u_correo'];
      $this->regimen = $row['u_regimenfis'];
      $this->rfc = $row['u_rfc'];
      $this->razonsocial = $row['u_razon'];
      $this->calle = $row['u_calle'];
      $this->nume = $row['u_nume'];
      $this->colonia = $row['u_colonia'];
      $this->numi = $row['u_numi'];
      $this->cp = $row['u_cp'];
      $this->municipio = $row['u_municipio'];
      $this->estado = $row['u_estado'];
      $this->pais = $row['u_pais'];;
      $this->remision = $row['r_remision'];
      $this->pedido = $row['r_pedido'];
      $this->observacion = $row['r_observacion'];
      $this->adjunto = $row['r_adj'];
      $this->fechareporte = $row['r_fecha'];
      $this->horareporte = $row['r_hora'];
      $this->estatus = $row['r_estatus'];
      $this->formapago = $row['f_nmb'];
      $this->fechapedido = $row['up_fecha'];
      $this->curso = $row['c_nmb'];
      $this->factura = $row['up_facturacion'];
    }
    function updateacademy($id,$estatus,$comentarios,$pedido){
      $sql = 'UPDATE reportepago SET
          r_estatus = "'.$estatus.'",
          r_uautoriza = "8",
          r_fautoriza = "'.date('Y-m-d H:i:s').'",
          r_comentario = "'.$comentarios.'"
          WHERE r_id = "'.$id.'"';
      setqAcademy($sql);

      $sqlpedido = 'UPDATE usuarios_pedidos SET
                up_estatus = "'.$estatus.'"
                WHERE up_id = "'.$pedido.'"';
      setqAcademy($sqlpedido);
    }
    function sendmailacademy($web,$correop,$usuario,$accion = NULL,$pedido = NULL,$comentarios = NULL){
      require_once('lib/mailer/class.phpmailer.php');
      require_once("lib/mailer/class.smtp.php");

      $sql = 'SELECT * FROM correosop WHERE c_id = "'.$correop.'" AND c_web = "'.$web.'"';
      $result = setq($sql);
      $rwco = $result->fetch_array();

      $codescripcion = nl2br($rwco['c_descripcion']);
      $coemail = $rwco['c_mail'];
      $cotitulo = $rwco['c_titulo'];
      $coccp1 = $rwco['c_ccp1'];
      $coccp2 = $rwco['c_ccp2'];
      $coccp3 = $rwco['c_ccp3'];

      $sqlUsu = 'SELECT u_nmb,u_apellidos,u_correo FROM usuarios WHERE u_id = "'.$usuario.'"';
      $resultUsu = setqAcademy($sqlUsu);
      $rowus = $resultUsu->fetch_array();

      $nmbc = $rowus['u_nmb'].' '.$rowus['u_apellidos'];
      //$mailc = $rowus['u_correo'];
      $mailc = 'jorge.guerrero0913@gmail.com';

      $sqlPed = 'SELECT * FROM  usuarios_pedidos INNER JOIN cursos ON c_id = up_curso WHERE up_id = "'.$pedido.'"';
      $resultPed = setqAcademy($sqlPed);
      $rowPed = $resultPed->fetch_array();
      
      $title = $cotitulo;
      //$title = str_replace('--%nombrecliente%--',$pedido,$title);

      $message = $codescripcion;
      $message = str_replace('--%nombrecliente%--',trim($nmbc),$message);

      if($accion == 3) $rem = 'Tu escuela virtual de JDSHOPMX';
      
      $message = str_replace('--%nombrecurso%--',trim($rowPed['c_nmb']),$message);
      $message = str_replace('--%comentarios%--',trim($comentarios),$message);

      $mail = new PHPMailer();
      $body = (utf8_decode($message));

      $mail->setFrom($coemail,$rem);
      $mail->AddAddress($mailc, $nmbc);

      if(!empty($coccp1))
        $mail->AddBCC($coccp1);
      if(!empty($coccp2))
        $mail->AddBCC($coccp2);
      if(!empty($coccp3))
        $mail->AddBCC($coccp3);

      $mail->Subject = utf8_decode($title);
      $mail->AltBody = "";
      $mail->MsgHTML($body);

      if($adjunto)
        $mail->AddAttachment($adjunto);

      $mail->SMTPSecure = "ssl";
      $mail->Host='jdshop.mx';
      $mail->Port = "465";
      $mail->IsSMTP();
      $mail->SMTPAuth = true;
      $mail->Username = $coemail;
      $mail->Password = "WpJDTye2020#";
      //$mail->SMTPDebug = 2;

      if(!$mail->send()) {
      }else{
      }
    }
    function resultresumen($fini,$ffin){
      if($fini && $ffin) {
        $sqla = ' AND DATE(r_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
        $sqld = ' AND DATE(rd_fecharegistro) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
      }

      $sqldistribuidor = 'SELECT * FROM registros_distribuidores WHERE rd_estatus = "R" '.$sqld.' ORDER BY rd_fecharegistro DESC';
      $this->resultdistribuidor = setq($sqldistribuidor);

      $sqlacademy = 'SELECT * FROM reportepago INNER JOIN usuarios_pedidos ON r_pedido = up_id 
                    INNER JOIN usuarios ON up_usuario = u_id INNER JOIN cursos ON up_curso = c_id
                    WHERE r_estatus = "R" '.$sqla.' ORDER BY r_fecha DESC';
      $this->resultpacademy = setqAcademy($sqlacademy);
    }
  }

  class viewlregistrosw {
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->model->estatuse = array("A" => "Autorizado","R" => "Por autorizar","N" => "Registro Básico","C" => "Rechazado");
      $this->model->estatusc = array("A" => "success","R" => "primary","N" => "warning","C" => "danger");

      $this->model->estp = array("N"=>"En Carrito de Compra","R"=>"Pago Reportado","A"=>"Aceptado","C"=>"Cancelado");
      $this->model->estc = array("N"=>"#FEF900","R"=>"#FD2401","F"=>"#7824B7","A"=>"#01B700","C"=>"#CC0000");

      $this->model->txtestatus = array("A" => "Finalizada","P" => "En espera de autorización","N" => "En Captura","C" => "Cancelada");
      $this->model->backestatus = array("A" => "btn-success","P" => "bg-amber bg-darken-2","N"=>"bg-yellow bg-darken-3","C" => "btn-danger");

      $this->model->txtestatusc = array("N"=>"NUEVA","F"=>"FINALIZADA","C"=>"CANCELADA","A"=>"ABONADA");
      $this->model->backestatusc = array("N"=>"background-color:#FF5C5C;color:white;","F"=>"background-color:#47B6FF","C"=>"background-color:#000000; color:white;","A"=>"background-color:#FFE447");
      ?>
      <script language="JavaScript">
        function checkSubmitedit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
      </script>
      <?php
    }
    function browse($estatus,$fini,$ffin) {
      //Sección: E1 Encabezado - Botones de acción
      echo '<div class="mb-5">';
        if($estatus == "A") $chest = "checked"; else $chest = "";
        $esta = ""; $esti = ""; $estt = "";
        if(!isset($estatus) || $estatus == "A") $esta = "selected";
        elseif($estatus == "I") $esti = "selected";
        else $estt = "selected";
        /* $actn = ""; $actr = ""; $acta = ""; $actc = ""; $actt = "";
        if($estatus == "A") $acta = "text-uppercase text-bold-900";
        elseif($estatus == "N") $actn = "text-uppercase text-bold-900";
        elseif($estatus == "R") $actr = "text-uppercase text-bold-900";
        elseif($estatus == "C") $actc = "text-uppercase text-bold-900";
        elseif($estatus == "T") $actt = "text-uppercase text-bold-900";
        //Sección: E2 Encabezado - Filtros
        echo '<a href="?modulo=registrosw&accion=index&estatus=N">
          <button type="button" class="btn btn-warning '.$actn.'">
            <span class="glyphicon glyphicon-cog"></span><i class="icon-file-o"></i> Básicos
            <div class="tag tag-pill tag-danger">'.busca("N",'registros_distribuidores','rd_estatus','COUNT(*)').'</div>
          </button>
        </a>
        <a href="?modulo=registrosw&accion=index&estatus=R">
          <button type="button" class="btn btn-primary '.$actr.'">
            <span class="glyphicon glyphicon-cog"></span><i class="icon-play4"></i> Por autorizar
            <div class="tag tag-pill tag-danger">'.busca("R",'registros_distribuidores','rd_estatus','COUNT(*)').'</div>
          </button>
        </a>
        <a href="?modulo=registrosw&accion=index&estatus=A">
          <button type="button" class="btn btn-success '.$acta.'">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-check-circle"></i> Autorizados
            <div class="tag tag-pill tag-danger">'.busca("A",'registros_distribuidores','rd_estatus','COUNT(*)').'</div>
          </button>
        </a>
        <a href="?modulo=registrosw&accion=index&estatus=C">
          <button type="button" class="btn btn-danger '.$actc.'">
            <span class="glyphicon glyphicon-cog"></span><i class="icon-times-circle"></i> Rechazados
            <div class="tag tag-pill tag-danger">'.busca("C",'registros_distribuidores','rd_estatus','COUNT(*)').'</div>
          </button>
        </a>
        <a href="?modulo=registrosw&accion=index&estatus=T">
          <button type="button" class="btn btn-primary '.$actt.'">
            <span class="glyphi con glyphicon-cog"></span><i class="icon-minus-circle"></i> Todos
          </button>
        </a>
        <a href="?modulo=registrosw&accion=registrosacademy">
          <button type="button" class="btn btn-info">
            <span class="glyphicon glyphicon-cog"></span><i class="icon-graduation-cap"></i> Registros Academy
          </button>
        </a>
        <a href="?modulo=registrosw&accion=registrosboletin">
          <button type="button" class="btn btn-secondary">
            <span class="glyphicon glyphicon-cog"></span><i class="icon-envelope-o"></i> Registros Boletín
          </button>
        </a>'; */
        if($estatus == "A") {$acta = "text-uppercase text-bold-900 btn-success"; $title = "Autorizados"; }
        elseif($estatus == "N") {$actn = "text-uppercase text-bold-900 btn-warning"; $title = "Básicos"; }
        elseif($estatus == "R") {$actr = "text-uppercase text-bold-900 btn-primary"; $title = "Por Autorizar"; }
        elseif($estatus == "C") {$actc = "text-uppercase text-bold-900 btn-danger"; $title = "Rechazados"; }
        elseif($estatus == "T") {$actt = "text-uppercase text-bold-900 btn-primary"; $title = ""; }
          
        echo '<div class="page-title-actions">
          <div class="form-inline mb-1">
            <div class="btn-group" role="group">
              <div class="btn-group">
                <a type="button" class="btn btn-success text-white" href="?modulo=registrosw&accion=index">
                  <i class="icon-world"></i> Registros Distribuidor
                </a>
                <button type="button" class="btn btn-success active dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                </button>
                <div class="dropdown-menu">
                  <a href="?modulo=registrosw&accion=index&estatus=N" type="button" class="btn dropdown-item '.$actn.'">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-file-o"></i> Básicos
                    <div class="tag tag-pill tag-danger">'.busca("N",'registros_distribuidores','rd_estatus','COUNT(*)').'</div>
                  </a>
                  <a href="?modulo=registrosw&accion=index&estatus=R" class="btn '.$actr.' dropdown-item" type="button">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-play5"></i> Por autorizar
                    <div class="tag tag-pill tag-danger">'.busca("R",'registros_distribuidores','rd_estatus','COUNT(*)').'</div>
                  </a>
                  <a href="?modulo=registrosw&accion=index&estatus=A" class="btn '.$acta.' dropdown-item" type="button">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-circle-check"></i> Autorizados
                    <div class="tag tag-pill tag-danger">'.busca("A",'registros_distribuidores','rd_estatus','COUNT(*)').'</div>
                  </a>
                  <a href="?modulo=registrosw&accion=index&estatus=C"  class="btn '.$actc.' dropdown-item"  type="button>
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-circle-cross"></i> Rechazados
                    <div class="tag tag-pill tag-danger">'.busca("C",'registros_distribuidores','rd_estatus','COUNT(*)').'</div>
                  </a>
                  <a href="?modulo=registrosw&accion=index&estatus=T" class="btn '.$actt.' dropdown-item" type="button">
                    <span class="glyphi con glyphicon-cog"></span><i class="icon-circle-minus"></i> Todos
                  </a>
                </div>
              </div>
              <a type="button" class="btn btn-info text-white" href="?modulo=registrosw&accion=registrosacademy">
                <i class="icon-world"></i> Registros Academy
              </a>
              <a type="button" class="btn btn-warning btn-darken-2 text-white" href="?modulo=registrosw&accion=registrosboletin">
                <i class="icon-world"></i> Registros Boletín
              </a>
            </div>
            <button accesskey="L" id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
              <span class="tag tag-pill tag-danger" bis_skin_checked="1">'.$this->model->resultx.'</span>
            </button>
          </div>';
          if(isset($_GET['estatus'])) $url = '&estatus='.$_GET['estatus'];
          else $url = '';

          echo'<form autocomplete="off" action="?modulo=registrosw&accion=index'.$url.'" method="post" onsubmit="return checkSubmitenviar();" class="row form-inline mb-1" id="filtro">
            <div id="filter-panel" class="collapse filter-panel col-md-12">
              <div class="mb-5">
                <label class="mr-sm-2">Desde:</label>
                <input type="date" id="fini" name="fini" value="'.$fini.'" class="form-control">
              </div>
              <div class="mb-5">
                <label class="mr-sm-2">Hasta:</label>
                <input type="date" id="ffin" name="ffin" value="'.$ffin.'" class="form-control">
              </div>';
              echo'<div class="mb-5 ml-1">
                <label style="color:transparent">Filtrar:</label><br>
                <button type="submit" class="btn btn-info"><i class="icon-filter2"></i> Filtrar</button>
              </div>
              <div class="mb-5">
                <label style="color:transparent">Limpiar:</label><br>
                <a href="?modulo=registrosw&accion=index'.$url.'" class="btn btn-warning"><i class="fa fa-times"></i> Limpiar</a>
              </div>
            </div>
          </form>';
        ?></div>
      </div>
      <script>
        function checerase(iduni){
          var conf = confirm("¿Deseas eliminar la categoria seleccionada?\nPresiona Aceptar para borrarla");
          if(conf == true){
            window.location.href="?modulo=registrosw&accion=borrar&id=" + iduni;
          }
        }
        function verlineasneg(iduni){
          window.open("?modulo=articulos&accion=index&categoria=" + iduni);
        }
      </script><?php
      echo '<div class="card row p-1">';
        //$sql = 'SET lc_time_names = "es_ES"; ';
        //setq($sql);
        /* $sql = 'SELECT date_format(rd_fecharegistro,"%M-%Y") mes FROM `registros_distribuidores` GROUP BY date_format(rd_fecharegistro, "%M-%Y") ORDER BY DATE(rd_fecharegistro)';
        $result = setq($sql);
        $rowx = $this->model->resultt->fetch_array();
        echo '<form action="index?modulo=registrosw&accion=index&estatus='.$_GET['estatus'].'" method="POST" role="form">
          <div class="col-md-3 mb-5">
            <label for="">Seleccionar Mes</label>
            <select name="fecha" id="fecha" class="form-control">
              <option value="">Todos</option>';
              while($row = $result->fetch_array()){
                if($_REQUEST['fecha'] == $row['mes']) $selected = 'selected'; else $selected = ''; 
                echo '<option value="'.$row['mes'].'" '.$selected.'>'.$row['mes'].'</option>';
              }
            echo '</select>
            <span class="tag tag-pill tag-danger" bis_skin_checked="1">'.$rowx['COUNT(*)'].'</span>
          </div>
          <div class="col-md-2 mb-5">
            <label>Enviar</label><br>
            <button type="submit" class="btn btn-info"><i class="fa fa-paper-plane"></i> Buscar</button>
          </div>
        </form>'; */
        echo'<div class="col-md-12">
          <h3>Registros Para Distribuidor - '.$title.'</h3>
          <hr>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead class="bg-light-blue bg-darken-2 text-white">
                <tr><th colspan="8" class="p-1 ">Registros para distribuidor</th></tr>
                <tr>
                  <th>No.</th>
                  <th>Nombre</th>
                  <th>Telefono</th>
                  <th>Correo</th>
                  <th>Fecha</th>
                  <th>Comentarios</th>
                  <th></th>
                  <th>Estatus</th>
                </tr>
              </thead>';
              $nresul = $this->model->resultx;
              while ($row = $this->model->result->fetch_array()) {
                echo '<tr>
                  <td>'.$nresul.'</td>
                  <td>'.$row['rd_nmb'].'</td>
                  <td>'.$row['rd_correo'].'</td>
                  <td>'.$row['rd_telefono'].'</td>
                  <td>'.fecha_formato($row['rd_fecharegistro'],false,false).'</td>
                  <td>'.$row['rd_comentarios'].'</td>
                  <td>';
                    if($row['rd_estatus'] != "N")
                      echo '<a href="?modulo=registrosw&accion=show&id='.$row['rd_id'].'">
                        <button type="button" class="btn btn-info">
                          <i class="icon-chevron-right2"></i> Ver solicitud
                        </button>
                      </a>' ;
                  echo '</td>';
                  echo '<td class="bg-'.$this->model->estatusc[$row['rd_estatus']].'">'.$this->model->estatuse[$row['rd_estatus']].'</td>
                </tr>';
                $nresul--;
              }
            echo '</table>
          </div>
        </div>
      </div>';
    }
    function show(){
      echo '<div class="row page-title-actions">
        <a href="?modulo=registrosw&accion=index">
          <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Atras </button>
        </a>';
        if($this->model->estatus == "R")
          echo '<a data-fancybox data-type="ajax" data-src="popup/autorizaregistro.php?solicitud='.$this->model->id.'" href="javascript:;">
            <button class="btn btn-success mr-1" ><i class="fa fa-check"></i> Dictaminar solicitud</button>
          </a>';
      echo '</div></div>';
      echo '<div class="table-responsive">';
        echo '<table class="table table-striped">
          <thead class="thead bg-grey bg-darken-1 text-white pt-1 pb-1">
            <tr><th colspan="3">Información del cliente</th></tr>
          </thead>';
          echo '<tr>
            <th class=" bg-grey bg-darken-1 text-white">Nombre</th>
            <th class=" bg-grey bg-darken-1 text-white">Telefono</th>
            <th class=" bg-grey bg-darken-1 text-white">Correo</th>
          </tr>
          <tr>
            <td>'.$this->model->nmb.'</td>
            <td>'.$this->model->telefono.'</td>
            <td>'.$this->model->correo.'</td>
          </tr>';
          echo '<tr>
            <th class=" bg-grey bg-darken-1 text-white">Fecha</th>
            <th class=" bg-grey bg-darken-1 text-white">Ciudad</th>
            <th class=" bg-grey bg-darken-1 text-white">Estado</th>
          </tr>
          <tr>
            <td>'.fecha_formato($this->model->fecharegistro,false,false).'</td>
            <td>'.$this->model->ciudad.'</td>
            <td>'.$this->model->estado.'</td>
          </tr>';
          echo '<tr>
            <th class=" bg-grey bg-darken-1 text-white">Comentarios</th>
            <th class=" bg-grey bg-darken-1 text-white">RFC</th>
            <th class=" bg-grey bg-darken-1 text-white">Razón Social</th>
          </tr>
          <tr>
            <td>'.$this->model->comentarios.'</td>
            <td>'.$this->model->rfc.'</td>
            <td>'.$this->model->razonsocial.'</td>
          </tr>';
          $path = '../jdshop.mx/iniciatunegocio/distribuidor/'.$this->model->id.'/';
          $dir = opendir($path);
          $idd = 0;
          echo '<tr><th colspan="3" class=" bg-grey bg-darken-1 text-white">Archivos</th></tr>';
          echo '<tr>';
            while ($elemento = readdir($dir)){
              $idd++;
              if( $elemento != "." && $elemento != ".."){
                if(!is_dir($path.$elemento) ){
                  echo '<td><a target="_BLANK" href="https://jdshop.mx/iniciatunegocio/distribuidor/'.$this->model->id.'/'.$elemento.'">
                    <button class="btn btn-secondary"><i class="fa fa-paperclip"></i>'. $elemento.'</button>
                  </a></td>';
                }
                if($idd%3 == 0) echo '</tr><tr>';
              }
            }
          echo '</tr>';
          if($this->model->estatus != "R"){
            echo '<tr>
              <th class=" bg-grey bg-darken-1 text-white">Fecha de evaluación</th>
              <th class=" bg-grey bg-darken-1 text-white">Estatus</th>
              <th class=" bg-grey bg-darken-1 text-white">Comentarios</th>
            </tr>
            <tr>
              <td>'.fecha_formato($this->model->fevaluacion,true,true).'</th>
              <td>'.$this->model->estatuse[$this->model->estatus].'</th>
              <td>'.$this->model->comevaluacion.'</th>
            </tr>';
          }
        echo '</table>
        </form>
      </div>';
    }
    function showacademy($origen,$fini,$ffin,$estatus){ 
      if($_REQUEST['origen'] == "1") {$origen1 = 'selected'; $ac = "JD SHOP";}
      elseif($_REQUEST['origen'] == "2") {$origen2 = 'selected'; $ac = "JD SUITE";}
      else $origen = 'selected';

      if($estatus == "R") $selr = 'selected';
      elseif($estatus == "B") $sela = 'selected';
      else $selt = 'selected';

      if($estatus == "R" || $estatus == "R") $title = "Registros de Academia JD SHOP";
      else $title = 'Registros de Boletín Academia JD SHOP';
      
      $nrows = $this->model->result->num_rows;

      echo '<div class="page-title-actions">
        <div class="form-inline mb-1">
          <div class="btn-group" role="group">
            <a type="button" class="btn btn-success text-white" href="?modulo=registrosw&accion=index">
              <i class="icon-world"></i> Registros Distribuidor
            </a>
            <a type="button" class="btn btn-info active text-white" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="icon-world"></i> Registros Academy
            </a>
            <a type="button" class="btn btn-warning btn-darken-2 text-white" href="?modulo=registrosw&accion=registrosboletin">
              <i class="icon-world"></i> Registros Boletín
            </a>
          </div>
          <button accesskey="L" id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
            <span class="tag tag-pill tag-danger" bis_skin_checked="1">'.$nrows.'</span>
          </button>
        </div>';

        echo'<form autocomplete="off" action="?modulo=registrosw&accion=registrosacademy" method="post" onsubmit="return checkSubmitenviar();" class="row form-inline mb-1" id="filtro">
          <div id="filter-panel" class="collapse filter-panel col-md-12">
            <div class="mb-5">
              <label class="mr-sm-2">Desde:</label>
              <input type="date" id="fini" name="fini" value="'.$fini.'" class="form-control">
            </div>
            <div class="mb-5">
              <label class="mr-sm-2">Hasta:</label>
              <input type="date" id="ffin" name="ffin" value="'.$ffin.'" class="form-control">
            </div>';
            echo'&nbsp;<div class="mb-5">
              <label class="mr-sm-2">Estatus:</label>';
              echo '<select name="estatus" class="form-control">
                <option value="" '.$selt.'>Todos</option>
                <option value="R" '.$selr.'>Registrados</option>
                <option value="B" '.$sela.'>Boletín</option>';
              echo '</select>';
            echo '</div>';
            echo'<div class="mb-5 ml-1">
              <label style="color:transparent">Filtrar:</label><br>
              <button type="submit" class="btn btn-info"><i class="icon-filter2"></i> Filtrar</button>
            </div>
            <div class="mb-5">
              <label style="color:transparent">Limpiar:</label><br>
              <a href="?modulo=registrosw&accion=registrosacademy" class="btn btn-warning"><i class="fa fa-times"></i> Limpiar</a>
            </div>
          </div>
        </form>
      </div>';

      echo '<div class="card row p-1">';
        echo'<div class="col-md-12">
          <h3>'.$title.'</h3>
          <hr>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead class="bg-darken-2 bg-light-blue text-white">
                <tr>
                  <th>No.</th>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Fecha Registro</th>
                  <th>Origen</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>';
                
                while($row = $this->model->result->fetch_array()){
                  if($row['ra_origen'] == "1") $origen = 'ACADEMY JD SHOP';  
                  elseif($row['ra_origen'] == "2") $origen = 'JDSUITE';

                  if($row['ra_registrado'] == "1"){
                    $class = 'green';
                    $icon = 'fa fa-check';
                  }else{
                    $class = 'danger';
                    $icon = 'fa fa-times';
                  }
                  $idAcademy = buscaAcademy($row['ra_correo'],'usuarios','u_correo','u_id');
                  echo '<tr>
                    <td class="text-xs-center">'.$nrows.'</td>
                    <td nowrap>'.buscaAcademy($row['ra_correo'],'usuarios','u_correo','u_nmb').'</td>
                    <td nowrap>'.$row['ra_correo'].'</td>
                    <td>'.fecha_formato($row['ra_fechar'],false,false).'</td>
                    <td>'.$origen.'</td>
                    <td>';
                    if($row['ra_registrado'] == "1")
                      echo'<a href="?modulo=registrosw&accion=showcliente&id='.$idAcademy.'">
                        <button class="btn btn-primary" type="button">
                          <i class="fa fa-eye"></i>
                        </button>
                      </a>';
                    echo'</td>
                  <tr>';
                  $nrows--;
                }
              echo '</tbody>
            </table>
          </div>
        </div>';
      echo'</div>';
    }
    function showboletin(){
      echo '<div class="btn-group mb-2" role="group">
        <a type="button" class="btn btn-success text-white" href="?modulo=registrosw&accion=index">
          <i class="icon-world"></i> Registros Distribuidor
        </a>
        <a type="button" class="btn btn-info text-white" href="?modulo=registrosw&accion=registrosacademy">
          <i class="icon-world"></i> Registros Academy
        </a>
        <a type="button" class="btn btn-warning btn-darken-2 text-white">
          <i class="icon-world"></i> Registros Boletín
        </a>
      </div>';
      echo '<div class="card row p-1">
        <div class="col-md-12">
          <h3>Registros de Boletín</h3>
          <hr>
          <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
              <thead class="bg-darken-2 bg-light-blue text-white">
                <tr>
                  <th width="15%">No</th>
                  <th>Correo</th>
                </tr>
              </thead>
              <tbody>';
                $no = 1;
                while($row = $this->model->result->fetch_array()){
                  echo '<tr>
                    <td>'.$no.'</td>
                    <td>'.$row['b_correo'].'</td>
                  <tr>';
                  $no++;
                }
              echo '</tbody>
            </table>
          </div>
        </div>
      </div>';
    }
    function showcliente(){
      echo '<div class="row page-title-actions">';
        //Sección: E2 Encabezado - Filtros
        echo '<a class="btn btn-warning" href="?modulo=registrosw&accion=registrosacademy"><i class="fa fa-arrow-left"></i> Atras</a>
      </div>';
      $rowinf = $this->model->resulti->fetch_array();
      $ncursos = $this->model->resultpp->num_rows;
      $txtc = 'Curso';
      if($ncursos > 1) $txtc = 'Cursos';
      echo '<div class="row mt-2">
        <div class="table">
          <div class="table-responsive">
            <center>
              <table class="table table-hover table-striped">
                <thead class="thead bg-blue bg-darken-3 text-white">
                  <tr>
                    <th colspan="7">Información básica de "'.$rowinf['u_nmb'].' '.$rowinf['u_apellidos'].'"</th>
                  </tr>
                  <tr>
                    <th style="text-align: center;">Nombre(s)</th>
                    <th style="text-align: center;">Apellidos</th>
                    <th style="text-align: center;">Correo</th>
                    <th style="text-align: center;">Telefono</th>
                    <th style="text-align: center;">Fecha de registro</th>
                    <th style="text-align: center;">Ciudad</th>
                    <th style="text-align: center;">Cursos comprados</th>
                  </tr>
                </thead>
                <tbody>';
                  echo '<tr>
                    <td nowrap style="text-align: center;">'.$rowinf['u_nmb'].'</td>
                    <td nowrap style="text-align: center;">'.$rowinf['u_apellidos'].'</td>
                    <td nowrap style="text-align: center;">'.$rowinf['u_correo'].'</td>
                    <td nowrap style="text-align: center;">'.$rowinf['u_telefono'].'</td>
                    <td nowrap style="text-align: center;">'.date("d-m-Y",strtotime($rowinf['u_fechar'])).'</td>
                    <td nowrap style="text-align: center;">'.$rowinf['u_ciudad'].'</td>
                    <td nowrap style="text-align: center;">'.$ncursos.' '.$txtc.'</td>
                  </tr>
                  <tr style="height: 20px;"></tr>
                  <tr class="thead bg-blue bg-darken-3 text-white">
                    <th colspan="7">Información básica de "'.$rowinf['u_nmb'].' '.$rowinf['u_apellidos'].'"</th>
                  </tr>';
                  if($rowinf['u_rfc']){
                    if($rowinf['u_numi'] || $rowinf['u_numi'] != 0) $ni = ', #'.$rowinf['u_numi'];
                    $dir = $rowinf['u_calle'].', #'.$rowinf['u_nume'].$ni.', '.$rowinf['u_colonia'].', '.$rowinf['u_cp'].', '.$rowinf['u_municipio'].', '.buscaAcademy($rowinf['u_estado'],'estado','e_id','e_nmb').', '.$rowinf['u_pais'];
                    echo'<tr class="thead bg-blue bg-darken-3 text-white">
                      <th style="text-align: center;">Razón social</th>
                      <th style="text-align: center;">RFC</th>
                      <th style="text-align: center;">Regimen fiscal</th>
                      <th style="text-align: center;" colspan="4">Dirección</th>
                    </tr>
                    <tr>
                      <td nowrap style="text-align: center;">'.$rowinf['u_razon'].'</td>
                      <td nowrap style="text-align: center;">'.$rowinf['u_rfc'].'</td>
                      <td nowrap style="text-align: center;">'.$rowinf['u_regimenfis'].' - '.buscaAcademy($rowinf['u_regimenfis'],'cfdi_regimenfiscal','cr_regimen','cr_descripcion').'</td>
                      <td nowrap style="text-align: center;" colspan="4">'.$dir.'</td>
                    </tr>';
                  }
                  else {
                    echo'<tr>
                      <td nowrap style="text-align: center;" colspan="7"><h4><strong>Sin datos capturados</strong></h4></td>
                    </tr>';
                  }
                echo'</tbody>
              </table>
            </center>
          </div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="table">
          <div class="table-responsive">
            <center>
              <table class="table table-hover table-striped">
                <thead class="thead bg-blue bg-darken-3 text-white">
                  <tr>
                    <th colspan="7">Reporte de cursos comprados de "'.$rowinf['u_nmb'].' '.$rowinf['u_apellidos'].'"</th>
                  </tr>
                  <tr>
                    <th width="5%">No.</th>
                    <th style="" colspan="2">Curso</th>
                    <th style="">Fecha pedido</th>
                    <th style="">Forma de pago</th>
                    <th style="width">Facturado</th>
                    <th style="">Estatus</th>
                  </tr>
                </thead>
                <tbody>';
                  if($ncursos > 0){
                    $i = $ncursos;
                    while($rowc = $this->model->resultp->fetch_array()){
                      if($rowc['up_facturado'] == "1"){
                        $class = 'green';
                        $icon = 'fa fa-check';
                      }else{
                        $class = 'danger';
                        $icon = 'fa fa-times';
                      }
                      echo'<tr>
                        <td style="width:10%;">'.$i.'</td>
                        <td style="" colspan="2">'.$rowc['c_nmb'].'</td>
                        <td style="" nowrap>'.date("d-m-Y H:i:s",strtotime($rowc['up_fecha'])).'</td>
                        <td style="">'.$rowc['f_nmb'].'</td>
                        <td style=""><i class=" '.$class.' '.$icon.'"></td>
                        <td style="background:'.$this->model->estc[$rowc['up_estatus']].'">'.$this->model->estp[$rowc['up_estatus']].'</td>
                      </tr>';
                      $i--;
                    }
                  }else{
                    echo '<tr>
                      <td nowrap style="text-align: center;" colspan="6"><h4><strong>Sin cursos comprados</strong></h4></td>
                    </tr>';
                  }
                echo'</tbody>
              </table>
            </center>
          </div>
        </div>
      </div>';
    }
    function dictamen($reporte){
      ?><script>
        function dictamen(estatus){
          document.getElementById("estatus").value = estatus;

          document.getElementById("formdictamen").submit();
        }
      </script><?php
      echo '<div class="row page-title-actions">';
        //Sección: E2 Encabezado - Filtros
        //$rowinf = $this->model->result->fetch_array();
        echo '<a class="btn btn-warning" href="?modulo=registrosw&accion=registrosacademy&p=1"><i class="fa fa-arrow-left"></i> Atras</a>';
        if($this->model->estatus == "R"){
          echo'&nbsp;<button type="button" class="btn btn-success" data-bs-toggle="modal" data-target="#dictamen">
            <i class="fa fa-check"></i> Dictamen
          </button>';
        }
      echo'</div>';
      //Modal - APROBAR O DENEGAR PAGO RECIBIDO
      echo '<div class="modal fade text-xs-left" id="dictamen" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <label class="modal-title text-text-bold-600" id="myModalLabel33">Reporte final de pago</label>
            </div>
            <form method="post" action="?modulo=registrosw&accion=finalizaracademy&id='.$_GET['id'].'" autocomplete="off" id="formdictamen">
              <input type="hidden" name="estatus" id="estatus">
              <input type="hidden" name="cliente" value="'.$this->model->cliente.'">
              <input type="hidden" name="pedido" value="'.$this->model->pedido.'">
              <div class="modal-body row">
                <div class="col-md-12">
                  <div class="mb-5">  
                    <label>Observaciones:*</label>
                    <textarea id="comentarios" name="comentarios" placeholder="Comentarios a enviar del dictamen por correo electronico" rows="4" class="form-control"></textarea>
                  </div>
                  <div id="suggestions-block"></div>
                </div>
              </div>
              <div class="modal-footer p-2">
                <button type="button" onclick="dictamen('."'A'".')" class="btn btn-success"><i class="fa fa-check"></i> Aprobar</button>
                <button type="button" onclick="dictamen('."'C'".')" class="btn btn-danger"><i class="fa fa-times"></i> Denegar</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal" aria-label="Close"><i class="icon-alert-circled"></i> Cancelar</button>
              </div>
            </form>
          </div>
        </div>
      </div>';
      echo'<div class="row mt-2">
        <div class="table">
          <div class="table-responsive">
            <center>
              <table class="table table-hover table-striped">
                <thead class="thead bg-blue bg-darken-3 text-white">
                  <tr>
                    <th colspan="6">Pedido de  "'.$this->model->nmb.' '.$this->model->apellidos.'" '.$this->model->correo.'</th>
                  </tr>
                  <tr>
                    <th colspan="3">Curso</th>
                    <th>Fecha de pedido</th>
                    <th colspan="2">Estatus</th>
                  </tr>
                </thead>
                <tbody>';
                  echo '<tr>
                    <td colspan="3">'.$this->model->curso.'</td>
                    <td nowrap>'.$this->model->fechapedido.'</td>
                    <td nowrap colspan="2" style="background:'.$this->model->estc[$this->model->estatus].'">'.$this->model->estp[$this->model->estatus].'</td>
                  </tr>
                  <tr style="height: 20px;"></tr>
                  <tr class="thead bg-blue bg-darken-3 text-white">
                    <th colspan="6">Información adicional</th>
                  </tr>';
                  echo'<tr>
                    <th nowrap><b>FORMA DE PAGO</b></th>
                    <th nowrap><b>FECHA DE REPORTE</b></th>
                    <th><b>ADJUNTO DE REPORTE</b></th>
                    <th nowrap colspan="2"><b>OBSERVACIONES</b></th>
                  </tr>';
                  echo'<tr>
                    <td nowrap>'.$this->model->formapago.'</td>
                    <td nowrap>'.$this->model->fechareporte.' '.$this->model->horareporte.'</td>
                    <td>';
                      if($this->model->adjunto)
                        echo'<a target="_BLANK" href="../../jdshop.mx/escuelavirtual/'.$this->model->adjunto.'">
                          <button type="button" class="btn btn-secondary"><i class="icon-attachment"></i> '.$this->model->adjunto.'</button>
                        </a>';
                      else echo'<b>Sin adjunto</b>';
                    echo'</td>
                    <td nowrap colspan="2">'.$this->model->observacion.'</td>
                  </tr>';
                  $rowspan = '';
                  $colspan = '';
                  if($this->model->factura == "1") {$rowspan = 'rowspan="2"'; $colspan = 'colspan="4"';}
                  echo'<tr>
                    <th '.$colspan.' nowrap><b>FACTURACIÓN</b></th>
                    <th '.$rowspan.' nowrap>
                      <b>Remisión</b> &nbsp;&nbsp;
                      <a href="?modulo=remisiones&accion=show&id='.$this->model->remision.'">
                        <button type="button" class="btn btn-sm btn-primary">
                          <i class="fa fa-eye"></i>
                        </button>
                      </a>
                    </th>
                  </tr>';
                  $estrem = busca($this->model->remision,'remisiones','r_id','r_estatus');
                  if($estrem == "A"){
                    $remision = '<td nowrap style="text-align: center;'.$this->model->backestatusc[busca($this->model->remision,'cxcobrar','cx_tipo = "R" AND cx_referencia','cx_estatus')].'">
                      '.$this->model->txtestatusc[busca($this->model->remision,'cxcobrar','cx_tipo = "R" AND cx_referencia','cx_estatus')].'
                    </td>';
                  }else{
                    $remision = '<td nowrap style="text-align: center;" class="'.$this->model->backestatus[$estrem].'">'.$this->model->txtestatus[$estrem].'</td>';
                  }
                  echo'<tr>';
                    if($this->model->factura == "1"){
                      if($this->model->numi || $this->model->numi != 0) $ni = ', #'.$this->model->numi;
                      $dir = $this->model->calle.', #'.$this->model->nume.$ni.', '.$this->model->colonia.', '.$this->model->cp.', '.$this->model->municipio.', '.buscaAcademy($this->model->estado,'estado','e_id','e_nmb').', '.$this->model->pais;
                      echo'
                        <th style="text-align: center;">Razón social</th>
                        <th style="text-align: center;">RFC</th>
                        <th style="text-align: center;">Regimen fiscal</th>
                        <th style="text-align: center;">Dirección</th>
                      </tr>
                      <tr>
                        <td nowrap style="text-align: center;">'.$this->model->razonsocial.'</td>
                        <td nowrap style="text-align: center;">'.$this->model->rfc.'</td>
                        <td style="text-align: center;">'.$this->model->regimen.' - '.buscaAcademy($this->model->regimen,'cfdi_regimenfiscal','cr_regimen','cr_descripcion').'</td>
                        <td style="text-align: center;">'.$dir.'</td>
                        '.$remision.'
                      </tr>';
                    }else{
                      echo'<td '.$colspan.'>No</td>
                      '.$remision.'';
                    }
                  echo'</tr>';
                echo'</tbody>
              </table>
            </center>
          </div>
        </div>
      </div>';

      echo '<div class="row mt-2">
        <div class="table">
          <div class="table-responsive">
            <center>
              <table class="table table-hover table-striped">
                <thead class="thead bg-blue bg-darken-3 text-white">
                  <tr>
                    <th colspan="6">Detalle del pedido</th>
                  </tr>
                  <tr>
                    <th>Modelo</th>
                    <th colspan="2">Articulo</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Importe</th>
                  </tr>
                </thead>
                <tbody>';
                  $sqlrem = 'SELECT * FROM remisionesd WHERE rd_remision = "'.$this->model->remision.'"';
                  $resultrem = setq($sqlrem);
                  $sub = 0;
                  $total = 0;
                  $descuento = 0;
                  while($rowrem = $resultrem->fetch_array()){
                    if(!busca($rowrem['rd_articulo'],'articulos','a_id','a_modelo')) $modelo = '-';
                    else $modelo = busca($rowrem['rd_articulo'],'articulos','a_id','a_modelo');
                    $importe = ($rowrem['rd_precio'] * $rowrem['rd_cantidad']);
                    echo'<tr>
                      <td>'.$modelo.'</td>
                      <td colspan="2">'.$rowrem['rd_nmbarticulo'].'</td>
                      <td>$'.number_format($rowrem['rd_precio'],2).'</td>
                      <td>'.number_format($rowrem['rd_cantidad'],2).'</td>
                      <td>$'.number_format($importe,2).'</td>
                    </tr>';
                  }
                echo'</tbody>
                <tfoot>
                  <tr>
                    <th colspan="4" style="text-align:right;">Subtotal</th>
                    <th colspan="2" style="text-align:right;">$ '.busca($this->model->remision,'remisiones','r_id','r_subtotal').'</th>
                  </tr>
                  <tr>
                    <th colspan="4" style="text-align:right;">Descuento</th>
                    <th colspan="2" style="text-align:right;">$ '.busca($this->model->remision,'remisiones','r_id','r_mdescuento').'</th>
                  </tr>
                  <tr>
                    <th colspan="4" style="text-align:right;">Total</th>
                    <th colspan="2" style="text-align:right;">$ '.busca($this->model->remision,'remisiones','r_id','r_total').'</th>
                  </tr>
                </tfoot>
              </table>
            </center>
          </div>
        </div>
      </div>';
    }
    function resumen($fini,$ffin){
      echo '<div class="page-title-actions">
        <div class="form-inline mb-1">
          <div class="btn-group" role="group">
            <a class="btn btn-secondary active">
              <i class="fa fa-info"></i> Resumen
            </a>
            <a type="button" class="btn btn-success text-white" href="?modulo=registrosw&accion=index">
              <i class="icon-world"></i> Registros Distribuidor
            </a>
            <a type="button" class="btn btn-info text-white" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="icon-world"></i> Registros Academy
            </a>
            <a type="button" class="btn btn-warning btn-darken-2 text-white" href="?modulo=registrosw&accion=registrosboletin">
              <i class="icon-world"></i> Registros Boletín
            </a>
          </div>
          <button accesskey="L" id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
          </button>
        </div>';

        echo'<form autocomplete="off" action="?modulo=registrosw&accion=resumen" method="post" onsubmit="return checkSubmitenviar();" class="row form-inline mb-1" id="filtro">
          <div id="filter-panel" class="collapse filter-panel col-md-12">
            <div class="mb-5">
              <label class="mr-sm-2">Desde:</label>
              <input type="date" id="fini" name="fini" value="'.$fini.'" class="form-control">
            </div>
            <div class="mb-5">
              <label class="mr-sm-2">Hasta:</label>
              <input type="date" id="ffin" name="ffin" value="'.$ffin.'" class="form-control">
            </div>';
            echo'<div class="mb-5 ml-1">
              <label style="color:transparent">Filtrar:</label><br>
              <button type="submit" class="btn btn-info"><i class="icon-filter2"></i> Filtrar</button>
            </div>
            <div class="mb-5">
              <label style="color:transparent">Limpiar:</label><br>
              <a href="?modulo=registrosw&accion=resumen" class="btn btn-warning"><i class="fa fa-times"></i> Limpiar</a>
            </div>
          </div>
        </form>
      </div>';

      echo '<div class="card row p-1">';
        echo'<div class="col-md-12">
          <h3>Registros de distribuidor por autorizar</h3>
          <hr>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead class="bg-darken-2 bg-light-blue text-white">
                <tr>
                  <th>Nombre</th>
                  <th>Telefono</th>
                  <th>Correo</th>
                  <th>Fecha</th>
                  <th>Comentarios</th>
                  <th></th>
                  <th>Estatus</th>
                </tr>
              </thead>
              <tbody>';
                while ($row = $this->model->resultdistribuidor->fetch_array()) {
                  echo '<tr>
                    <td>'.$row['rd_nmb'].'</td>
                    <td>'.$row['rd_correo'].'</td>
                    <td>'.$row['rd_telefono'].'</td>
                    <td>'.fecha_formato($row['rd_fecharegistro'],false,false).'</td>
                    <td>'.$row['rd_comentarios'].'</td>
                    <td>
                      <a href="?modulo=registrosw&accion=show&id='.$row['rd_id'].'">
                        <button type="button" class="btn btn-info">
                          <i class="icon-chevron-right2"></i> Ver solicitud
                        </button>
                      </a>
                    </td>';
                    echo '<td class="bg-'.$this->model->estatusc[$row['rd_estatus']].'">'.$this->model->estatuse[$row['rd_estatus']].'</td>
                  </tr>';
                }
              echo '</tbody>
            </table>
          </div>
        </div>';
        echo'<div class="col-md-12">
          <h3>Reportes de pagos de academy por autorizar</h3>
          <hr>
          <div class="table-responsive">
            <table class="table table-striped table-hover ">
              <thead class="bg-darken-2 bg-light-blue text-white">
                <tr>
                  <th>Cliente</th>
                  <th>Fecha de pedido</th>
                  <th>Fecha de reporte</th>
                  <th>Total</th>
                  <th>Estatus</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>';
                while($rowrp = $this->model->resultpacademy->fetch_array()){
                  echo'<tr>
                    <td nowrap>'.$rowrp['u_nmb'].' '.$rowrp['u_apellidos'].'</td>
                    <td nowrap>'.$rowrp['up_fecha'].'</td>
                    <td nowrap>'.$rowrp['r_fecha'].' '.$rowrp['r_hora'].'</td>
                    <td nowrap class="number-align">$'.$rowrp['c_costo'].'</td>
                    <td nowrap style="background-color:'.$this->model->estc[$rowrp['r_estatus']].'">'.$this->model->estp[$rowrp['r_estatus']].'</td>
                    <td nowrap>';
                      if($rowrp['r_estatus'] == "A" || $rowrp['r_estatus'] == "C") {$txtbtn = 'Ver pedido'; $stbtn = 'btn-info';}
                      elseif($rowrp['r_estatus'] == "R") {$txtbtn = 'Ver pedido'; $stbtn = 'btn-success';}

                      echo'<a href="?modulo=registrosw&accion=dictamen&id='.$rowrp['r_id'].'">
                        <button class="btn '.$stbtn.'" type="button">
                          <i class="icon-list-alt"></i> '.$txtbtn.'
                        </button>
                      </a>';
                    echo'</td>
                  </tr>';
                }
              echo'</tbody>
            </table>
          </div>
        </div>';
        echo '';
      echo'</div>';
    }
  }
?>