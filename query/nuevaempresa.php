<?php
  session_start();
  //ini_set('diplay_errors',1);
  include('../funciones.php');

  function getmaxjdsuite($key,$table,$cond=false,$sumar=true){
    $sql = 'SELECT MAX('.$key.') FROM '.$table.' ';
    if($cond) $sql.=' WHERE '.$cond;
    $result = setqjdsuite($sql);
    list($id) = $result->fetch_array();
    if($sumar) $id++;
    return($id);
  }

  $nmbemp = $_POST['nmbempresa'];
  $siglas = $_POST['siglas'];
  $correous = $_POST['correous'];
  $idcliente = $_POST['idcliente'];
  $idlicencia = $_POST['idlicencia'];
  $econtrato = "1";
  $passrand = cadena_aleatoria();
  $passrandupp = strtoupper($passrand);
  $nmbuser = 'ADMIN'.$siglas.'';

  $maxidemp = getmaxjdsuite('e_id','empresas');
  $sqlinemp = 'INSERT INTO empresas SET
                e_id = "'.$maxidemp.'",
                e_nmb = "'.$nmbemp.'",
                e_siglas = "'.$siglas.'",
                e_contrato = "'.$econtrato.'"';
  setqjdsuite($sqlinemp);

  $maxidprov = getmaxjdsuite('p_id','proveedores');
  $sqlinprov = 'INSERT INTO proveedores SET
                p_id = "'.$maxidprov.'",
                p_empresa = "'.$maxidemp.'",
                p_nmb = "'.$nmbemp.'",
                p_alias = "'.$nmbemp.'",
                p_estatus = "A",
                p_predeterminado = "1"';
  setqjdsuite($sqlinprov);

  $maxidalm = getmaxjdsuite('a_id','almacenes');
  $sqlinalm = 'INSERT INTO almacenes SET
              a_id = "'.$maxidalm.'",
              a_nmb = "ALMACEN PRINCIPAL '.$siglas.'",
              a_vendible = "1",
              a_empresa = "'.$maxidemp.'",
              a_estatus = "A"';
  setqjdsuite($sqlinalm);

  $maxidesq = getmaxjdsuite('ep_id','esquema_precio');
  $sqlesq = 'INSERT INTO esquema_precio SET
              ep_id = "'.$maxidesq.'",
              ep_empresa = "'.$maxidemp.'",
              ep_nmb = "PUBLICO EN GENERAL",
              ep_base = "C",
              ep_precio = "'.$maxidesq.'",
              ep_masmenos = "+",
              ep_sumarp = "20",
              ep_miva = "1",
              ep_sumarf = "0.00",
              ep_puntodv = "1",
              ep_estatus = "A"';
  setqjdsuite($sqlesq);
  //// FALTA SABER EL ep_sumarp

  $maxidcli = getmaxjdsuite('c_id','crm_clientes');
  $sqlincli = 'INSERT INTO crm_clientes SET
                c_id = "'.$maxidcli.'",
                c_empresa = "'.$maxidemp.'",
                c_almacen = "'.$maxidalm.'",
                c_alias = "PUBLICO EN GENERAL",
                c_nmb = "PUBLICO EN GENERAL",
                c_apellidos = "PUBLICO EN GENERAL",
                c_tipo = "C",
                c_fregistro = "'.date('Y-m-d').'",
                c_origen = "1",
                c_precio = "'.$maxidesq.'",
                c_publico = "1"';
  setqjdsuite($sqlincli);

  $sqlINVE = 'INSERT INTO grupos_empresas SET
                ge_grupo = "INVE",
                ge_empresa = "'.$maxidemp.'",
                ge_estatus = "A"';
  setqjdsuite($sqlINVE);

  $sqlADMIN = 'INSERT INTO grupos_empresas SET
                ge_grupo = "ADMIN",
                ge_empresa = "'.$maxidemp.'",
                ge_estatus = "A"';
  setqjdsuite($sqlADMIN);

  $sqlVNTS = 'INSERT INTO grupos_empresas SET
              ge_grupo = "VNTS",
              ge_empresa = "'.$maxidemp.'",
              ge_estatus = "A"';
  setqjdsuite($sqlVNTS);

  $sqlCONTA = 'INSERT INTO grupos_empresas SET
              ge_grupo = "CONTA",
              ge_empresa = "'.$maxidemp.'",
              ge_estatus = "A"';
  setqjdsuite($sqlCONTA);

  $sqluser = 'INSERT INTO usuarios SET
              u_empresa = "'.$maxidemp.'",
              u_id = "'.$nmbuser.'",
              u_password = PASSWORD("'.$passrandupp.'"),
              u_grupo = "ADMIN",
              u_nmb = "ADMIN '.$siglas.'",
              u_estatus = "A",
              u_correo = "'.$correous.'"';
  setqjdsuite($sqluser);

  $sqluseremp = 'INSERT INTO usuario_empresa SET
                ue_usuario = "'.$nmbuser.'",
                ue_empresa = "'.$maxidemp.'"';
  setqjdsuite($sqluseremp);

  $sqllicencia = 'UPDATE licencias SET l_estatus = "A"
                  WHERE l_id = "'.$idlicencia.'"';
  setq($sqllicencia);

  $sqlicencia = 'UPDATE licencias SET l_folder = "'.$maxidemp.'"
                  WHERE l_id = "'.$idlicencia.'"';
  setq($sqlicencia);

  $sqlemplic = 'UPDATE empresa_licencia SET
                el_empresa = "'.$maxidemp.'",
                el_useradmin = "'.$nmbuser.'",
                el_passadmin = "'.$passrand.'"
                WHERE el_licencia = "'.$idlicencia.'"';
  setqjdsuite($sqlemplic);

  $sqlinf = 'INSERT INTO foliosinf SET
              f_tipofac = "F",
              f_empresa = "'.$maxidemp.'",
              f_serie = "A",
              f_folio = "1"';
  setqjdsuite($sqlinf);
  $sqlinf = 'INSERT INTO foliosinf SET
              f_tipofac = "P",
              f_empresa = "'.$maxidemp.'",
              f_serie = "CP",
              f_folio = "1"';
  setqjdsuite($sqlinf);
  $sqlinf = 'INSERT INTO foliosinf SET
              f_tipofac = "N",
              f_empresa = "'.$maxidemp.'",
              f_serie = "NC",
              f_folio = "1"';
  setqjdsuite($sqlinf);

  //ENVIO DE CORREO CON LOS DATOS DE ACCESO
  require_once('../lib/mailer/class.phpmailer.php');
  require_once("../lib/mailer/class.smtp.php");
  //instancio un objeto de la clase PHPMailer
  $asesor  = buscajdsuite($idlicencia,'empresa_licencia','el_licencia','el_asesor');
  $nmbasesor = busca($asesor,'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)');

  $mail = new PHPMailer(); // defaults to using php "mail()"
  $mensaje2 = '<div>
    <div style=" position: absolute;">
      <div>
        <img src="../images/headermail.png">
      </div>
      <div style="font-size: small; font-family: system-ui; padding: 20px;">
        Estimado: <b>'.$nmbemp.'</b>.<br>
        El registro de su empresa ha sido concluido satisfactoriamente en nuestro sistema de JD CEO v2.0. <br>
        La siguiente información es proporcionada para lograr el accesso al sistema: <br><br>
        Usuario: <b>'.$nmbuser.' </b><br>
        Contraseña: <b>'.$passrand.' </b> <br>
        Asesor de soporte asignado: <b>'.$nmbasesor.'</b> <br>
        Accede al sistema desde <a href="https://jdceo.jdsuite.mx/">aquí</a>.<br>
        Puedes visualizar los tutoriales de JDCEO pulsando <a href="https://jdceo.jdsuite.mx/index?modulo=soporte&accion=tutoriales">aquí</a>.<br> 
        Favor de adjuntar la Cedula de Identificación Fiscal vía correo a soporte@jdsuite.mx, indicando el nombre de su empresa en el cuerpo del mismo. 
      </div>
      <div>
        <a href="https://www.jdsuite.mx/sistema-erp-crm"><img src="../images/footermail.png" style="cursor: pointer;"></a>
      </div>
      <div style="padding: 10px; padding-left: 10px;">
        <a href="https://www.facebook.com/suiteJD"><img src="../images/fb-icon.png" style="padding: 10px; cursor: pointer;"></a>
        <a href="https://www.instagram.com/jd_suite/"><img src="../images/ig-icon.png" style="padding: 10px; cursor: pointer;"></a>
        <a href="https://www.youtube.com/channel/UCt9ZVvOvsXnMpk_g7X1boNg"><img src="../images/yt-icon.png" style="padding: 10px; cursor: pointer;"></a>
      </div>
      <div style="font-size: small; font-family: system-ui; font-style: italic; padding-left: 20px;">
        Mensaje generado automáticamente por JD CEO 2.0
      </div>
    </div>
  </div>';
  $subject = 'Alta JD CEO';
  
  $body= (utf8_decode($mensaje2));
  //defino el email y nombre del remitente del mensaje
  $mail->setFrom('ventas@jdsuite.mx', utf8_decode('JDCEO'));
  $mail->addCC('develop@tye-solutions.com');
  $mail->AddAddress($correous, utf8_decode($nmbemp));
  //Añado un asunto al mensaje
  $mail->Subject =utf8_decode($subject);
  //Puedo definir un cuerpo alternativo del mensaje, que contenga solo texto
  $mail->AltBody ="Cuerpo alternativo del mensaje";
  //inserto el texto del mensaje en formato HTML
  $mail->MsgHTML($body);

  $mail->Host = 'jdsuite.mx';
  $mail->Port = 587;
  $mail->IsSMTP();
  //$mail->SMTPSecure = 'ssl';
  $mail->SMTPAuth = true; // turn on SMTP authentication
  $mail->Username = 'ventas@jdsuite.mx';
  $mail->Password = 'WpJDTye2020#';
  //$mail->SMTPDebug = 2;
  //envío el mensaje, comprobando si se envió correctamente
  if($mail->Send()){
  }else{
    echo 'Correo no enviado. <br>
      La contraseña del usuario: '.$nmbuser.', es la siguiente: '.$passrand.' <br>
      Cierre la ventana para no registrar nuevamente la empresa y guarde los datos de usuario y contraseña.';
    die();
  }
  redirect('../index?modulo=licencias&accion=show&id='.$idlicencia.'');
?>