<?php
require_once "config/conexion.php";

function e($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

// CONTAR REGISTROS
$totalDocentes = $pdo->query(
    "SELECT COUNT(*) FROM docentes"
)->fetchColumn();

$totalDestinos = $pdo->query(
    "SELECT COUNT(*) FROM destinos"
)->fetchColumn();

$totalTransportes = $pdo->query(
    "SELECT COUNT(*) FROM transportes"
)->fetchColumn();

$totalViajes = $pdo->query(
    "SELECT COUNT(*) FROM viajes"
)->fetchColumn();

// CONTAR VIAJES PROGRAMADOS
$programados = $pdo->query(
    "SELECT COUNT(*) FROM viajes WHERE estado='Programado'"
)->fetchColumn();

// CONSULTAR LOS ÚLTIMOS VIAJES
$sql = "SELECT
    v.id_viaje,
    d.nombre_completo AS docente,
    dest.nombre_destino AS destino,
    v.fecha_salida,
    v.fecha_regreso,
    v.estado

FROM viajes v

INNER JOIN docentes d
ON v.id_docente = d.id_docente

INNER JOIN destinos dest
ON v.id_destino = dest.id_destino

ORDER BY v.fecha_salida DESC
LIMIT 5";

$ultimosViajes = $pdo->query($sql)->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Inicio | ViajesDocentes</title>

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
    padding: 13px;
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
    padding: 30px;
}

.card {
    border: none;
    border-radius: 13px;
}

.estadistica {
    font-size: 30px;
    font-weight: bold;
    color: #183650;
}

.subtitulo {
    color: #748397;
    font-size: 14px;
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

<a href="index.php" class="activo">
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

<a href="destinos.php">
📍 Destinos
</a>

<a href="transportes.php">
🚐 Transportes
</a>

</aside>


<!-- CONTENIDO PRINCIPAL -->

<main class="col-md-9 col-lg-10 contenido">

<h2 class="fw-bold">
Bienvenido a ViajesDocentes
</h2>

<p class="text-secondary">
Administra y consulta los viajes de ida y vuelta del personal docente.
</p>


<!-- TARJETAS DE ESTADÍSTICAS -->

<div class="row g-4 mt-2">


<div class="col-md-6 col-xl-3">

<div class="card shadow-sm p-4">

<p class="subtitulo">Docentes registrados</p>

<div class="estadistica">
<?= (int)$totalDocentes ?>
</div>

<span class="text-secondary">
Personal docente
</span>

</div>

</div>


<div class="col-md-6 col-xl-3">

<div class="card shadow-sm p-4">

<p class="subtitulo">Destinos disponibles</p>

<div class="estadistica">
<?= (int)$totalDestinos ?>
</div>

<span class="text-secondary">
Lugares registrados
</span>

</div>

</div>


<div class="col-md-6 col-xl-3">

<div class="card shadow-sm p-4">

<p class="subtitulo">Transportes</p>

<div class="estadistica">
<?= (int)$totalTransportes ?>
</div>

<span class="text-secondary">
Unidades registradas
</span>

</div>

</div>


<div class="col-md-6 col-xl-3">

<div class="card shadow-sm p-4">

<p class="subtitulo">Viajes registrados</p>

<div class="estadistica">
<?= (int)$totalViajes ?>
</div>

<span class="text-secondary">
Total de recorridos
</span>

</div>

</div>

</div>


<!-- VIAJES PROGRAMADOS -->

<div class="card shadow-sm p-4 mt-4">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

<div>

<h5 class="fw-bold">
Viajes programados
</h5>

<p class="text-secondary mb-0">
Actualmente tienes <?= (int)$programados ?> viajes pendientes de realizar.
</p>

</div>

<a href="nuevo_viaje.php" class="btn btn-primary">
+ Programar viaje
</a>

</div>

</div>


<!-- TABLA DE ÚLTIMOS VIAJES -->

<div class="card shadow-sm p-4 mt-4">

<h5 class="fw-bold mb-3">
Últimos viajes registrados
</h5>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>
<th>Docente</th>
<th>Destino</th>
<th>Fecha de salida</th>
<th>Fecha de regreso</th>
<th>Estado</th>
</tr>

</thead>

<tbody>

<?php foreach ($ultimosViajes as $v): ?>

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

<td>
<?= e($v["docente"]) ?>
</td>

<td>
<?= e($v["destino"]) ?>
</td>

<td>
<?= date("d/m/Y H:i", strtotime($v["fecha_salida"])) ?>
</td>

<td>
<?= date("d/m/Y H:i", strtotime($v["fecha_regreso"])) ?>
</td>

<td>

<span class="badge text-bg-<?= $color ?>">
<?= e($v["estado"]) ?>
</span>

</td>

</tr>

<?php endforeach; ?>


<?php if (!$ultimosViajes): ?>

<tr>

<td colspan="5" class="text-center text-secondary">
Todavía no hay viajes registrados.
</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

<a href="viajes.php" class="btn btn-outline-primary btn-sm align-self-start">
Ver todos los viajes
</a>

</div>


<!-- PIE DE PÁGINA -->

<footer class="text-center text-secondary mt-4">

<p>
ViajesDocentes © 2026 | Sistema de coordinación de viajes de ida y vuelta para docentes.
</p>

</footer>

</main>

</div>

</div>

</body>
</html>
