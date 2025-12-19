// search.js
let allData = [];      // все загруженные элементы с сервера
let filtered = [];     // отфильтрованные по поиску
let shownCount = 0;    // сколько уже показано
const PAGE_SIZE = 10;  // по заданию вывод по 10

// текущий тип данных (nickname или news)
let currentType = 'nickname';
// отрисовать ещё элементы из filtered
function renderMore() {
    const list = $('#results-list'); 
    const slice = filtered.slice(shownCount, shownCount + PAGE_SIZE);

    slice.forEach(item => {
        const text = item.nickname
            ? item.nickname + ' — ' + item.about
            : item.title;
        $('<li>').text(text).appendTo(list);
    });

    shownCount += slice.length;
    if (shownCount >= filtered.length) {
        $('#load-more').hide();
    } else {
        $('#load-more').show();
    }
}

// применить фильтр по строке поиска (по уже загруженным данным)
function applyFilter() {
    const q = $('#search-input').val().toLowerCase();
    filtered = allData.filter(it => {
        const text = JSON.stringify(it).toLowerCase();
        return text.includes(q);
    });
    $('#results-list').empty();
    shownCount = 0;
    renderMore();
}

// запрос к ajax.php
function loadData(typeValue) {
    currentType = typeValue;

    $.post('ajax.php', {
        type: typeValue,
        offset: 0,
        limit: 1000
    }, function (data) {
        if (!data.success) {
            alert(data.error || 'Ошибка загрузки');
            return;
        }
        allData = data.results;
        filtered = allData.slice();
        $('#results-list').empty();
        shownCount = 0;
        renderMore();
    }, 'json');
}

$(function () {
    loadData('nickname'); // при желании можешь сделать переключатель типа
    // поиск по клику на кнопку
    $('#search-btn').on('click', function () {
        applyFilter();
    });
    // поиск «на лету» при вводе
    $('#search-input').on('input', function () {
        applyFilter();
    });
    // кнопка «Загрузить ещё»
    $('#load-more').on('click', function () {
        renderMore();
    });
});
