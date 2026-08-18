<?php
session_start();
// পাহারাদার (নিরাপত্তা)
if (!isset($_SESSION['auth_user'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "Srb@12345", "electricity_db");
$id = $_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM payments WHERE id=$id");
$row = mysqli_fetch_assoc($res);

if(isset($_POST['update'])){
    $p_date    = $_POST['p_date'];
    $p_from    = $_POST['p_from'];   // বিলার নম্বর
    $p_hishab  = $_POST['p_hishab']; // হিসাব নম্বর
    $p_trx     = $_POST['p_trx'];
    $p_amt     = $_POST['p_amt'];
    $p_charge  = $_POST['p_charge'];
    $p_total   = $_POST['p_total'];

    $update_sql = "UPDATE payments SET 
        payment_date='$p_date', 
        payment_from='$p_from', 
        hishab_no='$p_hishab', 
        trx_id='$p_trx', 
        amount='$p_amt', 
        service_charge='$p_charge', 
        total_amount='$p_total' 
        WHERE id=$id";
    
    if(mysqli_query($conn, $update_sql)){
        header("Location: view.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>তথ্য সংশোধন করুন</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 15px; }
        
        .edit-container { 
            max-width: 450px; 
            margin: 10px auto; 
            background: #fff; 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }

        h3 { text-align: center; color: #333; margin-bottom: 15px; font-size: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        .form-group { margin-bottom: 12px; } /* ফাঁকা জায়গা কমানো হয়েছে */
        
        label { display: block; font-size: 13px; font-weight: bold; color: #555; margin-bottom: 4px; }
        
        input, select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 6px; 
            font-size: 15px; 
            outline: none;
            background: #fdfdfd;
        }

        input:focus, select:focus { border-color: #007bff; background: #fff; }

        .btn-row { display: flex; gap: 10px; margin-top: 15px; }
        
        .btn-update { 
            flex: 2; 
            background: #28a745; 
            color: white; 
            border: none; 
            padding: 12px; 
            cursor: pointer; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: bold; 
            transition: 0.3s;
        }

        .btn-cancel { 
            flex: 1; 
            background: #6c757d; 
            color: white; 
            text-decoration: none; 
            text-align: center; 
            padding: 12px; 
            border-radius: 6px; 
            font-size: 16px;
        }

        .btn-update:hover { background: #218838; }
        .btn-cancel:hover { background: #5a6268; }

        @media screen and (max-width: 480px) {
            .edit-container { padding: 15px; }
            h3 { font-size: 18px; }
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h3>তথ্য সংশোধন করুন</h3>
    
    <form method="POST">
        <div class="form-group">
            <label>তারিখ ও সময়:</label>
            <input type="text" name="p_date" value="<?php echo $row['payment_date']; ?>">
        </div>

        <div class="form-group">
            <label>বিলার নম্বর:</label>
            <select name="p_from">
                <option value="RA-018844009514" <?php if($row['payment_from'] == 'RA-018844009514') echo 'selected'; ?>>RA-018844009514</option>
                <option value="BM-01331826365" <?php if($row['payment_from'] == 'BM-01331826365') echo 'selected'; ?>>BM-01331826365</option>
                <option value="NA-01331826365" <?php if($row['payment_from'] == 'NA-01331826365') echo 'selected'; ?>>NA-01331826365</option>
                <option value="BA-01780432441" <?php if($row['payment_from'] == 'BA-01780432441') echo 'selected'; ?>>BA-01780432441</option>
                <option value="BP-01884400951" <?php if($row['payment_from'] == 'BP-01884400951') echo 'selected'; ?>>BP-01884400951</option>
                <option value="BP-01571231275" <?php if($row['payment_from'] == 'BP-01571231275') echo 'selected'; ?>>BP-01571231275</option>
            </select>
        </div>

        <div class="form-group">
            <label>হিসাব নম্বর:</label>
            <input type="text" name="p_hishab" value="<?php echo $row['hishab_no']; ?>">
        </div>

        <div class="form-group">
            <label>ট্রানজেকশন আইডি:</label>
            <input type="text" name="p_trx" value="<?php echo $row['trx_id']; ?>">
        </div>

        <div class="form-group">
            <label>জমা টাকা:</label>
            <input type="text" name="p_amt" value="<?php echo $row['amount']; ?>">
        </div>

        <div class="form-group">
            <label>সার্ভিস চার্জ:</label>
            <input type="text" name="p_charge" value="<?php echo $row['service_charge']; ?>">
        </div>

        <div class="form-group">
            <label>সর্বমোট:</label>
            <input type="text" name="p_total" value="<?php echo $row['total_amount']; ?>">
        </div>

        <div class="btn-row">
            <button type="submit" name="update" class="btn-update">আপডেট</button>
            <a href="view.php" class="btn-cancel">বাতিল</a>
        </div>
    </form>
</div>

</body>
</html>