# Audit — Plugin Studiojae Reviews

> Audit réalisé sur demande (sans modification de code).  
> Plan d’action détaillé en fin de document.

---

## 1. Widget Page Avis (front) — Structure & icônes providers

### État actuel

- **Shortcode** `front/class-summary-shortcode.php` : le bloc score est rendu ainsi :
  - `.sj-summary__score-block` contient directement `.sj-summary__score-num` et `.sj-summary__score-info` (lignes 289–301).
  - Pas de wrapper parent, pas d’icônes de plateformes sous la note.

(Supprimer les shortcodes pour le moment. Garder que les widgets Elementor qui appliquent la logique.)

### Manques identifiés

- Pas de **wrapper** autour du bloc score (demande : une div « wrapper » englobante).
- Pas d’**icônes par provider** sous la note globale.
- **Réglages** : aucun choix d’icône par provider (liste des sources = `SOURCE_LABELS` dans `admin/backoffice/src/lib/constants.js` et `includes/class-labels.php`).
- **Lieux** : le formulaire Lieux (`admin/backoffice/src/pages/Lieux.jsx`) n’a pas de champ **icône** par lieu (chaque lieu a une `source` mais pas d’icône dédiée).
- **Widget Elementor** « SJ — Page Avis » (`elementor/widgets/class-summary-widget.php`) : pas d’option « Inclure les icônes » / « Afficher les icônes des providers » ; le widget délègue tout au shortcode sans passer un paramètre dédié (ex. `show_provider_icons`).

### Conclusion

Il faut : (1) ajouter un wrapper, (2) conserver `.sj-summary__score-num` et `.sj-summary__score-info` dans le bloc (déjà le cas), (3) afficher les icônes des providers sous la note, (4) ajouter en réglages une liste « providers » avec choix d’icône par source, (5) ajouter un champ icône par lieu (optionnel/override), (6) ajouter une option « Inclure les icônes » dans le widget Elementor et la propager au shortcode.

---

## 2. Bugs signalés

### 2.1 « Par plateforme » — rien pour « Autre »

- **Cause** : Dans `admin/backoffice/class-rest-api.php`, `by_source` est construit uniquement à partir des lignes retournées par la requête SQL groupée par `avis_source` (l.509–525). Seules les **sources qui ont au moins un avis** apparaissent. S’il n’y a aucun avis avec `source = 'autre'`, la ligne « Autre » n’existe pas.
- **Comportement** : Ce n’est pas un bug technique mais un choix d’affichage : n’afficher que les sources présentes dans les données.
- **Souhait** : Afficher quand même « Autre » avec count = 0 pour cohérence avec la liste des sources (comme dans les filtres).

**Action** : Enrichir `by_source` côté backend avec toutes les sources connues (ex. `SOURCE_LABELS` / `allowed_sources`), en mettant `count: 0` et `avg_rating: 0` pour les sources sans avis, puis trier (ex. par count décroissant). Le front (Dashboard) n’a pas à changer.

---

### 2.2 Avis globaux (987) — « j’en ai bien plus », CPT + Regiondo

- **État actuel** :
  - Le dashboard part des avis CPT (avec `avis_source` non vide), puis **enrichit** avec les totaux « plateforme » des **lieux** (`reviews_count`, `rating`) pour ne pas double-compter : pour chaque lieu, `extra = max(0, platform_count - lieu_cpt_count)` est ajouté au total (l.619–651).
  - Ensuite le total est réécrit avec `array_sum(array_column($by_source, 'count'))` après enrichissement de `by_source` (l.681–682).
- **Problème possible** :
  - Soit des **lieux** ne sont pas pris en compte (inactifs, filtre lieu, ou lieux non encore dans `Settings::lieux()`).
  - Soit tu veux **exclure** certains lieux du total (ex. lieu « Google import » dont les avis sont déjà dans le CPT, pour éviter double comptage).
- **Souhait** : Un champ **« Compter parmi les avis »** (toggle) par lieu : si décoché, ce lieu est **exclu** du dashboard (et idéalement des stats front) pour les totaux / by_source.

**Action** :
- Ajouter sur chaque lieu un booléen, ex. `count_in_dashboard` (défaut `true`).
- Backend : dans `dashboard()` et partout où on calcule le total / `by_source` à partir des lieux (y compris `sj_enriched_stats` dans `includes/helpers.php`), ne considérer que les lieux avec `count_in_dashboard !== false`.
- Front (backoffice) : dans le formulaire Lieux (#/lieux), ajouter le toggle « Compter parmi les avis » et l’envoyer en create/update lieu.
- Documenter que « décoché » = exclu du dashboard et des stats globales (éviter double comptage import Google + CPT).

---

### 2.3 Premiers avis chargés : pas la description, il faut « Voir plus »

- **État actuel** :
  - Côté **PHP** (shortcode) : le texte complet est mis dans `data-full` et en contenu du `<p class="sj-card__text">` (l.654–658 de `front/class-summary-shortcode.php`).
  - Au chargement, **front/assets/sj-summary.js** exécute `truncateCards(reviews)` (l.91–105) : il lit `data-full` (ou le texte du paragraphe), tronque à `words` (ex. 40), et met le texte tronqué dans le paragraphe. Le bouton « Voir plus » affiche le texte complet depuis `data-full`.
  - Donc **par conception**, les premiers avis affichent une **troncature** (ex. 40 mots) ; le descriptif complet n’apparaît qu’après « Voir plus ».
- **Bug possible** :
  - Si en **AJAX** (load more / filtres) l’API ne renvoie pas le champ `text` complet dans `sj_normalize_review`, alors `buildCardHtml` (l.548) mettrait un texte vide ou tronqué dans `data-full`, et après « Voir plus » on n’aurait pas le vrai descriptif.
  - Vérifier que `front_reviews` utilise bien `sj_normalize_review($post)` (sans troncature) et que `avis_text` (meta) contient bien le texte complet en base.
- **Souhait** : Si le problème est « les premiers avis n’ont pas le descriptif », soit c’est une demande de **comportement** (afficher tout le texte sans troncature par défaut), soit un **bug** (texte manquant en AJAX ou en first load).

**Action** :
- Vérifier que l’endpoint `front/reviews` renvoie bien `text` complet pour chaque avis (via `sj_normalize_review` et meta `avis_text`).
- Si oui : proposer une option « Mots avant Voir plus » à 0 = pas de troncature (texte complet d’emblée).
- Si non : corriger la source du texte (meta / normalisation) pour que les cartes reçues en AJAX aient le même contenu que le premier rendu serveur.

---

## 3. UI / UX — Backoffice

- **Constat** : Beaucoup de champs, peu d’icônes, couleurs partout (badges, boutons). Souhait d’un rendu plus « Anthropic-like » : sobre, icônes discrètes, texte lisible, fluide, pas de surcharge colorée.
- **Stack** : shadcn déjà utilisé (tooltip, sheet, etc.) mais pas partout.
- **Action** : Refactoriser l’UI du backoffice en s’appuyant sur shadcn (composants, tokens) : petits icônes + texte, hiérarchie claire, couleurs limitées, états (hover/focus) discrets. Idéalement un pass global par écran (Dashboard, Lieux, Avis, Réglages, Import) avec une checklist (titres, sous-titres, icônes, boutons, badges, tableaux).

---

## 4. Connexion des widgets front à la data

- **État** :
  - Le widget Elementor « Page Avis » appelle `SummaryShortcode::render()` avec les settings du widget (lieu_id, show_*, etc.) et le shortcode utilise `compute_stats_sql()` + `get_reviews()` (CPT + meta).
  - Les stats « enrichies » (CPT + plateforme) sont utilisées dans `compute_stats_sql` / `sj_enriched_stats` selon le contexte ; le shortcode reçoit bien `lieu_id`, `source_filter`, `lieu_ids`.
- **Risque** : Si « mal connecté » signifie que le **filtre lieu/source** du widget ne s’applique pas correctement, il faut tracer le flux : `elementor/widgets/class-summary-widget.php` → `render()` → `$sc->render([...])` et vérifier que `source_filter` et `lieu_ids` sont bien passés et utilisés dans le shortcode et dans les requêtes (SQL + `get_reviews`). Vérifier aussi que les **data-*** du conteneur (pour le JS) reflètent ces réglages (`data-source-filter`, `data-lieu-id`, `data-lieu-ids`) pour l’AJAX.
- **Action** : Audit ciblé du flux widget → shortcode → HTML data-* → `sj-summary.js` (params AJAX) et de l’usage de `lieu_id` / `lieu_ids` / `source_filter` dans `compute_stats_sql` et `get_reviews`. Corriger toute déconnexion (paramètres non passés ou mal nommés).

---

## 5. Pourquoi pas d’icônes providers en front ?

- **Cause** : Il n’existe pas encore de **concept** « icônes par provider » en front : pas de réglage, pas de champ par lieu, pas de rendu dans le shortcode (bloc score). La seule chose proche est `sj_source_icon(string)` dans `includes/helpers.php` (cité dans CLAUDE.md) et l’affichage « source » sur les cartes (texte, pas icône sous la note).
- **Action** : Mettre en place le flux complet : réglages (liste providers + icône par source) → option « Inclure les icônes » dans le widget → shortcode qui affiche les icônes sous la note (wrapper + bloc score existant + nouvelle zone icônes).

---

# Plan d’action (ordre recommandé)

1. **Dashboard & données**
   - **« Par plateforme »** : Enrichir `by_source` avec toutes les sources connues (count 0 si aucune donnée). Fichier : `admin/backoffice/class-rest-api.php` (méthode `dashboard()`).
   - **« Compter parmi les avis »** :
     - Ajouter `count_in_dashboard` (bool, défaut true) dans la structure lieu (validate_lieu_body, create/update_lieu, get_lieux).
     - Exclure les lieux avec `count_in_dashboard === false` dans `dashboard()` et dans `sj_enriched_stats()` (`includes/helpers.php`).
     - Formulaire Lieux : toggle « Compter parmi les avis » (`admin/backoffice/src/pages/Lieux.jsx` + API create/update).

2. **Widget Page Avis — structure & icônes**
   - **HTML/CSS** :
     - Introduire une div wrapper (ex. `.sj-summary__score-wrapper`) autour du bloc actuel.
     - Garder `.sj-summary__score-block` en `display: block` avec `.sj-summary__score-num` et `.sj-summary__score-info` à l’intérieur (déjà le cas), puis ajouter sous le bloc une zone « providers » (icônes).
   - **Réglages** :
     - Onglet ou section « Providers / Plateformes » : liste des sources (google, tripadvisor, …) avec choix d’icône par source (sélecteur d’icône ou URL/media). Stockage : option ou partie de `sj_reviews_settings`.
     - Lieux : champ optionnel « Icône » (override par lieu) dans le formulaire + sauvegarde REST.
   - **Shortcode** :
     - Nouvel attribut `show_provider_icons` (ou équivalent).
     - Si actif : rendre la zone icônes sous le score (sources dédupliquées depuis les stats / lieux, avec mapping source → icône depuis les réglages + override lieu).
   - **Widget Elementor** :
     - Ajouter l’option « Inclure les icônes des providers » (ou « Afficher les icônes sous la note ») et la passer au shortcode.
     - Vérifier que tous les paramètres utiles (lieu, source_filter, lieu_ids, etc.) sont bien transmis au shortcode et aux data-* pour le JS.

3. **Bug « descriptif / Voir plus »**
   - Vérifier que `front/reviews` renvoie bien `text` complet (meta `avis_text`) via `sj_normalize_review`.
   - Si les premiers avis (rendu serveur) n’affichent pas le bon texte : vérifier que le paragraphe contient bien le texte complet avant l’exécution de `truncateCards` et que `data-full` est bien renseigné.
   - Option : permettre « Mots avant Voir plus » = 0 pour désactiver la troncature (texte complet affiché directement).

4. **Connexion widgets ↔ data**
   - Audit du flux : SummaryWidget → `render()` → paramètres passés à `SummaryShortcode::render()` et aux data-* du DOM.
   - Vérifier que `source_filter`, `lieu_ids`, `lieu_id` sont utilisés dans `compute_stats_sql`, `get_reviews`, et dans les requêtes AJAX (buildFilterParams, etc.). Corriger les écarts.

5. **UI backoffice**
   - Passer les écrans principaux (Dashboard, Lieux, Avis, Réglages, Import) en revue pour : usage systématique de shadcn, icônes petites et cohérentes, texte lisible, couleurs réduites, états hover/focus discrets.
   - Refactoriser étape par étape (un écran ou un composant à la fois) pour tendre vers un style « Anthropic-like ».

6. **Technique / existant**
   - Aligner le plugin sur l’existant (traits, helpers, REST, cache) : pas de duplication de logique, réutilisation de `sj_enriched_stats`, `sj_normalize_review`, `Settings::lieux()`, et des constantes de sources.
   - Documenter dans `CLAUDE.md` : nouveau champ lieu `count_in_dashboard`, réglages « providers » (icônes), option shortcode/widget `show_provider_icons`, et comportement « Compter parmi les avis ».

---

*Document généré à partir de l’audit du plugin studiojae-reviews. Aucune modification de code n’a été effectuée lors de cet audit.*
