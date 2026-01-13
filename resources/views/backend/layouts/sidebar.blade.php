<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">{{ Auth::user()->name }}</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('admin.dashboard') }}">{{ substr(Auth::user()->name, 0, 2) }}</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>

            @canany(['Manage Categories', 'Manage Products', 'Manage Orders'])
                <li class="menu-header">E-Commerce</li>
            @endcanany

            @can('Manage Categories')
                <li
                    class="dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*']) }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-list"></i>
                        <span>Manage Categories</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ setActive(['admin.category.*']) }}"><a class="nav-link"
                                href="{{ route('admin.category.index') }}"><i class="fas fa-folder"></i> Category </a></li>
                        <li class="{{ setActive(['admin.sub-category.*']) }}"><a class="nav-link"
                                href="{{ route('admin.sub-category.index') }}"><i class="fas fa-folder-open"></i> Sub Category </a></li>
                        <li class="{{ setActive(['admin.child-category.*']) }}"><a class="nav-link"
                                href="{{ route('admin.child-category.index') }}"><i class="fas fa-level-down-alt"></i> Child Category </a></li>
                    </ul>
                </li>
            @endcan

            @canany(['Manage Products', 'View Product Stock'])
             <li class="menu-header">Products</li>
                <li class="dropdown {{ setActive(['admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*']) }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-box"></i>
                        <span>Manage Products</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ setActive(['admin.products.*']) }}"><a class="nav-link" href="{{ route('admin.products.index') }}"><i class="fas fa-boxes"></i> Products</a></li>
                        @role('Admin')
                        <li class="{{ setActive(['admin.units.*']) }}"><a class="nav-link" href="{{ route('admin.units.index') }}"><i class="fas fa-balance-scale"></i> Units</a></li>
                        <li class="{{ setActive(['admin.colors.*']) }}"><a class="nav-link" href="{{ route('admin.colors.index') }}"><i class="fas fa-palette"></i> Colors</a></li>
                        <li class="{{ setActive(['admin.sizes.*']) }}"><a class="nav-link" href="{{ route('admin.sizes.index') }}"><i class="fas fa-ruler"></i> Sizes</a></li>
                        @endrole
                    </ul>
                </li>
            @endcanany

            @role('Admin')
             <li class="menu-header">Bookings</li>
             <li class="dropdown {{ setActive(['admin.bookings.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-book"></i>
                    <span>Manage Bookings</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.bookings.*']) }}"><a class="nav-link" href="{{ route('admin.bookings.index') }}"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                </ul>
            </li>

            <li class="menu-header">Purchases</li>
            <li class="dropdown {{ setActive(['admin.purchases.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-shopping-cart"></i>
                    <span>Manage Purchases</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.purchases.index']) }}"><a class="nav-link" href="{{ route('admin.purchases.index') }}"><i class="fas fa-receipt"></i> All Purchases</a></li>
                    <li class="{{ setActive(['admin.purchases.create']) }}"><a class="nav-link" href="{{ route('admin.purchases.create') }}"><i class="fas fa-plus"></i> Create New</a></li>
                </ul>
            </li>
            @endrole

            <li class="menu-header">Product Requests</li>
            <li class="dropdown {{ setActive(['admin.product-requests.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-box-open"></i>
                    <span>Product Requests</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.product-requests.index']) }}"><a class="nav-link" href="{{ route('admin.product-requests.index') }}"><i class="fas fa-clipboard-list"></i> All Requests</a></li>
                    @if(auth()->user()->hasRole('Outlet User'))
                    <li class="{{ setActive(['admin.product-requests.create']) }}"><a class="nav-link" href="{{ route('admin.product-requests.create') }}"><i class="fas fa-plus"></i> Create New</a></li>
                    @endif
                </ul>
            </li>

            @role('Admin')
            <li class="menu-header">Sales</li>
            <li class="dropdown {{ setActive(['admin.sales.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-money-bill-wave"></i>
                    <span>Manage Sales</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.sales.index']) }}"><a class="nav-link" href="{{ route('admin.sales.index') }}"><i class="fas fa-list"></i> All Sales</a></li>
                    <li class="{{ setActive(['admin.sales.create']) }}"><a class="nav-link" href="{{ route('admin.sales.create') }}"><i class="fas fa-plus-circle"></i> Create New</a></li>
                </ul>
            </li>
            @endrole

            @role('Admin')
            <li class="menu-header">Reports</li>
            <li class="dropdown {{ setActive(['admin.reports.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-chart-line"></i>
                    <span>Reports</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.reports.index']) }}"><a class="nav-link" href="{{ route('admin.reports.index') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="{{ setActive(['admin.reports.stock']) }}"><a class="nav-link" href="{{ route('admin.reports.stock') }}"><i class="fas fa-info-circle"></i> Stock Report</a></li>
                    <li class="{{ setActive(['admin.reports.purchase']) }}"><a class="nav-link" href="{{ route('admin.reports.purchase') }}"><i class="fas fa-history"></i> Purchase History</a></li>
                    <li class="{{ setActive(['admin.reports.product-purchase-history']) }}"><a class="nav-link" href="{{ route('admin.reports.product-purchase-history') }}"><i class="fas fa-map-marker-alt"></i> Product Tracking</a></li>
                    <li class="{{ setActive(['admin.reports.low-stock']) }}"><a class="nav-link" href="{{ route('admin.reports.low-stock') }}"><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</a></li>
                    <li class="{{ setActive(['admin.reports.profit-loss']) }}"><a class="nav-link" href="{{ route('admin.reports.profit-loss') }}"><i class="fas fa-chart-bar"></i> Profit & Loss</a></li>
                </ul>
            </li>
            @endrole

            @role('Admin')
            <li class="menu-header">Brands & Vendors</li>
            <li class="dropdown {{ setActive(['admin.brand.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-tag"></i> <span>Brands</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.brand.index']) }}"><a class="nav-link" href="{{ route('admin.brand.index') }}">All Brands</a></li>
                    <li class="{{ setActive(['admin.brand.create']) }}"><a class="nav-link" href="{{ route('admin.brand.create') }}">Add Brand</a></li>
                </ul>
            </li>

            @can('Manage Vendors')
            <li class="dropdown {{ setActive(['admin.vendor.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-truck"></i> <span>Vendors</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.vendor.index']) }}"><a class="nav-link" href="{{ route('admin.vendor.index') }}">All Vendors</a></li>
                    <li class="{{ setActive(['admin.vendor.create']) }}"><a class="nav-link" href="{{ route('admin.vendor.create') }}">Add Vendor</a></li>
                </ul>
            </li>
            @endcan
            @endrole

            @can('Administration')
            <li class="menu-header">System</li>
            <li class="dropdown {{ setActive(['admin.permission.*', 'admin.role.*', 'admin.users.*', 'admin.settings.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-cogs"></i> <span>Administration</span></a>
                <ul class="dropdown-menu">
                     <li class="{{ setActive(['admin.users.*']) }}"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                     <li class="{{ setActive(['admin.permission.*']) }}"><a class="nav-link" href="{{ route('admin.permission.index') }}">Permissions</a></li>
                     <li class="{{ setActive(['admin.role.*']) }}"><a class="nav-link" href="{{ route('admin.role.index') }}">Roles</a></li>
                     <li class="{{ setActive(['admin.settings.*']) }}"><a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a></li>
                </ul>
            </li>
            @endcan


        </ul>
    </aside>
</div>
