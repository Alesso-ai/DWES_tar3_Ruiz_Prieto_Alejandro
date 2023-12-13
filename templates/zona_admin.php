<?php

function conectarDB()
{
    $cadena_conexion = 'mysql:dbname=dwes_t3;host=127.0.0.1';
    $usuario = "root";
    $clave = "";

    try {
        $bd = new PDO($cadena_conexion, $usuario, $clave);
        return $bd;
    } catch (PDOException $e) {
        echo "Error de conexión a la BD" . $e->getMessage();
    }
}

$conn = conectarDB();

function listarPizzas($conn)
{
    $consulta = $conn->prepare("SELECT nombre, precio FROM pizza");
    $consulta->execute();

    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo $row["nombre"] . "➞" . $row["precio"] . "€.<br>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conecta a la base de datos
    $conn = conectarDB();

    // Verifica si se está borrando una pizza
    if (isset($_POST["borrar_pizza"]) && !empty($_POST["pizza_borrar"])) {
        $pizza_id = $_POST["pizza_borrar"];

        $consulta_borrar = $conn->prepare("DELETE FROM pizza WHERE id = :pizza_id");
        $consulta_borrar->bindParam(':pizza_id', $pizza_id);
        $consulta_borrar->execute();
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    }

    // Recoge los datos del formulario
    $nombre = $_POST["nombre"];
    $coste = $_POST["coste"];
    $precio = $_POST["precio"];
    $ingredientes = $_POST["ingredientes"];
    $pizza_id = $_POST["pizza"];

    if (!empty($nombre) && isset($coste) && isset($precio) && !empty($ingredientes)) {
        // Si hay una pizza seleccionada desde el desplegable, actualiza en lugar de insertar
        if (!empty($pizza_id)) {
            $consulta_actualizar = $conn->prepare("UPDATE pizza SET nombre = :nombre, coste = :coste, precio = :precio, ingredientes = :ingredientes WHERE id = :pizza_id");
            $consulta_actualizar->bindParam(':pizza_id', $pizza_id);
        } else {
            // Si no hay una pizza seleccionada, inserta una nueva
            $consulta_actualizar = $conn->prepare("INSERT INTO pizza (nombre, coste, precio, ingredientes) VALUES (:nombre, :coste, :precio, :ingredientes)");
        }

        // Obtiene la información de la pizza seleccionada
        if (!empty($pizza_id)) {
            $consultaPizza = $conn->prepare("SELECT nombre, coste, precio, ingredientes FROM pizza WHERE id = :pizza_id");
            $consultaPizza->bindParam(':pizza_id', $pizza_id);
            $consultaPizza->execute();
            $pizzaDatos = $consultaPizza->fetch(PDO::FETCH_ASSOC);

            // Rellena los campos del formulario con los datos de la pizza seleccionada
            $_POST["nombre"] = $pizzaDatos['nombre'];
            $_POST["coste"] = $pizzaDatos['coste'];
            $_POST["precio"] = $pizzaDatos['precio'];
            $_POST["ingredientes"] = $pizzaDatos['ingredientes'];
        }

        // Asocia los parámetros
        $consulta_actualizar->bindParam(':nombre', $nombre);
        $consulta_actualizar->bindParam(':coste', $coste);
        $consulta_actualizar->bindParam(':precio', $precio);
        $consulta_actualizar->bindParam(':ingredientes', $ingredientes);

        // Ejecuta la consulta
        $consulta_actualizar->execute();
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zona Admin</title>
    <link rel="stylesheet" href="../tarea-evaluable3/styles/zona_admin.css">

</head>

<body>
    <h1>Nuestras Pizzas</h1>
    <?php listarPizzas($conn); ?>

    <h1>Personaliza tu Pizza</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
        <?php
        $pizzaSeleccionada = isset($_POST['pizza']) ? $_POST['pizza'] : '';

        // Obtiene la información de la pizza seleccionada
        if (!empty($pizzaSeleccionada)) {
            $consultaPizza = $conn->prepare("SELECT nombre, coste, precio, ingredientes FROM pizza WHERE id = :pizza_id");
            $consultaPizza->bindParam(':pizza_id', $pizzaSeleccionada);
            $consultaPizza->execute();
            $pizzaDatos = $consultaPizza->fetch(PDO::FETCH_ASSOC);
        }
        ?>

        <label for="nombre">Nombre de la Pizza:</label>
        <input type="text" id="nombre" name="nombre" required value="<?php echo isset($pizzaDatos['nombre']) ? $pizzaDatos['nombre'] : ''; ?>"><br>

        <label for="coste">Coste:</label>
        <input type="number" step="0.01" id="coste" name="coste" required value="<?php echo isset($pizzaDatos['coste']) ? $pizzaDatos['coste'] : ''; ?>"><br>

        <label for="precio">Precio:</label>
        <input type="number" step="0.01" id="precio" name="precio" required value="<?php echo isset($pizzaDatos['precio']) ? $pizzaDatos['precio'] : ''; ?>"><br>

        <label for="ingredientes">Ingredientes:</label>
        <textarea id="ingredientes" name="ingredientes" required><?php echo isset($pizzaDatos['ingredientes']) ? $pizzaDatos['ingredientes'] : ''; ?></textarea><br>

        <input type="submit" value="Crear/Actualizar Pizza">
    </form>

    <h2>Borrar Pizza</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
        <label for="pizza_borrar">Selecciona una pizza para borrar:</label>
        <select name="pizza_borrar" id="pizza_borrar">
            <?php
            $consultaPizzas = $conn->prepare("SELECT id, nombre FROM pizza");
            $consultaPizzas->execute();

            foreach ($consultaPizzas->fetchAll(PDO::FETCH_ASSOC) as $row) {
                echo "<option value='" . $row["id"] . "'>" . $row["nombre"] . "</option>";
            }
            ?>
        </select>
        <input type="submit" name="borrar_pizza" value="Borrar Pizza">
    </form>

    <form action="index.php" method="POST">
        <input type="submit" value="Cerrar Sesión">
    </form>

    <div class="imagen-pizza">
        <img src="../tarea-evaluable3/assets/imgs/Pizza.png" alt="Pizza" class="rotating-pizza">

    </div>
</body>

</html>