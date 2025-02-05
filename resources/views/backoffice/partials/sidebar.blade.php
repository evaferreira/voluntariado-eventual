<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ asset('/bower_components/admin-lte/dist/img/user_avatar.png') }}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ Auth::user()->nombreCompleto }}</p>
                <!-- Rol -->
                <p class="small"><i class="fa fa-circle text-success"></i> {{ Auth::user()->getRoleNames()->first() }}</p>
            </div>
        </div>

{{--        <!-- search form (Optional) -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
              </button>
            </span>
            </div>
        </form>
        <!-- /.search form -->--}}

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MENU</li>
            <!-- Optionally, you can add icons to the links -->
            {{--<li class="active"><a href="#"><i class="fa fa-link"></i> <span>Link</span></a></li>--}}
            {{--<li><a href="#"><i class="fa fa-link"></i> <span>Another Link</span></a></li>--}}
            <li class="treeview {{ request()->is('admin/actividades*') ? 'active menu-open' : ''}}">
                <a href="#"><i class="fa fa-calendar"></i> <span>Actividades</span>
                    <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
                </a>
                <ul class="treeview-menu">
                    @if (Auth::user()->hasRole('admin'))
                        <li class="{{request()->is('admin/actividades') ? 'active' : ''}}"><a href="/admin/actividades">Ver Todas</a></li>
                    @endif
                    <li class="{{request()->is('admin/actividades/crear') ? 'active' : ''}}"><a href="/admin/actividades/crear">Crear Nueva Actividad</a></li>
                    @if(Auth::user()->hasPermissionTo('ver_mis_actividades'))
                        <li class="{{request()->is('admin/actividades/usuario') ? 'active' : ''}}"><a href="/admin/actividades/usuario">Mis Actividades</a></li>
                    @endif
                </ul>
            </li>
            @if(Auth::user()->hasPermissionTo('ver_usuarios'))
                <li class="treeview {{ request()->is('admin/usuarios*') ? 'active menu-open' : ''}}">
                    <a href="#"><i class="fa fa-user"></i> <span>Usuarios</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                  </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="{{request()->is('admin/usuarios') ? 'active' : ''}}"><a href="/admin/usuarios">Ver listado</a></li>
                        <li class="{{request()->is('admin/usuarios/registrar') ? 'active' : ''}}"><a href="/admin/usuarios/registrar">Registrar Usuario</a></li>
                    </ul>
                </li>
            @endif
            @if(Auth::user()->hasPermissionTo('asignar_roles'))
                <li class="treeview {{ request()->is('admin/roles') ? 'active menu-open' : ''}}">
                    <a href="#"><i class="fa fa-shield"></i> <span>Roles</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                  </span>
                    </a>
                    <ul class="treeview-menu">
                            <li class="{{request()->is('admin/roles') ? 'active' : ''}}"><a href="/admin/roles">Asignar roles</a></li>
                    </ul>
                </li>
            @endif
            <li class="treeview">
                <a href="#"><i class="fa fa-globe"></i> <span>Portal de Actividades</span>
                    <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/">Home</a></li>
                    <li><a href="/actividades">Ver filtros</a></li>
                </ul>
            </li>
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>