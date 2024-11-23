<div id="lkib-bot-modal" class="lkib-modal">
    <div class="lkib-modal-content">
        <span class="lkib-bot-modal-close-btn">&times;</span>
        <h3>Add Bot</h3>
        <form class="lkib-form">
            <div class="lkib-form-column">

                <input type="hidden" id="id" name="id">

                <label for="bot_name">Name:</label>
                <input type="text" id="bot_name" name="bot_name">

                <label for="bot_platform">Platform:</label>
                <input type="text" id="bot_platform" name="bot_platform">

                <label for="strategy">Strategy:</label>
                <input type="text" id="strategy" name="strategy">

                <label for="status">Status:</label>
                <select id="bot-status" name="status" class="lkbi-status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <label for="">Bot Variables:</label>
                <div id="bot_variable_container">
                    
                </div>
                <div class="lkbi-bot-variable-container">
                    <button type="button" id="lkbi-add-bot-variable" class="bot-variable-btn">Add Bot Variable</button>
                </div>
                
            </div>

            <div class="lkib-form-actions">
                <button type="button" id="lkbi-bot-close_modal" class="close-btn">Close</button>
                <button type="button" id="lkbi-bot-submit" class="submit-btn">Submit</button>
            </div>
        </form>
    </div>
</div>


<div id="lkib-delete-bot-modal" class="lkib-modal">
    <div class="lkib-modal-content" style="width: 400px;">
        <span class="lkib-bot-modal-close-btn">&times;</span>
        <h3>Confirm Delete</h3>
        <h4>Are you sure you want to proceed?</h4>
        <div class="lkib-form-column">
            <input type="hidden" id="delete_id" name="delete_id">
            <div class="lkib-form-actions">
                <button type="button" id="bot_cancel_delete" class="close-btn">No</button>
                <button type="button" id="bot_confirm_delete" class="delete-btn">Yes</button>
            </div>
        </div>
    </div>
</div>