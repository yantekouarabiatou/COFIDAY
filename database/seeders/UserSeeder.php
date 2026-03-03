<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Poste;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Liste des utilisateurs extraite du fichier Excel
        $users = [
            [
                'username_raw' => 'akouzoubanda',
                'nom' => 'KOUZOU-BANDA',
                'prenom' => 'Amath Hassan',
                'email' => 'akouzoubanda@cofima.cc',
                'poste' => 'DIRECTEUR INFORMATIQUE',
                'role' => 'SUPER ADMINISTRATEUR',
            ],
            [
                'username_raw' => 'Magloire.Lanha@cofima.cc',
                'nom' => 'LANHA',
                'prenom' => 'Magloire',
                'email' => 'maglanha@gmail.com',
                'poste' => 'CONSULTANTS',
                'role' => 'SUPER ADMINISTRATEUR',
            ],
            [
                'username_raw' => 'jmavande',
                'nom' => 'AVANDE',
                'prenom' => 'Jean-Michel',
                'email' => 'jmavande@cofima.cc',
                'poste' => 'DIRECTEUR GENERALE',
                'role' => 'AGENT SUPER GESTIONNAIRE',
            ],

            [
                'username_raw' => 'Térence',
                'nom' => 'GANDJI',
                'prenom' => 'Térence Craig',
                'email' => 'gandjiterence@gmail.com',
                'poste' => 'STATISTICIEN',
                'role' => 'Statisticien-Economiste',
            ],
            [
                'username_raw' => 'Loukmane5',
                'nom' => 'ADANDE MOUSSA',
                'prenom' => 'Orou Loukmane',
                'email' => 'adandeloukmane@gmail.com',
                'poste' => 'STATISTICIEN',
                'role' => 'Statisticien-Economiste',
            ],
            [
                'username_raw' => 'ryantekoua@cofima.cc',
                'nom' => 'YANTEKOUA',
                'prenom' => 'Rabiatou',
                'email' => 'ryantekoua@cofima.cc',
                'poste' => 'INFORMATICIEN',
                'role' => 'SUPER ADMINISTRATEUR',
            ],
            [
                'username_raw' => 'Ifèdé',
                'nom' => 'SOSSA',
                'prenom' => 'Ifèdé',
                'email' => 'isossa@cofima.cc',
                'poste' => 'INFORMATICIEN',
                'role' => 'ADMINISTRATEUR',
            ],
            [
                'username_raw' => 'Gbessoua',
                'nom' => 'GBESSOUA',
                'prenom' => 'Thomas',
                'email' => 'tgbessoua@cofima.cc',
                'poste' => 'DIRECTEUR GENERALE',
                'role' => 'AGENT SUPER GESTIONNAIRE',
            ],
            [
                'username_raw' => 'Jean-Claude',
                'nom' => 'AVANDE',
                'prenom' => 'Jean-Claude',
                'email' => 'jcavande@cofima.cc',
                'poste' => 'DIRECTEUR GENERALE',
                'role' => 'AGENT SUPER GESTIONNAIRE',
            ],
            [
                'username_raw' => 'Marie-Laure',
                'nom' => 'EGUAGIE',
                'prenom' => 'Marie-Laure',
                'email' => 'meguagie@cofima.cc',
                'poste' => 'SECRETAIRE',
                'role' => 'AGENT',
            ],
            [
                'username_raw' => 'Fahimat',
                'nom' => 'ALLI',
                'prenom' => 'Fahimat',
                'email' => 'falli@cofima.cc',
                'poste' => 'COMPTABLE',
                'role' => 'AGENT',
            ],
            [
                'username_raw' => 'Lucien',
                'nom' => 'AMOUSSOU',
                'prenom' => 'Lucien',
                'email' => 'mamoussou@cofima.cc',
                'poste' => 'COMPTABLE',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Libert',
                'nom' => 'CODJOVI',
                'prenom' => 'Libert Raoul',
                'email' => 'lcodjovi@cofima.cc',
                'poste' => 'CHEF SERVICES QUALITÉS',
                'role' => 'AGENT GESTIONNAIRE',
            ],
            [
                'username_raw' => 'Carmen',
                'nom' => 'ANANI',
                'prenom' => 'Carmen',
                'email' => 'canani@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Pascal',
                'nom' => 'LIMA',
                'prenom' => 'Pascal',
                'email' => 'plima@cofima.cc',
                'poste' => 'SECRETAIRE',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Carolle',
                'nom' => 'HONVOU',
                'prenom' => 'Carolle',
                'email' => 'chonvou@cofima.cc',
                'poste' => 'COMPTABLE',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Armel',
                'nom' => 'KINKPO',
                'prenom' => 'Armelle',
                'email' => 'akinkpo@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Helza',
                'nom' => 'ZOHON',
                'prenom' => 'Helza',
                'email' => 'hzohoun@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Nabirou',
                'nom' => 'SACARI',
                'prenom' => 'Nabirou',
                'email' => 'nsacari@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Housséni',
                'nom' => 'OUOROU ZOUMAROU',
                'prenom' => 'Housséni',
                'email' => 'houorouzoumarou@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Horeb',
                'nom' => 'DOMINGO',
                'prenom' => 'Horeb',
                'email' => 'ahdomingo@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Estéban',
                'nom' => 'HOUNDONOUGBO QUENUM',
                'prenom' => 'Estéban',
                'email' => 'ehoundonougbo@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Jean-Eudes',
                'nom' => 'GBESSOUA',
                'prenom' => 'Jean-Eudes',
                'email' => 'jegbessoua@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Armelle',
                'nom' => 'GABA',
                'prenom' => 'Armelle',
                'email' => 'agaba@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
            [
                'username_raw' => 'Roger DES-LANLO', // contient un espace, nous le remplacerons par un point
                'nom' => 'DES-LANLO',
                'prenom' => 'Roger',
                'email' => 'rdeslanlo@cofima.cc',
                'poste' => 'AUDITEUR',
                'role' => 'AGENT GESTIONNAIRE',
            ],
            [
                'username_raw' => 'biroko',
                'nom' => 'IROKO',
                'prenom' => 'Belvik',
                'email' => 'biroko@cofima.cc',
                'poste' => 'INFORMATICIEN',
                'role' => 'SUPER ADMINISTRATEUR',
            ],
            [
                'username_raw' => 'pierreguida',
                'nom' => 'GUIDAN',
                'prenom' => 'K. A. PIERRE',
                'email' => 'kouessiahouangnimonpierreguida@gmail.com',
                'poste' => 'CONDUCTEUR',
                'role' => 'AGENT',
            ],
            [
                'username_raw' => 'jegbessoua',
                'nom' => 'GBESSOUA MEDESSI',
                'prenom' => 'JEAN-EUDES',
                'email' => 'jegbessoua@cofimabenin.cc',
                'poste' => 'AUDITEUR',
                'role' => 'COLABORATEUR',
            ],
        ];

        $currentYear = 2026; // année en cours

        foreach ($users as $data) {
            // Nettoyage du username : si c'est un email, on prend la partie avant @
            $username = $data['username_raw'];
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $username = explode('@', $username)[0];
            }
            // Remplacer les espaces par des points dans le username (pour "Roger DES-LANLO")
            $username = str_replace(' ', '.', $username);

            // Récupérer l'ID du poste correspondant
            $poste = Poste::where('intitule', $data['poste'])->first();
            if (!$poste) {
                $this->command->error("Poste introuvable : " . $data['poste']);
                continue;
            }

            // Générer le mot de passe : prénom sans espaces + "@2026"
            $prenomClean = preg_replace('/\s+/', '', $data['prenom']); // supprime tous les espaces
            $password = $prenomClean . '@' . $currentYear;

            // Créer ou mettre à jour l'utilisateur
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $username,
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'password' => Hash::make($password),
                    'poste_id' => $poste->id,
                    'telephone' => null,
                    'is_active' => true,
                ]
            );

            // Attribuer le rôle Spatie en fonction du rôle métier
            $roleName = $this->mapRole($data['role']);
            if ($roleName) {
                $user->syncRoles([$roleName]);
            } else {
                $this->command->warn("Rôle non mappé pour : " . $data['email'] . " (" . $data['role'] . ")");
            }
        }

        $this->command->info('✅ Utilisateurs créés avec succès !');
    }

    /**
     * Mappe les rôles du fichier vers les rôles Spatie.
     */
    private function mapRole(string $role): ?string
    {
        return match ($role) {
            'SUPER ADMINISTRATEUR', 'ADMINISTRATEUR' => 'admin',
            'AGENT SUPER GESTIONNAIRE', 'AGENT GESTIONNAIRE' => 'manager',
            'Statisticien-Economiste', 'AGENT' => 'collaborateur',
            'COLABORATEUR' => 'collaborateur',
            default => null,
        };
    }
}
