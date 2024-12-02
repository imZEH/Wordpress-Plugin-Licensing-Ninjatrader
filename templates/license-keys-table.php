<div class="wp-style-button-container">
    <div class="lkbi-search-property">
        <input type="text" placeholder="Search" id="keyword" name="keyword">
        <button class="lkbi-btn-search">Search</button>
        <button class="lkbi-btn-reset">Refresh</button>
    </div>


    <button id="lkbi-license-modal-action" class="wp-style-button">Add License</button>
</div>
<div class="table-container" style="position: relative;">
    <div class="spinner-container" style="display: none;">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../assets/img/spinner.gif'; ?>" alt="Loading..." style="width: 100px;">
    </div>

    <table class="lkbi-table" style="margin-top:20px;">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Product Slug</th>
                <th>Customer Name</th>
                <th>Customer Email</th>
                <th>Platform License</th>
                <th>Platform</th>
                <th>Machine ID</th>
                <th>Active Devices</th>
                <th style="width: 40px;">User Devices</th>
                <th style="width: 35px;">Status</th>
                <th style="width: 95px;">Actions</th>
            </tr>
        </thead>
        <tbody id="license-keys-tbody" class="lkbi-body">
            <!-- Table data here -->
        </tbody>
    </table>
    <p id="no-license-found-message" style="display:none; text-align:center;">No license keys found.</p>
</div>
<div id="pagination-container"></div>
<div id="toast-container" class="toast-container"></div>