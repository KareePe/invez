<?php
require_once(__DIR__ . '/config/session.php');
unset($_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_username']);
header('Location: ./');
exit;
