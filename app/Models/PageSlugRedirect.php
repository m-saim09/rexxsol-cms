<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['old_slug', 'target_slug', 'page_id'])]
class PageSlugRedirect extends Model
{
    public $timestamps = false;
}
