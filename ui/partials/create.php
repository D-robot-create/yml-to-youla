<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Создание нового фида</h5>
            </div>
            <div class="card-body">
                <!-- Шаги -->
                <div class="steps mb-4">
                    <div class="d-flex justify-content-between">
                        <div class="step active" id="step1">
                            <div class="step-circle">1</div>
                            <div class="step-label">Ввод URL</div>
                        </div>
                        <div class="step" id="step2">
                            <div class="step-circle">2</div>
                            <div class="step-label">Настройки</div>
                        </div>
                        <div class="step" id="step3">
                            <div class="step-circle">3</div>
                            <div class="step-label">Проверка</div>
                        </div>
                        <div class="step" id="step4">
                            <div class="step-circle">4</div>
                            <div class="step-label">Готово</div>
                        </div>
                    </div>
                </div>
                
                <!-- Шаг 1: Ввод URL -->
                <div id="step1-content" class="step-content">
                    <div class="mb-3">
                        <label for="yml-url" class="form-label">
                            <i class="fas fa-link me-1"></i> URL YML фида Яндекс.Маркета
                        </label>
                        <input type="url" class="form-control" id="yml-url" 
                               placeholder="https://example.com/feed.yml" required>
                        <div class="form-text">
                            Введите полный URL вашего YML фида. Пример: https://shop.ru/yml_export.xml
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button class="btn btn-primary" onclick="testUrl()">
                            <i class="fas fa-search me-1"></i> Проверить URL
                        </button>
                    </div>
                    
                    <div id="url-test-result"></div>
                    
                    <div class="text-end">
                        <button class="btn btn-success" onclick="nextStep(2)" disabled id="next-step1">
                            Далее <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Шаг 2: Настройки -->
                <div id="step2-content" class="step-content" style="display: none;">
                    <div class="mb-3">
                        <label for="feed-name" class="form-label">
                            <i class="fas fa-tag me-1"></i> Название фида
                        </label>
                        <input type="text" class="form-control" id="feed-name" 
                               placeholder="Мой магазин техники" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="update-interval" class="form-label">
                            <i class="fas fa-clock me-1"></i> Интервал обновления
                        </label>
                        <select class="form-select" id="update-interval">
                            <option value="15">Каждые 15 минут</option>
                            <option value="30" selected>Каждые 30 минут</option>
                            <option value="60">Каждый час</option>
                            <option value="180">Каждые 3 часа</option>
                            <option value="360">Каждые 6 часов</option>
                            <option value="720">Каждые 12 часов</option>
                            <option value="1440">Раз в день</option>
                        </select>
                        <div class="form-text">
                            Как часто автоматически обновлять фид. Рекомендуется каждые 30-60 минут.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-filter me-1"></i> Категории для конвертации
                        </label>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Система автоматически определяет категории Юлы на основе категорий Яндекс.Маркета.
                            Поддерживаемые категории:
                            <ul class="mb-0 mt-1">
                                <li><strong>Бытовая техника</strong> → Климатическая техника</li>
                                <li><strong>Бытовая техника</strong> → Вытяжки</li>
                                <li><strong>Ремонт и строительство</strong> → Отопление и вентиляция</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-secondary" onclick="prevStep(2)">
                            <i class="fas fa-arrow-left me-1"></i> Назад
                        </button>
                        <button class="btn btn-success" onclick="nextStep(3)">
                            Далее <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Шаг 3: Проверка -->
                <div id="step3-content" class="step-content" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Проверьте правильность введенных данных перед созданием фида.
                    </div>
                    
                    <table class="table table-bordered" id="review-table">
                        <tbody>
                            <!-- Заполняется через JavaScript -->
                        </tbody>
                    </table>
                    
                    <div class="text-center">
                        <button class="btn btn-lg btn-success" onclick="createFeed()" id="create-feed-btn">
                            <i class="fas fa-check me-1"></i> Создать фид
                        </button>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-secondary" onclick="prevStep(3)">
                            <i class="fas fa-arrow-left me-1"></i> Назад
                        </button>
                    </div>
                </div>
                
                <!-- Шаг 4: Готово -->
                <div id="step4-content" class="step-content" style="display: none;">
                    <div class="text-center py-5" id="step4-result">
                        <!-- Заполняется через JavaScript -->
                    </div>
                    
                    <div class="text-center">
                        <a href="./?page=feeds" class="btn btn-primary me-2">
                            <i class="fas fa-list me-1"></i> К списку фидов
                        </a>
                        <a href="./?page=create" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i> Создать еще один фид
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.steps {
    position: relative;
}

.steps:before {
    content: '';
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #dee2e6;
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #dee2e6;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-weight: bold;
    font-size: 1.2rem;
    border: 3px solid white;
}

.step.active .step-circle {
    background-color: #0d6efd;
    color: white;
}

.step.completed .step-circle {
    background-color: #198754;
    color: white;
}

.step-label {
    font-size: 0.9rem;
    color: #6c757d;
}

.step.active .step-label {
    color: #0d6efd;
    font-weight: bold;
}

.step-content {
    min-height: 300px;
}
</style>

<script>
let currentStep = 1;
let feedData = {
    yml_url: '',
    name: '',
    update_interval: 30,
    test_result: null
};

function nextStep(step) {
    // Сохраняем данные текущего шага
    if (step === 2) {
        feedData.yml_url = $('#yml-url').val();
        if (!feedData.yml_url) {
            showNotification('Введите URL фида', 'warning');
            return;
        }
    } else if (step === 3) {
        feedData.name = $('#feed-name').val();
        feedData.update_interval = $('#update-interval').val();
        
        if (!feedData.name) {
            showNotification('Введите название фида', 'warning');
            return;
        }
    }
    
    // Показываем следующий шаг
    $(`#step${currentStep}-content`).hide();
    $(`#step${step}-content`).show();
    
    // Обновляем индикатор шагов
    $(`#step${currentStep}`).removeClass('active');
    $(`#step${step}`).addClass('active');
    
    if (step === 3) {
        // Заполняем таблицу проверки с использованием sample_product
        let reviewHtml = `
            <tr>
                <th width="30%">URL YML фида:</th>
                <td>${feedData.yml_url}</td>
            </tr>
            <tr>
                <th>Название фида:</th>
                <td>${feedData.name}</td>
            </tr>
            <tr>
                <th>Интервал обновления:</th>
                <td>Каждые ${feedData.update_interval} минут</td>
            </tr>
            <tr>
                <th>Товаров найдено:</th>
                <td>${feedData.test_result?.items_count || 0}</td>
            </tr>`;
        
        // Проверяем sample_product
        if (feedData.test_result?.sample_product?.name) {
            reviewHtml += `
            <tr>
                <th>Пример товара:</th>
                <td>${feedData.test_result.sample_product.name} - ${feedData.test_result.sample_product.price || ''} руб.</td>
            </tr>`;
        } else {
            reviewHtml += `
            <tr>
                <th>Пример товара:</th>
                <td>Не удалось получить пример</td>
            </tr>`;
        }
        
        $('#review-table').html(reviewHtml);
    }
    
    currentStep = step;
}

function prevStep(step) {
    const prevStep = step - 1;
    
    $(`#step${step}-content`).hide();
    $(`#step${prevStep}-content`).show();
    
    $(`#step${step}`).removeClass('active');
    $(`#step${prevStep}`).addClass('active');
    
    currentStep = prevStep;
}

function testUrl() {
    const url = $('#yml-url').val();
    
    if (!url) {
        showNotification('Введите URL для проверки', 'warning');
        return;
    }
    
    // Показываем индикатор загрузки
    $('#url-test-result').html(`
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin me-1"></i> Проверяем URL...
        </div>
    `);
    
    $('#next-step1').prop('disabled', true);
    
    $.ajax({
        url: 'api.php?action=test',
        method: 'POST',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken,
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({ url: url }),
        success: function(data) {
            feedData.test_result = data;
            
            let html = `<div class="alert alert-success">
                <i class="fas fa-check-circle me-1"></i> 
                <strong>Успешно!</strong> YML фид доступен.`;
            
            if (data.items_count !== undefined) {
                html += `<br><small>Найдено товаров: <strong>${data.items_count}</strong></small>`;
            }
            
            // Используем sample_product вместо sample_data
            if (data.sample_product && data.sample_product.name) {
                html += `<br><small>Пример: ${data.sample_product.name}`;
                if (data.sample_product.price) {
                    html += ` - ${data.sample_product.price} руб.`;
                }
                html += `</small>`;
            }
            
            html += `</div>`;
            
            $('#url-test-result').html(html);
            $('#next-step1').prop('disabled', false);
            
            // Автоматически заполняем название
            if (!feedData.name) {
                try {
                    const domain = new URL(url).hostname;
                    $('#feed-name').val(`Фид с ${domain}`);
                } catch (e) {
                    $('#feed-name').val('Мой фид');
                }
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Ошибка проверки URL';
            
            $('#url-test-result').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    <strong>Ошибка!</strong> ${error}
                    <br>
                    <small>Проверьте правильность URL и доступность фида.</small>
                </div>
            `);
            
            $('#next-step1').prop('disabled', true);
            feedData.test_result = null;
        }
    });
}

function createFeed() {
    $('#create-feed-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Создание...');
    
    $.ajax({
        url: 'api.php?action=feeds',
        method: 'POST',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken,
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(feedData),
        success: function(data) {
            // Переходим к шагу 4
            nextStep(4);
            
            $('#step4-result').html(`
                <div class="alert alert-success">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <h4>Фид успешно создан!</h4>
                    <p>Ваш фид был сконвертирован в формат Юлы и готов к использованию.</p>
                    
                    <div class="mt-4">
                        <h5>Информация о фиде:</h5>
                        <ul class="list-unstyled">
                            <li><strong>ID фида:</strong> ${data.feed_id}</li>
                            <li><strong>Создано товаров:</strong> ${data.items_count}</li>
                            <li><strong>URL фида Юлы:</strong> 
                                <br><a href="${data.feed_url}" target="_blank">${data.feed_url}</a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Фиды автоматически обновляются по расписанию. Вы можете вручную обновить фид в любое время.
                        </div>
                    </div>
                </div>
            `);
        },
        error: function(xhr) {
            $('#create-feed-btn').prop('disabled', false).html('<i class="fas fa-check me-1"></i> Создать фид');
            
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Ошибка создания фида';
            showNotification(error, 'danger');
        }
    });
}
</script>