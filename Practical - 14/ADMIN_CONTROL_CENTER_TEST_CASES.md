# Practical 14 – Manual verification checklist

- [ ] Existing MySQL schema backed up or retained unchanged.
- [ ] `admin_control_schema.sql` imported in existing `studenthub_pr8_db`.
- [ ] Existing admin successfully signs in; dynamic menu shows admin modules.
- [ ] Counts match phpMyAdmin COUNT queries.
- [ ] Latest registrations are real database rows (or truthful empty state).
- [ ] Chart reflects actual last-six-month registrations (zero months supported).
- [ ] Student role sees only student menu; admin dashboard returns HTTP 403.
- [ ] One TEST student account can be disabled (never disable an admin).
- [ ] Disabled user cannot login or access protected pages.
- [ ] Same account re-enabled and login works again.
- [ ] `audit_logs` records actor, target, action, details, timestamp for both toggles.
- [ ] Previous Practical 11–13 modules still work and new admin mutations are logged.
- [ ] Browser back button cannot expose unauthorized protected dashboard data.

Expected schema: `user_accounts.is_active`, `audit_logs(log_id, admin_user_id, action, target_type, target_id, details, created_at)`.
