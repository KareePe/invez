<?php
if (session_status() === PHP_SESSION_NONE) session_start();
unset($_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_username']);
header('Location: ./');
exit;
