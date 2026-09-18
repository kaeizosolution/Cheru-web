$(document).ready(function () {
    initVendorEnquiriesTable();
});

function initVendorEnquiriesTable() {
    var isApi = (typeof use_api !== 'undefined' && use_api && typeof access_token !== 'undefined' && access_token);
    var apiUrl = '/api/v1/vendor/product_enquiries';

    var ajaxConfig = {
        url: apiUrl,
        type: 'POST',
        dataType: 'json',
        data: function (d) {
            return d;
        }
    };

    if (isApi) {
        ajaxConfig.beforeSend = function (xhr) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + access_token);
        };
    }

    $('#vendor_enquiries_table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        ordering: false,
        destroy: true,
        ajax: ajaxConfig,
        language: {
            paginate: {
                next: '&raquo;',
                previous: '&laquo;'
            },
            emptyTable: 'No enquiries found for your products.',
            zeroRecords: 'No enquiries match your search.'
        },
        dom: '<"top"f>t<"bottom"lip><"clear">',
        columns: [
            {
                // Date
                data: 'created_at',
                render: function (data) {
                    return data ? '<span style="white-space:nowrap;font-size:12px;">' + data + '</span>' : '-';
                }
            },
            {
                // Product
                data: 'product_name',
                render: function (data, type, row) {
                    var name = data ? data : '-';
                    return '<span style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:inline-block;font-weight:600;" title="' + name + '">' + name + '</span>' +
                           '<br><small class="text-muted">ID: ' + (row.product_id || '') + '</small>';
                }
            },
            {
                // Customer
                data: 'customer_name',
                render: function (data) {
                    return data ? '<strong>' + data + '</strong>' : '<em class="text-muted">Anonymous</em>';
                }
            },
            {
                // Message
                data: 'message',
                render: function (data) {
                    if (!data) return '<em class="text-muted">—</em>';
                    var plain = $('<div>').html(data).text();
                    if (plain.length > 80) {
                        return '<span title="' + plain + '" style="white-space:normal;max-width:200px;display:inline-block;">' + plain.substring(0, 80) + '...</span>';
                    }
                    return '<span style="white-space:normal;max-width:200px;display:inline-block;">' + plain + '</span>';
                }
            },
            {
                // Status
                data: 'is_contacted',
                render: function (data, type, row) {
                    if (parseInt(data) === 1) {
                        var badge = '<span class="badge badge-success" style="padding:5px 9px;border-radius:12px;font-weight:600;font-size:11px;"><i class="fa fa-check-circle mr-1"></i> Contacted</span>';
                        if (row.contacted_at) {
                            badge += '<br><small class="text-muted" style="font-size:10px;">' + row.contacted_at + '</small>';
                        }
                        return badge;
                    }
                    return '<span class="badge badge-warning" style="padding:5px 9px;border-radius:12px;font-weight:600;background:#f59e0b;color:#fff;font-size:11px;"><i class="fa fa-clock-o mr-1"></i> Not Contacted</span>';
                }
            },
            {
                // Action
                data: 'id',
                orderable: false,
                render: function (data, type, row) {
                    var contacted = parseInt(row.is_contacted);
                    if (contacted === 1) {
                        return '<button class="btn btn-outline-secondary btn-sm enq-response-btn" data-id="' + data + '" data-status="0" style="border-radius:8px;font-size:11px;padding:4px 8px;white-space:nowrap;">Mark Not Contacted</button>';
                    }
                    return '<button class="btn btn-primary btn-sm enq-response-btn" data-id="' + data + '" data-status="1" style="border-radius:8px;font-size:11px;padding:4px 8px;background:#6366f1;border:none;white-space:nowrap;"><i class="fa fa-paper-plane mr-1"></i> Response</button>';
                }
            }
        ]
    });

    // Handle Response button click
    $(document).on('click', '.enq-response-btn', function () {
        var btn       = $(this);
        var id        = btn.data('id');
        var newStatus = parseInt(btn.data('status'));

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/api/v1/vendor/product_enquiries/response',
            type: 'POST',
            dataType: 'json',
            beforeSend: function (xhr) {
                if (isApi) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + access_token);
                }
            },
            data: { id: id, status: newStatus },
            success: function (res) {
                btn.prop('disabled', false);
                if (res.status === 1) {
                    // Reload the table to reflect updated state
                    $('#vendor_enquiries_table').DataTable().ajax.reload(null, false);
                } else {
                    alert(res.message || 'Error updating status');
                    btn.prop('disabled', false).html(newStatus === 1 ? '<i class="fa fa-paper-plane mr-1"></i> Response' : 'Mark Not Contacted');
                }
            },
            error: function () {
                btn.prop('disabled', false);
                alert('Server error. Please try again.');
            }
        });
    });
}
