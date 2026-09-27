<?php

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Soporte de usuarios
|--------------------------------------------------------------------------
| El registro público y "olvidé mi contraseña" están desactivados: soporte
| crea usuarios y resetea contraseñas desde la terminal del contenedor.
*/

Artisan::command('soporte:crear-usuario
    {email : Correo de acceso}
    {nombre : Nombre visible del usuario}
    {--roles=Admin,Doctor : Roles separados por coma (Admin, Doctor, Tecnico)}
    {--doctor= : Nombre completo del perfil de doctor (crea/vincula el perfil)}
    {--matricula= : Matrícula profesional del doctor}
    {--especialidad=Pediatría : Especialidad del doctor}', function () {
    /** @var \Illuminate\Console\Command $this */
    $email = mb_strtolower((string) $this->argument('email'));

    if (User::where('email', $email)->exists()) {
        $this->error("Ya existe un usuario con el correo {$email}.");

        return 1;
    }

    $password = (string) $this->secret('Contraseña (mín. 12, mayúsculas, números y símbolos)');
    $validator = Validator::make(['password' => $password], [
        'password' => ['required', Password::min(12)->mixedCase()->letters()->numbers()->symbols()],
    ]);

    if ($validator->fails()) {
        $this->error($validator->errors()->first('password'));

        return 1;
    }

    $doctorId = null;
    if ($this->option('doctor')) {
        $license = (string) ($this->option('matricula') ?: 'SIN-MATRICULA-'.strtoupper(substr(md5($email), 0, 6)));
        $doctor = Doctor::firstOrCreate(
            ['license_number' => $license],
            [
                'full_name' => (string) $this->option('doctor'),
                'specialty' => (string) $this->option('especialidad'),
                'active' => true,
            ],
        );
        $doctorId = $doctor->id;
    }

    $user = User::create([
        'name' => (string) $this->argument('nombre'),
        'email' => $email,
        'password' => $password,
        'doctor_id' => $doctorId,
    ]);
    $user->forceFill(['email_verified_at' => now()])->save();

    $roles = array_filter(array_map('trim', explode(',', (string) $this->option('roles'))));
    foreach ($roles as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
    $user->syncRoles($roles);

    $this->info("Usuario {$email} creado con roles: ".implode(', ', $roles).($doctorId ? ' (con perfil de doctor)' : ''));

    return 0;
})->purpose('Crea un usuario del sistema (el registro público está desactivado)');

Artisan::command('soporte:resetear-password
    {email : Correo del usuario}
    {--quitar-2fa : Desactiva además la verificación en dos pasos (celular perdido)}', function () {
    /** @var \Illuminate\Console\Command $this */
    $user = User::where('email', mb_strtolower((string) $this->argument('email')))->first();

    if ($user === null) {
        $this->error('No existe un usuario con ese correo.');

        return 1;
    }

    $password = (string) $this->secret('Nueva contraseña (mín. 12, mayúsculas, números y símbolos)');
    $validator = Validator::make(['password' => $password], [
        'password' => ['required', Password::min(12)->mixedCase()->letters()->numbers()->symbols()],
    ]);

    if ($validator->fails()) {
        $this->error($validator->errors()->first('password'));

        return 1;
    }

    $user->password = $password;

    if ($this->option('quitar-2fa')) {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    $user->save();

    $this->info("Contraseña de {$user->email} actualizada.".($this->option('quitar-2fa') ? ' 2FA desactivado.' : ''));

    return 0;
})->purpose('Resetea la contraseña de un usuario (soporte)');
