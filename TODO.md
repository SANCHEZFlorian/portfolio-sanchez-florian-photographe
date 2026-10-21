# 📋 TODO — Portfolio Photographe (Florian Sanchez)

Suivi des galeries, optimisation des clichés et expérience visuelle.

---

## 🎯 En cours
- [ ] Sélection et compression optimisée des clichés haute résolution

## 📌 À venir
- [ ] Système de commande de tirages ou formulaire de devis de prestation
- [ ] Protection et filigrane léger des clichés présentés
- [ ] Tag(s) Instagram au niveau projet (avec support de plusieurs tags) au lieu de par photo
- [ ] Multi-tags par photo pour les projets de type catégorie auto (ex: portrait, détails, etc.)

## ✅ Terminé
- [x] **Élimination complète des résidus « Laravel » dans les titres, balises meta et configurations** :
  - **Correction du titre dynamique Inertia ([`resources/js/app.js`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/app.js))** : remplacement du fallback `Laravel` par `Florian Sanchez Photographie`, formatage intelligent du titre `title — Florian Sanchez Photographie` sans doublon si le nom est déjà présent dans le titre.
  - **Enrichissement SEO & Balises Meta complètes** : ajout de balises `<title>`, `<meta name="description">` et balises Open Graph spécifiques pour les pages À propos ([`About.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/About.vue)), Contact ([`Contact.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Contact.vue)), Galerie, Projets et pages d'authentification/profil.
  - **Nettoyage des configurations et fallbacks backend** : mise à jour des valeurs par défaut dans [`config/app.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/config/app.php), [`config/mail.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/config/mail.php), [`config/logging.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/config/logging.php), [`config/cache.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/config/cache.php), [`config/session.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/config/session.php), [`config/database.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/config/database.php), `.env` et `.env.example`.
  - **Remplacement de [`Welcome.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Welcome.vue)** par un composant d'accueil épuré aux couleurs de Florian Sanchez Photographie.
- [x] **Protection par Mot de Passe & Générateur Aléatoire pour l'Espace de Livraison Client** :
  - **Génération automatique de mot de passe à l'activation ([`Admin/Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue))** : dès que l'espace de livraison est coché, un mot de passe sécurisé et convivial (sans caractères visuellement ambigus) est automatiquement généré.
  - **Générateur interactif intégré & personnalisation totale** : bouton « Nouveau code » pour régénérer un mot de passe à la volée d'un clic, possibilité d'éditer manuellement le code, et bouton de copie instantanée du mot de passe seul.
  - **Bouton d'invitation prêt à envoyer (« Copier lien + mot de passe »)** : génère un texte d'invitation complet avec le lien de la galerie et son code d'accès, prêt à être collé dans WhatsApp, SMS ou email.
  - **Écran de verrouillage client dédié ([`DeliveryPassword.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/DeliveryPassword.vue))** : design nocturne soigné avec viseur/cadenas lumineux, formulaire de saisie avec bascule afficher/masquer le mot de passe, gestion des erreurs et déverrouillage de session. Aucune photo ni URL n'est chargée tant que le mot de passe n'est pas validé.
  - **Sécurisation stricte des téléchargements ([`DeliveryController.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/app/Http/Controllers/DeliveryController.php))** : blocage 403 de toute tentative de téléchargement d'archive ZIP ou de photo unitaire HD sans session déverrouillée par le mot de passe.
  - **Suite de tests automatisés étendue ([`DeliveryTest.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/tests/Feature/DeliveryTest.php))** : 7 tests de fonctionnalités validant la demande de mot de passe, le refus en cas d'erreur et le déverrouillage avec téléchargement.
- [x] **Espace Privé de Livraison Client des Shootings & Téléchargement Sécurisé HD (.ZIP)** :
  - **Gestion Admin dédiée ([`Admin/Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue))** : interrupteur pour activer/désactiver l'espace de livraison, génération automatique d'un jeton secret unique cryptographique (`delivery_token`), copie en un clic du lien client avec retour toast instantané, bouton d'ouverture directe, bouton de régénération du lien avec modale de confirmation, titre d'accueil personnalisable, message d'accompagnement du photographe, date d'expiration optionnelle et compteur de téléchargements.
  - **Badge de statut dans la liste des projets ([`Admin/Project/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Index.vue))** : colonne « Livraison » avec lien direct et compteur de téléchargements.
  - **Galerie Client Privée & Épurée ([`DeliveryShow.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/DeliveryShow.vue))** : vue réceptive aux couleurs du portfolio avec badge privé, titre, infos du shooting (lieu, date, nombre de clichés, poids total de l'archive), mot d'accueil personnalisé de Florian dans un encart chaleureux et bouton proéminent de téléchargement complet en un clic, débarrassée de tout jargon technique pour une expérience client limpide.
  - **Génération d'archive ZIP à la volée ([`DeliveryController.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/app/Http/Controllers/DeliveryController.php))** : création dynamique d'un fichier `.zip` structuré contenant tous les fichiers originaux bruts sans compression, préservant à 100% l'intégralité des métadonnées EXIF (boîtier, objectifs, ISO, focale, auteur/copyright Florian Sanchez).
  - **Téléchargement HD unitaire dans la Lightbox ([`PhotoLightbox.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/PhotoLightbox.vue))** : bouton dédié « Télécharger HD » permettant au client d'enregistrer ses clichés favoris un par un directement en pleine résolution.
  - **Protection anti-copie / anti-téléchargement sauvage ([`MasonryGrid.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/MasonryGrid.vue), [`PhotoLightbox.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/PhotoLightbox.vue))** : désactivation du clic-droit (`contextmenu.prevent`), désactivation du glisser-déposer (`dragstart.prevent`, `draggable="false"`, `-webkit-user-drag: none`), et `user-select: none` pour dissuader le vol de photos tout en réservant le téléchargement légitime au client.
  - **Suite de tests automatisés ([`DeliveryTest.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/tests/Feature/DeliveryTest.php))** : 4 tests de fonctionnalités validant la consultation, l'interdiction des liens inactifs, la génération/téléchargement du ZIP et la régénération sécurisée des tokens.
  - **Bac de photos en attente épinglé (`.unplaced-photos-dock`)** : le bandeau des photos restantes (`position: sticky; top: 12px; z-index: 45;`) suit désormais le défilement de la page avec un effet verre dépoli (`backdrop-filter: blur(18px)`) et un bouton `▲ Réduire` / `▼ Déplier`, permettant de glisser-déposer directement les photos dans n'importe quelle nouvelle ligne créée en bas de page sans jamais avoir à remonter tout en haut.
  - **Correction du conteneur de scroll admin ([`app.css`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/css/app.css))** : suppression de `overflow-y: auto` sur `.admin-main` qui empêchait les éléments `position: sticky` de s'accrocher à la fenêtre lors du scroll.
  - **Auto-scroll fluide de la fenêtre pendant le drag (`handleWindowDragOver`)** : défilement automatique progressif vers le haut ou vers le bas lorsque la souris s'approche des bords de l'écran en tenant une photo ou une ligne, + centrage automatique sur toute nouvelle ligne harmonique créée.
  - Déclinaison complète sur [`Admin/Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue) et [`Admin/Standalone/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Standalone/Index.vue).
- [x] **Pages d'Erreur Personnalisées (401, 403, 404, 419, 429, 500, 503) — Esthétique Viseur Reflex & Chambre Noire** :
  - **Composant Inertia Vue [`Error.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Error.vue)** : intégré à [`PublicLayout.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Layouts/PublicLayout.vue) avec le bokeh interactif [`PixiBackground.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/PixiBackground.vue), cadre de viseur optique reflex (`┌ ┐ └ ┘`), HUD appareil photo (`OUT_OF_FOCUS // F/1.4 • 1/1000s • ISO 400`), diaphragme rotatif et icônes Phosphor dédiées à chaque code d'erreur.
  - **Interception Inertia dans [`bootstrap/app.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/bootstrap/app.php)** : rendu fluide de `Error.vue` pour les erreurs `401`, `403`, `404`, `419`, `429`, `500` et `503` en préservant le code HTTP exact.
  - **Vues Blade autonomes de secours ([`resources/views/errors/`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/views/errors/layout.blade.php))** : templates `401`, `403`, `404`, `419`, `429`, `500` et `503` autonomes reprenant exactement le même design de viseur reflex au cas où le serveur est en mode maintenance (`php artisan down`) ou rencontre une erreur critique avant le chargement d'Inertia.
- [x] **Manipulation Géométrique Intra-Ligne (Décalage `← ↑ ↓ →`, Division `⊞ ×4` & Fusion `⬜ Grand`)** :
  - **Décalage local intelligent (`←`, `↑`, `↓`, `→`)** : possibilité de déplacer individuellement n'importe quel slot ou sous-bloc (qu'il soit vide ou occupé par une photo) à gauche, à droite, en haut ou en bas à l'intérieur de sa ligne harmonique sans jamais créer de trou ni briser les dimensions globales.
    - *Exemples* : décaler un *Grand Portrait* ($4\times 6$) vers la droite pour faire passer la colonne de *2 Petits Portraits* ($2\times 3$) à sa gauche, ou décaler un *Grand Paysage* ($6\times 4$) vers le haut/bas pour l'encadrer entre *2 Petits Paysages* en haut et *2 Petits Paysages* en bas.
  - **Division en 4 (`⊞ ×4`)** : tout *Grand Paysage* ($6\times 4$) peut être divisé en *4 Petits Paysages* ($3\times 2$), et tout *Grand Portrait* ($4\times 6$) peut être divisé en *4 Petits Portraits* ($2\times 3$) sur exactement la même emprise géométrique.
  - **Fusion en Grand format (`⬜ Grand`)** : tout carré $2\times 2$ de *4 Petits Paysages* ($3\times 2$) ou de *4 Petits Portraits* ($2\times 3$) peut être fusionné à nouveau en *1 Grand Paysage* ($6\times 4$) ou *1 Grand Portrait* ($4\times 6$).
  - Déclinaison complète sur [`Admin/Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue) et [`Admin/Standalone/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Standalone/Index.vue).
- [x] **Architecture par Lignes Harmoniques Vides, Slots & Bac de Photos en Attente (Dock)** :
  - **Lignes harmoniques créées vides par défaut** : l'ajout d'une ligne génère désormais une structure géométrique avec des emplacements réservés vides (`photo: null`), sans jamais aspirer ou réassigner les photos existantes contre la volonté de l'utilisateur.
  - **Bac de photos en attente (Dock)** : affichage des clichés non encore affectés dans un bandeau supérieur clair avec badges d'orientation (*Paysage* / *Portrait*), glisser-déposer intuitif vers n'importe quel emplacement libre ou barre de ligne, et bouton de remplissage automatique intelligent.
  - **Glisser-déposer flexible & permutation (Swap)** :
    - Glisser une photo vers un slot vide : prise d'emplacement immédiate avec ajustement automatique au format du slot.
    - Glisser vers un slot déjà occupé : permutation instantanée (*Swap*) entre les deux clichés sans casser la composition.
    - Glisser vers l'en-tête de ligne : assignation automatique au premier slot disponible de cette ligne.
    - Bouton détacher (`↩`) sur chaque cliché et zone de drop sur le dock pour retirer une photo d'une ligne vers le bac d'attente.
  - **Grande modale de composition ultra-large (1600px)** :
    - Agrandissement généreux de la modale en largeur et en hauteur, avec zone de prévisualisation centrée de 145px et grille filaire étirée à 15px par unité de rangée (au lieu de 9px), respectant fidèlement les proportions naturelles des clichés.
    - Différenciation chromatique immédiate (bleu azur pour les paysages, violet néon pour les portraits), typographies nettes avec ombrage et sans débordement de texte.
  - **Persistance et validation backend intégrales** : prise en charge et mise à jour de `span_cols` et `span_rows` aux côtés de `col_start`, `row_start` et `display_order` dans [`PhotoController.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/app/Http/Controllers/Admin/PhotoController.php) et [`StandalonePhotoController.php`](file:///d:/Code/portfolio-sanchez-florian-photographe/app/Http/Controllers/Admin/StandalonePhotoController.php).
  - Déclinaison complète et harmonisée sur [`Admin/Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue) et [`Admin/Standalone/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Standalone/Index.vue).
- [x] **Grande Modale Aérée de Lignes Harmoniques & Épure de l'Interface WYSIWYG** :
  - **Nettoyage de l'en-tête d'administration** : suppression des boutons superflus (*Structures Harmoniques*, *Combler les vides*, basculeur *Mosaïque en direct / Grille compacte*). L'administration fonctionne désormais exclusivement en mode mosaïque réelle WYSIWYG interactive.
  - **Grande modale de choix de ligne (`<AdminModal>` 880px)** : remplacement du dropdown popover trop étroit par une modale spacieuse et lisible sans défilement excessif.
  - **Aperçus géométriques filaires 12 colonnes nets** : affichage en cartes aérées avec badges détaillant le nombre et types de clichés requis.
  - Déclinaison harmonisée sur [`Admin/Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue) et [`Admin/Standalone/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Standalone/Index.vue).
- [x] **Ajustements Identité, Spécialités Réelles (Animaux, Paysages, Voitures, Motos), Boutons & Instagram** :
  - **Typographie du logo Navbar** : harmonisation de `FS` et `Florian Sanchez` avec la police [`Playfair Display`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/css/app.css#L747-L770) pour faire un écho immédiat avec le titre d'accueil.
  - **Boutons & Contraste idéal** : refonte de `.btn-primary` (« Découvrir mes projets », « Envoyer le message ») avec un dégradé bleu-violet signature lumineux et équilibré, éliminant les contrastes excessifs ou écrasés tout en s'accordant parfaitement avec `.btn-accent` (« Prendre contact »).
  - **Spécialités authentiques de Florian** : suppression du badge sur la photo et remplacement des textes de portrait par les 4 vrais domaines de Florian : **Animaux** ([`PhPawPrint`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Home.vue#L218-L225)), **Paysages** ([`PhMountains`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Home.vue#L226-L233)), **Automobile** ([`PhCar`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Home.vue#L234-L241)) et **Motos** ([`PhMotorcycle`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Home.vue#L242-L249)), avec textes et citation d'intention adaptés.
  - **Badge officiel Instagram `@ascorio17`** : intégration du badge cliquable officiel vers [instagram.com/ascorio17/](https://www.instagram.com/ascorio17/) dans la page de contact ([`Contact.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Contact.vue#L80-L95)) et la page À propos ([`About.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/About.vue#L64-L78)), ainsi qu'en base de données dans `site_settings`.
- [x] **Refonte typographique artistique, boutons « nuit mate & soyeuse » et enrichissement de la section À propos** :
  - Métamorphose du titre « Florian Sanchez » ([`Home.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Home.vue)) : abandon de la typo sans-serif lourde en graisse 800 pour la somptueuse typographie sérif [`Playfair Display`](https://fonts.google.com/specimen/Playfair+Display) en poids 500 noble et sculpté avec léger tracking aéré et lueur nocturne douce.
  - Harmonisation éditoriale des grands titres de sections (`h1`, `h2`) avec `Playfair Display` tout en préservant la neutralité fonctionnelle de l'administration.
  - Remplacement complet des emojis restants sur l'ensemble de la vitrine publique ([`Contact.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Contact.vue), [`Projects.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Projects.vue), [`ProjectShow.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/ProjectShow.vue), [`About.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/About.vue), [`Gallery.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Public/Gallery.vue)) au profit d'icônes Phosphor officielles.
- [x] **Intégration PixiJS v8 & Toile WebGL interactive** :
  - Nouveau composant universel [`PixiBackground.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/PixiBackground.vue) haute performance (`powerPreference: "low-power"`).
  - Mode *Bokeh Vitrine* : grands cercles ambiants épurés aux couleurs de la charte (#293ecc / #9d4edd), sans reflets blancs artificiels ni cercles concentriques surchargés, animés d'une dérive fluide, d'une respiration douce, d'une parallaxe au scroll et d'une déstabilisation/répulsion élastique réactive au passage de la souris.
  - Mode *Admin Constellation* : fond ambiant discret pour le back-office avec particules de poussières de studio et liens énergétiques doux.
  - Gestion automatique de la mise en pause lors de la réduction ou de la perte de focus de l'onglet.
- [x] **Suppression intégrale des emojis au profit de `@phosphor-icons/vue`** :
  - Remplacement de tous les emojis par la suite Phosphor officielle dans l'admin ([`AuthenticatedLayout.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Layouts/AuthenticatedLayout.vue), [`Dashboard.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Dashboard.vue), [`Project/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Index.vue), [`Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue), [`Category/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Category/Index.vue), [`Contact/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Contact/Index.vue), [`Standalone/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Standalone/Index.vue), [`AdminModal.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/AdminModal.vue), [`ConfirmModal.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/ConfirmModal.vue)).
  - Remplacement des icônes d'orientation par les ratios géométriques professionnels `3:2` et `2:3`.
- [x] **Refonte cinématique de l'ouverture des images ([`PhotoLightbox.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/PhotoLightbox.vue))** :
  - Effet d'ouverture de diaphragme focale avec flash de studio progressif et flou optique dynamique.
  - Halo d'exposition lumineux derrière le cliché.
  - Viseur appareil photo (HUD) indiquant le mode RAW et le numéro du cliché (`RAW • 04 / 28`).
  - Commandes complètes Phosphor (`PhX`, `PhCaretLeft`, `PhCaretRight`, `PhCornersOut`, `PhInstagramLogo`).
  - Prise en charge du mode plein écran (`F`) et du balayage tactile mobile (Touch Swipe).
- [x] **Sublimation du trait signature bleu-violet de la Navbar** :
  - Balayage lumineux avec aura néon dégradée sous les liens de navigation.
  - Déclinaison de l'effet sous forme d'indicateur latéral lumineux sur la barre d'administration.
  - Soulignement d'accentuation sur les titres de sections.
- [x] **Animations de défilement immersives ([`ScrollReveal.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Components/ScrollReveal.vue))** :
  - Transition avec adoucissement de profondeur focale (`blur(6px)` vers `blur(0px)`) et échelle dynamique.
  - Nettoyage automatique des propriétés GSAP (`clearProps`) pour ne pas entraver les survols CSS des cartes.
- [x] Structure de base de la vitrine photographique
- [x] Organisation par thématiques / séries de photos
- [x] **Système de mosaïque photographique modulaire (12 colonnes)** :
  - Respect strict des ratios capteur natifs (3:2 pour les paysages, 2:3 pour les portraits) sans aucun rognage d'image.
  - Résolution définitive du décalage vertical d'alignement (conflit GSAP `translate` / transitions CSS éliminé avec `clearProps`).
  - Échelle des formats ajustée et divisée par deux pour une densité visuelle idéale :
    - *Petit Paysage* (3×2, quart d'écran) & *Grand Paysage* (6×4, demi-écran).
    - *Petit Portrait* (2×3, sixième d'écran) & *Grand Portrait* (4×6, tiers d'écran).
  - Détection automatique de l'orientation de l'image (paysage / portrait) dès le téléversement côté backend.
- [x] **Interface d'administration interactive (WYSIWYG)** :
  - Vue « Mosaïque en direct » temps réel reproduisant fidèlement le rendu public du site.
  - Drag & Drop interactif directement dans la mosaïque avec réordonnancement optimiste réactif.
  - Sélecteurs de formats instantanés à 4 options claires : *Petit Paysage*, *Grand Paysage*, *Petit Portrait*, *Grand Portrait*.
  - Barre d'action et sélecteurs de format doux au survol pour garder les clichés dégagés et lisibles.
  - Mode commutable avec la « Grille compacte ».
- [x] **Système d'importation par lots automatiques (Chunked / Batch Upload)** :
  - Découpage automatique et transparent des téléversements volumineux (ex: 60+, 100+ photos) en petits paquets séquentiels (max 15 photos et 35 Mo par lot).
  - Contournement sécurisé de la limite PHP native (`max_file_uploads = 20` et `post_max_size`) sans risque de blocage serveur.
  - Conservation rigoureuse de l'ordre d'origine de sélection de chaque photo (`display_order` continu garanti).
  - Barre de progression animée en temps réel avec compteurs dynamiques (photos envoyées, lot en cours, pourcentage d'avancement).
  - Intégré dans l'édition des projets ([`Admin/Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue)) et dans la galerie vrac ([`Admin/Standalone/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Standalone/Index.vue)).
- [x] **Actions groupées & sélection multiple (Bulk Actions)** :
  - Cases à cocher individuelles sur chaque carte (mosaïque en direct & grille compacte) + case globale « Tout sélectionner ».
  - Barre d'outils flottante / sticky avec compteur dynamique de photos sélectionnées.
  - Actions instantanées : attribution d'un format en masse (*Petit/Grand Paysage/Portrait*), statut coup de cœur (*Star*), tag Instagram, catégorie (pour les photos en vrac) et suppression groupée avec modale de confirmation.
- [x] **Positionnement 2D libre en mosaïque (`col_start` / `row_start`) & Adaptation Responsive** :
  - Migration de base de données ajoutant les coordonnées 2D d'ancrage (`col_start`, `row_start`) dans la table `photos`.
  - Résolution du problème de réorganisation dense : les photos restent à leur emplacement 2D exact (en-dessous ou à côté d'une photo cible) au lieu d'être aspirées par les trous du flux automatique.
  - Adaptation responsive intelligente :
    - **Desktop (12 colonnes)** : respect strict des coordonnées 2D (`col_start` / `row_start`) pour reproduire fidèlement la composition artistique choisie dans le back-office.
    - **Tablette (6 colonnes)** & **Mobile (1 colonne)** : désactivation automatique et transparente des coordonnées 2D absolues pour basculer en flux fluide, évitant tout débordement horizontal ou espace vide disproportionné sur smartphone.
  - Résolution de l'écrasement en carré 1×1 : correction de la syntaxe CSS Grid (`grid-column: X / span W` et `grid-row: Y / span H`) pour préserver l'envergure des formats et l'accessibilité des boutons d'actions.
- [x] **Déplacement dynamique en direct style widgets Android / iOS** :
  - Réorganisation fluide et instantanée des photos dès le survol (`@dragenter`) pendant le maintien de la souris.
  - Décalage visuel en temps réel des photos environnantes pour faire immédiatement de la place au nouvel emplacement.
  - Feedback visuel clair (effet d'ombre, halo et mise en avant de la photo déplacée).
  - Sauvegarde automatique en arrière-plan au relâchement (`@dragend`) sans rechargement de page.
- [x] **Architecture par Lignes Harmoniques & Blocs de composition 100% étanches** :
  - **Structure en bandes indépendantes (`.admin-composition-row-block`)** : remplacement du flux dense par des lignes harmoniques autonomes munies de leur propre sous-grille 12 colonnes (`.admin-mosaic-row-grid`), éliminant définitivement les escaliers et les chevauchements involontaires où une photo passe sous une autre.
  - **Déplacement et inversion de lignes entières** :
    - Poignée de glisser-déposer dédiée (`⠿`) sur chaque en-tête de ligne pour réordonner des tranches complètes à la souris.
    - Commandes instantanées `▲ Monter`, `▼ Descendre` et `⇄ Inverser` (effet miroir gauche/droite automatique sans altérer les formats).
    - Bouton `+ Insérer ligne` intégré à chaque bandeau et bouton `➕ Ajouter une ligne harmonique` en bas de mosaïque (type tableur).
  - **Modale de modèles géométriques avec aperçus filaires 12 colonnes fidèles** : choix visuel instantané d'une structure mathématique parfaite sans icônes ni noms trompeurs.
  - **Drag & drop interne par permutation de slots (Swap propre)** : lâcher une photo sur un emplacement échange directement les coordonnées et formats entre les deux clichés sans bousculer le reste de la ligne.
  - **Catalogue exhaustif et dédoublonné des 10 archétypes géométriques purs** :
    1. *4 Petits Paysages* ($3\times 2$, $H=2$).
    2. *6 Petits Portraits* ($2\times 3$, $H=3$).
    3. *2 Grands Paysages* ($6\times 4$, $H=4$).
    4. *3 Grands Portraits* ($4\times 6$, $H=6$).
    5. *1 Grand Paysage + 4 Petits Paysages* ($H=4$).
    6. *1 G. Paysage + 1 G. Portrait + 2 P. Paysages + 2 P. Portraits* ($H=6$).
    7. *4 Petits Paysages + 6 Petits Portraits alternés* ($H=5$).
    8. *2 G. Paysages + 2 P. Paysages + 6 P. Portraits* ($H=8$).
    9. *2 G. Paysages + 2 P. Paysages + 1 G. Portrait + 2 P. Portraits* ($H=8$).
    10. *1 Grand Portrait + 8 Petits Portraits* ($H=6$).
  - Intégré et synchronisé dans l'édition de projet ([`Admin/Project/Edit.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Project/Edit.vue)) et la galerie autonome ([`Admin/Standalone/Index.vue`](file:///d:/Code/portfolio-sanchez-florian-photographe/resources/js/Pages/Admin/Standalone/Index.vue)).
- [x] **Sélecteur de modèles en Dropdown Popover discret (Style sélecteur d'emoji)** :
  - Remplacement de la grande modale intrusive par un mini-menu popover contextuel ancré sous le bouton `+ Insérer ligne` et au-dessus de `➕ Ajouter une ligne harmonique`.
  - Aperçus géométriques en vignettes carrées filaires 12 colonnes avec badges du nombre de photos nécessaires.
  - Clic en dehors (click-outside) et touche de fermeture rapide (`✕`) pour une ergonomie optimale sans interruption du flux de travail.





