(function () {
    jQuery(document).ready(function ($) {
        var _customer_id = 0;
        var _variation_id = 0;

        user_trade_search_btn_event($);
        user_trade_reset_search($);
        fetch_user_trade_summary($);
        user_trade_click_event($);
        back_button_function($);
        trade_exeuction_summary_btns($);
    });

    function user_trade_search_btn_event($){
        $('.lkbi-user-trade-btn-search').on('click', function () {
            search_user_trade_summary($);
        });
    }

    function user_trade_reset_search($) {
        $('.lkbi-user-trade-btn-reset').on('click', function () {
            $('#user-trade-keyword').val('');
            fetch_user_trade_summary($);
        });
    }

    function search_user_trade_summary($, page = 1, per_page = 10) {
        $('.spinner-container').show();
        var keyword = $('#user-trade-keyword').val();
        // Fetch data from the API
        $.ajax({
            url: `/wp-json/v1/search_user_trade_summary?keyword=${keyword}&page=${page}&per_page=${per_page}`,
            method: "GET",
            success: function (response) {

                console.log(response);
                $('.spinner-container').hide();

                // Check if data is present
                if (response.data.length === 0) {
                    // If no data, show a message
                    $('#no-user-trade-data-found-message').show();
                    $('#user-trade-pagination-container').hide();
                    return;
                }

                // Populate the table with data
                const tbody = $("#lkbi-user-trade-tbody");
                tbody.empty(); // Clear existing rows

                response.data.forEach((item) => {
                    // Set color based on PnL value (green if positive, red if negative)
                    const pnlColorClass = item.pnl < 0 ? 'lkbi-negative-pnl' : 'lkbi-positive-pnl';

                    const row = `
                        <tr class="lkbi-clickable-row" data-customer-id="${item.customer_id}" data-variation-id="${item.variation_id}" 
                            data-customer="${item.customer_name}" data-bot="${item.bot}">
                            <td>${item.customer_name}</td>
                            <td>${item.bot}</td>
                            <td>${item.symbol}</td>
                            <td>${item.first_open_timestamp}</td>
                            <td>${item.last_close_timestamp}</td>
                            <td class="${pnlColorClass}">${item.pnl}</td>
                            <td>${item.execution}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Hide the "no data" message and show the pagination container
                $('#no-user-trade-data-found-message').hide();
                $('#user-trade-pagination-container').show();

                // Render pagination
                renderPagination($, response.pagination, page, per_page);
            },
            error: function (xhr, status, error) {
                const tbody = $("#lkbi-user-trade-tbody");
                tbody.empty(); // Clear existing rows

                $('.spinner-container').hide();
                $('#no-user-trade-data-found-message').show();
            }
        });
    }

    function fetch_user_trade_summary($, page = 1, per_page = 10) {
        $('.spinner-container').show();

        // Fetch data from the API
        $.ajax({
            url: `/wp-json/v1/get_user_trade_summary?page=${page}&per_page=${per_page}`,
            method: "GET",
            success: function (response) {
                $('.spinner-container').hide();

                // Check if data is present
                if (response.data.length === 0) {
                    // If no data, show a message
                    $('#no-user-trade-data-found-message').show();
                    $('#user-trade-pagination-container').hide();
                    return;
                }

                // Populate the table with data
                const tbody = $("#lkbi-user-trade-tbody");
                tbody.empty(); // Clear existing rows

                response.data.forEach((item) => {
                    // Set color based on PnL value (green if positive, red if negative)
                    const pnlColorClass = item.pnl < 0 ? 'lkbi-negative-pnl' : 'lkbi-positive-pnl';

                    const row = `
                        <tr class="lkbi-clickable-row" data-customer-id="${item.customer_id}" data-variation-id="${item.variation_id}" 
                            data-customer="${item.customer_name}" data-bot="${item.bot}">
                            <td>${item.customer_name}</td>
                            <td>${item.bot}</td>
                            <td>${item.symbol}</td>
                            <td>${item.first_open_timestamp}</td>
                            <td>${item.last_close_timestamp}</td>
                            <td class="${pnlColorClass}">${item.pnl}</td>
                            <td>${item.execution}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Hide the "no data" message and show the pagination container
                $('#no-user-trade-data-found-message').hide();
                $('#user-trade-pagination-container').show();

                // Render pagination
                renderPagination($, response.pagination, page, per_page);
            },
            error: function (xhr, status, error) {
                $('.spinner-container').hide();
                $('#no-user-trade-data-found-message').hide();
                showToast($, 'Error fetching user trade summary:', error);
            }
        });
    }

    function fetch_trade_execution_summary($, customer_id, variation_id, symbol = "", start_date = "", end_date = "", page = 1, per_page = 10) {
        $('.spinner-container').show();

        // Fetch data from the API
        $.ajax({
            url: `/wp-json/v1/get_trade_execution_summary?customer_id=${customer_id}&variation_id=${variation_id}&symbol=${symbol}&start_date=${start_date}&end_date=${end_date}&page=${page}&per_page=${per_page}`,
            method: "GET",
            success: function (response) {
                $('.spinner-container').hide();

                // Check if data is present
                if (response.data.length === 0) {
                    $('#lkbi-user-trade-summary').show();
                    $('.lkbi-user-trade-search-property').show();

                    $('#lkbi-user-trade-execution-summary').hide();
                    $("#lkbi-table-2").hide();
                    $('#lkbi-no-user-trade-data-found-message').show();
                } else {
                    // Populate the second table with trade execution data
                    const tbody = $("#lkbi-user-trade-execution-summary-tbody");
                    tbody.empty(); // Clear existing rows

                    const dropdown = $('#lkbi-symbol-dropdown');
                    dropdown.empty(); // Clear existing options

                    // Add a default placeholder option
                    dropdown.append('<option value="">Select a Symbol</option>');

                    // Populate options with unique symbols
                    response.unique_symbols.forEach(function (symbol) {
                        dropdown.append(`<option value="${symbol}">${symbol}</option>`);
                    });

                    response.data.forEach((item) => {
                        const row = `
                            <tr class="lkbi-trade-row" data-id="${item.id}" data-details-fetched="false">
                                <td>
                                    <span class="accordion-icon">+</span>    
                                    ${item.symbol}
                                </td>
                                <td>${item.open_timestamp}</td>
                                <td>${item.close_timestamp}</td>
                                <td>${item.position}</td>
                                <td class="${item.total_pnl < 0 ? 'lkbi-negative-pnl' : 'lkbi-positive-pnl'}">${item.total_pnl}</td>
                                <td>${item.executions}</td>
                                <td>${item.total_duration}</td>
                                <td>${item.open_price}</td>
                                <td>${item.close_price}</td>
                                <td>${item.created_date}</td>
                            </tr>
                            <tr class="trade-details-row" id="trade-details-${item.id}" style="display:none;">
                                <td colspan="10" style="padding: 30px;">
                                    <table class="lkbi-table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th>Symbol</th>
                                                <th>Date</th>
                                                <th>Side</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Pnl</th>
                                                <th>Commission</th>
                                                <th>Fees</th>
                                            </tr>
                                        </thead>
                                        <tbody id="trade-details-${item.id}-tbody" class="lkbi-body"></tbody>
                                    </table>
                                    <div id="trade-execution-pagination-container" data-id="${item.id}"></div>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });

                    // Show the execution summary table
                    $('#lkbi-user-trade-summary').hide();
                    $('.lkbi-user-trade-search-property').hide();

                    $('#lkbi-user-trade-execution-summary').show();
                    $("#lkbi-table-2").show();
                    $('#lkbi-no-user-trade-data-found-message').hide();

                    $('#no-user-trade-data-found-message').hide();
                    $('#user-trade-pagination-container').hide();
                    $('#trade-execution-summary-pagination-container').show();

                    // Attach click event to toggle the accordion effect
                    attachAccordionClickEvent($);

                    renderPagination_trade_summary($, response.pagination, page, per_page);
                }
            },
            error: function (xhr, status, error) {
                const tbody = $("#lkbi-user-trade-execution-summary-tbody");
                tbody.empty(); // Clear existing rows
                $('.spinner-container').hide();
                $('#no-user-trade-data-found-message').show();
            }
        });
    }

    function fetch_trade_execution($, id, page = 1, per_page = 10){
        $('.spinner-container').show();

        $.ajax({
            url: `/wp-json/v1/get_trade_data_execution?id=${id}&page=${page}&per_page=${per_page}`,
            method: "GET",
            success: function (response) {
                const tradeDetailsTbody = $(`#trade-details-${id}-tbody`);
                tradeDetailsTbody.empty(); // Clear existing data

                response.data.forEach((item) => {
                    const pnlColorClass = item.pnl < 0 ? 'lkbi-negative-pnl' : 'lkbi-positive-pnl';
                    const detailRow = `
                        <tr>
                            <td>${item.symbol}</td>
                            <td>${item.created_date}</td>
                            <td>${item.side}</td>
                            <td>${item.price}</td>
                            <td>${item.quantity}</td>
                            <td class="${pnlColorClass}">${item.pnl}</td>
                            <td>${item.commission}</td>
                            <td>${item.fees}</td>
                        </tr>
                    `;
                    tradeDetailsTbody.append(detailRow);
                });

                $('.spinner-container').hide();

                renderPagination_trade_execution($, response.pagination, page, per_page);
            },
            error: function (xhr, status, error) {
                showToast($, 'Error fetching trade data execution:', error);
                $('.spinner-container').hide();
            }
        });
    }

    function trade_exeuction_summary_btns($){
        $('.lkbi-apply-btn').on('click', function () {
            var selected_symbol = $('#lkbi-symbol-dropdown').val();
            var start_date = $('#lkbi-start-date').val();
            var end_date = $('#lkbi-end-date').val();

            console.log(start_date);

            fetch_trade_execution_summary($, _customer_id, _variation_id, selected_symbol, start_date, end_date)
        });
    }

    function attachAccordionClickEvent($) {
        $('.lkbi-trade-row').on('click', function () {
            
            const row = $(this); // Store reference to the clicked row
            const id = row.data('id');
            const detailsRow = $(`#trade-details-${id}`);
            const isDetailsFetched = row.attr('data-details-fetched'); // Use the row reference
            const accordionIcon = row.find(".accordion-icon");

            

            // If details have been fetched before, just toggle visibility
            if (isDetailsFetched == "true") {
                if (detailsRow.is(":visible")) {
                    accordionIcon.text("+"); // Collapse state
                } else {
                    accordionIcon.text("-"); // Expand state
                }
                detailsRow.toggle();
            } else {
                $('.spinner-container').show();
                // Fetch detailed trade data when row is clicked
                fetch_trade_execution($, id);

                // Set the flag that details have been fetched
                row.attr('data-details-fetched', 'true'); // Set the flag on the correct row

                // Toggle the visibility of the detailed row
                if (detailsRow.is(":visible")) {
                    accordionIcon.text("+"); // Collapse state
                } else {
                    accordionIcon.text("-"); // Expand state
                }
                
                detailsRow.toggle();
            }
        });
    }

    function user_trade_click_event($) {
        // Set up click event for each row in the user trade summary table
        $('#lkbi-user-trade-tbody').on('click', '.lkbi-clickable-row', function () {
            const customer_id = $(this).data('customer-id');
            const variation_id = $(this).data('variation-id');

            _customer_id = customer_id;
            _variation_id = variation_id;

            const customer = $(this).data('customer');
            const bot = $(this).data('bot');

            $('div').find('p:first-child').html(`<strong>User:</strong> ${customer}`);
            $('div').find('p:nth-child(2)').html(`<strong>Product:</strong> ${bot}`);

            // Fetch the trade execution summary for the clicked row
            fetch_trade_execution_summary($, customer_id, variation_id);
        });
    }

    function showToast($, message, type = 'success') {
        var toast = $('<div class="toast ' + type + '">' + message + '</div>'); // Create toast with the given type
        $('#user-trade-toast-container').append(toast); // Append it to the container

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

    function back_button_function($) {
        $('#lkbi-back-btn').click(function () {
            // Hide the execution summary table 
            $('#lkbi-user-trade-execution-summary').hide();
            $('#lkbi-table-2').hide();

            $('#lkbi-user-trade-summary').show();
            $('.lkbi-user-trade-search-property').show();

            // Show the main table again (if necessary)
            $('#lkbi-user-trade-tbody').show(); // Assuming you want to display the main table again
            $('.lkbi-user-trade-search-property').show();

            $('#user-trade-pagination-container').show();
            $('#trade-execution-summary-pagination-container').hide();
        });
    }

    function renderPagination($, pagination, currentPage, perPage) {
        const totalPages = pagination.total_pages;
        const paginationContainer = $('#user-trade-pagination-container');

        // Clear any existing pagination
        paginationContainer.empty();

        // Previous button
        const prevPage = currentPage > 1 ? currentPage - 1 : 1;
        paginationContainer.append(`
            <button class="user-trade-pagination-btn" data-page="${prevPage}"><</button>
        `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationContainer.append(`
                <button class="user-trade-pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>
            `);
        }

        // Next button
        const nextPage = currentPage < totalPages ? currentPage + 1 : totalPages;
        paginationContainer.append(`
            <button class="user-trade-pagination-btn" data-page="${nextPage}">></button>
        `);

        // Handle page click
        $('.user-trade-pagination-btn').click(function () {
            const page = $(this).data('page');
            fetch_user_trade_summary($, page, perPage);
        });
    }

    function renderPagination_trade_summary($, pagination, currentPage, perPage) {
        const totalPages = pagination.total_pages;
        const paginationContainer = $('#trade-execution-summary-pagination-container');

        // Clear any existing pagination
        paginationContainer.empty();

        // Previous button
        const prevPage = currentPage > 1 ? currentPage - 1 : 1;
        paginationContainer.append(`
            <button class="user-trade-summary-pagination-btn" data-page="${prevPage}"><</button>
        `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationContainer.append(`
                <button class="user-trade-summary-pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>
            `);
        }

        // Next button
        const nextPage = currentPage < totalPages ? currentPage + 1 : totalPages;
        paginationContainer.append(`
            <button class="user-trade-summary-pagination-btn" data-page="${nextPage}">></button>
        `);

        // Handle page click
        $('.user-trade-summary-pagination-btn').click(function () {
            const page = $(this).data('page');
            fetch_trade_execution_summary($, _customer_id, _variation_id, page, perPage);
        });
    }

    function renderPagination_trade_execution($, pagination, currentPage, perPage) {
        const totalPages = pagination.total_pages;
        const paginationContainer = $('#trade-execution-pagination-container');

        // Clear any existing pagination
        paginationContainer.empty();

        // Previous button
        const prevPage = currentPage > 1 ? currentPage - 1 : 1;
        paginationContainer.append(`
            <button class="trade-execution-pagination-btn" data-page="${prevPage}"><</button>
        `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationContainer.append(`
                <button class="trade-execution-pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>
            `);
        }

        // Next button
        const nextPage = currentPage < totalPages ? currentPage + 1 : totalPages;
        paginationContainer.append(`
            <button class="trade-execution-pagination-btn" data-page="${nextPage}">></button>
        `);

        // Handle page click
        $('.trade-execution-pagination-btn').click(function () {
            const page = $(this).data('page');
            var id = $('#trade-execution-pagination-container').data('id');
            fetch_trade_execution($, id, page, perPage);
        });
    }
})();