<?php
namespace Aplicacao\Servicos;

class ClimaService {

    // 1. Converte Nome da Cidade em Latitude/Longitude
    public function buscarCoordsPorCidade($cidade) {
        // Remove acentos e espaços para a URL
        $cidadeFormatada = urlencode($cidade);
        $url = "https://geocoding-api.open-meteo.com/v1/search?name={$cidadeFormatada}&count=1&language=pt&format=json";

        $context = stream_context_create(['http' => ['timeout' => 3]]);
        $json = @file_get_contents($url, false, $context);

        if ($json) {
            $dados = json_decode($json, true);
            if (isset($dados['results'][0])) {
                return [
                    'lat' => $dados['results'][0]['latitude'],
                    'lon' => $dados['results'][0]['longitude'],
                    'nome_real' => $dados['results'][0]['name'] // Nome oficial encontrado
                ];
            }
        }
        // Se não achar, retorna coordenadas de Brasília como fallback
        return ['lat' => -15.7801, 'lon' => -47.9292, 'nome_real' => 'Brasília (Padrão)'];
    }

    // 2. Busca o clima (Agora aceita o array de coordenadas)
    public function obterClimaAtual($lat, $lon) {
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current_weather=true&timezone=America%2FSao_Paulo";

        $context = stream_context_create(['http' => ['timeout' => 3]]);
        $json = @file_get_contents($url, false, $context);

        if ($json === FALSE) return null;

        $dados = json_decode($json, true);

        if (isset($dados['current_weather'])) {
            return [
                'temperatura' => $dados['current_weather']['temperature'],
                'codigo' => $dados['current_weather']['weathercode'],
                'velocidade_vento' => $dados['current_weather']['windspeed'],
                'descricao' => $this->traduzirCodigoWMO($dados['current_weather']['weathercode']),
                'alerta' => $this->verificarAlerta($dados['current_weather']['weathercode'])
            ];
        }
        return null;
    }

    private function traduzirCodigoWMO($codigo) {
        $mapa = [
            0 => 'Céu Limpo ☀️', 1 => 'Ensolarado 🌤️', 2 => 'Nublado ⛅', 3 => 'Encoberto ☁️',
            45 => 'Nevoeiro 🌫️', 51 => 'Garoa 🌧️', 61 => 'Chuva ☔', 63 => 'Chuva Moderada ☔',
            65 => 'Chuva Forte ⛈️', 80 => 'Pancadas 🌦️', 95 => 'Tempestade ⚡'
        ];
        return isset($mapa[$codigo]) ? $mapa[$codigo] : 'Instável';
    }

    private function verificarAlerta($codigo) {
        return in_array($codigo, [63, 65, 80, 81, 82, 95, 96, 99]);
    }
}