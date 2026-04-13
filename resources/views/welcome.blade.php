<!DOCTYPE html>
<html lang="en">
<!-- blank.html  21 Nov 2019 03:54:41 GMT -->

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title', 'COFIMA - Admin Tableau de bord')</title>

    {{-- Favicon avec le logo COFIMA --}}
    @if(file_exists(storage_path('app/public/company/logo_cofima_bon.jpg')))
        <link rel="icon" href="{{ Storage::url('company/logo_cofima_bon.jpg') }}" type="image/jpeg">
        <link rel="apple-touch-icon" href="{{ Storage::url('company/logo_cofima_bon.jpg') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
    @stack('styles')
</head>

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li>
                            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                                <i data-feather="align-justify"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link nav-link-lg fullscreen-btn">
                                <i data-feather="maximize"></i>
                            </a>
                        </li>
                        <li>
                            <form class="form-inline mr-auto">
                                <div class="search-element">
                                    <input class="form-control" type="search" placeholder="Rechercher..."
                                        aria-label="Search">
                                    <button class="btn" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </li>
                    </ul>
                </div>

                <ul class="navbar-nav navbar-right">
                    <!-- Notifications -->
                    <li class="dropdown dropdown-list-toggle">
                        @auth
                        @php
                            $user = auth()->user();
                            $unreadCount = $user->unreadNotifications()->count();
                            $notifications = $user->notifications()->latest()->take(7)->get();
                        @endphp
                        <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg">
                            <i class="far fa-bell" style="color: #000000;"></i>
                            <span id="unread-count" class="badge badge-danger badge-header"
                                style="{{ $unreadCount > 0 ? '' : 'display: none;' }}">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        </a>
                            <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown" style="width: 360px;">
                                <div class="dropdown-header d-flex justify-content-between">
                                    Notifications
                                    @if($unreadCount > 0)
                                        <form method="POST" action="{{ route('notifications.mark-all-read') }}"
                                            class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-primary p-0 border-0"
                                                style="font-size: 12px;">
                                                Tout marquer comme lu
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <div class="dropdown-list-content dropdown-list-icons"
                                    style="max-height: 300px; overflow-y: auto;">
                                    @forelse($notifications as $notification)
                                        <a href="{{ $notification->data['url'] ?? '#' }}"
                                            class="dropdown-item {{ $notification->read_at ? '' : 'dropdown-item-unread' }}"
                                            data-notification-id="{{ $notification->id }}"
                                            onclick="handleNotificationClick(event, this)">
                                            <div
                                                class="dropdown-item-icon {{ $notification->data['color'] ?? 'bg-primary' }} text-white">
                                                <i class="{{ $notification->data['icon'] ?? 'fas fa-bell' }}"></i>
                                            </div>
                                            <div class="dropdown-item-desc">
                                                {!! $notification->data['message'] ?? 'Notification sans message' !!}
                                                <div class="time text-muted">
                                                    {{ $notification->created-at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="dropdown-item text-center text-muted py-4">
                                            <i class="far fa-bell-slash fa-2x mb-2"></i>
                                            <p>Aucune notification</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="dropdown-footer text-center">
                                    <a href="{{ route('notifications.index') }}">
                                        Voir toutes les notifications <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        @endauth
                    </li>

                    <!-- Icône paramètres visible sur mobile - VERSION SIMPLIFIÉE -->
                    <li class="nav-item d-lg-none">
                        <a href="#" class="nav-link nav-link-lg settingPanelToggle">
                            <i class="fa fa-cog"></i>
                        </a>
                    </li>

                    <!-- Menu utilisateur UNIQUE avec responsive -->
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            @auth
                                @php
                                    $user = auth()->user();

                                    // 1. Gestion du Nom et Initiales
                                    $nomComplet = $user->prenom . ' ' . $user->nom;
                                    $initiales = strtoupper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1));

                                    // 2. Gestion du Rôle
                                    $technicalName = null;

                                    if ($user->role) {
                                        $technicalName = $user->role->name;
                                    } elseif ($user->role_id && !is_numeric($user->role_id)) {
                                        $technicalName = $user->role_id;
                                    }

                                    $roleNames = [
                                        'super-admin' => 'Super Administrateur',
                                        'admin' => 'Administrateur',
                                        'responsable-conformite' => 'Responsable Conformité',
                                        'auditeur' => 'Auditeur Interne',
                                        'employe' => 'Agent de Traitement',
                                        'user' => 'Utilisateur Standard',
                                    ];

                                    if ($technicalName) {
                                        $displayRole = $roleNames[$technicalName] ?? ucwords(str_replace('-', ' ', $technicalName));
                                    } else {
                                        $displayRole = 'Utilisateur';
                                    }

                                    // 3. Gestion de la couleur
                                    $colors = [
                                        ['bg' => '#4a70b7', 'border' => '#3a5a9d'],
                                        ['bg' => '#10b981', 'border' => '#0da271'],
                                        ['bg' => '#f59e0b', 'border' => '#d97706'],
                                        ['bg' => '#ef4444', 'border' => '#dc2626'],
                                        ['bg' => '#06b6d4', 'border' => '#0891b2'],
                                        ['bg' => '#8b5cf6', 'border' => '#7c3aed'],
                                    ];
                                    $colorIndex = crc32($user->username ?? $user->email) % count($colors);
                                    $selectedColor = $colors[$colorIndex];
                                @endphp

                                <div class="d-flex align-items-center">
                                    <!-- Avatar - visible sur mobile et desktop -->
                                    <div class="avatar-wrapper position-relative">
                                        @if($user->photo)
                                            <img alt="image" src="{{ asset('storage/' . $user->photo) }}"
                                                class="user-img-radious-style"
                                                style="width: 38px; height: 38px; object-fit: cover; border-radius: 50%;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center user-img-radious-style"
                                                style="background: {{ $selectedColor['bg'] }}; color: white; width: 38px; height: 38px;
                                                border-radius: 50%; font-weight: 600; font-size: 14px;
                                                border: 2px solid {{ $selectedColor['border'] }};">
                                                {{ $initiales }}
                                            </div>
                                        @endif
                                        <div
                                            style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px;
                                            background: #10b981; border: 2px solid white; border-radius: 50%;">
                                        </div>
                                    </div>

                                    <!-- Info utilisateur - caché sur mobile -->
                                    <div class="user-info ml-2 d-none d-lg-block">
                                        <div class="user-name"
                                            style="font-size: 14px; font-weight: 600; color: #2d3748; line-height: 1.2;">
                                            {{ $nomComplet }}
                                        </div>
                                        <div class="user-role" style="font-size: 12px; color: #718096; line-height: 1.2;">
                                            {{ $displayRole }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="d-flex align-items-center">
                                    <div class="avatar-wrapper">
                                        <img alt="image" src="{{ asset('assets/img/user.png') }}"
                                            class="user-img-radious-style"
                                            style="width: 38px; height: 38px; object-fit: cover; border-radius: 50%;">
                                    </div>
                                    <div class="user-info ml-2 d-none d-lg-block">
                                        <div class="user-name" style="font-size: 14px; font-weight: 600; color: #2d3748;">
                                            Invité</div>
                                    </div>
                                </div>
                            @endauth
                        </a>

                        <!-- Dropdown menu - identique pour mobile et desktop -->
                        <div class="dropdown-menu dropdown-menu-right pullDown"
                            style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 12px; min-width: 260px; overflow: hidden;">

                            @auth
                                <div class="dropdown-header"
                                    style="background: linear-gradient(135deg, #4a70b7, #2c5282); color: white; padding: 20px;">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            @if($user->photo)
                                                <img alt="image" src="{{ asset('storage/' . $user->photo) }}"
                                                    style="width: 54px; height: 54px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.3);">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center"
                                                    style="background: {{ $selectedColor['bg'] }}; color: white; width: 54px; height: 54px;
                                                    border-radius: 50%; font-weight: 600; font-size: 18px; border: 3px solid rgba(255,255,255,0.3);">
                                                    {{ $initiales }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-size: 16px; font-weight: 600; margin-bottom: 2px;">
                                                {{ $user->prenom }}
                                            </div>
                                            <div style="font-size: 12px; opacity: 0.8; margin-bottom: 6px;">
                                                {{ $user->email }}
                                            </div>
                                            <div
                                                style="font-size: 11px; font-weight: 600; background: rgba(255,255,255,0.2);
                                                padding: 3px 10px; border-radius: 20px; display: inline-block;">
                                                {{ $displayRole }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropdown-body" style="padding: 10px 0;">
                                    <a href="{{ route('user-profile.show', $user->id) }}"
                                        class="dropdown-item has-icon d-flex align-items-center py-2">
                                        <div class="icon-wrapper mr-3 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: #ebf5ff; border-radius: 8px;">
                                            <i class="far fa-user text-primary" style="font-size: 14px;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: #2d3748;">Mon profil</div>
                                            <small class="text-muted d-block" style="line-height: 1;">Voir mes
                                                informations</small>
                                        </div>
                                    </a>

                                    <a href="{{ route('notifications.index') }}"
                                        class="dropdown-item has-icon d-flex align-items-center py-2">
                                        <div class="icon-wrapper mr-3 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: #fff5f5; border-radius: 8px;">
                                            <i class="far fa-bell text-danger" style="font-size: 14px;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: #2d3748;">Notifications</div>
                                            <small class="text-muted d-block" style="line-height: 1;">
                                                {{ isset($unreadCount) && $unreadCount > 0 ? $unreadCount . ' nouvelles' : 'À jour' }}
                                            </small>
                                        </div>
                                    </a>

                                    <a href="{{ route('activities') }}"
                                        class="dropdown-item has-icon d-flex align-items-center py-2">
                                        <div class="icon-wrapper mr-3 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: #fffbeb; border-radius: 8px;">
                                            <i class="fas fa-bolt text-warning" style="font-size: 14px;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: #2d3748;">Mes activités</div>
                                            <small class="text-muted d-block" style="line-height: 1;">Historique
                                                récent</small>
                                        </div>
                                    </a>

                                    @can('access-settings')
                                        <a href="{{ route('settings.show') }}"
                                            class="dropdown-item has-icon d-flex align-items-center py-2">
                                            <div class="icon-wrapper mr-3 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: #f0f9ff; border-radius: 8px;">
                                                <i class="fas fa-cog text-info" style="font-size: 14px;"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #2d3748;">Paramètres</div>
                                                <small class="text-muted d-block" style="line-height: 1;">Configuration</small>
                                            </div>
                                        </a>
                                    @endcan

                                    <div class="dropdown-divider my-2"></div>

                                    <form method="POST" action="{{ route('logout') }}" id="logout-form-nav">
                                        @csrf
                                        <a href="#"
                                            class="dropdown-item has-icon d-flex align-items-center py-2 text-danger"
                                            onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
                                            <div class="icon-wrapper mr-3 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: #fef2f2; border-radius: 8px;">
                                                <i class="fas fa-sign-out-alt" style="font-size: 14px;"></i>
                                            </div>
                                            <div style="font-weight: 600;">Déconnexion</div>
                                        </a>
                                    </form>
                                </div>
                            @else
                                <div class="p-3 text-center">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-block">Se connecter</a>
                                </div>
                            @endauth
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- ... reste du code inchangé ... -->

        </div>
    </div>

    <!-- Modals et scripts ... -->

    <!-- Scripts JS -->
    <script src="{{ asset('assets/js/app.min.js') }}"></script>

    @if(request()->routeIs('home') || request()->routeIs('dashboard') || request()->is('/'))
        <script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('assets/js/page/index.js') }}"></script>
    @endif

    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>

    @include('sweetalert::alert')

    <!-- Script pour gérer le panneau de settings -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gestion du bouton settings mobile
            const mobileSettingBtn = document.querySelector('.settingPanelToggle');
            const settingsPanel = document.querySelector('.settingSidebar');

            if (mobileSettingBtn && settingsPanel) {
                mobileSettingBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    settingsPanel.classList.toggle('show');
                });

                // Fermer en cliquant sur le bouton X dans le panel
                const closeBtn = settingsPanel.querySelector('.settingPanelToggle');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        settingsPanel.classList.remove('show');
                    });
                }

                // Fermer en cliquant à l'extérieur
                document.addEventListener('click', function(event) {
                    if (settingsPanel.classList.contains('show') &&
                        !settingsPanel.contains(event.target) &&
                        !mobileSettingBtn.contains(event.target)) {
                        settingsPanel.classList.remove('show');
                    }
                });
            }

            // Debug: afficher un message si le bouton est cliqué
            console.log('Script chargé');
            console.log('Bouton settings mobile:', mobileSettingBtn);
            console.log('Panel settings:', settingsPanel);

            // Gestion des notifications
            @if(auth()->check())
                if (typeof Echo !== 'undefined') {
                    Echo.private(`App.Models.User.{{ auth()->id() }}`)
                        .notification((notification) => {
                            console.log('Nouvelle notification reçue :', notification);

                            const badge = document.getElementById('unread-count');
                            if (badge) {
                                let count = parseInt(badge.textContent.replace('+', '')) || 0;
                                count++;
                                badge.textContent = count > 99 ? '99+' : count;
                                badge.style.display = 'block';
                            }

                            Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                icon: 'info',
                                title: notification.message || 'Nouvelle notification',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                            }).fire();
                        });
                }
            @endif

            // Initialisation Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: "Sélectionner une option",
                allowClear: true,
                width: '100%'
            });

            // Réinitialiser Select2 quand le modal se ferme
            $('#exportModal, #exportExcelModal').on('hidden.bs.modal', function () {
                $(this).find('.select2').select2('destroy').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            });
        });

        function handleNotificationClick(event, element) {
            const notificationId = element.dataset.notificationId;
            const url = element.getAttribute('href');
            const isUnread = element.classList.contains('dropdown-item-unread');

            if (!isUnread) {
                window.location.href = url;
                return;
            }

            event.preventDefault();

            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (response.ok) {
                    element.classList.remove('dropdown-item-unread');
                    const badge = document.getElementById('unread-count');
                    let count = parseInt(badge.textContent.replace('+', '')) || 0;
                    count--;
                    if (count <= 0) {
                        badge.style.display = 'none';
                    } else {
                        badge.textContent = count > 99 ? '99+' : count;
                    }
                    window.location.href = url;
                }
            })
            .catch(() => {
                window.location.href = url;
            });
        }
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
