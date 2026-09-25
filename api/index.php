<?php

// Suppress PHP deprecation notices in serverless environment
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

// Forward Vercel requests to normal index.php
require __DIR__ . '/../public/index.php';
