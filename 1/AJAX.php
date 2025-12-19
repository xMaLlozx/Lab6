<?php
// ajax.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// генерируем тестовые данные под два типа: nickname и news
function generateData($type, $count) {
    $data = [];

    for ($i = 1; $i <= $count; $i++) {
        if ($type === 'nickname') {
            $data[] = [
                'id'       => $i,
                'nickname' => 'user_' . $i,
                'about'    => 'Описание пользователя №' . $i
            ];
        } elseif ($type === 'news') {
            $text = 'Это текст новости №' . $i .
                    '. Используется для теста AJAX‑поиска и ленивой загрузки.';
            $data[] = [
                'id'    => $i,
                'title' => 'Новость №' . $i,
                'text'  => $text
            ];
        }
    }
    return $data;
}

ob_clean();

try {
    // обязательный параметр type
    $type = isset($_POST['type']) ? $_POST['type'] : '';

    if ($type !== 'nickname' && $type !== 'news') {
        echo json_encode([
            'success' => false,
            'error'   => 'Неверный параметр type'
        ], JSON_UNESCAPED_UNICODE);
        ob_end_flush();
        exit;
    }

    // параметры для ленивой загрузки
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $limit  = isset($_POST['limit']) ? intval($_POST['limit']) : 10;

    // просто 35 тестовых элементов
    $allData = generateData($type, 35);

    // порция данных
    $results = array_slice($allData, $offset, $limit);
    $hasMore = ($offset + $limit) < count($allData);

    echo json_encode([
        'success' => true,
        'type'    => $type,
        'total'   => count($allData),
        'offset'  => $offset,
        'limit'   => $limit,
        'results' => $results,
        'hasMore' => $hasMore
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Ошибка сервера',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

ob_end_flush();
