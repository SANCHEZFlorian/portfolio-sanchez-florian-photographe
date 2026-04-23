# Florian Sanchez - Photographie

Portfolio professionnel de Florian Sanchez, photographe. Construit avec Laravel 13, Vue.js 3 et Inertia.js.

## 🚀 Lancement en Local

Pour lancer le projet sur votre machine locale, suivez ces étapes :

### Pré-requis
- PHP 8.3+
- Composer
- Node.js & NPM
- Une base de données MySQL

### Installation & Démarrage rapide
1. **Initialisation du projet :**
   ```bash
   composer setup
   ```
   *Cette commande installe les dépendances PHP/JS, crée le fichier .env, génère la clé d'application, lance les migrations et build les assets.*

2. **Lancer l'environnement de développement :**
   ```bash
   composer dev
   ```
   *Cette commande lance simultanément le serveur Laravel (`php artisan serve`), le compilateur Vite (`npm run dev`) et le processeur de files d'attente.*

---

## 🌐 Déploiement sur le Serveur

Pour mettre à jour ou lancer le projet sur un serveur de production :

### Mise à jour (Pull & Build)
Si le projet est déjà installé, utilisez ces commandes pour mettre à jour :
```bash
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Configuration Serveur
Le serveur doit pointer vers le répertoire `public/` de l'application. Assurez-vous que les permissions sur les dossiers `storage` et `bootstrap/cache` sont correctement configurées (écriture autorisée pour l'utilisateur web).

---

## 🛠️ Stack Technique
- **Backend :** [Laravel 13](https://laravel.com)
- **Frontend :** [Vue.js 3](https://vuejs.org) avec [Inertia.js](https://inertiajs.com)
- **Tooling :** [Vite](https://vitejs.dev)
- **Animations :** [GSAP](https://greensock.com/gsap/)
- **Gestion d'images :** [Intervention Image](https://image.intervention.io/)

## 📄 Licence
Ce projet est sous licence MIT.
