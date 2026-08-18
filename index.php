<?php
session_start();
// পাহারাদার: লগইন করা না থাকলে login.php তে পাঠিয়ে দেবে
if (!isset($_SESSION['auth_user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@100..800&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>বিদুৎ বিল পেমেন্ট এর তথ্য প্রিন্ট</title>

<style>
/* আপনার অরিজিনাল ডিজাইন */
*{
    box-sizing:border-box;
}

body{
    font-family:Arial, sans-serif;
    background:#f5f5f5;
    padding:10px;
    margin:0;
}

.box{
    max-width:600px;
    margin:auto;
    background:#fff;
    border:1px solid #ccc;
    border-radius:8px;
    padding:15px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

.title{
    font-size:20px;
    font-weight:bold;
    text-align:center;
    margin-bottom:15px;
    border-bottom:2px solid #000;
    padding-bottom:8px;
}

table{
    width:100%;
    border-collapse:collapse;
}

td{
    border:1px solid #ddd;
    padding:2px;
}

.label{
    width:45%;
    font-weight:bold;
}

input,
select{
    width:100%;
    border:none;
    outline:none;
    background:transparent;
    font-size:16px;
}

.readonly{
    font-weight:bold;
    text-align:right;
}

.total{
    font-size:20px;
    font-weight:bold;
    text-align:right;
}

/* নতুন বাটনগুলোর ডিজাইন */
.action-buttons {
    max-width: 600px;
    margin: 20px auto;
    text-align: center;
}

.btn {
    padding: 10px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    color: #fff;
    margin: 5px;
}

.print-btn{ background: #07002a; }
.save-btn { background: #07002a; }
.view-btn { background: #09ac0e;  text-decoration: none; display: inline-block; }
.refresh-btn { background: #07a1c0; }

/* প্রিন্ট সেটিংস - আপনার অরিজিনাল সেটিংস */
@media print{
    body{ background:#fff; padding:0; margin:0; }
    .box{ width:100%; max-width:none; margin:0; padding:0; border:none; border-radius:0; box-shadow:none; }
    .action-buttons { display:none !important; } /* প্রিন্ট করার সময় বাটন দেখা যাবে না */
    td{ border:1px solid #000; padding:5px; font-size:12px; }
    .title{ font-size:16px; margin-bottom:5px; }
    .total{ font-size:18px; }
    input, select{ font-size:12px; }
    *{ -webkit-print-color-adjust:exact; print-color-adjust:exact; }
}

table td:nth-child(2),
table td:nth-child(2) input,
table td:nth-child(2) select{
    text-align:right !important;
}
</style>
</head>
<body>

<div class="box">
    <div class="title">বিলের প্রদানের তথ্য</div>
    <table>
        <tr>
            <td class="label">বিল প্রদানের তারিখ</td>
            <td id="paymentDate"></td>
        </tr>
        <tr>
            <td class="label">বিলার পেমেন্ট নম্বর</td>
            <td>
                <select id="paymentFrom">
                    <option>RA-018844009514</option>
                    <option>BM-01331826365</option>
                    <option>NA-01331826365</option>
                    <option>BA-01780432441</option>
                    <option>BP-01884400951</option>
                    <option>BP-01571231275</option>
                </select>
            </td>
        </tr>
        <!-- নতুন ইনপুট ঘর: হিসাব নং -->
        <tr>
            <td class="label">হিসাব নম্বর</td>
            <td><input type="text" id="hishabNo" placeholder="Enter Account No"></td>
        </tr>
        <tr>
            <td class="label">ট্রানজেকশন আইডি</td>
            <td><input type="text" id="trxid" placeholder="Enter Transaction ID"></td>
        </tr>
        <tr>
            <td class="label">পল্লী বিদুৎ অফিসে জমা</td>
            <td><input type="text" id="paidAmount" value="১০০০"></td>
        </tr>
        <tr>
            <td class="label">সার্ভিস চার্জ</td>
            <td class="readonly" id="serviceCharge">১০</td>
        </tr>
        <tr>
            <td class="label">সর্বমোট টাকা</td>
            <td class="total" id="totalAmount">১০১০.০০</td>
        </tr>
    </table>
</div>

<!-- বাটন এরিয়া -->
<div class="action-buttons">
    <a href="logout.php" class="btn view-btn" style="background:red;">লগআউট</a>
    <a href="view.php" class="btn view-btn">সব ডাটা দেখুন</a>
    <button class="btn refresh-btn" onclick="window.location.reload()">রিফ্রেশ</button>
    <button class="btn save-btn" onclick="saveAndPrint()">প্রিন্ট</button>
</div>

<script>
// ইংরেজি সংখ্যা → বাংলা সংখ্যা
function toBanglaNumber(num){ return num.toString().replace(/\d/g,d=>'০১২৩৪৫৬৭৮৯'[d]); }
// বাংলা সংখ্যা → ইংরেজি সংখ্যা
function toEnglishNumber(num){ return num.toString().replace(/[০-৯]/g,d=>'০১২৩৪৫৬৭৮৯'.indexOf(d)); }

function updateDateTime(){
    const now = new Date();
    const months = ["জানুয়ারি","ফেব্রুয়ারি","মার্চ","এপ্রিল","মে","জুন","জুলাই","আগস্ট","সেপ্টেম্বর","অক্টোবর","নভেম্বর","ডিসেম্বর"];
    let day = now.getDate();
    let month = months[now.getMonth()];
    let year = now.getFullYear();
    let hour24 = now.getHours();
    let minute = now.getMinutes();
    let period = (hour24 >= 5 && hour24 < 12) ? "সকাল" : (hour24 >= 12 && hour24 < 16) ? "দুপুর" : (hour24 >= 16 && hour24 < 18) ? "বিকাল" : (hour24 >= 18 && hour24 < 20) ? "সন্ধ্যা" : "রাত";
    let hour12 = hour24 % 12 || 12;

    document.getElementById("paymentDate").innerHTML =
        `${toBanglaNumber(day)} ${month} ${toBanglaNumber(year)} । ${period} ${toBanglaNumber(hour12)}:${toBanglaNumber(minute.toString().padStart(2,'0'))} মিনিট`;
}

// শুধুমাত্র একবার সময় আপডেট হবে (রিফ্রেশ ছাড়া আর পরিবর্তন হবে না)
updateDateTime();

function calculate(){
    let rawAmount = document.getElementById("paidAmount").value;
    let amount = parseFloat(toEnglishNumber(rawAmount)) || 0;
    let charge = (amount < 400) ? 5 : (amount >= 5000) ? 25 : (amount >= 1500) ? 15 : 10;
    document.getElementById("serviceCharge").innerText = toBanglaNumber(charge);
    document.getElementById("totalAmount").innerText = toBanglaNumber((amount + charge).toFixed(2));
}

document.getElementById("paidAmount").addEventListener("input", function(){
    let value = toEnglishNumber(this.value).replace(/\D/g,'');
    this.value = toBanglaNumber(value);
    calculate();
});
calculate();

// সেভ এবং প্রিন্ট করার ফাংশন
function saveAndPrint() {
    let formData = new FormData();
    formData.append('p_date', document.getElementById("paymentDate").innerText);
    formData.append('p_from', document.getElementById("paymentFrom").value);
    formData.append('p_hishab', document.getElementById("hishabNo").value); 
    formData.append('p_trx', document.getElementById("trxid").value);
    formData.append('p_amt', document.getElementById("paidAmount").value);
    formData.append('p_charge', document.getElementById("serviceCharge").innerText);
    formData.append('p_total', document.getElementById("totalAmount").innerText);

    fetch('save.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => {
        window.print();    
    })
    .catch(err => {
        window.print();
    });
}
</script>
</body>
</html>