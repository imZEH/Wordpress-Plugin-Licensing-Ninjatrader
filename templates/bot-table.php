<div class="wp-style-button-container">
    <div class="lkbi-search-property">
        <input type="text" placeholder="Search Bot" id="bot-keyword" name="bot-keyword">
        <button class="lkbi-bot-btn-search">Search</button>
        <button class="lkbi-bot-btn-reset">Reset</button>
    </div>


    <button id="lkbi-bot-modal-action" class="wp-style-button">Add Bot</button>
</div>
<div class="table-container" style="position: relative;">
    <div class="spinner-container" style="display: none;">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../assets/img/spinner.gif'; ?>" alt="Loading..." style="width: 100px;">
    </div>

    <table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Bots</th>
                <th>Platform</th>
                <th>Strategy</th>
                <th style="width: 60px;">Status</th>
                <th style="width: 110px;"></th>
            </tr>
        </thead>
        <tbody id="lkbi-bots-tbody">
            <!-- Table data here -->
        </tbody>
    </table>
    <p id="no-bot-found-message" style="display:none; text-align:center;">No bots data found.</p>
</div>
<div id="bot-pagination-container"></div>
<div id="bot-toast-container" class="toast-container"></div>