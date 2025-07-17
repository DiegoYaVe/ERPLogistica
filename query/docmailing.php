<?php
  //ini_set('display_errors',1);
  require('../funciones.php');

  if(isset($_POST['idD'])) $idD = $_POST['idD']; else $idD = getmax("md_id","mailing_campanasd");
  
  /*foreach($_POST as $campo => $valor){	echo "POST->". $campo ."= ". $valor.'<br>'; }
  foreach($_GET as $campo => $valor) {	echo "GET->". $campo ."= ". $valor.'<br>'; }
  foreach($_FILES as $campo => $valor) {	echo "FILES->". $campo ."= ". $valor.'<br>'; } */



  $file1 = $_FILES['file1'];


  if($file1['name'] != ""){
    
    $extensiones = array(0=>'application/pdf',1=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',2=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',3=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',4=>'application/msword');
    $max_tamanyo = 1024 * 1024 * 8;
    $ruta_indexphp = dirname(realpath(__FILE__));
    $ruta_fichero_origen = $file1['tmp_name'];
    $nuevo_nombre = $idD. $file1['name'];
    $ruta_nuevo_destino =  '../docs/mailing/' . $nuevo_nombre;
    $info = pathinfo($file1['name']);
    $extension = $info['extension'];

    if (in_array($file1['type'], $extensiones) ) {
      
        if ( $file1['size']< $max_tamanyo ) {
      
              if( move_uploaded_file($ruta_fichero_origen, $ruta_nuevo_destino)) {
      
              }
        }
    }


    $sql = 'INSERT mailing_documentos SET
    md_nmb = "'.$file1['name'].'",
    md_extension = "'.$extension.'", 
    md_estatus = "A",
    md_campanad = "'.$idD.'",
    md_ruta = "'.$ruta_nuevo_destino.'"';
     
  setq($sql);

  }

  $iddoc = (getmax("md_id","mailing_documentos")) - 1;
  //die($iddoc);
  //echo $file1['name'];
  echo '<div class="col-lg-4 p-2 border" id="doc'.$iddoc.'"><i class="icon-file"></i><br><p> '.$nuevo_nombre.'</p>
  <a class="btn btn-red btn-sm" onclick="borrardoc('.$iddoc.')" type="button"><i class="fa fa-trash"></i></a></div>'
?>