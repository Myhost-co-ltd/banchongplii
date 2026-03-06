<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $teacherRoleId = Role::where('name', 'teacher')->value('id');

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role_id'  => $teacherRoleId,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard.teacher');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $loginInput = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        if (Auth::attempt(['email' => $loginInput, 'password' => $password])) {
            $request->session()->regenerate();

            return $this->redirectByRole(Auth::user()->role_name ?? null);
        }

        $legacyTeacher = $this->findLegacyTeacherByLogin($loginInput);
        if (! $legacyTeacher || ! $this->matchesLegacyTeacherPassword($password, $legacyTeacher->password ?? null)) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput();
        }

        $teacherUser = $this->resolveLocalTeacherUser($legacyTeacher);

        Auth::login($teacherUser);
        $request->session()->regenerate();

        return redirect()->route('dashboard.teacher');
    }

    protected function redirectByRole(?string $roleName)
    {
        return match ($roleName) {
            'superadmin', 'admin' => redirect()->route('dashboard.admin'),
            'teacher' => redirect()->route('dashboard.teacher'),
            'director' => redirect()->route('dashboard.director'),
            default => $this->logoutWithError(),
        };
    }

    protected function logoutWithError()
    {
        Auth::logout();

        return redirect()->route('login')->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    private function findLegacyTeacherByLogin(string $login): ?object
    {
        if ($login === '' || ! Schema::hasTable('tb_teacher')) {
            return null;
        }

        $hasUsername = Schema::hasColumn('tb_teacher', 'username');
        $hasIdCard = Schema::hasColumn('tb_teacher', 'id_card_number');
        $hasTeacherId = Schema::hasColumn('tb_teacher', 'id_teacher');

        if (! $hasUsername && ! $hasIdCard && ! $hasTeacherId) {
            return null;
        }

        $query = DB::table('tb_teacher');

        $query->where(function ($conditions) use ($login, $hasUsername, $hasIdCard, $hasTeacherId) {
            $hasCondition = false;

            if ($hasUsername) {
                $conditions->whereRaw('TRIM(username) = ?', [$login]);
                $hasCondition = true;
            }

            if ($hasIdCard) {
                $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                $conditions->{$method}('TRIM(id_card_number) = ?', [$login]);
                $hasCondition = true;
            }

            if ($hasTeacherId && ctype_digit($login)) {
                $method = $hasCondition ? 'orWhere' : 'where';
                $conditions->{$method}('id_teacher', (int) $login);
            }
        });

        return $query->orderBy('id_teacher')->first();
    }

    private function matchesLegacyTeacherPassword(string $plainPassword, $storedPassword): bool
    {
        $stored = trim((string) $storedPassword);
        if ($stored === '') {
            return false;
        }

        if (preg_match('/^\$(2y|2a|argon2)/i', $stored) === 1 && Hash::check($plainPassword, $stored)) {
            return true;
        }

        if (preg_match('/^[a-f0-9]{32}$/i', $stored) === 1) {
            return hash_equals(strtolower($stored), md5($plainPassword));
        }

        return hash_equals($stored, $plainPassword);
    }

    private function resolveLocalTeacherUser(object $legacyTeacher): User
    {
        $teacherRoleId = Role::firstOrCreate(['name' => 'teacher'])->id;
        $teacherName = $this->buildLegacyTeacherName($legacyTeacher);
        $legacyEmail = $this->buildLegacyTeacherEmail($legacyTeacher);

        $user = User::query()->where('email', $legacyEmail)->first();

        if (! $user && $teacherName !== '') {
            $normalizedName = function_exists('mb_strtolower')
                ? mb_strtolower($teacherName, 'UTF-8')
                : strtolower($teacherName);

            $user = User::query()
                ->where('role_id', $teacherRoleId)
                ->whereRaw('LOWER(name) = ?', [$normalizedName])
                ->first();
        }

        if ($user) {
            $updates = [];

            if ($teacherName !== '' && $user->name !== $teacherName) {
                $updates['name'] = $teacherName;
            }

            if ((int) $user->role_id !== (int) $teacherRoleId) {
                $updates['role_id'] = $teacherRoleId;
            }

            if ($updates !== []) {
                $user->fill($updates);
                $user->save();
            }

            return $user;
        }

        return User::create([
            'name' => $teacherName !== '' ? $teacherName : 'Teacher',
            'email' => $legacyEmail,
            'password' => Hash::make(Str::random(32)),
            'role_id' => $teacherRoleId,
        ]);
    }

    private function buildLegacyTeacherName(object $legacyTeacher): string
    {
        $fullName = trim(collect([
            trim((string) ($legacyTeacher->id_title_name ?? '')),
            trim((string) ($legacyTeacher->name ?? '')),
            trim((string) ($legacyTeacher->surname ?? '')),
        ])->filter()->implode(' '));

        if ($fullName !== '') {
            return $fullName;
        }

        $username = trim((string) ($legacyTeacher->username ?? ''));
        if ($username !== '') {
            return $username;
        }

        $teacherId = (int) ($legacyTeacher->id_teacher ?? 0);

        return $teacherId > 0 ? "Teacher {$teacherId}" : 'Teacher';
    }

    private function buildLegacyTeacherEmail(object $legacyTeacher): string
    {
        $username = trim((string) ($legacyTeacher->username ?? ''));
        if ($username !== '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return strtolower($username);
        }

        $teacherId = (int) ($legacyTeacher->id_teacher ?? 0);
        if ($teacherId > 0) {
            return 'teacher'.$teacherId.'@tb-teacher.local';
        }

        $fingerprint = md5(implode('|', [
            (string) ($legacyTeacher->username ?? ''),
            (string) ($legacyTeacher->id_card_number ?? ''),
            (string) ($legacyTeacher->name ?? ''),
            (string) ($legacyTeacher->surname ?? ''),
        ]));

        return 'teacher'.substr($fingerprint, 0, 12).'@tb-teacher.local';
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
