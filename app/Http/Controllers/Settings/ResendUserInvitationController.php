<?php

namespace App\Http\Controllers\Settings;

use App\Enums\UserAccountRole;
use App\Facades\Accounts;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Gate;

class ResendUserInvitationController
{
    public function __invoke(UserInvitation $invitation)
    {
        $account = Accounts::current();
        Gate::allows('update', $account);
        abort_unless($account->getCurrentUser()?->getRole() === UserAccountRole::Owner, 403);

        $invitation->prolong();
        $invitation->send();

        return back();
    }
}
