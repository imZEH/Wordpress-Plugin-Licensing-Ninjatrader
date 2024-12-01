<div class="wp-style-button-container">
    <div class="lkbi-user-trade-search-property">
        <input type="text" placeholder="Search User" id="user-trade-keyword" name="user-trade-keyword">
        <button class="lkbi-user-trade-btn-search">Search</button>
        <button class="lkbi-user-trade-btn-reset">Refresh</button>
    </div>
    <div id="lkbi-table-2" style="width:100%; display: none;">
        <div class="lkbi-header">
            <!-- Left Section -->
            <div class="lkbi-left-section">
                
                <div style="margin-top: 10px;">
                    <p style="margin: 5px 0; font-size:large;"><strong>User:</strong> Neil Ragadio</p>
                    <p style="margin: 5px 0; font-size:large;"><strong>Product:</strong> Changing Tides</p>
                </div>
            </div>
            <!-- Right Section -->
            <div class="lkbi-right-section">
                <!-- Symbol Filter -->
                <div>
                    <label for="lkbi-symbol-dropdown" style="font-weight: bold; margin-right: 5px;">Symbol:</label>
                    <select id="lkbi-symbol-dropdown" name="symbol" style="padding: 5px; border: 1px solid #ccc; border-radius: 3px; min-width: 150px;">
                        <option value="symbol1">Symbol 1</option>
                        <option value="symbol2">Symbol 2</option>
                        <option value="symbol3">Symbol 3</option>
                    </select>
                </div>
                <!-- Date Range Filter -->
                <div>
                    <label for="start-date" style="font-weight: bold; margin-right: 5px;">Date Range:</label>
                    <input type="date" id="lkbi-start-date" name="start-date" style="padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                    <span style="margin: 0 5px;">to</span>
                    <input type="date" id="lkbi-end-date" name="end-date" style="padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                </div>

                <button id="lkbi-apply-btn" class="lkbi-apply-btn">
                    Apply
                </button>
                <button id="lkbi-back-btn" class="lkbi-back-btn">
                    Back
                </button>
            </div>
        </div>
    </div>


</div>
<div class="table-container" style="position: relative;">
    <div class="spinner-container" style="display: none;">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../assets/img/spinner.gif'; ?>" alt="Loading..." style="width: 100px;">
    </div>
    <table id="lkbi-user-trade-summary" class="lkbi-table" style="margin-top:20px;">
        <thead>
            <tr>
                <th>User</th>
                <th>Bot</th>
                <th>Symbol</th>
                <th>Open Timestamp</th>
                <th>Close Timestamp</th>
                <th>Pnl</th>
                <th>Total Execution</th>
            </tr>
        </thead>
        <tbody id="lkbi-user-trade-tbody" class="lkbi-body">
            <!-- Table data here -->
        </tbody>
    </table>

    <table id="lkbi-user-trade-execution-summary" class="lkbi-table" style="margin-top:20px;display:none;">
        <thead>
            <tr>
                <th>Symbol</th>
                <th>Open Timestamp</th>
                <th>Close Timestamp</th>
                <th>Position</th>
                <th>Pnl</th>
                <th>Total Execution</th>
                <th>Total Duration</th>
                <th>Open Price</th>
                <th>Close Price</th>
                <th>Date Created</th>
            </tr>
        </thead>
        <tbody id="lkbi-user-trade-execution-summary-tbody" class="lkbi-body">
            <!-- Table data here -->
        </tbody>
    </table>
    <p id="no-user-trade-data-found-message" style="display:none; text-align:center;">No user trade data found.</p>
</div>
<div id="user-trade-pagination-container"></div>
<div id="trade-execution-summary-pagination-container"></div>
<div id="user-trade-toast-container" class="toast-container"></div>