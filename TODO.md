# 📋 TODO — Portfolio Photographe (Florian Sanchez)

Suivi des galeries, optimisation des clichés et expérience visuelle.

---

## 🎯 En cours
- [ ] Sélection et compression optimisée des clichés haute résolution
- [ ] Effets de transition fluide / distorsion visuelle au survol des photos (PixiJS / WebGL)

## 📌 À venir
- [ ] Système de commande de tirages ou formulaire de devis de prestation
- [ ] Protection et filigrane léger des clichés présentés

## ✅ Terminé
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


