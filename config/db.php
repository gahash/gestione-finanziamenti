<?php
/**
 * Connessione al database.
 *
 * Le credenziali NON sono scritte nel codice: vengono lette dalle variabili
 * d'ambiente (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET).
 * In alternativa, copia `.env.example` in `.env` nella root del progetto
 * e valorizza i parametri: il file `.env` e' escluso dal versionamento.
 */

$envFile = __DIR__ . '/../.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if (getenv($key) === false) {
            putenv("$key=$value");
        }
    }
}

$host    = getenv('DB_HOST') ?: 'localhost';
$db      = getenv('DB_NAME') ?: '';
$user    = getenv('DB_USER') ?: '';
$pass    = getenv('DB_PASS') ?: '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log('Errore connessione DB: ' . $e->getMessage());
    http_response_code(500);
    die('Errore di connessione al database.');
}
