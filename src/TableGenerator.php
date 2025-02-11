<?php

namespace FormGenerator;

use PDO;

class TableGenerator
{
    private $pdo;
    private $config;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->config = require __DIR__ . '/../config/config.php'; // Load config
    }

    public function generateTable($table)
    {
        $query = $this->pdo->prepare("SHOW COLUMNS FROM " . $table);
        $query->execute();
        $schema = $query->fetchAll(PDO::FETCH_ASSOC);

        $dataQuery = $this->pdo->prepare("SELECT * FROM " . $table);
        $dataQuery->execute();
        $rows = $dataQuery->fetchAll(PDO::FETCH_ASSOC);

        $tableHtml = '<table id="data-table" class="table table-bordered">';
        $tableHtml .= '<thead><tr>';

        foreach ($schema as $field) {
            $fieldName = $field['Field'];
            if (in_array($fieldName, $this->config['disabled_fields'])) {
                continue;
            }

            $headerName = $this->config['table_header_names'][$fieldName] ?? ucfirst(str_replace('_', ' ', $fieldName));
            $tableHtml .= '<th>' . $headerName . '</th>';
        }

        $tableHtml .= '<th>Actions</th></tr></thead>';
        $tableHtml .= '<tbody>';

        foreach ($rows as $row) {
            $tableHtml .= '<tr>';
            foreach ($schema as $field) {
                $fieldName = $field['Field'];
                if (in_array($fieldName, $this->config['disabled_fields'])) {
                    continue;
                }

                $readonlyAttr = in_array($fieldName, $this->config['readonly_fields']) ? 'readonly' : '';

                $tableHtml .= '<td contenteditable="' . (!$readonlyAttr ? "true" : "false") . '">' . htmlspecialchars($row[$fieldName]) . '</td>';
            }

            $tableHtml .= '<td><button class="btn btn-sm btn-danger verify-button" data-id="' . $row['id'] . '" data-table="' . $table . '">Verify</button></td>';
            $tableHtml .= '</tr>';
        }

        $tableHtml .= '</tbody></table>';
        return $tableHtml;
    }
}
