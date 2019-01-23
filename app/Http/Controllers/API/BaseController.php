<?php

namespace App\Http\Controllers\API;

use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Exception\ValidationHttpException;

class BaseController extends Controller
{
    use Helpers;

    /**
     * Override validate method use dingo validation exception
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     */
    public function validate(
        Request $request,
        array $rules,
        array $messages = [],
        array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $rules, $messages,
                $customAttributes
            );
        if ($validator->fails()) {
            throw new ValidationHttpException(
                $validator->errors()
            );
        }
    }

}