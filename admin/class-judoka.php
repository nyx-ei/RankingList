<?php

if (!defined('ABSPATH')) exit;

class JUDOKA {
    public static function formatFullName($fullname) {
       $formatted = trim(preg_replace('/\s+/',' ', $fullname));

       $formatted = preg_replace_callback(
        '/\b[a-zA-ZÀ-ÖØ-öø-ÿ\'\-]+\b/u',
        function ($matches) {
            return mb_convert_case($matches[0], MB_CASE_TITLE, "UTF-8");
        },
        $formatted
    );

       return $formatted;
    }
}