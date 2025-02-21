<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpmpw_register_endpoint() {
    add_rewrite_endpoint('my-products', EP_ROOT | EP_PAGES);
}
add_action('init', 'wpmpw_register_endpoint');


function get_correct_paged() {
    global $wp_query;

    if (!empty($wp_query->query_vars['paged']) && is_numeric($wp_query->query_vars['paged'])) {
        return absint($wp_query->query_vars['paged']);
    }

    $paged = get_query_var('paged');
    if (!empty($paged) && is_numeric($paged)) {
        return absint($paged);
    }

    if (preg_match('/my-products\/page\/([0-9]+)/', $_SERVER['REQUEST_URI'], $matches)) {
        return absint($matches[1]);
    }

    return 1;
}


function wpmpw_my_products_content() {
    if (!is_user_logged_in()) {
        echo '<p>You must be logged in to view products.</p>';
        return;
    }

    $user_id = get_current_user_id();
    $products_per_page = 5;


    $paged = get_correct_paged();
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $products_per_page,
        'paged'          => $paged,
        'author'         => $user_id
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<table class="wpmpw-table">';
        echo '<thead><tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
              </tr></thead>';
        echo '<tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $product = wc_get_product($product_id);
            $edit_link = wc_get_account_endpoint_url('add-product') . '?product_id=' . $product_id;
            $delete_link = '?delete_product=' . $product_id;

            echo '<tr>';
            echo '<td>' . get_the_post_thumbnail($product_id, 'thumbnail') . '</td>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>' . wc_price($product->get_price()) . '</td>';
            echo '<td>' . $product->get_stock_quantity() . '</td>';
            echo '<td class="wpmpw-buttons">
                     <a href="' . $edit_link . '" class="edit">Edit</a>
                     <a href="' . $delete_link . '" class="delete" onclick="return confirm(\'Remove product?\');">Delete</a>
                   </td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            echo '<div class="pagination">';
            echo paginate_links([
                'base'    => esc_url(get_pagenum_link(1)) . '%_%',
                'format'  => 'page/%#%/',
                'current' => $paged,
                'total'   => $total_pages,
                'prev_text' => __('« Prev'),
                'next_text' => __('Next »'),
            ]);
            echo '</div>';
        }

        wp_reset_postdata();
    } else {
        echo '<p>У вас нет товаров.</p>';
    }
}
add_action('woocommerce_account_my-products_endpoint', 'wpmpw_my_products_content');
