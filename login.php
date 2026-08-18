<?php
session_start();
$conn = mysqli_connect("localhost", "root", "Srb@12345", "electricity_db");

if (isset($_POST['login'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['auth_user'] = $user;
        header("Location: view.php"); 
        exit();
    } else {
        $error = "ভুল ইউজারনেম বা পাসওয়ার্ড!";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>লগইন করুন</title>
    <style>
        /* ১. রিসেট কোড */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        /* ২. পুরো স্ক্রিন দখল করার জাদুকরী কোড */
        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden; /* স্ক্রিন যেন ফালতু না নড়ে */
        }

        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            
            /* এটিই আপনার ফরমকে সব দিক থেকে মাঝখানে রাখবে */
            display: grid;
            place-items: center; 
            align-content: center;
        }

        /* ৩. লগইন কার্ডের ডিজাইন */
        .login-card { 
            background: #ffffff; 
            padding: 35px 25px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); 
            width: 90%; 
            max-width: 360px; 
            text-align: center;
        }

        h2 { margin-bottom: 25px; color: #333; font-size: 24px; font-weight: bold; }
        
        .input-group { margin-bottom: 15px; text-align: left; }
        label { display: block; margin-bottom: 5px; color: #555; font-size: 14px; font-weight: 600; }
        
        input { 
            width: 100%; 
            padding: 13px; 
            border: 2px solid #eee; 
            border-radius: 10px; 
            font-size: 16px; 
            outline: none;
            background: #fdfdfd;
            transition: 0.3s;
        }
        
        input:focus { border-color: #667eea; background: #fff; }
        
        button { 
            width: 100%; 
            padding: 14px; 
            background: #667eea; 
            color: white; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
            font-size: 18px; 
            font-weight: bold;
            margin-top: 10px;
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        .error-msg { 
            background: #ffebee; 
            color: #c62828; 
            padding: 10px; 
            border-radius: 8px; 
            margin-bottom: 15px; 
            font-size: 13px;
            border: 1px solid #ffcdd2;
        }

        .footer-text { margin-top: 20px; color: #999; font-size: 11px; }

        /* ৪. কিবোর্ড উঠলে ফরম যেন উপরে চলে আসে (মোবাইল চেক) */
        @media (max-height: 500px) {
            body { align-content: flex-start; padding-top: 20px; overflow: auto; }
            .login-card { margin-bottom: 20px; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>লগইন করুন</h2>
        
        <?php if(isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>ইউজারনেম</label>
                <input type="text" name="username" placeholder="Username" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <label>পাসওয়ার্ড</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" name="login">লগইন</button>
        </form>
        
        <p class="footer-text">নিরাপদ বিল পেমেন্ট সিস্টেম</p>
    </div>

</body>
</html>