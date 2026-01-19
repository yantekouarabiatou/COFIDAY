<?php

namespace App\DataTables;

use App\Models\Dossier;
use Illuminate\Support\Str;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;

class DossiersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', 'pages.dossiers.partials.actions')
            ->addColumn('en_retard', fn($dossier) => $dossier->en_retard ? '1' : '0') // Pour le filtre
            ->editColumn('nom', fn($dossier) => '<a href="' . route('dossiers.show', $dossier) . '">' . Str::limit($dossier->nom, 30) . '</a>')
            ->editColumn('client.nom', fn($dossier) => '<a href="' . route('clients.show', $dossier->client) . '">' . Str::limit($dossier->client->nom, 25) . '</a>')
            ->editColumn('type_dossier_badge', fn($dossier) => $dossier->type_dossier_badge)
            ->editColumn('statut_badge', fn($dossier) => $dossier->statut_badge)
            ->editColumn('budget', fn($dossier) => $dossier->budget ? '<span class="badge badge-light">' . $dossier->budget_formate . '</span>' : '-')
            ->editColumn('date_ouverture', fn($dossier) => $dossier->date_ouverture->format('d/m/Y'))
            ->editColumn('reference', function ($dossier) {
                $badge = $dossier->en_retard ? '<span class="badge badge-danger ml-2"><i class="fas fa-exclamation-triangle"></i></span>' : '';
                return '<strong>' . $dossier->reference . '</strong>' . $badge;
            })
            ->rawColumns(['nom', 'client.nom', 'type_dossier_badge', 'statut_badge', 'budget', 'action', 'reference']);
    }

    /**
     * Get the query source of dataTable.
     */
     public function query(Dossier $model)
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'super-admin', 'rh', 'manager', 'directeur-general'])) {
            return $model->newQuery()->with('client');
        } else {
            return $model->newQuery()
                ->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('collaborateurs', function($subq) use ($user) {
                          $subq->where('user_id', $user->id)->where('is_active', true);
                      });
                })
                ->with('client');
        }
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('dossiers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'language' => [
                    'url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
                ],
            ]);
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            Column::make('reference')->title('Référence'),
            Column::make('nom')->title('Nom'),
            Column::make('client.nom')->title('Client')->name('client.nom')->orderable(false),
            Column::make('type_dossier')->title('Type')->name('type_dossier'),
            Column::make('statut')->title('Statut')->name('statut'),
            Column::make('date_ouverture')->title('Dates'),
            Column::make('budget')->title('Budget')->orderable(false),
            Column::make('en_retard')->name('en_retard')->visible(false)->searchable(true),
            Column::computed('action')->title('Actions')->exportable(false)->printable(false)->addClass('text-center'),
        ];
    }
}
