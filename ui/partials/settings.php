<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Настройки системы</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-4" id="settingsTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#general">
                            <i class="fas fa-sliders-h me-1"></i> Основные
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#categories">
                            <i class="fas fa-list-alt me-1"></i> Категории
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#cron">
                            <i class="fas fa-clock me-1"></i> Cron задачи
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#backup">
                            <i class="fas fa-database me-1"></i> Резервные копии
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Основные настройки -->
                    <div class="tab-pane fade show active" id="general">
                        <form id="general-settings-form">
                            <div class="mb-3">
                                <label class="form-label">Название системы</label>
                                <input type="text" class="form-control" value="YML to Youla Converter" disabled>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Путь к фидам</label>
                                <input type="text" class="form-control" value="./feeds/" disabled>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Максимальный размер фида</label>
                                <select class="form-select" disabled>
                                    <option>50 MB</option>
                                    <option>100 MB</option>
                                    <option>500 MB</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Таймаут загрузки (секунд)</label>
                                <input type="number" class="form-control" value="30" disabled>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="debug-mode" disabled>
                                <label class="form-check-label" for="debug-mode">Режим отладки</label>
                            </div>
                            
                            <button type="button" class="btn btn-primary" disabled>
                                <i class="fas fa-save me-1"></i> Сохранить настройки
                            </button>
                        </form>
                    </div>
                    
                    <!-- Категории -->
                    <div class="tab-pane fade" id="categories">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Настройки соответствия категорий Яндекс.Маркета и Юлы.
                            Эти настройки определяют, как товары будут распределены по категориям Юлы.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Категория Юлы</th>
                                        <th>ID категории</th>
                                        <th>Параметр</th>
                                        <th>Значения</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Бытовая техника → Климатическая техника</td>
                                        <td><span class="badge bg-info">2 / 212</span></td>
                                        <td><code>klimaticheskaya_tip</code></td>
                                        <td>
                                            <span class="badge bg-secondary me-1">9920 - Вентиляторы</span>
                                            <span class="badge bg-secondary me-1">9921 - Ионизаторы</span>
                                            <span class="badge bg-secondary me-1">9922 - Метеостанции</span>
                                            <span class="badge bg-secondary me-1">9923 - Мобильные кондиционеры</span>
                                            <span class="badge bg-secondary me-1">9924 - Настенные кондиционеры</span>
                                            <span class="badge bg-secondary me-1">9925 - Обогревательные приборы</span>
                                            <span class="badge bg-secondary me-1">9926 - Очистители воздуха</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Ремонт и строительство → Отопление и вентиляция</td>
                                        <td><span class="badge bg-info">6 / 610</span></td>
                                        <td><code>otoplenie_ventilyaciya_tip</code></td>
                                        <td>
                                            <span class="badge bg-secondary me-1">9519 - Вентиляция</span>
                                            <span class="badge bg-secondary me-1">9520 - Газовые баллоны</span>
                                            <span class="badge bg-secondary me-1">9521 - Камины и печи</span>
                                            <span class="badge bg-secondary me-1">9522 - Отопительные котлы</span>
                                            <span class="badge bg-secondary me-1">9523 - Радиаторы</span>
                                            <span class="badge bg-secondary me-1">9524 - Теплый пол</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Бытовая техника → Вытяжки</td>
                                        <td><span class="badge bg-info">2 / 206</span></td>
                                        <td><em>без параметра</em></td>
                                        <td><span class="badge bg-secondary">Вытяжки кухонные</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Изменение настроек категорий может повлиять на работу существующих фидов.
                            Рекомендуется тестировать изменения на копии фида.
                        </div>
                    </div>
                    
                    <!-- Cron задачи -->
                    <div class="tab-pane fade" id="cron">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Настройки автоматического обновления фидов. Cron задачи выполняются на сервере по расписанию.
                        </div>
                        
                        <div class="mb-3">
                            <h6>Команда для cron</h6>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="cron-command" 
                                       value="<?php echo realpath(__DIR__ . '/../../cron.php'); ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyCronCommand()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <small class="text-muted">Добавьте эту команду в crontab для автоматического обновления фидов.</small>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Пример конфигурации cron</h6>
                            <pre class="bg-light p-3 rounded"><code># Обновлять фиды каждые 30 минут
*/30 * * * * php <?php echo realpath(__DIR__ . '/../../cron.php'); ?>

# Или используя wget (если PHP через веб-сервер)
*/30 * * * * wget -q -O /dev/null https://ваш-сайт.ru/yml-to-youla/cron.php</code></pre>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Статус cron</h6>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Cron задачи активны. Последний запуск: <span id="last-cron-run"><?php 
                                    $logFile = __DIR__ . '/../../logs/cron.log';
                                    if (file_exists($logFile)) {
                                        $lines = file($logFile);
                                        $lastLine = end($lines);
                                        echo $lastLine ? substr($lastLine, 0, 19) : 'никогда';
                                    } else {
                                        echo 'никогда';
                                    }
                                ?></span>
                            </div>
                        </div>
                        
                        <button class="btn btn-success" onclick="testCron()">
                            <i class="fas fa-play me-1"></i> Тестовый запуск cron
                        </button>
                    </div>
                    
                    <!-- Резервные копии -->
                    <div class="tab-pane fade" id="backup">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Управление резервными копиями данных системы.
                        </div>
                        
                        <div class="mb-3">
                            <h6>Создать резервную копию</h6>
                            <p>Создаст архив со всеми фидами и базой данных.</p>
                            <button class="btn btn-primary" onclick="createBackup()">
                                <i class="fas fa-download me-1"></i> Создать backup
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Восстановить из backup</h6>
                            <div class="input-group mb-2">
                                <input type="file" class="form-control" id="backup-file" accept=".zip,.tar.gz">
                                <button class="btn btn-warning" onclick="restoreBackup()">
                                    <i class="fas fa-upload me-1"></i> Восстановить
                                </button>
                            </div>
                            <small class="text-muted">Внимание: восстановление перезапишет текущие данные!</small>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Существующие резервные копии</h6>
                            <div class="list-group" id="backup-list">
                                <div class="list-group-item text-center text-muted">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Загрузка списка...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyCronCommand() {
    const command = document.getElementById('cron-command');
    command.select();
    document.execCommand('copy');
    showNotification('Команда скопирована в буфер обмена', 'success');
}

function testCron() {
    $.ajax({
        url: 'api.php?action=cron',
        method: 'GET',
        headers: {
            'X-CSRF-Token': window.appConfig.csrfToken
        },
        success: function(data) {
            showNotification(`Cron выполнен. Обновлено фидов: ${data.feeds_updated || 0}`, 'success');
            $('#last-cron-run').text(new Date().toLocaleString('ru-RU'));
        },
        error: function() {
            showNotification('Ошибка при запуске cron', 'danger');
        }
    });
}

function createBackup() {
    // В реальном приложении здесь будет вызов API для создания backup
    showNotification('Функция создания backup в разработке', 'info');
}

function restoreBackup() {
    // В реальном приложении здесь будет вызов API для восстановления backup
    showNotification('Функция восстановления backup в разработке', 'info');
}

// Загружаем список backup при открытии вкладки
$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
    if (e.target.getAttribute('href') === '#backup') {
        loadBackupList();
    }
});

function loadBackupList() {
    // В реальном приложении здесь будет загрузка списка backup
    setTimeout(() => {
        $('#backup-list').html(`
            <div class="list-group-item text-center text-muted">
                <i class="fas fa-exclamation-circle me-1"></i>
                Функция backup в разработке
            </div>
        `);
    }, 500);
}
</script>