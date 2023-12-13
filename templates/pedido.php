<?php

// Inicia o reanuda la sesión del usuario
session_start();

// Función para establecer conexión a la base de datos
function conectarDB()
{
    $cadena_conexion = 'mysql:dbname=dwes_t3;host=127.0.0.1';
    $usuario = "root";
    $clave = "";

    try {
        // Establece la conexión usando PDO
        $bd = new PDO($cadena_conexion, $usuario, $clave);
        return $bd;
    } catch (PDOException $e) {
        // En caso de error, muestra un mensaje
        echo "Error de conexión a la BD" . $e->getMessage();
    }
}

// Obtiene la conexión a la base de datos
$conn = conectarDB();

// Función para listar pizzas (nombre y precio) desde la base de datos
function listarPizzas($conn)
{
    $consulta = $conn->prepare("SELECT nombre, precio FROM pizza");
    $consulta->execute();

    // Muestra la lista de pizzas
    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo $row["nombre"] . "➞" . $row["precio"] . "€.<br>";
    }
}

// Procesa el formulario si se ha enviado por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["pizza"]) && isset($_POST["cantidad"])) {
        // Asumiendo el ID del cliente
        $id_cliente = 1;
        // Obtiene la fecha y hora actuales
        $fecha_pedido = date("Y-m-d H:i:s");
        $detalle_pedido = "";
        $total = 0;

        // Recupera datos del formulario
        $pizza = $_POST["pizza"];
        $cantidad = $_POST["cantidad"];

        // Obtiene información de la pizza desde la base de datos
        $consultaPizza = $conn->prepare("SELECT nombre, precio FROM pizza WHERE nombre = :pizza");
        $consultaPizza->bindParam(':pizza', $pizza);
        $consultaPizza->execute();
        $row = $consultaPizza->fetch(PDO::FETCH_ASSOC);

        // Actualiza el detalle del pedido y calcula el total
        $detalle_pedido .= $row["nombre"] . ", Cantidad: $cantidad, Precio: " . $row["precio"] . "€; ";
        $total += $cantidad * $row["precio"];

        // Inserta en la tabla de pedidos
        $consultaInsertar = $conn->prepare("INSERT INTO pedidos (id_cliente, fecha_pedido, detalle_pedido, total) VALUES (:id_cliente, :fecha_pedido, :detalle_pedido, :total)");
        $consultaInsertar->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $consultaInsertar->bindParam(':fecha_pedido', $fecha_pedido);
        $consultaInsertar->bindParam(':detalle_pedido', $detalle_pedido);
        $consultaInsertar->bindParam(':total', $total);

        // Ejecuta la inserción y muestra un mensaje
        if ($consultaInsertar->execute()) {
            echo "Pedido realizado con éxito";
        } else {
            echo "Error al realizar el pedido";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos</title>
    <link rel="stylesheet" href="../tarea-evaluable3/styles/pedido.css">
</head>

<body>
    <div class="container">
        <?php
        // Muestra un mensaje de bienvenida si el usuario está autenticado
        if (isset($_SESSION["nombre"])) {
            echo "<div class='welcome'><h2>Bienvenido " . $_SESSION["nombre"] . "</h2></div>";
        }
        echo "<div class='content'>";
        echo "<div class='pizza-list'>";
        echo "<h1>Nuestras Pizzas</h1>";

        // Lista las pizzas disponibles llamando a la función listarPizzas
        echo "<ul>";
        listarPizzas($conn);
        echo "</ul>";
        echo "</div>";

        ?>
        <div class="video-container">
            <!-- Muestra un video de fondo -->
            <iframe width="560" height="315" src="https://www.youtube.com/embed/dXx_4n217Js?autoplay=1" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>

    <h2>Realizar Pedido</h2>
    <!-- Formulario para realizar pedidos -->
    <form class="form-container" action="gracias.php" method="POST">
        <label for="pizza">Selecciona una pizza:</label>
        <select name="pizza" id="pizza">
            <?php
            // Obtiene la lista de pizzas desde la base de datos
            $consulta = $conn->prepare("SELECT nombre FROM pizza");
            $consulta->execute();

            foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $row) {
                // Muestra opciones en el menú desplegable
                echo "<option value='" . $row["nombre"] . "'>" . $row["nombre"] . "</option>";
            }
            ?>
        </select>

        <label for="cantidad">Cantidad:</label>
        <!-- Campo para ingresar la cantidad de pizzas -->
        <input type="number" name="cantidad" value="1" min="1">

        <br>
        <!-- Botón para enviar el formulario -->
        <input type="submit" value="Añadir al Pedido">
    </form>

    <br>
    <form class="logout-form" action="index.php" method="POST">
        <!-- Formulario para cerrar sesión -->
        <input type="submit" value="Cerrar Sesión">
    </form>
    </div>

</body>

</html>