<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/tasks → Lista todas as tarefas do usuário autenticado
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()->orderBy('created_at', 'desc')->get();

        return response()->json($tasks);
    }

    // POST /api/tasks → Cria uma nova tarefa
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = $request->user()->tasks()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'done'        => false, // sempre começa como pendente (regra do PRD)
        ]);

        return response()->json($task, 201);
    }

    // PATCH /api/tasks/{id} → Atualiza uma tarefa (título, descrição ou status)
    public function update(Request $request, $id)
    {
        // Busca a tarefa E garante que ela pertence ao usuário autenticado
        $task = $request->user()->tasks()->findOrFail($id);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255', // sometimes = só valida se vier
            'description' => 'nullable|string',
            'done'        => 'sometimes|boolean',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    // DELETE /api/tasks/{id} → Deleta uma tarefa
    public function destroy(Request $request, $id)
    {
        // Busca a tarefa E garante que ela pertence ao usuário autenticado
        $task = $request->user()->tasks()->findOrFail($id);

        $task->delete();

        return response()->json([
            'message' => 'Tarefa deletada com sucesso.'
        ]);
    }
}
