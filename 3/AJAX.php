<?php
// ajax_schedule.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin', '*');

/**
 * Тестовое расписание на неделю
 */
function getLessons() {
    return [
        [
            'day'     => 'monday',
            'course'  => 1,
            'group'   => 'ИВТ-3',
            'lesson'  => 1,
            'time'    => '08:00-09:30',
            'subject' => 'Мат. анализ',
            'teacher' => 'проф. Баранов М.А.',
            'room'    => '108',
            'week'    => 'lower',
            'desc'    => 'Лекция по пределам и производным.'
        ],
        [
            'day'     => 'monday',
            'course'  => 1,
            'group'   => 'ИВТ-3',
            'lesson'  => 2,
            'time'    => '09:50-11:20',
            'subject' => 'Мат. анализ',
            'teacher' => 'проф. Баранов М.А.',
            'room'    => '108',
            'week'    => 'lower',
            'desc'    => 'Продолжение темы интегралов.'
        ],
        [
            'day'     => 'tuesday',
            'course'  => 1,
            'group'   => 'ИВТ-3',
            'lesson'  => 1,
            'time'    => '08:00-09:30',
            'subject' => 'Информатика',
            'teacher' => 'доц. Иванова И.И.',
            'room'    => '210',
            'week'    => 'upper',
            'desc'    => 'Практика по программированию.'
        ],
        [
            'day'     => 'wednesday',
            'course'  => 1,
            'group'   => 'ИВТ-3',
            'lesson'  => 3,
            'time'    => '11:30-13:00',
            'subject' => 'Физика',
            'teacher' => 'доц. Петров П.П.',
            'room'    => '305',
            'week'    => 'upper',
            'desc'    => 'Лекция по механике.'
        ]
    ];
}

/**
 * Расписание звонков
 */
function getBells() {
    return [
        ['lesson' => 1, 'time' => '08:00-09:30'],
        ['lesson' => 2, 'time' => '09:50-11:20'],
        ['lesson' => 3, 'time' => '11:30-13:00'],
        ['lesson' => 4, 'time' => '13:10-14:40']
    ];
}

ob_clean();

try {
    $type = isset($_POST['type']) ? $_POST['type'] : 'list';

    if ($type === 'bells') {
        echo json_encode([
            'success' => true,
            'bells'   => getBells()
        ], JSON_UNESCAPED_UNICODE);
        ob_end_flush();
        exit;
    }

    // фильтрация списка занятий
    $day    = isset($_POST['day']) ? $_POST['day'] : '';
    $course = isset($_POST['course']) ? intval($_POST['course']) : 0;
    $group  = isset($_POST['group']) ? $_POST['group'] : '';
    $week   = isset($_POST['week']) ? $_POST['week'] : ''; // upper/lower
    $q      = isset($_POST['q']) ? trim($_POST['q']) : ''; // поиск по названию

    $lessons = getLessons();
    $result = [];

    foreach ($lessons as $l) {
        if ($day && $l['day'] !== $day) continue;
        if ($course && $l['course'] != $course) continue;
        if ($group && $l['group'] !== $group) continue;
        if ($week && $l['week'] !== $week) continue;
        if ($q !== '' && mb_stripos($l['subject'], $q) === false) continue;
        $result[] = $l;
    }

    usort($result, function ($a, $b) {
        return $a['lesson'] <=> $b['lesson'];
    });

    echo json_encode([
        'success' => true,
        'items'   => $result
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Ошибка сервера',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

ob_end_flush();
