<?php

class Judoka_Admin
{
    private $judoka_model;
    private $competition_model;
    private $config_menu;

    public function __construct()
    {
        $this->judoka_model = new Judoka_Model();
        $this->competition_model = new Competition_Model();
        $this->config_menu = JUDOKA_ADMIN_MENU_CONFIG;

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_add_judoka', array($this, 'handle_add_judoka'));
        add_action('wp_ajax_edit_judoka', array($this, 'handle_edit_judoka'));
    }

    public function add_admin_menu()
    {
        $menu = $this->config_menu['menu'];
        add_menu_page(
            $menu['page_title'],
            $menu['menu_title'],
            $menu['capability'],
            $menu['menu_slug'],
            array($this, 'display_judoka_list'),
            $menu['icon'],
        );

        $submenu = $this->config_menu['submenu']['add_judoka'];
        add_submenu_page(
            $menu['menu_slug'],
            $submenu['page_title'],
            $submenu['menu_title'],
            $submenu['capability'],
            $submenu['menu_slug'],
            array($this, 'display_add_judoka')
        );

        $submenu = $this->config_menu['submenu']['edit_judoka'];
        add_submenu_page(
            $menu['menu_slug'],
            $submenu['page_title'],
            '',
            $submenu['capability'],
            $submenu['menu_slug'],
            array($this, 'display_edit_judoka')
        );
    }

    public function enqueue_admin_scripts()
    {
        wp_enqueue_style('judoka-admin-css', JUDOKA_PLUGIN_URL . 'admin/css/judoka-admin.css');
        wp_enqueue_script('judoka-admin-js', JUDOKA_PLUGIN_URL . 'admin/js/judoka-admin.js', array('jquery'));
        wp_localize_script('judoka-admin-js', 'judokaAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'judoka_nonce' => wp_create_nonce('add_judoka_nonce'),
            'judoka_edit_nonce' => wp_create_nonce('edit_judoka_nonce'),
        ));
    }

    public function display_judoka_list()
    {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/list-judokas.php';
    }

    public function display_add_judoka()
    {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/add-judoka.php';
    }

    public function display_edit_judoka()
    {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/edit-judoka.php';
    }

    public function handle_add_judoka()
    {
        if (!wp_verify_nonce($_POST['judoka_nonce'], 'add_judoka_nonce')) {
            wp_send_json_error('Nonce invalide');
            return;
        }

        if ($this->judoka_model->judoka_exists($_POST['full_name'], $_POST['birth_date'])) {
            wp_send_json_error('This judoka already exists!');
        }

        $photo_url = '';
        if (isset($_FILES['photo_profile'])) {
            $upload = wp_handle_upload($_FILES['photo_profile'], array('test_form' => false));
            if (!isset($upload['error'])) {
                $photo_url = $upload['url'];
            }
        }

        $images_urls = array();
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $file = array(
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $_FILES['images']['tmp_name'][$key],
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key],
                    );
                    $upload = wp_handle_upload($file, array('test_form' => false));
                    if (isset($upload['error'])) {
                        error_log('Upload error: ' . $upload['error']);
                    } else {
                        $images_urls[] = $upload['url'];
                    }
                }
            }
        }

        $judoka_data = array(
            'full_name' => $_POST['full_name'],
            'birth_date' => $_POST['birth_date'],
            'category' => $_POST['category'],
            'weight' => $_POST['weight'],
            'club' => $_POST['club'],
            'grade' => $_POST['grade'],
            'gender' => $_POST['gender'],
            'photo_profile' => $photo_url,
            'images' => $images_urls
        );

        $judoka_id = $this->judoka_model->create_judoka($judoka_data);

        if ($judoka_id) {
            if (isset($_POST['competitions']) && is_array($_POST['competitions'])) {
                foreach ($_POST['competitions'] as $competition) {
                    $competition_data = array(
                        'judoka_id' => $judoka_id,
                        'competition_name' => $competition['competition_name'],
                        'date_competition' => $competition['date_competition'],
                        'points' => $competition['points'],
                        'rang' => $competition['rang'],
                        'medals' => $competition['medals']
                    );
                    $this->competition_model->create($competition_data);
                }
            }

            wp_send_json_success('Judoka successfully added');
        } else {
            wp_send_json_error('Error adding judoka');
        }
    }

    public function handle_edit_judoka()
    {
        if (!wp_verify_nonce($_POST['judoka_edit_nonce'], 'edit_judoka_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        if (!isset($_POST['judoka_id']) || empty($_POST['judoka_id'])) {
            wp_send_json_error('Judoka ID is required');
            return;
        }

        $judoka_id = intval($_POST['judoka_id']);
        $judoka_exits = $this->judoka_model->get_judoka($judoka_id);

        if (!$judoka_exits) {
            wp_send_json_error('Judoka not found');
            return;
        }

        $photo_url = $judoka_exits->photo_profile;
        if (isset($_FILES['photo_profile']) && !empty($_FILES['photo_profile']['tmp_name'])) {
            $upload = wp_handle_upload($_FILES['photo_profile'], array('test_form' => false));
            if (!isset($upload['error'])) {
                $photo_url = $upload['url'];
            }
        }

        $existing_images = !empty($judoka_exits->images) ? json_decode($judoka_exits->images, true):array();
        $images_urls = $existing_images;

        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $file = array(
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $_FILES['images']['tmp_name'][$key],
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key],
                    );
                    $upload = wp_handle_upload($file, array('test_form' => false));
                    if (!isset($upload['error'])) {
                        $images_urls[] = $upload['url'];
                    }
                }
            }
        }

        $judoka_data = array(
            'full_name' => $_POST['full_name'],
            'birth_date' => $_POST['birth_date'],
            'category' => $_POST['category'],
            'weight' => $_POST['weight'],
            'club' => $_POST['club'],
            'grade' => $_POST['grade'],
            'gender' => $_POST['gender'],
            'photo_profile' => $photo_url,
            'images' => json_encode($images_urls)
        );

        $judoka_update = $this->judoka_model->update_judoka($judoka_id, $judoka_data);

        if ($judoka_update !== false) {
            if (isset($_POST['competitions']) && is_array($_POST['competitions'])) {
                foreach ($_POST['competitions'] as $competition) {
                    $competition_data = array(
                        'judoka_id' => $judoka_id,
                        'competition_name' => $competition['competition_name'],
                        'date_competition' => $competition['date_competition'],
                        'points' => $competition['points'],
                        'rang' => $competition['rang'],
                        'medals' => $competition['medals']
                    );

                    if (isset($competition['id']) && !empty($competition['id'])) {
                        $this->competition_model->update($competition['id'], $competition_data);
                    } else {
                        $this->competition_model->create($competition_data);
                    }
                }

                if (isset($_POST['removed_competitions']) && is_array($_POST['removed_competitions'])) {
                    foreach ($_POST['removed_competitions'] as $competition_id) {
                        $this->competition_model->delete($competition_id);
                    }
                }
            }

            wp_send_json_success('Judoka successfully updated');
        } else {
            wp_send_json_error('Error updating judoka');
        }
    }
}
