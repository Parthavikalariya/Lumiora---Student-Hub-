<?php
/** Server-owned role menus: never accept links or permissions from the browser. */
declare(strict_types=1);
function lumioraRoleMenu(string $role): array {
    $common = [
        ['title' => 'My Dashboard', 'href' => $role === 'admin' ? 'admin-dashboard.php' : 'student-dashboard.php', 'icon' => '▦', 'key' => 'dashboard'],
    ];
    if ($role === 'admin') {
        return array_merge($common, [
            ['title'=>'Student Management', 'href'=>'student-management.php', 'icon'=>'▤', 'key'=>'students'],
            ['title'=>'Event Management', 'href'=>'event-management.php', 'icon'=>'▣', 'key'=>'events'],
            ['title'=>'Live Event Studio', 'href'=>'live-event-studio.php', 'icon'=>'↗', 'key'=>'studio'],
            ['title'=>'Event Change Audit', 'href'=>'event-audit-log.php', 'icon'=>'◷', 'key'=>'event-audit'],
            ['title'=>'Admin Audit Logs', 'href'=>'admin-audit-logs.php', 'icon'=>'☷', 'key'=>'audit'],
            ['title'=>'Integration Center', 'href'=>'integration-center.php', 'icon'=>'✓', 'key'=>'integration'],
        ]);
    }
    return array_merge($common, [
        ['title'=>'Timetable', 'href'=>'student-timetable.php', 'icon'=>'▦', 'key'=>'timetable'],
        ['title'=>'Attendance', 'href'=>'student-attendance.php', 'icon'=>'◉', 'key'=>'attendance'],
        ['title'=>'Study Material', 'href'=>'student-resources.php', 'icon'=>'◫', 'key'=>'resources'],
        ['title'=>'My Profile', 'href'=>'student-profile.php', 'icon'=>'◎', 'key'=>'profile'],
    ]);
}
function renderLumioraSidebar(string $role, string $selected = 'dashboard'): void {
    echo '<aside class="sidebar" aria-label="Role-based navigation">';
    echo '<a class="brand" href="'.($role === 'admin' ? 'admin-dashboard.php' : 'student-dashboard.php').'">';
    echo '<span class="brand-icon">L</span><span><strong>Lumiora</strong><small>StudentHub · Control Center</small></span></a>';
    echo '<div class="menu-label">'.($role === 'admin' ? 'ADMIN WORKSPACE' : 'STUDENT WORKSPACE').'</div><nav class="side-nav">';
    foreach (lumioraRoleMenu($role) as $item) {
        $selectedClass = $item['key'] === $selected ? ' active' : '';
        echo '<a class="nav-link'.$selectedClass.'" href="'.htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8').'">';
        echo '<span class="nav-icon" aria-hidden="true">'.htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8').'</span>';
        echo '<span>'.htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8').'</span></a>';
    }
    echo '</nav><div class="sidebar-bottom"><span class="secure-dot"></span> Role protected · '.htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8').'</div></aside>';
}
