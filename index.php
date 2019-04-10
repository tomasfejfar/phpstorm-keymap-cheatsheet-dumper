<?php

declare(strict_types = 1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'vendor/autoload.php';

$db = new PDO('sqlite:db.sqlite');
/*
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
*/
$hotkeys = [
    'alt left',
    'control d',
    'alt y',
    'ctrl right_parenthesis',
];
$stmt = $db->query('SELECT name, parent FROM keymaps');
$keymaps = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

/**
 * @param PDO $db
 * @param $hotkey
 * @param string $keymapName
 * @return array
 */
function fetchIdByKeymapAndKey(PDO $db, $hotkey, string $keymapName)
{
    $stmt = $db->prepare('SELECT id FROM shortcuts WHERE shortcut = :shortcut AND keymapName = :name');
    $stmt->execute(
        [
            'shortcut' => $hotkey,
            'name' => $keymapName,
        ]
    );
    return $stmt->fetchColumn();
}

function dumpKeyForKeymap($keymap, $keymapParents, $keyresult)
{
    if (array_key_exists($keymap, $keyresult)) {
        return $keyresult[$keymap] . PHP_EOL;
    }

    if (array_key_exists($keymap, $keymapParents)) {
        return dumpKeyForKeymap($keymapParents[$keymap], $keymapParents, $keyresult);
    }

    throw new \LogicException(
        'Should not happen ' . print_r(
            [
                $keymap,
                $keymapParents,
                $keyresult,
            ], true
        )
    );
}

foreach ($hotkeys as $hotkey) {
    $keymapName = 'ImprovedEclipse';

    $id = false;
    while ($id === false && $keymaps[$keymapName]) {
        $id = fetchIdByKeymapAndKey($db, $hotkey, $keymapName);
        $keymapName = $keymaps[$keymapName];
    }
    $stmt = $db->prepare('SELECT keymapName, shortcut FROM shortcuts WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $id = preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1 ', $id);
    echo PHP_EOL . $id . PHP_EOL;
    foreach (array_keys($keymaps) as $keymap) {
        echo $keymap . ':' . dumpKeyForKeymap($keymap, $keymaps, $rows);
    }
}




