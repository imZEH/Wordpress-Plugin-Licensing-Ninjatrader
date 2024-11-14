<?php

add_action('rest_api_init', 'register_customer_search_endpoint');

function register_customer_search_endpoint()
{
    register_rest_route('v1', '/search_customer', array(
        'methods' => 'GET',
        'callback' => 'search_customer_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/search_product', array(
        'methods' => 'GET',
        'callback' => 'search_product_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/add_license_key', array(
        'methods' => 'POST',
        'callback' => 'add_update_license_key_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/get_licenses', array(
        'methods' => 'GET',
        'callback' => 'get_license_keys_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/activate_license_key', array(
        'methods' => 'POST',
        'callback' => 'activate_license_key_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/delete-license-key/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_license_key_function',
        'permission_callback' => '__return_true',
    ));
}

function search_customer_function($request)
{
    // Get the search term from the request
    $search_term = $request->get_param('search_term');
    if (!$search_term) {
        return new WP_Error('no_search_term', 'Search term is required', array('status' => 400));
    }

    // Query for customers ( users ) based on the search term
    $args = array(
        'role' => 'customer', // Adjust role as needed
        'search' => '*' . sanitize_text_field($search_term) . '*',
        'search_columns' => array('user_login', 'user_email'),
    );

    $users = get_users($args);

    // Prepare response data
    $response = [];
    if ($users) {
        foreach ($users as $user) {
            $response[] = array(
                'id' => $user->id,
                'name' => $user->display_name,
                'email' => $user->user_email,
            );
        }
    } else {
        return new WP_REST_Response(['message' => 'No customers found.'], 200);
    }

    return new WP_REST_Response($response, 200);
}

function search_product_function($request)
{
    // Get the search term from the request
    $search_term = $request->get_param('search_term');
    if (!$search_term) {
        return new WP_Error('no_search_term', 'Search term is required', array('status' => 400));
    }

    // Query for products based on the search term
    $args = array(
        'post_type' => 'product', // Adjust to query WooCommerce products
        'posts_per_page' => -1,   // Retrieve all products
        's' => sanitize_text_field($search_term),  // Search term ( same as in the customer search )
        'post_status' => 'publish', // Only published products
    );

    // Get the products based on the query
    $products = new WP_Query($args);

    // Prepare response data
    $response = [];
    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();

            // Get the product object
            $product = wc_get_product(get_the_ID());

            // Get the product slug (for simple or variable products)
            $product_slug = $product->get_slug();

            // If it's a variable product, retrieve variations and attributes
            if ($product->is_type('variable')) {
                // Get the variations (children)
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation = wc_get_product($variation_id);
                    $attributes = $variation->get_attributes();

                    // Get the variation slug
                    $variation_slug = $variation->get_slug();  // Retrieve variation slug

                    $variation_attributes = $product->get_variation_attributes();

                    if (isset($attributes['pa_platform'])) {
                        $platform = $attributes['pa_platform'];
                    } else if (isset($attributes['platform'])) {
                        $platform = $attributes['platform'];
                    } else {
                        $platform = '';
                    }

                    $variation_attributes = $product->get_variation_attributes();

                    foreach ($variation_attributes as $attribute_taxonomy => $term_slug) {
                        // Get product attribute name or taxonomy
                        $taxonomy = str_replace('attribute_', '', $attribute_taxonomy);
                        // The label name from the product attribute
                        $attribute_name = wc_attribute_label($taxonomy, $product);
                        // The term name (or value) from this attribute
                        if (taxonomy_exists($taxonomy)) {
                            $attribute_value = get_term_by('slug', $term_slug, $taxonomy)->name;
                        } else {
                            $attribute_value = $term_slug; // For custom product attributes
                        }
                    }

                    $response[] = array(
                        'product_name' => $product->get_name(),
                        'variation_name' => $variation->get_name(),
                        'platform' => $platform,
                        'price' => $variation->get_price_html(),
                        'product_id' => $product->get_id(),
                        'variation_id' => $variation->get_id(),
                        'product_slug' => $product_slug,  // Added product slug
                        'variation_slug' => $variation_slug
                    );
                }
            } else {
                // For simple products, just return the product info
                $response[] = array(
                    'product_name' => $product->get_name(),
                    'price' => $product->get_price_html(),
                    'product_id' => $product->get_id(),
                    'product_slug' => $product_slug  // Added product slug
                );
            }
        }
    } else {
        return new WP_REST_Response(['message' => 'No products found.'], 200);
    }

    return new WP_REST_Response($response, 200);
}


function add_update_license_key_function(WP_REST_Request $request)
{

    global $wpdb;

    try {
        // Get and validate data from the request
        $id = intval($request->get_param('id'));
        $customer_name = sanitize_text_field($request->get_param('customer_name'));
        $customer_email = sanitize_email($request->get_param('customer_email'));
        $product_id = intval($request->get_param('product_id'));
        $variation_id = intval($request->get_param('variation_id'));
        $customer_id = intval($request->get_param('customer_id'));
        $product_name = sanitize_text_field($request->get_param('product_name'));
        $product_slug = sanitize_text_field($request->get_param('product_slug'));
        $machine_id = sanitize_text_field($request->get_param('machine_id'));
        $license_key = sanitize_text_field($request->get_param('license_key'));
        $no_of_devices = intval($request->get_param('no_of_devices'));
        $platform = sanitize_text_field($request->get_param('platform'));
        $status = sanitize_text_field($request->get_param('status'));
        $auto_generate = intval($request->get_param('auto_generate'));

        // Input validation
        if (empty($customer_name) || empty($customer_email) || empty($product_name)) {
            throw new Exception('Missing required fields.');
        }

        // If auto-generate is enabled, create a new license key
        if ($auto_generate) {
            $salt = md5($product_id . $customer_name . $customer_email . $product_name);
            $random_key = strtoupper(bin2hex(random_bytes(8)));
            $combined_key = strtoupper(substr($random_key, 0, 8) . '-' . substr($salt, 0, 8) . '-' . substr($random_key, 8, 8) . '-' . substr($salt, 8, 8));
            $license_key = $combined_key;
        }

        // Prepare data for insertion or update
        $data = array(
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'product_name' => $product_name,
            'product_slug' => $product_slug,
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'platform' => $platform,
            'machine_id' => $machine_id,
            'license_key' => $license_key,
            'max_domains' => $no_of_devices,
            'status' => $status
        );

        $table_name = $wpdb->prefix . 'actlkbi_license_keys';

        // Insert or update based on whether the ID is provided
        if ($id) {
            // Update existing record
            $updated = $wpdb->update($table_name, $data, array('id' => $id));

            if ($updated === false) {
                throw new Exception('Failed to update license key.');
            }
            return new WP_REST_Response(['success' => true, 'message' => 'License key updated successfully.'], 200);
        } else {
            // Insert new record
            $inserted = $wpdb->insert($table_name, $data);

            if (!$inserted) {
                throw new Exception('Failed to add license key.');
            }
            return new WP_REST_Response(['success' => true, 'message' => 'License key added successfully.'], 200);
        }
    } catch (Exception $e) {
        error_log('Error in add_license_key_function: ' . $e->getMessage());
        return new WP_REST_Response(['success' => false, 'message' => $e->getMessage()], 400);
    }
}


function get_license_keys_function(WP_REST_Request $request)
{
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'actlkbi_license_keys';

    // Get pagination parameters from the request
    $page = $request->get_param('page') ?: 1;  // Default to page 1
    $per_page = $request->get_param('per_page') ?: 10;  // Default to 10 items per page

    // Calculate offset for SQL query
    $offset = ($page - 1) * $per_page;

    // Get the total number of rows for pagination
    $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Get the license key data with LIMIT and OFFSET for pagination
    $license_key_results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name ORDER BY created_date DESC LIMIT %d OFFSET %d", $per_page, $offset)
    );

    // Prepare response data
    $response = [];
    if ($license_key_results) {
        foreach ($license_key_results as $row) {
            // Handle domain count
            $domain_count = empty($row->domains) ? 0 : count(explode('~actlkbi~', $row->domains));
            $status = ucfirst($row->status);
            $color = $status === 'Active' ? 'green' : 'red';

            // Push each row into response array
            $response[] = array(
                'id'              => $row->id,
                'product_id'      => $row->product_id,
                'variation_id'      => $row->variation_id,
                'product_name'    => $row->product_name,
                'product_slug'    => $row->product_slug,
                'customer_id'     => $row->customer_id,
                'customer_name'   => $row->customer_name,
                'customer_email'  => $row->customer_email,
                'license_key'     => $row->license_key,
                'platform'        => $row->platform,
                'machine_id'      => $row->machine_id,
                'max_domains'      => $row->max_domains,
                'active_device'   => $domain_count . '/' . $row->max_domains,
                'status'          => $status,
                'domains'         => str_replace('~actlkbi~', '<br>', $row->domains),
                'status_color'    => $color
            );
        }
    } else {
        return new WP_REST_Response([
            'data' => $response,
            'pagination' => 0
        ], 200);
    }

    // Return pagination data
    $pagination = [
        'total_rows' => $total_rows,
        'total_pages' => ceil($total_rows / $per_page),
        'current_page' => $page,
        'per_page' => $per_page
    ];

    return new WP_REST_Response([
        'data' => $response,
        'pagination' => $pagination
    ], 200);
}

function activate_license_key_function(WP_REST_Request $request)
{
    global $wpdb;

    try {
        // Get parameters from the request
        $license_key = sanitize_text_field($request->get_param('license_key'));
        $machine_id = sanitize_text_field($request->get_param('machine_id'));
        $platform = sanitize_text_field($request->get_param('platform'));
        $product_slug = sanitize_text_field($request->get_param('product_slug'));
        $new_domain = sanitize_text_field($request->get_param('domain')); // New domain to add

        // Table name
        $table_name = $wpdb->prefix . 'actlkbi_license_keys';

        // Query to find the record matching the provided parameters
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE license_key = %s AND machine_id = %s AND platform = %s AND product_slug = %s AND status = %s",
            $license_key,
            $machine_id,
            $platform,
            $product_slug,
            "Active"
        );

        // Execute the query
        $result = $wpdb->get_row($query);

        // If a matching record is found
        if ($result) {
            // Get the current domains
            $existing_domains = $result->domains;
            $max_domains = $result->max_domains;

            // Split the domains by the delimiter '~actlkbi~' and count the number of domains
            $domains_array = explode("~actlkbi~", $existing_domains);
            $domains_array = array_filter($domains_array);

            // Check if the new domain already exists
            if (in_array($new_domain, $domains_array)) {
                return new WP_REST_Response([
                    'success' => true,
                    'message' => 'License key validated successfully',
                    'data' => array_merge((array) $result, ['domains' => $existing_domains])
                ], 200);
            }

            // Count the number of domains
            $domain_count = count($domains_array);

            // Check if the domain count is less than the max allowed domains
            if ($domain_count < $max_domains) {
                // If there are existing domains, append the new domain with the delimiter
                $new_domains = $domain_count > 0 ? $existing_domains . "~actlkbi~" . $new_domain : $new_domain;

                // Update the 'domains' field in the database
                $update_query = $wpdb->prepare(
                    "UPDATE $table_name SET domains = %s WHERE license_key = %s AND machine_id = %s AND platform = %s AND product_slug = %s AND status = %s",
                    $new_domains,
                    $license_key,
                    $machine_id,
                    $platform,
                    $product_slug,
                    "Active"
                );

                // Execute the update query
                $wpdb->query($update_query);

                // Return the success response with the updated data
                return new WP_REST_Response([
                    'success' => true,
                    'message' => 'License key validated successfully',
                    'data' => array_merge((array) $result, ['domains' => $new_domains])
                ], 200);
            } else {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Max domain limit reached, cannot add more domains'
                ], 400);
            }
        } else {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Invalid license key or parameters'
            ], 400);
        }
    } catch (Exception $e) {
        error_log('Error in activate_license_key_function: ' . $e->getMessage());
        return new WP_REST_Response(['success' => false, 'message' => $e->getMessage()], 400);
    }
}


function delete_license_key_function($request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'actlkbi_license_keys';
    $id = intval($request['id']);

    $deleted = $wpdb->delete($table_name, array('id' => $id), array('%d'));

    if ($deleted) {
        return new WP_REST_Response('Record deleted successfully', 200);
    } else {
        return new WP_REST_Response('Record not found or failed to delete', 404);
    }
}
