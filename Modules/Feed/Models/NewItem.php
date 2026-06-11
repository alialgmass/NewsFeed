<?php

namespace Modules\Feed\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\User\Models\InterestCategory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;
use Modules\Feed\Database\Factories\NewItemFactory;
#[Translatable('title', 'description')]
class NewItem extends Model implements HasMedia
{
    use HasFactory,HasTranslations,InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'body',
        'published_at',
        'source',
        'new_category_id'
    ];

    protected static function newFactory(): NewItemFactory
    {
        return NewItemFactory::new();
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'source' => 'array',
        ];
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(NewCategory::class, 'new_category_id');
    }

}
