<?php $currentPage = 'register'; ?>
<div class="auth-layout">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-brand">
                <a href="/">
                    <i class="fas fa-rocket"></i>
                    <span>ClientManager</span>
                </a>
            </div>
            
            <h1 class="auth-title">Create your account</h1>
            
            <form action="/register" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCsrf() ?>">
                
                <div class="form-group">
                    <label for="name" class="form-label">Full name</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           placeholder="John Doe" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="you@example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="••••••••" required>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm" class="form-label">Confirm password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" 
                           placeholder="••••••••" required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox">
                        <input type="checkbox" name="terms" required> 
                        I agree to the <a href="#" class="link">Terms of Service</a> and <a href="#" class="link">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create account</button>
                
                <div class="auth-divider">
                    <span>Or sign up with</span>
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
                <p>Already have an account? <a href="/login" class="link">Sign in</a></p>
            </div>
        </div>
        
        <div class="auth-image">
            <div class="image-overlay">
                <h2>Join thousands of satisfied users</h2>
                <p>Manage your business more efficiently with our tools</p>
            </div>
        </div>
    </div>
</div>