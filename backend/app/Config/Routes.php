<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Home::index');

// =============================================================================
// API v1 Routes
// =============================================================================
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], static function ($routes) {

    // -------------------------------------------------------------------------
    // Auth (public — no JWT required)
    // -------------------------------------------------------------------------
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/refresh', 'AuthController::refresh');

    // Auth (protected)
    $routes->post('auth/logout', 'AuthController::logout');
    $routes->get('auth/me', 'AuthController::me');
    $routes->put('auth/profile', 'AuthController::updateProfile');
    $routes->put('auth/password', 'AuthController::changePassword');

    // -------------------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------------------
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/kpis', 'DashboardController::kpis');
    $routes->get('dashboard/trend', 'DashboardController::trend');
    $routes->get('dashboard/heatmap', 'DashboardController::heatmap');
    $routes->get('dashboard/insights', 'DashboardController::insights');

    // -------------------------------------------------------------------------
    // Transactions
    // -------------------------------------------------------------------------
    $routes->get('transactions', 'TransactionController::index');
    $routes->get('transactions/(:num)', 'TransactionController::show/$1');
    $routes->post('transactions', 'TransactionController::create');
    $routes->put('transactions/(:num)', 'TransactionController::update/$1');
    $routes->delete('transactions/(:num)', 'TransactionController::delete/$1');

    // -------------------------------------------------------------------------
    // Categories
    // -------------------------------------------------------------------------
    $routes->get('categories', 'CategoryController::index');
    $routes->post('categories', 'CategoryController::create');
    $routes->put('categories/(:num)', 'CategoryController::update/$1');
    $routes->patch('categories/(:num)/archive', 'CategoryController::archive/$1');
    $routes->delete('categories/(:num)', 'CategoryController::delete/$1');

    // -------------------------------------------------------------------------
    // Profiles
    // -------------------------------------------------------------------------
    $routes->get('profiles', 'ProfileController::index');
    $routes->post('profiles', 'ProfileController::create');
    $routes->put('profiles/(:num)', 'ProfileController::update/$1');
    $routes->patch('profiles/(:num)/default', 'ProfileController::setDefault/$1');
    $routes->delete('profiles/(:num)', 'ProfileController::delete/$1');

    // -------------------------------------------------------------------------
    // Budgets
    // -------------------------------------------------------------------------
    $routes->get('budgets', 'BudgetController::index');
    $routes->post('budgets', 'BudgetController::create');
    $routes->put('budgets/(:num)', 'BudgetController::update/$1');
    $routes->delete('budgets/(:num)', 'BudgetController::delete/$1');

    // -------------------------------------------------------------------------
    // Savings Goals
    // -------------------------------------------------------------------------
    $routes->get('goals', 'GoalController::index');
    $routes->get('goals/(:num)', 'GoalController::show/$1');
    $routes->post('goals', 'GoalController::create');
    $routes->put('goals/(:num)', 'GoalController::update/$1');
    $routes->post('goals/(:num)/deposit', 'GoalController::deposit/$1');
    $routes->delete('goals/(:num)', 'GoalController::delete/$1');

    // -------------------------------------------------------------------------
    // Recurring Transactions
    // -------------------------------------------------------------------------
    $routes->get('recurring', 'RecurringTransactionController::index');
    $routes->post('recurring', 'RecurringTransactionController::create');
    $routes->put('recurring/(:num)', 'RecurringTransactionController::update/$1');
    $routes->patch('recurring/(:num)/toggle', 'RecurringTransactionController::toggle/$1');
    $routes->post('recurring/(:num)/process', 'RecurringTransactionController::process/$1');
    $routes->delete('recurring/(:num)', 'RecurringTransactionController::delete/$1');

    // -------------------------------------------------------------------------
    // Net Worth
    // -------------------------------------------------------------------------
    $routes->get('net-worth', 'NetWorthController::index');
    $routes->post('net-worth', 'NetWorthController::create');
    $routes->put('net-worth/(:num)', 'NetWorthController::update/$1');
    $routes->delete('net-worth/(:num)', 'NetWorthController::delete/$1');

    // -------------------------------------------------------------------------
    // Tags
    // -------------------------------------------------------------------------
    $routes->get('tags', 'TagController::index');
    $routes->post('tags', 'TagController::create');
    $routes->put('tags/(:num)', 'TagController::update/$1');
    $routes->delete('tags/(:num)', 'TagController::delete/$1');

    // -------------------------------------------------------------------------
    // Settings
    // -------------------------------------------------------------------------
    $routes->get('settings', 'SettingsController::index');
    $routes->put('settings', 'SettingsController::update');
    $routes->get('settings/currencies', 'SettingsController::currencies');

    // -------------------------------------------------------------------------
    // Receipts
    // -------------------------------------------------------------------------
    $routes->get('receipts/(:num)', 'ReceiptController::index/$1');
    $routes->post('receipts/(:num)', 'ReceiptController::upload/$1');
    $routes->get('receipts/download/(:num)', 'ReceiptController::download/$1');
    $routes->delete('receipts/(:num)', 'ReceiptController::delete/$1');

    // -------------------------------------------------------------------------
    // Reports & Export
    // -------------------------------------------------------------------------
    $routes->get('reports/monthly', 'ReportController::monthly');
    $routes->get('reports/yearly', 'ReportController::yearly');
    $routes->get('reports/category/(:num)', 'ReportController::category/$1');
    $routes->get('reports/income-vs-expense', 'ReportController::incomeVsExpense');
    $routes->get('reports/budget-performance', 'ReportController::budgetPerformance');
    $routes->get('reports/export/csv', 'ReportController::exportCsv');
    $routes->post('reports/import/csv', 'ReportController::importCsv');

    // -------------------------------------------------------------------------
    // Admin (requires admin role — filtered in AdminController + AdminFilter)
    // -------------------------------------------------------------------------
    $routes->group('admin', ['filter' => 'admin'], static function ($routes) {
        $routes->get('users', 'AdminController::users');
        $routes->post('users', 'AdminController::createUser');
        $routes->put('users/(:num)', 'AdminController::updateUser/$1');
        $routes->delete('users/(:num)', 'AdminController::deleteUser/$1');
        $routes->patch('users/(:num)/toggle', 'AdminController::toggleUser/$1');
        $routes->patch('users/(:num)/reset-password', 'AdminController::resetPassword/$1');
        $routes->get('stats', 'AdminController::stats');
        $routes->get('activity-logs', 'AdminController::activityLogs');
    });
});
