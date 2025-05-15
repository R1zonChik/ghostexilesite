<?php
class Database {
    private $pdo;
    private $host;
    private $dbname;
    private $username;
    private $password;
    
    public function __construct($host, $dbname, $username, $password) {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
        
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Ошибка подключения к базе данных: " . $e->getMessage());
            throw new Exception("Не удалось подключиться к базе данных. Пожалуйста, проверьте настройки подключения.");
        }
    }
    
    public function select($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Ошибка выполнения запроса select: " . $e->getMessage() . " | Запрос: " . $query);
            throw new Exception("Ошибка при выполнении запроса к базе данных.");
        }
    }
    
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Ошибка выполнения запроса selectOne: " . $e->getMessage() . " | Запрос: " . $query);
            throw new Exception("Ошибка при выполнении запроса к базе данных.");
        }
    }
    
    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Ошибка выполнения запроса execute: " . $e->getMessage() . " | Запрос: " . $query);
            throw new Exception("Ошибка при выполнении запроса к базе данных.");
        }
    }
    
    /**
     * Вставка записи в таблицу
     * @param string $table Имя таблицы
     * @param array $data Ассоциативный массив данных для вставки ['column' => 'value']
     * @return string|false ID вставленной записи или false в случае ошибки
     */
    public function insert($table, $data) {
        try {
            // Формируем список полей и плейсхолдеров
            $fields = array_keys($data);
            $placeholders = array_fill(0, count($fields), '?');
            
            // Строим SQL запрос
            $sql = "INSERT INTO `$table` (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
            
            // Логируем запрос для отладки
            error_log("Insert SQL: " . $sql);
            error_log("Insert Params: " . json_encode(array_values($data)));
            
            // Выполняем запрос
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));
            
            // Возвращаем ID вставленной записи
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Ошибка выполнения запроса insert: " . $e->getMessage() . " | Таблица: " . $table . " | Данные: " . json_encode($data));
            throw new Exception("Ошибка при выполнении запроса к базе данных.");
        }
    }
    
    /**
     * Обновление записей в таблице
     * @param string $table Имя таблицы
     * @param array $data Ассоциативный массив данных для обновления ['column' => 'value']
     * @param string $where Условие WHERE (например, "id = ?")
     * @param array $whereParams Параметры для условия WHERE
     * @return bool Результат выполнения запроса
     */
    public function update($table, $data, $where, $whereParams = []) {
        try {
            // Формируем SET часть запроса
            $set = [];
            foreach ($fields = array_keys($data) as $field) {
                $set[] = "`$field` = ?";
            }
            
            // Строим SQL запрос
            $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE $where";
            
            // Объединяем параметры данных и условия WHERE
            $params = array_merge(array_values($data), $whereParams);
            
            // Выполняем запрос
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Ошибка выполнения запроса update: " . $e->getMessage() . " | Таблица: " . $table . " | Данные: " . json_encode($data));
            throw new Exception("Ошибка при выполнении запроса к базе данных.");
        }
    }
    
    /**
     * Удаление записей из таблицы
     * @param string $table Имя таблицы
     * @param string $where Условие WHERE (например, "id = ?")
     * @param array $params Параметры для условия WHERE
     * @return bool Результат выполнения запроса
     */
    public function delete($table, $where, $params = []) {
        try {
            $sql = "DELETE FROM `$table` WHERE $where";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Ошибка выполнения запроса delete: " . $e->getMessage() . " | Таблица: " . $table . " | Условие: " . $where);
            throw new Exception("Ошибка при выполнении запроса к базе данных.");
        }
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}