<?php

namespace App\Http\Requests;

use App\Services\LinkStorageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ShortenLinkRequest extends FormRequest
{
    /**
     * No authentication in CutURL — the shorten form is always allowed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Base validation rules. URL scheme, alias availability and reserved-word
     * checks are handled in withValidator() so we can give precise messages.
     */
    public function rules(): array
    {
        return [
            'original_url' => ['required', 'string', 'max:2048'],
            'custom_alias' => ['nullable', 'string', 'max:'.(int) config('cuturl.alias_max_length', 64)],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'original_url.required' => 'Please enter a URL to shorten.',
            'expires_at.after' => 'The expiration date must be in the future.',
        ];
    }

    /**
     * Normalise input before validation runs.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'original_url' => is_string($this->original_url) ? trim($this->original_url) : $this->original_url,
            'custom_alias' => is_string($this->custom_alias) && trim($this->custom_alias) !== ''
                ? trim($this->custom_alias)
                : null,
            'expires_at' => is_string($this->expires_at) && trim($this->expires_at) !== ''
                ? trim($this->expires_at)
                : null,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $storage = app(LinkStorageService::class);

            // --- URL scheme validation (only http/https, never javascript:) ---
            $url = $this->input('original_url');

            if (is_string($url) && $url !== '') {
                $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
                $host = parse_url($url, PHP_URL_HOST);

                if (! in_array($scheme, ['http', 'https'], true) || ! $host) {
                    $validator->errors()->add(
                        'original_url',
                        'Enter a valid http:// or https:// URL.'
                    );
                }
            }

            // --- Custom alias validation ---
            $alias = $this->input('custom_alias');

            if (is_string($alias) && $alias !== '') {
                if (! $storage->isValidAlias($alias)) {
                    $validator->errors()->add(
                        'custom_alias',
                        'Aliases may only contain letters, numbers, hyphens and underscores.'
                    );
                } elseif ($storage->isReserved($alias)) {
                    $validator->errors()->add(
                        'custom_alias',
                        "\"{$alias}\" is a reserved word and cannot be used."
                    );
                } elseif ($storage->findByCode($alias) !== null) {
                    $validator->errors()->add(
                        'custom_alias',
                        "The alias \"{$alias}\" is already taken."
                    );
                }
            }
        });
    }
}
