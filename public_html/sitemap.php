<?php
// sitemap.php — генератор мультиязычных sitemap c явными ?lang=sk|ru|ua и учетом корневой страницы "/"
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php'; // ожидается Database::getConnection() -> PDO

// Языки и hreflang
$langs = ['sk','ru','ua'];
$hreflangMap = ['sk' => 'sk', 'ru' => 'ru', 'ua' => 'uk']; // ua -> uk
$baseUrl = rtrim(defined('SITE_URL') ? SITE_URL : '', '/'); // без завершающего слеша
$outputDir = __DIR__ . '/sitemaps';
@mkdir($outputDir, 0755, true);

// Статические страницы: корень "/" + явные скрипты (без index.php, чтобы не дублировать "/")
$staticScripts = [
    '/',               // корневая
    'services.php',
    'pricing.php',
    'gallery.php',
    'blog.php',
    'contacts.php',
    'about.php',
];

// Утилиты
function iso_date($ts) {
    if (!$ts) return '';
    if (is_numeric($ts)) return date('Y-m-d', (int)$ts);
    $t = strtotime($ts);
    return $t ? date('Y-m-d', $t) : '';
}

function url_with_lang($baseUrl, $script, $lang) {
    // для корня '/' — всегда /?lang=xx
    if ($script === '/') {
        return $baseUrl . '/?lang=' . $lang;
    }
    // для прочих — /script.php?lang=xx
    $sep = (strpos($script, '?') === false) ? '?' : '&';
    return $baseUrl . '/' . ltrim($script, '/') . $sep . 'lang=' . $lang;
}

function write_urlset($filepath, $entries, $hreflangMap) {
    $fh = fopen($filepath, 'w');
    if (!$fh) return false;
    fwrite($fh, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
    fwrite($fh, "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n");
    foreach ($entries as $e) {
        fwrite($fh, "  <url>\n");
        fwrite($fh, "    <loc>" . htmlspecialchars($e['loc'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</loc>\n");
        if (!empty($e['lastmod'])) {
            fwrite($fh, "    <lastmod>" . htmlspecialchars($e['lastmod'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</lastmod>\n");
        }
        if (!empty($e['alternates']) && is_array($e['alternates'])) {
            foreach ($e['alternates'] as $lng => $href) {
                $hl = $hreflangMap[$lng] ?? $lng;
                fwrite($fh, "    <xhtml:link rel=\"alternate\" hreflang=\"" . htmlspecialchars($hl, ENT_QUOTES, 'UTF-8') . "\" href=\"" . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . "\" />\n");
            }
            // x-default → на SK (если есть), иначе на первый элемент
            $xDefault = $e['alternates']['sk'] ?? reset($e['alternates']);
            if ($xDefault) {
                fwrite($fh, "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"" . htmlspecialchars($xDefault, ENT_QUOTES, 'UTF-8') . "\" />\n");
            }
        }
        fwrite($fh, "  </url>\n");
    }
    fwrite($fh, "</urlset>\n");
    fclose($fh);
    return true;
}

// 1) Статические страницы
$pagesByLang = ['sk'=>[], 'ru'=>[], 'ua'=>[]];

foreach ($staticScripts as $script) {
    // lastmod: для "/" берём время изменения index.php, для остальных — соответствующий скрипт
    $filePath = ($script === '/') ? (__DIR__ . '/index.php') : (__DIR__ . '/' . ltrim($script, '/'));
    $lastmod = file_exists($filePath) ? iso_date(filemtime($filePath)) : '';

    // альтернативы для всех языков
    $alts = [];
    foreach ($langs as $lng) {
        $alts[$lng] = url_with_lang($baseUrl, $script, $lng);
    }

    // Для каждой локали добавляем свою запись
    foreach ($langs as $lng) {
        $pagesByLang[$lng][] = [
            'loc' => $alts[$lng],
            'lastmod' => $lastmod,
            'alternates' => $alts,
        ];
    }
}

// 2) Блог из БД
try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Проверим наличие created_at/updated_at
    $descCols = $pdo->query("DESCRIBE blog_posts")->fetchAll(PDO::FETCH_COLUMN);
    $hasCreated = $descCols ? in_array('created_at', $descCols, true) : false;
    $hasUpdated = $descCols ? in_array('updated_at', $descCols, true) : false;

    $select = "id, slug";
    if ($hasCreated) $select .= ", created_at";
    if ($hasUpdated) $select .= ", updated_at";

    $stmt = $pdo->query("SELECT $select FROM blog_posts");
    $posts = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Exception $e) {
    $posts = [];
}

$blogByLang = ['sk'=>[], 'ru'=>[], 'ua'=>[]];

foreach ($posts as $row) {
    $slug = trim($row['slug'] ?? '');
    if ($slug === '') continue;

    // URL постов: blog-post.php?slug=...&lang=xx
    $alts = [];
    foreach ($langs as $lng) {
        $alts[$lng] = $baseUrl . '/blog-post.php?slug=' . rawurlencode($slug) . '&lang=' . $lng;
    }

    // lastmod приоритет: updated_at > created_at > пусто
    $lastmod = '';
    if (!empty($row['updated_at'])) $lastmod = iso_date($row['updated_at']);
    elseif (!empty($row['created_at'])) $lastmod = iso_date($row['created_at']);

    foreach ($langs as $lng) {
        $blogByLang[$lng][] = [
            'loc' => $alts[$lng],
            'lastmod' => $lastmod,
            'alternates' => $alts,
        ];
    }
}

// 3) Генерация файлов sitemap
$indexEntries = [];

// pages
foreach ($langs as $lng) {
    if (!empty($pagesByLang[$lng])) {
        $file = $outputDir . "/sitemap-pages-{$lng}.xml";
        write_urlset($file, $pagesByLang[$lng], $hreflangMap);
        $indexEntries[] = [
            'loc' => $baseUrl . '/sitemaps/' . basename($file),
            'lastmod' => date('c'),
        ];
    }
}

// blog
foreach ($langs as $lng) {
    if (!empty($blogByLang[$lng])) {
        $file = $outputDir . "/sitemap-blog-{$lng}.xml";
        write_urlset($file, $blogByLang[$lng], $hreflangMap);
        $indexEntries[] = [
            'loc' => $baseUrl . '/sitemaps/' . basename($file),
            'lastmod' => date('c'),
        ];
    }
}

// 4) sitemap-index.xml
$indexFile = $outputDir . '/sitemap-index.xml';
$fh = fopen($indexFile, 'w');
fwrite($fh, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
fwrite($fh, "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");
foreach ($indexEntries as $s) {
    fwrite($fh, "  <sitemap>\n");
    fwrite($fh, "    <loc>" . htmlspecialchars($s['loc'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</loc>\n");
    fwrite($fh, "    <lastmod>" . htmlspecialchars($s['lastmod'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</lastmod>\n");
    fwrite($fh, "  </sitemap>\n");
}
fwrite($fh, "</sitemapindex>\n");
fclose($fh);

// Вывод
header('Content-Type: text/plain; charset=utf-8');
echo "Sitemaps generated in: {$outputDir}\n";
foreach (glob($outputDir . '/*.xml') as $f) {
    echo basename($f) . "\n";
}
echo "\nSubmit in GSC: {$baseUrl}/sitemaps/sitemap-index.xml\n";