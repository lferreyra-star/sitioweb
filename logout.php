<?php
/**
 * Cierre de sesión (solo acepta POST con token CSRF).
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Auth;

if (!es_post()) {
    ir_a('index.php');
}

verificar_csrf();
Auth::olvidarRecuerdo();
Auth::cerrar();

// La sesión ya no existe: recrearla para guardar el mensaje flash.
session_start();
flash('info', 'Has cerrado la sesión correctamente.');

ir_a('index.php');
