<?php

namespace App\Services;

use App\Contracts\DoctorServiceContract;
use App\DTOs\DoctorDTO;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DoctorService implements DoctorServiceContract
{
    /**
     * {@inheritdoc}
     */
    public function findByUserId(string $userId): ?Doctor
    {
        $user = User::query()->select(['id', 'doctor_id'])->find($userId);
        if (! $user?->doctor_id) {
            return null;
        }

        /** @var Doctor|null $doctor */
        $doctor = Doctor::find($user->doctor_id);

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

            if ($dto->user_id) {
                User::whereKey($dto->user_id)->update(['doctor_id' => $doctor->id]);
            }

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
