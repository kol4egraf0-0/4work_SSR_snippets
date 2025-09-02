<?php 

//http(s)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

//домен
$host = $_SERVER['HTTP_HOST'];

//без GET, до ?
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

//убираем слеш в конце
$path = rtrim($path, '/');

// Если путь пустой (главная), делаем корневой слеш
if ($path === '') {
    $path = '/';
}

//чистый канонический URL
$canonical_url = $protocol . '://' . $host . $path;

//вывода тега
echo '<link rel="canonical" href="' . htmlspecialchars($canonical_url) . '" />';
?>