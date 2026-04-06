# FinTrack — Application Usage Guide

A complete walkthrough of every feature in FinTrack.

---

## Getting Started

1. Open the app at `http://localhost:5173` (dev) or `http://localhost` (Docker)
2. Login with the default admin: `admin@fintrack.app` / `Admin@1234`
3. Change your password when prompted (Settings > Change Password)

---

## Navigation

The left sidebar provides access to all sections:

| Menu Item | Description |
|-----------|-------------|
| **Dashboard** | Overview with charts, KPIs, and insights |
| **Transactions** | Add, edit, filter, and manage income/expenses |
| **Categories** | Manage expense and income categories |
| **Budgets** | Set monthly/yearly spending limits |
| **Goals** | Create and track savings goals |
| **Recurring** | Automate regular income and expenses |
| **Net Worth** | Track assets and liabilities |
| **Reports** | Monthly, yearly, budget performance, and category reports |
| **Settings** | Currency, theme, password |
| **Profiles** | Manage financial profiles |
| **Admin** | User management (admin only) |

---

## Dashboard

The dashboard shows a real-time overview of your finances.

**Period Selector** (top-right): Switch between Week / Month / Quarter / Year to adjust KPI comparisons.

### KPI Cards
- **Income** — Total income for the period with % change vs previous period
- **Expenses** — Total expenses with % change (green = decreased, red = increased)
- **Net Balance** — Income minus expenses
- **Savings Rate** — Percentage of income saved

### Charts
- **Monthly Trend** — Line chart showing 12-month income vs expense trajectory
- **Spending by Category** — Donut chart with expense/income toggle
- **Income vs Expenses** — Grouped bar chart comparing monthly totals
- **Daily Spending Heatmap** — Calendar view of daily spending intensity (365 days)

### Widgets
- **Budget Status** — Progress bars for each active budget
- **Savings Goals** — Progress toward each savings target
- **Smart Insights** — Auto-generated tips (e.g., "Groceries spending up 35%")
- **Recent Transactions** — Last 5 transactions with quick "View all" link

---

## Transactions

### Adding a Transaction
1. Click **"Add Transaction"**
2. Select **Expense** or **Income** (toggle buttons)
3. Enter the **amount** and select **currency**
4. Choose a **category** (filtered by type — expense categories for expenses, income for income)
5. Pick a **date**
6. Add a **description** and optional **notes**
7. Add **tags** (type a tag name and press Enter)
8. Click **Create**

### Editing a Transaction
- Click the pencil icon on any transaction row
- Make changes and click **Update**
- When editing, you can also **upload receipts** (images/PDFs)

### Filtering Transactions
The filter bar supports:
- **Search** — searches the description field
- **Type** — filter by Expense or Income
- **Category** — filter by specific category
- **Date range** — pick start and end dates
- **Amount range** — set min/max amounts
- **Sort** — by date, amount, or description

Click **Apply** to filter, or the filter-slash icon to **Reset**.

### CSV Export / Import
- **Export**: Click the download icon to export current filtered transactions as CSV
- **Import**: Click the upload icon, select a CSV file — the app auto-creates categories if needed

### Receipts
When editing a transaction:
- Click **"Upload Receipt"** to attach an image or PDF
- View existing receipts in the dialog
- Click the X to remove a receipt

---

## Categories

### Tabs
- **Expense** — Categories for spending (Groceries, Rent, etc.)
- **Income** — Categories for earnings (Salary, Freelance, etc.)
- **Archived** — Disabled categories (hidden from dropdowns)

### Creating a Category
1. Click **"Add Category"**
2. Enter a **name**
3. Choose **type** (Expense or Income)
4. Pick an **icon** from 20 options
5. Choose a **color** from 17 swatches
6. Click **Create**

### Managing Categories
- **Edit** — pencil icon (change name, icon, color)
- **Archive** — inbox icon (hides from dropdowns, moves to Archived tab)
- **Restore** — replay icon on archived categories
- **Delete** — trash icon (only on archived categories, only if no transactions use it)

---

## Budgets

### Creating a Budget
1. Click **"Add Budget"**
2. Optionally select a **category** (or leave blank for a general budget)
3. Set the **budget limit** amount
4. Choose **period**: Monthly or Yearly
5. Select the **month** and **year**
6. Click **Create**

### Reading Budget Cards
- **Progress bar**: Shows how much of the budget is used
  - Indigo = under 70%
  - Amber = 70-90%
  - Red = over 90%
- **Spent / Limit**: Current spending vs your limit
- **% used** and **remaining** amount shown

### Navigation
Use the left/right arrows to browse different months.

---

## Savings Goals

### Creating a Goal
1. Click **"New Goal"**
2. Enter a **name** (e.g., "Vacation Fund", "Emergency Fund")
3. Set the **target amount**
4. Optionally set a **target date**
5. Pick an **icon** and **color**
6. Click **Create**

### Making a Deposit
1. Click **"Add Deposit"** on any active goal
2. Enter the **amount**
3. Pick the **date**
4. Add an optional **note** (e.g., "Monthly savings")
5. Click **Deposit**

### Progress Tracking
- Circular progress ring shows the percentage
- Current amount, target, and remaining are displayed
- Goals auto-complete when target is reached
- Toggle **"Show Completed"** to see finished goals

---

## Recurring Transactions

Automate your regular income and expenses so you don't have to enter them manually each time.

### Creating a Recurring Transaction
1. Click **"Add Recurring"**
2. Select **Expense** or **Income** (toggle buttons)
3. Enter the **amount**
4. Choose a **category** (filtered by type)
5. Add a **description** (e.g., "Monthly rent")
6. Select a **frequency**: Daily, Weekly, Monthly, or Yearly
7. Pick the **next due date**
8. Click **Create**

### Managing Recurring Transactions
- **Generate Transaction**: Click the play icon to manually create a transaction from the recurring template. The next due date advances automatically.
- **Pause/Activate**: Click the pause icon to temporarily disable a recurring entry. Paused entries appear faded.
- **Edit**: Click the pencil icon to modify amount, frequency, or other details.
- **Delete**: Click the trash icon to permanently remove.

### Status Indicators
- **Due**: Red badge appears when a recurring transaction is past its next due date
- **Paused**: Amber badge for inactive recurring entries

---

## Net Worth

Track your assets and liabilities to see your total net worth at a glance.

### Adding an Entry
1. Click **"Add Entry"**
2. Select **Asset** or **Liability** (toggle buttons)
3. Enter a **name** (e.g., "Savings Account", "Car Loan")
4. Enter the **amount**
5. Pick a **date**
6. Choose an **icon** and **color**
7. Add optional **notes**
8. Click **Create**

### Reading the Dashboard
- **Total Assets**: Sum of all asset entries (green)
- **Total Liabilities**: Sum of all liability entries (red)
- **Net Worth**: Assets minus liabilities
- **Breakdown Chart**: Donut chart showing all entries by value
- Entries are grouped into **Assets** and **Liabilities** sections

### Managing Entries
- **Edit**: Click the pencil icon on any entry card to update the amount or details
- **Delete**: Click the trash icon to remove an entry

---

## Reports

### Monthly Report
- Navigate months with arrow buttons
- **4 KPI cards**: Income, Expenses, Balance, Savings Rate
- **Daily Spending Chart**: Bar chart showing spending per day
- **Expense Breakdown**: Donut chart of spending by category
- **Category Tables**: Detailed breakdown with transaction counts and share percentages

### Yearly Report
- Navigate years with arrow buttons
- **4 KPI cards** for the full year
- **Monthly Trend Chart**: Grouped bar chart (12 months)
- **Category Table**: Full year expense breakdown

### Income vs Expense
- **3 summary cards**: Total Income, Total Expenses, Net Savings
- **Combined Chart**: Bars for income/expense with a balance line
- **12-month table**: Detailed month-by-month breakdown

### Budget Performance
- Navigate years with arrow buttons
- **4 summary cards**: Total Budgeted, Total Spent, Overall Usage %, Over Budget count
- **Monthly breakdown**: Each month shows all budgets with progress bars
  - Progress bars color-coded: Indigo (< 70%), Amber (70-90%), Red (> 90%)
  - Shows category icon, budgeted vs spent amounts, remaining, and usage %

### Category Detail
1. Select a **category** from the dropdown
2. Optionally set a **date range**
3. View **5 summary stats**: Total, Average, Count, Highest, Lowest
4. Browse all **transactions** in that category

### PDF Export
Every report tab has an **"Export PDF"** button in the top-right corner. Click it to download a formatted PDF of the current report, including tables and summary data.

---

## Settings

### Preferences
- **Currency**: Default currency for new transactions (EUR, USD, GBP, INR, JPY, CAD, AUD, CHF)
- **Theme**: Dark (default) or Light mode
- **Date Format**: DD/MM/YYYY, MM/DD/YYYY, or YYYY-MM-DD
- **Language**: English

Click **"Save Preferences"** to apply.

### Change Password
1. Enter your **current password**
2. Enter the **new password** (min 8 characters)
3. **Confirm** the new password
4. Click **"Update Password"**

### Account Info
Your name, email, and role are displayed at the bottom.

---

## Profiles

Profiles let you separate different financial contexts (e.g., Personal, Business, Freelance).

### Creating a Profile
1. Click **"Add Profile"**
2. Enter a **name** and optional **description**
3. Click **Create**

### Switching Profiles
- Set a profile as **default** — all new transactions go to this profile
- The profile switcher in the **header** (top bar) lets you switch quickly if you have multiple profiles

### Rules
- You cannot delete the **default** profile
- To delete a profile, first set another one as default

---

## Settings

### Preferences
Change currency, theme (dark/light), date format, and language. Click **Save Preferences**.

### Account
Update your **Name** and **Email** directly from the Account section. Click **Update Profile** to save. The name in the sidebar/header updates immediately.

### Change Password
Enter your current password, then a new password (min 8 characters), confirm it, and click **Update Password**.

---

## Admin Panel (Admin Only)

Visible only to users with the admin role. The panel has two tabs: **Users** and **Activity Logs**.

### Users Tab

#### System Stats
4 cards showing: Total Users, Active Users, Total Transactions, Total Categories.

#### User Management
- **Create User**: Name, email, password, role (user/admin). New users must change password on first login.
- **Edit User**: Update name, email, and role for any user.
- **Activate/Deactivate**: Toggle user access (ban icon = deactivate, check icon = activate). You cannot deactivate yourself.
- **Reset Password**: Set a new password for any user. They'll be prompted to change it on next login.
- **Delete User**: Permanently delete a user and all their data. You cannot delete your own account.

### Activity Logs Tab

A complete audit trail of all user actions in the system.

**Columns:** Time, User, Action, Entity, Description, IP Address.

**Filters:**
- Filter by **User** (dropdown of all users)
- Filter by **Action** (login, transaction_create, admin_user_delete, etc.)
- Filter by **Date From / Date To** date range

**Actions logged automatically:**
| Action | Triggered by |
|--------|-------------|
| `login` / `logout` | Auth events |
| `register` | New user registration |
| `password_change` | User changes own password |
| `transaction_create/update/delete` | Transaction CRUD |
| `category_create/update/archive/restore/delete` | Category CRUD |
| `budget_create/update/delete` | Budget CRUD |
| `goal_create/update/deposit/delete` | Savings goal events |
| `admin_user_create/update/delete` | Admin creates/edits/deletes users |
| `admin_user_activate/deactivate` | Admin toggles user status |
| `admin_password_reset` | Admin resets a user's password |

**Auto-cleanup:** Logs older than 90 days are automatically removed. Run manually:
```bash
docker compose exec backend php spark logs:cleanup
# Or with custom retention:
docker compose exec backend php spark logs:cleanup --days=30
```

---

## Keyboard & UI Tips

- **Sidebar collapse**: Click the toggle at the bottom of the sidebar to collapse it
- **Mobile**: Sidebar auto-collapses on small screens, accessible via hamburger menu
- **Theme toggle**: Sun/moon icon in the header bar
- **Hover actions**: Edit/delete buttons appear on hover for categories, budgets, goals, and transactions

---

## Related Documentation

- **[DOCKER.md](DOCKER.md)** — Docker installation, commands, and troubleshooting
- **[INSTALL.md](INSTALL.md)** — Local development setup (without Docker)
