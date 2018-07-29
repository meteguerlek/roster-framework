<?php

namespace App\Http\Controllers;

use App\Models\User;
use Roster\Auth\Roster\Auth;

class Controller
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @param $status
     * @param $type
     * @param $message
     * @param array $additionalFields
     * @return array
     */
    public function addMessage($status, $type, $message, array $additionalFields = [])
    {
        return $this->messages[] = array_merge(['status' => $status, 'type' => $type, 'message' => $message], $additionalFields);
    }

    /**
     * @param $validation
     * @return void
     */
    protected function setValidationMessages($validation)
    {
        foreach ($validation->getErrors() as $field => $errors)
        {
            $this->addMessage('validation-error', 'error', $errors, ['field' => $field]);
        }
    }

    /**
     * @return array
     */
    protected function getMessages()
    {
        return $this->messages;
    }
}
