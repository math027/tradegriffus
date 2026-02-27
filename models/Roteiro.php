<?php
/**
 * Model Roteiro
 */
class Roteiro
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(array $filters = []): array
    {
        $sql = 'SELECT r.*, u.nome AS promotor_nome, u.avatar AS promotor_avatar 
                FROM roteiros r 
                JOIN users u ON r.promotor_id = u.id';
        $params = [];
        $where = [];

        if (!empty($filters['promotor_id'])) {
            $where[] = 'r.promotor_id = :promotor_id';
            $params['promotor_id'] = $filters['promotor_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'r.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['data_inicio'])) {
            $where[] = 'r.data_inicio >= :data_inicio';
            $params['data_inicio'] = $filters['data_inicio'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY r.data_inicio DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.nome AS promotor_nome, u.avatar AS promotor_avatar 
             FROM roteiros r 
             JOIN users u ON r.promotor_id = u.id 
             WHERE r.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO roteiros (titulo, promotor_id, data_inicio, data_fim, status, observacoes, created_by) 
             VALUES (:titulo, :promotor_id, :data_inicio, :data_fim, :status, :observacoes, :created_by)'
        );
        $stmt->execute([
            'titulo'       => $data['titulo'],
            'promotor_id'  => $data['promotor_id'],
            'data_inicio'  => $data['data_inicio'],
            'data_fim'     => $data['data_fim'],
            'status'       => $data['status'] ?? 'pendente',
            'observacoes'  => $data['observacoes'] ?? null,
            'created_by'   => $data['created_by'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['titulo', 'promotor_id', 'data_inicio', 'data_fim', 'status', 'observacoes'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = 'UPDATE roteiros SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM roteiros WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countByStatus(string $status, int $promotorId = null): int
    {
        $sql = 'SELECT COUNT(*) FROM roteiros WHERE status = :status';
        $params = ['status' => $status];

        if ($promotorId) {
            $sql .= ' AND promotor_id = :promotor_id';
            $params['promotor_id'] = $promotorId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna roteiros do promotor para o período atual
     */
    public function doPromotor(int $promotorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, 
                    (SELECT COUNT(*) FROM visitas v WHERE v.roteiro_id = r.id) AS total_visitas,
                    (SELECT COUNT(*) FROM visitas v WHERE v.roteiro_id = r.id AND v.status = "concluida") AS visitas_concluidas
             FROM roteiros r 
             WHERE r.promotor_id = :promotor_id 
             AND r.data_fim >= CURDATE()
             ORDER BY r.data_inicio ASC'
        );
        $stmt->execute(['promotor_id' => $promotorId]);
        return $stmt->fetchAll();
    }
}
