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
            $nombre = trim($_POST["nombre_completo"]);
            $correo = trim($_POST["correo"]);
            $telefono = trim($_POST["telefono"]);
            $numero = trim($_POST["numero_empleado"]);
            $contrasena = trim($_POST["contrasena"]);

            if ($accion === "guardar") {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                $sql = "INSERT INTO docentes
                (nombre_completo, correo, telefono, numero_empleado, contrasena)
                VALUES (?, ?, ?, ?, ?)";

                $pdo->prepare($sql)->execute([
                    $nombre, $correo, $telefono, $numero, $hash
                ]);

                $mensaje = "Docente registrado correctamente.";

            } else {
                $id = (int)$_POST["id_docente"];

                if ($contrasena !== "") {
                    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                    $sql = "UPDATE docentes SET
                    nombre_completo=?, correo=?, telefono=?,
                    numero_empleado=?, contrasena=?
                    WHERE id_docente=?";

                    $pdo->prepare($sql)->execute([
                        $nombre, $correo, $telefono, $numero, $hash, $id
                    ]);
                } else {
                    $sql = "UPDATE docentes SET
                    nombre_completo=?, correo=?, telefono=?,
                    numero_empleado=?
                    WHERE id_docente=?";

                    $pdo->prepare($sql)->execute([
                        $nombre, $correo, $telefono, $numero, $id
                    ]);
                }

                $mensaje = "Docente actualizado correctamente.";
            }

        } elseif ($accion === "eliminar") {
            $id = (int)$_POST["id_docente"];

            $pdo->prepare(
                "DELETE FROM docentes WHERE id_docente=?"
            )->execute([$id]);

            $mensaje = "Docente eliminado.";
        }

    } catch (PDOException $ex) {
        $mensaje = "No se pudo realizar la operación. Verifica los datos o las relaciones existentes.";
    }
}

$editar = null;

if (isset($_GET["editar"])) {
    $st = $pdo->prepare(
        "SELECT * FROM docentes WHERE id_docente=?"
    );
    $st->execute([(int)$_GET["editar"]]);
    $editar = $st->fetch(PDO::FETCH_ASSOC);
}

$docentes = $pdo->query(
    "SELECT * FROM docentes ORDER BY id_docente DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Docentes | ViajesDocentes</title>

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
    <a href="docentes.php" class="activo">Docentes</a>
    <a href="destinos.php">Destinos</a>
    <a href="transportes.php">Transportes</a>
</aside>

<main class="col-md-9 col-lg-10 contenido">

<h1 class="h3">Administración de docentes</h1>
<p class="text-secondary">Registro del personal docente.</p>

<?php if ($mensaje): ?>
<div class="alert alert-info">
    <?= e($mensaje) ?>
</div>
<?php endif; ?>

<div class="row g-4">

<div class="col-lg-5">
<div class="card shadow-sm">
<div class="card-body">

<h5><?= $editar ? "Editar docente" : "Registrar docente" ?></h5>

<form method="POST">

<input type="hidden" name="accion"
value="<?= $editar ? "actualizar" : "guardar" ?>">

<?php if ($editar): ?>
<input type="hidden" name="id_docente"
value="<?= (int)$editar["id_docente"] ?>">
<?php endif; ?>

<div class="mb-3">
<label class="form-label">Nombre completo</label>
<input class="form-control" name="nombre_completo"
required value="<?= e($editar["nombre_completo"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Correo electrónico</label>
<input type="email" class="form-control" name="correo"
required value="<?= e($editar["correo"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Teléfono</label>
<input class="form-control" name="telefono"
required value="<?= e($editar["telefono"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Número de empleado</label>
<input class="form-control" name="numero_empleado"
required value="<?= e($editar["numero_empleado"] ?? "") ?>">
</div>

<div class="mb-3">
<label class="form-label">Contraseña</label>
<input type="password" class="form-control"
name="contrasena" <?= $editar ? "" : "required" ?>>
<?php if ($editar): ?>
<small class="text-muted">Déjala vacía para conservar la contraseña.</small>
<?php endif; ?>
</div>

<button class="btn btn-primary">
<?= $editar ? "Guardar cambios" : "Registrar docente" ?>
</button>

<?php if ($editar): ?>
<a href="docentes.php" class="btn btn-secondary">Cancelar</a>
<?php endif; ?>

</form>
</div>
</div>
</div>

<div class="col-lg-7">
<div class="card shadow-sm">
<div class="card-body">

<h5>Docentes registrados</h5>

<div class="table-responsive">
<table class="table table-hover">

<thead>
<tr>
<th>Nombre</th>
<th>Correo</th>
<th>Teléfono</th>
<th>Empleado</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach ($docentes as $d): ?>
<tr>

<td><?= e($d["nombre_completo"]) ?></td>
<td><?= e($d["correo"]) ?></td>
<td><?= e($d["telefono"]) ?></td>
<td><?= e($d["numero_empleado"]) ?></td>

<td>
<a class="btn btn-sm btn-primary"
href="docentes.php?editar=<?= (int)$d["id_docente"] ?>">
Editar
</a>

<form method="POST" class="d-inline"
onsubmit="return confirm('¿Eliminar docente?')">

<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id_docente"
value="<?= (int)$d["id_docente"] ?>">

<button class="btn btn-sm btn-danger">Eliminar</button>
</form>
</td>

</tr>
<?php endforeach; ?>

<?php if (!$docentes): ?>
<tr>
<td colspan="5" class="text-center">No hay docentes registrados.</td>
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
