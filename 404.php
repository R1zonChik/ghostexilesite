<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Страница не найдена — Ghost Exile Guide</title>
    <link rel="icon" href="https://ghost-exile.site/img/ico_logo.ico" type="image/x-icon" sizes="32x32">
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }
        
        body {
            background: url('/assets/images/error404.gif') center center no-repeat;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #ffffff;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        
        .error-container {
            text-align: center;
            padding: 40px;
            background-color: rgba(18, 18, 18, 0.8);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            backdrop-filter: blur(5px);
            border: 1px solid #333;
            position: relative;
            z-index: 2;
        }
        
        h1 {
            font-size: 5rem;
            color: #bb86fc;
            margin: 0;
            text-shadow: 0 0 10px rgba(187, 134, 252, 0.5);
            animation: pulse 2s infinite;
        }
        
        h2 {
            margin-bottom: 30px;
            font-size: 2rem;
            color: #e040fb;
        }
        
        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        a {
            color: #bb86fc;
            text-decoration: none;
            transition: color 0.3s;
            font-weight: bold;
        }
        
        a:hover {
            color: #e040fb;
            text-decoration: underline;
        }
        
        .ghost-message {
            margin-top: 30px;
            color: #e040fb;
            font-size: 1.2rem;
            font-style: italic;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .home-btn {
            display: inline-block;
            background-color: #bb86fc;
            color: #121212;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .home-btn:hover {
            background-color: #e040fb;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(224, 64, 251, 0.4);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>Страница не найдена</h2>
        <p>Запрошенная страница отсутствует или была перемещена.<br>
        Возможно, призраки украли контент или вы перешли по устаревшей ссылке.</p>
        
        <a href="/" class="home-btn">Вернуться на главную</a>
        
        <p class="ghost-message">👻 Призраки украли контент!</p>
    </div>
    
    <!-- Скрытый аудио элемент -->
    <audio id="background-sound" loop preload="auto">
        <source src="/assets/audio/redsound404.mp3" type="audio/mp3">
    </audio>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const audio = document.getElementById('background-sound');
        let audioStarted = false;
        
        // Установка громкости на 0.7 (70%)
        audio.volume = 0.99;
        
        // Функция для попытки воспроизведения звука
        function tryPlayAudio() {
            if (audioStarted) return;
            
            audio.play().then(() => {
                audioStarted = true;
            }).catch(error => {
                // Если браузер блокирует автовоспроизведение, просто игнорируем ошибку
                console.log('Автовоспроизведение заблокировано браузером:', error);
            });
        }
        
        // Запуск звука при первом движении мыши
        window.addEventListener('mousemove', tryPlayAudio, { once: true });
        window.addEventListener('touchstart', tryPlayAudio, { once: true });
        window.addEventListener('scroll', tryPlayAudio, { once: true });
        
        // Обработка окончания аудио (хотя с loop это не должно происходить)
        audio.addEventListener('ended', function() {
            if (document.visibilityState === 'visible') {
                audio.play().catch(error => {
                    console.log('Не удалось перезапустить звук:', error);
                });
            }
        });
        
        // Обработка видимости страницы
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible' && audioStarted && audio.paused) {
                audio.play().catch(error => {
                    console.log('Не удалось возобновить звук после возвращения на страницу:', error);
                });
            }
        });
    });
    </script>
</body>
</html>