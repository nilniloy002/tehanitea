<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'default_address', 'marketing_consent'];
    protected function casts(): array { return ['marketing_consent' => 'boolean']; }
}
