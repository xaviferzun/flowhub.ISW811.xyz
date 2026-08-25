<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Socialite\Facades\Socialite;

class ConnectedAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'webhook_url',
    ];

    /**
     * Never serializar los tokens en JSON/array,
     * aunque el modelo se devuelva accidentalmente en una respuesta.
     *
     * @var list<string>
     */
    protected $hidden = [
        'access_token',
        'refresh_token',
        'webhook_url',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'webhook_url' => 'encrypted',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Indica si el access token ya venció. Proveedores que no reportan
     * expiración (ej. GitHub con OAuth App clásica) guardan expires_at
     * en null, y por lo tanto nunca se consideran vencidos.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Devuelve un access token válido para usar contra la API del proveedor.
     * Si el token actual venció, lo refresca primero contra el proveedor
     * usando el refresh_token guardado, y persiste el resultado.
     *
     * @throws \RuntimeException si el token venció y no hay refresh_token disponible.
     */
    public function freshAccessToken(): string
    {
        if (! $this->isExpired()) {
            return $this->access_token;
        }

        if (! $this->refresh_token) {
            throw new \RuntimeException(
                "La conexión con {$this->provider} venció y no tiene refresh_token guardado; el usuario debe reconectarla desde /connections."
            );
        }

        $refreshed = Socialite::driver($this->provider)->refreshToken($this->refresh_token);

        $this->update([
            'access_token' => $refreshed->token,
            // Algunos proveedores no devuelven un refresh_token nuevo al refrescar
            // (asumen que el actual sigue siendo válido) — si no viene uno nuevo,
            // conservamos el que ya teníamos en vez de dejarlo en null.
            'refresh_token' => $refreshed->refreshToken ?? $this->refresh_token,
            'expires_at' => $refreshed->expiresIn ? now()->addSeconds($refreshed->expiresIn) : null,
        ]);

        return $this->access_token;
    }
}
