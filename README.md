# Family Financing — Personal Income & Expense Manager

This is a personal project created for testing and personal use. Anyone is free to use it if they find it helpful.

Project overview

Family Financing is a lightweight Laravel application for tracking family income and expenses. It lets you record daily transactions, organize them by categories and tags, schedule recurring (periodic) transactions, and view summaries on a dashboard with charts.

Key features
- Record transactions (income and expense) with title, amount, date, category, and tags.
- Create and manage categories and tags for structured reporting.
- Define periodic (recurring) transactions that can be applied on schedule.
- Live dashboard with visual summaries and charts (built with Laravel Livewire).
- Uses SQLite by default for quick local setup (can be configured to other DBs).

Tech stack
- Backend: Laravel (Eloquent models for Transaction, Category, Tag, PeriodicTransaction, Inventory)
- Frontend: Laravel Livewire, Vite, Tailwind CSS
- Database: SQLite (default), compatible with MySQL/Postgres

Quick installation (development)

1. Clone the repository and enter the project folder:
   git clone <repository-url>
   cd family-financing

2. Install PHP dependencies:
   composer install

3. Install JavaScript dependencies:
   npm install

4. Create environment file and adjust settings (DB, APP_URL, etc.):
   cp .env.example .env
   # By default the project includes database/database.sqlite — ensure your .env uses it or configure another DB.

5. Generate app key:
   php artisan key:generate

6. Run database migrations (and optionally seeders):
   php artisan migrate
   # php artisan db:seed

7. Run dev servers / build assets:
   # For development with hot reload
   npm run dev
   # Or build production assets
   npm run build

8. Start the application:
   php artisan serve
   # Visit http://localhost:8000

Usage notes
- The web UI (Livewire components) provides pages to manage Categories, Tags, Transactions, Periodic Transactions, and a Dashboard.
- Transactions support many-to-many tags and belong to a category and user.
- Periodic transactions can be used to model recurring incomes/expenses.

Contributing
- This is a personal project but open to contributions. Fork, create a branch for your feature/fix, and open a pull request.

License
- This project is provided under the MIT License.