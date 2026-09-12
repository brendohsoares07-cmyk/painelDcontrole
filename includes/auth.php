<?php
/**
 * Verifica se o usuário está logado.
 * Deve ser incluído no topo de toda página que exige login.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . 'login/login.php');
    exit;
}
