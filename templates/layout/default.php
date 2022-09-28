<?php $cakeDescription = 'tFiles'; ?>
<!DOCTYPE html>
<html lang="EN">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?= $cakeDescription ?> </title>
    <?= $this->Html->meta('icon') ?>

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.1/normalize.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">


    <?= $this->element('css'); ?>
    <?= $this->fetch('meta') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://www.google.com/recaptcha/api.js"></script>
</head>
<body class="background-body">
<div class="row">
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="/"><small class="text-whitesmoke">t</small>Files</a>
        </div>
        <?php if (!is_null($telegram_logged_user_data)): ?>
            <span class="badge bg-light text-dark">
                <?= $telegram_logged_user_data['first_name'] . ' ' . $telegram_logged_user_data['last_name']; ?>
            </span>
        <?php endif; ?>
        <div class="top-nav-links">
            <?php if (is_null($telegram_logged_user_data)): ?>
                <a class="text-whitesmoke" href="https://t.me/tmp_files_bot"
                   target="_blank"><?= $text_get_telegram_id; ?></a>
            <?php else: ?>
                <button class="text-whitesmoke" onclick="deleteTelegramAuthUser();"><?= $text_logout; ?></button>
            <?php endif; ?>
        </div>
    </nav>
</div>
<main class="main">
    <div class="container">
        <?= $this->element('top_buttons'); ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="content">
                    <?= $this->Flash->render() ?>
                    <br>
                    <?= $this->fetch('content') ?>
                </div>
            </div>
        </div>
        <?php if (is_null($telegram_logged_user_data)): ?>
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                    <div class="content float-right">
                        <script async src="https://telegram.org/js/telegram-widget.js?15"
                                data-telegram-login="tmp_files_bot"
                                data-size="medium" data-radius="4" data-onauth="onTelegramAuth(user)"
                                data-request-access="write"></script>
                        <script type="text/javascript">
                            function onTelegramAuth(user) {
                                setTelegramUser(user);
                            }
                        </script>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<hr>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="content">
                    <?= 'Utenti:' . $statistics['users_count']; ?>
                    <?= 'Messagi:' . $statistics['messages_count']; ?>
                    <?= 'Files:' . $statistics['files_count']; ?>
                    <?= 'Files size:' . number_format($statistics['files_total_size'] / 1048576, 1) . 'Mb'; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="content">
                    <a class="text-whitesmoke" href="mailto:info@tfiles.eu">info@tfiles.eu</a></div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
            crossorigin="anonymous">
    </script>

    <?= $this->element('js'); ?>
</footer>
</body>
</html>
