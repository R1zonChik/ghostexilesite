document.addEventListener('DOMContentLoaded', function() {
    // Инициализация WYSIWYG-редактора
    if (document.querySelector('.wysiwyg-editor')) {
        initWysiwygEditor();
    }
    
    // Обработчик для выпадающего меню пользователя
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
    
    // Подтверждение удаления
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите удалить этот элемент?')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Предпросмотр изображения
    const imageInput = document.getElementById('featured_image');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.innerHTML = `<img src="${this.result}" alt="Предпросмотр">`;
                    imagePreview.style.display = 'block';
                });
                
                reader.readAsDataURL(file);
            }
        });
    }
});

// Инициализация WYSIWYG-редактора
function initWysiwygEditor() {
    // Подключаем TinyMCE
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