<?php
session_start();
include __DIR__ . "/../server/database.php";
error_reporting(E_ALL);

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// DATE
date_default_timezone_set("Asia/Jakarta");

$hour = date("H");

if($hour >= 5 && $hour < 12) {
    $greeting = "Selamat Pagi";
}elseif($hour >= 12 && $hour < 15) {
    $greeting = "Selamat Siang";
}elseif($hour >= 15 && $hour < 18) {
    $greeting = "Selamat Sore";
}else {
    $greeting = "Selamat Malam";
}

// TAMBAH
if (isset($_POST['tambah_barang'])) {
    $nama   = mysqli_real_escape_string($db, trim($_POST['nama_barang'])) ;
    $jumlah = (int) $_POST['jumlah_barang'];
    $harga  = (int) $_POST['harga_barang'];

    if ($nama === '' || $jumlah <= 0 || $harga <= 0) {
        $_SESSION['report_message'] = "Input tidak valid";
    } else {
        $db->query("INSERT INTO barang (nama_barang,jumlah_barang,harga_barang)
                    VALUES ('$nama',$jumlah,$harga)");
        $_SESSION['report_message'] = "Barang ditambahkan";
    }
    header("Location: dashboard.php");
    exit;
}

// UPDATE
if (isset($_POST['update_barang'])) {
    $id     = (int) $_POST['id_barang'];
    $nama   = mysqli_real_escape_string($db, trim($_POST['nama_barang'])) ;
    $jumlah = (int) $_POST['jumlah_barang'];
    $harga  = (int) $_POST['harga_barang'];

    if ($id > 0) {
        $db->query("UPDATE barang SET
            nama_barang='$nama',
            jumlah_barang=$jumlah,
            harga_barang=$harga
            WHERE id_barang=$id
        ");
        $_SESSION['report_message'] = "Barang diupdate";
    }
    header("Location: dashboard.php");
    exit;
}

// JUAL
if (isset($_POST['jual_barang'])) {
    $id     = (int) $_POST['id_barang'];
    $jumlah_sekarang = (int) $_POST['jumlah_barang_sekarang'];
    
    if ($jumlah_sekarang <= 0 || $id <= 0) {
        $_SESSION['report_message'] = "Input tidak valid";
        header("Location: dashboard.php");
        exit;
    }

   $result = $db->query("SELECT jumlah_barang FROM barang WHERE id_barang = $id");
   $barang = $result->fetch_assoc();

   if (!$barang) {
        $_SESSION['report_message'] = "Barang tidak ditemukan";
        header("Location: dashboard.php");
        exit;
    }

    $stok_sekarang = (int) $barang['jumlah_barang'];

    if ($jumlah_sekarang > $stok_sekarang) {
        $_SESSION['report_message'] = "Stok tidak mencukupi";
        header("Location: dashboard.php");
        exit;
    }

    $stok_baru = $stok_sekarang - $jumlah_sekarang;

    $update = $db->query("
        UPDATE barang 
        SET jumlah_barang = $stok_baru 
        WHERE id_barang = $id
    ");

    $_SESSION['report_message'] = $update
        ? "Barang berhasil dijual"
        : "Gagal menjual barang";

    header("Location: dashboard.php");
    exit;
}

// DELETE
if (isset($_POST['hapus_barang'])) {
    $id = (int) $_POST['id_barang'];
    if ($id > 0) {
        $db->query("DELETE FROM barang WHERE id_barang=$id");
        $_SESSION['report_message'] = "Barang dihapus";
    }
    header("Location: dashboard.php");
    exit;
}

// search
$search = "";
if(isset($_GET['keyword'])) {
    $search = mysqli_real_escape_string($db,$_GET['keyword']);
    $query = "SELECT * FROM barang WHERE nama_barang LIKE '%$search%'";

}else {
    $query = "SELECT * FROM barang";
}


// logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}


$data = $db->query($query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>

<body class="bg-slate-50 min-h-screen">

    <nav class="bg-white shadow-sm border-b px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <span class="bg-blue-600 text-white p-1.5 rounded-lg">📦</span>
            Inventaris
        </h1>
        <div class="flex items-center gap-4">
            <form method="POST" class="inline">
                <button name="logout" class="text-sm font-medium text-red-600 hover:bg-red-50 px-3 py-2 rounded-lg transition">Logout</button>
            </form>
        </div>
    </nav>
    
    <main class="max-w-6xl mx-auto p-4 sm:p-6">
        <span class="text-gray-600 my-9 sm:block"><?= $greeting;?>, <span class="font-semibold text-gray-900"><?= $_SESSION['username']; ?></span></span>
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <form action="" method="GET" class="flex gap-2">
                    <div class="relative flex-grow">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input 
                            type="text" 
                            name="keyword" 
                            value="<?= htmlspecialchars($search); ?>"
                            placeholder="Cari nama barang..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                        >
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Cari
                    </button>
                    <?php if($search !== ""): ?>
                        <a href="?" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
            
        <?php if(isset($_SESSION['report_message'])): ?>
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded shadow-sm flex justify-between items-center">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <p class="font-medium text-sm"><?= $_SESSION['report_message']; unset($_SESSION['report_message']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-700">Daftar Barang</h2>
            <button onclick="toggleAdd()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition flex items-center gap-2 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Barang
            </button>
        </div>

        <div id="addForm" class="hidden mb-8 bg-white p-6 rounded-xl shadow-md border border-gray-100 transition-all">
            <h3 class="font-bold text-gray-800 mb-4">Input Barang Baru</h3>
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <input name="nama_barang" class="border border-gray-300 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Nama Barang" required>
                <input name="jumlah_barang" type="number" class="border border-gray-300 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Jumlah" required>
                <input name="harga_barang" type="number" class="border border-gray-300 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Harga (Rp)" required>
                <div class="sm:col-span-3 flex justify-end gap-2 mt-2">
                    <button type="button" onclick="toggleAdd()" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</button>
                    <button type="submit" name="tambah_barang" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition">Simpan Barang</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm uppercase">
                        <tr>
                            <th class="p-4 font-semibold text-center">No</th>
                            <th class="p-4 font-semibold text-center">Nama Barang</th>
                            <th class="p-4 font-semibold text-center">Jumlah</th>
                            <th class="p-4 font-semibold text-center">Harga</th>
                            <th class="p-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php if(mysqli_num_rows($data) > 0):?>
                            <?php $no = 1; while($row = $data->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 text-center text-gray-400"><?= $no++; ?></td>
                                    <td class="p-4 font-medium text-gray-900"><?= $row['nama_barang']; ?></td>
                                    <td class="p-4 text-center">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs font-bold"><?= $row['jumlah_barang']; ?> unit</span>
                                    </td>
                                    <td class="p-4 text-right pr-8 font-semibold text-blue-600">
                                        Rp <?= number_format($row['harga_barang'],0,',','.'); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex justify-center gap-3">
                                            <button onclick="openEdit(<?= $row['id_barang'] ?>, '<?= $row['nama_barang'] ?>', <?= $row['jumlah_barang'] ?>, <?= $row['harga_barang'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800 flex items-center gap-1 font-medium transition">
                                                Edit
                                            </button>
                                            <button onclick="openJual(<?= $row['id_barang'] ?>)"
                                                class="text-green-600 hover:text-green-800 flex items-center gap-1 font-medium transition">
                                                Jual
                                            </button>
                                            <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus barang ini?')">
                                                <input type="hidden" name="id_barang" value="<?= $row['id_barang'] ?>">
                                                <button name="hapus_barang" class="text-red-600 hover:text-red-800 font-medium transition">
                                                Hapus
                                                </button>
                                            </form>
                                        </div> 
                                </tr>
                            <?php endwhile; ?>
                            <?php else :?>
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-500 italic">
                                        Data tidak ditemukan untuk kata kunci "<?= htmlspecialchars($search) ?>"
                                    </td>
                                </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    

    <!-- EDIT -->
    <div id="editModal" class="hidden fixed inset-0 z-50 overflow-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h3 class="text-lg font-bold text-gray-800">Edit Informasi Barang</h3>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id_barang" id="edit_id">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Barang</label>
                    <input name="nama_barang" id="edit_nama" class="border border-gray-300 rounded-lg p-2.5 w-full focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jumlah</label>
                        <input name="jumlah_barang" id="edit_jumlah" type="number" class="border border-gray-300 rounded-lg p-2.5 w-full focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Harga</label>
                        <input name="harga_barang" id="edit_harga" type="number" class="border border-gray-300 rounded-lg p-2.5 w-full focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-6">
                    <button type="button" onclick="closeEdit()" class="px-4 py-2.5 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition">Batal</button>
                    <button name="update_barang" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-md transition">Update Data</button>
                </div>
            </form>
        </div>
    </div>


    <!-- JUAL -->
     <div id="jualModal" class="hidden fixed inset-0 z-50 overflow-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h3 class="text-lg font-bold text-gray-800">Berapa Barang Tejual</h3>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id_barang" id="jumlah_id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jumlah</label>
                        <input name="jumlah_barang_sekarang" type="number" class="border border-gray-300 rounded-lg p-2.5 w-full focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-6">
                    <button type="button" onclick="closeJual()" class="px-4 py-2.5 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition">Batal</button>
                    <button name="jual_barang" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-md transition">Barag Tejual</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JS SCRIPT -->
    <script>
        function toggleAdd() {
            const form = document.getElementById('addForm');
            form.classList.toggle('hidden');
        }

        function openEdit(id, nama, jumlah, harga) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_jumlah').value = jumlah;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEdit() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function openJual(id) {
            document.getElementById('jumlah_id').value = id;
            document.getElementById('jualModal').classList.remove('hidden');
        }

        function closeJual() {
            document.getElementById('jualModal').classList.add('hidden');
        }
    </script>
</body>
</html>
