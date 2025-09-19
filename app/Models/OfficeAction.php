<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeAction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'office_id',
        'action_type_id',
        'user_id',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function actionType(): BelongsTo
    {
        return $this->belongsTo(ActionType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
