<?php
// Mencegah error CORS dan mengatur tipe data balasan ke JSON
header('Content-Type: application/json');
include 'koneksi.php';

// Siapkan wadah kosong (array) untuk menampung seluruh soal yang akan dikirim
$data_final = array();

// 1. Ambil semua Kategori dari database
$query_kat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori ASC");

while($row_kat = mysqli_fetch_assoc($query_kat)) {
    $id_kat = $row_kat['id_kategori'];
    
    // Siapkan wadah untuk kategori ini
    $kategori_data = array(
        'id' => 'kat_' . $id_kat,
        'nama' => $row_kat['nama_kategori'],
        'soal' => array()
    );

    // 2. Ambil tepat 1 soal ACAK untuk masing-masing level poin (100, 200, 300, 400, 500) di kategori ini
    // Memastikan poin urut dari 100 ke 500
    $poin_list = array(100, 200, 300, 400, 500);
    
    foreach ($poin_list as $poin_target) {
        // Query inti untuk PENGACAKAN BANK SOAL
        // ORDER BY RAND() LIMIT 1 berarti: "Kocok semua soal poin 100 di kategori ini, ambil 1 pemenangnya"
        $q_soal = "SELECT * FROM soal WHERE id_kategori='$id_kat' AND poin='$poin_target' ORDER BY RAND() LIMIT 1";
        $res_soal = mysqli_query($conn, $q_soal);
        
        if (mysqli_num_rows($res_soal) > 0) {
            $row_soal = mysqli_fetch_assoc($res_soal);
            
            // Masukkan data soal yang terpilih ke dalam wadah kategori
            $kategori_data['soal'][] = array(
                'points' => (int)$row_soal['poin'],
                'q' => $row_soal['pertanyaan'],
                'a' => $row_soal['jawaban'],
                'img' => $row_soal['gambar'] ? $row_soal['gambar'] : '',
                'audio' => $row_soal['audio'] ? $row_soal['audio'] : '',
                'time' => (int)$row_soal['waktu']
            );
        } else {
            // JIKA KOSONG (Admin belum buat soal untuk poin tersebut), buat soal "Dummy" agar tata letak card tidak bolong
            $kategori_data['soal'][] = array(
                'points' => $poin_target,
                'q' => "TIDAK ADA SOAL! (Admin belum membuat soal bernilai $poin_target untuk kategori ini).",
                'a' => "KOSONG",
                'img' => '',
                'audio' => '',
                'time' => 15
            );
        }
    }
    
    // Masukkan kategori yang sudah terisi soal ke wadah utama
    $data_final[] = $kategori_data;
}

// 3. Konversi wadah utama (array PHP) menjadi format JSON, lalu "muntahkan" ke peramban
echo json_encode($data_final);
exit();
?>