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
$conn = mysqli_connect("localhost", "root", "Srb@12345", "electricity_db");

// --- স্ট্যাটাস টগল ফাংশন ---
if(isset($_GET['toggle_id']) && isset($_GET['current_status'])){
    $id = $_GET['toggle_id'];
    $current = $_GET['current_status'];
    $new_status = ($current == 'পেইড') ? 'আনপেইড' : 'পেইড';
    mysqli_query($conn, "UPDATE payments SET status='$new_status' WHERE id=$id");
    header("Location: view.php");
    exit();
}

// ফিল্টার ইনপুটগুলো ধরা
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort_type = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// বাংলা তারিখকে তুলনাযোগ্য ইংরেজি তারিখে রূপান্তর করার ফাংশন
function convertBnToEnDate($bnDateString) {
    $bn_digits = array('০','১','২','৩','৪','৫','৬','৭','৮','৯');
    $en_digits = array('0','1','2','3','4','5','6','7','8','9');
    $months = [
        "জানুয়ারি" => "01", "ফেব্রুয়ারি" => "02", "মার্চ" => "03", "এপ্রিল" => "04",
        "মে" => "05", "জুন" => "06", "জুলাই" => "07", "আগস্ট" => "08",
        "সেপ্টেম্বর" => "09", "অক্টোবর" => "10", "নভেম্বর" => "11", "ডিসেম্বর" => "12"
    ];
    $dateStr = str_replace($bn_digits, $en_digits, $bnDateString);
    $parts = explode(" ", $dateStr);
    
    if(count($parts) >= 3) {
        $day = str_pad($parts[0], 2, "0", STR_PAD_LEFT);
        $month = isset($months[$parts[1]]) ? $months[$parts[1]] : "01";
        $year = $parts[2];
        return "$year-$month-$day";
    }
    return "0000-00-00";
}

// সর্ট করার লজিক
switch ($sort_type) {
    case 'old': $order_by = "id ASC"; break;
    case 'high': $order_by = "LENGTH(amount) DESC, amount DESC"; break;
    case 'low': $order_by = "LENGTH(amount) ASC, amount ASC"; break;
    default: $order_by = "id DESC";
}

// মূল কুয়েরি
$sql = "SELECT * FROM payments WHERE (trx_id LIKE '%$search%' OR payment_from LIKE '%$search%' OR payment_date LIKE '%$search%' OR hishab_no LIKE '%$search%')";
if ($status_filter != '') {
    $sql .= " AND status = '$status_filter'";
}
$sql .= " ORDER BY $order_by";
$result = mysqli_query($conn, $sql);

// ডিলিট লজিক
if(isset($_GET['delete_id'])){
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM payments WHERE id=$id");
    header("Location: view.php");
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>বিলের তালিকা</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #fdfdfd; }
        .container { max-width: 1250px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .back-btn { background: #000; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        
        .controls { display: flex; gap: 8px; margin-bottom: 20px; background: #f8f9fa; padding: 15px; border-radius: 5px; flex-wrap: wrap; align-items: center; }
        .search-box { flex: 2; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .filter-input { padding: 9px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .sort-box { padding: 10px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; }
        .find-btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #007bff; color: white; padding: 15px; text-align: center; border: 1px solid #ddd; }
        td { padding: 12px; text-align: center; border: 1px solid #eee; font-size: 15px; }
        tr:hover { background-color: #f1f1f1; }

        .btn { padding: 6px 15px; text-decoration: none; border-radius: 4px; color: white; font-size: 14px; margin: 2px; display: inline-block; font-weight: bold; }
        .btn-print { background: #28a745; }
        .btn-edit { background: #ffc107; color: black; }
        .btn-delete { background: #dc3545; }

        .status-badge { padding: 5px 15px; border-radius: 20px; text-decoration: none; color: white; font-weight: bold; font-size: 13px; display: inline-block; }
        .status-paid { background: #28a745; }
        .status-unpaid { background: #dc3545; }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <a href="index.php" class="back-btn">← নতুন এন্ট্রি করুন</a>
    </div>
    
    <h2 style="color: #333; margin-top: 0;">সব জমানো বিলের তথ্য</h2>

    <form class="controls" method="GET">
        <input type="text" name="search" class="search-box" placeholder="সার্চ করুন..." value="<?php echo htmlspecialchars($search); ?>">
        
        <select name="status_filter" class="filter-input">
            <option value="">সব অবস্থা</option>
            <option value="পেইড" <?php echo ($status_filter == 'পেইড') ? 'selected' : ''; ?>>পেইড</option>
            <option value="আনপেইড" <?php echo ($status_filter == 'আনপেইড') ? 'selected' : ''; ?>>আনপেইড</option>
        </select>

        <input type="date" name="from_date" class="filter-input" value="<?php echo $from_date; ?>">
        <span>থেকে</span>
        <input type="date" name="to_date" class="filter-input" value="<?php echo $to_date; ?>">
        
        <select name="sort" class="sort-box" onchange="this.form.submit()">
            <option value="new" <?php echo ($sort_type == 'new') ? 'selected' : ''; ?>>নতুন আগে</option>
            <option value="old" <?php echo ($sort_type == 'old') ? 'selected' : ''; ?>>পুরানো আগে</option>
            <option value="high" <?php echo ($sort_type == 'high') ? 'selected' : ''; ?>>টাকা (বেশি)</option>
            <option value="low" <?php echo ($sort_type == 'low') ? 'selected' : ''; ?>>টাকা (কম)</option>
        </select>
        
        <button type="submit" class="find-btn">খুঁজুন</button>
    </form>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ক্র: নং</th> <!-- সিরিয়াল কলাম যোগ করা হয়েছে -->
                <th style="width: 15%;">তারিখ</th>
                <th style="width: 12%;">বিলার নম্বর</th>
                <th style="width: 12%;">হিসাব নম্বর</th>
                <th style="width: 13%;">ট্রানজেকশন আইডি</th>
                <th style="width: 9%;">পরিমাণ</th>
                <th style="width: 9%;">মোট</th>
                <th style="width: 7%;">অবস্থা</th>
                <th style="width: 18%;">অ্যাকশন</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sl = 1; // লুপের বাইরে সিরিয়াল শুরু হলো ১ থেকে
            while($row = mysqli_fetch_assoc($result)) { 
                // তারিখ ফিল্টার চেক করা
                if (!empty($from_date) || !empty($to_date)) {
                    $dbDateEn = convertBnToEnDate($row['payment_date']);
                    
                    if (!empty($from_date) && $dbDateEn < $from_date) continue;
                    if (!empty($to_date) && $dbDateEn > $to_date) continue;
                }

                $status = !empty($row['status']) ? $row['status'] : 'পেইড';
                $status_class = ($status == 'পেইড') ? 'status-paid' : 'status-unpaid';
            ?>
            <tr>
                <td><?php echo $sl++; ?></td> <!-- এখানে সিরিয়াল প্রিন্ট হচ্ছে এবং ১ করে বাড়ছে -->
                <td><?php echo $row['payment_date']; ?></td>
                <td><?php echo $row['payment_from']; ?></td>
                <td><?php echo $row['hishab_no']; ?></td>
                <td><?php echo $row['trx_id']; ?></td>
                <td><?php echo $row['amount']; ?></td>
                <td><?php echo $row['total_amount']; ?></td>
                <td>
                    <a href="view.php?toggle_id=<?php echo $row['id']; ?>&current_status=<?php echo $status; ?>" class="status-badge <?php echo $status_class; ?>" onclick="return confirm('অবস্থা পরিবর্তন করতে চান?')">
                        <?php echo $status; ?>
                    </a>
                </td>
                <td>
                    <a href="print_single.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-print">প্রিন্ট</a>
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">এডিট</a>
                    <a href="view.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('মুছে ফেলবেন?')">ডিলিট</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>