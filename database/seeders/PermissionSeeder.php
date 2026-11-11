<?php

namespace Database\Seeders;

use App\Support\CmsPermissions;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        CmsPermissions::register();
    }
}
