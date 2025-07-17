<?php
session_start();
include_once('../funciones.php');

$idalmacen = $_GET['id'];

if($idalmacen){
  $sql = 'SELECT * FROM almacenes WHERE a_id = "'.$idalmacen.'"';
  $result = setq($sql);
  $row = $result->fetch_array();
}

if(!$idalmacen) $titulo = 'Agregar'; else $titulo = 'Actualizar';

if($row['a_id']) $accion = 'update'; else $accion = 'insert';


echo '
  <div class="container">
    <div class="alert alert-primary">'.$titulo.' almacen</div>
    <form method="post" action="?modulo=almacenes&accion='.$accion.'" enctype="multipart/form-data">

    <div class="row">
      <input type="hidden" value="'.$row['a_id'].'" name="id" />
      <div class="col-md-10 mb-5">
        <label>Nombre del almacen: </label>
        <input type="text" id="nmb" class="form-control focus mayus" placeholder="Dale un nombre a tu almacen" name="nmb" value="'.$row['a_nmb'].'" required="required" >
      </div>
      <div class="col-md-2 mb-5">
        <label>Vendible:</label>';
        if($row['a_vendible']) $vendible = "checked"; else $vendible = "";
        echo '<input type="checkbox" name="vendible" '.$vendible.' class="flipswitchalm" />
      </div>
      </div>

      <div class="row">
      <div class="col-md-6 mb-5">
        <label>Calle:</label> 
        <input type="text" name="calle" value="'.$row['a_calle'].'" class="form-control" />
      </div>
      <div class="col-md-3 mb-5">
        <label>Num Ex.:</label> 
        <input type="text" name="nume" value="'.$row['a_nume'].'" class="form-control" />
      </div>
      <div class="col-md-3 mb-5">
        <label>Num Int.:</label> 
        <input type="text" name="numi" value="'.$row['a_numi'].'" class="form-control" />
      </div>
      </div>

      <div class="row">
      <div class="col-md-4 mb-5">
        <label>Colonia:</label> 
        <input type="text" name="colonia" value="'.$row['a_colonia'].'" class="form-control" />
      </div>
      <div class="col-md-4 mb-5">
        <label>Ciudad:</label> 
        <input type="text" name="ciudad" value="'.$row['a_ciudad'].'" class="form-control" />
      </div>
      <div class="col-md-4 mb-5">
        <label>Estado:</label> 
        <input type="text" name="estado" value="'.$row['a_estado'].'" class="form-control" />
      </div>
      </div>

      <div class="row">
      <div class="col-md-2 mb-5">
        <label>Pais:</label> 
        <input type="text" name="pais" value="'.$row['a_pais'].'" class="form-control" />
      </div>
      <div class="col-md-2 mb-5">
        <label>CP:</label> 
        <input type="text" name="cp" value="'.$row['a_cp'].'" class="form-control" />
      </div>
      <div class="col-md-2 mb-5">
        <label>Telefono:</label> 
        <input type="number" name="telefono" value="'.$row['a_telefono'].'" class="form-control"  />
      </div>
      <div class="col-md-3 mb-5">
        <label>Correo:</label> 
        <input type="email" name="correo" value="'.$row['a_correo'].'" class="form-control" />
      </div>';
      if (!$row['a_img'])
        echo '
        <div class="col-md-3 mb-5">
          <label>Imagen:</label> 
          <input type="file" name="img" class="form-control" />
        </div>';  
      else
        echo '
        <div class="col-md-3 mb-5">
          <label>Imagen</label><br>
          <img src="'.$row['a_img'].'" alt="" class="width-100 img-thumbnail">
          <button type="button" onclick="borrarimg('.$idalmacen.');" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
        </div>
        <script class="">
          function borrarimg(id){
            var c = confirm("¿Estás seguro de eliminar la imagen del almacen?");
            if(c){
              window.location.href="?modulo=almacenes&accion=borrarimg&id="+id;
            }
          }
        </script>
        '; 

      echo '
      </div>
      <div class="col-md-12 text-xs-center mb-5" style="display: flex; justify-content: center;">
        <!-- <label>Guardar: </label> <br> -->
        <button type="submit" class="btn btn-primary"><i class="fa fa-save">&nbsp;Guardar</i>
      </div>
    </form>
  </div>';

?>