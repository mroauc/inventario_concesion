<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TecnicoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id|unique:tecnicos,user_id,' . ($this->route('tecnico') ? $this->route('tecnico')->id : ''),
            'nombre' => 'required|string|max:255',
            'especialidad' => 'required|string|max:255',
            'telefono_contacto' => 'nullable|string|max:255',
            'email_contacto' => 'nullable|email|max:255',
            'zona_cobertura' => 'nullable|string|max:255',
            'certificaciones' => 'nullable|string',
            'disponibilidad' => 'required|in:disponible,ocupado,de_baja',
            'nota' => 'nullable|string'
        ];
    }
}
