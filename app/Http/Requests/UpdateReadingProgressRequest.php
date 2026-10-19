<?php

namespace App\Http\Requests;

use App\Models\Book;
use App\ReadingStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateReadingProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $book = $this->route('book');

        return $book instanceof Book
            && $this->user()?->savedBooks()->whereKey($book->getKey())->exists() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reading_status' => ['required', Rule::enum(ReadingStatus::class)],
            'current_page' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('current_page')) {
                    return;
                }

                $book = $this->route('book');

                if (! $book instanceof Book || $book->page_count === null) {
                    return;
                }

                if ($this->integer('current_page') > $book->page_count) {
                    $validator->errors()->add(
                        'current_page',
                        "La página actual no puede superar las {$book->page_count} páginas del libro.",
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reading_status.required' => 'Seleccioná un estado de lectura.',
            'reading_status.enum' => 'El estado de lectura seleccionado no es válido.',
            'current_page.required' => 'Ingresá la página actual.',
            'current_page.integer' => 'La página actual debe ser un número entero.',
            'current_page.min' => 'La página actual no puede ser negativa.',
            'current_page.max' => 'La página actual es demasiado grande.',
        ];
    }
}
