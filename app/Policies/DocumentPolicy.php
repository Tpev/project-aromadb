<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $doc): bool {
        return $doc->owner_user_id === $user->id || $user->is_admin ?? false;
    }
    public function update(User $user, Document $doc): bool {
        return $doc->owner_user_id === $user->id || $user->is_admin ?? false;
    }
    public function delete(User $user, Document $doc): bool {
        return $doc->owner_user_id === $user->id || $user->is_admin ?? false;
    }
}
