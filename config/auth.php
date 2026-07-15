<?php
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user'])) {
    set_flash('warning', 'Silakan login terlebih dahulu.');
    redirect('index.php');
}
