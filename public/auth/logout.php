<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/services/SessionManager.php';

SessionManager::logout();

header("Location: ../../views/auth/login-form.php");
exit;