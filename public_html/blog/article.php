<?php
// /blog/article.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

session_start();

$slug = $_GET['slug'] ?? '';
$slug = trim($slug);

if ($slug === '') {
    http_response_code(404);
    echo "Статья не найдена";
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'ru');
$lang = in_array($lang, ['sk', 'ru', 'ua']) ? $lang : 'ru';

$titleField = "title_{$lang}";
$excerptField = "excerpt_{$lang}";
$contentField = "content_{$lang}";

try {
    $stmt = $conn->prepare("
        SELECT id, $titleField AS title, $excerptField AS excerpt, $contentField AS content,
               featured_image, published_at, is_published
        FROM blog_posts
        WHERE slug = :slug AND is_published = 1
        LIMIT 1
    ");
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Ошибка базы данных: " . htmlspecialchars($e->getMessage());
    exit;
}

if (!$post) {
    http_response_code(404);
    echo "Статья не найдена";
    exit;
}

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$publishedDate = $post['published_at'] ? date('d.m.Y', strtotime($post['published_at'])) : '';

include __DIR__ . '/../includes/header.php';
?>

<main class="max-w-4xl mx-auto p-4">
    <article class="prose max-w-none">
        <h1><?= esc($post['title']) ?></h1>
        <?php if ($publishedDate): ?>
            <p class="text-sm text-gray-500 mb-4">Опубликовано: <?= esc($publishedDate) ?></p>
        <?php endif; ?>
        <?php if (!empty($post['featured_image'])): ?>
            <img src="<?= esc($post['featured_image']) ?>" alt="<?= esc($post['title']) ?>" style="max-width:100%; height:auto; margin-bottom:1rem;" />
        <?php endif; ?>
        <div>
            <?= $post['content'] /* предполагается, что контент уже безопасен и содержит HTML */ ?>
        </div>
    </article>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>