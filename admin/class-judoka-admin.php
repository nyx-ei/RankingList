<?php

class Judoka_Admin {
    private $judoka_model;
    private $competition_model;

    public function __construct()
    {
        $this->judoka_model = new Judoka_Model();
        $this->competition_model = new Competition_Model();

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_ajouter_judoka', array($this, 'handle_add_judoka'));
        add_action('wp_ajax_modifier_judoka', array($this, 'handle_edit_judoka'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Judokas Management',
            'Judokas',
            'manage_options',
            'judokas-management',
            array($this, 'display_judoka_list'),
            'dashicons-groups'
        );

        add_submenu_page(
            'judokas-management',
            'Add a Judoka',
            'Add a Judoka',
            'manage_options',
            'add-judoka',
            array($this, 'display_add_judoka')
        );
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_style('judoka-admin-css', JUDOKA_PLUGIN_URL . 'admin/css/judoka-admin.css');
        wp_enqueue_script('judoka-admin-js', JUDOKA_PLUGIN_URL . 'admin/js/judoka-admin.js', array('jquery'));
        wp_localize_script('judoka-admin-js', 'judokaAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    public function display_judoka_list() {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/list-judokas.php';
    }

    public function display_add_judoka() {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/add-judoka.php';
    }

    public function handle_add_judoka() {
        if (!wp_verify_nonce($_POST['judoka_nonce'], 'ajouter_judoka_nonce')) {
            wp_send_json_error('Nonce invalide');
        }

        if ($this->judoka_model->exists($_POST['nom_complet'], $_POST['date_naissance'])) {
            wp_send_json_error('Ce judoka existe déjà');
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
                $upload = wp_handle_upload($_FILES['images'], array('test_form' => false));
                if (!isset($upload['error'])) {
                    $images_urls[] = $upload['url'];
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

        
        $judoka_id = $this->judoka_model->create($judoka_data);

        if ($judoka_id) {
            
            if (!empty($_POST['competition_name'])) {
                $competition_data = array(
                    'judoka_id' => $judoka_id,
                    'competition_name' => $_POST['competition_name'],
                    'date_competition' => $_POST['date_competition'],
                    'points' => $_POST['points'],
                    'rang' => $_POST['rang'],
                    'medals' => $_POST['medals']
                );
                $this->competition_model->create($competition_data);
            }
            wp_send_json_success('Judoka successfully added');
        } else {
            wp_send_json_error('Error adding judoka');
        }
    }
}
