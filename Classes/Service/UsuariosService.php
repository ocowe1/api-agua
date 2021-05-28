<?php


namespace Service;

use InvalidArgumentException;
use Repository\TokensAutorizadosRepository;
use Repository\UsuariosRepository;
use Util\ConstantesGenericasUtil;

class UsuariosService
{

    public const TABELA = 'usuarios';
    public const RECURSOS_GET = ['listar'];
    public const RECURSOS_DELETE = ['deletar'];
    public const RECURSOS_POST = ['cadastrar', 'login', 'drink'];
    public const RECURSOS_PUT = ['atualizar'];

    private array $dados;
    private array $dadosCorpoRequest = [];

    private object $UsuariosRepository;
    private object $TokensAutorizadosRepository;

    /**
     * UsuariosService constructor.
     * @param array $dados
     */
    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->UsuariosRepository = new UsuariosRepository();
        $this->TokensAutorizadosRepository = new TokensAutorizadosRepository();
    }

    /**
     * @return mixed
     */
    public function validarGet()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_GET, true)) {
            $retorno = $this->dados['id'] > 0 ? $this->getOneByKey() : $this->$recurso();
        } else {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
        }

        $this->validarRetornoRequest($retorno);

        return $retorno;
    }

    /**
     * @return mixed
     */
    public function validarDelete()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_DELETE, true)) {
            $retorno = $this->validarID($recurso);
        } else {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
        }

        $this->validarRetornoRequest($retorno);

        return $retorno;
    }

    /**
     * @return mixed
     */
    public function validarPut()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_PUT, true)) {
            $retorno = $this->validarToken($recurso);
        } else {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
        }

        $this->validarRetornoRequest($retorno);

        return $retorno;
    }

    /**
     * @return mixed
     */
    public function validarPost()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_POST, true)) {
            $retorno = $this->$recurso();
        } else {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
        }

        $this->validarRetornoRequest($retorno);

        return $retorno;
    }

    /**
     * @return mixed
     */
    private function drink()
    {
        $drink_ml = $this->dadosCorpoRequest['drink_ml'];
        $token = $this->TokensAutorizadosRepository->getToken();
        if ($drink_ml) {
            if($token) {
                $usuariosInfo = $this->UsuariosRepository->getUsuarioInfo($token);
                $getDrink = $this->UsuariosRepository->getDrink($token);
                $params = [];
                $params['drink_ml'] = $drink_ml;
                $params['drink'] += $getDrink['drink'];
                $params['usuario_id'] = $usuariosInfo['id'];
                $params['date'] = date('Y/m/d');

                if ($this->UsuariosRepository->insertDrinkMl($params) > 0) {
                    $this->UsuariosRepository->getMySQL()->getDb()->commit();
                    return $this->UsuariosRepository->getDrink($token);
                }

                $this->UsuariosRepository->getMySQL()->getDb()->rollBack();

                throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_GENERICO);
            }else{
                throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_TOKEN_VAZIO);
            }
        } else {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_CAMPO_OBRIGATORIO);
        }

    }

    /**
     * @return mixed
     */
    public function login()
    {
        $dados = [];
        $dados['email'] = $this->dadosCorpoRequest['email'];
        $dados['senha'] = $this->dadosCorpoRequest['senha'];

        if ($dados['email'] && $dados['senha']) {
            $verificaLogin = $this->UsuariosRepository->verificaLogin($dados);
            $loginToken = $this->UsuariosRepository->getTokenLogin($dados);
            $dadosUsuario = $this->UsuariosRepository->getLoginDados($loginToken['token']);
            if ($verificaLogin > 0) {
                return $dadosUsuario;
            }
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_USUARIO_NAO_EXISTE);
        } else {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_CAMPO_OBRIGATORIO);
        }
    }

    /**
     * @return mixed
     */
    private function getOneByKey()
    {
        return $this->UsuariosRepository->getUsuarios($this->dados['id']);

    }

    /**
     * @return mixed
     */
    public function historico()
    {
        $id = $this->dados['recurso'];
        if($id)
        {
            return $this->UsuariosRepository->getUsuarioDrink($id);
        }else{
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_ID_OBRIGATORIO);
        }
    }

    /**
     * @return array
     */
    public function ranking()
    {
        return $this->UsuariosRepository->getRanking();
    }

    /**
     * @return array
     */
    private function listar()
    {
        return $this->UsuariosRepository->getMySQL()->getAll(self::TABELA);

    }

    /**
     * @return string
     */
    private function deletar()
    {
        return $this->UsuariosRepository->getMySQL()->delete(self::TABELA, $this->TokensAutorizadosRepository->getToken());
    }

    /**
     * @return array
     */
    private function cadastrar()
    {
        [$email, $nome, $senha] = [$this->dadosCorpoRequest['email'], $this->dadosCorpoRequest['nome'], $this->dadosCorpoRequest['senha']];

        if ($email && $nome && $senha) {
            if ($this->UsuariosRepository->insetUser($email, $nome, $senha) > 0) {
                $idInserido = $this->UsuariosRepository->getMySQL()->getDb()->lastInsertId();
                $this->UsuariosRepository->getMySQL()->getDb()->commit();
                return ['id_inserido' => $idInserido];
            }

            $this->UsuariosRepository->getMySQL()->getDb()->rollBack();

            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_EMAIL_EXISTENTE);
        } else {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_CAMPO_OBRIGATORIO);
        }

    }

    /**
     * @return string
     */
    private function atualizar()
    {
        if ($this->UsuariosRepository->updateUser($this->TokensAutorizadosRepository->getToken(), $this->dadosCorpoRequest) > 0) {
            $this->UsuariosRepository->getMySQL()->getDb()->commit();
            return ConstantesGenericasUtil::MSG_ATUALIZADO_SUCESSO;
        }

        $this->UsuariosRepository->getMySQL()->getDb()->rollBack();

        throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_NAO_AFETADO);
    }

    /**
     * @param $dadosRequest
     */
    public function setDadosCorpoRequest($dadosRequest)
    {
        $this->dadosCorpoRequest = $dadosRequest;
    }

    /**
     * @param $retorno
     */
    private function validarRetornoRequest($retorno): void
    {
        if ($retorno === null) {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_GENERICO);
        }
    }

    /**
     * @param string $recurso
     * @return mixed
     */
    private function validarToken(string $recurso)
    {
        $token = $this->TokensAutorizadosRepository->getToken();
        if ($token > 0) {
            $retorno = $this->$recurso();
        } else {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_ID_OBRIGATORIO);
        }
        return $retorno;
    }

}