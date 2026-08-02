<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';

function currentUser(): ?array {
    return $_SESSION['ed_user'] ?? null;
}

function requireLogin(): void {
    if (!currentUser()) {
        header('Location: ' . BASE_PATH . '/pages/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ((currentUser()['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo '<h2 style="font-family:sans-serif;padding:40px;color:#c0392b;">403 — Admin access required.</h2>';
        exit;
    }
}

function isAdmin(): bool {
    return (currentUser()['role'] ?? '') === 'admin';
}

// Which signature blocks a role can see on customer-profile
function sigVisibility(): array {
    $role = currentUser()['role'] ?? '';
    $map = [
        'admin'             => ['sig_sales_person','sig_team_leader','sig_finance','sig_commercial','sig_coordinator','sig_head'],
        'team_leader'       => ['sig_sales_person','sig_team_leader'],
        'finance'           => ['sig_sales_person','sig_team_leader','sig_finance'],
        'sales_person'      => ['sig_sales_person'],
        'commercial'        => ['sig_commercial'],
        'sales_coordinator' => ['sig_coordinator'],
        'head_of_business'  => ['sig_head'],
        // New department roles
        'marketing'         => ['sig_sales_person'],
        'costing'           => [],
        'production'        => [],
        'commercial_dept'   => ['sig_commercial'],
    ];
    return $map[$role] ?? array_keys($map['admin']); // fallback: show all
}

// Which order workflow tabs a role can access (empty = all)
function allowedTabs(): array {
    $role = currentUser()['role'] ?? '';
    $map = [
        'marketing'       => ['marketing-intake', 'marketing', 'customer-profile', 'create-customer'],
        'sales_person'    => ['customer-profile', 'create-customer'],
        // Marketing team leader — marketing work + team-lead approval
        'team_leader'     => ['marketing-intake', 'marketing', 'customer-profile', 'create-customer'],
        'finance'         => ['customer-profile'],
        'costing'         => ['costing-review'],
        'production'      => ['production'],
        'commercial_dept' => ['sales','single-pi','summary-pi','master-pi','lc','exchange','commercial','packing','delivery','truck','origin','beneficiary','forwarding','bank-forwarding','po-status'],
        'commercial'      => ['sales','single-pi','summary-pi','master-pi','lc','exchange','commercial','packing','delivery','truck','origin','beneficiary','forwarding','bank-forwarding','po-status'],
    ];
    // admin and legacy roles see everything
    return $map[$role] ?? [];
}

function canAccessTab(string $tabId): bool {
    $allowed = allowedTabs();
    if (empty($allowed)) return true; // no restriction = all tabs
    return in_array($tabId, $allowed, true);
}

function currentUserRole(): string {
    return currentUser()['role'] ?? '';
}
