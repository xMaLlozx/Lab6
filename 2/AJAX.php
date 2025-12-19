<?php
// ajax.php для задания 2
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

/**
 * Генерация простых новостей
 */
function generateNews($count) {
    $news = [];
    for ($i = 1; $i <= $count; $i++) {
        $full = 'Это полный текст новости №' . $i .
            '. Здесь находится расширенная информация о событии, ' .
            'используемая для лабораторной работы по AJAX.';
        $news[] = [
            'id'        => $i,
            'title'     => 'Новость №' . $i,
            'shortText' => 'Краткий текст новости №' . $i,
            'fullText'  => $full
        ];
    }
    return $news;
}

ob_clean();

try {
    // если есть id — отдать одну новость для модального окна
    if (isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $allNews = generateNews(25);
        $found = null;

        foreach ($allNews as $item) {
            if ($item['id'] === $id) {
                $found = $item;
                break;
            }
        }

        if ($found) {
            echo json_encode([
                'success'  => true,
                'title'    => $found['title'],
                'fullText' => $found['fullText']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'error'   => 'Новость не найдена'
            ], JSON_UNESCAPED_UNICODE);
        }

        ob_end_flush();
        exit;
    }

    // список новостей (для страниц и поиска)
    $page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $limit  = isset($_POST['limit']) ? intval($_POST['limit']) : 5; // по заданию 5
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';

    $allNews = generateNews(25);

    // фильтр по тексту
    if ($search !== '') {
        $q = mb_strtolower($search);
        $filtered = [];
        foreach ($allNews as $n) {
            $text = mb_strtolower($n['title'] . ' ' . $n['shortText'] . ' ' . $n['fullText']);
            if (mb_strpos($text, $q) !== false) {
                $filtered[] = $n;
            }
        }
        $allNews = $filtered;
    }

    $totalNews  = count($allNews);
    $totalPages = $limit > 0 ? max(1, ceil($totalNews / $limit)) : 1;

    if ($page < 1) $page = 1;
    if ($page > $totalPages) $page = $totalPages;

    $offset      = ($page - 1) * $limit;
    $newsForPage = array_slice($allNews, $offset, $limit);

    echo json_encode([
        'success'    => true,
        'page'       => $page,
        'totalPages' => $totalPages,
        'totalNews'  => $totalNews,
        'news'       => $newsForPage
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Ошибка сервера',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

ob_end_flush();
