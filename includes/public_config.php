<?php
// includes/public_config.php

// Define paths that do not enforce login
// Paths are relative to the web root or strictly matched script names
return [
    'whitelist' => [
        'login.php', 
        'index.php', 
        'release_notes.php', 
        'bg_animation.php',
        'api/public_endpoints.php', // Example
        'includes/auth_check.php',  // Helper specifically for frontend checks without redirects
        'assets/user_scripts/beemore_ticket_popup.user.js' // Scripts that might need access (though usually they hit APIs)
    ]
];
?>
