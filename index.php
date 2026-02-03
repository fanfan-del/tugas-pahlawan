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

// Ambil 6 pahlawan terbaru untuk ditampilkan di landing page
$sql_heroes = "SELECT * FROM pahlawan ORDER BY id DESC LIMIT 6";
$result = $conn->query($sql_heroes);
$heroes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Pahlawan Indonesia - Beranda</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        .hero-title {
            font-family: 'Montserrat', sans-serif;
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23dc2626' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gradient-to-b from-red-50 to-white">
    <!-- Header/Navigation -->
    <header class="sticky top-0 z-50 bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="bg-red-600 text-white p-2 rounded-lg">
                        <i class="fas fa-landmark text-xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-red-700 hero-title">Galeri Pahlawan</h1>
                </div>
                
                <nav class="hidden md:flex space-x-8">
                    <a href="#home" class="text-red-700 font-medium hover:text-red-800 transition">Beranda</a>
                    <a href="#gallery" class="text-gray-700 hover:text-red-700 transition">Galeri</a>
                    <a href="#about" class="text-gray-700 hover:text-red-700 transition">Tentang</a>
                    <a href="dashboard.php" class="bg-red-800 text-white px-5 py-2 rounded-lg hover:bg-red-900 transition font-medium">
        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
    </a>
                    <a href="admin.php" class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition font-medium">
                        <i class="fas fa-tools mr-2"></i>Kelola Data
                    </a>
                </nav>
                
                <!-- Mobile menu button -->
                <button id="mobile-menu-button" class="md:hidden text-red-700">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden mt-4 space-y-4 pb-4">
                <a href="#home" class="block text-red-700 font-medium">Beranda</a>
                <a href="dashboard.php" class="block bg-red-800 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition font-medium text-center">
        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
    </a>
                <a href="#gallery" class="block text-gray-700">Galeri</a>
                <a href="#about" class="block text-gray-700">Tentang</a>
                <a href="admin.php" class="block bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition font-medium text-center">
                    <i class="fas fa-tools mr-2"></i>Kelola Data
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="py-16 md:py-24 bg-pattern">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 hero-title">
                        Mengenal <span class="text-red-600">Pahlawan</span> Indonesia
                    </h2>
                    <p class="text-lg text-gray-700 mb-8">
                        Jelajahi kisah perjuangan dan jasa para pahlawan dari berbagai daerah di Indonesia. 
                        Mereka adalah pelopor kemerdekaan dan pembangun bangsa yang patut kita kenang.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="#gallery" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium">
                            <i class="fas fa-images mr-2"></i>Lihat Galeri
                        </a>
                        <a href="admin.php" class="bg-white text-red-600 border border-red-600 px-6 py-3 rounded-lg hover:bg-red-50 transition font-medium">
                            <i class="fas fa-plus-circle mr-2"></i>Tambah Pahlawan
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <div class="relative">
                        <div class="bg-red-100 rounded-2xl p-2 shadow-xl">
                            <img src="assets/img/uang-baru-10-ribu.jpg" 
                                 alt="Patung Pahlawan" 
                                 class="rounded-xl w-full h-64 md:h-80 object-cover">
                        </div>
                        <div class="absolute -bottom-4 -left-4 bg-white p-4 rounded-xl shadow-lg">
                            <div class="flex items-center">
                                <div class="bg-red-100 p-3 rounded-lg mr-3">
                                    <i class="fas fa-users text-red-600 text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo count($heroes); ?>+</p>
                                    <p class="text-gray-600">Pahlawan Terdaftar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 hero-title">Galeri Pahlawan</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Kumpulan pahlawan nasional dari berbagai daerah di Indonesia
                </p>
            </div>
            
            <?php if (empty($heroes)): ?>
                <div class="text-center py-12 bg-gray-50 rounded-2xl">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum ada data pahlawan</h3>
                    <p class="text-gray-500 mb-6">Silakan tambahkan pahlawan pertama melalui halaman admin</p>
                    <a href="admin.php" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium inline-flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Pahlawan Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($heroes as $hero): ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover border border-gray-100">
                            <div class="h-48 overflow-hidden">
                                <!-- PATH GAMBAR -->
                                <img src="<?php echo !empty($hero['gambar']) ? 'assets/img/' . htmlspecialchars($hero['gambar']) : 'https://images.unsplash.com/photo-1541336032412-2048a678540d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'; ?>" 
                                     alt="<?php echo htmlspecialchars($hero['nama_pahlawan']); ?>"
                                     class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                            </div>
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($hero['nama_pahlawan']); ?></h3>
                                    <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">
                                        <?php echo htmlspecialchars($hero['daerah']); ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 mb-4 line-clamp-3">
                                    <?php echo htmlspecialchars($hero['jasa']); ?>
                                </p>
                                <div class="flex justify-between items-center">
                                    <a href="admin.php?edit_id=<?php echo $hero['id']; ?>" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        <i class="fas fa-info-circle mr-1"></i>Detail
                                    </a>
                                    <a href="admin.php" class="text-gray-500 hover:text-red-600">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-12">
                    <a href="admin.php" class="inline-flex items-center bg-white text-red-600 border border-red-600 px-6 py-3 rounded-lg hover:bg-red-50 transition font-medium">
                        <i class="fas fa-list mr-2"></i>Lihat Semua Pahlawan
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 bg-gradient-to-br from-red-50 to-white">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 hero-title">Tentang Galeri Ini</h2>
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="mb-6">
                        <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-flag text-red-600 text-2xl"></i>
                        </div>
                        <p class="text-lg text-gray-700 mb-6">
                            Galeri Pahlawan Indonesia adalah platform digital yang bertujuan untuk melestarikan 
                            dan mengenang jasa-jasa para pahlawan dari seluruh penjuru tanah air. 
                            Kami berkomitmen untuk menyebarkan nilai-nilai perjuangan dan patriotisme 
                            kepada generasi muda.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="text-center p-4">
                            <i class="fas fa-history text-red-500 text-3xl mb-3"></i>
                            <h4 class="font-bold text-gray-900 mb-2">Melestarikan Sejarah</h4>
                            <p class="text-gray-600 text-sm">Mengabadikan kisah perjuangan para pahlawan</p>
                        </div>
                        <div class="text-center p-4">
                            <i class="fas fa-book-open text-red-500 text-3xl mb-3"></i>
                            <h4 class="font-bold text-gray-900 mb-2">Edukasi Publik</h4>
                            <p class="text-gray-600 text-sm">Sumber pembelajaran bagi masyarakat</p>
                        </div>
                        <div class="text-center p-4">
                            <i class="fas fa-hands-helping text-red-500 text-3xl mb-3"></i>
                            <h4 class="font-bold text-gray-900 mb-2">Partisipasi Aktif</h4>
                            <p class="text-gray-600 text-sm">Masyarakat dapat berkontribusi menambah data</p>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 rounded-xl p-6 border border-red-100">
                        <h4 class="font-bold text-gray-900 mb-3">Ingin Berkontribusi?</h4>
                        <p class="text-gray-700 mb-4">
                            Anda dapat menambahkan data pahlawan baru atau melengkapi informasi yang sudah ada 
                            melalui halaman administrasi kami.
                        </p>
                        <a href="admin.php" class="inline-flex items-center bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium">
                            <i class="fas fa-tools mr-2"></i>Mulai Kelola Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-red-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="bg-white p-2 rounded-lg">
                            <i class="fas fa-landmark text-red-700 text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold hero-title">Galeri Pahlawan</h3>
                    </div>
                    <p class="text-red-100 max-w-md">
                        Platform digital untuk melestarikan sejarah dan jasa para pahlawan Indonesia.
                    </p>
                </div>
                
                <div class="text-center md:text-right">
                    <h4 class="text-lg font-bold mb-4">Navigasi Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-red-100 hover:text-white transition">Beranda</a></li>
                        <li><a href="#gallery" class="text-red-100 hover:text-white transition">Galeri</a></li>
                        <li><a href="#about" class="text-red-100 hover:text-white transition">Tentang</a></li>
                        <li><a href="admin.php" class="text-red-100 hover:text-white transition font-medium">Kelola Data</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-red-700 mt-8 pt-8 text-center text-red-200">
                <p>&copy; <?php echo date('Y'); ?> Galeri Pahlawan Indonesia. Semua hak dilindungi.</p>
                <p class="mt-2 text-sm">Dibangun dengan <i class="fas fa-heart text-red-300"></i> untuk Indonesia</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                if(this.getAttribute('href') === '#') return;
                
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    const mobileMenu = document.getElementById('mobile-menu');
                    if(!mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }
                }
            });
        });

        // Add active class to nav links on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('nav a[href^="#"]');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if(scrollY >= (sectionTop - 100)) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('text-red-700', 'font-medium');
                link.classList.add('text-gray-700');
                if(link.getAttribute('href') === `#${current}`) {
                    link.classList.remove('text-gray-700');
                    link.classList.add('text-red-700', 'font-medium');
                }
            });
        });
    </script>
</body>
</html>