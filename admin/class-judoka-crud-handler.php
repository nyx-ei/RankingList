<?php

if (!defined('ABSPATH')) exit;

class Judoka_CRUD_Handler
{
    private $judoka_model;
    private $competition_model;
    private $file_handler;

    public function __construct()
    {
        $this->judoka_model = new Judoka_Model();
        $this->competition_model = new Competition_Model();
        $this->file_handler = new Judoka_File_Handler();
    }

    public function handle_add()
    {
        if (!wp_verify_nonce($_POST['judoka_nonce'], 'add_judoka_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        if ($this->judoka_model->judoka_exists($_POST['full_name'], $_POST['birth_date'])) {
            wp_send_json_error('This judoka already exists!');
            return;
        }

        $judoka_data = $this->prepare_judoka_data();
        $judoka_id = $this->judoka_model->create_judoka($judoka_data);

        if ($judoka_id) {
            $this->updateOrCreateCompetitions($judoka_id);
            wp_send_json_success('Judoka successfully added');
        } else {
            wp_send_json_error('Error adding judoka');
        }
    }

    public function handle_edit()
    {
        if (!$this->verify_edit_request()) {
            return;
        }

        $judoka_id = intval($_POST['judoka_id']);
        $judoka_data = $this->prepare_judoka_data($judoka_id);

        $judoka_update = $this->judoka_model->update_judoka($judoka_id, $judoka_data);

        if ($judoka_update !== false) {
            $this->updateOrCreateCompetitions($judoka_id);
            wp_send_json_success('Judoka successfully updated');
        } else {
            wp_send_json_error('Error updating judoka');
        }
    }

    public function handle_delete() {
        if (!$this->verify_delete_request()) {
            return;
        }

        $judoka_id = intval($_POST['judoka_id']);
        $judoka = $this->judoka_model->get_judoka($judoka_id);

        $delete_competitions = $this->competition_model->delete_by_judoka($judoka_id);

        if ($delete_competitions !== false && $this->judoka_model->delete_judoka($judoka_id)) {
            wp_send_json_success('Judoka successfully deleted!');
        } else {
            wp_send_json_error('Error deleting judoka');
        }
    }

    private function updateOrCreateCompetitions($judoka_id)
    {
        if (isset($_POST['competitions']) && is_array($_POST['competitions'])) {
            foreach ($_POST['competitions'] as $competition) {
                $competition_data = [
                    'judoka_id' => $judoka_id,
                    'competition_name' => $competition['competition_name'],
                    'date_competition' => $competition['date_competition'],
                    'points' => $competition['points'],
                    'rang' => $competition['rang'],
                    'medals' => $competition['medals']
                ];

                if (isset($competition['id']) && !empty($competition['id'])) {
                    $this->competition_model->update($competition['id'], $competition_data);
                } else {
                    $this->competition_model->create($competition_data);
                }
            }
        }
    }

    private function prepare_judoka_data($judoka_id = null)
    {
        if ($judoka_id && empty($_FILES['photo_profile']['name'])) {
            $photo_url = $_POST['old_photo_profile'] ?? '';
        } else {
            $photo_url = $this->file_handler->handle_profile_photo($judoka_id);
        }
    
        if ($judoka_id && empty($_FILES['images']['name'][0])) {
            $images_urls = explode(',', $_POST['old_images'] ?? '');
        } else {
            $images_urls = $this->file_handler->handle_gallery_images($judoka_id);
        }
    
        return [
            'full_name' => $_POST['full_name'],
            'birth_date' => $_POST['birth_date'],
            'category' => $_POST['category'],
            'weight' => $_POST['weight'],
            'club' => $_POST['club'],
            'grade' => $_POST['grade'],
            'gender' => $_POST['gender'],
            'photo_profile' => $photo_url,
            'images' => json_encode($images_urls)
        ];
    }

    private function verify_edit_request()
    {
        if (!isset($_POST['judoka_edit_nonce']) || !wp_verify_nonce($_POST['judoka_edit_nonce'], 'edit_judoka_nonce')) {
            wp_send_json_error('Invalid nonce');
            return false;
        }

        if (!isset($_POST['judoka_id']) || empty($_POST['judoka_id'])) {
            wp_send_json_error('Judoka ID is missing');
            return false;
        }
        return true;
    }

    private function verify_delete_request()
    {
        if (!isset($_POST['judoka_delete_nonce']) || !wp_verify_nonce($_POST['judoka_delete_nonce'], 'delete_judoka_nonce')) {
            wp_send_json_error('Invalid nonce');
            return false;
        }

        if (!isset($_POST['judoka_id']) || empty($_POST['judoka_id'])) {
            wp_send_json_error('Judoka ID is missing');
            return false;
        }
        return true;
    }

}
