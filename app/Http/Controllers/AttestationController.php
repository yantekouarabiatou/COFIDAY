<?php

namespace App\Http\Controllers;

use App\Mail\AttestationApprouvéeMail;
use App\Mail\AttestationAutreNotifRHMail;
use App\Mail\AttestationRefuseeMail;
use App\Mail\AttestationSoumiseMail;
use App\Models\DemandeAttestation;
use App\Models\User;
use App\Services\AttestationMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RealRashid\SweetAlert\Facades\Alert;

class AttestationController extends Controller
{
    // ── Liste des demandes ────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user    = Auth::user();
        $isAdmin = $user->hasAnyRole(['admin', 'rh', 'directeur-general']);

        $query = DemandeAttestation::with('user', 'validateur')->orderByDesc('created_at');

        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($isAdmin && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $demandes      = $query->paginate(15)->withQueryString();
        $totalDemandes = $isAdmin
            ? DemandeAttestation::count()
            : DemandeAttestation::where('user_id', $user->id)->count();
        $enAttente     = $isAdmin
            ? DemandeAttestation::enAttente()->count()
            : DemandeAttestation::enAttente()->where('user_id', $user->id)->count();
        $approuvees    = $isAdmin
            ? DemandeAttestation::approuve()->count()
            : DemandeAttestation::approuve()->where('user_id', $user->id)->count();
        $users         = $isAdmin ? User::orderBy('nom')->get() : collect();

        return view('pages.attestations.index', compact(
            'demandes',
            'isAdmin',
            'totalDemandes',
            'enAttente',
            'approuvees',
            'users'
        ));
    }

    // ── Formulaire création ──────────────────────────────────────────────────

    public function create()
    {
        return view('pages.attestations.create', ['user' => Auth::user()]);
    }

    // ── Enregistrement ───────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user = Auth::user();
        if (empty($user->date_embauche)) {
            Alert::error('Erreur', 'Votre profil ne contient pas de date d’embauche. Veuillez la renseigner avant de faire une demande.');
            return back()->withInput();
        }

        $validated = $request->validate([
            'type'            => 'required|in:attestation_simple,attestation_banque,attestation_ambassade,attestation_autre',
            'motif'           => 'required|string|min:20|max:2000',
            'destinataire'    => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,attestation_banque',
                'required_if:type,attestation_ambassade',
            ],
            'inclure_salaire' => 'nullable|boolean',
            'salaire_net'     => 'nullable|numeric|min:0',
            'poste'           => 'required|string|max:255',
        ], [
            'motif.min'                => 'Le motif doit contenir au moins 20 caractères.',
            'destinataire.required_if' => 'Veuillez préciser le destinataire.',
            'date_embauche.required'   => 'Veuillez indiquer votre date d\'embauche.',
            'date_embauche.date'       => 'La date d\'embauche doit être une date valide.',
            'date_embauche.before_or_equal' => 'La date d\'embauche ne peut pas être dans le futur.',
            'poste.required'           => 'Veuillez indiquer votre poste.',
            'poste.max'                => 'Le poste doit contenir au maximum 255 caractères.',
        ]);

        $inclureSalaire = $request->boolean('inclure_salaire');

        if ($inclureSalaire && empty($validated['salaire_net'])) {
            return back()
                ->withErrors(['salaire_net' => 'Veuillez saisir le salaire net si vous souhaitez l’inclure.'])
                ->withInput();
        }

        $demandeAttestation = DemandeAttestation::create([
            'user_id'         => $user->id,
            'type'            => $validated['type'],
            'motif'           => $validated['motif'],
            'destinataire'    => $validated['destinataire'] ?? null,
            'inclure_salaire' => $inclureSalaire,
            'salaire_net'     => $inclureSalaire ? ($validated['salaire_net'] ?? null) : null,
            'date_embauche'   => $user->date_embauche,   // ← récupérée depuis la table users
            'poste'           => $validated['poste'],
            'numero_reference' => DemandeAttestation::genererNumeroReference(),
            'statut'          => 'en_attente',
        ]);

        // Envoyer des emails différenciés à l'employé, à la secrétaire et au Directeur Général
        try {
              AttestationMailService::envoyerConfirmationSoumission($demandeAttestation);
        } catch (\Exception $e) {
            // Log l'erreur mais ne bloque pas la soumission
            Log::error('Erreur envoi email confirmation attestation: ' . $e->getMessage());
        }

        Alert::success('Succès', 'Votre demande a été transmise au Directeur Général pour validation.');
        return redirect()->route('attestations.index');
    }

    // ── Détail d’une demande ─────────────────────────────────────────────────

    public function show(DemandeAttestation $attestation)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['admin', 'rh', 'directeur-general']) && $attestation->user_id !== $user->id) {
            abort(403);
        }
        // Charge la relation user pour éviter un N+1 dans la vue
        $attestation->load('user', 'validateur');
        return view('pages.attestations.show', compact('attestation'));
    }

    // ── Interface de validation (DG/RH) ──────────────────────────────────────

    public function validationIndex()
    {
        $this->authorizeAdmin();

        $demandes    = DemandeAttestation::with('user')
            ->where('statut', 'en_attente')
            ->orderByDesc('created_at')
            ->paginate(20);
        $nbEnAttente = DemandeAttestation::enAttente()->count();

        return view('pages.attestations.validation', compact('demandes', 'nbEnAttente'));
    }

    // ── Traitement de la demande (approbation / refus) ───────────────────────

    public function traiter(Request $request, DemandeAttestation $attestation)
    {
        $this->authorizeAdmin();

        if ($attestation->statut !== 'en_attente') {
            Alert::warning('Information', 'Cette demande a déjà été traitée.');
            return back();
        }

        $request->validate([
            'action'      => 'required|in:approuve,refuse',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $action    = $request->action;
            $dgUser    = Auth::user();
            $numeroRef = ($action === 'approuve') ? DemandeAttestation::genererNumeroReference() : null;

            $attestation->update([
                'statut'           => $action,
                'valide_par'       => $dgUser->id,
                'date_validation'  => now(),
                'commentaire_dg'   => $request->commentaire,
                'numero_reference' => $numeroRef,
            ]);

            $attestation->load('user');

            if (! $attestation->user) {
                throw new \Exception("L'utilisateur associé à la demande #{$attestation->id} est introuvable.");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur enregistrement décision attestation', [
                'attestation_id' => $attestation->id,
                'error'          => $e->getMessage(),
            ]);
            Alert::error('Erreur', 'Impossible d\'enregistrer la décision : ' . $e->getMessage());
            return back();
        }

        // Envoi des emails (après commit)
        try {
            $employeEmail = $attestation->user->email;

            // Récupération des emails depuis la config (supports tableau)
            $secretaireEmails = config('cofima.email_secretaire');
            $secretaireEmails = is_array($secretaireEmails) ? $secretaireEmails : [$secretaireEmails];
            $secretaireEmails = array_filter($secretaireEmails);

            $rhEmail = config('cofima.email_rh'); // chaîne ou null

            if ($action === 'approuve') {
                // Liste des destinataires en copie (secrétaires uniquement)
                $cc = array_filter($secretaireEmails, fn($e) => $e !== $employeEmail);

                if ($attestation->type === 'attestation_autre') {
                    // Notification au RH si configuré
                    if ($rhEmail && $rhEmail !== $employeEmail && !in_array($rhEmail, $cc)) {
                        Mail::to($rhEmail)->send(new AttestationAutreNotifRHMail($attestation));
                    }
                    // Envoi à l'employé avec copie aux secrétaires
                    Mail::to($employeEmail)->cc($cc)->send(new AttestationApprouvéeMail($attestation, true));
                } else {
                    Mail::to($employeEmail)->cc($cc)->send(new AttestationApprouvéeMail($attestation, false));
                }
            } else { // refus
                Mail::to($employeEmail)->send(new AttestationRefuseeMail($attestation, $request->commentaire));
            }

            Alert::success('Succès', $action === 'approuve'
                ? 'Demande approuvée. Notifications envoyées.'
                : 'Demande refusée. L’employé a été notifié.');
        } catch (\Exception $e) {
            Log::error('Erreur envoi email traitement attestation', [
                'attestation_id' => $attestation->id,
                'action'         => $action,
                'error'          => $e->getMessage(),
            ]);
            Alert::warning('Attention', 'La décision a été enregistrée mais l\'envoi des emails a échoué.');
        }

        return redirect()->route('attestations.validation.index');
    }

    // ── Annulation par l’employé ─────────────────────────────────────────────

    public function annuler(DemandeAttestation $attestation)
    {
        $user = Auth::user();

        if ($attestation->user_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403);
        }

        if ($attestation->statut !== 'en_attente') {
            Alert::warning('Attention', 'Seules les demandes en attente peuvent être annulées.');
            return back();
        }

        $attestation->delete();
        Alert::success('Succès', 'Votre demande a été annulée.');
        return redirect()->route('attestations.index');
    }

    private function getSecretaireEtDgEmails(): array
    {
        return [
            'secretaire' => config('cofima.email_secretaire', 'cofima@cofima.cc'),
            'dg' => $this->getEmailByRoleOrPoste(
                'directeur-general',
                ['DIRECTEUR GENERAL', 'DIRECTEUR GÉNÉRAL', 'DIRECTEUR GENERALE', 'DG'],
                'cofima.email_dg'
            ),
        ];
    }

    private function getEmailByRoleOrPoste(string $role, array $posteTitles, string $configKey, array $excludeEmails = []): ?string
    {
        $email = User::role($role)
            ->when(!empty($excludeEmails), fn($q) => $q->whereNotIn('email', $excludeEmails))
            ->value('email');

        if (!$email) {
            $email = User::whereHas('poste', function ($query) use ($posteTitles) {
                $query->whereIn('intitule', $posteTitles);
            })
                ->when(!empty($excludeEmails), fn($q) => $q->whereNotIn('email', $excludeEmails))
                ->value('email');
        }

        return $email ?: config($configKey);
    }

    private function getRhEmail(): ?string
    {
        $emailRh = User::role('rh')->value('email');

        if (! $emailRh) {
            $emailRh = User::whereHas('poste', function ($query) {
                $query->whereIn('intitule', [
                    'RH',
                    'Ressources Humaines',
                    'Responsable RH',
                    'Responsable Ressources Humaines',
                ]);
            })->value('email');
        }

        return $emailRh ?: config('cofima.email_rh');
    }

    // ── Helper d’autorisation ────────────────────────────────────────────────

    private function authorizeAdmin(): void
    {
        if (! Auth::user()->hasAnyRole(['admin', 'rh', 'directeur-general'])) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
