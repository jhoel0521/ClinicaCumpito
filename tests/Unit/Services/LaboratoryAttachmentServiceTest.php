<?php

use App\Models\Consultation;
use App\Models\LaboratoryAttachment;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Services\LaboratoryAttachmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('LaboratoryAttachmentService', function (): void {
    test('replaceForRequest conserva un único archivo y limpia adjuntos antiguos de la solicitud y sus parámetros', function (): void {
        Storage::fake('public');

        $request = LaboratoryRequest::factory()->create([
            'consultation_id' => Consultation::factory()->create(['status' => 'saved'])->id,
            'status' => 'pending',
        ]);
        $item = LaboratoryRequestItem::factory()->create(['laboratory_request_id' => $request->id]);

        Storage::disk('public')->put('lab-attachments/old-order.pdf', 'orden');
        Storage::disk('public')->put('lab-attachments/old-item.jpg', 'parametro');

        LaboratoryAttachment::create([
            'laboratory_request_id' => $request->id,
            'file_path' => 'lab-attachments/old-order.pdf',
            'original_name' => 'orden-anterior.pdf',
            'mime_type' => 'application/pdf',
        ]);
        LaboratoryAttachment::create([
            'laboratory_request_item_id' => $item->id,
            'file_path' => 'lab-attachments/old-item.jpg',
            'original_name' => 'placa-anterior.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $attachment = app(LaboratoryAttachmentService::class)->replaceForRequest(
            $request->id,
            UploadedFile::fake()->create('estudio-completo.pdf', 120, 'application/pdf'),
        );

        expect(LaboratoryAttachment::query()->count())->toBe(1)
            ->and($attachment->laboratory_request_id)->toBe($request->id)
            ->and($attachment->laboratory_request_item_id)->toBeNull()
            ->and($attachment->original_name)->toBe('estudio-completo.pdf')
            ->and(Storage::disk('public')->exists($attachment->file_path))->toBeTrue()
            ->and(Storage::disk('public')->exists('lab-attachments/old-order.pdf'))->toBeFalse()
            ->and(Storage::disk('public')->exists('lab-attachments/old-item.jpg'))->toBeFalse();
    });

    test('replaceForRequest no permite cambiar el archivo de una solicitud recibida', function (): void {
        Storage::fake('public');

        $request = LaboratoryRequest::factory()->create([
            'consultation_id' => Consultation::factory()->create(['status' => 'saved'])->id,
            'status' => 'received',
        ]);

        expect(
            fn () => app(LaboratoryAttachmentService::class)->replaceForRequest(
                $request->id,
                UploadedFile::fake()->create('nuevo.pdf', 20, 'application/pdf'),
            ),
        )->toThrow(DomainException::class, 'solicitud pendiente');

        expect(Storage::disk('public')->allFiles())->toBeEmpty()
            ->and(LaboratoryAttachment::query()->count())->toBe(0);
    });

    test('deleteForRequest rechaza un archivo que pertenece a otra solicitud', function (): void {
        Storage::fake('public');

        $request = LaboratoryRequest::factory()->create(['status' => 'pending']);
        $otherRequest = LaboratoryRequest::factory()->create(['status' => 'pending']);
        $attachment = LaboratoryAttachment::create([
            'laboratory_request_id' => $otherRequest->id,
            'file_path' => 'lab-attachments/other.pdf',
            'original_name' => 'otro.pdf',
            'mime_type' => 'application/pdf',
        ]);

        expect(
            fn () => app(LaboratoryAttachmentService::class)->deleteForRequest($request->id, $attachment->id),
        )->toThrow(ModelNotFoundException::class);

        expect($attachment->fresh())->not->toBeNull();
    });
});
