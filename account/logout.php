<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
unset($_SESSION['uid']);
session_regenerate_id(true);
flash('success', 'You have been signed out. See you next time.');
redirect('account/login.php');
