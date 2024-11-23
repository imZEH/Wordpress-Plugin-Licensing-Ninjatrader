(function () {
    var variablesArray = []; // Array to store the values
    var variablesSet = new Set(); // Use a Set to store unique variable names

    jQuery(document).ready(function ($) {
        bot_close_model($);
        bot_modal($);
        add_bot_variable($);
        form_submit($);
        fetch_bots_data($);
        edit_data($);
        search_bots($);
        reset_search($);
        delete_data($);
        update_bot_status($);
    });

    function bot_close_model($) {
        $('#lkbi-bot-close_modal').on('click', function () {
            $('#lkib-bot-modal').hide(); // Hide the modal (you can also add fadeOut for smooth closing)
        });
    }

    function bot_modal($) {
        // When the "Add License" button is clicked, show the modal
        $('#lkbi-bot-modal-action').on('click', function (e) {

            variablesArray = [];

            $('#id').val('');
            $('#bot_name').val('');
            $('#bot_platform').val('');
            $('#strategy').val('');
            $('#bot-status').val('Active');

            $('#bot_variable_container').empty();

            addVariable($);

            e.preventDefault();
            $('#lkib-bot-modal').fadeIn(); // Show the modal
        });

        // When the "close" button (×) is clicked, hide the modal
        $('.lkib-bot-modal-close-btn').on('click', function () {
            $('#lkib-bot-modal').fadeOut(); // Hide the modal
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
        $("#bot_variable_container").append(content);

        // Initialize the variable in the array with an empty value
        variablesArray.push("");

        console.log("Variables Array after Addition:", variablesArray); // Log the array for debugging
    }

    function add_bot_variable($) {
        // Add variable on button click
        $("#lkbi-add-bot-variable").click(function () {
            // Add the new variable block
            addVariable($);
        });

        // Attach event listener for deleting variables
        $("#bot_variable_container").on("click", ".delete-icon", function () {
            const parentDiv = $(this).closest(".variable-item"); // Get the parent container
            const index = parentDiv.index(); // Get the index of the variable
            parentDiv.remove();
        });
        
    }

    function form_submit($) {
        $('#lkbi-bot-submit').on('click', function (e) {
            e.preventDefault(); // Prevent default form submission
            // Gather the form data

            const resultObject = {};
            let hasError = false;
            variablesSet = new Set();

            $("#bot_variable_container .variable-item").each(function () {
                const variableNameField = $(this).find(".variable-name");
                const errorMessage = $(this).find(".error-message");
            
                // Reset border color and hide error message
                variableNameField.css("border-color", "");
                errorMessage.hide();
            });

            $("#bot_variable_container .variable-item").each(function () {
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
                bot_name: $('#bot_name').val(),
                platform: $('#bot_platform').val(),
                strategy: $('#strategy').val(),
                status: $('#bot-status').val(),
                bot_variables: resultObject,
            };

            // Send the data to the REST API
            $.ajax({
                url: '/wp-json/v1/add_bots', // REST API endpoint
                method: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        if ($('#id').val() == null) {
                            showToast($, 'Successfully saved', 'success');
                        } else {
                            showToast($, 'Successfully updated', 'success');
                        }
                        $('#lkib-bot-modal').fadeOut();
                        fetch_bots_data($);
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
            $('#lkib-bot-modal').fadeOut();
        });
    }

    function search_bots($, page = 1, per_page = 10) {
        $('.lkbi-bot-btn-search').on('click', function () {
            $('.spinner-container').show();
            var keyword = $('#bot-keyword').val();
            $.ajax({
                url: `/wp-json/v1/search_internal_bots?keyword=${keyword}&page=${page}&per_page=${per_page}`,
                method: 'GET',
                success: function (response) {
                    // Clear any existing rows in the table
                    $('#lkbi-bots-tbody').empty();

                    if (response.data.length === 0) {
                        // If no license keys, show a message in the table
                        $('#no-bot-found-message').show();
                        $('#bot-pagination-container').hide();
                    } else {
                        // Loop through the response and create table rows
                        response.data.forEach(function (item) {
                            var isChecked = item.status === 'Active' ? 'checked' : '';
                            var row = `<tr data-item='${JSON.stringify(item)}'>
                                <td>${item.id}</td>
                                <td>${item.bot_name}</td>
                                <td>${item.platform}</td>
                                <td>${item.strategy}</td>
                                <td>
                                    <label class="lkbi-switch status-toggle">
                                        <input type="checkbox" class="lkbi-status-toggle-checkbox" data-id="${item.id}" ${isChecked}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <a class="edit-bot-btn" href="#" style="color: #0073aa; text-decoration: none;">
                                        <i class="dashicons dashicons-edit" style="font-size: 16px;"></i> Edit
                                    </a>
                                    <a class="a-delete-bot-btn" href="#" data-id="${item.id}" style="color: #d63638; text-decoration: none;">
                                        <i class="dashicons dashicons-trash" style="font-size: 16px;"></i> Delete
                                    </a>
                                </td>
                            </tr>`;
                            // Append the row to the tbody
                            $('#lkbi-bots-tbody').append(row);
                        });

                        $('#no-bot-found-message').hide();
                        $('#bot-pagination-container').show();
                        // Render pagination
                        renderPagination($, response.pagination, page, per_page);
                    }


                    $('.spinner-container').hide();
                },
                error: function (error) {
                    $('.spinner-container').hide();
                    $('#no-bot-found-message').hide();
                    showToast($, 'Error fetching license data:', error);
                }
            });
        });
    }

    function fetch_bots_data($, page = 1, per_page = 10) {
        $('.spinner-container').show();
        $.ajax({
            url: `/wp-json/v1/get_bots?page=${page}&per_page=${per_page}`,
            method: 'GET',
            success: function (response) {
                // Clear any existing rows in the table
                $('#lkbi-bots-tbody').empty();
    
                if (response.data.length === 0) {
                    // If no license keys, show a message in the table
                    $('#no-bot-found-message').show();
                    $('#bot-pagination-container').hide();
                } else {
                    // Loop through the response and create table rows
                    response.data.forEach(function (item) {
                        var isChecked = item.status === 'Active' ? 'checked' : '';
                        var row = `<tr data-item='${JSON.stringify(item)}'>
                            <td>${item.id}</td>
                            <td>${item.bot_name}</td>
                            <td>${item.platform}</td>
                            <td>${item.strategy}</td>
                            <td>
                                <label class="lkbi-switch status-toggle">
                                    <input type="checkbox" class="lkbi-status-toggle-checkbox" data-id="${item.id}" ${isChecked}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <a class="edit-bot-btn" href="#" style="color: #0073aa; text-decoration: none;">
                                    <i class="dashicons dashicons-edit" style="font-size: 16px;"></i> Edit
                                </a>
                                <a class="a-delete-bot-btn" href="#" data-id="${item.id}" style="color: #d63638; text-decoration: none;">
                                    <i class="dashicons dashicons-trash" style="font-size: 16px;"></i> Delete
                                </a>
                            </td>
                        </tr>`;
                        // Append the row to the tbody
                        $('#lkbi-bots-tbody').append(row);
                    });
    
                    $('#no-bot-found-message').hide();
                    $('#bot-pagination-container').show();
                    // Render pagination
                    renderPagination($, response.pagination, page, per_page);
                }
    
                $('.spinner-container').hide();
            },
            error: function (error) {
                $('.spinner-container').hide();
                $('#no-bot-found-message').hide();
                showToast($, 'Error fetching license data:', error);
            }
        });
    }
    
    function reset_search($) {
        $('.lkbi-bot-btn-reset').on('click', function () {
            $('#bot-keyword').val('');
            fetch_bots_data($);
        });
    }

    function edit_data($) {
        $(document).on('click', '.edit-bot-btn', function (e) {
            e.preventDefault();
    
            // Get the data associated with the clicked row (bot data)
            var data = JSON.parse($(this).closest('tr').attr('data-item'));
    
            // Populate the bot details in the modal
            $('#id').val(data.id);
            $('#bot_name').val(data.bot_name);
            $('#bot_platform').val(data.platform);
            $('#strategy').val(data.strategy);
            $('#bot-status').val(data.status);
    
            // Clear existing Bot Variables in the modal (if any)
            $('#bot_variable_container').empty();
    
            // If bot variables exist in the data, use them; otherwise, initialize with an empty object
            let variablesObject = data.bot_variables ? data.bot_variables : {};
    
            // Iterate over the bot_variables object and create corresponding input elements for each variable
            Object.keys(variablesObject).forEach(function(variableName) {
                const variableValue = variablesObject[variableName];
    
                // Generate each bot variable field (with name and value)
                const variableHTML = `
                    <div class="variable-item" style="display: flex; align-items: center; gap: 20px;">
                        <input type="text" name="variable" class="variable-name" value="${variableName}" placeholder="Variable Name">
                        <input type="text" name="value" class="variable-value" value="${variableValue}" placeholder="Value">
                        <span class="delete-icon" style="cursor: pointer; color: red; font-size: 20px; margin-left: 10px;">&times;</span>
                    </div>
                `;
                // Append the variable input fields to the container
                $('#bot_variable_container').append(variableHTML);
            });
    
            // Show the modal
            $('#lkib-bot-modal').fadeIn();
        });
    }
    

    function delete_data($) {
        $(document).on('click', '.a-delete-bot-btn', function (e) {
            console.log("test");
            e.preventDefault();
            const recordId = $(this).data('id');

            $('#delete_id').val(recordId); // Set the ID in hidden input field
            $('#lkib-delete-bot-modal').show(); // Show delete confirmation modal
        });

        // Close modal when close button is clicked
        $('.lkib-bot-modal-close-btn, #bot_cancel_delete').on('click', function () {
            $('#lkib-delete-bot-modal').hide();
        });

        // Confirm delete
        $('#bot_confirm_delete').on('click', function () {
            const recordId = $('#delete_id').val();

            // AJAX request to delete record
            $.ajax({
                url: `/wp-json/v1/delete_bots/${recordId}`,
                method: 'DELETE',
                success: function (response) {
                    showToast($, 'Record deleted successfully.', 'success');
                    fetch_bots_data($);
                    $('#lkib-delete-bot-modal').hide();
                },
                error: function (error) {
                    showToast($, 'Failed to delete the record.', 'error');
                }
            });
        });
    }

    function update_bot_status($){
        $(document).on('change', '.lkbi-status-toggle-checkbox', function () {
            var botId = $(this).data('id');
            var newStatus = $(this).is(':checked') ? 'Active' : 'Inactive';
            
            var $row = $(this).closest('tr'); // Get the closest table row
            var data = JSON.parse($(this).closest('tr').attr('data-item'));
            data['status'] = newStatus;
            $row.attr('data-item', JSON.stringify(data));

        
            // Make an AJAX request to update the status
            $.ajax({
                url: `/wp-json/v1/update_bot_status/${botId}`,
                method: 'POST',
                data: {
                    status: newStatus
                },
                success: function () {
                    
                    showToast($, 'Bot status updated successfully.');
                },
                error: function () {
                    showToast($, 'Failed to update bot status.', 'error');
                }
            });
        });
        
    }

    function showToast($, message, type = 'success') {
        var toast = $('<div class="toast ' + type + '">' + message + '</div>'); // Create toast with the given type
        $('#bot-toast-container').append(toast); // Append it to the container

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
        const paginationContainer = $('#bot-pagination-container');

        // Clear any existing pagination
        paginationContainer.empty();

        // Previous button
        const prevPage = currentPage > 1 ? currentPage - 1 : 1;
        paginationContainer.append(`
            <button class="bot-pagination-btn" data-page="${prevPage}">Previous</button>
        `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationContainer.append(`
                <button class="bot-pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>
            `);
        }

        // Next button
        const nextPage = currentPage < totalPages ? currentPage + 1 : totalPages;
        paginationContainer.append(`
            <button class="bot-pagination-btn" data-page="${nextPage}">Next</button>
        `);

        // Handle page click
        $('.bot-pagination-btn').click(function () {
            const page = $(this).data('page');
            fetch_bots_data($, page, perPage);
        });
    }
})();