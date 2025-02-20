<?php
if (!defined('ABSPATH')) {
    exit;
}


function wpmpw_my_products_endpoint() {
    add_rewrite_endpoint('my-products', EP_ROOT | EP_PAGES);
}
add_action('init', 'wpmpw_my_products_endpoint');


function wpmpw_add_pagination_query_vars($vars) {
    $vars[] = 'paged';
    return $vars;
}
add_filter('query_vars', 'wpmpw_add_pagination_query_vars');


function wpmpw_my_products_content() {
    if (!is_user_logged_in()) {
        echo '<p>You must be logged in to view your items.</p>';
        return;
    }

    $user_id = get_current_user_id();
    $products_per_page = 4;
    
   if (preg_match('/my-products\/page\/([0-9]+)/', $_SERVER['REQUEST_URI'], $matches)) {
        $paged = absint($matches[1]);
    }

    $args = array(
        'post_type'      => 'product',
        'post_status'    => array('publish', 'pending', 'draft'),
        'posts_per_page' => $products_per_page,
        'paged'          => $paged,
        'author'         => $user_id,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<table class="wpmpw-table">';
        echo '<thead><tr>
                <th>Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
              </tr></thead>';
        echo '<tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $product = wc_get_product($product_id);
            $edit_link = get_permalink($product_id) . '?edit=1';
            $delete_link = '?delete_product=' . $product_id;

            echo '<tr>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>' . ($product->get_stock_quantity() ?: '—') . '</td>';
            echo '<td>' . wc_price($product->get_price()) . '</td>';
            echo '<td>' . ucfirst($product->get_status()) . '</td>';
            echo '<td class="wpmpw-buttons">
                    <a href="' . $edit_link . '" class="edit">Edit</a>
                    <a href="' . $delete_link . '" class="delete" onclick="return confirm(\'Remove product?\');">Delete</a>
                  </td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';


        $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
        $total_pages = $query->max_num_pages;

        if ($total_pages > 1) {
            echo '<div class="pagination">';
            echo paginate_links(array(
                'base'    => esc_url_raw(add_query_arg('paged', '%#%')),
                'format'  => '',
                'current' => max(1, $paged),
                'total'   => $total_pages,
                'prev_text' => __('« Previous'),
                'next_text' => __('Next »'),
            ));

            echo '</div>';
        }


        wp_reset_postdata();
    } else {
        echo '<p>You have no products.</p>';
    }
}
add_action('woocommerce_account_my-products_endpoint', 'wpmpw_my_products_content');
