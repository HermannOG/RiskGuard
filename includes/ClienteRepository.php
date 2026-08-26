<?php


class ClienteRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Devuelve todos los proyectos activos, ordenados */
    public function listarProyectos(string $lang = 'es'): array
    {
        $col_titulo = "titulo_$lang";
        $col_desc   = "desc_$lang";

        $stmt = $this->pdo->query("
            SELECT p.id,
                   p.{$col_titulo}  AS titulo,
                   p.{$col_desc}    AS descripcion,
                   p.icono, p.etiquetas, p.url_demo, p.destacado, p.orden,
                   c.nombre         AS cliente_nombre,
                   c.sector         AS cliente_sector
            FROM proyectos p
            LEFT JOIN clientes c ON c.id = p.cliente_id
            WHERE p.activo = 1
            ORDER BY p.destacado DESC, p.orden ASC, p.id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /** Obtiene un cliente por su slug */
public function obtenerPorSlug(string $slug): ?array
{
    $stmt = $this->pdo->prepare(
        "SELECT * FROM clientes WHERE slug = :slug AND activo = 1"
    );
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/** Proyectos de un cliente específico */
public function listarPorCliente(int $clienteId, string $lang = 'es'): array
{
    $col_titulo = "titulo_$lang";
    $col_desc   = "desc_$lang";

    $stmt = $this->pdo->prepare("
        SELECT p.id,
               p.{$col_titulo}  AS titulo,
               p.{$col_desc}    AS descripcion,
               p.icono, p.etiquetas, p.url_demo, p.destacado, p.orden
        FROM proyectos p
        WHERE p.cliente_id = :cid AND p.activo = 1
        ORDER BY p.orden ASC, p.id ASC
    ");
    $stmt->execute([':cid' => $clienteId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /** Solo los destacados (para el preview en el home) */
    public function listarDestacados(string $lang = 'es', int $limite = 3): array
    {
        $col_titulo = "titulo_$lang";
        $col_desc   = "desc_$lang";

        $stmt = $this->pdo->prepare("
            SELECT p.id,
                   p.{$col_titulo}  AS titulo,
                   p.{$col_desc}    AS descripcion,
                   p.icono, p.etiquetas, p.url_demo
            FROM proyectos p
            WHERE p.activo = 1 AND p.destacado = 1
            ORDER BY p.orden ASC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Guarda o actualiza un proyecto (solo admin) */
    public function guardar(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->pdo->prepare("
                UPDATE proyectos SET
                    titulo_es=:tes, titulo_en=:ten,
                    desc_es=:des,   desc_en=:den,
                    icono=:icono,   etiquetas=:etiquetas,
                    url_demo=:url,  destacado=:destacado,
                    orden=:orden,   cliente_id=:cliente_id
                WHERE id=:id
            ");
            $stmt->execute([...array_intersect_key($data, array_flip([
                'tes','ten','des','den','icono','etiquetas','url','destacado','orden','cliente_id','id'
            ]))]);
            return (int)$data['id'];
        }
        $stmt = $this->pdo->prepare("
            INSERT INTO proyectos
                (titulo_es,titulo_en,desc_es,desc_en,icono,etiquetas,url_demo,destacado,orden,cliente_id)
            VALUES
                (:tes,:ten,:des,:den,:icono,:etiquetas,:url,:destacado,:orden,:cliente_id)
        ");
        $stmt->execute($data);
        return (int)$this->pdo->lastInsertId();
    }
}
