<?php
/** Public portal gateway. Only server-verified roles receive protected links. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
startAuthSession();
$role = null;
if (!empty($_SESSION['user_id'])) {
    try { if (refreshSessionIdentity()) $role = ($_SESSION['role'] ?? null); }
    catch (Throwable $ex) { error_log('Portal identity check: '.$ex->getMessage()); }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Lumiora StudentHub final integrated semester project portal">
<title>Lumiora | StudentHub Integrated Portal</title><link rel="stylesheet" href="integration-style.css"></head>
<body><div class="portal-shell">
<header class="portal-header"><a class="portal-brand" href="portal.php"><span class="brand-tile">L</span><span>Lumiora<small>StudentHub · Integrated Portal</small></span></a><nav aria-label="Primary navigation"><a href="home.html">Original home</a><a href="about.html">About</a>
<?php if ($role === 'admin'): ?><a href="admin-dashboard.php">Admin workspace</a><a href="integration-center.php">Integration tests</a>
<?php elseif ($role === 'student'): ?><a href="student-dashboard.php">Student workspace</a>
<?php else: ?><a href="register.php">Register</a><a href="login.php">Secure login</a><?php endif; ?></nav></header>
<main><section class="portal-hero"><div><p class="eyebrow">PRACTICAL 15 · FINAL INTEGRATION</p><h1>One connected place for learning and campus life<span class="accent">.</span></h1><p>Student records, events, registrations, secure accounts, live AJAX operations and role-aware administration—all together in the Lumiora StudentHub project.</p><div class="portal-actions">
<?php if ($role === 'admin'): ?><a class="button-primary" href="admin-dashboard.php">Open Admin Control Center →</a><a class="button-secondary" href="integration-center.php">Run integration readiness checks</a>
<?php elseif ($role === 'student'): ?><a class="button-primary" href="student-dashboard.php">Open Student Workspace →</a>
<?php else: ?><a class="button-primary" href="login.php">Sign in securely →</a><a class="button-secondary" href="register.php">Create student account</a><?php endif; ?></div></div><div class="hero-mark" aria-hidden="true"><span>⌘</span><small>PHP · MySQL · Fetch</small></div></section>
<section aria-label="Modules"><div class="section-label"><p class="eyebrow">CONNECTED MODULES</p><h2>Everything in one deployment</h2></div><div class="module-grid">
<article class="module"><div class="module-icon">◎</div><h3>Secure Access</h3><p>Login, session expiry, account status checks and server-side role restrictions.</p><span>Practical 9 · 10 · 14</span></article>
<article class="module"><div class="module-icon">▤</div><h3>Student Management</h3><p>Add, edit, search, filter and soft-delete student records using MySQL.</p><span>Practical 11</span></article>
<article class="module"><div class="module-icon">▣</div><h3>Event Management</h3><p>Posters, CRUD, event status, audit entries and registration counts.</p><span>Practical 12</span></article>
<article class="module"><div class="module-icon">↗</div><h3>Live Event Studio</h3><p>Fetch-powered GET, POST, PATCH, DELETE and live search without full reload.</p><span>Practical 13</span></article>
<article class="module"><div class="module-icon">◫</div><h3>Admin Insights</h3><p>Database-driven KPIs, registration chart, activity feed and RBAC navigation.</p><span>Practical 14</span></article>
<article class="module"><div class="module-icon">✓</div><h3>Release Readiness</h3><p>Protected integration checks, installation guides and test evidence checklist.</p><span>Practical 15</span></article></div></section>
</main><footer class="portal-footer">Lumiora · StudentHub Final Integration · Local XAMPP build</footer>
</div></body></html>
