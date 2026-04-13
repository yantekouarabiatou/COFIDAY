@extends('layaout')

@section('title', $title)

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-chart-bar"></i> {{ $title }}</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-header">
                <div class="card-header-action">
                    <form method="GET" action="{{ route('rapports.mensuel') }}" class="d-inline">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <select name="user_id" class="form-control select2">
                                    <option value="">Tous les employés</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ ($selectedUser && $selectedUser->id == $user->id) ? 'selected' : '' }}>
                                            {{ $user->nom }} - {{ $user->prenom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-auto">
                                <input type="month" name="date_filter" class="form-control"
                                    value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-primary">Voir</button>
                            </div>

                            @if($selectedUser || request()->has('user_id') || request()->has('date_filter'))
                                <div class="col-auto">
                                    <a href="{{ route('rapports.mensuel') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Réinitialiser
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if(isset($selectedUser))
                    @include('pages.rapports.partials.single-user', [
                        'entries' => $dailyEntries,
                        'user' => $selectedUser
                    ])
                @else
                    @foreach($dailyEntries as $userId => $entries)
                        @php $user = $entries->first()->user; @endphp
                        @include('pages.rapports.partials.single-user', [
                            'entries' => $entries,
                            'user' => $user
                        ])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
