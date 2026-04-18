<?php

namespace App\Http\Resources;

use App\Enums\BoardRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class MemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = $this->pivot->role ?? null;
        $roleValue = $role instanceof BoardRole ? $role->value : (string) $role;

        return [
            'user_id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $roleValue,
            'created_at' => $this->pivot->created_at?->toIso8601String(),
        ];
    }
}
