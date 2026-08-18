<?php
session_start();
if (!isset($_SESSION['auth_user'])) { header("Location: login.php"); exit(); }
$conn = mysqli_connect("localhost", "root", "Srb@12345", "electricity_db");

if (isset($_POST['update_pass'])) {
    $current_user = $_SESSION['auth_user'];
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];

    // বর্তমান পাসওয়ার্ড চেক করা
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$current_user' AND password='$old_pass'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE users SET password='$new_pass' WHERE username='$current_user'");
        $msg = "<p style='color:green;'>পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে!</p>";
    } else {
        $msg = "<p style='color:red;'>পুরানো পাসওয়ার্ডটি সঠিক নয়!</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>পাসওয়ার্ড পরিবর্তন</title>
    <style>
        body { font-family: Arial; padding: 50px; background: #fdfdfd; }
        .box { max-width: 350px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h3>পাসওয়ার্ড পরিবর্তন</h3>
        <?php if(isset($msg)) echo $msg; ?>
        <form method="POST">
            <input type="password" name="old_pass" placeholder="পুরানো পাসওয়ার্ড" required>
            <input type="password" name="new_pass" placeholder="নতুন পাসওয়ার্ড" required>
            <button type="submit" name="update_pass" class="btn">আপডেট করুন</button>
            <br><br>
            <a href="view.php" style="display:block; text-align:center;">ফিরে যান</a>
        </form>
    </div>
</body>
</html>