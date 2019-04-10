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
        foreach ($xmlDefintion->action as $action) {
            $item = [
                'name' => $keymapName,
                'parent' => $keymapParent ?? null
            ];
            $item['id'] = (string) $action['id'];
            if ($action->{'keyboard-shortcut'}) {
                $item['shortcut'] = (string) $action->{'keyboard-shortcut'}['first-keystroke'];
            } elseif ($action->{'mouse-shortcut'}) {
                $item['shortcut'] = strtolower((string) $action->{'keyboard-shortcut'}['keystroke']);
            }

            $stmt = $this->db->prepare('INSERT INTO shortcuts VALUES (:id, :shortcut, :name, :parent)');
            $stmt->execute($item);
            if ($i++ > 100) {
                return;
            }
        }
    }
}
