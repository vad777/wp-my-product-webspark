<?php
if (!defined('ABSPATH')) {
    exit;
}


function wpmpw_add_product_endpoint() {
    if(class_exists('WooCommerce')){
        add_rewrite_endpoint('add-product', EP_ROOT | EP_PAGES);
        flush_rewrite_rules();
    }
}

add_action('init', 'wpmpw_add_product_endpoint');

function wpmpw_add_product_content() {
    if (!is_user_logged_in()) {
        echo '<p>You must be logged in to add a product.</p>';
        return;
    }

    ?>
    <form class="wpmpw-form" method="post" enctype="multipart/form-data">
        <p>
            <label for="product_name">Product name</label>
            <input type="text" name="product_name" required>
        </p>
        <p>
            <label for="product_price">Price</label>
            <input type="number" name="product_price" step="0.01" required>
        </p>
        <p>
            <label for="product_quantity">Quantity</label>
            <input type="number" name="product_quantity" required>
        </p>
        <p>
            <label for="product_description">Description</label>
            <?php wp_editor('', 'product_description'); ?>
        </p>
        <p>
            <label for="product_image">Product image</label>
            <input type="file" name="product_image">
        </p>
        <p>
            <input type="submit" name="wpmpw_add_product" value="Add product">
        </p>
    </form>
    <?php

    if (isset($_POST['wpmpw_add_product'])) {
        wpmpw_handle_product_submission();
    }
}
add_action('woocommerce_account_add-product_endpoint', 'wpmpw_add_product_content');


function wpmpw_handle_product_submission() {
    if (!is_user_logged_in()) return;

    $user_id = get_current_user_id();
    $product_name = sanitize_text_field($_POST['product_name']);
    $product_price = floatval($_POST['product_price']);
    $product_quantity = intval($_POST['product_quantity']);
    $product_description = wp_kses_post($_POST['product_description']);

    $product_data = array(
        'post_title'    => $product_name,
        'post_content'  => $product_description,
        'post_status'   => 'pending',
        'post_type'     => 'product',
        'post_author'   => $user_id,
    );

    $product_id = wp_insert_post($product_data);
    if ($product_id) {
        update_post_meta($product_id, '_price', $product_price);
        update_post_meta($product_id, '_stock', $product_quantity);
        wp_set_object_terms($product_id, 'simple', 'product_type');


        if (!empty($_FILES['product_image']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $attachment_id = media_handle_upload('product_image', $product_id);
            if (!is_wp_error($attachment_id)) {
                set_post_thumbnail($product_id, $attachment_id);
            }
        }

        echo '<p>The product has been successfully added and is awaiting moderation.</p>';
        wpmpw_send_admin_email($product_id);
    }
}
