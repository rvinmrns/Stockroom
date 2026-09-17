# JSON interactions

The browser uses `fetch` for login, signup, logout, inventory mutations, search, filters, and in-app page links. MySQL remains the persistent store. Standard form submissions still work when JavaScript is unavailable. CSV export remains a normal download.

Send `Accept: application/json` for a JSON page response. Send `Content-Type: application/json` for JSON form submissions. Requests use the existing session cookie, and every mutation requires the `csrf` value returned by a page request.

## Read

- `GET /stockroom.php`: login/setup page when signed out; inventory when signed in.
- `GET /stockroom.php?signup=1`: signup page when signed out.
- `GET /stockroom.php?q=keyboard&status=low`: filtered inventory.
- `GET /stockroom.php?add=1` or `?edit=ID`: product form for authorized accounts.
- `GET /users.php`: account names, roles, and active status, restricted to admins.
- `GET /assignments.php`: assignment entry form and saved item/employee records, restricted to admins.

Page responses contain `ok`, `csrf`, `data`, and `html`. `data` includes structured inventory/account information; `html` contains the PHP-rendered view used to update the browser without duplicating templates in JavaScript. Password hashes are never returned.

## Write

For item assignments, POST to `/assignments.php` with `action: "save_assignment"`, `csrf`, `brand`, `description`, `unit`, `serial_number`, `employee_name`, `position`, `office`, and `date_received` (`YYYY-MM-DD`). Admin access is required. Duplicate serial numbers and invalid fields return HTTP 422. The database independently enforces serial-number uniqueness.

POST JSON objects to `/stockroom.php` with `csrf`, `action`, and the relevant fields:

| Action | Fields |
| --- | --- |
| login | username, password |
| signup | username, password, role (`user` only; defaults to `user`) |
| setup | username, password (first admin only) |
| logout | no additional fields |
| create | name, quantity, minimum_stock |
| update | id and the create fields |
| adjust | id, amount, direction (`in` or `out`) |
| delete | id |

Successful mutations return `ok: true`, `message`, `url`, and the current `csrf`. The browser requests the updated page as JSON. Errors return `ok: false` and `message` with HTTP 400 for malformed input, 401 for missing/invalid authentication, 403 for permission or CSRF failures, and 422 for validation errors.

The browser retains form values on errors, uses text-only error messages, confirms deletion, and blocks overlapping requests. It does not automatically retry writes after a network error. Browser Back/Forward loads a fresh GET to avoid restoring stale authorization state.

Run `php tests/json.php` to exercise JSON requests. It creates and removes a randomly named test database and local server; the configured database is not modified. Requires PHP cURL, PDO MySQL, `proc_open`, and a MySQL account allowed to create test databases.
