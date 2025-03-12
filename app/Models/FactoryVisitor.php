<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactoryVisitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'visit_date',
        'number_of_visitors',
        'purpose',
        'qr_code',
        'status'
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];
}
