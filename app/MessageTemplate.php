<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    public static function getConfigurableTemplates()
    {
        return [
            [
                'key' => 'invitation',
                'label' => 'Invitation',
                'description' => 'Sent to the recipient of an invitation',
                'variables' => ['user', 'loan_limit'],
                'defaults' => [
                    'email' => "Hi, you can now borrow up to KES {{loan_limit}} from {{user.short_name}}. Download Kopesha from Playstore to register",
                    'sms' => 'Hi, you can now borrow up to KES {{loan_limit}} from {{user.short_name}}. Download Kopesha from Playstore to register',
                    'push' => 'Hi, you can now borrow up to KES {{loan_limit}} from {{user.short_name}}. Download Kopesha from Playstore to register'
                ]
            ],
        ];
    }
}
