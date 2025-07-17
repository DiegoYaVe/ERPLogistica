<?php
  //ini_set('display_errors',1);
  require('../funciones.php');

  if(isset($_POST['idD'])) $idD = $_POST['idD']; else $idD = getmax("md_id","mailing_campanasd");
  

  $img1 = $_FILES['img1'];

  if($_POST['tipo'] == "I"){


  if($img1['name'] != ""){

    $extensiones = array(0=>'image/jpg',1=>'image/jpeg',2=>'image/png');
    $max_tamanyo = 1024 * 1024 * 8;
    $ruta_fichero_origen = $img1['tmp_name'];
    $info = pathinfo($img1['name']);
    $extension = $info['extension'];
    $nuevo_nombre = $img1['name'].$idD.'.'.$extension.'';
    //echo "$nuevo_nombre<br>";
    //$ruta_nuevo_destino =  '/jdceo2/docs/mailing/'.$nuevo_nombre;

    $ftphost = "tye-solutions.com";
    $ftpuser = "tyesolutions";
    $ftppass = '$PassTyE2022#';

    $cid = ftp_connect($ftphost);
    $login = ftp_login($cid, $ftpuser, $ftppass);

    if(!$cid || !$login){
      die('error en la conexion');
    }

    //$ruta = "/jdceo2/docs/mailing";
    $ruta = "/www/jdshop.mx/images/mailing";
    
    if(!@ftp_chdir($cid, $ruta)){
      ftp_mkdir($cid, $ruta);
    }
    
    ftp_pasv($cid, true);
    if($img1['size']< $max_tamanyo ){
      if(ftp_put($cid, $ruta.'/'.$nuevo_nombre, $ruta_fichero_origen, FTP_BINARY)){
        $r = "SUBIDA";
      }
    }
    
    ftp_close($cid);
    //$respuesta = 'https://jdshop.mx/images/'.$nuevo_nombre;
    $respuesta = '
    <div class="col-lg-4 p-2 border" id=""><br>
    <img src="https://jdshop.mx/images/mailing/'.$nuevo_nombre.'" class="img-thumbnail"> 
    <a class="btn btn-primary m-1 btn-sm" onclick="copiar('.$idD.')"><i class="icon-copy"></i></a>
    <input type="text" id="'.$idD.'" value="https://jdshop.mx/images/mailing/'.$nuevo_nombre.'" hidden />';
  }
   


  }elseif($_POST['tipo'] == "A"){


    if($img1['name'] != ""){
      
      $extensiones = array(0=>'image/jpg',1=>'image/jpeg',2=>'image/png');
      $max_tamanyo = 1024 * 1024 * 8;
      $ruta_indexphp = dirname(realpath(__FILE__));
      $ruta_fichero_origen = $img1['tmp_name'];
      $ruta_nuevo_destino =  'images/mailing/' . $img1['name'];
      $info = pathinfo($img1['name']);
      $extension = $info['extension'];

      if (in_array($img1['type'], $extensiones) ) {

          if ( $img1['size']< $max_tamanyo ) {

                if( move_uploaded_file($ruta_fichero_origen, $ruta_nuevo_destino)) {

                }
          }
      }

      $sql = 'INSERT mailing_imagenes SET
      mi_nmb = "'.$img1['name'].'",
      mi_extension = "'.$extension.'", 
      mi_estatus = "A",
      mi_campanad = "'.$idD.'",
      mi_ruta = "'.$ruta_nuevo_destino.'"';
      
    setq($sql);
    
    }
    $iddoc = (getmax("mi_id","mailing_imagenes")) - 1;

    $respuesta = '
    <div class="col-lg-4 p-2 border" id="img'.$iddoc.'"><br>
    <img src="'.$ruta_nuevo_destino.'" class="img-thumbnail"> 
    <a class="btn btn-red btn-sm" onclick="borrarimg('.$iddoc.')" type="button"><i class="fa fa-trash"></i></a></div>';

  }

  echo $respuesta;
?>