<style>
    /* Fix for Top Nav Layout: Icon above Text */
    .navbar.main-navbar .navbar-nav {
        flex-direction: row;
        align-items: center; /* Center items vertically in the list */
        display: flex;
        height: 100%; /* Take full height of navbar */
    }
    .navbar.main-navbar .collapse.navbar-collapse {
        align-items: center !important;
        display: flex !important;
    }
    
    /* Ensure right side aligned too */
    .navbar.main-navbar .navbar-right {
        align-items: center;
        display: flex;
        height: 100%;
    }
    .navbar.main-navbar .navbar-nav .nav-item {
        height: 100%;
        display: flex;
        align-items: center;
    }
    .navbar.main-navbar .navbar-nav .nav-item .nav-link {
        display: flex;
        flex-direction: column;
        justify-content: center; /* Center content vertically within link */
        align-items: center;
        text-align: center;
        height: 100%; /* Full height */
        padding: 0 20px; /* Horizontal padding only, height handled by flex */
        color: #fff !important; /* Default white text */
        font-weight: 600;
        transition: all 0.3s;
        border-radius: 5px;
        min-height: 70px; /* Minimum height to ensure clickability if navbar grows */
    }
    
    /* Active & Open States: White Background, Dark Text */
    .navbar.main-navbar .navbar-nav .nav-item.active .nav-link,
    .navbar.main-navbar .navbar-nav .nav-item.show .nav-link,
    .navbar.main-navbar .navbar-nav .nav-item .nav-link:hover {
        background-color: #fff !important;
        color: #6777ef !important; /* Primary blue color for text */
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .navbar.main-navbar .navbar-nav .nav-item .nav-link i {
        margin-right: 0 !important;
        margin-bottom: 6px;
        font-size: 24px; /* Bigger Icon */
    }
    .navbar.main-navbar .navbar-nav .nav-item .nav-link span {
        font-size: 14px; /* Bigger Text */
        line-height: 1.2;
    }
    
    /* Ensure dropdowns don't conflict */
    .dropdown-menu {
        margin-top: 10px !important;
    }

    /* Mobile Responsive Tweaks */
    @media (max-width: 991.98px) {
        .navbar.main-navbar .navbar-nav {
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }
        .collapse.navbar-collapse {
            background: #6777ef; /* Match navbar color */
            padding: 10px;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .navbar.main-navbar .navbar-nav .nav-item {
            width: 100%;
        }
        .navbar.main-navbar .navbar-nav .nav-item .nav-link {
            flex-direction: row; /* Horizontal on mobile list */
            justify-content: flex-start;
            padding: 10px;
        }
        .navbar.main-navbar .navbar-nav .nav-item .nav-link i {
            margin-right: 15px !important;
            margin-bottom: 0;
            font-size: 20px;
        }
        .navbar.main-navbar .navbar-nav .nav-item .nav-link span {
            font-size: 16px;
        }
        /* Hide Profile in Collapse if shown separately, or adjust */
    }
</style>
<nav class="navbar navbar-expand-lg main-navbar">
    <!-- Mobile Hamburger Toggle (Opens Sidebar) -->
    <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg d-lg-none text-white">
        <i class="fas fa-bars" style="font-size: 24px;"></i>
    </a>

    <!-- Top Nav Items: Visible ONLY on Desktop -->
    <ul class="navbar-nav mx-auto d-none d-lg-flex"> <!-- Hidden on mobile, Flex on desktop -->
            <!-- Dashboard -->
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>

            <!-- Categories -->
            @can('Manage Categories')
            <li class="nav-item dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-list"></i><span>Categories</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.category.*']) }}"><a class="nav-link" href="{{ route('admin.category.index') }}">Category</a></li>
                    <li class="{{ setActive(['admin.sub-category.*']) }}"><a class="nav-link" href="{{ route('admin.sub-category.index') }}">Sub Category</a></li>
                    <li class="{{ setActive(['admin.child-category.*']) }}"><a class="nav-link" href="{{ route('admin.child-category.index') }}">Child Category</a></li>
                </ul>
            </li>
            @endcan

            <!-- Products -->
            @canany(['Manage Products', 'View Product Stock'])
            <li class="nav-item dropdown {{ setActive(['admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-box"></i><span>Products</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.products.*']) }}"><a class="nav-link" href="{{ route('admin.products.index') }}">All Products</a></li>
                    @role('Admin')
                    <li class="{{ setActive(['admin.units.*']) }}"><a class="nav-link" href="{{ route('admin.units.index') }}">Units</a></li>
                    <li class="{{ setActive(['admin.colors.*']) }}"><a class="nav-link" href="{{ route('admin.colors.index') }}">Colors</a></li>
                    <li class="{{ setActive(['admin.sizes.*']) }}"><a class="nav-link" href="{{ route('admin.sizes.index') }}">Sizes</a></li>
                    @endrole
                </ul>
            </li>
            @endcanany

             <!-- Bookings -->
            @role('Admin')
            <li class="nav-item dropdown {{ setActive(['admin.bookings.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-book"></i><span>Bookings</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.bookings.*']) }}"><a class="nav-link" href="{{ route('admin.bookings.index') }}">All Bookings</a></li>
                </ul>
            </li>

            <!-- Purchases -->
            <li class="nav-item dropdown {{ setActive(['admin.purchases.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-shopping-cart"></i><span>Purchases</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.purchases.index']) }}"><a class="nav-link" href="{{ route('admin.purchases.index') }}">All Purchases</a></li>
                    <li class="{{ setActive(['admin.purchases.create']) }}"><a class="nav-link" href="{{ route('admin.purchases.create') }}">Create New</a></li>
                </ul>
            </li>
            @endrole
            
            <!-- Sales -->
            @role('Admin')
            <li class="nav-item dropdown {{ setActive(['admin.sales.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-money-bill-wave"></i><span>Sales</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.sales.index']) }}"><a class="nav-link" href="{{ route('admin.sales.index') }}">All Sales</a></li>
                    <li class="{{ setActive(['admin.sales.create']) }}"><a class="nav-link" href="{{ route('admin.sales.create') }}">Create New</a></li>
                </ul>
            </li>
            @endrole

             <!-- Product Requests -->
             <li class="nav-item dropdown {{ setActive(['admin.product-requests.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-box-open"></i><span>Requests</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.product-requests.index']) }}"><a class="nav-link" href="{{ route('admin.product-requests.index') }}">All Requests</a></li>
                    @if(auth()->user()->hasRole('Outlet User'))
                    <li class="{{ setActive(['admin.product-requests.create']) }}"><a class="nav-link" href="{{ route('admin.product-requests.create') }}">Create New</a></li>
                    @endif
                </ul>
            </li>

             <!-- Reports -->
             @role('Admin')
             <li class="nav-item dropdown {{ setActive(['admin.reports.*']) }}">
                 <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-chart-line"></i><span>Reports</span></a>
                 <ul class="dropdown-menu">
                     <li class="{{ setActive(['admin.reports.index']) }}"><a class="nav-link" href="{{ route('admin.reports.index') }}">Dashboard</a></li>
                     <li class="{{ setActive(['admin.reports.stock']) }}"><a class="nav-link" href="{{ route('admin.reports.stock') }}">Stock Report</a></li>
                     <li class="{{ setActive(['admin.reports.purchase']) }}"><a class="nav-link" href="{{ route('admin.reports.purchase') }}">Purchase History</a></li>
                     <li class="{{ setActive(['admin.reports.product-purchase-history']) }}"><a class="nav-link" href="{{ route('admin.reports.product-purchase-history') }}">Product Tracking</a></li>
                     <li class="{{ setActive(['admin.reports.low-stock']) }}"><a class="nav-link" href="{{ route('admin.reports.low-stock') }}">Low Stock Alert</a></li>
                     <li class="{{ setActive(['admin.reports.profit-loss']) }}"><a class="nav-link" href="{{ route('admin.reports.profit-loss') }}">Profit & Loss</a></li>
                 </ul>
             </li>
             @endrole

            <!-- More (Brands, Vendors, Settings) -->
        <!-- Brands -->
        @role('Admin')
        <li class="nav-item dropdown {{ setActive(['admin.brand.*']) }}">
            <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-tag"></i><span>Brands</span></a>
            <ul class="dropdown-menu">
                <li class="{{ setActive(['admin.brand.index']) }}"><a class="nav-link" href="{{ route('admin.brand.index') }}">All Brands</a></li>
                <li class="{{ setActive(['admin.brand.create']) }}"><a class="nav-link" href="{{ route('admin.brand.create') }}">Add Brand</a></li>
            </ul>
        </li>
        @endrole

        <!-- Vendors -->
        @can('Manage Vendors')
        <li class="nav-item dropdown {{ setActive(['admin.vendor.*']) }}">
            <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-truck"></i><span>Vendors</span></a>
            <ul class="dropdown-menu">
                <li class="{{ setActive(['admin.vendor.index']) }}"><a class="nav-link" href="{{ route('admin.vendor.index') }}">All Vendors</a></li>
                <li class="{{ setActive(['admin.vendor.create']) }}"><a class="nav-link" href="{{ route('admin.vendor.create') }}">Add Vendor</a></li>
            </ul>
        </li>
        @endcan

        <!-- System -->
        @can('Administration')
         <li class="nav-item dropdown {{ setActive(['admin.permission.*', 'admin.role.*', 'admin.users.*', 'admin.settings.*']) }}">
            <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-cogs"></i><span>System</span></a>
            <ul class="dropdown-menu">
                <li class="{{ setActive(['admin.users.*']) }}"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="{{ setActive(['admin.permission.*']) }}"><a class="nav-link" href="{{ route('admin.permission.index') }}">Permissions</a></li>
                <li class="{{ setActive(['admin.role.*']) }}"><a class="nav-link" href="{{ route('admin.role.index') }}">Roles</a></li>
                <li class="{{ setActive(['admin.settings.*']) }}"><a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a></li>
            </ul>
        </li>
        @endcan
        </ul>

    <ul class="navbar-nav navbar-right">
      <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
        <img alt="image" height="30px" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}" class="rounded-circle mr-1">
        <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->name }} </div></a>
        <div class="dropdown-menu dropdown-menu-right">
          {{-- <a href="{{ route('profile.edit') }}" class="dropdown-item has-icon">
            <i class="far fa-user"></i> Profile
          </a> --}}
          <div class="dropdown-divider"></div>
           <!-- Authentication -->
           <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}" onclick="event.preventDefault();
            this.closest('form').submit();" class="dropdown-item has-icon text-danger">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </form>
        </div>
      </li>
    </ul>
  </nav>
