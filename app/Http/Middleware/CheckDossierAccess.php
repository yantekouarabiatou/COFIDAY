<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Dossier;
use Illuminate\Http\Request;

class CheckDossierAccess
{
    public function handle(Request $request, Closure $next)
    {
        $dossierId = $request->route('dossier') ??
                     $request->route('dossier_id') ??
                     $request->input('dossier_id');

        if ($dossierId) {
            $dossier = Dossier::find($dossierId);

            if (!$dossier) {
                abort(404, 'Dossier non trouvé.');
            }

            if (!$dossier->userCanAccess(auth()->id())) {
                abort(403, 'Accès non autorisé à ce dossier.');
            }
        }

        return $next($request);
    }
}
