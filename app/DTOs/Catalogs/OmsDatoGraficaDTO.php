<?php

namespace App\DTOs\Catalogs;

class OmsDatoGraficaDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $oms_catalogo_grafica_id,
        public readonly float $x_value,
        public readonly float $l_value,
        public readonly float $m_value,
        public readonly float $s_value,
        public readonly ?float $sd3neg = null,
        public readonly ?float $sd2neg = null,
        public readonly ?float $sd1neg = null,
        public readonly ?float $sd0 = null,
        public readonly ?float $sd1 = null,
        public readonly ?float $sd2 = null,
        public readonly ?float $sd3 = null,
        public readonly ?float $p3 = null,
        public readonly ?float $p15 = null,
        public readonly ?float $p50 = null,
        public readonly ?float $p85 = null,
        public readonly ?float $p97 = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            oms_catalogo_grafica_id: $data['oms_catalogo_grafica_id'],
            x_value: (float) $data['x_value'],
            l_value: (float) $data['l_value'],
            m_value: (float) $data['m_value'],
            s_value: (float) $data['s_value'],
            sd3neg: isset($data['sd3neg']) ? (float) $data['sd3neg'] : null,
            sd2neg: isset($data['sd2neg']) ? (float) $data['sd2neg'] : null,
            sd1neg: isset($data['sd1neg']) ? (float) $data['sd1neg'] : null,
            sd0: isset($data['sd0']) ? (float) $data['sd0'] : null,
            sd1: isset($data['sd1']) ? (float) $data['sd1'] : null,
            sd2: isset($data['sd2']) ? (float) $data['sd2'] : null,
            sd3: isset($data['sd3']) ? (float) $data['sd3'] : null,
            p3: isset($data['p3']) ? (float) $data['p3'] : null,
            p15: isset($data['p15']) ? (float) $data['p15'] : null,
            p50: isset($data['p50']) ? (float) $data['p50'] : null,
            p85: isset($data['p85']) ? (float) $data['p85'] : null,
            p97: isset($data['p97']) ? (float) $data['p97'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'oms_catalogo_grafica_id' => $this->oms_catalogo_grafica_id,
            'x_value' => $this->x_value,
            'l_value' => $this->l_value,
            'm_value' => $this->m_value,
            's_value' => $this->s_value,
            'sd3neg' => $this->sd3neg,
            'sd2neg' => $this->sd2neg,
            'sd1neg' => $this->sd1neg,
            'sd0' => $this->sd0,
            'sd1' => $this->sd1,
            'sd2' => $this->sd2,
            'sd3' => $this->sd3,
            'p3' => $this->p3,
            'p15' => $this->p15,
            'p50' => $this->p50,
            'p85' => $this->p85,
            'p97' => $this->p97,
        ], fn ($value) => ! is_null($value));
    }
}
