<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = new Database();
$resultado = $db->fetchOne('SELECT 1 + 1 AS suma');

echo "Conexión OK. 1 + 1 = " . $resultado['suma'];