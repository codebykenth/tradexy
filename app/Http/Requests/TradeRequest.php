<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * True when this request is for creating (POST),
     * False when updating (PUT / PATCH).
     */
    private function isCreating(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Get the validation rules that apply to the request.
     * Rules differ slightly between store (POST) and update (PUT/PATCH).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $creating = $this->isCreating();

        return [
            // --- Market Type ---
            'market' => [$creating ? 'required' : 'sometimes', 'string', 'in:crypto,pse'],

            // --- General Info ---
            // On create: required. On update: 'sometimes' so unchanged fields don't need re-sending.
            'symbol' => [$creating ? 'required' : 'sometimes', 'string', 'max:20', 'alpha_num:ascii'],
            'open_datetime' => [$creating ? 'required' : 'sometimes', 'date', 'before_or_equal:now'],
            'close_datetime' => ['sometimes', 'nullable', 'date', 'after_or_equal:open_datetime', 'before_or_equal:now'],
            'timeframe' => ['sometimes', 'nullable', 'string', 'in:1m,5m,15m,30m,1hr,4hr,1d,1w'],
            'strategy_id' => ['sometimes', 'nullable', Rule::exists('strategies', 'id')->where('user_id', auth()->id())],

            // --- Entry Details ---
            'entry_side' => ['sometimes', 'nullable', 'string', 'in:long,short'],
            'leverage' => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:500'],
            // On create: required. On update: 'sometimes' allows partial updates.
            'avg_entry_price' => [$creating ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:999999999'],
            'quantity' => [$creating ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:999999999'],
            'cum_entry_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'stop_loss_price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999999'],
            'take_profit_price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999999'],
            'entry_emotion' => ['sometimes', 'nullable', 'string', 'max:50'],

            // --- Exit Details ---
            'exit_side' => ['sometimes', 'nullable', 'string', 'in:long,short'],
            'avg_exit_price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999999'],
            'cum_exit_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'exit_emotion' => ['sometimes', 'nullable', 'string', 'max:50'],

            // --- Fees & PnL ---
            'open_fees' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999'],
            'close_fees' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999'],
            'closed_pnl' => ['sometimes', 'nullable', 'numeric', 'min:-999999999', 'max:999999999'],
            'total_pnl' => ['sometimes', 'nullable', 'numeric', 'min:-999999999', 'max:999999999'],

            // --- Dynamic Form Arrays ---
            'entry_reason' => ['sometimes', 'nullable', 'array', 'max:10'],
            // 'nullable' is required: Laravel's ConvertEmptyStringsToNull middleware converts empty text inputs to null before validation runs. Without it, clearing a reason field fails the 'string' rule and silently redirects back.
            // array_filter() in the controller then removes nulls before the DB sync.
            'entry_reason.*' => ['nullable', 'string', 'max:255'],
            'exit_reason' => ['sometimes', 'nullable', 'array', 'max:10'],
            'exit_reason.*' => ['nullable', 'string', 'max:255'],
            'lesson' => ['sometimes', 'nullable', 'array', 'max:10'],
            'lesson.*' => ['nullable', 'string', 'max:255'],

            // --- Chart Upload ---
            // On update: always nullable so user doesn't have to re-upload an existing chart.
            'chart_picture' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            // --- PSE Fees (only applicable when market = pse) ---
            'broker_commission' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999'],
            'pse_trans_fee' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999'],
            'sccp_fee' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999'],
            'pse_vat' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999'],
            'sales_tax' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999'],

            // --- Type ---
            'is_demo' => ['sometimes', 'boolean'],
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
            // General
            'symbol.required' => 'Please enter a trading symbol (e.g. BTCUSDT).',
            'symbol.alpha_num' => 'Symbol must only contain letters and numbers (e.g. BTCUSDT).',
            'symbol.max' => 'Symbol must not exceed 20 characters.',
            'open_datetime.required' => 'Please enter the trade open date and time.',
            'open_datetime.before_or_equal' => 'Open date cannot be in the future.',
            'close_datetime.after_or_equal' => 'Close date must be on or after the open date.',
            'close_datetime.before_or_equal' => 'Close date cannot be in the future.',
            'strategy_id.exists' => 'The selected strategy is invalid or does not belong to you.',

            // Entry
            'avg_entry_price.required' => 'Please enter the average entry price.',
            'avg_entry_price.min' => 'Entry price must be a positive number.',
            'quantity.required' => 'Please enter the trade quantity.',
            'quantity.min' => 'Quantity must be a positive number.',
            'leverage.min' => 'Leverage must be at least 1x.',
            'leverage.max' => 'Leverage cannot exceed 500x.',

            // Reasons & Lessons
            'entry_reason.max' => 'You can add a maximum of 10 entry reasons.',
            'entry_reason.*.string' => 'Each entry reason must be plain text.',
            'entry_reason.*.max' => 'Each entry reason must not exceed 255 characters.',
            'exit_reason.max' => 'You can add a maximum of 10 exit reasons.',
            'exit_reason.*.string' => 'Each exit reason must be plain text.',
            'exit_reason.*.max' => 'Each exit reason must not exceed 255 characters.',
            'lesson.max' => 'You can add a maximum of 10 lessons.',
            'lesson.*.string' => 'Each lesson must be plain text.',
            'lesson.*.max' => 'Each lesson must not exceed 255 characters.',

            // Chart
            'chart_picture.image' => 'The chart must be an image file.',
            'chart_picture.mimes' => 'Chart must be a JPG, PNG, or WebP image.',
            'chart_picture.max' => 'Chart image must be smaller than 5MB.',

            // Market
            'market.required' => 'Please select a market type (Crypto or PSE).',
            'market.in' => 'Market must be Crypto or PSE.',
        ];
    }
}
