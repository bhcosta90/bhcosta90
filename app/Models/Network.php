<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

final class Network extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    public const CACHE_KEY = '20250523162700';

    public function histories(): HasMany
    {
        return $this->hasMany(NetworkHistory::class);
    }
}
