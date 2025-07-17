<?php
session_start();
date_default_timezone_set("America/Guatemala");
include_once('../funciones.php');

if (!isset($_SESSION['eid']) or !isset($_SESSION['db'])){
  $actual_link = url_completa();
	header("Location: ../logout?next=$actual_link");
}

$operador = $_SESSION['eid'];
$nmboperador = busca($_SESSION['eid'], 'pr_empleados', 'pe_id', 'pe_nmb');
$rol = busca($_SESSION['eid'], 'pr_empleados', 'pe_id', 'pe_rol');

$inactive = 60*60*1440; //minutos
if(isset($_SESSION['timeout']) ) {
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive){
    $actual_link = url_completa();
    header("Location: logout?next=$actual_link");
  }
}
$_SESSION['timeout'] = time();
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
	<head><base href="">
    <title>Fabrica de inflables</title>
		<meta name="description" content="The most advanced Bootstrap Admin Theme on Themeforest trusted by 94,000 beginners and professionals. Multi-demo, Dark Mode, RTL support and complete React, Angular, Vue &amp; Laravel versions. Grab your copy now and get life-time updates for free." />
		<meta name="keywords" content="Metronic, bootstrap, bootstrap 5, Angular, VueJs, React, Laravel, admin themes, web design, figma, web development, free templates, free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button, bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta charset="utf-8" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content="Metronic - Bootstrap 5 HTML, VueJS, React, Angular &amp; Laravel Admin Dashboard Theme" />
		<meta property="og:url" content="https://keenthemes.com/metronic" />
		<meta property="og:site_name" content="Keenthemes | Metronic" />
		<link rel="canonical" href="https://preview.keenthemes.com/metronic8" />
		<link rel="shortcut icon" href="../assets/media/logos/favicon.png" />
    <link href="../assets/css/jquery.fancybox.min.css" rel="stylesheet" type="text/css" />
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Page Vendor Stylesheets(used by this page)-->
		<link href="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
		<!--end::Page Vendor Stylesheets-->
		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		<link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
		<link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">

    <!--begin::Col-->
    <div class="col-12">
      <!--begin::Mixed Widget 2-->
      <div class="card card-xxl-stretch">
        <!--begin::Header-->
        <div class="card-header border-0 bg-primary py-5 rounded-0">
          <h3 class="card-title fw-bolder text-white fs-1qx"><?php echo $nmboperador; ?> <span class="h-20px border-gray-200 border-start ms-3 mx-2"></span> <small class="text-black fs-7 fw-bold my-1 ms-1"> Producción </small></h3>
          <!-- <img alt="Logo" src="../img/UNICO-LOGO-OFICIAL.png" class="h-70px logo"> -->
          <div class="card-toolbar">
            <!--begin::Menu-->
            <button type="button" class="btn btn-sm btn-icon btn-color-white btn-active-white btn-active-color- border-0 me-n3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
              <!--begin::Svg Icon | path: icons/duotune/general/gen024.svg-->
              <span class="svg-icon svg-icon-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
                  <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <rect x="5" y="5" width="5" height="5" rx="1" fill="#000000" />
                    <rect x="14" y="5" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                    <rect x="5" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                    <rect x="14" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                  </g>
                </svg>
              </span>
              <!--end::Svg Icon-->
            </button>
            <!--begin::Menu 3-->
            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-bold w-200px py-3" data-kt-menu="true">
              <!--begin::Heading-->
              <div class="menu-item px-3">
                <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Acciones</div>
              </div>
              <!--end::Heading-->
              <!--begin::Menu item-->
              <div class="menu-item px-3">
                <a class="menu-link px-3" data-fancybox data-type="ajax" data-src="historialprocesos.php" href="javascript:;">
                  Historial Actividades
                </a>
              </div>
              <!--end::Menu item-->
            </div>
            <div class="ms-4">
                <a href="../logout" class="btn btn-warning" style="color: #fff">Cerrar Sesión</a>
              </div>
            <!--end::Menu 3-->
            <!--end::Menu-->
          </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body p-0">
          <!--begin::Chart-->
          <div class="bg-primary" data-kt-color="danger" style="height: 60px"></div>
          <!--end::Chart-->
          <!--begin::Stats-->
          <div class="card-p position-relative" style="margin-top: -6rem!important;">
            <!--begin::Row-->
            <div class="row g-0">
              <div class="card-body">
              <!--begin::Col-->
              <?php
              // PROCESOS PENDIENTES
              $sql = 'SELECT * FROM pr_procesosop INNER JOIN roles_permisos ON pp_proceso = rp_proceso WHERE rp_idrol = "'.$rol.'" AND ((pp_estatus = "P") OR (pp_operador = "'.$operador.'" AND pp_estatus = "A"))  ';
              $result= setq($sql);
              if($result->num_rows > 0 ){
                while($row = $result->fetch_array()){
                  echo '
                  
                  
                  <div class="col-md-12">
                  <a href="temporizadorpps?op='.$row['pp_ordenp'].'&proceso='.$row['pp_id'].'&tipo=1" class="">
                  <!--begin::Item-->
                  <div class="d-flex align-items-center mb-8 rounded p-4 border bg-white" style="">
                    <!--begin::Bullet-->
                    <span class="bullet bullet-vertical h-40px bg-warning"></span>
                    <!--end::Bullet-->
                    <!--begin::Checkbox-->
                    <!-- <div class="form-check form-check-custom form-check-solid mx-5">
                      <input class="form-check-input" type="checkbox" value="">
                    </div>-->
                    <div class="mx-5">
                      <!--begin::Svg Icon | path: assets/media/icons/duotune/coding/cod001.svg-->
                      <span class="svg-icon svg-icon-warning svg-icon-2hx"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                      <path opacity="0.3" d="M22.1 11.5V12.6C22.1 13.2 21.7 13.6 21.2 13.7L19.9 13.9C19.7 14.7 19.4 15.5 18.9 16.2L19.7 17.2999C20 17.6999 20 18.3999 19.6 18.7999L18.8 19.6C18.4 20 17.8 20 17.3 19.7L16.2 18.9C15.5 19.3 14.7 19.7 13.9 19.9L13.7 21.2C13.6 21.7 13.1 22.1 12.6 22.1H11.5C10.9 22.1 10.5 21.7 10.4 21.2L10.2 19.9C9.4 19.7 8.6 19.4 7.9 18.9L6.8 19.7C6.4 20 5.7 20 5.3 19.6L4.5 18.7999C4.1 18.3999 4.1 17.7999 4.4 17.2999L5.2 16.2C4.8 15.5 4.4 14.7 4.2 13.9L2.9 13.7C2.4 13.6 2 13.1 2 12.6V11.5C2 10.9 2.4 10.5 2.9 10.4L4.2 10.2C4.4 9.39995 4.7 8.60002 5.2 7.90002L4.4 6.79993C4.1 6.39993 4.1 5.69993 4.5 5.29993L5.3 4.5C5.7 4.1 6.3 4.10002 6.8 4.40002L7.9 5.19995C8.6 4.79995 9.4 4.39995 10.2 4.19995L10.4 2.90002C10.5 2.40002 11 2 11.5 2H12.6C13.2 2 13.6 2.40002 13.7 2.90002L13.9 4.19995C14.7 4.39995 15.5 4.69995 16.2 5.19995L17.3 4.40002C17.7 4.10002 18.4 4.1 18.8 4.5L19.6 5.29993C20 5.69993 20 6.29993 19.7 6.79993L18.9 7.90002C19.3 8.60002 19.7 9.39995 19.9 10.2L21.2 10.4C21.7 10.5 22.1 11 22.1 11.5ZM12.1 8.59998C10.2 8.59998 8.6 10.2 8.6 12.1C8.6 14 10.2 15.6 12.1 15.6C14 15.6 15.6 14 15.6 12.1C15.6 10.2 14 8.59998 12.1 8.59998Z" fill="black"/>
                      <path d="M17.1 12.1C17.1 14.9 14.9 17.1 12.1 17.1C9.30001 17.1 7.10001 14.9 7.10001 12.1C7.10001 9.29998 9.30001 7.09998 12.1 7.09998C14.9 7.09998 17.1 9.29998 17.1 12.1ZM12.1 10.1C11 10.1 10.1 11 10.1 12.1C10.1 13.2 11 14.1 12.1 14.1C13.2 14.1 14.1 13.2 14.1 12.1C14.1 11 13.2 10.1 12.1 10.1Z" fill="black"/>
                      </svg></span>
                      <!--end::Svg Icon-->
                    </div>
                    <!--end::Checkbox-->
                    <!--begin::Description-->
                    <div class="flex-grow-1">
                      <p class="text-gray-800 text-hover-primary fw-bolder fs-6">EN ESPERA DE OPERADOR</p>
                      <span class="text-muted fw-bold d-block">
                        Proceso: '.busca($row['pp_proceso'],'pr_procesos','ppr_id','ppr_nmb').' <br>
                        Solicitud: '.busca($row['pp_solicitud'],'pr_solicitudes','ps_id','ps_nmb').' <br>
                        Orden de producción: '.busca($row['pp_ordenp'],'pr_ordenprod','po_id','po_folio').' <br>
                      </span>
                    </div>
                    <!--end::Description-->
                    <span class="badge badge-light-warning fs-8 fw-bolder">Pendiente</span>
                  </div>
                  <!--end:Item-->
                  </a>
                  </div>
                  
                  ';
                }
              }else{
                echo '
                <div class="col-md-12">
                <!--begin::Item-->
                <div class="d-flex align-items-center mb-8 rounded p-4 border bg-white" style="">
                  <!--begin::Bullet-->
                  <span class="bullet bullet-vertical h-40px bg-danger"></span>
                  <!--end::Bullet-->
                  <!--begin::Checkbox-->
                  <!-- <div class="form-check form-check-custom form-check-solid mx-5">
                    <input class="form-check-input" type="checkbox" value="">
                  </div>-->
                  <div class="mx-5">
                  <!--begin::Svg Icon | path: assets/media/icons/duotune/arrows/arr011.svg-->
                  <span class="svg-icon svg-icon-danger svg-icon-2hx"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path opacity="0.3" d="M6 19.7C5.7 19.7 5.5 19.6 5.3 19.4C4.9 19 4.9 18.4 5.3 18L18 5.3C18.4 4.9 19 4.9 19.4 5.3C19.8 5.7 19.8 6.29999 19.4 6.69999L6.7 19.4C6.5 19.6 6.3 19.7 6 19.7Z" fill="black"/>
                  <path d="M18.8 19.7C18.5 19.7 18.3 19.6 18.1 19.4L5.40001 6.69999C5.00001 6.29999 5.00001 5.7 5.40001 5.3C5.80001 4.9 6.40001 4.9 6.80001 5.3L19.5 18C19.9 18.4 19.9 19 19.5 19.4C19.3 19.6 19 19.7 18.8 19.7Z" fill="black"/>
                  </svg></span>
                  <!--end::Svg Icon-->
                  </div>
                  <!--end::Checkbox-->
                  <!--begin::Description-->
                  <div class="flex-grow-1 text-center">
                    <p class="text-gray-800 text-hover-primary fw-bolder fs-6">No hay procesos disponibles</p>
                  </div>
                  <!--end::Description-->
                </div>
                <!--end:Item-->
                </div>
                
                ';
              }
              ?>
              </div>
            </div>
            <!--end::Row-->
            <!--begin::Row-->
              <!-- aqui van los otros -->
            <!--end::Row-->
          </div>
          <!--end::Stats-->
        </div>
        <!--end::Body-->
      </div>
      <!--end::Mixed Widget 2-->
    </div>
									

		<!--end::Scrolltop-->
		<!--end::Main-->
		<script>var hostUrl = "../assets/";</script>
		<!--begin::Javascript-->
		<!--begin::Global Javascript Bundle(used by all pages)-->
		<script src="../assets/plugins/global/plugins.bundle.js"></script>
		<script src="../assets/js/scripts.bundle.js"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Page Vendors Javascript(used by this page)-->
		<script src="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
		<!--end::Page Vendors Javascript-->
		<!--begin::Page Custom Javascript(used by this page)-->
		<script src="../assets/js/custom/widgets.js"></script>
		<script src="../assets/js/custom/apps/chat/chat.js"></script>
		<script src="../assets/js/custom/modals/create-app.js"></script>
		<script src="../assets/js/custom/modals/upgrade-plan.js"></script>
	  <script src="../assets/js/jquery.fancybox.min.js"></script>
		<!--end::Page Custom Javascript-->
		<!--end::Javascript-->
	</body>
	<!--end::Body-->
</html>
