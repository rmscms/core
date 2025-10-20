$(document).ready(function() {
    // Batch selection functionality
    const $selectAll = $('#select_all');
    const $rowSelectors = $('.row-selector');
    const $batchActionsBtn = $('#batch-actions-btn');

    // Select all functionality
    $selectAll.on('change', function() {
        const isChecked = $(this).is(':checked');
        $rowSelectors.prop('checked', isChecked);
        updateBatchActionsButton();
    });

    // Individual row selection
    $rowSelectors.on('change', function() {
        const totalRows = $rowSelectors.length;
        const checkedRows = $rowSelectors.filter(':checked').length;
        const allChecked = checkedRows === totalRows;
        const noneChecked = checkedRows === 0;

        // Update select all checkbox
        $selectAll.prop('checked', allChecked);
        $selectAll.prop('indeterminate', !allChecked && !noneChecked);

        updateBatchActionsButton();
    });

    function updateBatchActionsButton() {
        const hasSelection = $rowSelectors.filter(':checked').length > 0;
        $batchActionsBtn.prop('disabled', !hasSelection);
    }

    // ======================================
    // AJAX Toggle Functionality for Boolean Fields
    // ======================================

    // Handle toggle button clicks
    $(document).on('click', '.toggle-btn', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const toggleUrl = $btn.data('toggle-url');

        if (!toggleUrl) {
            console.error('Toggle URL not found');
            return;
        }

        // Prevent double clicks
        if ($btn.hasClass('toggling')) {
            return;
        }

        $btn.addClass('toggling');

        // Make AJAX request
        $.ajax({
            url: toggleUrl,
            method: 'POST',
            showLoader: false, // Don't show global loader for toggles
            success: function(response) {
                if (response.success) {
                    // Update button state based on new value
                    const newValue = response.data.value;

                    if (newValue) {
                        // Active state - Limitless style
                        $btn.find('button').removeClass('btn-danger')
                            .addClass('btn-success')
                            .find('i')
                            .removeClass('ph-x text-white')
                            .addClass('ph-check text-white');
                    } else {
                        // Inactive state - Limitless style
                        $btn.find('button').removeClass('btn-success')
                            .addClass('btn-danger')
                            .find('i')
                            .removeClass('ph-check text-white')
                            .addClass('ph-x');
                    }

                    // Show success notification
                    if (response.message) {
                        showNotification(response.message, 'success', 3000);
                    }
                } else {
                    // Show error message
                    const message = response.message || 'خطا در تغییر وضعیت';
                    showNotification(message, 'error');
                }
            },
            error: function(xhr) {
                // Error handled by global error handler
                console.error('Toggle request failed:', xhr);
            },
            complete: function() {
                $btn.removeClass('toggling');
            }
        });
    });

    // Confirmation modal using jQuery
    $('#confirmModal').on('show.bs.modal', function(event) {
        const $button = $(event.relatedTarget);
        const actionUrl = $button.data('action-url');
        const actionMethod = $button.data('action-method') || 'GET';
        const actionTitle = $button.data('action-title');

        const $form = $('#confirmForm');
        const $message = $('#confirm-message');

        if ($form.length && actionUrl) {
            $form.attr('action', actionUrl);

            // Update method based on action
            let $methodInput = $form.find('input[name="_method"]');

            if (actionMethod && actionMethod.toUpperCase() !== 'POST') {
                // For DELETE, PUT, PATCH methods - ensure _method input exists
                if ($methodInput.length === 0) {
                    $methodInput = $('<input type="hidden" name="_method">');
                    $form.append($methodInput);
                }
                $methodInput.val(actionMethod.toUpperCase());
            } else {
                // For POST method, remove _method input if exists
                $methodInput.remove();
            }
        }

        if ($message.length && actionTitle) {
            $message.text(`آیا از ${actionTitle} مطمئن هستید؟`);
        }
    });
});

// Per page change function using jQuery
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}

// Batch action submission using jQuery
function submitBatchAction(actionUrl) {
    const selectedIds = $('.row-selector:checked').map(function() {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
        alert('لطفاً حداقل یک رکورد را انتخاب کنید.');
        return;
    }

    // Create a new form for batch action using jQuery
    const $batchForm = $('<form>', {
        method: 'POST',
        action: actionUrl
    });

    // Add CSRF token
    const csrfToken = $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content');
    if (csrfToken) {
        $batchForm.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: csrfToken
        }));
    }

    // Add selected IDs
    selectedIds.forEach(id => {
        $batchForm.append($('<input>', {
            type: 'hidden',
            name: 'ids[]',
            value: id
        }));
    });

    // Submit form
    $('body').append($batchForm);
    $batchForm.submit();
}
