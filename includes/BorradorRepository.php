<?php

class BorradorRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Devuelve el borrador de una empresa (o null si no tiene ninguno en curso). */
    public function obtener(int $empresaId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM evaluacion_borradores WHERE empresa_id = :empresa_id'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        $fila = $stmt->fetch();
        if (!$fila) {
            return null;
        }
        $fila['respuestas'] = json_decode($fila['respuestas_json'], true) ?: [];
        return $fila;
    }

    /**
     * Crea o actualiza el borrador de la empresa (uno solo por empresa: cada
     * autoguardado sobrescribe el anterior).
     */
    public function guardar(int $empresaId, ?int $usuarioId, string $organizacion, string $evaluador, string $fecha, array $respuestas): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO evaluacion_borradores (empresa_id, usuario_id, organizacion, evaluador, fecha, respuestas_json)
             VALUES (:empresa_id, :usuario_id, :organizacion, :evaluador, :fecha, :respuestas_json)
             ON DUPLICATE KEY UPDATE
                usuario_id = VALUES(usuario_id),
                organizacion = VALUES(organizacion),
                evaluador = VALUES(evaluador),
                fecha = VALUES(fecha),
                respuestas_json = VALUES(respuestas_json),
                actualizado_en = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'empresa_id'      => $empresaId,
            'usuario_id'      => $usuarioId,
            'organizacion'    => $organizacion,
            'evaluador'       => $evaluador,
            'fecha'           => $fecha,
            'respuestas_json' => json_encode($respuestas, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /** Borra el progreso guardado de una empresa (al terminar la evaluación o descartarla). */
    public function eliminar(int $empresaId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM evaluacion_borradores WHERE empresa_id = :empresa_id');
        $stmt->execute(['empresa_id' => $empresaId]);
    }
}