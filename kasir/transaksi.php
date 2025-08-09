<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

// Ambil data produk untuk referensi pencarian
$produkResult = $conn->query("SELECT id, nama_produk, harga_jual, gambar FROM produk ORDER BY nama_produk ASC");

// Simpan produk dalam array supaya gampang cari nanti di JS (json_encode)
$produkArray = [];
while ($row = $produkResult->fetch_assoc()) {
    $produkArray[] = $row;
}

$nama = $_SESSION['nama'];
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Transaksi Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-blue-900 to-black text-gray-100 min-h-screen font-sans flex">



<!-- Main content -->
<div id="mainContent" class="flex-1 p-6 md:ml-64">

    <h1 class="text-3xl font-bold mb-6">Transaksi Kasir</h1>

    <!-- Input barcode/manual input -->
    <div class="mb-6">
        <label for="barcodeInput" class="block mb-2 font-semibold text-gray-300">Masukkan Barcode / Produk</label>
        <input id="barcodeInput" type="text" placeholder="Scan atau ketik barcode produk..." autofocus
               class="w-full p-3 rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-500" />
    </div>

    <!-- Cart Table -->
    <div class="overflow-x-auto rounded-lg shadow-lg bg-gray-800 border border-gray-700">
        <table class="w-full text-left table-auto">
            <thead class="bg-gray-700 text-gray-300 uppercase text-sm">
                <tr>
                    <th class="p-3">Gambar</th>
                    <th class="p-3">Nama Produk</th>
                    <th class="p-3">Harga</th>
                    <th class="p-3">Qty</th>
                    <th class="p-3">Subtotal</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody id="cartBody" class="divide-y divide-gray-700">
                <!-- Isi cart dinamis via JS -->
                <tr><td colspan="6" class="p-4 text-center text-gray-400 italic">Keranjang kosong</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Total & Submit -->
    <div class="mt-6 flex justify-between items-center">
        <div>
            <span class="text-xl font-bold">Total: </span>
            <span id="totalHarga" class="text-2xl font-extrabold text-green-400">Rp 0</span>
        </div>
        <button id="btnCheckout" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded text-white font-semibold disabled:opacity-50" disabled>
            Bayar
        </button>
    </div>

</div>

<script>
// Data produk dari PHP
const produkList = <?= json_encode($produkArray) ?>;

let cart = {};

const barcodeInput = document.getElementById('barcodeInput');
const cartBody = document.getElementById('cartBody');
const totalHargaElem = document.getElementById('totalHarga');
const btnCheckout = document.getElementById('btnCheckout');

function formatRupiah(num) {
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function renderCart() {
    cartBody.innerHTML = '';
    const keys = Object.keys(cart);
    if (keys.length === 0) {
        cartBody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-gray-400 italic">Keranjang kosong</td></tr>`;
        totalHargaElem.textContent = 'Rp 0';
        btnCheckout.disabled = true;
        return;
    }

    let total = 0;
    keys.forEach((id) => {
        const item = cart[id];
        const subtotal = item.harga_jual * item.qty;
        total += subtotal;

        cartBody.innerHTML += `
            <tr>
                <td class="p-3">
                    <img src="/apotek-kasir/assets/img/produk/${item.gambar}" alt="${item.nama_produk}" class="w-16 h-16 object-cover rounded" />
                </td>
                <td class="p-3">${item.nama_produk}</td>
                <td class="p-3">${formatRupiah(item.harga_jual)}</td>
                <td class="p-3">
                    <input type="number" min="1" value="${item.qty}" data-id="${id}" class="qtyInput w-16 p-1 rounded text-black" />
                </td>
                <td class="p-3">${formatRupiah(subtotal)}</td>
                <td class="p-3">
                    <button data-id="${id}" class="removeBtn bg-red-600 hover:bg-red-700 rounded px-3 py-1 text-white">Hapus</button>
                </td>
            </tr>
        `;
    });
    totalHargaElem.textContent = formatRupiah(total);
    btnCheckout.disabled = false;

    // Attach event listeners untuk qty input dan remove button
    document.querySelectorAll('.qtyInput').forEach(input => {
        input.addEventListener('change', e => {
            const id = e.target.dataset.id;
            let val = parseInt(e.target.value);
            if (isNaN(val) || val < 1) val = 1;
            cart[id].qty = val;
            renderCart();
        });
    });
    document.querySelectorAll('.removeBtn').forEach(btn => {
        btn.addEventListener('click', e => {
            const id = e.target.dataset.id;
            delete cart[id];
            renderCart();
        });
    });
}

// Tambah produk ke cart berdasarkan barcode input
barcodeInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        const barcode = barcodeInput.value.trim();
        if (!barcode) return;

        // Cari produk berdasar barcode (asumsi barcode ada di produk)
        const produk = produkList.find(p => p.barcode === barcode);

        if (!produk) {
            alert('Produk tidak ditemukan!');
            barcodeInput.value = '';
            return;
        }

        if (cart[produk.id]) {
            cart[produk.id].qty++;
        } else {
            cart[produk.id] = {...produk, qty: 1};
        }

        barcodeInput.value = '';
        renderCart();
    }
});

// Checkout
btnCheckout.addEventListener('click', () => {
    if (Object.keys(cart).length === 0) return;

    // Kirim data ke server (misalnya via fetch/ajax, tapi disini kita redirect ke halaman proses)
    // Bisa juga kirim lewat form POST, atau session, tergantung kebutuhan

    alert('Fitur checkout belum diimplementasikan. Implementasi backend sesuai kebutuhan.');
});

renderCart();
</script>

</body>
<script>
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const toggleBtn = document.getElementById('sidebarToggle');

toggleBtn?.addEventListener('click', () => {
    sidebar?.classList.toggle('-translate-x-full');
    if (!sidebar.classList.contains('-translate-x-full')) {
        mainContent?.classList.add('md:ml-64');
    } else {
        mainContent?.classList.remove('md:ml-64');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
</html>
