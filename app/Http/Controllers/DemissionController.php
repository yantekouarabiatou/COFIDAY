<?php

namespace App\Http\Controllers;

use App\Mail\CertificatTravailMail;
use App\Mail\DemissionAccepteeMail;
use App\Mail\DemissionRefuseeMail;
use App\Mail\DemissionReçueMail;
use App\Mail\DemissionSoumiseMail;
use App\Models\DemandeDemission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RealRashid\SweetAlert\Facades\Alert;

class DemissionController extends Controller
{
    // ── Formulaire de démission ──────────────────────────────────────────────

    public function index()
    {
        $user    = Auth::user();
        $isAdmin = $user->hasAnyRole(['admin', 'rh', 'directeur-general']);

        $query = DemandeDemission::with('user', 'validateur')->orderByDesc('created_at');
        if (!$isAdmin) $query->where('user_id', $user->id);

        $demandes   = $query->paginate(15);
        $enAttente  = $isAdmin
            ? DemandeDemission::enAttente()->count()
            : DemandeDemission::enAttente()->where('user_id', $user->id)->count();
        $approuvees = $isAdmin
            ? DemandeDemission::approuve()->count()
            : DemandeDemission::approuve()->where('user_id', $user->id)->count();

        return view('pages.certificats.index', compact('demandes', 'isAdmin', 'enAttente', 'approuvees'));
    }
    public function create()
    {
        $user = Auth::user();

        // Vérifier si l'employé a déjà une démission en cours
        $demissionActive = DemandeDemission::where('user_id', $user->id)
            ->where('statut', 'en_attente')
            ->exists();

        return view('pages.certificats.create', compact('user', 'demissionActive'));
    }

    // ── Enregistrement de la lettre de démission ─────────────────────────────

    public function store(Request $request)
    {
        $user = Auth::user();

        // Bloquer si une démission est déjà en cours
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
            'lettre.min'                  => 'La lettre de démission doit contenir au moins 50 caractères.',
        ]);

        $demande = DemandeDemission::create([
            'user_id'               => $user->id,
            'date_depart_souhaitee' => $request->date_depart_souhaitee,
            'lettre'                => $request->lettre,
            'numero_reference'      => DemandeDemission::genererNumeroReference(),
            'statut'                => 'en_attente',
        ]);

        try {
            $emailSecretaire = config('cofima.email_secretaire', 'biroko@cofima.cc');
            $emailDG = User::role('directeur-general')->value('email')
                ?? config('cofima.email_dg', 'jmavande@cofima.cc');
            $emailRH = User::role('rh')->value('email')
                ?? config('cofima.email_rh');

            $employeeCc = array_unique(array_filter([$emailSecretaire, $emailRH]));
            Mail::to($user->email)
                ->cc($employeeCc)
                ->send(new DemissionSoumiseMail($demande));

            $dgRecipients = array_unique(array_filter([$emailDG, $emailRH]));
            if (!empty($dgRecipients)) {
                Mail::to($dgRecipients)
                    ->cc($emailSecretaire)
                    ->send(new DemissionReçueMail($demande));
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi email démission : ' . $e->getMessage(), [
                'demande_id' => $demande->id,
            ]);
            Alert::warning('Attention', 'Votre lettre de démission a bien été enregistrée, mais l’envoi des emails a rencontré un problème.');
            return redirect()->route('demissions.index');
        }

        Alert::success('Succès', 'Votre lettre de démission a été transmise à la Direction Générale.');
        return redirect()->route('demissions.index');
    }

    // ── Détail ───────────────────────────────────────────────────────────────

    public function show(DemandeDemission $demission)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'rh', 'directeur-general']) && $demission->user_id !== $user->id) {
            abort(403);
        }
        return view('pages.certificats.show', compact('demission'));
    }

    // ── Interface validation DG ──────────────────────────────────────────────

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

    // ── Traitement DG ────────────────────────────────────────────────────────

    public function traiter(Request $request, DemandeDemission $demission)
    {
        $this->authorizeAdmin();

        DB::beginTransaction();
        try {
            if ($demission->statut !== 'en_attente') {
                Alert::warning('Information', 'Cette démission a déjà été traitée.');
                return back();
            }

            $request->validate([
                'action'                => 'required|in:acceptee,refusee',
                'commentaire'           => 'nullable|string|max:1000',
                'date_depart_confirmee' => 'nullable|date|required_if:action,acceptee',
            ]);

            $action = $request->action;
            $dgUser = Auth::user();

            $demission->update([
                'statut'          => $action,
                'valide_par'      => $dgUser->id,
                'date_validation' => now(),
                'commentaire_dg'  => $request->commentaire,
            ]);

            if ($action === 'acceptee') {
                // ── Génération automatique du certificat de travail ──────────
                $numeroCertificat = DemandeDemission::genererNumeroCertificat();

                $demission->update([
                    'numero_certificat'           => $numeroCertificat,
                    'certificat_genere'            => true,
                    'date_generation_certificat'   => now(),
                ]);

                $emailSecretaire = config('cofima.email_secretaire', 'biroko@cofima.cc');

                // Mail employé : acceptation + certificat de travail joint
                Mail::to($demission->user->email)
                    ->cc($emailSecretaire)
                    ->send(new CertificatTravailMail($demission, $request->date_depart_confirmee));
            } else {
            }

            DB::commit();
            Alert::success('Succès', $action === 'acceptee'
                ? 'Démission acceptée. Le certificat de travail a été généré et envoyé par mail.'
                : 'Démission refusée. L\'employé a été notifié.');

            return redirect()->route('demissions.validation.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Erreur', 'Une erreur est survenue : ' . $e->getMessage());
            return back();
        }
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    private function authorizeAdmin(): void
    {
        if (!Auth::user()->hasAnyRole(['admin', 'rh', 'directeur-general'])) abort(403);
    }
}
