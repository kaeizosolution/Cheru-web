<?php
$sess   = $this->session->userdata($TYPE);
$obj    = $admin_obj;
$fname  = $obj ? $obj->fname  : '';
$lname  = $obj ? $obj->lname  : '';
$email  = $obj ? $obj->email  : '';
$mobile = $obj ? $obj->mobile : '';
// Read from admin_img column (renamed from vendor_img/logo)
$logo   = $obj && !empty($obj->admin_img) ? $obj->admin_img : (($obj && !empty($obj->logo)) ? $obj->logo : '');
$is_super = !empty($sess['super_admin']) && $sess['super_admin'] == 1;
$avatar_url = $logo ? base_url('assets/images/' . $logo) : base_url('assets/vendor/images/admin-avatar.png');
?>
<style>
.profile-card { background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(99,102,241,.08); overflow:hidden; }
.profile-banner { background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%); height:120px; position:relative; }
.profile-avatar-wrap { position:absolute; bottom:-45px; left:32px; }
.profile-avatar { width:90px; height:90px; border-radius:50%; border:4px solid #fff; object-fit:cover; box-shadow:0 4px 16px rgba(99,102,241,.25); background:#e0e0e0; }
.profile-avatar-initials { width:90px; height:90px; border-radius:50%; border:4px solid #fff; box-shadow:0 4px 16px rgba(99,102,241,.25); background:linear-gradient(135deg,#818cf8,#6366f1); display:flex;align-items:center;justify-content:center; font-size:30px;font-weight:700;color:#fff; }
.profile-info-header { padding: 56px 32px 20px; border-bottom:1px solid #f1f5f9; }
.profile-info-header h5 { margin:0; font-weight:700; color:#1e293b; font-size:18px; }
.profile-info-header small { color:#64748b; font-size:13px; }
.badge-super { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; font-size:11px; padding:3px 10px; border-radius:20px; font-weight:600; margin-left:8px; }
.badge-sub { background:#e0e7ff; color:#6366f1; font-size:11px; padding:3px 10px; border-radius:20px; font-weight:600; margin-left:8px; }
.profile-form-section { padding:28px 32px 32px; }
.section-title { font-size:13px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:.7px; margin-bottom:18px; display:flex; align-items:center; gap:8px; }
.section-title::after { content:''; flex:1; height:1px; background:#e2e8f0; }
.form-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:5px; }
.form-control { border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; padding:9px 13px; transition:border .18s; }
.form-control:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
.avatar-upload-wrap { display:flex; align-items:center; gap:16px; margin-bottom:10px; }
.avatar-preview { width:64px; height:64px; border-radius:50%; object-fit:cover; border:3px solid #e0e7ff; }
.avatar-preview-initials { width:64px; height:64px; border-radius:50%; border:3px solid #e0e7ff; background:linear-gradient(135deg,#818cf8,#6366f1); display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700; color:#fff; }
.btn-save-profile { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; border-radius:9px; padding:10px 28px; font-weight:600; font-size:14px; transition:all .2s; }
.btn-save-profile:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(99,102,241,.4); color:#fff; }
.change-pass-card { background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(99,102,241,.08); margin-top:24px; }
.change-pass-card .card-header { background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:16px 24px; border-radius:16px 16px 0 0; }
.change-pass-card .card-header h6 { margin:0; font-weight:700; color:#1e293b; font-size:14px; }
.change-pass-card .card-body { padding:24px; }
</style>

<div class="page-body">
<div class="container-fluid">
    <div class="page-header">
        <div class="row">
            <div class="col-lg-6"><h3>My Profile</h3></div>
            <?= $breadcrumbs ?>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php if ($this->session->flashdata('profile_error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-circle mr-2"></i><?= $this->session->flashdata('profile_error') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php $this->session->unset_userdata('profile_error'); ?>
    <?php endif; ?>
    <?php if ($this->session->flashdata('profile_success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle mr-2"></i><?= $this->session->flashdata('profile_success') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php $this->session->unset_userdata('profile_success'); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Left column: profile card -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="profile-card">
                <div class="profile-banner">
                    <div class="profile-avatar-wrap">
                        <?php if ($logo): ?>
                            <img src="<?= $avatar_url ?>" class="profile-avatar" alt="Avatar" id="headerAvatarBig">
                        <?php else: ?>
                            <div class="profile-avatar-initials" id="headerAvatarBig"><?= strtoupper(substr($fname,0,1)) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="profile-info-header">
                    <h5>
                        <?= htmlspecialchars($fname . ' ' . $lname) ?>
                        <?php if ($is_super): ?>
                            <span class="badge-super"><i class="fa fa-crown mr-1"></i>Super Admin</span>
                        <?php else: ?>
                            <span class="badge-sub"><i class="fa fa-user-shield mr-1"></i>Sub Admin</span>
                        <?php endif; ?>
                    </h5>
                    <small><?= htmlspecialchars($email) ?></small>
                    <?php if ($mobile): ?>
                    <br><small><i class="fa fa-phone mr-1"></i><?= htmlspecialchars($mobile) ?></small>
                    <?php endif; ?>
                </div>
                <div style="padding:16px 32px 24px;">
                    <?php
                    $role_names = array();
                    if (!$is_super && !empty($obj->admin_id)) {
                        $roles = $this->db->select('r.name')
                            ->from('ec_admin_to_roles ar')
                            ->join('ec_admin_roles r', 'r.role_id = ar.role_id')
                            ->where('ar.admin_id', (int)$obj->admin_id)
                            ->where('r.status', 1)
                            ->get()->result();
                        foreach ($roles as $r) {
                            $role_names[] = $r->name;
                        }
                    }
                    ?>
                    <div style="display:flex;gap:16px;">
                        <div style="flex:1;background:#f8fafc;border-radius:10px;padding:12px 14px;text-align:left;">
                            <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:600;text-align:center;">Roles</div>
                            <div style="font-size:13px;font-weight:600;color:#1e293b;margin-top:5px;line-height:1.5;">
                                <?php if ($is_super): ?>
                                    <div style="text-align:center;font-weight:700;">Super Admin</div>
                                <?php else: ?>
                                    <?php if (!empty($role_names)): ?>
                                        <ul style="margin:0;padding-left:15px;list-style-type:disc;">
                                            <?php foreach ($role_names as $rname): ?>
                                                <li><?= htmlspecialchars($rname) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div style="text-align:center;color:#94a3b8;font-weight:700;">No Roles</div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="flex:1;background:#f8fafc;border-radius:10px;padding:12px 14px;text-align:center;">
                            <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:600;">Status</div>
                            <div style="font-size:14px;font-weight:700;color:#10b981;margin-top:3px;"><i class="fa fa-circle" style="font-size:8px;"></i> Active</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column: edit form -->
        <div class="col-xl-8 col-lg-7">
            <div class="profile-card">
                <div class="profile-form-section">
                    <form action="<?= base_url($TYPE . '/admin/profile_save') ?>" method="POST" enctype="multipart/form-data" id="profileForm">
                        <input type="hidden" name="<?= $csrf->name ?>" value="<?= $csrf->hash ?>">

                        <div class="section-title"><i class="fa fa-user mr-1"></i> Personal Information</div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">First Name <span style="color:#ef4444;">*</span></label>
                                    <input type="text" name="fname" class="form-control" value="<?= htmlspecialchars($fname) ?>" placeholder="First name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="lname" class="form-control" value="<?= htmlspecialchars($lname) ?>" placeholder="Last name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email Address <span style="color:#ef4444;">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($mobile) ?>" placeholder="Mobile" maxlength="10">
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-3"><i class="fa fa-image mr-1"></i> Profile Picture</div>
                        <div class="avatar-upload-wrap">
                            <?php if ($logo): ?>
                                <img src="<?= $avatar_url ?>" class="avatar-preview" id="avatarPreview" alt="Profile Photo">
                            <?php else: ?>
                                <div class="avatar-preview-initials" id="avatarInitials"><?= strtoupper(substr($fname,0,1)) ?></div>
                            <?php endif; ?>
                            <div>
                                <input type="file" name="logo" id="logoInput" class="form-control" accept="image/*" style="max-width:280px;">
                                <small class="text-muted d-block mt-1">JPG, PNG, GIF — max 2MB</small>
                            </div>
                        </div>

                        <div class="mt-4 d-flex align-items-center gap-3">
                            <button type="submit" class="btn-save-profile">
                                <i class="fa fa-save mr-2"></i>Save Changes
                            </button>
                            <a href="<?= base_url($TYPE . '/dashboard') ?>" class="btn btn-light ml-3" style="border-radius:9px;font-weight:600;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password card -->
            <div class="change-pass-card">
                <div class="card-header">
                    <h6><i class="fa fa-lock mr-2" style="color:#6366f1;"></i>Change Password</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url($TYPE . '/admin/update_password/' . ($obj ? $obj->admin_id : '')) ?>" method="POST">
                        <input type="hidden" name="<?= $csrf->name ?>" value="<?= $csrf->hash ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new1" id="pf_pass1" class="form-control" placeholder="New password" required minlength="6">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="new2" id="pf_pass2" class="form-control" placeholder="Confirm password" required minlength="6">
                                    <span id="pf_pass_err" style="color:#ef4444;font-size:12px;"></span>
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="pf_save_pass" class="btn-save-profile">
                            <i class="fa fa-key mr-2"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>

        </div><!-- /col -->
    </div><!-- /row -->
</div><!-- /container -->
</div><!-- /page-body -->

<script>
// Live avatar preview on file select
document.getElementById('logoInput').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var existing = document.getElementById('avatarPreview');
        var initials = document.getElementById('avatarInitials');
        if (existing) {
            existing.src = e.target.result;
        } else {
            // Replace initials div with img
            var img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'avatar-preview';
            img.id = 'avatarPreview';
            if (initials) initials.parentNode.replaceChild(img, initials);
        }
    };
    reader.readAsDataURL(file);
});

// Password match validation
document.getElementById('pf_save_pass').addEventListener('click', function(e) {
    var p1 = document.getElementById('pf_pass1').value;
    var p2 = document.getElementById('pf_pass2').value;
    if (p1 !== p2) {
        e.preventDefault();
        document.getElementById('pf_pass_err').textContent = 'Passwords do not match.';
    } else {
        document.getElementById('pf_pass_err').textContent = '';
    }
});
</script>
