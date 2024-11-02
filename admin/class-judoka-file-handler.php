<?php

if (!defined('ABSPATH')) exit;

class Judoka_File_Handler {
    public function handle_profile_photo($judoka_id = null) {
        $photo_url = '';
        if (isset($_FILES['photo_profile'])) {
            $upload = wp_handle_upload($_FILES['photo_profile'], ['test_form' => false]);
            if (!isset($upload['error'])) {
                $photo_url = $upload['url'];
            }
        }
        return $photo_url;
    }

    public function handle_gallery_images($judoka_id = null) {
        $images_urls = [];
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $_FILES['images']['tmp_name'][$key],
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key],
                    ];
                    $upload = wp_handle_upload($file, ['test_form' => false]);
                    if (!isset($upload['error'])) {
                        $images_urls[] = $upload['url'];
                    }
                }
            }
        }
        return $images_urls;
    }

    public function delete_files($judoka) {
        if (!empty($judoka->photo_profile)) {
            $this->delete_uploaded_file($judoka->photo_profile);
        }

        if (!empty($judoka->images)) {
            $images = json_decode($judoka->images, true);
            if (is_array($images)) {
                foreach ($images as $image_url) {
                    $this->delete_uploaded_file($image_url);
                }
            }
        }
    }

    private function delete_uploaded_file($file_url) {
        $upload_dir = wp_upload_dir();
        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_url);

        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
}
