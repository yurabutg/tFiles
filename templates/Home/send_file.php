<div class="row">
    <div class="column">
        <?= $this->Form->create(null, ['id' => 'upload_form', 'type' => 'file']); ?>
        <?= $this->Form->control('telegram_id', ['type' => 'number', 'required' => true, 'placeholder' => $text_enter_telegram_id, 'label' => false,
                'value' => (!is_null($telegram_logged_user_data)) ? $telegram_logged_user_data['id'] : ''
        ]); ?>
        <?= $this->Form->control('file', ['type' => 'file', 'required' => true, 'label' => false]); ?>
        <?= $this->Form->input('g-recaptcha-response', ['type' => 'hidden']); ?>
        <h6 class="text-danger"><?= vsprintf($text_max_file_size, [$upload_max_file_size]); ?></h6>
        <?= $this->Form->button($text_send_file, [
            'class' => 'g-recaptcha float-right',
            'data-sitekey' => $reCaptcha_site_key,
            'data-callback' => 'onSubmit',
            'data-action' => 'submit'
        ]); ?>
        <?= $this->Form->end() ?>
        <div class="meter hidden">
            <span style="width:100%;"><span class="progress"></span></span>
        </div>
    </div>
</div>
