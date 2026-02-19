<?php
set_time_limit(0); 
ob_implicit_flush(true);
ob_end_flush();

// --- 1. KONFIGURASI UTAMA ---
$groq_api_key = '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'; 
$folder_name  = "barthes";

if (!is_dir($folder_name)) { 
    mkdir($folder_name, 0755, true); 
}

function create_slug($string) {
    return trim(strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)), '-');
}

function update_backlink_list($folder, $url, $title) {
    $file_path = $folder . DIRECTORY_SEPARATOR . 'listbacklink.txt';
    $line = '<a href="' . $url . '">' . $title . '</a>' . PHP_EOL;
    return file_put_contents($file_path, $line, FILE_APPEND | LOCK_EX);
}

function update_sitemap($domain, $folder) {
    $domain = rtrim($domain, '/');
    $files = glob($folder . "/*.html");
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($files as $file) {
        if(basename($file) == "sitemap.xml") continue;
        $xml .= '<url><loc>https://' . $domain . '/' . $folder . '/' . basename($file) . '</loc><lastmod>' . date('Y-m-d') . '</lastmod><priority>0.80</priority></url>';
    }
    $xml .= '</urlset>';
    file_put_contents($folder . "/sitemap.xml", $xml);
}

function generate_ai_content($keyword, $api_key, $idx) {
    $url = "https://api.groq.com/openai/v1/chat/completions";
    
    // Sudut pandang trending yang memicu Google Discover
    $angles = [
        "Analisis Ritme Permainan Terkini dan Algoritma $keyword",
        "Eksperimen Statistik dan Probabilitas Kemenangan $keyword",
        "Rahasia Pola Volatilitas Tinggi yang Jarang Diketahui pada $keyword",
        "Studi Mendalam Strategi Psikologi dan Manajemen Risiko $keyword",
        "Review Fitur Tersembunyi dan Optimasi Jam Bermain $keyword"
    ];
    $selected_angle = $angles[$idx % count($angles)];

    $model = "llama-3.1-8b-instant"; 

    $prompt = "Tulis artikel analisis profesional (MINIMAL 1000 KATA) tentang '$keyword'. 
               Fokus pembahasan: $selected_angle.
               
               STRUKTUR ARTIKEL (WAJIB):
               1. Judul (H1): Harus deskriptif, menarik, dan memicu CTR tinggi untuk Google Discover.
               2. Deskripsi: Ringkasan konten yang relevan untuk pembaca.
               3. Paragraf Pembuka: Harus menarik, baku, dan langsung ke inti topik.
               4. Isi: Minimal 1000 kata. Berikan data, logika, dan analisis mendalam. Bukan spam.
               5. Gunakan minimal 7 subjudul (H2/H3).

               KETENTUAN TEKNIS:
               - JANGAN gunakan simbol markdown (** atau __). Gunakan tag HTML <strong>.
               - JANGAN gunakan simbol bintang (*) untuk list. Gunakan <ul> dan <li>.
               - JANGAN gunakan kata 'Kesimpulannya' atau 'Di era digital'. Gunakan bahasa manusia yang mengalir.
               - Konten harus unik, informatif, dan tidak plagiat.

               FORMAT OUTPUT:
               [DESC] tulis meta deskripsi singkat [/DESC]
               <h1>Judul Artikel</h1>
               (Konten artikel dalam format HTML murni)";

    $data = [
        "model" => $model,
        "messages" => [
            ["role" => "system", "content" => "Anda adalah Editor Senior yang ahli menulis artikel 1000 kata lebih yang lolos deteksi AI. Anda hanya mengirim HTML murni tanpa markdown."],
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.8,
        "max_tokens" => 4000
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $api_key]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);

    return $result['choices'][0]['message']['content'] ?? false;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Google Discover Engine v3 - 1000 Words</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f3f5; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 40px; }
        h2 { color: #2c3e50; text-align: center; }
        input, button { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; }
        button { background: #2980b9; color: white; border: none; font-weight: bold; cursor: pointer; font-size: 16px; }
        .log-box { background: #1c2833; color: #2ecc71; padding: 15px; border-radius: 8px; height: 350px; overflow-y: auto; font-family: monospace; font-size: 13px; margin-top: 20px; border: 1px solid #000; }
        .success { color: #fff; background: #27ae60; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>

<div class="container">
    <h2>🤖 Discover Pro Engine (1000+ Words)</h2>
    <form method="POST">
        <input type="text" name="keyword" placeholder="Contoh: Analisis Ritme Lucky PGSoft" required>
        <input type="text" name="domain" placeholder="namadomain.com" required>
        <input type="text" name="reg_link" placeholder="https://link-pendaftaran.com" required>
        <input type="number" name="jumlah" value="3">
        <button type="submit" name="start">MULAI GENERATE ARTIKEL</button>
    </form>

    <?php if (isset($_POST['start'])): ?>
    <div class="log-box" id="log">
        <?php
        $kw = $_POST['keyword']; 
        $domain = $_POST['domain']; 
        $reg = $_POST['reg_link']; 
        $jml = (int)$_POST['jumlah'];

        // Cek template.html
        $html_layout = file_exists('template.html') ? file_get_contents('template.html') : "{{CONTENT}}";

        for ($i = 1; $i <= $jml; $i++) {
            echo "> Memproses artikel ke-$i (Target 1000+ kata)...<br>";
            flush();

            $ai_text = generate_ai_content($kw, $groq_api_key, $i);

            if ($ai_text) {
                // Bersihkan semua simbol markdown yang mungkin muncul
                $ai_text = str_replace(['**', '```html', '```', '###'], '', $ai_text);

                // Ekstrak Meta Deskripsi
                preg_match('/\[DESC\](.*?)\[\/DESC\]/s', $ai_text, $m_desc);
                $meta_desc = !empty($m_desc[1]) ? trim(strip_tags($m_desc[1])) : $kw;
                $ai_text = preg_replace('/\[DESC\].*?\[\/DESC\]/s', '', $ai_text);

                // Ekstrak Judul
                preg_match('/<h1>(.*?)<\/h1>/i', $ai_text, $m_title);
                $judul = !empty($m_title[1]) ? strip_tags($m_title[1]) : $kw . " " . rand(100,999);
                $slug = create_slug($judul);

                // Buat CTA Button
                $cta = '<p style="text-align:center;"><a href="' . $reg . '" target="_blank" rel="nofollow noopener" style="background:#e67e22; color:#fff; padding:15px 30px; text-decoration:none; font-weight:bold; border-radius:50px; display:inline-block; margin:20px 0;">KLIK UNTUK INFORMASI PENDAFTARAN</a></p>';
                
                $final_content = preg_replace('/<\/h1>/i', "</h1>" . $cta, $ai_text, 1);
                $final_content .= $cta;

                // Masukkan ke Template
                $placeholders = ['{{TITLE}}', '{{DESCRIPTION}}', '{{CANONICAL}}', '{{CONTENT}}', '{{DOMAIN}}', '{{REG_LINK}}'];
                $replacements = [
                    $judul, 
                    $meta_desc, 
                    "https://$domain/$folder_name/$slug.html", 
                    $final_content, 
                    $domain, 
                    $reg
                ];

                $output_html = str_replace($placeholders, $replacements, $html_layout);

                // Simpan File
                $file_path = "$folder_name/$slug.html";
                file_put_contents($file_path, $output_html);

                // Update List Backlink
                $full_url = "https://$domain/$folder_name/$slug.html";
                update_backlink_list($folder_name, $full_url, $judul);

                echo "<span class='success'>✅ BERHASIL:</span> $file_path (Google Discover Optimized)<br>";
            } else {
                echo "<span style='color:#e74c3c;'>❌ Gagal generate pada antrian ke-$i</span><br>";
            }
            
            echo "<script>var obj = document.getElementById('log'); obj.scrollTop = obj.scrollHeight;</script>";
            sleep(3); 
            flush();
        }
        update_sitemap($domain, $folder_name);
        echo "<br>🚀 PROSES SELESAI!";
        ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
