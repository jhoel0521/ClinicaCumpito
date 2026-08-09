<?php

use App\ValueObjects\PaperSize;

test('el formato de receta coincide con las dimensiones del diseño aprobado', function (): void {
    $paper = PaperSize::prescription();

    expect($paper->widthMm)->toBe(129.91)
        ->and($paper->heightMm)->toBe(210.08)
        ->and($paper->marginMm)->toBe(0.0)
        ->and($paper->toDompdf())->toBe([0, 0, 129.91, 210.08]);
});
