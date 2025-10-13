<?php 
// app/Services/Auth/AuthService.php
namespace App\Services\Auth;

use App\Models\User;
use App\Models\Producer;
use App\Models\Buyer;
use App\Models\Transporter;
use App\Enums\UserRole;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\AccountSuspendedException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private PhoneVerificationService $phoneVerificationService
    ) {}

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function register(array $data): array
    {
        DB::beginTransaction();

        try {
            // Créer utilisateur
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'is_active' => true,
            ]);

            // Créer profil selon le rôle
            $profile = $this->createProfile($user, $data);

            // Générer code de vérification
            $verification = $this->phoneVerificationService->generate($user->phone);

            // Envoyer SMS de vérification
            $this->phoneVerificationService->sendSMS($user->phone, $verification->code);

            // Générer token API
            $token = $user->createToken('auth_token', [$user->role->value])->plainTextToken;

            DB::commit();

            return [
                'user' => $user->fresh(),
                'profile' => $profile,
                'token' => $token,
                'requires_verification' => true,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Connexion utilisateur
     */
    public function login(string $phone, string $password, ?string $fcmToken = null): array
    {
        $user = User::where('phone', $phone)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        if (!$user->is_active) {
            throw new AccountSuspendedException();
        }

        // Mettre à jour FCM token
        if ($fcmToken) {
            $user->update(['fcm_token' => $fcmToken]);
        }

        // Générer token avec abilities basés sur le rôle
        $abilities = $this->getTokenAbilities($user);
        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        return [
            'user' => $user,
            'profile' => $user->getProfile(),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Déconnexion
     */
    public function logout(User $user, bool $allDevices = false): void
    {
        if ($allDevices) {
            $user->tokens()->delete();
        } else {
            $user->currentAccessToken()->delete();
        }
    }

    /**
     * Créer profil selon le rôle
     */
    private function createProfile(User $user, array $data)
    {
        return match($user->role) {
            UserRole::PRODUCER => Producer::create([
                'user_id' => $user->id,
                'location_id' => $data['location_id'],
                'farm_name' => $data['farm_name'] ?? null,
                'farm_address' => $data['farm_address'] ?? null,
                'years_experience' => $data['years_experience'] ?? null,
            ]),
            
            UserRole::BUYER => Buyer::create([
                'user_id' => $user->id,
                'location_id' => $data['location_id'],
                'business_name' => $data['business_name'],
                'business_type' => $data['business_type'] ?? 'restaurant',
                'delivery_address' => $data['delivery_address'],
                'stars_rating' => $data['stars_rating'] ?? null,
            ]),
            
            UserRole::TRANSPORTER => Transporter::create([
                'user_id' => $user->id,
                'vehicle_type' => $data['vehicle_type'],
                'vehicle_registration' => $data['vehicle_registration'],
                'driver_license_number' => $data['driver_license_number'],
                'max_capacity_kg' => $data['max_capacity_kg'],
                'has_refrigeration' => $data['has_refrigeration'] ?? false,
                'service_areas' => $data['service_areas'] ?? [],
            ]),
            
            default => null,
        };
    }

    /**
     * Obtenir permissions du token selon le rôle
     */
    private function getTokenAbilities(User $user): array
    {
        return match($user->role) {
            UserRole::PRODUCER => [
                'producer',
                'offers:create',
                'offers:update',
                'offers:delete',
                'orders:view',
                'orders:confirm',
            ],
            
            UserRole::BUYER => [
                'buyer',
                'cart:manage',
                'orders:create',
                'orders:view',
                'orders:cancel',
                'payments:initiate',
            ],
            
            UserRole::TRANSPORTER => [
                'transporter',
                'deliveries:view',
                'deliveries:accept',
                'deliveries:update',
                'deliveries:complete',
            ],
            
            UserRole::ADMIN => ['*'],
            
            default => [],
        };
    }

    /**
     * Rafraîchir token
     */
    public function refreshToken(User $user): string
    {
        // Révoquer ancien token
        $user->currentAccessToken()->delete();

        // Créer nouveau token
        $abilities = $this->getTokenAbilities($user);
        return $user->createToken('auth_token', $abilities)->plainTextToken;
    }

    /**
     * Changer mot de passe
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new InvalidCredentialsException();
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Révoquer tous les tokens pour forcer reconnexion
        $user->tokens()->delete();
    }

    /**
     * Réinitialiser mot de passe
     */
    public function resetPassword(string $phone, string $verificationCode, string $newPassword): void
    {
        // Vérifier code
        $verification = $this->phoneVerificationService->verify($phone, $verificationCode);

        if (!$verification) {
            throw new \Exception('Code de vérification invalide');
        }

        // Trouver utilisateur
        $user = User::where('phone', $phone)->firstOrFail();

        // Changer mot de passe
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Révoquer tous les tokens
        $user->tokens()->delete();
    }
}