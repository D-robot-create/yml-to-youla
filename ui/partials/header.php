<?php
// Получаем CSRF токен для JavaScript
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YML to Youla Converter</title>
    
    <!-- Bootstrap CSS -->
    <link href="ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="ui/css/style.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <script>
        // Глобальные переменные для JS
        window.appConfig = {
            baseUrl: '<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>',
            csrfToken: '<?php echo $csrfToken; ?>',
            debugMode: <?php echo DEBUG_MODE ? 'true' : 'false'; ?>
        };
    </script>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="./">
                <i class="fas fa-exchange-alt me-2"></i>
                YML → Youla Converter
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') == 'dashboard' ? 'active' : ''; ?>" href="./">
                            <i class="fas fa-tachometer-alt me-1"></i> Дашборд
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') == 'feeds' ? 'active' : ''; ?>" href="./?page=feeds">
                            <i class="fas fa-rss me-1"></i> Фиды
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') == 'create' ? 'active' : ''; ?>" href="./?page=create">
                            <i class="fas fa-plus-circle me-1"></i> Новый фид
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') == 'logs' ? 'active' : ''; ?>" href="./?page=logs">
                            <i class="fas fa-clipboard-list me-1"></i> Логи
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') == 'settings' ? 'active' : ''; ?>" href="./?page=settings">
                            <i class="fas fa-cog me-1"></i> Настройки
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    <button class="btn btn-outline-light me-2" onclick="runCron()">
                        <i class="fas fa-sync-alt"></i> Обновить все
                    </button>
                    <span class="navbar-text text-light">
                        <i class="fas fa-clock"></i> <?php echo date('H:i'); ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <!-- Уведомления -->
        <div id="notification-area" style="position: fixed; top: 80px; right: 20px; z-index: 9999; width: 350px;"></div>