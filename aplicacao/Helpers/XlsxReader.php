<?php
namespace Aplicacao\Helpers;

class XlsxReader {

    public static function lerLinhas(string $arquivo): array {
        if (!class_exists('ZipArchive')) return [];

        $zip = new \ZipArchive();
        if ($zip->open($arquivo) !== true) return [];

        // Lê tabela de strings compartilhadas
        $strings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ss = simplexml_load_string($ssXml);
            foreach ($ss->si as $si) {
                if (isset($si->t)) {
                    $strings[] = (string)$si->t;
                } else {
                    // Rich text: concatena todos os <r><t>
                    $text = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) $text .= (string)$r->t;
                    }
                    $strings[] = $text;
                }
            }
        }

        // Lê a primeira planilha
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) return [];

        $sheet = simplexml_load_string($sheetXml);
        $mapa  = [];

        foreach ($sheet->sheetData->row as $row) {
            $rowIdx = (int)$row['r'] - 1;
            foreach ($row->c as $cell) {
                $ref    = (string)$cell['r'];                   // ex: A1, B2
                $colStr = preg_replace('/[0-9]/', '', $ref);
                $colIdx = self::colunaParaIndice($colStr);
                $tipo   = (string)$cell['t'];

                $valor = '';
                if ($tipo === 'inlineStr' && isset($cell->is->t)) {
                    $valor = (string)$cell->is->t;
                } elseif (isset($cell->v)) {
                    $v = (string)$cell->v;
                    $valor = ($tipo === 's') ? ($strings[(int)$v] ?? '') : $v;
                }

                $mapa[$rowIdx][$colIdx] = $valor;
            }
        }

        if (empty($mapa)) return [];

        // Normaliza para array sequencial por linha
        $resultado = [];
        foreach ($mapa as $rowIdx => $cols) {
            $maxCol = max(array_keys($cols));
            $linha  = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $linha[] = $cols[$i] ?? '';
            }
            $resultado[$rowIdx] = $linha;
        }
        ksort($resultado);
        return array_values($resultado);
    }

    private static function colunaParaIndice(string $col): int {
        $col    = strtoupper($col);
        $result = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $result = $result * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $result - 1;
    }
}
