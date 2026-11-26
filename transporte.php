```php
<?php
require_once "config/conexion.php";

function e($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$mensaje = "";
$tipoMensaje = "info";
$editar = null;
$transportes = [];

try {

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("La conexión PDO no está disponible.");
    }

    // REGISTRAR, ACTUALIZAR Y ELIMINAR
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $accion = $_POST["accion"] ?? "";

        if ($accion === "guardar" || $accion === "actualizar") {

            $tipo = trim($_POST["tipo_transporte"] ?? "");
            $numero = trim($_POST["numero_unidad"] ?? "");
            $capacidad = (int)($_POST["capacidad"] ?? 0);
            $conductor = trim($_POST["conductor"] ?? "");

            if ($tipo === "" || $numero === "" || $capacidad <= 0 || $conductor === "") {
                throw new Exception("Completa todos los campos correctamente.");
            }

            if ($accion === "guardar") {

                $sql = "INSERT INTO transportes
                (tipo_transporte, numero_unidad, capacidad, conductor)
                VALUES (?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $tipo,
                    $numero,
                    $capacidad,
                    $conductor
                ]);

                $mensaje = "¡Transporte registrado correctamente!";
                $tipoMensaje = "success";

            } else {

                $id = (int)($_POST["id_transporte"] ?? 0);

                if ($id <= 0) {
                    throw new Exception("Identificador de transporte inválido.");
                }

                $sql = "UPDATE transportes SET
                tipo_transporte = ?,
                numero_unidad = ?,
                capacidad = ?,
                conductor = ?
                WHERE id_transporte = ?";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $tipo,
                    $numero,
                    $capacidad,
                    $conductor,
                    $id
                ]);

                $mensaje = "¡Transporte actualizado correctamente!";
                $tipoMensaje = "success";
            }

        } elseif ($accion === "eliminar") {

            $id = (int)($_POST["id_transporte"] ?? 0);

            if ($id <= 0) {
                throw new Exception("Identificador inválido.");
            }

            $stmt = $pdo->prepare(
                "DELETE FROM transportes WHERE id_transporte = ?"
            );

            $stmt->execute([$id]);

            $mensaje = "Transporte eliminado correctamente.";
            $tipoMensaje = "success";
        }
    }

    // CONSULTAR PARA EDITAR
    if (isset($_GET["editar"])) {

        $id = (int)$_GET["editar"];

        $stmt = $pdo->prepare(
            "SELECT * FROM transportes WHERE id_transporte = ?"
        );

        $stmt->execute([$id]);

        $editar = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$editar) {
            $mensaje = "El transporte solicitado no existe.";
            $tipoMensaje = "warning";
        }
    }

    // MOSTRAR TRANSPORTES
    $transportes = $pdo->query(
        "SELECT * FROM transportes ORDER BY id_transporte DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {

    if ($ex->getCode() == "23000") {
        $mensaje = "El número de unidad ya está registrado o el transporte está relacionado con un viaje.";
    } else {
        $mensaje = "Error de base de datos: " . $ex->getMessage();
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

    <title>Transportes | ViajesDocentes</title>

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

            <!-- MENÚ LATERAL -->

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

                <a href="docentes.php">
                    👨‍🏫 Docentes
                </a>

                <a href="destino.php">
                    📍 Destinos
                </a>

                <a href="transporte.php" class="activo">
                    🚐 Transportes
                </a>

            </aside>


            <!-- CONTENIDO PRINCIPAL -->

            <main class="col-md-9 col-lg-10 contenido">

                <h1 class="h3 fw-bold">
                    Administración de transportes
                </h1>

                <p class="text-secondary">
                    Registro y administración de unidades de transporte disponibles.
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


                    <!-- FORMULARIO DE REGISTRO -->

                    <div class="col-lg-5">

                        <div class="card shadow-sm">

                            <div class="card-body">

                                <h5 class="fw-bold mb-4">

                                    <?= $editar ? "✏️ Editar transporte" : "🚐 Registrar transporte" ?>

                                </h5>


                                <form method="POST" action="transporte.php">

                                    <input
                                        type="hidden"
                                        name="accion"
                                        value="<?= $editar ? "actualizar" : "guardar" ?>"
                                    >

                                    <?php if ($editar): ?>

                                        <input
                                            type="hidden"
                                            name="id_transporte"
                                            value="<?= (int)$editar["id_transporte"] ?>"
                                        >

                                    <?php endif; ?>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Tipo de transporte
                                        </label>

                                        <select
                                            name="tipo_transporte"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">
                                                Selecciona el transporte
                                            </option>

                                            <?php
                                            $tipos = [
                                                "Autobús",
                                                "Van",
                                                "Camioneta",
                                                "Automóvil",
                                                "Microbús"
                                            ];
                                            ?>

                                            <?php foreach ($tipos as $tipo): ?>

                                                <option
                                                    value="<?= e($tipo) ?>"
                                                    <?= (($editar["tipo_transporte"] ?? "") === $tipo) ? "selected" : "" ?>
                                                >
                                                    <?= e($tipo) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Número de unidad
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="numero_unidad"
                                            placeholder="Ej. BUS-001"
                                            maxlength="20"
                                            required
                                            value="<?= e($editar["numero_unidad"] ?? "") ?>"
                                        >

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Capacidad de pasajeros
                                        </label>

                                        <input
                                            type="number"
                                            class="form-control"
                                            name="capacidad"
                                            placeholder="Ej. 40"
                                            min="1"
                                            required
                                            value="<?= e($editar["capacidad"] ?? "") ?>"
                                        >

                                    </div>


                                    <div class="mb-3">

                                        <label class="form-label">
                                            Nombre del conductor
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="conductor"
                                            placeholder="Nombre completo"
                                            maxlength="100"
                                            required
                                            value="<?= e($editar["conductor"] ?? "") ?>"
                                        >

                                    </div>


                                    <div class="d-flex gap-2">

                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >

                                            <?= $editar ? "Guardar cambios" : "Registrar transporte" ?>

                                        </button>


                                        <?php if ($editar): ?>

                                            <a
                                                href="transporte.php"
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



                    <!-- TABLA DE TRANSPORTES -->

                    <div class="col-lg-7">

                        <div class="card shadow-sm">

                            <div class="card-body">

                                <h5 class="fw-bold mb-3">
                                    🚐 Transportes registrados
                                </h5>


                                <div class="table-responsive">

                                    <table class="table table-hover">

                                        <thead class="table-light">

                                            <tr>

                                                <th>Tipo</th>
                                                <th>Unidad</th>
                                                <th>Capacidad</th>
                                                <th>Conductor</th>
                                                <th>Acciones</th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php foreach ($transportes as $trans): ?>

                                                <tr>

                                                    <td>
                                                        <?= e($trans["tipo_transporte"]) ?>
                                                    </td>

                                                    <td>
                                                        <strong>
                                                            <?= e($trans["numero_unidad"]) ?>
                                                        </strong>
                                                    </td>

                                                    <td>
                                                        <?= e($trans["capacidad"]) ?> pasajeros
                                                    </td>

                                                    <td>
                                                        <?= e($trans["conductor"]) ?>
                                                    </td>

                                                    <td>

                                                        <div class="d-flex gap-1">

                                                            <a
                                                                class="btn btn-sm btn-primary"
                                                                href="transporte.php?editar=<?= (int)$trans["id_transporte"] ?>"
                                                            >
                                                                Editar
                                                            </a>


                                                            <form
                                                                method="POST"
                                                                action="transporte.php"
                                                                onsubmit="return confirm('¿Eliminar este transporte?')"
                                                            >

                                                                <input
                                                                    type="hidden"
                                                                    name="accion"
                                                                    value="eliminar"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="id_transporte"
                                                                    value="<?= (int)$trans["id_transporte"] ?>"
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


                                            <?php if (empty($transportes)): ?>

                                                <tr>

                                                    <td colspan="5" class="text-center text-secondary">
                                                        No hay transportes registrados.
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
