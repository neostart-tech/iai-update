@extends('layouts.master')

@section('content')
<div class="bg-gray-50 py-6 px-2">
    <div class="container mx-auto">
        <h2 class="text-2xl font-bold text-center text-green-800 mb-8">
            Organigramme de l'IAI-TOGO
        </h2>

        <div class="org-chart flex flex-col items-center">

            <!-- Conseil de Direction -->
            <div class="org-node main">Conseil de Direction</div>

            <!-- Représentation Nationale -->
            <div class="org-children mt-6">
                <div class="org-node important">Représentation Nationale</div>
            </div>

            <!-- Tronc unique depuis Représentation Nationale vers 2 niveaux: cellules puis directions -->
            <div class="org-branch">
                <!-- Niveau 1: cellules (ordre exact demandé) -->
                <div class="org-children level-1 mt-6">
                    <div class="org-node small">Conseil Scientifique & Pédagogique</div>
                    <div class="org-node small">Comité d’Orientation</div>
                    <div class="org-node small">Cellule de Contrôle Interne</div>
                    <div class="org-node small">Secrétariat central</div>
                    <div class="org-node small">Cellule Communication et des Relations Extérieures</div>
                    <div class="org-node small">Secrétariat Particulier</div>
                </div>

                <!-- Niveau 2: trois Directions avec sous-arborescences imbriquées -->
                <div class="org-children level-2 mt-6">
                    <!-- Direction des Affaires Académiques et de la Scolarité -->
                    <div class="org-node dir">
                        Direction des Affaires Académiques et de la Scolarité
                        <div class="org-children">
                            <div class="org-node small">Titulaires des Niveaux de formation</div>
                            <div class="org-node small">Responsables des Champs Disciplinaires (UE)</div>
                            <div class="org-node small">Secrétariat Particulier</div>
                            <div class="org-node">
                                Division des Etudes et Scolarité
                                <div class="org-children">
                                    <div class="org-node small">Section Gestion des stages et Insertion Professionnelle</div>
                                    <div class="org-node small">Section Orientation et Formation Professionnelle</div>
                                    <div class="org-node small">Section Documentation et Reprographie</div>
                                    <div class="org-node small">Section Gestion Examens, Concours et Evaluations</div>
                                </div>
                            </div>
                            <div class="org-node">
                                Division des Affaires Académiques
                                <div class="org-children">
                                    <div class="org-node small">Section Gestion des Enseignements</div>
                                    <div class="org-node small">Section de Gestion des Carrières Universitaires</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Direction Administrative et Financière -->
                    <div class="org-node dir">
                        Direction Administrative et Financière
                        <div class="org-children">
                            <div class="org-node">
                                Division Affaires administratives et Ressources humaines
                                <div class="org-children">
                                    <div class="org-node small">Section des Affaires Administratives</div>
                                    <div class="org-node small">Section des Ressources Humaines et Gestion des carrières professionnelles</div>
                                </div>
                            </div>
                            <div class="org-node">
                                Division Comptabilité et Finances
                                <div class="org-children">
                                    <div class="org-node small">Section Comptabilité et Patrimoine</div>
                                    <div class="org-node small">Section Finances</div>
                                </div>
                            </div>
                            <div class="org-node small">Secrétariat Particulier</div>
                        </div>
                    </div>

                    <!-- Direction de Développement et de Recherche -->
                    <div class="org-node dir">
                        Direction de Développement et de Recherche
                        <div class="org-children">
                            <div class="org-node small">Secrétariat Particulier</div>
                            <div class="org-node">
                                Division Recherche et développement
                                <div class="org-children">
                                    <div class="org-node small">Section Recherche et Incubation de projets</div>
                                    <div class="org-node small">Section Développement</div>
                                    <div class="org-node small">Section Formation Continue</div>
                                </div>
                            </div>
                            <div class="org-node">
                                Division Système d’Information et Maintenance
                                <div class="org-children">
                                    <div class="org-node small">Section Systèmes et Réseaux</div>
                                    <div class="org-node small">Section Système d’Information</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* Style de base */
.org-node {
    background: #fff;
    border: 2px solid #0D7A37;
    border-radius: 6px;
    padding: 0.6rem 0.7rem;
    min-width: 140px;
    text-align: center;
    font-weight: 600;
    font-size: 0.8rem;
    color: #1a1a1a;
    position: relative;
}

/* Tronc reliant Représentation Nationale à ses deux niveaux */
.org-branch { position: relative; padding-bottom: 0.5rem; }
.org-branch::before {
    content: '';
    position: absolute;
    top: -20px; /* rejoint la barre des enfants du dessus */
    left: 50%;
    width: 3px;
    height: 100%;
    background: #0D7A37;
    transform: translateX(-50%);
}

/* Niveaux: couleurs et épaisseurs spécifiques */
.org-children.level-1::before { height: 3px; background: #0D7A37; }
.org-children.level-1 > .org-node::before { width: 3px; background:#0D7A37; }
.org-children.level-2::before { height: 3px; background: #0D7A37; }
.org-children.level-2 > .org-node::before { width: 3px; background: #0D7A37; }
.org-node.main {
    background: #0D7A37;
    color: #fff;
    font-size: 1rem;
}
.org-node.important {
    border: 2px solid #b09d72;
    font-weight: 700;
}
.org-node.dir {
    background: #2d5016;
    color: #fff;
    font-size: 0.85rem;
}
.org-node.small {
    font-size: 0.72rem;
    padding: 0.35rem 0.5rem;
    min-width: 120px;
}

/* Enfants (disposition horizontale + ligne de liaison) */
.org-children {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2.2rem;
    flex-wrap: wrap;
    position: relative;
}

/* Barre horizontale reliant les enfants */
.org-children::before {
    content: '';
    position: absolute;
    top: -22px;
    left: 0;
    right: 0;
    height: 2px;
    background: #0D7A37;
}

/* Trait vertical pour chaque enfant */
.org-children > .org-node::before {
    content: '';
    position: absolute;
    top: -22px;
    left: 50%;
    width: 2px;
    height: 20px;
    background: #0D7A37;
    transform: translateX(-50%);
}

/* Responsive */
@media (max-width: 1024px) {
    .org-children { gap: 0.8rem; }
}
@media (max-width: 768px) {
    .org-node { min-width: 100px; padding: .32rem .4rem; font-size: .66rem; }
}
</style>
@endsection
