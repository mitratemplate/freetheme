<?php
/**
 * خروج از سامانه
 */
session_start();
session_destroy();
header('Location: login.php');
exit;
