<?php
session_start();

// --- KONFIGURASI DATABASE ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'pahlawan_db');

// --- KONEKSI DATABASE ---
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// --- CEK LOGIN (sederhana) ---
// Dalam implementasi real, gunakan sistem login yang proper
$is_logged_in = true; // Asumsi sudah login

// --- AMBIL DATA STATISTIK ---
$stats = [];

// Total pahlawan
$sql_total = "SELECT COUNT(*) as total FROM pahlawan";
$result = $conn->query($sql_total);
$stats['total_pahlawan'] = $result->fetch_assoc()['total'];

// Pahlawan per daerah (top 5)
$sql_daerah = "SELECT daerah, COUNT(*) as jumlah FROM pahlawan GROUP BY daerah ORDER BY jumlah DESC LIMIT 5";
$result = $conn->query($sql_daerah);
$stats['top_daerah'] = $result->fetch_all(MYSQLI_ASSOC);

// Pahlawan terbaru (5 terbaru)
$sql_terbaru = "SELECT nama_pahlawan, daerah, created_at FROM pahlawan ORDER BY id DESC LIMIT 5";
$result = $conn->query($sql_terbaru);
$stats['terbaru'] = $result->fetch_all(MYSQLI_ASSOC);

// Total daerah unik
$sql_daerah_unik = "SELECT COUNT(DISTINCT daerah) as total_daerah FROM pahlawan";
$result = $conn->query($sql_daerah_unik);
$stats['total_daerah'] = $result->fetch_assoc()['total_daerah'];

// Data untuk chart (pahlawan per bulan)
$sql_chart = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as bulan,
                COUNT(*) as jumlah
              FROM pahlawan 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY bulan";
$result = $conn->query($sql_chart);
$stats['chart_data'] = $result->fetch_all(MYSQLI_ASSOC);

// Aktivitas terakhir (gabungan semua aksi)
$sql_aktivitas = "SELECT 
                    'ditambahkan' as tipe,
                    nama_pahlawan,
                    created_at as waktu
                  FROM pahlawan 
                  UNION ALL
                  SELECT 
                    'diedit' as tipe,
                    nama_pahlawan,
                    updated_at as waktu
                  FROM pahlawan 
                  WHERE updated_at IS NOT NULL
                  ORDER BY waktu DESC 
                  LIMIT 10";
$result = $conn->query($sql_aktivitas);
$stats['aktivitas'] = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Galeri Pahlawan</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .bg-gradient-sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }
        .active-nav {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #dc2626;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Main Container -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-gradient-sidebar text-white flex-shrink-0 hidden md:flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="bg-red-600 p-2 rounded-lg">
                        <i class="fas fa-shield-alt text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Galeri Pahlawan</h1>
                        <p class="text-xs text-gray-400">Admin Dashboard</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg active-nav">
                            <i class="fas fa-tachometer-alt w-6"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                            <i class="fas fa-users w-6"></i>
                            <span>Kelola Pahlawan</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                            <i class="fas fa-home w-6"></i>
                            <span>Landing Page</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                            <i class="fas fa-chart-bar w-6"></i>
                            <span>Statistik</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                            <i class="fas fa-cog w-6"></i>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                </ul>

                <!-- User Profile -->
                <div class="mt-auto p-4 border-t border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="font-medium">Administrator</p>
                            <p class="text-xs text-gray-400">Admin</p>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white border-b border-gray-200 p-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile menu button -->
                        <button id="mobileMenuButton" class="md:hidden text-gray-600">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 hover:text-red-600">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-600 rounded-full"></span>
                        </button>
                        
                        <!-- User dropdown -->
                        <div class="relative">
                            <button id="userDropdownButton" class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="hidden md:inline">Admin</span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            
                            <!-- Dropdown menu -->
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-2 z-50">
                                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profil
                                </a>
                                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Pengaturan
                                </a>
                                <div class="border-t my-1"></div>
                                <a href="#" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl p-6 text-white mb-6 shadow-lg">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold mb-2">Selamat Datang, Administrator!</h1>
                            <p class="text-red-100">Kelola data pahlawan dengan mudah melalui dashboard ini.</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <a href="admin.php" class="bg-white text-red-600 font-semibold px-6 py-3 rounded-lg hover:bg-red-50 transition inline-flex items-center">
                                <i class="fas fa-plus-circle mr-2"></i>Tambah Pahlawan Baru
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Pahlawan -->
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Total Pahlawan</p>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_pahlawan']; ?></p>
                            </div>
                            <div class="bg-red-100 p-3 rounded-full">
                                <i class="fas fa-users text-red-600 text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center text-green-600 text-sm">
                                <i class="fas fa-arrow-up mr-1"></i>
                                <span>12% dari bulan lalu</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Daerah -->
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Daerah Asal</p>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_daerah']; ?></p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-map-marker-alt text-blue-600 text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="#" class="text-blue-600 text-sm font-medium hover:text-blue-800">
                                Lihat semua daerah <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pahlawan Baru Bulan Ini -->
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Bulan Ini</p>
                                <p class="text-3xl font-bold text-gray-800">3</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-calendar-plus text-green-600 text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center text-gray-600 text-sm">
                                <i class="fas fa-history mr-1"></i>
                                <span>3 pahlawan baru</span>
                            </div>
                        </div>
                    </div>

                    <!-- Gambar Terupload -->
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Dengan Gambar</p>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_pahlawan']; ?></p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-image text-purple-600 text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center text-gray-600 text-sm">
                                <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                <span>Semua memiliki gambar</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Chart -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">Statistik Penambahan Pahlawan</h3>
                            <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                                <option>6 Bulan Terakhir</option>
                                <option>1 Tahun Terakhir</option>
                                <option>Semua Waktu</option>
                            </select>
                        </div>
                        <div class="h-64">
                            <canvas id="pahlawanChart"></canvas>
                        </div>
                    </div>

                    <!-- Top Daerah -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-6">Top 5 Daerah Asal</h3>
                        <div class="space-y-4">
                            <?php if (!empty($stats['top_daerah'])): ?>
                                <?php foreach ($stats['top_daerah'] as $index => $daerah): ?>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center mr-3">
                                                <?php echo $index + 1; ?>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($daerah['daerah']); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">
                                                <?php echo $daerah['jumlah']; ?> pahlawan
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">Belum ada data daerah</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Latest Heroes -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Aktivitas Terakhir -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">Aktivitas Terakhir</h3>
                            <a href="#" class="text-red-600 text-sm font-medium hover:text-red-800">
                                Lihat semua <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="space-y-4">
                            <?php if (!empty($stats['aktivitas'])): ?>
                                <?php foreach ($stats['aktivitas'] as $aktivitas): ?>
                                    <div class="flex items-start">
                                        <div class="mr-3 mt-1">
                                            <?php if ($aktivitas['tipe'] == 'ditambahkan'): ?>
                                                <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-plus text-sm"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-gray-800">
                                                <span class="font-medium"><?php echo htmlspecialchars($aktivitas['nama_pahlawan']); ?></span>
                                                <?php echo $aktivitas['tipe'] == 'ditambahkan' ? 'ditambahkan' : 'diperbarui'; ?>
                                            </p>
                                            <p class="text-gray-500 text-sm">
                                                <?php echo date('d M Y H:i', strtotime($aktivitas['waktu'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">Belum ada aktivitas</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pahlawan Terbaru -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">Pahlawan Terbaru</h3>
                            <a href="admin.php" class="text-red-600 text-sm font-medium hover:text-red-800">
                                Lihat semua <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="space-y-4">
                            <?php if (!empty($stats['terbaru'])): ?>
                                <?php foreach ($stats['terbaru'] as $pahlawan): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($pahlawan['nama_pahlawan']); ?></p>
                                                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($pahlawan['daerah']); ?></p>
                                            </div>
                                        </div>
                                        <span class="text-gray-500 text-sm">
                                            <?php echo date('d M', strtotime($pahlawan['created_at'])); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">Belum ada pahlawan</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Aksi Cepat</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <a href="admin.php" class="bg-red-50 border border-red-200 rounded-lg p-4 text-center hover:bg-red-100 transition">
                            <div class="text-red-600 text-2xl mb-2">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <p class="font-medium text-gray-800">Tambah Pahlawan</p>
                            <p class="text-gray-500 text-sm mt-1">Tambah data baru</p>
                        </a>
                        <a href="admin.php" class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center hover:bg-blue-100 transition">
                            <div class="text-blue-600 text-2xl mb-2">
                                <i class="fas fa-edit"></i>
                            </div>
                            <p class="font-medium text-gray-800">Edit Data</p>
                            <p class="text-gray-500 text-sm mt-1">Perbarui informasi</p>
                        </a>
                        <a href="#" class="bg-green-50 border border-green-200 rounded-lg p-4 text-center hover:bg-green-100 transition">
                            <div class="text-green-600 text-2xl mb-2">
                                <i class="fas fa-download"></i>
                            </div>
                            <p class="font-medium text-gray-800">Ekspor Data</p>
                            <p class="text-gray-500 text-sm mt-1">Download sebagai CSV</p>
                        </a>
                        <a href="index.php" class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center hover:bg-purple-100 transition">
                            <div class="text-purple-600 text-2xl mb-2">
                                <i class="fas fa-external-link-alt"></i>
                            </div>
                            <p class="font-medium text-gray-800">Lihat Website</p>
                            <p class="text-gray-500 text-sm mt-1">Kunjungi landing page</p>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar (Hidden by default) -->
    <div id="mobileSidebar" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex">
            <div class="w-64 bg-gradient-sidebar text-white h-screen">
                <div class="p-4 flex justify-between items-center border-b border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="bg-red-600 p-2 rounded-lg">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span class="font-bold">Dashboard</span>
                    </div>
                    <button id="closeMobileMenu" class="text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <nav class="p-4">
                    <ul class="space-y-2">
                        <li><a href="dashboard.php" class="block p-3 rounded-lg active-nav">Dashboard</a></li>
                        <li><a href="admin.php" class="block p-3 rounded-lg hover:bg-gray-800">Kelola Pahlawan</a></li>
                        <li><a href="index.php" class="block p-3 rounded-lg hover:bg-gray-800">Landing Page</a></li>
                        <li><a href="#" class="block p-3 rounded-lg hover:bg-gray-800">Statistik</a></li>
                        <li><a href="#" class="block p-3 rounded-lg hover:bg-gray-800">Pengaturan</a></li>
                    </ul>
                </nav>
            </div>
            <div class="flex-1" id="mobileSidebarOverlay"></div>
        </div>
    </div>

    <script>
        // Mobile Menu Toggle
        document.getElementById('mobileMenuButton').addEventListener('click', function() {
            document.getElementById('mobileSidebar').classList.remove('hidden');
        });

        document.getElementById('closeMobileMenu').addEventListener('click', function() {
            document.getElementById('mobileSidebar').classList.add('hidden');
        });

        document.getElementById('mobileSidebarOverlay').addEventListener('click', function() {
            document.getElementById('mobileSidebar').classList.add('hidden');
        });

        // User Dropdown
        document.getElementById('userDropdownButton').addEventListener('click', function() {
            document.getElementById('userDropdown').classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const button = document.getElementById('userDropdownButton');
            
            if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Chart.js Implementation
        const ctx = document.getElementById('pahlawanChart').getContext('2d');
        
        // Data dari PHP
        const chartData = <?php echo json_encode($stats['chart_data']); ?>;
        
        // Siapkan labels dan data
        const labels = chartData.map(item => {
            const date = new Date(item.bulan + '-01');
            return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
        });
        
        const data = chartData.map(item => item.jumlah);
        
        // Buat chart
        const pahlawanChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Pahlawan',
                    data: data,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Auto-refresh dashboard setiap 30 detik (opsional)
        // setTimeout(() => {
        //     window.location.reload();
        // }, 30000);
    </script>
</body>
</html>