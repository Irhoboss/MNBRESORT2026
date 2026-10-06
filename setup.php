<?php // Run once in the browser, then DELETE this file.
require 'config.php';
foreach([['Resort Admin','admin@mnb.com','Admin','admin123'],['Front Desk Staff','staff@mnb.com','Staff','staff123']] as [$n,$e,$r,$p]){
 $pdo->prepare("INSERT IGNORE INTO users(full_name,email,user_role,password_hash) VALUES(?,?,?,?)")->execute([$n,$e,$r,password_hash($p,PASSWORD_DEFAULT)]);}
echo 'Done. admin@mnb.com / admin123 and staff@mnb.com / staff123 created. Delete setup.php now and change the passwords.';
