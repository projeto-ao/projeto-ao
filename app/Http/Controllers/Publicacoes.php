<?php

namespace App\Http\Controllers;

use App\User as ModelUsuarios;
use App\Publicacoes as ModelPublicacoes;
use App\Http\Controllers\ImagemHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class Publicacoes extends Controller
{
    public function nova(Request $request)
    {
        $usuario = \Auth::user();

        $publicacao = new ModelPublicacoes();

        $requestImagem = $request->file('imagem');

        try {
            DB::beginTransaction();

            $publicacao->id_criador = $usuario->id;
            $publicacao->nome_criador = $usuario->name;
            $publicacao->texto = $request->input('texto');
            $publicacao->imagem = $requestImagem
                ? (new ImagemHelper)->salvarImagem($requestImagem)
                : null;

            $publicacao->save();

            DB::commit();
        } catch(\Exception $e) {
            Log::error($e);
        }

        return redirect('linha-do-tempo');
    }

    public function curtir($idPublicacao)
    {
        $usuario = \Auth::user();

        $publicacao = ModelPublicacoes::where('id', $idPublicacao)->first();

        try {
            DB::beginTransaction();

            $publicacao->curtidas += 1;
            $publicacao->save();

            DB::commit();
        } catch(\Exception $e) {
            Log::error($e);
        }
    }

    public function apagar($idPublicacao)
    {
        $publicacao = ModelPublicacoes::where('id', $idPublicacao)->first();

        try {
            DB::beginTransaction();

            if ($publicacao->compartilhamentos > 0) {
                $publicacoesFilhas = ModelPublicacoes::
                    where('id_publicacao_original', $idPublicacao)
                    ->get();

                foreach($publicacoesFilhas as $publicacaoFilha) {
                    $publicacaoFilha->delete();
                }
            }

            $publicacao->delete();

            DB::commit();
        } catch(\Exception $e) {
            Log::error($e);
        }
    }

    public function compartilhar($idPublicacao)
    {
        $usuario = \Auth::user();

        $publicacaoOriginal = ModelPublicacoes::where('id', $idPublicacao)
            ->first();
        $novaPublicacao = new ModelPublicacoes();

        try {
            DB::beginTransaction();

            $publicacaoOriginal->compartilhamentos += 1;

            $novaPublicacao->id_criador = $publicacaoOriginal->id_criador;
            $novaPublicacao->nome_criador = $publicacaoOriginal->nome_criador;
            $novaPublicacao->texto = $publicacaoOriginal->texto;
            $novaPublicacao->id_publicacao_original = $publicacaoOriginal->id;
            $novaPublicacao->id_compartilhador = $usuario->id;
            $novaPublicacao->nome_compartilhador = $usuario->name;

            if ($publicacaoOriginal->imagem !== null) {
                $novaPublicacao->imagem = $publicacaoOriginal->imagem;
            }

            $publicacaoOriginal->save();
            $novaPublicacao->save();

            DB::commit();
        } catch(\Exception $e) {
            Log::error($e);
        }
    }
}
