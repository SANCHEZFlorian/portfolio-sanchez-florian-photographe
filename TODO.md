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
