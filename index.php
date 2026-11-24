<?php
require_once "../config/conexion.php";

function e($dato) {
    return htmlspecialchars((string)($dato ?? ""), ENT_QUOTES, "UTF-8");
}

try {
    // Totales de las cuatro tablas
    $totales = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM viajes) AS viajes,
            (SELECT COUNT(*) FROM docentes) AS docentes,
            (SELECT COUNT(*) FROM destinos) AS destinos,
            (SELECT COUNT(*) FROM transportes) AS transportes
    ")->fetch(PDO::FETCH_ASSOC);

    // Últimos diez viajes
    $sql = "SELECT v.id_viaje, d.nombre_completo AS docente,
                   dest.nombre_destino AS destino, v.lugar_salida,
                   v.fecha_salida, v.fecha_regreso, t.tipo_transporte,
                   t.numero_unidad, v.estado
            FROM viajes v
            INNER JOIN docentes d ON v.id_docente = d.id_docente
            INNER JOIN destinos dest ON v.id_destino = dest.id_destino
            INNER JOIN transportes t ON v.id_transporte = t.id_transporte
            ORDER BY v.fecha_salida DESC
            LIMIT 10";

    $viajes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $error = false;
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    $error = true;
    $viajes = [];
    $totales = ["viajes" => 0, "docentes" => 0, "destinos" => 0, "transportes" => 0];
}

function estadoClase($estado) {
    $clases = [
        "Programado" => "primary",
        "En curso" => "warning",
        "Completado" => "success",
        "Cancelado" => "danger"
    ];
    return $clases[$estado] ?? "secondary";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ViajesDocentes | Inicio</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6fa; color:#26364a; }
        .barra { background:#25364a; }
        .sidebar { background:white; min-height:calc(100vh - 64px); border-right:1px solid #e8ebf0; }
        .sidebar a { display:block; padding:11px 13px; margin:4px 0; color:#536174; text-decoration:none; border-radius:8px; }
        .sidebar a:hover, .sidebar .activo { background:#eaf0f8; color:#25364a; font-weight:600; }
        main { padding:30px; }
        .resumen, .tabla { border:0; border-radius:13px; }
        .numero { font-size:2rem; font-weight:700; color:#25364a; margin:0; }
        .simbolo { font-size:1.5rem; background:#edf2f8; padding:10px; border-radius:10px; }
        .table thead th { background:#f8f9fb; color:#68778a; font-size:.78rem; white-space:nowrap; }
        .table td, .table th { padding:13px; vertical-align:middle; }
        @media(max-width:767px) { .sidebar { min-height:auto; } main { padding:20px 12px; } }
    </style>
</head>
<body>

<nav class="navbar navbar-dark barra px-3">
    <a href="index.php" class="navbar-brand fw-bold">
        <img src="../img/flor.png" alt="Logo" width="38" class="me-2">
        ViajesDocentes
    </a>
    <span class="navbar-text text-white-50 d-none d-md-block">Control de traslados institucionales</span>
</nav>

<div class="container-fluid">
    <div class="row">
        <aside class="col-md-3 col-lg-2 sidebar p-3">
            <h6 class="text-secondary mb-3">MENÚ</h6>
            <a href="index.php" class="activo">⌂ &nbsp; Inicio</a>
            <a href="nuevo_viaje.php">＋ &nbsp; Programar viaje</a>
            <a href="viajes.php">▤ &nbsp; Historial de viajes</a>
            <a href="docentes.php">♙ &nbsp; Docentes</a>
            <a href="destinos.php">📍 &nbsp; Destinos</a>
            <a href="transportes.php">🚌 &nbsp; Transportes</a>
        </aside>

        <main class="col-md-9 col-lg-10">
            <h1 class="h3 fw-bold mb-1">Panel principal</h1>
            <p class="text-secondary mb-4">Resumen general del sistema de viajes para docentes.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">No se pudo cargar la información. Verifica la conexión con la base de datos.</div>
            <?php else: ?>

            <div class="row g-3 mb-4">
                <?php
                $tarjetas = [
                    ["Viajes registrados", $totales["viajes"], "🧳"],
                    ["Docentes", $totales["docentes"], "👨‍🏫"],
                    ["Destinos", $totales["destinos"], "📍"],
                    ["Transportes", $totales["transportes"], "🚌"]
                ];
                foreach ($tarjetas as $tarjeta):
                ?>
                <div class="col-sm-6 col-xl-3">
                    <div class="card resumen shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-secondary mb-1"><?= e($tarjeta[0]) ?></p>
                                <p class="numero"><?= (int)$tarjeta[1] ?></p>
                            </div>
                            <span class="simbolo"><?= $tarjeta[2] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <section class="card tabla shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Viajes recientes</h2>
                            <p class="text-secondary small mb-0">Últimos diez registros de viajes.</p>
                        </div>
                        <a href="viajes.php" class="btn btn-outline-primary btn-sm">Ver todos</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Docente</th>
                                    <th>Ruta</th>
                                    <th>Salida</th>
                                    <th>Regreso</th>
                                    <th>Transporte</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($viajes): foreach ($viajes as $viaje): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($viaje["docente"]) ?></td>
                                    <td><?= e($viaje["lugar_salida"]) ?> → <?= e($viaje["destino"]) ?></td>
                                    <td><?= e(date("d/m/Y H:i", strtotime($viaje["fecha_salida"]))) ?></td>
                                    <td><?= e(date("d/m/Y H:i", strtotime($viaje["fecha_regreso"]))) ?></td>
                                    <td><?= e($viaje["tipo_transporte"]) ?><small class="d-block text-secondary"><?= e($viaje["numero_unidad"]) ?></small></td>
                                    <td><span class="badge text-bg-<?= e(estadoClase($viaje["estado"])) ?>"><?= e($viaje["estado"]) ?></span></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="6" class="text-center text-secondary py-4">Todavía no hay viajes registrados.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <?php endif; ?>
            <p class="text-center text-secondary small mt-4">ViajesDocentes · Sistema de gestión de traslados</p>
        </main>
    </div>
</div>
</body>
</html>
