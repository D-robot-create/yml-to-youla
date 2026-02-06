<?php
// Проверяем, передан ли ID фида
$feedId = $_GET['id'] ?? '';
if (empty($feedId)) {
    echo '<div class="alert alert-danger">ID фида не указан</div>';
    return;
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Редактирование фида <span id="feed-name-title"></span></h5>
            </div>
            <div class="card-body">
                <div id="loading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3">Загрузка данных фида...</p>
                </div>
                
                <div id="edit-form" style="display: none;">
                    <form id="feed-edit-form">
                        <div class="mb-3">
                            <label for="edit-feed-name" class="form-label">Название фида</label>
                            <input type="text" class="form-control" id="edit-feed-name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit-update-interval" class="form-label">Интервал обновления (минуты)</label>
                            <input type="number" class="form-control" id="edit-update-interval" min="5" max="1440" required>
                            <div class="form-text">Минимум 5 минут, максимум 1440 минут (24 часа)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Текущий URL фида</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="edit-yml-url" readonly>
                                <a class="btn btn-outline-secondary" id="edit-yml-link" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Ссылка на фид Юлы</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="edit-youla-url" readonly>
                                <a class="btn btn-outline-secondary" id="edit-youla-link" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            URL фида изменить нельзя. Для изменения URL создайте новый фид.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="./?page=feeds" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Назад к списку
                            </a>
                            <div>
                                <button type="button" class="btn btn-danger me-2" onclick="deleteCurrentFeed()">
                                    <i class="fas fa-trash me-1"></i> Удалить
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Сохранить изменения
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div id="error-message" style="display: none;">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <span id="error-text"></span>
                    </div>
                    <a href="./?page=feeds" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Назад к списку
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const feedId = '<?php echo $feedId; ?>';

$(document).ready(function() {
    loadFeedData();
});

function loadFeedData() {
    $.ajax({
        url: `api.php?action=feed&id=${feedId}`,
        method: 'GET',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken
        },
        success: function(feed) {
            // Заполняем форму
            $('#feed-name-title').text(feed.name);
            $('#edit-feed-name').val(feed.name);
            $('#edit-update-interval').val(feed.update_interval);
            $('#edit-yml-url').val(feed.yml_url);
            $('#edit-youla-url').val(feed.youla_url);
            
            // Устанавливаем ссылки
            $('#edit-yml-link').attr('href', feed.yml_url);
            $('#edit-youla-link').attr('href', feed.youla_url);
            
            // Показываем форму
            $('#loading').hide();
            $('#edit-form').show();
        },
        error: function(xhr) {
            $('#loading').hide();
            
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Ошибка загрузки данных фида';
            $('#error-text').text(error);
            $('#error-message').show();
        }
    });
}

// Обработка формы
$('#feed-edit-form').on('submit', function(e) {
    e.preventDefault();
    
    const data = {
        name: $('#edit-feed-name').val(),
        update_interval: $('#edit-update-interval').val()
    };
    
    $.ajax({
        url: `api.php?action=feed&id=${feedId}`,
        method: 'PUT',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken,
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(data),
        success: function(response) {
            showNotification(response.message, 'success');
            // Возвращаемся к списку через 1 секунду
            setTimeout(() => {
                window.location.href = './?page=feeds';
            }, 1000);
        },
        error: function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Ошибка сохранения';
            showNotification(error, 'danger');
        }
    });
});

function deleteCurrentFeed() {
    if (!confirm('Вы уверены, что хотите удалить этот фид? Все связанные данные будут удалены.')) {
        return;
    }
    
    $.ajax({
        url: `api.php?action=feed&id=${feedId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken
        },
        success: function(response) {
            showNotification(response.message, 'success');
            setTimeout(() => {
                window.location.href = './?page=feeds';
            }, 1000);
        },
        error: function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Ошибка удаления';
            showNotification(error, 'danger');
        }
    });
}
</script>