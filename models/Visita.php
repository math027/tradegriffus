<?php
/**
 * Model Visita — com workflow completo (check-in, fotos, observações, check-out)
 */
class Visita
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, p.nome AS pdv_nome, p.endereco AS pdv_endereco,
                    p.latitude AS pdv_lat, p.longitude AS pdv_lng,
                    p.cidade AS pdv_cidade, p.bairro AS pdv_bairro, p.rua AS pdv_rua,
                    u.nome AS promotor_nome
             FROM visitas v
             JOIN pdvs p ON v.pdv_id = p.id
             JOIN users u ON v.promotor_id = u.id
             WHERE v.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function porRota(int $rotaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, p.nome AS pdv_nome, p.endereco AS pdv_endereco
             FROM visitas v
             JOIN pdvs p ON v.pdv_id = p.id
             WHERE v.rota_id = :rota_id
             ORDER BY v.data_prevista ASC'
        );
        $stmt->execute(['rota_id' => $rotaId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO visitas (rota_id, pdv_id, promotor_id, data_prevista, status) 
             VALUES (:rota_id, :pdv_id, :promotor_id, :data_prevista, :status)'
        );
        $stmt->execute([
            'rota_id'       => $data['rota_id'],
            'pdv_id'        => $data['pdv_id'],
            'promotor_id'   => $data['promotor_id'],
            'data_prevista'  => $data['data_prevista'],
            'status'        => 'pendente',
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Cria visita se não existe para este PDV/promotor/data, ou retorna a existente
     */
    public function criarOuRetornar(int $rotaId, int $pdvId, int $promotorId, string $data): array
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM visitas 
             WHERE pdv_id = :pdv_id AND promotor_id = :promotor_id AND data_prevista = :data
             LIMIT 1'
        );
        $stmt->execute(['pdv_id' => $pdvId, 'promotor_id' => $promotorId, 'data' => $data]);
        $existing = $stmt->fetch();

        if ($existing) {
            return $this->findById($existing['id']);
        }

        $id = $this->create([
            'rota_id'       => $rotaId,
            'pdv_id'        => $pdvId,
            'promotor_id'   => $promotorId,
            'data_prevista' => $data,
        ]);

        return $this->findById($id);
    }

    /**
     * Realiza check-in na visita
     */
    public function checkin(int $id, array $data): bool
    {
        $lat = !empty($data['latitude']) ? $data['latitude'] : null;
        $lng = !empty($data['longitude']) ? $data['longitude'] : null;

        $stmt = $this->db->prepare(
            'UPDATE visitas SET 
                checkin_at = NOW(), 
                latitude_in = :lat, 
                longitude_in = :lng,
                foto_checkin = :foto,
                status = "em_andamento"
             WHERE id = :id'
        );
        $result = $stmt->execute([
            'id'   => $id,
            'lat'  => $lat,
            'lng'  => $lng,
            'foto' => $data['foto'] ?? null,
        ]);

        // Se tiver coordenadas válidas, verifica se deve atualizar o PDV
        if ($result && $lat && $lng) {
            $visita = $this->findById($id);
            if ($visita) {
                require_once MODELS_PATH . '/Pdv.php';
                $pdvModel = new Pdv();
                $pdvModel->atualizarCoordenadasSeConsistente(
                    (int) $visita['pdv_id'],
                    (float) $lat,
                    (float) $lng
                );
            }
        }

        return $result;
    }

    /**
     * Realiza check-out na visita
     */
    public function checkout(int $id, array $data = []): bool
    {
        $lat = !empty($data['latitude']) ? $data['latitude'] : null;
        $lng = !empty($data['longitude']) ? $data['longitude'] : null;

        $stmt = $this->db->prepare(
            'UPDATE visitas SET 
                checkout_at = NOW(), 
                latitude_out = :lat, 
                longitude_out = :lng,
                observacao = :obs,
                foto_checkout = :foto,
                status = "concluida"
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'   => $id,
            'lat'  => $lat,
            'lng'  => $lng,
            'obs'  => $data['observacao'] ?? null,
            'foto' => $data['foto'] ?? null,
        ]);
    }

    /**
     * Salvar observação durante a visita
     */
    public function salvarObservacao(int $id, string $texto): bool
    {
        $stmt = $this->db->prepare('UPDATE visitas SET observacao = :obs WHERE id = :id');
        return $stmt->execute(['id' => $id, 'obs' => $texto]);
    }

    /**
     * Salvar fotos de trabalho (JSON array de paths)
     */
    public function salvarFotos(int $id, array $fotos): bool
    {
        $stmt = $this->db->prepare('UPDATE visitas SET fotos_trabalho = :fotos WHERE id = :id');
        return $stmt->execute(['id' => $id, 'fotos' => json_encode($fotos)]);
    }

    /**
     * Adicionar foto de trabalho ao array existente
     */
    public function adicionarFoto(int $id, string $path): bool
    {
        $visita = $this->findById($id);
        $fotos = json_decode($visita['fotos_trabalho'] ?? '[]', true) ?: [];
        $fotos[] = $path;
        return $this->salvarFotos($id, $fotos);
    }

    /**
     * Visitas de hoje (para dashboard)
     */
    public function hoje(int $promotorId = null): array
    {
        $sql = 'SELECT v.*, p.nome AS pdv_nome, p.endereco AS pdv_endereco,
                       p.latitude AS pdv_lat, p.longitude AS pdv_lng,
                       p.cidade AS pdv_cidade, p.bairro AS pdv_bairro,
                       u.nome AS promotor_nome, u.avatar AS promotor_avatar
                FROM visitas v
                JOIN pdvs p ON v.pdv_id = p.id
                JOIN users u ON v.promotor_id = u.id
                WHERE v.data_prevista = CURDATE()';
        $params = [];

        if ($promotorId) {
            $sql .= ' AND v.promotor_id = :promotor_id';
            $params['promotor_id'] = $promotorId;
        }

        $sql .= ' ORDER BY v.checkin_at DESC, v.data_prevista ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Conta visitas de hoje
     */
    public function countHoje(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM visitas WHERE data_prevista = CURDATE()');
        return (int) $stmt->fetchColumn();
    }

    /**
     * Últimos check-ins (para activity feed)
     */
    public function ultimosCheckins(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT v.checkin_at, p.nome AS pdv_nome, u.nome AS promotor_nome, u.avatar AS promotor_avatar, v.status
             FROM visitas v
             JOIN pdvs p ON v.pdv_id = p.id
             JOIN users u ON v.promotor_id = u.id
             WHERE v.checkin_at IS NOT NULL
             ORDER BY v.checkin_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Verifica se tem visita em andamento (impede múltiplas simultâneas)
     */
    public function emAndamento(int $promotorId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, p.nome AS pdv_nome
             FROM visitas v
             JOIN pdvs p ON v.pdv_id = p.id
             WHERE v.promotor_id = :pid AND v.status = "em_andamento"
             LIMIT 1'
        );
        $stmt->execute(['pid' => $promotorId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
