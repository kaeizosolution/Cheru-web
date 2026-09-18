<?php
$path = base_url();
$TYPE = isset($_SESSION['type']) ? $_SESSION['type'] : 'vendor';

if($this->session->userdata($TYPE)){
    $logo = '';
    $session_obj = $this->session->userdata($TYPE);
    $logo = isset($session_obj['logo']) ? unserialize($session_obj['logo']) : '';
    $store_logo = isset($logo['thumb']) ? $logo['thumb'] : ''; 
    
    // FIX: Changed login_id to vendor_id to match your new session structure
    $logged_in = isset($session_obj['vendor_id']) ? $session_obj['vendor_id'] : (isset($session_obj['login_id']) ? $session_obj['login_id'] : '');
    
    // Fetch latest vendor image/logo from database dynamically
    if (!empty($logged_in) && isset($this->db)) {
        $vendor_db = $this->db->select('*')->from('ec_vendor')->where('vendor_id', (int)$logged_in)->get()->row();
        if ($vendor_db) {
            if (!empty($vendor_db->vendor_img)) {
                $vi = @unserialize($vendor_db->vendor_img);
                if (is_array($vi) && isset($vi['thumb'])) {
                    $store_logo = $vi['thumb'];
                }
            }
            if (empty($store_logo) && !empty($vendor_db->logo)) {
                $l = @unserialize($vendor_db->logo);
                if (is_array($l) && isset($l['thumb'])) {
                    $store_logo = $l['thumb'];
                }
            }
        }
    }

    // FIX: Changed fname to vendor_name or added fallback
    $name  = isset($session_obj['vendor_name']) ? $session_obj['vendor_name'] : (isset($session_obj['fname']) ? $session_obj['fname'] : 'Vendor');
    
    // FIX: Changed email to vendor_email or added fallback
    $email = isset($session_obj['vendor_email']) ? $session_obj['vendor_email'] : (isset($session_obj['email']) ? $session_obj['email'] : '');
}

// Expose vendor access token for JS API calls (currency switcher)
$_vendor_sess   = $this->session->userdata('vendor');
$_vendor_token  = (is_array($_vendor_sess) && !empty($_vendor_sess['access_token']))
                    ? $_vendor_sess['access_token']
                    : '';

$page_lang  = get_page_language_data('header_page_lang');
$page_lang  = get_page_language_data('admin_page_lang');
$controller = $this->router->fetch_class();
$model = $this->router->fetch_method();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">   
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <title>Vendor Panel</title>  
    
    <link rel="shortcut icon" type="image/png" href="/assets/vendor/images/favicon.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="/assets/vendor/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/vendor/plugins/select2/css/select2.min.css">
    <link href="/assets/vendor/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="/assets/vendor/plugins/jquery-nice-select/css/nice-select.css" rel="stylesheet">
    
    <link href="/assets/vendor/css/style.css?r=1" rel="stylesheet">
    <link href="/assets/vendor/css/custom.css?r=1" rel="stylesheet">
    <link rel="stylesheet" href="/assets/plugins/parsley/parsley.css" />

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/popper.min.js"></script>
    <script src="/assets/js/bootstrap.js"></script>
    <script src="/assets/plugins/daterangepicker/moment.min.js"></script>
    <script src="/assets/plugins/daterangepicker/daterangepicker.js"></script>

    <style>
        :root {
            --primary: #6366f1;
            --dark: #1e293b;
            --sidebar-bg: #191c4d; /* Deep Blue */
            --accent-color: #6366f1;
            --text-color: rgba(255, 255, 255, 0.85);
            --active-bg: rgba(255, 255, 255, 0.1);
            --sidebar-width: 17rem;
            --collapsed-width: 80px;
            --header-height: 80px;
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background: #f3f5f8;
            overflow-x: hidden; 
        }
        
        /* FIX: Force FontAwesome */
        .fa, .fas, .far, .fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
            font-weight: 900 !important;
            font-style: normal;
        }

        /* --- 1. ORBIT LOADER --- */
        .loader-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: var(--sidebar-bg);
            z-index: 99999; /* Extremely high Z-Index to block everything */
            display: flex; justify-content: center; align-items: center;
        }
        .loader-container {
            position: relative; width: 150px; height: 150px;
            display: flex; justify-content: center; align-items: center;
        }
        .loader-logo { 
            width: 80px; height: auto; object-fit: contain; z-index: 10;
            animation: pulse-logo 2s ease-in-out infinite;
        }
        .loader-ring {
            position: absolute; width: 100%; height: 100%; border-radius: 50%;
            border: 3px solid transparent; border-top-color: var(--accent-color);
            animation: spin 1.5s linear infinite;
        }
        .loader-ring:before {
            content: ""; position: absolute; top: 10px; left: 10px; right: 10px; bottom: 10px;
            border-radius: 50%; border: 3px solid transparent; border-top-color: #fff;
            animation: spin-reverse 3s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes spin-reverse { 0% { transform: rotate(0deg); } 100% { transform: rotate(-360deg); } }
        @keyframes pulse-logo { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(0.9); opacity: 0.8; } }

        /* --- 2. NAV HEADER --- */
        .nav-header {
            background: var(--sidebar-bg) !important;
            width: var(--sidebar-width); 
            height: var(--header-height);
            z-index: 1001;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            position: fixed; top: 0; left: 0;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s;
        }
        
        .logo-abbr { display: none; max-height: 45px !important; width: auto !important; margin: 0 !important; }
        .nav-header .brand-title {
            margin-left: -15px !important;
            max-width: 211px !important;
        }
        .brand-title {
            display: block;
            max-height: 104px !important;
            width: auto !important;
            margin: 0 !important;
            filter: brightness(0) invert(1) !important;
        }
        .nav-header .brand-logo {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* --- 3. TOP HEADER --- */
        .header {
            background: #fff;
            padding-left: var(--sidebar-width); 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            height: var(--header-height);
            position: fixed; top: 0; left: 0; width: 100%;
            z-index: 1000;
            transition: all 0.3s;
        }

        .header-content {
            height: 100%;
            padding-right: 30px;
            padding-left: 20px;
            background: linear-gradient(to right, #fff 40%, rgba(25, 28, 77, 0.8)), 
                        url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: right center;
            border-radius: 0 0 0 30px;
            display: flex; align-items: center;
        }
        
        .navbar { padding: 0; height: 100%; width: 100%; }
        
        /* Toggle Button */
        .toggle-sidebar {
            color: #333; cursor: pointer; font-size: 24px; margin-right: 20px;
            display: flex; align-items: center;
        }
        
        /* Profile & Currency Dropdown Styling */
        .header-right .header-profile > a.nav-link,
        .header-right .header-currency > a.nav-link {
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 8px 16px;
            border: 1px solid rgba(255,255,255,0.3);
            display: inline-flex; 
            align-items: center;
            color: #fff !important;
            height: 48px;
            box-sizing: border-box;
        }
        .header-right .header-currency > a.nav-link:hover {
            background: rgba(255,255,255,0.3);
            text-decoration: none;
        }
        .header-right .header-currency .currency-symbol {
            font-size: 15px; 
            font-weight: 700; 
            margin-right: 6px; 
            line-height: 1;
        }
        .header-right .header-currency .currency-code {
            font-size: 13px; 
            font-weight: 600; 
            line-height: 1;
        }
        .header-right .header-currency .currency-arrow {
            font-size: 11px; 
            margin-left: 6px; 
            line-height: 1;
        }
        .header-info span { color: #fff !important; font-weight: 600; }
        .header-info small { color: rgba(255,255,255,0.8) !important; }
        
        /* Dropdown Position */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 8px;
            margin-top: 5px !important;
            right: 0 !important;
            left: auto !important;
            top: 100% !important;
            position: absolute !important;
            transform: none !important;
            min-width: 160px;
            z-index: 1005;
        }

        /* --- 5. SIDEBAR --- */
        .deznav {
            background: var(--sidebar-bg) !important;
            width: var(--sidebar-width);
            position: fixed;
            top: var(--header-height);
            bottom: 0;
            left: 0;
            z-index: 999;
            overflow-y: visible !important; /* Allows tooltips */
            transition: all 0.3s;
            padding-bottom: 50px;
        }
        
        .deznav-scroll {
            height: 100%;
            overflow-y: auto;
            overflow-x: visible;
        }
        
        .deznav-scroll::-webkit-scrollbar { width: 5px; }
        .deznav-scroll::-webkit-scrollbar-track { background: transparent; }
        .deznav-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        .metismenu { background: transparent !important; padding: 0; margin: 0; display: flex; flex-direction: column;}
        .metismenu li { position: relative; width: 100%; display: block; }

        .metismenu li a {
            color: var(--text-color) !important;
            font-weight: 500;
            font-size: 14px;
            border-left: 4px solid transparent;
            padding: 15px 25px;
            transition: all 0.2s;
            display: flex; 
            align-items: center; 
            justify-content: flex-start; 
            text-decoration: none;
        }
        
        .metismenu li a i {
            color: rgba(255,255,255,0.6) !important;
            font-size: 18px;
            width: 30px; text-align: center; margin-right: 10px;
        }

        .metismenu li a:hover,
        .metismenu li.mm-active > a {
            background: var(--active-bg);
            color: #fff !important;
            border-left-color: var(--primary);
        }
        .metismenu li.mm-active > a i { color: #fff !important; }

        /* --- 6. CONTENT BODY --- */
        .content-body {
            margin-left: 17rem;
            padding-top: 100px;
            min-height: 100vh;
            transition: all 0.3s;
            position: relative;
            z-index: 1;
        }

        /* =========================================
           MINI SIDEBAR LOGIC
           ========================================= */
        
        body.sidebar-collapsed .nav-header { width: var(--collapsed-width); }
        body.sidebar-collapsed .deznav { width: var(--collapsed-width); }
        body.sidebar-collapsed .header { padding-left: var(--collapsed-width); }
        body.sidebar-collapsed .content-body { margin-left: var(--collapsed-width); }

        /* Logo Swap */
        body.sidebar-collapsed .brand-title { display: none !important; }
        body.sidebar-collapsed .logo-abbr { display: block !important; margin: 0 auto !important; }
        
        /* Hide Text */
        body.sidebar-collapsed .metismenu li a .nav-text { display: none; }
        
        /* Center Icons */
        body.sidebar-collapsed .metismenu li a { justify-content: center; padding: 15px 0; }
        body.sidebar-collapsed .metismenu li a i { margin: 0; font-size: 22px; }

        /* Floating Tooltip */
        body.sidebar-collapsed .metismenu li:hover .nav-text {
            display: block !important;
            position: absolute;
            left: var(--collapsed-width);
            top: 0;
            background: #191c4d;
            color: #fff !important;
            padding: 14px 20px;
            width: max-content;
            z-index: 99999;
            font-weight: 600;
            border-radius: 0 6px 6px 0;
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
            white-space: nowrap;
            opacity: 1;
            visibility: visible;
            pointer-events: none;
        }

        @media (max-width: 767px) {
            .nav-header { width: 5rem; }
            .header { padding-left: 5rem; }
            .content-body { margin-left: 5rem; }
            .brand-title { display: none; }
            .logo-abbr { display: block; }
        }
    </style>
</head>
<body>

<?php if(isset($logged_in)){ ?> 
    
    <div class="loader-wrapper">
        <div class="loader-container">
            <div class="loader-ring"></div>
            <img src="/assets/vendor/images/cheru-removebg-preview.png" alt="Loading..." class="loader-logo">
        </div>
    </div>
    
    <div id="main-wrapper">

        <div class="nav-header">
            <a href="/vendor/dashboard" class="brand-logo">
                <img class="logo-abbr" src="/assets/vendor/images/favicon.png" alt="Icon">
                <img class="brand-title" src="/assets/vendor/images/cheru-removebg-preview.png" alt="Logo">
            </a>
        </div>

        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        
                        <div class="header-left d-flex align-items-center">
                            <div class="toggle-sidebar" title="Toggle Sidebar">
                                <i class="fa fa-bars"></i>
                            </div>
                        </div>

                        <ul class="navbar-nav header-right">
                            <?php
                            /* --- Currency Switcher Dropdown (Vendor Header) --- */
                            $CI_hdr =& get_instance();
                            // Use the default active DB connection (avoids slave config issues)
                            $all_currencies = $CI_hdr->db
                                ->select('currency_id, name, iso_code, symbol, rate, basic')
                                ->from('ec_currency')
                                ->where('status', '1')
                                ->order_by('basic', 'desc')
                                ->order_by('name', 'asc')
                                ->get()->result();
                            if (empty($all_currencies)) {
                                $dummy = new stdClass();
                                $dummy->currency_id = 1;
                                $dummy->name = 'US Dollar';
                                $dummy->iso_code = 'USD';
                                $dummy->symbol = '$';
                                $dummy->rate = 1.0;
                                $dummy->basic = 1;
                                $all_currencies = [$dummy];
                            }
                            $current_cur_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
                            $current_cur = null;
                            foreach ($all_currencies as $_c) {
                                if ((int)$_c->currency_id === $current_cur_id) {
                                    $current_cur = $_c;
                                    break;
                                }
                            }
                            // Default to the basic/base currency if none selected
                            if (!$current_cur && !empty($all_currencies)) {
                                foreach ($all_currencies as $_c) {
                                    if ($_c->basic == 1) { $current_cur = $_c; break; }
                                }
                                if (!$current_cur) $current_cur = $all_currencies[0];
                            }
                            ?>
                            <?php if (!empty($all_currencies) && count($all_currencies) >= 1): ?>
                            <li class="nav-item dropdown header-currency mr-2 me-2" style="align-self:center;">
                                <a class="nav-link dropdown-toggle"
                                   href="#" role="button" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="currency-symbol"><?= htmlspecialchars($current_cur ? $current_cur->symbol : '$') ?></span>
                                    <span class="currency-code"><?= htmlspecialchars($current_cur ? $current_cur->iso_code : 'USD') ?></span>
                                    <i class="fa fa-angle-down currency-arrow"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right dropdown-menu-end shadow border-0" style="min-width:180px; border-radius:10px; padding:8px 0;">
                                    <li><h6 class="dropdown-header" style="font-size:11px; text-transform:uppercase; color:#999; letter-spacing:.5px;">Select Currency</h6></li>
                                    <?php foreach ($all_currencies as $_cur): ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center vendor-cur-item <?= ((int)$_cur->currency_id === $current_cur_id) ? 'active fw-bold' : '' ?>"
                                           href="#"
                                           data-cur-id="<?= (int)$_cur->currency_id ?>"
                                           data-cur-symbol="<?= htmlspecialchars($_cur->symbol) ?>"
                                           data-cur-code="<?= htmlspecialchars($_cur->iso_code) ?>"
                                           style="font-size:13px; padding:8px 16px; justify-content: space-between; cursor:pointer;">
                                            <span>
                                                <span style="width:22px; display:inline-block; text-align:center; font-weight:700; margin-right:8px;"><?= htmlspecialchars($_cur->symbol) ?></span>
                                                <span><?= htmlspecialchars($_cur->iso_code) ?> — <?= htmlspecialchars($_cur->name) ?></span>
                                            </span>
                                            <?php if ((int)$_cur->currency_id === $current_cur_id): ?>
                                            <i class="fa fa-check text-success vendor-cur-check" style="font-size:11px;"></i>
                                            <?php else: ?>
                                            <i class="fa fa-check text-success vendor-cur-check" style="font-size:11px; visibility:hidden;"></i>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <?php endif; ?>

                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                                    <?php if(isset($store_logo) && $store_logo) {?>
                                         <img src="<?= $store_logo; ?>" id="headerProfileImg" width="20" alt="" class="rounded-circle">
                                    <?php }else{ ?> 
                                        <img src="/assets/vendor/images/profile/pic1.jpg" id="headerProfileImg" width="20" alt="" class="rounded-circle">
                                    <?php }?>
                                    <div class="header-info ms-3">
                                        <span>Hi, <b><?=$name; ?></b></span>
                                        <small style="display:block; opacity:0.8;"><?=$email; ?></small>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="/vendor/profile" class="dropdown-item ai-icon">
                                        <i class="fa fa-user text-primary"></i>
                                        <span class="ms-2">Profile </span>
                                    </a>
                                    <a href="/<?= $TYPE?>/auth/logout" class="dropdown-item ai-icon">
                                        <i class="fa fa-sign-out-alt text-danger"></i>
                                        <span class="ms-2">Logout </span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <div class="deznav">
            <div class="deznav-scroll">
                <ul class="metismenu" id="menu">
                    <li><a class="ai-icon" href="<?php echo site_url("vendor/dashboard"); ?>" aria-expanded="false">
                            <i class="fa fa-tv"></i><span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <li><a class="ai-icon" href="<?php echo site_url("vendor/earning"); ?>" aria-expanded="false">
                            <i class="fa fa-money-bill-wave"></i><span class="nav-text">Earning</span>
                        </a>
                    </li>
                    
                    <li><a class="ai-icon" href="<?= $path.$_SESSION['type']; ?>/product" aria-expanded="false">
                            <i class="fa fa-box-open"></i><span class="nav-text">Product</span>
                        </a>
                    </li>

                    <li><a class="ai-icon" href="<?= $path.$_SESSION['type']; ?>/returns" aria-expanded="false">
                            <i class="fa fa-undo"></i><span class="nav-text">Returns</span>
                        </a>
                    </li>

                    <li><a class="ai-icon" href="<?php echo site_url("vendor/order"); ?>" aria-expanded="false">
                            <i class="fa fa-shopping-cart"></i><span class="nav-text">Orders</span>
                        </a>                    
                    </li>

                    <li><a class="ai-icon" href="<?php echo site_url("vendor/reviews"); ?>" aria-expanded="false">
                            <i class="fa fa-star"></i><span class="nav-text">Reviews</span>
                        </a>                    
                    </li>

                    <li><a class="ai-icon" href="<?php echo site_url("vendor/product_enquiries"); ?>" aria-expanded="false">
                            <i class="fa fa-envelope-open-text"></i><span class="nav-text">Messagner</span>
                        </a>
                    </li>

                    <!-- <li><a class="ai-icon" href="<?php echo site_url("vendor/order/finance"); ?>" aria-expanded="false">
                            <i class="fa fa-scale-balanced"></i><span class="nav-text">Finance</span>
                        </a>
                    </li> -->

                    <!-- <li><a class="ai-icon" href="/vendor/notification" aria-expanded="false">
                            <i class="fa fa-bell"></i><span class="nav-text">Notification</span>
                        </a>
                    </li> -->
                    
                    <li><a class="ai-icon" href="/vendor/profile" aria-expanded="false">
                            <i class="fa fa-user"></i><span class="nav-text">Profile</span>
                        </a>
                    </li>

                    <li><a class="ai-icon" href="/<?= $TYPE?>/auth/logout" aria-expanded="false">
                            <i class="fa fa-sign-out-alt"></i><span class="nav-text">Logout</span>
                        </a>
                    </li>
                </ul>
                
                <div class="copyright text-center mt-4">
                    <p style="font-size:12px; color:rgba(255,255,255,0.5);"><strong>CHERU</strong> © <?php echo date("Y"); ?></p>
                </div>
            </div>
        </div>

<?php } ?>

<script>
/* Vendor access token injected from PHP session — used for API calls */
var VENDOR_ACCESS_TOKEN = '<?= addslashes($_vendor_token) ?>';
</script>
<script>
$(document).ready(function() {
    // Sidebar Toggle Logic
    $('.toggle-sidebar').click(function() {
        $('body').toggleClass('sidebar-collapsed');
    });

    // Loader Fade Out (With Failsafe)
    function hideLoader() {
        $('.loader-wrapper').fadeOut(500);
    }

    $(window).on('load', function() {
        hideLoader();
    });
    
    // Force hide after 1s in case window.load missed it
    setTimeout(hideLoader, 1000);

    /* ── Vendor Currency Switcher — calls /api/v1/vendor/set-currency ── */
    $(document).on('click', '.vendor-cur-item', function(e) {
        e.preventDefault();
        var $el       = $(this);
        var curId     = $el.data('cur-id');
        var curSymbol = $el.data('cur-symbol');
        var curCode   = $el.data('cur-code');

        if ($el.hasClass('active')) return; // already selected, do nothing

        // Build headers — use Bearer token if available, else rely on session cookie
        var headers = { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        if (VENDOR_ACCESS_TOKEN && VENDOR_ACCESS_TOKEN !== '') {
            headers['Authorization'] = 'Bearer ' + VENDOR_ACCESS_TOKEN;
        }

        // Optimistic UI — update the displayed symbol/code immediately
        $('.currency-symbol').text(curSymbol);
        $('.currency-code').text(curCode);
        $('.vendor-cur-item').removeClass('active fw-bold');
        $('.vendor-cur-check').css('visibility', 'hidden');
        $el.addClass('active fw-bold');
        $el.find('.vendor-cur-check').css('visibility', 'visible');

        // Call API
        fetch('<?= base_url('api/v1/vendor/set-currency') ?>', {
            method : 'POST',
            headers: headers,
            body   : JSON.stringify({ currency_id: curId })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data && (data.status === 1 || data.status === '1')) {
                // Reload to apply currency conversion across the page
                window.location.reload();
            } else {
                console.warn('Vendor currency switch API error:', data);
                // Reload anyway — server session was set, UI will catch up
                window.location.reload();
            }
        })
        .catch(function(err) {
            console.error('Vendor currency switch failed:', err);
            // Fallback: reload so CurrencySwitcher controller logic takes over on next request
            window.location.reload();
        });
    });
    /* ── End Vendor Currency Switcher ── */
});
</script>