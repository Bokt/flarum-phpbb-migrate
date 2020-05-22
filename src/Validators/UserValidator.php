<?php

namespace Bokt\Phpbb\Validators;

class UserValidator extends \Flarum\User\UserValidator
{
    protected function getRules()
    {
        $rules = parent::getRules();

        $idSuffix = $this->user ? ','.$this->user->id : '';

        $rules['username'] = [
            'required',
            'regex:/^[\S]+$/i',
            'unique:users,username'.$idSuffix,
            'min:1',
            'max:30'
        ];

        $rules['email'] = [
            'required',
            'email'
        ];

        return $rules;
    }
}
