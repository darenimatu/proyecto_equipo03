<?php
require_once "config/conexion.php";

function e($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

function fechaHora($fecha) {
    return date("d/m/Y H:i", strtotime($fecha));
}

$mensaje = "";

// ACTUALIZAR ESTADO, EDITAR O ELIMINAR
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $accion = $_POST["accion"];

        if ($accion === "estado") {

            $id = (int)$_POST["id_viaje"];
            $estado = $_POST["estado"];

            $pdo->prepare(
                "UPDATE viajes SET estado=? WHERE id_viaje=?"
            )->execute([$estado, $id]);

            $mensaje = "Estado actualizado correctamente.";

        } elseif ($accion === "actualizar") {

            $id = (int)$_POST["id_viaje"];
            $docente = (int)$_POST["id_docente"];
            $destino = (int)$_POST["id_destino"];
            $transporte = (int)$_POST["id_transporte"];

            $salida = str_replace("T", " ", $_POST["fecha_salida"]);
            $regreso = str_replace("T", " ", $_POST["fecha_regreso"]);

            $lugar = trim($_POST["lugar_salida"]);
            $estado = $_POST["estado"];

            if (strtotime($regreso) <= strtotime($salida)) {
                throw new Exception("La fecha de regreso debe ser posterior a la salida.");
            }

            $sql = "UPDATE viajes SET
            id_docente=?, id_destino=?, id_transporte=?,
            fecha_salida=?, fecha_regreso=?, lugar_salida=?, estado=?
            WHERE id_viaje=?";

            $pdo->prepare($sql)->execute([
                $docente,
                $destino,
                $transporte,
                $salida,
                $regreso,
                $lugar,
                $estado,
                $id
            ]);

            $mensaje = "Viaje actualizado correctamente.";

        } elseif ($accion === "eliminar") {

            $id = (int)$_POST["id_viaje"];

            $pdo->prepare(
                "DELETE FROM viajes WHERE id_viaje=?"
            )->execute([$id]);

            $mensaje = "Viaje eliminado correctamente.";
        }

    } catch (Exception $ex) {
        $mensaje = $ex->getMessage();
    }
}

// CONSULTAR VIAJES
$sql = "SELECT
    v.id_viaje,
    d.nombre_completo AS docente,
    dest.nombre_destino AS destino,
    dest.origen,
    v.lugar_salida,
    v.fecha_salida,
    v.fecha_regreso,
    t.tipo_transporte,
    t.numero_unidad,
    t.conductor,
    v.estado

FROM viajes v

INNER JOIN docentes d
ON v.id_docente = d.id_docente

INNER JOIN destinos dest
ON v.id_destino = dest.id_destino

INNER JOIN transportes t
ON v.id_transporte = t.id_transporte

ORDER BY v.fecha_salida DESC";

$viajes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// CONSULTAR DATOS PARA EDITAR
$editar = null;

if (isset($_GET["editar"])) {
    $st = $pdo->prepare(
        "SELECT * FROM viajes WHERE id_viaje=?"
    );

    $st->execute([(int)$_GET["editar"]]);
    $editar = $st->fetch(PDO::FETCH_ASSOC);
}

// LISTAS PARA EL FORMULARIO
$docentes = $pdo->query(
    "SELECT * FROM docentes ORDER BY nombre_completo"
)->fetchAll(PDO::FETCH_ASSOC);

$destinos = $pdo->query(
    "SELECT * FROM destinos ORDER BY nombre_destino"
)->fetchAll(PDO::FETCH_ASSOC);

$transportes = $pdo->query(
    "SELECT * FROM transportes ORDER BY numero_unidad"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial de viajes | ViajesDocentes</title>

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
<a href="viajes.php" class="activo">Historial de viajes</a>
<a href="docentes.php">Docentes</a>
<a href="destinos.php">Destinos</a>
<a href="transportes.php">Transportes</a>
</aside>

<main class="col-md-9 col-lg-10 contenido">

<h1 class="h3">Historial de viajes</h1>
<p class="text-secondary">
Consulta y administra los viajes registrados.
</p>

<?php if ($mensaje): ?>
<div class="alert alert-info"><?= e($mensaje) ?></div>
<?php endif; ?>

<?php if ($editar): ?>

<div class="card shadow-sm mb-4">
<div class="card-body">

<h5>Editar viaje</h5>

<form method="POST" class="row g-3">

<input type="hidden" name="accion" value="actualizar">
<input type="hidden" name="id_viaje"
value="<?= (int)$editar["id_viaje"] ?>">

<div class="col-md-6">
<label class="form-label">Docente</label>
<select class="form-select" name="id_docente" required>

<?php foreach ($docentes as $d): ?>
<option value="<?= (int)$d["id_docente"] ?>"
<?= $editar["id_docente"] == $d["id_docente"] ? "selected" : "" ?>>
<?= e($d["nombre_completo"]) ?>
</option>
<?php endforeach; ?>

</select>
</div>

<div class="col-md-6">
<label class="form-label">Destino</label>
<select class="form-select" name="id_destino" required>

<?php foreach ($destinos as $d): ?>
<option value="<?= (int)$d["id_destino"] ?>"
<?= $editar["id_destino"] == $d["id_destino"] ? "selected" : "" ?>>
<?= e($d["nombre_destino"]) ?>
</option>
<?php endforeach; ?>

</select>
</div>

<div class="col-md-6">
<label class="form-label">Transporte</label>
<select class="form-select" name="id_transporte" required>

<?php foreach ($transportes as $t): ?>
<option value="<?= (int)$t["id_transporte"] ?>"
<?= $editar["id_transporte"] == $t["id_transporte"] ? "selected" : "" ?>>
<?= e($t["tipo_transporte"]) ?> -
<?= e($t["numero_unidad"]) ?>
</option>
<?php endforeach; ?>

</select>
</div>

<div class="col-md-6">
<label class="form-label">Lugar de salida</label>
<input class="form-control" name="lugar_salida"
required value="<?= e($editar["lugar_salida"]) ?>">
</div>

<div class="col-md-6">
<label class="form-label">Fecha de salida</label>
<input type="datetime-local" class="form-control"
name="fecha_salida" required
value="<?= date("Y-m-d\TH:i", strtotime($editar["fecha_salida"])) ?>">
</div>

<div class="col-md-6">
<label class="form-label">Fecha de regreso</label>
<input type="datetime-local" class="form-control"
name="fecha_regreso" required
value="<?= date("Y-m-d\TH:i", strtotime($editar["fecha_regreso"])) ?>">
</div>

<div class="col-md-6">
<label class="form-label">Estado</label>
<select class="form-select" name="estado">

<?php foreach (["Programado","En curso","Completado","Cancelado"] as $op): ?>
<option <?= $editar["estado"] === $op ? "selected" : "" ?>>
<?= $op ?>
</option>
<?php endforeach; ?>

</select>
</div>

<div class="col-12">
<button class="btn btn-primary">Guardar cambios</button>
<a href="viajes.php" class="btn btn-secondary">Cancelar</a>
</div>

</form>
</div>
</div>

<?php endif; ?>

<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">
<h5>Viajes registrados</h5>
<a href="nuevo_viaje.php" class="btn btn-primary">
+ Programar viaje
</a>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">

<thead>
<tr>
<th>Docente</th>
<th>Ruta</th>
<th>Salida</th>
<th>Regreso</th>
<th>Transporte</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach ($viajes as $v): ?>

<?php
$colores = [
    "Programado" => "primary",
    "En curso" => "warning",
    "Completado" => "success",
    "Cancelado" => "danger"
];

$color = $colores[$v["estado"]] ?? "secondary";
?>

<tr>

<td><?= e($v["docente"]) ?></td>

<td>
<strong><?= e($v["lugar_salida"]) ?></strong>
<br>
<small>→ <?= e($v["destino"]) ?></small>
</td>

<td><?= fechaHora($v["fecha_salida"]) ?></td>

<td><?= fechaHora($v["fecha_regreso"]) ?></td>

<td>
<?= e($v["tipo_transporte"]) ?>
<br>
<small><?= e($v["numero_unidad"]) ?></small>
</td>

<td>
<span class="badge text-bg-<?= $color ?>">
<?= e($v["estado"]) ?>
</span>
</td>

<td>

<a class="btn btn-sm btn-primary mb-1"
href="viajes.php?editar=<?= (int)$v["id_viaje"] ?>">
Editar
</a>

<form method="POST" class="mb-1">
<input type="hidden" name="accion" value="estado">
<input type="hidden" name="id_viaje"
value="<?= (int)$v["id_viaje"] ?>">

<select class="form-select form-select-sm"
name="estado">

<?php foreach (["Programado","En curso","Completado","Cancelado"] as $op): ?>
<option <?= $v["estado"] === $op ? "selected" : "" ?>>
<?= $op ?>
</option>
<?php endforeach; ?>

</select>

<button class="btn btn-sm btn-outline-primary mt-1">
Cambiar estado
</button>
</form>

<form method="POST"
onsubmit="return confirm('¿Eliminar este viaje?')">

<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id_viaje"
value="<?= (int)$v["id_viaje"] ?>">

<button class="btn btn-sm btn-danger">Eliminar</button>
</form>

</td>
</tr>

<?php endforeach; ?>

<?php if (!$viajes): ?>
<tr>
<td colspan="7" class="text-center">
No hay viajes registrados.
</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

</div>
</div>

</main>
</div>
</div>

</body>
</html>
