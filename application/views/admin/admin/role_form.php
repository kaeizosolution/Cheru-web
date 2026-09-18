<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<style>
    .card{border:none;border-radius:16px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);}
    .card-header-custom{background:#fff;border-bottom:1px solid #e2e8f0;padding:20px 25px;border-radius:16px 16px 0 0;}
    .card-title-custom{font-family:'Poppins',sans-serif;font-weight:600;color:#1e293b;font-size:18px;margin:0;}
    .pmt-table{width:100%;border-collapse:collapse;font-size:13px;}
    .pmt-table th,.pmt-table td{padding:10px 16px;vertical-align:middle;}
    .pmt-table thead th{background:#f8fafc;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.6px;text-align:center;white-space:nowrap;border-bottom:2px solid #e2e8f0;}
    .pmt-table thead th:first-child{text-align:left;color:#1e293b;font-size:12px;}
    .pmt-table thead th .pmt-icon{display:block;margin-bottom:3px;color:#6366f1;font-size:14px;}
    .pmt-table tr.pmt-grp td{background:#eff1ff;color:#6366f1;font-weight:800;font-size:11.5px;text-transform:uppercase;letter-spacing:.7px;border-top:2px solid #ddd6fe;}
    .pmt-grp-actions{float:right;font-size:12px;font-weight:600;}
    .pmt-grp-actions a{text-decoration:none;margin-left:12px;}
    .pmt-grp-actions .pmt-sa{color:#6366f1;}
    .pmt-grp-actions .pmt-cl{color:#94a3b8;}
    .pmt-table tr.pmt-row td{border-bottom:1px solid #f1f5f9;transition:background .12s;}
    .pmt-table tr.pmt-row:hover td{background:#fafbff;}
    .pmt-table td:first-child{font-weight:500;color:#334155;text-align:left;}
    .pmt-table td.pmt-cell{text-align:center;}
    .pmt-cb{display:none;}
    .pmt-lbl{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border:2px solid #cbd5e1;border-radius:5px;cursor:pointer;transition:all .18s;background:#fff;}
    .pmt-lbl:hover{border-color:#6366f1;background:#f0f0ff;}
    .pmt-cb:checked+.pmt-lbl{background:#6366f1;border-color:#6366f1;}
    .pmt-cb:checked+.pmt-lbl::after{content:'';display:block;width:5px;height:10px;border:2px solid #fff;border-top:none;border-left:none;transform:rotate(45deg) translateY(-1px);}
    .sub-tab-nav{padding:0 25px;border-bottom:2px solid #e2e8f0;background:#fff;}
    .sub-tab-nav .nav{gap:4px;margin:0;padding:0;}
    .sub-tab-nav .nav-link{font-size:13px;color:#64748b;padding:12px 18px;border-radius:0;border-bottom:3px solid transparent;}
    .sub-tab-nav .nav-link.active{font-weight:600;color:#6366f1;border-bottom:3px solid #6366f1;}
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3 style="font-family:'Poppins',sans-serif;font-weight:700;color:#1e293b;">
                        <?= $role_id ? 'Edit Role' : 'Create Role' ?>
                    </h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row">
                <div class="col-lg-12">
                    <?php if (($this->session->flashdata('error')) || validation_errors() != '') { ?>
                    <div class="alert alert-danger m-b-20">
                        <?= validation_errors(); ?>
                        <?= $this->session->flashdata('error') ?>
                        <?php $this->session->unset_userdata('error'); ?>
                    </div>
                    <?php } ?>

                    <div class="card">
                        <!-- Sub-tabs -->
                        <div class="sub-tab-nav">
                            <ul class="nav">
                                <li class="nav-item"><a href="/<?= $TYPE ?>/admin" class="nav-link"><i class="fa fa-users mr-1"></i> Manage Admins</a></li>
                                <li class="nav-item"><a href="/<?= $TYPE ?>/admin/roles" class="nav-link active"><i class="fa fa-shield-alt mr-1"></i> Manage Roles</a></li>
                            </ul>
                        </div>

                        <div class="card-header-custom">
                            <h5 class="card-title-custom">
                                <i class="fa fa-shield-alt mr-2" style="color:#6366f1;"></i>
                                <?= $role_id ? 'Edit Role Details & Permissions' : 'New Role Details & Permissions' ?>
                            </h5>
                        </div>

                        <form method="POST" action='<?php echo $role_id ? "/$TYPE/admin/role_edit/$role_id" : "/$TYPE/admin/role_add"; ?>' autocomplete="off">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" style="font-weight:600;color:#374151;">Role Name <span class="text-danger">*</span></label>
                                            <input type="text" name="role_name" class="form-control" value="<?= isset($role_name) ? htmlspecialchars($role_name) : '' ?>" placeholder="e.g. Sales Manager, Support Specialist" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label" style="font-weight:600;color:#374151;">Status</label>
                                            <select class="form-control" name="role_status" required>
                                                <option value="1" <?= (isset($role_status) && $role_status == 1) ? 'selected' : '' ?>>Active</option>
                                                <option value="0" <?= (isset($role_status) && $role_status == 0) ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Permission Matrix -->
                                <?php if (isset($all_modules) && count($all_modules) > 0): ?>
                                <?php
                                $__parent_ids = array();
                                foreach($all_modules as $__m) {
                                    if (!empty($__m->parent_id)) {
                                        $__parent_ids[(int)$__m->parent_id] = true;
                                    }
                                }
                                $__gmap=['vendor'=>'Marketplace','product_list'=>'Marketplace','categories_prod'=>'Marketplace','brand'=>'Marketplace','attributes'=>'Marketplace','tax'=>'Marketplace','order'=>'Orders','returns'=>'Orders','reviews'=>'Orders','coupon'=>'Marketing','advertisement'=>'Marketing','deal_of_the_day'=>'Marketing','subscription'=>'Marketing','banner'=>'CMS','homepage'=>'CMS','homepage_widget_settings'=>'CMS','page'=>'CMS','customer'=>'Customer','enquiries'=>'Customer','product_enquiries'=>'Customer','dashboard'=>'Administration','admin'=>'Administration'];
                                $__grps=['Marketplace'=>[],'Orders'=>[],'Marketing'=>[],'CMS'=>[],'Customer'=>[],'Administration'=>[],'Others'=>[]];
                                foreach($all_modules as $__m){
                                    if (isset($__parent_ids[(int)$__m->module_id])) {
                                        continue;
                                    }
                                    $__grps[isset($__gmap[strtolower($__m->module_key??'')])?$__gmap[strtolower($__m->module_key??'')]:'Others'][]=$__m;
                                }
                                $__ptypes=$all_perm_types; $__pcols=count($__ptypes);
                                ?>
                                <div class="card mt-4" style="box-shadow:none;border:1px solid #e2e8f0;">
                                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                                        <div>
                                            <h6 style="margin:0;font-weight:700;color:#1e293b;font-size:14px;"><i class="fa fa-th-list mr-2" style="color:#6366f1;"></i>Permission Matrix</h6>
                                            <small class="text-muted">Set accessibility bounds for this role. These apply to all members assigned to this role.</small>
                                        </div>
                                        <div>
                                            <button type="button" id="global-select-all" class="btn btn-sm btn-primary" style="padding:4px 12px;font-size:12px;margin-right:5px;"><i class="fa fa-check-double mr-1"></i>Select All</button>
                                            <button type="button" id="global-clear-all" class="btn btn-sm btn-light" style="padding:4px 12px;font-size:12px;border:1px solid #cbd5e1;color:#475569;"><i class="fa fa-times mr-1"></i>Clear All</button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="pmt-table">
                                                <thead>
                                                    <tr>
                                                        <th>MODULE</th>
                                                        <?php foreach($__ptypes as $__pt):?><th><span class="pmt-icon"><i class="fa fa-shield-alt"></i></span><?=htmlspecialchars(strtoupper($__pt->name))?></th><?php endforeach;?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($__grps as $__gn=>$__gm): if(empty($__gm)) continue;?>
                                                    <tr class="pmt-grp" data-group="<?=htmlspecialchars($__gn)?>">
                                                        <td colspan="<?=$__pcols+1?>"><?=htmlspecialchars($__gn)?>
                                                            <span class="pmt-grp-actions">
                                                                <a href="javascript:void(0);" class="pmt-group-sa pmt-sa"><i class="fa fa-check-square mr-1"></i>Select All</a>
                                                                <a href="javascript:void(0);" class="pmt-group-cl pmt-cl"><i class="fa fa-square mr-1"></i>Clear</a>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php foreach($__gm as $__mod): 
                                                        $__has = isset($assigned_permissions[$__mod->module_id]);
                                                    ?>
                                                    <tr class="pmt-row" data-module-id="<?=$__mod->module_id?>" data-parent-id="<?=(int)($__mod->parent_id??0)?>">
                                                        <td><?=htmlspecialchars($__mod->title)?>
                                                            <input type="checkbox" class="d-none mod-check" name="modules[]" id="mod_<?=$__mod->module_id?>" value="<?=$__mod->module_id?>" data-parent-id="<?=(int)($__mod->parent_id??0)?>" <?=$__has?'checked':''?>>
                                                        </td>
                                                        <?php foreach($__ptypes as $__pt): 
                                                            $__cid='pmt_'.$__mod->module_id.'_'.$__pt->perm_type_id; 
                                                            $__chk=$__has && isset($assigned_permissions[$__mod->module_id][$__pt->perm_type_id]);?>
                                                        <td class="pmt-cell">
                                                            <input type="checkbox" class="pmt-cb" id="<?=$__cid?>" name="perm_type[<?=$__mod->module_id?>][]" value="<?=$__pt->perm_type_id?>" data-module-id="<?=$__mod->module_id?>" data-perm-type-id="<?=$__pt->perm_type_id?>" <?=$__chk?'checked':''?>>
                                                            <label for="<?=$__cid?>" class="pmt-lbl"></label>
                                                        </td>
                                                        <?php endforeach;?>
                                                    </tr>
                                                    <?php endforeach;?>
                                                <?php endforeach;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer text-right" style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                                <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                                <a href="/<?= $TYPE ?>/admin/roles" class="btn btn-light" style="border-radius:8px;padding:8px 20px;">Cancel</a>
                                <button type="submit" class="btn btn-primary" style="background:#6366f1;border:none;border-radius:8px;padding:8px 25px;font-weight:600;">Save Role</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Checkbox click: independent per-column, no cascade between columns
$(document).on('change', '.pmt-cb', function() {
    var mid  = $(this).data('module-id');
    var $row = $(this).closest('tr.pmt-row');

    var anyChecked = $row.find('.pmt-cb:checked').length > 0;
    $('#mod_' + mid).prop('checked', anyChecked);
});

// Group Select All (checks all permission types for all group modules)
$(document).on('click', '.pmt-group-sa', function(e) {
    e.preventDefault();
    var $grpRow = $(this).closest('tr.pmt-grp');
    var $rows   = $grpRow.nextUntil('tr.pmt-grp', 'tr.pmt-row');
    $rows.each(function() {
        var $row = $(this);
        var mid  = $row.data('module-id');
        $row.find('.pmt-cb').prop('checked', true);
        $('#mod_' + mid).prop('checked', true);
    });
});

// Group Clear
$(document).on('click', '.pmt-group-cl', function(e) {
    e.preventDefault();
    var $grpRow = $(this).closest('tr.pmt-grp');
    var $rows   = $grpRow.nextUntil('tr.pmt-grp', 'tr.pmt-row');
    $rows.each(function() {
        var $row = $(this);
        var mid  = $row.data('module-id');
        $row.find('.pmt-cb').prop('checked', false);
        $('#mod_' + mid).prop('checked', false);
    });
});

// Global Select All (checks all permission types for all modules)
$(document).on('click', '#global-select-all', function(e) {
    e.preventDefault();
    $('tr.pmt-row').each(function() {
        var $row = $(this);
        var mid  = $row.data('module-id');
        $row.find('.pmt-cb').prop('checked', true);
        $('#mod_' + mid).prop('checked', true);
    });
});

// Global Clear All
$(document).on('click', '#global-clear-all', function(e) {
    e.preventDefault();
    $('tr.pmt-row').each(function() {
        var $row = $(this);
        var mid  = $row.data('module-id');
        $row.find('.pmt-cb').prop('checked', false);
        $('#mod_' + mid).prop('checked', false);
    });
});
</script>
