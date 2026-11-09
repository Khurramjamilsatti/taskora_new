<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
unset($_SESSION['admin_id']);
session_regenerate_id(true);
flash('success', 'Signed out.');
redirect('admin/login.php');
