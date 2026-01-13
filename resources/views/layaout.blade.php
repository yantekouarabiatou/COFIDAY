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
                                                    {{ $notification->created_at->diffForHumans() }}
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
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            @auth
                                @php
                                    $user = auth()->user();

                                    // 1. Gestion du Nom et Initiales
                                    $nomComplet = $user->prenom . ' ' . $user->nom;
                                    $initiales = strtoupper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1));

                                    // 2. Gestion du Rôle (Récupération via role_id)
                                    // On vérifie si la relation 'role' existe, sinon on regarde si role_id contient directement le nom (cas rare)
                                    $technicalName = null;

                                    if ($user->role) {
                                        // Cas standard : role_id est un chiffre qui pointe vers la table roles
                                        $technicalName = $user->role->name;
                                    } elseif ($user->role_id && !is_numeric($user->role_id)) {
                                        $technicalName = $user->role_id;
                                    }

                                    // Tableau de traduction
                                    $roleNames = [
                                        'super-admin' => 'Super Administrateur',
                                        'admin' => 'Administrateur',
                                        'responsable-conformite' => 'Responsable Conformité',
                                        'auditeur' => 'Auditeur Interne',
                                        'gestionnaire-plaintes' => 'Gestionnaire des Plaintes',
                                        'agent' => 'Agent de Traitement',
                                        'user' => 'Utilisateur Standard',
                                    ];

                                    // Logique d'affichage
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
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="index.html">
                            <img alt="image" src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" class="header-logo" />
                            <span class="logo-name"></span>
                        </a>
                    </div>
                    <ul class="sidebar-menu">

                        {{-- GESTION DES REGISTRES - Uniquement Clients, Dossiers et Gestion des Temps --}}
                        @if(
                                auth()->user()->can('accéder au tableau de bord admin') ||
                                auth()->user()->can('accéder au tableau de bord utilisateur') ||
                                auth()->user()->can('voir les clients') ||
                                auth()->user()->can('créer des clients') ||
                                auth()->user()->can('voir les dossiers') ||
                                auth()->user()->can('créer des dossiers') ||
                                auth()->user()->can('voir les entrées journalières') ||
                                auth()->user()->can('créer des entrées journalières') ||
                                auth()->user()->can('voir tous les temps') ||
                                auth()->user()->can('voir les congés') ||
                                auth()->user()->can('créer des congés')
                            )
                            <li class="menu-header">GESTION DU TEMPS</li>

                            @can('accéder au tableau de bord utilisateur')
                                <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    <a href="{{ route('dashboard') }}" class="nav-link">
                                        <i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span>
                                    </a>
                                </li>
                            @endcan

                            {{-- Clients --}}
                            @can(['voir les clients', 'créer des clients'])
                                <li class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                                    <a href="{{ route('clients.index') }}" class="nav-link">
                                        <i class="fas fa-user-tie"></i><span>Clients</span>
                                    </a>
                                </li>
                            @endcan

                            {{-- Dossiers --}}
                            @can(['voir les dossiers', 'créer des dossiers'])
                                <li class="dropdown {{ request()->routeIs('dossiers.*') || request()->routeIs('missions.*') ? 'active' : '' }}">
                                    <a href="#" class="menu-toggle nav-link has-dropdown {{ request()->routeIs('dossiers.*') || request()->routeIs('missions.*') ? 'active' : '' }}">
                                        <i class="fas fa-folder-open"></i><span>Dossiers</span>
                                    </a>
                                    <ul class="dropdown-menu" style="{{ request()->routeIs('dossiers.*') || request()->routeIs('missions.*') ? 'display: block;' : '' }}">
                                        @can('voir les dossiers')
                                            <li class="{{ request()->routeIs('dossiers.index') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('dossiers.index') }}">
                                                    <i class="fas fa-list"></i> Liste des dossiers
                                                </a>
                                            </li>
                                        @endcan

                                        @can('créer des dossiers')
                                            <li class="{{ request()->routeIs('dossiers.create') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('dossiers.create') }}">
                                                    <i class="fas fa-plus-circle"></i> Nouveau dossier
                                                </a>
                                            </li>
                                        @endcan

                                        {{-- Séparateur --}}
                                        <li><hr class="dropdown-divider"></li>

                                        {{-- Nouvelles fonctionnalités d'analyse --}}
                                        @can(['voir les rapports mensuels', 'voir tous les temps'])
                                            <li class="dropdown-title">
                                                <span class="" style="color: #2c5282"><i class="fas fa-chart-line"></i> ANALYSE & RAPPORTS</span>
                                            </li>

                                            <li class="{{ request()->routeIs('missions.analyse') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('missions.analyse') }}">
                                                    <i class="fas fa-chart-pie"></i> Analyse par mission
                                                </a>
                                            </li>
                                            <li>
                                                <a class="nav-link" href="#" data-toggle="modal" data-target="#exportModal">
                                                    <i class="fas fa-file-pdf"></i> Rapport complet
                                                </a>
                                            </li>

                                            <li>
                                                <a class="nav-link" href="#" data-toggle="modal" data-target="#exportExcelModal">
                                                    <i class="fas fa-file-excel"></i> Export Excel
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            {{-- Gestion des Temps --}}
                            @can(['voir les entrées journalières', 'créer des entrées journalières', 'voir tous les temps'])
                                <li class="dropdown {{ request()->routeIs('daily-entries.*') || request()->routeIs('rapports.mensuel') ? 'active' : '' }}">
                                    <a href="#" class="menu-toggle nav-link has-dropdown {{ request()->routeIs('daily-entries.*') || request()->routeIs('rapports.mensuel') ? 'active' : '' }}">
                                        <i class="fas fa-clock"></i><span>Gestion des Temps</span>
                                    </a>
                                    <ul class="dropdown-menu" style="{{ request()->routeIs('daily-entries.*') || request()->routeIs('rapports.mensuel') ? 'display: block;' : '' }}">
                                        @can('créer des entrées journalières')
                                            <li class="{{ request()->routeIs('daily-entries.create') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('daily-entries.create') }}">
                                                    <i class="fas fa-plus-circle"></i> Saisir une tache
                                                </a>
                                            </li>
                                        @endcan
                                        @can(['voir les entrées journalières', 'voir tous les temps'])
                                            <li class="{{ request()->routeIs('daily-entries.index') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('daily-entries.index') }}">
                                                    <i class="fas fa-list-alt"></i> Liste des taches
                                                </a>
                                            </li>
                                        @endcan
                                        @can(['voir les rapports mensuels', 'voir tous les temps'])
                                            <li class="{{ request()->routeIs('rapports.mensuel') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('rapports.mensuel') }}">
                                                    <i class="fas fa-calendar-alt"></i> Rapport mensuel
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan
                            {{-- Gestion des Congés --}}
                            @can(['voir les congés', 'créer des congés'])
                            <li class="dropdown {{ request()->is('conges*') ? 'active' : '' }}">
                                <a href="#" class="menu-toggle nav-link has-dropdown {{ request()->is('conges*') ? 'active' : '' }}">
                                    <i class="fas fa-umbrella-beach"></i><span>Gestion des Congés</span>
                                </a>
                                <ul class="dropdown-menu" style="{{ request()->is('conges*') ? 'display: block;' : '' }}">
                                    @can('créer des congés')
                                        <li class="{{ request()->routeIs('conges.create') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('conges.create') }}"><i class="fas fa-user-plus"></i> Créer un congé</a>
                                        </li>
                                    @endcan


                                    @can('voir les congés')
                                        <li class="{{ request()->routeIs('conges.index') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('conges.index') }}"><i class="fas fa-list"></i> Liste des congés</a>
                                        </li>
                                    @endcan

                                    @can('voir les congés')
                                    <li class="{{ request()->routeIs('conges.calendrier') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('conges.calendrier') }}">
                                            <i class="fas fa-calendar-alt"></i> <span>Calendrier des congés</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('voir les congés')
                                    <li class="{{ request()->routeIs('conges.calendrier') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('conges.solde') }}">
                                            <i class="fas fa-chart-pie"></i> <span>soldes des congés</span>
                                        </a>
                                    </li>
                                @endcan
                                </ul>
                            </li>
                            @endcan

                        @endif

                        {{-- GESTION DES PARAMÈTRES (inchangée) --}}
                        @if(
                                auth()->user()->can('voir les utilisateurs') ||
                                auth()->user()->can('créer des utilisateurs') ||
                                auth()->user()->can('voir les postes') ||
                                auth()->user()->can('voir les rôles') ||
                                auth()->user()->can('voir les permissions') ||
                                auth()->user()->can('gérer les permissions') ||
                                auth()->user()->can('voir les paramètres')
                            )
                            <li class="menu-header">GESTION DES PARAMÈTRES</li>

                            @can(['voir les utilisateurs', 'créer des utilisateurs', 'voir les postes'])
                                <li class="dropdown {{ request()->is('users*') || request()->is('postes*') ? 'active' : '' }}">
                                    <a href="#"
                                        class="menu-toggle nav-link has-dropdown {{ request()->is('users*') || request()->is('postes*') ? 'active' : '' }}">
                                        <i class="fas fa-users-cog"></i><span>Gestion des Utilisateurs</span>
                                    </a>
                                    <ul class="dropdown-menu"
                                        style="{{ request()->is('users*') || request()->is('postes*') ? 'display: block;' : '' }}">
                                        @can('créer des utilisateurs')
                                            <li class="{{ request()->routeIs('users.create') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('users.create') }}"><i
                                                        class="fas fa-user-plus"></i> Créer un utilisateur</a>
                                            </li>
                                        @endcan
                                        @can('voir les utilisateurs')
                                            <li class="{{ request()->routeIs('users.index') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-list"></i>
                                                    Liste des utilisateurs</a>
                                            </li>
                                        @endcan
                                        @can('voir les postes')
                                            <li class="{{ request()->routeIs('postes.*') ? 'active' : '' }}">
                                                <a class="nav-link" href="{{ route('postes.index') }}"><i
                                                        class="fas fa-briefcase"></i> Gestion des postes</a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            @can(['voir les rôles', 'voir les permissions', 'gérer les permissions'])
                                <li
                                    class="dropdown {{ request()->is('admin/permissions*') || request()->is('roles*') ? 'active' : '' }}">
                                    <a href="#"
                                        class="menu-toggle nav-link has-dropdown {{ request()->is('admin/permissions*') || request()->is('roles*') ? 'active' : '' }}">
                                        <i class="fas fa-user-lock"></i><span>Gestion des permissions</span>
                                    </a>
                                    <ul class="dropdown-menu"
                                        style="{{ request()->is('admin/permissions*') || request()->is('roles*') ? 'display: block;' : '' }}">
                                        @can('voir les rôles')
                                            <li><a class="nav-link" href="{{ route('admin.roles.index') }}"><i
                                                        class="fas fa-user-tag"></i> Gérer les rôles</a></li>
                                        @endcan
                                        @can(['voir les permissions', 'gérer les permissions'])
                                            <li><a class="nav-link" href="{{ route('admin.roles.permissions.index') }}"><i
                                                        class="fas fa-tasks"></i> Gérer les permissions</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            @can('voir les paramètres')
                                <li class="dropdown {{ request()->is('settings*') ? 'active' : '' }}">
                                    <a href="#"
                                        class="menu-toggle nav-link has-dropdown {{ request()->is('settings*') ? 'active' : '' }}">
                                        <i class="fas fa-cog"></i><span>Paramètres Entreprise</span>
                                    </a>
                                    <ul class="dropdown-menu" style="{{ request()->is('settings*') ? 'display: block;' : '' }}">
                                        <li>
                                            <a class="nav-link" href="{{ route('settings.show') }}">
                                                <i class="fas fa-building"></i> Paramètres Généraux
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                        @endif

                        {{-- GESTION DES LOGS (inchangée) --}}
                        @can(['accéder au tableau de bord admin', 'accéder au tableau de bord utilisateur'])
                            <li class="menu-header">GESTION DES LOGS</li>

                            <li
                                class="dropdown {{ request()->routeIs('activities') || request()->routeIs('notifications.*') ? 'active' : '' }}">
                                <a href="#"
                                    class="menu-toggle nav-link has-dropdown {{ request()->routeIs('activities') || request()->routeIs('notifications.*') ? 'active' : '' }}">
                                    <i class="fas fa-clipboard-list"></i><span>Gestion des activités</span>
                                </a>
                                <ul class="dropdown-menu"
                                    style="{{ request()->routeIs('activities') || request()->routeIs('notifications.*') ? 'display: block;' : '' }}">
                                    @can('accéder au tableau de bord admin')
                                        <li class="{{ request()->routeIs('activities') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('activities') }}"><i class="fas fa-history"></i>
                                                Voir Activités</a>
                                        </li>
                                    @endcan
                                    <li class="{{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('notifications.index') }}"><i
                                                class="fas fa-bell"></i> Notifications</a>
                                    </li>
                                </ul>
                            </li>
                        @endcan

                        {{-- RAPPORTS & STATISTIQUES (rapport temps conservé) --}}
                        @can('voir les statistiques')
                            <li class="menu-header">RAPPORTS & STATISTIQUES</li>

                            @php
                                $rapportActive = request()->is('rapports*')
                                    || request()->routeIs('statistics.*')
                                    || request()->routeIs('stats.*')
                                    || request()->routeIs('reports.*');
                            @endphp

                            <li class="dropdown {{ $rapportActive ? 'active' : '' }}">
                                <a href="#" class="menu-toggle nav-link has-dropdown {{ $rapportActive ? 'active' : '' }}">
                                    <i class="fas fa-chart-line"></i><span>Statistiques</span>
                                </a>

                                <ul class="dropdown-menu" style="{{ $rapportActive ? 'display: block;' : '' }}">
                                    @can('voir les statistiques générales')
                                    <li class="{{ request()->routeIs('admin.stats.globale') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.stats.globale') }}">
                                            <i class="fas fa-chart-pie"></i> Statistiques générales
                                        </a>
                                    </li>
                                    @endcan

                                    @can(['voir les rapports mensuels', 'voir tous les temps'])
                                        <li class="{{ request()->routeIs('rapports.mensuel') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('rapports.mensuel') }}">
                                                <i class="fas fa-clock"></i> Rapport des Temps
                                            </a>
                                        </li>
                                    @endcan

                                     @can(['voir les rapports mensuels', 'voir tous les temps'])
                                        <li class="{{ request()->routeIs('rapports.mensuel') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('conges.dashboard') }}">
                                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                                            </a>
                                        </li>
                                    @endcan


                                </ul>
                            </li>
                        @endcan

                    </ul>
                </aside>
            </div>
            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="section-body">
                        @yield('content')
                    </div>
                </section>
                <div class="settingSidebar">
                    <a href="javascript:void(0)" class="settingPanelToggle">
                        <i class="fa fa-spin fa-cog"></i>
                    </a>
                    <div class="settingSidebar-body ps-container ps-theme-default">
                        <div class=" fade show active">
                            <div class="setting-panel-header">Panneau de Configuration</div>

                            <div class="p-15 border-bottom">
                                <h6 class="font-medium m-b-10">Sélectionner la Disposition</h6>
                                <div class="selectgroup layout-color w-50">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="value" value="1"
                                            class="selectgroup-input-radio select-layout" checked>
                                        <span class="selectgroup-button">Clair</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="value" value="2"
                                            class="selectgroup-input-radio select-layout">
                                        <span class="selectgroup-button">Sombre</span>
                                    </label>
                                </div>
                            </div>

                            <div class="p-15 border-bottom">
                                <h6 class="font-medium m-b-10">Couleur de la Barre Latérale</h6>
                                <div class="selectgroup selectgroup-pills sidebar-color">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="icon-input" value="1"
                                            class="selectgroup-input select-sidebar">
                                        <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                                            data-original-title="Barre latérale claire">
                                            <i class="fas fa-sun"></i>
                                        </span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="icon-input" value="2"
                                            class="selectgroup-input select-sidebar" checked>
                                        <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                                            data-original-title="Barre latérale sombre">
                                            <i class="fas fa-moon"></i>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="p-15 border-bottom">
                                <div class="theme-setting-options">
                                    <label class="m-b-0">
                                        <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                                            id="mini_sidebar_setting">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="control-label p-l-10">Barre Latérale Mini</span>
                                    </label>
                                </div>
                            </div>

                            <div class="p-15 border-bottom">
                                <div class="theme-setting-options">
                                    <label class="m-b-0">
                                        <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                                            id="sticky_header_setting">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="control-label p-l-10">En-tête Fixe</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele">
                                <a href="#" class="btn btn-icon icon-left btn-primary btn-restore-theme">
                                    <i class="fas fa-undo"></i> Restaurer les Paramètres par Défaut
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <footer class="main-footer">
                <div class="footer-left">
                    <a href="templateshub.net">COFIMA BENIN</a></a>
                </div>
                <div class="footer-right">
                </div>
            </footer>
        </div>
    </div>

         <!-- Modal Export PDF -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-file-pdf mr-2"></i> Exporter le rapport en PDF
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <form id="exportPdfForm" action="{{ route('missions.filtrer') }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="export_format" value="pdf">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="pdf_dossier_id">Dossier concerné <span class="text-danger">*</span></label>
                        <select name="dossier_id" id="pdf_dossier_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Sélectionner un dossier --</option>
                            @foreach(App\Models\Dossier::enCours()->orderBy('reference')->get() as $dossier)
                                <option value="{{ $dossier->id }}">
                                    {{ $dossier->reference }} — {{ $dossier->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pdf_date_debut">Date de début</label>
                                <input type="date" name="date_debut" id="pdf_date_debut" class="form-control"
                                       value="{{ date('Y-m-d', strtotime('-1 month')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pdf_date_fin">Date de fin</label>
                                <input type="date" name="date_fin" id="pdf_date_fin" class="form-control"
                                       value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pdf_type_rapport">Type de rapport</label>
                        <select name="type_rapport" id="pdf_type_rapport" class="form-control select2" style="width: 100%;">
                            <option value="complet" selected>Rapport complet (avec analyse détaillée)</option>
                            <option value="simple">Rapport simple (liste des interventions)</option>
                            <option value="statistiques">Statistiques seulement</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-pdf mr-2"></i> Générer le PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export Excel -->
<div class="modal fade" id="exportExcelModal" tabindex="-1" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exportExcelModalLabel">
                    <i class="fas fa-file-excel mr-2"></i> Exporter les données en Excel
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <form id="exportExcelForm" action="{{ route('missions.filtrer') }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="export_format" value="excel">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="excel_dossier_id">Dossier(s) à exporter</label>
                        <select name="dossier_id" id="excel_dossier_id" class="form-control select2" style="width: 100%;">
                            <option value="all">Tous les dossiers en cours</option>
                            <optgroup label="Dossier spécifique">
                                @foreach(App\Models\Dossier::enCours()->orderBy('reference')->get() as $dossier)
                                    <option value="{{ $dossier->id }}">
                                        {{ $dossier->reference }} — {{ $dossier->nom }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date de début</label>
                                <input type="date" name="date_debut" class="form-control"
                                       value="{{ date('Y-m-d', strtotime('-1 month')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date de fin</label>
                                <input type="date" name="date_fin" class="form-control"
                                       value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Contenu à inclure dans l'export</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chk_personnels" name="include_personnels" checked>
                            <label class="custom-control-label" for="chk_personnels">Liste des personnels intervenants</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chk_temps" name="include_temps" checked>
                            <label class="custom-control-label" for="chk_temps">Temps passé par jour et par personne</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chk_stats" name="include_statistiques" checked>
                            <label class="custom-control-label" for="chk_stats">Statistiques et totaux</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chk_autres" name="include_autres_missions">
                            <label class="custom-control-label" for="chk_autres">Autres missions non liées</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-excel mr-2"></i> Exporter en Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- 1. jQuery + Bootstrap + Stisla JS (app.min.js contient déjà jQuery) -->
    <script src="{{ asset('assets/js/app.min.js') }}"></script>

    <!-- 2. JS conditionnels dashboard -->
    @if(request()->routeIs('home') || request()->routeIs('dashboard') || request()->is('/'))
        <script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('assets/js/page/index.js') }}"></script>
    @endif

    <!-- 3. Scripts Stisla obligatoires -->
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <!-- 4. Select2 JS (DOIT ÊTRE AVANT tes scripts perso) -->
    <script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
    @include('sweetalert::alert')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('status'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('status') }}',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ session('error') }}',
                    showConfirmButton: false,
                    timer: 6000,
                    timerProgressBar: true
                });
            @endif
            });
    </script>
    <!-- 5. SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('sweetalert::alert')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const mainWrapper = document.querySelector('.main-wrapper-1'); // Peut avoir besoin de la classe body-dark/light
            const sidebar = document.querySelector('.main-sidebar');
            const sidebarStyleBase = 'sidebar-style-2'; // Conservez votre classe de style de base
            const navbarBg = document.querySelector('.navbar-bg');
            const mainNavbar = document.querySelector('.main-navbar');

            // Récupère le thème sauvegardé (dark par défaut)
            // J'utilise 'light' par défaut pour coller aux classes de votre HTML initial si rien n'est stocké.
            const savedTheme = localStorage.getItem('theme') || 'light';

            function applyTheme(theme) {
                if (theme === 'light') {
                    body.classList.remove('dark-mode'); // J'utilise dark-mode au cas où votre thème l'utilise
                    body.classList.add('light-mode');

                    // 1. Barre latérale (Sidebar)
                    if (sidebar) {
                        // Supprimer les classes sombres spécifiques si elles existent
                        sidebar.classList.remove('sidebar-dark', 'dark-sidebar-specific-class');
                    }

                    // 2. Barre de navigation (Navbar)
                    if (navbarBg) {
                        // Pour la navbar-bg: souvent juste pour la couleur
                        navbarBg.classList.remove('bg-dark', 'navbar-dark');
                        navbarBg.classList.add('bg-white'); // Rendre la couleur de la barre claire (blanc)
                    }
                    if (mainNavbar) {
                        mainNavbar.classList.remove('navbar-dark');
                        mainNavbar.classList.add('navbar-light');
                    }


                } else {
                    // Mode sombre
                    body.classList.remove('light-mode');
                    body.classList.add('dark-mode');

                    // 1. Barre latérale (Sidebar)
                    if (sidebar) {
                        // Ajoutez la classe qui rend le sidebar sombre
                        sidebar.classList.add('sidebar-dark'); // Assurez-vous que votre CSS a une règle pour ça
                    }

                    // 2. Barre de navigation (Navbar)
                    if (navbarBg) {
                        // Rendre la couleur de la barre sombre
                        navbarBg.classList.remove('bg-white');
                        navbarBg.classList.add('bg-dark', 'navbar-dark');
                    }
                    if (mainNavbar) {
                        mainNavbar.classList.remove('navbar-light');
                        mainNavbar.classList.add('navbar-dark');
                    }
                }
            }

            // Applique immédiatement le thème sauvegardé
            applyTheme(savedTheme);

            // Synchronise les boutons du panneau de configuration
            document.querySelectorAll('.select-layout').forEach(input => {
                // Correction de la logique de vérification
                const targetTheme = input.value === '1' ? 'light' : 'dark';
                if (targetTheme === savedTheme) {
                    input.checked = true;
                }

                input.addEventListener('change', function () {
                    const newTheme = this.value === '1' ? 'light' : 'dark';
                    applyTheme(newTheme);
                    localStorage.setItem('theme', newTheme);
                });
            });

            // Logique pour la barre latérale "sombre/claire" dans le panneau de config
            document.querySelectorAll('.select-sidebar').forEach(input => {
                // Note: Cette logique est souvent redondante si le thème est déjà global.
                // Je la neutralise pour la laisser gérer par le thème global.
                // Si vous voulez une sidebar sombre dans un thème clair, vous devrez
                // implémenter une logique distincte ici, affectant les classes de la sidebar.
            });
        });
    </script>
    <script>
        function handleNotificationClick(event, element) {
            const notificationId = element.dataset.notificationId;
            const url = element.getAttribute('href');
            const isUnread = element.classList.contains('dropdown-item-unread');

            // Si déjà lue → on va directement
            if (!isUnread) {
                window.location.href = url;
                return;
            }

            // Sinon → on marque comme lue en AJAX
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
                        // Mise à jour visuelle immédiate
                        element.classList.remove('dropdown-item-unread');

                        // Mise à jour du compteur
                        const badge = document.getElementById('unread-count');
                        let count = parseInt(badge.textContent.replace('+', '')) || 0;
                        count--;
                        if (count <= 0) {
                            badge.style.display = 'none';
                        } else {
                            badge.textContent = count > 99 ? '99+' : count;
                        }

                        // Redirection après succès
                        window.location.href = url;
                    }
                })
                .catch(() => {
                    // En cas d'erreur → on va quand même
                    window.location.href = url;
                });
        }
    </script>
    @if(auth()->check())
        <script>
            // Chargement sécurisé de Pusher + Laravel Echo (uniquement si tu utilises les notifications en temps réel)
            document.addEventListener('DOMContentLoaded', function () {
                // Si Echo n'est pas chargé (ex: page sans mix, ou sans npm run dev), on évite l'erreur fatale
                if (typeof Echo === 'undefined') {
                    console.warn('Laravel Echo non chargé sur cette page – notifications en temps réel désactivées.');
                    return;
                }

                Echo.private(`App.Models.User.{{ auth()->id() }}`)
                    .notification((notification) => {
                        console.log('Nouvelle notification reçue :', notification);

                        // Mise à jour du badge du nombre de notifications non lues
                        const badge = document.getElementById('unread-count');
                        if (badge) {
                            let count = parseInt(badge.textContent.replace('+', '')) || 0;
                            count++;

                            badge.textContent = count > 99 ? '99+' : count;
                            badge.style.display = 'block';
                        }

                        // Toast discret en haut à droite
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: notification.message || 'Nouvelle notification',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                        });

                        Toast.fire();
                    });
            });
        </script>
    @endif
    <script>
        // Applique le thème dès le chargement (avant que la page s'affiche)
        (function () {
            const theme = localStorage.getItem('theme') || 'dark';
            if (theme === 'light') {
                document.body.classList.add('light');
            } else {
                document.body.classList.add('dark');
                document.querySelector('.main-sidebar')?.classList.add('sidebar-dark');
                document.querySelector('.navbar-bg')?.classList.add('navbar-dark');
            }
        })();
    </script>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialisation Select2 sur tous les selects avec la classe .select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Sélectionner une option",
            allowClear: true,
            width: '100%'
        });

        // Optionnel : réinitialiser Select2 quand le modal se ferme (évite bugs d'affichage)
        $('#exportModal, #exportExcelModal').on('hidden.bs.modal', function () {
            $(this).find('.select2').select2('destroy').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        });
    });
    </script>
    @endpush
    <!-- 6. TES SCRIPTS PERSONNALISÉS EN DERNIER ! -->
    @stack('scripts')
    @yield('scripts')
</body>

</html>
