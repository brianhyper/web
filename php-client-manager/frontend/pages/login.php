<?php $currentPage = 'login'; ?>
<div class="auth-layout">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-brand">
                <a href="/">
                    <i class="fas fa-rocket"></i>
                    <span>ClientManager</span>
                </a>
            </div>
            
            <h1 class="auth-title">Sign in to your account</h1>
            
            <form action="/login" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrf() ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="you@example.com" required>
                </div>
                
                <div class="form-group">
                    <div class="form-label-group">
                        <label for="password" class="form-label">Password</label>
                        <a href="/forgot-password" class="link">Forgot password?</a>
                    </div>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="••••••••" required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign in</button>
                
                <div class="auth-divider">
                    <span>Or continue with</span>
                </div>
                
                <div class="social-auth">
                    <button type="button" class="btn btn-social btn-google">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button type="button" class="btn btn-social btn-microsoft">
                        <i class="fab fa-microsoft"></i> Microsoft
                    </button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="/register" class="link">Sign up</a></p>
            </div>
        </div>
        
        <div class="auth-image">
            <div class="image-overlay">
                <h2>Streamline your client management</h2>
                <p>All your clients, projects, and invoices in one place</p>
            </div>
        </div>
    </div>
</div>