<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

final class ProfileDestroyRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'userDeletion';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        if ($user && $user->provider) {
            return [
                'email_confirmation' => ['required', 'string', 'in:'.$user->email],
            ];
        }

        return [
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email_confirmation.required' => 'Email confirmation is required.',
            'email_confirmation.in' => 'The entered email does not match your account email.',
            'password.required' => 'Password is required to delete your account.',
        ];
    }

    /**
     * Validate that the current password is correct.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if ($user && !$user->provider) {
                if (!Hash::check($this->password, $user->password)) {
                    $validator->errors()->add('password', 'The password is incorrect.');
                }
            }
        });
    }
}
