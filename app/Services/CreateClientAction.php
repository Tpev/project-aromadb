<?php

namespace App\Services;

use App\Http\Requests\AssistantCreateClientRequest;
use App\Models\ClientProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateClientAction
{
    public function run(int $userId, array $slots): ClientProfile
    {
        // validate via rules
        $validator = validator($slots, (new AssistantCreateClientRequest())->rules(), (new AssistantCreateClientRequest())->messages());
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $v = $validator->validated();

        return DB::transaction(function () use ($userId, $v) {
            return ClientProfile::create([
                'user_id'            => $userId,
                'first_name'         => $v['first_name'],
                'last_name'          => $v['last_name'],
                'email'              => $v['email'] ?? null,
                'phone'              => $v['phone'] ?? null,
                'address'            => null,
                'birthdate'          => null,
                'notes'              => $v['notes'] ?? null,
                'first_name_billing' => $v['first_name'],
                'last_name_billing'  => $v['last_name'],
            ]);
        });
    }
}
