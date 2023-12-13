<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pizzeria</title>
    <link rel="stylesheet" href="../tarea-evaluable3/styles/index.css">
</head>
<body>
    <?php
    if (isset($err)) {
        echo "<p>Revise usuario y contraseña</p>";
    }

    // Verifica si hay un usuario logueado
    if (isset($_SESSION['usuario'])) {
        echo "<p>Bienvenido, {$_SESSION['usuario']}! <a href='index.php'>Cerrar sesión</a></p>";

        // Verifica el rol del usuario
        if ($_SESSION['rol'] == '1') {
            echo "<h1>Panel de administración</h1>";
            echo "<p>Modifica precios, costes, etc.</p>";
            echo "<a href='zona_admin.php'>Crear nueva pizza</a>";

            // Aquí puedes mostrar otras secciones dependiendo del rol sin modificar los labels
        } else {
            echo "<h1>Nuestras Pizzas</h1>";
            // Llamamos a conectarDB para obtener la conexión
            $conn = conectarDB();
            listarPizzas($conn);
        }
    } else {
        // Si no está logueado, muestra el formulario de login
        echo "<h1>Pizzeria Alejandro's</h1>";
        echo "<h3>Inicie sesión para ver nuestras pizzas.</h3>";
        echo "<form method='POST' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
        echo "<label for='usuario'>Usuario</label>";
        echo "<input value='" . (isset($usuario) ? $usuario : '') . "' name='usuario' type='text'>";
        echo "<label for='clave'>Clave</label>";
        echo "<input name='clave' type='password'>";
        echo "<input type='submit' value='Iniciar Sesion'>";
        echo "</form>";
    }
    ?>
    <br>
    <a href="nuevo_usuario.php">Regristrarse</a>
</body>
</html>

<?php
function conectarDB()
{
    // Conectar a la base de datos con el puerto, usuario y clave
    $cadena_conexion = 'mysql:dbname=dwes_t3;host=127.0.0.1';
    $usuario = "root";
    $clave = "";

    // Variable $bd es un objeto PDO que contiene la conexión
    try {
        $bd = new PDO($cadena_conexion, $usuario, $clave);
        return $bd;
    } catch (PDOException $e) {
        echo "Error de conexión a la BD" . $e->getMessage();
    }
}

//comprobar usuario a traves de un get o un post
//get formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usu = isset($_POST["usuario"]) && isset($_POST["clave"]) ? comprobar_usuario($_POST["usuario"], $_POST["clave"]) : FALSE;
    if ($usu == FALSE) {
        $err = TRUE;
    } else {
        session_start();
        $_SESSION["usuario"] = $usu["usuario"];
        $_SESSION["rol"] = $usu["rol"];
        $_SESSION["nombre"] = $usu["nombre"];
        // Verifica el rol del usuario
        if ($_SESSION['rol'] == '1') {
            header("Location:zona_admin.php");
        } else {
            header("Location:pedido.php");
        }
    }
}

// Recibimos nombre y clave y lo usamos para hacer una consulta a la base de datos
function comprobar_usuario($nombre, $clave)
{
    $conn = conectarDB();
    $consulta = $conn->prepare("SELECT usuario,nombre,rol FROM USUARIOS WHERE usuario = '$nombre' AND clave = '$clave'");
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        $row = $consulta->fetch(PDO::FETCH_ASSOC);
        return array("usuario" => $row["usuario"], "rol" => $row["rol"], "nombre" => $row["nombre"]);
    } else {
        return FALSE;
    }
}
?>