    </div> <!-- закрываем container -->
    
    <!-- Футер -->
    <footer class="mt-5 py-3 bg-light border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        <i class="fas fa-code text-primary"></i> YML to Youla Converter v1.0
                    </p>
                    <small class="text-muted">Автоматическая конвертация фидов Яндекс.Маркета в формат Юлы</small>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">
                        <i class="fas fa-database text-success"></i>
                        <span id="stats-feeds-count">0</span> фидов | 
                        <span id="stats-items-count">0</span> товаров
                    </p>
                    <small class="text-muted">Обновлено: <span id="last-update-time"><?php echo date('H:i:s'); ?></span></small>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Модальные окна -->
    <div class="modal fade" id="feedDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Детали фида</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="feedDetailsContent">
                    Загрузка...
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmMessage">
                    Вы уверены?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmButton">Подтвердить</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Скрипты -->
    <script src="ui/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="ui/js/main.js"></script>
</body>
</html>