<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\User;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveRequestMail;

class CongeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $user = Auth::user();

        $conges = Conge::with('user')
            ->when(!$user->hasRole('admin'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        return view('pages.conges.index', compact('conges'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('pages.conges.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     try {
    //         // 1. Validation
    //         $request->validate([
    //             'type_conge' => 'required|in:MALADIE,MATERNITE,REMUNERE,NON REMUNERE',
    //             'date_debut' => 'required|date',
    //             'date_fin'   => 'required|date|after_or_equal:date_debut',
    //         ], [
    //             'type_conge.required' => 'Le type de congé est obligatoire.',
    //             'type_conge.in' => 'Type de congé invalide.',
    //             'date_debut.required' => 'La date de début est obligatoire.',
    //             'date_fin.required' => 'La date de fin est obligatoire.',
    //             'date_fin.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
    //         ]);

    //         // 2. Création du congé
    //         Conge::create([
    //             'type_conge' => $request->type_conge,
    //             'date_debut' => $request->date_debut,
    //             'date_fin'   => $request->date_fin,
    //             'user_id'    => Auth::id(),
    //         ]);

    //         // 3. SweetAlert succès
    //         Alert::success('Succès', 'Votre congé a été enregistrée avec succès.');

    //         return redirect()->route('conges.index');

    //     } catch (Exception $e) {

    //         // SweetAlert erreur
    //         Alert::error('Erreur', 'Une erreur est survenue lors de l’enregistrement du congé.');

    //         return back()->withInput();
    //     }
    // }

    public function store()
    {
        /*
        |----------------------------------------------------
        | 1. Données statiques (simulation)
        |----------------------------------------------------
        */

        $employee = User::first(); // Employé fictif
        //$validator = User::where('role', 'manager')->first(); // Valideur fictif

        // Faux objet LeaveRequest (non sauvegardé)
        $leave = new Conge();
        $leave->id = 1;
        $leave->user = $employee;
        $leave->date_debut = '2026-01-20';
        $leave->date_fin = '2026-01-25';
        $leave->days = 5;
        $leave->reason = 'Congé annuel (test envoi mail)';
        $leave->status = 'pending_manager';

        // Si tu as une relation type
        $leave->type = (object) [
            'name' => 'Congé annuel'
        ];

        /*
        |----------------------------------------------------
        | 2. Génération du PDF
        |----------------------------------------------------
        */

        $pdf = Pdf::loadView('pdfs.leave_request', [
            'leave' => $leave,
        ]);

        $pdfPath = storage_path('app/temp/test_leave_request.pdf');

        // Créer le dossier s'il n'existe pas
        if (!is_dir(dirname($pdfPath))) {
            mkdir(dirname($pdfPath), 0755, true);
        }

        $pdf->save($pdfPath);

        /*
        |----------------------------------------------------
        | 3. Envoi du mail
        |----------------------------------------------------
        */
        $validator = 'adisiroko@gmail.com';

        if (!file_exists($pdfPath)) {
            dd('PDF non généré', $pdfPath);
        }

        Mail::to($validator)->send(new LeaveRequestMail($leave, $pdfPath));

        /*
        |----------------------------------------------------
        | 4. Réponse simple
        |----------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Email envoyé avec succès (PDF joint)',
            'sent_to' => $validator,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conge $conge)
    {
        // Sécurité : seul le propriétaire peut voir son congé
        if ($conge->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        // Charger la relation user (utile plus tard)
        $conge->load('user');

        return view('pages.conges.show', compact('conge'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conge $conge)
    {
        // Sécurité : seul le propriétaire peut modifier son congé
        if ($conge->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        return view('pages.conges.edit', compact('conge'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conge $conge)
    {
        // Sécurité
        if ($conge->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        try {
            // Validation
            $request->validate([
                'type_conge' => 'required|in:MALADIE,MATERNITE,REMUNERE,NON REMUNERE',
                'date_debut' => 'required|date',
                'date_fin'   => 'required|date|after_or_equal:date_debut',
            ], [
                'type_conge.required' => 'Le type de congé est obligatoire.',
                'type_conge.in' => 'Type de congé invalide.',
                'date_debut.required' => 'La date de début est obligatoire.',
                'date_fin.required' => 'La date de fin est obligatoire.',
                'date_fin.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
            ]);

            // Mise à jour
            $conge->update([
                'type_conge' => $request->type_conge,
                'date_debut' => $request->date_debut,
                'date_fin'   => $request->date_fin,
            ]);

            // SweetAlert succès
            Alert::success('Succès', 'Le congé a été modifié avec succès.');

            return redirect()->route('conges.index');

        } catch (Exception $e) {

            // SweetAlert erreur
            Alert::error('Erreur', 'Une erreur est survenue lors de la modification du congé.');

            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conge $conge)
    {
        try {
            // Sécurité : admin OU propriétaire
            if (!Auth::user()->hasRole('admin') && $conge->user_id !== Auth::id()) {
                abort(403, 'Accès non autorisé');
            }

            $conge->delete();

            Alert::success('Succès', 'Le congé a été supprimé avec succès.');

            return redirect()->route('conges.index');

        } catch (\Exception $e) {

            Alert::error('Erreur', 'Une erreur est survenue lors de la suppression du congé.');

            return back();
        }
    }
}
