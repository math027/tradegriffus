<?php
/**
 * Model Ponto (Time Clock)
 */
class Ponto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retorna o registro de ponto de hoje para um usuário
     */
    public function hoje(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ponto WHERE user_id = :uid AND data = CURDATE() LIMIT 1'
        );
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Registra um evento de ponto (entrada, almoco_saida, almoco_retorno, saida)
     */
    public function registrar(int $userId, string $tipo): bool
    {
        $tiposValidos = ['entrada', 'almoco_saida', 'almoco_retorno', 'saida'];
        if (!in_array($tipo, $tiposValidos)) return false;

        $horaAtual = date('H:i:s');

        // Tenta inserir ou atualizar (upsert)
        $registro = $this->hoje($userId);

        if (!$registro) {
            // Criar registro do dia
            $stmt = $this->db->prepare(
                "INSERT INTO ponto (user_id, data, {$tipo}) VALUES (:uid, CURDATE(), :hora)"
            );
            return $stmt->execute(['uid' => $userId, 'hora' => $horaAtual]);
        }

        // Já existe — se já preencheu este campo, não permite alterar
        if (!empty($registro[$tipo])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE ponto SET {$tipo} = :hora WHERE id = :id"
        );
        return $stmt->execute(['hora' => $horaAtual, 'id' => $registro['id']]);
    }

    /**
     * Retorna registros de ponto por período (para admin)
     */
    public function porPeriodo(string $inicio, string $fim, ?int $userId = null): array
    {
        $sql = 'SELECT p.*, u.nome AS user_nome
                FROM ponto p
                JOIN users u ON u.id = p.user_id
                WHERE p.data BETWEEN :inicio AND :fim';
        $params = ['inicio' => $inicio, 'fim' => $fim];

        if ($userId) {
            $sql .= ' AND p.user_id = :uid';
            $params['uid'] = $userId;
        }

        $sql .= ' ORDER BY p.data DESC, u.nome ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lista promotores CLT (para filtro admin)
     */
    public function promotoresClt(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nome FROM users WHERE role = 'promotor' AND tipo_contrato = 'clt' AND ativo = 1 ORDER BY nome"
        );
        return $stmt->fetchAll();
    }

    /**
     * Busca registro de ponto por ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ponto WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ajusta horários de um registro de ponto (somente dias anteriores)
     */
    public function ajustar(int $id, array $campos, int $adminId): bool|string
    {
        $registro = $this->findById($id);
        if (!$registro) {
            return 'Registro não encontrado.';
        }

        // Bloqueia ajuste do dia atual
        if ($registro['data'] === date('Y-m-d')) {
            return 'Não é permitido ajustar o ponto do dia atual.';
        }

        $camposValidos = ['entrada', 'almoco_saida', 'almoco_retorno', 'saida'];
        $sets = [];
        $params = ['id' => $id, 'admin' => $adminId];

        foreach ($camposValidos as $campo) {
            if (array_key_exists($campo, $campos)) {
                $valor = trim($campos[$campo]);
                if ($valor === '') {
                    $sets[] = "{$campo} = NULL";
                } else {
                    $sets[] = "{$campo} = :{$campo}";
                    $params[$campo] = $valor;
                }
            }
        }

        if (empty($sets)) {
            return 'Nenhum campo para atualizar.';
        }

        $sets[] = 'ajustado_por = :admin';
        $sets[] = 'ajustado_em = NOW()';

        $sql = 'UPDATE ponto SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params) ? true : 'Erro ao salvar ajuste.';
    }
}
