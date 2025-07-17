<?php
//ini_set('display_errors',1);
class departamentosw{

  function __construct(){
      $this->model = new modeldepartamentosw($obj);
  }

  function index(){
      $this->model->result();
      $this->view = new viewdepartamentosw($this->model);
      $this->view->browse();
  }

  function edit(){
      //if(!isset($_GET['id'])) //$_GET['id'] = NULL;
      $this->model->selectdep($_GET['id']);
      $this->model->resultcon();
      $this->view = new viewdepartamentosw($this->model);
      $this->view->edit();
  }

  function insertdepto(){
    $this->model->insertdepto($_POST['nmbac']);

    redirect('?modulo=departamentosw&accion=index');
  }

  function updatedepto(){
    $this->model->updatedepto($_GET['id'],$_POST['nmbac']);

    redirect('?modulo=departamentosw&accion=index');
  }

  function asignaconceptos(){
    $this->model->resultcon();
    while($row = $this->model->resultdc->fetch_array()){
      if(isset($_POST['chcon'.$row['c_id']])){
        $this->model->asignaconcepto($_GET['departamento'],$row['c_id']);
      }
      else{
        $this->model->desasignaconcepto($_GET['departamento'],$row['c_id']);
      }

    }
    redirect('?modulo=departamentosw&accion=edit&id='.$_GET['departamento']);
  }

  function conceptos(){
      $this->model->resultcon();
      $this->view = new viewdepartamentosw($this->model);
      $this->view->browsec();
  }
  function insertconcepto(){
    $this->model->insertconcepto($_POST['nmbac']);

    redirect('?modulo=departamentosw&accion=conceptos');
  }

  function updateconcepto(){
    $this->model->updateconcepto($_GET['id'],$_POST['nmbac']);

    redirect('?modulo=departamentosw&accion=conceptos');
  }

  function editc(){
      $this->model->selectc($_GET['id']);
      $this->model->resultct();
      $this->view = new viewdepartamentosw($this->model);
      $this->view->editc();
  }

  function asignacategoria(){
    $this->model->resultct();
    while($row = $this->model->resultct->fetch_array()){
      if(isset($_POST['chcat'.$row['cw_id']])){
        $this->model->asignacategoria($_GET['concepto'],$row['cw_id']);
      }
      else{
        $this->model->desasignacategoria($_GET['concepto'],$row['cw_id']);
      }

    }
    redirect('?modulo=departamentosw&accion=editc&id='.$_GET['concepto']);
  }

  function categorias(){
    $this->model->resultct();
    $this->view = new viewdepartamentosw($this->model);
    $this->view->browsect();
  }

  function insertcategoria(){
    $this->model->insertcategoria($_POST['nmbac'],$_POST['catceo']);

    redirect('?modulo=departamentosw&accion=categorias');
  }

  function updatecategoria(){
    $this->model->updatecategoria($_GET['id'],$_POST['nmbac'],$_POST['catceo']);

    redirect('?modulo=departamentosw&accion=categorias');
  }

}

class modeldepartamentosw{
  function result(){
    $sql = 'SELECT * FROM departamentosw ORDER BY dw_id ASC';
    $this->result = setq($sql);
  }

  function selectdep($id){
    $sql = 'SELECT * FROM departamentosw WHERE dw_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();

    $this->id = $row['dw_id'];
    $this->nmb = $row['dw_nmb'];
    $this->estatus = $row['dw_estatus'];
    $this->observacion	 = $row['dw_observacion'];
    $this->logo	 = $row['dw_logo'];
    $this->ext = $row['dw_ext'];
    $this->home = $row['dw_home'];
    $this->orden = $row['dw_orden'];
    $this->ordenm = $row['dw_ordenm'];
    $this->updated = $row['dw_updated'];
  }

  function resultcon(){
    $sql = 'SELECT * FROM conceptos ORDER BY c_nmb';
    $this->resultdc = setq($sql);
  }

  function asignaconcepto($departamento,$concepto){
    $sql = 'INSERT IGNORE INTO departamento_concepto
            SET dc_departamento = "'.$departamento.'", dc_concepto = "'.$concepto.'"';
    setq($sql);
  }
  function desasignaconcepto($departamento,$concepto){
    $sql = 'DELETE FROM departamento_concepto
            WHERE dc_departamento = "'.$departamento.'" AND dc_concepto = "'.$concepto.'"';
    setq($sql);
  }

  function insertdepto($nombre){
    $sql = 'INSERT INTO departamentosw SET dw_nmb = "'.clearvmayus($nombre).'", dw_estatus = "A"';
    setq($sql);
    $this->idd = getmax('dw_id','departamentosw',false,false);
  }

  function updatedepto($id,$nombre){
    $sql = 'UPDATE departamentosw SET dw_nmb = "'.clearvmayus($nombre).'", dw_estatus = "A" WHERE dw_id = "'.$id.'"';
    setq($sql);
  }

  function resultc(){
    $sql = 'SELECT * FROM conceptos  ORDER BY c_id ASC';
    $this->result = setq($sql);
  }

  function selectc($id){
    $sql = 'SELECT * FROM conceptos WHERE c_id = "'.$id.'" ORDEr BY c_nmb ASC';
    $resultcc = setq($sql);
    $row = $resultcc->fetch_array();
    $this->id = $row['c_id'];
    $this->nmb = $row['c_nmb'];
    $this->updated = $row['c_updated'];
    $this->estatus = $row['c_estatus'];
  }

  function resultct(){
    $sql = 'SELECT * FROM categoriasw ORDER BY cw_id ASC';
    $this->resultct = setq($sql);
  }

  function asignacategoria($concepto,$categoria){
    $sql = 'INSERT IGNORE INTO concepto_categoria
            SET cc_concepto = "'.$concepto.'", cc_categoria = "'.$categoria.'"';
    setq($sql);
  }
  function desasignacategoria($concepto,$categoria){
    $sql = 'DELETE FROM concepto_categoria
            WHERE cc_concepto = "'.$concepto.'" AND cc_categoria = "'.$categoria.'"';
    setq($sql);
  }

  function insertconcepto($nombre){
    $sql = 'INSERT INTO conceptos SET c_nmb = "'.clearvmayus($nombre).'", c_estatus = "A"';
    setq($sql);
    $this->idd = getmax('c_id','conceptos',false,false);
  }

  function updateconcepto($id,$nombre){
    $sql = 'UPDATE conceptos SET c_nmb = "'.clearvmayus($nombre).'", c_estatus = "A" WHERE c_id = "'.$id.'"';
    setq($sql);
  }

  function selectcat($id){
    $sql = 'SELECT * FROM categoriasw WHERE cw_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['cw_id'];
    $this->nmb = $row['cw_nmb'];
    $this->updated = $row['cw_updated'];
    $this->estatus = $row['cw_estatus'];
    $this->catceo = $row['cw_categoria'];
  }

  function insertcategoria($nombre,$catceo){
    $sql = 'INSERT INTO categoriasw SET cw_nmb = "'.clearvmayus($nombre).'", cw_estatus = "A", cw_categoria = "'.$catceo.'"';
    setq($sql);
    $this->idd = getmax('cw_id','categoriasw',false,false);
  }

  function updatecategoria($id,$nombre,$catceo){
    $sql = 'UPDATE categoriasw SET cw_nmb = "'.clearvmayus($nombre).'", cw_estatus = "A", cw_categoria = "'.$catceo.'" WHERE cw_id = "'.$id.'"';
    setq($sql);
  }

}

class viewdepartamentosw{
    var $model;

    function __construct($model){
        $this->model = $model;
    }

    function browse(){
        ?>
        <div class="row page-title-actions">
            <div class="text-center alert alert-icon-left alert-arrow-left alert-info mb-2" style="display: <?php echo 'none'; ?>; text-align: center;" role="alert">
                <span class="alert-icon"><i class="fa fa-thumbs-o-up"></i></span>
                Estás modificando el departamento <strong><?php echo 'X'; ?></strong>
            </div>

            <div class="mb-5 col-md-2">
              <a data-fancybox data-type="ajax" data-src="popup/setdeptow.php" href="javascript:;" >
                <button type="button" class="btn btn-primary">
                    <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
                </button>
              </a>
            </div>
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-warning" href="?modulo=departamentosw&accion=conceptos">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-grid"></i> Conceptos
                </a>
            </div>
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-success" href="?modulo=departamentosw&accion=categorias" >
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-asterisk2"></i> Categorias
                </a>
            </div>
<!--
            <div class="ml-2 col-md-1">
                <button class="btn btn-secondary" data-toggle="tooltip" data-placement="bottom"
                data-original-title="Una linea de negocio es un organizador estadístico de tus productos, esto te permitirá analizar el comportamiento y crear planes a futuro" data-trigger="click" >
                    <i class="icon-question-circle"></i>
                </button>
            </div>
-->
        </div>

        <div id="filter-panel" class="col-md-9 collapse filter-panel">
            <div class="panel panel-default">
                <div class="panel-body">
                <form class="form-inline" role="form" method="post" action="?modulo=lineasn&accion=index">
                    <div class="mb-5 mr-2">
                    <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
                    </div><!-- form group [search] -->

                    <div class="mb-5">
                    <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                    </button>
                    <a href="?modulo=lineasn&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                    </button></a>
                    </div>
                </form>
                </div>
            </div>
            </div>

        </div></div>

        <?php
        $i=1;
    echo '<div class="row">
            <div class="container col-md-6">
              <div class="container table-responsive">
                <table class="table table-striped">
                  <thead class="bg-light-blue bg-darken-2 text-white">
                      <tr><th colspan="3">Departamentos Web</th></tr>
                      <tr>
                        <th>Agregar conceptos</th>
                        <th>Nombre Departamento</th>
                        <th>Editar</th>
                      </tr>
                  </thead>
                  <tbody>';
        while ($row = $this->model->result->fetch_array()){
          $i++;
        ?>
            <tr>
                <th scope="row" class=""> <button type="button" class="btn btn-info text-white">
                <a href="?modulo=departamentosw&accion=edit&id=<?php echo $row['dw_id']; ?>"><i class="fa fa-plus-circle"></i></a>
                </button></th>
                <td><?php echo $row['dw_nmb'];?></td>
                <td><a data-fancybox data-type="ajax" data-src="popup/setdeptow.php?id=<?php echo $row['dw_id'] ?>" href="javascript:;" >
                <button type="button" class="btn btn-primary">
                    <span class="glyphicon glyphicon-cog"></span><i class="fa fa-pen"></i> Editar
                </button>
              </a></td>
            </tr>
                        <?php   
            }

                echo ' </tbody>
                    </table>
                </div> 
            </div>
        </div>';

    }

    function edit(){
        ?>
            <div class="row page-title-actions">
            <div class="text-center alert alert-icon-left alert-arrow-left alert-info mb-2" style="display: <?php echo ''; ?>; text-align: center;" role="alert">
                <span class="alert-icon"><i class="fa fa-thumbs-o-up"></i></span>
                Estás modificando el departamento <strong><?php echo $this->model->nmb; ?></strong>
            </div>
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-danger active" href="?modulo=departamentosw&accion=index">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-th-list"></i> Departamentos
                </a>
            </div>
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-warning" href="?modulo=departamentosw&accion=conceptos">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-th-large"></i> Conceptos
                </a>
            </div>
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-success" href="?modulo=departamentosw&accion=categorias">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-grid"></i> Categorias
                </a>
            </div>
        </div>

        <div id="filter-panel" class="col-md-9 collapse filter-panel">
            <div class="panel panel-default">
                <div class="panel-body">
                <form class="form-inline" role="form" method="post" action="?modulo=lineasn&accion=index">
                    <div class="mb-5 mr-2">
                    <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
                    </div><!-- form group [search] -->

                    <div class="mb-5">
                    <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                    </button>
                    <a href="?modulo=lineasn&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                    </button></a>
                    </div>
                </form>
                </div>
            </div>
            </div>

        </div></div>

        <?php

  $i=0;
  echo '<form action="?modulo=departamentosw&accion=asignaconceptos&departamento='.$this->model->id.'" method="post">
              <div class="container table-responsive">
                <table class="table-striped">
                  <thead class="bg-light-blue bg-darken-2 text-white">
                  <tr>
                    <th colspan="4"><center><button type="submit" class="btn btn-success" />
                      <i class="fa fa-redo"></i> Actualizar departamentos
                    </button></center></th></tr>
                    <tr>
                      <th class=" p-1">Asignado</th>
                      <th class=" p-1">Concepto Nombre</th>

                      <th class=" p-1">Asignado</th>
                      <th class=" p-1">Concepto Nombre</th>
                    </tr>
                  </thead>
                <tbody>';

  $i = 0;
  echo '<tr>';
  while ($row = $this->model->resultdc->fetch_array()){
    $i++;

    if(busca($row['c_id'],'departamento_concepto','dc_departamento = "'.$this->model->id.'" AND dc_concepto','COUNT(*)') > 0){
        $check = 'checked';
    }
    else $check = '';

    echo '<td class="" style="width: 5%;" >
          <input type="checkbox" name="chcon'.$row['c_id'].'" '.$check.'>
          </td>
          <td class="p-1" style="width: 43%;">
            <a target="_BLANK" href="?modulo=departamentosw&accion=editc&id='.$row['c_id'].'">'.$row['c_nmb'].'</a>
          </td>';
    if($i%2 == 0) echo '</tr><tr>';
  }
  echo '</tr>';

  echo '</tbody>
      </table>
    </div> ';
                        
}

function browsec(){
  ?>
  <div class="row page-title-actions">
      <div class="text-center alert alert-icon-left alert-arrow-left alert-info mb-2" style="display: <?php echo 'none'; ?>; text-align: center;" role="alert">
          <span class="alert-icon"><i class="fa fa-thumbs-o-up"></i></span>
          Estás modificando el concepto <strong><?php echo 'X'; ?></strong>
      </div>

      <div class="mb-5 col-md-2">
        <a data-fancybox data-type="ajax" data-src="popup/setconcepto.php" href="javascript:;" >
          <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
          </button>
        </a>
      </div>
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-danger" href="?modulo=departamentosw&accion=index">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-th-list"></i> Departamentos
                </a>
            </div>
<!--
            <div class="ml-3 col-md-1">
                <a type="button" class="btn btn-warning" href="?modulo=departamentosw&accion=conceptos">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-th-large"></i> Conceptos
                </a>
            </div>
-->
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-success" href="?modulo=departamentosw&accion=categorias">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-grid"></i> Categorias
                </a>
            </div>
  </div>

  <div id="filter-panel" class="col-md-9 collapse filter-panel">
      <div class="panel panel-default">
          <div class="panel-body">
          <form class="form-inline" role="form" method="post" action="?modulo=lineasn&accion=index">
              <div class="mb-5 mr-2">
              <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
              </div><!-- form group [search] -->

              <div class="mb-5">
              <button type="submit" class="btn btn-info">
              <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
              </button>
              <a href="?modulo=lineasn&accion=index"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
              </button></a>
              </div>
          </form>
          </div>
      </div>
      </div>

  </div></div>

  <?php
  $i=1;
echo '<div class="row">
      <div class="container col-md-6">
        <div class="container table-responsive">
          <table class="table table-striped">
            <thead class="bg-light-blue bg-darken-2 text-white">
                <tr><th colspan="3">Conceptos Web</th></tr>
                <tr>
                  <th>Agregar Categorias</th>
                  <th>Nombre Departamento</th>
                  <th>Editar</th>
                </tr>
            </thead>
            <tbody>';
  while ($row = $this->model->resultdc->fetch_array()){
    $i++;
  ?>
      <tr>
          <th scope="row" class=""> <button type="button" class="btn btn-info text-white">
          <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar departamento"
          href="?modulo=departamentosw&accion=editc&id=<?php echo $row['c_id']; ?>"><i class="fa fa-plus-circle"></i></a>
          </button></th>
          <td><?php echo $row['c_nmb'];?></td>
          <td>
            <a data-fancybox data-type="ajax" data-src="popup/setconcepto.php?id=<?php echo $row['c_id'] ?>" href="javascript:;" >
              <button type="button" class="btn btn-primary">
                <span class="glyphicon glyphicon-cog"></span><i class="fa fa-pen"></i> Editar
              </button>
            </a>
          </td>
      </tr>
                  <?php

      }

          echo ' </tbody>
              </table>
          </div>
      </div>
  </div>';

}

function editc(){
  ?>
    <div class="row page-title-actions">
      <div class="text-center alert alert-icon-left alert-arrow-left alert-info mb-2" style="display: <?php echo ''; ?>; text-align: center;" role="alert">
          <span class="alert-icon"><i class="fa fa-thumbs-o-up"></i></span>
          Estás modificando el concepto <strong><?php echo $this->model->nmb; ?></strong>
      </div>
      <div class="mb-5 col-md-2">
          <a type="button" class="btn btn-danger" href="?modulo=departamentosw&accion=index">
              <span class="glyphicon glyphicon-cog"></span><i class="icon-th-list"></i> Departamentos
          </a>
      </div>
      <div class="mb-5 col-md-2">
          <a type="button" class="btn btn-warning active " href="?modulo=departamentosw&accion=conceptos">
              <span class="glyphicon glyphicon-cog"></span><i class="icon-th-large"></i> Conceptos
          </a>
      </div>
      <div class="mb-5 col-md-2">
          <a type="button" class="btn btn-success" href="?modulo=departamentosw&accion=categorias">
              <span class="glyphicon glyphicon-cog"></span><i class="icon-grid"></i> Categorias
          </a>
      </div>
  </div>

  <div id="filter-panel" class="col-md-9 collapse filter-panel">
      <div class="panel panel-default">
          <div class="panel-body">
          <form class="form-inline" role="form" method="post" action="?modulo=lineasn&accion=index">
              <div class="mb-5 mr-2">
              <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
              </div><!-- form group [search] -->

              <div class="mb-5">
              <button type="submit" class="btn btn-info">
              <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
              </button>
              <a href="?modulo=lineasn&accion=index"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
              </button></a>
              </div>
          </form>
          </div>
      </div>
      </div>

  </div></div>

  <?php

  $i=0;
  echo '<form action="?modulo=departamentosw&accion=asignacategoria&concepto='.$this->model->id.'" method="post">
              <div class="container table-responsive">
                <table class="table-striped">
                  <thead class="bg-light-blue bg-darken-2 text-white">
                  <tr>
                    <th colspan="4"><center><button type="submit" class="btn btn-success" />
                      <i class="fa fa-redo"></i> Actualizar Conceptos
                    </button></center></th></tr>
                    <tr>
                      <th class=" p-1">Asignado</th>
                      <th class=" p-1">Categorias Nombre</th>

                      <th class=" p-1">Asignado</th>
                      <th class=" p-1">Categorias Nombre</th>
                    </tr>
                  </thead>
                <tbody>';

  $i = 0;
  echo '<tr>';
  while ($row = $this->model->resultct->fetch_array()){
    $i++;

    if(busca($row['cw_id'],'concepto_categoria','cc_concepto = "'.$this->model->id.'" AND cc_categoria','COUNT(*)') > 0){
        $check = 'checked';
    }
    else $check = '';

    echo '<td class="" style="width: 5%;" >
          <input type="checkbox" name="chcat'.$row['cw_id'].'" '.$check.'>
          </td>
          <td class="p-1" style="width: 43%;">'.$row['cw_nmb'].'</td>';
    if($i%2 == 0) echo '</tr><tr>';
  }
  echo '</tr>';

  echo '</tbody>
      </table>
    </div> ';

}

    function browsect(){
        ?>
        <div class="row page-title-actions">
            <div class="text-center alert alert-icon-left alert-arrow-left alert-info mb-2" style="display: <?php echo 'none'; ?>; text-align: center;" role="alert">
                <span class="alert-icon"><i class="fa fa-thumbs-o-up"></i></span>
                Estás modificando la Categoria <strong><?php echo 'X'; ?></strong>
            </div>
            <div class="mb-5 col-md-2">
              <a data-fancybox data-type="ajax" data-src="popup/setcategoriasw.php" href="javascript:;" >
                <button type="button" class="btn btn-primary">
                    <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
                </button>
              </a>
            </div>
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-danger" href="?modulo=departamentosw&accion=index">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-grid"></i> Departamentos
                </a>
            </div>
            <div class="mb-5 col-md-2">
                <a type="button" class="btn btn-warning" href="?modulo=departamentosw&accion=conceptos">
                    <span class="glyphicon glyphicon-cog"></span><i class="icon-asterisk2"></i> Conceptos
                </a>
            </div>
        </div>
    
        <div id="filter-panel" class="col-md-9 collapse filter-panel">
            <div class="panel panel-default">
                <div class="panel-body">
                <form class="form-inline" role="form" method="post" action="?modulo=lineasn&accion=index">
                    <div class="mb-5 mr-2">
                    <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
                    </div><!-- form group [search] -->
    
                    <div class="mb-5">
                    <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                    </button>
                    <a href="?modulo=lineasn&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                    </button></a>
                    </div>
                </form>
                </div>
            </div>
            </div>
    
        </div></div>
    
        <?php
        $i=1;

  echo '
            <div class="container row">
              <div class="table-responsive col-md-4">
                <table class="table table-striped">
                  <thead class="bg-light-blue bg-darken-2 text-white">
                  <tr>
                    <th>Nombre Categorias</th>
                    <th width="20%"></th>
                  </tr>
                  </thead>
                <tbody>';
  while ($row = $this->model->resultct->fetch_array()){
  ?>
    <tr>
        <td><?php echo $row['cw_nmb'];?></td>
        <td>
          <a data-fancybox data-type="ajax" data-src="popup/setcategoriasw.php?id=<?php echo $row['cw_id']; ?>" href="javascript:;" >
            <button type="button" class="btn btn-primary">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-pen"></i> Editar
            </button>
          </a>
        </td>
    </tr>
  <?php
  }

  echo '  </tbody>
        </table>
      </div>
    </div>';

    }



}