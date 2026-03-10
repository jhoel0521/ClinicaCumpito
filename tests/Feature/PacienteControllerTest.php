<?php

use App\Models\MedicalCondition;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->patient = Patient::factory()->create(['user_id' => $this->user->id]);
    $this->condition = MedicalCondition::factory()->create();
});

describe('PacienteController - Index', function () {
    test('usuario autenticado puede ver lista de pacientes', function () {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.index'));

        $response->assertStatus(200)
            ->assertViewIs('pacientes.index')
            ->assertSeeLivewire('patient-list')
            ->assertSee('data-ui="button"', false);
    });

    test('usuario no autenticado es redirigido a login', function () {
        $response = $this->get(route('pacientes.index'));

        $response->assertRedirect(route('login'));
    });

    test('la lista de pacientes está paginada', function () {
        Patient::factory(20)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire('patient-list');
    });
});

describe('PacienteController - Create', function () {
    test('usuario autenticado puede ver formulario de creación', function () {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.create'));

        $response->assertStatus(200)
            ->assertViewIs('pacientes.create')
            ->assertViewHas('conditions')
            ->assertSee('Nuevo Paciente');
    });

    test('formulario incluye lista de condiciones médicas', function () {
        MedicalCondition::factory()->count(3)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.create'));

        $response->assertViewHas('conditions', function ($conditions) {
            return $conditions->count() >= 3;
        });
    });
});

describe('PacienteController - Store', function () {
    test('puede crear un paciente con datos válidos', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Juan Pérez García',
            'date_of_birth' => '2010-05-15',
            'gender' => 'M',
            'blood_group' => 'O+',
            'allergies' => 'Penicilina',
            'pathologies' => 'Asma leve',
            'surgeries' => 'Apéndice removido',
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();  // Verify it redirects somewhere
        $this->assertDatabaseHas('patients', [
            'full_name' => 'Juan Pérez García',
            'gender' => 'M',
        ]);
    });

    test('falla al crear paciente sin nombre', function () {
        $user = User::factory()->create();

        $data = [
            'date_of_birth' => '2010-05-15',
            'gender' => 'M',
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertSessionHasErrors('full_name');
    });

    test('falla al crear paciente sin fecha de nacimiento', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Juan Pérez García',
            'gender' => 'M',
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertSessionHasErrors('date_of_birth');
    });

    test('falla al crear paciente sin género', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Juan Pérez García',
            'date_of_birth' => '2010-05-15',
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertSessionHasErrors('gender');
    });

    test('falla si fecha de nacimiento es futura', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Juan Pérez García',
            'date_of_birth' => now()->addDays(10)->format('Y-m-d'),
            'gender' => 'M',
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertSessionHasErrors('date_of_birth');
    });

    test('falla si género es inválido', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Juan Pérez García',
            'date_of_birth' => '2010-05-15',
            'gender' => 'X',
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertSessionHasErrors('gender');
    });

    test('puede crear paciente con condiciones médicas', function () {
        $user = User::factory()->create();
        $condition = MedicalCondition::factory()->create();

        $data = [
            'full_name' => 'Juan Pérez García',
            'date_of_birth' => '2010-05-15',
            'gender' => 'M',
            'medical_conditions' => [
                $condition->id => [
                    'condition_id' => $condition->id,
                    'status' => 'Positive',
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('patient_medical_conditions', [
            'medical_condition_id' => $condition->id,
            'status' => 'Positive',
        ]);
    });

    test('puede crear paciente con grupo sanguineo valido', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Paciente con Grupo',
            'date_of_birth' => '2019-10-15',
            'gender' => 'F',
            'blood_group' => 'AB-',
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('patients', [
            'full_name' => 'Paciente con Grupo',
            'blood_group' => 'AB-',
        ]);
    });

    test('falla si grupo sanguineo es invalido al crear', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Paciente Grupo Invalido',
            'date_of_birth' => '2018-05-11',
            'gender' => 'M',
            'blood_group' => 'X+',
        ];

        $response = $this->actingAs($user)
            ->post(route('pacientes.store'), $data);

        $response->assertSessionHasErrors('blood_group');
    });

    test('puede crear paciente sin especificar grupo sanguineo', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Paciente Sin Grupo',
            'date_of_birth' => '2010-05-15',
            'gender' => 'M',
        ];

        $this->actingAs($user)
            ->post(route('pacientes.store'), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('patients', [
            'full_name' => 'Paciente Sin Grupo',
            'blood_group' => null,
        ]);
    });

    test('puede crear paciente con cada grupo sanguineo valido', function (string $group) {
        $user = User::factory()->create();

        $data = [
            'full_name' => "Paciente {$group}",
            'date_of_birth' => '2010-05-15',
            'gender' => 'M',
            'blood_group' => $group,
        ];

        $this->actingAs($user)
            ->post(route('pacientes.store'), $data)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('patients', ['blood_group' => $group]);
    })->with(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']);
});

describe('PacienteController - Show', function () {
    test('usuario autenticado puede ver dashboard del paciente', function () {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.show', $this->patient->id));

        $response->assertStatus(200)
            ->assertViewIs('pacientes.show')
            ->assertViewHas('patient');
    });

    test('el paciente mostrado es el correcto', function () {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.show', $this->patient->id));

        $response->assertViewHas('patient', function ($patient) {
            return $patient->id === $this->patient->id;
        });
    });

    test('dashboard muestra condiciones médicas del paciente', function () {
        $this->patient->medicalConditions()->attach(
            $this->condition->id,
            ['status' => 'Positive']
        );

        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.show', $this->patient->id));

        $response->assertViewHas('patient', function ($patient) {
            return $patient->medicalConditions->contains($this->condition->id);
        });
    });

    test('usuario no autenticado no puede ver dashboard', function () {
        $response = $this->get(route('pacientes.show', $this->patient->id));

        $response->assertRedirect(route('login'));
    });

    test('dashboard muestra grupo sanguineo del paciente', function () {
        $patient = Patient::factory()->create([
            'blood_group' => 'O-',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.show', $patient->id));

        $response->assertStatus(200)
            ->assertSee('O-');
    });

    test('dashboard muestra edad con formato clinico exacto', function () {
        Carbon::setTestNow('2026-02-26');

        $patient = Patient::factory()->create([
            'date_of_birth' => '2026-02-12',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.show', $patient->id));

        $response->assertStatus(200)
            ->assertSee('14 días');

        Carbon::setTestNow();
    });
});

describe('PacienteController - Edit', function () {
    test('usuario autenticado puede ver formulario de edición', function () {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.edit', $this->patient->id));

        $response->assertStatus(200)
            ->assertViewIs('pacientes.edit')
            ->assertViewHas('patient');
    });

    test('formulario de edición muestra datos actuales del paciente', function () {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('pacientes.edit', $this->patient->id));

        $response->assertViewHas('patient', function ($patient) {
            return $patient->id === $this->patient->id;
        });
    });
});

describe('PacienteController - Update', function () {
    test('puede actualizar datos del paciente', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => 'Nombre Actualizado',
            'date_of_birth' => $this->patient->date_of_birth->format('Y-m-d'),
            'gender' => $this->patient->gender->value(),
            'allergies' => 'Nuevas alergias',
        ];

        $response = $this->actingAs($user)
            ->put(route('pacientes.update', $this->patient->id), $data);

        $response->assertRedirect(route('pacientes.show', $this->patient->id));
        $this->assertDatabaseHas('patients', [
            'id' => $this->patient->id,
            'full_name' => 'Nombre Actualizado',
        ]);
    });

    test('falla al actualizar sin nombre', function () {
        $user = User::factory()->create();

        $data = [
            'date_of_birth' => $this->patient->date_of_birth->format('Y-m-d'),
            'gender' => $this->patient->gender->value(),
        ];

        $response = $this->actingAs($user)
            ->put(route('pacientes.update', $this->patient->id), $data);

        $response->assertSessionHasErrors('full_name');
    });

    test('puede actualizar condiciones médicas del paciente', function () {
        $user = User::factory()->create();
        $condition = MedicalCondition::factory()->create();

        $data = [
            'full_name' => $this->patient->full_name,
            'date_of_birth' => $this->patient->date_of_birth->format('Y-m-d'),
            'gender' => $this->patient->gender->value(),
            'medical_conditions' => [
                $condition->id => [
                    'condition_id' => $condition->id,
                    'status' => 'Negative',
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->put(route('pacientes.update', $this->patient->id), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('patient_medical_conditions', [
            'patient_id' => $this->patient->id,
            'medical_condition_id' => $condition->id,
            'status' => 'Negative',
        ]);
    });

    test('puede actualizar grupo sanguineo del paciente', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => $this->patient->full_name,
            'date_of_birth' => $this->patient->date_of_birth->format('Y-m-d'),
            'gender' => $this->patient->gender->value(),
            'blood_group' => 'B+',
        ];

        $response = $this->actingAs($user)
            ->put(route('pacientes.update', $this->patient->id), $data);

        $response->assertRedirect(route('pacientes.show', $this->patient->id));

        $this->assertDatabaseHas('patients', [
            'id' => $this->patient->id,
            'blood_group' => 'B+',
        ]);
    });

    test('falla si grupo sanguineo es invalido al actualizar', function () {
        $user = User::factory()->create();

        $data = [
            'full_name' => $this->patient->full_name,
            'date_of_birth' => $this->patient->date_of_birth->format('Y-m-d'),
            'gender' => $this->patient->gender->value(),
            'blood_group' => 'ZZ',
        ];

        $response = $this->actingAs($user)
            ->put(route('pacientes.update', $this->patient->id), $data);

        $response->assertSessionHasErrors('blood_group');
    });

    test('puede limpiar el grupo sanguineo de un paciente al actualizar', function () {
        $user = User::factory()->create();
        $patient = Patient::factory()->create([
            'blood_group' => 'O+',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->put(route('pacientes.update', $patient->id), [
                'full_name' => $patient->full_name,
                'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
                'gender' => $patient->gender->value(),
                // blood_group omitido → debe quedar null
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'blood_group' => null,
        ]);
    });
});

describe('PacienteController - Destroy', function () {
    test('puede eliminar un paciente', function () {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('pacientes.destroy', $patient->id));

        $response->assertRedirect(route('pacientes.index'));
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    });

    test('usuario no autenticado no puede eliminar', function () {
        $patient = Patient::factory()->create();

        $response = $this->delete(route('pacientes.destroy', $patient->id));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('patients', ['id' => $patient->id]);
    });
});
