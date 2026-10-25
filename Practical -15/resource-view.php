<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
requireLogin('student');
$names=['oop'=>'Object-Oriented Programming','dm'=>'Discrete Mathematics','fdsa'=>'Fundamentals of Data Structures','wdf'=>'Web Development Fundamentals','fcn'=>'Fundamentals of Computer Networks','cpi'=>'Computer Programming'];
$topic=$_GET['topic']??'';
if (!is_string($topic) || !array_key_exists($topic,$names)) { http_response_code(404); exit('Resource not found.'); }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Study Resources | Lumiora</title><link rel="stylesheet" href="integration-style.css"></head><body><div class="portal-shell"><header class="portal-header"><a class="portal-brand" href="student-resources.php">Lumiora · Study Resources</a><nav><a href="student-dashboard.php">Student Dashboard</a></nav></header><main><section class="portal-hero"><div><p class="eyebrow">RESOURCE CATALOG</p><h1><?=e($names[$topic])?><span class="accent">.</span></h1><p>The original portal links to this subject, but a corresponding notes file was not included in the supplied project. No material is invented. Add instructor-approved files to publish actual downloads.</p><a class="button-primary" href="studymaterial.html">← Back to catalog</a></div></section></main></div></body></html>
