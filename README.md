# SchoolHub

School Management System

This project is SchoolHub, a Laravel-based school administration application for a multi-branch school setup.

## Project status

Completed:
- Laravel 13 base app and MySQL configuration
- Authentication with login and logout
- Inactive-account login protection and secure session logout
- Guest redirect to login
- Protected dashboard route and dashboard shell
- Built-in role permissions with branch-aware user access
- Super Administrator custom-role management with permission validation
- Protection for built-in roles, assigned roles, and the last active Super Administrator
- User-list filtering by status, role, and authorized branch
- Branch and user administration screens with policy checks
- Admin-managed school profile and time-zone settings
- Branch-scoped dashboard counts and campus filtering
- Academic year and term management per branch
- Date-overlap and academic-year boundary validation
- Transactional active-year and active-term changes
- Admin creation command
- Incremental database migrations for roles, branches, academic years, terms, and school settings
- Responsive UI shell for desktop and mobile
- Automated authentication, branch-isolation, dashboard, and academic-calendar tests

Planned next:
- Student management module
- Academic structure including grades, classes, and enrollment
- Attendance, examinations, and results
- Fee configuration and invoicing
- Reports and exports
- English and Amharic language switcher

## Local setup on Windows and WAMP

1. Install PHP 8.3 and Composer.
2. Open a terminal in the project root.
3. Copy the environment file if needed:
   ```bash
   copy .env.example .env
   ```
4. Confirm the database values in `.env` use the local WAMP MySQL connection:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=school_management_system
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Run the app dependencies:
   ```bash
   php composer.phar install
   npm install
   ```
6. Generate the application key:
   ```bash
   php artisan key:generate
   ```
7. Run the migrations:
   ```bash
   php artisan migrate
   ```
8. Start the app:
   ```bash
   php artisan serve
   ```
9. Compile frontend assets:
   ```bash
   npm run build
   ```

## Initial administrator creation

Create the first administrator with a secure password of your choice:

```bash
php artisan app:create-admin "System Admin" admin@school.local "your-strong-password"
```

Then log in at `/login`.

## Roles and permission model

The app includes seven protected built-in roles and a Super Administrator-only custom-role manager. Custom permissions are stored as permission keys and are limited to delegated module and branch-scoped capabilities; system-wide and built-in role privileges cannot be granted through the custom-role form. Custom roles cannot be deleted while users are assigned to them.

The current live database predates the custom-role permission column, so custom role writes require the pending additive migration. Do not apply it until the schema change has been reviewed. It only adds the nullable `permission_keys` column; existing role assignments remain unchanged, and built-in permissions continue to use their legacy defaults while that column is null.

## Testing

Run the project tests with:

```bash
php artisan test
```

## Email setup for password reset

The app is configured with Laravel's built-in mail settings. For local testing, you can keep `MAIL_MAILER=log` until a real provider such as SMTP or Mailgun is configured.

## Backup and restoration

- Back up the MySQL database regularly.
- Back up the `.env` file and any uploaded files in `storage`.
- Keep migrations and seeders under version control.

## Security notes

- Never commit real secrets.
- Use strong passwords for admin accounts.
- Keep the app in production mode and disable debug output before deployment.
- Enforce authorization server-side for every protected action.

## Production checklist

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set `APP_URL` to the final public domain
- Configure a real mail provider
- Use HTTPS and secure session cookies
- Review all user roles and branch restrictions before launch
