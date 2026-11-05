<?php
session_start();
require_once 'koneksi_postcard.php';

if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    unset($_SESSION['promo_diskon']);
    unset($_SESSION['promo_bonus']);
    header("Location: index_postcard.php");
    exit();
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;

foreach ($cart as $item) {
    $subtotal += ($item['harga'] * $item['qty']);
}

// Logika Promo Otomatis Berdasarkan Total Belanja
$diskon = 0;
$bonus = "-";
$pesan_promo = "";

if ($subtotal > 0) {
    if ($subtotal >= 100000) {
        $diskon = $subtotal * 0.10; // Diskon 10%
        $bonus = "1 Exclusive Postcard";
        $pesan_promo = "Selamat! Belanja di atas 100K mendapat Diskon 10% & Bonus Exclusive Postcard.";
    } elseif ($subtotal >= 50000) {
        $diskon = 5000; // Potongan 5K
        $bonus = "Sticker Pack";
        $pesan_promo = "Selamat! Belanja di atas 50K mendapat Potongan IDR 5.000 & Bonus Sticker Pack.";
    } else {
        $selisih = 50000 - $subtotal;
        $pesan_promo = "Belanja IDR " . number_format($selisih, 0, ',', '.') . " lagi untuk mendapatkan diskon dan bonus item!";
    }
}

// Simpan ke session untuk ditampilkan di nota
$_SESSION['promo_diskon'] = $diskon;
$_SESSION['promo_bonus'] = $bonus;

$total_akhir = $subtotal - $diskon;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Postcards by Author</title>
    <style>
        body { margin: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #ffffff; color: #000000; }
        header { padding: 30px 40px; border-bottom: 1px solid #000000; text-align: center; }
        header h1 { margin: 0; font-size: 20px; text-transform: lowercase; letter-spacing: 2px; font-weight: normal;}
        
        .container { max-width: 700px; margin: 50px auto; padding: 0 20px; }
        h2 { font-size: 16px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #000000; padding-bottom: 10px; margin-bottom: 20px;}
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
        th { text-align: left; padding: 10px 0; border-bottom: 1px solid #eeeeee; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;}
        td { padding: 15px 0; border-bottom: 1px solid #eeeeee; }
        .td-right { text-align: right; }
        
        .promo-banner { background-color: #f9f9f9; padding: 15px; border: 1px dashed #000000; margin-bottom: 30px; font-size: 12px; text-align: center; letter-spacing: 1px;}
        
        .summary { width: 100%; font-size: 14px; margin-bottom: 30px; }
        .summary td { padding: 10px 0; }
        .summary .grand-total { font-weight: bold; font-size: 16px; border-top: 1px solid #000000; }

        .action-btns { display: flex; justify-content: space-between; margin-top: 40px; }
        .btn { padding: 15px 30px; text-decoration: none; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; border: 1px solid #000000; transition: 0.3s; text-align: center;}
        .btn-back { background: #ffffff; color: #000000; }
        .btn-back:hover { background: #f5f5f5; }
        .btn-checkout { background: #000000; color: #ffffff; flex: 1; margin-left: 20px; }
        .btn-checkout:hover { background: #333333; }
    </style>
</head>
<body>

    <header>
        <h1>postcards by author</h1>
    </header>

    <div class="container">
        <h2>Rincian Pesanan</h2>

        <?php if (empty($cart)) { ?>
            <p style="text-align:center; padding: 40px 0; color: #888;">Keranjang belanja kosong.</p>
            <div class="action-btns">
                <a href="index_postcard.php" class="btn btn-back" style="flex:1; margin:0;">Kembali ke Katalog</a>
            </div>
        <?php } else { ?>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="td-right">Qty</th>
                        <th class="td-right">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item) { ?>
                    <tr>
                        <td><?= strtoupper($item['nama']) ?></td>
                        <td class="td-right"><?= $item['qty'] ?></td>
                        <td class="td-right">IDR <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="promo-banner">
                <?= $pesan_promo ?>
            </div>

            <table class="summary">
                <tr>
                    <td>Subtotal</td>
                    <td class="td-right">IDR <?= number_format($subtotal, 0, ',', '.') ?></td>
                </tr>
                <?php if ($diskon > 0) { ?>
                <tr>
                    <td>Diskon Otomatis</td>
                    <td class="td-right">- IDR <?= number_format($diskon, 0, ',', '.') ?></td>
                </tr>
                <?php } ?>
                <?php if ($bonus != "-") { ?>
                <tr>
                    <td>Item Bonus</td>
                    <td class="td-right"><i><?= strtoupper($bonus) ?></i></td>
                </tr>
                <?php } ?>
                <tr class="grand-total">
                    <td>TOTAL</td>
                    <td class="td-right">IDR <?= number_format($total_akhir, 0, ',', '.') ?></td>
                </tr>
            </table>

            <div class="action-btns">
                <a href="checkout_postcard.php?clear=1" class="btn btn-back">Batal</a>
                <a href="nota_postcard.php" class="btn btn-checkout">Proses Checkout</a>
            </div>
        <?php } ?>
    </div>

</body>
</html>