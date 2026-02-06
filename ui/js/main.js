// Глобальные функции для работы с UI

// Показ уведомлений
function showNotification(message, type = 'info', duration = 5000) {
    const notificationArea = document.getElementById('notification-area');
    if (!notificationArea) return;
    
    const notificationId = 'notification-' + Date.now();
    const icon = getNotificationIcon(type);
    
    const notification = document.createElement('div');
    notification.id = notificationId;
    notification.className = `alert alert-${type} alert-dismissible fade show notification`;
    notification.innerHTML = `
        ${icon}
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    notificationArea.appendChild(notification);
    
    // Автоматическое скрытие
    setTimeout(() => {
        const notif = document.getElementById(notificationId);
        if (notif) {
            notif.classList.remove('show');
            setTimeout(() => notif.remove(), 150);
        }
    }, duration);
}

function getNotificationIcon(type) {
    switch(type) {
        case 'success': return '<i class="fas fa-check-circle me-2"></i>';
        case 'danger': return '<i class="fas fa-exclamation-circle me-2"></i>';
        case 'warning': return '<i class="fas fa-exclamation-triangle me-2"></i>';
        case 'info': return '<i class="fas fa-info-circle me-2"></i>';
        default: return '<i class="fas fa-info-circle me-2"></i>';
    }
}

// Форматирование дат
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('ru-RU');
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffDays > 0) {
        return `${diffDays} д. назад`;
    } else if (diffHours > 0) {
        return `${diffHours} ч. назад`;
    } else if (diffMins > 0) {
        return `${diffMins} мин. назад`;
    } else {
        return 'только что';
    }
}

// Форматирование размера файла
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Загрузка CSRF токена
function loadCsrfToken() {
    if (!window.appConfig.csrfToken) {
        $.ajax({
            url: 'api.php?action=csrf',
            method: 'GET',
            async: false,
            success: function(data) {
                window.appConfig.csrfToken = data.csrf_token;
            }
        });
    }
}

// Глобальная обработка ошибок AJAX
$(document).ajaxError(function(event, xhr) {
    if (xhr.status === 403 && xhr.responseJSON?.message?.includes('CSRF')) {
        showNotification('Сессия истекла. Обновите страницу.', 'warning');
        loadCsrfToken();
    } else if (xhr.status === 500) {
        showNotification('Внутренняя ошибка сервера', 'danger');
    } else if (xhr.status === 404) {
        showNotification('Ресурс не найден', 'warning');
    }
});

// Инициализация при загрузке страницы
$(document).ready(function() {
    // Загружаем CSRF токен если его нет
    if (!window.appConfig.csrfToken) {
        loadCsrfToken();
    }
    
    // Инициализация DataTables если есть таблицы
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
            },
            pageLength: 25,
            responsive: true
        });
    }
    
    // Автоматическое скрытие уведомлений
    setTimeout(() => {
        $('.alert:not(.permanent)').alert('close');
    }, 5000);
});

// Копирование в буфер обмена
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Скопировано в буфер обмена', 'success');
    }).catch(err => {
        showNotification('Ошибка копирования', 'danger');
    });
}

// Обновление времени в реальном времени
function startClock() {
    function updateClock() {
        const now = new Date();
        $('.current-time').text(now.toLocaleTimeString('ru-RU'));
        $('.current-date').text(now.toLocaleDateString('ru-RU'));
    }
    
    updateClock();
    setInterval(updateClock, 1000);
}

// Проверка состояния сервера
function checkServerStatus() {
    $.ajax({
        url: 'api.php?action=health',
        method: 'GET',
        timeout: 5000,
        success: function() {
            $('.server-status').removeClass('text-danger').addClass('text-success')
                .html('<i class="fas fa-check-circle"></i> Онлайн');
        },
        error: function() {
            $('.server-status').removeClass('text-success').addClass('text-danger')
                .html('<i class="fas fa-times-circle"></i> Офлайн');
        }
    });
}

// Инициализация всех функций
function initApp() {
    startClock();
    checkServerStatus();
    setInterval(checkServerStatus, 60000); // Проверять каждую минуту
}

// Запуск инициализации при загрузке
$(window).on('load', initApp);