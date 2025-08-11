<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasUlids;

    protected $fillable = [
        'code',
        'title',
        'electronic',
        'dissemination',
        'classification_id',
        'user_id',
        'office_id',
        'section_id',
        'source_id',
        'published_at',

    ];

    protected $casts = [
        'published_at' => 'datetime',
        'dissemination' => 'boolean',
        'electronic' => 'boolean',
    ];

    // public static function booted(): void{
    //     static::forceDeleting(function(self $document){
    //         $document->attachments->each->delete();
    //     });

    //     static::creating(function (self $document){
    //         $faker = fake()->unique();

    //         do{
    //             $codes = collect(range(1,10))->map(fn()=> $faker->bothify('??????####'))->toArray();

    //             $available =
    //         }
    //     })

    public function isDraft(): bool
    {
        return is_null($this->published_at);
    }
}
