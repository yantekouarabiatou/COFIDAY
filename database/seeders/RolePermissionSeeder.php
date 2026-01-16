<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache des permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // =============================================
        // LISTE COMPLÈTE ET ORGANISÉE DES PERMISSIONS
        // =============================================
        $permissions = [

            // ================= UTILISATEURS =================
            ['name' => 'voir les utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'créer des utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'modifier des utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'supprimer des utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'assigner des rôles', 'group' => 'utilisateurs'],

            // ================= CLIENTS =================
            ['name' => 'voir les clients', 'group' => 'clients'],
            ['name' => 'créer des clients', 'group' => 'clients'],
            ['name' => 'modifier des clients', 'group' => 'clients'],
            ['name' => 'supprimer des clients', 'group' => 'clients'],

            // ================= DOSSIERS =================
            ['name' => 'voir les dossiers', 'group' => 'dossiers'],
            ['name' => 'créer des dossiers', 'group' => 'dossiers'],
            ['name' => 'modifier des dossiers', 'group' => 'dossiers'],
            ['name' => 'supprimer des dossiers', 'group' => 'dossiers'],

            // ================= TEMPS / FEUILLES DE TEMPS =================
            ['name' => 'voir les entrées journalières', 'group' => 'temps'],
            ['name' => 'voir tous les temps', 'group' => 'temps'],
            ['name' => 'créer des entrées journalières', 'group' => 'temps'],
            ['name' => 'modifier des entrées journalières', 'group' => 'temps'],
            ['name' => 'supprimer des entrées journalières', 'group' => 'temps'],
            ['name' => 'valider les feuilles de temps', 'group' => 'temps'],
            ['name' => 'refuser les feuilles de temps', 'group' => 'temps'],

            // ================= CONGÉS - DEMANDES =================
            ['name' => 'voir les demandes de congés', 'group' => 'conges'],
            ['name' => 'voir toutes les demandes de congés', 'group' => 'conges'],
            ['name' => 'créer des demandes de congés', 'group' => 'conges'],
            ['name' => 'modifier des demandes de congés', 'group' => 'conges'],
            ['name' => 'supprimer des demandes de congés', 'group' => 'conges'],
            ['name' => 'approuver les demandes de congés', 'group' => 'conges'],
            ['name' => 'refuser les demandes de congés', 'group' => 'conges'],

            // ================= CONGÉS - SOLDES =================
            ['name' => 'voir les soldes de congés', 'group' => 'conges_soldes'],
            ['name' => 'voir tous les soldes de congés', 'group' => 'conges_soldes'],
            ['name' => 'modifier les soldes de congés', 'group' => 'conges_soldes'],
            ['name' => 'réinitialiser les soldes annuels', 'group' => 'conges_soldes'],

            // ================= CONGÉS - DASHBOARD & RAPPORTS =================
            ['name' => 'accéder au dashboard des congés', 'group' => 'conges_dashboard'],
            ['name' => 'voir le calendrier des congés', 'group' => 'conges_dashboard'],
            ['name' => 'voir les statistiques congés', 'group' => 'conges_dashboard'],

            // ================= EXPORTS EXCEL / PDF =================
            ['name' => 'exporter les temps en excel', 'group' => 'exports'],
            ['name' => 'exporter les temps en pdf', 'group' => 'exports'],
            ['name' => 'exporter les congés en excel', 'group' => 'exports'],
            ['name' => 'exporter les congés en pdf', 'group' => 'exports'],
            ['name' => 'exporter les soldes de congés', 'group' => 'exports'],

            // ================= STATISTIQUES GÉNÉRALES =================
            ['name' => 'voir les statistiques', 'group' => 'statistiques'],
            ['name' => 'voir les statistiques générales', 'group' => 'statistiques'],
            ['name' => 'voir les rapports mensuels temps', 'group' => 'statistiques'],

            // ================= PARAMÈTRES =================
            ['name' => 'voir les paramètres', 'group' => 'parametres'],
            ['name' => 'modifier les paramètres', 'group' => 'parametres'],
            ['name' => 'access-settings', 'group' => 'parametres'],

            // ================= DOCUMENTS / MÉDIAS =================
            ['name' => 'voir les documents', 'group' => 'medias'],
            ['name' => 'télécharger les documents', 'group' => 'medias'],
            ['name' => 'supprimer les documents', 'group' => 'medias'],

            // ================= DASHBOARDS =================
            ['name' => 'accéder au tableau de bord admin', 'group' => 'dashboard'],
            ['name' => 'accéder au tableau de bord utilisateur', 'group' => 'dashboard'],

            // ================= POSTES =================
            ['name' => 'voir les postes', 'group' => 'postes'],
            ['name' => 'gérer les postes', 'group' => 'postes'],
            ['name' => 'créer des postes', 'group' => 'postes'],
            ['name' => 'modifier des postes', 'group' => 'postes'],
            ['name' => 'supprimer des postes', 'group' => 'postes'],

            // ================= RÔLES & PERMISSIONS =================
            ['name' => 'voir les rôles', 'group' => 'roles_permissions'],
            ['name' => 'gérer les rôles', 'group' => 'roles_permissions'],
            ['name' => 'gérer les permissions', 'group' => 'roles_permissions'],
            ['name' => 'voir les permissions', 'group' => 'roles_permissions'],

            // ================= LOGS & ACTIVITÉS =================
            ['name' => 'voir les logs', 'group' => 'logs'],
            ['name' => 'voir les logs système', 'group' => 'logs'],
            ['name' => 'voir les activités', 'group' => 'logs'],

            // ================= NOTIFICATIONS =================
            ['name' => 'voir les notifications', 'group' => 'notifications'],
            ['name' => 'marquer les notifications comme lues', 'group' => 'notifications'],
            ['name' => 'gérer les notifications', 'group' => 'notifications'],
        ];

        // Création / Mise à jour des permissions
        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                ['group' => $perm['group'] ?? 'autre']
            );
        }

        // =============================================
        //          DÉFINITION DES RÔLES
        // =============================================

        // SUPER-ADMIN / ADMIN → TOUT
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // RH (Ressources Humaines)
        $rh = Role::firstOrCreate(['name' => 'rh']);
        $rh->syncPermissions([
            // Utilisateurs
            'voir les utilisateurs',
            'voir les postes',

            // Congés - complet
            'voir les demandes de congés',
            'voir toutes les demandes de congés',
            'créer des demandes de congés',
            'modifier des demandes de congés',
            'supprimer des demandes de congés',
            'approuver les demandes de congés',
            'refuser les demandes de congés',
            'voir les soldes de congés',
            'voir tous les soldes de congés',
            'modifier les soldes de congés',
            'réinitialiser les soldes annuels',
            'accéder au dashboard des congés',
            'voir le calendrier des congés',
            'voir les statistiques congés',

            // Exports congés & temps
            'exporter les congés en excel',
            'exporter les congés en pdf',
            'exporter les soldes de congés',
            'exporter les temps en excel',

            // Temps (lecture + validation)
            'voir tous les temps',
            'voir les rapports mensuels temps',
            'valider les feuilles de temps',
            'refuser les feuilles de temps',

            // Notifications
            'voir les notifications',
            'marquer les notifications comme lues',
            'gérer les notifications',

            // Statistiques
            'voir les statistiques',
            'voir les statistiques générales',
        ]);

        // MANAGER / Responsable
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            // Temps
            'voir les entrées journalières',
            'voir tous les temps',
            'valider les feuilles de temps',
            'refuser les feuilles de temps',
            'voir les rapports mensuels temps',

            // Congés
            'voir les demandes de congés',
            'voir toutes les demandes de congés',
            'approuver les demandes de congés',
            'refuser les demandes de congés',
            'voir les soldes de congés',
            'voir le calendrier des congés',

            // Exports
            'exporter les temps en excel',
            'exporter les temps en pdf',

            // Notifications
            'voir les notifications',
            'marquer les notifications comme lues',

            // Statistiques
            'voir les statistiques',
        ]);

        // EMPLOYÉ / UTILISATEUR STANDARD
        $employe = Role::firstOrCreate(['name' => 'employe']);
        $employe->syncPermissions([
            // Temps
            'voir les entrées journalières',
            'créer des entrées journalières',
            'modifier des entrées journalières',
            'supprimer des entrées journalières',

            // Congés
            'voir les demandes de congés',
            'créer des demandes de congés',
            'modifier des demandes de congés',
            'supprimer des demandes de congés',
            'voir les soldes de congés',
            'voir le calendrier des congés',

            // Notifications
            'voir les notifications',
            'marquer les notifications comme lues',

            // Dashboard utilisateur
            'accéder au tableau de bord utilisateur',
        ]);

        // DIRECTEUR GÉNÉRAL
        $dg = Role::firstOrCreate(['name' => 'directeur-general']);
        $dg->syncPermissions([
            // Temps
            'voir tous les temps',
            'voir les rapports mensuels temps',

            // Congés
            'voir toutes les demandes de congés',
            'voir tous les soldes de congés',
            'approuver les demandes de congés',
            'refuser les demandes de congés',
            'modifier les soldes de congés',
            'accéder au dashboard des congés',
            'voir le calendrier des congés',
            'voir les statistiques congés',

            // Exports
            'exporter les congés en excel',
            'exporter les congés en pdf',
            'exporter les temps en excel',
            'exporter les temps en pdf',
            'exporter les soldes de congés',

            // Statistiques
            'voir les statistiques',
            'voir les statistiques générales',

            // Notifications
            'voir les notifications',
            'marquer les notifications comme lues',
        ]);

        // AUDITEUR INTERNE (si nécessaire)
        $auditeur = Role::firstOrCreate(['name' => 'auditeur']);
        $auditeur->syncPermissions([
            'voir les dossiers',
            'voir tous les temps',
            'voir les rapports mensuels temps',
            'exporter les temps en excel',
            'exporter les temps en pdf',
            'voir les statistiques',
            'voir les statistiques générales',
            'voir les notifications',
            'marquer les notifications comme lues',
        ]);

        // RESPONSABLE CONFORMITÉ
        $responsableConformite = Role::firstOrCreate(['name' => 'responsable-conformite']);
        $responsableConformite->syncPermissions([
            'voir les dossiers',
            'voir tous les temps',
            'voir les rapports mensuels temps',
            'voir les statistiques',
            'voir les statistiques générales',
            'exporter les temps en excel',
            'exporter les temps en pdf',
            'voir les notifications',
            'marquer les notifications comme lues',
        ]);

        $this->command->info('✅ Permissions et rôles créés avec succès !');
    }
}
