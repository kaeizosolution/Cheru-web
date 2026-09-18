<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/responsive.css">
    
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #ec4899;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
            --dark: #1e293b;
            --light: #f3f4f6;
            --accent-color: #6366f1;
            --sidebar-bg: #191c4d;
            
            /* STRONGER 3D SHADOWS */
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background-color: #f3f5f8;
            color: var(--dark);
        }

        /* --- ORBIT LOADER --- */
        .loader-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: var(--sidebar-bg);
            z-index: 99999;
            display: flex; justify-content: center; align-items: center;
        }
        
        .loader-container {
            position: relative;
            width: 150px; height: 150px;
            display: flex; justify-content: center; align-items: center;
        }

        .loader-logo { 
            width: 80px; 
            height: auto; 
            object-fit: contain; 
            z-index: 10;
            animation: pulse-logo 2s ease-in-out infinite;
        }

        .loader-ring {
            position: absolute;
            width: 100%; height: 100%;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: var(--accent-color); /* Blue Ring */
            animation: spin 1.5s linear infinite;
        }
        
        .loader-ring:before {
            content: "";
            position: absolute;
            top: 10px; left: 10px; right: 10px; bottom: 10px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #fff; /* White Ring */
            animation: spin-reverse 3s linear infinite;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes spin-reverse { 0% { transform: rotate(0deg); } 100% { transform: rotate(-360deg); } }
        @keyframes pulse-logo { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(0.9); opacity: 0.8; } }

        /* --- COMPACT 3D CARDS --- */
        .dashboard-card {
            background: #fff;
            border: none;
            border-radius: 12px; /* Slightly smaller radius for compact look */
            padding: 15px 20px; /* Tighter Padding */
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            height: 100%; /* Force equal height */
            display: flex;
            align-items: center;
            min-height: 90px; /* Ensure decent height */
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
            z-index: 10;
        }

        /* Icon Wrapper (Smaller) */
        .card-icon-wrapper {
            width: 45px; height: 45px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            margin-right: 15px;
            flex-shrink: 0;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
        }

        .bg-light-primary { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        .bg-light-secondary { background: rgba(236, 72, 153, 0.1); color: var(--secondary); }
        .bg-light-success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .bg-light-info { background: rgba(59, 130, 246, 0.1); color: var(--info); }
        .bg-light-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }

        .card-label {
            font-size: 11px; /* Smaller label */
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            font-family: 'Inter', sans-serif !important;
        }

        .card-value {
            font-family: 'Poppins', sans-serif !important;
            font-size: 20px; /* Compact number */
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            line-height: 1;
        }

        /* --- FILTER BAR --- */
        .filter-container {
            background: #fff;
            padding: 8px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            display: inline-flex;
            width: 100%;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .filter-container ul {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
            justify-content: space-between;
        }

        .filter-container li { flex: 1; text-align: center; padding: 0 2px; }

        .filter-container a {
            display: block;
            padding: 8px 12px;
            color: #64748b;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 13px;
            font-family: 'Inter', sans-serif !important;
        }

        .filter-container li.active a,
        .filter-container a:hover {
            background-color: var(--primary);
            color: #fff;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
            transform: translateY(-1px);
        }

        /* --- CHART CARD --- */
        .chart-card {
            background: #fff;
            border-radius: 16px;
            padding: 0;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.02);
            display: flex; flex-direction: column;
        }
        .chart-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            background: #fff;
        }
        .chart-header h5 {
            margin: 0;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 700;
            color: var(--dark);
            font-size: 16px;
        }
        .chart-body { 
            padding: 20px; 
            height: 350px; /* Fixed height */
            width: 100%;
            position: relative;
        }
        
        .page-header h3 {
            font-family: 'Poppins', sans-serif !important;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 22px;
        }
    </style>
</head>

<div class="page-body">
    
    <div class="loader-wrapper">
        <div class="loader-container">
            <div class="loader-ring"></div>
            <img src="<?php echo base_url('assets/vendor/images/cheru-removebg-preview.png'); ?>" alt="Loading..." class="loader-logo">
        </div>
    </div>

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?= $page_lang->dashboard; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row d-flex align-items-stretch">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon-wrapper bg-light-primary">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <p class="card-label"><?=$page_lang->total_item; ?></p>
                        <h3 class="card-value counter"><?php echo $total_order_qty->total_item; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon-wrapper bg-light-success">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <div>
                        <p class="card-label">Total delivered orders</p>
                        <h3 class="card-value counter"><?php echo isset($delivered_orders_count) ? $delivered_orders_count : 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon-wrapper bg-light-warning">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <p class="card-label">Total pending orders</p>
                        <h3 class="card-value counter"><?php echo isset($pending_orders_count) ? $pending_orders_count : 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon-wrapper bg-light-info">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div>
                        <p class="card-label"><?=$page_lang->total_vendor; ?></p>
                        <h3 class="card-value counter"><?php echo $vendor_total->total; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    if ($this->uri->segment(4) === FALSE){ $arg = 0; } else { $arg = $this->uri->segment(4); }   
    $staus =''; $customdate_staus='';$today_staus='';$week_staus='';$annual_staus='';$month_staus='';

    if($arg =='customdate'){ $customdate_staus ='active'; }
    elseif($arg =='today'){ $today_staus ='active'; }
    elseif($arg =='week'){ $week_staus ='active'; }
    elseif($arg =='month'){ $month_staus ='active'; }
    elseif($arg =='annual'){ $annual_staus ='active'; }
    else{ $staus ='active'; }
    ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-8 col-lg-10 col-md-12">
                <div class="filter-container">
                    <ul id="report_filter">
                        <li class="<?php echo $today_staus ?>">
                            <a href="/admin/dashboard/index/today"><?= $page_lang->today; ?></a>
                        </li>
                        <li class="<?php echo $week_staus ?>">
                            <a href="/admin/dashboard/index/week"><?= $page_lang->week; ?></a>
                        </li>
                        <li class="<?php echo $month_staus; echo $staus; ?>">
                            <a href="/admin/dashboard/index/month"><?= $page_lang->month; ?></a>
                        </li>
                        <li class="<?php echo $annual_staus ?>">
                            <a href="/admin/dashboard/index/annual"><?= $page_lang->annual; ?></a>
                        </li>
                        <li class="<?php echo $customdate_staus ?>">
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-filter mr-1"></i> <?= $page_lang->custom_date; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row d-flex align-items-stretch">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon-wrapper bg-light-primary">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div>
                        <?php $count_all_visiter = isset($count_all_visiter) ? $count_all_visiter:'-'; ?>  
                        <p class="card-label"><?= $page_lang->order; ?></p>
                        <h3 class="card-value counter"><?php echo isset($summary->total_orders) ? $summary->total_orders : 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon-wrapper bg-light-secondary">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                    <div>
                        <?php $count_all_subscribe= isset($count_all_subscribe) ? $count_all_subscribe:'-'; ?> 
                        <p class="card-label"><?= $page_lang->income; ?></p>
                        <h3 class="card-value counter"><?php echo isset($summary->total_income) ? $summary->total_income : 0.00; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon-wrapper bg-light-success">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <p class="card-label"><?= $page_lang->products; ?></p>
                        <h3 class="card-value counter"><?php echo isset($summary->total_products) ? $summary->total_products : 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-icon-wrapper bg-light-info">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <p class="card-label"><?= $page_lang->users; ?></p>
                        <h3 class="card-value counter"><?php echo isset($summary->total_users) ? $summary->total_users : 0; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mb-5">
        <div class="row">
            <div class="col-md-12">
                <div class="chart-card">
                    <div class="chart-header">
                        <h5><?= $page_lang->summary; ?></h5>
                    </div>
                    <div class="chart-body">
                        <div class="row mb-4">
                            <div class="col-sm-6 text-center">
                                <h2 class="font-weight-bold text-primary mb-1" style="font-family: 'Poppins', sans-serif !important; font-size: 22px;"><?php echo $total_product_qty = isset($total_product_qty) ? $total_product_qty:0; ?></h2>
                                <p class="text-muted font-weight-bold text-uppercase ls-1" style="font-size:11px;"><?= $page_lang->products; ?></p>
                            </div>
                            <div class="col-sm-6 text-center">
                                <h2 class="font-weight-bold text-success mb-1" style="font-family: 'Poppins', sans-serif !important; font-size: 22px;"><?php echo $sale_amount = isset($sale_amount) ? $sale_amount:0; ?></h2>
                                <p class="text-muted font-weight-bold text-uppercase ls-1" style="font-size:11px;"><?= $page_lang->sale; ?></p>
                            </div>
                        </div>
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="myModal" class="modal fade customModel" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border:none; box-shadow: var(--hover-shadow);">
                <div class="modal-header" style="border-bottom: 1px solid #f1f5f9; padding: 20px 25px;">
                    <h5 class="modal-title font-weight-bold" style="font-family: 'Poppins', sans-serif !important;"><?= $page_lang->custom_date; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" style="opacity: 0.5;">&times;</button>                
                </div>
                <div class="modal-body p-4">
                    <form id="filter_data" method="post">
                        <div class="row">                           
                            <div class="col-md-12 mb-4">
                                <label for="Order Date" class="font-weight-bold text-dark mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Select Date Range</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0;"><i class="fa fa-calendar text-primary"></i></span>
                                    </div>
                                    <input type="text" id="date" name="date" class="form-control border-left-0" style="height: 45px; border-radius: 0 10px 10px 0; border-color: #e2e8f0; font-weight: 500;" placeholder="YYYY-MM-DD">
                                </div>
                            </div>
                            <div class="col-md-12">                  
                                <button class="btn btn-primary btn-block font-weight-bold" style="height: 45px; border-radius: 10px; font-size: 14px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);" type="button">Apply Filter</button>
                            </div>                          
                        </div>
                    </form>
            </div>
        </div>
    </div>

    <script>
    $('#date').daterangepicker({
        autoUpdateInput: true,
        "singleDatePicker": false,
        "showDropdowns": false,
        linkedCalendars: false,
        locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
    }, function(start, end, label) {
        $('#date').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
    });

    $("#date").on('apply.daterangepicker', function(ev, Picker) {
        $('#date').val(Picker.startDate.format('YYYY-MM-DD') + ' - ' + Picker.endDate.format('YYYY-MM-DD'));
    });

    $('#date').on('cancel.daterangepicker', function(ev, picker) {
        $('#date').val('');
    });

    $("#filter_data").submit(function(event){
        event.preventDefault();
        $("#myModal .close").click()
    });
    
    $('#myModal .btn-primary').on('click', function (e){
        var dt = $('#date').val();
        if(dt) {
            var parts = dt.split(' - ');
            var start = (parts[0] || '').trim();
            var end = (parts[1] || '').trim();
            if(!end) { end = start; }
            window.location.replace("/admin/dashboard/index/customdate/"+start+"/"+end);
            $("#myModal .close").click()
        }
    });

    $(window).on('load', function() {
        $('.loader-wrapper').fadeOut(600);
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script>
    var ctx = document.getElementById('myChart').getContext('2d');
    
    // Create Gradient
    var gradientFill = ctx.createLinearGradient(0, 0, 0, 400);
    gradientFill.addColorStop(0, "rgba(99, 102, 241, 0.3)");
    gradientFill.addColorStop(1, "rgba(99, 102, 241, 0.0)");

    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= isset($graph_labels) ? $graph_labels : "['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']"; ?>,
            datasets: [{
                label: '#<?=$page_lang->products; ?>',
                data: <?= $product_qty; ?>,
                backgroundColor: gradientFill,
                borderColor: '#6366f1',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1',
                pointRadius: 5,
                pointHoverRadius: 7,
                lineTension: 0.4
            },{
                label: '#<?=$page_lang->sale; ?>',
                data: <?= isset($graph_sale_amount) ? $graph_sale_amount : '[]'; ?>,
                backgroundColor: 'rgba(16, 185, 129, 0.15)',
                borderColor: '#10b981',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#10b981',
                pointRadius: 5,
                pointHoverRadius: 7,
                lineTension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: { 
                        beginAtZero: true,
                        fontColor: '#94a3b8',
                        padding: 10,
                        fontFamily: 'Inter'
                    },
                    gridLines: { 
                        color: "rgba(0,0,0,0.05)",
                        drawBorder: false
                    }
                }],
                xAxes: [{
                    ticks: {
                        fontColor: '#94a3b8',
                        padding: 10,
                        fontFamily: 'Inter'
                    },
                    gridLines: { display: false }
                }]
            },
            legend: { display: true },
            tooltips: {
                backgroundColor: '#1e293b',
                titleFontFamily: 'Poppins',
                bodyFontFamily: 'Inter',
                cornerRadius: 8,
                xPadding: 12,
                yPadding: 12,
                displayColors: false
            }
        }
    });

    $('.carousel').carousel({
      interval: false,
    });
    </script>

</div>
</body>
</html>