<?php
require_once 'includes/init.php';
$pageTitle = 'Контакты';
$pageDescription = 'Свяжитесь с разработчиками Ghost Exile для вопросов и предложений';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <section class="main-content">
                <h1 class="page-title">Контакты</h1>
                
                <div class="contact-info">
                    <p class="lead">
                        Добро пожаловать на страницу контактов Ghost Exile Guide! Если у вас возникли вопросы по игре Ghost Exile, предложения по улучшению сайта или вы обнаружили ошибку в материалах, вы можете связаться с нами одним из удобных способов.
                    </p>
                    
                    <p class="lead">
                        Наша команда всегда открыта для общения и готова помочь вам с любыми вопросами, связанными с игрой или содержанием сайта. Выберите наиболее удобный для вас способ связи из представленных ниже.
                    </p>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <h3><i class="fab fa-vk"></i> ВКонтакте</h3>
                            <p>Если вам надо обратиться к создателям гайда, задать вопрос по игре или сообщить об ошибке на сайте, вы можете написать нам в официальный чат группы ВКонтакте:</p>
                            <a href="https://vk.com/im/convo/-226926048?entrypoint=profile_page" target="_blank" class="btn btn-vk">
                                <i class="fab fa-vk"></i> Написать в чат группы
                            </a>
                            <p class="contact-note-small">В чате группы вам ответят администраторы и модераторы сообщества. Это самый быстрый способ получить ответ на общие вопросы по игре.</p>
                        </div>
                        
                        <div class="contact-method">
                            <h3><i class="fab fa-discord"></i> Discord</h3>
                            <p>Для прямой связи с разработчиками игры вы можете написать им в Discord. Выберите разработчика в зависимости от вашего вопроса:</p>
                            
                            <div class="developers">
                                <div class="developer-card">
                                    <div class="developer-avatar">
                                        <img src="https://cdn.discordapp.com/embed/avatars/0.png" alt="LostOne" class="avatar">
                                        <span class="status-badge"></span>
                                    </div>
                                    <div class="developer-info">
                                        <h4>LostOne</h4>
                                        <p class="developer-role">
                                            <span class="role-badge lead-dev">Lead-Developer</span>
                                            <span class="role-badge game-designer">Game Designer</span>
                                            <span class="role-badge audio-designer">Audio Designer</span>
                                            <span class="role-badge programmer">Programmer</span>
                                        </p>
                                        <p class="developer-desc">
                                            Основатель Ghost Exile и LostOneTeam. Отвечает за основную разработку игры, дизайн игровых механик и аудио-составляющую проекта.
                                        </p>
                                        <p class="developer-note">
                                            По вопросам от контент-создателей и блогеров пишите RamirezZ
                                        </p>
                                        <p class="discord-tag">Discord: <code>lostone_g</code></p>
                                    </div>
                                </div>
                                
                                <div class="developer-card">
                                    <div class="developer-avatar">
                                        <img src="https://cdn.discordapp.com/embed/avatars/1.png" alt="RamirezZ" class="avatar">
                                        <span class="status-badge"></span>
                                    </div>
                                    <div class="developer-info">
                                        <h4>RamirezZ</h4>
                                        <p class="developer-role">
                                            <span class="role-badge lead-dev">Lead-Developer</span>
                                            <span class="role-badge level-designer">Level Designer</span>
                                            <span class="role-badge community">Community moderator</span>
                                        </p>
                                        <p class="developer-desc">
                                            Отвечает за дизайн уровней, взаимодействие с сообществом и модерацию контента. Координирует работу с контент-создателями и блогерами.
                                         и блогерами.
                                        </p>
                                        <p class="developer-note">
                                            Если у вас есть вопросы по игре, пишите мне лично
                                        </p>
                                        <p class="discord-tag">Discord: <code>ramriezz_ge</code></p>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="contact-note-small">
                                При обращении в Discord, пожалуйста, представьтесь и четко сформулируйте ваш вопрос. Это поможет разработчикам быстрее вам ответить.
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-note">
                        <p><i class="fas fa-info-circle"></i> <strong>Важно:</strong> Форма обратной связи на сайте больше не используется. Пожалуйста, используйте указанные выше способы для связи с разработчиками. Мы стараемся отвечать на все сообщения в течение 24-48 часов.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
body {
    background-color: #36393f;
    color: #dcddde;
}

.main-content {
    background-color: #2f3136;
    border-radius: 8px;
    padding: 30px;
    margin: 30px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.page-title {
    color: #fff;
    margin-bottom: 25px;
    border-bottom: 1px solid #40444b;
    padding-bottom: 15px;
    font-size: 28px;
}

.lead {
    color: #b9bbbe;
    line-height: 1.6;
    font-size: 16px;
    margin-bottom: 20px;
}

.contact-info {
    margin: 20px 0;
}

.contact-methods {
    display: flex;
    flex-direction: column;
    gap: 30px;
    margin: 30px 0;
}

.contact-method {
    background-color: #36393f;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.contact-method h3 {
    margin-top: 0;
    color: #fff;
    border-bottom: 2px solid #40444b;
    padding-bottom: 10px;
    font-size: 22px;
}

.contact-method p {
    color: #b9bbbe;
    line-height: 1.6;
    margin-bottom: 15px;
}

.btn-vk {
    background-color: #4a76a8;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
    font-size: 16px;
    margin-bottom: 15px;
}

.btn-vk:hover {
    background-color: #3d6898;
    color: #fff;
    text-decoration: none;
}

.contact-note-small {
    font-size: 14px;
    color: #72767d;
    margin-top: 15px;
    font-style: italic;
}

.developers {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px;
}

.developer-card {
    display: flex;
    background-color: #202225;
    border-radius: 8px;
    padding: 20px;
    color: #fff;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.developer-avatar {
    position: relative;
    margin-right: 20px;
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    background-color: #36393f;
}

.status-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 16px;
    height: 16px;
    background-color: #43b581;
    border-radius: 50%;
    border: 3px solid #202225;
}

.developer-info {
    flex: 1;
}

.developer-info h4 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 20px;
    color: #fff;
}

.developer-role {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 12px;
}

.role-badge {
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
}

.lead-dev {
    background-color: #9b59b6;
}

.game-designer {
    background-color: #1abc9c;
}

.audio-designer {
    background-color: #3498db;
}

.programmer {
    background-color: #f1c40f;
}

.level-designer {
    background-color: #2980b9;
}

.community {
    background-color: #f39c12;
}

.developer-desc {
    font-size: 14px;
    color: #b9bbbe;
    margin-bottom: 10px;
    line-height: 1.5;
}

.developer-note {
    font-size: 14px;
    color: #72767d;
    margin-bottom: 10px;
    font-style: italic;
}

.discord-tag {
    font-size: 14px;
    color: #b9bbbe;
    margin-bottom: 0;
}

.discord-tag code {
    background-color: #2f3136;
    padding: 3px 8px;
    border-radius: 4px;
    font-family: 'Consolas', monospace;
}

.contact-note {
    margin-top: 30px;
    padding: 15px;
    background-color: rgba(237, 66, 69, 0.1);
    border-left: 4px solid #ed4245;
    color: #ed4245;
    border-radius: 4px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .developer-card {
        flex-direction: column;
    }
    
    .developer-avatar {
        margin-right: 0;
        margin-bottom: 15px;
        align-self: center;
    }
    
    .main-content {
        padding: 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>