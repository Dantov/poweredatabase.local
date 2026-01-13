<div class="container-fluid">
    <div class="navbar-header">
        <button type="button" id="sidebarCollapse" class="btn btn-info navbar-btn">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="user-bar top-icons-agileits-w3layouts text-center mt-0 navBar670Plus">
            <li class="nav-item dropdown mr-3" data-toggle="tooltip" data-placement="left" title="Список коллекций">
                <a class="nav-link" href="#" role="button" data-toggle="modal" data-target="#collectionsModal">
                    <i class="fas fa-gem"></i>
                </a>
            </li>
        </ul>
        <!-- User-bar -->
        <ul class="user-bar top-icons-agileits-w3layouts text-right mt-0 navBar670Plus">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" style="padding: .5rem 0rem;" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="false">
                    <div class="profile-l">
                        <img src="images/defaultUser2.png" class="img-fluid" alt="Responsive image">
                    </div>
                </a>
                <div class="dropdown-menu drop-3">
                    <a href="#" class="dropdown-item mt-2">
                        <h4><i class="far fa-user mr-3"></i>Профиль</h4>
                    </a>
                    <a href="#" class="dropdown-item mt-2">
                        <h4><i class="fas fa-tools mr-3"></i></i>Настройки</h4>
                    </a>
                    <a href="#" class="dropdown-item mt-2">
                        <h4><i class="fas fa-chart-pie mr-3"></i>Статистика</h4>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="login.html">Выход</a>
                </div>
            </li>
        </ul>
        <!--// User-bar -->
    </div>
    <div class="clearfix"></div>
    <!-- Search-from -->
    <form action="#" method="post" class="form-inline mx-auto search-form" id="search-form">
        <input class="form-control mr-sm-2" type="search" placeholder="Поиск" aria-label="Search" required="">
        <button class="btn btn-style my-2 my-sm-0" type="submit"><i class="fas fa-search"></i></button>
    </form>
    <!--// Search-from -->

    <!-- User-bar -->
    <ul class="user-bar top-icons-agileits-w3layouts float-right" id="user-bar">
        <li class="nav-item dropdown mr-3" data-toggle="tooltip" data-placement="left" title="Список коллекций">
            <a class="nav-link" href="#" role="button" data-toggle="modal" data-target="#collectionsModal">
                <i class="fas fa-gem"></i>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" style="padding: .5rem 0rem;" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
                <span class="mr-1">Манюк Павел</span>
                <div class="profile-l">
                    <img src="images/defaultUser2.png" class="img-fluid" alt="Responsive image">
                </div>
            </a>
            <div class="dropdown-menu drop-3">
                <a href="#" class="dropdown-item mt-2">
                    <h4><i class="far fa-user mr-3"></i>Профиль</h4>
                </a>
                <a href="#" class="dropdown-item mt-2">
                    <h4><i class="fas fa-tools mr-3"></i></i>Настройки</h4>
                </a>
                <a href="#" class="dropdown-item mt-2">
                    <h4><i class="fas fa-chart-pie mr-3"></i>Статистика</h4>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="login.html">Выход</a>
            </div>
        </li>
    </ul>
    <!--// User-bar -->

    <!-- Media < 670px -->
    <div class="row navBar670Plus">
        <div class="col">
            <!-- Search-from -->
            <form action="#" method="post" class="form-inline mx-auto search-form">
                <input class="form-control mr-sm-2" type="search" placeholder="Поиск" aria-label="Search" required="">
                <button class="btn btn-style my-2 my-sm-0" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <!--// Search-from -->
        </div>
    </div>
</div>

<ul class="dropdown-menu dropdown-menu-right">
    <li role="presentation"><a href="controllers/setSort.php?regStat=none">Нет</a></li>
    <li role="presentation" class="divider"></li>
    <li><a href="controllers/setSort.php?regStat=onVerifi" title="Находится в очереди на проверку">На проверке</a></li><li><a href="controllers/setSort.php?regStat=Signed" title="Подписано технологом, лежит в очереди на поддержки">Проверено</a></li><li><a href="controllers/setSort.php?regStat=Support" title="На изделие поставлены поддержки, лежит в очереди на рост">Поддержки</a></li><li><a href="controllers/setSort.php?regStat=onPrint" title="Сейчас растится на 3D принтере">В росте</a></li><li><a href="controllers/setSort.php?regStat=Printed" title="Изделие выращено на 3D принтере">Выращено</a></li><li><a href="controllers/setSort.php?regStat=MMdone" title="Готовая мастер модель">Готовая ММ</a></li><li><a href="controllers/setSort.php?regStat=signalDone" title="Вышла готовая продукция!">Вышел сигнал!</a></li><li><a href="controllers/setSort.php?regStat=wip" title="Находится в работе у 3D модельера">В работе 3D</a></li><li><a href="controllers/setSort.php?regStat=wipM" title="Находится в работе на участках: доработки мм, монтировки, закрепки, полировки и т.д.">В работе (Монт.)</a></li><li><a href="controllers/setSort.php?regStat=onRepaire" title="Сейчас находится в ремонте у 3D модельера">В ремонте</a></li><li><a href="controllers/setSort.php?regStat=defer" title="Модель исключена из серии по некоторым причинам.">Отложено</a></li>							</ul>