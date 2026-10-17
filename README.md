# 📷 Portfolio Photographe — Florian Sanchez

Galerie photographique mettant en valeur les clichés et séries d'art de Florian Sanchez.

---

## 🛠️ Stack Technique

- **Frontend / Fullstack** : Vue.js 3, Tailwind CSS, Vite (ou Laravel Inertia)
- **Rendu & Galeries** : Affichage optimisé des images, PixiJS pour les transitions WebGL

---

## 💻 1. Lancement en Local

### Prérequis
- Node.js 20+ & npm (et PHP 8.2+ si backend Laravel)

### Installation
```bash
npm install
npm run dev
```

---

## 🚀 2. Déploiement sur Serveur VPS (Debian 13)

### 2.1 Build et transfert
```bash
# Compilation statique
npm run build

# Déploiement dans /var/www/portfolio-photo
```

### 2.2 Configuration Nginx (`/etc/nginx/sites-available/portfolio-photo`)
```nginx
server {
    listen 80;
    server_name photo.sanchez-florian.fr;
    root /var/www/portfolio-photo/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~* \.(?:ico|css|js|gif|jpe?g|png|webp|avif|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/portfolio-photo /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d photo.sanchez-florian.fr
```
