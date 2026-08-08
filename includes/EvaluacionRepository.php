<?php

class EvaluacionRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function guardar(array $datos): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO evaluaciones
                    (empresa_id, usuario_id, evaluador, area_evaluada, dba, fecha_evaluacion,
                     pct_confidencialidad, pct_integridad, pct_disponibilidad, pct_global)
                 VALUES
                    (:empresa_id, :usuario_id, :evaluador, :area_evaluada, :dba, :fecha,
                     :pct_c, :pct_i, :pct_d, :pct_global)'
            );
            $stmt->execute([
                'empresa_id'    => $datos['empresa_id'],
                'usuario_id'    => $datos['usuario_id'] ?? null,
                'evaluador'     => $datos['evaluador'],
                'area_evaluada' => $datos['area_evaluada'] ?? null,
                'dba'           => $datos['dba'] ?? null,
                'fecha'         => $datos['fecha'],
                'pct_c'         => $datos['pct']['C'],
                'pct_i'         => $datos['pct']['I'],
                'pct_d'         => $datos['pct']['D'],
                'pct_global'    => $datos['pct_global'],
            ]);
            $evaluacionId = (int) $this->pdo->lastInsertId();

            $stmtResp = $this->pdo->prepare(
                'INSERT INTO evaluacion_respuestas (evaluacion_id, pregunta_id, respuesta, nivel_madurez, comentario)
                 VALUES (:evaluacion_id, :pregunta_id, :respuesta, :nivel_madurez, :comentario)'
            );
            foreach ($datos['respuestas'] as $r) {
                $stmtResp->execute([
                    'evaluacion_id' => $evaluacionId,
                    'pregunta_id'   => $r['pregunta_id'],
                    'respuesta'     => $r['respuesta'],
                    'nivel_madurez' => $r['nivel_madurez'] ?? null,
                    'comentario'    => $r['comentario'] ?? null,
                ]);
            }

            $stmtCtrl = $this->pdo->prepare(
                'INSERT INTO evaluacion_control_resultado (evaluacion_id, control_id, pct_cumplimiento, nivel_madurez)
                 VALUES (:evaluacion_id, :control_id, :pct, :madurez)'
            );
            foreach ($datos['resultadosControl'] as $rc) {
                $stmtCtrl->execute([
                    'evaluacion_id' => $evaluacionId,
                    'control_id'    => $rc['id'],
                    'pct'           => $rc['pctControl'],
                    'madurez'       => $rc['madurez'],
                ]);
            }

            $this->pdo->commit();
            return $evaluacionId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Trae una evaluación por id, junto con el nombre de la empresa.
     * Devuelve null si no existe.
     */
    public function obtenerPorId(int $evaluacionId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ev.*, e.nombre AS empresa
             FROM evaluaciones ev
             JOIN empresas e ON e.id = ev.empresa_id
             WHERE ev.id = :id'
        );
        $stmt->execute(['id' => $evaluacionId]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    /** Resultados por control (pct de cumplimiento y nivel de madurez), guardados al calcular. */
    public function resultadosControl(int $evaluacionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT control_id, pct_cumplimiento, nivel_madurez
             FROM evaluacion_control_resultado
             WHERE evaluacion_id = :id'
        );
        $stmt->execute(['id' => $evaluacionId]);
        $filas = $stmt->fetchAll();

        $porControl = [];
        foreach ($filas as $f) {
            $porControl[(int) $f['control_id']] = [
                'pctControl' => (float) $f['pct_cumplimiento'],
                'madurez'    => (int) $f['nivel_madurez'],
            ];
        }
        return $porControl;
    }

    /** Respuestas guardadas (respuesta si/no/na, nivel de madurez manual, comentario) por pregunta. */
    public function respuestas(int $evaluacionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pregunta_id, respuesta, nivel_madurez, comentario
             FROM evaluacion_respuestas
             WHERE evaluacion_id = :id'
        );
        $stmt->execute(['id' => $evaluacionId]);
        $filas = $stmt->fetchAll();

        $porPregunta = [];
        foreach ($filas as $f) {
            $porPregunta[(int) $f['pregunta_id']] = [
                'respuesta'    => $f['respuesta'],
                'nivelMadurez' => $f['nivel_madurez'] !== null ? (int) $f['nivel_madurez'] : null,
                'comentario'   => $f['comentario'],
            ];
        }
        return $porPregunta;
    }
}