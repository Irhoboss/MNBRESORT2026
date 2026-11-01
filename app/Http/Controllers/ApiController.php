<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResortData;
use PDO;
use PDOException;

final class ApiController
{
    public function __invoke(Request $request)
    {
        require_once app_path('Support/api_helpers.php');

        global $pdo, $MAIL;
        $pdo = null;
        $MAIL = config('mnb.mail');
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params(['httponly' => true, 'secure' => $request->isSecure(), 'samesite' => 'Lax']);
            session_start();
        }
        date_default_timezone_set('Asia/Manila');
        $in = $request->json()->all() ?: [];
        $a = $request->query('a', '');
try{
 $pdo = ResortData::connection();
 switch($a){
case 'register':
 $n=clean($in['name']??'');$e=strtolower(clean($in['email']??''));$ph=clean($in['phone']??'',20);$pw=(string)($in['password']??'');
 if(empty($in['guardian_confirmed']))out(['error'=>'Age and policy acknowledgement is required'],422);
 if(!$n||!filter_var($e,FILTER_VALIDATE_EMAIL)||strlen($pw)<6)out(['error'=>'Complete all fields (password min. 6 characters)'],422);
 if(q("SELECT 1 FROM users WHERE email=?",[$e])->fetch())out(['error'=>'Email already registered'],422);
 $error=issueOtp($e,'register',null,$n,$ph,password_hash($pw,PASSWORD_DEFAULT));if($error)out(['error'=>$error],503);out(['otp_sent'=>1,'email'=>$e]);
case 'verifyregister':
 $e=strtolower(clean($in['email']??''));$check=validateOtp($e,'register',$in['code']??'');if(isset($check['error']))out(['error'=>$check['error']],422);$o=$check['row'];if(!$o['full_name']||!$o['password_hash'])out(['error'=>'Registration request expired. Start again.'],422);if(q("SELECT 1 FROM users WHERE email=?",[$e])->fetch())out(['error'=>'Email already registered'],422);$pdo->beginTransaction();q("INSERT INTO users(full_name,email,phone,password_hash) VALUES(?,?,?,?)",[$o['full_name'],$e,$o['phone'],$o['password_hash']]);$id=$pdo->lastInsertId();q("DELETE FROM email_otps WHERE email=? AND purpose='register'",[$e]);$pdo->commit();$safe=htmlspecialchars($o['full_name'],ENT_QUOTES,'UTF-8');sendEmail($e,'Welcome to MNB Resort','<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:28px;color:#20243a"><h2>Welcome to MNB Resort, '.$safe.'!</h2><p>Your account was created successfully. You can now log in to manage bookings, payments, and messages.</p><p>Thank you for choosing MNB Resort.</p></div>');loginAs($id);
case 'resetotp':
 $e=strtolower(clean($in['email']??''));if(filter_var($e,FILTER_VALIDATE_EMAIL)){$user=q("SELECT user_id FROM users WHERE email=?",[$e])->fetch();if($user){$error=issueOtp($e,'reset',$user['user_id']);if($error)out(['error'=>$error],503);}}out(['otp_sent'=>1]);
case 'resetpassword':
 $e=strtolower(clean($in['email']??''));$pw=(string)($in['password']??'');if(strlen($pw)<6)out(['error'=>'New password must be at least 6 characters'],422);$check=validateOtp($e,'reset',$in['code']??'');if(isset($check['error']))out(['error'=>$check['error']],422);$o=$check['row'];$pdo->beginTransaction();q("UPDATE users SET password_hash=? WHERE user_id=?",[password_hash($pw,PASSWORD_DEFAULT),$o['user_id']]);q("DELETE FROM email_otps WHERE email=? AND purpose='reset'",[$e]);$pdo->commit();out(['ok'=>1]);
case 'login':
 $u=q("SELECT * FROM users WHERE email=?",[strtolower(clean($in['email']??''))])->fetch();
 if(!$u||!password_verify((string)($in['password']??''),$u['password_hash']))out(['error'=>'Wrong email or password'],401);if(empty($u['is_active']))out(['error'=>'This account is inactive. Contact the resort administrator.'],403);
 loginAs($u['user_id']);
case 'logout': session_destroy(); out(['ok'=>1]);
case 'me':
 $u=$_SESSION['u']??null;if(!$u)out(['user'=>null]);
 if($u['user_role']=='Guest'&&!q("SELECT is_active FROM users WHERE user_id=?",[$u['user_id']])->fetchColumn()){session_destroy();out(['user'=>null]);}$n=$u['user_role']=='Guest'?q("SELECT COUNT(*) FROM messages WHERE thread_id=? AND sender_id<>? AND is_read=0 AND is_archived=0",[$u['user_id'],$u['user_id']])->fetchColumn():q("SELECT COUNT(*) FROM messages WHERE sender_id=thread_id AND is_read=0 AND is_archived=0")->fetchColumn();
 $nn=(int)q("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0",[$u['user_id']])->fetchColumn();out(['user'=>$u,'unread'=>(int)$n,'notif_unread'=>$nn]);
case 'avail':
 $ci=$_GET['ci']??'';$co=$_GET['co']??'';if(!dt($ci)||!dt($co)||$co<=$ci)out(['error'=>'Invalid dates'],422);
 $rs=q("SELECT * FROM accommodations WHERE is_available=1 AND is_archived=0 ORDER BY rate_per_night")->fetchAll();
 foreach($rs as &$r){$r['free']=free($r['room_id'],$ci,$co);$r['is_favorite']=($_SESSION['u']['user_role']??'')=='Guest'?(int)q("SELECT COUNT(*) FROM favorites WHERE user_id=? AND room_id=?",[$_SESSION['u']['user_id'],$r['room_id']])->fetchColumn():0;}
 out($rs);
case 'rooms': need('Staff','Admin');$arch=(int)($_GET['arch']??0);out(q("SELECT * FROM accommodations WHERE is_archived=? ORDER BY room_id",[$arch])->fetchAll());
case 'saveroom':
 need('Staff','Admin');$id=(int)($in['id']??0);
 $v=[clean($in['name']??'',50),clean($in['category']??'',50),clean($in['description']??'',500),clean($in['amenities']??'',500),max(1,(int)($in['capacity']??1)),max(0,(float)($in['rate']??0)),empty($in['open'])?0:1];
 if(!$v[0])out(['error'=>'Room name required'],422);
 if($id)q("UPDATE accommodations SET room_name=?,category=?,description=?,amenities=?,capacity=?,rate_per_night=?,is_available=? WHERE room_id=?",[...$v,$id]);
 else{q("INSERT INTO accommodations(room_name,category,description,amenities,capacity,rate_per_night,is_available) VALUES(?,?,?,?,?,?,?)",$v);$id=$pdo->lastInsertId();}
 audit($id?'Room updated':'Room added',"Room #$id · ".$v[0]);
 if(array_key_exists('photos',$in)||!empty($in['photo'])){$photoError=savePhoto($id,$in['photos']??$in['photo']);if($photoError)out(['error'=>$photoError],422);}
 out(['ok'=>1]);
case 'archiveroom':
 need('Staff','Admin');$id=(int)($in['id']??0);$arch=empty($in['arch'])?0:1;if(!q("SELECT 1 FROM accommodations WHERE room_id=?",[$id])->fetch())out(['error'=>'Room not found'],404);q("UPDATE accommodations SET is_archived=? WHERE room_id=?",[$arch,$id]);audit($arch?'Room archived':'Room restored',"Room #$id");out(['ok'=>1]);
case 'delroom':
 need('Staff','Admin');$id=(int)($in['id']??0);if(q("SELECT 1 FROM reservations WHERE room_id=? LIMIT 1",[$id])->fetch())out(['error'=>'This room has booking history. Archive it instead.'],409);q("DELETE FROM accommodations WHERE room_id=?",[$id]);audit('Room deleted',"Room #$id");out(['ok'=>1]);
case 'reserve':
 $u=need('Guest');$room=(int)($in['room']??0);$ci=$in['ci']??'';$co=$in['co']??'';$g=(int)($in['guests']??0);
 if(!dt($ci)||!dt($co)||$co<=$ci||$ci<date('Y-m-d'))out(['error'=>'Invalid dates'],422);
 $pdo->beginTransaction();
 $r=q("SELECT * FROM accommodations WHERE room_id=? AND is_available=1 AND is_archived=0 FOR UPDATE",[$room])->fetch();
 if(!$r||$g<1||$g>$r['capacity']){$pdo->rollBack();out(['error'=>'Room not available for that many guests'],422);}
 if(!free($room,$ci,$co)){$pdo->rollBack();out(['error'=>'Sorry, that room was just booked for those dates'],409);}
 q("INSERT INTO reservations(guest_id,room_id,check_in_date,check_out_date,guest_count) VALUES(?,?,?,?,?)",[$u['user_id'],$room,$ci,$co,$g]);$rid=$pdo->lastInsertId();$pdo->commit();notify($u['user_id'],'Booking request received',"Your booking request #$rid is pending resort confirmation.",'booking',$rid);out(['ok'=>1]);
case 'mine':
 $u=need('Guest');out(q("SELECT r.*,a.room_name,a.rate_per_night,DATEDIFF(check_out_date,check_in_date) nights,(SELECT COALESCE(SUM(CASE WHEN p.transaction_type='Payment' THEN p.amount ELSE -p.amount END),0) FROM payments p WHERE p.reservation_id=r.reservation_id AND p.status='Approved') amount_paid FROM reservations r JOIN accommodations a ON a.room_id=r.room_id WHERE guest_id=? ORDER BY check_in_date DESC",[$u['user_id']])->fetchAll());
case 'cancel':
 $u=need('Guest');$rid=(int)$in['id'];$changed=q("UPDATE reservations SET status='Cancelled' WHERE reservation_id=? AND guest_id=? AND status IN ('Pending','Confirmed') AND payment_status='Unpaid'",[$rid,$u['user_id']]);if(!$changed->rowCount())out(['error'=>'This booking can no longer be cancelled online. Please contact the resort.'],422);notify($u['user_id'],'Booking cancelled',"Booking #$rid was cancelled.",'booking',$rid);out(['ok'=>1]);
case 'list':
 need('Staff','Admin');$w=['1'];$p=[];
 if(!empty($_GET['st'])){$w[]='r.status=?';$p[]=$_GET['st'];}
 if(($_GET['payment']??'')==='paid')$w[]="r.payment_status='Paid'";
 elseif(($_GET['payment']??'')==='unpaid')$w[]="r.payment_status IN ('Unpaid','Partial')";
 if(!empty($_GET['act']))$w[]="r.status IN ".ACTIVE;
 if(!empty($_GET['s'])){$w[]='(u.full_name LIKE ? OR a.room_name LIKE ? OR r.reservation_id=?)';$l='%'.$_GET['s'].'%';array_push($p,$l,$l,(int)$_GET['s']);}
 if(!empty($_GET['from'])&&!empty($_GET['to'])){$w[]='r.check_in_date<=? AND r.check_out_date>?';array_push($p,$_GET['to'],$_GET['from']);}
 $rows=q("SELECT r.*,u.full_name,u.phone,a.room_name,a.rate_per_night,DATEDIFF(r.check_out_date,r.check_in_date) nights,(SELECT COALESCE(SUM(CASE WHEN p.transaction_type='Payment' THEN p.amount ELSE -p.amount END),0) FROM payments p WHERE p.reservation_id=r.reservation_id AND p.status='Approved') amount_paid FROM reservations r JOIN users u ON u.user_id=r.guest_id JOIN accommodations a ON a.room_id=r.room_id WHERE ".implode(' AND ',$w)." ORDER BY r.created_at DESC LIMIT 300",$p)->fetchAll();foreach($rows as &$row)$row['checkout_overdue']=$row['status']==='Checked-In'&&checkoutOverdue($row['check_out_date']);unset($row);out($rows);
case 'setstatus':
 need('Staff','Admin');$id=(int)$in['id'];$st=$in['st']??'';
 $ok=['Confirmed'=>['Pending'],'Cancelled'=>['Pending','Confirmed'],'Checked-In'=>['Confirmed'],'Completed'=>['Checked-In']];
 $r=q("SELECT status,guest_id,payment_status,qr_code_hash FROM reservations WHERE reservation_id=?",[$id])->fetch();
 if(!$r||!isset($ok[$st])||!in_array($r['status'],$ok[$st]))out(['error'=>'Action not allowed for current status'],422);
 if($st==='Completed'){$tot=(float)q("SELECT DATEDIFF(check_out_date,check_in_date)*a.rate_per_night FROM reservations r JOIN accommodations a ON a.room_id=r.room_id WHERE reservation_id=?",[$id])->fetchColumn();$paid=(float)q("SELECT COALESCE(SUM(CASE WHEN transaction_type='Payment' THEN amount ELSE -amount END),0) FROM payments WHERE reservation_id=? AND status='Approved'",[$id])->fetchColumn();if($paid+0.005<$tot)out(['error'=>'Record the full payment before checking out.'],422);}
 if($st=='Confirmed')q("UPDATE reservations SET status=?,qr_code_hash=COALESCE(qr_code_hash,?) WHERE reservation_id=?",[$st,bin2hex(random_bytes(32)),$id]);
 elseif($st=='Checked-In')q("UPDATE reservations SET status=?,qr_code_hash=COALESCE(qr_code_hash,?) WHERE reservation_id=?",[$st,bin2hex(random_bytes(32)),$id]);
 else q("UPDATE reservations SET status=?,qr_code_hash=NULL WHERE reservation_id=?",[$st,$id]);
 if($st==='Completed')q("UPDATE reservations SET payment_status='Paid' WHERE reservation_id=?",[$id]);
 audit('Booking '.$st,"Booking #$id");notify($r['guest_id'],'Booking '.$st,"Your booking #$id is now $st.",'booking',$id);
 out(['ok'=>1]);
case 'scan':
 need('Staff','Admin');
 $code=clean($in['code']??'',255);
 $r=q("SELECT r.*,u.full_name,u.phone,a.room_name,DATEDIFF(r.check_out_date,r.check_in_date)*a.rate_per_night total,(SELECT COALESCE(SUM(CASE WHEN p.transaction_type='Payment' THEN p.amount ELSE -p.amount END),0) FROM payments p WHERE p.reservation_id=r.reservation_id AND p.status='Approved') amount_paid FROM reservations r JOIN users u ON u.user_id=r.guest_id JOIN accommodations a ON a.room_id=r.room_id WHERE qr_code_hash=?",[$code])->fetch();
 if(!$r||!in_array($r['status'],['Confirmed','Checked-In'],true))out(['error'=>'Invalid, expired, or already used QR code'],404);
 $r['next_action']=$r['status']=='Confirmed'?'Check-In':'Check-Out';
 $r['remaining_balance']=max(0,(float)$r['total']-(float)$r['amount_paid']);$r['checkout_overdue']=$r['status']==='Checked-In'&&checkoutOverdue($r['check_out_date']);
 $checkoutTime=q("SELECT setting_value FROM resort_settings WHERE setting_key='check_out_time'")->fetchColumn()?:'12:00';$r['checkout_due_at']=$r['check_out_date'].' '.$checkoutTime;
 out($r);
case 'checkqr':
 need('Staff','Admin');$code=clean($in['code']??'',255);$pdo->beginTransaction();
 $r=q("SELECT r.*,u.full_name,u.phone,a.room_name,DATEDIFF(r.check_out_date,r.check_in_date)*a.rate_per_night total FROM reservations r JOIN users u ON u.user_id=r.guest_id JOIN accommodations a ON a.room_id=r.room_id WHERE qr_code_hash=? FOR UPDATE",[$code])->fetch();
 if(!$r||!in_array($r['status'],['Confirmed','Checked-In'],true)){$pdo->rollBack();out(['error'=>'This QR was already used or has expired.'],409);}
 if($r['status']==='Checked-In'){$paid=(float)q("SELECT COALESCE(SUM(CASE WHEN transaction_type='Payment' THEN amount ELSE -amount END),0) FROM payments WHERE reservation_id=? AND status='Approved'",[$r['reservation_id']])->fetchColumn();if($paid+0.005<(float)$r['total']||$r['payment_status']!=='Paid'){$pdo->rollBack();out(['error'=>'Checkout is blocked until the full balance of '.number_format(max(0,(float)$r['total']-$paid),2).' is paid and approved.'],422);}}
 $next=$r['status']=='Confirmed'?'Checked-In':'Completed';$nextCode=$next=='Checked-In'?$code:null;
 q("UPDATE reservations SET status=?,qr_code_hash=?,payment_status=IF(?='Completed','Paid',payment_status) WHERE reservation_id=? AND qr_code_hash=?",[$next,$nextCode,$next,$r['reservation_id'],$code]);$r['status']=$next;$r['qr_code_hash']=$nextCode;if($next==='Completed')$r['payment_status']='Paid';$pdo->commit();notify($r['guest_id'],'Booking '.$next,"Your booking #{$r['reservation_id']} is now $next.",'booking',$r['reservation_id']);out($r);
case 'stats':
 need('Staff','Admin');$t=date('Y-m-d');
 out(['pending'=>q("SELECT COUNT(*) FROM reservations WHERE status='Pending'")->fetchColumn(),'arrivals'=>q("SELECT COUNT(*) FROM reservations WHERE status='Confirmed' AND check_in_date=?",[$t])->fetchColumn(),'inhouse'=>q("SELECT COUNT(*) FROM reservations WHERE status='Checked-In'")->fetchColumn(),'rooms'=>q("SELECT COUNT(*) FROM accommodations WHERE is_available=1")->fetchColumn(),'guests'=>q("SELECT COUNT(*) FROM users WHERE user_role='Guest'")->fetchColumn()]);
case 'threads':
 need('Staff','Admin');
 $arch=(int)($_GET['arch']??0);out(q("SELECT u.user_id,u.full_name,(SELECT message_text FROM messages WHERE thread_id=u.user_id AND is_archived=? ORDER BY message_id DESC LIMIT 1) last,(SELECT sent_at FROM messages WHERE thread_id=u.user_id AND is_archived=? ORDER BY message_id DESC LIMIT 1) last_at,(SELECT COUNT(*) FROM messages WHERE thread_id=u.user_id AND sender_id=u.user_id AND is_read=0 AND is_archived=0) unread FROM users u WHERE EXISTS(SELECT 1 FROM messages WHERE thread_id=u.user_id AND is_archived=?) ORDER BY last_at DESC",[$arch,$arch,$arch])->fetchAll());
case 'msgs':
 $u=need();$guest=$u['user_role']=='Guest';$g=$guest?$u['user_id']:(int)($_GET['g']??0);
 $arch=(int)($_GET['arch']??0);q($guest?"UPDATE messages SET is_read=1 WHERE thread_id=? AND sender_id<>? AND is_archived=0":"UPDATE messages SET is_read=1 WHERE thread_id=? AND sender_id=? AND is_archived=0",[$g,$g]);
 out(['name'=>q("SELECT full_name FROM users WHERE user_id=?",[$g])->fetchColumn(),'rows'=>q("SELECT m.*,s.full_name FROM messages m JOIN users s ON s.user_id=m.sender_id WHERE thread_id=? AND is_archived=? ORDER BY message_id",[$g,$arch])->fetchAll()]);
case 'send':
 $u=need();$g=$u['user_role']=='Guest'?$u['user_id']:(int)($in['g']??0);$t=clean($in['text']??'',1000);
 if(!$g||!$t)out(['error'=>'Empty message'],422);
 $firstGuestMessage=$u['user_role']=='Guest'&&!q("SELECT 1 FROM messages WHERE thread_id=? LIMIT 1",[$g])->fetch();
 q("INSERT INTO messages(thread_id,sender_id,message_text) VALUES(?,?,?)",[$g,$u['user_id'],$t]);
 if($firstGuestMessage){$staff=q("SELECT user_id FROM users WHERE user_role IN ('Staff','Admin') ORDER BY CASE user_role WHEN 'Staff' THEN 0 ELSE 1 END,user_id LIMIT 1")->fetchColumn();if($staff)q("INSERT INTO messages(thread_id,sender_id,message_text) VALUES(?,?,?)",[$g,$staff,'Hello and welcome to MNB Resort! How can we help you today?']);}
 out(['ok'=>1]);
case 'archivethread':
 need('Staff','Admin');$g=(int)($in['g']??0);$arch=empty($in['arch'])?0:1;if(!q("SELECT 1 FROM messages WHERE thread_id=? LIMIT 1",[$g])->fetch())out(['error'=>'Conversation not found'],404);q("UPDATE messages SET is_archived=? WHERE thread_id=?",[$arch,$g]);out(['ok'=>1]);
case 'delthread':
 need('Staff','Admin');$g=(int)($in['g']??0);q("DELETE FROM messages WHERE thread_id=?",[$g]);out(['ok'=>1]);
case 'report':
 need('Staff','Admin');$p=$_GET['p']??'day';$d=(string)($_GET['d']??date('Y-m-d'));
 $f=['day'=>['%Y-%m-%d',10],'month'=>['%Y-%m',7],'year'=>['%Y',4]][$p]??['%Y-%m-%d',10];
 out(q("SELECT r.*,u.full_name,u.phone,a.room_name,a.rate_per_night,DATEDIFF(r.check_out_date,r.check_in_date) nights FROM reservations r JOIN users u ON u.user_id=r.guest_id JOIN accommodations a ON a.room_id=r.room_id WHERE DATE_FORMAT(r.check_in_date,?)=? ORDER BY r.check_in_date",[$f[0],substr($d,0,$f[1])])->fetchAll());
case 'notifications':
 $u=need();q("INSERT INTO notifications(user_id,title,message,notification_type,reservation_id) SELECT r.guest_id,'Upcoming reservation reminder',CONCAT('Booking #',r.reservation_id,' check-in is on ',DATE_FORMAT(r.check_in_date,'%b %e, %Y'),'.'), 'reminder',r.reservation_id FROM reservations r WHERE r.status IN ('Confirmed','Checked-In') AND r.check_in_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 3 DAY) AND NOT EXISTS(SELECT 1 FROM notifications n WHERE n.user_id=r.guest_id AND n.reservation_id=r.reservation_id AND n.notification_type='reminder')");out(q("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100",[$u['user_id']])->fetchAll());
case 'readnotifications':
 $u=need();q("UPDATE notifications SET is_read=1 WHERE user_id=?",[$u['user_id']]);out(['ok'=>1]);
case 'announce':
 need('Staff','Admin');$title=clean($in['title']??'',120);$message=clean($in['message']??'',500);if(!$title||!$message)out(['error'=>'Announcement title and message are required'],422);$guests=q("SELECT user_id FROM users WHERE user_role='Guest' AND is_active=1")->fetchAll(PDO::FETCH_COLUMN);foreach($guests as $uid)notify($uid,$title,$message,'announcement');audit('Announcement sent',$title);out(['ok'=>1,'count'=>count($guests)]);
case 'favorites':
 $u=need('Guest');out(q("SELECT a.* FROM favorites f JOIN accommodations a ON a.room_id=f.room_id WHERE f.user_id=? AND a.is_archived=0 ORDER BY f.created_at DESC",[$u['user_id']])->fetchAll());
case 'togglefavorite':
 $u=need('Guest');$room=(int)($in['room']??0);$r=q("SELECT room_id FROM accommodations WHERE room_id=? AND is_archived=0",[$room])->fetchColumn();if(!$r)out(['error'=>'Room is unavailable'],404);$has=q("SELECT 1 FROM favorites WHERE user_id=? AND room_id=?",[$u['user_id'],$room])->fetchColumn();if($has){q("DELETE FROM favorites WHERE user_id=? AND room_id=?",[$u['user_id'],$room]);out(['saved'=>0]);}q("INSERT INTO favorites(user_id,room_id) VALUES(?,?)",[$u['user_id'],$room]);out(['saved'=>1]);
case 'payments':
 $u=need('Guest');out(['history'=>q("SELECT p.*,r.check_in_date,r.check_out_date,a.room_name FROM payments p JOIN reservations r ON r.reservation_id=p.reservation_id JOIN accommodations a ON a.room_id=r.room_id WHERE r.guest_id=? ORDER BY p.paid_at DESC",[$u['user_id']])->fetchAll(),'bookings'=>q("SELECT r.reservation_id,r.status,r.payment_status,r.check_in_date,r.check_out_date,a.room_name,DATEDIFF(r.check_out_date,r.check_in_date)*a.rate_per_night total,(SELECT COALESCE(SUM(CASE WHEN p.transaction_type='Payment' THEN p.amount ELSE -p.amount END),0) FROM payments p WHERE p.reservation_id=r.reservation_id AND p.status='Approved') amount_paid FROM reservations r JOIN accommodations a ON a.room_id=r.room_id WHERE r.guest_id=? AND r.status<>'Cancelled' ORDER BY r.check_in_date DESC",[$u['user_id']])->fetchAll()]);
case 'recordpayment':
 need('Staff','Admin');$rid=(int)($in['reservation_id']??0);$amount=(float)($in['amount']??0);$method=clean($in['method']??'',40);$state=$in['payment_status']??'Paid';if(!in_array($state,['Paid','Unpaid'],true))out(['error'=>'Choose Paid or Unpaid'],422);$pdo->beginTransaction();$r=q("SELECT r.status,r.guest_id,DATEDIFF(r.check_out_date,r.check_in_date)*a.rate_per_night total FROM reservations r JOIN accommodations a ON a.room_id=r.room_id WHERE r.reservation_id=? FOR UPDATE",[$rid])->fetch();if(!$r){$pdo->rollBack();out(['error'=>'Booking not found'],404);}if($r['status']!=='Checked-In'){$pdo->rollBack();out(['error'=>'Payments can only be recorded after check-in.'],422);}$paid=(float)q("SELECT COALESCE(SUM(CASE WHEN transaction_type='Payment' THEN amount ELSE -amount END),0) FROM payments WHERE reservation_id=? AND status='Approved'",[$rid])->fetchColumn();$remaining=max(0,(float)$r['total']-$paid);if($state==='Unpaid'){if($paid>0.005){$pdo->rollBack();out(['error'=>'A booking with recorded payments cannot be changed to Unpaid. Record a refund instead.'],422);}q("UPDATE reservations SET payment_status='Unpaid' WHERE reservation_id=?",[$rid]);$pdo->commit();out(['ok'=>1,'payment_status'=>'Unpaid','amount_paid'=>0]);}if($amount<=0||abs($amount-$remaining)>0.005||!in_array($method,['Cash','Bank transfer','GCash','Card','Other'],true)){$pdo->rollBack();out(['error'=>'Enter the full remaining amount and a valid payment method'],422);}q("INSERT INTO payments(reservation_id,amount,payment_method,transaction_type,status,recorded_by) VALUES(?,?,?,'Payment','Approved',?)",[$rid,$amount,$method,$_SESSION['u']['user_id']]);$new=$paid+$amount;$state=$new>=((float)$r['total'])?'Paid':'Partial';q("UPDATE reservations SET payment_status=? WHERE reservation_id=?",[$state,$rid]);$pdo->commit();audit('Payment recorded',"Booking #$rid · ".number_format($amount,2)." · $method");notify($r['guest_id'],'Payment received',"We recorded ".number_format($amount,2)." for booking #$rid via $method. Payment status: $state.",'payment',$rid);out(['ok'=>1,'payment_status'=>$state,'amount_paid'=>$new]);
case 'adminpayments':
 need('Admin');out(q("SELECT p.*,r.guest_id,r.status booking_status,u.full_name,a.room_name,r.check_in_date,r.check_out_date FROM payments p JOIN reservations r ON r.reservation_id=p.reservation_id JOIN users u ON u.user_id=r.guest_id JOIN accommodations a ON a.room_id=r.room_id ORDER BY (p.status='Pending') DESC,p.paid_at DESC LIMIT 500")->fetchAll());
case 'reviewpayment':
 $u=need('Admin');$id=(int)($in['id']??0);$state=$in['status']??'';if(!in_array($state,['Approved','Rejected'],true))out(['error'=>'Choose approve or reject'],422);$pdo->beginTransaction();$p=q("SELECT p.*,r.guest_id,r.status booking_status FROM payments p JOIN reservations r ON r.reservation_id=p.reservation_id WHERE p.payment_id=? FOR UPDATE",[$id])->fetch();if(!$p||$p['status']!='Pending'){$pdo->rollBack();out(['error'=>'This transaction is no longer pending'],409);}if($state==='Approved'&&$p['transaction_type']==='Payment'&&$p['booking_status']!=='Checked-In'){$pdo->rollBack();out(['error'=>'Payment proofs can only be approved after check-in.'],422);}q("UPDATE payments SET status=?,recorded_by=? WHERE payment_id=?",[$state,$u['user_id'],$id]);$net=(float)q("SELECT COALESCE(SUM(CASE WHEN transaction_type='Payment' THEN amount ELSE -amount END),0) FROM payments WHERE reservation_id=? AND status='Approved'",[$p['reservation_id']])->fetchColumn();$total=(float)q("SELECT DATEDIFF(r.check_out_date,r.check_in_date)*a.rate_per_night FROM reservations r JOIN accommodations a ON a.room_id=r.room_id WHERE r.reservation_id=?",[$p['reservation_id']])->fetchColumn();$paystate=$net<=0?'Unpaid':($net+0.005>=$total?'Paid':'Partial');q("UPDATE reservations SET payment_status=? WHERE reservation_id=?",[$paystate,$p['reservation_id']]);$pdo->commit();audit('Payment '.$state,"Transaction #$id · Booking #{$p['reservation_id']}");if($state=='Approved'&&$p['transaction_type']=='Payment')notify($p['guest_id'],'Payment received',"Payment #$id for booking #{$p['reservation_id']} was approved.",'payment',$p['reservation_id']);out(['ok'=>1]);
case 'createrefund':
 $u=need('Admin');$id=(int)($in['payment_id']??0);$amount=(float)($in['amount']??0);$p=q("SELECT p.reservation_id,r.guest_id FROM payments p JOIN reservations r ON r.reservation_id=p.reservation_id WHERE p.payment_id=? AND p.transaction_type='Payment' AND p.status='Approved'",[$id])->fetch();if(!$p||$amount<=0)out(['error'=>'Choose a valid approved payment and refund amount'],422);$refunded=(float)q("SELECT COALESCE(SUM(amount),0) FROM payments WHERE reservation_id=? AND transaction_type='Refund' AND status IN ('Pending','Approved')",[$p['reservation_id']])->fetchColumn();$received=(float)q("SELECT COALESCE(SUM(amount),0) FROM payments WHERE reservation_id=? AND transaction_type='Payment' AND status='Approved'",[$p['reservation_id']])->fetchColumn();if($amount>$received-$refunded+.005)out(['error'=>'Refund exceeds the refundable amount'],422);q("INSERT INTO payments(reservation_id,amount,payment_method,transaction_type,status,recorded_by) VALUES(?,?, 'Refund','Refund','Pending',?)",[$p['reservation_id'],$amount,$u['user_id']]);$rid=$pdo->lastInsertId();audit('Refund requested',"Refund #$rid · Booking #{$p['reservation_id']} · ".number_format($amount,2));out(['ok'=>1]);
case 'reviews':
 $u=need();$rows=q("SELECT v.*,u.full_name,a.room_name FROM reviews v JOIN users u ON u.user_id=v.guest_id JOIN accommodations a ON a.room_id=v.room_id ".($u['user_role']=='Admin'?'':'WHERE v.is_hidden=0 ')."ORDER BY v.created_at DESC")->fetchAll();$mine=$u['user_role']=='Guest'?q("SELECT r.reservation_id,r.check_in_date,r.check_out_date,a.room_name,a.room_id FROM reservations r JOIN accommodations a ON a.room_id=r.room_id LEFT JOIN reviews v ON v.reservation_id=r.reservation_id WHERE r.guest_id=? AND r.status='Completed' AND v.review_id IS NULL ORDER BY r.check_out_date DESC",[$u['user_id']])->fetchAll():[];out(['reviews'=>$rows,'eligible'=>$mine]);
case 'submitreview':
 $u=need('Guest');$rid=(int)($in['reservation_id']??0);$rating=(int)($in['rating']??0);$body=clean($in['review']??'',1000);$r=q("SELECT room_id FROM reservations WHERE reservation_id=? AND guest_id=? AND status='Completed'",[$rid,$u['user_id']])->fetch();if(!$r||$rating<1||$rating>5||!$body)out(['error'=>'A completed stay, 1–5 star rating, and review text are required'],422);q("INSERT INTO reviews(reservation_id,guest_id,room_id,rating,review_text) VALUES(?,?,?,?,?)",[$rid,$u['user_id'],$r['room_id'],$rating,$body]);out(['ok'=>1]);
case 'reviewaction':
 need('Admin');$id=(int)($in['id']??0);$action=$in['action']??'';if($action=='hide'||$action=='show'){q("UPDATE reviews SET is_hidden=? WHERE review_id=?",[$action=='hide'?1:0,$id]);audit('Review '.$action,"Review #$id");}elseif($action=='reply'){$reply=clean($in['reply']??'',1000);if(!$reply)out(['error'=>'Reply cannot be empty'],422);q("UPDATE reviews SET admin_reply=? WHERE review_id=?",[$reply,$id]);audit('Review reply added',"Review #$id");}else out(['error'=>'Invalid review action'],422);out(['ok'=>1]);
case 'adminsummary':
 need('Admin');$open=(int)q("SELECT COUNT(*) FROM accommodations WHERE is_available=1 AND is_archived=0")->fetchColumn();$inside=(int)q("SELECT COUNT(*) FROM reservations WHERE status='Checked-In'")->fetchColumn();out(['users'=>(int)q("SELECT COUNT(*) FROM users WHERE user_role='Guest'")->fetchColumn(),'bookings'=>(int)q("SELECT COUNT(*) FROM reservations")->fetchColumn(),'revenue'=>(float)q("SELECT COALESCE(SUM(CASE WHEN transaction_type='Payment' THEN amount ELSE -amount END),0) FROM payments WHERE status='Approved'")->fetchColumn(),'pending'=>(int)q("SELECT COUNT(*) FROM reservations WHERE status='Pending'")->fetchColumn(),'today'=>(int)q("SELECT COUNT(*) FROM reservations WHERE check_in_date=CURDATE()")->fetchColumn(),'upcoming'=>q("SELECT r.reservation_id,r.check_in_date,u.full_name,a.room_name FROM reservations r JOIN users u ON u.user_id=r.guest_id JOIN accommodations a ON a.room_id=r.room_id WHERE r.status='Confirmed' AND r.check_in_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY) ORDER BY r.check_in_date LIMIT 8")->fetchAll(),'pending_payments'=>(int)q("SELECT COUNT(*) FROM reservations WHERE status IN ('Pending','Confirmed','Checked-In') AND payment_status IN ('Unpaid','Partial')")->fetchColumn(),'payment_proofs'=>(int)q("SELECT COUNT(*) FROM payments WHERE status='Pending' AND transaction_type='Payment'")->fetchColumn(),'rooms'=>$open,'occupied'=>$inside,'available'=>max(0,$open-$inside),'recent'=>q("SELECT x.*,u.full_name FROM admin_activity x LEFT JOIN users u ON u.user_id=x.user_id ORDER BY x.activity_id DESC LIMIT 8")->fetchAll()]);
case 'packages':
 $u=need();out(q("SELECT * FROM packages ".($u['user_role']=='Admin'?'':'WHERE is_active=1')." ORDER BY item_type,name")->fetchAll());
case 'savepackage':
 need('Admin');$id=(int)($in['id']??0);$type=in_array($in['type']??'',['Package','Add-on'],true)?$in['type']:'Package';$name=clean($in['name']??'',100);$desc=clean($in['description']??'',500);$price=max(0,(float)($in['price']??0));if(!$name)out(['error'=>'Package or add-on name required'],422);if($id)q("UPDATE packages SET item_type=?,name=?,description=?,price=? WHERE package_id=?",[$type,$name,$desc,$price,$id]);else{$s=q("INSERT INTO packages(item_type,name,description,price) VALUES(?,?,?,?)",[$type,$name,$desc,$price]);$id=$pdo->lastInsertId();}audit($id?'Package updated':'Package added',"$type · $name");out(['ok'=>1]);
case 'packageactive':
 need('Admin');$id=(int)($in['id']??0);$active=empty($in['active'])?0:1;q("UPDATE packages SET is_active=? WHERE package_id=?",[$active,$id]);audit($active?'Package activated':'Package disabled',"Package #$id");out(['ok'=>1]);
case 'settings':
 need();$defaults=['resort_name'=>'MNB Resort','resort_logo'=>'','contact_number'=>'','contact_email'=>'','address'=>'Papaya, Nasugbu, Batangas','check_in_time'=>'14:00','check_out_time'=>'12:00','cancellation_policy'=>'Unpaid bookings may be cancelled before check-in. Contact the resort for paid bookings.','payment_methods'=>'Cash, GCash, Bank transfer'];$saved=q("SELECT setting_key,setting_value FROM resort_settings")->fetchAll();foreach($saved as $s)$defaults[$s['setting_key']]=$s['setting_value'];out($defaults);
case 'savesettings':
 need('Admin');$keys=['resort_name','resort_logo','contact_number','contact_email','address','check_in_time','check_out_time','cancellation_policy','payment_methods'];foreach($keys as $k){$v=clean($in[$k]??'',1000);q("INSERT INTO resort_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)",[$k,$v]);}audit('Resort settings updated','General resort profile and policies');out(['ok'=>1]);
case 'activity':
 need('Admin');out(q("SELECT x.*,u.full_name FROM admin_activity x LEFT JOIN users u ON u.user_id=x.user_id ORDER BY x.activity_id DESC LIMIT 300")->fetchAll());
case 'submitpayment':
 $u=need('Guest');$rid=(int)($in['reservation_id']??0);$amount=(float)($in['amount']??0);$method=clean($in['method']??'',40);$proof=$in['proof']??'';$r=q("SELECT r.guest_id,r.status,DATEDIFF(r.check_out_date,r.check_in_date)*a.rate_per_night total FROM reservations r JOIN accommodations a ON a.room_id=r.room_id WHERE r.reservation_id=? AND r.guest_id=?",[$rid,$u['user_id']])->fetch();if(!$r||$r['status']!=='Checked-In'||$amount<=0||!in_array($method,['GCash','Bank transfer','Cash','Other'],true))out(['error'=>'Payment proof can only be submitted after check-in. Enter a valid amount and payment method.'],422);if(!preg_match('#^data:image/(jpeg|png|webp);base64,(.+)$#s',$proof,$m))out(['error'=>'Upload a JPEG, PNG, or WebP payment proof'],422);$bytes=base64_decode($m[2],true);if(!$bytes||strlen($bytes)>3e6||!getimagesizefromstring($bytes))out(['error'=>'Payment proof must be a valid image under 3 MB'],422);$paid=(float)q("SELECT COALESCE(SUM(CASE WHEN transaction_type='Payment' THEN amount ELSE -amount END),0) FROM payments WHERE reservation_id=? AND status='Approved'",[$rid])->fetchColumn();$waiting=(float)q("SELECT COALESCE(SUM(CASE WHEN transaction_type='Payment' THEN amount ELSE -amount END),0) FROM payments WHERE reservation_id=? AND status='Pending'",[$rid])->fetchColumn();if($amount>$r['total']-$paid-$waiting+.005)out(['error'=>'Amount exceeds the remaining balance'],422);q("INSERT INTO payments(reservation_id,amount,payment_method,transaction_type,status,proof) VALUES(?,?,?,'Payment','Pending',?)",[$rid,$amount,$method,$proof]);out(['ok'=>1]);
case 'users': need('Admin');out(q("SELECT u.user_id,u.full_name,u.email,u.phone,u.user_role,u.is_active,u.created_at,(SELECT COUNT(*) FROM reservations r WHERE r.guest_id=u.user_id) bookings,(SELECT COUNT(*) FROM reservations r WHERE r.guest_id=u.user_id AND r.status='Completed') completed,(SELECT COUNT(*) FROM reservations r WHERE r.guest_id=u.user_id AND r.status='Cancelled') cancelled,(SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN reservations r ON r.reservation_id=p.reservation_id WHERE r.guest_id=u.user_id AND p.transaction_type='Payment' AND p.status='Approved') spent FROM users u ORDER BY u.user_role,u.full_name")->fetchAll());
case 'useractive':
 $u=need('Admin');$id=(int)($in['id']??0);$active=empty($in['active'])?0:1;if($id==$u['user_id'])out(['error'=>'You cannot deactivate your own account'],422);$changed=q("UPDATE users SET is_active=? WHERE user_id=? AND user_role='Guest'",[$active,$id]);if(!$changed->rowCount())out(['error'=>'Customer was not found or already has this status'],404);audit($active?'Customer activated':'Customer deactivated',"User #$id");out(['ok'=>1]);
case 'userdetail':
 need('Admin');$id=(int)($_GET['id']??0);$user=q("SELECT user_id,full_name,email,phone,address,created_at FROM users WHERE user_id=? AND user_role='Guest'",[$id])->fetch();if(!$user)out(['error'=>'Customer not found'],404);$user['bookings']=q("SELECT r.*,a.room_name,a.rate_per_night,DATEDIFF(r.check_out_date,r.check_in_date) nights FROM reservations r JOIN accommodations a ON a.room_id=r.room_id WHERE r.guest_id=? ORDER BY r.created_at DESC",[$id])->fetchAll();$user['payments']=q("SELECT p.*,r.reservation_id FROM payments p JOIN reservations r ON r.reservation_id=p.reservation_id WHERE r.guest_id=? ORDER BY p.paid_at DESC",[$id])->fetchAll();out($user);
case 'adduser':
 need('Admin');$e=strtolower(clean($in['email']??''));$ro=in_array($in['role']??'',['Staff','Admin'])?$in['role']:'Staff';
 if(!filter_var($e,FILTER_VALIDATE_EMAIL)||strlen($in['password']??'')<6||!clean($in['name']??''))out(['error'=>'Complete all fields'],422);
 $name=clean($in['name']);q("INSERT INTO users(full_name,email,phone,user_role,password_hash) VALUES(?,?,?,?,?)",[$name,$e,clean($in['phone']??'',20),$ro,password_hash($in['password'],PASSWORD_DEFAULT)]);$safe=htmlspecialchars($name,ENT_QUOTES,'UTF-8');sendEmail($e,'Welcome to MNB Resort','<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:28px;color:#20243a"><h2>Welcome to MNB Resort, '.$safe.'!</h2><p>Your '.$ro.' account has been created successfully by the resort administrator.</p><p>Please log in using the credentials provided by your administrator.</p></div>');audit('Staff account created',"$ro · $e");out(['ok'=>1]);
case 'deluser':
 $u=need('Admin');if((int)$in['id']==$u['user_id'])out(['error'=>'Cannot delete yourself'],422);
 q("DELETE FROM users WHERE user_id=?",[(int)$in['id']]);out(['ok'=>1]);
case 'myprofile': $u=need();$pc=userPhotoColumn();out(q("SELECT full_name,email,phone,address,date_of_birth,`$pc` AS photo,user_role,created_at FROM users WHERE user_id=?",[$u['user_id']])->fetch());
case 'profilephoto':
 $u=need();$d=$in['photo']??'';
 if(!preg_match('#^data:image/(jpeg|png|webp);base64,(.+)$#',$d,$m))out(['error'=>'Choose a valid JPEG, PNG, or WebP photo'],422);
 $b=base64_decode($m[2],true);if(!$b||strlen($b)>3e6||!getimagesizefromstring($b))out(['error'=>'Photo must be a valid image under 3 MB'],422);
 @mkdir(public_path('uploads'),0755,true);$ext=$m[1]=='jpeg'?'jpg':$m[1];$f='uploads/profile'.$u['user_id'].'_'.time().'.'.$ext;
 if(file_put_contents(public_path($f),$b)===false)out(['error'=>'Could not save profile photo. Make sure public/uploads is writable.'],500);
 $pc=userPhotoColumn();q("UPDATE users SET `$pc`=? WHERE user_id=?",[$f,$u['user_id']]);out(['photo'=>$f]);
case 'profile':
 $u=need();$n=clean($in['name']??'');$e=strtolower(clean($in['email']??''));
 if(!$n||!filter_var($e,FILTER_VALIDATE_EMAIL))out(['error'=>'Valid name and email required'],422);
 if(q("SELECT 1 FROM users WHERE email=? AND user_id<>?",[$e,$u['user_id']])->fetch())out(['error'=>'Email already used'],422);
 $dob=$in['dob']??null;if($dob!==null&&$dob!==''&&!dt($dob))out(['error'=>'Enter a valid date of birth'],422);q("UPDATE users SET full_name=?,email=?,phone=?,address=?,date_of_birth=? WHERE user_id=?",[$n,$e,clean($in['phone']??'',20),clean($in['address']??'',255),$dob?:null,$u['user_id']]);$_SESSION['u']['full_name']=$n;out(['user'=>$_SESSION['u']]);
case 'password':
 $u=need();$r=q("SELECT email,password_hash FROM users WHERE user_id=?",[$u['user_id']])->fetch();if(!password_verify((string)($in['old']??''),$r['password_hash']))out(['error'=>'Current password is wrong'],422);$error=issueOtp($r['email'],'password_change',$u['user_id']);if($error)out(['error'=>$error],503);out(['otp_sent'=>1,'email'=>$r['email']]);
case 'changepassword':
 $u=need();$pw=(string)($in['password']??'');if(strlen($pw)<6)out(['error'=>'New password must be at least 6 characters'],422);$r=q("SELECT email FROM users WHERE user_id=?",[$u['user_id']])->fetch();$check=validateOtp($r['email'],'password_change',$in['code']??'');if(isset($check['error']))out(['error'=>$check['error']],422);$o=$check['row'];if((int)$o['user_id']!==(int)$u['user_id'])out(['error'=>'That code does not match this account.'],422);$pdo->beginTransaction();q("UPDATE users SET password_hash=? WHERE user_id=?",[password_hash($pw,PASSWORD_DEFAULT),$u['user_id']]);q("DELETE FROM email_otps WHERE email=? AND purpose='password_change'",[$r['email']]);$pdo->commit();out(['ok'=>1]);
default: out(['error'=>'Unknown action'],404);
}}catch(PDOException $e){if($pdo instanceof PDO&&$pdo->inTransaction())$pdo->rollBack();$state=(string)$e->getCode();$detail=strtolower($e->getMessage());$msg=match($state){'23000'=>str_contains($detail,'duplicate entry')?'That value is already in use.':'This record is still in use and cannot be removed.','2002','2003','1045'=>'Could not connect to MySQL. Check that the server is running and the MNB_DB_* settings in .env are correct.','42S22','42S02'=>'Database schema is missing a required column or table. Run the SQL update files, then refresh.','HY000'=>'The database rejected this operation. Check that MySQL is running and that the schema updates are applied.',default=>'Database operation failed ('.$state.').'};out(['error'=>$msg],500);}
    }
}
