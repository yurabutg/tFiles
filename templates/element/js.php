<?= $this->Html->script('custom.js'); ?>

<?php if ($current_action == 'sendText') $form_name = 'message_form';
elseif ($current_action == 'index') $form_name = 'validate_form';
elseif ($current_action == 'sendFile') $form_name = 'upload_form'; ?>

<?php if (isset($form_name)): ?>
    <script>
        function onSubmit(token) {
            document.getElementById("<?= $form_name ?>").submit();
        }
    </script>
<?php endif; ?>
