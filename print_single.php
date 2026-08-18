<?php
session_start();
// পাহারাদার: লগইন করা না থাকলে login.php তে পাঠিয়ে দেবে
if (!isset($_SESSION['auth_user'])) {
    header("Location: login.php");
    exit();
}
?>
<?php
$conn = mysqli_connect("localhost", "root", "Srb@12345", "electricity_db");
$id = $_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM payments WHERE id=$id");
$data = mysqli_fetch_assoc($res);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <style>
        /* ইনডেক্স পেজের প্রিন্ট সেটিংস হুবহু এখানে দেওয়া হলো */
        *{
            box-sizing:border-box;
        }
        body{
            background:#fff;
            padding:0;
            margin:0;
            font-family: Arial, sans-serif;
        }

        .box{
            width:100%;
            max-width:none;
            margin:0;
            padding:0;
            border:none;
        }

        .title{
            font-size:16px;
            font-weight:bold;
            text-align:center;
            margin-bottom:5px;
            border-bottom:2px solid #000;
            padding-bottom:5px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        td{
            border:1px solid #000;
            padding:5px;
            font-size:12px; /* ইনডেক্সের প্রিন্ট সাইজ ১২ ছিল */
        }

        .label{
            width:45%;
            font-weight:bold;
            text-align: left;
        }

        .text-right{
            text-align: right;
            font-weight: bold;
        }

        /* প্রিন্ট করার সময় স্কেল ঠিক রাখার জন্য */
        @media print{
            @page {
                margin: 0;
            }
            body {
                margin: 0;
            }
            *{
                -webkit-print-color-adjust:exact;
                print-color-adjust:exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="box">
        <div class="title">
            বিলের প্রদানের তথ্য
        </div>

        <table>
            <tr>
                <td class="label">বিল প্রদানের তারিখ</td>
                <td class="text-right"><?php echo $data['payment_date']; ?></td>
            </tr>
            <tr>
                <td class="label">বিলার পেমেন্ট নম্বর</td>
                <td class="text-right"><?php echo $data['payment_from']; ?></td>
            </tr>
            <tr>
                <td class="label">হিসাব নম্বর</td>
                <td class="text-right"><?php echo $data['hishab_no']; ?></td>
            </tr>
            <tr>
                <td class="label">ট্রানজেকশন আইডি</td>
                <td class="text-right"><?php echo $data['trx_id']; ?></td>
            </tr>
            <tr>
                <td class="label">পল্লী বিদুৎ অফিসে জমা</td>
                <td class="text-right"><?php echo $data['amount']; ?></td>
            </tr>
            <tr>
                <td class="label">সার্ভিস চার্জ</td>
                <td class="text-right"><?php echo $data['service_charge']; ?></td>
            </tr>
            <tr>
                <td class="label" style="font-size:14px;">সর্বমোট টাকা</td>
                <td class="text-right" style="font-size:14px;"><?php echo $data['total_amount']; ?></td>
            </tr>
        </table>
    </div>
</body>
</html>