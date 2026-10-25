<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Users\Infrastructure\Database\Factories\UserFactory as ModuleUserFactory;

/**
 * @extends ModuleUserFactory<User>
 */
class UserFactory extends ModuleUserFactory
{
    protected $model = User::class;
}
