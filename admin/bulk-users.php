<?php
// Placeholder - Actions groupées sur utilisateurs
require_once __DIR__ . '/../backend/includes/db.php';
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/functions.php';

require_admin();
require_post();

redirect('/admin/users.php');
