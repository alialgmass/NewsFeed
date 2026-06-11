<?php

namespace Modules\Feed\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Feed\Database\Factories\NewCategoryFactory;
use Modules\User\Models\InterestCategory;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable('title', 'description')]
class NewCategory extends Model
{
    use HasFactory, HasTranslations;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'parent_id',
    ];

    protected static function newFactory(): NewCategoryFactory
    {
        return NewCategoryFactory::new();
    }

    public function parent()
    {
        return $this->belongsTo(NewCategory::class, 'parent_id');
    }
    public function interests()
    {
        return $this->hasMany(InterestCategory::class, 'new_category_id');
    }
}
