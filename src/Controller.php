<?php

class Controller
{
    protected $errors = [];
    protected $validFields = ['name', 'email', 'password', 'active'];

    protected function handleGet(string | null $id): bool
    {
        if ($id) {
            return true;
        }
        return false;
    }


    protected function handlePost(array $data = [])
    {
        if (isset($data['credentials'])) {
            return $this->validateDataForAuth($data);
        }

        $this->validateName($data);
        $this->validateActive($data);
        $this->validatePassword($data);
        $this->validateEmail($data);

        if (count($this->errors) > 0) {
            $this->respondUnprocessableEntity($this->errors);
            exit;
        } else {
            $clearData = $this->clearUnknowFields($data);
            return ['create' => $clearData];
        }
    }


    protected function validateName(array $data): void
    {
        if (!isset($data['name'])) {
            $this->errors[] = 'name is required.';
        }

        if (isset($data['name']) && trim($data['name']) === '') {
            $this->errors[] = 'name must not be empty.';
        }
    }

    protected function validateActive(array $data): void
    {
        if (isset($data['active'])) {
            if (!is_bool($data['active'])) {
                $this->errors[] = 'active must be a boolean value.';
            }
        }
    }

    protected function validatePassword(array $data): void
    {
        if (!isset($data['password'])) {
            $this->errors[] = 'password is required.';
        }

        if (isset($data['password']) && strlen(trim($data['password'])) < 8) {
            $this->errors[] = 'password must be at least 8 characters long.';
        }
    }

    protected function validateEmail(array $data): void
    {
        if (!isset($data['email'])) {
            $this->errors[] = "email is required.";
        }
        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "invalid email format.";
            }
        }
    }

    protected function clearUnknowFields(array $data)
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->validFields)) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    protected function validateDataForAuth(array $data)
    {
        if (isset($data['credentials']['email']) && isset($data['credentials']['password'])) {
            return ['login' => ['email' => $data['credentials']['email'], 'password' => $data['credentials']['password']]];
        }
        $this->respondUnprocessableEntity(['invalid data format.']);
        exit;
    }

    protected function validateForPatch(array $data): array | null
    {
        $clearData = $this->clearUnknowFields($data);

        if (count($clearData) < 1) {
            $this->respondUnprocessableEntity(['no data for updating was supplied.']);
            exit;
        }

        if (isset($data['name'])) {
            $this->validateName($data);
        }

        if (isset($data['email'])) {
            $this->validateEmail($data);
        }

        if (isset($data['password'])) {
            $this->validatePassword($data);
        }

        if (isset($data['active'])) {
            $this->validateActive($data);
        }

        if (!empty($this->errors)) {
            $this->respondUnprocessableEntity($this->errors);
            exit;
        } else {
            return $clearData;
        }
        return null;
    }


    //Validation methods ends//

    //Responses methods begins//
    protected function respondNotFound(string | null $id)
    {
        http_response_code(404);
        echo json_encode(['message' => "Admin with id: $id, not found."]);
    }

    protected function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    protected function respondCreated(string $id): void
    {
        http_response_code(201);
        echo json_encode(['message' => "New admin created.", "id" => $id]);
    }

    protected function respondNotIdComing()
    {
        http_response_code(422);
        echo json_encode(["message" => 'not key id was found in the request.']);
    }

    protected function respondNotAuthorized()
    {
        http_response_code(401);
        echo json_encode(["message" => 'no authorized.']);
    }
    //Responses methods ends//
}
