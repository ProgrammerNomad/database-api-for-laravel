<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'Social',
        'CompanyName',
        'Telephones',
        'Emails',
        'Titles',
        'State',
        'Postcode',
        'Country',
        'Vertical'
    ];
}
