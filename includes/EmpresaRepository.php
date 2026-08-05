<?php

class EmpresaRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function normalizar(string $nombre): string
    {
        $nombre = mb_strtolower(trim($nombre), 'UTF-8');
        return preg_replace('/\s+/', ' ', $nombre);
    }

    /**
     * Busca la empresa por su nombre normalizado; si no existe, la crea.
     * Esto es lo que "conecta" las evaluaciones de la misma empresa entre años.
     */
    public function obtenerOCrear(string $nombre): int
    {
        $normalizado = $this->normalizar($nombre);

        $stmt = $this->pdo->prepare('SELECT id FROM empresas WHERE nombre_normalizado = :norm');
        $stmt->execute(['norm' => $normalizado]);
        $fila = $stmt->fetch();

        if ($fila) {
            return (int) $fila['id'];
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO empresas (nombre, nombre_normalizado) VALUES (:nombre, :norm)'
        );
        $stmt->execute(['nombre' => $nombre, 'norm' => $normalizado]);
        return (int) $this->pdo->lastInsertId();
    }

    public function historial(int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM evaluaciones WHERE empresa_id = :empresa_id ORDER BY fecha_evaluacion DESC'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }
}
