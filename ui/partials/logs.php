<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Логи системы</h5>
                <div>
                    <button class="btn btn-light btn-sm me-2" onclick="clearLogs()">
                        <i class="fas fa-trash me-1"></i> Очистить логи
                    </button>
                    <button class="btn btn-light btn-sm" onclick="downloadLogs()">
                        <i class="fas fa-download me-1"></i> Скачать логи
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Фильтры -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select" id="log-feed-filter" onchange="loadLogs()">
                            <option value="">Все фиды</option>
                            <!-- Заполняется через JavaScript -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="log-level-filter" onchange="loadLogs()">
                            <option value="">Все уровни</option>
                            <option value="info">Информация</option>
                            <option value="success">Успех</option>
                            <option value="warning">Предупреждение</option>
                            <option value="error">Ошибка</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="date" class="form-control" id="log-date-filter" onchange="loadLogs()">
                    </div>
                </div>
                
                <!-- Таблица логов -->
                <div class="table-responsive">
                    <table class="table table-sm" id="logs-table">
                        <thead>
                            <tr>
                                <th width="20%">Время</th>
                                <th width="15%">Фид</th>
                                <th>Сообщение</th>
                                <th width="10%">Уровень</th>
                            </tr>
                        </thead>
                        <tbody id="logs-body">
                            <!-- Заполняется через JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <span id="logs-info" class="text-muted">Загрузка...</span>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Page navigation" class="float-end">
                            <ul class="pagination pagination-sm" id="logs-pagination">
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
let logsCurrentPage = 1;
let logsTotalPages = 1;
let selectedFeedId = '';
let selectedLevel = '';
let selectedDate = '';

$(document).ready(function() {
    loadFeedList();
    loadLogs();
    
    // Устанавливаем сегодняшнюю дату по умолчанию
    const today = new Date().toISOString().split('T')[0];
    $('#log-date-filter').val(today);
});

function loadFeedList() {
    $.ajax({
        url: 'api.php?action=feeds&limit=1000',
        method: 'GET',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken
        },
        success: function(data) {
            let options = '<option value="">Все фиды</option>';
            
            if (data.data && data.data.length > 0) {
                data.data.forEach(feed => {
                    options += `<option value="${feed.id}">${feed.name}</option>`;
                });
            }
            
            $('#log-feed-filter').html(options);
        }
    });
}

function loadLogs(page = 1) {
    logsCurrentPage = page;
    
    selectedFeedId = $('#log-feed-filter').val();
    selectedLevel = $('#log-level-filter').val();
    selectedDate = $('#log-date-filter').val();
    
    let url = `api.php?action=logs&page=${page}&limit=20`;
    if (selectedFeedId) url += `&feed_id=${selectedFeedId}`;
    
    $.ajax({
        url: url,
        method: 'GET',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken
        },
        success: function(data) {
            renderLogsTable(data);
            renderLogsPagination(data.pagination);
        },
        error: function() {
            showNotification('Ошибка при загрузке логов', 'danger');
        }
    });
}

function renderLogsTable(data) {
    let html = '';
    
    if (data.data && data.data.length > 0) {
        // Фильтруем по уровню и дате на клиенте (можно сделать на сервере)
        const filteredLogs = data.data.filter(log => {
            if (selectedLevel) {
                const logLevel = getLogLevel(log.message);
                if (selectedLevel !== logLevel) return false;
            }
            
            if (selectedDate) {
                const logDate = new Date(log.created_at).toISOString().split('T')[0];
                if (selectedDate !== logDate) return false;
            }
            
            return true;
        });
        
        if (filteredLogs.length > 0) {
            filteredLogs.forEach(log => {
                const logTime = new Date(log.created_at);
                const logLevel = getLogLevel(log.message);
                const levelClass = getLevelClass(logLevel);
                
                html += `
                <tr>
                    <td>
                        <small>${logTime.toLocaleString('ru-RU')}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${log.feed_id}</span>
                    </td>
                    <td>
                        <div>${log.message}</div>
                        <small class="text-muted">ID: ${log.id}</small>
                    </td>
                    <td>
                        <span class="badge ${levelClass}">${logLevel}</span>
                    </td>
                </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="4" class="text-center text-muted">Нет логов по заданным фильтрам</td></tr>';
        }
    } else {
        html = '<tr><td colspan="4" class="text-center text-muted">Логи отсутствуют</td></tr>';
    }
    
    $('#logs-body').html(html);
    
    // Обновляем информацию о количестве
    const total = data.pagination?.total || 0;
    $('#logs-info').text(`Всего записей: ${total}`);
}

function renderLogsPagination(pagination) {
    logsTotalPages = pagination.pages;
    
    let html = '';
    const maxVisible = 5;
    let startPage = Math.max(1, logsCurrentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(logsTotalPages, startPage + maxVisible - 1);
    
    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    // Кнопка "Назад"
    html += `
    <li class="page-item ${logsCurrentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadLogs(${logsCurrentPage - 1})">&laquo;</a>
    </li>
    `;
    
    // Первая страница
    if (startPage > 1) {
        html += `
        <li class="page-item">
            <a class="page-link" href="#" onclick="loadLogs(1)">1</a>
        </li>
        ${startPage > 2 ? '<li class="page-item disabled"><span class="page-link">...</span></li>' : ''}
        `;
    }
    
    // Страницы
    for (let i = startPage; i <= endPage; i++) {
        html += `
        <li class="page-item ${i === logsCurrentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="loadLogs(${i})">${i}</a>
        </li>
        `;
    }
    
    // Последняя страница
    if (endPage < logsTotalPages) {
        html += `
        ${endPage < logsTotalPages - 1 ? '<li class="page-item disabled"><span class="page-link">...</span></li>' : ''}
        <li class="page-item">
            <a class="page-link" href="#" onclick="loadLogs(${logsTotalPages})">${logsTotalPages}</a>
        </li>
        `;
    }
    
    // Кнопка "Вперед"
    html += `
    <li class="page-item ${logsCurrentPage === logsTotalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadLogs(${logsCurrentPage + 1})">&raquo;</a>
    </li>
    `;
    
    $('#logs-pagination').html(html);
}

function getLogLevel(message) {
    const lowerMessage = message.toLowerCase();
    
    if (lowerMessage.includes('ошибка') || lowerMessage.includes('error') || lowerMessage.includes('failed')) {
        return 'error';
    } else if (lowerMessage.includes('успешно') || lowerMessage.includes('success') || lowerMessage.includes('created')) {
        return 'success';
    } else if (lowerMessage.includes('предупреждение') || lowerMessage.includes('warning')) {
        return 'warning';
    } else {
        return 'info';
    }
}

function getLevelClass(level) {
    switch (level) {
        case 'error': return 'bg-danger';
        case 'success': return 'bg-success';
        case 'warning': return 'bg-warning';
        default: return 'bg-info';
    }
}

function clearLogs() {
    if (!confirm('Вы уверены, что хотите очистить все логи? Это действие нельзя отменить.')) {
        return;
    }
    
    // В реальном приложении здесь будет вызов API для очистки логов
    showNotification('Функция очистки логов в разработке', 'info');
}

function downloadLogs() {
    // В реальном приложении здесь будет вызов API для скачивания логов
    showNotification('Функция скачивания логов в разработке', 'info');
}
</script>