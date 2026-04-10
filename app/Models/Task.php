<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Define quais colunas podem ser preenchidas via requisição
    // Isso é uma proteção de segurança chamada "Mass Assignment Protection"
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'done',
    ];

    // Diz ao Laravel para tratar 'done' como boolean (true/false)
    // sem isso, viria como "0" ou "1" string do banco
    protected $casts = [
        'done' => 'boolean',
    ];

    // Define o relacionamento: uma Task pertence a um User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
