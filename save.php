<?php
session_start();
// পাহারাদার: লগইন করা না থাকলে login.php তে পাঠিয়ে দেবে
if (!isset($_SESSION['auth_user'])) {
    header("Location: login.php");
    exit();
}
?>
<?php
// ডাটাবেজ কানেকশন
$host = "localhost";
$user = "root";
$pass = "Srb@12345"; 
$db   = "electricity_db";

$conn = mysqli_connect($host, $user, $pass, $db);

// যদি কানেকশন ঠিক থাকে
if ($conn) {
    // জাভাস্ক্রিপ্ট থেকে পাঠানো তথ্যগুলো ধরা হচ্ছে
    $date   = isset($_POST['p_date']) ? $_POST['p_date'] : '';
    $from   = isset($_POST['p_from']) ? $_POST['p_from'] : '';
    $hishab = isset($_POST['p_hishab']) ? $_POST['p_hishab'] : ''; // নতুন যোগ করা হয়েছে
    $trx    = isset($_POST['p_trx']) ? $_POST['p_trx'] : '';
    $amt    = isset($_POST['p_amt']) ? $_POST['p_amt'] : '';
    $charge = isset($_POST['p_charge']) ? $_POST['p_charge'] : '';
    $total  = isset($_POST['p_total']) ? $_POST['p_total'] : '';

    // যদি ট্রানজেকশন আইডি খালি না থাকে তবেই সেভ হবে
    if(!empty($trx)) {
        // SQL কোড আপডেট করা হয়েছে হিসাব নম্বর সেভ করার জন্য
        $sql = "INSERT INTO payments (payment_date, payment_from, hishab_no, trx_id, amount, service_charge, total_amount, status) 
                VALUES ('$date', '$from', '$hishab', '$trx', '$amt', '$charge', '$total', 'পেইড')";
        
        if(mysqli_query($conn, $sql)) {
            echo "সফলভাবে ডাটাবেজে সেভ হয়েছে!";
        } else {
            echo "ভুল হয়েছে: " . mysqli_error($conn);
        }
    } else {
        echo "ট্রানজেকশন আইডি লিখে সেভ বাটনে ক্লিক করো!";
    }
} else {
    echo "ডাটাবেজ কানেক্ট হচ্ছে না!";
}
?>