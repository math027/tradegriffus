<?php
/**
 * Model PDV (Ponto de Venda)
 */
class Pdv
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(bool $apenasAtivos = true): array
    {
        $sql = 'SELECT * FROM pdvs';
        if ($apenasAtivos) {
            $sql .= ' WHERE ativo = 1';
        }
        $sql .= ' ORDER BY nome ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pdvs WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pdvs WHERE codigo = :codigo');
        $stmt->execute(['codigo' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        // Se lat/lng não informados, tenta geocodificar
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $coords = $this->geocodeAddress(
                $data['rua'] ?? '',
                $data['numero'] ?? '',
                $data['cidade'] ?? '',
                $data['uf'] ?? ''
            );
            if ($coords) {
                $data['latitude'] = $coords['lat'];
                $data['longitude'] = $coords['lon'];
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO pdvs (codigo, nome, cnpj, rua, numero, bairro, cidade, uf, cep, endereco, latitude, longitude, responsavel, telefone) 
             VALUES (:codigo, :nome, :cnpj, :rua, :numero, :bairro, :cidade, :uf, :cep, :endereco, :latitude, :longitude, :responsavel, :telefone)'
        );
        $stmt->execute([
            'codigo'      => $data['codigo'] ?? null,
            'nome'        => $data['nome'],
            'cnpj'        => $data['cnpj'] ?? null,
            'rua'         => $data['rua'] ?? null,
            'numero'      => $data['numero'] ?? null,
            'bairro'      => $data['bairro'] ?? null,
            'cidade'      => $data['cidade'] ?? null,
            'uf'          => $data['uf'] ?? null,
            'cep'         => $data['cep'] ?? null,
            'endereco'    => $data['endereco'] ?? null,
            'latitude'    => $data['latitude'] ?? null,
            'longitude'   => $data['longitude'] ?? null,
            'responsavel' => $data['responsavel'] ?? null,
            'telefone'    => $data['telefone'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        // Se lat/lng não informados, tenta geocodificar
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $coords = $this->geocodeAddress(
                $data['rua'] ?? '',
                $data['numero'] ?? '',
                $data['cidade'] ?? '',
                $data['uf'] ?? ''
            );
            if ($coords) {
                $data['latitude'] = $coords['lat'];
                $data['longitude'] = $coords['lon'];
            }
        }

        $fields = [];
        $params = ['id' => $id];

        $allowed = ['codigo', 'nome', 'cnpj', 'rua', 'numero', 'bairro', 'cidade', 'uf', 'cep', 'endereco', 'latitude', 'longitude', 'responsavel', 'telefone', 'ativo'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = 'UPDATE pdvs SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE pdvs SET ativo = 0 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM pdvs WHERE ativo = 1')->fetchColumn();
    }

    /**
     * Retorna visitas realizadas neste PDV (com fotos e promotor)
     */
    public function visitasPorPdv(int $pdvId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT v.id, v.checkin_at, v.checkout_at, v.status,
                    v.foto_checkin, v.foto_checkout, v.fotos_trabalho,
                    v.observacao, u.nome AS promotor_nome
             FROM visitas v
             JOIN users u ON v.promotor_id = u.id
             WHERE v.pdv_id = :pdv_id AND v.checkin_at IS NOT NULL
             ORDER BY v.checkin_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue('pdv_id', $pdvId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retorna respostas de pesquisas respondidas neste PDV
     */
    public function respostasPorPdv(int $pdvId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id, r.dados, r.created_at,
                    p.titulo AS pesquisa_titulo, p.campos AS pesquisa_campos,
                    u.nome AS promotor_nome
             FROM respostas r
             JOIN pesquisas p ON r.pesquisa_id = p.id
             JOIN users u ON r.promotor_id = u.id
             WHERE r.pdv_id = :pdv_id
             ORDER BY r.created_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue('pdv_id', $pdvId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retorna todas as visitas com fotos (galeria geral)
     */
    public function todasFotosVisitas(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT v.id, v.checkin_at, v.checkout_at, v.status,
                    v.foto_checkin, v.foto_checkout, v.fotos_trabalho,
                    v.observacao, u.nome AS promotor_nome,
                    p.nome AS pdv_nome, p.id AS pdv_id
             FROM visitas v
             JOIN users u ON v.promotor_id = u.id
             JOIN pdvs p ON v.pdv_id = p.id
             WHERE v.checkin_at IS NOT NULL
               AND (v.foto_checkin IS NOT NULL OR v.fotos_trabalho IS NOT NULL OR v.foto_checkout IS NOT NULL)
             ORDER BY v.checkin_at DESC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue('off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retorna fotos de visitas de hoje para um PDV específico
     */
    public function fotosDoDia(int $pdvId, string $data): array
    {
        $stmt = $this->db->prepare(
            'SELECT v.id, v.checkin_at, v.checkout_at,
                    v.foto_checkin, v.foto_checkout, v.fotos_trabalho,
                    u.nome AS promotor_nome
             FROM visitas v
             JOIN users u ON v.promotor_id = u.id
             WHERE v.pdv_id = :pdv_id
               AND DATE(v.checkin_at) = :data
               AND v.checkin_at IS NOT NULL
             ORDER BY v.checkin_at DESC'
        );
        $stmt->execute(['pdv_id' => $pdvId, 'data' => $data]);
        return $stmt->fetchAll();
    }

    /**
     * Remove fotos e respostas com mais de N dias
     * Retorna array com contagem de itens removidos
     */
    public function limparDadosAntigos(int $dias = 45): array
    {
        $limite = date('Y-m-d H:i:s', strtotime("-{$dias} days"));
        $resultado = ['fotos_removidas' => 0, 'respostas_removidas' => 0, 'arquivos_deletados' => 0];

        // 1. Buscar visitas antigas com fotos
        $stmt = $this->db->prepare(
            'SELECT id, foto_checkin, foto_checkout, fotos_trabalho
             FROM visitas
             WHERE checkin_at < :limite
               AND (foto_checkin IS NOT NULL OR fotos_trabalho IS NOT NULL OR foto_checkout IS NOT NULL)'
        );
        $stmt->execute(['limite' => $limite]);
        $visitasAntigas = $stmt->fetchAll();

        foreach ($visitasAntigas as $v) {
            $arquivos = [];
            if ($v['foto_checkin'])  $arquivos[] = $v['foto_checkin'];
            if ($v['foto_checkout']) $arquivos[] = $v['foto_checkout'];
            $fotos = json_decode($v['fotos_trabalho'] ?? '[]', true) ?: [];
            $arquivos = array_merge($arquivos, $fotos);

            foreach ($arquivos as $arq) {
                $caminho = rtrim($_SERVER['DOCUMENT_ROOT'] ?? (__DIR__ . '/../public'), '/') . '/' . ltrim($arq, '/');
                if (file_exists($caminho)) {
                    unlink($caminho);
                    $resultado['arquivos_deletados']++;
                }
            }
            $resultado['fotos_removidas']++;
        }

        // Limpar campos de fotos nas visitas antigas
        $stmt = $this->db->prepare(
            'UPDATE visitas SET foto_checkin = NULL, foto_checkout = NULL, fotos_trabalho = NULL
             WHERE checkin_at < :limite
               AND (foto_checkin IS NOT NULL OR fotos_trabalho IS NOT NULL OR foto_checkout IS NOT NULL)'
        );
        $stmt->execute(['limite' => $limite]);

        // 2. Deletar respostas antigas
        $stmt = $this->db->prepare('DELETE FROM respostas WHERE created_at < :limite');
        $stmt->execute(['limite' => $limite]);
        $resultado['respostas_removidas'] = $stmt->rowCount();

        return $resultado;
    }

    /**
     * Atualiza coordenadas do PDV somente se os últimos 3 check-ins
     * estiverem na mesma região (~11m / 0.0001 graus)
     */
    public function atualizarCoordenadasSeConsistente(int $id, float $lat, float $lng): bool
    {
        // Busca os últimos 3 check-ins com coordenadas neste PDV
        $stmt = $this->db->prepare(
            'SELECT latitude_in, longitude_in
             FROM visitas
             WHERE pdv_id = :pdv_id AND latitude_in IS NOT NULL AND longitude_in IS NOT NULL
             ORDER BY checkin_at DESC
             LIMIT 3'
        );
        $stmt->execute(['pdv_id' => $id]);
        $ultimos = $stmt->fetchAll();

        // Precisa de pelo menos 3 check-ins
        if (count($ultimos) < 3) {
            return false;
        }

        // Verifica se todos os 3 estão dentro de ~11m da coordenada atual
        $threshold = 0.0001; // ~11 metros
        foreach ($ultimos as $checkin) {
            if (abs($checkin['latitude_in'] - $lat) > $threshold || abs($checkin['longitude_in'] - $lng) > $threshold) {
                return false;
            }
        }

        // Verifica se é diferente das coordenadas atuais do PDV
        $pdv = $this->findById($id);
        if ($pdv && $pdv['latitude'] && $pdv['longitude']) {
            if (abs($pdv['latitude'] - $lat) <= $threshold && abs($pdv['longitude'] - $lng) <= $threshold) {
                return false; // Já está na mesma posição
            }
        }

        // Atualiza as coordenadas do PDV
        $stmt = $this->db->prepare('UPDATE pdvs SET latitude = :lat, longitude = :lng WHERE id = :id');
        return $stmt->execute(['id' => $id, 'lat' => $lat, 'lng' => $lng]);
    }

    /**
     * Retorna os últimos N check-ins com coordenadas para este PDV
     */
    public function ultimosCheckinsComCoordenadas(int $pdvId, int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            'SELECT v.latitude_in, v.longitude_in, v.checkin_at, u.nome AS promotor_nome
             FROM visitas v
             JOIN users u ON v.promotor_id = u.id
             WHERE v.pdv_id = :pdv_id AND v.latitude_in IS NOT NULL AND v.longitude_in IS NOT NULL
             ORDER BY v.checkin_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue('pdv_id', $pdvId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Busca coordenadas na API Nominatim (OpenStreetMap)
     */
    private function geocodeAddress(string $rua, string $numero, string $cidade, string $uf): ?array
    {
        if (empty($cidade) || empty($uf)) {
            return null;
        }

        $query = "{$rua}, {$numero}, {$cidade} - {$uf}, Brasil";
        $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($query) . "&limit=1&email=suporte@griffus.com.br";

        $opts = [
            'http' => [
                'header' => "User-Agent: TradeGriffusApp/1.0\r\n"
            ]
        ];
        
        try {
            $context = stream_context_create($opts);
            $json = file_get_contents($url, false, $context);
            if ($json) {
                $data = json_decode($json, true);
                if (!empty($data) && isset($data[0]['lat'])) {
                    return [
                        'lat' => $data[0]['lat'],
                        'lon' => $data[0]['lon']
                    ];
                }
            }
        } catch (Exception $e) {
            // Silently fail to save without coords
        }

        return null;
    }
}
