<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-rss me-2"></i> Управление фидами</h5>
                <a href="./?page=create" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i> Новый фид
                </a>
            </div>
            <div class="card-body">
                <!-- Фильтры -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" id="search-filter" class="form-control" placeholder="Поиск...">
                    </div>
                    <div class="col-md-3">
                        <select id="status-filter" class="form-select">
                            <option value="">Все статусы</option>
                            <option value="active">Активные</option>
                            <option value="inactive">Неактивные</option>
                            <option value="error">С ошибками</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary" onclick="loadFeeds()">
                                <i class="fas fa-redo"></i> Обновить
                            </button>
                            <button class="btn btn-outline-info" onclick="exportFeeds()">
                                <i class="fas fa-download"></i> Экспорт
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Таблица фидов -->
                <div class="table-responsive">
                    <table class="table table-hover" id="feeds-table">
                        <thead>
                            <tr>
                                <th width="30%">Название</th>
                                <th>URL фида</th>
                                <th>Товары</th>
                                <th>Обновление</th>
                                <th>Статус</th>
                                <th width="15%">Действия</th>
                            </tr>
                        </thead>
                        <tbody id="feeds-body">
                            <!-- Заполняется через JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <span id="feeds-info" class="text-muted">Загрузка...</span>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Page navigation" class="float-end">
                            <ul class="pagination pagination-sm" id="pagination">
                                <!-- Заполняется через JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let totalPages = 1;
let searchQuery = '';
let statusFilter = '';

$(document).ready(function() {
    loadFeeds();
    
    // Поиск при вводе
    $('#search-filter').on('keyup', function() {
        searchQuery = $(this).val();
        currentPage = 1;
        loadFeeds();
    });
    
    // Фильтр по статусу
    $('#status-filter').on('change', function() {
        statusFilter = $(this).val();
        currentPage = 1;
        loadFeeds();
    });
});

function loadFeeds(page = 1) {
    currentPage = page;
    
    let url = `api.php?action=feeds&page=${page}&limit=10`;
    if (searchQuery) url += `&search=${encodeURIComponent(searchQuery)}`;
    if (statusFilter) url += `&status=${statusFilter}`;
    
    $.ajax({
        url: url,
        method: 'GET',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken
        },
        success: function(data) {
            renderFeedsTable(data);
            renderPagination(data.pagination);
        },
        error: function() {
            showNotification('Ошибка при загрузке фидов', 'danger');
        }
    });
}

function renderFeedsTable(data) {
    let html = '';
    
    if (data.data && data.data.length > 0) {
        data.data.forEach(feed => {
            // Форматируем время
            const lastUpdate = new Date(feed.last_updated);
            const nextUpdate = new Date(feed.next_update);
            const now = new Date();
            
            // Определяем статус
            let statusBadge = '';
            let statusClass = '';
            
            if (feed.status === 'active') {
                statusClass = nextUpdate < now ? 'warning' : 'success';
                statusBadge = `<span class="badge bg-${statusClass}">Активен</span>`;
            } else if (feed.status === 'error') {
                statusBadge = '<span class="badge bg-danger">Ошибка</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary">Неактивен</span>';
            }
            
            html += `
            <tr>
                <td>
                    <strong>${feed.name}</strong><br>
                    <small class="text-muted">ID: ${feed.id}</small>
                </td>
                <td>
                    <small>
                        <a href="${feed.yml_url}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                            ${feed.yml_url}
                        </a>
                    </small>
                </td>
                <td>
                    <span class="badge bg-info">${feed.items_count || 0}</span>
                    ${feed.file_exists ? 
                        `<small class="text-muted ms-1">(${formatFileSize(feed.file_size)})</small>` : 
                        `<small class="text-danger ms-1">(файл отсутствует)</small>`
                    }
                </td>
                <td>
                    <small>
                        <div>Последнее: ${lastUpdate.toLocaleString('ru-RU')}</div>
                        <div>Следующее: ${nextUpdate.toLocaleString('ru-RU')}</div>
                    </small>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="${feed.file_url}" class="btn btn-outline-primary" title="Скачать">
                            <i class="fas fa-download"></i>
                        </a>
                        <button class="btn btn-outline-info" onclick="showFeedDetails('${feed.id}')" title="Детали">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="manualUpdateFeed('${feed.id}')" title="Обновить">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteFeed('${feed.id}', '${feed.name}')" title="Удалить">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            `;
        });
    } else {
        html = '<tr><td colspan="6" class="text-center text-muted">Нет фидов</td></tr>';
    }
    
    $('#feeds-body').html(html);
    
    // Обновляем информацию о количестве
    const total = data.pagination?.total || 0;
    const from = (data.pagination?.page - 1) * data.pagination?.limit + 1;
    const to = Math.min(data.pagination?.page * data.pagination?.limit, total);
    $('#feeds-info').text(`Показано ${from}-${to} из ${total} фидов`);
}

function renderPagination(pagination) {
    totalPages = pagination.pages;
    
    let html = '';
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    // Кнопка "Назад"
    html += `
    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadFeeds(${currentPage - 1})">&laquo;</a>
    </li>
    `;
    
    // Первая страница
    if (startPage > 1) {
        html += `
        <li class="page-item">
            <a class="page-link" href="#" onclick="loadFeeds(1)">1</a>
        </li>
        ${startPage > 2 ? '<li class="page-item disabled"><span class="page-link">...</span></li>' : ''}
        `;
    }
    
    // Страницы
    for (let i = startPage; i <= endPage; i++) {
        html += `
        <li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="loadFeeds(${i})">${i}</a>
        </li>
        `;
    }
    
    // Последняя страница
    if (endPage < totalPages) {
        html += `
        ${endPage < totalPages - 1 ? '<li class="page-item disabled"><span class="page-link">...</span></li>' : ''}
        <li class="page-item">
            <a class="page-link" href="#" onclick="loadFeeds(${totalPages})">${totalPages}</a>
        </li>
        `;
    }
    
    // Кнопка "Вперед"
    html += `
    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadFeeds(${currentPage + 1})">&raquo;</a>
    </li>
    `;
    
    $('#pagination').html(html);
}

function showFeedDetails(feedId) {
    $.ajax({
        url: `api.php?action=feed&id=${feedId}`,
        method: 'GET',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken
        },
        success: function(feed) {
            let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Основная информация</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">ID:</th>
                            <td>${feed.id}</td>
                        </tr>
                        <tr>
                            <th>Название:</th>
                            <td>${feed.name}</td>
                        </tr>
                        <tr>
                            <th>Статус:</th>
                            <td>
                                <span class="badge bg-${feed.status === 'active' ? 'success' : 'secondary'}">
                                    ${feed.status === 'active' ? 'Активен' : 'Неактивен'}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Товаров:</th>
                            <td>
                                <span class="badge bg-info">${feed.items_count || 0}</span>
                                ${feed.xml_items_count ? 
                                    `<span class="text-muted ms-1">(в XML: ${feed.xml_items_count})</span>` : 
                                    ''
                                }
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Временные метки</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Создан:</th>
                            <td>${new Date(feed.created_at).toLocaleString('ru-RU')}</td>
                        </tr>
                        <tr>
                            <th>Последнее обновление:</th>
                            <td>${new Date(feed.last_updated).toLocaleString('ru-RU')}</td>
                        </tr>
                        <tr>
                            <th>Следующее обновление:</th>
                            <td>
                                <span class="badge bg-info">
                                    ${new Date(feed.next_update).toLocaleString('ru-RU')}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Интервал:</th>
                            <td>Каждые ${feed.update_interval} минут</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <h6>URL адреса</h6>
                    <div class="mb-2">
                        <strong>Исходный YML:</strong><br>
                        <a href="${feed.yml_url}" target="_blank" class="small">${feed.yml_url}</a>
                    </div>
                    <div>
                        <strong>Сконвертированный Youla:</strong><br>
                        <a href="${feed.youla_url}" target="_blank" class="small">${feed.youla_url}</a>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <h6>Информация о файле</h6>
                    ${feed.file_exists ? 
                        `<div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Файл существует
                            <br>Размер: ${formatFileSize(feed.file_size)}
                            <br>Изменен: ${feed.file_mtime ? new Date(feed.file_mtime).toLocaleString('ru-RU') : 'N/A'}
                        </div>` :
                        `<div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> Файл отсутствует
                        </div>`
                    }
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-primary" onclick="manualUpdateFeed('${feed.id}')">
                            <i class="fas fa-sync-alt me-1"></i> Обновить сейчас
                        </button>
                        <a href="${feed.youla_url}" class="btn btn-success" target="_blank">
                            <i class="fas fa-download me-1"></i> Скачать XML
                        </a>
                        <button class="btn btn-warning" onclick="editFeed('${feed.id}')">
                            <i class="fas fa-edit me-1"></i> Редактировать
                        </button>
                    </div>
                </div>
            </div>
            `;
            
            $('#feedDetailsContent').html(html);
            $('#feedDetailsModal').modal('show');
        },
        error: function() {
            showNotification('Ошибка при загрузке деталей фида', 'danger');
        }
    });
}

function manualUpdateFeed(feedId) {
    $.ajax({
        url: `api.php?action=update&id=${feedId}`,
        method: 'POST',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken
        },
        success: function(data) {
            showNotification(data.message, 'success');
            loadFeeds(currentPage); // Обновляем таблицу
            $('#feedDetailsModal').modal('hide');
        },
        error: function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Ошибка обновления';
            showNotification(error, 'danger');
        }
    });
}

function deleteFeed(feedId, feedName) {
    $('#confirmMessage').html(`
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Вы уверены, что хотите удалить фид "<strong>${feedName}</strong>"?
            <br><br>
            <small class="text-muted">Будет удален XML файл и все связанные данные. Это действие нельзя отменить.</small>
        </div>
    `);
    
    $('#confirmButton').off('click').on('click', function() {
        $.ajax({
            url: `api.php?action=feed&id=${feedId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': window.appConfig.csrfToken
            },
            success: function(data) {
                showNotification(data.message, 'success');
                loadFeeds(currentPage); // Обновляем таблицу
                $('#confirmModal').modal('hide');
            },
            error: function(xhr) {
                const error = xhr.responseJSON ? xhr.responseJSON.message : 'Ошибка удаления';
                showNotification(error, 'danger');
                $('#confirmModal').modal('hide');
            }
        });
    });
    
    $('#confirmModal').modal('show');
}

function editFeed(feedId) {
    window.location.href = `./?page=edit&id=${feedId}`;
}

function exportFeeds() {
    // В будущем можно реализовать экспорт в CSV
    showNotification('Экспорт в разработке', 'info');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>