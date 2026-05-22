# WaisWallet — Premium Personal Finance Dashboard

WaisWallet is a sleek, modern, and highly interactive personal finance management web application. Built with **Laravel 11**, **Inertia.js**, **Vue 3**, **Tailwind CSS v4**, and **PostgreSQL**, WaisWallet is designed with premium dark/light themes, smooth page animations, glassmorphism card layouts, and dynamic data visualizations.

---

## Key Features

### 📈 1. Interactive Dashboard
- **Aggregate Metrics**: Track your total balance, monthly spending, monthly savings, and budget utilization in real time.
- **Spending & Balance Charts**: Clear visual timelines of your cash flow trends using ApexCharts.
- **Card Wallet Preview**: Quick glance at your active credit/debit card accounts.
- **Recent Transactions**: Instant tabular view of your latest 5 transactions.

### 💸 2. Transactions Manager
- **Full CRUD operations**: Create, update, view, and delete transactions.
- **Server-side Search & Filtering**: Filter by type (Income vs Expense) or Category, and search by transaction title with debounced requests.
- **Pagination**: High-performance paginated lists using Inertia's router.
- **Custom Confirmation Modals**: Destructive deletions secured with clean, native-styled confirmation cards.

### 💳 3. Virtual Wallet
- **Card Customization**: Add Visa, Mastercard, American Express, or Discover cards with custom name tags and color gradients.
- **Manage Funding Methods**: Visual credit/debit card components representing funding methods.
- **Dynamic Deletion**: Remove expired or unused payment cards.

### 🎯 4. Savings Goals Tracker
- **Set Financial Milestones**: Define targets (e.g., Emergency Fund, Car Downpayment) with goal amounts and target dates.
- **Visual Progress Bars**: Watch savings progress automatically calculated.
- **Contribution History**: Easily deposit or withdraw funds from goals to adjust current balances.
- **Time Indicators**: Displays "days left" badges (highlighted in red for goals ending in under 30 days) and automated success triggers.

### 📊 5. Financial Analytics
- **Spending Trends**: Smooth line/area charts visualizing daily outflows.
- **Category Distributions**: Donut and pie charts ranking category expenses by percentage and absolute values.
- **Monthly Inflows vs Outflows**: High-level visual comparisons of income vs expenses over the last 6 months.

### 📄 6. Financial Reports
- **Monthly and Yearly Scopes**: Toggle report views to review data for specific months or full financial years.
- **Trend Comparison Indicators**: Compare current metrics against previous periods (last month/year) with percentage increases/decreases.
- **Print Statement View**: Custom print styles format statements beautifully for physical printouts or PDF saves.
- **Data Exporting**: Download detailed statements directly as JSON files.

---

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Vue 3 (Composition API), Inertia.js, Pinia (Theme State)
- **Styling**: Tailwind CSS v4, Custom CSS Design Variables, Google Fonts (Plus Jakarta Sans, IBM Plex Mono, Lora)
- **Charts**: ApexCharts (vue3-apexcharts)
- **Database**: PostgreSQL (Production) / SQLite (Local Support)

---

## Installation & Setup

Follow these steps to run WaisWallet locally:

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ and npm
- PostgreSQL database (or SQLite setup)

### 1. Clone the repository
```bash
git clone https://github.com/omskurtttttt/WaisWallet.git
cd WaisWallet
```

### 2. Install dependencies
```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
```

### 3. Environment configuration
Copy the sample environment file and configure your database:
```bash
cp .env.example .env
```
Open `.env` and fill in your connection details (PostgreSQL details, database name, username, and password).
*Note: If using SQLite, set `DB_CONNECTION=sqlite` and let Laravel create the database file.*

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations & Seeder
Run the database migrations and seed realistic mock data to explore the application:
```bash
php artisan migrate --seed
```
*This seeds a default test user:*
- **Email**: `john@example.com`
- **Password**: `password`

### 6. Start the Servers
Launch the Laravel development backend:
```bash
php artisan serve
```

In a separate terminal, launch the Vite dev server for hot module reloading:
```bash
npm run dev
```

Visit the application at `http://localhost:8000`.

---

## Production Build

To compile and optimize assets for production deployment:
```bash
npm run build
```

---

## Code Quality & Verification
WaisWallet is built following Laravel conventions and modern Vue structures. Assets and compilation scripts compile with zero linting warnings or compilation errors.
