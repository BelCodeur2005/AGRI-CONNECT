<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Agri-Connect Application Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration centrale pour l'application Agri-Connect Cameroun
    |
    */

    // =================================================================
    // COMMISSIONS & FRAIS PLATEFORME
    // =================================================================
    
    'platform_commission' => env('PLATFORM_COMMISSION', 7), // 7% par défaut
    'platform_fee_fixed' => env('PLATFORM_FEE_FIXED', 0), // Frais fixe additionnel
    'min_commission_amount' => env('MIN_COMMISSION_AMOUNT', 100), // Commission minimum en FCFA
    
    // =================================================================
    // FRAIS DE LIVRAISON
    // =================================================================
    
    'delivery' => [
        'base_fee' => env('DELIVERY_BASE_FEE', 1000), // Frais de base en FCFA
        'fee_per_km' => env('DELIVERY_FEE_PER_KM', 50), // FCFA par km
        'fee_per_kg' => env('DELIVERY_FEE_PER_KG', 5), // FCFA par kg
        'free_delivery_threshold' => env('FREE_DELIVERY_THRESHOLD', 50000), // Livraison gratuite au-dessus de ce montant
        'max_delivery_distance' => env('MAX_DELIVERY_DISTANCE', 100), // km maximum
        'guarantee_hours' => env('DELIVERY_GUARANTEE_HOURS', 48), // Garantie livraison en heures
        'consolidation_radius' => env('DELIVERY_CONSOLIDATION_RADIUS', 5), // km pour grouper les livraisons
    ],

    // =================================================================
    // GESTION DES COMMANDES
    // =================================================================
    
    'orders' => [
        'min_order_amount' => env('MIN_ORDER_AMOUNT', 5000), // Montant minimum commande en FCFA
        'max_items_per_order' => env('MAX_ITEMS_PER_ORDER', 50), // Nombre max d'articles par commande
        'confirmation_timeout_hours' => env('ORDER_CONFIRMATION_TIMEOUT', 24), // Délai pour producteur confirme
        'cancellation_allowed_hours' => env('ORDER_CANCELLATION_HOURS', 12), // Délai annulation acheteur
        'auto_complete_days' => env('ORDER_AUTO_COMPLETE_DAYS', 7), // Complétion auto après livraison
        'dispute_window_days' => env('ORDER_DISPUTE_WINDOW_DAYS', 3), // Délai pour ouvrir litige
    ],

    // =================================================================
    // GESTION DES OFFRES
    // =================================================================
    
    'offers' => [
        'max_photos' => env('OFFER_MAX_PHOTOS', 5), // Nombre max de photos
        'min_quantity' => env('OFFER_MIN_QUANTITY', 1), // Quantité minimum
        'max_validity_days' => env('OFFER_MAX_VALIDITY_DAYS', 30), // Durée max validité offre
        'expiration_reminder_days' => env('OFFER_EXPIRATION_REMINDER', 3), // Rappel avant expiration
        'low_stock_threshold' => env('OFFER_LOW_STOCK_THRESHOLD', 10), // Alerte stock faible
        'reservation_timeout_minutes' => env('OFFER_RESERVATION_TIMEOUT', 30), // Durée réservation panier
        'auto_expire_enabled' => env('OFFER_AUTO_EXPIRE', true), // Expiration automatique
    ],

    // =================================================================
    // PAIEMENTS
    // =================================================================
    
    'payments' => [
        'escrow_hold_hours' => env('PAYMENT_ESCROW_HOLD_HOURS', 72), // Durée séquestre (72h)
        'auto_release_after_delivery_hours' => env('PAYMENT_AUTO_RELEASE_HOURS', 24), // Libération auto après livraison
        'refund_processing_days' => env('PAYMENT_REFUND_DAYS', 7), // Délai traitement remboursement
        'split_minimum_amount' => env('PAYMENT_SPLIT_MIN', 1000), // Montant min pour split
        'transaction_timeout_minutes' => env('PAYMENT_TIMEOUT', 15), // Timeout transaction
        
        // Mobile Money
        'momo_callback_timeout' => env('MOMO_CALLBACK_TIMEOUT', 120), // Timeout callback en secondes
        'max_momo_amount' => env('MAX_MOMO_AMOUNT', 500000), // Montant max Mobile Money
        'momo_retry_attempts' => env('MOMO_RETRY_ATTEMPTS', 3), // Tentatives en cas d'échec
    ],

    // =================================================================
    // NOTIFICATIONS
    // =================================================================
    
    'notifications' => [
        'sms_enabled' => env('NOTIFICATIONS_SMS_ENABLED', true),
        'email_enabled' => env('NOTIFICATIONS_EMAIL_ENABLED', true),
        'push_enabled' => env('NOTIFICATIONS_PUSH_ENABLED', true),
        
        'sms_provider' => env('SMS_PROVIDER', 'nexah'), // nexah, twilio, etc.
        'sms_sender_name' => env('SMS_SENDER_NAME', 'AgriConnect'),
        
        'fcm_server_key' => env('FCM_SERVER_KEY'),
        
        // Fréquence notifications
        'order_updates' => true, // Notifications mises à jour commande
        'payment_updates' => true, // Notifications paiements
        'delivery_tracking' => true, // Suivi livraison temps réel
        'marketing_notifications' => env('MARKETING_NOTIFICATIONS', false), // Notifs marketing
    ],

    // =================================================================
    // VÉRIFICATIONS & CERTIFICATIONS
    // =================================================================
    
    'verification' => [
        'phone_verification_required' => env('PHONE_VERIFICATION_REQUIRED', true),
        'phone_code_length' => env('PHONE_CODE_LENGTH', 6),
        'phone_code_expiry_minutes' => env('PHONE_CODE_EXPIRY', 10),
        'max_verification_attempts' => env('MAX_VERIFICATION_ATTEMPTS', 5),
        
        'producer_verification_required' => env('PRODUCER_VERIFICATION_REQUIRED', false),
        'id_card_required' => env('ID_CARD_REQUIRED', false),
        
        'transporter_certification_required' => env('TRANSPORTER_CERTIFICATION_REQUIRED', true),
        'driver_license_required' => env('DRIVER_LICENSE_REQUIRED', true),
    ],

    // =================================================================
    // NOTES & ÉVALUATIONS
    // =================================================================
    
    'ratings' => [
        'min_rating' => 1,
        'max_rating' => 5,
        'min_orders_for_rating' => env('MIN_ORDERS_FOR_RATING', 1), // Commandes min pour noter
        'featured_rating_threshold' => env('FEATURED_RATING_THRESHOLD', 4.5), // Note pour être en avant
        'bad_rating_threshold' => env('BAD_RATING_THRESHOLD', 3.0), // Seuil mauvaise note
        'rating_window_days' => env('RATING_WINDOW_DAYS', 14), // Délai pour noter après livraison
    ],

    // =================================================================
    // CERTIFICATION TRANSPORTEURS
    // =================================================================
    
    'transporter_certification' => [
        'levels' => [
            'bronze' => [
                'min_deliveries' => 10,
                'min_rating' => 3.5,
                'bonus_percentage' => 0, // Pas de bonus
            ],
            'silver' => [
                'min_deliveries' => 50,
                'min_rating' => 4.0,
                'bonus_percentage' => 5, // +5% sur chaque livraison
            ],
            'gold' => [
                'min_deliveries' => 200,
                'min_rating' => 4.5,
                'bonus_percentage' => 10, // +10%
            ],
            'platinum' => [
                'min_deliveries' => 500,
                'min_rating' => 4.8,
                'bonus_percentage' => 15, // +15%
            ],
        ],
        'recalculation_frequency' => 'weekly', // Recalcul niveau certification
    ],

    // =================================================================
    // SÉCURITÉ & LIMITES
    // =================================================================
    
    'security' => [
        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'login_lockout_minutes' => env('LOGIN_LOCKOUT_MINUTES', 15),
        'password_expiry_days' => env('PASSWORD_EXPIRY_DAYS', 90),
        'session_lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'token_expiry_days' => env('TOKEN_EXPIRY_DAYS', 30), // API tokens
        
        'max_active_sessions' => env('MAX_ACTIVE_SESSIONS', 3), // Sessions simultanées
        'suspicious_activity_threshold' => env('SUSPICIOUS_ACTIVITY_THRESHOLD', 10),
    ],

    // =================================================================
    // UPLOADS & STOCKAGE
    // =================================================================
    
    'uploads' => [
        'max_file_size' => env('MAX_FILE_SIZE', 5120), // Ko (5 Mo)
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx'],
        
        'paths' => [
            'profiles' => 'profiles',
            'offers' => 'offers',
            'products' => 'products',
            'delivery_proofs' => 'delivery-proofs',
            'disputes' => 'disputes',
            'documents' => 'documents',
            'id_cards' => 'id-cards',
            'licenses' => 'licenses',
        ],
    ],

    // =================================================================
    // BUSINESS RULES
    // =================================================================
    
    'business' => [
        'buyer_types' => [
            'individual' => 'Particulier',
            'restaurant' => 'Restaurant',
            'hotel' => 'Hôtel',
            'supermarket' => 'Supermarché',
            'retailer' => 'Détaillant',
            'wholesaler' => 'Grossiste',
            'processor' => 'Transformateur',
            'exporter' => 'Exportateur',
        ],
        
        'premium_buyer_threshold' => env('PREMIUM_BUYER_THRESHOLD', 1000000), // FCFA dépensés
        'premium_buyer_min_orders' => env('PREMIUM_BUYER_MIN_ORDERS', 10),
        
        'producer_verification_badges' => [
            'verified' => 'Producteur Vérifié',
            'organic_certified' => 'Certification Bio',
            'fair_trade' => 'Commerce Équitable',
            'top_rated' => 'Meilleur Vendeur',
        ],
    ],

    // =================================================================
    // ANALYTICS & REPORTING
    // =================================================================
    
    'analytics' => [
        'tracking_enabled' => env('ANALYTICS_ENABLED', true),
        'retention_days' => env('ANALYTICS_RETENTION_DAYS', 365), // Durée conservation données
        'anonymize_after_days' => env('ANONYMIZE_AFTER_DAYS', 90), // Anonymisation
        
        'reports' => [
            'daily_summary_time' => '08:00', // Heure envoi rapport journalier
            'weekly_summary_day' => 'monday', // Jour rapport hebdomadaire
            'monthly_summary_day' => 1, // Jour rapport mensuel
        ],
    ],

    // =================================================================
    // MAINTENANCE & SYSTÈME
    // =================================================================
    
    'system' => [
        'maintenance_mode' => env('MAINTENANCE_MODE', false),
        'maintenance_message' => env('MAINTENANCE_MESSAGE', 'Maintenance en cours. Retour bientôt.'),
        
        'auto_cleanup_enabled' => env('AUTO_CLEANUP_ENABLED', true),
        'cleanup_schedule' => 'daily', // Nettoyage données temporaires
        
        'cache_ttl' => env('CACHE_TTL', 3600), // Durée cache en secondes
        'api_rate_limit' => env('API_RATE_LIMIT', 60), // Requêtes par minute
        'api_rate_limit_premium' => env('API_RATE_LIMIT_PREMIUM', 120), // Pour utilisateurs premium
    ],

    // =================================================================
    // FEATURES FLAGS (Activer/Désactiver fonctionnalités)
    // =================================================================
    
    'features' => [
        'cart_enabled' => env('FEATURE_CART', true),
        'favorites_enabled' => env('FEATURE_FAVORITES', true),
        'disputes_enabled' => env('FEATURE_DISPUTES', true),
        'ratings_enabled' => env('FEATURE_RATINGS', true),
        'delivery_tracking_enabled' => env('FEATURE_DELIVERY_TRACKING', true),
        'multi_producer_orders' => env('FEATURE_MULTI_PRODUCER', true),
        'payment_splits_enabled' => env('FEATURE_PAYMENT_SPLITS', true),
        'market_prices_enabled' => env('FEATURE_MARKET_PRICES', true),
        'producer_availability' => env('FEATURE_PRODUCER_AVAILABILITY', true),
        'delivery_groups' => env('FEATURE_DELIVERY_GROUPS', true),
    ],



    // =================================================================
    // CONTACTS & SUPPORT
    // =================================================================
    
    'support' => [
        'phone' => env('SUPPORT_PHONE', '+237651712856'),
        'email' => env('SUPPORT_EMAIL', 'support@agri-connect.cm'),
        'whatsapp' => env('SUPPORT_WHATSAPP', '+237651712856'),
        'hours' => '24/7',
        'response_time_hours' => 2, // Temps réponse moyen
    ],

    // =================================================================
    // LEGAL & COMPLIANCE
    // =================================================================
    
    'legal' => [
        'terms_version' => env('TERMS_VERSION', '1.0'),
        'privacy_version' => env('PRIVACY_VERSION', '1.0'),
        'cookies_enabled' => env('COOKIES_ENABLED', true),
        'gdpr_compliant' => env('GDPR_COMPLIANT', false), // Pour export éventuel
        'data_retention_years' => env('DATA_RETENTION_YEARS', 7),
    ],

];