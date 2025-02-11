<?php

namespace FormGenerator;

use PDO;

class FormGenerator
{
    private $pdo;
    private $config;
    private $selectFields;
    private $multiSelectFields;
    private $select2Fields; // Store Select2 fields

    public function __construct(PDO $pdo, array $selectFields = [], array $multiSelectFields = [], array $select2Fields = [])
    {
        $this->pdo = $pdo;
        $this->config = require __DIR__ . '/../config/config.php'; // Load config file
        $this->selectFields = $selectFields;
        $this->multiSelectFields = $multiSelectFields;
        $this->select2Fields = $select2Fields; // Allow Select2 fields dynamically
    }

    public function generateForm($table, $numColumns = 2, $oldValues = [])
    {
        $query = $this->pdo->prepare("
            SELECT COLUMN_NAME as Field, COLUMN_TYPE as Type, COLUMN_COMMENT as Comment
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = :table
        ");
        $query->execute(['table' => $table]);
        $schema = $query->fetchAll(PDO::FETCH_ASSOC);

        $colWidth = 12 / max(1, min($numColumns, 12));
        $formHtml = '<form method="POST" action="save.php" class="p-4 border rounded" id="tables_data">';
        $formHtml .= '<div class="row">';

        foreach ($schema as $field) {
            $fieldName = $field['Field'];
            $fieldType = strtolower($field['Type']);

            // Skip disabled fields
            if (in_array($fieldName, $this->config['disabled_fields'])) {
                continue;
            }

            // Custom field labels & placeholders
            $fieldLabel = $this->config['form_labels'][$fieldName] ?? ucfirst(str_replace('_', ' ', $fieldName));
            $placeholder = $this->config['field_placeholders'][$fieldName] ?? '';

            $oldValue = isset($oldValues[$fieldName]) ? htmlspecialchars($oldValues[$fieldName], ENT_QUOTES, 'UTF-8') : '';

            // Determine Select Fields
            $select2Class = in_array($fieldName, $this->select2Fields) ? 'select2' : ''; // Check if Select2 should be applied
            if (isset($this->multiSelectFields[$fieldName])) {
                [$sourceTable, $idColumn, $nameColumn] = $this->multiSelectFields[$fieldName];
                $options = $this->fetchSelectOptions($sourceTable, $idColumn, $nameColumn);
                $inputType = 'multiselect';
            } elseif (isset($this->selectFields[$fieldName])) {
                [$sourceTable, $idColumn, $nameColumn] = $this->selectFields[$fieldName];
                $options = $this->fetchSelectOptions($sourceTable, $idColumn, $nameColumn);
                $inputType = 'select';
            } elseif (preg_match('/enum\((.*)\)/', $fieldType, $matches)) {
                $options = str_replace("'", "", explode(",", $matches[1]));
                $inputType = 'select';
            } else {
                $inputType = 'text';
                if (preg_match('/int|double|float|decimal/', $fieldType)) {
                    $inputType = 'number';
                } elseif (preg_match('/text/', $fieldType)) {
                    $inputType = 'textarea';
                } elseif (preg_match('/date/', $fieldType)) {
                    $inputType = 'date';
                }
            }

            $readonlyAttr = in_array($fieldName, $this->config['readonly_fields']) ? 'readonly' : '';

            $formHtml .= '<div class="col-md-' . $colWidth . '">';
            $formHtml .= '<div class="form-group">';
            $formHtml .= '<label for="' . $fieldName . '">' . $fieldLabel . '</label>';

            // Multi-Select Dropdown
            if ($inputType == 'multiselect') {
                $selectedValues = explode(',', $oldValue);
                $formHtml .= '<select class="form-control ' . $select2Class . '" name="' . $fieldName . '[]" id="' . $fieldName . '" multiple>';
                foreach ($options as $key => $value) {
                    $selected = in_array($key, $selectedValues) ? 'selected' : '';
                    $formHtml .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($value) . '</option>';
                }
                $formHtml .= '</select>';
            }
            // Single Select Dropdown
            elseif ($inputType == 'select') {
                $formHtml .= '<select class="form-control ' . $select2Class . '" name="' . $fieldName . '" id="' . $fieldName . '">';
                foreach ($options as $key => $value) {
                    $selected = ($oldValue == $key) ? 'selected' : '';
                    $formHtml .= '<option value="' . $key . '" ' . $selected . '>' . ucfirst($value) . '</option>';
                }
                $formHtml .= '</select>';
            }
            // Textarea
            elseif ($inputType == 'textarea') {
                $formHtml .= '<textarea class="form-control" name="' . $fieldName . '" id="' . $fieldName . '" placeholder="' . $placeholder . '" ' . $readonlyAttr . '>' . $oldValue . '</textarea>';
            }
            // Input Field
            else {
                $formHtml .= '<input type="' . $inputType . '" class="form-control" name="' . $fieldName . '" id="' . $fieldName . '" placeholder="' . $placeholder . '" value="' . $oldValue . '" ' . $readonlyAttr . '>';
            }

            $formHtml .= '</div>';
            $formHtml .= '</div>';
        }

        $formHtml .= '</div>';
        $formHtml .= '<button type="submit" class="btn btn-primary mt-3">Save</button>';
        $formHtml .= '</form>';

        // Include Select2 JavaScript & CSS
        $formHtml .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">';
        $formHtml .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>';
        $formHtml .= '<script>
            $(document).ready(function() {
                $(".select2").select2();
            });
        </script>';

        return $formHtml;
    }

    private function fetchSelectOptions($table, $idColumn, $nameColumn)
    {
        $query = $this->pdo->prepare("SELECT `$idColumn`, `$nameColumn` FROM `$table` ORDER BY `$nameColumn` ASC");
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $options = [];
        foreach ($results as $row) {
            $options[$row[$idColumn]] = $row[$nameColumn];
        }
        return $options;
    }
}
