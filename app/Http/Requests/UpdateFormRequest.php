<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_name' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:10|in:CFT,SFT,RFT,CUM,SQM,RM',
            'rate' => 'nullable|numeric|min:0',
            'group_by' => 'nullable|string|max:255',
            'client_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'fields' => 'nullable|array',
            'fields.*.id' => 'nullable|exists:fields,id',
            'fields.*.description' => 'nullable|string',
            'fields.*.quantity' => 'nullable|numeric',
            'fields.*.length' => 'nullable|numeric|min:0',
            'fields.*.width' => 'nullable|numeric|min:0',
            'fields.*.height' => 'nullable|numeric|min:0',
            'fields.*.product' => 'nullable|numeric',
        ];
    }
}
