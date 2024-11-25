<?php

if (!defined('ABSPATH')) exit;

class Judoka_Import_Handler {
    private $judoka_model;

    public function __construct() {
        $this->judoka_model = new Judoka_Model();
    }

    public function handle_import() {
        if (!$this->verify_import_request()) {
            return;
        }

        try {
            $import_count = $this->import_csv($_FILES['judoka_import_file']['tmp_name']);
            wp_send_json_success([
                'message' => sprintf('%d judokas successfully imported', $import_count),
                'count' => $import_count
            ]);
        } catch (Exception $e) {
            wp_send_json_error('Import error: ' . $e->getMessage());
        }
    }

    private function verify_import_request() {
        if (!wp_verify_nonce($_POST['judoka_import_nonce'], 'import_judoka_nonce')) {
            wp_send_json_error('Invalid nonce');
            return false;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return false;
        }

        if (!isset($_FILES['judoka_import_file']) || $_FILES['judoka_import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('No file uploaded or upload error');
            return false;
        }

        $file_ext = strtolower(pathinfo($_FILES['judoka_import_file']['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            wp_send_json_error('Invalid file format. Please upload a CSV file.');
            return false;
        }

        return true;
    }

    private function import_csv($file_path) {
        if (($handle = fopen($file_path, "r")) === FALSE) {
            throw new Exception('Unable to open file');
        }

        $headers = fgetcsv($handle, 0, ",");
        if (!$headers) {
            fclose($handle);
            throw new Exception('Invalid CSV format: no headers found');
        }

        $required_headers = ['full_name', 'birth_date', 'category', 'weight', 'club', 'grade', 'gender'];
        $headers = array_map('trim', array_map('strtolower', $headers));
        $missing_headers = array_diff($required_headers, $headers);

        if (!empty($missing_headers)) {
            fclose($handle);
            throw new Exception('Missing required columns: ' . implode(', ', $missing_headers));
        }

        $column_indexes = array_flip($headers);
        $import_count = 0;

        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            try {
                $judoka_data = $this->prepare_judoka_data($data, $column_indexes);
                if ($this->judoka_model->create_judoka($judoka_data)) {
                    $import_count++;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        fclose($handle);
        return $import_count;
    }

    private function prepare_judoka_data($data, $column_indexes) {
        return [
            'full_name' => Judoka::formatFullName(trim($data[$column_indexes['full_name']])),
            'birth_date' => trim($data[$column_indexes['birth_date']]),
            'category' => trim($data[$column_indexes['category']]),
            'weight' => floatval(trim($data[$column_indexes['weight']])),
            'club' => trim($data[$column_indexes['club']]),
            'grade' => trim($data[$column_indexes['grade']]),
            'gender' => trim($data[$column_indexes['gender']])
        ];
    }
}
