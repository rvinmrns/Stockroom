# DENR MIMAROPA — Inventory system

A PHP and MySQL/MariaDB inventory app with Admin and User roles, product management, stock adjustments, search, stock filters, CSV export, and employee item assignments with unique serial numbers.

## Project structure

- `views/` — page layouts and forms; shared menus and product cards are in `views/partials/`.
- `app/` — authentication, database connection, and JSON response helpers.
- `public/` — request controllers (`stockroom.php`, `users.php`, `assignments.php`) and browser assets (`style.css`, `accounts.css`, `app.js`).
- `database/` — database schema and setup information.

Edit the UI in `views/`; continue opening the existing URLs under `public/`. Keep the web server's document root set to `public`, not `views`. See [UI template guide](views/README.md).


## Starting the app

On Windows, run `powershell -ExecutionPolicy Bypass -File .\start.ps1` from this folder. The script locates PHP (including common XAMPP paths) and serves the correct `public` folder. Keep that terminal open and visit **http://127.0.0.1:8000**.

If the browser shows **Not Found**, stop the existing server with Ctrl+C first, then use the startup script. Serving the project root instead of `public` causes this error.

1. Install **PHP 8.1 or newer** with **PDO MySQL** enabled (the `pdo_mysql` extension), and start **MySQL** in XAMPP. This installation already has the migrated `inventory_hq` database. For a new installation, create that database and import `database/schema.sql` using phpMyAdmin.
2. Open a terminal in this project folder.
3. Start the app:

   ```sh
   php -S localhost:8000 -t public
   ```

4. Visit **http://localhost:8000**, create your first admin account, and click **Add product**.

The app uses MySQL database `inventory_hq` on `127.0.0.1:3306`. Open phpMyAdmin and expand `inventory_hq` to see `users`, `products`, and `item_assignments`. Only the schema is included in this repository; accounts, employee records, database backups, and passwords are not included. No Composer or npm packages are needed. Google Fonts is optional; system fonts are used when offline.

If using XAMPP, enable `pdo_mysql` in its `php.ini` and run the command above with the full path to `php.exe`, or configure Apache with this project's `public` directory as its document root. Do not serve the project root: database tools and backups should remain outside the web-accessible directory.

## Usage

The User workspace is view-only and initially shows all products, including out-of-stock items. Users can search by product name, filter by stock status, and export the filtered list as CSV. Users cannot edit products or access account administration or employee assignments.

Admins can open **Item assignments** to record Brand, Description, Unit, Serial number, Employee name, Position, Office, and Date received. All fields are required. **Save** stores the record in MySQL table `item_assignments` and displays it in the saved assignments list. Serial numbers are unique across assignments, ignoring letter case and surrounding spaces; duplicates are blocked by a database unique index. This register is separate from product quantity tracking and does not automatically change stock levels. Users cannot access employee assignment records.

Forms and in-app page links use JSON requests to update the screen without a full page reload. Validation errors appear in the current form. Data continues to be stored in MySQL. See [JSON interaction details](app/JSON-API.md).

- Add a product with a name, starting quantity, and minimum stock.
- Use **Edit** to change product details or set the exact stock quantity.
- Use **Stock** to add received units or remove issued units. Stock cannot fall below zero.
- Products at or below their minimum stock appear as low stock; zero quantities display as out of stock.
- Search by product name. **Export CSV** exports the current filtered results.
- Dashboard totals always cover the entire inventory.
- Delete permanently removes a product; the browser asks for confirmation when JavaScript is enabled.

## Scope

The first visit asks you to create an admin account; there are no default passwords. After initial setup, sign out and choose **New here? Sign up** to register as a **User**. Choose your own username and password (minimum 5 characters). Signup signs you in automatically; subsequent visits use the same sign-in form for all roles. Public signup cannot create admins. Admins can open **Accounts** to view account names and active status; this page has no editing controls.

Accounts and inventory are saved in MySQL database `inventory_hq`. Existing accounts were migrated with their IDs and password hashes intact. The original SQLite file and a dated backup are retained under `data`, but are no longer used by the app. Usernames are case-insensitive. See [database settings and migration details](database/README.md).

| Role | Access |
| --- | --- |
| User | View inventory, search, filter, export CSV |
| Admin | Manage products and stock; view account names, roles, and status |

Permissions are enforced on the server, including direct POST requests. Existing inventory is preserved when accounts are added. Designed for local use; stock movement history is not included. Use HTTPS and suitable hosting configuration before deploying publicly. Use phpMyAdmin's Export feature to back up `inventory_hq`.

## Quick checks

```sh
php -l public/stockroom.php
php -S localhost:8000 -t public
```

Create a product, edit its name, receive stock, issue stock, and try issuing more units than available. Check low-stock filtering, search, CSV export, and deletion. Save an item assignment and try a duplicate serial number. Confirm Users cannot make changes or access employee assignments.
