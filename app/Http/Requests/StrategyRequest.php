<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StrategyRequest extends FormRequest
{
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
        $isPost = $this->isMethod('POST');

        return [
            'name' => [$isPost ? 'required' : 'sometimes', 'string', 'max:255'],
            'status' => [$isPost ? 'required' : 'sometimes', 'string', 'in:active,testing,inactive'],
            'target_rr' => ['nullable', 'numeric', 'min:0'],
            'max_risk_per_trade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'category' => ['nullable', 'array'],
            'category.*' => ['string', 'max:50'],
            'markets' => ['nullable', 'array'],
            'markets.*' => ['string', 'in:crypto,forex,stocks,options,commodities,indices,pse'],
            'timeframes' => ['nullable', 'array'],
            'timeframes.*' => ['string', 'max:10'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],

            'entry_rules' => ['nullable', 'array'],
            'entry_rules.*' => ['nullable', 'string', 'max:255'],

            'exit_rules' => ['nullable', 'array'],
            'exit_rules.*' => ['nullable', 'string', 'max:255'],

            'risk_management_rules' => ['nullable', 'array'],
            'risk_management_rules.*' => ['nullable', 'string', 'max:255'],

            'scaling_rules' => ['nullable', 'array'],
            'scaling_rules.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a name for this strategy.',
            'name.sometimes' => 'The strategy name is required for update.',
            'name.string' => 'The strategy name must be a valid string.',
            'name.max' => 'The strategy name cannot exceed 255 characters.',

            'status.required' => 'Please select a status for the strategy.',
            'status.sometimes' => 'The strategy status is required for update.',
            'status.in' => 'The selected status is invalid. Please choose from active, testing, or inactive.',

            'target_rr.numeric' => 'The target R:R must be a valid number.',
            'target_rr.min' => 'The target R:R cannot be negative.',

            'max_risk_per_trade.numeric' => 'The maximum risk per trade must be a valid number.',
            'max_risk_per_trade.min' => 'The risk percentage cannot be less than 0.',
            'max_risk_per_trade.max' => 'The risk percentage cannot exceed 100.',

            'description.string' => 'The description must be valid text.',
            'description.max' => 'The description is too long (maximum 5000 characters).',

            'entry_rules.array' => 'The entry rules format is invalid.',
            'entry_rules.*.string' => 'Each entry rule must be valid text.',
            'entry_rules.*.max' => 'An entry rule cannot exceed 255 characters.',

            'exit_rules.array' => 'The exit rules format is invalid.',
            'exit_rules.*.string' => 'Each exit rule must be valid text.',
            'exit_rules.*.max' => 'An exit rule cannot exceed 255 characters.',

            'risk_management_rules.array' => 'The risk management rules format is invalid.',
            'risk_management_rules.*.string' => 'Each risk management rule must be valid text.',
            'risk_management_rules.*.max' => 'A risk management rule cannot exceed 255 characters.',

            'scaling_rules.array' => 'The scaling rules format is invalid.',
            'scaling_rules.*.string' => 'Each scaling rule must be valid text.',
            'scaling_rules.*.max' => 'A scaling rule cannot exceed 255 characters.',
        ];
    }
}
