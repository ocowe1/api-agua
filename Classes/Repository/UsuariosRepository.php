<?php

namespace Repository;

use DB\MySQL;
use InvalidArgumentException;
use PDO;
use Util\ConstantesGenericasUtil;

class UsuariosRepository
{

    private object $MySQL;
    public const TABELA = "usuarios";


    /**
     * UsuariosRepository constructor.
     */
    public function __construct()
    {
        $this->MySQL = new MySQL();
    }

    /**
     * @param $email
     * @param $nome
     * @param $senha
     * @return int
     */
    public function insetUser($email, $nome, $senha)
    {
        $consultaInsert = "INSERT INTO " . self::TABELA . " (email, nome, senha, token) values (:email, :nome, :senha, :token)";
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaInsert);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':senha', $senha);
        $stmt->bindValue(':token', md5(uniqid(rand(), true)));
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * @param $id
     * @param $dados
     * @return int
     */
    public function updateUser($token, $dados)
    {
        $consultaUpdate = "UPDATE " . self::TABELA . " SET email = :email, nome = :nome, senha = :senha WHERE token = :token";
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':senha', $dados['senha']);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * @param $dados
     * @return int
     */
    public function verificaLogin($dados)
    {
        $consultaLogin = "SELECT id FROM " . self::TABELA . " WHERE email = :email and senha = :senha";
        $stmt = $this->MySQL->getDb()->prepare($consultaLogin);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':senha', $dados['senha']);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * @param $dados
     * @return mixed
     */
    public function getTokenLogin($dados)
    {
        $consultaLogin = "SELECT token FROM " . self::TABELA . " WHERE email = :email and senha = :senha";
        $stmt = $this->MySQL->getDb()->prepare($consultaLogin);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':senha', $dados['senha']);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function getLoginDados($token)
    {
        $consultaLoginDados = "SELECT u.token, u.id, u.nome, u.email, count(dc.drink) as drink_counter FROM " . self::TABELA . " u LEFT JOIN drink_counter dc ON u.id = dc.usuario_id  WHERE u.token = :token";
        $stmt = $this->MySQL->getDb()->prepare($consultaLoginDados);
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getUsuarios($id)
    {
        $consultaUsuarios = "SELECT u.id, u.nome, u.email, count(dc.drink) as drink_counter FROM " . self::TABELA . " u LEFT JOIN drink_counter dc ON u.id = dc.usuario_id WHERE u.id = :id";
        $stmt = $this->MySQL->getDb()->prepare($consultaUsuarios);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function getUsuarioInfo($token)
    {
        $consultaInfo = "SELECT * FROM " . self::TABELA . " WHERE token = :token";
        $stmt = $this->MySQL->getDb()->prepare($consultaInfo);
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $params
     * @return int
     */
    public function insertDrinkMl($params)
    {
        $consultaInsert = "INSERT INTO drink_counter (drink, ml, usuario_id, date) values (:drink, :ml, :usuario_id, :dataHora)";
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaInsert);
        $stmt->bindValue(':drink', $params['drink']);
        $stmt->bindValue(':ml', $params['drink_ml']);
        $stmt->bindValue(':usuario_id', $params['usuario_id']);
        $stmt->bindValue(':dataHora', $params['date']);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getUsuarioDrink($id)
    {
        $consultaDrink = "SELECT u.nome, dc.ml as drink_ml, dc.date as data FROM " . self::TABELA . " u LEFT JOIN drink_counter dc ON dc.usuario_id = u.id WHERE u.id = :id";
        $stmt = $this->MySQL->getDb()->prepare($consultaDrink);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
    }


    /**
     * @return array
     */
    public function getRanking()
    {
        $constularRanking = "SELECT u.nome, CASE IFNULL(SUM(dc.ml), 0) WHEN 0 THEN 0 ELSE SUM(dc.ml) END AS drink_ml FROM " . self::TABELA . " u LEFT JOIN drink_counter dc ON u.id = dc.usuario_id WHERE DATE(dc.date) = DATE(NOW()) GROUP BY u.id ORDER BY drink_ml DESC";
        $stmt = $this->MySQL->getDb()->prepare($constularRanking);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function getDrink($token)
    {
        $consultaDrink = "SELECT u.id, u.nome, u.email, u.senha, count(dc.drink) as drink_counter FROM " . self::TABELA . " u LEFT JOIN drink_counter dc ON u.id = dc.usuario_id WHERE u.token = :token";
        $stmt = $this->MySQL->getDb()->prepare($consultaDrink);
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return MySQL|object
     */
    public function getMySQL()
    {
        return $this->MySQL;
    }

}