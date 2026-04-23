<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'is_active', 'avatar_path', 'address'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * URL relatif ke file di disk public (lewat symlink public/storage).
     * Dipakai alih-alih Storage::url() agar gambar tetap benar saat APP_URL beda host/port
     * dengan yang dipakai browser (mis. localhost vs 127.0.0.1:8000).
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! filled($this->avatar_path)) {
                    return null;
                }

                $path = str_replace('\\', '/', (string) $this->avatar_path);

                if (! Storage::disk('public')->exists($path)) {
                    return null;
                }

                return '/storage/'.ltrim($path, '/');
            },
        );
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }
}
