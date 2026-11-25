<?php
require_once "config/conexion.php";

function e($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $docente = (int)$_POST["id_docente"];
        $destino = (int)$_POST["id_destino"];
        $transporte = (int)$_POST["id_transporte"];

        $salida = $_POST["fecha_salida"];
        $regreso = $_POST["fecha_regreso"];

        $lugar = trim($_POST["lugar_salida"]);
        $estado = $_POST["estado"] ?? "Programado";

        if (
            !$docente || !$destino || !$transporte ||
            !$salida || !$regreso || $lugar === ""
        ) {
            throw new Exception("Completa todos los campos.");
        }

        if (strtotime($regreso) <= strtotime($salida)) {
            throw new Exception("El regreso debe ser posterior a la salida.");
        }

        $estados = [
            "Programado", "En curso", "Completado", "Cancelado"
        ];

        if (!in_array($estado, $estados, true)) {
            $estado = "Programado";
        }

        $sql = "INSERT INTO viajes
        (id_docente, id_destino, id_transporte,
        fecha_salida, fecha_regreso, lugar_salida, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $pdo->prepare($sql)->execute([
            $docente,
            $destino,
            $transporte,
            str_replace("T", " ", $salida) . ":00",
            str_replace("T", " ", $regreso) . ":00",
            $lugar,
            $estado
        ]);

        $mensaje = "¡Viaje programado correctamente!";

    } catch (PDOException $ex) {
        $mensaje = "Error al registrar el viaje. Verifica los datos.";
    } catch (Exception $ex) {
        $mensaje = $ex->getMessage();
    }
}

$docentes = $pdo->query(
    "SELECT id_docente, nombre_completo
    FROM docentes ORDER BY nombre_completo"
)->fetchAll(PDO::FETCH_ASSOC);

$destinos = $pdo->query(
    "SELECT id_destino, nombre_destino, origen
    FROM destinos ORDER BY nombre_destino"
)->fetchAll(PDO::FETCH_ASSOC);

$transportes = $pdo->query(
    "SELECT id_transporte, tipo_transporte,
    numero_unidad, capacidad, conductor
    FROM transportes ORDER BY numero_unidad"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Programar viaje | ViajesDocentes</title>

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
<a href="nuevo_viaje.php" class="activo">Programar viaje</a>
<a href="viajes.php">Historial de viajes</a>
<a href="docentes.php">Docentes</a>
<a href="destinos.php">Destinos</a>
<a href="transportes.php">Transportes</a>
</aside>

<main class="col-md-9 col-lg-10 contenido">

<h1 class="h3">Programar un viaje</h1>
<p class="text-secondary">
Registro de recorridos de ida y vuelta para docentes.
</p>

<?php if ($mensaje): ?>
<div class="alert alert-info"><?= e($mensaje) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
<div class="card-body p-4">

<h5 class="mb-4">Datos del recorrido</h5>

<?php if (!$docentes || !$destinos || !$transportes): ?>
<div class="alert alert-warning">
Primero debes registrar docentes, destinos y transportes.
</div>
<?php endif; ?>

<form method="POST" class="row g-3">

<div class="col-md-6">
<label class="form-label">Docente responsable</label>
<select class="form-select" name="id_docente" required>
<option value="">Selecciona un docente</option>

<?php foreach ($docentes as $d): ?>
<option value="<?= (int)$d["id_docente"] ?>">
<?= e($d["nombre_completo"]) ?>
</option>
<?php endforeach; ?>

</select>
</div>

<div class="col-md-6">
<label class="form-label">Destino</label>
<select class="form-select" name="id_destino" required>
<option value="">Selecciona un destino</option>

<?php foreach ($destinos as $d): ?>
<option value="<?= (int)$d["id_destino"] ?>">
<?= e($d["nombre_destino"]) ?>
(<?= e($d["origen"]) ?>)
</option>
<?php endforeach; ?>

</select>
</div>

<div class="col-md-6">
<label class="form-label">Transporte</label>
<select class="form-select" name="id_transporte" required>
<option value="">Selecciona una unidad</option>

<?php foreach ($transportes as $t): ?>
<option value="<?= (int)$t["id_transporte"] ?>">
<?= e($t["tipo_transporte"]) ?>
- Unidad <?= e($t["numero_unidad"]) ?>
(<?= (int)$t["capacidad"] ?> lugares)
</option>
<?php endforeach; ?>

</select>
</div>

<div class="col-md-6">
<label class="form-label">Conductor</label>
<input class="form-control" value="Se muestra según el transporte seleccionado" disabled>
</div>

<div class="col-md-6">
<label class="form-label">Lugar de salida</label>
<input class="form-control" name="lugar_salida"
placeholder="Ej. CECyTE Plantel Pomuch" required>
</div>

<div class="col-md-6">
<label class="form-label">Estado del viaje</label>
<select class="form-select" name="estado">
<option>Programado</option>
<option>En curso</option>
<option>Completado</option>
<option>Cancelado</option>
</select>
</div>

<div class="col-md-6">
<label class="form-label">Fecha y hora de salida</label>
<input type="datetime-local" class="form-control"
name="fecha_salida" required>
</div>

<div class="col-md-6">
<label class="form-label">Fecha y hora de regreso</label>
<input type="datetime-local" class="form-control"
name="fecha_regreso" required>
</div>

<div class="col-12">
<button type="submit" class="btn btn-primary"
<?= (!$docentes || !$destinos || !$transportes) ? "disabled" : "" ?>>
Guardar viaje
</button>

<a href="viajes.php" class="btn btn-secondary">
Ver historial
</a>
</div>

</form>
</div>
</div>

</main>
</div>
</div>

</body>
</html>
