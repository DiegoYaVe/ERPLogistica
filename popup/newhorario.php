<?php
  session_start();
  ini_set('display_errors',1);
  date_default_timezone_set("America/Mexico_City");
  include_once('../funciones.php');

  if(!isset($_GET['id'])) {
    $titulo = 'Asignación de horarios para citas de JD CEO';
    $btntxt = 'Guardar';
    $icon = 'fa fa-save';
    $accion = 'asignar';
    $duracion = "30";
    $fecha = busca("JOSS",'registros_horarios','rh_web = "1" AND rh_usuario','MAX(rh_fecha)');
    if($fecha) $selfecha = date("Y-m-d", strtotime($fecha."+ 1 days"));
    else $selfecha = date("Y-m-d");

    $minfecha = date("Y-m-d");
  }else{
    $titulo = 'Actualización de horario para cita de JD CEO';
    $btntxt = 'Actualizar';
    $icon = 'icon-reload';
    $accion = 'updateh&id='.$_GET['id'];
    $duracion = busca($_GET['id'],'registros_horarios','rh_web = "1" AND rh_id','rh_duracion');
    $horaS = date("H:i",strtotime(busca($_GET['id'],'registros_horarios','rh_id','rh_hora')));
    $fecha = busca($_GET['id'],'registros_horarios','rh_id','rh_fecha');
    $hdrd = 'readonly disabled';
    $maxd = 'hidden';
    $sql = 'SELECT * FROM registros_horarios WHERE rh_web = "1" AND rh_fecha = "'.$fecha.'"';
    $result = setq($sql);
    $dataList = '<datalist id="horarios">';
    $i = 0;
    while($row = $result->fetch_array()){
      $dataList .= '<option data-hora="'.$i.'" value="'.date("H:i",strtotime($row['rh_hora'])).'">';
      $i++;
    }
    $dataList .= '</datalist>';
  }

  if(isset($_GET['fecha'])) $duracion = busca($_GET['fecha'],'registros_horarios','rh_web = "1" AND rh_fecha','rh_duracion');
  $step = $duracion * 60;

  ?>
  <script>
    function checkHora(){
      var horaM = document.getElementById("horaN").value;
      var horaO = document.getElementById("horaO").value;
      var btn = document.getElementById("btn");
      var valh = $('#horarios').find('option[value="'+horaM+'"]').data('hora');
      var split = horaM.split(":");
      if(horaM === horaO) {
        btn.disabled = false;
        document.getElementById("error").innerHTML = '';
      }else if(valh === undefined){
        btn.disabled = false;
        document.getElementById("error").innerHTML = '';
      }else{
        btn.disabled = true;
        document.getElementById("error").innerHTML = 'La hora no puede ser seleccionada porque ya esta programada para el mismo día';
      }
    }
    function horarios(){
      var bandera = document.getElementById("nhoras").value;
      var duracion = document.getElementById("duracion").value;
      var inicio = document.getElementById("inicio").value;
      var fin = document.getElementById("fin").value;
      var split = inicio.split(":");
      var splitfin = fin.split(":");
      var hora = split[0];
      var minutos = split[1];
      for(var i = 0; i < bandera; i++){
        if(parseFloat(i) == 0) {
          document.getElementById("delh"+i).removeAttribute("value");
          document.getElementById("hora"+i).setAttribute("value",hora+":"+minutos);
          console.log("inicio: "+inicio);
          document.getElementById("delh"+i).removeAttribute("hidden");
          document.getElementById("hora"+i).setAttribute("step",(duracion*60));
        }
        else if(parseFloat(hora) < parseFloat(splitfin[0])){
          document.getElementById("hora"+i).setAttribute("step",(duracion*60));
          minutos = parseInt(minutos) + parseInt(duracion);
          if(parseInt(minutos) >= 60){
            entero = Math.trunc(minutos/60);
            hora = parseInt(entero) + parseInt(hora);
            minutos = parseInt(minutos) - (parseInt(entero) * 60);
            if(parseInt(minutos) < 10) minutos = "0"+parseInt(minutos);
          }
          if(parseFloat(hora) == parseFloat(splitfin[0])){
            if(parseFloat(minutos) <= parseFloat(splitfin[1])){ 
              document.getElementById("hora"+i).setAttribute("value",hora+":"+minutos);
              document.getElementById("delh"+i).removeAttribute("hidden");
            }else {
              document.getElementById("hora"+i).setAttribute("value","");
              document.getElementById("delh"+i).setAttribute("hidden","hidden");
              document.getElementById("hora"+i).removeAttribute("required");
            }
          }else if(parseFloat(hora) < parseFloat(splitfin[0])){
            document.getElementById("hora"+i).setAttribute("value",hora+":"+minutos);
            document.getElementById("delh"+i).removeAttribute("hidden");
          }else {
            document.getElementById("hora"+i).setAttribute("value","");
            document.getElementById("delh"+i).setAttribute("hidden","hidden");
            document.getElementById("hora"+i).removeAttribute("required");
          }
        }else{
          document.getElementById("hora"+i).setAttribute("value","");
          document.getElementById("delh"+i).setAttribute("hidden","hidden");
          document.getElementById("hora"+i).removeAttribute("required");
        }
      }
    }
    function apartar(id){
      var ch = document.getElementById("hsel"+id);
      if(ch.checked){
        document.getElementById("hora"+id+"").disabled = true;
      }else document.getElementById("hora"+id+"").disabled = false;
    }
    function minmax(){
      var bandera = document.getElementById("nhoras").value;
      var inicio = document.getElementById("inicio").value;
      var fin = document.getElementById("fin").value;
      for(var i = 0; i < bandera; i++){
        document.getElementById("hora"+i).setAttribute("max",fin);
        document.getElementById("hora"+i).setAttribute("min",inicio);
      }
      horarios();
    }
    function delhora(id){
      document.getElementById("hora"+id).setAttribute("value","");
      document.getElementById("delh"+id).setAttribute("hidden","hidden");
      document.getElementById("hora"+id).removeAttribute("required");
    }
  </script>';
  <?php
  echo '<div class="container">  
    <div class="col-12 alert alert-primary">'.$titulo.'</div>
    <form method="post" action="index?modulo=registros&accion='.$accion.'">
      <div class="row col-md-12">
        <div class="mb-5 col-md-6 col-lg-3 col-xs-12">
          <label>Fecha disponible:</label>
          <input type="date" id="fecha" name="fecha" class="form-control focus" value="'.$selfecha.'" min="'.$minfecha.'" required="required" '.$hdrd.'>
        </div>';
        if($_GET['id']){
          echo $dataList;
          $sqlmm = 'SELECT MIN(rh_hora), MAX(rh_hora) FROM registros_horarios WHERE rh_web = "1" AND rh_fecha = "'.$fecha.'"';
          $resultmm = setq($sqlmm);
          list($min,$max) = $resultmm->fetch_array();

          echo '<input type="hidden" id="horaO" value="'.$horaS.'">
          <input type="hidden" id="nhoras" value="1">';
          echo '<div class="mb-5 col-md-6 col-lg-3 col-xs-12">
            <label>Nueva hora disponible:</label>
            <input type="time" id="horaN" name="horaN" class="form-control hora" value="'.$horaS.'" min="'.date("H:i",strtotime($horaS)).'" max="'.date("H:i",strtotime($horaS.' + '.$duracion.' minutes')).'" required="required" onchange="checkHora();">
            <label class="font-small-2" style="color:red" id="error"></label>
          </div>';
        }else{
          echo '<div class="mb-5 col-md-6 col-lg-3 col-xs-12">
            <label>Primera hora disponible:</label>
            <input type="time" id="inicio" name="inicio" class="form-control hora" value="09:30" required="required" onchange="minmax();">
            <label class="font-small-2" style="color:red" id="error"></label>
          </div>';
          echo '<div class="mb-5 col-md-6 col-lg-3 col-xs-12">
            <label>Última hora disponible:</label>
            <input type="time" id="fin" name="fin" class="form-control hora" value="18:30" required="required" onchange="minmax();">
            <label class="font-small-2" style="color:red" id="error"></label>
          </div>';
          echo '<div class="mb-5 col-md-6 col-lg-3 col-xs-12">
            <label>Duración:</label>
            <div class="input-group">
              <input type="number" id="duracion" name="duracion" class="form-control square" step="1" value="'.$duracion.'" min="1" '.$maxd.' required="required" plceholder="Duración" onfocus="this.select();" onchange="horarios();">
              <span class="input-group-addon">Mins</span>
            </div>
          </div>';
        }
        echo'<div class="mb-5 col-md-3" hidden>
          <label>Asesor:</label>
          <select class="form-control" name="asesor" id="asesor" required="required">';
            $sqlu = 'SELECT u_id FROM usuarios WHERE u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"';
            $resultu = setq($sqlu);
            while($rowu = $resultu->fetch_array()){
              if($rowu['u_id'] == "JOSS") $seldis = "selected"; else $seldis = "";
              echo '<option value="'.$rowu['u_id'].'" '.$seldis.'>'.$rowu['u_id'].'</option>';
            }
          echo'</select>
        </div>';
      echo'</div>';
      if(!isset($_GET['id'])){
        echo'<div class="col-md-12 alert alert-primary">Horarios disponibles</div>';
        if(!isset($_GET['fecha'])){
          echo'<div class="row col-md-12">';
            for($i = 0; $i < 6; $i++){
              $inicio = "09:00";
              $hora1 = date("H:i", strtotime($inicio)+(1800*($i+1)));
              echo'<div class="mb-5 col-md-4 col-lg-2 col-xs-6">  
                <label>Horario '.($i+1).':</label>&nbsp;&nbsp;<button id="delh'.$i.'" type="button" class="btn btn-sm btn-danger text-white" onclick="delhora('.$i.');" title="Eliminar hora"><i class="fa fa-trash"></i></button>
                <input type="time" id="hora'.$i.'" name="hora[]" class="form-control hora" step="'.$step.'" value="'.$hora1.'" required="required"  >
              </div>';
            }
          echo '</div>
          <div class="row col-md-12">';
            for($i = 6; $i < 12; $i++){
              $inicio = date("H:i", strtotime("12:30"));
              $hora1 = date("H:i", strtotime($inicio)+(1800*($i+1)));
              echo'<div class="mb-5 col-md-4 col-lg-2 col-xs-6">  
                <label>Horario '.($i+1).':</label>&nbsp;&nbsp;<button id="delh'.$i.'" type="button" class="btn btn-sm btn-danger text-white" onclick="delhora('.$i.');" title="Eliminar hora"><i class="fa fa-trash"></i></button>
                <input type="time" id="hora'.$i.'" name="hora[]" class="form-control hora" step="'.$step.'" value="'.$hora1.'" required="required"  >
              </div>';
            }
          echo '</div>';
        }else{
          $i = 0;
          echo'<div class="row col-md-12">';
            $sqlfclon = 'SELECT * FROM registros_horarios WHERE rh_web = "1" AND rh_fecha = "'.$_GET['fecha'].'" ORDER BY rh_hora ASC LIMIT 0,6';
            $resultclone = setq($sqlfclon);
            while($rowclone = $resultclone->fetch_array()){
              echo'<div class="mb-5 col-md-4 col-lg-2 col-xs-6">  
                <label>Horario '.($i+1).':</label>&nbsp;&nbsp;<button id="delh'.$i.'" type="button" class="btn btn-sm btn-danger text-white" onclick="delhora('.$i.');" title="Eliminar hora"><i class="fa fa-trash"></i></button>
                <input type="time" id="hora'.$i.'" name="hora[]" class="form-control hora" step="'.$step.'" value="'.date("H:i",strtotime($rowclone['rh_hora'])).'" required="required"  >
              </div>';
              $i++;
            }
            if($i < 6){
              for($a = $i; $a < 6; $a++){
                echo'<div class="mb-5 col-md-4 col-lg-2 col-xs-6">  
                  <label>Horario '.($i+1).':</label>&nbsp;&nbsp;<button id="delh'.$i.'" type="button" class="btn btn-sm btn-danger text-white" onclick="delhora('.$i.');" title="Eliminar hora"><i class="fa fa-trash"></i></button>
                  <input type="time" id="hora'.$i.'" name="hora[]" class="form-control hora" step="'.$step.'">
                </div>';
                $i++;
              }
            }
          echo '</div>';
          echo'<div class="row col-md-12">';
            $sqlfclon = 'SELECT * FROM registros_horarios WHERE rh_web = "1" AND rh_fecha = "'.$_GET['fecha'].'" ORDER BY rh_hora ASC LIMIT 6,12';
            $resultclone = setq($sqlfclon);
            while($rowclone = $resultclone->fetch_array()){
              echo'<div class="mb-5 col-md-4 col-lg-2 col-xs-6">  
                <label>Horario '.($i+1).':</label>&nbsp;&nbsp;<button id="delh'.$i.'" type="button" class="btn btn-sm btn-danger text-white" onclick="delhora('.$i.');" title="Eliminar hora"><i class="fa fa-trash"></i></button>
                <input type="time" id="hora'.$i.'" name="hora[]" class="form-control hora" step="'.$step.'" value="'.date("H:i",strtotime($rowclone['rh_hora'])).'" required="required"  >
              </div>';
              $i++;
            }
            if($i < 12){
              for($a = $i; $a < 12; $a++){
                echo'<div class="mb-5 col-md-4 col-lg-2 col-xs-6">  
                  <label>Horario '.($i+1).':</label>&nbsp;&nbsp;<button id="delh'.$i.'" type="button" class="btn btn-sm btn-danger text-white" onclick="delhora('.$i.');" title="Eliminar hora"><i class="fa fa-trash"></i></button>
                  <input type="time" id="hora'.$i.'" name="hora[]" class="form-control hora" step="'.$step.'">
                </div>';
                $i++;
              }
            }
          echo '</div>';
        }
        echo'<input type="hidden" id="nhoras" value="12">';
      }
      echo'<div class="mb-5 col-md-12">
        <center>
          <button type="submit" class="btn btn-primary" id="btn">
            <i class="'.$icon.'"></i> '.$btntxt.'
          </button>
        </center>
      </div>
    </form>
  </div>';

?>