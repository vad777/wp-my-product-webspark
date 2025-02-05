=== WP My Product Webspark ===
Contributors: yourusername
Tags: woocommerce, my account, products, crud
Requires at least: 5.6
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin extends WooCommerce functionality by allowing users to manage their products through the **My Account** page.


The **WP My Product Webspark** plugin enhances WooCommerce, enabling users to **add, edit, and delete their products** directly from their **My Account** section.

**Main Features**:
- Add products through the **Add Product** page.
- View and manage products on the **My Products** page.
- CRUD operations (Create, Read, Update, Delete).
- Pagination for product listings.
- Admin email notifications for new products.
- Uses the WP Media Library for image uploads.


1. **Download the ZIP archive** of the plugin.
2. **Go to your WordPress admin panel** → **Plugins** → **Add New** → **Upload Plugin**.
3. Select `wp-my-product-webspark.zip` and click **Install Now**.
4. **Activate the plugin**.
5. **Make sure WooCommerce is installed and activated!**


1. **New menu items will appear in the WooCommerce My Account section**:
   - **Add Product** – allows users to add new products.
   - **My Products** – displays a list of the user's products with edit and delete options.

2. **All new products are created with the status "Pending Review"**.
3. **The admin receives an email notification** whenever a new product is added.
4. **Users can only manage their own products**.


= What should I do if pagination does not work? =
1. Go to **Settings** → **Permalinks** and click **Save Changes**.
2. Ensure the `rewrite_rules` for **my-products** are properly set in the code.
3. Check for potential conflicts with other WooCommerce plugins.

= How can I change the product status after creation? =
By default, products are assigned the "Pending Review" status. If you want products to be published immediately, open `wp-my-product-webspark.php` and find this line:
```php
$post_data['post_status'] = 'pending';
