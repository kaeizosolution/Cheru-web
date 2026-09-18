<?php
// Ensure the URL helper is loaded
// $autoload['helper'] = array('url');

$TYPE = $_SESSION['type'];
if($this->session->userdata($TYPE)){
    $session_obj = $this->session->userdata($TYPE);
    $logged_in = $session_obj['logged_in'];
    $name      = $session_obj['fname'];
    $super_admin = $session_obj['super_admin'];
    $image = isset($session_obj['image']) ? $session_obj['image'] : '';
}
$page_lang  = get_page_language_data('header_page_lang');
$page_lang  = get_page_language_data('admin_page_lang');
$controller = $this->router->fetch_class(); // Gets 'tax', 'product', etc.
$model = $this->router->fetch_method();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url('assets/vendor/images/favicon.png'); ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/bootstrap.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/cust_style.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/responsive.css'); ?>">
    
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/plugins/parsley/parsley.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/plugins/select2/select2.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/plugins/daterangepicker/daterangepicker.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/plugins/datatables/dataTables.bootstrap4.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/plugins/datatables/responsive.bootstrap4.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/common/css/snackbar.css'); ?>">

    <script src="<?php echo base_url('assets/admin/js/jquery-3.2.1.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/js/popper.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/js/bootstrap.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/plugins/sweetalert/sweetalert.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/plugins/parsley/parsley.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/plugins/select2/select2.full.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/plugins/daterangepicker/moment.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/plugins/daterangepicker/daterangepicker.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/plugins/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/admin/plugins/datatables/dataTables.responsive.min.js'); ?>"></script>

    <style>
        :root {
            --sidebar-bg: #191c4d;       
            --sidebar-width: 260px;
            --collapsed-width: 80px;
            --header-height: 80px;
            --active-bg: rgba(255, 255, 255, 0.15);
            --text-color: rgba(255, 255, 255, 0.9);
            --accent-color: #6366f1;
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background: #f4f7f6;
            margin: 0;
            overflow-x: hidden;
        }

        /* FORCE FONTAWESOME */
        .fa, .fas, .far, .fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
            font-weight: 900 !important;
            font-style: normal;
        }

        /* --- ORBIT LOADER --- */
        .loader-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: var(--sidebar-bg);
            z-index: 99999;
            display: flex; justify-content: center; align-items: center;
        }
        .loader-logo { width: 150px; animation: logo-pulse 2s ease-in-out infinite; }
        @keyframes logo-pulse { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.8; transform: scale(1.05); } 100% { opacity: 1; transform: scale(1); } }
        .loader-dots { margin-top: 20px; display: flex; gap: 8px; }
        .loader-dots div { width: 10px; height: 10px; background: #fff; border-radius: 50%; animation: bounce 0.6s infinite alternate; }
        .loader-dots div:nth-child(2) { animation-delay: 0.2s; }
        .loader-dots div:nth-child(3) { animation-delay: 0.4s; }
        @keyframes bounce { to { transform: translateY(-10px); } }


        /* --- HEADER --- */
        .page-main-header {
            height: var(--header-height);
            display: flex;
            background: #fff;
            position: fixed;
            top: 0; left: 0; width: 100%;
            z-index: 1050; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .main-header-left {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: var(--sidebar-bg);
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 0 10px; 
            transition: all 0.3s;
            position: relative;
            z-index: 1051;
        }

        .logo-wrapper { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
        .logo-wrapper a {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .logo-wrapper img { max-height: 104px !important; width: auto !important; object-fit: contain; transition: all 0.3s; margin: 0 !important; }
        .logo-wrapper .logo-big {
            margin-left: -15px !important;
            max-width: 211px !important;
        }
        .logo-small { display: none; max-height: 45px !important; width: auto !important; margin: 0 !important; }

        .main-header-right {
            flex-grow: 1;
            background: linear-gradient(rgba(25, 28, 77, 0.75), rgba(25, 28, 77, 0.55)), 
                        url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: space-between;
            padding-right: 30px;
            padding-left: 20px;
        }

        /* TOGGLE BUTTON */
        .toggle-sidebar {
            color: #fff; cursor: pointer; font-size: 22px; width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center; border-radius: 4px;
            margin-right: auto;
        }
        .toggle-sidebar:hover { background: rgba(255,255,255,0.2); transform: scale(1.1); }

        /* HEADER BUTTONS */
        .nav-menus .dropdown-toggle {
            background-color: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            padding: 8px 15px !important;
            border-radius: 4px !important;
            font-size: 13px !important;
            display: flex; align-items: center; cursor: pointer; text-transform: capitalize; box-shadow: none !important;
        }
        .nav-menus .dropdown-toggle:hover { background-color: rgba(255, 255, 255, 0.25) !important; }
        .nav-menus .dropdown-toggle::after { display: none !important; } 
        .nav-menus .dropdown-menu { border: none; box-shadow: 0 5px 25px rgba(0,0,0,0.15); border-radius: 6px; margin-top: 10px; }
        .nav-menus .dropdown-item { padding: 10px 20px; font-size: 14px; color: #333; }
        .nav-menus .dropdown-item:hover { background-color: #f0f0f0; }


        /* --- SIDEBAR --- */
        .page-sidebar {
            width: var(--sidebar-width);
            position: fixed; top: var(--header-height); bottom: 0; left: 0;
            background: var(--sidebar-bg); 
            z-index: 1000;
            overflow-y: auto; transition: all 0.3s;
            padding-top: 10px;
            padding-bottom: 50px;
        }
        
        .sidebar-menu { list-style: none; padding: 0; margin: 0; width: 100%; }
        .sidebar-menu li { position: relative; width: 100%; display: block; }

        .page-sidebar .sidebar-menu li > a {
            display: flex; align-items: center;
            padding: 14px 25px;
            color: var(--text-color) !important;
            text-decoration: none;
            transition: all 0.2s; border-left: 3px solid transparent;
            width: 100%;
        }

        .page-sidebar .sidebar-menu li a i {
            width: 35px; font-size: 18px; text-align: left;
            display: inline-flex; align-items: center;
            color: rgba(255,255,255,0.7); 
        }

        .page-sidebar .sidebar-menu li a span { font-size: 14px; font-weight: 500; white-space: nowrap; }

        .page-sidebar .sidebar-menu li a:hover, 
        .page-sidebar .sidebar-menu li.active > a {
            color: #fff !important; background: var(--active-bg);
            border-left-color: var(--accent-color);
        }
        .page-sidebar .sidebar-menu li a:hover i, .page-sidebar .sidebar-menu li.active > a i { color: #fff; }

        /* --- SUBMENU --- */
        .sidebar-submenu {
            display: none; 
            background-color: #13163a;
            list-style: none; padding: 0; margin: 0;
            width: 100%;
        }
        .sidebar-submenu li a { padding-left: 55px !important; font-size: 13px !important; }
        .sidebar-submenu li a i { width: 25px !important; font-size: 12px !important; }
        
        /* Force open if parent is active */
        .sidebar-menu li.active > .sidebar-submenu { display: block; }

        /* --- MINI SIDEBAR LOGIC --- */
        body.sidebar-collapsed .page-sidebar { width: var(--collapsed-width); overflow: visible; background: var(--sidebar-bg) !important; }
        body.sidebar-collapsed .main-header-left { width: var(--collapsed-width); min-width: var(--collapsed-width); padding: 0; justify-content: center; }
        body.sidebar-collapsed .page-body-wrapper { margin-left: var(--collapsed-width); }
        
        body.sidebar-collapsed .logo-wrapper img { display: none; }
        body.sidebar-collapsed .logo-small { display: block !important; }

        body.sidebar-collapsed .sidebar-menu li a span { display: none; } 
        body.sidebar-collapsed .sidebar-menu li a .fa-angle-right { display: none; }
        body.sidebar-collapsed .sidebar-submenu { display: none !important; }

        body.sidebar-collapsed .sidebar-menu li a { justify-content: center; padding: 15px 0; }
        body.sidebar-collapsed .sidebar-menu li a i { margin: 0; width: auto; font-size: 22px; }

        body.sidebar-collapsed .sidebar-menu li:hover a span {
            display: flex !important; position: absolute; left: 80px; top: 0; height: 100%;
            background: #1e293b; color: #fff; padding: 0 20px; align-items: center;
            white-space: nowrap; z-index: 10001; box-shadow: 5px 0 10px rgba(0,0,0,0.1);
            border-radius: 0 6px 6px 0; width: auto;
        }

        /* --- CONTENT PUSH --- */
        .page-body-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            width: auto; padding: 25px; transition: all 0.3s;
        }
        
        /* --- TABLE BUTTONS --- */
        table .btn, .table .btn { color: #fff !important; }
        table .btn i, .table .btn i { color: #fff !important; }
        
        .page-body-wrapper a:not(.sidebar-link) { color: #007bff; text-decoration: none; }
        .page-body-wrapper .btn { color: #fff !important; }
        .page-body-wrapper .btn-primary { background-color: #007bff; border-color: #007bff; }
    </style>
</head>
<body>
<?php if(isset($navigation) && $navigation == 0){} else { ?>
    
    <div class="loader-wrapper">
        <div class="loader-container">
            <div class="loader-ring"></div>
            <img src="<?php echo base_url('assets/vendor/images/cheru-removebg-preview.png'); ?>" alt="Loading..." class="loader-logo">
        </div>
    </div>

    <div class="page-main-header">
        <div class="main-header-left">
            <div class="logo-wrapper">
                <a href="<?php echo base_url('admin/dashboard'); ?>">
                    <img src="<?php echo base_url('assets/vendor/images/cheru-removebg-preview.png'); ?>" alt="Logo" class="logo-big"/>
                    <img src="<?php echo base_url('assets/vendor/images/favicon.png'); ?>" alt="Icon" class="logo-small"/>
                </a>
            </div>
        </div>
        
        <div class="main-header-right">
             <div class="toggle-sidebar" title="Toggle Sidebar"><i class="fa fa-bars"></i></div>

             <ul class="nav-menus d-flex align-items-center" style="list-style:none; margin:0;">
                <li class="mr-3 dropdown">
                    <button class="dropdown-toggle btn" type="button" data-toggle="dropdown">
                        <i class="fa fa-globe mr-2"></i> 
                        <?php 
                             $ci =& get_instance();
                             $curr_lang = $ci->session->userdata('ln');
                             echo ($curr_lang == '12') ? 'French' : 'English'; 
                        ?>
                        <i class="fa fa-chevron-down ml-2" style="font-size:10px;"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="<?php echo base_url('admin/LanguageSwitcher/switchLang/3'); ?>">English</a>
                        <a class="dropdown-item" href="<?php echo base_url('admin/LanguageSwitcher/switchLang/12'); ?>">French</a>
                    </div>
                </li>
                <li class="dropdown" id="account-dropdown-li">
                    <button class="dropdown-toggle btn d-flex align-items-center" type="button" data-toggle="dropdown" id="acct-dd-btn" style="gap:8px;padding:4px 12px;border-radius:4px;background:transparent;border:1px solid rgba(255,255,255,0.3);color:#fff;height:38px;">
                        <?php
                        $__sess_img = isset($session_obj['image']) ? $session_obj['image'] : '';
                        $__sess_name = isset($session_obj['fname']) ? $session_obj['fname'] : 'Admin';
                        if ($__sess_img): ?>
                            <img src="<?= base_url('assets/images/' . $__sess_img) ?>" alt="avatar" style="width:26px;height:26px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.5);flex-shrink:0;">
                        <?php else: ?>
                            <span style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#818cf8,#6366f1);display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0;"><?= strtoupper(substr($__sess_name,0,1)) ?></span>
                        <?php endif; ?>
                        <span style="font-size:13px;font-weight:500;color:#fff;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($__sess_name) ?></span>
                        <i class="fa fa-chevron-down ml-1" style="font-size:10px;color:rgba(255,255,255,0.8);"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" style="min-width:230px;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.12);border:1px solid #e2e8f0;padding:8px 0;margin-top:8px;">
                        <!-- Profile info header -->
                        <div style="padding:12px 16px 10px;border-bottom:1px solid #f1f5f9;">
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <?php if ($__sess_img): ?>
                                    <img src="<?= base_url('assets/images/' . $__sess_img) ?>" alt="avatar" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #e0e7ff;">
                                <?php else: ?>
                                    <span style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#818cf8,#6366f1);display:inline-flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff;flex-shrink:0;"><?= strtoupper(substr($__sess_name,0,1)) ?></span>
                                <?php endif; ?>
                                <div style="min-width:0;">
                                    <div style="font-size:14px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($__sess_name) ?></div>
                                    <div style="font-size:11px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= isset($session_obj['email']) ? htmlspecialchars($session_obj['email']) : '' ?></div>
                                </div>
                            </div>
                        </div>
                        <!-- My Profile link -->
                        <a class="dropdown-item d-flex align-items-center" href="<?= base_url($TYPE . '/admin/profile') ?>" style="padding:10px 16px;font-size:13px;font-weight:500;color:#374151;gap:10px;transition:background .15s;">
                            <span style="width:28px;height:28px;border-radius:7px;background:#ede9fe;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa fa-user" style="font-size:12px;color:#6366f1;"></i>
                            </span>
                            My Profile
                        </a>
                        <div style="border-top:1px solid #f1f5f9;margin:4px 0;"></div>
                        <!-- Logout -->
                        <a class="dropdown-item d-flex align-items-center" href="<?= base_url($TYPE . '/auth/logout') ?>" style="padding:10px 16px;font-size:13px;font-weight:500;color:#ef4444;gap:10px;transition:background .15s;">
                            <span style="width:28px;height:28px;border-radius:7px;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa fa-power-off" style="font-size:12px;color:#ef4444;"></i>
                            </span>
                            <?= isset($page_lang->logout) ? $page_lang->logout : 'Logout' ?>
                        </a>
                    </div>
                </li>
             </ul>
        </div>
    </div>

    <div class="page-sidebar custom-scrollbar">
        <ul class="sidebar-menu">
        <?php
        // Load session data
        $__admin_sess    = $this->session->userdata($TYPE);
        $__is_super      = !empty($__admin_sess['super_admin']) && $__admin_sess['super_admin'] == 1;
        $__permissions   = (isset($__admin_sess['permissions']) && is_array($__admin_sess['permissions']))
                            ? $__admin_sess['permissions'] : [];

        // Helper: can this admin access a module_key?
        // Dashboard is always accessible to all admins regardless of permissions.
        $__always_visible = ['dashboard'];
        $__can_access = function($module_key) use ($__is_super, $__permissions, $__always_visible) {
            if ($__is_super) return true;
            if (in_array(strtolower($module_key), $__always_visible)) return true;
            return array_key_exists($module_key, $__permissions);
        };

        // Fetch all active modules from DB
        $__all_modules = $this->db
            ->select('module_id, parent_id, title, url, icon, module_key')
            ->from('ec_admin_modules')
            ->where('status', 1)
            ->order_by('sort_order', 'ASC')
            ->get()->result();

        // Separate parent and children
        $__parent_mods  = [];
        $__child_mods   = [];
        foreach ($__all_modules as $__m) {
            if ((int)$__m->parent_id === 0) {
                $__parent_mods[] = $__m;
            } else {
                $__child_mods[(int)$__m->parent_id][] = $__m;
            }
        }

        foreach ($__parent_mods as $__mod):
            $__has_children = isset($__child_mods[$__mod->module_id]);

            // For dropdown parents: check if at least one child is accessible
            if ($__has_children) {
                $__any_child_allowed = false;
                foreach ($__child_mods[$__mod->module_id] as $__child) {
                    if ($__can_access($__child->module_key)) {
                        $__any_child_allowed = true;
                        break;
                    }
                }
                if (!$__any_child_allowed) continue; // skip parent if no child accessible
            } else {
                // Single link: check permission for this module's own key
                // Dashboard is always shown for all admins
                if (!$__can_access($__mod->module_key)) continue;
            }

            // Determine active state
            $__is_active = ($controller == strtolower(str_replace('-', '_', basename($__mod->url))));
        ?>
            <li id="nev_<?= $__mod->module_key ?>" <?= $__is_active ? 'class="active"' : '' ?>>
                <?php if ($__has_children): ?>
                    <a href="javascript:void(0);" class="sidebar-link toggle-submenu">
                        <i class="fa <?= htmlspecialchars($__mod->icon) ?>"></i>
                        <span><?= htmlspecialchars($__mod->title) ?></span>
                        <i class="fa fa-angle-right ml-auto"></i>
                    </a>
                    <ul class="sidebar-submenu">
                    <?php foreach ($__child_mods[$__mod->module_id] as $__child):
                        if (!$__can_access($__child->module_key)) continue;
                    ?>
                        <li id="nev_<?= $__child->module_key ?>">
                            <a href="<?= base_url($__child->url) ?>">
                                <i class="fa <?= htmlspecialchars($__child->icon) ?>"></i>
                                <?= htmlspecialchars($__child->title) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <a href="<?= base_url($__mod->url) ?>" class="sidebar-link">
                        <i class="fa <?= htmlspecialchars($__mod->icon) ?>"></i>
                        <span><?= htmlspecialchars($__mod->title) ?></span>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>

    <div class="page-body-wrapper">
<?php } ?>

<script>
$(document).ready(function() {
    // 1. Sidebar Toggle
    $('.toggle-sidebar').click(function() {
        $('body').toggleClass('sidebar-collapsed');
    });

    // 2. Submenu Toggle
    $('.toggle-submenu').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $parentLi = $(this).parent();
        var $submenu = $(this).next('.sidebar-submenu');
        $submenu.stop(true, true).slideToggle(300);
        $parentLi.toggleClass('active');
    });

    // 3. Loader Fade Out
    setTimeout(function(){
        $('.loader-wrapper').fadeOut(500);
    }, 600);

    // 4. CORRECT ACTIVE LINK LOGIC
    var controller = '<?= $controller ?>'; // e.g., 'tax', 'product'
    var currentPath = window.location.pathname;

    // Reset all actives first
    $('.sidebar-menu li').removeClass('active');

    // Case 1: Direct Match ID (e.g., id="nev_tax")
    var $directLink = $('#nev_' + controller);
    if ($directLink.length > 0) {
        $directLink.addClass('active');
        
        // If it's a submenu item, expand parent
        var $parentSubmenu = $directLink.closest('.sidebar-submenu');
        if ($parentSubmenu.length > 0) {
            $parentSubmenu.slideDown(0); // Open instantly
            $parentSubmenu.parent('li').addClass('active'); // Highlight parent
        }
    }
    
    // Case 2: Specific Overrides (if controller name doesn't match ID)
    // E.g., Driver Shipping might be under 'driver' controller but needs separate ID
    if (currentPath.includes('/driver/shipping')) {
        $('#nev_driver_shipping').addClass('active');
        $('#nev_parent_driver').removeClass('active'); // Remove highlighting from main driver if separate
    }
});
</script>