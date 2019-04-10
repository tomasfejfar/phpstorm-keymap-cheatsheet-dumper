<?php

declare(strict_types = 1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'vendor/autoload.php';


$db = new PDO('sqlite:db.sqlite');
$db->exec('DROP TABLE IF EXISTS shortcuts');
$db->exec('
CREATE TABLE IF NOT EXISTS shortcuts (
    id VARCHAR(255), 
    shortcut VARCHAR(255), 
    keymapName VARCHAR(255) 
)
');
$db->exec('DROP TABLE IF EXISTS keymaps');
$db->exec('CREATE TABLE keymaps (
    name VARCHAR(255), 
    parent VARCHAR(255)
)');
$i = 0;

$keymapFiles = [
    __DIR__ . '/$default.xml',
    __DIR__ . '/Eclipse.xml',
    __DIR__ . '/Better_Eclipse.xml',
];

foreach ($keymapFiles as $file) {
    $keymapXml = simplexml_load_string(file_get_contents($file));
    (new KeymapXmlParser($db))->parseXmlDefinition($keymapXml);
    echo 'Loaded ' . $file . PHP_EOL;
}




