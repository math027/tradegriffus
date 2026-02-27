<?php
/**
 * Model Resposta (respostas de pesquisas)
 */
class Resposta
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function porPesquisa(int $pesquisaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.nome AS promotor_nome, p.nome AS pdv_nome
             FROM respostas r
             JOIN users u ON r.promotor_id = u.id
             JOIN pdvs p ON r.pdv_id = p.id
             WHERE r.pesquisa_id = :pesquisa_id
             ORDER BY r.created_at DESC'
        );
        $stmt->execute(['pesquisa_id' => $pesquisaId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO respostas (pesquisa_id, visita_id, promotor_id, pdv_id, dados) 
             VALUES (:pesquisa_id, :visita_id, :promotor_id, :pdv_id, :dados)'
        );
        $stmt->execute([
            'pesquisa_id' => $data['pesquisa_id'],
            'visita_id'   => $data['visita_id'] ?? null,
            'promotor_id' => $data['promotor_id'],
            'pdv_id'      => $data['pdv_id'],
            'dados'       => is_array($data['dados']) ? json_encode($data['dados']) : $data['dados'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function countByPesquisa(int $pesquisaId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM respostas WHERE pesquisa_id = :id');
        $stmt->execute(['id' => $pesquisaId]);
        return (int) $stmt->fetchColumn();
    }

    public function countTotal(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM respostas')->fetchColumn();
    }
}
