<?php
require_once "config/conexion.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Instrucciones | Gestión de Viajes</title>

    <link rel="stylesheet" href="css/estilo.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>

    <!-- HEADER -->
    <header>
        <nav class="navbar bg-dark navbar-dark">
            <div class="container-fluid">

                <div>
                    <img src="img/flor.png" alt="Logo" class="img-logo">

                    <a href="index.php" class="navbar-brand logo">
                        Gestión de Viajes
                    </a>
                </div>

                <span class="navbar-text">
                    Sistema de viajes para docentes
                </span>

            </div>
        </nav>
    </header>

    <div class="container-fluid">
        <div class="row">

            <!-- MENU LATERAL -->
            <aside class="col-md-3 col-lg-2 sidebar p-3">

                <h6 class="text-secondary mb-3">
                    MENÚ PRINCIPAL
                </h6>

                <a href="index.php">Inicio</a>

                <a href="docentes.php">Docentes</a>

                <a href="destinos.php">Destinos</a>

                <a href="transportes.php">Transportes</a>

                <a href="viajes.php">Viajes</a>

                <a href="instrucciones.php" class="activo">
                    Instrucciones
                </a>

            </aside>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="col-md-9 col-lg-10 contenido">

                <header class="mb-4">

                    <h1 class="h3">
                        Instrucciones del sistema
                    </h1>

                    <p class="text-secondary">
                        Guía para utilizar correctamente el sistema
                        de gestión de viajes de ida y vuelta para docentes.
                    </p>

                </header>

                <!-- BIENVENIDA -->
                <section class="mb-4">

                    <div class="card shadow-sm border-0">

                        <div class="card-body">

                            <h2 class="h5">
                                Bienvenido al sistema
                            </h2>

                            <p>
                                Este sistema permite administrar los viajes
                                realizados por los docentes, facilitando el
                                registro de información personal, destinos,
                                transportes y fechas de salida y regreso.
                            </p>

                            <p>
                                Para utilizar correctamente el sistema,
                                sigue las instrucciones que se presentan
                                a continuación.
                            </p>

                        </div>

                    </div>

                </section>

                <!-- PASO 1 -->
                <section class="mb-4">

                    <div class="card shadow-sm border-0">

                        <div class="card-body">

                            <h2 class="h5 text-primary">
                                1. Registro de docentes
                            </h2>

                            <p>
                                En esta sección se administra la información
                                de los docentes que realizarán los viajes.
                            </p>

                            <ul>
                                <li>Ingresar el nombre completo del docente.</li>
                                <li>Registrar su correo electrónico.</li>
                                <li>Agregar su número telefónico.</li>
                                <li>Registrar su número de empleado.</li>
                                <li>Establecer su contraseña de acceso.</li>
                            </ul>

                            <p class="text-secondary mb-0">
                                Importante: verificar que el correo electrónico
                                y el número de empleado no estén registrados
                                anteriormente.
                            </p>

                        </div>

                    </div>

                </section>

                <!-- PASO 2 -->
                <section class="mb-4">

                    <div class="card shadow-sm border-0">

                        <div class="card-body">

                            <h2 class="h5 text-primary">
                                2. Registro de destinos
                            </h2>

                            <p>
                                En esta sección se registran los lugares
                                a los que podrán viajar los docentes.
                            </p>

                            <ul>
                                <li>Escribir el nombre del destino.</li>
                                <li>Indicar el origen del viaje.</li>
                                <li>Registrar la dirección correspondiente.</li>
                                <li>Agregar una descripción del destino.</li>
                            </ul>

                            <p class="text-secondary mb-0">
                                Se recomienda verificar los datos del destino
                                antes de guardar el registro.
                            </p>

                        </div>

                    </div>

                </section>

                <!-- PASO 3 -->
                <section class="mb-4">

                    <div class="card shadow-sm border-0">

                        <div class="card-body">

                            <h2 class="h5 text-primary">
                                3. Registro de transportes
                            </h2>

                            <p>
                                Esta sección permite registrar los medios
                                de transporte disponibles para realizar
                                los viajes.
                            </p>

                            <ul>
                                <li>Seleccionar el tipo de transporte.</li>
                                <li>Registrar el número de unidad.</li>
                                <li>Indicar la capacidad del transporte.</li>
                                <li>Registrar el nombre del conductor.</li>
                            </ul>

                            <p class="text-secondary mb-0">
                                Cada unidad debe contar con su información
                                correspondiente para facilitar su identificación.
                            </p>

                        </div>

                    </div>

                </section>

                <!-- PASO 4 -->
                <section class="mb-4">

                    <div class="card shadow-sm border-0">

                        <div class="card-body">

                            <h2 class="h5 text-primary">
                                4. Registro de viajes
                            </h2>

                            <p>
                                En esta sección se registran los viajes
                                de ida y vuelta que realizarán los docentes.
                            </p>

                            <ul>
                                <li>Seleccionar al docente que realizará el viaje.</li>
                                <li>Seleccionar el destino correspondiente.</li>
                                <li>Asignar el transporte disponible.</li>
                                <li>Ingresar la fecha y hora de salida.</li>
                                <li>Ingresar la fecha y hora de regreso.</li>
                                <li>Registrar el lugar de salida.</li>
                                <li>Establecer el estado del viaje.</li>
                            </ul>

                            <div class="alert alert-info mb-0">

                                <strong>Importante:</strong>

                                La fecha de regreso debe ser posterior
                                a la fecha de salida. Verifica que toda
                                la información sea correcta antes de
                                guardar el viaje.

                            </div>

                        </div>

                    </div>

                </section>

                <!-- PASO 5 -->
                <section class="mb-4">

                    <div class="card shadow-sm border-0">

                        <div class="card-body">

                            <h2 class="h5 text-primary">
                                5. Consulta de viajes
                            </h2>

                            <p>
                                Después de registrar un viaje, se puede
                                consultar la información almacenada
                                en el sistema.
                            </p>

                            <ul>
                                <li>Consultar los datos del docente.</li>
                                <li>Verificar el destino seleccionado.</li>
                                <li>Revisar el transporte asignado.</li>
                                <li>Consultar las fechas de salida y regreso.</li>
                                <li>Verificar el estado del viaje.</li>
                            </ul>

                        </div>

                    </div>

                </section>

                <!-- RECOMENDACIONES -->
                <section class="mb-4">

                    <div class="card shadow-sm border-0">

                        <div class="card-body">

                            <h2 class="h5 text-success">
                                Recomendaciones generales
                            </h2>

                            <ul>
                                <li>
                                    Verificar que los datos ingresados sean correctos.
                                </li>

                                <li>
                                    Evitar registrar información duplicada.
                                </li>

                                <li>
                                    Comprobar la disponibilidad del transporte.
                                </li>

                                <li>
                                    Revisar las fechas de salida y regreso.
                                </li>

                                <li>
                                    Mantener actualizada la información de los viajes.
                                </li>

                                <li>
                                    No compartir las contraseñas de acceso.
                                </li>
                            </ul>

                        </div>

                    </div>

                </section>

                <!-- FINAL -->
                <section>

                    <div class="alert alert-success">

                        <h2 class="h5">
                            ¡Listo para utilizar el sistema!
                        </h2>

                        <p class="mb-0">
                            Siguiendo estas instrucciones podrás utilizar
                            correctamente el sistema de gestión de viajes
                            de ida y vuelta para docentes, manteniendo
                            organizada la información de cada viaje.
                        </p>

                    </div>

                </section>

            </main>

        </div>
    </div>

    <!-- BOOTSTRAP -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>