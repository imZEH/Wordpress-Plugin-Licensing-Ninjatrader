<div id="lkib-indicator-modal" class="lkib-modal">
    <div class="lkib-modal-content">
        <span class="lkib-indicator-modal-close-btn">&times;</span>
        <h3>Add Indicator</h3>
        <form class="lkib-form">
            <div class="lkib-form-column">

                <input type="hidden" id="id" name="id">

                <label for="indicator_name">Name:</label>
                <input type="text" id="indicator_name" name="indicator_name">

                <label for="indicator_platform">Platform:</label>
                <input type="text" id="indicator_platform" name="indicator_platform">

                <label for="indicator-strategy">Strategy:</label>
                <input type="text" id="indicator-strategy" name="indicator-strategy">

                <label for="status">Status:</label>
                <select id="indicator-status" name="status" class="lkbi-status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <label for="">Indicator Variables:</label>
                <div id="indicator_variable_container">
                    
                </div>
                <div class="lkbi-indicator-variable-container">
                    <button type="button" id="lkbi-add-indicator-variable" class="indicator-variable-btn" style="width: 35% !important;">Add Indicator Variable</button>
                </div>
                
            </div>

            <div class="lkib-form-actions">
                <button type="button" id="lkbi-indicator-close_modal" class="close-btn">Close</button>
                <button type="button" id="lkbi-indicator-submit" class="submit-btn">Submit</button>
            </div>
        </form>
    </div>
</div>


<div id="lkib-delete-indicator-modal" class="lkib-modal">
    <div class="lkib-modal-content" style="width: 400px;">
        <span class="lkib-indicator-modal-close-btn">&times;</span>
        <h3>Confirm Delete</h3>
        <h4>Are you sure you want to proceed?</h4>
        <div class="lkib-form-column">
            <input type="hidden" id="delete_id" name="delete_id">
            <div class="lkib-form-actions">
                <button type="button" id="indicator_cancel_delete" class="close-btn">No</button>
                <button type="button" id="indicator_confirm_delete" class="delete-btn">Yes</button>
            </div>
        </div>
    </div>
</div>