<?php
require_once('config/db.php');

header('Content-Type: application/xml; charset=UTF-8');

$base = 'https://www.invez.biz';

$static_pages = [
    ['path' => '/',           'priority' => '1.0', 'changefreq' => 'weekly'],
    ['path' => '/properties', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['path' => '/portfolio',  'priority' => '0.7', 'changefreq' => 'weekly'],
    ['path' => '/content',    'priority' => '0.7', 'changefreq' => 'weekly'],
    ['path' => '/about',      'priority' => '0.5', 'changefreq' => 'monthly'],
    ['path' => '/contact',    'priority' => '0.5', 'changefreq' => 'monthly'],
];

$properties = db()->query(
    "SELECT id, updated_at FROM properties WHERE is_active = 1 ORDER BY id DESC"
)->fetchAll();

$articles = db()->query(
    "SELECT id, updated_at FROM articles WHERE is_active = 1 ORDER BY id DESC"
)->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($static_pages as $page): ?>
    <url>
        <loc><?= htmlspecialchars($base . $page['path']) ?></loc>
        <changefreq><?= $page['changefreq'] ?></changefreq>
        <priority><?= $page['priority'] ?></priority>
    </url>
<?php endforeach; ?>
<?php foreach ($properties as $p): ?>
    <url>
        <loc><?= htmlspecialchars($base . '/property/' . $p['id']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>
<?php foreach ($articles as $a): ?>
    <url>
        <loc><?= htmlspecialchars($base . '/article/' . $a['id']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($a['updated_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
<?php endforeach; ?>
</urlset>
