<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function userLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json(['message' => 'Login successful', 'token' => $token]);
    }

    

    public function userRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string',
            'nombre' => 'required|string',
            'apellido_paterno' => 'required|string',
            'apellido_materno' => 'required|string',
            'edad' => 'required|integer',
            'estado' => 'required|string',
            'ocupacion' => 'required|string',
            'escolaridad' => 'required|string',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'edad' => $request->edad,
            'estado' => $request->estado,
            'ocupacion' => $request->ocupacion,
            'escolaridad' => $request->escolaridad,
        ]);

        // Autenticar al usuario y obtener el token
        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'User registered successfully', 'token' => $token]);
    }

    public function profile(Request $request)
    {
        try {
            $user = $request->user(); // Obtén el usuario autenticado desde la solicitud
            return response()->json(['user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserById($userId)
{
    $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

 

    return response()->json(['user' => $user]);
}



// En el método adminLogin del AuthController
public function adminLogin(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Obtén el usuario autenticado
    $user = JWTAuth::user();

    // Incluye el rol en la respuesta
    return response()->json(['message' => 'Login successful', 'token' => $token, 'role' => $user->role]);
}

// En el método adminRegister del AuthController
public function adminRegister(Request $request)
{
    $request->validate([
        'email' => 'required|string|email|unique:users',
        'password' => 'required|string',
        'confirm_password' => 'required|string|same:password', // Validación de contraseña confirmada
        'role' => 'required|array',
        'nombre' => 'required|string',
        'apellido_paterno' => 'required|string',
        'apellido_materno' => 'required|string',
    ]);

    // Asegurémonos de que el campo 'role' sea almacenado como un array
    $roles = is_array($request->role) ? $request->role : [$request->role];

    $user = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $roles, // Almacenar los roles como un array
        'nombre' => $request->nombre,
        'apellido_paterno' => $request->apellido_paterno,
        'apellido_materno' => $request->apellido_materno,
    ]);

    // Autenticar al usuario y obtener el token
    $token = JWTAuth::fromUser($user);

    return response()->json(['message' => 'User registered successfully', 'token' => $token, 'role' => $user->role]);
}


public function getUsersAdministrativos()
{
    // Filtra los usuarios con roles administrativos
    $rolesAdministrativos = ['Capturista', 'Carrusel', 'Plantillas', 'Admin', 'Validador']; // Ajusta según tus roles
    $users = User::whereIn('role', $rolesAdministrativos)->get();

    return response()->json(['users' => $users]);
}


public function eliminarUsuarioAdministrativo($id)
{
    $usuario = User::find($id);

    if (!$usuario) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    // Realiza cualquier validación adicional antes de eliminar, si es necesario

    $usuario->delete();

    return response()->json(['message' => 'Usuario eliminado con éxito']);
}



public function updateAdministrativos(Request $request, $id)
{
    $user = User::findOrFail($id);

    $rules = [
        'password' => 'sometimes|required|string',
        'confirm_password' => 'sometimes|required|string|same:password',
        'role' => 'required|array',
        'nombre' => 'required|string',
        'apellido_paterno' => 'required|string',
        'apellido_materno' => 'required|string',
    ];

    // Aplicar la validación del correo electrónico solo si se proporciona un nuevo correo electrónico
    if ($request->has('current_email')) {
        $rules['email'] = [
            'required',
            'string',
            'email',
            function ($attribute, $value, $fail) use ($request, $user) {
                if ($value === $request->current_email) {
                    // Si los correos son iguales, simplemente pasa la validación
                    return;
                }

                // Validar que el correo no exista en otro usuario
                $existingUser = User::where('email', $value)->first();
                if ($existingUser && $existingUser->_id != $user->_id) {
                    $fail("El correo electrónico ya está en uso por otro usuario.");
                }
            },
        ];
    }

    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['message' => 'Error de validación', 'errors' => $validator->errors()], 422);
    }

    try {
        // Actualizar los demás campos
        $updateData = [
            'email' => $request->email,
            'role' => $request->role,
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
        ];

        // Actualizar la contraseña solo si se proporciona una nueva
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json(['message' => 'Usuario actualizado exitosamente', 'user' => $user]);
    } catch (\Exception $e) {
        // Manejar la excepción de MongoDB (E11000 duplicate key error)
        return response()->json(['message' => 'Error: El correo electrónico ya está en uso por otro usuario.'], 422);
    }
}



public function updateUserProfile(Request $request, $id)
{
    $user = User::findOrFail($id);

    $rules = [
        'password' => 'sometimes|required|string',
        'confirm_password' => 'sometimes|required|string|same:password',
        'nombre' => 'required|string',
        'apellido_paterno' => 'required|string',
        'apellido_materno' => 'required|string',
        'edad' => 'required|integer',
        'estado' => 'required|string',
        'ocupacion' => 'required|string',
        'escolaridad' => 'required|string',
    ];

    // Aplicar la validación del correo electrónico solo si se proporciona un nuevo correo electrónico
    if ($request->has('current_email')) {
        $rules['email'] = [
            'required',
            'string',
            'email',
            function ($attribute, $value, $fail) use ($request, $user) {
                if ($value === $request->current_email) {
                    // Si los correos son iguales, simplemente pasa la validación
                    return;
                }

                // Validar que el correo no exista en otro usuario
                $existingUser = User::where('email', $value)->first();
                if ($existingUser && $existingUser->_id != $user->_id) {
                    $fail("El correo electrónico ya está en uso por otro usuario.");
                }
            },
        ];
    }

    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['message' => 'Error de validación', 'errors' => $validator->errors()], 422);
    }

    try {
        // Actualizar los demás campos
        $updateData = [
            'email' => $request->email,
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'edad' => $request->edad,
            'estado' => $request->estado,
            'ocupacion' => $request->ocupacion,
            'escolaridad' => $request->escolaridad,
        ];

        // Actualizar la contraseña solo si se proporciona una nueva
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json(['message' => 'Usuario actualizado exitosamente', 'user' => $user]);
    } catch (\Exception $e) {
        // Manejar la excepción de MongoDB (E11000 duplicate key error)
        return response()->json(['message' => 'Error: El correo electrónico ya está en uso por otro usuario.'], 422);
    }
}

}