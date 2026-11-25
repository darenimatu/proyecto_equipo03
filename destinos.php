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

            $nombre = trim($_POST["nombre_destino"]);
            $origen = trim($_POST["origen"]);
            $direccion = trim($_POST["direccion"]);
            $descripcion = trim($_POST["descripcion"]);

            if ($accion === "guardar") {

                $sql = "INSERT INTO destinos
                (nombre_destino, origen, direccion, descripcion)
                VALUES (?, ?, ?, ?)";

                $pdo->prepare($sql)->execute([
                    $nombre, $origen, $direccion, $descripcion
                ]);

                $mensaje = "Destino registrado correctamente.";

            } else {

                $id = (int)$_POST["id_destino"];

                $sql = "UPDATE destinos SET
                nombre_destino=?, origen=?, direccion=?, descripcion=?
                WHERE id_destino=?";

                $pdo->prepare($sql)->execute([
                    $nombre, $origen, $direccion, $descripcion, $id
                ]);

                $mensaje = "Destino actualizado.";
            }

        } elseif ($accion === "eliminar") {

            $pdo->prepare(
                "DELETE FROM destinos WHERE id_destino=?"
            )->execute([(int)$_POST["id_destino"]]);

            $mensaje = "Destino eliminado.";
        }

    } catch (PDOException $ex) {
        $mensaje = "No se pudo realizar la operación. Verifica si el destino tiene viajes asociados.";
    }
}

$editar = null;

if (isset($_GET["editar"])) {
    $st = $pdo->prepare(
        "SELECT * FROM destinos WHERE id_destino=?"
    );
    $st->execute([(int)$_GET["editar"]]);
    $editar = $st->fetch(PDO::FETCH_ASSOC);
}

$destinos = $pdo->query(
    "SELECT * FROM destinos ORDER BY id_destino DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Destinos | ViajesDocentes</title>
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
<a href="destinos.php" class="activo">Destinos</a>
<a href="transportes.php">Transportes</a>
</aside>

<main class="col-md-9 col-lg-10 contenido">

<h1 class="h3">Administración de destinos</h1>
<p class="text-secondary">Registro y consulta de destinos.</p>

<?php if ($mensaje): ?>
<div class="alert alert-info"><?= e($mensaje) ?></div>
<?php endif; ?>

<div class="row g-4">

<div class="col-lg-5">
<div class="card shadow-sm">
<div class="card-body">

<h5><?= $editar ? "Editar destino" : "Registrar destino" ?></h5>

<form method="POST">

<input type="hidden" name="accion"
value="<?= $editar ? "actualizar" : "guardar" ?>">

<?php if ($editar): ?>
<input type="hidden" name="id_destino"
value="<?= (int)$editar["id_destino"] ?>">
<?php endif; ?>

<div class="mb-3">
<label class="form-label">Nombre del destino</label>
<input class="form-control" name="nombre_destino"
required value="<?= e($editar["nombre_destino"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Origen</label>
<input class="form-control" name="origen"
required value="<?= e($editar["origen"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Dirección o punto de encuentro</label>
<input class="form-control" name="direccion"
required value="<?= e($editar["direccion"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Descripción</label>
<textarea class="form-control" name="descripcion"
rows="3"><?= e($editar["descripcion"] ?? "") ?></textarea>
</div>

<button class="btn btn-primary">
<?= $editar ? "Guardar cambios" : "Registrar destino" ?>
</button>

<?php if ($editar): ?>
<a href="destinos.php" class="btn btn-secondary">Cancelar</a>
<?php endif; ?>

</form>
</div>
</div>
</div>

<div class="col-lg-7">
<div class="card shadow-sm">
<div class="card-body">

<h5>Destinos registrados</h5>

<div class="table-responsive">
<table class="table table-hover">

<thead>
<tr>
<th>Destino</th>
<th>Origen</th>
<th>Dirección</th>
<th>Descripción</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach ($destinos as $d): ?>
<tr>
<td><?= e($d["nombre_destino"]) ?></td>
<td><?= e($d["origen"]) ?></td>
<td><?= e($d["direccion"]) ?></td>
<td><?= e($d["descripcion"]) ?></td>

<td>
<a class="btn btn-sm btn-primary"
href="destinos.php?editar=<?= (int)$d["id_destino"] ?>">
Editar
</a>

<form method="POST" class="d-inline"
onsubmit="return confirm('¿Eliminar destino?')">

<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id_destino"
value="<?= (int)$d["id_destino"] ?>">

<button class="btn btn-sm btn-danger">Eliminar</button>
</form>
</td>
</tr>
<?php endforeach; ?>

<?php if (!$destinos): ?>
<tr>
<td colspan="5" class="text-center">No hay destinos registrados.</td>
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
