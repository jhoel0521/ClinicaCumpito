<?php

namespace App\Services;

use App\Contracts\DoctorServiceContract;
use App\DTOs\DoctorDTO;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DoctorService implements DoctorServiceContract
{
    /**
     * {@inheritdoc}
     */
    public function findByUserId(string $userId): ?Doctor
    {
        /** @var Doctor|null $doctor */
        $doctor = Doctor::where('user_id', $userId)->first();

        return $doctor;
    }

    /**
     * {@inheritdoc}
     */
    public function create(DoctorDTO $dto): Doctor
    {
        return DB::transaction(function () use ($dto) {
            /** @var Doctor $doctor */
            $doctor = Doctor::create($dto->toArray());

            return $doctor;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $id, DoctorDTO $dto): Doctor
    {
        return DB::transaction(function () use ($id, $dto) {
            /** @var Doctor $doctor */
            $doctor = Doctor::findOrFail($id);
            $doctor->update($dto->toArray());

            return $doctor;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveDoctors(): Collection
    {
        /** @var Collection<int, Doctor> $doctors */
        $doctors = Doctor::where('active', true)->get();

        return $doctors;
    }
}
