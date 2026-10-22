<?php
/** Admin-only SPA-like event CRUD interface using the PHP JSON API. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
requireLogin('admin');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?=e((string)$_SESSION['csrf'])?>">
    <title>Lumiora Live Event Studio | AJAX Event Management</title>
    <link rel="stylesheet" href="live-event-studio.css">
    <script src="live-event-studio.js" defer></script>
</head>
<body>
<main class="shell">
    <nav class="top-nav" aria-label="Project navigation">
        <div class="brand"><span class="logo-mark">L</span><span><strong>Lumiora</strong><small>StudentHub · Admin Workspace</small></span></div>
        <div class="top-links">
            <a href="admin-dashboard.php">Admin Dashboard</a>
            <a href="event-management.php">Poster Management</a>
            <a href="event-audit-log.php">Audit Log</a>
        </div>
    </nav>

    <section class="hero" aria-labelledby="page-title">
        <div>
            <span class="eyebrow"><span class="live-dot" aria-hidden="true"></span> Fetch API + MySQLi · Live workspace</span>
            <h1 id="page-title">Live Event Studio</h1>
            <p>Create, update, search and archive campus events instantly. Every action sends a JSON request without reloading this page.</p>
            <div class="hero-actions">
                <button id="add-event" type="button" class="primary">+ Add New Event</button>
                <button id="reload-events" type="button" class="outline">↻ Refresh List</button>
            </div>
        </div>
        <div class="hero-graphic" aria-hidden="true"><div class="circle circle-a"></div><div class="circle circle-b"></div><div class="hero-card"><span>API</span><b>{ JSON }</b><small>GET · POST · PATCH · DELETE</small></div></div>
    </section>

    <section class="stats" aria-label="Event summary">
        <div class="stat-card"><span>Total Events</span><strong id="stat-total">—</strong><small>All records</small></div>
        <div class="stat-card"><span>Active Events</span><strong id="stat-active">—</strong><small>Available in dashboard</small></div>
        <div class="stat-card"><span>Open Registration</span><strong id="stat-open">—</strong><small>Accepting registrations</small></div>
        <div class="stat-card"><span>Archived Events</span><strong id="stat-archived">—</strong><small>Restorable records</small></div>
    </section>

    <div id="alert" class="notice" role="status" aria-live="polite" hidden></div>

    <section class="panel" aria-labelledby="event-list-heading">
        <div class="panel-heading"><div><h2 id="event-list-heading">Event Records</h2><p>Asynchronous search, category filter and pagination</p></div><span id="request-state" class="connection">Ready</span></div>
        <form id="filter-form" class="filters" role="search">
            <div class="field search-field"><label for="search">Search events or venues</label><input type="search" id="search" name="search" maxlength="100" placeholder="Search by name, venue or description" autocomplete="off"><small>Search updates after you stop typing</small></div>
            <div class="field"><label for="category">Category</label><select id="category" name="category"><option value="">All categories</option><option>Workshop</option><option>Technical</option><option>Cultural</option><option>Sports</option><option>Seminar</option><option>Other</option></select></div>
            <div class="field"><label for="status">Event Status</label><select id="status" name="status"><option value="all">Open &amp; Closed</option><option value="open">Open</option><option value="closed">Closed</option></select></div>
            <div class="field"><label for="archive">Record State</label><select id="archive" name="archive"><option value="active">Active</option><option value="archived">Archived</option></select></div>
            <button class="filter-reset" id="reset-filters" type="reset">Reset</button>
        </form>
        <div class="table-wrap" id="table-region" aria-live="polite" aria-busy="true">
            <table><thead><tr><th>Poster</th><th>Event / Venue</th><th>Category</th><th>Date</th><th>Registrations</th><th>Status</th><th>Actions</th></tr></thead><tbody id="event-rows"><tr><td colspan="7" class="empty">Loading events...</td></tr></tbody></table>
        </div>
        <div class="table-bottom"><p id="result-count" class="result-count">Loading...</p><div id="page-buttons" class="page-buttons" aria-label="Results pagination"></div></div>
        <p class="footnote">Delete uses safe archive (soft delete), keeping prior registrations intact. To upload or replace a poster, open <a href="event-management.php">Poster Management</a>.</p>
    </section>
    <footer class="site-footer">Lumiora Event Studio · Secure admin JSON API · Prepared MySQLi queries and CSRF validation</footer>
</main>

<dialog id="event-dialog" aria-labelledby="dialog-title">
    <form id="event-form" class="modal-form" novalidate>
        <div class="modal-head"><div><span class="eyebrow">Live Event Studio</span><h2 id="dialog-title">Add New Event</h2></div><button type="button" class="close-dialog" aria-label="Close event editor">×</button></div>
        <p class="modal-description">Save changes instantly with a secure JSON API request.</p>
        <input id="edit-id" name="event_id" type="hidden">
        <div class="form-grid">
            <div class="field"><label for="event_name">Event Name *</label><input id="event_name" name="event_name" minlength="3" maxlength="150" required placeholder="e.g. Cloud Computing Workshop"></div>
            <div class="field"><label for="event-category">Category *</label><select id="event-category" name="category" required><option value="Workshop">Workshop</option><option value="Technical">Technical</option><option value="Cultural">Cultural</option><option value="Sports">Sports</option><option value="Seminar">Seminar</option><option value="Other">Other</option></select></div>
            <div class="field"><label for="event_date">Event Date *</label><input id="event_date" name="event_date" type="date" min="2020-01-01" max="2100-12-31" required></div>
            <div class="field"><label for="venue">Venue *</label><input id="venue" name="venue" minlength="2" maxlength="120" required placeholder="e.g. Computer Lab 2"></div>
            <div class="field"><label for="event-status">Registration Status *</label><select id="event-status" name="event_status"><option value="open">Open</option><option value="closed">Closed</option></select></div>
            <div class="field full"><label for="description">Description</label><textarea id="description" name="description" rows="3" maxlength="2000" placeholder="Describe the event for students"></textarea></div>
        </div>
        <p id="form-error" role="alert" class="form-error" hidden></p>
        <div class="modal-actions"><button type="button" class="outline close-dialog">Cancel</button><button id="save-event" class="primary" type="submit">Save Event</button></div>
    </form>
</dialog>
</body>
</html>
