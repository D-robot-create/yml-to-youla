<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i> Помощь и документация</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="helpAccordion">
                    <!-- Раздел 1 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#help1">
                                <i class="fas fa-play-circle me-2"></i> Быстрый старт
                            </button>
                        </h2>
                        <div id="help1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <h5>Как создать первый фид:</h5>
                                <ol>
                                    <li>Перейдите в раздел "Новый фид"</li>
                                    <li>Введите URL вашего YML фида Яндекс.Маркета</li>
                                    <li>Нажмите "Проверить URL" для проверки доступности</li>
                                    <li>Задайте название фида и интервал обновления</li>
                                    <li>Нажмите "Создать фид"</li>
                                    <li>Используйте полученную ссылку для добавления фида в Юлу</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Раздел 2 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help2">
                                <i class="fas fa-cog me-2"></i> Настройка cron
                            </button>
                        </h2>
                        <div id="help2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <h5>Настройка автоматического обновления:</h5>
                                <p>Для автоматического обновления фидов необходимо настроить cron задачу:</p>
                                <pre class="bg-light p-3 rounded"><code># Обновлять фиды каждые 30 минут
*/30 * * * * php /полный/путь/к/yml-to-youla/cron.php</code></pre>
                                <p>Или используя wget (если PHP через веб-сервер):</p>
                                <pre class="bg-light p-3 rounded"><code>*/30 * * * * wget -q -O /dev/null https://ваш-сайт.ru/yml-to-youla/cron.php</code></pre>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Раздел 3 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help3">
                                <i class="fas fa-list-alt me-2"></i> Категории Юлы
                            </button>
                        </h2>
                        <div id="help3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <h5>Поддерживаемые категории:</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Категория Юлы</th>
                                            <th>ID</th>
                                            <th>Параметр</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Бытовая техника → Климатическая техника</td>
                                            <td><span class="badge bg-info">2 / 212</span></td>
                                            <td><code>klimaticheskaya_tip</code></td>
                                        </tr>
                                        <tr>
                                            <td>Ремонт и строительство → Отопление и вентиляция</td>
                                            <td><span class="badge bg-info">6 / 610</span></td>
                                            <td><code>otoplenie_ventilyaciya_tip</code></td>
                                        </tr>
                                        <tr>
                                            <td>Бытовая техника → Вытяжки</td>
                                            <td><span class="badge bg-info">2 / 206</span></td>
                                            <td><em>без параметра</em></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Раздел 4 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help4">
                                <i class="fas fa-exclamation-triangle me-2"></i> Решение проблем
                            </button>
                        </h2>
                        <div id="help4" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <h5>Частые проблемы и решения:</h5>
                                <div class="mb-3">
                                    <strong>Ошибка "Cannot fetch YML feed":</strong>
                                    <ul>
                                        <li>Проверьте доступность YML фида по URL</li>
                                        <li>Убедитесь, что фид не требует авторизации</li>
                                        <li>Проверьте настройки firewall на сервере</li>
                                    </ul>
                                </div>
                                <div class="mb-3">
                                    <strong>Фиды не обновляются автоматически:</strong>
                                    <ul>
                                        <li>Проверьте настройку cron задачи</li>
                                        <li>Убедитесь, что cron имеет права на выполнение PHP скриптов</li>
                                        <li>Проверьте логи в разделе "Логи"</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong>Пустой XML файл:</strong>
                                    <ul>
                                        <li>Проверьте, что YML фид содержит товары</li>
                                        <li>Убедитесь, что товары имеют цены и названия</li>
                                        <li>Проверьте логи конвертации</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>