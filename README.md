# PHP Blog Management System (PHP 8 + MySQL)

Production-ready blog CMS with secure admin dashboard, TinyMCE editor, media uploads, SEO metadata, and clean public blog pages.

## Features
- Admin authentication (sessions, password_hash, CSRF) with login/logout.
- Dashboard stats, recent edits, flash messages.
- Blog CRUD with draft/published/scheduled states, slug editing, pagination, search, filtering, sorting.
- TinyMCE WYSIWYG editor with authenticated image upload handler.
- Media validation (MIME/size), auto-renaming, soft deletes for blogs.
- Public blog list + detail pages with SEO meta tags and 404 handling.

## Quick Setup
1. **Create MySQL DB**
   ```sql
   CREATE DATABASE blog_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   Import schema:
   ```bash
   mysql -u <user> -p blog_cms < database/schema.sql
   ```
2. **Configure DB Credentials**
   Edit `config/config.php` or set environment variables `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `BASE_URL` (e.g., `/` or `/your-subdir/`).
3. **Permissions**
   Ensure the web server can write to `uploads/blogs/`:
   ```bash
   chmod -R 775 uploads
   ```
4. **Default Admin**
   - Email: `admin@example.com`
   - Password: `Admin@123`
   Change the password after first login.
5. **Access**
   - Admin: `/admin/auth/login.php`
   - Public blog list: `/` (or `/index.php`)
   - Public post: `/blog.php?slug=your-post-slug`

## Project Structure
- `config/` — App + DB config.
- `includes/` — Shared layout, helpers, CSRF, auth guard.
- `admin/` — Dashboard, auth, blog management, media upload handler.
- `public/` — Front-facing pages (list + detail).
- `assets/css/` — Basic styling for admin and public views.
- `uploads/blogs/` — Stored images (with `.htaccess` to disable script execution).
- `database/schema.sql` — Tables + default admin seed.

## Notes & Security
- Uses prepared statements everywhere; HTML sanitized to strip scripts.
- CSRF tokens on all state-changing forms.
- Session regenerated on login; logout destroys the session.
- Image uploads restricted by MIME type and size (2MB default).

Deploy on Apache/shared hosting by pointing the document root to the project. Update `BASE_URL` if deploying in a subdirectory (e.g., `/mypetbnb/`).
