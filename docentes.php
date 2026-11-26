```php
<?php
require_once "config/conexion.php";

function e($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$mensaje = "";
$tipoMensaje = "info";
$editar = null;
$docentes = [];

try {

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("La conexión PDO no está disponible.");
    }

    // PROCESAR FORMULARIOS
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $accion = $_POST["accion"] ?? "";

        if ($accion === "guardar" || $accion === "actualizar") {

            $nombre = trim($_POST["nombre_completo"] ?? "");
            $correo = trim($_POST["correo"] ?? "");
            $telefono = trim($_POST["telefono"] ?? "");
            $numEmpleado = trim($_POST["numero_empleado"] ?? "");
            $contrasena = trim($_POST["contrasena"] ?? "");

            if ($nombre === "" || $correo === "" || $telefono === "" || $numEmpleado === "") {
                throw new Exception("Completa todos los campos obligatorios.");
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Ingresa un correo electrónico válido.");
            }

            if ($accion === "guardar") {

                if ($contrasena === "") {
                    throw new Exception("La contraseña es obligatoria para registrar un docente.");
                }

                $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                $sql = "INSERT INTO docentes
                (nombre_completo, correo, telefono, numero_empleado, contrasena)
                VALUES (?, ?, ?, ?, ?)";

                $pdo->prepare($sql)->execute([
                    $nombre,
                    $correo,
                    $telefono,
                    $numEmpleado,
                    $hash
                ]);

                $mensaje = "Docente registrado correctamente.";
                $tipoMensaje = "success";

            } else {

                $id = (int)($_POST["id_docente"] ?? 0);

                if ($id <= 0) {
                    throw new Exception("Identificador de docente inválido.");
                }

                if ($contrasena !== "") {

                    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                    $sql = "UPDATE docentes SET
                    nombre_completo = ?,
                    correo = ?,
                    telefono = ?,
                    numero_empleado = ?,
                    contrasena = ?
                    WHERE id_docente = ?";

                    $pdo->prepare($sql)->execute([
                        $nombre,
                        $correo,
                        $telefono,
                        $numEmpleado,
                        $hash,
                        $id
                    ]);

                } else {

                    $sql = "UPDATE docentes SET
                    nombre_completo = ?,
                    correo = ?,
                    telefono = ?,
                    numero_empleado = ?
                    WHERE id_docente = ?";

                    $pdo->prepare($sql)->execute([
                        $nombre,
                        $correo,
                        $telefono,
                        $numEmpleado,
                        $id
                    ]);
                }

                $mensaje = "Docente actualizado correctamente.";
                $tipoMensaje = "success";
            }

        } elseif ($accion === "eliminar") {

            $id = (int)($_POST["id_docente"] ?? 0);

            if ($id <= 0) {
                throw new Exception("Identificador de docente inválido.");
            }

            $pdo->prepare(
                "DELETE FROM docentes WHERE id_docente = ?"
            )->execute([$id]);

            $mensaje = "Docente eliminado correctamente.";
            $tipoMensaje = "success";
        }
    }

    // CONSULTAR DOCENTE PARA EDITAR
    if (isset($_GET["editar"])) {

        $id = (int)$_GET["editar"];

        $st = $pdo->prepare(
            "SELECT * FROM docentes WHERE id_docente = ?"
        );

        $st->execute([$id]);

        $editar = $st->fetch(PDO::FETCH_ASSOC);

        if (!$editar) {
            $mensaje = "El docente solicitado no existe.";
            $tipoMensaje = "warning";
        }
    }

    // CONSULTAR DOCENTES
    $docentes = $pdo->query(
        "SELECT * FROM docentes ORDER BY id_docente DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {

    if ($ex->getCode() == "23000") {
        $mensaje = "El correo o número de empleado ya se encuentra registrado, o el docente tiene viajes relacionados.";
    } else {
        $mensaje = "Error en la base de datos: " . $ex->getMessage();
    }

    $tipoMensaje = "danger";

} catch (Exception $ex) {

    $mensaje = $ex->getMessage();
    $tipoMensaje = "warning";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Docentes | ViajesDocentes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body {
            background: #f3f6fa;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: #183650;
        }

        .sidebar {
            background: white;
            min-height: 100vh;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            color: #43566b;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .sidebar a:hover,
        .sidebar .activo {
            background: #e8f0f8;
            color: #1c4c75;
        }

        .contenido {
            padding: 28px;
        }

        .card {
            border: 0;
            border-radius: 13px;
        }

        .table {
            vertical-align: middle;
        }

    </style>

</head>

<body>

    <!-- BARRA SUPERIOR -->

    <nav class="navbar navbar-dark p-3">

        <div class="container-fluid">

            <span class="navbar-brand fw-bold">
                🚌 ViajesDocentes
            </span>

            <span class="text-white">
                Sistema de coordinación de viajes
            </span>

        </div>

    </nav>


    <div class="container-fluid">

        <div class="row">

            <!-- MENÚ LATERAL CORREGIDO -->

            <aside class="col-md-3 col-lg-2 sidebar p-3">

                <h6 class="text-secondary mb-3">
                    MENÚ PRINCIPAL
                </h6>

                <a href="index.php">
                    🏠 Inicio
                </a>

                <a href="nuevo_viaje.php">
                    🚌 Programar viaje
                </a>

                <a href="viajes.php">
                    📋 Historial de viajes
                </a>

                <a href="docentes.php" class="activo">
                    👨‍🏫 Docentes
                </a>

                <a href="destino.php">
                    📍 Destinos
                </a>

                <a href="transporte.php">
                    🚐 Transportes
                </a>

            </aside>


            <!-- CONTENIDO PRINCIPAL -->

            <main class="col-md-9 col-lg-10 contenido">

                <h1 class="h3 fw-bold">
                    Administración de docentes
                </h1>

                <p class="text-secondary">
                    Registro y control de personal docente.
                </p>


                <!-- MENSAJES -->

                <?php if ($mensaje): ?>

                    <div class="alert alert-<?= e($tipoMensaje) ?> alert-dismissible fade show">

                        <?= e($mensaje) ?>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                        </button>

                    </div>

                <?php endif; ?>


                <div class="row g-4">


                    <!-- FORMULARIO -->

                    <div class="col-lg-5">

                        <div class="card shadow-sm">

                            <div class="card-body">

                                <h5 class="fw-bold mb-4">

                                    <?= $editar ? "✏️ Editar docente" : "👨‍🏫 Registrar docente" ?>

                                </h5>


                                <form method="POST" action="docentes.php">

                                    <input
                                        type="hidden"
                                        name="accion"
                                        value="<?= $editar ? "actualizar" : "guardar" ?>"
                                    >

                                    <?php if ($editar): ?>

                                        <input
                                            type="hidden"
                                            name="id_docente"
                                            value="<?= (int)$editar["id_docente"] ?>"
                                        >

                                    <?php endif; ?>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Nombre completo
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="nombre_completo"
                                            required
                                            value="<?= e($editar["nombre_completo"] ?? "") ?>"
                                        >

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Número de empleado
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="numero_empleado"
                                            required
                                            value="<?= e($editar["numero_empleado"] ?? "") ?>"
                                        >

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Correo electrónico
                                        </label>

                                        <input
                                            type="email"
                                            class="form-control"
                                            name="correo"
                                            required
                                            value="<?= e($editar["correo"] ?? "") ?>"
                                        >

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Teléfono
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="telefono"
                                            required
                                            value="<?= e($editar["telefono"] ?? "") ?>"
                                        >

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">

                                            Contraseña

                                            <?php if ($editar): ?>

                                                <small class="text-secondary">
                                                    (Dejar vacío para no cambiar)
                                                </small>

                                            <?php endif; ?>

                                        </label>

                                        <input
                                            type="password"
                                            class="form-control"
                                            name="contrasena"
                                            <?= $editar ? "" : "required" ?>
                                        >

                                    </div>


                                    <div class="d-flex gap-2">

                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >

                                            <?= $editar ? "Guardar cambios" : "Registrar docente" ?>

                                        </button>


                                        <?php if ($editar): ?>

                                            <a
                                                href="docentes.php"
                                                class="btn btn-secondary"
                                            >
                                                Cancelar
                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>



                    <!-- TABLA DE DOCENTES -->

                    <div class="col-lg-7">

                        <div class="card shadow-sm">

                            <div class="card-body">

                                <h5 class="fw-bold mb-3">
                                    📋 Docentes registrados
                                </h5>


                                <div class="table-responsive">

                                    <table class="table table-hover">

                                        <thead class="table-light">

                                            <tr>

                                                <th>Empleado</th>

                                                <th>Nombre</th>

                                                <th>Correo</th>

                                                <th>Teléfono</th>

                                                <th>Acciones</th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php foreach ($docentes as $d): ?>

                                                <tr>

                                                    <td>
                                                        <?= e($d["numero_empleado"]) ?>
                                                    </td>

                                                    <td>
                                                        <?= e($d["nombre_completo"]) ?>
                                                    </td>

                                                    <td>
                                                        <?= e($d["correo"]) ?>
                                                    </td>

                                                    <td>
                                                        <?= e($d["telefono"]) ?>
                                                    </td>

                                                    <td>

                                                        <div class="d-flex gap-1">

                                                            <a
                                                                class="btn btn-sm btn-primary"
                                                                href="docentes.php?editar=<?= (int)$d["id_docente"] ?>"
                                                            >
                                                                Editar
                                                            </a>


                                                            <form
                                                                method="POST"
                                                                action="docentes.php"
                                                                onsubmit="return confirm('¿Eliminar este docente?')"
                                                            >

                                                                <input
                                                                    type="hidden"
                                                                    name="accion"
                                                                    value="eliminar"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="id_docente"
                                                                    value="<?= (int)$d["id_docente"] ?>"
                                                                >

                                                                <button
                                                                    type="submit"
                                                                    class="btn btn-sm btn-danger"
                                                                >
                                                                    Eliminar
                                                                </button>

                                                            </form>

                                                        </div>

                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>


                                            <?php if (empty($docentes)): ?>

                                                <tr>

                                                    <td
                                                        colspan="5"
                                                        class="text-center text-secondary"
                                                    >
                                                        No hay docentes registrados.
                                                    </td>

                                                </tr>

                                            <?php endif; ?>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PIE DE PÁGINA -->

                <footer class="text-center text-secondary mt-4">

                    <p>
                        ViajesDocentes © 2026 |
                        Sistema de coordinación de viajes de ida y vuelta para docentes.
                    </p>

                </footer>

            </main>

        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
```
