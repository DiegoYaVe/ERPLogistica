<!DOCTYPE html>
<html>
<head>
  <title>Tabla Arrastrable Estilizada con Sortable</title>
  <!-- Incluye los estilos de Materialize -->
  <link rel="stylesheet" href="../assets/css/materialize.min.css">
  <!-- Incluye los estilos personalizados para la tabla arrastrable -->
  <style>
    /* Añade estilos personalizados para la tabla arrastrable */
    .sortable-table {
      width: 100%;
    }
    .sortable-item {
      background-color: #f5f5f5;
      border: 1px solid #e0e0e0;
      padding: 10px;
      cursor: grab;
    }
  </style>
</head>
<body>
  <form id="ordenForm" action="prueba2.php" method="POST">
    <div class="container mt-5">
      <h1>Mi Tabla Arrastrable Estilizada</h1>

      <!-- Tabla para los elementos arrastrables -->
      <table id="mi-tabla" class="sortable-table">
        <thead>
          <tr>
            <th>Elemento</th>
          </tr>
        </thead>
        <tbody>
          <tr class="sortable-item" data-valor="1">
            <td><span id="elemento1" name="orden[]" value="1">Diego 11</span></td>
          </tr>
          <tr class="sortable-item" data-valor="2">
            <td><span id="elemento2" name="orden[]" value="2">Diego 22</span></td>
          </tr>
          <tr class="sortable-item" data-valor="3">
            <td><span id="elemento3" name="orden[]" value="3">Diego 33</span></td>
          </tr>
          <tr class="sortable-item" data-valor="4">
            <td><span id="elemento4" name="orden[]" value="4">Diego 44</span></td>
          </tr>
        </tbody>
      </table>

      <!-- Botón para guardar el orden -->
      <button type="button" id="guardarOrden" class="btn btn-primary mt-3">Guardar Orden</button>    
    </div>
  </form>
  

  <!-- Incluye jQuery (necesario para Sortable) -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <!-- Incluye la biblioteca Sortable -->
  <script src="../assets/js/Sortable.min.js"></script>
  <!-- Incluye los scripts de Materialize -->
  <script src="../assets/js/materialize.min.js"></script>
  <script>
    // Inicializar la tabla arrastrable
    const miTabla = document.getElementById('mi-tabla').getElementsByTagName('tbody')[0];
    const guardarOrdenBtn = document.getElementById('guardarOrden');

    const sortable = new Sortable(miTabla, {
      animation: 150, // Duración de la animación en milisegundos (opcional)
      group: 'mi-tabla', // Grupo para habilitar el arrastre entre diferentes tablas (opcional)
      onEnd: function(evt) {
        guardarOrdenBtn.disabled = false; // Habilitar el botón para guardar el orden
      }
    });

    guardarOrdenBtn.addEventListener('click', function() {
    // Crear el formulario
    const myForm = document.createElement("form");

    // Asignar un ID al formulario
    myForm.id = "orden-prospectos";

    // Asignar la URL de acción y el método
    myForm.action = "prueba2.php";
    myForm.method = "POST";

    // Obtener las celdas con los datos que deseas enviar
    const celdasSpan = document.querySelectorAll('#mi-tabla tbody td span');

    // Bucle que recorre los spans y guarda los valores e ids en la matriz "orden"
    for (var i = 0; i < celdasSpan.length; i++) {
        // Obtener el valor y el id del span
        let span = celdasSpan[i];
        let valor = span.innerText;
        let id = span.id;

        // Crear un input oculto para enviar el orden por POST
        let ordenValor = document.createElement('input');
        ordenValor.type = 'hidden';
        ordenValor.name = 'valor' + i;
        ordenValor.value = JSON.stringify(valor);
        myForm.appendChild(ordenValor);

        let ordenId = document.createElement('input');
        ordenId.type = 'hidden';
        ordenId.name = 'id' + i;
        ordenId.value = JSON.stringify(id);
        myForm.appendChild(ordenId);
    }
    let tamano = document.createElement('input');
    tamano.type = 'hidden';
    tamano.name = 'tamano';
    tamano.value = JSON.stringify(celdasSpan.length);
    myForm.appendChild(tamano);

    // Agregar el formulario al cuerpo del documento
    document.body.appendChild(myForm);

    // Enviar el formulario
    myForm.submit();
});



    
  </script>
</body>
</html>
