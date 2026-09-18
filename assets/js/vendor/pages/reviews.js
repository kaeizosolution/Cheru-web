$(document).ready(function () {
    initReviewsTable();
});

function renderStars(rating) {
    var stars = '';
    var r = parseInt(rating) || 0;
    for (var i = 1; i <= 5; i++) {
        if (i <= r) {
            stars += '<i class="fa fa-star" style="color:#f5a623;font-size:14px;"></i>';
        } else {
            stars += '<i class="fa fa-star" style="color:#ddd;font-size:14px;"></i>';
        }
    }
    return stars;
}

function initReviewsTable() {
    var isApi = (typeof use_api !== 'undefined' && use_api && typeof access_token !== 'undefined' && access_token);
    var url = isApi
        ? '/api/v1/vendor/Vendor/reviews'
        : '/vendor/reviews/get_reviews_ajax';

    var ajaxConfig = {
        "url": url,
        "type": "POST",
        "dataType": "json",
        "data": function (d) {
            if (!isApi) {
                d[csrf.name] = csrf.hash;
            }
            return d;
        }
    };

    if (isApi) {
        ajaxConfig.beforeSend = function (xhr) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + access_token);
        };
    }

    $('#reviews_table').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "searching": true,
        "ordering": false,
        "destroy": true,
        "ajax": ajaxConfig,
        "language": {
            "paginate": {
                "next": '&raquo;',
                "previous": '&laquo;'
            },
            "emptyTable": "No reviews found for your products.",
            "zeroRecords": "No reviews match your search."
        },
        "dom": '<"top"f>t<"bottom"lip><"clear">',
        "columns": [
            {
                "data": "review_rating_id",
                "render": function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                "data": "product_name",
                "render": function (data) {
                    return '<span>' + (data ? $('<div>').text(data).html() : '-') + '</span>';
                }
            },
            {
                "data": "customer_name",
                "render": function (data) {
                    return data ? $('<div>').text(data).html() : '-';
                }
            },
            {
                "data": "rating",
                "render": function (data) {
                    return renderStars(data) + ' <small class="text-muted">(' + (data || 0) + '/5)</small>';
                }
            },
            {
                "data": "title",
                "render": function (data) {
                    return data ? $('<div>').text(data).html() : '<em class="text-muted">No title</em>';
                }
            },
            {
                "data": "review",
                "render": function (data) {
                    if (!data) return '<em class="text-muted">No review</em>';
                    var escaped = $('<div>').text(data).html();
                    if (escaped.length > 100) {
                        return '<span title="' + escaped + '">' + escaped.substring(0, 100) + '...</span>';
                    }
                    return escaped;
                }
            },
            {
                "data": "status",
                "render": function (data) {
                    if (data == '1' || data === 'approved') {
                        return '<span class="badge bg-success">Approved</span>';
                    }
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                }
            },
            {
                "data": "date_created",
                "render": function (data) {
                    if (!data) return '-';
                    var d = new Date(data);
                    if (isNaN(d.getTime())) return data;
                    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            }
        ]
    });
}
