<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <div class="alert-content">
            <i class="fas fa-exclamation-circle"></i>
            <div><?= htmlspecialchars($_SESSION['error']) ?></div>
        </div>
        <button class="close-btn">&times;</button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <div class="alert-content">
            <i class="fas fa-check-circle"></i>
            <div><?= htmlspecialchars($_SESSION['success']) ?></div>
        </div>
        <button class="close-btn">&times;</button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['info'])): ?>
    <div class="alert alert-info">
        <div class="alert-content">
            <i class="fas fa-info-circle"></i>
            <div><?= htmlspecialchars($_SESSION['info']) ?></div>
        </div>
        <button class="close-btn">&times;</button>
    </div>
    <?php unset($_SESSION['info']); ?>
<?php endif; ?>