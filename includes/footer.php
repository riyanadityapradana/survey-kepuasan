    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($extraScripts)) : ?>
        <?php foreach ($extraScripts as $script) : ?>
            <script src="<?= e($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($inlineScripts)) : ?>
        <?php foreach ($inlineScripts as $script) : ?>
            <script><?= $script; ?></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
