<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpmpw_register_custom_email($email_classes) {
    require_once __DIR__ . '/class-wpmpw-admin-email.php';
    if (class_exists('WPMPW_Admin_Email')) {
        $email_classes['WPMPW_Admin_Email'] = new WPMPW_Admin_Email();
        return $email_classes;
    }
    return $email_classes;
}
add_filter('woocommerce_email_classes', 'wpmpw_register_custom_email');


function wpmpw_send_admin_email($product_id) {

    $email = WC()->mailer()->emails['WPMPW_Admin_Email'] ?? null;

    if ($email && $email->is_enabled()) {
        $email->trigger($product_id);
    }
}
add_action('wpmpw_send_admin_email', 'wpmpw_send_admin_email');
