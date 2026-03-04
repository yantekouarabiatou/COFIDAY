<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    /**
     * Affiche les paramètres de l'entreprise.
     * @return \Illuminate\View\View
     */
    public function show()
    {
        // Récupère l'unique ligne de paramètres (ou crée un nouvel objet vide)
        $setting = CompanySetting::firstOrNew(['id' => 1]);

        return view('company_settings.show', compact('setting'));
    }

    /**
     * Affiche le formulaire de modification des paramètres.
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // Récupère l'unique ligne de paramètres (ou crée un nouvel objet vide)
        $setting = CompanySetting::firstOrNew(['id' => 1]);

        return view('company_settings.edit', compact('setting'));
    }

    /**
     * Met à jour les paramètres de l'entreprise.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanySetting  $companySetting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CompanySetting $setting)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'slogan'       => 'nullable|string|max:255',
            'email'        => 'required|email|max:255',
            'telephone'    => 'nullable|string|max:50',
            'adresse'      => 'nullable|string|max:255',
            'ville'        => 'nullable|string|max:100',
            'pays'         => 'nullable|string|max:100',
            'site_web'     => 'nullable|url|max:255',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'guide'        => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        $data = $request->except(['logo', 'guide']);

        // Gestion du logo
        if ($request->hasFile('logo')) {
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('company/logos', 'public');
        }

        // Gestion du guide
        if ($request->hasFile('guide')) {
            if ($setting->guide && Storage::disk('public')->exists($setting->guide)) {
                Storage::disk('public')->delete($setting->guide);
            }
            $data['guide'] = $request->file('guide')->store('company/guides', 'public');
        }

        $setting->update($data);

        return redirect()->route('settings.show')
            ->with('success', 'Les paramètres de l\'entreprise ont été mis à jour avec succès.');
    }
/**
 * Visualiser le guide d'utilisation dans le navigateur.
 */
public function viewGuide()
{
    $setting = CompanySetting::firstOrFail();

    if (!$setting->guide || !Storage::disk('public')->exists($setting->guide)) {
        abort(404, 'Guide introuvable.');
    }

    $extension = strtolower(pathinfo($setting->guide, PATHINFO_EXTENSION));
    $path      = Storage::disk('public')->path($setting->guide);
    $fileName  = basename($setting->guide);

    // Les .doc/.docx ne peuvent pas s'ouvrir dans le navigateur
    // On les télécharge directement
    if (in_array($extension, ['doc', 'docx'])) {
        return response()->download($path, $fileName);
    }

    // Pour les PDF : ouverture dans le navigateur
    return response()->file($path, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $fileName . '"',
    ]);
}

/**
 * Télécharger le guide d'utilisation.
 */
public function downloadGuide()
{
    $setting = CompanySetting::firstOrFail();

    if (!$setting->guide || !Storage::disk('public')->exists($setting->guide)) {
        abort(404, 'Guide introuvable.');
    }

    $extension = strtolower(pathinfo($setting->guide, PATHINFO_EXTENSION));
    $path      = Storage::disk('public')->path($setting->guide);
    $fileName  = 'Guide_Utilisation_' . now()->format('Ymd') . '.' . $extension;

    return response()->download($path, $fileName);
}
}
