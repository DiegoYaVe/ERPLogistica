<?php
  //ini_set('display_errors','1');
  $GLOBALS['menu'] = "Contabilidad";
  class proyecciones{
    var $model;
    var $view;
    function proyecciones(){ // constructor
      $this->model= new Modelproyecciones; // crea modelo
    }
    function index(){
      if(!isset($_GET['page']))    $_GET['page'] = 1;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = 'A';
      if(!isset($_POST['nombre']))    $_POST['nombre'] = NULL;
      if(!isset($_POST['rubro']))    $_POST['rubro'] = NULL;

      $this->model->result($_GET['page'],$_REQUEST['estatus'],$_POST['nombre'],$_POST['rubro']);
      $this->view=new Viewproyecciones($this->model);
      $this->view->browse($_GET['page'],$_REQUEST['estatus'],$_POST['nombre'],$_POST['rubro']);
    }
    function edit() {
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      $this->model->select($_GET['id']);
      $this->view = new Viewproyecciones($this->model);
      $this->view->edit();
    }
    function show() {
      $this->model->select($_GET['id']);
      $this->view = new Viewproyecciones($this->model);
      $this->view->show();
    }
    function insert(){
      $sqlm = 'SELECT MAX(p_id) FROM proyecciones';
      $resultm = mysql_query($sqlm) or die($sqlm);
      list($id) = mysql_fetch_array($resultm);
      $id++;

      $this->model->setData($id,$_POST['nombre'],$_POST['rubro'],$_POST['importe'],$_POST['fecha'],$_POST['iteracion'],$_POST['observaciones'],$_POST['user']);
      $this->model->insert();

      header('Location:?modulo=proyecciones&accion=show&id='.$id);
    }
    function update(){
      $this->model->setData($_GET['id'],$_POST['nombre'],$_POST['rubro'],$_POST['importe'],$_POST['fecha'],$_POST['iteracion'],$_POST['observaciones'],$_POST['user']);
      $this->model->deletep($_GET['id']);
      $this->model->update();

      $cuenta=busca($_GET['id'],'proyecciones','p_id','p_cxpagos');
      if($cuenta){
        $estatus=busca($cuenta,'cxpagos','cp_id','cp_estatus');
        if($estatus=="N"){
          $sql = 'UPDATE cxpagos SET cp_monto = "'.$_POST['importe'].'" WHERE cp_id="'.$cuenta.'"';
          mysql_query($sql) or die($sql);
        }
      }
      header('Location:?modulo=proyecciones&accion=show&id='.$_GET['id']);
    }
    function desactivar() {
      $this->model->deletep($_GET['id']);
      $this->model->desactivar($_GET['id']);
      header('Location:?modulo=proyecciones&accion=show&id='.$_GET['id']);
    }
    function activar() {
      $this->model->activar($_GET['id']);
      header('Location:?modulo=proyecciones&accion=show&id='.$_GET['id']);
    }
  }

  class Modelproyecciones{
    function result($page,$estatus,$nombre,$rubro) {
      $page--;
      $pageresult = 50;
      $pag = ' LIMIT '.($page * $pageresult).','.$pageresult;

      $sql = 'SELECT * FROM proyecciones WHERE p_id <>0';
      if($nombre) $sql.=' AND p_nmb LIKE "%'.$nombre.'%"';
      if($estatus) $sql.=' AND p_estatus = "'.$estatus.'"';
      if($rubro) $sql.=' AND p_rubro = "'.$rubro.'"';

      $sql.=' ORDER BY p_id DESC';

      $this->resultt = mysql_query($sql) or die($sql);
      list($nr) = mysql_fetch_array($this->resultt);
      $this->np = $nr/$pageresult;

      $sql.=$pag ;
      $this->result=mysql_query($sql);
      return $this->result;
    }
    function setData($id,$nombre,$rubro,$importe,$fecha,$iteracion,$observaciones,$user){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
      $simboldi = array('\"',"\'","\#","\$","\%", "\&","\/","\(","\)","\=","\?","\¡","\*","\+","\~","\^","\[","\°","\|","\{","\}","\[","\]");
      $this->id= $id;
      $this->nombre=str_replace($simbol,$simboldi, mb_strtoupper(trim($nombre)));
      $this->rubro= $rubro;
      $this->importe=$importe;
      $this->fecha=$fecha;
      $this->iteracion=$iteracion;
      $this->observaciones= str_replace($simbol,$simboldi, mb_strtoupper(trim($observaciones)));
      $this->user=$user;
    }
    function insert(){
      $sql='INSERT INTO proyecciones SET
      p_id="'.$this->id.'",
      p_nmb="'.$this->nombre.'",
      p_rubro="'.$this->rubro.'",
      p_importe="'.$this->importe.'",
      p_fechap="'.$this->fecha.'",
      p_iteracion="'.$this->iteracion.'",
      p_observaciones="'.$this->observaciones.'",
      p_user="'.$this->user.'",
      p_estatus="A"';
      mysql_query($sql) or die($sql);
    }
    function update(){
      $sql='UPDATE proyecciones SET
      p_nmb="'.$this->nombre.'",
      p_rubro="'.$this->rubro.'",
      p_importe="'.$this->importe.'",
      p_fechap="'.$this->fecha.'",
      p_iteracion="'.$this->iteracion.'",
      p_observaciones="'.$this->observaciones.'",
      p_user="'.$this->user.'",
      p_estatus="A" WHERE p_id="'.$this->id.'"';
      mysql_query($sql) or die($sql);
    }
    function select($id) {
      $sql = 'SELECT * FROM proyecciones WHERE p_id= "' . $id . '"';
      $result = mysql_query($sql) or die($sql);
      if($result)$row = mysql_fetch_array($result);

      $this->id= $row['p_id'];
      $this->nombre= $row['p_nmb'];
      $this->rubro=$row['p_rubro'];
      $this->importe=$row['p_importe'];
      $this->user= $row['p_user'];
      $this->fechap= $row['p_fechap'];
      $this->estatus= $row['p_estatus'];
      $this->cxpagos= $row['p_cxpagos'];
      $this->observaciones= $row['p_observaciones'];
      $this->iteracion= $row['p_iteracion'];
    }
    function activar($id) {
      $sql = 'UPDATE proyecciones SET p_estatus = "A" WHERE p_id="'.$id.'"';
      mysql_query($sql) or die($sql);
    }
    function desactivar($id) {
      $sql = 'UPDATE proyecciones SET p_estatus = "I" WHERE p_id="'.$id.'"';
      mysql_query($sql) or die($sql);
    }
    function deletep($id){
      $sql = 'DELETE FROM cxpagos WHERE cp_tipo = "P" AND cp_estatus = "N" AND cp_idref = "'.$id.'"';
      mysql_query($sql) or die($sql);
    } 
  }
  
  class Viewproyecciones{
    function Viewproyecciones($model) {
      include ('header.php');
      echo'<!--<div class="content-wrapper">
        <section class="content">
          <div class="div" id="titulo01">
            <div class="div2" id="name01">'.strtoupper(busca($_GET['modulo'], 'grupos_accion', 'ga_accion = "'.$_GET['accion'].'" AND ga_modulo', 'ga_descripcion')).'
            </div>-->';
      $this->model = $model;
    }
    function browse($page,$estatus,$nombre,$rubro) {
        $iteracionp = array("S"=>"SEMANAL","Q"=>"QUINCENAL","M"=>"MENSUAL","U"=>"UNICA");
        $estatusp = array("A"=>"ACTIVO","I"=>"INACTIVO","F"=>"FINALIZADO","C"=>"CANCELADO");
        echo '<div class="div2" id="bboton01">
          <a href="?modulo=proyecciones&accion=edit" onclick="this.onclick = function(){return false;}"><input type="button" value="" id="nuevo" class="botonb"></a>&nbsp;';
          $e1 = '';$e2 = '';$est = '';
          if($estatus) $est = '&estatus='.$estatus;
          if($estatus == "A") $e1 = "selected";
          if($estatus == "I") $e2 = "selected";
          if($page > 1)
            echo '<a href="?modulo=proyecciones&accion=index'.$est.'&page='.($page-1).'" accesskey="t"><input type="button" value=" " class="botonb" id="atras"></a>&nbsp;';
          if($page < $this->model->np)
            echo '<a href="?modulo=proyecciones&accion=index'.$est.'&page='.($page+1).'" accesskey="l"><input type="button" value=" " class="botonb" id="adelante"></a>&nbsp;';
        echo'</div>
      </div>';
      echo'<form action="?modulo=proyecciones&accion=index" method="post" onsubmit="return checkSubmitenviar();">
        <b>Nombre: </b><input type="text" size="30" value="'.$nombre.'" name="nombre">&nbsp;
        <b>Rubro: </b>'.menu_select_db('rubros', 'r_id', 'r_nmb', $rubro, 'rubro', 'r_estatus = "A"',false,false,true,false).'&nbsp;
        <b>Estatus: </b><select name="estatus" id="estatus" onchange="this.form.submit()">
          <option value="">TODOS</option>
          <option value="A" '.$e1.'>ACTIVO</option>
          <option value="I" '.$e2.'>INACTIVO</option>
        </select>&nbsp;
        <input type="submit" value="" id="actualizar" class="botont"/>
      </form>';
      echo'<div class="blue">
        <center>
          <table class="lista" border="0" width="100%" >
            <thead>
              <tr>
                <th align=center style="width:5%"><b>ID</b></th>
                <th align="center"><b>Nombre</b></th>
                <th align=center><b>Rubro</b></th>
                <th align=center><b>Iteracion</b></th>
                <th align=center><b>Fecha programada</b></th>
                <th align=center><b>Importe</b></th>
                <th align=center><b>Estatus</b></th>
              </tr>
            </thead>
            <tbody>';
              if ($this->model->result){
                while ($row=mysql_fetch_array($this->model->result)) {
                  echo '<tr><th align=center><a href=?modulo=proyecciones&accion=show&id='.$row['p_id'].' onclick="this.onclick = function(){return false;}">'.$row['p_id'].'</a></th>';
                    echo '<td align=center>'.$row['p_nmb'].'</td>';
                    echo '<td align=center>'.busca($row['p_rubro'],'rubros','r_id','r_nmb').'</td>';
                    echo '<td align=center>'.$iteracionp[$row['p_iteracion']].'</td>';
                    echo '<td align=center>'.fecha_formato($row['p_fechap']).'</td>';
                    echo '<td align=center>'.number_format($row['p_importe'],2).'</td>';
                    if($row['p_estatus']=='A')
                      echo '<td align=center style="background:#25FF3B" >'.$estatusp[$row['p_estatus']].'</td>';
                    else if($row['p_estatus']=='F')
                      echo '<td align=center style="background:#54C6FF" >'.$estatusp[$row['p_estatus']].'</td>';
                    else
                      echo '<td align=center style="background:#FF4A32" >'.$estatusp[$row['p_estatus']].'</td>';
                  echo '</tr>';
                }
              }
            echo '</tbody>
          </table>
        </center>
      </div>';
    }

function edit(){

  $t1 = '';
  $t2 = '';
  $t3 = '';
  $t4 = '';
  if($this->model->iteracion == "U") $t1 = "selected";
  if($this->model->iteracion == "S") $t2 = "selected";
  if($this->model->iteracion == "Q") $t3 = "selected";
  if($this->model->iteracion == "M") $t4 = "selected";

    $tipop = array("C"=>"PRESTAMO OTORGADO","P"=>"PRESTAMO SOLICITADO");

  echo '<div class="div2" id="bboton01">
  <a href="?modulo=proyecciones&accion=index" accesskey="t"><input type="button" value=" " class="botonb" id="atras"></a>
        </div></div>';

      $accion = 'insert';
      if (isset($this->model->id)) {
        $accion = 'update&id='.$this->model->id;
        $fecha=$this->model->fechap;
        $importe=$this->model->importe;
        $iteraccion=$this->model->iteracion;

      }else{
        $fecha=date('Y-m-d');
        $importe=100;
        $iteraccion=1;
      }
      echo '<form autocomplete="off" method=post name="pro"  action=?modulo=proyecciones&accion='.$accion.' onsubmit="return checkSubmitguardar();">
            <center>
            <table class="lista" border="0" cellspacing="3" width="100%"><tbody>';

      if($this->model->id)
        echo '<thead><tr><th colspan="4"><b>Editar '.busca($this->model->id,'proyecciones','p_id','p_nmb').'</b></th></tr></thead>';
      else
        echo '<thead><tr><th colspan="4"><b>Proyeccion nueva</b></th></tr></thead>';

      echo '<tr>
      <th>Nombre</th>
      <th>Rubro</th>
      <th>Importe</th>
      <th>Fecha programada</th>
      </tr>';

      echo '<tr>';
      echo '<td><input type="text" name="nombre" value="'.$this->model->nombre.'" required placeholder="Ingrese un nombre..." autofocus /></td>
      <td>'.menu_select_db('rubros', 'r_id', 'r_nmb', $this->model->rubro, 'rubro', 'r_estatus = "A"',false,false,false,true).'</td>
      <td><input type="number" onfocus="this.select();" style="width:80px" min="100" name="importe" step="1" required value="'.$importe.'" /></td>';
      echo '<td><input type="date" name="fecha" value="'.$fecha.'" required /></td>';
      echo '</tr>';

      echo '<tr>
      <th>Iteraccion</th>
      <th colspan="2">Descripcion</th>
      <th>Genera</th>
      </tr>';

      echo '<tr>
      <td><select name="iteracion">
            <option value="U" '.$t1.'>UNICA</option>
            <option value="S" '.$t2.'>SEMANAL</option>
              <option value="Q" '.$t3.' >QUINCENAL</option>
              <option value="M" '.$t4.'>MENSUAL</option>
          </select></td>
      <td colspan="2"><textarea name="observaciones" cols="70" rows="6">'.$this->model->observaciones.'</textarea></td>
      <td>'.$_SESSION['uid'].' <input type="hidden" name="user" value="'.$_SESSION['uid'].'" /> </td>
      </tr>';

       echo'<tr><td align="center" colspan="4">
       <center><input type="submit" value="" id="guardar" class="botont"></center>
       </td></tr>

       </tbody></table> </form>
       </center>';
}

function show(){
?>
<script>
function mensaje(){
    alert('Por favor ingrese una nueva fecha programada, para que la proyeccion se comienza a aplicar');
}
</script>

<?php
    $iteracionp = array("S"=>"SEMANAL","Q"=>"QUINCENAL","M"=>"MENSUAL","U"=>"UNICA");
    $estatusp = array("A"=>"ACTIVO","I"=>"INACTIVO");
    echo '<div class="div2" id="bboton01">
        <a href="?modulo=proyecciones&accion=index" accesskey="t"><input type="button" value=" " class="botonb" id="atras"></a>
        <a href="?modulo=proyecciones&accion=edit&id='.$this->model->id.'" accesskey="t"><input type="button" value=" " class="botonb" id="editar"></a>';
    if($this->model->estatus=="I")
        echo '<a href="?modulo=proyecciones&accion=activar&id='.$this->model->id.'" accesskey="t"><input type="button" value=" " class="botonb" id="activar" onclick="mensaje();" ></a>';
    else
        echo '<a href="?modulo=proyecciones&accion=desactivar&id='.$this->model->id.'" accesskey="t"><input type="button" value=" " class="botonb" id="desactivar"></a>';
    echo'</div></div>';

echo '<center><table class="lista" border="0" cellspacing="3" width="100%"><tbody>';

      echo '<tr><th colspan="4"><b>PROYECCION '.$this->model->id.'</b></th></tr>';
      echo '<tr>
       </center>';
      echo '<th>Nombre</th>
      <th>Rubro</th>
      <th>Importe</th>
      <th>Fecha programada</th>
      </tr>';

      echo '<tr>';
      echo '<td>'.$this->model->nombre.'</td>
      <td>'.busca($this->model->rubro,'rubros','r_id','r_nmb').'</td>
      <td>'.number_format($this->model->importe,2).'</td>
      <td>'.cambiar_fecha($this->model->fechap).'</td>
      </tr>';

      echo '<tr>
      <th>Iteraccion</th>
      <th colspan="2">Descripcion</th>
      <th>Estatus</th>
      </tr>';

      echo '<tr>
      <td>'.$iteracionp[$this->model->iteracion].'</td>
      <td colspan="2">'.nl2br($this->model->observaciones).'</td>
      <td>'.$estatusp[$this->model->estatus].'</td>
      </tr>';

       echo'
       </tbody></table>';
}

}
?>