<?php
// db.php: Configuración y funciones de interacción con CouchDB

// Configuración de CouchDB
define('COUCHDB_HOST', '127.0.0.1');
define('COUCHDB_PORT', '5984');
define('COUCHDB_DB', 'apaza-couch'); // Nombre de la base de datos
define('COUCHDB_USER', 'admin'); // Usuario
define('COUCHDB_PASSWORD', '1234'); // Contraseña

// Función para hacer solicitudes HTTP con autenticación
function couchdb_request($method, $url, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_USERPWD, COUCHDB_USER . ':' . COUCHDB_PASSWORD);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['body' => json_decode($response, true), 'code' => $code];
}

// Crear una tarea para un usuario
function create_task($user_id, $title, $description) {
    $document = [
        'user_id' => $user_id,
        'title' => $title,
        'description' => $description,
        'status' => 1, // 1 = visible
        'created_at' => date('Y-m-d H:i:s')
    ];

    $task_id = strtolower($user_id . '_' . str_replace(' ', '_', $title));
    return couchdb_request('PUT', "http://" . COUCHDB_HOST . ":" . COUCHDB_PORT . "/" . COUCHDB_DB . "/" . $task_id, $document);
}

// Obtener todas las tareas y sus usuarios
function get_all_users_tasks() {
    $url = "http://" . COUCHDB_HOST . ":" . COUCHDB_PORT . "/" . COUCHDB_DB . "/_all_docs?include_docs=true";
    return couchdb_request('GET', $url);
}
?>
