# MySQL database

The application now uses `inventory_hq` in MySQL/MariaDB. Start **MySQL** from XAMPP Control Panel, then open phpMyAdmin and expand **inventory_hq**. The `users` and `products` tables hold the application data.

Connection defaults in `app/database.php` match this local XAMPP installation:

| Setting | Default |
| --- | --- |
| DB_HOST | 127.0.0.1 |
| DB_PORT | 3306 |
| DB_NAME | inventory_hq |
| DB_USER | root |
| DB_PASSWORD | Empty |

Override these with environment variables before starting PHP if your local settings change. Password hashes were migrated without modification, so existing logins still work.

For a new installation, create the `inventory_hq` database in phpMyAdmin, select it, and import `database/schema.sql`. Then open the app to create the initial admin account.

The one-time SQLite migration is `php database/migrate.php`. It creates a backup, imports records in a transaction, verifies every field, and refuses to overwrite populated destination tables. **The current installation has already been migrated; do not rerun it.** The SQLite files under `data` are backups only; new activity is stored in MySQL.

Use phpMyAdmin's Export feature to back up the current database.
