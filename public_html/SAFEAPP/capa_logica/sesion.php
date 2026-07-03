<?php

// Deny-by-default: require_once this before any other require in a
// procesos/*.php endpoint, so no endpoint logic runs for a request that
// has no session.
//
// Only checks that a session exists ($_SESSION['idusu']). Role and menu
// permissions (acceso/rol) are a separate concern; endpoints that need
// finer-grained authorization must still add it themselves.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['idusu'])) {
    header('HTTP/1.1 403');
    echo json_encode(array('status' => 'error', 'message' => 'No autorizado'));
    exit();
}
