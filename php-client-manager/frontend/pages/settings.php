<?php $currentPage = 'settings'; ?>
<div class="settings">
    <div class="page-header">
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Manage your account preferences</p>
    </div>

    <div class="settings-layout">
        <div class="settings-sidebar">
            <ul class="settings-menu">
                <li class="active"><a href="#profile"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="#security"><i class="fas fa-shield-alt"></i> Security</a></li>
                <li><a href="#notifications"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="#billing"><i class="fas fa-credit-card"></i> Billing</a></li>
                <li><a href="#preferences"><i class="fas fa-sliders-h"></i> Preferences</a></li>
                <li><a href="#integrations"><i class="fas fa-plug"></i> Integrations</a></li>
            </ul>
        </div>
        
        <div class="settings-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Profile Settings</h2>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label class="form-label">Profile Picture</label>
                            <div class="avatar-upload">
                                <div class="avatar-preview">
                                    <img src="<?= htmlspecialchars($_SESSION['user_avatar'] ?? '/assets/images/avatar.png') ?>" 
                                         alt="Profile Preview" id="avatar-preview">
                                </div>
                                <div class="avatar-actions">
                                    <label class="btn btn-outline">
                                        <i class="fas fa-upload"></i> Upload Photo
                                        <input type="file" id="avatar-upload" accept="image/*" hidden>
                                    </label>
                                    <button type="button" class="btn btn-text">Remove</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" value="John">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="Doe">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="john.doe@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" rows="3">Product designer at Acme Inc.</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Timezone</label>
                            <select class="form-control">
                                <option>(GMT-12:00) International Date Line West</option>
                                <option>(GMT-11:00) Midway Island, Samoa</option>
                                <option selected>(GMT-08:00) Pacific Time (US & Canada)</option>
                                <!-- More options -->
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="reset" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Security Settings</h2>
                </div>
                <div class="card-body">
                    <div class="security-item">
                        <div class="security-info">
                            <h3>Password</h3>
                            <p>Last changed 3 months ago</p>
                        </div>
                        <button class="btn btn-outline">Change Password</button>
                    </div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <h3>Two-Factor Authentication</h3>
                            <p>Add an extra layer of security to your account</p>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" id="two-factor-toggle">
                            <label for="two-factor-toggle"></label>
                        </div>
                    </div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <h3>Active Sessions</h3>
                            <p>Manage devices that are logged into your account</p>
                        </div>
                        <button class="btn btn-outline">View Sessions</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>