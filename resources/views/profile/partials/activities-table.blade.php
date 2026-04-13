{{-- resources/views/profile/partials/activities-table.blade.php --}}
@if($items->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3"></i>
        <p>Aucune activité</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Date</th>
                    <th>Élément</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items->take(10) as $item)
                <tr>
                    <td class="small text-muted">
                        {{ $item->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        @switch($type)
                            @case('plainte')
                                <strong>{{ $item->Reference ?? $item->nom_client }}</strong>
                                @break
                            @case('audit')
                                {{ $item->nom_client }}
                                @break
                            @case('cadeau')
                                {{ $item->nom }}
                                @break
                            @case('interet')
                                {{ $item->nom ?? 'Conflit d\'intérêt' }}
                                @break
                            @case('independance')
                                {{ $item->nom_client }}
                                @break
                        @endswitch
                    </td>
                    <td>
                        @if(method_exists($item, 'etat_plainte') && $item->etat_plainte)
                            <span class="badge badge-{{ $item->etat_plainte == 'Résolue' ? 'success' : ($item->etat_plainte == 'En cours' ? 'warning' : 'secondary') }}">
                                {{ $item->etat_plainte }}
                            </span>
                        @elseif(isset($item->action_prise))
                            <span class="badge badge-{{ $item->action_prise == 'accepté' ? 'success' : 'danger' }}">
                                {{ ucfirst($item->action_prise) }}
                            </span>
                        @else
                            <span class="badge badge-info">Actif</span>
                        @endif
                    </td>
                    <td>
                        <a href="#"
                           class="btn btn-sm btn-outline-primary">
                            Voir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
