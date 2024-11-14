<div class="wp-style-button-container">
    <div class="lkbi-search-property">
        Select Property
        <select id="status" name="status">
            <option value="">Select</option>
            <option value="product">Product</option>
            <option value="client_name">Client Name</option>
            <option value="client_email">Client Email</option>
            <option value="license_key">License Key</option>
            <option value="platform">Platform</option>
            <option value="status">Status</option>
        </select>
        <input type="text" id="no_of_devices" name="no_of_devices">
        <button class="btn-search">Search</button>
    </div>


    <button id="modal-action" class="wp-style-button">Add License</button>
</div>
<div class="table-container" style="position: relative;">
    <div class="spinner-container" style="display: none;">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../assets/img/spinner.gif'; ?>" alt="Loading..." style="width: 100px;">
    </div>

    <table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Product Slug</th>
                <th>Customer Name</th>
                <th>Customer Email</th>
                <th style="width: 200px;">Platform License</th>
                <th>Platform</th>
                <th style="width: 71px;">Machine ID</th>
                <th>Active Devices</th>
                <th style="width: 50px;">User Devices</th>
                <th style="width: 60px;">Status</th>
                <th style="width: 110px;"></th>
            </tr>
        </thead>
        <tbody id="license-keys-tbody">
            <!-- Table data here -->
        </tbody>
    </table>
    <p id="no-license-found-message" style="display:none; text-align:center;">No license keys found.</p>
</div>
<div id="pagination-container"></div>
<div id="toast-container" class="toast-container"></div>