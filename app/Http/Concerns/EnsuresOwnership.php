<?php

namespace App\Http\Concerns;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;

trait EnsuresOwnership
{
    protected function authUser(): User
    {
        /** @var User $user */
        $user = request()->user();

        return $user;
    }

    protected function ownServiceProviderId(): ?int
    {
        return $this->authUser()->serviceProvider?->id;
    }

    protected function ownServiceUserId(): ?int
    {
        return $this->authUser()->serviceUser?->id;
    }

    protected function requireOwnUserId(int $userId): void
    {
        if ((int) $this->authUser()->id !== (int) $userId) {
            $this->deny();
        }
    }

    protected function requireOwnProvider(int $providerId): void
    {
        if (! $this->ownServiceProviderId() || (int) $this->ownServiceProviderId() !== (int) $providerId) {
            $this->deny();
        }
    }

    protected function requireOwnServiceUser(int $serviceUserId): void
    {
        if (! $this->ownServiceUserId() || (int) $this->ownServiceUserId() !== (int) $serviceUserId) {
            $this->deny();
        }
    }

    protected function deny(string $message = 'You do not have access to this resource.'): never
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => $message,
        ], 403));
    }
}
