document.addEventListener('DOMContentLoaded', function() {
    // Мобильное меню
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    const closeMenu = document.querySelector('.close-menu');
    
    if (mobileMenuToggle && mobileMenu && closeMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
        
        closeMenu.addEventListener('click', function() {
            mobileMenu.classList.remove('show');
            document.body.style.overflow = '';
        });
    }
    
    // Анимация при прокрутке
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    function checkScroll() {
        animateElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('animated');
            }
        });
    }
    
    // Проверяем при загрузке страницы
    checkScroll();
    
    // Проверяем при прокрутке
    window.addEventListener('scroll', checkScroll);
    
    // Плавная прокрутка для якорных ссылок
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId !== '#') {
                e.preventDefault();
                
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Закрываем мобильное меню, если оно открыто
                    if (mobileMenu && mobileMenu.classList.contains('show')) {
                        mobileMenu.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                }
            }
        });
    });
});