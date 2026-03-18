<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'nom' => 'Orange Cameroun',
                'email' => 'contact@orange.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Immeuble Orange, Boulevard de la République, Douala',
                'telephone' => '+237 233 40 00 00',
                'contact_principal' => 'M. Jean Dupont',
                'secteur_activite' => 'Télécommunications',
                'numero_siret' => '12345678901234',
                'code_naf' => '6110Z',
                'site_web' => 'https://www.orange.cm',
                'notes' => 'Client majeur - Contrat annuel',
                'statut' => 'actif',
            ],
            [
                'nom' => 'MTN Cameroon',
                'email' => 'contact@mtn.cm',
                'siege_social' => 'Yaoundé, Cameroun',
                'adresse' => 'Immeuble MTN, Rue 1844, Yaoundé',
                'telephone' => '+237 222 20 00 00',
                'contact_principal' => 'Mme Sophie Martin',
                'secteur_activite' => 'Télécommunications',
                'numero_siret' => '23456789012345',
                'code_naf' => '6110Z',
                'site_web' => 'https://www.mtn.cm',
                'notes' => 'Client stratégique - Partenariat',
                'statut' => 'actif',
            ],
            [
                'nom' => 'Banque Atlantique',
                'email' => 'direction@banqueatlantique.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Avenue Général de Gaulle, Akwa, Douala',
                'telephone' => '+237 233 50 00 00',
                'contact_principal' => 'M. Pierre Laurent',
                'secteur_activite' => 'Banque',
                'numero_siret' => '34567890123456',
                'code_naf' => '6419Z',
                'site_web' => 'https://www.banqueatlantique.cm',
                'notes' => 'Audit annuel obligatoire',
                'statut' => 'actif',
            ],
            [
                'nom' => 'Société Générale Cameroun',
                'email' => 'info@societegenerale.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Boulevard de la Liberté, Bonanjo, Douala',
                'telephone' => '+237 233 42 00 00',
                'contact_principal' => 'M. Thomas Bernard',
                'secteur_activite' => 'Banque',
                'numero_siret' => '45678901234567',
                'code_naf' => '6419Z',
                'site_web' => 'https://www.societegenerale.cm',
                'notes' => 'Client historique - 10 ans de collaboration',
                'statut' => 'actif',
            ],
            [
                'nom' => 'Axa Assurance',
                'email' => 'assurance@axa.cm',
                'siege_social' => 'Yaoundé, Cameroun',
                'adresse' => 'Rue Joseph Mballa Eloumden, Bastos, Yaoundé',
                'telephone' => '+237 222 21 00 00',
                'contact_principal' => 'Mme Claire Petit',
                'secteur_activite' => 'Assurance',
                'numero_siret' => '56789012345678',
                'code_naf' => '6512Z',
                'site_web' => 'https://www.axa.cm',
                'notes' => 'Contrat de conseil en cours',
                'statut' => 'actif',
            ],
            [
                'nom' => 'TotalEnergies',
                'email' => 'cameroon@totalenergies.com',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Zone Industrielle, Bassa, Douala',
                'telephone' => '+237 233 30 00 00',
                'contact_principal' => 'M. Robert Durand',
                'secteur_activite' => 'Énergie',
                'numero_siret' => '67890123456789',
                'code_naf' => '1920Z',
                'site_web' => 'https://www.totalenergies.cm',
                'notes' => 'Client multinational - Procédures strictes',
                'statut' => 'actif',
            ],
            [
                'nom' => 'Douala Port Authority',
                'email' => 'info@portdedouala.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Port Autonome de Douala, BP 4021 Douala',
                'telephone' => '+237 233 40 50 00',
                'contact_principal' => 'M. Michel Moreau',
                'secteur_activite' => 'Portuaire',
                'numero_siret' => '78901234567890',
                'code_naf' => '5222Z',
                'site_web' => 'https://www.portdedouala.cm',
                'notes' => 'Organisme public - Appels d\'offres',
                'statut' => 'actif',
            ],
            [
                'nom' => 'Ministère des Finances',
                'email' => 'secretariat@minfi.cm',
                'siege_social' => 'Yaoundé, Cameroun',
                'adresse' => 'Ministère des Finances, Centre Ville, Yaoundé',
                'telephone' => '+237 222 23 00 00',
                'contact_principal' => 'M. Directeur Général',
                'secteur_activite' => 'Public',
                'numero_siret' => '89012345678901',
                'code_naf' => '8411Z',
                'site_web' => 'https://www.minfi.cm',
                'notes' => 'Client institutionnel - Procédures administratives longues',
                'statut' => 'actif',
            ],
            [
                'nom' => 'Cameroun Airlines',
                'email' => 'contact@camair-co.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Aéroport International de Douala',
                'telephone' => '+237 233 42 50 00',
                'contact_principal' => 'M. Directeur Commercial',
                'secteur_activite' => 'Transport aérien',
                'numero_siret' => '90123456789012',
                'code_naf' => '5110Z',
                'site_web' => 'https://www.camair-co.cm',
                'notes' => 'Prospect à convertir - Réunion prévue',
                'statut' => 'prospect',
            ],
            [
                'nom' => 'Brasseries du Cameroun',
                'email' => 'info@sabc.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Rue des Brasseries, Bassa, Douala',
                'telephone' => '+237 233 43 00 00',
                'contact_principal' => 'M. Directeur Financier',
                'secteur_activite' => 'Agroalimentaire',
                'numero_siret' => '01234567890123',
                'code_naf' => '1102Z',
                'site_web' => 'https://www.sabc.cm',
                'notes' => 'Ancien client - Inactif depuis 2022',
                'statut' => 'inactif',
            ],
            [
                'nom' => 'Orange Money',
                'email' => 'support@orangemoney.cm',
                'siege_social' => 'Yaoundé, Cameroun',
                'adresse' => 'Immeuble Orange Money, Centre Ville, Yaoundé',
                'telephone' => '+237 222 24 00 00',
                'contact_principal' => 'Mme Responsable Compliance',
                'secteur_activite' => 'Fintech',
                'numero_siret' => '11223344556677',
                'code_naf' => '6499Z',
                'site_web' => 'https://www.orangemoney.cm',
                'notes' => 'Filiale d\'Orange - Audit réglementaire',
                'statut' => 'actif',
            ],
            [
                'nom' => 'MTN Mobile Money',
                'email' => 'compliance@mtnmobilemoney.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'MTN House, Bonapriso, Douala',
                'telephone' => '+237 233 44 00 00',
                'contact_principal' => 'M. Chef de Service',
                'secteur_activite' => 'Fintech',
                'numero_siret' => '22334455667788',
                'code_naf' => '6499Z',
                'site_web' => 'https://www.mtnmobilemoney.cm',
                'notes' => 'Audit AML en cours',
                'statut' => 'actif',
            ],
            [
                'nom' => 'Canal+ Cameroun',
                'email' => 'admin@canalplus.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Rue du Cinéma, Akwa, Douala',
                'telephone' => '+237 233 45 00 00',
                'contact_principal' => 'M. Directeur Administratif',
                'secteur_activite' => 'Médias',
                'numero_siret' => '33445566778899',
                'code_naf' => '6010Z',
                'site_web' => 'https://www.canalplus.cm',
                'notes' => 'Nouveau client - Signature contrat prochaine',
                'statut' => 'prospect',
            ],
            [
                'nom' => 'Express Union',
                'email' => 'direction@expressunion.cm',
                'siege_social' => 'Yaoundé, Cameroun',
                'adresse' => 'Avenue Kennedy, Yaoundé',
                'telephone' => '+237 222 25 00 00',
                'contact_principal' => 'M. Contrôleur Interne',
                'secteur_activite' => 'Microfinance',
                'numero_siret' => '44556677889900',
                'code_naf' => '6492Z',
                'site_web' => 'https://www.expressunion.cm',
                'notes' => 'Audit semestriel programmé',
                'statut' => 'actif',
            ],
            [
                'nom' => 'CIMENCAM',
                'email' => 'info@cimencam.cm',
                'siege_social' => 'Douala, Cameroun',
                'adresse' => 'Usine de Bonabéri, Douala',
                'telephone' => '+237 233 46 00 00',
                'contact_principal' => 'M. Directeur d\'Usine',
                'secteur_activite' => 'Cimenterie',
                'numero_siret' => '55667788990011',
                'code_naf' => '2351Z',
                'site_web' => 'https://www.cimencam.cm',
                'notes' => 'Client industriel - Contrôle qualité',
                'statut' => 'actif',
            ],
            [
                'nom' => 'Cofplan',
                'email' => 'cofplan@cofima.cc',
                'siege_social' => 'Cotonou, Bénin',
                'adresse' => 'Cotonou, Bénin',
                'telephone' => null,
                'contact_principal' => null,
                'secteur_activite' => 'Audit & Conseil',
                'numero_siret' => '9988776655443', // valeur unique fictive
                'code_naf' => '6920Z',
                'site_web' => null,
                'notes' => 'Client interne - Missions importées depuis Cofplan',
                'statut' => 'actif',
            ],
        ];

        foreach ($clients as $clientData) {
            // Générer une référence unique basée sur le nom
            $reference = 'CLI-' . strtoupper(substr(preg_replace('/[^A-Z]/', '', $clientData['nom']), 0, 3)) . '-' . rand(1000, 9999);

            Client::updateOrCreate(
                ['numero_siret' => $clientData['numero_siret']], // clé unique
                array_merge($clientData, [
                    'logo' => $this->generateLogoUrl($clientData['nom']),
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('👥 ' . count($clients) . ' clients créés avec succès !');
        $this->command->info('   - Actifs: ' . count(array_filter($clients, fn($c) => $c['statut'] == 'actif')));
        $this->command->info('   - Prospects: ' . count(array_filter($clients, fn($c) => $c['statut'] == 'prospect')));
        $this->command->info('   - Inactifs: ' . count(array_filter($clients, fn($c) => $c['statut'] == 'inactif')));
    }

    /**
     * Génère une URL de logo fictive basée sur le nom du client
     */
    private function generateLogoUrl(string $nom): ?string
    {
        $logos = [
            'Orange Cameroun' => 'logos/orange.png',
            'MTN Cameroon' => 'logos/mtn.png',
            'Banque Atlantique' => 'logos/banque-atlantique.png',
            'Société Générale' => 'logos/societe-generale.png',
            'Axa Assurance' => 'logos/axa.png',
            'TotalEnergies' => 'logos/total.png',
            'Douala Port Authority' => 'logos/port-douala.png',
            'Ministère des Finances' => 'logos/minfi.png',
            'Cameroun Airlines' => 'logos/camair.png',
            'Brasseries du Cameroun' => 'logos/sabc.png',
            'Orange Money' => 'logos/orange-money.png',
            'MTN Mobile Money' => 'logos/mtn-money.png',
            'Canal+ Cameroun' => 'logos/canalplus.png',
            'Express Union' => 'logos/express-union.png',
            'CIMENCAM' => 'logos/cimencam.png',
        ];

        return $logos[$nom] ?? null;
    }
}
