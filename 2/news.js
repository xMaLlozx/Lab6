let currentPage = 1;
const PER_PAGE = 5;
let currentSearch = '';

function loadNews(page) {
    currentPage = page;
    $.post('AJAX.php', {
        page:  page,
        limit: PER_PAGE,
        search: currentSearch
    }, function (data) {
        if (!data.success) {
            alert(data.error || 'Ошибка загрузки новостей');
            return;
        }
        renderNews(data.news);
        renderPagination(data.page, data.totalPages);
    }, 'json');
}

function renderNews(items) {
    const container = $('#news-container');
    container.empty();

    if (!items.length) {
        container.text('Новостей не найдено');
        return;
    }

    items.forEach(n => {
        const item = $('<div class="news-item">');
        $('<div class="news-title">').text(n.title).appendTo(item);
        $('<div class="news-short">').text(n.shortText).appendTo(item);
        $('<span class="news-more">')
            .text('More info')
            .data('id', n.id)
            .appendTo(item);
        container.append(item);
    });
}

function renderPagination(page, totalPages) {
    const pag = $('#pagination');
    pag.empty();

    const prev = $('<span>').text('Prev')
        .toggleClass('disabled', page === 1)
        .data('page', page - 1);
    pag.append(prev);

    for (let p = 1; p <= totalPages; p++) {
        $('<span>')
            .text(p)
            .toggleClass('active', p === page)
            .data('page', p)
            .appendTo(pag);
    }

    const next = $('<span>').text('Next')
        .toggleClass('disabled', page === totalPages)
        .data('page', page + 1);
    pag.append(next);
}

function openModal(id) {
    $.post('ajax.php', { id: id }, function (data) {
        if (!data.success) {
            alert(data.error || 'Ошибка загрузки новости');
            return;
        }
        $('#modal-title').text(data.title);
        $('#modal-text').text(data.fullText);
        $('#news-modal').show();
    }, 'json');
}

$(function () {
    // первая загрузка
    loadNews(1);
    $('#pagination').on('click', 'span', function () {
        if ($(this).hasClass('disabled') || $(this).hasClass('active')) return;
        const page = $(this).data('page');
        loadNews(page);
    });
    // клик по "More info"
    $('#news-container').on('click', '.news-more', function () {
        const id = $(this).data('id');
        openModal(id);
    });
    // закрытие модалки
    $('#modal-close').on('click', function () {
        $('#news-modal').hide();
    });
    $('#news-modal').on('click', function (e) {
        if (e.target.id === 'news-modal') {
            $('#news-modal').hide();
        }
    });
    // поиск по тексту
    $('#news-search').on('input', function () {
        currentSearch = $(this).val();
        loadNews(1);
    });
});
