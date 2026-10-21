<?php

namespace App\Policies;

use App\Models\DesignerStat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DesignerStatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DesignerStat $designerStat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DesignerStat $designerStat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DesignerStat $designerStat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DesignerStat $designerStat): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DesignerStat $designerStat): bool
    {
        return false;
    }


    /**
     * Menentukan apakah designer boleh mengubah status ketersediaannya.
     */
    public function updateStatus(User $user, DesignerStat $designerStat): bool
    {
        // Harus bertipe designer, pemilik data stat tersebut, dan tidak sedang ditangguhkan
        return $user->isDesigner() 
            && $user->id === $designerStat->designer_id 
            && !$designerStat->isSuspended();
    }
}
