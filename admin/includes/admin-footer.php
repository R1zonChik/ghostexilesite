</div><!-- .admin-main -->
    </div><!-- .admin-wrapper -->
    
    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
    <script>
        // Инициализация TinyMCE
        if (document.querySelector('.wysiwyg-editor')) {
            tinymce.init({
                selector: '.wysiwyg-editor',
                height: 500,
                menubar: true,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
            });
        }
        
        // Выпадающее меню пользователя
        const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
        const userDropdownMenu = document.querySelector('.user-dropdown-menu');
        
        if (userDropdownToggle && userDropdownMenu) {
            userDropdownToggle.addEventListener('click', function() {
                userDropdownMenu.classList.toggle('show');
            });
            
            // Закрытие меню при клике вне его
            document.addEventListener('click', function(event) {
                if (!userDropdownToggle.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    userDropdownMenu.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html>