# Installation Troubleshooting Guide

If you are facing issues while installing or migrating this project to a new server, please follow this guide.

## Common Issue 1: "Database file at path... does not exist" or "Table 'sessions' doesn't exist"

**Cause:** 
Your `.env` file has `SESSION_DRIVER=database` but the database tables have not been created yet (migrations have not run). When Laravel tries to load the first page (even the `/install` page), it crashes because it can't find the `sessions` table in the database.

**Solution:**
1. Open your `.env` file.
2. Change `SESSION_DRIVER=database` to `SESSION_DRIVER=file`.
3. Save the file and reload the `/install` page.

## Common Issue 2: Redirecting to Homepage continuously when trying to visit `/install`

**Cause:**
Your `.env` file has `APP_INSTALLED=true`. This tells the system that the installation is already finished, so it blocks access to the `/install` page for security reasons.

**Solution:**
1. Open your `.env` file.
2. Scroll to the bottom and change `APP_INSTALLED=true` to `APP_INSTALLED=false`.
3. If it still redirects, your browser has cached the redirect. Open an **Incognito Window** or **Private Window** and visit `/install` again.

## Common Issue 3: 1045 Access Denied for user (Database Connection Error)

**Cause:**
The database username or password in your `.env` file is incorrect, or your password contains special characters that are not properly escaped.

**Solution:**
1. Check your database credentials in your hosting control panel (e.g., CloudPanel, cPanel).
2. Open your `.env` file.
3. If your password contains any special characters (like `#`, `@`, `$`, `&`), you MUST wrap the password in double quotes. 
   **Example:** `DB_PASSWORD="My@Password#123"`
4. Make sure there are no spaces around the `=` sign.

## Quick Checklist for a Fresh Installation:
Before visiting `/install` on a new server, make sure your `.env` file has these 3 settings:
- `SESSION_DRIVER=file`
- `APP_INSTALLED=false`
- `DB_CONNECTION=mysql` (with the `#` removed from the database lines below it)

Once the setup is successfully completed via `/install`, you can change `SESSION_DRIVER` back to `database` if you prefer.
