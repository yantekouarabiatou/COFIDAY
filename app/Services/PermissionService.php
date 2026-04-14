<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Récupère toutes les permissions et les groupe par le champ 'group'.
     */
    public function getGroupedPermissions(): Collection
    {
        return Permission::all()->groupBy('group');
    }

    /**
     * Définit les libellés (labels) des groupes pour l'affichage.
     */
    public function getPermissionGroups(): array
    {
        return [
            'utilisateurs'          => 'Gestion des Utilisateurs',
            'rôles'                 => 'Rôles et Permissions (Système)',
            'Attestation de Travail' => 'Gestion des Attestations de Travail',
            'Certificats de Travail' => 'Gestion des Certificats de Travail',
            'Congés'                => 'Gestion des Congés',
            'postes'                => 'Gestion des Postes',
            'statistiques'          => 'Statistiques et Rapports',
            'paramètres'            => 'Paramètres Système',
            'médias'                => 'Médias et Documents',
            'dashboard'             => 'Tableaux de Bord',
            'rôles_permissions'     => 'Gestion des Permissions Méta',
            'Logs'                  => 'Gestion des Logs Système',
            'notifications'         => 'Gestion des Notifications',
        ];
    }

    /**
     * Synchronise les permissions d'un rôle.
     * @param Role $role L'instance du rôle Spatie
     * @param array $permissionNames Tableau de noms de permissions (ex: ['voir les plaintes', 'créer des plaintes'])
     */
    public function syncRolePermissions(Role $role, array $permissionNames): Role
    {
        // Spatie syncPermissions accepte directement un tableau de noms de permissions
        $role->syncPermissions($permissionNames);

        return $role;
    }

    /**
     * Crée un rôle et lui assigne immédiatement des permissions.
     * @param array $roleData Données du rôle (ex: ['name' => 'nouveau-role', 'guard_name' => 'web'])
     * @param array $permissionNames Tableau de noms de permissions
     */
    public function createRoleWithPermissions(array $roleData, array $permissionNames = []): Role
    {
        $role = Role::create($roleData);

        if (!empty($permissionNames)) {
            $this->syncRolePermissions($role, $permissionNames);
        }

        return $role;
    }
}
