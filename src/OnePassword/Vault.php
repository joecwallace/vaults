<?php

namespace Wallace\Vaults\OnePassword;

use Wallace\Vaults\Exceptions\RequiredOptionException;
use Wallace\Vaults\Vault as BaseVault;

class Vault implements BaseVault
{
    private $op;

    private $vaultId;

    public function __construct(Op $op, string $vaultId)
    {
        $this->op = $op;
        $this->vaultId = $vaultId;
    }

    public static function init(array $options) : self
    {
        if (empty($options['vault_id'])) {
            throw new RequiredOptionException('vault_id');
        }

        return new static(new Op($options), $options['vault_id']);
    }

    public function store(string $title, string $username, string $password) : string
    {
        $data = $this->fillTemplate($this->getTemplateForLogin(), compact('username', 'password'));
        $payload = $this->encode($data);

        return $this->createItem('Login', $title, $payload)['uuid'];
    }

    public function find(string $id) : array
    {
        $result = [];
        $item = $this->getItem($id);

        foreach ($item['details']['fields'] as $field) {
            $result[$field['name']] = $field['value'];
        }

        return $result;
    }

    public function delete(string $id) : void
    {
        $this->deleteItem($id);
    }

    private function fillTemplate(array $template, array $fields) : array
    {
        $template['fields'] = array_map(function ($field) use ($fields) {
            $fieldName = $field['name'];

            if (array_key_exists($fieldName, $fields)) {
                $field['value'] = $fields[$fieldName];
            }

            return $field;
        }, $template['fields']);

        return $template;
    }

    private function getTemplateForLogin() : array
    {
        return $this->execAndDecode('get template Login');
    }

    private function encode(array $data) : string
    {
        return base64_encode(trim(json_encode($data), '='));
    }

    private function createItem($type, $title, $payload) : array
    {
        return $this->execAndDecode("create item {$type} {$payload} --title=\"{$title}\" --vault={$this->vaultId}");
    }

    private function getItem($id) : array
    {
        return $this->execAndDecode("get item {$id}");
    }

    private function deleteItem($id) : array
    {
        return $this->execAndDecode("delete item {$id}");
    }

    private function execAndDecode($command): array
    {
        return json_decode($this->op->exec($command), true);
    }
}
