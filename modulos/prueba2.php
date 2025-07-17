<?php
  session_start(); 
  ini_set('display_errors', 1);
  include_once('../funciones.php');
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 1 Jul 2000 05:00:00 GMT");
  include_once('remisiones.php');
  include_once('cotizaciones.php'); // Fecha en el pasado
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD X7HTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" data-textdirection="ltr" class="loading">
<head>
    <base href="">
    <title>Fabrica de inflables</title>
    <meta name="description"
      content="SOMOS LA FÁBRICA #1 DE IFLABLES EN MÉXICO" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../assets/media/logos/favicon.png" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link href="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/jquery.fancybox.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" class="">
    <!--end::Global Stylesheets Bundle-->
    <script src="../assets/plugins/global/plugins.bundle.js"></script>
    <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  </head>
  
    <?php
    //echo '<table><thead><th>Artículo</th><th>SKU</th></thead><tbody>';
    $vendedores = ['VENDEDOR2', 'VENDEDOR7', 'VENDEDOR4', 'VENDEDOR6', 'VENDEDOR1'];
    $i = 0;
    $sql = 'SELECT * FROM crm_leads WHERE cl_vendedor = "VENDEDOR5" AND cl_acercamiento = "0"';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $sqlupd = 'UPDATE crm_leads SET cl_vendedor = "'.$vendedores[$i].'" WHERE cl_id = "'.$row['cl_id'].'"';
      setq($sqlupd);
      $i++;
      if($i == 5) $i = 0;
    }
     //echo '</tbody></table>'
    echo 'Terminó';
    
    ?> 
    <!--begin::Javascript-->
    <!--begin::Global Javascript Bundle(used by all pages)-->
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
  </body>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
</html>


