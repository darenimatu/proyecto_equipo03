<?php
// Conexión a la base de datos
require_once __DIR__ . "/conexion.php";

// Verificar que exista la conexión PDO
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Error: no se pudo establecer la conexión con la base de datos.");
}

try {
    // 1. Contar todos los viajes
    $stmtTotal = $pdo->query("
        SELECT COUNT(*)
        FROM viajes
    ");

    $totalViajes = $stmtTotal->fetchColumn();

    // 2. Contar los viajes con estado válido
    $stmtCorrectas = $pdo->query("
        SELECT COUNT(*)
        FROM viajes
        WHERE estado IN ('Programado', 'En curso', 'Completado')
    ");

    $totalCorrectas = $stmtCorrectas->fetchColumn();

    // 3. Contar los viajes cancelados
    $stmtCanceladas = $pdo->query("
        SELECT COUNT(*)
        FROM viajes
        WHERE estado = 'Cancelado'
    ");

    $totalCanceladas = $stmtCanceladas->fetchColumn();

    // 4. Consultar los datos de los viajes
    $sqlValidaciones = "
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
        FROM viajes v
        INNER JOIN docentes d
            ON v.id_docente = d.id_docente
        INNER JOIN destinos dest
            ON v.id_destino = dest.id_destino
        INNER JOIN transportes t
            ON v.id_transporte = t.id_transporte
        ORDER BY v.id_viaje DESC
        LIMIT 10
    ";

    $stmtValidaciones = $pdo->query($sqlValidaciones);
    $validaciones = $stmtValidaciones->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al consultar los viajes. Revisa la conexión y las tablas de la base de datos.");
}

// Función para mostrar texto de forma segura
function mostrar($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Función para mostrar las fechas
function mostrarFecha($fecha)
{
    if (empty($fecha)) {
        return 'No registrada';
    }

    $timestamp = strtotime($fecha);

    if ($timestamp === false) {
        return 'Fecha inválida';
    }

    return date('d/m/Y H:i', $timestamp);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Validaciones | EduTrip</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../CSS/estilo.css">
</head>

<body>

    <!-- ENCABEZADO -->
    <header>
        <nav class="navbar bg-dark navbar-dark">
            <div class="container-fluid">

                <a href="../index.php" class="navbar-brand">
                    EduTrip
                </a>

                <span class="navbar-text text-white">
                    Control de traslados institucionales
                </span>

            </div>
        </nav>
    </header>

    <div class="container-fluid">
        <div class="row">

            <!-- MENÚ LATERAL -->
            <aside class="col-md-3 col-lg-2 sidebar p-3">

                <h6 class="text-secondary mb-3">
                    MENÚ
                </h6>

                <a href="../index.php">Inicio</a>

                <a href="../nuevo_viaje.php">
                    Programar viaje
                </a>

                <a href="../viajes.php">
                    Historial de viajes
                </a>

                <a href="../docentes.php">
                    Docentes
                </a>

                <a href="../destinos.php">
                    Destinos
                </a>

                <a href="../transportes.php">
                    Transportes
                </a>

                <a href="validaciones.php" class="activo">
                    Validaciones
                </a>

            </aside>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="col-md-9 col-lg-10 contenido">

                <header class="mb-4">

                    <h1 class="h3">
                        Panel de validaciones
                    </h1>

                    <p class="text-secondary">
                        Verificación de la información de los viajes
                        de ida y vuelta para docentes.
                    </p>

                </header>

                <!-- TARJETAS DE RESUMEN -->
                <section class="mb-4">

                    <div class="row g-3">

                        <!-- TOTAL DE VIAJES -->
                        <article class="col-md-4">

                            <div class="card shadow-sm tarjeta-resumen">
                                <div class="card-body">

                                    <p class="text-secondary">
                                        Viajes registrados
                                    </p>

                                    <p class="numero">
                                        <?= (int) $totalViajes ?>
                                    </p>

                                </div>
                            </div>

                        </article>

                        <!-- VIAJES VÁLIDOS -->
                        <article class="col-md-4">

                            <div class="card shadow-sm tarjeta-resumen">
                                <div class="card-body">

                                    <p class="text-secondary">
                                        Viajes con estado válido
                                    </p>

                                    <p class="numero">
                                        <?= (int) $totalCorrectas ?>
                                    </p>

                                </div>
                            </div>

                        </article>

                        <!-- VIAJES CANCELADOS -->
                        <article class="col-md-4">

                            <div class="card shadow-sm tarjeta-resumen">
                                <div class="card-body">

                                    <p class="text-secondary">
                                        Viajes cancelados
                                    </p>

                                    <p class="numero">
                                        <?= (int) $totalCanceladas ?>
                                    </p>

                                </div>
                            </div>

                        </article>

                    </div>

                </section>

                <!-- TABLA DE VALIDACIONES -->
                <section>

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <h2 class="h5 mb-3">
                                Verificación de datos de viajes
                            </h2>

                            <div class="table-responsive">

                                <table class="table table-hover align-middle">

                                    <thead>
                                        <tr>
                                            <th>Docente</th>
                                            <th>Destino</th>
                                            <th>Lugar de salida</th>
                                            <th>Fecha de ida</th>
                                            <th>Fecha de regreso</th>
                                            <th>Transporte</th>
                                            <th>Estado</th>
                                            <th>Validación</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php if (count($validaciones) > 0): ?>

                                            <?php foreach ($validaciones as $viaje): ?>

                                                <?php
                                                // Comprobar que los datos estén completos
                                                $datosCompletos =
                                                    !empty($viaje['docente']) &&
                                                    !empty($viaje['destino']) &&
                                                    !empty($viaje['lugar_salida']) &&
                                                    !empty($viaje['fecha_salida']) &&
                                                    !empty($viaje['fecha_regreso']) &&
                                                    strtotime($viaje['fecha_salida']) !== false &&
                                                    strtotime($viaje['fecha_regreso']) !== false &&
                                                    strtotime($viaje['fecha_regreso']) >
                                                    strtotime($viaje['fecha_salida']) &&
                                                    !empty($viaje['tipo_transporte']) &&
                                                    !empty($viaje['numero_unidad']);

                                                $badgeClass = $datosCompletos
                                                    ? 'text-bg-success'
                                                    : 'text-bg-danger';

                                                $texto = $datosCompletos
                                                    ? 'Datos completos'
                                                    : 'Revisar';
                                                ?>

                                                <tr>

                                                    <td>
                                                        <?= mostrar($viaje['docente']) ?>
                                                    </td>

                                                    <td>
                                                        <?= mostrar($viaje['destino']) ?>
                                                    </td>

                                                    <td>
                                                        <?= mostrar($viaje['lugar_salida']) ?>
                                                    </td>

                                                    <td>
                                                        <?= mostrarFecha($viaje['fecha_salida']) ?>
                                                    </td>

                                                    <td>
                                                        <?= mostrarFecha($viaje['fecha_regreso']) ?>
                                                    </td>

                                                    <td>
                                                        <?= mostrar($viaje['tipo_transporte']) ?>
                                                        (<?= mostrar($viaje['numero_unidad']) ?>)
                                                    </td>

                                                    <td>
                                                        <?= mostrar($viaje['estado']) ?>
                                                    </td>

                                                    <td>
                                                        <span class="badge <?= $badgeClass ?>">
                                                            <?= $texto ?>
                                                        </span>
                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <tr>
                                                <td colspan="8"
                                                    class="text-center text-muted py-4">

                                                    No hay registros para validar.

                                                </td>
                                            </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </section>

            </main>

        </div>
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

</body>
</html>
```