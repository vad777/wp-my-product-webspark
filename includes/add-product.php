<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpmpw_add_product_endpoint() {
    if (class_exists('WooCommerce')) {
        add_rewrite_endpoint('add-product', EP_ROOT | EP_PAGES);
        flush_rewrite_rules();
    }
}
add_action('init', 'wpmpw_add_product_endpoint');

function wpmpw_add_product_content() {
    if (!is_user_logged_in()) {
        echo '<p>You must be logged in to add or edit a product..</p>';
        return;
    }

    $product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
    $is_edit = $product_id > 0;
    $product = $is_edit ? wc_get_product($product_id) : null;

    if ($is_edit && (!$product || get_post_field('post_author', $product_id) != get_current_user_id())) {
        echo '<p>Error: Product not found or you do not have permission to edit it.</p>';
        return;
    }

    $product_name = $is_edit ? $product->get_name() : '';
    $product_price = $is_edit ? $product->get_price() : '';
    $product_quantity = $is_edit ? get_post_meta($product_id, '_stock', true) : '';
    $product_description = $is_edit ? $product->get_description() : '';

    if(!$product_quantity){
        $product_quantity =1;
    }

    if (isset($_GET['updated']) && $_GET['updated'] == '1') {
        echo '<p style="color: green;">The product has been updated successfully!</p>';
    }
    ?>
    <h2 style="margin-top:0" class="edit-title"><?php echo $is_edit ? 'Edit product' : 'Add product'; ?></h2>

    <form class="wpmpw-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('wpmpw_save_product', 'wpmpw_nonce'); ?>

        <p>
            <label for="product_name">Product name</label>
            <input type="text" name="product_name" value="<?php echo esc_attr($product_name); ?>" required>
        </p>

        <div class="product_price_quantity" >
            <div style="width: 40%; margin-right: 20px">
                <label for="product_price">Price</label>
                <input type="number" name="product_price" value="<?php echo esc_attr($product_price); ?>" required>
            </div>
            <div style="width: 40%">
                <label for="product_quantity">Quantity</label>
                <div class="quantity-selector">
                    <button type="button" class="quantity-minus">−</button>
                    <input type="number" name="product_quantity" id="product_quantity" value="<?php echo esc_attr($product_quantity); ?>" min="1" required>
                    <button type="button" class="quantity-plus">+</button>
                </div>
            </div>
        </div>

        <p>
            <label for="product_description">Description</label>
            <?php wp_editor($product_description, 'product_description'); ?>
        </p>
        <p>
            <label for="product_image">Product image</label>
            <input type="file" name="product_image">
        </p>

        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <p>
            <input type="submit" name="wpmpw_save_product" value="<?php echo $is_edit ? 'Save changes' : 'Add product'; ?>">
        </p>
    </form>
    <?php
}

add_action('woocommerce_account_add-product_endpoint', 'wpmpw_add_product_content');

function wpmpw_handle_product_submission() {
    if (!is_user_logged_in()) return;


    if (isset($_POST['wpmpw_nonce']) && !wp_verify_nonce($_POST['wpmpw_nonce'], 'wpmpw_save_product')) {
        return;
    }

    $user_id = get_current_user_id();
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $is_edit = $product_id > 0;

    $product_name = isset($_POST['product_name']) ? sanitize_text_field($_POST['product_name']) : '';
    $product_price = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0;
    $product_quantity = isset($_POST['product_quantity']) ? intval($_POST['product_quantity']) : 1;
    $product_description = isset($_POST['product_description']) ? wp_kses_post($_POST['product_description']) : '';


    if ($is_edit) {
        $product_data = array(
            'ID'           => $product_id,
            'post_title'   => $product_name,
            'post_content' => $product_description,
        );
        wp_update_post($product_data);
    } else {
        $product_data = array(
            'post_title'    => $product_name,
            'post_content'  => $product_description,
            'post_status'   => 'pending',
            'post_type'     => 'product',
            'post_author'   => $user_id,
        );
        $product_id = wp_insert_post($product_data);
    }

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

        clean_post_cache($product_id);
        wp_cache_delete($product_id, 'posts');

        nocache_headers();

        do_action('wpmpw_send_admin_email', $product_id);

        wp_safe_redirect(add_query_arg(['product_id' => $product_id, 'updated' => '1'], wc_get_account_endpoint_url('add-product')));
        exit;
    }
}


add_action('template_redirect', 'wpmpw_handle_product_submission');
