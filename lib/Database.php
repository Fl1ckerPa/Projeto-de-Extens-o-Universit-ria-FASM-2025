<?php
/**
 * Database - Classe para manipulação de banco de dados
 * Adaptada do AtomPHP para uso sem MVC
 */

class Database 
{
    private $conexao;
    private static $dbdrive  = "";
    private static $host     = "";
    private static $port     = "";
    private static $user     = "";
    private static $password = "";
    private static $db       = "";
    
    protected $table;
    private $select = "*";
    private $join = "";
    private $where = "";
    private $groupBy = "";
    private $orderBy = "";
    private $limit = "";  
    private $params = [];

    public function __construct()
    {
        self::$dbdrive  = DB_DRIVE;
        self::$host     = DB_HOST;
        self::$port     = DB_PORT;
        self::$db       = DB_DATABASE;
        self::$user     = DB_USER;
        self::$password = DB_PASSWORD;
    }

    private function __clone() {}

    public function __destruct() {
        $this->disconnect();
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    private function getDBDrive() {return self::$dbdrive;}
    private function getHost()    {return self::$host;}
    private function getPort()    {return self::$port;}
    private function getUser()    {return self::$user;}
    private function getPassword(){return self::$password;}
    private function getDB()      {return self::$db;}

    public function connect()
    { 
        try {
            if ($this->getDBDrive() == 'mysql') {
                $this->conexao = new \PDO(
                    $this->getDBDrive().":host=".$this->getHost().";port=".$this->getPort().";dbname=".$this->getDB().";charset=utf8", 
                    $this->getUser(), 
                    $this->getPassword(), 
                    [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
                );
            }
            $this->conexao->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
        return $this->conexao;
    }

    private function disconnect(){
        $this->conexao = null;
    }

    // Métodos diretos SQL
    public function dbSelect($sql, $params = null)
    {
        if ((gettype($params) != 'array') && (gettype($params) != "NULL")) {
            $params = [$params];
        }
        $query = $this->connect()->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        $query->execute($params);
        return $query;
    }
    
    public function dbInsert($sql, $params = null)
    {
        try {        
            $conexao = $this->connect();
            $query   = $conexao->prepare($sql);
            $query->execute($params);
            return $conexao->lastInsertId();
        } catch (\Exception $e) {
            throw new \Exception('Erro ao inserir: ' . $e->getMessage());
        }     
    }

    public function dbUpdate($sql, $params = null)
    {
        try {
            $query = $this->connect()->prepare($sql);
            $query->execute($params);
            return $query->rowCount();
        } catch (\Exception $e) {
            throw new \Exception('Erro ao atualizar: ' . $e->getMessage());
        }  
    }

    public function dbDelete($sql, $params=null)
    {
        try {
            $query = $this->connect()->prepare($sql);
            $query->execute($params);
            return $query->rowCount();
        } catch (\Exception $e) {
            throw new \Exception('Erro ao excluir: ' . $e->getMessage());
        }       
    }

    public function dbBuscaArray($rscPdo)
    {
        $aRegistro = $rscPdo->fetch(\PDO::FETCH_ASSOC);
        return ($aRegistro === false) ? [] : $aRegistro;
    }

    public function dbBuscaArrayAll($rscPdo)
    {
        return $rscPdo->fetchall(\PDO::FETCH_ASSOC);
    }

    // Query Builder
    public function select($columns = "*")
    {
        $this->select = $columns;
        return $this;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function join($table, $condition, $tipoJoin = "INNER")
    {
        $this->join .= ' ' . $tipoJoin . " JOIN " . $table . " ON " . $condition;
        return $this;
    }

    public function where($condition, $params = "")
    {
        $operadores = ["=", ">=", "<=", ">", "<", "<>"];

        if ($this->where == "") {
            $this->where = " WHERE ";
        } else {
            $this->where .= " AND ";
        }

        if (gettype($condition) == "string") {
            $aKey = explode(" ", $condition);
            if (count($aKey) > 1 && in_array($aKey[1], $operadores)) {
                $this->where .= $condition . " ? ";
            } else {
                $this->where .= $condition . " = ? ";
            }
            $this->params = array_merge($this->params, [$params]);
        } else {
            $lAnd = false;
            foreach ($condition as $key => $value) {
                if ($lAnd) {
                    $this->where .= " AND ";
                } else {
                    $lAnd = true;
                }
                $aKey = explode(" ", $key);
                if (count($aKey) > 1 && in_array($aKey[1], $operadores)) {
                    $this->where .= $key . " ? ";
                } else {
                    $this->where .= $key . " = ? ";
                }
            }
            $this->params = array_values($condition);
        }
        return $this;
    }

    public function orWhere($condition, $params = "")
    {
        if ($this->where == "") {
            $this->where = " WHERE ";
        } else {
            $this->where .= " OR ";
        }
        $this->where .= $condition . " = ? ";
        $this->params = array_merge($this->params, [$params]);
        return $this;
    }

    public function whereLike($field, $value, $operadorLogico = "AND")
    {
        $clause = " {$field} LIKE ? ";
        $this->params[] = "%$value%";
        if (empty($this->where)) {
            $this->where = " WHERE {$clause}";
        } else {
            $this->where .= " {$operadorLogico} {$clause}";
        }
        return $this;
    }

    public function whereIn($field, $params, $operadorLogico = 'AND')
    {
        $placeholders = [];
        foreach ($params as $value) {
            $placeholders[] = "?";
            $this->params[] = $value;
        }
        $clause = "{$field} IN (" . implode(', ', $placeholders) . ")";
        if (empty($this->where)) {
            $this->where = " WHERE {$clause}";
        } else {
            $this->where .= " {$operadorLogico} {$clause}";
        }
        return $this;
    }

    public function orderBy($column, $direction = "ASC")
    {
        $this->orderBy = " ORDER BY " . $column . " " . $direction;
        return $this;
    }

    public function groupBy($column)
    {
        $this->groupBy = " GROUP BY $column";
        return $this;
    }

    public function findAll()
    {
        return $this->prepareSelect("all");
    }

    public function first()
    {
        return $this->prepareSelect("first");
    }

    public function findCount()
    {
        $cSql = "SELECT COUNT(*) as total FROM {$this->table} {$this->join} {$this->where}";
        $query = $this->connect()->prepare($cSql);
        $query->execute($this->params);
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $count = $result ? (int)$result['total'] : 0;
        $this->dbClear();
        return $count;
    }

    private function prepareSelect($tipoRetorno = "all")
    {
        $cSql = "SELECT {$this->select} FROM {$this->table} {$this->join} {$this->where} {$this->groupBy} {$this->orderBy}";
        $query = $this->connect()->prepare($cSql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        $query->execute($this->params);

        $result = null;
        if ($tipoRetorno == "all") {
            $result = $this->dbBuscaArrayAll($query);
        } elseif ($tipoRetorno == "first") {
            $result = $this->dbBuscaArray($query);
        }

        $this->dbClear();
        return $result;
    }

    public function dbClear()
    {
        $this->select = "*";
        $this->join = "";
        $this->where = "";
        $this->groupBy = "";
        $this->orderBy = "";
        $this->limit = "";  
        $this->params = [];
    }

    public function insert(array $data)
    {
        try {
            $columns = implode(", ", array_keys($data));
            $placeHolders = ":" . implode(", :", array_keys($data));
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeHolders)";

            $conexao = $this->connect();
            $query = $conexao->prepare($sql);
            $query->execute($data);

            $rs = $conexao->lastInsertId();
            $this->dbClear();
            return $rs;
        } catch (\Exception $err) {
            $this->dbClear();
            throw new \Exception("Erro ao inserir: " . $err->getMessage());
        }
    }

    public function update(array $data)
    {
        try {
            $fields = implode(" = ?, ", array_keys($data)) . " = ?";
            $sql    = "UPDATE {$this->table} SET {$fields} {$this->where}";
            $updData = array_merge(array_values($data), $this->params);

            $query  = $this->connect()->prepare($sql);
            $query->execute($updData);

            $rs = $query->rowCount();
            $this->dbClear();
            return $rs;
        } catch (\Exception $err) {
            $this->dbClear();
            throw new \Exception("Erro ao atualizar: " . $err->getMessage());
        }
    }

    public function delete()
    {
        try {
            $sql = "DELETE FROM {$this->table} {$this->where}";
            $query = $this->connect()->prepare($sql);
            $query->execute($this->params);
            $rs = $query->rowCount();
            $this->dbClear();
            return $rs;
        } catch (\Exception $err) {
            $this->dbClear();
            throw new \Exception("Erro ao excluir: " . $err->getMessage());
        }
    }
}

