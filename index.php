<?php
require_once 'db.php';

$all_users = [
    ["user_id" => "Carlos", "name" => "Carlos Mendoza"],
    ["user_id" => "Ana", "name" => "Ana López"],
    ["user_id" => "Juan", "name" => "Juan Pérez"],
    ["user_id" => "María", "name" => "María Gómez"],
    ["user_id" => "Luis", "name" => "Luis Fernández"]
];

$default_tasks = [
    ["title" => "Revisión de documentación", "description" => "Actualizar y corregir los documentos del proyecto."],
    ["title" => "Optimización de base de datos", "description" => "Mejorar consultas SQL para mayor eficiencia."],
    ["title" => "Diseño de interfaz", "description" => "Crear un prototipo para la nueva interfaz de usuario."],
    ["title" => "Reunión semanal", "description" => "Presentación de avances y objetivos en la reunión semanal."],
    ["title" => "Testeo de funcionalidades", "description" => "Ejecutar pruebas de unidad y de integración en el código."],
    ["title" => "Actualización de librerías", "description" => "Revisar y actualizar las librerías a sus versiones más recientes."],
    ["title" => "Documentación de API", "description" => "Escribir documentación para las nuevas funciones de la API."],
    ["title" => "Análisis de rendimiento", "description" => "Monitorear y ajustar el rendimiento de la aplicación."],
    ["title" => "Control de calidad", "description" => "Revisar la aplicación en búsqueda de errores y mejoras."],
    ["title" => "Creación de scripts de despliegue", "description" => "Automatizar el proceso de despliegue en el servidor."]
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = !empty($_POST['existing_user']) ? $_POST['existing_user'] : $_POST['new_user'];

    if (!empty($_POST['existing_task'])) {
        $title = $_POST['existing_task'];
        $filtered_tasks = array_filter($default_tasks, fn($task) => $task['title'] === $title);
        $description = !empty($filtered_tasks) ? reset($filtered_tasks)['description'] : 'Descripción no disponible';
    } else {
        $title = $_POST['new_task_title'];
        $description = $_POST['new_task_description'];
    }

    $result = create_task($user_id, $title, $description);

    if ($result['code'] == 201) {
        echo "<p style='color: green;'>Tarea agregada con éxito.</p>";
    } else {
        echo "<p style='color: red;'>Error al agregar la tarea.</p>";
    }
}

// Obtener todos los usuarios y tareas para mostrarlos en la tabla
$all_tasks = get_all_users_tasks();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Tareas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Agenda de Tareas</h1>
    
    <form method="post" action="index.php">
        <label for="existing_user">Seleccionar usuario existente:</label>
        <select id="existing_user" name="existing_user">
            <option value="">Nuevo usuario</option>
            <?php foreach ($all_users as $user): ?>
                <option value="<?php echo htmlspecialchars($user['user_id']); ?>">
                    <?php echo htmlspecialchars($user['user_id']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>

        <label for="new_user">O agregar nuevo usuario:</label>
        <input type="text" id="new_user" name="new_user">

        <br><br>

        <label for="existing_task">Seleccionar tarea predeterminada:</label>
        <select id="existing_task" name="existing_task">
            <option value="">Nueva tarea</option>
            <?php foreach ($default_tasks as $task): ?>
                <option value="<?php echo htmlspecialchars($task['title']); ?>">
                    <?php echo htmlspecialchars($task['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>

        <label for="new_task_title">O agregar nueva tarea:</label>
        <input type="text" id="new_task_title" name="new_task_title" placeholder="Título de la nueva tarea">
        <textarea id="new_task_description" name="new_task_description" placeholder="Descripción de la nueva tarea"></textarea>

        <br><br>

        <button type="submit">Agregar Tarea</button>
    </form>

    <h2>Lista de Tareas por Usuario</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Título de la Tarea</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha de Creación</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $current_user = null;
            if (!empty($all_tasks['body']['rows'])):
                foreach ($all_tasks['body']['rows'] as $task):
                    if (isset($task['doc']['user_id'])):
                        $show_user = ($current_user !== $task['doc']['user_id']);
                        $current_user = $task['doc']['user_id'];
                        ?>
                        <tr>
                            <?php if ($show_user): ?>
                                <td rowspan="<?php echo count(array_filter($all_tasks['body']['rows'], fn($t) => $t['doc']['user_id'] == $current_user)); ?>">
                                    <?php echo htmlspecialchars($task['doc']['user_id']); ?>
                                </td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($task['doc']['title']); ?></td>
                            <td><?php echo htmlspecialchars($task['doc']['description']); ?></td>
                            <td><?php echo $task['doc']['status'] == 1 ? 'Visible' : 'Oculto'; ?></td>
                            <td><?php echo htmlspecialchars($task['doc']['created_at']); ?></td>
                        </tr>
                    <?php
                    endif;
                endforeach;
            else:
                ?>
                <tr>
                    <td colspan="5">No hay tareas para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
