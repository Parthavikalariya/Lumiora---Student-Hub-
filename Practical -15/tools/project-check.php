<?php
/** CLI-only preflight: php tools/project-check.php [--db] */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
$root=dirname(__DIR__);
$needed=['portal.php','login.php','admin-dashboard.php','student-dashboard.php','student-management.php','event-management.php','event-api.php','live-event-studio.php','admin-audit-logs.php','integration-center.php','README.md','docs/TEST_REPORT.md'];
$fail=0;
echo "Lumiora Practical 15 preflight\nPHP ".PHP_VERSION."\n";
foreach($needed as $file) { $ok=is_file($root.'/'.$file); echo ($ok?'PASS':'FAIL')." file $file\n"; if(!$ok) $fail++; }
foreach(['mysqli','pdo_mysql','fileinfo','session'] as $ext){$ok=extension_loaded($ext);echo ($ok?'PASS':'FAIL')." extension $ext\n";if(!$ok)$fail++;}
if(in_array('--db',$argv,true)) {
    try {
        require_once $root.'/mysqli-db.php';
        $db=getMySQLiConnection();
        foreach(['students','events','event_registrations','user_accounts','audit_logs'] as $name){
            $q=$db->prepare('SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?');
            $q->bind_param('s',$name);$q->execute();$n=(int)$q->get_result()->fetch_assoc()['n'];$q->close();
            echo ($n===1?'PASS':'FAIL')." database table $name\n";if($n!==1)$fail++;
        }
        $db->close();
    } catch(Throwable $e){ echo "FAIL database connection (check XAMPP and environment)\n"; $fail++; }
}
echo $fail===0?"Preflight passed; browser testing still required.\n":"$fail problem(s) found.\n";
exit($fail===0?0:1);
