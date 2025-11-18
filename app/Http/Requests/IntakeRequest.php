<?php

// app/Http/Requests/IntakeRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntakeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'idempotency_key' => ['required','string','max:128'],
            'source' => ['required','string'], // e.g. "matcher-v1"
            'type' => ['required','in:order_confirmation,order_match,exception,invoice,batch'],
            'payload' => ['required','array'],

            // OPTIONAL file refs (base64 or URL)
            'payload.pdf_url' => ['nullable','url'],
            'payload.pdf_base64' => ['nullable','string'],

            // OPTIONAL direct file upload (multipart/form-data)
            'files' => ['nullable','array'],
            'files.*' => ['file','mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png','max:16384'], // 16MB max per file
            'payload.file_names' => ['nullable','array'], // Optional: custom file names

            // Optional batch for multiple records at once
            'records' => ['nullable','array'],
            'records.*.type' => ['required_with:records','in:order_confirmation,order_match,exception,invoice'],
            'records.*.payload' => ['required_with:records','array'],
        ];
    }

    public function idempotencyKey(): string
    {
        return $this->input('idempotency_key');
    }
}

