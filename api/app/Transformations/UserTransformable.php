<?php
namespace App\Transformations;

use App\User;

trait UserTransformable
{
    /**
     * Select user fields
     *
     * @param App\User $user
     * @param string $tenantName
     * @return App\User
     */
    protected function transformUser(User $user, string $tenantName): User
    {
        $prop = new User;

        $prop->user_id = (int) $user->user_id;
        $prop->first_name = $user->first_name;
        $prop->last_name = $user->last_name;
        $prop->avatar = !empty($user->avatar)  ? $user->avatar : $this->helpers->getUserDefaultProfileImage($tenantName);

        return $prop;
    }
}
