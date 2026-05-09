<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    public const STATUS_UNCHECKED = '未確認';
    public const STATUS_PRESENT = '出';
    public const STATUS_ABSENT = '欠';

    public const STATUS_LABELS = [
        self::STATUS_UNCHECKED => self::STATUS_UNCHECKED,
        self::STATUS_PRESENT => self::STATUS_PRESENT,
        self::STATUS_ABSENT => self::STATUS_ABSENT,
    ];

    protected $fillable = [
        'name',
        'company',
        'status',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? self::STATUS_UNCHECKED;
    }
}
