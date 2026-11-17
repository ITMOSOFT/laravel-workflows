<?php

namespace ItmoSoft\Workflows\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = false;
}
