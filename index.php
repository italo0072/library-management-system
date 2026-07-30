<?php
require_once 'classes/Biblioteca.php';

$biblioteca = new Biblioteca();
$mensaje = '';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'crear_libro':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $libro = new Libro($_POST['titulo'], $_POST['autor'], $_POST['isbn'], (int)$_POST['cantidad']);
            $ok = $biblioteca->agregarLibro($libro);
            $mensaje = $ok
                ? 'Libro agregado correctamente.'
                : 'Ya existe un libro activo con ese ISBN.';
        }
        break;

    case 'editar_libro':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $biblioteca->editarLibro($_POST['id'], [
                'titulo'   => $_POST['titulo'],
                'autor'    => $_POST['autor'],
                'isbn'     => $_POST['isbn'],
                'cantidad' => (int)$_POST['cantidad'],
            ]);
            $mensaje = 'Libro actualizado correctamente.';
        }
        break;

    case 'eliminar_libro':
        $biblioteca->eliminarLibro($_GET['id']);
        $mensaje = 'Libro eliminado correctamente.';
        break;

    case 'crear_usuario':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST['nombre'], $_POST['email'], $_POST['telefono']);
            $ok = $biblioteca->agregarUsuario($usuario);
            $mensaje = $ok
                ? 'Usuario agregado correctamente.'
                : 'Ya existe un usuario activo con ese email.';
        }
        break;

    case 'editar_usuario':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $biblioteca->editarUsuario($_POST['id'], [
                'nombre'   => $_POST['nombre'],
                'email'    => $_POST['email'],
                'telefono' => $_POST['telefono'],
            ]);
            $mensaje = 'Usuario actualizado correctamente.';
        }
        break;

    case 'eliminar_usuario':
        $biblioteca->eliminarUsuario($_GET['id']);
        $mensaje = 'Usuario eliminado correctamente.';
        break;

    case 'prestar_libro':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $biblioteca->prestarLibro($_POST['libro_id'], $_POST['usuario_id']);
            $mensaje = $ok ? 'Préstamo registrado correctamente.' : 'No hay ejemplares disponibles para prestar.';
        }
        break;

    case 'devolver_libro':
        $ok = $biblioteca->devolverLibro($_GET['id']);
        $mensaje = $ok ? 'Devolución registrada correctamente.' : 'No se pudo registrar la devolución.';
        break;
}

$libros = $biblioteca->obtenerLibros();
$usuarios = $biblioteca->obtenerUsuarios();
$prestamosActivos = $biblioteca->obtenerPrestamosActivos();

$verHistorial = isset($_GET['ver_historial']);
$historialPrestamos = $verHistorial ? $biblioteca->obtenerTodosPrestamos() : [];

$libroEditar = isset($_GET['editar_libro']) ? $biblioteca->buscarLibro($_GET['editar_libro']) : null;
$usuarioEditar = isset($_GET['editar_usuario']) ? $biblioteca->buscarUsuario($_GET['editar_usuario']) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema de Gestión de Biblioteca</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; color: #222; }
    h1 { text-align: center; color: #2c3e50; }
    .mensaje { max-width: 900px; margin: 0 auto 20px; padding: 12px 16px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 6px; }
    .mensaje.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .contenedor { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .full { grid-column: 1 / -1; }
    .card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .card h2 { margin-top: 0; color: #34495e; border-bottom: 2px solid #eee; padding-bottom: 8px; }
    form label { display: block; margin-top: 10px; font-size: 14px; font-weight: bold; }
    form input, form select { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px; }
    form button { margin-top: 14px; padding: 9px 18px; background: #2c3e50; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    form button:hover { background: #1a252f; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
    th { background: #f0f2f5; }
    .acciones a { margin-right: 8px; text-decoration: none; font-size: 13px; }
    .editar { color: #2980b9; }
    .eliminar { color: #c0392b; }
    .prestar { color: #27ae60; }
    .badge { padding: 2px 8px; border-radius: 10px; font-size: 12px; }
    .badge-activo { background: #fff3cd; color: #856404; }
    .badge-agotado { background: #f8d7da; color: #721c24; }
    .badge-devuelto { background: #d4edda; color: #155724; }
    .titulo-con-boton { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 8px; }
    .titulo-con-boton h2 { border: none; margin: 0; padding: 0; }
    .btn-secundario { text-decoration: none; background: #eef1f4; color: #2c3e50; padding: 7px 14px; border-radius: 4px; font-size: 13px; font-weight: bold; white-space: nowrap; }
    .btn-secundario:hover { background: #dfe4e8; }
</style>
</head>
<body>

<h1> Sistema de Gestión de Biblioteca</h1>

<?php if ($mensaje):
    $esError = strpos($mensaje, 'No se puede') === 0 || strpos($mensaje, 'No hay') === 0
        || strpos($mensaje, 'No se pudo') === 0 || strpos($mensaje, 'Ya existe') === 0;
?>
    <div class="mensaje<?= $esError ? ' error' : '' ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="contenedor">

    <div class="card">
        <h2><?= $libroEditar ? 'Editar Libro' : 'Agregar Libro' ?></h2>
        <form method="POST" action="index.php?action=<?= $libroEditar ? 'editar_libro' : 'crear_libro' ?>">
            <?php if ($libroEditar): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($libroEditar['id']) ?>">
            <?php endif; ?>
            <label>Título</label>
            <input type="text" name="titulo" required value="<?= htmlspecialchars($libroEditar['titulo'] ?? '') ?>">
            <label>Autor</label>
            <input type="text" name="autor" required value="<?= htmlspecialchars($libroEditar['autor'] ?? '') ?>">
            <label>ISBN</label>
            <input type="text" name="isbn" value="<?= htmlspecialchars($libroEditar['isbn'] ?? '') ?>">
            <label>Cantidad</label>
            <input type="number" name="cantidad" min="0" required value="<?= htmlspecialchars($libroEditar['cantidad'] ?? 1) ?>">
            <button type="submit"><?= $libroEditar ? 'Guardar Cambios' : 'Agregar Libro' ?></button>
        </form>
    </div>

    <div class="card">
        <h2><?= $usuarioEditar ? 'Editar Usuario' : 'Agregar Usuario' ?></h2>
        <form method="POST" action="index.php?action=<?= $usuarioEditar ? 'editar_usuario' : 'crear_usuario' ?>">
            <?php if ($usuarioEditar): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($usuarioEditar['id']) ?>">
            <?php endif; ?>
            <label>Nombre</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($usuarioEditar['nombre'] ?? '') ?>">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($usuarioEditar['email'] ?? '') ?>">
            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($usuarioEditar['telefono'] ?? '') ?>">
            <button type="submit"><?= $usuarioEditar ? 'Guardar Cambios' : 'Agregar Usuario' ?></button>
        </form>
    </div>

    <div class="card full">
        <h2>Registrar Préstamo</h2>
        <form method="POST" action="index.php?action=prestar_libro">
            <label>Libro</label>
            <select name="libro_id" required>
                <option value="">-- Selecciona un libro --</option>
                <?php foreach ($libros as $libro): ?>
                    <option value="<?= $libro['id'] ?>" <?= $libro['cantidad'] <= 0 ? 'disabled' : '' ?>>
                        <?= htmlspecialchars($libro['titulo']) ?> (disponibles: <?= $libro['cantidad'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Usuario</label>
            <select name="usuario_id" required>
                <option value="">-- Selecciona un usuario --</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Prestar Libro</button>
        </form>
    </div>

    <div class="card full">
        <h2>Libros</h2>
        <table>
            <thead>
                <tr><th>Título</th><th>Autor</th><th>ISBN</th><th>Cantidad</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($libros as $libro): ?>
                <tr>
                    <td><?= htmlspecialchars($libro['titulo']) ?></td>
                    <td><?= htmlspecialchars($libro['autor']) ?></td>
                    <td><?= htmlspecialchars($libro['isbn']) ?></td>
                    <td>
                        <?= $libro['cantidad'] ?>
                        <?php if ($libro['cantidad'] <= 0): ?>
                            <span class="badge badge-agotado">agotado</span>
                        <?php endif; ?>
                    </td>
                    <td class="acciones">
                        <a class="editar" href="index.php?editar_libro=<?= $libro['id'] ?>">Editar</a>
                        <a class="eliminar" href="index.php?action=eliminar_libro&id=<?= $libro['id'] ?>"
                           onclick="return confirm('¿Eliminar este libro?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($libros)): ?>
                    <tr><td colspan="5">No hay libros registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card full">
        <h2>Usuarios</h2>
        <table>
            <thead>
                <tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                    <td class="acciones">
                        <a class="editar" href="index.php?editar_usuario=<?= $usuario['id'] ?>">Editar</a>
                        <a class="eliminar" href="index.php?action=eliminar_usuario&id=<?= $usuario['id'] ?>"
                           onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="4">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card full">
        <div class="titulo-con-boton">
            <h2>Préstamos Activos</h2>
            <?php if ($verHistorial): ?>
                <a class="btn-secundario" href="index.php">Ocultar Historial</a>
            <?php else: ?>
                <a class="btn-secundario" href="index.php?ver_historial=1">Ver Historial de Devoluciones</a>
            <?php endif; ?>
        </div>
        <table>
            <thead>
                <tr><th>Libro</th><th>Usuario</th><th>Fecha Préstamo</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($prestamosActivos as $prestamo): ?>
                <tr>
                    <td><?= htmlspecialchars($prestamo['libro_titulo']) ?></td>
                    <td><?= htmlspecialchars($prestamo['usuario_nombre']) ?></td>
                    <td><?= htmlspecialchars($prestamo['fecha_prestamo']) ?></td>
                    <td><span class="badge badge-activo"><?= htmlspecialchars($prestamo['estado']) ?></span></td>
                    <td class="acciones">
                        <a class="prestar" href="index.php?action=devolver_libro&id=<?= $prestamo['id'] ?>"
                           onclick="return confirm('¿Marcar como devuelto?')">Devolver</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($prestamosActivos)): ?>
                    <tr><td colspan="5">No hay préstamos activos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($verHistorial): ?>
    <div class="card full">
        <h2>Historial de Préstamos y Devoluciones</h2>
        <table>
            <thead>
                <tr><th>Libro</th><th>Usuario</th><th>Fecha Préstamo</th><th>Fecha Devolución</th><th>Estado</th></tr>
            </thead>
            <tbody>
                <?php foreach ($historialPrestamos as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['libro_titulo']) ?></td>
                    <td><?= htmlspecialchars($h['usuario_nombre']) ?></td>
                    <td><?= htmlspecialchars($h['fecha_prestamo']) ?></td>
                    <td><?= htmlspecialchars($h['fecha_devolucion'] ?? '—') ?></td>
                    <td>
                        <?php if ($h['estado'] === 'devuelto'): ?>
                            <span class="badge badge-devuelto">devuelto</span>
                        <?php else: ?>
                            <span class="badge badge-activo">activo</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($historialPrestamos)): ?>
                    <tr><td colspan="5">No hay préstamos registrados todavía.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
