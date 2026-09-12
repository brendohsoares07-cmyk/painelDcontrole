<?php
/**
 * Configurações gerais do sistema
 * - Inicia a sessão
 * - Define constantes globais
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Caminho base do projeto (detectado automaticamente, não depende do nome da pasta)
// config.php sempre fica em <raiz-do-projeto>/config/config.php, então dirname(__DIR__)
// é a raiz do projeto, independente de qual página chamou este arquivo.
$projetoRaiz   = str_replace('\\', '/', dirname(__DIR__));
$documentRoot  = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
$caminhoBase   = $documentRoot !== '' ? str_replace($documentRoot, '', $projetoRaiz) : '';
define('BASE_URL', ($caminhoBase !== '' ? $caminhoBase : '') . '/');

date_default_timezone_set('America/Sao_Paulo');

// Exibir erros durante o desenvolvimento (remover/alterar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
