<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpmpw_send_admin_email($product_id) {
    $admin_email = get_option('admin_email');
    $product = get_post($product_id);
    $product_link = get_edit_post_link($product_id);
    $author_link = admin_url("user-edit.php?user_id={$product->post_author}");

    $subject = "New product is awaiting moderation";
    $message = "Title: {$product->post_title}\n\n";
    $message .= "Author page: {$author_link}\n";
    $message .= "Edit product: {$product_link}";

    wp_mail($admin_email, $subject, $message);
}
