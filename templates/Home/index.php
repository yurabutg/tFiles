<div class="row">
    <?php if (!empty($result)) : ?>
        <div class="column">
            <div class="message">
                <small><?= $text_record_expiration; ?></small>
                <b><?= date('d/m/Y H:i', $result['expiration_time']); ?></b>
                <hr class="margin-5">
                <?php if (!is_null($result['file_preview'])) : ?>
                    <a href="<?= $app_root; ?>download/<?= $result['token']; ?>"
                       target="_blank">
                        <img src="<?= $result['file_preview']; ?>" alt="Preview">
                    </a>
                <?php elseif (!is_null($result['file_name'])) : ?>
                    <?= $text_file_name . ': '; ?>
                    <a href="<?= $app_root; ?>download/<?= $result['token']; ?>"
                       target="_blank"><b><?= $result['file_name']; ?></b></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="column">
            <?php if (!empty($result['text'])) : ?>
                <?php $text_delete = $text_delete_record; ?>
                <div class="message success text-center">
                    <?php if (filter_var($result['text'], FILTER_VALIDATE_URL)) : ?>
                        <h5 class="margin-5"><a href="<?= $result['text']; ?>" target="_blank">URL</a></h5>
                    <?php else: ?>
                        <h5 class="margin-5"><?= $result['text']; ?></h5>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php $text_delete = $text_delete_file; ?>
            <?php endif; ?>
            <div class="row">
                <div class="col-xs-12 col-sm-<?= (empty($result['text'])) ? '6' : '12'; ?>">
                    <?php
                    echo $this->Form->create(NULL, [
                        'url' => [
                            'controller' => 'Home',
                            'action' => 'delete'
                        ]
                    ]);
                    echo $this->Form->input('token', ['type' => 'hidden', 'value' => $result['token']]);
                    echo $this->Form->button($text_delete, ['class' => 'float-left width-100']);
                    echo $this->Form->end(); ?>
                </div>
                <?php if (empty($result['text'])) : ?>
                    <div class="col-xs-12 col-sm-6">
                        <a href="<?= $app_root; ?>download/<?= $result['token']; ?>" target="_blank"
                           class="button button-outline width-100 float-right"><?= $text_download_file; ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>

        <div class="column">
            <?= $this->Form->create(null, ['id' => 'validate_form']); ?>
            <?= $this->Form->control('pin', [
                'label' => false,
                'placeholder' => $text_pin,
                'type' => 'number',
                'autocomplete' => 'off',
                'required' => true
            ]); ?>
            <?= $this->Form->input('g-recaptcha-response', ['type' => 'hidden']); ?>
            <?= $this->Form->button($text_validate, [
                'class' => 'g-recaptcha float-right',
                'data-sitekey' => $reCaptcha_site_key,
                'data-callback' => 'onSubmit',
                'data-action' => 'submit'
            ]); ?>
            <?= $this->Form->end(); ?>
        </div>

    <?php endif; ?>
</div>
