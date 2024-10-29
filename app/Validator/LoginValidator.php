<?php

namespace App\Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginValidator
{

    public function validateRequest(Request $request)
    {
        $request->validate($this->getValidationRules());
    }

    public function validate(array $fields)
    {
        return Validator::make($fields, $this->getValidationRules());
    }

    private function getValidationRules(): array
    {
        $rules = [
            'email' => 'required|string',
            'password' => 'required|string',
        ];

        if (config('app.recaptcha_enabled')) {
            $rules[recaptchaFieldName()] = recaptchaRuleName();
        }

        return $rules;
    }
}
