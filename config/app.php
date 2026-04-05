<?php

define('APP_NAME', env('APP_NAME', 'Survei Kepuasan RSPI'));
define('BASE_URL', env('BASE_URL', '/survey-kepuasan'));
define('APP_TIMEZONE', env('APP_TIMEZONE', 'Asia/Makassar'));
define('TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', ''));
define('TELEGRAM_CHAT_ID', env('TELEGRAM_CHAT_ID', '@form_survey_rspi'));
define('TELEGRAM_NOTIF_ENABLED', TELEGRAM_BOT_TOKEN !== '' && TELEGRAM_CHAT_ID !== '');
