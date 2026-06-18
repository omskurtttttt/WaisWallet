# WaisWallet — Personal Finance Dashboard

WaisWallet is a sleek, modern, and highly interactive personal finance management web application. Built with **Laravel 13**, **Inertia.js 2.0**, **Vue 3**, **Tailwind CSS v4**, and **PostgreSQL**, WaisWallet features dark/light themes, smooth page animations, glassmorphism card layouts, and dynamic data visualizations.

---

## 📈 Key Features

### 1. Interactive Dashboard

- **Aggregate Metrics**: Real-time display of total balance, monthly income, monthly expense, and net monthly savings.
- **Visual Cash Flow**: Timeline trends of income vs. expenses and daily spending utilizing ApexCharts.
- **Card Wallet Preview**: Quick visual reference of active funding cards and their computed balances.
- **Recent Activity**: Instant tabular view of the latest 5 transactions.

### 💸 2. Transactions Manager

- **Full CRUD Support**: Securely create, view, update, and delete transaction logs.
- **Server-Side Filters**: Search by title, type (Income/Expense), category, status, and custom date ranges with debounced requests.
- **Stable Pagination**: Sorted tables with URL parameter syncing for shareable/bookmarkable search states.
- **Confirmation Modals**: Destructive deletes protected by micro-animated confirmation modals.

### 💳 3. Virtual Wallet

- **Card Customization**: Set up Visa, Mastercard, American Express, or Discover cards with customized names and vivid color gradients.
- **Balance Tracking**: Automatically calculates card balances on-the-fly based on transaction inflows and outflows.
- **Soft Deletes**: Safety-net deletion of payment cards.

### 🎯 4. Savings Goals Tracker

- **Financial Milestones**: Define targets (e.g., Emergency Fund, Car Downpayment) with goal amounts and target dates.
- **Visual Progress**: Automatically computed percentages and visual progress bars.
- **Fund Allocation**: Deposit or withdraw funds from goals to adjust current balances.
- **Goal Status**: "Days left" badges (highlighted in red for goals ending in under 30 days) and automated completion states.

### 📊 5. Financial Analytics & Budgets

- **Category Budgets**: Set monthly spending limits per category (e.g., Food, Bills, Shopping).
- **Spending Alerts**: Interactive progress bars highlighting budget utilization (overall and by category).
- **Interactive Visualizations**: Donut charts for category breakdown, line charts for daily outflows, and comparative bar charts.

### 🔔 6. Notification Center

- **System Alerts**: In-app notifications triggered for budget warnings (80% or exceeded 100%) and savings goal achievements (80% milestone or completed).
- **Read Management**: Instantly mark notifications as read or clear all at once.

### 📄 7. Financial Reports

- **Dynamic Scopes**: Review financial performance toggling between specific months or full financial years.
- **Trend Indicators**: Compare metrics against previous periods (last month/year) showing percentage growth/decrease.
- **JSON Statement Export**: Fast backup data downloads.
- **Print Layout**: Custom CSS print media rules to cleanly print statements or save them as PDFs.

---

## 🛠 Tech Stack

- **Backend**: Laravel 13, PHP 8.3+
- **Frontend**: Vue 3 (Composition API), Inertia.js 2.0 (Vue 3 Adapter), Pinia (Theme Preference)
- **Styling**: Tailwind CSS v4, Custom CSS Variables, Google Fonts (Plus Jakarta Sans, Lora, IBM Plex Mono)
- **Charts**: ApexCharts (`vue3-apexcharts`)
- **Database**: PostgreSQL (Production & Local)
- **Testing**: Pest Framework, PHPUnit

---

## 📂 Project Architecture

The codebase follows professional Laravel and Vue architecture practices:

```
├── app/
│   ├── Http/
│   │   ├── Controllers/   # Thin controllers managing route responses
│   │   ├── Requests/      # Specialized FormRequests handling validation
│   │   └── Middleware/    # Auth, verified, and theme middleware
│   ├── Models/            # Eloquent models using UUID concerns and relationships
│   ├── Policies/          # Authorization checks guarding operations per user
│   └── Services/          # Extracted business logic layer (Dashboard, Reports, etc.)
├── config/                # Central application configuration files
├── database/
│   ├── migrations/        # PostgreSQL schema structure files
│   └── seeders/           # Populates user with cards, transactions, and categories
├── resources/
│   ├── css/
│   │   └── app.css        # Tailwind v4 configuration and custom CSS variables
│   └── js/
│       ├── Components/    # Reusable Vue components (UI inputs, Modals, Charts)
│       ├── Composables/   # Shared frontend helpers (currency formatting, debouncing)
│       ├── Pages/         # Parent Inertia view templates
│       ├── Stores/        # Pinia store for persistent theme states
│       └── app.js         # Inertia startup bootstrap
└── tests/                 # Full feature test suite using Pest
```

---

## 🚀 Installation & Setup

Follow these steps to run WaisWallet locally:

### Prerequisites

- **PHP 8.3+**
- **Composer**
- **Node.js 18+** & **npm**
- **PostgreSQL** server running locally

### 1. Clone the Repository

```bash
git clone https://github.com/omskurtttttt/WaisWallet.git
cd WaisWallet
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
```

### 3. Environment Configuration

Copy the sample configuration file:

```bash
cp .env.example .env
```

Open `.env` and fill in your local PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=waiswallet
DB_USERNAME=your_postgres_user
DB_PASSWORD=your_postgres_password
```

### 4. Create the Database

Ensure you have created the database specified in `.env` inside your PostgreSQL server:

```sql
CREATE DATABASE waiswallet;
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Migrations & Seeder

Populate the database with realistic mock data to explore the dashboard features instantly:

```bash
php artisan migrate --seed
```

_This seeds a default user:_

- **Email**: `john@example.com`
- **Password**: `password`

### 7. Run Development Servers

Start the PHP artisan server:

```bash
php artisan serve
```

In a separate terminal, launch the Vite dev server for hot module reloading:

```bash
npm run dev
```

Visit the application at `http://localhost:8000` and sign in.

---

## 🧪 Verification & Linting

WaisWallet includes a robust suite of automated tests verifying dashboard controllers, analytics calculations, goal triggers, notifications, and permissions.

Run the test suite:

```bash
php artisan test
```

To optimize resources for production deployment:

```bash
npm run build
```
