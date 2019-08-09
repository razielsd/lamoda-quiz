<?php

namespace App\Storage;

use \PDO;
use \PDOException;

class Storage {
    protected $connection = null;

    /**
     * @return PDO
     */
    protected function getConnection(): PDO
    {
        if ($this->connection === null) {
            $dsn = 'mysql:host=mariadb;dbname=lamoda';
            $username = 'root';
            $passwd = 'root';
            $this->connection = new PDO($dsn, $username, $passwd);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->connection;
    }


    /**
     * @param array $container
     * @return string
     * @throws Exception
     */
    public function addContainer(array $container): int
    {
        try {
            $sql = 'INSERT INTO ContainerStorage SET container=%s;';
            $sql = sprintf($sql, $this->getConnection()->quote(json_encode($container)));
            $this->getConnection()->query($sql);
        } catch (PDOException $e) {
            throw new Exception('Error add container: ' . $e->getMessage(), 0, $e);
        }
        return $this->getConnection()->lastInsertId();
    }


    public function getContainer(int $limit, int $offset = 0): array
    {
        $sql = 'SELECT id, container FROM ContainerStorage ORDER BY id ASC LIMIT %d OFFSET %d;';
        $sql = sprintf($sql, $limit, $offset);
        try {
            return $this->getConnection()->query($sql)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception('Error get container: ' . $e->getMessage(), 0, $e);
        }
    }

}