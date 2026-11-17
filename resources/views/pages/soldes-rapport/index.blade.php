@extends('layaout')

@section('title', 'Rapport des soldes de congés')

@php
    $toutes     = $annee === 'toutes';
    $anneeLabel = $toutes ? 'toutes les années' : $annee;
    $fmt        = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        #userSelect + .select2-container { width: 100% !important; }
        .stat-box { border-radius: 8px; padding: 16px; color: #fff; }
        .stat-box h3 { margin: 0; font-weight: 700; color: #fff; }
        .stat-box small { opacity: .9; }
        .bg-acquis   { background: #2E75B6; }
        .bg-reportes { background: #6f42c1; }
        .bg-pris     { background: #dc3545; }
        .bg-restants { background: #2f9e44; }
        .year-pills .btn { margin: 0 4px 6px 0; min-width: 64px; }
        .table td, .table th { vertical-align: middle; text-align: center; }
        .table td:first-child, .table th:first-child { text-align: left; }
    </style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-chart-bar"></i> Rapport des soldes de congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Rapport des soldes</div>
        </div>
    </div>

    <div class="section-body">

        {{-- Filtres & exports --}}
        <div class="card">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-lg-6">
                        <label class="font-weight-bold d-block">Année</label>
                        <div class="year-pills">
                            <a href="{{ route('soldes-rapport.index', ['annee' => 'toutes']) }}"
                               class="btn btn-sm {{ $toutes ? 'btn-primary' : 'btn-outline-primary' }}">Toutes</a>
                            @foreach($annees->sortDesc() as $a)
                                <a href="{{ route('soldes-rapport.index', ['annee' => $a]) }}"
                                   class="btn btn-sm {{ !$toutes && $a == $annee ? 'btn-primary' : 'btn-outline-primary' }}">{{ $a }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label class="font-weight-bold">Utilisateur</label>
                        <select id="userSelect" class="form-control">
                            <option value="">- Tous les utilisateurs -</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->prenom }} {{ $u->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 text-lg-right mt-3 mt-lg-0">
                        <span id="exportLabel" class="d-block text-muted small mb-1"></span>
                        <a id="btnPdf" class="btn btn-danger" href="#"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a id="btnExcel" class="btn btn-success" href="#"><i class="fas fa-file-excel"></i> Excel</a>
                        <a class="btn btn-outline-success mt-1"
                           href="{{ route('soldes-rapport.export', ['format' => 'excel', 'annee' => $annee]) }}"
                           title="Synthèse {{ $toutes ? 'cumulée' : $annee }} + détail de chaque utilisateur pour chaque année">
                            <i class="fas fa-users"></i> Excel – tous les utilisateurs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Totaux --}}
        <div class="row">
            <div class="col-md-3 mb-3"><div class="stat-box bg-acquis"><small>Jours acquis - {{ $anneeLabel }}</small><h3 id="totAcquis">{{ $fmt($totaux['acquis']) }}</h3></div></div>
            <div class="col-md-3 mb-3"><div class="stat-box bg-reportes"><small>Jours reportés</small><h3 id="totReportes">{{ $fmt($totaux['reportes']) }}</h3></div></div>
            <div class="col-md-3 mb-3"><div class="stat-box bg-pris"><small>Jours consommés</small><h3 id="totPris">{{ $fmt($totaux['pris']) }}</h3></div></div>
            <div class="col-md-3 mb-3"><div class="stat-box bg-restants"><small>Jours restants</small><h3 id="totRestants">{{ $fmt($totaux['restants']) }}</h3></div></div>
        </div>

        {{-- Graphique par utilisateur --}}
        <div class="card">
            <div class="card-header">
                <h4>Solde et consommation par utilisateur - {{ $anneeLabel }}{{ $toutes ? ' (cumul)' : '' }}</h4>
            </div>
            <div class="card-body">
                @if($soldes->isEmpty())
                    <p class="text-muted text-center my-4">Aucun solde enregistré pour {{ $anneeLabel }}.</p>
                @else
                    <div id="chartUsers"></div>
                @endif
            </div>
        </div>

        {{-- Évolution par année --}}
        <div class="card">
            <div class="card-header">
                <h4 id="evolutionTitle">Évolution par année - tous les utilisateurs</h4>
            </div>
            <div class="card-body">
                <div id="chartEvolution"></div>
                <p id="evolutionEmpty" class="text-muted text-center my-4" style="display:none">
                    Aucun solde enregistré pour cet utilisateur.
                </p>
            </div>
        </div>

        {{-- Tableau détaillé --}}
        <div class="card">
            <div class="card-header"><h4>Détail - {{ $anneeLabel }}{{ $toutes ? ' (cumul)' : '' }}</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableSoldes" class="table table-striped table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>Collaborateur</th>
                                @if($toutes)<th>Période</th>@endif
                                <th>Acquis</th>
                                <th>Reportés</th>
                                <th>Consommés</th>
                                <th>Restants</th>
                                <th>Consommation</th>
                                <th>Export</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($soldes as $s)
                                @php
                                    $dispo = $s->jours_acquis + ($s->jours_reportes ?? 0);
                                    $taux  = $dispo > 0 ? round($s->jours_pris / $dispo * 100) : 0;
                                @endphp
                                <tr data-user="{{ $s->user_id }}">
                                    <td>{{ $s->user->prenom }} {{ $s->user->nom }}</td>
                                    @if($toutes)<td>{{ $s->annee }}</td>@endif
                                    <td data-order="{{ $s->jours_acquis }}">{{ $fmt($s->jours_acquis) }}</td>
                                    <td data-order="{{ $s->jours_reportes ?? 0 }}">{{ $fmt($s->jours_reportes ?? 0) }}</td>
                                    <td class="text-danger" data-order="{{ $s->jours_pris }}">{{ $fmt($s->jours_pris) }}</td>
                                    <td class="font-weight-bold text-success" data-order="{{ $s->jours_restants }}">{{ $fmt($s->jours_restants) }}</td>
                                    <td style="min-width:120px" data-order="{{ $taux }}">
                                        <div class="progress" style="height:16px" title="{{ $taux }} %">
                                            <div class="progress-bar bg-danger" style="width: {{ min($taux, 100) }}%">{{ $taux }} %</div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <a class="btn btn-sm btn-outline-danger" title="PDF (historique toutes années)"
                                           href="{{ route('soldes-rapport.export', ['format' => 'pdf', 'user_id' => $s->user_id]) }}">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a class="btn btn-sm btn-outline-success" title="Excel (historique toutes années)"
                                           href="{{ route('soldes-rapport.export', ['format' => 'excel', 'user_id' => $s->user_id]) }}">
                                            <i class="fas fa-file-excel"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{-- Le layout ne charge ApexCharts que sur le dashboard : on le charge ici --}}
<script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts non chargé');
        return;
    }
    // Texte lisible en thème sombre comme en clair
    const dark = !document.body.classList.contains('light');
    Apex.chart = { foreColor: dark ? '#e3e6ea' : '#373d3f', background: 'transparent' };
    Apex.grid = { borderColor: dark ? '#3a3f4b' : '#e0e0e0' };
    Apex.tooltip = { theme: dark ? 'dark' : 'light' };

    const annee       = @json($annee);
    const anneeLabel  = @json((string) $anneeLabel);
    const soldes      = @json($chartSoldes);
    const totauxAll   = @json($totaux);
    const evolution   = @json($evolution);
    const historiques = @json($historiques);
    const exportUrl   = @json(route('soldes-rapport.export', ['format' => '__F__']));
    const COLORS = { pris: '#dc3545', restants: '#2f9e44', acquis: '#2E75B6', reportes: '#6f42c1' };
    const fmt = v => (Math.round(v * 100) / 100).toLocaleString('fr-FR');

    // ── 1. Graphique par utilisateur (barres horizontales empilées) ──
    let usersChart = null;
    if (soldes.length && document.getElementById('chartUsers')) {
        usersChart = new ApexCharts(document.getElementById('chartUsers'), {
            chart: { type: 'bar', stacked: true, height: Math.max(320, soldes.length * 32), toolbar: { show: true } },
            plotOptions: { bar: { horizontal: true, barHeight: '70%' } },
            series: [],
            xaxis: { categories: [], title: { text: 'Jours' } },
            colors: [COLORS.pris, COLORS.restants],
            dataLabels: { enabled: true, formatter: v => v ? v : '' },
            legend: { position: 'top' },
            tooltip: { y: { formatter: v => v + ' j' } },
        });
        usersChart.render();
    }

    function majUsers(rows) {
        if (!usersChart) return;
        usersChart.updateOptions({
            chart: { height: Math.max(rows.length > 1 ? 320 : 160, rows.length * 32) },
            xaxis: { categories: rows.map(s => s.nom) },
        });
        usersChart.updateSeries([
            { name: 'Jours consommés', data: rows.map(s => s.pris) },
            { name: 'Jours restants',  data: rows.map(s => s.restants) },
        ]);
    }

    // ── 2. Évolution par année (barres verticales groupées) ──
    const evoChart = new ApexCharts(document.getElementById('chartEvolution'), {
        chart: { type: 'bar', height: 340, toolbar: { show: true } },
        plotOptions: { bar: { columnWidth: '60%' } },
        series: [],
        xaxis: { categories: [] },
        colors: [COLORS.acquis, COLORS.reportes, COLORS.pris, COLORS.restants],
        dataLabels: { enabled: false },
        legend: { position: 'top' },
        tooltip: { y: { formatter: v => v + ' j' } },
        noData: { text: 'Aucune donnée' },
    });
    evoChart.render();

    function majEvolution(rows) {
        const vide = !rows.length;
        document.getElementById('evolutionEmpty').style.display = vide ? 'block' : 'none';
        document.getElementById('chartEvolution').style.display = vide ? 'none' : 'block';
        evoChart.updateOptions({ xaxis: { categories: rows.map(r => String(r.annee)) } });
        evoChart.updateSeries([
            { name: 'Acquis',    data: rows.map(r => +r.acquis) },
            { name: 'Reportés',  data: rows.map(r => +r.reportes) },
            { name: 'Consommés', data: rows.map(r => +r.pris) },
            { name: 'Restants',  data: rows.map(r => +r.restants) },
        ]);
    }

    function majTotaux(t) {
        document.getElementById('totAcquis').textContent   = fmt(t.acquis);
        document.getElementById('totReportes').textContent = fmt(t.reportes);
        document.getElementById('totPris').textContent     = fmt(t.pris);
        document.getElementById('totRestants').textContent = fmt(t.restants);
    }

    // ── DataTable du détail (filtrée par l'utilisateur sélectionné) ──
    let filtreUser = '';
    $.fn.dataTable.ext.search.push(function (settings, data, index) {
        if (settings.nTable.id !== 'tableSoldes' || !filtreUser) return true;
        return settings.aoData[index].nTr.dataset.user === filtreUser;
    });
    const table = $('#tableSoldes').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Tous']],
        order: [[0, 'asc']],
        columnDefs: [{ targets: -1, orderable: false, searchable: false }],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
            emptyTable: 'Aucun solde pour cette période.',
            zeroRecords: 'Aucun solde pour cet utilisateur sur cette période.',
        },
    });

    // ── Select2 sur le choix d'utilisateur (recherche par nom) ──
    const select = document.getElementById('userSelect');
    $(select).select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: '- Tous les utilisateurs -',
        allowClear: true,
        language: { noResults: () => 'Aucun utilisateur trouvé' },
    });

    // ── Sélection utilisateur : tout se met à jour sans rechargement ──
    function majSelection() {
        const userId = select.value;
        const nom    = select.options[select.selectedIndex].text;
        const params = new URLSearchParams({ annee });
        if (userId) params.set('user_id', userId);

        document.getElementById('btnPdf').href   = exportUrl.replace('__F__', 'pdf')   + '?' + params;
        document.getElementById('btnExcel').href = exportUrl.replace('__F__', 'excel') + '?' + params;
        document.getElementById('exportLabel').textContent = userId
            ? 'Export : ' + nom + ' - historique toutes années'
            : 'Export : tous les utilisateurs - ' + anneeLabel;
        document.getElementById('evolutionTitle').textContent = 'Évolution par année - ' + (userId ? nom : 'tous les utilisateurs');

        // Graphique utilisateurs + tableau + totaux filtrés
        const rows = userId ? soldes.filter(s => String(s.user_id) === userId) : soldes;
        majUsers(rows);

        filtreUser = userId;
        table.draw();

        if (userId) {
            const r = soldes.find(s => String(s.user_id) === userId);
            majTotaux(r ? r : { acquis: 0, reportes: 0, pris: 0, restants: 0 });
        } else {
            majTotaux(totauxAll);
        }

        majEvolution(userId ? (historiques[userId] || []) : evolution);
    }
    $(select).on('change', majSelection);   // Select2 déclenche l'événement via jQuery
    majSelection();
});
</script>
@endpush
