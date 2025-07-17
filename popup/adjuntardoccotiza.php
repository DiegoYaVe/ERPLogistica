<?php
  session_start();
  include_once('../funciones.php');
  include_once('../modulos/remisiones.php');

  echo '<script>
    function checksubmit(){
      document.getElementById("adjuntar").value = "Subiendo archivo, por favor espere";
      document.getElementById("adjuntar").disabled = true;
      return true;
    }
  </script>';

  echo '<form method="post" action="?modulo=cotizaciones&accion=setadjunto&id='.$_GET['id'].'" enctype="multipart/form-data" onsubmit="checksubmit();">
    <div class="row">';
      $adjunto = busca($_GET['id'],'crm_cotizaciones','cc_id','cc_adjunto');
      if(!$adjunto || empty($adjunto)){
        echo '<div class="col-12 col-md-12 alert alert-primary">Adjuntar documento a cotización</div>';
        $path = 'docs/adjcot/';
        if (!is_dir($path)) @mkdir($path, 0755);
        echo '<script>
          function setadjunto(ir){
            cb1 = document.getElementsByClassName("adjone");
            for (var i = 0; i < cb1.length; i++) {
              if(cb1[i].checked == true){
                cb1[i].checked = false;
              }
            }
            document.getElementById("el"+ir).checked = true;

            var elem = document.getElementById("elementos").value;
            for(var i=1;i<=elem;i++){
              document.getElementById("row" + i).style.backgroundColor = "white";
            }
            document.getElementById("row" + ir).style.backgroundColor = "grey";

            var typedoc = document.getElementById("typedoc" + ir).value;

            if(typedoc == "A"){
              document.getElementById("col" + ir).style.display = "none";
              document.getElementById("colad" + ir).style.display = "block";
            }else{
              document.getElementById("col" + elem).style.display = "";
              document.getElementById("colad" + elem).style.display = "none";
            }
            document.getElementById("sended").value = ir;
          }
          function archivodup(fileInput){
            var files = fileInput.files;
            var datos = document.getElementById("elementos").value;

            for (var i = 0; i < files.length; i++) {
              var nmbadj = files[i].name;
            }
            console.log("name: "+nmbadj);
            for(var i = 1; i <= (datos-1); i++){
              var valadj = document.getElementById("el"+i).value;
              console.log("nameadj: "+valadj);
              if(nmbadj === valadj){
                document.getElementById("adjuntar").disabled = true;
                document.getElementById("error").style.display = "";
              }
              else {
                document.getElementById("adjuntar").disabled = false;
                document.getElementById("error").style.display = "none";
              }
            }
          }
        </script>';

        /*
        
            if(nmbadj === valadj){
              document.getElementById("adjuntar").disabled = true;
              document.getElementById("error").style.display = "";
            }
            else {
              document.getElementById("adjuntar").disabled = false;
              document.getElementById("error").style.display = "none";
            }
        */
        $i = 0;
        if (is_dir($path)) {
          $dir = opendir($path);
          while ($elemento = readdir($dir)){
            if( $elemento != "." && $elemento != ".."){
              if( !is_dir($path.'/'.$elemento) ){
                $extension = pathinfo($path.'/'.$elemento, PATHINFO_EXTENSION);
                if($extension != "php"){
                  $i++;
                  echo '<input type="hidden" id="typedoc'.$i.'" name="typedoc'.$i.'" value="D">';
                  echo '<div class="col-12 col-md-12 p-2" id="row'.$i.'" style="background:#FFFFFF;">
                    <div>
                      <input type="checkbox" class="adjone" name="adjunto" id="el'.$i.'" value="'.$elemento.'" onchange="setadjunto('.$i.');" />
                      <label for="el'.$i.'">'.$elemento.'</label>
                      <!--<a type="button" download="'.$path.'/'.$elemento.'" href="'.$path.'/'.$elemento.'" class="btn btn-success round">
                        <i class="icon-download4"></i>
                      </a>-->
                    </div>
                  </div>';
                }
              }
            }
          }
          closedir($dir);
        }
        $i++;

        $style = 'style="display:none;"'; $hid = '';
        if($i == 1) {$style = ''; $hid = 'hidden';}
        $selecc = "";
        $txtadj = "Elegir archivo externo";
        echo '<input type="hidden" id="typedoc'.$i.'" name="typedoc'.$i.'" value="A">';
        echo '<div id="row'.$i.'" class="p-2" style="background:#FFFFFF;">
          <div class="form-control">
            <input type="checkbox" class="adjone" name="adjunto" id="el'.$i.'" value="'.$path.$elemento.'" '.$hid.' onchange="setadjunto('.$i.');" />
            <label id="col'.$i.'" for="el'.$i.'" style="">'.$txtadj.'</label>
            <input type="file" name="fileadj" id="colad'.$i.'" '.$style.' onchange="archivodup(this);" required/>
          </div>
        </div>';
        echo '<div class="col-12 col-md-12 mt-1">
          <center>
            <button type="submit" class="btn btn-primary" id="adjuntar" ><i class="icon-paperclip2"></i>Subir archivo</button><br>
            <lable id="error" style="display:none; color:red;">El archivo seleccionado ya ha sido utilizado anteriormente, seleccionalo de la lista mostrada</label>
          </center>
        </div>';
        echo '<input type="hidden" name="elementos" value="'.$i.'" id="elementos">';
        echo '<input type="hidden" name="sended" value="" id="sended">';
      }else{
        echo '<div class="col-12 col-md-12 alert alert-primary">Elemento adjunto</div>';
        $path1 = "docs/adjcot/".$adjunto;
        $archivo = $path1;

        echo '
          <div class="row">
            <center>
              <a target="_BLANK" href="'.$archivo.'">
                <button type="button" class="btn btn-primary"> <i class="far fa-file"></i> '.busca($_GET['id'],'crm_cotizaciones','cc_id','cc_adjunto').'</button>
              </a>
              <a href="?modulo=cotizaciones&accion=deladjunto&id='.$_GET['id'].'">
                <button type="button" class="btn btn-danger" ><i class="fa fa-trash"></i> Borrar</button>
                <!--<button type="button" class="btn btn-danger" ><i class="fa fa-trash"></i> Borrar</button>-->
              </a>
            </center>
          </div>';
      }
    echo '</div>
  </form>';
?>