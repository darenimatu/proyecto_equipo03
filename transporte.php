<?php
require_once "config/conexion.php";

function e($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    try {
        if ($accion === "guardar" || $accion === "actualizar") {

            $tipo = trim($_POST["tipo_transporte"]);
            $unidad = trim($_POST["numero_unidad"]);
            $capacidad = (int)$_POST["capacidad"];
            $conductor = trim($_POST["conductor"]);

            if ($accion === "guardar") {

                $sql = "INSERT INTO transportes
                (tipo_transporte, numero_unidad, capacidad, conductor)
                VALUES (?, ?, ?, ?)";

                $pdo->prepare($sql)->execute([
                    $tipo, $unidad, $capacidad, $conductor
                ]);

                $mensaje = "Transporte registrado correctamente.";

            } else {

                $id = (int)$_POST["id_transporte"];

                $sql = "UPDATE transportes SET
                tipo_transporte=?, numero_unidad=?,
                capacidad=?, conductor=?
                WHERE id_transporte=?";

                $pdo->prepare($sql)->execute([
                    $tipo, $unidad, $capacidad, $conductor, $id
                ]);

                $mensaje = "Transporte actualizado.";
            }

        } elseif ($accion === "eliminar") {

            $pdo->prepare(
                "DELETE FROM transportes WHERE id_transporte=?"
            )->execute([(int)$_POST["id_transporte"]]);

            $mensaje = "Transporte eliminado.";
        }

    } catch (PDOException $ex) {
        $mensaje = "No se pudo realizar la operación. Verifica si la unidad está registrada o asignada a un viaje.";
    }
}

$editar = null;

if (isset($_GET["editar"])) {
    $st = $pdo->prepare(
        "SELECT * FROM transportes WHERE id_transporte=?"
    );
    $st->execute([(int)$_GET["editar"]]);
    $editar = $st->fetch(PDO::FETCH_ASSOC);
}

$transportes = $pdo->query(
    "SELECT * FROM transportes ORDER BY id_transporte DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transportes | ViajesDocentes</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f3f6fa; }
.navbar { background:#183650; }
.sidebar { background:white; min-height:100vh; }
.sidebar a {
    display:block;
    padding:12px;
    color:#43566b;
    text-decoration:none;
}
.sidebar a:hover, .activo {
    background:#e8f0f8;
    color:#1c4c75;
}
.contenido { padding:28px; }
.card { border:0; border-radius:13px; }
</style>
</head>

<body>

<nav class="navbar navbar-dark p-3">
<span class="navbar-brand">🚌 ViajesDocentes</span>
</nav>

<div class="container-fluid">
<div class="row">

<aside class="col-md-3 col-lg-2 sidebar p-3">
<h6>MENÚ</h6>
<a href="index.php">Inicio</a>
<a href="nuevo_viaje.php">Programar viaje</a>
<a href="viajes.php">Historial de viajes</a>
<a href="docentes.php">Docentes</a>
<a href="destinos.php">Destinos</a>
<a href="transportes.php" class="activo">Transportes</a>
</aside>

<main class="col-md-9 col-lg-10 contenido">

<h1 class="h3">Administración de transportes</h1>
<p class="text-secondary">Registro de unidades y conductores.</p>

<?php if ($mensaje): ?>
<div class="alert alert-info"><?= e($mensaje) ?></div>
<?php endif; ?>

<div class="row g-4">

<div class="col-lg-5">
<div class="card shadow-sm">
<div class="card-body">

<h5><?= $editar ? "Editar transporte" : "Registrar transporte" ?></h5>

<form method="POST">

<input type="hidden" name="accion"
value="<?= $editar ? "actualizar" : "guardar" ?>">

<?php if ($editar): ?>
<input type="hidden" name="id_transporte"
value="<?= (int)$editar["id_transporte"] ?>">
<?php endif; ?>

<div class="mb-3">
<label class="form-label">Tipo de transporte</label>
<input class="form-control" name="tipo_transporte"
placeholder="Autobús o camioneta" required
value="<?= e($editar["tipo_transporte"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Número de unidad</label>
<input class="form-control" name="numero_unidad"
required value="<?= e($editar["numero_unidad"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Capacidad de pasajeros</label>
<input type="number" min="1" class="form-control"
name="capacidad" required
value="<?= e($editar["capacidad"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Nombre del conductor</label>
<input class="form-control" name="conductor"
required value="<?= e($editar["conductor"] ?? "") ?>">
</div>

<button class="btn btn-primary">
<?= $editar ? "Guardar cambios" : "Registrar transporte" ?>
</button>

<?php if ($editar): ?>
<a href="transportes.php" class="btn btn-secondary">Cancelar</a>
<?php endif; ?>

</form>
</div>
</div>
</div>

<div class="col-lg-7">
<div class="card shadow-sm">
<div class="card-body">

<h5>Transportes registrados</h5>

<div class="table-responsive">
<table class="table table-hover">

<thead>
<tr>
<th>Tipo</th>
<th>Unidad</th>
<th>Capacidad</th>
<th>Conductor</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach ($transportes as $t): ?>
<tr>

<td><?= e($t["tipo_transporte"]) ?></td>
<td><?= e($t["numero_unidad"]) ?></td>
<td><?= (int)$t["capacidad"] ?></td>
<td><?= e($t["conductor"]) ?></td>

<td>
<a class="btn btn-sm btn-primary"
href="transportes.php?editar=<?= (int)$t["id_transporte"] ?>">
Editar
</a>

<form method="POST" class="d-inline"
onsubmit="return confirm('¿Eliminar transporte?')">

<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id_transporte"
value="<?= (int)$t["id_transporte"] ?>">

<button class="btn btn-sm btn-danger">Eliminar</button>
</form>
</td>

</tr>
<?php endforeach; ?>

<?php if (!$transportes): ?>
<tr>
<td colspan="5" class="text-center">No hay transportes registrados.</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

</div>
</div>
</div>

</div>
</main>
</div>
</div>

</body>
</html>
