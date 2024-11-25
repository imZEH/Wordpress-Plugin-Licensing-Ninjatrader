<?php

add_action('rest_api_init', 'register_indicator_endpoint');

function register_indicator_endpoint()
{
    register_rest_route('v1', '/get_indicators', array(
        'methods' => 'GET',
        'callback' => 'get_indicators_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/add_indicators', array(
        'methods' => 'POST',
        'callback' => 'add_update_indicators_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/search_internal_indicators', array(
        'methods' => 'GET',
        'callback' => 'search_indicators_internal_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/search_indicators', array(
        'methods' => 'GET',
        'callback' => 'search_indicators_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/delete_indicators/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_indicators_function',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('v1', '/update_indicator_status/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'update_indicator_status_function',
        'permission_callback' => '__return_true',
    ));
}


function get_indicators_function(WP_REST_Request $request)
{
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'actlkbi_indicators';

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
            $status = ucfirst($row->status);
            $color = $status === 'Active' ? 'green' : 'red';
            // Push each row into response array
            $response[] = array(
                'id'            => $row->id,
                'indicator_name'      => $row->indicator_name,
                'platform'      => $row->platform,
                'strategy'      => $row->strategy,
                'indicator_variables' => json_decode($row->indicator_variables, true),
                'status'        => $status,
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

function search_indicators_internal_function(WP_REST_Request $request)
{
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'actlkbi_indicators';

    $keyword = $request->get_param('keyword') ?: '';  // Default to empty string

    // Get pagination parameters from the request
    $page = $request->get_param('page') ?: 1;  // Default to page 1
    $per_page = $request->get_param('per_page') ?: 10;  // Default to 10 items per page

    // Calculate offset for SQL query
    $offset = ($page - 1) * $per_page;

    // Build WHERE clause safely
    $query_parts = [];
    if (is_numeric($keyword)) {
        $query_parts[] = $wpdb->prepare("id = %d", $keyword);
    } else {
        $query_parts[] = $wpdb->prepare("indicator_name LIKE %s", '%' . $wpdb->esc_like($keyword) . '%');
        $query_parts[] = $wpdb->prepare("platform = %s", $keyword);
        $query_parts[] = $wpdb->prepare("strategy LIKE %s", '%' . $wpdb->esc_like($keyword) . '%');
        $query_parts[] = $wpdb->prepare("LOWER(status) = LOWER(%s)", $keyword);
    }
    $query = $query_parts ? 'WHERE ' . implode(' OR ', $query_parts) : '';

    // Get the total number of rows for pagination
    $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $query");

    // Get the license key data with LIMIT and OFFSET for pagination
    $license_key_results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name $query ORDER BY created_date DESC LIMIT %d OFFSET %d", $per_page, $offset)
    );

    // Prepare response data
    $response = [];
    if ($license_key_results) {
        foreach ($license_key_results as $row) {
            // Handle domain count
            $status = ucfirst($row->status);
            $color = $status === 'Active' ? 'green' : 'red';

            // Push each row into response array
            $response[] = array(
                'id'             => $row->id,
                'indicator_name' => $row->indicator_name,
                'platform'       => $row->platform,
                'strategy'       => $row->strategy,
                'indicator_variables' => json_decode($row->indicator_variables, true),
                'status'         => $status,
                'status_color'   => $color
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


function search_indicators_function(WP_REST_Request $request)
{
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'actlkbi_indicators';

    // Get parameters from the request
    $id = $request->get_param('id');  // id parameter
    $name = $request->get_param('name');  // name parameter

    // Error handling: if both id and name are provided, return an error
    if ($id && $name) {
        return new WP_REST_Response([
            "success" => false,
            'message' => 'You cannot provide both id and name parameters at the same time.'
        ], 400);  // Bad request error
    }

    // Error handling: if neither id nor name is provided, return an error
    if (!$id && !$name) {
        return new WP_REST_Response([
            "success" => false,
            'message' => 'You must provide either an id or a name parameter.'
        ], 400);  // Bad request error
    }

    // Get pagination parameters from the request
    $page = $request->get_param('page') ?: 1;  // Default to page 1
    $per_page = $request->get_param('per_page') ?: 10;  // Default to 10 items per page

    // Calculate offset for SQL query
    $offset = ($page - 1) * $per_page;

    // Initialize query
    $query = "WHERE 1=1"; // Start with a condition that is always true

    // Add condition for id if provided
    if ($id) {
        $query .= $wpdb->prepare(" AND id = %d", $id);
    }

    // Add condition for name if provided
    if ($name) {
        $query .= $wpdb->prepare(" AND indicator_name LIKE %s", '%' . $wpdb->esc_like($name) . '%');
    }

    // Get the total number of rows for pagination
    $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $query");

    // Get the license key data with LIMIT and OFFSET for pagination
    $license_key_results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name $query ORDER BY created_date DESC LIMIT %d OFFSET %d", $per_page, $offset)
    );

    // Prepare response data
    $response = [];
    if ($license_key_results) {
        foreach ($license_key_results as $row) {
            // Handle domain count
            $status = ucfirst($row->status);
            $color = $status === 'Active' ? 'green' : 'red';

            // Push each row into response array
            $response[] = array(
                'id'            => $row->id,
                'indicator_name'      => $row->indicator_name,
                'platform'      => $row->platform,
                'strategy'      => $row->strategy,
                'indicator_variables' => json_decode($row->indicator_variables, true),
                'status'        => $status,
                'status_color'  => $color
            );
        }
    } else {
        return new WP_REST_Response([
            'data' => $response,
            'pagination' => 0,
        ], 200);
    }

    // Return pagination data
    $pagination = [
        'total_rows' => $total_rows,
        'total_pages' => ceil($total_rows / $per_page),
        'current_page' => $page,
        'per_page' => $per_page,
    ];

    return new WP_REST_Response([
        'data' => $response,
        'pagination' => $pagination
    ], 200);
}

function add_update_indicators_function(WP_REST_Request $request)
{

    global $wpdb;

    try {
        // Get and validate data from the request
        $id = intval($request->get_param('id'));
        $indicator_name = sanitize_text_field($request->get_param('indicator_name'));
        $platform = sanitize_text_field($request->get_param('platform'));
        $strategy = sanitize_text_field($request->get_param('strategy'));
        $indicator_variables = $request->get_param('indicator_variables');
        $status = sanitize_text_field($request->get_param('status'));

        // Input validation
        if (empty($indicator_name)) {
            throw new Exception('Missing required fields.');
        }

        // Prepare data for insertion or update
        $data = array(
            'id'            => $id,
            'indicator_name'      => $indicator_name,
            'platform'      => $platform,
            'strategy'      => $strategy,
            'indicator_variables' => json_encode($indicator_variables),
            'status'        => $status
        );

        $table_name = $wpdb->prefix . 'actlkbi_indicators';

        // Insert or update based on whether the ID is provided
        if ($id) {
            // Update existing record
            $updated = $wpdb->update($table_name, $data, array('id' => $id));

            if ($updated === false) {
                throw new Exception('Failed to update Indicator.');
            }
            return new WP_REST_Response(['success' => true, 'message' => 'Indicator updated successfully.'], 200);
        } else {
            // Insert new record
            $inserted = $wpdb->insert($table_name, $data);

            if (!$inserted) {
                throw new Exception('Failed to add Indicator.');
            }
            return new WP_REST_Response(['success' => true, 'message' => 'Indicator added successfully.'], 200);
        }
    } catch (Exception $e) {
        error_log('Error in add_update_indicators_function: ' . $e->getMessage());
        return new WP_REST_Response(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function delete_indicators_function($request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'actlkbi_indicators';
    $id = intval($request['id']);

    $deleted = $wpdb->delete($table_name, array('id' => $id), array('%d'));

    if ($deleted) {
        return new WP_REST_Response('Record deleted successfully', 200);
    } else {
        return new WP_REST_Response('Record not found or failed to delete', 404);
    }
}

function update_indicator_status_function(WP_REST_Request $request)
{

    global $wpdb;

    try {
        // Get and validate data from the request
        $id = intval($request->get_param('id'));
        $status = sanitize_text_field($request->get_param('status'));

        // Input validation
        if (empty($id)) {
            throw new Exception('Missing required fields.');
        }

        // Prepare data for insertion or update
        $data = array(
            'id'            => $id,
            'status'        => $status
        );

        $table_name = $wpdb->prefix . 'actlkbi_indicators';

        // Insert or update based on whether the ID is provided
        if ($id) {
            // Update existing record
            $updated = $wpdb->update($table_name, $data, array('id' => $id));

            if ($updated === false) {
                throw new Exception('Failed to update indicator status.');
            }
            return new WP_REST_Response(['success' => true, 'message' => 'Indicator updated successfully.'], 200);
        } else {
            throw new Exception('Failed to update indicator status.');
        }
    } catch (Exception $e) {
        error_log('Error in update_indicator_status_function: ' . $e->getMessage());
        return new WP_REST_Response(['success' => false, 'message' => $e->getMessage()], 400);
    }
}
