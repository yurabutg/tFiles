<div class="row">
    <div class="column">
        <?= $this->Form->create(null, ['id' => 'message_form']); ?>
        <?= $this->Form->control('telegram_id', ['type' => 'number', 'required' => true, 'placeholder' => $text_enter_telegram_id, 'label' => false, 'value' => (!is_null($telegram_logged_user_data)) ? $telegram_logged_user_data['id'] : '']); ?>
        <?= $this->Form->control('text', ['type' => 'text', 'required' => true, 'placeholder' => $text_enter_message, 'label' => false]); ?>
        <?= $this->Form->input('g-recaptcha-response', ['type' => 'hidden']); ?>
        <?= $this->Form->button ($text_send_text, [
            'class' => 'g-recaptcha float-right',
            'data-sitekey' => $reCaptcha_site_key,
            'data-callback' => 'onSubmit',
            'data-action' => 'submit'
        ]); ?>
        <?= $this->Form->end() ?>
    </div>
</div>
