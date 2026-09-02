<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Balance;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

final class BalanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    private function isCreating(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $creating = $this->isCreating();

        return [
            'date' => [
                $creating ? 'required' : 'sometimes',
                'date',
                'before_or_equal:'.now()->addDay()->endOfDay()->toDateTimeString(),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $date = Carbon::parse($value)->toDateString(); // extract calendar date only

                    $query = Balance::where('user_id', auth()->id())
                        ->whereDate('date', '=', $date)
                        ->where('is_demo', $this->boolean('is_demo'))
                        ->where('market', $this->input('market', 'crypto'));

                    // On update, ignore the current record
                    if (!$this->isCreating()) {
                        $query->where('id', '!=', $this->route('balance'));
                    }

                    if ($query->exists()) {
                        $fail('A balance entry already exists for this date.');
                    }
                },
            ],
            'wallet_balance' => [$creating ? 'required' : 'sometimes', 'numeric', 'min:-9999999999', 'max:9999999999'],
            'total_equity' => [$creating ? 'required' : 'sometimes', 'numeric', 'min:-9999999999', 'max:9999999999'],
            'cum_realised_pnl' => [$creating ? 'required' : 'sometimes', 'numeric', 'min:-9999999999', 'max:9999999999'],
            'is_demo' => ['sometimes', 'boolean'],
            'market' => [$creating ? 'required' : 'sometimes', 'string', 'in:crypto,pse'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'The date is required.',
            'date.date' => 'Please enter a valid date.',
            'date.before_or_equal' => 'The date cannot be beyond tomorrow.',
            'wallet_balance.required' => 'Wallet balance is required.',
            'wallet_balance.numeric' => 'Wallet balance must be a number.',
            'total_equity.required' => 'Total equity is required.',
            'total_equity.numeric' => 'Total equity must be a number.',
            'cum_realised_pnl.required' => 'Cumulative realized PnL is required.',
            'cum_realised_pnl.numeric' => 'Cumulative realized PnL must be a number.',
        ];
    }
}
