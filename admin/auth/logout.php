<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
session_destroy();
redirect(BASE_URL . 'admin/auth/login.php');
