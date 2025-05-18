<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'message' => 'required_if:mode,tts|string|max:500',
            'mode' => 'required|in:tts,manual',
            'ruangans' => 'required|array|min:1',
            'ruangans.*' => 'string|exists:ruangan,nama_ruangan', // Ubah validasi ke nama ruangan
        ];
    }

    public function messages()
    {
        return [
            'ruangans.required' => 'Pilih minimal satu ruangan',
            'ruangans.min' => 'Pilih minimal satu ruangan',
        ];
    }
}