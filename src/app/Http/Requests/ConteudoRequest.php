<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConteudoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
           'papel' => ['required', 'string', 'max:255'],
           'conteudo' => ['required', 'string'], // 'string' é o tipo correto para texto no Laravel
        ];
    }
}
