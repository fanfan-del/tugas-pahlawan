<?php
// --- KONFIGURASI DATABASE ---
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'root'); // Ganti dengan username DB Anda
define('DB_PASSWORD', '');     // Ganti dengan password DB Anda
define('DB_NAME', 'pahlawan_db'); // Ganti dengan nama DB Anda

// --- KONEKSI DATABASE ---
 $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// --- VARIABEL GLOBAL ---
 $heroes = [];
 $hero_to_edit = null;
 $success_message = '';
 $error_message = '';

// --- LOGIKA CRUD ---

// 1. HAPUS (DELETE)
if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    
    // Ambil nama file gambar untuk dihapus dari folder
    $sql_get_image = "SELECT gambar FROM pahlawan WHERE id = ?";
    $stmt = $conn->prepare($sql_get_image);
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    $hero = $result->fetch_assoc();
    
    if ($hero && !empty($hero['gambar'])) {
        // --- PERUBAHAN: PATH HAPUS GAMBAR ---
        $file_path = 'assets/img/' . basename($hero['gambar']);
        if (file_exists($file_path)) {
            unlink($file_path); // Hapus file gambar
        }
    }

    // Hapus data dari database
    $sql_delete = "DELETE FROM pahlawan WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id_to_delete);
    if ($stmt->execute()) {
        $success_message = "Data pahlawan berhasil dihapus.";
    } else {
        $error_message = "Gagal menghapus data.";
    }
    header("Location: index.php?message=" . urlencode($success_message));
    exit;
}

// 2. TAMBAH & EDIT (CREATE & UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama = $_POST['nama_pahlawan'];
    $daerah = $_POST['daerah'];
    $jasa = $_POST['jasa'];
    $gambar_path = null;

    // Proses Upload Gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['gambar']['tmp_name'];
        $file_name = basename($_FILES['gambar']['name']);
        // Sanitasi nama file
        $file_name = preg_replace("/[^A-Z0-9._-]/i", '_', $file_name);
        // --- PERUBAHAN: PATH UPLOAD GAMBAR ---
        $dest_path = 'assets/img/' . $file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $gambar_path = $file_name; // Simpan hanya nama filenya
        }
    }

    if ($id > 0) { // MODE EDIT
        $sql = "UPDATE pahlawan SET nama_pahlawan=?, daerah=?, jasa=?";
        $params = [$nama, $daerah, $jasa];
        $types = "sss";
        
        if ($gambar_path) {
            $sql .= ", gambar=?";
            $params[] = $gambar_path;
            $types .= "s";
        }
        $sql .= " WHERE id=?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $success_message = "Data pahlawan berhasil diperbarui.";
        } else {
            $error_message = "Gagal memperbarui data.";
        }

    } else { // MODE TAMBAH
        if ($gambar_path) {
            $sql = "INSERT INTO pahlawan (nama_pahlawan, daerah, jasa, gambar) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $nama, $daerah, $jasa, $gambar_path);
        } else {
            $sql = "INSERT INTO pahlawan (nama_pahlawan, daerah, jasa) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nama, $daerah, $jasa);
        }
        
        if ($stmt->execute()) {
            $success_message = "Pahlawan baru berhasil ditambahkan.";
        } else {
             $error_message = "Gagal menambah data.";
        }
    }
    
    header("Location: index.php?message=" . urlencode($success_message));
    exit;
}

// 3. AMBIL DATA UNTUK EDIT
if (isset($_GET['edit_id'])) {
    $id_to_edit = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM pahlawan WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    $hero_to_edit = $result->fetch_assoc();
}

// 4. AMBIL SEMUA DATA (READ)
 $sql_read = "SELECT * FROM pahlawan ORDER BY id DESC";
 $result = $conn->query($sql_read);
if ($result) {
    $heroes = $result->fetch_all(MYSQLI_ASSOC);
}

 $conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Pahlawan Indonesia</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .img-preview {
            max-width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-50 to-white min-h-screen">
    <!-- Header -->
<!-- Header -->
<header class="bg-red-700 text-white shadow-lg">
    <div class="container mx-auto px-4 py-6 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="index.php" class="bg-white text-red-700 font-bold py-2 px-4 rounded-lg hover:bg-red-100 transition duration-300 flex items-center">
                <i class="fas fa-home mr-2"></i> Beranda
            </a>
            <a href="dashboard.php" class="bg-red-800 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-900 transition duration-300 flex items-center">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
        </div>
        <div>
            <button onclick="openModal()" class="bg-white text-red-700 font-bold py-2 px-4 rounded-lg hover:bg-red-100 transition duration-300 flex items-center">
                <i class="fas fa-plus-circle mr-2"></i> Tambah Pahlawan
            </button>
        </div>
    </div>
</header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Notifikasi -->
        <?php if (isset($_GET['message']) && !empty($_GET['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && !empty($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_GET['error']); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Daftar Pahlawan</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Gambar</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Pahlawan</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Asal Daerah</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Jasa</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($heroes)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-10">
                                        <i class="fas fa-inbox text-6xl text-gray-300"></i>
                                        <p class="text-gray-500 mt-4">Belum ada data pahlawan. Tambahkan pahlawan pertama!</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($heroes as $hero): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-5 py-5">
                                            <!-- --- PERUBAHAN: PATH TAMPILAN GAMBAR DI TABEL --- -->
                                            <img src="<?php echo !empty($hero['gambar']) ? 'assets/img/' . htmlspecialchars($hero['gambar']) : 'https://via.placeholder.com/80x80.png?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($hero['nama_pahlawan']); ?>" class="w-16 h-16 object-cover rounded-lg">
                                        </td>
                                        <td class="px-5 py-5 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($hero['nama_pahlawan']); ?></td>
                                        <td class="px-5 py-5 text-sm"><?php echo htmlspecialchars($hero['daerah']); ?></td>
                                        <td class="px-5 py-5 text-sm"><?php echo htmlspecialchars($hero['jasa']); ?></td>
                                        <td class="px-5 py-5 text-sm">
                                            <a href="admin.php?edit_id=<?php echo $hero['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="admin.php?delete_id=<?php echo $hero['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Form (Create/Update) -->
    <div id="heroModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900" id="modalTitle">
                    <?php echo $hero_to_edit ? 'Edit Data Pahlawan' : 'Tambah Pahlawan Baru'; ?>
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="admin.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $hero_to_edit['id'] ?? ''; ?>">
                
                <div>
                    <label for="nama_pahlawan" class="block text-sm font-medium text-gray-700">Nama Pahlawan</label>
                    <input type="text" id="nama_pahlawan" name="nama_pahlawan" required
                           value="<?php echo htmlspecialchars($hero_to_edit['nama_pahlawan'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div>
                    <label for="daerah" class="block text-sm font-medium text-gray-700">Asal Daerah</label>
                    <input type="text" id="daerah" name="daerah" required
                           value="<?php echo htmlspecialchars($hero_to_edit['daerah'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div>
                    <label for="jasa" class="block text-sm font-medium text-gray-700">Jasa</label>
                    <textarea id="jasa" name="jasa" rows="3" required
                              class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"><?php echo htmlspecialchars($hero_to_edit['jasa'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label for="gambar" class="block text-sm font-medium text-gray-700">Gambar (Opsional)</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    <?php if ($hero_to_edit && !empty($hero_to_edit['gambar'])): ?>
                        <p class="text-xs text-gray-500 mt-2">Gambar saat ini:</p>
                        <!-- --- PERUBAHAN: PATH TAMPILAN GAMBAR DI MODAL --- -->
                        <img src="assets/img/<?php echo htmlspecialchars($hero_to_edit['gambar']); ?>" alt="Current Image" class="img-preview">
                    <?php endif; ?>
                    <img id="imagePreview" class="img-preview hidden" alt="Preview Gambar">
                </div>
                
                <div class="flex justify-end pt-4">
                    <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 hover:bg-gray-400 transition duration-300">
                        Batal
                    </button>
                    <button type="submit" class="bg-red-700 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-800 transition duration-300">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('heroModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('heroModal').classList.add('hidden');
            <?php if (!$hero_to_edit): ?>
                document.querySelector('form').reset();
                document.getElementById('imagePreview').classList.add('hidden');
            <?php endif; ?>
        }
        
        <?php if ($hero_to_edit): ?>
            document.addEventListener('DOMContentLoaded', () => {
                openModal();
            });
        <?php endif; ?>

        const imageInput = document.getElementById('gambar');
        const imagePreview = document.getElementById('imagePreview');

        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    imagePreview.src = event.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>