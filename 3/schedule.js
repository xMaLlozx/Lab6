// schedule.js

// получаем номер календарной недели
function getWeekNumber(d) {
    d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

// верхняя/нижняя неделя
function getWeekType(weekNum) {
    return (weekNum % 2 === 0) ? 'верхняя' : 'нижняя';
}

// загрузка списка занятий
function loadLessons() {
    $.post('AJAX.php', {
        type: 'list',
        day: $('#day').val(),
        course: $('#course').val(),
        group: $('#group').val(),
        week: $('#week-type').data('week'),
        q: $('#subject-search').val()
    }, function (data) {
        const list = $('#lessons-list');
        list.empty();

        if (!data.success) {
            list.text('Ошибка загрузки расписания');
            return;
        }

        if (!data.items.length) {
            list.text('Нет пар по выбранным условиям');
            return;
        }

        data.items.forEach(function (item) {
            const row = $('<div class="lesson-row">').data('item', item);
            $('<div class="lesson-col num">')
                .text(item.lesson + ' пара (' + item.time + ')')
                .appendTo(row);
            $('<div class="lesson-col subj">')
                .text(item.subject + ' — ' + item.teacher)
                .appendTo(row);
            $('<div class="lesson-col room">')
                .text(item.room)
                .appendTo(row);
            list.append(row);
        });
    }, 'json');
}

$(function () {
    const today = new Date();
    const weekNum = getWeekNumber(today);
    const weekType = getWeekType(weekNum); // верхняя/нижняя

    // текущая дата и неделя
    $('#current-date')
        .text(today.toLocaleDateString('ru-RU'))
        .attr('title', 'Неделя № ' + weekNum);

    $('#week-type')
        .text(weekType)
        .data('week', weekType === 'верхняя' ? 'upper' : 'lower')
        .attr('title', 'Неделя № ' + weekNum);

    // по умолчанию выбираем текущий день
    const dayIndex = today.getDay(); // 0-вс, 1-пн ...
    const map = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
    const currentDay = map[dayIndex];
    $('#day').val(currentDay);

    // первая загрузка
    loadLessons();

    // фильтры и поиск
    $('#course, #group, #day').on('change', function () {
        loadLessons();
    });

    $('#btn-search').on('click', function () {
        loadLessons();
    });

    $('#subject-search').on('input', function () {
        loadLessons();
    });

    // аккордеон с полной информацией
    $('#lessons-list').on('click', '.lesson-row', function () {
        const item = $(this).data('item');

        // убрать прошлый аккордеон
        $('.accordion').remove();

        const acc = $('<div class="accordion">')
            .html(
                '<div>Предмет: ' + item.subject + '</div>' +
                '<div>Преподаватель: ' + item.teacher + '</div>' +
                '<div>Аудитория: ' + item.room + '</div>' +
                '<div>Неделя: ' + item.week + '</div>' +
                '<div>Описание: ' + item.desc + '</div>'
            );

        $(this).after(acc);
    });
});
