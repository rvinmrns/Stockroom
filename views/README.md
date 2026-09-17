# UI templates

Edit page markup and forms here. The PHP controllers load these templates after authentication, validation, and database processing.

| File | Purpose |
| --- | --- |
| inventory.php | Admin inventory dashboard and User catalog layout |
| assignments.php | Admin item and employee assignment form and records |
| users.php | Admin account list |
| login.php | Login, signup, and first-admin setup |
| partials/catalog.php | User product cards |
| partials/account-menu.php | Account links and sign-out form |
| welcome.php | Legacy role-selection layout; not used by the current unified login |

Templates use variables prepared by `public/*.php` or `app/auth.php`; do not open them directly in the browser. Keep HTML escaping (`e()`) on user-provided values. Shared partials use filesystem paths relative to the view directory.

CSS and JavaScript remain in `public/style.css`, `public/accounts.css`, and `public/app.js` so the browser can load them. Browser links and form actions continue to target `stockroom.php`, `users.php`, and `assignments.php`.

The public document root must remain `public`. `beginPage()` and `endPage()` in the controllers preserve HTML and JSON responses; views contain the presentation only.
