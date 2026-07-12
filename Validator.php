<?php
class Validator
{
    private array $errors = [];
    private array $data = [];

    public function sanitize(array $input): bool
    {
        $this->errors = [];
        $this->data = [
            'id' => (int) ($input['id'] ?? 0),
            'action' => trim((string) $input['action'] ?? ""),
        ];
        if(isset($input["rank"])){
            $this->data["rank"] = (int) ($input['rank'] ?? 1);
        }
        if(isset($input["message"])){
            $this->data["message"] = trim((string) ($input['message'] ?? ""));
        }
        if(isset($input["offset"])){
            $this->data["offset"] = (int) ($input['offset'] ?? 0);
        }
        if(isset($input["perpage"])){
            $this->data["perpage"] = (int) ($input['perpage'] ?? 10);
        }

        if (empty($this->data['action'])) {
            $this->errors[] = "Не заполнен action!";
        }
        if (!isset($this->data['id']) || $this->data['id'] < 0) {
            $this->errors[] = "Некорректный id!";
        }
        if ($this->data['action'] !=="delete") {
            if (isset($this->data["message"]) && empty($this->data['message'])) {
                $this->errors[] = "Текст задачи не может быть пустым";
            }
            if (isset($this->data["rank"]) && ($this->data['rank'] < 1 || $this->data['rank'] > 10) ) {
                $this->errors[] = "Неверно указан ранг";
            }
        }
        if ($this->data['action'] =="get") {
            if (isset($this->data["offset"]) && $this->data['offset'] < 0) {
                $this->errors[] = "Передано неверное значение offset" . $this->data["offset"];
            }
            if (isset($this->data["perpage"]) && $this->data['perpage'] < 1 ) {
                $this->errors[] = "Передано неверное значение perpage" . $this->data['perpage'] ;
            }
        }

        return empty($this->errors);
    }
    public function getFirstError(): string
    {
        return $this->errors[0] ?? "Неизвестная ошибка";
    }
    public function getData(){
        return $this->data;
    }
}
