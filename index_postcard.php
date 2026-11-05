<?php
session_start();
require_once 'koneksi_postcard.php';

// Logika tambah ke keranjang menggunakan AJAX
if (isset($_POST['ajax_add_to_cart'])) {
    $id_produk = $_POST['id_produk'];
    $nama_produk = $_POST['nama_produk'];
    $harga_produk = $_POST['harga_produk'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id_produk) {
            $item['qty']++;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $id_produk,
            'nama' => $nama_produk,
            'harga' => $harga_produk,
            'qty' => 1
        ];
    }
    
    // Hitung total item terbaru untuk diperbarui di layar
    $total_items = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_items += $item['qty'];
    }
    
    // Kembalikan data dalam format JSON agar bisa dibaca oleh JavaScript
    echo json_encode(['status' => 'success', 'total_items' => $total_items]);
    exit(); // Hentikan eksekusi agar tidak memuat seluruh HTML
}

$total_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total_items += $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postcards by Author</title>
    <style>
        body { 
            margin: 0; 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            background-color: #ffffff; 
            color: #000000; 
            scroll-behavior: smooth; 
        }
        
        header { 
            padding: 30px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #000000; 
        }
        .branding h1 { margin: 0; font-size: 22px; text-transform: lowercase; letter-spacing: 2px; font-weight: normal;}
        .branding p { margin: 5px 0 0 0; font-size: 12px; color: #666666; letter-spacing: 1px;}
        .cart-info { 
            font-size: 12px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            color: #000000; 
            text-decoration: none; 
            border: 1px solid #000000; 
            padding: 10px 20px; 
            transition: 0.3s;
        }
        .cart-info:hover { background-color: #000000; color: #ffffff; }
        
        .container { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 40px; }
        
        .card { 
            background: #ffffff; 
            border: 1px solid #eeeeee; 
            transition: 0.3s; 
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .card:hover { border-color: #000000; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .card img { 
            width: 100%; 
            height: 250px; 
            object-fit: contain; 
            object-position: center;
            background-color: #fafafa; 
        }
        .card-body { 
            padding: 20px; 
            text-align: center; 
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .card-title { margin: 0 0 10px 0; font-size: 16px; font-weight: bold; letter-spacing: 1px; }
        .card-desc { font-size: 12px; color: #888888; margin-bottom: 15px; flex-grow: 1; }
        .card-price { font-size: 14px; margin-bottom: 20px; }
        
        .card-form { margin-top: auto; width: 100%; }

        .btn-add { 
            background-color: #ffffff; 
            color: #000000; 
            border: 1px solid #000000; 
            padding: 10px; 
            width: 100%; 
            cursor: pointer; 
            font-size: 12px; 
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s; 
        }
        .btn-add:hover { background-color: #000000; color: #ffffff; }
        /* Style untuk efek tombol saat memuat */
        .btn-add.loading { background-color: #eeeeee; color: #888888; cursor: not-allowed; }
        .btn-add.success { background-color: #000000; color: white; border-color: #ffffff; }

        footer { border-top: 1px solid #000000; padding: 40px 20px; text-align: center; margin-top: 60px; font-size: 12px; color: #666666; }
        .footer-content p { margin: 5px 0; letter-spacing: 1px;}

        .floating-cart {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #000000;
            color: #ffffff;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-decoration: none;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
        }
        
        .floating-cart.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .floating-cart:hover {
            background-color: #333333;
            transform: translateY(-5px);
        }

        .floating-cart svg {
            width: 24px;
            height: 24px;
            fill: #ffffff;
        }

        .floating-cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ffffff;
            color: #000000;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #000000;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>

    <header>
        <div class="branding">
            <h1>postcards by author</h1>
            <p>post your card around the world.</p>
        </div>
        <!-- ID id-cart-header ditambahkan agar mudah diupdate via JS -->
        <a href="checkout_postcard.php" class="cart-info" id="cart-header">Keranjang (<?= $total_items ?>)</a>
    </header>

    <div class="container">
        <div class="grid">
            <?php
            $query = mysqli_query($koneksi, "SELECT * FROM produk");
            while ($row = mysqli_fetch_assoc($query)) {
            ?>
            <div class="card">
                <img src="<?= $row['gambar_url'] ?>" alt="<?= $row['nama'] ?>">
                <div class="card-body">
                    <h3 class="card-title"><?= strtoupper($row['nama']) ?></h3>
                    <p class="card-desc"><?= $row['deskripsi'] ?></p>
                    <div class="card-price">IDR <?= number_format($row['harga'], 0, ',', '.') ?></div>
                    
                    <!-- PERBAIKAN: Form diubah untuk mencegah reload default dan memicu JavaScript -->
                    <form class="card-form" onsubmit="tambahKeKeranjang(event, this)">
                        <input type="hidden" name="id_produk" value="<?= $row['id'] ?>">
                        <input type="hidden" name="nama_produk" value="<?= $row['nama'] ?>">
                        <input type="hidden" name="harga_produk" value="<?= $row['harga'] ?>">
                        <button type="submit" class="btn-add">Tambah</button>
                    </form>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <p>EMAIL: theauthor12@gmail.com | INSTAGRAM: @authormotret</p>
            <p>JL. BOUGENVILLE NO. 12</p>
        </div>
    </footer>

    <a href="checkout_postcard.php" class="floating-cart" id="floatingCart">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
        </svg>
        <!-- ID id-cart-floating ditambahkan -->
        <span class="floating-cart-badge" id="cart-floating"><?= $total_items ?></span>
    </a>

    <!-- SCRIPT BARU: AJAX Fetch API -->
    <script>
        // Logika kemunculan ikon keranjang melayang
        window.addEventListener('scroll', function() {
            const floatingCart = document.getElementById('floatingCart');
            if (window.scrollY > 150) {
                floatingCart.classList.add('show');
            } else {
                floatingCart.classList.remove('show');
            }
        });

        // Logika tambah keranjang tanpa reload
        function tambahKeKeranjang(event, formElement) {
            event.preventDefault(); // Mematikan sifat bawaan form (mencegah reload halaman)
            
            const btn = formElement.querySelector('.btn-add');
            const originalText = btn.innerText;
            
            // Ubah tombol jadi status loading
            btn.innerText = '...';
            btn.classList.add('loading');
            btn.disabled = true;

            // Mengambil data dari form
            const formData = new FormData(formElement);
            formData.append('ajax_add_to_cart', '1'); // Trigger untuk kondisi PHP di atas

            // Mengirim data ke halaman ini sendiri via AJAX
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Update angka keranjang di Header dan Floating icon secara real-time
                    document.getElementById('cart-header').innerText = 'Keranjang (' + data.total_items + ')';
                    document.getElementById('cart-floating').innerText = data.total_items;
                    
                    // Beri animasi berhasil pada tombol
                    btn.innerText = 'Ditambahkan';
                    btn.classList.remove('loading');
                    btn.classList.add('success');
                    
                    // Kembalikan tombol ke kondisi semula setelah 1.5 detik
                    setTimeout(() => {
                        btn.innerText = originalText;
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.innerText = 'Gagal';
                btn.classList.remove('loading');
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                }, 1500);
            });
        }
    </script>

</body>
</html>