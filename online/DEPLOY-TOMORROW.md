# Maikot Business Manager — Online Deployment Checklist

This folder is preparation for the PHP + MySQL online version. The current `demo/` LocalStorage version is intentionally left untouched.

## Tomorrow: hosting/domain setup order

1. Create the domain/subdomain in cPanel and note its document root.
2. Create a MySQL database and MySQL user in cPanel.
3. Give that user **ALL PRIVILEGES** on the new database.
4. Open phpMyAdmin, select the new database, and import `online/schema.sql`.
5. On the hosting server, copy `online/config/database.example.php` to `online/config/database.php` and fill in the real DB name, DB user, and DB password.
6. Do **not** commit the real `database.php` credentials to GitHub.
7. Upload/deploy the project files to the chosen document root.
8. Open `/online/api/health.php` on the live domain. It should report `ok: true` and all required tables as `true`.
9. Only after the health check passes, connect the browser UI to the API/database.
10. Import the recovered LocalStorage JSON backup into MySQL only after taking one more local JSON backup.
11. Verify counts before switching to online mode: Products, Suppliers, Purchases, Sales, Parties and Payments.
12. Test from both PC and mobile before making the database-backed version the primary working system.

## Data migration rules

- Preserve existing product IDs/links where possible during migration.
- Do not regenerate old product codes automatically.
- Purchase conversion fields (`purchase_unit`, `units_per_unit`, transport/other cost modes) must be preserved.
- Sales must be migrated with bill number, line items, VAT, paid amount, due amount and party linkage.
- Parties and received payments must be migrated before validating final receivables.
- Never replace production data with sample data.

## Security before real use

- Add admin login before exposing write APIs publicly.
- Use PHP sessions with `password_hash()` / `password_verify()`.
- Keep database credentials server-only.
- Use prepared statements for all database writes.
- Force HTTPS once the domain SSL certificate is active.
- Keep regular JSON/database backups.

## Prepared files

- `online/schema.sql` — full MySQL schema for current demo features.
- `online/config/database.example.php` — safe credential template.
- `online/api/bootstrap.php` — common JSON/database bootstrap.
- `online/api/health.php` — database and table readiness test.

## Current status

The existing GitHub Pages demo remains the working LocalStorage version. The online backend preparation is separate so today's working data/UI is not disturbed.
