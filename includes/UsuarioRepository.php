<?php

class UsuarioRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarPorUsuario(string $nombreUsuario): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, e.nombre AS empresa_nombre
             FROM usuarios u
             LEFT JOIN empresas e ON e.id = u.empresa_id
             WHERE u.nombre_usuario = :u'
        );
        $stmt->execute(['u' => $nombreUsuario]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function existeUsuario(string $nombreUsuario): bool
    {
        return $this->buscarPorUsuario($nombreUsuario) !== null;
    }

    /**
     * Crea (o reutiliza) la empresa por nombre y una cuenta de tipo "empresa"
     * asociada a ella.
     */
    public function crearEmpresaUsuario(string $nombreEmpresa, string $nombreUsuario, string $password): int
    {
        require_once __DIR__ . '/EmpresaRepository.php';
        $empresaRepo = new EmpresaRepository($this->pdo);
        $empresaId = $empresaRepo->obtenerOCrear($nombreEmpresa);

        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (empresa_id, nombre_usuario, password_hash, rol)
             VALUES (:empresa_id, :usuario, :hash, "empresa")'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'usuario'    => $nombreUsuario,
            'hash'       => password_hash($password, PASSWORD_DEFAULT),
        ]);
        return (int) $this->pdo->lastInsertId();
    }
}
