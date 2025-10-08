# Agri-Connect API

## À propos
La solution numérique qui transforme l'agriculture camerounaise. Du champ à la table : Direct, Fiable, Louable.

## Fonctionnalités
- Authentification Sanctum (`app/Http/Controllers/Api/Auth/*`)
- Offres et commandes ([app/Models/Offer.php](cci:7://file:///d:/Bel/projets/projet_laravel/projet_agri_connect/agri-connect-api/app/Models/Offer.php:0:0-0:0), [app/Models/Order.php](cci:7://file:///d:/Bel/projets/projet_laravel/projet_agri_connect/agri-connect-api/app/Models/Order.php:0:0-0:0))
- Logistique & livraisons ([app/Models/Delivery.php](cci:7://file:///d:/Bel/projets/projet_laravel/projet_agri_connect/agri-connect-api/app/Models/Delivery.php:0:0-0:0))
- Litiges & soutien ([app/Models/Dispute.php](cci:7://file:///d:/Bel/projets/projet_laravel/projet_agri_connect/agri-connect-api/app/Models/Dispute.php:0:0-0:0))

## Architecture
- Laravel 11, PHP 8.2 ([composer.json](cci:7://file:///d:/Bel/projets/projet_laravel/projet_agri_connect/agri-connect-api/composer.json:0:0-0:0))
- Sanctum, Spatie Permission, Intervention Image
- Structure des dossiers ([app/](cci:7://file:///d:/Bel/projets/projet_laravel/projet_agri_connect/agri-connect-api/app:0:0-0:0), [database/migrations/](cci:7://file:///d:/Bel/projets/projet_laravel/projet_agri_connect/agri-connect-api/database/migrations:0:0-0:0), [routes/](cci:7://file:///d:/Bel/projets/projet_laravel/projet_agri_connect/agri-connect-api/routes:0:0-0:0))

## Prérequis
- PHP 8.2+
- Composer
- MySQL/PostgreSQL
- Node 18+ (pour Vite)

## Installation
```bash
git clone <repo>
cd agri-connect-api
composer install
cp .env.example .env
php artisan key:generate
# Configurer DB, stockage, services
php artisan migrate --seed
npm install
npm run build