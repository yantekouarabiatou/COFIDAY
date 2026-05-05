<?php

namespace App\Http\Controllers;

use App\Mail\CertificatTravailMail;
use App\Mail\DemissionRefuseeMail;
use App\Models\DemandeDemission;
use App\Models\User;
use App\Services\DemissionMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RealRashid\SweetAlert\Facades\Alert;

class DemissionController extends Controller
{
    // ── Liste des demandes ──────────────────────────────────────────────────

    public function index()
    {
        $user    = Auth::user();
        $isAdmin = $user->hasAnyRole(['admin', 'rh', 'directeur-general']);

        $query = DemandeDemission::with('user', 'validateur')->orderByDesc('created_at');
        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        $demandes   = $query->paginate(15);
        $enAttente  = $isAdmin
            ? DemandeDemission::enAttente()->count()
            : DemandeDemission::enAttente()->where('user_id', $user->id)->count();
        $approuvees = $isAdmin
            ? DemandeDemission::approuve()->count()
            : DemandeDemission::approuve()->where('user_id', $user->id)->count();

        return view('pages.certificats.index', compact('demandes', 'isAdmin', 'enAttente', 'approuvees'));
    }

    // ── Formulaire de création ──────────────────────────────────────────────

    public function create()
    {
        $user = Auth::user();

        $demissionActive = DemandeDemission::where('user_id', $user->id)
            ->where('statut', 'en_attente')
            ->exists();

        return view('pages.certificats.create', compact('user', 'demissionActive'));
    }

    // ── Enregistrement de la demande ─────────────────────────────────────────

    public function store(Request $request)
    {
        $user = Auth::user();
        if (empty($user->date_embauche)) {
            Alert::error('Erreur', 'Votre profil ne contient pas de date d’embauche. Veuillez la renseigner avant de faire une demande de démission.');
            return back()->withInput();
        }

        $demissionActive = DemandeDemission::where('user_id', $user->id)
            ->where('statut', 'en_attente')
            ->exists();

        if ($demissionActive) {
            Alert::warning('Attention', 'Vous avez déjà une demande de démission en cours de traitement.');
            return back();
        }

        $request->validate([
            'date_depart_souhaitee' => 'required|date|after:today',
            'lettre'                => 'required|string|min:50|max:5000',
        ], [
            'date_depart_souhaitee.after' => 'La date de départ doit être postérieure à aujourd\'hui.',
            'date_embauche.before_or_equal' => 'La date d\'embauche ne peut pas être une date future.',
            'lettre.min'                  => 'La lettre de démission doit contenir au moins 50 caractères.',
        ]);

        $demande = DemandeDemission::create([
            'user_id'               => $user->id,
            'date_depart_souhaitee' => $request->date_depart_souhaitee,
            'date_embauche' => $user->date_embauche,
            'lettre'                => $request->lettre,
            'numero_reference'      => DemandeDemission::genererNumeroReference(),
            'statut'                => 'en_attente',
        ]);

        try {
            DemissionMailService::envoyerConfirmationSoumission($demande);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du mail de soumission de démission', [
                'demande_id' => $demande->id,
                'error'      => $e->getMessage(),
            ]);
            Alert::warning('Attention', 'Votre lettre a bien été enregistrée, mais l’envoi des emails a rencontré un problème.');
        }

        Alert::success('Succès', 'Votre lettre de démission a été transmise à la Direction Générale.');
        return redirect()->route('demissions.index');
    }

    // ── Détail d’une demande ────────────────────────────────────────────────

    public function show(DemandeDemission $demission)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['admin', 'rh', 'directeur-general']) && $demission->user_id !== $user->id) {
            abort(403);
        }
        return view('pages.certificats.show', compact('demission'));
    }

    // ── Interface de validation (DG / RH) ────────────────────────────────────

    public function validationIndex()
    {
        $this->authorizeAdmin();

        $demandes    = DemandeDemission::with('user')
            ->where('statut', 'en_attente')
            ->orderByDesc('created_at')
            ->paginate(20);
        $nbEnAttente = DemandeDemission::enAttente()->count();

        return view('pages.certificats.validation', compact('demandes', 'nbEnAttente'));
    }

    // ── Traitement de la demande (acceptation / refus) ───────────────────────

    public function traiter(Request $request, DemandeDemission $demission)
    {
        $this->authorizeAdmin();

        if ($demission->statut !== 'en_attente') {
            Alert::warning('Information', 'Cette demande a déjà été traitée.');
            return back();
        }

        $request->validate([
            'action'                => 'required|in:acceptee,refusee',
            'commentaire'           => 'nullable|string|max:1000',
            'date_depart_confirmee' => 'nullable|date|required_if:action,acceptee',
        ]);

        $action = $request->action;
        $dgUser = Auth::user();

        // 1. Enregistrement de la décision (transaction)
        DB::beginTransaction();
        try {
            $demission->update([
                'statut'          => $action,
                'valide_par'      => $dgUser->id,
                'date_validation' => now(),
                'commentaire_dg'  => $request->commentaire,
            ]);

            if ($action === 'acceptee') {
                $numeroCertificat = DemandeDemission::genererNumeroCertificat();
                $demission->update([
                    'numero_certificat'           => $numeroCertificat,
                    'certificat_genere'            => true,
                    'date_generation_certificat'   => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'enregistrement de la décision de démission', [
                'demande_id' => $demission->id,
                'error'      => $e->getMessage(),
            ]);
            Alert::error('Erreur', 'Impossible d\'enregistrer la décision : ' . $e->getMessage());
            return back();
        }

        // 2. Envoi des emails (après commit, pour ne pas annuler la décision en cas d'échec)
        try {
            $secretaire = config('cofima.email_secretaire', 'cofima@cofima.cc');
            $rh         = $this->getRhEmail();
            $dg         = $this->getDgEmail();
            $ccList     = array_unique(array_filter([$secretaire, $rh, $dg]));

            if ($action === 'acceptee') {
                Mail::to($demission->user->email)
                    ->cc($ccList)
                    ->send(new CertificatTravailMail($demission, $request->date_depart_confirmee));
                Alert::success('Succès', 'Démission acceptée. Le certificat de travail a été généré et envoyé par mail.');
            } else {
                Mail::to($demission->user->email)
                    ->cc($ccList)
                    ->send(new DemissionRefuseeMail($demission, $request->commentaire));
                Alert::success('Succès', 'Démission refusée. L’employé a été notifié.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email de traitement de démission', [
                'demande_id' => $demission->id,
                'action'     => $action,
                'error'      => $e->getMessage(),
            ]);
            Alert::warning('Attention', 'La décision a été enregistrée mais l\'envoi de l\'email a échoué. Veuillez contacter le support.');
        }

        return redirect()->route('demissions.validation.index');
    }

    // ── Helpers pour la récupération des emails (RH et DG uniquement) ────────

    private function getRhEmail(): ?string
    {
        $email = User::role('rh')->value('email');
        if (! $email) {
            $email = User::whereHas('poste', function ($query) {
                $query->whereIn('intitule', ['RH', 'Ressources Humaines', 'Responsable RH', 'Responsable Ressources Humaines']);
            })->value('email');
        }
        return $email ?: config('cofima.email_rh');
    }

    private function getDgEmail(): ?string
    {
        $email = User::role('directeur-general')->value('email');
        if (! $email) {
            $email = User::whereHas('poste', function ($query) {
                $query->whereIn('intitule', ['DIRECTEUR GENERAL', 'DIRECTEUR GÉNÉRAL', 'DIRECTEUR GENERALE', 'DG']);
            })->value('email');
        }
        return $email ?: config('cofima.email_dg');
    }

    private function authorizeAdmin(): void
    {
        if (! Auth::user()->hasAnyRole(['admin', 'rh', 'directeur-general'])) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
