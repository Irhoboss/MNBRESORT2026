<?php
// Edit these for your server
$DB=['host'=>'localhost','name'=>'mnb_resort','user'=>'root','pass'=>''];
// getenv() takes the VARIABLE NAME (for example MNB_SMTP_HOST), not the SMTP value.
// On Windows, set the MNB_SMTP_* variables, then restart Apache. Keep the app password secret.
$MAIL=['host'=>getenv('MNB_SMTP_HOST')?:'smtp.gmail.com','port'=>(int)(getenv('MNB_SMTP_PORT')?:587),'encryption'=>getenv('MNB_SMTP_ENCRYPTION')?:'tls','username'=>getenv('MNB_SMTP_USER')?:'','password'=>getenv('MNB_SMTP_PASS')?:'','from_email'=>getenv('MNB_MAIL_FROM')?:getenv('MNB_SMTP_USER')?:'','from_name'=>getenv('MNB_MAIL_NAME')?:'MNB Resort'];
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']);
session_start();
date_default_timezone_set('Asia/Manila');
$pdo=new PDO("mysql:host={$DB['host']};dbname={$DB['name']};charset=utf8mb4",$DB['user'],$DB['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
