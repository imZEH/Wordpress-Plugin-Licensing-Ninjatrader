<div class="wp-style-button-container">
    <div class="lkbi-search-property">
        <input type="text" placeholder="Search Indicator" id="indicator-keyword" name="indicator-keyword">
        <button class="lkbi-indicator-btn-search">Search</button>
        <button class="lkbi-indicator-btn-reset">Reset</button>
    </div>


    <button id="lkbi-indicator-modal-action" class="wp-style-button">Add Indicator</button>
</div>
<div class="table-container" style="position: relative;">
    <div class="spinner-container" style="display: none;">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../assets/img/spinner.gif'; ?>" alt="Loading..." style="width: 100px;">
    </div>

    <table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Indicators</th>
                <th>Platform</th>
                <th>Strategy</th>
                <th style="width: 60px;">Status</th>
                <th style="width: 110px;"></th>
            </tr>
        </thead>
        <tbody id="lkbi-indicator-tbody">
            <!-- Table data here -->
        </tbody>
    </table>
    <p id="no-indicator-found-message" style="display:none; text-align:center;">No indicators data found.</p>
</div>
<div id="indicator-pagination-container"></div>
<div id="indicator-toast-container" class="toast-container"></div>