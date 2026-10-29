<template>
    <footer class="site-footer">
        <div class="container footer-main">
            <div>
                <RouterLink
                    to="/"
                    class="logo"
                >
                    RESPAWN
                    <span class="logo-dot">®</span>
                </RouterLink>
                <p>
                    Новое железо.
                    <br />
                    Новый уровень игры.
                </p>
            </div>
            <div>
                <h3>Экипировка</h3>
                <RouterLink to="/catalog?category=keyboards">Клавиатуры</RouterLink>
                <RouterLink to="/catalog?category=mice">Мыши</RouterLink>
                <RouterLink to="/catalog?category=chairs">Кресла</RouterLink>
                <RouterLink to="/catalog?category=computers">Компьютеры</RouterLink>
            </div>
            <div>
                <h3>Твой RESPAWN</h3>
                <RouterLink to="/account">Личный кабинет</RouterLink>
                <RouterLink to="/favorites">Избранное</RouterLink>
                <RouterLink to="/cart">Корзина</RouterLink>
                <RouterLink to="/#about">О проекте</RouterLink>
            </div>
            <div class="footer-note">
                <span class="tag">ПОРТФОЛИО-ПРОЕКТ</span>
                <p>
                    Демонстрационный магазин.
                    <br />
                    Товары, оплата и доставка учебные.
                    <br />
                    Реальные деньги не списываются.
                </p>
                <span class="muted">PHP / LARAVEL / VUE</span>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>© {{ new Date().getFullYear() }} RESPAWN</span>
            <span>BUILD YOUR NEXT LEVEL.</span>
            <a href="#top">Наверх ↑</a>
        </div>
    </footer>
</template>
