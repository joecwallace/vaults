<?php

namespace Wallace\Vaults\OnePassword;

use Wallace\Vaults\Vault as BaseVault;

class Vault implements BaseVault
{
    private $op;

    private $vaultId;

    public function __construct(Op $op, string $vaultId = null)
    {
        $this->op = $op;

        // TODO: dependency on config()
        $this->vaultId = $vaultId ?? config('one-password.vault-id');
    }

    public function store(string $title, string $username, string $password) : string
    {
        $data = $this->fillTemplate($this->getTemplateForLogin(), compact('username', 'password'));
        $payload = $this->encode($data);

        return $this->createItem('Login', $title, $payload)->uuid;
    }

    public function find(string $id) : array
    {
        // TODO: dependency on collect()
        return collect($this->getItem($id)->details->fields)->mapWithKeys(function ($field) {
            return [$field->name => $field->value];
        })->all();
    }

    public function delete(string $id)
    {
        $this->deleteItem($id);
    }

    private function fillTemplate(array $template, array $fields) : array
    {
        // TODO: dependency on collect()
        $template['fields'] = collect($template['fields'])->map(function ($field) use ($fields) {
            $fieldName = $field['name'];

            if (array_key_exists($fieldName, $fields)) {
                $field['value'] = $fields[$fieldName];
            }

            return $field;
        })->all();

        return $template;
    }

    private function getTemplateForLogin() : array
    {
        return json_decode($this->op->exec('get template Login'), true);
    }

    private function encode(array $data)
    {
        return base64_encode(trim(json_encode($data), '='));
    }

    private function createItem($type, $title, $payload)
    {
        return json_decode($this->op->exec("create item {$type} {$payload} --title=\"{$title}\" --vault={$this->vaultId}"));
    }

    private function getItem($id)
    {
        return json_decode($this->op->exec("get item {$id}"));
    }

    private function deleteItem($id)
    {
        return json_decode($this->op->exec("delete item {$id}"));
    }
}
