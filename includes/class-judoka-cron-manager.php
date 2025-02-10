<?php

declare(strict_types=1);

class Judoka_Cron_Manager
{
    private static ?Judoka_Cron_Manager $instance = null;

    private static Judoka_Ranking_Shortcode $ranking_Shortcode;

    private const CRON_HOOK = 'store_judoka_rankings';

    private function __construct()
    {
        $this->ranking_Shortcode = new Judoka_Ranking_Shortcode();
        $this->init_hooks();
    }

    private function __clone(): void {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    private function init_hooks(): void
    {
        add_action(self::CRON_HOOK, [$this,'store_rankings']);
    }

    public static function get_instance(): Judoka_Cron_Manager
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setup_cron(): bool {
        try {
            if (!wp_next_scheduled(self::CRON_HOOK)) {
                // Schedule for midnight
                $midnight = strtotime('tomorrow midnight');
                $scheduled = wp_schedule_event($midnight, 'daily', self::CRON_HOOK);

                if ($scheduled === false) {
                    error_log('Failed to schedule Judoka rankings cron job');
                    return false;
                }

                error_log('Successfully scheduled Judoka rankings cron job');
                return true;
            }
            return true;
        } catch (Exception $e) {
            error_log('Error setting up Judoka rankings cron: ' . $e->getMessage());
            return false;
        }
    }

    public function remove_cron(): bool {
        try {
            $removed = wp_clear_scheduled_hook(self::CRON_HOOK);

            if ($removed === false) {
                error_log('Failed to remove Judoka rankings cron job');
                return false;
            }

            error_log('Successfully removed Judoka rankings cron job');
            return true;
        } catch (Exception $e) {
            error_log('Error removing Judoka rankings cron: ' . $e->getMessage());
            return false;
        }
    }

    public function store_rankings(): void {
        try {
            error_log('Starting Judoka rankings storage job at ' . current_time('Y-m-d H:i:s'));

            $result = $this->ranking_shortcode->store_current_rankings();

            if ($result) {
                error_log('Successfully stored Judoka rankings');
            } else {
                error_log('Failed to store Judoka rankings');
            }

        } catch (Exception $e) {
            error_log('Error during Judoka rankings storage: ' . $e->getMessage());
        }
    }

    public function is_cron_scheduled(): bool {
        return wp_next_scheduled(self::CRON_HOOK) !== false;
    }

    public function get_next_scheduled_time() {
        return wp_next_scheduled(self::CRON_HOOK);
    }
}