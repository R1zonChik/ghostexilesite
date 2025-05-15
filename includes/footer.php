</div> <!-- Закрытие .content-wrapper -->
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-widgets">
                <div class="widget">
                    <h3>О Ghost Exile</h3>
                    <p>Ghost Exile — это захватывающая кооперативная хоррор-игра, где команда охотников за привидениями исследует различные локации, собирает улики и идентифицирует типы призраков, используя специальное оборудование.</p>
                    <p>Наш сайт предоставляет полные руководства, советы и стратегии для успешной охоты на призраков.</p>
                </div>
                
                <div class="widget">
                    <h3>Полезные ссылки</h3>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/about.php">Об игре</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/category.php?slug=ghosts">Все призраки</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/category.php?slug=equipment">Оборудование</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/category.php?slug=maps">Карты локаций</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php">Связаться с нами</a></li>
                    </ul>
                </div>
                
                <div class="widget">
                    <h3>Присоединяйтесь к нам</h3>
                    <p>Следите за обновлениями и общайтесь с другими охотниками за привидениями в наших социальных сетях.</p>
                    <div class="social-links">
                        <a href="https://vk.com/ghostexile_ru" target="_blank" class="social-link vk">
                            <i class="fab fa-vk"></i>
                        </a>
                        <a href="https://t.me/ghostexileofficial" target="_blank" class="social-link telegram">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <a href="https://discord.gg/K3TpZPccqH" target="_blank" class="social-link discord">
                            <i class="fab fa-discord"></i>
                        </a>
                        <a href="https://www.youtube.com/channel/UCU0_xL7WGL7QGaVzdDMfgaw" target="_blank" class="social-link youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Ghost Exile Guide. Все права защищены.</p>
                <p>Ghost Exile является зарегистрированной торговой маркой. Этот сайт не аффилирован с разработчиками игры и создан фанатами для фанатов.</p>
            </div>
        </div>
    </footer>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Скрипт для модального окна выбора языка -->
    <script>
    (function(){
        // Функции для работы с куками
        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
        }

        function getCookie(name) {
            const cookieName = name + "=";
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                let cookie = cookies[i].trim();
                if (cookie.indexOf(cookieName) === 0) {
                    return cookie.substring(cookieName.length, cookie.length);
                }
            }
            return null;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Проверяем, установлен ли язык
            const langCookie = getCookie('siteLang');
            
            // Если язык не установлен, показываем модальное окно
            if (!langCookie) {
                const modal = document.getElementById('lang-modal');
                const ruBtn = document.getElementById('lang-ru');
                const enBtn = document.getElementById('lang-en');
                const confirmBtn = document.getElementById('lang-confirm');
                const statusText = document.getElementById('lang-status');
                
                let selectedLang = null;
                let audioPlaying = false;
                
                // Создаем два аудио объекта для разных языков
                const audioRu = new Audio('assets/audio/ru-intro.mp3');
                const audioEn = new Audio('assets/audio/en-dev.mp3');
                
                // Показываем модальное окно
                modal.classList.add('active');
                
                // Обработчик для русского языка
                ruBtn.addEventListener('click', function() {
                    // Делаем русскую кнопку активной
                    ruBtn.classList.add('active');
                    enBtn.classList.remove('active');
                    
                    // Сбрасываем статус
                    statusText.textContent = 'Прослушайте аудиосообщение...';
                    
                    // Останавливаем предыдущее аудио, если оно играет
                    audioRu.pause();
                    audioRu.currentTime = 0;
                    audioEn.pause();
                    audioEn.currentTime = 0;
                    
                    // Блокируем кнопку подтверждения и русскую кнопку
                    confirmBtn.disabled = true;
                    ruBtn.disabled = true; // Блокируем только русскую кнопку
                    
                    // Устанавливаем выбранный язык
                    selectedLang = 'ru';
                    
                    // Воспроизводим аудио
                    audioRu.play();
                    audioPlaying = true;
                    
                    // Когда аудио закончится, разблокируем кнопки
                    audioRu.onended = function() {
                        confirmBtn.disabled = false;
                        ruBtn.disabled = false; // Разблокируем русскую кнопку
                        statusText.textContent = 'Нажмите "Подтвердить"';
                        audioPlaying = false;
                    };
                });
                
                // Обработчик для английского языка
                enBtn.addEventListener('click', function() {
                    // Делаем английскую кнопку активной
                    enBtn.classList.add('active');
                    ruBtn.classList.remove('active');
                    
                    // Сбрасываем статус
                    statusText.textContent = 'Please wait...';
                    
                    // Останавливаем предыдущее аудио, если оно играет
                    audioRu.pause();
                    audioRu.currentTime = 0;
                    audioEn.pause();
                    audioEn.currentTime = 0;
                    
                    // Блокируем кнопку подтверждения и английскую кнопку
                    confirmBtn.disabled = true;
                    enBtn.disabled = true; // Блокируем только английскую кнопку
                    
                    // Устанавливаем выбранный язык
                    selectedLang = 'en';
                    
                    // Воспроизводим аудио для английского языка
                    audioEn.play();
                    audioPlaying = true;
                    
                    // Когда аудио закончится, показываем сообщение "In development"
                    audioEn.onended = function() {
                        statusText.textContent = 'In development';
                        enBtn.disabled = false; // Разблокируем английскую кнопку
                        audioPlaying = false;
                        // Кнопка подтверждения остается заблокированной
                    };
                });
                
                // Обработчик для кнопки подтверждения
                confirmBtn.addEventListener('click', function() {
                    if (selectedLang === 'ru' && !audioPlaying) {
                        // Устанавливаем куки на 365 дней
                        setCookie('siteLang', 'ru', 365);
                        
                        // Перезагружаем страницу
                        window.location.reload();
                    }
                });
            }
        });
    })();
    </script>
</body>
</html>