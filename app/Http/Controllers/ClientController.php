<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Dossier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Afficher la liste des clients
     */
    public function index()
    {
        $clients = Client::withCount('dossiers')->latest()->get();

        return view('pages.clients.index', compact('clients'));
    }

    
    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $client= Client::all();
        return view('pages.clients.create', compact('client'));
    }

    /**
     * Enregistrer un nouveau client
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:clients,nom',
            'email' => 'nullable|email|max:255|unique:clients,email',
            'siege_social' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'contact_principal' => 'nullable|string|max:255',
            'secteur_activite' => 'nullable|string|max:255',
            'numero_siret' => 'nullable|string|max:14|unique:clients,numero_siret',
            // 'code_naf' => 'nullable|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_web' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'statut' => 'required|in:actif,inactif,prospect',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        // Traitement du logo
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clients/logos', 'public');
        }

        // Création du client
        $client = Client::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'siege_social' => $request->siege_social,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'contact_principal' => $request->contact_principal,
            'secteur_activite' => $request->secteur_activite,
            'numero_siret' => $request->numero_siret,
            // 'code_naf' => $request->code_naf,
            'logo' => $logoPath,
            'site_web' => $request->site_web,
            'notes' => $request->notes,
            'statut' => $request->statut,
        ]);

        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès!');
    }

    /**
     * Afficher les détails d'un client
     */
    public function show(Client $client)
    {
        $client->load('dossiers');
        return view('pages.clients.show', compact('client'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Client $client)
    {
        return view('pages.clients.edit', compact('client'));
    }

    /**
     * Mettre à jour un client
     */
    public function update(Request $request, Client $client)
    {
        // Validation avec règles uniques excluant le client actuel
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:clients,nom,' . $client->id,
            'email' => 'nullable|email|max:255|unique:clients,email,' . $client->id,
            'siege_social' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'contact_principal' => 'nullable|string|max:255',
            'secteur_activite' => 'nullable|string|max:255',
            'numero_siret' => 'nullable|string|max:14|unique:clients,numero_siret,' . $client->id,
            // 'code_naf' => 'nullable|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_web' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'statut' => 'required|in:actif,inactif,prospect',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        // Traitement du logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($client->logo && Storage::disk('public')->exists($client->logo)) {
                Storage::disk('public')->delete($client->logo);
            }

            $logoPath = $request->file('logo')->store('clients/logos', 'public');
            $client->logo = $logoPath;
        }

        // Mise à jour du client
        $client->update([
            'nom' => $request->nom,
            'email' => $request->email,
            'siege_social' => $request->siege_social,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'contact_principal' => $request->contact_principal,
            'secteur_activite' => $request->secteur_activite,
            'numero_siret' => $request->numero_siret,
            // 'code_naf' => $request->code_naf,
            'site_web' => $request->site_web,
            'notes' => $request->notes,
            'statut' => $request->statut,
        ]);

        return redirect()->route('clients.index')
            ->with('success', 'Client mis à jour avec succès!');
    }

    /**
     * Supprimer un client
     */
    public function destroy(Client $client)
    {
        if ($client->dossiers()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce client car il possède des dossiers associés.'
            ], 422);
        }

        if ($client->logo && Storage::disk('public')->exists($client->logo)) {
            Storage::disk('public')->delete($client->logo);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client supprimé avec succès.'
        ]);
    }

    /**
     * Télécharger le logo
     */
    public function downloadLogo(Client $client)
    {
        if (!$client->logo || !Storage::disk('public')->exists($client->logo)) {
            return redirect()->back()
                ->with('error', 'Logo non trouvé.');
        }

        return Storage::disk('public')->download($client->logo, 'logo-' . str_slug($client->nom) . '.' . pathinfo($client->logo, PATHINFO_EXTENSION));
    }

    /**
     * Exporter la liste des clients en PDF
     */
    public function exportPdf()
    {
        $clients = Client::withCount('dossiers')->latest()->get();

        $pdf = Pdf::loadView('pages.clients.pdf', compact('clients'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('liste-clients-' . date('Y-m-d') . '.pdf');
    }
}
