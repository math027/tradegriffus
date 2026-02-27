<?php
/**
 * Model Pesquisa
 */
class Pesquisa
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(bool $apenasAtivas = true): array
    {
        $sql = 'SELECT p.*, u.nome AS criado_por_nome FROM pesquisas p JOIN users u ON p.created_by = u.id';
        if ($apenasAtivas) {
            $sql .= ' WHERE p.ativa = 1';
        }
        $sql .= ' ORDER BY p.created_at DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pesquisas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pesquisas (titulo, descricao, campos, created_by) VALUES (:titulo, :descricao, :campos, :created_by)'
        );
        $stmt->execute([
            'titulo'     => $data['titulo'],
            'descricao'  => $data['descricao'] ?? null,
            'campos'     => is_array($data['campos']) ? json_encode($data['campos']) : $data['campos'],
            'created_by' => $data['created_by'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['titulo', 'descricao', 'ativa'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (isset($data['campos'])) {
            $fields[] = 'campos = :campos';
            $params['campos'] = is_array($data['campos']) ? json_encode($data['campos']) : $data['campos'];
        }

        if (empty($fields)) return false;

        $sql = 'UPDATE pesquisas SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE pesquisas SET ativa = 0 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function deletePermanente(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM pesquisas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM pesquisas WHERE ativa = 1')->fetchColumn();
    }
}
