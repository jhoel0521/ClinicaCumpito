<?php

namespace App\DTOs\Catalogs;

class OmsCatalogoGraficaDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $nombre,
        public readonly string $codigo,
        public readonly ?string $descripcion = null,
        public readonly string $tipo_grafica = 'peso_talla',
        public readonly string $rango_edad = '',
        public readonly string $sexo = 'M',
        public readonly int $minimo_z_score = -3,
        public readonly int $maximo_z_score = 3,
        public readonly int $minimo_percentil = 3,
        public readonly int $maximo_percentil = 97,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nombre: $data['nombre'],
            codigo: $data['codigo'],
            descripcion: $data['descripcion'] ?? null,
            tipo_grafica: $data['tipo_grafica'] ?? 'peso_talla',
            rango_edad: $data['rango_edad'] ?? '',
            sexo: $data['sexo'] ?? 'M',
            minimo_z_score: isset($data['minimo_z_score']) ? (int) $data['minimo_z_score'] : -3,
            maximo_z_score: isset($data['maximo_z_score']) ? (int) $data['maximo_z_score'] : 3,
            minimo_percentil: isset($data['minimo_percentil']) ? (int) $data['minimo_percentil'] : 3,
            maximo_percentil: isset($data['maximo_percentil']) ? (int) $data['maximo_percentil'] : 97,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            'tipo_grafica' => $this->tipo_grafica,
            'rango_edad' => $this->rango_edad,
            'sexo' => $this->sexo,
            'minimo_z_score' => $this->minimo_z_score,
            'maximo_z_score' => $this->maximo_z_score,
            'minimo_percentil' => $this->minimo_percentil,
            'maximo_percentil' => $this->maximo_percentil,
        ], fn ($value) => ! is_null($value));
    }
}
