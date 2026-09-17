# Inventory database

The app now uses MySQL database `inventory_hq`. Files in this directory are retained SQLite backups from before migration and are no longer updated. See [MySQL instructions](../database/README.md).

- `users`: username, password hash, role, and active status.
- `products`: product name, SKU, category, quantity, minimum stock, price, and update time.

New signups and inventory changes are saved in MySQL. View the live tables by expanding `inventory_hq` in phpMyAdmin.

These backups stay outside the public web directory. Do not commit them to source control. Use phpMyAdmin Export for current backups.
