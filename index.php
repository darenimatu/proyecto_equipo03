<?php
require_once "../config/conexion.php";

/*
 * Panel principal de ViajesDocentes
 * Tablas utilizadas: docentes, destinos, transportes y viajes.
 */

function e($valor): string
{
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

function fechaLegible(?string $fecha): string
{
    if (empty($fecha)) {
        return "No especificada";
    }

   $timestamp=strtotime($fecha);
    return $timestamp ? date("d/m/Y H:i", $timestamp) : "Fecha no válida";
}

$totales = [
    "viajes" => 0,
    "docentes" => 0,
    "destinos" => 0,
    "transportes" => 0
];

$viajesRecientes = [];
$errorConsulta = null;

try {
    // Conteo de registros de las cuatro tablas.
    $sqlTotales = "
        SELECT
            (SELECT COUNT(*) FROM viajes) AS viajes,
            (SELECT COUNT(*) FROM docentes) AS docentes,
            (SELECT COUNT(*) FROM destinos) AS destinos,
            (SELECT COUNT(*) FROM transportes) AS transportes
    ";

    $totales = pdo->query(sqlTotales)->fetch(PDO::FETCH_ASSOC);

    // Últimos diez viajes, relacionando las cuatro tablas.
    $sqlViajes = "
        SELECT
            v.id_viaje,
            d.nombre_completo AS docente,
            dest.nombre_destino AS destino,
            v.lugar_salida,
            v.fecha_salida,
            v.fecha_regreso,
            t.tipo_transporte,
            t.numero_unidad,
            v.estado
        FROM viajes AS v
        INNER JOIN docentes AS d
            ON v.id_docente = d.id_docente
        INNER JOIN destinos AS dest
            ON v.id_destino = dest.id_destino
        INNER JOIN transportes AS t
            ON v.id_transporte = t.id_transporte
        ORDER BY v.fecha_salida DESC
        LIMIT 10
    ";

    $viajesRecientes = pdo->query(sqlViajes)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $error) {
    // El detalle técnico se registra en el servidor, no se muestra al visitante.
    error_log("Error en el panel de ViajesDocentes: " . $error->getMessage());
    $errorConsulta = "No fue posible cargar la información. Verifica la conexión y la estructura de la base de datos.";
}

function claseEstado(?string $estado): string
{
    switch ($estado) {
        case "Programado":
            return "bg-primary-subtle text-primary";
        case "En curso":
            return "bg-warning-subtle text-dark";
        case "Completado":
            return "bg-success-subtle text-success";
        case "Cancelado":
            return "bg-danger-subtle text-danger";
        default:
            return "bg-secondary-subtle text-secondary";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ViajesDocentes | Panel principal</title>

    <link rel="stylesheet" href="../css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">

    <style>
        :root {
            --color-principal: #25364a;
            --color-fondo: #f4f6fa;
            --color-texto: #263445;
        }

        body {
            background: var(--color-fondo);
            color: var(--color-texto);
            min-height: 100vh;
        }

        .barra-superior {
            background: var(--color-principal);
            min-height: 70px;
        }

        .img-logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
            margin-right: 10px;
        }

        .marca {
            display: inline-flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .sidebar {
            background: #fff;
            min-height: calc(100vh - 70px);
            border-right: 1px solid #e7eaf0;
        }

        .sidebar a {
            display: block;
            color: #526174;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 9px;
            margin-bottom: 5px;
            transition: .2s ease;
        }

        .sidebar a:hover,
        .sidebar a.activo {
            background: #eaf0f8;
            color: var(--color-principal);
            font-weight: 600;
        }

        .contenido {
            padding: 32px;
        }

        .tarjeta-resumen {
            border: 0;
            border-radius: 14px;
            height: 100%;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .tarjeta-resumen:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(28, 45, 68, .10) !important;
        }

        .icono-tarjeta {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            font-size: 1.35rem;
            background: #edf2f8;
        }

        .numero {
            font-size: 2rem;
            line-height: 1.2;
            font-weight: 700;
            margin: 8px 0 0;
            color: var(--color-principal);
        }

        .panel-tabla {
            border: 0;
            border-radius: 14px;
            overflow: hidden;
        }

        .panel-tabla .card-header {
            background: #fff;
            border-bottom: 1px solid #edf0f4;
            padding: 20px 22px;
        }

        .table thead th {
            color: #66758a;
            background: #f8f9fb;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
            padding: 14px;
        }

        .table tbody td {
            padding: 14px;
            font-size: .9rem;
        }

        .badge-estado {
            padding: 7px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                min-height: auto;
                border-right: 0;
                border-bottom: 1px solid #e7eaf0;
            }

            .contenido {
                padding: 22px 14px;
            }

            .texto-navbar {
                display: none;
            }
        }
    </style>
</head>

<body>

<header>
    <nav class="navbar navbar-dark barra-superior">
        <div class="container-fluid px-4">
            <a href="index.php" class="marca">
                <img src="../img/flor.png" alt="Logo de ViajesDocentes" class="img-logo">
                ViajesDocentes
            </a>

            <span class="navbar-text text-white-50 texto-navbar">
                Sistema de control de traslados institucionales
            </span>
        </div>
    </nav>
</header>

<div class="container-fluid">
    <div class="row">

        <!-- Menú lateral -->
        <aside class="col-md-3 col-lg-2 sidebar p-3">
            <h6 class="text-secondary fw-bold mb-3 mt-2">MENÚ PRINCIPAL</h6>

            <a href="index.php" class="activo">⌂ &nbsp; Inicio</a>
            <a href="nuevo_viaje.php">＋ &nbsp; Programar viaje</a>
            <a href="viajes.php">▤ &nbsp; Historial de viajes</a>
            <a href="docentes.php">♙ &nbsp; Docentes</a>
            <a href="destinos.php">⌖ &nbsp; Destinos</a>
            <a href="transportes.php">▣ &nbsp; Transportes</a>
        </aside>

        <!-- Contenido principal -->
        <main class="col-md-9 col-lg-10 contenido">

            <div class="mb-4">
                <span class="text-secondary small">INICIO / PANEL PRINCIPAL</span>
                <h1 class="h3 fw-bold mt-2 mb-1">Panel principal</h1>
                <p class="text-secondary mb-0">
                    Consulta general de los registros del sistema de viajes para docentes.
                </p>
            </div>

            <?php if ($errorConsulta): ?>
                <div class="alert alert-danger" role="alert">
                    <?= e($errorConsulta) ?>
                </div>
            <?php else: ?>

                <!-- Tarjetas con el total de registros por tabla -->
                <section class="mb-4">
                    <div class="row g-3">

                        <div class="col-sm-6 col-xl-3">
                            <article class="card shadow-sm tarjeta-resumen">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-secondary mb-1">Total de viajes</p>
                                            <p class="numero"><?= (int)$totales["viajes"] ?></p>
                                        </div>
                                        <div class="icono-tarjeta">🧳</div>
                                    </div>
                                    <small class="text-secondary">Tabla: viajes</small>
                                </div>
                            </article>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <article class="card shadow-sm tarjeta-resumen">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-secondary mb-1">Docentes registrados</p>
                                            <p class="numero"><?= (int)$totales["docentes"] ?></p>
                                        </div>
                                        <div class="icono-tarjeta">👨‍🏫</div>
                                    </div>
                                    <small class="text-secondary">Tabla: docentes</small>
                                </div>
                            </article>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <article class="card shadow-sm tarjeta-resumen">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-secondary mb-1">Destinos registrados</p>
                                            <p class="numero"><?= (int)$totales["destinos"] ?></p>
                                        </div>
                                        <div class="icono-tarjeta">📍</div>
                                    </div>
                                    <small class="text-secondary">Tabla: destinos</small>
                                </div>
                            </article>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <article class="card shadow-sm tarjeta-resumen">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-secondary mb-1">Transportes registrados</p>
                                            <p class="numero"><?= (int)$totales["transportes"] ?></p>
                                        </div>
                                        <div class="icono-tarjeta">🚌</div>
                                    </div>
                                    <small class="text-secondary">Tabla: transportes</small>
                                </div>
                            </article>
                        </div>

                    </div>
                </section>

                <!-- Tabla de viajes recientes -->
                <section class="card shadow-sm panel-tabla">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Viajes recientes</h2>
                            <p class="text-secondary small mb-0">
                                Información relacionada de docentes, destinos y transportes.
                            </p>
                        </div>
                        <a href="viajes.php" class="btn btn-outline-primary btn-sm">
                            Ver todos los viajes
                        </a>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Docente</th>
                                        <th>Ruta</th>
                                        <th>Fecha de salida</th>
                                        <th>Fecha de regreso</th>
                                        <th>Transporte</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (!empty($viajesRecientes)): ?>
                                        <?php foreach ($viajesRecientes as $viaje): ?>
                                            <tr>
                                                <td class="text-secondary">
                                                    #<?= (int)$viaje["id_viaje"] ?>
                                                </td>

                                                <td class="fw-semibold">
                                                    <?= e($viaje["docente"]) ?>
                                                </td>

                                                <td>
                                                    <span class="text-secondary">
                                                        <?= e($viaje["lugar_salida"]) ?>
                                                    </span>
                                                    <span class="text-primary mx-1">→</span>
                                                    <span class="fw-semibold">
                                                        <?= e($viaje["destino"]) ?>
                                                    </span>
                                                </td>

                                                <td><?= e(fechaLegible($viaje["fecha_salida"])) ?></td>
                                                <td><?= e(fechaLegible($viaje["fecha_regreso"])) ?></td>

                                                <td>
                                                    <?= e($viaje["tipo_transporte"]) ?>
                                                    <small class="d-block text-secondary">
                                                        Unidad: <?= e($viaje["numero_unidad"]) ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <span class="badge badge-estado <?= e(claseEstado($viaje["estado"])) ?>">
                                                        <?= e($viaje["estado"]) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-secondary py-5">
                                                <div class="fs-2 mb-2">🗂️</div>
                                                <strong>No hay viajes registrados.</strong>
                                                <p class="small mb-0">
                                                    Cuando programes un viaje, aparecerá en esta tabla.
                                                </p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

            <?php endif; ?>

            <footer class="text-center text-secondary small mt-4">
                ViajesDocentes · Sistema de control de traslados institucionales
            </footer>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>
</html>