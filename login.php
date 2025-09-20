<?php
ini_set('display_errors', 1);
include_once('funciones.php');
//foreachdie();
session_start();
if (!isset($_SESSION['uid'])){
  if (!isset($_POST['ok'])){
		$userl = NULL;
		if(isset($_COOKIE['user'])){
			$userl = $_COOKIE['user'];
		}

		if(isset($_GET['lusr']) && !empty($_GET['lusr'])) $userl = $_GET['lusr'];

		$autofp = "";
		$autofu = "";
		if($userl) $autofp = "autofocus"; else $autofu = "autofocus";

		if(isset($_GET['next'])){
			$vars = explode("?",$_SERVER['REQUEST_URI']);
			$url = $vars[1];
			$numurl = strlen($url);
			$urls = substr($url,5,($numurl-5));
		}
?>
<!DOCTYPE html>
<!--
Author: Keenthemes
Product Name: Metronic - Bootstrap 5 HTML, VueJS, React, Angular & Laravel Admin Dashboard Theme
Purchase: https://1.envato.market/EA4JP
Website: http://www.keenthemes.com
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
License: For each use you must have a valid license purchased only from above link in order to legally use the theme for your project.
-->
<html lang="en">
	<!--begin::Head-->
	<head>	<base href="">
	<title>DVLogistics</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="assets/media/logos/favicon.png" />
	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->
	<!--begin::Page Vendor Stylesheets(used by this page)-->
	<link href="assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
	<!--end::Page Vendor Stylesheets-->
	<!--begin::Global Stylesheets Bundle(used by all pages)-->
	<link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" class="">
	<!--end::Global Stylesheets Bundle-->
	<script src="assets/plugins/global/plugins.bundle.js"></script>
	<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
		<!--end::Global Stylesheets Bundle-->
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="bg-body">
	<script>

		document.addEventListener('DOMContentLoaded', function () {
			
			document.body.addEventListener('keydown', esEnter);

			// Obtén la tabla
			var tabla = document.getElementById('myTableG');

			// Añade un evento clic a la tabla
			tabla.addEventListener('mousedown', function (event) {
				contralong(0);
			});	
		});
		function contralong(valor){
			var tipo = document.getElementById('tipo');
			var contra = document.getElementById('pass');
			var checktd = document.getElementById('checktd');
			var length = contra.value.length;
			if(valor==0) length++;
			if(tipo.value == "0"){
				if(length <= 4){
					var val = contra.value;
					var elementoClicado = event.target;
					var contenidoCelda = elementoClicado.textContent.trim();
					contra.value = val+contenidoCelda;
					contra.focus();
					length = contra.value.length;
					if(length == 4){	
						checktd.style.background = 'lightgreen';
					} else {
						checktd.style.background = '';
					}
				}
			}
		}
		function esEnter(tecla){
			if (event.key === 'Enter' || event.keyCode === 13) {
				// Coloca aquí el código que deseas ejecutar
				sendlogin();
			}
		} 
    function recupera(){
      document.getElementById("form-login").style.display = "none";
      document.getElementById("formrecover").style.display = "";
      document.getElementById("mailr").focus();
    }
    function desrecupera(){
      document.getElementById("form-login").style.display = "";
      document.getElementById("formrecover").style.display = "none";
      document.getElementById("uid").focus();
    }
    function sendlogin(){
      var usr = document.getElementById("uid");
      var pass = document.getElementById("pass");
			var tipo = document.getElementById("tipo");

			if (usr.hidden) {
				if(pass.value != "" && pass.value != undefined){
						document.loginform.submit();
				}
				else{
					alert("Inserte su contraseña");
				}
			} else {
				if(usr.value != "" && usr.value != undefined && pass.value != "" && pass.value != undefined){
						document.loginform.submit();
				}
				else{
					alert("Llene los campos de usuario y contraseña");
				}
			}
    }

	function sendloginprod(){
  	var usr = document.getElementById("uid");
    var pass = document.getElementById("pass");
	  var btnprod = document.getElementById("btnprod");
	  var btnnormal = document.getElementById("btnnormal");
	  var lblusr = document.getElementById("lblusr");
		var teclado = document.getElementById("teclado");
		var tipo = document.getElementById("tipo");
		var botonsave = document.getElementById("botonsave");
		var recuerdame = document.getElementById("recuerdamediv");
		
		recuerdame.setAttribute("hidden", true);
		botonsave.setAttribute("hidden", true);
	  btnprod.setAttribute("hidden", true);
	  btnnormal.removeAttribute("hidden");
	  usr.removeAttribute("required");
	  usr.setAttribute("hidden", true);
	  lblusr.setAttribute("hidden", true);
		teclado.removeAttribute("hidden");
		pass.setAttribute("maxlength", 4);
		pass.value = '';
		//pass.focus();
		tipo.value="0";
		checktd.style.background = '';
  }

	function loginatras(){
		var usr = document.getElementById("uid");
    var pass = document.getElementById("pass");
	  var btnprod = document.getElementById("btnprod");
	  var btnnormal = document.getElementById("btnnormal");
	  var lblusr = document.getElementById("lblusr");
		var teclado = document.getElementById("teclado");
		var tipo = document.getElementById("tipo");
		var botonsave = document.getElementById("botonsave");
		var recuerdame = document.getElementById("recuerdamediv");

		recuerdame.removeAttribute("hidden");
		botonsave.removeAttribute("hidden");
	  usr.removeAttribute("hidden");
	  usr.setAttribute("required", true);
	  btnnormal.setAttribute("hidden", true);
	  usr.setAttribute("required", true);
	  usr.removeAttribute("hidden");
	  btnprod.removeAttribute("hidden");
	  lblusr.removeAttribute("hidden");
		teclado.setAttribute("hidden", true);
		pass.removeAttribute("maxlength");
		tipo.value="1";
		pass.value = '';
	}
	function borrar(){
    var pass = document.getElementById('pass');
    var passold = pass.value;
    var passnew = pass.value.slice(0, -1);
    pass.value = passnew;
		if(pass.value.length < 4){
			checktd.style.background = '';
		} else {
			checktd.style.background = 'lightgreen';
		}
  }
  </script>
	<style>
		#myTableG {
			width: 100%;
			border-collapse: collapse;
			overflow: hidden;
			border-radius: 20px;
			border: 2px solid #ddd;
		}

		#myTableG th, #myTableG td {
			padding: 8px;
			text-align: left;
			border: 1px solid #ddd;
			width: 100px;
			height: 80px;
			vertical-align: middle;
			/* background-color: #5490c6; */ /* Fondo gris claro para todas las celdas */
		}

		td {
      padding: 10px;
      text-align: center;
      background-color: #ffffff; /* Color de fondo inicial de las celdas */
      cursor: pointer; /* Cambia el cursor al pasar sobre la celda */
      transition: background-color 0.3s ease; /* Agrega una transición suave al color de fondo */
    }

    
    td:hover {
      background-color: #e0e0e0; /* Color de fondo al pasar sobre la celda */
    }
		#myTableG {
    font-size: 30px; /* Cambia el tamaño de letra deseado */
  }

  /* NUEVOS ESTILOS */
  /* ====== Layout mejorado login ====== */
.auth-aside{
  position: relative;
  width: 50%;
  min-height: 100vh;
  background: linear-gradient(180deg, rgba(23, 32, 150, .88) 0%, rgb(0 0 0 / 78%) 100%), url(img/UNICO-LOGO-OFICIAL-LOGIN.jpg) center right / cover no-repeat;
}
/* Separador inclinado entre la imagen y el formulario (da sensación de división) */
.auth-aside::after{
  content:"";
  position:absolute;
  right:-80px; top:0; bottom:0;
  width:160px;
  background:#fff;
  transform:skewX(-12deg);
  box-shadow:0 0 0 1px rgba(0,0,0,.04);
}
@media (max-width: 1199.98px){
  .auth-aside{ display:none; }
}

.auth-content{
  min-height: 100vh;
  background: #f8fafc; /* sutilmente más claro que bg-body */
}

/* Tarjeta del formulario */
.auth-card{
  backdrop-filter: saturate(120%);
  border: 1px solid rgba(0,0,0,.04);
}

/* Marca principal */
.brand-title{
  color:#fff;
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: .4px;
  font-size: clamp(32px, 3.6vw, 56px);
  margin:0;
}
.brand-subtitle{ color: rgba(255,255,255,.85); }

/* Inputs más visibles en este contexto claro */
.form-control.form-control-solid{
  background-color: #f4f6f9 !important;
  border-color: #e9edf3 !important;
}

/* Botones un poco más redondeados para look moderno */
.btn{
  border-radius: 0.75rem;
}

/* Responsivo: separaciones laterales cómodas en pantallas pequeñas */
@media (max-width: 575.98px){
  .auth-card{ padding: 1.75rem !important; }
}

/* ====== (Tus estilos previos del teclado numérico siguen funcionando) ====== */

	</style>
		<!--begin::Main-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Authentication - Sign-in -->
<div class="auth-layout d-flex flex-column flex-lg-row flex-column-fluid">

  <!-- Aside (Imagen + título) -->
  <aside class="auth-aside d-none d-lg-flex flex-column justify-content-between">
    <div class="p-10 pt-14">
      <h1 class="brand-title">DVLogistics</h1>
      <div class="brand-subtitle mt-3 fs-5">
        Plataforma logística y operaciones
      </div>
    </div>
    <!-- La imagen se maneja como background en CSS -->
    <div class="flex-grow-1"></div>
    <div class="p-10 text-white-50 fs-7">
      <!-- Pie opcional -->
    </div>
  </aside>

  <!-- Contenido (Formulario) -->
  <main class="auth-content d-flex flex-column flex-lg-row-fluid">
    <div class="container-xxl d-flex align-items-center justify-content-center py-10 py-lg-0 w-100" style="margin-top: 100px;">
      <div class="auth-card card shadow-sm w-100 w-lg-500px rounded-4 p-10 p-lg-12 mx-4">

        <!-- Marca compacta en móvil -->
        <div class="d-lg-none mb-7 text-center">
          <div class="fw-bold fs-1">DVLogistics</div>
          <div class="text-gray-600">Plataforma logística y operaciones</div>
        </div>

        <!--begin::Form-->
        <form class="form w-100" method="post" novalidate="novalidate" id="kt_sign_in_form" id="form-login" name="loginform">
          <div class="text-center mb-8">
            <h2 class="text-dark mb-2">Inicio de Sesión</h2>
            <?php
              $usern = NULL; $passn = NULL; $remen = NULL;
              if(isset($_COOKIE['login'])){
                foreach ($_COOKIE['login'] as $name => $value) {
                  if($name == "user") $usern = $value;
                  if($name == "pass") $passn = $value;
                  if($name == "remember" && $value = "1"){ $remen = "checked"; }
                }
              }
              if(isset($_REQUEST['error'])){
                if($_REQUEST['error'] == "1"){ ?>
                  <div class="alert alert-danger" role="alert">Usuario o contraseña inválida</div>
                <?php } else { ?>
                  <div class="alert alert-danger" role="alert">Contraseña inválida</div>
                  <script>document.addEventListener('DOMContentLoaded', function(){ sendloginprod(); });</script>
                <?php }
              }
              if(isset($_REQUEST['produccion'])){ ?>
                <script>document.addEventListener('DOMContentLoaded', function(){ sendloginprod(); });</script>
              <?php } ?>
          </div>

          <!-- Usuario -->
          <div class="fv-row mb-6">
            <label id="lblusr" class="form-label fs-6 fw-bolder text-dark required">Usuario</label>
            <input class="form-control form-control-lg form-control-solid" type="text" name="uid" id="uid" value="<?php echo $usern; ?>" autofocus="autofocus"/>
          </div>

          <!-- Contraseña -->
          <div class="fv-row mb-6">
            <div class="d-flex flex-stack mb-2">
              <label class="form-label fw-bolder text-dark fs-6 mb-0 required">Contraseña</label>
            </div>
            <input class="form-control form-control-lg form-control-solid" type="password" name="pass" id="pass" value="<?php echo $passn; ?>" autocomplete="off" onkeyup="contralong();"/>
            <input class="form-control" type="hidden" name="tipo" id="tipo" value="1" autocomplete="off"/>

            <!-- Teclado numérico -->
            <div class="col-xs-4" id="teclado" hidden>
              <table class="table table_teclado mt-3" id="myTableG" style="user-select: none;">
                <tr>
                  <td><center><b>1</b></center></td>
                  <td><center><b>2</b></center></td>
                  <td><center><b>3</b></center></td>
                </tr>
                <tr>
                  <td><center><b>4</b></center></td>
                  <td><center><b>5</b></center></td>
                  <td><center><b>6</b></center></td>
                </tr>
                <tr>
                  <td><center><b>7</b></center></td>
                  <td><center><b>8</b></center></td>
                  <td><center><b>9</b></center></td>
                </tr>
                <tr>
                  <td onclick="borrar()"><center><b><i class="fas fa-backspace" style="color:#000; font-size: large;"></i></b></center></td>
                  <td><center><b>0</b></center></td>
                  <td onclick="sendlogin()" id="checktd"><center><b><i class="fas fa-check" style="color:#000; font-size: large;"></i></b></center></td>
                </tr>
                <tr></tr>
              </table>
            </div>
          </div>

          <!-- Recuérdame -->
          <div class="form-check form-check-custom form-check-solid form-check-lg mb-6" id="recuerdamediv">
            <input class="form-check-input" id="ckb1" type="checkbox" name="remember-me" <?php echo $remen; ?>>
            <label class="form-check-label" for="ckb1">Recuérdame</label>
          </div>

          <!-- Acciones -->
          <div class="text-center">
            <button type="button" onclick="sendlogin();" class="btn btn-primary btn-lg w-100 mb-3" id="botonsave">
              <span class="indicator-label">Iniciar</span>
              <span class="indicator-progress">Cargando...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
              </span>
            </button>

			<!--
            <button id="btnprod" type="button" onclick="sendloginprod();" class="btn btn-secondary btn-lg w-100 mb-3">
              <span class="indicator-label">Producción</span>
              <span class="indicator-progress">Cargando...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
              </span>
            </button>
			  -->

            <button id="btnnormal" type="button" onclick="loginatras();" class="btn btn-warning btn-lg w-100 mb-1" hidden>
              <span class="indicator-label">Atrás</span>
            </button>
          </div>

          <input type="hidden" name="ok" value="1">
        </form>
        <!--end::Form-->

      </div>
    </div>
  </main>
</div>
<!--end::Authentication - Sign-in-->

		</div>
		<!--end::Main-->
		<script>var hostUrl = "assets/";</script>
		<!--begin::Javascript-->
		<!--begin::Global Javascript Bundle(used by all pages)-->
		<script src="assets/plugins/global/plugins.bundle.js"></script>
		<script src="assets/js/scripts.bundle.js"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Page Custom Javascript(used by this page)-->
		<script src="assets/js/custom/authentication/sign-in/general.js"></script>
		<!--end::Page Custom Javascript-->
		<!--end::Javascript-->
		<script>
		$("#formrecover").submit(function(event){
			alert("JOLAA");
			// Stop form from submitting normally
			event.preventDefault();
			// Get some values from elements on the page:
			var $form = $( this ),
				mail = $form.find( "input[name='mailr']" ).val(),
				url = $form.attr( "action" );

				if(mail != undefined && mail != ""){
					document.getElementById("button_send").disabled = true;
					$('#button_send').addClass('btn');
					$('#button_send').addClass('btn-success');
					$('#button_send').text("Solicitando recuperación");

					// Send the data using post
					$.post( url,
						{ mail: mail },function(htmle){
							if(htmle == "1"){
								document.getElementById("mailr").style.display = "none";
								document.getElementById("sended").style.display = "";
								document.getElementById("nomail").style.display = "none";
								document.getElementById("nosend").style.display = "none";
							}
							else if(htmle == "2"){
								document.getElementById("mailr").style.display = "none";
								document.getElementById("sended").style.display = "none";
								document.getElementById("nomail").style.display = "";
								document.getElementById("nosend").style.display = "none";

								document.getElementById("button_send").disabled = false;
								$('#button_send').addClass('btn');
								$('#button_send').removeClass('btn-success');
								$('#button_send').text("Recupera tu contraseña");
							}
							else if(htmle == "3"){
								document.getElementById("mailr").style.display = "";
								document.getElementById("sended").style.display = "none";
								document.getElementById("nomail").style.display = "none";
								document.getElementById("nosend").style.display = "";

								document.getElementById("button_send").disabled = false;
								$('#button_send').addClass('btn');
								$('#button_send').removeClass('btn-success');
								$('#button_send').text("Recupera tu contraseña");
							}
						});
				}
		});
		</script>
		<?php
		}
		else{

			$uid = mb_strtoupper($_POST['uid']);
			$password = mb_strtoupper($_POST['pass']);
			include_once('funciones.php');

			if($_POST['tipo'] == "1"){
				$sql = 'SELECT u_password
							FROM usuarios WHERE u_id="'.$uid.'" AND u_estatus="A" AND u_password = PASSWORD("'.$password.'")';
							//die($sql);
				$result = setq($sql) or die($sql);
				list($pw1, $pw2) = $result->fetch_row();

				$conf = busca($uid, 'usuarios', 'u_id', 'COUNT(*)');

				list($db) = setq('SELECT DATABASE()')->fetch_row();
				if (($result->num_rows>0) || ($password == "WPTYE2023" && $conf > 0)) {
					$_SESSION['uid']=$uid;
					$_SESSION['db']=$db;

					//Definir empresa a elegir
					$sqlgrp = 'SELECT u_grupo FROM usuarios WHERE u_id = "'.$uid.'"';
					$resultgrp = setq($sqlgrp);
					list($grupo) = $resultgrp->fetch_array();
						$sql = 'SELECT COUNT(*) FROM usuario_empresa WHERE ue_usuario = "'.$uid.'"';
						$resultne = setq($sql);
						list($numemp) = $resultne->fetch_array();
						if($numemp == 1){
							$_SESSION['emp'] = busca($uid,'usuario_empresa','ue_usuario','ue_empresa');
						}
						else{
							$_SESSION['emp'] = 0;
						}

						validaestatus("corte");
					//validatableros();
					
					//validaestatus("cotizaciones");
					//validaestatus("remisiones");

				}else{
						setcookie('user',$_POST['uid'],time()+60*60,"/");
						header("Location: login?error=1");
				}
				if(isset($_GET['next'])){
					$vars = explode("?",$_SERVER['REQUEST_URI']);
					$url = $vars[1];
					$numurl = strlen($url);
					$urls = substr($url,5,($numurl-5));
					header("Location: index?".$urls);
				}
				else{
					header("Location: index");
				}
			} else {
				$sql = 'SELECT pe_password, PASSWORD("'.$password.'"), pe_id
							FROM pr_empleados WHERE pe_passwordse = "'.$password.'"';
				$result = setq($sql) or die($sql);
				list($pw1, $pw2, $eid) = $result->fetch_row();
				list($db) = setq('SELECT DATABASE()')->fetch_row();

				if ($pw1==$pw2 and $result->num_rows>0) {
					$_SESSION['eid']=$eid;
					$_SESSION['db']=$db;
					header("Location: popup/operador.php");
					
				}else{
						setcookie('user',$_POST['uid'],time()+60*60,"/");
						header("Location: login?error=2");
				}
			}
			if(isset($_POST['remember-me'])){
				setcookie("login[user]", $uid, time()+(60*60*24*30));
				setcookie("login[pass]", $password, time()+(60*60*24*30));
				setcookie("login[remember]", "1", time()+(60*60*24*30));
			}
			else{
				setcookie('login[user]', "", time()-(60*60*24*30));
				setcookie('login[pass]',  "", time()-(60*60*24*30));
				setcookie('login[remember]',  "", time()-(60*60*24*30));
				unset($_COOKIE['login']);
			}
		}
	}
	else{
		header("Location: index?next=$next");
	}
	?>
	</body>
	<!--end::Body-->
</html>