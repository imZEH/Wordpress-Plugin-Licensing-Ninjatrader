<?php

class License_Key_Handler
{
    public static function init()
    {
        // Hook for initial license generation on order completion
        add_action('woocommerce_order_status_completed', [__CLASS__, 'generate_license_key']);

        // Hooks for subscription status changes
        add_action('sumosubscriptions_active_subscription', [__CLASS__, 'handle_subscription_activation']);
        
        add_action('sumosubscriptions_cancel_subscription', [__CLASS__, 'handle_subscription_cancellation']);
        add_action('sumosubscriptions_pause_subscription', [__CLASS__, 'handle_subscription_cancellation']);
    }

    public static function generate_license_key($order_id)
    {
        $order = wc_get_order($order_id);
        global $wpdb;

        // Loop through each item in the order
        foreach ($order->get_items() as $item) {
            $product_name = $item->get_name();
            $product_id = $item->get_product_id();
            $product_slug = $item->get_product()->get_slug();
            $customer_id = $order->get_user_id();
            $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $customer_email = $order->get_billing_email();
            $machine_id = $order->get_meta('billing_machine_id');

            $order = wc_get_order($order_id);

            $product = $item->get_product();

            $quantity = $item->get_quantity();

            if ($product->is_type('variation')) {
                $variation_id = $product->get_id();
                $product_id = $product->get_parent_id();
                //$product_name = $product->get_name(); // Variation name

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
            }

            $salt = md5($product_id . $customer_id . $customer_email . $product_name);
            $random_key = strtoupper(bin2hex(random_bytes(8)));
            $combined_key = strtoupper(substr($random_key, 0, 8) . '-' . substr($salt, 0, 8) . '-' . substr($random_key, 8, 8) . '-' . substr($salt, 8, 8));

            $product_type = $product->get_type();

            $status = "Inactive";
            if (!in_array($product_type, ['subscription', 'variable-subscription'])) {
                $status = "Active";
            }

            $wpdb->insert(
                $wpdb->prefix . 'actlkbi_license_keys',
                array(
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,
                    'product_name' => $product_name,
                    'product_slug' => $product_slug,
                    'customer_id' => $customer_id,
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'platform' => $attribute_value,
                    'machine_id' => $machine_id,
                    'license_key' => $combined_key,
                    'max_domains' => $quantity,
                    'status' => $status,
                    'order_id' => $order_id
                )
            );

            self::send_email_on_order_complete($order_id, $product_name, $combined_key);
        }
    }

    public static function send_email_on_order_complete($order_id, $product_name, $combined_key)
    {
        // Get the order object
        $order = wc_get_order($order_id);

        // Get the customer's name and email address
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $customer_email = $order->get_billing_email();

        // Get the email settings from plugin options
        $options = get_option('license_key_settings');
        $from_name = $options['license_email_from_name'] ?? 'acetradingbots.com';
        $from_email = $options['license_email_from_address'] ?? 'support@acetradingbots.com';
        $message_template = $options['license_email_message'] ?? 'Dear {customer_name},\n\nNew license has been created for you.\n\nLicense information is given below:\n\nProduct Name: {product_name}\nLicense Code: {license_key}';

        // Replace placeholders with actual data
        $message = str_replace('{customer_name}', $customer_name, $message_template);
        $message = str_replace('{product_name}', $product_name, $message);
        $message = str_replace('{license_key}', $combined_key, $message);

        // Email subject
        $subject = 'Your New License Information';

        // Set email headers (optional, to send HTML email)
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

        // Send the email to the customer
        wp_mail($customer_email, $subject, nl2br($message), $headers);
    }

    public static function handle_subscription_activation( $subscription_id ) {
        global $wpdb;
    
        // Retrieve the parent order ID from the subscription meta
        $order_id_array = get_post_meta( $subscription_id, 'sumo_get_parent_order_id', true );
    
        // Since the value is stored in an array, extract the first element
        $order_id = is_array( $order_id_array ) ? $order_id_array[0] : $order_id_array;
    
        // Debug the extracted order ID
        error_log( 'Extracted Order ID: ' . $order_id );
    
        if ( $order_id ) {
            // Update the license status to 'active'
            $wpdb->update(
                $wpdb->prefix . 'actlkbi_license_keys',
                array( 'status' => 'Active' ),
                array( 'order_id' => $order_id )
            );
        } else {
            error_log( 'Order ID not found for Subscription ID: ' . $subscription_id );
        }
    }

    public static function handle_subscription_cancellation($subscription_id)
    {
        global $wpdb;
    
        // Retrieve the parent order ID from the subscription meta
        $order_id_array = get_post_meta( $subscription_id, 'sumo_get_parent_order_id', true );
    
        // Since the value is stored in an array, extract the first element
        $order_id = is_array( $order_id_array ) ? $order_id_array[0] : $order_id_array;
    
        // Debug the extracted order ID
        error_log( 'Extracted Order ID: ' . $order_id );
    
        if ( $order_id ) {
            // Update the license status to 'active'
            $wpdb->update(
                $wpdb->prefix . 'actlkbi_license_keys',
                array( 'status' => 'Inactive' ),
                array( 'order_id' => $order_id )
            );
        } else {
            error_log( 'Order ID not found for Subscription ID: ' . $subscription_id );
        }
    }
}
