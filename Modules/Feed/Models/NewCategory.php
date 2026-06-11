<?php

namespace Modules\Feed\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Feed\Database\Factories\NewCategoryFactory;
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
        'parent_id'
    ];

    protected static function newFactory(): NewCategoryFactory
    {
        return NewCategoryFactory::new();
    }

    public function parent()
    {
        return $this->belongsTo(NewCategory::class, 'parent_id');
    }
}
