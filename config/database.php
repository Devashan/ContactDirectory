<?php

class DB
{
    private static ?DB $instance = null;
    private mysqli $conn;

    private function __construct()
    {
        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASSWORD');
        $name = getenv('DB_NAME');

        $this->conn = new mysqli($host, $user, $pass, $name);

        if ($this->conn->connect_error) {
            throw new Exception("Database connection failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
    }

    public static function create_instance(): DB
    {
        if (self::$instance === null) {
            self::$instance = new DB();
        }

        return self::$instance;
    }

    public function query(string $sql)
    {
        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Query error: " . $this->conn->error);
        }

        return $result;
    }

    public function has_results(mysqli_result $result): bool
    {
        return $result->num_rows > 0;
    }

    public function prepare(string $sql): mysqli_stmt
    {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        return $stmt;
    }

    public function sanitize(string $value): string
    {
        return $this->conn->real_escape_string($value);
    }

    public function insert_id(): int
    {
        return $this->conn->insert_id;
    }

    public function close(): void
    {
        $this->conn->close();
    }

    public function fetchAll(mysqli_result $result): array
    {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne(mysqli_result $result): ?array
    {
        return $result->fetch_assoc() ?: null;
    }

    public function affected_rows(): int
    {
        return $this->conn->affected_rows;
    }
}