<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
    $target_dir = "uploads/";
    $originalFileName = $_FILES["fileToUpload"]["name"];
    $target_file = $target_dir . basename($originalFileName);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Periksa apakah file adalah file teks
    if ($fileType != "txt") {
        echo "Maaf, hanya file teks (.txt) yang diizinkan.";
        $uploadOk = 0;
    }

    // Periksa apakah file sudah ada
    if (file_exists($target_file)) {
        echo "Maaf, file sudah ada.";
        $uploadOk = 0;
    }

    // Periksa ukuran file
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        echo "Maaf, file terlalu besar.";
        $uploadOk = 0;
    }

    // Jika semua pengecekan berhasil, lakukan unggah file dan konversi
    if ($uploadOk) {
        move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);

        // Baca konten dari file teks
        $fileContent = file_get_contents($target_file);

        // Pisahkan konten menjadi baris
        $lines = explode("\n", $fileContent);

        // Inisialisasi array untuk menyimpan data
        $data = array();

        // Tentukan header untuk JSON
        $headers = explode("\t", trim($lines[0]));

        // Iterasi melalui setiap baris (lewatkan baris pertama, karena berisi header)
        for ($i = 1; $i < count($lines); $i++) {
            // Pisahkan baris menjadi nilai-nilai
            $values = explode("\t", trim($lines[$i]));

            // Gabungkan header dan nilai-nilai ke dalam array asosiatif
            $item = array();
            for ($j = 0; $j < count($headers); $j++) {
                // Pemeriksaan apakah indeks tersedia di dalam array $values
                $cleanedValue = isset($values[$j]) ? trim($values[$j]) : null;
                $item[$headers[$j]] = $cleanedValue;
            }

            // Tambahkan item ke dalam array data
            $data[] = $item;
        }

        // Konversi array data ke format JSON
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Buat nama file JSON berdasarkan nama file teks
        $jsonFileName = "output_" . pathinfo($originalFileName, PATHINFO_FILENAME) . ".json";

        // Set header untuk memastikan browser mengenali sebagai file JSON untuk diunduh
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $jsonFileName);

        // Hapus file teks yang diunggah setelah konversi
        unlink($target_file);

        // Keluarkan data JSON langsung ke output
        echo $jsonData;
    }
}
?>
