<div class="modal" id="<?= $modalId ?>">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= htmlspecialchars($title) ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <?= $content ?>
        </div>
        <div class="modal-footer">
            <?php if (isset($footer)): ?>
                <?= $footer ?>
            <?php else: ?>
                <button class="btn btn-secondary modal-cancel">Cancel</button>
                <button class="btn btn-primary modal-confirm">Confirm</button>
            <?php endif; ?>
        </div>
    </div>
</div>