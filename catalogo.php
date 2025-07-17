<?php
//ini_set('display_errors',1);
include_once('funciones.php');
/* $sucursal = 2;

$total = 0;
foreach($_COOKIE['cartinf'] as $clave=>$item){
  $sqlart = 'SELECT * FROM articulos INNER JOIN articulosw ON a_id = aw_articulo WHERE a_estatus = "A" AND a_id = "'.$item[0].'" AND aw_sucursal = "'.$sucursal.'" AND aw_estatus = "A" ';
  $resultart = setqnube($sqlart);
  $total = $resultart->num_rows;
}
$nmbsucursal = buscanube($sucursal,'sucursales','s_id','s_nmb');

if(!isset($_COOKIE['sucursal']) || $_COOKIE['sucursal'] != $sucursal){
  redirect('index.php');
  die();
}
 */
?>
<!doctype html>
<html data-theme="light" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../dist/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,600;1,600;1,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../dist/owl.carousel.min.css">
  <link rel="stylesheet" href="../dist/owl.theme.green.css">
  <link rel="stylesheet" href="../dist/custom.css">
  <title>La Fabrica de inflables</title> 
  <link rel="icon" type="images/png" href="assets/media/logos/favicon.png">
</head>


<body class="">



<!-- SM -->
<div class="navbar flex md:hidden justify-center" style="position: relative; z-index: 100; background: #007cff">
    <div class="">
      <a href="#" class="btn btn-ghost normal-case text-xl">
        <img src="img/UNICO-LOGO-OFICIAL.png" alt="" class="max-w-[6rem] text-black">
      </a>
    </div>
</div>

<div class="navbar flex md:hidden text-black" style="position: relative; z-index: 100;background: #007cff">
  <div class="navbar-start">
    <div class="dropdown" style="display:none;">
      <label tabindex="0" class="btn btn-ghost btn-circle">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
      </label>
      <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
        <li><a>Homepage</a></li>
        <li><a>Portfolio</a></li>
        <li><a>About</a></li>
      </ul>
    </div>
  </div>
  <div class="navbar-center max-w-[13rem] text-center">
    <h2 class="text-lg font-bold italic text-white ">
      Catálogo - La Fabrica de Inflables
    </h2>
  </div>
  <div class="navbar-end">
  </div>
</div>
<!-- SM -->


<!-- LG -->

<div hidden class="hidden md:flex navbar  text-black " style="position: relative; z-index: 99; box-shadow: 0px 12px 20px 0px black; background: #007cff">
  <div class="navbar-start">
    <div class="dropdown" style="display:none;">
      <label tabindex="0" class="btn btn-ghost lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
      </label>
      <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
        <li><a>Item 1</a></li>
        <li>
          <a>Parent</a>
          <ul class="p-2">
            <li><a>Submenu 1</a></li>
            <li><a>Submenu 2</a></li>
          </ul>
        </li>
        <li><a>Item 3</a></li>
      </ul>
    </div>
    <div class="">
    <a href="#" class="">
      <img src="img/UNICO-LOGO-OFICIAL.png" alt="" class="max-w-[6rem] text-black">
    </a>
    </div>
  </div>
  <div class="navbar-center hidden lg:block">
    <!-- <ul class="menu menu-horizontal px-1 font-bold">
      <li><a>Item 1</a></li>
      <li tabindex="0">
        <details>
          <summary>Parent</summary>
          <ul class="p-2">
            <li><a>Submenu 1</a></li>
            <li><a>Submenu 2</a></li>
          </ul>
        </details>
      </li>
      <li><a>Item 3</a></li>
    </ul> -->
    <div class="text-center">
      <h2 class="text-3xl font-extrabold italic text-white">
        Catálogo - La fabrica de inflables
      </h2>
      <!-- <h2 class="font-bold text-xs">
        Lunes a Viernes de 12:00 a 21:00hrs
      </h2>
      <h2 class="font-bold text-xs">
        Sábado y Domingo de 10:00 a 21:00hrs
      </h2> -->
    </div>
  </div>
  <div class="navbar-end">
  <!-- <a href="#"> 
      <button  class="mt-4 mb-8 w-full rounded-md bg-black px-6 py-3 font-medium text-white">Comprar accesos</button>
    </a> -->
    <!-- <div class="hidden lg:block ">
      <a href="" class="btn bg-black text-white border-0">Compra tus accesos</a>
    </div> -->
    <!-- <a class="text-black cursor-pointer" href="setdetalles?s=<?php echo $sucursal; ?>">
    <div class="indicator">
      <span class="indicator-item indicator-start badge bg-white text-black"><?php echo $total; ?></span> 
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 hover:stroke-2 transition duration-300 ease-in-out hover:scale-110">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
      </svg>
    </div>
    </a> -->
  </div>
</div>

<!-- LG -->

    <section class="min-h-screen bg-infla" style="background-image: url('images/fondo-landing.jpg'); background-size: cover; background-position: center; min-height: 85vh">
      <div class="justify-items-center grid grid-cols-1 md:grid-cols-3 gap-8 p-4">
      <?php 
				$sql = 'SELECT SUM(e_cantidad) AS cantidad, e_articulo, e_modelo, a_nmb FROM existencias LEFT JOIN articulos ON a_id = e_articulo WHERE a_estatus = "A" AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") GROUP BY e_articulo, e_modelo ORDER BY `articulos`.`a_nmb` ASC';
				$result = setq($sql);
				while($row = $result -> fetch_array()){
					if($row['e_modelo']){
						$nmb = $row['a_nmb'].' '.busca($row['e_articulo'], 'articulos_variantes','av_modelo = "'.$row['e_modelo'].'" AND av_articulo', 'av_nmb');
						$precio = busca($row['e_articulo'], 'articulos_precios', 'ap_modelo = "'.$row['e_modelo'].'" AND ap_activo = 1 AND ap_articulo', 'ap_precio');
						$query = 'SELECT CONCAT("img/",ad_ruta) AS nombre_completo FROM articulos_descargas WHERE ad_articulo = "'.$row['e_articulo'].'" AND ad_modelo = "'.$row['e_modelo'].'" ORDER BY ad_id DESC LIMIT 1';
					} else {
						$nmb = $row['a_nmb'];
						$precio = busca($row['e_articulo'], 'articulos_precios', 'ap_activo = 1 AND ap_articulo', 'ap_precio');
						$query = 'SELECT CONCAT("img/productos/",i_nmb, ".", i_ext) AS nombre_completo FROM imagenes WHERE i_idp = "'.$row['e_articulo'].'" ORDER BY i_idimg DESC LIMIT 1';
					}
					$resultq = setq($query);
  				list($rutaimg) = $resultq->fetch_array();
					$existencia = number_format($row['cantidad'], 0);
					?>
						<div class="bg-infla rounded-[2.1rem] w-[20rem] lg:w-[30rem] pt-5 pb-6" style="border-width: 5px; border-color: white;">
							<div class="justify-items-center">
								<img src="<?php echo $rutaimg ?>" alt="Producto" style="border-radius: 35px; width: 65%">
							</div>
							<div class="px-10 mt-5 text-black text-shadow justify-items-center">
								<h3 class="text-xl lg:text-2xl font-black italic text-left"><?php echo $nmb ?></h3>
								<p class="text-xl lg:text-3xl font-semibold italic text-left mt-2">Precio: $<?php echo number_format($precio, 2) ?></p>
								<p class="text-xl lg:text-3xl font-semibold italic text-left">Existencia: <?php echo $existencia ?></p>
							</div>
						</div>
					<?php	
					
				}
			?>
      </div>
      <br class="">
    </section>
<style>
  img.responsive-img {
    width: 100%;
    height: auto;
    max-height: 50rem;
    object-fit: contain; 
  }

  @media (min-width: 1024px) {
    img.responsive-img {
      max-height: 20rem;
    }
  }

  @media (min-width: 1280px) {
    img.responsive-img {
      max-height: 20rem;
    }
  }
</style>

<section class="">
  <div class=" grid grid-cols-1 lg:grid-cols-2 px-16 py-5" style="background: #007cff">
    <div class="">
    <div id="footer-beta" class="hide-at-xs hide-at-sm">
      <div class="footer-container">
        <div class="footer-links-container">
          <a href="#" class="text-white">Todos los derechos reservados. La Fabrica de Inflables 2023</a>
        </div>
      </div>
    </div>
    <div class="">
    
    </div>

  </div>
</section>


</body>

<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/main.js"></script>
<script src="../js/owl.carousel.min.js"></script>
<script class="">
  $('#main').owlCarousel({
    loop:true,
    autoplay: true,
    margin:10,
    nav:false,
    touch: true,
    responsive:{
        0:{
            items:1
        },
        600:{
            items:1
        },
        1000:{
            items:1
        }
    }
  });
  $('#ptr').owlCarousel({
    autoplay: true,
    loop:true,
    margin:10,
    nav:false,
    dots: false,
    items: 2,
    center: true,
    responsive:{
        600:{
            items:4
        }
    }
  })

  function finform(){
    console.log("Entro aqui");
    var botonfin = document.getElementById("botonfin");
    var form = document.getElementById("formrev");
    
    botonfin.disabled = true;
  }
</script>

</html>