<?php

declare(strict_types = 1);

class KeymapXmlParser
{

    /** @var PDO */
    private $db;

    public function __construct(
        PDO $db
    )
    {
        $this->db = $db;
    }

    public function parseXmlDefinition(SimpleXMLElement $xmlDefintion): void
    {
        $i = 0;

        $keymapName = (string) $xmlDefintion['name'];
        if ($xmlDefintion['parent']) {
            $keymapParent = (string) $xmlDefintion['parent'];
        }
        $stmt = $this->db->prepare('INSERT INTO keymaps VALUES (:name, :parent)');
        $stmt->execute(
            [
                'name' => $keymapName,
                'parent' => $keymapParent ?? null,
            ]
        );
        foreach ($xmlDefintion->action as $action) {
            $item = [
                'name' => $keymapName,
            ];
            $item['id'] = (string) $action['id'];
            if ($action->{'keyboard-shortcut'}) {
                $item['shortcut'] = $this->shortcutSort((string) $action->{'keyboard-shortcut'}['first-keystroke']);
            } elseif ($action->{'mouse-shortcut'}) {
                $item['shortcut'] = strtolower((string) $action->{'keyboard-shortcut'}['keystroke']);
            }

            $stmt = $this->db->prepare('INSERT INTO shortcuts VALUES (:id, :shortcut, :name)');
            $stmt->execute($item);
            if ($i++ > 100) {
                return;
            }
        }
    }

    private function shortcutSort(string $shortcut)
    {
        $shortcut = strtolower($shortcut);
        $from = $shortcut;
        $keyorder = [
            'control' => 1,
            'alt' => 2,
            'altgr' => 2,
            'shift' => 3,
        ];
        $keys = explode(' ', $shortcut);
        usort($keys, function ($key1, $key2) use ($keyorder) {
            $key1Val = $key2Val = 999;
            if (array_key_exists($key1, $keyorder)) {
                $key1Val = $keyorder[$key1];
            }
            if (array_key_exists($key2, $keyorder)) {
                $key2Val = $keyorder[$key2];
            }
            return $key1Val <=> $key2Val;
        });
        $to = implode(' ', $keys);
        if ($from !== $to) {
            echo sprintf('From %s'.PHP_EOL.'  to %s' . PHP_EOL, $from, $to);
        }
        return $to;
    }
}
