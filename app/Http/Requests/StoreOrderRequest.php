<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->has('quantity'))
        {
            return [
                'quantity' => 'required|integer|min:1|max:10000',
            ];
        }elseif(Auth::user()->role === 'admin'){
            return [
                'price' => 'required|numeric|min:1|max:10000',
            ];
        }
    }
    public function messages()
    {
        if ($this->has('quantity'))
        {
            return [
                'quantity.integer' => 'Must be number',
                'quantity.max' => 'Max quantity 10000',
                'quantity.min' => 'Min quantity 1'
            ];
        }else {
            return [
                'price.integer' => 'Must be number',
            ];
        }
    }
}
