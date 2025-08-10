<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

$kategoriResult = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

$kategoriArray = [];
while ($row = $kategoriResult->fetch_assoc()) {
    $kategoriArray[] = $row;
}


$produkResult = $conn->query("
    SELECT 
        produk.id,
        produk.nama_produk,
        produk.harga_jual,
        produk.gambar,
        produk.barcode,
        kategori.nama_kategori AS kategori
    FROM produk
    LEFT JOIN kategori ON produk.id_kategori = kategori.id
    ORDER BY produk.nama_produk ASC
");

$produkArray = [];
while ($row = $produkResult->fetch_assoc()) {
    $produkArray[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Transaksi Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen font-sans">
    <div class="flex flex-col md:flex-row min-h-screen">

        <!-- KIRI: PRODUK -->
        <main class="flex-1 p-6">
            <h1 class="text-4xl font-bold mb-6">🛒 Transaksi</h1>
            <!-- BARCODE SEARCH -->
<div class="mb-4">
    <input type="text" id="barcodeInput"
           placeholder="Scan atau ketik nomor barcode..."
           class="w-full px-4 py-2 rounded bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
           autofocus />
</div>


            <!-- Filter Kategori -->
            <div class="flex flex-wrap gap-3 mb-6">
                
<?php foreach ($kategoriArray as $kategori): ?>
    <button 
        class="kategoriBtn bg-gray-700 hover:bg-blue-600 px-4 py-2 rounded-full text-white transition" 
        data-kategori="<?= htmlspecialchars($kategori['nama_kategori']) ?>">
        <?= htmlspecialchars($kategori['nama_kategori']) ?>
    </button>
<?php endforeach; ?>
            
                <button 
                    class="kategoriBtn bg-blue-500 px-4 py-2 rounded-full text-white" 
                    data-kategori="all">
                    Semua
                </button>
            </div>

            <!-- Grid Produk -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($produkArray as $produk): ?>
                    <div 
                        class="produkCard bg-white text-gray-900 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-transform transform hover:-translate-y-1"
                        data-kategori="<?= htmlspecialchars($produk['kategori']) ?>">
                        <img src="/apotek-kasir/assets/img/produk/<?= $produk['gambar'] ?>" 
                             class="w-full h-48 object-cover" 
                             alt="<?= $produk['nama_produk'] ?>" />
                        <div class="p-4">
                            <h3 class="text-lg font-bold mb-1"><?= $produk['nama_produk'] ?></h3>
                            <p class="text-sm text-gray-600 mb-2">Rp <?= number_format($produk['harga_jual'], 0, ',', '.') ?></p>
                            <button 
                                onclick='addToCart(<?= json_encode($produk) ?>)' 
                                class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded">
                                Add to cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p id="noProdukMsg" class="hidden text-gray-400 text-center col-span-full">
    Belum ada produk dalam kategori ini.
</p>

            </div>
            
        </main>

        <!-- KANAN: KERANJANG -->
        <aside class="w-full md:w-80 bg-gray-800 p-6 border-t md:border-t-0 md:border-l border-gray-700">
            <div class="mb-4">
    <label class="block mb-1">Cari Member (No. Telepon)</label>
    <div class="flex space-x-2">
        <input type="text" id="memberPhone" placeholder="0812xxxxxx"
               class="flex-1 px-3 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none" />
        <button type="button" id="searchMemberBtn"
                class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-white">
            Cari
        </button>
    </div>
    <p id="memberResult" class="mt-2 text-sm text-green-400 hidden"></p>
    <p id="memberNotFound" class="mt-2 text-sm text-red-400 hidden">
        Member tidak ditemukan. <a href="/apotek-kasir/member/tambah.php" class="underline">Tambah Member</a>
    </p>
    <input type="hidden" name="id_member" id="idMemberInput" form="checkoutForm">
</div>

            <h2 class="text-2xl font-semibold mb-4">🧾 Order</h2>
            <div id="cartItems" class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                <!-- Diisi via JS -->
            </div>
            <div class="mt-6 pt-4 border-t border-gray-600">
                <p class="text-lg">Total:</p>
                <p id="cartTotal" class="text-3xl font-bold text-green-400">Rp 0</p>
                <button id="clearCartBtn"
        class="mt-3 w-full bg-gray-700 hover:bg-gray-600 text-white py-2 rounded transition">
    Clear Cart
</button>

                <button id="orderBtn" disabled
                        class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded disabled:opacity-50">
                    Order
                </button>
            </div>
            <!-- Metode Bayar -->
<div class="mt-4">
    <label class="block mb-1">Metode Bayar</label>
    <select id="metodeBayar" name="metode_bayar" form="checkoutForm"
        class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white">
        <option value="cash">Cash</option>
        <option value="qris">QRIS</option>
        <option value="debit">Debit</option>
    </select>
</div>
<!-- Bayar dan Kembalian -->
<div class="mt-4">
    <label class="block mb-1">Bayar</label>
    <input type="number" id="bayarInput" min="0" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white" placeholder="Masukkan nominal bayar">
</div>
<div class="mt-2">
    <label class="block mb-1">Kembalian</label>
    <input type="text" id="kembalianInput" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white" readonly>
</div>
<div class="mt-2">
    <label class="block mb-1">Diskon (Poin Member)</label>
    <input type="number" id="diskonInput" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white" readonly value="0">
</div>
<div class="mt-2">
    <label class="block mb-1">Poin Member</label>
    <input type="number" id="poinMemberInput" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white" readonly value="0">
</div>
<div class="mt-2 flex items-center gap-2">
    <input type="checkbox" id="pakaiPoinCheckbox" class="form-checkbox h-5 w-5 text-blue-600">
    <label for="pakaiPoinCheckbox" class="text-sm">Gunakan poin sebagai diskon</label>
</div>
        </aside>
    </div>
     <!-- Form Checkout (hidden) -->
    <form id="checkoutForm" method="POST" action="/apotek-kasir/includes/transaksi/proses_checkout.php">
        <input type="hidden" name="cartData" id="cartDataInput">
    </form>

</div>

<!-- SCRIPT JS -->
<script>
const produkList = <?= json_encode($produkArray) ?>;
let cart = {};
let memberPoin = 0;
let pakaiPoin = false;

function addToCart(produk) {
    if (cart[produk.id]) {
        cart[produk.id].qty += 1;
    } else {
        cart[produk.id] = { ...produk, qty: 1 };
    }
    renderCart();
}

function updateDiskon() {
    const diskonInput = document.getElementById("diskonInput");

    if (pakaiPoin) {
        const total = getTotalBelanja();
        const diskon = Math.min(memberPoin, total); // max diskon = total
        diskonInput.value = diskon;
    } else {
        diskonInput.value = 0;
    }
}



function updatePoinMember() {
    document.getElementById("poinMemberInput").value = memberPoin;
}

function updateKembalian() {
    const bayar = parseInt(document.getElementById("bayarInput").value) || 0;
    const total = getTotalAfterDiskon();
    const kembalian = bayar - total;
    document.getElementById("kembalianInput").value = kembalian > 0 ? formatRupiah(kembalian) : "Rp 0";
}
function getTotalBelanja() {
    let total = 0;
    Object.values(cart).forEach(item => {
        total += item.harga_jual * item.qty;
    });
    return total;
}


function getTotalAfterDiskon() {
    const total = getTotalBelanja();
    const diskon = parseInt(document.getElementById("diskonInput").value) || 0;
    return Math.max(0, total - diskon);
}



// Render keranjang
function renderCart() {
    const cartItems = document.getElementById("cartItems");
    const cartTotal = document.getElementById("cartTotal");
    const orderBtn = document.getElementById("orderBtn");

    cartItems.innerHTML = "";
    let total = 0;

    Object.keys(cart).forEach(id => {
        const item = cart[id];
        total += item.harga_jual * item.qty;

        cartItems.innerHTML += `
            <div class="flex items-center justify-between bg-gray-800 p-3 rounded">
                <img src="/apotek-kasir/assets/img/produk/${item.gambar}" class="w-12 h-12 object-cover rounded" />
                <div class="flex-1 ml-3">
                    <p class="font-semibold">${item.nama_produk}</p>
                    <p class="text-sm text-gray-300">${formatRupiah(item.harga_jual)}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="updateQty('${id}', -1)" class="bg-gray-600 px-2 rounded">-</button>
                    <span>${item.qty}</span>
                    <button onclick="updateQty('${id}', 1)" class="bg-gray-600 px-2 rounded">+</button>
                </div>
            </div>
        `;
    });

    cartTotal.textContent = formatRupiah(getTotalAfterDiskon());
    orderBtn.disabled = total === 0;
    updateDiskon();
    updatePoinMember();
    updateKembalian();
}
document.getElementById("clearCartBtn").addEventListener("click", () => {
    if (confirm("Yakin ingin menghapus semua item dari keranjang?")) {
        cart = {};
        renderCart();
    }
});
document.getElementById("barcodeInput").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        const barcode = this.value.trim();
        if (!barcode) return;

        const produk = produkList.find(p => p.barcode === barcode);

        if (produk) {
            addToCart(produk);
            this.value = '';
        } else {
            alert("Produk dengan barcode tersebut tidak ditemukan.");
        }
    }
});



// Update jumlah qty
function updateQty(id, change) {
    if (cart[id]) {
        cart[id].qty += change;
        if (cart[id].qty <= 0) delete cart[id];
    }
    renderCart();
}

// Format rupiah
function formatRupiah(num) {
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Filter kategori
document.querySelectorAll('.kategoriBtn').forEach(button => {
    button.addEventListener('click', () => {
        const kategori = button.dataset.kategori;
        document.querySelectorAll('.kategoriBtn').forEach(btn => {
            btn.classList.remove('bg-blue-500');
            btn.classList.add('bg-gray-700');
        });
        button.classList.add('bg-blue-500');
        button.classList.remove('bg-gray-700');

        const cards = document.querySelectorAll('.produkCard');
        let found = false;

        cards.forEach(card => {
            if (kategori === 'all' || card.dataset.kategori === kategori) {
                card.classList.remove('hidden');
                found = true;
            } else {
                card.classList.add('hidden');
            }
        });

        // Tampilkan pesan jika tidak ada produk
        const noProdukMsg = document.getElementById('noProdukMsg');
        if (!found) {
            noProdukMsg.classList.remove('hidden');
        } else {
            noProdukMsg.classList.add('hidden');
        }
    });
});


document.getElementById("orderBtn").addEventListener("click", () => {
    const cartDataInput = document.getElementById("cartDataInput");
    cartDataInput.value = JSON.stringify(cart);

    // Tambahkan metode bayar, diskon, bayar ke form
    const checkoutForm = document.getElementById("checkoutForm");
    let metodeInput = document.getElementById("metodeBayarHidden");
    let diskonInput = document.getElementById("diskonHidden");
    let bayarInput = document.getElementById("bayarHidden");
    let pakaiPoinInput = document.getElementById("pakaiPoinHidden");
    if (!metodeInput) {
        metodeInput = document.createElement("input");
        metodeInput.type = "hidden";
        metodeInput.name = "metode_bayar";
        metodeInput.id = "metodeBayarHidden";
        checkoutForm.appendChild(metodeInput);
    }
    if (!diskonInput) {
        diskonInput = document.createElement("input");
        diskonInput.type = "hidden";
        diskonInput.name = "diskon";
        diskonInput.id = "diskonHidden";
        checkoutForm.appendChild(diskonInput);
    }
    if (!bayarInput) {
        bayarInput = document.createElement("input");
        bayarInput.type = "hidden";
        bayarInput.name = "bayar";
        bayarInput.id = "bayarHidden";
        checkoutForm.appendChild(bayarInput);
    }
    if (!pakaiPoinInput) {
        pakaiPoinInput = document.createElement("input");
        pakaiPoinInput.type = "hidden";
        pakaiPoinInput.name = "pakai_poin";
        pakaiPoinInput.id = "pakaiPoinHidden";
        checkoutForm.appendChild(pakaiPoinInput);
    }
    metodeInput.value = document.getElementById("metodeBayar").value;
    diskonInput.value = memberPoin;
    bayarInput.value = document.getElementById("bayarInput").value;
    pakaiPoinInput.value = pakaiPoin ? 1 : 0;

    checkoutForm.submit();
});

// Checkbox event
document.getElementById("pakaiPoinCheckbox").addEventListener("change", function() {
    pakaiPoin = this.checked;
    updateDiskon();
    renderCart();
});

// Saat member ditemukan, update poin
document.getElementById("searchMemberBtn").addEventListener("click", () => {
    const telp = document.getElementById("memberPhone").value.trim();
    const resultP = document.getElementById("memberResult");
    const notFoundP = document.getElementById("memberNotFound");
    const idInput = document.getElementById("idMemberInput");

    resultP.classList.add("hidden");
    notFoundP.classList.add("hidden");
    idInput.value = '';

    if (!telp) return;

fetch(`/apotek-kasir/includes/transaksi/cek_member.php?telp=${encodeURIComponent(telp)}`)
  .then(res => res.json())
  .then(data => {
    if (data.status === 'found') {
resultP.textContent = `Member: ${data.nama} (Poin: ${data.poin})`;
      resultP.classList.remove("hidden");
      notFoundP.classList.add("hidden");
      idInput.value = data.id;  // simpan id member di hidden input
      memberPoin = parseInt(data.poin) || 0;
      updatePoinMember();
      updateDiskon();
      renderCart();
    } else {
      memberPoin = 0;
      updatePoinMember();
      updateDiskon();
      renderCart();
    }
  })
  .catch(err => {
    alert("Terjadi kesalahan saat mencari member.");
    console.error(err);
  });
});

// Reset poin jika member tidak ditemukan
document.getElementById("memberPhone").addEventListener("input", () => {
    memberPoin = 0;
    updatePoinMember();
    updateDiskon();
    renderCart();
});
</script>

<!-- Sidebar Toggle (Dipertahankan) -->
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
</body>
</html>
