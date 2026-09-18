<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<style>
    .card{border:none;border-radius:16px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);}
    .card-header-custom{display:flex;justify-content:space-between;align-items:center;padding:20px 25px;background:#fff;border-bottom:1px solid #e2e8f0;border-radius:16px 16px 0 0;}
    .card-title-custom{font-family:'Poppins',sans-serif;font-weight:600;color:#1e293b;font-size:18px;margin:0;}
    .btn-add-role{background:#6366f1;color:#fff !important;border-radius:8px;padding:8px 20px;font-weight:500;border:none;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;text-decoration:none;cursor:pointer;}
    .btn-add-role:hover{background:#4f46e5;transform:translateY(-2px);}
    #rolesTable thead th{background:#f8fafc;color:#64748b;font-weight:600;text-transform:uppercase;font-size:12px;padding:15px 20px;border-bottom:1px solid #e2e8f0;}
    #rolesTable tbody td{padding:15px 20px;vertical-align:middle;color:#334155;border-bottom:1px solid #e2e8f0;font-size:14px;}
    .badge-active{background:rgba(16,185,129,0.1);color:#10b981;padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;text-transform:uppercase;}
    .badge-inactive{background:rgba(239,68,68,0.1);color:#ef4444;padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;text-transform:uppercase;}
    .btn-r{border:none;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;text-decoration:none;}
    .btn-r-edit{background:#eff6ff;color:#3b82f6;} .btn-r-edit:hover{background:#dbeafe;color:#1d4ed8;}
    .btn-r-del{background:#fef2f2;color:#ef4444;} .btn-r-del:hover{background:#fee2e2;color:#b91c1c;}
    .sub-tab-nav{padding:0 25px;border-bottom:2px solid #e2e8f0;background:#fff;}
    .sub-tab-nav .nav{gap:4px;margin:0;padding:0;}
    .sub-tab-nav .nav-link{font-size:13px;color:#64748b;padding:12px 18px;border-radius:0;border-bottom:3px solid transparent;}
    .sub-tab-nav .nav-link.active{font-weight:600;color:#6366f1;border-bottom:3px solid #6366f1;}
</style>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6"><h3 style="font-family:'Poppins',sans-serif;font-weight:700;color:#1e293b;">Role Management</h3></div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger m-3 rounded"><?= $this->session->flashdata('error') ?><?php $this->session->unset_userdata('error'); ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success m-3 rounded"><?= $this->session->flashdata('success') ?><?php $this->session->unset_userdata('success'); ?></div>
                    <?php endif; ?>

                    <!-- Sub-tabs -->
                    <div class="sub-tab-nav">
                        <ul class="nav">
                            <li class="nav-item"><a href="/<?= $TYPE ?>/admin" class="nav-link"><i class="fa fa-users mr-1"></i> Manage Admins</a></li>
                            <li class="nav-item"><a href="/<?= $TYPE ?>/admin/roles" class="nav-link active"><i class="fa fa-shield-alt mr-1"></i> Manage Roles</a></li>
                        </ul>
                    </div>

                    <div class="card-header-custom">
                        <h5 class="card-title-custom"><i class="fa fa-shield-alt mr-2" style="color:#6366f1;"></i>Roles List</h5>
                        <a href="/<?= $TYPE ?>/admin/role_add" class="btn-add-role"><i class="fa fa-plus"></i> Add New Role</a>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="rolesTable" class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Role Name</th>
                                        <th style="text-align:center;">Admins Assigned</th>
                                        <th style="text-align:center;">Status</th>
                                        <th>Date Added</th>
                                        <th style="text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($roles)): $i = 1; foreach ($roles as $role): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><strong style="color:#1e293b;"><?= htmlspecialchars($role->name) ?></strong></td>
                                        <td style="text-align:center;">
                                            <span style="background:#eff6ff;color:#3b82f6;padding:4px 12px;border-radius:20px;font-weight:600;font-size:13px;"><?= (int)$role->admin_count ?></span>
                                        </td>
                                        <td style="text-align:center;">
                                            <?= $role->status == 1 ? '<span class="badge-active">Active</span>' : '<span class="badge-inactive">Inactive</span>' ?>
                                        </td>
                                        <td><?= $role->date_added ? date('d M Y', strtotime($role->date_added)) : '—' ?></td>
                                        <td style="text-align:center;">
                                            <a href="/<?= $TYPE ?>/admin/role_edit/<?= (int)$role->role_id ?>" class="btn-r btn-r-edit"><i class="fa fa-pencil"></i> Edit Permissions</a>
                                            <?php if ((int)$role->admin_count === 0): ?>
                                            <a href="/<?= $TYPE ?>/admin/role_delete/<?= (int)$role->role_id ?>" class="btn-r btn-r-del ml-1" onclick="return confirm('Delete this role permanently?')"><i class="fa fa-trash"></i> Delete</a>
                                            <?php else: ?>
                                            <span class="btn-r ml-1" style="background:#f1f5f9;color:#94a3b8;cursor:not-allowed;" title="Reassign admins before deleting"><i class="fa fa-lock"></i> In Use</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:50px;color:#94a3b8;">
                                            <i class="fa fa-shield-alt" style="font-size:40px;margin-bottom:12px;display:block;color:#cbd5e1;"></i>
                                            No roles found. <a href="/<?= $TYPE ?>/admin/role_add" style="color:#6366f1;font-weight:600;">Create your first role</a>.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
