<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\RegistrationType;
use App\Models\Participant;
use App\Models\RaceCategory;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
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
        $registrationType = $this->input('registration_type');
        $categoryId = $this->getCategoryId();

        $identityRules = [
            'required',
            'string',
            'max:50',
            $this->identityNumberUniqueRule($categoryId),
        ];

        $rules = [
            'registration_type' => ['required', Rule::enum(RegistrationType::class)],
            'website' => ['nullable', 'string', 'max:0'],

            // PIC data (first participant)
            'pic.full_name' => 'required|string|max:255',
            'pic.bib_name' => 'required|string|max:255',
            'pic.email' => 'required|email:rfc,dns|max:255',
            'pic.whatsapp' => ['required', 'regex:/^(\+?62|0)[0-9]{9,13}$/'],
            'pic.gender' => ['required', Rule::enum(Gender::class)],
            'pic.date_of_birth' => 'required|date|before:-5 years',
            'pic.jersey_size' => ['required', 'exists:jersey_sizes,code', 'max:10'],
            'pic.identity_number' => $identityRules,
            'pic.emergency_contact_name' => 'required|string|max:255',
            'pic.emergency_contact_phone' => ['required', 'regex:/^(\+?62|0)[0-9]{9,13}$/'],
            'pic.emergency_relation' => 'required|string|max:50',
        ];

        // Add members validation for collective registrations
        if ($registrationType === RegistrationType::Collective5->value) {
            $rules = array_merge($rules, $this->getMembersRules(4));
        } elseif ($registrationType === RegistrationType::Collective10->value) {
            $rules = array_merge($rules, $this->getMembersRules(9));
        }

        return $rules;
    }

    /**
     * Get validation rules for team members
     */
    protected function getMembersRules(int $count): array
    {
        $categoryId = $this->getCategoryId();

        return [
            'members' => ['required', 'array', "size:{$count}"],
            'members.*.full_name' => 'required|string|max:255',
            'members.*.bib_name' => 'required|string|max:255',
            'members.*.email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'distinct',
                function ($attribute, $value, $fail) {
                    $picEmail = $this->input('pic.email');
                    if ($value === $picEmail) {
                        $fail('Member email must be different from PIC email.');
                    }
                },
            ],
            'members.*.whatsapp' => [
                'required',
                'regex:/^(\+?62|0)[0-9]{9,13}$/',
                'distinct',
                function ($attribute, $value, $fail) {
                    $picWhatsapp = $this->input('pic.whatsapp');
                    if ($value === $picWhatsapp) {
                        $fail('Member WhatsApp number must be different from PIC.');
                    }
                },
            ],
            'members.*.gender' => ['required', Rule::enum(Gender::class)],
            'members.*.date_of_birth' => 'required|date|before:-5 years',
            'members.*.jersey_size' => ['required', 'exists:jersey_sizes,code', 'max:10'],
            'members.*.identity_number' => [
                'required',
                'string',
                'max:50',
                'distinct',
                $this->identityNumberUniqueRule($categoryId),
                function ($attribute, $value, $fail) {
                    $picIdentity = $this->input('pic.identity_number');
                    if ($value === $picIdentity) {
                        $fail('Nomor identitas anggota harus berbeda dari PIC.');
                    }
                },
            ],
            'members.*.emergency_contact_name' => 'required|string|max:255',
            'members.*.emergency_contact_phone' => ['required', 'regex:/^(\+?62|0)[0-9]{9,13}$/'],
            'members.*.emergency_relation' => 'required|string|max:50',
        ];
    }

    protected function getCategoryId(): ?int
    {
        $category = $this->route('category');

        if ($category instanceof RaceCategory) {
            return $category->id;
        }

        if (is_numeric($category)) {
            return (int) $category;
        }

        return null;
    }

    protected function identityNumberUniqueRule(?int $categoryId): Closure
    {
        return function ($attribute, $value, $fail) use ($categoryId) {
            if ($value === null || $value === '') {
                return;
            }

            if (! $categoryId) {
                return;
            }

            $exists = Participant::query()
                ->where('identity_number', $value)
                ->whereHas('registration', function ($query) use ($categoryId) {
                    $query->where('race_category_id', $categoryId);
                })
                ->exists();

            if ($exists) {
                $fail('Nomor identitas sudah terdaftar pada kategori ini.');
            }
        };
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'pic.full_name.required' => 'Full name is required',
            'pic.email.required' => 'Email is required',
            'pic.email.email' => 'Please provide a valid email address',
            'pic.whatsapp.required' => 'WhatsApp number is required',
            'pic.whatsapp.regex' => 'WhatsApp number format is invalid. Use format: 08xxx or +628xxx',
            'pic.gender.required' => 'Gender is required',
            'pic.date_of_birth.required' => 'Date of birth is required',
            'pic.date_of_birth.before' => 'Participant must be at least 5 years old',
            'pic.jersey_size.required' => 'Jersey size is required',
            'pic.identity_number.required' => 'Nomor identitas wajib diisi',
            'pic.identity_number.max' => 'Nomor identitas maksimal 50 karakter',
            'pic.emergency_contact_name.required' => 'Emergency contact name is required',
            'pic.emergency_contact_phone.required' => 'Emergency contact phone is required',
            'pic.emergency_relation.required' => 'Emergency contact relation is required',

            'website.max' => 'Form terdeteksi otomatis. Silakan coba lagi.',

            'members.required' => 'Team members data is required',
            'members.size' => 'Invalid number of team members',
            'members.*.email.distinct' => 'Each member must have a unique email address',
            'members.*.whatsapp.distinct' => 'Each member must have a unique WhatsApp number',
            'members.*.identity_number.required' => 'Nomor identitas anggota wajib diisi',
            'members.*.identity_number.distinct' => 'Nomor identitas anggota harus berbeda',
            'members.*.emergency_contact_name.required' => 'Emergency contact name is required for each member',
            'members.*.emergency_contact_phone.required' => 'Emergency contact phone is required for each member',
            'members.*.emergency_relation.required' => 'Emergency contact relation is required for each member',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'pic.full_name' => 'full name',
            'pic.email' => 'email',
            'pic.whatsapp' => 'WhatsApp number',
            'pic.gender' => 'gender',
            'pic.date_of_birth' => 'date of birth',
            'pic.jersey_size' => 'jersey size',
            'pic.identity_number' => 'nomor identitas',
            'pic.emergency_contact_name' => 'emergency contact name',
            'pic.emergency_contact_phone' => 'emergency contact phone',
            'pic.emergency_relation' => 'emergency contact relation',
            'members.*.identity_number' => 'nomor identitas',
            'members.*.emergency_contact_name' => 'emergency contact name',
            'members.*.emergency_contact_phone' => 'emergency contact phone',
            'members.*.emergency_relation' => 'emergency contact relation',
        ];
    }
}
