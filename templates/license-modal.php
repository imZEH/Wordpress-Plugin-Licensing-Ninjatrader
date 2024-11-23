<div id="lkib-license-modal" class="lkib-modal">
    <div class="lkib-modal-content">
        <span class="lkib-modal-close-btn">&times;</span>
        <h3>Add License Key</h3>
        <form class="lkib-form">
            <div class="lkib-form-column">

                <input type="hidden" id="id" name="id">

                <!-- Column 1 -->
                <label for="customer_search">Customer:</label>
                <input type="text" id="customer_search" name="customer_search" placeholder="Search by name or email">
                <div id="customer_results"></div>

                <!-- Hidden fields for selected customer name and email -->
                <input type="hidden" id="customer_id" name="customer_id">
                <input type="hidden" id="customer_name" name="customer_name">
                <input type="hidden" id="customer_email" name="customer_email">

                <label for="product_search">Product:</label>
                <input type="text" id="product_search" name="product_search" placeholder="Search for products">
                <div id="product_results"></div>

                <!-- Hidden fields for selected customer name and email -->
                <input type="hidden" id="product_id" name="product_id">
                <input type="hidden" id="variation_id" name="variation_id">
                <input type="hidden" id="product_name" name="product_name">
                <input type="hidden" id="product_slug" name="product_slug">
                <input type="hidden" id="platform" name="platform">

                <label for="machine_id">Machine ID:</label>
                <input type="text" id="machine_id" name="machine_id">


            </div>

            <div class="lkib-form-column">
                <!-- Column 2 -->

                <label for="license_key">
                    License Key: (
                    <span>
                        <label for="auto_generate" id="auto_generate_label">Auto Generate?</label>
                        <input type="checkbox" id="auto_generate" name="auto_generate">
                    </span>
                    )
                </label>
                <input type="text" id="license_key" name="license_key">

                <label for="no_of_devices">No of Devices:</label>
                <input type="number" id="no_of_devices" name="no_of_devices">

                <!-- <label for="platform">Platform:</label>
                <select id="platform" name="platform">
                    <option value="NinjaTrader">NinjaTrader</option>
                    <option value="MT5">MT5</option>
                </select> -->

                <label for="status">Status:</label>
                <select id="status" class="lkbi-status" name="status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <div class="lkib-form-actions">
                <button type="button" id="close_modal" class="close-btn">Close</button>
                <button type="button" id="submit_license" class="submit-btn">Submit</button>
            </div>
        </form>
    </div>
</div>


<div id="lkib-delete-modal" class="lkib-modal">
    <div class="lkib-modal-content" style="width: 400px;">
        <span class="lkib-modal-close-btn">&times;</span>
        <h3>Confirm Delete</h3>
        <h4>Are you sure you want to proceed?</h4>
        <div class="lkib-form-column">
            <input type="hidden" id="delete_id" name="delete_id">
            <div class="lkib-form-actions">
                <button type="button" id="cancel_delete" class="close-btn">No</button>
                <button type="button" id="confirm_delete" class="delete-btn">Yes</button>
            </div>
        </div>
    </div>
</div>