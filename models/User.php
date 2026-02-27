<?php
/**
 * Model User
 */
class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email AND ativo = 1 LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nome, email, role, tipo_contrato, telefone, avatar, ativo, created_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function all(string $role = null): array
    {
        $sql = 'SELECT id, nome, email, role, tipo_contrato, telefone, avatar, ativo, created_at FROM users';
        $params = [];

        if ($role) {
            $sql .= ' WHERE role = :role';
            $params['role'] = $role;
        }

        $sql .= ' ORDER BY nome ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (nome, email, senha, role, tipo_contrato, telefone) VALUES (:nome, :email, :senha, :role, :tipo_contrato, :telefone)'
        );
        $stmt->execute([
            'nome'           => $data['nome'],
            'email'          => $data['email'],
            'senha'          => password_hash($data['senha'], PASSWORD_DEFAULT),
            'role'           => $data['role'] ?? 'promotor',
            'tipo_contrato'  => $data['tipo_contrato'] ?? 'pj',
            'telefone'       => $data['telefone'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['nome', 'email', 'telefone', 'role', 'tipo_contrato', 'ativo', 'avatar'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (isset($data['senha']) && !empty($data['senha'])) {
            $fields[] = 'senha = :senha';
            $params['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) return false;

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET ativo = 0 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function reativar(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET ativo = 1 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function deletePermanente(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = :role AND ativo = 1');
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }
}
