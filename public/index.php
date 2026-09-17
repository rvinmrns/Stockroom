<?php
declare(strict_types=1);
// Keep the site root and existing bookmarks working after the controller rename.
header('Location: stockroom.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''), true, 307);
exit;
