<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Email')) {
    include_once WC_ABSPATH . 'includes/emails/class-wc-email.php';
}

class WPMPW_Admin_Email extends WC_Email {

    public function __construct() {
        $this->id             = 'wpmpw_admin_email';
        $this->title          = __('New Product Notification', 'wpmpw');
        $this->description    = __('Sends an email to the admin when a new product is created.', 'wpmpw');

        $this->heading        = __('New Product Submitted', 'wpmpw');
        $this->subject        = __('New Product: {product_title}', 'wpmpw');

        $this->recipient      = get_option('admin_email');

        $this->template_html  = 'emails/admin-new-product.php';
        $this->template_plain = 'emails/plain/admin-new-product.php';

        parent::__construct();
    }

    public function trigger($product_id) {
        if (!$this->is_enabled() || !$this->get_recipient()) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) return;

        $this->placeholders = array(
            '{product_title}' => $product->get_title(),
            '{product_link}'  => admin_url("post.php?post={$product_id}&action=edit"),
        );

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    public function get_content_html() {
        return wc_get_template_html($this->template_html, array(
            'email_heading' => $this->get_heading(),
            'product_title' => $this->placeholders['{product_title}'],
            'product_link'  => $this->placeholders['{product_link}'],
            'email'         => $this,
        ));
    }

    public function get_content_plain() {
        return wc_get_template_html($this->template_plain, array(
            'email_heading' => $this->get_heading(),
            'product_title' => $this->placeholders['{product_title}'],
            'product_link'  => $this->placeholders['{product_link}'],
            'email'         => $this,
        ));
    }
}
