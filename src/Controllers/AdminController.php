<?php

namespace Controllers;

use PDO;
use Controller;
use Jwt;
use Models\AdminModel;

class AdminController extends Controller
{
    public function __construct(protected AdminModel $model)
    {
    }
    public function get(string | null $id): void
    {
        /**if true $id then query for one only else query for all */
        if ($this->handleGet($id)) {

            $result = $this->model->getById($id);

            if ($result) {

                echo json_encode($result);
                exit;
            }

            $this->respondNotFound($id);
            exit;
        } else {
            echo json_encode($this->model->getAll());
            exit;
        }
    }

    public function post(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $dataForquery = $this->handlePost($data);

        if (isset($dataForquery['create'])) {

            $lasttId = $this->model->create($dataForquery['create']);
            $this->respondCreated($lasttId);
            exit;
        }

        if (isset($dataForquery['login'])) {
            $user = $this->model->login($dataForquery['login']['email'], $dataForquery['login']['password']);

            if ($user === false) {
                $this->respondNotAuthorized();
                exit;
            }
            $jwt = new Jwt($_ENV['SECRET_KEY']);

            echo json_encode(['access_token' => $jwt->encode($user)]);
        }
    }

    public function delete(string | null $id)
    {
        $affectedRows =  $this->model->delete($id);
        if ($affectedRows < 1) {
            $this->respondNotFound($id);
            exit;
        } else {
            echo json_encode(['message' => "$affectedRows admin was deleted."]);
        }
    }

    public function patch(string | null $id)
    {
        if ($id === null) {

            $this->respondNotFound($id);
            exit;
        }
        $row = $this->model->getById($id);

        if (!$row) {
            $this->respondNotFound($id);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $dataForquery = $this->validateForPatch($data);

        if ($dataForquery) {
            $rows = $this->model->update($id, $dataForquery);
            echo json_encode(['message' => "row with id: $id was updated."]);
        }
    }
}
