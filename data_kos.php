<?php
// DATA KOS YANG KONSISTEN UNTUK SEMUA HALAMAN
$semua_kos = [];

// Data kos lengkap untuk 45 kos - HARGA TETAP DAN KONSISTEN
for ($i = 1; $i <= 45; $i++) {
    // Hanya putra atau putri (tidak ada campuran)
    $jenis = ($i % 2 == 0) ? 'putra' : 'putri';
    
    // Daftar nama kos yang konsisten (gunakan nama yang sama dengan di dashboard)
    $nama_kos_list = [
        'Harmoni', 'Merpati', 'Sejahtera', 'Anggrek', 'Dahlia', 'Kenanga', 
        'Seruni', 'Cendana', 'Teratai', 'Mawar', 'Melati', 'Sakura',
        'Raflesia', 'Edelweis', 'Kamboja', 'Lavender', 'Tulip', 'Orchid',
        'Bougenville', 'Flamingo', 'Peacock', 'Swan', 'Dove', 'Eagle',
        'Phoenix', 'Dragon', 'Unicorn', 'Pegasus', 'Griffin', 'Centaur',
        'Saturn', 'Jupiter', 'Mars', 'Venus', 'Mercury', 'Neptune',
        'Uranus', 'Pluto', 'Earth', 'Moon', 'Sun', 'Star', 'Galaxy',
        'Comet', 'Meteor'
    ];
    
    // Daftar lokasi yang konsisten
    $lokasi_list = ['Karangwangkal', 'Grendeng', 'Purwokerto Timur', 'Kecamatan Salaman', 'Kaliwungu'];
    
    // Daftar pemilik yang konsisten
    $pemilik_list = ['Budi Santoso', 'Sari Dewi', 'Agus Wijaya', 'Ratna Sari', 'Joko Prasetyo'];
    
    // HARGA TETAP DAN KONSISTEN - gunakan harga bulat (tanpa koma)
    $harga_list = [
        5000000, 5200000, 5400000, 5600000, 5800000, 6000000,
        6200000, 6400000, 6600000, 6800000, 7000000, 7200000,
        7400000, 7600000, 7800000, 8000000, 8200000, 8400000,
        8600000, 8800000, 9000000, 9200000, 9400000, 9600000,
        9800000, 10000000, 10200000, 10400000, 10600000, 10800000,
        11000000, 11200000, 11400000, 11600000, 11800000, 12000000,
        12200000, 12400000, 12600000, 12800000, 13000000, 13200000,
        13400000, 13600000, 13800000
    ];
    
    // Sisa kamar yang konsisten berdasarkan ID
    $sisa_kamar = ($i % 5) + 1; // 1-5 kamar
    
    // Rating yang konsisten berdasarkan ID
    $rating_base = 3.5;
    $rating = $rating_base + (($i % 15) * 0.1);
    if ($rating > 5.0) $rating = 5.0;
    
    // Tipe kamar yang konsisten
    $tipe_kamar = ($i % 3 == 0) ? 'Kamar Mandi Luar' : 'Kamar Mandi Dalam';
    
    // Ukuran kamar yang konsisten
    $ukuran_kamar = ($i % 4) + 3; // 3-6 meter persegi

    // Fasilitas yang mungkin
    $fasilitas_options = [
        ['wifi', 'parkir_luas'],
        ['wifi', 'ac', 'km_dalam'],
        ['wifi', 'laundry', 'dapur'],
        ['ac', 'km_dalam', 'laundry'],
        ['wifi', 'ac', 'laundry', 'dapur', 'keamanan_24_jam']
    ];

    // Pilih fasilitas berdasarkan ID
    $fasilitas = $fasilitas_options[$i % count($fasilitas_options)];
    
    // Data kos lengkap - PASTIKAN SEMUA DATA KONSISTEN
    $semua_kos[$i] = [
        'id' => $i,
        'nama' => $nama_kos_list[($i - 1) % count($nama_kos_list)],
        'alamat' => $lokasi_list[($i - 1) % count($lokasi_list)],
        'alamat_lengkap' => 'Jl. Contoh No.' . $i . ', ' . $lokasi_list[($i - 1) % count($lokasi_list)] . ', Purwokerto',
        'harga' => $harga_list[$i - 1], // Gunakan harga dari list yang tetap
        'deskripsi' => "Kos yang nyaman dan strategis untuk mahasiswa. Fasilitas lengkap dengan akses mudah ke kampus dan pusat kota. Lingkungan yang aman dan nyaman untuk belajar.",
        'tipe' => $tipe_kamar,
        'jenis_kelamin' => $jenis,
        'ukuran' => $ukuran_kamar,
        'kapasitas' => ($i % 3) + 1, // 1-3 orang
        'pemilik_nama' => $pemilik_list[($i - 1) % count($pemilik_list)],
        'pemilik_telepon' => '0812' . str_pad(($i * 1234567) % 10000000, 7, '0', STR_PAD_LEFT),
        'lat' => -7.423 + (($i % 10) / 1000),
        'lng' => 109.236 + (($i % 10) / 1000),
        'rating' => number_format($rating, 1),
        'total_review' => 5 + ($i % 20),
        'sisa_kamar' => $sisa_kamar,
        'fasilitas' => $fasilitas // Tambahkan fasilitas
    ];
}

// FUNGSI UNTUK MENGAMBIL KOS BERDASARKAN ID
function get_kos_by_id($id) {
    global $semua_kos;
    return isset($semua_kos[$id]) ? $semua_kos[$id] : null;
}

// FUNGSI UNTUK MENGAMBIL KOS BERDASARKAN RANGE ID
function get_kos_by_range($start, $end) {
    global $semua_kos;
    $result = [];
    
    for ($i = $start; $i <= $end && $i <= count($semua_kos); $i++) {
        if (isset($semua_kos[$i])) {
            $result[] = $semua_kos[$i];
        }
    }
    
    return $result;
}

// FUNGSI UNTUK MENGAMBIL SEMUA KOS
function get_all_kos() {
    global $semua_kos;
    return $semua_kos;
}

// Tambahkan fungsi get_filtered_kos() jika belum ada
if (!function_exists('get_filtered_kos')) {
    function get_filtered_kos($filters = []) {
        $all_kos = get_all_kos();
        $filtered = [];
        
        foreach ($all_kos as $kos) {
            $match = true;
            
            // Filter berdasarkan keyword
            if (!empty($filters['keyword'])) {
                $keyword = strtolower($filters['keyword']);
                $nama = strtolower($kos['nama']);
                $alamat = strtolower($kos['alamat']);
                
                if (strpos($nama, $keyword) === false && strpos($alamat, $keyword) === false) {
                    $match = false;
                }
            }
            
            // Filter berdasarkan harga
            if (!empty($filters['harga'])) {
                $harga_filter = (int)$filters['harga'];
                $harga = $kos['harga'];
                
                switch ($harga_filter) {
                    case 5000000:
                        if ($harga >= 5000000) $match = false;
                        break;
                    case 10000000:
                        if ($harga < 5000000 || $harga > 10000000) $match = false;
                        break;
                    case 15000000:
                        if ($harga <= 10000000 || $harga > 15000000) $match = false;
                        break;
                    case 20000000:
                        if ($harga <= 15000000) $match = false;
                        break;
                }
            }
            
            // Filter berdasarkan fasilitas
            if (!empty($filters['fasilitas'])) {
                if (!isset($kos['fasilitas']) || !is_array($kos['fasilitas'])) {
                    $match = false;
                } elseif (!in_array($filters['fasilitas'], $kos['fasilitas'])) {
                    $match = false;
                }
            }
            
            // Filter berdasarkan jenis
            if (!empty($filters['jenis'])) {
                if ($kos['jenis_kelamin'] != $filters['jenis']) {
                    $match = false;
                }
            }
            
            // Filter berdasarkan jarak
            if (!empty($filters['jarak'])) {
                $jarak_filter = (int)$filters['jarak'];
                $jarak = $kos['jarak_ke_kampus'] ?? 0;
                
                switch ($jarak_filter) {
                    case 500:
                        if ($jarak > 500) $match = false;
                        break;
                    case 1000:
                        if ($jarak <= 500 || $jarak > 1000) $match = false;
                        break;
                    case 5000:
                        if ($jarak <= 1000 || $jarak > 5000) $match = false;
                        break;
                }
            }
            
            if ($match) {
                $filtered[] = $kos;
            }
        }
        
        return $filtered;
    }
}

// DEBUG: Untuk memastikan data konsisten
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<pre>";
    echo "Data Kos untuk ID 1-4:\n";
    for ($i = 1; $i <= 4; $i++) {
        $kos = $semua_kos[$i];
        echo "ID: {$kos['id']} | Nama: {$kos['nama']} | Harga: {$kos['harga']} | Alamat: {$kos['alamat']} | Sisa: {$kos['sisa_kamar']} | Jenis: {$kos['jenis_kelamin']} | Fasilitas: " . implode(', ', $kos['fasilitas']) . "\n";
    }
    echo "\nData Kos untuk ID 5-13:\n";
    for ($i = 5; $i <= 13; $i++) {
        $kos = $semua_kos[$i];
        echo "ID: {$kos['id']} | Nama: {$kos['nama']} | Harga: {$kos['harga']} | Alamat: {$kos['alamat']} | Sisa: {$kos['sisa_kamar']} | Jenis: {$kos['jenis_kelamin']} | Fasilitas: " . implode(', ', $kos['fasilitas']) . "\n";
    }
    echo "</pre>";
    exit;
}
?>