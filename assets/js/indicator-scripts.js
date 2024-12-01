(function () {
    var variablesArray = []; // Array to store the values
    var variablesSet = new Set(); // Use a Set to store unique variable names

    jQuery(document).ready(function ($) {
        indicator_close_model($);
        indicator_modal($);
        add_indicator_variable($);
        form_submit($);
        fetch_indicator_data($);
        edit_data($);
        search_indicators($);
        reset_search($);
        delete_data($);
        update_indicator_status($);
    });

    function indicator_close_model($) {
        $('#lkbi-indicator-close_modal').on('click', function () {
            $('#lkib-indicator-modal').hide(); // Hide the modal (you can also add fadeOut for smooth closing)
        });
    }

    function indicator_modal($) {
        // When the "Add License" button is clicked, show the modal
        $('#lkbi-indicator-modal-action').on('click', function (e) {

            variablesArray = [];

            $('#id').val('');
            $('#indicator_name').val('');
            $('#indicator_platform').val('');
            $('#indicator-strategy').val('');
            $('#indicator-status').val('Active');

            $('#indicator_variable_container').empty();

            addVariable($);

            e.preventDefault();
            $('#lkib-indicator-modal').fadeIn(); // Show the modal
        });

        // When the "close" button (×) is clicked, hide the modal
        $('.lkib-indicator-modal-close-btn').on('click', function () {
            $('#lkib-indicator-modal').fadeOut(); // Hide the modal
        });
    }

    function addVariable($) {
        const content = `
            <div class="variable-item" style="display: flex; flex-direction: column; gap: 5px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <input type="text" name="variable" placeholder="Variable Name" class="variable-name">
                    <input type="text" name="value" placeholder="Value" class="variable-value">
                    <span class="delete-icon" style="cursor: pointer; color: red; font-size: 30px; display: flex; justify-content: center; width: 30px; height: 30px;">&times;</span>
                </div>
                <small class="error-message" style="color: red; display: none;">Duplicate variable name detected</small>
            </div>
        `;
        $("#indicator_variable_container").append(content);

        // Initialize the variable in the array with an empty value
        variablesArray.push("");

        console.log("Variables Array after Addition:", variablesArray); // Log the array for debugging
    }

    function add_indicator_variable($) {
        // Add variable on button click
        $("#lkbi-add-indicator-variable").click(function () {
            // Add the new variable block
            addVariable($);
        });

        // Attach event listener for deleting variables
        $("#indicator_variable_container").on("click", ".delete-icon", function () {
            const parentDiv = $(this).closest(".variable-item"); // Get the parent container
            const index = parentDiv.index(); // Get the index of the variable
            parentDiv.remove();
        });
        
    }

    function form_submit($) {
        $('#lkbi-indicator-submit').on('click', function (e) {
            e.preventDefault(); // Prevent default form submission
            // Gather the form data

            const resultObject = {};
            let hasError = false;
            variablesSet = new Set();

            $("#indicator_variable_container .variable-item").each(function () {
                const variableNameField = $(this).find(".variable-name");
                const errorMessage = $(this).find(".error-message");
            
                // Reset border color and hide error message
                variableNameField.css("border-color", "");
                errorMessage.hide();
            });

            $("#indicator_variable_container .variable-item").each(function () {
                const variableNameField = $(this).find(".variable-name");
                const variableName = variableNameField.val().trim();
                const variableValue = $(this).find(".variable-value").val().trim();
    
                console.log(variableName);
                if (variableName) {
                    // Check for duplication
                    if (variablesSet.has(variableName)) {
                        hasError = true;
    
                        // Highlight the field with red border and show the error message
                        variableNameField.css("border-color", "red");
                        $(this).find(".error-message").show();
    
                    } else {
                        // Add to the Set and the object
                        variablesSet.add(variableName);
                        resultObject[variableName] = variableValue;
                    }
                }
            });

            console.log(resultObject);
            console.log(hasError);
            if(hasError){
                return;
            }

            console.log(resultObject);

            var formData = {
                id: $('#id').val(),
                indicator_name: $('#indicator_name').val(),
                platform: $('#indicator_platform').val(),
                strategy: $('#strategy').val(),
                status: $('#indicator-status').val(),
                indicator_variables: resultObject,
            };

            // Send the data to the REST API
            $.ajax({
                url: '/wp-json/v1/add_indicators', // REST API endpoint
                method: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        if ($('#id').val() == null) {
                            showToast($, 'Successfully saved', 'success');
                        } else {
                            showToast($, 'Successfully updated', 'success');
                        }
                        $('#lkib-indicator-modal').fadeOut();
                        fetch_indicator_data($);
                    } else {
                        showToast($, 'Failed to submit license key: ' + response.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    showToast($, 'There was an error submitting the license key.', 'error');
                }
            });
        });

        // When the close button is clicked, close the modal
        $('#close_modal').on('click', function () {
            $('#lkib-indicator-modal').fadeOut();
        });
    }

    function search_indicators($, page = 1, per_page = 10) {
        $('.lkbi-indicator-btn-search').on('click', function () {
            $('.spinner-container').show();
            var keyword = $('#indicator-keyword').val();
            $.ajax({
                url: `/wp-json/v1/search_internal_indicators?keyword=${keyword}&page=${page}&per_page=${per_page}`,
                method: 'GET',
                success: function (response) {
                    // Clear any existing rows in the table
                    $('#lkbi-indicator-tbody').empty();

                    if (response.data.length === 0) {
                        // If no license keys, show a message in the table
                        $('#no-indicator-found-message').show();
                        $('#indicator-pagination-container').hide();
                    } else {
                        // Loop through the response and create table rows
                        response.data.forEach(function (item) {
                            var isChecked = item.status === 'Active' ? 'checked' : '';
                            var row = `<tr data-item='${JSON.stringify(item)}'>
                                <td>${item.id}</td>
                                <td>${item.indicator_name}</td>
                                <td>${item.platform}</td>
                                <td>${item.strategy}</td>
                                <td>
                                    <label class="lkbi-switch status-toggle">
                                        <input type="checkbox" class="lkbi-status-toggle-checkbox" data-id="${item.id}" ${isChecked}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <a class="edit-indicator-btn" href="#" style="color: #0073aa; text-decoration: none;">
                                        <i class="dashicons dashicons-edit" style="font-size: 16px;"></i> Edit
                                    </a>
                                    <a class="a-delete-indicator-btn" href="#" data-id="${item.id}" style="color: #d63638; text-decoration: none;">
                                        <i class="dashicons dashicons-trash" style="font-size: 16px;"></i> Delete
                                    </a>
                                </td>
                            </tr>`;
                            // Append the row to the tbody
                            $('#lkbi-indicator-tbody').append(row);
                        });

                        $('#no-indicator-found-message').hide();
                        $('#indicator-pagination-container').show();
                        // Render pagination
                        renderPagination($, response.pagination, page, per_page);
                    }


                    $('.spinner-container').hide();
                },
                error: function (error) {
                    $('.spinner-container').hide();
                    $('#no-indicator-found-message').hide();
                    showToast($, 'Error fetching license data:', error);
                }
            });
        });
    }

    function fetch_indicator_data($, page = 1, per_page = 10) {
        $('.spinner-container').show();
        $.ajax({
            url: `/wp-json/v1/get_indicators?page=${page}&per_page=${per_page}`,
            method: 'GET',
            success: function (response) {
                // Clear any existing rows in the table
                $('#lkbi-indicator-tbody').empty();
    
                if (response.data.length === 0) {
                    // If no license keys, show a message in the table
                    $('#no-indicator-found-message').show();
                    $('#indicator-pagination-container').hide();
                } else {
                    // Loop through the response and create table rows
                    response.data.forEach(function (item) {
                        var isChecked = item.status === 'Active' ? 'checked' : '';
                        var row = `<tr data-item='${JSON.stringify(item)}'>
                            <td>${item.id}</td>
                            <td>${item.indicator_name}</td>
                            <td>${item.platform}</td>
                            <td>${item.strategy}</td>
                            <td>
                                <label class="lkbi-switch status-toggle">
                                    <input type="checkbox" class="lkbi-status-toggle-checkbox" data-id="${item.id}" ${isChecked}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <a class="edit-indicator-btn" href="#" style="color: #0073aa; text-decoration: none;">
                                    <i class="dashicons dashicons-edit" style="font-size: 16px;"></i> Edit
                                </a>
                                <a class="a-delete-indicator-btn" href="#" data-id="${item.id}" style="color: #d63638; text-decoration: none;">
                                    <i class="dashicons dashicons-trash" style="font-size: 16px;"></i> Delete
                                </a>
                            </td>
                        </tr>`;
                        // Append the row to the tbody
                        $('#lkbi-indicator-tbody').append(row);
                    });
    
                    $('#no-indicator-found-message').hide();
                    $('#indicator-pagination-container').show();
                    // Render pagination
                    renderPagination($, response.pagination, page, per_page);
                }
    
                $('.spinner-container').hide();
            },
            error: function (error) {
                $('.spinner-container').hide();
                $('#no-indicator-found-message').hide();
                showToast($, 'Error fetching license data:', error);
            }
        });
    }
    
    function reset_search($) {
        $('.lkbi-indicator-btn-reset').on('click', function () {
            $('#indicator-keyword').val('');
            fetch_indicator_data($);
        });
    }

    function edit_data($) {
        $(document).on('click', '.edit-indicator-btn', function (e) {
            e.preventDefault();
    
            // Get the data associated with the clicked row (indicator data)
            var data = JSON.parse($(this).closest('tr').attr('data-item'));
    
            // Populate the indicator details in the modal
            $('#id').val(data.id);
            $('#indicator_name').val(data.indicator_name);
            $('#indicator_platform').val(data.platform);
            $('#indicator-strategy').val(data.strategy);
            $('#indicator-status').val(data.status);
    
            // Clear existing Indicator Variables in the modal (if any)
            $('#indicator_variable_container').empty();
    
            // If indicator variables exist in the data, use them; otherwise, initialize with an empty object
            let variablesObject = data.indicator_variables ? data.indicator_variables : {};
    
            // Iterate over the indicator_variables object and create corresponding input elements for each variable
            Object.keys(variablesObject).forEach(function(variableName) {
                const variableValue = variablesObject[variableName];
    
                // Generate each indicator variable field (with name and value)
                const variableHTML = `
                    <div class="variable-item" style="display: flex; align-items: center; gap: 20px;">
                        <input type="text" name="variable" class="variable-name" value="${variableName}" placeholder="Variable Name">
                        <input type="text" name="value" class="variable-value" value="${variableValue}" placeholder="Value">
                        <span class="delete-icon" style="cursor: pointer; color: red; font-size: 20px; margin-left: 10px;">&times;</span>
                    </div>
                `;
                // Append the variable input fields to the container
                $('#indicator_variable_container').append(variableHTML);
            });
    
            // Show the modal
            $('#lkib-indicator-modal').fadeIn();
        });
    }
    

    function delete_data($) {
        $(document).on('click', '.a-delete-indicator-btn', function (e) {
            console.log("test");
            e.preventDefault();
            const recordId = $(this).data('id');

            $('#delete_id').val(recordId); // Set the ID in hidden input field
            $('#lkib-delete-indicator-modal').show(); // Show delete confirmation modal
        });

        // Close modal when close button is clicked
        $('.lkib-indicator-modal-close-btn, #indicator_cancel_delete').on('click', function () {
            $('#lkib-delete-indicator-modal').hide();
        });

        // Confirm delete
        $('#indicator_confirm_delete').on('click', function () {
            const recordId = $('#delete_id').val();

            // AJAX request to delete record
            $.ajax({
                url: `/wp-json/v1/delete_indicators/${recordId}`,
                method: 'DELETE',
                success: function (response) {
                    showToast($, 'Record deleted successfully.', 'success');
                    fetch_indicator_data($);
                    $('#lkib-delete-indicator-modal').hide();
                },
                error: function (error) {
                    showToast($, 'Failed to delete the record.', 'error');
                }
            });
        });
    }

    function update_indicator_status($){
        $(document).on('change', '.lkbi-status-toggle-checkbox', function () {
            var indicatorId = $(this).data('id');
            var newStatus = $(this).is(':checked') ? 'Active' : 'Inactive';
            
            var $row = $(this).closest('tr'); // Get the closest table row
            var data = JSON.parse($(this).closest('tr').attr('data-item'));
            data['status'] = newStatus;
            $row.attr('data-item', JSON.stringify(data));

        
            // Make an AJAX request to update the status
            $.ajax({
                url: `/wp-json/v1/update_indicator_status/${indicatorId}`,
                method: 'POST',
                data: {
                    status: newStatus
                },
                success: function () {
                    
                    showToast($, 'Indicator status updated successfully.');
                },
                error: function () {
                    showToast($, 'Failed to update indicator status.', 'error');
                }
            });
        });
        
    }

    function showToast($, message, type = 'success') {
        var toast = $('<div class="toast ' + type + '">' + message + '</div>'); // Create toast with the given type
        $('#indicator-toast-container').append(toast); // Append it to the container

        // Add a click event listener to close the toast when clicked
        toast.on('click', function () {
            $(this).fadeOut(function () {
                $(this).remove(); // Remove the toast after fading out
            });
        });

        // Remove the toast after 5 seconds if it hasn't been clicked
        setTimeout(function () {
            if (toast.is(':visible')) {
                toast.fadeOut(function () {
                    $(this).remove(); // Remove the toast after the animation
                });
            }
        }, 5000); // Show the toast for 5 seconds
    }

    function renderPagination($, pagination, currentPage, perPage) {
        const totalPages = pagination.total_pages;
        const paginationContainer = $('#indicator-pagination-container');

        // Clear any existing pagination
        paginationContainer.empty();

        // Previous button
        const prevPage = currentPage > 1 ? currentPage - 1 : 1;
        paginationContainer.append(`
            <button class="indicator-pagination-btn" data-page="${prevPage}"><</button>
        `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationContainer.append(`
                <button class="indicator-pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>
            `);
        }

        // Next button
        const nextPage = currentPage < totalPages ? currentPage + 1 : totalPages;
        paginationContainer.append(`
            <button class="indicator-pagination-btn" data-page="${nextPage}">></button>
        `);

        // Handle page click
        $('.indicator-pagination-btn').click(function () {
            const page = $(this).data('page');
            fetch_indicator_data($, page, perPage);
        });
    }
})();