<?php

class Model
{
    protected function validate(array $data)
    {
        $this->validateName($data);

        $this->validateActive($data);

        $this->validatePassword($data);

        $comingFields = array_keys($data);

        foreach ($comingFields as $field) {
            if (!in_array($field, $this->allowedFields)) {
                $this->errors[] = "field '$field' was not expected.";
            }
        }
    }

    protected function unsetUnkownFieldsAndId(array $comingFields): array
    {
        unset($comingFields['id']);

        foreach ($comingFields as $key => $value) {
            if (!in_array($key, $this->allowedFields)) {
                unset($comingFields[$key]);
            }
        }

        return $comingFields;
    }

    protected function validateName(array $data)
    {
        if (isset($data['name']) && trim($data['name'])  === '') {
            $this->errors[] = 'name must be filled.';
        }

        if (!isset($data['name'])) {
            $this->errors[] = 'name is required.';
        }
    }

    protected function validatePassword(array $data,)
    {
        if (!isset($data['password'])) {
            $this->errors[] = 'password is required.';
        }

        if (isset($data['password'])) {
            if (strlen(trim($data['password'])) < 8) {
                $this->errors[] = 'password length must be 8 characters minimun.';
            }
        }
    }

    protected function validateActive(array $data)
    {
        if (isset($data['active'])) {
            if (!is_bool($data['active'])) {
                $this->errors[] = 'active must be a boolean value.';
            }
        }
    }

    protected function validateForPatch(array $data)
    {
        if (isset($data['name']) && trim($data['name'])  === '') {
            $this->errors[] = 'name must be filled.';
        }

        if (isset($data['password'])) {
            if (strlen(trim($data['password'])) < 8) {
                $this->errors[] = 'password length must be 8 characters minimun.';
            }
        }

        $this->validateActive($data);
    }
}
