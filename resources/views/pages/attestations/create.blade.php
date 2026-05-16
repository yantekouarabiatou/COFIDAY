@extends('layaout')
@section('title', 'Nouvelle demande d\'attestation')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-alt"></i> Attestation de travail</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('attestations.index') }}">Mes demandes</a></div>
            <div class="breadcrumb-item">Nouvelle demande</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-12 col-sm-12">

                {{-- Étape 1 : Choisir le type --}}
                <div class="mb-2">
                    <span class="text-muted small"><i class="fas fa-info-circle"></i>
                        Sélectionnez le type d'attestation dont vous avez besoin
                    </span>
                </div>

                <div class="row mb-4" id="type-cards">
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="type-card" data-type="attestation_simple">
                            <div class="type-card-icon" style="color:#4e73df;">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                            <div class="type-card-title">Attestation simple</div>
                            <div class="type-card-desc">Démarches administratives courantes</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="type-card" data-type="attestation_banque">
                            <div class="type-card-icon" style="color:#1cc88a;">
                                <i class="fas fa-university fa-2x"></i>
                            </div>
                            <div class="type-card-title">Banque / Crédit</div>
                            <div class="type-card-desc">Usage bancaire, prêt, financement</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="type-card" data-type="attestation_ambassade">
                            <div class="type-card-icon" style="color:#36b9cc;">
                                <i class="fas fa-globe fa-2x"></i>
                            </div>
                            <div class="type-card-title">Ambassade / Visa</div>
                            <div class="type-card-desc">Demande de visa, démarches consulaires</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="type-card" data-type="attestation_appel_offre">
                            <div class="type-card-icon" style="color:#e74a3b;">
                                <i class="fas fa-handshake fa-2x"></i>
                            </div>
                            <div class="type-card-title">Appel d'offres / Marché</div>
                            <div class="type-card-desc">Manifestation d'intérêt, offre client</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="type-card" data-type="attestation_autre">
                            <div class="type-card-icon" style="color:#858796;">
                                <i class="fas fa-pen-fancy fa-2x"></i>
                            </div>
                            <div class="type-card-title">Format spécifique</div>
                            <div class="type-card-desc">La Secrétaire rédige selon votre besoin</div>
                        </div>
                    </div>
                </div>

                {{-- Étape 2 : Formulaire --}}
                <div id="form-container" style="display:none;">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4 id="form-title"></h4>
                        </div>
                        <div class="card-body">
                            <div class="info-employe-grid mb-4">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-user"></i> Nom complet</span>
                                    <span class="info-value">{{ $user->prenom }} {{ $user->nom }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-briefcase"></i> Poste</span>
                                    <span class="info-value">{{ $user->poste->intitule ?? 'Non défini' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                                    <span class="info-value">{{ $user->email }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-calendar-alt"></i> Date</span>
                                    <span class="info-value">{{ now()->isoFormat('D MMMM YYYY') }}</span>
                                </div>
                            </div>

                            <form action="{{ route('attestations.store') }}" method="POST" id="att-form">
                                @csrf
                                <input type="hidden" name="type" id="type-input" value="{{ old('type') }}">

                                <div id="destinataire-block" class="form-group" style="display:none;">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-building mr-1"></i>
                                        Destinataire <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="destinataire"
                                           class="form-control @error('destinataire') is-invalid @enderror"
                                           value="{{ old('destinataire') }}"
                                           placeholder="Ex : Banque Atlantique, Ambassade de France…"
                                           id="destinataire-input">
                                    @error('destinataire')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="salaire-block" style="display:none;">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   class="custom-control-input"
                                                   id="inclure_salaire"
                                                   name="inclure_salaire"
                                                   value="1"
                                                   {{ old('inclure_salaire') ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="inclure_salaire">
                                                Inclure mon salaire net dans l'attestation
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Cochez uniquement si la banque l'exige (ex. : dossier de prêt).
                                        </small>
                                    </div>
                                    <div id="salaire-montant-block" class="form-group ml-4" style="{{ old('inclure_salaire') ? '' : 'display:none;' }}">
                                        <label class="font-weight-bold">
                                            Salaire net mensuel (FCFA) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number"
                                               name="salaire_net"
                                               class="form-control @error('salaire_net') is-invalid @enderror"
                                               value="{{ old('salaire_net') }}"
                                               placeholder="Ex : 250000"
                                               min="0" step="1000"
                                               style="max-width:260px;">
                                        @error('salaire_net')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-calendar-check mr-1 text-primary"></i>
                                        Date d'embauche <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        value="{{ Auth::user()->date_embauche ? \Carbon\Carbon::parse(Auth::user()->date_embauche)->format('d/m/Y') : 'Non renseignée' }}"
                                        readonly disabled>
                                    <small class="text-muted">
                                        Cette information provient de votre profil. 
                                        @if(!Auth::user()->date_embauche)
                                            <a href="{{ route('users.edit', Auth::user()) }}" class="text-danger">Veuillez la renseigner</a> avant de faire une demande.
                                        @endif
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-briefcase mr-1 text-primary"></i>
                                        Poste <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="poste"
                                           class="form-control @error('poste') is-invalid @enderror"
                                           value="{{ old('poste', $user->poste->intitule ?? '') }}"
                                           placeholder="Ex : Assistant de direction">
                                    @error('poste')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-pen mr-1 text-primary"></i>
                                        Rédigez votre demande <span class="text-danger">*</span>
                                    </label>
                                    <div class="motif-editor">
                                        <div class="motif-toolbar">
                                            <button type="button" class="toolbar-btn" id="btn-template" title="Utiliser un modèle">
                                                <i class="fas fa-magic"></i> Modèle
                                            </button>
                                            <button type="button" class="toolbar-btn" id="btn-clear" title="Effacer">
                                                <i class="fas fa-eraser"></i>
                                            </button>
                                            <span id="char-counter" class="toolbar-counter">0 / 2000</span>
                                        </div>
                                        <textarea name="motif"
                                                  id="motif-textarea"
                                                  class="motif-textarea @error('motif') is-invalid @enderror"
                                                  maxlength="2000"
                                                  placeholder="Rédigez votre demande ici…">{{ old('motif') }}</textarea>
                                        @error('motif')
                                            <div class="invalid-feedback d-block px-3 pb-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div id="type-info-box" class="alert" style="display:none;"></div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary" id="btn-reset">
                                        <i class="fas fa-arrow-left"></i> Changer de type
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane"></i> Envoyer la demande
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.type-card {
    border: 2px solid #e3e6f0;
    border-radius: 12px;
    padding: 24px 16px;
    text-align: center;
    cursor: pointer;
    transition: all .22s ease;
    background: #fff;
    height: 100%;
}
.type-card:hover {
    transform: translateY(-5px);
    border-color: #4e73df;
    box-shadow: 0 8px 24px rgba(78,115,223,.18);
}
.type-card.selected {
    border-color: #4e73df;
    background: linear-gradient(135deg, #eef0fc 0%, #fff 100%);
    box-shadow: 0 4px 18px rgba(78,115,223,.2);
}
.type-card-icon  { margin-bottom: 12px; }
.type-card-title { font-weight: 700; font-size: .95rem; color: #2e2e2e; margin-bottom: 6px; }
.type-card-desc  { font-size: .8rem; color: #6c757d; }

.info-employe-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 16px;
}
.info-item { display: flex; flex-direction: column; }
.info-label { font-size: .75rem; color: #858796; font-weight: 600; margin-bottom: 2px; }
.info-value { font-size: .9rem; color: #2e2e2e; font-weight: 500; }

.motif-editor {
    border: 1px solid #ced4da;
    border-radius: 8px;
    overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
}
.motif-editor:focus-within {
    border-color: #4e73df;
    box-shadow: 0 0 0 .2rem rgba(78,115,223,.15);
}
.motif-toolbar {
    background: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.toolbar-btn {
    border: 1px solid #d1d3e2;
    background: #fff;
    border-radius: 4px;
    padding: 4px 12px;
    font-size: .8rem;
    cursor: pointer;
    color: #495057;
    transition: all .15s;
}
.toolbar-btn:hover { background: #e8eaf5; }
.toolbar-counter { margin-left: auto; font-size: .78rem; color: #858796; font-weight: 600; }
.motif-textarea {
    width: 100%;
    min-height: 200px;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    padding: 14px 16px;
    font-size: .9rem;
    line-height: 1.75;
    resize: vertical;
    background: #fff;
    font-family: inherit;
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    // ========== TEMPLATES ET CONFIGURATIONS ==========
    const TEMPLATES = {
        attestation_simple:
`Je me permets par la présente de solliciter une attestation de travail.

En effet, j'exerce au sein du Cabinet COFIMA depuis le [date d'embauche] en tant que [poste], et dans le cadre de démarches administratives, une attestation de travail m'est nécessaire comme preuve de profession.

Je vous saurais gré de bien vouloir m'établir ce document attestant de ma présence au sein du Cabinet depuis la date susmentionnée.

Je vous prie d'agréer, Monsieur l'Associé-Gérant, l'expression de mes salutations distinguées.`,

        attestation_banque:
`Je me permets par la présente de solliciter une attestation de travail à l'usage de [nom de la banque].

En effet, dans le cadre d'un [prêt / financement / ouverture de compte], l'établissement bancaire m'a demandé de fournir une attestation officielle de mon employeur.

Je vous saurais gré de bien vouloir m'établir ce document dans les meilleurs délais.

Je vous prie d'agréer, Monsieur l'Associé-Gérant, l'expression de mes salutations distinguées.`,

        attestation_ambassade:
`Je me permets par la présente de solliciter une attestation de travail à l'usage de [nom de l'ambassade / consulat].

En effet, dans le cadre d'une demande de visa [type de visa], l'ambassade susmentionnée exige une attestation d'employeur attestant de ma situation professionnelle et de mon lien contractuel avec le Cabinet COFIMA.

Je vous saurais gré de bien vouloir m'établir ce document dans les meilleurs délais.

Je vous prie d'agréer, Monsieur l'Associé-Gérant, l'expression de mes salutations distinguées.`,

        attestation_appel_offre:
`Je me permets par la présente de solliciter une attestation de travail dans le cadre d'une [manifestation d'intérêt / offre de marché / appel d'offres] pour [précisez l'intitulé du marché ou de l'offre].

En ma qualité de [poste], je suis appelé(e) à faire valoir mon appartenance au Cabinet COFIMA ainsi que mon expérience professionnelle auprès du [maître d'ouvrage / commanditaire].

Je vous saurais gré de bien vouloir m'établir ce document dans les meilleurs délais afin de respecter les délais de soumission.

Je vous prie d'agréer, Monsieur l'Associé-Gérant, l'expression de mes salutations distinguées.`,

        attestation_autre:
`Je me permets par la présente de solliciter une attestation de travail dont le format spécifique m'est exigé par [précisez l'organisme].

[Décrivez précisément ici le format ou les informations spécifiques dont vous avez besoin, ex. : mention du salaire, ancienneté, type de contrat, date d'embauche exacte, etc.]

Je reste disponible pour toute information complémentaire.

Je vous prie d'agréer, Monsieur l'Associé-Gérant, l'expression de mes salutations distinguées.`,
    };

    const TYPE_TITLES = {
        attestation_simple:       '<i class="fas fa-file-alt"></i> Attestation simple — Démarches administratives',
        attestation_banque:       '<i class="fas fa-university"></i> Attestation de travail — Usage bancaire',
        attestation_ambassade:    '<i class="fas fa-globe"></i> Attestation de travail — Ambassade / Visa',
        attestation_appel_offre:  '<i class="fas fa-handshake"></i> Attestation — Appel d\'offres / Manifestation d\'intérêt',
        attestation_autre:        '<i class="fas fa-pen-fancy"></i> Attestation — Format spécifique',
    };

    const TYPE_INFOS = {
        attestation_simple:       { cls: 'alert-info',    msg: '📋 Format standard COFIMA. Votre attestation sera jointe au mail. Passez chez la secrétaire pour la version originale cachetée.' },
        attestation_banque:       { cls: 'alert-success', msg: '🏦 Ce format mentionne le destinataire bancaire. Vous pouvez choisir d\'y inclure votre salaire net si la banque l\'exige.' },
        attestation_ambassade:    { cls: 'alert-info',    msg: '✈️ Ce format est adapté aux démarches de visa. Précisez bien le nom de l\'ambassade ou du consulat concerné.' },
        attestation_appel_offre:  { cls: 'alert-danger',  msg: '🤝 Ce format atteste de votre appartenance au Cabinet dans le cadre d\'une offre commerciale ou d\'un appel d\'offres. Précisez bien le nom du marché.' },
        attestation_autre:        { cls: 'alert-warning', msg: '✍️ La Secrétaire préparera manuellement votre attestation selon vos indications. Soyez le plus précis possible dans votre description.' },
    };

    // Éléments DOM
    const typeCards = document.querySelectorAll('.type-card');
    const formContainer = document.getElementById('form-container');
    const typeInput = document.getElementById('type-input');
    const formTitle = document.getElementById('form-title');
    const destinataireBlock = document.getElementById('destinataire-block');
    const destinataireInput = document.getElementById('destinataire-input');
    const salaireBlock = document.getElementById('salaire-block');
    const salaireMontantBlock = document.getElementById('salaire-montant-block');
    const inclureSalaireCheck = document.getElementById('inclure_salaire');
    const infoBox = document.getElementById('type-info-box');
    const motifTextarea = document.getElementById('motif-textarea');
    const charCounter = document.getElementById('char-counter');
    const resetBtn = document.getElementById('btn-reset');
    const templateBtn = document.getElementById('btn-template');
    const clearBtn = document.getElementById('btn-clear');
    const dateEmbaucheInput = document.querySelector('input[name="date_embauche"]');
    const posteInput = document.querySelector('input[name="poste"]');

    let currentType = null;

    // ========== FONCTIONS UTILITAIRES ==========
    function formatDateForTemplate(value) {
        if (!value) return '[date d\'embauche]';
        const date = new Date(value);
        if (isNaN(date.getTime())) return value;
        return date.toLocaleDateString('fr-FR');
    }

    function getTemplateReplacements() {
        const dateEmbauche = dateEmbaucheInput ? dateEmbaucheInput.value : '';
        const poste = posteInput ? posteInput.value : '';
        const destinataire = destinataireInput ? destinataireInput.value : '';

        return {
            '\\[date d\\\'embauche\\]': formatDateForTemplate(dateEmbauche),
            '\\[poste\\]': poste || '[poste]',
            '\\[nom de la banque\\]': destinataire || '[nom de la banque]',
            '\\[nom de l\\\'ambassade / consulat\\]': destinataire || '[nom de l\'ambassade / consulat]',
        };
    }

    function applyTemplateValues(template) {
        const replacements = getTemplateReplacements();
        let result = template;
        for (const pattern in replacements) {
            const regex = new RegExp(pattern, 'gi');
            result = result.replace(regex, replacements[pattern]);
        }
        return result;
    }

    function updateCounter() {
        if (!motifTextarea) return;
        const len = motifTextarea.value.length;
        charCounter.textContent = len + ' / 2000';
        charCounter.style.color = len > 1800 ? '#dc3545' : (len > 1500 ? '#e67e22' : '#858796');
    }

    function insertTemplate() {
        if (!currentType || !TEMPLATES[currentType]) return;
        const template = TEMPLATES[currentType];
        motifTextarea.value = applyTemplateValues(template);
        updateCounter();
    }

    function clearMotif() {
        if (motifTextarea) {
            motifTextarea.value = '';
            updateCounter();
        }
    }

    function toggleSalaire() {
        if (salaireMontantBlock) {
            salaireMontantBlock.style.display = inclureSalaireCheck.checked ? 'block' : 'none';
        }
    }

    function refreshTemplatePlaceholders() {
        if (!motifTextarea) return;
        // Vérifie si le texte contient encore des placeholders
        if (/\[(date d'embauche|poste|nom de la banque|nom de l'ambassade \/ consulat)\]/i.test(motifTextarea.value)) {
            motifTextarea.value = applyTemplateValues(motifTextarea.value);
            updateCounter();
        }
    }

    function selectType(type, element) {
        currentType = type;
        // Met à jour la classe selected
        typeCards.forEach(card => card.classList.remove('selected'));
        if (element) element.classList.add('selected');

        typeInput.value = type;
        formTitle.innerHTML = TYPE_TITLES[type];

        // Destinataire
        const showDest = ['attestation_banque', 'attestation_ambassade'].includes(type);
        destinataireBlock.style.display = showDest ? 'block' : 'none';
        if (destinataireInput) destinataireInput.required = showDest;

        // Salaire
        salaireBlock.style.display = type === 'attestation_banque' ? 'block' : 'none';
        if (type !== 'attestation_banque') {
            inclureSalaireCheck.checked = false;
            toggleSalaire();
        }

        // Message info
        const info = TYPE_INFOS[type];
        infoBox.className = 'alert ' + info.cls;
        infoBox.innerHTML = info.msg;
        infoBox.style.display = 'block';

        formContainer.style.display = 'block';
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function resetSelection() {
        currentType = null;
        typeCards.forEach(card => card.classList.remove('selected'));
        formContainer.style.display = 'none';
        typeInput.value = '';
        infoBox.style.display = 'none';
        document.getElementById('type-cards').scrollIntoView({ behavior: 'smooth' });
    }

    // ========== ATTACHEMENT DES ÉVÉNEMENTS ==========
    // Cartes de type
    typeCards.forEach(card => {
        card.addEventListener('click', (e) => {
            const type = card.getAttribute('data-type');
            if (type) selectType(type, card);
        });
    });

    // Bouton reset
    if (resetBtn) resetBtn.addEventListener('click', resetSelection);

    // Boutons de l'éditeur
    if (templateBtn) templateBtn.addEventListener('click', insertTemplate);
    if (clearBtn) clearBtn.addEventListener('click', clearMotif);

    // Checkbox salaire
    if (inclureSalaireCheck) inclureSalaireCheck.addEventListener('change', toggleSalaire);

    // Compteur de caractères
    if (motifTextarea) {
        motifTextarea.addEventListener('input', updateCounter);
        updateCounter();
    }

    // Mise à jour automatique des placeholders quand les champs changent
    if (dateEmbaucheInput) dateEmbaucheInput.addEventListener('change', refreshTemplatePlaceholders);
    if (posteInput) posteInput.addEventListener('input', refreshTemplatePlaceholders);
    if (destinataireInput) destinataireInput.addEventListener('input', refreshTemplatePlaceholders);

    // Restauration après erreur de validation
    @if(old('type'))
        const oldType = '{{ old('type') }}';
        const oldCard = document.querySelector(`.type-card[data-type="${oldType}"]`);
        if (oldCard) selectType(oldType, oldCard);
        if (motifTextarea) updateCounter();
    @endif
})();
</script>
@endpush