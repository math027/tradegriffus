<?php
/**
 * Model Rota — Rotas fixas semanais e exceções
 */
class Rota
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // =============================================
    // CRUD Básico
    // =============================================

    /**
     * Retorna todas as rotas fixas de um promotor (7 dias)
     */
    public function porPromotor(int $promotorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM rotas WHERE promotor_id = ? AND ativo = 1 ORDER BY dia_semana"
        );
        $stmt->execute([$promotorId]);
        return $stmt->fetchAll();
    }

    /**
     * Retorna a rota de um promotor em determinado dia da semana
     */
    public function porPromotorDia(int $promotorId, int $diaSemana): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM rotas WHERE promotor_id = ? AND dia_semana = ? AND ativo = 1"
        );
        $stmt->execute([$promotorId, $diaSemana]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Cria rota fixa para um dia da semana (se não existir)
     */
    public function criarSeNaoExiste(int $promotorId, int $diaSemana, int $createdBy): int
    {
        $existente = $this->porPromotorDia($promotorId, $diaSemana);
        if ($existente) {
            return $existente['id'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO rotas (promotor_id, dia_semana, created_by) VALUES (?, ?, ?)"
        );
        $stmt->execute([$promotorId, $diaSemana, $createdBy]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Garante que um promotor tem rotas para os 5 dias úteis
     */
    public function garantirSemanaCompleta(int $promotorId, int $createdBy): void
    {
        for ($dia = 1; $dia <= 5; $dia++) {
            $this->criarSeNaoExiste($promotorId, $dia, $createdBy);
        }
    }

    // =============================================
    // PDVs Fixos
    // =============================================

    /**
     * Retorna PDVs fixos de uma rota (com dados do PDV)
     */
    public function pdvsFixos(int $rotaId): array
    {
        $stmt = $this->db->prepare(
            "SELECT rp.id AS rota_pdv_id, rp.ordem, p.*
             FROM rota_pdvs rp
             JOIN pdvs p ON p.id = rp.pdv_id
             WHERE rp.rota_id = ?
             ORDER BY rp.ordem ASC"
        );
        $stmt->execute([$rotaId]);
        return $stmt->fetchAll();
    }

    /**
     * Adiciona PDV fixo à rota
     */
    public function adicionarPdvFixo(int $rotaId, int $pdvId): void
    {
        // Próxima ordem
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(ordem), 0) + 1 AS prox FROM rota_pdvs WHERE rota_id = ?"
        );
        $stmt->execute([$rotaId]);
        $ordem = $stmt->fetch()['prox'];

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO rota_pdvs (rota_id, pdv_id, ordem) VALUES (?, ?, ?)"
        );
        $stmt->execute([$rotaId, $pdvId, $ordem]);
    }

    /**
     * Remove PDV fixo da rota
     */
    public function removerPdvFixo(int $rotaId, int $pdvId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM rota_pdvs WHERE rota_id = ? AND pdv_id = ?"
        );
        $stmt->execute([$rotaId, $pdvId]);
    }

    /**
     * Atualiza a ordem dos PDVs fixos (drag and drop)
     */
    public function reordenarPdvs(int $rotaId, array $pdvIds): void
    {
        $stmt = $this->db->prepare(
            "UPDATE rota_pdvs SET ordem = ? WHERE rota_id = ? AND pdv_id = ?"
        );
        foreach ($pdvIds as $ordem => $pdvId) {
            $stmt->execute([$ordem, $rotaId, (int) $pdvId]);
        }
    }

    // =============================================
    // Exceções temporárias
    // =============================================

    /**
     * Adiciona exceção temporária (só naquele dia)
     */
    public function adicionarExcecao(int $rotaId, string $data, string $tipo, int $pdvId, int $createdBy): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO rota_excecoes (rota_id, data, tipo, pdv_id, created_by) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$rotaId, $data, $tipo, $pdvId, $createdBy]);
    }

    /**
     * Remove exceção temporária
     */
    public function removerExcecao(int $excecaoId): void
    {
        $stmt = $this->db->prepare("DELETE FROM rota_excecoes WHERE id = ?");
        $stmt->execute([$excecaoId]);
    }

    /**
     * Exceções de uma rota em uma data
     */
    public function excecoesDoDia(int $rotaId, string $data): array
    {
        $stmt = $this->db->prepare(
            "SELECT re.*, p.nome AS pdv_nome, p.endereco, p.latitude, p.longitude
             FROM rota_excecoes re
             JOIN pdvs p ON p.id = re.pdv_id
             WHERE re.rota_id = ? AND re.data = ?"
        );
        $stmt->execute([$rotaId, $data]);
        return $stmt->fetchAll();
    }

    // =============================================
    // Semana efetiva (fixos + exceções calculados)
    // =============================================

    /**
     * Monta a semana útil de um promotor a partir de uma data (segunda-feira)
     * Retorna array com 5 dias (seg-sex), cada um com PDVs efetivos
     */
    public function semanaEfetiva(int $promotorId, string $dataInicio): array
    {
        $diasNome = ['', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];
        $semana = [];

        for ($i = 0; $i < 5; $i++) {
            $data = date('Y-m-d', strtotime($dataInicio . " +{$i} days"));
            $diaSemana = $i + 1; // 1=seg
            $rota = $this->porPromotorDia($promotorId, $diaSemana);

            $dia = [
                'data'       => $data,
                'dia_semana' => $diaSemana,
                'dia_nome'   => $diasNome[$diaSemana],
                'rota_id'    => $rota ? $rota['id'] : null,
                'pdvs'       => [],
            ];

            if ($rota) {
                // PDVs fixos
                $fixos = $this->pdvsFixos($rota['id']);
                foreach ($fixos as $pdv) {
                    $pdv['origem'] = 'fixo';
                    $dia['pdvs'][] = $pdv;
                }

                // Exceções do dia
                $excecoes = $this->excecoesDoDia($rota['id'], $data);
                foreach ($excecoes as $exc) {
                    if ($exc['tipo'] === 'adicionar_pdv') {
                        $dia['pdvs'][] = [
                            'id'        => $exc['pdv_id'],
                            'nome'      => $exc['pdv_nome'],
                            'endereco'  => $exc['endereco'],
                            'latitude'  => $exc['latitude'],
                            'longitude' => $exc['longitude'],
                            'origem'    => 'temporario',
                            'excecao_id' => $exc['id'],
                            'ordem'     => $exc['ordem'],
                        ];
                    } elseif ($exc['tipo'] === 'remover_pdv') {
                        // Remove o PDV fixo da lista
                        $dia['pdvs'] = array_filter($dia['pdvs'], function($p) use ($exc) {
                            return $p['id'] != $exc['pdv_id'];
                        });
                        $dia['pdvs'] = array_values($dia['pdvs']);
                    }
                }

                // Reordena por ordem
                usort($dia['pdvs'], fn($a, $b) => ($a['ordem'] ?? 99) - ($b['ordem'] ?? 99));
            }

            $semana[] = $dia;
        }

        return $semana;
    }

    // =============================================
    // PDVs em Batch (split-screen)
    // =============================================

    /**
     * Sincroniza PDVs fixos de uma rota (substitui toda a lista)
     */
    public function sincronizarPdvsFixos(int $rotaId, array $pdvIds): void
    {
        // Remove todos os PDVs fixos atuais
        $this->db->prepare("DELETE FROM rota_pdvs WHERE rota_id = ?")->execute([$rotaId]);

        // Insere os novos na ordem recebida
        $stmt = $this->db->prepare("INSERT INTO rota_pdvs (rota_id, pdv_id, ordem) VALUES (?, ?, ?)");
        foreach ($pdvIds as $ordem => $pdvId) {
            $stmt->execute([$rotaId, (int) $pdvId, $ordem]);
        }
    }

    // =============================================
    // Pesquisas por Rota
    // =============================================

    /**
     * Retorna pesquisas vinculadas a uma rota
     */
    public function pesquisasDaRota(int $rotaId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.* FROM rota_pesquisas rp
             JOIN pesquisas p ON p.id = rp.pesquisa_id
             WHERE rp.rota_id = ?"
        );
        $stmt->execute([$rotaId]);
        return $stmt->fetchAll();
    }

    /**
     * Sincroniza pesquisas de uma rota (substitui toda a lista)
     */
    public function sincronizarPesquisas(int $rotaId, array $pesquisaIds): void
    {
        $this->db->prepare("DELETE FROM rota_pesquisas WHERE rota_id = ?")->execute([$rotaId]);

        $stmt = $this->db->prepare("INSERT INTO rota_pesquisas (rota_id, pesquisa_id) VALUES (?, ?)");
        foreach ($pesquisaIds as $pesquisaId) {
            $stmt->execute([$rotaId, (int) $pesquisaId]);
        }
    }

    /**
     * Retorna pesquisas da semana completa
     */
    public function pesquisasDaSemana(int $promotorId): array
    {
        $rotas = $this->porPromotor($promotorId);
        $resultado = [];
        foreach ($rotas as $rota) {
            $resultado[$rota['dia_semana']] = $this->pesquisasDaRota($rota['id']);
        }
        return $resultado;
    }

    /**
     * Otimiza a ordem dos PDVs por proximidade geográfica
     * Algoritmo: nearest neighbor (vizinho mais próximo)
     */
    public function otimizarOrdem(int $rotaId): void
    {
        $pdvs = $this->pdvsFixos($rotaId);
        if (count($pdvs) <= 1) return;

        // Filtra PDVs com coordenadas
        $comCoord = array_filter($pdvs, fn($p) => $p['latitude'] && $p['longitude']);
        $semCoord = array_filter($pdvs, fn($p) => !$p['latitude'] || !$p['longitude']);

        if (count($comCoord) <= 1) return; // Nada a otimizar

        // Nearest neighbor
        $comCoord = array_values($comCoord);
        $ordenados = [$comCoord[0]];
        $restantes = array_slice($comCoord, 1);

        while (count($restantes) > 0) {
            $ultimo = end($ordenados);
            $menorDist = PHP_FLOAT_MAX;
            $menorIdx = 0;

            foreach ($restantes as $idx => $pdv) {
                $dist = $this->haversine(
                    $ultimo['latitude'], $ultimo['longitude'],
                    $pdv['latitude'], $pdv['longitude']
                );
                if ($dist < $menorDist) {
                    $menorDist = $dist;
                    $menorIdx = $idx;
                }
            }

            $ordenados[] = $restantes[$menorIdx];
            array_splice($restantes, $menorIdx, 1);
            $restantes = array_values($restantes);
        }

        // Adiciona os sem coordenadas no final
        $ordenados = array_merge($ordenados, array_values($semCoord));

        // Salva nova ordem
        $ids = array_map(fn($p) => $p['id'], $ordenados);
        $this->reordenarPdvs($rotaId, $ids);
    }

    /**
     * Distância Haversine entre 2 pontos (em km)
     */
    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371; // Raio da Terra em km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
