<script language="javascript">
function checksubmit(){
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function cargar(){
  opener.location.reload();
  window.close();
}
function closewindow(){
   window.opener.location.reload();
   window.close();
}
</script>
<?php
session_start();
  include_once('../funciones.php');

  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  include_once('../modulos/nomina.php');
  $nomin = new modelnomina();
  $nomin->selectnomina($_GET['id']);
  $nomina = $_GET['id'];
  $empleado = $_GET['empleado'];

  if($nomin->id){
    $accion = 'updatef&id='.$_GET['id'];
    $title = "Detalle de nomina ".busca($empleado,'empleados','e_id','e_nmb');
  }

    $sqlf = 'SELECT MAX(n_ffin) FROM nomina WHERE n_empresa = "'.$_SESSION['emp'].'"';
    $resultf = setq($sqlf) or die($sqlf);
    list($fini) = $resultf->fetch_row();
    $fini = date('Y-m-d',strtotime($fini.'+ 1 days'));

    if(!$periodicidad) $periodicidad = "04";
    if($periodicidad == "04" && $fini != date('Y-m-d',strtotime($fini.' first day of this month'))) $ffin = date('Y-m-d',strtotime($fini.' last day of this month'));
    $dias = busca($periodicidad,'nomina_periodicidad','np_clave','np_dias');
    $dias--;

    if(!$ffin) $ffin = date('Y-m-d',strtotime($fini.' + '.$dias.' days'));

echo'
<div class="container mt-1 ">
  <form action="?modulo=nomina&accion='.$accion.'" method="post" >
    <input type="hidden" name="empresa" value="'.$_SESSION['emp'].'" />
    <div class="row">
      <div class="col-12 col-md-12 alert alert-primary"><label class="modaltext">'.$title.' Nomina</label></div>
      <div class="btn-group" data-toggle="buttons">';
echo '</div>
    </div>';
echo '<div class="table-responsive">
      <table class="table table-hover table-striped">';

$sqlp = 'SELECT * FROM nominad WHERE d_empleado = "'.$empleado.'" AND d_nomina = "'.$nomina.'" AND d_tipo IN ("P","N")';
$resultp = setq($sqlp) or die($sqlp);
if($resultp->num_rows > 0){
  echo '<tr><td colspan="6" class="bg-blue bg-darken-3 text-white pt-1 pb-1">Percepciones</td></tr>';
  echo '<tr><td width="45%" class="bg-blue bg-darken-3 text-white">Tipo</td>
            <td width="10%" class="bg-blue bg-darken-3 text-white">Cantidad</td>
            <td width="10%" class="bg-blue bg-darken-3 text-white">Clave</td>
            <td width="15%" class="bg-blue bg-darken-3 text-white">Gravado</td>
            <td width="15%" class="bg-blue bg-darken-3 text-white">Exento</td>
            <td width="5%" class="bg-blue bg-darken-3 text-white"></td></tr>';

  while($rowp = $resultp->fetch_array()){
    echo '<tr><td>'.menu_select_db('nomina_percepciones', 'np_id', 'np_nmb', $rowp['d_tipodp'], 'tipodp'.$rowp['d_id'],'XXX','','','',true).'</td>';
    echo '<td><input type="number" class="form-control" value="'.$rowp['d_cantidad'].'" name="cantidad'.$rowp['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowp['d_clave'].'" name="clave'.$rowp['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowp['d_importe'].'" name="importe'.$rowp['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowp['d_exento'].'" name="exento'.$rowp['d_id'].'" required /></td>';
    echo '<td><a href="?modulo=nomina&accion=deleted&id='.$rowp['d_id'].'&empleado='.$empleado.'&nomina='.$nomina.'">
          <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button></td></tr>';
  }
}

$sqld = 'SELECT * FROM nominad WHERE d_empleado = "'.$empleado.'" AND d_nomina = "'.$nomina.'" AND d_tipo = "D"';
$resultd = setq($sqld) or die($sqld);
if($resultd->num_rows > 0){
  echo '<tr><td colspan="6" class="bg-blue bg-darken-3 text-white pt-1 pb-1">Deducciones</td></tr>';
  echo '<tr><td width="45%" class="bg-blue bg-darken-3 text-white">Tipo</td>
            <td width="10%" class="bg-blue bg-darken-3 text-white">Cantidad</td>
            <td width="10%" class="bg-blue bg-darken-3 text-white">Clave</td>
            <td width="15%" class="bg-blue bg-darken-3 text-white">Gravado</td>
            <td width="15%" class="bg-blue bg-darken-3 text-white">Exento</td>
            <td width="5%" class="bg-blue bg-darken-3 text-white"></td></tr>';

  while($rowd = $resultd->fetch_array()){
    echo '<tr><td>'.menu_select_db('nomina_deducciones', 'nd_id', 'nd_nmb', $rowd['d_tipodp'], 'tipodp'.$rowd['d_id'],'XXX','','','',true).'</td>';
    echo '<td><input type="number" class="form-control" value="'.$rowd['d_cantidad'].'" name="cantidad'.$rowd['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowd['d_clave'].'" name="clave'.$rowd['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowd['d_importe'].'" name="importe'.$rowd['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowd['d_exento'].'" name="exento'.$rowd['d_id'].'" required /></td>';
    echo '<td><a href="?modulo=nomina&accion=deleted&id='.$rowd['d_id'].'&empleado='.$empleado.'&nomina='.$nomina.'">
          <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button></td></tr>';
  }
}

$sqlo = 'SELECT * FROM nominad WHERE d_empleado = "'.$empleado.'" AND d_nomina = "'.$nomina.'" AND d_tipo IN ("O")';
$resulto = setq($sqlo) or die($sqlo);
if($resulto->num_rows > 0){
  echo '<tr><td colspan="6" class="bg-blue bg-darken-3 text-white pt-1 pb-1">Otros pagos</td></tr>';
  echo '<tr><td width="45%" class="bg-blue bg-darken-3 text-white">Tipo</td>
            <td width="10%" class="bg-blue bg-darken-3 text-white">Cantidad</td>
            <td width="10%" class="bg-blue bg-darken-3 text-white">Clave</td>
            <td width="15%" class="bg-blue bg-darken-3 text-white">Gravado</td>
            <td width="15%" class="bg-blue bg-darken-3 text-white">Exento</td>
            <td width="5%" class="bg-blue bg-darken-3 text-white"></td></tr>';

  while($rowo = $resulto->fetch_array()){
    echo '<tr><td>'.menu_select_db('nomina_otrospagos', 'no_id', 'no_nmb', $rowo['d_tipodp'], 'tipodp'.$rowo['d_id'],'XXX','','','',true).'</td>';
    echo '<td><input type="number" class="form-control" value="'.$rowo['d_cantidad'].'" name="cantidad'.$rowo['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowo['d_clave'].'" name="clave'.$rowo['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowo['d_importe'].'" name="importe'.$rowo['d_id'].'" required /></td>';
    echo '<td><input type="number" class="form-control number-align" value="'.$rowo['d_exento'].'" name="exento'.$rowo['d_id'].'" required /></td>';
    echo '<td><a href="?modulo=nomina&accion=deleted&id='.$rowo['d_id'].'&empleado='.$empleado.'&nomina='.$nomina.'">
          <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button></td></tr>';
  }
}


echo '</table><div>
        <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-redo"></i> Actualizar</button></center>
      </div>
    </div>
  </form>
</div>';
?>