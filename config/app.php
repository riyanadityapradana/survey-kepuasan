<?php

define('APP_NAME', env('APP_NAME', 'Survei Kepuasan RSPI'));
define('BASE_URL', env('BASE_URL', '/survey-kepuasan'));
define('APP_TIMEZONE', env('APP_TIMEZONE', 'Asia/Makassar'));
define('TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', ''));
define('TELEGRAM_CHAT_ID', env('TELEGRAM_CHAT_ID', '@form_survey_rspi'));
define('TELEGRAM_NOTIF_ENABLED', TELEGRAM_BOT_TOKEN !== '' && TELEGRAM_CHAT_ID !== '');
define('MAIL_HOST', env('MAIL_HOST', ''));
define('MAIL_PORT', (int) env('MAIL_PORT', '587'));
define('MAIL_USERNAME', env('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', ''));
define('MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', MAIL_USERNAME));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', APP_NAME));
define('MAIL_TO_ADDRESS', env('MAIL_TO_ADDRESS', ''));
define('MAIL_TO_NAME', env('MAIL_TO_NAME', 'Admin Survei'));
define('MAIL_NOTIF_ENABLED', MAIL_HOST !== '' && MAIL_USERNAME !== '' && MAIL_PASSWORD !== '' && MAIL_TO_ADDRESS !== '');
