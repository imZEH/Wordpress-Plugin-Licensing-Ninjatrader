<?php

add_action('rest_api_init', 'register_user_trade_data_endpoint');

function register_user_trade_data_endpoint()
{
    register_rest_route('v1', '/add_user_trade', array(
        'methods' => 'POST',
        'callback' => 'add_user_trade_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/get_user_trade_summary', array(
        'methods' => 'GET',
        'callback' => 'get_user_trade_summary_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/search_user_trade_summary', array(
        'methods' => 'GET',
        'callback' => 'search_user_trade_summary_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/get_trade_execution_summary', array(
        'methods' => 'GET',
        'callback' => 'get_trade_execution_summary_function',
        'permission_callback' => '__return_true',
        'args' => [
            'customer_id' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                },
            ],
            'variation_id' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                },
            ],
        ],
    ));

    register_rest_route('v1', '/get_trade_data_execution', array(
        'methods' => 'GET',
        'callback' => 'get_trade_data_execution_function',
        'permission_callback' => '__return_true',
    ));
}

function add_user_trade_function(WP_REST_Request $request)
{

    global $wpdb;

    try {
        $errors = [];

        // Validate each field
        $customer_id = intval($request->get_param('customer_id'));
        if (empty($customer_id)) {
            $errors[] = 'customer_id is required';
        }

        $variation_id = intval($request->get_param('variation_id'));
        if (empty($variation_id)) {
            $errors[] = 'variation_id is required';
        }

        $symbol = sanitize_text_field($request->get_param('symbol'));
        if (empty($symbol)) {
            $errors[] = 'symbol is required';
        }

        $open_timestamp = sanitize_text_field($request->get_param('open_timestamp'));
        // Add validation for open_timestamp if needed, e.g., check format or value.

        $close_timestamp = sanitize_text_field($request->get_param('close_timestamp'));
        // Add validation for close_timestamp if needed, e.g., check format or value.

        $position = sanitize_text_field($request->get_param('position'));
        if (empty($position)) {
            $errors[] = 'position is required';
        }

        $price = floatval($request->get_param('price'));
        if (empty($price) && $price !== 0.0) { // Explicitly check for zero as it can be valid
            $errors[] = 'price is required';
        }

        $side = sanitize_text_field($request->get_param('side'));
        if (empty($side)) {
            $errors[] = 'side is required';
        }

        $pnl = floatval($request->get_param('pnl'));
        if (empty($pnl) && $pnl !== 0.0) { // Explicitly check for zero as it can be valid
            $errors[] = 'pnl is required';
        }

        $quantity = intval($request->get_param('quantity'));
        if (empty($quantity)) {
            $errors[] = 'quantity is required';
        }

        $commission = intval($request->get_param('commission'));
        // Optional validation for commission if needed.

        $fees = floatval($request->get_param('fees'));
        // Optional validation for fees if needed.

        // Check if there are errors and throw an exception with the details
        if (!empty($errors)) {
            throw new Exception('Validation failed: ' . implode(', ', $errors));
        }

        // Continue with the rest of your logic if all fields are valid


        // Prepare data for insertion or update
        $data = array(
            'customer_id' => $customer_id,
            'variation_id' => $variation_id,
            'symbol' => $symbol,
            'open_timestamp' => $open_timestamp,
            'close_timestamp' => $close_timestamp,
            'position' => $position,
            'price' => $price,
            'side' => $side,
            'pnl' => $pnl,
            'quantity' => $quantity,
            'commission' => $commission,
            'fees' => $fees
        );

        $table_name = $wpdb->prefix . 'actlkbi_user_trade_data';

        // Insert new record
        $inserted = $wpdb->insert($table_name, $data);

        if (!$inserted) {
            throw new Exception('Failed to add user trade.');
        }

        return new WP_REST_Response(['success' => true, 'message' => 'User trade added successfully.'], 200);
    } catch (Exception $e) {
        error_log('Error in add_user_trade_function: ' . $e->getMessage());
        return new WP_REST_Response(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function search_user_trade_summary_function(WP_REST_Request $request)
{
    global $wpdb;

    // Get pagination parameters from the request
    $page = $request->get_param('page') ?: 1; // Default to page 1
    $per_page = $request->get_param('per_page') ?: 10; // Default to 10 items per page
    $offset = ($page - 1) * $per_page;

    // Get search parameter from the request
    $search = $request->get_param('keyword');
    $search_query = '';
    $search_value = [];

    if (!empty($search)) {
        // Use a LIKE query to search for matching customer names or IDs
        $search_query = " AND (lk.customer_name LIKE %s) ";
        $search_value = ['%' . $wpdb->esc_like($search) . '%'];
    }

    // Count total rows for pagination
    $total_rows_query = "
        SELECT COUNT(DISTINCT lk.customer_id, lk.variation_id)
        FROM {$wpdb->prefix}actlkbi_license_keys AS lk
        LEFT JOIN {$wpdb->prefix}actlkbi_user_trade_data AS utd
        ON lk.customer_id = utd.customer_id
        WHERE utd.open_timestamp IS NOT NULL 
          AND utd.close_timestamp IS NOT NULL
          AND lk.variation_id = utd.variation_id
          $search_query
    ";

    $total_rows = $wpdb->get_var($wpdb->prepare($total_rows_query, ...$search_value));

    // Query to fetch paginated data
    $query = "
        SELECT
            lk.customer_id,
            utd.variation_id,
            lk.customer_name AS Customer,
            lk.product_name AS bot,
            utd.symbol,
            MIN(utd.open_timestamp) AS FirstOpenTimestamp,
            MAX(utd.close_timestamp) AS LastCloseTimestamp,
            (SELECT pnl FROM wp_actlkbi_user_trade_data WHERE open_timestamp IS NOT NULL AND close_timestamp IS NOT NULL ORDER BY created_date DESC LIMIT 1) AS Pnl,
            (SELECT COUNT(id) FROM wp_actlkbi_user_trade_data _utd WHERE _utd.open_timestamp = utd.open_timestamp) AS Execution
        FROM
            {$wpdb->prefix}actlkbi_license_keys AS lk
        LEFT JOIN
            {$wpdb->prefix}actlkbi_user_trade_data AS utd
        ON
            lk.customer_id = utd.customer_id
        WHERE
            utd.open_timestamp IS NOT NULL 
            AND utd.close_timestamp IS NOT NULL
            AND lk.variation_id = utd.variation_id
            $search_query
        GROUP BY
            lk.customer_name, lk.variation_id
        ORDER BY
            utd.created_date DESC
        LIMIT %d OFFSET %d
    ";

    $results = $wpdb->get_results($wpdb->prepare($query, ...array_merge($search_value, [$per_page, $offset])));

    if (empty($results)) {
        return new WP_REST_Response('No data found', 404);
    }

    // Prepare the response
    $response_data = [];
    foreach ($results as $row) {
        $response_data[] = [
            'customer_id' => $row->customer_id,
            'variation_id' => $row->variation_id,
            'customer_name' => $row->Customer,
            'bot' => $row->bot,
            'symbol' => $row->symbol,
            'first_open_timestamp' => $row->FirstOpenTimestamp,
            'last_close_timestamp' => $row->LastCloseTimestamp,
            'pnl' => $row->Pnl,
            'execution' => $row->Execution,
        ];
    }

    // Pagination info
    $pagination = [
        'total_rows' => $total_rows,
        'total_pages' => ceil($total_rows / $per_page),
        'current_page' => $page,
        'per_page' => $per_page,
    ];

    return new WP_REST_Response([
        'data' => $response_data,
        'pagination' => $pagination
    ], 200);
}


function get_user_trade_summary_function(WP_REST_Request $request)
{
    global $wpdb;

    try {
        // Get pagination parameters from the request
        $page = $request->get_param('page') ?: 1;  // Default to page 1
        $per_page = $request->get_param('per_page') ?: 10;  // Default to 10 items per page
        $offset = ($page - 1) * $per_page;

        // Count total rows for pagination
        $total_rows = $wpdb->get_var("
            SELECT COUNT(DISTINCT lk.customer_id, lk.variation_id)
            FROM {$wpdb->prefix}actlkbi_license_keys AS lk
            LEFT JOIN {$wpdb->prefix}actlkbi_user_trade_data AS utd
            ON lk.customer_id = utd.customer_id
            WHERE utd.open_timestamp IS NOT NULL 
              AND utd.close_timestamp IS NOT NULL
              AND lk.variation_id = utd.variation_id
        ");

        // Query to fetch paginated data
        $query = $wpdb->prepare("
            SELECT
                lk.customer_id,
                utd.variation_id,
                lk.customer_name AS Customer,
                lk.product_name AS bot,
                utd.symbol,
                MIN(utd.open_timestamp) AS FirstOpenTimestamp,
                MAX(utd.close_timestamp) AS LastCloseTimestamp,
                (SELECT pnl FROM wp_actlkbi_user_trade_data WHERE open_timestamp IS NOT NULL AND close_timestamp IS NOT NULL ORDER BY created_date DESC LIMIT 1) AS Pnl,
                (SELECT COUNT(id) FROM wp_actlkbi_user_trade_data _utd WHERE _utd.open_timestamp = utd.open_timestamp) AS Execution
            FROM
                {$wpdb->prefix}actlkbi_license_keys AS lk
            LEFT JOIN
                {$wpdb->prefix}actlkbi_user_trade_data AS utd
            ON
                lk.customer_id = utd.customer_id
            WHERE
                utd.open_timestamp IS NOT NULL AND utd.close_timestamp IS NOT NULL
                AND lk.variation_id = utd.variation_id
            GROUP BY
                lk.customer_name, lk.variation_id
            ORDER BY
                utd.created_date DESC
            LIMIT %d OFFSET %d
        ", $per_page, $offset);

        $results = $wpdb->get_results($query);

        if (empty($results)) {
            return new WP_REST_Response('No data found', 404);
        }

        // Prepare the response
        $response_data = [];
        foreach ($results as $row) {
            $response_data[] = [
                'customer_id' => $row->customer_id,
                'variation_id' => $row->variation_id,
                'customer_name' => $row->Customer,
                'bot' => $row->bot,
                'symbol' => $row->symbol,
                'first_open_timestamp' => $row->FirstOpenTimestamp,
                'last_close_timestamp' => $row->LastCloseTimestamp,
                'pnl' => $row->Pnl,
                'execution' => $row->Execution,
            ];
        }

        // Pagination info
        $pagination = [
            'total_rows' => $total_rows,
            'total_pages' => ceil($total_rows / $per_page),
            'current_page' => $page,
            'per_page' => $per_page
        ];

        return new WP_REST_Response([
            'data' => $response_data,
            'pagination' => $pagination
        ], 200);
    } catch (Exception $e) {
        // Log the error for debugging
        error_log('Error in get_user_trade_summary_function: ' . $e->getMessage());
        
        // Return a response indicating an error occurred
        return new WP_REST_Response([
            'error' => 'An error occurred while fetching trade summary.',
            'details' => $e->getMessage(),
        ], 500);
    }
}



function format_duration($seconds)
{
    // Calculate days, hours, minutes, and seconds
    $days = floor($seconds / 86400); // 1 day = 86400 seconds
    $hours = floor(($seconds % 86400) / 3600); // 1 hour = 3600 seconds
    $minutes = floor(($seconds % 3600) / 60); // 1 minute = 60 seconds
    $seconds = $seconds % 60;

    // Format the result into "Xd Xh Xm Xs"
    $formatted_duration = '';
    if ($days > 0) {
        $formatted_duration .= "{$days}d ";
    }
    if ($hours > 0 || $days > 0) { // Include hours if we have days
        $formatted_duration .= "{$hours}h ";
    }
    if ($minutes > 0 || $hours > 0 || $days > 0) { // Include minutes if we have hours or days
        $formatted_duration .= "{$minutes}m ";
    }
    $formatted_duration .= "{$seconds}s"; // Always show seconds

    return $formatted_duration;
}

function get_trade_execution_summary_function(WP_REST_Request $request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'actlkbi_user_trade_data';

    // Get pagination parameters from the request
    $page = $request->get_param('page') ?: 1; // Default to page 1
    $per_page = $request->get_param('per_page') ?: 10; // Default to 10 items per page
    $offset = ($page - 1) * $per_page;

    // Get additional parameters
    $customer_id = $request->get_param('customer_id');
    $variation_id = $request->get_param('variation_id');
    $symbol = $request->get_param('symbol'); // Optional symbol filter
    $start_date = $request->get_param('start_date'); // e.g., '2024-01-01'
    $end_date = $request->get_param('end_date'); // e.g., '2024-12-31'

    // Query to get all unique symbols
    $unique_symbols = $wpdb->get_col($wpdb->prepare(
        "
        SELECT DISTINCT symbol
        FROM $table_name
        WHERE customer_id = %d AND variation_id = %d
        ",
        $customer_id,
        $variation_id
    ));

    // Base WHERE condition
    $where_conditions = [
        "customer_id = %d",
        "variation_id = %d"
    ];
    $query_params = [$customer_id, $variation_id];

    // Add symbol condition if provided
    if (!empty($symbol)) {
        $where_conditions[] = "symbol = %s";
        $query_params[] = $symbol;
    }

    // Add date range condition based on created_date
    if (!empty($start_date)) {
        $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
        $where_conditions[] = "created_date >= %s";
        $query_params[] = $start_date;
    }
    
    if (!empty($end_date)) {
        $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
        $where_conditions[] = "created_date <= %s";
        $query_params[] = $end_date;
    }

    // Combine WHERE conditions
    $where_clause = implode(' AND ', $where_conditions);

    // Count total rows for pagination
    $total_rows_query = "
        SELECT COUNT(DISTINCT symbol, position)
        FROM $table_name
        WHERE $where_clause
    ";
    $total_rows = $wpdb->get_var($wpdb->prepare($total_rows_query, ...$query_params));

    // Query to fetch paginated data
    $results_query = "
        SELECT 
            id,
            created_date,
            symbol,
            MIN(open_timestamp) AS open_timestamp,
            MAX(close_timestamp) AS close_timestamp,
            position,
            (SELECT pnl FROM $table_name WHERE customer_id = %d AND variation_id = %d ORDER BY created_date DESC LIMIT 1) AS total_pnl,
            COUNT(*) AS executions,
            TIMESTAMPDIFF(SECOND, MIN(open_timestamp), MAX(close_timestamp)) AS total_duration,
            (SELECT price FROM $table_name WHERE symbol = t.symbol AND close_timestamp = '0000-00-00 00:00:00' AND customer_id = %d AND variation_id = %d ORDER BY open_timestamp ASC LIMIT 1) AS open_price,
            (SELECT price FROM $table_name WHERE symbol = t.symbol AND open_timestamp IS NOT NULL AND close_timestamp IS NOT NULL AND customer_id = %d AND variation_id = %d ORDER BY close_timestamp DESC LIMIT 1) AS close_price
        FROM $table_name t
        WHERE $where_clause
        GROUP BY symbol, position
        ORDER BY open_timestamp DESC
        LIMIT %d OFFSET %d
    ";

    $results_params = array_merge(
        [$customer_id, $variation_id, $customer_id, $variation_id, $customer_id, $variation_id],
        $query_params,
        [$per_page, $offset]
    );
    $results = $wpdb->get_results($wpdb->prepare($results_query, ...$results_params));

    if (empty($results)) {
        return new WP_REST_Response('No data found', 404);
    }

    // Prepare the response
    $response_data = [];
    foreach ($results as $row) {
        $response_data[] = [
            'id' => $row->id,
            'symbol' => $row->symbol,
            'open_timestamp' => $row->open_timestamp,
            'close_timestamp' => $row->close_timestamp,
            'position' => $row->position,
            'total_pnl' => $row->total_pnl,
            'executions' => $row->executions,
            'total_duration' => format_duration($row->total_duration),
            'open_price' => $row->open_price,
            'close_price' => $row->close_price,
            'created_date' => $row->created_date
        ];
    }

    // Pagination info
    $pagination = [
        'total_rows' => $total_rows,
        'total_pages' => ceil($total_rows / $per_page),
        'current_page' => $page,
        'per_page' => $per_page
    ];

    return new WP_REST_Response([
        'unique_symbols' => $unique_symbols, // Include the unique symbols in the response
        'data' => $response_data,
        'pagination' => $pagination,
        'test' => $where_clause
    ], 200);
}


function get_trade_data_execution_function(WP_REST_Request $request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'actlkbi_user_trade_data';

    // Retrieve the `id` parameter from the request.
    $id = intval($request['id']);

    // Query to get the entry for the provided ID.
    $entry = $wpdb->get_row($wpdb->prepare("
        SELECT open_timestamp
        FROM $table_name
        WHERE id = %d
        LIMIT 1
    ", $id));

    // Check if the entry exists.
    if (!$entry) {
        return new WP_REST_Response(['message' => 'No trade data found for the given ID'], 404);
    }

    // Extract the `open_timestamp` of the given entry.
    $open_timestamp = $entry->open_timestamp;

    // Get pagination parameters from the request
    $page = $request->get_param('page') ?: 1;  // Default to page 1
    $per_page = $request->get_param('per_page') ?: 10;  // Default to 10 items per page
    $offset = ($page - 1) * $per_page;

    // Count total rows for pagination
    $total_rows = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM $table_name
        WHERE open_timestamp = %s
    ", $open_timestamp));

    // Query to get paginated records with the same `open_timestamp`.
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT *
        FROM $table_name
        WHERE open_timestamp = %s
        LIMIT %d OFFSET %d
    ", $open_timestamp, $per_page, $offset));

    // Check if matching records exist.
    if (empty($results)) {
        return new WP_REST_Response(['message' => 'No matching records found'], 404);
    }

    // Pagination info
    $pagination = [
        'total_rows' => $total_rows,
        'total_pages' => ceil($total_rows / $per_page),
        'current_page' => $page,
        'per_page' => $per_page
    ];

    // Prepare the response
    $response_data = [
        'data' => $results,
        'pagination' => $pagination
    ];

    // Return the trade data with pagination metadata
    return new WP_REST_Response($response_data, 200);
}

