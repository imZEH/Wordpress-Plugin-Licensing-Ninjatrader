(function () {
    jQuery(document).ready(function ($) {
        tab_navigation($);
        modal($);
        search_customer($);
        toggle($);
        search_product($);
        close_model($);
        form_submit($);
        edit_data($);
        delete_data($);
        fetch_license_data($);
        search_license_key($);
        reset_search($);
    });

    function tab_navigation($) {
        $('.lkbi-tab').on('click', function (e) {
            e.preventDefault();

            // Remove active classes
            $('.lkbi-tab').removeClass('nav-tab-active');
            $('.tab-content').removeClass('active');

            // Add active classes to the clicked tab and related content
            $(this).addClass('nav-tab-active');
            $($(this).attr('href')).addClass('active');
        });
    }

    function close_model($) {
        $('#close_modal').on('click', function () {
            $('#lkib-license-modal').hide(); // Hide the modal (you can also add fadeOut for smooth closing)
        });
    }

    function toggle($) {
        $('#auto_generate').on('change', function () {
            var isChecked = $(this).is(':checked');
            $('#license_key').prop('disabled', isChecked); // Disable if checked
            if (isChecked) {
                $('#license_key').val(''); // Clear field if auto-generate is checked
            }
        });
    }

    function modal($) {
        // When the "Add License" button is clicked, show the modal
        $('#lkbi-license-modal-action').on('click', function (e) {

            $('#id').val('');
            $('#customer_search').val('');
            $('#customer_name').val('');
            $('#customer_email').val('');
            $('#customer_id').val('');
            $('#product_search').val('');
            $('#product_id').val('');
            $('#variation_id').val('');
            $('#product_name').val('');
            $('#product_slug').val('');
            $('#machine_id').val('');
            $('#license_key').val('');
            $('#platform').val('');
            $('#no_of_devices').val('');
            $('#status').val($('#status option:first').val());
            $('#auto_generate').show();
            $('#auto_generate_label').show();
            $('#auto_generate').prop('checked', false);
            $('#license_key').prop('disabled', false);

            e.preventDefault();
            $('#lkib-license-modal').fadeIn(); // Show the modal
        });

        // When the "close" button (×) is clicked, hide the modal
        $('.lkib-modal-close-btn').on('click', function () {
            $('#lkib-license-modal').fadeOut(); // Hide the modal
        });

        // When the user clicks outside the modal, hide it
        // $(window).on('click', function(e) {
        //     if ($(e.target).is('#lkib-license-modal')) {
        //         $('#lkib-license-modal').fadeOut(); // Hide the modal if clicked outside
        //     }
        // });
    }

    function search_customer($) {
        $('#customer_search').on('keydown', function () {
            var searchTerm = $(this).val();

            if (searchTerm.length >= 3) {
                $.ajax({
                    url: '/wp-json/v1/search_customer', // REST API endpoint
                    method: 'GET',
                    data: {
                        search_term: searchTerm
                    },
                    success: function (response) {
                        var resultsContainer = $('#customer_results');
                        resultsContainer.empty();

                        if (response.message) {
                            resultsContainer.append('<p>' + response.message + '</p>');
                        } else {
                            response.forEach(function (customer) {
                                resultsContainer.append('<p class="customer-result" data-id="' + customer.id + '" data-name="' + customer.name + '" data-email="' + customer.email + '">' + customer.name + ' (' + customer.email + ')</p>');
                            });
                        }
                    }
                });
            } else {
                $('#customer_results').empty();
            }
        });

        $(document).on('click', '.customer-result', function () {
            var customer_id = $(this).data('id');
            var customerName = $(this).data('name');
            var customerEmail = $(this).data('email');

            // Populate the hidden fields
            $('#customer_id').val(customer_id);
            $('#customer_name').val(customerName);
            $('#customer_email').val(customerEmail);

            // Set the search field to show "name (email)"
            $('#customer_search').val(customerName + ' (' + customerEmail + ')');

            // Clear the results list
            $('#customer_results').empty();
        });

        // Hide suggestions when clicking outside the search input or results
        $(document).on('click', function (event) {
            if (!$(event.target).closest('#customer_search, #customer_results').length) {
                $('#customer_results').empty();
            }
        });
    }

    function search_product($) {
        $('#product_search').on('keydown', function () {
            var searchTerm = $(this).val();

            if (searchTerm.length >= 3) {
                $.ajax({
                    url: '/wp-json/v1/search_product', // REST API endpoint
                    method: 'GET',
                    data: {
                        search_term: searchTerm
                    },
                    success: function (response) {
                        console.log(response);
                        var resultsContainer = $('#product_results');
                        resultsContainer.empty();

                        if (response.message) {
                            resultsContainer.append('<p>' + response.message + '</p>');
                        } else {
                            response.forEach(function (product) {
                                // Check if both variation_name and platform are defined (not null or undefined)
                                if (product.variation_name) {
                                    // Construct the result item with product name, variation name, and platform
                                    resultsContainer.append('<p class="product-result" data-productid= "' + product.product_id + '" data-variantid= "' + product.variation_id + '" data-productslug= "' + product.product_slug + '" data-variationslug= "' + product.variation_slug + '" data-name="' + product.product_name + '" data-variation="' + product.variation_name + '" data-platform="' + product.platform + '">' + product.variation_name + '(#' + product.variation_id + ')</p>');
                                }
                                // If neither variation_name nor platform are defined, display just the product name
                                else {
                                    resultsContainer.append('<p class="product-result" data-productid= "' + product.product_id + '" data-variantid= "' + product.variation_id + '" data-productslug= "' + product.product_slug + '" data-name="' + product.product_name + '">' + product.product_name + '(#' + product.product_id + ')</p>');
                                }
                            });
                        }
                    }
                });
            } else {
                $('#product_results').empty();
            }
        });

        $(document).on('click', '.product-result', function () {
            var product_name = $(this).data('name');
            var variation = $(this).data('variation');
            var product_id = $(this).data('productid');
            var variation_id = $(this).data('variantid') || 0;
            var product_slug = $(this).data('productslug');
            var variationslug = $(this).data('variationslug');
            var platform = $(this).data('platform');

            if (variation) {
                $('#product_search').val(variation);
            } else {
                $('#product_search').val(product_name);
            }

            // Populate the hidden fields
            $('#product_id').val(product_id);
            if (variation_id == "undefined") {
                $('#variation_id').val(0);
                $('#product_name').val(product_name);
                $('#product_slug').val(product_slug);
            } else {
                $('#variation_id').val(variation_id);
                $('#product_name').val(variation);
                $('#product_slug').val(variationslug);
            }

            $('#platform').val(formatText(platform));

            // Clear the results list
            $('#product_results').empty();
        });
    }

    function form_submit($) {
        $('#submit_license').on('click', function (e) {
            e.preventDefault(); // Prevent default form submission
            // Gather the form data
            var formData = {
                id: $('#id').val(),
                customer_id: $('#customer_id').val(),
                customer_name: $('#customer_name').val(),
                customer_email: $('#customer_email').val(),
                product_id: parseInt($('#product_id').val() || '0', 10),
                variation_id: parseInt($('#variation_id').val() || '0', 10),
                product_name: $('#product_name').val(),
                product_slug: $('#product_slug').val(),
                machine_id: $('#machine_id').val(),
                license_key: $('#license_key').val(),
                no_of_devices: $('#no_of_devices').val(),
                platform: $('#platform').val(),
                status: $('#status').val(),
                auto_generate: $('#auto_generate').prop('checked') ? 1 : 0 // Check if auto-generate is checked
            };

            // Send the data to the REST API
            $.ajax({
                url: '/wp-json/v1/add_license_key', // REST API endpoint
                method: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {

                        if ($('#id').val() == null) {
                            showToast($, 'Successfully saved', 'success');
                        } else {
                            showToast($, 'Successfully updated', 'success');
                        }
                        $('#lkib-license-modal').fadeOut();
                        fetch_license_data($);
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
            $('#lkib-license-modal').fadeOut();
        });
    }

    function edit_data($) {
        $(document).on('click', '.edit-btn', function (e) {
            e.preventDefault();

            var data = JSON.parse($(this).closest('tr').attr('data-item'));

            if (data.platform) {
                $('#product_search').val(`${data.product_name} - ${data.platform}`);
            } else {
                $('#product_search').val(data.product_name);
            }

            $('#id').val(data.id);
            $('#customer_id').val(data.customer_id);
            $('#customer_search').val(data.customer_name + ' (' + data.customer_email + ')');
            $('#customer_name').val(data.customer_name);
            $('#customer_email').val(data.customer_email);

            $('#product_id').val(data.product_id);
            $('#variation_id').val(data.variation_id);
            $('#product_name').val(data.product_name);
            $('#product_slug').val(data.product_slug);
            $('#platform').val(data.platform);

            $('#machine_id').val(data.machine_id);
            $('#license_key').val(data.license_key);
            $('#no_of_devices').val(data.max_domains);
            $('#status').val(data.status);
            $('#auto_generate').hide();
            $('#auto_generate_label').hide();
            $('#license_key').prop('disabled', false);

            e.preventDefault();
            $('#lkib-license-modal').fadeIn(); // Show the modal

        });
    }

    function delete_data($) {
        $(document).on('click', '.a-delete-btn', function (e) {
            console.log("test");
            e.preventDefault();
            const recordId = $(this).data('id');

            $('#delete_id').val(recordId); // Set the ID in hidden input field
            $('#lkib-delete-modal').show(); // Show delete confirmation modal
        });

        // Close modal when close button is clicked
        $('.lkib-modal-close-btn, #cancel_delete').on('click', function () {
            $('#lkib-delete-modal').hide();
        });

        // Confirm delete
        $('#confirm_delete').on('click', function () {
            const recordId = $('#delete_id').val();

            // AJAX request to delete record
            $.ajax({
                url: `/wp-json/v1/delete-license-key/${recordId}`,
                method: 'DELETE',
                success: function (response) {
                    showToast($, 'Record deleted successfully.', 'success');
                    fetch_license_data($);
                    $('#lkib-delete-modal').hide();
                },
                error: function (error) {
                    showToast($, 'Failed to delete the record.', 'error');
                }
            });
        });
    }

    function fetch_license_data($, page = 1, per_page = 10) {
        $('.spinner-container').show();
        $.ajax({
            url: `/wp-json/v1/get_licenses?page=${page}&per_page=${per_page}`,
            method: 'GET',
            success: function (response) {
                // Clear any existing rows in the table
                $('#license-keys-tbody').empty();

                if (response.data.length === 0) {
                    // If no license keys, show a message in the table
                    $('#no-license-found-message').show();
                    $('#pagination-container').hide();
                } else {
                    // Loop through the response and create table rows
                    response.data.forEach(function (item) {
                        var statusColor = item.status_color === 'green' ? 'color:green;' : 'color:red;';
                        var row = `<tr data-item='${JSON.stringify(item)}'>
                            <td>${item.product_name}</td>
                            <td>${item.product_slug}</td>
                            <td>${item.customer_name} (ID:${item.customer_id})</td>
                            <td>${item.customer_email}</td>
                            <td>${item.license_key}</td>
                            <td>${item.platform}</td>
                            <td>${item.machine_id}</td>
                            <td>${item.domains != null ? item.domains : ''}</td>
                            <td>${item.active_device}</td>
                            <td style="${statusColor}">${item.status}</td>
                            <td>
                                <a class="edit-btn" href="#" style="color: #0073aa; text-decoration: none;">
                                    <i class="dashicons dashicons-edit" style="font-size: 16px;"></i> Edit
                                </a>
                                <a class="a-delete-btn" href="#" data-id="${item.id}" style="color: #d63638; text-decoration: none;">
                                    <i class="dashicons dashicons-trash" style="font-size: 16px;"></i> Delete
                                </a>
                            </td>
    
                        </tr>`;
                        // Append the row to the tbody
                        $('#license-keys-tbody').append(row);
                    });

                    $('#no-license-found-message').hide();
                    $('#pagination-container').show();
                    // Render pagination
                    renderPagination($, response.pagination, page, per_page);
                }


                $('.spinner-container').hide();
            },
            error: function (error) {
                $('.spinner-container').hide();
                $('#no-license-found-message').hide();
                showToast($, 'Error fetching license data:', error);
            }
        });
    }

    function search_license_key($, page = 1, per_page = 10) {
        $('.lkbi-btn-search').on('click', function () {
            $('.spinner-container').show();
            var keyword = $('#keyword').val();
            $.ajax({
                url: `/wp-json/v1/search-license-key?keyword=${keyword}&page=${page}&per_page=${per_page}`,
                method: 'GET',
                success: function (response) {
                    // Clear any existing rows in the table
                    $('#license-keys-tbody').empty();

                    if (response.data.length === 0) {
                        // If no license keys, show a message in the table
                        $('#no-license-found-message').show();
                        $('#pagination-container').hide();
                    } else {
                        // Loop through the response and create table rows
                        response.data.forEach(function (item) {
                            var statusColor = item.status_color === 'green' ? 'color:green;' : 'color:red;';
                            var row = `<tr data-item='${JSON.stringify(item)}'>
                            <td>${item.product_name}</td>
                            <td>${item.product_slug}</td>
                            <td>${item.customer_name}</td>
                            <td>${item.customer_email}</td>
                            <td>${item.license_key}</td>
                            <td>${item.platform}</td>
                            <td>${item.machine_id}</td>
                            <td>${item.domains != null ? item.domains : ''}</td>
                            <td>${item.active_device}</td>
                            <td style="${statusColor}">${item.status}</td>
                            <td>
                                <a class="edit-btn" href="#" style="color: #0073aa; text-decoration: none;">
                                    <i class="dashicons dashicons-edit" style="font-size: 16px;"></i> Edit
                                </a>
                                <a class="a-delete-btn" href="#" data-id="${item.id}" style="color: #d63638; text-decoration: none;">
                                    <i class="dashicons dashicons-trash" style="font-size: 16px;"></i> Delete
                                </a>
                            </td>
    
                        </tr>`;
                            // Append the row to the tbody
                            $('#license-keys-tbody').append(row);
                        });

                        $('#no-license-found-message').hide();
                        $('#pagination-container').show();
                        // Render pagination
                        renderPagination($, response.pagination, page, per_page);
                    }


                    $('.spinner-container').hide();
                },
                error: function (error) {
                    $('.spinner-container').hide();
                    $('#no-license-found-message').hide();
                    showToast($, 'Error fetching license data:', error);
                }
            });
        });
    }

    function reset_search($) {
        $('.lkbi-btn-reset').on('click', function () {
            $('#keyword').val('');
            fetch_license_data($);
        });
    }

    function renderPagination($, pagination, currentPage, perPage) {
        const totalPages = pagination.total_pages;
        const paginationContainer = $('#pagination-container');

        // Clear any existing pagination
        paginationContainer.empty();

        // Previous button
        const prevPage = currentPage > 1 ? currentPage - 1 : 1;
        paginationContainer.append(`
            <button class="pagination-btn" data-page="${prevPage}"><</button>
        `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationContainer.append(`
                <button class="pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>
            `);
        }

        // Next button
        const nextPage = currentPage < totalPages ? currentPage + 1 : totalPages;
        paginationContainer.append(`
            <button class="pagination-btn" data-page="${nextPage}">></button>
        `);

        // Handle page click
        $('.pagination-btn').click(function () {
            const page = $(this).data('page');
            fetch_license_data($, page, perPage);
        });
    }

    function showToast($, message, type = 'success') {
        var toast = $('<div class="toast ' + type + '">' + message + '</div>'); // Create toast with the given type
        $('#toast-container').append(toast); // Append it to the container

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

    function formatText(text) {
        // Replace hyphens with spaces
        text = text.replace(/-/g, " ");

        // Capitalize the first letter of each word
        text = text.replace(/\b\w/g, char => char.toUpperCase());

        return text;
    }
})();