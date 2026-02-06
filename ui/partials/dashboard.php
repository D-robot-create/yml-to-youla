<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i> Дашборд</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h4><i class="fas fa-rocket me-2"></i> Добро пожаловать в YML to Youla Converter!</h4>
                    <p class="mb-0">Система для автоматической конвертации фидов Яндекс.Маркета в формат Юлы.</p>
                </div>
                
                <!-- Быстрые действия -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-plus-circle fa-3x text-success mb-3"></i>
                                <h5>Создать новый фид</h5>
                                <p>Добавьте YML фид для автоматической конвертации</p>
                                <a href="./?page=create" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i> Создать
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-rss fa-3x text-primary mb-3"></i>
                                <h5>Управление фидами</h5>
                                <p>Просмотр и управление существующими фидами</p>
                                <a href="./?page=feeds" class="btn btn-primary">
                                    <i class="fas fa-list me-1"></i> Перейти
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-cog fa-3x text-warning mb-3"></i>
                                <h5>Настройки</h5>
                                <p>Настройка системы и категорий</p>
                                <a href="./?page=settings" class="btn btn-warning">
                                    <i class="fas fa-sliders-h me-1"></i> Настроить
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Информация о системе -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Информация о системе</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>PHP версия:</span>
                                        <span class="badge bg-info"><?php echo phpversion(); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Версия системы:</span>
                                        <span class="badge bg-success">1.0</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Время сервера:</span>
                                        <span class="badge bg-dark" id="server-time"><?php echo date('H:i:s'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Директория фидов:</span>
                                        <span class="badge bg-secondary">/feeds/</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i> Быстрый старт</h6>
                            </div>
                            <div class="card-body">
                                <ol class="mb-0">
                                    <li class="mb-2">Нажмите "Создать" для добавления нового фида</li>
                                    <li class="mb-2">Укажите URL вашего YML фида Яндекс.Маркета</li>
                                    <li class="mb-2">Настройте интервал обновления (рекомендуется 30 минут)</li>
                                    <li class="mb-2">Получите ссылку на фид в формате Юлы</li>
                                    <li>Добавьте ссылку в личном кабинете Юлы</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Обновляем время каждую секунду
setInterval(function() {
    const now = new Date();
    $('#server-time').text(now.toLocaleTimeString('ru-RU'));
}, 1000);
</script>