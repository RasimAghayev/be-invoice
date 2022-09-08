
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item navbar-brand-mini-wrapper">
            <a class="nav-link navbar-brand brand-logo-mini" href="/">
                <img src="{{ asset('https://autolux.az/header/logo.svg') }}" alt="logo" class="logo-light" style="width: 50px;"/>
            </a>
        </li>
{{--        <li class="nav-item nav-profile">--}}
{{--            <a href="#" class="nav-link">--}}
{{--                <div class="profile-image">--}}
{{--                    <img class="img-xs rounded-circle" src="assets/images/faces/face8.jpg" alt="profile image">--}}
{{--                    <div class="dot-indicator bg-success"></div>--}}
{{--                </div>--}}
{{--                <div class="text-wrapper">--}}
{{--                    <p class="profile-name">Henry Klein</p>--}}
{{--                    <p class="designation">Administrator</p>--}}
{{--                </div>--}}
{{--                <div class="icon-container">--}}
{{--                    <i class="icon-bubbles"></i>--}}
{{--                    <div class="dot-indicator bg-danger"></div>--}}
{{--                </div>--}}
{{--            </a>--}}
{{--        </li>--}}
        <li class="nav-item active">
            <a class="nav-link" data-bs-toggle="collapse" href="#customers" aria-expanded="false" aria-controls="e-commerce">
                <span class="menu-title">Müştəri</span> <i class="icon-people menu-icon"></i>
            </a>
            <div class="collapse" id="customers">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="/customer"> Siyahı </a></li>
                    <li class="nav-item"> <a class="nav-link" href="/pricing-table">Yarat</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#invoice" aria-expanded="false" aria-controls="e-commerce">
                <span class="menu-title">Qaimə</span> <i class="icon-briefcase menu-icon"></i>
            </a>
            <div class="collapse" id="invoice">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="/invoice"> Siyahı </a></li>
                    <li class="nav-item"> <a class="nav-link" href="/pricing-table">Yarat</a></li>
                </ul>
            </div>
        </li>
    </ul>
</nav>
