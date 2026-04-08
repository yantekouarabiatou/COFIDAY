{{--
    ══════════════════════════════════════════════════════════════════════════
    MENU SIDEBAR — Attestations & Certificats   (v2 — séparation claire)
    À insérer après le bloc "Gestion des Congés" dans votre sidebar
    ══════════════════════════════════════════════════════════════════════════
--}}

{{-- ── Attestations de travail ──────────────────────────────────────────── --}}



{{--
    ══════════════════════════════════════════════════════════════════════════
    PERMISSIONS SPATIE — à ajouter dans votre PermissionSeeder
    ══════════════════════════════════════════════════════════════════════════

    // Attestations
    Permission::firstOrCreate(['name' => 'créer des demandes d attestation']);
    Permission::firstOrCreate(['name' => 'voir les demandes d attestation']);
    Permission::firstOrCreate(['name' => 'valider les demandes d attestation']);

    // Démissions
    Permission::firstOrCreate(['name' => 'soumettre une démission']);
    Permission::firstOrCreate(['name' => 'voir les démissions']);
    Permission::firstOrCreate(['name' => 'valider les démissions']);

    // Employé
    Role::findByName('employe')->givePermissionTo([
        'créer des demandes d attestation',
        'voir les demandes d attestation',
        'soumettre une démission',
        'voir les démissions',
    ]);

    // Directeur Général
    Role::findByName('directeur-general')->givePermissionTo([
        'créer des demandes d attestation',
        'voir les demandes d attestation',
        'valider les demandes d attestation',
        'voir les démissions',
        'valider les démissions',
    ]);

    // RH
    Role::findByName('rh')->givePermissionTo([
        'voir les demandes d attestation',
        'valider les demandes d attestation',
        'voir les démissions',
        'valider les démissions',
    ]);

    // Admin
    Role::findByName('admin')->givePermissionTo([
        'créer des demandes d attestation',
        'voir les demandes d attestation',
        'valider les demandes d attestation',
        'soumettre une démission',
        'voir les démissions',
        'valider les démissions',
    ]);
--}}
