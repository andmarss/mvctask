<nav class="navbar navbar-default navbar-static-top">
    <div class="container"><div class="navbar-header">
            <button type="button" data-toggle="collapse" data-target="#app-navbar-collapse" class="navbar-toggle collapsed">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="<?=route('index');?>" class="navbar-brand">
                Задачник
            </a>
        </div>
        <div id="app-navbar-collapse" class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <?php if(!\App\Auth::check()): ?>
                    <li>
                        <a href="<?=route('login-index');?>">Авторизироваться</a>
                    </li>
                    <li>
                        <a href="<?=route('register-index');?>">Зарегестрироваться</a>
                    </li>
                <?php else: ?>
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle">
                            <?=\App\Auth::user()->name ? \App\Auth::user()->name : 'Пользователь';?>
                            <span class="caret"></span>
                        </a>
                        <ul role="menu" class="dropdown-menu">
                            <li>
                                <a href="<?=route('personal-area', ['id' => \App\Auth::id()]);?>">Личный кабинет</a>
                                <a href="<?=domain();?>/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Выйти
                                </a>
                                <form id="logout-form" action="<?=route('logout');?>" method="POST">
                                    <?=csrf_field();?>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>