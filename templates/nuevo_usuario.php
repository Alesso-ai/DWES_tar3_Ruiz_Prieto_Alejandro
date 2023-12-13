<?php

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

// Función para verificar si un usuario ya existe en la base de datos
function usuarioExiste($conn, $usuario)
{
    $consulta = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
    $consulta->bindParam(':usuario', $usuario);
    $consulta->execute();
    $resultado = $consulta->fetchColumn();
    return $resultado > 0;
}

// Procesa el formulario si se ha enviado por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Establece conexión a la base de datos
    $conn = conectarDB();
    
    // Obtiene datos del formulario
    $usuario = $_POST["usuario"];
    $nombre = $_POST["nombre"];
    $clave = $_POST["clave"];
    $correo = $_POST["correo"];
    $rol = 2;

    // Verifica si el usuario ya existe
    if (usuarioExiste($conn, $usuario)) {
        echo "El nombre de usuario ya existe. Por favor, elija otro.";
    } else {
        // Inserta el nuevo usuario en la base de datos
        $consulta = $conn->prepare("INSERT INTO usuarios (usuario, nombre, clave, correo,rol) VALUES (:usuario, :nombre, :clave, :correo, :rol)");
        $consulta->bindParam(':usuario', $usuario);
        $consulta->bindParam(':nombre', $nombre);
        $consulta->bindParam(':clave', $clave);
        $consulta->bindParam(':correo', $correo);
        $consulta->bindParam(':rol', $rol);

        try {
            $consulta->execute();

            // Inicia la sesión para el nuevo usuario
            session_start();
            $_SESSION['usuario'] = $usuario;
            $_SESSION['rol'] = $rol;
            $_SESSION['nombre'] = $nombre;

            // Redirige a la página de pedidos
            header('Location: pedido.php');
        } catch (PDOException $e) {
            // En caso de error, muestra un mensaje
            echo "Error al crear el usuario: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../tarea-evaluable3/styles/nuevo_usuario.css">
</head>

<body>
    <h1>Registro de Usuario</h1>
    <!-- Formulario para crear un nuevo usuario -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" required><br>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required><br>

        <label for="clave">Clave:</label>
        <input type="password" name="clave" required><br>

        <label for="correo">Correo:</label>
        <input type="email" name="correo" required></input><br>

        <br>
        <!-- Botón para enviar el formulario -->
        <input type="submit" value="Crear Usuario"></input>
    </form>
</body>

</html>
