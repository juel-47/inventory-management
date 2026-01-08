<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}">{{ Auth::user()->name }}</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('dashboard') }}">{{ substr(Auth::user()->name, 0, 2) }}</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="nav-link"><i class="fas fa-fire"></i><span>Dashboard</span></a>
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
                                href="{{ route('admin.category.index') }}">Category </a></li>
                        <li class="{{ setActive(['admin.sub-category.*']) }}"><a class="nav-link"
                                href="{{ route('admin.sub-category.index') }}">Sub Category </a></li>
                        <li class="{{ setActive(['admin.child-category.*']) }}"><a class="nav-link"
                                href="{{ route('admin.child-category.index') }}">Child Category </a></li>
                    </ul>
                </li>
            @endcan

            @can('Manage Brands')
                <li class="menu-header">Brands</li>
                <li class="dropdown {{ setActive(['admin.brand.*']) }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-list"></i>
                        <span>Manage Brands</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="{{ setActive(['admin.brand.index']) }}">
                            <a class="nav-link" href="{{ route('admin.brand.index') }}">
                                Brands
                            </a>
                        </li>
                        <li class="{{ setActive(['admin.brand.create']) }}">
                            <a class="nav-link" href="{{ route('admin.brand.create') }}">
                                Add Brand
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @can('Manage Vendors')
                <li class="menu-header">Vendors</li>
                <li class="dropdown {{ setActive(['admin.vendor.*']) }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-truck"></i>
                        <span>Manage Vendors</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="{{ setActive(['admin.vendor.index']) }}">
                            <a class="nav-link" href="{{ route('admin.vendor.index') }}">
                                Vendors
                            </a>
                        </li>
                        <li class="{{ setActive(['admin.vendor.create']) }}">
                            <a class="nav-link" href="{{ route('admin.vendor.create') }}">
                                Add Vendor
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan
            @can('Administration')
                <li class="menu-header">Authorization</li>
                <li class="{{ setActive(['admin.permission.*']) }}"><a class="nav-link"
                        href="{{ route('admin.permission.index') }}"><i
                            class="fab fa-accessible-icon"></i><span>Permission</span></a>
                </li>
                <li class="{{ setActive(['admin.role.*']) }}"><a class="nav-link"
                        href="{{ route('admin.role.index') }}"><i class="fas fa-user-shield"></i><span>Roles</span></a>
                </li>
                <li class="{{ setActive(['admin.users.*']) }}"><a class="nav-link"
                        href="{{ route('admin.users.index') }}"><i class="far fa-user"></i><span>Users</span></a>
                </li>
            @endcan


        </ul>
    </aside>
</div>
