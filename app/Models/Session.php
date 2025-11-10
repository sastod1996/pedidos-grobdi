<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Session extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'sessions';

    /**
     * The primary key type and incrementing behaviour.
     */
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_activity' => 'integer',
    ];

    /**
     * Relationship: session belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: return the last activity as a Carbon instance in the app timezone.
     */
    public function getLastActivityAtAttribute(): ?Carbon
    {
        if (! $this->last_activity) {
            return null;
        }

        return Carbon::createFromTimestamp($this->last_activity)
            ->setTimezone(config('app.timezone'));
    }

    /**
     * Accessor: determine if the session is still considered online.
     */
    public function getIsOnlineAttribute(): bool
    {
        $lastActivity = $this->last_activity_at;

        if (! $lastActivity) {
            return false;
        }

        $timeout = now()->subMinutes((int) config('session.lifetime', 120));

        return $lastActivity->greaterThanOrEqualTo($timeout);
    }

    /**
     * Accessor: human readable browser name parsed from the user agent.
     */
    public function getBrowserNameAttribute(): string
    {
        $agent = $this->normalizedUserAgent();

        if ($agent === '') {
            return 'Desconocido';
        }

        if (Str::contains($agent, 'edg')) {
            return 'Microsoft Edge';
        }

        if (Str::contains($agent, ['opr/', 'opera'])) {
            return 'Opera';
        }

        if (Str::contains($agent, ['chrome', 'crios']) && ! Str::contains($agent, ['edg', 'opr/'])) {
            return 'Google Chrome';
        }

        if (Str::contains($agent, 'firefox')) {
            return 'Mozilla Firefox';
        }

        if (Str::contains($agent, 'safari') && ! Str::contains($agent, ['chrome', 'crios'])) {
            return 'Safari';
        }

        if (Str::contains($agent, ['msie', 'trident'])) {
            return 'Internet Explorer';
        }

        return 'Desconocido';
    }

    /**
     * Accessor: human readable device class (mobile / tablet / desktop).
     */
    public function getDeviceTypeAttribute(): string
    {
        $agent = $this->normalizedUserAgent();

        if ($agent === '') {
            return 'Desconocido';
        }

        if ($this->isTablet($agent)) {
            return 'Tablet';
        }

        if ($this->isMobile($agent)) {
            return 'Móvil';
        }

        if (Str::contains($agent, ['windows', 'macintosh', 'linux', 'x11'])) {
            return 'Laptop/PC';
        }

        return 'Desconocido';
    }

    /**
     * Accessor: human readable operating system/platform name.
     */
    public function getPlatformNameAttribute(): string
    {
        $agent = $this->normalizedUserAgent();

        if ($agent === '') {
            return 'Desconocido';
        }

        if (Str::contains($agent, 'windows')) {
            return 'Windows';
        }

        if (Str::contains($agent, ['macintosh', 'mac os', 'macos'])) {
            return 'macOS';
        }

        if (Str::contains($agent, ['iphone', 'ipad', 'ipod', 'ios'])) {
            return 'iOS';
        }

        if (Str::contains($agent, ['android'])) {
            return 'Android';
        }

        if (Str::contains($agent, ['linux', 'ubuntu', 'debian'])) {
            return 'Linux';
        }

        return 'Desconocido';
    }

    /**
     * Determine whether the session belongs to a guest user.
     */
    public function getIsGuestAttribute(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Accessor: extract decoded payload data as an associative array.
     */
    public function getPayloadDataAttribute(): array
    {
        if (! $this->payload) {
            return [];
        }

        $raw = $this->payload;

        try {
            $raw = Crypt::decryptString($raw);
        } catch (DecryptException $e) {
            // Payload is not encrypted, continue with original value.
        } catch (\Throwable $e) {
            return [];
        }

        $decoded = base64_decode($raw, true);

        if ($decoded !== false) {
            $raw = $decoded;
        }

        $data = $this->attemptUnserialize($raw);

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * Accessor: expose payload data as pretty printed JSON for UI purposes.
     */
    public function getPayloadJsonAttribute(): string
    {
        $data = $this->payload_data;

        if (empty($data)) {
            return json_encode(new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Accessor: determine the user id stored within the payload.
     */
    public function getPayloadUserIdAttribute(): ?int
    {
        $data = $this->payload_data;

        if (empty($data)) {
            return null;
        }

        $flattened = Arr::dot($data);

        $candidates = collect([
            Arr::get($flattened, 'login_web'),
            Arr::get($flattened, 'user.id'),
            Arr::get($flattened, 'user_id'),
            Arr::get($flattened, 'auth.id'),
        ])->filter(function ($value) {
            return is_numeric($value);
        });

        if ($candidates->isNotEmpty()) {
            return (int) $candidates->first();
        }

        foreach ($flattened as $key => $value) {
            if (! is_numeric($value)) {
                continue;
            }

            if (Str::contains($key, ['login', 'user', 'auth']) && Str::endsWith($key, ['id', '_id'])) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * Accessor: human readable fallback for the user name.
     */
    public function getUserDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        if ($this->payload_user_id) {
            return 'Usuario ID ' . $this->payload_user_id;
        }

        return 'Invitado';
    }

    /**
     * Accessor: human readable fallback for the user email.
     */
    public function getUserDisplayEmailAttribute(): string
    {
        if ($this->user) {
            return $this->user->email;
        }

        if ($this->payload_user_id) {
            return 'ID relacionado ' . $this->payload_user_id;
        }

        return 'N/D';
    }

    /**
     * Attempt to unserialize the stored payload safely.
     */
    protected function attemptUnserialize(string $serialized): array|false
    {
        try {
            $data = unserialize($serialized, ['allowed_classes' => false]);

            if (is_array($data)) {
                return $data;
            }
        } catch (\Throwable $e) {
            try {
                $data = unserialize($serialized);

                if (is_array($data)) {
                    return $data;
                }
            } catch (\Throwable $e2) {
                return false;
            }

            return false;
        }

        return false;
    }

    /**
     * Normalise the user agent string for comparisons.
     */
    protected function normalizedUserAgent(): string
    {
        return Str::of((string) $this->user_agent)
            ->lower()
            ->trim()
            ->value();
    }

    /**
     * Rudimentary mobile detection.
     */
    protected function isMobile(string $agent): bool
    {
        return Str::contains($agent, [
            'mobile', 'iphone', 'ipod', 'blackberry', 'windows phone',
            'opera mini', 'opera mobi', 'android', 'webos', 'silk', 'fennec',
        ]);
    }

    /**
     * Rudimentary tablet detection.
     */
    protected function isTablet(string $agent): bool
    {
        return Str::contains($agent, [
            'ipad', 'tablet', 'kindle', 'silk', 'playbook', 'nexus 7', 'nexus 10',
        ]);
    }
}
