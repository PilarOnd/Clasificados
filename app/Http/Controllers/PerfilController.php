<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    private function readUserData(): array
    {
        $path = base_path('user.json');
        if (!file_exists($path)) {
            return ['usuarios' => []];
        }
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : ['usuarios' => []];
    }

    private function writeUserData(array $data): void
    {
        $path = base_path('user.json');
        $fp = fopen($path, 'c+');
        if ($fp === false) {
            // fallback simple
            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return;
        }
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    public function edit()
    {
        $data = $this->readUserData();
        $user = $data['usuarios'][0] ?? [
            'nombre' => 'Administrador',
            'correo' => 'admin.prueba@gmail.com',
            'rol' => 'Administrador',
            'foto' => null,
        ];
        return view('perfil', ['perfil' => $user]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $data = $this->readUserData();
        $user = $data['usuarios'][0] ?? [];

        if (isset($validated['name'])) {
            $user['nombre'] = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user['correo'] = $validated['email'];
        }
        if (!empty($validated['password'])) {
            $user['contraseña'] = $validated['password'];
        }

        if ($request->hasFile('avatar')) {
            // eliminar foto anterior si es del disco public
            if (!empty($user['foto'])) {
                $previous = ltrim((string) $user['foto'], '/');
                if (str_starts_with($previous, 'storage/')) {
                    $relative = substr($previous, strlen('storage/'));
                    Storage::disk('public')->delete($relative);
                }
            }

            $extension = $request->file('avatar')->getClientOriginalExtension();
            $filename = 'avatar_'.time().'_'.bin2hex(random_bytes(4)).'.'.$extension;
            $stored = $request->file('avatar')->storeAs('public/avatars', $filename);
            $publicPath = Storage::url($stored); // /storage/avatars/...
            $user['foto'] = $publicPath;
        }

        $data['usuarios'][0] = $user;
        $this->writeUserData($data);

        return redirect()->route('perfil')->with('status', 'Perfil actualizado');
    }
}


