<?php
// basic backup command (requires 'mysqldump' in your system path)
$filename = "backup_" . date('Y-m-d') . ".sql";
exec("mysqldump -u root test > backups/$filename");
echo "Backup created: $filename";
?>