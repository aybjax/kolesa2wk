<?php
namespace Queue\Controller;

class QueueController
{
    protected $db;

    public function __construct()
    {
        $this->db = \App\Database\PushTokenDBFactory::createDatabase();
    }

    protected function save(array $data): void
    {
        $this->db->insertData($data);
    }

    protected function delete(array $data): void
    {
        $this->db->deleteData($data);
    }
}
