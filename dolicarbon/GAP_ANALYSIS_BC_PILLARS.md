# DoliCarbon – Matrice d’écarts vs piliers ABC (Bilan Carbone®)

Références : [Outils conformes](https://abc-transitionbascarbone.fr/les-outils-conformes/), [Mise en conformité](https://abc-transitionbascarbone.fr/mise-en-conformite).  
Objectif produit : **alignement méthodologique interne** (sans revendication « conforme ABC » sans audit externe).

| Pilier ABC | Exigence fonctionnelle | Avant implémentation | Après implémentation (cible) | Criticité | Quick win |
|------------|----------------------|----------------------|------------------------------|-----------|-----------|
| Cadre technique | Cadrage périmètre / exclusions / matérialité | Bilan année + notes basiques | Table **cadrage** liée au bilan, versionnée, verrouillable | Haute | Formulaire cadrage + champs stockés |
| Cadre technique | Facteurs sourcés, versionnés, statut | Source texte + actif | **version**, **valid_from/to**, **status**, priorité | Haute | Colonnes + UI facteurs |
| Cadre technique | Moteur de calcul explicite / reproductible | Formule implicite en code | **Empreinte calcul** (snapshot kgCO2e, formule, fingerprint) | Haute | computeEmission enrichi |
| Accompagnement | Rôles / workflow données | Aucun | Statuts **draft→submitted→review→validated→locked** + droit **validate** | Moyenne | workflow.php + entrées |
| Accompagnement | Collecte guidée | Saisie / import factures | Checklists **par catégorie** (config JSON cadrage) + écran qualité | Moyenne | Champs + UI hints |
| Action significative | Plan transition chiffré | Actions simples | **baseline/target**, scores, **MACC** (coût / abattement), jalons | Haute | Colonnes action + écran transition |
| Action significative | Prévu vs réalisé | gain_estimated / actual | Comparaison + commentaire écart | Moyenne | Rapport + champs |
| Transparence | Traçabilité / audit | Aucun | **audit_log** + liste par bilan/entrée | Haute | Service + traceability.php |
| Transparence | Qualité / incertitudes | Aucun | **quality_grade**, **uncertainty** % sur entrées, agrégats rapport | Haute | quality.php + rapport |
| Transparence | Snapshots reproductibles | Aucun | **snapshot** JSON + hash | Moyenne | snapshot.php |
| Communication | Restitution multi-niveaux | Dashboard / report basiques | Onglets exécutif / analyste + drill-down | Moyenne | Report Angular |
| Communication | Exports + garde-fous | JSON partiel | **CSV/JSON** + annexes + **disclaimer** (pas de claim ABC) | Haute | export.php + config bridge |

**Synthèse** : les écarts majeurs étaient cadrage méthodo, incertitudes, workflow, audit trail, snapshot et exports structurés. Le lot livré couvre ces briques pour un niveau « expert interne » pilotable dans Dolibarr + Angular.
