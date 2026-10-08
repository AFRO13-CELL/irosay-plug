# IROZAY DE PLUG — Deployment Guide

Your system has been running locally on Laragon this whole time, which is great for
development but not meant for real customers to depend on. This guide covers what
changes before you trust it with daily sales.

## 1. Environment settings

In production, edit `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-real-domain.com
```

`APP_DEBUG=false` is critical — with it `true`, any error shows a full stack trace
(file paths, query contents, sometimes credentials) to anyone who triggers it. Never
run a live store with debug mode on.

Generate a fresh app key if you haven't already for this environment:

```
php artisan key:generate
```

## 2. Database

- Use a real MySQL/MariaDB instance from your host (not Laragon's local one).
- Update `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env` to match.
- Run migrations and seeders on the fresh production database:
  ```
  php artisan migrate --force
  php artisan db:seed --force
  ```
  (`--force` is required because Laravel normally refuses to run migrations in
  production without it, as a safety check.)
- **Change the seeded admin password immediately** — `ChangeMe123!` from Phase 1
  should never reach a live server. Log in once and update it via the Users page,
  or update it directly before going live.
- Set up automatic daily MySQL backups through your host's control panel — this
  system has no built-in backup feature, and losing the database means losing
  every sale, IMEI record, and financial history permanently.

## 3. Web server

`php artisan serve` is a development-only server — it isn't hardened, doesn't
handle concurrent load well, and stops the moment you close its terminal. For
production, use a real web server pointed at the `public/` folder:

- **Apache**: point the virtual host's document root at `public/`, enable
  `mod_rewrite` (Laravel's `.htaccess` in `public/` needs it).
- **Nginx**: use Laravel's standard Nginx config block (search "Laravel Nginx
  configuration" in Laravel's official docs for the exact block — it routes
  everything through `public/index.php`).

Most Ghanaian/African shared-hosting panels (cPanel, etc.) support pointing a
domain's document root at a subfolder — set it to `public/`, not the project root.

## 4. HTTPS

Get an SSL certificate (Let's Encrypt is free) so customer and login data isn't
sent in plain text. Most hosts offer one-click Let's Encrypt setup.

## 5. Performance caching

Before going live, run:

```
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

These pre-compile config, routes, and views so every request doesn't re-parse
them from scratch. **Important:** if you cache config/routes, you must re-run
these commands after every future code change (including copying in a new
phase's files) or Laravel will keep serving the old cached version. Run
`php artisan optimize:clear` to wipe all caches if something seems stuck.

## 6. File permissions

The `storage/` and `bootstrap/cache/` folders need to be writable by the web
server process. On Linux hosts: `chmod -R 775 storage bootstrap/cache` and make
sure the web server user owns them or is in the owning group.

## 7. Tailwind CDN

The UI currently loads Tailwind CSS from a CDN (`cdn.tailwindcss.com`) at
runtime, which the Tailwind team explicitly says is for development/prototyping
only — it's slower and not meant for production traffic. Before a serious
launch, consider compiling Tailwind properly with Vite (Laravel's default
frontend build tool) instead. This is a real code change, not a config toggle —
let me know if you'd like help with it.

## 8. Monitoring

Consider setting up:
- Uptime monitoring (a free service like UptimeRobot pinging your site)
- Error logging alerts — `storage/logs/laravel.log` grows over time; check it
  periodically or forward it somewhere you'll actually see problems

## 9. Going-live checklist

- [ ] `.env` set to production values, `APP_DEBUG=false`
- [ ] Real database, migrated and seeded
- [ ] Default admin password changed
- [ ] Automatic database backups configured
- [ ] Domain pointed at `public/`, HTTPS enabled
- [ ] Config/route/view caches built
- [ ] Storage folders writable
- [ ] You've walked through a full test sale, purchase, and return on the LIVE
      site before announcing it to customers
