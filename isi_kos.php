<?php
// data_kos.php
// File ini berisi data kos yang konsisten (default tetap tidak berubah)

/**
 * Mendapatkan data kos berdasarkan range ID
 * @param int $start ID awal
 * @param int $end ID akhir
 * @return array Data kos dalam range tersebut
 */
function get_kos_by_range($start, $end) {
    $all_kos = get_all_kos();
    $result = [];
    
    for ($i = $start; $i <= $end; $i++) {
        if (isset($all_kos[$i])) {
            $result[] = $all_kos[$i];
        }
    }
    
    return $result;
}

/**
 * Mendapatkan data kos berdasarkan ID spesifik
 * @param int $id ID kos
 * @return array|null Data kos atau null jika tidak ditemukan
 */
function get_kos_by_id($id) {
    $all_kos = get_all_kos();
    return isset($all_kos[$id]) ? $all_kos[$id] : null;
}

/**
 * Mendapatkan semua data kos (45 data)
 * @return array Semua data kos
 */
function get_all_kos() {
    // Data kos yang konsisten dan tidak berubah
    $kos_data = [
        // ID 1-4: Untuk rekomendasi di dashboard
        1 => [
            'id' => 1,
            'nama' => 'Mawar Indah',
            'alamat' => 'Jl. Karangwangkal No. 12',
            'harga' => 7500000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 350,
            'fasilitas' => ['wifi', 'km_dalam', 'parkir_luas'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos yang nyaman dengan lingkungan asri dan dekat kampus.',
            'pemilik' => 'Budi Santoso',
            'telepon' => '081234567890'
        ],
        2 => [
            'id' => 2,
            'nama' => 'Pelangi Asri',
            'alamat' => 'Jl. Grendeng Raya No. 45',
            'harga' => 8500000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 500,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos khusus putri dengan keamanan 24 jam.',
            'pemilik' => 'Siti Aisyah',
            'telepon' => '081234567891'
        ],
        3 => [
            'id' => 3,
            'nama' => 'Melati Sejahtera',
            'alamat' => 'Jl. Purwokerto Timur No. 78',
            'harga' => 6500000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 420,
            'fasilitas' => ['wifi', 'parkir_luas', 'dapur'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos dengan harga terjangkau dan fasilitas memadai.',
            'pemilik' => 'Agus Wijaya',
            'telepon' => '081234567892'
        ],
        4 => [
            'id' => 4,
            'nama' => 'Anggrek Harmoni',
            'alamat' => 'Jl. Kecamatan Salaman No. 23',
            'harga' => 9200000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 280,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry', 'dapur', 'keamanan_24_jam'],
            'sisa_kamar' => 1,
            'deskripsi' => 'Kos premium dengan fasilitas lengkap dekat kampus.',
            'pemilik' => 'Rina Dewi',
            'telepon' => '081234567893'
        ],
        
        // ID 5-13: Untuk daftar kos terbaru di dashboard
        5 => [
            'id' => 5,
            'nama' => 'Harmoni Jaya',
            'alamat' => 'Jl. Kaliwungu No. 15',
            'harga' => 7200000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 600,
            'fasilitas' => ['wifi', 'parkir_luas'],
            'sisa_kamar' => 5,
            'deskripsi' => 'Kos baru dengan konsep modern minimalis.',
            'pemilik' => 'Joko Prasetyo',
            'telepon' => '081234567894'
        ],
        6 => [
            'id' => 6,
            'nama' => 'Merpati Putih',
            'alamat' => 'Jl. Karangwangkal No. 89',
            'harga' => 8100000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 450,
            'fasilitas' => ['wifi', 'ac', 'km_dalam'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos bersih dan rapi untuk mahasiswi.',
            'pemilik' => 'Dewi Lestari',
            'telepon' => '081234567895'
        ],
        7 => [
            'id' => 7,
            'nama' => 'Sejahtera Abadi',
            'alamat' => 'Jl. Grendeng No. 56',
            'harga' => 6900000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 750,
            'fasilitas' => ['wifi', 'dapur', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos dengan suasana tenang cocok untuk belajar.',
            'pemilik' => 'Bambang Sutrisno',
            'telepon' => '081234567896'
        ],
        8 => [
            'id' => 8,
            'nama' => 'Dahlia Garden',
            'alamat' => 'Jl. Purwokerto Timur No. 34',
            'harga' => 9500000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 320,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry', 'keamanan_24_jam'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos dengan taman yang asri dan lingkungan nyaman.',
            'pemilik' => 'Lina Marlina',
            'telepon' => '081234567897'
        ],
        9 => [
            'id' => 9,
            'nama' => 'Kenanga Indah',
            'alamat' => 'Jl. Salaman No. 67',
            'harga' => 7800000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 550,
            'fasilitas' => ['wifi', 'km_dalam', 'parkir_luas'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan akses mudah ke transportasi umum.',
            'pemilik' => 'Rudi Hartono',
            'telepon' => '081234567898'
        ],
        10 => [
            'id' => 10,
            'nama' => 'Seruni Elok',
            'alamat' => 'Jl. Kaliwungu No. 41',
            'harga' => 8800000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 480,
            'fasilitas' => ['wifi', 'ac', 'laundry', 'dapur'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos dengan interior yang estetik dan nyaman.',
            'pemilik' => 'Maya Sari',
            'telepon' => '081234567899'
        ],
        11 => [
            'id' => 11,
            'nama' => 'Cendana Raya',
            'alamat' => 'Jl. Karangwangkal No. 28',
            'harga' => 7100000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 670,
            'fasilitas' => ['wifi', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos ekonomis dengan fasilitas dasar yang memadai.',
            'pemilik' => 'Ahmad Fauzi',
            'telepon' => '081234567800'
        ],
        12 => [
            'id' => 12,
            'nama' => 'Teratai Damai',
            'alamat' => 'Jl. Grendeng Raya No. 72',
            'harga' => 9000000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 380,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'keamanan_24_jam'],
            'sisa_kamar' => 1,
            'deskripsi' => 'Kos dengan lingkungan yang damai dan tenang.',
            'pemilik' => 'Sari Indah',
            'telepon' => '081234567801'
        ],
        13 => [
            'id' => 13,
            'nama' => 'Mawar Garden',
            'alamat' => 'Jl. Purwokerto Timur No. 19',
            'harga' => 8200000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 520,
            'fasilitas' => ['wifi', 'km_dalam', 'dapur', 'parkir_luas'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan konsep rumah taman yang asri.',
            'pemilik' => 'Eko Priyanto',
            'telepon' => '081234567802'
        ],
        
        // ID 14-45: Untuk halaman semua_kos.php
        14 => [
            'id' => 14,
            'nama' => 'Sakura Japan',
            'alamat' => 'Jl. Kecamatan Salaman No. 55',
            'harga' => 9600000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 300,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry', 'dapur'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos dengan desain bergaya Jepang yang unik.',
            'pemilik' => 'Yuki Tanaka',
            'telepon' => '081234567803'
        ],
        15 => [
            'id' => 15,
            'nama' => 'Raflesia Besar',
            'alamat' => 'Jl. Kaliwungu No. 33',
            'harga' => 6800000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 720,
            'fasilitas' => ['wifi', 'parkir_luas'],
            'sisa_kamar' => 5,
            'deskripsi' => 'Kos dengan kamar yang luas dan nyaman.',
            'pemilik' => 'Wawan Setiawan',
            'telepon' => '081234567804'
        ],
        16 => [
            'id' => 16,
            'nama' => 'Edelweis Alami',
            'alamat' => 'Jl. Karangwangkal No. 47',
            'harga' => 8900000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 430,
            'fasilitas' => ['wifi', 'ac', 'km_dalam'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan udara sejuk dan lingkungan alami.',
            'pemilik' => 'Nina Kusuma',
            'telepon' => '081234567805'
        ],
        17 => [
            'id' => 17,
            'nama' => 'Kamboja Indah',
            'alamat' => 'Jl. Grendeng No. 62',
            'harga' => 7300000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 580,
            'fasilitas' => ['wifi', 'dapur', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos dengan harga terjangkau dekat pusat kota.',
            'pemilik' => 'Arif Rahman',
            'telepon' => '081234567806'
        ],
        18 => [
            'id' => 18,
            'nama' => 'Lavender Wangi',
            'alamat' => 'Jl. Purwokerto Timur No. 81',
            'harga' => 9400000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 290,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry', 'keamanan_24_jam'],
            'sisa_kamar' => 1,
            'deskripsi' => 'Kos dengan aroma terapi dan suasana relaksasi.',
            'pemilik' => 'Dian Pertiwi',
            'telepon' => '081234567807'
        ],
        19 => [
            'id' => 19,
            'nama' => 'Tulip Belanda',
            'alamat' => 'Jl. Salaman No. 26',
            'harga' => 7700000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 530,
            'fasilitas' => ['wifi', 'km_dalam', 'parkir_luas'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan desain bergaya Eropa yang elegan.',
            'pemilik' => 'Hendrik Van Dijk',
            'telepon' => '081234567808'
        ],
        20 => [
            'id' => 20,
            'nama' => 'Orchid Premium',
            'alamat' => 'Jl. Kaliwungu No. 58',
            'harga' => 10500000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 250,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry', 'dapur', 'keamanan_24_jam', 'kulkas'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos premium dengan fasilitas hotel bintang.',
            'pemilik' => 'Lisa Anggraeni',
            'telepon' => '081234567809'
        ],
        
        // ID 21-30
        21 => [
            'id' => 21,
            'nama' => 'Bougenville Cantik',
            'alamat' => 'Jl. Karangwangkal No. 39',
            'harga' => 7900000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 490,
            'fasilitas' => ['wifi', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos dengan banyak tanaman hias yang indah.',
            'pemilik' => 'Tono Wijaya',
            'telepon' => '081234567810'
        ],
        22 => [
            'id' => 22,
            'nama' => 'Flamingo Pink',
            'alamat' => 'Jl. Grendeng Raya No. 64',
            'harga' => 8600000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 410,
            'fasilitas' => ['wifi', 'ac', 'km_dalam'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan warna-warna cerah dan ceria.',
            'pemilik' => 'Rina Puspita',
            'telepon' => '081234567811'
        ],
        23 => [
            'id' => 23,
            'nama' => 'Peacock Merak',
            'alamat' => 'Jl. Purwokerto Timur No. 37',
            'harga' => 7000000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 680,
            'fasilitas' => ['wifi', 'dapur', 'parkir_luas'],
            'sisa_kamar' => 5,
            'deskripsi' => 'Kos dengan desain warna-warni yang menarik.',
            'pemilik' => 'Bambang Surya',
            'telepon' => '081234567812'
        ],
        24 => [
            'id' => 24,
            'nama' => 'Swan Putih',
            'alamat' => 'Jl. Kecamatan Salaman No. 48',
            'harga' => 9200000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 330,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos dengan suasana elegan dan bersih.',
            'pemilik' => 'Mira Santoso',
            'telepon' => '081234567813'
        ],
        25 => [
            'id' => 25,
            'nama' => 'Dove Damai',
            'alamat' => 'Jl. Kaliwungu No. 22',
            'harga' => 7400000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 560,
            'fasilitas' => ['wifi', 'km_dalam', 'parkir_luas'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan suasana tenang dan damai.',
            'pemilik' => 'Andi Kurniawan',
            'telepon' => '081234567814'
        ],
        26 => [
            'id' => 26,
            'nama' => 'Eagle Tinggi',
            'alamat' => 'Jl. Karangwangkal No. 71',
            'harga' => 9800000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 270,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'dapur', 'keamanan_24_jam'],
            'sisa_kamar' => 1,
            'deskripsi' => 'Kos di lantai tinggi dengan pemandangan kota.',
            'pemilik' => 'Sari Wulandari',
            'telepon' => '081234567815'
        ],
        27 => [
            'id' => 27,
            'nama' => 'Phoenix Bangkit',
            'alamat' => 'Jl. Grendeng No. 53',
            'harga' => 8200000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 510,
            'fasilitas' => ['wifi', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos yang baru direnovasi dengan baik.',
            'pemilik' => 'Rudi Cahyono',
            'telepon' => '081234567816'
        ],
        28 => [
            'id' => 28,
            'nama' => 'Dragon Naga',
            'alamat' => 'Jl. Purwokerto Timur No. 66',
            'harga' => 8900000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 390,
            'fasilitas' => ['wifi', 'ac', 'km_dalam'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos dengan desain unik bergaya oriental.',
            'pemilik' => 'Lily Chen',
            'telepon' => '081234567817'
        ],
        29 => [
            'id' => 29,
            'nama' => 'Unicorn Ajaib',
            'alamat' => 'Jl. Salaman No. 29',
            'harga' => 7600000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 590,
            'fasilitas' => ['wifi', 'dapur', 'parkir_luas'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan dekorasi fantasi yang menarik.',
            'pemilik' => 'Doni Setiawan',
            'telepon' => '081234567818'
        ],
        30 => [
            'id' => 30,
            'nama' => 'Pegasus Terbang',
            'alamat' => 'Jl. Kaliwungu No. 44',
            'harga' => 9300000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 310,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos dengan konsep mitologi Yunani yang unik.',
            'pemilik' => 'Eva Maria',
            'telepon' => '081234567819'
        ],
        
        // ID 31-40
        31 => [
            'id' => 31,
            'nama' => 'Griffin Mitos',
            'alamat' => 'Jl. Karangwangkal No. 18',
            'harga' => 7800000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 540,
            'fasilitas' => ['wifi', 'km_dalam', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos dengan desain bergaya kuno yang megah.',
            'pemilik' => 'Randy Orton',
            'telepon' => '081234567820'
        ],
        32 => [
            'id' => 32,
            'nama' => 'Centaur Half',
            'alamat' => 'Jl. Grendeng Raya No. 77',
            'harga' => 8500000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 440,
            'fasilitas' => ['wifi', 'ac', 'km_dalam'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan konsep unik setengah manusia setengah kuda.',
            'pemilik' => 'Chiron Aster',
            'telepon' => '081234567821'
        ],
        33 => [
            'id' => 33,
            'nama' => 'Saturn Cincin',
            'alamat' => 'Jl. Purwokerto Timur No. 52',
            'harga' => 7100000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 650,
            'fasilitas' => ['wifi', 'dapur', 'parkir_luas'],
            'sisa_kamar' => 5,
            'deskripsi' => 'Kos dengan desain luar angkasa yang futuristik.',
            'pemilik' => 'Galileo Galilei',
            'telepon' => '081234567822'
        ],
        34 => [
            'id' => 34,
            'nama' => 'Jupiter Raksasa',
            'alamat' => 'Jl. Kecamatan Salaman No. 63',
            'harga' => 9700000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 260,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry', 'keamanan_24_jam'],
            'sisa_kamar' => 1,
            'deskripsi' => 'Kos terbesar dengan fasilitas paling lengkap.',
            'pemilik' => 'Cassini Probe',
            'telepon' => '081234567823'
        ],
        35 => [
            'id' => 35,
            'nama' => 'Mars Merah',
            'alamat' => 'Jl. Kaliwungu No. 31',
            'harga' => 8300000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 470,
            'fasilitas' => ['wifi', 'km_dalam', 'parkir_luas'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan warna dominan merah yang energik.',
            'pemilik' => 'Elon Musk',
            'telepon' => '081234567824'
        ],
        36 => [
            'id' => 36,
            'nama' => 'Venus Cantik',
            'alamat' => 'Jl. Karangwangkal No. 84',
            'harga' => 9100000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 340,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'dapur'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos dengan dekorasi elegan dan feminin.',
            'pemilik' => 'Aphrodite Love',
            'telepon' => '081234567825'
        ],
        37 => [
            'id' => 37,
            'nama' => 'Mercury Cepat',
            'alamat' => 'Jl. Grendeng No. 27',
            'harga' => 6900000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 710,
            'fasilitas' => ['wifi', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos dengan akses super cepat ke berbagai tempat.',
            'pemilik' => 'Hermes Speed',
            'telepon' => '081234567826'
        ],
        38 => [
            'id' => 38,
            'nama' => 'Neptune Laut',
            'alamat' => 'Jl. Purwokerto Timur No. 59',
            'harga' => 8800000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 400,
            'fasilitas' => ['wifi', 'ac', 'km_dalam'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan tema laut dan warna biru yang menenangkan.',
            'pemilik' => 'Poseidon Sea',
            'telepon' => '081234567827'
        ],
        39 => [
            'id' => 39,
            'nama' => 'Uranus Unik',
            'alamat' => 'Jl. Salaman No. 36',
            'harga' => 7500000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 570,
            'fasilitas' => ['wifi', 'dapur', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos dengan desain yang unik dan berbeda dari yang lain.',
            'pemilik' => 'William Herschel',
            'telepon' => '081234567828'
        ],
        40 => [
            'id' => 40,
            'nama' => 'Pluto Kecil',
            'alamat' => 'Jl. Kaliwungu No. 68',
            'harga' => 6400000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 800,
            'fasilitas' => ['wifi', 'km_dalam'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos kecil yang nyaman dengan harga ekonomis.',
            'pemilik' => 'Clyde Tombaugh',
            'telepon' => '081234567829'
        ],
        
        // ID 41-45
        41 => [
            'id' => 41,
            'nama' => 'Earth Bumi',
            'alamat' => 'Jl. Karangwangkal No. 93',
            'harga' => 8700000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 460,
            'fasilitas' => ['wifi', 'parkir_luas'],
            'sisa_kamar' => 3,
            'deskripsi' => 'Kos dengan konsep ramah lingkungan dan eco-friendly.',
            'pemilik' => 'Gaia Mother',
            'telepon' => '081234567830'
        ],
        42 => [
            'id' => 42,
            'nama' => 'Moon Bulan',
            'alamat' => 'Jl. Grendeng Raya No. 38',
            'harga' => 9200000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 320,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos dengan suasana tenang seperti malam bulan purnama.',
            'pemilik' => 'Selene Night',
            'telepon' => '081234567831'
        ],
        43 => [
            'id' => 43,
            'nama' => 'Sun Matahari',
            'alamat' => 'Jl. Purwokerto Timur No. 74',
            'harga' => 8100000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 490,
            'fasilitas' => ['wifi', 'dapur', 'parkir_luas'],
            'sisa_kamar' => 4,
            'deskripsi' => 'Kos dengan pencahayaan alami yang maksimal.',
            'pemilik' => 'Helios Sun',
            'telepon' => '081234567832'
        ],
        44 => [
            'id' => 44,
            'nama' => 'Star Bintang',
            'alamat' => 'Jl. Kecamatan Salaman No. 51',
            'harga' => 9900000,
            'jenis_kelamin' => 'putri',
            'jarak_ke_kampus' => 230,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry', 'dapur', 'keamanan_24_jam', 'kulkas'],
            'sisa_kamar' => 1,
            'deskripsi' => 'Kos bintang 5 dengan rating tertinggi.',
            'pemilik' => 'Twinkle Little',
            'telepon' => '081234567833'
        ],
        45 => [
            'id' => 45,
            'nama' => 'Galaxy Bimasakti',
            'alamat' => 'Jl. Kaliwungu No. 16',
            'harga' => 10200000,
            'jenis_kelamin' => 'putra',
            'jarak_ke_kampus' => 210,
            'fasilitas' => ['wifi', 'ac', 'km_dalam', 'laundry', 'dapur', 'keamanan_24_jam', 'kulkas', 'air_panas'],
            'sisa_kamar' => 2,
            'deskripsi' => 'Kos terbaik dengan fasilitas paling lengkap dan modern.',
            'pemilik' => 'Andromeda Space',
            'telepon' => '081234567834'
        ]
    ];
    
    return $kos_data;
}

/**
 * Mendapatkan data kos dengan filter
 * @param array $filters Filter yang akan diterapkan
 * @return array Data kos yang sudah difilter
 */
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
            if (!in_array($filters['fasilitas'], $kos['fasilitas'])) {
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
            $jarak = $kos['jarak_ke_kampus'];
            
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
?>