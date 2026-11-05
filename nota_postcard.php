<?php
session_start();

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$diskon = isset($_SESSION['promo_diskon']) ? $_SESSION['promo_diskon'] : 0;
$bonus = isset($_SESSION['promo_bonus']) ? $_SESSION['promo_bonus'] : "-";
$subtotal = 0;

foreach ($cart as $item) {
    $subtotal += ($item['harga'] * $item['qty']);
}
$total_akhir = $subtotal - $diskon;

// Kosongkan keranjang setelah nota dibuat
unset($_SESSION['cart']);
unset($_SESSION['promo_diskon']);
unset($_SESSION['promo_bonus']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota - Postcards by Author</title>
    <style>
        body { 
            background-color: #f5f5f5;
            font-family: 'Courier New', Courier, monospace;
            display: flex; justify-content: center; padding: 40px 20px; margin: 0; color: #000000;
        }

        .receipt-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 350px;
            padding: 40px 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .receipt-header { text-align: center; margin-bottom: 20px; border-bottom: 1px dashed #000000; padding-bottom: 20px; }
        .receipt-header h2 { margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px;}
        .receipt-header p { margin: 0; font-size: 12px; color: #555;}

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12px; }
        th { text-align: left; border-bottom: 1px dashed #000000; padding-bottom: 5px; }
        td { padding: 8px 0; vertical-align: top; }
        .td-right { text-align: right; }
        
        .divider { border-bottom: 1px dashed #000000; margin: 15px 0; }
        
        .totals { width: 100%; font-size: 12px; }
        .totals td { padding: 4px 0; }
        .totals .grand-total { font-weight: bold; font-size: 14px; border-top: 1px dashed #000000; padding-top: 10px; margin-top: 5px;}

        .barcode { text-align: center; font-size: 24px; margin-top: 30px; letter-spacing: 2px; }
        .footer-text { text-align: center; font-size: 11px; margin-top: 10px; color: #555;}

        .btn-home { 
            display: block; 
            text-align: center; 
            margin-top: 40px; 
            text-decoration: none; 
            background: #000000; 
            color: #ffffff; 
            padding: 12px; 
            font-family: 'Helvetica Neue', Helvetica, sans-serif; 
            font-size: 12px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }
        .btn-home:hover { background: #333333; }
    </style>
</head>
<body>

    <div class="receipt-container">
        <div class="receipt-header">
            <h2>POSTCARDS BY AUTHOR</h2>
            <p>Jl. Bougenville No. 12</p>
            <p><?= date('d-m-Y H:i:s') ?></p>
        </div>

        <?php if (empty($cart) && $subtotal == 0) { ?>
            <p style="text-align:center;">Transaksi tidak valid.</p>
        <?php } else { ?>
            <table>
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th class="td-right">QTY</th>
                        <th class="td-right">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item) { ?>
                    <tr>
                        <td>
                            <?= strtoupper($item['nama']) ?><br>
                            <small>@ <?= number_format($item['harga'], 0, ',', '.') ?></small>
                        </td>
                        <td class="td-right"><?= $item['qty'] ?></td>
                        <td class="td-right"><?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="divider"></div>

            <table class="totals">
                <tr>
                    <td>SUBTOTAL</td>
                    <td class="td-right"><?= number_format($subtotal, 0, ',', '.') ?></td>
                </tr>
                <?php if ($diskon > 0) { ?>
                <tr>
                    <td>DISKON PROMO</td>
                    <td class="td-right">- <?= number_format($diskon, 0, ',', '.') ?></td>
                </tr>
                <?php } ?>
                <?php if ($bonus != "-") { ?>
                <tr>
                    <td>BONUS</td>
                    <td class="td-right"><?= strtoupper($bonus) ?></td>
                </tr>
                <?php } ?>
                <tr class="grand-total">
                    <td>TOTAL BAYAR</td>
                    <td class="td-right">IDR <?= number_format($total_akhir, 0, ',', '.') ?></td>
                </tr>
            </table>
        <?php } ?>

        <div class="barcode">|||| ||| || ||| | |||</div>
        <div class="footer-text">
            <p>TERIMA KASIH</p>
            <p>IG: @authormotret | theauthor12@gmail.com</p>
        </div>

        <a href="index_postcard.php" class="btn-home">Kembali ke Katalog</a>
    </div>

</body>
</html>